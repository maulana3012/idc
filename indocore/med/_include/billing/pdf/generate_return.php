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

$_return_date 	= date("j-M-Y",strtotime($_return_date));
$_po_date 		= empty($_po_date) ? "" : date("j-M-Y", strtotime($_po_date));
$_revision_time += 1;

//SQL
//Cus list
$sql = "
SELECT
  a.it_code, a.it_model_no, a.it_desc, b.reit_unit_price, b.reit_qty, b.reit_unit_price * b.reit_qty AS amount, b.reit_remark
FROM
  ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_return_item AS b ON (a.it_code = b.it_code)
WHERE b.turn_code = '$_code'
ORDER BY a.it_code
";
$result	=& query($sql);
$row_cus = numQueryRows($result);
//Warehouse list
if($_paper == 0) {
	$sql_wh = "
	SELECT
		a.it_code, b.istd_it_code_for, a.it_model_no, a.it_desc, b.istd_qty, b.istd_remark
	FROM ".ZKP_SQL."_tb_outstanding_item as b JOIN ".ZKP_SQL."_tb_item as a USING(it_code)
	WHERE std_idx = $_std_idx
	ORDER BY it_code,istd_idx
	";
	$wh_res	=& query($sql_wh);
	$row_wh = numQueryRows($wh_res);
}
//information
$cus_sql	= "
SELECT
	(select cus_contact_phone from ".ZKP_SQL."_tb_customer where cus_code='$_cus_to') AS cus_phone,		--0
	(select cus_fax from ".ZKP_SQL."_tb_customer where cus_code='$_cus_to') AS cus_fax,					--1
	(select cus_address from ".ZKP_SQL."_tb_customer where cus_code='$_ship_to') AS ship_address,		--2
	(select cus_contact_phone from ".ZKP_SQL."_tb_customer where cus_code='$_ship_to') AS ship_phone,	--3
	(select cus_fax from ".ZKP_SQL."_tb_customer where cus_code='$_ship_to') AS ship_fax,				--4
	(select bill_inv_date from ".ZKP_SQL."_tb_billing where bill_code='$_bill_code') AS bill_date		--5
";
$cus_res =& query($cus_sql);
$cus	 =& fetchRow($cus_res);

//TEMPLATE PDF ===================================================================================================
$pdf = new FPDI();
$tpl_pdf = array();

if(ZKP_SQL == 'IDC') {
	$row	 = array(23, 0, $row_cus, 0, 0);
	if($_vat_val>0) {
		if($_disc>0) $row[1]=4;
		else		 $row[1]=2;
	} else {
		if($_disc>0) $row[1]=2;
	}
	$row[3] = $row[1]+$row[2];
	$row[4] = $row[0]-$row[1];
	$page = array(1, ceil($row[3] / $row[0]));

	$tpl_pdf[0] = "tpl_return_invoice_idc.pdf";
	$doc_for = "CUSTOMER";
	$counter = array(0,0); $total = array(0,0,0,0,0);
	while ($counter[0] < $row[2]) {
		include "generate_return_invoice_idc.php"; $page[0]++;
	}
	$doc_for = "ADMIN";
	$counter = array(0,0); $total = array(0,0,0,0,0);
	while ($counter[0] < $row[2]) {
		include "generate_return_invoice_idc.php"; $page[0]++;
	}
} else if(ZKP_SQL == 'MED')	{
	if($_ordered_by == 1) {
		$tpl_pdf[0] = "tpl_med_invoice_return.pdf";
	} else if($_ordered_by == 2) {
		$tpl_pdf[0] = "tpl_smd_invoice_return.pdf";
	}

	$row	 = array(22, 0, $row_cus, 0, 0);
	if($_vat_val>0) {
		if($_disc>0) $row[1]=4;
		else		 $row[1]=2;
	} else {
		if($_disc>0) $row[1]=2;
	}
	$row[3] = $row[1]+$row[2];
	$row[4] = $row[0]-$row[1];
	$page = array(1, ceil($row[3] / $row[0]));
	$counter = array(0,0); $total = array(0,0,0,0,0);
	while ($counter[0] < $row[2]) {
		include "generate_return_invoice_med.php"; $page[0]++;
	}
}

//NOTA RETURN =========================================================================================================
if ($_vat > 0) {
	$tpl_pdf[2] = "tpl_return_pajak.pdf";
	$counter= array(0,1);					// counter, no item
	$total	= array(0,0,0);					// row 1, 2, 4
	$row	= array(12, 0, $row_cus);		//0. default limit, 1. adding row, 2.jumlah item, 3.jumlah total baris, 4.-
	while ($counter[0] < $row[2]) {
		include "generate_return_pajak.php";
	}
}

//DO RETURN WAREHOUSE =================================================================================================
if($_paper == 0) {
	$tpl_pdf[1] = "tpl_return_warehouse.pdf";
	$counter = array(0,0);
	$qty = array(0,0);
	$row = array(19, 19, $row_wh, $row_cus); //0.wh limit, 1.cus limit, 2.total wh, 3.total cus
	$big = ($row_wh>$row_cus) ? 0 : 1;
	$page = array(1, ceil($row[$big+2] / $row[$big]));

	while ($counter[$big] < $row[$big+2]) {
		include "generate_return_warehouse_pdf.php";
		$page[0]++;
	}
}

//PRINT END==========================================================================================================
//detimine the document name & document path.
$storage = PDF_STORAGE . "billing/$currentDept/". date("Ym/", strtotime($_return_date));
$doc_name = trim($_code) . "_rev_" . $_revision_time .".pdf";
(!is_dir($storage))? mkdir($storage, 0777, true) : 0;

//Save pdf document
$pdf->Output($storage.$doc_name, 'F');
?>