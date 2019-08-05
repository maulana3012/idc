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
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "inventory_summary_by_customer.php";
$_cus_to		= isset($_GET['_cus_to'])? urldecode($_GET['_cus_to']) : "";
$_cus_to_attn	= isset($_GET['_cus_to_attn'])? urldecode($_GET['_cus_to_attn']) : "";
$_cus_to_address =isset($_GET['_cus_to_address'])? urldecode($_GET['_cus_to_address']) : "";

//header
$date_sql = "select max(sl_date) AS max_date from ".ZKP_SQL."_tb_sales_log where cus_code = '$_cus_to' ";
$res  =& query($date_sql);
$date =& fetchRowAssoc($res);
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
function fillCustomer(code) {

	var x = (screen.availWidth - 400) / 2;
	var y = (screen.availHeight - 600) / 2;
    var dept = window.document.frmInsert._dept.value;

	var win = window.open(
        '../../_include/order/p_list_cus_code.php?_dept='+dept+'&_check_code='+ code,
		'summary_inv',
		'scrollbars,width=400,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	win.focus();
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
<h4>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] INVENTORY SUMMARY BY CUSTOMER</h4>
<?php if($date["max_date"] != '0001-01-01') { ?>
<div align="right" style="color:#016FA1;font-size:13px">
"Last sales updated stock on <b><?php echo date("d-M-Y", strtotime($date["max_date"])) ?></b>"
</div>
<?php } ?>
<form name="frmInsert" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
<input type="hidden" name="_dept" value="<?php echo $department ?>">
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<th width="12%">CUSTOMER CODE</th>
		<td width="25%">
			<input name="_cus_to" type="text" class="req" size="10" maxlength="7" value="<?php echo $_cus_to;?>">
			&nbsp;<button class="input_sky" style="height:19px" onClick="fillCustomer(window.document.frmInsert._cus_to.value)">SEARCH</button>
		</td>
		<th width="8%">ATTN</th>
		<td width="43%"><input type="text" name="_cus_to_attn" class="fmt" style="width:100%" value="<?php echo $_cus_to_attn;?>" readonly></td>
	</tr>
	<tr>
		<th width="12%">ADDRESS</th>
		<td colspan="3"><input type="text" name="_cus_to_address" class="fmt"  style="width:100%" value="<?php echo $_cus_to_address;?>" readonly></td>
	</tr>
</table>
</form><br />
<?php require_once APP_DIR . "_include/order/report/rpt_inventory_summary_by_customer.php" ?>
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