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
	$tmp[]   = "rjde_deleted_timestamp BETWEEN TIMESTAMP '$some_date 00:00:00' AND '$some_date 23:59:59'";
} else {
	$tmp[]   = "rjde_deleted_timestamp BETWEEN TIMESTAMP '$period_from 00:00:00' AND '$period_to 23:59:59'";
}

$strWhere	= implode(" AND ", $tmp);

$sql = "
SELECT
  to_char(rjde_deleted_timestamp, 'YYMM') AS year_month,
  to_char(rjde_deleted_timestamp, 'Mon, YYYY') AS month,
  EXTRACT(WEEK FROM rjde_deleted_timestamp) AS week,
  to_char(rjde_deleted_timestamp, 'dd/Mon/yy') AS date,
  to_char(rjde_deleted_timestamp, 'hh24:mi:ss') AS timestamp,
  rjde_idx,
  it_code,
  it_model_no,
  rjde_qty,
  rjde_deleted_by_account,
  to_char(rjde_warranty, 'Month- yy') AS ed,
  rjde_desc
FROM
 ".ZKP_SQL."_tb_reject_demo 
 JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
WHERE " . $strWhere ."
ORDER BY year_month, week, date, rjde_idx";

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
		$col['timestamp'],		//3
		$col['rjde_idx'],		//4
		$col['it_code'],		//5
		$col['it_model_no'], 	//6
		$col['rjde_qty'],		//7
		$col['rjde_deleted_by_account'],	//8
		$col['ed'],				//9
		$col['rjde_desc'] 		//10
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

	if($cache[3] != $col['rjde_idx']) {
		$cache[3] = $col['rjde_idx'];
	}

	$group0[$col['month']][$col['week']][$col['date']][$col['rjde_idx']] = 1;
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
		<tr height="25px">
			<th width="15%">DELETED DATE</th>
			<th width="20%">MODEL NO</th>
			<th width="15%">E/D</th>
			<th>REMARK</th>
			<th width="7%">QTY</th>
			<th width="15%">DELETED BY</th>
		</tr>\n
END;

	//monthly summary
	$monthly_summary = 0;
	$weekth = array();
	foreach ($month as $week_name => $weekk) {

		$weekth = getWeek($rd[$rdIdx][1] * 604800); 	//7day * 24 hour * 60 Min * 60 Sec

		print "<tr>\n";
		print "<td colspan=\"6\">{$weekth['string']}</td>\n";
		print "</tr>\n";

		$print_tr_1 = 0;
		//weekly summary
		$weekly_summary = 0;
		foreach ($weekk as $move => $item) {
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
				cell($rd[$rdIdx][9], ' valign="top"');						//E/D
				cell($rd[$rdIdx][10], ' valign="top"');						//remark
				cell(number_format($rd[$rdIdx][7],2), ' align="right"');	//qty
				cell($rd[$rdIdx][8].' / '.$rd[$rdIdx][3]);					//deleted by
				print "</tr>\n";

				$date	= $rd[$rdIdx][2];
				$total += $rd[$rdIdx][7];
				$rdIdx++;
			}

			print "<tr>\n";
			cell($date, ' colspan="3"  align="right" align="right" style="color:darkblue"');
			cell(number_format($total,2), ' align="right" style="color:darkblue"');
			cell("&nbsp", ' align="right" style="color:darkblue"');
			print "</tr>\n";

			$weekly_summary += $total;
		}

		print "<tr>\n";
		cell($weekth['string'], ' colspan="4"  align="right" style="color:darkblue"');
		cell(number_format($weekly_summary,2), ' align="right" style="color:darkblue"');
		cell("&nbsp", ' align="right" style="color:darkblue"');
		print "</tr>\n";

		$monthly_summary += $weekly_summary;
	}

	print "<tr>\n";
	cell('<b>'.$month_name.'<b>', ' colspan="4"  align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($monthly_summary,2), ' align="right" style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br>\n";

	$grand_total += $monthly_summary;
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="80%" class="table_f">
	<tr height="25px">
		<th width="15%">DELETED DATE</th>
		<th width="20%">MODEL NO</th>
		<th width="15%">E/D</th>
		<th>REMARK</th>
		<th width="7%">QTY</th>
		<th width="15%">DELETED BY</th>
	</tr>\n
END;
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="4"  align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total,2), ' align="right" style="color:brown; background-color:lightyellow"');
cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br>\n";
?>