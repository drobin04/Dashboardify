<?php
include('../shared_functions.php');

if (isset($_GET["action"])) {
    switch ($_GET["action"]) {
        case "movewidget":
            $x_position = abs($_GET["x"]);
            $y_position = abs($_GET["y"]);
            $recid = $_GET["RecID"];
            if (DoIOwnThisWidget($recid)) { 
                $sql = "Update Widgets Set PositionX = ?, PositionY = ? Where RecID = ?";
                
                $localdb = getPDO_DBFile();
				$stmt1 = $localdb->prepare($sql);
				$stmt1->bindParam(1, $x_position, PDO::PARAM_STR);
				$stmt1->bindParam(2, $y_position, PDO::PARAM_STR);
				$stmt1->bindParam(3, $recid, PDO::PARAM_STR);
				$stmt1->execute();
                
            } else {
                echo "you don't have rights to update this widget!";
            }
                // check that user owns this widget
            break;
        case "resize":
            $x = abs($_GET["width"]);
            $y = abs($_GET["height"]);
            $recid = $_GET["id"];
            if (DoIOwnThisWidget($recid)) {
                $sql = "update widgets set SizeX = ?, SizeY = ? Where RecID = ?";
                
                $localdb = getPDO_DBFile();
				$stmt1 = $localdb->prepare($sql);
				$stmt1->bindParam(1, $x, PDO::PARAM_STR);
				$stmt1->bindParam(2, $y, PDO::PARAM_STR);
				$stmt1->bindParam(3, $recid, PDO::PARAM_STR);
				$stmt1->execute();
                
            }
            break;

        default :
            echo "action unrecognized.";
            break;
    } 

} else {
    echo "No Action Specified.";
}


?>
