<?php
$pdf->setSourceFile(APP_DIR . "_include/order/template_pdf/" . $tpl_pdf[0]);
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, 0,0,0,0);

//Header
$pdf->setFont('Arial', 'B', 15);
$pdf->setXY(10, 20);
$pdf->Cell(100,10, $company[0]);
$pdf->setFont('Arial', '', 9);
$pdf->setXY(10, 29);
$pdf->Cell(170, 3.5, $company[1]);
if(isset($company[2])) {
	$pdf->setXY(10, 33);
	$pdf->Cell(170, 3.5, implode('   ', $company[2]));
}

/*	1	*/	$pdf->setXY(175,43);
			$pdf->Cell(25,6,"CUSTOMER",0,0,'C');
			$pdf->setXY(40,43);
			$pdf->Cell(25,6,"page {$page[0]} / {$page[1]}",0,0,'C');
/*	2	*/	$pdf->setXY(30,52);
			$pdf->Cell(105, 4, $_bill_to_attn);
			$pdf->Cell(42, 4, $_bill_to);
			$pdf->Cell(0, 4, $_code);
/*	3	*/	$pdf->setXY(30,56);
			$pdf->Cell(147, 4, $_bill_to_address);
			$pdf->Cell(0, 4, $_po_date);
/*	4	*/	$pdf->setXY(30,63);
			$pdf->Cell(105, 4, $_cus_to_attn);
			$pdf->Cell(42, 4, $_cus_to);
			$pdf->Cell(0, 4, $_ord_code);
/*	5	*/	$pdf->setXY(135,67);
			$pdf->Cell(42, 4, $_received_by);
			$pdf->Cell(0, 4, $_ord_date);
/*	6	*/	$pdf->setXY(30,71);
			$pdf->Cell(147, 4, $_po_no);
			$pdf->Cell(0, 4, $_po_date);

//Head Total
$i = 0;
pg_result_seek($cus_res, $counter);
if($counter == 0) {
	$pdf->setY(83);
} else {
	$pdf->setXY(10, 83);
	$pdf->Cell(116,4, 'Previous balance',0);
	$pdf->Cell(19, 4, number_format((double)$total[0]), 0,0,'R');
	$pdf->Cell(25, 4, number_format((double)$total[1]), 0,0,'R');
	$pdf->setY(87);
	$i++;
}
if($counter == $row[2]) {$counter+=$row[1];}

//Body Item
while($items =& fetchRow($cus_res, 0)) {
	$pdf->Cell(14, 3.5, $items[0],0);								// code
	$pdf->Cell(23, 3.5, substr($items[1],0,10),0);					// item no
	$pdf->Cell(68, 3.5, substr($items[2],0,35),0);					// description
	$pdf->Cell(20, 3.5, number_format((double)$items[3]),0,0,'R');	// unit price
	$pdf->Cell(10, 3.5, number_format((double)$items[4]),0,0,'R');	// qty
	$pdf->Cell(25, 3.5, number_format((double)$items[5]),0,0,'R');	// amount
	$pdf->Cell(8);
	$pdf->Cell(0, 3.5, $items[7],0,1);

	$total[0]	+= $items[4];
	$total[1]	+= $items[5];
	$_delivery_date = $items[7];
	$i++;
	if($i == $row[0]) {break;}
}
$counter += $i;

/*	0	*/	$amount_vat = $_vat/100 * $total[1];
			$pdf->setXY(145,190);
			$pdf->Cell(25, 3.5, number_format((double)$total[1]),0,0,'R');
			$pdf->setXY(145,194);
			$pdf->Cell(25, 3.5, number_format((double)$amount_vat),0,0,'R');
			$pdf->setXY(135,198);
			$pdf->Cell(10, 3.5, number_format((double)$total[0]),0,0,'R');
			$pdf->Cell(25, 3.5, number_format((double)$total[1] + $amount_vat),0,0,'R');
/*	1	*/	$pdf->setXY(34,205);
			$pdf->Cell(6, 4, ($_delivery_chk & 1) ? "X":""); 	// ex Whouse
			$pdf->Cell(36);
			$pdf->Cell(6, 4, ($_delivery_chk & 2) ? "X":""); 	// Franco(P/D)
			$pdf->Cell(34);
			$pdf->Cell(30, 4, $_delivery_by);				 	// Deliverd by
			$pdf->Cell(6, 4, ($_delivery_chk & 4) ? "X":""); 	// Freight charge :
			$pdf->Cell(25);
			$pdf->Cell(0, 4, number_format((double)$_delivery_freight_charge));
/*	2	*/	$pdf->setXY(34,209);
			$pdf->Cell(6, 4, ($_payment_chk & 1) ? "X":""); 	// ex COD
			$pdf->Cell(36);
			$pdf->Cell(6, 4, ($_payment_chk & 2) ? "X":""); 	// Prepaid
			$pdf->Cell(34);
			$pdf->Cell(6, 4, ($_payment_chk & 4) ? "X":""); 	// Consignment
			$pdf->Cell(24);
			$pdf->Cell(6, 4, ($_payment_chk & 8) ? "X":""); 	// FREE/TO/LF/RP/PT
/*	3	*/	$pdf->setXY(44,213);
			$pdf->Cell(6, 4, $_payment_widthin_days, 0, 0,'R'); // within days after
			$pdf->Cell(26);
			$pdf->Cell(6, 4, ($_payment_chk & 16) ? "X":"");	// SJ/Inv/Fp/Tender
			$pdf->Cell(47);
			$pdf->Cell(17, 4, (empty($_payment_closing_on) ? "" : date("j-M,y", strtotime($_payment_closing_on))),0); // Closing on
			$pdf->Cell(6, 4, ($_payment_chk & 32) ? "X":""); 	// for the Month/Week(M/W)
/*	4	*/	$pdf->setXY(34,217);
			$pdf->Cell(6, 4, ($_payment_chk & 64) ? "X":""); 			// By Cash
			$pdf->Cell(36);
			$pdf->Cell(6, 4, ($_payment_chk & 128) ? "X":""); 			// By check
			$pdf->Cell(34);
			$pdf->Cell(6, 4, ($_payment_chk & 256) ? "X":"");			// By Transfer
			$pdf->Cell(24);
			$pdf->Cell(6, 4, ($_payment_chk & 512) ? "X":""); 			// By Giro
/*	5	*/	$pdf->setXY(39, 220);
			$pdf->Cell(43, 4, substr($_payment_cash_by, 0, 18)); 		// cash by
			$pdf->Cell(40, 4, substr($_payment_check_by, 0, 18));		// check by
			$pdf->Cell(35, 4, substr($_payment_transfer_by, 0, 18));	// transfer by
			$pdf->Cell(40, 4, substr($_payment_giro_by, 0, 18));		// giro by
/*	6	*/	$pdf->setXY(28, 227);
			$pdf->MultiCell(180, 4, "[". trim($_ship_to) . "] " .  $_ship_to_attn);
			$pdf->setXY(28, 231);
			$pdf->MultiCell(180, 4, $_ship_to_address);
/*	6	*/	$pdf->setXY(10, 239);
			$pdf->MultiCell(187, 4, $_remark);							// Remark
/*	7	*/	$pdf->setXY(20, 270);
			$pdf->Cell(30, 4, $_sign_by, 0, 0, 'C');					// Signed By
/*	7	*/	$pdf->setFont('Arial', 'I', 8);
			$pdf->setY(248);											// Information
			if($_type_invoice == 0) {
				$pdf->Cell(188, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time), 0,0,'R');
			} else if($_type_invoice == 1) {
				$pdf->Cell(188, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)." | No. Only", 0,0,'R');
			}
?>