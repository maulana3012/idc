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
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "summary_monthly_item_by_ed_customer.php";
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
<form name="frmSearch" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
<table width="100%" class="table_layout">
	<tr>
		<td width="85%" rowspan="2"><strong class="title">[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] ED SUMMARY by monthly<br /><br /></strong></td>
		<td> ORDER BY </td>
		<td> SOURCE </td>
	</tr>
	<tr>
		<td>
			<select name="cboOrderBy">
				<option value="all">==ALL==</option>
<?php
foreach($cboFilter[3]['do'][ZKP_SQL] as $val => $key) {
	echo "\t\t\t\t<option value=\"".$key[0]."\">".$key[1]."</option>\n";
}
?>
			</select>
		</td>
		<td>
			<select name="cboFilterSource">
				<option value="all">==ALL==</option>
				<optgroup label="-Out Stock-" style="font-style:italic;">
					<option value="out">Outgoing</option>
					<option value="out-1">Billing</option>
					<option value="out-2">Order</option>
					<option value="out-3">DT</option>
					<option value="out-4">DF</option>
					<option value="out-5">DR</option>
					<option value="out-6">DM</option>
				</optgroup>
				<optgroup label="-In Stock-" style="font-style:italic;">
					<option value="in">Incoming</option>
					<option value="in-1">Return Billing</option>
					<option value="in-2">Return Order</option>
					<option value="in-3">Return DT</option>
				</optgroup>
			</select>
		</td>
	</tr>
</table>
<table width="100%" class="table_layout">
	<tr>
		<td width="25%"></td>
		<td> PUSAT </td>
		<td> DEPT </td>
		<td> TYPE </td>
		<td> SORT DATE BY </td>
		<td> PERIOD FROM </td>
		<td> PERIOD TO </td>
		<td bgcolor="#F3F3F3" width="5%" align="center" rowspan="2" style="background-color:#00000">
			<a href="javascript:checkValidityPeriod()"><img src="../../_images/icon/search_mini.gif"></a>
		</td>
	</tr>
	<tr>	
		<td></td>
		<td>
<?php
	$sql = "SELECT cug_code, substr(cug_name, 1, 13) || '...' AS cus_name FROM ".ZKP_SQL."_tb_customer_group ORDER BY cug_name";
	isZKError($result = & query($sql)) ? $M->printMessage($result):0;

	if(numQueryRows($result) <= 0) {
		$o = new ZKError("INFORMATION", "INFORMATION", "Please register the $arg first. you can find the [new ". ucfirst($arg) ."] under the BASIC DATA menu");
		$M->printMessage($result);
	} else {
		print "<select name=\"_cug_code\" class=\"req\">\n";
		print "\t<option value=\"all\">==ALL==</option>\n";
	
		while ($columns = fetchRow($result)) {
			print "\t<option value=\"".$columns[0]."\">".$columns[1]."</option>\n";
		}
		print "</select>\n";
	}
?>
		</td>
		<td>
			<select name="_dept">
				<option value="all">==ALL==</option>
				<option value="A">A</option>
				<option value="D">D</option>
				<option value="H">H</option>
				<option value="M">M</option>
				<option value="P">P</option>
				<option value="T">T</option>
				<option value="S">CS</option>
				<option value="DEMO">DEMO</option>
			</select>
		</td>
		<td>
			<select name="cboVat">
				<option value="all">==ALL==</option>
				<option value="1">VAT</option>
				<option value="2">NON VAT</option>
			</select>
		</td>
		<td>
			<select name="cboDateBy">
				<option value="document_date">DOCUMENT DATE</option>
				<option value="confirm_date">CONFIRM DATE</option>
			</select>
		</td>
		<td>
			<select name="cboMonthFrom">
<?php foreach($month as $value => $key) { 
echo "\t\t\t\t<option value=\"$value\">$key</option>\n";
}?>
			</select> 
			<select name="cboYearFrom">
<?php foreach($year_from as $value => $key) { 
echo "\t\t\t\t<option value=\"$value\">$key</option>\n";
}?>
			</select> &nbsp;
		</td>
		<td>
			<select name="cboMonthTo">
<?php foreach($month as $value => $key) { 
echo "\t\t\t\t<option value=\"$value\">$key</option>\n";
}?>
			</select> 
			<select name="cboYearTo">
<?php foreach($year_to as $value => $key) { 
echo "\t\t\t\t<option value=\"$value\">$key</option>\n";
}?>
			</select>
		</td>
	</tr>
</table><br />
</form>
<iframe id="rightFrame" src="i_summary_monthly_item_by_ed_customer.php?<?php echo getQueryString()?>" frameborder="0" width="100%" height="2000px" name="iFrm"></iframe><br /><br />
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	var ts = <?php echo time() * 1000;?>;

	setSelect(f.cboDateBy, "<?php echo isset($_GET['cboDateBy']) ? $_GET['cboDateBy'] : "document_date"?>");
	setSelect(f._cug_code, "<?php echo isset($_GET['_cug_code']) ? $_GET['_cug_code'] : "all"?>");
	setSelect(f._dept, "<?php echo isset($_GET['_dept']) ? $_GET['_dept'] : "all"?>");
	setSelect(f.cboVat, "<?php echo isset($_GET['cboVat']) ? $_GET['cboVat'] : "all"?>");
	setSelect(f.cboFilterSource, "<?php echo isset($_GET['cboFilterSource']) ? $_GET['cboFilterSource'] : "all"?>");
	setSelect(f.cboOrderBy, "<?php echo isset($_GET['cboOrderBy']) ? $_GET['cboOrderBy'] : "all"?>");
	setSelect(f.cboMonthFrom, "<?php echo isset($_GET['cboMonthFrom']) ? $_GET['cboMonthFrom'] : $period_default[0] ?>");
	setSelect(f.cboYearFrom, "<?php echo isset($_GET['cboYearFrom']) ? $_GET['cboYearFrom'] : $period_default[1] ?>");
	setSelect(f.cboMonthTo, "<?php echo isset($_GET['cboMonthTo']) ? $_GET['cboMonthTo'] : $period_default[2] ?>");
	setSelect(f.cboYearTo, "<?php echo isset($_GET['cboYearTo']) ? $_GET['cboYearTo'] : $period_default[3] ?>");

	function submitSearch() {
		f.submit();
	}

	function checkValidityPeriod() {
		var f = window.document.frmSearch;

		var month_from	= parseInt(f.cboMonthFrom.value);
		var year_from	= parseInt(f.cboYearFrom.value);
		var month_to	= parseInt(f.cboMonthTo.value);
		var year_to		= parseInt(f.cboYearTo.value);

		//Check validity From - To
		var d1 = parseDate('1-'+month_from+'-'+year_from, 'prefer_euro_format');
		var d2 = parseDate('1-'+month_to+'-'+year_to, 'prefer_euro_format');
		if (d1.getTime() > d2.getTime()) {
			alert("Period to must be future than Period from");
			return;
		}

		//Check max month
		var count_month	= 0;
		var start_mon	= 0;
		var end_month	= 0;
		var con	= '';
		for (var i=year_from; i<=year_to; i++) {

			if(year_from==year_to)	{con=1; start_mon=month_from; end_month=month_to;}
			else if(i==year_from)	{con=2; start_mon=month_from; end_month=12;}
			else if(i==year_to)		{con=3; start_mon=1; end_month=month_to;}
			else			 		{con=4; start_mon=1; end_month=12;}

			for(var j=start_mon; j<=end_month; j++) {
				count_month++;
			}
		}
		if(count_month>12) {
			alert("Your selected period more than 12 months");
			return;
		}
		submitSearch();
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