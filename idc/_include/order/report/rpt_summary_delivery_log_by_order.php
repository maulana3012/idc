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

$tmp_order  = array();
$tmp_return = array();

//SET WHERE PARAMETER
if($_sort_by == 'O') {
	$tmp_return[] = "reor.reor_code = ''";
} else if($_sort_by == 'R') {
	$tmp_order[]  = "ord.ord_code = ''";
}

if ($_cus_code != 'all') {
	$tmp_order[]  = "cus_code = '$_cus_code'";
	$tmp_return[] = "cus_code = '$_cus_code'";
}

if ($some_date != "") {
	$tmp_order[]  = "deli.deli_date = DATE '$some_date'";
	$tmp_return[] = "roit.roit_date = DATE '$some_date'";
} else {
	$tmp_order[]  = "deli.deli_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_return[] = "roit.roit_date BETWEEN DATE '$period_from' AND '$period_to'";
}

if ($_last_category != 0) {
    $catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
    $tmp_order[]  = "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
    $tmp_return[] = "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

$strWhereOrder  = implode(" AND ", $tmp_order);
$strWhereReturn = implode(" AND ", $tmp_return);

$sql_order = "
SELECT
  ord.ord_code AS code,
  to_char(ord.ord_po_date, 'dd-Mon-YYYY') as po_date,
  ord.ord_ship_to AS ship_to,
  ord.ord_ship_to_attn AS ship_to_attn,
  deit.it_code AS it_code,
  it.it_model_no AS it_model_no,
  deli_idx AS idx,
  deli_by AS deli_by,
  to_char(deli.deli_date, 'dd-Mon-YYYY') as delivery,
  (SELECT odit_qty FROM ".ZKP_SQL."_tb_order_item WHERE ord_code = ord.ord_code AND it_code = deit.it_code) AS ord_qty,
  deit.deit_qty AS deli_qty,
  '../order/revise_order.php?_code='||ord.ord_code AS go_page
FROM
  ".ZKP_SQL."_tb_order AS ord
  JOIN ".ZKP_SQL."_tb_delivery AS deli USING(ord_code)
  LEFT JOIN ".ZKP_SQL."_tb_delivery_item AS deit USING(deli_idx)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE " . $strWhereOrder;

$sql_return = "
SELECT
  reor.reor_code AS code,
  to_char(reor.reor_po_date, 'dd-Mon-YYYY') as po_date,
  reor.reor_ship_to AS ship_to,
  reor.reor_ship_to_attn AS ship_to_attn,
  roit.it_code AS it_code,
  it.it_model_no AS it_model_no,
  0 AS idx,
  '' AS deli_by,
  to_char(roit.roit_date, 'dd-Mon-YYYY') as delivery,
  -roit.roit_qty AS ord_qty,
  -roit.roit_qty AS deli_qty,
  '../order/revise_return_order.php?_code='||reor.reor_code AS go_page
FROM
  ".ZKP_SQL."_tb_return_order AS reor
  JOIN ".ZKP_SQL."_tb_return_order_item AS roit USING(reor_code)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE " . $strWhereReturn;

$sql = "$sql_order UNION $sql_return ORDER BY code, po_date DESC";

// raw data
$rd = array();
$rdIdx = 0;

$cache = array("","");
$group0 = array();

$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['code'],			//0
		$col['po_date'],		//1
		$col['ship_to'],		//2
		$col['ship_to_attn'],	//3
		$col['idx'],			//4
		$col['delivery'],		//5
		$col['deli_by'],		//6
		$col['it_code'],		//7
		$col['it_model_no'],	//8
		$col['ord_qty'],		//9
		$col['deli_qty'],		//10
		$col['go_page']			//11
	);

	//1st grouping
	if($cache[0] != $col['code']) {
		$cache[0] = $col['code'];
		$group0[$col['code']] = array();
	}

	if($cache[1] != $col['it_code']) {
		$cache[1] = $col['it_code'];
		$group0[$col['code']][$col['it_code']] = 1;
	} else {
		$group0[$col['code']][$col['it_code']] = 1;
	}
}

function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//Print Header
print <<<END
<table width="100%" class="table_c">
	<tr>
		<th width="11%">ORDER#</th>
		<th width="10%">PO DATE</th>
		<th>SHIP TO</th>
		<th width="10%">DELIVERY</th>
		<th width="12%">DELIVERD BY</th>
		<th width="20%">MODEL NO</th>
		<th width="6%">PO QTY</th>
		<th width="8%">DELI QTY</th>
	</tr>\n
END;

foreach ($group0 as $total1 => $group1) {
	$rowSpan = 0;
	array_walk_recursive($group1, 'getRowSpan');
	$rowSpan += 1;

	print "<tr>\n";
	cell_link("<span class=\"bar\">".$rd[$rdIdx][0]."</span>", ' align="center" valign="top" rowspan="'.$rowSpan.'"',
		' href="'.$rd[$rdIdx][11].'"');
	cell($rd[$rdIdx][1], ' valign="top" rowspan="'.$rowSpan.'"');
	cell("[{$rd[$rdIdx][2]}] {$rd[$rdIdx][3]}", ' valign="top" rowspan="'.$rowSpan.'"');
	cell($rd[$rdIdx][5], ' valign="top"  align="center" rowspan="'.$rowSpan.'"');
	cell($rd[$rdIdx][6], ' valign="top" rowspan="'.$rowSpan.'"');

	$totalPO = 0;
	$totalEA = 0;
	$print_tr_1 = 0;
	foreach($group1 as $group2) {
		if($print_tr_1++ > 0) print "<tr>\n";

		if(substr($rd[$rdIdx][0],0,1) == 'O') {
			cell_link("[{$rd[$rdIdx][7]}] {$rd[$rdIdx][8]}", "", " href=\"javascript:openWindow('./p_detail_delivery_log.php?idx={$rd[$rdIdx][4]}&_code={$rd[$rdIdx][7]}', 450, 220);\"");
		} else if(substr($rd[$rdIdx][0],0,1) == 'R') {
			cell("[{$rd[$rdIdx][7]}] {$rd[$rdIdx][8]}", "");
		}
		cell($rd[$rdIdx][9], ' align="right"');
		cell(number_format((double)$rd[$rdIdx][10]), ' align="right"');
		print "</tr>\n";

		$totalPO += $rd[$rdIdx][9];
		$totalEA += $rd[$rdIdx][10];
		$rdIdx++;
	}
	
	print "<tr>\n";
	cell("<b>$total1</b>", ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$totalPO), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$totalEA), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
}
print "</table><br>\n";
?>