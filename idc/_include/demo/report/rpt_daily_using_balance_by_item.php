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

$strWhere = implode(" AND ", $tmp);

$sql = "
SELECT
  icat_midx,
  icat_pidx,
  it_code,
  it_model_no,
  use_code,
  use_request_by,
  to_char(use_request_date,'dd-Mon-YY') AS request_date,
  to_char(use_cfm_marketing_timestamp, 'dd-Mon-YY') AS confirm_date,
  use_cus_to,
  use_cus_name,
  CASE
	WHEN usit_returnable IS TRUE then 'Yes'
	WHEN usit_returnable IS FALSE then 'No'
  END AS is_returnable,
  usit_qty AS request_qty,
  (SELECT sum(rdit_qty) FROM ".ZKP_SQL."_tb_using_demo JOIN ".ZKP_SQL."_tb_return_demo USING(use_code) JOIN ".ZKP_SQL."_tb_return_demo_item USING(red_code) WHERE use_code=b.use_code AND it_code=c.it_code) AS return_qty,
  to_char(".ZKP_SQL."_getLastReturnDate(use_code, it_code), 'dd-Mon-YY') AS last_return_date,
  'confirm_request.php?_code=' || use_code AS go_page,
  use_request_date
FROM
 ".ZKP_SQL."_tb_using_demo AS a
 JOIN ".ZKP_SQL."_tb_using_demo_item AS b USING(use_code)
 JOIN ".ZKP_SQL."_tb_item AS c USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS e USING(icat_midx) 
WHERE ". $strWhere ."
ORDER BY icat_pidx, icat_midx, it_code, use_request_date, use_code";
/*
echo "<pre>";
echo $sql;
echo "</pre>";
*/
$rd = array();
$rdIdx = 0;
$cache = array("","","");
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'],			//0
		$col['it_code'],			//1
		$col['it_model_no'],		//2
		$col['use_code'],			//3
		$col['use_request_by'],		//4
		$col['request_date'],		//5
		$col['confirm_date'],		//6
		$col['use_cus_to'],			//7
		$col['use_cus_name'],		//8
		$col['is_returnable'],		//9
		$col['request_qty'],		//10
		$col['return_qty'],			//11
		$col['last_return_date'],	//12
		$col['go_page']				//13
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
			<th width="8%">REQUEST DATE</th>
			<th width="8%">CONFIRM DATE</th>
			<th>CUSTOMER / EVENT</th>
			<th width="7%">RETURNABLE</th>	
			<th width="7%">USE<br />QTY</th>
			<th width="7%">RTRN<br />QTY</th>
			<th width="7%">BAL<br />QTY</th>
			<th width="8%">LAST<br />RTRN DATE</th>
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
			$bal_qty		= $rd[$rdIdx][10]-$rd[$rdIdx][11];

			cell_link("<span class=\"bar\">".$rd[$rdIdx][3]."</span>", ' valign="top" align="center"',
				' href="'.$rd[$rdIdx][13].'"');										//Request no
			cell($rd[$rdIdx][5], ' valign="top" align="center"');					//Request date
			cell($rd[$rdIdx][6], ' valign="top" align="center"');					//Confirm date
			cell("[".trim($rd[$rdIdx][7])."] ".$rd[$rdIdx][8], ' valign="top"');	//Customer / Event
			cell($rd[$rdIdx][9], ' align="center"');								//Returnable
			cell(number_format($rd[$rdIdx][10],2), ' align="right"');				//Request qty
			cell(($rd[$rdIdx][11]!='')? number_format($rd[$rdIdx][11],2) : '-', ' align="right"');	//Return qty
			cell(number_format($bal_qty,2), ' align="right"');					//Balance qty
			cell($rd[$rdIdx][12], ' align="center"');							//Last Retun Date
			print "</tr>\n";

			$total[0] += $rd[$rdIdx][10];
			$total[1] += $rd[$rdIdx][11];
			$total[2] += $bal_qty;
			$rdIdx++;
		}
		print "<tr>\n";
		cell("$total2", ' colspan="5" align="right" style="color:darkblue"');
		cell(number_format($total[0],2), ' align="right" style="color:darkblue"');
		cell(number_format($total[1],2), ' align="right" style="color:darkblue"');
		cell(number_format($total[2],2), ' align="right" style="color:darkblue"');
		cell('');
		print "</tr>\n";

		$gTotal[0] += $total[0];
		$gTotal[1] += $total[1];
		$gTotal[2] += $total[2];
	}

	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="6" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($gTotal[0],2), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($gTotal[1],2), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($gTotal[2],2), ' align="right" style="color:brown; background-color:lightyellow"');
	cell('',' style="color:brown; background-color:lightyellow"');
	print "</tr>\n";

	print "</table><br />\n";
	
	$ggTotal[0] += $gTotal[0];
	$ggTotal[1] += $gTotal[1];
	$ggTotal[2] += $gTotal[2];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_f">
	<tr>
		<th width="15%">MODEL NO</th>
		<th width="13%">REQUEST NO.</th>
		<th width="8%">REQUEST DATE</th>
		<th width="8%">CONFIRM DATE</th>
		<th>CUSTOMER / EVENT</th>
		<th width="7%">RETURNABLE</th>	
		<th width="7%">USE<br />QTY</th>
		<th width="7%">RTRN<br />QTY</th>
		<th width="7%">BAL<br />QTY</th>
		<th width="8%">LAST<br />RTRN DATE</th>
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="6" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTotal[0],2), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTotal[1],2), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTotal[2],2), ' align="right" style="color:brown; background-color:lightyellow"');
cell('',' style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>