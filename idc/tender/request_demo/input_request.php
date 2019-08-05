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
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "input_request.php";

//PROCESS FORM
require_once APP_DIR . "_include/request_demo/tpl_process_form.php";
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
	if (window.rowPosition.rows.length <= 0) {
		alert("You need to choose at least 1 item");
		return;
	}

	if (verify(o)) {
		if(confirm("Are you sure to save request?")) {
			o.submit();
		}
	}
}

function fillCustomer(target) {

	keyword = window.document.frmInsert._cus_to.value;

	var x = (screen.availWidth - 400) / 2;
	var y = (screen.availHeight - 600) / 2;
	var win = window.open(
		'../../_include/request_demo/p_list_cus_code.php?_dept=<?php echo $department ?>&_check_code='+ keyword,
		'',
		'scrollbars,width=400,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

//Open window for search item
var wSearchItem;
function fillItem(){
	var x = (screen.availWidth - 580) / 2;
	var y = (screen.availHeight - 600) / 2;
	wSearchItem = window.open(
		'p_list_item.php', 'wSearchItem',
		'scrollbars,width=550,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wSearchItem.focus();
}

//It Called by child window window's name is wSearchItem
function createItem() {

	var f2		 = wSearchItem.document.frmCreateItem;
	var oTR		 = window.document.createElement("TR");
	var oTD		 = new Array();
	var oTextbox = new Array();

	//check same item in WAREHOUSE list
	var count = rowPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow	 = window.rowPosition.rows(i);
		if (oRow.id == trim(f2.elements[0].value)) {
			alert(
				"Please check item list"+
				"\nItem ["+ trim(f2.elements[0].value) +"] " + f2.elements[1].value +  " already exist!");
			for (var i=0; i<8; i++) {f2.elements[i].value = '';}
			return false;
		}
	}

	//Print cell for WH
	for (var i=0; i<7; i++) {
		oTD[i] = window.document.createElement("TD");
		oTextbox[i]		 = window.document.createElement("INPUT");
		oTextbox[i].type = "text";

		switch (i) {
			case 0: // it_code
				oTD[i].innerText			= trim(f2.elements[0].value);
				oTextbox[i].type			= "hidden";
				oTextbox[i].name			= "_it_code[]";
				oTextbox[i].value			= f2.elements[0].value;
				break;

			case 1: // it_model_no
				oTextbox[i].style.width		= "100%";
				oTextbox[i].className		= "fmt";
				oTextbox[i].name			= "_it_model_no[]";
				oTextbox[i].value			= f2.elements[1].value;
				oTextbox[i].readOnly		= 'readonly';
				break;

			case 2: //  it_desc
				oTextbox[i].style.width		= "100%";
				oTextbox[i].className		= "fmt";
				oTextbox[i].name			= "_it_desc[]";
				oTextbox[i].value			= f2.elements[2].value;
				oTextbox[i].readOnly		= 'readonly';
				break;

			case 3: //  it_qty
				oTextbox[i].style.width		= "100%";
				oTextbox[i].className		= "reqn";
				oTextbox[i].name			= "_it_qty[]";
				oTextbox[i].value			= numFormatval(f2.elements[3].value+'',2);
				oTextbox[i].onblur			= function() {updateAmount();}
				oTextbox[i].onkeyup			= function() {formatNumber(this, 'dot');}
				break;

			case 4: //  it_returned
				oTD[i].align				= "center";
				if(f2.elements[6].value == "0") {
					oTD[i].innerText		= 'Yes';
				} else if(f2.elements[6].value == "1") {
					oTD[i].innerText		= 'No';
				}
				oTextbox[i].type			= "hidden";
				oTextbox[i].name			= "_it_returnable[]";
				oTextbox[i].value			= f2.elements[6].value;
				break;

			case 5: //  it_remark
				oTextbox[i].style.width		= "100%";
				oTextbox[i].className		= "fmt";
				oTextbox[i].name			= "_it_remark[]";
				oTextbox[i].value			= f2.elements[7].value;
				break;

			case 6: // DELETE
				oTD[i].innerHTML			= "<a href=\"javascript:deleteItem('" + trim(f2.elements[0].value)+ "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD[i].align				= "center";
				break;
		}

		if (i!=6) oTD[i].appendChild(oTextbox[i]);
		oTR.id = trim(f2.elements[0].value);
		oTR.appendChild(oTD[i]);
	}
	for (var i=0; i<8; i++) {f2.elements[i].value = '';}
	window.rowPosition.appendChild(oTR);
	updateAmount();

}

//Delete Item rows collection
function deleteItem(idx) {
	var count = window.rowPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow	 = window.rowPosition.rows(i);
		if (oRow.id == idx) {
			var n = window.rowPosition.removeChild(oRow);
			count = count - 1;
			break;
		}
	}
	updateAmount();
}

//Reculate Amount base on the form element
function updateAmount(){

	var f			= window.document.frmInsert;
	var e 			= window.document.frmInsert.elements;
	var countWH		= window.rowPosition.rows.length;;
	var numInputWH	= 6;
	var idx_qty		= 10;		/////
	var sumOfQty	= 0;

	for (var i=0; i<countWH; i++) {
		var qty = parseFloat(removecomma(e((idx_qty)+i*numInputWH).value));
		sumOfQty	+= qty;
	}
	f.totalWhQty.value	  = numFormatval(sumOfQty + '', 2);

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
<h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] NEW REQUEST</h3>
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode' value='insert_request'>
<input type="hidden" name="_dept" value="<?php echo $department ?>">
<strong class="info">REQUEST INFORMATION</strong>
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<th width="15%">REQUEST NO</th>
		<td width="35%" colspan="2"></td>
		<th width="15%">REQUEST BY</th>
		<td width="20%"><input name="_request_by" type="text" class="req" size="15" maxlength="32" value="<?php echo $S->getValue("ma_account")?>"></td>
		<th width="15%">REQUEST DATE</th>
		<td><input name="_request_date" type="text" class="reqd" size="15" value="<?php echo date("j-M-Y")?>"></td>
	</tr>
	<tr>
		<th rowspan="2">CUSTOMER/<br />EVENT</th>
		<th width="12%"><a href="javascript:fillCustomer('customer')" accesskey="c"><u>C</u>ODE</a></th>
		<td><input name="_cus_to" type="text" class="req" size="10" maxlength="7"></td>
		<th>NAME</th>
		<td colspan="3"><input type="text" name="_cus_name" class="req" style="width:100%" maxlength="128"></td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="5"><input type="text" name="_cus_address" class="fmt"  style="width:100%" maxlength="255"></td>
	</tr>
</table><br />
<strong class="info">ITEM LIST</strong> &nbsp; <small class="comment"><i><a href="javascript:fillItem()">( search <img src="../../_images/icon/search_mini.gif"> )</a></i></small>
<table width="100%" class="table_box">
	<thead>
		<tr height="40px">
			<th width="7%">CODE</th>
			<th width="15%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="7%">QTY</th>
			<th width="7%">RETURNABLE<br />Yes | No</th>
			<th width="15%">REMARK</th>
			<th width="5%">DEL</th>
		</tr>
	</thead>
	<tbody id="rowPosition">
	</tbody>
	<tr>
		<th colspan="3" align="right">TOTAL QTY</th>
		<th width="7%"><input name="totalWhQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th colspan="3" width="20%">&nbsp;</th>
	</tr>
</table><br />
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<th>SIGN BY</th>
		<td><input type="text" name="_sign_by" class="req" size="15" maxlength="32"></td>
	</tr>
	<tr>
		<th width="15%">REMARK</th>
		<td><textarea name="_remark" rows="5" style="width:100%"></textarea></td>
	</tr>
</table><br />
</form>
<p align='center'>
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkform(window.document.frmInsert)'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save request"> &nbsp; Save request</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_request.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel request"> &nbsp; Cancel request</button>
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