<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 29-May, 2007 23:50:32
* @author    : daesung kim
*/
//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, HTTP_DIR . "<script language=\"javascript1.2\">window.close();</script>");

//GLOBAL
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
	$period_default[0] = (date('n')==12) ? 1 : date('n')+1;
	$period_default[1] = (date('n')<12) ? date('Y')-1 : date('Y');
	$period_default[2] = date('n');
	$period_default[3] = date('Y');

$_filter_dept	=  "A";
if(isset($_GET['cboFilterDept']) && $_GET['cboFilterDept'] != 'all') {
	$_filter_dept	=  $_GET['cboFilterDept'];
}
$_filter_group	= isset($_GET['cboFilterGroup']) ? $_GET['cboFilterGroup'] : "all";
$_filter_vat	= isset($_GET['cboFilterVat']) ? $_GET['cboFilterVat'] : "all";
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

$grade			= array('1st','2nd','3rd','4th','5th','6th','7th','8th','9th','10th','11th','12th');
$period_month	= array();
$start_month = $_month_from;
$start_year	 = $_year_from;
for($i=0; $i<12; $i++) {
	if($start_month>12) { $start_month = $start_month-12; $start_year+=1;}
	$_from_date	= date('Y-n-d', mktime(0,0,0, $start_month, 1, $start_year));
	$_to_date	= date('Y-n-d', mktime(0,0,0, $start_month+1, 1-1, $start_year));
	$period_month[$i] = "BETWEEN DATE '$_from_date' AND '$_to_date'";
	$start_month++;
}
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
</head>
<body style="margin:8px">
<h3>[<font color="#446fbe">GENERAL</font>] SUMMARY MONTHLY by payment percentage</h3>
<form name="frmSearch" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
<input type="hidden" name="cboFilterGroup" value="<?php echo $_filter_group ?>">
<input type="hidden" name="cboFilterVat" value="<?php echo $_filter_vat ?>">
<input type="hidden" name="cboMonthFrom" value="<?php echo $_month_from ?>">
<input type="hidden" name="cboYearFrom" value="<?php echo $_year_from ?>">
<input type="hidden" name="cboMonthTo" value="<?php echo $_month_to ?>">
<input type="hidden" name="cboYearTo" value="<?php echo $_year_to ?>">
<table width="100%" class="table_layout">
	<tr>
		<td width="30%"></td>
		<td> DEPT </td>
		<td> PUSAT </td>
		<td> TYPE INV. </td>
		<td> PERIOD FROM </td>
		<td> PERIOD TO </td>
		<td bgcolor="#F3F3F3" width="5%" align="center" rowspan="2" style="background-color:#00000">
			<a href="javascript:window.close()"><img src="../../_images/icon/delete_2.gif"></a>
		</td>
	</tr>
	<tr>
		<td> </td>
		<td>
			<select name="cboFilterDept" onchange="submitSearch()">
				<option value="A">APOTIK</option>
				<option value="D">DEALER</option>
				<option value="H">HOSPITAL</option>
				<option value="P">PHARMACEUTICAL</option>
			</select>
		</td>
		<td>
<?php
$sql = "SELECT cug_code, substr(cug_name, 1, 13) || '...' AS cus_name FROM ".ZKP_SQL."_tb_customer_group ORDER BY cug_name";
isZKError($result = & query($sql)) ? $M->printMessage($result):0;

if(numQueryRows($result) <= 0) {
	$o = new ZKError("INFORMATION", "INFORMATION", "Please register the $arg first. you can find the [new ". ucfirst($arg) ."] under the BASIC DATA menu");
	$M->printMessage($result);
} else {
	print "<select name=\"cboFilterGroupX\" class=\"req\" onchange=\"submitSearch()\" disabled>\n";
	print "\t<option value=\"all\">==ALL==</option>\n";

	while ($columns = fetchRow($result)) {
		print "\t<option value=\"".$columns[0]."\">".$columns[1]."</option>\n";
	}
	print "</select>\n";
}
?>
		</td>
		<td>
			<select name="cboFilterVatX" disabled>
				<option value="all">==ALL==</option>
				<option value="vat">VAT</option>
				<option value="non">NON VAT</option>
			</select>
		</td>
		<td>
			<select name="cboMonthFromX" disabled>
				<option value="<?php echo $_month_from ?>"><?php echo $month[$_month_from] ?></value>
			</select> 
			<select name="cboYearFromX" disabled>
				<option value="<?php echo $_year_from ?>"><?php echo $_year_from ?></value>
			</select> &nbsp;
		</td>
		<td>
			<select name="cboMonthToX" disabled>
				<option value="<?php echo $_month_to ?>"><?php echo $month[$_month_to] ?></value>
			</select> 
			<select name="cboYearToX" disabled>
				<option value="<?php echo $_year_to ?>"><?php echo $_year_to ?></value>
			</select>
		</td>
	</tr>
</table>
</form><br />
<?php include "./report/rpt_summary_monthly_by_payment_percentage.php"; ?>
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	var ts = <?php echo time() * 1000;?>;

	setSelect(f.cboFilterDept, "<?php echo isset($_GET['cboFilterDept']) ? $_GET['cboFilterDept'] : "A"?>");
	setSelect(f.cboFilterGroupX, "<?php echo isset($_GET['cboFilterGroup']) ? $_GET['cboFilterGroup'] : "all"?>");
	setSelect(f.cboFilterVatX, "<?php echo isset($_GET['cboFilterVat']) ? $_GET['cboFilterVat'] : "all"?>");
	setSelect(f.cboMonthFromX, "<?php echo isset($_GET['cboMonthFrom']) ? $_GET['cboMonthFrom'] : $period_default[0] ?>");
	setSelect(f.cboYearFromX, "<?php echo isset($_GET['cboYearFrom']) ? $_GET['cboYearFrom'] : $period_default[1] ?>");
	setSelect(f.cboMonthToX, "<?php echo isset($_GET['cboMonthTo']) ? $_GET['cboMonthTo'] : $period_default[2] ?>");
	setSelect(f.cboYearToX, "<?php echo isset($_GET['cboYearTo']) ? $_GET['cboYearTo'] : $period_default[3] ?>");

	function submitSearch() {
		f.submit();
	}
</script>
</body>
</html>