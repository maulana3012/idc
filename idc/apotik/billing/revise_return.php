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
$title	= array(1=>	"Revise : Issue return invoice &amp; unconfirmed return Item",
					"Issue return invoice only", 
					"DETAIL : Issue return invoice &amp; confirmed return item",
					"DETAIL : Issue return invoice only");

//PROCESS FORM
require_once APP_DIR . "_include/billing/tpl_process_return_form.php";

//========================================================================================= DEFAULT PROCESS
if($S->getValue("ma_authority") & 1)	{ $page_permission = false;}
else                                    { $page_permission = true;}

$tmp[]	  = "turn_code = '$_code'";
$strWhere = implode(" AND ", $tmp);

$sql = 
"SELECT *,
  ".ZKP_SQL."_isDepositUsed(turn_return_condition,turn_code,turn_cus_to,turn_return_date) AS deposit_used,
  ".ZKP_SQL."_isValidShowInvoice('".ZKP_FUNCTION."', '$_code','billing_return') AS bill_accessible,  
  (SELECT std_idx FROM ".ZKP_SQL."_tb_outstanding WHERE std_doc_ref='$_code') AS std_idx,
  (SELECT inc_idx FROM ".ZKP_SQL."_tb_incoming WHERE inc_doc_ref='$_code') AS inc_idx,
  (select to_char(bill_inv_date,'dd-Mon-yyyy') from ".ZKP_SQL."_tb_billing where bill_code=t.turn_bill_code) AS bill_date,
  (select bill_do_no from ".ZKP_SQL."_tb_billing where bill_code=t.turn_bill_code) AS bill_do_no,
  (select to_char(bill_do_date,'dd-Mon-yyyy') from ".ZKP_SQL."_tb_billing where bill_code=t.turn_bill_code) AS bill_do_date,
  (select bill_sj_code from ".ZKP_SQL."_tb_billing where bill_code=t.turn_bill_code) AS bill_sj_code,
  (select to_char(bill_sj_date,'dd-Mon-yyyy') from ".ZKP_SQL."_tb_billing where bill_code=t.turn_bill_code) AS bill_sj_date,
  (select bill_po_no from ".ZKP_SQL."_tb_billing where bill_code=t.turn_bill_code) AS bill_po_no,
  (select to_char(bill_po_date,'dd-Mon-yyyy') from ".ZKP_SQL."_tb_billing where bill_code=t.turn_bill_code) AS bill_po_date,
  CASE
	WHEN turn_paper=0 AND turn_cfm_wh_delivery_by_account!='' THEN 3
	WHEN turn_paper=1 AND turn_cfm_wh_delivery_by_account!='' THEN 4
	WHEN turn_paper=0 THEN 1
	WHEN turn_paper=1 THEN 2
  END AS template,
  CASE
  	WHEN turn_cfm_wh_delivery_by_account != '' THEN true
  	ELSE false
  END AS is_turn_lock
FROM ".ZKP_SQL."_tb_return as t WHERE $strWhere";
$result =& query($sql);
$column =& fetchRowAssoc($result);

if($column['bill_accessible']=='f' && $page_permission) {
	$result = new ZKError(
				"NOT_ACCESSIBLE_INVOICE",
				"NOT_ACCESSIBLE_INVOICE",
				"Invoice No. <b>$_code</b> is not accessible. Please contact the manager to see the detail.");
	$M->goErrorPage($result,  HTTP_DIR . "$currentDept/summary/daily_billing_by_group.php?cboFilterDoc=R");
} else if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
}

if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} 

//take discount percentage from customer group
$sql = "SELECT cug_basic_disc_pct from ".ZKP_SQL."_tb_customer_group WHERE cug_code = (SELECT cug_code FROM ".ZKP_SQL."_tb_customer WHERE cus_code = '".$column['turn_cus_to']."')";
isZKError($res =& query($sql)) ? $M->goErrorPage($res, HTTP_DIR . "$currentDept/$moduleDept/index.php") : false;
$disc = fetchRow($res);

//[CUSTOMER] billing item
$cusitem_sql = "
SELECT
 a.it_code,			--0
 a.it_model_no,		--1
 a.it_desc,			--2
 b.reit_unit_price,	--3
 b.reit_qty,		--4
 b.reit_unit_price * b.reit_qty AS amount,	--5	
 b.reit_remark,		--6
 b.reit_idx,		--7		
 b.icat_midx		--8
FROM ".ZKP_SQL."_tb_return_item AS b JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE turn_code = '$_code'
ORDER BY it_code, reit_idx";
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
<script language="javascript" type="text/javascript">
function enabledText(o, value) {
	var f = window.document.frmInsert;

	if (value == 'cash') {
		if(o.checked == true) {
			f._payment_cash_by.disabled = false;
			f._payment_cash_by.focus();
		} else {
			f._payment_cash_by.disabled = true;
			f._payment_cash_by.value = '';
		}
	}  else if (value == 'check') {
		if(o.checked == true) {
			f._payment_check_by.disabled = false;
			f._payment_check_by.focus();
		} else {
			f._payment_check_by.disabled = true;
			f._payment_check_by.value = '';
		}
		enabledBankOption(o);
	}  else if (value == 'transfer') {
		if(o.checked == true) {
			f._payment_transfer_by.disabled = false;
			f._payment_transfer_by.focus();
		} else {
			f._payment_transfer_by.disabled = true;
			f._payment_transfer_by.value = '';
		}
		enabledBankOption(o);
	}
}

function enabledBankOption(o) {
	var f = window.document.frmInsert;

	if(f.web_url.value == 'IDC') {
		if (o.checked == true) {
			for(var i=0; i<6; i++) {
				f._bank[i].disabled = false;
			}
		} else if (o.checked == false) {
			for(var i=0; i<6; i++) {
				f._bank[i].disabled   = true;
				f._bank[i].checked	  = false;
			}
			f._bank_address.value = '';
		}
	} else if(f.web_url.value == 'IDC') {
		if (o.checked == true) {
			for(var i=0; i<2; i++) {
				f._bank[i].disabled = false;
			}
		} else if (o.checked == false) {
			for(var i=0; i<2; i++) {
				f._bank[i].disabled   = true;
				f._bank[i].checked	  = false;
			}
			f._bank_address.value = '';
		}
	}

}

function bankDesc(o) {
	var f = window.document.frmInsert;

	if (o == "BCA1") {
		f._bank_address.value = "BCA KCU WISMA GKBI\nA/N\t: PT. INDOCORE PERKASA\nA/C\t: 0063094100";
	} else if (o == "BCA2") {
		f._bank_address.value = "BCA KCU KELAPA GADING\nA/N\t: In Ki Kim W/O Lee\nA/C\t: 0650690176";
	} else if (o == "BII1") {
		f._bank_address.value = "BII KELAPA GADING JAKARTA\nA/N\t: In Ki Kim W/O Lee\nA/C\t: 1.016.691.961";
	} else if (o == "BII2") {
		f._bank_address.value = "BII KELAPA GADING JAKARTA\nA/N\t: PT. INDOCORE PERKASA\nA/C\t: 2.016.296.083";
	} else if (o == "BII3") {
		f._bank_address.value = "BII CABANG GRAHA CEMPAKA MAS\nA/C\t: 219751283\nA/N\t: PT. Medisindo Bahana";
	} else if (o == "MANDIRI") {
		f._bank_address.value = "MANDIRI KCP JAKARTA DESIGN CENTER\nA/N\t: In Ki Kim\nA/C\t: 117-00-0219394-4";
	} else if (o == "DANAMON") {
		f._bank_address.value = "DANAMON CAB. KELAPA GADING, JAKARTA\nA/N\t: PT. Indocore Perkasa\nA/C\t: 21772660";
	} else if (o == "DANAMON2") {
		f._bank_address.value = "DANAMON CABANG KELAPA GADING II\nA/C\t: 351438364\nA/N\t: PT. Medisindo Bahana";
	}
}

function initPage() {
<?php
$j = array("IDC"=>6, "MED"=>2);
if ($column['turn_payment_chk'] & 32 || $column['turn_payment_chk'] & 64 || $column['turn_payment_chk'] & 128) {
	echo "\twindow.document.frmInsert._payment_transfer_by.disabled = false;\n";
	for($i=0; $i<$j[ZKP_SQL]; $i++) {
		echo "\twindow.document.frmInsert._bank[".$i."].disabled = false;\n";
//		if($i==5) {echo "\n";}
	}
} else {
	echo "\twindow.document.frmInsert._payment_transfer_by.disabled = true;\n";
	for($i=0; $i<$j[ZKP_SQL]; $i++) {
		echo "\twindow.document.frmInsert._bank[".$i."].disabled = true;\n";
//		if($i==5) {echo "\n";}
	}
}
?>
var f = window.document.frmInsert;

	if (window.document.frmInsert._is_turn_lock.value == 't') {
		window.document.frmInsert._return_date.readOnly = 'readonly';
		window.document.all.btnDelete.disabled  = true;
	}

	setSelect(window.document.frmInsert._payment_sj_inv_fp_tender, "<?php echo $column['turn_payment_sj_inv_fp_tender'] ?>");
	setSelect(window.document.frmInsert._type, "<?php echo $column['turn_type_return'] ?>");
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
[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] REVISE RETURN<br />
</strong>
<small class="comment">* <?php echo $title[$column['template']] ?></small><hr><br />
<form name="frmInsert" method="post">
<input type="hidden" name="p_mode">
<input type='hidden' name="_code" value="<?php echo rtrim($column['turn_code'])?>">
<input type='hidden' name="_bill_code" value="<?php echo $column['turn_bill_code']?>">
<input type='hidden' name="_paper" value="<?php echo $column['turn_paper']?>">
<input type='hidden' name="_std_idx" value="<?php echo $column['std_idx']?>">
<input type='hidden' name="_inc_idx" value="<?php echo $column['inc_idx']?>">
<input type='hidden' name="_return_condition" value="<?php echo $column['turn_return_condition']?>">
<input type='hidden' name="_dept" value="<?php echo $column['turn_dept']?>">
<input type='hidden' name="_revision_time" value="<?php echo $column['turn_revesion_time']?>">
<input type='hidden' name="_old_total_amount" value="<?php echo $column['turn_total_return']?>">
<input type='hidden' name="_cus_to" value="<?php echo $column['turn_cus_to']?>">
<input type='hidden' name="_ship_to" value="<?php echo $column['turn_ship_to']?>">
<input type='hidden' name="_bill_date" value="<?php echo $column['turn_bill_inv_date']?>">
<input type='hidden' name="_bill_vat_inv_no" value="<?php echo $column['turn_bill_vat_inv_no']?>">
<?php 
require_once APP_DIR . "_include/billing/tpl_detail_return_top.php"; 
require_once APP_DIR . "_include/billing/tpl_detail_return_item_".$column['template'].".php"; 
require_once APP_DIR . "_include/billing/tpl_detail_return_bottom.php"; 
?>
<input type='hidden' name="_ordered_by" value="<?php echo $column['turn_ordered_by']?>">
<input type="hidden" name="web_url" value="<?php echo ZKP_SQL ?>">
<input type='hidden' name="_is_turn_lock" value="<?php echo $column['is_turn_lock']?>">
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td>
			<button name='btnDelete' class='input_red' style='width:120px;'><img src="../../_images/icon/trash.gif" width="15px" align="middle" alt="Delete return"> &nbsp; Delete return</button>
		</td>
		<td align="right">
			Rev No:
			<select name="_revision_time" id="_revision_time">
			<?php
				for($counter = $column['turn_revesion_time']; $counter >= 0; $counter--) {
					echo "\t\t\t<option value=\"$counter\">$counter</option>\n";
				}
			?>
			</select>&nbsp;
			<button name='btnPrint' class='input_btn' style='width:100px;'><img src="../../_images/icon/print.gif" width="18px" align="middle" alt="Print pdf"> &nbsp; Print PDF</button>&nbsp;
			<button name='btnUpdate' class='input_btn' style='width:80px;'><img src="../../_images/icon/update.gif" width="20px" align="middle" alt="Update return billing"> &nbsp; Update</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
		</td>
	</tr>
</table><br />
<script language="javascript" type="text/javascript">

	//Define the form that you want to handle
	var oForm = window.document.frmInsert;

	window.document.all.btnDelete.onclick = function() {
		<?php if ($column['deposit_used'] == 't') { ?>
		alert("If you want to delete this return,\nyou have to delete Payment which using this deposit first");
		window.document.all.btnDelete.disabled = true;
		return;
		<?php } ?>

		if(confirm("Are you sure to delete return?")) {
			if(verify(oForm)){
				oForm.p_mode.value = 'delete';
				oForm.submit();
			}
		}
	}

	window.document.all.btnPrint.onclick = function() {
		var winforPrint = window.open('','','toolbar=no,width=780,height=580,resizable=yes');
		winforPrint.document.location.href = "../../_include/billing/pdf/download_pdf.php?_dept=<?php echo $currentDept ?>&_code=<?php echo trim($_code)."&_inv_date=".date("Ym", strtotime($column['turn_return_date']))?>&_rev=" + window._revision_time.value;
	}

	window.document.all.btnUpdate.onclick = function() {
		<?php if($column['template']==1) { ?>
		if (window.itemWHPosition.rows.length <= 0) {
			alert("You need to choose at least 1 item");
			return;
		}
		<?php } ?>
		if (window.itemCusPosition.rows.length <= 0) {
			alert("You need to choose at least 1 item");
			return;
		}
		if(oForm._ship_to_responsible_by.value == 0) {
			alert("Responsibly by must be entered");
			return;
		}
		if(confirm("Are you sure to update?")) {
			if(verify(oForm)){
				oForm.p_mode.value = 'update';
				oForm.submit();
			}
		}
	}

	window.document.all.btnList.onclick = function() {
		window.location.href = '<?php echo HTTP_DIR . "$currentDept/$moduleDept/daily_billing_by_customer.php?_cus_code=". $column['turn_ship_to'] ?>';
	}
</script>
<!--END Button-->
<br /><br />
<?php
if($column['turn_paper'] == 1 && $column["turn_vat"] > 0 && $column["turn_return_date"] > '2019-08-31') {
	require_once APP_DIR . "_include/billing/tpl_detail_return_confirm.php";
}
require_once APP_DIR . "_include/billing/tpl_detail_attachment_return.php";
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