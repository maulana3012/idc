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
$left_loc	= "list_ed_demo.php";

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

//=============================================================================================== move stock
if(ckperm(ZKP_DELETE,  HTTP_DIR . "$currentDept/$moduleDept/index.php", 'delete')) {

	$_it_code 	= $_POST['_it_code'];
	$_it_date 	= $_POST['_expired_date'];
	$_it_desc 	= 'Expired';
	$_it_qty 	= $_POST['_it_qty'];
	$_log_by_account	= $S->getValue("ma_account");

	$result = executeSP(
		ZKP_SQL."_deleteDemoStock",
		"$\${$_it_code}$\$",
		"$\${$_it_date}$\$",
		"$\${$_it_desc}$\$",
		$_it_qty,
		"$\${$_log_by_account}$\$"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/list_ed_demo.php");
	}
	goPage(HTTP_DIR . "$currentDept/$moduleDept/list_ed_demo.php");
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
function deleteItem(it_code, it_date, qty) {
	var f = window.document.frmDeleteStock;

	f._it_code.value		= it_code;
	f._expired_date.value	= it_date;
	f._it_qty.value			= qty;
	var d			= parseDate(f._expired_date.value, 'prefer_euro_format');
	var date		= formatDate(d, 'NNN-yyyy');

	if(confirm("Are you sure to delete "+ it_code + " with E/D " + date + " from the system ?\n\n"+
			   "*Deleting from system will cause demo stock in marketing decrease")) {
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
	window.document.frmSearch.icat_3.options.length = 1;
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
<form name="frmSearch" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
<table width="100%" class="table_layout">
	<tr>
		<td width="50%"><h4>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] LIST E/D DEMO</h4></td>
		<td align="right">
			Category : 
			<input type="hidden" name="lastCategoryNo" value="0">
			<select name="icat_1" onChange="fillOption(window.document.frmSearch.icat_2, this.value)" onClick="resetOption()">
				<option value="0">==ALL==</option>
			</select>&nbsp;
			<select name="icat_2" onChange="fillOption(window.document.frmSearch.icat_3, this.value)">
				<option value="0">==ALL==</option>
			</select>&nbsp;
			<select name="icat_3">
				<option value="0">==ALL==</option>
			</select> &nbsp;
		</td>
	</tr>
</table><br />
</form>
<?php require_once APP_DIR . "_include/demo/report/rpt_summary_ed_demo.php" ?>
<form name="frmDeleteStock" method="post">
<input type="hidden" name="p_mode" value="delete">
<input type="hidden" name="_it_code" class="req">
<input type="hidden" name="_expired_date" class="reqd">
<input type="hidden" name="_it_qty" class="reqn">
</form>
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	var last_category = 0;
<?php 
if (isset($_GET['icat_3']) && $_GET['icat_3'] != 0)
	echo "\tlast_category = {$_GET['icat_3']};\n";
else if (isset($_GET['icat_2']) && $_GET['icat_2'] != 0)
	echo "\tlast_category = {$_GET['icat_2']};\n";
else if(isset($_GET['icat_1']) && $_GET['icat_1'] != 0)
	echo "\tlast_category = {$_GET['icat_1']};\n";
?>

	f.icat_1.onchange	  = function() {
		f.lastCategoryNo.value	= this.value;
		f.submit();
	}

	f.icat_2.onchange  = f.icat_1.onchange;
	f.icat_3.onchange  = f.icat_1.onchange;
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