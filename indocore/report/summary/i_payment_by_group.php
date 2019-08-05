<?php
//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

$month		= array(1=>'January','February','March','April','May','June','July','August','September','October','November','December');
$current_year = date('Y');
$year		= array();
	for($i=$current_year; $i>$current_year-10 ; $i--) {
		$year[$i] = $i;
	}

$s_mode		= isset($_GET['s_mode']) ? $_GET['s_mode'] : "period";
$_cug_code	= isset($_GET['_cug_code']) ? $_GET['_cug_code'] : "all";
$_marketing	= isset($_GET['cboFilterMarketing']) ? $_GET['cboFilterMarketing'] : "all";
$_order_by	= isset($_GET['cboFilterOrderBy']) ? $_GET['cboFilterOrderBy'] : $cboFilter[1][ZKP_FUNCTION][6][0];
$_chk_company	= isset($_GET['chkCompany']) ? $_GET['chkCompany'] : 'off';
$_filter_doc	= isset($_GET['cboFilterDoc']) ? $_GET['cboFilterDoc'] : "all";
$_vat		= isset($_GET['cboFilterVat']) ? $_GET['cboFilterVat'] : $cboFilter[2][ZKP_FUNCTION][0][0];
$_dept		= isset($_GET['cboFilterDept']) ? $_GET['cboFilterDept'] : "all";
$_method	= isset($_GET['cboMethod']) ? $_GET['cboMethod'] : "all";
$_bank		= isset($_GET['cboBank']) ? $_GET['cboBank'] : "all";
$_from_mon	= isset($_GET['cboFromMonth']) ? $_GET['cboFromMonth'] : "";
$_from_year	= isset($_GET['cboFromYear']) ? $_GET['cboFromYear'] : "";
$cboSearchType 	= isset($_GET['cboSearchType'])? $_GET['cboSearchType'] : "";
$txtSearch 	= isset($_GET['txtSearch'])? $_GET['txtSearch'] : "";

if($s_mode == 'period') {
	$some_date 		= "";
	$period_from 	= isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", time()- 2592000);
	$period_to 		= isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", time());
} elseif ($s_mode == 'date') {
	$some_date 		= $_GET['some_date'];
	$period_from 	= "";
	$period_to 		= "";
}
?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISI-8859-1" />
<title>Untitled Document</title>
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
</head>
<body>
<table width="1500px" class="table_layout">
	<tr>
		<td>
		<?php include "./report/rpt_payment_by_group.php"?>
		</td>
	</tr>
</table>
</body>
</html>