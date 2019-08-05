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
ckperm(ZKP_INSERT, HTTP_DIR . $currentDept . "/packing_list/index.php");

//GLOBAL
$left_loc = "input_pl_step_3.php";

//PROCESS FORM
require_once APP_DIR . "_include/purchasing/tpl_process_form_pl.php";
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

	var numItem		= window.rowPosition.rows.length;
	var numInput	= 9;
	var idx_qty		= 22;
	var e			= window.document.frmInsert.elements;

	for (var i=0; i<numItem; i++) {
		var value	= parseFloat(removecomma(e(idx_qty+i*numInput).value));
		if(value == 0) {
			alert("Please delete item with 0 (Null) value qty");
			return;
		}
	}

	if (verify(o)) {
		if(confirm("Are you sure to save?")) {
			o.submit();
		}
	}
}

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

function createItem() {

	var o	= window.document.frmInsert;
	var f2	= wSearchItem.document.frmCreateItem;
	var oTR = window.document.createElement("TR");

	var oTD = new Array();
	var oTextbox = new Array();

	var count = rowPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.rowPosition.rows(i);
		if (trim(oRow.cells(0).innerText) == trim(f2.elements(0).value)) {
			alert("Same Item code already exist!");
			return false;
		}
	}

	for (var i=0; i<10; i++) {
		oTD[i] = window.document.createElement("TD");
		oTextbox[i] = window.document.createElement("INPUT");
		oTextbox[i].type = "text";

		switch (i) {
			case 0: // ITEM CODE
				oTD[i].innerText		= f2.elements[0].value;
				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_it_code[]";
				oTextbox[i].value		= f2.elements[0].value;
				break;

			case 1: // ITEM NAME
				oTextbox[i].style.width	= "100%";
				oTextbox[i].className	= "fmt";
				oTextbox[i].name		= "_it_item[]";
				oTextbox[i].value		= f2.elements[2].value;
				break;

			case 2: // DESCRIPTION
				oTextbox[i].style.width	= "100%";
				oTextbox[i].className	= "fmt";
				oTextbox[i].name		= "_it_desc[]";
				oTextbox[i].value		= f2.elements[3].value;
				break;

			case 3: // ATTRIBUTE
				oTextbox[i].style.width	= "100%";
				oTextbox[i].className	= "fmt";
				oTextbox[i].name		= "_it_att[]";
				oTextbox[i].value		= f2.elements[4].value;
				break;

			case 4: // UNIT PRICE
				oTD[i].align			= "right";
				oTextbox[i].style.width = "100%";
				oTextbox[i].className	= "fmtn";
				oTextbox[i].name		= "_it_unit_price[]";
				oTextbox[i].value		= numFormatval(f2.elements[5].value+'',2);
				oTextbox[i].onblur 		= function() {updateAmount();}
				oTextbox[i].onkeyup		= function() {formatNumber(this, 'dot');}
				break;

			case 5: // QTY
				oTD[i].align			= "right";
				oTextbox[i].style.width = "100%";
				oTextbox[i].className	= "fmtn";
				oTextbox[i].name		= "_it_qty[]";
				oTextbox[i].value		= addcomma(f2.elements[6].value);
				oTextbox[i].onblur 		= function() {updateAmount();}
				oTextbox[i].onkeyup		= function() {formatNumber(this, 'dot');}
				break;

			case 6: //AMOUNT
				var amount = f2.elements[5].value * f2.elements[6].value;

				oTD[i].align			= "right";
				oTextbox[i].readOnly	= "readonly";
				oTextbox[i].name		= "_it_amount[]";
				oTextbox[i].style.width = "100%";
				oTextbox[i].className	= "fmtn";
				oTextbox[i].value		= numFormatval(amount+'',2);
				break;

			case 7: // REMARK
				oTextbox[i].style.width	= "100%";
				oTextbox[i].className	= "fmt";
				oTextbox[i].name		= "_it_remark[]";
				oTextbox[i].value		= f2.elements[7].value;
				break;

			case 8: // DELETE
				oTD[i].innerHTML		= "<a href=\"javascript:deleteItem('" + trim(f2.elements[0].value) + "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD[i].align			= "center";
				break;

			case 9: // ICAT MIDX
				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_icat_midx[]";
				oTextbox[i].value		= f2.elements[1].value;
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

function deleteItem(idx) {

	var count = window.rowPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.rowPosition.rows(i);
		if (oRow.id == idx) {
			var n = window.rowPosition.removeChild(oRow);
			count = count - 1;
		}
	}
	updateAmount();
}

function updateAmount(){

	var f			= window.document.frmInsert;
	var numItem		= window.rowPosition.rows.length;
	var numInput	= 9;

	var idx_price	= 21;			/////
	var idx_qty		= idx_price+1;
	var idx_amount	= idx_price+2;

	var sumOfQty	= 0;
	var sumOfTotal	= 0;
	var e = window.document.frmInsert.elements;

	for (var i = 0; i< numItem; i++) {
		var price	= parseFloat(removecomma(e(idx_price+i*numInput).value));
		var qty		= parseFloat(removecomma(e(idx_qty+i*numInput).value));

		e(idx_amount+i*numInput).value = numFormatval((price*qty)+'',2);

		sumOfQty	+= qty;
		sumOfTotal	+= price*qty;
	}

	f.totalQty.value	  = addcomma(sumOfQty);
	f.totalAmount.value   = numFormatval(sumOfTotal + '', 2);
}
</script>
</head>
<body topmargin="0" leftmargin="0" onLoad="updateAmount()">
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
<h4>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] NEW PL (STEP 2 / 2)</h4>
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode' value='insert_claim'>
<input type="hidden" name="_inv_no" value="<?php echo $_inv_no?>">
<input type="hidden" name="_inv_date" value="<?php echo $_inv_date?>">
<input type="hidden" name="_layout_type" value="<?php echo $_layout_type?>">
<input type="hidden" name="_received_by" value="<?php echo addslashes($_received_by)?>">
<input type="hidden" name="_sp_code" value="<?php echo addslashes($_supplier_code)?>">
<input type="hidden" name="_sp_name" value="<?php echo $_supplier_name?>">
<input type="hidden" name="_etd_date" value="<?php echo $_etd_date?>">
<input type="hidden" name="_eta_date" value="<?php echo $_eta_date?>">
<input type="hidden" name="_shipment_mode" value="<?php echo $_shipment_mode?>">
<input type="hidden" name="_mode_desc" value="<?php echo $_mode_desc?>">
	<span class="bar_bl">INVOICE INFORMATION</span>
	<table width="100%" class="table_box">
		<tr>
			<th width="12%">INVOICE NO</th>
			<td width="40%"><?php echo $_inv_no ?></td>
			<th width="15%">INVOICE DATE</th>
			<td><?php echo $_inv_date ?></td>
		</tr>
		<tr>
			<th>ETD DATE</th>
			<td><?php echo $_etd_date ?></td>
			<th>ETA DATE</th>
			<td><?php echo $_eta_date ?></td>
		</tr>
		<tr>
			<th>RECEIVED BY</th>
			<td><?php echo $_received_by ?></td>
		</tr>
		<tr>
			<th>PL TYPE</th>
			<td>
				<input type="radio" name="_type" value="1" disabled>NORMAL &nbsp;
				<input type="radio" name="_type" value="2" disabled checked>DOOR TO DOOR
			</td>
			<th>SHIPMENT MODE</th>
			<td>
				<input type="radio" name="_mode" value="sea" <?php echo ($_shipment_mode == 'sea') ? 'checked' : '' ?> disabled>SEA &nbsp;
				<input type="radio" name="_mode" value="air" <?php echo ($_shipment_mode == 'air') ? 'checked' : '' ?> disabled>AIR &nbsp;
				<input type="radio" name="_mode" value="other" <?php echo ($_shipment_mode == 'other') ? 'checked' : '' ?> disabled>OTHER
				<input type="text" name="_desc" class="fmt" size="10" maxlength="15" value="<?php echo $_mode_desc ?>" disabled>
			</td>
		</tr>
	</table>
	<table width="100%" class="table_box" cellspacing="1">
		<tr>
			<th rowspan="3" width="12%">SUPPLIER</th>
			<th width="12%">CODE</th>
			<td width="28%"><?php echo $_supplier_code ?></td>
			<th width="15%">NAME</th>
			<td><?php echo $_supplier_name ?></td>
		</tr>
		<tr>
			<th>ATTN</th>
			<td><?php echo $_supplier_attn ?></td>
			<th>CC</th>
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
			<tr>
				<th width="5%">CODE</th>
				<th width="17%">ITEM</th>
				<th>DESC</th>
				<th width="5%">ATT</th>
				<th width="10%">UNIT PRICE<br />US$</th>
				<th width="8%">QTY</th>
				<th width="10%">AMOUNT<br />US$</th>
				<th width="11%">REMARK</th>
				<th width="5%">DEL</th>
			</tr>
		</thead>
		<tbody id="rowPosition">
		</tbody>
	</table>
	<table width="100%" class="table_box">
		<tr>
			<th align="right">GRAND TOTAL</th>
			<th width="8%"><input name="totalQty" type="text" class="reqn" style="width:100%" readonly></th>
			<th width="10%"><input name="totalAmount" type="text" class="reqn" style="width:100%" readonly></th>
			<th width="16%">&nbsp;</th>
		</tr>
	</table><br>
	<span class="bar_bl">OTHERS</span>
	<table width="100%" class="table_box">
		<tr>
			<th width="15%">REMARK</th>
			<td><textarea name="_remark" style="width:100%" rows="4"></textarea></td>
		</tr>
	</table>
<input type="hidden" name="_ordered_by" value="<?php echo $_ordered_by ?>">
</form>
<p align='center'>
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkform(window.document.frmInsert)'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save PL"> &nbsp; Save PL</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_pl_step_3.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel PL"> &nbsp; Cancel PL</button>
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