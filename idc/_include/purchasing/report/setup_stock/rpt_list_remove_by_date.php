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
  to_char(mv_timestamp, 'YYMM') AS year_month,
  to_char(mv_timestamp, 'Mon, YYYY') AS month,
  EXTRACT(WEEK FROM mv_timestamp) AS week,
  to_char(mv_timestamp, 'dd/Mon/yy') AS date,
  mv_idx AS idx,
  it_code,
  it_model_no,
  it_code || mv_idx AS item_moved, 
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
  mv_by_account AS log_by_account
FROM
 ".ZKP_SQL."_tb_move_stock 
 JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
WHERE " . $strWhere ."
ORDER BY year_month, week, date, item_moved";
//echo $sql;
// raw data
$rd = array();
$rdIdx = 0;
$cache = array("","","","");
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['month'],			//0
		$col['week'],			//1
		$col['date'],			//2
		$col['item_moved'],		//3
		$col['idx'],			//4
		$col['it_code'],		//5
		$col['it_model_no'], 	//6
		$col['timestamp'],		//7
		$col['log_by_account'],	//8
		$col['from_location'],	//9
		$col['to_location'],	//10
		$col['from_type'], 		//11
		$col['to_type'],		//12
		$col['qty'],			//13
		$col['remark']	 		//14
	);

	//1st grouping
	if($cache[0] != $col['month']) {
		$cache[0] = $col['month'];
		$group0[$col['month']] = array();
	}

	if($cache[1] != $col['week']) {
		$cache[1] = $col['week'];
		$group0[$col['month']][$col['week']] = array();
	}

	if($cache[2] != $col['date']) {
		$cache[2] = $col['date'];
		$group0[$col['month']][$col['week']][$col['date']] = array();
	}

	if($cache[3] != $col['item_moved']) {
		$cache[3] = $col['item_moved'];
	}

	$group0[$col['month']][$col['week']][$col['date']][$col['item_moved']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$grand_total = 0;

//GROUP BY MONTH
foreach ($group0 as $month_name => $month) {
	echo "<span class=\"comment\"><b>[". $month_name. "]</b></span>\n";
	print <<<END
	<table width="80%" class="table_f">
		<tr>
			<th width="10%" rowspan="2">MOVE<br />DATE</th>
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

	//monthly summary
	$monthly_summary = 0;
	$weekth = array();
	foreach ($month as $week_name => $week) {

		$weekth = getWeek($rd[$rdIdx][1] * 604800); 	//7day * 24 hour * 60 Min * 60 Sec

		print "<tr>\n";
		print "<td colspan=\"15\">{$weekth['string']}</td>\n";
		print "</tr>\n";

		$print_tr_1 = 0;
		//weekly summary
		$weekly_summary = 0;
		foreach ($week as $move => $item) {
			$rowSpan = 0;
			array_walk_recursive($item, 'getRowSpan');
			$rowSpan += 1;
			
			if($print_tr_1++ > 0) print "<tr>\n";
			cell($rd[$rdIdx][2], ' align="center" valign="top" rowspan="'.$rowSpan.'"');

			$total = 0;
			$print_tr_2 = 0;
			//item
			foreach ($item as $value) {
				if($print_tr_2++ > 0) print "<tr>\n";
				cell("[".trim($rd[$rdIdx][5])."] ".$rd[$rdIdx][6], ' valign="top"');	//model no
				cell($rd[$rdIdx][9], ' align="center" valign="top"');			//from location
				cell('<img src="../../_images/icon/arrow_right_disabled.gif" width="10px">', ' align="center"');							//Reference date
				cell($rd[$rdIdx][10], ' align="center" valign="top"');			//to location
				cell($rd[$rdIdx][11], ' align="center" valign="top"');			//type
				cell(number_format($rd[$rdIdx][13],2), ' align="right"');		//qty
				cell($rd[$rdIdx][14]);											//remark
				cell('input by '. $rd[$rdIdx][8], ' valign="top"');				//by
				print "</tr>\n";

				$date	= $rd[$rdIdx][2];
				$total += $rd[$rdIdx][13];
				$rdIdx++;
			}

			print "<tr>\n";
			cell($date, ' colspan="5"  align="right" align="right" style="color:darkblue"');
			cell(number_format($total,2), ' align="right" style="color:darkblue"');
			cell("&nbsp", ' align="right" style="color:darkblue"');
			cell("&nbsp", ' align="right" style="color:darkblue"');
			print "</tr>\n";

			$weekly_summary += $total;
		}

		print "<tr>\n";
		cell($weekth['string'], ' colspan="6"  align="right" style="color:darkblue"');
		cell(number_format($weekly_summary,2), ' align="right" style="color:darkblue"');
		cell("&nbsp", ' align="right" style="color:darkblue"');
		cell("&nbsp", ' align="right" style="color:darkblue"');
		print "</tr>\n";

		$monthly_summary += $weekly_summary;
	}

	print "<tr>\n";
	cell('<b>'.$month_name.'<b>', ' colspan="6"  align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($monthly_summary,2), ' align="right" style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br>\n";

	$grand_total += $monthly_summary;
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="80%" class="table_f">
	<tr>
		<th width="10%" rowspan="2">MOVE<br />DATE</th>
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
cell("<b>GRAND TOTAL</b>", ' colspan="6"  align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total,2), ' align="right" style="color:brown; background-color:lightyellow"');
cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br>\n";
?>