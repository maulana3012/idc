.<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim
*
* $$
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
  use_code,
  use_request_by,
  to_char(use_request_date, 'dd-Mon-YYYY') AS request_date,
  use_cus_to,
  use_cus_name,
  it_code,
  it_model_no,
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
WHERE " . $strWhere ."
ORDER BY use_code, use_request_date, it_code";

echo "<pre>";
//var_dump($sql);
echo "</pre>";

// raw data
$rd		= array();
$rdIdx	= 0;
$i		= 0;
$cache	= array("","");
$group0 = array();

$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['use_code'],			//0
		$col['use_request_by'],		//1
		$col['request_date'],		//2
		$col['use_cus_to'],			//3
		$col['use_cus_name'],		//4
		$col['it_code'],			//5
		$col['it_model_no'], 		//6
		$col['req_qty'], 			//7
		$col['turn_qty'], 			//8	
		$col['last_turn_code'],		//9	
		$col['last_turn_date'],		//10
		$col['go_page_req'],		//11
		$col['go_page_turn']		//12	
	);

	//1st grouping
	if($cache[0] != $col['use_code']) {
		$cache[0] = $col['use_code'];
		$group0[$col['use_code']] = array();
	}

	if($cache[1] != $col['it_code']) {
		$cache[1] = $col['it_code'];
	}

	$group0[$col['use_code']][$col['it_code']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$ggTotal = array(0,0,0);

//
print <<<END
<table width="100%" class="table_f">
	<tr>
		<th width="9%">REQUEST<br />NO.</th>
		<th width="7%">REQUEST<br />DATE</th>
		<th>CUSTOMER / EVENT</th>
		<th width="8%">REQUEST BY</th>
		<th width="17%">MODEL NO</th>
		<th width="6%">REQ<br />(Pcs)</th>
		<th width="6%">TURN<br />(Pcs)</th>
		<th width="6%">BAL<br />(Pcs)</th>
		<th width="9%">Last RD No.</th>
		<th width="7%">Last RD Date</th>
	</tr>\n
END;

foreach ($group0 as $total1 => $group1) {
	$rowSpan = 0;
	array_walk_recursive($group1, 'getRowSpan');

	print "<tr>\n";
	cell_link("<span class=\"bar\">".$rd[$rdIdx][0]."</span>", ' align="center" valign="" rowspan="'.$rowSpan.'"',
		' href="'.$rd[$rdIdx][11].'"');																// Document no
	cell($rd[$rdIdx][2], ' valign="top" align="center" rowspan="'.$rowSpan.'"');					// Document date
	cell("[".trim($rd[$rdIdx][3])."] ".$rd[$rdIdx][4], ' valign="top" rowspan="'.$rowSpan.'"');		// Customer
	cell(ucfirst($rd[$rdIdx][1]), ' valign="top" rowspan="'.$rowSpan.'"');							// Request by

	$total		= array(0,0,0);
	$print_tr_1 = 0;
	//ITEM
	foreach($group1 as $group2) {
		if($print_tr_1++ > 0) print "<tr>\n";

		cell("[".trim($rd[$rdIdx][5])."] ".$rd[$rdIdx][6]);						// Model No
		cell(number_format((double)$rd[$rdIdx][7],2), ' align="right"');		// Request qty
		cell(number_format((double)$rd[$rdIdx][8],2), ' align="right"');		// Return qty
		cell(number_format((double)$rd[$rdIdx][7]-$rd[$rdIdx][8],2), ' align="right"');		// Balance qty
		cell_link("<span class=\"bar\">".$rd[$rdIdx][9]."</span>", ' align="center" valign=""',
			' href="'.$rd[$rdIdx][12].'"');										// Last RD no
		cell($rd[$rdIdx][10],' align="center" ');								// Last RD Date
		print "</tr>\n";

		$total[0] += $rd[$rdIdx][7];
		$total[1] += $rd[$rdIdx][8];
		$total[2] += $rd[$rdIdx][7]-$rd[$rdIdx][8];
		$rdIdx++;
	}

	print "<tr>\n";
	cell("TOTAL $total1", ' colspan="5" align="right" style="color:darkblue"');
	cell(number_format((double)$total[0],2), ' align="right" style="color:darkblue"');
	cell(number_format((double)$total[1],2), ' align="right" style="color:darkblue"');
	cell(number_format((double)$total[2],2), ' align="right" style="color:darkblue"');
	cell('');
	cell('');
	print "</tr>\n";

	for($i=0; $i<3; $i++)
		$ggTotal[$i] += $total[$i];
}
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$ggTotal[0],2), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$ggTotal[1],2), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$ggTotal[2],2), ' align="right" style="color:brown; background-color:lightyellow"');
cell('', ' align="right" style="color:brown; background-color:lightyellow"');
cell('', ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br />\n";

?>