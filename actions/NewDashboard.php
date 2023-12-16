<html>
<head>    <?php include_once("../actions/logoutredirect.php");?> </head>
<body>

<?php
    include_once("../shared_functions.php"); // Using for SQLite connectivity via execquery / selectquery functions, and debuglogging

    debuglog($_POST);
    $dashboardname = $_POST["dashboardname"];

    $sessionid = $_COOKIE["SessionID"]; debuglog($sessionid, "SessionID");
    //Get User for Session ID
    $userid = selectquery("Select UserID From Sessions Where SessionID = '" . $sessionid . "'")[0]["UserID"];
    debuglog($userid, "User ID found for user");

    $post_data = file_get_contents('php://input');
    $sep = "','"; // Seperator

    // Prepare INSERT statement.
    $dashboardid = GUID();
	$select = "INSERT INTO Dashboards (Name, DashboardID,UserID) VALUES ('" . $dashboardname . "', '" . $dashboardid . "','" . $userid . "')";
	execquery($select);
	header("Location: ../index.php");
?>

</body>

</html>