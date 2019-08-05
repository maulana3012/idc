<?php
$pdf->addPage();

//No Faktur Pajak
$pdf->setXY(80,30);
$pdf->Cell(40, 4, '010.000-'.substr($_code,11,2).'.000'.substr($_code,3,5));

//Pembeli Barang
$pdf->setXY(55,102);
$pdf->Cell(140, 4, $_pajak_name); 
$pdf->setXY(55,112);
$pdf->Cell(140, 4, $_pajak_address);
$pdf->setXY(55,123);
$pdf->Cell(60, 4, $_cus_npwp);
$pdf->setXY(145,123);
$pdf->Cell(60, 4, $_cus_npwp);

$pdf->setY(143);

//Item List
$sql = "
SELECT
  a.it_model_no,
  b.biit_unit_price,
  b.biit_qty,
  b.biit_unit_price * b.biit_qty AS amount
FROM
  ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_billing_item AS b ON (a.it_code = b.it_code)
WHERE b.bill_code = '$_code'
ORDER BY a.it_code";

$res_item	 =& query($sql);
$numRowItem	 = numQueryRows($res_item);
$number		 = 1;
$totalAmountItem = 0;

while($items =& fetchRow($res_item, 0)) {
	$pdf->setXY(0, $pdf->getY() + 3.5);
	$pdf->Cell(5, 3.5, $number,0,0,'L');					// no urut
	$pdf->Cell(7);
	$pdf->Cell(53, 3.5, substr($items[0],0,30),0);			// model item
	$pdf->Cell(10, 3.5, number_format((double)$items[2]),0,0,'R');	// qty
	$pdf->Cell(15, 3.5, 'Unit @');							// unit @
	$pdf->Cell(25, 3.5, number_format((double)$items[1]),0,0,'R');	//unit price
	$pdf->setXY(143, $pdf->getY());
	$pdf->Cell(30, 3.5, number_format((double)$items[3]),0,0,'R');	// amount

	$number++;
	$totalAmountItem += $items[3];
}

$totalAmountItem_d = $totalAmountItem;

//TOTAL & TOTAL AMOUNT
$pdf->setXY(143, 202);
$pdf->Cell(30,4, number_format((double)$totalAmountItem), 0,0,'R');			//Harga Jual/Penggantian/Uang Muka/Termin
if($_disc > 0) {
	$amount_disc = $totalAmountItem * $_disc/100;
	$totalAmountItem_d = $totalAmountItem - $amount_disc;
	$pdf->setXY(143, 210);
	$pdf->Cell(30,4, number_format((double)$amount_disc), 0,0,'R');			//Dikurangi Potongan Harga
} 
$pdf->setXY(143, 222);
$pdf->Cell(30,4, number_format((double)$totalAmountItem_d), 0,0,'R');		//Dasar Pengenaan Pajak
$pdf->setXY(143, 229);
$pdf->Cell(30,4, number_format((double)$totalAmountItem_d*0.1), 0,0,'R');	//PPN = 10% X Dasar Pengenaan Pajak

//Date
$pdf->setXY(122, 237);
$pdf->Cell(15,4, 'Jakarta');
$pdf->setXY(162, 237);
$pdf->Cell(35,4, date("d F Y",strtotime($_inv_date)));

//Signature
if($_signature_pajak_by == 'A') {
	$pdf->setXY(135, 266);
	$pdf->Cell(55,4,'In Ki Kim Lee',0,0,"C");
	$pdf->setXY(135, 270);
	$pdf->Cell(55,4,'Presiden Director',0,0,"C");
} else if($_signature_pajak_by == 'B') {
	$pdf->setXY(135, 266);
	$pdf->Cell(55,4,'Min Sang Hyun',0,0,"C");
	$pdf->setXY(135, 270);
	$pdf->Cell(55,4,'Director',0,0,"C");
}
?>