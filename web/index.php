<?php
// cheesy as all hell, but for now means I don't have to frig about with
// cgi setup or anything worse...
$url = $_GET['url'];
$cmd = "cd /home/ben/proj/hnews_checker; ./check_hnews";
if( $url )
    $cmd .= " {$url}";
$cmd .= " 2>&1";
passthru( $cmd );
?>
