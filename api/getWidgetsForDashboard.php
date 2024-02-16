<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once('../shared_functions.php');
$dashboardid = $_GET['recid'];
$dashboardwidgetdata = serializeWidgetsForDashboard($dashboardid);

$dashboardhash = hash('md5', $dashboardwidgetdata);

// Update dashboard record with hash. 
execquery_bind2("Update Dashboards Set HashedWidgetValue = ? Where DashboardID = ?", $dashboardhash, $dashboardid);


setcookie('hash_' . $dashboardid, $dashboardhash,  2147483640 ,'/');

// TODO:

// Send a cookie containing the hashed dashboard value. 

// On future requests, need to see if a hash was sent (check in a way that can accept nulls), and if so, check if it matches the dashboard's hash.

// If it matches the dashboard hash, then send back nothing (update the javascript to properly check for this and do nothing if nothing is returned

// If it doesn't match, generate the widget json data and send it back. 

// Format for hash cookie: 'hash_{dashboardidhere}': Hash value


echo $dashboardwidgetdata;


?>