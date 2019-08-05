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
//SET WHERE PARAMETER
$tmp_0	= array();
$tmp_1	= array();

if ($_cug_code != 'all') {
	$tmp_0[]		= "c.ord_cus_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp_1[]		= "c.reor_cus_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$sql_order 		= " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
	$sql_return 	= " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
} else {
	$sql_order = "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = c.ord_cus_to),
		'Others') AS cug_name,";
	$sql_return = "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = c.reor_cus_to),
		'Others') AS cug_name,";
}

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

$sql_order .= "
  b.cus_code AS cus_code,
  b.cus_full_name AS cus_full_name,
  c.ord_code AS code,
  c.ord_po_date AS po_date,
  to_char(c.ord_po_date, 'dd-Mon-YY') AS date,
  e.it_code AS it_code,
  e.it_model_no AS it_model_no,
  d.odit_qty AS qty,
  d.odit_unit_price AS unit_price,
  (d.odit_qty * d.odit_unit_price) AS amount,
  '../order/revise_order.php' AS go_page
FROM
  ".ZKP_SQL."_tb_customer AS b
  JOIN ".ZKP_SQL."_tb_order AS c ON b.cus_code = c.ord_cus_to
  JOIN ".ZKP_SQL."_tb_order_item AS d USING(ord_code)
  JOIN ".ZKP_SQL."_tb_item AS e USING(it_code)
WHERE " . $strWhereOrder;

$sql_return .= "
  b.cus_code AS cus_code,
  b.cus_full_name AS cus_full_name,
  c.reor_code AS code,
  c.reor_po_date AS po_date,
  to_char(c.reor_po_date, 'dd-Mon-YY') AS date,
  e.it_code AS it_code,
  e.it_model_no AS it_model_no,
  -d.roit_qty AS qty,
  -d.roit_unit_price AS unit_price,
  -(d.roit_qty * d.roit_unit_price) AS amount,
  '../order/revise_return_order.php' AS go_page
FROM
  ".ZKP_SQL."_tb_customer AS b
  JOIN ".ZKP_SQL."_tb_return_order AS c ON b.cus_code = c.reor_cus_to
  JOIN ".ZKP_SQL."_tb_return_order_item AS d USING(reor_code)
  JOIN ".ZKP_SQL."_tb_item AS e USING(it_code)
WHERE " . $strWhereReturn;

$sql = "$sql_order UNION $sql_return ORDER BY cug_name, cus_code, po_date, code, it_code";

// raw data
$rd		= array();
$rdIdx	= 0;
$cache	= array("","","","");
$group0 = array();
$res	=& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['cug_name'],			//0
		$col['cus_code'],			//1
		$col['cus_full_name'],		//2
		$col['code'],				//3
		$col['date'],				//4
		$col['it_code'],			//5
		$col['it_model_no'],		//6
		$col['unit_price'],			//7
		$col['qty'],				//8
		$col['amount'],				//9
		$col['go_page']				//10
	);

	//1st grouping
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
$gg_total	= array(0,0);

//GROUP
foreach ($group0 as $total1 => $group1) {

	echo "<span class=\"comment\"><b> PUSAT: ". $total1. "</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th width="18%">CUSTOMER</th>
			<th width="13%">ORDER#</th>
			<th width="9%">PO DATE</th>
			<th>MODEL NO</th>
			<th width="10%">UNIT PRICE<br/>(Rp)</th>
			<th width="8%">QTY<br>(EA)</th>
			<th width="12%">AMOUNT<br>(Rp)</th>
		</tr>\n
END;

	$g_total	= array(0,0);
	$print_tr_1 = 0;
	
	print "<tr>\n";

	//CUSTOMER
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += count($group2);

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".trim($rd[$rdIdx][1])."]<br/>".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');	//Customer

		$print_tr_2 = 0;
		//ORDER
		foreach($group2 as $total3 => $group3) {
			$rowSpan = 0;
			array_walk_recursive($group3, 'getRowSpan');
			$rowSpan += 1;

			if($print_tr_2++ > 0) print "<tr>\n";
			cell_link("<span class=\"bar\">".$rd[$rdIdx][3]."</span>", ' align="center" valign="top" rowspan="'.$rowSpan.'"',
				' href="'.$rd[$rdIdx][10].'?_code='.$rd[$rdIdx][3].'"');							//code
			cell($rd[$rdIdx][4], ' valign=""top align="center" rowspan="'.$rowSpan.'"');			//date

			$print_tr_3	= 0;
			$total		= array(0,0);
			//ITEM
			foreach($group3 as $group4) {
				if($print_tr_3++ > 0) print "<tr>\n";
				
				cell("[".trim($rd[$rdIdx][5])."] ".$rd[$rdIdx][6]);			//item
				cell(number_format((double)$rd[$rdIdx][7]), ' align="right"');		//unit price
				cell(number_format((double)$rd[$rdIdx][8]), ' align="right"');		//qty
				cell(number_format((double)$rd[$rdIdx][9]), ' align="right"');		//amount
				print "</tr>\n";

				$total[0]	+= $rd[$rdIdx][8];
				$total[1]	+= $rd[$rdIdx][9];
				$code		= $rd[$rdIdx][3];
				$rdIdx++;
			}
			
			print "<tr>\n";
			cell($code, ' colspan="2" align="right" style="color:darkblue"');
			cell(number_format((double)$total[0]), ' align="right" style="color:darkblue"');
			cell(number_format((double)$total[1]), ' align="right" style="color:darkblue"');
			print "</tr>\n";

			$g_total[0]	+= $total[0];
			$g_total[1]	+= $total[1];
		}
	}

	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$g_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$g_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$gg_total[0]	+= $g_total[0];
	$gg_total[1]	+= $g_total[1];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th width="18%">CUSTOMER</th>
			<th width="13%">ORDER#</th>
			<th width="9%">PO DATE</th>
			<th>MODEL NO</th>
			<th width="10%">UNIT PRICE<br/>(Rp)</th>
			<th width="8%">QTY<br>(EA)</th>
			<th width="12%">AMOUNT<br>(Rp)</th>
		</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$gg_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$gg_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>