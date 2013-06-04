<?php

include_once("includes/inc.global.php");

$cUser->MustBeLevel(2);
$p->site_section = ADMINISTRATION;
$p->page_title = $lng_initial_mailing;

$subject = $lng_important_message_from." " . SITE_LONG_TITLE;
/*
$message = "Hello,\n\nThe new " . SITE_LONG_TITLE . " interactive website is now online!  You can now browse the directory, create and modify your listings, and exchange hours on the web.\n\nThe website address is the same (http://www.fourthcornerexchange.com) and your new userid and password are listed at the end of this message.  The password was automatically generated and we recommend you go to the Member Profile section and change it to something you can more easily remember.\n\nIf you have questions about the site you can reply to this email or call Calvin at 201-7361.";
*/

$message = "";

$all_members = new cMemberGroup();
$all_members->LoadMemberGroup();

$output = "";

foreach ($all_members->members as $member) {
	
	$password = $member->GeneratePassword();
	$changed = $member->ChangePassword($password);
	
	if(!$changed) {
		$output .= $lng_pwd_reset_for." '". $member->member_id ."'. ".$lng_skipped_email.".<BR>";
		continue;
	}

// $member->person[0]->email
	$mailed = mailex($member->person[0]->email, 
	                  $subject, 
	                  $message . "\n\n".$lng_member_id.": ". $member->member_id ."\n". $lng_pwd.": ". $password);

	if(!$mailed)
		$output .= $lng_could_not_email." ". $member->member_id .".  ".$lng_his_her_pwd_is." '". $password ."'.<BR>";
}

if($output == "")
	$output = $lng_email_send_all_members;

$p->DisplayPage($output);


?>
