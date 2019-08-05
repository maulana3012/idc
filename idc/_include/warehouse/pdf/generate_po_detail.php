<?php
$pdf->setSourceFile(APP_DIR . "_include/warehouse/template_pdf/" . $pdf_template);
$tplidx = $pdf->importPage(1, '/MediaBox');
$pdf->addPage();
$pdf->useTemplate($tplidx, -2,5.5,0,0);

/*	1	*/	// SETUP
			$pdf->setFont('Arial', 'I', 10);
			if($page[1] > 1) {
				$pdf->setXY(172,81);
				$pdf->Cell(20, 4, 'Page '.$page[0].' of '.$page[1]);	
			}
			$pdf->setFont('Arial', '', 10);

/*	2	*/	// TO
			$pdf->setXY(25,66);
			$pdf->Cell(147, 4, $_sp_name);
			$pdf->Cell(40, 4, $_code);
			$pdf->setXY(25,70);
			$pdf->MultiCell(110, 4, $_sp_address);
			$pdf->setX(25);
			if($_sp_phone!='' && $_sp_fax!='')			$pdf->Cell(147, 4, "Phone : $_sp_phone, Fax : $_sp_fax");
			else if($_sp_phone!='' && $_sp_fax=='')		$pdf->Cell(147, 4, "Phone : $_sp_phone");
			else if($_sp_phone=='' && $_sp_fax!='')		$pdf->Cell(147, 4, "Fax : $_sp_fax");
			$pdf->setXY(172,71);
			$pdf->Cell(0, 4, $_po_date);
			$pdf->setXY(172,76);
			$pdf->Cell(0, 4, $_deli_date);
			$pdf->setXY(25,80);
			$pdf->Cell(147, 4, $_sp_attn);
/*	3	*/	// SAYS
			$pdf->setXY(15,215);
			$pdf->MultiCell(190, 4, $_says_in_word);
			// REMARK
			$pdf->setXY(15,225);
			$pdf->MultiCell(190, 4, $_remark);
/*	4	*/	// SIGNATURE
			$pdf->setXY(10, 268);
			$pdf->Cell(43,4,$_prepared_by,0,0,"C");
			$pdf->Cell(27);
			$pdf->Cell(40,4,$_confirmed_by,0,0,"C");
			$pdf->Cell(30);
			$pdf->Cell(40,4,$_approved_by,0,0,"C");
/*	5	*/	$pdf->setFont('Arial', 'I', 8);
			$pdf->setY(237);
			$pdf->Cell(197, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time), 0,0,'R');
			$pdf->SetFont('Arial','',11);

//Item list
$i = 0;
pg_result_seek($result, $counter);
if($counter==0) {
	$pdf->setY(99);
} else {
	$pdf->setXY(12,99);
	$pdf->Cell(82,4, 'Previous balance');
	$pdf->Cell(14, 4, number_format($total[0]), 0,0,'R');
	$pdf->Cell(30);
	$pdf->Cell(29, 4, number_format($total[1]), 0,0,'R');
	$pdf->setY(103);
	$i++;
}
while($items =& fetchRow($result, 0)) {
	$pdf->setX(12);
	$pdf->Cell(6, 4, $no++,0,0,'C');						//NO
	$pdf->Cell(15, 4, $items[0],0);							//MODEL NO
	$pdf->Cell(60, 4, substr($items[2],0,32),0);			//DESC
	$pdf->Cell(15, 4, number_format($items[3]),0,0,'R');	//QTY
	$pdf->Cell(12, 4, substr($items[4],0,32),0,0,'C');		//UNIT
	$pdf->Cell(21, 4, number_format($items[5]),0,0,'R');	//UNIT PRICE
	$pdf->Cell(27, 4, number_format($items[6]),0,0,'R');	//AMOUNT
	$pdf->Cell(2, 4, '',0);
	$pdf->Cell(35, 4, $items[7],0,1);						//REMARK

	$total[0]	+= $items[3];
	$total[1]	+= $items[6];
	$i++;
	if($i == $row[0]) {break;}
}
$counter += $i;


if($page[0] == $page[1]) {

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
			$pdf->setXY(50,197);
			$pdf->Cell(90,4, 'TOTAL ', 0, 0,'R');
			$pdf->Cell(28,4, number_format($total[1]), 0,0,'R');

			$pdf->setXY(50, $pdf->getY()+4);
			$pdf->Cell(90,4, $_text_add1, 0, 0,'R');
			$pdf->Cell(28,4, number_format($_total_add1), 0,0,'R');
		} else if($_total_add1>0 && $_total_add2>0) {					// 0-1-1
			$pdf->setXY(50,193);
			$pdf->Cell(90,4, 'TOTAL ', 0, 0,'R');
			$pdf->Cell(28,4, number_format($total[1]), 0,0,'R');

			$pdf->setXY(50, $pdf->getY()+4);
			$pdf->Cell(90,4, "$_text_add1 ", 0, 0,'R');
			$pdf->Cell(28,4, number_format($_total_add1), 0,0,'R');

			$pdf->setXY(50, $pdf->getY()+4);
			$pdf->Cell(90,4, "$_text_add2 ", 0, 0,'R');
			$pdf->Cell(28,4, number_format($_total_add2), 0,0,'R');
		} 
	}

}


//Grand total
$pdf->SetFont('Arial','',11);
$pdf->setXY(93,206);
$pdf->Cell(15,4, number_format($total[0]),0,0,'R');
$pdf->Cell(35);
$pdf->Cell(25,4, number_format($after_vat),0,0,'R');
?>