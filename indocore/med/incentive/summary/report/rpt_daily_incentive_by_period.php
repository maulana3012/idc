<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim
*
* $$
*/
//Variable Color
$display_css['bill_before_due'] 	= "color:#333333";
$display_css['bill_over_due'] 		= "background-color:lightyellow; color:red";
$display_css['bill_paid'] 			= "background-color:lightgrey; color:#333333";
$display_css['bill_tf_before_due']	= "color:purple";
$display_css['bill_tf_over_due']	= "background-color:lightyellow;color:purple";
$display_css['bill_tf_paid']		= "background-color:lightgrey;color:purple";
$display_css['turn_counted'] 		= "color:#EE5811";
$display_css['turn_uncounted'] 		= "color:#9D9DA1";

//SET WHERE PARAMETER
$tmp_bill = array();
$tmp_turn = array();

if(ZKP_FUNCTION == 'ALL') {
	if($_order_by != 'all'){
		$tmp_bill[]	= "bill_ordered_by = $_order_by";
		$tmp_turn[] = "turn_ordered_by = $_order_by";
	}
} else {
	$tmp_bill[]	= "bill_ordered_by = ".$cboFilter[1][ZKP_URL][0][0];
	$tmp_turn[] = "turn_ordered_by = ".$cboFilter[1][ZKP_URL][0][0];
	$tmp_bill[] = ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill_code,'billing')";
	$tmp_turn[] = ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', turn_code,'billing_return')";
}

if ($_cug_code != 'all') {
	$tmp_bill[]	= "bill_ship_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp_turn[]	= "turn_ship_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$sql_bill 	= " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
	$sql_return = " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
} else {
	$sql_bill = "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = bill_ship_to),
		'Others') AS cug_name,";
	$sql_return = "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = turn_ship_to),
		'Others') AS cug_name,";
}

if($_dept != 'all') {
	$tmp_bill[]	= "bill_dept = '$_dept'";
	$tmp_turn[]	= "turn_dept = '$_dept'";
}

if($_filter_doc == 'I'){
	$tmp_turn[] = "turn_code = ''";
} else if($_filter_doc == 'R') {
	$tmp_bill[]	= "bill_code = ''";
}

if($_vat == 'vat') {
	$tmp_bill[]	= "bill_vat > 0";
	$tmp_turn[]	= "turn_vat > 0";  
} else if($_vat == 'vat-IO') {
	$tmp_bill[]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
	$tmp_turn[]	= "turn_vat > 0 AND turn_code = ''";
} else if($_vat == 'vat-IP') {
	$tmp_bill[]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
	$tmp_turn[]	= "turn_vat > 0 AND turn_code = ''";
} else if ($_vat == 'non') {
	$tmp_bill[]	= "bill_vat = 0";
	$tmp_turn[]	= "turn_vat = 0";
}

if($_status == 'paid') {
	$tmp_bill[]	= "bill_remain_amount <= 0";
	$tmp_turn[] = "turn_code is null";  
} else if($_status == 'unpaid') {
	$tmp_bill[]	= "bill_total_billing_rev = bill_remain_amount";
	$tmp_turn[] = "turn_code is null";  
} else if($_status == 'half_paid') {
	$tmp_bill[]	= "bill_remain_amount < bill_total_billing_rev AND bill_remain_amount > 0";
	$tmp_turn[] = "turn_code is null";  
} else if($_status == 'has_bal') {
	$tmp_bill[]	= "bill_remain_amount > 0";
	$tmp_turn[] = "turn_code is null";  
}

if ($some_date != "") {
	$tmp_bill[] = "bill_inv_date = DATE '$some_date'";
	$tmp_turn[] = "turn_return_date = DATE '$some_date'";
} else {
	$tmp_bill[] = "bill_inv_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
	$tmp_turn[] = "turn_return_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
}

if($S->getValue('ma_see_all')) {
	if($_marketing != "all") {
		$tmp_bill[]	= "bill_responsible_by = $_marketing";
		$tmp_turn[] = "turn_responsible_by = $_marketing";
	}
} else {
	$tmp_bill[] = "bill_responsible_by = ". $S->getValue("ma_idx");
	$tmp_turn[] = "turn_responsible_by = ". $S->getValue("ma_idx");
}

$strWhereBill = implode(" AND ", $tmp_bill);
$strWhereTurn = implode(" AND ", $tmp_turn);

//DEFAULT LIST
$sql_bill .="
  cus_code,
  cus_full_name,
  bill_code AS invoice_code,
  to_char(bill_inv_date,'dd/Mon/YY') AS invoice_date,
  bill_inv_date AS date,
  to_char(bill_inv_date, 'YYMM') AS month,
  to_char(bill_inv_date, 'Mon, YYYY') AS bill_month,
  EXTRACT(WEEK FROM bill_inv_date) AS bill_week,
  trunc((bill_total_billing - bill_delivery_freight_charge) * 100 / (bill_vat+100)) AS amount,
  trunc((bill_total_billing - bill_delivery_freight_charge) * 100 / (bill_vat+100) * bill_vat/100) AS vat,
  trunc(bill_total_billing - bill_delivery_freight_charge) AS amount_vat,
  bill_remain_amount AS remain_amount,
  CASE
	WHEN bill_cfm_tukar_faktur IS NOT NULL AND bill_remain_amount > 0 AND bill_payment_giro_due > CURRENT_TIMESTAMP THEN 'bill_tf_before_due'
	WHEN bill_cfm_tukar_faktur IS NOT NULL AND bill_remain_amount > 0 AND bill_payment_giro_due < CURRENT_TIMESTAMP THEN 'bill_tf_over_due'
	WHEN bill_cfm_tukar_faktur IS NOT NULL AND bill_remain_amount <= 0 THEN 'bill_tf_paid'
	WHEN bill_total_billing = 0 THEN 'bill_before_due'
	WHEN bill_remain_amount <= 0 THEN 'bill_paid'
	WHEN bill_remain_amount > 0 AND bill_payment_giro_due > CURRENT_TIMESTAMP THEN 'bill_before_due'
	WHEN bill_remain_amount > 0 AND bill_payment_giro_due < CURRENT_TIMESTAMP THEN 'bill_over_due'
  END AS invoice_layout,
  CASE
	WHEN bill_dept = 'A' THEN '../../apotik/billing/revise_billing.php?_code='||bill_code
	WHEN bill_dept = 'D' THEN '../../dealer/billing/revise_billing.php?_code='||bill_code
	WHEN bill_dept = 'H' THEN '../../hospital/billing/revise_billing.php?_code='||bill_code
	WHEN bill_dept = 'M' THEN '../../marketing/billing/revise_billing.php?_code='||bill_code
	WHEN bill_dept = 'P' THEN '../../pharmaceutical/billing/revise_billing.php?_code='||bill_code
	WHEN bill_dept = 'T' THEN '../../tender/billing/revise_billing.php?_code='||bill_code
  END AS go_page
FROM
  ".ZKP_SQL."_tb_customer AS c
  JOIN ".ZKP_SQL."_tb_billing AS b ON bill_ship_to = cus_code
WHERE $strWhereBill";

$sql_return .="
  cus_code,
  cus_full_name,
  turn_code AS invoice_code,
  to_char(turn_return_date,'dd/Mon/YY') AS invoice_date,
  turn_return_date AS date,
  to_char(turn_return_date, 'YYMM') AS month,
  to_char(turn_return_date, 'Mon, YYYY') AS bill_month,
  EXTRACT(WEEK FROM turn_return_date) AS bill_week,
  trunc((turn_total_return - turn_delivery_freight_charge) * 100 / (turn_vat+100))*-1 AS amount,
  trunc((turn_total_return - turn_delivery_freight_charge) * 100 / (turn_vat+100) * turn_vat/100)*-1 AS vat,
  trunc(turn_total_return - turn_delivery_freight_charge)*-1 AS amount_vat,
  null AS remain_amount,
  CASE
	WHEN turn_return_condition = 1 THEN 'turn_uncounted'
	ELSE 'turn_counted'
  END AS invoice_layout,
  CASE
	WHEN b.turn_dept = 'A' THEN '../../apotik/billing/revise_return.php?_code='||turn_code
	WHEN b.turn_dept = 'D' THEN '../../dealer/billing/revise_return.php?_code='||turn_code
	WHEN b.turn_dept = 'H' THEN '../../hospital/billing/revise_return.php?_code='||turn_code
	WHEN b.turn_dept = 'M' THEN '../../marketing/billing/revise_return.php?_code='||turn_code
	WHEN b.turn_dept = 'P' THEN '../../pharmaceutical/billing/revise_return.php?_code='||turn_code
	WHEN b.turn_dept = 'T' THEN '../../tender/billing/revise_return.php?_code='||turn_code
  END AS go_page
FROM
  ".ZKP_SQL."_tb_customer AS c
  JOIN ".ZKP_SQL."_tb_return AS b ON  turn_ship_to = cus_code
WHERE $strWhereTurn";

$sql = "$sql_bill UNION $sql_return ORDER BY month, bill_week, date, invoice_code";
/*
echo "<pre>";
var_dump($sql);
echo "</pre>";
exit;
*/
// raw data
$rd		= array();
$rdIdx	= 0;
$cache	= array("","","");
$group0 = array();
$res = query($sql);
while($col = fetchRowAssoc($res)) {

	$rd[] = array(
		$col['bill_month'],				//0
		$col['bill_week'], 				//1
		$col['invoice_date'], 			//2
		$col['invoice_code'],			//3
		$col['cus_code'],				//4
		$col['cus_full_name'],			//5
		$col['amount'],					//6
		$col['vat'], 					//7
		$col['amount_vat'], 			//8
		$col['remain_amount'], 			//9
		$col['invoice_layout'],			//10
		$col['go_page'],				//11
	);

	if($cache[0] != $col['bill_month']) {
		$cache[0] = $col['bill_month'];
		$group0[$col['bill_month']] = array();
	}

	if($cache[1] != $col['bill_week']) {
		$cache[1] = $col['bill_week'];
		$group0[$col['bill_month']][$col['bill_week']] = array();
	}
	
	if($cache[2] != $col['invoice_code']) {
		$cache[2] = $col['invoice_code'];
	}

	$group0[$col['bill_month']][$col['bill_week']][$col['invoice_code']] = 1;
}

//grand summary
$grand_total = array (0,0,0,0,0,0,0);

//GROUP BY MONTH
foreach ($group0 as $month_name => $month) {
	echo "<span class=\"comment\"><b>[". $month_name. "]</b></span>\n";
	print <<<END
	<table width="80%" class="table_f">
		<tr>
			<th width="10%">INV. DATE</th>
			<th width="22%">INV. NO</th>
			<th>CUSTOMER</th>
			<th width="7%">AMOUNT<br>(Rp)</th>
			<th width="7%">VAT<br>(Rp)</th>
			<th width="7%">AMOUNT<br>+VAT</th>
			<th width="7%">REMAIN<br>(Rp)</th>
		</tr>\n
END;

	//monthly summary
	$monthly_summary = array (0,0,0,0,0,0,0);
	$weekth = array();

	foreach ($month as $week_name => $bill_week) {
		
		$weekth = getWeek($rd[$rdIdx][1] * 604800); 	//7day * 24 hour * 60 Min * 60 Sec

		print "<tr>\n";
		print "<td colspan=\"7\">{$weekth['string']}</td>\n";
		print "</tr>\n";
		
		//weekly summary
		$weekly_summary = array(0,0,0,0);
		$print_tr_1 = 0;
		foreach ($bill_week as $billing) {
			if($print_tr_1++ > 0) print "<tr>\n";
			cell($rd[$rdIdx][2], ' style="'.$display_css[$rd[$rdIdx][10]].'" align="center" valign="top"');					// Invoice date
			cell_link($rd[$rdIdx][3], ' style="'.$display_css[$rd[$rdIdx][10]].'" align="center" valign="top"',				// Invoice code
				' href="'.$rd[$rdIdx][11].'" target="_parent"');
			cell($rd[$rdIdx][5], ' style="'.$display_css[$rd[$rdIdx][10]].'" valign="top"');								// Customer
			cell(number_format($rd[$rdIdx][6]), ' style="'.$display_css[$rd[$rdIdx][10]].'" align="right", valign="top"'); 	// amount
			cell(number_format($rd[$rdIdx][7]), ' style="'.$display_css[$rd[$rdIdx][10]].'" align="right", valign="top"');	// vat
			cell(number_format($rd[$rdIdx][8]), ' style="'.$display_css[$rd[$rdIdx][10]].'" align="right", valign="top"');	// amount+vat
			cell(number_format($rd[$rdIdx][9]), ' style="'.$display_css[$rd[$rdIdx][10]].'" align="right", valign="top"');	// remain
			print "</tr>\n";

			//SUB TOTAL
			if($rd[$rdIdx][10] != "turn_uncounted") {
				$weekly_summary[0] += $rd[$rdIdx][6]; 	//Amount
				$weekly_summary[1] += $rd[$rdIdx][7];	//Vat
				$weekly_summary[2] += $rd[$rdIdx][8];	//Grand total
				$weekly_summary[3] += $rd[$rdIdx][9];	//Remain amount
			}
			$rdIdx++;
		}

		print "</tr>\n";
		cell($weekth['string'], ' colspan="3"  align="right" align="right" style="color:brown"');
		cell(number_format($weekly_summary[0]), ' align="right" style="color:brown"');
		cell(number_format($weekly_summary[1]), ' align="right" style="color:brown"');
		cell(number_format($weekly_summary[2]), ' align="right" style="color:brown"');
		cell(number_format($weekly_summary[3]), ' align="right" style="color:brown"');
		print "</tr>\n";

		//Monthly TOTAL
		$monthly_summary[0] += $weekly_summary[0];
		$monthly_summary[1] += $weekly_summary[1];
		$monthly_summary[2] += $weekly_summary[2];
		$monthly_summary[3] += $weekly_summary[3];
	}
	
	print "<tr>\n";
	cell('<b>'.$month_name.'<b>', ' colspan="3"  align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($monthly_summary[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($monthly_summary[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($monthly_summary[2]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($monthly_summary[3]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br>\n";

	//Monthly TOTAL
	$grand_total[0] += $monthly_summary[0];
	$grand_total[1] += $monthly_summary[1];
	$grand_total[2] += $monthly_summary[2];
	$grand_total[3] += $monthly_summary[3];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="80%" class="table_f">
	<tr>
		<th width="10%">INV. DATE</th>
		<th width="22%">INV. NO</th>
		<th>CUSTOMER</th>
		<th width="7%">AMOUNT<br>(Rp)</th>
		<th width="7%">VAT<br>(Rp)</th>
		<th width="7%">AMOUNT<br>+VAT</th>
		<th width="7%">REMAIN<br>(Rp)</th>
	</tr>\n
END;
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="3"  align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[3]), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br>\n";
?>