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
$display_css['bill_tf_paid']		= "background-color:lightgrey;color:purple";
$display_css['turn_counted'] 		= "color:#EE5811";
$display_css['turn_uncounted'] 		= "color:#9D9DA1";

//SET WHERE PARAMETER
$tmp_bill	= array();
$tmp_turn	= array();
$tmp_dr		= array();
if($department=='A') $t_col = 5;
else $t_col = 4;

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp_bill[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_turn[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_dr[]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if(ZKP_FUNCTION == 'ALL') {
	if($_order_by != 'all'){
		$tmp_bill[]	= "bill_ordered_by = $_order_by";
		$tmp_turn[] = "turn_ordered_by = $_order_by";
		$tmp_dr[]	= "dr_ordered_by = $_order_by";
	}
} else {
	$tmp_bill[]	= "bill_ordered_by = ".$cboFilter[1][ZKP_FUNCTION][0][0];
	$tmp_turn[] = "turn_ordered_by = ".$cboFilter[1][ZKP_FUNCTION][0][0];
	$tmp_dr[]	= "dr_ordered_by = ".$cboFilter[1][ZKP_FUNCTION][0][0];
	$tmp_bill[] = ZKP_SQL."_isValidShowInvoice('".ZKP_FUNCTION."', bill_code,'billing')";
	$tmp_turn[] = ZKP_SQL."_isValidShowInvoice('".ZKP_FUNCTION."', turn_code,'billing_return')";
	$tmp_dr[]	= ZKP_SQL."_isValidShowInvoice('".ZKP_FUNCTION."', dr_code,'dr')";
}

if($_marketing != "all") {
	$tmp_bill[]	= "cus_responsibility_to = $_marketing";
	$tmp_turn[] = "cus_responsibility_to = $_marketing";
	$tmp_dr[]	= "cus_responsibility_to = $_marketing";
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

if ($some_date != "") {
	$tmp_bill[]	= "bill_inv_date = DATE '$some_date'";
	$tmp_turn[] = "turn_return_date = DATE '$some_date'";
	$tmp_dr[]	= "dr_issued_date = DATE '$some_date'";
} else {
	$tmp_bill[]	= "bill_inv_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
	$tmp_turn[] = "turn_return_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
	$tmp_dr[]	= "dr_issued_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
}

if($_vat == 'vat') {
	$tmp_bill[]	= "bill_vat > 0";
	$tmp_turn[]	= "turn_vat > 0";  
	$tmp_dr[]	= "dr_type_item = 1";
} else if($_vat == 'vat-IO') {
	$tmp_bill[]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
	$tmp_turn[] = "turn_vat > 0";  
	$tmp_dr[]	= "dr_type_item = 1"; 
} else if($_vat == 'vat-IP') {
	$tmp_bill[]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
	$tmp_turn[] = "turn_code is null"; 
	$tmp_dr[]	= "dr_code is null";  
} else if ($_vat == 'non') {
	$tmp_bill[]	= "bill_vat = 0";
	$tmp_turn[]	= "turn_vat = 0";
	$tmp_dr[]	= "dr_type_item = 2";
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

if($cboSearchType != '' && $txtSearch != '') {
	$type = array("byCity"=>"cus_city");
	$tmp_bill[]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
	$tmp_turn[]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
	$tmp_dr[]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
	$get[] = "$cboSearchType=$txtSearch";
}

$tmp_bill[]	= "bill_dept = '$department'";
$tmp_turn[]	= "turn_dept = '$department'";
$tmp_dr[]	= "dr_dept = '$department'";

$strWhereBilling = implode(" AND ", $tmp_bill);
$strWhereReturn	 = implode(" AND ", $tmp_turn);
$strWhereDR		 = implode(" AND ", $tmp_dr);

$sql_bill .= "
  trim(b.bill_ship_to) ||' - '||b.bill_ship_to_name AS ship_to,
  b.bill_code AS invoice_code,
  bi.biit_idx AS it_idx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  bi.biit_qty AS qty,
  TRUNC(bi.biit_unit_price * (1 - b.bill_discount/100),2) AS unit_price,  
  b.bill_inv_date AS invoice_date
FROM
 ".ZKP_SQL."_tb_customer AS c
 JOIN ".ZKP_SQL."_tb_billing AS b ON bill_ship_to = c.cus_code
 JOIN ".ZKP_SQL."_tb_billing_item AS bi USING(bill_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE " . $strWhereBilling ;

$sql = "$sql_bill ORDER BY cug_name, ship_to, invoice_date, invoice_code, biit_unit_price DESC, it_code";

echo "<pre>";
echo $sql;
echo "</pre>";

// raw data
$rd		= array();
$rdIdx	= 0;
$cache	= array("","","","");
$group0	= array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['cug_name'],				//0
		$col['ship_to'],				//1
		$col['ship_to_name'],			//2
		$col['invoice_code'], 			//3
		$col['invoice_date'], 	//4
		$col['invoice_due_date'],		//5
		$col['invoice_sales_from'],		//6
		$col['invoice_sales_to'],		//7
		$col['freight_charge'],			//8
		$col['discount'],				//9
		$col['it_idx'],					//10
		$col['it_code'], 				//11
		$col['it_model_no'],			//12
		$col['unit_price'], 			//13
		$col['qty'],					//14
		$col['amount'],					//15
		$col['vat'],					//16
		$col['amount']+$col['vat'],		//17
		$col['grand_total'],			//18
		$col['invoice_condition'],		//19
		$col['invoice_layout_general'],			//20
		$col['invoice_layout_qty'],				//21
		$col['invoice_layout_amount'],			//22
		$col['go_page']					//23
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
if($department=='A') {
	print <<<END
	<table width="50%" class="table_f">
		<tr>
			<th>SHIP TO<br />CUSTOMER</th>
			<th width="10%">INV. NO</th>
			<th width="7%">INV. DATE</th>
			<th width="13%">MODEL NO</th>
			<th width="7%">UNIT PRICE<br/>(Rp)</th>
			<th width="4%">QTY<br>(EA)</th>
			<th width="7%">AMOUNT<br>-VAT (Rp)</th>
			<th width="7%">VAT<br>(Rp)</th>
			<th width="7%">AMOUNT<br>+VAT</th>
			<th width="7%">AMOUNT<br>+FRT/VAT</th>
		</tr>\n
END;
} else {
	print <<<END
	<table width="70%" class="table_f">
		<tr>
			<th>SHIP TO<br />CUSTOMER</th>
			<th width="20%">INV. NO</th>
			<th width="15%">INV. DATE</th>
			<th width="25%">MODEL NO</th>
			<th width="10%">UNIT PRICE<br/>(Rp)</th>
			<th width="5%">QTY<br>(EA)</th>
		</tr>\n
END;
}

	print "<tr>\n";

	$cus_total = array(0,0,0,0,0,0);
	$print_tr_1 = 0;
	//CUSTOMER
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += count($group2);

		if($print_tr_1++ > 0) print "<tr>\n";
		cell($rd[$rdIdx][1], ' valign="top" rowspan="'.$rowSpan.'"');	//Customer ship to

		$print_tr_2 = 0;
		//ORDER
		foreach($group2 as $total3 => $group3) {
			$rowSpan = 0;
			array_walk_recursive($group3, 'getRowSpan');
			$rowSpan +=1;

			if($print_tr_2++ > 0) print "<tr>\n";
			cell_link($rd[$rdIdx][3], ' style="'.$display_css[$rd[$rdIdx][20]].'" align="center" valign="top" rowspan="'.$rowSpan.'"',
				' href="'.$rd[$rdIdx][23].'" style="'.$display_css[$rd[$rdIdx][20]].'"');												//Invoice no
			cell($rd[$rdIdx][4], ' style="'.$display_css[$rd[$rdIdx][20]].'" valign="top" align="center" rowspan="'.$rowSpan.'"');		//Invoice document date

			//freight, qty, amount, vat, amount_vat, grand_total 
			$inv_total	= array($rd[$rdIdx][8],0,0,0,0,$rd[$rdIdx][18]);
			$print_tr_3 = 0;

			//ITEM
			foreach($group3 as $group4) {
				if($print_tr_3++ > 0) print "<tr>\n";
				cell($rd[$rdIdx][12], ' style="'.$display_css[$rd[$rdIdx][20]].'"');	//Model No
				cell(number_format((double)$rd[$rdIdx][13]), ' style="'.$display_css[$rd[$rdIdx][22]].'" align="right"');	//Unit price
				cell(number_format((double)$rd[$rdIdx][14]), ' style="'.$display_css[$rd[$rdIdx][21]].'" align="right"');	//Qty
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
	cell("<b>$total1</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cus_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$g_total[0] += $cus_total[0];
	$g_total[1] += $cus_total[1];
	$g_total[2] += $cus_total[2];
	$g_total[3] += $cus_total[3];
	$g_total[4] += $cus_total[4];
	$g_total[5] += $cus_total[5];
}
