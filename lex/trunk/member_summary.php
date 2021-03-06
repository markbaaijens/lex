<?php

include_once("includes/inc.global.php");
include_once("classes/class.listing.php");
$p->site_section = PROFILE;

$cUser->MustBeLoggedOn();

$member = new cMember;
$member->LoadMember($_REQUEST["member_id"]);

$p->page_title = $lng_summary_for." ".$member->PrimaryName();

if ($cUser->IsLevel(2))	{
     $output  = "<img src=\"images/write.png\" width=\"16\" height=\"16\" align=\"center\"/> ";  
	   $output .= "<a href=member_edit.php?mode=admin&member_id=".$member->member_id.">".
	               $lng_edit_a_member_account."</a> | ";		
	   $output .= "<a href=member_photo_upload.php?mode=admin&member_id=".$member->member_id.">".
	               $lng_edit_a_member_photo."</a><br><br>";
	}
else {
	if ($cUser->member_id == $member->member_id) {
    $output  = "<img src=\"images/write.png\" width=\"16\" height=\"16\" align=\"center\"/> ";	  
	  $output .= "<a href=member_edit.php?mode=self>".$lng_edit_my_pers_info."</a> | ";
		$output .= "<a href=member_photo_upload.php?mode=self>".$lng_upload_change_photo."</a> | ";
		$output .= "<a href=password_change.php>".$lng_change_my_pwd."</a><br><br>";
	}
}
	
$output .= "<STRONG><I>".$lng_contact_information_cap."</I></STRONG><P>";

$output .= $member->DisplayMember($cUser);

$output .= "<BR><P><STRONG><I>".$lng_offerd_listings_cap."</I></STRONG><P>";
$listings = new cListingGroup(OFFER_LISTING);
$listings->LoadListingGroup(null, null, $_REQUEST["member_id"]);

$output .= "<table width=100%><tr valign=top><td width=50%>";
$output .= $listings->DisplayListingGroup();
$output .= "</td></tr></table>";

$output .= "<BR><P><STRONG><I>".$lng_wanted_listings_cap."</I></STRONG><P>";
$listings = new cListingGroup(WANT_LISTING);
$listings->LoadListingGroup(null, null, $_REQUEST["member_id"]);

$output .= "<table width=100%><tr valign=top><td width=50%>";
$output .= $listings->DisplayListingGroup();
$output .= "</td></tr></table>";

$p->DisplayPage($output); 

?>
