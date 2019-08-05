<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*
* $Id: input_account.php,v 1.3 2008/08/12 03:34:22 neki Exp $
*/
//REQUIRE
require_once "../../zk_config.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "input_account.php";

//PROCESS FORM
require_once "tpl_process_form.php";
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
          	<!--START: BODY-->
<strong class="title">[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] ADD NEW ACCOUNT<br /><br /></strong>
<form method="post" action="" name="frmAdd">
<input type="hidden" name="p_mode" value="insert_account">
<table class="table_a" width="100%">
	<tr>
		<th width="100">ACCOUNT</th>
		<td><input type="text" name="txtAccount" class="req" maxlength="16">
		<span class="comment">*Length : 4 - 16 character, Must be unique</span></td>
		<th width="100">REG/ DATE</th>
		<td><input type="text" name="txtRegDate" class="req" value="<?php echo date("Y-m-d")?>" size="15" readonly>
	</tr>
	<tr>
		<th>USER NAME</th>
		<td colspan="3"><input type="text" name="txtUsrName" class="req" maxlength="32" size="40">
		<span class="comment">*Within 64 characters</span></td>
	</tr>
	<tr>
		<th>REMARK</th>
		<td colspan="3">
			<textarea name="txtRemark" rows="4" cols="60"></textarea>
			<span class="comment">*Within 255 characters</span>
		</td>
	</tr>
</table>
</form>
<p align='center'>
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='if(verify(document.frmAdd)){document.frmAdd.submit()}'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save account"> &nbsp; Save account</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_account.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel add new account"> &nbsp; Cancel</button>
</p>
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