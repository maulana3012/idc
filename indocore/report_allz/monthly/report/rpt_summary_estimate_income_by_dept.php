<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim
*
*
*/
// INCLUDE FILES 
include "rpt_summary_estimate_income_percentage.php";

//SET WHERE PARAMETER
function strwherePeriod($para_year, $para_month, $para_s_range=0, $para_e_range=1) {
	$_from_date	= date('Y-n-d', mktime(0,0,0, $para_month+$para_s_range, 1, $para_year));
	$_to_date	= date('Y-n-d', mktime(0,0,0, $para_month+$para_e_range, 1-1, $para_year));
	$period		= "between date '$_from_date' AND '$_to_date'";
	return $period;
}

$tmp_bill	= array();
$tmp_turn	= array();
$tmp_bill_month	= array();

if($_filter_vat == 'vat') {
	$tmp_bill[]	= "bill_vat > 0";
	$tmp_turn[]	= "turn_vat > 0";
	$tmp_bill_month[]	= "bill_vat > 0";
} else if ($_filter_vat == 'non') {
	$tmp_bill[]	= "bill_vat = 0.00";
	$tmp_turn[]	= "turn_vat = 0.00";
	$tmp_bill_month[]	= "bill_vat = 0.00";
}

$tmp_bill[]		= "bill_inv_date " . strwherePeriod($_year, $_month, -11);
$tmp_turn[]		= "bill_inv_date " . strwherePeriod($_year, $_month, -11);

$strWhereBill  	= implode(" AND ", $tmp_bill);
$strWhereTurn  	= implode(" AND ", $tmp_turn);
$strWhereBillMonth	= implode(" AND ", $tmp_bill_month);

$sql_bill = "
SELECT
  to_char(bill_inv_date, 'YYYYMM') AS inv_month_asc,
  to_char(bill_inv_date, 'Month, YY') AS inv_month,
  bill_dept AS inv_dept_asc,
  CASE
 	WHEN bill_dept = 'A' THEN 'Apotik'
	WHEN bill_dept = 'D' THEN 'Dealer'
	WHEN bill_dept = 'H' THEN 'Hospital'
	WHEN bill_dept = 'P' THEN 'Pharmaceutical'
  END AS inv_dept,
  sum(bill_total_billing) AS total_invoice
FROM ".ZKP_SQL."_tb_billing
WHERE $strWhereBill /*and bill_remain_amount > 0*/
GROUP BY inv_dept_asc, inv_dept, inv_month_asc, inv_month";

$sql_turn = "
SELECT
  to_char(bill_inv_date, 'YYYYMM') AS inv_month_asc,
  to_char(bill_inv_date, 'Month, YY') AS inv_month,
  turn_dept AS inv_dept_asc,
  CASE
 	WHEN turn_dept = 'A' THEN 'Apotik'
	WHEN turn_dept = 'D' THEN 'Dealer'
	WHEN turn_dept = 'H' THEN 'Hospital'
	WHEN turn_dept = 'P' THEN 'Pharmaceutical'
  END AS inv_dept,
  sum(turn_total_return)*-1 AS total_invoice
FROM ".ZKP_SQL."_tb_billing JOIN ".ZKP_SQL."_tb_return ON bill_code=turn_bill_code
WHERE $strWhereTurn AND turn_return_condition != 3
GROUP BY inv_dept_asc, inv_dept, inv_month_asc, inv_month";

$sql = "$sql_bill UNION $sql_turn ORDER BY inv_dept_asc, inv_month_asc";

// raw data
$rd 		= array();
$rdIdx		= 0;
$cache		= array("","");
$group0 	= array();
$mon_bill	= array();
$res		=& query($sql);
while($col =& fetchRowAssoc($res)) {
	if(!isset($mon_bill[$col["inv_dept_asc"].$col["inv_month_asc"]])) {
		$rd[] = array(
			$col['inv_dept_asc'],	//0
			$col['inv_dept'],		//1
			$col['inv_month_asc'],	//2
			$col['inv_month']		//3
		);

		//1st grouping
		if($cache[0] != $col['inv_dept_asc']) {
			$cache[0] = $col['inv_dept_asc'];
			$group0[$col['inv_dept_asc']] = array();
		}

		if($cache[1] != $col['inv_month_asc']) {
			$cache[1] = $col['inv_month_asc'];
		}

		$group0[$col['inv_dept_asc']][$col['inv_month_asc']] = 1;
		$mon_bill[$col["inv_dept_asc"].$col["inv_month_asc"]] = $col["total_invoice"];
	} else {
		$mon_bill[$col["inv_dept_asc"].$col["inv_month_asc"]] += $col["total_invoice"];
	}
}

//GROUP TOTAL
$grand_total = array(0,0);

// DEPT
foreach ($group0 as $total1 => $group1) {
echo "<span class=\"comment\"><b>Dept : {$rd[$rdIdx][1]}</b></span>\n";
print <<<END
<table class="table_f" width="400px">
	<tr height="15px">
		<th width="110px">MONTH<br />BILLING</th>
		<th width="50px">MONTH<br />GRADE</th>
		<th width="100px">%</th>
		<th width="100px">BILLING<br />AMOUNT</th>
		<th width="130px">ESTIMATE<br />INCOME</th>
	</tr>\n
END;

	$dept_amount = array(0,0);
	// PEIOD
	foreach($group1 as $total2 => $group2) {
		// grade, %, billing, estimate
		$idx			= $month[substr($rd[$rdIdx][2],4,2)];
		$data_ref		= array($amount_ref[1][$rd[$rdIdx][0].$rd[$rdIdx][2]], $amount_ref[4][$rd[$rdIdx][0].$idx]);
		$data_ref[2]	= ($data_ref[0] < $data_ref[1]) ? $data_ref[0] : $data_ref[1];
		$data_ref[3]	= ($data_ref[0] < $data_ref[1]) ? '#446fbe' : '#333333';
		$amount		= $mon_bill[$rd[$rdIdx][0].$rd[$rdIdx][2]];
		$remain		= $amount_ref[0][$rd[$rdIdx][0].$rd[$rdIdx][2]];
		$data		= array($grade[$idx-1], number_format((double)$data_ref[2],2), $amount, ($amount*$data_ref[2]) / 100);

		if($remain > 0) {
			print "<tr>\n";
			cell(' '.$rd[$rdIdx][3]);	// MONTH BILLING
			cell($data[0]." ");		// GRADE
			cell($data[1]." ", ' align="right" style="color:'.$data_ref[3].'"');	// %
			cell(number_format((double)$data[2], 0), ' align="right"');	// TOTAL BILLING PER MONTH
			cell(number_format((double)$data[3], 0), ' align="right"');	// TOTAL ESTIMATE INCOME
			print "</tr>\n";

			$dept_amount[0] += $data[2];
			$dept_amount[1] += $data[3];
		}

		$rdIdx++;
	}

	print "<tr>\n";
	cell("<b>TOTAL</b>", ' colspan="3" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$dept_amount[0], 0), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$dept_amount[1], 0), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$grand_total[0] += $dept_amount[0];
	$grand_total[1] += $dept_amount[1];
}

print <<<END
<span class="comment"><b>GRAND TOTAL</b></span>
<table class="table_f" width="400px">
	<tr height="15px">
		<th width="110px">MONTH<br />BILLING</th>
		<th width="50px">MONTH<br />GRADE</th>
		<th width="50px">%</th>
		<th width="100px">BILLING<br />AMOUNT</th>
		<th width="130px">ESTIMATE<br />INCOME</th>
	</tr>\n
END;
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="3" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[0], 0), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[1], 0), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
print "<span class=\"comment\"><i>Generate SUMMARY ESTIMATE INCOME by department at ".date('j-M-Y H:i:s ') . " by " . ucfirst($S->getValue("ma_account")) .".<i></span><br />\n";
?>