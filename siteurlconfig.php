<?php 
if (file_exists('config/siteurlconfig.txt')) {
    $siteurl = file_get_contents('config/siteurlconfig.txt');
    If ((isset($siteurl)) && $siteurl != '') {
        
    } else {
        header('Location: setup.php');
    }
} else {
    header('Location: setup.php');
}   

?>