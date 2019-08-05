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
$left_loc 	  = "payment_by_date.php";
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
<?php
$sql_mkt = "SELECT ma_idx, ma_account, ma_display_as FROM tb_mbracc WHERE ma_display_as > 0 ORDER BY ma_account";
$res_mkt = & query($sql_mkt);
echo "var mkt = new Array();\n";
$i = 0;
while ($row =& fetchRow($res_mkt,0)) {
	if($row[2] & 1) $j='IDC';
	if($row[2] & 2) $j='MED';
	if($row[2] & 1 && $row[2] & 2) $j='ALL';
	if($row[2] == 4) $j=false;
	if($j != false) {
		echo "mkt['".$i++."'] = ['".$row[0]."','".strtoupper($row[1])."',".$row[2]."];\n";
	}
}
?>

function initOption() {
	for (i=0; i<mkt.length; i++) 
		addOption(document.frmSearch.cboFilterMarketing,mkt[i][1], mkt[i][0]);
	addOption(document.frmSearch.cboFilterMarketing,'PUSAT', '1000');
	setSelect(window.document.frmSearch.cboFilterMarketing, "<?php echo isset($_GET['cboFilterMarketing']) ? $_GET['cboFilterMarketing'] : "all"?>");
}

function fillOption(issubmit) {
	var f = window.document.frmSearch;
	var orderby	= window.document.frmSearch.cboFilterOrderBy.value;
	var method	= window.document.frmSearch.cboMethod.value;

	if (orderby == 'all') {
		if(method == 'check' || method == 'transfer' || method == 'giro') {
			addOption(document.frmSearch.cboBank,"BCA1", "BCA1");
			addOption(document.frmSearch.cboBank,"BCA2", "BCA2");
			addOption(document.frmSearch.cboBank,"MANDIRI", "MANDIRI");
			addOption(document.frmSearch.cboBank,"BII1", "BII1");
			addOption(document.frmSearch.cboBank,"BII2", "BII2");
			addOption(document.frmSearch.cboBank,"BII3", "BII3");
			addOption(document.frmSearch.cboBank,"DANAMON", "DANAMON");
			addOption(document.frmSearch.cboBank,"DANAMON2", "DANAMON2");
			addOption(document.frmSearch.cboBank,"BNI SYR", "BNIS");
		}
	} else if (orderby == '1') {
		if(method == 'check' || method == 'giro') {
			addOption(document.frmSearch.cboBank,"BII2", "BII2");
			addOption(document.frmSearch.cboBank,"DANAMON", "DANAMON");
			addOption(document.frmSearch.cboBank,"BNIS", "BNI SYR");
		} else if(method == 'transfer') {
			addOption(document.frmSearch.cboBank,"BCA1", "BCA1");
			addOption(document.frmSearch.cboBank,"BCA2", "BCA2");
			addOption(document.frmSearch.cboBank,"MANDIRI", "MANDIRI");
			addOption(document.frmSearch.cboBank,"BII1", "BII1");
			addOption(document.frmSearch.cboBank,"BII2", "BII2");
			addOption(document.frmSearch.cboBank,"DANAMON", "DANAMON");
			addOption(document.frmSearch.cboBank,"BNI SYR", "BNIS");
		}
	} else if (orderby == '2') {
		if(method == 'check' || method == 'transfer' || method == 'giro') {
			addOption(document.frmSearch.cboBank,"BII3", "BII3");
			addOption(document.frmSearch.cboBank,"BNI SYR", "BNIS");
		}
	} else if (orderby == '3') {
		if(method == 'check' || method == 'giro') {
			addOption(document.frmSearch.cboBank,"BII2", "BII2");
			addOption(document.frmSearch.cboBank,"DANAMON", "DANAMON");
			addOption(document.frmSearch.cboBank,"BNI SYR", "BNIS");
		} else if(method == 'transfer') {
			addOption(document.frmSearch.cboBank,"BCA1", "BCA1");
			addOption(document.frmSearch.cboBank,"BCA2", "BCA2");
			addOption(document.frmSearch.cboBank,"MANDIRI", "MANDIRI");
			addOption(document.frmSearch.cboBank,"BII1", "BII1");
			addOption(document.frmSearch.cboBank,"BII2", "BII2");
			addOption(document.frmSearch.cboBank,"DANAMON", "DANAMON");
			addOption(document.frmSearch.cboBank,"BNI SYR", "BNIS");
		}
	}

	if(issubmit) {
		if(f.some_date.value != '') {
			f.period_from.value = '';
			f.period_to.value = '';
			f.cboPeriod.value = '';
			f.s_mode.value = 'date';
		} else {
			f.some_date.value = '';
			f.s_mode.value = 'period';
		}
		f.submit();
	}
}

$(document).ready(function(){
	var url		= $("input[name$=web_url]").val();
	var dept	= $("input[name$=web_dept]").val();

	var ishideFilterOrderBy	= new Array("IDC","MED","MEP");
	var ishideFilterGroupBy	= new Array("dealer","hospital","maketing", "pharmaceutical", "tender");

	if(in_array(url, ishideFilterOrderBy)) $(".divOrderBy").hide();
	if(in_array(dept, ishideFilterGroupBy)) $(".divGroupBy").hide();

	fillOption(false);
	setSelect(f.cboBank, "<?php echo isset($_GET['cboBank']) ? $_GET['cboBank'] : "all"?>");
	initOption();

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
$_cug_code		= isset($_GET['_cug_code']) ? $_GET['_cug_code'] : "all";
$_marketing		= isset($_GET['cboFilterMarketing']) ? $_GET['cboFilterMarketing'] : "all";
$_order_by		= isset($_GET['cboFilterOrderBy']) ? $_GET['cboFilterOrderBy'] : $cboFilter[1][ZKP_FUNCTION][0][0];
$_filter_doc	= isset($_GET['cboFilterDoc']) ? $_GET['cboFilterDoc'] : "all";
$_vat			= isset($_GET['cboFilterVat']) ? $_GET['cboFilterVat'] : $cboFilter[2][ZKP_FUNCTION][0][0];
$_dept			= isset($_GET['cboFilterDept']) ? $_GET['cboFilterDept'] : "all";
$_method		= isset($_GET['cboMethod']) ? $_GET['cboMethod'] : "all";
$_bank			= isset($_GET['cboBank']) ? $_GET['cboBank'] : "all";
$cboSearchType 	= isset($_GET['cboSearchType'])? $_GET['cboSearchType'] : "";
$txtSearch 		= isset($_GET['txtSearch'])? $_GET['txtSearch'] : "";

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
<h3>[<font color="#446fbe">GENERAL</font>] PAYMENT SUMMARY by date</h3>
<form name="frmSearch" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
<input type="hidden" name="web_url" value="<?php echo ZKP_URL ?>">
<input type="hidden" name="web_dept" value="<?php echo $currentDept ?>">
<table width="100%" class="table_layout">
	<tr>
		<td width="80%" rowspan="2"></td>
		<td><div class="divOrderBy"> ORDER BY </div></td>
		<td><div class="divGroupBy"> PUSAT </div></td>
		<td> MARKETING </td>
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
		<td><div class="divGroupBy">
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
		</div></td>
		<td>
	        <select name="cboFilterMarketing" id="cboFilterMarketing" class="fmt">
                <option value="all">==SELECT==</option>
            </select>
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
		<td width="20%"> </td>
		<td> FILTER BY </td>
		<td> VAT </td>
		<td> DEPT </td>
		<td> METHOD </td>
		<td> BANK </td>
		<td> PAYMENT DATE </td>
		<td> PAYMENT PERIOD </td>
	</tr>
	<tr>
		<td> </td>
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
			<select name="cboMethod" id="cboMethod" onChange="fillOption(true)">
				<option value="all">==ALL==</option>
				<option value="cash">CASH</option>
				<option value="check">CHECK</option>
				<option value="transfer">T/S</option>
				<option value="giro">GIRO</option>
			</select>
		</td>
		<td>
			<select name="cboBank" id="cboBank">
            	<option value="all">==ALL==</option>
			</select>
		</td>
		<td valign="middle">
			<input type="hidden" name="s_mode">
			<a href="javascript:setDate(-1)"> <img src="../../_images/icon/arrow_left.gif" alt="Previous date"> </a>
			<input type="text" name="some_date" size="10" class="fmtd" value="<?php echo $some_date?>">
			<a href="javascript:setDate(1)"> <img src="../../_images/icon/arrow_right.gif" alt="Next date"> </a>
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
<iframe id="rightFrame" src="i_payment_by_date.php?<?php echo getQueryString()?>" frameborder="0" width="100%" height="1000px" name="iFrm"></iframe><br /><br />
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	var ts = <?php echo time() * 1000;?>;

	setSelect(f.cboPeriod, "<?php echo isset($_GET['cboPeriod']) ? $_GET['cboPeriod'] : "default"?>");
	setSelect(f._cug_code, "<?php echo isset($_GET['_cug_code']) ? $_GET['_cug_code'] : "all"?>");
	setSelect(f.cboFilterOrderBy, "<?php echo isset($_GET['cboFilterOrderBy']) ? $_GET['cboFilterOrderBy'] : $cboFilter[1][ZKP_FUNCTION][0][0]?>");
	setSelect(f.cboFilterVat, "<?php echo isset($_GET['cboFilterVat']) ? $_GET['cboFilterVat'] : $cboFilter[2][ZKP_FUNCTION][0][0]?>");
	setSelect(f.cboFilterDoc, "<?php echo isset($_GET['cboFilterDoc']) ? $_GET['cboFilterDoc'] : "all"?>");
	setSelect(f.cboFilterDept, "<?php echo isset($_GET['cboFilterDept']) ? $_GET['cboFilterDept'] : "all"?>");
	setSelect(f.cboMethod, "<?php echo isset($_GET['cboMethod']) ? $_GET['cboMethod'] : "all"?>");
	setSelect(f.cboSearchType, "<?php echo isset($_GET['cboSearchType']) ? $_GET['cboSearchType'] : "byPayment"?>");

	function setDate(value){
		var date = parseDate(f.some_date.value, 'prefer_euro_format');

		if(date == null) {date = new Date();}

		date.setDate(date.getDate()+value)
		f.some_date.value = formatDate(date, 'd-NNN-yyyy');
		f.period_from.value = '';
		f.period_to.value = '';
		f.cboPeriod.value = '';
		f.s_mode.value = 'date';
		f.submit();
	}

	f._cug_code.onchange = function() {
		if(f.some_date.value != '') {
			f.period_from.value = '';
			f.period_to.value = '';
			f.cboPeriod.value = '';
			f.s_mode.value = 'date';
		} else {
			f.some_date.value = '';
			f.s_mode.value = 'period';
		}
		f.submit();
	}

	f.cboFilterOrderBy.onchange		= f._cug_code.onchange;
	f.cboFilterVat.onchange			= f._cug_code.onchange;
	f.cboFilterDoc.onchange			= f._cug_code.onchange;
	f.cboFilterMarketing.onchange	= f._cug_code.onchange;
	f.cboFilterDept.onchange		= f._cug_code.onchange;
	f.cboBank.onchange				= f._cug_code.onchange;

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