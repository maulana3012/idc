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

//Global
$s_mode			= isset($_GET['s_mode']) ? $_GET['s_mode'] : "period";
$_order_by		= isset($_GET['cboFilterOrderBy']) ? $_GET['cboFilterOrderBy'] : $cboFilter[1][ZKP_FUNCTION][0][0];
$_marketing		= isset($_GET['cboFilterMarketing']) ? $_GET['cboFilterMarketing'] : "all";
$_filter_doc	= isset($_GET['cboFilterDoc']) ? $_GET['cboFilterDoc'] : "all";
$_vat			= isset($_GET['cboFilterVat']) ? $_GET['cboFilterVat'] : $cboFilter[2][ZKP_FUNCTION][0][0];
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

$variable = array(
	'order'		=> array('all'=>'==ALL==', '1'=>'INDOCORE', '2'=>'MEDIKUS EKA'),
	'doc'		=> array('all'=>'==ALL==', 'I'=>'INVOICE', 'R'=>'RETURN', 'DR'=>'DR'),
	'dept'		=> array('all'=>'==ALL==', 'A'=>'APOTIK', 'D'=>'DEALER', 'H'=>'HOSPITAL', 'P'=>'PHARMACEUTICAL', 'S'=>'Customer Service'),
	'vat'		=> array('all'=>'==ALL==', 'vat'=>'VAT', 'vat-IO'=>'VAT - IO', 'vat-IP'=>'VAT - IP', 'non'=>'NON VAT'),
);

$mkt_sql = "SELECT ma_idx, ma_account FROM ".ZKP_SQL."_tb_mbracc WHERE ma_display_as in (1)";
$mkt_res = & query($mkt_sql);
$variable['marketing']["all"] = "==ALL==";
while ($col = fetchRow($mkt_res)) {
	$variable['marketing'][$col[0]] = strtoupper($col[1]);
};
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
		<td width="50%" rowspan="4" colspan="2" valign="top"><span style="font-size:17px;font-weight:bold;color:#000000">BILLING SUMMARY<br />by supplier</span></td>
		<th>ORDER</th>
		<td><?php echo $variable['order'][$_order_by] ?></td>
	</tr>
	<tr>
		<th>MARKETING</th>
		<td><?php echo $variable['marketing'][$_marketing] ?></td>
	</tr>
	<tr>
		<th>FILTER BY</th>
		<td><?php echo $variable['doc'][$_filter_doc] ?></td>
	</tr>
	<tr>
		<th>DEPT</th>
		<td><?php echo $variable['dept'][$_dept] ?></td>
	</tr>
	<tr>
		<th width="20%">PERIOD</th>
		<td><?php echo  ($some_date != '') ? $some_date : "FROM\t: $period_from<br />TO\t: $period_to" ?></td>
		<th>VAT</th>
		<td><?php echo $variable['vat'][$_vat] ?></td>
	</tr>
</table><br />
<?php
$tmp_bill	= array();
$tmp_turn	= array();
$tmp_dr		= array();

if(ZKP_FUNCTION == 'ALL') {
	if($_order_by != 'all'){
		$tmp_bill[]	= "bill_ordered_by = $_order_by";
		$tmp_turn[] = "turn_ordered_by = $_order_by";
		$tmp_dr[]	= "dr_ordered_by = $_order_by";
	}
} else {
	$tmp_bill[]	= "bill_ordered_by = ".$cboFilter[1][ZKP_FUNCTION][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_FUNCTION."', bill_code,'billing')";
	$tmp_turn[] = "turn_ordered_by = ".$cboFilter[1][ZKP_FUNCTION][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_FUNCTION."', turn_code,'billing_return')";
	$tmp_dr[]	= "dr_ordered_by = ".$cboFilter[1][ZKP_FUNCTION][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_FUNCTION."', dr_code,'dr')";
}

if ($_filter_doc == "I") {
	$tmp_turn[]		= "turn.turn_code = NULL";
	$tmp_dr[]		= "dr.dr_code = NULL";
} else if ($_filter_doc == "R") {
	$tmp_bill[]		= "bill.bill_code = NULL";
	$tmp_dr[]		= "dr.dr_code = NULL";
} else if ($_filter_doc == "DR") {
	$tmp_bill[]		= "bill.bill_code = NULL";
	$tmp_turn[]		= "turn.turn_code = NULL";
}

if($_dept != 'all') {
	$tmp_bill[] = "bill_dept = '$_dept'";
	$tmp_turn[] = "turn_dept = '$_dept'";
	$tmp_dr[]	= "dr_dept = '$_dept'";
}

if($_marketing != "all") {
	$tmp_bill[]	= "cus_responsibility_to = $_marketing";
	$tmp_turn[] = "cus_responsibility_to = $_marketing";
	$tmp_dr[]	= "cus_responsibility_to = $_marketing";
}

if($_vat == 'vat') {
	$tmp_bill[] 	= "bill_vat > 0";
	$tmp_turn[] 	= "turn_vat > 0";
	$tmp_dr[]		= "dr_code is null";
} else if($_vat == 'vat-IO') {
	$tmp_bill[] 	= "bill_type_pajak = 'IO'";
	$tmp_turn[] 	= "turn_vat > 0";
	$tmp_dr[]		= "dr_code is null";
} else if($_vat == 'vat-IP') {
	$tmp_bill[] 	= "bill_type_pajak = 'IP'";
	$tmp_turn[] 	= "turn_code = NULL";
	$tmp_dr[]		= "dr_code is null";
} else if ($_vat == 'non') {
	$tmp_bill[] 	= "bill_vat = 0";
	$tmp_turn[] 	= "turn_vat = 0";
	$tmp_dr[]		= "dr_code is null";
}

if ($some_date != "") {
	$tmp_bill[] = "bill_inv_date  = DATE '$some_date'";
	$tmp_turn[] = "turn_return_date  = DATE '$some_date'";
	$tmp_dr[]	= "dr_issued_date  = DATE '$some_date'";
} else {
	$tmp_bill[] = "bill_inv_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
	$tmp_turn[] = "turn_return_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
	$tmp_dr[]	= "dr_issued_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
}

$strWhereBill   = implode(" AND ", $tmp_bill);
$strWhereTurn   = implode(" AND ", $tmp_turn);
$strWhereDR		= implode(" AND ", $tmp_dr);

$icat_name	= array();
$icat_child	= array();

$sp_res =& query("SELECT icat_midx,icat_name FROM ".ZKP_SQL."_tb_item_cat WHERE icat_depth=1");
while($col =& fetchRowAssoc($sp_res)) {
	$icat_name[]	= $col["icat_name"];
	$icat_child[]	= executeSP(ZKP_SQL."_getSubCategory", $col["icat_midx"]);
}

$numSupplier = count($icat_child);

$bill_qty	= '';
$turn_qty	= '';
$dr_qty		= '';
$bill_amount = '';
$turn_amount = '';
for($i = 0 ; $i < count($icat_child); $i++) {
	$bill_qty = $bill_qty . "(SELECT sum(biit_qty) FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_billing ON bill_ship_to=cus_code JOIN ".ZKP_SQL."_tb_billing_item USING(bill_code) WHERE icat_midx IN({$icat_child[$i][0]}) AND $strWhereBill) AS bill_qty$i,";
	$turn_qty = $turn_qty . "(SELECT -sum(reit_qty) FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_return ON turn_ship_to=cus_code JOIN ".ZKP_SQL."_tb_return_item USING(turn_code) WHERE icat_midx IN({$icat_child[$i][0]}) AND $strWhereTurn) AS turn_qty$i,";
	$dr_qty	  = $dr_qty . "(SELECT sum(drit_qty) FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_dr ON dr_ship_to=cus_code JOIN ".ZKP_SQL."_tb_dr_item USING(dr_code) JOIN ".ZKP_SQL."_tb_item USING(it_code) JOIN ".ZKP_SQL."_tb_item_cat USING(icat_midx) WHERE icat_midx IN({$icat_child[$i][0]}) AND $strWhereDR) AS dr_qty$i,";
	$bill_amount = $bill_amount . "(SELECT sum(biit_qty * (biit_unit_price*(100-bill_discount)/100)) FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_billing ON bill_ship_to=cus_code JOIN ".ZKP_SQL."_tb_billing_item USING(bill_code) WHERE icat_midx IN({$icat_child[$i][0]}) AND $strWhereBill) AS bill_amount$i,";
	$turn_amount = $turn_amount . "(SELECT sum(-(reit_qty * (reit_unit_price*(100-turn_discount)/100))) FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_return ON turn_ship_to=cus_code JOIN ".ZKP_SQL."_tb_return_item USING(turn_code) WHERE icat_midx IN({$icat_child[$i][0]}) AND $strWhereTurn AND turn_return_condition IN (2,3,4)) AS bill_amount$i,";
}

$sql = "SELECT $bill_qty $turn_qty $dr_qty $bill_amount ". substr($turn_amount,$turn_amount,-1);
$result =& query($sql);
$column =& fetchRow($result);

$total_amount = 0;
for($i = $numSupplier*3-2;$i < $numSupplier*5; $i++) {
	$total_amount += $column[$i];
}

print <<<END
<table width="100%" class="table_c">
	<tr>
		<th>SUPPLIER</th>
		<th width="18%">QTY<br>(EA)</th>
		<th width="25%">AMOUNT<br>(Rp)</th>
		<th width="15%">RATE</th> 
	</tr>\n
END;
$i = 0;
$total = array(0,0,0);
foreach($icat_name as $key => $value) {
	$qty	= $column[$i] + $column[$i+$numSupplier] + $column[$i+($numSupplier*2)] ;
	$amount	= $column[$i+($numSupplier*3)] + $column[$i+($numSupplier*4)];
	if($amount==0 && $total_amount==0) {
		$rate = 0;
	} else {
		$rate = ($amount*100)/$total_amount;
	}

	if($qty!=0 || $amount!=0 || $rate!=0) {
		print "<tr>\n";
		cell($value);
		cell(number_format((double)$qty), ' align="right"');
		cell(number_format((double)$amount), ' align="right"');
		cell(number_format((double)$rate,2)."%", ' align="right"');
		print "</tr>\n";
	}

	$total[0] += $qty;
	$total[1] += $amount;
	$total[2] += $rate;
	$rate	 = 0;
	$i++;
}
print "<tr>\n";
cell('TOTAL', ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$total[2],2)."%", ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>";
?>
</body>
</html>