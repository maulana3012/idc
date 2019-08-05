<?php
$pdf->setSourceFile(APP_DIR . "_include/purchasing/template_pdf/" . $pdf_template);
$tplidx = $pdf->importPage(1, '/MediaBox');
$pdf->addPage();
$pdf->useTemplate($tplidx, -2,5.5,0,0);

/*	1	*/	$pdf->setFont('Arial', '', 13);
			$pdf->setXY(170,20);
			$pdf->Cell(20, 4, (ZKP_SQL=='IDC' && $_revision_time > 0) ? "REVISE : $_revision_time" : '');		//Revision time
/*	2	*/	$pdf->setFont('Arial', '', 10);
			$pdf->setXY(95,53);
			$pdf->Cell(20, 4, "PO NO : ".$_code);		//PO NO	
/*	3	*/	$pdf->setXY(40, 63);
			$pdf->Cell(60, 4, $_sp_name);				//TO
			$pdf->Cell(40);
			$pdf->Cell(50, 4, $info[1]);				//FAX
/*	4	*/	$pdf->setXY(40, $pdf->getY()+5);
			$pdf->Cell(60, 4, $info[2]);				//ATTN
			$pdf->Cell(40);
			$pdf->Cell(50, 4, $info[0]);				//TELP
/*	5	*/	$pdf->setXY(40,$pdf->getY()+5);
			$pdf->Cell(60, 4, $info[3]);				//CC
			$pdf->Cell(40);
			$pdf->Cell(50, 4, $_po_date);				//DATE
/*	6	*/	$pdf->setXY(40, $pdf->getY()+5);
			$pdf->Cell(60, 4, 'ORDER');					//RE
			$pdf->Cell(40);
			$pdf->Cell(50, 4, $page[0]." / ".$page[1]);	//PAGE
/*	7	*/	$pdf->setXY(25, 193);
			$pdf->MultiCell(170, 4, $_print_remark);
			$pdf->SetFont('Arial','',11);
			$pdf->setXY(28, 270);
			$pdf->Cell(40,4,$_prepared_by,0,0,"C");
			$pdf->Cell(75);
			$pdf->Cell(40,4,$_confirmed_by,0,0,"C");
/*	8	*/	$pdf->setXY(117, 91);
			$pdf->Cell(23,4,"( ".$curr[$_currency_type]." )",0,0,"C");
			$pdf->Cell(23,4,"( ".$curr[$_currency_type]." )",0,0,"C");

//Item list
$i = 0;
pg_result_seek($result, $counter);
if($counter==0) {
	$pdf->setY(100);
} else {
	$pdf->setXY(38,100);
	$pdf->Cell(46,4, 'Previous balance');
	$pdf->Cell(15, 3.5, number_format($total[0]), 0,0,'R');
	$pdf->Cell(40);
	$pdf->Cell(23, 3.5, number_format($total[1],2), 0,0,'R');
	$pdf->setY(104);
	$i++;
}
while($items =& fetchRow($result, 0)) {
	$pdf->setX(23);
	$pdf->Cell(13, 4, $no++,0,0,'C');						//NO
	$pdf->Cell(3);
	$pdf->Cell(45, 4, substr($items[1],0,32),0);			//ITEM
	$pdf->Cell(15, 4, number_format($items[4]),0,0,'R');	//QTY
	$pdf->Cell(2);
	$pdf->Cell(15, 4, substr($items[6],0,15),0);			//ITEM
	$pdf->Cell(23, 4, number_format($items[3],2),0,0,'R');	//UNIT PRICE
	$pdf->Cell(23, 4, number_format($items[5],2),0,0,'R');	//AMOUNT
	$pdf->Cell(4);
	$pdf->Cell(35, 4, substr($items[7],0,32),0,1);			//REMARK

	$total[0]	+= $items[4];
	$total[1]	+= $items[5];
	$i++;
	if($i == $row[0]) {break;}
}
$counter += $i;

$pdf->setXY(84, 177);
$pdf->Cell(15,4, number_format($total[0]), 0, 0,'R');
$pdf->Cell(40);
$pdf->Cell(23,4, number_format($total[1],2), 0,0,'R');
?>