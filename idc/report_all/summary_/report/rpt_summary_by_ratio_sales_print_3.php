<?php
$amounttotal = array(0,0,0,0,0,0,0);
print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th rowspan="2">TEAM</th>
			<th rowspan="2">CATEGORY ITEM</th>
			<th width="10%">BILLING<br />(Rp)</th>
			<th width="5%">% BILL</th>
			<th width="10%">FULL BILLING<br />(Rp)</th>
			<th width="10%">PAYMENT<br />REALTIME<br />(Rp)</th>
			<th width="10%">REMAIN<br />(Rp)</th>
			<th width="5%">% PAID</th>
			<th width="5%">% REMAIN</th>
		</tr>
		<tr>
			<th>(1)</th>
			<th>(2)</th>
			<th>(3)</th>
			<th>(4)</th>
			<th>(5)</th>
			<th>(6)</th>
			<th>(7)</th>
		</tr>\n
END;
foreach ($channel as $i => $key) {
	$total = array(0,0,0,0,0,0,0);
	print "<tr>\n";	
	cell($channel[$i], ' rowspan="5" valign="top"');

	for($j = 0; $j < count($item); $j++) {
		if($j > 0) print "<tr>\n";	

		cell($item[$j]);
		cell((empty($t['general'][$i][$j][0]) ? 0 : number_format((double)$t['general'][$i][$j][0])), ' align="right"');
		cell((empty($t['general'][$i][$j][2]) ? 0 : number_format((double)$t['general'][$i][$j][2],2)."%"), ' align="right"');
		cell((empty($t['general'][$i][$j][1]) ? 0 : number_format((double)$t['general'][$i][$j][1])), ' align="right"');
		cell((empty($t['realtime'][$i][$j][3]) ? 0 : number_format((double)$t['realtime'][$i][$j][3])), ' align="right"');
		cell((empty($t['realtime'][$i][$j][4]) ? 0 : number_format((double)$t['realtime'][$i][$j][4])), ' align="right"');
		cell((empty($t['realtime'][$i][$j][5]) ? 0 : number_format((double)$t['realtime'][$i][$j][5],2)."%"), ' align="right"');
		cell((empty($t['realtime'][$i][$j][6]) ? 0 : number_format((double)$t['realtime'][$i][$j][6],2)."%"), ' align="right"');
		print "</tr>\n";

		$total[0] += (empty($t['general'][$i][$j][0])) ? 0 : $t['general'][$i][$j][0];
		$total[1] += (empty($t['general'][$i][$j][2])) ? 0 : $t['general'][$i][$j][2];
		$total[2] += (empty($t['general'][$i][$j][1])) ? 0 : $t['general'][$i][$j][1];
		$total[3] += (empty($t['realtime'][$i][$j][3])) ? 0 : $t['realtime'][$i][$j][3];
		$total[4] += (empty($t['realtime'][$i][$j][4])) ? 0 : $t['realtime'][$i][$j][4];
		$total[5] += (empty($t['realtime'][$i][$j][5])) ? 0 : $t['realtime'][$i][$j][5];
		$total[6] += (empty($t['realtime'][$i][$j][6])) ? 0 : $t['realtime'][$i][$j][6];

	}

	print "<tr>\n";	
	cell("<b>Total ".$channel[$i]." </b>", ' align="right" style="color:brown;"');
	cell(number_format((double)$total[0]), ' align="right" style="color:brown;"');
	cell(number_format((double)$total[1],2)."%", ' align="right" style="color:brown;"');
	cell(number_format((double)$total[2]), ' align="right" style="color:brown;"');
	cell(number_format((double)$total[3]), ' align="right" style="color:brown;"');
	cell(number_format((double)$total[4]), ' align="right" style="color:brown;"');
	cell("");
	cell("");

	for($k=0; $k<7; $k++) {
		$amounttotal[$k] += $total[$k];
	}
	print "</tr>\n";

}
print "\t<tr>\n";
cell("<b>TOTAL</b>", ' colspan="2" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$amounttotal[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell("", ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$amounttotal[2]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$amounttotal[3]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$amounttotal[4]), ' align="right" style="color:brown; background-color:lightyellow"');
cell("", ' style="color:brown; background-color:lightyellow"');
cell("", ' style="color:brown; background-color:lightyellow"');
print "\t</tr>\n";
print "</table><br />\n";
?>