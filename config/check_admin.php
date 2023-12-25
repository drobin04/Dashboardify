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

function AmIAdmin() {
    $recid = getCurrentUserID();
    if ($recid == "0") {
        if (doesDatabaseExist()) {
            // You have no session cookie, but the DB does exist - You MAY have no right to be here.
            // Will progress to code below, where it checks if there are ANY administrators.
            // If there are NO admin users defined, you are allowed to be here to configure the page.
            // If there is at least one other admin already defined, and you're not auth'ed with a session cookie, you are not allowed to be here. without logging in and having your account marked
            //      as an admin 
        } else {
            return True;
        }
        // OK, so, getCurrentUserID will return '0' if no session cookie can be found.
        // If DB file has been deleted / doesn't exist yet, this will return 0, and then we will know to check if the db exists or not before running this admin check (which depends on DB.)
        // If DB doesn't exist yet, then we have a right to be here, because app is being configured, so we can just return true. 
    } else {
        $query = "Select Admin From Users Where RecID = '" . getCurrentUserID() . "'"; // Initial check, does THIS user have admin rights :) 
        $results = queryDB($query);
        debuglog_admin($results, "Admin_check_Results");
        $countofadminusersSQL = "Select Count(*) As Count From Users Where Admin = 1"; // This is to check if there are ANY admins in the system at all, in case the first check returns false, to see if user
                                                                                       //   should get access anyway
        $adminusercountresults = querydb($countofadminusersSQL)[0]["Count"];

        If (($results[0]["Admin"] == "1") or $adminusercountresults == 0) { // If you are confirmed as an admin, OR if there are no admins yet, we proceed.
            return true;
        } else { // Otherwise, you shouldn't be here :) 
            return false;
        }
    }

    
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
    $debug_needed = false;
    if ($debug_needed) {
        $message = json_encode($object, JSON_PRETTY_PRINT);
        $label = "Debug" . ($label ? " ($label): " : ': ');
        echo "<script>console.log(\"$label\", $message);</script>";
    }
}



?>