<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*/
require_once "../../../zk_config.php";

$file	= $_GET['_file'];
if(!file_exists($file))
    die("<span class='comment'>File does not exist: $file</span>");

//Output PDF
header('Content-Type: application/pdf');
header('Content-Length: '.filesize($file));
header('Content-disposition: inline; filename="'.($_code."_rev_".$_rev.".pdf").'"');
readfile($file);
?> 
