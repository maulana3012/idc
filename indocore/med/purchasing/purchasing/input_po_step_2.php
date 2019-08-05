<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*/
//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "input_po_step_1.php";

//PROCESS FORM
require_once APP_DIR . "_include/purchasing/tpl_process_form.php";
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
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

	//Check has same CODE
	var count = rowPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.rowPosition.rows(i);
		if (trim(oRow.cells(0).innerText) == trim(f2.elements(0).value)) {
			alert("Same Item code already exist!");
			return false;
		}
	}

	//If you add more cell
	// 1. increase tthe count as number of td
	// 2. add Case
	// the Cell order match with p_list_item.php field.
	for (var i=0; i<10; i++) {
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
				oTextbox[i].name		= "_poit_item[]";
				oTextbox[i].value		= f2.elements[2].value;
				break;

			case 2: // DESCRIPTION
				oTextbox[i].style.width	= "100%";
				oTextbox[i].className	= "fmt";
				oTextbox[i].name		= "_poit_desc[]";
				oTextbox[i].value		= f2.elements[3].value;
				break;

			case 3: // ATTRIBUTE
				oTextbox[i].style.width	= "100%";
				oTextbox[i].className	= "fmt";
				oTextbox[i].name		= "_poit_att[]";
				oTextbox[i].value		= f2.elements[4].value;
				break;

			case 4: // UNIT PRICE
				oTD[i].align			= "right";
				oTextbox[i].style.width = "100%";
				oTextbox[i].className	= "fmtn";
				oTextbox[i].name		= "_poit_unit_price[]";
				oTextbox[i].value		= numFormatval(f2.elements[5].value+'',2);
				oTextbox[i].onblur 		= function() {updateAmount();}
				oTextbox[i].onkeyup		= function() {formatNumber(this, 'dot');}
				break;

			case 5: // QTY
				oTD[i].align			= "right";
				oTextbox[i].style.width = "100%";
				oTextbox[i].className	= "fmtn";
				oTextbox[i].name		= "_poit_qty[]";
				oTextbox[i].value		= addcomma(f2.elements[6].value);
				oTextbox[i].onblur 		= function() {updateAmount();}
				oTextbox[i].onkeyup		= function() {formatNumber(this, 'dot');}
				break;

			case 6: //AMOUNT
				var amount = f2.elements[5].value * f2.elements[6].value;
				oTD[i].align			= "right";
				oTextbox[i].readOnly	= "readonly";
				oTextbox[i].name		= "_poit_amount[]";
				oTextbox[i].style.width = "100%";
				oTextbox[i].className	= "fmtn";
				oTextbox[i].value		= numFormatval(amount+'',2);
				break;

			case 7: // REMARK
				oTextbox[i].style.width	= "100%";
				oTextbox[i].className	= "fmt";
				oTextbox[i].name		= "_poit_remark[]";
				oTextbox[i].value		= f2.elements[7].value;
				break;

			case 8: // DELETE
				oTD[i].innerHTML = "<a href=\"javascript:deleteItem('" + trim(f2.elements[0].value) + "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD[i].align = "center";
				break;

			case 9: // ICAT MIDX
				oTextbox[i].type = "hidden";
				oTextbox[i].name = "_icat_midx[]";
				oTextbox[i].value = f2.elements[1].value;
				break;
		}

		if (i!= 8) oTD[i].appendChild(oTextbox[i]);
		oTR.id = trim(f2.elements[0].value);
		oTR.appendChild(oTD[i]);
	}
	window.rowPosition.appendChild(oTR);

	//Reset pop form
	for (var i=0; i< 8; i++) {f2.elements[i].value = '';}
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
	var numInput	= 9;

	var idx_price	= 25;
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

	f.totalQty.value	  = addcomma(sumOfQty);
	f.totalAmount.value   = numFormatval(sumOfTotal + '', 2);
}

function seePOLayout() {
	var f = window.document.frmInsert;

	if(f._layout_type.value == 1){
		var type = 1;
	} else if(f._layout_type.value == 2){
		var type = 2;
	} else if(f._layout_type.value == 3){
		var type = 3;
	} else if(f._layout_type.value == 4){
		var type = 4;
	}

	var x = (screen.availWidth - 600) / 2;
	var y = (screen.availHeight - 250) / 2;
	var win = window.open(
		'./p_po_layout.php?_type=' + type,
		'',
		'scrollbars,width=600,height=250,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}
</script>
</head>
<body topmargin="0" leftmargin="0">
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
<table width="100%">
  <tr>
	<td>
		<strong style="font-size:18px;font-weight:bold">
		[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] NEW PO (STEP 2 / 2)<br />
		</strong>
	</td>
  </tr>
  <tr>
	<td colspan="2"><small class="comment">* <?php echo $title[$_po_type_invoice] ?></small></td>
  </tr>
</table>
<hr><br />
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode' value='insert'>
<input type="hidden" name="_po_date" value="<?php echo $_po_date?>">
<input type="hidden" name="_po_type" value="<?php echo $_po_type?>">
<input type="hidden" name="_shipment_mode" value="<?php echo $_shipment_mode?>">
<input type="hidden" name="_mode_desc" value="<?php echo $_mode_desc?>">
<input type="hidden" name="_received_by" value="<?php echo addslashes($_received_by)?>">
<input type="hidden" name="_layout_type" value="<?php echo $_layout_type?>">
<input type="hidden" name="_currency_type" value="<?php echo $_currency_type?>">
<input type="hidden" name="_sp_code" value="<?php echo addslashes($_supplier_code)?>">
<input type="hidden" name="_sp_name" value="<?php echo addslashes($_supplier_name)?>">
	<span class="bar_bl">PO INFORMATION</span>
	<table width="100%" class="table_box">
		<tr>
			<th width="12%">PO NO</th>
			<td></td>
			<th width="15%">PO DATE</th>
			<td><?php echo $_po_date ?></td>
		</tr>
		<tr>
			<th>PO TYPE</th>
			<td>
				<input type="radio" name="_type" value="1" disabled <?php echo ($_po_type == 1) ? "checked" : "" ?>>NORMAL &nbsp;
				<input type="radio" name="_type" value="2" disabled <?php echo ($_po_type == 2) ? "checked" : "" ?>>DOOR TO DOOR
			</td>
			<th>SHIPMENT MODE</th>
			<td>
				<input type="radio" name="_mode" value="sea" disabled <?php echo ($_shipment_mode == 'sea') ? "checked" : "" ?>>SEA &nbsp;
				<input type="radio" name="_mode" value="air" disabled <?php echo ($_shipment_mode == 'air') ? "checked" : "" ?>>AIR &nbsp;
				<input type="radio" name="_mode" value="other" disabled <?php echo ($_shipment_mode == 'other') ? "checked" : "" ?>>OTHER &nbsp; &nbsp;
				<?php echo $_mode_desc ?>
			</td>
		</tr>
		<tr>
			<th>RECEIVED BY</th>
			<td><?php echo $_received_by ?></td>
			<th>LAYOUT TYPE</th>
			<td>
				<input type="radio" name="_po_type" value="1" disabled <?php echo ($_layout_type == 1) ? "checked" : "" ?>>1 &nbsp; &nbsp; 
				<input type="radio" name="_po_type" value="2" disabled <?php echo ($_layout_type == 2) ? "checked" : "" ?>>2 &nbsp; &nbsp;
				<input type="radio" name="_po_type" value="3" disabled <?php echo ($_layout_type == 3) ? "checked" : "" ?>>3 &nbsp; &nbsp;
				<input type="radio" name="_po_type" value="4" disabled <?php echo ($_layout_type == 4) ? "checked" : "" ?>>4 &nbsp; &nbsp;
				<a href="javascript:seePOLayout()"><small>see layout</small></a>
			</td>
		</tr>
		<tr>
			<th>CURRENCY TYPE</th>
			<td>
				<input type="radio" name="currency_type" value="1" disabled <?php echo ($_currency_type == 1) ? "checked" : "" ?> id="usd"><label for="usd">USD &nbsp; &nbsp;</label>
				<input type="radio" name="currency_type" value="2" disabled <?php echo ($_currency_type == 2) ? "checked" : "" ?> id="rp" checked><label for="rp">RUPIAH &nbsp; &nbsp;</label>
			</td>
		</tr>
	</table>
	<table width="100%" class="table_box" cellspacing="1">
		<tr>
			<th rowspan="3" width="12%">SUPPLIER</th>
			<th width="10%">CODE</th>
			<td width="23%"><?php echo $_supplier_code ?></td>
			<th width="15%">NAME</th>
			<td width="43%"><?php echo $_supplier_name ?></td>
		</tr>
		<tr>
			<th width="12%">ATTN</th>
			<td><?php echo $_supplier_attn ?></td>
			<th width="12%">CC</th>
			<td><?php echo $_supplier_cc ?></td>
		</tr>
		<tr>
			<th>TELP</th>
			<td><?php echo $_supplier_phone ?></td>
			<th>FAX</th>
			<td><?php echo $_supplier_fax ?></td>
		</tr>
	</table><br />
	<span class="bar_bl">ITEM LIST</span> &nbsp; <small class="comment"><i><a href="javascript:fillItem()">( search <img src="../../_images/icon/search_mini.gif"> )</a></i></small>
	<table width="100%" class="table_box">
		<thead>
			<tr height="25px">
				<th width="5%">CODE</th>
				<th width="17%">ITEM</th>
				<th width="25%">DESC</th>
				<th width="5%">ATT</th>
				<th width="12%">UNIT PRICE</th>
				<th width="8%">QTY</th>
				<th width="12%">AMOUNT</th>
				<th width="11%">REMARK</th>
				<th width="5%">DEL</th>
			</tr>
		</thead>
		<tbody id="rowPosition">
		</tbody>
	</table>
	<table width="100%" class="table_box">
		<tr>
			<th width="64%" align="right">GRAND TOTAL</th>
			<th width="8%"><input name="totalQty" type="text" class="reqn" style="width:100%" readonly></th>
			<th width="12%"><input name="totalAmount" type="text" class="reqn" style="width:100%" readonly></th>
			<th width="16%">&nbsp;</th>
		</tr>
	</table><br>
	<span class="bar_bl">OTHERS</span>
	<table width="100%" class="table_box">
		<tr>
			<th width="15%">PREPARED BY</th>
			<td><input type="text" name="_prepared_by" class="req" value="<?php echo $S->getValue("ma_account") ?>"></td>
			<th width="15%">CONFIRMED BY</th>
			<td><input type="text" name="_confirmed_by" class="req"></td>
		</tr>
		<tr>
			<th>REMARK</th>
			<td colspan="3"><textarea name="_remark" style="width:100%" rows="2"></textarea></td>
		</tr>
		<tr>
			<th>PO PRINT<br />REMARK</th>
			<td colspan="3">
				<textarea name="_print_remark" style="width:100%" rows="4">
1.   Shipment : By <?php echo strtoupper($_shipment_mode)."\n" ?>
      SHIPPING MARK : DUL - JKT - BY <?php echo strtoupper($_shipment_mode)."\n\n" ?>
<?php if($_forwarder_code != '') { ?>
2.   Please deliver those goods to our local forwarder as follow :
      <?php echo "$_forwarder_name\n" ?>
      <?php echo "$_forwarder_address\n"/*str_replace("\r","\n&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$_forwarder_address)."\n"*/?>
      TEL : <?php echo $_forwarder_phone." / FAX : ".$_forwarder_fax."\n";?>
      ATTN : <?php echo $_forwarder_contact."\n";?>
      MOBILE PHONE : <?php echo$_forwarder_mobile_phone."\n\n";?>
<?php } ?>
<?php echo ($_forwarder_code == '') ? '2':'3' ?>.   <?php echo ($_po_type == 1) ? "NORMAL" : "DOOR TO DOOR" ?>
				</textarea>
			</td>
		</tr>
	</table>
<input type="hidden" name="_po_type_invoice" value="<?php echo $_po_type_invoice?>">
<input type="hidden" name="_ordered_by" value="<?php echo $_ordered_by ?>">
<input type="hidden" name="web_url" value="<?php echo ZKP_SQL ?>">
</form>
<p align='center'>
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkform(window.document.frmInsert)'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save PO"> &nbsp; Save PO</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_po_step_1.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel PO"> &nbsp; Cancel PO</button>
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