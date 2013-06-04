<?php
include_once("includes/inc.global.php");

$cUser->MustBeLoggedOn();
$p->site_section = SECTION_DIRECTORY;

require_once ("classes/class.listing.php");
include("includes/inc.forms.php");

$p->page_title = $lng_download_listing_report;

$form->addElement("hidden","class",$report_class);
$form->addElement("header", null, $lng_click_to_open_pdf, null);
$form->addElement("static", null, null, null);
$form->addElement("submit", "btnSubmit", $lng_download);

if ($form->validate()) { // Form is validated so processes the data
  $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form
	$p->DisplayPage($form->toHtml());
}

function process_data ($values) {
	global $p, $lng_download_complete;

  $report = new cListingReport();
  $report->DownloadReport();

  // Apparently statements below are not executed  
	$list = $lng_download_complete;
	$p->DisplayPage($list);
}
?>
