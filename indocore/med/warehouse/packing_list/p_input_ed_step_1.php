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
ckperm(ZKP_INSERT, "javascript:window.close();");

//Check PARAMETER
if(!isset($_GET['_pl_idx']) && !isset($_GET['_code']) && !isset($_GET['_max_qty']))
	die("<script language=\"javascript1.2\">window.close();</script>");

$_pl_idx  = trim($_GET['_pl_idx']);
$_code	  = trim($_GET['_code']);
$_max_qty = trim($_GET['_max_qty']);
$_idx  = trim($_GET['_idx']);

//DEFAULT PROCESS
$sql = "SELECT * FROM ".ZKP_SQL."_tb_pl_item WHERE it_code = '$_code' AND pl_idx = $_pl_idx";
if(isZKError($result =& query($sql)))
	die("<script language=\"javascript1.2\">window.close();</script>");
$column =& fetchRowAssoc($result);
?>
<html>
<head>
<title>SET EXPIRED DATE</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="text/javascript" type="text/javascript" src="../../_script/aden.js"></script>
<script language="javascript" type="text/javascript" src="../../_script/js_category.php"></script>
<script language="javascript" type="text/javascript">
function updateAmount(){

	var f			= window.document.frmInsert;
	var numItem		= window.ed_position.rows.length;
	var numInput	= 5;
	var idx_qty		= 11;
	var max_amount	= <?php echo $_max_qty ?>;
	var sumOfQty	= 0;

	var e = window.document.frmInsert.elements;

	for (var i = 0; i< numItem; i++) {
		var qty = parseFloat(removecomma(e(idx_qty+i*numInput).value));
		sumOfQty	+= qty;
	}
	f.totalQty.value = addcomma(sumOfQty);
	var remain		 = parseFloat(max_amount) - parseFloat(sumOfQty);
	f._qty.value 	 = remain;
}

function initPage() {
	updateAmount();
	window.document.frmInsert._date.focus();
}
</script>
</head>
<body style="margin:8pt" onload="initPage()">
<!--START: BODY-->
<table width="100%" class="table_box">
	<tr>
		<th width="20%">ITEM</th>
		<td><font color="#446fbe" style="font-weight:bold">[<?php echo trim($column["it_code"]) ?>]</font> <?php echo $column["plit_item"] ?></td>
	</tr>
	<tr>
		<th>DESC</th>
		<td><?php echo $column["plit_desc"] ?></td>
	</tr>
</table><br />
<form name="frmInsert" method="POST" action="./p_input_ed_step_2.php">
<input type='hidden' name='p_mode' value='ed_info'>
<input type='hidden' name='_pl_idx' value='<?php echo $_pl_idx ?>'>
<input type='hidden' name='_idx' value='<?php echo $_idx ?>'>
<input type='hidden' name='_it_code' value='<?php echo trim($column["it_code"]) ?>'>
<input type='hidden' name='_it_name' value='<?php echo $column["plit_item"] ?>'>
<input type='hidden' name='_it_desc' value='<?php echo $column["plit_desc"] ?>'>
<input type='hidden' name='_num_row'>
<table width="75%" class="table_box">
	<tr>
		<th width="65%">E/D DATE</th>
		<th width="20%">QTY</th>
		<th width="15%"></th>
	</tr>
	<tr>
		<td><input type="text" name="_date" class="fmtd" style="width:100%" onKeyPress="if(window.event.keyCode==13) {checkForm();window.document.frmInsert._date.focus();}"></td>
		<td><input type="text" name="_qty" class="fmtn" style="width:100%" value="<?php echo number_format($_max_qty) ?>" onBlur="updateAmount" onKeyUp="formatNumber(this,'dot')" onKeyPress="if(window.event.keyCode==13) {checkForm();window.document.frmInsert._date.focus();}"></td>
		<td align="center"><button name="btnAddDate" class="input_sky" onClick="checkForm()">ADD</button></td>
	</tr>
	<tbody id="ed_position">
	</tbody>
</table>
<table width="75%" class="table_box">
	<tr>
		<th width="65%" align="right">TOTAL</th>
		<th width="20%"><input type="text" name="totalQty" class="fmtn" style="width:100%" readonly></th>
		<th width="15%"></th>
	</tr>
</table><br />
</form>
<center>
	<button name="btnNext" class="input_sky">NEXT</button> &nbsp; &nbsp;
	<button name="btnCancel" class="input_sky">CANCEL</button>
</center>
<script language="javascript" type="text/javascript">
function checkForm() {

	var f = window.document.frmInsert;

	var max_amount	= <?php echo $_max_qty ?>;
	var amount		= parseFloat(removecomma(f._qty.value)) + parseFloat(removecomma(f.totalQty.value));
	var d			= parseDate(f._date.value, 'prefer_euro_format');
	var count 		= ed_position.rows.length;

	if (d == null) {
		alert("column EXPIRED DATE must be inputed by proper format");
		f._date.value = '';
		f._date.focus();
		return;
	} else if(trim(f._date.value).length <= 0 || trim(f._date.value).length <= 0) {
		alert("Please insert EXPIRED DATE");
		f._date.focus();
		return;
	} else if(f._qty.value == 0) {
		alert("Please input qty");
		f._qty.focus();
		return;
	} else if(isNaN(removecomma(f._qty.value))) {
		alert("You can enter only number");
		f._qty.value = "";
		f._qty.focus();
		return;
	} else if(amount >  max_amount) {
		alert("Max qty for this item is "+max_amount+"\nPlease check qty again");
		f._date.value	= "";
		f._qty.value	= "";
		f._qty.focus();
		return;
	}

	for (var i=0; i<count; i++) {
		var oRow = window.ed_position.rows(i);
		if (oRow.cells(0).innerText == formatDate(d, 'NNN-yyyy')) {
			alert("Same expired date already exist!");
			f._date.value	= "";
			return false;
		}
	}
	addNewRow();
}

function addNewRow() {
	var f = window.document.frmInsert;
	var d = parseDate(f._date.value, 'prefer_euro_format');
	var oTR = window.document.createElement("TR");
	var oTD = new Array();
	var oTextbox = new Array();

	for (var i=0; i<5; i++) {
		oTD[i] = window.document.createElement("TD");
		oTextbox[i] = window.document.createElement("INPUT");
		oTextbox[i].type = "text";

		switch (i) {
			case 0: //DATE LAYOUT
				oTD[i].innerText   = formatDate(d, 'NNN-yyyy');
				oTextbox[i].type = "hidden";
				oTextbox[i].name = "_layout_date[]";
				oTextbox[i].value		= formatDate(d, 'NNN-yyyy');
				f._date.value	= '';
				break;

			case 1: //QTY
				oTD[i].innerText = addcomma(removecomma(f._qty.value));
				oTD[i].align	 = 'right';
				oTextbox[i].type = "hidden";
				oTextbox[i].name = "_exp_qty[]";
				oTextbox[i].value	= f._qty.value;
				f._qty.value	= '';
				break;

			case 2: //BUTTON
				oTD[i].align	= "center";
				oTextbox[i].type	= "button";
				oTextbox[i].name	= "btnDelDate";
				oTextbox[i].value	= "[ - ]";
				oTextbox[i].className = "fmt";
				oTextbox[i].onclick = function () {
					var oRow = this.parentElement.parentElement;
					window.ed_position.removeChild(oRow);
					updateAmount();
				}
				break;

			case 3: //IT CODE
				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_ed_it_code[]";
				oTextbox[i].value		= f._it_code.value;
				break;

			case 4: //DATE
				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_ed_date[]";
				oTextbox[i].value		= formatDate(d, '1-NNN-yyyy');
				break;
		}

		if (i<5) oTD[i].appendChild(oTextbox[i]);
		oTR.id = d;
		oTR.appendChild(oTD[i]);
	}
	window.ed_position.appendChild(oTR);
	f._date.focus();

	updateAmount();
}

	window.document.all.btnNext.onclick = function() {
		var max_amount	= <?php echo $_max_qty ?>;

		if (window.ed_position.rows.length == 0) {
			alert("You need to leave at least 1 expired date");
			return;
		} else if(window.document.frmInsert.totalQty.value < max_amount) {
			alert("You haven't completed the setting\nPlease check again");
			return;
		}
		window.document.frmInsert.submit();
	}

	window.document.all.btnCancel.onclick = function() {
		window.close();
	}

</script>
<!--END: BODY-->
</body>
</html>