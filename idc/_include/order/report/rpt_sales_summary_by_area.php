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
$tmp_item = array();

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

if($cboSearchType != '' && $txtSearch != '') {
	$type = array("byCity"=>"cus_city");
	$tmp[] = $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
	$get[] = "$cboSearchType=$txtSearch";
}

$tmp[]	  = "sl_dept = '$department'";
$strWhere = implode(" AND ", $tmp);

$sql .= "
 it.icat_midx,
 it.it_code,
 it.it_model_no,
 cus_city,
 sum(sl_qty) AS qty,
 sum(sl_payment_price * sl_qty) AS amount
FROM
 ".ZKP_SQL."_tb_customer AS cus
 JOIN ".ZKP_SQL."_tb_sales_log AS sl USING(cus_code)
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE " . $strWhere . "
GROUP BY cug_name, it.icat_midx, it.it_code, it.it_model_no, cus_city
ORDER BY it.icat_midx, it.it_code, it.it_model_no, cus_city";

// raw data
$rd		= array();
$rdIdx	= 0;
$cache	= array("","","");
$group0	= array();
$res	=& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'],			//0
		$col['it_code'],	 		//1
		$col['it_model_no'],		//2
		$col['cus_city'],		  	//3
		$col['qty'],				//4
		$col['amount']			  	//5
	);

	//1st grouping
	if($cache[0] != $col['icat_midx']) {
		$cache[0] = $col['icat_midx'];
		$group0[$col['icat_midx']] = array();
	}

	if($cache[1] != $col['it_code']) {
		$cache[1] = $col['it_code'];
		$group0[$col['icat_midx']][$col['it_code']] = array();
	}

	if($cache[2] != $col['cus_city']) {
		$cache[2] = $col['cus_city'];
	}

	$group0[$col['icat_midx']][$col['it_code']][$col['cus_city']] = 1;
}
/*
echo "<pre>";
var_dump($sql);
echo "</pre>";
exit;
*/
//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$ggTotal = array(0,0,0,0);

//GROUP
foreach ($group0 as $total1 => $group1) {

	//get category path from current icat_midx.
	$path = executeSP(ZKP_SQL."_getCategoryPath", $rd[$rdIdx][0]);
	eval(html_entity_decode($path[0]));	
	$path = array_reverse($path);
	$total1 = $path[1][4]." > ".$path[2][4]." > ".$path[3][4] ;

	echo "<span class=\"comment\"><b> CATEGORY: ". $total1. "</b></span>\n";
	print <<<END
	<table width="70%" class="table_f">
		<tr>
			<th width="25%">MODEL NO</th>
			<th>CITY</th>
			<th width="8%">QTY<br>(EA)</th>
			<th width="15%">AMOUNT<br/>-VAT</th>
			<th width="15%">VAT</th>
			<th width="15%">AMOUNT<br/>+VAT</th>
		</tr>\n
END;
	$gTotal = array(0,0,0,0);
	$print_tr_1 = 0;

	print "<tr>\n";
	//MODEL
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell('['.trim($rd[$rdIdx][1]).'] '.$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"'); //it_model_no

		$total = array(0,0,0,0);
		$print_tr_2 = 0;
		//CITY
		foreach($group2 as $total3 => $group3) {

			if($print_tr_2++ > 0) print "<tr>\n";
			cell(($rd[$rdIdx][3]=='')?'<i class="comment">Undefined</i>':$rd[$rdIdx][3]);								// City
			cell(number_format((double)$rd[$rdIdx][4]), ' align="right"');			// Qty
			cell(number_format((double)$rd[$rdIdx][5]), ' align="right"');			// Amount before vat
			cell(number_format((double)$rd[$rdIdx][5]*1/10), ' align="right"');		// vat
			cell(number_format((double)$rd[$rdIdx][5]*1.1), ' align="right"');		// amount after vat

			$total[0] += $rd[$rdIdx][4];
			$total[1] += $rd[$rdIdx][5];
			$total[2] += round($rd[$rdIdx][5]*1/10);
			$total[3] += round($rd[$rdIdx][5]*1.1);
			$model_name = $rd[$rdIdx][2];
			$rdIdx++;
		}

		print "<tr>\n";
		cell($model_name, ' align="right" style="color:darkblue"');
		cell(number_format((double)$total[0]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$total[1]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$total[2]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$total[3]), ' align="right" style="color:darkblue"');
		print "</tr>\n";

		$gTotal[0] += $total[0];
		$gTotal[1] += $total[1];
		$gTotal[2] += $total[2];
		$gTotal[3] += $total[3];
	}
	print "<tr>\n";
	cell("<b>TOTAL $total1</b>", ' colspan="2" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$gTotal[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$gTotal[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$gTotal[2]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$gTotal[3]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";
	
	$ggTotal[0] += $gTotal[0];
	$ggTotal[1] += $gTotal[1];
	$ggTotal[2] += $gTotal[2];
	$ggTotal[3] += $gTotal[3];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
	<table width="70%" class="table_f">
		<tr>
			<th width="25%">MODEL NO</th>
			<th>CITY</th>
			<th width="8%">QTY<br>(EA)</th>
			<th width="15%">AMOUNT<br/>-VAT</th>
			<th width="15%">VAT</th>
			<th width="15%">AMOUNT<br/>+VAT</th>
		</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="2" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$ggTotal[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$ggTotal[1]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$ggTotal[2]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$ggTotal[3]), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br />\n";
?>