<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*/

//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc   = "list_stock.php";
$_code      = urldecode($_GET['_code']);
$_location  = urldecode($_GET['cboLocation']);
$_filter_by = isset($_GET['cboTypeActivity']) ? $_GET['cboTypeActivity'] : '0';
$_show_note = isset($_GET['chkIncNote']) ? $_GET['chkIncNote'] : 'false';
$period_from= isset($_GET['period_from'])? $_GET['period_from'] : date("1-M-Y");
$period_to  = isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", time());
$type_activity = array(
                    11=>'Initial Stock', 12=>'P/L', 13=>'Return (Good Condition)', 14=>'Replace Claim', 
                    15=>'RT', 16=> 'Borrow', 17=>'Replace Claim', 18=>'Incoming (Move type)', 19=>'Incoming (Move location)',
                    10=>'Incoming (Change Type)', 9=>'PO+ (Shadow)', 
                    21=> 'DO', 22=>'DT', 23=>'DF', 24=>'DR', 25=>'Lend', 26=>'DM',
                    27=>'Move to Reject', 28=>'Outgoing Return (Move type)', 29=>'Delete Expired Stock', 30=>'Outgoing (Move Location)',
                    31=>'Outgoing (Change Type)', 32=>'PO- (Shadow)', 
                );

//=========================================================================================== DEFAULT PROCESS
$sql = "
SELECT  
  it_code, it_model_no,
  (SELECT sum(log_qty) FROM ".ZKP_SQL."_tb_stock_logs WHERE it_code='$_code' AND log_wh_location=$_location AND log_document_type BETWEEN 9 AND 20 AND log_document_type not in (25,16,28,18) AND log_qty_status=true ) AS in_stock,
  (SELECT sum(log_qty) FROM ".ZKP_SQL."_tb_stock_logs WHERE it_code='$_code' AND log_wh_location=$_location AND log_document_type BETWEEN 21 AND 32 AND log_document_type not in (25,16,28,18) AND log_qty_status=true ) AS out_stock, 
  (SELECT sum(stk_qty) FROM ".ZKP_SQL."_tb_stock WHERE it_code='$_code' AND stk_wh_location=$_location),
  (SELECT max(to_char(stk_updated, 'dd-Mon-yy hh24:mi:ss')) FROM ".ZKP_SQL."_tb_stock WHERE it_code='$_code' AND stk_wh_location=$_location) AS stk_updated, 
  ".ZKP_SQL."_getLastBalanceQty('$_code', null, $_location, $_filter_by, date '$period_from 00:00:00'),
  (SELECT sum(stk_qty) FROM ".ZKP_SQL."_tb_stock WHERE it_code='$_code') AS stock_total,
  it_critical_stock,
  (SELECT sum(stk_qty) FROM ".ZKP_SQL."_tb_stock WHERE it_code='$_code') - it_critical_stock AS stk_diff,
  it_ed
FROM ".ZKP_SQL."_tb_item
WHERE it_code = '$_code'";

$result = query($sql);
$item   = fetchRow($result);
$tmp    = array();

if($_filter_by == 0) {}
else if($_filter_by == 1) $tmp[] = "log_document_type in(10,11,12,13,14,15,16,17,18,19)";
else if($_filter_by == 2) $tmp[] = "log_document_type in(21,22,23,24,25,26,27,28,29,30,31)";
else if($_filter_by == 14) $tmp[] = "log_document_type in (14,17)";
else $tmp[] = "log_document_type = $_filter_by";

if($_show_note == 'false') $tmp[] = "log_qty_status IS TRUE";

$tmp[] = "log_cfm_timestamp BETWEEN TIMESTAMP '$period_from 00:00:00' AND '$period_to 23:59:59'";
$tmp[] = "it_code = '$_code'";
$tmp[] = "log_wh_location = $_location";
$tmp[] = "log_document_type not in (25,16, 28,18, 10,31, 9,32)";
$strWhere   = implode(" AND ", $tmp);

$sql_history    = "
SELECT *, 
  case
    when log_qty_status is false then 0
    when log_qty_value is false then -log_qty
    else log_qty
  end as qty
FROM ".ZKP_SQL."_tb_stock_logs WHERE $strWhere ORDER BY log_cfm_timestamp";
$res_history    = query($sql_history);
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language="text/javascript" type="text/javascript" src="../../_script/js_category.php"></script>
<script language='text/javascript' type='text/javascript'>
function seeDetail() {
    var code    = trim(window.document.frmSearch._code.value);
    var loc     = window.document.frmSearch.cboLocation.value;

    window.location.href = 'history_stock_v1.php?_code=' + code + '&cboLocation=' + loc;
}

function calcuteQty() {

    var f       = window.document.frmSearch;
    var e       = window.document.frmSearch.elements;
    var count   = window.rowPosition.rows.length;
    var idx_qty = 11;       /////
    var sumQty  = 0;

    for (var i=0; i<count; i++) {
        var qty     = parseFloat(removecomma(e(idx_qty+i).value));
        sumQty  += qty;
    }

    if(f.chkIncBalance.checked) {
        sumQty += parseFloat(removecomma(f.totalLastPeriodQty.value));
    }

    window.document.frmSearch.totalQty.value    = numFormatval(sumQty+'',2);
}

function initPage() {
    setSelect(window.document.frmSearch.cboLocation, "<?php echo $_GET['cboLocation'] ?>");
    setSelect(window.document.frmSearch.cboTypeActivity, "<?php echo isset($_GET['cboTypeActivity']) ? $_GET['cboTypeActivity'] : '' ?>");
    calcuteQty();
    <?php if($item[10]=='t') { ?>
    setSelect(window.document.frmEDStock.cboMonth, "<?php echo date("n") ?>");
    <?php } ?>
}
</script>
</head>
<body topmargin="0" leftmargin="0" onLoad="initPage()">
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
<h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] STOCK HISTORY</h3>
<form name="frmSearch" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
<fieldset>
    <legend> <img src="../../_images/icon/description.gif" align="middle"> <span style="color:#446fbe;font-family:courier;font-weight:bold">DESCRIPTION </span></legend>
    <table width="100%" class="table_layout">
        <tr height="35px">
            <td width="60%" align="right"></td>
            <td>Item Code : <input type="text" name="_code" class="fmt" style="color:darkblue" size="7" maxlength="6" value="<?php echo $_code ?>"></td>
            <td>
                Location :
                <select name="cboLocation">
                <?php 
                $wh = array($cboFilter[3]['purchasing'][ZKP_FUNCTION], count($cboFilter[3]['purchasing'][ZKP_FUNCTION]));
                for($i=0; $i<$wh[1]; $i++) {
                    $v = (intval($_location)==intval($wh[0][$i][0]))?' selected':'';
                    echo "\t\t\t<option value=\"".$wh[0][$i][0]."\" ".$v.">".$wh[0][$i][1]."</option>\n";
                }
                ?>
                </select>
            </td>
            <td>
                <button name="btnSeeDetail" class="input_sky" onClick="seeDetail()">SEE DETAIL</button>
            </td>
        </tr>
    </table><br />
</fieldset><br /><br />
<fieldset>
    <legend> <img src="../../_images/icon/package.gif" align="middle"> <span style="color:#446fbe;font-family:courier;font-weight:bold">CURRENT STOCK </span></legend><br />
    <table width="100%" class="table_n">
        <tr>
            <th width="15%">CODE</th>
            <th>MODEL NO</th>
            <th width="10%">IN STOCK</th>
            <th width="10%">OUT STOCK</th>
            <th width="10%">BALANCE<br />STOCK</th>
            <th width="15%">Last Updated</th>
        </tr>
    </table>
    <table width="100%" class="table_box">
        <tr height="35px">
            <td width="15%"><?php echo $item[0] ?></td>
            <td><?php echo $item[1] ?></td>
            <td width="10%" align="center"><?php echo number_format($item[2],2) ?></td>
            <td width="10%" align="center"><?php echo number_format($item[3],2) ?></td>
            <td width="10%" align="center"><?php echo number_format($item[4],2) ?></td>
            <td width="15%"><?php echo $item[5] ?></td>
        </tr>
    </table><br />
    <table width="40%" class="table_box" align="right">
        <tr>
            <td rowspan="2" width="20%" align="right" valign="middle"><img src="../../_images/icon/alert.gif"></td>
            <td><div align="right" style="color:#446fbe;font-family:verdana;font-weight:bold;font-size:8pt;">STOCK LEVEL INFORMATION</div></td>
        </tr>
        <tr>
            <td>
                <table width="100%" class="table_box" align="right">
                    <tr>
                        <th><font size="0.25px">CURRENT<br />ALL STOCK</font></th>
                        <th><font size="0.25px">CRITICAL<br />STOCK</font></th>
                        <th><font size="0.25px">CALC<br />( + / - )</font></th>
                    </tr>
                    <tr>
                        <td align="center"><?php echo number_format($item[7],2) ?></td>
                        <td align="center"><?php echo number_format($item[8],2) ?></td>
                        <?php if($item[9] > 0 ) { ?>
                        <td align="center"><?php echo number_format($item[9],2) ?></td>
                        <?php } else { ?>
                        <td align="center" style="color:red"><?php echo number_format($item[9]*-1,2) ?></td>
                        <?php } ?> 
                    </tr>
                </table>
            </td>
        </tr>
    </table><br />
</fieldset><br /><br />
<fieldset>
    <legend> <img src="../../_images/icon/history.gif" align="middle"> <span style="color:#446fbe;font-family:courier;font-weight:bold">COMPLETE STOCK HISTORY until 22-Dec-2012</span></legend>
    <table width="100%" class="table_box">
        <tr>
            <td width="35%"></td>
            <td>Type Activity :</td>
            <td>
                <select name="cboTypeActivity">
                    <option value="0" style="background-color:#FFFFFF;color:darkblue"> == ALL CONDITION== </option>
                    <optgroup label="-In Stock-" style="background-color:#FFFF99;color:darkblue;">
                        <option value="1">ALL IN STOCK</option>
                        <option value="11">Initial Stock</option>
                        <option value="12">P/L</option>
                        <option value="13">Return (Good Condition)</option>
                        <option value="14">Replace Claim</option>
                        <option value="15">Return Temporarry</option>
                        <option value="19">Incoming (Move Location)</option>
                    </optgroup>
                    <optgroup label="-Out Stock-" style="background-color:#FFCC99;color:darkblue">
                        <option value="2">ALL OUT STOCK</option>
                        <option value="21">DO</option>
                        <option value="22">DT</option>
                        <option value="23">DF</option>
                        <option value="24">DR</option>
                        <option value="26">DM</option>
                        <option value="27">Move to Reject</option>
                        <option value="29">Delete expired stock</option>
                        <option value="30">Outgoing (Move Location)</option>
                        
                    </optgroup>
                </select>
            </td>
            <td>
                From <input type="text" name="period_from" size="10" class="fmtd" value="<?php echo $period_from; ?>">&nbsp;
                To <input type="text" name="period_to" size="10" class="fmtd"  value="<?php echo $period_to; ?>">
            </td>
        </tr>
        <tr>
            <td colspan="4" align="right"><input type="checkbox" name="chkIncNote" onClick="includeNote(this.checked)" <?php echo ($_show_note == 'true')?'checked':'' ?>> <i>show cancelled record</i></td>
        </tr>
    </table><br />
    <table width="100%" class="table_n">
        <tr height="40px">
            <th width="20%">CONFIRM DATE</th>
            <th width="18%">TYPE</th>
            <th width="20%">DOCUMENT NO.</th>
            <th width="15%">DOCUMENT DATE</th>
            <th width="8%">QTY<br />(Pcs)</th>
            <th width="20%">REMARK</th>
            <th width="4%"></th>
        </tr>
    </table><br />
    <div style="height:400; overflow-y:scroll">
    <table width="100%" class="table_box">
        <thead>
            <tr height="35px">
                <td align="right"><i>Last period balance</i></td>
                <td width="8%"><input type="text" name="totalLastPeriodQty" class="reqn" style="width:100%" value="<?php echo number_format($item[6],2) ?>" readonly></td>
                <td width="18%">&nbsp;</td>
            </tr>
        </thead>
    </table>
    <table width="100%" class="table_n">
        <tbody id="rowPosition">
    <?php while($log =& fetchRowAssoc($res_history)) { ?>
        <tr <?php echo ($log["log_qty_status"]=='f')?"style=\"text-decoration:line-through;\"":"" ?>>
            <td width="4%" align="center">
                <?php
                if($log["log_qty_status"]=='t') {
                    if($log["log_document_type"]==21 || $log["log_document_type"]==22 || $log["log_document_type"]==23 || $log["log_document_type"]==24 || $log["log_document_type"]==25 || $log["log_document_type"]==26) { /*21,22,23,24,25,26*/
                        $f = PDF_STORAGE.'warehouse/do/'.date('Ym', strtotime($log["log_document_date"])).'/'.$log["log_document_no"].'_rev_'.$log["log_revision_time"].'.pdf';
                ?>
                <a href="javascript:downloadPDF('<?php echo $f ?>')"><img src="../../_images/icon/pdf.gif" width="15px" alt="Download pdf <?php echo $log["log_document_no"].' rev '.$log["log_revision_time"] ?>"></a>
                <?php
                    } else if($log["log_document_type"]==13 || $log["log_document_type"]==15) { /*13,15*/
                        $f = PDF_STORAGE.'warehouse/return/'.date('Ym', strtotime($log["log_document_date"])).'/'.$log["log_document_no"].'_rev_'.$log["log_revision_time"].'.pdf';
                ?> 
                <a href="javascript:downloadPDF('<?php echo $f ?>')"><img src="../../_images/icon/pdf.gif" width="15px" alt="Download pdf <?php echo $log["log_document_no"].' rev '.$log["log_revision_time"] ?>"></a>
                <?php
                    } 
                }
                ?>
                <input type="hidden" name="_qty" value="<?php echo $log["qty"]?>">
            </td>
            <td width="18%"><?php echo date('d / m / Y H:i:s', strtotime($log["log_cfm_timestamp"])) ?></td>
            <td width="20%"><?php echo $type_activity[$log["log_document_type"]] ?></td>
            <td width="20%"><?php echo $log["log_document_no"] ?></td>
            <td width="15%" align="center"><?php echo ($log["log_document_date"]!='') ? date('d-M-y', strtotime($log["log_document_date"])):'' ?></td>
            <td width="8%" align="right"><?php echo ($log["log_qty_value"]=='t') ? number_format($log["log_qty"],2): '-'.number_format($log["log_qty"],2) ?></td>
            <td><?php echo $log["log_remark"] ?></td>
            <?php 
            if($log["log_qty_status"]=='f') {
                if($log["log_document_type"]==21 || $log["log_document_type"]==22 || $log["log_document_type"]==23 || $log["log_document_type"]==24 || $log["log_document_type"]==25 || $log["log_document_type"]==26) { /*21,22,23,24,25,26*/
                    $f = PDF_STORAGE.'warehouse/do/'.date('Ym', strtotime($log["log_document_date"])).'/'.$log["log_document_no"].'_rev_'.$log["log_revision_time"].'.pdf';
            ?>
            <td  width="20%" align="right">
                <img src="../../_images/icon/comment.gif" alt="Unconfirmed by: <?php echo $log["log_uncfm_by_account"].", ".date('d-M-Y g:i:s',strtotime($log["log_cfm_timestamp"])) ?>">
                <a href="javascript:downloadPDF('<?php echo $f ?>')"><img src="../../_images/icon/pdf.gif" width="15px" alt="Download unconfirmed pdf for <?php echo $log["log_document_no"].' rev '.$log["log_revision_time"] ?>"></a>
            <td>
            <?php
                } else if($log["log_document_type"]==13 || $log["log_document_type"]==15) { /*13,15*/
                    $f = PDF_STORAGE.'warehouse/return/'.date('Ym', strtotime($log["log_document_date"])).'/'.$log["log_document_no"].'_rev_'.$log["log_revision_time"].'.pdf';
            ?>
            <td  width="20%" align="right">
                <img src="../../_images/icon/comment.gif" alt="Unconfirmed by: <?php echo $log["log_uncfm_by_account"].", ".date('d-M-Y g:i:s',strtotime($log["log_uncfm_timestamp"])) ?>">
                <a href="javascript:downloadPDF('<?php echo $f ?>')"><img src="../../_images/icon/pdf.gif" width="15px" alt="Download unconfirmed pdf for <?php echo $log["log_document_no"].' rev '.$log["log_revision_time"] ?>"></a>
            <td>
            <?php
                } else {
            ?>
            <td  width="20%" align="right"><td>
            <?php
                }
            } else {
            ?>
            <td  width="20%" align="right"><td>
            <?php 
            }
            ?>
        </tr>
    <?php } ?>
        </tbody>
        <tr>
            <th colspan="4" align="left"><input type="checkbox" name="chkIncBalance" onClick="calcuteQty()" checked><i style="font-weight:normal">include last period balance</i></th>
            <th align="right">Total Qty</th>
            <th><input type="text" name="totalQty" class="reqn" style="width:100%" readonly></th>
            <th colspan="2"></th>
        </tr>
    </table><br />
    </div><br />
</fieldset><br /><br />
</form>
<?php if($item[10]=='t') { ?>
<fieldset>
    <legend> <img src="../../_images/icon/calender.gif" align="middle"> <span style="color:#446fbe;font-family:courier;font-weight:bold">E/D LOG </span></legend>
    <form name="frmEDStock">
    <input type="hidden" name="p_mode" value="search">
    <input type="hidden" name="_code" value="<?php echo $_code ?>">
    <input type="hidden" name="_model_no" value="<?php echo $item[1] ?>">
    <input type="hidden" name="_type" value="<?php echo $_type ?>">
    <input type="hidden" name="_location" value="<?php echo $_location ?>">
    <table class="table_box" width="100%">
        <tr>
            <td align="right">
                <span class="comment">Search E/D log for this period :</span> 
                <select name="cboMonth">
                    <option value="1">January</option>
                    <option value="2">February</option>
                    <option value="3">March</option>
                    <option value="4">April</option>
                    <option value="5">May</option>
                    <option value="6">June</option>
                    <option value="7">July</option>
                    <option value="8">August</option>
                    <option value="9">September</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                </select>&nbsp; 
                <input type="text" name="_year" class="fmtn" style="width:40px" maxlength="4" value="<?php echo date("Y") ?>" onKeyPress="if(window.event.keyCode == 13) findEDLog()">&nbsp;            
            </td>
            <th width="5%"><a href="javascript:findEDLog()"><img src="../../_images/icon/search_mini.gif" alt="Search"></a></th>
        </tr>
    </table><br />
    </form>
</fieldset>
<script language="javascript1.2" type="text/javascript">
function findEDLog() {
    var f = window.document.frmEDStock;
    if(f._year.value.length < 4) {
        alert("Please input year in complete digit");
        f._year.focus();
        return;
    }

    var code    = f._code.value;
    var model   = f._model_no.value;
    var loc     = f._location.value;
    var month   = f.cboMonth.value;
    var year    = f._year.value;
    var filter_by   = window.document.frmSearch.cboTypeActivity.value;

    var x = (screen.availWidth - 650) / 2;
    var y = (screen.availHeight - 650) / 2;

    wSearchItem = 
        window.open("p_list_ed.php?_code="+code+"&_model="+model+"&_loc="+loc+"&_month="+month+"&_year="+year+"&cboTypeActivity="+filter_by,
        '',
        'scrollbars,width=650,height=650,screenX='+x+',screenY='+y+',left='+x+',top='+y);
    wSearchItem.focus();
}
</script>
<?php } ?>
<script language="javascript1.2" type="text/javascript">
    var f = window.document.frmSearch;

    f.cboTypeActivity.onchange    = function() {
        goPageLocation();
    }

    f.period_from.onkeypress = function() {
        if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
            goPageLocation();
        }
    }

    f.period_to.onkeypress = function() {
        if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
            goPageLocation();
        }
    }

    function includeNote() {
        goPageLocation();
    }

    function goPageLocation() {
        var f = window.document.frmSearch;
        window.location.href = "history_stock_v1.php?_code="+trim(f._code.value)+"&cboLocation="+f.cboLocation.value+"&cboTypeActivity="+f.cboTypeActivity.value+
                                                "&chkIncNote="+f.chkIncNote.checked+"&period_from="+f.period_from.value+"&period_to="+f.period_to.value;
    }

function downloadPDF(file) {
    var winforPrint = window.open('','','toolbar=no,width=780,height=580,resizable=yes');
    winforPrint.document.location.href = "../../_include/warehouse/pdf/download_stock_pdf.php?_file="+file;
}
</script>
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