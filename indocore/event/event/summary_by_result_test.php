<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim
*/
//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";
require_once LIB_DIR . "zk_listing.php";

//GLOBAL
$left_loc = "summary_by_result_test.php";

//DEFAULT PROCESS
$sqlQuery = new strSelect("SELECT * FROM ".ZKP_SQL."_tb_event JOIN ".ZKP_SQL."_tb_event_peserta USING(ev_idx)");
$tmp = array();
$strGet = "";

//Usia
if(isset($_GET['usia_from']) && isset($_GET['usia_to'])) {
	if($_GET['usia_from'] != '' && $_GET['usia_to'] != '') {
		$_usia_from	= $_GET['usia_from'];
		$_usia_to	= $_GET['usia_to'];
		$tmp[] = "evp_usia BETWEEN $_usia_from AND $_usia_to";
		$strGet .= "&usia_from=$_usia_from&usia_to=$_usia_to";
	}
}

//Jenis Kelamin
if(isset($_GET['cbojk']) && $_GET['cbojk'] != 'all') {
	$_jk	= $_GET['cbojk'];
	$tmp[] = "evp_jenis_kelamin = '$_jk'";
	$strGet .= "&jk=$_jk";
}

$period_from 	= isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", time()-2592000);
$period_to 		= isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", time());
$tmp[] = "ev_tanggal_acara between date '$period_from' and date '$period_to'";
$strGet .= "&period_from=$period_from&period_to=$period_to";

if(count($tmp) > 0) {
	$strWhere = implode(" AND ", $tmp);
	$sqlQuery->whereCaluse = $strWhere;
}
$sqlQuery->setOrderBy("ev_tanggal_acara DESC, evp_code");
if(isZKError($result =& query($sqlQuery->getSQL())))
	$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/index.php");

//Total Rows
$numRow = numQueryRows($result);
$numRowPage = 200;

//Declare Paging
$oPage = new strPaging($sqlQuery->getSQL(), $numRowPage);
$oPage->strPrev       = "";
$oPage->strNext       = "";
$oPage->strPrevDiv    = "<";
$oPage->strNextDiv    = ">";
$oPage->strLast       = ">>";
$oPage->strFirst      = "<<";
$oPage->strCurrentNum = "<strong>[%s]</strong>";
$oPage->strGet = $strGet;

if(isZKError($result =& query($oPage->getListQuery())))
	$m->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");

$usia_from 	= isset($_GET['usia_from'])? urldecode($_GET['usia_from']) : "";
$usia_to 	= isset($_GET['usia_to'])? urldecode($_GET['usia_to']) : "";
$jk		 	 = isset($_GET['cbojk'])? urldecode($_GET['cbojk']) : "all";
$period_from = isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", time()-2592000);
$period_to 	 = isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", time());
$total 		 = array();
for($i = 0; $i < 13; $i++) {
	$total[0][$i] = 0;
	$total[1][$i] = 0;
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
<?php
$usia_from 		= isset($_GET['usia_from'])? urldecode($_GET['usia_from']) : "";
$usia_to 		= isset($_GET['usia_to'])? urldecode($_GET['usia_to']) : "";
$jk		 		= isset($_GET['jk'])? urldecode($_GET['jk']) : "all";
$period_from 	= isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", time()-2592000);
$period_to 		= isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", time());
?>
<h4>DAFTAR HASIL PEMERIKSAAN</h4>
<form name="frmSearch" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
<table width="100%" class="table_layout">
	<tr height="5px"><td></td></tr>
	<tr>
		<td width="10%"></td>
		<td> 
			EVENT PERIOD 
			<select name="cboPeriod">
				<option value=""></option>
				<option value="lastWeek">LAST WEEK</option>
				<option value="lastMonth">LAST MONTH</option>
				<option value="thisWeek">THIS WEEK</option>
				<option value="thisMonth">THIS MONTH</option>
			</select>
			<input type="text" name="period_from" size="10" class="fmtd" value="<?php echo $period_from; ?>">&nbsp;
			<input type="text" name="period_to" size="10" class="fmtd"  value="<?php echo $period_to; ?>">
		</td>
		<td>
			RANGE USIA 
			<input type="text" name="usia_from" size="5" class="fmtd" value="<?php echo $usia_from; ?>">&nbsp;-&nbsp;
			<input type="text" name="usia_to" size="5" class="fmtd"  value="<?php echo $usia_to; ?>">
		</td>
		<td> 
			JK 
			<select name="cbojk">
				<option value="all"></option>
				<option value="l">LAKI-LAKI</option>
				<option value="p">PEREMPUAN</option>
			</select>
		</td>
	</tr>
</table><br />
</form>
<table width="100%" class="table_f">
	<tr>
		<th rowspan="2" width="2%">No</th>
		<th rowspan="2" width="6%">No Input</th>
		<th rowspan="2" width="15%">Nama</th>
		<th rowspan="2" width="5%">JK</th>
		<th rowspan="2" width="5%">Usia</th>
		<th colspan="2">Tekanan Darah</th>
		<th colspan="2">Glukosa Darah</th>
		<th colspan="9">Komposisi Tubuh</th>
	</tr>
	<tr>
		<th width="5%">SIS</th>
		<th width="5%">DIA</th>
		<th width="5%">Puasa</th>
		<th width="5%">Swaktu</th>
		<th width="5%">Berat<br />(Kg)</th>
		<th width="5%">Tinggi<br />(Cm)</th>
		<th width="5%">Lemak<br />Tubuh (%)</th>
		<th width="5%">BMI</th>
		<th width="5%">Lemak<br />Perut</th>
		<th width="5%">BMR</th>
		<th width="5%">Lemak <br />Subkutan (%)</th>
		<th width="5%">Otot<br />rangka (%)</th>
		<th width="5%">Umur<br />tubuh</th>
	</tr>
	<?php while ($column =& fetchRowAssoc($result)) { ?>
	<tr>
		<td><?php echo ++$oPage->serial ;?></td>
		<td><?php echo $column["evp_code"] ?></td>
		<td><a href="revise_event_peserta.php?_id=<?php echo $column["evp_code"];?>"><span style="color:blue"><?php echo $column["evp_nama"] ?></span></a></td>
		<td align="center"><?php echo strtoupper($column["evp_jenis_kelamin"]) ?></td>
		<td align="right"><?php echo $column["evp_usia"] ?></td>
		<td align="right"><?php echo $column["evp_sistolik"] ?></td>
		<td align="right"><?php echo $column["evp_diastolik"] ?></td>
		<td align="right"><?php echo $column["evp_glukosa_darah_puasa"] ?></td>
		<td align="right"><?php echo $column["evp_glukosa_darah_sewaktu"] ?></td>
		<td align="right"><?php echo $column["evp_berat_badan"] ?></td>
		<td align="right"><?php echo $column["evp_tinggi_badan"] ?></td>
		<td align="right"><?php echo $column["evp_lemak_tubuh"] ?></td>
		<td align="right"><?php echo $column["evp_bmi"] ?></td>
		<td align="right"><?php echo $column["evp_lemak_perut"] ?></td>
		<td align="right"><?php echo $column["evp_bmr"] ?></td>
		<td align="right"><?php echo $column["evp_lemak_subkutan"] ?></td>
		<td align="right"><?php echo $column["evp_otot_rangka"] ?></td>
		<td align="right"><?php echo $column["evp_klasifikasi_umur_tubuh"] ?></td>
	</tr>
	<?php 
		if($column["evp_sistolik"] > 0) 				$total[0][0]++;
		if($column["evp_diastolik"] > 0) 				$total[0][1]++;
		if($column["evp_glukosa_darah_puasa"] > 0) 		$total[0][2]++;
		if($column["evp_glukosa_darah_sewaktu"] > 0)	$total[0][3]++;
		if($column["evp_berat_badan"] > 0) 				$total[0][4]++;
		if($column["evp_tinggi_badan"] > 0) 			$total[0][5]++;
		if($column["evp_lemak_tubuh"] > 0) 				$total[0][6]++;
		if($column["evp_bmi"] > 0) 						$total[0][7]++;
		if($column["evp_lemak_perut"] > 0) 				$total[0][8]++;
		if($column["evp_bmr"] > 0) 						$total[0][9]++;
		if($column["evp_lemak_subkutan"] > 0) 			$total[0][10]++;
		if($column["evp_otot_rangka"] > 0) 				$total[0][11]++;
		if($column["evp_klasifikasi_umur_tubuh"] > 0) 	$total[0][12]++;

		$total[1][0] = $column["evp_sistolik"];
		$total[1][1] = $column["evp_diastolik"];
		$total[1][2] = $column["evp_glukosa_darah_puasa"];
		$total[1][3] = $column["evp_glukosa_darah_sewaktu"];
		$total[1][4] = $column["evp_berat_badan"];
		$total[1][5] = $column["evp_tinggi_badan"];
		$total[1][6] = $column["evp_lemak_tubuh"];
		$total[1][7] = $column["evp_bmi"];
		$total[1][8] = $column["evp_lemak_perut"];
		$total[1][9] = $column["evp_bmr"];
		$total[1][10] = $column["evp_lemak_subkutan"];
		$total[1][11] = $column["evp_otot_rangka"];
		$total[1][12] = $column["evp_klasifikasi_umur_tubuh"];
	}
	?>
	<tr>
		<th colspan="5" align="right">Total</th>
		<?php 
		for($i=0; $i<13; $i++) {
			echo "\t\t<td align=\"right\">". number_format($total[1][$i]) ."</td>\n";
		}
		?>
	</tr>
	<tr>
		<th colspan="5" align="right">Jumlah Perhitungan</th>
		<?php 
		for($i=0; $i<13; $i++) {
			echo "\t\t<td align=\"right\">". number_format($total[0][$i]) ."</td>\n";
		}
		?>
	</tr>
	<tr>
		<th colspan="5" align="right">Rata-rata</th>
		<?php 
		for($i=0; $i<13; $i++) {
			if($total[1][$i]<=0 || $total[0][$i]<=0) echo "\t\t<td align=\"right\">0</td>\n";
			else echo "\t\t<td align=\"right\">". number_format($total[1][$i]/$total[0][$i], 2)."</td>\n";
		}
		?>
	</tr>
</table>
<table width="100%" cellpadding="0" cellspacing="2" border="0">
	<tr>
		<td align="center"><?php echo $oPage->putPaging();?></td>
	</tr>
</table>
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	var ts = <?php echo time() * 1000;?>;

	setSelect(f.cbojk, "<?php echo isset($_GET['cbojk']) ? $_GET['cbojk'] : "all"?>");
	setSelect(f.cboPeriod, "<?php echo isset($_GET['cboPeriod']) ? $_GET['cboPeriod'] : "default"?>");

	f.usia_from.onkeypress = function() {
		if(window.event.keyCode == 13) {
			if(f.usia_from.value.length <= 0 || f.usia_to.value.length <= 0) {
				alert("Usia harus diisi"); f.usia_from.focus(); return;
			} else if(isNaN(removecomma(f.usia_from.value)) || isNaN(removecomma(f.usia_to.value))) {
				alert("Usia harus diisi dengan angka"); f.usia_from.focus(); return;
			} else if(removecomma(f.usia_from.value) > removecomma(f.usia_to.value)) {
				alert("Kolom usia dari harus lebih kecil dari kolom usia sampai"); f.usia_from.focus(); return;
			}
			f.submit();
		}
	}

	f.usia_to.onkeypress = function() {
		if(window.event.keyCode == 13) {
			if(f.usia_from.value.length <= 0 || f.usia_to.value.length <= 0) {
				alert("Usia harus diisi"); f.usia_from.focus(); return;
			} else if(isNaN(removecomma(f.usia_from.value)) || isNaN(removecomma(f.usia_to.value))) {
				alert("Usia harus diisi dengan angka"); f.usia_from.focus(); return;
			} else if(removecomma(f.usia_from.value) > removecomma(f.usia_to.value)) {
				alert("Kolom usia dari harus lebih kecil dari kolom usia sampai"); f.usia_from.focus(); return;
			}
			f.submit();
		}
	}

	f.cbojk.onchange = function() {
		f.submit();
	}

	f.cboPeriod.onchange = function() {
		if (this.value != "") {
			setPeriod(ts, this.value, f.period_from, f.period_to);
			f.submit();
		}
	}


	f.period_from.onkeypress = function() {
		if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
			f.cboPeriod.value = '';
			f.submit();
		}
	}

	f.period_to.onkeypress = function() {
		if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
			f.cboPeriod.value = '';
			f.submit();
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