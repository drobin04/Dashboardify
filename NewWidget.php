<html>
<head>    <?php include_once("logoutredirect.php");?> </head>
<body>

<?php
    include_once('shared_functions.php');
	$sessionID = $_COOKIE["SessionID"];

    function GetUsersDashboard() {
        $select = "SELECT S.UserID, D.RecID As DashboardRecID FROM Sessions S Left Join Dashboards D On D.UserID = S.UserID Where SessionID = '" . $_COOKIE["SessionID"] . "'";
        debuglog($select,"SQL Query Searching For User Dashboard By SessionID");
        // Get the results.
        $results = selectquery($select);
        debuglog($results,"Results from SQL Query search");
        $dash = $results[0]["DashboardRecID"];
        debuglog($user,"User info selected");
        return $dash;
    }

    $post_data = file_get_contents('php://input');

	//Delete Existing Widget ID if Edit ID passed: 
	If ($_POST["ID"] <> "") {
		$sql = "Delete From Widgets Where RecID = '" . $_POST["ID"] . "'";
		execquery($sql);
		echo "DELETED WIDGET WITH ID '" . $_POST["ID"] . "', Inserting new copy now...";
		header("Location: index.php");
	}

    //Insert new Widget
	$dashboardid = $_POST["dashboardID"]; //Identify which dashboard the widget needs to be added to.

    //Prepare Variables
    $sep = "','"; // Seperator
    $sqlserveraddress = $_POST["SQLServerAddressName"];
    $sqldbname = $_POST["SQLDBName"];
    $sqlusername = $_POST["sqluser"];
    $sqlpass = $_POST["sqlpass"];
    $sqlquery = str_replace("'","''",$_POST["sqlquery"]);
    
    $globaldefault = "";

    if (isset($_POST["GlobalDefault"])) {
        $globaldefault = $_POST["GlobalDefault"];

    } else {
        $globaldefault = "0";
    }

    //$_POST[""]
	
    // Prepare INSERT statement.
	$select = "INSERT INTO Widgets (WidgetType,BookmarkDisplayText,PositionX,PositionY,SizeX,SizeY,WidgetURL,WidgetCSSClass,Notes,DashboardRecID
    ,sqlserveraddress,sqldbname,sqluser,sqlpass,sqlquery, Global) 
    VALUES ('" . $_POST["WidgetType"] . $sep . $_POST["DisplayText"] . $sep . $_POST["PositionX"] . $sep . $_POST["PositionY"] . $sep . 
    $_POST["SizeX"] . $sep . $_POST["SizeY"] . $sep . $_POST["URL"] . $sep . $_POST["CSSClass"] . $sep . str_replace("'", "''",$_POST["Notes"]) . $sep . $dashboardid . $sep .
    $sqlserveraddress . $sep . $sqldbname . $sep . $sqlusername . $sep . $sqlpass . $sep . $sqlquery .
    "', '" . $globaldefault . "')";
	debuglog($select);
    debuglog($sessionID, "Session ID");
    debuglog($dashboardid, "Dashboard ID");

    //$stmt->bindParam(':notes', $_POST["Notes"]); // Failed, don't understand why.
    execquery($select);
    //echo "Finished; click <a href='index.php'>Here</a> to return to main dashboard.";
	header("Location: index.php" . "?SelectDashboardID=" . $dashboardid);
?>

</body>

</html>