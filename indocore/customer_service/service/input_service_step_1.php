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
$left_loc 	   = "input_service_step_1.php";
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
	if (verify(o)) {
		o.submit();
	}
}

function fillRegNo() {
	var x = (screen.availWidth - 450) / 2;
	var y = (screen.availHeight - 600) / 2;
	var win = window.open(
		'p_list_registration.php', '',
		'scrollbars,width=450,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

function fillCustomer(target) {
	var f		 = window.document.frmInsert;

	if(f._source_cus[1].checked == true) {
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

	if(f._source_cus[0].checked == true) {
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
		f._source_customer.value = 1;
	} else if(f._source_cus[1].checked == true) {
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
		f._source_customer.value = 0;
	}
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
<h3>[<font color="#446fbe">CUSTOMER SERVICE</font>] NEW SERVICE BILLING (STEP 1 / 2)</h3>
<form name='frmInsert' method='POST' action="input_service_step_2.php">
<input type='hidden' name='p_mode' value='svc_info'>
<input type='hidden' name='_source_customer' value="1">
<span class="bar_bl">CUSTOMER INFORMATION</span>
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
		<th>REG NO</th>
		<td>
			<input type="text" name="_reg_no" class="fmt" size="10" readonly> &nbsp; <a href="javascript:fillRegNo()"><img src="../../_images/icon/search_mini.gif" alt="Search reg no reference"></a>
		</td>
		<th>GUARANTEE</th>
		<td colspan="4">
			<input type="radio" name="_is_guarantee" value="true" id="true" onclick="enabledText()" checked><label for="true">Yes, until : &nbsp;</label><input type="text" name="_guarantee_period" class="reqd" size="15"> &nbsp;
			<input type="radio" name="_is_guarantee" value="false" id="false" onclick="enabledText()"><label for="false">Expired</label>
		</td>
	</tr>
</table><br />
<span class="bar_bl">SERVICE INFORMATION</span>
<table width="100%" class="table_box" cellpadding="1" cellspacing="1">
	<tr>
		<th width="15%" rowspan="4">CUSTOMER</th>
		<td width="2%" rowspan="2"><input type="radio" name="_source_cus" value="1" onclick="setEnabled()" checked></td>
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
		<td width="2%" rowspan="2"><input type="radio" name="_source_cus" value="0" onclick="setEnabled()"></td>
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
</form>
<div align='right'>
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkform(window.document.frmInsert)'><img src="../../_images/icon/next.gif" width="15px" align="middle" alt="Go to next step"> &nbsp; Next step</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_service_step_1.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel registration"> &nbsp; Cancel reg</button>
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