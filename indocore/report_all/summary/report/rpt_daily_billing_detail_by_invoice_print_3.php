<?php
//GROUP TOTAL
$g_total = array(0,0,0,0,0,0);  // freight, qty, vat, amount, amount+vat, amount+vat+frt

//GROUP
foreach ($group0 as $total1 => $group1) {

	echo "<span class=\"comment\"><b><div id=\"".$total1."-A\"> ". $doc[$total1] . "</div></b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th width="10%">INV. NO</th>
			<th width="7%">INV. DATE</th>
			<th>CUSTOMER NAME</th>
			<th width="7%">FREIGHT</th>
			<th width="3%">DISC<br />(%)</th>
			<th width="13%">MODEL NO</th>
			<th width="7%">UNIT PRICE<br/>(Rp)</th>
			<th width="4%">QTY<br>(EA)</th>
			<th width="7%">AMOUNT<br>-VAT (Rp)</th>
			<th width="7%">VAT<br>(Rp)</th>
			<th width="7%">AMOUNT<br>+VAT</th>
			<th width="7%">AMOUNT<br>+FRT/VAT</th>
		</tr>\n
END;
	print "<tr>\n";

	$cus_total = array(0,0,0,0,0,0);
	$print_tr_1 = 0;
	//CUSTOMER
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan +=1;

		$page = explode('--', $rd[$rdIdx][23]);

		if($print_tr_1++ > 0) print "<tr>\n";
		cell_link($rd[$rdIdx][3], ' style="'.$display_css[$rd[$rdIdx][20]].'" align="center" valign="top" rowspan="'.$rowSpan.'"',
			' href="'."http://192.168.10.88/" . $s_page[0][$page[0]][$page[1]] . '/' . $s_page[1][$page[2]] . "/" . $page[3].'" style="'.$display_css[$rd[$rdIdx][20]].'"');	//Invoice no
		cell($rd[$rdIdx][4], ' style="'.$display_css[$rd[$rdIdx][20]].'" valign="top" align="center" rowspan="'.$rowSpan.'"');		//Invoice document date
		cell("[".trim($rd[$rdIdx][1])."]<br/>".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'" style="'.$display_css[$rd[$rdIdx][20]].'"');	//Customer ship to
		cell(number_format((double)$rd[$rdIdx][8]),' style="'. $display_css[$rd[$rdIdx][22]] .'" valign="top" align="right" rowspan="'.$rowSpan.'"'); 			//Freight charge
		cell($rd[$rdIdx][9],' style="'.$display_css[$rd[$rdIdx][22]].'" valign="top" align="center" rowspan="'.$rowSpan.'"'); 		//Discout

		//freight, qty, amount, vat, amount_vat, grand_total
		$inv_total	= array($rd[$rdIdx][8],0,0,0,0,$rd[$rdIdx][18]);
		$print_tr_2 = 0;
		//ORDER
		foreach($group2 as $total3 => $group3) {
			if($print_tr_2++ > 0) print "<tr>\n";
			cell($rd[$rdIdx][12], ' style="'.$display_css[$rd[$rdIdx][20]].'"');	//Model No
			cell(number_format((double)$rd[$rdIdx][13]), ' style="'.$display_css[$rd[$rdIdx][22]].'" align="right"');	//Unit price
			cell(number_format((double)$rd[$rdIdx][14]), ' style="'.$display_css[$rd[$rdIdx][21]].'" align="right"');	//Qty
			cell(number_format((double)$rd[$rdIdx][15]), ' style="'.$display_css[$rd[$rdIdx][22]].'" align="right"'); 	//Amount
			cell(number_format((double)$rd[$rdIdx][16]), ' style="'.$display_css[$rd[$rdIdx][22]].'" align="right"');	//Vat
			cell(number_format((double)$rd[$rdIdx][17]), ' style="'.$display_css[$rd[$rdIdx][22]].'" align="right"');	//Amount + vat
			cell("&nbsp;", ' style="'.$display_css[$rd[$rdIdx][20]].'"');
			print "</tr>\n";

			//Count invoice amount
			$inv_total[1] += $rd[$rdIdx][14]; // qty
			$inv_total[2] += $rd[$rdIdx][15]; // amount
			$inv_total[3] += $rd[$rdIdx][16]; // vat
			$inv_total[4] += $rd[$rdIdx][17]; // amount+vat

			$css_general	= $rd[$rdIdx][20];
			$css_qty		= $rd[$rdIdx][21];
			$css_amount		= $rd[$rdIdx][22];
			$rdIdx++;
		}

		print "<tr>\n";
		cell("INVOICE TOTAL", ' style="'.$display_css[$css_general].'" colspan="2" align="right" style="color:darkblue;"');
		cell(number_format((double)$inv_total[1]), ' style="'.$display_css[$css_qty].'" align="right" style="color:darkblue;"');
		cell(number_format((double)$inv_total[2]), ' style="'.$display_css[$css_amount].'" align="right" style="color:darkblue;"');
		cell(number_format((double)$inv_total[3]), ' style="'.$display_css[$css_amount].'" align="right" style="color:darkblue;"');
		cell(number_format((double)$inv_total[4]), ' style="'.$display_css[$css_amount].'" align="right" style="color:darkblue;"');
		cell(number_format((double)$inv_total[5]), ' style="'.$display_css[$css_amount].'" align="right" style="color:darkblue;"');
		print "</tr>\n";

		//nilai dihitung atau tidak dihitung
		//qty
		if($css_qty == 'turn_counted') {	$cus_total[1] += $inv_total[1]*-1; }
		else {								$cus_total[1] += $inv_total[1]; }

		//amount
		if($css_amount == 'turn_counted') {				//return
			$cus_total[0] += $inv_total[0]*-1;
			$cus_total[2] += $inv_total[2]*-1;
			$cus_total[3] += $inv_total[3]*-1;
			$cus_total[4] += $inv_total[4]*-1;
			$cus_total[5] += $inv_total[5]*-1;
		} else if($css_amount != 'turn_uncounted') {	//billing
			$cus_total[0] += $inv_total[0];
			$cus_total[2] += $inv_total[2];
			$cus_total[3] += $inv_total[3];
			$cus_total[4] += $inv_total[4];
			$cus_total[5] += $inv_total[5];
		}
	}
	print "<tr>\n";
	cell("<b><div id=\"".$total1."-Z\">".$doc[$total1]."</div></b>", ' colspan="3" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cus_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell("<a href=\"#up\"><small>up</small></a>", ' align="center" style="background-color:lightyellow" colspan="3"');
	cell(number_format((double)$cus_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cus_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cus_total[3]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cus_total[4]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cus_total[5]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$g_total[0] += $cus_total[0];
	$g_total[1] += $cus_total[1];
	$g_total[2] += $cus_total[2];
	$g_total[3] += $cus_total[3];
	$g_total[4] += $cus_total[4];
	$g_total[5] += $cus_total[5];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th>SHIP TO<br />CUSTOMER</th>
			<th width="10%">INV. NO</th>
			<th width="7%">INV. DATE</th>
			<th width="7%">DUE DATE</th>
			<th width="7%">FREIGHT</th>
			<th width="3%">DISC<br />(%)</th>
			<th>MODEL NO</th>
			<th width="7%">UNIT PRICE<br/>(Rp)</th>
			<th width="4%">QTY<br>(EA)</th>
			<th width="7%">AMOUNT<br>-VAT (Rp)</th>
			<th width="7%">VAT<br>(Rp)</th>
			<th width="7%">AMOUNT<br>+VAT</th>
			<th width="7%">AMOUNT<br>+FRT/VAT</th>
		</tr>\n
END;

	print "<tr>\n";
	cell("<b>GRAND TOTAL</b>", ' colspan="4" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$g_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="background-color:lightyellow"');
	cell("&nbsp", ' style="background-color:lightyellow"');
	cell("&nbsp", ' style="background-color:lightyellow"');
	cell(number_format((double)$g_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$g_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$g_total[3]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$g_total[4]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$g_total[5]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
print "</table>\n";
?>