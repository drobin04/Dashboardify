# Dashboardify
webpage for developing customizeable personal dashboards


# Requirements

Need to set up PHP to allow sqlite(3) / PDO extensions.
If this is not done, you may get a blank screen when loading the app, as almost all important data is pulled from the included Dashboardify.S3DB file.


# To-Do

- Update index page / homepage to read in SessionID cookie, match to a userID, and then load in only widgets where the userID is present

- Add either a new column to track user ID, or use the existing Dashboard ID column (leaning towards new column, so dashboard ID column can be used for multiple dashboards)

- Work on functionality to create and select between multiple dashboards

- Update login page to properly create user accounts if they don't exist - seems to fail when i test


# File / DB Structure

The UI consists of a Dashboard made up of multiple user-defined Widgets.

Dashboards are stored in the Dashboardify sqlite database table 'Dashboards'.

Widgets are stored in the table 'Widgets', and have a foreign key linking dashboards, 'DashboardRecID', which mathes Dashboards.RecID .

Dashboards table has a foreign key linking Users table, User ID.

When a user logs in, the interface needs to query for the current user ID from the session ID, then search for Dashboards for the current user ID, and then select one. 

+ If there are no dashboards for the current user, the interface needs to create one, and then grab the new guid for it and load that dashboard.

Later feature can be to add a way of tracking default dashboard for user (for now just load first one).

# fix bug
Start-login has a bug when run from localhost where it appends /dashboardify/ to the url which breaks on my desktop...

