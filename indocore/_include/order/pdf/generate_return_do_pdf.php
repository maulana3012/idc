<?php
$pdf->setSourceFile(APP_DIR . "_include/order/template_pdf/" . $tpl_pdf[2]);
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, 0,0,0,0);

//Header
$pdf->setFont('Arial', 'B', 15);
$pdf->setXY(12, 25);
$pdf->Cell(100,10, $company[0]);
$pdf->setFont('Arial', '', 9);
$pdf->setXY(12, 34);
$pdf->Cell(170, 3.5, $company[1]);
if(isset($company[2])) {
	$pdf->setXY(12, 37);
	$pdf->Cell(170, 3.5, implode('   ', $company[2]));
}

/*	1	*/	$pdf->setFont('Arial', '', 10);
			$pdf->setXY(170,49);
			$pdf->Cell(25,6,"WAREHOUSE",0,0,'C');
			$pdf->setXY(43,48);
			$pdf->Cell(25,6,"page {$page[0]} / {$page[1]}",0,0,'C');
/*	2	*/	$pdf->setXY(30,58);
			$pdf->Cell(145, 4, "[". trim($_cus_to) ."] ". $_cus_to_attn);
			$pdf->Cell(0, 4, $_code);
/*	3	*/	$pdf->setXY(30,62);
			$pdf->Cell(145, 4, $_cus_to_address);
			$pdf->Cell(0, 4, $_po_date);
/*	4	*/	$pdf->setXY(30,68);
			$pdf->Cell(145, 4, "[". trim($_ship_to) ."] ". $_ship_to_attn);
			$pdf->Cell(0, 4, $_code);
/*	5	*/	$pdf->setXY(30,72);
			$pdf->Cell(145, 4, $_ship_to_address);
			$pdf->Cell(0, 4, $_po_date);

//Head Total
$i = array(0,0);
pg_result_seek($wh_res, $counter[0]);
pg_result_seek($cus_res, $counter[1]);

if($counter[0]==0) {
	$pdf->setY(86);
} else {
	$pdf->setXY(11,86);
	$pdf->Cell(127, 4, "Previous balance");
	$pdf->Cell(15, 4, number_format((double)$qty[0],2),0,0,'R');
	$pdf->setY(90);
	$i[0]++;
}
while($item_wh =& fetchRow($wh_res, 0)) {
	$pdf->setX(11);
	$pdf->Cell(15, 3.5, $item_wh[0],0);									//code
	$pdf->Cell(15, 3.5, $item_wh[1],0);									//for
	$pdf->Cell(25, 3.5, substr($item_wh[2],0,11),0); 					// item no
	$pdf->Cell(72, 3.5, substr($item_wh[3],0,40),0);					// description
	$pdf->Cell(15, 3.5, number_format((double)$item_wh[4],2),0,0,'R');	// qty
	$pdf->Cell(5);
	$pdf->Cell(0, 3.5, $item_wh[5],0,1);								//remark

	$qty[0] += $item_wh[4];
	$i[0]++;
	if($i[0] == $row[0]) {break;}
}

if($counter[1]==0) {
	$pdf->setY(171);
} else {
	$pdf->setXY(11,171);
	$pdf->Cell(123, 4, "Previous balance");
	$pdf->Cell(15, 4, number_format((double)$qty[1]),0,0,'R');
	$pdf->setY(175);
	$i[1]++;
}
while($items =& fetchRow($cus_res, 0)) {
	$pdf->setX(11);
	$pdf->Cell(15, 3.5, $items[0],0);								// code
	$pdf->Cell(40, 3.5, substr($items[1],0,20),0);					// item no
	$pdf->Cell(73, 3.5, substr($items[2],0,40),0);					// description
	$pdf->Cell(10, 3.5, number_format((double)$items[4]),0,0,'R');	// qty
	$pdf->Cell(5);
	$pdf->Cell(0, 3.5, $items[6],0,1);								//remark

	$qty[1] += $items[4];
	$i[1]++;
	if($i[1] == $row[0]) {break;}
}

$counter[0]+=$row[0];
$counter[1]+=$row[0];

$pdf->setXY(142,160);
$pdf->Cell(0, 4, number_format((double)$qty[0],2));
$pdf->setXY(142,244);
$pdf->Cell(0, 4, number_format((double)$qty[1],0));
?>