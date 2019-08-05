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
ckperm(ZKP_SELECT, HTTP_DIR . "warehouse/warehouse/index.php");

//Global
$left_loc 	  = "daily_summary_by_period.php";
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
<?php
$s_mode 	= isset($_GET['s_mode']) ? $_GET['s_mode'] : "period";
$_order_by	= isset($_GET['cboOrderBy']) ? $_GET['cboOrderBy'] : "0";
$_sort_date	= isset($_GET['cboSortDateBy']) ? $_GET['cboSortDateBy'] : "doc_date";
$_cug_code	= isset($_GET['_cug_code']) ? $_GET['_cug_code'] : "all";
$_source 	= isset($_GET['cboSource']) ? $_GET['cboSource'] : "all";
$_dept		= isset($_GET['_dept']) ? $_GET['_dept'] : "all";
$_status	= isset($_GET['cboStatus']) ? $_GET['cboStatus'] : "all";

if($s_mode == 'period') {
	$some_date 		= "";
	$period_from 	= isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", time()-1209600);
	$period_to 		= isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", time()+1209600);
} elseif ($s_mode == 'date') {
	$some_date 		= $_GET['some_date'];
	$period_from 	= "";
	$period_to 		= "";
}
?>
<form name="frmSearch" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
<table width="100%" class="table_layout">
	<tr>
		<td width="80%" rowspan="2"><h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] DAILY SUMMARY by period</h3></td>
		<td> ORDER BY </td>
		<td> SOURCE </td>
	</tr>
	<tr>
		<td>
			<select name="cboOrderBy">
				<option value="0">==ALL==</option>
				<option value="1">INDOCORE</option>
				<option value="2">MEDIKUS EKA</option>
			</select>
		</td>
		<td>
			<select name="cboSource">
				<option value="all">==ALL==</option>
				<option value="0">BILLING</option>
				<option value="1">ORDER</option>
				<option value="2">RETURN BILLING</option>
				<option value="3">RETURN ORDER</option>
			</select>
		</td>
	</tr>
</table><br />
<table width="100%" class="table_layout">
	<tr>
		<td> PUSAT </td>
		<td> DEPT </td>
		<td> SORT DATE BY </td>
		<td> DATE </td>
		<td> PERIOD </td>
		<td> STATUS </td>
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
		print "<select name=\"_cug_code\" class=\"req\">\n";
		print "\t<option value=\"all\">==ALL==</option>\n";
	
		while ($columns = fetchRow($result)) {
			print "\t<option value=\"".$columns[0]."\">".$columns[1]."</option>\n";
		}
		print "</select>\n";
	}
?>
		</td>
		<td>
			<select name="_dept">
				<option value="all">==ALL==</option>
				<option value="A">A</option>
				<option value="D">D</option>
				<option value="H">H</option>
				<option value="P">P</option>
			</select>
		</td>
		<td>
			<select name="cboSortDateBy">
				<option value="doc_date">DOCUMENT DATE</option>
				<option value="cfm_date">CONFIRM DATE</option>
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
				<option value="lastWeek">LAST WEEK</option>
				<option value="lastMonth">LAST MONTH</option>
				<option value="thisWeek">THIS WEEK</option>
				<option value="thisMonth">THIS MONTH</option>
				<option value="nextWeek">NEXT WEEK</option>
				<option value="nextMonth">NEXT MONTH</option>
			</select>
			<input type="text" name="period_from" size="10" class="fmtd" value="<?php echo $period_from; ?>">&nbsp;
			<input type="text" name="period_to" size="10" class="fmtd"  value="<?php echo $period_to; ?>">
		</td>
		<td>
			<select name="cboStatus">
				<option value="all">==ALL==</option>
				<option value="0">UNCONFIRMED</option>
				<option value="1">CONFIRM</option>
			</select>
		</td>
	</tr>
</table>
</form><br />
<?php require_once APP_DIR . "_include/warehouse/report/warehouse/rpt_daily_summary_by_period.php" ?>
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	var ts = <?php echo time() * 1000?>;

	setSelect(f.cboPeriod, "<?php echo isset($_GET['cboPeriod']) ? $_GET['cboPeriod'] : "default"?>");
	setSelect(f._cug_code, "<?php echo isset($_GET['_cug_code']) ? $_GET['_cug_code'] : "all"?>");
	setSelect(f.cboOrderBy, "<?php echo isset($_GET['cboOrderBy']) ? $_GET['cboOrderBy'] : "0"?>");
	setSelect(f.cboSortDateBy, "<?php echo isset($_GET['cboSortDateBy']) ? $_GET['cboSortDateBy'] : "doc_date"?>");
	setSelect(f.cboSource, "<?php echo isset($_GET['cboSource']) ? $_GET['cboSource'] : "all"?>");
	setSelect(f._dept, "<?php echo isset($_GET['_dept']) ? $_GET['_dept'] : "all"?>");
	setSelect(f.cboStatus, "<?php echo isset($_GET['cboStatus']) ? $_GET['cboStatus'] : "all"?>");

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

	f._cug_code.onchange = function() {
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

	f.cboOrderBy.onchange		= f._cug_code.onchange;
	f.cboSortDateBy.onchange	= f._cug_code.onchange;
	f.cboSource.onchange		= f._cug_code.onchange;
	f._dept.onchange			= f._cug_code.onchange;
	f.cboStatus.onchange		= f._cug_code.onchange;

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