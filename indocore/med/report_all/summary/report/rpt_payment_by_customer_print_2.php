<?php
$db = array("idc", "med");
$j = 0;
for($i=0; $i<2; $i++) {
	$sql_billing1[$i] = "
	SELECT
	  c.cus_code AS ship_to,
	  c.cus_full_name AS ship_to_name,
	  b.bill_code AS invoice_code,
	  b.bill_inv_date AS bill_inv_date,
	  to_char(b.bill_inv_date, 'dd/Mon/YY') AS invoice_date,
	  to_char(b.bill_payment_giro_due, 'dd/Mon/YY') AS invoice_due_date,
	  ".$db[$i]."_getDueRemain(b.bill_code) AS invoice_due_remain,
	  trunc((b.bill_total_billing - b.bill_delivery_freight_charge) * 100 / (b.bill_vat+100)) AS amount,
	  b.bill_delivery_freight_charge AS delivery_charge,
	  trunc((b.bill_total_billing - b.bill_delivery_freight_charge) * 100 / (b.bill_vat+100) * b.bill_vat/100) AS vat,
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
	  CASE 
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'idc' THEN 'idc_'||b.bill_code
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'idc' THEN 'mep_'||b.bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' THEN 'med_'||b.bill_code
	  END AS invoice_source,
	  CASE
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'A' THEN 'http://192.168.1.88/idc/apotik/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'D' THEN 'http://192.168.1.88/idc/dealer/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'H' THEN 'http://192.168.1.88/idc/hospital/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'M' THEN 'http://192.168.1.88/idc/marketing/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'P' THEN 'http://192.168.1.88/idc/pharmaceutical/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'T' THEN 'http://192.168.1.88/idc/tender/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'A' THEN 'http://192.168.1.88/mep/apotik/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'D' THEN 'http://192.168.1.88/mep/dealer/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'H' THEN 'http://192.168.1.88/mep/hospital/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'M' THEN 'http://192.168.1.88/mep/marketing/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'P' THEN 'http://192.168.1.88/mep/pharmaceutical/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'T' THEN 'http://192.168.1.88/mep/tender/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.bill_dept = 'A' THEN 'http://192.168.1.88/med/apotik/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.bill_dept = 'D' THEN 'http://192.168.1.88/med/dealer/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.bill_dept = 'H' THEN 'http://192.168.1.88/med/hospital/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.bill_dept = 'M' THEN 'http://192.168.1.88/med/marketing/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.bill_dept = 'P' THEN 'http://192.168.1.88/med/pharmaceutical/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.bill_dept = 'T' THEN 'http://192.168.1.88/med/tender/billing/revise_billing.php?_code='||bill_code
	  END AS go_page,
	  CASE
		WHEN p.pay_paid <= 0 then 'A'
		WHEN p.pay_paid > 0 AND p.pay_note = 'DEPOSIT-B' THEN 'B'
		ELSE 'C'
	  END AS type_type
	FROM
	  ".$db[$i]."_tb_customer AS c
	  JOIN ".$db[$i]."_tb_billing AS b ON cus_code = bill_ship_to
	  JOIN ".$db[$i]."_tb_payment AS p USING(bill_code)
	WHERE " . $strWhere[$j++];
	
	$sql_billing2[$i] = "
	SELECT
	  c.cus_code AS ship_to,
	  c.cus_full_name AS ship_to_name,
	  b.bill_code AS invoice_code,
	  b.bill_inv_date AS bill_inv_date,
	  to_char(b.bill_inv_date, 'dd/Mon/YY') AS invoice_date,
	  to_char(b.bill_payment_giro_due, 'dd/Mon/YY') AS invoice_due_date,
	  ".$db[$i]."_getDueRemain(b.bill_code) AS invoice_due_remain,
	  trunc((b.bill_total_billing - b.bill_delivery_freight_charge) * 100 / (b.bill_vat+100)) AS amount,
	  b.bill_delivery_freight_charge AS delivery_charge,
	  trunc((b.bill_total_billing - b.bill_delivery_freight_charge) * 100 / (b.bill_vat+100) * b.bill_vat/100) AS vat,
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
	  CASE 
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'idc' THEN 'idc_'||b.bill_code
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'idc' THEN 'mep_'||b.bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' THEN 'med_'||b.bill_code
	  END AS invoice_source,
	  CASE
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'A' THEN 'http://192.168.1.88/idc/apotik/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'D' THEN 'http://192.168.1.88/idc/dealer/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'H' THEN 'http://192.168.1.88/idc/hospital/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'M' THEN 'http://192.168.1.88/idc/marketing/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'P' THEN 'http://192.168.1.88/idc/pharmaceutical/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.bill_dept = 'T' THEN 'http://192.168.1.88/idc/tender/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'mep' AND b.bill_dept = 'A' THEN 'http://192.168.1.88/mep/apotik/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'mep' AND b.bill_dept = 'D' THEN 'http://192.168.1.88/mep/dealer/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'mep' AND b.bill_dept = 'H' THEN 'http://192.168.1.88/mep/hospital/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'mep' AND b.bill_dept = 'M' THEN 'http://192.168.1.88/mep/marketing/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'mep' AND b.bill_dept = 'P' THEN 'http://192.168.1.88/mep/pharmaceutical/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'mep' AND b.bill_dept = 'T' THEN 'http://192.168.1.88/mep/tender/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.bill_dept = 'A' THEN 'http://192.168.1.88/med/apotik/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.bill_dept = 'D' THEN 'http://192.168.1.88/med/dealer/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.bill_dept = 'H' THEN 'http://192.168.1.88/med/hospital/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.bill_dept = 'M' THEN 'http://192.168.1.88/med/marketing/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.bill_dept = 'P' THEN 'http://192.168.1.88/med/pharmaceutical/billing/revise_billing.php?_code='||bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.bill_dept = 'T' THEN 'http://192.168.1.88/med/tender/billing/revise_billing.php?_code='||bill_code
	  END AS go_page,
	  CASE
		WHEN p.pay_paid <= 0 then 'A'
		WHEN p.pay_paid > 0 AND p.pay_note = 'DEPOSIT-B' THEN 'B'
		ELSE 'C'
	  END AS type_type
	FROM
	  ".$db[$i]."_tb_customer AS c
	  JOIN ".$db[$i]."_tb_billing AS b ON cus_code = bill_ship_to
	  JOIN ".$db[$i]."_tb_payment AS p USING(bill_code)
	WHERE " . $strWhere[$j++];

	$sql_service[$i] = "
	SELECT
	  c.cus_code AS ship_to,
	  c.cus_full_name AS ship_to_name,
	  sv_code AS invoice_code,
	  sv_due_date AS bill_inv_date,
	  to_char(sv_due_date, 'dd/Mon/YY') AS invoice_date,
	  to_char(sv_due_date, 'dd/Mon/YY') AS invoice_due_date,
	  CASE
		WHEN sv_total_remain > 0 THEN (sv_date - CURRENT_DATE)::text
		ELSE '0'
	  END AS invoice_due_remain,
	  sv_total_amount AS amount,
	  null AS delivery_charge,
	  null AS vat,
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
	  svpay_date AS pay_date,
	  'USUAL' AS payment_note,
	  'billing' AS layout,
	  CASE 
		WHEN '".$db[$i]."' = 'idc' THEN 'idc_'||sv_code
		WHEN '".$db[$i]."' = 'med' THEN 'med_'||sv_code
	  END AS invoice_source,
	  CASE
		WHEN '".$db[$i]."' = 'idc' THEN 'http://192.168.1.88/idc/customer_service/service/revise_service.php?_code='||sv_code
		WHEN '".$db[$i]."' = 'med' THEN 'http://192.168.1.88/med/customer_service/service/revise_service.php?_code='||sv_code
	  END AS go_page,
	  null AS type_type
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

$sql .= " ORDER BY ship_to, bill_inv_date, invoice_code, pay_date";
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
$res	= query($sql);
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
		$col['go_page'],				//19
		$col['invoice_source']			//20
	);

	//1st grouping
	if($cache[0] != $col['ship_to']) {
		$cache[0] = $col['ship_to'];
		$group0[$col['ship_to']] = array();
	}

	if($cache[1] != $col['invoice_source']) {
		$cache[1] = $col['invoice_source'];
		$group0[$col['ship_to']][$col['invoice_source']] = array();
	}

	if($cache[2] != $col['pay_idx']) {
		$cache[2] = $col['pay_idx'];
	}

	$group0[$col['ship_to']][$col['invoice_source']][$col['pay_idx']] = 1;
}
?>