<?php
//grand summary
$grand_total = array (0,0,0,0,0,0,0);

//GROUP
foreach ($group0 as $total1 => $group1) {
	echo "<span class=\"comment\"><b>PUSAT: ". $total1. "</b></span>\n";
	print <<<END
	<table width="1000px" class="table_f">
		<tr>
			<th>SHIP TO<br />CUSTOMER</th>	
			<th width="6%">INV. DATE</th>
			<th width="8%">INV. NO</th>
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

	$group_summary = array (0,0,0,0,0,0,0);
	$print_tr_1 = 0;
	//CUSTOMER
	foreach ($group1 as $total2 => $group2) {

		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += count($group2);

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[". trim($rd[$rdIdx][1]) ."]<br/>".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');	//Customer

		$customer_summary = array(0,0,0,0,0,0,0);
		$print_tr_2 = 0;

		//INVOICE
		foreach ($group2 as $total3 => $group3) {
			$rowSpan = 0;
			array_walk_recursive($group3, 'getRowSpan');

			if($print_tr_2++ > 0) print "<tr>\n";
			cell($rd[$rdIdx][3], ' valign="top" rowspan="'.$rowSpan.'"');								//Invoice date
			cell_link($rd[$rdIdx][4], ' align="center" valign="top" rowspan="'.$rowSpan.'"',			//Invoice no
				' href="'.$rd[$rdIdx][20].'" target="_parent"');
			cell($rd[$rdIdx][5], ' align="center" valign="top" rowspan="'.$rowSpan.'"');				//Due date
			cell($rd[$rdIdx][6], ' align="right" valign="top" rowspan="'.$rowSpan.'"');					//D/S
			cell(number_format((double)$rd[$rdIdx][7]), ' align="right" valign="top" rowspan="'.$rowSpan.'"');	//amount
			cell(number_format((double)$rd[$rdIdx][8]), ' align="right" valign="top" rowspan="'.$rowSpan.'"');	// freight
			cell(number_format((double)$rd[$rdIdx][9]), ' align="right" valign="top" rowspan="'.$rowSpan.'"');	//vat
			cell(number_format((double)$rd[$rdIdx][10]), ' align="right" valign="top" rowspan="'.$rowSpan.'"');	//amount +vat+freight

			//0.Amount
			//1. Delivery_freight
			//2. amount_vat_freight
			//3. total_Amount
			//4. Payment paid
			//5. Remain_Billing
			//6. Payment remark
			//7. Payment note
			//8. Invoice
			$invoice	= array($rd[$rdIdx][7],$rd[$rdIdx][8],$rd[$rdIdx][9],$rd[$rdIdx][10],0,$rd[$rdIdx][16],$rd[$rdIdx][17],$rd[$rdIdx][18], $rd[$rdIdx][4],0);
			$print_tr_3 = 0;
			//PAYMENT
			foreach ($group3 as $group4) {
				if($print_tr_3++ > 0) print "<tr>\n";
				cell($rd[$rdIdx][12], ' style="'.$display_css[$rd[$rdIdx][19]].'" align="right"');		//Payment date
				cell($rd[$rdIdx][13], ' style="'.$display_css[$rd[$rdIdx][19]].'" align="center"');		//Payment method
				cell($rd[$rdIdx][14], ' style="'.$display_css[$rd[$rdIdx][19]].'" align="center"');		//Payment bank
				cell(number_format((double)$rd[$rdIdx][15]), ' style="'.$display_css[$rd[$rdIdx][19]].'" align="right"'); //Amount paid
				cell("&nbsp;");									//Remain billing
				cell($rd[$rdIdx][17],' style="'.$display_css[$rd[$rdIdx][19]].'"');						//Payment remark

				if($rd[$rdIdx][21] == 'payment_billing') {
					$deduction_sql = "SELECT pade_description, pade_amount FROM ".ZKP_SQL."_tb_payment_deduction WHERE pay_idx={$rd[$rdIdx][11]}";
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

				if ($rd[$rdIdx][19] != 'uncounted') {
					$invoice[4] 	+= $rd[$rdIdx][15];
				}
				$customer_name	 =  '['. trim($rd[$rdIdx][1]). '] ' . $rd[$rdIdx][2];
				$rdIdx++;
			}

			print "<tr>\n";
			cell('INVOICE <b>'.$invoice[8].'</b>', ' colspan="4"  align="right" align="right" style="color:blue"');
			cell(number_format((double)$invoice[0]), ' align="right" style="color:blue"');	//Amount
			cell(number_format((double)$invoice[1]), ' align="right" style="color:blue"');	//Delivery charge
			cell(number_format((double)$invoice[2]), ' align="right" style="color:blue"');	//Vat
			cell(number_format((double)$invoice[3]), ' align="right" style="color:blue"');	//Grand total
			cell("&nbsp");	//Payment date
			cell("&nbsp");	//Payment method
			cell("&nbsp");	//Payment bank
			cell(number_format((double)$invoice[4]), ' align="right" style="color:blue"');	//Payment amount
			cell(number_format((double)$invoice[5]), ' align="right" style="color:blue"');	//Billing remain
			cell("&nbsp");
			cell(number_format((double)$invoice[9]), ' align="right" style="color:blue"');
			print "</tr>\n";

			//SUB TOTAL
			$customer_summary[0] += $invoice[0];	//Amount
			$customer_summary[1] += $invoice[1];	//Delivery charge
			$customer_summary[2] += $invoice[2];	//Vat
			$customer_summary[3] += $invoice[3]; 	//Grand total
			$customer_summary[4] += $invoice[4];	//Payment amount
			$customer_summary[5] += $invoice[5];	//Remain billing
			$customer_summary[6] += $invoice[9];
		}

		print "<tr>\n";
		cell("<b>$customer_name</b>", ' colspan="5"  align="right" align="right" style="color:brown"');
		cell(number_format((double)$customer_summary[0]), ' align="right" style="color:brown"');
		cell(number_format((double)$customer_summary[1]), ' align="right" style="color:brown"');
		cell(number_format((double)$customer_summary[2]), ' align="right" style="color:brown"');
		cell(number_format((double)$customer_summary[3]), ' align="right" style="color:brown"');
		cell("&nbsp;");
		cell("&nbsp;");
		cell("&nbsp;");
		cell(number_format((double)$customer_summary[4]), ' align="right" style="color:brown"');
		cell(number_format((double)$customer_summary[5]), ' align="right" style="color:brown"');
		cell("&nbsp");
		cell(number_format((double)$customer_summary[6]), ' align="right" style="color:brown"');
		print "</tr>\n";

		//Monthly TOTAL
		$group_summary[0] += $customer_summary[0];
		$group_summary[1] += $customer_summary[1];
		$group_summary[2] += $customer_summary[2];
		$group_summary[3] += $customer_summary[3];
		$group_summary[4] += $customer_summary[4];
		$group_summary[5] += $customer_summary[5];
		$group_summary[6] += $customer_summary[6];
	}
	
	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="5"  align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$group_summary[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$group_summary[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$group_summary[2]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$group_summary[3]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$group_summary[4]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$group_summary[5]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$group_summary[6]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br>\n";

	//Monthly TOTAL
	$grand_total[0] += $group_summary[0];
	$grand_total[1] += $group_summary[1];
	$grand_total[2] += $group_summary[2];
	$grand_total[3] += $group_summary[3];
	$grand_total[4] += $group_summary[4];
	$grand_total[5] += $group_summary[5];
	$grand_total[6] += $group_summary[6];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="1000px" class="table_f">
	<tr>
		<th>SHIP TO<br />CUSTOMER</th>	
		<th width="6%">INV. DATE</th>
		<th width="8%">INV. NO</th>
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