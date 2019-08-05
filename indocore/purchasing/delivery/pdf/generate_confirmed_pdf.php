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

//Variable
$cus_item	= array();
$cus_item['1']	=	"SELECT
						a.it_code, a.it_model_no, a.it_desc, b.biit_qty, b.biit_remark
					FROM ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_billing_item AS b ON (a.it_code = b.it_code)
					WHERE b.bill_code = '$_out_doc_ref' ORDER BY a.it_code";
$cus_item['2']	=	"SELECT
						a.it_code, a.it_model_no, a.it_desc, b.odit_qty, b.odit_remark
					FROM ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_order_item AS b ON (a.it_code = b.it_code)
					WHERE b.ord_code = '$_out_doc_ref' ORDER BY a.it_code";
$cus_item['3']	=	"SELECT
						a.it_code, a.it_model_no, a.it_desc, b.dtit_qty, b.dtit_remark
					FROM ".ZKP_SQL."_tb_dt_item AS b JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
					WHERE dt_code = '$_out_doc_ref' ORDER BY it_code";
$cus_item['4']	=	"SELECT
						a.it_code, a.it_model_no, a.it_desc, b.dfit_qty, b.dfit_remark
					FROM ".ZKP_SQL."_tb_df_item AS b JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
					WHERE df_code = '$_out_doc_ref' ORDER BY it_code";
$cus_item['5']	=	"SELECT
						a.it_code, a.it_model_no, a.it_desc, b.drit_qty, b.drit_remark
					FROM ".ZKP_SQL."_tb_dr_item AS b JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
					WHERE dr_code = '$_out_doc_ref' ORDER BY it_code";
$cus_item['6']	=	"SELECT
						it_code, it_model_no, it_desc, rqit_qty, rqit_remark
					FROM ".ZKP_SQL."_tb_request_item AS b JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
					WHERE req_code = '$_out_doc_ref' ORDER BY it_code";


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

$sql_wh = "
SELECT
  a.it_code,			--0
  b.boit_it_code_for,	--1
  a.it_model_no,		--2
  a.it_desc,			--3
  b.boit_qty,			--4
  b.boit_remark 		--5
FROM
  ".ZKP_SQL."_tb_booking_item AS b
  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE b.book_idx = $_book_idx
ORDER BY a.it_code";
$res_wh	=& query($sql_wh);

$sql_cus = $cus_item[$_out_doc_type];
$res_cus =& query($sql_cus);

//Variable
$_date			= date("j-M-Y", strtotime($info['inv_date']));
$_do_date		= ($info['do_date']=='') ? '' : date('d-M-Y', strtotime($info['do_date']));
$_po_date		= ($info['po_date']=='') ? '' : date('d-M-Y', strtotime($info['po_date']));
$qtyTotal		= array(0,0);
$pdf = new FPDI();

//DO ===============================================================================================
$pdf->setSourceFile("template_pdf/tpl_do.pdf");
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, 0,0,210,296);
$pdf->setFont('Arial', '', 10);

$pdf->setXY(175,27);
$pdf->Cell(25,6,"WAREHOUSE",0,0,'C');
$pdf->setXY(30,38);

$pdf->Cell(150, 4, $info['cus_to_name']);
$pdf->Cell(0, 4, $info['do_no']);
$pdf->setXY(30,42);
$pdf->Cell(150, 4, $info['cus_to_address']);
$pdf->Cell(0, 4, $_do_date);
$pdf->setXY(30,46);
if($info['cus_to_phone'] != '' && $info['cus_to_fax'] != '') $pdf->Cell(150, 4, "Telp : ".$info['cus_to_phone']."  Fax : ".$info['cus_to_fax']);
else if($info['cus_to_phone'] != '' && $info['cus_to_fax'] == '') $pdf->Cell(150, 4, "Telp : ".$info['cus_to_phone']);
else if($info['cus_to_phone'] == '' && $info['cus_to_fax'] != '') $pdf->Cell(150, 4, "Fax : ".$info['cus_to_fax']);

$pdf->setXY(30,51);
$pdf->Cell(150, 4, $info['ship_to_name']);
$pdf->Cell(0, 4, $_out_doc_ref);
$pdf->setXY(30,55);
$pdf->Cell(150, 4, $info['ship_to_address']);
$pdf->Cell(0, 4, $_date);
$pdf->setXY(30,59);
if($info['ship_to_phone'] != '' && $info['ship_to_fax'] != '') $pdf->Cell(150, 4, "Telp : ".$info['ship_to_phone']."  Fax : ".$info['ship_to_fax']);
else if($info['ship_to_phone'] != '' && $info['ship_to_fax'] == '') $pdf->Cell(150, 4, "Telp : ".$info['ship_to_phone']);
else if($info['ship_to_phone'] == '' && $info['ship_to_fax'] != '') $pdf->Cell(150, 4, "Fax : ".$info['ship_to_fax']);
$pdf->setXY(30,65);
$pdf->Cell(150, 4, $info['po_no']);
$pdf->Cell(0, 4, $_po_date);

//Warehouse List
$pdf->setY(82);
while($item_wh =& fetchRow($res_wh, 0)) {
	$pdf->setX(11);
	$pdf->Cell(15, 3.5, $item_wh[0],0);							//code
	$pdf->Cell(15, 3.5, $item_wh[1],0);							//for
	$pdf->Cell(23, 3.5, substr($item_wh[2],0,10),0); 			// item no
	$pdf->Cell(73, 3.5, substr($item_wh[3],0,40),0);			// description
	$pdf->Cell(15, 3.5, number_format($item_wh[4],2),0,0,'R'); 	// qty
	$pdf->Cell(5);
	$pdf->Cell(0, 3.5, $item_wh[5],0,1);

	$qtyTotal[0] += $item_wh[4];
}
$pdf->setXY(135,161.5);
$pdf->Cell(0, 4, number_format($qtyTotal[0],2));

//Customer List
pg_result_seek($res_cus, 0);
$pdf->setY(179);
while($item_cus =& fetchRow($res_cus, 0)) {
	$pdf->setX(11);
	$pdf->Cell(15, 3.5, $item_cus[0],0);						// code
	$pdf->Cell(40, 3.5, substr($item_cus[1],0,20),0);			// item no
	$pdf->Cell(70, 3.5, substr($item_cus[2],0,40),0);			// description
	$pdf->Cell(10, 3.5, number_format($item_cus[3]),0,0,'R'); 	// qty
	$pdf->Cell(5);
	$pdf->Cell(30, 3.5, $item_cus[4],0,1);						//remark

	$qtyTotal[1] += $item_cus[3];
}
$pdf->setXY(140,247.5);
$pdf->Cell(0, 4, number_format($qtyTotal[1]));

$pdf->setXY(30, 253);
$pdf->MultiCell(170, 3.5, $_remark);
$pdf->setFont('Arial', 'I', 8);
$pdf->setY(260);
$pdf->Cell(193, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)."   ", 0,0,'R');

//PRINT END==========================================================================================================
//detimine the document name & document path.
$storage = PDF_STORAGE . "warehouse/do/". date("Ym/", strtotime($_do_date));
$doc_name = trim($info['do_no']) . "_rev_" . $_revision_time .".pdf";
(!is_dir($storage))? mkdir($storage, 0777, true) : 0;

//Save pdf document
$pdf->Output($storage.$doc_name, 'F');
?>