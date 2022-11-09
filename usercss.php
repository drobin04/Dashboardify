<?php //Grab custom CSS from Database - This part is important for background colors for example
	
	// Create and connect to SQLite database file.
	$db_file = new PDO('sqlite:Dashboardify.s3db');
	// Prepare SELECT statement.
	$select = "SELECT * FROM UserCSS";
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