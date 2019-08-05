<?php
//grand summary
$grand_total = array (0,0,0,0,0,0,0);

//GROUP BY MONTH
foreach ($group0 as $month_name => $month) {
	echo "<span class=\"comment\"><b>[". $month_name. "]</b></span>\n";
	print <<<END
	<table width="1000px" class="table_f">
		<tr>
			<th width="6%">INV. DATE</th>
			<th width="8%">INV. NO</th>
			<th>CUSTOMER</th>
			<th width="6%">DUE DATE</th>
			<th width="3%">D/S</th>
			<th width="5%">AMOUNT</th>
			<th width="5%">FREIGHT<br>(Rp)</th>
			<th width="5%">VAT<br>(Rp)</th>
			<th width="5%">AMOUNT<br>+FRT/VAT</th>
			<th width="5%">PAID<br> DATE</th>
			<th width="3%">PAID<br />METHOD</th>
			<th width="5%">BANK</th>
			<th width="5%">PAID<br>(Rp)</th>
			<th width="5%">BALANCE</th>
			<th width="5%">PAID<br> REMARK</th>
			<th width="15%">DEDUCTION</th>
		</tr>\n
END;

	//monthly summary
	$monthly_summary = array (0,0,0,0,0,0,0);

	$weekth = array();

	foreach ($month as $week_name => $pay_week) {
		
		$weekth = getWeek($rd[$rdIdx][1] * 604800); 	//7day * 24 hour * 60 Min * 60 Sec
		
		print "<tr>\n";
		print "<td colspan=\"15\">{$weekth['string']}</td>\n";
		print "</tr>\n";
		
		//weekly summary
		$weekly_summary = array(0,0,0,0,0,0,0);
		$print_tr_1 = 0;
		foreach ($pay_week as $billing) {
			$rowSpan = 0;
			array_walk_recursive($billing, 'getRowSpan');

			if($print_tr_1++ > 0) print "<tr>\n";
			cell($rd[$rdIdx][2], ' align="center" valign="top" rowspan="'.$rowSpan.'"');		//Invoice date
			cell_link($rd[$rdIdx][3], ' align="center", valign="top" rowspan="'.$rowSpan.'"',	//Invoice code
				' href="'.$rd[$rdIdx][21].'" target="_parent"');
			cell($rd[$rdIdx][5], ' valign="top" rowspan="'.$rowSpan.'"');						//Cusomter
			cell($rd[$rdIdx][6], ' align="center", valign="top" rowspan="'.$rowSpan.'"');		//Due date
			cell($rd[$rdIdx][7], ' align="right", valign="top" rowspan="'.$rowSpan.'"');		//D/S
			cell(number_format((double)$rd[$rdIdx][8]), ' align="right", valign="top" rowspan="'.$rowSpan.'"'); 	//amount
			cell(number_format((double)$rd[$rdIdx][9]), ' align="right", valign="top" rowspan="'.$rowSpan.'"');		// freight
			cell(number_format((double)$rd[$rdIdx][10]), ' align="right", valign="top" rowspan="'.$rowSpan.'"');	//vat
			cell(number_format((double)$rd[$rdIdx][11]), ' align="right", valign="top" rowspan="'.$rowSpan.'"');	//amount +vat+freight

			//0.Amount
			//1. Delivery_freight
			//2. amount_vat_freight
			//3. total_Amount
			//4. Payment paid
			//5. Remain_Billing
			//6. Payment remark
			//7. Payment note
			//8. Invoice code
			$invoice	= array($rd[$rdIdx][8],$rd[$rdIdx][9],$rd[$rdIdx][10],$rd[$rdIdx][11],0,$rd[$rdIdx][17],$rd[$rdIdx][18],$rd[$rdIdx][19],$rd[$rdIdx][3],0);
			$print_tr_2 = 0;

			foreach ($billing as $paid_data) {
				if($print_tr_2++ > 0) print "<tr>\n";
				cell($rd[$rdIdx][13], ' style="'.$display_css[$rd[$rdIdx][20]].'" align="right"');	//Payment date
				cell($rd[$rdIdx][14], ' style="'.$display_css[$rd[$rdIdx][20]].'" align="center"');	//Payment method
				cell($rd[$rdIdx][15], ' style="'.$display_css[$rd[$rdIdx][20]].'" align="center"');	//Payment ank
				cell(number_format((double)$rd[$rdIdx][16]), ' style="'.$display_css[$rd[$rdIdx][20]].'" align="right"'); //Payment paid
				cell("&nbsp;");								//Invoice remain
				cell($rd[$rdIdx][18], ' style="'.$display_css[$rd[$rdIdx][20]].'"');				//Payment remark

				if($rd[$rdIdx][22] == 'payment_billing') {
					$deduction_sql = "SELECT pade_description, pade_amount FROM ".ZKP_SQL."_tb_payment_deduction WHERE pay_idx={$rd[$rdIdx][12]}";
					if ($txtSearch != '') { $deduction_sql .= "AND pade_description ILIKE '%$txtSearch%'"; }
					$deduction_res = query($deduction_sql);
					$num_deduction = numQueryRows($deduction_res);

					if($num_deduction == 0) { cell(""); }
					else {
						echo "<td>\n";
						echo "<table width=\"100%\" class=\"table_l\">\n";
						while($col = fetchRow($deduction_res)) {
							echo "<tr>\n";
							echo "<td>{$col[0]}</td>";
							echo "<td align=\"right\">". number_format((double)$col[1]) ."</td>";
							echo "</tr>\n";
							$invoice[9] += $col[1];
						}
						echo "</table>";
						echo "</td>\n";
					}
				} else { cell(""); }

				print "</tr>\n";

				if ($rd[$rdIdx][20] != 'uncounted') {
					$invoice[4] 	+= $rd[$rdIdx][16];
				}
				$rdIdx++;
			}

			print "<tr>\n";
			cell('INVOICE <b>'.$invoice[8].'</b>', ' colspan="5"  align="right" align="right" style="color:blue"');
			cell(number_format((double)$invoice[0]), ' align="right" style="color:blue"');	//Amount
			cell(number_format((double)$invoice[1]), ' align="right" style="color:blue"');	//Delivery charge
			cell(number_format((double)$invoice[2]), ' align="right" style="color:blue"');	//Vat
			cell(number_format((double)$invoice[3]), ' align="right" style="color:blue"');	//Grand total
			cell("&nbsp");
			cell("&nbsp");
			cell("&nbsp");
			cell(number_format((double)$invoice[4]), ' align="right" style="color:blue"');	//Payment paid
			cell(number_format((double)$invoice[5]), ' align="right" style="color:blue"');	//Invoice remain
			cell("&nbsp");
			cell(number_format((double)$invoice[9]), ' align="right" style="color:blue"');
			print "</tr>\n";

			//SUB TOTAL
			$weekly_summary[0] += $invoice[0]; 	//Amount
			$weekly_summary[1] += $invoice[1];	//Delivery charge
			$weekly_summary[2] += $invoice[2];	//Vat
			$weekly_summary[3] += $invoice[3];	//Grand total
			$weekly_summary[4] += $invoice[4];	//Payment amount
			$weekly_summary[5] += $invoice[5];	//Invoice remain
			$weekly_summary[6] += $invoice[9];
		}

		print "<tr>\n";
		cell($weekth['string'], ' colspan="5"  align="right" align="right" style="color:brown"');
		cell(number_format((double)$weekly_summary[0]), ' align="right" style="color:brown"');
		cell(number_format((double)$weekly_summary[1]), ' align="right" style="color:brown"');
		cell(number_format((double)$weekly_summary[2]), ' align="right" style="color:brown"');
		cell(number_format((double)$weekly_summary[3]), ' align="right" style="color:brown"');
		cell("&nbsp;");
		cell("&nbsp;");
		cell("&nbsp;");
		cell(number_format((double)$weekly_summary[4]), ' align="right" style="color:brown"');
		cell(number_format((double)$weekly_summary[5]), ' align="right" style="color:brown"');
		cell("&nbsp");
		cell(number_format((double)$weekly_summary[6]), ' align="right" style="color:brown"');
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
	cell('<b>'.$month_name.'<b>', ' colspan="5"  align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$monthly_summary[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$monthly_summary[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$monthly_summary[2]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$monthly_summary[3]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$monthly_summary[4]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$monthly_summary[5]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$monthly_summary[6]), ' align="right" style="color:brown; background-color:lightyellow"');
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
<table width="1000px" class="table_f">
	<tr>
		<th width="6%">INV. DATE</th>
		<th width="8%">INV. NO</th>
		<th>CUSTOMER</th>
		<th width="6%">DUE DATE</th>
		<th width="3%">D/S</th>
		<th width="5%">AMOUNT</th>
		<th width="5%">FREIGHT<br>(Rp)</th>
		<th width="5%">VAT<br>(Rp)</th>
		<th width="5%">AMOUNT<br>+FRT/VAT</th>
		<th width="5%">PAID<br> DATE</th>
		<th width="3%">PAID<br />METHOD</th>
		<th width="5%">BANK</th>
		<th width="5%">PAID<br>(Rp)</th>
		<th width="5%">BALANCE</th>
		<th width="5%">PAID<br> REMARK</th>
		<th width="15%">DEDUCTION</th>
	</tr>\n
END;
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="5"  align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[3]), ' align="right" style="color:brown; background-color:lightyellow"');
cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[4]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[5]), ' align="right" style="color:brown; background-color:lightyellow"');
cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[6]), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br>\n";
?>