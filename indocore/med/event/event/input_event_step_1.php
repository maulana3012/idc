<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*/
//REQUIRE
require_once "../../zk_config.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "input_event_step_1.php";

//INPUT EVENT ===============================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'insert')) {

	$_nama_acara		= $_POST['_nama_acara'];
	$_tanggal_acara		= $_POST['_tanggal_acara'];
	$_tempat_acara		= $_POST['_tempat_acara'];
	$_nama_peyelenggara	= $_POST['_nama_peyelenggara'];

	$result = executeSP(
		ZKP_SQL."_insertEvent",
		"$\${$_nama_acara}$\$",
		"$\${$_tanggal_acara}$\$",
		"$\${$_tempat_acara}$\$",
		"$\${$_nama_peyelenggara}$\$"
	);

	if (isZKError($result)) {
		$errMessage = $result->getMessage();
		if(strpos($errMessage, "duplicate key violates")) {
			$result = new ZKError(
				"DUPLICATE_CODE_EXIST",
				"DUPLICATE_CODE_EXIST",
				"Same Code exists, please check your order code again");
		}
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/index.php");
	}
	$_code = $result[0];
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/input_event_step_2.php?_code=".$_code);
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
<script src="../../_include/billing/input_billing.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
function checkform(o) {

	var d = parseDate(o._tanggal_acara.value, 'prefer_euro_format');
	if(o._nama_acara.value.length <= 0) { alert("Nama acara harus diisi"); o._nama_acara.focus(); return; }
	else if(d == null) { alert("Tanggal acara harus diisi dengan format tanggal"); o._tanggal_acara.value=""; o._tanggal_acara.focus(); return; }
	o._tanggal_acara.value = formatDate(d, "d-NNN-yyyy");
	o.submit();

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
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode' value='insert'>
<div class="head-line">Input Event <i>(1 / 2)</i></div>
<table width="100%" class="table_box">
	<tr>
		<td rowspan="2" width="15%"><div class="i_line">Nama Acara</div></td>
		<th width="15%">Nama</th>
		<td width="30%"><input type="text" name="_nama_acara" class="req" style="width:100%" maxlength="64"></td>
		<th width="15%">Tanggal</th>
		<td><input type="text" name="_tanggal_acara" class="reqd" size="15"></td>
	</tr>
	<tr>
		<th>Penyelenggara</th>
		<td><input type="text" name="_nama_peyelenggara" class="fmt" style="width:100%"></td>
		<th>Tempat</th>
		<td><input type="text" name="_tempat_acara" class="fmt" style="width:100%"></td>
	</tr>
</table>
</form>
<div align='right'>
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkform(window.document.frmInsert)'><img src="../../_images/icon/next.gif" width="15px" align="middle" alt="Go to next step"> &nbsp; Next step</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_event_step_1.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel Event"> &nbsp; Cancel Event</button>
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