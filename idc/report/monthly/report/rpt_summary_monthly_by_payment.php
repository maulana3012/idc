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
  to_char(bill_inv_date, 'Month, YYYY') AS inv_month,
  bill_dept AS inv_dept_asc,
  CASE
 	WHEN bill_dept = 'A' THEN 'Apotik'
	WHEN bill_dept = 'D' THEN 'Dealer'
	WHEN bill_dept = 'H' THEN 'Hospital'
	WHEN bill_dept = 'P' THEN 'Pharma'
  END AS inv_dept,
  sum(bill_total_billing) AS total_invoice
FROM ".ZKP_SQL."_tb_billing
WHERE $strWhereBill
GROUP BY inv_month_asc, inv_month, inv_dept_asc, inv_dept";

$sql_turn = "
SELECT
  to_char(bill_inv_date, 'YYYYMM') AS inv_month_asc,
  to_char(bill_inv_date, 'Month, YYYY') AS inv_month,
  turn_dept AS inv_dept_asc,
  CASE
 	WHEN turn_dept = 'A' THEN 'Apotik'
	WHEN turn_dept = 'D' THEN 'Dealer'
	WHEN turn_dept = 'H' THEN 'Hospital'
	WHEN turn_dept = 'P' THEN 'Pharma'
  END AS inv_dept,
  sum(turn_total_return)*-1 AS total_invoice
FROM ".ZKP_SQL."_tb_billing JOIN ".ZKP_SQL."_tb_return ON bill_code=turn_bill_code
WHERE $strWhereTurn AND turn_return_condition != 3
GROUP BY inv_month_asc, inv_month, inv_dept_asc, inv_dept";

$sql = "$sql_bill UNION $sql_turn ORDER BY inv_month_asc, inv_dept_asc";

// raw data
$rd 		= array();
$rdIdx		= 0;
$cache		= array("","");
$group0 	= array();
$mon_bill	= array();
$res		=& query($sql);
while($col =& fetchRowAssoc($res)) {
	if(!isset($mon_bill[$col["inv_month_asc"].$col["inv_dept_asc"]])) {
		$rd[] = array(
			$col['inv_month_asc'],		//0
			$col['inv_month'],			//1
			$col['inv_dept_asc'],		//2
			$col['inv_dept']			//3
		);

		//1st grouping
		if($cache[0] != $col['inv_month']) {
			$cache[0] = $col['inv_month'];
			$group0[$col['inv_month']] = array();
		}

		if($cache[1] != $col['inv_dept']) {
			$cache[1] = $col['inv_dept'];
		}

		$group0[$col['inv_month']][$col['inv_dept']] = 1;
		$mon_bill[$col["inv_month_asc"].$col["inv_dept_asc"]] = $col["total_invoice"];
	} else {
		$mon_bill[$col["inv_month_asc"].$col["inv_dept_asc"]] += $col["total_invoice"];
	}
}

//GROUP TOTAL
$grand_total = array();

//PERIOD
foreach ($group0 as $total1 => $group1) {
echo "<span class=\"comment\"><b>{$rd[$rdIdx][1]}</b></span>\n";
print <<<END
<table class="table_f" width="1150px">
	<tr height="15px">
		<th width="70px" rowspan="3">DEPT</th>
		<th width="80px" rowspan="3">TOTAL BILLING<br />(Rp)</th>
		<th width="840px" colspan="12">MONTHLY PAYMENT</th>
		<th width="80px" rowspan="3">TOTAL PAYMENT<br />(Rp)</th>
		<th width="80px" rowspan="3">BALANCE<br />(Rp)</th>
	</tr>
	<tr>\n
END;

	// VAR
	$_pYear		= substr($rd[$rdIdx][0],0,4);
	$_pMonth	= (substr($rd[$rdIdx][0],4,2) < 10) ? substr($rd[$rdIdx][0],5,2) : substr($rd[$rdIdx][0],4,2);
	$_pPeriod	= $_month_to.'-'.$_year_to;

	// PRINT GRADE
	$_tYear		= $_pYear;
	$_tMonth 	= $_pMonth;
	$_is_true	= true;
	for($i=1; $i<=12; $i++) {
		if($_tMonth>12) { $_tYear+=1; $_tMonth=1; }
		
		if($_is_true == true) {
			echo "\t\t<th width=\"80px\">".$grade[$i]."</th>\n";
		} else {
			echo "\t\t<th width=\"80px\"> </th>\n";
		}

		if($_pPeriod == $_tMonth.'-'.$_tYear) { $_is_true = false; }
		$_tMonth++;	
	}

print <<<END
	</tr>
	<tr>\n
END;

	// PRINT MON - YEAR
	$_tYear		= $_pYear;
	$_tMonth 	= $_pMonth;
	$_is_true	= true;
	for($i=0; $i<12; $i++) {
		if($_tMonth>12) { $_tYear+=1; $_tMonth=1; }

		if($_is_true == true) {
			echo "\t\t<th width=\"80px\">".substr($month[$_tMonth],0,3)."-".$_tYear	."</th>\n";
		} else {
			echo "\t\t<th width=\"80px\"></th>\n";
		}

		if($_pPeriod == $_tMonth.'-'.$_tYear) { $_is_true = false; }
		$_tMonth++;
	}

print <<<END
	</tr>\n
END;

	$dept_amount = array();
	$print_tr_1	 = 0;
	print "<tr>\n";
	//DEPARTMENT
	foreach($group1 as $total2 => $group2) {
		if($print_tr_1++ > 0) print "<tr>\n";
		cell($rd[$rdIdx][3]);	// DEPARTMENT
		cell(number_format((double)$mon_bill[$rd[$rdIdx][0].$rd[$rdIdx][2]], 0), ' align="right"');	// TOTAL BILLING PER MONTH

		$_tYear		= $_pYear;
		$_tMonth 	= $_pMonth;
		$_is_true	= true;
		$mon_payment = array();
		for($j=0; $j<12; $j++) {
			$amount = 0;
			if($_tMonth>12) { $_tYear+=1; $_tMonth=1; }

			// PAYMENT PER MONTH
			if($_is_true == true) {
				$whereBillMonth = ($strWhereBillMonth=='') ? "" : " AND $strWhereBillMonth";
				$sql_payMonth = "
					SELECT sum(pay_paid) 
					  FROM ".ZKP_SQL."_tb_billing JOIN ".ZKP_SQL."_tb_payment USING(bill_code)
					  WHERE bill_dept = '".$rd[$rdIdx][2]."' AND bill_inv_date ".strwhere($_pYear, $_pMonth)." AND pay_date ".strwhere($_tYear, $_tMonth)." $whereBillMonth
					";

				$res_month =& query($sql_payMonth);
				while($col_month =& fetchRow($res_month)) {
					$amount += $col_month[0];
				}
				cell(number_format((double)$amount,0), ' align="right"');
			} else {
				cell('', ' align="right" style="background-color:#F7F7F7"');
			}

			if($_pPeriod == $_tMonth.'-'.$_tYear) { $_is_true = false; }
			$_tMonth++;
			$mon_payment[$j]	= $amount;
			$dept_amount[$j+1]	+= $amount;
			$mon_payment[12]	+= $mon_payment[$j];
		}

		$mon_payment[13] = $mon_bill[$rd[$rdIdx][0].$rd[$rdIdx][2]] - $mon_payment[12];

		cell(number_format((double)$mon_payment[12], 0), ' align="right"');		//TOTAL PAYMENT IN A YEAR
		cell(number_format((double)$mon_payment[13], 0), ' align="right"');		//BALANCE

		// AMOUNTS
		$dept_amount[0]	 += $mon_bill[$rd[$rdIdx][0].$rd[$rdIdx][2]];
		$dept_amount[13] += $mon_payment[12];
		$dept_amount[14] += $mon_payment[13];
		$rdIdx++;
	}

	print "<tr>\n";
	cell("<b>TOTAL</b>", ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$dept_amount[0], 0), ' align="right" style="color:brown; background-color:lightyellow"');
	for($i=1; $i<=12; $i++) {
		cell(number_format((double)$dept_amount[$i]), ' align="right" style="color:brown; background-color:lightyellow"');
	}
	cell(number_format((double)$dept_amount[13], 0), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$dept_amount[14], 0), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	for($i=0; $i<15; $i++) {
		$grand_total[$i] += $dept_amount[$i];
	}
}

print <<<END
<span class="comment"><b>GRAND TOTAL</b></span>
<table class="table_f" width="1150px">
	<tr height="15px">
		<th width="70px" rowspan="3">DEPT</th>
		<th width="80px" rowspan="3">TOTAL BILLING<br />(Rp)</th>
		<th width="840px" colspan="12">MONTHLY PAYMENT</th>
		<th width="80px" rowspan="3">TOTAL PAYMENT<br />(Rp)</th>
		<th width="80px" rowspan="3">BALANCE<br />(Rp)</th>
	</tr>
	<tr>\n
END;
	for($i=1; $i<=12; $i++) {
		echo "\t\t<th width=\"80px\">".$grade[$i]."</th>\n";
	}
print <<<END
	</tr>
	<tr>\n
END;
	$_tMonth = $_month_from;
	$_tYear	 = $_year_from;
	for($i=0; $i<12; $i++) {
		if($_tMonth>12) { $_tMonth = $_tMonth-12; $_tYear+=1;}
		echo "\t\t<th width=\"80px\">".substr($month[$_tMonth],0,3)."-".substr($_tYear,2,2)."</th>\n";
		$_tMonth++;
	}
print <<<END
	</tr>\n
END;

print "<tr>\n";
cell("<b>TOTAL</b>", ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[0], 0), ' align="right" style="color:brown; background-color:lightyellow"');
for($i=1; $i<=12; $i++) {
	cell(number_format((double)$grand_total[$i], 0), ' align="right" style="color:brown; background-color:lightyellow"');
}
cell(number_format((double)$grand_total[13], 0), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[14], 0), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
print "<span class=\"comment\"><i>Generate SUMMARY MONTHLY by payment portion at ".date('j-M-Y H:i:s ') . " by " . ucfirst($S->getValue("ma_account")) .".<i></span><br />\n";
?>