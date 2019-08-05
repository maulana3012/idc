<?php
$pdf->setSourceFile(APP_DIR . "_include/other/template_pdf/". $tpl_pdf[0]);
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, 0,0,0,0);

//Header
$pdf->setFont('Arial', 'B', 15);
$pdf->setXY(10, 10);
$pdf->Cell(100,10, $company[0]);
$pdf->setFont('Arial', '', 9);
$pdf->setXY(10, 19);
$pdf->Cell(170, 3.5, $company[1]);
if(isset($company[2])) {
	$pdf->setXY(10, 23);
	$pdf->Cell(170, 3.5, implode('   ', $company[2]));
}

/*	1	*/	$pdf->setFont('Arial', '', 10);
			$pdf->setXY(178, 32);
			$pdf->Cell(25,6,"WAREHOUSE",0,0,'C');
/*	2	*/	$pdf->setXY(28,42);
			$pdf->Cell(153, 4, $_cus_name);
			$pdf->Cell(0, 4, $_code);
/*	3	*/	$pdf->setXY(28,46);
			$pdf->Cell(153, 4, $_cus_address);
			$pdf->Cell(0, 4, $_do_date);
/*	4	*/	$pdf->setXY(28,51);
			if($cus[0] != '' && $cus[1] != '') $pdf->Cell(153, 4, "Telp : ".$cus[0]."  Fax : ".$cus[1]);
			else if($cus[0] != '' && $cus[1] == '') $pdf->Cell(153, 4, "Telp : ".$cus[0]);
			else if($cus[0] == '' && $cus[1] != '') $pdf->Cell(153, 4, "Fax : ".$cus[1]);
/*	5	*/	$pdf->setXY(28,54);
			$pdf->Cell(153, 4, $_ship_name);
			$pdf->setXY(28,58);
			$pdf->Cell(153, 4, $cus[2]);
			$pdf->setXY(28,62);
			if($cus[3] != '' && $cus[4] != '') $pdf->Cell(153, 4, "Telp : ".$cus[3]."  Fax : ".$cus[4]);
			else if($cus[3] != '' && $cus[4] == '') $pdf->Cell(153, 4, "Telp : ".$cus[3]);
			else if($cus[3] == '' && $cus[4] != '') $pdf->Cell(153, 4, "Fax : ".$cus[4]);
/*	6	*/	$pdf->setXY(10, 244);
			$pdf->MultiCell(170, 3.5, $_remark);
			$pdf->setFont('Arial', 'I', 8);
			$pdf->setY(253);
			$pdf->Cell(195, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)."   ", 0,0,'R');
			$pdf->setFont('Arial', '', 10);

$pdf->setY(75);
while($items =& fetchRow($whitem_res, 0)) {
	$pdf->setX(10);
	$pdf->Cell(16, 4, $items[0],0);									// code
	$pdf->Cell(16, 4, $items[1],0);									// it code for
	$pdf->Cell(22, 4, substr($items[2],0,8),0); 					// item no
	$pdf->Cell(75, 4, substr($items[3],0,40),0);					// description
	$pdf->Cell(15, 4, number_format((double)$items[4],2),0,0,'R');	// qty
	$pdf->Cell(4);
	$pdf->Cell(0, 4, $items[5],0,1);								//remark
	$totalWHQty += $items[4];
}

$pdf->setY(163);
while($item_cus=& fetchRow($cusitem_res, 0)) {
	$pdf->setX(11);
	$pdf->Cell(15, 3.5, $item_cus[0],0);								//code
	$pdf->Cell(35, 3.5, substr($item_cus[1],0,30),0); 					// item no
	$pdf->Cell(71, 3.5, substr($item_cus[2],0,40),0);					// description
	$pdf->Cell(15, 3.5, number_format((double)$item_cus[3]),0,0,'R');	// qty
	$pdf->Cell(5);
	$pdf->Cell(0, 3.5, $item_cus[5],0,1);								//remark
	$totalCusQty += $item_cus[3];
}

$pdf->setXY(142,152);
$pdf->Cell(0, 4, number_format((double)$totalWHQty,2));
$pdf->setXY(140,240);
$pdf->Cell(0, 4, $totalCusQty);
?>