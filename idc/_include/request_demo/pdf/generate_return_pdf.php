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

//sql
$req_sql = "
SELECT
	use_code, 				--0
	use_request_date, 		--1
	use_cus_name, 			--2
	use_cus_address,		--3
	cus_contact_phone,		--4
	cus_fax					--5
FROM ".ZKP_SQL."_tb_customer AS c JOIN ".ZKP_SQL."_tb_using_demo AS u ON c.cus_code = u.use_cus_to WHERE use_code = '$_use_code'";
$req_res =& query($req_sql);
$req	 =& fetchRow($req_res);

$sql_item = "
SELECT
  it_code,
  it_model_no,
  it_desc,
  rdit_qty
FROM ".ZKP_SQL."_tb_return_demo_item JOIN ".ZKP_SQL."_tb_item USING(it_code) WHERE red_code = '$_code' AND rdit_qty>0";
$res_item = query($sql_item);

//variable
$_return_date	= date("j-M-Y",strtotime($_return_date));
$_use_date		= date("j-M-Y",strtotime($req[1]));
$totalQty		= 0;
$_revision_time += 1;

$pdf = new FPDI();

//REQUEST ===============================================================================================
$pdf->setSourceFile(APP_DIR . "_include/request_demo/template_pdf/tpl_return_demo.pdf");
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
			$pdf->Cell(148, 4, $req[2]);
			$pdf->Cell(0, 4, $_code);
/*	3	*/	$pdf->setXY(33,57);
			$pdf->Cell(148, 6, $req[3]);
			$pdf->Cell(0, 6, $_return_date);
/*	4	*/	$pdf->setXY(33,61);
			if($req[4] != '' && $req[5] != '') $pdf->Cell(151, 4, "Telp : ".$req[4]."  Fax : ".$req[5]);
			else if($req[4] != '' && $req[5] == '') $pdf->Cell(151, 4, "Telp : ".$req[4]);
			else if($req[4] == '' && $req[5] != '') $pdf->Cell(151, 4, "Fax : ".$req[5]);
			else $pdf->Cell(151, 5, '');
/*	5	*/	$pdf->setXY(33,66);
			$pdf->Cell(148, 4, ucfirst($_return_by));
			$pdf->Cell(0, 4, $_use_code);
			$pdf->setXY(181,70);
			$pdf->Cell(0, 4, $_use_date);
/*	6	*/	$pdf->setXY(12, 224);
			$pdf->MultiCell(170, 3.5, $_remark);
			$pdf->setFont('Arial', 'I', 8);
			$pdf->setY(241);
			$pdf->Cell(190, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)."   ", 0,0,'R');
/*	7	*/	$pdf->setFont('Arial', '', 10);
			$pdf->setXY(35, 267);
			$pdf->Cell(30, 4, $_sign_by,0,0,'C');

//Item list
$pdf->setY(84);
while($items =& fetchRow($res_item, 0)) {
	$pdf->setX(12);
	$pdf->Cell(15, 3.5, $items[0],0);									//Code
	$pdf->Cell(10);
	$pdf->Cell(25, 3.5, substr($items[1],0,20),0); 						//Model
	$pdf->Cell(72, 3.5, substr($items[2],0,40),0); 						//Desc
	$pdf->Cell(20, 3.5, number_format((double)$items[3],2),0,1,'R');	//Qty
	$totalQty += $items[3];
}
$pdf->setXY(133,214);
$pdf->Cell(20, 4, number_format((double)$totalQty,2),0,0,'R');

//PRINT END==========================================================================================================
//detimine the document name & document path.
$storage = PDF_STORAGE . "marketing/using/". date("Ym/", strtotime($_return_date));
$doc_name = trim($_code) . "_rev_" . $_revision_time .".pdf";
(!is_dir($storage))? mkdir($storage, 0777, true) : 0;

//Save pdf document
$pdf->Output($storage.$doc_name, 'F');
?>