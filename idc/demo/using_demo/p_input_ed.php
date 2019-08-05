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
	var d = parseDate(f._expired_date.value, 'prefer_euro_format');

	if(d == null) {
		alert("Please input expired date with proper format");
		f._expired_date.value = '';
		f._expired_date.focus();
		return;
	} else if(f._qty.value == '' || f._qty.value== 0) {
		alert("Please input qty");
		f._qty.focus();
		return;
	}
	f._expired_date.value = formatDate(d, "d-NNN-yyyy");

	window.opener.createED();
	window.document.frmInsert._expired_date.value='';
	window.document.frmInsert._qty.value='';
	window.document.frmInsert._expired_date.focus();
}
</script>
</head>
<body style="margin:8pt" onload="window.document.frmInsert._expired_date.focus();">
<table width="100%" cellpadding="0" class="main">
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
<table width="100%" class="table_l">
	<tr>
		<th width="20%">ITEM</th>
		<td colspan="2"><strong class="info"><font color="#446fbe" style="font-weight:bold">[<?php echo $_code ?>]</font> <?php echo $_item ?></strong></td>
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
<strong>Input E/D Stock</strong>
<table width="100%" class="table_l">
	<tr>
		<th width="20%">E/D</th>
		<td><input type="text" name="_expired_date" class="reqd" size="15"></td>
		<th width="20%">USE</th>
		<td width="25%"><input type="text" name="_qty" class="reqn" style="width:60%" value="0" onKeyUp="formatNumber(this, 'dot');" onKeyPress="if(window.event.keyCode == 13) createNewED()"></td>
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