<html>
<body>
<?php
	include_once('logoutredirect.php');
	include_once('../shared_functions.php');
	$qr = $_SERVER['QUERY_STRING']; // Get querystring value
	$d = str_replace("RecID=","",$qr); // strip out querystring key / header, just want the value
    
	if (DoIOwnThisWidget($d)) {

		$select = "Delete From Widgets Where RecID = '" . $d . "'";
		execquery($select);
		

	} else {

	}
	echo "<script>window.close();</script>Complete. Window should close now. <br />Query Executed: " . $select;
		
	echo "<a href='http://douglasrobinson.me/Dashboardify/'>Return to Management</a>";
	redirect("../index.php");
?>

</body>

</html>