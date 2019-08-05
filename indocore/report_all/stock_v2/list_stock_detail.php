<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim
*/

//REQUIRE
require_once "../../zk_config.php";

//DEFAULT VARIABLE
$left_loc	= "list_stock_detail.php";
$_type		= isset($_GET['cboType']) ? $_GET['cboType'] : "all";
$_kurs		= isset($_GET['_kurs']) ? $_GET['_kurs'] : "9200";
$period_from	= isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", mktime(0,0,0,date("m"),1, date("Y")));
$period_to	= isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", mktime(0,0,0,date("m")+1,1, date("Y"))-1);
$_location	= isset($_GET['_location'])? $_GET['_location'] : 0;

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
<script language="text/javascript" type="text/javascript" src="../../_script/js_category.php"></script>
<script src="../../_script/jquery1.7.1.min.js" type="text/javascript"></script>
<script src="../../_script/chosen.jquery.js" type="text/javascript"></script>
<link href="../../_script/chosen.css" rel="stylesheet" type="text/css">
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
		if($i<=2) echo "\tfillOption(window.document.frmSearch.icat_".($i+1).", \"{$path[$i][0]}\");\n";
	}
}
?>
}

function resetOption() {
	window.document.frmSearch.icat_3.options.length = 1;
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
			<td style="padding:10" height="870" valign="top">
		<!--START: BODY-->
<form name="frmSearch" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
<table width="100%" class="table_box">
	<tr>
		<td rowspan="2"><h4>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] DETAIL STOCK</h4></td>
	</tr>
	<tr>
		<td colspan="4" align="right">	
			<input type="hidden" name="lastCategoryNo" value="0">
			<select name="icat_1" onChange="fillOption(window.document.frmSearch.icat_2, this.value)" onClick="resetOption()">
				<option value="0">==ALL==</option>
			</select>&nbsp;
			<select name="icat_2" onChange="fillOption(window.document.frmSearch.icat_3, this.value)">
				<option value="0">==ALL==</option>
			</select>&nbsp;
			<select name="icat_3">
				<option value="0">==ALL==</option>
			</select>
		</td>
	</tr>
	<tr>	
		<td width="60%"></td>
		<td>WAREHOUSE</td>
		<td>PERIODE</td>		
		<td>TYPE</td>
		<td>KURS</td>
	</tr>
	<tr>
		<td></td>
		<td>
			<select name="_location">
				<option value="0">MED &amp; SMD</option>
				<option value="1">MEDISINDO</option>
				<option value="2">SAMUDIA</option>
			</select>
		</td>
		<td>
			<a href="javascript:setFilterDate('period',-1)"> <img src="../../_images/icon/arrow_left.gif" alt="Previous month"> </a>
			<input type="text" name="period_from" size="12" class="fmtd" value="<?php echo $period_from; ?>">&nbsp;
			<input type="text" name="period_to" size="12" class="fmtd"  value="<?php echo $period_to; ?>">
			<a href="javascript:setFilterDate('period',1)"> <img src="../../_images/icon/arrow_right.gif" alt="Next month"> </a>
		</td>
		<td>
			<select name="cboType">
				<option value="all">==ALL==</option>
				<option value="1">NORMAL</option>
				<option value="2">DTD</option>
			</select>
		</td>
		<td><input type="text" name="_kurs" size="12" class="fmtn"  value="<?php echo $_kurs; ?>" onkeyup="formatNumber(this, 'dot')"></td>
	</tr>
</table><br /><br />
<?php require_once APP_DIR . "_include/purchasing/report/stock_v2/rpt_list_stock_detail.php" ?><br />
<p style="text-align: right; font-style: italic">Generate at <?php echo date('d-M-Y H:i:s')?></p>
</form>
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	var ts = <?php echo time() * 1000;?>;

	var last_category = 0;
<?php 
if (isset($_GET['icat_2']) && $_GET['icat_2'] != 0)
	echo "\tlast_category = {$_GET['icat_2']};\n";
else if(isset($_GET['icat_1']) && $_GET['icat_1'] != 0)
	echo "\tlast_category = {$_GET['icat_1']};\n";
?>

	function setFilterDate(status, value){
		var d = new Date(ts);
		setFilterPeriodCalc(d, value, f.period_from, f.period_to);
		f.lastCategoryNo.value = last_category;
		f.submit();
	}

	setSelect(f.cboType, "<?php echo isset($_GET['cboType']) ? $_GET['cboType'] : "all"?>");
	setSelect(f._location, "<?php echo isset($_GET['_location']) ? $_GET['_location'] : "0"?>");

	f.cboType.onchange = function() {
		f.lastCategoryNo.value = last_category;
		f.submit();
	}
	f._kurs.onkeypress = function() {
		if(window.event.keyCode == 13) {
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
	
	f.icat_1.onchange = function() {
		f.lastCategoryNo.value = this.value;
		f.submit();
	}

	f.icat_2.onchange  = f.icat_1.onchange;
	f.icat_3.onchange  = f.icat_1.onchange;
	f._location.onchange = f.icat_1.onchange; 
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