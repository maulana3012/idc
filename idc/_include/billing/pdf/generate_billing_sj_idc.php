<?php
$pdf->setSourceFile(APP_DIR . "_include/billing/template_pdf/" . $tpl_pdf[2]);
$tplidx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplidx, -2,5.5,0,0);
$pdf->setFont('Arial', '', 10);

//Header
$pdf->setFont('Arial', 'B', 15);
$pdf->setXY(8, 20);
$pdf->Cell(100,10, $company[0]);
$pdf->setFont('Arial', '', 9);
$pdf->setXY(8, 29);
$pdf->Cell(170, 3.5, $company[1]);
if(isset($company[2])) {
	$pdf->setXY(8, 33);
	$pdf->Cell(170, 3.5, implode('   ', $company[2]));
}

/* 1. */	if($page[1] > 1) {
				$pdf->setFont('Arial', 'I', 8);
				$pdf->setXY(32, 44);
				$pdf->Cell(20,6,"page {$page[0]} / {$page[1]}",0,0,'C');
			}
			$pdf->setFont('Arial', '', 10);
			$pdf->setXY(180,44);
			$pdf->Cell(25,6,"CUSTOMER",0,0,'C');
/* 2. */	$pdf->setXY(22,55);
			$pdf->Cell(158, 4, $_cus_name);
			$pdf->Cell(0, 4, "J". substr($_code,1,1). substr($_code, 2));
			$pdf->setXY(22,59);
			$pdf->Cell(158, 4, $_cus_address);
			if(empty($_sj_date)) $pdf->Cell(0, 4, date("j-M-Y", strtotime($_inv_date)));
			else $pdf->Cell(0, 4, date("j-M-Y", strtotime($_sj_date)));
/* 3. */	$pdf->setXY(22,63);
			if($cus[0] != '' && $cus[1] != '') $pdf->Cell(158, 4, "Telp : ".$cus[0]."  Fax : ".$cus[1]);
			else if($cus[0] != '' && $cus[1] == '') $pdf->Cell(158, 4, "Telp : ".$cus[0]);
			else if($cus[0] == '' && $cus[1] != '') $pdf->Cell(158, 4, "Fax : ".$cus[1]);
/* 4. */	$pdf->setXY(22,68);
			$pdf->Cell(98, 4, $_cus_attn);
			$pdf->Cell(0, 4, $_received_by);
/* 5. */	$pdf->setXY(33,205);
			$pdf->Cell(6, 4, ($_delivery_chk & 1) ? "X":"");	// ex Whouse
			$pdf->Cell(38);
			$pdf->Cell(6, 4, $_delivery_franco); 				// Franco(P/D)
			$pdf->Cell(35);
			$pdf->Cell(23, 4, $_delivery_by); 					// Delivered by
			$pdf->Cell(4, 4, ($_delivery_chk & 4) ? "X":"");	// Freight charge
			if($_delivery_freight_charge > 0) {
				$pdf->Cell(10);
				$pdf->Cell(4, 4, "X");
				$pdf->Cell(26);
				$pdf->Cell(0, 4, 'Rp. '.number_format((double)$_delivery_freight_charge));
			}
/* 6. */	$pdf->setXY(24, 210);
			$pdf->Cell(74, 4, '['.trim($_ship_to).'] '.substr($_ship_name, 0, 48));
			$pdf->setXY(24, 214);
			$pdf->Cell(64, 4, $cus[2]);
			$pdf->setXY(24, 218);
			if($cus[3] != '' && $cus[4] != '') $pdf->Cell(158, 4, "Telp : ".$cus[3]."  Fax : ".$cus[4]);
			else if($cus[3] != '' && $cus[4] == '') $pdf->Cell(158, 4, "Telp : ".$cus[3]);
			else if($cus[3] == '' && $cus[4] != '') $pdf->Cell(158, 4, "Fax : ".$cus[4]);
/* 7. */	$pdf->setXY(24,227);
			$pdf->MultiCell(170, 3.5, $_remark);
/* 8. */	$pdf->setFont('Arial', 'I', 8);
			$pdf->setY(242);
			$pdf->Cell(198, 4, "Created by ".$_lastupdated_by_account.date(', j-M-Y g:i:s')." Rev:".($_revision_time)."   ", 0,0,'R');
			$pdf->setFont('Arial', '', 10);
/* 9. */	$pdf->setXY(12, 265);
			$pdf->Cell(36,4,$_signature_by,0,0,"C");

/* 10. */	$pdf->setXY(80, 252);
		$pdf->setFont('Arial', 'B', 12);
		$pdf->Cell(55, 6, "P E R H A T I A N", 1,0,'C');
		$pdf->setXY(80, 260);
		$pdf->setFont('Arial', '', 10);
		$pdf->MultiCell(55, 4.5, "Tidak Menerima Complain Selisih Barang Setelah 7 Hari dari Tanggal Terima Barang", 1,1);

$i = 0;
pg_result_seek($result, $counter);

if($counter==0) {
	$pdf->setY(82);
} else {
	$pdf->setXY(7,82);
	$pdf->Cell(136, 4, "Previous balance");
	$pdf->Cell(10, 4, number_format((double)$qty),0,0,'R');
	$pdf->setY(86);
	$i++;
}
while($items =& fetchRow($result, 0)) {
	$pdf->setX(7);
	$pdf->Cell(16, 4, $items[0],0);									// code
	$pdf->Cell(20, 4, substr($items[1],0,8),0); 					// item no
	$pdf->Cell(80, 4, substr($items[2],0,50),0);					// description
	$pdf->Cell(20);
	$pdf->Cell(10, 3.5, number_format((double)$items[4]),0,0,'R');	// qty
	$pdf->Cell(5);
	$pdf->Cell(30, 3.5, $items[6],0,1);								// remark

	$qty += $items[4];
	$i++;
	if($i == $row[0]) {break;}
}
$counter += $i;

$pdf->setXY(146,196);
$pdf->Cell(7,4, number_format((double)$qty), 0, 0,'R');
?>