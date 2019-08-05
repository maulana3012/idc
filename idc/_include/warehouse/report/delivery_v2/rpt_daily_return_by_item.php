<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim
*
* $_po_date : Inquire Date
*
*/
//SET WHERE PARAMETER
$tmp	= array();

if($_order_by == '1') {
	$tmp[] = "substr(inc_doc_ref,1,1)='R' and substr(inc_doc_ref,4,1)!='M'";
} else if($_order_by == '2') {
	$tmp[] = "substr(inc_doc_ref,1,1)='R' and substr(inc_doc_ref,4,1)='M'";
}

if($_source == 1) {
	$tmp[]	= "b.inc_doc_type = 'Return Billing'"; 
} else if ($_source == 2) {
	$tmp[]	= "b.inc_doc_type = 'Return Order'"; 
} else if ($_source == 3) {
	$tmp[]	= "b.inc_doc_type = 'Return DT'"; 
}

if ($_cug_code != 'all') {
	//If group specified, 
	$tmp[]  = "b.cus_code IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$sql	= " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
} else {
	 // if null, return Others Group
	$sql = "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = b.cus_code),
		'Others') AS cug_name,";
}

if($_dept != 'all') {
	$tmp[] = "b.inc_dept = '$_dept'";
}

if ($_filter_date == 'turn_date' && $some_date != "") {
	$tmp[]   = "b.inc_date = DATE '$some_date'";
} else if ($_filter_date == 'turn_date' && $some_date == "") {
	$tmp[]   = "b.inc_date BETWEEN DATE '$period_from' AND '$period_to'";
} else if ($_filter_date == 'cfm_date' && $some_date != "") {
	$tmp[]   = "b.inc_confirmed_timestamp BETWEEN TIMESTAMP '$some_date 00:00:00' AND '$some_date 23:59:59'";
} else if ($_filter_date == 'cfm_date' && $some_date == "") {
	$tmp[]   = "b.inc_confirmed_timestamp BETWEEN TIMESTAMP '$period_from 00:00:00' AND '$period_to 23:59:59'";
}

if($_status == 0 && $_status != 'all') {
	$tmp[]	= "b.inc_is_confirmed = false"; 
} else if ($_status == 1 && $_status != 'all') {
	$tmp[]	= "b.inc_is_confirmed = true";
}

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp[] = "e.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

$strWhere   = implode(" AND ", $tmp);

$sql .= "
  a.cus_code AS cus_code,
  a.cus_full_name AS cus_full_name,
  b.inc_idx AS inc_idx,
  b.inc_std_idx AS std_idx,
  b.inc_doc_ref AS return_no,
  to_char(b.inc_date,'dd-Mon-YY') AS return_date,
  to_char(b.inc_confirmed_timestamp,'dd-Mon-YY') AS confirm_date,
  CASE
	WHEN b.inc_type = 1 THEN 'VAT'
	WHEN b.inc_type = 2 THEN 'NON'
	WHEN b.inc_type = 3 THEN 'NO SPEC'
  END AS return_type,
  CASE
	WHEN b.inc_doc_type IN('Return Billing', 'Return Order') THEN 'confirm_return.php'
	WHEN b.inc_doc_type = 'Return DT' THEN 'confirm_return_dt.php'
  END AS go_page,
  c.init_idx AS it_idx,
  c.it_code AS it_code,
  d.it_model_no AS it_model_no,
  c.init_qty AS qty,
  c.init_stock_qty AS stock_qty,
  c.init_demo_qty AS demo_qty,
  c.init_reject_qty AS reject_qty,
  e.icat_pidx AS icat_pidx,
  e.icat_midx AS icat_midx
FROM
  ".ZKP_SQL."_tb_customer AS a
  JOIN ".ZKP_SQL."_tb_incoming AS b USING(cus_code)
  JOIN ".ZKP_SQL."_tb_incoming_item AS c USING(inc_idx)
  JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS e USING(icat_midx)
WHERE ". $strWhere ."
ORDER BY icat_pidx, icat_midx, it_code, return_no, it_idx";

// raw data
$rd = array();
$rdIdx = 0;
$cache = array("","","",""); // 3th level
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'], 	//0
		$col['it_code'],	//1
		$col['it_model_no'],	//2
		$col['cug_name'],	//3
		$col['cus_code'],	//4
		$col['cus_full_name'],	//5
		$col['inc_idx'], 	//6
		$col['std_idx'], 	//7
		$col['return_no'], 	//8
		$col['return_date'], 	//9
		$col['confirm_date'],	//10
		$col['return_type'],	//11
		$col['it_idx'], 	//12
		$col['qty'],		//13
		$col['stock_qty'], 	//14
		$col['demo_qty'], 	//15
		$col['reject_qty'],	//16
		$col['go_page']		//17
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

	if($cache[2] != $col['inc_idx']) {
		$cache[2] = $col['inc_idx'];
		$group0[$col['icat_midx']][$col['it_code']][$col['inc_idx']] = array();
	}

	if($cache[3] != $col['it_idx']) {
		$cache[3] = $col['it_idx'];
	}

	$group0[$col['icat_midx']][$col['it_code']][$col['inc_idx']][$col['it_idx']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$grand_total = array(0,0,0,0);

//GROUP
foreach ($group0 as $total1 => $group1) {

	//get category path from current icat_midx.
	$path = executeSP(ZKP_SQL."_getCategoryPath", $rd[$rdIdx][0]);
	eval(html_entity_decode($path[0]));	
	$path = array_reverse($path);
	$total1 = $path[1][4]." > ".$path[2][4]." > ".$path[3][4] ;

	echo "<span class=\"comment\"><b> CATEGORY: ". $total1. "</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th rowspan="2" width="15%">MODEL NO</th>
			<th rowspan="2" width="13%">RETURN NO</th>
			<th rowspan="2" width="8%">RETURN DATE</th>
			<th rowspan="2" width="8%">CONFIRM DATE</th>
			<th rowspan="2">CUSTOMER</th>
			<th rowspan="2" width="6%">QTY<br>(EA)</th>
			<th colspan="3" width="15%">SAVE TO (pcs)</th>
		</tr>
		<tr>
			<th width="5%">STOCK</th>
			<th width="5%">DEMO</th>
			<th width="5%">REJECT</th>
		</tr>\n
END;
	$cat_total = array(0,0,0,0);
	$print_tr_1 = 0;

	print "<tr>\n";
	//MODEL
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".trim($rd[$rdIdx][1])."] ".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');	//Item code, model no

		$model_total = array(0,0,0,0);
		$print_tr_2 = 0;
		//ORDER
		foreach($group2 as $total3 => $group3) {
			$rowSpan = 0;
			array_walk_recursive($group3, 'getRowSpan');

			if($print_tr_2++ > 0) print "<tr>\n";
			cell_link("<span class=\"bar\">".$rd[$rdIdx][8]."</span>", ' align="center" valign="top" rowspan="'.$rowSpan.'"',
				' href="'.$rd[$rdIdx][17].'?_inc_idx='.$rd[$rdIdx][6].'&_std_idx='.$rd[$rdIdx][7].'"');	//Return no
			cell($rd[$rdIdx][9], ' align="center" valign="top" rowspan="'.$rowSpan.'"');	//Return date
			cell($rd[$rdIdx][10], ' align="center" valign="top" rowspan="'.$rowSpan.'"');	//Return confirm
			cell("[".trim($rd[$rdIdx][4])."] ".$rd[$rdIdx][5], ' valign="top" rowspan="'.$rowSpan.'"');	//Customer name

			$print_tr_3 = 0;
			//ITEM
			foreach($group3 as $total4 => $group4) {
				if($print_tr_3++ > 0) print "<tr>\n";

				cell(number_format($rd[$rdIdx][13],2), ' align="right"'); //qty
				cell(($rd[$rdIdx][14]==0)?'':number_format($rd[$rdIdx][14],2), ' align="right"');	//stock qty
				cell(($rd[$rdIdx][15]==0)?'':number_format($rd[$rdIdx][15],2), ' align="right"');	//demo qty
				cell(($rd[$rdIdx][16]==0)?'':number_format($rd[$rdIdx][16],2), ' align="right"');	//reject qty
				print "</tr>\n";

				$model_total[0] += $rd[$rdIdx][13];
				$model_total[1] += $rd[$rdIdx][14];
				$model_total[2] += $rd[$rdIdx][15];
				$model_total[3] += $rd[$rdIdx][16];
				$model_no	 = $rd[$rdIdx][2];
				$rdIdx++;
			}
		}

		print "<tr>\n";
		cell($model_no, ' colspan="4" align="right" style="color:darkblue"');
		cell(number_format($model_total[0],2), ' align="right" style="color:darkblue"');
		cell(number_format($model_total[1],2), ' align="right" style="color:darkblue"');
		cell(number_format($model_total[2],2), ' align="right" style="color:darkblue"');
		cell(number_format($model_total[3],2), ' align="right" style="color:darkblue"');
		print "</tr>\n";

		$cat_total[0] += $model_total[0];
		$cat_total[1] += $model_total[1];
		$cat_total[2] += $model_total[2];
		$cat_total[3] += $model_total[3];
	}
	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($cat_total[0],2), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($cat_total[1],2), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($cat_total[2],2), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($cat_total[3],2), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";
	
	$grand_total[0] += $cat_total[0];
	$grand_total[1] += $cat_total[1];
	$grand_total[2] += $cat_total[2];
	$grand_total[3] += $cat_total[3];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_f">
		<tr>
			<th rowspan="2" width="15%">MODEL NO</th>
			<th rowspan="2" width="13%">RETURN NO</th>
			<th rowspan="2" width="8%">RETURN DATE</th>
			<th rowspan="2" width="8%">CONFIRM DATE</th>
			<th rowspan="2">CUSTOMER</th>
			<th rowspan="2" width="6%">QTY<br>(EA)</th>
			<th colspan="3" width="15%">SAVE TO (pcs)</th>
		</tr>
		<tr>
			<th width="5%">STOCK</th>
			<th width="5%">DEMO</th>
			<th width="5%">REJECT</th>
		</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[0],2), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[1],2), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[2],2), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[3],2), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>