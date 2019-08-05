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

if ($_cus_code != "") {
	$tmp[]	= "use_cus_to = '$_cus_code'";
}

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if ($some_date != "") {
	$tmp[]	= "use_request_date = DATE '$some_date'";
} else {
	$tmp[]	= "use_request_date BETWEEN DATE '$period_from' AND '$period_to'";
}

if($_status == 'uncfm') {
	$tmp[]	= "use_cfm_marketing_timestamp IS NULL";
} else if($_status == 'cfm') {
	$tmp[]	= "use_cfm_marketing_timestamp IS NOT NULL";
}

$tmp[]	= "use_cfm_marketing_timestamp IS NOT NULL";
$tmp[]	= "use_dept = '$department'";

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
  usit_qty AS req_qty,
  (SELECT sum(rdit_qty) FROM med_tb_using_demo join med_tb_return_demo using (use_code) join med_tb_return_demo_item using (red_code) WHERE it_code=c.it_code AND use_code=b.use_code) AS turn_qty,
  (SELECT red_code FROM med_tb_using_demo join med_tb_return_demo using (use_code) join med_tb_return_demo_item using (red_code) WHERE it_code=c.it_code AND use_code=b.use_code) AS last_turn_code,
  (SELECT to_char(max(red_return_date), 'dd-Mon-YYYY') FROM med_tb_using_demo join med_tb_return_demo using (use_code) join med_tb_return_demo_item using (red_code) WHERE it_code=c.it_code AND use_code=b.use_code) AS last_turn_date,
  'revise_request.php?_code=' || use_code AS go_page_req,
  'revise_return.php?_code=' || (SELECT red_code FROM med_tb_using_demo join med_tb_return_demo using (use_code) join med_tb_return_demo_item using (red_code) WHERE it_code=c.it_code AND use_code=b.use_code) AS go_page_turn
FROM
 ".ZKP_SQL."_tb_using_demo AS a
 JOIN ".ZKP_SQL."_tb_using_demo_item AS b USING(use_code)
 JOIN ".ZKP_SQL."_tb_item AS c USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx) 
WHERE ". $strWhere ."
ORDER BY icat_pidx, icat_midx, it_code, use_request_date, it_code";

echo "<pre>";
//var_dump($sql);
echo "</pre>";

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
		$col['req_qty'],		//8
		$col['turn_qty'],		//9
		$col['last_turn_code'],		//10
		$col['last_turn_date'],		//11
		$col['go_page_req'],		//12
		$col['go_page_turn']		//13	
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
$ggTotal = array(0,0,0);

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
			<th width="6%">REQ<br />(Pcs)</th>
			<th width="6%">TURN<br />(Pcs)</th>
			<th width="6%">BAL<br />(Pcs)</th>
			<th width="9%">Last RD No.</th>
			<th width="7%">Last RD Date</th>
		</tr>\n
END;

	$print_tr_1 = 0;
	$gTotal		= array(0,0,0);
	print "<tr>\n";
	//ITEM
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".trim($rd[$rdIdx][1])."] ".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');	//Model No

		$total		= array(0,0,0);
		$print_tr_2 = 0;
		//IDX
		foreach($group2 as $total3 => $group3) {

			if($print_tr_2++ > 0) print "<tr>\n";
			cell_link("<span class=\"bar\">".$rd[$rdIdx][3]."</span>", ' valign="top" align="center"',
				' href="'.$rd[$rdIdx][12].'"');										//Document no
			cell($rd[$rdIdx][5], ' valign="top" align="center"');					//Document date
			cell("[".trim($rd[$rdIdx][6])."] ".$rd[$rdIdx][7], ' valign="top"');	//Customer / Event
			cell(number_format((double)$rd[$rdIdx][8],2), ' align="right"');		// Request qty
			cell(number_format((double)$rd[$rdIdx][9],2), ' align="right"');		// Return qty
			cell(number_format((double)$rd[$rdIdx][8]-$rd[$rdIdx][9],2), ' align="right"');		// Balance qty
			cell_link("<span class=\"bar\">".$rd[$rdIdx][10]."</span>", ' valign="top" align="center"',
				' href="'.$rd[$rdIdx][13].'"');										// Last RD No
			cell($rd[$rdIdx][11], ' align="center"');								// Last RD Date
			print "</tr>\n";

			$total[0] += $rd[$rdIdx][8];
			$total[1] += $rd[$rdIdx][9];
			$total[2] += $rd[$rdIdx][8]-$rd[$rdIdx][9];
			$rdIdx++;
		}
		print "<tr>\n";
		cell("$total2", ' colspan="3" align="right" style="color:darkblue"');
		for($i=0; $i<3; $i++)
			cell(number_format((double)$total[$i],2), ' align="right" style="color:darkblue"');

		cell('');
		cell('');
		print "</tr>\n";

		for($i=0; $i<3; $i++)
			$gTotal[$i] += $total[$i];
	}

	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="4" align="right" style="color:brown; background-color:lightyellow"');
	for($i=0; $i<3; $i++)
		cell(number_format((double)$gTotal[$i],2), ' align="right" style="color:brown; background-color:lightyellow"');
	cell('',' style="color:brown; background-color:lightyellow"');
	cell('',' style="color:brown; background-color:lightyellow"');
	print "</tr>\n";

	print "</table><br />\n";
	
	for($i=0; $i<3; $i++)
		$ggTotal[$i] += $gTotal[$i];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_f">
	<tr>
		<th width="15%">MODEL NO</th>
		<th width="13%">REQUEST NO.</th>
		<th width="10%">REQUEST DATE</th>
		<th>CUSTOMER / EVENT</th>
		<th width="6%">REQ<br />(Pcs)</th>
		<th width="6%">TURN<br />(Pcs)</th>
		<th width="6%">BAL<br />(Pcs)</th>
		<th width="9%">Last RD No.</th>
		<th width="7%">Last RD Date</th>
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="4" align="right" style="color:brown; background-color:lightyellow"');
for($i=0; $i<3; $i++)
	cell(number_format((double)$ggTotal[$i],2), ' align="right" style="color:brown; background-color:lightyellow"');
cell('',' style="color:brown; background-color:lightyellow"');
cell('',' style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>