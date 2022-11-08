<html>
<head>    <?php include(dirname(__FILE__) . "/modules/logoutredirect.php");?> </head>
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




    //Insert new Widget
	$dashboardid = GetUsersDashboard(); //Identify which dashboard the widget needs to be added to.

    //Prepare Variables
    $sep = "','"; // Seperator
    $sqlserveraddress = $_POST["SQLServerAddressName"];
    $sqldbname = $_POST["SQLDBName"];
    $sqlusername = $_POST["sqluser"];
    $sqlpass = $_POST["sqlpass"];
    $sqlquery = str_replace("'","''",$_POST["sqlquery"]);
    
    //$_POST[""]
	
    // Prepare INSERT statement.
	$select = "INSERT INTO Widgets (WidgetType,BookmarkDisplayText,PositionX,PositionY,SizeX,SizeY,WidgetURL,WidgetCSSClass,Notes,DashboardRecID
    ,sqlserveraddress,sqldbname,sqluser,sqlpass,sqlquery) 
    VALUES ('" . $_POST["WidgetType"] . $sep . $_POST["DisplayText"] . $sep . $_POST["PositionX"] . $sep . $_POST["PositionY"] . $sep . 
    $_POST["SizeX"] . $sep . $_POST["SizeY"] . $sep . $_POST["URL"] . $sep . $_POST["CSSClass"] . $sep . $_POST["Notes"] . $sep . $dashboardid . $sep .
    $sqlserveraddress . $sep . $sqldbname . $sep . $sqlusername . $sep . $sqlpass . $sep . $sqlquery .
    "')";
	$stmt = $db_file->prepare($select);
	
	// Execute statement.
	$stmt->execute();
	header("Location: index.php");
?>

</body>

</html>