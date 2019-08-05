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

//Check PARAMETER
if(!isset($_GET['_cus_code']) || $_GET['_cus_code'] == "")
	die("<script language=\"javascript1.2\">window.close();</script>");

$_cus_code 	= trim($_GET['_cus_code']);
$strGet		= "";

//DEFAULT PROCESS
$sqlQuery = new strSelect("
SELECT
  icat_midx,
  it_code,
  it_model_no,
  it_type,
  it_desc
FROM
  ".ZKP_SQL."_tb_item");

//Search Option 1 : by Category
if(isset($_GET['lastCategoryNo']) && $_GET['lastCategoryNo'] > 0) {
	$_lastCategoryNo = $_GET['lastCategoryNo'];
	
	//get all the sub icat_midx value from stored procedure.
	$catList = executeSP("getSubCategory", $_lastCategoryNo);
	$sqlQuery->whereCaluse = "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	
	//get category path from current icat_midx.
	if(isZKError($path = executeSP("getCategoryPath", $_lastCategoryNo))) {
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

$strGet = "_cus_code=" . $_cus_code;

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
	printf("it['%s'] = ['%s','%s','%s','%s','%s'];\n",
		addslashes($rows[1]), //it_code
		addslashes($rows[1]), //it_code
		addslashes($rows[0]), //icat_midx
		addslashes($rows[2]), //it_model_no
		addslashes($rows[3]), //it_type
		addslashes($rows[4]) //it_desc
	); 
}
?>

function fillItem(idx) {
	var f = window.document.frmCreateItem;

	f._icat_midx.value	 = it[idx][1];
	f._it_code.value 	 = it[idx][0];
	f._it_model_no.value = it[idx][2];
	f._it_type.value	 = it[idx][3];
	f._it_desc.value	 = it[idx][4];
	f._biit_unit_price.focus();

}

//Wrapper function. It call opener's function.
function createNewItem() {
	var f = window.document.frmCreateItem;

	if (f._it_code.value.length <= 0) {
		alert("Please select the code first");
		f.it_code.focus();
		return;
	} else if(f._biit_unit_price.value == 0) {
		alert("please input unit price");
		f._biit_unit_price.focus();
		return;
	} else if(f._biit_qty.value == 0) {
		alert("please input qty");
		f._biit_qty.focus();
		return;
	}  else if (isNaN(removecomma(f._biit_unit_price.value))) {
		alert("You can enter only number");
		f._biit_unit_price.value = "";
		f._biit_unit_price.focus();
		 return false;
	} else if(isNaN(removecomma(f._biit_qty.value))) {
		alert("You can enter only number");
		f._biit_qty.value = "";
		f._biit_qty.focus();
		return false;
	}

	f._biit_unit_price.value = removecomma(f._biit_unit_price.value);
	f._biit_qty.value 		 = removecomma(f._biit_qty.value);
	f._biit_remark.value	 = f._biit_remark.value;

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
<strong>APOTIK [<?php echo $_cus_code?>] CURRENT INVENTORY</strong>
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
			<button name="btnSearchByCat" class="input_sky"><img src="../../_images/search.gif"> Search</button>
		</form>
		</td>
	</tr>
	<tr>
		<td align="right" colspan="2">
			<form name="frmSrarchByKeyword" method="get">
			<input type="hidden" name="_cus_code" value="<?php echo $_cus_code?>">
			Search:
			<select name="searchBy">
				<option value="code_no">CODE</option>
				<option value="model_no" <?php echo (isset($_GET['searchBy']) && $_GET['searchBy'] == "model_no") ? "selected":""?>>MODEL</option>
				<option value="desc" <?php echo (isset($_GET['searchBy']) && $_GET['searchBy'] == "desc") ? "selected":""?>>DESCRIPTION</option>
			</select>
			<input type="text" name="txtKeyword" size="15" class="fmt"
			value="<?php echo isset($_GET['txtKeyword']) ? $_GET['txtKeyword'] : ""?>" accesskey="k">
				<button name="btnSearchByKeyword" class="input_sky" onClick="searchByKeyword();" style="height:19px">Search</button>
				<button name="btnAll" class="input_sky" style="height:19px;width:35px" onClick="window.location.href = '<?php echo HTTP_DIR . "apotik/billing/" ?>p_list_item.php?_cus_code=<?php echo $_cus_code?>'">All</button>
			</form>
			<script language="javascript" type="text/javascript">
				window.document.frmSearch.btnSearchByCat.onclick = function() {
					var o = window.document.frmSearch.all.tags('SELECT');
					for (var i = 2; i >=0; i--) {
						if (o[i].value != 0) {
							window.location.href = "?lastCategoryNo=" + o[i].value + "&_cus_code=" + '<?php echo $_cus_code?>';
							break;
						}
					}
				}
			</script>
		</td>
	</tr>
	</table>
	<table width="100%" class="table_box">
	  <tr>
		<th width="3%" rowspan="2">No</th>
		<th width="10%" rowspan="2">CODE</th>
		<th width="30%">MODEL NO</th>
		<th>DESC</th>
	  </tr>
	</table>
	<div style="height:420; overflow-y:scroll">
	<table width="100%" class="table_c">
<?php
pg_result_seek($result, 0);
while ($column =& fetchRowAssoc($result)) {
?>
	  <tr>
		<td><?php echo ++$oPage->serial ;?></td>
		<td><a href="javascript:fillItem('<?php echo $column['it_code'];?>')"><?php echo $column['it_code']?></a></td>
		<td><?php echo $column['it_model_no']?></td>
		<td><?php echo cut_string($column['it_desc'],30)?></td>
	  </tr>
	  <?php
}//end repeat rows
?>
	</table>
	</div>
	<table width="100%" cellpadding="0" cellspacing="2" border="0">
	  <tr>
		<td align="center"><?php echo $oPage->putPaging();?></td>
	  </tr>
	</table>
	<form name="frmCreateItem">
	<table width="100%" class="table_box">
		<tbody>
			<tr>
				<th width="8%">CODE</th>
				<th width="12%">ITEM NO</th>
				<th width="25%">DESCRIPTION</th>
				<th width="12%">UNIT PRICE</th>
				<th width="8%">QTY</th>
				<th>REMARK</th>
			</tr>
			<tr>
				<td><input type="text" name="_it_code" style="width:100%" class="req" readonly>
				</td>
				<td>
					<input type="hidden" name="_icat_midx">
					<input type="hidden" name="_it_type">
					<input type="text" name="_it_model_no" style="width:100%" class="fmt" readonly>
				</td>
				<td><input type="text" name="_it_desc" style="width:100%" class="fmt" readonly></td>
				<td><input type="text" name="_biit_unit_price" style="width:100%" class="reqn" onKeyUp="formatNumber(this, 'dot');"></td>
				<td><input type="text" name="_biit_qty" style="width:100%" class="reqn" onKeyUp="formatNumber(this, 'dot');" onKeyPress="if(window.event.keyCode == 13) createNewItem()"></td>
				<td><input type="text" name="_biit_remark" class="fmt" style="width:100%" onKeyPress="if(window.event.keyCode == 13) createNewItem()"></td>
			</tr>
			<tr>
			<td colspan="6" align="right">
				<button class="input_sky" onClick="createNewItem()" accesskey="a"><img src="../../_images/add.png" width="13"> ADD</button>
				<button onClick="window.close();" class="input_sky">CLOSE</button></td>
			</tr>
		</tbody>
	</table>
	</form>
	<!--END: BODY-->
</body>
</html>