<?php
//GROUP TOTAL
$grand_total = array(0,0);

//GROUP
foreach ($group0 as $total1 => $group1) {

	//get category path from current icat_midx.
	$path = executeSP(ZKP_SQL."_getCategoryPath", $rd[$rdIdx][0]);
	eval(html_entity_decode($path[0]));	
	$path = array_reverse($path);
	$total1 = $path[1][4]." > ".$path[2][4] ;

	echo "<span class=\"comment\"><b> CATEGORY: ". $total1. "</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th width="25%">MODEL NO</th>
			<th>CITY</th>
			<th width="10%">QTY<br>(EA)</th>
			<th width="20%">AMOUNT<br>(Rp)</th>
		</tr>\n
END;
	$cat_total = array(0,0);
	$print_tr_1 = 0;

	print "<tr>\n";
	//MODEL
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell('['.trim($rd[$rdIdx][2]).'] '.$rd[$rdIdx][3], ' valign="top" rowspan="'.$rowSpan.'"');		//Model No

		$model_total = array(0,0);
		$print_tr_2 = 0;
		//CITY
		foreach($group2 as $total3 => $group3) {
			if($rd[$rdIdx][4]!='')	$city=$rd[$rdIdx][4];
			else					$city='Undefined';

			if($print_tr_2++ > 0) print "<tr>\n";
			cell(($rd[$rdIdx][4]=='')?'<i class="comment">Undefined</i>':$rd[$rdIdx][4]);	// City
			cell(number_format((double)$tot[$rd[$rdIdx][2]][$city][0]), ' align="right"');			// Qty
			cell(number_format((double)$tot[$rd[$rdIdx][2]][$city][1]), ' align="right"');			// Amount

			$model_total[0] += $tot[$rd[$rdIdx][2]][$city][0];
			$model_total[1] += $tot[$rd[$rdIdx][2]][$city][1];
			$model_no = $rd[$rdIdx][3]; 	// Model no
			$rdIdx++;
		}

		print "<tr>\n";
		cell($model_no, ' align="right" style="color:darkblue"');
		cell(number_format((double)$model_total[0]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$model_total[1]), ' align="right" style="color:darkblue"');
		print "</tr>\n";

		$cat_total[0] += $model_total[0];
		$cat_total[1] += $model_total[1];
	}

	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="2" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cat_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cat_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$grand_total[0] += $cat_total[0];
	$grand_total[1] += $cat_total[1];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_f">
	<tr>
		<th width="25%">MODEL NO</th>
		<th>CITY</th>
		<th width="10%">QTY<br>(EA)</th>
		<th width="20%">AMOUNT<br>(Rp)</th>
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="2" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>