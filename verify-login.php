<html>
<head></head>
<body>

<?php
    $db_file = new PDO('sqlite:Dashboardify.s3db');
    $email = $_POST["email"];
    function GetUserIDFromEmail() {
        $localdb = $db_file = new PDO('sqlite:Dashboardify.s3db'); //NECESSARY - Failed to load when referencing db_file from outside function block!!!
        //echo "1...";
        $select = "SELECT RecID FROM Users Where Email = '" . $email . "'";
        //echo "2...";
        $stmt = $localdb->prepare($select);
        //echo "3...";
        // Execute statement.
        $stmt->execute();
        //echo "4...";
        // Get the results.
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $user = $results[0]["RecID"];
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
    function CreateUserIDForEmail() {
        $insert = "Insert Into Users (Email) VALUES ('". $email . "')";
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

    $userid = GetUserIDFromEmail();

    echo "User Found?: " . $userid;

    If (is_scalar($userid)) {
        // do nothing, proceed to next section, where we'll set up their session id
        echo "User ID Found in DB! - " . $userid;
    }
    else {
        // Create User record, re-run statement
        echo "<br />About to try creating user id. <br />";
        CreateUserIDForEmail();

        
        $userid = GetUserIDFromEmail();

    }


    //Create new session for user
        //If is_scalar(...The result of the sql query to get user ID from table...)
        $sessionid = GUID();
        echo "Session ID: " . $sessionid;

        CreateSessionForID($userid, $sessionid);
        echo "Creating cookie...";
        setcookie("SessionID", $sessionid);

    //Save session ID into cookie

   
?>

</body>

</html>