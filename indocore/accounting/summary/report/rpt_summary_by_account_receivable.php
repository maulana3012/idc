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

$tmp_billing1 = array();
$tmp_billing2 = array();
$tmp_service = array();

if(ZKP_FUNCTION == 'ALL') {
	if($_order_by == 1) {
		$tmp_billing1[] = "SUBSTR(bill_code,1,1) = 'I'";
		$tmp_billing2[] = "SUBSTR(bill_code,1,1) = 'I'";
	} else if($_order_by == 2) {
		$tmp_billing1[] = "SUBSTR(bill_code,1,1) = 'M'";
		$tmp_billing2[] = "SUBSTR(bill_code,1,1) = 'M'";
		$tmp_service[] = "sv_code is null";
	}
} else {
	if($cboFilter[1][ZKP_URL][0][0] == 1) {
		$tmp_billing1[] = "SUBSTR(bill_code,1,1) = 'I' AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill_code,'billing')";
		$tmp_billing2[] = "SUBSTR(bill_code,1,1) = 'I' AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill_code,'billing')";
	} else if($cboFilter[1][ZKP_URL][0][0] == 2) {
		$tmp_billing1[] = "SUBSTR(bill_code,1,1) = 'M' AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill_code,'billing')";
		$tmp_billing2[] = "SUBSTR(bill_code,1,1) = 'M' AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill_code,'billing')";
		$tmp_service[] = "sv_code is null";
	}
}

if ($_filter_doc == "I") {
	$tmp_billing1[] = "p.pay_paid > 0 AND pay_note IN ('USUAL', 'DEPOSIT-A', 'RETURN')";
	$tmp_billing2[] = "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
} else if ($_filter_doc == "R") {
	$tmp_billing1[] = "p.pay_paid <= 0 AND pay_note IN ('USUAL', 'DEPOSIT-A', 'RETURN')";
	$tmp_billing2[] = "p.pay_idx IS NULL";
	$tmp_service[] = "svpay_idx is null";
} else if ($_filter_doc == "CT") {
	$tmp_billing1[] = "p.pay_idx IS NULL";
	$tmp_billing2[] = "pay_note IN ('CROSS_TRANSFER+')";
	$tmp_service[] = "svpay_idx is null";
} else {
	$tmp_billing1[]	= "pay_note IN ('USUAL', 'DEPOSIT-A', 'RETURN')";
	$tmp_billing2[]	= "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
}

if($_marketing != "all") {
	$tmp_billing1[] = "cus_responsibility_to = $_marketing";
	$tmp_billing2[] = "cus_responsibility_to = $_marketing";
	$tmp_service[] = "sv_code is null";
}

if ($some_date != "") {
	$tmp_billing1[] = "p.pay_date = DATE '$some_date'";
	$tmp_billing2[] = "p.pay_date = DATE '$some_date'";
	$tmp_service[] = "svpay_date = DATE '$some_date'";
} else {
	$tmp_billing1[] = "p.pay_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_billing2[] = "p.pay_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_service[] = "svpay_date BETWEEN DATE '$period_from' AND '$period_to'";
}

if($_dept != 'all' && $_dept != 'S') {
	$tmp_billing1[] = "p.pay_dept = '$_dept'";
	$tmp_billing2[] = "p.pay_dept = '$_dept'";
	$tmp_service[] = "sv_code is null";
} else if($_dept == 'S') {
	$tmp_billing1[] = "bill_code is null";
	$tmp_billing2[] = "bill_code is null";
}

if($_vat == 'vat') { 
	$tmp_billing1[] = "SUBSTR(p.bill_code,2,1) IN ('O','P')";
	$tmp_billing2[] = "SUBSTR(p.bill_code,2,1) IN ('O','P')";
	$tmp_service[] = "sv_code is null";
} else if($_vat == 'vat-IO') {
	$tmp_billing1[] = "SUBSTR(p.bill_code,2,1) = 'O'";
	$tmp_billing2[] = "SUBSTR(p.bill_code,2,1) = 'O'";
	$tmp_service[] = "sv_code is null";
} else if($_vat == 'vat-IP') {
	$tmp_billing1[] = "SUBSTR(p.bill_code,2,1) = 'P'";
	$tmp_billing2[] = "SUBSTR(p.bill_code,2,1) = 'P'";
	$tmp_service[] = "sv_code is null";
} else if ($_vat == 'non') {
	$tmp_billing1[] = "SUBSTR(p.bill_code,2,1) = 'X'";
	$tmp_billing2[] = "SUBSTR(p.bill_code,2,1) = 'X'";
}

$strWhereBilling1	= implode(" AND ", $tmp_billing1);
$strWhereBilling2	= implode(" AND ", $tmp_billing2);
$strWhereService	= implode(" AND ", $tmp_service);

print <<<END
	<span class="comment" align=>(In Rupiah)</span>
	<table width="1200px" class="table_f">
		<tr>
			<th width="115px" rowspan="2">TEAM</th>
			<th width="70px" rowspan="2">CASH</th>
			<th width="280px" colspan="4">CHECK</th>
			<th width="490px" colspan="7">TRANSFER</th>
			<th width="280px" colspan="4">GIRO</th>
			<th width="80px" rowspan="2">TOTAL</th>
		</tr>
		<tr>
			<th width="70px"><i>OLD</i></th>
			<th width="70px">BII2</th>
			<th width="70px">DANAMON</th>
			<th width="70px">BNI SYR</th>
			<th width="70px">BCA1</th>
			<th width="70px">BCA2</th>
			<th width="70px">MANDIRI</th>
			<th width="70px">BII1</th>
			<th width="70px">BII2</th>
			<th width="70px">DANAMON</th>
			<th width="70px">BNI SYR</th>
			<th width="70px"><i>OLD</i></th>
			<th width="70px">BII2</th>
			<th width="70px">DANAMON</th>
			<th width="70px">BNI SYR</th>
		</tr>\n

END;

for($i=0; $i<17; $i++) $total[$i] = 0;
foreach ($channel as $value => $key) {
	$sql = "
		SELECT 
			p.pay_method AS method, 
			p.pay_bank AS bank,
			sum(p.pay_paid) AS amount
		FROM ".ZKP_SQL."_tb_customer AS c JOIN ".ZKP_SQL."_tb_payment AS p USING(cus_code)
		WHERE c.cus_channel = '$value' AND $strWhereBilling1
		GROUP BY p.pay_method, p.pay_bank
			UNION
		SELECT 
			p.pay_method AS method, 
			p.pay_bank AS bank,
			sum(p.pay_paid) AS amount
		FROM ".ZKP_SQL."_tb_customer AS c JOIN ".ZKP_SQL."_tb_payment AS p USING(cus_code)
		WHERE c.cus_channel = '$value' AND $strWhereBilling2
		GROUP BY p.pay_method, p.pay_bank
			UNION
		SELECT 
			p.svpay_method AS method, 
			p.svpay_bank AS bank, 
			sum(p.svpay_paid) AS amount
		FROM ".ZKP_SQL."_tb_customer AS c JOIN ".ZKP_SQL."_tb_service_payment AS p USING(cus_code)
		WHERE c.cus_channel = '$value' AND $strWhereService 
		GROUP BY p.svpay_method, p.svpay_bank
		ORDER BY method";
	$result =& query($sql);

	$amount		 = array();
	$tot_channel = 0;
	while($col =& fetchRowAssoc($result)) {
		if(isset($amount[trim($col['method'])][trim($col['bank'])])) {
			$amount[trim($col['method'])][trim($col['bank'])] += $col['amount'];
			$tot_channel += $col['amount'];
		} else {
			$amount[trim($col['method'])][trim($col['bank'])] = $col['amount'];
			$tot_channel += $col['amount'];
		}
	}

	print "<tr>\n";
	cell($channel[$value]);	// Channel
	cell((isset($amount['cash']['']))			? number_format($amount['cash'][''])			: '', ' align="right"');
	cell((isset($amount['check']['']))			? number_format($amount['check'][''])			: '', ' align="right"');
	cell((isset($amount['check']['BII2']))		? number_format($amount['check']['BII2'])		: '', ' align="right"');
	cell((isset($amount['check']['DANAMON']))	? number_format($amount['check']['DANAMON'])	: '', ' align="right"');
	cell((isset($amount['check']['BNIS']))		? number_format($amount['check']['BNIS'])		: '', ' align="right"');
	cell((isset($amount['transfer']['BCA1']))	? number_format($amount['transfer']['BCA1'])	: '', ' align="right"');
	cell((isset($amount['transfer']['BCA2']))	? number_format($amount['transfer']['BCA2'])	: '', ' align="right"');
	cell((isset($amount['transfer']['MANDIRI']))? number_format($amount['transfer']['MANDIRI']) : '', ' align="right"');
	cell((isset($amount['transfer']['BII1']))	? number_format($amount['transfer']['BII1'])	: '', ' align="right"');
	cell((isset($amount['transfer']['BII2']))	? number_format($amount['transfer']['BII2'])	: '', ' align="right"');
	cell((isset($amount['transfer']['DANAMON']))? number_format($amount['transfer']['DANAMON'])	: '', ' align="right"');
	cell((isset($amount['transfer']['BNIS']))	? number_format($amount['transfer']['BNIS'])	: '', ' align="right"');
	cell((isset($amount['giro']['']))			? number_format($amount['giro'][''])			: '', ' align="right"');
	cell((isset($amount['giro']['BII2']))		? number_format($amount['giro']['BII2'])		: '', ' align="right"');
	cell((isset($amount['giro']['DANAMON']))	? number_format($amount['giro']['DANAMON'])		: '', ' align="right"');
	cell((isset($amount['giro']['BNIS']))		? number_format($amount['giro']['BNIS'])		: '', ' align="right"');
	cell(number_format($tot_channel), ' align="right"');
	print "</tr>\n";

	$total[0]  += (isset($amount['cash']['']))				? $amount['cash'][''] 				: 0;
	$total[1]  += (isset($amount['check']['']))				? $amount['check']['']				: 0;
	$total[2]  += (isset($amount['check']['BII2']))			? $amount['check']['BII2']			: 0;
	$total[3]  += (isset($amount['check']['DANAMON']))		? $amount['check']['DANAMON']		: 0;
	$total[4]  += (isset($amount['check']['BNIS']))			? $amount['check']['BNIS']			: 0;
	$total[5]  += (isset($amount['transfer']['BCA1']))		? $amount['transfer']['BCA1']		: 0;
	$total[6]  += (isset($amount['transfer']['BCA2']))		? $amount['transfer']['BCA2']		: 0;
	$total[7]  += (isset($amount['transfer']['MANDIRI']))	? $amount['transfer']['MANDIRI'] 	: 0;
	$total[8]  += (isset($amount['transfer']['BII1']))		? $amount['transfer']['BII1']		: 0;
	$total[9]  += (isset($amount['transfer']['BII2']))		? $amount['transfer']['BII2']		: 0;
	$total[10] += (isset($amount['transfer']['DANAMON']))	? $amount['transfer']['DANAMON']	: 0;
	$total[11] += (isset($amount['transfer']['BNIS']))		? $amount['transfer']['BNIS']		: 0;
	$total[12] += (isset($amount['giro']['']))				? $amount['giro']['']				: 0;
	$total[13] += (isset($amount['giro']['BII2']))			? $amount['giro']['BII2']			: 0;
	$total[14] += (isset($amount['giro']['DANAMON']))		? $amount['giro']['DANAMON']		: 0;
	$total[15] += (isset($amount['giro']['BNIS']))			? $amount['giro']['BNIS']			: 0;
	$total[16] += $tot_channel;

}

print "<tr>\n";
cell("<b>TOTAL</b>", ' rowspan="2" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($total[0]), ' rowspan="2" align="right" style="color:brown; background-color:lightyellow"');
for($i=1; $i<16; $i++) cell(number_format($total[$i]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($total[16]), ' rowspan="2" align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "<tr>\n";
cell(number_format($total[1]+$total[2]+$total[3]+$total[4]), ' colspan="4" align="center" style="color:brown; background-color:lightyellow"');
cell(number_format($total[5]+$total[6]+$total[7]+$total[8]+$total[9]+$total[10]+$total[11]), ' colspan="7" align="center" style="color:brown; background-color:lightyellow"');
cell(number_format($total[12]+$total[13]+$total[14]+$total[15]), ' colspan="4" align="center" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>";
?>