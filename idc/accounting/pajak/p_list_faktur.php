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
require_once LIB_DIR . "zk_listing.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, "javascript:window.close();");

//PROCESS FORM
require_once "tpl_process_form.php"; 

//Check PARAMETER
if(!isset($_GET['_idx']) || $_GET['_type'] == "")
    die("<script language=\"javascript1.2\">window.close();</script>");

$_idx = $_GET['_idx'];
$_type = $_GET['_type'];
$strGet = "";

//DEFAULT PROCESS
if($_type == 'list') {
$sqlQuery = new strSelect("SELECT * FROM   ".ZKP_SQL."_tb_faktur_pajak_item");
$sqlQuery->whereCaluse = "fk_idx = $_idx";

//Search Option 1 : by bill_code
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "bill_code") {
  $sqlQuery->setWhere(" AND %s ILIKE '%%%s%%'", array("bill_code" => "txtKeyword"), "AND");
  $strGet = $sqlQuery->getQueryString() . "searchBy=bill_code";
}

//Search Option 1 : by fkit_number
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "fkit_number") {
  $sqlQuery->setWhere(" AND %s ILIKE '%%%s%%'", array("fkit_number" => "txtKeyword"), "AND");
  $strGet = $sqlQuery->getQueryString() . "searchBy=fkit_number";
}

$strGet = "_idx=$_idx&_type=list";

$sqlQuery->setOrderBy("fkit_number");
if(isZKError($result =& query($sqlQuery->getSQL())))
    $M->goErrorPage($result,  "javascript:window.close();");

//Total Rows
$numRow = numQueryRows($result);

//Declare Paging
$oPage = new strPaging($sqlQuery->getSQL(), 50);
$oPage->strPrev       = "";
$oPage->strNext       = "";
$oPage->strPrevDiv    = "<";
$oPage->strNextDiv    = ">";
$oPage->strLast       = ">>";
$oPage->strFirst      = "<<";
$oPage->strCurrentNum = "<strong>[%s]</strong>";
$oPage->strGet = $strGet;

if(isZKError($result =& query($oPage->getListQuery())))
    $m->goErrorPage($result, "javascript:window.close();");


} else if($_type == 'edit') {
$sql = "SELECT * FROM ".ZKP_SQL."_tb_faktur_pajak WHERE fk_idx = $_idx";
if (isZKError($result =& query($sql))) $M->goErrorPage($result, "javascript:window.close();");
$fk =& fetchRowAssoc($result);
}
?>
<html>
<head>
<title><?php echo STRTOUPPER($_type) ?> FAKTUR PAJAK</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="text/javascript" type="text/javascript" src="../../_script/aden.js"></script>
<script language="javascript" type="text/javascript">
<?php if($_type == 'list') { ?>
function searchByKeyword() {
  var o = window.document.frmSrarchByKeyword;
  
  if(o.txtKeyword.value.length() <=0 ) {
    alert("Please insert the keyword");
    o.txtKeyword.focus();
  } else {
    o.submit();
  }
}
<?php } else if($_type == 'edit') { ?>
function checkform(o) {
  if (verify(o)) {
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
    if(parseInt(o._from.value) >= parseInt(o._to.value)) {
      alert('Value from cannot more than value to');
      return;
    }
    if(confirm("Are you sure to update data?")) {
      o.submit();
    }
  }
}
<?php } ?>
</script>
</head>
<body style="margin:8pt">
<!--START: BODY-->
<h5><?php echo STRTOUPPER($_type) ?> FAKTUR PAJAK<hr></h5>
<?php if($_type == 'list') { ?>
<table width="100%" class="table_box">
    <tr>
        <td align="right">
            <form name="frmSrarchByKeyword" method="get">
            <input type="hidden" name="_idx" value="<?php echo $_idx?>">
            <input type="hidden" name="_type" value="<?php echo $_type?>">
            Search : &nbsp;
            <select name="searchBy">
                <option value="bill_code">INVOICE NO</option>
                <option value="fkit_number" <?php echo (isset($_GET['searchBy']) && $_GET['searchBy'] == "fkit_number") ? "selected":""?>>FK NUMBER</option>
            </select> &nbsp;
            <input type="text" name="txtKeyword" size="15" class="fmt" value="<?php echo isset($_GET['txtKeyword']) ? $_GET['txtKeyword'] : ""?>" accesskey="k" onKeyPress="if(window.event.keyCode == 13) searchByKeyword();"> &nbsp;
            </form>
        </td>
        <th width="12%">
            <a href="javascript:searchByKeyword()"><img src="../../_images/icon/search_mini.gif" alt="Search"></a>&nbsp;
            <a href="javascript:document.location.href='p_list_faktur.php?_idx=<?php echo $_idx?>&_type=<?php echo $_type ?>'"><img src="../../_images/icon/list_mini.gif" alt="Show all invoices"></a>
        </th>
    </tr>
</table><br />
<table width="100%" class="table_box">
    <tr height="35px">
        <th>No</th>
        <th>FAKTUR PAJAK NUMBER</th>
        <th>BILL CODE</th>
    </tr>
</table>
<div style="height:430; overflow-y:scroll">
<table width="100%" class="table_c">
<?php
pg_result_seek($result, 0);
while ($column =& fetchRowAssoc($result)) {
?>
      <tr>
        <td align="center"><?php echo ++$oPage->serial ;?></td>
        <td align="center"><?php echo $column['fkit_number'] ?></td>
        <td align="center"><?php echo $column['bill_code'] ?></td>
      </tr>
<?php } ?>
</table>
</div>
<table width="100%" cellpadding="0" cellspacing="2" border="0">
    <tr>
        <td align="center"><?php echo $oPage->putPaging();?></td>
    </tr>
</table>


<?php } else if($_type == 'edit') { ?>
<form name='frmUpdate' method='POST'>
  <input type='hidden' name='p_mode' value='update_number'>
  <input type='hidden' name='_idx' value='<?php echo $_idx?>'>
  <table width="100%" class="table_a">
    <tr>
      <th width="30%">YEAR (YY)</th>
      <td colspan="2"><input name="_year" type="text" class="req" size="2" maxlength="2" value="<?php echo $fk['fk_year'] ?>" onKeyUp="formatNumber(this, 'dot');">
      </td>
    </tr>
    <tr>
      <th>DIGIT</th>
      <td colspan="2"><input name="_digit" type="text" class="req" size="2" maxlength="3" onKeyUp="formatNumber(this, 'dot');" value="<?php echo $fk['fk_digit'] ?>">
      </td>
    </tr>
    <tr>
      <th>FAKTUR NUMBER</th>
      <td>
        FROM <input name="_from" type="text" class="reqn" size="10" maxlength="8" onKeyUp="formatNumber(this, 'dot');" value="<?php echo $fk['fk_from'] ?>"> &nbsp;
        TO <input name="_to" type="text" class="reqn" size="10" maxlength="8" onKeyUp="formatNumber(this, 'dot');" value="<?php echo $fk['fk_to'] ?>">
      </td>
      <td width="20%">
        <button name='btnSave' class='input_btn' style='width:100%;' onclick="checkform(window.document.frmUpdate, 'add')"><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Update"> &nbsp; Update</button>
      </td>
    </tr>
  </table><br />
</form>
<?php } ?>
<!--END: BODY-->
</body>
</html>