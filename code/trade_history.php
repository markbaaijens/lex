<?php
	include_once("includes/inc.global.php");
	
	$cUser->MustBeLoggedOn();
	$p->site_section = EXCHANGES;
	$p->page_title = $lng_exchange_history;

	include("classes/class.trade.php");
	
	$member = new cMember;
	
	if($_REQUEST["mode"] == "self") {
		$member = $cUser;
	} else {
  	// trade history of other members only visible for Committee, and Admin
		if($_REQUEST["member_id"] != $cUser->member_id)
			$cUser->MustBeLevel(1); 
		$member->LoadMember($_REQUEST["member_id"]);
		$p->page_title .= " ".$lng_for." ".$member->PrimaryName();
	}
	
	if ($member->balance >= 0)
		$color = "black"; 
	else
		$color = "red"; 
	
	$from_date = $_REQUEST["from"];
	if ($_REQUEST["from"] == NULL ) $from_date = LONG_LONG_AGO;
	$from = new cDateTime($from_date);

	$to_date = $_REQUEST["to"];
	if ($_REQUEST["to"] == "" ) $to_date = FAR_FAR_AWAY;
	$to = new cDateTime($to_date);
	
	$list  = "<B>".$lng_currente_balance.": </B><FONT COLOR=". $color .">".
	          $member->FormattedBalance() . "</FONT> ". UNITS."<br>";
	          
  if (($from_date != LONG_LONG_AGO) and ($to_date != FAR_FAR_AWAY)) {
  	$list .= "<B>".$lng_for_period_from."</B>:  ". $from->ShortDate() ." ".$lng_to_until." ".
	          $to->ShortDate(); 
	}
	
	$list .= "<P>";

	$trade_group = new cTradeGroup($member->member_id, $from_date, $to_date); 
	$trade_group->LoadTradeGroup("individual");
	$list .= $trade_group->DisplayTradeGroup();
	
	$p->DisplayPage($list);

?>
