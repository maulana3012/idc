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

$tmp_0 = array();
$tmp_1 = array();

//SET WHERE PARAMETER
$tmp_0[]  = "c.cus_code = '$_cus_code'";
$tmp_1[]  = "c.cus_code = '$_cus_code'";

if ($_filter_by == '0') {
	$tmp_1[] = "reor_code = ''";
} else if ($_filter_by == '1') {
	$tmp_0[] = "ord_code = ''";
}

if ($_sales_type == '0') {
	$tmp_0[] = "ord_type = 'OO'";
	$tmp_1[] = "reor_type = 'RO'";
} else if ($_sales_type == '1') {
	$tmp_0[] = "ord_type = 'OK'";
	$tmp_1[] = "reor_type = 'RK'";
}

if ($some_date != "") {
	$tmp_0[] = "ord_po_date = DATE '$some_date'";
	$tmp_1[] = "reor_po_date = DATE '$some_date'";
} else {
	$tmp_0[] = "ord_po_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
	$tmp_1[] = "reor_po_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
}

if ($_last_category != 0) {
    $catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
    $tmp_0[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
    $tmp_1[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if ($_paper == '0') {
	$tmp_0[]	= "ord_type_invoice = '0'";
	$tmp_1[]	= "reor_paper = 0";
} else if ($_paper == '1') {
	$tmp_0[]	= "ord_type_invoice = '1'";
	$tmp_1[]	= "reor_paper = 1";
}

$tmp_0[]	= "ord_dept = '$department'";
$tmp_1[]	= "reor_dept = '$department'";

$strWhereOrder  = implode(" AND ", $tmp_0);
$strWhereReturn = implode(" AND ", $tmp_1);

$sql_order = "
SELECT
  icat.icat_pidx AS icat_pidx,
  it.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  ord.ord_code AS code,
  ord.ord_po_date AS date,
  to_char(ord.ord_po_date, 'dd-Mon-yy') as po_date,
  c.cus_code AS cus_code,
  c.cus_full_name AS cus_full_name,
  odit.odit_qty AS qty,
  odit.odit_unit_price AS unit_price,
  (odit.odit_qty * odit.odit_unit_price) AS amount,
  '../order/revise_order.php' AS go_page
FROM
	".ZKP_SQL."_tb_customer AS c
    JOIN ".ZKP_SQL."_tb_order AS ord ON c.cus_code = ord.ord_cus_to
    JOIN ".ZKP_SQL."_tb_order_item AS odit USING(ord_code)
    JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
    JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE " . $strWhereOrder;

$sql_return = "
SELECT
  icat.icat_pidx AS icat_pidx,
  it.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  reor.reor_code AS code,
  reor.reor_po_date AS date,
  to_char(reor.reor_po_date, 'dd-Mon-yy') as po_date,
  c.cus_code AS cus_code,
  c.cus_full_name AS cus_full_name,
  -roit.roit_qty AS qty,
  -roit.roit_unit_price AS unit_price,
  -(roit.roit_qty * roit.roit_unit_price) AS amount,
  '../order/revise_return_order.php' AS go_page
FROM
	".ZKP_SQL."_tb_customer AS c
    JOIN ".ZKP_SQL."_tb_return_order AS reor ON c.cus_code = reor.reor_cus_to
    JOIN ".ZKP_SQL."_tb_return_order_item AS roit USING(reor_code)
    JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
    JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE " . $strWhereReturn;

$sql = "$sql_order UNION $sql_return ORDER BY date, code, it_code";

// raw data
$rd		= array();
$rdIdx	= 0;
$cache	= array("","","");
$group0 = array();
$res 	=& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['cus_code'],			//0
		$col['cus_full_name'],		//1
		$col['code'],				//2
		$col['po_date'],			//3
		$col['it_code'],			//4
		$col['it_model_no'],		//5
		$col['unit_price'], 		//6
		$col['qty'],				//7
		$col['amount'],				//8
		$col['go_page']				//9
	);

	//1st grouping
	if($cache[0] != $col['cus_code']) {
		$cache[0] = $col['cus_code'];
		$group0[$col['cus_code']] = array();
	}

	if($cache[1] != $col['code']) {
		$cache[1] = $col['code'];
		$group0[$col['cus_code']][$col['code']] = array();
	}

	if($cache[2] != $col['it_code']) {
		$cache[2] = $col['it_code'];
	}

	$group0[$col['cus_code']][$col['code']][$col['it_code']] = 1;
}

echo "<pre>";
//var_dump($sql_order,$group0);
echo "</pre>";
//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$gg_total	= array(0,0);

//GROUP
foreach ($group0 as $total1 => $group1) {

	echo "<span class=\"comment\"><b> Customer : [". trim($total1). "] {$rd[$rdIdx][1]}</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th width="13%">ORDER#</th>
			<th width="9%">PO DATE</th>
			<th>MODEL NO</th>
			<th width="12%">UNIT PRICE<br/>(Rp)</th>
			<th width="8%">QTY<br>(EA)</th>
			<th width="12%">AMOUNT<br>(Rp)</th>
		</tr>\n
END;
	$print_tr_1 = 0;
	print "<tr>\n";

	//ORDER
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell_link("<span class=\"bar\">".$rd[$rdIdx][2]."</span>", ' align="center" valign="top" rowspan="'.$rowSpan.'"',
				' href="'.$rd[$rdIdx][9].'?_code='.$rd[$rdIdx][2].'"');						//order code
		cell($rd[$rdIdx][3], ' valign=""top align="center" rowspan="'.$rowSpan.'"');		//po date

		$print_tr_2 = 0;
		$total		= array(0,0);
		//ITEM
		foreach($group2 as $total3 => $group3) {
			if($print_tr_2++ > 0) print "<tr>\n";

			cell("[".trim($rd[$rdIdx][4])."] ".$rd[$rdIdx][5]);			//item
			cell(number_format((double)$rd[$rdIdx][6]), ' align="right"');		//price
			cell(number_format((double)$rd[$rdIdx][7]), ' align="right"');		//qty
			cell(number_format((double)$rd[$rdIdx][8]), ' align="right"');		//amount
			print "</tr>\n";

			$total[0] += $rd[$rdIdx][7];
			$total[1] += $rd[$rdIdx][8];
			$customer	= $rd[$rdIdx][1];
			$rdIdx++;
		}

	print "<tr>\n";
	cell("$total2", ' colspan="2"  align="right" style="color:darkblue"');
	cell(number_format((double)$total[0]), '  align="right" style="color:darkblue"');
	cell(number_format((double)$total[1]), '  align="right" style="color:darkblue"');
	print "</tr>\n";

	$gg_total[0] += $total[0];
	$gg_total[1] += $total[1];
	}
print "<tr height=\"25px\">\n";
cell("<b>[$total1] $customer</b>", ' colspan="4" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$gg_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$gg_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br />\n";
}
?>