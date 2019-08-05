<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*
* $Id: input_supplier.php,v 1.1 2008/04/28 06:52:13 neki Exp $
*/
//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "input_supplier.php";

//INSERT PROCESS 
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/input_supplier.php", 'insert')) {
		
	$_code				= strtoupper($_POST['_code']);
	$_internal_name		= $_POST['_internal_name'];
	$_full_name			= $_POST['_full_name'];
	$_contact_name		= $_POST['_contact_name'];
	$_contact_position	= $_POST['_contact_position'];
	$_contact_phone		= $_POST['_contact_phone'];
	$_contact_hphone	= $_POST['_contact_hphone'];
	$_contact_email		= $_POST['_contact_email'];
	$_phone				= $_POST['_phone'];
	$_fax				= $_POST['_fax'];
	$_address			= $_POST['_address'];

	$result = executeSP(
		ZKP_SQL."_insertLocalSupplier",
		"$\${$_code}$\$",
		"$\${$_internal_name}$\$",
		"$\${$_full_name}$\$",
		"$\${$_contact_name}$\$",
		"$\${$_contact_position}$\$",
		"$\${$_contact_phone}$\$",
		"$\${$_contact_hphone}$\$",
		"$\${$_contact_email}$\$",
		"$\${$_phone}$\$",
		"$\${$_fax}$\$",
		"$\${$_address}$\$"
	);

	if (isZKError($result)) {
		$errMessage = $result->getMessage();
		if(strpos($errMessage, "duplicate key violates")) {
			$result = new ZKError(
				"DUPLICATE_CODE_EXIST",
				"DUPLICATE_CODE_EXIST",
				"The code : <strong>$_code</strong> already exist. please, use different code");
		}
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/input_supplier.php");
	}
	else $M->goPage(HTTP_DIR . "$currentDept/$moduleDept/list_supplier.php");
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
<h3>[<font color="blue"><?php echo strtoupper($currentDept) ?></font>] NEW SUPPLIER</h3>
<form name="frmNew" method="post">
<input type="hidden" name="p_mode" value="insert">
<table width="100%" class="table_a">
	<tr>
		<th colspan="4"><strong>BASIC INFORMATION</strong></th>
	</tr>
	<tr>
		<th width="15%">CODE</th>
		<td width="40%"><input name="_code" type="text" class="req" style="width:10%" maxlength="2"> 
		  <span class="comment">* 2 Character with unique</span></td>
		<th width="15%">INTERNAL NAME</th>
		<td><input name="_internal_name" type="text" class="req" size="25" maxlength="128"></td>
	</tr>
	<tr>
		<th>FULL NAME</th>
		<td colspan="3"><input name="_full_name" type="text" class="req" size="80" maxlength="128"> 
		<span class="comment">* For formal purpose</span></td>
	</tr>
	<tr>
		<th colspan="4"><strong>CONTACT INFORMATION</strong></th>
	</tr>
	<tr>
		<th>NAME</th>
		<td colspan="3">
			<input name="_contact_name" type="text" class="fmt" size="50" maxlength="128">
			&nbsp; <span class="comment">Position  :</span> <input name="_contact_position" type="text" class="fmt" size="25" maxlength="32">
		</td>
	</tr>
	<tr>
		<th>CONTACT</th>
		<td colspan="3">
			<span class="comment">
			Phone : <input name="_contact_phone" type="text" class="fmt" size="20" maxlength="32"> &nbsp; &nbsp;
			HP : <input name="_contact_hphone" type="text" class="fmt" size="20" maxlength="32"> &nbsp; &nbsp;
			Email : <input name="_contact_email" type="text" class="fmt" size="35" maxlength="64"> &nbsp; &nbsp;
			</span>
		</td>
	</tr>
		<th colspan="4"><strong>ADDRESS INFORMATION</strong></th>
	<tr>
		<th rowspan="2">ADDRESS</th>
		<td rowspan="2"><textarea name="_address" cols="75" rows="4"></textarea></td>
		<th>PHONE</th>
		<td><input name="_phone" type="text" class="fmt" size="20" maxlength="32"></td>
	</tr>
	<tr>
		<th>FAX</th>
		<td><input name="_fax" type="text" class="fmt" size="20" maxlength="32"></td>
	</tr>
</table>
</form>
<p align="center">
	<button name='btnSave' class='input_btn' style='width:150px;'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle"> &nbsp; Save Supplier</button>&nbsp;
	<button name='btnList' class='input_btn' style='width:150px;'><img src="../../_images/icon/list.gif" width="20px" align="middle"> &nbsp; List Supplier</button>
</p>
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmNew;

	window.document.all.btnSave.onclick = function() {
		if(verify(oForm)){
			if(confirm("Are you sure to save supplier?")) {
				oForm.submit();
			}
		}
	}
	
	window.document.all.btnList.onclick = function() {
		window.location.href = 'list_supplier.php';
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