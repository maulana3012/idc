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
$channel["000"] = "Medical Dealer";
$channel["001"] = "Medicine Dist";
$channel["002"] = "Pharmacy Chain";
$channel["003"] = "Gen/ Specialty";
$channel["004"] = "Pharmaceutical";
$channel["005"] = "Hospital";
$channel["6.1"] = "M/L Marketing";
$channel["6.2"] = "Mail Order";
$channel["6.3"] = "Internet Business";
$channel["007"] = "Promotion & Other";
$channel["008"] = "Individual";
$channel["009"] = "Private use";
$channel["00S"] = "Service";

$tmp = array(); $strWhere = array(); $sql = array();

if(ZKP_URL == 'ALL') {
	if($_order_by != 'all'){
		$tmp['bill'][] = "bill_ordered_by = $_order_by";
		$tmp['turn'][] = "turn_ordered_by = $_order_by"; 
		$tmp['pay'][] = "bill_ordered_by = $_order_by"; 
	}
} else {
	$tmp['bill'][] = "bill_ordered_by = ".$cboFilter[1][ZKP_URL][0][0]; 
	$tmp['turn'][] = "turn_ordered_by = ".$cboFilter[1][ZKP_URL][0][0]; 
	$tmp['pay'][] = "bill_ordered_by = ".$cboFilter[1][ZKP_URL][0][0]; 
}

if ($_cus_code != '') {
	$tmp['bill'][] = "b.bill_cus_to = '$_cus_code'"; 
	$tmp['turn'][] = "b.turn_cus_to = '$_cus_code'"; 
	$tmp['pay'][] = "b.bill_cus_to = '$_cus_code'"; 
}

if($_vat == 'vat') {
	$tmp['bill'][] = "bill_vat > 0";
	$tmp['turn'][] = "turn_vat > 0";
	$tmp['pay'][] = "bill_vat > 0";
} else if($_vat == 'vat-IO') {
	$tmp['bill'][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
	$tmp['turn'][] = "turn_vat > 0";  
	$tmp['pay'][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
}else if($_vat == 'vat-IP') {
	$tmp['bill'][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
	$tmp['turn'][] = "turn_code = ''";  
	$tmp['pay'][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
} else if ($_vat == 'non') {
	$tmp['bill'][] = "bill_vat = 0";
	$tmp['turn'][] = "turn_vat = 0";
	$tmp['pay'][] = "bill_vat = 0";
}

if($_dept != 'all') {
	$tmp['bill'][]	= "bill_dept = '$_dept'";
	$tmp['turn'][]	= "turn_dept = '$_dept'";
	$tmp['pay'][]	= "pay_dept = '$_dept'";
}

if($currentDept == 'accounting') {
	$tmp['bill'][] = "bill_vat > 0";
	$tmp['turn'][] = "turn_vat > 0";
	$tmp['pay'][]  = "bill_vat > 0";
}
/*
$tmp['bill'][] = "bill_code = 'BO-00024T-A13'";
$tmp['turn'][] = "turn_bill_code = 'BO-00024T-A13'";
$tmp['pay'][]  = "bill_code = 'BO-00024T-A13'";
*/
$tmp['bill'][] = "bill_inv_date <= DATE '$period_to'";
$tmp['turn'][] = "turn_return_date <= DATE '$period_to'";
$tmp['pay'][]  = "pay_date <= DATE '$period_to' AND pay_paid > 0";
#$tmp['pay'][]  = "pay_date <= DATE '$period_to' AND pay_paid-pay_paid_charge > 0";

$strWhere['bill'] = implode(" AND ", $tmp['bill']);
$strWhere['turn'] = implode(" AND ", $tmp['turn']);
$strWhere['pay'] = implode(" AND ", $tmp['pay']);

$sql = "
  SELECT 
	'bill_item' AS condition,
	cus_channel AS channel, 
	'['|| trim(c.cus_code) || '] ' || cus_full_name AS bill_to, 
	bill_code AS code, bill_inv_date AS date, bill_code AS reference, 
	null as return_conditon,
	null AS is_counted,
	--trunc(SUM(biit_qty * biit_unit_price)*((100+bill_vat)/100))+bill_delivery_freight_charge AS amount
	SUM(biit_qty * biit_unit_price) + round( bill_vat/100* trunc(SUM(biit_qty * biit_unit_price))) + bill_delivery_freight_charge AS amount
  FROM
	".ZKP_SQL."_tb_customer AS c JOIN ".ZKP_SQL."_tb_billing AS b ON b.bill_cus_to = cus_code JOIN ".ZKP_SQL."_tb_billing_item USING (bill_code)
  WHERE ".$strWhere['bill']."
  GROUP BY condition, channel, bill_to, code,  date, reference, bill_vat, bill_delivery_freight_charge
UNION
  SELECT 
	'bill_discount' AS condition, 
	cus_channel AS channel, 
	'['|| trim(c.cus_code) || '] ' || cus_full_name AS bill_to, 
	bill_code AS code, bill_inv_date AS date, bill_code AS reference,
	null as return_conditon,
	null AS is_counted,
	trunc(SUM(biit_qty * biit_unit_price)*((100+bill_vat)/100) * bill_discount/100) AS amount
  FROM
	".ZKP_SQL."_tb_customer AS c JOIN ".ZKP_SQL."_tb_billing AS b ON b.bill_cus_to = cus_code JOIN ".ZKP_SQL."_tb_billing_item USING (bill_code)
  WHERE ".$strWhere['bill']." AND bill_discount > 0
  GROUP BY condition, channel, bill_to, code,  date, reference, bill_vat, bill_discount
UNION
  SELECT 
	'bill_freight' AS condition, 
	cus_channel AS channel, 
	'['|| trim(c.cus_code) || '] ' || cus_full_name AS bill_to, 
	bill_code AS code, bill_inv_date AS date, bill_code AS reference,
	null as return_conditon,
	null AS is_counted,
	bill_delivery_freight_charge AS amount
  FROM
	".ZKP_SQL."_tb_customer AS c JOIN ".ZKP_SQL."_tb_billing AS b ON b.bill_cus_to = cus_code
  WHERE ".$strWhere['bill']." AND bill_delivery_freight_charge > 0
UNION
  SELECT 
	'return' AS condition, 
	cus_channel AS channel, 
	'['|| trim(c.cus_code) || '] ' || cus_full_name AS bill_to, 
	turn_code AS code, turn_return_date AS date, turn_bill_code AS reference,
	turn_return_condition::text as return_conditon,
	CASE 
		WHEN turn_return_condition = 3 THEN 'false'
		ELSE 'true'
	END AS is_counted,
	trunc(SUM(reit_qty * reit_unit_price)*((100+turn_vat)/100)) AS amount
  FROM
	".ZKP_SQL."_tb_customer AS c JOIN ".ZKP_SQL."_tb_return AS b ON b.turn_cus_to = cus_code JOIN ".ZKP_SQL."_tb_return_item USING (turn_code)
  WHERE ".$strWhere['turn']."
  GROUP BY condition, channel, bill_to, code, return_conditon, date, reference, turn_vat, turn_return_condition
UNION
  SELECT 
	'payment' AS condition, 
	cus_channel AS channel, 
	'['|| trim(c.cus_code) || '] ' || cus_full_name AS bill_to, 
	pay_idx::text AS code, pay_date::date AS date, bill_code AS reference,
	null as return_conditon,
	null AS is_counted,
	pay_paid AS amount
	--pay_paid-pay_paid_charge AS amount
  FROM
	".ZKP_SQL."_tb_customer AS c JOIN ".ZKP_SQL."_tb_billing AS b ON b.bill_cus_to = cus_code JOIN ".ZKP_SQL."_tb_payment USING (bill_code)
  WHERE ".$strWhere['pay']."
ORDER BY channel, bill_to, reference, date, condition, code
";

$rd		= array();
$rdIdx	= 0;
$cache	= array("","","","");
$group0	= array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['reference'],		//0
		$col['code'],			//1
		$col['channel'],		//2
		$col['bill_to'], 		//3
		$col['date'], 			//4
		$col['condition'],		//5
		$col['amount'],			//6
		$col['return_conditon']	//7		
	);

	if($cache[0] != $col['channel']) {
		$cache[0] = $col['channel'];
		$group0[$col['channel']] = array();
	}

	if($cache[1] != $col['bill_to']) {
		$cache[1] = $col['bill_to'];
		$group0[$col['channel']][$col['bill_to']] = array();
	}
	
	if($cache[2] != $col['reference']) {
		$cache[2] = $col['reference'];
		$group0[$col['channel']][$col['bill_to']][$col['reference']] = array();
	}

	if($cache[3] != $col['condition'].$col['code']) {
		$cache[3] = $col['condition'].$col['code'];
	}

	$group0[$col['channel']][$col['bill_to']][$col['reference']][$col['condition'].$col['code']] = 1;

}

echo "<pre>";
//var_dump($sql);
echo "</pre>";

$cust = array(); 
$cust_channel = array(); $cust_cus = array();
/*
Key Index Customer Bill
0. Saldo Awal
1. Current Period - Tagihan
2. Current Period - Bayar
3. Current Period - Return
4. Current Period - Pot. Tagihan
5. Current Period - Saldo Akhir
6. Bill Item
7. Bill Disc
8. Bill Freight
9. Return
10. Payment
11. Accumulated Bill Remain
12. Bill_Item - Disc - Return type 2
13. Uncounted Return
*/
foreach ($group0 as $total1 => $group1) {
	foreach($group1 as $total2 => $group2) {
		foreach($group2 as $total3 => $group3) {
			foreach($group3 as $total4) {

	$tot = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0); // temp value while looping
	switch ($rd[$rdIdx][5])	{ // Condition
	  case "bill_item": 
		if(strtotime($rd[$rdIdx][4]) < strtotime($period_from)) {
			$tot[0] += (int) $rd[$rdIdx][6];
		} else {
			$tot[1] += (int) $rd[$rdIdx][6];
		}		
		$tot[6] += (int) $rd[$rdIdx][6];
		$tot[12] += (int) $rd[$rdIdx][6];
		break;
	  case "bill_discount":
		if(strtotime($rd[$rdIdx][4]) < strtotime($period_from)) {
			$tot[0] -= (int) $rd[$rdIdx][6];
		} else {
			$tot[4] += (int) $rd[$rdIdx][6];
		}
		$tot[7] += (int) $rd[$rdIdx][6];
		$tot[12] -= (int) $rd[$rdIdx][6];
		break;
	  case "bill_freight":
		$tot[8] += (int) $rd[$rdIdx][6];
		break;
	  case "return":
		/*
		1. return RR, 
		2. return Unpaid -> Ngurangin nilai amount bill
		3. return Paid Billing, Money back No -> Nilai return muncul, tapi tidak dihitung
		*/
		if($rd[$rdIdx][7] != '1') {
			if(strtotime($rd[$rdIdx][4]) < strtotime($period_from)) {
				if($rd[$rdIdx][7] != '3') {
					$tot[0] -= (int) $rd[$rdIdx][6];
				}
			} else {
				$tot[3] += (int) $rd[$rdIdx][6];
				if($rd[$rdIdx][7] == '3') {
					$tot[13] += (int) $rd[$rdIdx][6];
				}
			}
			$tot[9] += (int) $rd[$rdIdx][6];
			if($rd[$rdIdx][7] == '2') {
				$tot[12] -= (int) $rd[$rdIdx][6];
			}
		}
		break;
	  case "payment":
		// if payment < (bill_item - bill_disc - return)
		if($cust[$rd[$rdIdx][2]][$rd[$rdIdx][3]][$rd[$rdIdx][0]][10] < $cust[$rd[$rdIdx][2]][$rd[$rdIdx][3]][$rd[$rdIdx][0]][12]) {
			// if (payment yang ada + payment baru) > (bill_item - bill_disc - return)
			if( ($cust[$rd[$rdIdx][2]][$rd[$rdIdx][3]][$rd[$rdIdx][0]][10] + (int) $rd[$rdIdx][6]) > $cust[$rd[$rdIdx][2]][$rd[$rdIdx][3]][$rd[$rdIdx][0]][12]) {
				if(strtotime($rd[$rdIdx][4]) < strtotime($period_from)) 
					 $tot[0] -= $cust[$rd[$rdIdx][2]][$rd[$rdIdx][3]][$rd[$rdIdx][0]][12] - $cust[$rd[$rdIdx][2]][$rd[$rdIdx][3]][$rd[$rdIdx][0]][10];
				else $tot[2] += $cust[$rd[$rdIdx][2]][$rd[$rdIdx][3]][$rd[$rdIdx][0]][12] - $cust[$rd[$rdIdx][2]][$rd[$rdIdx][3]][$rd[$rdIdx][0]][10];
			// else if payment baru > (bill_item - bill_disc - return)
			} else if((int) $rd[$rdIdx][6] > $cust[$rd[$rdIdx][2]][$rd[$rdIdx][3]][$rd[$rdIdx][0]][12]) {
				if(strtotime($rd[$rdIdx][4]) < strtotime($period_from)) 
					 $tot[0] -= (int) ($cust[$rd[$rdIdx][2]][$rd[$rdIdx][3]][$rd[$rdIdx][0]][6] - $cust[$rd[$rdIdx][2]][$rd[$rdIdx][3]][$rd[$rdIdx][0]][12]);
				else $tot[2] += (int) ($cust[$rd[$rdIdx][2]][$rd[$rdIdx][3]][$rd[$rdIdx][0]][6] - $cust[$rd[$rdIdx][2]][$rd[$rdIdx][3]][$rd[$rdIdx][0]][12]);
			} else {
				if(strtotime($rd[$rdIdx][4]) < strtotime($period_from)) 
					 $tot[0] -= (int) $rd[$rdIdx][6];
				else $tot[2] += (int) $rd[$rdIdx][6];
			}
		} else {
			$tot[2] += (int) $rd[$rdIdx][6];
		}
		$tot[10] += (int) $rd[$rdIdx][6];
		break;
	}

	for($i=0; $i<14; $i++) $cust[$rd[$rdIdx][2]][$rd[$rdIdx][3]][$rd[$rdIdx][0]][$i] += $tot[$i];
	$cust_channel[] = $rd[$rdIdx][2]; $cust_cus[] = $rd[$rdIdx][3];
	$rdIdx++;
			}
		}
	}
}

echo "<pre>";
//var_dump($cust);
echo "</pre>";

$cust_channel = array_values(array_unique($cust_channel));
$cust_cus = array_values(array_unique($cust_cus));

$g_total = array(0,0,0,0,0,0);
for($i=0; $i<count($cust_channel); $i++) {
	$key = $cust_channel[$i];

	$t_total = array(0,0,0,0,0,0);
	if(count($cust[$key]) > 0) {
	print "<span>".$channel[$key]."</span>";
print <<<END
<table width="100%" class="table_f">
	<tr>
		<th rowspan="2">NAMA CUSTOMER</th>
		<th width="12%" rowspan="2">SALDO AWAL</th>
		<th colspan="4">TRANSAKSI</th>
		<th width="12%" rowspan="2">SALDO AKHIR</th>
	</tr>
	<tr>
		<th width="12%">TAGIHAN</th>
		<th width="12%">BAYAR</th>
		<th width="12%">RETURN</th>
		<th width="12%">POT. TAGIHAN</th>
	</tr>\n
END;

		foreach($cust[$key] as $a => $cus) { 
			$total = array(0,0,0,0,0,0,0,0,0,0,0,0);
			if(count($cus) > 0) {
				foreach($cus as $b => $bill) {
					for($j=0; $j<14; $j++) $total[$j] += $bill[$j];
				}
				$total[5] = $total[0] + $total[1] - $total[2] - $total[3] - $total[4] + $total[13];
			}
	
			print "<tr>\n";
			cell_link($a, '', ' href="summary_by_acc_receivable_detail.php?code='.substr($a, 1, strpos($a, ']')-1).'&name='.$a.'"');		// Customer
			for($j=0; $j<6; $j++) {
				cell(number_format($total[$j]), ' align="right"');
				$t_total[$j] += $total[$j];
			}
			print "</tr>\n";
		}
	
		print "<tr>\n";
		cell("Total ".$channel[$key] . " ", ' align="right" style="color:brown; background-color:lightyellow"');
		for($j=0; $j<6; $j++) {
			cell(number_format($t_total[$j]), ' align="right" style="color:brown; background-color:lightyellow"');
			$g_total[$j] += $t_total[$j];
		}
		print "</tr>\n";
		print "</table><br />";
	}
}

print <<<END
<table width="100%" class="table_f">
	<tr>
		<th rowspan="2">NAMA CUSTOMER</th>
		<th width="12%" rowspan="2">SALDO AWAL</th>
		<th colspan="4">TRANSAKSI</th>
		<th width="12%" rowspan="2">SALDO AKHIR</th>
	</tr>
	<tr>
		<th width="12%">TAGIHAN</th>
		<th width="12%">BAYAR</th>
		<th width="12%">RETURN</th>
		<th width="12%">POT. TAGIHAN</th>
	</tr>\n
END;
print "<tr>\n";
	cell("GRAND TOTAL", ' style="color:brown; background-color:lightyellow"');
	for($j=0; $j<6; $j++) {
		cell(number_format($g_total[$j]), ' align="right" style="color:brown; background-color:lightyellow"');
	}
	print "</tr>\n";
print "</table>";

echo "<pre>";
//print_r($cust);
echo "</pre>";
?>
