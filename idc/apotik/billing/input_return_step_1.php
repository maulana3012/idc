<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*/
//REQUIRE
require_once "../../zk_config.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "input_return_step_1.php";
$ordby	= array('ALL'=>'', 'IDC'=>1, 'MEP'=>2);
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script src="../../_include/billing/input_return.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
function checkform(o) {

	if(o._type.value == 'RR'){
		var return_condition = 1;
	} else if(o._is_bill_paid[1].checked == true){
		var return_condition = 2;
	} else if(o._is_bill_paid[0].checked == true && o._is_money_back[1].checked == true){
		var return_condition = 3;
	} else if(o._is_bill_paid[0].checked == true && o._is_money_back[0].checked == true){
		var return_condition = 4;
	}
	o._return_condition.value = return_condition;

	var d0 = formatDate2(new Date()); 
	var turn = formatDate2(parseDate(o._return_date.value, 'prefer_euro_format'));

	if(turn < d0) {
		alert("Return date must be same or later than today");
		o._inv_date.focus();
		return;
	}

	if(o._ship_to_responsible_by.value == 0) {
		alert("Responsibly by must be entered");
		return;
	}

	if (verify(o)) {
		o.submit();
	}
}

function fillInvoice() {

	var f = window.document.frmInsert;
	var x = (screen.availWidth - 530) / 2;
	var y = (screen.availHeight - 600) / 2;
	var ship_to = f._ship_to.value;
	var ship_name = f._ship_name.value;

	var v_order_by = '';
	if(f._access.value == 'ALL') {
		if(f.cboOrdBy[0].checked) {v_order_by = 1}
		else if(f.cboOrdBy[1].checked) {v_order_by = 2}
	} else {
		v_order_by = f._ordered_by.value;
	}

	if(ship_to.length <= 0) {
		alert("You have to fill ship to customer first");
		f._ship_to.focus(); return;
	}

	var win = window.open(
		'./p_list_invoice.php?_order_by=' + v_order_by + '&_cus_code=' + trim(ship_to) + '&_cus_name=' + trim(ship_name),
		'',
		'scrollbars,width=530,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

function  vatValue(o, value) {
	if(value == 1) {
		window.document.frmInsert._is_vat.value = value;
		window.document.frmInsert._vat.disabled = false;
		window.document.frmInsert._vat.value = "10";
	} else {
		window.document.frmInsert._is_vat.value = value;
		window.document.frmInsert._vat.disabled = true;
		window.document.frmInsert._vat.value = "";
	}
}

function checkedValue() {
	var f = window.document.frmInsert;

	if(f._type.value == 'RO') {
		for(var i=0; i<2; i++) {
			f._is_bill_paid[i].disabled		= false;
			f._is_money_back[i].disabled	= false;
		}
		f._is_bill_paid[1].checked			= true;
		f._is_money_back[1].checked			= true;
	} else if(f._type.value == 'RR') {
		for(var i=0; i<2; i++) {
			f._is_bill_paid[i].disabled		= true;
			f._is_money_back[i].disabled	= true;
			f._is_bill_paid[i].checked		= false;
			f._is_money_back[i].checked		= false;
		}
	}
}

function enabledMoneyBack() {
	var f = window.document.frmInsert;

	if(f._is_bill_paid[0].checked == true) {
		f._is_money_back[0].disabled = false;
		f._is_money_back[1].disabled = false;
		f._is_money_back[1].checked  = true;
		f._bill_paid.value	= 1;
	} else {
		for(var i=0; i<2; i++) {
			f._is_money_back[i].disabled = true;
			f._is_money_back[i].checked  = false;
		}
		f._bill_paid.value	= 0;
	}
}

function chkBillType(o) {
	var f = window.document.frmInsert;
	highlighter('bill_type');
}

function chkOrdBy(o) {
	var f = window.document.frmInsert;
	var dept = f._dept.value;

	if(o.value=='1' && o.checked) {
		if(dept == 'A') {
			f._btnVat[0].checked = true;
			vatValue('y');
		} else {
			f._btnVat[1].checked = true;
			vatValue('n');
		}
	} else if(o.value=='2' && o.checked) {
		f._btnVat[1].checked = true;
		vatValue('n');
	}

	highlighter('order_by');
}

function highlighter(type) {
	var f = window.document.frmInsert;

	if(type == 'bill_type') {
		for(var i=0; i<2; i++) {
			if(f.cboTypeBill[i].checked) {
				document.getElementById(i+1).style.backgroundColor="#4e6074";
				document.getElementById(i+1).style.color="#fff";
			} else {
				document.getElementById(i+1).style.backgroundColor="#fff";
				document.getElementById(i+1).style.color="#666";
			}
		}
	} 
	if(f._access.value == 'ALL') {
		if(type == 'order_by') {
			for(var i=0; i<2; i++) {
				if(f.cboOrdBy[i].checked) {
					document.getElementById(i+3).style.backgroundColor="#4e6074";
					document.getElementById(i+3).style.color="#fff";
				} else {
					document.getElementById(i+3).style.backgroundColor="#fff";
					document.getElementById(i+3).style.color="#666";
				}
			}
		}
	}
}

function initPage() {
	var f = window.document.frmInsert;
	f._is_vat.value = 1;
	f.cboTypeBill[0].checked = true;
	highlighter('bill_type');
	highlighter('order_by');
	initOption();
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
<h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] NEW RETURN BILLING (STEP 1 / 2)</h3>
<form name='frmInsert' method='POST' action="./input_return_step_2.php">
<input type='hidden' name='p_mode' value='order_info'>
<input type='hidden' name='_type_return'>
<input type='hidden' name='_return_condition'>
<input type='hidden' name='_dept' value='<?php echo $department ?>'>
<input type='hidden' name='_access' value='<?php echo ZKP_FUNCTION ?>'>
<input type="hidden" name="_ordered_by" value="<?php echo (ZKP_FUNCTION=='ALL')?"1":$cboFilter[1][ZKP_FUNCTION][0][0] ?>">
<table width="100%" class="table_box">
<?php if (ZKP_FUNCTION == 'ALL') { ?>
	<tr>
		<td colspan="2"><strong class="info">ORDERED BY</strong></td>
	</tr>
	<tr height="40px">
		<td id="3"><input type="radio" name="cboOrdBy" value="1" id="3" onClick="chkOrdBy(this)" checked><label for="3">INDOCORE</label></td>
		<td id="4"><input type="radio" name="cboOrdBy" value="2" id="4" onClick="chkOrdBy(this)"><label for="4">MEDIKUS EKA</label></td>
	</tr>
<?php } ?>
	<tr>
		<td colspan="2"><strong class="info"><br />RETURN TYPE</strong></td>
	</tr>
	<tr height="40px">
		<td width="50%" id="1"><input type="radio" name="cboTypeBill" value="0" id="1" onClick="chkBillType(this)"><label for="1">Issue return invoice &amp; receive item</label></td>
		<td width="50%" id="2"><input type="radio" name="cboTypeBill" value="1" id="2" onClick="chkBillType(this)"><label for="2">Issue return invoice only</label></td>
	</tr>
</table><br />
<?php
require_once APP_DIR . "_include/billing/tpl_input_return_1.php"; 
?>
</form>
<div align='right'>
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkform(window.document.frmInsert)'><img src="../../_images/icon/next.gif" width="15px" align="middle" alt="Go to next step"> &nbsp; Next step</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_return_step_1.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel return billing"> &nbsp; Cancel return</button>
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