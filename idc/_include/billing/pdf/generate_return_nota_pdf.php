<?php
//NOTA RETURN ===============================================================================================
$pdf->setSourceFile(APP_DIR . "_include/billing/template_pdf/tpl_return_nota_pajak.pdf");
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, -2,5.5,0,0);

/*	1	*/	$pdf->setFont('Arial', '', 10);
			$pdf->setXY(160, 31);
			$pdf->Cell(25,4,$_bill_code,0,0,'C');	//invoice reference
/*	2	*/	$pdf->setXY(45,48);
			$pdf->Cell(35, 4, $_faktur_no);			//vat invoice number
			$pdf->Cell(80);
			$pdf->Cell(25, 4, ($_bill_date == '') ? '' : date('j-M-Y', strtotime($_bill_date)));			//invoice issued date
/*	3	*/	$pdf->setXY(42,65);
			$pdf->Cell(85, 4, $_cus_name);			//Messrs
			$pdf->setXY(42,70);
			$pdf->Cell(125, 4, $_cus_address);		// address
			$pdf->setXY(42,79);
			$pdf->Cell(0, 4, $_cus_npwp);			// NPWP
/*	4	*/	$pdf->setXY(42,65);
			$pdf->Cell(85, 4, $_cus_name);			//Messrs
/*	5	*/	$pdf->setXY(42,95);
			if(ZKP_SQL == 'IDC') {
				$pdf->Cell(0, 4, 'PT. INDOCORE PERKASA');
				$pdf->setXY(42,100);
				$pdf->Cell(0, 4, 'GRAHA MAS PEMUDA BLOK AB NO.19 JATI PULO GADUNG JAKARTA TIMUR DKI JAKARTA RAYA');
				$pdf->setXY(42,109);
				$pdf->Cell(0, 4, '01.882.938.2-059.000');
			} else if(ZKP_SQL == 'MED') {
				$pdf->Cell(0, 4, 'PT. MEDISINDO BAHANA');
				$pdf->setXY(42,100);
				$pdf->Cell(0, 4, 'RUKAN GRAHA CEMPAKA MAS BLOK E 15 JAKARTA PUSAT DKI JAKARTA RAYA');
				$pdf->setXY(42,109);
				$pdf->Cell(0, 4, '');
			}

$i = 0;
if($counter[0]==0) {
	$pdf->setY(125);
} else {
	$pdf->setXY(22,129);
	$pdf->Cell(130, 4, "Jumlah Sebelumnya");
	$pdf->Cell(30, 4, number_format((double)$total[0]),0,0,'R');
	$pdf->setY(129);
	$i++;
}

pg_result_seek($result, $counter[0]);
while($items =& fetchRow($result, 0)) {
	$pdf->setXY(2, $pdf->getY() + 4);
	$pdf->Cell(18, 4, $counter[1]++,0,0,'C');							// no
	$pdf->setX(22);
	$pdf->Cell(58, 4, substr($items[1],0,8),0);						// item no
	$pdf->Cell(15, 4, number_format((double)$items[4]),0,0,'C');	// qty
	$pdf->Cell(5);
	$pdf->Cell(25, 4, number_format((double)$items[3]),0,0,'R');	//unit price
	$pdf->Cell(20);
	$pdf->Cell(37, 4, number_format((double)$items[5]),0,0,'R');	// amount

	$total[0] += $items[5];
	$counter[0]++;
	$i++;
	if($i == $row[0]) {break;}
}

if($counter[0] == $row[2]) {
	//TOTAL & TOTAL AMOUNT
	$pdf->setXY(153, 179);
	$pdf->Cell(30,4, number_format((double)$total[0]), 0,0,'R');			//Jumlah Harga BKP yang di kembalikan
	if($_disc > 0) {
		$total[1] = $total[0] * $_disc/100;
		$total[2] = $total[0] - $total[1];
		$pdf->setXY(153, 184);
		$pdf->Cell(30,4, number_format((double)$total[1]), 0,0,'R');		//Dikurangi Potongan Harga
	} else {
		$total[2] = $total[0];
	}
	$pdf->setXY(153, 190);
	$pdf->Cell(30,4, number_format((double)$total[2]), 0,0,'R');			//Dasar Pengenaan Pajak
	$pdf->setXY(153, 196);
	$pdf->Cell(30,4, number_format((double)$total[2]*0.1), 0,0,'R');		//Pajak Pertambahan Nilai yang Kembali
	$pdf->setXY(153, 207);
	$pdf->Cell(30,4, number_format((double)$total[0] + ($total[2]*0.1) - $total[1]), 0,0,'R');	//JUMLAH
}

//Date
$pdf->setXY(155, 222);
$pdf->Cell(35,4, date("d F Y",strtotime($_return_date)));
$pdf->setXY(145, 245);
$pdf->Cell(35,4, $_signature_pajak_by,0,0,'C');
?>