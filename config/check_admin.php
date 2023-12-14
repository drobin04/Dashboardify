<?php

function IsAdmin($userID) {
    
    $query = "Select Admin From Users Where RecID = '" . $userID . "'";

    $db_file = new PDO('sqlite:../Dashboardify/Dashboardify.s3db');
    $stmt1 = $db_file->prepare($query);
    $stmt1->execute();
    $results = $stmt1->fetchAll(PDO::FETCH_ASSOC);
    debuglog_admin($results, "Admin_check_Results");
    If ($results[0]["Admin"] == "1") {
        return true;
    } else {
        return false;
    }
    //return $results;
}

function debuglog_admin( $object=null, $label=null ){
    $message = json_encode($object, JSON_PRETTY_PRINT);
    $label = "Debug" . ($label ? " ($label): " : ': ');
    echo "<script>console.log(\"$label\", $message);</script>";
}



?>