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
$left_loc	= "input_do_step_1.php";
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
function checkForm(o) {

	if(o._type_item.value == '' && o._type_vat[0].checked == true) {
		o._type_item.value = 1;
	} else if(o._type_item.value == '' && o._type_vat[1].checked == true) {
		o._type_item.value = 2;
	} 

	if (verify(o)) {
		o.submit();
	}
}

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

function copyCustomer(o, target) {
	var f = window.document.frmInsert;

	if(target == 'ship') {
		if(o.checked) {
			f._ship_to.value	= f._cus_to.value;
			f._ship_name.value	= f._cus_name.value;
		} else {
			f._ship_to.value	= "";
			f._ship_name.value	= "";
		}
	}
}

function setEnabledText(value){

	var f = window.document.frmInsert;

	if(value == '') {
		f._turn_code.readOnly	= true;
		f._turn_code.value		= '';
		f._turn_code.className	= 'fmt';
		f._turn_date.className	= 'fmt';
		f._type_vat[0].disabled	= false;
		f._type_vat[1].disabled	= false;
	} else if(value == 'df') {
		f._turn_code.readOnly	= true;
		f._turn_code.value		= '';
		f._turn_code.className	= 'fmt';
		f._turn_date.className	= 'fmt';
		f._type_vat[1].checked	= true;
		f._type_vat[0].disabled	= false;
		f._type_vat[1].disabled	= false;
	} else if(value == 'dr') {
		f._turn_code.readOnly	= false;
		f._turn_code.className	= 'req';
		f._turn_date.className	= 'reqd';
		f._type_vat[0].disabled	= false;
		f._type_vat[1].disabled	= false;
	} else if(value == 'dt') {
		f._turn_code.readOnly	= true;
		f._turn_code.value		= '';
		f._turn_code.className	= 'fmt';
		f._turn_date.className	= 'fmt';
		f._type_vat[1].checked	= true;
		f._type_vat[0].disabled	= true;
		f._type_vat[1].disabled	= true;
		f._type_item.value		= 2;
	}
}

function fillReturnCode(){

	var x = (screen.availWidth - 550) / 2;
	var y = (screen.availHeight - 600) / 2;
	var ship_to		= window.document.frmInsert._ship_to.value;
	var ship_name	= window.document.frmInsert._ship_name.value;


	if(window.document.frmInsert._do_type.value != 'dr') {
		return;
	}else if(ship_to.length <= 0) {
		alert("You have to fill ship to customer first");
		window.document.frmInsert._ship_to.focus();
		return;
	}

	var win = window.open(
		'../../_include/other/p_list_return.php?_cus_code='+ trim(ship_to) + '&_cus_name=' + trim(ship_name),
		'',
		'scrollbars,width=550,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
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
<strong class="title">[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] NEW DELIVERY REQUEST STEP (1 / 2)</strong><br /><br />
<form name='frmInsert' method='POST' action="./input_do_step_2.php">
<input type='hidden' name='p_mode' value='do_info'>
<input type='hidden' name='_type_item' value=''>
<strong class="info">DO INFORMATION</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="13%">DO TYPE</th>
		<td width="34%">
			<select name="_do_type" class="req" onchange="setEnabledText(this.value)">
				<option value="">==SELECT==</option>
				<option value="df">FREE</option>
				<option value="dr">REPLACE</option>
				<option value="dt">TEMPORARY</option>
			</select>
		</td>
		<th width="13%">DO DATE</th>
		<td><input name="_do_date" type="text" class="reqd" size="15" value="<?php echo date("j-M-Y")?>"></td>
	</tr>
	<tr>
		<th>ISSUED BY</th>
		<td><input name="_received_by" type="text" class="req" size="15" maxlength="32" value="<?php echo $S->getValue("ma_account") ?>"></td>
		<th>ISSUED DATE</th>
		<td><input name="_issued_date" type="text" class="reqd" size="15" value="<?php echo date("j-M-Y")?>"></td>
	</tr>
	<tr>
		<th>REQUEST BY</th>
		<td><input name="_issued_by" type="text" class="fmt" size="15" maxlength="32"></td>
		<th>TYPE ITEM</th>
		<td>
			<input type="radio" name="_type_vat" value="1"> Vat &nbsp;
			<input type="radio" name="_type_vat" value="2"> Non Vat
		</td>
	</tr>
</table>
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<th rowspan="2" width="13%">CUSTOMER</th>
		<th width="10%"><a href="javascript:fillCustomer('customer')" accesskey="c"><u>C</u>ODE</a></th>
		<td width="24%"><input name="_cus_to" type="text" class="req" size="10" maxlength="7"></td>
		<th width="13%">NAME</th>
		<td width="43%"><input type="text" name="_cus_name" class="req" style="width:100%" maxlength="128"></td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="3"><input type="text" name="_cus_address" class="fmt"  style="width:100%" maxlength="255"></td>
	</tr>
	<tr>
		<th>SHIP TO</th>
		<th><a href="javascript:fillCustomer('ship')">CODE</a></th>
		<td>
			<input name="_ship_to" type="text" class="req" size="10" maxlength="7">
			<input type="checkbox" name="chkAbove" onClick="copyCustomer(this, 'ship')" id="ship"><label for="ship">Same as Above</label>
		</td>
		<th>NAME</th>
		<td><input type="text" name="_ship_name" class="req" style="width:100%" maxlength="128"></td>
	</tr>
	<tr>
		<th>RETURN REF.</th>
		<th>CODE</th>
		<td>
			<input type="text" name="_turn_code" class="fmt" size="15" readonly> &nbsp;
			<a href="javascript:fillReturnCode()"><img src="../../_images/icon/search_mini.gif" alt="Search return code . . ."></a>
		</td>
		<th>DATE</th>
		<td><input type="text" name="_turn_date" class="fmtd" size="15" readonly></td>
	</tr>
</table>
</form>
<div align="right">
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkForm(window.document.frmInsert)'><img src="../../_images/icon/next.gif" width="15px" align="middle" alt="Go to next step"> &nbsp; Next step</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_do_step_1.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel DO"> &nbsp; Cancel DO</button>
</div>
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