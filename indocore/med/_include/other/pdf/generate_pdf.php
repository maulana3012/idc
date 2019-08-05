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

$_inv_date	= date("j-M-Y",strtotime($_inv_date));
$_po_date	= empty($_po_date) ? "" : date("j-M-Y", strtotime($_po_date));
$_do_date	= empty($_do_date) ? "" : date("j-M-Y", strtotime($_do_date));
$_revision_time += 1;

//SQL
$whitem_sql = "
SELECT
  a.it_code, b.boit_it_code_for, a.it_model_no,	a.it_desc, b.boit_qty, b.boit_remark
FROM
  ".ZKP_SQL."_tb_booking_item AS b JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE b.book_idx = $_book_idx
ORDER BY a.it_code,b.boit_idx";
$cusitem_sql = "
SELECT
  a.it_code, a.it_model_no, a.it_desc, b.{$_do_type}it_qty, b.{$_do_type}it_remark
FROM
  ".ZKP_SQL."_tb_{$_do_type}_item AS b JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE {$_do_type}_code = '$_code'
ORDER BY it_code,{$_do_type}it_idx";
$cus_sql	= "
SELECT
	(select cus_contact_phone from ".ZKP_SQL."_tb_customer where cus_code='$_cus_to') AS cus_phone,		--0
	(select cus_fax from ".ZKP_SQL."_tb_customer where cus_code='$_cus_to') AS cus_fax,					--1
	(select cus_address from ".ZKP_SQL."_tb_customer where cus_code='$_ship_to') AS ship_address,		--2
	(select cus_contact_phone from ".ZKP_SQL."_tb_customer where cus_code='$_ship_to') AS ship_phone,	--3
	(select cus_fax from ".ZKP_SQL."_tb_customer where cus_code='$_ship_to') AS ship_fax				--4
";
$totalWHQty	 = 0;
$totalCusQty = 0;

$sql_freight = "
SELECT
  a.it_code, a.it_model_no, a.it_desc, ".ZKP_SQL."_getUserPrice(a.it_code, CURRENT_DATE)/1.1, d.{$_do_type}it_qty, ".ZKP_SQL."_getUserPrice(a.it_code, CURRENT_DATE)/1.1 * d.{$_do_type}it_qty AS amount, d.{$_do_type}it_remark
FROM
  ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_{$_do_type}_item AS d ON (a.it_code = d.it_code)
WHERE d.{$_do_type}_code = '$_code'
ORDER BY a.it_code";

$whitem_res		=& query($whitem_sql);
$cusitem_res	=& query($cusitem_sql);
$res_freight	=& query($sql_freight);
$cus_res 		=& query($cus_sql); $cus =& fetchRow($cus_res);

$pdf = new FPDI();

//GENERATE PDF ========================================================================================================
if($_do_type == 'dt')	$tpl_pdf[0]	= "tpl_dt.pdf";
if($_do_type == 'dr')	$tpl_pdf[0]	= "tpl_dr.pdf";
if($_do_type == 'df')	$tpl_pdf[0]	= "tpl_df.pdf";

include "generate_do_warehouse.php";
include "generate_do_sj.php";
include "generate_do_ekspedisi.php";

//PRINT END==========================================================================================================
//detimine the document name & document path.
$storage = PDF_STORAGE . "other_document/$currentDept/". date("Ym/", strtotime($_issued_date));
$doc_name = trim($_code) . "_rev_" . $_revision_time .".pdf";
(!is_dir($storage))? mkdir($storage, 0777, true) : 0;

//Save pdf document
$pdf->Output($storage.$doc_name, 'F');
?>