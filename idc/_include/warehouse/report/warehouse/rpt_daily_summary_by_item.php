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
$tmp_bill0		= array();
$tmp_bill1		= array();
$tmp_ord		= array();
$tmp_deli		= array();
$tmp_turn_bill0	= array();
$tmp_turn_bill1	= array();
$tmp_turn_ord	= array();
$tmp_turn_deli	= array();

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$strWhereItem = "WHERE a.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if ($_filter_doc == "I") {
	$tmp_turn_bill0[]= "turn_code = NULL";
	$tmp_turn_bill1[]= "turn_code = NULL";
	$tmp_turn_ord[]	= "ord_code = NULL";
	$tmp_turn_deli[]= "reor_code = NULL";
} else if ($_filter_doc == "R") {
	$tmp_bill0[]		= "bill_code = NULL";
	$tmp_bill1[]		= "bill_code = NULL";
	$tmp_ord[]		= "ord_code = NULL";
	$tmp_deli[]		= "deli_idx = NULL";
}

if($_order_by == 1){
	$tmp_bill0[]   		= "bill_ordered_by = 1";
	$tmp_bill1[]   		= "bill_ordered_by = 1";
	$tmp_turn_bill0[]	= "turn_ordered_by = 1";
	$tmp_turn_bill1[]	= "turn_ordered_by = 1";
} else if($_order_by == 2) {
	$tmp_bill0[]   		= "bill_ordered_by = 2";
	$tmp_bill1[]   		= "bill_ordered_by = 2";
	$tmp_turn_bill0[]	= "turn_ordered_by = 2";
	$tmp_turn_bill1[]	= "turn_ordered_by = 2";
	$tmp_ord[]			= "ord_code	IS NULL";
	$tmp_deli[]			= "deli_idx	IS NULL";
	$tmp_turn_ord[]		= "reor_code IS NULL";
	$tmp_turn_deli[]	= "reor_code IS NULL";
}

if ($some_date == "" && $period_from == "") {
	$tmp_bill0[]		= "bill_cfm_wh_date	= null";
	$tmp_bill1[]		= "bill_inv_date	= null";
	$tmp_ord[]			= "ord_cfm_wh_date	= null";
	$tmp_deli[]			= "deli_date		= null";
	$tmp_turn_bill0[]	= "turn_cfm_wh_date = null";
	$tmp_turn_bill1[]	= "turn_return_date = null";
	$tmp_turn_ord[]		= "reor_cfm_wh_date = null";
	$tmp_turn_deli[]	= "reor_po_date		= null";
} else if ($some_date != "") {
	$tmp_bill0[]		= "bill_cfm_wh_date	= date '$some_date'";
	$tmp_bill1[]		= "bill_inv_date	= date '$some_date'";
	$tmp_ord[]			= "ord_cfm_wh_date	= DATE '$some_date'";
	$tmp_deli[]			= "deli_date		= DATE '$some_date'";
	$tmp_turn_bill0[]	= "turn_cfm_wh_date = DATE '$some_date'";
	$tmp_turn_bill1[]	= "turn_return_date = DATE '$some_date'";
	$tmp_turn_ord[]		= "reor_cfm_wh_date = DATE '$some_date'";
	$tmp_turn_deli[]	= "reor_po_date = DATE '$some_date'";
} else {
	$tmp_bill0[]		= "bill_cfm_wh_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_bill1[]		= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_ord[]			= "ord_cfm_wh_date 	BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_deli[]			= "deli_date 		BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_turn_bill0[]	= "turn_cfm_wh_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_turn_bill1[]	= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_turn_ord[]		= "reor_cfm_wh_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_turn_deli[]	= "reor_po_date 	BETWEEN DATE '$period_from' AND '$period_to'";
}

if($_vat != 'all'){
	$tmp_ord[]			= "ord_code = NULL";
	$tmp_deli[]			= "deli_idx = NULL";
	$tmp_turn_bill0[]	= "turn_code = NULL";
	$tmp_turn_bill1[]	= "turn_code = NULL";
	$tmp_turn_ord[]		= "reor_code = NULL";
	$tmp_turn_deli[]	= "reor_code = NULL";
}

if($_vat == 'vat') {
	$tmp_bill0[]   = "bill_vat > 0";
	$tmp_bill1[]   = "bill_vat > 0";
} else if($_vat == 'vat-IO') {
	$tmp_bill0[]   = "bill_vat > 0 AND bill_type_pajak = 'IO'";
	$tmp_bill1[]   = "bill_vat > 0 AND bill_type_pajak = 'IO'";
}else if($_vat == 'vat-IP') {
	$tmp_bill0[]   = "bill_vat > 0 AND bill_type_pajak = 'IP'";
	$tmp_bill1[]   = "bill_vat > 0 AND bill_type_pajak = 'IP'";
} else if ($_vat == 'non') {
	$tmp_bill0[]   = "bill_vat = 0";
	$tmp_bill1[]   = "bill_vat = 0";
}

$strWhereBill0  		= implode(" AND ", $tmp_bill0);
$strWhereBill1  		= implode(" AND ", $tmp_bill1);
$strWhereOrd  			= implode(" AND ", $tmp_ord);
$strWhereDeli  			= implode(" AND ", $tmp_deli);
$strWhereTurn0  		= implode(" AND ", $tmp_turn_bill0);
$strWhereTurn1  		= implode(" AND ", $tmp_turn_bill1);
$strWhereTurnOrd		= implode(" AND ", $tmp_turn_ord);
$strWhereTurnDeli		= implode(" AND ", $tmp_turn_deli);

$sql = 
"SELECT
  a.icat_pidx AS icat_pidx,
  a.icat_midx AS icat_midx,
  b.it_code AS it_code,
  b.it_model_no AS it_model_no,
  (SELECT SUM(biit_qty) FROM ".ZKP_SQL."_tb_billing JOIN ".ZKP_SQL."_tb_billing_item USING(bill_code) WHERE it_code = b.it_code AND bill_dept='D' AND bill_type_invoice=0 AND $strWhereBill0) AS qty_0,		--0				
  (SELECT -SUM(reit_qty) FROM ".ZKP_SQL."_tb_return JOIN ".ZKP_SQL."_tb_return_item USING(turn_code) WHERE it_code = b.it_code AND turn_dept='D' AND turn_paper = 0 AND $strWhereTurn0) AS qty_1,			--1
  (SELECT SUM(biit_qty) FROM ".ZKP_SQL."_tb_billing JOIN ".ZKP_SQL."_tb_billing_item USING(bill_code) WHERE it_code = b.it_code AND bill_dept='H' AND bill_type_invoice=0 AND $strWhereBill0) AS qty_2,		--2
  (SELECT -SUM(reit_qty) FROM ".ZKP_SQL."_tb_return JOIN ".ZKP_SQL."_tb_return_item USING(turn_code) WHERE it_code = b.it_code AND turn_dept='H' AND turn_paper = 0 AND $strWhereTurn0) AS qty_3,			--3
  (SELECT SUM(biit_qty) FROM ".ZKP_SQL."_tb_billing JOIN ".ZKP_SQL."_tb_billing_item USING(bill_code) WHERE it_code = b.it_code AND bill_dept='P' AND bill_type_invoice=0 AND $strWhereBill0) AS qty_4,		--4
  (SELECT -SUM(reit_qty) FROM ".ZKP_SQL."_tb_return JOIN ".ZKP_SQL."_tb_return_item USING(turn_code) WHERE it_code = b.it_code AND turn_dept='P' AND turn_paper = 0 AND $strWhereTurn0) AS qty_5,			--5
  (SELECT SUM(biit_qty) FROM ".ZKP_SQL."_tb_billing JOIN ".ZKP_SQL."_tb_billing_item USING(bill_code) WHERE it_code = b.it_code AND bill_dept='A' AND bill_type_invoice=0 AND $strWhereBill0) AS qty_6,		--6
  (SELECT -SUM(reit_qty) FROM ".ZKP_SQL."_tb_return JOIN ".ZKP_SQL."_tb_return_item USING(turn_code) WHERE it_code = b.it_code AND turn_dept='A' AND $strWhereTurn0) AS qty_7,								--7
  (SELECT SUM(biit_qty) FROM ".ZKP_SQL."_tb_billing JOIN ".ZKP_SQL."_tb_billing_item USING(bill_code) WHERE it_code = b.it_code AND bill_dept='A' AND bill_type_invoice=1 AND $strWhereBill1) AS qty_8,		--8
  (SELECT SUM(odit_qty) FROM ".ZKP_SQL."_tb_order JOIN ".ZKP_SQL."_tb_order_item USING(ord_code) WHERE it_code = b.it_code AND $strWhereOrd) AS qty_9,														--9
  (SELECT -SUM(roit_qty) FROM ".ZKP_SQL."_tb_return_order JOIN ".ZKP_SQL."_tb_return_order_item USING(reor_code) WHERE it_code = b.it_code AND $strWhereTurnOrd) AS qty_10,									--10
  (SELECT SUM(deit_qty) FROM ".ZKP_SQL."_tb_order JOIN ".ZKP_SQL."_tb_delivery USING(ord_code) JOIN ".ZKP_SQL."_tb_delivery_item USING(deli_idx) WHERE it_code = b.it_code AND $strWhereDeli) AS qty_11,				--11
  (SELECT -SUM(reit_qty) FROM ".ZKP_SQL."_tb_return JOIN ".ZKP_SQL."_tb_return_item USING(turn_code) WHERE it_code = b.it_code AND turn_dept='A' AND turn_paper = 0 AND $strWhereTurn0) AS qty_12,			--12
  (SELECT -SUM(reit_qty) FROM ".ZKP_SQL."_tb_return JOIN ".ZKP_SQL."_tb_return_item USING(turn_code) WHERE it_code = b.it_code AND turn_dept='A' AND turn_paper = 1 AND $strWhereTurn1) AS qty_13,			--13
  (SELECT -SUM(roit_qty) FROM ".ZKP_SQL."_tb_return_order JOIN ".ZKP_SQL."_tb_return_order_item USING(reor_code) WHERE it_code = b.it_code AND $strWhereTurnDeli) AS qty_14									--14
FROM ".ZKP_SQL."_tb_item_cat AS a JOIN ".ZKP_SQL."_tb_item AS b USING(icat_midx) 
$strWhereItem
ORDER BY icat_pidx, icat_midx, it_code
";

// raw data
$rd = array();
$rdIdx = 0;

$cache = array("","");
$group0 = array();
$a = array("","");
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'],				//0
		$col['it_code'],				//1
		$col['it_model_no'],			//2
		$col['qty_0']+$col['qty_1'],	//3	D
		$col['qty_2']+$col['qty_3'],	//4	H
		$col['qty_4']+$col['qty_5'],	//5	P
		$col['qty_6']+$col['qty_12'],	//6	Paper A
		$col['qty_8']+$col['qty_13'],	//7	Paper B
		$col['qty_9']+$col['qty_10'],	//8	Order
		$col['qty_11']+$col['qty_14'],	//9	Deli
		$col['qty_6']+$col['qty_12']+$col['qty_9']+$col['qty_10'],	//10	Total A
		$col['qty_0']+$col['qty_1']+$col['qty_2']+$col['qty_3']+$col['qty_4']+$col['qty_5']+$col['qty_6']+$col['qty_12']+$col['qty_9']+$col['qty_10'] //11	TOTAL
	);

	//1st grouping
	if($cache[0] != $col['icat_midx']) {
		$cache[0] = $col['icat_midx'];
		$group0[$col['icat_midx']] = array();
	}

	if($cache[1] != $col['it_code']) {
		$cache[1] = $col['it_code'];
	}

	$group0[$col['icat_midx']][$col['it_code']] = 1;
}

function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$grand_total = array(3=>0,0,0,0,0,0,0,0,0);

//GROUP
foreach ($group0 as $total1 => $group1) {
	//get category path from current icat_midx.
	$path = executeSP(ZKP_SQL."_getCategoryPath", $rd[$rdIdx][0]);
	eval(html_entity_decode($path[0]));	
	$path = array_reverse($path);
	$total1 = $path[1][4]." > ".$path[2][4]." > ". $path[3][4];

	echo "<span class=\"comment\"><b> CATEGORY: ". $total1. "</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th rowspan="2">MODEL NO</th>
			<th rowspan="2" width="7%">D</th>
			<th rowspan="2" width="7%">H</th>
			<th rowspan="2" width="7%">P</th>
			<th colspan="5" width="35%">A</th>
			<th rowspan="2" width="8%">TOTAL</th>
		</tr>\n
		<tr>
			<th width="7%">issue Item</th>
			<th width="7%">issue No.</th>
			<th width="7%">order</th>
			<th width="7%">delivery</th>
			<th width="7%">Total<small>*</small></th>
		</tr>\n
END;

	$cat_total = array(3=>0,0,0,0,0,0,0,0,0);
	$print_tr_1 = 0;

	//print "<tr>\n";
	//ITEM CODE
	foreach($group1 as $total2 => $group2) {

		if($rd[$rdIdx][11] != 0 || $rd[$rdIdx][7] != 0 || $rd[$rdIdx][9] != 0) {
			//PRINT CONTENT
			if($print_tr_1++ > 0) print "<tr>\n";
			cell("[".trim($rd[$rdIdx][1])."] ".$rd[$rdIdx][2]);			//model name
			cell(number_format($rd[$rdIdx][3]), ' align="right"');		//billing D
			cell(number_format($rd[$rdIdx][4]), ' align="right"');		//billing H
			cell(number_format($rd[$rdIdx][5]), ' align="right"');		//billing P
			cell(number_format($rd[$rdIdx][6]), ' align="right"');		//billing A, paperA
			cell(number_format($rd[$rdIdx][7]), ' align="right"');		//billing A, paperB
			cell(number_format($rd[$rdIdx][8]), ' align="right"');		//order
			cell(number_format($rd[$rdIdx][9]), ' align="right"');		//delivery
			cell(number_format($rd[$rdIdx][10]), ' align="right"');		//billing A, paper A + order
			cell(number_format($rd[$rdIdx][11]), ' align="right"');		//TOTAL
			print "</tr>\n";
		}

		for ($i=3; $i<12; $i++)
			$cat_total[$i] += $rd[$rdIdx][$i];
		$rdIdx++;
	}

	print "<tr>\n";
	cell("<b>$total1</b>", ' align="right" style="color:brown; background-color:lightyellow"');
	for ($i=3; $i<12; $i++)
		cell(number_format($cat_total[$i]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	for ($i=3; $i<12; $i++)
		$grand_total[$i] += $cat_total[$i];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th rowspan="2">MODEL NO</th>
			<th rowspan="2" width="7%">D</th>
			<th rowspan="2" width="7%">H</th>
			<th rowspan="2" width="7%">P</th>
			<th colspan="5" width="35%">A</th>
			<th rowspan="2" width="8%">TOTAL</th>
		</tr>\n
		<tr>
			<th width="7%">issue Item</th>
			<th width="7%">issue No.</th>
			<th width="7%">order</th>
			<th width="7%">delivery</th>
			<th width="7%">Total<small>*</small></th>
		</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' align="right" style="color:brown; background-color:lightyellow"');
for ($i=3; $i<12; $i++)
	cell(number_format($grand_total[$i]), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
/*
echo "<pre>";
echo $sql;
echo "</pre>";
*/
?>