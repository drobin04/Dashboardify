<html>
<body>
<?php
	include_once('logoutredirect.php');
	include_once('../shared_functions.php');

    $d = $_GET['RecID'];
    //echo "Widget Posted: " . $d;
	if (DoIOwnThisWidget($d)) {

		$select = "Delete From Widgets Where RecID = ?";
		execquery_bind1($select, $d);
		echo "Deleted Widget " . $d;
		
	} else {
		echo "You don't own this widget.";
	}
	
?>

</body>

</html>
