<?php
$db = array("idc", "med");
$j = 0;
for($i=0; $i<2; $i++) {
	$sql_bill[$i] = "
	SELECT
	  icat.icat_pidx AS icat_pidx,
	  it.icat_midx AS icat_midx,
	  it.it_code AS it_code,
	  it.it_model_no AS it_model_no,
	  bi.biit_idx AS it_idx,
	  b.bill_code AS invoice_code,
	  to_char(b.bill_inv_date, 'dd-Mon-yy') AS invoice_issue_date,
	  c.cus_code AS ship_to,
	  c.cus_full_name AS ship_to_name,
	  bi.biit_qty AS qty,
	  TRUNC((bi.biit_unit_price * (1 - b.bill_discount/100)),2) AS unit_price,
	  TRUNC((bi.biit_qty * bi.biit_unit_price * (1 - b.bill_discount/100)),2) AS amount,
	  TRUNC((b.bill_vat/100)*(bi.biit_qty * bi.biit_unit_price * (1 - b.bill_discount/100)),2) AS amount_vat,
	  TRUNC(((bi.biit_qty * bi.biit_unit_price * (1 - b.bill_discount/100)) + (b.bill_vat/100)*(bi.biit_qty * bi.biit_unit_price * (1 - b.bill_discount/100))),2) AS grand_total,
	  b.bill_inv_date AS invoice_date,
	  'billing' AS invoice_condition,
	  'billing' AS invoice_layout_general,
	  'billing' AS invoice_layout_qty,
	  'billing' AS invoice_layout_amount,
	  CASE 
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'idc' THEN 'idc_'||b.bill_code
		WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'idc' THEN 'mep_'||b.bill_code
		WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' THEN 'med_'||b.bill_code
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
	FROM
	  ".$db[$i]."_tb_customer AS c
	  JOIN ".$db[$i]."_tb_billing AS b ON c.cus_code = b.bill_ship_to
	  JOIN ".$db[$i]."_tb_billing_item AS bi USING(bill_code) 
	  JOIN ".$db[$i]."_tb_item_cat AS icat USING(icat_midx)
	  JOIN ".$db[$i]."_tb_item AS it USING(it_code)
	WHERE " . $strWhere[$j++];
	
	$sql_return[$i] = "
	SELECT
	  icat.icat_pidx AS icat_pidx,
	  it.icat_midx AS icat_midx,
	  it.it_code AS it_code,
	  it.it_model_no AS it_model_no,
	  ti.reit_idx AS it_idx,
	  t.turn_code AS invoice_code,
	  to_char(t.turn_return_date, 'dd-Mon-yy') AS invoice_issue_date,
	  c.cus_code AS ship_to,
	  c.cus_full_name AS ship_to_name,
	  ti.reit_qty AS qty,
	  TRUNC((ti.reit_unit_price * (1 - t.turn_discount/1000)),2) AS unit_price,
	  TRUNC((ti.reit_qty * ti.reit_unit_price * (1 - t.turn_discount/100)),2) AS amount,
	  TRUNC(((t.turn_vat/100)*(ti.reit_qty * ti.reit_unit_price * (1 - t.turn_discount/100))),2) AS amount_vat,
	  TRUNC((ti.reit_qty * ti.reit_unit_price * (1 - t.turn_discount/100)) + (t.turn_vat/100)*(ti.reit_qty * ti.reit_unit_price * (1 - t.turn_discount/100)),2) AS grand_total ,
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
	FROM
	  ".$db[$i]."_tb_customer AS c
	  JOIN ".$db[$i]."_tb_return AS t ON c.cus_code = t.turn_ship_to
	  JOIN ".$db[$i]."_tb_return_item AS ti USING(turn_code) 
	  JOIN ".$db[$i]."_tb_item_cat AS icat USING(icat_midx)
	  JOIN ".$db[$i]."_tb_item AS it USING(it_code)
	WHERE " . $strWhere[$j++];
	
	$sql_dr[$i] = "
	SELECT
	  icat.icat_pidx AS icat_pidx,
	  it.icat_midx AS icat_midx,
	  it.it_code AS it_code,
	  it.it_model_no AS it_model_no,
	  bi.drit_idx AS it_idx,
	  b.dr_code AS invoice_code,
	  to_char(b.dr_issued_date, 'dd-Mon-yy') AS invoice_issue_date,
	  c.cus_code AS ship_to,
	  c.cus_full_name AS ship_to_name,
	  bi.drit_qty AS qty,
	  null AS unit_price,
	  null AS amount,
	  null AS amount_vat,
	  null AS grand_total,
	  b.dr_issued_date AS invoice_date,
	  'billing' AS invoice_condition,
	  'billing' AS invoice_layout_general,
	  'billing' AS invoice_layout_qty,
	  'billing' AS invoice_layout_amount,
	  CASE 
		WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'idc' THEN 'idc_'||b.dr_code
		WHEN b.dr_ordered_by = 2 AND '".$db[$i]."' = 'idc' THEN 'mep_'||b.dr_code
		WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'med' THEN 'med_'||b.dr_code
	  END AS invoice_source,
	  CASE
		WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.dr_dept = 'A' THEN 'http://192.168.0.88/idc/apotik/billing/revise_billing.php?_code='||dr_code
		WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.dr_dept = 'D' THEN 'http://192.168.0.88/idc/dealer/billing/revise_billing.php?_code='||dr_code
		WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.dr_dept = 'H' THEN 'http://192.168.0.88/idc/hospital/billing/revise_billing.php?_code='||dr_code
		WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.dr_dept = 'M' THEN 'http://192.168.0.88/idc/marketing/billing/revise_billing.php?_code='||dr_code
		WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.dr_dept = 'P' THEN 'http://192.168.0.88/idc/pharmaceutical/billing/revise_billing.php?_code='||dr_code
		WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'idc' AND b.dr_dept = 'T' THEN 'http://192.168.0.88/idc/tender/billing/revise_billing.php?_code='||dr_code
		WHEN b.dr_ordered_by = 2 AND '".$db[$i]."' = 'mep' AND b.dr_dept = 'A' THEN 'http://192.168.0.88/mep/apotik/billing/revise_billing.php?_code='||dr_code
		WHEN b.dr_ordered_by = 2 AND '".$db[$i]."' = 'mep' AND b.dr_dept = 'D' THEN 'http://192.168.0.88/mep/dealer/billing/revise_billing.php?_code='||dr_code
		WHEN b.dr_ordered_by = 2 AND '".$db[$i]."' = 'mep' AND b.dr_dept = 'H' THEN 'http://192.168.0.88/mep/hospital/billing/revise_billing.php?_code='||dr_code
		WHEN b.dr_ordered_by = 2 AND '".$db[$i]."' = 'mep' AND b.dr_dept = 'M' THEN 'http://192.168.0.88/mep/marketing/billing/revise_billing.php?_code='||dr_code
		WHEN b.dr_ordered_by = 2 AND '".$db[$i]."' = 'mep' AND b.dr_dept = 'P' THEN 'http://192.168.0.88/mep/pharmaceutical/billing/revise_billing.php?_code='||dr_code
		WHEN b.dr_ordered_by = 2 AND '".$db[$i]."' = 'mep' AND b.dr_dept = 'T' THEN 'http://192.168.0.88/mep/tender/billing/revise_billing.php?_code='||dr_code
		WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.dr_dept = 'A' THEN 'http://192.168.0.88/med/apotik/billing/revise_billing.php?_code='||dr_code
		WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.dr_dept = 'D' THEN 'http://192.168.0.88/med/dealer/billing/revise_billing.php?_code='||dr_code
		WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.dr_dept = 'H' THEN 'http://192.168.0.88/med/hospital/billing/revise_billing.php?_code='||dr_code
		WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.dr_dept = 'M' THEN 'http://192.168.0.88/med/marketing/billing/revise_billing.php?_code='||dr_code
		WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.dr_dept = 'P' THEN 'http://192.168.0.88/med/pharmaceutical/billing/revise_billing.php?_code='||dr_code
		WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'med' AND b.dr_dept = 'T' THEN 'http://192.168.0.88/med/tender/billing/revise_billing.php?_code='||dr_code	  
	  END AS go_page
	FROM
	  ".$db[$i]."_tb_customer AS c
	  JOIN ".$db[$i]."_tb_dr AS b ON c.cus_code = b.dr_ship_to
	  JOIN ".$db[$i]."_tb_dr_item AS bi USING(dr_code) 
	  JOIN ".$db[$i]."_tb_item AS it USING(it_code)
	  JOIN ".$db[$i]."_tb_item_cat AS icat USING(icat_midx)
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

$sql .= " ORDER BY ship_to, icat_pidx, icat_midx, it_code, invoice_date, invoice_code";
/*
echo "<pre>";
var_dump($sql);
echo "</pre>";
*/
// raw data
$rd		= array();
$rdIdx	= 0;
$cache	= array("","","","");
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'],				//0
		$col['it_code'],				//1
		$col['it_model_no'],			//2
		$col['it_idx'],					//3
		$col['invoice_code'],			//4
		$col['invoice_issue_date'],		//5
		$col['ship_to'],				//6
		$col['ship_to_name'],			//7
		$col['unit_price'],				//8
		$col['qty'],					//9
		$col['amount'],					//10
		$col['amount_vat'],				//11
		$col['grand_total'],			//12
		$col['invoice_condition'],		//13
		$col['invoice_layout_general'],	//14
		$col['invoice_layout_qty'],		//15
		$col['invoice_layout_amount'],	//16
		$col['go_page'],				//17
		$col['invoice_source']			//18
	);

	//1st grouping
	if($cache[0] != $col['icat_midx']) {
		$cache[0] = $col['icat_midx'];
		$group0[$col['icat_midx']] = array();
	}

	if($cache[1] != $col['it_code']) {
		$cache[1] = $col['it_code'];
		$group0[$col['icat_midx']][$col['it_code']] = array();
	}

	if($cache[2] != $col['invoice_source']) {
		$cache[2] = $col['invoice_source'];
		$group0[$col['icat_midx']][$col['it_code']][$col['invoice_source']] = array();
	}

	if($cache[3] != $col['it_idx']) {
		$cache[3] = $col['it_idx'];
	}

	$group0[$col['icat_midx']][$col['it_code']][$col['invoice_source']][$col['it_idx']] = 1;
}
?>