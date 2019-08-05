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
$left_loc = "daily_return_by_group.php";

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

function fillOptionInit() {
	fillOption(window.document.frmSearch.icat_1, 0);
<?php
//Set initial option value
if(isset($path) && is_array($path)) {
	$count = count($path);
	for($i = 1; $i < $count; $i++) {
		echo "\twindow.document.frmSearch.icat_$i.value = \"{$path[$i][0]}\";\n";
		if($i ==1 ) echo "\tfillOption(window.document.frmSearch.icat_".($i+1).", \"{$path[$i][0]}\");\n";
	}
}
?>
}

function resetOption() {
	window.document.frmSearch.icat_2.options.length = 1;
	window.document.frmSearch.lastCategoryNo.value = "";
}
</script>
</head>
<body topmargin="0" leftmargin="0" onLoad="fillOptionInit()">
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
$s_mode 		= isset($_GET['s_mode']) ? $_GET['s_mode'] : "period";
$_filter_date	= isset($_GET['cboDateBy']) ? $_GET['cboDateBy'] : "turn_date";
$_cug_code		= isset($_GET['_cug_code']) ? $_GET['_cug_code'] : "all";
$_dept			= isset($_GET['cboDept']) ? $_GET['cboDept'] : "all";
$_vat			= isset($_GET['cboVat']) ? $_GET['cboVat'] : "all";
$_status		= isset($_GET['cboStatus']) ? $_GET['cboStatus'] : "all";
$_source		= isset($_GET['cboSource']) ? $_GET['cboSource'] : "all";
$_order_by		= isset($_GET['cboOrderBy']) ? $_GET['cboOrderBy'] : "all";

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
		<td rowspan="2" width="90%" valign="top"><strong class="title">[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] RETURN SUMMARY by group</strong></td>
		<td> ORDER BY </td>
		<td>PUSAT</td>
        <td> CATEGORY </td>         
	</tr>
	<tr>
		<td>
			<select name="cboOrderBy">
				<option value="all">==ALL==</option>
<?php
foreach($cboFilter[3]['do'][ZKP_SQL] as $val => $key) {
	echo "\t\t\t\t<option value=\"".$key[0]."\">".$key[1]."</option>\n";
}
?>
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
		<td width="20%"></td>
		<td>DEPT</td>
		<td> TYPE </td>
		<td>SOURCE</td>
		<td>STATUS</td>
		<td>SORT DATE BY</td>
		<td>DATE</td>
		<td>PERIOD</td>
	</tr>
	<tr>
		<td></td>
		<td>
			<select name="cboDept">
				<option value="all">==ALL==</option>
				<option value="A">A</option>
				<option value="D">D</option>
				<option value="H">H</option>
				<option value="M">M</option>
				<option value="P">P</option>
				<option value="T">T</option>
				<option value="S">CS</option>
				<option value="DEMO">DEMO</option>
			</select>
		</td>
		<td>
			<select name="cboVat">
				<option value="all">==ALL==</option>
				<option value="1">VAT</option>
				<option value="2">NON VAT</option>
			</select>
		<td>
			<select name="cboSource">
				<option value="all">==ALL==</option>
				<option value="1">Return Billing</option>
				<option value="2">Return Order</option>
				<option value="3">Return DT</option>
			</select>
		</td>
		<td>
			<select name="cboStatus">
				<option value="all">==ALL==</option>
				<option value="0">Unconfirm</option>
				<option value="1">Confirm</option>
			</select>
		</td>
		<td>
			<select name="cboDateBy">
				<option value="turn_date">RETURN DATE</option>
				<option value="cfm_date">CONFIRM DATE</option>
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
			<a href="./daily_return_by_item.php?<?php echo getQueryString()?>">View By Item</a>
		</td>
	</tr>
</table><br />
<?php require_once APP_DIR . "_include/warehouse/report/delivery/rpt_daily_return_by_group.php" ?>
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	var ts = <?php echo time() * 1000;?>;

	setSelect(f.cboDateBy, "<?php echo isset($_GET['cboDateBy']) ? $_GET['cboDateBy'] : "turn_date"?>");
	setSelect(f._cug_code, "<?php echo isset($_GET['_cug_code']) ? $_GET['_cug_code'] : "all"?>");
	setSelect(f.cboDept, "<?php echo isset($_GET['cboDept']) ? $_GET['cboDept'] : "all"?>");
	setSelect(f.cboVat, "<?php echo isset($_GET['cboVat']) ? $_GET['cboVat'] : "all"?>");
	setSelect(f.cboStatus, "<?php echo isset($_GET['cboStatus']) ? $_GET['cboStatus'] : "all"?>");
	setSelect(f.cboSource, "<?php echo isset($_GET['cboSource']) ? $_GET['cboSource'] : "all"?>");
	setSelect(f.cboOrderBy, "<?php echo isset($_GET['cboOrderBy']) ? $_GET['cboOrderBy'] : "all"?>");

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

	f._cug_code.onchange = function() {
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

	f.cboDateBy.onchange	= f._cug_code.onchange;
	f.cboDept.onchange		= f._cug_code.onchange;
	f.cboVat.onchange		= f._cug_code.onchange;
	f.cboSource.onchange	= f._cug_code.onchange;
	f.cboStatus.onchange	= f._cug_code.onchange;
	f.cboOrderBy.onchange	= f._cug_code.onchange;

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
			'./p_summary_return.php?_code='+ keyword,
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