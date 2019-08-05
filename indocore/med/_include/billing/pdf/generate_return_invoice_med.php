<?php
//RETURN INVOICE ===============================================================================================
$pdf->setSourceFile(APP_DIR . "_include/billing/template_pdf/" . $tpl_pdf[0]);
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, -2,5.5,0,0);

/*	1	*/	if($page[1] > 1) {
				$pdf->setFont('Arial', 'I', 8);
				$pdf->setXY(11, 36);
				$pdf->Cell(20,6,"page {$page[0]} / {$page[1]}");
			}
			$pdf->setFont('Arial', '', 10);
/*	2	*/	$pdf->setXY(70,38);
			$pdf->Cell(33, 4, $_return_date, 0,0,'C');
			$pdf->Cell(35, 4, $_code, 0,0,'C');
			$pdf->Cell(25, 4, ($cus[5]=='') ? '' : date('d-M-Y', strtotime($cus['5'])), 0,0,'C');
			$pdf->Cell(40, 4, $_bill_code, 0,0,'C');
/* 	3	*/	$pdf->setXY(11,56);		// Customer to
			$pdf->Cell(95, 4, $_cus_name);
			$pdf->setXY(11,60);
			$pdf->MultiCell(95, 3.5, $_cus_address);
			if($cus[0] != '' && $cus[1] != '') 		{$pdf->setX(11); $pdf->Cell(95, 4, "Telp : ".$cus[0]."  Fax : ".$cus[1]);}
			else if($cus[0] != '' && $cus[1] == '') {$pdf->setX(11); $pdf->Cell(95, 4, "Telp : ".$cus[0]);}
			else if($cus[0] == '' && $cus[1] != '') {$pdf->setX(11); $pdf->Cell(95, 4, "Fax : ".$cus[1]);}
			$pdf->setXY(22,77);
			$pdf->Cell(95, 4, $_cus_attn);

			$pdf->setXY(115,56);	// Bill to
			$pdf->Cell(95, 4, $_ship_name);
			$pdf->setXY(115,60);
			$pdf->MultiCell(95, 3.5, $cus[2]);
			if($cus[3] != '' && $cus[4] != '') 		{$pdf->setX(115); $pdf->Cell(95, 4, "Telp : ".$cus[3]."  Fax : ".$cus[4]);}
			else if($cus[3] != '' && $cus[4] == '') {$pdf->setX(115); $pdf->Cell(95, 4, "Telp : ".$cus[3]);}
			else if($cus[3] == '' && $cus[4] != '') {$pdf->setX(115); $pdf->Cell(95, 4, "Fax : ".$cus[4]);}
/*	4	*/	$pdf->setXY(11, 218);
			if($_bank != '') $pdf->Cell(15,4,'Bank : ');
			$pdf->setXY(17, 218);
			$pdf->setLeftMargin(28);
			$pdf->Write(4, $_bank_address);
/*	5	*/	$pdf->setXY(43,195);
			$pdf->Cell(26, 4, $_delivery_by);
			$pdf->Cell(5, 4, ($_delivery_chk & 1) ? "X":"");
			$pdf->Cell(23);
			$pdf->Cell(0, 4, ($_delivery_freight_charge>0) ? 'Rp. '.number_format((double)$_delivery_freight_charge) : '');
/*	6	*/	$pdf->setXY(45,203);
			$pdf->Cell(5, 4, $_payment_widthin_days, 0, 0,'R');
			$pdf->Cell(30);
			$pdf->Cell(0,4, (empty($_payment_giro_due) ? "": date("j/M/Y", strtotime($_payment_giro_due))));
			$pdf->setXY(36,210);
			$pdf->Cell(6, 4, ($_payment_chk & 16) ? "X":"");
			$pdf->Cell(27);
			$pdf->Cell(6, 4, ($_payment_chk & 32) ? "X":"");
			$pdf->Cell(35);
			$pdf->Cell(6, 4, ($_payment_chk & 64) ? "X":"");
			$pdf->Cell(20);
			$pdf->Cell(6, 4, ($_payment_chk & 128) ? "X":"");
/*	7	*/	$pdf->setXY(28, 232);
			$pdf->MultiCell(170, 3.5, $_remark);
/*	8	*/	$pdf->setXY(30, 266);
			$pdf->Cell(40,4,ucfirst($_signature_by),0,0,"C");
/*	9	*/	$pdf->setFont('Arial', 'I', 8);
			$pdf->setY(247);
			if($_type_invoice == 0) {
				$pdf->Cell(175, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time), 0,0,'R');
			} else if($_type_invoice == 1) {
				$pdf->Cell(175, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)." | No. Only", 0,0,'R');
			}
			$pdf->setFont('Arial', '', 10);

//Head Total
$i = 0;
pg_result_seek($result, $counter[1]);
if($counter[0] == 0) {
	$pdf->setY(92);
} else {
	$pdf->setXY(11,92);
	$pdf->Cell(128, 4, "Previous balance");
	$pdf->Cell(10, 4, number_format((double)$total[0]),0,0,'R');
	$pdf->Cell(25, 4, number_format((double)$total[1]),0,0,'R');
	$pdf->setY(96);
	$i++;
}
if($counter[0] == $row[2]) {$counter[0]+=$row[1];}

//Body Item
while($items =& fetchRow($result,0)) {
	$pdf->setX(11);
	$pdf->Cell(15, 4, $items[0],0);							// code
	$pdf->Cell(25, 4, substr($items[1],0,8),0); 			// item no
	$pdf->Cell(68, 4, substr($items[2],0,35),0);			// description
	$pdf->Cell(20, 4, number_format($items[3]),0,0,'R'); 	// unit price
	$pdf->Cell(10, 4, number_format($items[4]),0,0,'R'); 	// qty
	$pdf->Cell(25, 4, number_format($items[5]),0,0,'R');	// amount
	$pdf->Cell(2);
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

			$pdf->setXY(120, 164);
			$pdf->Cell(18, 3.5, "Sub Total",0,0,"R");
			$pdf->Cell(11);
			$pdf->Cell(25, 3.5, number_format((double)$total[1]), 0,0,'R');
		
			$pdf->setXY(120, $pdf->getY() + 4);
			$pdf->Cell(18, 3.5, "$_disc %             Disc",0,0,"R");
			$pdf->Cell(11);
			$pdf->Cell(25, 3.5, number_format((double)$total[2]), 0,0,'R');
		
			$pdf->setXY(120, $pdf->getY() + 4);
			$pdf->Cell(18, 3.5, "Before VAT",0,0,"R");
			$pdf->Cell(11);
			$pdf->Cell(25, 3.5, number_format((double)$total[4]), 0,0,'R');
		
			$pdf->setXY(120, $pdf->getY() + 4);
			$pdf->Cell(18, 3.5, "$_vat %             VAT",0,0,"R");
			$pdf->Cell(11);
			$pdf->Cell(25, 3.5, number_format((double)$total[3]), 0,0,'R');

		} else if($_disc <= 0) {
			$total[3] = $_vat/100 * $total[1];
		
			$pdf->setXY(120, 172);
			$pdf->Cell(18, 3.5, "Before VAT",0,0,"R");
			$pdf->Cell(11);
			$pdf->Cell(25, 3.5, number_format((double)$total[1]), 0,0,'R');
		
			$pdf->setXY(120, $pdf->getY() + 4);
			$pdf->Cell(18, 3.5, "$_vat %             VAT",0,0,"R");
			$pdf->Cell(11);
			$pdf->Cell(25, 3.5, number_format((double)$total[3]), 0,0,'R');
		}
	} else if ($_vat <= 0) {
		if($_disc > 0) {
			$total[2] = $_disc/100 * $total[1];

			$pdf->setXY(120, 172);
			$pdf->Cell(18, 3.5, "Sub Total",0,0,"R");
			$pdf->Cell(11);
			$pdf->Cell(25, 3.5, number_format((double)$total[1]), 0,0,'R');

			$pdf->setXY(120, $pdf->getY() + 4);
			$pdf->Cell(18, 3.5, "$_disc %             Disc",0,0,"R");
			$pdf->Cell(11);
			$pdf->Cell(25, 3.5, number_format((double)$total[2]), 0,0,'R');
		}
	}

	//Freight charge
	if(!empty($_delivery_freight_charge)) {
		$pdf->setXY(120, 184);
		$pdf->Cell(18, 3.5, "Delivery Cost",0,0,"R");
		$pdf->Cell(11);
		$pdf->Cell(25, 3.5, number_format((double)$_delivery_freight_charge),0,0,"R");

		$pdf->setXY(120, $pdf->getY() + 4);
		$pdf->Cell(18, 3.5, "Grand Total",0,0,"R");
		$pdf->Cell(11);
		$pdf->Cell(25, 3.5, number_format((double)$total[1] + round($total[3]) - round($total[2]) + $_delivery_freight_charge),0,0,"R");
	}

	$counter[0] += $row[1];
}

//TOTAL & TOTAL AMOUNT
$pdf->setXY(139, 179.5);
$pdf->Cell(10,4, number_format((double)$total[0]), 0, 0,'R');
$pdf->Cell(25,4, number_format((double)$total[1] + round($total[3]) - round($total[2])), 0,0,'R');
?>