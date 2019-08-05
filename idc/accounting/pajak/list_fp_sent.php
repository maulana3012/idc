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
$left_loc        = "list_fp_sent.php";

//PROCESS FORM
require_once "tpl_process_form.php"; 

//DEFAULT PROCESS
$s_mode     = isset($_GET['s_mode']) ? $_GET['s_mode'] : "period";
$_data      = isset($_GET['cboData']) ? $_GET['cboData'] : "invoice";
$_show      = isset($_GET['cboShowCheckBox']) ? $_GET['cboShowCheckBox'] : "excel";
$_sort_by   = isset($_GET['cboSortBy']) ? $_GET['cboSortBy'] : "bill_vat_inv_no";
$_order_by  = isset($_GET['cboFilterOrderBy']) ? $_GET['cboFilterOrderBy'] : $cboFilter[1][ZKP_FUNCTION][0][0];
$cboSearchType = isset($_GET['cboSearchType'])? $_GET['cboSearchType'] : "pajak_name";
$txtSearch  = isset($_GET['txtSearch'])? $_GET['txtSearch'] : "";
$_dept      = isset($_GET['cboFilterDept']) ? $_GET['cboFilterDept'] : "all";
$_status    = isset($_GET['cboFilterStatus']) ? $_GET['cboFilterStatus'] : "";
$_has_pdf   = isset($_GET['cboFilterPdf']) ? $_GET['cboFilterPdf'] : "";

if($s_mode == 'period') {
    $some_date       = "";
    $period_from     = isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", mktime(0,0,0,date("m"),1,date("Y")));
    $period_to       = isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", mktime(0,0,0,date("m")+1,0,date("Y")));
} elseif ($s_mode == 'date') {
    $some_date       = $_GET['some_date'];
    $period_from     = "";
    $period_to       = "";
}

//SET WHERE PARAMETER
if($_data == "invoice") {
    $tmp["turn"][] = "turn_code IS NULL";
} else if($_data == "return") {
    $tmp["bill"][] = "bill_code IS NULL";
}

if(ZKP_FUNCTION == 'ALL') {
    if($_order_by != 'all'){
        $tmp["bill"][]    = "bill_ordered_by = $_order_by";
        $tmp["turn"][]    = "turn_ordered_by = $_order_by";
    }
} else {
    $tmp["bill"][]    = "bill_ordered_by = ".$cboFilter[1][ZKP_FUNCTION][0][0];
    $tmp["turn"][]    = "turn_ordered_by = ".$cboFilter[1][ZKP_FUNCTION][0][0];
    $tmp["bill"][]    = ZKP_SQL."_isValidShowInvoice('".ZKP_FUNCTION."', bill_code,'billing')";
    $tmp["turn"][]    = ZKP_SQL."_isValidShowInvoice('".ZKP_FUNCTION."', turn_code,'billing_return')";
}

if($cboSearchType != "" && $txtSearch != "") {
    if($cboSearchType == "pajak_code") {
        $tmp["bill"][] = "bill_pajak_to ILIKE '%$txtSearch%'";
        $tmp["turn"][] = "turn_cus_to ILIKE '%$txtSearch%'";
    } elseif($cboSearchType == "pajak_name") {
        $tmp["bill"][] = "bill_pajak_to_name ILIKE '%$txtSearch%'";
        $tmp["turn"][] = "turn_cus_to_name ILIKE '%$txtSearch%'";
    } else if($cboSearchType == "ship_code") {
        $tmp["bill"][] = "bill_ship_to ILIKE '%$txtSearch%'";
        $tmp["turn"][] = "turn_ship_to ILIKE '%$txtSearch%'";
    } elseif($cboSearchType == "ship_name") {
        $tmp["bill"][] = "bill_ship_to_name ILIKE '%$txtSearch%'";
        $tmp["turn"][] = "turn_ship_to_name ILIKE '%$txtSearch%'";
    } elseif($cboSearchType == "bill_code") {
        $tmp["bill"][] = "bill_code ILIKE '%$txtSearch%'";
        $tmp["turn"][] = "bill_code ILIKE '%$txtSearch%'";
    } 
}

if ($some_date != "") {
    $tmp["bill"][] = "bill_inv_date = DATE '$some_date'";
    $tmp["turn"][] = "turn_return_date = DATE '$some_date'";
} else {
    $tmp["bill"][] = "bill_inv_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
    $tmp["turn"][] = "turn_return_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
}

if($_dept != 'all') {
    $tmp["bill"][] = "bill_dept = '$_dept'";
    $tmp["turn"][] = "turn_dept = '$_dept'";
}

if($_status != '') {
    $tmp["bill"][] = "bill_is_fp_delivery = '$_status'";
}

if($_has_pdf == 'true')       $tmp["bill"][] = ZKP_SQL."_getpdf(bill_code) is not null";
else if($_has_pdf == 'false') $tmp["bill"][] = ZKP_SQL."_getpdf(bill_code) is null";

$tmp["bill"][] = "bill_vat > 0";
$tmp["turn"][] = "turn_vat > 0";

$strWhere["bill"] = implode(" AND ", $tmp["bill"]);
$strWhere["turn"] = implode(" AND ", $tmp["turn"]);

//DEFAULT LIST
$sql ="
SELECT
  'billing' as type,
  bill_pajak_to_name AS cus_full_name,
  bill_code,
  to_char(bill_inv_date,'dd/Mon/YY') AS inv_date,
  bill_npwp,
  bill_is_fp_delivery,
  bill_vat_inv_no,
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
    WHEN b.bill_dept = 'S' THEN '../../sales/billing/revise_billing.php?_code='||bill_code
    WHEN b.bill_dept = 'T' THEN '../../tender/billing/revise_billing.php?_code='||bill_code
  END AS go_page,
  (SELECT count(billf_idx) FROM ".ZKP_SQL."_tb_billing_file WHERE bill_code = b.bill_code AND billf_file_type = 'Faktur Pajak') AS is_has_pdf,
  (SELECT count(billf_idx) FROM ".ZKP_SQL."_tb_billing_file WHERE bill_code = b.bill_code AND billf_file_type = 'Faktur Pajak Rev') AS is_has_pdfp,
  ".ZKP_SQL."_getemail(bill_pajak_to, bill_ship_to) AS email_pajak,
  substr($_sort_by,4) as sort_col
FROM
  ".ZKP_SQL."_tb_customer AS c
  JOIN ".ZKP_SQL."_tb_billing AS b ON  bill_cus_to = cus_code
WHERE ".$strWhere["bill"]."


UNION


SELECT
  'return' as type,
  (select bill_pajak_to_name FROM ".ZKP_SQL."_tb_billing WHERE bill_code = b.turn_bill_code) AS cus_full_name,
  turn_code as bill_code,
  to_char(turn_return_date,'dd/Mon/YY') AS inv_date,
  (select bill_npwp FROM ".ZKP_SQL."_tb_billing WHERE bill_code = b.turn_bill_code) AS bill_npwp,
  null as bill_is_fp_delivery,
  (select bill_vat_inv_no FROM ".ZKP_SQL."_tb_billing WHERE bill_code = b.turn_bill_code) AS bill_vat_inv_no,
  ROUND((turn_total_return - turn_delivery_freight_charge) * 100 / (turn_vat+100)) AS amount,
  ROUND(ROUND((turn_total_return - turn_delivery_freight_charge) * 100 / (turn_vat+100)) * turn_vat/100) AS vat,
  turn_total_return - turn_delivery_freight_charge AS amount_vat,
  CASE
    WHEN b.turn_dept = 'A' THEN '../../apotik/billing/revise_return.php?_code='||turn_code
    WHEN b.turn_dept = 'D' THEN '../../dealer/billing/revise_return.php?_code='||turn_code
    WHEN b.turn_dept = 'H' THEN '../../hospital/billing/revise_return.php?_code='||turn_code
    WHEN b.turn_dept = 'M' THEN '../../marketing/billing/revise_return.php?_code='||turn_code
    WHEN b.turn_dept = 'P' THEN '../../pharmaceutical/billing/revise_return.php?_code='||turn_code
    WHEN b.turn_dept = 'S' THEN '../../sales/billing/revise_return.php?_code='||turn_code
    WHEN b.turn_dept = 'T' THEN '../../tender/billing/revise_return.php?_code='||turn_code
  END AS go_page,
  null AS is_has_pdf,
  null AS is_has_pdfp,
  null AS email_pajak,
  (select substr($_sort_by,4) FROM ".ZKP_SQL."_tb_billing WHERE bill_code = b.turn_bill_code) AS sort_col
FROM
  ".ZKP_SQL."_tb_customer AS c
  JOIN ".ZKP_SQL."_tb_return AS b ON  turn_cus_to = cus_code
WHERE ".$strWhere["turn"]."

ORDER BY sort_col
";
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
    var url        = $("input[name$=web_url]").val();
    var dept    = $("input[name$=web_dept]").val();

    var ishideFilterOrderBy    = new Array("IDC","MED","MEP");
    var ishideFilterGroupBy    = new Array("dealer","hospital","maketing", "pharmaceutical", "tender");

    if(in_array(url, ishideFilterOrderBy)) $(".divOrderBy").hide();
    if(in_array(dept, ishideFilterGroupBy)) $(".divGroupBy").hide();

    <?php if($_show == "excel") { ?>
    window.document.all.btnSummarize.disabled = true;
    <?php } else if($_show == "mail") { ?>
    window.document.all.btnGenerateExcel.disabled = true;
    <?php } else { ?>
    window.document.all.btnSummarize.disabled = true;
    window.document.all.btnGenerateExcel.disabled = true;
    <?php } ?>

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
        <td rowspan="2" width="90%"><h3>[<font color="#446fbe">GENERAL</font>] SEND MAIL FAKTUR PAJAK </h3></td>
        <td><div class="divOrderBy"> ORDER BY </div></td>
        <td> DATA </td>
        <td> SHOW CHECKBOX </td>
        <td> PDF PAJAK </td>
        <td> STATUS EMAIL </td>
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
             <select name="cboData">
                <option value="invoice">== INVOICE ==</option>
                <option value="return">== RETURN ==</option>
            </select>
        </td>
        <td>
             <select name="cboShowCheckBox">
                <option value="none"></option>
                <option value="excel">GENERATE EXCEL</option>
                <option value="mail">SEND EMAIL</option>
            </select>
        </td>
        <td>
            <select name="cboFilterPdf">
                <option value="">==ALL==</option>
                <option value="true">TRUE</option>
                <option value="false">FALSE</option>
            </select>
        </td>
        <td>
            <select name="cboFilterStatus">
                <option value="">==ALL==</option>
                <option value="t">DELIVERY</option>
                <option value="f">NOT DELIVERY</option>
            </select>
        </td>
    </tr>
</table><br />
<table width="100%" class="table_layout">
    <tr>
        <td rowspan="2" width="50%"> </td>
        <td> SORT BY </td>
        <td> SEARCH BY </td>
        <td> DEPT </td>
        <td><center> INVOICE DATE </center></td>
        <td><center> INVOICE PERIOD </center></td>
    </tr>
    <tr>
        <td>
             <select name="cboSortBy">
                <option value="bill_code">Bill Code</option>
                <option value="bill_vat_inv_no">Faktur Pajak No.</option>
            </select>
        </td>
        <td>
            <select name="cboSearchType">
                <option value="pajak_code">Pajak Code</option>
                <option value="pajak_name">Pajak Name</option>
                <option value="ship_code">Ship Code</option>
                <option value="ship_name">Ship Name</option>
                <option value="bill_code">Bill Code</option>
            </select>
            <input type="text" name="txtSearch" size="20" class="fmt" value="<?php echo $txtSearch; ?>">
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
</form>
Total : <?php echo $numRow ?>
<form name="frmSendMail" action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">
<input type="hidden" name="p_mode">
<input type="hidden" name="_bill_code">
<input type="hidden" name="_customer_email">
<input type="hidden" name="_file_type">
<input type="hidden" name="_location" value="list_fp_sent.php?<?php echo getQueryString() ?>">
<table width="100%" class="table_f">
    <tr>
        <th width="1%"></th>
        <th width="7%">INV. DATE</th>
        <th width="20%">CUSTOMER</th>
        <th width="12%">NPWP</th>
        <th width="13%">INVOICE NO</th>
        <th width="12%">FP. NO</th>
        <th width="7%">AMOUNT</th>
        <th width="6%">VAT</th>
        <th width="8%">AMOUNT<br>+VAT</th>
        <th width="10%">SENDMAIL</th>
    </tr>
<?php 
$amount = 0;
$vat    = 0;
$total    = 0;
while ($columns =& fetchRowAssoc($result)) {
    if($columns['bill_is_fp_delivery'] == 't') {
        $style = 'style="background-color:lightgrey; color:#333333"';
    } else if (substr($columns['bill_vat_inv_no'],0,3) != '010') {
        $style = 'style="background-color:#EEEEEE; color:#333333"';
    } else { $style = ''; }

    echo "<tr'>\n";
    if($_show == "excel") {
        if($columns['is_has_pdf'] == 0) {
            cell('<input type="checkbox" name="chkDO[]" value="'.$columns['bill_code'].'">', ' align="center" '.$style);
        } else {
            cell(' ', ' align="center" '.$style);
        }
    } else if($_show == "mail") {
        if($columns['is_has_pdf'] > 0) {
            cell('<input type="checkbox" name="chkDO[]" value="'.$columns['bill_code'].'">', ' align="center" '.$style);
        } else {
            cell(' ', ' align="center" '.$style);
        }
    } else {
        cell(' ', ' align="center" '.$style);
    }
    cell($columns['inv_date'], ' align="center" '.$style);
    cell($columns['cus_full_name'], ' align="left" '.$style);
    cell($columns['bill_npwp'], ' align="center" '.$style);
    cell_link($columns['bill_code'], ' align="center" '.$style, ' href="'.$columns['go_page'].'"');
    cell($columns['bill_vat_inv_no'], ' align="center" '.$style);    
    cell(number_format((double)$columns['amount']), ' align="right" '.$style);
    cell(number_format((double)$columns['vat']), ' align="right" '.$style);
    cell(number_format((double)$columns['amount_vat']), ' align="right" '.$style);
    if($columns["type"]=='billing') {
    if($columns['bill_is_fp_delivery'] == 'f') {
        if($columns['is_has_pdf'] == 0) {
            cell('', ' align="center" '.$style);
        } else {
            if($columns['email_pajak'] == "") {
                cell('<button name="btnSend" class="input_sky" onclick="sendMail(\''.$columns['bill_code'].'\', \''.$columns['email_pajak'].'\', \'FP\')" disabled>SEND MAIL</button>', ' align="center" '.$style);
            } else {
                cell('<button name="btnSend" class="input_sky" onclick="sendMail(\''.$columns['bill_code'].'\', \''.$columns['email_pajak'].'\', \'FP\')">SEND MAIL</button>', ' align="center" '.$style);
            }
        }
    } else {
        
        if($columns['is_has_pdfp'] == 0) {
            cell('<button name="btnSend" class="input_sky" onclick="sendMail(\''.$columns['bill_code'].'\', \''.$columns['email_pajak'].'\', \'FP\')">RE-SEND</button>', ' align="center" '.$style);
        } else {
            cell('<button name="btnSend" class="input_sky" onclick="sendMail(\''.$columns['bill_code'].'\', \''.$columns['email_pajak'].'\', \'FP\')">RE-SEND</button><br />
                  <button name="btnSend" class="input_sky" onclick="sendMail(\''.$columns['bill_code'].'\', \''.$columns['email_pajak'].'\', \'FP Perbaikan\')">MAIL REV</button>
                ', ' align="center" '.$style);
        }

    } } else {
      cell("");
    }
    echo "</tr>\n";

    $amount  += $columns['amount'];
    $vat     += $columns['vat'];
    $total   += $columns['amount_vat'];
}

if(isset($_GET['module']) && $_GET['module']=='result'){
    $bill_code = $_GET['bill_code'];
        
    $sql = "UPDATE med_tb_billing SET bill_is_fp_delivery = true WHERE bill_code = '$bill_code'";
    
    query($sql); 
    
    echo "<script>window.alert('EMAIL TELAH BERHASIL TERKIRIM !');
          window.location=('".$_SERVER[PHP_SELF]."')</script>";
}
?>
    <tr>
        <td colspan="6" align="right" style="color:brown; background-color:lightyellow">GRAND TOTAL</td>
        <td align="right" style="color:brown; background-color:lightyellow"><?php echo number_format((double)$amount) ?></td>
        <td align="right" style="color:brown; background-color:lightyellow"><?php echo number_format((double)$vat) ?></td>
        <td align="right" style="color:brown; background-color:lightyellow"><?php echo number_format((double)$total) ?></td>
    </tr>
</table>
</form>
<br />
<table width="100%" class="table_layout">
    <tr>
        <td><input type="checkbox" name="chkAll" onclick="checkAll(this.checked)"><span class="comment">check all</span></td>
        <td align="right">
        <button name='btnGenerateCsv' class='input_btn' onclick="summarizeDoCsv()"><img src="../../_images/icon/i_excel.gif" width="20px" align="middle"> &nbsp; Generate Csv (eFaktur)</button> &nbsp; &nbsp;
        <button name='btnGenerateExcel' class='input_btn' onclick="summarizeDoExcel()"><img src="../../_images/icon/i_excel.gif" width="20px" align="middle"> &nbsp; Generate Excel </button> &nbsp; &nbsp;
        <button name='btnSummarize' class='input_btn' style='width:130px;' onclick="summarizeDO()"><img src="../../_images/icon/list.gif" width="20px" align="middle"> &nbsp; Summarize</button>
        </td>        
    </tr>
</table><br />
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
<script language="javascript1.2" type="text/javascript">
    var f = window.document.frmSearch;
    var ts = <?php echo time() * 1000;?>;

    setSelect(f.cboData, "<?php echo isset($_GET['cboData']) ? $_GET['cboData'] : "invoice"?>");
    setSelect(f.cboSortBy, "<?php echo isset($_GET['cboSortBy']) ? $_GET['cboSortBy'] : "bill_vat_inv_no"?>");
    setSelect(f.cboShowCheckBox, "<?php echo isset($_GET['cboShowCheckBox']) ? $_GET['cboShowCheckBox'] : "excel"?>");
    setSelect(f.cboFilterOrderBy, "<?php echo isset($_GET['cboFilterOrderBy']) ? $_GET['cboFilterOrderBy'] : $cboFilter[1][ZKP_FUNCTION][0][0]?>");
    setSelect(f.cboFilterDept, "<?php echo isset($_GET['cboFilterDept']) ? $_GET['cboFilterDept'] : "all"?>");
    setSelect(f.cboSearchType, "<?php echo isset($_GET['cboSearchType']) ? $_GET['cboSearchType'] : "pajak_name"?>");
    setSelect(f.cboFilterStatus, "<?php echo isset($_GET['cboFilterStatus']) ? $_GET['cboFilterStatus'] : ""?>");
    setSelect(f.cboFilterPdf, "<?php echo isset($_GET['cboFilterPdf']) ? $_GET['cboFilterPdf'] : ""?>");    
    
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

    f.cboShowCheckBox.onchange  = f.cboFilterOrderBy.onchange;
    f.cboFilterStatus.onchange  = f.cboFilterOrderBy.onchange;
    f.cboFilterDept.onchange    = f.cboFilterOrderBy.onchange;
    f.cboSortBy.onchange        = f.cboFilterOrderBy.onchange;
    f.cboData.onchange          = f.cboFilterOrderBy.onchange;
    f.cboFilterPdf.onchange     = f.cboFilterOrderBy.onchange;

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

    function checkAll(o) {
        var oCheck = window.document.all.tags("INPUT");
    
        for (var i = 0; i < oCheck.length; i++) {
            if (oCheck[i].type == "checkbox" && oCheck[i].name == "chkDO[]") {
                oCheck[i].checked = o;
            }
        }
    }

    function summarizeDO() {
        var oCheck         = window.document.all.tags("INPUT");
        var keyword         = '';
        var counter         = 0;

        for (var i = 0; i < oCheck.length; i++) {
            if (oCheck[i].type == "checkbox" && oCheck(i).name == "chkDO[]" && oCheck(i).checked) {
                if(keyword == '') {
                    keyword = oCheck[i].value;
                } else {
                    keyword = keyword+ ',' + oCheck[i].value;
                }
            }
        }

        if(keyword == '') {
            alert("You haven't checked any Billing.\nPlease check first");
            return;
        }

        var x = (screen.availWidth - 1000) / 2;
        var y = (screen.availHeight - 550) / 2;
        win = window.open(
            './p_summary_faktur_pajak.php?_code='+keyword,
            'win',
            'scrollbars,width=1000,height=550,screenX='+x+',screenY='+y+',left='+x+',top='+y);
        win.focus();
    }
    
    function summarizeDoExcel() {
        var oCheck         = window.document.all.tags("INPUT");
        var keyword         = '';
        var counter         = 0;

        for (var i = 0; i < oCheck.length; i++) {
            if (oCheck[i].type == "checkbox" && oCheck(i).name == "chkDO[]" && oCheck(i).checked) {
                if(keyword == '') {
                    keyword = oCheck[i].value;
                } else {
                    keyword = keyword+ ',' + oCheck[i].value;
                }
            }
        }

        if(keyword == '') {
            alert("You haven't checked any Billing.\nPlease check first");
            return;
        }
        
        window.location.assign("./laporan_approval_pajak.php?_code="+keyword);
    }
    
    function summarizeDoCsv() {
        var oCheck         = window.document.all.tags("INPUT");
        var keyword         = '';
        var counter         = 0;

        for (var i = 0; i < oCheck.length; i++) {
            if (oCheck[i].type == "checkbox" && oCheck(i).name == "chkDO[]" && oCheck(i).checked) {
                if(keyword == '') {
                    keyword = oCheck[i].value;
                } else {
                    keyword = keyword+ ',' + oCheck[i].value;
                }
            }
        }

        if(keyword == '') {
            alert("You haven't checked any Billing.\nPlease check first");
            return;
        }
        
        if(window.document.frmSearch.cboData.value == "invoice") {
          window.location.assign("./daily_billing_by_invoice_csv2.php?_code="+keyword+"&<?php echo getQueryString()?>");
        } else if(window.document.frmSearch.cboData.value == "return") {
          window.location.assign("./daily_billing_by_invoice_csv_return.php?_code="+keyword+"&<?php echo getQueryString()?>");
        }
       
    }

    function sendMail(bill_code, email_customer, file) {
        if(confirm("Are you sure to send the file "+file+"? Bill code "+bill_code)) {
            window.document.frmSendMail.p_mode.value = "send_mail";
            window.document.frmSendMail._bill_code.value = bill_code;
            window.document.frmSendMail._customer_email.value = email_customer;
            window.document.frmSendMail._file_type.value = file;
            window.document.frmSendMail.submit();
        }
    }
</script>
</body>
</html>