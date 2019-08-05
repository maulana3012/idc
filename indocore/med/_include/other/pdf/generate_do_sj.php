<?php
$pdf->setSourceFile(APP_DIR . "_include/other/template_pdf/tpl_sj.pdf");
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, 0,0,0,0);

//Header
$pdf->setFont('Arial', 'B', 15);
$pdf->setXY(10, 15);
$pdf->Cell(100,10, $company[0]);
$pdf->setFont('Arial', '', 9);
$pdf->setXY(10, 24);
$pdf->Cell(170, 3.5, $company[1]);
if(isset($company[2])) {
	$pdf->setXY(10, 28);
	$pdf->Cell(170, 3.5, implode('   ', $company[2]));
}

/*	1	*/	$pdf->setFont('Arial', '', 10);
			$pdf->setXY(182,38);
			$pdf->Cell(25,6,"CUSTOMER",0,0,'C');
/*	2	*/	$pdf->setXY(22,49);
			$pdf->Cell(162, 4, $_cus_name);
			$pdf->Cell(0, 4, "J". substr($_code,1,1). substr($_code, 2));
			$pdf->setXY(22,53);
			$pdf->Cell(162, 4, $_cus_address);
			$pdf->Cell(0, 4, date("j-M-Y", strtotime($_do_date)));
/*	3	*/	$pdf->setXY(22,57);
			if($cus[0] != '' && $cus[1] != '') $pdf->Cell(162, 4, "Telp : ".$cus[0]."  Fax : ".$cus[1]);
			else if($cus[0] != '' && $cus[1] == '') $pdf->Cell(162, 4, "Telp : ".$cus[0]);
			else if($cus[0] == '' && $cus[1] != '') $pdf->Cell(162, 4, "Fax : ".$cus[1]);
/*	4	*/	$pdf->setXY(120,62);
			$pdf->Cell(50, 4, $_received_by);
/*	5	*/	$pdf->setXY(35,199);
			$pdf->Cell(6, 4, $_delivery_warehouse);
			$pdf->Cell(38);
			$pdf->Cell(6, 4, $_delivery_franco);
			$pdf->Cell(35);
			$pdf->Cell(23, 4, $_delivery_by);
			$pdf->Cell(4, 4, '');
			if($_delivery_freight_charge > 0) {
				$pdf->Cell(10);
				$pdf->Cell(4, 4, "X");
				$pdf->Cell(26);
				$pdf->Cell(0, 4, 'Rp. '.number_format((double)$_delivery_freight_charge));
			}
/*	6	*/	$pdf->setXY(24, 205);
			$pdf->Cell(74, 4, '['.trim($_ship_to).'] '.substr($_ship_name, 0, 48));
			$pdf->setXY(24, 209);
			$pdf->Cell(64, 4, $cus[2]);
			$pdf->setXY(24, 213);
			if($cus[3] != '' && $cus[4] != '') $pdf->Cell(158, 4, "Telp : ".$cus[3]."  Fax : ".$cus[4]);
			else if($cus[3] != '' && $cus[4] == '') $pdf->Cell(158, 4, "Telp : ".$cus[3]);
			else if($cus[3] == '' && $cus[4] != '') $pdf->Cell(158, 4, "Fax : ".$cus[4]);
/*	7	*/	$pdf->setXY(10, 225);
			$pdf->MultiCell(170, 3.5, $_remark);
			$pdf->setFont('Arial', 'I', 8);
			$pdf->setY(235);
			$pdf->Cell(198, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)."   ", 0,0,'R');
			$pdf->setFont('Arial', '', 10);

$totalCusQty = 0;
pg_result_seek($cusitem_res, 0);
$pdf->setY(76);
while($item_cus =& fetchRow($cusitem_res, 0)) {
	$pdf->setX(10);
	$pdf->Cell(16, 4, $item_cus[0],0);									// code
	$pdf->Cell(20, 4, substr($item_cus[1],0,8),0); 						// item no
	$pdf->Cell(80, 4, substr($item_cus[2],0,50),0);						// description
	$pdf->Cell(20);
	$pdf->Cell(10, 3.5, number_format((double)$item_cus[3]),0,0,'R');	// qty
	$pdf->Cell(5);
	$pdf->Cell(30, 3.5, $item_cus[4],0,1);								// remark
	$totalCusQty += $item_cus[3];
}
$pdf->setXY(149,191);
$pdf->Cell(7,4, number_format((double)$totalCusQty), 0, 0,'R');
?>