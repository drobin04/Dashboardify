<?php 

function serializeWidgetsForDashboard($dashboardid) {
	// Assume this runs for the current user, should never need to run this for anyone else other than current....
	
	//Get list of widgets for this dashboard
	// Filter by current user!
	$sql = "Select BookmarkDisplayText, WidgetURL, WidgetCSSClass, Notes, PositionX, PositionY,SizeX,SizeY,RecID, DashboardRecID, WidgetType,Notes From v_Widgets Where DashboardRecID = ? and UserRecID = ?";
	$currentuserid = getCurrentUserID();
	$data = selectquery_bind2($sql,$dashboardid,$currentuserid); 
	// Serialize $data variable into JSON format
	$jsonData = json_encode($data);
	return $jsonData;
	
}

// Notes - 

// In the widget container, once it's just widgets being generated via PHP (the 'forms' / dialogs are static content, right?), we can have that be content that's loaded via a javascript api request, with most of the rest being HTML data.
// We can then swap over to largely serving the page via HTML, with ***VERY LITTLE*** traffic and compute being done by PHP.
// Need JS on client side that can receive, for example, an array (JSON or javascript array), and then iterate upon that to build the widgets onto the screen.
// There will be SOME widget types with special data (i.e sql usernames / passwords) where not all data can be passed; and for those we will have them rendered on the server. Might want to wrap in JSON and have a field that just reads that it'll be direct HTML output or something that just gets printed at that point, dunno.
// Store a HASH value for the container of widgets! 
// Hash value gets copied into separate item in localstorage on client; and stored on server against the Dashboard recID.
// Ideally, some way to store a list of these hashes in localstorage or browser sql, one per dashboard used (could even just append dashboard ID as part of the key).
// When loading the dashboard, maybe check if the hash has changed from the server; using a small javascript ping. If so, re-load the data from the server, dispose of what's in the widget container, and redraw the page.




// For transitioning to a javascript client side model;
// Have a function that serializes out a list of all widget data as output for an api request; Just the actual widget contents though (minus maybe any sensitive creds?)
// Then, hash the value of the output, and store that hash with the dashboard record
// On the client side, store this hash as well
// On page load, render from cache. Check if there's an updated hash on the server. If so, download from the server and redraw locally
// This way, the page can be built out locally without depending on a php generation every time we build the page. 
// I think this is going to be important for scaling up, while minimizing server compute necessary for functionality. 
// Ideally we minimize the amount of traffic that has to pass in either direction, while also minimizing the amount of php compute that has to happen; so at the base, they are just pinging an html page for the initial serve, and then retrieving data from PHP based API's afterwards

function rootdir() { // Added for testing evaluation of __DIR__ when referenced from another file
	// Seems to work properly and always returns this file's root location and not the referencing file's location
	
return __DIR__;	
}

function getCurrentUserFolder() {
	$myuserid = getCurrentUserID();
	$dir = rootdir() . "/user/" . $myuserid . "/";
	// Check if folder exists. If not, create it. 
	if (!file_exists($dir)) {
		mkdir($dir, 0777, true);
		//echo "Folder created successfully!";
	} else {
		//echo "Folder already exists!";
	}
	
	return $dir;
}

function breakifnotadmin() {
if (!AmIAdmin()) {
	echo "you are not permitted to perform this operation.";
	exit();	
}
}
function doesDatabaseExist() {
	$rootPath = $_SERVER['DOCUMENT_ROOT'];
	$dbpath = $rootPath . '/Dashboardify/Dashboardify.s3db';
	if (file_exists($dbpath) && filesize($dbpath) > 0) {
		return True;

	} else {
		return False;
	}
	
}

function doIOwnThisWidget_archive_Delete_This($widgetID) {
	// get current user RecID
	$userid = getCurrentUserID();
	
	$count = scalarquery("Select count(*) as Count From Widgets W 
		Left Join Dashboards D On W.DashboardRecID = D.DashboardID 
		Left Join Users U On U.RecID = D.UserID
		Where W.RecID = '" . $widgetID . "' 
		and U.RecID = '" . $userid . "'");
		
		if ($count != "0") {
		//match found	
			return true;
		} else {
			return false;
		}
	
}

function getUserIDFromEmail($email) {
	return scalarquery("Select RecID From Users Where Email = '" . $email . "'", "RecID");
		
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
function selectquery_bind1($sql,$param1) {
	$dbpath = 'sqlite:' . rootdir() . '/Dashboardify.s3db';
	$db_file = new PDO($dbpath);
	$stmt = $db_file->prepare($sql);
	$stmt->bindParam(1,$param1,PDO::PARAM_STR);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $results;
}
function selectquery_bind2($sql,$param1, $param2) {
	$dbpath = 'sqlite:' . rootdir() . '/Dashboardify.s3db';
	$db_file = new PDO($dbpath);
	$stmt = $db_file->prepare($sql);
	$stmt->bindParam(1,$param1,PDO::PARAM_STR);
	$stmt->bindParam(2,$param2,PDO::PARAM_STR);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
function scalarquery_bind1($sql, $boundParam, $columnname) {
	debuglog($sql,"about to execute scalar query");
	$rootPath = $_SERVER['DOCUMENT_ROOT'];
	$dbpath = 'sqlite:' . $rootPath . '/Dashboardify/Dashboardify.s3db';
	$db_file = new PDO($dbpath);
	$stmt1 = $db_file->prepare($sql);
	$stmt1->bindParam(1,$boundParam,PDO::PARAM_STR);
	$stmt1->execute();
	$results = $stmt1->fetchAll(PDO::FETCH_ASSOC);
	return $results[0][$columnname];
}


function DoesUserExist($email) {
	$rootPath = $_SERVER['DOCUMENT_ROOT'];
	$dbpath = 'sqlite:' . $rootPath . '/Dashboardify/Dashboardify.s3db';
	$db_file = new PDO($dbpath);
	$sql = "Select Count(*) As Count From Users Where Email = ?";
	$stmt1 = $db_file->prepare($sql);
	$stmt1->bindParam(1, $email, PDO::PARAM_STR);
	$stmt1->execute();
	$results = $stmt1->fetchAll(PDO::FETCH_ASSOC)[0]["Count"];
	//return $results[0][$columnname];
	If ($results >= 1) {
		return true;
	} else {
		return false;
	}

}

function getPDO_DBFile() {
	$rootPath = $_SERVER['DOCUMENT_ROOT'];
	$localdb = new PDO('sqlite:' . $rootPath . '/Dashboardify/Dashboardify.s3db');
	return $localdb;
}

function getCurrentUserID() {
	if (isset($_COOKIE["SessionID"])) {
		$sessionid = $_COOKIE["SessionID"]; 
		$userid = selectquery_bind1("Select UserID From Sessions Where SessionID = ?", $sessionid)[0]["UserID"]; 
		return $userid;
	} else {
		$userid = "0";
		return $userid;
	}
	
}

function getCurrentUserEmail() { // GET EMAIL ADDRESS / USERNAME FOR USER
	if (isset($_COOKIE["SessionID"])) {
		$sessionid = $_COOKIE["SessionID"]; 
		debuglog($sessionid, "SessionID"); //Get User for Session ID
		$userid = selectquery("Select Email From Sessions S Left Join Users U on U.RecID = S.UserID Where S.SessionID = '" . $sessionid . "'")[0]["Email"]; 
		debuglog($userid, "Email found for user");
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
function execquery_bind1($sql,$param1) {
	//$localdb = new PDO('sqlite:Dashboardify.s3db');
	$rootPath = $_SERVER['DOCUMENT_ROOT'];
	$localdb = new PDO('sqlite:' . $rootPath . '/Dashboardify/Dashboardify.s3db');
	$stmt1 = $localdb->prepare($sql);
	$stmt1->bindParam(1, $param1, PDO::PARAM_STR);
	$stmt1->execute();
	
}
function execquery_bind2($sql,$param1, $param2) {
	//$localdb = new PDO('sqlite:Dashboardify.s3db');
	$rootPath = $_SERVER['DOCUMENT_ROOT'];
	$localdb = new PDO('sqlite:' . $rootPath . '/Dashboardify/Dashboardify.s3db');
	$stmt = $localdb->prepare($sql);
	$stmt->bindParam(1, $param1, PDO::PARAM_STR);
	$stmt->bindParam(2, $param2, PDO::PARAM_STR);
	$stmt->execute();
	
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
            $html .= '<td>' . (is_null($value) ? '' : htmlspecialchars($value)) . '</td>';
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
	$debug_logging_enabled = false;
	
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

function redirect($url) {
    $currentUrl = $_SERVER['REQUEST_URI'];
    $baseUrl = rtrim(dirname($currentUrl), '/');
    $absoluteUrl = $baseUrl . '/' . ltrim($url, '/');
	//echo '<script>alert("redirecting!");</script>';
    echo "<script>location.href = '". $absoluteUrl ."';</script>";
}

?>
