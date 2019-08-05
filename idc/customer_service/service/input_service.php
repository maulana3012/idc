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
$left_loc 	   = "input_service.php";

//---------------------------------------------------------------------------------------------------- insert
if(ckperm(ZKP_INSERT,  HTTP_DIR . "$currentDept/$moduleDept/index.php", 'insert')) {

	$_service_date		= $_POST['_service_date'];
	$_received_by		= $_POST['_received_by'];
	$_is_guarantee		= $_POST['_is_guarantee'];
	$_guarantee_period	= $_POST['_guarantee_period'];
	$_signature_by		= $_POST['_signature_by'];
	$_days_to_due		= ($_POST['_days_to_due'] == '') ? 0 : $_POST['_days_to_due'];
	$_due_date			= $_POST['_due_date'];
	$_remark			= $_POST['_remark'];
	$_total_disc		= $_POST['totalDisc'];
	$_total_amount		= $_POST['totalAmount'];
	$_revision_time 	= -1;
	$_lastupdated_by_account = $S->getValue("ma_account");

	$_source_customer	= $_POST['_source_customer'];
	$_cus_to			= (isset($_POST['_cus_to'])) ? $_POST['_cus_to'] : '';
	$_cus_name			= (isset($_POST['_cus_name'])) ? $_POST['_cus_name'] : '';
	$_cus_address		= (isset($_POST['_cus_address'])) ? $_POST['_cus_address'] : '';
	$_make_cus_name		= (isset($_POST['_make_cus_name'])) ? $_POST['_make_cus_name'] : '';
	$_make_cus_phone	= (isset($_POST['_make_cus_phone'])) ? $_POST['_make_cus_phone'] : '';
	$_make_cus_hphone	= (isset($_POST['_make_cus_hphone'])) ? $_POST['_make_cus_hphone'] : '';
	$_make_cus_address	= (isset($_POST['_make_cus_address'])) ? $_POST['_make_cus_address'] : '';

	$_due_date_chk	= 0;
	foreach($_POST['_due_date_chk'] as $val) $_due_date_chk = $_due_date_chk + $val;

	//Item 
	foreach($_POST['_it_model_no'] as $val)				$_it_model_no[]			= $val;
	foreach($_POST['_it_sn'] as $val)					$_it_sn[] 				= $val;
	foreach($_POST['_it_repair_desc'] as $val)			$_it_repair_desc[]		= $val;
	foreach($_POST['_it_repair_qty'] as $val)			$_it_repair_qty[]		= $val;
	foreach($_POST['_it_repair_price'] as $val)			$_it_repair_price[]		= $val;
	foreach($_POST['_it_repair_remark'] as $val)		$_it_repair_remark[]	= $val;

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
		"insertService",
		"$\${$_service_date}$\$",
		"$\${$_received_by}$\$",
		$_source_customer,
		"$\${$_cus_to}$\$",
		"$\${$_cus_name}$\$",
		"$\${$_cus_address}$\$",
		"$\${$_make_cus_name}$\$",
		"$\${$_make_cus_phone}$\$",
		"$\${$_make_cus_hphone}$\$",
		"$\${$_make_cus_address}$\$",
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

	if (isZKError($result)) {
		$errMessage = $result->getMessage();
		if(strpos($errMessage, "duplicate key violates")) {
			$result = new ZKError(
				"DUPLICATE_CODE_EXIST",
				"DUPLICATE_CODE_EXIST",
				"Same Code exists, please check your order code again");
		}
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/input_service.php");
	} else if(isZKError($result)) {
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/input_service.php");
	}
	//SAVE PDF FILE
	$_code = $result[0];
	include "./pdf/generate_service_pdf.php";
	goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_service.php?_code=$_code");
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
function checkform(value, print_position) {
	var f = window.document.frmInsert;

	if(value) {
		if (window.modelPosition.rows.length <= 0) {
			alert("You need to fill at least 1 model");
			f.insertModel.focus();
			return;
		}

		if (window.repairPosition.rows.length <= 0) {
			alert("You need to fill at least 1 repair item");
			f._repair_desc.focus();
			return;
		}

		if (verify(f)) {
			if(confirm("Are you sure to save?")) {
				f.submit();
			}
		}	
	} else{
		//check form
		if (print_position == 0) {
			if(f.insertModel.value.length <= 0) {
				alert("Please insert the model no");
				f.insertModel.focus();
				return;
			} else if(f.insertSN.value.length <= 0) {
				alert("Please insert the serial no");
				f.insertSN.focus();
				return;
			}
		} else if (print_position == 1) {
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
}

function printRow(print_position) {
	var f	= window.document.frmInsert;
	var oTR = window.document.createElement("TR");
	var oTD = new Array();
	var oTextbox = new Array();

	if (print_position == 0) {
		for (var i=0; i<3; i++) {
			oTD[i] = window.document.createElement("TD");
			oTextbox[i] = window.document.createElement("INPUT");
			oTextbox[i].type = "text";
	
			switch (i) {
				case 0: // _model
					oTextbox[i].style.width		= "100%";
					oTextbox[i].className		= "req";
					oTextbox[i].name			= "_it_model_no[]";
					oTextbox[i].value			= f.insertModel.value;
					break;
	
				case 1: // _sn
					oTextbox[i].style.width		= "100%";
					oTextbox[i].className		= "req";
					oTextbox[i].name			= "_it_sn[]";
					oTextbox[i].value			= f.insertSN.value;
					break;
		
				case 2: // [del]
					oTD[i].align		= "center";
					oTD[i].innerHTML	= "<a href=\"javascript:deleteItem('" + f.insertModel.value+'-'+f.insertSN.value + "',0)\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
					break;
			}
			if (i<2) oTD[i].appendChild(oTextbox[i]);
			oTR.id = f.insertModel.value+'-'+f.insertSN.value;
			oTR.appendChild(oTD[i]);
		}
		window.modelPosition.appendChild(oTR);
		f.insertModel.value	= '';f.insertSN.value	= '';f.insertModel.focus();
	} else if (print_position == 1) {
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
		var count = window.modelPosition.rows.length;
		for (var i=0; i<count; i++) {
			var oRow = window.modelPosition.rows(i);
			if (oRow.id == id) {
				var n = window.modelPosition.removeChild(oRow);
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
	var f		 = window.document.frmInsert;

	if(f._source_customer[1].checked == true) {
		alert("You cannot input customer code for this service.");
		return;
	}
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

function setEnabled() {
	var f = window.document.frmInsert;

	if(f._source_customer[0].checked == true) {
		f._cus_to.disabled = false
		f._cus_name.disabled = false;
		f._cus_address.disabled = false;
		f._make_cus_name.disabled = true;
		f._make_cus_phone.disabled = true;
		f._make_cus_hphone.disabled = true;
		f._make_cus_address.disabled = true;
		f._make_cus_name.value = '';
		f._make_cus_phone.value = '';
		f._make_cus_hphone.value = '';
		f._make_cus_address.value = '';
		f._cus_to.className = 'req';
		f._cus_name.className = 'req';
		f._make_cus_name.className = 'fmt';
	} else if(f._source_customer[1].checked == true) {
		f._cus_to.disabled = true;
		f._cus_name.disabled = true;
		f._cus_address.disabled = true;
		f._make_cus_name.disabled = false;
		f._make_cus_phone.disabled = false;
		f._make_cus_hphone.disabled = false;
		f._make_cus_address.disabled = false;
		f._cus_to.value = '';
		f._cus_name.value = '';
		f._cus_address.value = '';
		f._cus_to.className = 'fmt';
		f._cus_name.className = 'fmt';
		f._make_cus_name.className = 'req';
	}
}

function updateAmount() {
	var f	= window.document.frmInsert;
	var e 	= window.document.frmInsert.elements;
	var countModel		= window.modelPosition.rows.length;
	var countRepair		= window.repairPosition.rows.length;
	var countReplace	= window.replacePosition.rows.length;
	var numInputModel	= 2;
	var numInput		= 5;

	var idx_model	= 18;		/////
	var idx_qty1	= idx_model+(numInputModel*countModel)+6;
	var idx_price1	= idx_model+(numInputModel*countModel)+7;
	var idx_amount1	= idx_model+(numInputModel*countModel)+8;

	var idx_qty2	= idx_model+(numInputModel*countModel)+(numInput*countRepair)+5+6;
	var idx_price2	= idx_model+(numInputModel*countModel)+(numInput*countRepair)+5+7;
	var idx_amount2	= idx_model+(numInputModel*countModel)+(numInput*countRepair)+5+8;

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

	f.totalNet.value	= numFormatval(sumOfNet + '', 0);
	f.totalAmount.value	= numFormatval(sumOfNet-disc + '', 0);
}

function initPage() {
	updateAmount();
	setEnabled();
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
<h3>[<font color="#446fbe">CUSTOMER SERVICE</font>] NEW SERVICE BILLING</h3>
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode' value='insert'>
<span class="bar_bl">CUSTOMER INFORMATION</span>
<table width="100%" class="table_box" cellpadding="1" cellspacing="1">
	<tr>
		<th width="15%" rowspan="4">CUSTOMER</th>
		<td width="2%" rowspan="2"><input type="radio" name="_source_customer" value="1" onclick="setEnabled()" checked></td>
		<td><img src="../../_images/properties/p_leftmenu_icon02.gif"> Source from current customer</td>
	</tr>
	<tr>
		<td>
			<table width="100%" class="table_box">
				<tr>
					<th width="12%"><a href="javascript:fillCustomer('customer')" accesskey="c"><u>C</u>ODE</a></th>
					<td width="15%"><input type="text" name="_cus_to" class="req" size="10" maxlength="7"></td>
					<th width="12%">NAME</th>
					<td><input type="text" name="_cus_name" class="fmt" style="width:100%" maxlength="128"></td>
				</tr>
				<tr>
					<th>ADDRESS</th>
					<td colspan="3"><input type="text" name="_cus_address" class="fmt" style="width:100%" maxlength="128"></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr height="25px">
		<td width="2%" rowspan="2"><input type="radio" name="_source_customer" value="0" onclick="setEnabled()"></td>
		<td valign="bottom"><img src="../../_images/properties/p_leftmenu_icon02.gif"> Make a new customer</td>
	</tr>
	<tr>
		<td>
			<table width="100%" class="table_box">
				<tr>
					<th width="12%">NAME</th>
					<td><input type="text" name="_make_cus_name" class="fmt" style="width:100%" maxlength="128"></td>
					<th width="10%">PHONE</th>
					<td width="15%"><input type="text" name="_make_cus_phone" class="fmt" style="width:100%" maxlength="32"></td>
					<th width="10%">HP</th>
					<td width="15%"><input type="text" name="_make_cus_hphone" class="fmt" style="width:100%" maxlength="32"></td>
				</tr>
				<tr>
					<th>ADDRESS</th>
					<td colspan="5"><input type="text" name="_make_cus_address" class="fmt" style="width:100%" maxlength="128"></td>
				</tr>
			</table>
		</td>
	</tr>
</table><br />
<span class="bar_bl">SERVICE INFORMATION</span>
<table width="100%" class="table_box" cellpadding="1" cellspacing="1">
	<tr>
		<th width="15%">SERVICE NO</th>
		<td width="25%"></td>
		<th width="12%">SERVICE DATE</th>
		<td><input type="text" name="_service_date" class="reqd" size="15" value="<?php echo date('d-M-Y') ?>"></td>
		<th width="12%">RECEIVED BY</th>
		<td width="20%"><input type="text" name="_received_by" class="req" size="15" value="<?php echo $S->getValue("ma_account")?>"></td>
	</tr>
	<tr>
		<th>GUARANTEE</th>
		<td colspan="4">
			<input type="radio" name="_is_guarantee" value="true" id="true" onclick="enabledText()" checked><label for="true">Yes, until : &nbsp;</label><input type="text" name="_guarantee_period" class="reqd" size="15"> &nbsp;
			<input type="radio" name="_is_guarantee" value="false" id="false" onclick="enabledText()"><label for="false">Expired</label>
		</td>
	</tr>
	<tr>
		<th>MODEL &amp; SN</th>
		<td colspan="5">
			<table width="100%" class="table_box">
				<tr>
					<td valign="top" width="40%">
						<span class="comment"><b><u>Insert Model &amp; SN</u></b></span>
						<table width="100%" class="table_box">
							<tr>
								<td width="30%">Model no</td>
								<td>
									<b>:</b> <input type="text" name="insertModel" class="fmt" size="23" maxlength="64"> &nbsp; &nbsp;
									<button name="btnAddModel" class="fmt" style="width:50px" onclick="checkform(false, 0)">ADD</button>
								</td>
							</tr>
							<tr>
								<td>Serial number</td>
								<td><b>:</b> <input type="text" name="insertSN" class="fmt" size="35" maxlength="64" onKeyPress="if(window.event.keyCode==13) {checkform(false, 0)}"></td>
							</tr>
						</table>
					</td>
					<td valign="top" width="40%">
						<span class="comment"><b><u>List Model &amp; SN</u></b></span>
						<table width="100%" class="table_box">
							<thead>
								<tr>
									<td width="40%"></td>
									<td width="50%"></td>
									<td width="5%"></td>
								</tr>
							</thead>
							<tbody id="modelPosition">
							</tbody>
						</table>
					</td>
				</tr>
				
			</table>
		</td>
	</tr>
</table><br />
<table width="80%" class="table_box">
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
					<td><input type="text" name="_repair_desc" class="fmt" style="width:100%" onKeyPress="if(window.event.keyCode==13) {checkform(false,1)}"></td>
					<td><input type="text" name="_repair_qty" class="fmtn" style="width:100%" value="1" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')" onKeyPress="if(window.event.keyCode==13) {checkform(false,1)}"></td>
					<td><input type="text" name="_repair_price" class="fmtn" style="width:100%" value="0" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')" onKeyPress="if(window.event.keyCode==13) {checkform(false,1)}"></td>
					<td><input type="text" name="_repair_amount" class="fmtn" style="width:100%" readonly></td>
					<td><input type="text" name="_repair_remark" class="fmt" style="width:100%" onKeyPress="if(window.event.keyCode==13) {checkform(false,1)}"></td>
					<td><a href="javascript:checkform(false,1)"><img src="../../_images/icon/add.png" align="middle" alt="Add row"></a></td>
				</tr>
			</thead>
			<tbody id="repairPosition">
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
					<td><input type="text" name="_replace_part_name" class="fmt" style="width:100%" onKeyPress="if(window.event.keyCode==13) {checkform(false,2)}"></td>
					<td><input type="text" name="_replace_qty" class="fmtn" style="width:100%" value="1" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')" onKeyPress="if(window.event.keyCode==13) {checkform(false,2)}"></td>
					<td><input type="text" name="_replace_price" class="fmtn" style="width:100%" value="0" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')" onKeyPress="if(window.event.keyCode==13) {checkform(false,2)}"></td>
					<td><input type="text" name="_replace_amount" class="fmtn" style="width:100%" readonly></td>
					<td><input type="text" name="_replace_remark" class="fmt" style="width:100%" onKeyPress="if(window.event.keyCode==13) {checkform(false,2)}"></td>
					<td><a href="javascript:checkform(false,2)"><img src="../../_images/icon/add.png" align="middle" alt="Add row"></a></td>
				</tr>
			</thead>
			<tbody id="replacePosition">
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
			<th><input type="text" name="totalDisc" class="reqn" style="width:100%;color:red" value="0" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')"></th>
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
			<input type="checkbox" name="_due_date_chk[]" value="1"> <input type="text" name="_days_to_due" class="fmtn" style="width:25px" onblur="setDueDate(this.checked,1)"> days after
			<select name="_date_condition" class="fmt">
				<option value="Invoice">INVOICE</option>
			</select>
			<input type="checkbox" name="_due_date_chk[]" value="2" onclick="setDueDate(this.checked,2)"> COD &nbsp; &nbsp;
			Due : <input type="text" name="_due_date" class="reqd" size="10">
		</td>
		<th width="12%">SIGN BY</th>
		<td><input type="text" name="_signature_by" class="req" size="15"></td>
	</tr>
	<tr>
		<th>REMARK</th>
		<td colspan="3"><textarea name="_remark" style="width:100%" rows="4"></textarea></td>
	</tr>
</table>
</form>
<p align='center'>
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkform(true)'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save service"> &nbsp; Save service</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_service.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel service"> &nbsp; Cancel service</button>
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