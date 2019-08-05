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
$left_loc		= "daily_billing_by_invoice.php";

$s_mode			= isset($_GET['s_mode']) ? $_GET['s_mode'] : "period";
$_sort_by		= isset($_GET['cboSortBy']) ? $_GET['cboSortBy'] : "bill_vat_inv_no";
$_order_by		= isset($_GET['cboFilterOrderBy']) ? $_GET['cboFilterOrderBy'] : $cboFilter[1][ZKP_FUNCTION][0][0];
$_vat			= isset($_GET['cboFilterVat']) ? $_GET['cboFilterVat'] : ((ZKP_FUNCTION=='MEP')?"all":"vat-IO"); 
$_paper			= isset($_GET['cboFilterPaper']) ? $_GET['cboFilterPaper'] : "all";
$cboSearchType 	= isset($_GET['cboSearchType'])? $_GET['cboSearchType'] : "";
$txtSearch 		= isset($_GET['txtSearch'])? $_GET['txtSearch'] : "";
$cboFilterPdf   = isset($_GET['cboFilterPdf'])? $_GET['cboFilterPdf'] : "";
$cboFilterEmail = isset($_GET['cboFilterEmail'])? $_GET['cboFilterEmail'] : "";

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

if($_vat == 'vat') $tmp[] = "bill_vat > 0";
else if ($_vat == 'vat-IO') $tmp[] = "bill_vat > 0 AND bill_type_pajak = 'IO'";
else if ($_vat == 'vat-IP') $tmp[] = "bill_vat > 0 AND bill_type_pajak = 'IP'";
else if ($_vat == 'non') $tmp[] = "bill_vat = 0";

if($cboSearchType != '' && $txtSearch != '') {
	$type = array("byCity"=>"cus_city");
	$tmp[] = $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
	$GET[] = "$cboSearchType=$txtSearch";
}

if ($some_date != "") $tmp[] = "bill_inv_date = DATE '$some_date'";
else $tmp[] = "bill_inv_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";

if($cboFilterEmail != '') $tmp[] = "bill_is_fp_delivery = '$cboFilterEmail'";

if($cboFilterPdf == 'true') $tmp[] = ZKP_SQL."_getpdf(bill_code) is not null";
else if($cboFilterPdf == 'false') $tmp[] = ZKP_SQL."_getpdf(bill_code) is null";

$tmp[] = "bill_dept = '$department'";

$strWhere = implode(" AND ", $tmp);

//DEFAULT LIST
$sql ="
SELECT
  bill_pajak_to_name AS cus_full_name,
  bill_code,
  to_char(bill_inv_date,'dd/Mon/YY') AS inv_date,
  bill_npwp,
  bill_vat_inv_no,
  bill_is_fp_delivery,  
  ".ZKP_SQL."_getpdf(bill_code) AS pdf_pajak,
  CASE 
  	WHEN '".ZKP_URL."' = 'MED' AND bill_pajak_to = '0MSD' THEN 
  		TRUNC(((bill_total_billing - bill_delivery_freight_charge) * 100 / (bill_vat+100)) * 0.888888888888888,0)
  	ELSE (bill_total_billing - bill_delivery_freight_charge) * 100 / (bill_vat+100) 
  END AS amount,
  CASE 
  	WHEN '".ZKP_URL."' = 'MED' AND bill_pajak_to = '0MSD' THEN 
  		TRUNC(TRUNC(((bill_total_billing - bill_delivery_freight_charge) * 100 / (bill_vat+100)) * 0.888888888888888,0)*0.1,0)
  	ELSE
  		(bill_total_billing - bill_delivery_freight_charge) * 100 / (bill_vat+100) * bill_vat/100 
  END AS vat,
  CASE 
  	WHEN '".ZKP_URL."' = 'MED' AND bill_pajak_to = '0MSD' THEN 
  		TRUNC(bill_total_billing * 0.888888888888888,0)- bill_delivery_freight_charge
  	ELSE
  		bill_total_billing - bill_delivery_freight_charge 
  END AS amount_vat,
  CASE
	WHEN b.bill_dept = 'A' THEN '../../apotik/billing/revise_billing.php?_code='||bill_code
	WHEN b.bill_dept = 'D' THEN '../../dealer/billing/revise_billing.php?_code='||bill_code
	WHEN b.bill_dept = 'H' THEN '../../hospital/billing/revise_billing.php?_code='||bill_code
	WHEN b.bill_dept = 'M' THEN '../../marketing/billing/revise_billing.php?_code='||bill_code
	WHEN b.bill_dept = 'P' THEN '../../pharmaceutical/billing/revise_billing.php?_code='||bill_code
	WHEN b.bill_dept = 'T' THEN '../../tender/billing/revise_billing.php?_code='||bill_code
  END AS go_page,
  CASE
    WHEN ".ZKP_SQL."_getemail(bill_pajak_to, bill_ship_to) is not null THEN 'check_small.gif'
    ELSE 'delete.gif'
   END AS email_pajak
FROM
  ".ZKP_SQL."_tb_customer AS c
  JOIN ".ZKP_SQL."_tb_billing AS b ON  bill_cus_to = cus_code
WHERE $strWhere
ORDER BY substr($_sort_by,4)";
/*
echo "<pre>";
var_dump($sql);
echo "</pre>";
exit;
*/
if(isZKError($result = & query($sql)))
	$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");

$numRow = numQueryRows($result);
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
$(document).ready(function(){
	var url		= $("input[name$=web_url]").val();
	var dept	= $("input[name$=web_dept]").val();

	var ishideFilterOrderBy	= new Array("IDC","MED","MEP");
	var ishideFilterGroupBy	= new Array("dealer","hospital","maketing", "pharmaceutical", "tender");

	if(in_array(url, ishideFilterOrderBy)) $(".divOrderBy").hide();
	if(in_array(dept, ishideFilterGroupBy)) $(".divGroupBy").hide();

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
		<td> SORT BY </td>
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
			 <select name="cboSortBy">
				<option value="bill_code">Bill Code</option>
				<option value="bill_vat_inv_no">Faktur Pajak No.</option>
			</select>
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
		<td rowspan="2" width="50%"> </td>
		<td> VAT </td>
		<td> PDF PAJAK </td>
		<td> EMAIL </td>
		<td> INVOICE DATE </td>
		<td> INVOICE PERIOD </td>
	</tr>
	<tr>
		<td>
			<select name="cboFilterVat">
				<option value="all">==ALL==</option>
				<option value="vat">VAT</option>
				<option value="vat-IO">VAT - IO</option>
				<option value="vat-IP">VAT - IP</option>
			</select>
		</td>
		
		<td valign="middle">
			<select name="cboFilterPdf">
				<option value="">==ALL==</option>
				<option value="true">TRUE</option>
				<option value="false">FALSE</option>
			</select>
		</td>
		<td valign="middle">
			<select name="cboFilterEmail">
				<option value="">==ALL==</option>
				<option value="t">Delivery</option>
				<option value="f">Undelivery</option>
			</select>
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
	<tr>
		<td colspan="6" align="right"></td>
	</tr>
</table>

Total : <?php echo $numRow ?>
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	var ts = <?php echo time() * 1000;?>;

	setSelect(f.cboSortBy, "<?php echo isset($_GET['cboSortBy']) ? $_GET['cboSortBy'] : "bill_vat_inv_no"?>");
	setSelect(f.cboFilterOrderBy, "<?php echo isset($_GET['cboFilterOrderBy']) ? $_GET['cboFilterOrderBy'] : $cboFilter[1][ZKP_FUNCTION][0][0]?>");
	setSelect(f.cboFilterVat, "<?php echo isset($_GET['cboFilterVat']) ? $_GET['cboFilterVat'] : ((ZKP_FUNCTION=='MEP')?"all":"vat-IO") ?>");
	setSelect(f.cboSearchType, "<?php echo isset($_GET['cboSearchType']) ? $_GET['cboSearchType'] : ""?>");
	setSelect(f.cboFilterPaper, "<?php echo isset($_GET['cboFilterPaper']) ? $_GET['cboFilterPaper'] : "all"?>");
	setSelect(f.cboFilterPdf, "<?php echo isset($_GET['cboFilterPdf']) ? $_GET['cboFilterPdf'] : ""?>");
	setSelect(f.cboFilterEmail, "<?php echo isset($_GET['cboFilterEmail']) ? $_GET['cboFilterEmail'] : ""?>");

	
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
		f.submit();
	}

	f.cboFilterOrderBy.onchange = function() {
		if(f.some_date.value.length > 0) {
			f.period_from.value = '';
			f.period_to.value = '';
			f.s_mode.value = 'date';
		} else {
			f.some_date.value = '';
			f.s_mode.value = 'period';
		}
		f.submit();
	}

	f.cboFilterVat.onchange			= f.cboFilterOrderBy.onchange;
	f.cboFilterPaper.onchange		= f.cboFilterOrderBy.onchange;
	f.cboFilterEmail.onchange		= f.cboFilterOrderBy.onchange;
	f.cboFilterPdf.onchange			= f.cboFilterOrderBy.onchange;
	f.cboSortBy.onchange			= f.cboFilterOrderBy.onchange;

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
			f.submit();
		}
	}
	
	f.some_date.onkeypress = function() {
		if(window.event.keyCode == 13 && validDate(f.some_date)) {
			f.s_mode.value = 'date';
			f.submit();
		}
	}

	f.period_from.onkeypress = function() {
		if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
			f.s_mode.value = 'period';
			f.submit();
		}
	}

	f.period_to.onkeypress = function() {
		if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
			f.s_mode.value = 'period';
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
		<th width="3%">PDF PAJAK</th>
		<th width="3%">EMAIL</th>
		<th width="3%">CUS<br />EMAIL</th>
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
	echo "<td align='center'>";
	if($columns['pdf_pajak'] != ''){ echo "<img src='../../_images/icon/check_small.gif' width='10' height='11' align='absmiddle'>"; }else{ echo "<img src='../../_images/icon/delete.gif' width='10' height='11' align='absmiddle'>"; }
	echo "</td>";
	echo "<td align='center'>";
	if($columns['bill_is_fp_delivery']=='t'){ echo "<img src='../../_images/icon/check_small.gif' width='10' height='11' align='absmiddle'>"; }else{ echo "<img src='../../_images/icon/delete.gif' width='10' height='11' align='absmiddle'>"; }
	echo "</td>";	
	cell('<img src="../../_images/icon/'.$columns['email_pajak'].'" width="10" height="11" align="absmiddle">',' align="center"');
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