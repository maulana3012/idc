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
$left_loc	= "list_detail_demo.php";
$strGet 	= "";

//DEFAULT PROCESS
$sqlQuery = new strSelect(
"SELECT 
  it_code,
  it_model_no,
  it_desc,
  ".ZKP_SQL."_getDemoQty(1, it_code) AS in_qty,
  ".ZKP_SQL."_getDemoQty(2, it_code) AS use_qty,
  ".ZKP_SQL."_getDemoQty(3, it_code) AS return_qty,
  ".ZKP_SQL."_getDemoQty(4, it_code) AS deleted_qty,
  ".ZKP_SQL."_getDemoQty(1, it_code)+ ".ZKP_SQL."_getDemoQty(3, it_code)- ".ZKP_SQL."_getDemoQty(2, it_code)- ".ZKP_SQL."_getDemoQty(4, it_code) AS bal_qty
FROM
	".ZKP_SQL."_tb_demo
	JOIN ".ZKP_SQL."_tb_item USING(it_code)
");

//Search Option 1 : by Category
if(isset($_GET['lastCategoryNo']) && $_GET['lastCategoryNo'] > 0) {
	$_lastCategoryNo = $_GET['lastCategoryNo'];

	//get all the sub icat_midx value from stored procedure.
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_lastCategoryNo);
	$sqlQuery->whereCaluse = "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";

	//get category path from current icat_midx.
	if(isZKError($path = executeSP(ZKP_SQL."_getCategoryPath", $_lastCategoryNo))) {
		$M->goErrorPage($path, HTTP_DIR . "$currentDept/$moduleDept/index.php");
	} else {
		eval(html_entity_decode($path[0]));	
		$path = array_reverse($path);
	}
	$strGet = "&lastCategoryNo=" . $_lastCategoryNo . "_location=$_location";
}

//Search Option 2 : by model no
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "model_no") {
	$sqlQuery->setWhere("%s ILIKE '%%%s%%'", array("it_model_no" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString() . "searchBy=model_no";
}

//Search Option 3 : by description
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "desc") {
	$sqlQuery->setWhere("%s ILIKE '%%%s%%'", array("it_desc" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString(). "searchBy=desc";
}

//Search Option 4 : by Code
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "code_no") {
	$sqlQuery->setWhere("%s ILIKE '%%%s%%'", array("it_code" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString(). "searchBy=code_no";
}

$findStrGet = str_replace('curpage=','',getQueryString());
$strGet		= $findStrGet;

$sqlQuery->setOrderBy("it_code");

if(isZKError($result =& query($sqlQuery->getSQL()))) {
	$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/index.php");
}

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
		<td style="color:#000000"><h4>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] CURRENT DEMO STOCK</h4></td>
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
		<td colspan="2" align="right">
			<form name="frmSrarchByKeyword" method="get">
			Search: 
			<select name="searchBy">
				<option value="code_no">CODE</option>
				<option value="model_no" <?php echo (isset($_GET['searchBy']) && $_GET['searchBy'] == "model_no") ? "selected":""?>>MODEL</option>
				<option value="desc" <?php echo (isset($_GET['searchBy']) && $_GET['searchBy'] == "desc") ? "selected":""?>>DESCRIPTION</option>
			</select>&nbsp;
			<input type="text" name="txtKeyword" size="15" class="fmt"
			value="<?php echo isset($_GET['txtKeyword']) ? $_GET['txtKeyword'] : ""?>">
			</form>
		</td>
		<th width="8%">
			<a href="javascript:searchByKeyword()"><img src="../../_images/icon/search_mini.gif" alt="Search"></a>&nbsp;
			<a href="javascript:document.location.href='list_detail_demo.php'"><img src="../../_images/icon/list_mini.gif" alt="Show all item"></a>&nbsp;
		</th>
	</tr>
</table><br />
<script language="javascript" type="text/javascript">
function searchByCat() {
	var o = window.document.frmSearch.all.tags('SELECT');
	for (var i = 2; i >=0; i--) {
		if (o[i].value != 0) {
			window.location.href = "?lastCategoryNo=" + o[i].value;
			break;
		}
	}
}
</script>
<?php if(numQueryRows($result)<=0) { ?>
<span class="comment"><i>(No recorder stock)</i></span>
<?php } else { ?>
			<table width="100%" class="table_c">
				<tr>
					<th width="8%">CODE</th>
					<th width="15%">ITEM NO</th>
					<th>DESCRIPTION</th>
					<th width="7%">IN QTY<br />(Pcs)</th>
					<th width="7%">USE QTY<br />(Pcs)</th>
					<th width="7%">RETURN<br />(Pcs)</th>
					<th width="7%">DELETED<br />(Pcs)</th>
					<th width="7%">DEMO<br />(Pcs)</th>
				</tr>
				<?php  while ($column =& fetchRowAssoc($result)) { ?>
				<tr>
					<td><?php echo $column['it_code']?></td>
					<td><?php echo substr($column['it_model_no'], 0, 13)?></td>
					<td><?php echo cut_string($column['it_desc'],65);?></td>
					<td align="right"><?php echo ($column['it_code']=='2101  ' || $column['it_code']=='2101NE') ? number_format($column['in_qty'],2) : number_format($column['in_qty'])?></td>
					<td align="right"><?php echo ($column['it_code']=='2101  ' || $column['it_code']=='2101NE') ? number_format($column['use_qty'],2) :number_format($column['use_qty'])?></td>
					<td align="right"><?php echo ($column['it_code']=='2101  ' || $column['it_code']=='2101NE') ? number_format($column['return_qty'],2) : number_format($column['return_qty'])?></td>
					<td align="right"><?php echo ($column['it_code']=='2101  ' || $column['it_code']=='2101NE') ? number_format($column['deleted_qty'],2) : number_format($column['deleted_qty'])?></td>
					<td align="right"><?php echo ($column['it_code']=='2101  ' || $column['it_code']=='2101NE') ? number_format($column['bal_qty'],2) : number_format($column['bal_qty'])?></td>
				</tr>
				<?php ++$oPage->serial; } ?>
			</table>
			<table width="100%" cellpadding="0" cellspacing="2" border="0">
				<tr>
					<td align="center"><?php echo $oPage->putPaging();?></td>
				</tr>
			</table>
<?php } ?>
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