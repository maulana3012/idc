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
$left_loc	= "list_stock.php";
$_code		= trim(urldecode($_GET['_code']));
$_type		= (isset($_GET['_type'])) ? urldecode($_GET['_type']) : 'all';
$_location = $cboFilter[4][ZKP_URL];
$_location	= (isset($_GET['_wh_location'])) ? urldecode($_GET['_wh_location']) : '0';
$_activity	= (isset($_GET['cboTypeActivity'])) ? urldecode($_GET['cboTypeActivity']) : '0';
$period_from 	= isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", mktime(0,0,0,date("m"),1, date("Y")));
$period_to 	= isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", mktime(0,0,0,date("m")+1,1, date("Y"))-1);

$v_type = array('all'=>'ALL', 1=>'Normal', 2=>'DTD');
$v_loc = array('ALL', 'MED', 'SMD');

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
function initPage() {
  var f = window.document.frmLog;
  setSelect(f.cboTypeActivity, "<?php echo $_activity?>");
}

function changeItem(obj, val) {
  var f = window.document.frmLog;
  if(obj == 'type') {
    if(val == 'all') { val='1' }
    else if(val == '1') { val='2' }
    else if(val == '2') { val='all' }
    f._type.value = val;
  } else if (obj == 'loc') {
    if(val == '0') { val='1' }
    else if(val == '1') { val='2' }
    else if(val == '2') { val='0' }
    f._wh_location.value = val;
  } else if (obj == 'activity') {
    f.cboTypeActivity.value = val;
  }
  f.submit();
}

function setFilterDate(value){
  var f = window.document.frmLog;
  var ts = <?php echo time() * 1000;?>;
  var d = new Date(ts);

  setFilterPeriodCalc(d, value, f.period_from, f.period_to);
  f.submit();
}

function calcuteQty(o) {
  var a = parseFloat(removecomma($("#totalLastPeriodQty").val()));
  var b = parseFloat(removecomma($("#totalPeriod").val()));
  var c = parseFloat(removecomma($("#totalQty").val()));

  if(o) {
    $("#totalQty").val(numFormatval(a+b+'',2));
  } else {
    $("#totalQty").val(numFormatval(b+'',2));
  }
}
</script>
</head>
<body topmargin="0" leftmargin="0" onload="initPage()">
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
<h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] STOCK DETAIL</h3>
<form name="frmLog" method="GET">
<?php include "history_stock_detail_item.php";?>
<?php include "history_stock_detail_log.php";?>
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