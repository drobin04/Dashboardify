<html>
    <head>
        <script type="module" src="js/md-block.js"></script>
        <link type="text/css" rel="stylesheet" href="css/settings_page.css">
        <style>
            #siteurlconfig {width: 300px;}
            #SQLUpdate {width: 100%;}
            .TableResults, .TableResults tr, .TableResults th, .TableResults td {border: 1px black solid; text-align: center;}
        </style>

    </head>
<?php

?>
    <body>
        <div class="MainPanel">
        <md-block>
# Setup / Admin page for Dashboardify

This page is currently under construction. If you're seeing this and somehow using this app - Please contact Doug Robinson.

### [Click Here To Return To Dashboardify](index.php)

<?php
include('shared_functions.php');
$css = "";
$urlvalue = "";

$filename = 'Dashboardify.s3db';
if (file_exists($filename) && filesize($filename) > 0) {
  $dbfound = "<div style='border: 1px black solid; display: inline; padding: 3px;'>Database file found. No Need To Create.</div> ";  




} else {
    $dbfound = "The database file is either missing or has not been created yet. <br/>
    Would you like to try creating the database now? <br/>
    <a href='createDB.php'>Yes - Create the Dashboardify database.</a>";
    //$dbfound = "";
}
if (file_exists('config/siteurlconfig.txt')) {
    $urlvalue = file_get_contents('config/siteurlconfig.txt');

}
if (file_exists('config/defaultdashboardurl.txt')) {
    $defaultdashimage = file_get_contents('config/defaultdashboardurl.txt');
} else { $defaultdashimage = ""; }
if (file_exists('config/globalcss.css')) {
    $css = file_get_contents('config/globalcss.css');
}

if (isset($_GET["SQLUpdate"])) {
    $sqlqueryresults = "";

    $sql = $_GET["SQLUpdate"];
    $operationtype = $_GET["SQLOperationType"];

    if ($operationtype == "Exec") {
        execquery($sql);
        $sqlqueryresults = "Query Executed.";
    } elseif ($operationtype == "Select") {
        $results = selectquery($sql);
        $sqlqueryresults = generateTableFromObjects($results);
    }



} else { $sqlqueryresults = "";}

?>


## Site URL config (required)
There are some items that will need to reference the base URL for this webpage. 
Please configure the box below with the site URL, in the format of ' https://example.com/this_site_directory/'
</md-block>
    <form action="config/storesiteurlconfig.php">
<input id="siteurlconfig" name="siteurlconfig" value="<?php echo $urlvalue ?>" ></input><button>Submit</button>
</form>

<h2>Create/Delete Database</h2>
<?php echo $dbfound ?> <br />

<p>WARNING: There is no confirmation after clicking this link!!!
<a href="delete-dashboardify-db.php">Delete Dashboardify DB</a></p>
<md-block>


## Global Dashboard CSS - Default For All Users

This is the default CSS that will be loaded for everyone's dashboards, underneath any user-supplied custom CSS for their dashboards. 

</md-block>
<form action="config/savecss.php">
<textarea cols="50" rows="5" name="CSS"><?php echo $css ?></textarea><br />
<button>Submit</button>
</form>

<h2>Users</h2>

<table>
<tr><th>RecID</th><th>Username/Email</th><th>Admin</th><th>Actions</th></tr>
<?php
$userslist = selectquery("Select RecID, Email, Admin FROM Users");

if (isset($userslist)) {
    foreach ($userslist as $user) {
        if (($user["Admin"] != "1") Or ($user["Admin"] = "")) {
            $user["Admin"] = "N";
        }
        else { $user["Admin"] = "Y";}
        echo "<tr><td>" . $user["RecID"] . "</td><td>" . $user["Email"] . "</td><td>" . $user["Admin"] . "</td><td><a href='config/delete_user.php?recID=" . $user["RecID"] 
        . "'>Delete?</a> " . "<a href='config/make_admin.php?recID=" . $user["RecID"] 
        . "'>Make Admin?</a> <a href='config/remove_admin.php?recID=" . $user["RecID"] . "'>Remove Admin?</a></tr>";


    }
    
    //echo "<br /><br />" . generateTableFromObjects($userslist);
}

?>
</table>
<br />

<h2>New User Experience</h3>
<p>Below are settings affecting new users on the system.</p>
<label>Authentication Type: </label>
<?php
$authmode = scalarquery("Select Value From Settings Where Name = 'AuthMode'", "Value");
?>
<select ID="ddlAuthType" name="AuthType">
    <option value='<?php echo $authmode?>' selected='selected'><?php echo $authmode?></option><!--default value in line above-->
    <option value="Password">Password</option>

</select><br />

<label>Default Dashboard Background Image For First Dashboard: </label><input id='defaultdashimage' name='defaultdashimage' value='<?php echo $defaultdashimage ?>'></input>


<form action="setup.php">
<h2>Manual SQL Updates</h2>

<input type="radio" id="exec" name="SQLOperationType" value="Exec">
<label for="exec">Execute (No Results)</label><br>
<input type="radio" id="select" name="SQLOperationType" value="Select">
<label for="select">Select (With Results)</label><br>
<br />
<textarea id="SQLUpdate" name="SQLUpdate"></textarea><br />
<button>Submit</button>
</form>

<?php echo $sqlqueryresults ?>


<br /><br />
<h3>Update Code</h3>
<p>The following button will allow you to update your app's code from the main Dashboardify repo.
Backup your files first!! 
It's possible that changes may have occurred in newer versions of the repo, which expect database columns which might not exist yet on your version, and which would cause problems. 

Future update planned to support checking DB for missing pieces.</p>
<br />
<a href="clone.php">Update Site From Dashboardify Repo</a>

</div>
    </body>

</html>
