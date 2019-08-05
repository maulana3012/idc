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
$dept['A']	= 'Apotik Team Sales Data by Customer';
$dept['D']	= 'Dealer Team Sales Data by Customer';
$dept['H']	= 'Hospital Team Sales Data by Customer';
$dept['M']	= 'Marketing Team Sales Data by Customer';
$dept['P']	= 'Pharmaceutical Team Sales Data by Customer';
$dept['T']	= 'Tender Team Sales Data by Customer';

$tmp_bill	= array();
$tmp_sl		= array();
$tmp_turn	= array();
$tmp_bill_month	= array();
$tmp_sl_month	= array();
$tmp_turn_month	= array();

if(ZKP_URL == 'ALL') {
	if($_order_by != 'all'){
		$tmp_bill[]		= "bill_ordered_by = $_order_by";
		$tmp_bill_month[]	= "bill_ordered_by = $_order_by";
		$tmp_turn[]		= "turn_ordered_by = $_order_by";
		$tmp_turn_month[]	= "turn_ordered_by = $_order_by";
	}
} else {
	$tmp_bill[]		= "bill_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill_code,'billing')";
	$tmp_bill_month[]	= "bill_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill_code,'billing')";
	$tmp_turn[]		= "turn_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', turn_code,'billing_return')";
	$tmp_turn_month[]	= "turn_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', turn_code,'billing_return')";
	$tmp_sl[] 		= "bill_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill_code,'billing')";
	$tmp_sl_month[] 	= "bill_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill_code,'billing')";
}

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp_bill[]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_sl[] 	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_turn[]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_bill_month[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_sl_month[]	  = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_turn_month[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
} else {
	$tmp_bill[]	= "bill_type_billing in (1,2)";
	$tmp_sl[] 	= "bill_type_billing = 3";
	$tmp_bill_month[] = "bill_type_billing in (1,2)";
	$tmp_sl_month[]	  = "bill_type_billing = 3";
}

if ($_filter_doc == "I") {
	$tmp_turn[]		= "turn_code is null";
	$tmp_turn_month[]	= "turn_code is null";
} else if ($_filter_doc == "R") {
	$tmp_bill[]		= "bill_code is null";
	$tmp_sl[]		= "bill_code is null";
	$tmp_bill_month[]	= "bill_code is null";
	$tmp_sl_month[]		= "bill_code is null";
}

if($_filter_vat == 'vat') {
	$tmp_bill[]		= "bill_vat > 0";
	$tmp_sl[]		= "bill_vat > 0";
	$tmp_turn[]		= "turn_vat > 0";
	$tmp_bill_month[]	= "bill_vat > 0";
	$tmp_sl_month[]		= "bill_vat > 0";
	$tmp_turn_month[] 	= "turn_vat > 0";
} else if ($_filter_vat == 'non') {
	$tmp_bill[]		= "bill_vat = 0";
	$tmp_sl[]		= "bill_vat = 0";
	$tmp_turn[]		= "turn_vat = 0";
	$tmp_bill_month[]	= "bill_vat = 0";
	$tmp_sl_month[]		= "bill_vat = 0";
	$tmp_turn_month[]	= "turn_vat = 0";
}

if($_filter_dept != 'all') {
	$tmp_bill[]		= "bill_dept = '$_filter_dept'";
	$tmp_sl[]		= "bill_dept = '$_filter_dept'";
	$tmp_turn[]		= "turn_dept = '$_filter_dept'";
	$tmp_bill_month[]	= "bill_dept = '$_filter_dept'";
	$tmp_sl_month[]		= "bill_dept = '$_filter_dept'";
	$tmp_turn_month[]	= "turn_dept = '$_filter_dept'";
}

$show_default_dept = array('report', 'report_all');
if(!in_array($currentDept, $show_default_dept))
{
	$tmp_bill[]		= "bill_dept = '$department'";
	$tmp_sl[]		= "bill_dept = '$department'";
	$tmp_turn[]		= "turn_dept = '$department'";
	$tmp_bill_month[]	= "bill_dept = '$department'";
	$tmp_sl_month[]		= "bill_dept = '$department'";
	$tmp_turn_month[]	= "turn_dept = '$department'";	
}

$tmp_bill[]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
$tmp_sl[]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
$tmp_turn[]	= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to' AND turn_return_condition != 1 AND turn_return_condition IN (2,3,4)";
$tmp_turn_month[]	= "turn_return_condition != 1 AND turn_return_condition IN (2,3,4)";

$strWhereBill  	= implode(" AND ", $tmp_bill);
$strWhereSl  	= implode(" AND ", $tmp_sl);
$strWhereTurn 	= implode(" AND ", $tmp_turn);
$strWhereBillMonth	= implode(" AND ", $tmp_bill_month);
$strWhereSalesMonth	= implode(" AND ", $tmp_sl_month);
$strWhereTurnMonth	= implode(" AND ", $tmp_turn_month);

// SQL
include "rpt_summary_monthly_bill_customer_by_amount_sql.php";
/*
echo "<pre>";
var_dump($sql);
echo "</pre>";
exit;
*/
// raw data
$rd 	= array();
$rdIdx	= 0;
$cache	= array("","",""); // 3th level
$group0 = array();
$amount = array('A'=>0,'D'=>0,'H'=>0,'P'=>0);
$sub_amount = array();
$cus_amount	= array();
$a 		= '';

$res =& query($sql);
while($col =& fetchRowAssoc($res)) {
	if($a != $col['ship_to']) {
		$rd[] = array(
			$col['dept'],		//0
			$col['cug_name'],	//1
			$col['ship_to'],	//2
			$col['ship_to_name'],	//3
			$col['doc_period'],	//4
			$col['amount']		//5
		);

		//1st grouping
		if($cache[0] != $col['dept']) {
			$cache[0] = $col['dept'];
			$group0[$col['dept']] = array();
		}

		if($cache[1] != $col['cug_name']) {
			$cache[1] = $col['cug_name'];
			$group0[$col['dept']][$col['cug_name']] = array();
		}
	
		if($cache[2] != $col['ship_to']) {
			$cache[2] = $col['ship_to'];
		}

		$group0[$col['dept']][$col['cug_name']][$col['ship_to']] = 1;
	}
	$a = $col['ship_to'];

	//to get total amount each dept, cug name, ship_to, period
	if(empty($val[$col['dept']][$col['cug_name']][$col['ship_to']][$col['doc_period']]))
		$val[$col['dept']][$col['cug_name']][$col['ship_to']][$col['doc_period']] = $col['amount'];
	else	$val[$col['dept']][$col['cug_name']][$col['ship_to']][$col['doc_period']] += $col['amount'];

}
/*
echo "<pre>";
var_dump($val);
echo "</pre>";
*/
//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

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
			$mon = 0;
			for($k=0; $k<$mon_length; $k++) {

				if (empty($val[$rd[$rdIdx][0]][$rd[$rdIdx][1]][$rd[$rdIdx][2]][$period_month[$mon]]))
					$amount = 0;
				else	$amount = $val[$rd[$rdIdx][0]][$rd[$rdIdx][1]][$rd[$rdIdx][2]][$period_month[$mon]];
				cell(number_format((double)$amount), ' align="right"');

				if(!isset($cus_amount[$j]))  
					$cus_amount[$j]	  = $amount;
				else 	$cus_amount[$j]	  += $amount;

				if(!isset($group_amount[$k])) 
					$group_amount[$k] = $amount;
				else	$group_amount[$k] += $amount;

				$mon++;
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