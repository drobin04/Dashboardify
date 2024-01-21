<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

include_once('../shared_functions.php');

//get recid
$recid = $_GET['RecID'];
//echo "RecID: " . $recid;
// Get current user's ID 
$userrecid = getCurrentUserID();
//echo "<br />User ID: " . $userrecid;
// Search DB for widget where recid and user id match, ensuring that we get the widget only if it's valid for this user. 

$notes = scalarquery_bind1("
	Select Notes from v_Widgets
	Where UserRecID = '" . $userrecid . "' and RecID = ?
	",$recid, "Notes");

echo $notes;
?>