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
ckperm(ZKP_SELECT, "javascript:window.close();");

$strGet		= "";
$_lastCategoryNo = $_GET['lastCategoryNo'];

//DEFAULT PROCESS
$sqlQuery = new strSelect("
SELECT
  icat_midx,
  it_code,
  it_model_no,
  it_desc
FROM
  ".ZKP_SQL."_tb_item");

//Search Option 1 : by Category
if(isset($_GET['lastCategoryNo']) && $_GET['lastCategoryNo'] > 0) {
	$_lastCategoryNo = $_GET['lastCategoryNo'];
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_lastCategoryNo);

	if($_lastCategoryNo == '46') {
		$sqlQuery->whereCaluse = "it_code IN ('2000', '2000A', '2000C', '2000D')";
	} else if($_lastCategoryNo == '29') {
		$sqlQuery->whereCaluse = "it_code IN ('0200', '0223', '0224', '0225', '0213', '0220', '0221', '0222')";
	} else {
		$sqlQuery->whereCaluse = "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	}

	//get category path from current icat_midx.
	if(isZKError($path = executeSP(ZKP_SQL."_getCategoryPath", $_lastCategoryNo))) {
		$M->goErrorPage($path, "javascript:window.close();");
	} else {
		eval(html_entity_decode($path[0]));	
		$path = array_reverse($path);
	}

	$strGet = "&lastCategoryNo=" . $_lastCategoryNo;
}

//Search Option 2 : by model no
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "model_no") {
	$sqlQuery->setWhere("AND %s ILIKE '%%%s%%'", array("it_model_no" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString() . "searchBy=model_no";
}

//Search Option 3 : by description
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "desc") {
	$sqlQuery->setWhere("AND %s ILIKE '%%%s%%'", array("it_desc" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString(). "searchBy=desc";
}

//Search Option 4 : by Code
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "code_no") {
	$sqlQuery->setWhere("AND %s ILIKE '%%%s%%'", array("it_code" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString(). "searchBy=code_no";
}

$sqlQuery->setOrderBy("it_code");
if(isZKError($result =& query($sqlQuery->getSQL())))
	$M->goErrorPage($result,  "javascript:window.close();");

//Total Rows
$numRow = numQueryRows($result);

//Declare Paging
$oPage = new strPaging($sqlQuery->getSQL(), 40);
$oPage->strPrev       = "";
$oPage->strNext       = "";
$oPage->strPrevDiv    = "<";
$oPage->strNextDiv    = ">";
$oPage->strLast       = ">>";
$oPage->strFirst      = "<<";
$oPage->strCurrentNum = "<strong>[%s]</strong>";
$oPage->strGet = $strGet;

if(isZKError($result =& query($oPage->getListQuery())))
	$m->goErrorPage($result, "javascript:window.close();");
?>
<html>
<head>
<title>ITEMS LIST</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="text/javascript" type="text/javascript" src="../../_script/aden.js"></script>
<script language="javascript" type="text/javascript" src="../../_script/js_category.php"></script>
<script language='javascript' type='text/javascript'>
<?php
// Print Javascript Code
echo "var it = new Array();\n";
$i = 0;
while ($rows =& fetchRow($result, 0)) {
	printf("it['%s'] = ['%s','%s','%s','%s'];\n",
		addslashes($rows[1]), 
		addslashes($rows[0]), 
		addslashes($rows[1]), 
		addslashes($rows[2]), 
		addslashes($rows[3])
	); 
}
?>

function fillItem(idx) {
	var f = window.opener.document.frmInsert;
	f._it_code.value	 = it[idx][1];
	f._it_model_no.value = it[idx][2];
	window.close();
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
	//window.document.frmSrarchByKeyword.txtKeyword.focus();
}

function resetOption() {
	window.document.frmSearch.icat_3.options.length = 1;
}

function initPage() {
	fillOptionInit();

	if(window.document.frmSearch.lastCategoryNo.value == '46' || window.document.frmSearch.lastCategoryNo.value == '29') {
		window.document.frmSearch.icat_2.disabled = true;
		window.document.frmSearch.icat_3.disabled = true;
		window.document.frmSearch.searchBy.disabled = true;
		window.document.frmSearch.txtKeyword.readonly = true;
	}

	window.document.frmSearch.txtKeyword.focus();
}
</script>
</head>
<body style="margin:8pt" onLoad="initPage()">
<strong>
[<font color="blue"><?php echo strtoupper($currentDept) ?></font>] LIST ITEM<br />
</strong>
<hr>
<form name="frmSearch" method="GET">
<table width="100%" class="table_box">
	<tr>
		<td align="right">
			Category:
			<input type="hidden" name="lastCategoryNo" value="<?php echo $_lastCategoryNo ?>">
			<select name="icat_1" onChange="fillOption(window.document.frmSearch.icat_2, this.value)" onClick="resetOption()" disabled>
				<option value="0">==ALL==</option>
			</select>&nbsp;
			<select name="icat_2" onChange="fillOption(window.document.frmSearch.icat_3, this.value)">
				<option value="0">==ALL==</option>
			</select>&nbsp;
			<select name="icat_3">
				<option value="0">==ALL==</option>
			</select> &nbsp;
		</td>
		<th rowspan="2" width="7%">
			<a href="javascript:searchByCat()"><img src="../../_images/icon/search_mini.gif" alt="Search"></a><br />
			<a href="javascript:document.location.href='p_list_item.php?lastCategoryNo=<?php echo ($_lastCategoryNo=='29') ? 1:$_lastCategoryNo ?>'"><img src="../../_images/icon/list_mini.gif" alt="Show all item"></a>&nbsp;
		</th>
	</tr>
	<tr>
		<td align="right">
			Search:
			<select name="searchBy">
				<option value="code_no">CODE</option>
				<option value="model_no" <?php echo (isset($_GET['searchBy']) && $_GET['searchBy'] == "model_no") ? "selected":""?>>MODEL</option>
				<option value="desc" <?php echo (isset($_GET['searchBy']) && $_GET['searchBy'] == "desc") ? "selected":""?>>DESCRIPTION</option>
			</select>
			<input type="text" name="txtKeyword" size="15" class="fmt" value="<?php echo isset($_GET['txtKeyword']) ? $_GET['txtKeyword'] : ""?>" accesskey="k">
		</td>
	</tr>
</table><br />
</form>
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
<table width="100%" class="table_box">
  <tr height="25px">
	<th width="3%" rowspan="2">No</th>
	<th width="15%" rowspan="2">CODE</th>
	<th width="30%">MODEL NO</th>
	<th>DESC</th>
  </tr>
</table>
<div style="height:450; overflow-y:scroll">
<table width="100%" class="table_c">
<?php
pg_result_seek($result, 0);
while ($column =& fetchRowAssoc($result)) {
?>
  <tr>
	<td><?php echo ++$oPage->serial ;?></td>
	<td><a href="javascript:fillItem('<?php echo $column['it_code'];?>')"><?php echo $column['it_code']?></a></td>
	<td><?php echo cut_string($column['it_model_no'],15)?></td>
	<td><?php echo cut_string($column['it_desc'],32)?></td>
  </tr>
<?php } ?>
</table>
</div>
<table width="100%" cellpadding="0" cellspacing="2" border="0">
  <tr>
	<td align="center"><?php echo $oPage->putPaging();?></td>
  </tr>
</table>
</body>
</html>