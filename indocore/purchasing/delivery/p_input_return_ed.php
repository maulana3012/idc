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
$_value		= trim($_GET['_value']);
?>
<html>
<head>
<title>SET EXPIRED DATE</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="text/javascript" type="text/javascript" src="../../_script/aden.js"></script>
<script language="javascript" type="text/javascript">
function enabledText(type, o) {
	var f = window.document.frmInsert;

	if(type == 3 && o == 'on') {
		f._sn.disabled			= false;
		f._warranty.disabled	= false;
		f._desc.disabled		= false;
		f._date.disabled		= true;
		f._qty.disabled			= true;
		f._loc[0].disabled		= true;
		f._loc[1].disabled		= true;

		f._date.value			= '';
		f._qty.value			= '';

		f._date.className		= 'fmtd';
		f._qty.className		= 'fmtn';
		f._sn.className			= 'req';
		f._warranty.className	= 'reqd';

		f._sn.focus();
	} else {
		f._sn.disabled			= true;
		f._warranty.disabled	= true;
		f._desc.disabled		= true;
		f._date.disabled		= false;
		f._qty.disabled			= false;
		f._loc[0].disabled		= false;
		f._loc[1].disabled		= false;

		f._sn.value				= '';
		f._warranty.value		= '';
		f._desc.value			= '';

		f._date.className		= 'reqd';
		f._qty.className		= 'reqn';
		f._sn.className			= 'fmt';
		f._warranty.className	= 'fmtd';

		f._date.focus();
	}
}

function createNewED() {
	var f = window.document.frmInsert;
	var loc  = 0;
	var type = 0;

	if(verify(f)){
		if(f._type[0].checked==true) {
			if(f._qty.value == '' || f._qty.value== 0) {
				alert("Please input qty");
				return;
			}
		}

		if(f._loc[0].checked==true){loc = 1;}
		else if(f._loc[1].checked==true){loc = 2;}

		if(f._type[0].checked==true){type = 1;}
		else if(f._type[1].checked==true){type = 2;}
		else if(f._type[2].checked==true){type = 3;}

		f._location.value	= loc;
		f._save_to.value	= type;

		if(type==1 || type==2) {window.opener.createED();}
		else if(type==3) {window.opener.createRejectDesc();}

		window.document.frmInsert._date.value	='';
		window.document.frmInsert._qty.value	='';
		window.location.reload();
	}
}

function initPage() {
<?php if($_value=='false') { ?>
	window.document.frmInsert._type[2].checked = true;
	window.document.frmInsert._type[0].disabled = true;
	window.document.frmInsert._type[1].disabled = true;
	enabledText(3, 'on');
	window.document.frmInsert._sn.focus();
<?php } else { ?>
	window.document.frmInsert._date.focus();
<?php } ?>
}
</script>
</head>
<body style="margin:8pt" onload="initPage()">
<table width="100%" cellpadding="0">
	<tr><td colspan="2" background="../../_images/properties/p_leftmenu_bg05.gif"><img src="../../_images/properties/p_dot.gif"></td></tr>
    <tr bgcolor=#f9f9fb>
        <td height="25"><img src="../../_images/icon/setting.gif">&nbsp;&nbsp;<strong>ITEM DESCRIPTION</strong></td>
    </tr>
    <tr><td colspan="2" background="../../_images/properties/p_leftmenu_bg05.gif"><img src="../../_images/properties/p_dot.gif"></td></tr>
</table>
<table width="100%" cellpadding="0">
    <tr>
		<td valign="top"><img src="../../_images/properties/p_leftmenu_icon02.gif"></td>
    	<td>
<form name="frmInsert" method="GET">
<input type='hidden' name='_code' value='<?php echo $_code ?>'>
<input type='hidden' name='_item' value='<?php echo $_item ?>'>
<input type='hidden' name='_location'>
<input type='hidden' name='_save_to' value='<?php echo $_item ?>'>
<strong>Input Return E/D</strong>
<table width="100%" class="table_l">
	<tr>
		<th>ITEM</th>
		<td colspan="3"><font color="#446fbe" style="font-weight:bold">[<?php echo $_code ?>]</font> <?php echo $_item ?></td>
	</tr>
	<tr>
		<th>SAVE TO</th>
		<td colspan="3">
			<input type="radio" name="_type" id="1" onclick="enabledText(1,this.value)" checked><label for="1">Stock &nbsp;</label>
			<input type="radio" name="_type" id="2" onclick="enabledText(2,this.value)"><label for="2">Demo Unit &nbsp;</label>
			<input type="radio" name="_type" id="3" onclick="enabledText(3,this.value)"><label for="3">Reject</label>
		</td>
	</tr>
	<tr>
		<th width="25%">E/D</th>
		<td width="40%"><input type="text" name="_date" class="reqd" size="15"></td>
		<th width="25%">QTY</th>
		<td><input type="text" name="_qty" size="3" class="reqn" onKeyUp="formatNumber(this, 'dot');" onKeyPress="if(window.event.keyCode == 13) createNewED()"></td>
	</tr>
	<tr>
		<th>LOCATION</th>
		<td colspan="3">
			<input type="radio" name="_loc" id="idc" checked><label for="idc">Indocore &nbsp;</label>
			<input type="radio" name="_loc" id="dnr"><label for="dnr">DNR</label>
		</td>
	</tr>
	<tr>
		<th>SN</th>
		<td><input type="text" name="_sn" class="fmt" style="width:100%" maxlength="32" disabled></td>
		<th>WARRANTY</th>
		<td><input type="text" name="_warranty" class="fmtd" size="15" disabled onKeyPress="if(window.event.keyCode == 13) createNewED()"></td>
	</tr>
	<tr>
		<th>DESC</th>
		<td colspan="3"><input type="text" name="_desc" class="fmt" style="width:100%" maxlength="255" disabled onKeyPress="if(window.event.keyCode == 13) createNewED()"></td>
	</tr>
</table>
</form>
    	</td>
    </tr>
</table>
<div align="right">
	<button name='btnAdd' class='input_sky' style='width:50px;height:25px' onclick='createNewED()'><img src="../../_images/icon/add.gif" width="15px" align="middle" alt="Add"></button>&nbsp;
	<button name='btnClose' class='input_sky' style='width:50px;height:25px' onclick='window.close()'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Close pop-up"></button>
</div>
</body>
</html>