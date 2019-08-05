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
$left_loc 	   = "list_service.php";
if (!isset($_GET['_code']) || $_GET['_code'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else {
	$_code = urldecode($_GET['_code']);
}

//---------------------------------------------------------------------------------------------------- delete
if (ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'delete')) {

	$_rev = (int) $_POST['_revision_time'];
	$_service_date = date("Ym", strtotime($_POST['_service_date']));

	$result =& query("DELETE FROM ".ZKP_SQL."_tb_service WHERE sv_code = '$_code'");

	if(isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_service.php?_code=$_code");
	} else {
		for ($i = $_rev ; $i >=0 ; $i--) {
			@unlink(APP_DIR . "_user_data/billing/service/{$_service_date}/{$_code}_rev_{$i}.pdf");
		}
	}
	goPage(HTTP_DIR . "$currentDept/$moduleDept/list_service.php");
}

//---------------------------------------------------------------------------------------------------- update
if(ckperm(ZKP_UPDATE,  HTTP_DIR . "$currentDept/$moduleDept/index.php", 'update')) {

	$_code				= $_POST['_code'];
	$_reg_no			= $_POST['_reg_no'];
	$_service_date		= $_POST['_service_date'];
	$_received_by		= $_POST['_received_by'];
	$_cus_to			= $_POST['_cus_to'];
	$_cus_name			= $_POST['_cus_name'];
	$_cus_address		= $_POST['_cus_address'];
	$_is_guarantee		= $_POST['_is_guarantee'];
	$_guarantee_period	= empty($_POST['_guarantee_period']) ? "" : $_POST['_guarantee_period'];
	$_signature_by		= $_POST['_signature_by'];
	$_days_to_due		= $_POST['_days_to_due'];
	$_due_date			= $_POST['_due_date'];
	$_remark			= $_POST['_remark'];
	$_total_disc		= $_POST['totalDisc'];
	$_total_amount		= $_POST['totalAmount'];
	$_revision_time		= (int) $_POST['_revision_time'];
	$_lastupdated_by_account = $S->getValue("ma_account");

	$_due_date_chk	= 0;
	if(isset($_POST['_due_date_chk'])) {
		foreach($_POST['_due_date_chk'] as $val) $_due_date_chk = $_due_date_chk + $val;
	}

	//Item 
	foreach($_POST['_it_code'] as $val)					$_it_code[]				= $val;
	foreach($_POST['_it_model_no'] as $val)				$_it_model_no[]			= $val;
	foreach($_POST['_it_sn'] as $val)					$_it_sn[] 				= $val;
	foreach($_POST['_it_repair_desc'] as $val)			$_it_repair_desc[]		= $val;
	foreach($_POST['_it_repair_qty'] as $val)			$_it_repair_qty[]		= $val;
	foreach($_POST['_it_repair_price'] as $val)			$_it_repair_price[]		= $val;
	foreach($_POST['_it_repair_remark'] as $val)		$_it_repair_remark[]	= $val;

	$_it_code				= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_it_model_no			= '$$' . implode('$$,$$', $_it_model_no) . '$$';
	$_it_sn					= '$$' . implode('$$,$$', $_it_sn) . '$$';
	$_it_repair_desc		= '$$' . implode('$$,$$', $_it_repair_desc) . '$$';
	$_it_repair_qty			= implode(',', $_it_repair_qty);
	$_it_repair_price		= implode(',', $_it_repair_price);
	$_it_repair_remark		= '$$' . implode('$$,$$', $_it_repair_remark) . '$$';

	if(isset($_POST['_it_replace_part_name'])) {
		foreach($_POST['_it_replace_part_name'] as $val)	$_it_replace_part_name[]= $val;
		foreach($_POST['_it_replace_qty'] as $val)			$_it_replace_qty[]		= $val;
		foreach($_POST['_it_replace_price'] as $val)		$_it_replace_price[]	= $val;
		foreach($_POST['_it_replace_remark'] as $val)		$_it_replace_remark[]	= $val;

		$_it_replace_part_name	= '$$' . implode('$$,$$', $_it_replace_part_name) . '$$';
		$_it_replace_qty		= implode(',', $_it_replace_qty);
		$_it_replace_price		= implode(',', $_it_replace_price);
		$_it_replace_remark		= '$$' . implode('$$,$$', $_it_replace_remark) . '$$';
	} else {
		$_it_replace_part_name	= '$$$$';
		$_it_replace_qty		= '0';
		$_it_replace_price		= '0';
		$_it_replace_remark		= '$$$$';
	}

	$result = executeSP(
		ZKP_SQL."_updateService",
		"$\${$_code}$\$",
		"$\${$_reg_no}$\$",
		"$\${$_service_date}$\$",
		"$\${$_received_by}$\$",
		"$\${$_cus_to}$\$",
		"$\${$_cus_name}$\$",
		"$\${$_cus_address}$\$",
		"$\${$_is_guarantee}$\$",
		"$\${$_guarantee_period}$\$",
		"$\${$_signature_by}$\$",
		$_due_date_chk,
		$_days_to_due,
		"$\${$_due_date}$\$",
		"$\${$_remark}$\$",
		$_total_disc,
		$_total_amount,
		"$\${$_lastupdated_by_account}$\$",
		"ARRAY[$_it_code]",
		"ARRAY[$_it_model_no]",
		"ARRAY[$_it_sn]",
		"ARRAY[$_it_repair_desc]",
		"ARRAY[$_it_repair_qty]",
		"ARRAY[$_it_repair_price]",
		"ARRAY[$_it_repair_remark]",
		"ARRAY[$_it_replace_part_name]",
		"ARRAY[$_it_replace_qty]",
		"ARRAY[$_it_replace_price]",
		"ARRAY[$_it_replace_remark]"
	);

	if(isZKError($result)) {
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/revise_service.php?_code=$_code");
	}
	//SAVE PDF FILE
	include "./pdf/generate_service_pdf.php";
	goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_service.php?_code=$_code");
}

//---------------------------------------------------------------------------------------------- insert payment
if (ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'add_payment')) {

	$_code			= $_POST['_code'];
	$_cus_code		= strtoupper($_POST['_cus_code']);
	$_payment_date	= $_POST['_payment_date'];
	$_payment_paid	= $_POST['_payment_paid'];
	$_payment_remark= $_POST['_payment_remark'];
	$_method		= $_POST['_method'];
	$_bank			= isset($_POST['_bank']) ? $_POST['_bank'] : '';
	$_inputed_by	= $S->getValue('ma_account');

	$result = executeSP(
		ZKP_SQL."_addNewServicePayment",
		"$\${$_code}$\$",
		"$\${$_cus_code}$\$",
		"$\${$_payment_date}$\$",
		$_payment_paid,
		"$\${$_payment_remark}$\$",
		"$\${$_inputed_by}$\$",
		"$\${$_method}$\$",
		"$\${$_bank}$\$"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_service.php?_code=$_code");
	}
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_service.php?_code=$_code");
}

//----------------------------------------------------------------------------------------- delete payment
if (ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'delete_payment')) {
	$_pay_idx	= $_POST['_pay_idx'];
	$result 	= query("DELETE FROM ".ZKP_SQL."_tb_service_payment WHERE svpay_idx=$_pay_idx");

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_service.php?_code=$_code");
	}
	goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_service.php?_code=$_code");
}

//========================================================================================= DEFAULT PROCESS
//service
$sql = "SELECT * FROM ".ZKP_SQL."_tb_service WHERE sv_code = '$_code'";
$result = query($sql);
$column = fetchRowAssoc($result);

if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
}

$model_sql		= "SELECT * FROM ".ZKP_SQL."_tb_service_item WHERE sv_code = '$_code'";
$repair_sql		= "SELECT * FROM ".ZKP_SQL."_tb_service_repair WHERE sv_code = '$_code'";
$replace_sql	= "SELECT * FROM ".ZKP_SQL."_tb_service_replace WHERE sv_code = '$_code'";
$pay_sql		= "SELECT * FROM ".ZKP_SQL."_tb_service_payment WHERE sv_code = '$_code'";

$model_res		= query($model_sql);
$repair_res		= query($repair_sql);
$replace_res	= query($replace_sql);
$pay_res		= query($pay_sql);
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
var wSearchItem;
function fillItem(){
	var x = (screen.availWidth - 580) / 2;
	var y = (screen.availHeight - 620) / 2;

	wSearchItem = window.open("./p_list_item.php",'wSearchItem',
		'scrollbars,width=550,height=640,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wSearchItem.focus();
}

function createItem() {

	var f2 = wSearchItem.document.frmCreateItem;
	var oTR = window.document.createElement("TR");

	var oTD = new Array();
	var oTextbox = new Array();

	for (var i=0; i<4; i++) {
		oTD[i] = window.document.createElement("TD");
		oTextbox[i] = window.document.createElement("INPUT");
		oTextbox[i].type = "text";

		switch (i) {
			case 0: // ITEM CODE
				oTD[i].innerText		= f2.elements[2].value;
				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_it_code[]";
				oTextbox[i].value		= f2.elements[2].value;
				break;

			case 1: // MODEL NO
				oTextbox[i].style.width = "100%";
				oTextbox[i].className	= "req";
				oTextbox[i].name		= "_it_model_no[]";
				oTextbox[i].value		= f2.elements[3].value;
				break;

			case 2: // SERIAL NUMBER
				oTextbox[i].style.width = "100%";
				oTextbox[i].className	= "req";
				oTextbox[i].name		= "_it_sn[]";
				oTextbox[i].value		= f2.elements[4].value;
				break;

			case 3: // DELETE
				oTD[i].innerHTML		= "<a href=\"javascript:deleteItem('" + f2.elements[3].value+'||'+f2.elements[4].value + "',0)\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD[i].align			= "center";
				break;
		}
		if (i<3) oTD[i].appendChild(oTextbox[i]);
		oTR.id = f2.elements[3].value+'||'+f2.elements[4].value;
		oTR.appendChild(oTD[i]);
	}

	window.itemPosition.appendChild(oTR);
	for (var i=3; i<5; i++) {f2.elements[i].value = '';}
	updateAmount();
}

function checkform(print_position) {
	var f = window.document.frmInsert;

	//check form
	if (print_position == 1) {
		if(f._repair_desc.value.length <= 0) {
			alert("Please insert the description");
			f._repair_desc.focus();
			return;
		} else if (f._repair_qty.value.length <= 0) {
			alert("Please fill the qty");
			f._repair_qty.focus();
			return;
		} else if (f._repair_price.value.length <= 0) {
			alert("Please fill the unit price");
			f._repair_price.focus();
			return;
		}
	} else if (print_position == 2) {
		if(f._replace_part_name.value.length <= 0) {
			alert("Please insert the part name");
			f._replace_part_name.focus();
			return;
		} else if (f._replace_qty.value.length <= 0) {
			alert("Please fill the qty");
			f._replace_qty.focus();
			return;
		} else if (f._replace_price.value.length <= 0) {
			alert("Please fill the unit price");
			f._replace_price.focus();
			return;
		}
	}
	printRow(print_position);
}

function printRow(print_position) {
	var f	= window.document.frmInsert;
	var oTR = window.document.createElement("TR");
	var oTD = new Array();
	var oTextbox = new Array();

	if (print_position == 1) {
		for (var i=0; i<6; i++) {
			oTD[i] = window.document.createElement("TD");
			oTextbox[i] = window.document.createElement("INPUT");
			oTextbox[i].type = "text";
	
			switch (i) {
				case 0: // _repair_desc
					oTextbox[i].style.width		= "100%";
					oTextbox[i].className		= "req";
					oTextbox[i].name			= "_it_repair_desc[]";
					oTextbox[i].value			= f._repair_desc.value;
					break;
	
				case 1: // _repair_qty
					oTextbox[i].style.width		= "100%";
					oTextbox[i].className		= "reqn";
					oTextbox[i].name			= "_it_repair_qty[]";
					oTextbox[i].value			= f._repair_qty.value;
					oTextbox[i].onblur			= function() {updateAmount();}
					oTextbox[i].onkeyup			= function() {formatNumber(this, 'dot');}
					break;
		
				case 2: // _repair_price
					oTextbox[i].style.width		= "100%";
					oTextbox[i].className		= "reqn";
					oTextbox[i].name			= "_it_repair_price[]";
					oTextbox[i].value			= f._repair_price.value;
					oTextbox[i].onblur			= function() {updateAmount();}
					oTextbox[i].onkeyup			= function() {formatNumber(this, 'dot');}
					break;

				case 3: // _repair_amount
					var amount = parseFloat(parseInt(removecomma(f._repair_qty.value))) * parseFloat(parseInt(removecomma(f._repair_price.value)));

					oTextbox[i].style.width		= "100%";
					oTextbox[i].className		= "reqn";
					oTextbox[i].readOnly		= "readonly";
					oTextbox[i].name			= "_it_repair_amount[]";
					oTextbox[i].value			= numFormatval(amount+'',0);					
					break;

				case 4: // _repair_remark
					oTextbox[i].style.width		= "100%";
					oTextbox[i].className		= "fmt";
					oTextbox[i].name			= "_it_repair_remark[]";
					oTextbox[i].value			= f._repair_remark.value;					
					break;

				case 5: // [del]
					oTD[i].align		= "center";
					oTD[i].innerHTML	= "<a href=\"javascript:deleteItem('" + f._repair_desc.value + "',1)\"><img src='../../_images/icon/delete.gif' width='15px'></a>";
					break;
			}
			if (i<5) oTD[i].appendChild(oTextbox[i]);
			oTR.id = f._repair_desc.value;
			oTR.appendChild(oTD[i]);
		}
		window.repairPosition.appendChild(oTR);
		f._repair_desc.value= '';f._repair_qty.value= '0';f._repair_price.value= '0';f._repair_remark.value= '';f._repair_desc.focus();
	} else if (print_position == 2) {
		for (var i=0; i<6; i++) {
			oTD[i] = window.document.createElement("TD");
			oTextbox[i] = window.document.createElement("INPUT");
			oTextbox[i].type = "text";
	
			switch (i) {
				case 0: // _replace_part_name
					oTextbox[i].style.width		= "100%";
					oTextbox[i].className		= "req";
					oTextbox[i].name			= "_it_replace_part_name[]";
					oTextbox[i].value			= f._replace_part_name.value;
					break;
	
				case 1: // _replace_qty
					oTextbox[i].style.width		= "100%";
					oTextbox[i].className		= "reqn";
					oTextbox[i].name			= "_it_replace_qty[]";
					oTextbox[i].value			= f._replace_qty.value;
					oTextbox[i].onblur			= function() {updateAmount();}
					oTextbox[i].onkeyup			= function() {formatNumber(this, 'dot');}
					break;
		
				case 2: // _replace_price
					oTextbox[i].style.width		= "100%";
					oTextbox[i].className		= "reqn";
					oTextbox[i].name			= "_it_replace_price[]";
					oTextbox[i].value			= f._replace_price.value;
					oTextbox[i].onblur			= function() {updateAmount();}
					oTextbox[i].onkeyup			= function() {formatNumber(this, 'dot');}
					break;

				case 3: // _replace_amount
					var amount = parseFloat(parseInt(removecomma(f._replace_qty.value))) * parseFloat(parseInt(removecomma(f._replace_price.value)));

					oTextbox[i].style.width		= "100%";
					oTextbox[i].className		= "reqn";
					oTextbox[i].readOnly		= "readonly";
					oTextbox[i].name			= "_it_replace_amount[]";
					oTextbox[i].value			= numFormatval(amount+'',0);					
					break;

				case 4: // _replace_remark
					oTextbox[i].style.width		= "100%";
					oTextbox[i].className		= "fmt";
					oTextbox[i].name			= "_it_replace_remark[]";
					oTextbox[i].value			= f._replace_remark.value;					
					break;

				case 5: // [del]
					oTD[i].align		= "center";
					oTD[i].innerHTML	= "<a href=\"javascript:deleteItem('" + f._replace_part_name.value + "',2)\"><img src='../../_images/icon/delete.gif' width='15px'></a>";
					break;
			}
			if (i<5) oTD[i].appendChild(oTextbox[i]);
			oTR.id = f._replace_part_name.value;
			oTR.appendChild(oTD[i]);
		}
		window.replacePosition.appendChild(oTR);
		f._replace_part_name.value= '';f._replace_qty.value= '0';f._replace_price.value= '0';f._replace_remark.value= '';f._replace_part_name.focus();
	}
	updateAmount();
}

function deleteItem(id, print_position) {
	if(print_position == 0) {
		var count = window.itemPosition.rows.length;
		for (var i=0; i<count; i++) {
			var oRow = window.itemPosition.rows(i);
			if (oRow.id == id) {
				var n = window.itemPosition.removeChild(oRow);
				count = count - 1;
				break;
			}
		}
	} else if(print_position == 1) {
		var count = window.repairPosition.rows.length;
		for (var i=0; i<count; i++) {
			var oRow = window.repairPosition.rows(i);
			if (oRow.id == id) {
				var n = window.repairPosition.removeChild(oRow);
				count = count - 1;
				break;
			}
		}
	} else if(print_position == 2) {
		var count = window.replacePosition.rows.length;
		for (var i=0; i<count; i++) {
			var oRow = window.replacePosition.rows(i);
			if (oRow.id == id) {
				var n = window.replacePosition.removeChild(oRow);
				count = count - 1;
				break;
			}
		}
	}
	updateAmount();
}

function fillCustomer(target) {

	keyword = window.document.frmInsert._cus_to.value;

	var x = (screen.availWidth - 400) / 2;
	var y = (screen.availHeight - 600) / 2;
	var win = window.open(
		'./p_list_cus_code.php?_check_code='+ keyword, '',
		'scrollbars,width=400,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

function enabledText() {
	var f	= window.document.frmInsert;

	if(f._is_guarantee[0].checked) {
		f._guarantee_period.disabled	= false;
		f._guarantee_period.className	= "reqd";
		f._guarantee_period.readOnly	= false;
	} else if(f._is_guarantee[1].checked) {
		f._guarantee_period.disabled	= true;
		f._guarantee_period.className	= "fmt";
		f._guarantee_period.readOnly	= "readonly";
		f._guarantee_period.value		= "";
	}
}

function setDueDate(value,idx) {
	var f		 = window.document.frmInsert;
	var due_date = parseDate(f._service_date.value, 'prefer_euro_format');

	if(idx == 1) {
		if(f._days_to_due.value.length == 0) {
			var add_days = 0;
		} else {
			var add_days = parseInt(f._days_to_due.value);
		}
		due_date.setDate(due_date.getDate()+add_days);
	} else if(idx == 2) {
		if(value) {due_date.setDate(due_date.getDate());}
		else{f._due_date.value = '';return;}
	}

	f._due_date.value = formatDate(due_date, 'd-NNN-yyyy');
}

function updateAmount() {
	var f	= window.document.frmInsert;
	var e 	= window.document.frmInsert.elements;
	var countItem		= window.itemPosition.rows.length;
	var countRepair		= window.repairPosition.rows.length;
	var countReplace	= window.replacePosition.rows.length;
	var numInputModel	= 3;
	var numInput		= 5;

	var idx_item	= 13;		/////
	var idx_qty1	= idx_item+(numInputModel*countItem)+6;
	var idx_price1	= idx_item+(numInputModel*countItem)+7;
	var idx_amount1	= idx_item+(numInputModel*countItem)+8;
/*
alert(
e(idx_item).value +'\n'+
idx_qty1+'. '+e(idx_item).value +'\n'+
idx_price1+'. '+e(idx_price1).value +'\n'+
idx_amount1+'. '+e(idx_amount1).value +'\n'
);
*/
	var idx_qty2	= idx_item+(numInputModel*countItem)+(numInput*countRepair)+5+6;
	var idx_price2	= idx_item+(numInputModel*countItem)+(numInput*countRepair)+5+7;
	var idx_amount2	= idx_item+(numInputModel*countItem)+(numInput*countRepair)+5+8;

	f._repair_amount.value	= numFormatval(parseFloat(parseInt(removecomma(f._repair_qty.value))) * parseFloat(parseInt(removecomma(f._repair_price.value)))+'',0);;
	f._replace_amount.value	= numFormatval(parseFloat(parseInt(removecomma(f._replace_qty.value))) * parseFloat(parseInt(removecomma(f._replace_price.value)))+'',0);;

	var sumOfNet	= 0;
	var sumOfTotal	= 0;

	for (var i=0; i<countRepair; i++) {
		var price  = parseFloat(removecomma(e(idx_price1+i*numInput).value));
		var qty	   = parseFloat(removecomma(e(idx_qty1+i*numInput).value));
		var amount = price*qty;

		e(idx_amount1+i*numInput).value = numFormatval((amount)+'',0);
		sumOfNet	+= amount;
	}

	for (var i=0; i<countReplace; i++) {
		var price  = parseFloat(removecomma(e(idx_price2+i*numInput).value));
		var qty	   = parseFloat(removecomma(e(idx_qty2+i*numInput).value));
		var amount = price*qty;

		e(idx_amount2+i*numInput).value = numFormatval((amount)+'',0);
		sumOfNet	+= amount;
	}

	var disc = parseFloat(removecomma(f.totalDisc.value));

	f.totalItem.value	= numFormatval(countItem + '', 0);
	f.totalNet.value	= numFormatval(sumOfNet + '', 0);
	f.totalAmount.value	= numFormatval(sumOfNet-disc + '', 0);
}

function initPage() {
	enabledText();
	updateAmount();
	defaultPaymentConfirm();
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
<h3>[<font color="#446fbe">CUSTOMER SERVICE</font>] REVISE BILLING</h3>
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode'>
<input type='hidden' name='_code' value="<?php echo $column["sv_code"] ?>">
<input type='hidden' name='_revision_time' value="<?php echo $column["sv_revesion_time"] ?>">
<table width="100%" class="table_box" cellpadding="1" cellspacing="1">
	<tr>
		<td colspan="3"><span class="bar_bl">SERVICE INFORMATION</span></td>
		<td colspan="3" align="right">
			<I>Last updated by : <?php echo ucfirst($column['sv_lastupdated_by_account']).date(', j-M-Y g:i:s', strtotime($column['sv_lastupdated_timestamp']))?></I>
		</td>
	</tr>
	<tr>
		<th width="12%">SERVICE NO</th>
		<td width="25%"><span class="bar_bl"><?php echo $column["sv_code"] ?></span></td>
		<th width="12%">SERVICE DATE</th>
		<td><input type="text" name="_service_date" class="reqd" size="15" value="<?php echo date('d-M-Y', strtotime($column["sv_date"])) ?>"></td>
		<th width="12%">RECEIVED BY</th>
		<td width="20%"><input type="text" name="_received_by" class="req" size="15" value="<?php echo $column["sv_received_by"] ?>"></td>
	</tr>
	<tr>
		<th>REG NO</th>
		<td><input type="text" name="_reg_no" class="fmt" size="15" value="<?php echo $column["sv_reg_no"] ?>" readOnly> &nbsp; <a target="_blank" href="javascript:window.location.href='../registration/revise_registration.php?_code=<?php echo $column["sv_reg_no"] ?>'"><img src="../../_images/icon/list_mini.gif"></a></td>
		<th>GUARANTEE</th>
		<td colspan="4">
			<input type="radio" name="_is_guarantee" value="true" id="true" onclick="enabledText()" <?php echo ($column["sv_is_guarantee"]=='t') ? 'checked':'' ?>><label for="true">Yes, until : &nbsp;</label><input type="text" name="_guarantee_period" class="reqd" size="15" value="<?php echo ($column["sv_guarantee_period"]!='') ? date('d-M-Y', strtotime($column["sv_guarantee_period"])) : '' ?>"> &nbsp;
			<input type="radio" name="_is_guarantee" value="false" id="false" onclick="enabledText()" <?php echo ($column["sv_is_guarantee"]=='f') ? 'checked':'' ?>><label for="false">Expired</label>
		</td>
</table>
<table width="100%" class="table_box" cellpadding="1" cellspacing="1">
	<tr>
		<th width="12%" rowspan="2">CUSTOMER</th>
		<th width="10%"><a href="javascript:fillCustomer('customer')" accesskey="c"><u>C</u>ODE</a></th>
		<td width="15%"><input type="text" name="_cus_to" class="req" size="10" maxlength="7" value="<?php echo $column["sv_cus_to"] ?>"></td>
		<th width="12%">NAME</th>
		<td><input type="text" name="_cus_name" class="fmt" style="width:100%" maxlength="128" value="<?php echo $column["sv_cus_to_name"] ?>"></td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="3"><input type="text" name="_cus_address" class="fmt" style="width:100%" maxlength="128"  value="<?php echo $column["sv_cus_to_address"] ?>"></td>
	</tr>
</table><br />
<table width="80%" class="table_box">
	<tr><td>
		<span class="bar_bl">ITEM LIST</span> &nbsp; <small class="comment"><i><a href="javascript:fillItem()">( search <img src="../../_images/icon/search_mini.gif"> )</a></i></small>
		<table width="70%" class="table_box" cellspacing="1">
			<thead>
				<tr height="25px">
					<th width="15%">CODE</th>
					<th>MODEL NO</th>
					<th width="40%">SERIAL NUMBER</th>
					<th width="8%"></th>
				</tr>
			</thead>
			<tbody id="itemPosition">
<?php while($items =& fetchRow($model_res)) { ?>
	<tr id="<?php echo trim($items[3]).'||'.trim($items[4])?>">
		<td><input type="hidden" name="_it_code[]" value="<?php echo $items[1]?>"><?php echo $items[1]?></td>
		<td><input type="text" name="_it_model_no[]" class="req" style="width:100%" value="<?php echo $items[3]?>"></td>
		<td><input type="text" name="_it_sn[]" class="req" style="width:100%" value="<?php echo $items[4]?>"></td>
		<td align="center"><a href="javascript:deleteItem(<?php echo "'".trim($items[3]).'||'.trim($items[4])."'"?>,0)"><img src="../../_images/icon/delete.gif" width="15px"></a></td>
	</tr>
<?php } ?>
			</tbody>
		</table>
		<table width="70%" class="table_box" cellspacing="1">
			<tr>
				<th align="right">TOTAL ITEM(S) &nbsp; <input type="text" name="totalItem" class="fmtn" size="5"></th>
				<th width="8%"></th>
			</tr>
		</table><br />
	</td></tr>
	<tr><td>
		<span class="bar_bl">DETAIL OF REPAIRS</span>
		<table width="100%" class="table_box" cellspacing="1">
			<thead>
				<tr height="25px">
					<th>DESCRIPTION</th>
					<th width="5%">QTY</th>
					<th width="12%">UNIT PRICE<br />(Rp)</th>
					<th width="15%">AMOUNT<br />(Rp)</th>
					<th width="20%">REMARKS</th>
					<th width="3%"></th>
				</tr>
				<tr>
					<td><input type="text" name="_repair_desc" class="fmt" style="width:100%" onKeyPress="if(window.event.keyCode==13) {checkform(1)}"></td>
					<td><input type="text" name="_repair_qty" class="fmtn" style="width:100%" value="1" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')" onKeyPress="if(window.event.keyCode==13) {checkform(1)}"></td>
					<td><input type="text" name="_repair_price" class="fmtn" style="width:100%" value="0" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')" onKeyPress="if(window.event.keyCode==13) {checkform(1)}"></td>
					<td><input type="text" name="_repair_amount" class="fmtn" style="width:100%" readonly></td>
					<td><input type="text" name="_repair_remark" class="fmt" style="width:100%" onKeyPress="if(window.event.keyCode==13) {checkform(1)}"></td>
					<td><a href="javascript:checkform(1)"><img src="../../_images/icon/add.png" align="middle" alt="Add row"></a></td>
				</tr>
			</thead>
			<tbody id="repairPosition">
			<?php while($items =& fetchRow($repair_res)) { ?>
				<tr id="<?php echo $items[2] ?>">
					<td><input type="text" name="_it_repair_desc[]" class="req" style="width:100%" value="<?php echo $items[2] ?>"></td>
					<td><input type="text" name="_it_repair_qty[]" class="reqn" style="width:100%" value="<?php echo number_format($items[3]) ?>" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')"></td>
					<td><input type="text" name="_it_repair_price[]" class="reqn" style="width:100%" value="<?php echo number_format($items[4]) ?>" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')"></td>
					<td><input type="text" name="_it_repair_amount[]" class="reqn" style="width:100%" value="<?php echo number_format($items[3]*$items[4]) ?>" readonly></td>
					<td><input type="text" name="_it_repair_remark[]" class="fmt" style="width:100%" value="<?php echo $items[5] ?>"></td>
					<td align="center"><a href="javascript:deleteItem('<?php echo $items[2] ?>',1)"><img src="../../_images/icon/delete.gif" width='15px'></a></td>
				</tr>
			<?php } ?>
			</tbody>
		</table><br />
		<span class="bar_bl">PART REPLACED</span>
		<table width="100%" class="table_box" cellspacing="1">
			<thead>
				<tr height="25px">
					<th>PART NAME</th>
					<th width="5%">QTY</th>
					<th width="12%">UNIT PRICE<br />(Rp)</th>
					<th width="15%">AMOUNT<br />(Rp)</th>
					<th width="20%">REMARKS</th>
					<th width="3%"></th>
				</tr>
				<tr>
					<td><input type="text" name="_replace_part_name" class="fmt" style="width:100%" onKeyPress="if(window.event.keyCode==13) {checkform(2)}"></td>
					<td><input type="text" name="_replace_qty" class="fmtn" style="width:100%" value="1" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')" onKeyPress="if(window.event.keyCode==13) {checkform(2)}"></td>
					<td><input type="text" name="_replace_price" class="fmtn" style="width:100%" value="0" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')" onKeyPress="if(window.event.keyCode==13) {checkform(2)}"></td>
					<td><input type="text" name="_replace_amount" class="fmtn" style="width:100%" readonly></td>
					<td><input type="text" name="_replace_remark" class="fmt" style="width:100%" onKeyPress="if(window.event.keyCode==13) {checkform(2)}"></td>
					<td><a href="javascript:checkform(2)"><img src="../../_images/icon/add.png" align="middle" alt="Add row"></a></td>
				</tr>
			</thead>
			<tbody id="replacePosition">
			<?php while($items =& fetchRow($replace_res)) { ?>
				<tr id="<?php echo $items[2] ?>">
					<td><input type="text" name="_it_replace_part_name[]" class="req" style="width:100%" value="<?php echo $items[2] ?>"></td>
					<td><input type="text" name="_it_replace_qty[]" class="reqn" style="width:100%" value="<?php echo number_format($items[3]) ?>" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')"></td>
					<td><input type="text" name="_it_replace_price[]" class="reqn" style="width:100%" value="<?php echo number_format($items[4]) ?>" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')"></td>
					<td><input type="text" name="_it_replace_amount[]" class="reqn" style="width:100%" value="<?php echo number_format($items[3]*$items[4]) ?>" readonly></td>
					<td><input type="text" name="_it_replace_remark[]" class="fmt" style="width:100%" value="<?php echo $items[5] ?>"></td>
					<td align="center"><a href="javascript:deleteItem('<?php echo $items[2] ?>',2)"><img src="../../_images/icon/delete.gif" width='15px'></a></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</td></tr>
	<tr><td>
	<table width="100%" class="table_box">
		<tr>
			<th align="right">NET TOTAL</th>
			<th width="15%"><input type="text" name="totalNet" class="reqn" style="width:100%" readonly></th>
			<th width="23%"></th>
		</tr>
		<tr>
			<th align="right">DISCOUNT</th>
			<th><input type="text" name="totalDisc" class="reqn" style="width:100%;color:red" value="<?php echo number_format($column["sv_total_discount"]) ?>" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')"></th>
			<th></th>
		</tr>
		<tr>
			<th align="right">TOTAL</th>
			<th><input type="text" name="totalAmount" class="reqn" style="width:100%" readonly></th>
			<th></th>
		</tr>
	</table>
	</td></tr>
</table><br /><br />
<span class="bar_bl">OTHER INFORMATION</span>
<table width="100%" class="table_box" cellpadding="1">
	<tr>
		<th width="12%">DUE DATE</th>
		<td width="50%">
			<input type="checkbox" name="_due_date_chk[]" value="1"<?php echo ($column['sv_due_date_chk'] & 1)? " checked":""?>> <input type="text" name="_days_to_due" class="fmtn" style="width:25px" onblur="setDueDate(this.checked,1)" value="<?php echo $column["sv_days_to_due"] ?>"> days after 
			<select name="_date_condition" class="fmt">
				<option value="Invoice">INVOICE</option>
			</select>
			<input type="checkbox" name="_due_date_chk[]" value="2"<?php echo ($column['sv_due_date_chk'] & 2)? " checked":""?> onclick="setDueDate(this.checked,2)"> COD &nbsp; &nbsp;
			Due : <input type="text" name="_due_date" class="reqd" size="10" value="<?php echo date('d-M-Y',strtotime($column["sv_due_date"])) ?>">
		</td>
		<th width="12%">SIGN BY</th>
		<td><input type="text" name="_signature_by" class="req" size="15" value="<?php echo $column["sv_signature_by"] ?>"></td>
	</tr>
	<tr>
		<th>REMARK</th>
		<td colspan="3"><textarea name="_remark" style="width:100%" rows="4"><?php echo $column["sv_remark"] ?></textarea></td>
	</tr>
</table>
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td>
			<button name='btnDelete' class='input_red' style='width:120px;'><img src="../../_images/icon/trash.gif" width="15px" align="middle" alt="Delete service"> &nbsp; Delete service</button>
		</td>
		<td align="right">
			Rev No:
			<select name="_revision_time" id="_revision_time">
			<?php
				for($counter = $column['sv_revesion_time']; $counter >= 0; $counter--) {
					echo "\t\t\t<option value=\"$counter\">$counter</option>\n";
				}
			?>
			</select>&nbsp;
			<button name='btnPrint' class='input_btn' style='width:100px;'><img src="../../_images/icon/print.gif" width="18px" align="middle" alt="Print pdf"> &nbsp; Print PDF</button>&nbsp;
			<button name='btnUpdate' class='input_btn' style='width:80px;'><img src="../../_images/icon/update.gif" width="20px" align="middle" alt="Update service"> &nbsp; Update</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
		</td>
	</tr>
</table><br /><br />
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmInsert;

	window.document.all.btnDelete.onclick = function() {
		if(confirm("Are you sure to delete service?")) {
			oForm.p_mode.value = 'delete';
			oForm.submit();
		}
	}
	
	window.document.all.btnPrint.onclick = function() {
		var winforPrint = window.open('','','toolbar=no,width=780,height=580,resizable=yes');
		winforPrint.document.location.href = "<?php echo HTTP_DIR . "customer_service/service/pdf/" ?>download_pdf.php?_code=<?php echo trim($_code)."&_date=".date("Ym", strtotime($column['sv_date']))?>&_rev=" + window._revision_time.value;
	}

	window.document.all.btnUpdate.onclick = function() {
		if (window.itemPosition.rows.length <= 0) {
			alert("You need to fill at least 1 item in item list");
			return;
		}

		if (window.repairPosition.rows.length <= 0) {
			alert("You need to fill at least 1 repair item");
			oForm._repair_desc.focus();
			return;
		}

		if(confirm("Are you sure to update?")) {
			if(verify(oForm)){
				oForm.p_mode.value = 'update';
				oForm.submit();
			}
		}
	}

	window.document.all.btnList.onclick = function() {
		window.location.href = 'list_service.php';
	}
</script>
<!---------------------------------------- start payment confirmation ---------------------------------------->
<table width="100%" class="table_sub">
    <tr>
        <th height="35"><img src="../../_images/icon/setting_mini.gif"> &nbsp; <strong>PAYMENT CONFIRM</strong></th>
    </tr>
</table>
<table width="100%" cellpadding="0">
 	<tr>
		<td width="2%"><img src="../../_images/properties/p_leftmenu_icon02.gif"></td>
		<td><strong>Payment Information</strong></td>
    </tr>
    <tr>
		<td></td>
    	<td>
			<form name="frmPayment" method="post">
			<input type="hidden" name="p_mode">
			<input type="hidden" name="_code" value="<?php echo $column["sv_code"]?>">
			<input type="hidden" name="_cus_code" value="<?php echo $column["sv_cus_to"]?>">
			<input type="hidden" name="_remain_amount" value="<?php echo $column["sv_total_remain"]?>">
			<input type="hidden" name="_pay_idx">
			<table width="100%" class="table_box">
				<tr>
					<td width="15%">PAYMENT DATE</td>
					<td width="2%">:</td>
					<td width="25%" colspan="3"><input type="text" class="reqd" name="_payment_date" size="15" value="<?php echo date("j-M-Y")?>"></td>
					<td width="14%">AMOUNT</td>
					<td width="2%">:</td>
					<td>Rp. <input type="text" class="reqn" name="_payment_paid" size="15" onKeyUp="formatNumber(this,'dot')" value="<?php echo number_format($column["sv_total_remain"]) ?>"></td>
				</tr>
				<tr>
					<td>METHOD</td>
					<td>:</td>
					<td colspan="3">
						<input type="radio" name="_method" value="cash" onClick="enabledBankPayment(this, 'cash')" checked>Cash &nbsp;
						<input type="radio" name="_method" value="check" onClick="enabledBankPayment(this, 'check')">Check &nbsp;
						<input type="radio" name="_method" value="transfer" onClick="enabledBankPayment(this, 'transfer')">Transfer &nbsp;
						<input type="radio" name="_method" value="giro" onClick="enabledBankPayment(this, 'giro')">Giro &nbsp;
					</td>
					<td width="15%">REMARK</td>
					<td width="2%">:</td>
					<td><input type="text" name="_payment_remark" class="fmt" style="width:100%"></td>
				</tr>
				<tr>
					<td valign="top">BANK</td>
					<td valign="top">:</td>
					<td>
						<input type="radio" name="_bank" value="BCA1" disabled>BCA 1<br />
						<input type="radio" name="_bank" value="BCA2" disabled>BCA 2
					</td>
					<td>
						<input type="radio" name="_bank" value="MANDIRI" disabled>Mandiri<br />
						<input type="radio" name="_bank" value="BII1" disabled>BII 1
					</td>
					<td>
						<input type="radio" name="_bank" value="BII2" disabled>BII 2<br />
						<input type="radio" name="_bank" value="DANAMON" disabled>Danamon
					</td>
					<td colspan="5" align="right" valign="bottom">
						<button name='btnSave' class='input_btn' style='width:130px;'> <img src="../../_images/icon/btnSave-blue.gif" align="middle"> &nbsp; Save payment</button>
					</td>
				</tr>
			</table><br />
			</form>
    	</td>
    </tr>
 	<tr height="20">
		<td width="2%"><img src="../../_images/properties/p_leftmenu_icon02.gif"></td>
		<td><strong>Payment History</strong></td>
    </tr>
	<tr>
    	<td></td>
		<td>
<?php
if (numQueryRows($pay_res) <= 0) echo "\t\t\t<span class=\"comment\"><i>(No recorded payment)</i></span>";
else { ?>
<table width="100%" class="table_n">
	<tr>
		<th width="5%">#</th>
		<th>PAYMENT<br />DATE</th>
		<th width="10%">METHOD</th>
		<th width="10%">BANK</th>
		<th width="25%">REMARK</th>
		<th width="18%">INPUT</th>
		<th>AMOUNT<br />(Rp)</th>
		<th width="5%">DEL</th>
	</tr>
<?php $i=1; while($payment = fetchRow($pay_res)) { $total_paid=0; ?>
	<tr id="<?php echo $payment[0] ?>">
		<td align="center"><?php echo $i++ ?></td>
		<td align="center"><?php echo date('d-M-y',strtotime($payment[3])) ?></td>
		<td align="center"><?php echo strtoupper($payment[5]) ?></td>
		<td align="center"><?php echo strtoupper($payment[6]) ?></td>
		<td><?php echo $payment[9] ?></td>
		<td><?php echo $payment[7] . ", " . date('j-M-Y', strtotime($payment[8])) ?></td>
		<td align="right"><?php echo number_format($payment[4], 2) ?></td>
		<td align="center"><img src="../../_images/icon/delete.gif" width="12" onclick="deletePayment(<?php echo $payment[0] ?>)"></td>
	</tr>
<?php 
	$total_paid += $payment[4];
} ?>
		<tr>
		<td colspan="6" align="right">TOTAL PAID</td>
		<td align="right"><input type="text" class="fmtn" style="width:80%" value="<?php echo number_format($total_paid, 2) ?>" readonly></td>
		<td></td>
	</tr>
	<tr>
		<td colspan="6" align="right"><b style="color:red">LACK</b></td>
		<td align="right"><input type="text" class="fmtn" style="color:red;width:80%" value="<?php echo number_format($column['sv_total_remain'], 2) ?>" readonly></td>
		<td></td>
	</tr>
	<tr>
		<th colspan="6" align="right">TOTAL AMOUNT</th>
		<th align="right"><input type="text" class="fmtn" style="width:80%" value="<?php echo number_format($column['sv_total_amount'], 2) ?>" readonly></th>
		<th></th>
	</tr>
</table>
<?php
}
?>
</table><br /><br />
<script language="javascript" type="text/javascript">
function defaultPaymentConfirm() {
	var f = window.document.frmPayment;

<?php if ($column['sv_total_remain'] <= 0) { ?>
	f.btnSave.disabled = true;
	f._payment_date.className	= 'fmt';
	f._payment_paid.className	= 'fmt';
	f._payment_date.disabled	= true;
	f._payment_paid.disabled	= true;
	f._payment_remark.disabled	= true;
	f._method[0].disabled	= true;
	f._method[1].disabled	= true;
	f._method[2].disabled	= true;
	f._method[3].disabled	= true;
	f._bank[0].disabled   = true;
	f._bank[1].disabled   = true;
	f._bank[2].disabled   = true;
	f._bank[3].disabled   = true;
	f._bank[4].disabled   = true;
	f._bank[5].disabled   = true;
	f._payment_date.value		= '';
	f._payment_paid.value		= '';
<?php } ?>
}

function enabledBankPayment(o, method){
	var f = window.document.frmPayment;

	if (o.checked == true && method == 'transfer') {
		f._bank[0].disabled = false;
		f._bank[1].disabled = false;
		f._bank[2].disabled = false;
		f._bank[3].disabled = false;
		f._bank[4].disabled = false;
		f._bank[5].disabled = false;
	} else if (method != 'transfer') {
		f._bank[0].disabled   = true;
		f._bank[1].disabled   = true;
		f._bank[2].disabled   = true;
		f._bank[3].disabled   = true;
		f._bank[4].disabled   = true;
		f._bank[5].disabled   = true;
		f._bank[0].checked	  = false;
		f._bank[1].checked	  = false;
		f._bank[2].checked	  = false;
		f._bank[3].checked	  = false;
		f._bank[4].checked	  = false;
		f._bank[5].checked	  = false;
	}
}

function deletePayment(idx) {
	if(confirm("Are you sure to delete?")) {
		window.document.frmPayment.p_mode.value = 'delete_payment';
		window.document.frmPayment._pay_idx.value    = idx;
		window.document.frmPayment.submit();
	}
}

window.document.frmPayment.btnSave.onclick = function() {
	var o = window.document.frmPayment;

	if(parseFloat(removecomma(o._payment_paid.value)) > parseFloat(removecomma(o._remain_amount.value))) {
		alert("Payment cannot more than remain billing");
		o._payment_paid.value = numFormatval(o._remain_amount.value+'',0);
		o._payment_paid.focus();
		return;
	} 

	if(o._method[2].checked==true){
		var check = false;
		for (var i=0;i<6;i++) {
			if(o._bank[i].checked==true){
				var check = true;
			}
		}
		if(check == false){
			alert("Please choose the bank");
			return;
		}
	}

	if(confirm("Are you sure to save payment")) {
		if(verify(o)) {
			o.p_mode.value = 'add_payment';
			o.submit();
		}
	}
}
</script>
<!---------------------------------------- end payment confirmation ---------------------------------------->
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