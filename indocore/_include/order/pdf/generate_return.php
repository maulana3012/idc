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

//VARIABLE
$_return_date 	= date("j-M-Y",strtotime($_po_date));
$_po_date 		= empty($_po_date) ? "" : date("j-M-Y", strtotime($_po_date));
$_revision_time += 1;

//SQL
//Customer List
$sql_cus = "
SELECT
  a.it_code, a.it_model_no, a.it_desc, b.roit_unit_price, b.roit_qty, b.roit_unit_price * b.roit_qty AS amount, b.roit_remark, to_char(b.roit_date, 'DD-Mon-YYYY') AS delivery
FROM
  ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_return_order_item AS b ON (a.it_code = b.it_code)
WHERE b.reor_code = '$_code' ORDER BY a.it_code";
$cus_res =& query($sql_cus);
$row_cus = numQueryRows($cus_res);
//Warehouse List
if($_paper == 0) {
	$sql_wh = "
	SELECT
	  a.it_code, b.istd_it_code_for, a.it_model_no,	a.it_desc, b.istd_qty, b.istd_remark
	FROM
	  ".ZKP_SQL."_tb_outstanding_item as b JOIN ".ZKP_SQL."_tb_item as a USING(it_code)
	WHERE std_idx = $_std_idx ORDER BY a.it_code";
	$wh_res =& query($sql_wh);
	$row_wh = numQueryRows($wh_res);
}
//Cus info
$cus_sql	= "
SELECT
	(select cus_contact_phone from ".ZKP_SQL."_tb_customer where cus_code='$_cus_to') AS cus_phone,		--0
	(select cus_fax from ".ZKP_SQL."_tb_customer where cus_code='$_cus_to') AS cus_fax,					--1
	(select cus_contact_phone from ".ZKP_SQL."_tb_customer where cus_code='$_ship_to') AS ship_phone,	--2
	(select cus_fax from ".ZKP_SQL."_tb_customer where cus_code='$_ship_to') AS ship_fax				--3
";
$cus =& query($cus_sql);
$cus	 =& fetchRow($cus);

//TEMPLATE PDF ========================================================================================================
$tpl_pdf = array();
$tpl_pdf[2] = 'tpl_return_order_do.pdf';
$pdf = new FPDI();

//INVOICE =============================================================================================================
if(ZKP_SQL == 'IDC') {
	if($_dept == 'A') {
		$tpl_pdf[0] = 'tpl_return_order_customer_A.pdf';
		$tpl_pdf[1] = 'tpl_return_order_admin_A.pdf';
	} else {
		$tpl_pdf[0] = 'tpl_return_order_customer_B.pdf';
		$tpl_pdf[1] = 'tpl_return_order_admin_B.pdf';
	}
	$counter = 0;
	$total 	 = array(0,0);				//0.total qty, 1.total amount
	$row	 = array(30, 0, $row_cus);	//0.limit, 1.total item
	$page 	 = array(1, ceil($row[2] / $row[0]));
	while ($counter < $row[2]) {
		include "generate_return_customer_pdf.php";
		$page[0]++;
	}
	$counter = 0;
	$total 	 = array(0,0);				//0.total qty, 1.total amount
	$row	 = array(30, 0, $row_cus);	//0.limit, 1.total item
	$page 	 = array(1, ceil($row[2] / $row[0]));
	while ($counter < $row[2]) {
		include "generate_return_admin_pdf.php";
		$page[0]++;
	}
} else if(ZKP_SQL == 'MED') {
	$tpl_pdf[0] = 'tpl_return_order_med.pdf';
	$counter = 0;
	$total 	 = array(0,0);				//0.total qty, 1.total amount
	$row	 = array(31, 0, $row_cus);	//0.limit, 1.total item
	$page 	 = array(1, ceil($row[2] / $row[0]));
	while ($counter < $row[2]) {
		include "generate_return_med.php";
		$page[0]++;
	}
}

if($_paper == 0) {
	//DO WAREHOUSE ====================================================================================================
	$counter = array(0,0);
	$total	 = array(0,0);				//0.total qty, 1.total amount
	$row 	 = array(20, 0, $row_wh);	//0.limit, 1.total item
	$page 	 = array(1, ceil($row[2] / $row[0]));
	while ($counter[0] < $row[2]) {
		include "generate_return_do_pdf.php";
		$page[0]++;
	}
}

//PRINT END==========================================================================================================
//detimine the document name & document path.
$storage = PDF_STORAGE . "order/$currentDept/". date("Ym/", strtotime($_return_date));
$doc_name = trim($_code) . "_rev_" . ($_revision_time).".pdf";
error_log("dir? ". is_dir($storage));

(!is_dir($storage))? mkdir($storage, 0777, true) : 0;

//Save pdf document
$pdf->Output($storage.$doc_name, 'F');
?>