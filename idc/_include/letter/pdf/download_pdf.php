<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*/
require_once "../../../zk_config.php";

$_dept		= $_GET['_dept'];
$_code_no	= substr($_GET['_code'],0,4);
$_code_type	= substr($_GET['_code'],9,1);
$_rev		= $_GET['_rev'];
$_reg_date	= $_GET['_reg_date'];

$f = PDF_STORAGE . "letter/{$_dept}/{$_reg_date}/{$_code_no}{$_code_type}_rev_{$_rev}.pdf";

if(!file_exists($f))
    die("File does not exist: $f");

//Output PDF
header('Content-Type: application/pdf');
header('Content-Length: '.filesize($f));
header('Content-disposition: inline; filename="'.($_code."_rev_".$_rev.".pdf").'"');
readfile($f);
?>