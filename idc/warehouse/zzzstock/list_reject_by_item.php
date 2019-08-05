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
$left_loc 	= "list_reject_by_item.php";
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
$_dept		= isset($_GET['cboDept']) ? $_GET['cboDept'] : "all";
$_status	= isset($_GET['cboStatus']) ? $_GET['cboStatus'] : "all";

if($s_mode == 'period') {
	$some_date 		= "";
	$period_from 	= isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", time()-604800);
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
<h4>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] REJECT SUMMARY by item</h4>
<form name="frmSearch" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
<table width="100%" class="table_layout">
	<tr>
		<td width="70%"> </td>
		<td> DEPT </td>
		<td> STATUS </td>
		<td> DATE </td>
		<td> PERIOD </td>
	</tr>
	<tr>
		<td></td>
		<td>
			<select name="cboDept">
				<option value="all">==ALL==</option>
				<option value="A">A</option>
				<option value="D">D</option>
				<option value="H">H</option>
				<option value="P">P</option>
			</select>
		</td>
		<td>
			<select name="cboStatus">
				<option value="all">==ALL==</option>
				<option value="on_wh">In Warehouse</option>
				<option value="on_repair">On Repair</option>
				<option value="on_stock">Back to Stock</option>
				<option value="on_deleted">Deleted</option>
			</select>
		</td>
		<td>
			<input type="hidden" name="s_mode">
				<select name="cboDate">
				<option value=""></option>
					<option value="-1">YESTERDAY</option>
					<option value="0">TODAY</option>
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
			<input type="text" name="period_from" size="10" class="fmtd" value="<?php echo $period_from; ?>">&nbsp;
			<input type="text" name="period_to" size="10" class="fmtd"  value="<?php echo $period_to; ?>">
		</td>
	</tr>
</table><br />
</form>
<table width="100%" class="table_box">
	<tr>
		<td align="right">
			<a href="./list_reject_by_group.php?<?php echo getQueryString()?>">View By Group</a>
		</td>
	</tr>
</table>
<?php require_once APP_DIR . "_include/warehouse/report/stock/rpt_list_reject_by_item.php" ?>
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	var ts = <?php echo time() * 1000;?>;

	setSelect(f.cboDate, "<?php echo isset($_GET['cboDate']) ? $_GET['cboDate'] : "default"?>");
	setSelect(f.cboPeriod, "<?php echo isset($_GET['cboPeriod']) ? $_GET['cboPeriod'] : "default"?>");
	setSelect(f.cboDept, "<?php echo isset($_GET['cboDept']) ? $_GET['cboDept'] : "all"?>");
	setSelect(f.cboStatus, "<?php echo isset($_GET['cboStatus']) ? $_GET['cboStatus'] : "all"?>");

	f.cboDept.onchange = function() {
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

	f.cboStatus.onchange = f.cboDept.onchange;

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

	function detailReject(idx, btnDel) {
		var x = (screen.availWidth - 470) / 2;
		var y = (screen.availHeight - 250) / 2;
		var win = window.open(
			'./p_detail_reject.php?_idx='+idx+'&btnDel='+btnDel,
			'',
			'scrollbars,width=550,height=250,screenX='+x+',screenY='+y+',left='+x+',top='+y);
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