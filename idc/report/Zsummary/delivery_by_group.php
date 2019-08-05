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

//GLOBAL
$left_loc = "delivery_by_group.php";
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
<?php
$s_mode		= isset($_GET['s_mode']) ? $_GET['s_mode'] : "period";
$_order_by	= isset($_GET['cboFilterOrder']) ? $_GET['cboFilterOrder'] : "all";
$_marketing	= isset($_GET['cboFilterMarketing']) ? $_GET['cboFilterMarketing'] : "all";
$_cug_code	= isset($_GET['cboFilterCusGroup']) ? $_GET['cboFilterCusGroup'] : "all";
$_document	= isset($_GET['cboFilterDoc']) ? $_GET['cboFilterDoc'] : "all";
$_status	= isset($_GET['cboFilterStatus']) ? $_GET['cboFilterStatus'] : "all";
$_sort_date	= isset($_GET['cboFilterSortDate']) ? $_GET['cboFilterSortDate'] : "inv";

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
?>
<form name="frmSearch" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
<table width="100%" class="table_layout">
	<tr>
		<td width="80%" rowspan="2"><h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] DELIVERY SUMMARY by group</h3></td>
		<td> ORDER BY </td>
		<td> MARKETING </td>
	</tr>
	<tr>
		<td>
			<select name="cboFilterOrder">
				<option value="0">==ALL==</option>
				<option value="1">INDOCORE</option>
			</select>
		</td>
		<td>
<?php
$sql = "SELECT ma_idx, ma_account FROM ".ZKP_SQL."_tb_mbracc WHERE ma_display_as in (1)";
isZKError($result = & query($sql)) ? $M->printMessage($result):0;
	if(numQueryRows($result) <= 0) {
		$o = new ZKError("INFORMATION", "INFORMATION", "Please register the member hospital first");
		$M->printMessage($result);
	} else {
		print "\t\t\t<select name=\"cboFilterMarketing\" class=\"fmt\">\n";
		print "\t\t\t\t<option value=\"all\">==SELECT==</option>\n";
	
		while ($columns = fetchRow($result)) {
			print "\t\t\t\t<option value=\"".$columns[0]."\">".strtoupper($columns[1])."</option>\n";
		}
		print "\t\t\t</select>\n";
	}
?>
		</td>
	</tr>
</table><br />
<table width="100%" class="table_layout">
	<tr>
		<td> PUSAT </td>
		<td> FILTER BY </td>
		<td> STATUS </td>
		<td> SORT DATE BY</td>
		<td> DATE </td>
		<td> PERIOD </td>
	</tr>
	<tr>
		<td>
<?php
	$sql = "SELECT cug_code, substr(cug_name, 1, 13) || '...' AS cus_name FROM ".ZKP_SQL."_tb_customer_group ORDER BY cug_name";
	isZKError($result = & query($sql)) ? $M->printMessage($result):0;

	if(numQueryRows($result) <= 0) {
		$o = new ZKError("INFORMATION", "INFORMATION", "Please register the $arg first. you can find the [new ". ucfirst($arg) ."] under the BASIC DATA menu");
		$M->printMessage($result);
	} else {
		print "<select name=\"cboFilterCusGroup\" class=\"req\">\n";
		print "\t<option value=\"all\">==ALL==</option>\n";
	
		while ($columns = fetchRow($result)) {
			print "\t<option value=\"".$columns[0]."\">".$columns[1]."</option>\n";
		}
		print "</select>\n";
	}
?>
		</td>
		<td>
			<select name="cboFilterDoc">
				<option value="all">==ALL==</option>
				<option value="I">INVOICE</option>
				<option value="R">RETURN</option>
				<option value="DT">DT</option>
				<option value="DF">DF</option>
				<option value="DR">DR</option>
			</select>
		</td>
		<td>
			<select name="cboFilterStatus">
				<option value="all">==ALL==</option>
				<option value="pending">PENDING</option>
				<option value="delivered">DELIVERED</option>
			</select>
		</td>
		<td>
			<select name="cboFilterSortDate">
				<option value="inv">INVOICE DATE</option>
				<option value="deli">DELIVERY DATE</option>
			</select>
		</td>
		<td>
			<input type="hidden" name="s_mode">
			<a href="javascript:setDate(-1)"> <img src="../../_images/icon/arrow_left.gif" alt="Previous date"> </a>
			<input type="text" name="some_date" size="10" class="fmtd" value="<?php echo $some_date?>">
			<a href="javascript:setDate(1)"> <img src="../../_images/icon/arrow_right.gif" alt="Next date"> </a>
		</td>
		<td>
			<select name="cboPeriod">
				<option value=""></option>
				<option value="lastWeek">LAST WEEK</option>
				<option value="lastMonth">LAST MONTH</option>
				<option value="thisWeek">THIS WEEK</option>
				<option value="thisMonth">THIS MONTH</option>
			</select>
			<input type="text" name="period_from" size="10" class="fmtd" value="<?php echo $period_from; ?>">&nbsp;
			<input type="text" name="period_to" size="10" class="fmtd"  value="<?php echo $period_to; ?>">
		</td>
	</tr>
</table>
</form>
<div align="right">
	<a href="./delivery_by_item.php?<?php echo getQueryString()?>">View By Item</a>
</div><br />
<?php include "./report/rpt_delivery_by_group.php"; ?>
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	var ts = <?php echo time() * 1000;?>;

	setSelect(f.cboPeriod, "<?php echo isset($_GET['cboPeriod']) ? $_GET['cboPeriod'] : "default"?>");
	setSelect(f.cboFilterOrder, "<?php echo isset($_GET['cboFilterOrder']) ? $_GET['cboFilterOrder'] : "all"?>");
	setSelect(f.cboFilterMarketing, "<?php echo isset($_GET['cboFilterMarketing']) ? $_GET['cboFilterMarketing'] : "all"?>");
	setSelect(f.cboFilterCusGroup, "<?php echo isset($_GET['cboFilterCusGroup']) ? $_GET['cboFilterCusGroup'] : "all"?>");
	setSelect(f.cboFilterDoc, "<?php echo isset($_GET['cboFilterDoc']) ? $_GET['cboFilterDoc'] : "all"?>");
	setSelect(f.cboFilterStatus, "<?php echo isset($_GET['cboFilterStatus']) ? $_GET['cboFilterStatus'] : "all"?>");
	setSelect(f.cboFilterSortDate, "<?php echo isset($_GET['cboFilterSortDate']) ? $_GET['cboFilterSortDate'] : "inv"?>");

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

	f.cboFilterOrder.onchange = function() {
		if(f.some_date.value != '') {
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

	f.cboFilterMarketing.onchange	= f.cboFilterOrder.onchange;
	f.cboFilterCusGroup.onchange	= f.cboFilterOrder.onchange;
	f.cboFilterDoc.onchange			= f.cboFilterOrder.onchange;
	f.cboFilterStatus.onchange		= f.cboFilterOrder.onchange;
	f.cboFilterSortDate.onchange	= f.cboFilterOrder.onchange;

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