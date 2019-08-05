<?php
//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

$month		= array(1=>'January','February','March','April','May','June','July','August','September','October','November','December');
$month_x	= array(0=>'January','February','March','April','May','June','July','August','September','October','November','December');
$current_year = date('Y');
$year_from	= array();
	for($i=$current_year; $i>$current_year-10 ; $i--) {
		$year_from[$i] = $i;
	}
$year_to	= array();
	for($i=$current_year+1; $i>$current_year-10 ; $i--) {
		$year_to[$i] = $i;
	}
$period_default = array();
	$period_default[0] = date('n');//(date('n')==12) ? 1 : date('n')+1;
	$period_default[1] = date('Y');//(date('n')<12) ? date('Y')-1 : date('Y');
	$period_default[2] = date('n');
	$period_default[3] = date('Y');

$_filter_date = isset($_GET['cboDateBy']) ? $_GET['cboDateBy'] : "document_date";
$_cug_code	= isset($_GET['_cug_code']) ? $_GET['_cug_code'] : "all";
$_dept		= isset($_GET['_dept']) ? $_GET['_dept'] : "all";
$_vat		= isset($_GET['cboVat']) ? $_GET['cboVat'] : "all";
$_source	= isset($_GET['cboFilterSource']) ? $_GET['cboFilterSource'] : "all";
$_order_by	= isset($_GET['cboOrderBy']) ? $_GET['cboOrderBy'] : "all";
$_month_from	= isset($_GET['cboMonthFrom']) ? $_GET['cboMonthFrom'] : $period_default[0];
$_year_from		= isset($_GET['cboYearFrom']) ? $_GET['cboYearFrom'] : $period_default[1];
$_month_to		= isset($_GET['cboMonthTo']) ? $_GET['cboMonthTo'] : $period_default[2];
$_year_to		= isset($_GET['cboYearTo']) ? $_GET['cboYearTo'] : $period_default[3];
$period_from	= date('Y-n-d', mktime(0,0,0, $_month_from, 1, $_year_from));
$period_to		= date('Y-n-d', mktime(0,0,0, $_month_to+1, 1-1, $_year_to));
$mon_length		= 0;
	for ($i=$_year_from; $i<=$_year_to; $i++) {
		if($_year_from==$_year_to)	{$start_mon=$_month_from; $end_month=$_month_to;}
		else if($i==$_year_from)	{$start_mon=$_month_from; $end_month=12;}
		else if($i==$_year_to)		{$start_mon=1; $end_month=$_month_to;}
		else if($i==$_year_from)	{$start_mon=1; $end_month=$_month_to;}
		else			 			{$start_mon=1; $end_month=12;}

		for($j=$start_mon; $j<=$end_month; $j++) {
			$mon_length++;
		}
	}

$period_month = array();
$start_month = $_month_from;
$start_year	 = $_year_from;
for($i=0; $i<$mon_length; $i++) {
	if($start_month>12) { $start_month = $start_month-12; $start_year+=1;}
	$_from_date	= date('Y-n-d', mktime(0,0,0, $start_month, 1, $start_year));
	$_to_date	= date('Y-n-d', mktime(0,0,0, $start_month+1, 1-1, $start_year));
	$period_month[$i] = "BETWEEN DATE '$_from_date 00:00:00' AND '$_to_date 23:59:59'";
	$start_month++;
}

$table_len	 = ($mon_length*50)+630;
?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISI-8859-1" />
<title>ED SUMMARY by monthly</title>
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
</head>
<body>
<table width="<?php echo $table_len?>px" class="table_layout">
	<tr>
		<td>
        <?php require_once APP_DIR . "_include/warehouse/report/summary/rpt_summary_monthly_item_by_ed_customer.php" ?>
		</td>
	</tr>
</table>
</body>
</html>