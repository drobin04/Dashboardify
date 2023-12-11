# Dashboardify
Web-app for developing personal, customizeable dashboards with bookmarks, embedded frames, as well as database and API connectivity.

# Requirements / Setup

- Need to set up PHP to allow sqlite(3) / PDO extensions.
If this is not done, you may get a blank screen when loading the app, as almost all important data is pulled from the included Dashboardify.S3DB file.

- There is a line of code in index.php that controls the link structure for edit and delete buttons, called $siteurl , this needs to be updated with the base URL for the site. It is likely defaulted to localhost. 

- For SQL Server connectivity, the following extensions need to be enabled (uncommented) in php.ini, and the 2 included dll files in the /ext folder are needed, which contain microsoft's sql server driver for PHP.
    extension=php_sqlsrv_56_ts.dll
    extension=php_pdo_sqlsrv_56_ts.dll  


## To-do: Update setup documentation.... Start to finish of deploying app to new server...


# File / DB Structure

The UI consists of a Dashboard made up of multiple user-defined Widgets.

Dashboards are stored in the Dashboardify sqlite database table 'Dashboards'.

Widgets are stored in the table 'Widgets', and have a foreign key linking dashboards, 'DashboardRecID', which mathes Dashboards.RecID .

Dashboards table has a foreign key linking Users table, User ID.

When a user logs in, the interface queries for the current user ID from the session ID, then searches for Dashboards for the current user ID, and then selects the first one. 

Later feature can be to add a way of tracking default dashboard for user (for now just load first one).

# SQL Server Connectivity

Note - When adding SQL Server connections, server names may need to be entered in serveraddress\instance format, if an instance other than the default is needed. 