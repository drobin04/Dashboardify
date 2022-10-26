<html>
<head></head>
<body>

<?php
	
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

	// Prepare INSERT statement.
	$select = "INSERT INTO Widgets (WidgetType,BookmarkDisplayText,PositionX,PositionY,SizeX,SizeY,WidgetURL,WidgetCSSClass,Notes) VALUES ('" . $_POST["WidgetType"] . "','" . $_POST["DisplayText"] . "','" . $_POST["PositionX"] . "','" . $_POST["PositionY"] . "','" . $_POST["SizeX"] . "','" . $_POST["SizeY"] . "','" . $_POST["URL"] . "','" . $_POST["CSSClass"] . "', '" . $_POST["Notes"] . "')";
	$stmt = $db_file->prepare($select);
	
	// Execute statement.
	$stmt->execute();
	header("Location: index.php");
?>

</body>

</html>