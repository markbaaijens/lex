<?php
include_once("includes/inc.global.php");
$p->site_section = SITE_SECTION_OFFER_LIST;

$output = $lng_need_to_logged_on."<BR><BR>".login_form("session");

$p->DisplayPage($output);

?>
