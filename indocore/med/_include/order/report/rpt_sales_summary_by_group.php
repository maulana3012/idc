<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim
*
* $_search_date : Inquire Date
*/

$tmp = array();

//SET WHERE PARAMETER
if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if ($_cug_code != 'all') {
	$tmp[]		= "cus.cus_code IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$sql 		= " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
} else {
	$sql = "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = cus.cus_code),
		'Others') AS cug_name,";
}

if ($_cus_code != "") {
	$tmp[]	= "cus_code = '$_cus_code'";
}

if ($some_date != "") {
	$tmp[] = "sl_date = DATE '$some_date'";
} else {
	$tmp[] = "sl_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
}

if($_marketing != "all") {
	$tmp[]	= "sl_cus_to_responsible_by = $_marketing";
}

if($_document == "sales") {
	$tmp[]	= "sl_qty > 0";
} else if($_document == "return") {
	$tmp[]	= "sl_qty < 0";
}

if($cboSearchType != '' && $txtSearch != '') {
	$type = array("byCity"=>"cus_city");
	$tmp[] = $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
	$get[] = "$cboSearchType=$txtSearch";
}

$tmp[]	  = "sl_dept = '$department'";

$strWhere = implode(" AND ", $tmp);

$sql .= "
 cus.cus_code,
 cus.cus_full_name,
 it.it_code,
 it.it_model_no,
 sl_idx,
 to_char(sl_date, 'dd-Mon-YY') AS sl_date,
 sl_add_disc,
 sl_debit_price,
 sl_payment_price,
 sl_qty,
 sl_payment_price * sl_qty AS sl_report_amount,
 sl_debit_price * sl_qty AS sl_amount,
 /*round((sl_payment_price - sl_debit_price) / sl_debit_price * 100)*/null AS sl_diff,
 (CASE WHEN sl_payment_price - sl_debit_price < 0 THEN sl_debit_price - sl_payment_price ELSE 0 END) * sl_qty AS sl_lesspay,
 (CASE WHEN sl_payment_price - sl_debit_price > 0 THEN sl_payment_price - sl_debit_price ELSE 0 END) * sl_qty AS sl_overpay,
 sl_faktur_no,
 sl_lop_no,
 sl_remark
FROM
 ".ZKP_SQL."_tb_customer AS cus
 JOIN ".ZKP_SQL."_tb_sales_log AS sl USING(cus_code)
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE " . $strWhere . "
ORDER BY cug_name, cus.cus_code, it.it_code, sl_date DESC";

$rd		= array();
$rdIdx	= 0;
$cache	= array("","","","");
$group0	= array();
$res	=& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['cug_name'], 			//0
		$col['cus_code'],		  	//1
		$col['cus_full_name'],  	//2
		$col['it_code'],	 		//3
		$col['it_model_no'], 		//4
		$col['sl_date'],			//5
		$col['sl_faktur_no'],		//6
		$col['sl_lop_no'],			//7
		$col['sl_add_disc'],		//8
		$col['sl_remark'],			//9
		$col['sl_debit_price'], 	//10
		$col['sl_payment_price'], 	//11
		$col['sl_qty'],				//12
		$col['sl_amount'],			//13
		$col['sl_report_amount'],	//14
		$col['sl_diff'],			//15
		$col['sl_overpay'],			//16
		$col['sl_lesspay'],			//17
		$col['sl_idx']				//18
	);

	//1st grouping
	if($cache[0] != $col['cug_name']) {
		$cache[0] = $col['cug_name'];
		$group0[$col['cug_name']] = array();
	}

	if($cache[1] != $col['cus_code']) {
		$cache[1] = $col['cus_code'];
		$group0[$col['cug_name']][$col['cus_code']] = array();
	}

	if($cache[2] != $col['it_code']) {
		$cache[2] = $col['it_code'];
		$group0[$col['cug_name']][$col['cus_code']][$col['it_code']] = array();
	}

	if($cache[3] != $col['sl_idx']) {
		$cache[3] = $col['sl_idx'];
	}

	$group0[$col['cug_name']][$col['cus_code']][$col['it_code']][$col['sl_idx']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$ggTotal = array(0,0,0,0,0,0,0);

//GROUP
foreach ($group0 as $total1 => $group1) {

	echo "<span class=\"comment\"><b> PUSAT: ". $total1. "</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th width="15%">CUSTOMER</th>
			<th width="15%">MODEL NO</th>
			<th width="12%">SALES<br>DATE</th>
			<th>ADD<br>DISC</th>
			<th>REMARK</th>
			<th width="10%">FAKTUR /<br />BILL NO</th>
			<th width="8%">LOP NO</th>
			<th width="8%">@PRICE</th>
			<th width="5%">S/QTY<br>(EA)</th>
			<th width="8%">AMOUNT<br/>-VAT</th>
			<th width="8%">VAT</th>
			<th width="8%">AMOUNT<br/>+VAT</th>
			<th width="8%">AMOUNT<br/>-VAT<br />(Ref)</th>
			<!--<th width="7%">OVER</th>
			<th width="7%">DEDUCT</th>
			<th width="5%">+ / -<br />( % )</th>-->
		</tr>\n
END;

	print "<tr>\n";

	$gTotal = array(0,0,0,0,0,0,0);
	$print_tr_1 = 0;
	//CUSTOMER
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell('['.trim($rd[$rdIdx][1]).'] '.$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"'); // customer

		$total = array(0,0,0,0,0,0,0);
		$print_tr_2 = 0;
		//MODEL
		foreach($group2 as $total3 => $group3) {
			$rowSpan = 0;
			array_walk_recursive($group3, 'getRowSpan');

			if($print_tr_2++ > 0) print "<tr>\n";
			cell('['.trim($rd[$rdIdx][3]).'] '.$rd[$rdIdx][4], ' valign="top" rowspan="'.$rowSpan.'"'); //it_model_no
			$print_tr_3 = 0;

			//LOGS
			foreach($group3 as $group4) {
				if($print_tr_3++ > 0) print "<tr>\n";

				cell_link($rd[$rdIdx][5], ' align="center"',
					" href=\"javascript:openWindow('./p_detail_sales_log.php?idx={$rd[$rdIdx][18]}', 450, 250);\""); //sl_date
				cell(($rd[$rdIdx][8]<=0)?'':$rd[$rdIdx][8].'%', ' align="right"'); // sl_add_disc
				cell($rd[$rdIdx][9]); // sl_remark
				cell($rd[$rdIdx][6]); // sl_faktur_no
				cell($rd[$rdIdx][7]); // sl_lop_no
				cell(number_format((double)$rd[$rdIdx][11]), ' align="right"');			//unit_price
				cell(number_format((double)$rd[$rdIdx][12]), ' align="right"');			//qty
				cell(number_format((double)$rd[$rdIdx][14]), ' align="right"');			//amount before vat
				cell(number_format((double)$rd[$rdIdx][14]*1/10), ' align="right"');	//vat
				cell(number_format((double)$rd[$rdIdx][14]*1.1), ' align="right"');		//amount after vat
				cell(number_format((double)$rd[$rdIdx][13]), ' align="right"');			//ref
				/*cell(number_format((double)$rd[$rdIdx][16]), ' align="right"');			//over
				cell(number_format((double)$rd[$rdIdx][17]), ' align="right"');			//lest
				cell($rd[$rdIdx][15].'%', ' align="right"');					//diff*/
				print "</tr>\n";

				$total[0] += $rd[$rdIdx][12];
				$total[1] += $rd[$rdIdx][14];
				$total[2] += round($rd[$rdIdx][14]*1/10);
				$total[3] += round($rd[$rdIdx][14]*1.1);
				$total[4] += $rd[$rdIdx][13];
				$total[5] += $rd[$rdIdx][16];
				$total[6] += $rd[$rdIdx][17];
				$customer_name = $rd[$rdIdx][2];
				$rdIdx++;
			}
		}

		print "<tr>\n";
		cell($customer_name, ' colspan="7" align="right" style="color:darkblue"');
		cell(number_format((double)$total[0]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$total[1]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$total[2]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$total[3]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$total[4]), ' align="right" style="color:darkblue"');
		/*cell(number_format((double)$total[5]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$total[6]), ' align="right" style="color:darkblue"');
		cell("&nbsp;");*/
		print "</tr>\n";

		$gTotal[0] += $total[0];
		$gTotal[1] += $total[1];
		$gTotal[2] += $total[2];
		$gTotal[3] += $total[3];
		$gTotal[4] += $total[4];
		$gTotal[5] += $total[5];
		$gTotal[6] += $total[6];
	}
	
	print "<tr>\n";
	cell("<b>TOTAL $total1</b>", ' colspan="8" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$gTotal[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$gTotal[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$gTotal[2]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$gTotal[3]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$gTotal[4]), ' align="right" style="color:brown; background-color:lightyellow"');
	/*cell(number_format((double)$gTotal[5]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$gTotal[6]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell("&nbsp;", ' style="color:brown; background-color:lightyellow"');*/
	print "</tr>\n";
	print "</table><br />\n";

	$ggTotal[0] += $gTotal[0];
	$ggTotal[1] += $gTotal[1];
	$ggTotal[2] += $gTotal[2];
	$ggTotal[3] += $gTotal[3];
	$ggTotal[4] += $gTotal[4];
	$ggTotal[5] += $gTotal[5];
	$ggTotal[6] += $gTotal[6];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th width="15%">CUSTOMER</th>
			<th width="15%">MODEL NO</th>
			<th width="12%">SALES<br>DATE</th>
			<th>ADD<br>DISC</th>
			<th>REMARK</th>
			<th width="10%">FAKTUR /<br />BILL NO</th>
			<th width="8%">LOP NO</th>
			<th width="8%">@PRICE</th>
			<th width="5%">S/QTY<br>(EA)</th>
			<th width="8%">AMOUNT<br/>-VAT</th>
			<th width="8%">VAT</th>
			<th width="8%">AMOUNT<br/>+VAT</th>
			<th width="8%">AMOUNT<br/>-VAT<br />(Ref)</th>
			<!--<th width="7%">OVER</th>
			<th width="7%">DEDUCT</th>
			<th width="5%">+ / -<br />( % )</th>-->
		</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="8" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$ggTotal[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$ggTotal[1]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$ggTotal[2]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$ggTotal[3]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$ggTotal[4]), ' align="right" style="color:brown; background-color:lightyellow"');
/*cell(number_format((double)$ggTotal[5]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$ggTotal[6]), ' align="right" style="color:brown; background-color:lightyellow"');
cell("&nbsp;", ' style="color:brown; background-color:lightyellow"');*/
print "</tr>\n";
print "</table><br />\n";
?>