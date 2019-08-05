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

$tmp = array();

//SET WHERE PARAMETER
if(ZKP_FUNCTION == 'ALL') {
	if($_order_by != 'all'){
		$tmp[]	= "dt_ordered_by = $_order_by";
	}
} else {
	$tmp[]	= "dt_ordered_by = ".$cboFilter[1][ZKP_FUNCTION][0][0];
}

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if ($_cug_code != 'all') {
	$tmp[]		= "dt_cus_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
}

if ($some_date != "") {
	$tmp[] = "dt_date = DATE '$some_date'";
} else {
	$tmp[] = "dt_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
}

if($_cus_code != "") {
	$tmp[] = "dt_cus_to = '$_cus_code'";
}

$tmp[]	= "dt_dept = '$department'";

$strWhere = implode(" AND ", $tmp);

$sql = "
SELECT
  icat_midx,
  icat_pidx,
  it_code,
  it_model_no,
  it_desc,
  dt_code,
  dt_date,
  to_char(dt_issued_date, 'dd-Mon-yy') as dt_issued_date,
  cus_code,
  cus_full_name,
  dt_issued_by AS request_by,
  dtit_idx,
  dtit_qty,
  ".ZKP_SQL."_getRDTQty(dt_code, it_code, NULL) AS return_qty,
  ".ZKP_SQL."_getLastRTCode(dt_code, it_code) AS last_rt_code,
  to_char(".ZKP_SQL."_getLastRTDate(dt_code, it_code), 'dd-Mon-yy') AS last_rt_date,
  'revise_dt.php?_code='||dt_code AS go_page,
  'revise_return_dt.php?_code='||".ZKP_SQL."_getLastRTCode(dt_code, it_code) AS go_return_page
FROM
  	".ZKP_SQL."_tb_customer AS c
    JOIN ".ZKP_SQL."_tb_dt AS dt ON c.cus_code = dt.dt_cus_to
	JOIN ".ZKP_SQL."_tb_dt_item AS dtit USING(dt_code)
    JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
    JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx) 
WHERE ". $strWhere ."
ORDER BY icat_pidx, icat_midx, it_code, dt_code, dtit_idx";
/*
echo "<pre>";
echo $sql;
echo "</pre>";
*/
// raw data
$rd = array();
$rdIdx = 0;
$cache = array("","","","");
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'],		//0
		$col['it_code'],		//1
		$col['it_model_no'],	//2
		$col['it_desc'],		//3
		$col['dt_code'],		//4
		$col['dt_issued_date'],	//5
		$col['cus_code'],		//6
		$col['cus_full_name'],	//7
		$col['request_by'],		//8
		$col['dtit_idx'],		//9
		$col['dtit_qty'],		//10
		$col['return_qty'],		//11
		$col['last_rt_code'],	//12
		$col['last_rt_date'],	//13
		$col['go_page'],		//14
		$col['go_return_page']	//15
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

	if($cache[2] != $col['dt_code']) {
		$cache[2] = $col['dt_code'];
		$group0[$col['icat_midx']][$col['it_code']][$col['dt_code']] = array();
	}
	

	if($cache[3] != $col['dtit_idx']) {
		$cache[3] = $col['dtit_idx'];
	}

	$group0[$col['icat_midx']][$col['it_code']][$col['dt_code']][$col['dtit_idx']] = 1;
}
/*
echo "<pre>";
var_dump($group0);
echo "</pre>";
*/
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
			<th width="10%">DT NO#</th>
			<th width="8%">DT DATE</th>
			<th>CUSTOMER</th>
			<th width="10%">REQUEST BY</th>
			<th width="5%">DT QTY<br />(Pcs)</th>
			<th width="5%">RT QTY<br />(Pcs)</th>
			<th width="5%">BAL<br />(Pcs)</th>
			<th width="10%">Last RT No.</th>
			<th width="8%">Last RT Date</th>
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
		cell("[".$rd[$rdIdx][1]."] ".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');	//Model No

		$total		= array(0,0,0);
		$print_tr_2 = 0;
		//IDX
		foreach($group2 as $total3 => $group3) {
			$rowSpan = 0;
			array_walk_recursive($group3, 'getRowSpan');

			cell_link("<span class=\"bar\">".$rd[$rdIdx][4]."</span>", ' valign="top" align="center"',
				' href="'.$rd[$rdIdx][14].'"');										//DT No
			cell($rd[$rdIdx][5], ' valign="top" align="center"');					//DT Date
			cell("[".trim($rd[$rdIdx][6])."] ".$rd[$rdIdx][7], ' valign="top"');	//Customer to
			cell($rd[$rdIdx][8], ' valign="top"');									//Request by

			$print_tr_3 = 0;
			//IDX
			foreach($group3 as $total4 => $group4) {
				if($print_tr_3++ > 0) print "<tr>\n";
				$bal_qty	= $rd[$rdIdx][10]-$rd[$rdIdx][11];
	
				cell(number_format((double)$rd[$rdIdx][10]), ' align="right"');				//DT qty
				cell(number_format((double)$rd[$rdIdx][11]), ' align="right"');				//RT qty 
				if($bal_qty < 0)
					cell(number_format((double)$bal_qty), ' align="right" style="color:red"');
				else cell(number_format((double)$bal_qty), ' align="right"');				//Balance
				cell_link("<span class=\"bar\">".$rd[$rdIdx][12]."</span>", ' align="center"', 
					' href="'.$rd[$rdIdx][15].'"');									//last RT code
				cell($rd[$rdIdx][13], ' align="center"');							//last RT date
				print "</tr>\n";

				$total[0] += $rd[$rdIdx][10];
				$total[1] += $rd[$rdIdx][11];
				$total[2] += $bal_qty;	
				$rdIdx++;
			}
		}
		print "<tr>\n";
		cell("$total2", ' colspan="4" align="right" style="color:darkblue"');
		cell(number_format((double)$total[0]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$total[1]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$total[2]), ' align="right" style="color:darkblue"');
		cell('');
		cell('');
		print "</tr>\n";

		$gTotal[0] += $total[0];
		$gTotal[1] += $total[1];
		$gTotal[2] += $total[2];
	}

	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$gTotal[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$gTotal[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$gTotal[2]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell('',' style="color:brown; background-color:lightyellow"');
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
		<th width="10%">DT NO#</th>
		<th width="8%">DT DATE</th>
		<th>CUSTOMER</th>
		<th width="10%">REQUEST BY</th>
		<th width="5%">DT QTY<br />(Pcs)</th>
		<th width="5%">RT QTY<br />(Pcs)</th>
		<th width="5%">BAL<br />(Pcs)</th>
		<th width="10%">Last RT No.</th>
		<th width="8%">Last RT Date</th>
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$ggTotal[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$ggTotal[1]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$ggTotal[2]), ' align="right" style="color:brown; background-color:lightyellow"');
cell('',' style="color:brown; background-color:lightyellow"');
cell('',' style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>