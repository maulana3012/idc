<?php
$db = array("idc", "med");
$j = 0;
for($i=0; $i<2; $i++) {

	//continue sql
	$sql_bill[$i] .="
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
	  ".$db[$i]."_getDueRemain(b.bill_code) AS due_remain,
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
	  CASE 
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'idc' THEN 'idc_'||b.bill_code
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'idc' THEN 'mep_'||b.bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' THEN 'med_'||b.bill_code
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'med' THEN 'smd_'||b.bill_code
	  END AS invoice_source,
	  CASE
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'A' THEN 'http://192.168.0.88/idc/apotik/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'D' THEN 'http://192.168.0.88/idc/dealer/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'H' THEN 'http://192.168.0.88/idc/hospital/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'M' THEN 'http://192.168.0.88/idc/marketing/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'P' THEN 'http://192.168.0.88/idc/pharmaceutical/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'T' THEN 'http://192.168.0.88/idc/tender/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'A' THEN 'http://192.168.0.88/mep/apotik/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'D' THEN 'http://192.168.0.88/mep/dealer/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'H' THEN 'http://192.168.0.88/mep/hospital/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'M' THEN 'http://192.168.0.88/mep/marketing/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'P' THEN 'http://192.168.0.88/mep/pharmaceutical/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'T' THEN 'http://192.168.0.88/mep/tender/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.bill_dept = 'A' THEN 'http://192.168.0.88/med/apotik/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.bill_dept = 'D' THEN 'http://192.168.0.88/med/dealer/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.bill_dept = 'H' THEN 'http://192.168.0.88/med/hospital/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.bill_dept = 'M' THEN 'http://192.168.0.88/med/marketing/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.bill_dept = 'P' THEN 'http://192.168.0.88/med/pharmaceutical/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.bill_dept = 'T' THEN 'http://192.168.0.88/med/tender/billing/revise_billing.php?_code='||bill_code
	  END AS go_page
	FROM ".$db[$i]."_tb_customer AS c JOIN ".$db[$i]."_tb_billing AS b ON bill_ship_to = cus_code
	WHERE " . $strWhere[$j++];
	
	$sql_turn[$i] .="
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
	  ".$db[$i]."_getDueRemain(t.turn_code) AS due_remain,
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
	  CASE 
		WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'idc' THEN 'idc_'||t.turn_code
		WHEN t.turn_ordered_by = 2 AND '".$db[$i]."' = 'idc' THEN 'mep_'||t.turn_code
		WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'med' THEN 'med_'||t.turn_code
		WHEN t.turn_ordered_by = 2 AND '".$db[$i]."' = 'med' THEN 'smd_'||t.turn_code
	  END AS invoice_source,
	  CASE
		WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND t.turn_dept = 'A' THEN 'http://192.168.0.88/idc/apotik/billing/revise_billing.php?_code='||turn_code
		WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND t.turn_dept = 'D' THEN 'http://192.168.0.88/idc/dealer/billing/revise_billing.php?_code='||turn_code
		WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND t.turn_dept = 'H' THEN 'http://192.168.0.88/idc/hospital/billing/revise_billing.php?_code='||turn_code
		WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND t.turn_dept = 'M' THEN 'http://192.168.0.88/idc/marketing/billing/revise_billing.php?_code='||turn_code
		WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND t.turn_dept = 'P' THEN 'http://192.168.0.88/idc/pharmaceutical/billing/revise_billing.php?_code='||turn_code
		WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND t.turn_dept = 'T' THEN 'http://192.168.0.88/idc/tender/billing/revise_billing.php?_code='||turn_code
		WHEN t.turn_ordered_by = 2 AND '".$db[$i]."' = 'idc' AND t.turn_dept = 'A' THEN 'http://192.168.0.88/mep/apotik/billing/revise_billing.php?_code='||turn_code
		WHEN t.turn_ordered_by = 2 AND '".$db[$i]."' = 'idc' AND t.turn_dept = 'D' THEN 'http://192.168.0.88/mep/dealer/billing/revise_billing.php?_code='||turn_code
		WHEN t.turn_ordered_by = 2 AND '".$db[$i]."' = 'idc' AND t.turn_dept = 'H' THEN 'http://192.168.0.88/mep/hospital/billing/revise_billing.php?_code='||turn_code
		WHEN t.turn_ordered_by = 2 AND '".$db[$i]."' = 'idc' AND t.turn_dept = 'M' THEN 'http://192.168.0.88/mep/marketing/billing/revise_billing.php?_code='||turn_code
		WHEN t.turn_ordered_by = 2 AND '".$db[$i]."' = 'idc' AND t.turn_dept = 'P' THEN 'http://192.168.0.88/mep/pharmaceutical/billing/revise_billing.php?_code='||turn_code
		WHEN t.turn_ordered_by = 2 AND '".$db[$i]."' = 'idc' AND t.turn_dept = 'T' THEN 'http://192.168.0.88/mep/tender/billing/revise_billing.php?_code='||turn_code
		WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'med' AND t.turn_dept = 'A' THEN 'http://192.168.0.88/med/apotik/billing/revise_billing.php?_code='||turn_code
		WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'med' AND t.turn_dept = 'D' THEN 'http://192.168.0.88/med/dealer/billing/revise_billing.php?_code='||turn_code
		WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'med' AND t.turn_dept = 'H' THEN 'http://192.168.0.88/med/hospital/billing/revise_billing.php?_code='||turn_code
		WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'med' AND t.turn_dept = 'M' THEN 'http://192.168.0.88/med/marketing/billing/revise_billing.php?_code='||turn_code
		WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'med' AND t.turn_dept = 'P' THEN 'http://192.168.0.88/med/pharmaceutical/billing/revise_billing.php?_code='||turn_code
		WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'med' AND t.turn_dept = 'T' THEN 'http://192.168.0.88/med/tender/billing/revise_billing.php?_code='||turn_code
	  END AS go_page
	FROM ".$db[$i]."_tb_customer AS c JOIN ".$db[$i]."_tb_return AS t ON turn_ship_to = cus_code
	WHERE " . $strWhere[$j++];
	
	$sql_cs[$i] .="
	  to_char(b.sv_date,'dd/Mon/YY') AS invoice_issue_date,
	  b.sv_code AS invoice_code,
	  cus_code AS ship_to,
	  cus_full_name AS ship_to_name,
	  '-' AS payment_method,
	  to_char(b.sv_due_date, 'dd/Mon/YY') AS invoice_due_date,
	  null AS bank,
	  ".$db[$i]."_getDueRemainCS(sv_code) AS due_remain,
	  sv_total_amount AS amount,
	  null AS delivery_charge,
	  null AS vat,
	  null AS amount_vat,
	  b.sv_total_amount AS grand_total,
	  b.sv_total_amount - b.sv_total_remain AS amount_paid,
	  b.sv_total_remain AS remain_amount,
	  to_char(b.sv_last_payment_date,'dd/Mon/YY') AS last_payment_date,
	  b.sv_due_date AS due_payment,
	  'billing' AS invoice_condition,
	  CASE
		WHEN sv_total_remain <= 0 THEN 'bill_paid'
		WHEN sv_total_remain > 0 AND sv_due_date > CURRENT_TIMESTAMP THEN 'bill_before_due'
		WHEN sv_total_remain > 0 AND sv_due_date < CURRENT_TIMESTAMP THEN 'bill_over_due'
	  END AS invoice_layout,
	  CASE 
		WHEN '".$db[$i]."' = 'idc' THEN 'idc_'||sv_code
		WHEN '".$db[$i]."' = 'med' THEN 'med_'||sv_code
	  END AS invoice_source,
	  CASE
		WHEN '".$db[$i]."' = 'idc' THEN 'http://192.168.0.88/idc/customer_service/service/revise_service.php?_code='||sv_code
		WHEN '".$db[$i]."' = 'med' THEN 'http://192.168.0.88/med/customer_service/service/revise_service.php?_code='||sv_code
	  END AS go_page
	FROM ".$db[$i]."_tb_customer AS c JOIN ".$db[$i]."_tb_service AS b ON sv_cus_to = cus_code
	WHERE " . $strWhere[$j++];

}

switch (ZKP_URL) {
  case "ALL":
	$sql =	$sql_bill[0] . " UNION " . $sql_turn[0] . " UNION " .  $sql_cs[0] . " UNION " . 
			$sql_bill[1] . " UNION " . $sql_turn[1] . " UNION " .  $sql_cs[1];
	break;
  case "IDC":
	$sql =	$sql_bill[0] . " UNION " . $sql_turn[0] . " UNION " .  $sql_cs[0];
	break;
  case "MED":
	$sql =	$sql_bill[1] . " UNION " . $sql_turn[1] . " UNION " .  $sql_cs[1];
	break;	
  case "MEP":
	$sql =	$sql_bill[0] . " UNION " . $sql_turn[0] . " UNION " .  $sql_cs[0];
	break;
}

$sql .= " ORDER BY cug_name, due_payment, invoice_code";
/*
echo "<pre>";
var_dump($sql);
echo "</pre>";
exit;
*/
// raw data
$rd = array();
$rdIdx = 0;
$cache = array("","");
$group0 = array();
$res = query($sql);
while($col = fetchRowAssoc($res)) {

	$rd[] = array(
		$col['cug_name'],		//0
		$col['invoice_issue_date'], 	//1
		$col['invoice_code'],		//2
		$col['ship_to'],		//3
		$col['ship_to_name'],		//4
		$col['payment_method'],		//5
		$col['invoice_due_date'], 	//6
		$col['bank'], 			//7
		$col['due_remain'], 		//8
		$col['amount'],			//9
		$col['delivery_charge'],	//10
		$col['vat'],			//11
		$col['amount_vat'],		//12
		$col['grand_total'],		//13
		$col['amount_paid'],		//14
		$col['remain_amount'],		//15
		$col['last_payment_date'],	//16
		$col['invoice_condition'],	//17
		$col['invoice_layout'],		//18
		$col['go_page'],		//19
		$col['invoice_source']		//20
	);

	//1st grouping
	if($cache[0] != $col['cug_name']) {
		$cache[0] = $col['cug_name'];
		$group0[$col['cug_name']] = array();
	}

	if($cache[1] != $col['invoice_source']) {
		$cache[1] = $col['invoice_source'];
	}

	$group0[$col['cug_name']][$col['invoice_source']] = 1;
}
?>