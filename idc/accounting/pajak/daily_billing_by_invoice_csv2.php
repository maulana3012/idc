<?php 
header('Expires: 0');
header('Cache-control: private');
header('Cache-Control: must-revalidate, GET-check=0, pre-check=0');
header('Content-Description: File Transfer');
header('Content-Type: application/vnd.ms-excel');
header('Content-disposition: attachment; filename="file_pajak.csv"'); 

//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//Global
$_code	  		= $_GET['_code'];
$_code	  		= explode(",",trim($_code));
$_code   		= "'".implode("','",$_code)."'";
$s_mode			= isset($_GET['s_mode']) ? $_GET['s_mode'] : "period";
$_sort_by       = isset($_GET['cboSortBy']) ? $_GET['cboSortBy'] : "bill_vat_inv_no";
$_marketing		= isset($_GET['cboFilterMarketing']) ? $_GET['cboFilterMarketing'] : "all";
$_order_by		= isset($_GET['cboFilterOrderBy']) ? $_GET['cboFilterOrderBy'] : $cboFilter[1][ZKP_FUNCTION][0][0];
$_dept			= isset($_GET['cboFilterDept']) ? $_GET['cboFilterDept'] : "all";
$_vat			= isset($_GET['cboFilterVat']) ? $_GET['cboFilterVat'] : ((ZKP_FUNCTION=='MEP')?"all":"vat-IO"); 
$_paper			= isset($_GET['cboFilterPaper']) ? $_GET['cboFilterPaper'] : "all";
$cboSearchType 	= isset($_GET['cboSearchType'])? $_GET['cboSearchType'] : "";


if($s_mode == 'period') {
	$some_date 		= "";
	$period_from 	= isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", mktime(0,0,0,date("m"),1,date("Y")));
	$period_to 		= isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", mktime(0,0,0,date("m")+1,0,date("Y")));
} elseif ($s_mode == 'date') {
	$some_date 		= $_GET['some_date'];
	$period_from 	= "";
	$period_to 		= "";
}

//SET WHERE PARAMETER
if(ZKP_FUNCTION == 'ALL') {
	if($_order_by != 'all'){
		$tmp[]	= "bill_ordered_by = $_order_by";
	}
} else {
	$tmp[]	= "bill_ordered_by = ".$cboFilter[1][ZKP_FUNCTION][0][0];
	$tmp[]	= ZKP_SQL."_isValidShowInvoice('".ZKP_FUNCTION."', bill_code,'billing')";
}

if($_marketing != "all") $tmp[] = "cus_responsibility_to = $_marketing";

if($_vat == 'vat') $tmp[] = "bill_vat > 0";
else if ($_vat == 'vat-IO') $tmp[] = "bill_vat > 0 AND bill_type_pajak = 'IO'";
else if ($_vat == 'vat-IP') $tmp[] = "bill_vat > 0 AND bill_type_pajak = 'IP'";
else if ($_vat == 'non') $tmp[] = "bill_vat = 0";

if ($some_date != "") $tmp[] = "bill_inv_date = DATE '$some_date'";
else $tmp[] = "bill_inv_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";

if($_dept != 'all') $tmp[] = "bill_dept = '$_dept'";

if($_code != '') $tmp[] = "bill_code IN ($_code)";

$strWhere = implode(" AND ", $tmp);

//DEFAULT LIST
$sql ="
SELECT 
'FK' AS var_fk,
substr(bill_vat_inv_no,1,2) AS var_kd_jenis_transaksi,
'0' AS var_fg_pengganti,
substr(bill_vat_inv_no,5,3) || substr(bill_vat_inv_no,9,2) || substr(bill_vat_inv_no,12) AS var_nomor_faktur,
to_char(bill_inv_date,'mm')::int AS var_masa_pajak,
to_char(bill_inv_date,'YYYY') AS var_tahun_pajak, 
to_char(bill_inv_date,'dd/mm/YYYY') AS var_tanggal_faktur,
CASE 
	WHEN bill_npwp is null THEN '000000000000000'
	ELSE substr(bill_npwp,1,2) || substr(bill_npwp,4,3) || substr(bill_npwp,8,3) || substr(bill_npwp,12,1) || substr(bill_npwp,14,3) || substr(bill_npwp,18,3) 
END AS var_npwp,
bill_pajak_to_name AS var_nama,
bill_pajak_to_address AS var_alamat_lengkap,
to_char((SELECT SUM( TRUNC((biit_unit_price*biit_qty)*((100-bill_discount)/100)) ) FROM ".ZKP_SQL."_tb_billing_item WHERE bill_code = a.bill_code), '99999999999') AS var_jumlah_dpp,
to_char((SELECT SUM( ROUND(((biit_unit_price*biit_qty)*((100-bill_discount)/100)*0.1),0) ) FROM ".ZKP_SQL."_tb_billing_item WHERE bill_code = a.bill_code), '99999999999') AS var_jumlah_ppn,
'0' AS var_jumlah_ppnbm,
CASE
  WHEN substr(bill_vat_inv_no,1,3) = '070' THEN '1'
  ELSE ''
END AS var_id_ket_tambahan,
'' AS var_fg_uang_muka,
'' AS var_uang_muka_dpp,
'' AS var_uang_muka_ppn,
'' AS var_uang_muka_ppnbm,
'' AS var_referensi,
'FAPR' AS var_fapr,
CASE 
	WHEN '".ZKP_SQL."' = 'IDC' THEN 'PT. Indocore Perkasa'
    WHEN '".ZKP_SQL."' = 'MED' AND bill_ordered_by = 1 THEN 'PT. Medisindo Bahana'
    WHEN '".ZKP_SQL."' = 'MED' AND bill_ordered_by = 2 THEN 'PT. Samudia Bahtera'
END AS var_npwp_pengeluar_faktur,
CASE 
    WHEN '".ZKP_SQL."' = 'IDC' THEN 'Graha Mas Pemuda Blok AB No. 19, Jati Pulo Gadung, Jakarta Timur, DKI Jakarta Raya'
    WHEN '".ZKP_SQL."' = 'MED' THEN 'Graha Cempaka Mas Blok E No.15, Kel. Sumur Batu, Kec. Kemayoran, Jakarta Pusat, DKI Jakarta Raya, 10640'
END AS var_alamat_npwp,
'' AS var_jalan,
'' AS var_blok,
'' AS var_nomor,
'' AS var_rt,
'' AS var_rw,
'' AS var_kecamatan,
'' AS var_kelurahan,
'' AS var_kabupaten,
'' AS var_propinsi,
'' AS var_kode_pos,
'' AS var_no_telp,
'OF' AS var_of,
it_code AS var_kode_objek,
it_model_no AS var_nama_item,
to_char((TRUNC(biit_unit_price)), '99999999999') AS var_harga_satuan,
biit_qty AS var_jumlah_barang,
to_char(TRUNC(biit_unit_price*biit_qty), '99999999999') AS var_harga_total,
to_char(TRUNC((biit_unit_price*biit_qty)*(bill_discount/100)), '99999999999') AS var_diskon,
to_char(ROUND((biit_unit_price*biit_qty)*((100-bill_discount)/100)), '99999999999') AS var_dpp,
to_char(ROUND(((biit_unit_price*biit_qty)*((100-bill_discount)/100)*0.1)), '99999999999') AS var_ppn,
'0' AS var_tarif_ppnbm,
'0' AS var_ppnbm,
bill_code,
biit_idx
FROM ".ZKP_SQL."_tb_billing AS a JOIN ".ZKP_SQL."_tb_billing_item AS b USING (bill_code) 
WHERE $strWhere
ORDER BY substr($_sort_by,4), it_code";
/*
echo "<pre>";
var_dump($sql);
echo "</pre>";
*/
echo '"FK","KD_JENIS_TRANSAKSI","FG_PENGGANTI","NOMOR_FAKTUR","NOMOR_FAKTUR","TAHUN_PAJAK","TANGGAL_FAKTUR","NPWP","NAMA","ALAMAT_LENGKAP","JUMLAH_DPP","JUMLAH_PPN","JUMLAH_PPNBM","ID_KETERANGAN_TAMBAHAN","FG__UANG_MUKA","UANG_MUKA_DPP","UANG_MUKA_PPN","UANG_MUKA_PPNBM","REFERENSI"'."\r";
echo '"LT","NPWP","NAMA","JALAN","BLOK","NOMOR","RT","RW","KECAMATAN","KELURAHAN","KABUPATEN","PROPINSI","KODE_POS","NOMOR_TELEPON"'."\r";
echo '"OF","KODE_OBJEK","NAMA","HARGA_SATUAN","JUMLAH_BARANG","HARGA_TOTAL","DISKON","DPP","PPN","TARIF_PPNBM","PPNBM"'."\r";

$rd		= array();
$rdIdx	= 0;
$group0	= array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {
	
	$rd[] = array(
		$col['var_fk'], 
		$col['var_kd_jenis_transaksi'],
		$col['var_fg_pengganti'],
		$col['var_nomor_faktur'],
		$col['var_masa_pajak'],
		$col['var_tahun_pajak'],
		$col['var_tanggal_faktur'],
		$col['var_npwp'],
		$col['var_nama'],
		$col['var_alamat_lengkap'],
		$col['var_jumlah_dpp'],
		$col['var_jumlah_ppn'],
		$col['var_jumlah_ppnbm'],
		$col['var_id_ket_tambahan'],
		$col['var_fg_uang_muka'],
		$col['var_uang_muka_dpp'],
		$col['var_uang_muka_ppn'],
		$col['var_uang_muka_ppnbm'],
		$col['var_referensi'],
		$col['var_fapr'],
		$col['var_npwp_pengeluar_faktur'],
		$col['var_alamat_npwp'],
		$col['var_jalan'],
		$col['var_blok'],
		$col['var_nomor'],
		$col['var_rt'],
		$col['var_rw'],
		$col['var_kecamatan'],
		$col['var_kelurahan'],
		$col['var_kabupaten'],
		$col['var_propinsi'],
		$col['var_kode_pos'],
		$col['var_no_telp'],
		$col['var_of'],
		$col['var_kode_objek'],
		$col['var_nama_item'],
		$col['var_harga_satuan'],
		$col['var_jumlah_barang'],
		$col['var_harga_total'],
		$col['var_diskon'],
		$col['var_dpp'],
		$col['var_ppn'],
		$col['var_tarif_ppnbm'],
		$col['var_ppnbm'],
		$col['bill_code'],
		$col['biit_idx']
	);

	if($cache[0] != $col['bill_code']) {
		$cache[0] = $col['bill_code'];
		$group0[$col['bill_code']] = array();
	}

	if($cache[1] != $col['biit_idx']) {
		$cache[1] = $col['biit_idx'];
	}
		
	$group0[$col['bill_code']][$col['biit_idx']] = 1;

}

foreach ($group0 as $value1 => $group1) {

	for($i=0; $i<19; $i++) {
		echo '"'.strtoupper(htmlspecialchars_decode(trim($rd[$rdIdx][$i]))).'"'.',';
	}
	$raw_row = $rdIdx;
	$jumlah_ppn = $rd[$rdIdx][11];
	echo "\r";

	for($i=$i; $i<33; $i++) {
		echo '"'.strtoupper(trim($rd[$rdIdx][$i])).'"'.',';
	}
	echo "\r";

	$ppn = 0;
	foreach($group1 as $value2){
		for($i=33; $i<44; $i++){
			echo '"'.strtoupper(trim($rd[$rdIdx][$i])).'"'.',';
			if($i == 41) $ppn += $rd[$rdIdx][41];
		}
		echo "\r";
		$rdIdx++;
	}
} 
?>