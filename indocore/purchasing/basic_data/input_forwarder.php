<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*
* $Id: input_forwarder.php,v 1.1 2008/04/28 06:52:13 neki Exp $
*/
//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_INSERT, HTTP_DIR .  "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "input_forwarder.php";

//INSERT PROCESS 
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/input_forwarder.php", 'insert')) {
		
	$_code				= strtoupper($_POST['_code']);
	$_full_name			= $_POST['_full_name'];
	$_representative	= $_POST['_representative'];
	$_contact_name		= $_POST['_contact_name'];
	$_contact_phone		= $_POST['_contact_phone'];
	$_contact_email		= $_POST['_contact_email'];
	$_address			= $_POST['_address'];
	$_phone				= $_POST['_phone'];
	$_fax				= $_POST['_fax'];
	$_mobile_phone		= $_POST['_mobile_phone'];
	$_remark			= $_POST['_remark'];

	$result = executeSP(
		ZKP_SQL."_addNewForwarder",
		"$\${$_code}$\$",
		"$\${$_full_name}$\$",
		"$\${$_representative}$\$",
		"$\${$_contact_name}$\$",
		"$\${$_contact_phone}$\$",
		"$\${$_contact_email}$\$",
		"$\${$_address}$\$",
		"$\${$_phone}$\$",
		"$\${$_fax}$\$",
		"$\${$_mobile_phone}$\$",
		"$\${$_remark}$\$"
	);

	if (isZKError($result)) {
		$errMessage = $result->getMessage();
		if(strpos($errMessage, "duplicate key violates")) {
			$result = new ZKError(
				"DUPLICATE_CODE_EXIST",
				"DUPLICATE_CODE_EXIST",
				"The code : <strong>$_code</strong> already exist. please, use different code");
		}
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/input_forwarder.php");
	}
	else $M->goPage(HTTP_DIR . "$currentDept/$moduleDept/list_forwarder.php");
}
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
<h4>[<font color="blue"><?php echo strtoupper($currentDept) ?></font>] NEW FORWARDER</h4>
<form name="frmNew" method="post">
<input type="hidden" name="p_mode" value="insert">
<table width="100%" class="table_a">
	<tr>
		<th colspan="4"><strong>BASIC FORWARDER INFORMATION</strong></th>
	</tr>
	<tr>
		<th width="15%">CODE</th>
		<td width="41%"><input name="_code" type="text" class="req" size="4" maxlength="4"> 
		  <span class="comment">* 4 Character with unique</span></td>
		<th width="15%">FULL NAME</th>
		<td width="29%"><input name="_full_name" type="text" class="req" style="width:100%" maxlength="128"></td>
	</tr>
	<tr>
		<th>PRESIDENT DIRECTOR</th>
		<td colspan="3"><input name="_representative" type="text" class="fmt" size="50" maxlength="128"></td>
	</tr>
	<tr>
		<th width="15%">CONTACT NAME</th>
		<td width="41%"><input name="_contact_name" type="text" class="fmt" size="25" maxlength="128"></td>
		<th width="15%">CONTACT PHONE</th>
		<td width="29%"><input name="_contact_phone" type="text" class="fmt" size="25" maxlength="32"></td>
	</tr>
	<tr>
		<th>CONTACT E-MAIL</th>
		<td><input name="_contact_email" type="text" class="fmt" size="60" maxlength="64"></td>
		<th>MOBILE PHONE</th>
		<td><input name="_mobile_phone" type="text" class="fmt" maxlength="32"></td>
	</tr>
	<tr>
		<th>OFFICE ADDRESS</th>
		<td colspan="3"><textarea name="_address" cols="55" rows="4"></textarea></td>
	</tr>
	<tr>
		<th>OFFICE PHONE</th>
		<td><input name="_phone" type="text" class="fmt" maxlength="32"></td>
		<th>OFFICE FAX</th>
		<td><input name="_fax" type="text" class="fmt" maxlength="32"></td>
	</tr>
	<tr>
		<th>REMARKS</th>
		<td colspan="3"><textarea name="_remark" style="width:100%" rows="2"></textarea></td>
	</tr>
</table>
</form>
<p align="center">
	<button name='btnSave' class='input_btn' style='width:150px;'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle"> &nbsp; Save Forwarder</button>&nbsp;
	<button name='btnList' class='input_btn' style='width:150px;'><img src="../../_images/icon/list.gif" width="20px" align="middle"> &nbsp; List Forwarder</button>
</p>
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmNew;

	window.document.all.btnSave.onclick = function() {
		if(confirm("Are you sure to save forwarder?")) {
			if(verify(oForm)){
				oForm.submit();
			}
		}
	}
	
	window.document.all.btnList.onclick = function() {
		window.location.href = 'list_forwarder.php';
	}
</script>
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