<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

include_once('../shared_functions.php');

//Get recid from URL
$recid = $_GET['RecID'];

//Get notes data from post

$notes = $_POST['Notes'];

//echo $recid . "<br />" . $notes;

If (DoIOwnThisWidget($recid)) {

	// Massage notes field to handle single quotes that need to be 
	// escaped
	$notes_msged = str_replace("'","''",$notes);
	
	execquery_bind2("Update Widgets Set Notes = ? Where RecID = ?", $notes_msged, $recid);
	
	redirect('../index.php');
}

?>