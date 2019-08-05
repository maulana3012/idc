<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*/
require_once "../../../zk_config.php";

$_code		= $_GET['_code'];
$_rev		= $_GET['_rev'];
$_po_date	= date("Ym", strtotime($_GET['_po_date']));

$f = PDF_STORAGE . "purchasing/po_local/{$_po_date}/{$_code}_rev_{$_rev}.pdf";

if(!file_exists($f))
    die("File does not exist: $f");

//Output PDF
header('Content-Type: application/pdf');
header('Content-Length: '.filesize($f));
header('Content-disposition: inline; filename="'.($_code."_rev_".$_rev.".pdf").'"');
readfile($f);
?> 