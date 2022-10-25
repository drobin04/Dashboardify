<html>
<body>
<?php
	$qr = $_SERVER['QUERY_STRING'];
	echo $qr . "<br />";
        $d = str_replace("RecID=","",$qr);
        echo $d . "<br />";       
    	// Create and connect to SQLite database file.
	$db_file = new PDO('sqlite:Dashboardify.s3db');
	// Prepare INSERT statement.
	$select = "Delete From Widgets Where RecID = '" . $d . "'";
	$stmt = $db_file->prepare($select);
	
	// Execute statement.
	$stmt->execute();
	echo "<script>window.close();</script>Complete. Window should close now. <br />Query Executed: " . $select;
	
	echo "<a href='http://douglasrobinson.me/Dashboardify/'>Return to Management</a>"
?>

</body>

</html>