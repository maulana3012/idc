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
require_once APP_DIR . "_include/order/tpl_process_form.php";

//DEFAULT PROCESS  ============================================================================================
$sql = "
SELECT *, 
 (SELECT book_idx FROM ".ZKP_SQL."_tb_booking WHERE book_doc_ref='$_code' AND book_doc_type=2) AS book_idx,
 (SELECT book_is_revised FROM ".ZKP_SQL."_tb_booking WHERE book_doc_ref='$_code' AND book_doc_type=2) AS book_is_revised,
 ".ZKP_SQL."_isOrderUsed(ord_code) AS order_used,
 CASE
  WHEN ord_type_invoice=0 AND ord_cfm_wh_delivery_timestamp is not null THEN true
  WHEN ord_type_invoice=1 AND ord_cfm_deli_timestamp is not null THEN true
  ELSE false
 END AS is_ord_lock
FROM ".ZKP_SQL."_tb_order WHERE ord_code = '$_code' AND ord_dept='{$department}'";
if(isZKError($result =& query($sql))) $M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");
$column =& fetchRowAssoc($result);
$template = $column['ord_type_invoice']; 

if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else if($column['ord_cfm_wh_delivery_timestamp'] == '') {
	// Harus DO yang Sudah di confirm gudang
	$message = new ZKError(
		"HAS_NOT_CONFIRMED_BY_WAREHOUSE",
		"HAS_NOT_CONFIRMED_BY_WAREHOUSE",
		"Order no $_code has not been confirmed by warehouse. Please check again");
	$M->goErrorPage($message,  HTTP_DIR . "$currentDept/$moduleDept/revise_order.php?_code=".urlencode($_code));
} else if($column['book_is_revised'] == 't') {
	// Bukan DO yang dalam proses revisi
	$message = new ZKError(
		"ERROR_STATUS_REVISED",
		"ERROR_STATUS_REVISED",
		"Dokumen no $_code status is revised. You cannot change item or qty before the document confirm by warehouse");
	$M->goErrorPage($message,  HTTP_DIR . "$currentDept/$moduleDept/revise_order.php?_code=".urlencode($_code));
}

if($department == 'A') {
	//take discount percentage from customer group
	$sql = "SELECT cug_basic_disc_pct from ".ZKP_SQL."_tb_customer_group WHERE cug_code = (SELECT cug_code FROM ".ZKP_SQL."_tb_customer WHERE cus_code = '".$column["ord_cus_to"]."')";
	isZKError($res =& query($sql)) ? $M->goErrorPage($res, HTTP_DIR . "$currentDept/$moduleDept/revise_order.php?_code=$_code") : false;
	$disc = fetchRow($res);
} else {
	$sql = "SELECT cug_basic_disc_pct from ".ZKP_SQL."_tb_customer_group WHERE cug_code = (SELECT cug_code FROM ".ZKP_SQL."_tb_customer WHERE cus_code = '".$column["ord_cus_to"]."')";
	isZKError($res =& query($sql)) ? $M->goErrorPage($res, HTTP_DIR . "$currentDept/$moduleDept/input_order_step_1.php") : false;
	$disc = fetchRow($res);
	$disc[0] = (empty($disc[0])) ? 0 : $disc[0];
}

//[WAREHOUSE] order item
$book_idx = ($column["book_idx"]=='')?0:$column["book_idx"];
$whitem_sql = "
SELECT
  a.it_code,			--0
  a.icat_midx,			--1
  a.it_model_no,		--2
  a.it_type,			--3
  a.it_desc,			--4
  b.boit_it_code_for,	--5
  b.boit_qty,			--6
  b.boit_function,		--7
  b.boit_remark, 		--8
  b.boit_type			--9
FROM
  ".ZKP_SQL."_tb_booking_item AS b
  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE b.book_idx = $book_idx
ORDER BY a.it_code";
$wh_res	=& query($whitem_sql);

//[CUSTOMER] order item
$cusitem_sql =
"SELECT
  a.it_code,			--0
  a.it_model_no,		--1
  a.it_desc,			--2
  b.odit_unit_price,	--3
  b.odit_qty,			--4
  b.odit_unit_price * b.odit_qty AS amount,				--5
  to_char(b.odit_delivery, 'DD-Mon-YYYY') AS delivery,	--6
  b.odit_remark											--7
FROM ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_order_item AS b ON (a.it_code = b.it_code)
WHERE b.ord_code = '$_code'";
$cus_res =& query($cusitem_sql);
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script src="../../_include/order/input_order.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
function initPage(){
	if(window.document.frmInsert.is_ord_lock.value == 't') {
		window.document.frmInsert.btnChangeOrder.disabled = true;
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
		[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] REVISE ORDER<br />
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
<input type="hidden" name="_code" value="<?php echo $column['ord_code']?>">
<input type="hidden" name="_book_idx" value="<?php echo $column['book_idx']?>">
<input type="hidden" name="_type_invoice" value="<?php echo $column['ord_type_invoice']?>">
<input type="hidden" name="_type" value="<?php echo $column['ord_type']?>">
<input type="hidden" name="_revision_time" value="<?php echo $column['ord_revision_time']?>">
<input type="hidden" name="_deli_date">
<?php 
require_once APP_DIR . "_include/order/tpl_revise_order_top.php";
require_once APP_DIR . "_include/order/tpl_revise_item_1.php"; 
require_once APP_DIR . "_include/order/tpl_revise_order_bottom.php";

//[WAREHOUSE] outgoing item
$outitem_sql = "
SELECT trim(it_code), otst_qty
FROM
  ".ZKP_SQL."_tb_outgoing_v2
  JOIN ".ZKP_SQL."_tb_outgoing_stock_v2 USING(out_idx)
WHERE out_doc_ref = '".trim($_code)."'
ORDER BY it_code";
$outitem_res =& query($outitem_sql);

while($items =& fetchRow($outitem_res)) {
	$out_item[0][] = $items[0];
	$out_item[1][] = $items[1];
	echo "<input type=\"hidden\" name=\"_out_it_code[]\" value=\"". $items[0]. "\">";
	echo "<input type=\"hidden\" name=\"_out_it_qty[]\" value=\"". $items[1]. "\">\n";
}
?>
<input type="hidden" name="is_ord_lock" value="<?php echo $column["is_ord_lock"]?>">
<input type="hidden" name="_dept" value="<?php echo $column['ord_dept']?>">
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td>
			<button name='btnDelete' class='input_red' style='width:120px;'><img src="../../_images/icon/trash.gif" width="15px" align="middle" alt="Delete order"> &nbsp; Delete order</button>
		</td>
		<td align="right">
			Rev No:
			<select name="revision_time">
			<?php
				for($counter = $column['ord_revision_time']; $counter >= 0; $counter--) {
					echo "\t\t\t<option value=\"$counter\">$counter</option>\n";
				}
			?>
			</select>&nbsp;
			<button name='btnPrint' class='input_btn' style='width:100px;'><img src="../../_images/icon/print.gif" width="18px" align="middle" alt="Print pdf"> &nbsp; Print PDF</button>&nbsp;
			<button name='btnUpdate' class='input_btn' style='width:80px;'><img src="../../_images/icon/update.gif" width="20px" align="middle" alt="Update order"> &nbsp; Update</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
		</td>
</table><br /><br />
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
		winforPrint.document.location.href = "../../_include/order/pdf/download_pdf.php?_code=<?php echo trim($_code)."&_dept=".$currentDept."&_po_date=".date("Ym", strtotime($column['ord_po_date']))?>&_rev=" + window.document.all.revision_time.value;
	}

	window.document.all.btnUpdate.onclick = function() {
		if(oForm._type_invoice.value == 0) {
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
				oForm.p_mode.value = 'update_order_revised';
				oForm.submit();
			}
		}

	}

	window.document.all.btnList.onclick = function() {
		window.location.href = "<?php echo HTTP_DIR . "$currentDept/summary_order/" ?>summary_order_by_group.php";
	}
</script>
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