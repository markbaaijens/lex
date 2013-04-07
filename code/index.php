<?php

include_once("includes/inc.global.php");
include_once("classes/class.trade.php");

$list = "";
if (! $cUser->IsLoggedOn() or $cUser->IsLevel(2))
	// Show some generic info for guests; also for admins for which the dasboard is not very usefull
	$list .= PAGE_HOME_TEXT;
else {
	// Show user dashboard
	$member = $cUser;	
	$p->site_section = PROFILE; // Is required to display page title	
	$p->page_title = $lng_member." ".$member->PrimaryName();	
	
	// Some handy links
	$list .= "<a href=trade.php?mode=self>".$lng_record_exchange."</a> | ";
	$list .= "<a href=member_profile.php>".$lng_member_profile."</a> | ";
	$list .= "<a href=trade_history.php?mode=self>".
				 $lng_exchange_history."</a><br>";
	$list .= "<br>";	

	// Summary info	
	$list .= "<table width=100%><tr valign=top><td width=50%>";				
	$list .= "<strong>".$lng_balance_limits.":</strong> ";
	$list .= $member->FormattedBalance()." ".UNITS;				
	$list .= " (+".$member->MemberLimitMaxBalance()."/".$member->MemberLimitMinBalance().")<br>";					

	$pending = new cTradesPending($cUser->member_id);
	$list .= "<strong>".$lng_exchanges_pending.":</strong> ";
	$list .= "<a href=trades_pending.php title=\"".$lng_invoices_transactions_pending."\">".
	 				$pending->numIn." ".$lng_require_action."</a><br>";	
	 				
	$stats = new cTradeStats($member->member_id);
	$list .= "<strong>".$lng_activity.":</strong> ";

	if ($stats->most_recent == "")
		$list .= $lng_no_exchanges_yet."<br>";
	else		
		$list .= '<a href="trade_history.php?mode=self" title="'.$lng_exchange_history.'">'. $stats->total_trades ." ".
		$lng_exchanges_total."</a> ".$lng_sum_of." ". 
		$stats->total_units . " ". strtolower(UNITS) . ", ".
		$lng_last_on." ". $stats->most_recent->ShortDate() ."<br>";
	 				
	$list .= "</table>";	
	$list .= "<br>";
	
	// Recent transactions (last month)	
	$from_date = date(DATE_ATOM, mktime(0, 0, 0, date("m")-1, date("d"), date("Y")));	
	$to_date = FAR_FAR_AWAY;		
	$trade_group = new cTradeGroup($member->member_id, $from_date, $to_date); 
	$trade_group->LoadTradeGroup("individual");
	$list .= $lng_transactions_last_month.":";
	$list .= $trade_group->DisplayTradeGroup();
	
}

$p->DisplayPage($list);

?>
  
