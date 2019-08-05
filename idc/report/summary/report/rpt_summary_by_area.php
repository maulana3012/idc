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
//SET WHERE PARAMETER
$tmp_bill	= array();
$tmp_turn	= array();
$tmp_dr		= array();
$tmpbill_item	= array();
$tmpturn_item	= array();
$tmpdr_item		= array();

if(ZKP_FUNCTION == 'ALL') {
	if($_order_by != 'all'){
		$tmp_bill[]	= "bill_ordered_by = $_order_by";
		$tmp_turn[] = "turn_ordered_by = $_order_by";
		$tmp_dr[]	= "dr_ordered_by = $_order_by";
		$tmpbill_item[]	= "b.bill_ordered_by = $_order_by";
		$tmpturn_item[]	= "b.turn_ordered_by = $_order_by";
		$tmpdr_item[]	= "b.dr_ordered_by = $_order_by";
	}
} else {
	$tmp_bill[]	= "bill_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill_code,'billing')";
	$tmp_turn[] = "turn_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', turn_code,'billing_return')";
	$tmp_dr[]	= "dr_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', dr_code,'dr')";
	$tmpbill_item[]	= "b.bill_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', b.bill_code,'billing')";
	$tmpturn_item[]	= "b.turn_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', b.turn_code,'billing_return')";
	$tmpdr_item[]	= "b.dr_ordered_by =".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', b.dr_code,'dr')";
}

if ($_filter_doc == "I") {
	$tmp_turn[]		= "b.turn_code = NULL";
	$tmp_dr[]		= "b.dr_code = NULL";
	$tmpturn_item[] = "b.turn_code= NULL";
	$tmpdr_item[]	= "b.dr_code= NULL";
} else if ($_filter_doc == "R") {
	$tmp_bill[]		= "b.bill_code = NULL";
	$tmp_dr[]		= "b.dr_code = NULL";
	$tmpbill_item[] = "b.bill_code = NULL";
	$tmpdr_item[]	= "b.dr_code= NULL";
} else if ($_filter_doc == "DR") {
	$tmp_bill[]		= "b.bill_code = NULL";
	$tmp_turn[]		= "b.turn_code = NULL";
	$tmpbill_item[] = "b.bill_code = NULL";
	$tmpturn_item[] = "b.turn_code= NULL";
}

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp_bill[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_turn[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_dr[]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if($_marketing != "all") {
	$tmp_bill[]	= "cus_responsibility_to = $_marketing";
	$tmp_turn[] = "cus_responsibility_to = $_marketing";
	$tmp_dr[] = "cus_responsibility_to = $_marketing";
	$tmpbill_item[] = "cus_responsibility_to = $_marketing";
	$tmpturn_item[] = "cus_responsibility_to = $_marketing";
	$tmpdr_item[]	= "cus_responsibility_to = $_marketing";
}

if($_dept != 'all') {
	$tmp_bill[] = "b.bill_dept = '$_dept'";
	$tmp_turn[] = "b.turn_dept = '$_dept'";
	$tmp_dr[]	= "b.dr_dept = '$_dept'";
	$tmpbill_item[] = "b.bill_dept = '$_dept'";
	$tmpturn_item[] = "b.turn_dept = '$_dept'";
	$tmpdr_item[]	= "b.dr_dept = '$_dept'";
}

if ($some_date != "") {
	$tmp_bill[] 	= "b.bill_inv_date = DATE '$some_date'";
	$tmp_turn[] 	= "b.turn_return_date = DATE '$some_date'";
	$tmp_dr[]		= "b.dr_issued_date = DATE '$some_date'";
	$tmpbill_item[]	= "b.bill_inv_date = DATE '$some_date'";
	$tmpturn_item[]	= "b.turn_return_date = DATE '$some_date'";
	$tmpdr_item[]	= "b.dr_issued_date = DATE '$some_date'";
} else {
	$tmp_bill[]		= "b.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_turn[]		= "b.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_dr[]		= "b.dr_issued_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmpbill_item[] = "b.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmpturn_item[] = "b.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmpdr_item[]	= "b.dr_issued_date BETWEEN DATE '$period_from' AND '$period_to'";
}

if($_vat == 'vat') {
	$tmp_bill[] 	= "b.bill_vat > 0";
	$tmp_turn[] 	= "b.turn_vat > 0";
	$tmp_dr[]		= "dr_type_item = 1";
	$tmpbill_item[] = "b.bill_vat > 0";
	$tmpturn_item[] = "b.turn_vat > 0"; 
	$tmpdr_item[]	= "dr_type_item = 1";
} else if($_vat == 'vat-IO') {
	$tmp_bill[] 	= "b.bill_vat > 0 AND b.bill_type_pajak = 'IO'";
	$tmp_turn[] 	= "b.turn_vat > 0";
	$tmp_dr[]		= "dr_type_item = 1";
	$tmpbill_item[] = "b.bill_vat > 0 AND b.bill_type_pajak = 'IO'";
	$tmpturn_item[] = "b.turn_vat > 0";
	$tmpdr_item[]	= "dr_type_item = 1";
}else if($_vat == 'vat-IP') {
	$tmp_bill[] 	= "b.bill_vat > 0 AND b.bill_type_pajak = 'IP'";
	$tmp_turn[] 	= "b.turn_code = NULL";
	$tmp_dr[]		= "dr_code is null";
	$tmpbill_item[] = "b.bill_vat > 0 AND b.bill_type_pajak = 'IP'";
	$tmpturn_item[] = "b.turn_code = NULL";
	$tmpdr_item[]	= "dr_code is null";  
} else if ($_vat == 'non') {
	$tmp_bill[] 	= "b.bill_vat = 0";
	$tmp_turn[] 	= "b.turn_vat = 0";
	$tmp_dr[]		= "dr_type_item = 2";
	$tmpbill_item[] = "b.bill_vat = 0";
	$tmpturn_item[] = "b.turn_vat = 0";
	$tmpdr_item[]	= "dr_type_item = 2";
}

if($cboSearchType != '' && $txtSearch != '') {
	$type = array("byCity"=>"cus_city");
	$tmp_bill[] = $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
	$tmp_turn[] = $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
	$tmp_dr[] 	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
	$tmpbill_item[] = $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
	$tmpturn_item[] = $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
	$tmpdr_item[]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
	$get[] = "$cboSearchType=$txtSearch";
}

$strWhereBill  		= implode(" AND ", $tmp_bill);
$strWhereTurn 		= implode(" AND ", $tmp_turn);
$strWhereDR 		= implode(" AND ", $tmp_dr);
$strWhereBillItem   = implode(" AND ", $tmpbill_item);
$strWhereTurnItem   = implode(" AND ", $tmpturn_item);
$strWhereDRItem		= implode(" AND ", $tmpdr_item);

$sql_bill = "
SELECT
  icat.icat_pidx AS icat_pidx,
  it.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  c.cus_city AS cus_city
FROM
  ".ZKP_SQL."_tb_customer AS c
  JOIN ".ZKP_SQL."_tb_billing AS b ON c.cus_code = b.bill_ship_to
  JOIN ".ZKP_SQL."_tb_billing_item AS bi USING(bill_code) 
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE ". $strWhereBill ."
GROUP BY icat.icat_pidx, it.icat_midx, it.it_code, it.it_model_no, c.cus_city
";

$sql_return = "
SELECT
  icat.icat_pidx AS icat_pidx,
  it.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  c.cus_city AS cus_city
FROM
  ".ZKP_SQL."_tb_customer AS c
  JOIN ".ZKP_SQL."_tb_return AS b ON c.cus_code = b.turn_ship_to
  JOIN ".ZKP_SQL."_tb_return_item AS ti USING(turn_code) 
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE ". $strWhereTurn ."
GROUP BY icat.icat_pidx, it.icat_midx, it.it_code, it.it_model_no, c.cus_city
";

$sql_dr = "
SELECT
  icat.icat_pidx AS icat_pidx,
  it.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  c.cus_city AS cus_city
FROM
  ".ZKP_SQL."_tb_customer AS c
  JOIN ".ZKP_SQL."_tb_dr AS b ON c.cus_code = b.dr_ship_to
  JOIN ".ZKP_SQL."_tb_dr_item AS bi USING(dr_code) 
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE ". $strWhereDR ."
GROUP BY icat.icat_pidx, it.icat_midx, it.it_code, it.it_model_no, c.cus_city
";

$sql = "$sql_bill  UNION $sql_return UNION $sql_dr  ORDER BY icat_pidx, icat_midx,it_code,it_model_no,cus_city ";

// raw data
$rd		= array();
$rdIdx	= 0;
$cache	= array("","","");
$group0 = array();
$res 	=& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_pidx'],				//0
		$col['icat_midx'],				//1
		$col['it_code'],				//2
		$col['it_model_no'],			//3
		$col['cus_city']				//4
	);

	//1st grouping
	if($cache[0] != $col['icat_pidx']) {
		$cache[0] = $col['icat_pidx'];
		$group0[$col['icat_pidx']] = array();
	}
	
	if($cache[1] != $col['icat_midx'].'-'.$col['it_code']) {
		$cache[1] = $col['icat_midx'].'-'.$col['it_code'];
		$group0[$col['icat_pidx']][$col['icat_midx'].'-'.$col['it_code']] = array();
	}

	if($cache[2] != $col['cus_city']) {
		$cache[2] = $col['cus_city'];
	}

	$group0[$col['icat_pidx']][$col['icat_midx'].'-'.$col['it_code']][$col['cus_city']] = 1;
}


$sql = "
SELECT
  bill_code AS invoice,
  icat.icat_pidx AS icat_pidx,
  it.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  CASE 
	WHEN c.cus_city IS NOT NULL THEN c.cus_city
	ELSE 'Undefined'
  END AS cus_city ,
  TRUNC(bi.biit_qty) AS qty,
  TRUNC((bi.biit_qty * bi.biit_unit_price * (1 - b.bill_discount/100)),2) AS amount
FROM
  ".ZKP_SQL."_tb_customer AS c
  JOIN ".ZKP_SQL."_tb_billing AS b ON c.cus_code = b.bill_ship_to
  JOIN ".ZKP_SQL."_tb_billing_item AS bi USING(bill_code) 
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE ". $strWhereBill ."
		UNION
SELECT
  turn_code AS invoice,
  icat.icat_pidx AS icat_pidx,
  it.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  CASE 
	WHEN c.cus_city IS NOT NULL THEN c.cus_city
	ELSE 'Undefined'
  END AS cus_city ,
  TRUNC(ti.reit_qty)*-1 AS qty,
  CASE 
	WHEN turn_return_condition=1 THEN null
	ELSE TRUNC((ti.reit_qty * ti.reit_unit_price * (1 - b.turn_discount/100)),2)*-1 
  END AS amount
FROM
  ".ZKP_SQL."_tb_customer AS c
  JOIN ".ZKP_SQL."_tb_return AS b ON c.cus_code = b.turn_ship_to
  JOIN ".ZKP_SQL."_tb_return_item AS ti USING(turn_code) 
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE ". $strWhereTurn ."
		UNION
SELECT
  dr_code AS invoice,
  icat.icat_pidx AS icat_pidx,
  it.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  CASE 
	WHEN c.cus_city IS NOT NULL THEN c.cus_city
	ELSE 'Undefined'
  END AS cus_city ,
  bi.drit_qty AS qty,
  null AS amount
FROM
  ".ZKP_SQL."_tb_customer AS c
  JOIN ".ZKP_SQL."_tb_dr AS b ON c.cus_code = b.dr_ship_to
  JOIN ".ZKP_SQL."_tb_dr_item AS bi USING(dr_code) 
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE ". $strWhereDR ."
ORDER BY icat_pidx, icat_midx,it_code,it_model_no,cus_city ";

$tot = array();
$cit = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {
	$rd[] = array(
		$col['it_code'],$col['cus_city'],$col['qty'],$col['amount']
	);

	if(isset($tot[$col['it_code']][$col['cus_city']][0])) {
		$tot[$col['it_code']][$col['cus_city']][0] += $col['qty'];
		$tot[$col['it_code']][$col['cus_city']][1] += $col['amount'];
	} else {
		$tot[$col['it_code']][$col['cus_city']][0] = $col['qty'];
		$tot[$col['it_code']][$col['cus_city']][1] = $col['amount'];
	}

	if(!isset($cit[$col['cus_city']])) {
		$cit[$col['cus_city']] = $col['cus_city'];
	}

}
sort($cit);
/*
echo "<pre>";
echo $sql;
echo "</pre>";
*/
//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$grand_total = array(0,0);

//GROUP
foreach ($group0 as $total1 => $group1) {

	//get category path from current icat_midx.
	$path = executeSP(ZKP_SQL."_getCategoryPath", $rd[$rdIdx][0]);
	eval(html_entity_decode($path[0]));	
	$path = array_reverse($path);
	$total1 = $path[1][4]." > ".$path[2][4] ;

	echo "<span class=\"comment\"><b> CATEGORY: ". $total1. "</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th width="25%">MODEL NO</th>
			<th>CITY</th>
			<th width="10%">QTY<br>(EA)</th>
			<th width="20%">AMOUNT<br>(Rp)</th>
		</tr>\n
END;
	$cat_total = array(0,0);
	$print_tr_1 = 0;

	print "<tr>\n";
	//MODEL
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell('['.trim($rd[$rdIdx][2]).'] '.$rd[$rdIdx][3], ' valign="top" rowspan="'.$rowSpan.'"');		//Model No

		$model_total = array(0,0);
		$print_tr_2 = 0;
		//CITY
		foreach($group2 as $total3 => $group3) {
			if($rd[$rdIdx][4]!='')	$city=$rd[$rdIdx][4];
			else					$city='Undefined';

			if($print_tr_2++ > 0) print "<tr>\n";
			cell(($rd[$rdIdx][4]=='')?'<i class="comment">Undefined</i>':$rd[$rdIdx][4]);	// City
			cell(number_format((double)$tot[$rd[$rdIdx][2]][$city][0]), ' align="right"');			// Qty
			cell(number_format((double)$tot[$rd[$rdIdx][2]][$city][1]), ' align="right"');			// Amount

			$model_total[0] += $tot[$rd[$rdIdx][2]][$city][0];
			$model_total[1] += $tot[$rd[$rdIdx][2]][$city][1];
			$model_no = $rd[$rdIdx][3]; 	// Model no
			$rdIdx++;
		}

		print "<tr>\n";
		cell($model_no, ' align="right" style="color:darkblue"');
		cell(number_format((double)$model_total[0]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$model_total[1]), ' align="right" style="color:darkblue"');
		print "</tr>\n";

		$cat_total[0] += $model_total[0];
		$cat_total[1] += $model_total[1];
	}

	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="2" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cat_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cat_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$grand_total[0] += $cat_total[0];
	$grand_total[1] += $cat_total[1];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_f">
	<tr>
		<th width="25%">MODEL NO</th>
		<th>CITY</th>
		<th width="10%">QTY<br>(EA)</th>
		<th width="20%">AMOUNT<br>(Rp)</th>
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="2" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";


?>