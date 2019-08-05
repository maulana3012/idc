<?php
$pdf->setSourceFile(APP_DIR . "_include/warehouse/template_pdf/tpl_do.pdf");
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, 0,0,210,296);
$pdf->setFont('Arial', '', 10);

//Header
$pdf->setFont('Arial', 'B', 15);
$pdf->setXY(10, 5);
$pdf->Cell(100,10, $company[0]);
$pdf->setFont('Arial', '', 9);
$pdf->setXY(10, 13);
$pdf->Cell(170, 3.5, $company[1]);

/*	1	*/	if($page[1] > 1) {
			$pdf->setFont('Arial', 'I', 8);
			$pdf->setXY(40, 23);
			$pdf->Cell(20,6,"page {$page[0]} / {$page[1]}",0,0,'C');
			}
			$pdf->setFont('Arial', '', 9);
			$pdf->setXY(175,23);
			$pdf->Cell(25,6,"WAREHOUSE",0,0,'C');
/*	2	*/	$pdf->setXY(30,31);
			$pdf->Cell(150, 4, $info['cus_to_name']);
			$pdf->Cell(0, 4, $info['do_no']);
			$pdf->setXY(30,35);
			$pdf->Cell(150, 4, $info['cus_to_address']);
			$pdf->Cell(0, 4, $_do_date);
			$pdf->setXY(30,39);
			if($info['cus_to_phone'] != '' && $info['cus_to_fax'] != '') $pdf->Cell(150, 4, "Telp : ".$info['cus_to_phone']."  Fax : ".$info['cus_to_fax']);
			else if($info['cus_to_phone'] != '' && $info['cus_to_fax'] == '') $pdf->Cell(150, 4, "Telp : ".$info['cus_to_phone']);
			else if($info['cus_to_phone'] == '' && $info['cus_to_fax'] != '') $pdf->Cell(150, 4, "Fax : ".$info['cus_to_fax']);
/*	3	*/	$pdf->setXY(30,43);
			$pdf->Cell(150, 4, $info['ship_to_name']);
			$pdf->Cell(0, 4, $_out_doc_ref);
			$pdf->setXY(30,47);
			$pdf->Cell(150, 4, $info['ship_to_address']);
			$pdf->Cell(0, 4, $_date);
			$pdf->setXY(30,52);
			if($info['ship_to_phone'] != '' && $info['ship_to_fax'] != '') $pdf->Cell(150, 4, "Telp : ".$info['ship_to_phone']."  Fax : ".$info['ship_to_fax']);
			else if($info['ship_to_phone'] != '' && $info['ship_to_fax'] == '') $pdf->Cell(150, 4, "Telp : ".$info['ship_to_phone']);
			else if($info['ship_to_phone'] == '' && $info['ship_to_fax'] != '') $pdf->Cell(150, 4, "Fax : ".$info['ship_to_fax']);
			$pdf->setXY(30,56);
			$pdf->Cell(150, 4, $info['po_no']);
			$pdf->Cell(0, 4, $_po_date);
/*	4	*/	$pdf->setXY(8, 250);
			$pdf->MultiCell(200, 3.5, $_remark);
			$pdf->setFont('Arial', 'I', 8);
			$pdf->setY(261);
			$pdf->Cell(195, 4, "Created by ".$_cfm_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)."   ", 0,0,'R');

$pdf->setFont('Arial', '', 9);
$i = array(0,0);
pg_result_seek($res_wh, $counter[0]);
pg_result_seek($res_cus, $counter[1]);

//Warehouse List
if($counter[0]==0) {
	$pdf->setY(71);
} else {
	$pdf->setXY(8,71);
	$pdf->Cell(128, 4, "Jumlah Qty Sebelumnya",0);
	$pdf->Cell(15, 4, number_format((double)$qty[0],2),0,0,'R');
	$pdf->setY(74.5);	
	$i[0]++;
}
while($item_wh =& fetchRow($res_wh, 0)) {
	$pdf->setX(8);
	$pdf->Cell(18, 3.5, $item_wh[0],0);							//code
	$pdf->Cell(18, 3.5, $item_wh[1],0);							//for
	$pdf->Cell(23, 3.5, substr($item_wh[2],0,10),0); 			// item no
	$pdf->Cell(73, 3.5, substr($item_wh[3],0,40),0);			// description
	$pdf->Cell(15, 3.5, number_format($item_wh[4],2),0,0,'R'); 	// qty
	$pdf->Cell(5);
	$pdf->Cell(0, 3.5, $item_wh[5],0,1);

	$qty[0] += $item_wh[4];
	$i[0]++;
	if($i[0] == $row[0]) {break;}
}

//Customer List
if($counter[1]==0) {
	$pdf->setY(164);
} else {
	$pdf->setXY(8,164);
	$pdf->Cell(128, 4, "Jumlah Qty Sebelumnya",0);
	$pdf->Cell(15, 4, number_format((double)$qty[1]),0,0,'R');
	$pdf->setY(168.5);
	$i[1]++;
}
while($item_cus =& fetchRow($res_cus, 0)) {
	$pdf->setX(8);
	$pdf->Cell(18, 3.5, $item_cus[0],0);						// code
	$pdf->Cell(40, 3.5, substr($item_cus[1],0,20),0);			// item no
	$pdf->Cell(75, 3.5, substr($item_cus[2],0,40),0);			// description
	$pdf->Cell(10, 3.5, number_format($item_cus[3]),0,0,'R'); 	// qty
	$pdf->Cell(5);
	$pdf->Cell(30, 3.5, $item_cus[4],0,1);						//remark

	$qty[1] += $item_cus[3];
	$i[1]++;
	if($i[1] == $row[1]) {break;}
}

$counter[0]+=$row[0];
$counter[1]+=$row[1];

$pdf->setXY(147,152);
$pdf->Cell(0, 4, number_format((double)$qty[0],2));
$pdf->setXY(147,245);
$pdf->Cell(0, 4, number_format((double)$qty[1],0));
?>