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
require_once "../../_system/util_cities.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//Global
$left_loc 	  = "list_warranty.php";
$cboProvince	= isset($_GET['cboProvince'])? $_GET['cboProvince'] : "";
$cboCity		= isset($_GET['cboCity'])? $_GET['cboCity'] : "";
$period_from 	= isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", time() - 2592000);
$period_to 		= isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", time());

if(isset($_GET['lastCategoryNo']) && $_GET['lastCategoryNo'] > 0) {
	$_last_category = $_GET['lastCategoryNo'];

	//get category path from current icat_midx.
	if(isZKError($path = executeSP(ZKP_SQL."_getCategoryPath", $_last_category))) {
		$M->goErrorPage($path, HTTP_DIR . "$currentDept/$moduleDept/index.php");
	} else {
		eval(html_entity_decode($path[0]));	
		$path = array_reverse($path);
	}
	
} else {
	$_last_category	= 0;
}
?>
<html>
<head>
<title>PT. INDOCORE PERKASA [APOTIK MANAGEMENT SYSTEM]</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language="text/javascript" type="text/javascript" src="../../_script/js_category.php"></script>
<script language="text/javascript" type="text/javascript" src="../../_script/js_city.php"></script>
<script language='text/javascript' type='text/javascript'>
function fillOption(target, pidx) {
	target.options.length = 1;
	for(var i = 0; i<icat.length; i++) {
		if (icat[i][1] == pidx) {
			target.options[target.options.length] = new Option(icat[i][4], icat[i][0]);
		}
	}

	window.document.frmSearch.lastCategoryNo.value = pidx;
}

function fillOptionCity(target, province) {
	target.options.length = 1;
	for(var i = 0; i<city.length; i++) {
		if (city[i][1] == province) {
			target.options[target.options.length] = new Option(city[i][2], city[i][2]);
		}
	}
}

function fillOptionInit() {
	fillOption(window.document.frmSearch.icat_1, 0);
<?php
//Set initial option value
if(isset($path) && is_array($path)) {
	$count = count($path);
	for($i = 1; $i < $count; $i++) {
		echo "\twindow.document.frmSearch.icat_$i.value = \"{$path[$i][0]}\";\n";
	}
}
?>
}

function initPage() {
	fillOptionInit();
	if(window.document.frmSearch.cboProvince.value.length > 0) {
		fillOptionCity(window.document.frmSearch.cboCity, window.document.frmSearch.cboProvince.value);
		setSelect(f.cboCity, "<?php echo isset($_GET['cboCity']) ? $_GET['cboCity'] : ""?>");
	}
}
</script>
</head>
<body topmargin="0" leftmargin="0" onLoad="initPage()">
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
<h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] LIST WARRANTY CARD</h3>
<form name="frmSearch" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
<table width="100%" class="table_layout">
	<tr height="5px"><td></td></tr>
	<tr>
		<td width="30%"></td>
		<td> PRODUCT </td>
		<td> CITY </td>
		<td> PURCHASE DATE </td>
	</tr>
	<tr>
		<td></td>
		<td>
			<input type="hidden" name="lastCategoryNo" value="<?php echo $_last_category ?>">
			<select name="icat_1">
				<option value="0">==ALL==</option>
			</select>
		</td>
		<td>
<select name="cboProvince" class="req" onchange="fillOptionCity(window.document.frmSearch.cboCity, this.value)">
	<option value="">==SELECT==</option>
<?php
foreach($province as $p_key => $p_val) {
	print "\t<option value=\"".$p_val. "\">".$p_val."</option>\n";
}
?>
</select>
<select name="cboCity" class="req">
	<option value="">==SELECT==</option>
</select>
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
</table>
</form>
<?php //include "./report/rpt_daily_billing_detail_by_item.php"; ?>
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	var ts = <?php echo time() * 1000;?>;

	setSelect(f.cboPeriod, "<?php echo isset($_GET['cboPeriod']) ? $_GET['cboPeriod'] : ""?>");
	setSelect(f.cboProvince, "<?php echo isset($_GET['cboProvince']) ? $_GET['cboProvince'] : ""?>");

	var last_category = <?php echo isset($_GET['icat_1']) ? $_GET['icat_1'] : 0 ?>;

	f.cboProvince.onchange = function() {
		f.lastCategoryNo.value = last_category;
		f.submit();
	}

	f.cboCity.onchange = f.cboProvince.onchange;

	f.cboPeriod.onchange = function() {
		if (this.value != "") {
			setPeriod(ts, this.value, f.period_from, f.period_to);
			f.lastCategoryNo.value = last_category;
			f.submit();
		}
	}

	f.period_from.onkeypress = function() {
		if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
			f.lastCategoryNo.value = last_category;
			f.submit();
		}
	}

	f.period_to.onkeypress = function() {
		if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
			f.lastCategoryNo.value = last_category;
			f.submit();
		}
	}

	f.icat_1.onchange	  = function() {
		f.lastCategoryNo.value = this.value;
		f.submit();
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