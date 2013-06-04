<?php
include_once("includes/inc.global.php");
$p->site_section = PROFILE;
$p->page_title = $lng_member_profile;

$cUser->MustBeLoggedOn();

$list .= "<A HREF=password_change.php><FONT SIZE=2>".$lng_change_my_pwd."</FONT></A><BR>";
$list .= "<A HREF=member_edit.php?mode=self><FONT SIZE=2>".$lng_edit_my_pers_info."</FONT></A><BR>";

if (ALLOW_IMAGES==true)
	$list .= "<A HREF=member_photo_upload.php?mode=self><FONT SIZE=2>".$lng_upload_change_photo."</FONT></A><BR>";
$list .= "<br>";
$list .= "<b>".$lng_summary."</b><br>";	
$list .= "<a href=member_summary.php?member_id=".$cUser->member_id."><font size=2>".$lng_view_my_profile_as_others."</font></a><br>";
$list .= "<br>";
$list .= "<b>".$lng_joint."</b><br>";

$list .= "<A HREF=member_contact_create.php?mode=self><FONT SIZE=2>".$lng_add_joint_member_to_my_acc."</FONT></A><br>";
$list .= "<A HREF=member_contact_choose.php><FONT SIZE=2>".$lng_edit_delete_joint_member."</FONT></A><br>";
$list .= "<br>";

$list .= "<b>".$lng_create_an_income_share."</b><br>";
if (ALLOW_INCOME_SHARES==true)
	$list .= "<A HREF=income_ties.php><FONT SIZE=2>".$lng_manage_income_shares."</FONT></A><p>";
else	
  $list .= $lng_income_share_inactive."<br>";
	

$p->DisplayPage($list);

?>
