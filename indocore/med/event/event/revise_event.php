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
$_code	= urldecode($_GET['_code']);

//DELETE PESERTA ============================================================================================
if(ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/revise_event.php?_code=$_code", 'delete')) {

	if(isZKError($result =& query("DELETE FROM ".ZKP_SQL."_tb_event WHERE ev_idx = $_code"))) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_event.php?_code=$_code");
	}

	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/summary_by_personal_info.php");
}

//UPDATE EVENT ==============================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'update')) {

	$_code				= $_POST['_code'];
	$_nama_acara		= $_POST['_nama_acara'];
	$_tanggal_acara		= $_POST['_tanggal_acara'];
	$_tempat_acara		= $_POST['_tempat_acara'];
	$_nama_peyelenggara	= $_POST['_nama_peyelenggara'];

	$result = executeSP(
		ZKP_SQL."_updateEvent",
		$_code,
		"$\${$_nama_acara}$\$",
		"$\${$_tanggal_acara}$\$",
		"$\${$_tempat_acara}$\$",
		"$\${$_nama_peyelenggara}$\$"
	);

	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_event.php?_code=".$_code);
}

//========================================================================================= DEFAULT PROCESS
$sql = "SELECT * FROM ".ZKP_SQL."_tb_event WHERE ev_idx = $_code";
$result =& query($sql);
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
<script src="../../_include/billing/input_billing.js" type="text/javascript"></script>
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
<input type='hidden' name='p_mode'>
<input type='hidden' name='_code' value="<?php echo $_code ?>">
<div class="head-line">Detail Event, <i><?php echo $column["ev_nama_acara"] ?></i></div>
<table width="100%" class="table_box">
	<tr>
		<td rowspan="2" width="15%"><div class="i_line">Nama Acara</div></td>
		<th width="15%">Nama</th>
		<td width="30%"><input type="text" name="_nama_acara" class="req" style="width:100%" maxlength="64" value="<?php echo $column["ev_nama_acara"] ?>"></td>
		<th width="15%">Tanggal</th>
		<td><input type="text" name="_tanggal_acara" class="reqd" size="10" value="<?php echo date('d-M-Y', strtotime($column["ev_tanggal_acara"])) ?>"></td>
	</tr>
	<tr>
		<th>Penyelenggara</th>
		<td><input type="text" name="_nama_peyelenggara" class="fmt" style="width:100%" value="<?php echo $column["ev_penyelenggara"] ?>"></td>
		<th>Tempat</th>
		<td><input type="text" name="_tempat_acara" class="fmt" style="width:100%" value="<?php echo $column["ev_tempat_acara"] ?>"></td>
	</tr>
</table>
</form>
<div align='right'>
	<button name='btnUpdate' class='input_btn' style='width:80px;'><img src="../../_images/icon/update.gif" width="20px" align="middle"> &nbsp; Update</button>&nbsp;
	<button name='btnDelete' class='input_red' style='width:80px;'><img src="../../_images/icon/trash.gif" width="15px" align="middle"> &nbsp; Delete</button>
</div>
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmInsert;

	window.document.all.btnDelete.onclick = function() {
		if(confirm("Are you sure to delete event?")) {	
			oForm.p_mode.value = 'delete';
			oForm.submit();
		}
	}

	window.document.all.btnUpdate.onclick = function() {
		var o = window.document.frmInsert;
		var d = parseDate(o._tanggal_acara.value, 'prefer_euro_format');
		if(o._nama_acara.value.length <= 0) { alert("Nama acara harus diisi"); o._nama_acara.focus(); return; }
		else if(d == null) { alert("Tanggal acara harus diisi dengan format tanggal"); o._tanggal_acara.value=""; o._tanggal_acara.focus(); return; }
		o._tanggal_acara.value = formatDate(d, "d-NNN-yyyy");
		if(confirm("Are you sure to update event?")) {	
			o.p_mode.value = 'update';
			o.submit();
		}
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