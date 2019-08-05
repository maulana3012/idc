<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*/
//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";	

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "input_event_step_1.php";
if (!isset($_GET['_code']) || $_GET['_code'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else {
	$_code = urldecode($_GET['_code']);
}

//INPUT PESERTA =============================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'insert')) {

	$_action		= $_POST['_action'];
	$_code			= $_POST['_code'];
	$_nama			= $_POST['_nama'];
	$_alamat		= $_POST['_alamat'];
	$_kota			= $_POST['_kota'];
	$_kode_pos		= $_POST['_kode_pos'];
	$_jns_kelamin	= $_POST['_jns_kelamin'];
	$_usia			= $_POST['_usia'];
	$_telepon		= $_POST['_telepon'];
	$_handphone		= $_POST['_handphone'];
	$_email			= $_POST['_email'];
	$_jns_alkes		= $_POST['_jns_alkes'];
	$_lastupdated_by_account = ucfirst($S->getValue("ma_account"));

	$_td_sistolik		= $_POST['_td_sistolik'];
	$_td_diastolik		= $_POST['_td_diastolik'];
	$_gd_sewaktu		= $_POST['_gd_sewaktu'];
	$_gd_puasa			= $_POST['_gd_puasa'];
	$_kt_berat_badan	= $_POST['_kt_berat_badan'];
	$_kt_tinggi_badan	= $_POST['_kt_tinggi_badan'];
	$_kt_lemak_tubuh	= $_POST['_kt_lemak_tubuh'];
	$_kt_bmi			= $_POST['_kt_bmi'];
	$_kt_lemak_perut	= $_POST['_kt_lemak_perut'];
	$_kt_bmr			= $_POST['_kt_bmr'];
	$_kt_lemak_subkutan	= $_POST['_kt_lemak_subkutan'];
	$_kt_otot_rangka	= $_POST['_kt_otot_rangka'];
	$_kt_klasifikasi_umur_tubuh	= $_POST['_kt_klasifikasi_umur_tubuh'];

	$result = executeSP(
		ZKP_SQL."_insertEventPeserta",
		$_code,
		"$\${$_nama}$\$",
		"$\${$_alamat}$\$",
		"$\${$_kota}$\$",
		"$\${$_kode_pos}$\$",
		"$\${$_jns_kelamin}$\$",
		$_usia,
		"$\${$_telepon}$\$",
		"$\${$_handphone}$\$",
		"$\${$_email}$\$",
		"$\${$_jns_alkes}$\$",
		"$\${$_lastupdated_by_account}$\$",
		$_td_sistolik,
		$_td_diastolik,
		$_gd_sewaktu,
		$_gd_puasa,
		$_kt_berat_badan,
		$_kt_tinggi_badan,
		$_kt_lemak_tubuh,
		$_kt_bmi,
		$_kt_lemak_perut,
		$_kt_bmr,
		$_kt_lemak_subkutan,
		$_kt_otot_rangka,
		$_kt_klasifikasi_umur_tubuh
	);

	if($_action == 'add') {
		$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/input_event_step_2.php?_code=$_code");
	} else if($_action == 'stop') {
		$_id = substr($result[0],8,5);
		$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/summary_by_personal_info.php");
	}

}

//========================================================================================= DEFAULT PROCESS
$sql = "SELECT * FROM ".ZKP_SQL."_tb_event WHERE ev_idx = $_code";
$result =& query($sql);
$column =& fetchRowAssoc($result);
if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/input_event_step_1.php");
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
function checkform(o, action) {

	if(o._nama.value.length <=0) {
		alert("Nama harus diisi"); o._nama.focus(); return;
	} else if(o._alamat.value.length <=0) {
		alert("Alamat harus diisi"); o._alamat.focus(); return;
	} else if(o._kota.value.length <=0) {
		alert("Kota harus diisi"); o._kota.focus(); return;
	} else if(o._handphone.value.length <=0) {
		alert("Handphone harus diisi"); o._handphone.focus(); return;
	} 

	if(o._usia.value.length > 0 && isNaN(removecomma(o._usia.value))) {
		alert("Usia harus diisi dengan angka"); o._usia.value = ''; o._usia.focus(); return;
	} else if(o._kode_pos.value.length > 0 && isNaN(removecomma(o._kode_pos.value))) {
		alert("Kode pos harus diisi dengan angka"); o._kode_pos.value = ''; o._kode_pos.focus(); return;
	} else if (o._email.value.length > 0 && !isEmailAddr(o._email.value)) {
		alert("Email tidak valid"); o._email.focus(); return;
	} else if(o._td_sistolik.value.length > 0 && isNaN(removecomma(o._td_sistolik.value))) {
		alert("Tekanan darah sistolik harus diisi dengan angka"); o._td_sistolik.value = ''; o._td_sistolik.focus(); return;
	} else if(o._td_diastolik.value.length > 0 && isNaN(removecomma(o._td_diastolik.value))) {
		alert("Tekanan darah diastolik harus diisi dengan angka"); o._td_diastolik.value = ''; o._td_diastolik.focus(); return;
	} else if(o._gd_sewaktu.value.length > 0 && isNaN(removecomma(o._gd_sewaktu.value))) {
		alert("Glukosa darah sewaktu harus diisi dengan angka"); o._gd_sewaktu.value = ''; o._gd_sewaktu.focus(); return;
	} else if(o._gd_puasa.value.length > 0 && isNaN(removecomma(o._gd_puasa.value))) {
		alert("Glukosa darah puasa harus diisi dengan angka"); o._gd_puasa.value = ''; o._gd_puasa.focus(); return;
	} else if(o._kt_berat_badan.value.length > 0 && isNaN(removecomma(o._kt_berat_badan.value))) {
		alert("Berat badan harus diisi dengan angka"); o._kt_berat_badan.value = ''; o._kt_berat_badan.focus(); return;
	} else if(o._kt_tinggi_badan.value.length > 0 && isNaN(removecomma(o._kt_tinggi_badan.value))) {
		alert("Tinggi badan harus diisi dengan angka"); o._kt_tinggi_badan.value = ''; o._kt_tinggi_badan.focus(); return;
	} else if(o._kt_lemak_tubuh.value.length > 0 && isNaN(removecomma(o._kt_lemak_tubuh.value))) {
		alert("% Lemak Tubuh harus diisi dengan angka"); o._kt_lemak_tubuh.value = ''; o._kt_lemak_tubuh.focus(); return;
	} else if(o._kt_bmi.value.length > 0 && isNaN(removecomma(o._kt_bmi.value))) {
		alert("BMI harus diisi dengan angka"); o._kt_bmi.value = ''; o._kt_bmi.focus(); return;
	} else if(o._kt_lemak_perut.value.length > 0 && isNaN(removecomma(o._kt_lemak_perut.value))) {
		alert("Lemak Perut/ Viceral harus diisi dengan angka"); o._kt_lemak_perut.value = ''; o._kt_lemak_perut.focus(); return;
	} else if(o._kt_bmr.value.length > 0 && isNaN(removecomma(o._kt_bmr.value))) {
		alert("BMR harus diisi dengan angka"); o._kt_bmr.value = ''; o._kt_bmr.focus(); return;
	} else if(o._kt_lemak_subkutan.value.length > 0 && isNaN(removecomma(o._kt_lemak_subkutan.value))) {
		alert("% Lemak Subkutan harus diisi dengan angka"); o._kt_lemak_subkutan.value = ''; o._kt_lemak_subkutan.focus(); return;
	} else if(o._kt_otot_rangka.value.length > 0 && isNaN(removecomma(o._kt_otot_rangka.value))) {
		alert("% Otot Rangka harus diisi dengan angka"); o._kt_otot_rangka.value = ''; o._kt_otot_rangka.focus(); return;
	} else if(o._kt_klasifikasi_umur_tubuh.value.length > 0 && isNaN(removecomma(o._kt_klasifikasi_umur_tubuh.value))) {
		alert("Klasifikasi Umur Tubuh harus diisi dengan angka"); o._kt_klasifikasi_umur_tubuh.value = ''; o._kt_klasifikasi_umur_tubuh.focus(); return;
	} 

	if(confirm("Are you sure to save event?")) {
		o._action.value = action;
		o.submit();
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
          	<!--START: BODY-->
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode' value='insert'>
<input type='hidden' name='_action'>
<input type='hidden' name='_code' value="<?php echo $_code ?>">
<div class="head-line">Input Event <i>(2 / 2)</i></div>
<table width="100%" class="table_box">
	<tr>
		<td rowspan="2" width="15%"><div class="i_line">Nama Acara</div></td>
		<th width="15%">Nama</th>
		<td width="30%"><?php echo $column["ev_nama_acara"] ?></td>
		<th width="15%">Tanggal</th>
		<td><?php echo date('d-M-Y', strtotime($column["ev_tanggal_acara"])) ?></td>
	</tr>
	<tr>
		<th>Penyelenggara</th>
		<td><?php echo $column["ev_penyelenggara"] ?></td>
		<th>Tempat</th>
		<td><?php echo $column["ev_tempat_acara"] ?></td>
	</tr>
</table>
<div class="i_line">Data Diri</div>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">NAMA</th>
		<td width="20%" colspan="2"><input type="text" name="_nama" class="req" size="30"></td>
		<th rowspan="2" width="15%">ALAMAT</th>
		<td rowspan="2">
			<textarea name="_alamat" rows="3" style="width:100%" class="req"></textarea>
			Kota <input type="text" name="_kota" class="req" size="20"> &nbsp;
			Kode Pos <input type="text" name="_kode_pos" class="fmt" size="10">
		</td>
	</tr>
	<tr>
		<th valign="top">JENIS KELAMIN</th>
		<td colspan="2" valign="top">
			<input type="radio" name="_jns_kelamin" value="l" checked>L &nbsp; <input type="radio" name="_jns_kelamin" value="p">P	, 
			<i>Usia </i>
			<input type="text" name="_usia" class="fmt" size="5">
		</td>
	</tr>
	<tr>
		<th>CONTACT</th>
		<td><i>Rumah<br />HP</i></td>
		<td>
			<input type="text" name="_telepon" class="fmt" size="15"><br />
			<input type="text" name="_handphone" class="req" size="15">
		</td>
		<th rowspan="2">Alat kesehatan<br />yang dimiliki</th>
		<td rowspan="2"><textarea name="_jns_alkes" rows="4" style="width:100%"></textarea></td>
	</tr>
	<tr>
		<th>EMAIL</th>
		<td colspan="2"><input type="text" name="_email" class="fmt" style="width:100%"></td>
	</tr>
</table><br />
<div class="i_line">Hasil Pemeriksaan</div>
<table width="100%" class="table_box">
  <tr>
    <td width="40%" valign="top">
	  <table width="100%" class="table_box">
	  <tr>
		<td colspan="2"><span class="non">Tekanan Darah</span></td>
	  </tr>
	  <tr>
		<th width="50%">Sistolik<br />Diastolik</th>
		<td><input type="text" name="_td_sistolik" class="fmtn" size="5"> mmHG<br /><input type="text" name="_td_diastolik" class="fmtn" size="5"> mmHG</td>
	  </tr>
	  </table>
	</td>
	<td rowspan="2" width="3%"></td>
	<td rowspan="2" valign="top">
	  <table width="100%" class="table_box">
	  <tr>
		<td colspan="2"><span class="non">Komposisi Tubuh</span></td>
	  </tr>
	  <tr>
		<th width="30%">Berat Badan</th>
		<td><input type="text" name="_kt_berat_badan" class="fmtn" size="5"> kg</td>
		<th width="30%">Tinggi Badan</th>
		<td><input type="text" name="_kt_tinggi_badan" class="fmtn" size="5"> cm</td>
	  </tr>
	  <tr>
		<th>% Lemak Tubuh</th>
		<td><input type="text" name="_kt_lemak_tubuh" class="fmtn" size="5"></td>
		<th>BMI</th>
		<td><input type="text" name="_kt_bmi" class="fmtn" size="5"></td>
	  </tr>
	  <tr>
		<th>Lemak Perut/ Viceral</th>
		<td><input type="text" name="_kt_lemak_perut" class="fmtn" size="5"></td>
		<th>BMR</th>
		<td><input type="text" name="_kt_bmr" class="fmtn" size="5"></td>
	  </tr>
	  <tr>
		<th>% Lemak Subkutan</th>
		<td><input type="text" name="_kt_lemak_subkutan" class="fmtn" size="5"></td>
		<th>% Otot Rangka</th>
		<td><input type="text" name="_kt_otot_rangka" class="fmtn" size="5"></td>
	  </tr>
	  <tr>
		<th>Klasifikasi Umur Tubuh</th>
		<td><input type="text" name="_kt_klasifikasi_umur_tubuh" class="fmtn" size="5"></td>
	  </tr>
	  </table>
	</td>
  </tr>
  <tr>
	<td valign="top">
	  <table width="100%" class="table_box">
	  <tr>
		<td colspan="2"><span class="non"><br />Kadar Glukosa Darah Kapiler</span></td>
	  </tr>
	  <tr>
		<th width="50%">Glukosa darah sewaktu<br />Glukosa darah puasa</th>
		<td><input type="text" name="_gd_sewaktu" class="fmtn" size="5"> mg/dL<br /><input type="text" name="_gd_puasa" class="fmtn" size="5"> mg/dL</td>
	  </tr>
	  </table>
	</td>	
  </tr>
</table>
</form>
<p align='center'>
	<button name='btnSaveAdd' class='input_btn' style='width:100px;' onclick='checkform(window.document.frmInsert, "add")'>Save & Add</button>&nbsp;
	<button name='btnSave' class='input_btn' style='width:80px;' onclick='checkform(window.document.frmInsert, "stop")'>Save</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:90px;' onclick='window.location.href="input_event_step_1.php"'>Batal</button>
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