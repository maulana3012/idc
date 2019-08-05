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
?>
<html>
<head>
<title>ITEMS LIST</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="text/javascript" type="text/javascript" src="../../_script/aden.js"></script>
<script language="text/javascript" type="text/javascript" src="../../_script/searchSuggest/ajax_search.js"></script>
<script language='javascript' type='text/javascript'>
function createNewItem() {
	var f = window.document.frmCreateItem;

	if (f._wh_it_code.value.length <= 0) {
		alert("Please select the code first");
		f.txtSearch.focus();
		return;
	} else if (f._wh_it_code.value.length <= 0) {
		alert("Please select the code first");
		f.txtSearch.focus();
		return;
	} else if (f._wh_it_qty.value.length <= 0) {
		alert("Please fill the qty");
		f._wh_it_qty.focus();
		return;
	} else if (f._wh_it_qty.value <= 0) {
		alert("Qty must be more than 0");
		f._wh_it_qty.focus();
		return;
	} else if (parseFloat(removecomma(f._wh_it_qty.value)) > parseFloat(removecomma(f._est_stock.value))) {
		alert("Qty value can't more than estimated stock value");
		f._wh_it_qty.value = addcomma(removecomma(f._est_stock.value));
		f._wh_it_qty.focus();
		return;
	} 

	f._wh_it_qty.value 	 	= removecomma(f._wh_it_qty.value);
	f._wh_it_function.value = removecomma(f._wh_it_function.value);
	f._wh_it_remark.value	= f._wh_it_remark.value;
	f.submit();
}

function initPage() {
	window.document.frmSearch.txtSearch.focus();
}
</script>
</head>
<body style="margin:8pt" onLoad="initPage()">
<strong>
[<font color="blue"><?php echo strtoupper($currentDept) ?></font>] CURRENT STOCK (STEP 1 / 2)<br />
<small>* Printed for customer item list</small>
</strong>
<hr>

<form name="frmSearch" id="frmSearch">
<input type="hidden" name="_dept" value="<?php echo $department?>">
<table width="100%" class="table_box">
	<tr>
		<th width="15%">SEARCH</th>
		<td>
			<select name="cboSeachBy">
				<option value="it_code">ITEM CODE</option>
				<option value="it_model_no" selected>MODEL NO</option>
			</select>
		</td>
		<td width="70%">
			<input type="text" id="txtSearch" name="txtSearch" alt="Search Model No" style="width:100%" class="fmt" onkeyup="searchSuggest();" autocomplete="off" onKeyPress="if(window.event.keyCode == 13) searchStock(window.document.frmSearch._dept.value, window.document.frmSearch.txtSearch.value);" />
			<div id="search_suggest"></div>
		</td>
		<td>
			<button name="btnSetStock" class='input_sky' onclick="javascript:searchStock(window.document.frmSearch._dept.value, window.document.frmSearch.txtSearch.value)"> &nbsp; <img src="../../_images/icon/search_mini.gif" alt="Search"> &nbsp; </button>
		</td>
	</tr>
</table>
</form>

<form name="frmCreateItem" method="POST" action="./p_list_item_2.php">
<input type="hidden" name="p_mode" value='item_info'>
<input type="hidden" name="_cus_code" value="<?php echo isset($_GET["_cus_code"]) ? $_GET["_cus_code"] : "" ?>">
<table width="100%" class="table_box">
	<tr>
		<th rowspan="2" width="8%">CODE</th>
		<th rowspan="2" width="20%">ITEM NO</th>
		<th rowspan="2" colspan="2">DESCRIPTION</th>
		<th colspan="2" width="20%">STOCK</th>
	</tr>
	<tr>
		<th width="12%">REAL</th>
		<th width="12%">EST</th>
	</tr>
	<tr>
		<td>
			<input type="hidden" name="_wh_it_icat_midx">
			<input type="hidden" name="_wh_it_type">
			<input type="text" name="_wh_it_code" style="width:100%" class="fmt" readonly>
		</td>
		<td><input type="text" name="_wh_it_model_no" style="width:100%" class="fmt" readonly></td>
		<td colspan="2"><input type="text" name="_wh_it_desc" style="width:100%" class="fmt" readonly></td>
		<td><input type="text" name="_real_stock" style="width:100%" class="fmtn" readonly></td>
		<td><input type="text" name="_est_stock" style="width:100%" class="fmtn" readonly></td>
	</tr>
</table>
<table width="100%" class="table_box">
	<tr>
		<th align="right">BOOKING QTY &nbsp;</th>
		<td align="right" width="25%"><input type="text" name="_wh_it_qty" size="5" class="reqn" onKeyUp="formatNumber(this, 'dot');" onKeyPress="if(window.event.keyCode == 13) createNewItem()"></td>
	</tr>
	<tr>
		<th align="right">FUNCTION (x) &nbsp;</th>
		<td align="right"><input type="text" name="_wh_it_function" size="5" class="reqn" onKeyUp="formatNumber(this, 'dot');" onKeyPress="if(window.event.keyCode == 13) createNewItem()"></td>
	</tr>
	<tr>
		<th align="right">REMARK &nbsp;</th>
		<td align="right"><input type="text" name="_wh_it_remark" class="fmt" onKeyPress="if(window.event.keyCode == 13) createNewItem()"></td>
	</tr>
</table><br />
<div align="right">
	<button name='btnNext' class='input_sky' style='width:60px;height:25px;' onclick='createNewItem()'><img src="../../_images/icon/next.gif" width="15px" align="middle" alt="Next"></button>&nbsp;
	<button name='btnClose' class='input_sky' style='width:60px;height:25px;' onclick='window.close()'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Close pop-up"></button>
</div>

</form>
</body>
</html>