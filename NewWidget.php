<html>
<head>    <?php include_once("actions/logoutredirect.php");?> </head>
<body>

<?php
// Test this code...
// Maps widget object from POST.
class CustomObject {
    public $WidgetType;
    public $DisplayText;
    public $PositionX;
    public $PositionY;
    public $SizeX;
    public $SizeY;
    public $URL;
    public $CSSClass;
    public $Notes;
    public $GlobalDefault;
    public $SQLServerAddressName;
    public $SQLDBName;
    public $sqluser;
    public $sqlpass;
    public $sqlquery;
    public $ID;
    public $DashboardRecID;

    public function mapFromPost($postArray) {
        foreach ($postArray as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = (string)$value;
            }
        }
    }
}

// Example usage
$customObject = new CustomObject();
$customObject->mapFromPost($_POST);
?>


<?php
    include_once('shared_functions.php');
	$sessionID = $_COOKIE["SessionID"];
	$userid = getCurrentUserID();
    function GetUsersDashboard() {
        $select = "SELECT S.UserID, D.RecID As DashboardRecID FROM Sessions S Left Join Dashboards D On D.UserID = S.UserID Where SessionID = '" . $_COOKIE["SessionID"] . "'";
        //debuglog($select,"SQL Query Searching For User Dashboard By SessionID");
        // Get the results.
        $results = selectquery($select);
        //debuglog($results,"Results from SQL Query search");
        $dash = $results[0]["DashboardRecID"];
        //debuglog($user,"User info selected");
        return $dash;
    }

    $post_data = file_get_contents('php://input');
    $break_if_tried_editing_widget_without_permission = false;

    $dashboardid = $_POST["dashboardID"]; //Identify which dashboard the widget needs to be added to.

    
	//Delete Existing Widget ID if Edit ID passed: 
	If ($_POST["ID"] <> "") {
        $widgetID = $_POST["ID"];
        if (DoIOwnThisWidget($widgetID)) {
            $sql = "Delete From Widgets Where RecID = ?";
            execquery_bind1($sql, $_POST["ID"]);
            echo "DELETED WIDGET WITH ID '" . $_POST["ID"] . "', Inserting new copy now...";
        } else {
            $break_if_tried_editing_widget_without_permission = true;
            redirect("index.php" . "?SelectDashboardID=" . $dashboardid);
        }
		
		redirect("index.php" . "?SelectDashboardID=" . $dashboardid);
	}
    if (!$break_if_tried_editing_widget_without_permission) {
        //Insert new Widget

        //Prepare Variables
        $sep = "','"; // Seperator
        $sqlserveraddress = $_POST["SQLServerAddressName"];
        $sqldbname = $_POST["SQLDBName"];
        $sqlusername = $_POST["sqluser"];
        $sqlpass = $_POST["sqlpass"];
        $sqlquery = str_replace("'","''",$_POST["sqlquery"]);
        
        $globaldefault = "";
        
        // Break if user is trying to save a DBQuery value without having access. 
        if (isset($_POST["sqlqeury"])) {
        	include_once('config/check_admin.php');
        	if (!AmIAdmin()) {
        		echo "Failed to save widget. You are trying to save a widget with a SQL query, though you are not marked as an admin. To remove this constraint, edit NewWidget.php.";
        		exit();	
        	}
        }

        if (isset($_POST["GlobalDefault"])) {
            $globaldefault = $_POST["GlobalDefault"];

        } else {
            $globaldefault = "0";
        }

        $notesvalue = str_replace("'", "''",$_POST["Notes"]);
        // Prepare INSERT statement.
        $select = "INSERT INTO Widgets (WidgetType,BookmarkDisplayText,PositionX,PositionY,SizeX,SizeY,WidgetURL,WidgetCSSClass,Notes,DashboardRecID
        ,sqlserveraddress,sqldbname,sqluser,sqlpass,sqlquery, Global, UserRecID) 
        VALUES (?, ?, ?, ?, ?,?,?,?,?,?,?,?,?,?,?,?,?)";
        
        $localdb = getPDO_DBFile();
		$stmt1 = $localdb->prepare($select);
		$stmt1->bindParam(1, $_POST["WidgetType"], PDO::PARAM_STR);
		$stmt1->bindParam(2, $_POST["DisplayText"], PDO::PARAM_STR);
		$stmt1->bindParam(3, $_POST["PositionX"], PDO::PARAM_STR);
		$stmt1->bindParam(4, $_POST["PositionY"], PDO::PARAM_STR);
		$stmt1->bindParam(5, $_POST["SizeX"], PDO::PARAM_STR);
		$stmt1->bindParam(6, $_POST["SizeY"], PDO::PARAM_STR);
		$stmt1->bindParam(7, $_POST["URL"], PDO::PARAM_STR);
		$stmt1->bindParam(8, $_POST["CSSClass"], PDO::PARAM_STR);
		$stmt1->bindParam(9, $notesvalue, PDO::PARAM_STR);
		$stmt1->bindParam(10, $dashboardid, PDO::PARAM_STR);
		$stmt1->bindParam(11, $sqlserveraddress, PDO::PARAM_STR);
		$stmt1->bindParam(12, $sqldbname, PDO::PARAM_STR);
		$stmt1->bindParam(13, $sqlusername, PDO::PARAM_STR);
		$stmt1->bindParam(14, $sqlpass, PDO::PARAM_STR);
		$stmt1->bindParam(15, $sqlquery, PDO::PARAM_STR);
		$stmt1->bindParam(16, $globaldefault, PDO::PARAM_STR);
		$stmt1->bindParam(17, $userid, PDO::PARAM_STR);		
		$stmt1->execute();
            //echo "Finished; click <a href='index.php'>Here</a> to return to main dashboard.";
	    redirect("index.php" . "?SelectDashboardID=" . $dashboardid);
    } else {
        redirect("index.php");
    }

?>

</body>

</html>
