<?php
$db = array("idc", "med");
$j = 0;
for($i=0; $i<2; $i++) {

	$sql_billing1[$i] .= "
	  to_char(p.pay_date, 'YYMM') AS month,
	  to_char(p.pay_date, 'Mon, YYYY') AS pay_month,
	  EXTRACT(WEEK FROM p.pay_date) AS pay_week,
	  to_char(b.bill_inv_date, 'dd/Mon/YY') AS invoice_date,
	  b.bill_code AS invoice_code,
	  c.cus_code AS ship_to,
	  c.cus_full_name AS ship_to_name,
	  to_char(b.bill_payment_giro_due, 'dd/Mon/YY') AS invoice_due_date,
	  ".$db[$i]."_getDueRemain(b.bill_code) AS invoice_due_remain,
	  ROUND((b.bill_total_billing - b.bill_delivery_freight_charge) * 100 / (b.bill_vat+100)) AS amount,
	  b.bill_delivery_freight_charge AS delivery_charge,
	  ROUND((b.bill_total_billing - b.bill_delivery_freight_charge) * 100 / (b.bill_vat+100) * b.bill_vat/100) AS vat,
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
	  CASE
		WHEN p.pay_paid <= 0 then 'return'
		WHEN p.pay_paid > 0 AND p.pay_note = 'DEPOSIT-B' THEN 'uncounted'
		ELSE 'billing'
	  END AS layout,
	  bill_inv_date AS inv_date,
	  p.pay_date AS pay_date,
	  p.pay_note AS payment_note,
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
	  END AS go_page,
	  'payment_billing' AS pay_source
	FROM
	  ".$db[$i]."_tb_customer AS c
	  JOIN ".$db[$i]."_tb_billing AS b ON cus_code = bill_ship_to
	  JOIN ".$db[$i]."_tb_payment AS p USING(bill_code)
	WHERE " . $strWhere[$j++];
	
	$sql_billing2[$i] .= "
	  to_char(p.pay_date, 'YYMM') AS month,
	  to_char(p.pay_date, 'Mon, YYYY') AS pay_month,
	  EXTRACT(WEEK FROM p.pay_date) AS pay_week,
	  to_char(b.bill_inv_date, 'dd/Mon/YY') AS invoice_date,
	  b.bill_code AS invoice_code,
	  c.cus_code AS ship_to,
	  c.cus_full_name AS ship_to_name,
	  to_char(b.bill_payment_giro_due, 'dd/Mon/YY') AS invoice_due_date,
	  ".$db[$i]."_getDueRemain(b.bill_code) AS invoice_due_remain,
	  ROUND((b.bill_total_billing - b.bill_delivery_freight_charge) * 100 / (b.bill_vat+100)) AS amount,
	  b.bill_delivery_freight_charge AS delivery_charge,
	  ROUND((b.bill_total_billing - b.bill_delivery_freight_charge) * 100 / (b.bill_vat+100) * b.bill_vat/100) AS vat,
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
	  CASE
		WHEN p.pay_paid <= 0 then 'return'
		WHEN p.pay_paid > 0 AND p.pay_note = 'DEPOSIT-B' THEN 'uncounted'
		ELSE 'billing'
	  END AS layout,
	  bill_inv_date AS inv_date,
	  p.pay_date AS pay_date,
	  p.pay_note AS payment_note,
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
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'mep' AND b.bill_dept = 'A' THEN 'http://192.168.0.88/mep/apotik/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'mep' AND b.bill_dept = 'D' THEN 'http://192.168.0.88/mep/dealer/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'mep' AND b.bill_dept = 'H' THEN 'http://192.168.0.88/mep/hospital/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'mep' AND b.bill_dept = 'M' THEN 'http://192.168.0.88/mep/marketing/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'mep' AND b.bill_dept = 'P' THEN 'http://192.168.0.88/mep/pharmaceutical/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'mep' AND b.bill_dept = 'T' THEN 'http://192.168.0.88/mep/tender/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.bill_dept = 'A' THEN 'http://192.168.0.88/med/apotik/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.bill_dept = 'D' THEN 'http://192.168.0.88/med/dealer/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.bill_dept = 'H' THEN 'http://192.168.0.88/med/hospital/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.bill_dept = 'M' THEN 'http://192.168.0.88/med/marketing/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.bill_dept = 'P' THEN 'http://192.168.0.88/med/pharmaceutical/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.bill_dept = 'T' THEN 'http://192.168.0.88/med/tender/billing/revise_billing.php?_code='||bill_code
	  END AS go_page,
	  'payment_billing' AS pay_source
	FROM
	  ".$db[$i]."_tb_customer AS c
	  JOIN ".$db[$i]."_tb_billing AS b ON cus_code = bill_ship_to
	  JOIN ".$db[$i]."_tb_payment AS p USING(bill_code)
	WHERE " . $strWhere[$j++];
	
	$sql_service[$i] .= "
	  to_char(p.svpay_date, 'YYMM') AS month,
	  to_char(p.svpay_date, 'Mon, YYYY') AS pay_month,
	  EXTRACT(WEEK FROM p.svpay_date) AS pay_week,
	  to_char(sv_date, 'dd/Mon/YY') AS invoice_date,
	  sv_code AS invoice_code,
	  c.cus_code AS ship_to,
	  c.cus_full_name AS ship_to_name,
	  to_char(sv_due_date, 'dd/Mon/YY') AS invoice_due_date,
	  CASE
		WHEN sv_total_remain > 0 THEN (sv_date - CURRENT_DATE)::text
		ELSE '0'
	  END AS invoice_due_remain,
	  sv_total_amount AS amount,
	  null AS delivery_charge,
	  null AS amount_vat_freight,
	  sv_total_amount AS grand_total,
	  svpay_idx AS pay_idx,
	  to_char(svpay_date, 'dd/Mon/YY') AS payment_date,
	  CASE
		WHEN svpay_method = 'cash' THEN 'CASH'
		WHEN svpay_method = 'check' THEN 'CHECK'
		WHEN svpay_method = 'transfer' THEN 'T/S'
		WHEN svpay_method = 'giro' THEN 'GIRO'
		ELSE '-'
	  END AS payment_method,
	  svpay_bank AS payment_bank,
	  svpay_paid AS payment_amount,
	  sv_total_remain AS invoice_remain_amount,
	  svpay_remark AS payment_remark,
	  'billing' AS layout,
	  sv_date AS inv_date,
	  svpay_date AS pay_date,
	  'USUAL' AS payment_note,
	  CASE 
		WHEN '".$db[$i]."' = 'idc' THEN 'idc_'||sv_code
		WHEN '".$db[$i]."' = 'med' THEN 'med_'||sv_code
	  END AS invoice_source,
	  CASE
		WHEN '".$db[$i]."' = 'idc' THEN 'http://192.168.0.88/idc/customer_service/service/revise_service.php?_code='||sv_code
		WHEN '".$db[$i]."' = 'med' THEN 'http://192.168.0.88/med/customer_service/service/revise_service.php?_code='||sv_code
	  END AS go_page,
	  'payment_service' AS pay_source
	FROM
	  ".$db[$i]."_tb_customer AS c
	  JOIN ".$db[$i]."_tb_service AS b ON cus_code = sv_cus_to
	  JOIN ".$db[$i]."_tb_service_payment AS p USING(sv_code)
	WHERE " . $strWhere[$j++];

}

switch (ZKP_URL) {
  case "ALL":
	$sql =	$sql_billing1[0] . " UNION " . $sql_billing2[0] . " UNION " .  $sql_service[0] . " UNION " . 
			$sql_billing1[1] . " UNION " . $sql_billing2[1] . " UNION " .  $sql_service[1];
	break;
  case "IDC":
	$sql =	$sql_billing1[0] . " UNION " . $sql_billing2[0] . " UNION " .  $sql_service[0];
	break;
  case "MED":
	$sql =	$sql_billing1[1] . " UNION " . $sql_billing2[1] . " UNION " .  $sql_service[1];
	break;	
  case "MEP":
	$sql =	$sql_billing1[0] . " UNION " . $sql_billing2[0] . " UNION " .  $sql_service[0];
	break;
}

$sql .= " ORDER BY month, pay_week, inv_date,invoice_code, pay_date";
/*
echo "<pre>";
echo $sql;
echo "</pre>";
*/
// raw data
$rd		= array();
$rdIdx	= 0;
$cache	= array("","","", "");
$group0 = array();
$res = query($sql);
while($col = fetchRowAssoc($res)) {

	$rd[] = array(
		$col['pay_month'],				//0
		$col['pay_week'], 				//1
		$col['invoice_date'], 			//2
		$col['invoice_code'],			//3
		$col['ship_to'],				//4
		$col['ship_to_name'],			//5
		$col['invoice_due_date'],		//6
		$col['invoice_due_remain'], 	//7
		$col['amount'], 				//8
		$col['delivery_charge'],		//9
		$col['vat'],					//10
		$col['grand_total'],			//11
		$col['pay_idx'],				//12
		$col['payment_date'],			//13
		$col['payment_method'],			//14
		$col['payment_bank'],			//15
		$col['payment_amount'],			//16
		$col['invoice_remain_amount'],	//17
		$col['payment_remark'],			//18
		$col['payment_note'],			//19
		$col['layout'],					//20
		$col['go_page'],				//21
		$col['pay_source'],				//22
		$col['invoice_source']			//23
	);

	if($cache[0] != $col['pay_month']) {
		$cache[0] = $col['pay_month'];
		$group0[$col['pay_month']] = array();
	}

	if($cache[1] != $col['pay_week']) {
		$cache[1] = $col['pay_week'];
		$group0[$col['pay_month']][$col['pay_week']] = array();
	}
	
	if($cache[2] != $col['invoice_source']) {
		$cache[2] = $col['invoice_source'];
		$group0[$col['pay_month']][$col['pay_week']][$col['invoice_source']] = array();
	}
	
	if($cache[3] != $col['pay_idx']) {
		$cache[3] = $col['pay_idx'];
	}

	$group0[$col['pay_month']][$col['pay_week']][$col['invoice_source']][$col['pay_idx']] = 1;
}
?>