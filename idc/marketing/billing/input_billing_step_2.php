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
$left_loc = "input_billing_step_1.php";

//PROCESS FORM
require_once APP_DIR . "_include/billing/tpl_process_form.php"; 
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script src="../../_include/billing/input_billing.js" type="text/javascript"></script>
<script src="../../_script/js_sales.php?_dept=<?php echo $_dept ?>&_ship_to=<?php echo trim($_ship_to) ?>&_cug_code=<?php echo $_cug_code ?>" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
function checkform(o) {

	if(o._type_bill.value == '1') {
		if (window.itemWHPosition.rows.length <= 0 || window.itemCusPosition.rows.length <= 0) {
			alert("You need to choose at least 1 item");
			return;
		}
	} else {
		if(o._type_bill.value == '2') {
			if (window.rowPosition.rows.length <= 0) {
				alert("You need to choose at least 1 item");
				return;
			}
		} else if(o._type_bill.value == '3') {
			if (window.salesPosition.rows.length <= 0 || window.billPosition.rows.length <= 0) {
				alert("You need to choose at least 1 item");
				return;
			}
		}

		if(o._dept.value == 'A') {
			if(o._sales_from.value.length > 0 || o._sales_to.value.length > 0) {
				var d1 = parseDate(o._sales_from.value, 'prefer_euro_format');
				var d2 = parseDate(o._sales_to.value, 'prefer_euro_format');
				if (d1.getTime() > d2.getTime()) {
					alert("Sales to must be later than sales from");
					o._sales_from.value = '';
					o._sales_to.value = '';
					o._sales_from.focus();
					return;
				} 
			}	
		}
	}

	if (verify(o)) {
		if(confirm("Are you sure to save billing?")) {
			o.submit();
		}
	}
}
</script>
</head>
<body topmargin="0" leftmargin="0" onLoad="initPageInput()">
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
<table width="100%">
  <tr>
	<td>
		<strong style="font-size:18px;font-weight:bold">
		[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] NEW BILLING (STEP 2 / 2)<br />
		</strong>
	</td>
	<td valign="center" width="25%" align="right" rowspan="2" style="background-color:#F3F3F3;color: #016FA1;">
		<h3><?php echo $ordby[$_ordered_by] ?></h3>
	</td>
  </tr>
  <tr>
	<td colspan="2"><small class="comment">* <?php echo $title[$_type_bill] ?></small></td>
  </tr>
</table>
<hr><br />
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode' value='insert_billing'>
<input type="hidden" name="_type_bill" value="<?php echo $_type_bill ?>">
<input type="hidden" name="_dept" value="<?php echo $_dept ?>">
<input type="hidden" name="_received_by" value="<?php echo addslashes($_received_by)?>">
<input type="hidden" name="_inv_date" value="<?php echo $_inv_date?>">
<input type="hidden" name="_do_no" value="<?php echo $_do_no?>">
<input type="hidden" name="_do_date" value="<?php echo $_do_date?>">
<input type="hidden" name="_chk_sj_code" value="<?php echo $_chk_sj_code?>">
<input type="hidden" name="_sj_code" value="<?php echo $_sj_code?>">
<input type="hidden" name="_sj_date" value="<?php echo $_sj_date?>">
<input type="hidden" name="_po_no" value="<?php echo $_po_no?>">
<input type="hidden" name="_po_date" value="<?php echo $_po_date?>">
<input type="hidden" name="_is_vat" value="<?php echo $_btnVat?>">
<input type="hidden" name="_vat_val" value="<?php echo ($_vat=='')?0:$_vat ?>">
<input type="hidden" name="_is_tax" value="<?php echo $_type_of_pajak?>">
<input type="hidden" name="_ship_to_responsible_by" value="<?php echo $_ship_to_responsible_by?>">
<input type="hidden" name="_cug_code" value="<?php echo $_cug_code?>">
<input type="hidden" name="_cus_to" value="<?php echo $_cus_to?>">
<input type="hidden" name="_cus_name" value="<?php echo $_cus_name?>">
<input type="hidden" name="_cus_attn" value="<?php echo $_cus_attn?>">
<input type="hidden" name="_cus_npwp" value="<?php echo addslashes($_cus_npwp)?>">
<input type="hidden" name="_cus_address" value="<?php echo addslashes($_cus_address)?>">
<input type="hidden" name="_ship_to" value="<?php echo addslashes($_ship_to)?>">
<input type="hidden" name="_ship_name" value="<?php echo addslashes($_ship_name)?>">
<input type="hidden" name="_pajak_to" value="<?php echo $_pajak_to?>">
<input type="hidden" name="_pajak_name" value="<?php echo $_pajak_name?>">
<input type="hidden" name="_pajak_address" value="<?php echo addslashes($_pajak_address)?>">
<?php 
require_once APP_DIR . "_include/billing/tpl_input_billing_top.php"; 
require_once APP_DIR . "_include/billing/tpl_input_item_".$_type_bill.".php";
require_once APP_DIR . "_include/billing/tpl_input_billing_bottom.php"; 
?>
<input type="hidden" name="_ordered_by" value="<?php echo $_ordered_by ?>">
<input type="hidden" name="web_url" value="<?php echo ZKP_SQL ?>">
</form>
<p align='center'>
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkform(window.document.frmInsert)'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save billing"> &nbsp; Save billing</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_billing_step_1.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel billing"> &nbsp; Cancel billing</button>
</p>
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