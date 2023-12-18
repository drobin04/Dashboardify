<?php 
function doesDatabaseExist() {
	$rootPath = $_SERVER['DOCUMENT_ROOT'];
	$dbpath = $rootPath . '/Dashboardify/Dashboardify.s3db';
	if (file_exists($dbpath) && filesize($dbpath) > 0) {
		return True;

	} else {
		return False;
	}
	
}
function selectquery($sql) {
	debuglog($sql,"about to execute query");
	$rootPath = $_SERVER['DOCUMENT_ROOT'];
	$dbpath = 'sqlite:' . $rootPath . '/Dashboardify/Dashboardify.s3db';
	$db_file = new PDO($dbpath);
	$dbpath = $rootPath . '/Dashboardify/Dashboardify.s3db';
	$dsn = 'sqlite:' . $dbpath;
	$db_file = new PDO($dsn);
	$stmt1 = $db_file->prepare($sql);
	$stmt1->execute();
	$results = $stmt1->fetchAll(PDO::FETCH_ASSOC);
	//debuglog($results,"Query results");
	return $results;
}
function scalarquery($sql, $columnname) {
	debuglog($sql,"about to execute scalar query");
	$rootPath = $_SERVER['DOCUMENT_ROOT'];
	$dbpath = 'sqlite:' . $rootPath . '/Dashboardify/Dashboardify.s3db';
	$db_file = new PDO($dbpath);
	$stmt1 = $db_file->prepare($sql);
	$stmt1->execute();
	$results = $stmt1->fetchAll(PDO::FETCH_ASSOC);
	return $results[0][$columnname];
}

function getPDO_DBFile() {
	$rootPath = $_SERVER['DOCUMENT_ROOT'];
	$localdb = new PDO('sqlite:' . $rootPath . '/Dashboardify/Dashboardify.s3db');
	return $localdb;
}

function getCurrentUserID() {
	if (isset($_COOKIE["SessionID"])) {
		$sessionid = $_COOKIE["SessionID"]; 
		debuglog($sessionid, "SessionID"); //Get User for Session ID
		$userid = selectquery("Select UserID From Sessions Where SessionID = '" . $sessionid . "'")[0]["UserID"]; 
		debuglog($userid, "User ID found for user");
		return $userid;
	} else {
		$userid = "0";
		return $userid;
	}
	
}
function execquery($sql) {
	//$localdb = new PDO('sqlite:Dashboardify.s3db');
	$rootPath = $_SERVER['DOCUMENT_ROOT'];
	$localdb = new PDO('sqlite:' . $rootPath . '/Dashboardify/Dashboardify.s3db');
	$stmt1 = $localdb->prepare($sql);
	$stmt1->execute();
	
}

/**
 * Function to generate HTML table from array of objects
 *
 * @param array $data
 * @return string
 */
function generateTableFromObjects(array $data): string {
    if (empty($data)) {
        return '<p>No data available</p>';
    }

    $html = '<table class="TableResults">';
    $html .= '<tr>';
    foreach ($data[0] as $key => $value) {
        $html .= '<th>' . htmlspecialchars($key) . '</th>';
    }
    $html .= '</tr>';

    foreach ($data as $row) {
        $html .= '<tr>';
        foreach ($row as $value) {
            $html .= '<td>' . htmlspecialchars($value) . '</td>';
        }
        $html .= '</tr>';
    }

    $html .= '</table>';

    return $html;
}

function GUID()
{
	if (function_exists('com_create_guid') === true)
	{
		return trim(com_create_guid(), '{}');
	}

	return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}
function debuglog( $object=null, $label=null ){
	// DEBUG LOGGING
	// SET THE BELOW TO TRUE IF YOU WANT DEBUGGING INFO TO APPEAR IN CONSOLE OF WEBPAGES
	// HOWEVER THIS BLOATS THE TRANSFERRED DATA ON PAGES AND IS PREFERRABLY LEFT OFF FOR BETTER PERFORMANCE WHILE LIVE. 
	$debug_logging_enabled = true;
	
	$message = json_encode($object, JSON_PRETTY_PRINT);
	$label = "Debug" . ($label ? " ($label): " : ': ');
	if ($debug_logging_enabled) {
		echo "<script>console.log(\"$label\", $message);</script>";
	}
}

function getAuthMode() {
	$authmode = scalarquery("Select Value From Settings Where Name = 'AuthMode'", "Value");
	return $authmode;
}

function DoIOwnThisWidget($widgetrecid) {
	$userid = getCurrentUserID();
	$q = "select count(w.recid) as matches from widgets w
	left join Dashboards D on w.DashboardRecID = D.DashboardID
	left join Users U on U.RecID = D.UserID
	where userid = '" . $userid . "'
	and w.RecID = '" . $widgetrecid . "'";
	if (scalarquery($q, "matches") != 0) {
		return true;
	} else {
		return false;
	}
}
?>