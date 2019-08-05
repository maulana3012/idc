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

$_service_date	= date("j-M-Y",strtotime($_service_date));
$_guarantee_period	= ($_guarantee_period == '') ? 'Expired' : date("j-M-Y",strtotime($_guarantee_period));
$_revision_time += 1;

//sql information
$model_sql		= "SELECT * FROM ".ZKP_SQL."_tb_service_item WHERE sv_code = '$_code'";
$repair_sql		= "SELECT * FROM ".ZKP_SQL."_tb_service_repair WHERE sv_code = '$_code'";
$replace_sql	= "SELECT * FROM ".ZKP_SQL."_tb_service_replace WHERE sv_code = '$_code'";
$model_res		= query($model_sql);
$repair_res		= query($repair_sql);
$replace_res	= query($replace_sql);

if(ZKP_SQL == 'IDC') {
	$tpl_pdf = "tpl_idc_service.pdf";
} else if(ZKP_SQL == 'MED')	{
	$tpl_pdf = "tpl_med_service.pdf";
}

if($_cus_to == '') {
	$custo_sql = "SELECT sv_cus_to,sv_cus_to_name,sv_cus_to_address FROM ".ZKP_SQL."_tb_service where sv_code = '$_code'";
	$custo_res =& query($custo_sql);
	$cus	   =& fetchRow($custo_res);
	$_cus_to		= $cus[0];
	$_cus_name		= $cus[1];
	$_cus_address	= $cus[2];
}

$cus_sql	= "
SELECT
	(select cus_contact_phone from ".ZKP_SQL."_tb_customer where cus_code='$_cus_to') AS cus_phone,		--0
	(select cus_fax from ".ZKP_SQL."_tb_customer where cus_code='$_cus_to') AS cus_fax					--1
";
$cus_res =& query($cus_sql);
$cus	 =& fetchRow($cus_res);

$pdf = new FPDI();

for($x= 0; $x<3; $x++) {
//INVOICE ===============================================================================================
$pdf->setSourceFile("template_pdf/" . $tpl_pdf);
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, -2,5.5,0,0);

$pdf->setFont('Arial', '', 10);				//Print for
$pdf->setXY(171,39);
if($x==0) $pdf->Cell(30, 4, 'CUSTOMER',0,0,'C');
else if($x==1) $pdf->Cell(30, 4, 'CUSTOMER SERVICE',0,0,'C');
else if($x==2) $pdf->Cell(30, 4, 'ADMIN',0,0,'C');

$pdf->setFont('Arial', '', 10);
$pdf->setXY(28,49);
$pdf->Cell(143, 4, $_cus_name); 			// Customer to
$pdf->Cell(0, 4, $_code);					// Service code
$pdf->setXY(28,53);
$pdf->Cell(143, 4, $_cus_address); 			// Customer addresss
$pdf->Cell(0, 4, $_service_date); 			// Service date
$pdf->setXY(28,57);
if($cus[0] != '' && $cus[1] != '') $pdf->Cell(153, 4, "Telp : ".$cus[0]."  Fax : ".$cus[1]);
else if($cus[0] != '' && $cus[1] == '') $pdf->Cell(153, 4, "Telp : ".$cus[0]);
else if($cus[0] == '' && $cus[1] != '') $pdf->Cell(153, 4, "Fax : ".$cus[1]);

//Model & SN
$pdf->setXY(171, 65.5);
$pdf->Cell(0, 4, $_guarantee_period);				//Warranty period
$model_set	  = '';
pg_result_seek($model_res,0);
while($items =& fetchRow($model_res)) {
	$model_set = $model_set.trim($items[3]).' : '.trim($items[4]).', ';
}
$pdf->setXY(28, 65.5);
$pdf->MultiCell(110, 3.5, substr($model_set,0,-2));	//Model & SN

//Repair information
$pdf->setY(88);
$i = 1;
$totalBilling = 0;
pg_result_seek($repair_res,0);
while($items =& fetchRow($repair_res, 0)) {
	$pdf->setX(11);
	$pdf->Cell(7, 3.5, $i,0);						//No
	$pdf->Cell(75, 3.5, substr($items[2],0,53),0); 	//Detail of repairs
	$pdf->Cell(15, 3.5, number_format($items[3]),0,0,'R');	//Qty
	$pdf->Cell(26, 3.5, number_format($items[4]),0,0,'R');	//Unit Price
	$pdf->Cell(26, 3.5, number_format($items[3]*$items[4]),0,0,'R');	//Amount
	$pdf->Cell(5);
	$pdf->Cell(0, 3.5, $items[5],0,1);				//Remark

	$totalBilling += $items[3]*$items[4];
	$i++;
}

//Replace part
$pdf->setY(150);
$i = 1;
pg_result_seek($replace_res,0);
while($items =& fetchRow($replace_res, 0)) {
	$pdf->setX(11);
	$pdf->Cell(7, 3.5, $i,0);						//No
	$pdf->Cell(75, 3.5, substr($items[2],0,53),0); 	//Detail of repairs
	$pdf->Cell(15, 3.5, number_format($items[3]),0,0,'R');	//Qty
	$pdf->Cell(26, 3.5, number_format($items[4]),0,0,'R');	//Unit Price
	$pdf->Cell(26, 3.5, number_format($items[3]*$items[4]),0,0,'R');	//Amount
	$pdf->Cell(5);
	$pdf->Cell(0, 3.5, $items[5],0,1);				//Remark

	$totalBilling += $items[3]*$items[4];
	$i++;
}

$pdf->setXY(140,203);
$pdf->Cell(20, 4, number_format($totalBilling),0,0,'R');
$pdf->setXY(140,207);
$pdf->Cell(20, 4, number_format($_total_disc),0,0,'R');
$pdf->setXY(140,212);
$pdf->Cell(20, 4, number_format($totalBilling-$_total_disc),0,0,'R');

//Remark
$pdf->setXY(25, 221);
$pdf->MultiCell(170, 3.5, $_remark);

//Note
$pdf->setXY(5, 237);
$pdf->MultiCell(190, 4,
'Note : Mohon segera ditransfer dan diberi keterangan biaya service, type alat dan nama perusahaan yang mentransfer.
           Bukti mohon di fax ke no 021-47882598. Up : Rosalia.
');

//Information
$pdf->setFont('Arial', 'I', 8);
$pdf->setY(245);
$pdf->Cell(197, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)."   ", 0,0,'R');

//Signature By
$pdf->setFont('Arial', '', 9);
$pdf->setXY(165, 267);
$pdf->Cell(40,4,$_signature_by,0,0,"C");
}

//PRINT END==========================================================================================================
//detimine the document name & document path.
$storage = PDF_STORAGE . "billing/service/". date("Ym/", strtotime($_service_date));
$doc_name = trim($_code) . "_rev_" . $_revision_time .".pdf";
(!is_dir($storage))? mkdir($storage, 0777, true) : 0;

//Save pdf document
$pdf->Output($storage.$doc_name, 'F');
?>