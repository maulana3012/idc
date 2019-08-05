<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*/
//REQUIRE
require_once "../../zk_config.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_INSERT, HTTP_DIR . $currentDept . "/packing_list/index.php");

//GLOBAL
$left_loc = "input_pl_step_3.php";
?>
<html>
<head>
<title>PT. INDOCORE PERKASA [MANAGEMENT SYSTEM]</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
function checkform(o) {

	var d1 = parseDate(o._etd_date.value, 'prefer_euro_format');
	var d2 = parseDate(o._eta_date.value, 'prefer_euro_format');

	if (verify(o)) {
		if (d1.getTime() > d2.getTime()) {
			alert("ETA date must be later than ETD date");
			o._etd_date.value = '';
			o._eta_date.value = '';
			o._etd_date.focus();
			return;
		}
		o.submit();
	}
}

function  enabledModeOther(value) {

	var f = window.document.frmInsert;

	if(value == 2) {
		f._mode_desc.readOnly = false;
		f._mode_desc.focus();
	} else {
		f._mode_desc.readOnly = true;
		f._mode_desc.value = '';
	}
	
}

function fillCode(target) {

	if(target == 'supplier') {
		var file	= './p_list_supplier.php?_name=';
		var keyword = window.document.frmInsert._sp_code.value;
	} else if(target == 'forwarder') {
		var file	= './p_list_forwarder.php?_name=';
		var keyword = window.document.frmInsert._fw_code.value;
	}

	var x = (screen.availWidth - 400) / 2;
	var y = (screen.availHeight - 600) / 2;
	var win = window.open(
		file + keyword,
		target,
		'scrollbars,width=400,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
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
<h4>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] NEW PL (STEP 1 / 2)</h4>
<form name='frmInsert' method='POST' action="./input_pl_step_4.php">
<input type='hidden' name='p_mode' value='order_info'>
<input type='hidden' name='_layout_type'>
	<span class="bar_bl">INVOICE INFORMATION</span>
	<table width="100%" class="table_box">
		<tr>
			<th width="12%">INVOICE NO</th>
			<td width="40%"><input name="_inv_no" type="text" class="req" size="15" maxlength="64"></td>
			<th width="15%">INVOICE DATE</th>
			<td><input name="_inv_date" type="text" class="reqd" size="15" value="<?php echo date("j-M-Y")?>" maxlength="64"></td>
		</tr>
		<tr>
			<th>ETD DATE</th>
			<td><input type="text" name="_etd_date" class="reqd" size="15"></td>
			<th>ETA DATE</th>
			<td><input type="text" name="_eta_date" class="reqd" size="15"></td>
		</tr>
		<tr>
			<th>RECEIVED BY</th>
			<td><input name="_received_by" type="text" class="req" size="20" maxlength="32" value="<?php echo $S->getValue("ma_account")?>"></td>
		</tr>
		<tr>
			<th>PL TYPE</th>
			<td>
				<input type="radio" name="_type" value="1" disabled >NORMAL &nbsp;
				<input type="radio" name="_type" value="2" disabled checked>DOOR TO DOOR
			</td>
			<th>SHIPMENT MODE</th>
			<td>
				<input type="radio" name="_shipment_mode" value="sea" id="sea" onclick="enabledModeOther(0)" checked><label for="sea">SEA</label> &nbsp;
				<input type="radio" name="_shipment_mode" value="air" id="air" onclick="enabledModeOther(1)"><label for="air">AIR</label> &nbsp;
				<input type="radio" name="_shipment_mode" value="other" id="other" onclick="enabledModeOther(2)"><label for="other">OTHER</label> &nbsp;
				<input type="text" name="_mode_desc" class="fmt" size="10" maxlength="15" readonly>
			</td>
		</tr>
	</table>
	<table width="100%" class="table_box" cellspacing="1">
		<tr>
			<th rowspan="3" width="12%">SUPPLIER</th>
			<th width="12%"><a href="javascript:fillCode('supplier')">CODE</a></th>
			<td WIDTH="28%"><input name="_sp_code" type="text" class="req" size="6" maxlength="4"></td>
			<th width="15%">NAME</th>
			<td><input type="text" name="_sp_name" class="req" style="width:100%"></td>
		</tr>
		<tr>
			<th>ATTN</th>
			<td><input type="text" name="_sp_attn" class="fmt" size="25"></td>
			<th>CC</th>
			<td><input type="text" name="_sp_cc" class="fmt" size="25"></td>
		</tr>
		<tr>
			<th>TELP</th>
			<td><input type="text" name="_sp_phone" class="fmt" size="25"></td>
			<th>FAX</th>
			<td><input type="text" name="_sp_fax" class="fmt" size="25"></td>
		</tr>
	</table>
</form>
<div align='right'>
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkform(window.document.frmInsert)'><img src="../../_images/icon/next.gif" width="15px" align="middle" alt="Go to next step"> &nbsp; Next step</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_pl_step_3.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel PL"> &nbsp; Cancel PL</button> 
</div>
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