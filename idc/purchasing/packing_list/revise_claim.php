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
$left_loc = "summary_pl_by_supplier.php";
if (!isset($_GET['_code']) || $_GET['_code'] == '') {
	goPage(HTTP_DIR . $currentDept . '/packing_list/summary_pl_by_supplier.php');
} else {
	$_code = urldecode($_GET['_code']);
}

//------------------------------------------------------------------------------------------------- delete PL
if (ckperm(ZKP_DELETE, HTTP_DIR . $currentDept . "/packing_list/index.php", 'delete')) {

	$result =& query("DELETE FROM ".ZKP_SQL."_tb_claim WHERE cl_idx = $_code");

	if(isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . $currentDept . "/packing_list/revise_claim.php?_code=".urlencode($_code));
	}
	goPage(HTTP_DIR . $currentDept . '/packing_list/summary_pl_by_supplier.php');
}

//------------------------------------------------------------------------------------------------- update PL
if(ckperm(ZKP_INSERT, HTTP_DIR . $currentDept . '/packing_list/index.php', 'update')) {

	$_code			= $_POST['_code'];
	$_sp_code		= strtoupper($_POST['_sp_code']);
	$_sp_name	 	= $_POST['_sp_name'];
	$_inv_no	 	= $_POST['_inv_no'];
	$_inv_date	 	= $_POST['_inv_date'];
	$_etd_date	 	= $_POST['_etd_date'];
	$_eta_date	 	= $_POST['_eta_date'];
	$_shipment_mode		= $_POST['_mode'];
	$_mode_desc		= $_POST['_mode_desc'];
	$_received_by		= $_POST['_received_by'];
	$_remark		= $_POST['_remark'];
	$_lastupdated_by_account = $S->getValue("ma_account");

	//Item Value
	foreach($_POST['_icat_midx'] as $val)		$_icat_midx[]		= $val;
	foreach($_POST['_it_code'] as $val)		$_it_code[]		= $val;
	foreach($_POST['_it_qty'] as $val)		$_it_qty[] 		= $val;
	foreach($_POST['_it_unit_price'] as $val)	$_it_unit_price[]	= $val;
	foreach($_POST['_it_remark'] as $val)		$_it_remark[]		= $val;
	foreach($_POST['_it_att'] as $val)		$_it_att[] 		= $val;

	//make pgsql ARRAY String for many item
	$_icat_midx	= implode(',', $_icat_midx);
	$_it_code	= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_it_qty	= implode(',', $_it_qty);
	$_it_unit_price = implode(',', $_it_unit_price);
	$_it_remark	= '$$' . implode('$$,$$', $_it_remark) . '$$';
	$_it_att	= '$$' . implode('$$,$$', $_it_att) . '$$';

	//update PL Claim
	$result = executeSP(
		ZKP_SQL."_updatePLClaim",
		$_code,
		"$\${$_sp_code}$\$",
		"$\${$_sp_name}$\$",
		"$\${$_inv_no}$\$",
		"$\${$_inv_date}$\$",
		"$\${$_etd_date}$\$",
		"$\${$_eta_date}$\$",
		"$\${$_received_by}$\$",
		"$\${$_lastupdated_by_account}$\$",
		"$\${$_shipment_mode}$\$",
		"$\${$_mode_desc}$\$",
		"$\${$_remark}$\$",
		"ARRAY[$_icat_midx]",
		"ARRAY[$_it_code]",
		"ARRAY[$_it_unit_price]",
		"ARRAY[$_it_qty]",
		"ARRAY[$_it_remark]",
		"ARRAY[$_it_att]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result,  HTTP_DIR . $currentDept . "/packing_list/revise_claim.php?_code=$_code");
	}
	$M->goPage(HTTP_DIR . $currentDept . "/packing_list/revise_claim.php?_code=".$_code);
}

//-------------------------------------------------------------------------------------------- DEFAULT PROCESS
$sql	= "SELECT *, ".ZKP_SQL."_isArrivedPL(2,cl_idx) AS is_arrived FROM ".ZKP_SQL."_tb_claim WHERE cl_idx = $_code";
$result = query($sql);
$column = fetchRowAssoc($result);

if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . $currentDept . '/packing_list/index.php');
}

$sql_item = "
SELECT 
 b.icat_midx, 		--0
 b.it_code,		--1
 b.it_model_no,		--2
 b.it_desc,		--3
 a.clit_unit_price,	--4
 a.clit_qty,		--5
 a.clit_unit_price*a.clit_qty,	--6
 a.clit_remark,		--7
 a.clit_attribute	--8
FROM
  ".ZKP_SQL."_tb_claim_item AS a
  JOIN ".ZKP_SQL."_tb_item AS b USING(it_code)
WHERE cl_idx = $_code
ORDER BY it_code";
$res_item	=& query($sql_item);
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
		o.submit();
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

//Delete Item wtd rows collection
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

//Reculate Amount base on the form element
function updateAmount(){

	var f			= window.document.frmInsert;
	var numItem		= window.rowPosition.rows.length;
	var numInput	= 9;

	var idx_price	= 20;			/////
	var idx_qty		= idx_price+1;
	var idx_amount	= idx_price+2;

	var sumOfQty	= 0;
	var sumOfTotal	= 0;
	var e = window.document.frmInsert.elements;

	for (var i = 0; i< numItem; i++) {
		var price	= parseFloat(removecomma(e(idx_price+i*numInput).value));	
		var qty 	= parseFloat(removecomma(e(idx_qty+i*numInput).value));

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

	var x = (screen.availWidth - 400) / 2;
	var y = (screen.availHeight - 600) / 2;
	var win = window.open(
		file + keyword,
		target,
		'scrollbars,width=400,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

function initPage() {
	updateAmount();

	<?php if($column['is_arrived']=='t') { ?>
	window.document.all.btnDelete.disabled = true;
	window.document.all.btnUpdate.disabled = true;
	<?php } ?>
}
</script>
</head>
<body topmargin="0" leftmargin="0" onload="initPage()">
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
<strong style="font-size:18px;font-weight:bold">
[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] DETAIL PL<br />
</strong>
<small class="comment">* Source by claim</small>
<hr><br />
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode'>
<input type="hidden" name="_code" value="<?php echo $column['cl_idx']?>">
<input type="hidden" name="_shipment_mode" value="<?php echo $column['cl_shipment_mode']?>">
	<table width="100%" class="table_box">
		<tr>
			<td colspan="3"><strong class="info">PL INFORMATION</strong></td>
			<td colspan="2" align="right">
				<I>Last updated by : <?php echo $column['cl_lastupdated_by_account'].date(', j-M-Y g:i:s', strtotime($column['cl_lastupdated_timestamp']))?></I>
			</td>
		</tr>
		<tr>
			<th width="12%">INVOICE NO</th>
			<td width="40%" colspan="2"><input name="_inv_no" type="text" class="req" size="15" maxlength="64" value="<?php echo $column["cl_inv_no"] ?>"></td>
			<th width="12%">INVOICE DATE</th>
			<td><input name="_inv_date" type="text" class="reqd" size="15" maxlength="64" value="<?php echo date('j-M-Y', strtotime($column['cl_inv_date']))?>"></td>
		</tr>
		<tr>
			<th>ETD DATE</th>
			<td colspan="2"><input type="text" name="_etd_date" class="reqd" size="15" value="<?php echo date('j-M-Y', strtotime($column['cl_etd_date'])) ?>"></td>
			<th>ETA DATE</th>
			<td><input type="text" name="_eta_date" class="reqd" size="15" value="<?php echo date('j-M-Y', strtotime($column['cl_eta_date'])) ?>"></td>
		</tr>
		<tr>
			<th>RECEIVED BY</th>
			<td colspan="2"><input name="_received_by" type="text" class="req" size="20" maxlength="32" value="<?php echo $column["cl_received_by"]?>"></td>
		</tr>
		<tr>
			<th>PL TYPE</th>
			<td colspan="2">
				<input type="radio" name="_type" value="1" <?php echo ($column["cl_type"] == 1) ? 'checked' : '' ?> disabled>NORMAL &nbsp;
				<input type="radio" name="_type" value="2" <?php echo ($column["cl_type"] == 2) ? 'checked' : '' ?> disabled>DOOR TO DOOR
			</td>
			<th>SHIPMENT MODE</th>
			<td colspan="2">
				<input type="radio" name="_mode" value="sea" <?php echo (trim($column["cl_shipment_mode"]) == 'sea') ? 'checked' : '' ?>>SEA &nbsp;
				<input type="radio" name="_mode" value="air" <?php echo (trim($column["cl_shipment_mode"]) == 'air') ? 'checked' : '' ?>>AIR &nbsp;
				<input type="radio" name="_mode" value="other" <?php echo (trim($column["cl_shipment_mode"]) == 'other') ? 'checked' : '' ?>>OTHER
				<input type="text" name="_mode_desc" class="fmt" size="10" maxlength="15" value="<?php echo $column["cl_shipment_desc"] ?>">
			</td>
		</tr>
		<tr>
			<th>SUPPLIER</th>
			<th width="12%"><a href="javascript:fillCode('supplier')">CODE</a></th>
			<td><input type="text" name="_sp_code" class="req" size="6" maxlength="4" value="<?php echo $column['cl_sp_code']?>"></td>
			<th>NAME</th>
			<td><input type="text" name="_sp_name" class="req" style="width:100%" value="<?php echo $column['cl_sp_name']?>"></td>
		</tr>
	</table><br />
	<strong class="info">ITEM LIST</strong> &nbsp; <small class="comment"><i><a href="javascript:fillItem()">( search <img src="../../_images/icon/search_mini.gif"> )</a></i></small>
	<table width="100%" class="table_box">
		<thead>
			<tr>
				<th width="5%">CODE</th>
				<th width="17%">ITEM</th>
				<th>DESC</th>
				<th width="8%">ATT</th>
				<th width="10%">UNIT PRICE<br />US$</th>
				<th width="8%">QTY</th>
				<th width="10%">AMOUNT<br />US$</th>
				<th width="11%">REMARK</th>
				<th width="5%">DEL</th>
			</tr>
		</thead>
		<tbody id="rowPosition">
<?php
while($items =& fetchRow($res_item)) {
	$max = $items[5];
	//$max = $qty[$items[1]]+$items[5];
?>
			<tr id="<?php echo trim($items[1])?>">
				<td><?php echo $items[1]?><input type="hidden" name="_it_code[]" value="<?php echo $items[1]?>"></td>
				<td><input type="text" name="_it_item[]" value="<?php echo $items[2]?>" style="width:100%" class="fmt"></td>
				<td><input type="text" name="_it_desc[]" value="<?php echo $items[3]?>" style="width:100%" class="fmt"></td>
				<td><input type="text" name="_it_att[]" value="<?php echo trim($items[8])?>" style="width:100%" class="fmt"></td>
				<td><input type="text" name="_it_unit_price[]" value="<?php echo number_format($items[4],2)?>" style="width:100%" class="reqn" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')"></td>
				<td><input type="text" name="_it_qty[]" value="<?php echo number_format($items[5],2)?>" style="width:100%" class="reqn" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')"></td>
				<td><input type="text" name="_it_amount[]" value="<?php echo number_format($items[6],2)?>" style="width:100%" class="reqn" readonly></td>
				<td><input type="text" name="_it_remark[]" value="<?php echo $items[7]?>" style="width:100%" class="fmt"></td>
				<td align="center">
					<input type="hidden" name="_icat_midx[]" value="<?php echo $items[0]?>">
					<a href="javascript:deleteItem('<?php echo trim($items[1])?>')"><img src='../../_images/icon/delete.gif' width='12px'></a>
				</td>
			</tr>
<?php } ?>
		</tbody>
	</table>
	<table width="100%" class="table_box">
		<tr>
			<th align="right">GRAND TOTAL</th>
			<th width="8%"><input name="totalQty" type="text" class="reqn" style="width:100%" readonly></th>
			<th width="10%"><input name="totalAmount" type="text" class="reqn" style="width:100%" readonly></th>
			<th width="16%">&nbsp;</th>
		</tr>
	</table><br />
	<strong class="info">OTHERS</strong>
	<table width="100%" class="table_box">
		<tr>
			<th width="15%">REMARK</th>
			<td><textarea name="_remark" style="width:100%" rows="3"><?php echo $column["cl_remark"] ?></textarea></td>
		</tr>
	</table>
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td>
			<button name='btnDelete' class='input_btn' style='width:120px;'><img src="../../_images/icon/trash.gif" width="15px" align="middle" alt="Delete PL"> &nbsp; Delete PL</button>
		</td>
		<td align="right">
			<button name='btnUpdate' class='input_btn' style='width:80px;'><img src="../../_images/icon/update.gif" width="20px" align="middle" alt="Update replace claim"> &nbsp; Update</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
		</td>
	</tr>
</table>
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmInsert;

	window.document.all.btnDelete.onclick = function() {
		if(confirm("Are you sure to delete?")) {
			oForm.p_mode.value = 'delete';
			oForm.submit();
		}
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
		window.location.href = '<?php echo HTTP_DIR . $currentDept . '/packing_list/summary_pl_by_supplier.php' ?>';
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