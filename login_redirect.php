<?php
include_once("includes/inc.global.php");
$p->site_section = SITE_SECTION_OFFER_LIST;

$output = $lng_need_to_logged_on."<BR><BR>"."<DIV STYLE='width=60%; padding: 5px;'><FORM ACTION="."login.php METHOD=POST><INPUT TYPE=HIDDEN NAME=action VALUE=login><INPUT TYPE=HIDDEN NAME=location VALUE='".$_SESSION["REQUEST_URI"]."'><TABLE class=NoBorder><TR><TD ALIGN=left>".$lng_member_id.":</TD><TD ALIGN=LEFT><INPUT TYPE=TEXT SIZE=12 NAME=user></TD></TR><TR><TD ALIGN=RIGHT>".$lng_pwd.":</TD><TD ALIGN=LEFT><INPUT TYPE=PASSWORD SIZE=12 NAME=pass></TD></TR></TABLE><DIV align='left'><INPUT TYPE=SUBMIT VALUE=".$lng_login."></DIV></FORM></DIV><BR>".$lng_if_dont_account_please." <A HREF=contact.php>".$lng_contact."</A> ".$lng_us_to_join.".<BR>";

$p->DisplayPage($output);

?>
