<?php
header("Access-Control-Allow-Origin: *");
// get request method
$method = $_SERVER['REQUEST_METHOD'];
if ($method == 'GET') {
	echo "THIS IS A GET REQUEST. testasdfasgeag";

	$dbpath = dirname(__FILE__,2) . '/Dashboardify.s3db';
	$db_file = new PDO('sqlite:' . $dbpath);

	$select = "INSERT INTO Events (path,body,headers) VALUES (?,?,?)";



	$stmt = $db_file->prepare($select);
	$path = $_SERVER["QUERY_STRING"];
	$body = '';
	$headers = '';
	$stmt->bindParam(1,$path,PDO::PARAM_STR);
	$stmt->bindParam(2,$body,PDO::PARAM_STR);
	$stmt->bindParam(3,$headers,PDO::PARAM_STR);

	//echo $stmt;
	// Execute statement.
	$stmt->execute();
	

}
if ($method == 'POST') {
	echo "THIS IS A POST REQUEST";
}
if ($method == 'PUT') {
	echo "THIS IS A PUT REQUEST";
}
if ($method == 'DELETE') {
	echo "THIS IS A DELETE REQUEST";
}
?>