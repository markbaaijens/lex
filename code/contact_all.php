<?php
include_once("includes/inc.global.php");
$p->site_section = SECTION_EMAIL;
$p->page_title = $lng_email_all_members;

$cUser->MustBeLevel(2);

include("includes/inc.forms.php");

//
// First, we define the form
//
$form->addElement("header", null, $lng_this_email_goes_to_all_members." ".SITE_LONG_TITLE.".", null);
$form->addElement("static", null, null, null);
// This element (email_from) cannot simply converted to a static one: if the data (form variable EMAIL_FROM) contains
// double quotes ", the text/data is malformed and the mail will NOT be send.
$form->addElement("text", "email_from", $lng_email_from_address, array("size" => 50, "maxlength" => 50)); 
$form->addElement("text", "subject", $lng_subject, array("size" => 50, "maxlength" => 50));
$form->addElement("static", null, null, null);
$form->addElement("textarea", "message", $lng_your_message, array("cols"=>65, "rows"=>10, "wrap"=>"soft"));
$form->addElement("static", null, null, null);

$form->addElement("static", null, null, null);
$form->addElement("submit", "btnSubmit", $lng_send);

//
// Define form rules
//
$form->addRule("subject", $lng_enter_subject, "required");
$form->addRule("message", $lng_enter_message, "required");

if ($form->validate()) { 
  // Form is validated so processes the data
  $form->freeze();
 	$form->process("process_data", false);
} else {  
  // Set default Values
  $current_values["email_from"] = EMAIL_FROM;
	$form->setDefaults($current_values);  
	
	// Display the form
	$p->DisplayPage($form->toHtml());
}

//
// The form has been submitted with valid data, so process it   
//
function process_data ($values) {
	global $p, $heard_from, $lng_message_send_to_all_members, $lng_errors_sending_mail_to_colon; 
	
	$output = "";
	$errors = "";
	$all_members = new cMemberGroup;
	$all_members->LoadMemberGroup();
	
	foreach($all_members->members as $member) {
		if($errors != "")
			$errors .= ", ";
		
		if($member->person[0]->email != "") {
		  $email_from = $values["email_from"];
			if ($email_from == "") 
			  // Fill email_from from a sensible default, otherwise there is a chance this will not be send or 
			  // otherwise will be marked as spam by the provider
			  $email_from = EMAIL_FROM;
  		$mailed = mail($member->person[0]->email, $values["subject"], wordwrap($values["message"], 64) , "From:". $email_from); 
		} 
		else
			$mailed = true;
		
		if(!$mailed)
			$errors .= $member->person[0]->email;
	}
	if($errors == "")
		$output .= $lng_message_send_to_all_members;
	else
		$output .= $lng_errors_sending_mail_to.":<BR>". $errors;	
		
	$p->DisplayPage($output);
}


?>
