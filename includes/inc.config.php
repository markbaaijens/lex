<?php
/* v1.0 note: a lot of these settings are now stored in MySQL and are configurable from the admin menu */

if (!isset($global) && $running_upgrade_script!=true)
{
	die(__FILE__." was included directly.  This file should only be included via inc.global.php.  Include() that one instead.");
}

if (file_exists("upgrade.php") && $running_upgrade_script!=true) {
	
	die("<font color=red>The file 'upgrade.php' was located on this server.</font>
	<p>If you are in the process of upgrading, that's fine, please <a href=upgrade.php>Click here</a> to run the upgrade script.<p>If you are NOT in the process of upgrading then leaving this file on the server poses a serious security hazard. Please remove this file immediately.");
}

/**********************************************************/
/******************* SITE LOCATIONS ***********************/

// The following only needs to be set if Pear has been
// installed manually by downloading the files
define ("PEAR_PATH", "/pear"); // no ending slash

// Ok, then lets define some paths (no need to edit these)
define ("CLASSES_PATH","classes/");
define ("IMAGES_PATH","images/");
define ("UPLOADS_PATH","uploads/");

/**********************************************************/
/***************** DATABASE LOGIN  ************************/

define ("DATABASE_USERNAME","root");
define ("DATABASE_PASSWORD","root");
define ("DATABASE_NAME","lex");
define ("DATABASE_SERVER","localhost"); // often "localhost"

/**********************************************************/
/********************* SITE NAMES *************************/

// What is the name of the site?
define ("SITE_LONG_TITLE", "Ruilsysteem van de Bossche Ruilkring");

// What is the short, friendly, name of the site?
define ("SITE_SHORT_TITLE", "Lekker ruilen, voor niks!");

/**********************************************************/
/***************** FOR MAINTENANCE ************************/

// If you need to take the website down for maintenance (such
// as during an upgrade), set the following value to true
// and customize the message, if you like

define ("DOWN_FOR_MAINTENANCE", false);
define ("MAINTENANCE_MESSAGE", SITE_LONG_TITLE ." ".$lng_currently_down_for_maintenance.$lng_try_again_later);


/***************************************************************************************************/
/***************** 01-12-08 - 19-12-08 Chris Macdonald (chris@cdmweb.co.uk) ************************/

// The following preferences can be set to turn on/off any of the new features

/* Set the MINIMUM Permission Level a member must hold to be able to submit ANY and ALL HTML
 * 0 = Members, 1 = Committee, 2 = Admins 
 * Note: This group will be allowed to submit any HTML tags and will not be restricted by the 'Safe List' defined below */
define("HTML_PERMISSION_LEVEL",1);

// ... HTML Safe List - define the tags that you want to allow all other users (who are below HTML_PERMISSION_LEVEL) to submit
//  Note the format should be just the tag name itself WITHOUT brackets (i.e. 'table' and not '<table>')
$allowedHTML = array('em','i','b','a','br','ul','ol','li','center','img','p');
// [TODO] Taking this a step further we could also specify whether or not a tag is allowed with parameters - currently by default parameters are allowed  

// Should we remove any JavaScript found in incoming data? Yes we should.
define("STRIP_JSCRIPT",true);

// Member images are resized 'on-the-fly', keeping the original dimensions. Specify the maximum width the image is to be DOWN-sized to here.
define("MEMBER_PHOTO_WIDTH",200); // in pixels
define("DEFAULT_PHOTO","images/localx_logo.png"); // default photo, or picture - added by ejkv
// Do we want to UP-scale images that are smaller than MEMBER_PHOTO_WIDTH (may look a bit ugly and pixelated)?
define("UPSCALE_SMALL_MEMBER_PHOTO",false);

// The options available in the 'How old is you?' dropdown (trying to be as innocuous as possible here with the defaults (e.g. 40's)- but feel free to provide more specific options)
$agesArr = array('---',$lng_under18,$lng_age18_30,$lng_age30s,$lng_age40s,$lng_age50s,$lng_age60s,$lng_age70s,$lng_over80,$lng_na,);

// The options available in the 'What Sex are you?' dropdown. At the time of writing (01-12-2008) the defaults should be fine
$sexArr = array('---', $lng_male, $lng_female, $lng_na,);

// Enable JavaScript bits on the Dropdown Member Select Box?
// This applies to the Transfer form; the idea is that it makes it simpler to find the member we're after if the dropdown list is lengthy
define("JS_MEMBER_SELECT",true);
// [TODO] Need to make this better - AJAX is probably the best method for this

// Give the option of searching Offers/Wants by KEYWORD?
define("KEYWORD_SEARCH_DIR",true);

// Allow members to Search the Members List? (Handy if the members list is long)
define("SEARCHABLE_MEMBERS_LIST",true);


// END 01-12-08 changes by chris

/**************************************************************/
/******************** SITE CUSTOMIZATION **********************/

// email addresses & phone number to be listed in the site
define ("EMAIL_FEATURE_REQUEST",""); // (is this actually used anywhere???)
define ("EMAIL_ADMIN","info@niksvoorniks.nl"); // email address Sanne / Marjan

define ("PHONE_ADMIN",""); // an email address may be substituted...

// What should appear at the front of all pages?
// Titles will look like "PAGE_TITLE_HEADER - PAGE_TITLE", or something 
// like "Local Exchange - Member Directory";
define ("PAGE_TITLE_HEADER", SITE_LONG_TITLE);

// What keywords should be included in all pages?
define ("SITE_KEYWORDS", $lng_sitekeywords. SITE_LONG_TITLE .",php");

// Logo Graphic for Header
define ("HEADER_LOGO", "logo_niksvoorniks.gif"); // changed by ejkv

// Title Graphic for Header
define ("HEADER_TITLE", "localx_title.png");

// Logo for Home Page
define ("HOME_LOGO", "localx_black.png");

// Picture appearing left of logo on Home Page
define ("HOME_PIC", "localx_home.png");

// What content should be in the site header and footer?
define ("PAGE_HEADER_CONTENT", "<table align=center cellpadding=15 cellspacing=0 id=\"mainTable\"><tr><td id=\"header\" align=center><a href=\"index.php\"><img src=\"" . "images/". HEADER_LOGO ."\" alt=\"". SITE_SHORT_TITLE . " logo\" border=0></a></td><td id=\"header\"><h1 align=right><img src=\"" . "images/". HEADER_TITLE ."\"></h1></td></tr>");

define ("PAGE_FOOTER_CONTENT", "<tr><td id=\"footer\" colspan=2><p align=center><strong>". SITE_LONG_TITLE ." </strong><br><a href=\"mailto:". EMAIL_ADMIN ."\">" . EMAIL_ADMIN ."</a> &#8226; ". PHONE_ADMIN ."<br><font size=\"-2\">".$lng_licenced_under." <a href=\"http://www.gnu.org/copyleft/gpl.html\">".$lng_gpl."</a> &#8226; ".$lng_local_exchange_uk_ver." ".LOCALX_VERSION." <a href=\""."info/credits.php\">".$lng_credits."</a></td></tr></table><br>");

/**********************************************************/
/**************** DEFINE SIDEBAR MENU *********************/

$SIDEBAR = array (
	array($lng_home,"index.php"),
	array($lng_learn_more,"info/more.php"), // old style info pages
// [CDM] uncomment line below to activate new style info pages 	
//  array("Information","pages.php?id=1"),
	array($lng_news_and_events,"news.php"),
	array($lng_offered,"listings.php?type=Offer"),
	array($lng_wanted,"listings.php?type=Want"),
	array($lng_update_listings,"listings_menu.php"),
	array($lng_exchanges,"exchange_menu.php"),
	array($lng_members_list,"member_directory.php"),
	array($lng_member_profile,"member_profile.php"),
	array($lng_contact_us,"contact.php"));
	
/**********************************************************/
/**************** DEFINE SITE SECTIONS ********************/

define ("EXCHANGES",0);
define ("LISTINGS",1);
define ("EVENTS",2);
define ("ADMINISTRATION",3);
define ("PROFILE",4);
define ("SECTION_FEEDBACK",5);
define ("SECTION_EMAIL",6);
define ("SECTION_INFO",7);
define ("SECTION_DIRECTORY",8);

$SECTIONS = array (
	array(0, "Exchanges", "exchange.gif"),
	array(1, "Listings", "listing.png"),
	array(2, "Events", "news.png"),
	array(3, "Administration", "admin.png"),
	array(4, "Events", "member.png"),
	array(5, "Feedback", "feedback.png"),
	array(6, "Email", "contact.png"),
	array(7, "Info", "info.png"),
	array(8, "Directory", "directory.png"));

/**********************************************************/
/******************* GENERAL SETTINGS *********************/

define ("UNITS", $lng_local_currency);  // This setting affects functionality, not just text displayed, so if you want to use hours/minutes this needs to read "Hours" exactly.  All other unit descriptions are ok, but receive no special treatment (i.e. there is no handling of "minutes"). - changed by ejkv

/**************** Monthly fee related settings ********************/

define("SYSTEM_ACCOUNT_ID", "system");
$monthly_fee_exempt_list = array("ADMIN", SYSTEM_ACCOUNT_ID, "extra_admin"); // added extra_admin - by ejkv

// End of monthly fee related settings.

define ("MAX_FILE_UPLOAD","1000000"); // Maximum file size, in bytes, allowed for uploads to the server - changed from 5000000 into 1000000 by ejkv
									 
// The following text will appear at the beggining of the email update messages
define ("LISTING_UPDATES_MESSAGE", "<h1>".SITE_LONG_TITLE."</h1>".$lng_list_update_message_01." <a href=" . "member_edit.php?mode=self>".$lng_member_profile."</a> ".$lng_list_update_message_02);


// Should inactive accounts have their listings automatically expired?
// This can be a useful feature.  It is an attempt to deal with the 
// age-old local currency problem of new members joining and then not 
// keeping their listings up to date or using the system in any way.  
// It is designed so that if a member doesn't record a trade OR update 
// a listing in a given period of time (default is six months), their 
// listings will be set to expire and they will receive an email to 
// that effect (as will the admin).
define ("EXPIRE_INACTIVE_ACCOUNTS",false); 

// If above is set, after this many days, accounts that have had no
// activity will have their listings set to expire.  They will have 
// to reactiveate them individually if they still want them.
define ("MAX_DAYS_INACTIVE","180");  

// How many days in the future the expiration date will be set for
define ("EXPIRATION_WINDOW","15");	

// How long should expired listings hang around before they are deleted?
define ("DELETE_EXPIRED_AFTER","90"); 

// The following message is the one that will be emailed to the person 
// whose listings have been expired (a delicate matter).
define ("EXPIRED_LISTINGS_MESSAGE", $lng_hello.",\n\n".$lng_expire_listings_message_01." ".SITE_SHORT_TITLE." " .$lng_expire_listings_message_02." ". EXPIRATION_WINDOW ." ".$lng_expire_listings_message_03.".\n\n".$lng_expire_listings_message_04." ".SITE_LONG_TITLE." ".$lng_expire_listings_message_05." ".MAX_DAYS_INACTIVE." ".$lng_expire_listings_message_06.".\n\n".$lng_expire_listings_message_07." ".PHONE_ADMIN.".\n\n".$lng_expire_listings_message_08." ". EXPIRATION_WINDOW ." ".$lng_expire_listings_message_09." ". DELETE_EXPIRED_AFTER ." ".$lng_expire_listings_message_10."\n\n\n".$lng_instructions_to_reactivate_listings.":\n1) ".$lng_login_to_the_website."\n2) ".$lng_go_to_update_listings."\n3) ".$lng_select_listings."\n4) ".$lng_select_listing_to_edit."\n5) ".$lng_uncheck_box."\n6) ".$lng_press_update_button."\n7) ".$lng_repeat_for_all_listings."\n");

// The year your local currency started -- the lowest year shown
// in the Join Year menu option for accounts.
define ("JOIN_YEAR_MINIMUM", "2005");  

define ("DEFAULT_COUNTRY", "Nederland");
define ("DEFAULT_ZIP_CODE", "0000aa"); // This is the postcode - changed by ejkv
define ("DEFAULT_CITY", "City"); // changed by ejkv
define ("DEFAULT_STATE", "Wijk"); // changed by ejkv
define ("DEFAULT_PHONE_AREA", "0xx"); // changed by ejkv

// Should short date formats display month before day (US convention)?
define ("MONTH_FIRST", false);		

define ("PASSWORD_RESET_SUBJECT", $lng_your." ". SITE_LONG_TITLE ." ".$lng_account);
define ("PASSWORD_RESET_MESSAGE", $lng_your_password_for." ". SITE_LONG_TITLE ." ".$lng_has_has_been_reset." ".PHONE_ADMIN.".\n\n".$lng_your_user_id_and_pwrd_are_listed_in_this_email);
define ("NEW_MEMBER_SUBJECT", $lng_welcome_to." ". SITE_LONG_TITLE);
define ("NEW_MEMBER_MESSAGE", $lng_hello_and_welcome_to." ". SITE_LONG_TITLE ." ".$lng_community."\n\n".$lng_a_member_account_has_been_created.":\n"."member_login.php\n\n".$lng_please_login_and_create_listings."\n\n".$lng_thank_you_for_joining_us);

/********************************************************************/
/************************* ADVANCED SETTINGS ************************/
// Normally, the defaults for the settings that follow don't need
// to be changed.

// What's the name and location of the stylesheet?
define ("SITE_STYLESHEET", "style.css");

// How long should trades be listed on the "leave feedback for 
// a recent exchange" page?  After this # of days they will be
// dropped from that list.
define ("DAYS_REQUEST_FEEDBACK", "30"); 

// Is debug mode on? (display errors to the general UI?)
define ("DEBUG", false);

// Is site SAFE mode on? =>> Pear::(mail) using the 5th mail parameter causes error by sending mail - added by ejkv
// If SAFE_mode_ON, in email.php no CC: will be sent, nor will CC: selction be showed in UI
define ("SAFE_MODE_ON", false); // usually false (has to be set in site control panel, or by provider)

// Is Local Exchange used as an embedded site in an CMS site (e.g. Joomla) - added by ejkv
// If EMBEDDED use, the Local Exchange Header, Footer, and side-bar (menu) will not be shown - added by ejkv
define ("EMBEDDED", false); // usually false (when Local Exchange runs stand-alone (not embedded)

// Should adminstrative activity be logged?  Set to 0 for no logging; 1 to 
// log trades recorded by administrators; 2 to also log changes to member 
// settings (LEVEL 2 NOT YET IMPLEMENTED)
define ("LOG_LEVEL", 1);

// How many consecutive failed logins should be allowed before locking out an account?
// This is important to protect against dictionary attacks.  Don't set higher than 10 or 20.
define ("FAILED_LOGIN_LIMIT", 10);

// Are magic quotes on?  Site has not been tested with magic_quotes_runtime on, 
// so if you feel inclined to change this setting, let us know how it goes :-)
define ("MAGIC_QUOTES_ON",false);
set_magic_quotes_runtime (0);

// CSS-related settings.  If you'r looking to change colors, 
// best to edit the CSS rather than add to this...
$CONTENT_TABLE = array("id"=>"contenttable", "cellspacing"=>"0", "cellpadding"=>"3");

// System events are processes which only need to run periodically,
// and so are run at intervals rather than weighing the system
// down by running them each time a particlular page is loaded.
// System Event Codes (such as ACCOUNT_EXPIRATION) are defined in inc.global.php
// System Event Frequency (how many minutes between triggering of events)
$SYSTEM_EVENTS = array (
	ACCOUT_EXPIRATION => 1440);  // Expire accounts once a day (every 1440 minutes)


/**********************************************************/
//	Everything below this line simply sets up the config.
//	Nothing should need to be changed, here.

if (PEAR_PATH != "")
	ini_set("include_path", PEAR_PATH .'/' . PATH_SEPARATOR . ini_get("include_path"));


if (DEBUG) error_reporting(E_ALL);
	else error_reporting(E_ALL ^ E_NOTICE);

define("LOAD_FROM_SESSION",-1);  // Not currently in use

// URL to PHP page which handles redirects and such.
define ("REDIRECT_URL","redirect.php");

?>
