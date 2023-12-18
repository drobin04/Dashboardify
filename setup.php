<html>
    <head>
        <script type="module" src="js/md-block.js"></script>
        <link type="text/css" rel="stylesheet" href="css/settings_page.css">
        <style>
            #siteurlconfig {width: 300px;}
            #SQLUpdate {width: 100%;}
            .TableResults, .TableResults tr, .TableResults th, .TableResults td {border: 1px black solid; text-align: center;}
        
            .custom_widget_providers textarea, .custom_widget_providers input {min-width: 500px;}
        </style>

    </head>
    <body>
        <div class="MainPanel">
        <md-block>
# Setup / Admin page for Dashboardify

This page is currently under construction. If you're seeing this and somehow using this app - Please contact Doug Robinson.

### [Click Here To Return To Dashboardify](index.php)

<?php
include_once('config/check_admin.php');
include_once('shared_functions.php');
include('actions/logoutredirect.php');
$css = "";
$urlvalue = "";
$widgetproviderlist = "";

if (isset($_GET["action"])) {

    $action = $_GET["action"];

    if ($action == "SubmitWidgetProvider") {
        $d = $_POST;

        // Get data from submit widgetprovider
        $widgetprovidername = $d["WidgetProviderName"];
        $widgetprovidercss = $d["WidgetProviderCSS"];
        $widgetproviderhtml = $d["WidgetProviderHTML"];
        $widgetproviderphp = $d["WidgetProviderPHP"];
        

        $select = "INSERT INTO CustomWidgetProviders (WidgetProviderName
        ,CSS_Styling, HTML_Content
        ,PHP_To_Run) VALUES (?, ?, ?, ?)";
        $localdb = getPDO_DBFile();
        //$rootPath = $_SERVER['DOCUMENT_ROOT'];
    	//$localdb = new PDO('sqlite:' . $rootPath . '/Dashboardify/Dashboardify.s3db');
        $stmt = $localdb->prepare($select);
        $stmt->bindParam(1,$widgetprovidername,PDO::PARAM_STR);
        $stmt->bindParam(2,$widgetprovidercss,PDO::PARAM_STR);
        $stmt->bindParam(3,$widgetproviderhtml,PDO::PARAM_STR);
        $stmt->bindParam(4,$widgetproviderphp,PDO::PARAM_STR);
        debuglog($stmt);
        
        $stmt->execute();
        echo "Widget Provider Registered Successfully.";
    }

    if ($action == "UpdateNewUserSettings") {

        $a_AuthType = $_POST["AuthType"];

        $select = "Update Settings
        
        Set Value = ?
        
        Where Name = 'AuthMode'";

        $localdb = getPDO_DBFile();
        //$rootPath = $_SERVER['DOCUMENT_ROOT'];
    	//$localdb = new PDO('sqlite:' . $rootPath . '/Dashboardify/Dashboardify.s3db');
        $stmt = $localdb->prepare($select);
        $stmt->bindParam(1,$a_AuthType,PDO::PARAM_STR);
        debuglog($stmt, "AuthMode query");
        
        $stmt->execute();

                
        $defaultdashimg = $_POST["defaultdashimage"];

        // Open the file in write mode
        $file = fopen('defaultdashboardurl.txt', 'w');

        // Write the value to the file
        fwrite($file, $defaultdashimg);

        // Close the file
        fclose($file);
        //header('Location: ../setup.php');
        //echo "Settings Registered Successfully.";
    }

}




$filename = 'Dashboardify.s3db';
$Exists = False;
If (doesDatabaseExist() && filesize($filename) > 0) {
    $Exists = True;
    if (!AmIAdmin()) { header('Location: index.php'); } // Redirect back to index if they don't have permissions to be here.

    $dbfound = "<div style='border: 1px black solid; display: inline; padding: 3px;'>Database file found. No Need To Create.</div> ";  // DB Found! Yay.
    
    if (scalarquery("Select Count(*) as Matches From Settings Where Name = 'SiteUrlConfig'", "Matches") == 0) {
        $urlvalue = "";
    } else {
        $urlvalue = scalarquery("Select Value From Settings Where Name = 'SiteUrlConfig'", "Value");
    
    }
} else {
    // Run this if db doesn't exist yet!!
    $dbfound = "The database file is either missing or has not been created yet. <br/>
    Would you like to try creating the database now? <br/>
    <a href='createDB.php'>Yes - Create the Dashboardify database.</a>";

    $urlvalue = ""; // Blank out SiteURLConfig as it's referenced from DB.



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
    <form method="POST" action="config/storesiteurlconfig.php">
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

<div id="userstable">
    <h2>Users</h2>

    <table>
    <tr><th>RecID</th><th>Username/Email</th><th>Admin</th><th>Actions</th></tr>
    <?php
    if ($Exists) {
    $userslist = selectquery("Select RecID, Email, Admin FROM Users");
    }
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
</div>
<br />
<div id="newuserexperience">
    <form id="NewUserSettings" method="POST" action="setup.php?action=UpdateNewUserSettings">
    <h2>New User Experience</h3>
    <p>Below are settings affecting new users on the system.</p>
    <label>Authentication Type: </label>
    <?php
    if ($Exists) {
    $authmode = scalarquery("Select Value From Settings Where Name = 'AuthMode'", "Value");
    } else {$authmode = "None";}
    ?>
    <select ID="ddlAuthType" name="AuthType">
        <option value='<?php echo $authmode?>' selected='selected'><?php echo $authmode?></option><!--default value in line above-->
        <option value="Password">Password</option>
        <option value="None">None</option>
    </select><br />

    <label>Default Dashboard Background Image For First Dashboard: </label>
    <input id='defaultdashimage' name='defaultdashimage' value='<?php echo $defaultdashimage ?>'></input>

    <br /><br />
    <button>Submit</button>

</form>
</div>

<div id="SQLUpdates">
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
</div>

<div id="CustomWidgetProviders" class="custom_widget_providers">
    <h2>Custom Widget Providers</h2>
    <!-- Table of results here -->
    <?php echo $widgetproviderlist ?>

    <form id="submitNewWidgetProvider" method="POST" action="setup.php?action=SubmitWidgetProvider">
    <label>Name for Custom Widget Provider: </label><br /><input id="WidgetProviderName" name="WidgetProviderName"><br />
    <label>CSS Styling For Widget:</label><br /><textarea id="CSS_For_Widget_Provider" name="WidgetProviderCSS"></textarea><br />
    <label>HTML Content To Display In Widget:</label><br /><textarea id="HTML_Content_For_Widget_Provider" name="WidgetProviderHTML"></textarea><br />
    <label>PHP To Run Before Loading Widget (To populate variables, if needed):</label><br /><textarea id="PHP_To_Run_For_Widget_Provider" name="WidgetProviderPHP"></textarea><br />
    <button>Submit</button>
    <!-- Note on how this should work
PHP code gets run BEFORE HTML content gets placed on the PHP side, 
That way PHP statements can be run that populate variables that will then be placed into the HTML content.

Also i want to find a way to embed the Widget data , raw, somewhere into the content of the widget, so that it might be accessed by javascript or something.

-->
    </form>
    <h3>

</div>
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
