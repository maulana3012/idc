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
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = 'daily_summary_by_group.php';
if (!isset($_GET['_code']) || $_GET['_code'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else {
	$_code = urldecode($_GET['_code']);
}
$ordby	= array(1=>'INDOCORE PERKASA', 2=>'MEDIKUS EKA');

//PROCESS FORM
require_once APP_DIR . "_include/other/tpl_process_form.php";

//========================================================================================= DEFAULT PROCESS
if(ZKP_FUNCTION != 'ALL') {
	$tmp[] = ZKP_SQL."_isValidShowInvoice('".ZKP_FUNCTION."', '$_code','dr')";
}
$tmp[]	  = "dr_code = '$_code'";
$strWhere = implode(" AND ", $tmp);

//dr
$sql	= "SELECT *,
	(SELECT book_idx FROM ".ZKP_SQL."_tb_booking WHERE book_doc_ref='$_code' AND book_doc_type=5) AS book_idx,
	(SELECT book_is_revised FROM ".ZKP_SQL."_tb_booking WHERE book_doc_ref='$_code' AND book_doc_type=5) AS book_is_revised
	FROM ".ZKP_SQL."_tb_dr WHERE $strWhere";
$result =& query($sql);
$column =& fetchRowAssoc($result);

if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else if($column['dr_cfm_wh_delivery_by_account'] == '') {
	// Harus DO yang Sudah di confirm gudang
	$message = new ZKError(
		"HAS_NOT_CONFIRMED_BY_WAREHOUSE",
		"HAS_NOT_CONFIRMED_BY_WAREHOUSE",
		"DR no $_code has not been confirmed by warehouse. Please check again");
	$M->goErrorPage($message,  HTTP_DIR . "$currentDept/$moduleDept/revise_dr.php?_code=".urlencode($_code));
} else if($column['book_is_revised'] == 't') {
	// Bukan DO yang dalam proses revisi
	$message = new ZKError(
		"ERROR_STATUS_REVISED",
		"ERROR_STATUS_REVISED",
		"Dokumen no $_code status is revised. You cannot change item or qty before the document confirm by warehouse");
	$M->goErrorPage($message,  HTTP_DIR . "$currentDept/$moduleDept/revise_dr.php?_code=".urlencode($_code));
}

//[WAREHOUSE] billing item
$whitem_sql = "
SELECT
  a.it_code,			--0
  a.it_model_no,		--1
  a.it_desc,			--2
  b.boit_it_code_for,	--3
  b.boit_qty,			--4
  b.boit_function,		--5
  b.boit_remark, 		--6
  b.boit_type			--7
FROM
  ".ZKP_SQL."_tb_booking_item AS b
  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE b.book_idx = ".$column['book_idx']."
ORDER BY a.it_code, b.boit_idx";
$whitem_res	=& query($whitem_sql);

//[CUSTOMER] billing item
$cusitem_sql = "
SELECT
  a.it_code,			--0
  a.it_model_no,		--1
  a.it_desc,			--2
  b.drit_qty,			--3
  b.drit_remark 		--4
FROM
  ".ZKP_SQL."_tb_dr_item AS b
  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE dr_code = '$_code'
ORDER BY it_code, drit_idx";
$cusitem_res	=& query($cusitem_sql);
/*
echo "<pre>";
var_dump($cusitem_sql);
echo "</pre>";
*/
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
function fillCustomer(target) {
	if (target == 'customer') {
		keyword = window.document.frmInsert._cus_to.value;
	} else if (target == 'ship') {
		keyword = window.document.frmInsert._ship_to.value;
	}

	var x = (screen.availWidth - 400) / 2;
	var y = (screen.availHeight - 600) / 2;
	var win = window.open(
		'../../_include/other/p_list_cus_code.php?_dept=<?php echo $department ?>&_check_code='+ keyword,
		target,
		'scrollbars,width=400,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

function seedetailreturn() {
	var x = (screen.availWidth - 500) / 2;
	var y = (screen.availHeight - 600) / 2;
	var win = window.open("<?php APP_DIR . "_include/order/p_detail_return.php?_code=" . $column['dr_turn_code'] ?>",'',
		'scrollbars,width=500,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

//Open window for search item
var wSearchItem;
function fillItem(){
	var x = (screen.availWidth - 580) / 2;
	var y = (screen.availHeight - 350) / 2;
	var type = window.document.frmInsert._type_item.value;

	wSearchItem = window.open("p_list_item_1.php?_type="+type,'wSearchItem',
		'scrollbars,width=550,height=350,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wSearchItem.focus();
}

//It Called by child window window's name is wSearchItem
function createItem() {

	var f2	  = wSearchItem.document.frmCreateItem;
	var oTR_1 = window.document.createElement("TR");
	var oTR_2 = window.document.createElement("TR");
	var oTD_1 = new Array();
	var oTD_2 = new Array();
	var oTextbox_1 = new Array();
	var oTextbox_2 = new Array();

	var count = window.itemCusPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.itemCusPosition.rows(i);
		if (oRow.id == trim(f2.elements[12].value)) {
			alert("[" + trim(f2.elements[12].value) + "] " + f2.elements[13].value + " already exist in customer item list");
			return;
		}
	}

	//Print cell for WH
	for (var i=0; i<8; i++) {
		oTD_1[i] = window.document.createElement("TD");
		oTextbox_1[i] = window.document.createElement("INPUT");
		oTextbox_1[i].type = "text";

		switch (i) {
			case 0: // _wh_it_code
				oTD_1[i].innerText	= trim(f2.elements[0].value);
				oTextbox_1[i].type	= "hidden";
				oTextbox_1[i].name	= "_wh_it_code[]";
				oTextbox_1[i].value	= f2.elements[0].value;
				break;

			case 1: // _wh_it_code_for
				oTD_1[i].innerText	= trim(f2.elements[12].value);
				oTextbox_1[i].type	= "hidden";
				oTextbox_1[i].name	= "_wh_it_code_for[]";
				oTextbox_1[i].value	= f2.elements[12].value;
				break;

			case 2: // _wh_it_model_no
				oTD_1[i].innerText	= f2.elements[3].value;
				oTextbox_1[i].type	= "hidden";
				oTextbox_1[i].name	= "_wh_it_model_no[]";
				oTextbox_1[i].value	= f2.elements[3].value;
				break;

			case 3: // _wh_it_desc
				oTD_1[i].innerText	= f2.elements[4].value;
				oTextbox_1[i].type	= "hidden";
				oTextbox_1[i].name	= "_wh_it_desc[]";
				oTextbox_1[i].value	= f2.elements[4].value;
				break;

			case 4: // _wh_it_qty
				oTD_1[i].innerText	= numFormatval(f2.elements[5].value+'',2);
				oTD_1[i].align		= "right";
				oTextbox_1[i].type	= "hidden";
				oTextbox_1[i].name	= "_wh_it_qty[]";
				oTextbox_1[i].value	= parseFloat(f2.elements[5].value);
				break;

			case 5: // _wh_it_function
				oTD_1[i].innerText	= numFormatval(f2.elements[6].value+'',2);
				oTD_1[i].align		= "right";
				oTextbox_1[i].type	= "hidden";
				oTextbox_1[i].name	= "_wh_it_function[]";
				oTextbox_1[i].value	= numFormatval(f2.elements[6].value+'',2);
				break;

			case 6: // _wh_it_remark
				oTextbox_1[i].style.width	= "100%";
				oTextbox_1[i].className		= "fmt";
				oTextbox_1[i].name			= "_wh_it_remark[]";
				oTextbox_1[i].value			= f2.elements[7].value;
				break;

			case 7: // DELETE
				oTD_1[i].innerHTML	= "<a href=\"javascript:deleteWHItem('" + trim(f2.elements[0].value) +'-'+ trim(f2.elements[12].value) + "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD_1[i].align		= "center";
				break;
		}

		if (i!=7) oTD_1[i].appendChild(oTextbox_1[i]);
		oTR_1.id = trim(f2.elements[0].value)+'-'+trim(f2.elements[12].value);
		oTR_1.appendChild(oTD_1[i]);
	}
	window.itemWHPosition.appendChild(oTR_1);

	if(f2.elements[9].checked==true) {var i = 8;}
	else {var i = 14;}

	//Print cell for Customer
	for (var i=i; i<14; i++) {
		oTD_2[i] = window.document.createElement("TD");
		oTextbox_2[i] = window.document.createElement("INPUT");
		oTextbox_2[i].type = "text";

		switch (i) {
			case 8: // _cus_it_code
				oTD_2[i].innerText	= trim(f2.elements[12].value);
				oTextbox_2[i].type	= "hidden";
				oTextbox_2[i].name	= "_cus_it_code[]";
				oTextbox_2[i].value	= f2.elements[12].value;
				break;

			case 9: // _cus_it_model_no
				oTD_2[i].innerText	= f2.elements[13].value;
				oTextbox_2[i].type	= "hidden";
				oTextbox_2[i].name	= "_cus_it_model_no[]";
				oTextbox_2[i].value	= f2.elements[13].value;
				break;

			case 10: // _cus_it_desc
				oTD_2[i].innerText	= f2.elements[14].value;
				oTextbox_2[i].type	= "hidden";
				oTextbox_2[i].name	= "_cus_it_desc[]";
				oTextbox_2[i].value	= f2.elements[14].value;
				break;

			case 11: // _cus_it_qty
				oTextbox_2[i].style.width	= "100%";
				oTextbox_2[i].className		= "reqn";
				oTextbox_2[i].name			= "_cus_it_qty[]";
				oTextbox_2[i].value			= numFormatval(removecomma(f2.elements[15].value)+'',0);
				oTextbox_2[i].onblur		= function() {updateAmount();}
				oTextbox_2[i].onkeyup		= function() {formatNumber(this, 'dot');}
				break;

			case 12: // _cus_it_remark
				oTextbox_2[i].style.width	= "100%";
				oTextbox_2[i].className		= "fmt";
				oTextbox_2[i].name			= "_cus_it_remark[]";
				oTextbox_2[i].value			= f2.elements[16].value;
				break;

			case 13: // DELETE
				oTD_2[i].innerHTML	= "<a href=\"javascript:deleteCusItem('" + trim(f2.elements[12].value) + "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD_2[i].align		= "center";
				break;
		}
		if (i!=13) oTD_2[i].appendChild(oTextbox_2[i]);
		oTR_2.id = trim(f2.elements[12].value);
		oTR_2.appendChild(oTD_2[i]);
	}
	if(f2.elements[9].checked==true) {window.itemCusPosition.appendChild(oTR_2);}
	updateAmount();
}

//Delete Item rows collection
function deleteWHItem(idx) {

	var count = window.itemWHPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.itemWHPosition.rows(i);
		if (oRow.id == idx) {
			var code_ref = trim(oRow.cells(1).innerText);
			var n = window.itemWHPosition.removeChild(oRow);
			count = count - 1;
			break;
		}
	}
	deleteCusItem(code_ref);
	updateAmount();
}

function deleteCusItem(idx) {
	var count = window.itemCusPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.itemCusPosition.rows(i);
		if (oRow.id == idx) {
			var n = window.itemCusPosition.removeChild(oRow);
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
	var countWH		= window.itemWHPosition.rows.length;
	var countCus	= window.itemCusPosition.rows.length;
	var numInputWH	= 7;
	var numInputCus	= 5;

	var idx_qty1	= 24;		/////
	var idx_qty2	= idx_qty1+(numInputWH*countWH);

	var sumOfQty1	= 0;
	var sumOfQty2	= 0;

	for (var i=0; i<countWH; i++) {
		var qty = parseFloat(removecomma(e(idx_qty1+i*numInputWH).value));
		sumOfQty1	+= qty;
	}

	for (var i=0; i<countCus; i++) {
		var qty	  = parseFloat(removecomma(e(idx_qty2+i*numInputCus).value));
		sumOfQty2	+= qty;
	}

	f.totalWhQty.value	  = numFormatval(sumOfQty1 + '', 2);
	f.totalCusQty.value	  = addcomma(sumOfQty2);
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
<table width="100%">
  <tr>
	<td valign="top">
		<strong style="font-size:18px;font-weight:bold">
		[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] REVISE DO REPLACE<br />
		</strong>
	</td>
	<td valign="center" width="25%" align="right" rowspan="2" style="background-color:#F3F3F3;color: #016FA1;">
		<h3><?php echo $ordby[$column['dr_ordered_by']] ?></h3>
	</td>
  </tr>
</table>
<hr><br />
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode'>
<input type='hidden' name='_code' value="<?php echo $column['dr_code'] ?>">
<input type="hidden" name="_dept" value="<?php echo $column['dr_dept']?>">
<input type='hidden' name="_revision_time" value="<?php echo $column['dr_revesion_time']?>">
<input type="hidden" name="_book_idx" value="<?php echo $column['book_idx']?>">
<input type="hidden" name="_type_item" value="<?php echo $column['dr_type_item']?>">
<input type='hidden' name='_date' value="<?php echo $column['dr_date'] ?>">
<input type='hidden' name='_turn_code' value="<?php echo $column['dr_turn_code'] ?>">
<input type='hidden' name='_turn_date' value="<?php echo $column['dr_turn_date'] ?>">
<table width="100%" class="table_box">
	<tr>
		<td colspan="2"><strong class="info">DR INFORMATION</strong></td>
		<td colspan="2" align="right">
			<i>Last updated by : <?php echo $column['dr_lastupdated_by_account'].date(', j-M-Y g:i:s', strtotime($column['dr_lastupdated_timestamp']))?></i>
		</td>
	</tr>
	<tr>
		<th width="12%">DR NO</th>
		<td width="30%"><b><?php echo $column['dr_code'] ?></b></td>
		<th width="12%">DR DATE</th>
		<td><input type="text" name="_do_date" class="reqd" size="15" value="<?php echo date('j-M-Y', strtotime($column['dr_date'])) ?>"></td>
	</tr>
	<tr>
		<th>ISSUED BY</th>
		<td><input type="text" name="_received_by" class="req" size="20" maxlength="32" value="<?php echo $column['dr_received_by'] ?>"></td>
		<th>ISSUED DATE</th>
		<td><input type="text" name="_issued_date" class="fmtd" size="15" value="<?php echo ($column['dr_issued_date']=='')?'':date('j-M-Y', strtotime($column['dr_issued_date'])) ?>"></td>
	</tr>
	<tr>
		<th>REQUEST BY</th>
		<td><input type="text" name="_issued_by" class="fmt" size="20" maxlength="32" value="<?php echo $column['dr_issued_by'] ?>"></td>
		<th>TYPE ITEM</th>
		<td>
			<input type="radio" name="_type_vat" value="1" disabled <?php echo ($column['dr_type_item']==1)?'checked':'' ?>> Vat &nbsp;
			<input type="radio" name="_type_vat" value="2" disabled <?php echo ($column['dr_type_item']==2)?'checked':'' ?>> Non Vat &nbsp;
		</td>
	</tr>
</table>
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<th rowspan="2" width="12%">CUSTOMER</th>
		<th width="10%"><a href="javascript:fillCustomer('customer')">CODE</a></th>
		<td width="20%"><input type="text" name="_cus_to" class="req" size="5" value="<?php echo $column['dr_cus_to'] ?>"></td>
		<th width="12%">NAME</th>
		<td colspan="2"><input type="text" name="_cus_name" class="fmt" style="width:100%" value="<?php echo $column['dr_cus_name'] ?>"></td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="4"><input type="text" name="_cus_address" class="fmt"  style="width:100%" maxlength="255" value="<?php echo $column['dr_cus_address'] ?>"></td>
	</tr>
	<tr>
		<th>SHIP TO</th>
		<th><a href="javascript:fillCustomer('ship')">CODE</a></th>
		<td><input type="text" name="_ship_to" class="req" size="5" value="<?php echo $column['dr_ship_to'] ?>"></td>
		<th>NAME</th>
		<td colspan="2"><input type="text" name="_ship_name" class="fmt" style="width:100%" value="<?php echo $column['dr_ship_name'] ?>"></td>
	</tr>
	<tr>
		<th>RETURN REF.</th>
		<th>CODE</th>
		<td><a href="../billing/revise_return.php?_code=<?php echo $column["dr_turn_code"] ?>" target="_blank"><b><?php echo $column["dr_turn_code"] ?></b></a></td>
		<th>DATE</th>
		<td><?php echo date('d-M-Y',strtotime($column["dr_turn_date"])) ?></td>
		<td align="right"></td>
	</tr>
</table><br />
<strong>[<font color="#446FBE">WAREHOUSE</font>] ITEM LIST</strong> &nbsp; <small class="comment"><i><a href="javascript:fillItem()">( search <img src="../../_images/icon/search_mini.gif"> )</a></i></small>
<table width="100%" class="table_box">
	<thead>
		<tr height="30px">
			<th width="7%">CODE</th>
			<th width="7%">FOR</th>
			<th width="15%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="7%">QTY</th>
			<th width="8%">(x)</th>
			<th width="15%">REMARK</th>
			<th width="5%">DEL</th>
		</tr>
	</thead>
	<tbody id="itemWHPosition">
<?php while($items =& fetchRow($whitem_res)) { ?>
		<tr id="<?php echo trim($items[0]).'-'.trim($items[3])?>">
			<td><?php echo $items[0]?><input type="hidden" name="_wh_it_code[]" value="<?php echo $items[0]?>"></td>
			<td><?php echo $items[3]?><input type="hidden" name="_wh_it_code_for[]" value="<?php echo $items[3]?>"></td>
			<td><?php echo $items[1]?><input type="hidden" name="_wh_it_model_no[]" value="<?php echo $items[1]?>"></td>
			<td><?php echo $items[2]?><input type="hidden" name="_wh_it_desc[]" value="<?php echo $items[2]?>"></td>
			<td align="right"><?php echo number_format((double)$items[4],2)?><input type="hidden" name="_wh_it_qty[]" value="<?php echo $items[4]?>"></td>
			<td align="right"><?php echo $items[5]?><input type="hidden" name="_wh_it_function[]" value="<?php echo $items[5]?>"></td>
			<td><input type="text" name="_wh_it_remark[]" class="fmt" style="wifth:100%" value="<?php echo $items[6]?>"></td>
			<td align="center">
				<a href="javascript:deleteWHItem('<?php echo trim($items[0]).'-'.trim($items[3])?>')"><img src="../../_images/icon/delete.gif" alt="Delete item <?php echo $items[1]?>" width="12px"></a>
			</td>
		</tr>
<?php } ?>
	</tbody>
</table>
<table width="100%" class="table_box">
	<tr>
		<th align="right">TOTAL</th>
		<th width="8%"><input name="totalWhQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="27%">&nbsp;</th>
	</tr>
</table><br />
<strong>[<font color="#446FBE">CUSTOMER</font>] ITEM LIST</strong>
<table width="100%" class="table_box">
	<thead>
		<tr height="30px">
			<th width="5%">CODE</th>
			<th width="13%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="8%">QTY</th>
			<th width="15%">REMARK</th>
			<th width="5%" colspan="3">DEL</th>
		</tr>
	</thead>
	<tbody id="itemCusPosition">
<?php while($items =& fetchRow($cusitem_res)) { ?>
		<tr id="<?php echo trim($items[0])?>">
			<td><?php echo $items[0]?><input type="hidden" name="_cus_it_code[]" value="<?php echo $items[0]?>"></td>
			<td><?php echo $items[1]?><input type="hidden" name="_cus_it_model_no[]" value="<?php echo $items[1]?>"></td>
			<td><?php echo $items[2]?><input type="hidden" name="_cus_it_desc[]" value="<?php echo $items[2]?>"></td>
			<td><input type="text" class="reqn" name="_cus_it_qty[]" value="<?php echo number_format((double)$items[3]) ?>" style="width:100%" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')"></td>
			<!-- <td align="right"><?php echo number_format((double)$items[3]) ?><input type="hidden" name="_cus_it_qty[]"value="<?php echo number_format((double)$items[3])?>"></td>-->
			<td><input type="text" name="_cus_it_remark[]" class="fmt" style="width:100%" value="<?php echo $items[4]?>"></td>
			<td align="center" colspan="3"><a href="javascript:deleteCusItem('<?php echo trim($items[0])?>')"><img src="../../_images/icon/delete.gif" alt="Delete item <?php echo $items[1]?>" width="12px"></a></td>
		</tr>
<?php } ?>
	</tbody>
</table>
<table width="100%" class="table_box">
	<tr>
		<th align="right">TOTAL</th>
		<th width="8%"><input name="totalCusQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="20%">&nbsp;</th>
	</tr>
</table><br />
<strong class="info">CONDITION</strong>
<table class="table_box" width="100%">
	<tr>
		<th width="12%">DELIVERY</th>
		<td>1.<input type="text" name="_delivery_warehouse" class="fmt" size="2" maxlength="2" value="<?php echo $column['dr_delivery_warehouse'] ?>">ex W/house(P/C/D)</td>
		<td>2.<input type="text" name="_delivery_franco" class="fmt" size="2" maxlength="2" value="<?php echo $column['dr_delivery_franco'] ?>">Franco(P/D)</td>
		<td>by <input type="text" name="_delivery_by" size="6" class="fmt" value="<?php echo $column['dr_delivery_by'] ?>"></td>
		<td>Freight charge : Rp <input type="text" name="_delivery_freight_charge" class="fmtn" onKeyUp="formatNumber(this,'dot')" size="8" onBlur="updateAmount()" value="<?php echo number_format((double)$column['dr_delivery_freight_charge']) ?>"></td>
	</tr>
	<tr>
		<th>REMARK</th>
		<td colspan="4"><textarea name="_remark" style="width:100%" rows="4"><?php echo $column['dr_remark'] ?></textarea></td>
	</tr>
</table>
<input type='hidden' name='_ordered_by' value="<?php echo $column["dr_ordered_by"] ?>">
<?php
//[WAREHOUSE] outgoing item
$outitem_sql = "
SELECT trim(it_code), otst_qty
FROM
  ".ZKP_SQL."_tb_outgoing_v2
  JOIN ".ZKP_SQL."_tb_outgoing_stock_v2 USING(out_idx)
WHERE out_doc_ref = '".trim($_code)."'
ORDER BY it_code";
$outitem_res =& query($outitem_sql);

while($items =& fetchRow($outitem_res)) {
	$out_item[0][] = $items[0];
	$out_item[1][] = $items[1];
	echo "<input type=\"hidden\" name=\"_out_it_code[]\" value=\"". $items[0]. "\">";
	echo "<input type=\"hidden\" name=\"_out_it_qty[]\" value=\"". $items[1]. "\">\n";
}
?>
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td align="right">
			<button name='btnUpdate' class='input_btn' style='width:80px;'><img src="../../_images/icon/update.gif" width="20px" align="middle" alt="Update DO"> &nbsp; Update</button>&nbsp;
			<button name='btnCancel' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Back to Detail</button>
		</td>
	</tr>
</table><br /><br />
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmInsert;

	window.document.all.btnUpdate.onclick = function() {
		if (window.itemWHPosition.rows.length <= 0) {
			alert("You need to choose at least 1 item");
			return;
		}

		if (window.itemCusPosition.rows.length <= 0) {
			alert("You need to choose at least 1 item");
			return;
		}

		if(confirm("Are you sure to update?")) {
			if(verify(oForm)){
				oForm.p_mode.value = 'update_dr_revised';
				oForm.submit();
			}
		}
	}

	window.document.all.btnCancel.onclick = function() {
		window.location.href = 'revise_dr.php?_code=<?php echo $_code ?>';
	}
</script>
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