<?php
$pdf->setSourceFile(APP_DIR . "_include/billing/template_pdf/" . $tpl_pdf[1]);
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, 0,0,210,296);

//Header
$pdf->setFont('Arial', 'B', 15);
$pdf->setXY(10, 5);
$pdf->Cell(100,10, $company[0]);
$pdf->setFont('Arial', '', 9);
$pdf->setXY(10, 14);
$pdf->Cell(170, 3.5, $company[1]);

/* 1. */	if($page[1] > 1) {
				$pdf->setFont('Arial', 'I', 8);
				$pdf->setXY(40, 25);
				$pdf->Cell(20,6,"page {$page[0]} / {$page[1]}",0,0,'C');
			}
			$pdf->setFont('Arial', '', 10);
			$pdf->setXY(173,25);
			$pdf->Cell(25,6,"WAREHOUSE",0,0,'C');
/* 2. */	$pdf->setXY(30,35);
			$pdf->Cell(74, 4, $_cus_name);
			$pdf->Cell(73);
			$pdf->Cell(0, 4, 'D'.substr($_code,1,13));
/* 3. */	$pdf->setXY(30,39);
			$pdf->Cell(130, 4, $_cus_address);
			$pdf->Cell(17);
			$pdf->Cell(0, 4, date('d-M-Y', strtotime($_do_date)));
/* 4. */	$pdf->setXY(30,43);
			if($cus[0] != '' && $cus[1] != '') $pdf->Cell(125, 4, "Telp : ".$cus[0]."  Fax : ".$cus[1]);
			else if($cus[0] != '' && $cus[1] == '') $pdf->Cell(125, 4, "Telp : ".$cus[0]);
			else if($cus[0] == '' && $cus[1] != '') $pdf->Cell(125, 4, "Fax : ".$cus[1]);
/* 5. */	$pdf->setXY(30,47);
			$pdf->Cell(74, 4, $_ship_name);
			$pdf->Cell(73);
			$pdf->Cell(0, 4, $_code);
/* 6. */	$pdf->setXY(30,51);
			$pdf->Cell(130, 4, $cus[2]);
			$pdf->Cell(17);
			$pdf->Cell(0, 4, date('d-M-Y', strtotime($_inv_date)));
/* 7. */	$pdf->setXY(30,55);
			if($cus[3] != '' && $cus[4] != '') $pdf->Cell(125, 4, "Telp : ".$cus[3]."  Fax : ".$cus[4]);
			else if($cus[3] != '' && $cus[4] == '') $pdf->Cell(125, 4, "Telp : ".$cus[3]);
			else if($cus[3] == '' && $cus[4] != '') $pdf->Cell(125, 4, "Fax : ".$cus[4]);
/* 8. */	$pdf->setXY(30,62);
			$pdf->Cell(74, 4, $_po_no);
			$pdf->Cell(73);
			$pdf->Cell(0, 4, $_po_date);
/* 9. */	$pdf->setXY(11, 255);
			$pdf->MultiCell(170, 3.5, $_remark);
/* 10. */	$pdf->setFont('Arial', 'I', 8);
			$pdf->setY(265);
			$pdf->Cell(193, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)."   ", 0,0,'R');

$pdf->setFont('Arial', '', 10);
$i = array(0,0);
pg_result_seek($wh_res, $counter[0]);
pg_result_seek($result, $counter[1]);

if($counter[0]==0) {
	$pdf->setY(77);
} else {
	$pdf->setXY(100,77);
	$pdf->Cell(37, 4, "Previous balance",0,0,'R');
	$pdf->Cell(15, 4, number_format((double)$qty[0],2),0,0,'R');
	$pdf->setY(81);
	$i[0]++;
}
while($item_wh =& fetchRow($wh_res, 0)) {
	$pdf->setX(11);
	$pdf->Cell(15, 3.5, $item_wh[0],0);									//code
	$pdf->Cell(15, 3.5, $item_wh[1],0);									//for
	$pdf->Cell(23, 3.5, substr($item_wh[2],0,10),0); 					// item no
	$pdf->Cell(73, 3.5, substr($item_wh[3],0,40),0);					// description
	$pdf->Cell(15, 3.5, number_format((double)$item_wh[4],2),0,0,'R');	// qty
	$pdf->Cell(5);
	$pdf->Cell(0, 3.5, $item_wh[5],0,1);								//remark

	$qty[0] += $item_wh[4];
	$i[0]++;
	if($i[0] == $row[0]) {break;}
}

if($counter[1]==0) {
	$pdf->setY(170);
} else {
	$pdf->setXY(100,170);
	$pdf->Cell(34, 4, "Previous balance",0,0,'R');
	$pdf->Cell(15, 4, number_format((double)$qty[1]),0,0,'R');
	$pdf->setY(174);
	$i[1]++;
}
while($items =& fetchRow($result, 0)) {
	$pdf->setX(11);
	$pdf->Cell(15, 3.5, $items[0],0);								// code
	$pdf->Cell(40, 3.5, substr($items[1],0,20),0);					// item no
	$pdf->Cell(72, 3.5, substr($items[2],0,40),0);					// description
	$pdf->Cell(10, 3.5, number_format((double)$items[4]),0,0,'R');	// qty
	$pdf->Cell(5);
	$pdf->Cell(30, 3.5, $items[6],0,1);								//remark

	$qty[1] += $items[4];
	$i[1]++;
	if($i[1] == $row[1]) {break;}
}

$counter[0]+=$row[0];
$counter[1]+=$row[1];

$pdf->setXY(141,157);
$pdf->Cell(0, 4, number_format((double)$qty[0],2));
$pdf->setXY(142,249);
$pdf->Cell(0, 4, number_format((double)$qty[1],0));
?>