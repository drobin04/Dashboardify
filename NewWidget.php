<html>
<head></head>
<body>

<?php
	
        $post_data = file_get_contents('php://input');
        //print_r($_POST);       
    	// Create and connect to SQLite database file.
	$db_file = new PDO('sqlite:Dashboardify.s3db');
	// Prepare INSERT statement.
	$select = "INSERT INTO Widgets (WidgetType,BookmarkDisplayText,PositionX,PositionY,SizeX,SizeY,WidgetURL,WidgetCSSClass,Notes) VALUES ('" . $_POST["WidgetType"] . "','" . $_POST["DisplayText"] . "','" . $_POST["PositionX"] . "','" . $_POST["PositionY"] . "','" . $_POST["SizeX"] . "','" . $_POST["SizeY"] . "','" . $_POST["URL"] . "','" . $_POST["CSSClass"] . "', '" . $_POST["Notes"] . "')";
	$stmt = $db_file->prepare($select);
	
	// Execute statement.
	$stmt->execute();
?>

</body>

</html>