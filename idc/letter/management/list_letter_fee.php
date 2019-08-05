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
require_once LIB_DIR . "zk_listing.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, "index.php");

//GLOBAL
$left_loc	= "list_letter_fee.php";


//GLOBAL
$type = array("T"=>"TENDER", "Q"=>"QUOTATION", "B"=>"BUSINESS", "O"=>"OTHERS");
$status = array(1=>"ON PROCESS", "CONFIRMED", "CANCELLED");
$search_by = array("letter_number"=>"lt_reg_no", "ship_to"=>"lt_send_to", "brief_summary"=>"lt_brief_summary");
$_dept      = isset($_GET['cboDept'])? $_GET['cboDept'] : "";
$_type      = isset($_GET['cboType'])? $_GET['cboType'] : "";
$_status    = isset($_GET['cboStatus'])? $_GET['cboStatus'] : "billing";
$_type_fee      = isset($_GET['cboTypeFee'])? $_GET['cboTypeFee'] : "";
$cboSearchBy  = isset($_GET['cboSearchBy'])? $_GET['cboSearchBy'] : "";
$txtSearchBy  = isset($_GET['txtSearchBy'])? $_GET['txtSearchBy'] : "";
$period_from  = isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", time()-2592000);
$period_to    = isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", time());

//DEFAULT PROCESS
$sqlQuery = new strSelect("
  SELECT * , 
    (select cus_full_name from ".ZKP_SQL."_tb_customer where cus_code = lt.cus_code) as cus_full_name,
    ".ZKP_SQL."_isFileExist(lt_reg_no) AS is_file_exist,     
    CASE
      WHEN lt_dept = 'A' THEN '../apotik/revise_letter.php?_code='||lt_reg_no
      WHEN lt_dept = 'D' THEN '../dealer/revise_letter.php?_code='||lt_reg_no
      WHEN lt_dept = 'H' THEN '../hospital/revise_letter.php?_code='||lt_reg_no
      WHEN lt_dept = 'P' THEN '../pharmaceutical/revise_letter.php?_code='||lt_reg_no
      WHEN lt_dept = 'G' THEN '../general/revise_letter.php?_code='||lt_reg_no
      WHEN lt_dept = 'M' THEN '../management/revise_letter.php?_code='||lt_reg_no
    END AS go_page
  FROM ".ZKP_SQL."_tb_letter AS lt");
$tmp = array();
$get = array();
$strGet = "";

if($_type != "") {
  $tmp[] = "lt_type_of_letter = '$_type'";
  $get[] = "cboType=$_type";
}

if($_status == "billing") {
  $tmp[] = "lt_is_charge = true";  
} else if($_status == "outstanding") {
  $tmp[] = "lt_is_charge = true";
  if($_type_fee == "") {
    $tmp[] = "lt_stamp_confirm IS NULL AND lt_amount_confirm IS NULL";
  } else if($_type_fee == "stamp") {
    $tmp[] = "lt_stamp_confirm IS NULL";
  } else if($_type_fee == "fee") {
    $tmp[] = "lt_amount_confirm IS NULL";
  }  
} else if($_status == "paid") {
  $tmp[] = "lt_is_charge = true";
  if($_type_fee == "") {
    $tmp[] = "lt_stamp_confirm IS NOT NULL OR lt_amount_confirm IS NOT NULL";
  } else if($_type_fee == "stamp") {
    $tmp[] = "lt_stamp_confirm IS NOT NULL";
  } else if($_type_fee == "fee") {
    $tmp[] = "lt_amount_confirm IS NOT NULL";
  }
}
$get[] = "cboStatus=$_status";

if($_type_fee == "stamp") {
  $tmp[] = "lt_stamp>0";
} else if($_type_fee == "fee") {
  $tmp[] = "lt_amount>0";
}
$get[] = "cboTypeFee=$_type_fee";

if($cboSearchBy != "" && $txtSearchBy != "") {
  $tmp[] = $search_by[$cboSearchBy] . " ILIKE '%$txtSearchBy%'";
  $get[] = "cboSearchBy=$cboSearchBy&txtSearchBy=$txtSearchBy";
}

if($moduleDept != "summary") {
  $tmp[] = "lt_dept = '".strtoupper(substr($moduleDept,0,1))."'"; 
} else {
  if($_dept != "") {
    $tmp[] = "lt_dept = '$_dept'";
    $get[] = "cboDept=$_dept";  
  }
}
$tmp[] = "lt_reg_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
$get[] = "period_from=$period_from&period_to=$period_to";

$strWhere   = implode(" AND ", $tmp);
$strWherePaging = implode("&", $get);

$sqlQuery->whereCaluse = $strWhere;
$sqlQuery->setOrderBy("lt_reg_date DESC");

if(isZKError($result =& query($sqlQuery->getSQL())))
  $M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/index.php");

//Total Rows
$numRow = numQueryRows($result);

//Declare Paging
$strGet = $sqlQuery->getQueryString() . $strWherePaging;
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
  $m->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");s
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
<h4>SUMMARY LETTER ADMINISTRATION</h4>
<form name="frmSearch" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
<table width="100%" class="table_layout">
  <tr height="5px"><td></td></tr>
  <tr>
    <td width="40%"> </td>
    <td> TYPE LETTER </td>
    <td> STATUS </td>
    <td> TYPE </td>
    <td> DOCUMENT PERIOD </td>
    <td> SEARCH BY </td>
  </tr>
  <tr>
    <td></td>
    <td>
      <select name="cboType">
        <option value="">==ALL==</option>
        <option value="T">TENDER</option>
        <option value="Q">QUOTATION</option>
        <option value="B">BUSINESS</option>
        <option value="O">OTHERS</option>
      </select>
    </td>
    <td>  
      <select name="cboStatus">
        <option value="">==ALL==</option>
        <option value="billing">HAS BILLING</option>
        <option value="outstanding">OUTSTANDING</option>
        <option value="paid">PAID</option>
      </select>
    </td>
    <td>  
      <select name="cboTypeFee">
        <option value="">==ALL==</option>
        <option value="stamp">STAMP</option>
        <option value="fee">FEE</option>
      </select>
    </td>
    <td>
      <a href="javascript:setFilterDate('period',-1)"> <img src="../../_images/icon/arrow_left.gif" alt="Previous month"> </a>
      <input type="text" name="period_from" size="10" class="fmtd" value="<?php echo $period_from; ?>">&nbsp;
      <input type="text" name="period_to" size="10" class="fmtd"  value="<?php echo $period_to; ?>">
      <a href="javascript:setFilterDate('period',1)"> <img src="../../_images/icon/arrow_right.gif" alt="Next month"> </a>
    </td>
    <td>
      <select name="cboSearchBy">
        <option value=""></option>
        <option value="letter_number">Letter Number</option>
        <option value="ship_to">Customer to</option>
        <option value="brief_summary">Brief Summary</option>
      </select>
      <input type="text" name="txtSearchBy" size="25" class="fmt"  value="<?php echo $txtSearchBy ?>">
    </td>
  </tr>
</table><br />
</form>
<table width="100%" class="table_f">
  <tr height="30px">
    <th width="2%" rowspan="2">No</th>
    <th width="10%" rowspan="2">Reg No</th>
    <th width="7%" rowspan="2">Reg Date</th>
    <th width="25%" rowspan="2">Customer to</th>
    <th width="8%" rowspan="2">Type Letter</th>
    <th rowspan="2">Brief Summary</th>
    <th width="8%" rowspan="2">Status</th>
    <th width="14%" colspan="4">Administration</th>
  </tr>
  <tr>
    <th width="2%"></th>
    <th width="4%">Stamp</th>
    <th width="2%"></th>
    <th width="6%">Fee</th>
  </tr>
  <?php while ($column =& fetchRowAssoc($result)) { ?>
  <tr>
    <td align="center"><?php echo ++$oPage->serial ;?></td>
    <td align="center"><a href="<?php echo $column["go_page"] ?>"><span style="color:blue"><?php echo $column["lt_reg_no"] ?></span></a></td>
    <td align="center"><?php echo date("d-M-y", strtotime($column["lt_reg_date"])) ?></td>
    <td><?php echo ($column["cus_code"]=="") ? "" : "[".trim($column["cus_code"])."] ".$column["cus_full_name"] ?> <?php echo ($column["lt_cus_attn"]=="") ? "" : " - ". $column["lt_cus_attn"] ?></td>
    <td align="center"><?php echo $type[$column["lt_type_of_letter"]] ?></td>
    <td><?php echo cut_string($column["lt_brief_summary"],50) ?></td>
    <td align="center"><?php echo $status[$column["lt_status_of_letter"]] ?></td>
    <td align="center"><?php echo ($column["lt_stamp_confirm"]!="") ? "<img src=\"../../_images/icon/check.jpg\">":"" ?></td>
    <td align="right"><?php echo ($column["lt_stamp"]>0) ? $column["lt_stamp"] : "" ?></td>
    <td align="center"><?php echo ($column["lt_amount_confirm"]!="") ? "<img src=\"../../_images/icon/check.jpg\">":"" ?></td>
    <td align="right"><?php echo ($column["lt_amount"]>0) ? number_format($column["lt_amount"],0) : "" ?></td>
  </tr>
  <?php } ?>
</table>
<table width="100%" cellpadding="0" cellspacing="2" border="0">
  <tr>
    <td align="center"><?php echo $oPage->putPaging();?></td>
  </tr>
</table>
<script language="javascript1.2" type="text/javascript">
  var f = window.document.frmSearch;
  var ts = <?php echo time() * 1000;?>;

  setSelect(f.cboStatus, "<?php echo isset($_GET['cboStatus']) ? $_GET['cboStatus'] : "billing"?>");
  setSelect(f.cboType, "<?php echo isset($_GET['cboType']) ? $_GET['cboType'] : ""?>");
  setSelect(f.cboTypeFee, "<?php echo isset($_GET['cboTypeFee']) ? $_GET['cboTypeFee'] : ""?>");  
  setSelect(f.cboSearchBy, "<?php echo isset($_GET['cboSearchBy']) ? $_GET['cboSearchBy'] : ""?>");

  function setFilterDate(status, value){
    var d = new Date(ts);
    setFilterPeriodCalc(d, value, f.period_from, f.period_to);
    f.submit();
  }

  f.cboStatus.onchange = function() {
    f.submit();
  }
  f.cboType.onchange = f.cboStatus.onchange;
  f.cboTypeFee.onchange = f.cboStatus.onchange;

  f.period_from.onkeypress = function() {
    if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
      f.submit();
    }
  }

  f.period_to.onkeypress = function() {
    if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
      f.submit();
    }
  }

  f.txtSearchBy.onkeypress = function() {
    if(window.event.keyCode == 13) {
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