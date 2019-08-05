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
		$tmp_billing1[] = "SUBSTR(bill_code,1,1) IN ('I','M','B')";
		$tmp_billing2[] = "SUBSTR(bill_code,1,1) IN ('I','M','B')";
	} else if($_order_by == 2) {
		$tmp_billing1[] = "SUBSTR(bill_code,1,1) = 'M'";
		$tmp_billing2[] = "SUBSTR(bill_code,1,1) = 'M'";
		$tmp_service[] = "sv_code is null";
	}
} else {
	if($cboFilter[1][ZKP_URL][0][0] == 1) {
		$tmp_billing1[] = "SUBSTR(bill_code,1,1) IN ('I','M','B') AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill_code,'billing')";
		$tmp_billing2[] = "SUBSTR(bill_code,1,1) IN ('I','M','B') AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill_code,'billing')";
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
	$tmp_billing1[] = "SUBSTR(p.bill_code,2,1) IN ('N','X')";
	$tmp_billing2[] = "SUBSTR(p.bill_code,2,1) IN ('N','X')";
}

$strWhereBilling1	= implode(" AND ", $tmp_billing1);
$strWhereBilling2	= implode(" AND ", $tmp_billing2);
$strWhereService	= implode(" AND ", $tmp_service);

$sql = "
SELECT 
	c.cus_channel AS channel,  
	p.pay_method AS method, 
	p.pay_bank AS bank,
	sum(p.pay_paid) AS amount
FROM ".ZKP_SQL."_tb_customer AS c JOIN ".ZKP_SQL."_tb_payment AS p USING(cus_code)
WHERE $strWhereBilling1
GROUP BY channel, method, bank
	UNION
SELECT 
	c.cus_channel AS channel, 
	p.pay_method AS method, 
	p.pay_bank AS bank,
	sum(p.pay_paid) AS amount
FROM ".ZKP_SQL."_tb_customer AS c JOIN ".ZKP_SQL."_tb_payment AS p USING(cus_code)
WHERE $strWhereBilling2
GROUP BY channel, method, bank
	UNION
SELECT 
	c.cus_channel AS channel, 
	p.svpay_method AS method, 
	p.svpay_bank AS bank, 
	sum(p.svpay_paid) AS amount
FROM ".ZKP_SQL."_tb_customer AS c JOIN ".ZKP_SQL."_tb_service_payment AS p USING(cus_code)
WHERE $strWhereService 
GROUP BY channel, method, bank
ORDER BY channel, method, bank";
$result =& query($sql);
$amount		 = array();
$tot_channel = 0;
while($col =& fetchRowAssoc($result)) {
	if(isset($amount[$col['channel']][trim($col['method'])][trim($col['bank'])])) {
		$amount[$col['channel']][trim($col['method'])][trim($col['bank'])] += $col['amount'];
		$tot_channel += $col['amount'];
	} else {
		$amount[$col['channel']][trim($col['method'])][trim($col['bank'])] = $col['amount'];
		$tot_channel += $col['amount'];
	}
}
/*
echo "<pre>";
var_dump($sql);
echo "</pre>";
*/
switch (ZKP_SQL) {
	// ALL ==============================================================================================================
	case "IDC":

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

	$t_total = 0;
	foreach($amount[$value] as $a=>$b) { foreach($b as $c) $t_total += $c; }

	print "<tr>\n";
	cell($channel[$value]);	// Channel
	cell((isset($amount[$value]['cash']['']))			? number_format((double)$amount[$value]['cash'][''])			: '', ' align="right"');
	cell((isset($amount[$value]['check']['']))			? number_format((double)$amount[$value]['check'][''])			: '', ' align="right"');
	cell((isset($amount[$value]['check']['BII2']))		? number_format((double)$amount[$value]['check']['BII2'])		: '', ' align="right"');
	cell((isset($amount[$value]['check']['DANAMON']))	? number_format((double)$amount[$value]['check']['DANAMON'])	: '', ' align="right"');
	cell((isset($amount[$value]['check']['BNIS']))		? number_format((double)$amount[$value]['check']['BNIS'])		: '', ' align="right"');
	cell((isset($amount[$value]['transfer']['BCA1']))	? number_format((double)$amount[$value]['transfer']['BCA1'])	: '', ' align="right"');
	cell((isset($amount[$value]['transfer']['BCA2']))	? number_format((double)$amount[$value]['transfer']['BCA2'])	: '', ' align="right"');
	cell((isset($amount[$value]['transfer']['MANDIRI']))? number_format((double)$amount[$value]['transfer']['MANDIRI']) : '', ' align="right"');
	cell((isset($amount[$value]['transfer']['BII1']))	? number_format((double)$amount[$value]['transfer']['BII1'])	: '', ' align="right"');
	cell((isset($amount[$value]['transfer']['BII2']))	? number_format((double)$amount[$value]['transfer']['BII2'])	: '', ' align="right"');
	cell((isset($amount[$value]['transfer']['DANAMON']))? number_format((double)$amount[$value]['transfer']['DANAMON'])	: '', ' align="right"');
	cell((isset($amount[$value]['transfer']['BNIS']))	? number_format((double)$amount[$value]['transfer']['BNIS'])	: '', ' align="right"');
	cell((isset($amount[$value]['giro']['']))			? number_format((double)$amount[$value]['giro'][''])			: '', ' align="right"');
	cell((isset($amount[$value]['giro']['BII2']))		? number_format((double)$amount[$value]['giro']['BII2'])		: '', ' align="right"');
	cell((isset($amount[$value]['giro']['DANAMON']))	? number_format((double)$amount[$value]['giro']['DANAMON'])		: '', ' align="right"');
	cell((isset($amount[$value]['giro']['BNIS']))		? number_format((double)$amount[$value]['giro']['BNIS'])		: '', ' align="right"');
	cell(number_format((double)$t_total), ' align="right"');
	print "</tr>\n";

	$total[0]  += (isset($amount[$value]['cash']['']))				? $amount[$value]['cash'][''] 				: 0;
	$total[1]  += (isset($amount[$value]['check']['']))				? $amount[$value]['check']['']				: 0;
	$total[2]  += (isset($amount[$value]['check']['BII2']))			? $amount[$value]['check']['BII2']			: 0;
	$total[3]  += (isset($amount[$value]['check']['DANAMON']))		? $amount[$value]['check']['DANAMON']		: 0;
	$total[4]  += (isset($amount[$value]['check']['BNIS']))			? $amount[$value]['check']['BNIS']			: 0;
	$total[5]  += (isset($amount[$value]['transfer']['BCA1']))		? $amount[$value]['transfer']['BCA1']		: 0;
	$total[6]  += (isset($amount[$value]['transfer']['BCA2']))		? $amount[$value]['transfer']['BCA2']		: 0;
	$total[7]  += (isset($amount[$value]['transfer']['MANDIRI']))	? $amount[$value]['transfer']['MANDIRI'] 	: 0;
	$total[8]  += (isset($amount[$value]['transfer']['BII1']))		? $amount[$value]['transfer']['BII1']		: 0;
	$total[9]  += (isset($amount[$value]['transfer']['BII2']))		? $amount[$value]['transfer']['BII2']		: 0;
	$total[10] += (isset($amount[$value]['transfer']['DANAMON']))	? $amount[$value]['transfer']['DANAMON']	: 0;
	$total[11] += (isset($amount[$value]['transfer']['BNIS']))		? $amount[$value]['transfer']['BNIS']		: 0;
	$total[12] += (isset($amount[$value]['giro']['']))				? $amount[$value]['giro']['']				: 0;
	$total[13] += (isset($amount[$value]['giro']['BII2']))			? $amount[$value]['giro']['BII2']			: 0;
	$total[14] += (isset($amount[$value]['giro']['DANAMON']))		? $amount[$value]['giro']['DANAMON']		: 0;
	$total[15] += (isset($amount[$value]['giro']['BNIS']))			? $amount[$value]['giro']['BNIS']			: 0;
	$total[16] += $t_total;

}

print "<tr>\n";
cell("<b>TOTAL</b>", ' rowspan="2" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$total[0]), ' rowspan="2" align="right" style="color:brown; background-color:lightyellow"');
for($i=1; $i<16; $i++) cell(number_format((double)$total[$i]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$total[16]), ' rowspan="2" align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "<tr>\n";
cell(number_format((double)$total[1]+$total[2]+$total[3]+$total[4]), ' colspan="4" align="center" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$total[5]+$total[6]+$total[7]+$total[8]+$total[9]+$total[10]+$total[11]), ' colspan="7" align="center" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$total[12]+$total[13]+$total[14]+$total[15]), ' colspan="4" align="center" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>";

	break;

	// MED==============================================================================================================
	case "MED":

print <<<END
	<span class="comment" align=>(In Rupiah)</span>
	<table class="table_f">
		<tr>
			<th align="center" width="115px" rowspan="2">TEAM</th>
			<th align="center" width="70px" rowspan="2">CASH</th>
			<th align="center" width="140px" colspan="2">CHECK</th>

			<th align="center" width="140px" colspan="2">TRANSFER</th>
			<th align="center" width="140pxx" colspan="2">GIRO</th>
			<th align="center" width="80px" rowspan="2">TOTAL</th>
		</tr>
		<tr>
			<th align="center" width="70px">BII</th>
			<th align="center" width="70px">DANAMON</th>
			<th align="center" width="70px">BII</th>
			<th align="center" width="70px">DANAMON</th>
			<th align="center" width="70px">BII</th>
			<th align="center" width="70px">DANAMON</th>
		</tr>\n

END;

for($i=0; $i<8; $i++) $total[$i] = 0;
foreach ($channel as $value => $key) {

	$t_total = 0;
	foreach($amount[$value] as $a=>$b) { foreach($b as $c) $t_total += $c; }

	print "<tr>\n";
	cell($channel[$value]);	// Channel
	cell((isset($amount[$value]['cash']['']))				? number_format((double)$amount[$value]['cash'][''])				: '', ' align="right"');
	cell((isset($amount[$value]['check']['BII3']))			? number_format((double)$amount[$value]['check']['BII3'])			: '', ' align="right"');
	cell((isset($amount[$value]['check']['DANAMON2']))		? number_format((double)$amount[$value]['check']['DANAMON2'])		: '', ' align="right"');
	cell((isset($amount[$value]['transfer']['BII3']))		? number_format((double)$amount[$value]['transfer']['BII3'])		: '', ' align="right"');
	cell((isset($amount[$value]['transfer']['DANAMON2']))	? number_format((double)$amount[$value]['transfer']['DANAMON2'])	: '', ' align="right"');
	cell((isset($amount[$value]['giro']['BII3']))			? number_format((double)$amount[$value]['giro']['BII3'])			: '', ' align="right"');
	cell((isset($amount[$value]['giro']['DANAMON2']))		? number_format((double)$amount[$value]['giro']['DANAMON2'])		: '', ' align="right"');
	cell(number_format((double)$t_total), ' align="right"');
	print "</tr>\n";

	$total[0]  += (isset($amount[$value]['cash']['']))				? $amount[$value]['cash'][''] 				: 0;
	$total[1]  += (isset($amount[$value]['check']['BII3']))			? $amount[$value]['check']['BII3']			: 0;
	$total[2]  += (isset($amount[$value]['check']['DANAMON2']))		? $amount[$value]['check']['DANAMON2']		: 0;
	$total[3]  += (isset($amount[$value]['transfer']['BII3']))		? $amount[$value]['transfer']['BII3']		: 0;
	$total[4]  += (isset($amount[$value]['transfer']['DANAMON2']))	? $amount[$value]['transfer']['DANAMON2']	: 0;
	$total[5]  += (isset($amount[$value]['giro']['BII3']))			? $amount[$value]['giro']['BII3']			: 0;
	$total[6]  += (isset($amount[$value]['giro']['DANAMON2']))		? $amount[$value]['giro']['DANAMON2']		: 0;
	$total[7] += $t_total;

}

print "<tr>\n";
cell("<b>TOTAL</b>", ' rowspan="2" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$total[0]), ' rowspan="2" align="right" style="color:brown; background-color:lightyellow"');
for($i=1; $i<7; $i++) cell(number_format((double)$total[$i]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$total[7]), ' rowspan="2" align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "<tr>\n";
cell(number_format((double)$total[1]+$total[2]), ' colspan="2" align="center" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$total[3]+$total[4]), ' colspan="2" align="center" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$total[5]+$total[6]), ' colspan="2" align="center" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>";

	break;
}
?>