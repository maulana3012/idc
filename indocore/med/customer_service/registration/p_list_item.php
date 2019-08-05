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

//DEFAULT PROCESS
$sqlQuery = new strSelect("SELECT it_code, it_model_no, it_type, it_desc FROM ".ZKP_SQL."_tb_item");
$strGet = "";
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
	$sqlQuery->setWhere("%s ILIKE '%s%%'", array("it_model_no" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString() . "searchBy=model_no";
}

//Search Option 3 : by description
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "desc") {
	$sqlQuery->setWhere("%s ILIKE '%%%s%%'", array("it_desc" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString(). "searchBy=desc";
}

//Search Option 4 : by Code
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "code_no") {
	$sqlQuery->setWhere("%s ILIKE '%s%%'", array("it_code" => "txtKeyword"), "AND");
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
<script language="text/javascript" type="text/javascript" src="../../_script/js_category.php"></script>
<script language='text/javascript' type='text/javascript'>
<?php
// Print Javascript Code
echo "var it = new Array();\n";
$i = 0;
while ($rows =& fetchRow($result,0)) {
	printf("it['%s'] = ['%s', '%s', '%s', '%s'];\n",
		addslashes($rows[0]), //code from query
		addslashes($rows[0]),
		addslashes($rows[1]), //model no
		addslashes($rows[3]), //desc
		empty($rows[4])?"0":$rows[4]); //unit price
}
?>

function enabledText() {
	var f	= window.document.frmCreateItem;

	if(f._it_is_guarantee[0].checked) {
		f._it_guarantee_period.disabled		= false;
		f._it_guarantee_period.className	= "reqd";
		f._it_guarantee_period.readOnly		= false;
	} else if(f._it_is_guarantee[1].checked) {
		f._it_guarantee_period.disabled		= true;
		f._it_guarantee_period.className	= "fmt";
		f._it_guarantee_period.readOnly		= "readonly";
		f._it_guarantee_period.value		= "";
	}
}

function fillItem(idx) {
	var f = window.document.frmCreateItem;
	f._it_code.value		= it[idx][0];
	f._it_model_no.value	= it[idx][1];
	f._it_guarantee_period.focus();
}

//Wrapper function. It call opener's function.
function createNewItem() {
	var f = window.document.frmCreateItem;
	var d = parseDate(f._it_guarantee_period.value, 'prefer_euro_format');

	if (f._it_code.value.length <= 0) {
		alert("Please select the code first");
		f._it_code.focus();
		return;
	} else if (f._it_model_no.value.length <= 0) {
		alert("Please fill the model no");
		f._it_model_no.focus();
		return;
	} else if (f._it_is_guarantee[0].checked && d==null) {
		alert("You must be input date with proper format")
		f._it_guarantee_period.value = "";
		f._it_guarantee_period.focus();
		return;
	} else if (f._it_sn.value.length <= 0) {
		alert("Please fill the serial number");
		f._it_sn.focus();
		return;
	}

	if(d!=null) { f._it_guarantee_period.value = formatDate(d, "d-NNN-yyyy"); }

	window.opener.createItem();
	window.document.frmSrarchByKeyword.txtKeyword.value = "";
	window.document.frmSrarchByKeyword.txtKeyword.focus();
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
</script>
</head>
<body style="margin:8pt" onLoad="fillOptionInit()">
<!--START: BODY-->
<strong>
[<font color="blue"><?php echo strtoupper($currentDept) ?></font>] CURRENT ITEM (STEP 1 / 1)<br />
<small>* Printed for sales item list</small>
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
		<td align="right" colspan="2">
			<form name="frmSrarchByKeyword" method="get">Search: 
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
	<tr heght="30px">
		<th width="7%">No</th>
		<th width="10%">CODE</th>
		<th width="25%">ITEM NO</th>
		<th>DESCRIPTION</th>
	</tr>
</table>
<div style="height:345; overflow-y:scroll">
<table width="100%" class="table_c">
<?php
pg_result_seek($result, 0);
while ($column =& fetchRowAssoc($result)) {
?>
	<tr>
		<td width="5%"><?php echo ++$oPage->serial ;?></td>
		<td width="12%"><a href="javascript:fillItem('<?php echo addslashes(html_entity_decode($column['it_code'], ENT_QUOTES));?>')"><?php echo $column['it_code']?></a></td>
		<td width="20%"><?php echo cut_string($column['it_model_no'],15)?></td>
		<td><?php echo $column['it_desc']?></td>
	</tr>
<?php } ?>
</table>
</div>
<table width="100%" cellpadding="0" cellspacing="2" border="0">
  <tr>
	<td align="center"><?php echo $oPage->putPaging();?></td>
  </tr>
</table>
<form name="frmCreateItem">
<table width="100%" class="table_box" cellspacing="1">
	<tbody>
		<tr>
			<th width="7%" rowspan="2">CODE</th>
			<th width="15%" rowspan="2">ITEM NO</th>
			<th width="18%" rowspan="2">GUARANTEE</th>
			<th width="15%" rowspan="2">SERIAL NUMBER</th>
			<th colspan="2">REMARK</th>
		</tr>
		<tr>
			<th>CUSTOMER</th>
			<th>TECHNICIAN</th>
		</tr>
		<tr>
			<td><input type="text" name="_it_code" style="width:100%" class="req" readonly></td>
			<td><input type="text" name="_it_model_no" style="width:100%" class="req"></td>
			<td>
				<input type="radio" name="_it_is_guarantee" value="1" onclick="enabledText()" checked>Yes, <input type="text" name="_it_guarantee_period" class="reqd" size="8">
				<input type="radio" name="_it_is_guarantee" value="0" onclick="enabledText()">No
			</td>
			<td><input type="text" name="_it_sn" style="width:100%" class="req" onKeyPress="if(window.event.keyCode == 13) createNewItem()"></td>
			<td><input type="text" name="_it_cus_complain" style="width:100%" class="fmt" onKeyPress="if(window.event.keyCode == 13) createNewItem()"></td>
			<td><input type="text" name="_it_tech_analyze" style="width:100%" class="fmt" onKeyPress="if(window.event.keyCode == 13) createNewItem()"></td>
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