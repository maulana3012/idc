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
$dept['A']	= 'Apotik Team Sales Data by Customer';
$dept['D']	= 'Dealer Team Sales Data by Customer';
$dept['H']	= 'Hospital Team Sales Data by Customer';
$dept['M']	= 'Marketing Team Sales Data by Customer';
$dept['P']	= 'Pharmaceutical Team Sales Data by Customer';
$dept['T']	= 'Tender Team Sales Data by Customer';

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
  bill_dept AS dept,
  bill_responsible_by AS ma_idx,
  CASE 
	WHEN bill_responsible_by=1000 THEN 'PUSAT'
	ELSE (SELECT ma_account FROM ".ZKP_SQL."_tb_mbracc WHERE ma_idx=b.bill_responsible_by) 
  END AS ma_account,
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
  turn_dept AS dept,
  turn_responsible_by AS ma_idx,
  CASE 
	WHEN turn_responsible_by=1000 THEN 'PUSAT'
	ELSE (SELECT ma_account FROM ".ZKP_SQL."_tb_mbracc WHERE ma_idx=b.turn_responsible_by) 
  END AS ma_account,
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

$sql = "$sql_bill UNION $sql_return ORDER BY dept, ma_idx, date, invoice_code";
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
		$col['dept'], 					//0
		$col['ma_idx'],					//1
		$col['ma_account'],				//2
		$col['invoice_date'], 			//3
		$col['invoice_code'],			//4
		$col['cus_code'],				//5
		$col['cus_full_name'],			//6
		$col['amount'],					//7
		$col['vat'], 					//8
		$col['amount_vat'], 			//9
		$col['remain_amount'], 			//10
		$col['invoice_layout'],			//11
		$col['go_page'],				//12
	);

	if($cache[0] != $col['dept']) {
		$cache[0] = $col['dept'];
		$group0[$col['dept']] = array();
	}

	if($cache[1] != $col['ma_account']) {
		$cache[1] = $col['ma_account'];
		$group0[$col['dept']][$col['ma_account']] = array();
	}
	
	if($cache[2] != $col['invoice_code']) {
		$cache[2] = $col['invoice_code'];
	}

	$group0[$col['dept']][$col['ma_account']][$col['invoice_code']] = 1;
}

//grand summary
$grand_total = array (0,0,0,0,0,0,0);

//GROUP BY MONTH
foreach ($group0 as $dept_name => $marketing_name) {
	echo "<span class=\"comment\"><b>". $dept[$dept_name] . "</b></span>\n";
	print <<<END
	<table width="80%" class="table_f">
		<tr>
			<th width="10%">INV. DATE</th>
			<th width="22%">INV. NO</th>
			<th>CUSTOMER</th>
			<th width="8%">AMOUNT<br>(Rp)</th>
			<th width="8%">VAT<br>(Rp)</th>
			<th width="8%">AMOUNT<br>+VAT</th>
			<th width="8%">REMAIN<br>(Rp)</th>
		</tr>\n
END;

	//monthly summary
	$dept_summary = array (0,0,0,0,0,0,0);

	foreach ($marketing_name as $inv_name => $ma_idx) {
		
		$marketing = ($rd[$rdIdx][2]=='') ? 'Undefined Marketing' : strtoupper($rd[$rdIdx][2]);
		print "<tr height=\"20px\">\n";
		if($marketing != "")	print "<td colspan=\"7\"><b>" . $marketing . "</b></td>\n";
		else					print "<td colspan=\"7\"><i></i></td>\n";
		print "</tr>\n";

		//weekly summary
		$marketing_summary = array(0,0,0,0);
		$print_tr_1 = 0;
		foreach ($ma_idx as $billing) {
			if($print_tr_1++ > 0) print "<tr>\n";
			cell($rd[$rdIdx][3], ' style="'.$display_css[$rd[$rdIdx][11]].'" align="center" valign="top"');					// Invoice date
			cell_link($rd[$rdIdx][4], ' style="'.$display_css[$rd[$rdIdx][11]].'" align="center" valign="top"',				// Invoice code
				' href="'.$rd[$rdIdx][12].'" target="_parent"');
			cell($rd[$rdIdx][6], ' style="'.$display_css[$rd[$rdIdx][11]].'" valign="top"');								// Customer
			cell(number_format($rd[$rdIdx][7]), ' style="'.$display_css[$rd[$rdIdx][11]].'" align="right", valign="top"'); 	// amount
			cell(number_format($rd[$rdIdx][8]), ' style="'.$display_css[$rd[$rdIdx][11]].'" align="right", valign="top"');	// vat
			cell(number_format($rd[$rdIdx][9]), ' style="'.$display_css[$rd[$rdIdx][11]].'" align="right", valign="top"');	// amount+vat
			cell(number_format($rd[$rdIdx][10]), ' style="'.$display_css[$rd[$rdIdx][11]].'" align="right", valign="top"');	// remain
			print "</tr>\n";

			//SUB TOTAL
			if($rd[$rdIdx][11] != "turn_uncounted") {
				$marketing_summary[0] += $rd[$rdIdx][7]; 	//Amount
				$marketing_summary[1] += $rd[$rdIdx][8];	//Vat
				$marketing_summary[2] += $rd[$rdIdx][9];	//Grand total
				$marketing_summary[3] += $rd[$rdIdx][10];	//Remain amount
			}
			$rdIdx++;
		}

		print "</tr>\n";
		cell($marketing, ' colspan="3"  align="right" align="right" style="color:brown"');
		cell(number_format($marketing_summary[0]), ' align="right" style="color:brown"');
		cell(number_format($marketing_summary[1]), ' align="right" style="color:brown"');
		cell(number_format($marketing_summary[2]), ' align="right" style="color:brown"');
		cell(number_format($marketing_summary[3]), ' align="right" style="color:brown"');
		print "</tr>\n";

		//Monthly TOTAL
		$dept_summary[0] += $marketing_summary[0];
		$dept_summary[1] += $marketing_summary[1];
		$dept_summary[2] += $marketing_summary[2];
		$dept_summary[3] += $marketing_summary[3];
	}
	
	print "<tr>\n";
	cell('<b>'.$dept[$dept_name].'<b>', ' colspan="3"  align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($dept_summary[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($dept_summary[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($dept_summary[2]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($dept_summary[3]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br>\n";

	//Monthly TOTAL
	$grand_total[0] += $dept_summary[0];
	$grand_total[1] += $dept_summary[1];
	$grand_total[2] += $dept_summary[2];
	$grand_total[3] += $dept_summary[3];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="80%" class="table_f">
	<tr>
		<th width="10%">INV. DATE</th>
		<th width="22%">INV. NO</th>
		<th>CUSTOMER</th>
		<th width="8%">AMOUNT<br>(Rp)</th>
		<th width="8%">VAT<br>(Rp)</th>
		<th width="8%">AMOUNT<br>+VAT</th>
		<th width="8%">REMAIN<br>(Rp)</th>
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