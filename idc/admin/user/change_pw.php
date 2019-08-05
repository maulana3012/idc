<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*
* $Id: change_pw.php,v 1.4 2008/07/01 02:15:18 neki Exp $
*/

require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//GLOBAL
$left_loc = "change_pw.php?ma_idx={$S->getValue('ma_idx')}";

//Update Password
if (isset($_POST['p_mode']) && $_POST['p_mode'] == 'update') {

	$ma_idx		= $_POST['ma_idx'];
	$txtOldPassword		= md5(trim($_POST['txtOldPassword']));
	$txtNewPassword		= md5(trim($_POST['txtNewPassword']));
	$txtReTypePassword	= md5(trim($_POST['txtReTypePassword']));

	$result = executeSP(
		"changePassword",
		$ma_idx, 
		"$\${$txtOldPassword}$\$", 
		"$\${$txtNewPassword}$\$");

	if (isZKError($result)) {
		$errMessage = $result->getMessage();
		$errNo = (int) substr($errMessage, -2); //Get error no at stored procedure

		switch ($errNo) {
			case 0 :
				$o = new ZKError("BLOCKED_ACCOUNT", "BLOCKED_ACCOUNT", "The account that you want to change password is still blocked. If you want to release this account, Please contact to administrator.");
				break;
			case 1 :
				$o = new ZKError("PASSWORD_EXPIRED", "PASSWORD_EXPIRED", "This password already expired. Please contact to administrator to re-issue the password");
				break;
			case 2 :
				$o = new ZKError("PASSWORD_EXPIRED", "TEMPORARY_PASSWORD_EXPIRED", "Issued password already expired. Please contact to administrator to re-issue the password. after received password, please change your password within 24 hours");
				break;
			case 3 :
				$o = new ZKError("PASSWORD_MISMATCH", "PASSWORD_MISMATCH", "Current password is now match. If you forget to your pasword, Please contact to administrator.");
				break;
			case 4 :
				$o = new ZKError("CANNOT_REUSE_OLDPASSWORD", "CANNOT_REUSE_OLDPASSWORD", "Your password must be different with before password. Please try again");
				break;
			default :
				$o =& $result;
		}
		
		$M->goErrorPage($o, HTTP_DIR . "/admin/user/change_pw.php?ma_idx=" . $ma_idx);

	//if password was changed sucessfully,
	} else {
		$S->logout();
		$o = new ZKError("INFORMATION", "Password was updated", "Your password was sucessfully changed. If you want to access the system. please login with new password.");
		$M->goErrorPage($o, LOGIN_PAGE);
	}
}

//DEFAULT PROCESS ========================================================
$sql = "SELECT * FROM tb_mbracc WHERE ma_idx = " . $_GET['ma_idx'];

if(isZKError($result =& query($sql))) {
	$M->goErrorPage($result, MAIN_PAGE);
}

if (numQueryRows($result) <= 0) {
	$o = new ZKError(
		"ACCOUNT NOT FOUND",
		"ACCOUNT NOT FOUND",
		"Cannot find the account. Account ID is " . $_GET['ma_idx']);
	$M->goErrorPage($o, MAIN_PAGE);
}

$data = fetchRowAssoc($result);
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_URL][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
function updatePassword() {
	var oldpw	= window.document.frmEdit.txtOldPassword;
	var pw1		= window.document.frmEdit.txtNewPassword;
	var pw2		= window.document.frmEdit.txtReTypePassword;

	if (oldpw.value.length == 0) {
		oldpw.focus();
		alert("You must suply the password that you have.");
	} else if (pw1.value.length <= 5 || pw1.value.length > 16) {
		pw1.value = "";
		pw2.value = "";
		pw1.focus();
		alert("Password must be 6 to 16 character.");
	} else if(pw1.value != pw2.value) {
		pw1.value = "";
		pw2.value = "";
		pw1.focus();
		alert("Password that you input is not match");
	} else {
		window.document.frmEdit.submit();
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
<!--START BODY-->
<h4>[<font color="blue">ADMIN</font>] CHANGE PASSWORD</h4>
<form method="post" action="" name="frmEdit">
<input type="hidden" name="p_mode" value="update">
<input type="hidden" name="ma_idx" value="<?php echo $_GET['ma_idx']?>">
<table class="table_a" width="80%">
	<tr>
		<th width="120">ACCOUNT</th>
		<td><?php echo $data['ma_account']?></td>
	</tr>
	<tr>
		<th>USER NAME</th>
		<td><?php echo $data['ma_displayname']?></td>
	</tr>
	<tr>
		<th>Current Password</th>
		<td><input type="password" name="txtOldPassword" class="req" maxlength="16"><br />
		<span class="comment">*If you get password from administrator, Please input the password you have.</span>
		</td>
	</tr>
	<tr>
		<th>New Password</th>
		<td><input type="password" name="txtNewPassword" class="req" maxlength="16"> 
			<span class="comment">*Password must be 6-16 charachter.</span>
		</td>
	</tr>
	<tr>
		<th>Re-Type</th>
		<td><input type="password" name="txtReTypePassword" class="req" maxlength="16">
		<span class="comment">* re-type your new password to confirm.</span></td>
	</tr>
</table>
<p align="center">
	<button name='btnSave' class='input_sky' style='width:60px;height:30px' onClick="updatePassword()"><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Update new password"></button>&nbsp;
</p>
</form>
  <!--END BODY-->
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