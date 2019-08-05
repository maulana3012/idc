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

//Check PARAMETER
$left_loc	= "list_stock.php";
$_status	= isset($_GET['_status']) ? $_GET['_status'] : 0;
$_location	= (isset($_GET['_location'])) ? trim($_GET['_location']):1;
$_lastCategoryNo = 0;
$strGet		= "";

//DEFAULT PROCESS
$sqlQuery = new strSelect("
SELECT 
  it_code,
  icat_midx,
  it_model_no,
  it_type,
  it_desc,
  ".ZKP_SQL."_getReadyStock(it_code,'$department') AS real_stock,
  ".ZKP_SQL."_getReadyStock(it_code,'$department') - ".ZKP_SQL."_getBookedStock(NULL,it_code) AS est_stock,
  ".ZKP_SQL."_getBookedStock(NULL,it_code) AS book_qty
FROM ".ZKP_SQL."_tb_item
");

$sqlQuery->whereCaluse = "it_status = 0 AND ".ZKP_SQL."_getReadyStock(it_code,'$department') > 0";

//Search Option 1 : by Category
if(isset($_GET['lastCategoryNo']) && $_GET['lastCategoryNo'] > 0) {
	$_lastCategoryNo = $_GET['lastCategoryNo'];
	
	//get all the sub icat_midx value from stored procedure.
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_lastCategoryNo);
	$sqlQuery->whereCaluse = "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	
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

$findStrGet = str_replace('curpage=','',getQueryString());
$strGet		= $findStrGet . "&_status=$_status";	//show just parent item

$sqlQuery->setOrderBy("it_code");
if(isZKError($result =& query($sqlQuery->getSQL())))
	$M->goErrorPage($result,  HTTP_DIR."$currentDept/$moduleDept/index.php");

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
	$m->goErrorPage($result, HTTP_DIR."$currentDept/$moduleDept/index.php");
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
<script language='javascript' type='text/javascript'>
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
	window.document.frmSrarchByKeyword.txtKeyword.focus();
}

function resetOption() {
	window.document.frmSearch.icat_3.options.length = 1;
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

function fillQtyItem() {

	var e 			= window.document.all.elements;
	var count		= window.itemPosition.rows.length;

	for (var i=0; i<count; i++) {
		var oRow = window.itemPosition.rows(i);
		var item = oRow.id; 
		for(var j=0; j<it.length; j++) {
			if(it[j][0] == item) {
				if(item == '2101' || item == '2101NE') {
					var dec = 2;
				} else {
					var dec = 0;
				}
				oRow.cells(4).innerText = numFormatval(parseFloat(it[j][5])+'',dec);
				oRow.cells(5).innerText = numFormatval(parseFloat(it[j][6])+'',dec);
				oRow.cells(6).innerText = numFormatval(parseFloat(it[j][7])+'',dec);
				break;
			}
		}
	}

}

function initPage() {
	fillOptionInit();
//	fillQtyItem();
}
</script>
</head>
<body topmargin="0" leftmargin="0" onLoad="initPage()">
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
		<td style="color:#000000"><h4>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] CURRENT STOCK</h4></td>
		<td align="right">
			<form name="frmSearch" method="GET">
			Category:
			<input type="hidden" name="lastCategoryNo" value="0">
			<input type="hidden" name="_status" value="<?php echo $_status ?>">
				<select name="icat_1" onChange="fillOption(window.document.frmSearch.icat_2, this.value)" onClick="resetOption()">
					<option value="0">==ALL==</option>
				</select>&nbsp;
				<select name="icat_2" onChange="fillOption(window.document.frmSearch.icat_3, this.value)">
					<option value="0">==ALL==</option>
				</select>&nbsp;
				<select name="icat_3">
					<option value="0">==ALL==</option>
				</select>
			</form>
		</td>
		<th width="7%">
			<a href="javascript:searchByCat()"><img src="../../_images/icon/search_mini.gif" alt="Search"></a>&nbsp;
		</th>
	</tr>
</table>
<table width="100%" class="table_box">
	<tr>
		<td align="right">
			<form name="frmSrarchByKeyword" method="get">
			Search:
			<select name="searchBy">
				<option value="code_no">CODE</option>
				<option value="model_no" <?php echo (isset($_GET['searchBy']) && $_GET['searchBy'] == "model_no") ? "selected":""?>>MODEL</option>
				<option value="desc" <?php echo (isset($_GET['searchBy']) && $_GET['searchBy'] == "desc") ? "selected":""?>>DESCRIPTION</option>
			</select>
				<input type="text" name="txtKeyword" size="15" class="fmt" value="<?php echo isset($_GET['txtKeyword']) ? $_GET['txtKeyword'] : ""?>" accesskey="k">
			</form>
		</td>
		<th width="10%">
			<a href="javascript:searchByKeyword()"><img src="../../_images/icon/search_mini.gif" alt="Search"></a>&nbsp;
			<a href="javascript:document.location.href='list_stock.php'"><img src="../../_images/icon/list_mini.gif" alt="Show all item"></a> &nbsp;
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
<table width="90%" class="table_c">
	<tr>
		<th rowspan="2" width="3%">No</th>
		<th rowspan="2" width="8%">CODE</th>
		<th rowspan="2" width="15%">ITEM NO</th>
		<th rowspan="2">DESCRIPTION</th>
		<th colspan="2" width="16%">STOCK</th>
		<th rowspan="2" width="8%">BOOKING<br />QTY</th>
	</tr>
	<tr>
		<th width="8%">REAL</th>
		<th width="8%">EST</th>
	</tr>
  <tbody id="itemPosition">
<?php
while ($column =& fetchRowAssoc($result)) {
?>
	<tr id="<?php echo trim($column['it_code'])?>">
		<td><?php echo ++$oPage->serial ;?></td>
		<td><span class="bar"><?php echo $column['it_code']?></span></td>
		<td><?php echo $column['it_model_no'] ?></td>
		<td><?php echo $column['it_desc'];?></td>
		<td width="11%" align="right"><?php echo $column['real_stock'];?></td>
		<td width="11%" align="right"><?php echo $column['est_stock'];?></td>
		<td width="11%" align="right"><?php echo $column['book_qty'];?></td>
	</tr>
<?php } ?>	
  </tbody>
</table>
<table width="100%" cellpadding="0" cellspacing="2" border="0">
  <tr>
	<td align="center"><?php echo $oPage->putPaging();?></td>
  </tr>
</table>

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