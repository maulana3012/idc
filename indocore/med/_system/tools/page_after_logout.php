<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*
*
* $Id: page_after_logout.php,v 1.1 2008/04/28 06:52:13 neki Exp $
*/

require_once "../../zk_config.php";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Logout</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link rel="stylesheet" href="../../_script/aden.css" type="text/css">
<script language="JavaScript" type="text/javascript" src="../../_script/aden.js"></script>
</head>
<body topmargin="0" leftmargin="0" onLoad="window.btnConfirm.focus()">
<table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#9CBECC">
    <tr>
        <td colspan="2">
<?php require_once APP_DIR . "_include/tpl_header.php"?>
        </td>
    </tr>
    <tr>
        <td style="padding:5" width="14%" bgcolor="#F0F5F6" align="center">&nbsp;
<?php
//LOGIN
//require_once $login__include;
?>
        </td>
        <td style="padding:10 10 0 10" width="*" valign="bottom">
<?php
//Top Menu
require_once APP_DIR . "_include/tpl_topMenu.php"
?>
        </td>
    </tr>
    <tr>
        <td width="10%" align="center" valign="top" style="padding:0 0 0 10" bgcolor="#F0F5F6" height="440" width="140"> &nbsp;
<?php
//LEFT
//require_once "_leftMenu.php";
?>
		</td>
        <td width="*" style="padding:0 3 3 3">
            <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
                <tr>
                                     <td style="padding:10" height="510" valign="top">
<!--START BODY-->
<br><br><br>
<table class="table_g" width="60%" align="center">
	<tr>
		<th align="center" height="22"><b>Information<b></th>
	</tr>
	<tr>
		<td colspan="2" height="40" align="center">You've just logged out successfully</td>
	</tr>
</table>
<br>
<table class="table_no" width="60%" align="center">
	<tr>
		<td align="center">
			<input type="button" onClick="window.location.href='<?php echo (empty($_GET['returnUrl'])) ? MAIN_PAGE : $_GET['returnUrl']?>'" name="btnConfirm" value="Confirm" class="input_sky"></td>
	</tr>
</table>
<!--END BODY-->
                    </td>
                 </tr>
             </table>
        </td>
    </tr>
    <tr>
        <td style="padding:5 10 5 10" colspan="2" bgcolor="#FFFFFF">
<?php require_once APP_DIR . "_include/tpl_footer.php"?>
        </td>
    </tr>
</table>
</body>
</html>