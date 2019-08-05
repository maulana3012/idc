<?php header('Expires: 0');
header('Cache-control: private');
header('Cache-Control: must-revalidate, GET-check=0, pre-check=0');
header('Content-Description: File Transfer');
header('Content-Type: application/vnd.ms-excel');
header('Content-disposition: attachment; filename="file_pajak.csv"'); 

//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//Global
$s_mode			= isset($_GET['s_mode']) ? $_GET['s_mode'] : "period";
$_marketing		= isset($_GET['cboFilterMarketing']) ? $_GET['cboFilterMarketing'] : "all";
$_order_by		= isset($_GET['cboFilterOrderBy']) ? $_GET['cboFilterOrderBy'] : $cboFilter[1][ZKP_FUNCTION][0][0];
$_dept			= isset($_GET['cboFilterDept']) ? $_GET['cboFilterDept'] : "all";
$_vat			= isset($_GET['cboFilterVat']) ? $_GET['cboFilterVat'] : ((ZKP_FUNCTION=='MEP')?"all":"vat-IO"); 
$_paper			= isset($_GET['cboFilterPaper']) ? $_GET['cboFilterPaper'] : "all";
$cboSearchType 	= isset($_GET['cboSearchType'])? $_GET['cboSearchType'] : "";
$txtSearch 		= isset($_GET['txtSearch'])? $_GET['txtSearch'] : "";

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

if($cboSearchType != '' && $txtSearch != '') {
	$type = array("byCity"=>"cus_city");
	$tmp[] = $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
	$GET[] = "$cboSearchType=$txtSearch";
}

if ($some_date != "") $tmp[] = "bill_inv_date = DATE '$some_date'";
else $tmp[] = "bill_inv_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";

if($_dept != 'all') $tmp[] = "bill_dept = '$_dept'";

$strWhere = implode(" AND ", $tmp);

//DEFAULT LIST
$sql ="
SELECT
 'A' AS var_kode_pajak,
 '2' AS var_kode_transaksi,
 cus_tax_code_status AS var_kode_status,
 '1' AS var_kode_dokumen,
 '0' AS var_flag_vat,
  bill_npwp AS var_npwp, 
  cus_full_name AS var_nama_lawan_transaksi,
  bill_vat_inv_no AS var_nomor_faktur,
  '0' AS var_jenis_dokumen,
  
  '' AS var_nomor_faktur_pengganti,
  '' AS var_nomor_jenis_dokumen_pengganti,
  to_char(bill_inv_date,'dd/mm/YYYY') AS var_tanggal_faktur,
  '' AS var_tanggal_spp,
  to_char(bill_inv_date,'mmmm') AS var_masa_pajak,
  to_char(bill_inv_date,'YYYY') AS var_tahun_pajak,
  
  '0' AS var_pembetulan,
  to_char((bill_total_billing - bill_delivery_freight_charge) * 100 / (bill_vat+100), '999999999') AS var_dpp,
  to_char((bill_total_billing - bill_delivery_freight_charge) * 100 / (bill_vat+100) * bill_vat/100, '999999999') AS var_ppn,
  '0' AS ppnbm
FROM
  ".ZKP_SQL."_tb_customer AS c
  JOIN ".ZKP_SQL."_tb_billing AS b ON  bill_cus_to = cus_code
WHERE $strWhere
ORDER BY bill_code";

//echo "<pre>";
//echo $sql;
//echo "</pre>";


echo "Kode Pajak;Kode Transaksi;Kode Status;Kode Dokumen;Flag VAT;NPWP / Nomor Paspor;Nama Lawan Transaksi;Nomor Faktur / Dokumen;Jenis Dokumen;Nomor Faktur Pengganti / Retur;Jenis Dokumen Dokumen Pengganti / Retur;Tanggal Faktur / Dokumen;Tanggal SSP;Masa Pajak;Tahun Pajak;Pembetulan;DPP;PPN;PPnBM\r";
$result = & query($sql);
while ($columns =& fetchRow($result)) {
	for($i=0; $i<19; $i++) {
		if($i==5) {
			$columns[$i] = str_replace(array('.','-',' '), array(''), $columns[$i]);
			echo $columns[$i].';';
		} else if($i==18)  echo '0';
		else echo substr(html_entity_decode($columns[$i], ENT_QUOTES),0,50).';';
	}
	echo "\r";
}
?>