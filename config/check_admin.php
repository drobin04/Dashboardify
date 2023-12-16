<?php

if (file_exists('../actions/logoutredirect.php')) {
	include_once('../actions/logoutredirect.php');
	} elseif (file_exists('actions/shared_functions.php')) {
		include_once('actions/shared_functions.php');
	}

function IsAdmin($userID) {
    
    $query = "Select Admin From Users Where RecID = '" . $userID . "'";
    $results = queryDB($query);
    debuglog_admin($results, "Admin_check_Results");
    $countofadminusersSQL = "Select Count(*) As Count From Users Where Admin = 1";
    $adminusercountresults = querydb($countofadminusersSQL)[0]["Count"];

    If (($results[0]["Admin"] == "1") or $adminusercountresults == 0) {
        return true;
    } else {
        return false;
    }
    //return $results;
}

function queryDB($sql) {
    //$db_file = new PDO('sqlite:../Dashboardify/Dashboardify.s3db');
    $rootPath = $_SERVER['DOCUMENT_ROOT'];
    $dbpath = 'sqlite:' . $rootPath . '/Dashboardify/Dashboardify.s3db';
    $db_file = new PDO($dbpath);
    $stmt1 = $db_file->prepare($sql);
    $stmt1->execute();
    return $stmt1->fetchAll(PDO::FETCH_ASSOC);
}

function debuglog_admin( $object=null, $label=null ){
    $message = json_encode($object, JSON_PRETTY_PRINT);
    $label = "Debug" . ($label ? " ($label): " : ': ');
    echo "<script>console.log(\"$label\", $message);</script>";
}



?>