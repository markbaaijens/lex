<?php
	include_once("includes/inc.global.php");
	
	$cUser->MustBeLoggedOn();
	$p->site_section = EXCHANGES;
	$p->page_title = $lng_exchange_history;
	
  // Only admin's have access to this option. Regular users are limited of 
  // seeing only their own transactions.
	$cUser->MustBeLevel(2);

	include("classes/class.trade.php");
	
	$from = new cDateTime($_REQUEST["from"]);
	$to = new cDateTime($_REQUEST["to"]);
	
	$output = "<B>".$lng_for_period_from." ". $from->ShortDate() ." ".$lng_to_until." ". $to->ShortDate() ."</B><P>";	

	$member_id = $_REQUEST["member_id"]; // added by ejkv
	if ($member_id == NULL) $member_id = "%"; // added by ejkv
	
	$trade_group = new cTradeGroup($member_id, $_REQUEST["from"], $_REQUEST["to"]); // replaced "%" by $member_id by ejkv
	$trade_group->LoadTradeGroup();
	$output .= $trade_group->DisplayTradeGroup();
	
	$p->DisplayPage($output);
	
?>
