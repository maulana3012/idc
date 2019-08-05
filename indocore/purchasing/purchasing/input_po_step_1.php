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
ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "input_po_step_1.php";
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
function checkform(o) {
	if (verify(o)) {
		o.submit();
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

function seePOLayout() {

	var f = window.document.frmInsert;

	if(f._layout_type[0].checked == true){
		var type = 1;
	} else if(f._layout_type[1].checked == true){
		var type = 2;
	} else if(f._layout_type[2].checked == true){
		var type = 3;
	} else if(f._layout_type[3].checked == true){
		var type = 4;
	}

	var x = (screen.availWidth - 600) / 2;
	var y = (screen.availHeight - 250) / 2;
	var win = window.open(
		'./p_po_layout.php?_type=' + type,
		'',
		'scrollbars,width=600,height=250,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

function highlighter(type) {
	var f = window.document.frmInsert;
	
	if(type == 'bill_type') {
		for(var i=0; i<2; i++) {
			if(f.cboTypePO[i].checked) {
				document.getElementById(i+1).style.backgroundColor="#4e6074";
				document.getElementById(i+1).style.color="#fff";
			} else {
				document.getElementById(i+1).style.backgroundColor="#fff";
				document.getElementById(i+1).style.color="#666";
			}
		}
	} 
}

function initPage(){
	highlighter('bill_type');
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
<h4>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] NEW PO (STEP 1 / 2)</h4>
<form name='frmInsert' method='POST' action="./input_po_step_2.php">
<input type='hidden' name='p_mode' value='order_info'>
<input type='hidden' name='_order_by' value='<?php echo (ZKP_FUNCTION=='ALL')?"":$cboFilter[1][ZKP_FUNCTION][0][0] ?>'>
<table width="100%" class="table_box">
	<tr>
		<td colspan="3"><strong class="info"><br />PO TYPE</strong></td>
	</tr>
	<tr height="40px">
		<td width="50%" id="1"><input type="radio" name="cboTypePO" value="1" id="po_item" onClick="highlighter('bill_type')" checked><label for="po_item">Issue PO &amp; Order Item</label></td>
		<td width="50%" id="2"><input type="radio" name="cboTypePO" value="2" id="po_invoice" onClick="highlighter('bill_type')"><label for="po_invoice">Invoice Only</label></td>
	</tr>
</table><br />
	<span class="bar_bl">PO INFORMATION</span>
	<table width="100%" class="table_box">
		<tr>
			<th width="15%">PO NO</th>
			<td><input name="_po_no" type="text" class="fmt" size="15" maxlength="64" readonly></td>
			<th width="15%">PO DATE</th>
			<td><input name="_po_date" type="text" class="reqd" size="15" value="<?php echo date("j-M-Y")?>" maxlength="64"></td>
		</tr>
		<tr>
			<th width="15%">PO TYPE</th>
			<td>
				<input type="radio" name="_po_type" value="1" id="1" checked><label for="1">NORMAL</label> &nbsp;
				<input type="radio" name="_po_type" value="2" id="2"><label for="2">DOOR TO DOOR</label>
			</td>
			<th>SHIPMENT MODE</th>
			<td>
				<input type="radio" name="_shipment_mode" value="sea" id="sea" onClick="enabledModeOther(0)" checked><label for="sea">SEA</label> &nbsp;
				<input type="radio" name="_shipment_mode" value="air" id="air" onClick="enabledModeOther(1)"><label for="air">AIR</label> &nbsp;
				<input type="radio" name="_shipment_mode" value="other" id="other" onClick="enabledModeOther(2)"><label for="air">OTHER</label> &nbsp;
				<input type="text" name="_mode_desc" class="fmt" size="10" maxlength="15" readonly>
			</td>
		</tr>
		<tr>
			<th width="15%">RECEIVED BY</th>
			<td width="34%"><input name="_received_by" type="text" class="req" size="20" maxlength="32" value="<?php echo $S->getValue("ma_account")?>"></td>
			<th>LAYOUT TYPE</th>
			<td>
				<input type="radio" name="_layout_type" value="1" id="1" checked><label for="1">1 &nbsp; &nbsp;</label>
				<input type="radio" name="_layout_type" value="2" id="2"><label for="2">2 &nbsp; &nbsp;</label>
				<input type="radio" name="_layout_type" value="3" id="3"><label for="3">3 &nbsp; &nbsp;</label>
				<input type="radio" name="_layout_type" value="4" id="4"><label for="4">4 &nbsp; &nbsp;</label>
				<a href="javascript:seePOLayout()"><small>see layout</small></a>
			</td>
		</tr>
		<tr>
			<th>CURRENCY TYPE</th>
			<td>
				<input type="radio" name="_currency_type" value="1" id="usd" checked><label for="usd">USD &nbsp; &nbsp;</label>
				<input type="radio" name="_currency_type" value="2" id="rp"><label for="rp">RUPIAH &nbsp; &nbsp;</label>
			</td>
		</tr>
	</table>
	<table width="100%" class="table_box" cellspacing="1">
		<tr>
			<th rowspan="3" width="12%">SUPPLIER</th>
			<th width="12%"><a href="javascript:fillCode('supplier')">CODE</a></th>
			<td width="25%"><input name="_sp_code" type="text" class="req" size="6" maxlength="4"></td>
			<th width="15%">NAME</th>
			<td width="43%"><input type="text" name="_sp_name" class="req" style="width:100%"></td>
		</tr>
		<tr>
			<th width="12%">ATTN</th>
			<td><input type="text" name="_sp_attn" class="fmt" size="25"></td>
			<th width="12%">CC</th>
			<td><input type="text" name="_sp_cc" class="fmt" size="25"></td>
		</tr>
		<tr>
			<th>TELP</th>
			<td><input type="text" name="_sp_phone" class="fmt" size="25"></td>
			<th>FAX</th>
			<td><input type="text" name="_sp_fax" class="fmt" size="25"></td>
		</tr>
		<tr>
			<th rowspan="3" width="12%">FORWARDER</th>
			<th width="12%"><a href="javascript:fillCode('forwarder')">CODE</a></th>
			<td width="25%"><input name="_fw_code" type="text" class="fmt" size="6" maxlength="4"></td>
			<th width="8%">NAME</th>
			<td width="43%"><input type="text" name="_fw_name" class="fmt" style="width:100%"></td>
		</tr>
		<tr>
			<th>TELP</th>
			<td><input type="text" name="_fw_phone" class="fmt" size="25"></td>
			<th>FAX</th>
			<td><input type="text" name="_fw_fax" class="fmt" size="25"></td>
		</tr>
		<tr>
			<th>MOBILE PHONE</th>
			<td><input type="text" name="_fw_mobile_phone" class="fmt" size="25"></td>
			<th>CONTACT</th>
			<td><input type="text" name="_fw_contact" class="fmt" size="25"></td>
		</tr>
	</table>
</form>
<div align='right'>
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkform(window.document.frmInsert)'><img src="../../_images/icon/next.gif" width="15px" align="middle" alt="Go to next step"> &nbsp; Next step</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_po_step_1.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel PO"> &nbsp; Cancel PO</button>
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