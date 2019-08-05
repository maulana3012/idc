<?php
//ORDER SHEET =========================================================================================================
$counter = 0;
$total 	 = array(0,0);				//0.total qty, 1.total amount
$row	 = array(31, 0, $row_cus);	//0.limit, 1.total item
$page 	 = array(1, ceil($row[2] / $row[0]));
while ($counter < $row[2]) {
	include "generate_order_sheet_med.php";
	$page[0]++;
}

if($_type_invoice == 0) {
	//DO WAREHOUSE ====================================================================================================
	$counter = array(0,0);
	$total	 = array(0,0);				//0.total qty, 1.total amount
	$qty	 = array(0,0);
	$row 	 = array(20, 22, $row_wh, $row_cus);	//0.wh limit, 1.cus limit, 2.total wh, 3.total cus
	$big	 = ($row_wh>$row_cus) ? 0 : 1;
	$page 	 = array(1, ceil($row[$big+2] / $row[$big]));
	while ($counter[$big] < $row[$big+2]) {
		include "generate_order_do_pdf.php";
		$page[0]++;
	}

	//SJ ==================================================================================================================
	$counter = 0;
	$total 	 = array(0,0);				//0.total qty, 1.total amount
	$row	 = array(29, 0, $row_cus);	//0.limit, 1.total item
	$qty	 = 0;
	$page 	 = array(1, ceil($row[2] / $row[0]));
	while ($counter < $row[2]) {
		include "generate_order_sj_med.php";
		$page[0]++;
	}
}
?>