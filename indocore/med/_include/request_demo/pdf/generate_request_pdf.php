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

$_request_date	= date("j-M-Y",strtotime($_request_date));
$_revision_time += 1;
$totalQty		= 0;

//sql variable
$sql_item = "
SELECT
  it_code,
  it_model_no,
  it_desc,
  usit_qty,
  usit_remark
FROM ".ZKP_SQL."_tb_using_demo_item JOIN ".ZKP_SQL."_tb_item USING(it_code) WHERE use_code = '$_code'";
$res_item = query($sql_item);

$cus_sql	= "
SELECT
	(select cus_contact_phone from ".ZKP_SQL."_tb_customer where cus_code='$_cus_to') AS cus_phone,		--0
	(select cus_fax from ".ZKP_SQL."_tb_customer where cus_code='$_cus_to') AS cus_fax					--1
";
$cus_res =& query($cus_sql);
$cus	 =& fetchRow($cus_res);

$pdf = new FPDI();

//REQUEST ===============================================================================================
$pdf->setSourceFile(APP_DIR . "_include/request_demo/template_pdf/tpl_request_demo.pdf");
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, 0,0,0,0);

//Header
$pdf->setFont('Arial', 'B', 15);
$pdf->setXY(13, 20);
$pdf->Cell(100,10, $company[0]);
$pdf->setFont('Arial', '', 9);
$pdf->setXY(13, 29);
$pdf->Cell(170, 3.5, $company[1]);
if(isset($company[2])) {
	$pdf->setXY(13, 33);
	$pdf->Cell(170, 3.5, implode('   ', $company[2]));
}

/*	1	*/	$pdf->setFont('Arial', '', 10);
			$pdf->setXY(173,44);
			$pdf->Cell(0, 4, 'MARKETING');
/*	2	*/	$pdf->setXY(33,54);
			$pdf->Cell(148, 4, $_cus_name);
			$pdf->Cell(0, 4, $_code);
/*	3	*/	$pdf->setXY(33,58);
			$pdf->Cell(148, 4, $_cus_address);
			$pdf->Cell(0, 4, $_request_date);
/*	4	*/	$pdf->setXY(33,61);
			if($cus[0] != '' && $cus[1] != '') $pdf->Cell(153, 4, "Telp : ".$cus[0]."  Fax : ".$cus[1]);
			else if($cus[0] != '' && $cus[1] == '') $pdf->Cell(153, 4, "Telp : ".$cus[0]);
			else if($cus[0] == '' && $cus[1] != '') $pdf->Cell(153, 4, "Fax : ".$cus[1]);
/*	5	*/	$pdf->setXY(33,66);
			$pdf->Cell(148, 4, ucfirst($_request_by));
/*	6	*/	$pdf->setXY(12, 224);
			$pdf->MultiCell(170, 3.5, $_remark);
/*	7	*/	$pdf->setFont('Arial', 'I', 8);
			$pdf->setY(241);
			$pdf->Cell(190, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)."   ", 0,0,'R');
/*	8	*/	$pdf->setFont('Arial', '', 10);
			$pdf->setXY(35, 267);
			$pdf->Cell(30, 4, $_sign_by,0,0,'C');

//Item list
$pdf->setY(84);
while($items =& fetchRow($res_item, 0)) {
	$pdf->setX(12);
	$pdf->Cell(15, 3.5, $items[0],0);									//Code
	$pdf->Cell(10);
	$pdf->Cell(25, 3.5, substr($items[1],0,20),0); 						//Model
	$pdf->Cell(75, 3.5, substr($items[2],0,42),0); 						//Desc
	$pdf->Cell(20, 3.5, number_format((double)$items[3],2),0,0,'R');	//Qty
	$pdf->Cell(5);
	$pdf->Cell(0, 3.5, $items[4],0,1);									//Remark
	$totalQty += $items[3];
}
$pdf->setXY(137,214);
$pdf->Cell(20, 4, number_format((double)$totalQty,2),0,0,'R');

//PRINT END==========================================================================================================
//detimine the document name & document path.
$storage = PDF_STORAGE . "marketing/using/". date("Ym/", strtotime($_request_date));
$doc_name = trim($_code) . "_rev_" . $_revision_time .".pdf";
(!is_dir($storage))? mkdir($storage, 0777, true) : 0;

//Save pdf document
$pdf->Output($storage.$doc_name, 'F');
?>