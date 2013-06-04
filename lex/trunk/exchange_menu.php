<?php
include_once("includes/inc.global.php");
include_once("classes/class.trade.php");
$p->site_section = EXCHANGES;
$p->page_title = $lng_exchanges;

$cUser->MustBeLoggedOn();

$pending = new cTradesPending($cUser->member_id);

$list  = "<A HREF=trade.php?mode=self><FONT SIZE=2>".$lng_record_exchange."</FONT></A><BR>";
$list .= "<A HREF=trades_pending.php><FONT SIZE=2>".$lng_invoices_transactions_pending."</a> (".$pending->numIn." ".$lng_require_action.")</FONT><br>";

$list .= "<A HREF=trade_history.php?mode=self><FONT SIZE=2>".$lng_view_balance_and_history."</FONT></A><BR>";

if ($cUser->IsLevel(2)) {
  $list .= "<br>";
  $list .= "<A HREF=timeframe_choose.php?action=trade_history_all><FONT SIZE=2>".$lng_view_trades_in_period."</FONT></A><br>";
  $list .= "<A HREF=trades_to_view.php><FONT SIZE=2>".$lng_view_members_history."</FONT></A><BR>";
}

if (SHOW_FEEDBACK == 1) {
  $list .= "<br>";
  $list .= "<b>".$lng_feedback_rating."</b><br>";
  $list .= "<A HREF=feedback_all.php?mode=self><FONT SIZE=2>".$lng_view_my_feedback."</FONT></A><BR>";
  $list .= "<A HREF=feedback_to_view.php><FONT SIZE=2>".$lng_view_members_feedback."</FONT></A><BR>";
  $list .= "<A HREF=feedback_choose.php?mode=self><FONT SIZE=2>".$lng_leave_feedback_for_recent_exchange."</FONT></A><BR>";
}

$p->DisplayPage($list);

?>
