<?php
//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$grade		= array('12th','11th','10th','9th','8th','7th','6th','5th','4th','3rd','2nd','1st');
$inv		= array(1=>12,11,10,9,8,7,6,5,4,3,2,1);
$month		= array();
$period_default = array();
	$period_default[0] = date('n');
	$period_default[1] = date('Y');
$_filter_vat	= isset($_GET['cboFilterVat']) ? $_GET['cboFilterVat'] : "all";
$_month			= isset($_GET['cboMonth']) ? $_GET['cboMonth'] : $period_default[0];
$_year			= isset($_GET['_year']) ? $_GET['_year'] : $period_default[1];

$_from_year	= date('Y', mktime(0,0,0, $_month-11, 1, $_year));
$_from_month= date('n', mktime(0,0,0, $_month-11, 1, $_year));
$_to_year	= $_year;
$_to_month	= $_month;

for($i=1; $i<=12; $i++) {
	if($_from_month>12) { $_from_year+=1; $_from_month=1; }
	if($_from_month<10) { $_from_month = '0'.$_from_month; }
	$month[$_from_month] = $i;
	$_from_month++;
}
?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISI-8859-1" />
<title>[GENERAL] SUMMARY ESTIMATE INCOME by department</title>
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
</head>
<body style="margin:8pt">
<table width="<?php echo $table_len?>px" class="table_layout">
	<tr>
		<td>
		<?php include "./report/rpt_summary_estimate_income_by_dept.php"; ?>
		</td>
	</tr>
</table>
</body>
</html>