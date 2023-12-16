# Steps to add new Widget Type

* Add dropdown entry to New Widget entry form
* If New fields are needed to capture information for this widget:
    * need to update createDB.php to ensure those fields get created when the DB is created, in case it gets deleted or refreshed onto a test environment.  
    * Need to update NewWidget.php to properly save the fields into the database
    * If New fields are added and need to be added to an existing DB, you may use the Manual SQL update on Setup.php, or update the s3db directly, if you have access to it.
* Update the code used for loading new widgets onto the dashboard, which is located after the code that's used to retrieve dashboards for the current user and select a dashboard (or create new if none found).