<?php
include_once('../shared_functions.php');
setcookie("SessionID", "/", time() - 3600, "/");
setcookie("SessionID", "", time() - 3600); // Expire cookie by setting it to an hour ago.
redirect("start-login.php");
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head><title>
	Dashboardify Logout
</title>
    <style>
       
    </style>
</head>
<body>

</div>

</body>

</html>
