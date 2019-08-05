<?php
$pdf->addPage();

$pdf->setFont('Arial', 'B', 15);
$pdf->setXY(8,32);
$pdf->Cell(25, 4, $title);

$pdf->setFont('Arial', '', 10);
/*	1	*/	if($page[1] > 1) {
				$pdf->setFont('Arial', 'I', 8);
				$pdf->setXY(8, 35);
				$pdf->Cell(20,6,"page {$page[0]} / {$page[1]}");
			}
			$pdf->setFont('Arial', '', 10);
/*	2	*/	$pdf->setXY(90,37);
			$pdf->Cell(32, 4, $_inv_date,0,0,'C');
			$pdf->Cell(42, 4, $_code,0,0,'C');
			if($_sj_code== '') $pdf->Cell(40, 4, "J". substr($_code,1,1). substr($_code, 2),0,0,'C');
			else $pdf->Cell(40, 4, $_sj_code,0,0,'C');
/* 	3	*/	$pdf->setXY(12,54);		// Customer to
			$pdf->Cell(100, 4, $_cus_name);
			$pdf->setXY(12,58);
			$pdf->MultiCell(95, 3.5, $_cus_address);
			if($cus[0] != '' && $cus[1] != '') 		{$pdf->setX(12); $pdf->Cell(90, 4, "Telp : ".$cus[0]."  Fax : ".$cus[1]);}
			else if($cus[0] != '' && $cus[1] == '') {$pdf->setX(12); $pdf->Cell(90, 4, "Telp : ".$cus[0]);}
			else if($cus[0] == '' && $cus[1] != '') {$pdf->setX(12); $pdf->Cell(90, 4, "Fax : ".$cus[1]);}
			$pdf->setXY(25,77);
			$pdf->Cell(100, 4, $_cus_attn);
			/*if(!empty($_cus_npwp)) {
				$pdf->setXY(12,82);
				$pdf->Cell(90, 4, "NPWP:  $_cus_npwp");
			}*/
			$pdf->setXY(114,54);	// Bill to
			$pdf->Cell(95, 4, $_ship_name);
			$pdf->setXY(114,58);
			$pdf->MultiCell(95, 3.5, $cus[2]);
			if($cus[3] != '' && $cus[4] != '') 		{$pdf->setX(114); $pdf->Cell(95, 4, "Telp : ".$cus[3]."  Fax : ".$cus[4]);}
			else if($cus[3] != '' && $cus[4] == '') {$pdf->setX(114); $pdf->Cell(95, 4, "Telp : ".$cus[3]);}
			else if($cus[3] == '' && $cus[4] != '') {$pdf->setX(114); $pdf->Cell(95, 4, "Fax : ".$cus[4]);}
			$pdf->setXY(140,77);
			$pdf->Cell(15, 4, $_po_date,0);
			$pdf->setXY(140,82);
			$pdf->Cell(40, 4, $_po_no,0);
/*	4	*/	/*$pdf->setXY(13, 226);
			if($_bank != '') $pdf->Cell(13,4,'Bank : ');
			$pdf->setXY(17, 226);
			$pdf->setLeftMargin(30);
			$pdf->Write(4, $_bank_address);*/
/*	5	*/	$pdf->setXY(43,201);
			$pdf->Cell(28, 4, $_delivery_by);
			$pdf->Cell(5, 4, ($_delivery_chk & 1) ? "X":"");
			$pdf->Cell(26);
			$pdf->Cell(0, 4, ($_delivery_freight_charge>0) ? 'Rp. '.number_format((double)$_delivery_freight_charge) : '');
/*	6	*/	/*$pdf->setXY(45,208);
			$pdf->Cell(5, 4, $_payment_widthin_days, 0, 0,'R');
			$pdf->Cell(30);
			$pdf->Cell(0,4, (empty($_payment_giro_due) ? "": date("j/M/Y", strtotime($_payment_giro_due))));
			$pdf->setXY(36,216);
			$pdf->Cell(6, 4, ($_payment_chk & 16) ? "X":"");
			$pdf->Cell(28);
			$pdf->Cell(6, 4, ($_payment_chk & 32) ? "X":"");
			$pdf->Cell(33);
			$pdf->Cell(6, 4, ($_payment_chk & 64) ? "X":"");
			$pdf->Cell(30);
			$pdf->Cell(6, 4, ($_payment_chk & 128) ? "X":"");
			$pdf->setXY(35,221);
			$pdf->Cell(35, 4, $_payment_cash_by,0);
			$pdf->Cell(37, 4, $_payment_check_by,0);
			$pdf->Cell(28, 4, $_payment_transfer_by,0);
			$pdf->Cell(35, 4, (empty($_payment_giro_issue) ? "":"Issue : ".date("j/M/Y", strtotime($_payment_giro_issue))),0);*/
/*	7	*/	$pdf->setXY(30, 239);
			$pdf->MultiCell(170, 3.5, $_remark);
/*	8	*/	$pdf->setXY(30, 271);
			$pdf->Cell(40,4,ucfirst($_signature_by),0,0,"C");
			if($_paper_format == "B") {
				$pdf->setFont('Arial', '', 20);
				$pdf->setXY(150, 267);
				$pdf->Cell(40,4,'XXXXX',0,0,"C");
				$pdf->setFont('Arial', '', 10);
			}
/*	9	*/	$pdf->setFont('Arial', 'I', 8);
			$pdf->setY(249);
			if($_type_invoice == 0) {
				$pdf->Cell(178, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time), 0,0,'R');
			} else if($_type_invoice == 1) {
				$pdf->Cell(178, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)." | No. Only", 0,0,'R');
			}
/*	10	*/	if($currentDept == 'dealer') {
				$pdf->setXY(12, 190);
				$pdf->setFont('Arial', 'I', 9);
				$pdf->MultiCell(80, 3.5, "Barang yang sudah dibeli tidak dapat dikembalikan atau ditukar dengan barang lain");
			}
			$pdf->setFont('Arial', '', 10);		

//Head Total
$i = 0;
pg_result_seek($result, $counter);
if($counter==0) {
	$pdf->setY(96);
} else {
	$pdf->setXY(10,96);
	$pdf->Cell(129, 4, "Previous balance");
	$pdf->Cell(10, 4, number_format((double)$qty),0,0,'R');
	$pdf->setY(100);
	$i++;
}

//Body Item
while($items =& fetchRow($result, 0)) {
	$pdf->setX(10);
	$pdf->Cell(15, 4, $items[0],0);									// code
	$pdf->Cell(25, 4, substr($items[1],0,8),0); 					// item no
	$pdf->Cell(89, 4, substr($items[2],0,50),0);					// description
	$pdf->Cell(10, 4, number_format((double)$items[4]),0,0,'R');	// qty
	$pdf->Cell(2);
	$pdf->Cell(0, 4, $items[6],0,1);								// remark

	$qty += $items[4];
	$i++;
	if($i == $row[0]) {break;}
}
$counter += $i;

//TOTAL & TOTAL AMOUNT
$pdf->setXY(137, 184);
$pdf->Cell(12,4, number_format((double)$qty), 0, 0,'R');
?>