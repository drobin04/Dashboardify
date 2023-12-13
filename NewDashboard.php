<html>
<head>    <?php include("logoutredirect.php");?> </head>
<body>

<?php
    include("shared_functions.php");

    debuglog($_POST);
    $dashboardname = $_POST["dashboardname"];


    //debuglog("shared functions module loaded.");
    $db_file = new PDO('sqlite:Dashboardify.s3db'); // Connect to SQLite database file.
    $sessionid = $_COOKIE["SessionID"]; debuglog($sessionid, "SessionID");
    //Get User for Session ID
    $userid = selectquery("Select UserID From Sessions Where SessionID = '" . $sessionid . "'")[0]["UserID"];
    debuglog($userid, "User ID found for user");

	

    $post_data = file_get_contents('php://input');
    $sep = "','"; // Seperator

    // Prepare INSERT statement.
    $dashboardid = GUID();
	$select = "INSERT INTO Dashboards (Name, DashboardID,UserID) VALUES ('" . $dashboardname . "', '" . $dashboardid . "','" . $userid . "')";
	$stmt = $db_file->prepare($select);
	
	// Execute statement.
	$stmt->execute();
	header("Location: index.php");
?>

</body>

</html>