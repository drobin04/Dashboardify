<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('../shared_functions.php');

$workingdir = __DIR__;
$wkdir2 = $_SERVER['DOCUMENT_ROOT'];
echo "Working directory: __DIR__: " . $workingdir;
echo "<br />";
echo "Working directory: " . $wkdir2;
echo "<br />";
echo "Working directory (Shared Functions __DIR__): " . checkdir();


?>
