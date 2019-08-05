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
$left_loc 	  = "summary_reg_by_item.php";
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
			<strong class="title">[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] SERVICE REGISTRATION SUMMARY by item</strong><br /><br />
<?php
$s_mode		= isset($_GET['s_mode']) ? $_GET['s_mode'] : "period";

if($s_mode == 'period') {
	$some_date 		= "";
	$period_from 	= isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", time()-2592000);
	$period_to 		= isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", time());
} elseif ($s_mode == 'date') {

	if(isset($_GET['some_date'])) {
		$some_date = $_GET['some_date'];
	} else {
		$some_date = date('j-M-Y');
	}
	$period_from 		= "";
	$period_to 			= "";
}
?>
					<form name="frmSearch" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
					<table width="100%" class="table_layout">
						<tr>
							<td width="70%"></td>
							<td> REG DATE </td>
							<td> REG PERIOD </td>
						</tr>
						<tr>
							<td></td>
                            <td valign="middle">
                                <input type="hidden" name="s_mode">
                                <a href="javascript:setFilterDate('date',-1)"> <img src="../../_images/icon/arrow_left.gif" alt="Previous date"> </a>
                                <input type="text" name="some_date" size="10" class="fmtd" value="<?php echo $some_date?>">
                                <a href="javascript:setFilterDate('date',1)"> <img src="../../_images/icon/arrow_right.gif" alt="Next date"> </a>
                            </td>
                            <td>
                                <a href="javascript:setFilterDate('period',-1)"> <img src="../../_images/icon/arrow_left.gif" alt="Previous month"> </a>
                                <input type="text" name="period_from" size="10" class="fmtd" value="<?php echo $period_from; ?>">&nbsp;
                                <input type="text" name="period_to" size="10" class="fmtd"  value="<?php echo $period_to; ?>">
                                <a href="javascript:setFilterDate('period',1)"> <img src="../../_images/icon/arrow_right.gif" alt="Next month"> </a>
                            </td>
						</tr>
					</table>
				</form>
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	var ts = <?php echo time() * 1000;?>;

	function setFilterDate(status, value){
		f.s_mode.value = status;
		if(status == 'date') {
			var date = parseDate(f.some_date.value, 'prefer_euro_format');
			setFilterDateCalc(date, value, f.some_date);
			f.period_from.value = '';
			f.period_to.value = '';
		} else if(status == 'period') {
			var d = new Date(ts);
			setFilterPeriodCalc(d, value, f.period_from, f.period_to);
		}
		f.submit();
	}	
	
	f.some_date.onkeypress = function() {
		if(window.event.keyCode == 13 && validDate(f.some_date)) {
			f.s_mode.value = 'date';
			f.submit();
		}
	}

	f.period_from.onkeypress = function() {
		if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
			f.s_mode.value = 'period';
			f.submit();
		}
	}

	f.period_to.onkeypress = function() {
		if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
			f.s_mode.value = 'period';
			f.submit();
		}
	}
</script>
			<table width="100%" class="table_box">
				<tr>
					<td align="right">
						<a href="./summary_reg_by_customer.php?<?php echo getQueryString()?>">View By Group</a>
					</td>
				</tr>
			</table>
			<?php include "./report/rpt_summary_reg_by_customer.php"?>
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