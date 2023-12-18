<html>
    <head>
    <script type="module" src="js/md-block.js"></script>

</head>
<body>
<html>
    <head>
    <script type="module" src="js/md-block.js"></script>

</head>
<body>
<?php
//include_once('actions/logoutredirect.php');
//include_once('config/check_admin.php');

setcookie("SessionID", "/", time() - 3600, "/");
unset($_COOKIE['SessionID']);
$file_pointer = "Dashboardify.s3db";
if (!unlink($file_pointer)) {
    echo("$file_pointer cannot be deleted due to an error");
} else {
    echo("$file_pointer has been deleted");
}
?>
<md-block>
Click to return to [the Setup page](setup.php) to continue setup.</md-block>
</body>
</html>
