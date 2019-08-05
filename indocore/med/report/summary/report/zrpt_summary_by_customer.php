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
$dept['A']	= 'Apotik Team Sales Data by Customer';
$dept['D']	= 'Dealer Team Sales Data by Customer';
$dept['H']	= 'Hospital Team Sales Data by Customer';
$dept['M']	= 'Marketing Team Sales Data by Customer';
$dept['P']	= 'Pharmaceutical Team Sales Data by Customer';
$dept['T']	= 'Tender Team Sales Data by Customer';

$tmp_bill = array();
$tmp_sl = array();
$tmp_turn = array();
$tmp_cus_bill  = array();
$tmp_cus_sl  = array();
$tmp_cus_turn  = array();

if(ZKP_FUNCTION == 'ALL') {
	if($_order_by == 1){
		$tmp_bill[] = "bill_ordered_by = 1";
		$tmp_turn[] = "turn_ordered_by = 1";
	} else if($_order_by == 2) {
		$tmp_bill[] = "bill_ordered_by = 2";
		$tmp_sl[] = "bill_code is null";
		$tmp_turn[] = "turn_ordered_by = 2";
	}
} else {
	$tmp_bill[]		= "bill_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill_code,'billing')";
	$tmp_turn[]		= "turn_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', turn_code,'billing_return')";
	$tmp_sl[] 		= "bill_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill_code,'billing')";
}

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp_bill[]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_sl[]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_turn[]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if($_marketing != "all") {
	$tmp_bill[]	= "cus_responsibility_to = $_marketing";
	$tmp_sl[]	= "cus_responsibility_to = $_marketing";
	$tmp_turn[] = "cus_responsibility_to = $_marketing";
}

if ($_filter_doc == "I") {
	$tmp_turn[]   = "turn_code = NULL";
} else if ($_filter_doc == "R") {
	$tmp_bill[]   = "bill_code = NULL";
	$tmp_sl[]   = "bill_code = NULL";
}

if($_dept != 'all') {
	$tmp_bill[] = "bill_dept = '$_dept'";
	$tmp_sl[] = "bill_dept = '$_dept'";
	$tmp_turn[] = "turn_dept = '$_dept'";
}

if ($some_date != "") {
	$tmp_bill[]   = "bill_inv_date =DATE '$some_date'";
	$tmp_sl[]   = "bill_inv_date =DATE '$some_date'";
	$tmp_turn[]   = "turn_return_date =DATE '$some_date'";
} else {
	$tmp_bill[]   = "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_sl[]   = "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_turn[]   = "turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
}

if($_vat == 'vat') {
	$tmp_bill[]		= "bill_vat > 0";
	$tmp_sl[]		= "bill_vat > 0";
	$tmp_turn[]		= "turn_vat > 0";
} else if($_vat == 'vat-IO') {
	$tmp_bill[]		= "bill_vat > 0 AND bill_type_pajak = 'IO'";
	$tmp_sl[]		= "bill_vat > 0 AND bill_type_pajak = 'IO'";
	$tmp_turn[]		= "turn_vat > 0";
} else if($_vat == 'vat-IP') {
	$tmp_bill[]		= "bill_vat > 0 AND bill_type_pajak = 'IP'";
	$tmp_sl[]		= "bill_vat > 0 AND bill_type_pajak = 'IP'";
	$tmp_turn[]		= "turn_code = NULL";
} else if ($_vat == 'non') {
	$tmp_bill[]		= "bill_vat = 0";
	$tmp_sl[]		= "bill_vat = 0";
	$tmp_turn[]		= "turn_vat = 0";
}

$tmp_bill[] = "bill_type_billing in (1,2)";
$tmp_sl[]	= "bill_type_billing = 3";

$strWhereBill		= implode(" AND ", $tmp_bill);
$strWhereSales		= implode(" AND ", $tmp_sl);
$strWhereTurn		= implode(" AND ", $tmp_turn);

$sql_bill = "
SELECT
  ".ZKP_SQL."_getGroupName(bill_dept, bill_ship_to) AS cug_name,
  bill_dept AS dept,
  c.cus_code AS ship_to,
  cus_full_name AS ship_to_name,
  trunc(sum(biit_qty * (biit_unit_price*(100-bill_discount)/100))) AS amount
FROM
  ".ZKP_SQL."_tb_customer AS c
  JOIN ".ZKP_SQL."_tb_billing AS b ON bill_ship_to = cus_code
  JOIN ".ZKP_SQL."_tb_billing_item USING(bill_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
  JOIN ".ZKP_SQL."_tb_item USING(it_code)
WHERE $strWhereBill
GROUP BY dept, cug_name, ship_to, ship_to_name";

$sql_sales = "
SELECT
  ".ZKP_SQL."_getGroupName(bill_dept, cus_code) AS cug_name,
  bill_dept AS dept,
  s.cus_code AS ship_to,
  (select cus_full_name from ".ZKP_SQL."_tb_customer where cus_code=s.cus_code) AS ship_to_name,
  sum(sl_qty * sl_payment_price) AS amount
FROM
  ".ZKP_SQL."_tb_billing AS b
  JOIN ".ZKP_SQL."_tb_billing_sales AS s USING(bill_code)
  JOIN ".ZKP_SQL."_tb_sales_log AS c USING(cus_code)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE $strWhereSales AND sl_date=s.bisl_date /*AND sl_faktur_no=s.bisl_sl_faktur_no AND sl_lop_no=s.bisl_lop_no*/
GROUP BY dept, cug_name, ship_to, ship_to_name";

$sql_turn = "
SELECT
  ".ZKP_SQL."_getGroupName(turn_dept, turn_ship_to) AS cug_name,
  turn_dept AS dept,
  c.cus_code AS ship_to,
  cus_full_name AS ship_to_name,
  SUM(-(turn_amount_qty_unit_price)) AS amount
FROM
  ".ZKP_SQL."_tb_customer AS c
  JOIN ".ZKP_SQL."_tb_return AS t ON turn_ship_to = cus_code
  JOIN ".ZKP_SQL."_tb_return_item USING(turn_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE $strWhereTurn AND turn_return_condition IN (2,3,4)
GROUP BY dept, cug_name, ship_to, ship_to_name";

$sql = "$sql_bill UNION $sql_sales UNION $sql_turn ORDER BY dept, cug_name, ship_to";
/*
echo "<pre>";
echo $sql;
echo "</pre>";
exit;
*/
// raw data
$rd 	= array();
$rdIdx	= 0;
$cache	= array("","",""); // 3th level
$group0 = array();
$amount = array('A'=>0,'D'=>0,'H'=>0,'M'=>0,'P'=>0,'T'=>0);
$a 		= '';

$res =& query($sql);
while($col =& fetchRowAssoc($res)) {
	if($a != $col['ship_to']) {
		$rd[] = array(
			$col['dept'],			//0
			$col['cug_name'],		//1
			$col['ship_to'],		//2
			$col['ship_to_name']	//3
		);

		//1st grouping
		if($cache[0] != $col['dept']) {
			$cache[0] = $col['dept'];
			$group0[$col['dept']] = array();
		}

		if($cache[1] != $col['cug_name']) {
			$cache[1] = $col['cug_name'];
			$group0[$col['dept']][$col['cug_name']] = array();
		}
	
		if($cache[2] != $col['ship_to']) {
			$cache[2] = $col['ship_to'];
		}

		$group0[$col['dept']][$col['cug_name']][$col['ship_to']] = 1;
	}
	$a = $col['ship_to'];

	//to get total amount each dept, cug name, customer
	$amount[$col['dept']] += $col['amount'];

	if(!isset($sub_amount[$col['dept']][$col['cug_name']])) {
		$sub_amount[$col['dept']][$col['cug_name']] = $col['amount'];
	} else {
		$sub_amount[$col['dept']][$col['cug_name']] += $col['amount'];
	}

	if(!isset($cus_amount[$col['dept']][$col['cug_name']][$col['ship_to']])) {
		$cus_amount[$col['dept']][$col['cug_name']][$col['ship_to']] = $col['amount'];
	} else {
		$cus_amount[$col['dept']][$col['cug_name']][$col['ship_to']] += $col['amount'];
	}
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$grand_total = 0;
$amount_cug	 = array();
//DEPARTEMEN
foreach ($group0 as $total1 => $group1) {

	echo "<span class=\"comment\"><b>{$dept[$rd[$rdIdx][0]]}</b></span>\n";
	print <<<END
	<table width="80%" class="table_f">
		<tr>
			<th width="20%">GROUP</th>
			<th>NAME</th>
			<th width="15%">AMOUNT<br>(Rp)</th>
			<th width="10%">RATE</th>
			<th width="10%">RATE</th>
		</tr>\n
END;
	$dept_amount = ARRAY(0,0); //amount, rate
	$print_tr_1 = 0;

	print "<tr>\n";
	//GROUP
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		$rowSpan += count($group2);

		if($print_tr_1++ > 0) print "<tr>\n";
		cell($rd[$rdIdx][1], ' valign="middle" align="center" rowspan="'.$rowSpan.'"');		//customer group name

		$group_amount = ARRAY(0,0); //amount, rate
		$a = '';
		$b = '';
		$rowSpan = $rowSpan + 1;
		$print_tr_2 = 0;
		//CUSTOMER
		foreach($group2 as $total3 => $group3) {

			$dep_amount = $amount[$rd[$rdIdx][0]];
			$subb_amount = $sub_amount[$rd[$rdIdx][0]][$rd[$rdIdx][1]];
			$cust_amount = $cus_amount[$rd[$rdIdx][0]][$rd[$rdIdx][1]][$rd[$rdIdx][2]];

			if($cust_amount == 0) {
				$rate = 0;
			} else if($cust_amount > 0) {
				$rate = $cust_amount*100/$subb_amount;
				if($rate < 0) $rate = $rate*-1;
			} else if($cust_amount < 0) {
				$rate = -($cust_amount*100/$subb_amount);
				if($rate > 0) $rate = $rate*-1;	
			}

			if($subb_amount == 0) $sub_rate = 0;
			else if($subb_amount > 0) {
				$sub_rate = $subb_amount*100/$dep_amount;
				if($sub_rate < 0) $sub_rate = $sub_rate*-1;
			} else if($subb_amount < 0) {
				$sub_rate = -($subb_amount*100/$dep_amount);
				if($sub_rate > 0) $sub_rate = $sub_rate*-1;
			}

			//PRINT CONTENT
			if($print_tr_2++ > 0) print "<tr>\n";
			cell("[".trim($rd[$rdIdx][2])."] ".$rd[$rdIdx][3], ' valign="top"');		//customer name
			cell(number_format((double)$cust_amount), ' align="right"');	//customer amount
			cell(number_format((double)$rate,2)." %", ' align="right"');	//customer percentage
			if($a != $rd[$rdIdx][1]) {
				cell(number_format((double)$sub_rate,2)." %", ' rowspan="'. $rowSpan .'" align="right"  style="color:darkblue;"');
				$a = $rd[$rdIdx][1];
			}
			print "</tr>\n";

			$b = $rd[$rdIdx][2];

			$group_amount[0]	+= $cust_amount;
			$group_amount[1]	+= $rate;
			$div 			= $rd[$rdIdx][0];
			$group_name		= $rd[$rdIdx][1];
			$rdIdx++;
		}
		cell($group_name, ' colspan="2" align="right" style="color:darkblue;"');			//customer group name
		cell(number_format((double)$group_amount[0]), ' align="right" style="color:darkblue;"');	//customer group amount
		cell(number_format((double)$group_amount[1],2)." %", ' align="right" style="color:darkblue;"');	//customer total percentage

		$dept_amount[0]	+= $group_amount[0];
		$dept_amount[1] += $sub_rate;
	}
	
	print "<tr>\n";
	cell("<b>TOTAL {$dept[$div]}</b>", ' colspan="2" align="right" style="color:brown; background-color:lightyellow"');	//dept name
	cell(number_format((double)$dept_amount[0]), ' align="right" style="color:brown; background-color:lightyellow"');			//dept total billing amount before vat
	cell('', ' align="right" style="color:brown; background-color:lightyellow"');										//
	cell(number_format((double)$dept_amount[1],2)." %", ' align="right" style="color:brown; background-color:lightyellow"');	//dept total percentage
	print "</tr>\n";
	print "</table><br />\n";

	$grand_total	+= $dept_amount[0];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="80%" class="table_f">
	<tr>
		<th width="15%">GROUP</th>
		<th>NAME</th>
		<th width="15%">AMOUNT<br>(Rp)</th>
		<th width="20%">COMPOSITION RATE</th>
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="2" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total), ' align="right" style="color:brown; background-color:lightyellow"');
cell('', ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>
