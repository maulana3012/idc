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
$left_loc = 'input_order_step_1.php';
if (!isset($_GET['_code']) || $_GET['_code'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else {
	$_code = urldecode($_GET['_code']);
}

//============================================================================================== move bill_code
if(ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'move_billing')) {

	$_code				= $_POST['_code'];
	$_rev				= (int) $_POST['_revision_time'];
	$_po_date			= date("Ym", strtotime($_POST['_po_date']));
	$_old_type_invoice	= $_POST['_old_type_invoice'];
	$_new_type_invoice	= $_POST['_new_type_invoice'];
	$_updated_by		= $S->getValue("ma_account");

	//move ord_type_invoice
	$result = executeSP(
		ZKP_SQL."_moveOrderType",
		"$\${$_code}$\$",
		$_old_type_invoice,
		$_new_type_invoice,
		"$\${$_po_date}$\$",
		"$\${$_updated_by}$\$"
	);

	if(isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/change_type.php?_code=$_code");
	} else {
		for ($i = $_rev ; $i >=0 ; $i--) {
			@unlink(PDF_STORAGE . "billing/$currentDept/{$_inv_date}/{$_old_code}_rev_{$i}.pdf");
		}
	}
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_order.php?_code=".$_code);
}

//=========================================================================================== DEFAULT PROCESS
if($S->getValue("ma_authority") & 2)	{ $page_permission = false;}
else 									{ $page_permission = true;}

if ($page_permission) {
	$result = new ZKError(
		"NOT_ENOUGH_AUTHORITY",
		"NOT_ENOUGH_AUTHORITY",
		"You don't have authority to do this action. Please contact the Administrator.");
	$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_order.php?_code=".$_code);
}

$sql = "SELECT * FROM ".ZKP_SQL."_tb_order WHERE ord_code = '$_code'";
if(isZKError($result =& query($sql)))
	$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");

$column =& fetchRowAssoc($result);

if(numQueryRows($result) <= 0)
	goPage(HTTP_DIR . $currentDept . "/delivery/index.php");
?>
<html>
<head>
<title>PT. INDOCORE PERKASA [APOTIK MANAGEMENT SYSTEM]</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
</head>
<body topmargin="0" leftmargin="0">
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
<table class="table_layout" width="100%">
	<tr>
		<td height="15" style="font-size:15px;font-weight:bold">[<font color="blue"><?php echo strtoupper($currentDept) ?></font>] DETAIL ORDER</td>
	</tr>
	<tr>
		<td><i class="comment"></i></td>
	</tr>
</table><br />
<table width="100%" class="table_box">
	<tr>
		<td colspan="3"><strong class="info">ORDER INFORMATION</strong></td>
		<td colspan="3" align="right">
			<I>Last updated by : <?php echo $column['ord_lastupdated_by_account'].date(', j-M-Y g:i:s', strtotime($column['ord_lastupdated_timestamp']))." Rev:".$column['ord_revision_time']?></I>
		</td>
	</tr>
	<tr>
		<th width="12%">CODE</th>
		<td width="25%"><strong><?php echo $column['ord_code']?></strong></td>
		<th width="12%">RECEIVED BY</th>
		<td><?php echo $column['ord_received_by']?></td>
		<th width="12%">CONFIRM BY</th>
		<td><?php echo $column['ord_confirm_by']?></td>
	</tr>
	<tr>
		<th>PO DATE</th>
		<td><?php echo date("j-M-Y", strtotime($column['ord_po_date']))?></td>
		<th>PO NO</th>
		<td><?php echo $column['ord_po_no']?></td>
		<th>VAT</th>
		<td><?php echo $column['ord_vat']?>%</td>
	</tr>
</table>
<table width="100%" class="table_nn" cellspacing="0">
	<tr height="30px">
		<th width="12%">&nbsp;</th>
		<th width="8%">CODE</th>
		<th width="12%">ATTN</th>
		<th width="70%">ADDRESS</th>
	</tr>
	<tr>
		<th>CUSTOMER</th>
		<td><?php echo $column['ord_cus_to']?></td>
		<td><?php echo cut_string($column['ord_cus_to_attn'],15)?></td>
		<td><?php echo cut_string($column['ord_cus_to_address'],105)?></td>
	</tr>
	<tr>
		<th>SHIP TO</th>
		<td><?php echo $column['ord_ship_to']?></td>
		<td><?php echo cut_string($column['ord_ship_to_attn'],15)?></td>
		<td><?php echo cut_string($column['ord_ship_to_address'],105)?></td>
	</tr>
	<tr>
		<th>BILL TO</th>
		<td><?php echo $column['ord_bill_to']?></td>
		<td><?php echo cut_string($column['ord_bill_to_attn'],15)?></td>
		<td><?php echo cut_string($column['ord_bill_to_address'],105)?></td>
	</tr>
</table><br />
<strong class="info">MOVING INFORMATION</strong>
<form name="frmUpdate" method="post">
<input type="hidden" name="p_mode" value="move_billing">
<input type="hidden" name="_code" value="<?php echo $column['ord_code']?>">
<input type="hidden" name="_old_type_invoice" value="<?php echo $column['ord_type_invoice']?>">
<input type="hidden" name="_revision_time" value="<?php echo $column['ord_revision_time']?>">
<input type="hidden" name="_po_date" value="<?php echo $column['ord_po_date']?>">
<table width="100%" class="table_box">
	<tr>
		<th width="12%">TYPE INVOICE</th>
		<td>
			<input type="radio" name="_new_type_invoice" value="0" id="0" <?php echo ($column['ord_type_invoice']==0)?'checked':'' ?>><label for="0"> Issue invoice &amp; booking item &nbsp;</label>
			<input type="radio" name="_new_type_invoice" value="1" id="1" <?php echo ($column['ord_type_invoice']==1)?'checked':'' ?>><label for="1"> Issue invoice only</label>
		</td>
		<td align="right">
			<button name="btnMoveDept" class="input_red"><img src="../../_images/icon/check.jpg"> &nbsp; Move Order</button>&nbsp;
			<button name="btnCancel" class="input_sky"><img src="../../_images/icon/delete_2.gif"> &nbsp; Cancel Move</button>
		</td>
	</tr>
</table>
</form>
<script language="javascript" type="text/javascript">
	var f = window.document.frmUpdate;

	f.btnCancel.onclick = function() {
		window.location.href = '<?php echo HTTP_DIR . "$currentDept/$moduleDept/revise_order.php?_code=$_code" ?>';
	}

	f.btnMoveDept.onclick = function() {
		if(confirm("Are you sure to move order?")) {
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