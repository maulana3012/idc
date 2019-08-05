<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim
*
* $id$
*/
//Variable Color (make same with the javascript)
$display_css['before_due'] 	= "color:black";
$display_css['over_due'] 	= "background-color:lightyellow; color:red";
$display_css['paid'] 		= "background-color:lightgrey; color:black";

//SET WHERE PARAMETER
$tmp = array();

if ($some_date != "") {
	$tmp[] = "sv_date = DATE '$some_date'";
} else {
	$tmp[] = "sv_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
}

if($_cus_code != '') {
	$tmp[] = "cus_code = '$_cus_code'";
}

if($_status == 'paid') {
	$tmp[]	= "sv_total_remain <= 0";
} else if($_status == 'unpaid') {
	$tmp[]	= "sv_total_amount = sv_total_remain";
} else if($_status == 'half_paid') {
	$tmp[]	= "sv_total_remain < sv_total_amount AND sv_total_remain > 0";
} else if($_status == 'has_bal') {
	$tmp[]	= "sv_total_remain > 0";
}

$strWhere = implode(" AND ", $tmp);

//continue sql
$sql ="
SELECT
	to_char(sv_due_date, 'YYMM') AS month,
	to_char(sv_due_date, 'Mon, YYYY') AS due_month,
	EXTRACT(WEEK FROM sv_due_date) AS due_week,
	sv_due_date,
	sv_code,
	cus_code,
	cus_full_name,
	to_char(sv_date, 'dd/Mon/YY') AS issued_date,
	to_char(sv_due_date, 'dd/Mon/YY') AS due_date,
	CASE
		WHEN sv_total_remain <= 0 THEN 'paid'
		WHEN sv_total_remain > 0 AND sv_due_date > CURRENT_TIMESTAMP THEN 'before_due'
		WHEN sv_total_remain > 0 AND sv_due_date < CURRENT_TIMESTAMP THEN 'over_due'
	END AS payment_status,
	CASE
		WHEN sv_total_remain <= 0 THEN 0
		WHEN sv_total_remain > 0 THEN sv_due_date - CURRENT_DATE
	END AS due_remain,
	sv_total_amount AS amount,
	sv_total_amount - sv_total_remain AS amount_paid,
	sv_total_remain AS remain_amount,
	to_char(sv_last_payment_date,'dd/Mon/YY') AS last_payment_date,
	'revise_service.php?_code='||sv_code AS go_page
FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_service ON cus_code = sv_cus_to
WHERE $strWhere
ORDER BY month, due_week, sv_due_date, sv_code";

// raw data
$rd = array();
$rdIdx = 0;

$cache = array("","","");
$group0 = array();

$res = query($sql);
while($col = fetchRowAssoc($res)) {

	$rd[] = array(
		$col['due_month'],			//0
		$col['due_week'],			//1
		$col['sv_code'],			//2
		$col['cus_code'],			//3
		$col['cus_full_name'], 		//4
		$col['issued_date'],		//5
		$col['due_date'], 			//6
		$col['payment_status'], 	//7
		$col['due_remain'], 		//8
		$col['amount'],				//9
		$col['amount_paid'], 		//10
		$col['remain_amount'],		//11
		$col['last_payment_date'],	//12 
		$col['go_page']				//13
	);

	if($cache[0] != $col['due_month']) {
		$cache[0] = $col['due_month'];
		$group0[$col['due_month']] = array();
	}

	if($cache[1] != $col['due_week']) {
		$cache[1] = $col['due_week'];
		$group0[$col['due_month']][$col['due_week']] = array();
	}
	
	if($cache[2] != $col['sv_code']) {
		$cache[2] = $col['sv_code'];
	}	

	$group0[$col['due_month']][$col['due_week']][$col['sv_code']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//grand summary
$grand_total = array (0,0,0);

//GROUP BY MONTH
foreach ($group0 as $month_name => $month) {
	echo "<span class=\"comment\"><b>[". $month_name. "]</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th width="10%">ISSUED DATE</th>
			<th width="10%">SERVICE NO</th>
			<th>CUSTOMER</th>
			<th width="10%">DUE DATE</th>
			<th width="5%">D/S</th>
			<th width="8%">AMOUNT</th>
			<th width="8%">PAID<br>(Rp)</th>
			<th width="8%">BAL<br>(Rp)</th>
			<th width="10%">LAST PAID</th>
		</tr>\n
END;

	//monthly summary
	$monthly_summary = array (0,0,0);
	$weekth = array();
	foreach ($month as $week_name => $due_week) {
		
		$weekth = getWeek($rd[$rdIdx][1] * 604800); 	//7day * 24 hour * 60 Min * 60 Sec
		
		print "<tr>\n";
		print "<td colspan=\"15\">{$weekth['string']}</td>\n";
		print "</tr>\n";
		
		//weekly summary
		$weekly_summary = array(0,0,0);

		foreach ($due_week as $invoice) {
		print "<tr>\n";
			cell($rd[$rdIdx][5], ' style="'.$display_css[$rd[$rdIdx][7]].'" align="center"');		//service date
			cell_link('<span class="bar">'.$rd[$rdIdx][2].'</span>', ' style="'.$display_css[$rd[$rdIdx][7]].'" align="center"', 	//service no
				' href="'.$rd[$rdIdx][13].'"');
			cell('['.trim($rd[$rdIdx][3]).'] '.$rd[$rdIdx][4], ' style="'.$display_css[$rd[$rdIdx][7]].'"');						//customer
			cell($rd[$rdIdx][5], ' style="'.$display_css[$rd[$rdIdx][7]].'" align="center"');		//due date
			cell($rd[$rdIdx][8], ' style="'.$display_css[$rd[$rdIdx][7]].'" align="center"');		//D/S
			cell(number_format($rd[$rdIdx][9]), ' style="'.$display_css[$rd[$rdIdx][7]].'" align="right"');  //amount
			cell(number_format($rd[$rdIdx][10]), ' style="'.$display_css[$rd[$rdIdx][7]].'" align="right"'); //paid
			cell(number_format($rd[$rdIdx][11]), ' style="'.$display_css[$rd[$rdIdx][7]].'" align="right"'); // bal
			cell($rd[$rdIdx][12], ' style="'.$display_css[$rd[$rdIdx][7]].'" align="center"');
			print "</tr>\n";

			//SUB TOTAL
			$weekly_summary[0] += $rd[$rdIdx][9]; 
			$weekly_summary[1] += $rd[$rdIdx][10];
			$weekly_summary[2] += $rd[$rdIdx][11];
			$rdIdx++;
		}

		print "<tr>\n";
		cell($weekth['string'], ' colspan="5"  align="right" align="right" style="color:darkblue"');
		cell(number_format($weekly_summary[0]), ' align="right" style="color:darkblue"');
		cell(number_format($weekly_summary[1]), ' align="right" style="color:darkblue"');
		cell(number_format($weekly_summary[2]), ' align="right" style="color:darkblue"');
		cell("&nbsp", ' align="right" style="color:darkblue"');
		print "</tr>\n";

		//Monthly TOTAL
		$monthly_summary[0] += $weekly_summary[0];
		$monthly_summary[1] += $weekly_summary[1];
		$monthly_summary[2] += $weekly_summary[2];
	}
	
	print "<tr>\n";
	cell('<b>'.$month_name.'<b>', ' colspan="5"  align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($monthly_summary[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($monthly_summary[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($monthly_summary[2]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br>\n";

	//Monthly TOTAL
	$grand_total[0] += $monthly_summary[0];
	$grand_total[1] += $monthly_summary[1];
	$grand_total[2] += $monthly_summary[2];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_f">
	<tr>
		<th width="10%">ISSUED DATE</th>
		<th width="10%">SERVICE NO</th>
		<th>CUSTOMER</th>
		<th width="10%">DUE DATE</th>
		<th width="5%">D/S</th>
		<th width="8%">AMOUNT</th>
		<th width="8%">PAID<br>(Rp)</th>
		<th width="8%">BAL<br>(Rp)</th>
		<th width="10%">LAST PAID</th>
	</tr>\n
END;
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="5"  align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br>\n";
?>