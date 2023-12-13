<?php //Grab custom CSS from Database - This part is important for background colors for example
	
	// Create and connect to SQLite database file.
	$db_file = new PDO('sqlite:Dashboardify.s3db');
	// Prepare SELECT statement.

	// Get UserID
	//$sessionid = $_COOKIE["SessionID"]; debuglog($sessionid, "SessionID"); //Get User for Session ID
	//$userid = selectquery("Select UserID From Sessions Where SessionID = '" . $sessionid . "'")[0]["UserID"]; debuglog($userid, "User ID found for user");
	// Commented out this code i was testing out introducing
	// Unsure of state of this.
	// Going to flesh out CSS on dashboards first. 
	

	$select = "SELECT * FROM UserCSS"; // "" Where UserID = " . $userid;
	$stmt = $db_file->prepare($select);
	
	// Execute statement.
	$stmt->execute();
	$usercss = "";
	// Get the results.
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	foreach($results as $row) {
		echo $row["CSS"];
		$usercss = $usercss . $row["CSS"];
	}
?>