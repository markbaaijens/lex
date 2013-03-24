<?php
include_once("includes/inc.global.php");

$cUser->MustBeLevel(2);
$p->site_section = ADMINISTRATION;
$p->page_title = $lng_reports;

include("classes/class.backup.php");
include("includes/inc.forms.php");
require_once ('Spreadsheet/Excel/Writer.php');

$form->addElement("header", null, $lng_report_spreadheet, null);
$form->addElement("static", null, null, null);

// Read all .sql-files in ./reports
if ($handle = opendir('./reports')) {
  while (false !== ($filename = readdir($handle))) {
    if ($filename == "." ) {
      continue;
    }  
    if ( $filename == "..") {    
      continue;
    }
    if ( ! strpos($filename, ".sql") ) {    
      continue;
    }
    
    // Remove .sql extention for better readability
    $reports[] = str_replace('.sql', '', $filename);
  }
  closedir($handle);

  // Sort the array: function readdir returns the filenames in the order in which they are 
  // stored by the filesystem. naturally we want to have this alphabetically...
  sort($reports);  
}

$form->addElement("select", "report", $lng_select_report, $reports);

$form->addElement("submit", "btnSubmit", $lng_download);

if ($form->validate()) { // Form is validated so processes the data
  $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form
	$p->DisplayPage($form->toHtml());
}

function process_data ($values) {
	global $p, $cDB, $reports;
	
	$file_name = $reports[$values["report"]];
	
	// Create spreadsheet object
	$workbook = new Spreadsheet_Excel_Writer();	
	$workbook->send($file_name . '.xls');		
	$worksheet =& $workbook->addWorksheet("Data");	
	
	// Set query-text
  $query_text = file_get_contents("./reports/" . $file_name . ".sql");	
	
//	$query_text = "SELECT * FROM person;";

  // Print column names
	$query = $cDB->Query($query_text);
  $i = 0;
  while ($i <= mysql_num_fields($query)) {
		$worksheet->write(0, $i - 1, $meta->name);			  
    $meta = mysql_fetch_field($query, $i);
		$field_names[$i] = $meta->name;
    $i++;
  }	

  // Print data
	$row_num = 1;
	while($row = mysql_fetch_array($query)) {
		$col_num = 0;
		foreach ($field_names as $field) {
			$worksheet->write($row_num, $col_num, $row[$field]);
			$col_num += 1;
		}
		$row_num += 1;
	}

	// Let's send the file
	$workbook->close();		

	$list = $lng_export_complete;
	$p->DisplayPage($list);
}
?>
