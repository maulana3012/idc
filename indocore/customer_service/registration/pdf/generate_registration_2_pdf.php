<?php
//=============================================================================================== CUSTOMER
$pdf->setSourceFile("template_pdf/" . $tpl_pdf);
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, -2,5.5,0,0);

if($page[1] > 1) {
	$pdf->setFont('Arial', 'I', 8);
	$pdf->setXY(185, 45);
	$pdf->Cell(20,6,"page {$page[0]} / {$page[1]}",0,0,'C');
	$pdf->setXY(185, 176.5);
	$pdf->Cell(20,6,"page {$page[0]} / {$page[1]}",0,0,'C');
}

//Invoice Info
	$pdf->setFont('Arial', '', 9);
	$pdf->setXY(57,53);
	$pdf->Cell(113, 4, $_cus_name);
	$pdf->Cell(0, 4, $_code);
	$pdf->setXY(57,57);
	$pdf->MultiCell(85, 3.5, $_cus_address);
	$pdf->setXY(170,57);
	$pdf->Cell(0, 4, $_reg_date);
	$pdf->setXY(57,183);
	$pdf->Cell(113, 4, $_cus_name);
	$pdf->Cell(0, 4, $_code);
	$pdf->setXY(57,188);
	$pdf->MultiCell(85, 3.5, $_cus_address);
	$pdf->setXY(170,188);
	$pdf->Cell(0, 4, $_reg_date);
//Signature By
	$pdf->setXY(158, 135);
	$pdf->Cell(40,4,$_signature_by,0,0,'C');
	$pdf->setXY(158, 267);
	$pdf->Cell(40,4,$_signature_by,0,0,'C');
//Information
	$pdf->setFont('Arial', 'I', 8);
	$pdf->setY(117);
	$pdf->Cell(193, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)."   ", 0,0,'R');
	$pdf->setY(248);
	$pdf->Cell(193, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)."   ", 0,0,'R');

//Item List
$pdf->setFont('Arial', '', 9);
$pdf->setY(76);
$i = 1;
pg_result_seek($item_res,$counter[0]);
while($items =& fetchRow($item_res, 0)) {
	$pdf->setX(30);
	$pdf->Cell(7, 3.5, $qty[0]+1,0);
	$pdf->Cell(2);
	$pdf->Cell(32, 3.5, $items[1],0);
	$pdf->Cell(32, 3.5, $items[2],0);
	$pdf->Cell(17, 3.5, '1',0,0,'C');
	$pdf->Cell(75, 3.5, $items[3],0,1);
	$qty[0]++;
	if($i++ == $row[0]) {break;}
}

$pdf->setY(207.5);
$i = 1;
pg_result_seek($item_res,$counter[0]);
while($items =& fetchRow($item_res, 0)) {
	$pdf->setX(30);
	$pdf->Cell(7, 3.5, $qty[1]+1,0);
	$pdf->Cell(2);
	$pdf->Cell(32, 3.5, $items[1],0);
	$pdf->Cell(32, 3.5, $items[2],0);
	$pdf->Cell(17, 3.5, '1',0,0,'C');
	$pdf->Cell(75, 3.5, $items[3],0,1);
	$qty[1]++;
	if($i++ == $row[0]) {break;}
}

$pdf->setXY(110, 112.5);
$pdf->Cell(0, 4, $qty[0]);
$pdf->setXY(110, 244);
$pdf->Cell(0, 4, $qty[1]);

?>