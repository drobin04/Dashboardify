<?php
include_once('shared_functions.php');
include('actions/logoutredirect.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<html>
    <head>
        <link type="text/css" rel="stylesheet" href="css/settings_page.css">
        <style>
            #siteurlconfig {}
            #SQLUpdate {width: 100%;}
            .TableResults, .TableResults tr, .TableResults th, .TableResults td {border: 1px black solid; text-align: center;}
        
            .custom_widget_providers textarea, .custom_widget_providers input {min-width: 500px;}
            
            
            .tab {
            	border: 1px solid black;	
            	padding-left: 10px;
            	display: none;
            }
            
            .tabPanel {
            	border: 1px solid black;	
            }
            
            .tabs_nav a {
            	/* margin: 5px; */
            	padding: 5px;
            	border: 1px solid black;

            }
            .tabs_nav a:hover {
            	background-color: yellow;	
            }
            .tabs_row {
            	padding-bottom: 5px;	
            }
            
            div:target {display:block;}
            
        </style>
        </head>
    <body>
<!-- Everything should be below MainPanel for now -->
        <div class="MainPanel">
        
        
        	<h1> Group / Organization Membership Setup</h1>

			<a href="index.php">Click Here To Return To Dashboardify</a>
            
            <!--Ability to create a new group-->

            <!--Ability to join a group... Need to plan this out a bit-->


            <!--If current user is an admin, include an area to add other people to a group or remove them?-->

			<!--List existing memberships-->
        </div><!-- End of Main Panel -->
    </body>

</html>
