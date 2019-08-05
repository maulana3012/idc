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
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "input_return_order_step_1.php";
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script src="../../_include/order/input_order_return.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
function checkform(o) {

	var d0  = formatDate2(new Date()); 
	var po  = formatDate2(parseDate(o._po_date.value, 'prefer_euro_format')); 

		if(po < d0) {
			alert("PO date must be same or later than today");
			o._po_date.focus();
			return;
		}
		if(o._ord_date.value != "")	{
			var ord = formatDate2(parseDate(o._ord_date.value, 'prefer_euro_format')); 
			if(po < ord) {
				alert("PO date must be same or later than Order date");
				o._po_date.focus();
				return;
			}
		}

	if (verify(o)) {


		o.submit();
	}

}

function highlighter(type) {
	var f = window.document.frmInsert;
	
	if(type == 'order_type') {
		for(var i=0; i<2; i++) {
			if(f.cboTypeOrd[i].checked) {
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
	window.document.frmInsert.cboTypeOrd[0].checked = true;
	highlighter('order_type');
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
<strong style="font-size:18px;font-weight:bold">
[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] NEW RETURN ORDER (STEP 1 / 2)<br />
</strong><br />
<form name='frmInsert' method='POST' action="./input_return_order_step_2.php">
<input type='hidden' name='p_mode' value='order_info'>
<input type='hidden' name='_return_type'>
<input type='hidden' name='_dept' value='<?php echo $department ?>'>
<table width="100%" class="table_box">
	<tr>
		<td colspan="3"><strong class="info"><br />RETURN TYPE</strong></td>
	</tr>
	<tr height="40px">
		<td width="33%" id="1"><input type="radio" name="cboTypeOrd" value="0" id="1" onClick="highlighter('order_type')"><label for="1">Issue invoice &amp; Booking Item</label></td>
		<td width="33%" id="2"><input type="radio" name="cboTypeOrd" value="1" id="2" onClick="highlighter('order_type')"><label for="2">Issue invoice only</label></td>
	</tr>
</table><br />
<strong class="info">RETURN INFORMATION</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="12%">RETURN TYPE</th>
		<td>
			<select name="_type" class="req">
				<option value="RO">SALES</option>
				<option value="RK">KONSINYASI</option>
			</select>
		</td>
		<th width="12%">RECEIVED BY</th>
		<td><input name="_received_by" type="text" class="req" id="_received_by" value="<?php echo $S->getValue("ma_account")?>"></td>
		<th width="12%">CONFIRM BY</th>
		<td><input name="_confirm_by" type="text" class="fmt">
		</td>
	</tr>
	<tr>
		<th>PO DATE</th>
		<td><input name="_po_date" type="text" class="reqd" id="_po_date" value="<?php echo date("j-M-Y")?>" maxlength="64"></td>
		<th>PO NO</th>
		<td><input name="_po_no" type="text" class="fmt" maxlength="64"></td>
		<th>VAT</th>
		<td><input name="_vat" type="text" class="fmtn" value="10" size="2" maxlength="4">
		%</td>
	</tr>
</table>
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<th rowspan="2" width="12%">CUSTOMER</th>
		<th width="12%"><a href="javascript:fillCustomer('customer')" accesskey="c"><u>C</u>ODE</a></th>
		<td width="25%">
			<input name="_cus_to" type="text" class="req" size="10" maxlength="7">
		</td>
		<th width="8%">ATTN</th>
		<td width="43%"><input type="text" name="_cus_to_attn" class="fmt" style="width:100%"></td>
	</tr>
	<tr>
		<th width="12%">ADDRESS</th>
		<td colspan="3"><input type="text" name="_cus_to_address" class="fmt"  style="width:100%"></td>
	</tr>
	<tr>
		<th rowspan="2" width="12%">SHIP TO</th>
		<th width="12%"><a href="javascript:fillCustomer('ship')">CODE</a></th>
		<td width="25%">
			<input name="_ship_to" type="text" class="fmt" size="10" maxlength="7">
			<input type="checkbox" name="chkAbove" onClick="copyCustomer(this, 'ship')" id="dahlia"><label for="dahlia">Same as Above</label>
		</td>
		<th width="8%">ATTN</th>
		<td width="43%"><input type="text" name="_ship_to_attn" class="fmt" style="width:100%"></td>
	</tr>
	<tr>
		<th width="12%">ADDRESS</th>
		<td colspan="3"><input type="text" name="_ship_to_address" class="fmt"  style="width:100%"></td>
	</tr>
	<tr>
		<th rowspan="2" width="12%">BILL TO</th>
		<th width="12%"><a href="javascript:fillCustomer('bill')">CODE</a></th>
		<td width="25%">
			<input name="_bill_to" type="text" class="fmt" size="10" maxlength="7">
			<input type="checkbox" name="chkAbove" onClick="copyCustomer(this, 'bill')" id="sana"><label for="sana">Same as Above</label>
		</td>
		<th width="8%">ATTN</th>
		<td width="43%"><input type="text" name="_bill_to_attn" class="fmt" style="width:100%"></td>
	</tr>
	<tr>
		<th width="12%">ADDRESS</th>
		<td colspan="3"><input type="text" name="_bill_to_address" class="fmt"  style="width:100%"></td>
	</tr>
	<tr>
		<th rowspan="2" width="12%">ORDER</th>
		<th width="12%"><a href="javascript:fillOrder()">REF. NO</a></th>
		<td><input name="_ord_code" type="text" class="fmt"></td>
		<th width="12%">DATE</th>
		<td><input name="_ord_date" type="text" class="fmtd"></td>
	</tr>
</table>
</form>
<div align='right'>
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkform(window.document.frmInsert)'><img src="../../_images/icon/next.gif" width="15px" align="middle" alt="Go to next step"> &nbsp; Next step</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_return_order_step_1.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel return order"> &nbsp; Cancel return</button>
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