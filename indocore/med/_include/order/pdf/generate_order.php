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

//VARIABLE
$_po_date		= date("j-M-Y",strtotime($_po_date));
$_revision_time += 1;

//SQL
//Customer List
$sql_cus = "
SELECT
  a.it_code, a.it_model_no, a.it_desc, b.odit_unit_price, b.odit_qty, b.odit_unit_price * b.odit_qty AS amount, b.odit_remark, to_char(b.odit_delivery, 'DD-Mon-YYYY') AS delivery
FROM
  ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_order_item AS b ON (a.it_code = b.it_code)
WHERE b.ord_code = '$_code' ORDER BY a.it_code";
$cus_res =& query($sql_cus);
$row_cus = numQueryRows($cus_res);
//Warehouse List
if($_type_invoice == 0) {
	$sql_wh = "
	SELECT
	  a.it_code, b.boit_it_code_for, a.it_model_no,	a.it_desc, b.boit_qty, b.boit_remark
	FROM
	  ".ZKP_SQL."_tb_booking_item AS b JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
	WHERE b.book_idx = $_book_idx ORDER BY a.it_code";
	$wh_res =& query($sql_wh);
	$row_wh = numQueryRows($wh_res);
}
//Insurance
$sql_freight = "
SELECT
  a.it_code, a.it_model_no, a.it_desc, trunc(".ZKP_SQL."_getUserPrice(a.it_code, CURRENT_DATE)/1.1,2) AS user_price, b.odit_qty, trunc(".ZKP_SQL."_getUserPrice(a.it_code, CURRENT_DATE)/1.1 * b.odit_qty,2) AS amount, b.odit_remark, to_char(b.odit_delivery, 'DD-Mon-YYYY') AS delivery
FROM
  ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_order_item AS b ON (a.it_code = b.it_code)
WHERE b.ord_code = '$_code' ORDER BY a.it_code";
$res_freight =& query($sql_freight);
$row_frt = numQueryRows($res_freight);
//Cus info
$cus_sql	= "
SELECT
	(select cus_phone from ".ZKP_SQL."_tb_customer where cus_code='$_cus_to') AS cus_phone,		--0
	(select cus_fax from ".ZKP_SQL."_tb_customer where cus_code='$_cus_to') AS cus_fax,					--1
	(select cus_phone from ".ZKP_SQL."_tb_customer where cus_code='$_ship_to') AS ship_phone,	--2
	(select cus_fax from ".ZKP_SQL."_tb_customer where cus_code='$_ship_to') AS ship_fax				--3
";
$cus =& query($cus_sql);
$cus	 =& fetchRow($cus);

//TEMPLATE PDF ===================================================================================================
$tpl_pdf[1] = 'tpl_order_do.pdf';
$pdf		= new FPDI();

if(ZKP_SQL == 'IDC') {
	if($_dept == 'A') {
		$tpl_pdf[0] = 'tpl_order_sheet_A_idc.pdf';
		$tpl_pdf[2] = 'tpl_order_sj_A_idc.pdf';
	} else {
		$tpl_pdf[0] = 'tpl_order_sheet_B_idc.pdf';
		$tpl_pdf[2] = 'tpl_order_sj_B_idc.pdf';
	}

	$tpl_pdf[3] = 'tpl_order_invoice_idc.pdf';
	$tpl_pdf[4] = 'tpl_order_ekspedisi_idc.pdf';
	include "generate_order_a.php";
} else if(ZKP_SQL == 'MED')	{
	$tpl_pdf[0] = 'tpl_order_sheet_med.pdf';
	$tpl_pdf[2] = 'tpl_order_sj_med.pdf';
	$tpl_pdf[3] = 'tpl_order_invoice_med.pdf';
	$tpl_pdf[4] = 'tpl_order_ekspedisi_med.pdf';
	include "generate_order_b.php";
}

//PRINT END==========================================================================================================
//detimine the document name & document path.
$storage = PDF_STORAGE . "order/$currentDept/". date("Ym/", strtotime($_po_date));
$doc_name = trim($_code) . "_rev_" . ($_revision_time).".pdf";
error_log("dir? ". is_dir($storage));

(!is_dir($storage))? mkdir($storage, 0777, true) : 0;

//Save pdf document
$pdf->Output($storage.$doc_name, 'F');
?>
