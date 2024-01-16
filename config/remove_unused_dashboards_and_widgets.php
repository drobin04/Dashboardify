<?php
include_once('../shared_functions.php');
include_once('check_admin.php');
breakifnotadmin();
echo "Removing the following records: <br /><br />";


//Remove dashboards where user doesn't exist - Select & execute this one first. 
$y = selectquery("Select d.name, u.Email 
From Dashboards d
Left Join Users u on d.UserID = u.RecID
Where u.RecID = '' Or u.RecID is NULL");
echo generateTableFromObjects($y);

$y2 = execquery("DELETE FROM Dashboards
WHERE UserID IN (
SELECT d.UserID
FROM Dashboards AS d
LEFT JOIN Users AS u ON d.UserID = u.RecID
WHERE u.RecID = '' OR u.RecID IS NULL
)");

echo "<br /><hr><br />";



//Remove widgets where dashboard doesn't exist
$x = selectquery("Select w.RecID, d.name From Widgets w 
	Left Join Dashboards d On w.DashboardRecID = d.DashboardID Where
	d.DashboardID = '' or d.DashboardID is null");
echo generateTableFromObjects($x);



$x2 = execquery("DELETE FROM Widgets
WHERE DashboardRecID IN (
    SELECT w.DashboardRecID
    FROM Widgets AS w
    LEFT JOIN Dashboards AS d ON w.DashboardRecID = d.DashboardID
    WHERE d.DashboardID = '' OR d.DashboardID IS NULL
)");




?>
<br />
<a href="../setup.php">Return to Setup page</a>