<?php

if (!isset($global))
{
	die(__FILE__." was included without inc.global.php being included first.  Include() that file first, then you can include ".__FILE__);
}

include_once("class.category.php");
include_once("class.feedback.php");
include_once("class.state_address.php"); // added by ejkv
require_once ("File/PDF.php");

class cListing
{
	var $member; // this will be an object of class cMember
	var $title;
	var $description;
	var $category; // this will be an object of class cCategory
	var $rate;
	var $status;
	var $posting_date; // the date a listing was created or last modified
	var $expire_date;
	var $reactivate_date;
	var $type;


	function cListing($member=null, $values=null) {
		if($member) {
			$this->member = $member;
			$this->title = $values['title'];
			$this->description = $values['description'];
			$this->rate = $values['rate'];
			$this->expire_date = $values['expire_date'];
			$this->type = $values['type'];
			$this->reactivate_date = null;
			$this->status = 'A';
			$this->category = new cCategory();
			$this->category->LoadCategory($values['category']);
		}
		
	}	

	function TypeCode() {
		if($this->type == OFFER_LISTING)
			return OFFER_LISTING_CODE;
		else
			return WANT_LISTING_CODE;			
	}

	function TypeDesc($type_code) {
		if($type_code == OFFER_LISTING_CODE)
			return OFFER_LISTING;
		else
			return WANT_LISTING;			
	}

	function SaveNewListing() {
		global $cDB, $cErr;		

		$insert = $cDB->Query("INSERT INTO ".DATABASE_LISTINGS." (title, description, category_code, member_id, rate, status, expire_date, reactivate_date, type) VALUES (". $cDB->EscTxt($this->title) .",". $cDB->EscTxt($this->description) .",". $cDB->EscTxt($this->category->id) .",". $cDB->EscTxt($this->member->member_id) .",". $cDB->EscTxt($this->rate) .",". $cDB->EscTxt($this->status) .",". $cDB->EscTxt($this->expire_date) .",". $cDB->EscTxt($this->reactivate_date) .",". $cDB->EscTxt($this->TypeCode()) .");");	

		return $insert;
	}			
		
	function SaveListing($update_posting_date=true) {
		global $cDB, $cErr;			
		
		if($update_posting_date) // changed posting date if update=true, due to the date a listing was modified - by ejkv
			$posting_date = ", posting_date='".date("Y-m-d h:i:s")."'"; // changed by ejkv
		else
			$posting_date = "";

		$update = $cDB->Query("UPDATE ".DATABASE_LISTINGS." SET title=". $cDB->EscTxt($this->title) .", description=". $cDB->EscTxt($this->description) .", category_code=". $cDB->EscTxt($this->category->id) .", rate=". $cDB->EscTxt($this->rate) .", status=". $cDB->EscTxt($this->status) .", expire_date=". $cDB->EscTxt($this->expire_date) .", reactivate_date=". $cDB->EscTxt($this->reactivate_date) . $posting_date ." WHERE title=". $cDB->EscTxt($this->title) ." AND member_id=". $cDB->EscTxt($this->member->member_id) ." AND type=". $cDB->EscTxt($this->TypeCode()) .";");	

		return $update;
	}
	
	function DeleteListing($title,$member_id,$type_code) {
		global $cDB, $cErr;
		
		$query = $cDB->Query("DELETE FROM ". DATABASE_LISTINGS ." WHERE title=".$cDB->EscTxt($title)." AND member_id=". $cDB->EscTxt($member_id) ." AND type=".  $cDB->EscTxt($type_code) .";");

		return mysql_affected_rows();
	}
							
	function LoadListing($title,$member_id,$type_code)
	{
		global $cDB, $cErr, $lng_error_access_the, $lng_listing_for, $lng_please_try_again_later;
		
		// select all offer data and populate the variables
		$query = $cDB->Query("SELECT description, category_code, member_id, rate, status, posting_date, expire_date, reactivate_date FROM ".DATABASE_LISTINGS." WHERE title=".$cDB->EscTxt($title)." AND member_id=" . $cDB->EscTxt($member_id) . " AND type=". $cDB->EscTxt($type_code) .";");
		
		if($row = mysql_fetch_array($query))
		{		
			$this->title=$title;
			$this->description=$cDB->UnEscTxt($row[0]);
			$this->member_id=$row[2];
			$this->rate=$cDB->UnEscTxt($row[3]);
			$this->status=$row[4];
			$this->posting_date=$row[5];
			$this->expire_date=$row[6];
			$this->reactivate_date=$row[7];
			$this->type=$this->TypeDesc($type_code);	
			$this->category = new cCategory();
			$this->category->LoadCategory($row[1]);		
		}
		else 
		{
			$cErr->Error($lng_error_access_the." ".$cDB->EscTxt($title)." ".$lng_listing_for." ".$member_id.".  ".$lng_please_try_again_later.".");
			include("redirect.php");
		}		
		
		// load member associated with member_id
		$this->member = new cMember;
		$this->member->LoadMember($member_id);
		
		$this->DeactivateReactivate();
	}
	
	function DeactivateReactivate() {
		if($this->reactivate_date) {
			$reactivate_date = new cDateTime($this->reactivate_date);
			if ($this->status == INACTIVE and $reactivate_date->Timestamp() <= strtotime("now")) {
				$this->status = ACTIVE;
				$this->reactivate_date = null;
				$this->SaveListing();
			}
		}
		if($this->expire_date) {
			$expire_date = new cDateTime($this->expire_date);
			if ($this->status <> EXPIRED and $expire_date->Timestamp() <= strtotime("now")) {
				$this->status = EXPIRED;
				$this->SaveListing();
			}
		}
	}
			
	function ShowListing()
	{
		$output = $this->type . "ed Data:<BR>";
		$output .= $this->title . ", " . $this->description . ", " . $this->category->id . ", " . $this->member->member_id . ", " . $this->rate . ", " . $this->status . ", " . $this->posting_date . ", " . $this->expire_date . ", " . $this->reactivate_date . "<BR><BR>";
		$output .= $this->member->ShowMember();
		
		return $output;
	}
	
	function DisplayListing()
	{   global $lng_description, $lng_rate;
		$output = "";
		if($this->description != "")
			$output .= "<div id=textblock>".nl2br($this->description)."</div>";
		$output .= "<small>".ShowRate($this->rate)."</small> ";				
		$output .= "<br><br><hr><br>";
		$output .= $this->member->DisplayMember();
		return $output;
	}	
}

class cListingGroup
{
	var $title;
	var $listing;  // this will be an array of objects of type cListing
	var $num_listings;  // number of active offers
	var $type;
	var $type_code;

	function cListingGroup($type) {
		$this->type = $type;
		if($type == OFFER_LISTING)
			$this->type_code = OFFER_LISTING_CODE;
		else
			$this->type_code = WANT_LISTING_CODE;		
	}
	
	function InactivateAll($reactivate_date) {
		global $cErr, $lng_could_not_inactivate_listing;
		
		if (!isset($this->listing))
			return true;
		
		foreach($this->listing as $listing)	{
			$current_reactivate = new cDateTime($listing->reactivate_date, false);
			if(($listing->reactivate_date == null or $current_reactivate->Timestamp() < $reactivate_date->Timestamp()) and $listing->status != EXPIRED) {
				$listing->reactivate_date = $reactivate_date->MySQLDate();
				$listing->status = INACTIVE;
				$success = $listing->SaveListing();
				
				if(!$success)
					$cErr->Error($lng_could_not_inactivate_listing.": '".$listing->title."'");
			}
		}
		return true;
	}
	
	function ExpireAll($expire_date) {
		global $cErr, $lng_could_not_expire_listing;
		
		if (!isset($this->listing))
			return true;
		
		foreach($this->listing as $listing)	{
			$listing->expire_date = $expire_date->MySQLDate();
			$success = $listing->SaveListing(false);
				
			if(!$success)
				$cErr->Error($lng_could_not_expire_listing.": '".$listing->title."'");
		}
		return true;
	}	
	
	function LoadListingGroup($title=null, $category=null, $member_id=null, $since=null, $include_expired=true)
	{
		global $cDB, $cErr;

		if($title == null)
			$this->title = "%";
		else
			$this->title = $title;
			
		if($category == null)
			$category = "%";
			
		if($member_id == null)
			$member_id = "%";
			
		if($since == null) 
			$since = "19990101000000";
			
		if($include_expired)
			$expired = "";
		else
			$expired = " AND expire_date is null";
			
		//select all the member_ids for this $title
		$query = $cDB->Query("SELECT title, member_id FROM ".DATABASE_LISTINGS.", ".DATABASE_CATEGORIES." WHERE title LIKE ". $cDB->EscTxt($this->title) ." AND ".DATABASE_LISTINGS.".category_code =".DATABASE_CATEGORIES.".category_id AND ".DATABASE_CATEGORIES.".category_id LIKE ". $cDB->EscTxt($category) ." AND type=". $cDB->EscTxt($this->type_code) ." AND member_id LIKE ". $cDB->EscTxt($member_id) ." AND posting_date >= '". $since ."'". $expired ." ORDER BY ".DATABASE_CATEGORIES.".description, title, member_id;");

		// instantiate new cOffer objects and load them
		$i = 0;
		$this->num_listings = 0;
				
		while($row = mysql_fetch_array($query))
		{
			$this->listing[$i] = new cListing;			
			$this->listing[$i]->LoadListing($row[0],$row[1],$this->type_code);
			if($this->listing[$i]->status == 'A')
			{
				$this->num_listings += 1;
			}
			$i += 1;
		}

		if($i == 0) {
			return false;
		}
		
		return true;
	}
	
	function DisplayListingGroup($show_ids=false, $active_only=true)
	{
		/*[chris]*/ // made some changes to way listings displayed, for better or for worse...
		
		global $cUser,$cDB, $lng_no_listings_found, $lng_learn_more;

		$output = "";
		$current_cat = "";

		if(isset($this->listing)) {
			foreach($this->listing as $listing) {
			
				if($active_only and $listing->status != ACTIVE)
					continue; // Skip inactive items
					
        // Category
				if($current_cat != $listing->category->id) {
					$output .= "<br><h2>" . $listing->category->description . "</h2>";
				}
				else
					$output .= "<br>";

        // Title
				$output .= "<b>".$listing->title."</b><br>";
				
        // Details
				if ($listing->description != "")
					$details = " ".  nl2br($listing->description); // RF - simple space is fine
				else
					$details = " --- "; 
				$output .= "<div id=textblock>".$details."</div>"; 

        // Rate
				$output .= "<small>".ShowRate($listing->rate)."</small> ";
										
				// Info-line: member
				$output .= "<small>"; 				

				$query = $cDB->Query("SELECT * FROM person WHERE member_id  = ". $cDB->EscTxt($listing->member_id) . " limit 0,1;");				
				$row = mysql_fetch_array($query);
				$memInfo = "<a href=member_summary.php?member_id=".
				       $listing->member_id.">".stripslashes($row["first_name"])." ".stripslashes($row["mid_name"])." ".
				       stripslashes($row["last_name"])." (". $listing->member_id .")</a> | ";
			
				if ($show_ids) {
          $output .= "<img src=\"images/member.png\" width=\"16\" height=\"16\" align=\"center\"/>";
					$output .= " "."$memInfo"." ";				
				
  				// Do we want to display the PostCode alongside the listing?
  				if (SHOW_POSTCODE_ON_LISTINGS==true) { // Only show postcode to logged in members
  
  					$pcode = stripslashes($row["address_post_code"]);
  					$pcode = str_replace(array("\n", "\r", "\t", " ", "\o", "\xOB"), '', $pcode); // Remove any white spaces as these will screw up our character count below
  					
  					$short_pcode = '';
  					
  					// Only display X number of characters from the postcode
  					for ($i=0;$i<(NUM_CHARS_POSTCODE_SHOW_ON_LISTINGS);$i++) {
  						$short_pcode .= $pcode{$i};						
  					}
  					$output .= " ".$short_pcode." | ";
          }					

          // Show state				
  				$states = new cStateList; // added by ejkv
	 			  $state_list = $states->MakeStateArray(); // added by ejkv
  				$state_list[0]="---"; // added by ejkv
  				$state_id = $row["address_state_code"];
  				$state_desc = $state_list[$state_id];
	
	   			$output .= "<a href=\"member_directory.php?uState=".$state_id."\">".$state_desc . "</a> ";				   			
				}

				// Info-line: more info
        $output .= "<img src=\"images/info.png\" width=\"16\" height=\"16\" align=\"center\"/>";
				$output .= "<a href=" . "listing_detail.php?type=". 
            				$this->type ."&title=" . urlencode($listing->title) ."&member_id=". $listing->member_id .">" . 
            				$lng_learn_more ."</a>";
				$output .= "</small><br>";            				 				
	
        // Prepare for next listing
				$current_cat = $listing->category->id;
				$current_title = $listing->title;
			}
		} 
		
		if($output == "")
			$output = $lng_no_listings_found;
	
								
		return $output;		
	}
}

class cTitleList  // This class circumvents the cListing class for performance reasons
{
	var $type;
	var $type_code;  // TODO: 'type' needs to be its own class which would include 'type_code'
	var $items_per_page;  // Not using yet...
	var $current_page;   // Not using yet...

	function cTitleList($type) {
		$this->type = $type;
		if($type == OFFER_LISTING)
			$this->type_code = OFFER_LISTING_CODE;
		else
			$this->type_code = WANT_LISTING_CODE;
	}	
									
	function MakeTitleArray($member_id="%") {
		global $cDB, $cErr;

		$query = $cDB->Query("SELECT DISTINCT title FROM ".DATABASE_LISTINGS." WHERE member_id LIKE ". $cDB->EscTxt($member_id) . " AND type=". $cDB->EscTxt($this->type_code) .";");

		$i=0;		
		while($row = mysql_fetch_array($query))
		{
			$titles[$i]= $cDB->UnEscTxt($row[0]);
			$i += 1;
		}
		
		if ($i == 0)
			$titles[0]= "";
		
		return $titles;
	}	

	function DisplayMemberListings($member) {
		global $cDB;

		$query = $cDB->Query("SELECT title FROM ".DATABASE_LISTINGS." WHERE member_id=". $cDB->EscTxt($member->member_id) ." AND type=". $cDB->EscTxt($this->type_code) ." ORDER BY title;");
		
		$output = "";
		$current_cat = "";
		while($row = mysql_fetch_array($query)) {
			$output .= "<A HREF=listing_edit.php?title=" . urlencode($cDB->UnEscTxt($row[0])) ."&member_id=".$member->member_id ."&type=". $this->type ."&mode=" . $_REQUEST["mode"] ."><FONT SIZE=2>". $cDB->UnEscTxt($row[0]) ."</FONT></A><BR>";
		}

		return $output;
	}

}

class cListingReport {
	var $member_list;
	var $offer_list;
	var $want_list;
	var $pdf;
	var $font;
	var $font_size;
	var $font_spacing;
	var $margin;
	var $column;
	
	function cListingReport () {
		$this->member_list = new cMemberGroup();
		$this->member_list->LoadMemberGroup();
		$this->offer_list = new cListingGroup(OFFER_LISTING);
		$this->offer_list->LoadListingGroup("%");
		$this->want_list = new cListingGroup(WANT_LISTING);
		$this->want_list->LoadListingGroup("%");
		$this->column = 1;	
		$this->margin = 15;
		$this->font = "Times";
		$this->font_size = 12;
		$this->font_spacing = 5;
		$this->pdf = &File_PDF::factory("P", "mm", "A4");
		$this->pdf->open();
		$this->pdf->addPage("P");
		$this->pdf->setFont($this->font,"",$this->font_size);
		$this->pdf->setMargins($this->margin, $this->margin, "105");
		$this->pdf->setAutoPageBreak(true,"2");
		$this->pdf->setXY($this->margin,$this->margin);
		$this->pdf->SetDisplayMode("real","single");
	}
	
	function DownloadReport () {	
    global $lng_listings, $lng_member_information, $lng_offered_listings, $lng_wanted_listings;
	
		$this->PrintFirstPage();
	
		$this->PrintSectionHeader($lng_member_information,FIRST);
		$this->PrintMembers();
	
		$this->PrintSectionHeader($lng_offered_listings);
		$this->PrintListings(OFFER_LISTING);
		
		$this->PrintSectionHeader($lng_wanted_listings);
		$this->PrintListings(WANT_LISTING);
		
		$this->pdf->Output($lng_listings.".pdf",true);
	}
	
	function PrintMembers() {	
		foreach($this->member_list->members as $member) {
			if ($member->account_type == "F")
				continue;	// Skip fund accounts
		
			$this->PrintLine("");
			$this->PrintTitle($member->PrimaryName());
			$this->PrintLine(" (". $member->member_id .")");
			if($member->person[0]->email)
				$this->PrintLine($member->person[0]->email);
			if($member->person[0]->phone1_number) {
				$this->PrintText($member->person[0]->DisplayPhone(1));
				if($member->person[0]->phone2_number)
					$this->PrintText(", ". $member->person[0]->DisplayPhone(2));
				$this->PrintLine("");				
			}
		}
	}
	
	function PrintListings($type) {
		$curr_category = "";
		if($type == OFFER_LISTING)
			$listings =& $this->offer_list->listing;
		else 
			$listings =& $this->want_list->listing;
			
		foreach ($listings as $listing) {
			if($listing->status != ACTIVE)
				continue;
				
			if($listing->category->id != $curr_category) {
				$this->PrintCategoryHeader($listing->category->description);
				$curr_category = $listing->category->id;
			}
			
			$this->PrintTitle($listing->title);
			$this->PrintDescription($listing->description);
			$this->PrintMember($listing->member->PrimaryName());
			$this->PrintLine("");													
		}	
	}
	
	function PrintSectionHeader($header, $first_page=false) {
		if(!$first_page)
			$this->NewPage();
		else
			$header = "\n". $header;
			
		$this->pdf->setFont($this->font,"B", $this->font_size + 6);
		$this->pdf->Write($this->font_spacing + 2, $header . "\n");
		$this->pdf->setFont($this->font,"", $this->font_size);	
	}
	
	function PrintCategoryHeader($category) {
		$this->DoPageBreaks();
		$this->pdf->setFont($this->font,"B", $this->font_size + 2);
		$this->pdf->Write($this->font_spacing + 1, "\n" . $category . "\n");
		$this->pdf->setFont($this->font,"", $this->font_size);
		$this->PrintLine("");				
	} 
	
	function PrintFirstPage() {
		$this->pdf->setFont($this->font,"BI",26);
		$this->pdf->Write(8,SITE_LONG_TITLE ." - ");
		$this->pdf->Write(8,$lng_members_directory."\n");
		$this->pdf->setFont($this->font,"",$this->font_size);
	}
	
	function PrintTitle($title) {
		$this->pdf->setFont($this->font,"BI",$this->font_size);
		$this->pdf->Write($this->font_spacing, $title);
		$this->pdf->setFont($this->font,"",$this->font_size);
	}
	
	function PrintDescription($desc) {
		if ($desc) {
			$this->pdf->setFont($this->font,"BI",$this->font_size);
			$this->pdf->Write($this->font_spacing, ": ");
			$this->pdf->setFont($this->font,"",$this->font_size);
			
			if(strlen($desc) < 40) {
				$this->pdf->Write($this->font_spacing,$desc);
			} else {
				// Need to print long descriptions word-by-word so my 
				// simple column pagebreak system will work.  
				// TODO: Should extend File_PDF class instead...			
				$words = split(" ",$desc);
				foreach($words as $word) {
					$this->DoPageBreaks();
					$this->pdf->Write($this->font_spacing,$word . " ");
				}
			}
		} 
	}
	
	function PrintMember($name) {
		$this->PrintLine(" (". $name .")");
	}
	
	function PrintText ($text) {
		$this->pdf->Write($this->font_spacing, $text);
	}
	
	function PrintLine($line) {
		$this->DoPageBreaks();
		$this->pdf->Write($this->font_spacing, $line . "\n");
	}

	function DoPageBreaks() {
		if($this->pdf->getY() >= 270) {
			if($this->column == 2) {
				$this->NewPage();
			} else { // New Column
				$this->NewColumn();
			} 
		}	
	}
	
	function NewPage() {
		$this->pdf->addPage("P");
		$this->pdf->setXY($this->margin,$this->margin);
		$this->pdf->setMargins($this->margin,$this->margin,"105");
		$this->pdf->setFont($this->font,"", $this->font_size);
		$this->column = 1;	
	}
	
	function NewColumn() {
		$this->pdf->setMargins("115",$this->margin,$this->margin);
		$this->pdf->setXY("115",$this->margin);
		$this->column = 2;	
	}
}

function ShowRate($rate) { 		
	$output = "";
	if (SHOW_RATE_ON_LISTINGS==true && $rate) {
    $output .= "<img src=\"images/tag.png\" width=\"16\" height=\"16\" align=\"center\"/> ";
		$output .= $rate;

    // To avoid doubled mentioning of the units description: check if '<units>' is already mentioned in the price (rate);
    // if NOT, add the units description.
    $pos = strpos( strtolower($rate), strtolower(UNITS));
    if ($pos === false) {
			$output .= " ".UNITS;
    }   					
	} 
	return $output;
}



?>
