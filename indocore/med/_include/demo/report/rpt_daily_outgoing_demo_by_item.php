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
	$tmp[] = "d.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if ($_cus_code != "") {
	$tmp[]	= "use_cus_to = '$_cus_code'";
}

if ($some_date != "") {
	$tmp[]	= "use_request_date = DATE '$some_date'";
} else {
	$tmp[]	= "use_request_date BETWEEN DATE '$period_from' AND '$period_to'";
}

if($_dept != 'all') {
	$tmp[]	= "use_dept = '$_dept'";
}

$tmp[]	= "use_cfm_marketing_timestamp IS NOT NULL";

$strWhere = implode(" AND ", $tmp);

$sql = "
SELECT
  icat_midx,
  icat_pidx,
  it_code,
  it_model_no,
  use_code,
  use_request_by,
  to_char(use_request_date,'dd-Mon-yy') AS request_date,
  use_cus_to,
  use_cus_name,
  usit_qty,
  usit_remark,
  'detail_request.php?_code=' || use_code AS go_page
FROM
 ".ZKP_SQL."_tb_using_demo AS a
 JOIN ".ZKP_SQL."_tb_using_demo_item AS b USING(use_code)
 JOIN ".ZKP_SQL."_tb_item AS c USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS d USING(icat_midx) 
WHERE ". $strWhere ."
ORDER BY icat_pidx, icat_midx, it_code, use_request_date, it_code";

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
		$col['use_code'],		//3
		$col['use_request_by'],	//4
		$col['request_date'],	//5
		$col['use_cus_to'],		//6
		$col['use_cus_name'],	//7
		$col['usit_qty'],		//8
		$col['usit_remark'],	//9
		$col['go_page']			//10
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

	if($cache[2] != $col['use_code']) {
		$cache[2] = $col['use_code'];
	}

	$group0[$col['icat_midx']][$col['it_code']][$col['use_code']] = 1;
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
	<table width="100%" class="table_f">
		<tr>
			<th width="15%">MODEL NO</th>
			<th width="13%">REQUEST NO.</th>
			<th width="10%">REQUEST DATE</th>
			<th>CUSTOMER / EVENT</th>
			<th width="10%">QTY<br />(Pcs)</th>
			<th width="15%">REMARK</th>
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
			cell_link("<span class=\"bar\">".$rd[$rdIdx][3]."</span>", ' valign="top" align="center"',
				' href="'.$rd[$rdIdx][10].'"');										//Document no
			cell($rd[$rdIdx][5], ' valign="top" align="center"');					//Document date
			cell("[".trim($rd[$rdIdx][6])."] ".$rd[$rdIdx][7], ' valign="top"');	//Customer / Event
			cell(number_format($rd[$rdIdx][8],2), ' align="right"');				//qty
			cell($rd[$rdIdx][9], ' align="center"');								//remark
			print "</tr>\n";

			$total += $rd[$rdIdx][8];
			$rdIdx++;
		}
		print "<tr>\n";
		cell("$total2", ' colspan="3" align="right" style="color:darkblue"');
		cell(number_format($total,2), ' align="right" style="color:darkblue"');
		cell('');
		print "</tr>\n";

		$gTotal += $total;
	}

	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="4" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($gTotal,2), ' align="right" style="color:brown; background-color:lightyellow"');
	cell('',' style="color:brown; background-color:lightyellow"');
	print "</tr>\n";

	print "</table><br />\n";
	
	$ggTotal += $gTotal;
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_f">
	<tr>
		<th>MODEL NO</th>
		<th width="15%">REFERENCE NO.</th>
		<th width="10%">REFERENCE DATE</th>
		<th width="10%">WH CFM<br />DATE</th>
		<th width="10%">RECEIVE<br />DATE</th>
		<th width="8%">DT QTY<br />(Pcs)</th>
		<th width="15%">REMARK</th>
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTotal,2), ' align="right" style="color:brown; background-color:lightyellow"');
cell('',' style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>