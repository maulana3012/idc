<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*
* $Id: detail_supplier.php,v 1.1 2008/04/28 06:52:13 neki Exp $
*/
//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "list_supplier.php";
$_code	  = (isset($_GET['_code'])) ? $_GET['_code'] : goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");

//PROCESS DELETE BILLING
if (ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'delete')) {

	$result =& query("DELETE FROM ".ZKP_SQL."_tb_supplier WHERE sp_code = '$_code'");

	if(isZKError($result))
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/detail_supplier.php?_code=".urlencode($_code));
	goPage(HTTP_DIR ."$currentDept/$moduleDept/list_supplier.php");
}

//UPDATE PROCESS 
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'update')) {
		
	$_code				= strtoupper($_POST['_code']);
	$_name				= $_POST['_name'];
	$_full_name			= $_POST['_full_name'];
	$_representative	= $_POST['_representative'];
	$_contact_name		= $_POST['_contact_name'];
	$_contact_phone		= $_POST['_contact_phone'];
	$_contact_email		= $_POST['_contact_email'];
	$_attn				= $_POST['_attn'];
	$_cc				= $_POST['_cc'];
	$_address			= $_POST['_address'];
	$_phone				= $_POST['_phone'];
	$_fax				= $_POST['_fax'];
	$_remark			= $_POST['_remark'];
	$_bank_name			= $_POST['_bank_name'];
	$_bank_currency		= $_POST['_bank_currency'];
	$_bank_swift		= $_POST['_bank_swift'];
	$_bank_address		= $_POST['_bank_address'];
	$_bank_acc_no		= $_POST['_bank_acc_no'];
	$_bank_acc_name		= $_POST['_bank_acc_name'];

	$result = executeSP(
		ZKP_SQL."_updateSupplier",
		"$\${$_code}$\$",
		"$\${$_name}$\$",
		"$\${$_full_name}$\$",
		"$\${$_representative}$\$",
		"$\${$_contact_name}$\$",
		"$\${$_contact_phone}$\$",
		"$\${$_contact_email}$\$",
		"$\${$_attn}$\$",
		"$\${$_cc}$\$",
		"$\${$_address}$\$",
		"$\${$_phone}$\$",
		"$\${$_fax}$\$",
		"$\${$_remark}$\$",
		"$\${$_bank_name}$\$",
		"$\${$_bank_swift}$\$",
		"$\${$_bank_address}$\$",
		"$\${$_bank_acc_no}$\$",
		"$\${$_bank_currency}$\$",
		"$\${$_bank_acc_name}$\$"
	);

	if (isZKError($result)) 
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/detail_supplier.php?_code=$_code");
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_supplier.php?_code=$_code");
}

//DEFAULT PROCESS
$sql = "SELECT * FROM ".ZKP_SQL."_tb_supplier WHERE sp_code = '$_code'";
if(isZKError($result =& query($sql)))
	$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");

$column =& fetchRowAssoc($result);
?>
<html>
<head>
<title>PT. INDOCORE PERKASA [APOTIK MANAGEMENT SYSTEM]</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
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
<h4>[<font color="blue"><?php echo strtoupper($currentDept) ?></font>] SUPPLIER DETAIL</h4>
<form name="frmUpdate" method="post">
<input type="hidden" name="p_mode">
<table width="100%" class="table_a">
	<tr>
		<th colspan="4"><strong>BASIC SUPPLIER INFORMATION</strong></th>
	</tr>
	<tr>
		<th width="15%">CODE</th>
		<td width="41%"><input name="_code" type="text" class="req" size="4" maxlength="4" value="<?php echo $column['sp_code'] ?>" readonly> 
		  <span class="comment">* 4 Character with unique</span></td>
		<th width="15%">INTERNAL NAME</th>
		<td width="29%"><input name="_name" type="text" class="req" size="25" maxlength="128" value="<?php echo $column['sp_name'] ?>"></td>
	</tr>
	<tr>
		<th>COMPANY FULL NAME</th>
		<td colspan="3"><input name="_full_name" type="text" class="req" size="50" maxlength="128" value="<?php echo $column['sp_full_name'] ?>"> 
		<span class="comment">* For formal purpose</span></td>
	</tr>
	<tr>
		<th>PRESIDENT DIRECTOR</th>
		<td colspan="3"><input name="_representative" type="text" class="fmt" size="50" maxlength="128" value="<?php echo $column['sp_representative'] ?>"></td>
	</tr>
	<tr>
		<th width="15%">CONTACT NAME</th>
		<td width="41%"><input name="_contact_name" type="text" class="fmt" size="25" maxlength="128" value="<?php echo $column['sp_contact_name'] ?>"></td>
		<th width="15%">CONTACT PHONE</th>
		<td width="29%"><input name="_contact_phone" type="text" class="fmt" size="25" maxlength="32" value="<?php echo $column['sp_contact_phone'] ?>"></td>
	</tr>
	<tr>
		<th>CONTACT E-MAIL</th>
		<td colspan="3"><input name="_contact_email" type="text" class="fmt" size="60" maxlength="64" value="<?php echo $column['sp_contact_email'] ?>"></td>
	</tr>
	<tr>
		<th>ATTN</th>
		<td><input name="_attn" type="text" class="fmt" size="25" maxlength="32" value="<?php echo $column['sp_contact_attn'] ?>"></td>
		<th>CC</th>
		<td><input name="_cc" type="text" class="fmt" size="25" maxlength="32" value="<?php echo $column['sp_contact_cc'] ?>"></td>
	</tr>
	<tr>
		<th>OFFICE ADDRESS</th>
		<td colspan="3"><textarea name="_address" cols="55" rows="4"><?php echo $column['sp_address'] ?></textarea></td>
	</tr>
	<tr>
		<th>OFFICE PHONE</th>
		<td><input name="_phone" type="text" class="fmt" maxlength="32" value="<?php echo $column['sp_phone'] ?>"></td>
		<th>OFFICE FAX</th>
		<td><input name="_fax" type="text" class="fmt" maxlength="32" value="<?php echo $column['sp_fax'] ?>"></td>
	</tr>
	<tr>
		<th>REMARKS</th>
		<td colspan="3"><textarea name="_remark" style="width:100%" rows="2"><?php echo $column['sp_remark'] ?></textarea></td>
	</tr>
	<tr>
		<th colspan="4"><strong>BANK REFERENCE</strong></th>
	</tr>
	<tr>
		<th>BANK NAME</th>
		<td><input name="_bank_name" type="text" class="fmt" maxlength="128" value="<?php echo $column['sp_bank_name'] ?>"></td>
		<th> SWIFT CODE</th>
		<td><input name="_bank_swift" type="text" class="fmt" maxlength="16" value="<?php echo $column['sp_bank_swift_code'] ?>"></td>
	</tr>
	<tr>
		<th>BANK ADDRESS</th>
		<td colspan="3"><textarea name="_bank_address" cols="55" rows="4"><?php echo $column['sp_bank_address'] ?></textarea></td>
	</tr>
	<tr>
		<th> ACCOUNT NO</th>
		<td><input name="_bank_acc_no" type="text" class="fmt" maxlength="128" value="<?php echo $column['sp_bank_account_no'] ?>"> 
		CURRENCY <input name="_bank_currency" type="text" class="fmt"" size="3" maxlength="3" value="<?php echo $column['sp_bank_currency'] ?>"></td>
		<th>ACCOUNT NAME</th>
		<td><input name="_bank_acc_name" type="text" class="fmt" value="<?php echo $column['sp_bank_account_name'] ?>"></td>
	</tr>
</table>
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td>
			<button name='btnDelete' class='input_red' style='width:150px;'><img src="../../_images/icon/trash.gif" width="15px" align="middle"> &nbsp; Delete Supplier</button>
		</td>
		<td align="right">
			<button name='btnUpdate' class='input_btn' style='width:80px;'><img src="../../_images/icon/update.gif" width="20px" align="middle"> &nbsp; Update</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle"> &nbsp; Go to summary</button>
		</td>
</table>
<!--START Button-->
<script language="javascript" type="text/javascript">
	var oForm = window.document.frmUpdate;

	window.document.all.btnDelete.onclick = function() {
		if(confirm("Are you sure to delete supplier?")) {
			oForm.p_mode.value = 'delete';
			oForm.submit();
		}
	}

	window.document.all.btnUpdate.onclick = function() {
		if(confirm("Are you sure to update supplier?")) {
			if(verify(oForm)){
				oForm.p_mode.value = 'update';
				oForm.submit();
			}
		}
	}

	window.document.all.btnList.onclick = function() {
		window.location.href = '<?php echo HTTP_DIR . "$currentDept/$moduleDept/list_supplier.php" ?>';
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