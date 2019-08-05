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
if($currentDept == 'apotik') {
	if(!isset($_GET['_cug_code']) || $_GET['_cug_code'] == "")
		die("<script language=\"javascript1.2\">window.close();</script>");
}

$_cug_code 		= trim($_GET['_cug_code']);
$period_from 	= isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", time() - 5184000);
$period_to 		= isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", time());
$strGet		= "";

//DEFAULT PROCESS
if($currentDept == 'apotik') {
	$sqlQuery = new strSelect("
	SELECT
	  cus_code, 
	  cus_full_name, 
	  sl_date AS date,
	  to_char(sl_date, 'dd-Mon-yy') AS sl_date, 
	  sl_faktur_no,
	  sl_lop_no,
	  sum ((sl_payment_price*sl_qty)+round(((sl_payment_price*sl_qty)/10),0) ) AS amount
	FROM
	  ".ZKP_SQL."_tb_customer_group 
	  join ".ZKP_SQL."_tb_customer using(cug_code)
	  join ".ZKP_SQL."_tb_sales_log using(cus_code)
	  join ".ZKP_SQL."_tb_item using(it_code)
	");
	$sqlQuery->whereCaluse = "cug_code='$_cug_code' AND sl_date BETWEEN DATE '$period_from' AND '$period_to' AND sl_dept='$department'";
} else {
	$sqlQuery = new strSelect("
	SELECT
	  cus_code, 
	  cus_full_name, 
	  sl_date AS date,
	  to_char(sl_date, 'dd-Mon-yy') AS sl_date, 
	  sl_faktur_no,
	  sl_lop_no,
	  sum ((sl_payment_price*sl_qty)+round(((sl_payment_price*sl_qty)/10),0) ) AS amount
	FROM
	  ".ZKP_SQL."_tb_customer
	  join ".ZKP_SQL."_tb_sales_log using(cus_code)
	  join ".ZKP_SQL."_tb_item using(it_code)
	");
	$sqlQuery->whereCaluse = "sl_date BETWEEN DATE '$period_from' AND '$period_to' AND sl_dept='$department'";
}

//Search Option 1 : by faktur no
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "faktur_no") {
	$sqlQuery->setWhere("AND %s ILIKE '%%%s%%'", array("sl_faktur_no"=>"txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString() . "searchBy=faktur_no";
}

//Search Option 2 : by LOP no
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "lop_no") {
	$sqlQuery->setWhere("AND %s ILIKE '%%%s%%'", array("sl_lop_no" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString(). "searchBy=lop_no";
}

//Search Option 3 : by Customer Code
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "cus_code") {
	$sqlQuery->setWhere("AND %s ILIKE '%%%s%%'", array("cus_code" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString(). "searchBy=cus_code";
}

$strGet = "_cug_code=$_cug_code";
$sqlQuery->setGroupBy("cus_code, cus_full_name, date, sl_date, sl_faktur_no, sl_lop_no");
$sqlQuery->setOrderBy("date, cus_code");
if(isZKError($result =& query($sqlQuery->getSQL()))) $M->goErrorPage($result,  "javascript:window.close();");

//Total Rows
$numRow = numQueryRows($result);

//Declare Paging
$oPage = new strPaging($sqlQuery->getSQL(), $numRow);
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
<title>Sales Report</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="text/javascript" type="text/javascript" src="../../_script/aden.js"></script>
<script language='javascript' type='text/javascript'>
function addSalesList() {

	var arrInput = new Array(0);
	var arrInputValue = new Array(0);
	var arrRow =  new Array(0);
	var oCheck	= window.document.frmList.tags("INPUT");
	var count	= window.rowPosition.rows.length;

	var j = 1;
	for (var i=0; i<count; i++) {
		var oRow	= window.rowPosition.rows(i);

		if(oCheck[j].type == "checkbox" && oCheck[j].checked) {
			arrRow[arrRow.length] = j;
		}
		j++;
	}

	//adding input into form
	var j = 1;
	for (var i=0; i<count; i++) {
		if(oCheck[j].type == "checkbox" && oCheck[j].checked) {
			arrInput.push(arrInput.length);
			arrInputValue.push("");

			document.getElementById('salesList').innerHTML="";
			for (k=0; k<arrInput.length; k++) {
				var oRow	= window.rowPosition.rows(arrRow[k]-1);
				document.getElementById('salesList').innerHTML+=createInput('sl_date[]', oRow.cells(1).innerText);
				document.getElementById('salesList').innerHTML+=createInput('cus_code[]', oRow.cells(2).innerText);
				document.getElementById('salesList').innerHTML+=createInput('cus_full_name[]', oRow.cells(3).innerText);
				document.getElementById('salesList').innerHTML+=createInput('faktur_no[]', oRow.cells(4).innerText);
				document.getElementById('salesList').innerHTML+=createInput('lop_no[]', oRow.cells(5).innerText);
				document.getElementById('salesList').innerHTML+=createInput('amount[]', removecomma(oRow.cells(6).innerText));
				document.getElementById('salesList').innerHTML+=createInput('null[]', '');
			}
			function createInput(name,value) {
				return "<input type='hidden' name='"+name+"' value='"+value+"'>";
			}
		}
		j++;
	}

}

function searchSales() {
	var o = window.document.frmSearch;

	if(o.searchBy.value != '' && o.txtKeyword.value <=0 ) {
		alert("Please insert the Faktur or LOP keyword");
		o.txtKeyword.focus();
	} else if(validPeriod(o.period_from, o.period_to)){
		var d1 = Date.parse(parseDate(o.period_from.value, 'prefer_euro_format'));
		var d2 = <?php echo time() * 1000;?>;
		var d = Math.round((d2 - d1) / 86400000);

		if(d > 180) {
			alert("Period cannot more than 180 days from today"); return;
		}
		o.submit();
	}
}

function updateAmount() {
	var f		= window.document.frmList;
	var e 		= window.document.frmList.elements;
	var oCheck	= window.document.frmList.tags("INPUT");
	var count	= window.rowPosition.rows.length;
	var amount	= 0;
	var k = 1;

	for (var i=0; i<count; i++) {
		var oRow	= window.rowPosition.rows(i);
		if(oCheck[k].type == "checkbox" && oCheck[k].checked) {
			amount	= amount + parseFloat(removecomma(oRow.cells(6).innerText));
		}

		//highlight row
		var orig_color;
		if(oCheck[k].checked) {
			orig_color = oRow.style.backgroundColor;
			for(var j=0; j<7; j++) { oRow.cells(j).style.backgroundColor = 'lightyellow'; }
		} else {
			for(var j=0; j<7; j++) { oRow.cells(j).style.backgroundColor = 'white'; }
		}
		k++;
	}
	var para = document.getElementById('amountChk');
	para.lastChild.nodeValue = numFormatval(amount+'',0);

	addSalesList();
}

function createNewItem() {
	var f = window.document.frmCreateItem;
	var para = document.getElementById('amountChk');
	var amount = (para.lastChild.nodeValue);
	if(amount <= 0) {
		alert("Please choose the sales data");
		return;
	}

	window.opener.createItem();
	window.location.reload();
}

function initPage() {
	updateAmount();
	var count	= window.rowPosition.rows.length;
	if(count > 20) {window.document.frmList.chkAll.disabled=true;}
}
</script>
</head>
<body style="margin:8pt" onload="initPage()">
<form name="frmSearch" method="GET">
<input type="hidden" name="_cug_code" value="<?php echo $_cug_code ?>">
<table width="100%" class="table_box">
	<tr>
		<td rowspan="2" width="50%">
			<font size="2e.m" style="font-weight:bold">
			[<font color="blue">SALES REPORT</font>]<br />
			<small>* Printed for customer sales list</small>
			</font>
			<hr>
		</td>
		<td align="right">
			Period : 
			<input type="text" name="period_from" size="10" class="fmtd" value="<?php echo $period_from; ?>" onKeyPress="if(window.event.keyCode == 13) searchSales()">&nbsp;
			<input type="text" name="period_to" size="10" class="fmtd"  value="<?php echo $period_to; ?>" onKeyPress="if(window.event.keyCode == 13) searchSales()">
		</td>
		<th width="7%" rowspan="2">
			<a href="javascript:searchSales()"><img src="../../_images/icon/search_mini.gif" alt="Search"></a>&nbsp;
			<a href="javascript:document.location.href='p_list_sales.php?_cug_code=<?php echo $_cug_code?>'"><img src="../../_images/icon/list_mini.gif" alt="Show all"></a>
		</th>
	</tr>
	<tr>
		<td align="right">
			Search By : 
			<select name="searchBy">
				<option value=""></option>
				<option value="faktur_no" <?php echo (isset($_GET['searchBy']) && $_GET['searchBy'] == "faktur_no") ? "selected":""?>>Faktur No</option>
				<option value="lop_no" <?php echo (isset($_GET['searchBy']) && $_GET['searchBy'] == "lop_no") ? "selected":""?>>LOP No</option>
				<option value="cus_code" <?php echo (isset($_GET['searchBy']) && $_GET['searchBy'] == "cus_code") ? "selected":""?>>Cus Code</option>
			</select>
			<input type="text" name="txtKeyword" size="15" class="fmt" value="<?php echo isset($_GET['txtKeyword']) ? $_GET['txtKeyword'] : ""?>" onKeyPress="if(window.event.keyCode == 13) searchSales()">
		</td>
	</tr>
</table><br />
</form>
<form name="frmCreateItem">
<span id="salesList"><br /></span>
</form>
<form name="frmList">
<table width="100%" class="table_box">
  <tr height="25px">
	<th width="4%"><input type="checkbox" name="chkAll"></th>
	<th width="10%">DATE</th>
	<th width="8%">CODE</th>
	<th>CUSTOMER NAME</th>
	<th width="10%">FAKTUR/<br />BILL NO</th>
	<th width="10%">LOP NO</th>
	<th width="15%">AMOUNT<br />(+VAT)</th>
  </tr>
</table>
<div style="height:390; overflow-y:scroll">
<table width="100%" class="table_c">
  <tbody id="rowPosition">
<?php
$amount = 0;
while ($column =& fetchRowAssoc($result)) { 
?>
  <tr>
	<td width="2%"><input type="checkbox" name="chk[]" onclick="updateAmount()"></td>
	<td width="12%"><?php echo $column['sl_date']?></td>
	<td width="5%"><?php echo $column['cus_code']?></td>
	<td><?php echo $column['cus_full_name']?></td>
	<td width="12%"><?php echo $column['sl_faktur_no']?></td>
	<td width="12%"><?php echo $column['sl_lop_no']?></td>
	<td width="12%" align="right"><?php echo number_format((double)$column['amount'])?></td>
  </tr>
<?php 
	$amount += $column['amount'];
} 
?>
  </tbody>
</table>
</form>
</div>
<table width="100%" class="table_box">
	<tr>
		<td>
			<span class="comment"><i><?php print ($numRow == 1) ? "1 row" : "$numRow rows"; ?> found</i></span>
		</td>
		<td align="right"><b>TOTAL</b></td>
		<td width="12%"><input type="text" name="totalAmount" class="fmtn" style="width:100%" value="<?php echo number_format((double)$amount) ?>" readonly></td>
		<td width="2%"></td>
	</tr>
</table><br />
<script language="javascript" type="text/javascript">
	//Check or uncheck all rows
	var orig_color;
	window.document.frmList.chkAll.onclick = function() {
		var oCheck = window.document.frmList.tags("INPUT");
		for (var i = 0; i < oCheck.length; i++) {
			if (oCheck[i].type == "checkbox" && oCheck[i].name == "chk[]") {
				oCheck[i].checked = window.document.frmList.chkAll.checked;
			}
		}
		updateAmount();
	}
</script>
<table width="100%" class="table_box">
	<tr>
		<td>Current check amount sales is Rp. <span id="amountChk" style="font-weight:bold;color:#000">0</span></td>
		<td width="10%">
			<button name='btnSummarize' class='input_sky' style="width:70px;height:25px" onclick='createNewItem()'>Add</button>&nbsp;
			<button name='btnClose' class='input_sky' style="width:70px;height:25px" onclick='window.close()'>Close</button>
		</td>
	</tr>
</table>
</body>
</html>