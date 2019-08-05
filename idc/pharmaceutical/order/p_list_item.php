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

$_cus_code 	= isset($_GET['_cus_code']) ? trim($_GET['_cus_code']) : "";
$strGet		= "";

//DEFAULT PROCESS
$sqlQuery = new strSelect("
SELECT
  it.it_code,
  it.it_model_no,
  it.it_type,
  it.it_desc,
  ".ZKP_SQL."_getUserPrice(it.it_code, CURRENT_DATE) AS user_price,
  inv.inv_jk,
  to_char(inv.inv_sales_updated,'dd-Mon-YY') AS sales_updated,
  (inv.inv_jk + inv.inv_jo - inv.inv_return - inv.inv_sales) AS r_stock,
  (SELECT sum(deit_jo_qty)
	FROM ".ZKP_SQL."_tb_delivery_item AS deit
	WHERE
	  deit.it_code = it.it_code AND
	  deit.cus_code = inv.cus_code AND
	  deit.deit_date > inv.inv_sales_updated) AS additional_sales_qty
FROM
  ".ZKP_SQL."_tb_item AS it LEFT JOIN ".ZKP_SQL."_tb_apotik_inv AS inv ON(it.it_code = inv.it_code AND inv.cus_code='{$_cus_code}')");

//Search Option 1 : by Category
if(isset($_GET['lastCategoryNo']) && $_GET['lastCategoryNo'] > 0) {
	$_lastCategoryNo = $_GET['lastCategoryNo'];
	
	//get all the sub icat_midx value from stored procedure.
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_lastCategoryNo);
	$sqlQuery->whereCaluse = "it.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	
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
	$sqlQuery->setWhere("%s ILIKE '%%%s%%'", array("it.it_model_no" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString() . "searchBy=model_no";
}

//Search Option 3 : by description
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "desc") {
	$sqlQuery->setWhere("%s ILIKE '%%%s%%'", array("it.it_desc" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString(). "searchBy=desc";
}

//Search Option 4 : by Code
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "code_no") {
	$sqlQuery->setWhere("%s ILIKE '%%%s%%'", array("it.it_code" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString(). "searchBy=code_no";
}

$findStrGet = str_replace('curpage=','',getQueryString());
$strGet		= $findStrGet . "&_cus_code=$_cus_code";

$sqlQuery->setOrderBy("it.it_code");
if(isZKError($result =& query($sqlQuery->getSQL()))) {
	$M->goErrorPage($result,  "javascript:window.close();");
}

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
	printf("it['%s'] = ['%s', '%s', '%s', '%s', %d, '%s', %d, %d];\n",
		addslashes($rows[0]), //code from query
		addslashes($rows[0]),
		addslashes($rows[1]), //model no
		addslashes($rows[3]), //desc
		empty($rows[4])?"0":$rows[4], //unit price
		empty($rows[5])?"0":$rows[5], //JK
		$rows[6], //Sales Date
		$rows[7], //Real stock on sales Date
		$rows[8] //additional sales (sum of JO from sales date to today)
		); 
}
?>

function fillItem(idx) {
	var f = window.document.frmCreateItem;

	f._it_code.value = it[idx][0];
	f._it_model_no.value = it[idx][1];
	f._it_desc.value = it[idx][2];
	f._it_unit_price.value = numFormatval(it[idx][3],0);
	f._it_qty.focus();
}

//Wrapper function. It call opener's function.
function createNewItem() {
	var f = window.document.frmCreateItem;
	var d = parseDate(f._it_delivery.value, 'prefer_euro_format');
	
	if (f._it_code.value.length <= 0) {
		alert("Please select the code first");
		f.it_code.focus();
		return;
		
	} else if(f._it_qty.value == 0) {
		alert("please input order qty");
		f._it_qty.focus();
		return;
		
	} else if (isNaN(removecomma(f._it_unit_price.value))) {
		alert("You can enter only number");
		f._it_unit_price.value = "";
		f._it_unit_price.focus();
		 return false;
	} else if(isNaN(removecomma(f._it_qty.value))) {
		alert("You can enter only number");
		f._it_qty.value = "";
		f._it_qty.focus();
		return false;
	} else if (d == null) {
		alert("You must be input date with proper format")
		f._it_delivery.value = "";
		f._it_delivery.focus();
		return false;
	}

	f._it_qty.value = removecomma(f._it_qty.value);
	f._it_unit_price.value = removecomma(f._it_unit_price.value);
	f._it_delivery.value = formatDate(d, "d-NNN-yyyy");
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
<strong>
[<font color="blue"><?php echo strtoupper($currentDept) ?></font>] CURRENT INVENTORY<br />
<small>* Inventory information in apotik <?php echo $_cus_code?></small>
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
		<form name="frmSrarchByKeyword" method="get">
		<input type="hidden" name="_cus_code" value="<?php echo $_cus_code?>">
		Search:
		<select name="searchBy">
			<option value="code_no">CODE</option>
			<option value="model_no" <?php echo (isset($_GET['searchBy']) && $_GET['searchBy'] == "model_no") ? "selected":""?>>MODEL</option>
			<option value="desc" <?php echo (isset($_GET['searchBy']) && $_GET['searchBy'] == "desc") ? "selected":""?>>DESCRIPTION</option>
		</select>
		<input type="text" name="txtKeyword" size="15" class="fmt" value="<?php echo isset($_GET['txtKeyword']) ? $_GET['txtKeyword'] : ""?>" accesskey="k">
		</form>
	</td>
	<th width="21%">
		<a href="javascript:searchByKeyword()"><img src="../../_images/icon/search_mini.gif" alt="Search"></a>&nbsp;
		<a href="javascript:document.location.href='p_list_item.php?_cus_code=<?php echo $_cus_code?>'"><img src="../../_images/icon/list_mini.gif" alt="Show all item"></a>&nbsp;
		<a href="javascript:document.location.href='p_list_item_1.php?_cus_code=<?php echo $_cus_code?>'"><img src="../../_images/icon/up.gif" width="15px" alt="Back"></a>
	</th>
</tr>
</table><br />
<script language="javascript" type="text/javascript">
	function searchByCat() {
		var o = window.document.frmSearch.all.tags('SELECT');
		for (var i = 2; i >=0; i--) {
			if (o[i].value != 0) {
				window.location.href = "?lastCategoryNo=" + o[i].value + "&_cus_code=" + '<?php echo $_cus_code?>';
				break;
			}
		}
	}
</script>
<table width="100%" class="table_box">
  <tr>
	<th width="3%" rowspan="2">No</th>
	<th width="8%" rowspan="2">CODE</th>
	<th>ITEM NO</th>
	<th width="17%" rowspan="2">@PRICE</th>
	<th width="6%" rowspan="2">JK<br></th>
	<th width="12%">STOCK</th>
  	<th width="10%">LAST SALES</th>
	<th width="12%" rowspan="2">EST/<br>SALES</th>
	<th width="12%" rowspan="2">EST/<br>STOCK</th>
  </tr>
</table>
<div style="height:430; overflow-y:scroll">
<table width="100%" class="table_c">
<?php
pg_result_seek($result, 0);
while ($column =& fetchRowAssoc($result)) {
?>
  <tr>
	<td width="3%"><?php echo ++$oPage->serial ;?></td>
	<td width="7%"><?php echo $column['it_code']?></td>
	<td width="13%"><?php echo cut_string($column['it_model_no'], 10)?></td>
	<td align="right" width="13%"><?php echo "Rp. ". number_format((double)$column['user_price'])?></td>
	<td align="right" width="7%"><?php echo number_format((double)$column['inv_jk'])?></td>
	<td align="right" width="7%"><?php echo number_format((double)$column['r_stock'])?></td>
	<td align="right" width="10%"><?php echo $column['sales_updated']?></td>
	<td align="right" width="8%"><?php echo number_format((double)$column['additional_sales_qty'])?></td>
	<td align="right" width="8%"><?php echo number_format((double)$column['r_stock'] - $column['additional_sales_qty'])?></td>
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