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

$_issued_date	= date("j-M-Y",strtotime($_issued_date));
$_revision_time += 1;
$totalQty		= 0;

//sql variable
$sql = "
SELECT
  it_code, it_model_no, it_desc, rqit_qty, rqit_remark, rqit_type
FROM
  ".ZKP_SQL."_tb_request_item AS b JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE req_code = '$_code'
ORDER BY it_code";
$result	=& query($sql);

$pdf = new FPDI();

//REQUEST ===============================================================================================
$pdf->setSourceFile(APP_DIR . "_include/demo/pdf/do_demo_request_stock.pdf");
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, 0,0,0,0);

//Header
$pdf->setFont('Arial', 'B', 15);
$pdf->setXY(13, 25);
$pdf->Cell(100,10, $company[0]);
$pdf->setFont('Arial', '', 9);
$pdf->setXY(13, 34);
$pdf->Cell(170, 3.5, $company[1]);
if(isset($company[2])) {
	$pdf->setXY(13, 38);
	$pdf->Cell(170, 3.5, $company[2][0]);
}

/*	1	*/	$pdf->setFont('Arial', '', 10);
			$pdf->setXY(170,48);
			$pdf->Cell(0, 4, 'WAREHOUSE');
/*	2	*/	$pdf->setXY(33,58);
			$pdf->Cell(137, 4, ucfirst($_issued_by));
			$pdf->Cell(0, 4, $_code);
			$pdf->setXY(170,62);
			$pdf->Cell(0, 6, $_issued_date);
/*	3	*/	$pdf->setXY(13, 228);
			$pdf->MultiCell(170, 3.5, $_remark);
/*	4	*/	$pdf->setFont('Arial', 'I', 8);
			$pdf->setY(247);
			$pdf->Cell(190, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)."   ", 0,0,'R');
			$pdf->setFont('Arial', '', 10);

$pdf->setY(80);
while($items =& fetchRow($result, 0)) {
	$pdf->setX(14);
	$pdf->Cell(15, 3.5, $items[0],0);					//Code
	$pdf->Cell(10);
	$pdf->Cell(25, 3.5, substr($items[1],0,20),0); 		//Model
	$pdf->Cell(67, 3.5, substr($items[2],0,50),0); 		//Desc
	$pdf->Cell(20, 3.5, number_format((double)$items[3],2),0,0,'R');	//Qty
	$pdf->Cell(5);
	$pdf->Cell(0, 3.5, $items[4],0,1);					//Remark

	$totalQty += $items[3];
}

$pdf->setXY(131,219);
$pdf->Cell(20, 4, number_format($totalQty, 2),0,0,'R');

//PRINT END==========================================================================================================
//detimine the document name & document path.
$storage = PDF_STORAGE . "marketing/request/". date("Ym/", strtotime($_issued_date));
$doc_name = trim($_code) . "_rev_" . $_revision_time .".pdf";
(!is_dir($storage))? mkdir($storage, 0777, true) : 0;

//Save pdf document
$pdf->Output($storage.$doc_name, 'F');
?>