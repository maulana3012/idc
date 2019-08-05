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
$tmp_dr		= array();
$tmpbill_item	= array();
$tmpturn_item	= array();
$tmpdr_item		= array();

if(ZKP_FUNCTION == 'ALL') {
	if($_order_by != 'all'){
		$tmp_bill[]	= "bill_ordered_by = $_order_by";
		$tmp_turn[] = "turn_ordered_by = $_order_by";
		$tmp_dr[]	= "dr_ordered_by = $_order_by";
		$tmpbill_item[]	= "bill.bill_ordered_by = $_order_by";
		$tmpturn_item[]	= "turn.turn_ordered_by = $_order_by";
		$tmpdr_item[]	= "dr.dr_ordered_by = $_order_by";
	}
} else {
	$tmp_bill[]	= "bill_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill_code,'billing')";
	$tmp_turn[] = "turn_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', turn_code,'billing_return')";
	$tmp_dr[]	= "dr_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', dr_code,'dr')";
	$tmpbill_item[]	= "bill.bill_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill.bill_code,'billing')";
	$tmpturn_item[]	= "turn.turn_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', turn.turn_code,'billing_return')";
	$tmpdr_item[]	= "dr.dr_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', dr.dr_code,'dr')";
}

if ($_filter_doc == "I") {
	$tmp_turn[]		= "turn.turn_code = NULL";
	$tmp_dr[]		= "dr.dr_code = NULL";
	$tmpturn_item[] = "turn.turn_code= NULL";
	$tmpdr_item[]	= "dr.dr_code= NULL";
} else if ($_filter_doc == "R") {
	$tmp_bill[]		= "bill.bill_code = NULL";
	$tmp_dr[]		= "dr.dr_code = NULL";
	$tmpbill_item[] = "bill.bill_code = NULL";
	$tmpdr_item[]	= "dr.dr_code= NULL";
} else if ($_filter_doc == "DR") {
	$tmp_bill[]		= "bill.bill_code = NULL";
	$tmp_turn[]		= "turn.turn_code = NULL";
	$tmpbill_item[] = "bill.bill_code = NULL";
	$tmpturn_item[] = "turn.turn_code= NULL";
}

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp_bill[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_turn[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_dr[]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if($_marketing != "all") {
	$tmp_bill[]	= "cus_responsibility_to = $_marketing";
	$tmp_turn[] = "cus_responsibility_to = $_marketing";
	$tmp_dr[] = "cus_responsibility_to = $_marketing";
	$tmpbill_item[] = "cus_responsibility_to = $_marketing";
	$tmpturn_item[] = "cus_responsibility_to = $_marketing";
	$tmpdr_item[]	= "cus_responsibility_to = $_marketing";
}

if($_dept != 'all') {
	$tmp_bill[] = "bill.bill_dept = '$_dept'";
	$tmp_turn[] = "turn.turn_dept = '$_dept'";
	$tmp_dr[]	= "dr.dr_dept = '$_dept'";
	$tmpbill_item[] = "bill.bill_dept = '$_dept'";
	$tmpturn_item[] = "turn.turn_dept = '$_dept'";
	$tmpdr_item[]	= "dr.dr_dept = '$_dept'";
}

if ($some_date != "") {
	$tmp_bill[] 	= "bill.bill_inv_date = DATE '$some_date'";
	$tmp_turn[] 	= "turn.turn_return_date = DATE '$some_date'";
	$tmp_dr[]		= "dr.dr_issued_date = DATE '$some_date'";
	$tmpbill_item[]	= "bill.bill_inv_date = DATE '$some_date'";
	$tmpturn_item[]	= "turn.turn_return_date = DATE '$some_date'";
	$tmpdr_item[]	= "dr.dr_issued_date = DATE '$some_date'";
} else {
	$tmp_bill[]		= "bill.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_turn[]		= "turn.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_dr[]		= "dr.dr_issued_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmpbill_item[] = "bill.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmpturn_item[] = "turn.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmpdr_item[]	= "dr.dr_issued_date BETWEEN DATE '$period_from' AND '$period_to'";
}

if($_vat == 'vat') {
	$tmp_bill[] 	= "bill_vat > 0";
	$tmp_turn[] 	= "turn.turn_vat > 0";
	$tmp_dr[]		= "dr_type_item = 1";
	$tmpbill_item[] = "bill_vat > 0";
	$tmpturn_item[] = "turn.turn_vat > 0"; 
	$tmpdr_item[]	= "dr_type_item = 1";
} else if($_vat == 'vat-IO') {
	$tmp_bill[] 	= "bill_vat > 0 AND bill_type_pajak = 'IO'";
	$tmp_turn[] 	= "turn.turn_vat > 0";
	$tmp_dr[]		= "dr_type_item = 1";
	$tmpbill_item[] = "bill_vat > 0 AND bill_type_pajak = 'IO'";
	$tmpturn_item[] = "turn.turn_vat > 0";
	$tmpdr_item[]	= "dr_type_item = 1";
} else if($_vat == 'vat-IP') {
	$tmp_bill[] 	= "bill_vat > 0 AND bill_type_pajak = 'IP'";
	$tmp_turn[] 	= "turn.turn_code = NULL";
	$tmp_dr[]		= "dr_code is null";
	$tmpbill_item[] = "bill_vat > 0 AND bill_type_pajak = 'IP'";
	$tmpturn_item[] = "turn.turn_code = NULL";
	$tmpdr_item[]	= "dr_code is null";  
} else if ($_vat == 'non') {
	$tmp_bill[] 	= "bill_vat = 0";
	$tmp_turn[] 	= "turn.turn_vat = 0";
	$tmp_dr[]		= "dr_type_item = 1";
	$tmpbill_item[] = "bill_vat = 0";
	$tmpturn_item[] = "turn.turn_vat = 0";
	$tmpdr_item[]	= "dr_type_item = 2";
}

$strWhereBill  		= implode(" AND ", $tmp_bill);
$strWhereTurn 		= implode(" AND ", $tmp_turn);
$strWhereDR 		= implode(" AND ", $tmp_dr);
$strWhereBillItem   = implode(" AND ", $tmpbill_item);
$strWhereTurnItem   = implode(" AND ", $tmpturn_item);
$strWhereDRItem		= implode(" AND ", $tmpdr_item);

$sql_bill = "
SELECT
  DISTINCT(it.it_code) AS it_code,
  it.it_model_no AS model_no,
  icat.icat_pidx AS icat_pidx,
  icat.icat_midx AS icat_midx
FROM
  ".ZKP_SQL."_tb_customer AS cus
  JOIN ".ZKP_SQL."_tb_billing AS bill ON bill_ship_to = cus_code
  JOIN ".ZKP_SQL."_tb_billing_item AS biit USING(bill_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE $strWhereBill";

$sql_turn = "
SELECT
  DISTINCT(it.it_code) AS it_code,
  it.it_model_no AS model_no,
  icat.icat_pidx AS icat_pidx,
  icat.icat_midx AS icat_midx
FROM
  ".ZKP_SQL."_tb_customer AS cus
  JOIN ".ZKP_SQL."_tb_return AS turn ON turn_ship_to = cus_code
  JOIN ".ZKP_SQL."_tb_return_item AS reit USING(turn_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE $strWhereTurn";

$sql_dr = "
SELECT
  DISTINCT(it.it_code) AS it_code,
  it.it_model_no AS model_no,
  icat.icat_pidx AS icat_pidx,
  icat.icat_midx AS icat_midx
FROM
  ".ZKP_SQL."_tb_customer AS cus
  JOIN ".ZKP_SQL."_tb_dr AS dr ON dr_ship_to = cus_code
  JOIN ".ZKP_SQL."_tb_dr_item AS biit USING(dr_code)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE $strWhereDR";

$sql = "$sql_bill UNION $sql_turn UNION $sql_dr ORDER by icat_pidx, icat_midx, it_code";
/*
echo "<pre>";
echo $sql;
echo "</pre>";
exit;
*/
// raw data
$rd		= array();
$rdIdx	= 0;
$cache	= array("","");
$group0	= array();
$a		= array("","");
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_pidx'],	//0
		$col['it_code'],	//1
		$col['model_no'],	//2
		$col['icat_midx']	//3
		);

	//1st grouping
	if($cache[0] != $col['icat_pidx']) {
		$cache[0] = $col['icat_pidx'];
		$group0[$col['icat_pidx']] = array();
	}

	if($cache[1] != $col['icat_midx'].$col['it_code']) {
		$cache[1] = $col['icat_midx'].$col['it_code'];
	}
	$group0[$col['icat_pidx']][$col['it_code']] = 1;
}

$total_amount = 0;
$amountbill_sql = 
"SELECT
	icat.icat_pidx AS icat_pidx,
	SUM(ROUND(biit.biit_qty * (biit.biit_unit_price*(100-bill.bill_discount)/100))) AS amount,
	'billing' AS condition
FROM
	".ZKP_SQL."_tb_customer AS cus
	JOIN ".ZKP_SQL."_tb_billing AS bill ON bill_ship_to = cus_code
	JOIN ".ZKP_SQL."_tb_billing_item AS biit USING(bill_code)
	JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE $strWhereBill
GROUP BY icat_pidx";

$amountturn_sql = 
"SELECT
	icat.icat_pidx AS icat_pidx,
	SUM(-(ROUND(reit.reit_qty * (reit.reit_unit_price*(100-turn.turn_discount)/100)))) AS amount,
	CASE
		WHEN turn.turn_return_condition=1 THEN 'turn_1'
		WHEN turn.turn_return_condition=2 THEN 'turn_2'
		WHEN turn.turn_return_condition=3 THEN 'turn_3'
		WHEN turn.turn_return_condition=4 THEN 'turn_4'
	END AS condition
FROM
	".ZKP_SQL."_tb_customer AS cus
	JOIN ".ZKP_SQL."_tb_return AS turn ON turn_ship_to = cus_code
	JOIN ".ZKP_SQL."_tb_return_item AS reit USING(turn_code)
	JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE $strWhereTurn AND turn.turn_return_condition IN(2,3,4)
GROUP BY icat_pidx, turn.turn_return_condition";

$amount_sql = "$amountbill_sql UNION $amountturn_sql ORDER BY icat_pidx";
$amount_res =& query($amount_sql);
while($amount_col =& fetchRowAssoc($amount_res)) {
	if(!isset($sub_amount[$amount_col['icat_pidx']])) {
		$sub_amount[$amount_col['icat_pidx']] = $amount_col['amount'];
	} else {
		$sub_amount[$amount_col['icat_pidx']] += $amount_col['amount'];
	}
	$total_amount += $amount_col['amount'];
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$grand_total = array(0,0,0);	//qty, amount, rate

//GROUP
foreach ($group0 as $total1 => $group1) {

	$rowSpan = 0;
	$rowSpan += count($group1)+1;

	//get category path from current icat_midx.
	$path = executeSP(ZKP_SQL."_getCategoryPath", $rd[$rdIdx][0]);
	eval(html_entity_decode($path[0]));	
	$path = array_reverse($path);
	$total1 = $path[1][4]." > ".$path[2][4] ;

	echo "<span class=\"comment\"><b> CATEGORY: ". $total1. "</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th>MODEL NO</th>
			<th width="15%">QTY<br>(EA)</th>
			<th width="20%">AMOUNT<br>(Rp)</th>
			<th width="10%">RATE</th>
			<th width="10%">RATE</th> 
		</tr>\n
END;
	$cat_total = array(0,0,0);	//qty, amount, rate
	$print_tr_1 = 0;
	$a = '';

	print "<tr>\n";
	//ITEM CODE
	foreach($group1 as $total2 => $group2) {

		$sql_item = "
				SELECT 
					SUM(biit.biit_qty) AS qty, 
					TRUNC(SUM(biit.biit_qty * (biit.biit_unit_price*(100-bill.bill_discount)/100)),2) AS amount,
					'billing' AS condition
				FROM
					".ZKP_SQL."_tb_customer AS cus
					JOIN ".ZKP_SQL."_tb_billing AS bill ON bill_ship_to = cus_code
					JOIN ".ZKP_SQL."_tb_billing_item AS biit USING(bill_code)
				WHERE biit.it_code = '{$rd[$rdIdx][1]}' AND biit.icat_midx = {$rd[$rdIdx][3]} AND $strWhereBillItem
			UNION 
				SELECT 
					SUM(-(reit.reit_qty)) AS qty, 
					TRUNC(SUM(-(reit.reit_qty * (reit.reit_unit_price*(100-turn.turn_discount)/100))),2) AS amount,
					CASE
						WHEN turn.turn_return_condition=1 THEN 'turn_1'
						WHEN turn.turn_return_condition=2 THEN 'turn_2'
						WHEN turn.turn_return_condition=3 THEN 'turn_3'
						WHEN turn.turn_return_condition=4 THEN 'turn_4'
					END AS condition
				FROM
					".ZKP_SQL."_tb_customer AS cus
					JOIN ".ZKP_SQL."_tb_return AS turn ON turn_ship_to = cus_code
					JOIN ".ZKP_SQL."_tb_return_item AS reit USING(turn_code)
				WHERE reit.it_code = '{$rd[$rdIdx][1]}' AND reit.icat_midx = {$rd[$rdIdx][3]} AND $strWhereTurnItem
			GROUP BY condition
			UNION
				SELECT 
					SUM(drit.drit_qty) AS qty, 
					0 AS amount,
					'billing' AS condition
				FROM
					".ZKP_SQL."_tb_customer AS cus
					JOIN ".ZKP_SQL."_tb_dr AS dr ON dr_ship_to = cus_code
					JOIN ".ZKP_SQL."_tb_dr_item AS drit USING(dr_code)
					JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
				WHERE drit.it_code = '{$rd[$rdIdx][1]}' AND it.icat_midx = {$rd[$rdIdx][3]} AND $strWhereDRItem
				";

		$result =& query($sql_item);
		$total = array(0,0);
		while($column =& fetchRowAssoc($result)) {
			if($column['condition'] != 'turn_1') {
				$total[1] += $column['amount'];
			}
			$total[0] += $column['qty'];
		}

		if($total[1] == 0) $rate = 0;
		else if($total[1] > 0) {
			$rate = $total[1]*100/$sub_amount[$rd[$rdIdx][0]];
			if($rate < 0) $rate = $rate*-1;
		}
		else if($total[1] < 0) {
			$rate = $total[1]*100/$sub_amount[$rd[$rdIdx][0]];
			if($rate > 0) $rate = -($rate);
		}

		if($sub_amount[$rd[$rdIdx][0]] == 0) $sub_rate = 0;
		else if($sub_amount[$rd[$rdIdx][0]] > 0) {
			$sub_rate = $sub_amount[$rd[$rdIdx][0]]*100/$total_amount;
			if($sub_rate < 0) $sub_rate = $sub_rate*-1;
		} else if($sub_amount[$rd[$rdIdx][0]] < 0) {
			$sub_rate = $sub_amount[$rd[$rdIdx][0]]*100/$total_amount;
			if($sub_rate > 0) $sub_rate = -($sub_rate);
		}

		//PRINT CONTENT
		if($print_tr_1++ > 0) print "<tr>\n";
		cell("{$rd[$rdIdx][2]}", ' valign="top"');						//model name
		cell(number_format((double)$total[0]), ' valign="top" align="right"');	//qty
		cell(number_format((double)$total[1]), ' valign="top" align="right"');	//amount
		cell(number_format((double)$rate, 2)." %", ' valign="top" align="right"');
		if($a != $rd[$rdIdx][0]) {
			cell(number_format((double)$sub_rate, 2)." %", ' rowspan="'. $rowSpan .'" align="right" style="color:darkblue"');
			$a = $rd[$rdIdx][0];
		}
		print "</tr>\n";

		$cat_total[0]	+= $total[0];
		$cat_total[1]	+= $total[1];
		$cat_total[2]	+= $rate;
		$rdIdx++;
	}

	print "<tr>\n";
	cell("<b>{$path[2][4]}</b>", ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cat_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cat_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cat_total[2], 2)." %", ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$grand_total[0]	+= $cat_total[0];
	$grand_total[1]	+= $cat_total[1]; 
	$grand_total[2]	+= $sub_rate;
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_f">
	<tr>
			<th>MODEL NO</th>
			<th width="15%">QTY<br>(EA)</th>
			<th width="20%">AMOUNT*<br />(Rp)</th>
			<th width="10%">RATE</th>
			<th width="10%">RATE</th>
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
cell('', ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[2], 2)." %", ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
print "<span class='comment'>*<i> Amount is amount before VAT, before freight charge and price including discount</i></span>\n";
?>