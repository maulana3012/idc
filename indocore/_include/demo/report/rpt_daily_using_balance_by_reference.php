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

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp[] = "e.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
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

if($_type != 'all') {
	$tmp[]	= "use_type = '$_type'";
}

if($currentDept == 'report' || $currentDept == 'report_all') {
	if($_status == 'uncfm') {
		$tmp[]	= "use_cfm_marketing_timestamp IS NULL";
	} else if($_status == 'cfm') {
		$tmp[]	= "use_cfm_marketing_timestamp IS NOT NULL";
	}
}

$strWhere = implode(" AND ", $tmp);

$sql = "
SELECT 
  use_code,
  use_request_by,
  to_char(use_request_date, 'dd-Mon-YY') AS request_date,
  to_char(use_cfm_marketing_timestamp, 'dd-Mon-YY') AS confirm_date,
  use_cus_to,
  use_cus_name,
  it_code,
  it_model_no,
  CASE
	WHEN usit_returnable IS TRUE then 'Yes'
	WHEN usit_returnable IS FALSE then 'No'
  END AS is_returnable,
  usit_qty AS request_qty,
  (SELECT sum(rdit_qty) FROM ".ZKP_SQL."_tb_using_demo JOIN ".ZKP_SQL."_tb_return_demo USING(use_code) JOIN ".ZKP_SQL."_tb_return_demo_item USING(red_code) WHERE use_code=b.use_code AND it_code=c.it_code) AS return_qty,
  to_char(".ZKP_SQL."_getLastReturnDate(use_code, it_code), 'dd-Mon-YY') AS last_return_date,
  'confirm_request.php?_code=' || use_code AS go_page
FROM
 ".ZKP_SQL."_tb_using_demo AS a
 JOIN ".ZKP_SQL."_tb_using_demo_item AS b USING(use_code)
 JOIN ".ZKP_SQL."_tb_item AS c USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS e USING(icat_midx) 
WHERE " . $strWhere ."
ORDER BY use_request_date, use_code, it_code";

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
		$col['confirm_date'],		//3
		$col['use_cus_to'],			//4
		$col['use_cus_name'],		//5
		$col['it_code'],			//6
		$col['it_model_no'], 		//7
		$col['is_returnable'], 		//8
		$col['request_qty'], 		//9
		$col['return_qty'], 		//10
		$col['last_return_date'], 	//11
		$col['go_page']				//12
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
$ggTotal	= array(0,0,0);

//
print <<<END
<table width="100%" class="table_f">
	<tr>
		<th>CUSTOMER / EVENT</th>
		<th width="12%">REQUEST NO.</th>
		<th width="8%">REQUEST DATE</th>
		<th width="8%">CONFIRM DATE</th>
		<th width="20%">MODEL NO</th>
		<th width="7%">RETURNABLE</th>
		<th width="7%">USE<br />QTY</th>
		<th width="7%">RTRN<br />QTY</th>
		<th width="7%">BAL<br />QTY</th>
		<th width="8%">LAST<br />RTRN DATE</th>
	</tr>\n
END;

foreach ($group0 as $total1 => $group1) {
	$rowSpan = 0;
	array_walk_recursive($group1, 'getRowSpan');

	print "<tr>\n";
	cell("[".trim($rd[$rdIdx][4])."] ".$rd[$rdIdx][5], ' valign="top" rowspan="'.$rowSpan.'"');		//Customer /Event
	cell_link("<span class=\"bar\">".$rd[$rdIdx][0]."</span>", ' align="center" valign="" rowspan="'.$rowSpan.'"',
		' href="'.$rd[$rdIdx][12].'"');													//Request no
	cell($rd[$rdIdx][2], ' valign="top" align="center" rowspan="'.$rowSpan.'"');		//Request date
	cell($rd[$rdIdx][3], ' valign="top" align="center" rowspan="'.$rowSpan.'"');		//Confirm date

	$total		= array(0,0,0);
	$print_tr_1 = 0;
	//ITEM
	foreach($group1 as $group2) {
		if($print_tr_1++ > 0) print "<tr>\n";
		$bal_qty		= $rd[$rdIdx][9]-$rd[$rdIdx][10];

		cell("[".trim($rd[$rdIdx][6])."] ".$rd[$rdIdx][7]);						//Model No
		cell($rd[$rdIdx][8], ' align="center"');								//Returnable
		cell(number_format($rd[$rdIdx][9],2), ' align="right"');					//Request qty
		cell(($rd[$rdIdx][10]!='')? number_format($rd[$rdIdx][10],2) : '-', ' align="right"');	//Return qty
		cell(number_format($bal_qty,2), ' align="right"');						//Balance qty
		cell($rd[$rdIdx][11], ' align="center"');								//Last return date
		print "</tr>\n";

		$total[0] += $rd[$rdIdx][9];
		$total[1] += $rd[$rdIdx][10];
		$total[2] += $bal_qty;
		$rdIdx++;
	}

	print "<tr>\n";
	cell("TOTAL $total1", ' colspan="6" align="right" style="color:darkblue"');
	cell(number_format($total[0],2), ' align="right" style="color:darkblue"');
	cell(number_format($total[1],2), ' align="right" style="color:darkblue"');
	cell(number_format($total[2],2), ' align="right" style="color:darkblue"');
	cell('');
	print "</tr>\n";

	$ggTotal[0] += $total[0];
	$ggTotal[1] += $total[1];
	$ggTotal[2] += $total[2];
}
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="6" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTotal[0],2), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTotal[1],2), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTotal[2],2), ' align="right" style="color:brown; background-color:lightyellow"');
cell('', ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>