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
$left_loc = "revise_po.php";
if (!isset($_GET['_code']) || $_GET['_code'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/summary_po_by_supplier.php");
} else {
	$_code = urldecode($_GET['_code']);
}
$title = array(1=>"Issue PO &amp; Order Item","Invoice only");

//PROCESS FORM
require_once APP_DIR . "_include/purchasing/tpl_process_form.php";

//DEFAULT PROCESS =====================================================================================================
$sql	= "SELECT * FROM ".ZKP_SQL."_tb_po WHERE po_code = '$_code'";
$result = query($sql);
$column = fetchRowAssoc($result);

if($column['po_confirmed_by_account'] != '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_po.php?_code=$_code");
}
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
		o.submit();
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

			case 1: // MODEL NO
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
				oTextbox[i].onblur = function() {updateAmount();}
				oTextbox[i].onkeyup		= function() {formatNumber(this, 'dot');}
				break;

			case 5: // QTY
				oTD[i].align			= "right";
				oTextbox[i].style.width = "100%";
				oTextbox[i].className	= "fmtn";
				oTextbox[i].name		= "_poit_qty[]";
				oTextbox[i].value		= addcomma(f2.elements[6].value);
				oTextbox[i].onblur = function() {updateAmount();}
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
				oTextbox[i].style.widt	= "100%";
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

	if (window.rowPosition.rows.length == 1) {
		alert("You need to leave at least 1 item");
		return;
	}

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
	var numItem		= window.rowPosition.rows.length; // number of Item
	var numInput	= 9; // number of Input Element in one Row (item)

	var idx_price	= 24; //first row's idx
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

function fillCode(target) {

	if(target == 'supplier') {
		var file	= './p_list_supplier.php?_name=';
		var keyword = window.document.frmInsert._sp_code.value;
	} else if(target == 'forwarder') {
		var file	= './p_list_forwarder.php?_name=';
		var keyword = window.document.frmInsert._fw_code.value;
	}

	var x = (screen.availWidth - 470) / 2;
	var y = (screen.availHeight - 600) / 2;
	var win = window.open(
		file + keyword,
		target,
		'scrollbars,width=470,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

function  enabledModeOther(value) {

	var f = window.document.frmInsert;

	if(value == 2) {
		f._mode_desc.readOnly = false;
		f._mode_desc.value = '<?php echo $column["po_shipment_desc"] ?>';
		f._mode_desc.focus();
	} else {
		f._mode_desc.readOnly = true;
		f._mode_desc.value = '';
	}
	
}

function seePOLayout() {

	var f = window.document.frmInsert;

	if(f._layout_type[0].checked == true){
		var type = 1;
	} else if(f._layout_type[1].checked == true){
		var type = 2;
	} else if(f._layout_type[2].checked == true){
		var type = 3;
	} else if(f._layout_type[3].checked == true){
		var type = 4;
	}

	var x = (screen.availWidth - 600) / 2;
	var y = (screen.availHeight - 250) / 2;
	var win = window.open(
		'./p_po_layout.php?_type=' + type,
		'',
		'scrollbars,width=600,height=250,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

function initPage() {
	var f = window.document.frmInsert;
	if(f._shipment_mode[2].checked == true) {
		f._mode_desc.readOnly = false;
	} else {
		f._mode_desc.readOnly = true;
	}

	updateAmount();
	window.document.all.btnPrint.focus();
}
</script>
</head>
<body topmargin="0" leftmargin="0" onLoad="initPage()">
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
		[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] REVISE PO<br />
		</strong>
	</td>
  </tr>
  <tr>
	<td colspan="2"><small class="comment">* <?php echo $title[$column['po_type_invoice']] ?></small></td>
  </tr>
</table>
<hr><br />
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode'>
<input type="hidden" name="_code" value="<?php echo $column['po_code']?>">
<input type='hidden' name="_revision_time" value="<?php echo $column['po_revesion_time']?>">
<input type="hidden" name="_po_type" value="<?php echo $column['po_type']?>">
	<table width="100%" class="table_box">
		<tr>
			<td colspan="2"><strong>PO INFORMATION</strong></td> 
			<td colspan="2" align="right">
				<I>Last updated by : <?php echo $column['po_lastupdated_by_account'].date(', j-M-Y g:i:s', strtotime($column['po_lastupdated_timestamp']))?></I>
			</td>
		</tr>
		<tr>
			<th width="15%">PO NO</th>
			<td><span class="bar"><?php echo $column['po_code'] ?></span></td>
			<th width="15%">PO DATE</th>
			<td><input type="text" name="_po_date" class="req" value="<?php echo date('j-M-Y', strtotime($column['po_date'])) ?>"></td>
		</tr>
		<tr>
			<th width="15%">PO TYPE</th>
			<td>
				<input type="radio" name="_type" value="1" disabled <?php echo ($column['po_type'] == 1) ? "checked" : "" ?>>NORMAL &nbsp;
				<input type="radio" name="_type" value="2" disabled <?php echo ($column['po_type'] == 2) ? "checked" : "" ?>>DOOR TO DOOR
			</td>
			<th>SHIPMENT MODE</th>
			<td>
				<input type="radio" name="_shipment_mode" value="sea" id="sea" <?php echo (trim($column['po_shipment_mode']) == 'sea') ? "checked" : "" ?> onClick="enabledModeOther(0)"><label for="sea">SEA &nbsp; </label>
				<input type="radio" name="_shipment_mode" value="air" id="air" <?php echo (trim($column['po_shipment_mode']) == 'air') ? "checked" : "" ?> onClick="enabledModeOther(1)"><label for="air">AIR &nbsp; </label>
				<input type="radio" name="_shipment_mode" value="other" id="other" <?php echo ($column['po_shipment_mode'] == 'other') ? "checked" : "" ?> onClick="enabledModeOther(2)"><label for="other">OTHER &nbsp; </label>
				<input type="text" name="_mode_desc" class="fmt" size="10" maxlength="15" value="<?php echo $column["po_shipment_desc"] ?>" readonly>
			</td>
		</tr>
		<tr>
			<th width="15%">RECEIVED BY</th>
			<td width="34%"><input type="text" name="_received_by" class="req" value="<?php echo $column['po_received_by']?>"></td>
			<th>LAYOUT TYPE</th>
			<td>
				<input type="radio" name="_layout_type" value="1" id="1" <?php echo ($column['po_layout_type'] == 1) ? "checked" : "" ?>><label for="1">1 &nbsp; &nbsp; </label> 
				<input type="radio" name="_layout_type" value="2" id="2" <?php echo ($column['po_layout_type'] == 2) ? "checked" : "" ?>><label for="2">2 &nbsp; &nbsp; </label>
				<input type="radio" name="_layout_type" value="3" id="3" <?php echo ($column['po_layout_type'] == 3) ? "checked" : "" ?>><label for="3">3 &nbsp; &nbsp; </label>
				<input type="radio" name="_layout_type" value="4" id="4" <?php echo ($column['po_layout_type'] == 4) ? "checked" : "" ?>><label for="4">4 &nbsp; &nbsp; </label>
				<a href="javascript:seePOLayout()"><small>see layout</small></a>
			</td>
		</tr>
		<tr>
			<th>CURRENCY TYPE</th>
			<td>
				<input type="radio" name="_currency_type" value="1" <?php echo ($column['po_currency_type'] == 1) ? "checked" : "" ?> id="usd"><label for="usd">USD &nbsp; &nbsp;</label>
				<input type="radio" name="_currency_type" value="2" <?php echo ($column['po_currency_type'] == 2) ? "checked" : "" ?> id="rp"><label for="rp">RUPIAH &nbsp; &nbsp;</label>
			</td>
		</tr>
	</table>
	<table width="100%" class="table_box" cellspacing="1">
		<tr>
			<th width="12%">SUPPLIER</th>
			<th width="12%"><a href="javascript:fillCode('supplier')">CODE</a></th>
			<td width="25%"><input type="text" name="_sp_code" class="req" size="6" maxlength="4" value="<?php echo $column['po_sp_code']?>"></td>
			<th width="15%">NAME</th>
			<td width="43%"><input type="text" name="_sp_name" class="req" style="width:100%" value="<?php echo $column['po_sp_name']?>"></td>
		</tr>
	</table><br />
	<strong>ITEM LIST</strong> <small class="comment"><i><a href="javascript:fillItem()">( search <img src="../../_images/icon/search_mini.gif"> )</a></i></small>
	<table width="100%" class="table_box">
		<thead>
			<tr>
				<th width="5%">CODE</th>
				<th width="17%">ITEM</th>
				<th width="25%">DESC</th>
				<th width="5%">ATT</th>
				<th width="12%">UNIT PRICE<br />(US$)</th>
				<th width="8%">QTY</th>
				<th width="12%">AMOUNT<br />(US$)</th>
				<th width="11%">REMARK</th>
				<th width="5%">DEL</th>
			</tr>
		</thead>
		<tbody id="rowPosition">
<?php
$sql = "
SELECT 
 icat_midx, 	--0
 it_code,		--1
 poit_item,		--2
 poit_desc,		--3
 poit_unit_price,	--4
 poit_qty,		--5
 poit_unit_price * poit_qty AS amount,	--6
 poit_remark,	--7
 poit_attribute	--8
FROM ".ZKP_SQL."_tb_po_item
WHERE po_code = '$_code'
ORDER BY it_code";
$result	=& query($sql);
while($items =& fetchRow($result)) {
?>
			<tr id="<?php echo trim($items[1])?>">
				<td><?php echo $items[1]?><input type="hidden" name="_it_code[]" value="<?php echo $items[1]?>"></td>
				<td><input type="text" name="_poit_item[]" value="<?php echo $items[2]?>" style="width:100%" class="fmt"></td>
				<td><input type="text" name="_poit_desc[]" value="<?php echo $items[3]?>" style="width:100%" class="fmt"></td>
				<td><input type="text" name="_poit_att[]" value="<?php echo trim($items[8])?>" style="width:100%" class="fmt"></td>
				<td><input type="text" name="_poit_unit_price[]" value="<?php echo number_format($items[4],2)?>" class="reqn" style="width:100%" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')"></td>
				<td><input type="text" name="_poit_qty[]" value="<?php echo number_format($items[5])?>" style="width:100%" class="reqn" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')"></td>
				<td><input type="text" name="_poit_amount[]" value="<?php echo number_format($items[6],2)?>" class="reqn" style="width:100%" readonly></td>
				<td><input type="text" name="_poit_remark[]" value="<?php echo $items[7]?>" style="width:100%" class="fmt"></td>
				<td align="center">
					<input type="hidden" name="_icat_midx[]" value="<?php echo $items[0]?>">
					<a href="javascript:deleteItem('<?php echo trim($items[1])?>')"><img src='../../_images/icon/delete.gif' width='12px'></a>
				</td>
			</tr>
<?php
} //END WHILE
?>
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
	<strong>OTHERS</strong>
	<table width="100%" class="table_box">
		<tr>
			<th width="15%">PREPARED BY</th>
			<td><input type="text" name="_prepared_by" class="req" maxlength="32" value="<?php echo $column['po_prepared_by']?>"></td>
			<th width="15%">CONFIRMED BY</th>
			<td><input type="text" name="_confirmed_by" class="req" maxlength="32" value="<?php echo $column['po_confirmed_by']?>"></td>
		</tr>
		<tr>
			<th>REMARK</th>
			<td colspan="3"><textarea name="_remark" style="width:100%" rows="3"><?php echo $column['po_remark']?></textarea></td>
		</tr>
		<tr>
			<th>PO PRINT<br />REMARK</th>
			<td colspan="3"><textarea name="_print_remark" style="width:100%" rows="4"><?php echo $column['po_doc_remark']?></textarea></td>
		</tr>
	</table>
<input type="hidden" name="_po_type_invoice" value="<?php echo $column['po_type_invoice']?>">
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
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
		</td>
	</tr>
</table><br /><br />
<hr>
<table width="100%">
	<tr>
		<td><strong>CONFIRM PO</strong></td>
		<td align="right"><button name='btnConfirm' class='input_btn' style='width:100px;'> <img src="../../_images/icon/btnSave-black.gif" align="middle" alt="Confirm PO"> &nbsp; Confirm PO</button>&nbsp;</td>
	</tr>
</table>
<hr>
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmInsert;

	window.document.all.btnDelete.onclick = function() {
		if(confirm("Are you sure to delete?")) {
			oForm.p_mode.value = 'delete';
			oForm.submit();
		}
	}

	window.document.all.btnPrint.onclick = function() {
		<?php
		if(ZKP_SQL == 'IDC') {
			if($column['po_currency_type'] == 1) 	 $code = substr($_code,0,2)."-". substr($_code,3,2)."-".substr($_code,6,2);
			else if($column['po_currency_type'] == 2) $code = substr($_code,0,2)."-". substr($_code,3,3)."-".substr($_code,7,2);
		} else if(ZKP_SQL == 'MED') {
			$no = explode('/', $_code);
			if ($no[0] < 100) {
				if($column['po_type'] == 1)		$code = substr($_code,0,2)."-". substr($_code,3,4)."-".substr($_code,8,2);
				else if($column['po_type'] == 2)	$code = substr($_code,0,2)."-". substr($_code,3,5)."-".substr($_code,9,2);
			} else {
				if($column['po_type'] == 1)		$code = substr($_code,0,3)."-". substr($_code,4,4)."-".substr($_code,9,2);
				else if($column['po_type'] == 2)	$code = substr($_code,0,3)."-". substr($_code,4,5)."-".substr($_code,10,2);
			}
		}
		?>
		var winforPrint = window.open('','','toolbar=no,width=780,height=580,resizable=yes');
		winforPrint.document.location.href = "../../_include/purchasing/pdf/download_pdf.php?_code=<?php echo $code ?>&_po_date=<?php echo $column['po_date'] ?>&_rev=" + window._revision_time.value;
	}

	window.document.all.btnUpdate.onclick = function() {
		if(confirm("Are you sure to update?")) {
			if(verify(oForm)){
				oForm.p_mode.value = 'update';
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
				oForm.p_mode.value = 'confirm';
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