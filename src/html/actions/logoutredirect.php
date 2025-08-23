<?php
    
	if (file_exists('../shared_functions.php')) {
	include_once('../shared_functions.php');
	} elseif (file_exists('../Dashboardify/shared_functions.php')) {
		include_once('../Dashboardify/shared_functions.php');
	}
	//If not logged in, redirect to login page
	//Need to check if current session ID is VALID! 
	
	// I want to wrap everything in a doesdbexist() function, but i think this gets called from setup page, so not sure if it would cause an issue with the setup page redirecting to itself every 5 milliseconds if db doesn't exist yet.
	// Maybe things would be simpler if it just started with a demo DB. 
	

	if (!isset($_COOKIE["SessionID"])) {
		if (doesDatabaseExist()) {
			redirect("../Dashboardify/actions/start-login.php?msg=CookieNotSet");

		}
	}
	


	if (isset($_COOKIE["SessionID"]) && doesDatabaseExist()) {
		$sessionid = $_COOKIE["SessionID"]; 
		$matchingsessioncount = selectquery("Select Count(Email) As Matches From Sessions S
		Left Join Users U on s.UserID = U.RecID
		Where S.SessionID = '" . $sessionid . "'")[0]["Matches"];
		
		debuglog($matchingsessioncount, "User ID found from DB - executing from logoutredirect.php");
	

		if ($matchingsessioncount == 0) {
			// Running into issue when in situation where db doesn't exist or is empty.
			//Check if db exists properly before doing this redirect.

			if (doesDatabaseExist()) {
				setcookie("SessionID", "/", time() - 3600, "/Dashboardify");
				unset($_COOKIE['SessionID']);
				redirect("../Dashboardify/actions/start-login.php?msg=UserNotFound");
			}
		}
	}
?>