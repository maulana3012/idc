<?php
$pdf->setSourceFile(APP_DIR . "_include/order/template_pdf/" . $tpl_pdf[0]);
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, 0,0,0,0);

/*	1	*/	if($page[1] > 1) {
				$pdf->setFont('Arial', 'I', 8);
				$pdf->setXY(38, 28);
				$pdf->Cell(20,6,"page {$page[0]} / {$page[1]}",0,0,'C');
			}
			$pdf->setFont('Arial', '', 10);
/*	2	*/	$pdf->setXY(80,37);
			$pdf->Cell(27, 4, $_po_date,0,0,'C');
			$pdf->Cell(28, 4, $_code,0,0,'C');
			$pdf->Cell(30, 4, "J".substr($_code,1),0,0,'C');
			$pdf->Cell(42, 4, $_po_no,0,0,'C');
/* 	3	*/	$pdf->setXY(8,53);		// Customer to
			$pdf->Cell(100, 4, $_cus_to_attn);
			$pdf->setXY(8,57);
			$pdf->MultiCell(100, 3.5, $_cus_to_address);
			if($cus[0] != '' && $cus[1] != '') 		{$pdf->setX(8); $pdf->Cell(100, 4, "Telp : ".$cus[0]."  Fax : ".$cus[1]);}
			else if($cus[0] != '' && $cus[1] == '') {$pdf->setX(8); $pdf->Cell(100, 4, "Telp : ".$cus[0]);}
			else if($cus[0] == '' && $cus[1] != '') {$pdf->setX(8); $pdf->Cell(100, 4, "Fax : ".$cus[1]);}

			$pdf->setXY(113,53);	// Ship to
			$pdf->Cell(95, 4, $_ship_to_attn);
			$pdf->setXY(113,57);
			$pdf->MultiCell(95, 3.5, $_ship_to_address);
			if($cus[2] != '' && $cus[3] != '') 		{$pdf->setX(113); $pdf->Cell(95, 4, "Telp : ".$cus[2]."  Fax : ".$cus[3]);}
			else if($cus[2] != '' && $cus[3] == '') {$pdf->setX(113); $pdf->Cell(95, 4, "Telp : ".$cus[2]);}
			else if($cus[2] == '' && $cus[3] != '') {$pdf->setX(113); $pdf->Cell(95, 4, "Fax : ".$cus[3]);}
/*	5	*/	$pdf->setXY(38,217);
			$pdf->Cell(42, 4, $_delivery_by);
			$pdf->Cell(5, 4, ($_delivery_freight_charge>0) ? "X":"");
			$pdf->Cell(25);
			$pdf->Cell(0, 4, ($_delivery_freight_charge>0) ? 'Rp. '.number_format((double)$_delivery_freight_charge) : '');
/*	7	*/	$pdf->setXY(24, 224);
			$pdf->MultiCell(170, 3.5, $_remark);
/*	8	*/	$pdf->setXY(153, 264);
			$pdf->Cell(30, 4, $_sign_by, 0, 0, 'C');
/*	9	*/	$pdf->setFont('Arial', 'I', 8);
			$pdf->setY(240);
			if($_type_invoice == 0) {
				$pdf->Cell(195, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time), 0,0,'R');
			} else if($_type_invoice == 1) {
				$pdf->Cell(195, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)." | No. Only", 0,0,'R');
			}
			$pdf->setFont('Arial', '', 10);

//Head Total
$i = 0;
pg_result_seek($cus_res, $counter);
if($counter == 0) {
	$pdf->setY(90);
} else {
	$pdf->setXY(10, 90);
	$pdf->Cell(129,4, 'Previous balance',0);
	$pdf->Cell(14, 4, number_format((double)$total[0]), 0,0,'R');
	$pdf->Cell(27, 4, number_format((double)$total[1]), 0,0,'R');
	$pdf->setY(94);
	$i++;
}
if($counter == $row[2]) {$counter+=$row[1];}

//Body Item
while($items =& fetchRow($cus_res, 0)) {
	$pdf->Cell(14, 3.5, $items[0],0);								// code
	$pdf->Cell(23, 3.5, substr($items[1],0,10),0);					// item no
	$pdf->Cell(72, 3.5, substr($items[2],0,38),0);					// description
	$pdf->Cell(20, 3.5, number_format((double)$items[3]),0,0,'R');	// unit price
	$pdf->Cell(14, 3.5, number_format((double)$items[4]),0,0,'R');	// qty
	$pdf->Cell(27, 3.5, number_format((double)$items[5]),0,0,'R');	// amount
	$pdf->Cell(3);
	$pdf->Cell(0, 3.5, $items[6],0,1);

	$total[0]	+= $items[4];
	$total[1]	+= $items[5];
	$_delivery_date = $items[7];
	$i++;
	if($i == $row[0]) {break;}
}
$counter += $i;

if($counter >= $row[2] && $i<=$row[2]) {
	$amount_vat = $_vat/100 * $total[1];
	$pdf->setXY(107,192);
	$pdf->Cell(30, 4, "Before VAT",0,0,'R');
	$pdf->Cell(16);
	$pdf->Cell(27, 4, number_format((double)$total[1]),0,0,'R');
	$pdf->setXY(107, $pdf->getY() + 4);
	$pdf->Cell(30, 4, "VAT",0,0,'R');
	$pdf->Cell(16);
	$pdf->Cell(27, 4, number_format((double)$amount_vat),0,0,'R');

	//Freight charge
	if($_delivery_freight_charge>0) {
		$pdf->setXY(107, 205);
		$pdf->Cell(30, 4, "Delivery Cost",0,0,"R");
		$pdf->Cell(16);
		$pdf->Cell(27, 4, number_format((double)$_delivery_freight_charge),0,0,"R");
		$pdf->setXY(107, $pdf->getY() + 4);
		$pdf->Cell(30, 4, "Grand Total",0,0,"R");
		$pdf->Cell(16);
		$pdf->Cell(27, 4, number_format((double)$amount_vat + $_delivery_freight_charge),0,0,"R");
	}
}
$pdf->setXY(139,200);
$pdf->Cell(14, 4, number_format((double)$total[0]),0,0,'R');
$pdf->Cell(27, 4, number_format((double)$total[1] + $amount_vat),0,0,'R');
?>