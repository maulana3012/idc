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
$left_loc	= "daily_billing_by_invoice.php";

$s_mode		= isset($_GET['s_mode']) ? $_GET['s_mode'] : "period";
$_marketing	= isset($_GET['cboFilterMarketing']) ? $_GET['cboFilterMarketing'] : "all";
$_order_by	= isset($_GET['cboFilterOrderBy']) ? $_GET['cboFilterOrderBy'] : $cboFilter[1][ZKP_FUNCTION][0][0];
$_dept		= isset($_GET['cboFilterDept']) ? $_GET['cboFilterDept'] : "all";
$_vat		= isset($_GET['cboFilterVat']) ? $_GET['cboFilterVat'] : ((ZKP_FUNCTION=='MEP')?"all":"vat-IO"); 
$_paper			= isset($_GET['cboFilterPaper']) ? $_GET['cboFilterPaper'] : "all";
$cboSearchType 	= isset($_GET['cboSearchType'])? $_GET['cboSearchType'] : "";
$txtSearch 		= isset($_GET['txtSearch'])? $_GET['txtSearch'] : "";

if($s_mode == 'period') {
	$some_date 		= "";
	$period_from 	= isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", mktime(0,0,0,date("m"),1,date("Y")));
	$period_to 		= isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", mktime(0,0,0,date("m")+1,0,date("Y")));
} elseif ($s_mode == 'date') {
	$some_date 		= $_GET['some_date'];
	$period_from 	= "";
	$period_to 		= "";
}

//SET WHERE PARAMETER
if(ZKP_FUNCTION == 'ALL') {
	if($_order_by != 'all'){
		$tmp[]	= "bill_ordered_by = $_order_by";
	}
} else {
	$tmp[]	= "bill_ordered_by = ".$cboFilter[1][ZKP_FUNCTION][0][0];
	$tmp[]	= ZKP_SQL."_isValidShowInvoice('".ZKP_FUNCTION."', bill_code,'billing')";
}

if($_marketing != "all") $tmp[] = "cus_responsibility_to = $_marketing";

if($_vat == 'vat') $tmp[] = "bill_vat > 0";
else if ($_vat == 'vat-IO') $tmp[] = "bill_vat > 0 AND bill_type_pajak = 'IO'";
else if ($_vat == 'vat-IP') $tmp[] = "bill_vat > 0 AND bill_type_pajak = 'IP'";
else if ($_vat == 'non') $tmp[] = "bill_vat = 0";

if($cboSearchType != '' && $txtSearch != '') {
	$type = array("byCity"=>"cus_city");
	$tmp[] = $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
	$get[] = "$cboSearchType=$txtSearch";
}

if ($some_date != "") $tmp[] = "bill_inv_date = DATE '$some_date'";
else $tmp[] = "bill_inv_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";

if($_dept != 'all') $tmp[] = "bill_dept = '$_dept'";

$strWhere = implode(" AND ", $tmp);

//DEFAULT LIST
$sql ="
SELECT
  cus_full_name,
  bill_code,
  to_char(bill_inv_date,'dd/Mon/YY') AS inv_date,
  bill_npwp,
  bill_vat_inv_no,
  (bill_total_billing - bill_delivery_freight_charge) * 100 / (bill_vat+100) AS amount,
  (bill_total_billing - bill_delivery_freight_charge) * 100 / (bill_vat+100) * bill_vat/100 AS vat,
  bill_total_billing - bill_delivery_freight_charge AS amount_vat,
  CASE
	WHEN b.bill_dept = 'A' THEN '../../apotik/billing/revise_billing.php?_code='||bill_code
	WHEN b.bill_dept = 'D' THEN '../../dealer/billing/revise_billing.php?_code='||bill_code
	WHEN b.bill_dept = 'H' THEN '../../hospital/billing/revise_billing.php?_code='||bill_code
	WHEN b.bill_dept = 'M' THEN '../../marketing/billing/revise_billing.php?_code='||bill_code
	WHEN b.bill_dept = 'P' THEN '../../pharmaceutical/billing/revise_billing.php?_code='||bill_code
	WHEN b.bill_dept = 'T' THEN '../../tender/billing/revise_billing.php?_code='||bill_code
  END AS go_page
FROM
  ".ZKP_SQL."_tb_customer AS c
  JOIN ".ZKP_SQL."_tb_billing AS b ON  bill_cus_to = cus_code
WHERE $strWhere
ORDER BY bill_code";

if(isZKError($result = & query($sql)))
	$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");
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

$(document).ready(function(){
	var url		= $("input[name$=web_url]").val();
	var dept	= $("input[name$=web_dept]").val();

	var ishideFilterOrderBy	= new Array("IDC","MED","MEP");
	var ishideFilterGroupBy	= new Array("dealer","hospital","maketing", "pharmaceutical", "tender");

	if(in_array(url, ishideFilterOrderBy)) $(".divOrderBy").hide();
	if(in_array(dept, ishideFilterGroupBy)) $(".divGroupBy").hide();
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
<form name="frmSearch" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
<input type="hidden" name="web_url" value="<?php echo ZKP_URL ?>">
<input type="hidden" name="web_dept" value="<?php echo $currentDept ?>">
<table width="100%" class="table_layout">
	<tr>
		<td rowspan="2" width="80%"><h3>[<font color="#446fbe">GENERAL</font>] BILLING SUMMARY by invoice</h3></td>
		<td><div class="divOrderBy"> ORDER BY </div></td>
		<td> SEARCH BY </td>
        <td> PAPER </td>
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
				<option value=""></option>
				<option value="byCity">CITY</option>
			</select>
			<input type="text" name="txtSearch" size="20" class="fmt" value="<?php echo $txtSearch; ?>">
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
	</tr>
</table><br />
<table width="100%" class="table_layout">
	<tr>
		<td rowspan="2" width="30%"> </td>
		<td> DEPT </td>
		<td> VAT </td>
		<td> MARKETING </td>
		<td> INVOICE DATE </td>
		<td> INVOICE PERIOD </td>
	</tr>
	<tr>
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
	        <select name="cboFilterMarketing" id="cboFilterMarketing" class="fmt">
                <option value="all">==SELECT==</option>
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
				<option value="lastMonth">LAST MONTH</option>
				<option value="thisMonth">THIS MONTH</option>
			</select>
			<input type="text" name="period_from" size="10" class="fmtd" value="<?php echo $period_from; ?>">&nbsp;
			<input type="text" name="period_to" size="10" class="fmtd"  value="<?php echo $period_to; ?>">
		</td>
	</tr>
</table><br />
</form>
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	var ts = <?php echo time() * 1000;?>;

	setSelect(f.cboPeriod, "<?php echo isset($_GET['cboPeriod']) ? $_GET['cboPeriod'] : "default"?>");
	setSelect(f.cboFilterOrderBy, "<?php echo isset($_GET['cboFilterOrderBy']) ? $_GET['cboFilterOrderBy'] : $cboFilter[1][ZKP_FUNCTION][0][0]?>");
	setSelect(f.cboFilterVat, "<?php echo isset($_GET['cboFilterVat']) ? $_GET['cboFilterVat'] : ((ZKP_FUNCTION=='MEP')?"all":"vat-IO") ?>");
	setSelect(f.cboFilterDept, "<?php echo isset($_GET['cboFilterDept']) ? $_GET['cboFilterDept'] : "all"?>");
	setSelect(f.cboSearchType, "<?php echo isset($_GET['cboSearchType']) ? $_GET['cboSearchType'] : ""?>");
	setSelect(f.cboFilterPaper, "<?php echo isset($_GET['cboFilterPaper']) ? $_GET['cboFilterPaper'] : "all"?>");

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

	f.cboFilterOrderBy.onchange = function() {
		if(f.some_date.value.length > 0) {
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

	f.cboFilterMarketing.onchange	= f.cboFilterOrderBy.onchange;
	f.cboFilterVat.onchange			= f.cboFilterOrderBy.onchange;
	f.cboFilterDept.onchange		= f.cboFilterOrderBy.onchange;
	f.cboFilterPaper.onchange		= f.cboFilterOrderBy.onchange;

	f.txtSearch.onkeypress = function() {
		if(window.event.keyCode == 13) {
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
<table width="100%" class="table_f">
	<tr>
		<th width="7%">INV. DATE</th>
		<th>CUSTOMER</th>
		<th width="15%">NPWP</th>
		<th width="13%">INVOICE NO</th>
		<th width="15%">FP. NO</th>
		<th width="8%">AMOUNT</th>
		<th width="8%">VAT</th>
		<th width="10%">AMOUNT<br>+VAT</th>
	</tr>
<?php 
$amount = 0;
$vat	= 0;
$total	= 0;
while ($columns =& fetchRowAssoc($result)) {

	echo "<tr>\n";
	cell($columns['inv_date'], ' align="center"');
	cell($columns['cus_full_name']);
	cell($columns['bill_npwp'], ' align="center"');
	cell_link($columns['bill_code'], ' align="center"', ' href="'.$columns['go_page'].'"');
	cell($columns['bill_vat_inv_no'], ' align="center"');
	cell(number_format((double)$columns['amount']),' align="right"');
	cell(number_format((double)$columns['vat']),' align="right"');
	cell(number_format((double)$columns['amount_vat']),' align="right"');
	echo "</tr>\n";

	$amount	+= $columns['amount'];
	$vat	+= $columns['vat'];
	$total	+= $columns['amount_vat'];
}
?>
	<tr>
		<td colspan="5" align="right" style="color:brown; background-color:lightyellow">GRAND TOTAL</td>
		<td align="right" style="color:brown; background-color:lightyellow"><?php echo number_format((double)$amount) ?></td>
		<td align="right" style="color:brown; background-color:lightyellow"><?php echo number_format((double)$vat) ?></td>
		<td align="right" style="color:brown; background-color:lightyellow"><?php echo number_format((double)$total) ?></td>
	</tr>
</table>
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