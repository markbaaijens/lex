<?php
include_once("includes/inc.global.php");
$p->site_section = SITE_SECTION_OFFER_LIST;

if($cUser->IsLoggedOn()) {
	
	if ($cUser->AccountIsRestricted())
		$list .= $lng_hi."<p>".LEECH_NOTICE;
	else {
		// Redirect to a usefull page
		if ($cUser->IsLevel(2))
		   // For admins, the admin menu is shown				
			header('Location: ./admin_menu.php');
		else 		
		   // For regular users, a dashboard is shown
			header('Location: ./index.php');					
	}			
}
else {
	$list = $cUser->UserLoginPage();
}

$p->DisplayPage($list);

?>
