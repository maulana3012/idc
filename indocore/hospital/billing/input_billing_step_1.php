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
$left_loc = "input_billing_step_1.php";
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
<script src="../../_include/billing/input_billing.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
function checkform(o) {

	if(o._do_date.disabled == false) {
		var d1 = parseDate(o._do_date.value, 'prefer_euro_format');
		var d2 = parseDate(o._inv_date.value, 'prefer_euro_format');

		if (d1.getTime() < d2.getTime()) {
			alert("Delivery Date must be future date than Invoice Date");
			return;
		}
	}

	if(o._access.value == 'ALL') {
		if(o.cboOrdBy[1].checked && o._btnVat[0].checked) {
			alert("If you want to choose Medikus Eka, vat type must be IX");
			o._btnVat[1].checked = true;
			vatValue('n');
			return;
		}
	} else if(o._access.value == 'MEP') {
		if(o._btnVat[0].checked) {
			alert("Vat type must be IX");
			o._btnVat[1].checked = true;
			vatValue('n');
			return;
		}
	} 
	
	if(o.cboTypeBill[2].checked && o._btnVat[1].checked) {
		alert("If you want to choose billing linked from sales report, you have to choose vat");
		o._btnVat[0].checked = true;
		vatValue('y');
		return;
	} else if(o._ship_to_responsible_by.value == 0) {
		alert("Responsibly by must be entered");
		return;
	}

	if (verify(o)) {
		o.submit();
	}

}

function chkBillType(o) {
	var f = window.document.frmInsert;

	if(o.value=='1' && o.checked) {f._do_date.value=f._inv_date.value;f._do_date.disabled=false;f._do_date.className='reqd';}
	else {f._do_date.value='';f._do_date.disabled=true;f._do_date.className='fmtd';}

	highlighter('bill_type');
}

function chkOrdBy(o) {
	var f = window.document.frmInsert;
	var dept = f._dept.value;

	if(o.value=='1' && o.checked) {
		f._btnVat[0].checked = true;
		f._btnVat[1].disabled = true;
		vatValue('y');
	} else if(o.value=='2' && o.checked) {
		f._btnVat[1].disabled = false;
		f._btnVat[1].checked = true;
		vatValue('n');
	}
	highlighter('order_by');
}

function highlighter(type) {
	var f = window.document.frmInsert;
	
	if(type == 'bill_type') {
		for(var i=0; i<3; i++) {
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
					document.getElementById(i+4).style.backgroundColor="#4e6074";
					document.getElementById(i+4).style.color="#fff";
				} else {
					document.getElementById(i+4).style.backgroundColor="#fff";
					document.getElementById(i+4).style.color="#666";
				}
			}
		}
	}
}

function enabledText(o) {
	var f = window.document.frmInsert;

	if(o.checked == true) {
		f._sj_code.disabled = false;
		f._sj_date.disabled = false;
		f._sj_code.className = 'req';
		f._sj_date.className = 'reqd';
		f._sj_date.value = f._inv_date.value;
		f._sj_code.focus();
	} else {
		f._sj_code.readonly == true;
		f._sj_date.readonly == true;
		f._sj_code.className = 'fmt';
		f._sj_date.className = 'fmt';
		f._sj_code.value	= '';
		f._sj_date.value	= '';
	}
}

function vatValue(o) {
	var f = window.document.frmInsert;

	if(o == "y") {
		var dsb_vat = false;
		var val_vat = '10';
		if(f._cus_to.value.length>0) {var chk_pajak = true;copyCustomer(f.chkAbove2, 'pajak')} else {var chk_pajak = false;}
		var dsb_pajak = false;
		var cls_pajak = 'req';
		var dsb_type_of_pajak = false;
		var val_type_of_pajak0 = true;
		var val_type_of_pajak1 = false;
	} else if(o == "n") {
		var dsb_vat = true;
		var val_vat = '';
		var chk_pajak = false;
		var dsb_pajak = true;
		var cls_pajak = 'fmt';
		var dsb_type_of_pajak = true;
		var val_type_of_pajak0 = false;
		var val_type_of_pajak1 = false;
	}

	f._vat_val.disabled = dsb_vat;
	f._vat_val.value	= val_vat;
	f.chkAbove2.checked = chk_pajak;
	f.chkAbove2.disabled = dsb_pajak;
	f._pajak_to.disabled = dsb_pajak;
	f._pajak_name.disabled = dsb_pajak;
	f._pajak_address.disabled = dsb_pajak;
	f._pajak_to.className = cls_pajak;
	f._pajak_name.className = cls_pajak;
	f._pajak_address.className = cls_pajak;
	f._pajak_to.value = '';
	f._pajak_name.value = '';
	f._pajak_address.value = '';
	f._type_of_pajak[0].disabled = dsb_type_of_pajak;
	f._type_of_pajak[1].disabled = dsb_type_of_pajak;
	f._type_of_pajak[0].checked = val_type_of_pajak0;
	f._type_of_pajak[1].checked = val_type_of_pajak1;
}

function initPage(){
	initOption();
	initBillDept(window.document.frmInsert._access.value);
	highlighter('bill_type');
	highlighter('order_by');
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
<h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] NEW BILLING (STEP 1 / 2)<br /></h3>
<form name='frmInsert' method='POST' action="./input_billing_step_2.php">
<input type='hidden' name='p_mode' value='order_info'>
<input type='hidden' name='_dept' value='<?php echo $department ?>'>
<input type='hidden' name='_access' value='<?php echo ZKP_FUNCTION ?>'>
<input type='hidden' name='_admin' value='<?php echo ucfirst($S->getValue("ma_account"))?>'>
<input type='hidden' name='_order_by' value='<?php echo (ZKP_FUNCTION=='ALL')?"":$cboFilter[1][ZKP_FUNCTION][0][0] ?>'>
<table width="100%" class="table_box">
<?php if (ZKP_FUNCTION == 'ALL') { ?>
	<tr>
		<td colspan="3"><strong class="info">ORDERED BY</strong></td>
	</tr>
	<tr height="40px">
		<td id="4"><input type="radio" name="cboOrdBy" value="1" id="4" onClick="chkOrdBy(this)" checked><label for="4">INDOCORE</label></td>
		<td id="5"><input type="radio" name="cboOrdBy" value="2" id="5" onClick="chkOrdBy(this)"><label for="5">MEDIKUS EKA</label></td>
		<td>&nbsp;</td>
	</tr>
<?php } ?>
	<tr>
		<td colspan="3"><strong class="info"><br />BILLING TYPE</strong></td>
	</tr>
	<tr height="40px">
		<td width="33%" id="1"><input type="radio" name="cboTypeBill" value="1" id="1" onClick="chkBillType(this)"><label for="1">Issue invoice &amp; Booking Item</label></td>
		<td width="33%" id="2"><input type="radio" name="cboTypeBill" value="2" id="2" onClick="chkBillType(this)"><label for="2">Issue invoice only</label></td>
		<td width="33%" id="3"><input type="radio" name="cboTypeBill" value="3" id="3" onClick="chkBillType(this)"><label for="3">Issue invoice &amp; Linked item from sales report</label></td>
	</tr>
</table><br />
<?php
require_once APP_DIR . "_include/billing/tpl_input_billing_1.php"; 
?>
</form>
<div align='right'>
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkform(window.document.frmInsert)'><img src="../../_images/icon/next.gif" width="15px" align="middle" alt="Go to next step"> &nbsp; Next step</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_billing_step_1.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel billing"> &nbsp; Cancel billing</button>
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