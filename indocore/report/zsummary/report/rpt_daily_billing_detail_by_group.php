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
$display_css['bill_before_due'] 	= "color:#333333";
$display_css['bill_over_due'] 		= "background-color:lightyellow; color:red";
$display_css['bill_paid'] 			= "background-color:lightgrey; color:#333333";
$display_css['bill_before_due_tf']	= "color:purple";
$display_css['bill_over_due_tf']	= "background-color:lightyellow;color:purple";
$display_css['bill_paid_tf']		= "background-color:lightgrey;color:purple";
$display_css['turn_counted'] 		= "color:EE5811";
$display_css['turn_uncounted'] 		= "color:#9D9DA1";

//SET WHERE PARAMETER
$tmp_bill	= array();
$tmp_turn	= array();
$tmp_dr		= array();

if(ZKP_URL == 'ALL') {
	if($_order_by != 'all'){
		$tmp_bill[]	= "bill_ordered_by = $_order_by";
		$tmp_turn[] = "turn_ordered_by = $_order_by";
		$tmp_dr[]	= "dr_ordered_by = $_order_by";
	}
} else {
	$tmp_bill[]	= "bill_ordered_by = ".$cboFilter[1][ZKP_URL][0][0];
	$tmp_turn[] = "turn_ordered_by = ".$cboFilter[1][ZKP_URL][0][0];
	$tmp_dr[]	= "dr_ordered_by = ".$cboFilter[1][ZKP_URL][0][0];
	$tmp_bill[] = ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill_code,'billing')";
	$tmp_turn[] = ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', turn_code,'billing_return')";
	$tmp_dr[]	= ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', dr_code,'dr')";
}

if ($_cug_code != 'all') {
	$tmp_bill[]	= "bill_ship_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp_turn[]	= "turn_ship_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp_dr[]	= "dr_ship_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$sql_bill 	= " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
	$sql_return = " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
	$sql_dr		= " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
} else {
	$sql_bill = "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = bill_ship_to),
		'Others') AS cug_name,";
	$sql_return = "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = turn_ship_to),
		'Others') AS cug_name,";
	$sql_dr = "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = dr_ship_to),
		'Others') AS cug_name,";
}

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp_bill[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_turn[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_dr[]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if($_filter_doc == 'I'){
	$tmp_turn[] = "turn_code = ''";
	$tmp_dr[]	= "dr_code = ''";
} else if($_filter_doc == 'R') {
	$tmp_bill[]	= "bill_code = ''";
	$tmp_dr[]	= "dr_code = ''";
} else if($_filter_doc == 'DR') {
	$tmp_bill[]	= "bill_code = ''";
	$tmp_turn[] = "turn_code = ''";
}

if($_dept != 'all') {
	$tmp_bill[]	= "bill_dept = '$_dept'";
	$tmp_turn[]	= "turn_dept = '$_dept'";
	$tmp_dr[]	= "dr_dept = '$_dept'";
}

if($_vat == 'vat') {
	$tmp_bill[]	= "bill_vat > 0";
	$tmp_turn[]	= "turn_vat > 0";  
	$tmp_dr[]	= "dr_type_item = 1";
} else if($_vat == 'vat-IO') {
	$tmp_bill[]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
	$tmp_turn[]	= "turn_vat > 0";  
	$tmp_dr[]	= "dr_type_item = 1";
}else if($_vat == 'vat-IP') {
	$tmp_bill[]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
	$tmp_turn[]	= "turn_code = ''";  
	$tmp_dr[]	= "dr_code is null";
} else if ($_vat == 'non') {
	$tmp_bill[]	= "bill_vat = 0";
	$tmp_turn[]	= "turn_vat = 0";
	$tmp_dr[]	= "dr_type_item = 2";
}

if($_marketing != "all") {
	$tmp_bill[]	= "cus_responsibility_to = $_marketing";
	$tmp_turn[] = "cus_responsibility_to = $_marketing";
	$tmp_dr[]	= "cus_responsibility_to = $_marketing";
}

if($cboSearchType != '' && $txtSearch != '') {
	$type = array("byCity"=>"cus_city");
	$tmp_bill[] = $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
	$tmp_turn[] = $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
	$tmp_dr[] = $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
	$get[] = "$cboSearchType=$txtSearch";
}

if ($some_date != "") {
	$tmp_bill[]	= "bill_inv_date = DATE '$some_date'";
	$tmp_turn[] = "turn_return_date = DATE '$some_date'";
	$tmp_dr[]	= "dr_issued_date = DATE '$some_date'";
} else {
	$tmp_bill[]	= "bill_inv_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
	$tmp_turn[] = "turn_return_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
	$tmp_dr[]	= "dr_issued_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
}

if ($_paper == '0') {
	$tmp_bill[]	= "bill_type_invoice = '0'";
	$tmp_turn[]	= "turn_paper = 0";
} else if ($_paper == '1') {
	$tmp_bill[]	= "bill_type_invoice = '1'";
	$tmp_turn[]	= "turn_paper = 1";
	$tmp_dr[]	= "dr_code is null";
} else if ($_paper == 'A') {
	$tmp_bill[]	= "bill_paper_format = 'A'";
	$tmp_turn[]	= "turn_paper = 0";
	$tmp_dr[]	= "dr_code is null";
} else if ($_paper == 'B') {
	$tmp_bill[]	= "bill_paper_format = 'B'";
	$tmp_turn[]	= "turn_paper = 1";
	$tmp_dr[]	= "dr_code is null";
}

$strWhereBilling = implode(" AND ", $tmp_bill);
$strWhereReturn	 = implode(" AND ", $tmp_turn);
$strWhereDR	 	 = implode(" AND ", $tmp_dr);

$sql_bill .= "
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
	WHEN b.bill_dept = 'A' THEN '../../apotik/billing/revise_billing.php?_code='||bill_code
	WHEN b.bill_dept = 'D' THEN '../../dealer/billing/revise_billing.php?_code='||bill_code
	WHEN b.bill_dept = 'H' THEN '../../hospital/billing/revise_billing.php?_code='||bill_code
	WHEN b.bill_dept = 'M' THEN '../../marketing/billing/revise_billing.php?_code='||bill_code
	WHEN b.bill_dept = 'P' THEN '../../pharmaceutical/billing/revise_billing.php?_code='||bill_code
	WHEN b.bill_dept = 'T' THEN '../../tender/billing/revise_billing.php?_code='||bill_code
  END AS go_page
FROM
 ".ZKP_SQL."_tb_customer AS c
 JOIN ".ZKP_SQL."_tb_billing AS b ON bill_ship_to = c.cus_code
 JOIN ".ZKP_SQL."_tb_billing_item AS bi USING(bill_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE " . $strWhereBilling ;

$sql_return .= "
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
	WHEN t.turn_dept = 'A' THEN '../../apotik/billing/revise_return.php?_code='||turn_code
	WHEN t.turn_dept = 'D' THEN '../../dealer/billing/revise_return.php?_code='||turn_code
	WHEN t.turn_dept = 'H' THEN '../../hospital/billing/revise_return.php?_code='||turn_code
	WHEN t.turn_dept = 'M' THEN '../../marketing/billing/revise_return.php?_code='||turn_code
	WHEN t.turn_dept = 'P' THEN '../../pharmaceutical/billing/revise_return.php?_code='||turn_code
	WHEN t.turn_dept = 'T' THEN '../../tender/billing/revise_return.php?_code='||turn_code
  END AS go_page
FROM
 ".ZKP_SQL."_tb_customer AS c
 JOIN ".ZKP_SQL."_tb_return AS t ON t.turn_ship_to = c.cus_code
 JOIN ".ZKP_SQL."_tb_return_item AS ti USING(turn_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE $strWhereReturn" ;

$sql_dr .= "
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
	WHEN b.dr_dept = 'A' THEN '../../apotik/_other/revise_dr.php?_code='||dr_code
	WHEN b.dr_dept = 'D' THEN '../../dealer/_other/revise_dr.php?_code='||dr_code
	WHEN b.dr_dept = 'H' THEN '../../hospital/_other/revise_dr.php?_code='||dr_code
	WHEN b.dr_dept = 'P' THEN '../../pharmaceutical/_other/revise_dr.php?_code='||dr_code
  END AS go_page
FROM
 ".ZKP_SQL."_tb_customer AS c
 JOIN ".ZKP_SQL."_tb_dr AS b ON dr_ship_to = c.cus_code
 JOIN ".ZKP_SQL."_tb_dr_item AS bi USING(dr_code)
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE " . $strWhereDR ;

$sql = "$sql_bill UNION $sql_return UNION $sql_dr ORDER BY cug_name, ship_to, invoice_date, invoice_code, it_code";

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
		$col['go_page']						//23
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

	if($cache[2] != $col['invoice_code']) {
		$cache[2] = $col['invoice_code'];
		$group0[$col['cug_name']][$col['ship_to']][$col['invoice_code']] = array();
	}

	if($cache[3] != $col['it_idx']) {
		$cache[3] = $col['it_idx'];
	}

	$group0[$col['cug_name']][$col['ship_to']][$col['invoice_code']][$col['it_idx']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL

$g_total = array(0,0,0,0,0,0);  // freight, qty, vat, amount, amount+vat, amount+vat+frt

//GROUP
foreach ($group0 as $total1 => $group1) {

	echo "<span class=\"comment\"><b> PUSAT: ". $total1. "</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th>SHIP TO<br />CUSTOMER</th>
			<th width="10%">INV. NO</th>
			<th width="7%">INV. DATE</th>
			<th width="7%">DUE DATE</th>
			<th width="7%">FREIGHT</th>
			<th width="3%">DISC<br />(%)</th>
			<th width="13%">MODEL NO</th>
			<th width="7%">UNIT PRICE<br/>(Rp)</th>
			<th width="4%">QTY<br>(EA)</th>
			<th width="7%">AMOUNT<br>-VAT (Rp)</th>
			<th width="7%">VAT<br>(Rp)</th>
			<th width="7%">AMOUNT<br>+VAT</th>
			<th width="7%">AMOUNT<br>+FRT/VAT</th>
		</tr>\n
END;
	print "<tr>\n";

	$cus_total = array(0,0,0,0,0,0);
	$print_tr_1 = 0;
	//CUSTOMER
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += count($group2);

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".$rd[$rdIdx][1]."]<br/>".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');	//Customer ship to

		$print_tr_2 = 0;
		//ORDER
		foreach($group2 as $total3 => $group3) {
			$rowSpan = 0;
			array_walk_recursive($group3, 'getRowSpan');
			$rowSpan +=1;

			if($print_tr_2++ > 0) print "<tr>\n";
			cell_link($rd[$rdIdx][3], ' style="'.$display_css[$rd[$rdIdx][20]].'" align="center" valign="top" rowspan="'.$rowSpan.'"',
				' href="'.$rd[$rdIdx][23].'" style="'.$display_css[$rd[$rdIdx][20]].'"');																							//Invoice no
			cell($rd[$rdIdx][4], ' style="'.$display_css[$rd[$rdIdx][20]].'" valign="top" align="center" rowspan="'.$rowSpan.'"');		//Invoice document date
			cell($rd[$rdIdx][5], ' style="'.$display_css[$rd[$rdIdx][20]].'" valign="top" align="center" rowspan="'.$rowSpan.'"');		//Invoice due date
			cell(number_format((double)$rd[$rdIdx][8]),' style="'. $display_css[$rd[$rdIdx][22]] .'" valign="top" align="right" rowspan="'.$rowSpan.'"'); 			//Freight charge
			cell($rd[$rdIdx][9],' style="'.$display_css[$rd[$rdIdx][22]].'" valign="top" align="center" rowspan="'.$rowSpan.'"'); 		//Discout

			//freight, qty, amount, vat, amount_vat, grand_total 
			$inv_total	= array($rd[$rdIdx][8],0,0,0,0,$rd[$rdIdx][18]);
			$print_tr_3 = 0;

			//ITEM
			foreach($group3 as $group4) {
				if($print_tr_3++ > 0) print "<tr>\n";
				cell($rd[$rdIdx][12], ' style="'.$display_css[$rd[$rdIdx][20]].'"');	//Model No
				cell(number_format((double)$rd[$rdIdx][13]), ' style="'.$display_css[$rd[$rdIdx][22]].'" align="right"');	//Unit price
				cell(number_format((double)$rd[$rdIdx][14]), ' style="'.$display_css[$rd[$rdIdx][21]].'" align="right"');	//Qty
				cell(number_format((double)$rd[$rdIdx][15]), ' style="'.$display_css[$rd[$rdIdx][22]].'" align="right"'); 	//Amount
				cell(number_format((double)$rd[$rdIdx][16]), ' style="'.$display_css[$rd[$rdIdx][22]].'" align="right"');	//Vat
				cell(number_format((double)$rd[$rdIdx][17]), ' style="'.$display_css[$rd[$rdIdx][22]].'" align="right"');	//Amount + vat
				cell("&nbsp;", ' style="'.$display_css[$rd[$rdIdx][20]].'"');
				print "</tr>\n";

				//Count invoice amount
				$inv_total[0] = $inv_total[0];
				$inv_total[1] += $rd[$rdIdx][14];
				$inv_total[2] += $rd[$rdIdx][15];
				$inv_total[3] += $rd[$rdIdx][16];
				$inv_total[4] += $rd[$rdIdx][17];

				$css_general	= $rd[$rdIdx][20];
				$css_qty		= $rd[$rdIdx][21];
				$css_amount		= $rd[$rdIdx][22];
				$rdIdx++;
			}

			print "<tr>\n";
			cell("INVOICE TOTAL", ' style="'.$display_css[$css_general].'" colspan="2" align="right" style="color:darkblue;"');
			cell(number_format((double)$inv_total[1]), ' style="'.$display_css[$css_qty].'" align="right" style="color:darkblue;"');
			cell(number_format((double)$inv_total[2]), ' style="'.$display_css[$css_amount].'" align="right" style="color:darkblue;"');
			cell(number_format((double)$inv_total[3]), ' style="'.$display_css[$css_amount].'" align="right" style="color:darkblue;"');
			cell(number_format((double)$inv_total[4]), ' style="'.$display_css[$css_amount].'" align="right" style="color:darkblue;"');
			cell(number_format((double)$inv_total[5]), ' style="'.$display_css[$css_amount].'" align="right" style="color:darkblue;"');
			print "</tr>\n";

			//nilai dihitung atau tidak dihitung
			//qty
			if($css_qty == 'turn_counted') {	$cus_total[1] += $inv_total[1]*-1; }
			else {								$cus_total[1] += $inv_total[1]; }

			//amount
			if($css_amount == 'turn_counted') {				//return
				$cus_total[0] += $inv_total[0]*-1;
				$cus_total[2] += $inv_total[2]*-1;
				$cus_total[3] += $inv_total[3]*-1;
				$cus_total[4] += $inv_total[4]*-1;
				$cus_total[5] += $inv_total[5]*-1;
			} else if($css_amount != 'turn_uncounted') {	//billing
				$cus_total[0] += $inv_total[0];
				$cus_total[2] += $inv_total[2];
				$cus_total[3] += $inv_total[3];
				$cus_total[4] += $inv_total[4];
				$cus_total[5] += $inv_total[5];
			}
		}
	}
	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="4" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cus_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="background-color:lightyellow" colspan="3"');
	cell(number_format((double)$cus_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cus_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cus_total[3]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cus_total[4]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cus_total[5]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$g_total[0] += $cus_total[0];
	$g_total[1] += $cus_total[1];
	$g_total[2] += $cus_total[2];
	$g_total[3] += $cus_total[3];
	$g_total[4] += $cus_total[4];
	$g_total[5] += $cus_total[5];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th>SHIP TO<br />CUSTOMER</th>
			<th width="10%">INV. NO</th>
			<th width="7%">INV. DATE</th>
			<th width="7%">DUE DATE</th>
			<th width="7%">FREIGHT</th>
			<th width="3%">DISC<br />(%)</th>
			<th>MODEL NO</th>
			<th width="7%">UNIT PRICE<br/>(Rp)</th>
			<th width="4%">QTY<br>(EA)</th>
			<th width="7%">AMOUNT<br>-VAT (Rp)</th>
			<th width="7%">VAT<br>(Rp)</th>
			<th width="7%">AMOUNT<br>+VAT</th>
			<th width="7%">AMOUNT<br>+FRT/VAT</th>
		</tr>\n
END;

	print "<tr>\n";
	cell("<b>GRAND TOTAL</b>", ' colspan="4" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$g_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="background-color:lightyellow"');
	cell("&nbsp", ' style="background-color:lightyellow"');
	cell("&nbsp", ' style="background-color:lightyellow"');
	cell(number_format((double)$g_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$g_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$g_total[3]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$g_total[4]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$g_total[5]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
print "</table>\n";
?>