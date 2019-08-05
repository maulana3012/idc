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
$channel["000"] = "Medical Dealer";
$channel["001"] = "Medicine Dist";
$channel["002"] = "Pharmacy Chain";
$channel["003"] = "Gen/ Specialty";
$channel["004"] = "Pharmaceutical";
$channel["005"] = "Hospital";
$channel["6.1"] = "M/L Marketing";
$channel["6.2"] = "Mail Order";
$channel["6.3"] = "Internet Business";
$channel["007"] = "Promotion & Other";
$channel["008"] = "Individual";
$channel["009"] = "Private use";
$channel["00S"] = "Service";

$tmp_bill	= array();
$tmp_turn	= array();
$tmp_sv		= array();
$tmp_paid	= array();
$tmp_svpay	= array();

if ($_filter_doc == "I") {
	$tmp_turn[]	= "t.turn_code = NULL";
	$tmp_paid[]	= "pay_paid > 0";
	$tmp_sv[]	= "sv_code is null";
	$tmp_svpay[]= "sv_code is null";
} else if ($_filter_doc == "R") {
	$tmp_bill[]	= "b.bill_code = NULL";
	$tmp_paid[]	= "pay_paid <= 0";
	$tmp_sv[]	= "sv_code is null";
	$tmp_svpay[]= "sv_code is null";
}

if($_order_by == 1) {
	$tmp_bill[] = "bill_ordered_by = 1";
	$tmp_turn[] = "turn_ordered_by = 1";
	$tmp_sv[]	= "sv_code is null"; 
	$tmp_svpay[]= "sv_code is null";
} else if($_order_by == 3) {
	$tmp_bill[] = "bill_code is null";
	$tmp_turn[] = "turn_code is null";
	$tmp_paid[]	= "bill_code is null";
}

if($_marketing != "all") {
	$tmp_bill[]	= "cus_responsibility_to = $_marketing";
	$tmp_turn[] = "cus_responsibility_to = $_marketing";
	$tmp_paid[] = "cus_responsibility_to = $_marketing";
	$tmp_sv[]	= "sv_code is null";
	$tmp_svpay[]= "sv_code is null";
}

if($_dept != 'all') {
	$tmp_bill[] = "b.bill_dept = '$_dept'";
	$tmp_turn[] = "t.turn_dept = '$_dept'";
	$tmp_paid[] = "b.bill_dept = '$_dept'";
	if($_dept != 'S') {
		$tmp_sv[]	= "sv_code is null";	
		$tmp_svpay[]= "sv_code is null";
	}
}

if ($some_date != "") {
	$tmp_bill[] = "b.bill_inv_date = DATE '$some_date'";
	$tmp_turn[] = "t.turn_return_date = DATE '$some_date'";
	$tmp_paid[] = "p.pay_date = DATE '$some_date'";
	$tmp_sv[]	= "sv_date = DATE '$some_date'";
	$tmp_svpay[]= "svpay_date = DATE '$some_date'";
} else {
	$tmp_bill[]	= "b.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_turn[]	= "t.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_paid[]	= "p.pay_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_sv[]	= "sv_date BETWEEN DATE '$period_from' AND '$period_to'";	
	$tmp_svpay[]= "svpay_date BETWEEN DATE '$period_from' AND '$period_to'";
}

if($_vat == 'vat') {
	$tmp_bill[]	= "b.bill_vat > 0";
	$tmp_turn[]	= "t.turn_vat > 0";
	$tmp_paid[]	= "b.bill_vat > 0";
	$tmp_sv[]	= "sv_code is null";
	$tmp_svpay[]= "sv_code is null";
} else if($_vat == 'vat-IO') {
	$tmp_bill[]	= "b.bill_vat > 0 AND substr(b.bill_code,2,1) = 'O'";
	$tmp_turn[]	= "t.turn_vat > 0";
	$tmp_paid[]	= "b.bill_vat > 0 AND substr(b.bill_code,2,1) = 'O'";
	$tmp_sv[]	= "sv_code is null";
	$tmp_svpay[]= "sv_code is null";
}else if($_vat == 'vat-IP') {
	$tmp_bill[]	= "b.bill_vat > 0 AND substr(b.bill_code,2,1) = 'P'";
	$tmp_turn[]	= "t.turn_code = NULL";
	$tmp_paid[]	= "b.bill_vat > 0 AND substr(b.bill_code,2,1) = 'P'";
	$tmp_sv[]	= "sv_code is null";
	$tmp_svpay[]= "sv_code is null";
} else if ($_vat == 'non') {
	$tmp_bill[]	= "b.bill_vat = 0";
	$tmp_turn[]	= "t.turn_vat = 0";
	$tmp_paid[]	= "b.bill_vat = 0";
	$tmp_sv[]	= "sv_code is null";
	$tmp_svpay[]= "sv_code is null";
}

$tmp_paid[]	  = "pay_note IN ('USUAL','DEPOSIT-A', 'RETURN')";

$strWhereBill    = implode(" AND ", $tmp_bill);
$strWhereTurn    = implode(" AND ", $tmp_turn);
$strWherePayment = implode(" AND ", $tmp_paid);
$strWhereService = implode(" AND ", $tmp_sv);
$strWhereSVPay	 = implode(" AND ", $tmp_svpay);
$amounttotal	 = array(0,0,0,0,0);	//0.billing before vat, 1.billing after vat, 2.paid billing, 3.remain billing, 4.rate
$grandamount	 = 0;

print <<<END
	<table width="70%" class="table_f">
		<tr>
			<th width="25%">TEAM</th>
			<th width="15%">BILLING<br />(Rp)</th>
			<th width="10%">COMPOSITION<br />RATE</th>
			<th width="15%">FULL BILLING<br />(Rp)</th>
			<th width="12%">PAID<br />BILLING</th>
			<th width="12%">REMAIN<br />BILLING</th>
		</tr>\n
END;

$sql = "
	SELECT SUM(b.bill_amount_qty_unit_price) as amount
	FROM ".ZKP_SQL."_tb_customer AS c JOIN ".ZKP_SQL."_tb_billing AS b ON bill_ship_to = cus_code WHERE $strWhereBill
  UNION
	SELECT -(SUM(t.turn_amount_qty_unit_price)) as amount
	FROM ".ZKP_SQL."_tb_customer AS c JOIN ".ZKP_SQL."_tb_return AS t ON turn_ship_to = cus_code WHERE $strWhereTurn AND turn_return_condition IN (2,3,4)
  UNION
	SELECT SUM(sv_total_amount) as amount
	FROM ".ZKP_SQL."_tb_customer AS c JOIN ".ZKP_SQL."_tb_service AS b ON sv_cus_to = cus_code WHERE $strWhereService
	";
$res =& query($sql);

while($col =& fetchRowAssoc($res)) {
	$grandamount += $col['amount'];
}

foreach ($channel as $value => $key) {
	$amount = array(0,0,0,0,0); //0.billing before vat, 1.billing after vat, 2.paid billing, 3.remain billing, 4.rate

	$sql_bill	= "
			SELECT
				'billing' AS type,
				SUM(b.bill_amount_qty_unit_price) AS total_before_vat,
				SUM(b.bill_total_billing) AS total_billing,
				SUM(b.bill_remain_amount) AS remain_billing,
				'billing' AS condition
			FROM
				".ZKP_SQL."_tb_customer AS c
				JOIN ".ZKP_SQL."_tb_billing AS b ON b.bill_cus_to = c.cus_code
			WHERE c.cus_channel = '$value' AND $strWhereBill
			GROUP BY condition
		UNION
			SELECT
				'return' AS type,
				-(SUM(t.turn_amount_qty_unit_price)) AS total_before_vat,
				-(SUM(t.turn_total_return)) AS total_billing,
				NULL AS remain_billing,
				CASE
					WHEN t.turn_return_condition=1 THEN 'turn_1'
					WHEN t.turn_return_condition=2 THEN 'turn_2'
					WHEN t.turn_return_condition=3 THEN 'turn_3'
					WHEN t.turn_return_condition=4 THEN 'turn_4'
				END AS condition
			FROM
				".ZKP_SQL."_tb_customer AS c
				JOIN ".ZKP_SQL."_tb_return AS t ON t.turn_cus_to = c.cus_code
			WHERE c.cus_channel = '$value' AND $strWhereTurn AND t.turn_return_condition IN (2,3,4)
			GROUP BY condition
		UNION
			SELECT
				'billing' AS type,
				SUM(sv_total_amount) AS total_before_vat,
				SUM(sv_total_amount) AS total_billing,
				SUM(sv_total_remain) AS remain_billing,
				'billing' AS condition
			FROM
				".ZKP_SQL."_tb_customer AS c
				JOIN ".ZKP_SQL."_tb_service AS b ON sv_cus_to = c.cus_code
			WHERE c.cus_channel = '$value' AND $strWhereService
			GROUP BY condition
		";

	$res_bill =& query($sql_bill);
	while($col_bill =& fetchRowAssoc($res_bill)) {
		$amount[0]	+= $col_bill['total_before_vat'];
		$amount[1]	+= $col_bill['total_billing'];
		$amount[3]	+= $col_bill['remain_billing'];
	}

	$sql_paid = "
			SELECT SUM(p.pay_paid)  AS paid_billing
			FROM
				".ZKP_SQL."_tb_customer AS c
				JOIN ".ZKP_SQL."_tb_billing AS b ON b.bill_cus_to = c.cus_code
				JOIN ".ZKP_SQL."_tb_payment AS p USING(bill_code)
			WHERE c.cus_channel = '$value' AND $strWherePayment
		UNION
			SELECT SUM(svpay_paid)  AS paid_billing
			FROM
				".ZKP_SQL."_tb_customer AS c
				JOIN ".ZKP_SQL."_tb_service AS b ON sv_cus_to = c.cus_code
				JOIN ".ZKP_SQL."_tb_service_payment AS p USING(sv_code)
			WHERE c.cus_channel = '$value' AND $strWhereSVPay
		";
	$res_paid =& query($sql_paid);
	while($col_paid =& fetchRowAssoc($res_paid)) {
		$amount[2]	+= $col_paid['paid_billing'];
	}

	if($amount[0] == 0) $amount[4] = 0;
	else if($amount[0] > 0) {
		$amount[4] = $amount[0]*100/$grandamount;
		if($amount[4] < 0) $amount[4] = $amount[4]*-1;
	}
	else if($amount[0] < 0) {
		$amount[4] = $amount[0]*100/$grandamount;
		if($amount[4] > 0) $amount[4] = -($amount[4]);
	}

	//PRINT VALUE
	print "<tr>\n";	
	print "\t<td><a href=\"javascript:seeDetail('$value')\">$key</a></td>\n";
	cell(number_format($amount[0]), ' align="right"');			//billing before vat
	cell(number_format($amount[4],2)."%", ' align="right"');	//rate
	cell(number_format($amount[1]), ' align="right"');			//billing after vat
	cell(number_format($amount[2]), ' align="right"');			//paid billing
	cell(number_format($amount[3]), ' align="right"');			//remain billing
	print "</tr>\n";

	$amounttotal[0] += $amount[0];	
	$amounttotal[1] += $amount[1];
	$amounttotal[2] += $amount[2];
	$amounttotal[3] += $amount[3];
	$amounttotal[4] += $amount[4];
}

print "\t<tr>\n";
cell('TOTAL BILLING', ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($amounttotal[0]), ' align="right" style="color:brown; background-color:lightyellow"');	//billing before vat
cell(number_format($amounttotal[4],2)."%", ' align="right" style="color:brown; background-color:lightyellow"');	//rate
cell(number_format($amounttotal[1]), ' align="right" style="color:brown; background-color:lightyellow"');	//billing after vat
cell(number_format($amounttotal[2]), ' align="right" style="color:brown; background-color:lightyellow"');	//paid billing
cell(number_format($amounttotal[3]), ' align="right" style="color:brown; background-color:lightyellow"');	//remain billing
print "\t</tr>\n";
print "</table><br />\n";
?>