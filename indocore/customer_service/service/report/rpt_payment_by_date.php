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

//SET WHERE PARAMETER
$tmp = array();

if ($some_date != "") {
	$tmp[] = "svpay_date = DATE '$some_date'";
} else {
	$tmp[] = "svpay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
}

if($_cus_code != '') {
	$tmp[] = "a.cus_code = '$_cus_code'";
}

$method_payment = array(
					"svpay_method = 'cash'", "svpay_method = 'check'", "svpay_method = 'giro'",
					"svpay_method = 'transfer'", "svpay_method = 'transfer' AND svpay_bank = 'DANAMON'",
					"svpay_method = 'transfer' AND svpay_bank = 'BCA1'", "svpay_method = 'transfer' AND svpay_bank = 'BCA2'",
					"svpay_method = 'transfer' AND svpay_bank = 'MANDIRI'", "svpay_method = 'transfer' AND svpay_bank = 'BII1'",
					"svpay_method = 'transfer' AND svpay_bank = 'BII2'"
				  );

for($i=0;$i<10;$i++){
	if($i==$_method && $_method != 'all') $tmp[] = $method_payment[$i];
}

$strWhere = implode(" AND ", $tmp);

$sql = "
SELECT
	to_char(svpay_date, 'YYMM') AS month,
	to_char(svpay_date, 'Mon, YYYY') AS pay_month,
	EXTRACT(WEEK FROM svpay_date) AS pay_week,
	svpay_date AS pay_date,
	sv_code AS service_no,
	sv_reg_no AS reg_no,
	to_char(sv_date, 'dd/Mon/YY') AS service_date,
	a.cus_code AS cus_code,
	a.cus_full_name AS cus_full_name, 
	sv_total_amount AS service_amount,
	sv_total_discount AS service_discount,
	sv_total_remain AS service_remain,
	svpay_idx AS payment_idx,
	CASE
	 	WHEN svpay_method = 'cash' THEN 'CASH'
 		WHEN svpay_method = 'check' THEN 'CHECK'
 		WHEN svpay_method = 'transfer' THEN 'T/S'
 		WHEN svpay_method = 'giro' THEN 'GIRO'
 		ELSE '-'
 	END AS payment_method,
	svpay_bank AS payment_bank,
	to_char(svpay_date, 'dd/Mon/YY') AS payment_date,
	svpay_paid AS payment_amount,
	svpay_remark AS payment_remark,
	'revise_service.php?_code='||sv_code AS go_page1,
	'../registration/revise_registration.php?_code=' || sv_reg_no AS go_page2
FROM
  ".ZKP_SQL."_tb_customer AS a
  JOIN ".ZKP_SQL."_tb_service AS b ON cus_code = sv_cus_to
  JOIN ".ZKP_SQL."_tb_service_payment AS c USING(sv_code)
WHERE $strWhere
ORDER BY month, pay_week, service_no, pay_date";

// raw data
$rd = array();
$rdIdx = 0;

$cache = array("","","", "");
$group0 = array();

$res = query($sql);
while($col = fetchRowAssoc($res)) {

	$rd[] = array(
		$col['pay_month'],			//0
		$col['pay_week'],			//1
		$col['service_no'],			//2
		$col['reg_no'],				//3
		$col['service_date'],		//4
		$col['cus_code'], 			//5
		$col['cus_full_name'],		//6
		$col['service_amount'], 	//7
		$col['service_discount'], 	//8
		$col['service_remain'], 	//9
		$col['payment_idx'],		//10
		$col['payment_method'], 	//11
		$col['payment_bank'],		//12
		$col['payment_date'],		//13
		$col['payment_amount'],		//14
		$col['pay_date'],			//15
		$col['payment_remark'],		//16
		$col['go_page1'],			//17
		$col['go_page2']			//18
	);

	if($cache[0] != $col['pay_month']) {
		$cache[0] = $col['pay_month'];
		$group0[$col['pay_month']] = array();
	}

	if($cache[1] != $col['pay_week']) {
		$cache[1] = $col['pay_week'];
		$group0[$col['pay_month']][$col['pay_week']] = array();
	}
	
	if($cache[2] != $col['service_no']) {
		$cache[2] = $col['service_no'];
		$group0[$col['pay_month']][$col['pay_week']][$col['service_no']] = array();
	}
	
	if($cache[3] != $col['payment_idx']) {
		$cache[3] = $col['payment_idx'];
	}

	$group0[$col['pay_month']][$col['pay_week']][$col['service_no']][$col['payment_idx']] = 1;
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
			<th width="7%">SERVICE DATE</th>
			<th width="10%">SERVICE NO</th>
			<th width="10%">REG NO</th>
			<th>CUSTOMER</th>
			<th width="6%">AMOUNT</th>
			<th width="7%">PAID<br> DATE</th>
			<th width="3%">PAID<br />METHOD</th>
			<th width="7%">BANK</th>
			<th width="6%">PAID<br>(Rp)</th>
			<th width="7%">BALANCE</th>
			<th width="7%">PAID<br> REMARK</th>
		</tr>\n
END;

	//monthly summary
	$monthly_summary = array (0,0,0);
	$weekth			 = array();

	foreach ($month as $week_name => $pay_week) {

		$weekth = getWeek($rd[$rdIdx][1] * 604800); 	//7day * 24 hour * 60 Min * 60 Sec

		print "<tr>\n";
		print "<td colspan=\"10\">{$weekth['string']}</td>\n";
		print "</tr>\n";

		//weekly summary
		$weekly_summary = array(0,0,0);
		$print_tr_1		= 0;
		foreach ($pay_week as $billing) {
			$rowSpan = 0;
			array_walk_recursive($billing, 'getRowSpan');

			if($print_tr_1++ > 0) print "<tr>\n";
			cell($rd[$rdIdx][4], ' align="center" valign="top" rowspan="'.$rowSpan.'"');					//service date
			cell_link('<span class="bar">'.$rd[$rdIdx][2].'</span>', ' align="center", valign="top" rowspan="'.$rowSpan.'"',
				' href="'.$rd[$rdIdx][17].'"');																//service no
			cell_link('<span class="bar">'.$rd[$rdIdx][3].'</span>', ' align="center", valign="top" rowspan="'.$rowSpan.'"',
				' href="'.$rd[$rdIdx][18].'"');																//service no
			cell($rd[$rdIdx][6], ' valign="top" rowspan="'.$rowSpan.'"');									//customer
			cell(number_format($rd[$rdIdx][7]), ' align="right", valign="top" rowspan="'.$rowSpan.'"');	//service amount

			$invoice_summary	= array();
			$invoice_summary[0] = $rd[$rdIdx][7];
			$invoice_summary[1] = 0;
			$invoice_summary[2] = $rd[$rdIdx][9];

			$payment_summary = 0;
			$print_tr_2		 = 0;
			foreach ($billing as $paid_data) {
				if($print_tr_2++ > 0) print "<tr>\n";
				cell($rd[$rdIdx][13], ' align="right"'); 				//date
				cell($rd[$rdIdx][11], ' align="center"');				//method
				cell($rd[$rdIdx][12], ' align="center"');				//bank
				cell(number_format($rd[$rdIdx][14]), ' align="right"'); //paid
				cell("&nbsp;");											//remain
				cell($rd[$rdIdx][16]);									//remark
				print "</tr>\n";

				$invoice_no			= $rd[$rdIdx][2];
				$invoice_summary[1] += $rd[$rdIdx][14];
				$rdIdx++;
			}

			print "<tr>\n";
			cell("$invoice_no", ' colspan="4"  align="right" align="right" style="color:blue"');
			cell(number_format($invoice_summary[0]), ' align="right" style="color:blue"');
			cell("&nbsp");
			cell("&nbsp");
			cell("&nbsp");
			cell(number_format($invoice_summary[1]), ' align="right" style="color:blue"');
			cell(number_format($invoice_summary[2]), ' align="right" style="color:blue"');
			cell("&nbsp");
			print "</tr>\n";

			//SUB TOTAL
			$weekly_summary[0] += $invoice_summary[0];  //amount
			$weekly_summary[1] += $invoice_summary[1]; 	//paid total
			$weekly_summary[2] += $invoice_summary[2]; 	//remain payment
		}

		print "<tr>\n";
		cell($weekth['string'], ' colspan="4"  align="right" align="right" style="color:brown"');
		cell(number_format($weekly_summary[0]), ' align="right" style="color:brown"');
		cell("&nbsp;");
		cell("&nbsp;");
		cell("&nbsp;");
		cell(number_format($weekly_summary[1]), ' align="right" style="color:brown"');
		cell(number_format($weekly_summary[2]), ' align="right" style="color:brown"');
		cell("&nbsp");
		print "</tr>\n";

		//Monthly TOTAL
		$monthly_summary[0] += $weekly_summary[0];
		$monthly_summary[1] += $weekly_summary[1];
		$monthly_summary[2] += $weekly_summary[2];
	}
	
	print "<tr>\n";
	cell('<b>'.$month_name.'<b>', ' colspan="4"  align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($monthly_summary[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
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
		<th width="7%">SERVICE DATE</th>
		<th width="10%">SERVICE NO</th>
		<th width="10%">REG NO</th>
		<th>CUSTOMER</th>
		<th width="6%">AMOUNT</th>
		<th width="7%">PAID<br> DATE</th>
		<th width="3%">PAID<br />METHOD</th>
		<th width="7%">BANK</th>
		<th width="6%">PAID<br>(Rp)</th>
		<th width="7%">BALANCE</th>
		<th width="7%">PAID<br> REMARK</th>
	</tr>\n
END;
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="4"  align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
cell("&nbsp", ' style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br>\n";
?>