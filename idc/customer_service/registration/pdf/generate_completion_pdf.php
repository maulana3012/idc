<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*
*/
//PAREPARE
require LIB_DIR . "fpdf/fpdi.php";
$pdf = new FPDI();

//Global
$sql = "SELECT * FROM ".ZKP_SQL."_tb_service_reg WHERE sg_code='$_code'";
$res = query($sql);
$col = fetchRowAssoc($res);

$item_sql	= "SELECT * FROM ".ZKP_SQL."_tb_service_reg_item WHERE sg_code = '$_code' ORDER BY it_code";
$item_res	= query($item_sql);
$item_row = numQueryRows($item_res);

if(ZKP_SQL == 'IDC') {
	$tpl_pdf = "tpl_idc_service_completion.pdf";
} else if(ZKP_SQL == 'MED')	{
	$tpl_pdf = "tpl_med_service_completion.pdf";
}

$counter = array(0,1);
$qty = array(0,0);
$row = array(9, $item_row); //0.limit, 1.total item
$page = array(1, ceil($row[1] / $row[0]));
while ($counter[0] < $row[1]) {
	include "generate_completion_2_pdf.php";
	$counter[0] += $row[0];
	$page[0]++;
}

//PRINT END==========================================================================================================
//detimine the document name & document path.
$storage = PDF_STORAGE . "billing/service/". date("Ym/", strtotime($col['sg_receive_date']));
$doc_name = trim($_code) . "_rev_f.pdf";
(!is_dir($storage))? mkdir($storage, 0777, true) : 0;

//Save pdf document
$pdf->Output($storage.$doc_name, 'F');
?>