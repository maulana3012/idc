<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*
* $Id: my_config.php,v 1.2 2008/07/01 02:15:18 neki Exp $
*/

require_once "../../zk_config.php";
require_once APP_DIR . "../_lib/zk_dbconn.php";

//GLOBAL
$left_loc = "my_config.php?ma_idx={$S->getValue('ma_idx')}";

if(isZKError($result = $S->isLogin())) {
    $M->goErrorPage($result, LOGIN_PAGE);
}

$ma_idx = $_REQUEST['ma_idx'];

//DEFAULT PROCESS ========================================================
$sql = "SELECT *,ma_idx, ma_account FROM tb_mbracc WHERE ma_idx = " . $_GET['ma_idx'];

if(isZKError($result =& query($sql))) {
	$M->goErrorPage($result, HTTP_DIR . "admin/main/index.php");
}

if (numQueryRows($result) <= 0) {
	$o = new ZKError(
		"ACCOUNT NOT FOUND",
		"ACCOUNT NOT FOUND",
		"Cannot find the account. Account ID is " . $_GET['ma_idx']);
	$M->goErrorPage($o, HTTP_DIR . "admin/main/index.php");
}

$data = fetchRowAssoc($result);

//get password valid until
if (!empty($data['ma_lastpasswdchangedate'])) {
	$result = executeSP("getValidPasswordPeriod", "$\${$data['ma_lastpasswdchangedate']}$\$");
} else {
	$data['ma_lastpasswdchangedate'] = "Waiting user login";
}
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_URL][0][1] ?></title>
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
<!--START BODY-->
<h4>[<font color="blue">ADMIN</font>] BASIC INFORMATION</h4>
<form method="post" action="" name="frmAdd">
<input type="hidden" name="p_mode" value="update">
<input type="hidden" name="ma_idx" value="<?php echo $_GET['ma_idx']?>">
<table class="table_a" width="100%">
	<tr>
		<th width="100">ACCOUNT</th>
		<td><input type="text" name="txtAccount" maxlength="16" class="input_sky" value="<?php echo $data['ma_account']?>" readonly> <span class="comment">*ReadOnly</span></td>
		<th width="100">REG/ DATE</th>
		<td><input type="text" name="txtRegDate" class="input_sky" value="<?php echo $data['ma_regdate']?>" size="25" readonly> <span class="comment">*ReadOnly</span>
	</tr>
	<tr>
		<th>USER NAME</th>
		<td colspan="3"><input type="text" name="txtUsrName" class="input_sky" maxlength="32" size="40" value="<?php echo $data['ma_displayname']?>" readonly> <span class="comment">*ReadOnly</span></td>
	</tr>
	<tr>
		<th>REMARK</th>
		<td colspan="3">
			<textarea name="txtRemark" rows="4" cols="60"><?php echo $data['ma_remark']?></textarea>&nbsp;
			<button name="btnSave" onClick="if(verify(document.frmAdd)){document.frmAdd.submit()}" class="input_sky">UPDATE REMARK</button>
		</td>
	</tr>
</table>
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