<?php
    include_once('../shared_functions.php');

    $email = $_POST["email"];
    $sessionid = "";
    $userid = "";

    function GetUserIDFromEmail($eml) {
        $select = "SELECT RecID FROM Users Where Email = '" . $eml . "'";
        debuglog($select,"SQL Query Searching For User By Email");
        $results = selectquery($select);
        debuglog($results,"Results from SQL Query search");
        $user = $results[0]["RecID"];
        debuglog($user,"User info selected");
        return $user;
    }
    
    function CreateUserIDForEmail($eml) {
        $select = "Insert Into Users (Email) VALUES ('". $eml . "')";
        execquery($select);
    }

    function CreateSessionForID($id, $sessid) {
        $insert = "Insert Into Sessions (UserID, SessionID) VALUES ('". $id . "', '" . $sessid . "')";
        execquery($insert);
    }
    debuglog($_POST, "Incoming POST arguments");
    debuglog($_POST["email"], "Email address to be used for search");
    $userid = GetUserIDFromEmail($email);
    debuglog("Results of GetUserIDFromEmail: " . $userid);

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
        setcookie("SessionID", $sessionid, 2147483640, "/"); //Save session ID into cookie
        header("Location: ../index.php");

?>