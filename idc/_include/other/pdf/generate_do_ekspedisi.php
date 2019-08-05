<?php
$pdf->setSourceFile(APP_DIR . "_include/other/template_pdf/tpl_ekspedisi.pdf");
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
			$pdf->setXY(180, 36);
			$pdf->Cell(25,6,"EKSPEDISI",0,0,'C');
/*	2	*/	$pdf->setXY(25,47);
			$pdf->Cell(158, 4, $_ship_name);
			$pdf->Cell(0, 4, $_code);
/*	3	*/	$pdf->setXY(25,51);
			$pdf->Cell(158, 4, $cus[2]);
			$pdf->Cell(0, 4, $_do_date);
/*	4	*/	$pdf->setXY(25,55);
			if($cus[0] != '' && $cus[1] != '') $pdf->Cell(125, 4, "Telp : ".$cus[0]."  Fax : ".$cus[1]);
			else if($cus[0] != '' && $cus[1] == '') $pdf->Cell(125, 4, "Telp : ".$cus[0]);
			else if($cus[0] == '' && $cus[1] != '') $pdf->Cell(125, 4, "Fax : ".$cus[1]);
/*	5	*/	$pdf->setXY(184,61);
			$pdf->Cell(0, 4, $_code);
			$pdf->setXY(184,65);
			$pdf->Cell(0, 4, date("j-M-Y", strtotime($_do_date)));
/*	6	*/	$pdf->setXY(25, 219);
			$pdf->Cell(74, 4, '['.trim($_ship_to).'] '.substr($_ship_name, 0, 48));
			$pdf->setXY(25, 223);
			$pdf->Cell(64, 4, $cus[2]);
/*	7	*/	$pdf->setFont('Arial', 'I', 8);
			$pdf->setY(238);
			$pdf->Cell(198, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)."   ", 0,0,'R');
			$pdf->setFont('Arial', '', 10);

$pdf->setY(87);
$total = array(0,0);
while($items =& fetchRow($res_freight, 0)) {
	$pdf->SetFont('Arial','',10);
	$pdf->setX(8);
	$pdf->Cell(16, 4, $items[0],0);									// code
	$pdf->Cell(20, 4, substr($items[1],0,8),0); 					// item no
	$pdf->Cell(75, 4, substr($items[2],0,40),0);					// description
	$pdf->Cell(17, 4, number_format((double)$items[3]),0,0,'R'); 	// unit price
	$pdf->Cell(14, 4, number_format((double)$items[4]),0,0,'R');	// qty
	$pdf->Cell(28, 4, number_format((double)$items[5]),0,0,'R');	// amount
	$pdf->Cell(8);
	$pdf->Cell(0, 4, $items[6],0,1);								// remark
	
	$total[0] += $items[4];
	$total[1] += $items[5];
}

$amount_disc = 0;
$amount_vat  = 0;
$amountAfterDisc = $total[1];
$pdf->setXY(135, 202);
$pdf->Cell(14,4, number_format((double)$total[0]), 0, 0,'R');
$pdf->Cell(28,4, number_format((double)$total[1] + round($amount_vat) - round($amount_disc)), 0,0,'R');

if(!empty($_delivery_freight_charge)) {
	$pdf->setXY(123, $pdf->getY() + 4.5);
	$pdf->Cell(22, 3.5, "Delivery Cost",0,0,"R");
	$pdf->Cell(32, 3.5, number_format((double)$_delivery_freight_charge),0,0,"R");

	$pdf->setXY(123, $pdf->getY() + 4);
	$pdf->Cell(22, 3.5, "Grand Total",0,0,"R");
	$pdf->Cell(32, 3.5, number_format((double)$total[1] + round($amount_vat) - round($amount_disc) + $_delivery_freight_charge),0,0,"R");
}
?>