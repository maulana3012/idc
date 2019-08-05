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

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp[] = "e.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if ($some_date != "") {
	$tmp[]   = "rjde_deleted_timestamp BETWEEN TIMESTAMP '$some_date 00:00:00' AND '$some_date 23:59:59'";
} else {
	$tmp[]   = "rjde_deleted_timestamp BETWEEN TIMESTAMP '$period_from 00:00:00' AND '$period_to 23:59:59'";
}

$strWhere	= implode(" AND ", $tmp);

$sql = "
SELECT
  icat_pidx AS icat_pidx,
  icat_midx AS icat_midx,
  it_code,
  it_model_no,
  rjde_idx,
  rjde_qty,
  rjde_deleted_by_account,
  rjde_deleted_timestamp,
  to_char(rjde_warranty, 'Month- yy') AS ed,
  to_char(rjde_deleted_timestamp, 'dd-Mon-yy') AS date,
  to_char(rjde_deleted_timestamp, 'dd/Mon/yy hh24:mi:ss') AS timestamp,
  rjde_desc
FROM
 ".ZKP_SQL."_tb_reject_demo 
 JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS e USING(icat_midx)
WHERE " . $strWhere ."
ORDER BY icat_pidx, icat_midx, it_code, rjde_deleted_timestamp";

echo "<pre>";
//echo $sql;
echo "</pre>";

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
		$col['rjde_idx'],		//3
		$col['rjde_qty'],		//4
		$col['rjde_deleted_by_account'],	//5
		$col['ed'],				//6
		$col['date'],			//7
		$col['timestamp'],		//8
		$col['rjde_desc'] 		//9
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

	if($cache[2] != $col['rjde_idx']) {
		$cache[2] = $col['rjde_idx'];
	}

	$group0[$col['icat_midx']][$col['it_code']][$col['rjde_idx']] = 1;
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
		<tr height="25px">
			<th width="20%">MODEL NO</th>
			<th width="18%">DELETED DATE</th>
			<th width="15%">E/D</th>
			<th>REMARK</th>
			<th width="7%">QTY</th>
			<th width="15%">DELETED BY</th>
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
			cell($rd[$rdIdx][8], ' align="center"');	//deleted date
			cell($rd[$rdIdx][6]);						//E/D
			cell($rd[$rdIdx][9]);						//remark
			cell(number_format($rd[$rdIdx][4],2), ' align="right"');	//qty
			cell($rd[$rdIdx][5], ' valign="top"');		//deleted by
			print "</tr>\n";

			$it_code	= trim($rd[$rdIdx][1]);
			$model_no	= $rd[$rdIdx][2];
			$model_total += $rd[$rdIdx][4];
			$rdIdx++;
		}

		print "<tr>\n";
		cell("[$it_code]$model_no", ' colspan="3" align="right" style="color:darkblue"');
		cell(number_format($model_total,2), ' align="right" style="color:darkblue"');
		cell('');
		print "</tr>\n";

		$cat_total += $model_total;
	}
	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="4" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($cat_total,2), ' align="right" style="color:brown; background-color:lightyellow"');
	cell('',' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$grand_total += $cat_total;
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="80%" class="table_f">
	<tr height="25px">
		<th width="20%">MODEL NO</th>
		<th width="18%">DELETED DATE</th>
		<th width="15%">E/D</th>
		<th>REMARK</th>
		<th width="7%">QTY</th>
		<th width="15%">DELETED BY</th>
	</tr>\n
END;
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="4" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total,2), ' align="right" style="color:brown; background-color:lightyellow"');
cell('',' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br />\n";
?>