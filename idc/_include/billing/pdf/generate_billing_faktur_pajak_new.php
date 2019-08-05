<?php

$pdf->addPage();
$pdf->setFont('Arial', '', 10);

//No Faktur Pajak
$pdf->setXY(80,28);
#$pdf->Cell(50, 4, '010.000-'.substr($_code,11,2).'.000'.substr($_code,3,5));
$pdf->Cell(50, 4, $cus[5]);

//Pembeli Barang
$pdf->setXY(60,100);
$pdf->Cell(100, 4, $_pajak_name); 
$pdf->setXY(60,111);
$pdf->MultiCell(140, 4, $_pajak_address);
$pdf->setXY(60,121);
//$pdf->Cell(60, 4, $_cus_npwp);
$pdf->Cell(60, 4, $cus[6]);

$i = 0;
if($counter[0]==0) {
	$pdf->setY(140);
} else {
	$pdf->setXY(13,140);
	$pdf->Cell(40, 4, "Jumlah Sebelumnya",0,0);
	$pdf->setXY(150,140);
	$pdf->Cell(29, 4, number_format((double)$total[0]),0,0,'R');
	$i++;
}

pg_result_seek($result, $counter[0]);
while($items =& fetchRow($result, 0)) {

	$pdf->setXY(0, $pdf->getY() + 3.5);
	$pdf->Cell(10, 3.5, $counter[1]++,0,0,'C'); 							// no urut
	$pdf->Cell(9);
	$pdf->Cell(53, 3.5, substr($items[1],0,30),0); 						// item no
	$pdf->Cell(10, 3.5, number_format((double)$items[4]),0,0,'R');		// qty
	$pdf->Cell(15, 3.5, 'Unit'); 										// qty
	$pdf->Cell(25, 3.5, number_format((double)$items[3]),0,0,'R'); 		// unit price
	$pdf->setXY(140, $pdf->getY());	
	$pdf->Cell(35, 3.5, number_format((double)$items[5]),0,0,'R');		// amount

	if(trim($_pajak_to) == '4SUM') {
		$pdf->setXY(0, $pdf->getY() + 3.5);
		$pdf->Cell(19);
		$pdf->Cell(53, 3.5, $items[2],0);									// desc
		$pdf->setXY(0, $pdf->getY() + 1);	
	}

	$total[0] += $items[5];
	$counter[0]++;
	$i++;
	if($i == $row[0]) {break;}
}

$total[2] = $total[0];

if($counter[0] == $row[2]) {
	$pdf->setXY(150, 200);
	$pdf->Cell(30,4, number_format((double)$total[0]), 0,0,'R');			//Harga Jual/Penggantian/Uang Muka/Termin
		if($_disc > 0) {
			$total[1] = $total[0] * $_disc/100;
			$total[2] = $total[0] - $total[1];
			$pdf->setXY(150,210);
			$pdf->Cell(30,4, number_format((double)$total[1]), 0,0,'R');	//Dikurangi Potongan Harga
		}
		$pdf->setXY(150, 221);
		$pdf->Cell(30,4, number_format((double)$total[2]), 0,0,'R');		//Dasar Pengenaan Pajak
		$pdf->setXY(150, 228);
		$pdf->Cell(30,4, number_format((double)$total[2]*0.1), 0,0,'R');	//PPN = 10% X Dasar Pengenaan Pajak
}

// xxx
$pdf->setXY(20, 200);
$pdf->Cell(62,4, 'XXXXXXXXXXXXXXXXXXXXXXXXXXXX', 0,0);					// Penggantian/Uang Muka/Termin

//Date
$pdf->setXY(123, 237);
$pdf->Cell(35,4, 'Jakarta');
$pdf->Cell(25,4, date("d F Y",strtotime($_inv_date)));

//Signature
if($_signature_pajak_by == 'A') {
	$pdf->setXY(135, 265);
	$pdf->Cell(55,4,'In Ki Kim Lee',0,0,"C");
	$pdf->setXY(135, 269);
	$pdf->Cell(55,4,'Presiden Director',0,0,"C");
} else if($_signature_pajak_by == 'B') {
	$pdf->setXY(135, 265);
	$pdf->Cell(55,4,'Hee Bae Kim',0,0,"C");
	$pdf->setXY(135, 269);
	$pdf->Cell(55,4,'Director',0,0,"C");
}
?>