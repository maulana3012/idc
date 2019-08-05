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
$left_loc = 'list_deposit.php';
if (!isset($_GET['_code']) || $_GET['_code'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else {
	$_code = urldecode($_GET['_code']);
}

//PROCESS DELETE DEPOSIT
if (ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'delete')) {

	$_code = $_POST['_code'];
	$_cus_code = $_POST['_cus_code'];

	$result =& query("DELETE FROM ".ZKP_SQL."_tb_deposit WHERE dep_idx = '$_code'");

	if(isZKError($result))
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/detail_deposit.php?_code=".urlencode($_code));
	goPage(HTTP_DIR . "$currentDept/summary/list_deposit.php?_cus_code=".$_cus_code);
}

//PROCESS UPDATE DEPOSIT
if (ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'update')) {

	$_code				= $_POST['_code'];
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
		ZKP_SQL."_updateDeposit",
		$_code,
		"$\${$_cus_name}$\$",
		"$\${$_payment_date}$\$",
		$_payment_paid,
		"$\${$_payment_method}$\$",
		"$\${$_payment_bank}$\$",
		"$\${$_payment_remark}$\$",
		"$\${$_inputed_by}$\$"
	);

	if (isZKError($result))
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");

	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_deposit.php?_code=$_code");
}

//DEFAULT PROCESS
$sql = "SELECT *, ".ZKP_SQL."_isDepositUsed(cus_code, dep_issued_date, dep_method, dep_bank) AS deposit_used FROM ".ZKP_SQL."_tb_deposit WHERE dep_idx = '$_code'";
if(isZKError($result =& query($sql)))
	$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");

$column =& fetchRowAssoc($result);
if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
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
function enabledBankPayment(o, method){
	var f = window.document.frmDeposit;

	if (o.checked == true) {
		if(method == 'transfer') {
			for(i=0; i<7; i++) {
				f._bank[i].disabled = false;
			}
			f._bank[6].checked	= true;
		} else if(method == 'check' || method == 'giro') {
			for(i=0; i<7; i++) {
				if(i<4) { f._bank[i].disabled = true; }
				else	{ f._bank[i].disabled = false; }
			}
			f._bank[6].checked	= true;
		} else {
			for(i=0; i<7; i++) {
				f._bank[i].disabled   = true;
				f._bank[i].checked	  = false;
			}
		}
	} 
}

function initPage() {
	var f = window.document.frmDeposit;

	if(f._method[2].checked) {
		for(i=0; i<7; i++) {
			f._bank[i].disabled = false;
		}
	} else if(f._method[1].checked || f._method[3].checked) {
		for(i=0; i<7; i++) {
			if(i<4) { f._bank[i].disabled = true; }
			else	{ f._bank[i].disabled = false; }
		}
	} else {
		for(i=0; i<7; i++) {
			f._bank[i].disabled   = true;
			f._bank[i].checked	  = false;
		}
	}

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
<strong style="font-size:18px;font-weight:bold">
[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] DETAIL DEPOSIT<br />
</strong>
<small class="comment">* Balance deposit for related customer</small>
<hr>
<form name="frmDeposit" method="post">
<input type="hidden" name="p_mode">
<input type="hidden" name="_code" value="<?php echo $column['dep_idx'] ?>">
<input type="hidden" name="_dept" value="<?php echo $column['dep_dept'] ?>">
<table width="80%" class="table_box">
	<tr>
		<td colspan="4" align="right"><span class="comment"><i>Last updated by : <?php echo $column['dep_updated_by_account'].date(', d-M-Y g:i:s', strtotime($column['dep_updated_timestamp'])) ?></i></span></td>
	</tr>
	<tr>
		<th width="20%">CUSTOMER CODE</th>
		<td width="25%"><input type="text" class="req" name="_cus_code" size="5" maxlength="7" value="<?php echo $column['cus_code'] ?>" readonly></td>
		<th width="20%">CUSTOMER NAME</th>
		<td><input type="text" class="req" name="_cus_name" style="width:100%" maxlength="128" value="<?php echo $column['dep_cus_name'] ?>"></td>
	</tr>
	<tr>
		<th>PAYMENT DATE</th>
		<td><input type="text" class="reqd" name="_payment_date" value="<?php echo date("j-M-Y", strtotime($column['dep_issued_date'])) ?>"></td>
		<th>AMOUNT (Rp)</th>
		<td><input type="text" class="reqn" name="_payment_paid" onKeyUp="formatNumber(this,'dot')" value="<?php echo number_format((double)$column['dep_amount']) ?>"></td>
	</tr>
	<tr>
		<th>METHOD</th>
		<td colspan="3">
			<input type="radio" name="_method" value="cash" onClick="enabledBankPayment(this, 'cash')" <?php echo (rtrim($column['dep_method']) == 'cash') ? 'checked' : '' ?>>Cash &nbsp;
			<input type="radio" name="_method" value="check" onClick="enabledBankPayment(this, 'check')" <?php echo (rtrim($column['dep_method']) == 'check') ? 'checked' : '' ?>>Check &nbsp;
			<input type="radio" name="_method" value="transfer" onClick="enabledBankPayment(this, 'transfer')" <?php echo (rtrim($column['dep_method']) == 'transfer') ? 'checked' : '' ?>>Transfer &nbsp;
			<input type="radio" name="_method" value="giro" onClick="enabledBankPayment(this, 'giro')" <?php echo (rtrim($column['dep_method']) == 'giro') ? 'checked' : '' ?>>Giro &nbsp;
		</td>
	</tr>
	<tr>
		<th>BANK</th>
		<td colspan="3">
			<input type="radio" name="_bank" value="BCA1" <?php echo (rtrim($column['dep_bank']) == 'BCA1') ? 'checked' : '' ?> disabled>BCA 1 &nbsp;
			<input type="radio" name="_bank" value="BCA2" <?php echo (rtrim($column['dep_bank']) == 'BCA2') ? 'checked' : '' ?> disabled>BCA 2 &nbsp;
			<input type="radio" name="_bank" value="MANDIRI" <?php echo (rtrim($column['dep_bank']) == 'MANDIRI') ? 'checked' : '' ?> disabled>Mandiri &nbsp;
			<input type="radio" name="_bank" value="BII1" <?php echo (rtrim($column['dep_bank']) == 'BII1') ? 'checked' : '' ?> disabled>BII 1 &nbsp;
			<input type="radio" name="_bank" value="BII2" <?php echo (rtrim($column['dep_bank']) == 'BII2') ? 'checked' : '' ?> disabled>BII 2 &nbsp;
			<input type="radio" name="_bank" value="DANAMON" <?php echo (rtrim($column['dep_bank']) == 'DANAMON') ? 'checked' : '' ?> disabled>Danamon &nbsp;
			<input type="radio" name="_bank" value="BNIS" <?php echo (rtrim($column['dep_bank']) == 'BNIS') ? 'checked' : '' ?> disabled>BNI Syariah &nbsp;
		</td>
	</tr>
	<tr>
		<th width="15%">REMARK</th>
		<td colspan="3"><input type="text" name="_payment_remark" class="fmt" style="width:100%" maxlength="255" value="<?php echo $column['dep_remark'] ?>"></td>
	</tr>
</table>
</form>
<table width="80%" cellpadding="2" cellspacing="0">
	<tr>
		<td>
			<button name='btnDelete' class='input_btn' style='width:120px;'><img src="../../_images/icon/trash.gif" width="15px" align="middle" alt="Delete deposit"> &nbsp; Delete deposit</button>
		</td>
		<td align="right">
			<button name='btnUpdate' class='input_btn' style='width:80px;'><img src="../../_images/icon/update.gif" width="20px" align="middle" alt="Update deposit"> &nbsp; Update</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
		</td>
	</tr>
</table><br />
<script language="javascript" type="text/javascript">

	//Define the form that you want to handle
	var oForm = window.document.frmDeposit;

	window.document.all.btnDelete.onclick = function() {
		<?php if ($column['deposit_used'] == 't') { ?>
		alert("If you want to delete this deposit,\nyou have to delete Payment which using this deposit first");
		window.document.all.btnDelete.disabled = true;
		return;
		<?php } ?>

		if(confirm("Are you sure to delete deposit?")) {
			oForm.p_mode.value = 'delete';
			oForm.submit();
		}
	}

	window.document.all.btnUpdate.onclick = function() {
		<?php
		if ($column['deposit_used'] == 't') {?>
			alert("If you want to update this deposit,\nyou have to delete Payment which using this deposit first");
			oForm._payment_paid.value = '<?php echo number_format((double)$column['dep_amount']) ?>';
			window.document.all.btnUpdate.disabled = true;
			return;
		<?php } ?>

		if(confirm("Are you sure to update?")) {
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
				oForm.p_mode.value = 'update';
				oForm.submit();
			}
		}
	}

	window.document.all.btnList.onclick = function() {
		window.location.href = '<?php echo HTTP_DIR . $currentDept . '/summary/list_deposit.php?_cus_code='.$column['cus_code'] ?>';
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