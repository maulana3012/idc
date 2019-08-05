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

$tmp_0	= array();
$tmp_1	= array();

//SET WHERE PARAMETER
if ($_cug_code != 'all') {
	$tmp_0[] = "cus.cus_code IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp_0[] = "cus.cus_code IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$sql_order 	= " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
	$sql_return	= " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
} else {
	$sql_order = "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = ord_cus_to),
		'Others') AS cug_name,";
	$sql_return = "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = reor_cus_to),
		'Others') AS cug_name,";
}

if ($_last_category != 0) {
    $catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
    $tmp_0[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
    $tmp_1[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if($_sort_by == 'O') {
	$tmp_1[] = "reor.reor_code = ''";
} else if($_sort_by == 'R') {
	$tmp_0[]  = "ord.ord_code = ''";
}

if($_sort_date == "deli") {
	if ($some_date != "") {
		$tmp_0[]  = "deli.deli_date = DATE '$some_date'";
		$tmp_1[] = "roit.roit_date = DATE '$some_date'";
	} else {
		$tmp_0[]  = "deli.deli_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_1[] = "roit.roit_date BETWEEN DATE '$period_from' AND '$period_to'";
	}
} else {
	if ($some_date != "") {
		$tmp_0[]  = "ord.ord_po_date = DATE '$some_date'";
		$tmp_1[] = "roit.roit_date = DATE '$some_date'";
	} else {
		$tmp_0[]  = "ord.ord_po_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_1[] = "roit.roit_date BETWEEN DATE '$period_from' AND '$period_to'";
	}
}

if ($_paper == '0') {
	$tmp_0[]	= "ord_type_invoice = '0'";
	$tmp_1[]	= "reor_paper = 0";
} else if ($_paper == '1') {
	$tmp_0[]	= "ord_type_invoice = '1'";
	$tmp_1[]	= "reor_paper = 1";
}

$tmp_0[] = "ord_dept = '$department' AND ord.ord_cfm_deli_timestamp IS NOT NULL";
$tmp_1[] = "reor_dept = '$department'";

$strWhereOrder  = implode(" AND ", $tmp_0);
$strWhereReturn = implode(" AND ", $tmp_1);
 
$sql_order .= "
  cus.cus_code AS cus_code,
  cus.cus_full_name AS cus_full_name,
  ord.ord_code AS code,
  to_char(ord.ord_po_date, 'dd-Mon-YY') AS po_date,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  odit_qty AS qty,
  odit_unit_price AS unit_price,
  (odit_qty * odit_unit_price) AS amount,
  to_char(deli.deli_date, 'dd-Mon-YY') AS date,
  deli.deli_date AS deli_date,
  deli.deli_by AS deli_by,
  '../order/revise_order.php?_code='||ord.ord_code AS go_page
FROM
  ".ZKP_SQL."_tb_customer AS cus
  JOIN ".ZKP_SQL."_tb_order AS ord ON cus.cus_code = ord.ord_cus_to
  JOIN ".ZKP_SQL."_tb_order_item AS odit USING(ord_code)
  JOIN ".ZKP_SQL."_tb_delivery AS deli USING(ord_code)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE " . $strWhereOrder;

$sql_return .= "
  cus.cus_code AS cus_code,
  cus.cus_full_name AS cus_full_name,
  reor.reor_code AS code,
  to_char(reor.reor_po_date, 'dd-Mon-YY') AS po_date,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  -roit_qty AS qty,
  -roit_unit_price AS unit_price,
  -(roit_qty * roit_unit_price) AS amount,
  to_char(roit.roit_date, 'dd-Mon-YY') AS date,
  roit.roit_date AS deli_date,
  NULL AS deli_by,
  '../order/revise_return_order.php?_code='||reor.reor_code AS go_page
FROM
  ".ZKP_SQL."_tb_customer AS cus
  JOIN ".ZKP_SQL."_tb_return_order AS reor ON cus.cus_code = reor.reor_cus_to
  JOIN ".ZKP_SQL."_tb_return_order_item AS roit USING(reor_code)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE " . $strWhereReturn;

$sql = "$sql_order UNION $sql_return ORDER BY cug_name, cus_code, deli_date, code, it_code";

// raw data
$rd		= array();
$rdIdx	= 0;
$cache	= array("","","","");
$group0 = array();
$res	=& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['cug_name'],		//0
		$col['cus_code'],		//1
		$col['cus_full_name'],	//2
		$col['code'],			//3
		$col['po_date'],		//4
		$col['it_code'],		//5
		$col['it_model_no'],	//6
		$col['unit_price'],		//7
		$col['qty'],			//8
		$col['amount'],			//9
		$col['date'],			//10
		$col['deli_by'],		//11
		$col['go_page']			//12
	);

	if($cache[0] != $col['cug_name']) {
		$cache[0] = $col['cug_name'];
		$group0[$col['cug_name']] = array();
	}

	if($cache[1] != $col['cus_code']) {
		$cache[1] = $col['cus_code'];
		$group0[$col['cug_name']][$col['cus_code']] = array();
	}

	if($cache[2] != $col['code']) {
		$cache[2] = $col['code'];
		$group0[$col['cug_name']][$col['cus_code']][$col['code']] = array();
	}

	if($cache[3] != $col['it_code']) {
		$cache[3] = $col['it_code'];
	}

	$group0[$col['cug_name']][$col['cus_code']][$col['code']][$col['it_code']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$ggTot = array(0,0);

//GROUP
foreach ($group0 as $total1 => $group1) {

	echo "<span class=\"comment\"><b> PUSAT: ". $total1. "</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th width="15%">CUSTOMER</th>
			<th width="12%">ORDER#</th>
			<th width="9%">PO DATE</th>
			<th width="9%">DELI OR<br />RETURN DATE</th>
			<th width="12%">DELIVERED BY</th>
			<th>MODEL NO</th>
			<th width="10%">UNIT PRICE<br/>(Rp)</th>
			<th width="5%">QTY<br>(EA)</th>
			<th width="12%">AMOUNT<br>(Rp)</th>
		</tr>\n
END;

	$gTot = array(0,0);
	$print_tr_1 = 0;
	print "<tr>\n";
	//CUSTOMER
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += count($group2);

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".trim($rd[$rdIdx][1])."] ".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');		// Customer to

		$print_tr_2 = 0;
		//ORDER
		foreach($group2 as $total3 => $group3) {
			$rowSpan = 0;
			array_walk_recursive($group3, 'getRowSpan');
			$rowSpan += 1;

			if($print_tr_2++ > 0) print "<tr>\n";
			cell_link("<span class=\"bar\">".$rd[$rdIdx][3]."</span>", ' align="center" valign="top" rowspan="'.$rowSpan.'"',
				' href="'.$rd[$rdIdx][12].'"');													// Order No
			cell($rd[$rdIdx][4], ' valign="top" align="center" rowspan="'.$rowSpan.'"');		// Order Date
			cell($rd[$rdIdx][10], ' valign="top" align="center" rowspan="'.$rowSpan.'"');		// Deli Date
			cell($rd[$rdIdx][11], ' valign="top" rowspan="'.$rowSpan.'"');						// Deli By

			$tot = array(0,0);
			$print_tr_3 = 0;
			//ITEM
			foreach($group3 as $group4) {
				if($print_tr_3++ > 0) print "<tr>\n";

				cell($rd[$rdIdx][6]);
				cell(number_format((double)$rd[$rdIdx][7]), ' align="right"');
				cell(number_format((double)$rd[$rdIdx][8]), ' align="right"');
				cell(number_format((double)$rd[$rdIdx][9]), ' align="right"');
				print "</tr>\n";

				$tot[0] += $rd[$rdIdx][8];
				$tot[1] += $rd[$rdIdx][9];
				$rdIdx++;
			}
			
			print "<tr>\n";
			cell("$total3", ' colspan="2" align="right" style="color:darkblue"');
			cell(number_format((double)$tot[0]), ' align="right" style="color:darkblue"');
			cell(number_format((double)$tot[1]), ' align="right" style="color:darkblue"');
			print "</tr>\n";
	
			$gTot[0] += $tot[0];
			$gTot[1] += $tot[1];
		}
	}

	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="7" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$gTot[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$gTot[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$ggTot[0] += $gTot[0];
	$ggTot[1] += $gTot[1];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th width="15%">CUSTOMER</th>
			<th width="12%">ORDER#</th>
			<th width="9%">PO DATE</th>
			<th width="9%">DELI OR<br />RETURN DATE</th>
			<th width="12%">DELIVERED BY</th>
			<th>MODEL NO</th>
			<th width="10%">UNIT PRICE<br/>(Rp)</th>
			<th width="5%">QTY<br>(EA)</th>
			<th width="12%">AMOUNT<br>(Rp)</th>
		</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="7" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$ggTot[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$ggTot[1]), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br />\n";
?>