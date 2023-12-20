<?php
include('../shared_functions.php');

if (isset($_GET["action"])) {
    switch ($_GET["action"]) {
        case "movewidget":
            $x_position = $_GET["x"];
            $y_position = $_GET["y"];
            $recid = $_GET["RecID"];

            execquery("Update Widgets Set PositionX = '" . $x_position . "', PositionY = '" . $y_position . "' Where RecID = '" . $recid . "'");
            // check that user owns this widget
            break;


    }

}


?>
