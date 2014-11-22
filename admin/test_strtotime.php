<?php
$strtotime = "";
if(isset($_GET['strtotime'])) {
    // Nullbytes abfangen!
    if (strpos("tmp".$_GET['strtotime'], "\x00"))
        $_GET['strtotime'] = "";
    $strtotime = rawurldecode($_GET['strtotime']);
    $strtotime = strip_tags($strtotime);
    $strtotime = stripslashes($strtotime);
    $strtotime = trim($strtotime, "\x00..\x19");
    if(false !== ($timestamp = strtotime($strtotime)))
        $strtotime = date("Y-m-d-H-i",$timestamp);
    else
        $strtotime = "Error";
}
echo $strtotime;
?>