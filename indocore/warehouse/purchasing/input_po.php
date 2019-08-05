<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*/
//REQUIRE
require_once "../../zk_config.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "input_po.php";

//PROCESS FORM
require_once APP_DIR . "_include/warehouse/tpl_process_po_local.php";
?>
<html>
<head>
<title>PT. INDOCORE PERKASA [MANAGEMENT SYSTEM]</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
function checkform(o) {

	if (window.rowPosition.rows.length == 0) {
		alert("You need to choose at least 1 item");
		return;
	}

	if (verify(o)) {
		if(confirm("Are you sure to save?")) {
			o.submit();
		}
	}
}

function fillCode(target) {

	if(target == 'supplier') {
		var file	= './p_list_supplier.php?_name=';
		var keyword = window.document.frmInsert._sp_code.value;
	} else if(target == 'forwarder') {
		var file	= './p_list_forwarder.php?_name=';
		var keyword = window.document.frmInsert._fw_code.value;
	}

	var x = (screen.availWidth - 400) / 2;
	var y = (screen.availHeight - 600) / 2;
	var win = window.open(
		file + keyword,
		target,
		'scrollbars,width=400,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

//Open window for search item
var wSearchItem;
function fillItem(){
	var x = (screen.availWidth - 580) / 2;
	var y = (screen.availHeight - 620) / 2;

	wSearchItem = window.open(
		'./p_list_item.php',
		'wSearchItem',
		'scrollbars,width=550,height=640,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wSearchItem.focus();
}

//It Called by child window window's name is wSearchItem
//Please see the p_list_item.php
function createItem() {

	var o	= window.document.frmInsert;
	var f2	= wSearchItem.document.frmCreateItem;
	var oTR = window.document.createElement("TR");

	var oTD = new Array();
	var oTextbox = new Array();
/*
	//Check has same CODE
	var count = rowPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.rowPosition.rows(i);
		if (trim(oRow.cells(0).innerText) == trim(f2.elements(0).value)) {
			alert("Same Item code already exist!");
			return false;
		}
	}
*/
	//If you add more cell
	// 1. increase tthe count as number of td
	// 2. add Case
	// the Cell order match with p_list_item.php field.
	for (var i=0; i<9; i++) {
		oTD[i] = window.document.createElement("TD");
		oTextbox[i] = window.document.createElement("INPUT");
		oTextbox[i].type = "text";

		switch (i) {
			case 0: // ITEM CODE
				oTD[i].innerText	= f2.elements[0].value;
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_it_code[]";
				oTextbox[i].value	= f2.elements[0].value;
				break;

			case 1: // ITEM NAME
				oTextbox[i].style.width	= "100%";
				oTextbox[i].className	= "fmt";
				oTextbox[i].name		= "_it_model_no[]";
				oTextbox[i].value		= f2.elements[1].value;
				oTextbox[i].readOnly	= "readOnly";
				break;

			case 2: // DESCRIPTION
				oTextbox[i].style.width	= "100%";
				oTextbox[i].className	= "fmt";
				oTextbox[i].name		= "_it_desc[]";
				oTextbox[i].value		= f2.elements[2].value;
				oTextbox[i].readOnly	= "readOnly";
				break;

			case 3: // ATTRIBUTE
				oTextbox[i].style.width	= "100%";
				oTextbox[i].className	= "fmt";
				oTextbox[i].name		= "_poit_unit[]";
				oTextbox[i].value		= f2.elements[3].value;
				break;

			case 4: // UNIT PRICE
				oTD[i].align			= "right";
				oTextbox[i].style.width = "100%";
				oTextbox[i].className	= "reqn";
				oTextbox[i].name		= "_poit_unit_price[]";
				oTextbox[i].value		= numFormatval(f2.elements[4].value+'',0);
				oTextbox[i].onblur 		= function() {updateAmount();}
				oTextbox[i].onkeyup		= function() {formatNumber(this, 'dot');}
				break;

			case 5: // QTY
				oTD[i].align			= "right";
				oTextbox[i].style.width = "100%";
				oTextbox[i].className	= "reqn";
				oTextbox[i].name		= "_poit_qty[]";
				oTextbox[i].value		= addcomma(f2.elements[5].value);
				oTextbox[i].onblur 		= function() {updateAmount();}
				oTextbox[i].onkeyup		= function() {formatNumber(this, 'dot');}
				break;

			case 6: //AMOUNT
				var amount 				= f2.elements[4].value * f2.elements[5].value;

				oTD[i].align			= "right";
				oTextbox[i].readOnly	= "readonly";
				oTextbox[i].name		= "_poit_amount[]";
				oTextbox[i].style.width = "100%";
				oTextbox[i].className	= "reqn";
				oTextbox[i].value		= numFormatval(amount+'',0);
				break;

			case 7: // REMARK
				oTextbox[i].style.width	= "100%";
				oTextbox[i].className	= "fmt";
				oTextbox[i].name		= "_poit_remark[]";
				oTextbox[i].value		= f2.elements[6].value;
				break;

			case 8: // DELETE
				oTD[i].innerHTML = "<a href=\"javascript:deleteItem('" + trim(f2.elements[0].value) + "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD[i].align = "center";
				break;
		}

		if (i!= 8) oTD[i].appendChild(oTextbox[i]);
		oTR.id = trim(f2.elements[0].value);
		oTR.appendChild(oTD[i]);
	}
	window.rowPosition.appendChild(oTR);

	//Reset pop form
	for (var i=0; i<7; i++) {
		if(i!=3) {f2.elements[i].value = ''; }
	}
	updateAmount();
}

//Delete Item wtd rows collection
function deleteItem(idx) {

	var count = window.rowPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.rowPosition.rows(i);
		if (oRow.id == idx) {
			var n = window.rowPosition.removeChild(oRow);
			count = count - 1; //decrease loop - 1
		}
	}
	updateAmount();
}

//Reculate Amount base on the form element
function updateAmount(){

	var f			= window.document.frmInsert;
	var numItem		= window.rowPosition.rows.length;
	var numInput	= 8;

	var idx_price	= 16;
	var idx_qty		= idx_price+1;
	var idx_amount	= idx_price+2;

	var e = window.document.frmInsert.elements;

	var sumOfQty	= 0;
	var sumOfTotal	= 0;

	for (var i = 0; i< numItem; i++) {
		var price = parseFloat(removecomma(e(idx_price+i*numInput).value));
		var qty = parseFloat(removecomma(e(idx_qty+i*numInput).value));

		e(idx_amount+i*numInput).value = numFormatval((price*qty)+'',2);

		sumOfQty	+= qty;
		sumOfTotal	+= price*qty;
	}

	add1		= parseFloat(removecomma(f.totalAdd1.value));
	add2		= parseFloat(removecomma(f.totalAdd2.value));
	sumBeforeVat= sumOfTotal+add1+add2;
	sumVat		= parseFloat(f.vat.value)/100 * sumBeforeVat;
	sumOfGrand	= sumBeforeVat+sumVat;

	f.totalQty.value		= addcomma(sumOfQty);
	f.totalAmount.value		= numFormatval(sumOfTotal + '', 2);
	f.totalBeforeVat.value	= numFormatval(sumBeforeVat + '', 2);
	f.totalVat.value		= numFormatval(sumVat + '', 2);
	f.totalGrand.value		= numFormatval(sumOfGrand + '', 2);
}
</script>
</head>
<body topmargin="0" leftmargin="0" onload="updateAmount()">
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
<h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] NEW PO LOCAL</h3>
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode' value='insert_PO'>
<span class="bar_bl">PO INFORMATION</span>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">PO NO</th>
		<td width="34%"><input name="_po_no" type="text" class="fmt" size="20" readonly></td>
		<th width="15%">PO DATE</th>
		<td><input name="_po_date" type="text" class="reqd" size="15" value="<?php echo date("j-M-Y")?>" maxlength="64"></td>
	</tr>
	<tr>
		<th width="15%">PO TYPE</th>
		<td>
			<input type="radio" name="_po_type" value="1" id="1><label for="1">NORMAL</label> &nbsp;
			<input type="radio" name="_po_type" value="2" id="2"checked><label for="2">NON VAT</label>
		</td>
		<th>DELIVERY DATE</th>
		<td><input name="_deli_date" type="text" class="fmtd" size="15" maxlength="64"></td>
	</tr>
</table>
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<th rowspan="3" width="12%">SUPPLIER</th>
		<th width="12%"><a href="javascript:fillCode('supplier')">CODE</a></th>
		<td width="25%"><input name="_sp_code" type="text" class="req" size="6" readOnly></td>
		<th width="15%">NAME</th>
		<td width="43%"><input type="text" name="_sp_name" class="req" style="width:100%" maxlength="125"></td>
	</tr>
	<tr>
		<th>ATTN</th>
		<td><input type="text" name="_sp_attn" class="fmt" size="25" maxlength="32"></td>
		<th>CONTACT</th>
		<td>
		Telp : <input type="text" name="_sp_phone" class="fmt" size="15" maxlength="32"> &nbsp;
		Fax : <input type="text" name="_sp_fax" class="fmt" size="15" maxlength="32">
		</td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="3"><input type="text" name="_sp_address" class="fmt" style="width:100%"></td>
	</tr>
</table><br />
<span class="bar_bl">ITEM LIST</span> &nbsp; <small class="comment"><i><a href="javascript:fillItem()">( search <img src="../../_images/icon/search_mini.gif"> )</a></i></small>
<table width="100%" class="table_box">
	<thead>
		<tr>
			<th width="5%">CODE</th>
			<th width="17%">ITEM</th>
			<th width="25%">DESC</th>
			<th width="5%">UNIT</th>
			<th width="10%">UNIT PRICE<br />(Rp)</th>
			<th width="8%">QTY</th>
			<th width="12%">AMOUNT<br />(Rp)</th>
			<th width="15%">REMARK</th>
			<th width="5%">DEL</th>
		</tr>
	</thead>
	<tbody id="rowPosition">
	</tbody>
</table>
<table width="100%" class="table_box">
	<tr>
		<th align="right">Total</th>
		<th width="8%"><input name="totalQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="12%"><input name="totalAmount" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="18%">&nbsp;</th>
	</tr>
	<tr>
		<th align="right" colspan="2">Addtional charge 1 : <input name="_add_charge1" type="text" class="fmt" style="width:30%"></th>
		<th><input name="totalAdd1" type="text" class="reqn" style="width:100%" onKeyUp="formatNumber(this,'dot')" onBlur="updateAmount()" value="0.00"></th>
		<th>&nbsp;</th>
	</tr>
	<tr>
		<th align="right" colspan="2">Addtional charge 2 : <input name="_add_charge2" type="text" class="fmt" style="width:30%"></th>
		<th style="border-bottom:1px solid #006da5"><input name="totalAdd2" type="text" class="reqn" style="width:100%" onKeyUp="formatNumber(this,'dot')" onBlur="updateAmount()" value="0.00"></th>
		<th>&nbsp;</th>
	</tr>
	<tr>
		<th align="right" colspan="2">Before VAT</th>
		<th><input name="totalBeforeVat" type="text" class="reqn" style="width:100%" readonly></th>
		<th>&nbsp;</th>
	</tr>
	<tr>
		<th align="right" colspan="2">VAT &nbsp; <input name="vat" type="text" class="reqn" style="width:30px" onKeyUp="formatNumber(this,'dot')" onBlur="updateAmount()" value="0.0"> %</th>
		<th style="border-bottom:1px solid #006da5"><input name="totalVat" type="text" class="reqn" style="width:100%" readonly></th>
		<th>&nbsp;</th>
	</tr>
	<tr>
		<th align="right" colspan="2">GRAND TOTAL</th>
		<th><input name="totalGrand" type="text" class="reqn" style="width:100%;" readonly></th>
		<th>&nbsp;</th>
	</tr>
	<tr>
		<th align="right">SAYS</th>
		<th colspan="4"><input name="_says_in_word" type="text" class="req" style="width:100%"></th>
		</th>
	</tr>
</table><br />
<span class="bar_bl">OTHERS</span>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">PREPARED BY</th>
		<td><input type="text" name="_prepared_by" class="req" size="15" maxlength="32" value="<?php echo ucfirst($S->getValue("ma_account")) ?>"></td>
		<th width="15%">CONFIRMED BY</th>
		<td><input type="text" name="_confirmed_by" class="req" size="15" maxlength="32"></td>
		<th width="15%">APPROVED BY</th>
		<td><input type="text" name="_approved_by" class="req" size="15" maxlength="32"></td>
	</tr>
	<tr>
		<th>REMARK</th>
		<td colspan="5"><textarea name="_remark" style="width:100%" rows="3"></textarea></td>
	</tr>
</table>
</form>
<p align='center'>
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkform(window.document.frmInsert)'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save PO"> &nbsp; Save PO</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_po.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel PO"> &nbsp; Cancel PO</button>
</p>
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