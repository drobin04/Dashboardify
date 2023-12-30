<?php
// IMPORTANT - Check if user is logged in / is a valid user before doing anything. 


// Accept POST arguments for variables
if (isset($_COOKIE["SessionID"])) {
		// So what do we need to send to this page? 
		
		//GET request
		// with WidgetID
		// with Width
		// with Height
		// and..... ? I think the rest will be handled in the widget properties? 
		// Positioning can be handled using iframe on the index.php page
		
		include_once('../shared_functions.php');
		
		// get widget ID 
		$widgetID = $_GET["WidgetID"];
		
		
		// check if I actually own this widget
		if (DoIOwnThisWidget($widgetID)) {
			// 
			// Get variables
			$width = $_GET["Width"];
			$height = $_GET["Height"];
			$x_axis_column = "x";
			$y_axis_column = "y";
			
			$w = selectquery("select * From Widgets Where RecID = '" . $widgetID . "'")[0];
			if ($w["WidgetType"] == "SQLite Chart (PHPGD)") {
			// Get sqlite query
			$query = $w["sqlquery"];
			$rootPath = $_SERVER['DOCUMENT_ROOT'];
			$dbpath = $rootPath . '/Dashboardify/Dashboardify.s3db';
			$dsn = 'sqlite:' . $dbpath;
			$db_file = new PDO($dsn);
			$stmt1 = $db_file->prepare($w["sqlquery"]);
			$stmt1->execute();
			$results = $stmt1->fetchAll(PDO::FETCH_ASSOC);
				
			createBarChart($results, $x_axis_column, $y_axis_column, $width, $height);
						
			}
			
			
		}
}
// Function to create a bar chart
function createBarChart($resultSet, $xAxisColumn, $yAxisColumn, $width, $height) {
    // Create an array to store the x and y values
    $xValues = [];
    $yValues = [];

    // Extract the data from the result set
    foreach ($resultSet as $row) {
        $xValues[] = $row[$xAxisColumn];
        $yValues[] = $row[$yAxisColumn];
    }

    // Create the image canvas
    $imageWidth = $width;
    $imageHeight = $height;
    $image = imagecreatetruecolor($imageWidth, $imageHeight);

    // Define the colors for the chart
    $backgroundColor = imagecolorallocate($image, 255, 255, 255);
    $barColor = imagecolorallocate($image, 0, 0, 255);
    $textColor = imagecolorallocate($image, 0, 0, 0);

    // Fill the background color
    imagefilledrectangle($image, 0, 0, $imageWidth, $imageHeight, $backgroundColor);

    // Calculate the maximum y-axis value
    $maxYValue = max($yValues);

    // Calculate the height of each bar
    $barHeight = ($imageHeight - 50) / $maxYValue;

    // Draw the bars
    $barWidth = ($imageWidth - 50) / count($xValues);
    for ($i = 0; $i < count($xValues); $i++) {
        $x = 25 + ($barWidth * $i);
        $y = $imageHeight - 25 - ($yValues[$i] * $barHeight);
        imagefilledrectangle($image, $x, $y, $x + $barWidth - 5, $imageHeight - 25, $barColor);
        imagestring($image, 5, $x, $y - 20, $yValues[$i], $textColor);
        imagestring($image, 5, $x, $imageHeight - 20, $xValues[$i], $textColor);
    }

    // Output the image
    header("Content-type: image/png");
    imagepng($image);
    imagedestroy($image);
}


function generateTestImage() {
// populate w/ db
include('shared_functions.php');

$results = selectquery("Select DashboardRecID, Count(*) as Count From Widgets Group By DashboardRecID");


// Example usage
$resultSet = [
    ['x' => 'Category 1', 'y' => 10],
    ['x' => 'Category 2', 'y' => 5],
    ['x' => 'Category 3', 'y' => 8],
];

$xAxisColumn = 'DashboardRecID';
$yAxisColumn = 'Count';

createBarChart($results, $xAxisColumn, $yAxisColumn);
}
?>