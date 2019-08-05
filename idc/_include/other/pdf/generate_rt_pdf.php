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

$_rdt_date	= date("j-M-Y",strtotime($_date));
$_dt_date	= empty($_dt_date) ? "" : date("j-M-Y", strtotime($_dt_date));
$_revision_time += 1;
$totalWHQty	 = 0;
$totalCusQty = 0;

//information
$cus_sql	= "
SELECT
	(select cus_contact_phone from ".ZKP_SQL."_tb_customer where cus_code='$_cus_to') AS cus_phone,		--0
	(select cus_fax from ".ZKP_SQL."_tb_customer where cus_code='$_cus_to') AS cus_fax,					--1
	(select cus_address from ".ZKP_SQL."_tb_customer where cus_code='$_ship_to') AS ship_address,		--2
	(select cus_contact_phone from ".ZKP_SQL."_tb_customer where cus_code='$_ship_to') AS ship_phone,	--3
	(select cus_fax from ".ZKP_SQL."_tb_customer where cus_code='$_ship_to') AS ship_fax				--4
";
$cus_res =& query($cus_sql);
$cus	 =& fetchRow($cus_res);

//Warehouse item list
$sql_wh = "
SELECT
  a.it_code,		--0
  a.it_model_no,	--1
  a.it_desc,		--2
  b.istd_qty,		--3
  b.istd_remark		--4
FROM ".ZKP_SQL."_tb_outstanding_item as b JOIN ".ZKP_SQL."_tb_item as a USING(it_code)
WHERE std_idx = $_std_idx
ORDER BY it_code,istd_idx";
$res_wh	=& query($sql_wh);

//Customer item list
$sql_cus = "
SELECT
  a.it_code,			--0
  a.it_model_no,		--1
  a.it_desc,			--2
  b.rdtit_qty,			--3
  b.rdtit_remark 		--4
FROM
  ".ZKP_SQL."_tb_return_dt_item AS b
  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE rdt_code = '$_code'
ORDER BY it_code";
$res_cus	=& query($sql_cus);


$pdf = new FPDI();

//INVOICE ===============================================================================================
$pdf->setSourceFile(APP_DIR . "_include/other/template_pdf/tpl_dt_return.pdf");
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, -2,5.5,0,0);

//Header
$pdf->setFont('Arial', 'B', 15);
$pdf->setXY(8, 15);
$pdf->Cell(100,10, $company[0]);
$pdf->setFont('Arial', '', 9);
$pdf->setXY(8, 24);
$pdf->Cell(170, 3.5, $company[1]);
if(isset($company[2])) {
	$pdf->setXY(8, 28);
	$pdf->Cell(170, 3.5, implode('   ', $company[2]));
}

/*	1	*/	$pdf->setFont('Arial', '', 10);
			$pdf->setXY(175, 37);
			$pdf->Cell(25,6,"WAREHOUSE",0,0,'C');
/*	2	*/	$pdf->setXY(28,47);
			$pdf->Cell(153, 4, $_cus_name);
			$pdf->Cell(0, 4, $_code);
/*	3	*/	$pdf->setXY(28,51);
			$pdf->Cell(153, 4, $_cus_address);
			$pdf->Cell(0, 4, $_rdt_date);
/*	4	*/	$pdf->setXY(28,55);
			if($cus[0] != '' && $cus[1] != '') $pdf->Cell(153, 4, "Telp : ".$cus[0]."  Fax : ".$cus[1]);
			else if($cus[0] != '' && $cus[1] == '') $pdf->Cell(153, 4, "Telp : ".$cus[0]);
			else if($cus[0] == '' && $cus[1] != '') $pdf->Cell(153, 4, "Fax : ".$cus[1]);
/*	5	*/	$pdf->setXY(28,59);
			$pdf->Cell(153, 4, $_ship_name);
			$pdf->Cell(0, 4, $_dt_code);
/*	6	*/	$pdf->setXY(28,63);
			$pdf->Cell(153, 4, $cus[2]);
			$pdf->Cell(0, 4, $_dt_date);
/*	7	*/	$pdf->setXY(28,67);
			if($cus[3] != '' && $cus[4] != '') $pdf->Cell(153, 4, "Telp : ".$cus[3]."  Fax : ".$cus[4]);
			else if($cus[3] != '' && $cus[4] == '') $pdf->Cell(153, 4, "Telp : ".$cus[3]);
			else if($cus[3] == '' && $cus[4] != '') $pdf->Cell(153, 4, "Fax : ".$cus[4]);
/*	8	*/	$pdf->setXY(8, 250);
			$pdf->MultiCell(170, 3.5, $_remark);
/*	9	*/	$pdf->setFont('Arial', 'I', 8);
			$pdf->setY(259);
			$pdf->Cell(195, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)."   ", 0,0,'R');
			$pdf->setFont('Arial', '', 10);

$pdf->setY(80);
while($item_wh =& fetchRow($res_wh, 0)) {
	$pdf->setX(8);
	$pdf->Cell(15, 3.5, $item_wh[0],0);									// code
	$pdf->Cell(40, 3.5, substr($item_wh[1],0,20),0);					// item no
	$pdf->Cell(78, 3.5, substr($item_wh[2],0,40),0);					// description
	$pdf->Cell(10, 3.5, number_format((double)$item_wh[3],2),0,0,'R'); 	// qty
	$pdf->Cell(5);
	$pdf->Cell(30, 3.5, $item_wh[4],0,1);								//remark

	$totalWHQty += $item_wh[3];
}

$pdf->setY(168);
while($item_cus =& fetchRow($res_cus, 0)) {
	$pdf->setX(8);
	$pdf->Cell(15, 3.5, $item_cus[0],0);								// code
	$pdf->Cell(40, 3.5, substr($item_cus[1],0,20),0);					// item no
	$pdf->Cell(73, 3.5, substr($item_cus[2],0,40),0);					// description
	$pdf->Cell(10, 3.5, number_format((double)$item_cus[3]),0,0,'R'); 	// qty
	$pdf->Cell(5);
	$pdf->Cell(30, 3.5, $item_cus[4],0,1);								//remark
	$totalCusQty += $item_cus[3];
}

$pdf->setXY(143,157);
$pdf->Cell(0, 4, number_format((double)$totalWHQty,2));
$pdf->setXY(143,245);
$pdf->Cell(0, 4, $totalCusQty);


//PRINT END==========================================================================================================
//detimine the document name & document path.
$storage = PDF_STORAGE . "other_document/$currentDept/". date("Ym/", strtotime($_date));
$doc_name = trim($_code) . "_rev_" . $_revision_time .".pdf";
(!is_dir($storage))? mkdir($storage, 0777, true) : 0;

//Save pdf document
$pdf->Output($storage.$doc_name, 'F');
?>