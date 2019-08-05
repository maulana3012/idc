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
$display_css['billing'] 	= "color:#333333";
$display_css['return'] 		= "color:#EE5811";
$display_css['uncounted'] 	= "color:#9D9DA1";

//SET WHERE PARAMETER
$tmp1 = array();
$tmp2 = array();

if(ZKP_FUNCTION == 'ALL') {
	if($_order_by != 'all'){
		$tmp1[]	= "bill_ordered_by = $_order_by";
		$tmp2[]	= "bill_ordered_by = $_order_by";
	}
} else {
	$tmp1[]	= "bill_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill_code,'billing')";
	$tmp2[]	= "bill_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill_code,'billing')";
}

if($_filter_doc == 'I') {
	$tmp1[] = "pay_paid > 0 AND pay_note NOT IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
	$tmp2[] = "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
} else if($_filter_doc == 'R') {
	$tmp1[] = "pay_paid < 0 AND pay_note NOT IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
	$tmp2[] = "pay_idx IS NULL";
} else if($_filter_doc == 'CT') {
	$tmp1[] = "pay_idx IS NULL";
	$tmp2[] = "pay_note IN ('CROSS_TRANSFER+')";
} else {
	$tmp1[] = "pay_note NOT IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
	$tmp2[] = "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
}

if ($some_date != "") {
	$tmp1[] = "p.pay_date = DATE '$some_date'";
	$tmp2[] = "p.pay_date = DATE '$some_date'";
} else {
	$tmp1[] = "p.pay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
	$tmp2[] = "p.pay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
}

if($_vat == 'vat') {
	$tmp1[] = "bill_vat > 0";
	$tmp2[] = "bill_vat > 0";
} else if($_vat == 'vat-IO') {
	$tmp1[] = "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
	$tmp2[] = "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
}else if($_vat == 'vat-IP') {
	$tmp1[] = "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
	$tmp2[] = "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
} else if ($_vat == 'non') {
	$tmp1[] = "bill_vat = 0";
	$tmp2[] = "bill_vat = 0";
}

if($_vat == 'vat-IO') {
	$tmp1[]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
	$tmp2[]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
	if($_filter_doc == 'R') {
		$tmp1[]	= "bill_code is null";
		$tmp2[]	= "bill_code is null";
	}
} else if($_vat == 'vat-IP') {
	$tmp1[]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
	$tmp2[]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
	if($_filter_doc == 'R') {
		$tmp1[]	= "bill_code is null";
		$tmp2[]	= "bill_code is null";
	}
}

if($cboSearchType != '' && $txtSearch != '') {
	$searchType = array("byPayment"=>1, "byDeduction"=>2);
	$tmp1[] = ZKP_SQL."_isPayDescTrue({$searchType[$cboSearchType]}, pay_idx, '%$txtSearch%') = true";
	$tmp2[] = ZKP_SQL."_isPayDescTrue({$searchType[$cboSearchType]}, pay_idx, '%$txtSearch%') = true";
}

$tmp1[] = "bill_ship_to = '$_cus_code'";
$tmp2[] = "bill_ship_to = '$_cus_code'";

$strWhere1 = implode(" AND ", $tmp1);
$strWhere2 = implode(" AND ", $tmp2);

$sql = "
SELECT
  c.cus_code AS ship_to,
  c.cus_full_name AS ship_to_name,
  b.bill_code AS invoice_code,
  to_char(b.bill_inv_date, 'dd/Mon/YY') AS invoice_date,
  to_char(b.bill_payment_giro_due, 'dd/Mon/YY') AS invoice_due_date,
  ".ZKP_SQL."_getDueRemain(b.bill_code) AS invoice_due_remain,
  (b.bill_total_billing - b.bill_delivery_freight_charge) * 100 / (b.bill_vat+100) AS amount,
  b.bill_delivery_freight_charge AS delivery_charge,
  (b.bill_total_billing - b.bill_delivery_freight_charge) * 100 / (b.bill_vat+100) * b.bill_vat/100 AS vat,
  b.bill_total_billing AS grand_total,
  p.pay_idx AS pay_idx,
  to_char(p.pay_date, 'dd/Mon/YY') AS payment_date,
  CASE
 	WHEN p.pay_method = 'cash' THEN 'CASH'
	WHEN p.pay_method = 'check' THEN 'CHECK'
	WHEN p.pay_method = 'transfer' THEN 'T/S'
	WHEN p.pay_method = 'giro' THEN 'GIRO'
	ELSE '-'
  END AS payment_method,
  CASE 
	WHEN p.pay_bank = 'BNIS' THEN 'BNI SYR'
	ELSE p.pay_bank
  END AS payment_bank,
  p.pay_paid AS payment_amount,
  b.bill_remain_amount AS invoice_remain_amount,
  p.pay_remark AS payment_remark,

  p.pay_date AS pay_date,
  p.pay_note AS payment_note,
  CASE
	WHEN p.pay_paid <= 0 then 'return'
  	WHEN p.pay_paid > 0 AND p.pay_note = 'DEPOSIT-B' THEN 'uncounted'
  	ELSE 'billing'
  END AS layout,
  'detail_billing.php?_code='||bill_code AS go_page
FROM
  ".ZKP_SQL."_tb_customer AS c
  JOIN ".ZKP_SQL."_tb_billing AS b ON cus_code = bill_ship_to
  JOIN ".ZKP_SQL."_tb_payment AS p USING(bill_code)
WHERE $strWhere1
	UNION
SELECT
  c.cus_code AS ship_to,
  c.cus_full_name AS ship_to_name,
  b.bill_code AS invoice_code,
  to_char(b.bill_inv_date, 'dd/Mon/YY') AS invoice_date,
  to_char(b.bill_payment_giro_due, 'dd/Mon/YY') AS invoice_due_date,
  ".ZKP_SQL."_getDueRemain(b.bill_code) AS invoice_due_remain,
  (b.bill_total_billing - b.bill_delivery_freight_charge) * 100 / (b.bill_vat+100) AS amount,
  b.bill_delivery_freight_charge AS delivery_charge,
  (b.bill_total_billing - b.bill_delivery_freight_charge) * 100 / (b.bill_vat+100) * b.bill_vat/100 AS vat,
  b.bill_total_billing AS grand_total,
  p.pay_idx AS pay_idx,
  to_char(p.pay_date, 'dd/Mon/YY') AS payment_date,
  CASE
 	WHEN p.pay_method = 'cash' THEN 'CASH'
	WHEN p.pay_method = 'check' THEN 'CHECK'
	WHEN p.pay_method = 'transfer' THEN 'T/S'
	WHEN p.pay_method = 'giro' THEN 'GIRO'
	ELSE '-'
  END AS payment_method,
  CASE 
	WHEN p.pay_bank = 'BNIS' THEN 'BNI SYR'
	ELSE p.pay_bank
  END AS payment_bank,
  p.pay_paid AS payment_amount,
  b.bill_remain_amount AS invoice_remain_amount,
  p.pay_remark AS payment_remark,

  p.pay_date AS pay_date,
  p.pay_note AS payment_note,
  CASE
	WHEN p.pay_paid <= 0 then 'return'
  	WHEN p.pay_paid > 0 AND p.pay_note = 'DEPOSIT-B' THEN 'uncounted'
  	ELSE 'billing'
  END AS layout,
  'detail_billing.php?_code='||bill_code AS go_page
FROM
  ".ZKP_SQL."_tb_customer AS c
  JOIN ".ZKP_SQL."_tb_billing AS b ON cus_code = bill_ship_to
  JOIN ".ZKP_SQL."_tb_payment AS p USING(bill_code)
WHERE $strWhere2
ORDER BY ship_to, invoice_code, pay_date";

// raw data
$rd		= array();
$rdIdx	= 0;
$cache	= array("","","", "");
$group0 = array();
$res = query($sql);
while($col = fetchRowAssoc($res)) {

	$rd[] = array(
		$col['ship_to'],				//0
		$col['ship_to_name'],			//1
		$col['invoice_date'], 			//2
		$col['invoice_code'],			//3
		$col['invoice_due_date'], 		//4
		$col['invoice_due_remain'],		//5
		$col['amount'], 				//6
		$col['delivery_charge'],		//7
		$col['vat'], 					//8
		$col['grand_total'],			//9
		$col['pay_idx'], 				//10
		$col['payment_date'],			//11
		$col['payment_method'], 		//12
		$col['payment_bank'],			//13
		$col['payment_amount'],			//14
		$col['invoice_remain_amount'],	//15
		$col['payment_remark'],			//16
		$col['payment_note'],			//17
		$col['layout'],					//18
		$col['go_page']					//19
	);

	//1st grouping
	if($cache[0] != $col['ship_to']) {
		$cache[0] = $col['ship_to'];
		$group0[$col['ship_to']] = array();
	}

	if($cache[1] != $col['invoice_code']) {
		$cache[1] = $col['invoice_code'];
		$group0[$col['ship_to']][$col['invoice_code']] = array();
	}

	if($cache[2] != $col['pay_idx']) {
		$cache[2] = $col['pay_idx'];
	}

	$group0[$col['ship_to']][$col['invoice_code']][$col['pay_idx']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//grand summary
$grand_total = array (0,0,0,0,0,0,0);

//CUSTOMER
foreach ($group0 as $total1 => $group1) {
	echo "<span class=\"comment\"><b>CUSTOMER : ". $total1. "</b></span>\n";
	print <<<END
	<table width="1000px" class="table_f">
		<tr>
			<th width="6%">INV. DATE</th>
			<th width="8%">INV. NO</th>
			<th width="6%">DUE DATE</th>
			<th width="3%">D/S</th>
			<th width="5%">AMOUNT</th>
			<th width="5%">FREIGHT<br>(Rp)</th>
			<th width="5%">VAT<br>(Rp)</th>
			<th width="5%">AMOUNT<br>+FRT/VAT</th>
			<th width="5%">PAID<br> DATE</th>
			<th width="3%">PAID<br />METHOD</th>
			<th width="5%">BANK</th>
			<th width="5%">PAID<br>(Rp)</th>
			<th width="5%">BALANCE</th>
			<th width="5%">PAID<br> REMARK</th>
			<th width="15%">DEDUCTION</th>
		</tr>\n
END;

	//monthly summary
	$customer_summary = array (0,0,0,0,0,0,0);
	$print_tr_1 = 0;

	foreach ($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');

		if($print_tr_1++ > 0) print "<tr>\n";
		cell($rd[$rdIdx][2], ' align="center" valign="top" rowspan="'.$rowSpan.'"');		//Invoice date
		cell_link($rd[$rdIdx][3], ' align="center", valign="top" rowspan="'.$rowSpan.'"',	//Invoice no
			' href="'.$rd[$rdIdx][19].'" target="_parent"');
		cell($rd[$rdIdx][4], ' align="center", valign="top" rowspan="'.$rowSpan.'"');		//Due date
		cell($rd[$rdIdx][5], ' align="right", valign="top" rowspan="'.$rowSpan.'"');		//D/S
		cell(number_format($rd[$rdIdx][6]), ' align="right", valign="top" rowspan="'.$rowSpan.'"');  //amount
		cell(number_format($rd[$rdIdx][7]), ' align="right", valign="top" rowspan="'.$rowSpan.'"'); // freight
		cell(number_format($rd[$rdIdx][8]), ' align="right", valign="top" rowspan="'.$rowSpan.'"'); //vat
		cell(number_format($rd[$rdIdx][9]), ' align="right", valign="top" rowspan="'.$rowSpan.'"'); //amount +vat+freight

		//0.Amount
		//1. Delivery_freight
		//2. amount_vat_freight
		//3. total_Amount
		//4. Payment paid
		//5. Remain_Billing
		//6. Payment remark
		//7. Payment note
		//8. Invoice code
		$invoice	= array($rd[$rdIdx][6],$rd[$rdIdx][7],$rd[$rdIdx][8],$rd[$rdIdx][9],0,$rd[$rdIdx][15],$rd[$rdIdx][16],$rd[$rdIdx][17],$rd[$rdIdx][3],0);
		$print_tr_2 = 0;
		foreach ($group2 as $total3) {
			$rowSpan = 0;
			array_walk_recursive($group2, 'getRowSpan');

			if($print_tr_2++ > 0) print "<tr>\n";
			cell($rd[$rdIdx][11], ' style="'.$display_css[$rd[$rdIdx][18]].'" align="right"');	//Payment date
			cell($rd[$rdIdx][12], ' style="'.$display_css[$rd[$rdIdx][18]].'" align="center"');	//Payment method
			cell($rd[$rdIdx][13], ' style="'.$display_css[$rd[$rdIdx][18]].'" align="center"');	//Payment paid
			cell(number_format($rd[$rdIdx][14]), ' style="'.$display_css[$rd[$rdIdx][18]].'" align="right"'); 	//Payment paid
			cell("&nbsp;", ' style="'.$display_css[$rd[$rdIdx][18]].'"');						//Invoice remain
			cell($rd[$rdIdx][16], ' style="'.$display_css[$rd[$rdIdx][18]].'"');				//Payment remark

			$deduction_sql = "SELECT pade_description, pade_amount FROM ".ZKP_SQL."_tb_payment_deduction WHERE pay_idx={$rd[$rdIdx][10]}";
			if ($txtSearch != '') { $deduction_sql .= "AND pade_description ILIKE '%$txtSearch%'"; }
			$deduction_res = query($deduction_sql);
			$num_deduction = numQueryRows($deduction_res);

			if($num_deduction == 0) { cell(""); }
			else {
				echo "<td>\n";
				echo "<table width=\"100%\" class=\"table_l\">\n";
				while($col = fetchRow($deduction_res)) {
					echo "<tr>\n";
					echo "<td>{$col[0]}</td>";
					echo "<td align=\"right\">". number_format($col[1]) ."</td>";
					echo "</tr>\n";
					$invoice[9] += $col[1];
				}
				echo "</table>";
				echo "</td>\n";
			}

			print "</tr>\n";

			if ($rd[$rdIdx][18] != 'uncounted') {
					$invoice[4] 	+= $rd[$rdIdx][14];
			}
			$rdIdx++;
		}

		print "<tr>\n";
		cell('INVOICE <b>'.$invoice[8].'</b>', ' colspan="4"  align="right" align="right" style="color:blue"');
		cell(number_format($invoice[0]), ' align="right" style="color:blue"');
		cell(number_format($invoice[1]), ' align="right" style="color:blue"');
		cell(number_format($invoice[2]), ' align="right" style="color:blue"');
		cell(number_format($invoice[3]), ' align="right" style="color:blue"');
		cell("&nbsp");
		cell("&nbsp");
		cell("&nbsp");
		cell(number_format($invoice[4]), ' align="right" style="color:blue"');
		cell(number_format($invoice[5]), ' align="right" style="color:blue"');
		cell("&nbsp");
		cell(number_format($invoice[9]), ' align="right" style="color:blue"');
		print "</tr>\n";

		//SUB TOTAL
		$customer_summary[0] += $invoice[0];	//Amount
		$customer_summary[1] += $invoice[1];	//Delivery charge
		$customer_summary[2] += $invoice[2];	//Vat
		$customer_summary[3] += $invoice[3];	//Grand total
		$customer_summary[4] += $invoice[4];	// Payment amount
		$customer_summary[5] += $invoice[5];	//Invoice remain
		$customer_summary[6] += $invoice[9];
	}
	
	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="4"  align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($customer_summary[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($customer_summary[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($customer_summary[2]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($customer_summary[3]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
	cell(number_format($customer_summary[4]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($customer_summary[5]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
	cell(number_format($customer_summary[6]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br>\n";

	//Monthly TOTAL
	$grand_total[0] += $customer_summary[0];
	$grand_total[1] += $customer_summary[1];
	$grand_total[2] += $customer_summary[2];
	$grand_total[3] += $customer_summary[3];
	$grand_total[4] += $customer_summary[4];
	$grand_total[5] += $customer_summary[5];
	$grand_total[6] += $customer_summary[6];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="1000px" class="table_f">
	<tr>
		<th width="6%">INV. DATE</th>
		<th width="8%">INV. NO</th>
		<th width="6%">DUE DATE</th>
		<th width="3%">D/S</th>
		<th width="5%">AMOUNT</th>
		<th width="5%">FREIGHT<br>(Rp)</th>
		<th width="5%">VAT<br>(Rp)</th>
		<th width="5%">AMOUNT<br>+FRT/VAT</th>
		<th width="5%">PAID<br> DATE</th>
		<th width="3%">PAID<br />METHOD</th>
		<th width="5%">BANK</th>
		<th width="5%">PAID<br>(Rp)</th>
		<th width="5%">BALANCE</th>
		<th width="5%">PAID<br> REMARK</th>
		<th width="15%">DEDUCTION</th>
	</tr>\n
END;
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="4"  align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[3]), ' align="right" style="color:brown; background-color:lightyellow"');
cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[4]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[5]), ' align="right" style="color:brown; background-color:lightyellow"');
cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[6]), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br>\n";
?>