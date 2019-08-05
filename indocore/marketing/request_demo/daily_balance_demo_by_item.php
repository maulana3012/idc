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
$left_loc 	  = "daily_balance_demo_by_item.php";

if(isset($_GET['lastCategoryNo']) && $_GET['lastCategoryNo'] > 0) {
	$_last_category = $_GET['lastCategoryNo'];

	//get category path from current icat_midx.
	if(isZKError($path = executeSP(ZKP_SQL."_getCategoryPath", $_last_category))) {
		$M->goErrorPage($path, HTTP_DIR . "$currentDept/$moduleDept/daily_order_by_item.php");
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
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script src="../../_script/jQuery.js" type="text/javascript"></script>
<script language="text/javascript" type="text/javascript" src="../../_script/js_category.php"></script>
<script language='text/javascript' type='text/javascript'>
function fillOptionInit() {
	var target = window.document.frmSearch.icat_1;
	var pidx = 0;

	target.options.length = 1;
	for(var i = 0; i<icat.length; i++) {
		if (icat[i][1] == pidx) {
			target.options[target.options.length] = new Option(icat[i][4], icat[i][0]);
		}
	}

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

function changeCustomer() {
	if(window.document.frmSearch.isFilledCus.checked) {
		fillCustomer('', window.document.frmSearch.isFilledCus.checked);
	}
}

function fillCustomer(code, is_checked) {
	if(is_checked) {
		var x = (screen.availWidth - 400) / 2;
		var y = (screen.availHeight - 600) / 2;
		var win = window.open(
			'../../_include/request_demo/p_list_cus_code.php?_dept=<?php echo $department ?>&_check_code='+ code,
			'summary',
			'scrollbars,width=400,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	} else {
		var f = window.document.frmSearch;
		f._cus_code.value = '';	
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
}

$(document).ready(function(){
	var url		= $("input[name$=web_url]").val();
	var ishideFilterOrderBy	= new Array("IDC","MED","MEP");
	if(in_array(url, ishideFilterOrderBy)) $(".divOrderBy").hide();
	fillOptionInit();
});
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
<?php
$_order_by	= isset($_GET['cboFilterOrderBy']) ? $_GET['cboFilterOrderBy'] : $cboFilter[1][ZKP_FUNCTION][0][0];
$_cus_code	= isset($_GET['_cus_code']) ? $_GET['_cus_code'] : "";
$_status	= isset($_GET['cboStatus']) ? $_GET['cboStatus'] : "all";
$s_mode 	= isset($_GET['s_mode']) ? $_GET['s_mode'] : "period";

if($s_mode == 'period') {
	$some_date 		= "";
	$period_from 	= isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", time()-604800);
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
<input type="hidden" name="web_url" value="<?php echo ZKP_URL ?>">
<input type="hidden" name="web_dept" value="<?php echo $currentDept ?>">
<table width="100%" class="table_layout">
	<tr>
		<td width="90%" rowspan="2"><h4>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] REQUEST DEMO SUMMARY by item</h4></td>
		<td><div class="divOrderBy"> ORDER BY </div></td>
        <td>CATEGORY</td>
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
        <td>	
			<input type="hidden" name="lastCategoryNo" value="0">
			<select name="icat_1" onChange="fillOption(window.document.frmSearch.icat_2, this.value)" onClick="resetOption()">
				<option value="0">==ALL==</option>
			</select>
			<select name="icat_2">
				<option value="0">==ALL==</option>
			</select>
		</td>
	</tr>
</table>
<table width="100%" class="table_layout">
	<tr>
		<td width="55%"></td>
		<td> CUS TO </td>
		<td> STATUS </td>
		<td> DOCUMENT DATE </td>
		<td> DOCUMENT PERIOD </td>
	</tr>
	<tr>
		<td></td>
		<td>
			<input type="checkbox" name="isFilledCus" onClick="fillCustomer('', this.checked)"<?php echo ($_cus_code!='') ? ' checked' : ''?>>
			<input type="text" name="_cus_code" size="8" class="fmt" value="<?php echo $_cus_code?>" readonly>
			<a href="javascript:changeCustomer()"><img src="../../_images/icon/go.png"></a>&nbsp; &nbsp;
		</td>
		<td>
			<select name="cboStatus">
				<option value="all">==ALL==</option>
				<option value="uncfm">UNCONFIRM</option>
				<option value="cfm">CONFIRM</option>
			</select>
		</td>
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
<table width="100%" class="table_box">
	<tr>
		<td align="right">
			<a href="./daily_balance_demo_by_reference.php?<?php echo getQueryString()?>">View By Reference</a>
		</td>
	</tr>
</table>
<?php require_once APP_DIR . "_include/request_demo/report/rpt_daily_balance_demo_by_item.php" ?>
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	var ts = <?php echo time() * 1000;?>;

	setSelect(f.cboFilterOrderBy, "<?php echo isset($_GET['cboFilterOrderBy']) ? $_GET['cboFilterOrderBy'] : $cboFilter[1][ZKP_FUNCTION][0][0] ?>");
	setSelect(f.cboStatus, "<?php echo isset($_GET['cboStatus']) ? $_GET['cboStatus'] : "all"?>");

	var last_category = 0;
<?php 
if (isset($_GET['icat_2']) && $_GET['icat_2'] != 0)
	echo "\tlast_category = {$_GET['icat_2']};\n";
else if(isset($_GET['icat_1']) && $_GET['icat_1'] != 0)
	echo "\tlast_category = {$_GET['icat_1']};\n";
?>

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
		f.lastCategoryNo.value = last_category;
		f.submit();
	}

	f.cboStatus.onchange = function() {
		if(f.some_date.value != '') {
			f.period_from.value = '';
			f.period_to.value = '';
			f.s_mode.value = 'date';
		} else {
			f.some_date.value = '';
			f.s_mode.value = 'period';
		}
		f.lastCategoryNo.value = last_category;
		f.submit();
	}
	f.cboFilterOrderBy.onchange		= f.cboStatus.onchange;
	
	f.some_date.onkeypress = function() {
		if(window.event.keyCode == 13 && validDate(f.some_date)) {
			f.s_mode.value = 'date';
			f.lastCategoryNo.value = last_category;
			f.submit();
		}
	}

	f.period_from.onkeypress = function() {
		if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
			f.s_mode.value = 'period';
			f.lastCategoryNo.value = last_category;
			f.submit();
		}
	}

	f.period_to.onkeypress = function() {
		if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
			f.s_mode.value = 'period';
			f.lastCategoryNo.value = last_category;
			f.submit();
		}
	}
	
	f.icat_1.onchange	  = function() {
		if(f.some_date.value != '') {
			f.period_from.value = '';
			f.period_to.value = '';
			f.s_mode.value = 'date';
		} else {
			f.some_date.value = '';
			f.s_mode.value = 'period';
		}
		f.lastCategoryNo.value = this.value;
		f.submit();
	}

	f.icat_2.onchange  = f.icat_1.onchange;
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