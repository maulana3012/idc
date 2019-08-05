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
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc	= "list_quantity_level.php";
$strGet 	= "";
$_status	= isset($_GET['cboFilterStatus'])? $_GET['cboFilterStatus'] : "all";

//========================================================================================== update permission
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'update')) {

	$_it_code = array();
	$_it_critical_qty = array();

	foreach ($_POST['chkItem'] as $val) {
		$_it_code[] = $val;
		$_it_critical_qty[] = $_POST['_it_critical_'.trim($val).'_qty'];
	}

	$_it_code = '$$' . implode('$$,$$', $_it_code) . '$$';
	$_it_critical_qty = implode(',', $_it_critical_qty);

	$result = executeSP(
		ZKP_SQL."_updateCriticalItem",
		"ARRAY[$_it_code]",
		"ARRAY[$_it_critical_qty]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/list_quantity_level.php");
	} else {
		$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/list_quantity_level.php");
	}
}

//========================================================================================== DEFAULT PROCESS
$sqlQuery = new strSelect(
"SELECT 
  it_code,
  it_model_no,
  it_desc,
  it_critical_stock,
  (SELECT sum(stk_qty) FROM ".ZKP_SQL."_tb_stock_v2 WHERE it_code=it.it_code) AS it_available_stock,
  (SELECT sum(stk_qty) FROM ".ZKP_SQL."_tb_stock_v2 WHERE it_code=it.it_code) - it_critical_stock AS it_diff,
  CASE
	WHEN (SELECT sum(stk_qty) FROM ".ZKP_SQL."_tb_stock_v2 WHERE it_code=it.it_code) - it_critical_stock > 0 THEN 'green'
	ELSE 'red'
  END AS it_color
FROM ".ZKP_SQL."_tb_item AS it
");

if($_status == 'all') {
	$sqlQuery->whereCaluse = "it_status = 0";
} else if($_status == 'available') {
	$sqlQuery->whereCaluse = "it_status = 0 AND ".ZKP_SQL."_statusQtyLevel(it_code) is true";
} else if($_status == 'critical') {
	$sqlQuery->whereCaluse = "it_status = 0 AND ".ZKP_SQL."_statusQtyLevel(it_code) is false";
}

//Search Option 1 : by Category
if(isset($_GET['lastCategoryNo']) && $_GET['lastCategoryNo'] > 0) {
	$_lastCategoryNo = $_GET['lastCategoryNo'];

	//get all the sub icat_midx value from stored procedure.
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_lastCategoryNo);
	$sqlQuery->whereCaluse = "it_status = 0 AND icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";

	//get category path from current icat_midx.
	if(isZKError($path = executeSP(ZKP_SQL."_getCategoryPath", $_lastCategoryNo))) {
		$M->goErrorPage($path, HTTP_DIR . "$currentDept/$moduleDept/index.php");
	} else {
		eval(html_entity_decode($path[0]));	
		$path = array_reverse($path);
	}
	$strGet = "lastCategoryNo=$_lastCategoryNo&_status=$_status" ;
}

//Search Option 2 : by model no
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "model_no") {
	$sqlQuery->setWhere(" AND %s ILIKE '%%%s%%'", array("it_model_no" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString() . "searchBy=model_no";
}

//Search Option 3 : by description
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "desc") {
	$sqlQuery->setWhere(" AND %s ILIKE '%%%s%%'", array("it_desc" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString(). "searchBy=desc";
}

//Search Option 4 : by Code
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "code_no") {
	$sqlQuery->setWhere(" AND %s ILIKE '%%%s%%'", array("it_code" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString(). "searchBy=code_no";
}

//$findStrGet = str_replace('curpage=','',getQueryString());
$strGet		= str_replace('curpage=','',getQueryString());

//$strGet		= "lastCategoryNo=" . $_lastCategoryNo;
$sqlQuery->setOrderBy("it_code");

if(isZKError($result =& query($sqlQuery->getSQL())))
	$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/index.php");

//Total Rows
$numRow = numQueryRows($result);

//Declare Paging
$oPage = new strPaging($sqlQuery->getSQL(), 25);
$oPage->strPrev       = "";
$oPage->strNext       = "";
$oPage->strPrevDiv    = "<";
$oPage->strNextDiv    = ">";
$oPage->strLast       = ">>";
$oPage->strFirst      = "<<";
$oPage->strCurrentNum = "<strong>[%s]</strong>";
$oPage->strGet = $strGet;

if(isZKError($result =& query($oPage->getListQuery())))
	$m->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");
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

function searchByKeyword() {
	var o = window.document.frmSrarchByKeyword;
	if(o.txtKeyword.value <=0 ) {
		alert("Please insert the model no or Keyword");
		o.txtKeyword.focus();
	} else {
		o.submit();
	}
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
<table width="100%" class="table_box">
	<tr>
		<td style="color:#000000"><h4>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] STOCK LEVEL</h4></td>
		<td align="right" colspan="2">
			<form name="frmSearch" method="GET">
			  Category:
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
			</form>
		</td>
		<th width="5%">
			<a href="javascript:searchByCat()"><img src="../../_images/icon/search_mini.gif" alt="Search"></a>&nbsp;
		</th>
	</tr>
</table>
<table width="100%" class="table_box">
	<tr>
		<td>
			Filter Status:
			<select name="cboFilterStatus">
				<option value="all">==ALL==</option>
				<option value="available">Available</option>
				<option value="critical">Critical Status</option>
			</select>
		</td>
		<td align="right">
			<form name="frmSrarchByKeyword" method="get">
			Search: 
			<select name="searchBy">
				<option value="code_no">CODE</option>
				<option value="model_no" <?php echo (isset($_GET['searchBy']) && $_GET['searchBy'] == "model_no") ? "selected":""?>>MODEL</option>
				<option value="desc" <?php echo (isset($_GET['searchBy']) && $_GET['searchBy'] == "desc") ? "selected":""?>>DESCRIPTION</option>
			</select>&nbsp;
			<input type="text" name="txtKeyword" size="15" class="fmt" value="<?php echo isset($_GET['txtKeyword']) ? $_GET['txtKeyword'] : ""?>">
			</form>
		</td>
		<th width="8%">
			<a href="javascript:searchByKeyword()"><img src="../../_images/icon/search_mini.gif" alt="Search"></a>&nbsp;
			<a href="javascript:document.location.href='list_quantity_level.php'"><img src="../../_images/icon/list_mini.gif" alt="Show all item"></a>&nbsp;
		</th>
	</tr>
</table><br />
<script language="javascript" type="text/javascript">
function searchByCat() {
	// take the last category number
	var o = window.document.frmSearch.all.tags('SELECT');
	for (var i = 2; i >=0; i--) {
		if (o[i].value != 0) {
			window.location.href = "?lastCategoryNo=" + o[i].value + '&cboFilterStatus=' + window.document.all.cboFilterStatus.value;
			break;
		}
	}
}

setSelect(window.document.all.cboFilterStatus, "<?php echo isset($_GET['cboFilterStatus']) ? $_GET['cboFilterStatus'] : "all"?>");

window.document.all.cboFilterStatus.onchange = function() {
	var status	= window.document.all.cboFilterStatus.value;
	window.location.href = 'list_quantity_level.php?<?php echo getQueryString() ?>&cboFilterStatus='+status;
}
</script>
<form name="frmUpdate" method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>">
<input type="hidden" name="p_mode" value="update">
<table width="100%" class="table_c">
	<tr>
		<th width="3%">No</th>
		<th width="6%">CODE</th>
		<th width="15%">ITEM NO</th>
		<th>DESCRIPTION</th>
		<th width="9%">CRITICAL<br /><input type="checkbox" name="chkAllItem[]" onclick="checkAll(this.checked)"> STOCK</th>
		<th width="8%">RECENTLY<br />STOCK</th>
		<th width="6%">CALC<br />( <font color="green">+</font> / <font color="red">-</font> )</th>
	</tr>
	<?php while ($column =& fetchRowAssoc($result)) { ?>
	<tr>
		<td><?php echo ++$oPage->serial ;?></td>
		<td><?php echo $column['it_code']?></td>
		<td><?php echo substr($column['it_model_no'], 0, 30)?></td>
		<td><?php echo cut_string($column['it_desc'],60);?></td>
		<td align="right">
			<input type="checkbox" name="chkItem[]" value="<?php echo $column['it_code']?>">
			<input type="text" name="_it_critical_<?php echo trim($column['it_code'])?>_qty" class="fmtn" style="width:60%;height:100%;color:darkblue" onKeyUp="formatNumber(this,'dot')" value="<?php echo number_format($column['it_critical_stock'])?>">
		</td>
		<td align="right"><?php echo ($column['it_available_stock'] == '')? '0' : number_format($column['it_available_stock'])?></td>
		<td align="right"><font color="<?php echo $column['it_color'] ?>"><?php echo ($column['it_diff']<=0) ? number_format($column['it_diff']*-1) : number_format($column['it_diff'])?></font></td>
	</tr>
	<?php } ?>
</table>
</form>
<table width="100%" cellpadding="0" cellspacing="2" border="0">
	<tr>
		
		<td align="right" width="50%"><?php echo $oPage->putPaging();?></td>
		<td width="28%"></td>
		<td><button name="btnUpdate" class="input_btn" style="width:100px;height:30px"><img src="../../_images/icon/setting_mini.gif"> &nbsp; Update</button></td>
	</tr>
</table>
<script language="javascript1.2" type="text/javascript">
function checkAll(o) {
	var oCheck = window.document.frmUpdate.tags("INPUT");

	for (var i = 0; i < oCheck.length; i++) {
		if (oCheck[i].type == "checkbox" && oCheck[i].name == "chkItem[]") {
			oCheck[i].checked = o;
		}
	}
}

window.document.all.btnUpdate.onclick = function() {
	var o			 = window.document.frmUpdate;
	var oCheck		 = window.document.frmUpdate.tags("INPUT");
	var selectedItem = new Array();
	var counter		 = 0;

	for (var i = 0; i < oCheck.length; i++) {
		if (oCheck[i].type == "checkbox" && oCheck(i).name == "chkItem[]" && oCheck(i).checked) {
			selectedItem[counter++] = oCheck[i].value;
		}
	}

	if(selectedItem.length > 0) {
		if (confirm("Are you sure to update?")) {
			if(verify(o)){
				o.submit();
			}
		}
	} else {
		alert("You haven't check any item.\nPlease check item that you want to update");
		return;
	}
}
</script>
            <!--END: html_body.tpl-->
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