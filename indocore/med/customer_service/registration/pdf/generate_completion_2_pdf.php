<?php
//=============================================================================================== CUSTOMER
$pdf->setSourceFile("template_pdf/" . $tpl_pdf);
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, -2,5.5,0,0);

if($page[1] > 1) {
	$pdf->setFont('Arial', 'I', 8);
	$pdf->setXY(190, 32);
	$pdf->Cell(20,6,"page {$page[0]} / {$page[1]}",0,0,'C');
	$pdf->setXY(190, 169.5);
	$pdf->Cell(20,6,"page {$page[0]} / {$page[1]}",0,0,'C');
}

//Invoice info
	$pdf->setFont('Arial', '', 9);
	$pdf->setXY(42,36);
	$pdf->Cell(118, 4, $col['sg_cus_to_name']);
	$pdf->Cell(0, 4, $_code);
	$pdf->setXY(42,40);
	$pdf->MultiCell(85, 3.5, $col['sg_cus_to_address']);
	$pdf->setXY(160,40.5);
	$pdf->Cell(0, 4, date('d-M-Y',strtotime($col['sg_receive_date'])));
	$pdf->setXY(160,45);
	$pdf->Cell(0, 4, date('d-M-Y',strtotime($col['sg_complete_date'])));
	$pdf->setXY(42,173.5);
	$pdf->Cell(118, 4, $col['sg_cus_to_name']);
	$pdf->Cell(0, 4, $_code);
	$pdf->setXY(42,177.8);
	$pdf->MultiCell(85, 3.5, $col['sg_cus_to_address']);
	$pdf->setXY(160,177.8);
	$pdf->Cell(0, 4, date('d-M-Y',strtotime($col['sg_receive_date'])));
	$pdf->setXY(160,182);
	$pdf->Cell(0, 4, date('d-M-Y',strtotime($col['sg_complete_date'])));
//Remark 
	$pdf->setXY(18, 109);
	$pdf->MultiCell(170, 3.5, $col['sg_remark']);
	$pdf->setXY(18, 246);
	$pdf->MultiCell(170, 3.5, $col['sg_remark']);
//Signature By
	$pdf->setXY(18, 134);
	$pdf->Cell(40,4,$col['sg_signature_completion_by'],0,0,'C');
	$pdf->setXY(18, 272);
	$pdf->Cell(40,4,$col['sg_signature_completion_by'],0,0,'C');
//Information
	$pdf->setFont('Arial', 'I', 8);
	$pdf->setY(114);
	$pdf->Cell(196, 4, "Created by ".ucfirst($S->getValue("ma_account")).date(', j-M-Y g:i:s'), 0,0,'R');
	$pdf->setY(251);
	$pdf->Cell(196, 4, "Created by ".ucfirst($S->getValue("ma_account")).date(', j-M-Y g:i:s'), 0,0,'R');

//Item List
$pdf->setFont('Arial', '', 9);
$i = 1;
$pdf->setY(64);
pg_result_seek($item_res,$counter[0]);
while($items =& fetchRowAssoc($item_res, 0)) {
	$pdf->setX(17);
	$pdf->Cell(7, 3.5, $qty[0]+1,0,0,'C');
	$pdf->Cell(2);
	$pdf->Cell(21, 3.5, $items['sgit_model_no'],0);
	$pdf->Cell(21, 3.5, $items['sgit_serial_number'],0);
	$pdf->Cell(10, 3.5, '1',0,0,'C');
	$pdf->Cell(73, 3.5, substr($items['sgit_tech_analyze'],0,42),0);
	$pdf->Cell(7, 3.5, ($items['sgit_service_action_chk']&1)?'x':'',0,0,'C');
	$pdf->Cell(7, 3.5, ($items['sgit_service_action_chk']&4)?'x':'',0,0,'C');
	$pdf->Cell(7, 3.5, ($items['sgit_service_action_chk']&2)?'x':'',0,0,'C');
	$pdf->Cell(6, 3.5, ($items['sgit_service_action_chk']&8)?'x':'',0,0,'C');
	$pdf->Cell(6, 3.5, ($items['sgit_service_action_chk']&16)?'x':'',0,0,'C');
	if($items['sgit_replacement_product']!='' && $items['sgit_replacement_part']!='')
		$pdf->Cell(0, 3.5, $items['sgit_replacement_product'].', '.$items['sgit_replacement_part'],0,1);
	else if($items['sgit_replacement_product']!='' && $items['sgit_replacement_part']=='')
		$pdf->Cell(0, 3.5, $items['sgit_replacement_product'],0,1);
	else if($items['sgit_replacement_product']=='' && $items['sgit_replacement_part']!='')
		$pdf->Cell(0, 3.5, $items['sgit_replacement_part'],0,1);
	else
		$pdf->Cell(0, 3.5, '',0,1);
	$qty[0]++;
	if($i++ == $row[0]) {break;}
}

$i = 1;
$pdf->setY(201);
pg_result_seek($item_res,$counter[0]);
while($items =& fetchRowAssoc($item_res, 0)) {
	$pdf->setX(17);
	$pdf->Cell(7, 3.5, $qty[1]+1,0,0,'C');
	$pdf->Cell(2);
	$pdf->Cell(21, 3.5, $items['sgit_model_no'],0);
	$pdf->Cell(21, 3.5, $items['sgit_serial_number'],0);
	$pdf->Cell(10, 3.5, '1',0,0,'C');
	$pdf->Cell(73, 3.5, substr($items['sgit_tech_analyze'],0,42),0);
	$pdf->Cell(7, 3.5, ($items['sgit_service_action_chk']&1)?'x':'',0,0,'C');
	$pdf->Cell(7, 3.5, ($items['sgit_service_action_chk']&4)?'x':'',0,0,'C');
	$pdf->Cell(7, 3.5, ($items['sgit_service_action_chk']&2)?'x':'',0,0,'C');
	$pdf->Cell(6, 3.5, ($items['sgit_service_action_chk']&8)?'x':'',0,0,'C');
	$pdf->Cell(6, 3.5, ($items['sgit_service_action_chk']&16)?'x':'',0,0,'C');
	if($items['sgit_replacement_product']!='' && $items['sgit_replacement_part']!='')
		$pdf->Cell(0, 3.5, $items['sgit_replacement_product'].', '.$items['sgit_replacement_part'],0,1);
	else if($items['sgit_replacement_product']!='' && $items['sgit_replacement_part']=='')
		$pdf->Cell(0, 3.5, $items['sgit_replacement_product'],0,1);
	else if($items['sgit_replacement_product']=='' && $items['sgit_replacement_part']!='')
		$pdf->Cell(0, 3.5, $items['sgit_replacement_part'],0,1);
	else
		$pdf->Cell(0, 3.5, '',0,1);
	$qty[1]++;
	if($i++ == $row[0]) {break;}
}

$pdf->setXY(72, 95.5);
$pdf->Cell(0, 4, $qty[0]);
$pdf->setXY(72, 232.5);
$pdf->Cell(0, 4, $qty[1]);


?>