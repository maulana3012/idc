<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim

*
* $_po_date : Inquire Date
*
*/
//Variable Color
$display_css['billing'] 		= "color:#333333";
$display_css['turn_counted'] 	= "color:EE5811";
$display_css['turn_uncounted'] 	= "color:#9D9DA1";

//SET WHERE PARAMETER
$tmp_bill	= array();
$tmp_turn	= array();
$tmp_dr		= array();

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

if ($some_date != "") {
	$tmp_bill[]	= "bill_inv_date = DATE '$some_date'";
	$tmp_turn[] = "turn_return_date = DATE '$some_date'";
	$tmp_dr[]	= "dr_issued_date = DATE '$some_date'";
} else {
	$tmp_bill[] = "bill_inv_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
	$tmp_turn[] = "turn_return_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
	$tmp_dr[]	= "dr_issued_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
}

if($_vat == 'vat') {
	$tmp_bill[]	= "bill_vat > 0";
	$tmp_turn[] = "turn_vat > 0";  
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
	$tmp_bill[] = "bill_vat = 0";
	$tmp_turn[] = "turn_vat = 0";
	$tmp_dr[]	= "dr_type_item = 2";
}

if ($_paper == '0') {
	$tmp_bill[]	= "bill_type_invoice = '0'";
	$tmp_turn[] = "turn_paper = 0";
} else if ($_paper == '1') {
	$tmp_bill[] = "bill_type_invoice = '1'";
	$tmp_turn[] = "turn_paper = 1";
	$tmp_dr[]	= "dr_code is null";
} else if ($_paper == 'A') {
	$tmp_bill[] = "bill_paper_format = 'A'";
	$tmp_turn[] = "turn_paper = 0";
	$tmp_dr[]	= "dr_code is null";
} else if ($_paper == 'B') {
	$tmp_bill[] = "bill_paper_format = 'B'";
	$tmp_turn[] = "turn_paper = 1";
	$tmp_dr[]	= "dr_code is null";
}

$tmp_bill[] = "c.cus_code = '$_cus_code' AND b.bill_dept= '$department'";
$tmp_turn[] = "c.cus_code = '$_cus_code' AND t.turn_dept= '$department'";
$tmp_dr[]	= "c.cus_code = '$_cus_code' AND b.dr_dept= '$department'";
/*
echo "<pre>";
var_dump($tmp_bill, $tmp_turn);
echo "</pre>";
*/
$strWhereBilling	= implode(" AND ", $tmp_bill);
$strWhereReturn		= implode(" AND ", $tmp_turn);
$strWhereDR			= implode(" AND ", $tmp_dr);

$sql_bill = "
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
  '../billing/revise_billing.php?_code='||bill_code AS go_page
FROM
  ".ZKP_SQL."_tb_customer AS c
  JOIN ".ZKP_SQL."_tb_billing AS b ON c.cus_code = b.bill_ship_to
  JOIN ".ZKP_SQL."_tb_billing_item AS bi USING(bill_code) 
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE ". $strWhereBilling;

$sql_return = "
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
  '../billing/revise_return.php?_code='||turn_code AS go_page
FROM
  ".ZKP_SQL."_tb_customer AS c
  JOIN ".ZKP_SQL."_tb_return AS t ON c.cus_code = t.turn_ship_to
  JOIN ".ZKP_SQL."_tb_return_item AS ti USING(turn_code) 
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE ". $strWhereReturn;

$sql_dr = "
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
  '../other/revise_dr.php?_code='||dr_code AS go_page
FROM
  ".ZKP_SQL."_tb_customer AS c
  JOIN ".ZKP_SQL."_tb_dr AS b ON c.cus_code = b.dr_ship_to
  JOIN ".ZKP_SQL."_tb_dr_item AS bi USING(dr_code) 
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE ". $strWhereDR;

$sql = "$sql_bill UNION $sql_return UNION $sql_dr ORDER BY ship_to, icat_pidx, icat_midx, it_code, invoice_date, invoice_code";

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
		$col['go_page']					//17
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

	if($cache[2] != $col['invoice_code']) {
		$cache[2] = $col['invoice_code'];
		$group0[$col['icat_midx']][$col['it_code']][$col['invoice_code']] = array();
	}

	if($cache[3] != $col['it_idx']) {
		$cache[3] = $col['it_idx'];
	}

	$group0[$col['icat_midx']][$col['it_code']][$col['invoice_code']][$col['it_idx']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$grand_total = array(0,0,0,0);

//GROUP
foreach ($group0 as $total1 => $group1) {

	//get category path from current icat_midx.
	$path = executeSP(ZKP_SQL."_getCategoryPath", $rd[$rdIdx][0]);
	eval(html_entity_decode($path[0]));	
	$path = array_reverse($path);
	$total1 = $path[1][4]." > ".$path[2][4]." > ".$path[3][4] ;

	echo "<span class=\"comment\"><b> CATEGORY : ". $total1. "</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th>MODEL NO</th>
			<th width="20%">INV. NO</th>
			<th width="10%">INV DATE</th>
			<th width="10%">@PRICE<br/>(Rp)</th>
			<th width="5%">QTY<br>(EA)</th>
			<th width="10%">AMOUNT<br>(Rp)</th>
			<th width="8%">VAT<br>(Rp)</th>
			<th width="12%">AMOUNT<br>+VAT(Rp)</th>
		</tr>\n
END;
	$cat_total = array(0,0,0,0);
	$print_tr_1 = 0;

	print "<tr>\n";
	//MODEL
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[". trim($rd[$rdIdx][1]) . "] ".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');	//Model no

		$model_total = array(0,0,0,0);
		$print_tr_2	= 0;
		//ORDER
		foreach($group2 as $total3 => $group3) {
			$rowSpan = 0;
			array_walk_recursive($group3, 'getRowSpan');

			if($print_tr_2++ > 0) print "<tr>\n";
			cell_link($rd[$rdIdx][4], ' style="'.$display_css[$rd[$rdIdx][14]].'" align="center" valign="top" rowspan="'.$rowSpan.'"', 
				' href="'.$rd[$rdIdx][17].'" style="'.$display_css[$rd[$rdIdx][14]].'" ');	//Invoice no
			cell($rd[$rdIdx][5], ' style="'.$display_css[$rd[$rdIdx][14]].'"  align="center" valign="top" rowspan="'.$rowSpan.'"');	//Invoice date

			$print_tr_3 = 0;
			//ITEM
			foreach($group3 as $total4 => $group4) {
				if($print_tr_3++ > 0) print "<tr>\n";
				cell(number_format((double)$rd[$rdIdx][8]), ' style="'.$display_css[$rd[$rdIdx][16]].'" align="right"');	//unit price
				cell(number_format((double)$rd[$rdIdx][9]), ' style="'.$display_css[$rd[$rdIdx][15]].'" align="right"');	//qty
				cell(number_format((double)$rd[$rdIdx][10]), ' style="'.$display_css[$rd[$rdIdx][16]].'" align="right"');	//amount
				cell(number_format((double)$rd[$rdIdx][11]), ' style="'.$display_css[$rd[$rdIdx][16]].'" align="right"');	//vat
				cell(number_format((double)$rd[$rdIdx][12]), ' style="'.$display_css[$rd[$rdIdx][16]].'" align="right"');	//amount + vat
				print "</tr>\n";

				//nilai positif atau negatif
				//qty
				if($rd[$rdIdx][15] == 'turn_counted') {	$model_total[0] += $rd[$rdIdx][9]*-1; }
				else {									$model_total[0] += $rd[$rdIdx][9]; }

				//amount
				if($rd[$rdIdx][16] == 'turn_counted') {				//return
					$model_total[1] += $rd[$rdIdx][10]*-1;
					$model_total[2] += $rd[$rdIdx][11]*-1;
					$model_total[3] += $rd[$rdIdx][12]*-1;
				} else if($rd[$rdIdx][16] != 'turn_uncounted') {	//billing
					$model_total[1] += $rd[$rdIdx][10];
					$model_total[2] += $rd[$rdIdx][11];
					$model_total[3] += $rd[$rdIdx][12];
				}

				$model_no	= $rd[$rdIdx][2];
				$rdIdx++;
			}
		}

		print "<tr>\n";
		cell($model_no, ' colspan="3" align="right" style="color:darkblue"');
		cell(number_format((double)$model_total[0]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$model_total[1]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$model_total[2]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$model_total[3]), ' align="right" style="color:darkblue"');
		print "</tr>\n";

		$cat_total[0] += $model_total[0];
		$cat_total[1] += $model_total[1];
		$cat_total[2] += $model_total[2];
		$cat_total[3] += $model_total[3];
	}
	
	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="4" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cat_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cat_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cat_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cat_total[3]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";
	
	$grand_total[0] += $cat_total[0];
	$grand_total[1] += $cat_total[1];
	$grand_total[2] += $cat_total[2];
	$grand_total[3] += $cat_total[3];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_f">
		<tr>
			<th width="15%">MODEL NO</th>
			<th width="13%">INV. NO</th>
			<th width="7%">INV DATE</th>
			<th width="7%">@PRICE<br/>(Rp)</th>
			<th width="5%">QTY<br>(EA)</th>
			<th width="7%">AMOUNT<br>(Rp)</th>
			<th width="7%">VAT<br>(Rp)</th>
			<th width="7%">AMOUNT<br>+VAT(Rp)</th>
		</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="4" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[3]), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>