<?php
//GROUP TOTAL
$g_total = array(0,0,0,0,0,0,0);

//GROUP TOTAL
$grand_total = array(0,0,0,0,0,0,0);

//CUSTOMER
foreach ($group0 as $group_name => $pusat) {
	echo "<span class=\"comment\"><b> CUSTOMER : [". $group_name. "] {$rd[$rdIdx][1]}</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th width="7%">INV. DATE</th>
			<th width="10%">INV. NO</th>
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
	$group_total = array(0,0,0,0,0,0,0);
	foreach ($pusat as $billing) {
		print "<tr>\n";
		cell($rd[$rdIdx][2], ' style="'.$display_css[$rd[$rdIdx][16]].'" align="center"');	//Invice date
		cell_link($rd[$rdIdx][3], ' style="'.$display_css[$rd[$rdIdx][16]].'" align="center"',
			' href="'.$rd[$rdIdx][17].' "style="'.$display_css[$rd[$rdIdx][16]].'"');		//Invoice no											//Invoice no
		cell($rd[$rdIdx][4], ' style="'.$display_css[$rd[$rdIdx][16]].'" align="center"');	//Payment by
		cell($rd[$rdIdx][5], ' style="'.$display_css[$rd[$rdIdx][16]].'" align="center"');	//Due date
		cell($rd[$rdIdx][6], ' style="'.$display_css[$rd[$rdIdx][16]].'"');					//Bank
		cell($rd[$rdIdx][7], ' style="'.$display_css[$rd[$rdIdx][16]].'" align="right"');	//D/S
		cell(number_format((double)$rd[$rdIdx][8]), ' style="'.$display_css[$rd[$rdIdx][16]].'" align="right"');	//amount
		cell(number_format((double)$rd[$rdIdx][9]), ' style="'.$display_css[$rd[$rdIdx][16]].'" align="right"');	// freight
		cell(number_format((double)$rd[$rdIdx][10]), ' style="'.$display_css[$rd[$rdIdx][16]].'" align="right"');	//vat
		cell(number_format((double)$rd[$rdIdx][11]), ' style="'.$display_css[$rd[$rdIdx][16]].'" align="right"');	//amount+vat
		cell(number_format((double)$rd[$rdIdx][12]), ' style="'.$display_css[$rd[$rdIdx][16]].'" align="right"');	//amount +vat+freight
		cell(number_format((double)$rd[$rdIdx][13]), ' style="'.$display_css[$rd[$rdIdx][16]].'" align="right"');	//Paid
		cell(number_format((double)$rd[$rdIdx][14]), ' style="'.$display_css[$rd[$rdIdx][16]].'" align="right"');	// Remain
		cell($rd[$rdIdx][15], ' style="'.$display_css[$rd[$rdIdx][16]].'" align="center"');					//.Last paid date
		print "</tr>\n";
		
		//SUB TOTAL
		if($rd[$rdIdx][16] == 'turn_counted') {				//return
			$group_total[0] += $rd[$rdIdx][8] *-1;
			$group_total[1] += $rd[$rdIdx][9] *-1;
			$group_total[2] += $rd[$rdIdx][10] *-1;
			$group_total[3] += $rd[$rdIdx][11] *-1;
			$group_total[4] += $rd[$rdIdx][12] *-1;
			$group_total[5] += $rd[$rdIdx][13] *-1;
			$group_total[6] += $rd[$rdIdx][14] *-1;
		} else {
			$group_total[0] += $rd[$rdIdx][8];
			$group_total[1] += $rd[$rdIdx][9];
			$group_total[2] += $rd[$rdIdx][10];
			$group_total[3] += $rd[$rdIdx][11];
			$group_total[4] += $rd[$rdIdx][12];
			$group_total[5] += $rd[$rdIdx][13];
			$group_total[6] += $rd[$rdIdx][14];
		}
		$rdIdx++;
	}

	print "<tr>\n";
	cell("GROUP TOTAL", ' colspan="6"  align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$group_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$group_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$group_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$group_total[3]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$group_total[4]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$group_total[5]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$group_total[6]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	//GRAND TOTAL
	$grand_total[0] += $group_total[0];
	$grand_total[1] += $group_total[1];
	$grand_total[2] += $group_total[2];
	$grand_total[3] += $group_total[3];
	$grand_total[4] += $group_total[4];
	$grand_total[5] += $group_total[5];
	$grand_total[6] += $group_total[6];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_f">
	<tr>
		<th width="7%">INV. DATE</th>
		<th width="10%">INV. NO</th>
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
cell("GROUP TOTAL", ' colspan="6"  align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[3]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[4]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[5]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[6]), ' align="right" style="color:brown; background-color:lightyellow"');
cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br />\n";
?>