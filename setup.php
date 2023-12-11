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


<?php

$filename = 'Dashboardify.s3db';
if (file_exists($filename) && filesize($filename) > 0) {
  echo "Database file found!! ";  
} else {
    echo "The database file is either missing or has not been created yet. <br/>
    Would you like to try creating the database now? <br/>
    [Yes - Create the Dashboardify database.](createDB.php)";
}
?>


## Site URL config
There are some items that will need to reference the base URL for this webpage. 
Please configure the box below with the site URL, in the format of ' https://example.com/this_site_directory/'



        </md-block>
    </body>

</html>
