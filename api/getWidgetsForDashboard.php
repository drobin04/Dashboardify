<?php

include_once('../shared_functions.php');
$dashboardid = $_GET['recid'];
echo serializeWidgetsForDashboard($dashboardid);


?>