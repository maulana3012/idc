<?php
$db = array("idc", "med");
$j = 0;
for($i=0; $i<2; $i++) {
	$sp_sql[$i] = "
	SELECT 
	  CASE
		WHEN icat_midx IN(1,70,116,146,176,240,125,59,341) THEN 'Healthcare Tools'
		WHEN icat_midx IN(46,232,282,252,109) THEN 'Diagnostic Tools'
		ELSE 'Others'
	  END AS cat,
	  icat_midx, 
	  icat_name 
	FROM ".$db[$i]."_tb_item_cat WHERE icat_pidx = 0 AND icat_midx > 0";
}

switch (ZKP_URL) {
  case "ALL": $sp_sql = $sp_sql[1]; break;
  case "IDC": $sp_sql = $sp_sql[0]; break;
  case "MED": $sp_sql = $sp_sql[1]; break;
  case "MEP": $sp_sql = $sp_sql[0]; break;
}

$sp_sql .= " ORDER BY cat, icat_name";
/*
echo "<pre>";
echo $sp_sql. "<br /><br />";
echo "</pre>";
*/
$sp_res =& query($sp_sql);
$group0 = array();
$group1 = array();
while($col =& fetchRow($sp_res)) {
	$sp[$col[1]] = array();
	$sp[$col[1]][0] = $col[1];
	$sp[$col[1]][1] = trim($col[2]);
	$sp[$col[1]][2] = $col[0];
	for($i=3; $i<$mon_length+5; $i++) $sp[$col[1]][$i] = 0;
	$group0[] = $col[0];
	$group1[$col[0]][] = array($col[1], $col[2]);
}
$group0 = array('Healthcare Tools','Diagnostic Tools','Others');

foreach($sp as $key => $val) {
	for($i=0; $i<$mon_length; $i++) {

		include "rpt_summary_monthly_bill_supplier_by_amount_print_3.php";

		$res_month =& query($sql_perMonth);
		while($col_month =& fetchRow($res_month)) {
			$sp[$key][3+$i] += $col_month[0];
			$sp[$key][3+$mon_length] += $col_month[0];
		}
	}
	$sp[$key][3+$mon_length+1] = $sp[$key][3+$mon_length] / $mon_length;
}

//SUPPLIER PART
$g_total = array();
print <<<END
<table class="table_f" width="{$table_len}px">
	<tr height="15px">
		<th width="120px" rowspan="2">CATEGORY</th>
		<th colspan="$mon_length">MONTH</th>
		<th width="100px" rowspan="2">TOTAL</th>
		<th width="100px" rowspan="2">AVG</th>
	</tr>
	<tr height="25px">\n
END;
	$start_month = $_month_from;
	$start_year	 = $_year_from;
	for($i=0; $i<$mon_length; $i++) {
		if($start_month>12) { $start_month = $start_month-12; $start_year+=1;}
		echo "\t\t<th width=\"80px\">".$month[$start_month]."<br />$start_year</th>\n";
		$start_month++;
	}
print <<<END
	</tr>\n
END;
foreach ($group0 as $part1 => $part_name) {
print <<<END
	<tr>
		<td colspan="$mon_length"><span class="comment"><b>$part_name</b></span></td>
	</tr>\n
END;

	$tot_sp = count($group1[$part_name]);
	$total = array();
	print "<tr>\n";
	for($i=0; $i<$tot_sp; $i++) {
		$sub = array($group1[$part_name][$i][0], $group1[$part_name][$i][1]);

		cell($sub[1]);
		for($j=3; $j<$mon_length+5; $j++) {
			cell(number_format((double)$sp[$sub[0]][$j]), ' align="right"');
			if(!isset($total[$part1][$j]))	$total[$part1][$j] = $sp[$sub[0]][$j];
			else					 			$total[$part1][$j] += $sp[$sub[0]][$j];
		}
		print "</tr>\n";
	}

	print "<tr>\n";
	cell("<b>TOTAL $part_name</b>", ' align="right" style="color:brown; background-color:lightyellow"');
	for($j=3; $j<$mon_length+3; $j++) {
		cell(number_format((double)$total[$part1][$j]), ' align="right" style="color:brown; background-color:lightyellow"');

		if(!isset($g_total[$j]))	$g_total[$j] = $total[$part1][$j];
		else					 	$g_total[$j] += $total[$part1][$j];
	}
	cell(number_format((double)$total[$part1][$j]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$total[$part1][$j+1]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";

	if(!isset($g_total[$j])) {
		$g_total[$j] = $total[$part1][$j];
		$g_total[$j+1] = $total[$part1][$j+1];
	} else {
		$g_total[$j] += $total[$part1][$j];
		$g_total[$j+1] += $total[$part1][$j+1];
	}
}

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' align="right" style="color:brown; background-color:lightyellow"');
for($j=3; $j<$mon_length+3; $j++) {
	cell(number_format((double)$g_total[$j]), ' align="right" style="color:brown; background-color:lightyellow"');
}
cell(number_format((double)$g_total[$j]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$g_total[$j+1]), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br />";
?>