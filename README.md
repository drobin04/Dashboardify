# Dashboardify
webpage for developing customizeable personal dashboards


# Requirements / Setup

- Need to set up PHP to allow sqlite(3) / PDO extensions.
If this is not done, you may get a blank screen when loading the app, as almost all important data is pulled from the included Dashboardify.S3DB file.

- There is a line of code in index.php that controls the link structure for edit and delete buttons, called $siteurl , this needs to be updated with the base URL for the site. It is likely defaulted to localhost. 

# File / DB Structure

The UI consists of a Dashboard made up of multiple user-defined Widgets.

Dashboards are stored in the Dashboardify sqlite database table 'Dashboards'.

Widgets are stored in the table 'Widgets', and have a foreign key linking dashboards, 'DashboardRecID', which mathes Dashboards.RecID .

Dashboards table has a foreign key linking Users table, User ID.

When a user logs in, the interface queries for the current user ID from the session ID, then searches for Dashboards for the current user ID, and then selects the first one. 

Later feature can be to add a way of tracking default dashboard for user (for now just load first one).

# To-Do

- Work on functionality to create and select between multiple dashboards
-- Dropdownlist next to existing buttons at top left, with list of dashboards user has (probably just by recid for now).
-- Probably javascript that handles the event when dropdownlist is changed, to load the selected dashboard. 
-- 

- Consider loading a small image from a site's root /favicon.ico above each bookmark for a more natural look, maybe this could be an optional feature.

# fix bug
Start-login has a bug when run from localhost where it appends /dashboardify/ to the url which breaks on my desktop...

For now, have added code when saving a widget to find the dashboard for the user as just looking up  their first dashboard... Later will need to update to properly receive from index.php which dashboard the user is on. 