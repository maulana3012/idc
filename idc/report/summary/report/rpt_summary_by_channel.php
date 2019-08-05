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

if(ZKP_FUNCTION == 'ALL') {
	if($_order_by != 'all'){
		$tmp_bill[]		= "b.bill_ordered_by = $_order_by";
		$tmp_turn[]		= "t.turn_ordered_by = $_order_by";
		if($_order_by == '1') {
			$tmp_paid[] 	= "substr(bill_code,1,1) = 'I'";
		} else if($_order_by == '2') {
			$tmp_sv[]		= "sv_code is null";
			$tmp_svpay[]	= "sv_code is null";
			$tmp_paid[] 	= "substr(bill_code,1,1) = 'M'";
		}
	}
} else {
	$tmp_bill[]		= "b.bill_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', b.bill_code,'billing')";
	$tmp_turn[]		= "t.turn_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', t.turn_code,'billing_return')";
	$tmp_sv[]		= "".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', sv_code,'service')";
	if($cboFilter[1][ZKP_URL][0][0] == '1') {
		$tmp_paid[] 	= "substr(bill_code,1,1) = 'I' AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', b.bill_code,'billing')";
		$tmp_svpay[] 	= "".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', sv_code,'service')";
	} else if($cboFilter[1][ZKP_URL][0][0] == '2') {
		$tmp_sv[]		= "sv_code is null";
		$tmp_svpay[]	= "sv_code is null";
		$tmp_paid[] 	= "substr(bill_code,1,1) = 'M' AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', b.bill_code,'billing')";
	}
}

if ($_filter_doc == "I") {
	$tmp_turn[]	= "t.turn_code = NULL";
	$tmp_paid[]	= "pay_note IN ('USUAL','DEPOSIT-A', 'RETURN','CROSS_TRANSFER+','CROSS_TRANSFER-')";
} else if ($_filter_doc == "R") {
	$tmp_bill[]	= "b.bill_code = NULL";
	$tmp_paid[]	= "pay_paid <= 0 AND pay_note IN ('USUAL','DEPOSIT-A', 'RETURN')";
	$tmp_sv[]	= "sv_code is null";
	$tmp_svpay[]= "sv_code is null";
} else if ($_filter_doc == "CT") {
	$tmp_bill[]	= "b.bill_code = NULL";
	$tmp_turn[]	= "t.turn_code = NULL";
	$tmp_sv[]	= "sv_code is null";
	$tmp_paid[]	= "pay_note IN ('CROSS_TRANSFER+')";
	$tmp_svpay[]= "sv_code is null";
} else {
	$tmp_paid[]	  = "pay_note IN ('USUAL','DEPOSIT-A', 'RETURN','CROSS_TRANSFER+','CROSS_TRANSFER-')";
}

if($_marketing != "all") {
	$tmp_bill[]	= "cus_responsibility_to = $_marketing";
	$tmp_turn[] = "cus_responsibility_to = $_marketing";
	$tmp_paid[] = "cus_responsibility_to = $_marketing";
	$tmp_sv[]	= "sv_code is null";
	$tmp_svpay[]= "sv_code is null";
}

if($_dept != 'all' && $_dept != 'CS') {
	$tmp_bill[] = "b.bill_dept = '$_dept'";
	$tmp_turn[] = "t.turn_dept = '$_dept'";
	$tmp_paid[] = "b.bill_dept = '$_dept'";
	$tmp_sv[]	= "sv_code is null";
	$tmp_svpay[]= "sv_code is null";
} else if($_dept == 'CS') {
	$tmp_bill[] = "b.bill_code is null";
	$tmp_turn[] = "t.turn_code is null";
	$tmp_paid[] = "b.bill_code is null";
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
}
/*
$tmp_bill[] = "bill_code in (
'IO-00584A-B10'
)";
*/
$strWhereBill    = implode(" AND ", $tmp_bill);
$strWhereTurn    = implode(" AND ", $tmp_turn);
$strWherePayment = implode(" AND ", $tmp_paid);
$strWhereService = implode(" AND ", $tmp_sv);
$strWhereSVPay	 = implode(" AND ", $tmp_svpay);
$amount			 = array();
$amounttotal	 = array(0,0,0,0,0,0,0);
$grandamount	 = 0;


/*
SQL
*/
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
	/*
	0. billing before vat
	1. rate
	2. billing after vat + freight charge
	3. billing realtime
	4. paid billing realtime
	5. paid billing
	6. remain billing
	7. amount billing before vat
	*/

	$sql_bill	= "
		SELECT
			'billing' AS invoice,
			sum(b.bill_amount_qty_unit_price) AS total_before_vat,
			sum(b.bill_total_billing) AS total_billing,
			sum(b.bill_total_billing_net) AS total_billing_realtime,
			sum(b.bill_remain_amount) AS remain_billing,
			(select sum(pay_paid) from ".ZKP_SQL."_tb_customer as c join ".ZKP_SQL."_tb_billing as b ON bill_cus_to = cus_code join ".ZKP_SQL."_tb_payment using(bill_code) where cus_channel = '$value' AND $strWhereBill AND pay_note!='DEPOSIT-B') AS pay_amount
		FROM
			".ZKP_SQL."_tb_customer AS c
			JOIN ".ZKP_SQL."_tb_billing AS b ON b.bill_cus_to = c.cus_code
		WHERE c.cus_channel = '$value' AND $strWhereBill
		GROUP BY invoice
	UNION
		SELECT
			'return' AS invoice,
			sum(-(t.turn_amount_qty_unit_price)) AS total_before_vat,
			sum(-(t.turn_total_return)) AS total_billing,
			NULL AS total_billing_realtime,
			NULL AS remain_billing,
			NULL AS pay_amount
		FROM
			".ZKP_SQL."_tb_customer AS c
			JOIN ".ZKP_SQL."_tb_return AS t ON t.turn_cus_to = c.cus_code
		WHERE c.cus_channel = '$value' AND $strWhereTurn AND t.turn_return_condition IN (2,3,4)
		GROUP BY invoice
	UNION
		SELECT
			'billing' AS invoice,
			sum(sv_total_amount) AS total_before_vat,
			sum(sv_total_amount) AS total_billing,
			sum(sv_total_amount) AS total_billing_realtime,
			sum(sv_total_remain) AS remain_billing,
			(select sum(svpay_paid) from ".ZKP_SQL."_tb_customer as c JOIN ".ZKP_SQL."_tb_service AS b ON sv_cus_to = c.cus_code JOIN ".ZKP_SQL."_tb_service_payment AS p USING(sv_code) WHERE c.cus_channel = '$value' AND $strWhereService) AS pay_amount
		FROM
			".ZKP_SQL."_tb_customer AS c
			JOIN ".ZKP_SQL."_tb_service AS b ON sv_cus_to = c.cus_code
		WHERE c.cus_channel = '$value' AND $strWhereService
		GROUP BY invoice
	";

	$res_bill =& query($sql_bill);
	while($col_bill =& fetchRowAssoc($res_bill)) {
		if(empty($amount[$value][0])) {
			$amount[$value][0]	= $col_bill['total_before_vat'];
			$amount[$value][2]	= $col_bill['total_billing'];
			$amount[$value][3]	= $col_bill['total_billing_realtime'];
			$amount[$value][4]	= $col_bill['pay_amount'];
			$amount[$value][6]	= $col_bill['remain_billing'];
		} else {
			$amount[$value][0]	+= $col_bill['total_before_vat'];
			$amount[$value][2]	+= $col_bill['total_billing'];
			$amount[$value][3]	+= $col_bill['total_billing_realtime'];
			$amount[$value][4]	+= $col_bill['pay_amount'];
			$amount[$value][6]	+= $col_bill['remain_billing'];
		}
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
		if(empty($amount[$value][5]))	$amount[$value][5]	= $col_paid['paid_billing'];
		else							$amount[$value][5]	+= $col_paid['paid_billing'];
	}

	if(empty($amount[$value][0])) {
		$amount[$value][1] = 0;
	} else if($amount[$value][0] > 0) {
		$amount[$value][1] = $amount[$value][0]*100/$grandamount;
		if($amount[$value][1] < 0) $amount[$value][1] = $amount[$value][1]*-1;
	} else if($amount[$value][0] < 0) {
		$amount[$value][1] = $amount[$value][0]*100/$grandamount;
		if($amount[$value][1] > 0) $amount[$value][1] = -($amount[$value][1]);
	}

}

print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th>TEAM</th>
			<th>BILLING<br />(Rp)</th>
			<th width="7%">COMPOSITION<br />RATE</th>
			<th width="12%">FULL BILLING<br />(Rp)</th>
			<th width="12%">BILLING REALTIME<br />(Rp)</th>
			<th width="12%">PAYMENT REALTIME<br />(Rp)</th>
			<th width="12%">PAID<br />BILLING</th>
			<th width="12%">REMAIN<br />BILLING</th>
		</tr>\n
END;

foreach ($channel as $value => $key) {

	//PRINT VALUE
	print "<tr>\n";	
	print "\t<td><a href=\"javascript:seeDetail('$value')\">$key</a></td>\n";
	cell((empty($amount[$value][0])) ? 0 : number_format((double)$amount[$value][0]), ' align="right"');			//billing before vat
	cell((empty($amount[$value][1])) ? 0.00 : number_format((double)$amount[$value][1],2)."%", ' align="right"');	//rate
	cell((empty($amount[$value][2])) ? 0 : number_format((double)$amount[$value][2]), ' align="right"');			//billing after vat
	cell((empty($amount[$value][3])) ? 0 : number_format((double)$amount[$value][3]), ' align="right"');			//billing after vat
	cell((empty($amount[$value][4])) ? 0 : number_format((double)$amount[$value][4]), ' align="right"');			//paid realtime
	cell((empty($amount[$value][5])) ? 0 : number_format((double)$amount[$value][5]), ' align="right"');			//paid billing
	cell((empty($amount[$value][6])) ? 0 : number_format((double)$amount[$value][6]), ' align="right"');			//remain billing
	print "</tr>\n";

	$amounttotal[0] += (empty($amount[$value][0])) ? 0 : $amount[$value][0];	
	$amounttotal[1] += (empty($amount[$value][1])) ? 0 : $amount[$value][1];
	$amounttotal[2] += (empty($amount[$value][2])) ? 0 : $amount[$value][2];
	$amounttotal[3] += (empty($amount[$value][3])) ? 0 : $amount[$value][3];
	$amounttotal[4] += (empty($amount[$value][4])) ? 0 : $amount[$value][4];
	$amounttotal[5] += (empty($amount[$value][5])) ? 0 : $amount[$value][5];
	$amounttotal[6] += (empty($amount[$value][6])) ? 0 : $amount[$value][6];
}

print "\t<tr>\n";
cell('TOTAL BILLING', ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$amounttotal[0]), ' align="right" style="color:brown; background-color:lightyellow"');	//billing before vat
cell(number_format((double)$amounttotal[1],2)."%", ' align="right" style="color:brown; background-color:lightyellow"');	//rate
cell(number_format((double)$amounttotal[2]), ' align="right" style="color:brown; background-color:lightyellow"');	//billing after vat
cell(number_format((double)$amounttotal[3]), ' align="right" style="color:brown; background-color:lightyellow"');	//paid realtime
cell(number_format((double)$amounttotal[4]), ' align="right" style="color:brown; background-color:lightyellow"');	//paid realtime
cell(number_format((double)$amounttotal[5]), ' align="right" style="color:brown; background-color:lightyellow"');	//paid billing
cell(number_format((double)$amounttotal[6]), ' align="right" style="color:brown; background-color:lightyellow"');	//remain billing
print "\t</tr>\n";
print "</table><br />\n";
?>