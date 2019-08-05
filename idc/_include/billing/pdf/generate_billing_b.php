<?php
//INVOICE ========================================================================================================
$counter = array(0,0);					//0. line, 1.query
$total	= array(0,0,0,0,0);				//0. total qty, 1. total amount, 2. total disc, 3. total vat, 4. total after disc = total amount - total disc
if($_code == 'IO-05647A-G16') {
	$row	= array(19, 0, $row_cus, 0, 0); //0. default limit, 1. adding row, 2.jumlah item, 3.jumlah total baris, 4.-
} else {
	$row	= array(20, 0, $row_cus, 0, 0); //0. default limit, 1. adding row, 2.jumlah item, 3.jumlah total baris, 4.-
}
if($_vat_val>0) {
	if($_disc>0) $row[1]=4;
	else		 $row[1]=2;
} else {
	if($_disc>0) $row[1]=2;
}
$row[3] = $row[1]+$row[2];
$row[4] = $row[0]-$row[1];
$page = array(1, ceil($row[3] / $row[0]));

while ($counter[0] < $row[3]) {
	include "generate_billing_invoice_med.php";
	$page[0]++;
}

//FAKTUR PAJAK ===================================================================================================
if($_vat_val > 0) {
	$counter= array(0,1);					// counter, no item
	$total	= array(0,0,0);					// row 1, 2, 4
	$row	= array(15, 0, $row_cus);		//0. default limit, 1. adding row, 2.jumlah item, 3.jumlah total baris, 4.-
	$tpl_pajak_pdf = "tpl_faktur_pajak_1_med.pdf";
	while ($counter[0] < $row[2]) {
		include "generate_billing_faktur_pajak_manual.php";
	}
	$counter= array(0,1);					// counter, no item
	$total	= array(0,0,0);					// row 1, 2, 4
	$row	= array(15, 0, $row_cus);		//0. default limit, 1. adding row, 2.jumlah item, 3.jumlah total baris, 4.-
	$tpl_pajak_pdf = "tpl_faktur_pajak_2_med.pdf";
	while ($counter[0] < $row[2]) {
		include "generate_billing_faktur_pajak_manual.php";
	}
}

//OTHERS DOCUMENT ================================================================================================
if($_type_invoice == 0) {
	//DO WAREHOUSE ===============================================================================================
	$counter = array(0,0);
	$qty = array(0,0);
	$row = array(22, 22, $row_wh, $row_cus); //0.wh limit, 1.cus limit, 2.total wh, 3.total cus
	$big = ($row_wh>$row_cus) ? 0 : 1;
	$page = array(1, ceil($row[$big+2] / $row[$big]));
	while ($counter[$big] < $row[$big+2]) {
		include "generate_billing_warehouse_pdf.php";
		$page[0]++;
	}

	//SURAT JALAN ================================================================================================
	$counter = 0;
	$qty = 0;
	$row = array(26, $row_cus); //0.limit, 1.total item
	$page = array(1, ceil($row[1] / $row[0]));
	while ($counter < $row[1]) {
		include "generate_billing_sj_med.php";
		$page[0]++;
	}

	//INSURANCE ==================================================================================================
	$counter = 0;
	$total = array(0,0,0,0);		//0. total qty, 1. total amount, 2. total disc, 3. total vat, 4. total after disc = total amount - total disc
	$row = array(20, 0, $row_frt);	//0.limit, 1.total item
	if($_vat_val>0) {
		if($_disc>0) $row[1]=4;
		else		 $row[1]=2;
	} else {
		if($_disc>0) $row[1]=2;
	}
	$row[3] = $row[1]+$row[2];
	$row[4] = $row[0]-$row[1];
	$page = array(1, ceil($row[3] / $row[0]));

	while ($counter < $row[3]) {
		include "generate_billing_ekspedisi_med.php";
		$page[0]++;
	}
}
?>