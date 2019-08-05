<?php
//GROUP TOTAL
$grand_total = array();

//DEPARTEMEN
foreach ($group0 as $total1 => $group1) {
echo "<span class=\"comment\"><b>{$dept[$rd[$rdIdx][0]]}</b></span>\n";
print <<<END
<table class="table_f" width="$table_len px">
	<tr height="15px">
		<th width="120px" rowspan="2">GROUP</th>
		<th width="330px" rowspan="2">NAME</th>
		<th colspan="$mon_length">MONTH</th>
		<th width="100px" rowspan="2">TOTAL</th>
		<th width="100px" rowspan="2">AVG</th>
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

	$dept_amount = array();
	$print_tr_1 = 0;
	print "<tr>\n";
	//GROUP
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		$rowSpan += count($group2);

		if($print_tr_1++ > 0) print "<tr>\n";
		cell($rd[$rdIdx][1], ' valign="middle" align="center" rowspan="'.$rowSpan.'"');		//Customer Group Name

		$group_amount = array();
		$cus_amount	 = array();
		$j = 0;
		$a = '';
		$b = '';
		$rowSpan = $rowSpan + 1;
		$print_tr_2 = 0;
		//CUSTOMER
		foreach($group2 as $total3 => $group3) {
			//PRINT CONTENT
			if($print_tr_2++ > 0) print "<tr>\n";
			cell('['.trim($rd[$rdIdx][2]).'] '.$rd[$rdIdx][3], ' valign="top"');		//customer name

			$start_month = $_month_from;
			$start_year	 = $_year_from;
			for($k=0; $k<$mon_length; $k++) {
				if($start_month>12) { $start_month = $start_month-12; $start_year+=1;}

				include "rpt_summary_monthly_bill_customer_by_amount_print_4.php";

				$amount = 0;
				$res_month =& query($sql_perMonth);
				while($col_month =& fetchRow($res_month)) {
					$amount += $col_month[0];
				}
				cell(number_format((double)$amount), ' align="right"');

				if(!isset($cus_amount[$j]))  {
					$cus_amount[$j]	  = $amount;
				} else {
					$cus_amount[$j]	  += $amount;
				}
				
				if(!isset($group_amount[$k]))  {
					$group_amount[$k] = $amount;
				} else {
					$group_amount[$k] += $amount;
				}

				$start_month++;
			}
			cell(number_format((double)$cus_amount[$j]), ' align="right"');
			cell(number_format((double)$cus_amount[$j]/$mon_length), ' align="right"');
			print "</tr>\n";

			if(!isset($group_amount[$mon_length]))		{ $group_amount[$mon_length] = $cus_amount[$j]; }
			else										{ $group_amount[$mon_length] += $cus_amount[$j]; }
			if(!isset($group_amount[$mon_length+1]))	{ $group_amount[$mon_length+1] = $cus_amount[$j]/$mon_length; }
			else										{ $group_amount[$mon_length+1] += $cus_amount[$j]/$mon_length; }

			$dept_name	= $rd[$rdIdx][0];
			$group_name	= $rd[$rdIdx][1];
			$rdIdx++;
			$j++;
		}
		cell($group_name, ' colspan="2" align="right" style="color:darkblue;"');			//customer group name
		for($i=0; $i<$mon_length; $i++) {
			cell(number_format((double)$group_amount[$i]), ' align="right" style="color:darkblue;"');
		}
		cell(number_format((double)$group_amount[$i]), ' align="right" style="color:darkblue;"');	//Grand Total Customer Group
		cell(number_format((double)$group_amount[$i+1]), ' align="right" style="color:darkblue;"');	//Average Amount Customer Group

		for($i=0; $i<$mon_length; $i++) {
			if(!isset($dept_amount[$i])) { $dept_amount[$i] = $group_amount[$i]; }
			else						 { $dept_amount[$i] += $group_amount[$i]; }
		}
		if(!isset($dept_amount[$mon_length]))	{ $dept_amount[$mon_length] = $group_amount[$mon_length]; }
		else						 		 	{ $dept_amount[$mon_length] += $group_amount[$mon_length]; }

		if(!isset($dept_amount[$mon_length+1]))	{ $dept_amount[$mon_length+1] = $group_amount[$mon_length+1]; }
		else									{ $dept_amount[$mon_length+1] += $group_amount[$mon_length+1]; }
	}

	print "<tr>\n";
	cell("<b>TOTAL {$dept[$dept_name]}</b>", ' colspan="2" align="right" style="color:brown; background-color:lightyellow"');	//Department
	for($i=0; $i<$mon_length; $i++) {
		cell(number_format((double)$dept_amount[$i]), ' align="right" style="color:brown; background-color:lightyellow"');
	}
	cell(number_format((double)$dept_amount[$i]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$dept_amount[$i+1]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br /><br />";

	for($i=0; $i<$mon_length; $i++) {
		if(!isset($grand_total[$i])) 	{ $grand_total[$i] = $dept_amount[$i]; }
		else							{ $grand_total[$i] += $dept_amount[$i]; }
	}
	if(!isset($grand_total[$mon_length])) 	{ $grand_total[$mon_length]	= $dept_amount[$mon_length]; }
	else									{ $grand_total[$mon_length]	+= $dept_amount[$mon_length]; }
	if(!isset($grand_total[$mon_length+1]))	{ $grand_total[$mon_length+1]	= $dept_amount[$mon_length+1]; }
	else									{ $grand_total[$mon_length+1]	+= $dept_amount[$mon_length+1]; }
}

print <<<END
<table class="table_f" width="$table_len px">
	<tr height="15px">
		<th width="120px" rowspan="2">GROUP</th>
		<th width="330px" rowspan="2">NAME</th>
		<th colspan="$mon_length">MONTH</th>
		<th width="100px" rowspan="2">TOTAL</th>
		<th width="100px" rowspan="2">AVG</th>
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
cell("<b>GRAND TOTAL</b>", ' colspan="2" align="right" style="color:brown; background-color:lightyellow"');
for($i=0; $i<$mon_length; $i++) {
	cell(isset($grand_total[$i]) ? number_format((double)$grand_total[$i]) : 0, ' align="right" style="color:brown; background-color:lightyellow"');
}
cell(isset($grand_total[$i]) ? number_format((double)$grand_total[$i]) : 0, ' align="right" style="color:brown; background-color:lightyellow"');
cell(isset($grand_total[$i]) ? number_format((double)$grand_total[$i+1]) : 0, ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>