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

//PROCESS FORM
require_once APP_DIR . "_include/billing/tpl_process_form.php";

//=========================================================================================== DEFAULT PROCESS
if($S->getValue("ma_authority") & 2)	{ $page_permission = false;}
else 									{ $page_permission = true;}

if ($page_permission) {
	$result = new ZKError(
		"NOT_ENOUGH_AUTHORITY",
		"NOT_ENOUGH_AUTHORITY",
		"You don't have authority to do this action. Please contact the Administrator.");
	$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_billing.php?_code=".$_code);
}

$sql = "
SELECT 
	*, ".ZKP_SQL."_isBillingUsed(bill_code) AS billing_used, 
	to_char(bill_po_date, 'dd-Mon-YYYY') AS bill_po_date,
	to_char(bill_payment_giro_issue, 'dd-Mon-YY') AS giro_issue,
	to_char(bill_payment_giro_due, 'dd-Mon-YY') AS giro_due
FROM ".ZKP_SQL."_tb_billing WHERE bill_code = '$_code'";
$result	=& query($sql);
$column =& fetchRowAssoc($result);

if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . "$currentDept/_summary/daily_billing_by_group.php");
} else if($column['bill_accessible'] == 't') {
	$result = new ZKError(
				"NOT_ACCESSIBLE_INVOICE",
				"NOT_ACCESSIBLE_INVOICE",
				"Invoice No. <b>$_code</b> is not accessible. Please contact the manager to see the detail.");
	$M->goErrorPage($result,  HTTP_DIR . "$currentDept/summary/daily_billing_by_group.php?cboFilterDoc=I");
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
<script language="javascript" type="text/javascript">
function initPage() {
<?php
if($column["bill_vat"]>0) {
	echo "setSelect(window.document.frmUpdate._new_dept, '{$column['bill_dept']}')";
}
?>
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
<hr><br />
<table width="100%" class="table_box">
	<tr>
		<td colspan="3"><strong class="info">BILLING INFORMATION</strong></td>
		<td colspan="2" align="right">
			<I>Last updated by : <?php echo ucfirst($column['bill_lastupdated_by_account']).date(', j-M-Y g:i:s', strtotime($column['bill_lastupdated_timestamp']))?></I>
		</td>
	</tr>
	<tr>
		<th width="15%">INVOICE NO</th>
		<td width="30%"><b class="info"><?php echo $_code ?></b></td>
		<th width="15%">INVOICE DATE</th>
		<td colspan="2"><?php echo date("j-M-Y", strtotime($column['bill_inv_date']))?></td>
	</tr>
	<tr>
		<th>FAKTUR PAJAK NO.</th>
		<td><?php echo $column['bill_vat_inv_no'] ?></td>
		<th>RECEIVED BY</th>
		<td><?php echo $column['bill_received_by']?></td>
	</tr>
	<tr>
		<th>DO NO</th>
		<td><?php echo $column['bill_do_no']?></td>
		<th>DO DATE</th>
		<td><?php echo ($column['bill_do_date'] == '') ? date("j-M-Y", strtotime($column['bill_inv_date'])) : date("j-M-Y", strtotime($column['bill_do_date']))?></td>
	</tr>
	<tr>
		<th>PO NO</th>
		<td><?php echo $column['bill_po_no']?></td>
		<th>PO DATE</th>
		<td><?php echo ($column['bill_po_date'] != '') ? date("j-M-Y", strtotime($column['bill_po_date'])) : ''?></td>
	</tr>
	<tr>
		<th>SJ CODE</th>
		<td><?php echo $column['bill_sj_code']?></td>
		<th width="15%">SJ DATE</th>
		<td colspan="2"><?php echo date("j-M-Y", strtotime($column['bill_sj_date']))?></td>
	</tr>
	<tr>
		<th>VAT</th>
		<td>
			<input type="radio" name="_btnVat" value="y" disabled <?php echo ($column['bill_vat'] > 0) ? 'checked' : '' ?>><input name="_vat" type="text" class="fmtn" size="2" maxlength="4" value="<?php echo $column['bill_vat'] ?>" readonly>%
			<input type="radio" name="_btnVat" value="n" disabled <?php echo ($column['bill_vat'] > 0) ? '' : 'checked' ?>>NON VAT
		</td>
		<th>TYPE OF PAJAK</th>
		<td>
			<input type="radio" name="_type_of_pajak" value="IO" <?php echo ($column['bill_type_pajak'] == 'IO') ? "checked" : '' ?> disabled>IO &nbsp;
			<input type="radio" name="_type_of_pajak" value="IP" <?php echo ($column['bill_type_pajak'] == 'IP') ? "checked" : '' ?> disabled>IP
		</td>
	</tr>
</table>
<table width="100%" class="table_box" cellspacing="0">
	<tr height="30px">
		<th width="15%">&nbsp;</th>
		<th width="8%">CODE</th>
		<th width="18%">NAME</th>
		<th>ADDRESS</th>
	</tr>
	<tr>
		<th>CUSTOMER</th>
		<td><?php echo $column['bill_cus_to']?></td>
		<td><?php echo substr($column['bill_cus_to_name'],0,15)?></td>
		<td><?php echo substr($column['bill_cus_to_address'],0,80)?></td>
	</tr>
	<tr>
		<th>PAJAK TO</th>
		<td><?php echo $column['bill_pajak_to']?></td>
		<td><?php echo substr($column['bill_pajak_to_name'],0,15)?></td>
		<td><?php echo substr($column['bill_pajak_to_address'],0,80)?></td>
	</tr>
	<tr>
		<th>SHIP TO</th>
		<td><?php echo $column['bill_ship_to']?></td>
		<td colspan="2"><?php echo $column['bill_ship_to_name']?></td>
	</tr>
</table><br />
<strong class="info">MOVING INFORMATION</strong>
<form name="frmUpdate" method="post">
<input type="hidden" name="p_mode" value="move_billing">
<input type="hidden" name="_old_code" value="<?php echo $column['bill_code']?>">
<input type='hidden' name="_inv_date" value="<?php echo $column['bill_inv_date']?>">
<input type='hidden' name="_old_type_invoice" value="<?php echo $column['bill_type_invoice']?>">
<input type='hidden' name="_old_dept" value="<?php echo $column['bill_dept']?>">
<input type='hidden' name="_revision_time" value="<?php echo $column['bill_revesion_time']?>">
<table width="100%" class="table_box">
	<tr>
		<th width="15%">MOVE TO</th>
		<td width="30%">
			<?php if($column["bill_vat"]>0) {  ?>
			<select name="_new_dept">
				<option value="A">APOTIK</option>
				<option value="D">DEALER</option>
				<option value="H">HOSPITAL</option>
				<option value="M">MARKETING</option>
				<option value="P">PHARMACEUTICAL</option>
				<option value="T">TENDER</option>
				<option value="S">SALES SUPPORT</option>
			</select>
			<?php } else { ?>
			<input type="hidden" name="_new_dept" value="<?php echo $column['bill_dept']?>"><b>== CANNOT MOVE DEPARTMENT ==</b>
			<?php } ?>
		</td>
		<th width="15%">TYPE INVOICE</th>
		<td>
			<input type="radio" name="_new_type_invoice" value="0" id="0" <?php echo ($column['bill_type_invoice']==0)?'checked':'' ?>><label for="0"> Issue invoice &amp; booking item &nbsp;</label>
			<input type="radio" name="_new_type_invoice" value="1" id="1" <?php echo ($column['bill_type_invoice']==1)?'checked':'' ?>><label for="1"> Issue invoice only</label>
		</td>
	</tr>
</table><br />
</form>
<table width="100%" class="table_layout">
	<tr>
		<td align="right">
			<button name="btnMoveDept" class="input_red" onclick='checkform(window.document.frmInsert)'><img src="../../_images/icon/check.jpg"> &nbsp; Move Billing</button>&nbsp;
			<button name="btnCancel" class="input_sky" onclick='window.location.href="revise_billing.php?_code=<?php echo $_code ?>"'><img src="../../_images/icon/delete_2.gif"> &nbsp; Cancel Move</button>
		</td>
	</tr>
</table>
<script language="javascript" type="text/javascript">
	window.document.all.btnMoveDept.onclick = function() {
		if(confirm("Are you sure to move billing?\nIf you move billing, it will delete the related data")) {
			window.document.frmUpdate.submit();
		}
	}
</script>
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