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
$left_loc	= "summary_estimate_income_by_dept.php";
$month		= array(1=>'January','February','March','April','May','June','July','August','September','October','November','December');
$period_default = array();
	$period_default[0] = date('n');
	$period_default[1] = date('Y');

$_filter_vat	= isset($_GET['cboFilterVat']) ? $_GET['cboFilterVat'] : "all";
$_month			= isset($_GET['cboMonth']) ? $_GET['cboMonth'] : $period_default[0];
$_year			= isset($_GET['_year']) ? $_GET['_year'] : $period_default[1];
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
<h3>[<font color="#446fbe">GENERAL</font>] SUMMARY ESTIMATE INCOME by department</h3>
<form name="frmSearch" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
<input type='hidden' name='p_mode' value='print'>
<table width="100%" class="table_box">
	<tr>
		<td width="80%"></td>
		<td> TYPE INV. </td>
		<td> PERIOD </td>
		<th width="5%" rowspan="2">
			<a href="javascript:submitSearch()"><img src="../../_images/icon/search_mini.gif"></a><!-- &nbsp;
			<a href="javascript:submitPrint()"><img src="../../_images/icon/print.gif"></a> &nbsp;
			<a href="javascript:submitGoToArchieves()"><img src="../../_images/icon/list_mini.gif" alt="See archieves"></a>-->
		</th>
	</tr>
	<tr>
		<td> </td>
		<td>
			<select name="cboFilterVat" onchange="submitSearch()">
				<option value="all">==ALL==</option>
				<option value="vat">VAT</option>
				<option value="non">NON VAT</option>
			</select>
		</td>
		<td>
			<select name="cboMonth">
<?php foreach($month as $value => $key) { 
echo "\t\t\t\t<option value=\"$value\">$key</option>\n";
}?>
			</select> &nbsp;
			<input type="text" name="_year" class="fmt" style="width:40px" value="<?php echo $_year?>">
		</td>
	</tr>
</table><br />
</form>
<iframe id="rightFrame" src="i_summary_estimate_income_by_dept.php?<?php echo getQueryString()?>" frameborder="0" width="100%" height="500px" name="iFrm"></iframe><br /><br />
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	var ts = <?php echo time() * 1000;?>;

	setSelect(f.cboFilterVat, "<?php echo isset($_GET['cboFilterVat']) ? $_GET['cboFilterVat'] : "all"?>");
	setSelect(f.cboMonth, "<?php echo isset($_GET['cboMonth']) ? $_GET['cboMonth'] : $period_default[0] ?>");

	function submitSearch() {
		if(f._year.value.length < 4 || parseInt(f._year.value.length) == false) {
			alert("Please input year in complete digit");
			f._year.focus();
			return;
		}
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
/*
	function submitPrint() {
		window.document.frmSearch.submit();
	}

	function submitGoToArchieves() {
		window.location.href = "../../_user_data/report/";
	}
*/
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