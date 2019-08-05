<?php
$pdf->setSourceFile(APP_DIR . "_include/billing/template_pdf/" . $tpl_pdf[0]);
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, -2,5.5,0,0);

/*	1	*/	if($page[1] > 1) {
				$pdf->setFont('Arial', 'I', 8);
				$pdf->setXY(48, 34);
				$pdf->Cell(0,6,"page {$page[0]} / {$page[1]}");
			}
			$pdf->setFont('Arial', '', 10);
/*	2	*/	$pdf->setXY(77,42);
			$pdf->Cell(28, 4, $_return_date,0,0,'C');
			$pdf->Cell(28, 4, $_code,0,0,'C');
			$pdf->Cell(30, 4, ($cus[5]=='') ? '' : date('d-M-Y', strtotime($cus['5'])),0,0,'C');
			$pdf->Cell(40, 4, $_bill_code,0,0,'C');
/* 	3	*/	$pdf->setXY(6,57);		// Customer to
			$pdf->Cell(100, 4, $_cus_name);
			$pdf->setXY(6,61);
			$pdf->MultiCell(100, 3.5, $_cus_address);
			if($cus[0] != '' && $cus[1] != '') 		{$pdf->setX(6); $pdf->Cell(100, 4, "Telp : ".$cus[0]."  Fax : ".$cus[1]);}
			else if($cus[0] != '' && $cus[1] == '') {$pdf->setX(6); $pdf->Cell(100, 4, "Telp : ".$cus[0]);}
			else if($cus[0] == '' && $cus[1] != '') {$pdf->setX(6); $pdf->Cell(100, 4, "Fax : ".$cus[1]);}
			$pdf->setXY(17,80);
			$pdf->Cell(100, 4, $_cus_attn);

			$pdf->setXY(110,57);	// Bill to
			$pdf->Cell(95, 4, $_ship_name);
			$pdf->setXY(110,61);
			$pdf->MultiCell(95, 3.5, $cus[2]);
			if($cus[3] != '' && $cus[4] != '') 		{$pdf->setX(110); $pdf->Cell(95, 4, "Telp : ".$cus[3]."  Fax : ".$cus[4]);}
			else if($cus[3] != '' && $cus[4] == '') {$pdf->setX(110); $pdf->Cell(95, 4, "Telp : ".$cus[3]);}
			else if($cus[3] == '' && $cus[4] != '') {$pdf->setX(110); $pdf->Cell(95, 4, "Fax : ".$cus[4]);}
/*	4	*/	$pdf->setXY(6, 225);
			if($_bank != '') $pdf->Cell(13,4,'Bank : ');
			if($_bank == 'BCA1' || $_bank == 'BCA2') $pdf->Cell(28,4,'BCA');
			else if($_bank == 'BII1' || $_bank == 'BII2') $pdf->Cell(28,4,'BII');
			$pdf->setXY(17, 225);
			$pdf->setLeftMargin(17);
			$pdf->Write(4, $_bank_address);
/*	5	*/	$pdf->setXY(35,203);
			$pdf->Cell(42, 4, $_delivery_by);
			$pdf->Cell(5, 4, ($_delivery_chk & 1) ? "X":"");
			$pdf->Cell(25);
			$pdf->Cell(0, 4, ($_delivery_freight_charge>0) ? 'Rp. '.number_format((double)$_delivery_freight_charge) : '');
/*	6	*/	$pdf->setXY(40,211);
			$pdf->Cell(5, 4, $_payment_widthin_days, 0, 0,'R');
			$pdf->Cell(40);
			$pdf->Cell(0,4, (empty($_payment_giro_due) ? "": date("j/M/Y", strtotime($_payment_giro_due))));
			$pdf->setXY(32,218);
			$pdf->Cell(6, 4, ($_payment_chk & 16) ? "X":"");
			$pdf->Cell(40);
			$pdf->Cell(6, 4, ($_payment_chk & 32) ? "X":"");
			$pdf->Cell(28);
			$pdf->Cell(6, 4, ($_payment_chk & 64) ? "X":"");
			$pdf->Cell(25);
			$pdf->Cell(6, 4, ($_payment_chk & 128) ? "X":"");
/*	7	*/	$pdf->setXY(22, 238);
			$pdf->MultiCell(170, 3.5, $_remark);
/*	8	*/	$pdf->setXY(10, 271);
			$pdf->Cell(40,4,ucfirst($_signature_by),0,0,"C");
/*	9	*/	$pdf->setFont('Arial', 'I', 8);
			$pdf->setY(248);
			if($_type_invoice == 0) {
				$pdf->Cell(190, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time), 0,0,'R');
			} else if($_type_invoice == 1) {
				$pdf->Cell(190, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)." | No. Only", 0,0,'R');
			}
			$pdf->setFont('Arial', '', 10);

//Head Total
$i = 0;
pg_result_seek($result, $counter[1]);
if($counter[0] == 0) {
	$pdf->setY(95);
} else {
	$pdf->setXY(4,95);
	$pdf->Cell(130, 4, "Previous balance");
	$pdf->Cell(14, 4, number_format((double)$total[0]),0,0,'R');
	$pdf->Cell(28, 4, number_format((double)$total[1]),0,0,'R');
	$pdf->setY(99);
	$i++;
}
if($counter[0] == $row[2]) {$counter[0]+=$row[1];}

//Body Item
while($items =& fetchRow($result,0)) {
	$pdf->setX(6);
	$pdf->Cell(16, 4, $items[0],0);							// code
	$pdf->Cell(20, 4, substr($items[1],0,8),0); 			// item no
	$pdf->Cell(75, 4, substr($items[2],0,40),0);			// description
	$pdf->Cell(17, 4, number_format($items[3]),0,0,'R'); 	// unit price
	$pdf->Cell(14, 4, number_format($items[4]),0,0,'R'); 	// qty
	$pdf->Cell(28, 4, number_format($items[5]),0,0,'R');	// amount
	$pdf->Cell(5);
	$pdf->Cell(0, 4, $items[6],0,1);

	$total[0] += $items[4];
	$total[1] += $items[5];
	$i++;
	if($i == $row[0]) {break;}
}
$counter[0] += $i;
if($page[0] > 1) { $counter[1] += $i-1; }
else			 { $counter[1] += $i; }

//Foot Total
$total[4] = $total[1];
if($counter[0] >= $row[2] && $i<=$row[4]) {
	if ($_vat > 0) {
		if($_disc > 0) {
			$total[2] = $_disc/100 * $total[1];
			$total[4] = $total[1] - $total[2];
			$total[3] = $_vat/100 * $total[4];	

			$pdf->setXY(98, 171);
			$pdf->Cell(35, 3.5, "Sub Total",0,0,"R");
			$pdf->Cell(20);
			$pdf->Cell(23, 3.5, number_format((double)$total[1]), 0,0,'R');
		
			$pdf->setXY(98, $pdf->getY() + 4);
			$pdf->Cell(35, 3.5, "$_disc %             Disc",0,0,"R");
			$pdf->Cell(20);
			$pdf->Cell(23, 3.5, number_format((double)$total[2]), 0,0,'R');
		
			$pdf->setXY(98, $pdf->getY() + 4);
			$pdf->Cell(35, 3.5, "Before VAT",0,0,"R");
			$pdf->Cell(20);
			$pdf->Cell(23, 3.5, number_format((double)$total[4]), 0,0,'R');
		
			$pdf->setXY(98, $pdf->getY() + 4);
			$pdf->Cell(35, 3.5, "$_vat %             VAT",0,0,"R");
			$pdf->Cell(20);
			$pdf->Cell(23, 3.5, number_format((double)$total[3]), 0,0,'R');

		} else if($_disc <= 0) {
			$total[3] = $_vat/100 * $total[1];
		
			$pdf->setXY(98, 179);
			$pdf->Cell(35, 3.5, "Before VAT",0,0,"R");
			$pdf->Cell(20);
			$pdf->Cell(23, 3.5, number_format((double)$total[1]), 0,0,'R');
		
			$pdf->setXY(98, $pdf->getY() + 4);
			$pdf->Cell(35, 3.5, "$_vat %             VAT",0,0,"R");
			$pdf->Cell(20);
			$pdf->Cell(23, 3.5, number_format((double)$total[3]), 0,0,'R');
		}
	} else if ($_vat <= 0) {
		if($_disc > 0) {
			$total[2] = $_disc/100 * $total[1];

			$pdf->setXY(98, 179);
			$pdf->Cell(35, 3.5, "Sub Total",0,0,"R");
			$pdf->Cell(20);
			$pdf->Cell(23, 3.5, number_format((double)$total[1]), 0,0,'R');

			$pdf->setXY(98, $pdf->getY() + 4);
			$pdf->Cell(35, 3.5, "$_disc %             Disc",0,0,"R");
			$pdf->Cell(20);
			$pdf->Cell(23, 3.5, number_format((double)$total[2]), 0,0,'R');
		}
	}

	//Freight charge
	if(!empty($_delivery_freight_charge)) {
		$pdf->setXY(100, 176);
		$pdf->Cell(35, 3.5, "Delivery Cost",0,0,"R");
		$pdf->Cell(20);
		$pdf->Cell(24, 3.5, number_format((double)$_delivery_freight_charge),0,0,"R");

		$pdf->setXY(100, 180);
		$pdf->Cell(35, 3.5, "Grand Total",0,0,"R");
		$pdf->Cell(20);
		$pdf->Cell(24, 3.5, number_format((double)$total[1] + round($total[3]) - round($total[2]) + $_delivery_freight_charge),0,0,"R");
	}

	$counter[0] += $row[1];
}

//TOTAL & TOTAL AMOUNT
$pdf->setXY(134, 187);
$pdf->Cell(14,4, number_format((double)$total[0]), 0, 0,'R');
$pdf->Cell(28,4, number_format((double)$total[1] + round($total[3]) - round($total[2])), 0,0,'R');
?>