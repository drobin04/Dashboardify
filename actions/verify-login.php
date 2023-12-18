<?php
    include_once('../shared_functions.php');
    $sessionid = "";
    $userid = "";

    // Request comes in. 
    $authmode = getAuthMode(); //record auth mode
    $userfound = false; // prep variable

    $Email = $_POST["email"];
    $password = "";
    if (isset($_POST["password"])) {
        $password = $_POST["password"];
    }
    $action = "";
    if (isset($_GET["action"])) {
        $action = $_GET["action"];

        CreateUserIDForEmail($Email, $password);
        $userid = GetUserIDFromEmail($Email);
        complete_login($userid);


    } else { // Try to log in for existing user
        if (scalarquery("Select Count(*) as matches from users where Email = '" . $Email . "'", "matches") == 1) {
            // If we find a match for username in system
            $userid = GetUserIDFromEmail($Email); // get the user ID.
            debuglog("Results of GetUserIDFromEmail: " . $userid);
    
            If (is_scalar($userid)) {
                $userfound = true; // found match for username
                // See if pw matches
                authenticate($Email, $userid, $password, $userfound); 
            }
            // else {
            //     if ($authmode == "None") {
            //             // Create User record, re-run statement
            //             complete_login($userid);

                    
                
            //     } else {
            //         // failed to authenticate!
            //         failed_auth();
            //     }
    
    
            // }
        } else {
                // Create User record, re-run statement
                debuglog("User ID Not Found, Creating one.");
                CreateUserIDForEmail($Email, $password);
                $userid = GetUserIDFromEmail($Email);
                complete_login($userid);
            
        }
    }
exit();
    
function failed_auth() {
    header('Location: start-login.php?msg=Failed_Auth_On_Verification');
}






    function GetUserIDFromEmail($eml) {
        $select = "SELECT RecID FROM Users Where Email = '" . $eml . "'";
        debuglog($select,"SQL Query Searching For User By Email");
        $results = selectquery($select);
        debuglog($results,"Results from SQL Query search");
        $user = $results[0]["RecID"];
        debuglog($user,"User info selected");
        return $user;
    }
    
    function CreateUserIDForEmail($eml, $pwd) {
        $select = "Insert Into Users (Email, password) VALUES (?,?)";
        
        $db = getPDO_DBFile();
        $stmt = $db->prepare($select);
        $stmt->bindParam(1,$eml,PDO::PARAM_STR);
        $stmt->bindParam(2,$pwd,PDO::PARAM_STR);
        $stmt->execute();

        //execquery($select);
    }

    function CreateSessionForID($id, $sessid) {
        $insert = "Insert Into Sessions (UserID, SessionID) VALUES ('". $id . "', '" . $sessid . "')";
        execquery($insert);
    }   

    
    
    //Create new session for user, IF password matches OR if new user. 
    function authenticate($eml, $userid, $password, $userfound) {
        if (getAuthMode() == "None") {
            complete_login($userid);
        } elseif (getAuthMode() == "Password") {
            if ($userfound) {
                $userpw = scalarquery("Select Password From Users Where Email = '" . $eml . "'", "password");
                if ($userpw == $password) {
                    complete_login($userid);
                } else {
                    failed_auth();
                    
                }
            }



        }
    }

    function complete_login($uid) {
            $sessionid = GUID();
            CreateSessionForID($uid, $sessionid);
            setcookie("SessionID", $sessionid, 2147483640, "/"); //Save session ID into cookie
            header("Location: ../index.php");
        }
?>