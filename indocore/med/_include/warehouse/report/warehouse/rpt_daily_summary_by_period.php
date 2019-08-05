
<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim
*
* $id$
*/
//Variable Color (make same with the javascript)
$display_css['unconfirmed'] = "color:black";
$display_css['confirmed'] 	= "background-color:#e5e0ec;color:#333333";

$tmp_0 = array();	//billing
$tmp_1 = array();	//order
$tmp_2 = array();	//return billing
$tmp_3 = array();	//return order
$source_date = array("","","","");

//SET WHERE PARAMETER
if($_source == "0") {
	$tmp_1[]		= "ord_code = ''";
	$tmp_2[]		= "turn_code = ''";
	$tmp_3[]		= "reor_code = ''";
} else if($_source == "1") {
	$tmp_0[]		= "bill_code = ''";
	$tmp_2[]		= "turn_code = ''";
	$tmp_3[]		= "reor_code = ''";
} else if($_source == "2") {
	$tmp_0[]		= "bill_code = ''";
	$tmp_1[]		= "ord_code = ''";
	$tmp_3[]		= "reor_code = ''";
} else if($_source == "3") {
	$tmp_0[]		= "bill_code = ''";
	$tmp_1[]		= "ord_code = ''";
	$tmp_2[]		= "turn_code = ''";
}

if($_order_by == 1){
	$tmp_0[]		= "bill_ordered_by = 1";
	$tmp_2[]		= "turn_ordered_by = 1";
} else if($_order_by == 2) {
	$tmp_0[]		= "bill_ordered_by = 2";
	$tmp_2[]		= "turn_ordered_by = 2";
	$tmp_1[]		= "ord_code IS NULL";
	$tmp_3[]		= "reor_code IS NULL";
}

if($_dept == 'A') {
	$tmp_0[]		= "bill_dept = 'A'";
	$tmp_1[]		= "ord_code = ''";
	$tmp_2[]		= "turn_dept = 'A'";
	$tmp_3[]		= "reor_code = ''";
} else if($_dept == 'D') {
	$tmp_0[]		= "bill_dept = 'D'";
	$tmp_1[]		= "ord_code = ''";
	$tmp_2[]		= "turn_dept = 'D'";
	$tmp_3[]		= "reor_code = ''";
} else if($_dept == 'H') {
	$tmp_0[]		= "bill_dept = 'H'";
	$tmp_1[]		= "ord_code = ''";
	$tmp_2[]		= "turn_dept = 'H'";
	$tmp_3[]		= "reor_code = ''";
} else if($_dept == 'P') {
	$tmp_0[]		= "bill_dept = 'P'";
	$tmp_1[]		= "ord_code = ''";
	$tmp_2[]		= "turn_dept = 'P'";
	$tmp_3[]		= "reor_code = ''";
}

if ($_cug_code != 'all') {
	//If group specified, 
	$tmp_0[]	= "bill_cus_to	IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp_1[]	= "ord_cus_to	IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp_2[]	= "turn_cus_to	IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp_3[]	= "reor_cus_to	IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
}

if($_status == "0") {
	$tmp_0[]	= "bill_cfm_wh_timestamp IS NULL";
	$tmp_1[]	= "ord_cfm_wh_timestamp IS NULL";
	$tmp_2[]	= "turn_cfm_wh_timestamp IS NULL";
	$tmp_3[]	= "reor_cfm_wh_timestamp IS NULL";
} else if($_status == "1") {
	$tmp_0[]	= "bill_cfm_wh_timestamp IS NOT NULL";
	$tmp_1[]	= "ord_cfm_wh_timestamp IS NOT NULL";
	$tmp_2[]	= "turn_cfm_wh_timestamp IS NOT NULL";
	$tmp_3[]	= "reor_cfm_wh_timestamp IS NOT NULL";
}

if($_sort_date == 'doc_date') {
	$source_date[0]	= 'bill_inv_date';
	$source_date[1]	= 'ord_po_date';
	$source_date[2]	= 'turn_return_date';
	$source_date[3]	= 'reor_po_date';
} else if($_sort_date == 'cfm_date') {
	$source_date[0]	= 'bill_cfm_wh_date';
	$source_date[1]	= 'ord_cfm_wh_date';
	$source_date[2]	= 'turn_cfm_wh_date';
	$source_date[3]	= 'reor_cfm_wh_date';
}

if ($some_date != "") {
	$tmp_0[] = "{$source_date[0]} = DATE '$some_date'";
	$tmp_1[] = "{$source_date[1]} = DATE '$some_date'";
	$tmp_2[] = "{$source_date[2]} = DATE '$some_date'";
	$tmp_3[] = "{$source_date[3]} = DATE '$some_date'";
} else {
	$tmp_0[] = "{$source_date[0]}  BETWEEN DATE '$period_from' AND DATE '$period_to'";
	$tmp_1[] = "{$source_date[1]}  BETWEEN DATE '$period_from' AND DATE '$period_to'";
	$tmp_2[] = "{$source_date[2]}  BETWEEN DATE '$period_from' AND DATE '$period_to'";
	$tmp_3[] = "{$source_date[3]}  BETWEEN DATE '$period_from' AND DATE '$period_to'";
}

$strWhere0 = implode(" AND ", $tmp_0);
$strWhere1 = implode(" AND ", $tmp_1);
$strWhere2 = implode(" AND ", $tmp_2);
$strWhere3 = implode(" AND ", $tmp_3);

$sql_0 ="
SELECT
	{$source_date[0]} 							AS orig_date,						
	to_char({$source_date[0]}, 'YYMM')			AS month,
	to_char({$source_date[0]}, 'Mon, YYYY')		AS month_year,
	EXTRACT(WEEK FROM {$source_date[0]})		AS week,
	bill_code 									AS code,
	bill_ship_to	 							AS cus_code,
	bill_ship_to_name 							AS cus_name,
	bill_dept 									AS source_dept,
	to_char(bill_inv_date, 'dd/Mon/YY') 		AS doc_date,
	to_char(bill_cfm_wh_date, 'dd/Mon/YY') 		AS cfm_date,
	biit_idx									AS idx,
	it_code 									AS it_code,
	it_model_no 								AS it_model_no,
	biit_unit_price 							AS unit_price,
	biit_qty 									AS qty,
	biit_qty*biit_unit_price 					AS amount,
	'detail_billing.php' 						AS go_page,
	CASE
		WHEN bill_cfm_wh_timestamp IS NOT NULL 	THEN 'confirmed'
		WHEN bill_cfm_wh_timestamp IS NULL 		THEN 'unconfirmed'
	END AS status
FROM
	".ZKP_SQL."_tb_billing AS a
	JOIN ".ZKP_SQL."_tb_billing_item AS b USING(bill_code) 
WHERE bill_type_invoice = 0 AND $strWhere0";

$sql_1 ="
SELECT
	{$source_date[1]} 							AS orig_date,						
	to_char({$source_date[1]}, 'YYMM')			AS month,
	to_char({$source_date[1]}, 'Mon, YYYY')		AS month_year,
	EXTRACT(WEEK FROM {$source_date[1]})		AS week,
	ord_code 									AS code,
	ord_ship_to	 								AS cus_code,
	ord_ship_to_attn 							AS cus_name,
	'A' 	 									AS source_dept,
	to_char(ord_po_date, 'dd/Mon/YY') 			AS doc_date,
	to_char(ord_cfm_wh_date, 'dd/Mon/YY') 		AS cfm_date,
	NULL 										AS idx,
	it_code 									AS it_code,
	it_model_no 								AS it_model_no,
	odit_unit_price 							AS unit_price,
	odit_qty 									AS qty,
	odit_qty*odit_unit_price 					AS amount,
	'detail_order.php' 							AS go_page,
	CASE
		WHEN ord_cfm_wh_timestamp IS NOT NULL 	THEN 'confirmed'
		WHEN ord_cfm_wh_timestamp IS NULL 	 	THEN 'unconfirmed'
	END AS status
FROM
	".ZKP_SQL."_tb_order AS a
	JOIN ".ZKP_SQL."_tb_order_item AS b USING(ord_code) 
	JOIN ".ZKP_SQL."_tb_item AS c USING(it_code)
WHERE $strWhere1";

$sql_2 ="
SELECT
	{$source_date[2]} 							AS orig_date,						
	to_char({$source_date[2]}, 'YYMM')			AS month,
	to_char({$source_date[2]}, 'Mon, YYYY')		AS month_year,
	EXTRACT(WEEK FROM {$source_date[2]})		AS week,
	turn_code 									AS code,
	turn_ship_to	 							AS cus_code,
	turn_ship_to_name 							AS cus_name,
	turn_dept  									AS source_dept,
	to_char(turn_return_date, 'dd/Mon/YY') 		AS doc_date,
	to_char(turn_cfm_wh_date, 'dd/Mon/YY') 		AS cfm_date,
	reit_idx									AS idx,
	it_code 									AS it_code,
	it_model_no 								AS it_model_no,
	reit_unit_price 							AS unit_price,
	-reit_qty 									AS qty,
	-reit_qty*reit_unit_price 					AS amount,
	'detail_return_billing.php'					AS go_page,
	CASE
		WHEN turn_cfm_wh_timestamp IS NOT NULL 	THEN 'confirmed'
		WHEN turn_cfm_wh_timestamp IS NULL 		THEN 'unconfirmed'
	END AS status
FROM
	".ZKP_SQL."_tb_return AS a
	JOIN ".ZKP_SQL."_tb_return_item AS b USING(turn_code) 
WHERE turn_paper = 0 AND $strWhere2";

$sql_3 ="
SELECT
	{$source_date[3]} 							AS orig_date,						
	to_char({$source_date[3]}, 'YYMM')			AS month,
	to_char({$source_date[3]}, 'Mon, YYYY')		AS month_year,
	EXTRACT(WEEK FROM {$source_date[3]})		AS week,
	reor_code 									AS code,
	reor_cus_to	 								AS cus_code,
	reor_cus_to_attn 							AS cus_name,
	'A'  										AS source_dept,
	to_char(reor_po_date, 'dd/Mon/YY') 			AS doc_date,
	to_char(reor_cfm_wh_date, 'dd/Mon/YY') 		AS cfm_date,
	NULL 										AS idx,
	it_code 									AS it_code,
	it_model_no 								AS it_model_no,
	roit_unit_price 							AS unit_price,
	-roit_qty 									AS qty,
	-roit_qty*roit_unit_price 					AS amount,
	'detail_return_order.php'					AS go_page,
	CASE
		WHEN reor_cfm_wh_timestamp IS NOT NULL 	THEN 'confirmed'
		WHEN reor_cfm_wh_timestamp IS NULL 		THEN 'unconfirmed'
	END AS status
FROM
	".ZKP_SQL."_tb_return_order AS a
	JOIN ".ZKP_SQL."_tb_return_order_item AS b USING(reor_code) 
	JOIN ".ZKP_SQL."_tb_item AS c USING(it_code)
WHERE $strWhere3";

$sql = "$sql_0 UNION $sql_1 UNION $sql_2 UNION $sql_3 ORDER BY month, week, orig_date, code, it_code";

// raw data
$rd = array();
$rdIdx	= 0;
$i		= 0;
$cache	= array("","","","");
$group0 = array();

$res = query($sql);
while($col = fetchRowAssoc($res)) {

	$rd[] = array(
		$col['month'],			//0
		$col['month_year'],		//1
		$col['week'],			//2
		$col['code'],			//3
		$col['status'],			//4
		$col['doc_date'],		//5
		$col['cfm_date'],		//6
		$col['cus_code'], 		//7
		$col['cus_name'],		//8
		$col['idx'],			//9
		$col['it_code'],		//10
		$col['it_model_no'],	//11
		$col['unit_price'],		//12
		$col['qty'],			//13
		$col['amount'],			//14
		$col['go_page']			//15
	);

	if($cache[0] != $col['month']) {
		$cache[0] = $col['month'];
		$group0[$col['month']] = array();
	}

	if($cache[1] != $col['week']) {
		$cache[1] = $col['week'];
		$group0[$col['month']][$col['week']] = array();
	}

	if($cache[2] != $col['code']) {
		$cache[2] = $col['code'];
		$group0[$col['month']][$col['week']][$col['code']] = array();
	}

	if($col['idx']=='') $col['idx'] = $i++;

	if($cache[3] != $col['idx']) {
		$cache[3] = $col['idx'];
	}


	$group0[$col['month']][$col['week']][$col['code']][$col['idx']] = 1;
}

echo "<pre>";
//var_dump($strWhere0,$strWhere1,$strWhere2,$strWhere3);
//var_dump($sql_1);
//var_dump($group0);
echo "</pre>";

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//grand summary
$grand_total = array (0,0);

//monthly summary
foreach ($group0 as $month_name => $month) {
	echo "<span class=\"comment\"><b>[". $rd[$rdIdx][1] . "]</b></span>\n";
	print <<<END
	<table width="90%" class="table_gg">
		<tr>
			<th width="8%">DOC DATE</th>
			<th width="13%">DOCUMENT<br />NO.</th>
			<th width="8%">CFM DATE</th>
			<th width="30%">CUSTOMER SHIP TO</th>
			<th>MODEL NO</th>
			<th width="10%">QTY</th>
		</tr>\n
END;

	//weekly summary
	$monthly_summary = array (0,0);
	$weekth			 = array();
	foreach ($month as $week_name => $due_week) {

		$weekth = getWeek($week_name * 604800); 	//7day * 24 hour * 60 Min * 60 Sec
		print "<tr height=\"30px\">\n";
		print "<td colspan=\"8\" style=\"background-color:lightyellow;color:darkgreen;font-family:verdana\">{$weekth['string']}</td>\n";
		print "</tr>\n";

		//invoice summary
		$print_tr_1		= 0;
		$weekly_summary = array(0,0);
		foreach ($due_week as $invoice => $item) {
			$rowSpan = 0;
			array_walk_recursive($item, 'getRowSpan');

			if($print_tr_1++ > 0) print "<tr>\n";
			cell($rd[$rdIdx][5], ' rowspan="'.$rowSpan.'" style="'.$display_css[$rd[$rdIdx][4]].'" align="center" valign="top"');					//doc date
			cell_link($rd[$rdIdx][3], ' rowspan="'.$rowSpan.'" style="'.$display_css[$rd[$rdIdx][4]].'" align="center" valign="top"',				//doc name
				' href="'.$rd[$rdIdx][15].'?_code='.urlencode($rd[$rdIdx][3]).'"');
			cell($rd[$rdIdx][6], ' rowspan="'.$rowSpan.'" style="'.$display_css[$rd[$rdIdx][4]].'" align="center" valign="top"');					//cfm date
			cell("[".trim($rd[$rdIdx][7])."] ".$rd[$rdIdx][8], ' rowspan="'.$rowSpan.'" style="'.$display_css[$rd[$rdIdx][4]].'" valign="top"');	//customer

			$print_tr_2 = 0;
			//item summary
			foreach($item as $item0) {
				if($print_tr_2++ > 0) print "<tr>\n";

				cell("[".trim($rd[$rdIdx][10])."] ".$rd[$rdIdx][11], ' style="'.$display_css[$rd[$rdIdx][4]].'"');	//model no
				cell(number_format($rd[$rdIdx][13]), ' style="'.$display_css[$rd[$rdIdx][4]].'" align="right"');	//qty
				print "</tr>\n";

				//SUB TOTAL
				$weekly_summary[0] += $rd[$rdIdx][13]; 
				$weekly_summary[1] += $rd[$rdIdx][14];
				$head				= $rd[$rdIdx][1];
				$rdIdx++;
			}
		}

		print "<tr height=\"25px\">\n";
		cell($weekth['string'], ' colspan="5"  align="right" align="right" style="background-color:lightyellow;color:darkgreen;font-family:verdana"');
		cell(number_format($weekly_summary[0]), ' align="right" style="color:darkblue;background-color:lightyellow"');
		print "</tr>\n";

		//Monthly TOTAL
		$monthly_summary[0] += $weekly_summary[0];
		$monthly_summary[1] += $weekly_summary[1];
	}
	
	print "<tr>\n";
	cell('<b>'.$head.'<b>', ' colspan="5"  align="right" style="color:brown; background-color:#ffffbd"');
	cell(number_format($monthly_summary[0]), ' align="right" style="color:brown; background-color:#ffffbd"');
	print "</tr>\n";
	print "</table><br>\n";

	//Monthly TOTAL
	$grand_total[0] += $monthly_summary[0];
	$grand_total[1] += $monthly_summary[1];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="90%" class="table_gg">
	<tr>
		<th width="8%">DOC DATE</th>
		<th width="13%">DOCUMENT<br />NO.</th>
		<th width="8%">CFM DATE</th>
		<th width="30%">CUSTOMER SHIP TO</th>
		<th>MODEL NO</th>
		<th width="10%">QTY</th>
	</tr>\n
END;
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="5"  align="right" style="color:brown; background-color:#ffffbd"');
cell(number_format($grand_total[0]), ' align="right" style="color:brown; background-color:#ffffbd"');
print "</tr>\n";
print "</table><br>\n";
?>