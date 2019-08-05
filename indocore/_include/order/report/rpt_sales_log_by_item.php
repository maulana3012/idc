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

if ($_cus_code != 'all') {
	$tmp[] = "cus_code = '$_cus_code'";
}

if ($some_date != "") {
	$tmp[] = "sl_date = DATE '$some_date'";
} else {
	$tmp[] = "sl_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
}

$tmp[]	  = "sl_dept = '$department'";
$strWhere = implode(" AND ", $tmp);

$sql = "
SELECT
 it.icat_midx,
 it.it_model_no,
 to_char(sl_date, 'dd-Mon-YY') AS sl_date,
 sl_idx,
 sl_add_disc,
 sl_debit_price,
 sl_payment_price,
 sl_qty,
 sl_debit_price * sl_qty AS sl_amount,
 round((sl_payment_price - sl_debit_price) / sl_payment_price * 100) AS sl_diff,
 CASE WHEN sl_payment_price - sl_debit_price < 0 THEN sl_debit_price - sl_payment_price ELSE 0 END * sl_qty AS sl_lesspay,
 CASE WHEN sl_payment_price - sl_debit_price > 0 THEN sl_payment_price - sl_debit_price ELSE 0 END * sl_qty AS sl_overpay,
 sl_remark
FROM
  ".ZKP_SQL."_tb_item_cat AS icat
  JOIN ".ZKP_SQL."_tb_item AS it USING(icat_midx)
  JOIN ".ZKP_SQL."_tb_sales_log AS sl USING(it_code)
WHERE " . $strWhere . "
ORDER BY icat.icat_pidx, it.icat_midx, it.it_code, sl_date DESC";

// raw data
$rd = array();
$rdIdx = 0;
$cache = array("","","","");
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'],			//0
		$col['it_model_no'],		//1
		$col['sl_date'],			//2
		$col['sl_add_disc'],		//3
		$col['sl_debit_price'],		//4
		$col['sl_payment_price'],	//5
		$col['sl_qty'], 			//6
		$col['sl_amount'], 			//7
		$col['sl_diff'], 			//8
		$col['sl_lesspay'], 		//9
		$col['sl_overpay'], 		//10
		$col['sl_remark'], 			//11
		$col['sl_idx'] 				//12
	);

	//1st grouping
	if($cache[0] != $col['icat_midx']) {
		$cache[0] = $col['icat_midx'];
		$group0[$col['icat_midx']] = array();
	}

	if($cache[1] != $col['it_model_no']) {
		$cache[1] = $col['it_model_no'];
		$group0[$col['icat_midx']][$col['it_model_no']] = array();
	}

	if($cache[2] != $col['sl_idx']) {
		$cache[2] = $col['sl_idx'];
	}

	$group0[$col['icat_midx']][$col['it_model_no']][$col['sl_idx']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$ggTot = array(0,0,0,0);

//GROUP
foreach ($group0 as $total1 => $group1) {

	//get category path from current icat_midx.
	$path = executeSP(ZKP_SQL."_getCategoryPath", $rd[$rdIdx][0]);
	eval(html_entity_decode($path[0]));	
	$path = array_reverse($path);
	$total1 = $path[1][4]." > ".$path[2][4]." > ".$path[3][4] ;

	echo "<span class=\"comment\"><b> CATEGORY: ". $total1. "</b></span>\n";
	print <<<END
	<table width="100%" class="table_c">
		<tr>
			<th width="15%">MODEL NO</th>
			<th width="8%">SALES<br>DATE</th>
			<th>ADD<br>(%)</th>
			<th>@PRICE<br/>(Rp)</th>
			<th>S/QTY<br>(EA)</th>
			<th width="12%">AMOUNT<br>(Rp)</th>
			<th width="10%">OVER PAY<br>(Rp)</th>
			<th>REMARK</th>
			<th width="10%">@REPORT<br/>(Rp)</th>
			<th>DEDUCT<br>(%)</th>
			<th width="10%">DEDUCT<br>(Rp)</th>
		</tr>\n
END;

	//GORUP TOTAL
	$gTot = array(0,0,0,0);
	print "<tr>\n";
	//MODEL
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell($rd[$rdIdx][1], ' valign="top" rowspan="'.$rowSpan.'"');

		//MODEL TOTAL
		$tot = array(0,0,0,0);
		//LOGS
		foreach($group2 as $total3 => $group3) {
			if($print_tr_2++ > 0) print "<tr>\n";

			cell_link($rd[$rdIdx][2],
				' align="center"',
				" href=\"javascript:openWindow('./p_detail_sales_log.php?idx={$rd[$rdIdx][12]}', 450, 250);\""
			);

			cell($rd[$rdIdx][3]."%", ' align="right"');
			cell(number_format((double)$rd[$rdIdx][4]), ' align="right"');
			cell(number_format((double)$rd[$rdIdx][6]), ' align="right"');
			cell(number_format((double)$rd[$rdIdx][7]), ' align="right"');
			cell(number_format((double)$rd[$rdIdx][10]), ' align="right"');
			cell($rd[$rdIdx][11]);
			cell(number_format((double)$rd[$rdIdx][5]), ' align="right"');
			cell($rd[$rdIdx][8].'%', ' align="right"');
			cell(number_format((double)$rd[$rdIdx][9]), ' align="right"');
			print "</tr>\n";

			$tot[0] += $rd[$rdIdx][6];
			$tot[1] += $rd[$rdIdx][7];
			$tot[2] += $rd[$rdIdx][9];
			$tot[3] += $rd[$rdIdx][10];
			$rdIdx++;
		}

		print "<tr>\n";
		cell("$total2", ' colspan="3" align="right" style="color:darkblue"');
		cell(number_format((double)$tot[0]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$tot[1]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$tot[2]), ' align="right" style="color:darkblue;"');
		cell("&nbsp;", ' colspan="3"');
		cell(number_format((double)$tot[3]), ' align="right" style="color:darkblue;"');
		print "</tr>\n";

		for($i=0; $i<4; $i++) {
			$gTot[$i] += $tot[$i];
		}
	}

	print "<tr>\n";
	cell("<b>TOTAL $total1</b>", ' colspan="4" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$gTot[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$gTot[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$gTot[2]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell("&nbsp;", ' colspan="3" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$gTot[3]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";
	
	for($i=0; $i<4; $i++) {
		$ggTot[$i] += $gTot[$i];
	}
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_c">
	<tr>
		<th width="15%">MODEL NO</th>
		<th width="8%">SALES<br>DATE</th>
		<th>ADD<br>(%)</th>
		<th>@PRICE<br/>(Rp)</th>
		<th>S/QTY<br>(EA)</th>
		<th width="12%">AMOUNT<br>(Rp)</th>
		<th width="10%">OVER PAY<br>(Rp)</th>
		<th>REMARK</th>
		<th width="10%">@REPORT<br/>(Rp)</th>
		<th>DEDUCT<br>(%)</th>
		<th width="10%">DEDUCT<br>(Rp)</th>
	</tr>\n
END;
	print "<tr>\n";
	cell("<b>GRAND TOTAL</b>", ' colspan="4" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$ggTot[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$ggTot[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$ggTot[2]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell("&nbsp;", ' colspan="3" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$ggTot[3]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";
?>