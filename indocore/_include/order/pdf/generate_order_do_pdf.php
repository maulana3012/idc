<?php
$pdf->setSourceFile(APP_DIR . "_include/order/template_pdf/" . $tpl_pdf[1]);
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, 0,0,0,0);

//Header
$pdf->setFont('Arial', 'B', 15);
$pdf->setXY(12, 20);
$pdf->Cell(100,10, $company[0]);
$pdf->setFont('Arial', '', 9);
$pdf->setXY(12, 29);
$pdf->Cell(170, 3.5, $company[1]);
if(isset($company[2])) {
	$pdf->setXY(12, 33);
	$pdf->Cell(170, 3.5, implode('   ', $company[2]));
}

/*	1	*/	$pdf->setFont('Arial', '', 10);
			$pdf->setXY(170,46);
			$pdf->Cell(25,6,"WAREHOUSE",0,0,'C');
			$pdf->setXY(38,46);
			$pdf->Cell(25,6,"page {$page[0]} / {$page[1]}",0,0,'C');
/*	2	*/	$pdf->setXY(27,55);
			$pdf->Cell(145, 4, "[". trim($_cus_to) ."] ". $_cus_to_attn);
			$pdf->Cell(0, 4, $_code);
/*	3	*/	$pdf->setXY(27,59);
			$pdf->Cell(145, 4, $_cus_to_address);
			$pdf->Cell(0, 4, $_po_date);
/*	4	*/	$pdf->setXY(27,64);
			$pdf->Cell(145, 4, "[". trim($_ship_to) ."] ". $_ship_to_attn);
			$pdf->Cell(0, 4, $_code);
/*	5	*/	$pdf->setXY(27,68);
			$pdf->Cell(145, 4, $_ship_to_address);
			$pdf->Cell(0, 4, $_po_date);
/*	6	*/	$pdf->setXY(27,74);
			$pdf->Cell(145, 4, $_po_no);
			$pdf->Cell(0, 4, $_po_date);
			$pdf->setFont('Arial', '', 10);

//Head Total
$i = array(0,0);
pg_result_seek($wh_res, $counter[0]);
pg_result_seek($cus_res, $counter[1]);

if($counter[0]==0) {
	$pdf->setY(90);
} else {
	$pdf->setXY(97,90);
	$pdf->Cell(37, 4, "Previous balance",0,0,'R');
	$pdf->Cell(15, 4, number_format((double)$qty[0],2),0,0,'R');
	$pdf->setY(94);
	$i[0]++;
}
while($item_wh =& fetchRow($wh_res, 0)) {
	$pdf->setX(11);
	$pdf->Cell(15, 3.5, $item_wh[0],0);									//code
	$pdf->Cell(15, 3.5, $item_wh[1],0);									//for
	$pdf->Cell(23, 3.5, substr($item_wh[2],0,10),0); 					// item no
	$pdf->Cell(70, 3.5, substr($item_wh[3],0,40),0);					// description
	$pdf->Cell(15, 3.5, number_format((double)$item_wh[4],2),0,0,'R');	// qty
	$pdf->Cell(5);
	$pdf->Cell(0, 3.5, $item_wh[5],0,1);								//remark

	$qty[0] += $item_wh[4];
	$i[0]++;
	if($i[0] == $row[0]) {break;}
}

if($counter[1]==0) {
	$pdf->setY(175);
} else {
	$pdf->setXY(97,175);
	$pdf->Cell(34, 4, "Previous balance",0,0,'R');
	$pdf->Cell(15, 4, number_format((double)$qty[1]),0,0,'R');
	$pdf->setY(179);
	$i[1]++;
}
while($items =& fetchRow($cus_res, 0)) {
	$pdf->setX(11);
	$pdf->Cell(15, 3.5, $items[0],0);								// code
	$pdf->Cell(38, 3.5, substr($items[1],0,20),0);					// item no
	$pdf->Cell(72, 3.5, substr($items[2],0,40),0);					// description
	$pdf->Cell(10, 3.5, number_format((double)$items[4]),0,0,'R');	// qty
	$pdf->Cell(5);
	$pdf->Cell(0, 3.5, $items[6],0,1);								//remark

	$qty[1] += $items[4];
	$i[1]++;
	if($i[1] == $row[0]) {break;}
}

$counter[0]+=$row[0];
$counter[1]+=$row[0];

$pdf->setXY(139,163);
$pdf->Cell(0, 4, number_format((double)$qty[0],2));
$pdf->setXY(139,247);
$pdf->Cell(0, 4, number_format((double)$qty[1],0));
?>