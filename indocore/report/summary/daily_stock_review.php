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
require_once LIB_DIR . "paginator.class.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc	= "daily_stock_review.php";
$txtRow		= isset($_GET['txtRow'])? $_GET['txtRow'] : 25;
$show_null	= isset($_GET['show_null'])? $_GET['show_null'] : "false";
$searchBy	= isset($_GET['searchBy'])? $_GET['searchBy'] : "";
$txtKeyword = isset($_GET['txtKeyword'])? $_GET['txtKeyword'] : "";
$_last_category = (empty($_GET['lastCategoryNo'])) ? 0 : $_GET['lastCategoryNo'];

if($_last_category != 0) {
	if(isZKError($path = executeSP(ZKP_SQL."_getCategoryPath", $_last_category))) {
		$M->goErrorPage($path, HTTP_DIR . "$currentDept/$moduleDept/index.php");
	} else {
		eval(html_entity_decode($path[0]));	
		$path = array_reverse($path);
	}
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
<table width="100%">
	<tr>
		<td valign="top">
			<h4>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] STOCK QUANTITY REVIEW</h4>
		</td>
		<td>
<form name="frmSearch" method="GET">
<input type="hidden" name="lastCategoryNo" value="0">
<input type="hidden" name="show_null" value="<?php echo $show_null ?>">
<table width="100%" class="table_box">
	<tr height="35px">
		<td align="right" colspan="2">
		  Category:
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
	<tr>
		<td align="right" colspan="2">
			Search: 
			<select name="searchBy">
				<option value="it.it_code">CODE</option>
				<option value="it.it_model_no" <?php echo (isset($_GET['searchBy']) && $_GET['searchBy'] == "model_no") ? "selected":""?>>MODEL</option>
				<option value="it.it_desc" <?php echo (isset($_GET['searchBy']) && $_GET['searchBy'] == "desc") ? "selected":""?>>DESCRIPTION</option>
			</select>&nbsp;
			<input type="text" name="txtKeyword" size="15" class="fmt" value="<?php echo isset($_GET['txtKeyword']) ? $_GET['txtKeyword'] : ""?>">
		</td>
	</tr>
    <tr>
    	<td align="right">
			Row : <input type="text" name="txtRow" size="3" class="fmtn" value="<?php echo $txtRow ?>">
            <!--<input type="checkbox" name="chkNull"<?php  echo ($show_null=='true') ? " checked":"" ?>> Include 0 qty-->
		</td>
		<th width="10%">
			<a href="javascript:searchItem()"><img src="../../_images/icon/search_mini.gif" alt="Search"></a>&nbsp;
			<a href="javascript:document.location.href='stock_qty_review.php?_location=<?php echo $_location ?>'"><img src="../../_images/icon/list_mini.gif" alt="Show all item"></a>&nbsp;
		</th>
    </tr>
</table>
</form>
		</td>
	</tr>
</table><br />
<?php include "./report/rpt_daily_stock_review.php"; ?>
<script language="javascript" type="text/javascript">
function searchItem() {
	var o = window.document.frmSearch.all.tags('SELECT');
	var icat_midx = 0;
	for (i=2; i>=0; i--) {
		if (o[i].value != 0) { icat_midx = o[i].value; break; }
	}

/*	if(document.frmSearch.chkNull.checked == false) {
		document.frmSearch.show_null.value = false;
	}*/
	document.frmSearch.lastCategoryNo.value = icat_midx;
	document.frmSearch.submit();
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