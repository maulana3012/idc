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
ckperm(ZKP_SELECT, HTTP_DIR ."$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc	= "list_apotik_policy.php";
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language='text/javascript' type='text/javascript'>
function checkform(o) {
	if (verify(o)) {
		o.submit();
	}
}
</script>
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
			<h4>APOTIK POLICY LIST</h4>

<!--START: SEARCH-->
			<div align="right">
<?php
$s_mode = (isset($_GET['s_mode']) && $_GET['s_mode'] != '') ? $_GET['s_mode'] : "period"; // Default period

//INITIALIZE VALUE
if ($s_mode == 'date') {
	$some_date = $_GET['some_date'];
	$period_from = "";
	$period_to = "";
	$sql = "
SELECT ap.ap_idx, ap.cus_code, cus.cus_full_name, ap.ap_desc,
to_char(ap.ap_date_from, 'dd-Mon-yyyy') AS date_from, to_char(ap.ap_date_to, 'dd-Mon-yyyy') AS date_to,
ap.ap_basic_disc_pct, ap.ap_disc_pct, ap.ap_is_valid, ap.ap_is_apply_all
FROM ".ZKP_SQL."_tb_apotik_policy AS ap INNER JOIN ".ZKP_SQL."_tb_customer AS cus USING(cus_code)
WHERE (ap_date_from, ap_date_to + 1) OVERLAPS (DATE '$some_date', DATE '$some_date')
ORDER BY ap.cus_code, ap_idx desc";

// Deafule is period
} else {
	$some_date = "";
	$period_from = (isset($_GET['period_from']) && $_GET['period_from']!='')?$_GET['period_from']:date("d-M-Y", time()-2592000); //Default 30 days ago
	$period_to = (isset($_GET['period_to']) && $_GET['period_to']!='')?$_GET['period_to']:date("d-M-Y", time()+2592000); //Default after 30 days
	$sql = "
SELECT ap.ap_idx, ap.cus_code, cus.cus_full_name, ap.ap_desc,
to_char(ap.ap_date_from, 'dd-Mon-yyyy') AS date_from, to_char(ap.ap_date_to, 'dd-Mon-yyyy') AS date_to,
ap.ap_basic_disc_pct, ap.ap_disc_pct, ap.ap_is_valid, ap.ap_is_apply_all
FROM ".ZKP_SQL."_tb_apotik_policy AS ap INNER JOIN ".ZKP_SQL."_tb_customer AS cus USING(cus_code)
WHERE (ap_date_from, ap_date_to + 1) OVERLAPS (DATE '$period_from', DATE '$period_to')
ORDER BY ap.cus_code, ap_idx desc";
}
?>
			<form name="frmSearch" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
				DATE:
				<input type="hidden" name="s_mode">
					<select name="cboDate">
						<option value=""></option>
						<option value="0">TODAY</option>
						<option value="-1">YESTERDAY</option>
					</select>
				<input type="text" name="some_date" size="10" class="fmtd" value="<?php echo $some_date?>"> |
				PERIOD
				<select name="cboPeriod">
					<option value=""></option>
					<option value="thisWeek">THIS WEEK</option>
					<option value="lastWeek">LAST WEEK</option>
					<option value="thisMonth">THIS MONTH</option>
					<option value="lastMonth">LAST MONTH</option>
				</select>
				FROM <input type="text" name="period_from" size="10" class="fmtd" value="<?php echo $period_from; ?>">&nbsp;
				TO <input type="text" name="period_to" size="10" class="fmtd"  value="<?php echo $period_to; ?>">
			</form>
			<script language="javascript1.2" type="text/javascript">
				var f = window.document.frmSearch;
				var ts = <?php echo time() * 1000;?>;

				setSelect(f.cboDate, "<?php echo isset($_GET['cboDate']) ? $_GET['cboDate'] : "default"?>");
				setSelect(f.cboPeriod, "<?php echo isset($_GET['cboPeriod']) ? $_GET['cboPeriod'] : "default"?>");
				
				f.cboDate.onchange = function() {
					setDate(ts, this.value, f.some_date);

					if (f.some_date.value != "") {
						f.period_from.value = '';
						f.period_to.value = '';
						f.cboPeriod.value = '';
						f.s_mode.value = 'date';
						f.submit();
					}
				}
				
				f.cboPeriod.onchange = function() {
					setPeriod(ts, this.value, f.period_from, f.period_to);
					f.some_date.value = '';
						f.s_mode.value = 'period';
						f.cboDate.value = '';
						f.submit();
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
			</div>
<!-- END: SEARCH -->
<?php require_once APP_DIR . "_include/order/report/rpt_price_policy_apotik_additional_disc.php" ?>
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