<?php
include('check_admin.php');
include_once('../shared_functions.php');
$rootPath = $_SERVER['DOCUMENT_ROOT'];
$db_file2 = $rootPath . '/Dashboardify/Dashboardify.s3db';

echo $db_file2;
if (file_exists($db_file2)) { echo "file found.";}
if (isadmin(getCurrentUserID())) {

$IncomingUserID = $_GET["recID"];

$query = "Update Users Set Admin = 1 Where RecID = " . $IncomingUserID . "";
execquery($query);
header('Location: ../setup.php');
echo "query: " . $query;
} else {
    echo "You don't have rights to do this. <a href='../setup.php'>Return to Setup.</a>";
}


?>