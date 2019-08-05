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
$left_loc = "summary_po_by_supplier.php";
if (!isset($_GET['_code']) || $_GET['_code'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/summary_po_by_supplier.php");
} else {
	$_code = urldecode($_GET['_code']);
}

//PROCESS FORM
require_once APP_DIR . "_include/warehouse/tpl_process_po_local.php";

//---------------------------------------------------------------------------------------------- DEFAULT PROCESS
$sql	= "SELECT * FROM ".ZKP_SQL."_tb_po_local WHERE po_code = '$_code'";
$result = query($sql);
$column = fetchRowAssoc($result);

if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else if($column['po_confirmed_by_account'] != '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_po.php?_code=$_code");
}

$sql_item	= "
SELECT 
  it_code,
  it_model_no, 
  it_desc, 
  poit_unit, 
  poit_unit_price, 
  poit_qty,
  poit_qty*poit_unit_price AS amount, 
  poit_remark
FROM ".ZKP_SQL."_tb_po_local_item JOIN ".ZKP_SQL."_tb_item USING(it_code) WHERE po_code = '$_code'
ORDER BY it_code";
$res_item	= query($sql_item);
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
	var idx_price	= 17;		/////
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
	
	window.document.all.btnPrint.focus();
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
<h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] DETAIL PO LOCAL</h3>
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode'>
<input type='hidden' name='_code' value="<?php echo $column["po_code"] ?>">
<input type='hidden' name='_revesion_time' value="<?php echo $column["po_revesion_time"] ?>">
<table width="100%" class="table_box">
	<tr>
		<td colspan="2"><span class="bar_bl">PO INFORMATION</span></td>
		<td colspan="2" align="right"><I>Last updated by : <?php echo ucfirst($column['po_lastupdated_by_account']).date(', j-M-Y g:i:s', strtotime($column['po_lastupdated_timestamp']))?></I></td>
	</tr>
	<tr>
		<th width="15%">PO NO</th>
		<td width="34%"><b><?php echo $column["po_code"] ?></b></td>
		<th width="15%">PO DATE</th>
		<td><input name="_po_date" type="text" class="reqd" size="15" value="<?php echo date('d-M-Y', strtotime($column["po_date"])) ?>" maxlength="64"></td>
	</tr>
	<tr>
		<th width="15%">PO TYPE</th>
		<td>
			<input type="radio" name="_po_type" value="1" id="1"<?php echo ($column["po_type"]==1) ? ' checked' : '' ?>><label for="1">NORMAL</label> &nbsp;
			<input type="radio" name="_po_type" value="2" id="2"<?php echo ($column["po_type"]==2) ? ' checked' : '' ?>><label for="2">NON VAT</label>
		</td>
		<th>DELIVERY DATE</th>
		<td><input name="_deli_date" type="text" class="fmtd" size="15" maxlength="64" value="<?php echo ($column["po_delivery_date"]=='') ? '' : date('d-M-Y', strtotime($column["po_delivery_date"])) ?>"></td>
	</tr>
</table>
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<th rowspan="3" width="12%">SUPPLIER</th>
		<th width="12%"><a href="javascript:fillCode('supplier')">CODE</a></th>
		<td width="25%"><input name="_sp_code" type="text" class="req" size="6" value="<?php echo $column["sp_code"] ?>" readOnly></td>
		<th width="15%">NAME</th>
		<td width="43%"><input type="text" name="_sp_name" class="req" style="width:100%" maxlength="125" value="<?php echo $column["po_sp_name"] ?>"></td>
	</tr>
	<tr>
		<th>ATTN</th>
		<td><input type="text" name="_sp_attn" class="fmt" size="25" maxlength="32" value="<?php echo $column["po_sp_attn"] ?>"></td>
		<th>CONTACT</th>
		<td>
		Telp : <input type="text" name="_sp_phone" class="fmt" size="15" maxlength="32" value="<?php echo $column["po_sp_phone"] ?>"> &nbsp;
		Fax : <input type="text" name="_sp_fax" class="fmt" size="15" maxlength="32" value="<?php echo $column["po_sp_fax"] ?>">
		</td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="3"><input type="text" name="_sp_address" class="fmt" style="width:100%" value="<?php echo $column["po_sp_address"] ?>"></td>
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
<?php while($items =& fetchRow($res_item)) { ?>
	<tr id="<?php echo $items[0]?>">
		<td><input type="hidden" name="_it_code[]" value="<?php echo $items[0]?>"><?php echo $items[0]?></td>
		<td><input type="text" name="_it_model_no[]" class="fmt" style="width:100%" value="<?php echo $items[1]?>" readonly></td>
		<td><input type="text" name="_it_desc[]" class="fmt" style="width:100%" value="<?php echo $items[2]?>" readonly></td>
		<td><input type="text" name="_poit_unit[]" class="fmt" style="width:100%" value="<?php echo $items[3]?>" readonly></td>
		<td><input type="text" name="_poit_unit_price[]" class="reqn" style="width:100%" value="<?php echo number_format($items[4]) ?>" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')"></td>
		<td><input type="text" name="_poit_qty[]" class="reqn" style="width:100%" value="<?php echo number_format($items[5]) ?>" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')"></td>
		<td><input type="text" name="_poit_amount[]" class="reqn" style="width:100%" value="<?php echo number_format($items[6],2) ?>" readonly></td>
		<td><input type="text" name="_poit_remark[]" class="fmt" style="width:100%" value="<?php echo $items[7]?>"></td>
		<td align="center"><a href="javascript:deleteItem('<?php echo $items[0]?>')"><img src='../../_images/icon/delete.gif' width='12px'></a></td>
	</tr>
<?php } ?>
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
		<th align="right" colspan="2">Addtional charge 1 : <input name="_add_charge1" type="text" class="fmt" style="width:30%" value="<?php echo $column["po_text_charge1"] ?>"></th>
		<th><input name="totalAdd1" type="text" class="reqn" style="width:100%" onKeyUp="formatNumber(this,'dot')" onBlur="updateAmount()" value="<?php echo number_format($column["po_total_charge1"],2) ?>"></th>
		<th>&nbsp;</th>
	</tr>
	<tr>
		<th align="right" colspan="2">Addtional charge 2 : <input name="_add_charge2" type="text" class="fmt" style="width:30%" value="<?php echo $column["po_text_charge2"] ?>"></th>
		<th style="border-bottom:1px solid #006da5"><input name="totalAdd2" type="text" class="reqn" style="width:100%" onKeyUp="formatNumber(this,'dot')" onBlur="updateAmount()" value="<?php echo number_format($column["po_total_charge2"],2) ?>"></th>
		<th>&nbsp;</th>
	</tr>
	<tr>
		<th align="right" colspan="2">Before VAT</th>
		<th><input name="totalBeforeVat" type="text" class="reqn" style="width:100%" readonly></th>
		<th>&nbsp;</th>
	</tr>
	<tr>
		<th align="right" colspan="2">VAT &nbsp; <input name="vat" type="text" class="reqn" style="width:30px" onKeyUp="formatNumber(this,'dot')" onBlur="updateAmount()" value="<?php echo $column["po_vat"] ?>"> %</th>
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
		<th colspan="4"><input name="_says_in_word" type="text" class="req" style="width:100%" value="<?php echo $column["po_says_in_words"] ?>"></th>
		</th>
	</tr>
</table><br />
<span class="bar_bl">OTHERS</span>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">PREPARED BY</th>
		<td><input type="text" name="_prepared_by" class="req" size="15" maxlength="32" value="<?php echo $column["po_prepared_by"] ?>"></td>
		<th width="15%">CONFIRMED BY</th>
		<td><input type="text" name="_confirmed_by" class="req" size="15" maxlength="32" value="<?php echo $column["po_confirmed_by"] ?>"></td>
		<th width="15%">APPROVED BY</th>
		<td><input type="text" name="_approved_by" class="req" size="15" maxlength="32" value="<?php echo $column["po_approved_by"] ?>"></td>
	</tr>
	<tr>
		<th>DOCUMENT<br />REMARK</th>
		<td colspan="5"><textarea name="_remark" style="width:100%" rows="3"><?php echo $column["po_remark"]?></textarea></td>
	</tr>
</table>
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td>
			<button name='btnDelete' class='input_red' style='width:120px;'><img src="../../_images/icon/trash.gif" width="15px" align="middle" alt="Delete PO"> &nbsp; Delete PO</button>
		</td>
		<td align="right">
			Rev No:
			<select name="_revision_time" id="_revision_time">
			<?php
				for($counter = $column['po_revesion_time']; $counter >= 0; $counter--) {
					echo "\t\t\t<option value=\"$counter\">$counter</option>\n";
				}
			?>
			</select>&nbsp;
			<button name='btnPrint' class='input_btn' style='width:100px;'><img src="../../_images/icon/print.gif" width="18px" align="middle" alt="Print pdf"> &nbsp; Print PDF</button>&nbsp;
			<button name='btnUpdate' class='input_btn' style='width:80px;'><img src="../../_images/icon/update.gif" width="20px" align="middle" alt="Update PO"> &nbsp; Update</button>&nbsp;
			<button name='btnConfirm' class='input_btn' style='width:100px;'> <img src="../../_images/icon/btnSave-black.gif" align="middle" alt="Confirm PO"> &nbsp; Confirm PO</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
		</td>
	</tr>
</table><br /><br />
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmInsert;

	window.document.all.btnDelete.onclick = function() {
		if(confirm("Are you sure to delete?")) {
			oForm.p_mode.value = 'delete_PO';
			oForm.submit();
		}
	}

	window.document.all.btnPrint.onclick = function() {
		var winforPrint = window.open('','','toolbar=no,width=780,height=580,resizable=yes');
		winforPrint.document.location.href = "../../_include/warehouse/pdf/download_po_pdf.php?_code=<?php echo $_code ?>&_po_date=<?php echo $column['po_date'] ?>&_rev=" +  window.document.all._revision_time.value;
	}

	window.document.all.btnUpdate.onclick = function() {
		if (window.rowPosition.rows.length == 0) { 
			alert("You need to choose at least 1 item");
			return;
		}

		if(verify(oForm)){
			if(confirm("Are you sure to update?")) {
				oForm.p_mode.value = 'update_PO';
				oForm.submit();
			}
		}
	}

	window.document.all.btnList.onclick = function() {
		window.location.href = '<?php echo HTTP_DIR . "$currentDept/$moduleDept/summary_po_by_supplier.php" ?>';
	}

	window.document.all.btnConfirm.onclick = function() {
		if(confirm("Are you sure to confirm?\n\n\*If you change some data in this form, please click [UPDATE] first.\n*If you confirm, PO DATA WILL BE FIXED with this qty & Data cannot be modified.")) {
			if(verify(oForm)){
				oForm.p_mode.value = 'confirm_PO';
				oForm.submit();
			}
		}
	}
</script>
<!--END Button-->
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