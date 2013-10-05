<?php
include_once("includes/inc.global.php");

$p->site_section = EXCHANGES;
$p->page_title = $lng_choose_time_period;

$cUser->MustBeLoggedOn();

include("includes/inc.forms.php");

$date_offset = '-1 month';

$form->addElement("hidden", "action", $_REQUEST["action"]);
$form->addElement("hidden", "mode", $_REQUEST["mode"]); 
$form->addElement("hidden", "member_id", $_REQUEST["member_id"]); 
$today = getdate();
$options = array('language'=> $lng_language, 'format' => 'dFY', 
                  'minYear' => $today['year']-3,
                  'maxYear' => $today['year']);
$form->addElement("date", "from", $lng_from_when, $options);
$form->addElement("date", "to", $lng_to_when, $options);
$form->addElement("static", null, null, null);
$form->addElement('submit', 'btnSubmit', $lng_submit);

if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form

  // The two dates differ with the given offset 
  $date_ref_from = getdate(strtotime($date_offset));
	$date_from = array("Y"=>$date_ref_from["year"], "F"=>$date_ref_from["mon"], "d"=>$date_ref_from["mday"]);
  $date_ref_to = getdate();
	$date_to = array("Y"=>$date_ref_to["year"], "F"=>$date_ref_to["mon"], "d"=>$date_ref_to["mday"]);	
	
	$form->setDefaults(array("from"=>$date_from, "to"=>$date_to));
	$p->DisplayPage($form->toHtml());
}

function process_data ($values) {
	global $cUser;
	
	$date = $values['from'];
	$from = $date['Y'] . '-' . $date['F'] . '-' . $date['d'];
	$date = $values['to']; 	
	$to = $date['Y'] . '-' . $date['F'] . '-' . $date['d'];

  // added trade history timeframe for specific member (from / to) 
	if ($_REQUEST["mode"]==NULL) // added by ejkv
		header("location:" . $_REQUEST["action"] .".php?from=".$from . "&to=". $to);
	else 
		header("location:" . "trade_history.php?mode=".$_REQUEST["mode"]."&member_id=".
		        $_REQUEST["member_id"]."&from=".$from . "&to=". $to);
	exit;	
} 

?>
