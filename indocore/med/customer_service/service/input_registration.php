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
$left_loc 	   = "input_registration.php";

//---------------------------------------------------------------------------------------------------- INSERT PROCESS
if(ckperm(ZKP_INSERT,  HTTP_DIR . "$currentDept/$moduleDept/index.php", 'insert')) {

	$_reg_date			= $_POST['_reg_date'];
	$_source_customer	= $_POST['_source_customer'];
	$_cus_to			= (isset($_POST['_cus_to'])) ? $_POST['_cus_to'] : '';
	$_cus_name			= (isset($_POST['_cus_name'])) ? $_POST['_cus_name'] : '';
	$_cus_address		= (isset($_POST['_cus_address'])) ? $_POST['_cus_address'] : '';
	$_make_cus_name		= (isset($_POST['_make_cus_name'])) ? $_POST['_make_cus_name'] : '';
	$_make_cus_phone	= (isset($_POST['_make_cus_phone'])) ? $_POST['_make_cus_phone'] : '';
	$_make_cus_hphone	= (isset($_POST['_make_cus_hphone'])) ? $_POST['_make_cus_hphone'] : '';
	$_make_cus_address	= (isset($_POST['_make_cus_address'])) ? $_POST['_make_cus_address'] : '';
	$_remark			= $_POST['_remark'];
	$_lastupdated_by_account = $S->getValue("ma_account");
	$_revision_time 	= -1;

	//Item 
	foreach($_POST['_it_code'] as $val)					$_it_code[]				= $val;
	foreach($_POST['_it_model_no'] as $val)				$_it_model_no[] 		= $val;
	foreach($_POST['_it_sn'] as $val)					$_it_sn[]				= $val;
	foreach($_POST['_it_is_guarantee'] as $val)			$_it_is_guarantee[]		= $val;
	foreach($_POST['_it_guarantee_period'] as $val)		$_it_guarantee_period[] = $val;
	foreach($_POST['_it_cus_complain'] as $val)			$_it_cus_complain[]		= $val;
	foreach($_POST['_it_tech_analyze'] as $val)			$_it_tech_analyze[]		= $val;

	$_it_code				= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_it_model_no			= '$$' . implode('$$,$$', $_it_model_no) . '$$';
	$_it_sn					= '$$' . implode('$$,$$', $_it_sn) . '$$';
	$_it_is_guarantee		= implode(',', $_it_is_guarantee);
	$_it_guarantee_period	= 'DATE $$' . implode('$$,$$', $_it_guarantee_period) . '$$';
	$_it_cus_complain		= '$$' . implode('$$,$$', $_it_cus_complain) . '$$';
	$_it_tech_analyze		= '$$' . implode('$$,$$', $_it_tech_analyze) . '$$';

	$result = executeSP(
		"insertServiceReg",
		"$\${$_reg_date}$\$",
		$_source_customer,
		"$\${$_cus_to}$\$",
		"$\${$_cus_name}$\$",
		"$\${$_cus_address}$\$",
		"$\${$_make_cus_name}$\$",
		"$\${$_make_cus_phone}$\$",
		"$\${$_make_cus_hphone}$\$",
		"$\${$_make_cus_address}$\$",
		"$\${$_lastupdated_by_account}$\$",
		"$\${$_remark}$\$",
		"ARRAY[$_it_code]",
		"ARRAY[$_it_model_no]",
		"ARRAY[$_it_sn]",
		"ARRAY[$_it_is_guarantee]",
		"ARRAY[$_it_guarantee_period]",
		"ARRAY[$_it_cus_complain]",
		"ARRAY[$_it_tech_analyze]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/input_registration.php");
	}
	$_code = $result[0];
	goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_registration.php?_code=$_code");
}
?>
<html>
<head>
<title>PT. INDOCORE PERKASA [APOTIK MANAGEMENT SYSTEM]</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
function checkform(value, print_position) {
	var f = window.document.frmInsert;

	if (verify(f)) {
		if(confirm("Are you sure to save?")) {
			f.submit();
		}
	}	

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

	for (var i=0; i<7; i++) {
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

			case 1: // MODEL NO
				oTextbox[i].style.width = "100%";
				oTextbox[i].className	= "req";
				oTextbox[i].name		= "_it_model_no[]";
				oTextbox[i].value		= f2.elements[1].value;
				break;

			case 2: // SERIAL NUMBER
				oTextbox[i].style.width = "100%";
				oTextbox[i].className	= "req";
				oTextbox[i].name		= "_it_sn[]";
				oTextbox[i].value		= f2.elements[5].value;
				break;

			case 3: // GUARANTEE PERIOD
				if(f2.elements[2].checked) {
					var nilaiText	= 'Yes, '+ f2.elements[3].value;
					var nilaiInput	= 1;
				} else {
					var nilaiText	= 'No';
					var nilaiInput	= 0;
				}
				oTD[i].align		= 'center';
				oTD[i].innerText	= nilaiText;
				oTextbox[i].type	= "hidden";
				oTextbox[i].name	= "_it_is_guarantee[]";
				oTextbox[i].value	= nilaiInput;
				break;

			case 4: // CUSTOMER COMPLAIN
				oTextbox[i].style.width = "100%";
				oTextbox[i].className	= "fmt";
				oTextbox[i].name		= "_it_cus_complain[]";
				oTextbox[i].value		= f2.elements[6].value;
				break;

			case 5: // TECHNICAL ANALYZE
				oTextbox[i].style.width = "100%";
				oTextbox[i].className	= "fmt";
				oTextbox[i].name		= "_it_tech_analyze[]";
				oTextbox[i].value		= f2.elements[7].value;
				break;

			case 6: // DELETE
				oTD[i].innerHTML		= "<a href=\"javascript:deleteItem('" + f2.elements[1].value+'||'+f2.elements[5].value + "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD[i].align			= "center";
				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_it_guarantee_period[]";
				if(f2.elements[2].checked) {
					var nilaiInput	= f2.elements[3].value;
				} else {
					var nilaiInput	= '1-Jan-1970';
				}
				oTextbox[i].value		= nilaiInput;
				break;
		}

		oTD[i].appendChild(oTextbox[i]);
		oTR.id = f2.elements[1].value+'||'+f2.elements[5].value;
		oTR.appendChild(oTD[i]);
	}

	window.rowPosition.appendChild(oTR);
	for (var i=0; i<8; i++) {f2.elements[i].value = '';}
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

function updateAmount() {
	var f		= window.document.frmInsert;
	var count	= window.rowPosition.rows.length;
	f.totalItem.value	= numFormatval(count + '', 0);
}

function initPage() {
	updateAmount();
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
<h3>[<font color="#446fbe">CUSTOMER SERVICE</font>] NEW SERVICE ITEM</h3>
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode' value='insert'>
<span class="bar_bl">SERVICE INFORMATION</span>
<table width="100%" class="table_box" cellpadding="1" cellspacing="1">
	<tr>
		<th width="15%">REG NO.</th>
		<td width="30%"><input type="text" name="_reg_no" class="fmt" size="20" readonly></td>
		<th width="15%">RECEIVE DATE.</th>
		<td><input type="text" name="_reg_date" class="reqd" size="12" value="<?php echo date('j-M-Y') ?>"></td>
	</tr>
</table>
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
<span class="bar_bl">ITEM LIST</span> &nbsp; <small class="comment"><i><a href="javascript:fillItem()">( search <img src="../../_images/icon/search_mini.gif"> )</a></i></small>
<table width="100%" class="table_box" cellpadding="1" cellspacing="1">
	<tr>
		<th width="8%" rowspan="2">CODE</th>
		<th width="20%" rowspan="2">MODEL NO</th>
		<th width="15%" rowspan="2">SERIAL<br />NUMBER</th>
		<th width="15%" rowspan="2">GUARANTEE</th>
		<th colspan="2">REMARK</th>
		<th width="3%" rowspan="2">DEL</th>
	</tr>
	<tr>
		<th>CUSTOMER</th>
		<th>TECHNICIAN</th>
	</tr>
	<tbody id="rowPosition">	
	</tbody>
	<tr>
		<th colspan="6" align="right">TOTAL ITEM <input type="text" name="totalItem" class="fmtn" style="width:5%" readOnly></th>
		<th></th>
	</tr>
</table><br />
<span class="bar_bl">SERVICE INFORMATION</span>
<table width="100%" class="table_box" cellpadding="1" cellspacing="1">
	<tr>
		<th width="15%">REMARK</th>
		<td><textarea name="_remark" rows="4" cols="55"></textarea></td>
	</tr>
</table>
</form>
<p align='center'>
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkform(true)'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save registration"> &nbsp; Save reg</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_registration.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel registration"> &nbsp; Cancel reg</button>
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