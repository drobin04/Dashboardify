<?php

// Get the request body as a JSON string
$data = file_get_contents('php://input');

// Parse the JSON string to a PHP object
$payload = json_decode($data);

// Open the SQLite database
$db = new SQLite3('Dashboardify.s3db');

// Check if a record already exists for the given userID and bookname
$stmt = $db->prepare('SELECT COUNT(*) FROM booktracking WHERE userID = :userID AND bookname = :bookname');
$stmt->bindValue(':userID', $payload->userID, SQLITE3_TEXT);
$stmt->bindValue(':bookname', $payload->bookname, SQLITE3_TEXT);
$result = $stmt->execute()->fetchArray();

if ($result[0] == 0) {
  // Insert a new record
  $stmt = $db->prepare('INSERT INTO booktracking (userID, bookname, location) VALUES (:userID, :bookname, :location)');
} else {
  // Update an existing record
  $stmt = $db->prepare('UPDATE booktracking SET location = :location WHERE userID = :userID AND bookname = :bookname');
}

// Bind the parameters and execute the statement
$stmt->bindValue(':userID', $payload->userID, SQLITE3_TEXT);
$stmt->bindValue(':bookname', $payload->bookname, SQLITE3_TEXT);
$stmt->bindValue(':location', json_encode($payload->location), SQLITE3_TEXT);
$stmt->execute();

// Close the database
$db->close();

?>