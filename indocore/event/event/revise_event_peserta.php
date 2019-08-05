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
$_code	= urldecode($_GET['_code']);
$_id	= $_GET['_id'];

//DELETE PESERTA ============================================================================================
if(ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/revise_event_peserta.php?_code=$_code&_id=$_id", 'delete')) {

	if(isZKError($result =& query("DELETE FROM ".ZKP_SQL."_tb_event_peserta WHERE evp_code='$_id'"))) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_event_peserta.php?_code=$_code&_id=$_id");
	}

	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/summary_by_personal_info.php");
}

//UPDATE PESERTA ============================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'update')) {

	$_code			= $_POST['_code'];
	$_id			= $_POST['_id'];
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
		ZKP_SQL."_updateEventPeserta",
		$_code,
		"$\${$_id}$\$",
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

	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_event_peserta.php?_id=$_id");

}

//========================================================================================= DEFAULT PROCESS
$sql = "SELECT * FROM ".ZKP_SQL."_tb_event JOIN ".ZKP_SQL."_tb_event_peserta using(ev_idx) WHERE evp_code='$_id'";
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
<input type='hidden' name='_action'>
<input type='hidden' name='_code' value="<?php echo $column["ev_idx"] ?>">
<input type='hidden' name='_id' value="<?php echo $column["evp_code"] ?>">
<div class="head-line">Detail Event, <i><?php echo $column["ev_nama_acara"] ?></i></div>
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
	<tr>
		<td align="right" colspan="5">
			<button name='btnUpdateEvent' class='input_btn' style='width:80px;'><img src="../../_images/icon/update.gif" width="20px" align="middle"> &nbsp; Update</button>&nbsp;
			<button name='btnAdd' class='input_btn' style='width:60px;'><img src="../../_images/icon/add.gif" width="20px" align="middle"> Add</button>
		</td>
	</tr>
</table>
<table width="100%" class="table_box">
	<tr>
		<td colspan="2"><div width="50%" class="i_line">Data Diri</div></td>
		<td colspan="3" align="right" valign="bottom"><span><i><?php echo "Lastupdated by ". $column["evp_updated_by_account"] . ", " . date("d-M-Y H:i:s", strtotime($column["evp_updated_timestamp"])) ?></i></span></td>
	</tr>
	<tr>
		<th width="15%">NAMA</th>
		<td width="20%" colspan="2"><input type="text" name="_nama" class="req" size="30" value="<?php echo $column["evp_nama"] ?>"></td>
		<th rowspan="2" width="15%">ALAMAT</th>
		<td rowspan="2">
			<textarea name="_alamat" rows="3" style="width:100%" class="req"><?php echo $column["evp_contact_alamat"] ?></textarea>
			Kota <input type="text" name="_kota" class="req" size="20" value="<?php echo $column["evp_kota"] ?>"> &nbsp;
			Kode Pos <input type="text" name="_kode_pos" class="fmt" size="10" value="<?php echo $column["evp_pos_kode"] ?>">
		</td>
	</tr>
	<tr>
		<th valign="top">JENIS KELAMIN</th>
		<td colspan="2" valign="top">
			<input type="radio" name="_jns_kelamin" value="l"<?php echo ($column["evp_jenis_kelamin"]=='l') ? " checked" : ""?>>L &nbsp; 
			<input type="radio" name="_jns_kelamin" value="p"<?php echo ($column["evp_jenis_kelamin"]=='p') ? " checked" : ""?>>P	, 
			<i>Usia </i>
			<input type="text" name="_usia" class="fmtn" size="5" value="<?php echo $column["evp_usia"] ?>">
		</td>
	</tr>
	<tr>
		<th>CONTACT</th>
		<td><i>Rumah<br />HP</i></td>
		<td>
			<input type="text" name="_telepon" class="fmt" size="15" value="<?php echo $column["evp_contact_telepon"] ?>"><br />
			<input type="text" name="_handphone" class="req" size="15" value="<?php echo $column["evp_contact_handphone"] ?>">
		</td>
		<th rowspan="2">Alat kesehatan<br />yang dimiliki</th>
		<td rowspan="2"><textarea name="_jns_alkes" rows="4" style="width:100%"><?php echo $column["evp_alat"] ?></textarea></td>
	</tr>
	<tr>
		<th>EMAIL</th>
		<td colspan="2"><input type="text" name="_email" class="fmt" style="width:100%" value="<?php echo $column["evp_contact_email"] ?>"></td>
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
		<td>
			<input type="text" name="_td_sistolik" class="fmtn" size="5" value="<?php echo $column["evp_sistolik"] ?>"> mmHG<br />
			<input type="text" name="_td_diastolik" class="fmtn" size="5" value="<?php echo $column["evp_diastolik"] ?>"> mmHG
		</td>
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
		<td><input type="text" name="_kt_berat_badan" class="fmtn" size="5" value="<?php echo $column["evp_berat_badan"] ?>"> kg</td>
		<th width="30%">Tinggi Badan</th>
		<td><input type="text" name="_kt_tinggi_badan" class="fmtn" size="5" value="<?php echo $column["evp_tinggi_badan"] ?>"> cm</td>
	  </tr>
	  <tr>
		<th>% Lemak Tubuh</th>
		<td><input type="text" name="_kt_lemak_tubuh" class="fmtn" size="5" value="<?php echo $column["evp_lemak_tubuh"] ?>"></td>
		<th>BMI</th>
		<td><input type="text" name="_kt_bmi" class="fmtn" size="5" value="<?php echo $column["evp_bmi"] ?>"></td>
	  </tr>
	  <tr>
		<th>Lemak Perut/ Viceral</th>
		<td><input type="text" name="_kt_lemak_perut" class="fmtn" size="5" value="<?php echo $column["evp_lemak_perut"] ?>"></td>
		<th>BMR</th>
		<td><input type="text" name="_kt_bmr" class="fmtn" size="5" value="<?php echo $column["evp_bmr"] ?>"></td>
	  </tr>
	  <tr>
		<th>% Lemak Subkutan</th>
		<td><input type="text" name="_kt_lemak_subkutan" class="fmtn" size="5" value="<?php echo $column["evp_lemak_subkutan"] ?>"></td>
		<th>% Otot Rangka</th>
		<td><input type="text" name="_kt_otot_rangka" class="fmtn" size="5" value="<?php echo $column["evp_otot_rangka"] ?>"></td>
	  </tr>
	  <tr>
		<th>Klasifikasi Umur Tubuh</th>
		<td><input type="text" name="_kt_klasifikasi_umur_tubuh" class="fmtn" size="5" value="<?php echo $column["evp_klasifikasi_umur_tubuh"] ?>"></td>
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
		<td>
			<input type="text" name="_gd_sewaktu" class="fmtn" size="5" value="<?php echo $column["evp_glukosa_darah_sewaktu"] ?>"> mg/dL<br />
			<input type="text" name="_gd_puasa" class="fmtn" size="5" value="<?php echo $column["evp_glukosa_darah_puasa"] ?>"> mg/dL
		</td>
	  </tr>
	  </table>
	</td>	
  </tr>
</table>
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td>
			<button name='btnDelete' class='input_red' style='width:80px;'><img src="../../_images/icon/trash.gif" width="15px" align="middle"> &nbsp; Delete</button>
		</td>
		<td align="right">
			<button name='btnUpdate' class='input_btn' style='width:80px;'><img src="../../_images/icon/update.gif" width="20px" align="middle"> &nbsp; Update</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle"> &nbsp; Go to summary</button>
		</td>
	</tr>
</table><br />
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

		if(confirm("Are you sure to update event?")) {
			oForm.p_mode.value = 'update';
			oForm.submit();
		}
	}

	window.document.all.btnUpdateEvent.onclick = function() {
		window.location.href = 'revise_event.php?_code='+oForm._code.value;
	}

	window.document.all.btnAdd.onclick = function() {
		window.location.href = 'input_event_step_2.php?_code='+oForm._code.value;;
	}

	window.document.all.btnList.onclick = function() {
		window.location.href = 'summary_by_personal_info.php';
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