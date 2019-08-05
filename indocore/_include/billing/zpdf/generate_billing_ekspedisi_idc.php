<?php
$pdf->setSourceFile(APP_DIR . "_include/billing/template_pdf/" . $tpl_pdf[3]);
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, -2,5.5,0,0);

//Header
$pdf->setFont('Arial', 'B', 15);
$pdf->setXY(8, 7);
$pdf->Cell(100,10, $company[0]);
$pdf->setFont('Arial', '', 9);
$pdf->setXY(8, 16);
$pdf->Cell(170, 3.5, $company[1]);
if(isset($company[2])) {
	$pdf->setXY(8, 20);
	$pdf->Cell(170, 3.5, implode('   ', $company[2]));
}

/*	1	*/	if($page[1] > 1) {
				$pdf->setFont('Arial', 'I', 8);
				$pdf->setXY(80, 34);
				$pdf->Cell(20,6,"page {$page[0]} / {$page[1]}",0,0,'C');
			}
			$pdf->setFont('Arial', '', 10);
			$pdf->setXY(178, 34);
			$pdf->Cell(25,6,"EKSPEDISI",0,0,'C');
/*	2	 */	$pdf->setXY(23,44);
			$pdf->Cell(157, 4, $_cus_name);
			$pdf->Cell(0, 4, $_code);
			$pdf->setXY(23,48);
			$pdf->Cell(157, 4, $_cus_address);
			$pdf->Cell(0, 4, $_inv_date);
/*	3	 */	$pdf->setXY(23,52);
			if($cus[0] != '' && $cus[1] != '') $pdf->Cell(1257, 4, "Telp : ".$cus[0]."  Fax : ".$cus[1]);
			else if($cus[0] != '' && $cus[1] == '') $pdf->Cell(157, 4, "Telp : ".$cus[0]);
			else if($cus[0] == '' && $cus[1] != '') $pdf->Cell(157, 4, "Fax : ".$cus[1]);
/*	4	 */	$pdf->setXY(23,58);
			$pdf->Cell(157, 4, $_cus_attn);
			if($_sj_code== '') $pdf->Cell(0, 4, "J". substr($_code,1,1). substr($_code, 2));
			else $pdf->Cell(0, 4, $_sj_code);
/*	5	 */	$pdf->setXY(180,63);
			if(empty($_sj_date)) $pdf->Cell(0, 4, date("j-M-Y", strtotime($_inv_date)));
			else $pdf->Cell(0, 4, date("j-M-Y", strtotime($_sj_date)));
/*	6	*/	if(!empty($_cus_npwp)) {
				$pdf->setXY(8,63);
				$pdf->Cell(14, 4, "NPWP:  $_cus_npwp");
			}
/*	7. 	*/	$pdf->setXY(23,71);
			$pdf->Cell(157, 4, $_po_no);
			$pdf->Cell(25, 4, $_po_date);
/*	8	*/	$pdf->setXY(23, 226);
			$pdf->Cell(74, 4, '['.trim($_ship_to).'] '.substr($_ship_name, 0, 48));
			$pdf->setXY(23, 230);
			$pdf->Cell(64, 4, $cus[2]);
/*	9	*/	$pdf->setFont('Arial', 'I', 8);
			$pdf->setY(245);
			$pdf->Cell(198, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)."   ", 0,0,'R');
/*	10	*/	$pdf->setFont('Arial', '', 10);
			$pdf->setXY(140, 269);
			$pdf->Cell(36,4,$_signature_by,0,0,"C");

//Head Total
$i = 0;
pg_result_seek($result, $counter);
if($counter == 0) {
	$pdf->setY(84);
} else {
	$pdf->setXY(7,84);
	$pdf->Cell(126, 4, "Previous balance",1);
	$pdf->Cell(14, 4, number_format((double)$total[0]),0,0,'R');
	$pdf->Cell(28, 4, number_format((double)$total[1]),0,0,'R');
	$pdf->setY(88);
	$i++;
}
if($counter == $row[2]) {$counter+=$row[1];}

//Body Item
while($items =& fetchRow($res_freight, 0)) {
	$pdf->setX(7);
	$pdf->Cell(16, 4, $items[0],0);									// code
	$pdf->Cell(20, 4, substr($items[1],0,8),0);						// item no
	$pdf->Cell(73, 4, substr($items[2],0,40),0);					// description
	$pdf->Cell(17, 4, number_format((double)$items[3]),0,0,'R'); 	// unit price
	$pdf->Cell(14, 4, number_format((double)$items[4]),0,0,'R'); 	// qty
	$pdf->Cell(28, 4, number_format((double)$items[5]),0,0,'R');	// amount
	$pdf->Cell(8);
	$pdf->Cell(0, 4, $items[6],0,1);

	$total[0] += $items[4];
	$total[1] += $items[5];
	$i++;
	if($i == $row[0]) {break;}
}
$counter += $i;

//Foot Total
$total[4] = $total[1];
if($counter >= $row[2] && $i<=$row[4]) {
	if (substr($_code, 0, 2) == "IO" || substr($_code, 0, 2) == "IP") {

		if($_disc > 0) {
			$total[2] = $_disc/100 * $total[1];
			$total[4] = $total[1] - $total[2];
			$total[3] = $_vat_val/100 * $total[4];	
			$total[3] = $_vat_val/100 * $total[4];	

			$pdf->setXY(100, 192);
			$pdf->Cell(35, 3.5, "Sub Total",0,0,"R");
			$pdf->Cell(20);
			$pdf->Cell(22, 3.5, number_format((double)$total[1]), 0,0,'R');
		
			$pdf->setXY(100, $pdf->getY() + 4);
			$pdf->Cell(35, 3.5, "$_disc %             Disc",0,0,"R");
			$pdf->Cell(20);
			$pdf->Cell(22, 3.5, number_format((double)$total[2]), 0,0,'R');
		
			$pdf->setXY(100, $pdf->getY() + 4);
			$pdf->Cell(35, 3.5, "Before VAT",0,0,"R");
			$pdf->Cell(20);
			$pdf->Cell(22, 3.5, number_format((double)$total[4]), 0,0,'R');
		
			$pdf->setXY(100, $pdf->getY() + 4);
			$pdf->Cell(35, 3.5, "$_vat_val %             VAT",0,0,"R");
			$pdf->Cell(20);
			$pdf->Cell(22, 3.5, number_format((double)$total[3]), 0,0,'R');
		} else if($_disc <= 0) {
			$total[3] = $_vat_val/100 * $total[1];
		
			$pdf->setXY(100, 201);
			$pdf->Cell(35, 3.5, "Before VAT",0,0,"R");
			$pdf->Cell(20);
			$pdf->Cell(22, 3.5, number_format((double)$total[1]), 0,0,'R');
		
			$pdf->setXY(100, $pdf->getY() + 4);
			$pdf->Cell(35, 3.5, "$_vat_val %             VAT",0,0,"R");
			$pdf->Cell(20);
			$pdf->Cell(22, 3.5, number_format((double)$total[3]), 0,0,'R');
		}
	} else if (substr($_code, 0, 2) == "IX" && $_disc > 0) {
		$total[2] = $_disc/100 * $total[1];

		$pdf->setXY(100, 201);
		$pdf->Cell(35, 3.5, "Sub Total",0,0,"R");
		$pdf->Cell(20);
		$pdf->Cell(22, 3.5, number_format((double)$total[1]), 0,0,'R');

		$pdf->setXY(100, $pdf->getY() + 4);
		$pdf->Cell(35, 3.5, "$_disc %             Disc",0,0,"R");
		$pdf->Cell(20);
		$pdf->Cell(22, 3.5, number_format((double)$total[2]), 0,0,'R');
	}

	if(!empty($_delivery_freight_charge)) {
		$pdf->setXY(123, 214);
		$pdf->Cell(22, 3.5, "Delivery Cost",0,0,"R");
		$pdf->Cell(32, 3.5, number_format((double)$_delivery_freight_charge),0,0,"R");

		$pdf->setXY(123, $pdf->getY() + 4);
		$pdf->Cell(22, 3.5, "Grand Total",0,0,"R");
		$pdf->Cell(32, 3.5, number_format((double)$total[1] + round($total[3]) - round($total[2]) + $_delivery_freight_charge),0,0,"R");
	}

	$counter += $row[1];
}

$pdf->setXY(135, 209);
$pdf->Cell(14,4, number_format((double)$total[0]), 0, 0,'R');
$pdf->Cell(28,4, number_format((double)$total[1] + round($total[3]) - round($total[2])), 0,0,'R');
?>