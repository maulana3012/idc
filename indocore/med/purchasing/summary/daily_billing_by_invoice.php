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
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//Global
$left_loc	= "daily_billing_by_invoice.php";
$s_mode		= isset($_GET['s_mode']) ? $_GET['s_mode'] : "period";
$_marketing	= isset($_GET['cboFilterMarketing']) ? $_GET['cboFilterMarketing'] : "all";
$_order_by	= isset($_GET['cboOrderBy']) ? $_GET['cboOrderBy'] : "1";
$_dept		= isset($_GET['cboFilterDept']) ? $_GET['cboFilterDept'] : "all";
$_vat		= isset($_GET['cboFilterVat']) ? $_GET['cboFilterVat'] : "vat-IO";

if($s_mode == 'period') {
	$some_date 		= "";
	$period_from 	= isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", mktime(0,0,0,date("m"),1,date("Y")));
	$period_to 		= isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", mktime(0,0,0,date("m")+1,0,date("Y")));
} elseif ($s_mode == 'date') {
	$some_date 		= $_GET['some_date'];
	$period_from 	= "";
	$period_to 		= "";
}

//SET WHERE PARAMETER
if($_order_by == 1) $tmp[] = "bill_ordered_by = 1";
else if($_order_by == 2) $tmp[] = "bill_ordered_by = 2";

if($_marketing != "all") $tmp[] = "cus_responsibility_to = $_marketing";

if($_vat == 'vat') $tmp[] = "bill_vat > 0";
else if ($_vat == 'vat-IO') $tmp[] = "bill_vat > 0 AND bill_type_pajak = 'IO'";
else if ($_vat == 'vat-IP') $tmp[] = "bill_vat > 0 AND bill_type_pajak = 'IP'";
else if ($_vat == 'non') $tmp[] = "bill_vat = 0";

if ($some_date != "") $tmp[] = "bill_inv_date = DATE '$some_date'";
else $tmp[] = "bill_inv_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";

if($_dept != 'all') $tmp[] = "bill_dept = '$_dept'";

$strWhere = implode(" AND ", $tmp);

//DEFAULT LIST
$sql ="
SELECT
  cus_full_name,
  bill_code,
  to_char(bill_inv_date,'dd/Mon/YY') AS inv_date,
  bill_npwp,
  bill_vat_inv_no,
  (bill_total_billing - bill_delivery_freight_charge) * 100 / (bill_vat+100) AS amount,
  (bill_total_billing - bill_delivery_freight_charge) * 100 / (bill_vat+100) * bill_vat/100 AS vat,
  bill_total_billing - bill_delivery_freight_charge AS amount_vat,
  CASE
	WHEN b.bill_dept = 'A' AND b.bill_type_invoice = 0 THEN '../../apotik/_billing/revise_billing.php?_code='||bill_code
	WHEN b.bill_dept = 'A' AND b.bill_type_invoice = 1 THEN '../../apotik/_billing/revise_billing_2.php?_code='||bill_code
	WHEN b.bill_dept = 'D' AND b.bill_type_invoice = 0 THEN '../../dealer/_billing/revise_billing.php?_code='||bill_code
	WHEN b.bill_dept = 'D' AND b.bill_type_invoice = 1 THEN '../../dealer/_billing/revise_billing_2.php?_code='||bill_code
	WHEN b.bill_dept = 'H' AND b.bill_type_invoice = 0 THEN '../../hospital/_billing/revise_billing.php?_code='||bill_code
	WHEN b.bill_dept = 'H' AND b.bill_type_invoice = 1 THEN '../../hospital/_billing/revise_billing_2.php?_code='||bill_code
	WHEN b.bill_dept = 'P' AND b.bill_type_invoice = 0 THEN '../../pharmaceutical/_billing/revise_billing.php?_code='||bill_code
	WHEN b.bill_dept = 'P' AND b.bill_type_invoice = 1 THEN '../../pharmaceutical/_billing/revise_billing_2.php?_code='||bill_code
  END AS go_page
FROM
  ".ZKP_SQL."_tb_customer AS c
  JOIN ".ZKP_SQL."_tb_billing AS b ON  bill_cus_to = cus_code
WHERE $strWhere
ORDER BY bill_code";

if(isZKError($result = & query($sql)))
	$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");
?>
<html>
<head>
<title>PT. INDOCORE PERKASA [APOTIK MANAGEMENT SYSTEM]</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
</head>
<body topmargin="0" leftmargin="0">
<table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#9CBECC">
  <tr>
    <td>
			<?php require_once APP_DIR . "_include/tpl_header.php"?>
    </td>
  </tr>
  <tr>
    <td style="padding:5 10 0 10" valign="bottom">
			<?php require_once APP_DIR . "_include/tpl_topMenu.php";?>
    </td>
  </tr>
  <tr>
    <td style="padding:0 3 3 3">
    	<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
        <tr>
						<?php require_once "_left_menu.php";?>
						<td style="padding:10" height="480" valign="top">
          	<!--START: BODY-->
          	<h3>[<font color="#446fbe">GENERAL</font>] BILLING SUMMARY by invoice</h3>
			<div align="right">
			<form name="frmSearch" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
			<table width="100%" class="table_layout">
				<tr>
					<td> ORDER BY </td>
					<td> MARKETING </td>
					<td> DEPT </td>
					<td> INVOICE DATE </td>
					<td> INVOICE PERIOD </td>
					<td> VAT </td>
				</tr>
				<tr>
					<td>
					<select name="cboOrderBy">
						<option value="1">INDOCORE</option>
					</select>
				</td>
				<td>
<?php
$sql = "SELECT ma_idx, ma_account FROM ".ZKP_SQL."_tb_mbracc WHERE ma_display_as in (1)";
isZKError($res = & query($sql)) ? $M->printMessage($res):0;
	if(numQueryRows($res) <= 0) {
		$o = new ZKError("INFORMATION", "INFORMATION", "Please register the member hospital first");
		$M->printMessage($res);
	} else {
		print "\t\t\t<select name=\"cboFilterMarketing\" class=\"fmt\">\n";
		print "\t\t\t\t<option value=\"all\">==SELECT==</option>\n";
	
		while ($columns = fetchRow($res)) {
			print "\t\t\t\t<option value=\"".$columns[0]."\">".strtoupper($columns[1])."</option>\n";
		}
		print "\t\t\t</select>\n";
	}
?>
				</td>
					<td>
						<select name="cboFilterDept">
							<option value="all">==ALL==</option>
							<option value="A">A</option>
							<option value="D">D</option>
							<option value="H">H</option>
							<option value="P">P</option>
							<option value="S">CS</option>
						</select>
					</td>
					<td valign="middle">
						<input type="hidden" name="s_mode">
						<a href="javascript:setDate(-1)"> <img src="../../_images/icon/arrow_left.gif" alt="Previous date"> </a>
						<input type="text" name="some_date" size="10" class="fmtd" value="<?php echo $some_date?>">
						<a href="javascript:setDate(1)"> <img src="../../_images/icon/arrow_right.gif" alt="Next date"> </a>
					</td>
					<td>
						<select name="cboPeriod">
							<option value=""></option>
							<option value="lastMonth">LAST MONTH</option>
							<option value="thisMonth">THIS MONTH</option>
						</select>
						FROM <input type="text" name="period_from" size="8" class="fmtd" value="<?php echo $period_from; ?>">&nbsp;
						TO <input type="text" name="period_to" size="8" class="fmtd"  value="<?php echo $period_to; ?>">
					</td>
					<td>
						<select name="cboFilterVat">
							<option value="all">==ALL==</option>
							<option value="vat">VAT</option>
							<option value="vat-IO">VAT - IO</option>
							<option value="vat-IP">VAT - IP</option>
							<option value="non">NON VAT</option>
						</select>
					</td>
				</tr>
			</table>
			</form>
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	var ts = <?php echo time() * 1000;?>;

	setSelect(f.cboPeriod, "<?php echo isset($_GET['cboPeriod']) ? $_GET['cboPeriod'] : "default"?>");
	setSelect(f.cboFilterMarketing, "<?php echo isset($_GET['cboFilterMarketing']) ? $_GET['cboFilterMarketing'] : "all"?>");
	setSelect(f.cboOrderBy, "<?php echo isset($_GET['cboOrderBy']) ? $_GET['cboOrderBy'] : "1"?>");
	setSelect(f.cboFilterVat, "<?php echo isset($_GET['cboFilterVat']) ? $_GET['cboFilterVat'] : "vat-IO"?>");
	setSelect(f.cboFilterDept, "<?php echo isset($_GET['cboFilterDept']) ? $_GET['cboFilterDept'] : "all"?>");

	function setDate(value){
		var date = parseDate(f.some_date.value, 'prefer_euro_format');

		if(date == null) {date = new Date();}

		date.setDate(date.getDate()+value)
		f.some_date.value = formatDate(date, 'd-NNN-yyyy');
		f.period_from.value = '';
		f.period_to.value = '';
		f.cboPeriod.value = '';
		f.s_mode.value = 'date';
		f.submit();
	}

	f.cboOrderBy.onchange = function() {
		if(f.some_date.value.length > 0) {
			f.period_from.value = '';
			f.period_to.value = '';
			f.cboPeriod.value = '';
			f.s_mode.value = 'date';
		} else {
			f.some_date.value = '';
			f.s_mode.value = 'period';
		}
		f.submit();
	}

	f.cboFilterMarketing.onchange	= f.cboOrderBy.onchange;
	f.cboFilterVat.onchange			= f.cboOrderBy.onchange;
	f.cboFilterDept.onchange		= f.cboOrderBy.onchange;

	f.cboPeriod.onchange = function() {
		if (this.value != "") {
			setPeriod(ts, this.value, f.period_from, f.period_to);
			f.some_date.value = '';
			f.s_mode.value = 'period';
			f.submit();
		}
	}
	
	f.some_date.onkeypress = function() {
		if(window.event.keyCode == 13 && validDate(f.some_date)) {
			f.s_mode.value = 'date';
			f.cboPeriod.value = '';
			f.submit();
		}
	}

	f.period_from.onkeypress = function() {
		if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
			f.s_mode.value = 'period';
			f.cboPeriod.value = '';
			f.submit();
		}
	}

	f.period_to.onkeypress = function() {
		if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
			f.s_mode.value = 'period';
			f.cboPeriod.value = '';
			f.submit();
		}
	}
</script>
				</div><br />
<table width="100%" class="table_f">
	<tr>
		<th width="7%">INV. DATE</th>
		<th>CUSTOMER</th>
		<th width="15%">NPWP</th>
		<th width="13%">INVOICE NO</th>
		<th width="15%">FP. NO</th>
		<th width="8%">AMOUNT</th>
		<th width="8%">VAT</th>
		<th width="10%">AMOUNT<br>+VAT</th>
	</tr>
<?php 
$amount = 0;
$vat	= 0;
$total	= 0;
while ($columns =& fetchRowAssoc($result)) {

	echo "<tr>\n";
	cell($columns['inv_date'], ' align="center"');
	cell($columns['cus_full_name']);
	cell($columns['bill_npwp'], ' align="center"');
	cell_link($columns['bill_code'], ' align="center"', ' href="'.$columns['go_page'].'"');
	cell($columns['bill_vat_inv_no'], ' align="center"');
	cell(number_format($columns['amount']),' align="right"');
	cell(number_format($columns['vat']),' align="right"');
	cell(number_format($columns['amount_vat']),' align="right"');
	echo "</tr>\n";

	$amount	+= $columns['amount'];
	$vat	+= $columns['vat'];
	$total	+= $columns['amount_vat'];
}
?>
	<tr>
		<td colspan="5" align="right" style="color:brown; background-color:lightyellow">GRAND TOTAL</td>
		<td align="right" style="color:brown; background-color:lightyellow"><?php echo number_format($amount) ?></td>
		<td align="right" style="color:brown; background-color:lightyellow"><?php echo number_format($vat) ?></td>
		<td align="right" style="color:brown; background-color:lightyellow"><?php echo number_format($total) ?></td>
	</tr>
</table>
            <!--END: BODY-->
          </td>
        </tr>
      </table>
      </td>
  </tr>
  <tr>
    <td style="padding:5 10 5 10" bgcolor="#FFFFFF">
			<?php require_once APP_DIR . "_include/tpl_footer.php"?>
    </td>
  </tr>
</table>
</body>
</html>