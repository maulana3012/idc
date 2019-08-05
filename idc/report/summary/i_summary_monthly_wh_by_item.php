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

$_cus_code		= isset($_GET['_cus_code']) ? $_GET['_cus_code'] : "";
$_cug_code		= isset($_GET['_cug_code']) ? $_GET['_cug_code'] : "all";
$_filter_doc	= isset($_GET['cboFilterDoc']) ? $_GET['cboFilterDoc'] : "all";
$_filter_vat	= isset($_GET['cboFilterVat']) ? $_GET['cboFilterVat'] : $cboFilter[2][ZKP_FUNCTION][0][0];
$_filter_dept	= isset($_GET['cboFilterDept']) ? $_GET['cboFilterDept'] : "all";
$_month_from	= isset($_GET['cboMonthFrom']) ? $_GET['cboMonthFrom'] : $period_default[0];
$_year_from		= isset($_GET['cboYearFrom']) ? $_GET['cboYearFrom'] : $period_default[1];
$_month_to		= isset($_GET['cboMonthTo']) ? $_GET['cboMonthTo'] : $period_default[2];
$_year_to		= isset($_GET['cboYearTo']) ? $_GET['cboYearTo'] : $period_default[3];
$period_from	= date('Y-n-d', mktime(0,0,0, $_month_from, 1, $_year_from));
$period_to		= date('Y-n-d', mktime(0,0,0, $_month_to+1, 1-1, $_year_to));
$_inc_med		= isset($_GET['chkIncMed']) ? $_GET['chkIncMed'] : "";
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

if(isset($_GET['lastCategoryNo']) && $_GET['lastCategoryNo'] > 0) {
	$_last_category = $_GET['lastCategoryNo'];

	//get category path from current icat_midx.
	if(isZKError($path = executeSP(ZKP_SQL."_getCategoryPath", $_last_category))) {
		$M->goErrorPage($path, HTTP_DIR . "$currentDept/$moduleDept/index.php");
	} else {
		eval(html_entity_decode($path[0]));	
		$path = array_reverse($path);
	}
	
} else {
	$_last_category	= 0;
}

$table_len	 = ($mon_length*50)+410;
?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISI-8859-1" />
<title>SUMMARY MONTHLY by item</title>
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
</head>
<body>
<table width="<?php echo $table_len?>px" class="table_layout">
	<tr>
		<td>
		<?php include "./report/rpt_summary_monthly_wh_by_item.php"; ?>
		</td>
	</tr>
</table>
</body>
</html>