<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$workingdir = __DIR__;
$wkdir2 = $_SERVER['DOCUMENT_ROOT'];
echo "Working directory: __DIR__: " . $workingdir;
echo "<br />";
echo "Working directory: " . $wkdir2;
echo "<br />";



?>

<br />
<br />
<?php include ('shared_functions.php'); echo "User Folder: " . getCurrentUserFolder(); ?>