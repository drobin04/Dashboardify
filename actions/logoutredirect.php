<?php
    
	if (file_exists('../shared_functions.php')) {
	include_once('../shared_functions.php');
	} elseif (file_exists('../Dashboardify/shared_functions.php')) {
		include_once('../Dashboardify/shared_functions.php');
	}
	//If not logged in, redirect to login page
	//Need to check if current session ID is VALID! 
	if (!isset($_COOKIE["SessionID"])) {
		header("Location: ../Dashboardify/actions/start-login.php?msg=CookieNotSet");
	}
	


	if (isset($_COOKIE["SessionID"])) {
		$sessionid = $_COOKIE["SessionID"]; 
		$matchingsessioncount = selectquery("Select Count(Email) As Matches From Sessions S
		Left Join Users U on s.UserID = U.RecID
		Where S.SessionID = '" . $sessionid . "'")[0]["Matches"];
		
		debuglog($matchingsessioncount, "User ID found from DB - executing from logoutredirect.php");
	}

	if ($matchingsessioncount == 0) {
		setcookie("SessionID", "/", time() - 3600, "/Dashboardify");
		unset($_COOKIE['SessionID']);
		header("Location: ../Dashboardify/actions/start-login.php?msg=UserNotFound");
	}
?>