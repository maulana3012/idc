<?php
//GROUP TOTAL
$grand_total = array(0,0,0);	//qty, amount, rate

//GROUP
foreach ($group0 as $total1 => $group1) {

	$rowSpan = 0;
	$rowSpan += count($group1)+1;

	//get category path from current icat_midx.
	$path = executeSP(ZKP_SQL."_getCategoryPath", $rd[$rdIdx][0]);
	eval(html_entity_decode($path[0]));	
	$path = array_reverse($path);
	$total1 = $path[1][4]." > ".$path[2][4] ;

	echo "<span class=\"comment\"><b> CATEGORY: ". $total1. "</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th>MODEL NO</th>
			<th width="15%">QTY<br>(EA)</th>
			<th width="20%">AMOUNT<br>(Rp)</th>
			<th width="10%">RATE</th>
			<th width="10%">RATE</th> 
		</tr>\n
END;
	$cat_total = array(0,0,0);	//qty, amount, rate
	$print_tr_1 = 0;
	$a = '';

	print "<tr>\n";
	//ITEM CODE
	foreach($group1 as $total2 => $group2) {

		include "rpt_summary_by_item_print_4.php";

		//PRINT CONTENT
		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".trim($rd[$rdIdx][1])."] {$rd[$rdIdx][2]}", ' valign="top"');								//model name
		cell(number_format((double)$total[0]), ' valign="top" align="right"');	//qty
		cell(number_format((double)$total[1]), ' valign="top" align="right"');	//amount
		cell(number_format((double)$rate, 2)." %", ' valign="top" align="right"');
		if($a != $rd[$rdIdx][0]) {
			cell(number_format((double)$sub_rate, 2)." %", ' rowspan="'. $rowSpan .'" align="right" style="color:darkblue"');
			$a = $rd[$rdIdx][0];
		}
		print "</tr>\n";

		$cat_total[0]	+= $total[0];
		$cat_total[1]	+= $total[1];
		$cat_total[2]	+= $rate;
		$rdIdx++;
	}

	print "<tr>\n";
	cell("<b>{$path[2][4]}</b>", ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cat_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cat_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cat_total[2], 2)." %", ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$grand_total[0]	+= $cat_total[0];
	$grand_total[1]	+= $cat_total[1]; 
	$grand_total[2]	+= $sub_rate;
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_f">
	<tr>
			<th>MODEL NO</th>
			<th width="15%">QTY<br>(EA)</th>
			<th width="20%">AMOUNT*<br />(Rp)</th>
			<th width="10%">RATE</th>
			<th width="10%">RATE</th>
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
cell('', ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[2], 2)." %", ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
print "<span class='comment'>*<i> Amount is amount before VAT, before freight charge and price including discount</i></span>\n";
?>