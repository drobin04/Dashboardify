<html>
<head></head>
<body>

<?php
	
	function debuglog( $object=null, $label=null ){
        $message = json_encode($object, JSON_PRETTY_PRINT);
        $label = "Debug" . ($label ? " ($label): " : ': ');
        echo "<script>console.log(\"$label\", $message);</script>";
    }
    function GetUsersDashboard() {
        $localdb = $db_file = new PDO('sqlite:Dashboardify.s3db');
        $select = "SELECT S.UserID, D.RecID As DashboardRecID FROM Sessions S Left Join Dashboards D On D.UserID = S.UserID Where SessionID = '" . $_COOKIE["SessionID"] . "'";
        debuglog($select,"SQL Query Searching For User Dashboard By SessionID");
        $stmt = $localdb->prepare($select);
        // Execute statement.
        $stmt->execute();
        // Get the results.
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        debuglog($results,"Results from SQL Query search");
        $dash = $results[0]["DashboardRecID"];
        debuglog($user,"User info selected");
        return $dash;
        //return "hello";
    }

    $post_data = file_get_contents('php://input');
    //print_r($_POST);       
    // Create and connect to SQLite database file.
	$db_file = new PDO('sqlite:Dashboardify.s3db');

	//Delete Existing Widget ID if Edit ID passed: 
	If ($_POST["ID"] <> "") {
		$sql = "Delete From Widgets Where RecID = '" . $_POST["ID"] . "'";
		$executor = $db_file->prepare($sql);
		$executor->execute();
		echo "DELETED WIDGET WITH ID '" . $_POST["ID"] . "', Inserting new copy now...";
		header("Location: index.php");
	}
	$dashboardid = GetUsersDashboard();

	// Prepare INSERT statement.
	$select = "INSERT INTO Widgets (WidgetType,BookmarkDisplayText,PositionX,PositionY,SizeX,SizeY,WidgetURL,WidgetCSSClass,Notes,DashboardRecID) VALUES ('" . $_POST["WidgetType"] . "','" . $_POST["DisplayText"] . "','" . $_POST["PositionX"] . "','" . $_POST["PositionY"] . "','" . $_POST["SizeX"] . "','" . $_POST["SizeY"] . "','" . $_POST["URL"] . "','" . $_POST["CSSClass"] . "', '" . $_POST["Notes"] . "', '" . $dashboardid . "')";
	$stmt = $db_file->prepare($select);
	
	// Execute statement.
	$stmt->execute();
	header("Location: index.php");
?>

</body>

</html>