<?php
//GLOBAL	
$_filter_vat	= isset($_GET['cboFilterVat']) ? $_GET['cboFilterVat'] : "all";
$_month_from	= date('n', mktime(0,0,0, $_month-11, 1, $_year));
$_year_from		= date('Y', mktime(0,0,0, $_month-11, 1, $_year));
$_month_to		= $_month;//($_month < 10) ? '0'.$_month : $_month;	
$_year_to		= $_year;
$period_from	= date('Y-n-d', mktime(0,0,0, $_month_from, 1, $_year_from));
$period_to		= date('Y-n-d', mktime(0,0,0, $_month_to+1, 1-1, $_year_to));
$_tGrade		= array();
	for($i=1; $i<=12; $i++) {
		if($_month_from > 12) {
			$_month_from = 1;
			$_year_from  = $_year_from+1;
		}
		$_tMonth = ($_month_from<10) ? "0".$_month_from : $_month_from;
		$_tYear  = $_year_from;
		$_tGrade[$i] = $_tYear.$_tMonth;
		$_month_from++;		
	}

//SET WHERE PARAMETER
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
	$tmp_bill_month[]	= "bill_vat = 0";
}

$tmp_bill[]		= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
$tmp_turn[]		= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";

$strWhereBill  	= implode(" AND ", $tmp_bill);
$strWhereTurn 	= implode(" AND ", $tmp_turn);
$strWhereBillMonth	= implode(" AND ", $tmp_bill_month);

$sql_bill = "
SELECT
  bill_dept AS inv_dept,
  to_char(bill_inv_date, 'YYYYMM') AS inv_month,
  sum(bill_total_billing) AS total_invoice
FROM ".ZKP_SQL."_tb_billing
WHERE $strWhereBill
GROUP BY inv_dept, inv_month";

$sql_turn = "
SELECT
  turn_dept AS inv_dept,
  to_char(bill_inv_date, 'YYYYMM') AS inv_month,
  sum(turn_total_return)*-1 AS total_invoice
FROM ".ZKP_SQL."_tb_billing JOIN ".ZKP_SQL."_tb_return ON bill_code=turn_bill_code
WHERE $strWhereTurn AND turn_return_condition != 3
GROUP BY inv_dept, inv_month";

$sql = "$sql_bill UNION $sql_turn ORDER BY inv_dept, inv_month";

// raw data
$rd 		= array();
$rdIdx		= 0;
$cache		= array("","");
$group0 	= array();
$mon_bill	= array();
$res		=& query($sql);
while($col =& fetchRowAssoc($res)) {
	if(!isset($mon_bill[$col["inv_dept"].$col["inv_month"]])) {
		$rd[] = array(
			$col['inv_dept'],	//0
			$col['inv_month']	//1
		);

		//1st grouping
		if($cache[0] != $col['inv_dept']) {
			$cache[0] = $col['inv_dept'];
			$group0[$col['inv_dept']] = array();
		}

		if($cache[1] != $col['inv_month']) {
			$cache[1] = $col['inv_month'];
		}

		$group0[$col['inv_dept']][$col['inv_month']] = 1;
		$mon_bill[$col["inv_dept"].$col["inv_month"]] = $col["total_invoice"];
	} else {
		$mon_bill[$col["inv_dept"].$col["inv_month"]] += $col["total_invoice"];
	}
}

$amount_ref	 	= array();	// 0.billing balance, 1.% remain side, 2. temp, 3. , 4. % average bottom
$grand_payment	= array();
foreach ($group0 as $total1 => $group1) {
	foreach ($group1 as $total2) {
		// VAR
		$_monBill 	= $mon_bill[$rd[$rdIdx][0].$rd[$rdIdx][1]];
		$_pYear		= substr($rd[$rdIdx][1],0,4);
		$_pMonth	= (substr($rd[$rdIdx][1],4,2) < 10) ? substr($rd[$rdIdx][1],5,1) : substr($rd[$rdIdx][1],4,2);
		$_pPeriod	= $_year_to.$_month_to;

		$_tYear		= $_pYear;
		$_tMonth 	= $_pMonth;
		$amount_per	= 0;
		$_is_true	= true;
		for($i=12; $i>0; $i--) {
			$amount = 0;
			if($_tMonth>12) { $_tYear+=1; $_tMonth=1; }

			if($_is_true) {
				// PAYMENT PER MONTH
				$whereBillMonth = ($strWhereBillMonth=='') ? "" : " AND $strWhereBillMonth";
				$sql_payMonth = "
					SELECT sum(pay_paid) 
					FROM ".ZKP_SQL."_tb_billing JOIN ".ZKP_SQL."_tb_payment USING(bill_code)
					WHERE bill_dept = '".$rd[$rdIdx][0]."' AND bill_inv_date ".strwherePeriod($_pYear, $_pMonth)."
					AND pay_date ".strwherePeriod($_tYear, $_tMonth)." $whereBillMonth";

				$res_month =& query($sql_payMonth);
				while($col_month =& fetchRow($res_month)) { $amount += $col_month[0]; }
				$percentage = number_format(($amount * 100) / $_monBill ,2);

				$grand_payment[$rd[$rdIdx][0].$rd[$rdIdx][1]] += $amount;
				$amount_ref[2][$rd[$rdIdx][0].$i] += $percentage;	// % bottom
				$amount_ref[3][$rd[$rdIdx][0].$i][] = $percentage;
				$amount_per += $percentage;
			}

			if($_pPeriod == $_tYear.$_tMonth) { $_is_true = false; }
			$_tMonth++;
		}
		$amount_ref[0][$rd[$rdIdx][0].$rd[$rdIdx][1]] = $_monBill - $grand_payment[$rd[$rdIdx][0].$rd[$rdIdx][1]];	// Billing Balance
		$amount_ref[1][$rd[$rdIdx][0].$rd[$rdIdx][1]] = number_format(100 - $amount_per,2);	// % side
		$rdIdx++;
	}
}

$i = 0;
$j = "";
foreach($amount_ref[2] as $key => $val) {
	if($j != substr($key,0,1)) { $j = substr($key,0,1); $i=0; }

	$amount_ref[4][$key] = number_format((double)$val / substr($key, 1), 2);
	$i++;
}
?>