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
$_reg_date	= date("j-M-Y",strtotime($_reg_date));
$_revision_time += 1;
if(isset($_source_customer) && $_source_customer==1) {
	$_cus_name		= $_cus_name;
	$_cus_address	= $_cus_address;
} else if($_cus_to != '') {
	$_cus_name		= $_cus_name;
	$_cus_address	= $_cus_address;
} else {
	$_cus_name		= $_make_cus_name;
	$_cus_address	= $_make_cus_address;
}

if(ZKP_SQL == 'IDC') {
	$tpl_pdf = "tpl_idc_service_reg.pdf";
} else if(ZKP_SQL == 'MED')	{
	$tpl_pdf = "tpl_med_service_reg.pdf";
}

//sql information
$item_sql	= "SELECT it_code,sgit_model_no,sgit_serial_number,sgit_cus_complain FROM ".ZKP_SQL."_tb_service_reg_item WHERE sg_code = '$_code' ORDER BY it_code";
$item_res	= query($item_sql);
$item_row = numQueryRows($item_res);

$counter = array(0,1);
$qty = array(0,0);
$row = array(10, $item_row); //0.limit, 1.total item
$page = array(1, ceil($row[1] / $row[0]));
while ($counter[0] < $row[1]) {
	include "generate_registration_2_pdf.php";
	$counter[0] += $row[0];
	$page[0]++;
}


//PRINT END==========================================================================================================
//detimine the document name & document path.
$storage = PDF_STORAGE . "billing/service/". date("Ym/", strtotime($_reg_date));
$doc_name = trim($_code) . "_rev_" . $_revision_time .".pdf";
(!is_dir($storage))? mkdir($storage, 0777, true) : 0;

//Save pdf document
$pdf->Output($storage.$doc_name, 'F');
?>