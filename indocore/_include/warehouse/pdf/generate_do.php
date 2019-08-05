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

//Sql
$cus_sql	= "
SELECT
	'D'||substr('$_out_doc_ref',2) AS do_no,
	CASE
		WHEN $_out_doc_type = '1' then (select bill_inv_date from ".ZKP_SQL."_tb_billing where bill_code='$_out_doc_ref')
		WHEN $_out_doc_type = '2' then (select ord_po_date from ".ZKP_SQL."_tb_order where ord_code='$_out_doc_ref')
		WHEN $_out_doc_type = '3' then (select dt_date from ".ZKP_SQL."_tb_dt where dt_code='$_out_doc_ref')
		WHEN $_out_doc_type = '4' then (select df_date from ".ZKP_SQL."_tb_df where df_code='$_out_doc_ref')
		WHEN $_out_doc_type = '5' then (select dr_date from ".ZKP_SQL."_tb_dr where dr_code='$_out_doc_ref')
		WHEN $_out_doc_type = '6' then (select req_issued_date from ".ZKP_SQL."_tb_request where req_code='$_out_doc_ref')
	END AS inv_date,
	CASE
		WHEN $_out_doc_type = '1' then (select bill_do_date from ".ZKP_SQL."_tb_billing where bill_code='$_out_doc_ref')
		WHEN $_out_doc_type = '2' then (select ord_po_date from ".ZKP_SQL."_tb_order where ord_code='$_out_doc_ref')
		WHEN $_out_doc_type = '3' then (select dt_date from ".ZKP_SQL."_tb_dt where dt_code='$_out_doc_ref')
		WHEN $_out_doc_type = '4' then (select df_date from ".ZKP_SQL."_tb_df where df_code='$_out_doc_ref')
		WHEN $_out_doc_type = '5' then (select dr_date from ".ZKP_SQL."_tb_dr where dr_code='$_out_doc_ref')
		WHEN $_out_doc_type = '6' then (select req_issued_date from ".ZKP_SQL."_tb_request where req_code='$_out_doc_ref')
	END AS do_date,
	CASE
		WHEN $_out_doc_type = '1' then (select bill_po_no from ".ZKP_SQL."_tb_billing where bill_code='$_out_doc_ref')
		WHEN $_out_doc_type = '2' then (select ord_po_no from ".ZKP_SQL."_tb_order where ord_code='$_out_doc_ref')
		ELSE ''
	END AS po_no,
	CASE
		WHEN $_out_doc_type = '1' then (select bill_po_date from ".ZKP_SQL."_tb_billing where bill_code='$_out_doc_ref')
		WHEN $_out_doc_type = '2' then (select ord_po_date from ".ZKP_SQL."_tb_order where ord_code='$_out_doc_ref')
		ELSE null
	END AS po_date,
	CASE
		WHEN $_out_doc_type = '1' then (select bill_cus_to from ".ZKP_SQL."_tb_billing where bill_code='$_out_doc_ref')
		WHEN $_out_doc_type = '2' then (select ord_cus_to from ".ZKP_SQL."_tb_order where ord_code='$_out_doc_ref')
		WHEN $_out_doc_type = '3' then (select dt_cus_to from ".ZKP_SQL."_tb_dt where dt_code='$_out_doc_ref')
		WHEN $_out_doc_type = '4' then (select df_cus_to from ".ZKP_SQL."_tb_df where df_code='$_out_doc_ref')
		WHEN $_out_doc_type = '5' then (select dr_cus_to from ".ZKP_SQL."_tb_dr where dr_code='$_out_doc_ref')
		WHEN $_out_doc_type = '6' then '7CUS'
	END AS cus_to,
	CASE
		WHEN $_out_doc_type = '1' then (select cus_full_name from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_billing on cus_code=bill_cus_to where bill_code='$_out_doc_ref')
		WHEN $_out_doc_type = '2' then (select cus_full_name from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_order on cus_code=ord_cus_to where ord_code='$_out_doc_ref')
		WHEN $_out_doc_type = '3' then (select cus_full_name from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_dt on cus_code=dt_cus_to where dt_code='$_out_doc_ref')
		WHEN $_out_doc_type = '4' then (select cus_full_name from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_df on cus_code=df_cus_to where df_code='$_out_doc_ref')
		WHEN $_out_doc_type = '5' then (select cus_full_name from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_dr on cus_code=dr_cus_to where dr_code='$_out_doc_ref')
		WHEN $_out_doc_type = '6' then (select cus_full_name from ".ZKP_SQL."_tb_customer where cus_code='7CUS')
	END AS cus_to_name,
	CASE
		WHEN $_out_doc_type = '1' then (select cus_phone from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_billing on cus_code=bill_cus_to where bill_code='$_out_doc_ref')
		WHEN $_out_doc_type = '2' then (select cus_phone from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_order on cus_code=ord_cus_to where ord_code='$_out_doc_ref')
		WHEN $_out_doc_type = '3' then (select cus_phone from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_dt on cus_code=dt_cus_to where dt_code='$_out_doc_ref')
		WHEN $_out_doc_type = '4' then (select cus_phone from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_df on cus_code=df_cus_to where df_code='$_out_doc_ref')
		WHEN $_out_doc_type = '5' then (select cus_phone from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_dr on cus_code=dr_cus_to where dr_code='$_out_doc_ref')
		WHEN $_out_doc_type = '6' then (select cus_phone from ".ZKP_SQL."_tb_customer where cus_code='7CUS')
	END AS cus_to_phone,
	CASE
		WHEN $_out_doc_type = '1' then (select cus_fax from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_billing on cus_code=bill_cus_to where bill_code='$_out_doc_ref')
		WHEN $_out_doc_type = '2' then (select cus_fax from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_order on cus_code=ord_cus_to where ord_code='$_out_doc_ref')
		WHEN $_out_doc_type = '3' then (select cus_fax from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_dt on cus_code=dt_cus_to where dt_code='$_out_doc_ref')
		WHEN $_out_doc_type = '4' then (select cus_fax from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_df on cus_code=df_cus_to where df_code='$_out_doc_ref')
		WHEN $_out_doc_type = '5' then (select cus_fax from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_dr on cus_code=dr_cus_to where dr_code='$_out_doc_ref')
		WHEN $_out_doc_type = '6' then (select cus_fax from ".ZKP_SQL."_tb_customer where cus_code='7CUS')
	END AS cus_to_fax,
	CASE
		WHEN $_out_doc_type = '1' then (select cus_address from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_billing on cus_code=bill_cus_to where bill_code='$_out_doc_ref')
		WHEN $_out_doc_type = '2' then (select cus_address from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_order on cus_code=ord_cus_to where ord_code='$_out_doc_ref')
		WHEN $_out_doc_type = '3' then (select cus_address from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_dt on cus_code=dt_cus_to where dt_code='$_out_doc_ref')
		WHEN $_out_doc_type = '4' then (select cus_address from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_df on cus_code=df_cus_to where df_code='$_out_doc_ref')
		WHEN $_out_doc_type = '5' then (select cus_address from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_dr on cus_code=dr_cus_to where dr_code='$_out_doc_ref')
		WHEN $_out_doc_type = '6' then (select cus_address from ".ZKP_SQL."_tb_customer where cus_code='7CUS')
	END AS cus_to_address,

	'$_cus_code' AS ship_to,
	(select cus_full_name from ".ZKP_SQL."_tb_customer where cus_code='$_cus_code') AS ship_to_name,
	(select cus_phone from ".ZKP_SQL."_tb_customer where cus_code='$_cus_code') AS ship_to_phone,
	(select cus_fax from ".ZKP_SQL."_tb_customer where cus_code='$_cus_code') AS ship_to_fax,
	(select cus_address from ".ZKP_SQL."_tb_customer where cus_code='$_cus_code') AS ship_to_address
";
$res_cus =& query($cus_sql);
$info	 =& fetchRowAssoc($res_cus);

$sql_cus  = "SELECT it_code, it_model_no, it_desc, ";
if($_out_doc_type == 1) {
	$sql_cus  .= "biit_qty, biit_remark FROM ".ZKP_SQL."_tb_billing_item JOIN ".ZKP_SQL."_tb_item USING(it_code) WHERE bill_code='$_out_doc_ref' ";
} else if($_out_doc_type == 2) {
	$sql_cus  .= "odit_qty, odit_remark FROM ".ZKP_SQL."_tb_order_item JOIN ".ZKP_SQL."_tb_item USING(it_code) WHERE ord_code='$_out_doc_ref' ";
} else if($_out_doc_type == 3) {
	$sql_cus  .= "dtit_qty, dtit_remark FROM ".ZKP_SQL."_tb_dt_item JOIN ".ZKP_SQL."_tb_item USING(it_code) WHERE dt_code='$_out_doc_ref' ";
} else if($_out_doc_type == 4) {
	$sql_cus  .= "dfit_qty, dfit_remark FROM ".ZKP_SQL."_tb_df_item JOIN ".ZKP_SQL."_tb_item USING(it_code) WHERE df_code='$_out_doc_ref' ";
} else if($_out_doc_type == 5) {
	$sql_cus  .= "drit_qty, drit_remark FROM ".ZKP_SQL."_tb_dr_item JOIN ".ZKP_SQL."_tb_item USING(it_code) WHERE dr_code='$_out_doc_ref' ";
} else if($_out_doc_type == 6) {
	$sql_cus  .= "rqit_qty, rqit_remark FROM ".ZKP_SQL."_tb_request_item JOIN ".ZKP_SQL."_tb_item USING(it_code) WHERE req_code='$_out_doc_ref' ";
}
$sql_cus .= "ORDER BY it_code;";
$res_cus =& query($sql_cus);
$row_cus = numQueryRows($res_cus);

$sql_wh = "
SELECT
  a.it_code, b.boit_it_code_for, a.it_model_no, a.it_desc, b.boit_qty, b.boit_remark
FROM
  ".ZKP_SQL."_tb_booking_item AS b
  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE b.book_idx = $_book_idx
ORDER BY a.it_code";
$res_wh	=& query($sql_wh);
$row_wh = numQueryRows($res_wh);

//Variable
$_date			= date("j-M-Y", strtotime($info['inv_date']));
$_do_date		= ($info['do_date']=='') ? '' : date('d-M-Y', strtotime($info['do_date']));
$_po_date		= ($info['po_date']=='') ? '' : date('d-M-Y', strtotime($info['po_date']));
$qtyTotal		= array(0,0);
$pdf = new FPDI();

//DO WAREHOUSE ===============================================================================================
$counter = array(0,0);
$qty = array(0,0);
$row = array(23, 23, $row_wh, $row_cus); //0.wh limit, 1.cus limit, 2.total wh, 3.total cus
$big = ($row_wh>$row_cus) ? 0 : 1;
$page = array(1, ceil($row[$big+2] / $row[$big]));
while ($counter[$big] < $row[$big+2]) {
	include "generate_do_detail.php";
	$page[0]++;
}

//PRINT END==========================================================================================================
//detimine the document name & document path.
$storage = PDF_STORAGE . "warehouse/do/". date("Ym/", strtotime($_do_date));
$doc_name = trim('D'.substr($_out_doc_ref,1)) . "_rev_" . $_revision_time .".pdf";
(!is_dir($storage))? mkdir($storage, 0777, true) : 0;

//Save pdf document
$pdf->Output($storage.$doc_name, 'F');
?>