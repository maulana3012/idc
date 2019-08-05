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
$tmp = array();

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if ($_cus_code != "") {
	$tmp[]	= "red_cus_to = '$_cus_code'";
}

if ($some_date != "") {
	$tmp[]	= "red_return_date = DATE '$some_date'";
} else {
	$tmp[]	= "red_return_date BETWEEN DATE '$period_from' AND '$period_to'";
}

if($_status == 'uncfm') {
	$tmp[]	= "red_cfm_marketing_timestamp IS NULL";
} else if($_status == 'cfm') {
	$tmp[]	= "red_cfm_marketing_timestamp IS NOT NULL";
}

$tmp[]	= "red_dept = '$department'";

$strWhere = implode(" AND ", $tmp);

$sql = "
SELECT
  icat_midx,
  icat_pidx,
  it_code,
  it_model_no,
  red_code,
  use_code,
  red_return_by,
  to_char(red_return_date,'dd-Mon-yy') AS return_date,
  cus_code,
  cus_full_name,
  rdit_qty,
  'revise_return.php?_code=' || red_code AS go_page_return,
  'revise_request.php?_code=' || use_code AS go_page_request
FROM
 ".ZKP_SQL."_tb_customer AS c
 JOIN ".ZKP_SQL."_tb_return_demo AS a ON red_cus_to = cus_code
 JOIN ".ZKP_SQL."_tb_return_demo_item AS b USING(red_code)
 JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx) 
WHERE ". $strWhere ." AND rdit_qty>0
ORDER BY icat_pidx, icat_midx, it_code, red_return_date, it_code";

$rd = array();
$rdIdx = 0;

$cache = array("","","");
$group0 = array();

$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'],		//0
		$col['it_code'],		//1
		$col['it_model_no'],	//2
		$col['red_code'],		//3
		$col['use_code'],		//4
		$col['red_return_by'],	//5
		$col['return_date'],	//6
		$col['cus_code'],		//7
		$col['cus_full_name'],	//8
		$col['rdit_qty'],		//9
		$col['go_page_return'],	//10
		$col['go_page_request']	//11
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

	if($cache[2] != $col['red_code']) {
		$cache[2] = $col['red_code'];
	}

	$group0[$col['icat_midx']][$col['it_code']][$col['red_code']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$ggTotal = 0;

//GROUP
foreach ($group0 as $total1 => $group1) {

	//get category path from current icat_midx.
	$path = executeSP(ZKP_SQL."_getCategoryPath", $rd[$rdIdx][0]);
	eval(html_entity_decode($path[0]));	
	$path = array_reverse($path);
	$total1 = $path[1][4]." > ".$path[2][4]." > ".$path[3][4] ;

	echo "<span class=\"comment\"><b> CATEGORY: ". $total1. "</b></span>\n";
	print <<<END
	<table width="100%" class="table_c">
		<tr>
			<th width="15%">MODEL NO</th>
			<th width="13%">REQUEST REF.</th>
			<th width="13%">RETURN NO.</th>
			<th width="10%">RETURN DATE</th>
			<th>CUSTOMER / EVENT</th>
			<th width="10%">QTY<br />(Pcs)</th>
		</tr>\n
END;

	$print_tr_1 = 0;
	$gTotal		= 0;
	print "<tr>\n";
	//ITEM
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".trim($rd[$rdIdx][1])."] ".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');	//Model No

		$total		= 0;
		$print_tr_2 = 0;
		//IDX
		foreach($group2 as $total3 => $group3) {

			if($print_tr_2++ > 0) print "<tr>\n";
			cell_link("<span class=\"bar\">".$rd[$rdIdx][4]."</span>", ' valign="top" align="center"',
				' href="'.$rd[$rdIdx][11].'"');										//Request ref
			cell_link("<span class=\"bar\">".$rd[$rdIdx][3]."</span>", ' valign="top" align="center"',
				' href="'.$rd[$rdIdx][10].'"');										//Document no
			cell($rd[$rdIdx][6], ' valign="top" align="center"');					//Document date
			cell("[".trim($rd[$rdIdx][7])."] ".$rd[$rdIdx][8], ' valign="top"');	//Customer / Event
			cell(number_format((double)$rd[$rdIdx][9],2), ' align="right"');				//qty
			print "</tr>\n";

			$total += $rd[$rdIdx][9];
			$rdIdx++;
		}
		print "<tr>\n";
		cell("$total2", ' colspan="4" align="right" style="color:darkblue"');
		cell(number_format((double)$total,2), ' align="right" style="color:darkblue"');
		print "</tr>\n";

		$gTotal += $total;
	}

	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$gTotal,2), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";

	print "</table><br />\n";
	
	$ggTotal += $gTotal;
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_f">
	<tr>
		<th width="15%">MODEL NO</th>
		<th width="13%">REQUEST REF.</th>
		<th width="13%">RETURN NO.</th>
		<th width="10%">RETURN DATE</th>
		<th>CUSTOMER / EVENT</th>
		<th width="10%">QTY<br />(Pcs)</th>
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$ggTotal,2), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>