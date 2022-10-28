<html>
<head></head>
<body>

<?php
    $db_file = new PDO('sqlite:Dashboardify.s3db');
    $email = $_POST["email"];
    $sessionid = "";
    $userid = "";

    function debuglog( $object=null, $label=null ){
        $message = json_encode($object, JSON_PRETTY_PRINT);
        $label = "Debug" . ($label ? " ($label): " : ': ');
        echo "<script>console.log(\"$label\", $message);</script>";
    }
    function GetUserIDFromEmail($eml) {
        $localdb = $db_file = new PDO('sqlite:Dashboardify.s3db');
        $select = "SELECT RecID FROM Users Where Email = '" . $eml . "'";
        debuglog($select,"SQL Query Searching For User By Email");
        $stmt = $localdb->prepare($select);
        // Execute statement.
        $stmt->execute();
        // Get the results.
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        debuglog($results,"Results from SQL Query search");
        $user = $results[0]["RecID"];
        debuglog($user,"User info selected");
        return $user;
        //return "hello";
    }
    function GUID()
    {
        if (function_exists('com_create_guid') === true)
        {
            return trim(com_create_guid(), '{}');
        }
    
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }
    function CreateUserIDForEmail($eml) {
        $insert = "Insert Into Users (Email) VALUES ('". $eml . "')";
        $localdb = new PDO('sqlite:Dashboardify.s3db'); //NECESSARY - Failed to load when referencing db_file from 
        $stmt = $localdb->prepare($insert);
        
        // Execute statement.
        $stmt->execute();
                    
    }

    function CreateSessionForID($id, $sessid) {
        
        $insert = "Insert Into Sessions (UserID, SessionID) VALUES ('". $id . "', '" . $sessid . "')";
        $localdb = new PDO('sqlite:Dashboardify.s3db'); //NECESSARY - Failed to load when referencing db_file from 
        $stmt = $localdb->prepare($insert);
        
        // Execute statement.
        $stmt->execute();
        

             
    }
     
    echo $_POST["email"] . ". <br />Trying to open sqlite db.<br />";
    debuglog($_POST, "Incoming POST arguments");
    debuglog($_POST["email"], "Email address to be used for search");


    $userid = GetUserIDFromEmail($email);

    If (is_scalar($userid)) {
        // do nothing, proceed to next section, where we'll set up their session id
        debuglog("User ID is Scalar, creating session ID");
    }
    else {
        // Create User record, re-run statement
        debuglog("User ID Not Found, Creating one.");
        CreateUserIDForEmail($email);
        $userid = GetUserIDFromEmail($email);

    }


    //Create new session for user
        $sessionid = GUID();
        CreateSessionForID($userid, $sessionid);
        debuglog($sessionid,"Session ID Created");
        setcookie("SessionID", $sessionid, 2147483640); //Save session ID into cookie
        header("Location: index.php");

?>

</body>

</html>