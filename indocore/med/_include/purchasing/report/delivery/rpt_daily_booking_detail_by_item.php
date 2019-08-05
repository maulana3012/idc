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
$tmp	= array();

if($_source != 'all') {
	$tmp[]	= "b.book_doc_type = $_source"; 
}

if ($_cug_code != 'all') {
	//If group specified, 
	$tmp[]  = "b.cus_code IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$sql	= " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
} else {
	 // if null, return Others Group
	$sql = "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = b.cus_code),
		'Others') AS cug_name,";
}

if($_dept != 'all') {
	if($_dept == 'M') {
		$tmp[] = "b.book_doc_type = 6";
	} else {
		$tmp[] = "b.book_dept = '$_dept'";
	}
}

if ($some_date != "") {
	$tmp[]   = "b.book_date = DATE '$some_date'";
} else {
	$tmp[]   = "b.book_date BETWEEN DATE '$period_from' AND '$period_to'";
}

if($_vat == 1) {
	$tmp[]	= "b.book_type = 1"; 
} else if ($_vat == 2) {
	$tmp[]	= "b.book_type = 2";
} else if ($_vat == 3) {
	$tmp[]	= "b.book_type = 3";
}

$tmp[]		= "b.book_is_delivered = 'f'";

$strWhere   = implode(" AND ", $tmp);

$sql = "
SELECT
  a.cus_code AS cus_code,
  a.cus_full_name AS cus_full_name,
  b.book_doc_ref AS doc_code,
  b.book_idx AS booked_idx,
  b.book_code AS booked_code,
  to_char(b.book_date,'dd-Mon-YY') AS issued_date,
  CASE
	WHEN b.book_type = 1 THEN 'VAT'
	WHEN b.book_type = 2 THEN 'NON'
	WHEN b.book_type = 3 THEN 'NO SPEC'
  END AS booked_type,
  c.boit_idx AS it_idx,
  c.boit_qty AS it_qty,
  c.boit_remark AS it_remark,
  d.it_code AS it_code,
  d.it_model_no AS it_model_no,
  e.icat_pidx AS icat_pidx,
  e.icat_midx AS icat_midx,
  CASE
 	WHEN book_doc_type = 6 THEN 'confirm_request.php'||b.book_doc_ref
 	ELSE 'confirm_do.php'||b.book_doc_ref
  END AS go_page
FROM
  ".ZKP_SQL."_tb_customer AS a
  JOIN ".ZKP_SQL."_tb_booking AS b USING(cus_code)
  JOIN ".ZKP_SQL."_tb_booking_item AS c USING(book_idx)
  JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS e USING(icat_midx)
WHERE ". $strWhere ."
ORDER BY icat_pidx, icat_midx, it_code, b.book_date, it_idx";

// raw data
$rd = array();
$rdIdx = 0;

$cache = array("","","",""); // 3th level
$group0 = array();

$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'], 		//0
		$col['it_code'],		//1
		$col['it_model_no'],	//2
		$col['booked_idx'], 	//3
		$col['booked_code'], 	//4
		$col['doc_code'],		//5
		$col['issued_date'],	//6
		$col['booked_type'],	//7
		$col['cus_code'],		//8
		$col['cus_full_name'],	//9
		$col['it_idx'],			//10
		$col['it_qty'],			//11
		$col['it_remark']		//12
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

	if($cache[2] != $col['booked_idx']) {
		$cache[2] = $col['booked_idx'];
		$group0[$col['icat_midx']][$col['it_code']][$col['booked_idx']] = array();
	}

	if($cache[3] != $col['it_idx']) {
		$cache[3] = $col['it_idx'];
	}
	
	$group0[$col['icat_midx']][$col['it_code']][$col['booked_idx']][$col['it_idx']] = 1;
}
/*
echo "<pre>";
var_dump($sql);
echo "</pre>";
exit;
*/
//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$grand_total = 0;

//GROUP
foreach ($group0 as $total1 => $group1) {

	//get category path from current icat_midx.
	$path = executeSP(ZKP_SQL."_getCategoryPath", $rd[$rdIdx][0]);
	eval(html_entity_decode($path[0]));	
	$path = array_reverse($path);
	$total1 = $path[1][4]." > ".$path[2][4]." > ".$path[3][4] ;

	echo "<span class=\"comment\"><b> CATEGORY: ". $total1. "</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th width="15%">MODEL NO</th>
			<th width="13%">DELI. NO</th>
			<th width="13%">INV. NO</th>
			<th width="8%">DO DATE</th>
			<th width="8%">OUTGOING<br />TYPE</th>
			<th>CUSTOMER</th>
			<th width="7%">QTY</th>
			<th width="10%">REMARK</th>
		</tr>\n
END;
	$cat_total = 0;
	$print_tr_1 = 0;

	print "<tr>\n";
	//MODEL
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[{$rd[$rdIdx][1]}] ".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');	//Item code, model no

		$model_total = 0;
		$print_tr_2 = 0;
		//ORDER
		foreach($group2 as $total3 => $group3) {
			$rowSpan = 0;
			array_walk_recursive($group3, 'getRowSpan');

			if($print_tr_2++ > 0) print "<tr>\n";
			cell_link("<span class=\"bar\">".$rd[$rdIdx][4]."</span>", ' align="center" valign="top" rowspan="'.$rowSpan.'"',
				' href="./confirm_do.php?_code='.$rd[$rdIdx][3].'"');				//Booking code
			cell($rd[$rdIdx][5], ' align="center" valign="top" rowspan="'.$rowSpan.'"');	//Document ref code
			cell($rd[$rdIdx][6], ' align="center" valign="top" rowspan="'.$rowSpan.'"');	//DO date
			cell($rd[$rdIdx][7], ' align="center" valign="top" rowspan="'.$rowSpan.'"');	//Booking type
			cell("[{$rd[$rdIdx][8]}] ".$rd[$rdIdx][9], ' valign="top" rowspan="'.$rowSpan.'"');	//Customer name

			$print_tr_3 = 0;
			//ITEM
			foreach($group3 as $total4 => $group4) {
				if($print_tr_3++ > 0) print "<tr>\n";

				cell(number_format($rd[$rdIdx][11],2), ' align="right"'); //qty
				cell($rd[$rdIdx][12]);									//remark
				print "</tr>\n";

				$model_total += $rd[$rdIdx][11]; //grand total	
				$model_no	 = $rd[$rdIdx][2];
				$rdIdx++;
			}
		}

		print "<tr>\n";
		cell($model_no, ' colspan="5" align="right" style="color:darkblue"');
		cell(number_format($model_total,2), ' align="right" style="color:darkblue"');
		cell('');
		print "</tr>\n";

		$cat_total += $model_total;
	}
	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="6" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($cat_total,2), ' align="right" style="color:brown; background-color:lightyellow"');
	cell('',' style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";
	
	$grand_total += $cat_total;
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th width="15%">MODEL NO</th>
			<th width="13%">DELI. NO</th>
			<th width="13%">INV. NO</th>
			<th width="8%">DO DATE</th>
			<th width="8%">OUTGOING<br />TYPE</th>
			<th>CUSTOMER</th>
			<th width="7%">QTY</th>
			<th width="10%">REMARK</th>
		</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="6" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total,2), ' align="right" style="color:brown; background-color:lightyellow"');
cell('', ' style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>