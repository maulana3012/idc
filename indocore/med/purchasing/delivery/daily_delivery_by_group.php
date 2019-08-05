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
$left_loc = "daily_delivery_by_group.php";
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
$_cug_code	= isset($_GET['_cug_code']) ? $_GET['_cug_code'] : "all";
$_dept		= isset($_GET['_dept']) ? $_GET['_dept'] : "all";
$_vat		= isset($_GET['cboVat']) ? $_GET['cboVat'] : "all";
$_source	= isset($_GET['cboSource']) ? $_GET['cboSource'] : "all";

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
<strong class="title">[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] OUTGOING SUMMARY by group<br /><br /></strong>
<form name="frmSearch" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
<table width="100%" class="table_layout">
	<tr>
		<td> SOURCE </td>
		<td> PUSAT </td>
		<td> DEPT </td>
		<td> CONFIRM DATE </td>
		<td> CONFIRM PERIOD </td>
		<td> VAT </td>
	</tr>
	<tr>
		<td>
			<select name="cboSource">
				<option value="all">==ALL==</option>
				<option value="1">Billing</option>
				<option value="2">Order</option>
				<option value="3">DT</option>
				<option value="4">DF</option>
				<option value="5">DR</option>
				<option value="6">DM</option>
			</select>
		</td>
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
				<option value="S">CS</option>
				<option value="M">MKT</option>
			</select>
		</td>
		<td>
			<input type="hidden" name="s_mode">
				<select name="cboDate">
				<option value=""></option>
					<option value="-1">YESTERDAY</option>
					<option value="0">TODAY</option>
					<option value="1">TOMORROW</option>
				</select>
			<input type="text" name="some_date" size="8" class="fmtd" value="<?php echo $some_date?>">
		</td>
		<td>
			<select name="cboPeriod">
				<option value=""></option>
				<option value="lastWeek">LAST WEEK</option>
				<option value="lastMonth">LAST MONTH</option>
				<option value="thisWeek">THIS WEEK</option>
				<option value="thisMonth">THIS MONTH</option>
			</select>
			<input type="text" name="period_from" size="8" class="fmtd" value="<?php echo $period_from; ?>">&nbsp;
			<input type="text" name="period_to" size="8" class="fmtd"  value="<?php echo $period_to; ?>">
		</td>
		<td>
			<select name="cboVat">
				<option value="all">==ALL==</option>
				<option value="1">VAT</option>
				<option value="2">NON VAT</option>
			</select>
		</td>
	</tr>
</table>
</form>
<table width="100%" class="table_box">
	<tr>
		<td align="right">
			<a href="./daily_delivery_by_item.php?<?php echo getQueryString()?>">View By Item</a>
		</td>
	</tr>
</table>
<?php require_once APP_DIR . "_include/purchasing/report/delivery/rpt_daily_delivery_detail_by_group.php" ?>
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	var ts = <?php echo time() * 1000;?>;

	setSelect(f.cboDate, "<?php echo isset($_GET['cboDate']) ? $_GET['cboDate'] : "default"?>");
	setSelect(f.cboPeriod, "<?php echo isset($_GET['cboPeriod']) ? $_GET['cboPeriod'] : "default"?>");
	setSelect(f._cug_code, "<?php echo isset($_GET['_cug_code']) ? $_GET['_cug_code'] : "all"?>");
	setSelect(f._dept, "<?php echo isset($_GET['_dept']) ? $_GET['_dept'] : "all"?>");
	setSelect(f.cboVat, "<?php echo isset($_GET['cboVat']) ? $_GET['cboVat'] : "all"?>");
	setSelect(f.cboSource, "<?php echo isset($_GET['cboSource']) ? $_GET['cboSource'] : "all"?>");

	f._cug_code.onchange = function() {
		if(f.cboDate.value != '' || f.some_date.value != '') {
			f.period_from.value = '';
			f.period_to.value = '';
			f.cboPeriod.value = '';
			f.s_mode.value = 'date';
		} else {
			f.some_date.value = '';
			f.s_mode.value = 'period';
			f.cboDate.value = '';
		}
		f.submit();
	}

	f.cboVat.onchange		= f._cug_code.onchange;
	f.cboSource.onchange	= f._cug_code.onchange;
	f._dept.onchange 		= f._cug_code.onchange;

	f.cboDate.onchange = function() {
		setDate(ts, this.value, f.some_date);

		if (f.some_date.value != "" || f.some_date.value != '') {
			f.period_from.value = '';
			f.period_to.value = '';
			f.cboPeriod.value = '';
			f.s_mode.value = 'date';
			f.submit();
		}
	}
	
	f.cboPeriod.onchange = function() {
		if (this.value != "") {
			setPeriod(ts, this.value, f.period_from, f.period_to);
			f.some_date.value = '';
			f.s_mode.value = 'period';
			f.cboDate.value = '';
			f.submit();
		}
	}
	
	f.some_date.onkeypress = function() {
		if(window.event.keyCode == 13 && validDate(f.some_date)) {
			f.s_mode.value = 'date';
			f.cboDate.value = '';
			f.cboPeriod.value = '';
			f.submit();
		}
	}

	f.period_from.onkeypress = function() {
		if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
			f.s_mode.value = 'period';
			f.cboDate.value = '';
			f.cboPeriod.value = '';
			f.submit();
		}
	}

	f.period_to.onkeypress = function() {
		if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
			f.s_mode.value = 'period';
			f.cboDate.value = '';
			f.cboPeriod.value = '';
			f.submit();
		}
	}

	function checkAll(o) {
		var oCheck = window.document.all.tags("INPUT");
	
		for (var i = 0; i < oCheck.length; i++) {
			if (oCheck[i].type == "checkbox" && oCheck[i].name == "chkDO[]") {
				oCheck[i].checked = o;
			}
		}
	}

	function summarizeDO() {
		var oCheck		 = window.document.all.tags("INPUT");
		var keyword		 = '';
		var counter		 = 0;

		for (var i = 0; i < oCheck.length; i++) {
			if (oCheck[i].type == "checkbox" && oCheck(i).name == "chkDO[]" && oCheck(i).checked) {
				if(keyword == '') {
					keyword = oCheck[i].value;
				} else {
					keyword = keyword + ', ' + oCheck[i].value;
				}
			}
		}

		if(keyword == '') {
			alert("You haven't checked any DO Code.\nPlease check first");
			return;
		}

		var x = (screen.availWidth - 550) / 2;
		var y = (screen.availHeight - 470) / 2;
		var win = window.open(
			'./p_summary_delivery_do.php?_code='+ keyword,
			'',
			'scrollbars,width=550,height=470,screenX='+x+',screenY='+y+',left='+x+',top='+y);
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