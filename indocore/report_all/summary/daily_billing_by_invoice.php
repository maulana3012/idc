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
$left_loc = "daily_billing_by_invoice.php";

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
<script language="text/javascript" type="text/javascript" src="../../_script/js_category.php"></script>
<script language='text/javascript' type='text/javascript'>
function fillOption(target, pidx) {
    target.options.length = 1;
    for(var i = 0; i<icat.length; i++) {
        if (icat[i][1] == pidx) {
            target.options[target.options.length] = new Option(icat[i][4], icat[i][0]);
        }
    }

    window.document.frmSearch.lastCategoryNo.value = pidx;
}

function fillOptionInit() {
    fillOption(window.document.frmSearch.icat_1, 0);
<?php
//Set initial option value
if(isset($path) && is_array($path)) {
    $count = count($path);
    for($i = 1; $i < $count; $i++) {
        echo "\twindow.document.frmSearch.icat_$i.value = \"{$path[$i][0]}\";\n";
        if($i ==1 ) echo "\tfillOption(window.document.frmSearch.icat_".($i+1).", \"{$path[$i][0]}\");\n";
    }
}
?>
}

function resetOption() {
    window.document.frmSearch.icat_2.options.length = 1;
    window.document.frmSearch.lastCategoryNo.value = "";
}

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

$(document).ready(function(){
	var url		= $("input[name$=web_url]").val();
	var dept	= $("input[name$=web_dept]").val();

	var ishideFilterOrderBy	= new Array("IDC","MED","MEP");
	var ishideFilterGroupBy	= new Array("dealer","hospital","maketing", "pharmaceutical", "tender");

	if(in_array(url, ishideFilterOrderBy)) $(".divOrderBy").hide();
	if(in_array(dept, ishideFilterGroupBy)) $(".divGroupBy").hide();

	fillOptionInit();
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
$s_mode		= isset($_GET['s_mode']) ? $_GET['s_mode'] : "period";
$_cug_code	= isset($_GET['_cug_code']) ? $_GET['_cug_code'] : "all";
$_order_by	= isset($_GET['cboFilterOrderBy']) ? $_GET['cboFilterOrderBy'] : $cboFilter[1][ZKP_FUNCTION][6][0];
$_chk_company	= isset($_GET['chkCompany']) ? $_GET['chkCompany'] : 'off';
$_filter_doc	= isset($_GET['cboFilterDoc']) ? $_GET['cboFilterDoc'] : "all";
$_dept		= isset($_GET['cboFilterDept']) ? $_GET['cboFilterDept'] : "all";
$_vat		= isset($_GET['cboFilterVat']) ? $_GET['cboFilterVat'] : $cboFilter[2][ZKP_FUNCTION][0][0];
$_marketing	= isset($_GET['cboFilterMarketing']) ? $_GET['cboFilterMarketing'] : "all";
$_paper		= isset($_GET['cboFilterPaper']) ? $_GET['cboFilterPaper'] : "all";
$cboSearchType 	= isset($_GET['cboSearchType'])? $_GET['cboSearchType'] : "";
$txtSearch 	= isset($_GET['txtSearch'])? $_GET['txtSearch'] : "";

if($s_mode == 'period') {
	$some_date 		= "";
	$period_from 	= isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", time());
	$period_to 		= isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", time()+86400);
} elseif ($s_mode == 'date') {
	$some_date 		= isset($_GET['some_date'])? $_GET['some_date'] : date("j-M-Y", time());
	$period_from 	= "";
	$period_to 		= "";
}
?>
<form name="frmSearch" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET"><div id="up"> &nbsp; </div>
<input type="hidden" name="web_url" value="<?php echo ZKP_URL ?>">
<input type="hidden" name="web_dept" value="<?php echo $currentDept ?>">
<table width="100%" class="table_layout">
	<tr>
		<td width="90%" rowspan="2"><h3>[<font color="#446fbe">GENERAL</font>] BILLING SUMMARY by invoice</h3></td>
		<td><div class="divOrderBy"> ORDER BY </div></td>
		<td><div class="divGroupBy"> PUSAT </div></td>
		<td> CATEGORY </td>
	</tr>
	<tr>
		<td><div class="divOrderBy">
			<input type="checkbox" name="chkCompany" <?php echo ($_chk_company == 'on') ? ' checked' : '' ?>>
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
			<input type="hidden" name="lastCategoryNo" value="0">
            <select name="icat_1" onChange="fillOption(window.document.frmSearch.icat_2, this.value)" onClick="resetOption()">
                <option value="0">==ALL==</option>
            </select>
            <select name="icat_2">
                <option value="0">==ALL==</option>
            </select>
		</td>
	</tr>
</table><br />
<table width="100%" class="table_layout">
	<tr>
		<td rowspan="2" width="35%"> </td>
		<td> FILTER BY </td>
		<td> DEPT </td>
		<td> VAT </td>
		<td> PAPER </td>
		<td> MARKETING </td>
		<td> SEARCH BY </td>
		<td> INVOICE DATE </td>
		<td> INVOICE PERIOD </td>
	</tr>
	<tr>
		<td>
			<select name="cboFilterDoc">
				<option value="all">==ALL==</option>
				<option value="I">INVOICE</option>
				<option value="R">RETURN</option>
				<option value="DR">DR</option>
			</select>
		</td>
		<td>
			<select name="cboFilterDept">
				<option value="all">==ALL==</option>
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
			<select name="cboFilterVat">
<?php
foreach($cboFilter[2][ZKP_FUNCTION] as $val => $key) {
	echo "\t\t\t\t<option value=\"".$key[0]."\">".$key[1]."</option>\n";
}
?>
			</select>
		</td>
		<td>
			 <select name="cboFilterPaper">
				<option value="all">==ALL==</option>
				<option value="0">No. &amp; Item</option>
				<option value="1">No. Only</option>
				<option value="A">A</option>
				<option value="B">B</option>
			</select>
		</td>
		<td>
			<select name="cboFilterMarketing" id="cboFilterMarketing" class="fmt">
			    <option value="all">==SELECT==</option>
			</select>
		</td>
		<td>
			<select name="cboSearchType">
				<option value=""></option>
				<option value="byCity">CITY</option>
			</select>
			<input type="text" name="txtSearch" size="20" class="fmt" value="<?php echo $txtSearch; ?>">
		</td>
		<td valign="middle">
			<input type="hidden" name="s_mode">
			<a href="javascript:setFilterDate('date',-1)"> <img src="../../_images/icon/arrow_left.gif" alt="Previous date"> </a>
			<input type="text" name="some_date" size="10" class="fmtd" value="<?php echo $some_date?>">
			<a href="javascript:setFilterDate('date',1)"> <img src="../../_images/icon/arrow_right.gif" alt="Next date"> </a>
		</td>
		<td>
			<a href="javascript:setFilterDate('period',-1)"> <img src="../../_images/icon/arrow_left.gif" alt="Previous month"> </a>
			<input type="text" name="period_from" size="10" class="fmtd" value="<?php echo $period_from; ?>">&nbsp;
			<input type="text" name="period_to" size="10" class="fmtd"  value="<?php echo $period_to; ?>">
			<a href="javascript:setFilterDate('period',1)"> <img src="../../_images/icon/arrow_right.gif" alt="Next month"> </a>
		</td>
	</tr>
</table>
</form>
<div align="right">
	<a href="./daily_billing_by_data.php?<?php echo getQueryString()?>">Export Data</a> &nbsp; | &nbsp;
	<a href="./daily_billing_by_group.php?<?php echo getQueryString()?>">View By Group</a> &nbsp; | &nbsp;
	<a href="./daily_billing_by_item.php?<?php echo getQueryString()?>">View By Item</a>
</div>
<table width="100%" class="table_layout">
	<tr>
		<td>
			<a href="#BN-A">[BN] Medisindo Non Pajak <b>A</b></a> / <a href="#BN-Z"><b>Z</b></a> | &nbsp; 
			<a href="#BO-A">[BO] Medisindo Pajak <b>A</b></a> / <a href="#BO-Z"><b>Z</b></a> | &nbsp; 
			<a href="#SO-A">[SO] Samudia Pajak <b>A</b></a> / <a href="#SO-Z"><b>Z</b></a> 
		</td>
		<td align="right">
			<a href="#DR-A">[DR] DO Replace <b>A</b></a> / <a href="#DR-Z"><b>Z</b></a> | &nbsp; 
			<a href="#RR-A">[RR] Return Replace <b>A</b></a> / <a href="#RR-Z"><b>Z</b></a> | &nbsp; 
			<a href="#RF-A">[RF] Return Pajak <b>A</b></a> / <a href="#RF-Z"><b>Z</b></a> | &nbsp; 
			<a href="#RN-A">[RN] Return Non Pajak <b>A</b></a> / <a href="#RN-Z"><b>Z</b></a> 
		</td>
	</tr>
</table><br />	
<?php include "./report/rpt_daily_billing_detail_by_invoice.php"; ?>
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	var ts = <?php echo time() * 1000;?>;

	setSelect(f._cug_code, "<?php echo isset($_GET['_cug_code']) ? $_GET['_cug_code'] : "all"?>");
	setSelect(f.cboFilterOrderBy, "<?php echo isset($_GET['cboFilterOrderBy']) ? $_GET['cboFilterOrderBy'] : $cboFilter[1][ZKP_FUNCTION][6][0]?>");
	setSelect(f.cboFilterDoc, "<?php echo isset($_GET['cboFilterDoc']) ? $_GET['cboFilterDoc'] : "all"?>");
	setSelect(f.cboFilterDept, "<?php echo isset($_GET['cboFilterDept']) ? $_GET['cboFilterDept'] : "all"?>");
	setSelect(f.cboFilterVat, "<?php echo isset($_GET['cboFilterVat']) ? $_GET['cboFilterVat'] : $cboFilter[2][ZKP_FUNCTION][0][0] ?>");
	setSelect(f.cboFilterMarketing, "<?php echo isset($_GET['cboFilterMarketing']) ? $_GET['cboFilterMarketing'] : "all"?>");
	setSelect(f.cboSearchType, "<?php echo isset($_GET['cboSearchType']) ? $_GET['cboSearchType'] : ""?>");
	setSelect(f.cboFilterPaper, "<?php echo isset($_GET['cboFilterPaper']) ? $_GET['cboFilterPaper'] : "all"?>");

	var last_category = 0;
<?php 
if (isset($_GET['icat_2']) && $_GET['icat_2'] != 0)
	echo "\tlast_category = {$_GET['icat_2']};\n";
else if(isset($_GET['icat_1']) && $_GET['icat_1'] != 0)
	echo "\tlast_category = {$_GET['icat_1']};\n";
?>

	function setFilterDate(status, value){
		f.s_mode.value = status;
		if(status == 'date') {
			var date = parseDate(f.some_date.value, 'prefer_euro_format');
			setFilterDateCalc(date, value, f.some_date);
			f.period_from.value = '';
			f.period_to.value = '';
		} else if(status == 'period') {
			var d = new Date(ts);
			setFilterPeriodCalc(d, value, f.period_from, f.period_to);
		}
		f.lastCategoryNo.value = last_category;
		f.submit();
	}

	f._cug_code.onchange = function() {
		if(f.some_date.value != '') {
			f.period_from.value = '';
			f.period_to.value = '';
			f.s_mode.value = 'date';
		} else {
			f.some_date.value = '';
			f.s_mode.value = 'period';
		}
		f.lastCategoryNo.value = last_category;
		f.submit();
	}

	f.cboFilterOrderBy.onchange	= f._cug_code.onchange;
	f.cboFilterDoc.onchange		= f._cug_code.onchange;
	f.cboFilterDept.onchange	= f._cug_code.onchange;
	f.cboFilterVat.onchange		= f._cug_code.onchange;
	f.cboFilterMarketing.onchange	= f._cug_code.onchange;	
	f.cboFilterPaper.onchange	= f._cug_code.onchange;
	f.chkCompany.onclick		= f._cug_code.onchange;

	f.icat_1.onchange	  = function() {
		if(f.some_date.value != '') {
			f.period_from.value = '';
			f.period_to.value = '';
			f.s_mode.value = 'date';
		} else {
			f.some_date.value = '';
			f.s_mode.value = 'period';
		}
		f.lastCategoryNo.value = this.value;
		f.submit();
	}
	f.icat_2.onchange  = f.icat_1.onchange;

	f.txtSearch.onkeypress = function() {
		if(window.event.keyCode == 13) {
			if(f.some_date.value != '') {
				f.period_from.value = '';
				f.period_to.value = '';
				f.s_mode.value = 'date';
			} else {
				f.some_date.value = '';
				f.s_mode.value = 'period';
			}
			f.lastCategoryNo.value = last_category;
			f.submit();
		}
	}

	f.some_date.onkeypress = function() {
		if(window.event.keyCode == 13 && validDate(f.some_date)) {
			f.s_mode.value = 'date';
			f.lastCategoryNo.value = last_category;
			f.submit();
		}
	}

	f.period_from.onkeypress = function() {
		if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
			f.s_mode.value = 'period';
			f.lastCategoryNo.value = last_category;
			f.submit();
		}
	}

	f.period_to.onkeypress = function() {
		if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
			f.s_mode.value = 'period';
			f.lastCategoryNo.value = last_category;
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