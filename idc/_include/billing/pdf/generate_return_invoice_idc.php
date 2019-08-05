<?php
$pdf->setSourceFile(APP_DIR . "_include/billing/template_pdf/" . $tpl_pdf[0]);
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, -2,5.5,0,0);

//Header
$pdf->setFont('Arial', 'B', 15);
$pdf->setXY(4, 5);
$pdf->Cell(100,10, $company[0]);
$pdf->setFont('Arial', '', 9);
$pdf->setXY(4, 13);
$pdf->Cell(170, 3.5, $company[1]);
if(isset($company[2])) {
	$pdf->setXY(4, 17);
	$pdf->Cell(170, 3.5, implode('   ', $company[2]));
}

/*	1	*/	if($page[1] > 1) {
				$pdf->setFont('Arial', 'I', 8);
				$pdf->setXY(33, 28);
				$pdf->Cell(20,6,"page {$page[0]} / {$page[1]}",0,0,'C');
			}
			$pdf->setFont('Arial', '', 10);
			$pdf->setXY(183, 27);
			$pdf->Cell(25,6,$doc_for,0,0,'C');
/*	2	*/	$pdf->setXY(18,38);
			$pdf->Cell(165, 4, $_cus_name);
			$pdf->Cell(0, 4, $_code);
/*	3	*/	$pdf->setXY(18,42);
			$pdf->Cell(165, 4, $_cus_address);
			$pdf->Cell(0, 4, $_return_date);
/*	4	*/	$pdf->setXY(18,46);
			if($cus[0] != '' && $cus[1] != '') $pdf->Cell(125, 4, "Telp : ".$cus[0]."  Fax : ".$cus[1]);
			else if($cus[0] != '' && $cus[1] == '') $pdf->Cell(125, 4, "Telp : ".$cus[0]);
			else if($cus[0] == '' && $cus[1] != '') $pdf->Cell(125, 4, "Fax : ".$cus[1]);
/*	5	*/	$pdf->setXY(18,51);
			$pdf->Cell(165, 4, $_cus_attn);
			$pdf->Cell(0, 4, $_bill_code);
			$pdf->setXY(182,55);
			$pdf->Cell(0, 4, ($cus[5]=='') ? '' : date('d-M-Y', strtotime($cus['5'])));
/*	6	*/	if(!empty($_npwp)) {
				$pdf->setXY(4,59);
				$pdf->Cell(0, 4, 'NPWP:   '.$_npwp);
			}
/*	7	*/	$pdf->setXY(18,64);
			$pdf->Cell(165, 4, $_po_no);
			$pdf->Cell(0, 4, $_po_date);

//Head Total
$i = 0;
pg_result_seek($result, $counter[1]);
if($counter[0] == 0) {
	$pdf->setY(78);
} else {
	$pdf->setXY(4,78);
	$pdf->Cell(131, 4, "Previous balance");
	$pdf->Cell(14, 4, number_format((double)$total[0]),0,0,'R');
	$pdf->Cell(28, 4, number_format((double)$total[1]),0,0,'R');
	$pdf->setY(82);
	$i++;
}
if($counter[0] == $row[2]) {$counter[0]+=$row[1];}

//Body Item
while($items =& fetchRow($result,0)) {
	$pdf->setX(4);
	$pdf->Cell(16, 4, $items[0],0);							// code
	$pdf->Cell(20, 4, substr($items[1],0,8),0); 			// item no
	$pdf->Cell(78, 4, substr($items[2],0,40),0);			// description
	$pdf->Cell(17, 4, number_format($items[3]),0,0,'R'); 	//unit price
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
if($currentDept == 'apotik') {
	$pdf->setFont('Arial', '', 11);
}
$total[4] = $total[1];

if($counter[0] >= $row[2] && $i<=$row[4]) {
	if ($_vat > 0) {
		if($_disc > 0) {
			$total[2] = $_disc/100 * $total[1];
			$total[4] = $total[1] - $total[2];
			$total[3] = $_vat/100 * $total[4];	

			$pdf->setXY(100, 155);
			$pdf->Cell(35, 3.5, "Sub Total",0,0,"R");
			$pdf->Cell(20);
			$pdf->Cell(24, 3.5, number_format((double)$total[1]), 0,0,'R');
		
			$pdf->setXY(100, $pdf->getY() + 4);
			$pdf->Cell(35, 3.5, "$_disc %             Disc",0,0,"R");
			$pdf->Cell(20);
			$pdf->Cell(24, 3.5, number_format((double)$total[2]), 0,0,'R');
		
			$pdf->setXY(100, $pdf->getY() + 4);
			$pdf->Cell(35, 3.5, "Before VAT",0,0,"R");
			$pdf->Cell(20);
			$pdf->Cell(24, 3.5, number_format((double)$total[4]), 0,0,'R');
		
			$pdf->setXY(100, $pdf->getY() + 4);
			$pdf->Cell(35, 3.5, "$_vat %             VAT",0,0,"R");
			$pdf->Cell(20);
			$pdf->Cell(24, 3.5, number_format((double)$total[3]), 0,0,'R');

		} else if($_disc <= 0) {
			$total[3] = $_vat/100 * $total[1];
		
			$pdf->setXY(100, 163);
			$pdf->Cell(35, 3.5, "Before VAT",0,0,"R");
			$pdf->Cell(20);
			$pdf->Cell(24, 3.5, number_format((double)$total[1]), 0,0,'R');
		
			$pdf->setXY(100, $pdf->getY() + 4);
			$pdf->Cell(35, 3.5, "$_vat %             VAT",0,0,"R");
			$pdf->Cell(20);
			$pdf->Cell(24, 3.5, number_format((double)$total[3]), 0,0,'R');
		}
	} else if ($_disc <= 0) {
		if($_vat > 0) {
			$total[2] = $_disc/100 * $total[1];

			$pdf->setXY(100, 163);
			$pdf->Cell(35, 3.5, "Sub Total",0,0,"R");
			$pdf->Cell(20);
			$pdf->Cell(24, 3.5, number_format((double)$total[1]), 0,0,'R');

			$pdf->setXY(100, $pdf->getY() + 4);
			$pdf->Cell(35, 3.5, "$_disc %             Disc",0,0,"R");
			$pdf->Cell(20);
			$pdf->Cell(24, 3.5, number_format((double)$total[2]), 0,0,'R');
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
$pdf->setXY(135, 172);
$pdf->Cell(14,4, number_format((double)$total[0]), 0, 0,'R');
$pdf->Cell(30,4, number_format((double)$total[1] + round($total[3]) - round($total[2])), 0,0,'R');
if($currentDept == 'apotik') {
	$pdf->setFont('Arial', '', 10);
}

/*	1	*/	$pdf->setXY(30,189);
			$pdf->Cell(6, 4, $_delivery_warehouse); 			// ex Whouse
			$pdf->Cell(38);
			$pdf->Cell(6, 4, $_delivery_franco);				// Franco(P/D)
			$pdf->Cell(36);
			$pdf->Cell(36, 4, $_delivery_by);					// Deliverd by
			$pdf->Cell(6, 4, ($_delivery_chk & 1) ? "X":"");	// Freight charge
			$pdf->Cell(23);
			$pdf->Cell(0, 4, ($_delivery_freight_charge <= 0) ? '' :number_format($_delivery_freight_charge));
/*	2	*/	$pdf->setXY(30,194);
			$pdf->Cell(6, 4, ($_payment_chk & 1) ? "X":""); // ex COD
			$pdf->Cell(38);
			$pdf->Cell(6, 4, ($_payment_chk & 2) ? "X":""); // Prepaid
			$pdf->Cell(30);
			$pdf->Cell(6, 4, ($_payment_chk & 4) ? "X":""); // Consignment
			$pdf->Cell(36);
			$pdf->Cell(6, 4, ($_payment_chk & 8) ? "X":""); // FREE/TO/LF/RP/PT
/*	3	*/	$pdf->setXY(41,198);
			$pdf->Cell(6, 4, $_payment_widthin_days, 0, 0,'R'); // within days after
			$pdf->Cell(26);
			$pdf->Cell(15, 4, $_payment_sj_inv_fp_tender); // SJ/Inv/Fp/Tender
			$pdf->Cell(43);
			$pdf->Cell(21, 4, (empty($_payment_closing_on) ? "" : date("j/M/y", strtotime($_payment_closing_on))),0); // Closing on
			$pdf->Cell(6, 4, $_payment_for_the_month_week); // for the Month/Week(M/W)
/*	4	*/	$pdf->setXY(30,203);
			$pdf->Cell(6, 4, ($_payment_chk & 16) ? "X":""); // By Cash
			$pdf->Cell(38);
			$pdf->Cell(6, 4, ($_payment_chk & 32) ? "X":""); // By check
			$pdf->Cell(30);
			$pdf->Cell(6, 4, ($_payment_chk & 64) ? "X":""); // By Transfer
			$pdf->Cell(36);
			$pdf->Cell(6, 4, ($_payment_chk & 128) ? "X":""); // By Giro
			$pdf->Cell(23);
			$pdf->Cell(10,4, (empty($_payment_giro_issue) ? "": date("j/M/Y", strtotime($_payment_giro_issue))));
/*	5	*/	$pdf->setXY(33, 206);
			$pdf->Cell(39, 4, substr($_payment_cash_by, 0, 18)); // cash by
			$pdf->Cell(6);
			$pdf->Cell(30, 4, substr($_payment_check_by, 0, 18));// check by
			$pdf->Cell(6);
			$pdf->Cell(40, 4, substr($_payment_transfer_by, 0, 18));// check by
			$pdf->Cell(28);
			$pdf->Cell(10,4, (empty($_payment_giro_due) ? "": date("j/M/Y", strtotime($_payment_giro_due))));
/*	6	*/	$pdf->setXY(22, 211);
			$pdf->Cell(74, 4, '['.trim($_ship_to).'] '.substr($_ship_name, 0, 48));
			$pdf->setXY(22, 215);
			$pdf->Cell(64, 4, $cus[2]);
/*	7	*/	$pdf->setXY(22,219);
			if($cus[0] != '' && $cus[1] != '') $pdf->Cell(125, 4, "Telp : ".$cus[0]."  Fax : ".$cus[1]);
			else if($cus[0] != '' && $cus[1] == '') $pdf->Cell(125, 4, "Telp : ".$cus[0]);
			else if($cus[0] == '' && $cus[1] != '') $pdf->Cell(125, 4, "Fax : ".$cus[1]);
/*	8	*/	$pdf->setXY(4, 222);
			if($_bank != '') $pdf->Cell(13,4,'Bank : ');
			$pdf->setXY(22, 222);
			$pdf->setLeftMargin(22);
			$pdf->Write(4, $_bank_address);
			$pdf->setLeftMargin(10);
/*	9	*/	$pdf->setXY(22, 234);
			$pdf->MultiCell(170, 3.5, $_remark);
			$pdf->setFont('Arial', 'I', 8);
			$pdf->setY(244);
			if($_paper == 0) {
				$pdf->Cell(198, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time), 0,0,'R');
			} else if($_paper == 1) {
				$pdf->Cell(198, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)." | No. Only", 0,0,'R');
			}
/*	10	*/	$pdf->setFont('Arial', '', 10);
			$pdf->setXY(10, 268);
			$pdf->Cell(36,4,$_signature_by,0,0,"C");
?>