<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*/

//REQUIRE
require_once "../../zk_config.php";
require_once "../../_system/util_html.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, MAIN_PAGE);

//GLOBAL
$left_loc	= "search_apotik_price_by_item.php";

$strWhere = array();

//Search Parameter : set period
$_period_from = (isset($_GET['_period_from']) && $_GET['_period_from'] != '') ? $_GET['_period_from'] : date("d-M-Y",time()-2419200);
$_period_to = (isset($_GET['_period_to']) && $_GET['_period_to'] != '') ? $_GET['_period_to'] : date("d-M-Y",time()+2419200);
$strWhere[] = "(ap_date_from, ap_date_to+1) OVERLAPS (DATE '$_period_from', DATE '$_period_to'+1)";

// Search Parameter : set customer group code
if (isset($_GET['_cug_code']) && $_GET['_cug_code'] != 'all') {
	$_cug_code = $_GET['_cug_code'];
	$strWhere[] = "cug_code = '".$_cug_code."'";
} else {
	$_cug_code = "all";
}

//Search parameter : set category
if(isset($_GET['_lastCategoryNo']) && $_GET['_lastCategoryNo'] > 0) {
	$_lastCategoryNo = $_GET['_lastCategoryNo'];
	
	//get all the sub icat_midx value from stored procedure.
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_lastCategoryNo);
	$strWhere[] = "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";

	//get category path from current icat_midx.
	isZKError($path = executeSP(ZKP_SQL."_getCategoryPath", $_lastCategoryNo)) ? $M->printMessage($path) : true;
	eval(html_entity_decode($path[0]));	
	$path = array_reverse($path);
}

// Query will be continue at report.
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
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
}


function fillOptionInit() {
	fillOption(window.document.frmSearch.icat_1, 0);
<?php
//Set initial option value
if(isset($path) && is_array($path)) {
	$count = count($path);
	for($i = 1; $i < $count; $i++) {
		echo "\twindow.document.frmSearch.icat_$i.value = \"{$path[$i][0]}\";\n";
		if($i<=2) echo "\tfillOption(window.document.frmSearch.icat_".($i+1).", \"{$path[$i][0]}\");\n";
	}
}
?>
}

function resetOption() {
	window.document.frmSearch.icat_3.options.length = 1;
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
<h4>ADDITIONAL DISCOUNT ITEM LIST</h4>
<div>
<form name="frmSearch" method="get" action="search_apotik_price_by_item.php">
	<table width="100%" class="table_box">
	<tr>
		<th>PERIOD</th>
		<td>
			FROM : <input name="_period_from" class="fmtd" size="10" value="<?php echo $_period_from?>"> &nbsp;
			TO : <input name="_period_to" class="fmtd" size="10" value="<?php echo $_period_to?>">
		</td>
		<th>GROUP</th>
		<td width="45%">
<?php
	$sql = "SELECT cug_code, cug_name, cug_basic_disc_pct FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code in (SELECT cug_code FROM ".ZKP_SQL."_tb_customer WHERE cus_channel='002') ORDER BY cug_name";
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
	</tr>
	<tr>
		<th>ITEM</th>
		<td colspan="3">
			<input type="hidden" name="_lastCategoryNo" value="0">
			<select name="icat_1" onChange="fillOption(window.document.frmSearch.icat_2, this.value)" onClick="resetOption()">
				<option value="0">==ALL==</option>
			</select>&nbsp;
			<select name="icat_2" onChange="fillOption(window.document.frmSearch.icat_3, this.value)">
				<option value="0">==ALL==</option >
			</select>&nbsp;
			<select name="icat_3">
				<option value="0">==ALL==</option>
			</select> &nbsp;
			<button name="btnSearch" class="input_sky" style="height:19px">SEARCH</button>
		</td>
	</tr>
</table><br />
</form>
<span class="comment">*Item list only show apotik policy with selected item</span>
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;

	setSelect(f._cug_code, <?php echo "'".$_cug_code."'"?>);
	f.btnSearch.onclick = function() {

		//set LastCategory Np
		var o = f.elements;
		for (var i = 6; i >=4; i--) { // From end element to 3ea
			if (o[i].value != 0) {
				f._lastCategoryNo.value = o[i].value;
				break;
			}
		}
		
		var d1 = parseDate(f._period_from.value, 'prefer_euro_format');
		var d2 = parseDate(f._period_to.value, 'prefer_euro_format');
		
		if(d1 == null || d2 == null) {
			alert("Please input correct date");
			f._period_from.value = '';
			f._period_to.value = '';
			f._period_from.focus();
			return;
		} else if (d1.getTime() > d2.getTime()) {
			alert("TO date is more earlier than FROM date");
			f._period_from.value = '';
			f._period_to.value = '';
			f._period_from.focus();
			return;
		}

		f._period_from.value = formatDate(d1, 'dd-NNN-yyyy');
		f._period_to.value = formatDate(d2, 'dd-NNN-yyyy');
		f.submit();
	}
</script>
</div>
<?php require_once APP_DIR . "_include/order/report/rpt_price_policy_additional_disc_item.php" ?>
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