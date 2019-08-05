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
$left_loc = "input_request.php";

//PROCESS FORM
require_once APP_DIR . "_include/demo/tpl_process_form.php";
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
	if (window.itemWHPosition.rows.length <= 0) {
		alert("You need to choose at least 1 item");
		return;
	}

	if (verify(o)) {
		if(confirm("Are you sure to save request?")) {
			o.submit();
		}
	}
}

//Open window for search item
var wSearchItem;
function fillItem(){
	var x = (screen.availWidth - 580) / 2;
	var y = (screen.availHeight - 300) / 2;
	wSearchItem = window.open(
		'./p_list_item.php','wSearchItem',
		'scrollbars,width=550,height=300,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wSearchItem.focus();
}

//It Called by child window window's name is wSearchItem
//Please see the p_list_item.php
function createItem() {

	var f2		= wSearchItem.document.frmCreateItem;
	var oTR		= window.document.createElement("TR");
	var oTD		= new Array();
	var oTextbox	= new Array();

	//check same item in WAREHOUSE list
	var count = itemWHPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow	 = window.itemWHPosition.rows(i);
		if (oRow.id == trim(f2.elements[3].value)) {
			alert(
				"Please check item list"+
				"\nItem ["+ trim(f2.elements[3].value) +"] " + f2.elements[4].value +  " already exist!");
			return false;
		}
	}

	//Print cell for WH
	for (var i=0; i<6; i++) {
		oTD[i] = window.document.createElement("TD");
		oTextbox[i]		 = window.document.createElement("INPUT");
		oTextbox[i].type = "text";

		switch (i) {
			case 0: // _wh_it_code
				oTD[i].innerText			= trim(f2.elements[3].value);
				oTextbox[i].type			= "hidden";
				oTextbox[i].name			= "_wh_it_code[]";
				oTextbox[i].value			= f2.elements[3].value;
				break;

			case 1: // _wh_it_model_no
				oTextbox[i].style.width		= "100%";
				oTextbox[i].className		= "fmt";
				oTextbox[i].name			= "_wh_it_model_no[]";
				oTextbox[i].value			= f2.elements[4].value;
				oTextbox[i].readOnly		= 'readonly';
				break;

			case 2: // _wh_it_desc
				oTextbox[i].style.width	= "100%";
				oTextbox[i].className		= "fmt";
				oTextbox[i].name			= "_wh_it_desc[]";
				oTextbox[i].value			= f2.elements[5].value;
				oTextbox[i].readOnly		= 'readonly';
				break;

			case 3: // _wh_it_qty
				oTextbox[i].style.width	= "100%";
				oTextbox[i].className		= "reqn";
				oTextbox[i].name			= "_wh_it_qty[]";
				oTextbox[i].value			= numFormatval(f2.elements[8].value+'',2);
				oTextbox[i].onblur			= function() {updateAmount();}
				oTextbox[i].onkeyup			= function() {formatNumber(this, 'dot');}
				oTextbox[i].readOnly		= 'readonly';
				break;

			case 4: // _wh_it_remark
				oTextbox[i].style.width		= "100%";
				oTextbox[i].className		= "fmt";
				oTextbox[i].name			= "_wh_it_remark[]";
				oTextbox[i].value			= f2.elements[9].value;
				break;

			case 5: // DELETE
				oTD[i].innerHTML			= "<a href=\"javascript:deleteWHItem('" + trim(f2.elements[1].value)+ "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD[i].align				= "center";
				break;
		}

		if (i!=5) oTD[i].appendChild(oTextbox[i]);
		oTR.id = trim(f2.elements[1].value);
		oTR.appendChild(oTD[i]);
	}
	window.itemWHPosition.appendChild(oTR);
	updateAmount();
}

//Delete Item rows collection
function deleteWHItem(idx) {
	var count = window.itemWHPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow	 = window.itemWHPosition.rows(i);
		if (oRow.id == idx) {
			var n = window.itemWHPosition.removeChild(oRow);
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
	var countWH		= window.itemWHPosition.rows.length;;
	var numInputWH	= 5;
	var idx_qty		= 6;	/////
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
<h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] NEW REQUEST DEMO UNIT STOCK</h3>
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode' value='insert_request'>
<strong class="info">REQUEST INFORMATION</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">REQUEST NO</th>
		<td width="20%"></td>
		<th width="15%">ISSUED BY</th>
		<td width="34%"><input name="_issued_by" type="text" class="req" size="15" maxlength="32" value="<?php echo $S->getValue("ma_account")?>"></td>
		<th width="15%">ISSUED DATE</th>
		<td><input name="_issued_date" type="text" class="reqd" size="15" value="<?php echo date("j-M-Y")?>"></td>
	</tr>
</table><br />
<strong class="info">[<font color="#315c87">WAREHOUSE</font>] ITEM LIST</strong> &nbsp; <small class="comment"><i><a href="javascript:fillItem()">( search <img src="../../_images/icon/search_mini.gif"> )</a></i></small>
<table width="100%" class="table_box">
	<thead>
		<tr height="35px">
			<th width="7%">CODE</th>
			<th width="15%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="7%">QTY</th>
			<th width="15%">REMARK</th>
			<th width="5%">DEL</th>
		</tr>
	</thead>
	<tbody id="itemWHPosition">
	</tbody>
	<tr>
		<th colspan="3" align="right">TOTAL QTY</th>
		<th width="7%"><input name="totalWhQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th colspan="2" width="20%">&nbsp;</th>
	</tr>
</table><br />
<table width="100%" class="table_box">
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