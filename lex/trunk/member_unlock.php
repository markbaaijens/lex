<?php
include_once("includes/inc.global.php");

$cUser->MustBeLevel(2);
$p->site_section = ADMINISTRATION;
$p->page_title = $lng_unlock_account_reset_pwd;

include("includes/inc.forms.php");

$form->addElement("header", null, $lng_this_form_unlocks_and_resets_pwd, null);
$form->addElement("static", null, null);
$ids = new cMemberGroup;
$ids->LoadMemberGroup(false);

$form->addElement("select", "member_id", $lng_choose_member_account, $ids->MakeIDArray(), array('style' => 'width: 22em;'));
$form->addElement("select", "emailTyp", $lng_emls, array("pword"=>$lng_send_pwd_reset_eml, "welcome"=>$lng_send_welcome_eml),array('style' => 'width: 22em;'));
$form->addElement("static", null, null, null);
$form->addElement("submit", "btnSubmit", $lng_unlock_and_reset);

if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form
	$p->DisplayPage($form->toHtml());
}

function process_data ($values) {
	global $p, $lng_member_locked_due_to, $lng_consecutive_login_failures,
        	$lng_has_been_unlocked_if_attempts_more_than, $lng_cause_could_indicate_hacker, 
        	$lng_pwd_has_been_reset, $lng_member_id, $lng_pwd, $lng_new_pwd, $lng_and_eml_sent_to_member, 
	        $lng_eml_new_pwd_failed, $lng_login; 
  	
	$list = "";
	$member = new cMember;
	$member->LoadMember($values["member_id"]);

	if($consecutive_failures = $member->UnlockAccount()) {
		$list .= $lng_member_locked_due_to." ". $consecutive_failures ." ".$lng_consecutive_login_failures." ". $lng_has_been_unlocked_if_attempts_more_than." ". PHONE_ADMIN ."</I>, ".$lng_cause_could_indicate_hacker."<P>";
	}

	$password = $member->GeneratePassword();
	$member->ChangePassword($password); // This will bomb out if the password change fails
	
	$list .= $lng_pwd_has_been_reset;
	
	if ($_REQUEST["emailTyp"]=='welcome') {
		
		$mailed = mailex($member->person[0]->email, 
  		                NEW_MEMBER_SUBJECT, 
	  	                NEW_MEMBER_MESSAGE . "<br><br>".
			                        $lng_member_id.": ". $values['member_id']."<br>". 
			                        $lng_pwd.": ". $password."<br><br>".
			                        "<a href=\"".server_base_url()."/member_login.php\">".$lng_login."</a>");
	}
	else {
		$mailed = mailex($member->person[0]->email, 
		                  PASSWORD_RESET_SUBJECT, 
		                  PASSWORD_RESET_MESSAGE . "<br><br>".
    		                  $lng_member_id.": ". $member->member_id ."<br>".
	     	                  $lng_new_pwd.": ". $password."<br><br>".
	                        "<a href=\"".server_base_url()."/member_login.php\">".$lng_login."</a>");
	}

	if($mailed)
		$list .= " ".$lng_and_eml_sent_to_member." (". $member->person[0]->email."/".$password.").";
	else
		$list .= ". <I>".$lng_eml_new_pwd_failed." ". PHONE_ADMIN ."</I>.";	
	$p->DisplayPage($list);
}

?>
