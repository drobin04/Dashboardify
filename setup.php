<html>
    <head>
        <script type="module" src="js/md-block.js"></script>


    </head>
<?php

?>
    <body>
        <md-block>
# Setup / Admin page for Dashboardify

This page is currently under construction. If you're seeing this and somehow using this app - Please contact Doug Robinson.

### [Click Here To Return To Dashboardify](index.php)

<?php
include('shared_functions.php');

$filename = 'Dashboardify.s3db';
if (file_exists($filename) && filesize($filename) > 0) {
  echo "Database file found!! ";  

// For populating 'User's list below, we will collect list of users in this section
$userslist = selectquery("Select RecID, Email, Admin FROM Users");



} else {
    echo "The database file is either missing or has not been created yet. <br/>
    Would you like to try creating the database now? <br/>
    [Yes - Create the Dashboardify database.](createDB.php)";
}
if (file_exists('config/siteurlconfig.txt')) {
    $urlvalue = file_get_contents('config/siteurlconfig.txt');

}
if (file_exists('config/globalcss.css')) {
    $css = file_get_contents('config/globalcss.css');
}
?>


## Site URL config (required)
There are some items that will need to reference the base URL for this webpage. 
Please configure the box below with the site URL, in the format of ' https://example.com/this_site_directory/'
</md-block>
    <form action="config/storesiteurlconfig.php">
<input id="siteurlconfig" name="siteurlconfig" value="<?php echo $urlvalue ?>" ></input><button>Submit</button>
</form>
<md-block>
## Delete Database
WARNING: There is no confirmation after clicking this link!!!
[Delete Dashboardify DB](delete-dashboardify-db.php)



## Global Dashboard CSS - Default For All Users

This is the default CSS that will be loaded for everyone's dashboards, underneath any user-supplied custom CSS for their dashboards. 

</md-block>
<form action="config/savecss.php">
<textarea cols="50" rows="5" name="CSS"><?php echo $css ?></textarea><br />
<button>Submit</button>
</form>

<md-block>


## Users
</md-block>

<table>
<tr><th>RecID</th><th>Username/Email</th><th>Admin</th><thActions</th></tr>
<?php
if (isset($userslist)) {
    foreach ($userslist as $user) {
        if (!(Isset($user["Admin"])) Or ($user["Admin"] = "")) {
            $user["Admin"] = "N";
        }
        echo "<tr><td>" . $user["RecID"] . "</td><td>" . $user["Email"] . "</td><td>" . $user["Admin"] . "</td><td><a href='deleteuser.php?recID=" . $user["RecID"] 
        . "'>Delete?</a> | " . "<a href='make_admin.php?recID=" . $user["RecID"] 
        . "'>Make Admin?</a></tr><br />";
    }
}

?>
</table>
<md-block>




</md-block>
    </body>

</html>
