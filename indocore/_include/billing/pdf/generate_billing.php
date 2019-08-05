<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*
*/
//PREPARE
require LIB_DIR . "fpdf/fpdi.php";

//TEMPORARRY VARIABLE
$_inv_date		= date("j-M-Y",strtotime($_inv_date));
$_po_date		= empty($_po_date) ? "" : date("j-M-Y", strtotime($_po_date));
$_revision_time += 1;

//SQL
//Customer List
$sql = "
SELECT
  a.it_code, a.it_model_no, a.it_desc, b.biit_unit_price, b.biit_qty, b.biit_unit_price * b.biit_qty AS amount, b.biit_remark
FROM
  ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_billing_item AS b ON (a.it_code = b.it_code)
WHERE b.bill_code = '$_code'
ORDER BY a.it_code
";
$result	=& query($sql);
$row_cus = numQueryRows($result);
//Warehouse List
if($_type_invoice == 0) {
	$sql_wh = "
	SELECT
	  a.it_code, b.boit_it_code_for, a.it_model_no, a.it_desc, b.boit_qty, b.boit_remark
	FROM
	  ".ZKP_SQL."_tb_booking_item AS b JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
	WHERE b.book_idx = $_book_idx
	ORDER BY a.it_code";
	$wh_res =& query($sql_wh);
	$row_wh = numQueryRows($wh_res);
}
//Ekspedisi
$sql_freight = "
SELECT
  a.it_code, a.it_model_no, a.it_desc, ".ZKP_SQL."_getUserPrice(a.it_code, CURRENT_DATE)/1.1, b.biit_qty, ".ZKP_SQL."_getUserPrice(a.it_code, CURRENT_DATE)/1.1 * b.biit_qty AS amount, b.biit_remark
FROM
  ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_billing_item AS b ON (a.it_code = b.it_code)
WHERE b.bill_code = '$_code'
ORDER BY a.it_code";
$res_freight =& query($sql_freight);
$row_frt = numQueryRows($res_freight);

//Cus info
$cus_sql	= "
SELECT
	(select cus_phone from ".ZKP_SQL."_tb_customer where cus_code='$_cus_to') AS cus_phone,				--0
	(select cus_fax from ".ZKP_SQL."_tb_customer where cus_code='$_cus_to') AS cus_fax,					--1
	(select cus_address from ".ZKP_SQL."_tb_customer where cus_code='$_ship_to') AS ship_address,		--2
	(select cus_phone from ".ZKP_SQL."_tb_customer where cus_code='$_ship_to') AS ship_phone,			--3
	(select cus_fax from ".ZKP_SQL."_tb_customer where cus_code='$_ship_to') AS ship_fax				--4
";
$cus_res =& query($cus_sql);
$cus	 =& fetchRow($cus_res);
$pdf = new FPDI();

//TEMPLATE PDF ===================================================================================================
$tpl_pdf = array();
$tpl_pdf[1] = "tpl_do_warehouse.pdf";

if(ZKP_SQL == 'IDC') {
	if($_paper_format == "A")			$tpl_pdf[0] = "tpl_invoice_idc_a.pdf";
	else if($_paper_format == "B")		$tpl_pdf[0] = "tpl_invoice_idc_b.pdf";
	$tpl_pdf[2] = "tpl_do_sj_idc.pdf";
	$tpl_pdf[3] = "tpl_do_ekspedisi_idc.pdf";
	$tpl_pdf[4] = "tpl_invoice_pajak_sederhana.pdf";
	include "generate_billing_idc.php";
} else if(ZKP_SQL == 'MED')	{
	if($_ordered_by == 1) {
		if($_paper_format == "A")			$tpl_pdf[0] = "tpl_med_invoice_a.pdf";
		else if($_paper_format == "B")		$tpl_pdf[0] = "tpl_med_invoice_b.pdf";
		$tpl_pdf[2] = "tpl_med_sj.pdf";
		$tpl_pdf[3] = "tpl_do_ekspedisi_med.pdf";
		$tpl_pdf[4] = "tpl_med_pajak_1.pdf";
		$tpl_pdf[5] = "tpl_med_pajak_2.pdf";
		$tpl_pdf[6] = "tpl_med_pajak_sederhana.pdf";
		include "generate_billing_med.php";
	} else if($_ordered_by == 2) {
		if($_paper_format == "A")			$tpl_pdf[0] = "tpl_smd_invoice_a.pdf";
		else if($_paper_format == "B")		$tpl_pdf[0] = "tpl_smd_invoice_b.pdf";
		$tpl_pdf[2] = "tpl_smd_sj.pdf";
		$tpl_pdf[3] = "tpl_smd_ekspedisi.pdf";
		$tpl_pdf[4] = "tpl_smd_pajak_1.pdf";
		$tpl_pdf[5] = "tpl_smd_pajak_2.pdf";
		$tpl_pdf[6] = "tpl_smd_pajak_sederhana.pdf";
		include "generate_billing_smd.php";
	}
}

//PRINT END ======================================================================================================
//detimine the document name & document path.
$storage = PDF_STORAGE . "billing/$currentDept/". date("Ym/", strtotime($_inv_date));
$doc_name = trim($_code) . "_rev_" . $_revision_time .".pdf";
(!is_dir($storage))? mkdir($storage, 0777, true) : 0;

//Save pdf document
$pdf->Output($storage.$doc_name, 'F');
?>