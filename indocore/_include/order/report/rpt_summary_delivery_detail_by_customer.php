<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kimf

*
* $_po_date : Inquire Date
*f
*/
$tmp_0 = array();
$tmp_1 = array();

//SET WHERE PARAMETER
if($_sort_by == 'O') {
	$tmp_1[] = "reor.reor_code = ''";
} else if($_sort_by == 'R') {
	$tmp_0[] = "ord.ord_code = ''";
}

if ($some_date != "") {
	$tmp_0[] = "deli.deli_date = DATE '$some_date'";
	$tmp_1[] = "roit.roit_date = DATE '$some_date'";
} else {
	$tmp_0[] = "deli.deli_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_1[] = "roit.roit_date BETWEEN DATE '$period_from' AND '$period_to'";
}

if ($_paper == '0') {
	$tmp_0[]	= "ord_type_invoice = '0'";
	$tmp_1[]	= "reor_paper = 0";
} else if ($_paper == '1') {
	$tmp_0[]	= "ord_type_invoice = '1'";
	$tmp_1[]	= "reor_paper = 1";
}

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp_0[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_1[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

$tmp_0[] = "cus.cus_code = '$_cus_code' AND ord_dept = '$department'";
$tmp_1[] = "cus.cus_code = '$_cus_code' AND reor_dept = '$department'";

$strWhereOrder  = implode(" AND ", $tmp_0);
$strWhereReturn = implode(" AND ", $tmp_1);

$sql_order = "
SELECT
  icat.icat_pidx AS icat_pidx,
  it.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  ord.ord_code AS code,
  to_char(ord.ord_po_date, 'dd-Mon-yy') as po_date,
  cus.cus_code AS cus_code,
  cus.cus_full_name AS cus_full_name,
  odit.odit_qty AS qty,
  odit.odit_unit_price AS unit_price,
  (odit.odit_qty * odit.odit_unit_price) AS amount,
  to_char(deli.deli_date, 'dd-Mon-YY') AS date,
  deli.deli_date AS deli_date,
  '../order/revise_order.php?_code='||ord.ord_code AS go_page
FROM
	".ZKP_SQL."_tb_customer AS cus
    JOIN ".ZKP_SQL."_tb_order AS ord ON cus.cus_code = ord.ord_cus_to
    JOIN ".ZKP_SQL."_tb_order_item AS odit USING(ord_code)
    JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
    JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
    JOIN ".ZKP_SQL."_tb_delivery AS deli USING(ord_code)
WHERE " . $strWhereOrder. " AND ord.ord_cfm_deli_timestamp IS NOT NULL";

$sql_return = "
SELECT
  icat.icat_pidx AS icat_pidx,
  it.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  reor.reor_code AS code,
  to_char(reor.reor_po_date, 'dd-Mon-yy') as po_date,
  cus.cus_code AS cus_code,
  cus.cus_full_name AS cus_full_name,
  -roit.roit_qty AS qty,
  -roit.roit_unit_price AS unit_price,
  -(roit.roit_qty * roit.roit_unit_price) AS amount,
  to_char(roit.roit_date, 'dd-Mon-YY') AS date,
  roit.roit_date AS deli_date,
  '../order/revise_return_order.php?_code='||reor.reor_code AS go_page
FROM
	".ZKP_SQL."_tb_customer AS cus
    JOIN ".ZKP_SQL."_tb_return_order AS reor ON cus.cus_code = reor.reor_cus_to
    JOIN ".ZKP_SQL."_tb_return_order_item AS roit USING(reor_code)
    JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
    JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE " . $strWhereReturn;

$sql = "$sql_order UNION $sql_return ORDER BY icat_pidx, icat_midx, it_code, deli_date, code";

echo "<pre>";
//var_dump($sql_order);
echo "</pre>";

// raw data
$rd		= array();
$rdIdx	= 0;
$cache	= array("","","","");
$group0	= array();
$res	=& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'],		//0
		$col['it_code'],		//1
		$col['it_model_no'],	//2
		$col['code'],			//3
		$col['po_date'],		//4
		$col['date'],			//5
		$col['cus_code'], 		//6
		$col['cus_full_name'],	//7
		$col['unit_price'],		//8
		$col['qty'],			//9
		$col['amount'],			//10
		$col['go_page']			//11
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

	if($cache[2] != $col['code']) {
		$cache[2] = $col['code'];
	}
	
	$group0[$col['icat_midx']][$col['it_code']][$col['code']] = 1;
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

	//get category path from current icat_midx.
	$path = executeSP(ZKP_SQL."_getCategoryPath", $rd[$rdIdx][0]);
	eval(html_entity_decode($path[0]));	
	$path = array_reverse($path);
	$total1 = $path[1][4]." > ".$path[2][4]." > ".$path[3][4] ;

	echo "<span class=\"comment\"><b> CATEGORY: ". $total1. "</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th>MODEL NO</th>
			<th width="15%">ORDER#</th>
			<th width="12%">PO DATE</th>
			<th width="12%">DELI DATE/<br />RETURN DATE</th>
			<th width="12%">@PRICE<br/>(Rp)</th>
			<th width="8%">QTY<br>(EA)</th>
			<th width="12%">AMOUNT<br>(Rp)</th>
		</tr>\n
END;

	$gTot = array(0,0);
	$print_tr_1 = 0;
	print "<tr>\n";
	//MODEL
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".trim($rd[$rdIdx][1])."] ".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');		// Model No

		$tot = array(0,0);
		$print_tr_2 = 0;
		//ORDER
		foreach($group2 as $total3 => $group3) {
			if($print_tr_2++ > 0) print "<tr>\n";

			cell_link("<span class=\"bar\">".$rd[$rdIdx][3]."</span>", ' align="center"',
				' href="'.$rd[$rdIdx][11].'"');
			cell($rd[$rdIdx][4], ' align="center"');
			cell($rd[$rdIdx][5], ' align="center"');
			cell(number_format((double)$rd[$rdIdx][8]), ' align="right"');
			cell(number_format((double)$rd[$rdIdx][9]), ' align="right"');
			cell(number_format((double)$rd[$rdIdx][10]), ' align="right"');
			print "</tr>\n";

			$it_model_no = $rd[$rdIdx][2];
			$tot[0] += $rd[$rdIdx][9];
			$tot[1] += $rd[$rdIdx][10];
			$rdIdx++;
		}

		print "<tr>\n";
		cell("[". trim($total2) . "] " . $it_model_no, ' colspan="4" align="right" style="color:darkblue"');
		cell(number_format((double)$tot[0]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$tot[1]), ' align="right" style="color:darkblue"');
		print "</tr>\n";

		$gTot[0] += $tot[0];
		$gTot[1] += $tot[1];
	}
	
	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
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
	<th>MODEL NO</th>
	<th width="15%">ORDER#</th>
	<th width="12%">PO DATE</th>
	<th width="12%">DELI DATE/<br />RETURN DATE</th>
	<th width="12%">@PRICE<br/>(Rp)</th>
	<th width="8%">QTY<br>(EA)</th>
	<th width="12%">AMOUNT<br>(Rp)</th>
</tr>\n
END;
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$ggTot[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$ggTot[1]), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>