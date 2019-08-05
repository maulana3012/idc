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
ckperm(ZKP_SELECT, HTTP_DIR . $currentDept . "/billing/index.php");

//GLOBAL
$left_loc = "input_deposit.php";

//PROCESS INSERT PAYMENT
if (ckperm(ZKP_INSERT, HTTP_DIR . $currentDept . "/billing/index.php", 'insert')) {

	$_dept				= $_POST['_dept'];
	$_cus_code	  		= strtoupper($_POST['_cus_code']);
	$_cus_name	  		= $_POST['_cus_name'];
	$_payment_date		= $_POST['_payment_date'];
	$_payment_paid		= $_POST['_payment_paid'];
	$_payment_method	= $_POST['_method'];
	$_payment_bank		= empty($_POST['_bank']) ? '' : $_POST['_bank'];
	$_payment_remark	= $_POST['_payment_remark'];
	$_inputed_by		= $S->getValue("ma_account");

	$result = executeSP(
		ZKP_SQL."_addNewDeposit",
		"$\${$_dept}$\$",
		"$\${$_cus_code}$\$",
		"$\${$_cus_name}$\$",
		"$\${$_payment_date}$\$",
		$_payment_paid,
		"$\${$_payment_method}$\$",
		"$\${$_payment_bank}$\$",
		"$\${$_payment_remark}$\$",
		"$\${$_inputed_by}$\$"
	);

	if (isZKError($result))
		$M->goErrorPage($result, HTTP_DIR . $currentDept . "/billing/input_deposit.php");

	$M->goPage(HTTP_DIR . $currentDept . "/summary/list_deposit.php?_cus_code=$_cus_code");
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
function checkForm(oForm) {
	if(oForm._method[2].checked == true) {
		var j = false;
		for(var i=0;i < 6;i++) {
			if(oForm._bank[i].checked == true) {
				var j = true;
			}
		}
		if (j == false) {
			alert('You have to choose the bank');
			return;
		}
	}

	if(verify(oForm)){
		if(confirm("Are you sure to save deposit?")) {
			oForm.submit();
		}
	}
}

function fillCustomer(code) {

	alert("To input new deposit, please choose Customer Bill To");

	var x = (screen.availWidth - 400) / 2;
	var y = (screen.availHeight - 600) / 2;
	var dept = window.document.frmDeposit._dept.value;

	var win = window.open(
		'../../_include/billing/p_list_cus_code.php?_dept='+dept+'&_check_code='+ code,
		'deposit',
		'scrollbars,width=400,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

function enabledBankPayment(o, method){
	var f = window.document.frmDeposit;

	if (o.checked == true) {
		if(method == 'transfer') {
			f._bank[0].disabled = false;
			f._bank[1].disabled = false;
			f._bank[2].disabled = false;
			f._bank[3].disabled = false;
			f._bank[4].disabled = false;
			f._bank[5].disabled = false;
			f._bank[6].disabled = false;
		} else if(method == 'check' || method == 'giro') {
			f._bank[0].disabled = true;
			f._bank[1].disabled = true;
			f._bank[2].disabled = true;
			f._bank[3].disabled = true;
			f._bank[4].disabled = false;
			f._bank[5].disabled = false;
			f._bank[6].disabled = false;
			f._bank[6].checked	= true;
		} else {
			f._bank[0].disabled   = true;
			f._bank[1].disabled   = true;
			f._bank[2].disabled   = true;
			f._bank[3].disabled   = true;
			f._bank[4].disabled   = true;
			f._bank[5].disabled   = true;
			f._bank[6].disabled   = true;
			f._bank[0].checked	  = false;
			f._bank[1].checked	  = false;
			f._bank[2].checked	  = false;
			f._bank[3].checked	  = false;
			f._bank[4].checked	  = false;
			f._bank[5].checked	  = false;
			f._bank[6].checked	  = false;
		}
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
<strong style="font-size:18px;font-weight:bold">
[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] NEW DEPOSIT<br />
</strong>
<small class="comment">* Input balance as deposit for related customer</small>
<hr><br />
<form name="frmDeposit" method="post">
<input type="hidden" name="p_mode" value="insert">
<input type="hidden" name="_dept" value="<?php echo $department ?>">
<table width="80%" class="table_box">
	<tr>
		<th width="20%">CUSTOMER CODE</th>
		<td width="25%"><input type="text" class="req" name="_cus_code" size="5" maxlength="7" onClick="fillCustomer('')" readonly></td>
		<th width="20%">CUSTOMER NAME</th>
		<td><input type="text" class="req" name="_cus_name" style="width:100%" maxlength="128"></td>
	</tr>
	<tr>
		<th>PAYMENT DATE</th>
		<td><input type="text" class="reqd" name="_payment_date" value="<?php echo date("j-M-Y")?>"></td>
		<th>AMOUNT (Rp)</th>
		<td><input type="text" class="reqn" name="_payment_paid" onKeyUp="formatNumber(this,'dot')"></td>
	</tr>
	<tr>
		<th>METHOD</th>
		<td colspan="3">
			<input type="radio" name="_method" value="cash" onClick="enabledBankPayment(this, 'cash')" checked>Cash &nbsp;
			<input type="radio" name="_method" value="check" onClick="enabledBankPayment(this, 'check')">Check &nbsp;
			<input type="radio" name="_method" value="transfer" onClick="enabledBankPayment(this, 'transfer')">Transfer &nbsp;
			<input type="radio" name="_method" value="giro" onClick="enabledBankPayment(this, 'giro')">Giro &nbsp;
		</td>
	</tr>
	<tr>
		<th>BANK</th>
		<td colspan="3">
			<input type="radio" name="_bank" value="BCA1" disabled>BCA 1 &nbsp;
			<input type="radio" name="_bank" value="BCA2" disabled>BCA 2 &nbsp;
			<input type="radio" name="_bank" value="MANDIRI" disabled>Mandiri &nbsp;
			<input type="radio" name="_bank" value="BII1" disabled>BII 1 &nbsp;
			<input type="radio" name="_bank" value="BII2" disabled>BII 2 &nbsp;
			<input type="radio" name="_bank" value="DANAMON" disabled>Danamon &nbsp;
			<input type="radio" name="_bank" value="BNIS" disabled>BNI Syariah &nbsp;
		</td>
	</tr>
	<tr>
		<th width="15%">REMARK</th>
		<td colspan="3"><input type="text" name="_payment_remark" class="fmt" style="width:100%" maxlength="255"></td>
	</tr>
</table>
</form>
<div align="center">
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkForm(window.document.frmDeposit)'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save deposit"> &nbsp; Save deposit</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_deposit.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel deposit"> &nbsp; Cancel deposit</button>
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