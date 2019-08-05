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
$left_loc	= "summary_monthly_sales_by_item.php";

$month		= array(1=>'January','February','March','April','May','June','July','August','September','October','November','December');
$month_x	= array(0=>'January','February','March','April','May','June','July','August','September','October','November','December');
$current_year = date('Y');
$year_from	= array();
	for($i=$current_year; $i>$current_year-10 ; $i--) {
		$year_from[$i] = $i;
	}
$year_to	= array();
	for($i=$current_year+1; $i>$current_year-10 ; $i--) {
		$year_to[$i] = $i;
	}
$period_default = array();
	$period_default[0] = date('n');//(date('n')==12) ? 1 : date('n')+1;
	$period_default[1] = date('Y');//(date('n')<12) ? date('Y')-1 : date('Y');
	$period_default[2] = date('n');
	$period_default[3] = date('Y');
$remark = "*) Based on Sales Report, Billing Issue Item, Return Item";

$_cus_code		= isset($_GET['_cus_code']) ? $_GET['_cus_code'] : "";
$_cug_code		= isset($_GET['_cug_code']) ? $_GET['_cug_code'] : "";
$_order_by		= isset($_GET['cboFilterOrder']) ? $_GET['cboFilterOrder'] : $cboFilter[1][ZKP_FUNCTION][0][0];
$_filter_doc	= isset($_GET['cboFilterDoc']) ? $_GET['cboFilterDoc'] : "all";
$_month_from	= isset($_GET['cboMonthFrom']) ? $_GET['cboMonthFrom'] : $period_default[0];
$_year_from		= isset($_GET['cboYearFrom']) ? $_GET['cboYearFrom'] : $period_default[1];
$_month_to		= isset($_GET['cboMonthTo']) ? $_GET['cboMonthTo'] : $period_default[2];
$_year_to		= isset($_GET['cboYearTo']) ? $_GET['cboYearTo'] : $period_default[3];

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
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script src="../../_script/jQuery.js" type="text/javascript"></script>
<script language="text/javascript" type="text/javascript" src="../../_script/js_category.php"></script>
<script language='text/javascript' type='text/javascript'>
function changeCustomer(code) {
	if(window.document.frmSearch.isFilledCus.checked) {
		fillCustomer(code, window.document.frmSearch.isFilledCus.checked);
	}
}

function fillCustomer(code, is_checked) {
	if(is_checked) {
		var x = (screen.availWidth - 400) / 2;
		var y = (screen.availHeight - 600) / 2;
		var dept = window.document.frmSearch._dept.value;

		var win = window.open(
			'../../_include/billing/p_list_cus_code.php?_dept='+dept+'&_check_code='+ code,
			'summary_monthly',
			'scrollbars,width=400,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	} else {
		var f = window.document.frmSearch;
		f._cus_code.value = '';	
		f.submit();
	}
}

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
		if($i<=2) echo "\tfillOption(window.document.frmSearch.icat_".($i+1).", \"{$path[$i][0]}\");\n";
	}
}
?>
}

function resetOption() {
	window.document.frmSearch.icat_2.options.length = 1;
	window.document.frmSearch.lastCategoryNo.value = "";
}

$(document).ready(function(){
	var url		= $("input[name$=web_url]").val();
	var dept	= $("input[name$=web_dept]").val();

	var ishideFilterOrderBy	= new Array("IDC","MED","MEP");
	var ishideFilterGroupBy	= new Array("dealer","hospital","maketing", "pharmaceutical", "tender");

	if(in_array(url, ishideFilterOrderBy)) $(".divOrderBy").hide();
	if(in_array(dept, ishideFilterGroupBy)) $(".divGroupBy").hide();

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
<form name="frmSearch" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
<input type="hidden" name="web_url" value="<?php echo ZKP_URL ?>">
<input type="hidden" name="web_dept" value="<?php echo $currentDept ?>">
<input type='hidden' name='_dept' value='<?php echo $department ?>'>
<table width="100%" class="table_layout">
	<tr>
		<td width="90%" rowspan="2"><h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] MONTHLY SALES SUMMARY by item</h3></td>
		<td> SHIP TO </td>
		<td><div class="divOrderBy"> ORDER BY </div></td>
		<td><div class="divGroupBy"> PUSAT </div></td>
	</tr>
		<td>
			<input type="checkbox" name="isFilledCus" onClick="fillCustomer(window.document.frmSearch._cus_code.value, this.checked)"<?php echo ($_cus_code!='') ? ' checked' : ''?>>
			<input type="text" name="_cus_code" size="10" class="fmt" value="<?php echo $_cus_code?>" readonly>
			<a href="javascript:changeCustomer(window.document.frmSearch._cus_code.value)"><img src="../../_images/icon/go.png"></a>&nbsp; &nbsp;
		</td>
		<td><div class="divOrderBy">
			<select name="cboFilterOrder" onChange="submitSearch()">
<?php
foreach($cboFilter[1][ZKP_FUNCTION] as $val => $key) {
	echo "\t\t\t\t<option value=\"".$key[0]."\">".$key[1]."</option>\n";
}
?>
			</select></div>
		</td>
		<td><div class="divGroupBy">
<?php
$sql = "SELECT cug_code, substr(cug_name, 1, 15) || '...' AS cus_name FROM ".ZKP_SQL."_tb_customer_group ORDER BY cug_name";
isZKError($result = & query($sql)) ? $M->printMessage($result):0;

if(numQueryRows($result) <= 0) {
	$o = new ZKError("INFORMATION", "INFORMATION", "Please register the $arg first. you can find the [new ". ucfirst($arg) ."] under the BASIC DATA menu");
	$M->printMessage($result);
} else {
	print "<select name=\"_cug_code\" class=\"req\" onchange=\"submitSearch()\">\n";
	print "\t<option value=\"\">==SELECT==</option>\n";

	while ($columns = fetchRow($result)) {
		print "\t<option value=\"".$columns[0]."\">".$columns[1]."</option>\n";
	}
	print "</select>\n";
}
?>
		</div></td>
	<tr>
</table><br />
<table width="100%" class="table_layout">
	<tr>
		<td width="40%"></td>
		<td> CATEGORY </td>
		<td> FILTER BY </td>
		<td> PERIOD FROM </td>
		<td> PERIOD TO </td>
		<td bgcolor="#F3F3F3" width="5%" align="center" rowspan="2" style="background-color:#00000">
			<a href="javascript:checkValidityPeriod()"><img src="../../_images/icon/search_mini.gif"></a>
		</td>
	</tr>
	<tr>
		<td> </td>
		<td>
			<input type="hidden" name="lastCategoryNo" value="0">
			<select name="icat_1" onChange="fillOption(window.document.frmSearch.icat_2, this.value)" onClick="resetOption()">
				<option value="0">==ALL==</option>
			</select>
			<select name="icat_2" onChange="fillOption(window.document.frmSearch.icat_3, this.value)">
				<option value="0">==ALL==</option>
			</select>
			<select name="icat_3">
				<option value="0">==ALL==</option>
			</select> &nbsp;
		</td>
		<td>
			<select name="cboFilterDoc" onChange="submitSearch()">
				<option value="all">==ALL==</option>
				<option value="sales">Sales</option>
				<option value="return">Return</option>
			</select>
		</td>
		<td>
			<select name="cboMonthFrom">
<?php foreach($month as $value => $key) { 
echo "\t\t\t\t<option value=\"$value\">$key</option>\n";
}?>
			</select> 
			<select name="cboYearFrom">
<?php foreach($year_from as $value => $key) { 
echo "\t\t\t\t<option value=\"$value\">$key</option>\n";
}?>
			</select> &nbsp;
		</td>
		<td>
			<select name="cboMonthTo">
<?php foreach($month as $value => $key) { 
echo "\t\t\t\t<option value=\"$value\">$key</option>\n";
}?>
			</select> 
			<select name="cboYearTo">
<?php foreach($year_to as $value => $key) { 
echo "\t\t\t\t<option value=\"$value\">$key</option>\n";
}?>
			</select>
		</td>
	</tr>
</table>
<div align="right"><span class="comment"><i><?php echo $remark ?></i></span></div>
<br />
</form>
<iframe id="rightFrame" src="i_summary_monthly_sales_by_item.php?<?php echo getQueryString()?>" frameborder="0" width="100%" height="500px" name="iFrm"></iframe><br /><br />
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	var ts = <?php echo time() * 1000;?>;

	setSelect(f._cug_code, "<?php echo isset($_GET['_cug_code']) ? $_GET['_cug_code'] : ""?>");
	setSelect(f.cboFilterOrder, "<?php echo isset($_GET['cboFilterOrder']) ? $_GET['cboFilterOrder'] : $cboFilter[1][ZKP_FUNCTION][0][0]?>");
	setSelect(f.cboFilterDoc, "<?php echo isset($_GET['cboFilterDoc']) ? $_GET['cboFilterDoc'] : "all"?>");
	setSelect(f.cboMonthFrom, "<?php echo isset($_GET['cboMonthFrom']) ? $_GET['cboMonthFrom'] : $period_default[0] ?>");
	setSelect(f.cboYearFrom, "<?php echo isset($_GET['cboYearFrom']) ? $_GET['cboYearFrom'] : $period_default[1] ?>");
	setSelect(f.cboMonthTo, "<?php echo isset($_GET['cboMonthTo']) ? $_GET['cboMonthTo'] : $period_default[2] ?>");
	setSelect(f.cboYearTo, "<?php echo isset($_GET['cboYearTo']) ? $_GET['cboYearTo'] : $period_default[3] ?>");

	var last_category = 0;
<?php 
if (isset($_GET['icat_3']) && $_GET['icat_3'] != 0)
	echo "\tlast_category = {$_GET['icat_3']};\n";
else if(isset($_GET['icat_2']) && $_GET['icat_2'] != 0)
	echo "\tlast_category = {$_GET['icat_2']};\n";
else if(isset($_GET['icat_1']) && $_GET['icat_1'] != 0)
	echo "\tlast_category = {$_GET['icat_1']};\n";
?>

	function submitSearch() {
		if(f._cug_code.value != '' && f._cus_code.value != '') {
			f._cus_code.value = '';
		}
		f.lastCategoryNo.value = last_category;
		f.submit();
	}

	f.icat_1.onchange	  = function() {
		f.lastCategoryNo.value = this.value;
		f.submit();
	}
	f.icat_2.onchange  = f.icat_1.onchange;
	f.icat_3.onchange  = f.icat_1.onchange;

	function checkValidityPeriod() {
		var f = window.document.frmSearch;

		var month_from	= parseInt(f.cboMonthFrom.value);
		var year_from	= parseInt(f.cboYearFrom.value);
		var month_to	= parseInt(f.cboMonthTo.value);
		var year_to		= parseInt(f.cboYearTo.value);

		//Check validity From - To
		var d1 = parseDate('1-'+month_from+'-'+year_from, 'prefer_euro_format');
		var d2 = parseDate('1-'+month_to+'-'+year_to, 'prefer_euro_format');
		if (d1.getTime() > d2.getTime()) {
			alert("Period to must be future than Period from");
			return;
		}

		//Check max month
		var count_month	= 0;
		var start_mon	= 0;
		var end_month	= 0;
		var con	= '';
		for (var i=year_from; i<=year_to; i++) {

			if(year_from==year_to)	{con=1; start_mon=month_from; end_month=month_to;}
			else if(i==year_from)	{con=2; start_mon=month_from; end_month=12;}
			else if(i==year_to)		{con=3; start_mon=1; end_month=month_to;}
			else			 		{con=4; start_mon=1; end_month=12;}

			for(var j=start_mon; j<=end_month; j++) {
				count_month++;
			}
		}
		if(count_month>12) {
			alert("Your selected period more than 12 months");
			return;
		}
		submitSearch();
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