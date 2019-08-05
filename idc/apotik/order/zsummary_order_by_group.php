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
require_once LIB_DIR . "zk_listing.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "summary_order_by_group.php";
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script src="../../_script/jQuery.js" type="text/javascript"></script>
<script language='text/javascript' type='text/javascript'>
$(document).ready(function(){
	var url		= $("input[name$=web_url]").val();
	var dept	= $("input[name$=web_dept]").val();

	var ishideFilterOrderBy	= new Array("IDC","MED","MEP");
	var ishideFilterGroupBy	= new Array("dealer","hospital","maketing", "pharmaceutical", "tender");

	if(in_array(url, ishideFilterOrderBy)) $(".divOrderBy").hide();
	if(in_array(dept, ishideFilterGroupBy)) $(".divGroupBy").hide();

});
</script>
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
$s_mode			= isset($_GET['s_mode']) ? $_GET['s_mode'] : "date";
$_cug_code		= isset($_GET['_cug_code']) ? $_GET['_cug_code'] : "all";
$_sales_type	= isset($_GET['cboSalesType']) ? $_GET['cboSalesType'] : "all";
$_filter_by		= isset($_GET['cboFilterBy']) ? $_GET['cboFilterBy'] : "all";
$_paper			= isset($_GET['cboFilterPaper']) ? $_GET['cboFilterPaper'] : "all";
$_order_by		= isset($_GET['cboFilterOrderBy']) ? $_GET['cboFilterOrderBy'] : $cboFilter[1][ZKP_FUNCTION][0][0];

if($s_mode == 'period') {
	$some_date 		= "";
	$period_from 	= $_GET['period_from'];
	$period_to 		= $_GET['period_to'];
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
<input type="hidden" name="web_url" value="<?php echo ZKP_URL ?>">
<input type="hidden" name="web_dept" value="<?php echo $currentDept ?>">
<table width="100%" class="table_layout">
	<tr>
		<td width="90%" rowspan="2"><h4>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] ORDER SUMMARY by group</h4></td>
		<td><div class="divOrderBy"> ORDER BY </div></td>
		<td><div class="divGroupBy"> PUSAT </div></td>
	</tr>
	<tr>
		<td><div class="divOrderBy">
			<select name="cboFilterOrderBy">
<?php
foreach($cboFilter[1][ZKP_FUNCTION] as $val => $key) {
	echo "\t\t\t\t<option value=\"".$key[0]."\">".$key[1]."</option>\n";
}
?>
			</select></div>
		</td>
		<td><div class="divGroupBy">
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
		</div></td>
	</tr>
</table>
<table width="100%" class="table_layout">
	<tr>
		<td width="10%" rowspan="2"></td>
		<td> FILTER BY </td>
		<td> SALES TYPE </td>
		<td> TYPE </td>
		<td> ORDER DATE </td>
		<td> ORDER PERIOD </td>
	</tr>
	<tr>
		<td>
			<select name="cboFilterBy">
				<option value="all">==ALL==</option>
				<option value="0">INVOICE</option>
				<option value="1">RETURN</option>
			</select>
		</td>
		<td>
			<select name="cboSalesType">
				<option value="all">==ALL==</option>
				<option value="0">OO</option>
				<option value="1">OK</option>
			</select>
		</td>
		<td>
			 <select name="cboFilterPaper">
				<option value="all">==ALL==</option>
				<option value="0">No. &amp; Item</option>
				<option value="1">No. Only</option>
			</select>
		</td>
		<td>
			<input type="hidden" name="s_mode">
				<select name="cboDate">
				<option value=""></option>
					<option value="-1">YESTERDAY</option>
					<option value="0">TODAY</option>
				</select>
			<input type="text" name="some_date" size="10" class="fmtd" value="<?php echo $some_date?>">
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
<table width="100%" class="table_box">
	<tr>
		<td align="right">
			<a href="<?php echo HTTP_DIR . "$currentDept/$moduleDept/"?>summary_order_by_item.php?<?php echo getQueryString()?>">View By Item</a>
		</td>
	</tr>
</table><br />
<?php require_once APP_DIR . "_include/order/report/rpt_daily_order_detail_by_group.php" ?>
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	var ts = <?php echo time() * 1000;?>;

	setSelect(f.cboDate, "<?php echo isset($_GET['cboDate']) ? $_GET['cboDate'] : "0"?>");
	setSelect(f.cboPeriod, "<?php echo isset($_GET['cboPeriod']) ? $_GET['cboPeriod'] : "default"?>");
	setSelect(f.cboFilterBy, "<?php echo isset($_GET['cboFilterBy']) ? $_GET['cboFilterBy'] : "all"?>");
	setSelect(f.cboSalesType, "<?php echo isset($_GET['cboSalesType']) ? $_GET['cboSalesType'] : "all"?>");
	setSelect(f._cug_code, "<?php echo isset($_GET['_cug_code']) ? $_GET['_cug_code'] : "all"?>");
	setSelect(f.cboFilterPaper, "<?php echo isset($_GET['cboFilterPaper']) ? $_GET['cboFilterPaper'] : "all"?>");
	setSelect(f.cboFilterOrderBy, "<?php echo isset($_GET['cboFilterOrderBy']) ? $_GET['cboFilterOrderBy'] : $cboFilter[1][ZKP_FUNCTION][0][0] ?>");
	
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

	f.cboSalesType.onchange		= f._cug_code.onchange;
	f.cboFilterBy.onchange		= f._cug_code.onchange;
	f.cboFilterPaper.onchange	= f._cug_code.onchange;
	f.cboFilterOrderBy.onchange	= f._cug_code.onchange;

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