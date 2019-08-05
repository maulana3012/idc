<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim
*/

//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";
require_once LIB_DIR . "zk_listing.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//Global
$left_loc = "list_available_number.php";
$_ordered_by = $cboFilter[1][ZKP_FUNCTION][0][0];

//PROCESS FORM
require_once "tpl_process_form.php"; 

//DEFAULT PROCESS
$sqlQuery = new strSelect("
  SELECT fk_idx, fk_digit, fk_year, fk_from, fk_to, 
  (select count(bill_code) from ".ZKP_SQL."_tb_faktur_pajak_item where fk_idx=f.fk_idx) as used,
  (select count(fkit_idx) 
  from 
    ".ZKP_SQL."_tb_faktur_pajak
    JOIN ".ZKP_SQL."_tb_faktur_pajak_item USING (fk_idx)
  where fk_year::text = to_char(CURRENT_DATE, 'YY') and fkit_ordered_by=$_ordered_by and bill_code is null) as available
FROM ".ZKP_SQL."_tb_faktur_pajak AS f JOIN ".ZKP_SQL."_tb_faktur_pajak_item AS fit USING (fk_idx)
");
$sqlQuery->whereCaluse = "fk_ordered_by = {$cboFilter[1][ZKP_FUNCTION][0][0]}";
$sqlQuery->setGroupBy("fk_idx, fk_digit, fk_year, fk_from, fk_to, fk_ordered_by");

$sql_confirm = "SELECT to_char(st_date, 'dd-Mon-yyyy') AS st_date FROM ".ZKP_SQL."_tb_setup WHERE st_desc='confirm_pajak'";
$res_confirm =& query($sql_confirm);
$col_confirm =& fetchRowAssoc($res_confirm);

$sqlQuery->setOrderBy("fk_idx DESC");
if(isZKError($result =& query($sqlQuery->getSQL())))
  $M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/index.php");

//Total Rows
$numRow = numQueryRows($result);

//Declare Paging
$oPage = new strPaging($sqlQuery->getSQL(), 10);
$oPage->strPrev       = "";
$oPage->strNext       = "";
$oPage->strPrevDiv    = "<";
$oPage->strNextDiv    = ">";
$oPage->strLast       = ">>";
$oPage->strFirst      = "<<";
$oPage->strCurrentNum = "<strong>[%s]</strong>";
$oPage->strGet = $strGet;

if(isZKError($result =& query($oPage->getListQuery())))
  $m->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//buffer fk_number
if(ZKP_SQL == 'IDC')
  $date_bill = array(date('Y-m-d', mktime(0,0,0, date('m')-4, 1, date('Y'))), date('Y-m-d', mktime(0,0,0, date('m')-1, 1-1, date('Y'))));
else if(ZKP_SQL == 'MED')
  $date_bill = array(date('Y-m-d', mktime(0,0,0, date('m')-3, 1, date('Y'))), date('Y-m-d', mktime(0,0,0, date('m'), 1-1, date('Y'))));
$sql_buffer = "SELECT count(bill_code) as total, ROUND(count(bill_code)/3,0) as avg FROM ".ZKP_SQL."_tb_billing WHERE bill_ordered_by=$_ordered_by AND bill_vat > 0 AND bill_inv_date BETWEEN DATE '".$date_bill[0]."' AND '".$date_bill[1]."'";
$res_buffer =& query($sql_buffer);
$buffer =& fetchRowAssoc($res_buffer);
$total = $buffer['total'];
$avg = $buffer['avg'];
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language='text/javascript' type='text/javascript'>
function checkform(o, type, idx) {
  if(type == 'add') {
      if(o._year.value.length < 2) {
        alert("Please input year 2 character number");
        o._year.focus();
        return;
      }
      if(o._digit.value.length < 3) {
        alert("Please input digit 3 character number");
        o._digit.focus();
        return;
      }
      if(parseFloat(removecomma(o._from.value)) >= parseFloat(removecomma(o._to.value))) {
        alert('Value from cannot more than value to');
        return;
      }
      if (verify(o)) {
        if(confirm("Are you sure to save data?")) {
          o.submit();
        }
      }
  } else if(type == 'delete') {
      if(confirm("Are you sure to delete faktur number?")) {
        o.p_mode.value = 'delete_number';
        o._idx.value = idx;
        o.submit();
      }
  } else if(type == 'confirm') {
    if (verify(o)) {
      if(confirm("Are you sure to confirm pajak?")) {
        o.submit(); 
      }
    }
  }
}

function viewFaktur(idx, type) {
  if(type == 'list') {
    var x1 = 500; var y1 = 600; 
  } else if(type == 'edit') {
    var x1 = 600; var y1 = 200; 
  }

  var x = (screen.availWidth - x1) / 2;
  var y = (screen.availHeight - y1) / 2;
  var win = window.open(
    'p_list_faktur.php?_idx='+idx+'&_type='+type, '',
    'scrollbars,width='+x1+',height='+y1+',screenX='+x+',screenY='+y+',left='+x+',top='+y);

}
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
      <strong class="title">[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] LIST FAKTUR PAJAK NUMBER</strong><br /><br />
            <table width="50%" class="table_aa">
              <tr>
                <th colspan="3">FAKTUR NUMBER</th>
                <th width="10%">TOTAL</th>
                <th width="10%">USED</th>
                <th width="25%">ACTION</th>
              </tr>
<?php
while ($column =& fetchRowAssoc($result)) {
?>
              <tr>
                <td width="10%" align="center"><?php echo $column['fk_digit']?></td>
                <td width="8%" align="center"><?php echo $column['fk_year']?></td>
                <td><?php echo str_pad($column['fk_from'],8,"0",STR_PAD_LEFT)  .' - '. str_pad($column['fk_to'],8,"0",STR_PAD_LEFT) ?></td>
                <td align="right"><?php echo $column['fk_to'] - $column['fk_from'] + 1 ?> &nbsp; </td>
                <td align="right"><?php echo $column['used'] ?> &nbsp; </td>
                <td align="center">
                  [<a href="javascript:viewFaktur(<?php echo $column['fk_idx']?>, 'list')">View</a>] &nbsp; 
                  <?php if($column['used'] > 0) { ?>
                  <?php } else { ?>
                  [<a href="javascript:viewFaktur(<?php echo $column['fk_idx']?>, 'edit')">Edit</a>] &nbsp; 
                  [<a href="javascript:checkform(window.document.frmAdd, 'delete', <?php echo $column['fk_idx'] ?>)">Del</a>]
                  <?php } ?>
                </td>
              </tr>
<?php 
  $avail = $column['available'];
} 
?>
          <?php if($_ordered_by == 1 && $avail < round($avg/2)) { ?>
          <tr>
            <td style="color:red" colspan="5">Total available faktur number period <?php echo date("Y") ?> (Critical <?php echo round($avg/2) ?>)</td>
            <td style="color:red" align="right"><?php echo $avail?></td>
          </tr>
          <?php } else {?>
          <tr>
            <td colspan="5">Total available faktur number period <?php echo date("Y") ?></td>
            <td align="right"><?php echo $avail?></td>
          </tr>
          <?php } ?>
          <tr>
            <td colspan="5">Total 3 months used faktur pajak number (<?php echo date('My', strtotime($date_bill[0])) .' - '. date('My', strtotime($date_bill[1])) ?>)</td>
            <td align="right"><?php echo number_format($total) ?></td>
          </tr>
          <tr>
            <td colspan="5">Average last 3 months used faktur pajak number (<?php echo date('My', strtotime($date_bill[0])) .' - '. date('My', strtotime($date_bill[1])) ?>)</td>
            <td align="right"><?php echo $avg ?></td>
          </tr>
          <tr>
            <td colspan="5">Request number = total * 120%</td>
            <td align="right"><?php echo number_format($total*1.2) ?></td>
          </tr>
      </table>
      <table width="50%" cellpadding="0" cellspacing="2" border="0">
        <tr>
          <td align="center"><?php echo $oPage->putPaging();?></td>
        </tr>
      </table>
      <br><span class="comment">* Pls click the prefer action to edit data</span><br>

      <br><br>
<span>Add New Number</span>
<form name='frmAdd' method='POST'>
  <input type='hidden' name='p_mode' value='insert_number'>
  <input type='hidden' name='_idx'>
  <table width="50%" class="table_a">
    <tr>
      <th width="30%">YEAR (YY)</th>
      <td colspan="2"><input name="_year" type="text" class="req" size="2" maxlength="2" value="<?php echo date('y') ?>" onKeyUp="formatNumber(this, 'dot');">
      </td>
    </tr>
    <tr>
      <th>DIGIT</th>
      <td colspan="2"><input name="_digit" type="text" class="req" size="2" maxlength="3" onKeyUp="formatNumber(this, 'dot');">
      </td>
    </tr>
    <tr>
      <th>FAKTUR NUMBER</th>
      <td>
        FROM <input name="_from" type="text" class="reqn" size="10" maxlength="10" onKeyUp="formatNumber(this, 'dot');"> &nbsp;
        TO <input name="_to" type="text" class="reqn" size="10" maxlength="10" onKeyUp="formatNumber(this, 'dot');">
      </td>
      <td width="20%">
        <button name='btnSave' class='input_btn' style='width:100%;' onclick="checkform(window.document.frmAdd, 'add')"><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save this number"> &nbsp; Save</button>
      </td>
    </tr>
  </table><br />
</form>

<span>Confirm Faktur Pajak</span>
<form name='frmConfirm' method='POST'>
  <input type='hidden' name='p_mode' value='confirm_date'>
  <table width="50%" class="table_a">
    <tr>
      <th width="30%">LAST CONFIRM</th>
      <td colspan="2"><?php echo $col_confirm['st_date'] ?></td>
    </tr>
    <tr>
      <th>DATE</th>
      <td><input name="_date" type="text" class="reqd" size="15" value="<?php echo date('d-M-Y') ?>"></td>
      <td width="20%">
        <button name='btnSave' class='input_btn' style='width:100%;' onclick="checkform(window.document.frmConfirm, 'confirm')"><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save item categry"> &nbsp; Save</button>
      </td>
    </tr>
  </table>
</form>

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