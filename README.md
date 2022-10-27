# Dashboardify
webpage for developing customizeable personal dashboards


# Requirements / Setup

- Need to set up PHP to allow sqlite(3) / PDO extensions.
If this is not done, you may get a blank screen when loading the app, as almost all important data is pulled from the included Dashboardify.S3DB file.

- There is a line of code in index.php that controls the link structure for edit and delete buttons, called $siteurl , this needs to be updated with the base URL for the site. It is likely defaulted to localhost. 

# To-Do

- Add either a new column to track user ID, or use the existing Dashboard ID column (leaning towards new column, so dashboard ID column can be used for multiple dashboards)

- Work on functionality to create and select between multiple dashboards

- Update NewWidget code to save new widgets with current user's UserID so they get added properly and not filtered out...

# File / DB Structure

The UI consists of a Dashboard made up of multiple user-defined Widgets.

Dashboards are stored in the Dashboardify sqlite database table 'Dashboards'.

Widgets are stored in the table 'Widgets', and have a foreign key linking dashboards, 'DashboardRecID', which mathes Dashboards.RecID .

Dashboards table has a foreign key linking Users table, User ID.

When a user logs in, the interface queries for the current user ID from the session ID, then searches for Dashboards for the current user ID, and then selects the first one. 

Later feature can be to add a way of tracking default dashboard for user (for now just load first one).

# fix bug
Start-login has a bug when run from localhost where it appends /dashboardify/ to the url which breaks on my desktop...
