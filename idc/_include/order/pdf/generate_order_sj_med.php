<?php
$pdf->setSourceFile(APP_DIR . "_include/order/template_pdf/" . $tpl_pdf[2]);
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, -2,5.5,0,0);

/*	1	*/	if($page[1] > 1) {
				$pdf->setFont('Arial', 'I', 8);
				$pdf->setXY(38, 34);
				$pdf->Cell(20,6,"page {$page[0]} / {$page[1]}",0,0,'C');
			}
			$pdf->setFont('Arial', '', 10);
/*	2	*/	$pdf->setXY(105,42);
			$pdf->Cell(33, 4, $_po_date,0,0,'C');
			$pdf->Cell(30, 4, "J".substr($_code,1),0,0,'C');
			$pdf->Cell(38, 4, $_po_no,0,0,'C');
/* 	3	*/	$pdf->setXY(6,57);		// Customer to
			$pdf->Cell(100, 4, $_cus_to_attn);
			$pdf->setXY(6,61);
			$pdf->MultiCell(100, 3.5, $_cus_to_address);
			if($cus[0] != '' && $cus[1] != '') 		{$pdf->setX(6); $pdf->Cell(100, 4, "Telp : ".$cus[0]."  Fax : ".$cus[1]);}
			else if($cus[0] != '' && $cus[1] == '') {$pdf->setX(6); $pdf->Cell(100, 4, "Telp : ".$cus[0]);}
			else if($cus[0] == '' && $cus[1] != '') {$pdf->setX(6); $pdf->Cell(100, 4, "Fax : ".$cus[1]);}

			$pdf->setXY(112,57);	// Ship to
			$pdf->Cell(95, 4, $_ship_to_attn);
			$pdf->setXY(112,61);
			$pdf->MultiCell(95, 3.5, $_ship_to_address);
			if($cus[2] != '' && $cus[3] != '') 		{$pdf->setX(112); $pdf->Cell(95, 4, "Telp : ".$cus[2]."  Fax : ".$cus[3]);}
			else if($cus[2] != '' && $cus[3] == '') {$pdf->setX(112); $pdf->Cell(95, 4, "Telp : ".$cus[2]);}
			else if($cus[2] == '' && $cus[3] != '') {$pdf->setX(112); $pdf->Cell(95, 4, "Fax : ".$cus[3]);}
/*	4	*/	$pdf->setXY(35,228);
			$pdf->Cell(42, 4, $_delivery_by);
			$pdf->Cell(5, 4, ($_delivery_freight_charge>0) ? "X":"");
			$pdf->Cell(25);
			$pdf->Cell(0, 4, ($_delivery_freight_charge>0) ? 'Rp. '.number_format((double)$_delivery_freight_charge) : '');
/*	6	*/	$pdf->setXY(22, 236);
			$pdf->MultiCell(168, 3.5, $_remark);
/*	7	*/	$pdf->setXY(15, 270);
			$pdf->Cell(30, 4, $_sign_by, 0, 0, 'C');
/*	8	*/	$pdf->setFont('Arial', 'I', 8);
			$pdf->setY(246);
			if($_type_invoice == 0) {
				$pdf->Cell(190, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time), 0,0,'R');
			} else if($_type_invoice == 1) {
				$pdf->Cell(190, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)." | No. Only", 0,0,'R');
			}
			$pdf->setFont('Arial', '', 10);

//Head Total
$i = 0;
pg_result_seek($cus_res, $counter);
if($counter==0) {
	$pdf->setY(96);
} else {
	$pdf->setXY(6,96);
	$pdf->Cell(150, 4, "Previous balance");
	$pdf->Cell(11, 4, number_format((double)$qty),0,0,'R');
	$pdf->setY(100);
	$i++;
}
while($items =& fetchRow($cus_res, 0)) {
	$pdf->setX(6);
	$pdf->Cell(16, 4, $items[0],0);									// code
	$pdf->Cell(35, 4, substr($items[1],0,18),0); 					// item no
	$pdf->Cell(90, 4, substr($items[2],0,60),0);					// description
	$pdf->Cell(5);
	$pdf->Cell(15, 4, number_format((double)$items[4]),0,0,'R');	// qty
	$pdf->Cell(5);
	$pdf->Cell(0, 4, $items[6],0,1);								// remark

	$qty += $items[4];
	$i++;
	if($i == $row[0]) {break;}
}
$counter += $i;

$pdf->setXY(160,212);
$pdf->Cell(7,4, number_format((double)$qty), 0, 0,'R');
?>