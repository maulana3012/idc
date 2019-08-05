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
ckperm(ZKP_INSERT, "javascript:window.close();");

//Check PARAMETER
if(!isset($_GET['_code']) && $_GET['_code'] != '')
	die("<script language=\"javascript1.2\">window.close();</script>");

$strGet		= "";
$_code		= trim($_GET['_code']);
$_item		= trim($_GET['_item']);
$_qty		= trim($_GET['_qty']);
$_loc		= isset($_GET['_loc']) ? $_GET['_loc'] : 1;

//DEFAULT PROCESS
$sqlQuery = new strSelect("SELECT sted_expired_date, sted_wh_location, ".ZKP_SQL."_getRemainMonth(sted_expired_date) AS expired_date, sum(sted_qty) AS remain_month FROM ".ZKP_SQL."_tb_stock_ed");
$sqlQuery->whereCaluse = "sted_qty>0 AND it_code='$_code' AND sted_wh_location=$_loc";
$strGet = "&_code=$_code&_loc=$_loc";
$sqlQuery->setGroupBy("sted_expired_date, sted_wh_location, expired_date");
$sqlQuery->setOrderBy("sted_expired_date");
if(isZKError($result =& query($sqlQuery->getSQL())))
	$M->goErrorPage($result,  "javascript:window.close();");
$numRow = numQueryRows($result);
?>
<html>
<head>
<title>SET EXPIRED DATE</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="text/javascript" type="text/javascript" src="../../_script/aden.js"></script>
<script language="javascript" type="text/javascript">
function createNewED() {
	var f = window.document.frmInsert;

	if(parseDate(f._rows_date.value, 'prefer_euro_format') == false) {
		alert("Please input E/D with fate format");
		return;
	} else if(f._qty.value == '' || f._qty.value== 0) {
		alert("Please input qty");
		return;
	}
	f._rows_date.value = formatDate(parseDate(f._rows_date.value, 'prefer_euro_format'), '1-'+'NNN-yyyy');
	f._qty.value = removecomma(numFormatval(f._qty.value+'',2));
	window.opener.createED();
	window.document.frmInsert._qty.value='';
	window.document.frmInsert._qty.focus();
}
</script>
</head>
<body style="margin:8pt">
<table width="100%" cellpadding="0">
	<tr><td colspan="2" background="../../_images/properties/p_leftmenu_bg05.gif"><img src="../../_images/properties/p_dot.gif"></td></tr>
    <tr bgcolor=#f9f9fb>
        <td height="25"><img src="../../_images/icon/setting.gif">&nbsp;&nbsp;<strong>ITEM DESCRIPTION</strong></td>
    </tr>
    <tr><td colspan="2" background="../../_images/properties/p_leftmenu_bg05.gif"><img src="../../_images/properties/p_dot.gif"></td></tr>
</table>
<table width="100%" cellpadding="0">
 	<tr>
		<td width="2%"><img src="../../_images/properties/p_leftmenu_icon02.gif"></td>
		<td><strong>Item Information</strong></td>
    </tr>
    <tr>
		<td></td>
    	<td>
<form name="frmSearch" method="GET">
<input type='hidden' name='_code' value='<?php echo $_code ?>'>
<input type='hidden' name='_item' value='<?php echo $_item ?>'>
<input type='hidden' name='_loc' value='<?php echo $_loc ?>'>
<table width="100%" class="table_l">
	<tr>
		<th>ITEM</th>
		<td colspan="2"><strong class="info"><font color="#446fbe" style="font-weight:bold">[<?php echo $_code ?>]</font> <?php echo $_item ?></strong></td>
	</tr>
	<tr>
		<th width="25%">LOCATION</th>
		<td>
<?php 
$wh = array($cboFilter[3]['purchasing'][ZKP_FUNCTION], count($cboFilter[3]['purchasing'][ZKP_FUNCTION]));
for($i=0; $i<$wh[1]; $i++) {
	$v = (intval($_loc)==intval($wh[0][$i][0]))?' checked':'';
	echo "\t\t\t<input type=\"radio\" name=\"_location\" value=\"".$wh[0][$i][0]."\" id=\"".$wh[0][$i][1]."\" disabled".$v."><label for=\"".$wh[0][$i][1]."\"> ".$wh[0][$i][1]." </label>\n";
}
?>
		</td>
	</tr>
</table>
</form>
    	</td>
    </tr>
    <tr height="10">	
    	<td></td>
    </tr>
    <tr>
		<td valign="top"><img src="../../_images/properties/p_leftmenu_icon02.gif"></td>
    	<td>
<form name="frmInsert" method="GET">
<input type='hidden' name='_code' value='<?php echo $_code ?>'>
<input type='hidden' name='_item' value='<?php echo $_item ?>'>
<input type='hidden' name='_loc' value='<?php echo $_loc ?>'>
<strong>Select E/D Stock</strong>
<table width="100%" class="table_l">
	<tr>
		<th width="20%">E/D</th>
		<td><input type="text" name="_rows_date" style="width:100%" class="fmtd"></td>
		<th width="20%">QTY</th>
		<td><input type="text" name="_qty" class="reqn" style="width:60%" value="0" onKeyPress="if(window.event.keyCode == 13) createNewED()"></td>
	</tr>
</table>
<div align="right">
	<button name='btnAdd' class='input_sky' style='width:50px;height:25px' onclick='createNewED()'><img src="../../_images/icon/add.gif" width="15px" align="middle" alt="Add"></button>&nbsp;
	<button name='btnClose' class='input_sky' style='width:50px;height:25px' onclick='window.close()'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Close pop-up"></button>
</div>
</form>
    	</td>
    </tr>
</table>
</body>
</html>