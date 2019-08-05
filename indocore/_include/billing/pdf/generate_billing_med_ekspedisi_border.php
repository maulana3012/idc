<?php
$pdf->setSourceFile(APP_DIR . "_include/billing/template_pdf/" . $tpl_pdf[3]);
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, -2,5.5,0,0);

/*	1	*/	if($page[1] > 1) {
				$pdf->setFont('Arial', 'I', 8);
				$pdf->setXY(9, 36);
				$pdf->Cell(20,6,"page {$page[0]} / {$page[1]}",0,0,'C');
			}
			$pdf->setFont('Arial', '', 10);
/*	2	*/	$pdf->setXY(117,38);
			$pdf->Cell(25, 4, $_inv_date, 0,0,'C');
			$pdf->Cell(35, 4, $_code, 0,0,'C');
			if($_sj_code== '') $pdf->Cell(28, 4, "J". substr($_code,1,1). substr($_code, 2), 0,0,'C');
			else $pdf->Cell(28, 4, $_sj_code, 0,0,'C');
/* 	3	*/	$pdf->setXY(11,56);		// Customer to
			$pdf->Cell(100, 4, $_cus_name);
			$pdf->setXY(11,60);
			$pdf->MultiCell(100, 3.5, $_cus_address);
			if($cus[0] != '' && $cus[1] != '') 		{$pdf->setX(11); $pdf->Cell(90, 4, "Telp : ".$cus[0]."  Fax : ".$cus[1]);}
			else if($cus[0] != '' && $cus[1] == '') {$pdf->setX(11); $pdf->Cell(90, 4, "Telp : ".$cus[0]);}
			else if($cus[0] == '' && $cus[1] != '') {$pdf->setX(11); $pdf->Cell(90, 4, "Fax : ".$cus[1]);}
			$pdf->setXY(22,77);
			$pdf->Cell(100, 4, $_cus_attn);

			$pdf->setXY(115,56);	// Bill to
			$pdf->Cell(95, 4, $_ship_name);
			$pdf->setXY(115,60);
			$pdf->MultiCell(95, 3.5, $cus[2]);
			if($cus[3] != '' && $cus[4] != '') 		{$pdf->setX(115); $pdf->Cell(95, 4, "Telp : ".$cus[3]."  Fax : ".$cus[4]);}
			else if($cus[3] != '' && $cus[4] == '') {$pdf->setX(115); $pdf->Cell(95, 4, "Telp : ".$cus[3]);}
			else if($cus[3] == '' && $cus[4] != '') {$pdf->setX(115); $pdf->Cell(95, 4, "Fax : ".$cus[4]);}
			$pdf->setXY(130,77);
			$pdf->Cell(50, 4, $_po_no,0);
			$pdf->setXY(130,81);
			$pdf->Cell(27, 4, $_po_date,0);
/*	4	*/	$pdf->setXY(11, 218);
			if($_bank != '') $pdf->Cell(13,4,'Bank : ');
			if($_bank == 'BCA1' || $_bank == 'BCA2') $pdf->Cell(28,4,'BCA');
			else if($_bank == 'BII1' || $_bank == 'BII2') $pdf->Cell(28,4,'BII');
			$pdf->setXY(15, 218);
			$pdf->setLeftMargin(23);
			$pdf->Write(4, $_bank_address);
/*	5	*/	$pdf->setXY(43, 194);
			$pdf->Cell(26, 4, $_delivery_by);
			$pdf->Cell(5, 4, ($_delivery_chk & 1) ? "X":"");
			$pdf->Cell(23);
			$pdf->Cell(0, 4, ($_delivery_freight_charge>0) ? 'Rp. '.number_format((double)$_delivery_freight_charge) : '');
/*	6	*/	$pdf->setXY(45,202);
			$pdf->Cell(5, 4, $_payment_widthin_days, 0, 0,'R');
			$pdf->Cell(30);
			$pdf->Cell(0,4, (empty($_payment_giro_due) ? "": date("j/M/Y", strtotime($_payment_giro_due))));
			$pdf->setXY(36,209);
			$pdf->Cell(6, 4, ($_payment_chk & 16) ? "X":"");
			$pdf->Cell(28);
			$pdf->Cell(6, 4, ($_payment_chk & 32) ? "X":"");
			$pdf->Cell(33);
			$pdf->Cell(6, 4, ($_payment_chk & 64) ? "X":"");
			$pdf->Cell(22);
			$pdf->Cell(6, 4, ($_payment_chk & 128) ? "X":"");
/*	7	*/	$pdf->setXY(28, 232);
			$pdf->MultiCell(170, 3.5, $_remark);
/*	8	*/	$pdf->setXY(150, 266);
			$pdf->Cell(40,4,ucfirst($_signature_by),0,0,"C");
/*	9	*/	$pdf->setFont('Arial', 'I', 8);
			$pdf->setY(246);
			if($_type_invoice == 0) {
				$pdf->Cell(180, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time), 0,0,'R');
			} else if($_type_invoice == 1) {
				$pdf->Cell(180, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)." | No. Only", 0,0,'R');
			}
/*	10	*/	if($currentDept == 'dealer') {
				$pdf->setXY(11, 184);
				$pdf->setFont('Arial', 'I', 9);
				$pdf->MultiCell(80, 3.5, "Barang yang sudah dibeli tidak dapat dikembalikan atau ditukar dengan barang lain");
			}
			$pdf->setFont('Arial', '', 10);

//Head Total
$i = 0;
pg_result_seek($result, $counter);
if($counter == 0) {
	$pdf->setY(92);
} else {
	$pdf->setXY(11,92);
	$pdf->Cell(128, 4, "Previous balance");
	$pdf->Cell(10, 4, number_format((double)$total[0]),0,0,'R');
	$pdf->Cell(25, 4, number_format((double)$total[1]),0,0,'R');
	$pdf->setY(96);
	$i++;
}
if($counter == $row[2]) {$counter+=$row[1];}

//Body Item
while($items =& fetchRow($result, 0)) {
	$pdf->setX(11);
	$pdf->Cell(15, 4, $items[0],0);									// code
	$pdf->Cell(25, 4, substr($items[1],0,9),0); 					// item no
	$pdf->Cell(68, 4, substr($items[2],0,37),0);					// description
	$pdf->Cell(20, 4, number_format((double)$items[3]),0,0,'R');	// unit price
	$pdf->Cell(10, 4, number_format((double)$items[4]),0,0,'R');	// qty
	$pdf->Cell(25, 4, number_format((double)$items[5]),0,0,'R');	// amount
	$pdf->Cell(3);
	$pdf->Cell(0, 4, $items[6],0,1);

	$total[0] += $items[4];
	$total[1] += $items[5];
	$i++;
	if($i == $row[0]) {break;}
}
$counter += $i;

//Foot Total
$total[4] = $total[1];
if($counter >= $row[2] && $i<=$row[4]) {
	if ($_vat_val > 0) {

		if($_disc > 0) {
			$total[2] = $_disc/100 * $total[1];
			$total[4] = $total[1] - $total[2];
			$total[3] = $_vat_val/100 * $total[4];	

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
			$pdf->Cell(18, 3.5, "$_vat_val %             VAT",0,0,"R");
			$pdf->Cell(11);
			$pdf->Cell(25, 3.5, number_format((double)$total[3]), 0,0,'R');

		} else if($_disc <= 0) {
			$total[3] = $_vat_val/100 * $total[1];
		
			$pdf->setXY(120, 172);
			$pdf->Cell(18, 3.5, "Before VAT",0,0,"R");
			$pdf->Cell(11);
			$pdf->Cell(25, 3.5, number_format((double)$total[1]), 0,0,'R');
		
			$pdf->setXY(120, $pdf->getY() + 4);
			$pdf->Cell(18, 3.5, "$_vat_val %             VAT",0,0,"R");
			$pdf->Cell(11);
			$pdf->Cell(25, 3.5, number_format((double)$total[3]), 0,0,'R');
		}
	} else if ($_vat_val == 0) {
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

	$counter += $row[1];
}

//TOTAL & TOTAL AMOUNT
$pdf->setXY(139, 179);
$pdf->Cell(10,4, number_format((double)$total[0]), 0, 0,'R');
$pdf->Cell(25,4, number_format((double)$total[1] + round($total[3]) - round($total[2])), 0,0,'R');
?>