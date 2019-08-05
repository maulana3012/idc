<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim
*
*
*/
//SET WHERE PARAMETER
$tmp_bill	= array();
$tmp_turn	= array();
$tmp_bill_month	= array();
$tmp_turn_month	= array();

if(ZKP_FUNCTION == 'ALL') {
	if($_order_by != 'all'){
		$tmp_bill[]			= "bill_ordered_by = $_order_by";
		$tmp_bill_month[]	= "bill_ordered_by = $_order_by";
		$tmp_turn[]			= "turn_ordered_by = $_order_by";
		$tmp_turn_month[]	= "turn_ordered_by = $_order_by";
	}
} else {
	$tmp_bill[]			= "bill_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill_code,'billing')";
	$tmp_bill_month[]	= "bill_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill_code,'billing')";
	$tmp_turn[]			= "turn_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', turn_code,'billing_return')";
	$tmp_turn_month[]	= "turn_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', turn_code,'billing_return')";
}

if ($_filter_doc == "I") {
	$tmp_turn[] = "turn_code is null";
	$tmp_turn_month[] = "turn_code is null";
} else if ($_filter_doc == "R") {
	$tmp_bill[] = "bill_code is null";
	$tmp_bill_month[] = "bill_code is null";
}

if($_filter_vat == 'vat') {
	$tmp_bill[] = "bill_vat > 0";
	$tmp_turn[] = "turn_vat > 0";
	$tmp_bill_month[] = "bill_vat > 0";
	$tmp_turn_month[] = "turn_vat > 0";
} else if ($_filter_vat == 'non') {
	$tmp_bill[] = "bill_vat = 0";
	$tmp_turn[] = "turn_vat = 0";
	$tmp_bill_month[] = "bill_vat = 0";
	$tmp_turn_month[] = "turn_vat = 0";
}

if($_filter_dept != 'all') {
	$tmp_bill[] = "bill_dept = '$_filter_dept'";
	$tmp_turn[] = "turn_dept = '$_filter_dept'";
	$tmp_bill_month[] = "bill_dept = '$_filter_dept'";
	$tmp_turn_month[] = "turn_dept = '$_filter_dept'";
}

$tmp_bill[]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to' AND bill_type_billing in (1,2)";
$tmp_turn[]	= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
$tmp_turn_month[]	= "turn_return_condition != 1";

$strWhereBill  	= implode(" AND ", $tmp_bill);
$strWhereTurn 	= implode(" AND ", $tmp_turn);
$strWhereBillMonth	= implode(" AND ", $tmp_bill_month);
$strWhereTurnMonth	= implode(" AND ", $tmp_turn_month);

$sp_sql = "
SELECT 
  CASE
    WHEN icat_midx IN(1,70,116,146,176,240,125,59) THEN 'Healthcare Tools'
    WHEN icat_midx IN(46,232,282,252,109) THEN 'Diagnostic Tools'
	ELSE 'Others'
  END AS cat,
  icat_midx, 
  icat_name 
FROM ".ZKP_SQL."_tb_item_cat WHERE icat_pidx = 0 AND icat_midx > 0 ORDER BY icat_code";
$sp_res =& query($sp_sql);
$group0 = array();
$group1 = array();
while($col =& fetchRow($sp_res)) {
	$sp[$col[1]] = array();
	$sp[$col[1]][0] = $col[1];
	$sp[$col[1]][1] = trim($col[2]);
	$sp[$col[1]][2] = $col[0];
	for($i=3; $i<$mon_length+5; $i++) $sp[$col[1]][$i] = 0;
	$group0[] = $col[0];
	$group1[$col[0]][] = array($col[1], $col[2]);
}
$group0 = array('Healthcare Tools','Diagnostic Tools','Others');

foreach($sp as $key => $val) {
	for($i=0; $i<$mon_length; $i++) {

		if($start_month>12) { $start_month = $start_month-12; $start_year+=1;}
		$whereBillMonth = ($strWhereBillMonth=='') ? "" : "$strWhereBillMonth AND ";
		$whereTurnMonth = ($strWhereTurnMonth=='') ? "" : "$strWhereTurnMonth AND ";
		$catList = executeSP(ZKP_SQL."_getSubCategory", $key);

		$sql_perMonth = 
			"SELECT trunc(sum(biit_qty * (biit_unit_price*(100-bill_discount)/100))) AS amount
			FROM
				".ZKP_SQL."_tb_billing
				JOIN ".ZKP_SQL."_tb_billing_item USING(bill_code)
				JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
				JOIN ".ZKP_SQL."_tb_item USING(it_code)
			WHERE $whereBillMonth bill_inv_date ".$period_month[$i]." AND icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")
		  UNION
			SELECT trunc(sum(reit_qty * (reit_unit_price*(100-turn_discount)/100)))*-1 AS amount
			FROM
				".ZKP_SQL."_tb_return
				JOIN ".ZKP_SQL."_tb_return_item USING(turn_code)
				JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
				JOIN ".ZKP_SQL."_tb_item USING(it_code)
			WHERE $whereTurnMonth turn_return_date ".$period_month[$i]." AND icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")
			";
/*
echo "<pre>";
echo $sql_perMonth. "<br /><br />";
echo "</pre>";
*/
		$res_month =& query($sql_perMonth);
		while($col_month =& fetchRow($res_month)) {
			$sp[$key][3+$i] += $col_month[0];
			$sp[$key][3+$mon_length] += $col_month[0];
		}
	}
	$sp[$key][3+$mon_length+1] = $sp[$key][3+$mon_length] / $mon_length;
}

//SUPPLIER PART
$g_total = array();
print <<<END
<table class="table_f" width="{$table_len}px">
	<tr height="15px">
		<th width="120px" rowspan="2">CATEGORY</th>
		<th colspan="$mon_length">MONTH</th>
		<th width="100px" rowspan="2">TOTAL</th>
		<th width="100px" rowspan="2">AVG</th>
	</tr>
	<tr height="25px">\n
END;
	$start_month = $_month_from;
	$start_year	 = $_year_from;
	for($i=0; $i<$mon_length; $i++) {
		if($start_month>12) { $start_month = $start_month-12; $start_year+=1;}
		echo "\t\t<th width=\"80px\">".$month[$start_month]."<br />$start_year</th>\n";
		$start_month++;
	}
print <<<END
	</tr>\n
END;
foreach ($group0 as $part1 => $part_name) {
print <<<END
	<tr>
		<td colspan="$mon_length"><span class="comment"><b>$part_name</b></span></td>
	</tr>\n
END;

	$tot_sp = count($group1[$part_name]);
	$total = array();
	print "<tr>\n";
	for($i=0; $i<$tot_sp; $i++) {
		$sub = array($group1[$part_name][$i][0], $group1[$part_name][$i][1]);

		cell($sub[1]);
		for($j=3; $j<$mon_length+5; $j++) {
			cell(number_format((double)$sp[$sub[0]][$j]), ' align="right"');
			if(!isset($total[$part1][$j]))	$total[$part1][$j] = $sp[$sub[0]][$j];
			else					 			$total[$part1][$j] += $sp[$sub[0]][$j];
		}
		print "</tr>\n";
	}

	print "<tr>\n";
	cell("<b>TOTAL $part_name</b>", ' align="right" style="color:brown; background-color:lightyellow"');
	for($j=3; $j<$mon_length+3; $j++) {
		cell(number_format((double)$total[$part1][$j]), ' align="right" style="color:brown; background-color:lightyellow"');

		if(!isset($g_total[$j]))	$g_total[$j] = $total[$part1][$j];
		else					 	$g_total[$j] += $total[$part1][$j];
	}
	cell(number_format((double)$total[$part1][$j]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$total[$part1][$j+1]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";

	if(!isset($g_total[$j])) {
		$g_total[$j] = $total[$part1][$j];
		$g_total[$j+1] = $total[$part1][$j+1];
	} else {
		$g_total[$j] += $total[$part1][$j];
		$g_total[$j+1] += $total[$part1][$j+1];
	}
}

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' align="right" style="color:brown; background-color:lightyellow"');
for($j=3; $j<$mon_length+3; $j++) {
	cell(number_format((double)$g_total[$j]), ' align="right" style="color:brown; background-color:lightyellow"');
}
cell(number_format((double)$g_total[$j]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$g_total[$j+1]), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br />";
?>