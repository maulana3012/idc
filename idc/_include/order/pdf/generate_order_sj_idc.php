<?php
$pdf->setSourceFile(APP_DIR . "_include/order/template_pdf/" . $tpl_pdf[2]);
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, 0,0,0,0);

//Header
$pdf->setFont('Arial', 'B', 15);
$pdf->setXY(15, 5);
$pdf->Cell(100,10, $company[0]);
$pdf->setFont('Arial', '', 9);
$pdf->setXY(15, 14);
$pdf->Cell(170, 3.5, $company[1]);
if(isset($company[2])) {
	$pdf->setXY(15, 18);
	$pdf->Cell(170, 3.5, implode('   ', $company[2]));
}

/*	1	*/	$pdf->setFont('Arial', '', 9);
			$pdf->setXY(177,28);
			$pdf->Cell(25,6,$pdf_for,0,0,'C');
			$pdf->setXY(37,28);
			$pdf->Cell(25,6,"page {$page[0]} / {$page[1]}",0,0,'C');
/*	2	*/	$pdf->setXY(35,37);
			$pdf->Cell(95, 4, $_cus_to_attn);
			$pdf->Cell(47, 4, $_cus_to);
			$pdf->Cell(0, 4, 'J'.substr($_code,1));
/*	3	*/	$pdf->setXY(35,41);
			$pdf->Cell(141, 4, $_cus_to_address);
			$pdf->Cell(0, 4, $_delivery_date);
/*	4	*/	$pdf->setXY(35,50);
			$pdf->Cell(95, 4, $_ship_to_attn);
			$pdf->Cell(47, 4, $_ship_to);
/*	5	*/	$pdf->setXY(35,54);
			$pdf->Cell(138, 4, $_ship_to_address);
/*	6	*/	$pdf->setXY(35,62);
			$pdf->Cell(95, 4, $_po_no);
			$pdf->Cell(47, 4, ucfirst($_received_by));
			$pdf->Cell(0, 4, $_po_date);
/*	7	*/	$pdf->setXY(32,202);
			$pdf->Cell(6, 4, ($_delivery_chk & 1) ? "X":"");		// ex Whouse
			$pdf->Cell(38);
			$pdf->Cell(6, 4, ($_delivery_chk & 2) ? "X":"");		// Franco(P/D)
			$pdf->Cell(39);
			$pdf->Cell(25, 4, $_delivery_by);						// Deliverd by
			$pdf->Cell(6, 4, ($_delivery_chk & 4) ? "X":""); 		// Freight charge :
			$pdf->Cell(27);
			$pdf->Cell(0, 4, number_format((double)$_delivery_freight_charge));
/*	8	*/	$pdf->setXY(15, 215);
			$pdf->MultiCell(183, 4, $_remark);
/*	9	*/	$pdf->setXY(170,257);
			$pdf->Cell(30, 4, $_sign_by, 0, 0, 'C');

//Head Total
$i = 0;
pg_result_seek($cus_res, $counter);
if($counter == 0) {
	$pdf->setY(77);
} else {
	$pdf->setXY(15, 77);
	$pdf->Cell(115,4, 'Previous balance',0);
	$pdf->Cell(19, 4, number_format((double)$total[0]), 0,0,'R');
	$pdf->Cell(25, 4, number_format((double)$total[1]), 0,0,'R');
	$pdf->setY(87);
	$i++;
}
if($counter == $row[2]) {$counter+=$row[1];}


while($items =& fetchRow($cus_res, 0)) {
	$pdf->Cell(5, 3.5, '',0);
	$pdf->Cell(16, 3.5, $items[0],0);								// code
	$pdf->Cell(30, 3.5, substr($items[1],0,18),0);					// item no
	$pdf->Cell(80, 3.5, substr($items[2],0,50),0);					// description
	$pdf->Cell(10, 3.5, number_format((double)$items[4]),0,0,'R');	// qty
	$pdf->Cell(28);
	$pdf->Cell(15, 3.5, $_delivery_date,0);							// delivery date
	$pdf->Cell(15, 3.5, $items[6],0,1);

	$total[0]	+= $items[4];
	$i++;
	if($i == $row[0]) {break;}
}
$counter += $i;

$pdf->setXY(144,193);
$pdf->Cell(7,4, number_format((double)$total[0]), 0, 0,'R');
?>