<?php
/* changes by ejkv
If site SAFE mode = on? =>> Pear::(mail) using the 5th mail parameter causes error by sending mail - added by ejkv
If SAFE_mode_ON is set true (inc.config.php), in email.php no CC: will be sent, nor will CC: selection be showed in UI
*/
include_once("includes/inc.global.php");
$p->site_section = SECTION_EMAIL;
$p->page_title = $lng_email_member;

$cUser->MustBeLoggedOn();

include("includes/inc.forms.php");

//
// First, we define the form
//

$form->addElement("hidden", "email_to", $_REQUEST["email_to"]);
$form->addElement("hidden", "member_to", $_REQUEST["member_to"]);
$member_to = new cMember;
$member_to->LoadMember($_REQUEST["member_to"]);
$form->addElement("header", null, $lng_to.": <I>". $_REQUEST["email_to"] . " (". $member_to->member_id .")</I>");
$form->addElement("text", "subject", $lng_subject.": ", array('size' => 35, 'maxlength' => 100));
if (!SAFE_MODE_ON) $form->addElement("select", "cc", $lng_would_you_receive_copy, array("Y"=>$lng_yes, "N"=>$lng_no)); // - changed by ejkv
// $form->addElement("select", "cc", $lng_would_you_receive_copy, array("Y"=>$lng_yes, "N"=>$lng_no));

/*  The following code should work, and works on my server, but not on Open Access.  Bug?
$cc[] =& HTML_QuickForm::createElement('radio',null,null,'<FONT SIZE=2>Yes</FONT>','Y');
$cc[] =& HTML_QuickForm::createElement('radio',null,null,'<FONT SIZE=2>No</FONT>','N');
$form->addGroup($cc, "cc", 'Would you like to recieve a copy?');
*/

$form->addElement("static", null, null, null);
$form->addElement("textarea", "message", $lng_your_message, array("cols"=>65, "rows"=>10, "wrap"=>"soft"));

$form->addElement("static", null, null, null);
$form->addElement("submit", "btnSubmit", $lng_send);

//
// Define form rules
//
$form->addRule("message", $lng_enter_message, "required");

if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form
	if (!SAFE_MODE_ON) $form->setDefaults(array("cc"=>"Y")); // - changed by ejkv
	// $form->setDefaults(array("cc"=>"Y"));
	$p->DisplayPage($form->toHtml());
}

//
// The form has been submitted with valid data, so process it   
//
function process_data ($values) {
	global $p, $cUser, $lng_problem_sending_mail, $lng_try_again_later, $lng_your_message_send, $lng_enter_email_address, $lng_member_profile; // added $lng_your_message_send, $lng_enter_email_address, $lng_member_profile, removed $lng_from_colon by ejkv

	if ($cUser->person[0]->email=="") { // added check for email-address of sender, display error if no email - by ejkv
		$output = $lng_problem_sending_mail."<br>".$lng_enter_email_address.": ".$lng_member_profile;
		$p->DisplayPage($output);
		return;
	}

	$body = wordwrap($values["message"], 64); // replaced $copy by $body with message-body - by ejkv
/*	if (!SAFE_MODE_ON) { // - added safe mode check by ejkv
		if($values["cc"] == "Y") {
			$body = "Cc: ". $cUser->person[0]->email . "\r\n\r\n" . wordwrap($values["message"], 64);
	    }
    }*/

    if(known_email_addressp($_REQUEST["email_to"])) {
    	// added SAFE_MODE check, and removed 5th parameter, if safe mode = ON - by ejkv
	   // replaced message-body by $body, $lng_from_colon by "From:" and '. $copy' by ', $cUser->person[0]->email' - by ejkv
      if (SAFE_MODE_ON) {
			$mailed = mailex($_REQUEST["email_to"], 
								   $values["subject"], 
								   $body, 
								   $cUser->person[0]->email);
		}
		else { // SAFE_MODE_ON = off
			if($values["cc"] == "Y") {
				$mailed = mailex($_REQUEST["email_to"], 
									   $values["subject"], 
									   $body, 
									   $cUser->person[0]->email, 
									   true);
			}
			else {
				$mailed = mailex($_REQUEST["email_to"], 
									   $values["subject"], 
									   $body, 
									   $cUser->person[0]->email);
			}
		}
    }
    else {
        $mailed = false;
    }

	if($mailed) {
		$output = $lng_your_message_send;
    }
	else {
		$output = $lng_problem_sending_mail." ".$lng_try_again_later;	
    }

	$p->DisplayPage($output);
}


/**
 * Checks whether the given email address exists in the database.
 */
function known_email_addressp($email) {
    global $cDB;

    $email = $cDB->EscTxt($email);
    $sql = "SELECT person_id FROM " . DATABASE_PERSONS .
                                                 " WHERE email = $email";
    $r = $cDB->Query($sql);
    if($row = mysql_fetch_array($r)) {
        return true;
    }
    else {
        return false;
    }
}

?>
