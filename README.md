# Dashboardify
webpage for developing customizeable personal dashboards


# Requirements

Need to set up PHP to allow sqlite(3) / PDO extensions.
If this is not done, you may get a blank screen when loading the app, as almost all important data is pulled from the included Dashboardify.S3DB file.


# To-Do

- Update index page / homepage to read in SessionID cookie, match to a userID, and then load in only widgets where the userID is present

- Add either a new column to track user ID, or use the existing Dashboard ID column (leaning towards new column, so dashboard ID column can be used for multiple dashboards)

- Work on functionality to create and select between multiple dashboards
