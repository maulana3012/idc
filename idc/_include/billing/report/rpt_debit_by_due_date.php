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
	$tmp_bill[]	= "bill_ordered_by = ".$cboFilter[1][ZKP_FUNCTION][0][0];
	$tmp_turn[] = "turn_ordered_by = ".$cboFilter[1][ZKP_FUNCTION][0][0];
	$tmp_bill[] = ZKP_SQL."_isValidShowInvoice('".ZKP_FUNCTION."', bill_code,'billing')";
	$tmp_turn[] = ZKP_SQL."_isValidShowInvoice('".ZKP_FUNCTION."', turn_code,'billing_return')";
}

if($_filter_date == 'inv' && $s_mode == "date") {
    $tmp_bill[] = "bill_inv_date = DATE '$some_date'";
    $tmp_turn[] = "turn_return_date = DATE '$some_date'";
} else if($_filter_date == 'inv' && $s_mode == "period") {
    $tmp_bill[] = "bill_inv_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
    $tmp_turn[] = "turn_return_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
} else if($_filter_date == 'due' && $s_mode == "date") {
    $tmp_bill[] = "bill_payment_giro_due = DATE '$some_date'";
    $tmp_turn[] = "turn_return_date = DATE '$some_date'";
} else if($_filter_date == 'due' && $s_mode == "period") {
    $tmp_bill[] = "bill_payment_giro_due  BETWEEN DATE '$period_from' AND DATE '$period_to'";
    $tmp_turn[] = "turn_return_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
}

if($_marketing != "all") {
	$tmp_bill[]	= "cus_responsibility_to = $_marketing";
	$tmp_turn[] = "cus_responsibility_to = $_marketing";
}

if($_filter_doc == 'I'){
	$tmp_turn[] = "turn_code = ''";
} else if($_filter_doc == 'R') {
	$tmp_bill[] = "bill_code = ''";
}

if ($_cug_code != 'all') {
	$tmp_bill[]	= "bill_ship_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp_turn[]	= "turn_ship_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$sql_bill 	= " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
	$sql_turn = " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
} else {
	$sql_bill = "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = bill_ship_to),
		'Others') AS cug_name,";
	$sql_turn = "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = turn_ship_to),
		'Others') AS cug_name,";
}

if($_vat == 'vat') {
	$tmp_bill[]	= "bill_vat > 0";
	$tmp_turn[] = "turn_vat > 0";  
} else if($_vat == 'vat-IO') {
	$tmp_bill[]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
	$tmp_turn[] = "turn_vat > 0 AND turn_code = ''";  
}else if($_vat == 'vat-IP') {
	$tmp_bill[]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
	$tmp_turn[] = "turn_vat > 0 AND turn_code = ''";  
} else if ($_vat == 'non') {
	$tmp_bill[] = "bill_vat = 0";
	$tmp_turn[] = "turn_vat = 0";
}

if($_status == 'paid') {
	$tmp_bill[]	= "b.bill_remain_amount <= 0";
	$tmp_turn[] = "t.turn_code = ''";  
} else if($_status == 'unpaid') {
	$tmp_bill[]	= "b.bill_total_billing_rev = b.bill_remain_amount";
	$tmp_turn[] = "t.turn_code = ''";
} else if($_status == 'half_paid') {
	$tmp_bill[]	= "b.bill_remain_amount < b.bill_total_billing_rev AND b.bill_remain_amount > 0";
	$tmp_turn[] = "t.turn_code = ''";  
} else if($_status == 'has_bal') {
	$tmp_bill[]	= "b.bill_remain_amount > 0";
	$tmp_turn[] = "t.turn_code = ''";
}

$tmp_bill[]		= "bill_dept = '$department'";
$tmp_turn[]		= "turn_dept = '$department' AND turn_return_condition IN (2,3,4)";

$strWhereBill = implode(" AND ", $tmp_bill);
$strWhereTurn = implode(" AND ", $tmp_turn);

//continue sql
$sql_bill ="
SELECT
  to_char(b.bill_payment_giro_due, 'YYMM') AS month,
  to_char(b.bill_payment_giro_due, 'Mon, YYYY') AS due_month,
  EXTRACT(WEEK FROM b.bill_payment_giro_due) AS due_week,
  to_char(b.bill_inv_date, 'dd/Mon/YY') AS invoice_issue_date,
  b.bill_code AS invoice_code,
  c.cus_code AS ship_to,
  c.cus_full_name AS ship_to_name,
  CASE
  	WHEN (b.bill_payment_chk & 16) > 0 THEN 'CASH'
	WHEN (b.bill_payment_chk & 32) > 0THEN 'CHECK'
	WHEN (b.bill_payment_chk & 64) > 0 THEN 'T/S'
 	WHEN (b.bill_payment_chk & 128) > 0 THEN 'GIRO'
 	ELSE '-'
  END AS payment_method,
  to_char(b.bill_payment_giro_due, 'dd/Mon/YY') AS invoice_due_date,
  b.bill_payment_bank AS bank,
  ".ZKP_SQL."_getDueRemain(b.bill_code) AS due_remain,
  (b.bill_total_billing - b.bill_delivery_freight_charge) * 100 / (b.bill_vat+100) AS amount,
  b.bill_delivery_freight_charge AS delivery_charge,
  (b.bill_total_billing - b.bill_delivery_freight_charge) * 100 / (b.bill_vat+100) * b.bill_vat/100 AS vat,
  b.bill_total_billing - b.bill_delivery_freight_charge AS amount_vat,
  b.bill_total_billing AS grand_total,
  b.bill_total_billing_rev - b.bill_remain_amount AS amount_paid,
  b.bill_remain_amount AS remain_amount,
  to_char(b.bill_last_payment_date,'dd/Mon/YY') AS last_payment_date,

  b.bill_payment_giro_due AS due_date,
  'billing' AS invoice_condition,
  CASE
	WHEN b.bill_cfm_tukar_faktur IS NOT NULL AND b.bill_remain_amount > 0 AND b.bill_payment_giro_due > CURRENT_TIMESTAMP THEN 'bill_tf_before_due'
	WHEN b.bill_cfm_tukar_faktur IS NOT NULL AND b.bill_remain_amount > 0 AND b.bill_payment_giro_due < CURRENT_TIMESTAMP THEN 'bill_tf_over_due'
	WHEN b.bill_cfm_tukar_faktur IS NOT NULL AND b.bill_remain_amount <= 0 THEN 'bill_tf_paid'
	WHEN b.bill_total_billing = 0 THEN 'bill_before_due'
	WHEN b.bill_remain_amount <= 0 THEN 'bill_paid'
	WHEN b.bill_remain_amount > 0 AND b.bill_payment_giro_due > CURRENT_TIMESTAMP THEN 'bill_before_due'
	WHEN b.bill_remain_amount > 0 AND b.bill_payment_giro_due < CURRENT_TIMESTAMP THEN 'bill_over_due'
  END AS invoice_layout,
  '../billing/revise_billing.php?_code='||bill_code AS go_page
FROM ".ZKP_SQL."_tb_customer AS c JOIN ".ZKP_SQL."_tb_billing AS b ON c.cus_code = b.bill_ship_to
WHERE $strWhereBill";

$sql_turn ="
SELECT
  to_char(t.turn_return_date, 'YYMM') AS month,
  to_char(t.turn_return_date, 'Mon, YYYY') AS due_month,
  EXTRACT(WEEK FROM t.turn_return_date) AS due_week,
  to_char(t.turn_return_date, 'dd/Mon/YY') AS invoice_issue_date,
  t.turn_code AS invoice_code,
  c.cus_code AS ship_to,
  c.cus_full_name AS ship_to_name,
  CASE
	WHEN (t.turn_payment_chk & 16) > 0 THEN 'CASH'
 	WHEN (t.turn_payment_chk & 32) > 0THEN 'CHECK'
 	WHEN (t.turn_payment_chk & 64) > 0 THEN 'T/S'
 	WHEN (t.turn_payment_chk & 128) > 0 THEN 'GIRO'
 	ELSE '-'
  END AS payment_method,
  to_char(t.turn_payment_giro_due, 'dd/Mon/YY') AS invoice_due_date,
  t.turn_payment_bank AS bank,
  ".ZKP_SQL."_getDueRemain(t.turn_code) AS due_remain,
  ((t.turn_total_return - t.turn_delivery_freight_charge) * 100 / (t.turn_vat+100)) AS amount,
  (t.turn_delivery_freight_charge) AS delivery_charge,
  ((t.turn_total_return - t.turn_delivery_freight_charge) * 100 / (t.turn_vat+100) * t.turn_vat/100) AS vat,
  (t.turn_total_return - t.turn_delivery_freight_charge) AS amount_vat,
  (t.turn_total_return) AS grand_total,
  (t.turn_total_return) AS amount_paid,
  NULL AS remain_amount,
  NULL AS last_payment_date,

  t.turn_payment_giro_due AS due_date,
  CASE
	WHEN t.turn_return_condition = 1 THEN 'return_1' 
	WHEN t.turn_return_condition = 2 THEN 'return_2'
    WHEN t.turn_return_condition = 3 THEN 'return_3'
	WHEN t.turn_return_condition = 4 THEN 'return_4'
  END AS invoice_condition,
  'turn_counted' AS invoice_layout,
  '../billing/revise_return.php?_code='||turn_code AS go_page
FROM ".ZKP_SQL."_tb_customer AS c JOIN ".ZKP_SQL."_tb_return AS t ON c.cus_code = t.turn_ship_to 
WHERE $strWhereTurn";

$sql = "$sql_bill UNION $sql_turn ORDER BY month, due_week, due_date";

// raw data
$rd		= array();
$rdIdx	= 0;
$cache	= array("","","");
$group0 = array();

$res = query($sql);
while($col = fetchRowAssoc($res)) {

	$rd[] = array(
		$col['due_month'],			//0
		$col['due_week'],			//1
		$col['invoice_issue_date'], //2
		$col['invoice_code'],		//3
		$col['ship_to'],			//4
		$col['ship_to_name'],		//5
		$col['payment_method'],		//6
		$col['invoice_due_date'], 	//7
		$col['bank'], 				//8
		$col['due_remain'], 		//9
		$col['amount'], 			//10
		$col['delivery_charge'],	//11
		$col['vat'],				//12
		$col['amount_vat'],			//13
		$col['grand_total'],		//14
		$col['amount_paid'],		//15
		$col['remain_amount'],		//16
		$col['last_payment_date'],	//17
		$col['invoice_condition'],	//18
		$col['invoice_layout'],		//19
		$col['go_page']				//20
	);

	if($cache[0] != $col['due_month']) {
		$cache[0] = $col['due_month'];
		$group0[$col['due_month']] = array();
	}

	if($cache[1] != $col['due_week']) {
		$cache[1] = $col['due_week'];
		$group0[$col['due_month']][$col['due_week']] = array();
	}
	
	if($cache[2] != $col['invoice_code']) {
		$cache[2] = $col['invoice_code'];
	}	

	$group0[$col['due_month']][$col['due_week']][$col['invoice_code']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//grand summary
$grand_total = array (0,0,0,0,0,0,0);

//GROUP BY MONTH
foreach ($group0 as $month_name => $month) {
	echo "<span class=\"comment\"><b>[". $month_name. "]</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th width="7%">INV. DATE</th>
			<th width="10%">INV. NO</th>
			<th>SHIP TO<br />CUSTOMER</th>
			<th width="3%">PAID<br />METHOD</th>
			<th width="7%">DUE DATE</th>
			<th width="7%">BANK</th>
			<th width="3%">D/S</th>
			<th width="6%">AMOUNT</th>
			<th width="6%">FREIGHT<br>(Rp)</th>
			<th width="6%">VAT<br>(Rp)</th>
			<th width="6%">AMOUNT<br>+VAT</th>
			<th width="6%">AMOUNT<br>+FRT/VAT</th>
			<th width="6%">PAID<br>(Rp)</th>
			<th width="6%">BAL<br>(Rp)</th>
			<th width="7%">LAST PAID</th>
		</tr>\n
END;

	//monthly summary
	$monthly_summary = array (0,0,0,0,0,0,0);
	$weekth = array();
	foreach ($month as $week_name => $due_week) {
		
		$weekth = getWeek($rd[$rdIdx][1] * 604800); 	//7day * 24 hour * 60 Min * 60 Sec
		print "<tr>\n";
		print "<td colspan=\"15\">{$weekth['string']}</td>\n";
		print "</tr>\n";

		//weekly summary
		$weekly_summary = array(0,0,0,0,0,0,0);

		foreach ($due_week as $invoice) {
		print "<tr>\n";
			cell($rd[$rdIdx][2], ' style="'.$display_css[$rd[$rdIdx][19]].'" align="center"');		//Invoice date
			cell_link($rd[$rdIdx][3], ' style="'.$display_css[$rd[$rdIdx][19]].'" align="center"',
				' href="'.$rd[$rdIdx][20].'" style="'.$display_css[$rd[$rdIdx][19]].'"');			//Invoice no
			cell($rd[$rdIdx][5], ' style="'.$display_css[$rd[$rdIdx][19]].'"');						//Customer	
			cell($rd[$rdIdx][6], ' style="'.$display_css[$rd[$rdIdx][19]].'" align="center"');		//Payment  by
			cell($rd[$rdIdx][7], ' style="'.$display_css[$rd[$rdIdx][19]].'" align="center"');		//Due date
			cell($rd[$rdIdx][8], ' style="'.$display_css[$rd[$rdIdx][19]].'"');						//Bank
			cell($rd[$rdIdx][9], ' style="'.$display_css[$rd[$rdIdx][19]].'" align="right"');		//D/S
			cell(number_format((double)$rd[$rdIdx][10]), ' style="'.$display_css[$rd[$rdIdx][19]].'" align="right"');	//amount
			cell(number_format((double)$rd[$rdIdx][11]), ' style="'.$display_css[$rd[$rdIdx][19]].'" align="right"');	//delivery charge
			cell(number_format((double)$rd[$rdIdx][12]), ' style="'.$display_css[$rd[$rdIdx][19]].'" align="right"');	//vat
			cell(number_format((double)$rd[$rdIdx][13]), ' style="'.$display_css[$rd[$rdIdx][19]].'" align="right"');	//amount+vat
			cell(number_format((double)$rd[$rdIdx][14]), ' style="'.$display_css[$rd[$rdIdx][19]].'" align="right"');	//amount +vat+freight
			cell(number_format((double)$rd[$rdIdx][15]), ' style="'.$display_css[$rd[$rdIdx][19]].'" align="right"');	//paid
			cell(number_format((double)$rd[$rdIdx][16]), ' style="'.$display_css[$rd[$rdIdx][19]].'" align="right"');	// Remain amount
			cell($rd[$rdIdx][17], ' style="'.$display_css[$rd[$rdIdx][19]].'" align="center"');		//Last paid date
			print "</tr>\n";

			//SUB TOTAL
			if($rd[$rdIdx][19] == 'turn_counted') {				//return
				$weekly_summary[0] += $rd[$rdIdx][10] *-1;
				$weekly_summary[1] += $rd[$rdIdx][11] *-1;
				$weekly_summary[2] += $rd[$rdIdx][12] *-1;
				$weekly_summary[3] += $rd[$rdIdx][13] *-1;
				$weekly_summary[4] += $rd[$rdIdx][14] *-1;
				$weekly_summary[5] += $rd[$rdIdx][15] *-1;
				$weekly_summary[6] += $rd[$rdIdx][16] *-1;
			} else {
				$weekly_summary[0] += $rd[$rdIdx][10];
				$weekly_summary[1] += $rd[$rdIdx][11];
				$weekly_summary[2] += $rd[$rdIdx][12];
				$weekly_summary[3] += $rd[$rdIdx][13];
				$weekly_summary[4] += $rd[$rdIdx][14];
				$weekly_summary[5] += $rd[$rdIdx][15];
				$weekly_summary[6] += $rd[$rdIdx][16];
			}
			$rdIdx++;
		}

		print "<tr>\n";
		cell($weekth['string'], ' colspan="7"  align="right" align="right" style="color:darkblue"');
		cell(number_format((double)$weekly_summary[0]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$weekly_summary[1]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$weekly_summary[2]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$weekly_summary[3]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$weekly_summary[4]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$weekly_summary[5]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$weekly_summary[6]), ' align="right" style="color:darkblue"');
		cell("&nbsp", ' align="right" style="color:darkblue"');
		print "</tr>\n";

		//Monthly TOTAL
		$monthly_summary[0] += $weekly_summary[0];
		$monthly_summary[1] += $weekly_summary[1];
		$monthly_summary[2] += $weekly_summary[2];
		$monthly_summary[3] += $weekly_summary[3];
		$monthly_summary[4] += $weekly_summary[4];
		$monthly_summary[5] += $weekly_summary[5];
		$monthly_summary[6] += $weekly_summary[6];
	}
	
	print "<tr>\n";
	cell('<b>'.$month_name.'<b>', ' colspan="7"  align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$monthly_summary[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$monthly_summary[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$monthly_summary[2]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$monthly_summary[3]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$monthly_summary[4]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$monthly_summary[5]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$monthly_summary[6]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br>\n";

	//Monthly TOTAL
	$grand_total[0] += $monthly_summary[0];
	$grand_total[1] += $monthly_summary[1];
	$grand_total[2] += $monthly_summary[2];
	$grand_total[3] += $monthly_summary[3];
	$grand_total[4] += $monthly_summary[4];
	$grand_total[5] += $monthly_summary[5];
	$grand_total[6] += $monthly_summary[6];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_f">
	<tr>
		<th width="7%">INV. DATE</th>
		<th width="10%">INV. NO</th>
		<th>SHIP TO<br />CUSTOMER</th>
		<th width="3%">PAID<br />METHOD</th>
		<th width="7%">DUE DATE</th>
		<th width="7%">BANK</th>
		<th width="3%">D/S</th>
		<th width="6%">AMOUNT</th>
		<th width="6%">FREIGHT<br>(Rp)</th>
		<th width="6%">VAT<br>(Rp)</th>
		<th width="6%">AMOUNT<br>+VAT</th>
		<th width="6%">AMOUNT<br>+FRT/VAT</th>
		<th width="6%">PAID<br>(Rp)</th>
		<th width="6%">BAL<br>(Rp)</th>
		<th width="7%">LAST PAID</th>
	</tr>\n
END;
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="7"  align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[3]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[4]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[5]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[6]), ' align="right" style="color:brown; background-color:lightyellow"');
cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br>\n";
?>