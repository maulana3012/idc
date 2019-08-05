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
/*
if(!isset($_GET['_type']) || $_GET['_type'] == "")
	die("<script language=\"javascript1.2\">window.close();</script>");
*/
$_status	= isset($_GET['_status']) ? $_GET['_status'] : 0;
$_type		= (isset($_GET['_type'])) ? $_GET['_type']:2;
$_org_type	= (isset($_GET['_org_type'])) ? trim($_GET['_org_type']):2;
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
  ".ZKP_SQL."_getReadyStock(it_code,'$department') - ".ZKP_SQL."_getBookedStock(NULL,it_code) AS est_stock
FROM ".ZKP_SQL."_tb_item
");

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
$strGet		= $findStrGet . "&_status=$_status&_type=$_type";	//show just parent item

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
	printf("it['%s'] = ['%s',%s,'%s','%s','%s',%s,%s];\n",
		addslashes($rows[0]),	//wh_it_code		-idx
		addslashes($rows[0]),	//wh_it_code		-0		-''
		$rows[1],				//wh_it_icat_midx	-1		-
		addslashes($rows[2]),	//wh_it_model_no	-2		-''
		addslashes($rows[3]),	//wh_it_type		-3		-''
		addslashes($rows[4]),	//wh_it_desc		-4		-''
		$_type,				 	//it_type			-5		-
		$rows[6]				//wh_it_max_qty		-6		-
	); 
}
?>

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

function fillItem(idx) {
	var f = window.document.frmCreateItem;

	f._wh_it_code.value 	= it[idx][0];
	f._wh_it_icat_midx.value= it[idx][1];
	f._wh_it_model_no.value = it[idx][2];
	f._wh_it_type.value 	= it[idx][3];
	f._wh_it_desc.value	 	= it[idx][4];
	f._it_type.value 		= it[idx][5];
	f._wh_it_max_qty.value	= it[idx][6];
	f._wh_it_function.value	= 1;
	f._wh_it_qty.focus();
}

//Wrapper function. It call opener's function.
function createNewItem() {
	var f = window.document.frmCreateItem;

	if (f._wh_it_code.value.length <= 0) {
		alert("Please select the code first");
		f._wh_it_code.focus();
		return;
	} else if (f._wh_it_code.value.length <= 0) {
		alert("Please select the code first");
		f._wh_it_code.focus();
		return;
	} else if (f._wh_it_qty.value.length <= 0) {
		alert("Please fill the qty");
		f._wh_it_qty.focus();
		return;
	} else if (f._wh_it_qty.value <= 0) {
		alert("Qty must be more than 0");
		f._wh_it_qty.focus();
		return;
	} else if (parseFloat(removecomma(f._wh_it_qty.value)) > parseFloat(removecomma(f._wh_it_max_qty.value))) {
		alert("Qty value can't more than estimated stock value");
		f._wh_it_qty.value = addcomma(f._wh_it_max_qty.value);
		f._wh_it_qty.focus();
		return;
	} 

	f._wh_it_qty.value 	 	= removecomma(f._wh_it_qty.value);
	f._wh_it_function.value = f._wh_it_function.value;
	f._wh_it_remark.value	= f._wh_it_remark.value;
	f.submit();
}
</script>
</head>
<body style="margin:8pt" onLoad="fillOptionInit()">
<!--START: BODY-->
<strong>
[<font color="blue"><?php echo strtoupper($currentDept) ?></font>] CURRENT STOCK<br />
<small>* Stock information</small>
</strong>
<hr>
<table width="100%" class="table_box">
	<tr>
		<td align="right">
			<form name="frmSearch" method="GET">
			Category:
			<input type="hidden" name="lastCategoryNo" value="0">
			<input type="hidden" name="_org_type" value="<?php echo $_org_type ?>">
			<input type="hidden" name="_type" value="<?php echo $_type ?>">
			<input type="hidden" name="_status" value="<?php echo $_status ?>">
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
		<th width="7%">
			<a href="javascript:searchByCat()"><img src="../../_images/icon/search_mini.gif" alt="Search"></a>&nbsp;
		</th>
	</tr>
</table>
<form name="frmSrarchByKeyword" method="get">
<input type="hidden" name="_org_type" value="<?php echo $_org_type ?>">
<input type="hidden" name="_type" value="<?php echo $_type ?>">
<table width="100%" class="table_box">
	<tr>
		<td align="right" colspan="2">
			Search:
			<select name="searchBy">
				<option value="code_no">CODE</option>
				<option value="model_no" <?php echo (isset($_GET['searchBy']) && $_GET['searchBy'] == "model_no") ? "selected":""?>>MODEL</option>
				<option value="desc" <?php echo (isset($_GET['searchBy']) && $_GET['searchBy'] == "desc") ? "selected":""?>>DESCRIPTION</option>
			</select>
			<input type="text" name="txtKeyword" size="15" class="fmt" value="<?php echo isset($_GET['txtKeyword']) ? $_GET['txtKeyword'] : ""?>" accesskey="k">
		</td>
		<th width="15%" rowspan="2">
			<a href="javascript:searchByKeyword()()"><img src="../../_images/icon/search_mini.gif" alt="Search"></a>&nbsp;
			<a href="javascript:document.location.href='p_list_stock.php'"><img src="../../_images/icon/list_mini.gif" alt="Show all item"></a>
		</th>
	</tr>
</table><br />
</form>
<script language="javascript" type="text/javascript">
	function searchByCat() {
		var o = window.document.frmSearch.all.tags('SELECT');
		for (var i = 2; i >=0; i--) {
			if (o[i].value != 0) {
				window.location.href = "?lastCategoryNo=" + o[i].value + "&_type=" + '<?php echo $_type?>';
				break;
			}
		}
	}
</script>
<table width="100%" class="table_box">
	<tr>
		<th rowspan="2" width="4%">No</th>
		<th rowspan="2" width="6%">CODE</th>
		<th rowspan="2" width="12%">ITEM NO</th>
		<th rowspan="2">DESCRIPTION</th>
		<th colspan="2" width="20%">STOCK</th>
		<th rowspan="2" width="5%"></th>
	</tr>
	<tr>
		<th width="10%">REAL</th>
		<th width="10%">EST</th>
	</tr>
</table>
<div style="height:380; overflow-y:scroll">
<table width="100%" class="table_c">
<?php
pg_result_seek($result, 0);
while ($column =& fetchRowAssoc($result)) {
?>
	<tr>
		<td><?php echo ++$oPage->serial ;?></td>
		<?php if($column['est_stock'] > 0) { ?>
		<td><?php echo $column['it_code']?></td>
		<?php } else { ?>
		<td style="color:#969696"><?php echo $column['it_code']?></td>
		<?php } ?>
		<td><?php echo substr($column['it_model_no'],0,15)?></td>
		<td><?php echo cut_string($column['it_desc'],32);?></td>
		<td width="11%" align="right"><?php echo number_format((double)$column['real_stock'])?></td>
		<td width="11%" align="right"><?php echo number_format((double)$column['est_stock'])?></td>
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