<?php
$pdf->setSourceFile(APP_DIR . "_include/warehouse/template_pdf/" . $pdf_template);
$tplidx = $pdf->importPage(1, '/MediaBox');
$pdf->addPage();
$pdf->useTemplate($tplidx, -2,5.5,0,0);

/*	1	*/	if($_revision_time > 0) {
				$pdf->setFont('Arial', '', 12);
				$pdf->setXY(183,48);
				$pdf->Cell(20, 4, 'REVISED');
			}
			$pdf->setFont('Arial', '', 10);
/*	2	*/	$pdf->setXY(27,58);
			$pdf->Cell(145, 4, $_sp_name);
			$pdf->Cell(40, 4, $_code);
			$pdf->setXY(27,63);
			$pdf->MultiCell(110, 4, $_sp_address);
			$pdf->setX(27);
			if($_sp_phone!='' && $_sp_fax!='')			$pdf->Cell(147, 4, "Phone : $_sp_phone, Fax : $_sp_fax");
			else if($_sp_phone!='' && $_sp_fax=='')		$pdf->Cell(147, 4, "Phone : $_sp_phone");
			else if($_sp_phone=='' && $_sp_fax!='')		$pdf->Cell(147, 4, "Fax : $_sp_fax");
			$pdf->setXY(172,63);
			$pdf->Cell(0, 4, $_po_date);
			$pdf->setXY(172,67);
			$pdf->Cell(0, 4, $_deli_date);
			$pdf->setXY(27,76);
			$pdf->Cell(147, 4, $_sp_attn);
/*	3	*/	$pdf->setXY(17,233);
			$pdf->MultiCell(190, 4, $_says_in_word);
/*	4	*/	$pdf->setXY(10, 270);
			$pdf->Cell(40,4,$_prepared_by,0,0,"C");
			$pdf->Cell(40);
			$pdf->Cell(40,4,$_confirmed_by,0,0,"C");
			$pdf->Cell(20);
			$pdf->Cell(40,4,$_approved_by,0,0,"C");
/*	5	*/	$pdf->setFont('Arial', 'I', 8);
			$pdf->setY(242);
			$pdf->Cell(197, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time), 0,0,'R');
			$pdf->SetFont('Arial','',11);

//Item list
$i = 0;
pg_result_seek($result, $counter);
if($counter==0) {
	$pdf->setY(95);
} else {
	$pdf->setXY(15,95);
	$pdf->Cell(85,4, 'Previous balance');
	$pdf->Cell(15, 4, number_format($total[0]), 0,0,'R');
	$pdf->Cell(30);
	$pdf->Cell(27, 4, number_format($total[1]), 0,0,'R');
	$pdf->setY(99);
	$i++;
}
while($items =& fetchRow($result, 0)) {
	$pdf->setX(15);
	$pdf->Cell(8, 4, $no++);								//NO
	$pdf->Cell(15, 4, $items[0],0);							//MODEL NO
	$pdf->Cell(62, 4, substr($items[2],0,32),0);			//DESC
	$pdf->Cell(15, 4, number_format($items[3]),0,0,'R');	//QTY
	$pdf->Cell(12, 4, substr($items[4],0,32),0,0,'C');		//UNIT
	$pdf->Cell(18, 4, number_format($items[5]),0,0,'R');	//UNIT PRICE
	$pdf->Cell(27, 4, number_format($items[6]),0,0,'R');	//AMOUNT
	$pdf->Cell(2, 4, '',0);
	$pdf->Cell(35, 4, $items[7],0,1);						//REMARK

	$total[0]	+= $items[3];
	$total[1]	+= $items[6];
	$i++;
	if($i == $row[0]) {break;}
}
$counter += $i;

$before_vat = $total[1]+$_total_add1+$_total_add2;
$amount_vat	= ($before_vat*$_vat) / 100;
$after_vat	= $before_vat+$amount_vat;

if($_vat>0) {
	if($_total_add1==0 && $_total_add2==0) {						// 1-0-0
		$pdf->setXY(55,214);
		$pdf->Cell(90,4, 'Total Before VAT', 0, 0,'R');
		$pdf->Cell(28,4, number_format($total[1]), 0,0,'R');

		$pdf->setXY(55, $pdf->getY()+4);
		$pdf->Cell(90,4, 'VAT '. number_format($_vat) . '% ', 0, 0,'R');
		$pdf->Cell(28,4, number_format($amount_vat), 0,0,'R');
	} else if($_total_add1>0 && $_total_add2==0) {					// 1-1-0
		$pdf->setXY(55,205);
		$pdf->Cell(90,4, 'TOTAL ', 0, 0,'R');
		$pdf->Cell(28,4, number_format($total[1]), 0,0,'R');

		$pdf->setXY(55, $pdf->getY()+4);
		$pdf->Cell(90,4, $_text_add1, 0, 0,'R');
		$pdf->Cell(28,4, number_format($_total_add1), 0,0,'R');

		$pdf->setXY(55, $pdf->getY()+4);
		$pdf->Cell(90,4, 'Total Before VAT', 0, 0,'R');
		$pdf->Cell(28,4, number_format($before_vat), 0,0,'R');

		$pdf->setXY(55, $pdf->getY()+4);
		$pdf->Cell(90,4, 'VAT '. number_format($_vat) . '% ', 0, 0,'R');
		$pdf->Cell(28,4, number_format($amount_vat), 0,0,'R');
	} else if($_total_add1>0 && $_total_add2>0) {					// 1-1-1
		$pdf->setXY(55,202);
		$pdf->Cell(90,4, 'TOTAL ', 0, 0,'R');
		$pdf->Cell(28,4, number_format($total[1]), 0,0,'R');

		$pdf->setXY(55, $pdf->getY()+4);
		$pdf->Cell(90,4, "$_text_add1 ", 0, 0,'R');
		$pdf->Cell(28,4, number_format($_total_add1), 0,0,'R');

		$pdf->setXY(55, $pdf->getY()+4);
		$pdf->Cell(90,4, "$_text_add2 ", 0, 0,'R');
		$pdf->Cell(28,4, number_format($_total_add2), 0,0,'R');

		$pdf->setXY(55, $pdf->getY()+4);
		$pdf->Cell(90,4, 'Total Before VAT', 0, 0,'R');
		$pdf->Cell(28,4, number_format($before_vat), 0,0,'R');

		$pdf->setXY(55, $pdf->getY()+4);
		$pdf->Cell(90,4, 'VAT '. number_format($_vat) . '% ', 0, 0,'R');
		$pdf->Cell(28,4, number_format($amount_vat), 0,0,'R');
	}
} else if($_vat==0) {
	if($_total_add1>0 && $_total_add2==0) {							// 0-1-0
		$pdf->setXY(55,214);
		$pdf->Cell(90,4, 'TOTAL ', 0, 0,'R');
		$pdf->Cell(28,4, number_format($total[1]), 0,0,'R');

		$pdf->setXY(55, $pdf->getY()+4);
		$pdf->Cell(90,4, $_text_add1, 0, 0,'R');
		$pdf->Cell(28,4, number_format($_total_add1), 0,0,'R');
	} else if($_total_add1>0 && $_total_add2>0) {					// 0-1-1
		$pdf->setXY(55,210);
		$pdf->Cell(90,4, 'TOTAL ', 0, 0,'R');
		$pdf->Cell(28,4, number_format($total[1]), 0,0,'R');

		$pdf->setXY(55, $pdf->getY()+4);
		$pdf->Cell(90,4, "$_text_add1 ", 0, 0,'R');
		$pdf->Cell(28,4, number_format($_total_add1), 0,0,'R');

		$pdf->setXY(55, $pdf->getY()+4);
		$pdf->Cell(90,4, "$_text_add2 ", 0, 0,'R');
		$pdf->Cell(28,4, number_format($_total_add2), 0,0,'R');
	} 
}

//Grand total
$pdf->setXY(101,225);
$pdf->Cell(15,4, number_format($total[0]),0,0,'R');
$pdf->Cell(30);
$pdf->Cell(27,4, number_format($after_vat),0,0,'R');
?>