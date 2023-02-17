<?php

// Get the data from the message body
$data = json_decode(file_get_contents('php://input'), true);

// Get the userID and bookname from the data
$userID = $data['userID'];
$bookname = $data['bookname'];

// Open the SQLite database
$db = new SQLite3('Dashboardify.s3db');

// Prepare and execute a SELECT statement to retrieve the location data
$stmt = $db->prepare('SELECT location FROM booktracking WHERE userID = :userID AND bookname = :bookname');
$stmt->bindValue(':userID', $userID, SQLITE3_TEXT);
$stmt->bindValue(':bookname', $bookname, SQLITE3_TEXT);
$result = $stmt->execute()->fetchArray();

// Close the database
$db->close();

if ($result) {
  // If a matching record was found, return the location data as JSON
  header('Content-Type: application/json');
  echo $result['location'];
} else {
  // If no matching record was found, return an error message
  header('HTTP/1.0 404 Not Found');
  echo 'No record found for userID ' . $userID . ' and bookname ' . $bookname;
}

?>