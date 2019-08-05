<?php
$pdf->setSourceFile(APP_DIR . "_include/billing/template_pdf/" . $tpl_pdf[2]);
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, -2,5.5,0,0);

/*	1	*/	if($page[1] > 1) {
				$pdf->setFont('Arial', 'I', 8);
				$pdf->setXY(35, 34);
				$pdf->Cell(20,6,"page {$page[0]} / {$page[1]}",0,0,'C');
			}
			$pdf->setFont('Arial', '', 10);
/*	2	*/	$pdf->setXY(76,42);
			if(empty($_sj_date)) $pdf->Cell(30, 4, date("j-M-Y", strtotime($_inv_date)));
			else $pdf->Cell(30, 4, date("j-M-Y", strtotime($_sj_date)));
			if($_sj_code== '') $pdf->Cell(30, 4, "J". substr($_code,1,1). substr($_code, 2));
			else $pdf->Cell(30, 4, $_sj_code);
			$pdf->Cell(30, 4, $_po_date,0,0,'C');
			$pdf->Cell(38, 4, $_po_no,0,0,'C');
/* 	3	*/	$pdf->setXY(6,57);		// Customer to
			$pdf->Cell(100, 4, $_cus_name);
			$pdf->setXY(6,61);
			$pdf->MultiCell(100, 3.5, $_cus_address);
			if($cus[0] != '' && $cus[1] != '') 		{$pdf->setX(6); $pdf->Cell(100, 4, "Telp : ".$cus[0]."  Fax : ".$cus[1]);}
			else if($cus[0] != '' && $cus[1] == '') {$pdf->setX(6); $pdf->Cell(100, 4, "Telp : ".$cus[0]);}
			else if($cus[0] == '' && $cus[1] != '') {$pdf->setX(6); $pdf->Cell(100, 4, "Fax : ".$cus[1]);}
			$pdf->setXY(17,80);
			$pdf->Cell(100, 4, $_cus_attn);

			$pdf->setXY(112,57);	// Bill to
			$pdf->Cell(95, 4, $_ship_name);
			$pdf->setXY(112,61);
			$pdf->MultiCell(95, 3.5, $cus[2]);
			if($cus[3] != '' && $cus[4] != '') 		{$pdf->setX(112); $pdf->Cell(95, 4, "Telp : ".$cus[3]."  Fax : ".$cus[4]);}
			else if($cus[3] != '' && $cus[4] == '') {$pdf->setX(112); $pdf->Cell(95, 4, "Telp : ".$cus[3]);}
			else if($cus[3] == '' && $cus[4] != '') {$pdf->setX(112); $pdf->Cell(95, 4, "Fax : ".$cus[4]);}
/*	4	*/	$pdf->setXY(35,228);
			$pdf->Cell(42, 4, $_delivery_by);
			$pdf->Cell(5, 4, ($_delivery_chk & 1) ? "X":"");
			$pdf->Cell(25);
			$pdf->Cell(0, 4, ($_delivery_freight_charge>0) ? 'Rp. '.number_format((double)$_delivery_freight_charge) : '');
/*	6	*/	$pdf->setXY(22, 236);
			$pdf->MultiCell(168, 3.5, $_remark);
/*	7	*/	$pdf->setXY(10, 270);
			$pdf->Cell(40,4,ucfirst($_signature_by),0,0,"C");
/*	8	*/	$pdf->setFont('Arial', 'I', 8);
			$pdf->setY(246);
			if($_type_invoice == 0) {
				$pdf->Cell(190, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time), 0,0,'R');
			} else if($_type_invoice == 1) {
				$pdf->Cell(190, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)." | No. Only", 0,0,'R');
			}
			$pdf->setFont('Arial', '', 10);

//Head Total
$i = 0;
pg_result_seek($result, $counter);
if($counter==0) {
	$pdf->setY(96);
} else {
	$pdf->setXY(6,96);
	$pdf->Cell(150, 4, "Previous balance");
	$pdf->Cell(10, 4, number_format((double)$qty),0,0,'R');
	$pdf->setY(100);
	$i++;
}
while($items =& fetchRow($result, 0)) {
	$pdf->setX(6);
	$pdf->Cell(16, 4, $items[0],0);									// code
	$pdf->Cell(35, 4, substr($items[1],0,18),0); 					// item no
	$pdf->Cell(90, 4, substr($items[2],0,60),0);					// description
	$pdf->Cell(5);
	$pdf->Cell(15, 4, number_format((double)$items[4]),0,0,'R');	// qty
	$pdf->Cell(5);
	$pdf->Cell(0, 4, $items[6],0,1);

	$qty += $items[4];
	$i++;
	if($i == $row[0]) {break;}
}
$counter += $i;

$pdf->setXY(160,212);
$pdf->Cell(7,4, number_format((double)$qty), 0, 0,'R');
?>