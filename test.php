<?php

$dbfile = new PDO('sqlite:Dashboardify.s3db');
//$insert = "Insert Into Sessions (UserID, SessionID) VALUES ('". $id . "', '" . $sessid . "')";
$insert = "Insert Into Sessions (UserID, SessionID) VALUES ('2', 'C231E0C0-FBF1-4C7D-A113-725FF06D91F0')";
debuglog($insert,"Create Session insert query");
$stmt = $$dbfile->prepare($insert);
$stmt->execute();
$dbfile->exec($insert);
?>