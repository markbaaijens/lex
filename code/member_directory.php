<?php

include_once("includes/inc.global.php");
$p->site_section = SECTION_DIRECTORY;
$p->page_title = $lng_member_directory;

$cUser->MustBeLoggedOn();

include_once("classes/class.listing.php");

// Global state list 
$states = new cStateList; 
$state_list = $states->MakeStateArray();
$state_list[0]="---";

$output = "<div align=\"right\"><A HREF=member_report.php>".$lng_download_member_report."</A><br></div>";

// Search function
if (SEARCHABLE_MEMBERS_LIST==true) {

	$output .= "<DIV STYLE='width=60%; padding: 5px;'><form action=member_directory.php method=get>";
	$output .= "<TABLE class=NoBorder>";
	$output .= "<TR><TD ALIGN=LEFT>".$lng_member_id.": </TD>
	            <TD ALIGN=LEFT><input type=text name=uID size=8 value='".$_REQUEST["uID"]."'></TD></TR>";
	$output .= "<TR><TD ALIGN=LEFT>".$lng_name_all_or_part.": </TD>
	            <TD ALIGN=LEFT><input type=text name=uName value='".$_REQUEST["uName"]."'></TD></TR>";
	$output .= "<TR><TD ALIGN=LEFT>".$lng_location_eg.": </TD>
	            <TD ALIGN=LEFT><input type=text name=uLoc value='".$_REQUEST["uLoc"]."'></TD></TR>";

	// States
	$output .= "<TR><TD ALIGN=LEFT>".STATE_TEXT.": </TD><TD ALIGN=LEFT>";
	$output .= "<select name='uState'>";
	for ($i = 0; $i < count($state_list); $i++) {	
		$selected = "";
		if ($_REQUEST["uState"] == $i)
			$selected = "selected=\"selected\"";	
	
		$output .= "   <option value='$i' $selected>".$state_list[$i]."</option>";
	}			            	
	$output .= "</select></TD></TR>";

	// Sort
	$orderBySel = array();
	$orderBySel["".$_REQUEST["orderBy"].""]='selected';
	
	$output .= "<TR><TD ALIGN=LEFT>".$lng_order_by.": </TD><TD ALIGN=LEFT>
	            <select name='orderBy'>
		            <option value='idA' ".$orderBySel["idA"].">".$lng_membership_no."</option>
		            <option value='fl' ".$orderBySel["fl"].">".$lng_first_name."</option>
		            <option value='lf' ".$orderBySel["lf"].">".$lng_last_name."</option>
		            <option value='nh' ".$orderBySel["nh"].">".$lng_neighbourhood."</option>
		            <option value='loc' ".$orderBySel["loc"].">".$lng_town."</option>
		            <option value='pc' ".$orderBySel["pc"].">".$lng_postcode."</option>
		          </select></TD></TR>";
		          		          
	$output .= "</TABLE>"; 
	$output .= "<p><input type=submit value=".$lng_search.">"; 
	$output .= "</form></DIV>"; 	
}

$output .= "<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=\"100%\">
  <TR BGCOLOR=\"#d8dbea\">
    <TD width=\"35%\"><FONT SIZE=2><B>".$lng_member."</B></FONT></TD>
    <TD width=\"30%\"><FONT SIZE=2><B>".$lng_contact_information_cap."</B></FONT></TD>
    <TD width=\"30%\"><FONT SIZE=2><B>" . ADDRESS_LINE_3 . "</B></FONT></TD>";

if (MEM_LIST_DISPLAY_BALANCE==true || $cUser->member_role >= 1)  {   
	$output .= "<TD ALIGN=RIGHT><FONT SIZE=2><B>".$lng_balance."</B></FONT></TD>";
}
$output .= "</TR>";

//Phones (comma separated with first name in parentheses for non-primary phones)
//Emails (comma separated with first name in parentheses for non-primary emails)

$member_list = new cMemberGroup();

// How should results be ordered?
switch($_REQUEST["orderBy"]) {
	
	case("pc"):
		$orderBy = 'ORDER BY address_post_code asc';
	break;
	
	case("nh"):
		$orderBy = 'ORDER BY address_state_code asc';
	break;
	
	case("loc"):
		$orderBy = 'ORDER BY address_city asc';
	break;
	
	case("fl"):
		$orderBy = 'ORDER BY first_name, last_name';
	break;
	
	case("idA"):
		$orderBy = 'ORDER BY member_id asc'; 
	break;
	
	case("lf"):
		$orderBy = 'ORDER BY last_name, first_name';
	break;
	
	default:
		$orderBy = 'ORDER BY member_id asc';
	break;
}

// SQL condition string
$condition = '';

function buildCondition(&$condition,$wh) { // Add a clause to the SQL condition string
	$condition .= " AND ";
	$condition .= " ".$wh. " ";	
}

if ($_REQUEST["uID"]) // We' re searching for a specific member ID in the SQL
	buildCondition($condition,"member.member_id like '%".trim($_REQUEST["uID"])."%'");

if ($_REQUEST["uName"]) { // We're searching for a specific username in the SQL
	
	$uName = trim($_REQUEST["uName"]);

	// Does it look like we've been provided with a first AND last name?
	$uName = explode(" ",$uName);
	
	$nameSrch = "person.first_name like '%".trim($uName[0])."%'";
	
	if ($uName[1]) { // surname provided
		
		$nameSrch .= " OR person.last_name like '%".trim($uName[1])."%'";
		
	}
	else // No surname, but term entered may be surname so apply to that too
		$nameSrch .= " OR person.last_name like '%".trim($uName[0])."%'";
		
	buildCondition($condition,"(".$nameSrch.")");
}

if ($_REQUEST["uLoc"]) // We're searching for a specific Location in the SQL
	buildCondition($condition,"(person.address_post_code like '%".trim($_REQUEST["uLoc"])."%' OR person.address_state_code like '%".trim($_REQUEST["uLoc"])."%' OR person.address_city like '%".trim($_REQUEST["uLoc"])."%' OR person.address_country like '%".trim($_REQUEST["uLoc"])."%')"); 

if ($_REQUEST["uState"]) // We' re searching for a specific state in the SQL
	if ($_REQUEST["uState"] <> 0)
		buildCondition($condition,"person.address_state_code = '".trim($_REQUEST["uState"])."'");
	
// Do search in SQL
$query = $cDB->Query("SELECT ".DATABASE_MEMBERS.".member_id FROM ". DATABASE_MEMBERS .",". DATABASE_PERSONS." WHERE ". DATABASE_MEMBERS .".member_id=". DATABASE_PERSONS.".member_id AND primary_member='Y' ".$condition." $orderBy;");
		
$i=0;

while($row = mysql_fetch_array($query)) // Each of our SQL results
{
	$member_list->members[$i] = new cMember;			
	$member_list->members[$i]->LoadMember($row[0]);
	$i += 1;
}
		
$i=0;

if($member_list->members) {

	foreach($member_list->members as $member) {
		// RF next condition is a hack to disable display of inactive members
		if($member->status != "I" || SHOW_INACTIVE_MEMBERS==true)  { 
		  // force display of inactive members off, unless specified otherwise in config file		
			if($member->account_type != "F") {  // Don't display fund accounts
				
				if($i % 2)
					$bgcolor = "#e4e9ea";
				else
					$bgcolor = "#FFFFFF";

        // Show photo-icon if member has profile photo
        // Function file_exists() can not handle wildcards, so we have to use glob()
        $member_photo_list = glob("uploads/"."mphoto_".$member->member_id.".*");
        if (!empty($member_photo_list)) {
          $member_photo_url = $member_photo_list[0];
        } else {
			 $member_photo_url = DEFAULT_PHOTO;          
        }
        $photo_icon = "<img src=\"".$member_photo_url."\""." align=\"left\" width=\"40\" heigth=\"40\" border=\"1\"/>";                
        unset($member_photo_list);
        
        // Show listing-icon if member has any     
        $listings = new cListingGroup(OFFER_LISTING);
        $listings->LoadListingGroup(null, null, $member->member_id, null, false); // Exclude expired listings (par5)
        if ($listings->num_listings > 0) {
			 $listing_icon_title = $lng_offers . " (". $listings->num_listings . ")";   
			 // Combine all the listings title's to a single text 
			 foreach ($listings->listing as $listing)
  			   $listing_icon_title .= " | ".$listing->title; 		 
          $listing_icon = "<img src=\"images/cart.png\" width=\"21\" align=\"top\" title=\"". $listing_icon_title ."\"/>";
        }        
        else
          $listing_icon = "";   
        unset($listings);  

        // Shorten description of email because long addresses forces the (contact) column too
        // broad: then there is not much left for the other columns 
		  $member_email_desc = $member->person[0]->email; 
		  if (strlen($member_email_desc) > 27) {
			  $member_email_desc = substr($member_email_desc, 0, 25) . "...";
		  }
        
				$state_id = $member->person[0]->address_state_code;
				$state_desc = "";
            if ($state_id <> 0) {
            	// Link in output (see below) will only shown if $state_desc has a value
					$state_desc = $state_list[$state_id];				 
				}          		
				$output .= 
					// Column 1: member-info
					"<TR VALIGN=TOP BGCOLOR=". $bgcolor .">
					  <TD><FONT SIZE=2>".
						  $photo_icon .
					      "<b>". $member->AllNames()."</b><br>" .
					      $member->MemberLink() . "&nbsp; $listing_icon".
	   	       "</FONT></TD>".
  					 // Column 2: contact-info
					  "<TD><FONT SIZE=2>". 
					   	"<A HREF=email.php?email_to=".$member->person[0]->email.
						      "&member_to=". $member->member_id . " title=\"".$member->person[0]->email."\" >".
						      $member_email_desc . "</a>". "<br>".
     					   $member->AllPhones() .
				    "</FONT></TD>".
  					 // Column 3: location-info				    
					  "<TD><FONT SIZE=2>". $member->person[0]->address_street1."<br>".
					   	$member->person[0]->address_post_code . " " .
					   	$member->person[0]->address_city . "<br>" . 
					      "<a href=\"member_directory.php?uState=".$state_id."\">".
	  					       $state_desc . "</a>".
  					  "</FONT></TD>";					   				   
				
				if (MEM_LIST_DISPLAY_BALANCE==true || $cUser->member_role >= 1) {
					$output .= "<TD ALIGN=RIGHT><FONT SIZE=2>";
  					$output .= $member->FormattedBalance();
					$output .= "</FONT></TD>";
        }					
				$output .= "</TR>";
				$i+=1;
		  }
	  } // end loop to force display of inactive members off
  }
} 

// RF display active accounts 
$output .= "<TR><TD colspan=5><br><br>".$lng_total_of." ".$i." ".$lng_active_accounts.".</TD></TR></TABLE>";

$p->DisplayPage($output); 

include("includes/inc.events.php");
?>
