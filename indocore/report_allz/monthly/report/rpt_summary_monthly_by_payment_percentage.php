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
//SET WHERE PARAMETER
$tmp_bill	= array();
$tmp_turn	= array();
$tmp_bill_month	= array();

if($_filter_dept != 'all') {
	$tmp_bill[]	= "bill_dept = '$_filter_dept'";
	$tmp_turn[]	= "turn_dept = '$_filter_dept'";
}

if ($_filter_group != 'all') {
	$tmp_bill[]	= "bill_cus_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_filter_group')";
	$tmp_turn[]	= "turn_cus_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_filter_group')";
	$tmp_bill_month[]	= "bill_cus_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_filter_group')";
}

if($_filter_vat == 'vat') {
	$tmp_bill[]	= "bill_vat > 0";
	$tmp_turn[]	= "turn_vat > 0";
	$tmp_bill_month[]	= "bill_vat > 0";
} else if ($_filter_vat == 'non') {
	$tmp_bill[]	= "bill_vat = 0.00";
	$tmp_turn[]	= "turn_vat = 0.00";
	$tmp_bill_month[]	= "bill_vat = 0";
}

$tmp_bill[]		= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
$tmp_turn[]		= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";

$strWhereBill  	= implode(" AND ", $tmp_bill);
$strWhereTurn 	= implode(" AND ", $tmp_turn);
$strWhereBillMonth	= implode(" AND ", $tmp_bill_month);

function strwhere($para_year, $para_month) {
	$_from_date	= date('Y-n-d', mktime(0,0,0, $para_month, 1, $para_year));
	$_to_date	= date('Y-n-d', mktime(0,0,0, $para_month+1, 1-1, $para_year));
	$period		= "between date '$_from_date' AND '$_to_date'";
	return $period;
}

$sql_bill = "
SELECT
  to_char(bill_inv_date, 'YYYYMM') AS inv_month_asc,
  to_char(bill_inv_date, 'Month YY') AS inv_month,
  sum(bill_total_billing) AS total_invoice
FROM ".ZKP_SQL."_tb_billing
WHERE $strWhereBill
GROUP BY inv_month_asc, inv_month";

$sql_turn = "
SELECT
  to_char(bill_inv_date, 'YYYYMM') AS inv_month_asc,
  to_char(bill_inv_date, 'Month YY') AS inv_month,
  sum(turn_total_return)*-1 AS total_invoice
FROM ".ZKP_SQL."_tb_billing JOIN ".ZKP_SQL."_tb_return ON bill_code=turn_bill_code
WHERE $strWhereTurn AND turn_return_condition != 3
GROUP BY inv_month_asc, inv_month";

$sql = "$sql_bill UNION $sql_turn ORDER BY inv_month_asc";

// raw data
$rd 		= array();
$rdIdx		= 0;
$cache		= array("");
$group0 	= array();
$mon_bill	= array();
$res		=& query($sql);
while($col =& fetchRowAssoc($res)) {
	if(!isset($mon_bill[$col["inv_month_asc"]])) {
		$rd[] = array(
			$col['inv_month_asc'],		//0
			$col['inv_month'],			//1
			$col["total_invoice"]		//2
		);

		//1st grouping
		if($cache[0] != $col['inv_month']) {
			$cache[0] = $col['inv_month'];
		}

		$group0[$col['inv_month']] = 1;
		$mon_bill[$col["inv_month_asc"]] = $col["total_invoice"];
	} else {
		$mon_bill[$col["inv_month_asc"]] += $col["total_invoice"];
	}
}

// TABLE
print <<<END
<table class="table_f" width="910px">
	<tr height="15px">
		<th width="100px" rowspan="2">MONTH<br />BILLING</th>
		<th width="720px" colspan="12">MONTHLY PAYMENT PERCENTAGE</th>
		<th width="60px" rowspan="2">TOTAL</th>
	</tr>
	<tr>
		<th width="60px">1st</th>
		<th width="60px">2nd</th>
		<th width="60px">3th</th>
		<th width="60px">4th</th>
		<th width="60px">5th</th>
		<th width="60px">6th</th>
		<th width="60px">7th</th>
		<th width="60px">8th</th>
		<th width="60px">9th</th>
		<th width="60px">10th</th>
		<th width="60px">11th</th>
		<th width="60px">12th</th>
	</tr>\n
END;

$grand_percentage = array();
foreach ($group0 as $total1 => $group1) {
	print "\t<tr>\n";
	cell($rd[$rdIdx][1]);		// MONTH BILLING

	// VAR
	$_pYear		= substr($rd[$rdIdx][0],0,4);
	$_pMonth	= (substr($rd[$rdIdx][0],4,2) < 10) ? substr($rd[$rdIdx][0],5,2) : substr($rd[$rdIdx][0],4,2);
	$_pPeriod	= $_month_to.'-'.$_year_to;

	$_tYear			= $_pYear;
	$_tMonth 		= $_pMonth;
	$_is_true		= true;
	$tot_percentage = 0;
	for($i=0; $i<12; $i++) {
		$amount = 0;
		if($_tMonth>12) { $_tYear+=1; $_tMonth=1; }

		// PAYMENT PER MONTH
		if($_is_true == true) {
			$whereBillMonth = ($strWhereBillMonth=='') ? "" : " AND $strWhereBillMonth";
			$sql_payMonth = "
				SELECT sum(pay_paid) 
				  FROM ".ZKP_SQL."_tb_billing JOIN ".ZKP_SQL."_tb_payment USING(bill_code)
				  WHERE bill_dept = '$_filter_dept' AND bill_inv_date ".strwhere($_pYear, $_pMonth)." AND pay_date ".strwhere($_tYear, $_tMonth)." $whereBillMonth
				";

			$res_month =& query($sql_payMonth);
			while($col_month =& fetchRow($res_month)) {
				$amount += $col_month[0];
			}

			$percentage = number_format(($amount * 100) / $mon_bill[$rd[$rdIdx][0]] ,2);
			$tot_percentage			+= $percentage;
			$grand_percentage[$i]	+= $percentage;

			cell(number_format((double)$percentage,2) . " % ", ' align="right"'); // TOTAL PERCENTAGE PER MONTH PAYMENT
		} else {
			cell('', ' align="right" style="background-color:#F0F0F0"');
		}
		if($_pPeriod == $_tMonth.'-'.$_tYear) { $_is_true = false; }
		$_tMonth++;
	}

	cell(number_format((double)$tot_percentage,2)." % "/* .number_format(100-$tot_percentage,2) */, ' align="right"');	// TOTAL PERCENTAGE PER MONTH BILLING
	print "\t</tr>\n";

	$grand_percentage[12] += $tot_percentage;
	$rdIdx++;
}

// TOTAL PERCENTAGE
print "\t<tr>\n";
cell("TOTAL", ' align="right" style="color:brown; background-color:lightyellow"');
$j = 0;
for($i=$mon_length; $i>0; $i--) {
	cell(number_format((double)$grand_percentage[$j++] , 2) ." % ", ' align="right" style="color:brown; background-color:lightyellow"');
}
for($i=12; $i>$mon_length; $i--) {
	cell("", ' align="right" style="color:brown; background-color:lightyellow"');
}
cell(/*number_format((double)$grand_percentage[12] , 2) ." % "*/'', ' align="right" style="color:brown; background-color:lightyellow"');
print "\t</tr>\n";

// DIVIDE
print "\t<tr>\n";
cell("DIVIDE", ' align="right" style="color:brown; background-color:#FFFFFF"');
$j = 0;
for($i=$mon_length; $i>0; $i--) {
	cell($i, ' align="center" style="color:brown; background-color:#FFFFFF"');
}
for($i=12; $i>$mon_length; $i--) {
	cell("", ' align="center" style="color:brown; background-color:#FFFFFF"');
}
cell(''/*$mon_length*/, ' align="center" style="color:brown; background-color:#FFFFFF"');
print "\t</tr>\n";

// TOTAL AVERAGE
print "\t<tr height=\"20px\">\n";
cell("AVERAGE", ' align="right" style="color:brown; background-color:lightyellow"');
$j = 0;
for($i=$mon_length; $i>0; $i--) {
	cell(number_format(($grand_percentage[$j++]) / $i , 2) ." % ", ' align="right" style="color:brown; background-color:lightyellow"');
}
for($i=12; $i>$mon_length; $i--) {
	cell("", ' align="right" style="color:brown; background-color:lightyellow"');
}
cell(/*number_format(($grand_percentage[12]) / $i , 2) ." % "*/'', ' align="right" style="color:brown; background-color:lightyellow"');
print "\t</tr>\n";
print "</table>\n";
print "<span class=\"comment\"><i>Generate SUMMARY MONTHLY by payment percentage at ".date('j-M-Y H:i:s ') . " by " . ucfirst($S->getValue("ma_account")) .".<i></span><br />\n";
?>