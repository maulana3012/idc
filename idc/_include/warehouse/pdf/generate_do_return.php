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

//SQL
$cus_sql	= "
SELECT
	CASE
		WHEN $_doc_type = '1' then (select turn_bill_code from ".ZKP_SQL."_tb_return where turn_code='$_doc_ref')
		WHEN $_doc_type = '2' then (select ord_code from ".ZKP_SQL."_tb_return_order where reor_code='$_doc_ref')
		WHEN $_doc_type = '3' then (select dt_code from ".ZKP_SQL."_tb_return_dt where rdt_code='$_doc_ref')
	END AS ref_no,
	CASE
		WHEN $_doc_type = '1' then (select turn_bill_inv_date from ".ZKP_SQL."_tb_return where turn_code='$_doc_ref')
		WHEN $_doc_type = '2' then (select ord_po_date from ".ZKP_SQL."_tb_order join ".ZKP_SQL."_tb_return_order using(ord_code) where reor_code='$_doc_ref')
		WHEN $_doc_type = '3' then (select rdt_dt_date from ".ZKP_SQL."_tb_return_dt where rdt_code='$_doc_ref')
	END AS ref_date,
	CASE
		WHEN $_doc_type = '1' then (select turn_cus_to from ".ZKP_SQL."_tb_return where turn_code='$_doc_ref')
		WHEN $_doc_type = '2' then (select reor_cus_to from ".ZKP_SQL."_tb_return_order where reor_code='$_doc_ref')
		WHEN $_doc_type = '3' then (select rdt_cus_to from ".ZKP_SQL."_tb_return_dt where rdt_code='$_doc_ref')
	END AS cus_to,
	CASE
		WHEN $_doc_type = '1' then (select cus_full_name from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_return on cus_code=turn_cus_to where turn_code='$_doc_ref')
		WHEN $_doc_type = '2' then (select cus_full_name from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_return_order on cus_code=reor_cus_to where reor_code='$_doc_ref')
		WHEN $_doc_type = '3' then (select cus_full_name from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_return_dt on cus_code=rdt_cus_to where rdt_code='$_doc_ref')
	END AS cus_to_name,
	CASE
		WHEN $_doc_type = '1' then (select cus_phone from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_return on cus_code=turn_cus_to where turn_code='$_doc_ref')
		WHEN $_doc_type = '2' then (select cus_phone from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_return_order on cus_code=reor_cus_to where reor_code='$_doc_ref')
		WHEN $_doc_type = '3' then (select cus_phone from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_return_dt on cus_code=rdt_cus_to where rdt_code='$_doc_ref')
	END AS cus_to_phone,
	CASE
		WHEN $_doc_type = '1' then (select cus_fax from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_return on cus_code=turn_cus_to where turn_code='$_doc_ref')
		WHEN $_doc_type = '2' then (select cus_fax from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_return_order on cus_code=reor_cus_to where reor_code='$_doc_ref')
		WHEN $_doc_type = '3' then (select cus_fax from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_return_dt on cus_code=rdt_cus_to where rdt_code='$_doc_ref')
	END AS cus_to_fax,
	CASE
		WHEN $_doc_type = '1' then (select cus_address from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_return on cus_code=turn_cus_to where turn_code='$_doc_ref')
		WHEN $_doc_type = '2' then (select cus_address from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_return_order on cus_code=reor_cus_to where reor_code='$_doc_ref')
		WHEN $_doc_type = '3' then (select cus_address from ".ZKP_SQL."_tb_customer join ".ZKP_SQL."_tb_return_dt on cus_code=rdt_cus_to where rdt_code='$_doc_ref')
	END AS cus_to_address,

	'$_cus_code' AS ship_to,
	(select cus_full_name from ".ZKP_SQL."_tb_customer where cus_code='$_cus_code') AS ship_to_name,
	(select cus_phone from ".ZKP_SQL."_tb_customer where cus_code='$_cus_code') AS ship_to_phone,
	(select cus_fax from ".ZKP_SQL."_tb_customer where cus_code='$_cus_code') AS ship_to_fax,
	(select cus_address from ".ZKP_SQL."_tb_customer where cus_code='$_cus_code') AS ship_to_address
";
$res_cus =& query($cus_sql);
$info	 =& fetchRowAssoc($res_cus);

$sql_cus = "SELECT a.it_code, a.it_model_no, a.it_desc, ";
if($_doc_type == 1) {
	$sql_cus .= "reit_qty, reit_remark FROM ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_return_item USING (it_code) WHERE turn_code = '$_doc_ref'";
} else if($_doc_type == 2) {
	$sql_cus .= "roit_qty, roit_remark FROM ".ZKP_SQL."_tb_item  AS aJOIN ".ZKP_SQL."_tb_return_order_item USING (it_code) WHERE reor_code = '$_doc_ref'";
} else if($_doc_type == 3) {
	$sql_cus .= "rdtit_qty, rdtit_remark FROM ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_return_dt_item USING (it_code) WHERE rdt_code = '$_doc_ref'";
}
$sql_cus .= "ORDER BY it_code";
$res_cus =& query($sql_cus);
$row_cus = numQueryRows($res_cus);

$sql_wh = "
SELECT
  a.it_code, a.it_model_no, a.it_desc, b.inst_qty, '' as remark
FROM ".ZKP_SQL."_tb_incoming_stock as b JOIN ".ZKP_SQL."_tb_item as a USING(it_code)
WHERE inc_idx = $_inc_idx
ORDER BY it_code";
$res_wh	=& query($sql_wh);
$row_wh = numQueryRows($res_wh);

//Variable
$_return_date	= date("j-M-Y", strtotime($_doc_date));
$_ref_date		= ($info['ref_date']=='') ? '' : date('d-M-Y', strtotime($info['ref_date']));
$qtyTotal		= array(0,0);
$pdf = new FPDI();

//DO WAREHOUSE ===============================================================================================
$counter = array(0,0);
$qty = array(0,0);
$row = array(22, 22, $row_wh, $row_cus); //0.wh limit, 1.cus limit, 2.total wh, 3.total cus
$big = ($row_wh>$row_cus) ? 0 : 1;
$page = array(1, ceil($row[$big+2] / $row[$big]));
while ($counter[$big] < $row[$big+2]) {
	include "generate_do_return_detail.php";
	$page[0]++;
}

//PRINT END==========================================================================================================
//detimine the document name & document path.
$storage = PDF_STORAGE . "warehouse/return/". date("Ym/", strtotime($_return_date));
$doc_name = trim($_doc_ref) . "_rev_" . $_revision_time .".pdf";
(!is_dir($storage))? mkdir($storage, 0777, true) : 0;

//Save pdf document
$pdf->Output($storage.$doc_name, 'F');
?>