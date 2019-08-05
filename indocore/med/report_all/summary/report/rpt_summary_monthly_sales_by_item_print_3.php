<?php
//GROUP TOTAL
$grand_total = array();

//DEPARTEMEN
foreach ($group0 as $total1 => $group1) {

	//get category path from current icat_midx.
	$path = executeSP(ZKP_SQL."_getCategoryPath", $rd[$rdIdx][0]);
	eval(html_entity_decode($path[0]));	
	$path = array_reverse($path);
	$cat = $path[1][4]." > ".$path[2][4]." > ".$path[3][4] ;

echo "<span class=\"comment\"><b> CATEGORY: $cat</b></span>\n";
print <<<END
<table class="table_f" width="$table_len px">
	<tr height="15px">
		<th width="250px" rowspan="2">MODEL NO</th>
		<th colspan="$mon_length">MONTH</th>
		<th width="80px" rowspan="2">TOTAL</th>
		<th width="80px" rowspan="2">AVG</th>
	</tr>
	<tr height="25px">\n
END;

	$start_month = $_month_from;
	$start_year	 = $_year_from;
	for($i=0; $i<$mon_length; $i++) {
		if($start_month>12) { $start_month = $start_month-12; $start_year+=1;}
		echo "\t\t<th width=\"80px\">".$month[$start_month]."<br />$start_year</th>\n";
		$start_month++;
	}
print <<<END
	</tr>\n
END;

	$item_cat	= array();
	$print_tr_1 = 0;
	print "<tr>\n";
	//GROUP
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		$rowSpan += count($group2);

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".trim($rd[$rdIdx][1])."] ". $rd[$rdIdx][2]);		//Model No

		$item_total	= 0;
		for($j=0; $j<$mon_length; $j++) {
			$amount = isset($tot[$rd[$rdIdx][1]][$period[$j]]) ? $tot[$rd[$rdIdx][1]][$period[$j]] : 0;
			cell(number_format((double)$amount,0), ' align="right"');
			if(isset($item_cat[$j])) $item_cat[$j]	+= $amount;
			else					 $item_cat[$j]	= $amount;
			$item_total 	+= $amount;
			$start_month++;
		}
		cell(number_format((double)$item_total,0), ' align="right"');
		cell(number_format((double)$item_total/$mon_length,2), ' align="right"');
		print "</tr>\n";

		if(isset($item_cat[$mon_length])) {
			$item_cat[$mon_length]	 += $item_total;
			$item_cat[$mon_length+1] += $item_total/$mon_length;
		} else {
			$item_cat[$mon_length]	 = $item_total;
			$item_cat[$mon_length+1] = $item_total/$mon_length;
		}
		$rdIdx++;
	}

	print "<tr>\n";
	cell("<b>$cat</b>", ' align="right" style="color:brown; background-color:lightyellow"');
	for($i=0; $i<$mon_length; $i++) {
		cell(number_format((double)$item_cat[$i],0), ' align="right" style="color:brown; background-color:lightyellow"');
	}
	cell(number_format((double)$item_cat[$i],0), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$item_cat[$i+1],2), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br /><br />";

	for($i=0; $i<$mon_length; $i++) {
		if(isset($grand_total[$i])) $grand_total[$i] += $item_cat[$i];
		else						$grand_total[$i] = $item_cat[$i];
	}
	if(isset($grand_total[$mon_length])) {
		$grand_total[$mon_length]	+= $item_cat[$mon_length];
		$grand_total[$mon_length+1]	+= $item_cat[$mon_length+1];
	} else {
		$grand_total[$mon_length]	= $item_cat[$mon_length];
		$grand_total[$mon_length+1]	= $item_cat[$mon_length+1];
	}
}

print <<<END
<table class="table_f" width="$table_len px">
	<tr height="15px">
		<th width="250px" rowspan="2">MODEL NO</th>
		<th colspan="$mon_length">MONTH</th>
		<th width="80px" rowspan="2">TOTAL</th>
		<th width="80px" rowspan="2">AVG</th>
	</tr>
	<tr height="25px">\n
END;

	$start_month = $_month_from;
	$start_year	 = $_year_from;
	for($i=0; $i<$mon_length; $i++) {
		if($start_month>12) { $start_month = $start_month-12; $start_year+=1;}
		echo "\t\t<th width=\"80px\">".$month[$start_month]."<br />$start_year</th>\n";
		$start_month++;
	}
print <<<END
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' align="right" style="color:brown; background-color:lightyellow"');
for($i=0; $i<$mon_length; $i++) {
	cell((isset($grand_total[$i])) ? number_format((double)$grand_total[$i],0) : 0, ' align="right" style="color:brown; background-color:lightyellow"');
}
cell((isset($grand_total[$i])) ? number_format((double)$grand_total[$i],0) : 0, ' align="right" style="color:brown; background-color:lightyellow"');
cell((isset($grand_total[$i+1])) ? number_format((double)$grand_total[$i+1],2) : 0, ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>