<?php
$rootPath = $_SERVER['DOCUMENT_ROOT'];
$db_file = new PDO('sqlite:' . $rootPath . '/Dashboardify/data/Dashboardify.s3db'); // Connect to SQLite database file.
$sessionid = $_COOKIE["SessionID"]; debuglog($sessionid, "SessionID"); //Get User for Session ID
$userid = selectquery("Select UserID From Sessions Where SessionID = '" . $sessionid . "'")[0]["UserID"]; debuglog($userid, "User ID found for user");
if (isadmin($userid)) { // Display Setup button if user is an admin. 
    echo "<button type='button' style='float:left !important;''><a class='nodeco' href='setup.php'>Setup</a></button>";
}
?>