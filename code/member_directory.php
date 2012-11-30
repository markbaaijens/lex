<?php

include_once("includes/inc.global.php");
$p->site_section = SECTION_DIRECTORY;
$p->page_title = $lng_member_directory;

$cUser->MustBeLoggedOn();

//include_once("classes/class.listing.php");

//[chris] Search function
if (SEARCHABLE_MEMBERS_LIST==true) {
	
	$output = "<form action=member_directory.php method=get>";
	$output .= $lng_member_id.": <input type=text name=uID size=4 value='".$_REQUEST["uID"]."'>
		<br>".$lng_name_all_or_part.": <input type=text name=uName value='".$_REQUEST["uName"]."'>
		<br>".$lng_location_eg." ".": <input type=text name=uLoc value='".$_REQUEST["uLoc"]."'>";
	
	$orderBySel = array();
	$orderBySel["".$_REQUEST["orderBy"].""]='selected';
	
// swapped option value 'idA', 'fl', and 'lf' by ejkv
	$output .= "<br>".$lng_order_by.": <select name='orderBy'>
		<option value='idA' ".$orderBySel["idA"].">".$lng_membership_no."</option>
		<option value='fl' ".$orderBySel["fl"].">".$lng_first_name."</option>
		<option value='lf' ".$orderBySel["lf"].">".$lng_last_name."</option>
		<option value='nh' ".$orderBySel["nh"].">".$lng_neighbourhood."</option>
		<option value='loc' ".$orderBySel["loc"].">".$lng_town."</option>
		<option value='pc' ".$orderBySel["pc"].">".$lng_postcode."</option>
		</select>";
	$output .= "<p><input type=submit value=".$lng_search."></form>"; 
}

// added STATE_TEXT column by ejkv
$output .= "<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=\"100%\">
  <TR BGCOLOR=\"#d8dbea\">
    <TD><FONT SIZE=2><B>".$lng_member."</B></FONT></TD>
    <TD><FONT SIZE=2><B>".$lng_phone."</B></FONT></TD>
    <TD><FONT SIZE=2><B>" . ADDRESS_LINE_3 . "</B></FONT></TD>
	<TD><FONT SIZE=2><B>" . STATE_TEXT . "</B></FONT></TD>
    <TD><FONT SIZE=2><B>" . ZIP_TEXT . "</B></FONT></TD>";

if (MEM_LIST_DISPLAY_BALANCE==true || $cUser->member_role >= 1)  {   
	$output .= "<TD ALIGN=RIGHT><FONT SIZE=2><B>".$lng_balance."</B></FONT></TD>"; // added ALIGN=RIGHT by ejkv

}
$output .= "</TR>";

//Phones (comma separated with first name in parentheses for non-primary phones)
//Emails (comma separated with first name in parentheses for non-primary emails)

$member_list = new cMemberGroup();
//$member_list->LoadMemberGroup();

// How should results be ordered?
switch($_REQUEST["orderBy"]) {
	
	case("pc"):
		$orderBy = 'ORDER BY address_post_code asc';
	break;
	
	case("nh"):
		$orderBy = 'ORDER BY address_state_code asc'; // changed address_street2 into address_state_code - by ejkv
	break;
	
	case("loc"):
		$orderBy = 'ORDER BY address_city asc';
	break;
	
	case("fl"):
		$orderBy = 'ORDER BY first_name, last_name';
	break;
	
	case("idA"):
		$orderBy = 'ORDER BY member_id asc'; // changed idD into idA and Order ascending by ejkv
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
	
//	if (strlen($condition)>0)
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
	buildCondition($condition,"(person.address_post_code like '%".trim($_REQUEST["uLoc"])."%' OR person.address_state_code like '%".trim($_REQUEST["uLoc"])."%' OR person.address_city like '%".trim($_REQUEST["uLoc"])."%' OR person.address_country like '%".trim($_REQUEST["uLoc"])."%')"); // changed address_street2 into address_state_code - by ejkv
	
// DEBUG: 
//ECHO "SELECT ".DATABASE_MEMBERS.".member_id FROM ". DATABASE_MEMBERS .",". DATABASE_PERSONS." WHERE ". DATABASE_MEMBERS .".member_id=". DATABASE_PERSONS.".member_id AND primary_member='Y' ".$condition." $orderBy";

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
$state = new cStateList; // added by ejkv
$state_list = $state->MakeStateArray(); // added by ejkv
$state_list[0]="---"; // added by ejkv

if($member_list->members) {

	foreach($member_list->members as $member) {
		// RF next condition is a hack to disable display of inactive members
		if($member->status != "I" || SHOW_INACTIVE_MEMBERS==true)  { // force display of inactive members off, unless specified otherwise in config file
		
			if($member->account_type != "F") {  // Don't display fund accounts
				
				if($i % 2)
					$bgcolor = "#e4e9ea";
				else
					$bgcolor = "#FFFFFF";
		
				$output .= // added $state_list[$member->person[0]->address_state_code] by ejkv
					"<TR VALIGN=TOP BGCOLOR=". $bgcolor .">
					   <TD><FONT SIZE=2>". $member->AllNames()." (". $member->MemberLink() .")
					       </FONT></TD>
					   <TD><FONT SIZE=2>". $member->AllPhones() ."</FONT></TD>
					   <TD><FONT SIZE=2>". $member->person[0]->address_city . "</FONT></TD>
					   <TD><FONT SIZE=2>". $state_list[$member->person[0]->address_state_code] . "</FONT></TD>
					   <TD><FONT SIZE=2>". $member->person[0]->address_post_code ."</FONT></TD>";				   
				
				if (MEM_LIST_DISPLAY_BALANCE==true || $cUser->member_role >= 1) {
					$output .= "<TD ALIGN=RIGHT><FONT SIZE=2>";
					if (SHOW_UNITS_DECIMALS == 0)
  					$output .= round($member->balance);
  				else
   					$output .= $member->balance;					
					$output .= "</FONT></TD>";
        }					
				$output .= "</TR>";
				$i+=1;
		 }
	 } // end loop to force display of inactive members off
}
} 

// $output .= "</TABLE>";
// RF display active accounts 
$output .= "<TR><TD colspan=5><br><br>".$lng_total_of." ".$i." ".$lng_active_accounts.".</TD></TR></TABLE>";

$p->DisplayPage($output); 

include("includes/inc.events.php");
?>
