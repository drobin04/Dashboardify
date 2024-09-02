<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

	

include_once("../shared_functions.php");


// Get querystring for RecID

// Check that it belongs to this user / filter for this user!
If (Isset($_GET["EditRecID"])) {
	$recid = $_GET["EditRecID"];
	
	//Setup query
	$sql = "Select BookmarkDisplayText, WidgetURL, WidgetCSSClass, Notes, PositionX, PositionY,SizeX,SizeY,RecID, DashboardRecID, WidgetType,Notes,sqlquery,sqldbname,sqlpass, sqlserveraddress From v_Widgets Where RecID = ? and UserRecID = ?";
	$currentuserid = getCurrentUserID();
	$data = selectquery_bind2($sql,$recid,$currentuserid); 
	// Serialize $data variable into JSON format
	$jsonData = json_encode($data);
	echo $jsonData;
	
	
}

?>