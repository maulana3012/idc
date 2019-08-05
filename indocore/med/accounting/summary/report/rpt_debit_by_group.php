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
	$tmp_bill[]	= "bill_ordered_by = ".$cboFilter[1][ZKP_URL][0][0];
	$tmp_turn[] = "turn_ordered_by = ".$cboFilter[1][ZKP_URL][0][0];
	$tmp_bill[] = ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill_code,'billing')";
	$tmp_turn[] = ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', turn_code,'billing_return')";
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
	$sql_turn   = " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
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

if ($some_date != "") {
	$tmp_bill[] = "bill_payment_giro_due = DATE '$some_date'";
	$tmp_turn[] = "turn_return_date = DATE '$some_date'";
} else {
	$tmp_bill[] = "bill_payment_giro_due  BETWEEN DATE '$period_from' AND DATE '$period_to'";
	$tmp_turn[] = "turn_return_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
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

if($_dept != 'all') {
	$tmp_bill[]	= "bill_dept = '$_dept'";
	$tmp_turn[]	= "turn_dept = '$_dept'";
}

if($_vat == 'vat-IO') {
	$tmp_bill[]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
	$tmp_turn[]	= "turn_code is null";  
} else if($_vat == 'vat-IP') {
	$tmp_bill[]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
	$tmp_turn[]	= "turn_code is null";  
}

$tmp_bill[]	= "bill_vat > 0";
$tmp_turn[]	= "turn_vat > 0 AND turn_return_condition IN (2,3,4)";

$strWhereBilling = implode(" AND ", $tmp_bill);
$strWhereReturn = implode(" AND ", $tmp_turn);

//continue sql
$sql_bill .="
  to_char(b.bill_inv_date,'dd/Mon/YY') AS invoice_issue_date,
  b.bill_code AS invoice_code,
  cus_code AS ship_to,
  cus_full_name AS ship_to_name,
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
  ROUND((b.bill_total_billing - b.bill_delivery_freight_charge) * 100 / (b.bill_vat+100),2) AS amount,
  b.bill_delivery_freight_charge AS delivery_charge,
  ROUND((b.bill_total_billing - b.bill_delivery_freight_charge) * 100 / (b.bill_vat+100) * b.bill_vat/100,2) AS vat,
  b.bill_total_billing - b.bill_delivery_freight_charge AS amount_vat,
  b.bill_total_billing AS grand_total,
  b.bill_total_billing_rev - b.bill_remain_amount AS amount_paid,
  b.bill_remain_amount AS remain_amount,
  to_char(b.bill_last_payment_date,'dd/Mon/YY') AS last_payment_date,

  b.bill_payment_giro_due AS due_payment,
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
  'detail_billing.php?_code='||bill_code AS go_page
FROM ".ZKP_SQL."_tb_customer AS c JOIN ".ZKP_SQL."_tb_billing AS b ON bill_ship_to = cus_code
WHERE $strWhereBilling";

$sql_turn .="
  to_char(t.turn_return_date,'dd/Mon/YY') AS invoice_issue_date,
  t.turn_code AS invoice_code,
  cus_code AS ship_to,
  cus_full_name AS ship_to_name,
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
  t.turn_delivery_freight_charge AS delivery_charge,
  ROUND(((t.turn_total_return - t.turn_delivery_freight_charge) * 100 / (t.turn_vat+100) * t.turn_vat/100),2) AS vat,
  ROUND((t.turn_total_return - t.turn_delivery_freight_charge),2) AS amount_vat,
  (t.turn_total_return) AS grand_total,
  (t.turn_total_return) AS amount_paid,
  NULL AS remain_amount,
  NULL AS last_payment_date,

  t.turn_payment_giro_due AS due_payment,
  CASE
	WHEN t.turn_return_condition = 1 THEN 'return_1' 
	WHEN t.turn_return_condition = 2 THEN 'return_2'
    WHEN t.turn_return_condition = 3 THEN 'return_3'
	WHEN t.turn_return_condition = 4 THEN 'return_4'
  END AS invoice_condition,
  'turn_counted' AS invoice_layout,
  'detail_return.php?_code='||turn_code AS go_page
FROM ".ZKP_SQL."_tb_customer AS c JOIN ".ZKP_SQL."_tb_return AS t ON turn_ship_to = cus_code
WHERE $strWhereReturn";

$sql = "$sql_bill UNION $sql_turn ORDER BY cug_name, due_payment, invoice_code";

// raw data
$rd = array();
$rdIdx = 0;
$cache = array("","");
$group0 = array();
$res = query($sql);
while($col = fetchRowAssoc($res)) {

	$rd[] = array(
		$col['cug_name'],				//0
		$col['invoice_issue_date'], 	//1
		$col['invoice_code'],			//2
		$col['ship_to'],				//3
		$col['ship_to_name'],			//4
		$col['payment_method'],			//5
		$col['invoice_due_date'], 		//6
		$col['bank'], 					//7
		$col['due_remain'], 			//8
		$col['amount'],					//9
		$col['delivery_charge'],		//10
		$col['vat'],					//11
		$col['amount_vat'],				//12
		$col['grand_total'],			//13
		$col['amount_paid'],			//14
		$col['remain_amount'],			//15
		$col['last_payment_date'],		//16
		$col['invoice_condition'],		//17
		$col['invoice_layout'],			//18
		$col['go_page']					//19
	);

	//1st grouping
	if($cache[0] != $col['cug_name']) {
		$cache[0] = $col['cug_name'];
		$group0[$col['cug_name']] = array();
	}

	if($cache[1] != $col['invoice_code']) {
		$cache[1] = $col['invoice_code'];
	}

	$group0[$col['cug_name']][$col['invoice_code']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$g_total = array(0,0,0,0,0,0,0);

//GROUP TOTAL
$grand_total = array(0,0,0,0,0,0,0);

//GROUP
foreach ($group0 as $group_name => $pusat) {
	echo "<span class=\"comment\"><b> GROUP: ". $group_name. "</b></span>\n";
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
	$group_total = array(0,0,0,0,0,0,0);
	foreach ($pusat as $billing) {
		print "<tr>\n";
		cell($rd[$rdIdx][1], ' style="'.$display_css[$rd[$rdIdx][18]].'" align="center"');	//Invoice date
		cell_link($rd[$rdIdx][2], ' style="'.$display_css[$rd[$rdIdx][18]].'" align="center"',
			' href="'.$rd[$rdIdx][19].'" style="'.$display_css[$rd[$rdIdx][18]].'"');													//Invoice no
		cell($rd[$rdIdx][4], ' style="'.$display_css[$rd[$rdIdx][18]].'"');					//Customer
		cell($rd[$rdIdx][5], ' style="'.$display_css[$rd[$rdIdx][18]].'" align="center"');	//Payment by
		cell($rd[$rdIdx][6], ' style="'.$display_css[$rd[$rdIdx][18]].'" align="center"');	//Due date
		cell($rd[$rdIdx][7], ' style="'.$display_css[$rd[$rdIdx][18]].'"');					//Bank
		cell($rd[$rdIdx][8], ' style="'.$display_css[$rd[$rdIdx][18]].'" align="right"');	//D/S
		cell(number_format($rd[$rdIdx][9]), ' style="'.$display_css[$rd[$rdIdx][18]].'" align="right"');	//Amount
		cell(number_format($rd[$rdIdx][10]), ' style="'.$display_css[$rd[$rdIdx][18]].'" align="right"');	//Delivery charge
		cell(number_format($rd[$rdIdx][11]), ' style="'.$display_css[$rd[$rdIdx][18]].'" align="right"');	//Vat
		cell(number_format($rd[$rdIdx][12]), ' style="'.$display_css[$rd[$rdIdx][18]].'" align="right"');	//Amount + Vat
		cell(number_format($rd[$rdIdx][13]), ' style="'.$display_css[$rd[$rdIdx][18]].'" align="right"');	//Grand total
		cell(number_format($rd[$rdIdx][14]), ' style="'.$display_css[$rd[$rdIdx][18]].'" align="right"');	//Amount paid
		cell(number_format($rd[$rdIdx][15]), ' style="'.$display_css[$rd[$rdIdx][18]].'" align="right"');	// Remain amount
		cell($rd[$rdIdx][16], ' style="'.$display_css[$rd[$rdIdx][18]].'" align="center"');		//Last paid
		print "</tr>\n";
		
		//SUB TOTAL
		if($rd[$rdIdx][18] == 'turn_counted') {				//return
			$group_total[0] += $rd[$rdIdx][9] *-1;
			$group_total[1] += $rd[$rdIdx][10] *-1;
			$group_total[2] += $rd[$rdIdx][11] *-1;
			$group_total[3] += $rd[$rdIdx][12] *-1;
			$group_total[4] += $rd[$rdIdx][13] *-1;
			$group_total[5] += $rd[$rdIdx][14] *-1;
			$group_total[6] += $rd[$rdIdx][15] *-1;
		} else {
			$group_total[0] += $rd[$rdIdx][9];
			$group_total[1] += $rd[$rdIdx][10];
			$group_total[2] += $rd[$rdIdx][11];
			$group_total[3] += $rd[$rdIdx][12];
			$group_total[4] += $rd[$rdIdx][13];
			$group_total[5] += $rd[$rdIdx][14];
			$group_total[6] += $rd[$rdIdx][15];
		}
		$rdIdx++;
	}

	print "<tr>\n";
	cell("GROUP TOTAL", ' colspan="7"  align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($group_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($group_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($group_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($group_total[3]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($group_total[4]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($group_total[5]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($group_total[6]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	//GRAND TOTAL
	$grand_total[0] += $group_total[0];
	$grand_total[1] += $group_total[1];
	$grand_total[2] += $group_total[2];
	$grand_total[3] += $group_total[3];
	$grand_total[4] += $group_total[4];
	$grand_total[5] += $group_total[5];
	$grand_total[6] += $group_total[6];
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
cell(number_format($grand_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[3]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[4]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[5]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[6]), ' align="right" style="color:brown; background-color:lightyellow"');
cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br />\n";
?>