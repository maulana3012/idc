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

//GLOBAL
$_lastCategoryNo = 0;
$strGet		= "";

//DEFAULT PROCESS
$sqlQuery = new strSelect("
SELECT 
  it_code,
  it_model_no,
  it_desc,
  CASE
	WHEN it_ed is true then 1
	WHEN it_ed is false then 0
  END AS it_returnable,
  demo_qty,
  ".ZKP_SQL."_getBookDemo(it_code) AS book_qty
FROM ".ZKP_SQL."_tb_demo AS d JOIN ".ZKP_SQL."_tb_item AS i USING(it_code)
");

$sqlQuery->whereCaluse = "demo_qty != 0";

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
	printf("it['%s'] = ['%s','%s','%s',%s,%s];\n",
		addslashes($rows[0]),	//it_code			-idx
		addslashes($rows[0]),	//it_code			-0		-''
		addslashes($rows[1]),	//it_model_no		-1		-''
		addslashes($rows[2]),	//it_desc			-2		-''
		$rows[3],				//it_returnable		-3		-
		$rows[4]-$rows[5]		//it_max_qty		-4		-
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

	f._it_code.value	 			= it[idx][0];
	f._it_model_no.value 			= it[idx][1];
	f._it_desc.value	 			= it[idx][2];
	f._it_returned.value			= it[idx][3];
	f._it_returnable[it[idx][3]].checked = true;
	f._it_qty.focus();
}

//Wrapper function. It call opener's function.
function createNewItem() {
	var f = window.document.frmCreateItem;

	if (f._it_code.value.length <= 0) {
		alert("Please select the code first");
		f._it_code.focus();
		return;
	} else if (f._it_qty.value.length <= 0) {
		alert("Please fill the qty");
		f._it_qty.focus();
		return;
	} else if (f._it_qty.value <= 0) {
		alert("Qty must be more than 0");
		f._it_qty.focus();
		return;
	} else if (f._it_qty.value > it[f._it_code.value][4]) {
		alert("Qty value can't more than estimated stock value");
		f._it_qty.value = '';
		f._it_qty.focus();
		return;
	} 

	if(f._it_returnable[0].checked == true) {
		f._it_returned.value = 0;
	} else if(f._it_returnable[1].checked == true) {
		f._it_returned.value = 1;
	}

	window.opener.createItem();
	window.document.frmSrarchByKeyword.txtKeyword.value = "";
	window.document.frmSrarchByKeyword.txtKeyword.focus();
}
</script>
</head>
<body style="margin:8pt" onLoad="fillOptionInit()">
<!--START: BODY-->
<strong>
[<font color="blue"><?php echo strtoupper($currentDept) ?></font>] CURRENT DEMO STOCK (STEP 1 / 1)<br />
<small>* Printed for item list</small>
</strong>
<hr>
<table width="100%" class="table_box">
	<tr>
		<td align="right">
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
		<th width="7%">
			<a href="javascript:searchByCat()"><img src="../../_images/icon/search_mini.gif" alt="Search"></a>&nbsp;
		</th>
	</tr>
</table>
<table width="100%" class="table_box">
	<tr>
		<td align="right" colspan="2">
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
		<th width="15%">
			<a href="javascript:searchByKeyword()"><img src="../../_images/icon/search_mini.gif" alt="Search"></a>&nbsp;
			<a href="javascript:document.location.href='p_list_item.php'"><img src="../../_images/icon/list_mini.gif" alt="Show all item"></a>&nbsp;
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
<table width="100%" class="table_box">
	<tr>
		<th rowspan="2" width="4%">No</th>
		<th rowspan="2" width="6%">CODE</th>
		<th rowspan="2" width="12%">ITEM NO</th>
		<th rowspan="2">DESCRIPTION</th>
		<th colspan="2" width="20%">DEMO STOCK</th>
	</tr>
	<tr>
		<th width="10%">REAL</th>
		<th width="10%">EST</th>
	</tr>
</table>
<div style="height:310; overflow-y:scroll">
<table width="100%" class="table_c">
<?php
pg_result_seek($result, 0);
while ($column =& fetchRowAssoc($result)) {
	$est_qty = $column['demo_qty']-$column['book_qty'];
?>
	<tr>
		<td><?php echo ++$oPage->serial ;?></td>
		<?php if($est_qty <= 0) { ?>
		<td style="color:#969696"><?php echo $column['it_code']?></td>
		<?php } else { ?>
		<td><a href="javascript:fillItem('<?php echo $column['it_code']?>')"><?php echo $column['it_code']?></a></td>
		<?php } ?>
		<td><?php echo substr($column['it_model_no'],0,15)?></td>
		<td><?php echo cut_string($column['it_desc'],55);?></td>
		<td width="8%" align="right"><?php echo number_format((double)$column['demo_qty'])?></td>
		<td width="8%" align="right"><?php echo number_format((double)$est_qty)?></td>
	</tr>
<?php } ?>
</table>
</div>
<table width="100%" cellpadding="0" cellspacing="2" border="0">
  <tr>
	<td align="center"><?php echo $oPage->putPaging();?></td>
  </tr>
</table>
<form name="frmCreateItem" method="POST">
<table width="100%" class="table_box">
	<tbody>
		<tr height="35px">
			<th width="8%">CODE</th>
			<th width="15%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="12%">QTY</th>
			<th width="8%">RETURNABLE</th>
			<th width="20%">REMARK</th>
		</tr>
		<tr>
			<td><input type="text" name="_it_code" style="width:100%" class="req" readonly></td>
			<td><input type="text" name="_it_model_no" style="width:100%" class="fmt" readonly></td>
			<td><input type="text" name="_it_desc" style="width:100%" class="fmt" readonly></td>
			<td><input type="text" name="_it_qty" style="width:100%" class="reqn" onKeyUp="formatNumber(this, 'dot');" onKeyPress="if(window.event.keyCode == 13) createNewItem()"></td>
			<td align="center">
				<input type="radio" name="_it_returnable" value="0" id="0" checked><label for="0">Y&nbsp;</label>
				<input type="radio" name="_it_returnable" value="1" id="1"><label for="1">N</label>
				<input type="hidden" name="_it_returned">
			</td>
			<td><input type="text" name="_it_remark" class="fmt" style="width:100%" onKeyPress="if(window.event.keyCode == 13) createNewItem()"></td>
		</tr>
		<tr>
			<td colspan="6" align="right">
				<button name='btnAdd' class='input_sky' style='width:50px;height:25px' onclick='createNewItem()'><img src="../../_images/icon/add.gif" width="18px" align="middle" alt="Add item"></button>&nbsp;
				<button name='btnClose' class='input_sky' style='width:50px;height:25px' onclick='window.close()'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Close pop-up"></button>
			</td>
		</tr>
	</tbody>
</table>
</form>
</body>
</html>