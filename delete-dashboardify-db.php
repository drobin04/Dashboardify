<?php
include_once('config/check_admin.php');
include_once('shared_functions.php');
include('actions/logoutredirect.php');
if (doesDatabaseExist()) {
    if (!isset($_COOKIE["SessionID"])) {
        echo "you don't have permission to do this, you're not logged in.";
        exit();
    }

    if (!IsAdmin(getCurrentUserID())) { redirect('index.php'); 
        exit();
    } // Redirect back to index if they don't have permissions to be here.

    $file_pointer = "Dashboardify.s3db";
    if (!unlink($file_pointer)) {
        echo("$file_pointer cannot be deleted due to an error");
    } else {
        echo("$file_pointer has been deleted");
    }

}

setcookie("SessionID", "/", time() - 3600, "/");
unset($_COOKIE['SessionID']);
redirect('index.php');
?>
<html>
    <head>
    <script type="module" src="js/md-block.js"></script>

</head>
<body>
<md-block>
Click to return to [the Setup page](setup.php) to continue setup.</md-block>
</body>
</html>
