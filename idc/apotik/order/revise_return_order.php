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
$_code		= $_GET['_code'];
$left_loc	= "summary_order_by_group.php";

//PROCESS FORM
require_once APP_DIR . "_include/order/tpl_process_return_form.php";

//================================================================================== DEFAULT PROCESS
$sql = "
SELECT *,
  (SELECT std_idx FROM ".ZKP_SQL."_tb_outstanding WHERE std_doc_ref = '$_code') AS std_idx,
  (SELECT inc_idx FROM ".ZKP_SQL."_tb_incoming WHERE inc_doc_ref = '$_code') AS inc_idx,
  CASE
	WHEN reor_paper=0 AND reor_cfm_wh_delivery_timestamp is not null THEN true
	ELSE false
  END AS is_ord_lock
FROM ".ZKP_SQL."_tb_return_order WHERE reor_code = '$_code' AND reor_dept='{$department}'";
if(isZKError($result =& query($sql))) $M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");
$column =& fetchRowAssoc($result);
$template = $column['reor_paper']; 

if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
}

if($department == 'A') {
	//take discount percentage from customer group
	$sql = "SELECT cug_basic_disc_pct from ".ZKP_SQL."_tb_customer_group WHERE cug_code = (SELECT cug_code FROM ".ZKP_SQL."_tb_customer WHERE cus_code = '".$column["reor_cus_to"]."')";
	isZKError($res =& query($sql)) ? $M->goErrorPage($res, HTTP_DIR . "$currentDept/$moduleDept/revise_order.php?_code=$_code") : false;
	$disc = fetchRow($res);
} else {
	$sql = "SELECT cug_basic_disc_pct from ".ZKP_SQL."_tb_customer_group WHERE cug_code = (SELECT cug_code FROM ".ZKP_SQL."_tb_customer WHERE cus_code = '".$column["reor_cus_to"]."')";
	isZKError($res =& query($sql)) ? $M->goErrorPage($res, HTTP_DIR . "$currentDept/$moduleDept/input_order_step_1.php") : false;
	$disc = fetchRow($res);
	$disc[0] = (empty($disc[0])) ? 0 : $disc[0];
}

if($column['reor_paper'] == 0) {
	//[WAREHOUSE] billing item
	$std_idx = ($column["std_idx"]=='') ? 0:$column["std_idx"];
	$whitem_sql = "
	SELECT
	  a.it_code,
	  b.istd_it_code_for,
	  a.it_model_no,
	  a.it_desc,
	  b.istd_qty,
	  b.istd_function,
	  b.istd_remark
	FROM ".ZKP_SQL."_tb_outstanding_item as b JOIN ".ZKP_SQL."_tb_item as a USING(it_code)
	WHERE std_idx = {$column['std_idx']}
	ORDER BY it_code,istd_idx";
	$wh_res	=& query($whitem_sql);
}

//[CUSTOMER] return item
$cusitem_sql = "
SELECT
 a.it_code,
 a.it_model_no,
 a.it_desc,
 b.roit_remark,
 b.roit_unit_price,
 b.roit_qty,
 b.roit_unit_price * b.roit_qty AS amount
FROM ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_return_order_item AS b ON (a.it_code = b.it_code)
WHERE b.reor_code = '$_code'";
$cus_res	=& query($cusitem_sql);
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
function initPage(){
	if(window.document.frmInsert.is_ord_lock.value == 't') {
		window.document.all.btnDelete.disabled = true;
	}
	updateAmount();
	window.document.all.btnPrint.focus();
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
		[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] REVISE RETURN ORDER<br />
		</strong>
	</td>
  </tr>
  <tr>
	<td colspan="2"><small class="comment">* <?php echo $title[$template+1] ?></small></td>
  </tr>
</table>
<hr><br />
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode' value='update'>
<input type='hidden' name="_ord_code" value="<?php echo $column['ord_code']?>">
<input type='hidden' name="_ord_date" value="<?php echo $column['reor_ord_reference_date']?>">
<input type="hidden" name="_code" value="<?php echo $column['reor_code']?>">
<input type="hidden" name="_type" value="<?php echo $column['reor_type']?>">
<input type="hidden" name="_revesion_time" value="<?php echo $column['reor_revesion_time']?>">
<input type="hidden" name="_deli_date">
<input type="hidden" name="_std_idx" value="<?php echo $column['std_idx']?>">
<input type="hidden" name="_inc_idx" value="<?php echo $column['inc_idx']?>">
<input type="hidden" name="_paper" value="<?php echo $column['reor_paper']?>">
<?php 
require_once APP_DIR . "_include/order/tpl_revise_return_order_top.php"; 
if($column['reor_paper'] == 0) {
	if($column['reor_cfm_wh_delivery_timestamp'] == '') {
		require_once APP_DIR . "_include/order/tpl_revise_return_item_1.php"; 
	} else {
		require_once APP_DIR . "_include/order/tpl_revise_return_item_3.php"; 
	}
} else if($column['reor_paper'] == 1) {
	require_once APP_DIR . "_include/order/tpl_revise_return_item_2.php"; 
}
require_once APP_DIR . "_include/order/tpl_revise_return_order_bottom.php"; 
?>
<input type="hidden" name="is_ord_lock" value="<?php echo $column["is_ord_lock"]?>">
<input type="hidden" name="_dept" value="<?php echo $column['reor_dept']?>">
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td>
			<button name='btnDelete' class='input_red' style='width:120px;'><img src="../../_images/icon/trash.gif" width="15px" align="middle" alt="Delete order"> &nbsp; Delete order</button>
		</td>
		<td align="right">
			Rev No:
			<select name="revesion_time">
			<?php
				for($counter = $column['reor_revesion_time']; $counter >= 0; $counter--) {
					echo "\t\t\t<option value=\"$counter\">$counter</option>\n";
				}
			?>
			</select>&nbsp;
			<button name='btnPrint' class='input_btn' style='width:100px;'><img src="../../_images/icon/print.gif" width="18px" align="middle" alt="Print pdf"> &nbsp; Print PDF</button>&nbsp;
			<button name='btnUpdate' class='input_btn' style='width:80px;'><img src="../../_images/icon/update.gif" width="20px" align="middle" alt="Update return"> &nbsp; Update</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
		</td>
</table>
<script language="javascript" type="text/javascript">
	var oForm = window.document.frmInsert;

	window.document.all.btnDelete.onclick = function() {
		if(confirm("Are you sure to delete?")) {
			oForm.p_mode.value = 'delete';
			oForm.submit();
		}
	}

	window.document.all.btnPrint.onclick = function() {
		var winforPrint = window.open('','','toolbar=no,width=780,height=580,resizable=yes');
		winforPrint.document.location.href = "../../_include/order/pdf/download_pdf.php?_code=<?php echo trim($_code)."&_dept=".$currentDept."&_po_date=".date("Ym", strtotime($column['reor_po_date']))?>&_rev=" + window.document.all.revesion_time.value;
	}

	window.document.all.btnUpdate.onclick = function() {
		if(oForm._paper.value == 0) {
			if (window.itemWHPosition.rows.length <= 0 || window.itemCusPosition.rows.length <= 0) {
				alert("You need to choose at least 1 item");
				return;
			}
		} else {
			if (window.itemCusPosition.rows.length <= 0) {
				alert("You need to choose at least 1 item");
				return;
			}
		}

		if(confirm("Are you sure to update?")) {
			if(verify(oForm)){
				oForm.p_mode.value = 'update';
				oForm.submit();
			}
		}
	}

	window.document.all.btnList.onclick = function() {
		window.location.href = "<?php echo HTTP_DIR ."$currentDept/summary_order/" ?>summary_order_by_group.php";
	}
</script>
<br /><br />
<?php
require_once APP_DIR . "_include/order/tpl_detail_attachment_return.php";
?>
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