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
$left_loc = 'daily_billing_by_apotik.php';
if (!isset($_GET['_code']) || $_GET['_code'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else {
	$_code = urldecode($_GET['_code']);
}
$title	= array(1=>"Revise : Issue Invoice &amp; unconfirmed booking Item","Issue invoice only","Issue invoice &amp; linked item from sales report", "Detail : Issue Invoice &amp; confirmed booking Item");

//PROCESS FORM
require_once APP_DIR . "_include/billing/tpl_process_form.php";

//========================================================================================= DEFAULT PROCESS
if($S->getValue("ma_authority") & 1)	{ $page_permission = false;}
else 									{ $page_permission = true;}

//billing
$sql = "
SELECT
  *, ".ZKP_SQL."_isBillingUsed(bill_code) AS billing_used,
  (SELECT book_idx FROM ".ZKP_SQL."_tb_booking WHERE book_doc_ref='$_code' AND book_doc_type=1) AS book_idx
FROM ".ZKP_SQL."_tb_billing WHERE bill_code = '$_code'";
$result =& query($sql);
$column =& fetchRowAssoc($result);

if($column['bill_accessible'] == 't') {
	if($page_permission) {
		$result = new ZKError(
					"NOT_ACCESSIBLE_INVOICE",
					"NOT_ACCESSIBLE_INVOICE",
					"Invoice No. <b>$_code</b> is not accessible. Please contact the manager to see the detail.");
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/_summary/daily_billing_by_group.php?cboFilterDoc=I");
	}
} 

if(numQueryRows($result) <= 0) {
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

//Variable template
if($column['bill_type_billing'] == '1' && $column['bill_cfm_wh_delivery_by_account'] == '') {
	$template = '1'; 
} else if($column['bill_type_billing'] == '2') {
	$template = '2'; 
} else if($column['bill_type_billing'] == '3') {
	$template = '3'; 
} else if($column['bill_cfm_wh_delivery_by_account'] != '') {
	$template = '4'; 
}
?>
<html>
<head>
<title>PT. INDOCORE PERKASA [APOTIK MANAGEMENT SYSTEM]</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script src="../../_include/billing/input_billing.js" type="text/javascript"></script>
<script src="../../_script/js_sales.php?_cug_code=<?php echo $disc[1] ?>" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
function initDetail() {
	var f = window.document.frmInsert;

	setSelect(window.document.frmInsert._payment_sj_inv_fp_tender, "<?php echo $column['bill_payment_sj_inv_fp_tender'] ?>");

	if(f._billing_used.value) {
		f.btnMoveDept.disabled = true;
	} else if(f._type_invoice.value == 0 && f._cfm_wh.value=='1') {
		f.btnMoveDept.disabled = true;
	} else if(f._type_template.value == '3') {
		f.btnMoveDept.disabled = true;
	}else {
		f.btnMoveDept.disabled = false;
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

if ($column['bill_payment_chk'] & 32 || $column['bill_payment_chk'] & 64 || $column['bill_payment_chk'] & 128) {
	echo "\tf._payment_transfer_by.disabled = false;\n";
	for($i=0; $i<6; $i++) {
		echo "\tf._bank[$i].disabled = false;\n";
	}
} else {
	echo "\tf._payment_transfer_by.disabled = true;\n";
	for($i=0; $i<6; $i++) {
		echo "\tf._bank[$i].disabled = true;\n";
	}
}

if($column['bill_cfm_delivery_by'] != '') {
	echo "\tf._delivery_freight_charge.readOnly = true;\n\n";
}
?>
}

function initPage() {

	if(window.document.frmInsert._type_bill.value == '3') {
		checkUpdatedSalesAmount();	// update amount in sales list
		checkSalesIdx(true, '', 0);	// print cus list according to current sales log
	}
	initDetail();
	updateAmount();
	window.document.frmInsert.btnMoveDept.disabled = true;

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
<small class="comment">* <?php echo $title[$template] ?></small>
<hr><br />
<form name="frmInsert" method="post">
<input type="hidden" name="p_mode">
<input type="hidden" name="_code" value="<?php echo $column['bill_code'] ?>">
<input type="hidden" name="_type_bill" value="<?php echo $column["bill_type_billing"] ?>">
<input type="hidden" name="_type_invoice" value="<?php echo $column['bill_type_invoice']?>">
<input type="hidden" name="_type_template" value="<?php echo $template ?>">
<input type="hidden" name="_book_idx" value="<?php echo $column['book_idx']?>">
<input type="hidden" name="_dept" value="<?php echo $column["bill_dept"] ?>">
<input type="hidden" name="_is_vat" value="<?php echo ($column["bill_vat"]>0) ? 'y':'n'?>">
<input type="hidden" name="_vat_val" value="<?php echo $column["bill_vat"] ?>">
<input type="hidden" name="_is_tax" value="<?php echo $column["bill_type_pajak"] ?>">
<input type="hidden" name="_cug_code" value="">
<input type="hidden" name="_revision_time" value="<?php echo $column['bill_revesion_time'] ?>">
<input type="hidden" name="_billing_used" value="<?php echo ($column['billing_used']=='t') ? true : false ?>">
<input type="hidden" name="_cfm_wh" value="<?php echo ($column['bill_cfm_wh_delivery_by_account']!='') ? true : false ?>">
<?php 
require_once APP_DIR . "_include/billing/tpl_detail_billing_top.php"; 
require_once APP_DIR . "_include/billing/tpl_detail_item_".$template.".php"; 
require_once APP_DIR . "_include/billing/tpl_detail_billing_bottom.php"; 
?>
</form>
<div align="right">
	<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
</div><br /><br />
<script language="javascript" type="text/javascript">
	window.document.all.btnList.onclick = function() {
		window.location.href = '<?php echo HTTP_DIR . $currentDept . '/_summary/daily_billing_by_customer.php?_cus_code='. $column['bill_ship_to'] ?>';
	}
</script>
<?php
if($column['billing_used'] == 't') require_once APP_DIR . "_include/billing/tpl_detail_return.php";
require_once APP_DIR . "_include/billing/tpl_detail_payment.php"; 
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