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
						a.it_code, a.it_model_no, a.it_desc, b.reit_qty, b.reit_remark
					FROM ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_return_item AS b ON (a.it_code = b.it_code)
					WHERE b.turn_code = '$_doc_ref' ORDER BY a.it_code";
$cus_item['2']	=	"SELECT
						a.it_code, a.it_model_no, a.it_desc, b.roit_qty, b.roit_remark
					FROM ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_return_order_item AS b ON (a.it_code = b.it_code)
					WHERE b.reor_code = '$_doc_ref' ORDER BY a.it_code";
$cus_item['3']	=	"SELECT
						a.it_code, a.it_model_no, a.it_desc, b.rdtit_qty, b.rdtit_remark
					FROM ".ZKP_SQL."_tb_return_dt_item AS b JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
					WHERE rdt_code = '$_doc_ref' ORDER BY it_code";


//Sql
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

$sql_wh = "
SELECT
  a.it_code,
  a.it_model_no,
  a.it_desc,
  b.inst_qty,
  '' as remark
FROM ".ZKP_SQL."_tb_incoming_stock as b JOIN ".ZKP_SQL."_tb_item as a USING(it_code)
WHERE inc_idx = $_inc_idx
ORDER BY it_code";
$res_wh	=& query($sql_wh);

$sql_cus = $cus_item[$_doc_type];
$res_cus =& query($sql_cus);

//Variable
$_return_date	= date("j-M-Y", strtotime($_doc_date));
$_ref_date		= ($info['ref_date']=='') ? '' : date('d-M-Y', strtotime($info['ref_date']));
$qtyTotal		= array(0,0);
$pdf = new FPDI();

//DO ===============================================================================================
$pdf->setSourceFile("template_pdf/tpl_return.pdf");
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, 0,0,210,296);

$pdf->setFont('Arial', '', 10);
$pdf->setXY(175,27);
$pdf->Cell(25,6,"WAREHOUSE",0,0,'C');

$pdf->setXY(30,37.5);
$pdf->Cell(148, 4, $info['cus_to_name']);
$pdf->Cell(0, 4, $_doc_ref);
$pdf->setXY(30,41.5);
$pdf->Cell(148, 4, $info['cus_to_address']);
$pdf->Cell(0, 4, $_return_date);
$pdf->setXY(30,45);
if($info['cus_to_phone'] != '' && $info['cus_to_fax'] != '') $pdf->Cell(125, 4, "Telp : ".$info['cus_to_phone']."  Fax : ".$info['cus_to_fax']);
else if($info['cus_to_phone'] != '' && $info['cus_to_fax'] == '') $pdf->Cell(125, 4, "Telp : ".$info['cus_to_phone']);
else if($info['cus_to_phone'] == '' && $info['cus_to_fax'] != '') $pdf->Cell(125, 4, "Fax : ".$info['cus_to_fax']);

$pdf->setXY(30,51);
$pdf->Cell(148, 4, $info['ship_to_name']);
$pdf->Cell(0, 4, $info['ref_no']);
$pdf->setXY(30,55);
$pdf->Cell(148, 4, $info['ship_to_address']);
$pdf->Cell(0, 4, $_ref_date);
$pdf->setXY(30,59);
if($info['ship_to_phone'] != '' && $info['ship_to_fax'] != '') $pdf->Cell(125, 4, "Telp : ".$info['ship_to_phone']."  Fax : ".$info['ship_to_fax']);
else if($info['ship_to_phone'] != '' && $info['ship_to_fax'] == '') $pdf->Cell(125, 4, "Telp : ".$info['ship_to_phone']);
else if($info['ship_to_phone'] == '' && $info['ship_to_fax'] != '') $pdf->Cell(125, 4, "Fax : ".$info['ship_to_fax']);

//Warehouse List
$pdf->setY(82);
while($item_wh =& fetchRow($res_wh, 0)) {
	$pdf->setX(11);
	$pdf->Cell(15, 3.5, $item_wh[0],0);							// code
	$pdf->Cell(40, 3.5, substr($item_wh[1],0,20),0);			// item no
	$pdf->Cell(70, 3.5, substr($item_wh[2],0,40),0);			// description
	$pdf->Cell(10, 3.5, number_format($item_wh[3]),0,0,'R');	// qty
	$pdf->Cell(5);
	$pdf->Cell(30, 3.5, $item_wh[4],0,1);						//remark

	$qtyTotal[0] += $item_wh[3];
}
$pdf->setXY(140,161.5);
$pdf->Cell(0, 4, $qtyTotal[0]);

//Customer List
$pdf->setY(179);
while($item_cus =& fetchRow($res_cus, 0)) {
	$pdf->setX(11);
	$pdf->Cell(15, 3.5, $item_cus[0],0);						// code
	$pdf->Cell(40, 3.5, substr($item_cus[1],0,20),0);			// item no
	$pdf->Cell(70, 3.5, substr($item_cus[2],0,40),0);			// description
	$pdf->Cell(10, 3.5, number_format($item_cus[3]),0,0,'R');	// qty
	$pdf->Cell(5);
	$pdf->Cell(30, 3.5, $item_cus[4],0,1);						//remark

	$qtyTotal[1] += $item_cus[3];
}
$pdf->setXY(140,247.5);
$pdf->Cell(0, 4, $qtyTotal[1]);

//Remark
$pdf->setXY(30, 253);
$pdf->MultiCell(170, 3.5, $_remark);
$pdf->setFont('Arial', 'I', 8);
$pdf->setY(261);
$pdf->Cell(192, 4, "Created by ".$_cfm_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)."   ", 0,0,'R');

//PRINT END==========================================================================================================
//detimine the document name & document path.
$storage = PDF_STORAGE . "warehouse/return/". date("Ym/", strtotime($_return_date));
$doc_name = trim($_doc_ref) . "_rev_" . $_revision_time .".pdf";
(!is_dir($storage))? mkdir($storage, 0777, true) : 0;

//Save pdf document
$pdf->Output($storage.$doc_name, 'F');
?>