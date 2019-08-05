<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim

*
* $_po_date : Inquire Date
*
*/
//SET WHERE PARAMETER
$tmp[] = "cus_code = '$_cus_code'";
$tmp[] = "dep_dept = '$department'";

if ($some_date != "") $tmp[] = "dep_issued_date = DATE '$some_date'";
else $tmp[] = "dep_issued_date BETWEEN DATE '$period_from' AND '$period_to'";

$strWhere = implode(" AND ", $tmp);

$sql = "
SELECT
  d.dep_idx,
  d.dep_issued_date,
  to_char(d.dep_issued_date, 'Mon-YY, dd') AS issued_date,
  d.cus_code,
  d.dep_cus_name,
  d.turn_code,
  d.pay_idx,
  d.dep_amount,
  d.dep_remark,
  d.dep_method,
  d.dep_bank,
  CASE
 	WHEN d.turn_code != '' THEN 'return'
 	WHEN d.pay_idx IS NOT NULL THEN 'payment'
 	ELSE 'deposit'
  END AS activity_status,
  (SELECT bill_code FROM ".ZKP_SQL."_tb_payment WHERE pay_idx = d.pay_idx) AS bill_code,
  (SELECT SUBSTR(pay_note,9,1) FROM ".ZKP_SQL."_tb_payment WHERE pay_idx = d.pay_idx) AS note
FROM
  ".ZKP_SQL."_tb_deposit AS d
WHERE ". $strWhere . "
ORDER BY
  dep_issued_date, dep_idx";

// raw data
$rd = array();
$rdIdx = 0;

$cache = array("","","",""); // 3th level
$group0 = array();

$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['dep_idx'],		//0
		$col['cus_code'],		//1
		$col['dep_cus_name'],	//2
		$col['turn_code'],		//3
		$col['pay_idx'],		//4
		$col['issued_date'],	//5
		$col['dep_amount'],		//6
		$col['activity_status'],//7
		$col['bill_code'],		//8
		$col['dep_issued_date'],	//9
		$col['dep_remark'],		//10
		$col['note'],			//11
		$col['dep_method'],		//12
		$col['dep_bank']		//13
	);

	//1st grouping
	if($cache[0] != $col['cus_code']) {
		$cache[0] = $col['cus_code'];
	}

	
	if($cache[1] != $col['dep_issued_date']) {
		$cache[1] = $col['dep_issued_date'];
		$group0[$col['cus_code']][$col['dep_issued_date']] = array();
	}

	if($cache[2] != $col['dep_idx']) {
		$cache[2] = $col['dep_idx'];
	}

	$group0[$col['cus_code']][$col['dep_issued_date']][$col['dep_idx']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$grand_total = array(0,0,0,0);

//CUSTOMER
foreach ($group0 as $total1 => $group1) {

	echo "<span class=\"comment\"><b> CUSTOMER : [". $total1."] ".$rd[$rdIdx][2]. "</b></span>\n";
	print <<<END
	<table width="65%" class="table_f">
		<tr>
			<th width="15%">DATE</th>
			<th>DESCRIPTION</th>
			<th width="15%">AMOUNT<br/>(Rp)</th>
		</tr>\n
END;
	$amount2	= 0;
	$print_tr_1 = 0;

	print "<tr>\n";
	//DATE
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');

		if($print_tr_1++ > 0) print "<tr>\n";
		cell($rd[$rdIdx][5], ' valign="top" rowspan="'.$rowSpan.'"');

		$amount1	= 0;
		$print_tr_2 = 0;
		//ACTIVITY
		foreach($group2 as $total3 => $group3) {

			if($print_tr_2++ > 0) print "<tr>\n";
			if($rd[$rdIdx][7] == 'deposit')
				cell_link("Deposit [method > {$rd[$rdIdx][12]} {$rd[$rdIdx][13]}]", '', ' href="../billing/detail_deposit.php?_code='.$rd[$rdIdx][0].'"');
			else if($rd[$rdIdx][7] == 'return')
				cell_link("Return number ".$rd[$rdIdx][3], '', ' href="../billing/revise_return.php?_code='.$rd[$rdIdx][3].'"');
			else if($rd[$rdIdx][7] == 'payment')
				cell_link("[{$rd[$rdIdx][11]}] Payment to {$rd[$rdIdx][8]} [method > {$rd[$rdIdx][12]} {$rd[$rdIdx][13]}]", '', ' href="javascript:seePaymentDetail('.$rd[$rdIdx][4].')"');
			cell(number_format((double)$rd[$rdIdx][6],2), ' align="right" valign="top"');

			print "</tr>\n";

			$cus_name   = $rd[$rdIdx][2];
			$amount1  += $rd[$rdIdx][6];
			$rdIdx++;
		}
		$amount2  += $amount1;
	}

	print "<tr>\n";
	cell("<b>[". $total1."] ".$cus_name." &nbsp;</b>", ' colspan="2" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$amount2,2), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";
}
?>