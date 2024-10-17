<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//include_once('config/check_admin.php');
include_once('../shared_functions.php');
include('logoutredirect.php');

// TODO: 
// Get Dashboard RecID From URL
$dashboardrecid = $_GET["recid"];

// Check whether this dashboard belongs to current user
if (DoIOwnThisDashboard($dashboardrecid)) {
    echo "Dashboard will get deleted - " . $dashboardrecid;

// If dashboard belongs to current user, delete dashboard from DB using an escaped / not injectable function.
echo "You can now return to Dashboardify.";

execquery_bind1("Delete From Dashboards Where DashboardID = ?", $dashboardrecid);


} else {
    echo "Ownership check failed.";
}



redirect("../index.php");

?>
