<?php 
header('Expires: 0');
header('Cache-control: private');
header('Cache-Control: must-revalidate, GET-check=0, pre-check=0');
header('Content-Description: File Transfer');
header('Content-Type: application/vnd.ms-excel');
header('Content-disposition: attachment; filename="file_return_pajak.csv"'); 

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
		$tmp[]	= "turn_ordered_by = $_order_by";
	}
} else {
	$tmp[]	= "turn_ordered_by = ".$cboFilter[1][ZKP_FUNCTION][0][0];
	$tmp[]	= ZKP_SQL."_isValidShowInvoice('".ZKP_FUNCTION."', turn_code,'billing_return')";
}

//if($_marketing != "all") $tmp[] = "cus_responsibility_to = $_marketing";

if($_vat == 'vat') $tmp[] = "turn_vat > 0";
else if ($_vat == 'vat-IO') $tmp[] = "turn_vat > 0";
else if ($_vat == 'vat-IP') $tmp[] = "turn_vat > 0";
else if ($_vat == 'non') $tmp[] = "turn_vat = 0";

if ($some_date != "") $tmp[] = "turn_return_date = DATE '$some_date'";
else $tmp[] = "turn_return_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";

if($_dept != 'all') $tmp[] = "turn_dept = '$_dept'";

if($_code != '') $tmp[] = "turn_code IN ($_code)";

$strWhere = implode(" AND ", $tmp);

//DEFAULT LIST
$sql ="
SELECT 

'RK' AS var_rk,
(select case 
  when bill_npwp is null THEN '000000000000000'
  else substr(bill_npwp,1,2) || substr(bill_npwp,4,3) || substr(bill_npwp,8,3) || substr(bill_npwp,12,1) || substr(bill_npwp,14,3) || substr(bill_npwp,18,3)
end as npwp FROM ".ZKP_SQL."_tb_billing WHERE bill_code = a.turn_bill_code) AS var_npwp,
(select bill_pajak_to_name FROM ".ZKP_SQL."_tb_billing WHERE bill_code = a.turn_bill_code) AS var_nama,
'01' AS var_kd_jenis_transaksi,
'0' AS var_fg_pengganti,
(select substr(bill_vat_inv_no,5,3) || substr(bill_vat_inv_no,9,2) || substr(bill_vat_inv_no,12) FROM ".ZKP_SQL."_tb_billing WHERE bill_code = a.turn_bill_code) AS var_nomor_faktur,
(select to_char(bill_inv_date,'dd/mm/YYYY') FROM ".ZKP_SQL."_tb_billing WHERE bill_code = a.turn_bill_code) AS var_tanggal_faktur,
turn_code AS var_nomor_dokumen_retur,
to_char(turn_return_date,'dd/mm/YYYY') AS var_tanggal_retur,
to_char(turn_return_date,'mm')::int AS var_masa_pajak_retur,
to_char(turn_return_date,'YYYY')::int AS var_tahun_pajak_retur,
to_char(ROUND(((turn_total_return - turn_delivery_freight_charge) * 100) / (turn_vat+100)), '99999999999') AS var_nilai_retur_dpp,
to_char(ROUND(ROUND(((turn_total_return - turn_delivery_freight_charge) * 100) / (turn_vat+100)) * turn_vat/100), '99999999999') AS var_nilai_retur_ppn,
'0' AS var_tarif_ppnbm
FROM ".ZKP_SQL."_tb_return AS a
WHERE $strWhere
ORDER BY turn_return_date, var_kd_jenis_transaksi";
/*
echo "<pre>";
var_dump($sql);
echo "</pre>";
exit;
*/
echo '"RK","NPWP","NAMA","KD_JENIS_TRANSAKSI","FG_PENGGANTI","NOMOR_FAKTUR","TANGGAL_FAKTUR","NOMOR_DOKUMEN_RETUR","TANGGAL_RETUR","MASA_PAJAK_RETUR","TAHUN_PAJAK_RETUR","NILAI_RETUR_DPP","NILAI_RETUR_PPN","NILAI_RETUR_PPNBM"'."\r";

$res =& query($sql);
while($col =& fetchRow($res)) {
	for($i=0; $i<14; $i++) {
		echo '"'.htmlspecialchars_decode(trim($col[$i])).'"'.',';
	}
	echo "\r";
}

?>