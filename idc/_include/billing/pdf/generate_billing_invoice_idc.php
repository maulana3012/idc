<?php
$pdf->setSourceFile(APP_DIR . "_include/billing/template_pdf/" . $tpl_pdf[0]);
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, -2,5.5,0,0);

//Header
$pdf->setFont('Arial', 'B', 15);
$pdf->setXY(9, 8);
$pdf->Cell(100,10, $company[0]);
$pdf->setFont('Arial', '', 9);
$pdf->setXY(9, 17);
$pdf->Cell(170, 3.5, $company[1]);
if(isset($company[2])) {
	$pdf->setXY(9, 21);
	$pdf->Cell(170, 3.5, implode('   ', $company[2]));
}

/* 1. */	if($page[1] > 1) {
				$pdf->setFont('Arial', 'I', 8);
				$pdf->setXY(25, 32);
				$pdf->Cell(20,6,"page {$page[0]} / {$page[1]}",0,0,'C');
			}
			$pdf->setFont('Arial', '', 10);
			$pdf->setXY(173, 32);
			$pdf->Cell(27,6, "CUSTOMER",0,0,'C');
/* 2. */	$pdf->setXY(22,41);
			$pdf->Cell(150, 4, $_cus_name);
			$pdf->setFont('Arial', '', 11);
				$pdf->Cell(0, 4, $_code);
			$pdf->setFont('Arial', '', 10);
			$pdf->setXY(172,45);
			$pdf->Cell(0, 4, $_inv_date);
			$pdf->setXY(22,45);
			$pdf->Multicell(130, 4, $_cus_address);
/* 3. */	$pdf->setX(22);
			if($cus[0] != '' && $cus[1] != '') $pdf->Cell(150, 4, "Telp : ".$cus[0]."  Fax : ".$cus[1]);
			else if($cus[0] != '' && $cus[1] == '') $pdf->Cell(150, 4, "Telp : ".$cus[0]);
			else if($cus[0] == '' && $cus[1] != '') $pdf->Cell(150, 4, "Fax : ".$cus[1]);
		$pdf->setXY(172,53);
			if($_sj_code== '') $pdf->Cell(0, 4, "J". substr($_code,1,1). substr($_code, 2));
			else $pdf->Cell(0, 4, $_sj_code);
/* 4. */	$pdf->setXY(22,57);
			$pdf->Cell(150, 4, $_cus_attn);
			if(empty($_sj_date)) $pdf->Cell(0, 4, date("j-M-Y", strtotime($_inv_date)));
			else $pdf->Cell(0, 4, date("j-M-Y", strtotime($_sj_date)));
/* 5. */	if(!empty($_cus_npwp)) {
				$pdf->setXY(9,61);
				$pdf->Cell(0, 4, "NPWP  $_cus_npwp");
			}
/* 7. */	$pdf->setXY(22,68);
			$pdf->Cell(150, 4, $_po_no);
			$pdf->Cell(25, 4, $_po_date);
/* 8. */	if(!empty($cus[5])) {
			$pdf->setXY(9,71);
			$pdf->Cell(150, 4, 'Faktur Pajak No. '. $cus[5]);
		}

//Head Total
$i = 0;
pg_result_seek($result, $counter[1]);
if($counter[0] == 0) {
	$pdf->setY(81);
} else {
	$pdf->setXY(8,81);
	$pdf->Cell(123, 4, "Previous balance");
	$pdf->Cell(10, 4, number_format((double)$total[0]),0,0,'R');
	$pdf->Cell(27, 4, number_format((double)$total[1]),0,0,'R');
	$pdf->setY(85);
	$i++;
}
if($counter[0] == $row[2]) {$counter[0]+=$row[1];}

//Body Item
while($items =& fetchRow($result, 0)) {
	$pdf->setX(8);
	$pdf->Cell(14, 4, $items[0],0);									// code
	$pdf->Cell(22, 4, substr($items[1],0,10),0); 					// item no
	$pdf->Cell(72, 4, substr($items[2],0,35),0);					// description
	$pdf->Cell(15, 4, number_format((double)$items[3]),0,0,'R');	// unit price
	$pdf->Cell(10, 4, number_format((double)$items[4]),0,0,'R');	// qty
	$pdf->Cell(27, 4, number_format((double)$items[5]),0,0,'R');	// amount
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
$pdf->setFont('Arial', '', 11);
$total[4] = $total[1];
if($counter[0] >= $row[2] && $i<=$row[4]) {
	if (substr($_code, 0, 2) == "IO" || substr($_code, 0, 2) == "IP") {

		if($_disc > 0) {
			$total[2] = $_disc/100 * $total[1];
			$total[4] = $total[1] - $total[2];
			$total[3] = $_vat_val/100 * $total[4];	

			$pdf->setXY(96, 153);
			$pdf->Cell(35, 3.5, "Sub Total",0,0,"R");
			$pdf->Cell(10);
			$pdf->Cell(27, 3.5, number_format((double)$total[1]), 0,0,'R');
		
			$pdf->setXY(96, $pdf->getY()+4);
			$pdf->Cell(35, 3.5, "$_disc % Disc",0,0,"R");
			$pdf->Cell(10);
			$pdf->Cell(27, 3.5, number_format((double)$total[2]), 0,0,'R');
		
			$pdf->setXY(96, $pdf->getY()+4);
			$pdf->Cell(35, 3.5, "Before VAT",0,0,"R");
			$pdf->Cell(10);
			$pdf->Cell(27, 3.5, number_format((double)$total[4]), 0,0,'R');
		
			$pdf->setXY(96, $pdf->getY()+4);
			$pdf->Cell(35, 3.5, "$_vat_val % VAT",0,0,"R");
			$pdf->Cell(10);
			$pdf->Cell(27, 3.5, number_format((double)$total[3]), 0,0,'R');

		} else if($_disc <= 0) {
			$total[3] = $_vat_val/100 * $total[1];
		
			$pdf->setXY(96, 161);
			$pdf->Cell(35, 3.5, "Before VAT",0,0,"R");
			$pdf->Cell(10);
			$pdf->Cell(27, 3.5, number_format((double)$total[1]), 0,0,'R');
		
			$pdf->setXY(96, $pdf->getY()+4);
			$pdf->Cell(35, 3.5, "$_vat_val % VAT",0,0,"R");
			$pdf->Cell(10);
			$pdf->Cell(27, 3.5, number_format((double)$total[3]), 0,0,'R');
		}
	} else if (substr($_code, 0, 2) == "IX" || substr($_code, 0, 2) == "MN") {
		if($_disc > 0) {
			$total[2] = $_disc/100 * $total[1];

			$pdf->setXY(96, 161);
			$pdf->Cell(35, 3.5, "Sub Total",0,0,"R");
			$pdf->Cell(10);
			$pdf->Cell(27, 3.5, number_format((double)$total[1]), 0,0,'R');

			$pdf->setXY(96, $pdf->getY()+4);
			$pdf->Cell(35, 3.5, "$_disc % Disc",0,0,"R");
			$pdf->Cell(10);
			$pdf->Cell(27, 3.5, number_format((double)$total[2]), 0,0,'R');
		}
	}

	//Freight charge
	if(!empty($_delivery_freight_charge)) {
		$pdf->setXY(96, 175);
		$pdf->Cell(35, 3.5, "Delivery Cost",0,0,"R");
		$pdf->Cell(10);
		$pdf->Cell(27, 3.5, number_format((double)$_delivery_freight_charge),0,0,"R");

		$pdf->setXY(96, $pdf->getY()+4);
		$pdf->Cell(35, 3.5, "Grand Total",0,0,"R");
		$pdf->Cell(10);
		$pdf->Cell(27, 3.5, number_format((double)$total[1] + round($total[3]) - round($total[2]) + $_delivery_freight_charge),0,0,"R");
	}

	$counter[0] += $row[1];
}

//TOTAL & TOTAL AMOUNT
$pdf->setFont('Arial', '', 12);
$pdf->setXY(131, 169.5);
$pdf->Cell(10,4, number_format((double)$total[0]), 0, 0,'R');
$pdf->Cell(27,4, number_format((double)$total[1] + round($total[3]) - round($total[2])), 0,0,'R');

$pdf->setFont('Arial', '', 10);
/*	1	*/	$pdf->setXY(30,187);
			$pdf->Cell(6, 4, $_delivery_warehouse);			// ex Whouse
			$pdf->Cell(40);
			$pdf->Cell(6, 4, $_delivery_franco);			// Franco(P/D)
			$pdf->Cell(40);
			$pdf->Cell(33, 4, $_delivery_by);				// Deliverd by
			$pdf->Cell(6, 4, ($_delivery_chk & 1) ? "X":"");// Freight charge :
			$pdf->Cell(23);
			$pdf->Cell(0, 4, ($_delivery_freight_charge>0) ? 'Rp. '.number_format((double)$_delivery_freight_charge) : '');
/*	2	*/	$pdf->setXY(30,$pdf->getY()+4);
			$pdf->Cell(6, 4, ($_payment_chk & 1) ? "X":""); // ex COD
			$pdf->Cell(40);
			$pdf->Cell(6, 4, ($_payment_chk & 2) ? "X":""); // Prepaid
			$pdf->Cell(32);
			$pdf->Cell(6, 4, ($_payment_chk & 4) ? "X":""); // Consignment
			$pdf->Cell(35);
			$pdf->Cell(6, 4, ($_payment_chk & 8) ? "X":""); // FREE/TO/LF/RP/PT
/*	3	*/	$pdf->setXY(41,$pdf->getY()+4.5);
			$pdf->Cell(6, 4, $_payment_widthin_days, 0, 0,'R'); // within days after
			$pdf->Cell(28);
			$pdf->Cell(15, 4, $_payment_sj_inv_fp_tender); // SJ/Inv/Fp/Tender
			$pdf->Cell(45);
			$pdf->Cell(19, 4, (empty($_payment_closing_on) ? "" : date("j/M/y", strtotime($_payment_closing_on))),0); // Closing on
			$pdf->Cell(6, 4, $_payment_for_the_month_week); // for the Month/Week(M/W)
/*	4	*/	$pdf->setXY(30,$pdf->getY()+4);
			$pdf->Cell(6, 4, ($_payment_chk & 16) ? "X":""); // By Cash
			$pdf->Cell(40);
			$pdf->Cell(6, 4, ($_payment_chk & 32) ? "X":""); // By check
			$pdf->Cell(32);
			$pdf->Cell(6, 4, ($_payment_chk & 64) ? "X":""); // By Transfer
			$pdf->Cell(35);
			$pdf->Cell(6, 4, ($_payment_chk & 128) ? "X":""); // By Giro
			$pdf->Cell(20);
			$pdf->Cell(10,4, (empty($_payment_giro_issue) ? "": date("j/M/Y", strtotime($_payment_giro_issue))));
/*	5	*/	$pdf->setXY(33, $pdf->getY()+5);
			$pdf->Cell(41, 4, substr($_payment_cash_by, 0, 18)); // cash by
			$pdf->Cell(6);
			$pdf->Cell(30, 4, substr($_payment_check_by, 0, 18));// check by
			$pdf->Cell(6);
			$pdf->Cell(40, 4, substr($_payment_transfer_by, 0, 18));// check by
			$pdf->Cell(25);
			$pdf->Cell(10,4, (empty($_payment_giro_due) ? "": date("j/M/Y", strtotime($_payment_giro_due))));
/*	6	*/	$pdf->setXY(24,$pdf->getY()+3.5);
			$pdf->Cell(74, 4, '['.trim($_ship_to).'] '.substr($_ship_name, 0, 48));
			$pdf->setXY(24,$pdf->getY()+4);
			$ptn = array("/(['\"])/", "/[\r\n][\s]+/");
			$rpm = array("\\1", " ");
			$pdf->MultiCell(170, 4, preg_replace($ptn, $rpm, $cus[2]));
/*	7	*/	$pdf->setXY(9, 220);
			if($_bank != '') $pdf->Cell(13,4,'Bank');
			if($_bank == 'BCA1' || $_bank == 'BCA2') $pdf->Cell(28,4,'BCA');
			else if($_bank == 'BII1' || $_bank == 'BII2') $pdf->Cell(28,4,'BII');
/*	8	*/	$pdf->setXY(24, 220);
			$pdf->setLeftMargin(24);
			$pdf->Write(4, $_bank_address);
			$pdf->setLeftMargin(10);
/*	9	*/	$pdf->setXY(24, 232);
			$pdf->MultiCell(170, 3.5, $_remark);
/*	10	*/	$pdf->setFont('Arial', 'I', 8);
			$pdf->setY(240);
			if($_type_invoice == 0) {
				$pdf->Cell(192, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time), 0,0,'R');
			} else if($_type_invoice == 1) {
				$pdf->Cell(192, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)." | No. Only", 0,0,'R');
			}
/*	11	*/	$pdf->setFont('Arial', '', 10);
			if($_paper_format == "A") {
				$pdf->setXY(9, 263);
				$pdf->Cell(34,4,ucfirst($_signature_by),0,0,"C");
			} else { 
				$pdf->setXY(165, 263);
				$pdf->Cell(34,4,ucfirst($_signature_by),0,0,"C");
			}
/*	12	*/	#if($currentDept == 'dealer') {
			$pdf->setXY(9, 175);
			$pdf->setFont('Arial', 'I', 9);
			$pdf->MultiCell(80, 3.5, "Barang yang sudah dibeli tidak dapat dikembalikan atau ditukar dengan barang lain");

			$pdf->setXY(80, 252);
			$pdf->setFont('Arial', 'B', 12);
			$pdf->Cell(55, 6, "P E R H A T I A N", 1,0,'C');
			$pdf->setXY(80, 260);
			$pdf->setFont('Arial', '', 10);
			$pdf->MultiCell(55, 5, "Tidak Menerima Complain Selisih Barang Setelah 7 Hari dari Tanggal Terima Barang", 1,1);
			$pdf->setFont('Arial', '', 9);
			#}
?>