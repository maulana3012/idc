<?php
$pdf->setSourceFile(APP_DIR . "_include/billing/template_pdf/" . $tpl_pdf[2]);
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, -2,5.5,0,0);

/*	1	*/	if($page[1] > 1) {
				$pdf->setFont('Arial', 'I', 8);
				$pdf->setXY(42, 45);
				$pdf->Cell(20,6,"page {$page[0]} / {$page[1]}",0,0,'C');
			}
			$pdf->setFont('Arial', '', 10);
/*	2	*/	$pdf->setXY(87,52);
			if(empty($_sj_date)) $pdf->Cell(25, 4, date("j-M-Y", strtotime($_inv_date)), 0,0,'C');
			else $pdf->Cell(25, 4, date("j-M-Y", strtotime($_sj_date)), 0,0,'C');
			if($_sj_code== '') $pdf->Cell(33, 4, "J". substr($_code,1,1). substr($_code, 2), 0,0,'C');
			else $pdf->Cell(33, 4, $_sj_code, 0,0,'C');
			$pdf->Cell(26, 4, $_po_date, 0,0,'C');
			$pdf->Cell(27, 4, $_po_no, 0,0,'C');
/* 	3	*/	$pdf->setXY(13,68);		// Customer to
			$pdf->Cell(100, 4, $_cus_name);
			$pdf->setXY(13,72);
			$pdf->MultiCell(95, 3.5, $_cus_address);
			if($cus[0] != '' && $cus[1] != '') 		{$pdf->setX(13); $pdf->Cell(90, 4, "Telp : ".$cus[0]."  Fax : ".$cus[1]);}
			else if($cus[0] != '' && $cus[1] == '') {$pdf->setX(13); $pdf->Cell(90, 4, "Telp : ".$cus[0]);}
			else if($cus[0] == '' && $cus[1] != '') {$pdf->setX(13); $pdf->Cell(90, 4, "Fax : ".$cus[1]);}
			$pdf->setXY(22,88);
			$pdf->Cell(100, 4, $_cus_attn);

			$pdf->setXY(112,68);	// Bill to
			$pdf->Cell(95, 4, $_ship_name);
			$pdf->setXY(112,72);
			$pdf->MultiCell(95, 3.5, $cus[2]);
			if($cus[3] != '' && $cus[4] != '') 		{$pdf->setX(112); $pdf->Cell(95, 4, "Telp : ".$cus[3]."  Fax : ".$cus[4]);}
			else if($cus[3] != '' && $cus[4] == '') {$pdf->setX(112); $pdf->Cell(95, 4, "Telp : ".$cus[3]);}
			else if($cus[3] == '' && $cus[4] != '') {$pdf->setX(112); $pdf->Cell(95, 4, "Fax : ".$cus[4]);}
/*	4	*/	$pdf->setXY(40, 222);
			$pdf->Cell(28, 4, $_delivery_by);
			$pdf->Cell(5, 4, ($_delivery_chk & 1) ? "X":"");
			$pdf->Cell(25);
			$pdf->Cell(0, 4, ($_delivery_freight_charge>0) ? 'Rp. '.number_format((double)$_delivery_freight_charge) : '');
/*	6	*/	$pdf->setXY(27, 230);
			$pdf->MultiCell(168, 3.5, $_remark);
/*	7	*/	$pdf->setXY(30, 262);
			$pdf->Cell(40,4,ucfirst($_signature_by),0,0,"C");
/*	8	*/	$pdf->setFont('Arial', 'I', 8);
			$pdf->setY(243);
			if($_type_invoice == 0) {
				$pdf->Cell(175, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time), 0,0,'R');
			} else if($_type_invoice == 1) {
				$pdf->Cell(175, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)." | No. Only", 0,0,'R');
			}
			$pdf->setFont('Arial', '', 10);

//Head Total
$i = 0;
pg_result_seek($result, $counter);
if($counter==0) {
	$pdf->setY(103);
} else {
	$pdf->setXY(12,103);
	$pdf->Cell(128, 4, "Previous balance");
	$pdf->Cell(10, 4, number_format((double)$qty),0,0,'R');
	$pdf->setY(107);
	$i++;
}
while($items =& fetchRow($result, 0)) {
	$pdf->setX(12);
	$pdf->Cell(15, 4, $items[0],0);									// code
	$pdf->Cell(33, 4, substr($items[1],0,18),0); 					// item no
	$pdf->Cell(80, 4, substr($items[2],0,48),0);					// description
	$pdf->Cell(10, 4, number_format((double)$items[4]),0,0,'R');	// qty
	$pdf->Cell(5);
	$pdf->Cell(0, 4, $items[6],0,1);

	$qty += $items[4];
	$i++;
	if($i == $row[0]) {break;}
}
$counter += $i;

$pdf->setXY(143,207);
$pdf->Cell(7,4, number_format((double)$qty), 0, 0,'R');
?>