<?php
$pdf->setSourceFile(APP_DIR . "_include/billing/template_pdf/" . $tpl_pdf[0]);
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, -2,5.5,0,0);

/*	1	*/	if($page[1] > 1) {
				$pdf->setFont('Arial', 'I', 8);
				$pdf->setXY(42, 45);
				$pdf->Cell(20,6,"page {$page[0]} / {$page[1]}",0,0,'C');
			}
			$pdf->setFont('Arial', '', 10);
/*	2	*/	$pdf->setXY(50,52);
			$pdf->Cell(25, 4, $_inv_date,0,0,'C');
			$pdf->Cell(37, 4, $_code,0,0,'C');
			if($_sj_code== '') $pdf->Cell(32, 4, "J". substr($_code,1,1). substr($_code, 2),0,0,'C');
			else $pdf->Cell(32, 4, $_sj_code,0,0,'C');
			$pdf->Cell(27, 4, $_po_date,0,0,'C');
			$pdf->Cell(27, 4, $_po_no,0,0,'C');
/* 	3	*/	$pdf->setXY(13,68);		// Customer to
			$pdf->Cell(100, 4, $_cus_name);
			$pdf->setXY(13,72);
			$pdf->MultiCell(95, 3.5, $_cus_address);
			if($cus[0] != '' && $cus[1] != '') 		{$pdf->setX(13); $pdf->Cell(90, 4, "Telp : ".$cus[0]."  Fax : ".$cus[1]);}
			else if($cus[0] != '' && $cus[1] == '') {$pdf->setX(13); $pdf->Cell(90, 4, "Telp : ".$cus[0]);}
			else if($cus[0] == '' && $cus[1] != '') {$pdf->setX(13); $pdf->Cell(90, 4, "Fax : ".$cus[1]);}
			$pdf->setXY(22,88);
			$pdf->Cell(100, 4, $_cus_attn);
			if(!empty($_cus_npwp)) {
				$pdf->setXY(13,92);
				$pdf->Cell(90, 4, "NPWP:  $_cus_npwp");
			}

			$pdf->setXY(112,68);	// Bill to
			$pdf->Cell(95, 4, $_ship_name);
			$pdf->setXY(112,72);
			$pdf->MultiCell(95, 3.5, $cus[2]);
			if($cus[3] != '' && $cus[4] != '') 		{$pdf->setX(112); $pdf->Cell(95, 4, "Telp : ".$cus[3]."  Fax : ".$cus[4]);}
			else if($cus[3] != '' && $cus[4] == '') {$pdf->setX(112); $pdf->Cell(95, 4, "Telp : ".$cus[3]);}
			else if($cus[3] == '' && $cus[4] != '') {$pdf->setX(112); $pdf->Cell(95, 4, "Fax : ".$cus[4]);}
/*	4	*/	$pdf->setXY(13, 222);
			if($_bank != '') $pdf->Cell(13,4,'Bank : ');
			$pdf->setXY(17, 222);
			$pdf->setLeftMargin(25);
			$pdf->Write(4, $_bank_address);
/*	5	*/	$pdf->setXY(43,199);
			$pdf->Cell(26, 4, $_delivery_by);
			$pdf->Cell(5, 4, ($_delivery_chk & 1) ? "X":"");
			$pdf->Cell(23);
			$pdf->Cell(0, 4, ($_delivery_freight_charge>0) ? 'Rp. '.number_format((double)$_delivery_freight_charge) : '');
/*	6	*/	$pdf->setXY(45,206);
			$pdf->Cell(5, 4, $_payment_widthin_days, 0, 0,'R');
			$pdf->Cell(30);
			$pdf->Cell(0,4, (empty($_payment_giro_due) ? "": date("j/M/Y", strtotime($_payment_giro_due))));
			$pdf->setXY(36,213);
			$pdf->Cell(6, 4, ($_payment_chk & 16) ? "X":"");
			$pdf->Cell(27);
			$pdf->Cell(6, 4, ($_payment_chk & 32) ? "X":"");
			$pdf->Cell(32);
			$pdf->Cell(6, 4, ($_payment_chk & 64) ? "X":"");
			$pdf->Cell(20);
			$pdf->Cell(6, 4, ($_payment_chk & 128) ? "X":"");
			$pdf->setXY(36,217);
			$pdf->Cell(32, 4, $_payment_cash_by,0);
			$pdf->Cell(37, 4, $_payment_check_by,0);
			$pdf->Cell(28, 4, $_payment_transfer_by,0);
			$pdf->Cell(35, 4, (empty($_payment_giro_issue) ? "":"Issue : ".date("j/M/Y", strtotime($_payment_giro_issue))),0);
/*	7	*/	$pdf->setXY(28, 235);
			$pdf->MultiCell(170, 3.5, $_remark);
/*	8	*/	if($_paper_format == "A") {
				$pdf->setXY(30, 266);
				$pdf->Cell(40,4,ucfirst($_signature_by),0,0,"C");
			} else { 
				$pdf->setXY(145, 266);
				$pdf->Cell(40,4,ucfirst($_signature_by),0,0,"C");
			}
/*	9	*/	$pdf->setFont('Arial', 'I', 8);
			$pdf->setY(248);
			if($_type_invoice == 0) {
				$pdf->Cell(175, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time), 0,0,'R');
			} else if($_type_invoice == 1) {
				$pdf->Cell(175, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)." | No. Only", 0,0,'R');
			}
/*	10	*/	if($currentDept == 'dealer') {
				$pdf->setXY(12, 189);
				$pdf->setFont('Arial', 'I', 9);
				$pdf->MultiCell(80, 3.5, "Barang yang sudah dibeli tidak dapat dikembalikan atau ditukar dengan barang lain");
			}
			$pdf->setFont('Arial', '', 10);

//Head Total
$i = 0;
pg_result_seek($result, $counter[1]);
if($counter[0] == 0) {
	$pdf->setY(103);
} else {
	$pdf->setXY(12,103);
	$pdf->Cell(118, 4, "Previous balance");
	$pdf->Cell(14, 4, number_format((double)$total[0]),0,0,'R');
	$pdf->Cell(23, 4, number_format((double)$total[1]),0,0,'R');
	$pdf->setY(107);
	$i++;
}
if($counter[0] == $row[2]) {$counter[0]+=$row[1];}

//Body Item
while($items =& fetchRow($result, 0)) {
	$pdf->setX(12);
	$pdf->Cell(15, 4, $items[0],0);									// code
	$pdf->Cell(22, 4, substr($items[1],0,8),0); 					// item no
	$pdf->Cell(65, 4, substr($items[2],0,35),0);					// description
	$pdf->Cell(20, 4, number_format((double)$items[3]),0,0,'R');	// unit price
	$pdf->Cell(10, 4, number_format((double)$items[4]),0,0,'R');	// qty
	$pdf->Cell(23, 4, number_format((double)$items[5]),0,0,'R');	// amount
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
	if ($_vat_val > 0) {

		if($_disc > 0) {
			$total[2] = $_disc/100 * $total[1];
			$total[4] = $total[1] - $total[2];
			$total[3] = $_vat_val/100 * $total[4];	

			$pdf->setXY(97, 168);
			$pdf->Cell(35, 3.5, "Sub Total",0,0,"R");
			$pdf->Cell(11);
			$pdf->Cell(24, 3.5, number_format((double)$total[1]), 0,0,'R');
		
			$pdf->setXY(97, $pdf->getY() + 4);
			$pdf->Cell(35, 3.5, "$_disc %             Disc",0,0,"R");
			$pdf->Cell(11);
			$pdf->Cell(24, 3.5, number_format((double)$total[2]), 0,0,'R');
		
			$pdf->setXY(97, $pdf->getY() + 4);
			$pdf->Cell(35, 3.5, "Before VAT",0,0,"R");
			$pdf->Cell(11);
			$pdf->Cell(24, 3.5, number_format((double)$total[4]), 0,0,'R');
		
			$pdf->setXY(97, $pdf->getY() + 4);
			$pdf->Cell(35, 3.5, "$_vat_val %             VAT",0,0,"R");
			$pdf->Cell(11);
			$pdf->Cell(24, 3.5, number_format((double)$total[3]), 0,0,'R');

		} else if($_disc <= 0) {
			$total[3] = $_vat_val/100 * $total[1];
		
			$pdf->setXY(97, 176);
			$pdf->Cell(35, 3.5, "Before VAT",0,0,"R");
			$pdf->Cell(11);
			$pdf->Cell(24, 3.5, number_format((double)$total[1]), 0,0,'R');
		
			$pdf->setXY(97, $pdf->getY() + 4);
			$pdf->Cell(35, 3.5, "$_vat_val %             VAT",0,0,"R");
			$pdf->Cell(11);
			$pdf->Cell(24, 3.5, number_format((double)$total[3]), 0,0,'R');
		}
	} else if ($_vat_val == 0) {
		if($_disc > 0) {
			$total[2] = $_disc/100 * $total[1];

			$pdf->setXY(97, 176);
			$pdf->Cell(35, 3.5, "Sub Total",0,0,"R");
			$pdf->Cell(11);
			$pdf->Cell(24, 3.5, number_format((double)$total[1]), 0,0,'R');

			$pdf->setXY(97, $pdf->getY() + 4);
			$pdf->Cell(35, 3.5, "$_disc %             Disc",0,0,"R");
			$pdf->Cell(12);
			$pdf->Cell(24, 3.5, number_format((double)$total[2]), 0,0,'R');
		}
	}

	//Freight charge
	if(!empty($_delivery_freight_charge)) {
		$pdf->setXY(97, 189);
		$pdf->Cell(35, 3.5, "Delivery Cost",0,0,"R");
		$pdf->Cell(11);
		$pdf->Cell(24, 3.5, number_format((double)$_delivery_freight_charge),0,0,"R");

		$pdf->setXY(97, 193);
		$pdf->Cell(35, 3.5, "Grand Total",0,0,"R");
		$pdf->Cell(11);
		$pdf->Cell(24, 3.5, number_format((double)$total[1] + round($total[3]) - round($total[2]) + $_delivery_freight_charge),0,0,"R");
	}

	$counter[0] += $row[1];
}

//TOTAL & TOTAL AMOUNT
$pdf->setXY(130, 184);
$pdf->Cell(14,4, number_format((double)$total[0]), 0, 0,'R');
$pdf->Cell(23,4, number_format((double)$total[1] + round($total[3]) - round($total[2])), 0,0,'R');
?>