<?php
//GROUP TOTAL
$grand_total = array(0,0,0,0);

//GROUP
foreach ($group0 as $total1 => $group1) {

	//get category path from current icat_midx.
	$path = executeSP(ZKP_SQL."_getCategoryPath", $rd[$rdIdx][0]);
	eval(html_entity_decode($path[0]));
	$path = array_reverse($path);
	$total1 = $path[1][4]." > ".$path[2][4]." > ".$path[3][4] ;

	echo "<span class=\"comment\"><b> CATEGORY: ". $total1. "</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th width="15%">MODEL NO</th>
			<th width="13%">INV. NO</th>
			<th width="10%">INV. DATE</th>
			<th>SHIP TO CUSTOMER</th>
			<th width="7%">@PRICE<br/>(Rp)</th>
			<th width="5%">QTY<br>(EA)</th>
			<th width="7%">AMOUNT<br>(Rp)</th>
			<th width="7%">VAT<br>(Rp)</th>
			<th width="7%">AMOUNT<br>+VAT(Rp)</th>
		</tr>\n
END;
	$cat_total = array(0,0,0,0);
	$print_tr_1 = 0;

	print "<tr>\n";
	//MODEL
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell('['.trim($rd[$rdIdx][1]).'] '.$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');		//Model No

		$model_total = array(0,0,0,0);
		$print_tr_2 = 0;
		//ORDER
		foreach($group2 as $total3 => $group3) {
			$rowSpan = 0;
			array_walk_recursive($group3, 'getRowSpan');
			$page = explode('--', $rd[$rdIdx][17]);

			if($print_tr_2++ > 0) print "<tr>\n";
			cell_link($rd[$rdIdx][4], ' style="'.$display_css[$rd[$rdIdx][14]].'" align="center" valign="top" rowspan="'.$rowSpan.'"',
				' href="'."http://192.168.10.88/" . $s_page[0][$page[0]][$page[1]] . '/' . $s_page[1][$page[2]] . "/" . $page[3].'"style="'.$display_css[$rd[$rdIdx][14]].'"');														//Invoice no
			cell($rd[$rdIdx][5], ' style="'.$display_css[$rd[$rdIdx][14]].'" align="center" valign="top" rowspan="'.$rowSpan.'"');					//Invoice date
			cell("[".trim($rd[$rdIdx][6])."] ".$rd[$rdIdx][7], ' style="'.$display_css[$rd[$rdIdx][14]].'" valign="top" rowspan="'.$rowSpan.'"');	//Ship to

			$print_tr_3 = 0;
			//ITEM
			foreach($group3 as $total4 => $group4) {
				if($print_tr_3++ > 0) print "<tr>\n";
				cell(number_format((double)$rd[$rdIdx][8]), ' style="'.$display_css[$rd[$rdIdx][16]].'" align="right"');	//unit price
				cell(number_format((double)$rd[$rdIdx][9]), ' style="'.$display_css[$rd[$rdIdx][15]].'" align="right"');	//qty
				cell(number_format((double)$rd[$rdIdx][10]), ' style="'.$display_css[$rd[$rdIdx][16]].'" align="right"');	//amount
				cell(number_format((double)$rd[$rdIdx][11]), ' style="'.$display_css[$rd[$rdIdx][16]].'" align="right"');	//vat
				cell(number_format((double)$rd[$rdIdx][12]), ' style="'.$display_css[$rd[$rdIdx][16]].'" align="right"');	//amount + vat
				print "</tr>\n";

				//nilai positif atau negatif
				//qty
				if($rd[$rdIdx][15] == 'turn_counted') {	$model_total[0] += $rd[$rdIdx][9]*-1; }
				else {									$model_total[0] += $rd[$rdIdx][9]; }

				//amount
				if($rd[$rdIdx][16] == 'turn_counted') {				//return
					$model_total[1] += $rd[$rdIdx][10]*-1;
					$model_total[2] += $rd[$rdIdx][11]*-1;
					$model_total[3] += $rd[$rdIdx][12]*-1;
				} else if($rd[$rdIdx][16] != 'turn_uncounted') {	//billing
					$model_total[1] += $rd[$rdIdx][10];
					$model_total[2] += $rd[$rdIdx][11];
					$model_total[3] += $rd[$rdIdx][12];
				}

				$model_no = $rd[$rdIdx][2]; 	//model no
				$rdIdx++;
			}
		}

		print "<tr>\n";
		cell($model_no, ' colspan="4" align="right" style="color:darkblue"');
		cell(number_format((double)$model_total[0]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$model_total[1]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$model_total[2]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$model_total[3]), ' align="right" style="color:darkblue"');
		print "</tr>\n";

		$cat_total[0] += $model_total[0];
		$cat_total[1] += $model_total[1];
		$cat_total[2] += $model_total[2];
		$cat_total[3] += $model_total[3];
	}

	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cat_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cat_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cat_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cat_total[3]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$grand_total[0] += $cat_total[0];
	$grand_total[1] += $cat_total[1];
	$grand_total[2] += $cat_total[2];
	$grand_total[3] += $cat_total[3];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_f">
	<tr>
			<th width="15%">MODEL NO</th>
			<th width="13%">INV. NO</th>
			<th width="10%">INV. DATE</th>
			<th>SHIP TO CUSTOMER</th>
			<th width="7%">@PRICE<br/>(Rp)</th>
			<th width="5%">QTY<br>(EA)</th>
			<th width="7%">AMOUNT<br>(Rp)</th>
			<th width="7%">VAT<br>(Rp)</th>
			<th width="7%">AMOUNT<br>+VAT(Rp)</th>
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[3]), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";

?>