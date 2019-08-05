<?php
//GROUP TOTAL
$grand_total = 0;
$amount_cug	 = array();
//DEPARTEMEN
foreach ($group0 as $total1 => $group1) {

	echo "<span class=\"comment\"><b>{$dept[$rd[$rdIdx][0]]}</b></span>\n";
	print <<<END
	<table width="80%" class="table_f">
		<tr>
			<th width="20%">GROUP</th>
			<th>NAME</th>
			<th width="15%">AMOUNT<br>(Rp)</th>
			<th width="10%">RATE</th>
			<th width="10%">RATE</th>
		</tr>\n
END;
	$dept_amount = ARRAY(0,0); //amount, rate
	$print_tr_1 = 0;

	print "<tr>\n";
	//GROUP
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		$rowSpan += count($group2);

		if($print_tr_1++ > 0) print "<tr>\n";
		cell($rd[$rdIdx][1], ' valign="middle" align="center" rowspan="'.$rowSpan.'"');		//customer group name

		$group_amount = ARRAY(0,0); //amount, rate
		$a = '';
		$b = '';
		$rowSpan = $rowSpan + 1;
		$print_tr_2 = 0;
		//CUSTOMER
		foreach($group2 as $total3 => $group3) {

			$dep_amount = $amount[$rd[$rdIdx][0]];
			$subb_amount = $sub_amount[$rd[$rdIdx][0]][$rd[$rdIdx][1]];
			$cust_amount = $cus_amount[$rd[$rdIdx][0]][$rd[$rdIdx][1]][$rd[$rdIdx][2]];

			if($cust_amount == 0) {
				$rate = 0;
			} else if($cust_amount > 0) {
				$rate = $cust_amount*100/$subb_amount;
				if($rate < 0) $rate = $rate*-1;
			} else if($cust_amount < 0) {
				$rate = -($cust_amount*100/$subb_amount);
				if($rate > 0) $rate = $rate*-1;	
			}

			if($subb_amount == 0) $sub_rate = 0;
			else if($subb_amount > 0) {
				$sub_rate = $subb_amount*100/$dep_amount;
				if($sub_rate < 0) $sub_rate = $sub_rate*-1;
			} else if($subb_amount < 0) {
				$sub_rate = -($subb_amount*100/$dep_amount);
				if($sub_rate > 0) $sub_rate = $sub_rate*-1;
			}

			//PRINT CONTENT
			if($print_tr_2++ > 0) print "<tr>\n";
			cell("[".trim($rd[$rdIdx][2])."] ".$rd[$rdIdx][3], ' valign="top"');		//customer name
			cell(number_format((double)$cust_amount), ' align="right"');	//customer amount
			cell(number_format((double)$rate,2)." %", ' align="right"');	//customer percentage
			if($a != $rd[$rdIdx][1]) {
				cell(number_format((double)$sub_rate,2)." %", ' rowspan="'. $rowSpan .'" align="right"  style="color:darkblue;"');
				$a = $rd[$rdIdx][1];
			}
			print "</tr>\n";

			$b = $rd[$rdIdx][2];

			$group_amount[0]	+= $cust_amount;
			$group_amount[1]	+= $rate;
			$div 			= $rd[$rdIdx][0];
			$group_name		= $rd[$rdIdx][1];
			$rdIdx++;
		}
		cell($group_name, ' colspan="2" align="right" style="color:darkblue;"');			//customer group name
		cell(number_format((double)$group_amount[0]), ' align="right" style="color:darkblue;"');	//customer group amount
		cell(number_format((double)$group_amount[1],2)." %", ' align="right" style="color:darkblue;"');	//customer total percentage

		$dept_amount[0]	+= $group_amount[0];
		$dept_amount[1] += $sub_rate;
	}
	
	print "<tr>\n";
	cell("<b>TOTAL {$dept[$div]}</b>", ' colspan="2" align="right" style="color:brown; background-color:lightyellow"');	//dept name
	cell(number_format((double)$dept_amount[0]), ' align="right" style="color:brown; background-color:lightyellow"');			//dept total billing amount before vat
	cell('', ' align="right" style="color:brown; background-color:lightyellow"');										//
	cell(number_format((double)$dept_amount[1],2)." %", ' align="right" style="color:brown; background-color:lightyellow"');	//dept total percentage
	print "</tr>\n";
	print "</table><br />\n";

	$grand_total	+= $dept_amount[0];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="80%" class="table_f">
	<tr>
		<th width="15%">GROUP</th>
		<th>NAME</th>
		<th width="15%">AMOUNT<br>(Rp)</th>
		<th width="20%">COMPOSITION RATE</th>
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="2" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total), ' align="right" style="color:brown; background-color:lightyellow"');
cell('', ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>