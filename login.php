<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head><title>
	Dashboardify Login
</title>
    <style>
        .MainPanel {
            width: 200px !important;
            height: 70px !important;
        }
        .MainPanel option {
           border-left-width: inherit;
           border-right-width: inherit;
        }
		
		body {
    background-color: lightblue;
}

		.MainPanel {
			margin: 40px auto;
			border-color: black;
			border: 1px solid black;
			background-color: white;
			padding: 30px;
			border-radius: 25px;
			overflow: auto;
			height: auto;
			width: auto;

		}

		.MainPanel input, .MainPanel textarea, .MainPanel select {
			border-left-width: inherit;
		}

		.container {
			width: 100% !important;
		}

		.htmlLit table {
			width: 100%;
			border: 1px solid;
			border-spacing: 0px !important;
			border-collapse: collapse;
		}

		.htmlLit th {
			border: 1px solid;
			border-collapse: collapse;
		}

		.htmlLit td {
			border: 1px solid;
			border-collapse: collapse;
			text-align: center;
		}

		
    </style>
</head>
<body>

<div class="MainPanel">




<!--Login screen-->
<form name="LoginForm" method="post" action="http://douglasrobinson.me/Dashboardify/login.php">
<label>Email Address:</label><br />
<input id="email" name="email" /><br />
<input type="Submit" value="Send Auth Token" />
</form>

<?php
//print_r($_POST);
// Takes raw data from the request
//$json = file_get_contents('php://input');

// Converts it into a PHP object
//$data = json_decode($json);
echo "this is from PHP!"
//echo $data->email;
?>

</div>

</body>

</html>
