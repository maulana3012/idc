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
$left_loc = 'daily_billing_by_group.php';
if (!isset($_GET['_code']) || $_GET['_code'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else {
	$_code = urldecode($_GET['_code']);
}
$title	= array(1=>	"Revise : Issue Invoice &amp; unconfirmed booking Item",
					"Revise : Issue invoice only",
					"Revise : Issue invoice &amp; linked item from sales report", 
					"DETAIL : Issue Invoice &amp; confirmed booking Item",
					"DETAIL : Issue invoice only",
					"DETAIL : Issue invoice &amp; linked item from sales report"
				);

//PROCESS FORM
require_once APP_DIR . "_include/billing/tpl_process_form.php";

//========================================================================================= DEFAULT PROCESS
if($S->getValue("ma_authority") & 1)	{ $page_permission = false;}
else                                    { $page_permission = true;}

$tmp[]	= "bill_code = '$_code'";
$strWhere = implode(" AND ", $tmp);

//billing
$sql = "
SELECT
  *, ".ZKP_SQL."_isBillingUsed(bill_code) AS billing_used,
  (SELECT count(turn_code) FROM ".ZKP_SQL."_tb_return WHERE turn_bill_code = '$_code') AS has_return,
  ".ZKP_SQL."_isValidShowInvoice('".ZKP_FUNCTION."', '$_code','billing') AS bill_accessible,
  (SELECT book_idx FROM ".ZKP_SQL."_tb_booking WHERE book_doc_ref='$_code' AND book_doc_type=1) AS book_idx,
  CASE
	WHEN bill_type_billing=1 AND bill_cfm_wh_delivery_by_account!='' THEN 4
	WHEN bill_type_billing=2 AND bill_cfm_wh_delivery_by_account!='' THEN 5
	WHEN bill_type_billing=3 AND bill_cfm_wh_delivery_by_account!='' THEN 6
	WHEN bill_type_billing=1 THEN 1
	WHEN bill_type_billing=2 THEN 2
	WHEN bill_type_billing=3 THEN 3
  END AS template,
  CASE
  	WHEN bill_cfm_wh_delivery_by_account != '' THEN true
  	ELSE false
  END AS is_bill_lock
FROM ".ZKP_SQL."_tb_billing WHERE $strWhere";
$result =& query($sql);
$column =& fetchRowAssoc($result);

if($column['bill_accessible']=='f' && $page_permission) {
	$result = new ZKError(
				"NOT_ACCESSIBLE_INVOICE",
				"NOT_ACCESSIBLE_INVOICE",
				"Invoice No. <b>$_code</b> is not accessible. Please contact the manager to see the detail.");
	$M->goErrorPage($result,  HTTP_DIR . "$currentDept/summary/daily_billing_by_group.php?cboFilterDoc=I");
} else if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
}

//take discount percentage from customer group
$sql = "SELECT cug_basic_disc_pct, cug_code from ".ZKP_SQL."_tb_customer_group WHERE cug_code = (SELECT cug_code FROM ".ZKP_SQL."_tb_customer WHERE cus_code = '".$column['bill_cus_to']."')";
isZKError($res =& query($sql)) ? $M->goErrorPage($res, HTTP_DIR . "$currentDept/$moduleDept/input_billing_step_1.php") : false;
$disc = fetchRow($res);
$disc[0] = ($disc[0]=="") ? "0" : $disc[0];

$cusitem_sql = "
SELECT
  a.it_code,			--0
  b.icat_midx,			--1
  b.it_model_no,		--2
  b.it_type,			--3
  b.it_desc,			--4
  b.biit_unit_price,	--5
  b.biit_qty,			--6
  b.biit_qty*b.biit_unit_price AS amount, --7
  b.biit_remark,		--8
  b.biit_sl_idx			--9
FROM
  ".ZKP_SQL."_tb_billing_item AS b
  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE b.bill_code = '$_code'
ORDER BY a.it_code,b.biit_idx";
$cusitem_res	=& query($cusitem_sql);
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
<script src="../../_script/js_sales.php?_dept=<?php echo $column["bill_dept"]?>&_ship_to=<?php echo trim($column["bill_ship_to"])?>&_cug_code=<?php echo empty($disc[1]) ? "":$disc[1] ?>" type="text/javascript"></script>
<script src="../../_script/jQuery.js" type="text/javascript"></script>
<script src="../../_script/jquery.validation.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">


function initDetail() {
	var f = window.document.frmInsert;

	setSelect(window.document.frmInsert._payment_sj_inv_fp_tender, "<?php echo $column['bill_payment_sj_inv_fp_tender'] ?>");

	if(f._billing_used.value) {
		f.btnMoveDept.disabled = true;
	} else if(f._type_invoice.value == 0 && f._cfm_wh.value=='1') {
		f.btnMoveDept.disabled = true;
	} else if(f._type_template.value == '3' && f._type_template.value == '6') {
		f.btnMoveDept.disabled = true;
	} else {
		f.btnMoveDept.disabled = false;
	}

	if(f._type_template.value == '4') {
		window.document.all.btnDelete.disabled = true;
		window.document.all.btnDelete.className = 'input_sky';
	}

	if(f._has_return.value > 0) {
		window.document.all.btnUpdate.disabled = true;
		window.document.all.btnUpdate.className = 'input_sky';
	}

	if(f._type_bill.value == 1) {
		f._do_no.readOnly='readOnly';f._do_no.className = 'fmt'; f._do_date.className = 'reqd';
	} else {
		f._do_no.readOnly='readOnly';f._do_no.className = 'fmt'; f._do_date.className = 'fmtd';
		f._do_no.value = ''; f._do_date.value = '';
	}

	f.chkSjCode.disabled = true;
	if(f._code.value.substr(1,13) == f._sj_code.value.substr(1,13)) {
		f.chkSjCode.checked = false;
		f._sj_code.className = 'fmt'; f._sj_date.className = 'fmtd';
		f._sj_code.readOnly = 'readOnly'; f._sj_date.readOnly = 'readOnly';
	} else {
		f.chkSjCode.checked = true;
		f._sj_code.className = 'req'; f._sj_date.className = 'reqd';
		f._sj_code.readOnly = false; f._sj_date.readOnly = false;
	}

	if(f._vat_val.value <= 0) {
		dsb_pajak = true; cls_pajak = 'fmt';
	} else {
		dsb_pajak = false; cls_pajak = 'req';
	}
	f._pajak_to.disabled = dsb_pajak;
	f._pajak_name.disabled =  dsb_pajak;
	f._pajak_address.disabled =  dsb_pajak;
	f._pajak_to.className = cls_pajak;
	f._pajak_name.className = cls_pajak;
	f._pajak_address.className = cls_pajak;

	if(f._delivery_freight_charge.value.length == 0 || f._delivery_freight_charge.value <= 0) {
		var dsb_freight = true;
		var cls_freight = 'fmtn';
	} else {
		var dsb_freight = false;
		var cls_freight = 'reqn';
	}
	f._delivery_freight_charge.disabled = dsb_freight;
	f._delivery_freight_charge.className = cls_freight;

	if(f._payment_sj_inv_fp_tender.value == 'Tukar Faktur') {
		f._tukar_faktur_date.disabled = false;
		f._tukar_faktur_date.className = 'reqd';

		<?php if($column['bill_cfm_tukar_faktur'] != '') { ?>
		f._tukar_faktur_date.readOnly = true;
		<?php } ?>
	} else {
		f._tukar_faktur_date.disabled = true;
		f._tukar_faktur_date.className = 'fmtd';
	}
<?php
if ($column['bill_payment_chk'] & 16) {
	echo "\tf._payment_cash_by.disabled = false;\n\n";
} else {
	echo "\tf._payment_cash_by.disabled = true;\n\n";
}

if ($column['bill_payment_chk'] & 32) {
	echo "\tf._payment_check_by.disabled = false;\n";
} else {
	echo "\tf._payment_check_by.disabled = true;\n\n";
}

$j = array("IDC"=>3, "MED"=>2);
if ($column['bill_payment_chk'] & 32 || $column['bill_payment_chk'] & 64 || $column['bill_payment_chk'] & 128) {
	echo "\tf._payment_transfer_by.disabled = false;\n";
	for($i=0; $i<$j[ZKP_SQL]; $i++) {
		echo "\tf._bank[$i].disabled = false;\n";
	}
} else {
	echo "\tf._payment_transfer_by.disabled = true;\n";
	for($i=0; $i<$j[ZKP_SQL]; $i++) {
		echo "\tf._bank[$i].disabled = true;\n";
	}
}

if($column['bill_cfm_delivery_by'] != '') {
	echo "\tf._delivery_freight_charge.readOnly = true;\n\n";
}
?>

if (f._is_vat.value == 'y') {
	for(var i=0; i<3; i++) { f._bank[i].disabled = false; }
	f._bank[1].checked = true;
	bankDesc('DANAMON');				
	f._bank[0].disabled = true;
} else if (f._is_vat.value == 'n') {
	for(var i=0; i<3; i++) { f._bank[i].disabled = false; }
	f._bank[0].checked = true;
	bankDesc('BCA1');
	f._bank[1].disabled = true;
}
f._bank[2].disabled = true;

}

function initPage() {

	if(window.document.frmInsert._type_bill.value == '3') {
		checkUpdatedSalesAmount();	// update amount in sales list
		checkSalesIdx(true, '', 0);	// print cus list according to current sales log
	}
	if(window.document.frmInsert._is_bill_lock.value == 't') {
		window.document.frmInsert._inv_date.readOnly = 'readonly';
		window.document.all.btnDelete.disabled = 'true';
	}
	defaultPaymentConfirm();
	initDetail();
	updateAmount();
	initOption();
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
<strong style="font-size:18px;font-weight:bold">
[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] DETAIL BILLING<br />
</strong>
<small class="comment">* <?php echo $title[$column['template']] ?></small><hr><br />
<form name="frmInsert" method="post">
<input type="hidden" name="p_mode">
<input type="hidden" name="_code" value="<?php echo $column['bill_code'] ?>">
<input type="hidden" name="_type_bill" value="<?php echo $column["bill_type_billing"] ?>">
<input type="hidden" name="_type_invoice" value="<?php echo $column['bill_type_invoice']?>">
<input type="hidden" name="_type_template" value="<?php echo $column['template'] ?>">
<input type="hidden" name="_book_idx" value="<?php echo $column['book_idx']?>">
<input type="hidden" name="_dept" value="<?php echo $column["bill_dept"] ?>">
<input type="hidden" name="_is_vat" value="<?php echo ($column["bill_vat"]>0) ? 'y':'n'?>">
<input type="hidden" name="_vat_val" value="<?php echo $column["bill_vat"] ?>">
<input type="hidden" name="_is_tax" value="<?php echo $column["bill_type_pajak"] ?>">
<input type="hidden" name="_cug_code" value="<?php echo empty($disc[1]) ? "0":$disc[1] ?>">
<input type="hidden" name="_revision_time" value="<?php echo $column['bill_revesion_time'] ?>">
<input type="hidden" name="_billing_used" value="<?php echo ($column['billing_used']=='t') ? true : false ?>">
<input type="hidden" name="_cfm_wh" value="<?php echo ($column['bill_cfm_wh_delivery_by_account']!='') ? true : false ?>">
<?php 
require_once APP_DIR . "_include/billing/tpl_detail_billing_top.php"; 
require_once APP_DIR . "_include/billing/tpl_detail_item_".$column['template'].".php"; 
require_once APP_DIR . "_include/billing/tpl_detail_billing_bottom.php"; 
?>
<input type="hidden" name="_ordered_by" value="<?php echo $column['bill_ordered_by'] ?>">
<input type="hidden" name="web_url" value="<?php echo ZKP_SQL ?>">
<input type="hidden" name="_is_bill_lock" value="<?php echo $column['is_bill_lock'] ?>">
<input type="hidden" name="_has_return" value="<?php echo $column['has_return'] ?>">
</form>
<!--START Button-->
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td>
			<button name='btnDelete' class='input_red' style='width:120px;'><img src="../../_images/icon/trash.gif" width="15px" align="middle" alt="Delete billing"> &nbsp; Delete billing</button>
		</td>
		<td align="right">
			Rev No:
			<select name="_revision_time" id="_revision_time">
			<?php
				for($counter = $column['bill_revesion_time']; $counter >= 0; $counter--) {
					echo "\t\t\t<option value=\"$counter\">$counter</option>\n";
				}
			?>
			</select>&nbsp;
			<button name='btnPrint' class='input_btn' style='width:100px;'><img src="../../_images/icon/print.gif" width="18px" align="middle" alt="Print pdf"> &nbsp; Print PDF</button>&nbsp;
			<button name='btnUpdate' class='input_btn' style='width:80px;'><img src="../../_images/icon/update.gif" width="20px" align="middle" alt="Update order"> &nbsp; Update</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
		</td>
	</tr>
</table><br /><br />
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmInsert;

	window.document.all.btnDelete.onclick = function() {
		if(oForm._billing_used.value) {
			alert("If you want to delete this billing,\nyou have to delete Invoice Return first");
			window.document.all.btnDelete.disabled = true;
			return;
		}
		<?php if(isset($used_deposit) && $used_deposit == 't') {?>
		alert("If you want to delete this billing,\nyou have to delete Payment used deposit first");
		window.document.all.btnDelete.disabled = true;
		return;
		<?php } ?>

		if(confirm("Are you sure to delete billing?")) {
			if(oForm._vat_val.value > 0) {
				window.location.href ='delete_billing.php?_code='+oForm._code.value;
			} else {
				oForm.p_mode.value = 'delete_billing';
				oForm.submit();
			}
		}
	}

	window.document.all.btnPrint.onclick = function() {
		var winforPrint = window.open('','','toolbar=no,width=780,height=580,resizable=yes');
		winforPrint.document.location.href = "../../_include/billing/pdf/download_pdf.php?_dept=<?php echo $currentDept ?>&_code=<?php echo trim($_code)."&_inv_date=".date("Ym", strtotime($column['bill_inv_date']))?>&_rev=" + window._revision_time.value;
	}

	window.document.all.btnUpdate.onclick = function() {
		if(oForm._dept.value == 'A' && oForm._type_bill.value != 1) {
			if(oForm._sales_from.value.length > 0 || oForm._sales_to.value.length > 0) {
				var d1 = parseDate(oForm._sales_from.value, 'prefer_euro_format');
				var d2 = parseDate(oForm._sales_to.value, 'prefer_euro_format');
				if (d1.getTime() > d2.getTime()) {
					alert("Sales to must be later than sales from");
					oForm._sales_from.value = '';
					oForm._sales_to.value = '';
					oForm._sales_from.focus();
					return;
				}
			}
		}
		if(oForm._type_bill.value == '1') {
			if (window.itemWHPosition.rows.length <= 0 || window.itemCusPosition.rows.length <= 0) {
				alert("You need to choose at least 1 item");
				return;
			}
		}
		if(oForm._ship_to_responsible_by.value == 0) {
			alert("Responsibly by must be entered");
			return;
		}
		if(confirm("Are you sure to update?")) {
			if(verify(oForm)){
				oForm.p_mode.value = 'update_billing';
				oForm.submit();
			}
		}
	}

	window.document.all.btnList.onclick = function() {
		window.location.href = '<?php echo HTTP_DIR . $currentDept . '/summary/daily_billing_by_customer.php?_cus_code='. $column['bill_ship_to'] ?>';
	}
</script>
<?php
if($column["bill_vat"]>0) {
	require_once APP_DIR . "_include/billing/tpl_detail_faktur_pajak.php";
	if($column['bill_type_billing'] != 1) {
		require_once APP_DIR . "_include/billing/tpl_detail_billing_confirm.php";
	}
}
if($column['billing_used'] == 't') require_once APP_DIR . "_include/billing/tpl_detail_return.php";
if($column['template']!=1/* && $column['bill_vat']>0*/) {
	require_once APP_DIR . "_include/billing/tpl_detail_delivery.php";
	if($column['bill_tukar_faktur_date'] != '') require_once APP_DIR . "_include/billing/tpl_detail_tukar_faktur.php"; 
}
require_once APP_DIR . "_include/billing/tpl_detail_payment.php"; 
require_once APP_DIR . "_include/billing/tpl_detail_attachment.php";
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