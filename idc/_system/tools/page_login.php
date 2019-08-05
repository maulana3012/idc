<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*
*
* $Id: page_login.php,v 1.1 2008/04/28 06:52:13 neki Exp $
*/
require_once "../../zk_config.php";
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_URL][0][1] ?> - Login Page</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link rel="stylesheet" href="../../_script/aden.css" type="text/css">
<script language="JavaScript" type="text/javascript" src="../../_script/aden.js"></script>
<script language="JavaScript" type="text/javascript">
function login() {
	if(window.document.frmLogin.userid.value <= 0) {
		alert("Account Id must be entered!");
		window.document.frmLogin.userid.focus();
		return false;
	} else if(window.document.frmLogin.userpw.value <= 0) {
		alert("Password must be entered!");
		window.document.frmLogin.userpw.focus();
		return false;
	}

    if(verify(window.document.frmLogin)) {
        window.document.frmLogin.submit();
    }
}
</script>
</head>
<body onload="window.document.frmLogin.userid.focus();">
<table height="100%" width="100%" bgcolor="#C2D0DA">
	<tr>
		<td height="3%" colspan="3">
		</td>
	</tr>
	<tr>
		<td width="2%" rowspan="3"></td>
		<td width="90%" height="5%" bgcolor="#80A9BA"></td>
		<td width="2%" rowspan="3"></td>
	</tr>
	<tr>
		<td bgcolor="#FFFFFF" class="main" valign="top">
		<!-- START PRINT CONTENT -->
<br /><br /><br /><br /><br /><br /><br /><br />
<form method="post" name="frmLogin" id="frmLogin" onSubmit="verify(this);">
<input type="hidden" name="returnUrl" value="<?php echo MAIN_PAGE.'&showMsg=true'?>">
<table class="table_box" width="100%" align="left">
	<tr>
		<td rowspan="2" width="15%"></td>
		<td colspan="7"><span style="font-family:courier; font-size:15; font-weight:bold; color:#333333"><?php echo $cboFilter[0][ZKP_URL][0][1] ?></span></td>
	</tr>
	<tr>
		<td><img src="../../_images/icon/user.png"></td>
		<td width="15%"><input type="text" name="userid" class="reqan" style="width:100%"></td>
		<td width="2%"></td>
		<td><img src="../../_images/icon/pass.gif"></td>
		<td width="15%"><input name="userpw" type="password" class="req"  style="width:100%" onKeyPress="if(window.event.keyCode == 13) login();"></td>
		<td><button class="input_sky" onclick="javascript:login()">Login <img src="../../_images/icon/security.gif"></button></td>
		<td width="50%"></td>
	</tr>
</table><br />
</form>
		<!-- END PRINT CONTENT -->
		</td>
	</tr>
	<tr>
		<td height="5%" bgcolor="#80A9BA"></td>
	</tr>
	<tr>
		<td height="3%" colspan="3"><?php require_once APP_DIR . "_include/tpl_footer.php"?></td>
	</tr>
</table>
</body>
</html>