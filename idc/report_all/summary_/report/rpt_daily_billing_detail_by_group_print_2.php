<?php
$db = array("idc", "med");
$j = 0;
for($i=0; $i<2; $i++) {

	$sql_bill[$i] .= "
	  b.bill_ship_to AS ship_to,
	  b.bill_ship_to_name AS ship_to_name,
	  b.bill_code AS invoice_code,
	  to_char(b.bill_inv_date, 'dd-Mon-YY') AS invoice_issue_date,
	  to_char(b.bill_payment_giro_due, 'dd-Mon-YY') AS invoice_due_date,
	  to_char(b.bill_sales_from, 'dd-Mon-YY') AS invoice_sales_from,
	  to_char(b.bill_sales_to, 'dd-Mon-YY') AS invoice_sales_to,
	  b.bill_delivery_freight_charge AS freight_charge,
	  b.bill_discount AS discount,
	  bi.biit_idx AS it_idx,
	  it.it_code AS it_code,
	  it.it_model_no AS it_model_no,
	  TRUNC(bi.biit_unit_price * (1 - b.bill_discount/100),2) AS unit_price,
	  bi.biit_qty AS qty,
	  TRUNC((bi.biit_qty * bi.biit_unit_price * (1 - b.bill_discount/100)),2) AS amount,
	  TRUNC((b.bill_vat/100)*(bi.biit_qty * bi.biit_unit_price * (1 - b.bill_discount/100)),2) AS vat,
	  bill_total_billing AS grand_total,
	  b.bill_inv_date AS invoice_date,
	  'billing' AS invoice_condition,
	  CASE
		WHEN b.bill_cfm_tukar_faktur IS NOT NULL AND b.bill_remain_amount > 0 AND b.bill_payment_giro_due > CURRENT_TIMESTAMP THEN 'bill_before_due_tf'
		WHEN b.bill_cfm_tukar_faktur IS NOT NULL AND b.bill_remain_amount > 0 AND b.bill_payment_giro_due < CURRENT_TIMESTAMP THEN 'bill_over_due_tf'
		WHEN b.bill_cfm_tukar_faktur IS NOT NULL AND b.bill_remain_amount <= 0 THEN 'bill_paid_tf'
		WHEN b.bill_total_billing = 0 THEN 'bill_before_due'
		WHEN b.bill_remain_amount <= 0 THEN 'bill_paid'
		WHEN b.bill_remain_amount > 0 AND b.bill_payment_giro_due > CURRENT_TIMESTAMP THEN 'bill_before_due'
		WHEN b.bill_remain_amount > 0 AND b.bill_payment_giro_due < CURRENT_TIMESTAMP THEN 'bill_over_due'
	  END AS invoice_layout_general,
	  CASE
		WHEN b.bill_cfm_tukar_faktur IS NOT NULL AND b.bill_remain_amount > 0 AND b.bill_payment_giro_due > CURRENT_TIMESTAMP THEN 'bill_before_due_tf'
		WHEN b.bill_cfm_tukar_faktur IS NOT NULL AND b.bill_remain_amount > 0 AND b.bill_payment_giro_due < CURRENT_TIMESTAMP THEN 'bill_over_due_tf'
		WHEN b.bill_cfm_tukar_faktur IS NOT NULL AND b.bill_remain_amount <= 0 THEN 'bill_paid_tf'
		WHEN b.bill_total_billing = 0 THEN 'bill_before_due'
		WHEN b.bill_remain_amount <= 0 THEN 'bill_paid'
		WHEN b.bill_remain_amount > 0 AND b.bill_payment_giro_due > CURRENT_TIMESTAMP THEN 'bill_before_due'
		WHEN b.bill_remain_amount > 0 AND b.bill_payment_giro_due < CURRENT_TIMESTAMP THEN 'bill_over_due'
	  END AS invoice_layout_qty,
	  CASE
		WHEN b.bill_cfm_tukar_faktur IS NOT NULL AND b.bill_remain_amount > 0 AND b.bill_payment_giro_due > CURRENT_TIMESTAMP THEN 'bill_before_due_tf'
		WHEN b.bill_cfm_tukar_faktur IS NOT NULL AND b.bill_remain_amount > 0 AND b.bill_payment_giro_due < CURRENT_TIMESTAMP THEN 'bill_over_due_tf'
		WHEN b.bill_cfm_tukar_faktur IS NOT NULL AND b.bill_remain_amount <= 0 THEN 'bill_paid_tf'
		WHEN b.bill_total_billing = 0 THEN 'bill_before_due'
		WHEN b.bill_remain_amount <= 0 THEN 'bill_paid'
		WHEN b.bill_remain_amount > 0 AND b.bill_payment_giro_due > CURRENT_TIMESTAMP THEN 'bill_before_due'
		WHEN b.bill_remain_amount > 0 AND b.bill_payment_giro_due < CURRENT_TIMESTAMP THEN 'bill_over_due'
	  END AS invoice_layout_amount,
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
	  END AS go_page
	FROM
	 ".$db[$i]."_tb_customer AS c
	 JOIN ".$db[$i]."_tb_billing AS b ON bill_ship_to = c.cus_code
	 JOIN ".$db[$i]."_tb_billing_item AS bi USING(bill_code)
	 JOIN ".$db[$i]."_tb_item AS it USING(it_code)
	WHERE " . $strWhere[$j++] ;
	
	$sql_return[$i] .= "
	  t.turn_ship_to AS ship_to,
	  t.turn_ship_to_name AS ship_to_name,
	  t.turn_code AS invoice_code,
	  to_char(t.turn_return_date, 'dd-Mon-YY') AS invoice_issued_date,
	  to_char(t.turn_payment_giro_due, 'dd-Mon-YY') AS invoice_due_date,
	  NULL AS sales_from,
	  NULL AS sales_to,
	  t.turn_delivery_freight_charge AS freight_charge,
	  t.turn_discount AS discount,
	  reit_idx AS it_idx,
	  it.it_code AS it_code,
	  it.it_model_no AS it_model_no,
	  TRUNC(ti.reit_unit_price * (1 - t.turn_discount/100),2) AS unit_price,
	  ti.reit_qty AS qty,
	  TRUNC((ti.reit_qty * ti.reit_unit_price * (1 - t.turn_discount/100)),2) AS amount,
	  TRUNC((t.turn_vat/100)*(ti.reit_qty * ti.reit_unit_price * (1 - t.turn_discount/100)),2) AS vat,
	  turn_total_return AS grand_total,
	  t.turn_return_date AS invoice_date,
	  CASE
		WHEN t.turn_return_condition = 1 THEN 'turn_counted' 
		WHEN t.turn_return_condition = 2 THEN 'turn_counted'
		WHEN t.turn_return_condition = 3 THEN 'turn_counted'
		WHEN t.turn_return_condition = 4 THEN 'turn_counted'
	  END AS invoice_condition,
	  'turn_counted' AS invoice_layout_general,
	  'turn_counted' AS invoice_layout_qty,
	  CASE
		WHEN t.turn_return_condition = 1 THEN 'turn_uncounted'
		ELSE 'turn_counted'
	  END AS invoice_layout_amount,
	  CASE 
	  	WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'idc' THEN 'idc_'||t.turn_code
		WHEN t.turn_ordered_by = 2 AND '".$db[$i]."' = 'idc' THEN 'mep_'||t.turn_code
		WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'med' THEN 'med_'||t.turn_code
	  END AS invoice_source,
	  CASE
	  	WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND t.turn_dept = 'A' THEN 'http://192.168.1.88/idc/apotik/billing/revise_billing.php?_code='||turn_code
	  	WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND t.turn_dept = 'D' THEN 'http://192.168.1.88/idc/dealer/billing/revise_billing.php?_code='||turn_code
	  	WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND t.turn_dept = 'H' THEN 'http://192.168.1.88/idc/hospital/billing/revise_billing.php?_code='||turn_code
	  	WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND t.turn_dept = 'M' THEN 'http://192.168.1.88/idc/marketing/billing/revise_billing.php?_code='||turn_code
	  	WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND t.turn_dept = 'P' THEN 'http://192.168.1.88/idc/pharmaceutical/billing/revise_billing.php?_code='||turn_code
	  	WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND t.turn_dept = 'T' THEN 'http://192.168.1.88/idc/tender/billing/revise_billing.php?_code='||turn_code
	  	WHEN t.turn_ordered_by = 2 AND '".$db[$i]."' = 'idc' AND t.turn_dept = 'A' THEN 'http://192.168.1.88/mep/apotik/billing/revise_billing.php?_code='||turn_code
	  	WHEN t.turn_ordered_by = 2 AND '".$db[$i]."' = 'idc' AND t.turn_dept = 'D' THEN 'http://192.168.1.88/mep/dealer/billing/revise_billing.php?_code='||turn_code
	  	WHEN t.turn_ordered_by = 2 AND '".$db[$i]."' = 'idc' AND t.turn_dept = 'H' THEN 'http://192.168.1.88/mep/hospital/billing/revise_billing.php?_code='||turn_code
	  	WHEN t.turn_ordered_by = 2 AND '".$db[$i]."' = 'idc' AND t.turn_dept = 'M' THEN 'http://192.168.1.88/mep/marketing/billing/revise_billing.php?_code='||turn_code
	  	WHEN t.turn_ordered_by = 2 AND '".$db[$i]."' = 'idc' AND t.turn_dept = 'P' THEN 'http://192.168.1.88/mep/pharmaceutical/billing/revise_billing.php?_code='||turn_code
	  	WHEN t.turn_ordered_by = 2 AND '".$db[$i]."' = 'idc' AND t.turn_dept = 'T' THEN 'http://192.168.1.88/mep/tender/billing/revise_billing.php?_code='||turn_code
	  	WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'med' AND t.turn_dept = 'A' THEN 'http://192.168.1.88/med/apotik/billing/revise_billing.php?_code='||turn_code
	  	WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'med' AND t.turn_dept = 'D' THEN 'http://192.168.1.88/med/dealer/billing/revise_billing.php?_code='||turn_code
	  	WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'med' AND t.turn_dept = 'H' THEN 'http://192.168.1.88/med/hospital/billing/revise_billing.php?_code='||turn_code
	  	WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'med' AND t.turn_dept = 'M' THEN 'http://192.168.1.88/med/marketing/billing/revise_billing.php?_code='||turn_code
	  	WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'med' AND t.turn_dept = 'P' THEN 'http://192.168.1.88/med/pharmaceutical/billing/revise_billing.php?_code='||turn_code
	  	WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'med' AND t.turn_dept = 'T' THEN 'http://192.168.1.88/med/tender/billing/revise_billing.php?_code='||turn_code
	  END AS go_page
	FROM
	 ".$db[$i]."_tb_customer AS c
	 JOIN ".$db[$i]."_tb_return AS t ON t.turn_ship_to = c.cus_code
	 JOIN ".$db[$i]."_tb_return_item AS ti USING(turn_code)
	 JOIN ".$db[$i]."_tb_item AS it USING(it_code)
	WHERE " . $strWhere[$j++] ;
	
	$sql_dr[$i] .= "
	  b.dr_ship_to AS ship_to,
	  b.dr_ship_name AS ship_to_name,
	  b.dr_code AS invoice_code,
	  to_char(b.dr_issued_date, 'dd-Mon-YY') AS invoice_issue_date,
	  null AS invoice_due_date,
	  null AS invoice_sales_from,
	  null AS invoice_sales_to,
	  null AS freight_charge,
	  null AS discount,
	  bi.drit_idx AS it_idx,
	  it.it_code AS it_code,
	  it.it_model_no AS it_model_no,
	  null AS unit_price,
	  bi.drit_qty AS qty,
	  null AS amount,
	  null AS vat,
	  null AS grand_total,
	  b.dr_issued_date AS invoice_date,
	  'billing' AS invoice_condition,
	  'bill_before_due' AS invoice_layout_general,
	  'bill_before_due' AS invoice_layout_qty,
	  'bill_before_due' AS invoice_layout_amount,
	  CASE 
	  	WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'idc' THEN 'idc_'||b.dr_code
		WHEN b.dr_ordered_by = 2 AND '".$db[$i]."' = 'idc' THEN 'mep_'||b.dr_code
		WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'med' THEN 'med_'||b.dr_code
	  END AS invoice_source,
	  CASE
		WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.dr_dept = 'A' THEN 'http://192.168.1.88/idc/apotik/billing/revise_billing.php?_code='||dr_code
	  	WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.dr_dept = 'D' THEN 'http://192.168.1.88/idc/dealer/billing/revise_billing.php?_code='||dr_code
	  	WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.dr_dept = 'H' THEN 'http://192.168.1.88/idc/hospital/billing/revise_billing.php?_code='||dr_code
	  	WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.dr_dept = 'M' THEN 'http://192.168.1.88/idc/marketing/billing/revise_billing.php?_code='||dr_code
	  	WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.dr_dept = 'P' THEN 'http://192.168.1.88/idc/pharmaceutical/billing/revise_billing.php?_code='||dr_code
	  	WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.dr_dept = 'T' THEN 'http://192.168.1.88/idc/tender/billing/revise_billing.php?_code='||dr_code
	  	WHEN b.dr_ordered_by = 2 AND '".$db[$i]."' = 'mep' AND b.dr_dept = 'A' THEN 'http://192.168.1.88/mep/apotik/billing/revise_billing.php?_code='||dr_code
	  	WHEN b.dr_ordered_by = 2 AND '".$db[$i]."' = 'mep' AND b.dr_dept = 'D' THEN 'http://192.168.1.88/mep/dealer/billing/revise_billing.php?_code='||dr_code
	  	WHEN b.dr_ordered_by = 2 AND '".$db[$i]."' = 'mep' AND b.dr_dept = 'H' THEN 'http://192.168.1.88/mep/hospital/billing/revise_billing.php?_code='||dr_code
	  	WHEN b.dr_ordered_by = 2 AND '".$db[$i]."' = 'mep' AND b.dr_dept = 'M' THEN 'http://192.168.1.88/mep/marketing/billing/revise_billing.php?_code='||dr_code
	  	WHEN b.dr_ordered_by = 2 AND '".$db[$i]."' = 'mep' AND b.dr_dept = 'P' THEN 'http://192.168.1.88/mep/pharmaceutical/billing/revise_billing.php?_code='||dr_code
	  	WHEN b.dr_ordered_by = 2 AND '".$db[$i]."' = 'mep' AND b.dr_dept = 'T' THEN 'http://192.168.1.88/mep/tender/billing/revise_billing.php?_code='||dr_code
	  	WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.dr_dept = 'A' THEN 'http://192.168.1.88/med/apotik/billing/revise_billing.php?_code='||dr_code
	  	WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.dr_dept = 'D' THEN 'http://192.168.1.88/med/dealer/billing/revise_billing.php?_code='||dr_code
	  	WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.dr_dept = 'H' THEN 'http://192.168.1.88/med/hospital/billing/revise_billing.php?_code='||dr_code
	  	WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.dr_dept = 'M' THEN 'http://192.168.1.88/med/marketing/billing/revise_billing.php?_code='||dr_code
	  	WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.dr_dept = 'P' THEN 'http://192.168.1.88/med/pharmaceutical/billing/revise_billing.php?_code='||dr_code
	  	WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.dr_dept = 'T' THEN 'http://192.168.1.88/med/tender/billing/revise_billing.php?_code='||dr_code	  
	  END AS go_page
	FROM
	 ".$db[$i]."_tb_customer AS c
	 JOIN ".$db[$i]."_tb_dr AS b ON dr_ship_to = c.cus_code
	 JOIN ".$db[$i]."_tb_dr_item AS bi USING(dr_code)
	 JOIN ".$db[$i]."_tb_item AS it USING(it_code)
	WHERE " . $strWhere[$j++];

}

switch (ZKP_URL) {
  case "ALL":
	$sql =	$sql_bill[0] . " UNION " . $sql_return[0] . " UNION " .  $sql_dr[0] . " UNION " . 
			$sql_bill[1] . " UNION " . $sql_return[1] . " UNION " .  $sql_dr[1];
	break;
  case "IDC":
	$sql =	$sql_bill[0] . " UNION " . $sql_return[0] . " UNION " .  $sql_dr[0];
	break;
  case "MED":
	$sql =	$sql_bill[1] . " UNION " . $sql_return[1] . " UNION " .  $sql_dr[1];
	break;	
  case "MEP":
	$sql =	$sql_bill[0] . " UNION " . $sql_return[0] . " UNION " .  $sql_dr[0];
	break;
}

$sql .= " ORDER BY cug_name, ship_to, invoice_date, invoice_code, it_code";
/*
echo "<pre>";
var_dump($sql_bill[0]);
echo "</pre>";
*/
// raw data
$rd		= array();
$rdIdx	= 0;
$cache	= array("","","","");
$group0	= array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['cug_name'],					//0
		$col['ship_to'],					//1
		$col['ship_to_name'],				//2
		$col['invoice_code'], 				//3
		$col['invoice_issue_date'], 		//4
		$col['invoice_due_date'],			//5
		$col['invoice_sales_from'],			//6
		$col['invoice_sales_to'],			//7
		$col['freight_charge'],				//8
		$col['discount'],					//9
		$col['it_idx'],						//10
		$col['it_code'], 					//11
		$col['it_model_no'],				//12
		$col['unit_price'], 				//13
		$col['qty'],						//14
		$col['amount'],						//15
		$col['vat'],						//16
		$col['amount']+$col['vat'],			//17
		$col['grand_total'],				//18
		$col['invoice_condition'],			//19
		$col['invoice_layout_general'],		//20
		$col['invoice_layout_qty'],			//21
		$col['invoice_layout_amount'],		//22
		$col['go_page'],					//23
		$col['go_page'],					//24
		$col['invoice_source']				//25
		
	);

	//1st grouping
	if($cache[0] != $col['cug_name']) {
		$cache[0] = $col['cug_name'];
		$group0[$col['cug_name']] = array();
	}

	if($cache[1] != $col['ship_to']) {
		$cache[1] = $col['ship_to'];
		$group0[$col['cug_name']][$col['ship_to']] = array();
	}

	if($cache[2] != $col['invoice_source']) {
		$cache[2] = $col['invoice_source'];
		$group0[$col['cug_name']][$col['ship_to']][$col['invoice_source']] = array();
	}

	if($cache[3] != $col['it_idx']) {
		$cache[3] = $col['it_idx'];
	}

	$group0[$col['cug_name']][$col['ship_to']][$col['invoice_source']][$col['it_idx']] = 1;
}
?>