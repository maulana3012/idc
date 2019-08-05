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
$tmp_bill	= array();
$tmp_turn	= array();
$tmp_dr		= array();
$tmpbill_item	= array();
$tmpturn_item	= array();
$tmpdr_item		= array();

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp_bill[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_turn[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_dr[]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_pl[]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_cl[]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_bill1[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_turn1[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_dr1[]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_pl1[]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_cl1[]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_price[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if($_order_by == 1){
	$tmp_bill[]	= "bill_ordered_by = 1";
	$tmp_turn[] = "turn_ordered_by = 1";
	$tmp_bill1[]	= "bill_ordered_by = 1";
	$tmp_turn1[] = "turn_ordered_by = 1";
} else if($_order_by == 2) {
	$tmp_bill[]	= "bill_ordered_by = 2";
	$tmp_turn[] = "turn_ordered_by = 2";
	$tmp_dr[]	= "dr_code is null";
	$tmp_bill1[]	= "bill_ordered_by = 2";
	$tmp_turn1[] = "turn_ordered_by = 2";
	$tmp_dr1[]	= "dr_code is null";
}

if($_vat == 'vat') {
	$tmp_bill[] = "bill.bill_vat > 0";
	$tmp_turn[] = "turn.turn_vat > 0";
	$tmp_dr[]	= "dr_type_item = 1";
	$tmp_pl[] 	= "inpl_type = 1";
	$tmp_cl[]	= "incl_type = 1";
	$tmp_bill1[] = "bill.bill_vat > 0";
	$tmp_turn1[] = "turn.turn_vat > 0";
	$tmp_dr1[]	= "dr_type_item = 1";
	$tmp_pl1[] 	= "inpl_type = 1";
	$tmp_cl1[]	= "incl_type = 1";
} else if ($_vat == 'non') {
	$tmp_bill[] = "bill.bill_vat = 0";
	$tmp_turn[] = "turn.turn_vat = 0";
	$tmp_dr[]	= "dr_type_item = 2";
	$tmp_pl[] 	= "inpl_type = 2";
	$tmp_cl[]	= "incl_type = 2";
	$tmp_bill1[] = "bill.bill_vat = 0";
	$tmp_turn1[] = "turn.turn_vat = 0";
	$tmp_dr1[]	= "dr_type_item = 2";
	$tmp_pl1[] 	= "inpl_type = 2";
	$tmp_cl1[]	= "incl_type = 2";
}

if ($some_date != "") {
	$tmp_bill[] = "bill.bill_inv_date = DATE '$some_date'";
	$tmp_turn[] = "turn.turn_return_date = DATE '$some_date'";
	$tmp_dr[]	= "dr.dr_issued_date = DATE '$some_date'";
	$tmp_pl[]	= "inpl.inpl_checked_date = DATE '$some_date'";
	$tmp_cl[]	= "incl.incl_checked_date = DATE '$some_date'";
} else {
	$tmp_bill[]	= "bill.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_turn[]	= "turn.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_dr[]	= "dr.dr_issued_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_pl[]	= "inpl.inpl_checked_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_cl[]	= "incl.incl_checked_date BETWEEN DATE '$period_from' AND '$period_to'";
}

$date = ($some_date != "") ? date('Y-m-d', strtotime($some_date)) : date('Y-m-d', strtotime($period_to));
$date_init = ($some_date != "") ? date('Y-m-d', strtotime($some_date)) : date('Y-m-d', strtotime($period_from)-86400);

$tmp_bill1[] = "bill.bill_inv_date BETWEEN DATE '2010-10-01' AND '$date_init'";
$tmp_turn1[] = "turn.turn_return_date BETWEEN DATE '2010-10-01' AND '$date_init'";
$tmp_dr1[]	= "dr.dr_issued_date BETWEEN DATE '2010-10-01' AND '$date_init'";
$tmp_pl1[]	= "inpl.inpl_checked_date BETWEEN DATE '2010-10-01' AND '$date_init'";
$tmp_cl1[]	= "incl.incl_checked_date BETWEEN DATE '2010-10-01' AND '$date_init'";

$strWhereBill  		= implode(" AND ", $tmp_bill);
$strWhereTurn 		= implode(" AND ", $tmp_turn);
$strWhereDR 		= implode(" AND ", $tmp_dr);
$strWherePL  		= implode(" AND ", $tmp_pl);
$strWhereCL 		= implode(" AND ", $tmp_cl);

$strWhereBillInit  		= implode(" AND ", $tmp_bill1);
$strWhereTurnInit 		= implode(" AND ", $tmp_turn1);
$strWhereDRInit 		= implode(" AND ", $tmp_dr1);
$strWherePLInit  		= implode(" AND ", $tmp_pl1);
$strWhereCLInit 		= implode(" AND ", $tmp_cl1);

$strWherePrice = isset($tmp_price) ? "WHERE ".implode(" AND ", $tmp_price) : "";

//================================================================================================================ Declare Item
$sqlItemDesc = "SELECT seit_code, it_code, it_model_no FROM idc_tb_item JOIN idc_tb_set_item AS b USING(it_code) WHERE it_code != seit_code";
$res =& query($sqlItemDesc);
while($col =& fetchRow($res)) {
	$item_duplicate[$col[0]][0] = $col[0];
	$item_duplicate[$col[0]][1][] = $col[1];
	$item_duplicate[$col[0]][2][] = $col[2];
}
echo "<pre>";
//var_dump($item_duplicate);
echo "</pre>";
function isAvailableItem(&$f_item, &$f_item_model, &$f_item_qty, &$f_full_item, $item_duplicate, &$item_avail) {
	$item_avail = true;
	if($f_item == $item_duplicate[$f_item][0]) {
		if(trim($f_item) == '2100' || trim($f_item) == '2100NE') {
			$f_item_model = $item_duplicate[$f_item][2][0];
			$f_item = $item_duplicate[$f_item][1][0];
			$f_item_qty = $f_item_qty/2;
			if(isset($f_full_item[0][$f_item])) {
				$f_full_item[1][$f_item] += $f_item_qty;
			} else {
				$f_full_item[1][$f_item] = $f_item_qty;
			}
			$f_full_item[0][$f_item] = $f_item_model;	
		} else {
			$t_f_item = $f_item;
			$t_f_item_model = $f_item_model;
			for($i=0; $i<count($item_duplicate[$t_f_item][1]); $i++) {
//echo count($item_duplicate[$f_item][1])."--$f_item-$i***";
				$f_item_model = $item_duplicate[$t_f_item][2][$i];
				$f_item = $item_duplicate[$t_f_item][1][$i];
//echo "$f_item-$i***<br />";
				isAvailableItem2($f_item, $f_item_model, $f_item_qty, $f_full_item, $item_duplicate);
			}
		}
		$item_avail = false;
	}
}

function isAvailableItem2(&$f_item, &$f_item_model, &$f_item_qty, &$f_full_item, $item_duplicate) {
	if(isset($f_full_item[0][$f_item])) {
		$f_full_item[1][$f_item] += $f_item_qty;
	} else {
		$f_full_item[1][$f_item] = $f_item_qty;
	}
	$f_full_item[0][$f_item] = $f_item_model;	
}

//================================================================================================================ Count Previous Balance
if($date_init >= '2010-09-30') {
	$filedata = explode("\n", file_get_contents("/home/neki/pdf/indocore/it_code.txt"));
	for($i = 0; $i < count($filedata); $i++) {
		$j = explode("[[[[[", $filedata[$i]);
		$item_init[0][$j[0]] = $j[1];
		$item_init[1][$j[0]] = (int) $j[2];
		$item_init[2][$j[0]] = (int) $j[3];
	}

$catList = explode(", ",$catList[0]);

if ($_last_category != 0) {
	foreach($item_init[2] as $key => $val) {
		if(!in_array($item_init[2][$key], $catList)) {
			unset($item_init[0][$key]);
			unset($item_init[1][$key]);
			unset($item_init[2][$key]);
		}
	}
	unset($item_init[2]);
}

if($date_init > '2010-09-30') {
$sqlInit = "
	SELECT it_code, it_model_no, init_qty AS it_qty
	FROM
		".ZKP_SQL."_tb_in_pl AS inpl
		JOIN ".ZKP_SQL."_tb_in_pl_item AS init USING(inpl_idx)
		JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
		JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
	WHERE $strWherePLInit
UNION
	SELECT it_code, it_model_no, init_qty AS it_qty
	FROM
		".ZKP_SQL."_tb_in_claim AS incl
		JOIN ".ZKP_SQL."_tb_in_claim_item AS init USING(incl_idx)
		JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
		JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
	WHERE $strWhereCLInit
UNION
	SELECT it.it_code, it.it_model_no, sum(biit_qty)*-1 AS qty
	FROM
		".ZKP_SQL."_tb_billing AS bill
		JOIN ".ZKP_SQL."_tb_billing_item AS biit USING(bill_code)
		JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
		JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
	WHERE $strWhereBillInit
	GROUP BY it.it_code, it.it_model_no 
UNION 
	SELECT it.it_code, it.it_model_no, sum(reit_qty) AS qty
	FROM
		".ZKP_SQL."_tb_return AS turn
		JOIN ".ZKP_SQL."_tb_return_item AS reit USING(turn_code)
		JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
		JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
	WHERE $strWhereTurnInit
	GROUP BY it.it_code, it.it_model_no
UNION
	SELECT it.it_code, it.it_model_no, sum(drit_qty)*-1 AS qty
	FROM
		".ZKP_SQL."_tb_dr AS dr
		JOIN ".ZKP_SQL."_tb_dr_item AS drit USING(dr_code)
		JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
		JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
	WHERE $strWhereDRInit
	GROUP BY it.it_code, it.it_model_no
ORDER BY it_code
";
$res =& query($sqlInit);
while($col =& fetchRow($res)) { 
	isAvailableItem($col[0], $col[1], $col[2], $item_init, $item_duplicate, $item_avail);
	if($item_avail) {
		if(isset($item_init[0][$col[0]])) {
			$item_init[1][$col[0]] += $col[2];
		} else {
			$item_init[1][$col[0]] = $col[2];
		}
		$item_init[0][$col[0]] = $col[1];
	}
}
echo "<pre>";
//var_dump($sqlInit);
//var_dump($item_init[1]);
echo "</pre>";
} }
//================================================================================================================ Count Incoming - Outgoing Balance
$sqlIncoming = "
	SELECT it_code, it_model_no, init_qty AS it_qty
	FROM
		".ZKP_SQL."_tb_in_pl AS inpl
		JOIN ".ZKP_SQL."_tb_in_pl_item AS init USING(inpl_idx)
		JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
		JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
	WHERE $strWherePL
UNION
	SELECT it_code, it_model_no, init_qty AS it_qty
	FROM
		".ZKP_SQL."_tb_in_claim AS incl
		JOIN ".ZKP_SQL."_tb_in_claim_item AS init USING(incl_idx)
		JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
		JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
	WHERE $strWhereCL
ORDER BY it_code
";

$sqlOutgoing = "
	SELECT it.it_code, it.it_model_no, sum(biit_qty) AS qty
	FROM
		".ZKP_SQL."_tb_billing AS bill
		JOIN ".ZKP_SQL."_tb_billing_item AS biit USING(bill_code)
		JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
		JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
	WHERE $strWhereBill
	GROUP BY it.it_code, it.it_model_no 
UNION 
	SELECT it.it_code, it.it_model_no, sum(reit_qty)*-1 AS qty
	FROM
		".ZKP_SQL."_tb_return AS turn
		JOIN ".ZKP_SQL."_tb_return_item AS reit USING(turn_code)
		JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
		JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
	WHERE $strWhereTurn 
	GROUP BY it.it_code, it.it_model_no
UNION
	SELECT it.it_code, it.it_model_no, sum(drit_qty) AS qty
	FROM
		".ZKP_SQL."_tb_dr AS dr
		JOIN ".ZKP_SQL."_tb_dr_item AS drit USING(dr_code)
		JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
		JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
	WHERE $strWhereDR 
	GROUP BY it.it_code, it.it_model_no
ORDER BY it_code
";

$item = array(); $in = array(); $out = array(); 
$res1 =& query($sqlIncoming);
$res2 =& query($sqlOutgoing);
while($col =& fetchRow($res1)) { $in[0][$col[0]] = $col[1]; $in[1][$col[0]]=$col[2]; }
while($col =& fetchRow($res2)) {
	isAvailableItem($col[0], $col[1], $col[2], $out, $item_duplicate, $item_avail);
	if($item_avail) {
		if(isset($out[0][$col[0]])) {
			$out[1][$col[0]] += $col[2];
		} else {
			$out[1][$col[0]] = $col[2];
		}
		$out[0][$col[0]] = $col[1];
	}
}
echo "<pre>";
//var_dump($out);
echo "</pre>";

$item = $item_init[0];
if(count($in[0]) > 0) foreach($in[0] as $key1 => $val1) 
	if(empty($item[$key1])) $item[$key1] = $val1; 
if(count($out[0]) > 0) foreach($out[0] as $key1 => $val1) 
	if(empty($item[$key1])) $item[$key1] = $val1; 

//================================================================================================================ Declare Price
$sqlPrice = "
SELECT 
	it_code,
	".ZKP_SQL."_getuserprice(it_code, '$date', 'net', 'kurs') AS ipn_price_dollar,
	".ZKP_SQL."_getuserprice(it_code, '$date', 'net', 'dollar') AS ipn_price_kurs
FROM 
	".ZKP_SQL."_tb_item_cat AS icat
	JOIN ".ZKP_SQL."_tb_item AS it USING (icat_midx)
	JOIN ".ZKP_SQL."_tb_item_price_net AS ipn USING (it_code)
$strWherePrice
";
$res3 =& query($sqlPrice);
while($col =& fetchRow($res3)) { 
	$price[0][$col[0]] = $col[1]; 
	$price[1][$col[0]] = $col[2];
}

echo "<pre>";
//var_dump($price[1]);
echo "</pre>";
print <<<END
<table width="100%" class="table_f">
	<tr>
		<th width="8%">CODE</th>
		<th>MODEL NO</th>
		<th width="8%">LAST<br />BAL</th>
		<th width="8%">IN</th> 
		<th width="8%">OUT</th> 
		<th width="10%">BALANCE</th> 
		<th width="12%">@ PRICE</th> 
		<th width="8%">KURS</th> 
		<th width="12%">TOTAL<br />(RP)</th> 
	</tr>\n
END;

	print "<tr>";
	cell("<i>Generate at ".date("d-M-Y H:i:s")."</i>", ' colspan="9" align="right"');
	print "</tr>";

$g_total = array(0,0,0,0,0);
if(count($item) > 0) { foreach($item as $key => $val) {

	$total = array(0,0,0,0,0);
	$total[0] = $item_init[1][$key];					// previous balance
	$total[1] = $in[1][$key];							// incoming
	$total[2] = $out[1][$key];							// outgoing
	$total[3] = $total[0] + $total[1] - $total[2];		// current balance
	$total[4] = ($price[0][$key]*$price[1][$key]) * $total[3];		// total (rp)

	print "<tr>\n";
	cell($key);
	cell($val);

	if(trim($key) == '2101' || trim($key) == '2101NE') {
		cell(number_format($total[0],2), ' align="right"');
		cell(($total[1]>0) ? number_format($total[1],2) : '', ' align="right"');
		cell(($total[2]>0) ? number_format($total[2],2) : '', ' align="right"');
	} else {
		cell(number_format($total[0]), ' align="right"');
		cell(($total[1]>0) ? number_format($total[1]) : '', ' align="right"');
		cell(($total[2]>0) ? number_format($total[2]) : '', ' align="right"');
	}
	cell(number_format($total[3]), ' align="right"');
	cell(isset($price[0][$key]) && $price[0][$key]!='' ? $price[0][$key] : 0, ' align="right"');
	cell(isset($price[1][$key]) && $price[1][$key]!='' ? $price[1][$key] : 0, ' align="right"');
	cell(number_format($total[4]), ' align="right"');
	print "</tr>\n";

	$g_total[0] += $total[0];
	$g_total[1] += $total[1];
	$g_total[2] += $total[2];
	$g_total[3] += $total[3];
	$g_total[4] += $total[4];

} }

print "<tr>\n";
cell("<b>TOTAL</b>", ' colspan="2" style="color:brown; background-color:lightyellow"');
cell(number_format($g_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($g_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($g_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($g_total[3]), ' align="right" style="color:brown; background-color:lightyellow"');
cell("", ' align="right" style="color:brown; background-color:lightyellow"');
cell("", ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($g_total[4]), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br />\n";
?>