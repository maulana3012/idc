<?php
//grand summary
$grand_total = array (0,0,0,0,0,0,0);

//GROUP BY MONTH
foreach ($group0 as $month_name => $month) {
	echo "<span class=\"comment\"><b>[". $month_name. "]</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th width="7%">INV. DATE</th>
			<th width="10%">INV. NO</th>
			<th>SHIP TO<br />CUSTOMER</th>
			<th width="3%">PAID<br />METHOD</th>
			<th width="7%">DUE DATE</th>
			<th width="7%">BANK</th>
			<th width="3%">D/S</th>
			<th width="6%">AMOUNT</th>
			<th width="6%">FREIGHT<br>(Rp)</th>
			<th width="6%">VAT<br>(Rp)</th>
			<th width="6%">AMOUNT<br>+VAT</th>
			<th width="6%">AMOUNT<br>+FRT/VAT</th>
			<th width="6%">PAID<br>(Rp)</th>
			<th width="6%">BAL<br>(Rp)</th>
			<th width="7%">LAST PAID</th>
		</tr>\n
END;

	//monthly summary
	$monthly_summary = array (0,0,0,0,0,0,0);
	$weekth = array();
	foreach ($month as $week_name => $due_week) {
		
		$weekth = getWeek($rd[$rdIdx][1] * 604800); 	//7day * 24 hour * 60 Min * 60 Sec
		print "<tr>\n";
		print "<td colspan=\"15\">{$weekth['string']}</td>\n";
		print "</tr>\n";

		//weekly summary
		$weekly_summary = array(0,0,0,0,0,0,0);

		foreach ($due_week as $invoice) {
		print "<tr>\n";
			cell($rd[$rdIdx][2], ' style="'.$display_css[$rd[$rdIdx][19]].'" align="center"');		//Invoice date
			cell_link($rd[$rdIdx][3], ' style="'.$display_css[$rd[$rdIdx][19]].'" align="center"',	//Invoice no
				' href="'.$rd[$rdIdx][20].'" style="'.$display_css[$rd[$rdIdx][19]].'"');
			cell($rd[$rdIdx][5], ' style="'.$display_css[$rd[$rdIdx][19]].'"');						//Customer	
			cell($rd[$rdIdx][6], ' style="'.$display_css[$rd[$rdIdx][19]].'" align="center"');		//Payment  by
			cell($rd[$rdIdx][7], ' style="'.$display_css[$rd[$rdIdx][19]].'" align="center"');		//Due date
			cell($rd[$rdIdx][8], ' style="'.$display_css[$rd[$rdIdx][19]].'"');						//Bank
			cell($rd[$rdIdx][9], ' style="'.$display_css[$rd[$rdIdx][19]].'" align="right"');		//D/S
			cell(number_format((double)$rd[$rdIdx][10]), ' style="'.$display_css[$rd[$rdIdx][19]].'" align="right"');	//amount
			cell(number_format((double)$rd[$rdIdx][11]), ' style="'.$display_css[$rd[$rdIdx][19]].'" align="right"');	//delivery charge
			cell(number_format((double)$rd[$rdIdx][12]), ' style="'.$display_css[$rd[$rdIdx][19]].'" align="right"');	//vat
			cell(number_format((double)$rd[$rdIdx][13]), ' style="'.$display_css[$rd[$rdIdx][19]].'" align="right"');	//amount+vat
			cell(number_format((double)$rd[$rdIdx][14]), ' style="'.$display_css[$rd[$rdIdx][19]].'" align="right"');	//amount +vat+freight
			cell(number_format((double)$rd[$rdIdx][15]), ' style="'.$display_css[$rd[$rdIdx][19]].'" align="right"');	//paid
			cell(number_format((double)$rd[$rdIdx][16]), ' style="'.$display_css[$rd[$rdIdx][19]].'" align="right"');	// Remain amount
			cell($rd[$rdIdx][17], ' style="'.$display_css[$rd[$rdIdx][19]].'" align="center"');		//Last paid date
			print "</tr>\n";

			//SUB TOTAL
			if($rd[$rdIdx][19] == 'turn_counted') {				//return
				$weekly_summary[0] += $rd[$rdIdx][10] *-1;
				$weekly_summary[1] += $rd[$rdIdx][11] *-1;
				$weekly_summary[2] += $rd[$rdIdx][12] *-1;
				$weekly_summary[3] += $rd[$rdIdx][13] *-1;
				$weekly_summary[4] += $rd[$rdIdx][14] *-1;
				$weekly_summary[5] += $rd[$rdIdx][15] *-1;
				$weekly_summary[6] += $rd[$rdIdx][16] *-1;
			} else {
				$weekly_summary[0] += $rd[$rdIdx][10];
				$weekly_summary[1] += $rd[$rdIdx][11];
				$weekly_summary[2] += $rd[$rdIdx][12];
				$weekly_summary[3] += $rd[$rdIdx][13];
				$weekly_summary[4] += $rd[$rdIdx][14];
				$weekly_summary[5] += $rd[$rdIdx][15];
				$weekly_summary[6] += $rd[$rdIdx][16];
			}
			$rdIdx++;
		}

		print "<tr>\n";
		cell($weekth['string'], ' colspan="7"  align="right" align="right" style="color:darkblue"');
		cell(number_format((double)$weekly_summary[0]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$weekly_summary[1]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$weekly_summary[2]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$weekly_summary[3]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$weekly_summary[4]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$weekly_summary[5]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$weekly_summary[6]), ' align="right" style="color:darkblue"');
		cell("&nbsp", ' align="right" style="color:darkblue"');
		print "</tr>\n";

		//Monthly TOTAL
		$monthly_summary[0] += $weekly_summary[0];
		$monthly_summary[1] += $weekly_summary[1];
		$monthly_summary[2] += $weekly_summary[2];
		$monthly_summary[3] += $weekly_summary[3];
		$monthly_summary[4] += $weekly_summary[4];
		$monthly_summary[5] += $weekly_summary[5];
		$monthly_summary[6] += $weekly_summary[6];
	}
	
	print "<tr>\n";
	cell('<b>'.$month_name.'<b>', ' colspan="7"  align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$monthly_summary[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$monthly_summary[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$monthly_summary[2]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$monthly_summary[3]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$monthly_summary[4]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$monthly_summary[5]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$monthly_summary[6]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br>\n";

	//Monthly TOTAL
	$grand_total[0] += $monthly_summary[0];
	$grand_total[1] += $monthly_summary[1];
	$grand_total[2] += $monthly_summary[2];
	$grand_total[3] += $monthly_summary[3];
	$grand_total[4] += $monthly_summary[4];
	$grand_total[5] += $monthly_summary[5];
	$grand_total[6] += $monthly_summary[6];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_f">
	<tr>
		<th width="7%">INV. DATE</th>
		<th width="10%">INV. NO</th>
		<th>SHIP TO<br />CUSTOMER</th>
		<th width="3%">PAID<br />METHOD</th>
		<th width="7%">DUE DATE</th>
		<th width="7%">BANK</th>
		<th width="3%">D/S</th>
		<th width="6%">AMOUNT</th>
		<th width="6%">FREIGHT<br>(Rp)</th>
		<th width="6%">VAT<br>(Rp)</th>
		<th width="6%">AMOUNT<br>+VAT</th>
		<th width="6%">AMOUNT<br>+FRT/VAT</th>
		<th width="6%">PAID<br>(Rp)</th>
		<th width="6%">BAL<br>(Rp)</th>
		<th width="7%">LAST PAID</th>
	</tr>\n
END;
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="7"  align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[3]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[4]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[5]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[6]), ' align="right" style="color:brown; background-color:lightyellow"');
cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br>\n";
?>