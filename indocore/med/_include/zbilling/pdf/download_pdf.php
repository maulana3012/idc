<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*/
require_once "../../../zk_config.php";

$_dept		= $_GET['_dept'];
$_code		= $_GET['_code'];
$_rev		= $_GET['_rev'];
$_inv_date	= $_GET['_inv_date'];

$f = PDF_STORAGE . "billing/{$_dept}/{$_inv_date}/{$_code}_rev_{$_rev}.pdf";

if(!file_exists($f))
    die("File does not exist: $f");

//Output PDF
header('Content-Type: application/pdf');
header('Content-Length: '.filesize($f));
header('Content-disposition: inline; filename="'.($_code."_rev_".$_rev.".pdf").'"');
readfile($f);
?>