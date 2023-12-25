
## Cached Homepage for faster loading / reduced server bandwidth / CPU strain
In addition to the base functionality on index.php, there is also an HTML page, 'cachedpage.html', which loads the content of the last loaded dashboard for the current user, from LocalStorage (Gets populated via javascript into localstorage on every index.php load). 

This enables much faster loads of the page, as it's mostly static content that doesn't change frequently, and greatly reduces the number of times PHP needs to run on the server side for a page that may frequently be called as a new tab page / home page etc. 

## Steps to add new Widget Type

* Add dropdown entry to New Widget entry form
* If New fields are needed to capture information for this widget:
    * need to update createDB.php to ensure those fields get created when the DB is created, in case it gets deleted or refreshed onto a test environment.  
    * Need to update NewWidget.php to properly save the fields into the database
    * If New fields are added and need to be added to an existing DB, you may use the Manual SQL update on Setup.php, or update the s3db directly, if you have access to it.
* Update the code used for loading new widgets onto the dashboard, which is located after the code that's used to retrieve dashboards for the current user and select a dashboard (or create new if none found).

## SQL Server Connectivity

Note - When adding SQL Server connections, server names may need to be entered in serveraddress\instance format, if an instance other than the default is needed. 

## File / DB Structure

The UI consists of a Dashboard made up of multiple user-defined Widgets.

Dashboards are stored in the Dashboardify sqlite database table 'Dashboards'.

Widgets are stored in the table 'Widgets', and have a foreign key linking dashboards, 'DashboardRecID', which mathes Dashboards.RecID .

Dashboards table has a foreign key linking Users table, User ID.

When a user logs in, the interface queries for the current user ID from the session ID, then searches for Dashboards for the current user ID, and then selects the first one. 

Later feature can be to add a way of tracking default dashboard for user (for now just load first one).

## Debug Logging

There is a feature available to troubleshoot what's going on as various steps are performed when loading most pages: Debug logging.

This is accomplshed by using a function under the shared_functions.php file, 'debuglog()', which when called, will echo a <script> tag onto the current screen.

The script will take any string or *object* and enumerate it into the browser's development console. 

This means that if this feature is enabled, you will be able to track what's going on on each page, by opening the browser console. 

To enable this feature, you need to change the shared_functions.php file. Find the commented section under the debuglog() function, and change $debug_logging_enabled to True.
