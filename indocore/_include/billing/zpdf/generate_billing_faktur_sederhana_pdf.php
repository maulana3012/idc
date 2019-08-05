<?php
$pdf->setSourceFile(APP_DIR . "_include/billing/template_pdf/" . $tpl_pdf[4]);
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, -2,5.5,0,0);
$pdf->setFont('Arial', '', 10);

/*	1	*/	/*$pdf->setXY(40,53);
			$pdf->Cell(140, 4, 'PT. INDOCORE PERKASA'); 
			$pdf->setXY(40,60);
			$pdf->Cell(140, 4, 'GRAHA MAS PEMUDA BLOK AB NO.19 JATI PULO GADUNG JAKARTA TIMUR DKI JAKARTA RAYA');
			$pdf->setXY(40,68);
			$pdf->Cell(140, 4, '01.882.938.2-059.000');*/
/*	2	*/	$pdf->setXY(37,80);
			$pdf->Cell(100, 4, $_pajak_name); 
			$pdf->setXY(37,85);
			$pdf->Cell(140, 4, $_pajak_address);
			$pdf->setXY(165,80);
			$pdf->Cell(60, 4, $_code);
/*	3	*/	$pdf->setXY(143, 226);
			$pdf->Cell(35,4, date("d F Y",strtotime($_inv_date)));
/*	4	*/	if($_signature_pajak_by == 'A') {
				$pdf->setXY(125, 253);
				$pdf->Cell(55,4,'In Ki Kim Lee',0,0,"C");
				$pdf->setXY(125, 257);
				$pdf->Cell(55,4,'Presiden Director',0,0,"C");
			} else if($_signature_pajak_by == 'B') {
				$pdf->setXY(125, 253);
				$pdf->Cell(55,4,'Min Sang Hyun',0,0,"C");
				$pdf->setXY(125, 257);
				$pdf->Cell(55,4,'Director',0,0,"C");
			}

//Head Total
pg_result_seek($result, $counter[1]);
$i = 0;
if($counter[0]==0) {
	$pdf->setY(112);
} else {
	$pdf->setXY(30,112);
	$pdf->Cell(125, 4, "Previous balance");
	$pdf->Cell(37, 4, number_format((double)$total[0]),0,0,'R');
	$pdf->setY(116);
	$i++;
}
if($counter[0] == $row[2]) {$counter[0]+=$row[1];}

//Body Item
while($items =& fetchRow($result, 0)) {
	$pdf->setX(10);
	$pdf->Cell(15, 3.5, $counter[2]++,0,0,'C'); 						// item no
	$pdf->Cell(5);
	$pdf->Cell(70, 3.5, substr($items[1],0,40)); 						// item no
	$pdf->Cell(15, 3.5, number_format((double)$items[4]),0,0,'R');		// qty
	$pdf->Cell(10, 3.5, 'Unit');
	$pdf->Cell(30, 3.5, number_format((double)$items[3]),0,0,'R'); 		//unit price
	$pdf->Cell(37, 3.5, number_format((double)$items[5]),0,1,'R');		// amount

	$total[0] += $items[5];
	$i++;

	if($i == $row[0]) {break;}
}
$total[2] = $total[0];
$counter[0] += $i;
if($page[0] > 1) { $counter[1] += $i-1; }
else			 { $counter[1] += $i; }

$pdf->setXY(162, 195);
$pdf->Cell(30,4, number_format((double)$total[0]), 0,0,'R');			//Harga Jual/Penggantian/Uang Muka/Termin
if($page[0] == $page[1]) {
	if($_disc > 0) {
		$total[1] = $total[0] * $_disc/100;
		$total[2] = $total[0] - $total[1];
		$pdf->setXY(160, 200);
		$pdf->Cell(30,4, number_format((double)$total[1]), 0,0,'R');	//Dikurangi Potongan Harga
	}
	$pdf->setXY(162, 211);
	$pdf->Cell(30,4, number_format((double)$total[2]), 0,0,'R');		//Dasar Pengenaan Pajak
	$pdf->setXY(162, 216);
	$pdf->Cell(30,4, number_format((double)$total[2]*0.1), 0,0,'R');	//PPN = 10% X Dasar Pengenaan Pajak
}
?>