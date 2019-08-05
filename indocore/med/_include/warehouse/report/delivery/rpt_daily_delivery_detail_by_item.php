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

if(ZKP_SQL == 'IDC') {
	if($_order_by == '1') {
		$tmp[] = "substr(out_code,1,1)='D' and substr(out_code,4,1)!='M'";
	} else if($_order_by == '2') {
		$tmp[] = "substr(out_code,1,1)='D' and substr(out_code,4,1)='M'";
	}
} else if(ZKP_SQL == 'MED') {
	if($_order_by == '1') {
		$tmp[] = "substr(out_code,1,1)='D' and substr(out_code,4,1)!='S'";
	} else if($_order_by == '2') {
		$tmp[] = "substr(out_code,1,1)='D' and substr(out_code,4,1)='S'";
	}
}

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp[] = "e.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if($_source != 'all') {
	$tmp[]	= "b.out_doc_type = $_source"; 
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
	if($_dept == 'DEMO') {
		$tmp[] = "b.out_doc_type = 6";
	} else {
		$tmp[] = "b.out_dept = '$_dept' AND b.out_doc_type != 6";
	}
}

if ($some_date != "") {
	$tmp[]   = "b.out_cfm_date = DATE '$some_date'";
} else {
	$tmp[]   = "b.out_cfm_date BETWEEN DATE '$period_from' AND '$period_to'";
}

if($_vat == 1) {
	$tmp[]	= "b.out_type = 1"; 
} else if ($_vat == 2) {
	$tmp[]	= "b.out_type = 2";
}

$strWhere   = implode(" AND ", $tmp);

$sql = "
SELECT
  a.cus_code AS cus_code,
  a.cus_full_name AS cus_full_name,
  b.out_idx AS out_idx,
  b.out_code AS deli_code,
  b.out_doc_ref AS doc_ref_code,
  to_char(b.out_cfm_date,'dd-Mon-YY') AS deli_date,
  to_char(b.out_issued_date,'dd-Mon-YY') AS issued_date,
  CASE
	WHEN b.out_type = 1 THEN 'VAT'
	WHEN b.out_type = 2 THEN 'NON'
	WHEN b.out_type = 3 THEN 'NO SPEC'
  END AS deli_type,
  c.otit_idx AS otit_idx,
  c.it_code AS it_code,
  d.it_model_no AS it_model_no,
  c.otit_qty AS otit_qty,
  e.icat_pidx AS icat_pidx,
  e.icat_midx AS icat_midx
FROM
  ".ZKP_SQL."_tb_customer AS a
  JOIN ".ZKP_SQL."_tb_outgoing AS b USING(cus_code)
  JOIN ".ZKP_SQL."_tb_outgoing_item AS c USING(out_idx)
  JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS e USING(icat_midx)
WHERE ". $strWhere ."
ORDER BY icat_pidx, icat_midx, it_code, b.out_cfm_date, otit_idx";

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
		$col['out_idx'],		//3
		$col['cus_code'],		//4
		$col['cus_full_name'],	//5
		$col['deli_code'], 		//6
		$col['doc_ref_code'], 	//7
		$col['deli_date'],		//8
		$col['issued_date'],	//9
		$col['deli_type'],		//10
		$col['otit_idx'],		//11
		$col['otit_qty']		//12
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

	if($cache[2] != $col['out_idx']) {
		$cache[2] = $col['out_idx'];
		$group0[$col['icat_midx']][$col['it_code']][$col['out_idx']] = array();
	}

	if($cache[3] != $col['otit_idx']) {
		$cache[3] = $col['otit_idx'];
	}
	
	$group0[$col['icat_midx']][$col['it_code']][$col['out_idx']][$col['otit_idx']] = 1;
}

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
			<th width="15%">DELI. NO</th>
			<th width="12%">INV. NO</th>
			<th width="8%">DO DATE</th>
			<th width="8%">CONFIRM<br />DATE</th>
			<th>CUSTOMER</th>
			<th width="5%">QTY</th>
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
		cell("[".trim($rd[$rdIdx][1])."] ".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');	//Item code, model no

		$model_total = 0;
		$print_tr_2 = 0;
		//ORDER
		foreach($group2 as $total3 => $group3) {
			$rowSpan = 0;
			array_walk_recursive($group3, 'getRowSpan');

			if($print_tr_2++ > 0) print "<tr>\n";
			cell_link("<span class=\"bar\">".$rd[$rdIdx][6]."</span>", ' align="center" valign="top" rowspan="'.$rowSpan.'"',
				' href="./detail_do.php?_code='.$rd[$rdIdx][3].'"');						//Delivery code
			cell($rd[$rdIdx][7], ' align="center" valign="top" rowspan="'.$rowSpan.'"');	//Document ref code
			cell($rd[$rdIdx][9], ' align="center" valign="top" rowspan="'.$rowSpan.'"');				//Issued date
			cell($rd[$rdIdx][8], ' align="center" valign="top" rowspan="'.$rowSpan.'"');	//Delivery date
			cell("[".trim($rd[$rdIdx][4])."] ".$rd[$rdIdx][5], ' valign="top" rowspan="'.$rowSpan.'"');	//Customer name

			$print_tr_3 = 0;
			//ITEM
			foreach($group3 as $total4 => $group4) {
				if($print_tr_3++ > 0) print "<tr>\n";

				cell(number_format($rd[$rdIdx][12],2), ' align="right"'); //qty
				print "</tr>\n";

				$item = "[".trim($rd[$rdIdx][1])."] ".$rd[$rdIdx][2];
				$model_total += $rd[$rdIdx][12]; //grand total	
				$rdIdx++;
			}
		}

		print "<tr>\n";
		cell($item, ' colspan="5" align="right" style="color:darkblue"');
		cell(number_format($model_total,2), ' align="right" style="color:darkblue"');
		print "</tr>\n";

		$cat_total += $model_total;
	}
	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="6" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($cat_total,2), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";
	
	$grand_total += $cat_total;
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_f">
	<tr>
		<th width="15%">MODEL NO</th>
		<th width="15%">DELI. NO</th>
		<th width="12%">INV. NO</th>
		<th width="8%">DO DATE</th>
		<th width="8%">CONFIRM<br />DATE</th>
		<th>CUSTOMER</th>
		<th width="5%">QTY</th>
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="6" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total,2), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>