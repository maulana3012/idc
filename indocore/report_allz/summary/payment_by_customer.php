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

//Global
$left_loc	= "payment_by_customer.php";
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script src="../../_script/jQuery.js" type="text/javascript"></script>
<script language='text/javascript' type='text/javascript'>
function fillCustomer(code) {

	var x = (screen.availWidth - 400) / 2;
	var y = (screen.availHeight - 600) / 2;
	var win = window.open(
		'<?php echo HTTP_DIR . "$currentDept/$moduleDept/" ?>p_list_cus_code.php?_check_code='+ code,
		'billing',
		'scrollbars,width=400,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

$(document).ready(function(){
	var url		= $("input[name$=web_url]").val();
	var ishideFilterOrderBy	= new Array("IDC","MED","MEP");
	if(in_array(url, ishideFilterOrderBy)) $(".divOrderBy").hide();
});
</script>
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
<?php
$s_mode			= isset($_GET['s_mode']) ? $_GET['s_mode'] : "period";
$_cus_code		= isset($_GET['_cus_code']) ? $_GET['_cus_code'] : "";
$_order_by		= isset($_GET['cboFilterOrderBy']) ? $_GET['cboFilterOrderBy'] : $cboFilter[1][ZKP_FUNCTION][0][0];
$_filter_doc	= isset($_GET['cboFilterDoc']) ? $_GET['cboFilterDoc'] : "all";
$_vat			= isset($_GET['cboFilterVat']) ? $_GET['cboFilterVat'] : $cboFilter[2][ZKP_FUNCTION][0][0];
$cboSearchType 	= isset($_GET['cboSearchType'])? $_GET['cboSearchType'] : "";
$txtSearch 		= isset($_GET['txtSearch'])? $_GET['txtSearch'] : "";
$_dept			= isset($_GET['cboFilterDept']) ? $_GET['cboFilterDept'] : "all";

if($s_mode == 'period') {
	$some_date 		= "";
	$period_from 	= isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", time()-2592000);
	$period_to 		= isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", time());
} elseif ($s_mode == 'date') {

	if(isset($_GET['some_date'])) {
		$some_date = $_GET['some_date'];
	} else {
		$some_date = date('j-M-Y');
		$_GET['cboDate'] = "0";
	}

	$period_from 		= "";
	$period_to 			= "";
}
?>
<h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] PAYMENT SUMMARY by customer</h3>
<form name="frmSearch" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
<input type="hidden" name="web_url" value="<?php echo ZKP_URL ?>">
<input type="hidden" name="web_dept" value="<?php echo $currentDept ?>">
<table width="100%" class="table_layout">
	<tr>
		<td width="80%" rowspan="2"></td>
		<td><div class="divOrderBy"> ORDER BY </div></td>
		<td> SEARCH BY REMARK </td>
	</tr>
	<tr>
		<td><div class="divOrderBy">
			<select name="cboFilterOrderBy">
<?php
foreach($cboFilter[1][ZKP_FUNCTION] as $val => $key) {
	echo "\t\t\t\t<option value=\"".$key[0]."\">".$key[1]."</option>\n";
}
?>
			</select></div>
		</td>
		<td>
			<select name="cboSearchType">
				<option value="byPayment">PAYMENT</option>
				<option value="byDeduction">DEDUCTION</option>
			</select> &nbsp; 
			<input type="text" name="txtSearch" size="25" class="fmt" value="<?php echo $txtSearch ?>">
		</td>
	</tr>
</table><br />
<table width="100%" class="table_layout">
	<tr>
		<td width="5%" rowspan="2"></td>
		<td> CUS CODE </td>
		<td> FILTER BY </td>
		<td> VAT </td>
        <td> DEPT </td>
		<td> PAYMENT DATE </td>
		<td> PAYMENT PERIOD </td>
	</tr>
	<tr>
		<td>
			<input type="text" name="_cus_code" size="10" class="fmt" onClick="fillCustomer('')" value="<?php echo $_cus_code?>" readonly>
		</td>
		<td>
			<select name="cboFilterDoc">
				<option value="all">==ALL==</option>
				<option value="I">INVOICE</option>
				<option value="R">RETURN</option>
				<option value="CT">CROSS TS</option>
			</select>
		</td>
		<td>
			<select name="cboFilterVat">
<?php
foreach($cboFilter[2][ZKP_FUNCTION] as $val => $key) {
	echo "\t\t\t\t<option value=\"".$key[0]."\">".$key[1]."</option>\n";
}
?>
			</select>
		</td>
        <td>
			<select name="cboFilterDept">
				<option value="all">=ALL=</option>
				<option value="A">A</option>
				<option value="D">D</option>
				<option value="H">H</option>
				<option value="M">M</option>
				<option value="P">P</option>
				<option value="T">T</option>
				<option value="S">CS</option>
			</select>
		</td>
		<td>
			<input type="hidden" name="s_mode">
				<select name="cboDate">
				<option value=""></option>
					<option value="-1">YESTERDAY</option>
					<option value="0">TODAY</option>
					<option value="1">TOMORROW</option>
				</select>
			<input type="text" name="some_date" size="10" class="fmtd" value="<?php echo $some_date?>">
		</td>
		<td>
			<select name="cboPeriod">
				<option value=""></option>
				<option value="lastWeek">LAST WEEK</option>
				<option value="lastMonth">LAST MONTH</option>
				<option value="thisWeek">THIS WEEK</option>
				<option value="thisMonth">THIS MONTH</option>
			</select>
			<input type="text" name="period_from" size="10" class="fmtd" value="<?php echo $period_from; ?>">&nbsp;
			<input type="text" name="period_to" size="10" class="fmtd"  value="<?php echo $period_to; ?>">
		</td>
	</tr>
</table><br />
</form>
<iframe id="rightFrame" src="i_payment_by_customer.php?<?php echo getQueryString()?>" frameborder="0" width="100%" height="1000px" name="iFrm"></iframe><br /><br />
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	var ts = <?php echo time() * 1000;?>;

	setSelect(f.cboDate, "<?php echo isset($_GET['cboDate']) ? $_GET['cboDate'] : "default"?>");
	setSelect(f.cboPeriod, "<?php echo isset($_GET['cboPeriod']) ? $_GET['cboPeriod'] : "default"?>");
	setSelect(f.cboFilterOrderBy, "<?php echo isset($_GET['cboFilterOrderBy']) ? $_GET['cboFilterOrderBy'] : $cboFilter[1][ZKP_FUNCTION][0][0] ?>");
	setSelect(f.cboFilterVat, "<?php echo isset($_GET['cboFilterVat']) ? $_GET['cboFilterVat'] : $cboFilter[2][ZKP_FUNCTION][0][0]?>");
	setSelect(f.cboFilterDoc, "<?php echo isset($_GET['cboFilterDoc']) ? $_GET['cboFilterDoc'] : "all"?>");
	setSelect(f.cboSearchType, "<?php echo isset($_GET['cboSearchType']) ? $_GET['cboSearchType'] : "byPayment"?>");
	setSelect(f.cboFilterDept, "<?php echo isset($_GET['cboFilterDept']) ? $_GET['cboFilterDept'] : "all"?>");

	f.cboFilterDoc.onchange = function() {
		if(f.cboDate.value != '' || f.some_date.value != '') {
			f.period_from.value = '';
			f.period_to.value = '';
			f.cboPeriod.value = '';
			f.s_mode.value = 'date';
		} else {
			f.some_date.value = '';
			f.s_mode.value = 'period';
			f.cboDate.value = '';
		}
		f.submit();
	}

	f.cboFilterVat.onchange			= f.cboFilterDoc.onchange;
	f.cboFilterOrderBy.onchange		= f.cboFilterDoc.onchange;
	f.cboFilterDept.onchange		= f.cboFilterDoc.onchange;

	f.txtSearch.onkeypress = function() {
		if(window.event.keyCode == 13) {
			if(f.some_date.value.length != 0) {
				if(validDate(f.some_date)) {
					f.s_mode.value = 'date';
					f.cboPeriod.value = '';
					f.submit();
				}
			} else {
				if(validPeriod(f.period_from, f.period_to)) {
					f.s_mode.value = 'period';
					f.cboPeriod.value = '';
					f.submit();
				}
			}
		}
	}

	f.cboDate.onchange = function() {
		setDate(ts, this.value, f.some_date);
		if (f.some_date.value != "" || f.some_date.value != '') {
			f.period_from.value = '';
			f.period_to.value = '';
			f.cboPeriod.value = '';
			f.s_mode.value = 'date';
			f.submit();
		}
	}
	
	f.cboPeriod.onchange = function() {
		if (this.value != "") {
			setPeriod(ts, this.value, f.period_from, f.period_to);
			f.some_date.value = '';
			f.s_mode.value = 'period';
			f.submit();
		}
	}
	
	f.some_date.onkeypress = function() {
		if(window.event.keyCode == 13 && validDate(f.some_date)) {
			f.s_mode.value = 'date';
			f.cboPeriod.value = '';
			f.submit();
		}
	}

	f.period_from.onkeypress = function() {
		if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
			f.s_mode.value = 'period';
			f.cboPeriod.value = '';
			f.submit();
		}
	}

	f.period_to.onkeypress = function() {
		if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
			f.s_mode.value = 'period';
			f.cboPeriod.value = '';
			f.submit();
		}
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