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
ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/list_item.php");

//GLOBAL
$left_loc = "input_item.php";

//PROCESS FORM
require_once "tpl_process_form.php";
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language="text/javascript" type="text/javascript" src="../../_script/js_category.php"></script>
<script language='text/javascript' type='text/javascript'>
function checkform(o) {
	if (verify(o)) {
			
		if(o._user_price_net_kurs.value > 0) {
			if(o._user_price_net_dollar.value < 0 || o._user_price_net_dollar.value == "") {
				alert("");
				o._user_price_net_dollar.focus();
			}
		}
		o.submit();
	}
}

function enabledColumn(id) {
	var f = window.document.frmAdd;
	if (id == 'childItem') {
		if(f._item_type.checked == false) {
			var count = window.rowPosition.rows.length;
			for (var i=0; i<count; i++) {
				var oRow = window.rowPosition.rows(i);
				var n = window.rowPosition.removeChild(oRow);
				count = count - 1;
				alert(count);
			}	
		}
	}
}

//Open window for search item
var wSearchItem;
function fillItem(){
	var f = window.document.frmAdd;
	var x = (screen.availWidth - 580) / 2;
	var y = (screen.availHeight - 620) / 2;

	if(f._item_type[0].checked == true) { return; }

	wSearchItem = window.open(
		'./p_list_item.php',
		'wSearchItem',
		'scrollbars,width=550,height=640,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wSearchItem.focus();
}

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

	if(window.document.frmAdd._item_type[1].checked && count == 1) {
		alert("Child just has 1 Item Referance");
		return false;
	}

	for (var i=0; i<3; i++) {
		oTD[i] 				= window.document.createElement("TD");
		oTextbox[i] 		= window.document.createElement("INPUT");
		oTextbox[i].type	= "hidden";

		switch (i) {
			case 0: // ITEM CODE
				oTD[i].innerText	= f2.elements[0].value;
				oTextbox[i].name	= "_it_code[]";
				oTextbox[i].value	= f2.elements[0].value;
				break;

			case 1: // ITEM NAME
				oTD[i].innerText	= f2.elements[1].value;
				oTextbox[i].name	= "_it_model_no[]";
				oTextbox[i].value	= f2.elements[1].value;
				break;

			case 2: // DELETE
				oTD[i].innerHTML = "<a href=\"javascript:deleteItem('" + trim(f2.elements[0].value) + "')\"><img src=\"../../_images/icon/delete.gif\" width=\"12px\"></a>";
				oTD[i].align = "center";
				break;
		}

		if (i!= 2) oTD[i].appendChild(oTextbox[i]);
		oTR.id = trim(f2.elements[0].value);
		oTR.appendChild(oTD[i]);
	}
	window.rowPosition.appendChild(oTR);
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
}


function fillOption(target, pidx) {
	target.options.length = 1;
	for(var i = 0; i<icat.length; i++) {
		if (icat[i][1] == pidx) {
			target.options[target.options.length] = new Option(icat[i][4], icat[i][0]);
		}
	}
}

function fillOptionInit() {
	fillOption(window.document.frmAdd.icat_1, 0);
}

function resetOption() {
	window.document.frmAdd.icat_3.options.length = 1;
	window.document.frmAdd._midx.value = "";
}
</script>
</head>
<body topmargin="0" leftmargin="0" onLoad="fillOptionInit()">
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
<strong class="title">[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] NEW ITEM</strong><br /><br />
<form name='frmAdd' method='POST'>
<input type='hidden' name='p_mode' value='insert_item'>
<table width="100%" class="table_a">
	<tr>
		<th>CATEGORY</th>
		<td>
		<input type="hidden" name="_midx" class="req">
			<select name="icat_1" onChange="fillOption(window.document.frmAdd.icat_2, this.value)" onClick="resetOption()">
				<option>==SELECT==</option>
			</select>&nbsp;
			<select name="icat_2" onChange="fillOption(window.document.frmAdd.icat_3, this.value)" onClick="window.document.frmAdd._midx.value = ''">
				<option>==SELECT==</option>
			</select>&nbsp;
			<select name="icat_3" onChange="window.document.frmAdd._midx.value = this.value">
				<option>==SELECT==</option>
			</select>&nbsp;</td>
		<th width="10%">MODEL NO</th>
		<td width="28%"><input name="_model_no" type="text" class="fmt" size="30" maxlength="32"></td>
	</tr>
	<tr>
		<th width="14%">ITEM CODE</th>
		<td width="48%">
			<input name="_code" type="text" class="req" size="10" maxlength="6">
			<span class="comment">* 6 Character only</span>
			</td>
		<th>TYPE</th>
		<td>
			<input name="_type" type="text" class="fmt" size="30">
		</td>
	</tr>
	<tr>
		<th>UNIT PRICE</th>
		<td colspan="3">
            Rp. <input name="_user_price" type="text" class="reqn" onKeyUp="formatNumber(this,'dot')" maxlength="12" size="18">&nbsp; 
            Valid from <input type="text" name="_date_from" size="10" class="reqd" value="<?php echo date("d-M-Y")?>">
		</td>
	</tr>
	<tr>
		<th>TAX DESC</th>
		<td colspan="3">
            $ <input name="_user_price_net_dollar" type="text" class="reqn" onKeyUp="formatNumber(this,'dot')" maxlength="5" size="4">&nbsp; 
            Kurs <input name="_user_price_net_kurs" type="text" class="reqn" onKeyUp="formatNumber(this,'dot')" maxlength="5" size="4">&nbsp; 
            Valid from <input type="text" name="_user_price_net_date" size="10" class="reqd" value="<?php echo date("d-M-Y")?>">
		</td>
	</tr>
	<tr>
		<th>DESCRIPTION</th>
		<td colspan="3"><input type="text" name="_desc" class="fmt" size="80"></td>
	</tr>
	<tr>
		<th>REMARK</th>
		<td colspan="3"><textarea name="_remark" style="width:50%" rows="5"></textarea></td>
	</tr>
</table><br />
<strong class="info">MANAGE ITEM</strong>
<table width="100%" class="table_a">
	<tr>
		<th width="14%">TYPE ITEM</th>
		<td width="35%">
			<input type="radio" name="_item_type" value="0" checked onClick="enabledColumn('childItem')"> Parent &nbsp; &nbsp;
			<input type="radio" name="_item_type" value="1" onClick="enabledColumn('childItem')"> Child &nbsp; &nbsp;
			<input type="radio" name="_item_type" value="2" onClick="enabledColumn('childItem')"> Mixed
		</td>
		<th width="14%">HAS E/D</th>
		<td>
			<input type="radio" name="_has_ed" value="t"> Yes &nbsp; &nbsp;
			<input type="radio" name="_has_ed" value="f" checked> No
		</td>
	</tr>
	<tr>
		<th>MANAGE</th>
		<td colspan="3">
			<table width="50%" class="table_nn">
			  <thead>
				<tr>
					<th width="15%">CODE</th>
					<th>MODEL NO</th>
					<th width="10%"><a href="javascript:fillItem()"><span class="comment"><i>(search)</i></span></a></th>
				</tr>
			  </thead>
			  <tbody id="rowPosition">
			  </tbody>
			</table>
		</td>
	</tr>
</table>
</form>
<p align='center'>
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkform(window.document.frmAdd)'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save item"> &nbsp; Save item</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_item.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel item"> &nbsp; Cancel item</button>
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