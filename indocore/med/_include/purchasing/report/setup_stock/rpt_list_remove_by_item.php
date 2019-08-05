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

if ($some_date != "") {
	$tmp[]   = "mv_timestamp BETWEEN TIMESTAMP '$some_date 00:00:00' AND '$some_date 23:59:59'";
} else {
	$tmp[]   = "mv_timestamp BETWEEN TIMESTAMP '$period_from 00:00:00' AND '$period_to 23:59:59'";
}

$strWhere	= implode(" AND ", $tmp);

$sql = "
SELECT
  icat_pidx AS icat_pidx,
  icat_midx AS icat_midx,
  it_code,
  it_model_no,
  mv_idx AS idx,
  CASE
	when mv_from_wh=1 THEN 'IDC'
	when mv_from_wh=2 THEN 'DNR'
  END AS from_location,
  CASE
	when mv_to_wh=1 THEN 'IDC'
	when mv_to_wh=2 THEN 'DNR'
  END AS to_location,
  CASE
	when mv_from_type=1 THEN 'VAT'
	when mv_from_type=2 THEN 'NON'
  END AS from_type,
  CASE
	when mv_to_type=1 THEN 'VAT'
	when mv_to_type=2 THEN 'NON'
  END AS to_type,
  mv_qty AS qty,
  mv_timestamp,
  mv_remark AS remark,
  to_char(mv_timestamp, 'dd-Mon-yy hh24:mi:ss') AS timestamp,
  mv_by_account AS log_by_account
FROM
 ".ZKP_SQL."_tb_move_stock 
 JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS e USING(icat_midx)
WHERE " . $strWhere ."
ORDER BY icat_pidx, icat_midx, it_code, mv_timestamp";

// raw data
$rd = array();
$rdIdx = 0;
$cache = array("","",""); // 3th level
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'],		//0
		$col['it_code'],		//1
		$col['it_model_no'], 	//2
		$col['idx'],			//3
		$col['timestamp'],		//4
		$col['log_by_account'],	//5
		$col['from_location'],	//6
		$col['to_location'],	//7
		$col['from_type'], 		//8
		$col['to_type'],		//9
		$col['qty'],			//10
		$col['remark']	 		//11
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

	if($cache[2] != $col['idx']) {
		$cache[2] = $col['idx'];
	}

	$group0[$col['icat_midx']][$col['it_code']][$col['idx']] = 1;
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
	<table width="80%" class="table_f">
		<tr>
			<th width="18%" rowspan="2">MODEL NO</th>
			<th width="27%" colspan="4" height="15px">ITEM DESCRIPTION</th>
			<th width="8%" rowspan="2">QTY</th>
			<th width="15%" rowspan="2">REMARK</th>
			<th rowspan="2">DESCRIPTION</th>
		</tr>
		<tr>
			<th width="8%">FROM</th>
			<th width="3%"><img src="../../_images/icon/arrow_right.gif"></th>
			<th width="8%">TO</th>
			<th width="8%">TYPE</th>
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
		cell("[".trim($rd[$rdIdx][1])."] ".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');	//model no

		$model_total	= 0;
		$print_tr_2		= 0;
		//ORDER
		foreach($group2 as $total3) {
			if($print_tr_2++ > 0) print "<tr>\n";
			cell($rd[$rdIdx][6], ' align="center" valign="top"');			//from location
			cell('<img src="../../_images/icon/arrow_right_disabled.gif" width="10px">', ' align="center"');							//Reference date
			cell($rd[$rdIdx][7], ' align="center" valign="top"');			//to location
			cell($rd[$rdIdx][8], ' align="center" valign="top"');			//type
			cell(number_format($rd[$rdIdx][10],2), ' align="right"');		//qty
			cell($rd[$rdIdx][11]);											//remark
			cell($rd[$rdIdx][4].', by '. $rd[$rdIdx][5], ' valign="top"');	//date
			print "</tr>\n";

			$it_code	= $rd[$rdIdx][1];
			$model_no	= $rd[$rdIdx][2];
			$model_total += $rd[$rdIdx][10];
			$rdIdx++;
		}

		print "<tr>\n";
		cell("[$it_code]$model_no", ' colspan="4" align="right" style="color:darkblue"');
		cell(number_format($model_total,2), ' align="right" style="color:darkblue"');
		cell('');
		print "</tr>\n";

		$cat_total += $model_total;
	}
	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($cat_total,2), ' align="right" style="color:brown; background-color:lightyellow"');
	cell('',' align="right" style="color:brown; background-color:lightyellow"');
	cell('',' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$grand_total += $cat_total;
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="80%" class="table_f">
	<tr>
		<th width="18%" rowspan="2">MODEL NO</th>
		<th width="27%" colspan="4" height="15px">ITEM DESCRIPTION</th>
		<th width="8%" rowspan="2">QTY</th>
		<th width="15%" rowspan="2">REMARK</th>
		<th rowspan="2">DESCRIPTION</th>
	</tr>
	<tr>
		<th width="8%">FROM</th>
		<th width="3%"><img src="../../_images/icon/arrow_right.gif"></th>
		<th width="8%">TO</th>
		<th width="8%">TYPE</th>
	</tr>\n
END;
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total,2), ' align="right" style="color:brown; background-color:lightyellow"');
cell('',' align="right" style="color:brown; background-color:lightyellow"');
cell('',' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br />\n";
?>