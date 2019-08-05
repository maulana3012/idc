<?php
$pdf->setSourceFile(APP_DIR . "_include/billing/template_pdf/" . $tpl_pdf[0]);
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, -2,5.5,0,0);

//Header
$pdf->setFont('Arial', 'B', 15);
$pdf->setXY(4, 8);
$pdf->Cell(100,10, $company[0]);
$pdf->setFont('Arial', '', 9);
$pdf->setXY(4, 17);
$pdf->Cell(170, 3.5, $company[1]);
if(isset($company[2])) {
	$pdf->setXY(4, 21);
	$pdf->Cell(170, 3.5, implode('   ', $company[2]));
}

/* 1. */	if($page[1] > 1) {
				$pdf->setFont('Arial', 'I', 8);
				$pdf->setXY(20, 28);
				$pdf->Cell(20,6,"page {$page[0]} / {$page[1]}",0,0,'C');
			}
			$pdf->setFont('Arial', '', 10);
			$pdf->setXY(183, 28);
			$pdf->Cell(25,6, "CUSTOMER",0,0,'C');
/* 2. */	$pdf->setXY(18,38);
			$pdf->Cell(160, 4, $_cus_name);
			if($currentDept == 'apotik') {
				$pdf->setFont('Arial', '', 11);
				$pdf->Cell(0, 4, $_code);
				$pdf->setFont('Arial', '', 10);
			} else {
				$pdf->Cell(0, 4, $_code);
			}
			$pdf->setXY(18,42);
			$pdf->Cell(160, 4, $_cus_address);
			$pdf->Cell(0, 4, $_inv_date);
/* 3. */	$pdf->setXY(18,46);
			if($cus[0] != '' && $cus[1] != '') $pdf->Cell(160, 4, "Telp : ".$cus[0]."  Fax : ".$cus[1]);
			else if($cus[0] != '' && $cus[1] == '') $pdf->Cell(160, 4, "Telp : ".$cus[0]);
			else if($cus[0] == '' && $cus[1] != '') $pdf->Cell(160, 4, "Fax : ".$cus[1]);
/* 4. */	$pdf->setXY(18,51);
			$pdf->Cell(160, 4, $_cus_attn);
			if($_sj_code== '') $pdf->Cell(0, 4, "J". substr($_code,1,1). substr($_code, 2));
			else $pdf->Cell(0, 4, $_sj_code);
/* 5. */	$pdf->setXY(178,55);
			if(empty($_sj_date)) $pdf->Cell(0, 4, date("j-M-Y", strtotime($_inv_date)));
			else $pdf->Cell(0, 4, date("j-M-Y", strtotime($_sj_date)));
/* 6. */	if(!empty($_cus_npwp)) {
				$pdf->setXY(4,55);
				$pdf->Cell(14, 4, "NPWP:  $_cus_npwp");
			}
/* 7. */	$pdf->setXY(18,64);
			$pdf->Cell(160, 4, $_po_no);
			$pdf->Cell(25, 4, $_po_date);

//Head Total
$i = 0;
pg_result_seek($result, $counter[1]);
if($counter[0] == 0) {
	$pdf->setY(78);
} else {
	$pdf->setXY(4,78);
	$pdf->Cell(131, 4, "Previous balance");
	$pdf->Cell(14, 4, number_format((double)$total[0]),0,0,'R');
	$pdf->Cell(30, 4, number_format((double)$total[1]),0,0,'R');
	$pdf->setY(82);
	$i++;
}
if($counter[0] == $row[2]) {$counter[0]+=$row[1];}

//Body Item
while($items =& fetchRow($result, 0)) {
	$pdf->setX(4);
	$pdf->Cell(16, 4, $items[0],0);									// code
	$pdf->Cell(20, 4, substr($items[1],0,8),0); 					// item no
	$pdf->Cell(78, 4, substr($items[2],0,40),0);					// description
	$pdf->Cell(17, 4, number_format((double)$items[3]),0,0,'R');	// unit price
	$pdf->Cell(14, 4, number_format((double)$items[4]),0,0,'R');	// qty
	$pdf->Cell(30, 4, number_format((double)$items[5]),0,0,'R');	// amount
	$pdf->Cell(4);
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
	if (substr($_code, 0, 2) == "IO" || substr($_code, 0, 2) == "IP") {

		if($_disc > 0) {
			$total[2] = $_disc/100 * $total[1];
			$total[4] = $total[1] - $total[2];
			$total[3] = $_vat_val/100 * $total[4];	

			$pdf->setXY(100, 158);
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
			$pdf->Cell(35, 3.5, "$_vat_val %             VAT",0,0,"R");
			$pdf->Cell(20);
			$pdf->Cell(24, 3.5, number_format((double)$total[3]), 0,0,'R');

		} else if($_disc <= 0) {
			$total[3] = $_vat_val/100 * $total[1];
		
			$pdf->setXY(100, 166);
			$pdf->Cell(35, 3.5, "Before VAT",0,0,"R");
			$pdf->Cell(20);
			$pdf->Cell(24, 3.5, number_format((double)$total[1]), 0,0,'R');
		
			$pdf->setXY(100, $pdf->getY() + 4);
			$pdf->Cell(35, 3.5, "$_vat_val %             VAT",0,0,"R");
			$pdf->Cell(20);
			$pdf->Cell(24, 3.5, number_format((double)$total[3]), 0,0,'R');
		}
	} else if (substr($_code, 0, 2) == "IX" || substr($_code, 0, 2) == "MN") {
		if($_disc > 0) {
			$total[2] = $_disc/100 * $total[1];

			$pdf->setXY(100, 166);
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
		$pdf->setXY(100, 180);
		$pdf->Cell(35, 3.5, "Delivery Cost",0,0,"R");
		$pdf->Cell(20);
		$pdf->Cell(24, 3.5, number_format((double)$_delivery_freight_charge),0,0,"R");

		$pdf->setXY(100, 184);
		$pdf->Cell(35, 3.5, "Grand Total",0,0,"R");
		$pdf->Cell(20);
		$pdf->Cell(24, 3.5, number_format((double)$total[1] + round($total[3]) - round($total[2]) + $_delivery_freight_charge),0,0,"R");
	}

	$counter[0] += $row[1];
}

//TOTAL & TOTAL AMOUNT
$pdf->setXY(135, 175);
$pdf->Cell(14,4, number_format((double)$total[0]), 0, 0,'R');
$pdf->Cell(30,4, number_format((double)$total[1] + round($total[3]) - round($total[2])), 0,0,'R');
if($currentDept == 'apotik') {
	$pdf->setFont('Arial', '', 10);
}

/*	1	*/	$pdf->setXY(30,192);
			$pdf->Cell(6, 4, $_delivery_warehouse);			// ex Whouse
			$pdf->Cell(38);
			$pdf->Cell(6, 4, $_delivery_franco);			// Franco(P/D)
			$pdf->Cell(36);
			$pdf->Cell(36, 4, $_delivery_by);				// Deliverd by
			$pdf->Cell(6, 4, ($_delivery_chk & 1) ? "X":"");// Freight charge :
			$pdf->Cell(23);
			$pdf->Cell(0, 4, ($_delivery_freight_charge>0) ? 'Rp. '.number_format((double)$_delivery_freight_charge) : '');
/*	2	*/	$pdf->setXY(30,197);
			$pdf->Cell(6, 4, ($_payment_chk & 1) ? "X":""); // ex COD
			$pdf->Cell(38);
			$pdf->Cell(6, 4, ($_payment_chk & 2) ? "X":""); // Prepaid
			$pdf->Cell(30);
			$pdf->Cell(6, 4, ($_payment_chk & 4) ? "X":""); // Consignment
			$pdf->Cell(36);
			$pdf->Cell(6, 4, ($_payment_chk & 8) ? "X":""); // FREE/TO/LF/RP/PT
/*	3	*/	$pdf->setXY(41,202);
			$pdf->Cell(6, 4, $_payment_widthin_days, 0, 0,'R'); // within days after
			$pdf->Cell(26);
			$pdf->Cell(15, 4, $_payment_sj_inv_fp_tender); // SJ/Inv/Fp/Tender
			$pdf->Cell(43);
			$pdf->Cell(21, 4, (empty($_payment_closing_on) ? "" : date("j/M/y", strtotime($_payment_closing_on))),0); // Closing on
			$pdf->Cell(6, 4, $_payment_for_the_month_week); // for the Month/Week(M/W)
/*	4	*/	$pdf->setXY(30,206);
			$pdf->Cell(6, 4, ($_payment_chk & 16) ? "X":""); // By Cash
			$pdf->Cell(38);
			$pdf->Cell(6, 4, ($_payment_chk & 32) ? "X":""); // By check
			$pdf->Cell(30);
			$pdf->Cell(6, 4, ($_payment_chk & 64) ? "X":""); // By Transfer
			$pdf->Cell(36);
			$pdf->Cell(6, 4, ($_payment_chk & 128) ? "X":""); // By Giro
			$pdf->Cell(23);
			$pdf->Cell(10,4, (empty($_payment_giro_issue) ? "": date("j/M/Y", strtotime($_payment_giro_issue))));
/*	5	*/	$pdf->setXY(33, 210);
			$pdf->Cell(41, 4, substr($_payment_cash_by, 0, 18)); // cash by
			$pdf->Cell(6);
			$pdf->Cell(30, 4, substr($_payment_check_by, 0, 18));// check by
			$pdf->Cell(6);
			$pdf->Cell(40, 4, substr($_payment_transfer_by, 0, 18));// check by
			$pdf->Cell(28);
			$pdf->Cell(10,4, (empty($_payment_giro_due) ? "": date("j/M/Y", strtotime($_payment_giro_due))));
/*	6	*/	$pdf->setXY(17, 215);
			$pdf->Cell(74, 4, '['.trim($_ship_to).'] '.substr($_ship_name, 0, 48));
			$pdf->setXY(17, 220);
			$pdf->Cell(64, 4, $cus[2]);
/*	7	*/	$pdf->setXY(4, 224);
			if($_bank != '') $pdf->Cell(13,4,'Bank : ');
			if($_bank == 'BCA1' || $_bank == 'BCA2') $pdf->Cell(28,4,'BCA');
			else if($_bank == 'BII1' || $_bank == 'BII2') $pdf->Cell(28,4,'BII');
/*	8	*/	$pdf->setXY(17, 224);
			$pdf->setLeftMargin(17);
			$pdf->Write(4, $_bank_address);
			$pdf->setLeftMargin(10);
/*	9	*/	if($_dept == 'A' && $_type_invoice==0) {
				$pdf->setXY(22, 237);
				$pdf->setFont('Arial', 'B', 9);
				$pdf->MultiCell(170, 3.5, "Pengiriman dengan ekspedisi, harap Surat Jalan dicap, ditandatangani dan diberi nama jelas kemudian di fax ke $company setelah barang diterima.");
				$pdf->setFont('Arial', '', 9);
				$pdf->setXY(22, 244);
				$pdf->MultiCell(170, 3.5, $_remark);
			} else {
				$pdf->setXY(22, 237);
				$pdf->MultiCell(170, 3.5, $_remark);
			}
/*	10	*/	$pdf->setFont('Arial', 'I', 8);
			$pdf->setY(248);
			if($_type_invoice == 0) {
				$pdf->Cell(198, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time), 0,0,'R');
			} else if($_type_invoice == 1) {
				$pdf->Cell(198, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)." | No. Only", 0,0,'R');
			}
/*	11	*/	$pdf->setFont('Arial', '', 9);
			if($_paper_format == "A") {
				$pdf->setXY(12, 271);
				$pdf->Cell(36,4,ucfirst($_signature_by),0,0,"C");
			} else { 
				$pdf->setXY(139, 271);
				$pdf->Cell(44,4,ucfirst($_signature_by),0,0,"C");
			}
/*	12	*/	if($currentDept == 'dealer') {
				$pdf->setXY(5, 180);
				$pdf->setFont('Arial', 'I', 9);
				$pdf->MultiCell(80, 3.5, "Barang yang sudah dibeli tidak dapat dikembalikan atau ditukar dengan barang lain");
				$pdf->setFont('Arial', '', 9);
			}
?>