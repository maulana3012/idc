<?php
$pdf->setSourceFile(APP_DIR . "_include/billing/template_pdf/".$tpl_pajak_pdf);
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, -2,5.5,0,0);
$pdf->setFont('Arial', '', 10);

//No Faktur Pajak
$pdf->setXY(85,38);
if(ZKP_FUNCTION == 'SMD')
	 $pdf->Cell(50, 4, '010.000-'.substr($_code,11,2).'.0000'.substr($_code,4,4));
else $pdf->Cell(50, 4, '010.000-'.substr($_code,11,2).'.0000'.substr($_code,3,5));

//Pembeli Barang
$pdf->setXY(42,95);
$pdf->Cell(100, 4, $_pajak_name); 
$pdf->setXY(42,104);
$pdf->Cell(140, 4, $_pajak_address);
$pdf->setXY(42,111);
$pdf->Cell(60, 4, $_cus_npwp);

$i = 0;
if($counter[0]==0) {
	$pdf->setY(127);
} else {
	$pdf->setXY(27,130);
	$pdf->Cell(40, 4, "Jumlah Sebelumnya",0,0);
	$pdf->setXY(150,130);
	$pdf->Cell(30, 4, number_format((double)$total[0]),0,0,'R');
	$i++;
}

pg_result_seek($result, $counter[0]);
while($items =& fetchRow($result, 0)) {
	$pdf->setXY(2, $pdf->getY() + 3.5);
	$pdf->Cell(10);
	$pdf->Cell(10, 3.5, $counter[1]++,0,0,'C'); 						// item no
	$pdf->Cell(5);
	$pdf->Cell(60, 3.5, substr($items[1],0,30),0); 						// item no
	$pdf->Cell(10, 3.5, number_format((double)$items[4]),0,0,'R');		// qty
	$pdf->Cell(10, 3.5, 'Unit'); 										// qty
	$pdf->Cell(20, 3.5, number_format((double)$items[3]),0,0,'R'); 		// unit price
	$pdf->Cell(10);
	$pdf->setXY(150, $pdf->getY());	
	$pdf->Cell(30, 3.5, number_format((double)$items[5]),0,0,'R');		// amount

	$total[0] += $items[5];
	$counter[0]++;
	$i++;
	if($i == $row[0]) {break;}
}

$total[2] = $total[0];

if($counter[0] == $row[2]) {
	$pdf->setXY(150, 186);
	$pdf->Cell(30,4, number_format((double)$total[0]), 0,0,'R');			//Harga Jual/Penggantian/Uang Muka/Termin
//	if($page[0] == $page[1]) {
		if($_disc > 0) {
			$total[1] = $total[0] * $_disc/100;
			$total[2] = $total[0] - $total[1];
			$pdf->setXY(150, 192);
			$pdf->Cell(30,4, number_format((double)$total[1]), 0,0,'R');	//Dikurangi Potongan Harga
		}
		$pdf->setXY(150, 203);
		$pdf->Cell(30,4, number_format((double)$total[2]), 0,0,'R');		//Dasar Pengenaan Pajak
		$pdf->setXY(150, 208);
		$pdf->Cell(30,4, number_format((double)$total[2]*0.1), 0,0,'R');	//PPN = 10% X Dasar Pengenaan Pajak
//	}
}

//Date
$pdf->setXY(123, 222);
$pdf->Cell(35,4, 'Jakarta');
$pdf->Cell(20,4, date("d F Y",strtotime($_inv_date)));

//Signature
$sign_pajak = array(
	'IDC'=>array(1=>array(
		'A'=>array('In Ki Kim Lee','Presiden Director'),
		'B'=>array('Min Sang Hyun','Director') 
		)),
	'MED'=>array(1=>array(
		'A'=>array('In Ki Kim Lee','Presiden Director'),
		'B'=>array('Min Sang Hyun','Director') 
		),
	  2=>array(
		'A'=>array('Min Sang Hyun','Director'),
		'B'=>array('Dahlia Sana Buwana','Assistant Manager') 
		))
);

$pdf->setXY(135, 249);
$pdf->Cell(55,4, $sign_pajak[ZKP_SQL][$_ordered_by][$_signature_pajak_by][0],0,0,"C");
$pdf->setXY(135, 253);
$pdf->Cell(55,4, $sign_pajak[ZKP_SQL][$_ordered_by][$_signature_pajak_by][1],0,0,"C");
?>