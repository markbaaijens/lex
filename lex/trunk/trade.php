<?php

include_once("includes/inc.global.php");

$p->site_section = EXCHANGES;
$p->page_title = $lng_record_exchange;

include("classes/class.trade.php");
include("includes/inc.forms.validation.php");

//
// Define form elements
//
$member = new cMember;

/* if($cUser->member_id == "ADMIN") {
	$p->DisplayPage($lng_sorry_no_exchanges_for_admin_account." <a href=admin_menu.php>".$lng_admin_menu."</a>.");	
	exit;
} */ // changed by ejkv (admin with member_role = 9 should have all rights)

if($_REQUEST["mode"] == "admin") {
	$cUser->MustBeLevel(1);
	$member->LoadMember($_REQUEST["member_id"]);
	$p->page_title .= " ".$lng_for." ".$member->PrimaryName(); // replaced " for " into " ".$lng_for." " - by ejkv
} else {
	$cUser->MustBeLoggedOn();
	$member = $cUser;
}	
	
$form->addElement('hidden', 'member_id', $member->member_id);
$form->addElement('hidden', 'mode', $_REQUEST["mode"]);
$form->addElement("html", "<TR></TR>");  // TODO: Move this to the header

if (MEMBERS_CAN_INVOICE==true) // Invoicing turned on in config, so let member choose
	$form->addElement("select", "typ", $lng_transaction_type, array($lng_transfer,$lng_invoice));
else // Invoicing turned off, this form now only functions to transfer money
	$form->addElement('hidden', 'typ', 0);	
	
$name_list = new cMemberGroup;
$name_list->LoadMemberGroup();

$form->addElement("select", "member_to", $lng_transfer_to_member, $name_list->MakeNameArray());

$category_list = new cCategoryList();
$form->addElement('select', 'category', $lng_category, $category_list->MakeCategoryArray());
$form->addElement("text", "units", $lng_nmbr_of. UNITS ."", array('size' => 5, 'maxlength' => 10));
if(UNITS == "Hours") {
	$form->addElement("text","minutes",$lng_nmbr_of_minutes,array('size'=>2,'maxlength'=>2));
}
$form->addElement('textarea', 'description', $lng_enter_description_of_exchange, array('cols'=>50, 'rows'=>4, 'wrap'=>'soft', 'maxlength'=>255));
$form->addElement('submit', 'btnSubmit', $lng_submit);

//
// Define form rules
//
//$form->addRule('description', 'Enter a description', 'required');
$form->registerRule('verify_not_self','function','verify_not_self');
$form->addRule('member_to', $lng_cannot_transfer_to_self, 'verify_not_self');
$form->registerRule('verify_selection','function','verify_selection');
$form->addRule('category', $lng_choose_category, 'verify_selection');
$form->addRule('member_to', $lng_choose_member, 'verify_selection');
$form->addRule('description', $lng_description_too_long, 'verify_max255');

if(UNITS == "Hours") {
	$form->registerRule('verify_whole_hours','function','verify_whole_hours');
	$form->addRule('units', $lng_hours_entered_must_be_whole, 'verify_whole_hours');
	$form->registerRule('verify_even_minutes','function','verify_even_minutes');
	$form->addRule('minutes', $lng_enter_three_minute_increments, 'verify_even_minutes');
} else {
	$form->registerRule('verify_valid_units','function','verify_valid_units');
	$form->addRule('units', $lng_enter_positive_number_two_dec, 'verify_valid_units');
}

$form->addRule("member_to", $lng_enter_a, "required");
$form->addRule("category", $lng_enter_a, "required");
$form->addRule("units", $lng_enter_a, "required");
$form->addRule('description', $lng_enter_a, "required");


//
// Then check if we are processing a submission or just displaying the form
//
if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process('process_data', false);
} else {
   $p->DisplayPage($form->toHtml());  // just display the form
}

function process_data ($values) {
	global $p, $member, $cErr, $cUser, $lng_nu_units_to_exchange, $lng_trade_failed, 
	       $lng_payment_received_on, $lng_hi_cap, $lng_let_know_received_payment_from, 
	       $lng_member_id, $lng_notified_you_wish_transfer, $lng_to_him_her, 
	       $lng_member_opted_to_confirm, $lng_would_you_like_to, $lng_record_another, 
	       $lng_exchange, $lng_invoice_facility_disabled, $lng_invoice_received_on, 
	       $lng_let_know_invoice_from, $lng_log_in_to_pay_reject_invoice, 
	       $lng_has_been_send_invoice_for, $lng_will_informed_when_member_pays, 
	       $lng_you_have, $lng_transferred_to, $lng_or_would_you_like_to_leave, $lng_feedback, 
	       $lng_for_this_member, $lng_donation_from,$lng_try_again_later, $lng_balance_too_low, 
	       $lng_balance_too_high,$lng_elected_to_confirm_payment,
	       $lng_transaction,$lng_from,$lng_description; 
	
	$list = "";
	
	if(UNITS == "Hours") {
		if($values['minutes'] > 0)
			$values['units'] = $values['units'] + ($values['minutes'] / 60);
	}
	
	if(!($values['units'] > 0)) {
		$cErr->Error($lng_nu_units_to_exchange);
		include("redirect.php");
	}
	
	$member_to_id = substr($values['member_to'],0, strpos($values['member_to'],"?")); // TODO:
	$member_to = new cMember;
	$member_to->LoadMember($member_to_id);
	
	if ($_REQUEST["mode"] == "admin") {
        $cUser->MustBeLevel(1);

        // record that trade was entered by an admin & log if logging enabled
		$type = TRADE_BY_ADMIN;
    }
	else {
		$type = TRADE_ENTRY;  // regular trade
    }
	
	/*
	[chris] For transaction approval
	*/
	
	// Member wishes to confirm payments made to him OR this is an invoice?
	if ($_REQUEST["typ"]==1 || $member_to->confirm_payments==1) { 
		
		if (htmlspecialchars($values['units']) >= 0 && $member_to_id != $member->member_id) {
			
			global $cDB;

			// Is this is a payment? "typ" 1 = Invoice			
			if ($_REQUEST["typ"]!=1) { 
			
				// Yes, this is a payment, so teh other side of the OR-statement above must be true:
				// $member_to->confirm_payments==1 -> member_to wants to confirm payments 
				
				// Is the member_from restricted?
				if ($member->restriction==1) {
					$list .= "<p>".LEECH_NOTICE;
				}
				else
					// This is a payment, member_from is not restricted, member_to wants to confirm the payment  
					if ($cDB->Query("INSERT INTO trades_pending (trade_date, member_id_from, member_id_to, amount, category, description, typ) VALUES (now(), ". 	$cDB->EscTxt($member->member_id) .", ". $cDB->EscTxt($member_to_id) .", ". $cDB->EscTxt($values["units"]) .", ". $cDB->EscTxt($values["category"]) .", ". 	$cDB->EscTxt($values["description"]) .", \"T\");")) {
					
					$mailed = mailex($member_to->person[0]->email, 
										   $lng_payment_received_on, 
										   $lng_let_know_received_payment_from." ".$member->member_id."\n\n".$lng_elected_to_confirm_payment."<br><br>"."trades_pending.php?action=incoming");
			
					$list .= $lng_member_id." ".$member_to_id." ".$lng_notified_you_wish_transfer." ". $values['units'] ." ". strtolower(UNITS) ." ".$lng_to_him_her.".<p>".$lng_member_opted_to_confirm.".<p>". // added $lng_member_id by ejkv
							$lng_would_you_like_to." <A HREF=trade.php?mode=".$_REQUEST["mode"]."&member_id=". $_REQUEST["member_id"].">".$lng_record_another."</A> ".$lng_exchange."?";
				}
				else
					$list .= $lng_trade_failed." ".$lng_try_again_later;	
			}
			else 
			// Is this an invoice?
			if ($_REQUEST["typ"]==1) {
				
				if (MEMBERS_CAN_INVOICE!=true) // Invoicing is turned off, user has no right to be here!
					$list .= $lng_invoice_facility_disabled;	
				else
					// This is an invoice 
					if ($cDB->Query("INSERT INTO trades_pending (trade_date, member_id_from, member_id_to, amount, category, description, typ) VALUES (now(), ". 	$cDB->EscTxt($member->member_id) .", ". $cDB->EscTxt($member_to_id) .", ". $cDB->EscTxt($values["units"]) .", ". $cDB->EscTxt($values["category"]) .", ". 	$cDB->EscTxt($values["description"]) .", \"I\");")) {
					
					$mailed = mailex($member_to->person[0]->email, 
  					                $lng_invoice_received_on, 
					                  $lng_let_know_invoice_from." ".
					                  $member->member_id." (".$values["units"]." ".UNITS.")<br>".
                            $lng_description.": ".$values["description"].
					                  "<br><br>".
					                  "<a href=\"".server_base_url()."/trades_pending.php?action=outgoing\">".
					                  $lng_log_in_to_pay_reject_invoice."</a>"); 
			
					$list .= $member_to_id." ".$lng_has_been_send_invoice_for." ". $values['units'] ." ". strtolower(UNITS) .".<p> ".$lng_will_informed_when_member_pays.".<p>".
						$lng_would_you_like_to." <A HREF=trade.php?mode=".$_REQUEST["mode"]."&member_id=". $_REQUEST["member_id"].">".$lng_record_another."</A> ".$lng_exchange."?";
			}
			else
					$list .= $lng_trade_failed." ".$lng_try_again_later;	
			}
			
		}
	}
	else { // Regular trade: no invoice AND member_to does NOT want to confirm payments
		
		$trade = new cTrade($member, $member_to, htmlspecialchars($values['units']), htmlspecialchars($values['category']), htmlspecialchars($values['description']), $type);
		
		$status = $trade->MakeTrade();
		
		if ($status != MAKE_TRADE_STATUS_OK) {
			$list .= $lng_trade_failed." (code=".$status.")";

      if ($status == MAKE_TRADE_STATUS_MEMBER_RESTRICTION)
				$list .= "<br><br><strong>".LEECH_NOTICE."</strong>";

      if ($status == MAKE_TRADE_STATUS_MEMBER_BELOW_MINIMUM)
				$list .= "<br><br><strong>".$lng_balance_too_low."</strong>";
				
      if ($status == MAKE_TRADE_STATUS_MEMBER_ABOVE_MAXIMUM)
				$list .= "<br><br><strong>".$lng_balance_too_high."</strong>";
								
		} else { // Trade has succeeded
		
			$list .= $lng_you_have." ". $values['units'] ." ". strtolower(UNITS) .$lng_transferred_to. $member_to_id .".  ".$lng_would_you_like_to." <A HREF=trade.php?mode=".$_REQUEST["mode"]."&member_id=". $_REQUEST["member_id"].">".$lng_record_another."</A> ".$lng_exchange."?<P>";
			
      if (SHOW_FEEDBACK == 1) {					
  			$list .= $lng_or_would_you_like_to_leave." <A HREF=feedback.php?mode=". $_REQUEST["mode"] ."&author=". $member->member_id ."&about=". $member_to_id ."&trade_id=". $trade->trade_id .">".$lng_feedback."</A> ".$lng_for_this_member."?"; 
			}

      mailex($member_to->person[0]->email, 
             $lng_payment_received_on, 
             $lng_let_know_received_payment_from." ".$member->member_id." (".$values["units"]." ".UNITS.")<br>".
             $lng_description.": ".$values["description"].
             "<br><br>".
             "<a href=\"".server_base_url()."/trade_history.php?mode=self\">Transactiegeschiedenis</a>"
             );
		
  		// Has the recipient got an income tie set-up? If so, we need to transfer a percentage of this elsewhere...
			$recipTie = cIncomeTies::getTie($member_to_id);
			
			if ($recipTie && ALLOW_INCOME_SHARES==true) {
				
				$member_to = new cMember;
				$member_to->LoadMember($member_to_id);
	
				$theAmount = round(($values['units']*$recipTie->percent)/100);
				
				$charity_to = new cMember;
				$charity_to->LoadMember($recipTie->tie_id);
	
				$trade2 = new cTrade($member_to, $charity_to, htmlspecialchars($theAmount), htmlspecialchars(INCOME_TIE_CATEGORY_ID), htmlspecialchars($lng_donation_from." ".$member_to_id.""), 'T');
		
				$status = $trade2->MakeTrade();
			}
		}
	}
	
   $p->DisplayPage($list);
}

function verify_not_self($element_name,$element_value) {
	global $member;
	$member_id = substr($element_value,0, strpos($element_value,"?"));
	if ($member_id == $member->member_id)
		return false;
	else
		return true;
}

function verify_valid_units($element_name,$element_value) { 
	if ($element_value < 0)
		return false; 
	elseif ($element_value * 100 != floor($element_value * 100)) 
		return false;	// allow no more than two decimal points
	else
		return true;
}

function verify_even_minutes ($z, $minutes) { // verifies # of minutes entered represents an evenly
	if($minutes/60*1000 == floor($minutes/60*1000)) 	// divisible fraction w/ no more than 3
		return true;												//	decimal points
	else
		return false;
}

function verify_whole_hours ($z, $hours) {
	if(abs(floor($hours)) != $hours)
		return false;
	else
		return true;
}

?>
