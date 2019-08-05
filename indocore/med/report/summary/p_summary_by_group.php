<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 29-May, 2007 23:50:32
* @author    : daesung kim
*/
//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, HTTP_DIR . "<script language=\"javascript1.2\">window.close();</script>");

//PARAMETER
if(!isset($_GET['_channel']) || $_GET['_channel'] == "")
	die("<script language=\"javascript1.2\">window.close();</script>");

$s_mode			= isset($_GET['s_mode']) ? $_GET['s_mode'] : "period";
$_channel	= urldecode($_GET['_channel']);
$_order_by		= isset($_GET['cboOrderBy']) ? $_GET['cboOrderBy'] : "all";
$_marketing		= isset($_GET['cboFilterMarketing']) ? $_GET['cboFilterMarketing'] : "all";
$_filter_doc	= isset($_GET['cboFilterDoc']) ? $_GET['cboFilterDoc'] : "all";
$_vat			= isset($_GET['cboFilterVat']) ? $_GET['cboFilterVat'] : "all";
$_dept			= isset($_GET['cboFilterDept']) ? $_GET['cboFilterDept'] : "all";

if($s_mode == 'period') {
	$some_date 		= "";
	$period_from 	= isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", time()-2592000);
	$period_to 		= isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", time());
} elseif ($s_mode == 'date') {

	if(isset($_GET['some_date'])) {
		$some_date = $_GET['some_date'];
	} else {
		$some_date = date('j-M-Y');
		$_GET['cboDate'] = "0";
	}

	$period_from 		= "";
	$period_to 			= "";
}

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

$order['0']	= '==ALL==';
$order['1']	= 'INDOCORE';
$order['3']	= 'SERVICE';

$filter['all']	= '==ALL==';
$filter['I']	= 'INVOICE';
$filter['R']	= 'RETURN';

$dept['all']		= '==ALL==';
$dept['A']		= 'APOTIK';
$dept['D']		= 'DEALER';
$dept['H']		= 'HOSPITAL';
$dept['P']		= 'PHARMACEUTICAL';
$dept['S']		= 'CUSTOMER SERVICE';

$vat['all']		= '==ALL==';
$vat['vat']		= 'VAT';
$vat['vat-IO']	= 'VAT - IO';
$vat['vat-IP']	= 'VAT - IP';
$vat['non']		= 'NON VAT';
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
</head>
<body style="margin:8px">
<table class="table_box" width="100%">
	<tr>
		<td width="50%" rowspan="5" valign="top"><h4>BILLING SUMMARY by group<br />channel : <?php echo $channel[$_channel] ?></h4></td>
		<th>ORDER BY</th>
		<td><?php echo $order[$_order_by] ?></td>
	</tr>
	<tr>
		<th>FILTER BY</th>
		<td><?php echo $filter[$_filter_doc] ?></td>
	</tr>
	<tr>
		<th>DEPT</th>
		<td><?php echo $dept[$_dept] ?></td>
	</tr>
	<tr>
		<th>VAT</th>
		<td><?php echo $vat[$_vat] ?></td>
	</tr>
	<tr>
		<th>PERIOD</th>
		<td><?php echo  ($some_date != '') ? $some_date : "FROM\t: $period_from<br />TO\t: $period_to" ?></td>
	</tr>
</table><br />
<?php
$tmp_bill	= array();
$tmp_turn	= array();
$tmp_sv		= array();

if ($_filter_doc == "I") {
	$tmp_turn[]	= "t.turn_code = NULL";
	$tmp_sv[]	= "sv_code is null";
} else if ($_filter_doc == "R") {
	$tmp_bill[]	= "b.bill_code = NULL";
	$tmp_sv[]	= "sv_code is null";
}

if($_order_by == 1) {
	$tmp_bill[] = "bill_ordered_by = 1";
	$tmp_turn[] = "turn_ordered_by = 1";
	$tmp_sv[]	= "sv_code is null"; 
} else if($_order_by == 3) {
	$tmp_bill[] = "bill_code is null";
	$tmp_turn[] = "turn_code is null";
}

if($_marketing != "all") {
	$tmp_bill[]	= "cus_responsibility_to = $_marketing";
	$tmp_turn[] = "cus_responsibility_to = $_marketing";
	$tmp_sv[]	= "sv_code is null";
}

if($_dept != 'all') {
	$tmp_bill[] = "b.bill_dept = '$_dept'";
	$tmp_turn[] = "t.turn_dept = '$_dept'";
	if($_dept != 'S') {
		$tmp_sv[]	= "sv_code is null";	
	}
}

if ($some_date != "") {
	$tmp_bill[] = "b.bill_inv_date = DATE '$some_date'";
	$tmp_turn[] = "t.turn_return_date = DATE '$some_date'";
	$tmp_sv[]	= "sv_date = DATE '$some_date'";
} else {
	$tmp_bill[]	= "b.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_turn[]	= "t.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_sv[]	= "sv_date BETWEEN DATE '$period_from' AND '$period_to'";	
}

if($_vat == 'vat') {
	$tmp_bill[]	= "b.bill_vat > 0";
	$tmp_turn[]	= "t.turn_vat > 0";
	$tmp_sv[]	= "sv_code is null";
} else if($_vat == 'vat-IO') {
	$tmp_bill[]	= "b.bill_vat > 0 AND substr(b.bill_code,2,1) = 'O'";
	$tmp_turn[]	= "t.turn_code = NULL";
	$tmp_sv[]	= "sv_code is null";
}else if($_vat == 'vat-IP') {
	$tmp_bill[]	= "b.bill_vat > 0 AND substr(b.bill_code,2,1) = 'P'";
	$tmp_turn[]	= "t.turn_code = NULL";
	$tmp_sv[]	= "sv_code is null";
} else if ($_vat == 'non') {
	$tmp_bill[]	= "b.bill_vat = 0";
	$tmp_turn[]	= "t.turn_vat = 0";
	$tmp_sv[]	= "sv_code is null";
}

$tmp_bill[] = "cus.cus_channel = '$_channel'";
$tmp_turn[] = "cus.cus_channel = '$_channel'";
$tmp_sv[]	= "cus.cus_channel = '$_channel'";

$strWhereBill    = implode(" AND ", $tmp_bill);
$strWhereTurn    = implode(" AND ", $tmp_turn);
$strWhereService = implode(" AND ", $tmp_sv);

$sql_bill = "
  SELECT
	COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = bill_cus_to),
	'Others') AS cug_name,
  	ROUND(SUM(biit.biit_qty*(biit_unit_price*(100-bill_discount)/100)),2) AS amount
FROM
	tb_customer AS cus
	JOIN ".ZKP_SQL."_tb_billing AS b ON b.bill_cus_to = cus.cus_code
	JOIN ".ZKP_SQL."_tb_billing_item AS biit USING(bill_code)
WHERE " . $strWhereBill ."
GROUP BY cug_name";

$sql_turn = "
  SELECT
	COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = turn_cus_to),
	'Others') AS cug_name,
  	ROUND(-(SUM(reit.reit_qty*(reit_unit_price*(100-turn_discount)/100))),2) AS amount
FROM
	tb_customer AS cus
	JOIN ".ZKP_SQL."_tb_return AS t ON t.turn_cus_to = cus.cus_code
	JOIN ".ZKP_SQL."_tb_return_item AS reit USING(turn_code)
WHERE t.turn_return_condition IN (2,3,4) AND " . $strWhereTurn ."
GROUP BY cug_name";

$sql_sv = "
  SELECT
	COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = sv_cus_to),
	'Others') AS cug_name,
  	SUM(sv_total_amount) AS amount
FROM
	tb_customer AS cus
	JOIN ".ZKP_SQL."_tb_service AS sv ON sv.sv_cus_to = cus.cus_code
WHERE " . $strWhereService ."
GROUP BY cug_name";

$sql = "$sql_bill UNION $sql_turn UNION $sql_sv ORDER BY cug_name";
$total = 0;
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {
	if(!isset($amount[$col['cug_name']])) {
		$amount[$col['cug_name']] = $col['amount'];
	} else {
		$amount[$col['cug_name']] += $col['amount'];
	}
	$total += $col['amount'];
}

print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th>CUSTOMER GROUP</th>
			<th width="25%">AMOUNT<br />(Rp)</th>
		</tr>\n
END;

pg_result_seek($res, 0);
while($col =& fetchRowAssoc($res)) {
	if(!isset($a[$col['cug_name']])) {
		print "<tr>\n";
		cell($col['cug_name']);
		cell(number_format((double)$amount[$col['cug_name']]), ' align="right"');
		print "</tr>\n";

		$a[$col['cug_name']] = $col['cug_name'];
	}
}

print "<tr>\n";
cell('TOTAL', ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$total), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>";
?>
</body>
</html>