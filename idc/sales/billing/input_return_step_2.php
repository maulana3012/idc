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
$left_loc = "input_return_step_1.php";

//PROCESS FORM
require_once APP_DIR . "_include/billing/tpl_process_return_form.php"; 

//================================================================================== DEFAULT PROCESS
if($isIssetBillCode) {
	//[WAREHOUSE] billing item
	$whitem_sql = "
	SELECT
	  a.it_code,
	  b.boit_it_code_for,
	  a.it_model_no,
	  a.it_desc,
	  b.boit_qty,
	  b.boit_function,
	  b.boit_remark
	FROM ".ZKP_SQL."_tb_booking_item as b join ".ZKP_SQL."_tb_item as a using(it_code)
	WHERE book_idx = $_book_idx ORDER BY it_code";
	$wh_res	=& query($whitem_sql);

	//[CUSTOMER] billing item
	$cusitem_sql = "
	SELECT 
	  icat_midx, 			--0
	  it_code,			--1
	  it_type,			--2
	  it_model_no,		--3
	  it_desc,			--4
	  biit_unit_price,	--5
	  biit_qty,			--6
	  biit_unit_price * biit_qty AS amount,	--7
	  biit_remark			--8
	FROM ".ZKP_SQL."_tb_billing_item
	WHERE bill_code = '$_bill_code'
	ORDER BY it_code";
	$cus_res	=& query($cusitem_sql);
}
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script src="../../_include/billing/input_return.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
function checkform(o) {

	if(o._paper.value == '0') {
		if(window.itemWHPosition.rows.length <= 0) {
			alert("You need to choose at least 1 item");
			return;
		}
	}
	if (window.itemCusPosition.rows.length <= 0) {
		alert("You need to choose at least 1 item");
		return;
	}

	if (verify(o)) {
		if(confirm("Are you sure to save return?")) {
			o.submit();
		}
	}
}

function initPage() {
	updateAmount();
	initOption();
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
<table width="100%">
  <tr>
	<td>
		<strong style="font-size:18px;font-weight:bold">
		[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] NEW RETURN BILLING (STEP 2 / 2)<br />
		</strong>
	</td>
	<td valign="center" width="25%" align="right" rowspan="2" style="background-color:#F3F3F3;color: #016FA1;">
		<h3><?php echo $ordby[$_ordered_by] ?></h3>
	</td>
  </tr>
  <tr>
	<td colspan="2"><small class="comment">* <?php echo $title[$_paper] ?></small></td>
  </tr>
</table>
<hr><br />
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode' value='insert'>
<input type="hidden" name="_dept" value="<?php echo $department ?>">
<input type="hidden" name="_paper" value="<?php echo $_paper ?>">
<input type="hidden" name="_type_return" value="<?php echo $_type_return ?>">
<input type="hidden" name="_return_date" value="<?php echo $_return_date ?>">
<input type="hidden" name="_received_by" value="<?php echo $_received_by ?>">
<input type="hidden" name="_ship_to_responsible_by" value="<?php echo $_ship_to_responsible_by?>">
<input type="hidden" name="_return_condition" value="<?php echo $_return_condition ?>">
<input type="hidden" name="_cus_to" value="<?php echo $_cus_to ?>">
<input type="hidden" name="_cus_name" value="<?php echo $_cus_name ?>">
<input type="hidden" name="_cus_attn" value="<?php echo $_cus_attn ?>">
<input type="hidden" name="_cus_npwp" value="<?php echo $_cus_npwp ?>">
<input type="hidden" name="_cus_address" value="<?php echo $_cus_address ?>">
<input type="hidden" name="_ship_to" value="<?php echo $_ship_to ?>">
<input type="hidden" name="_ship_name" value="<?php echo $_ship_name ?>">
<input type="hidden" name="_bill_code" value="<?php echo $_bill_code ?>">
<input type="hidden" name="_bill_date" value="<?php echo $_bill_date ?>">
<input type="hidden" name="_is_vat" value="<?php echo $_is_vat ?>">
<input type="hidden" name="_vat" value="<?php echo $_vat ?>">
<input type="hidden" name="_faktur_no" value="<?php echo $_faktur_no ?>">
<input type="hidden" name="_is_bill_paid" value="<?php echo $_is_bill_paid?>">
<input type="hidden" name="_is_money_back" value="<?php echo $_is_money_back?>">
<input type="hidden" name="_do_no" value="<?php echo $_do_no?>">
<input type="hidden" name="_do_date" value="<?php echo $_do_date?>">
<input type="hidden" name="_sj_no" value="<?php echo $_sj_no?>">
<input type="hidden" name="_sj_date" value="<?php echo $_sj_date?>">
<input type="hidden" name="_po_no" value="<?php echo $_po_no?>">
<input type="hidden" name="_po_date" value="<?php echo $_po_date?>">
<?php 
require_once APP_DIR . "_include/billing/tpl_input_return_top.php";
require_once APP_DIR . "_include/billing/tpl_input_return_item_".($_paper+1).".php";
require_once APP_DIR . "_include/billing/tpl_input_return_bottom.php"; 
?>
<input type="hidden" name="_ordered_by" value="<?php echo $_ordered_by ?>">
<input type="hidden" name="web_url" value="<?php echo ZKP_SQL ?>">
</form>
<p align='center'>
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkform(window.document.frmInsert)'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save return"> &nbsp; Save return</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_return_step_1.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel return"> &nbsp; Cancel return</button>
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