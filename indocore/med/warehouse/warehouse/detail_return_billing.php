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
ckperm(ZKP_SELECT, HTTP_DIR . "warehouse/warehouse/index.php");

//Global
$left_loc 	  = "daily_summary_by_period.php";
if (!isset($_GET['_code']) || $_GET['_code'] == '') {
	goPage(HTTP_DIR . 'warehouse/warehouse/index.php');
} else {
	$_code = urldecode($_GET['_code']);
}

//CONFIRM WAREHOUSE
if (ckperm(ZKP_INSERT, HTTP_DIR . "warehouse/warehouse/index.php", 'cfm_warehouse')) {

	$_code		= $_POST['_code'];
	$_date 		= $_POST['_date'];
	$_cfm_by	= $S->getValue("ma_account");

	$result = executeSP(
		ZKP_SQL."_cfmWarehouse",
		"$\$return_billing$\$",
		"$\${$_code}$\$",
		"$\${$_date}$\$",
		"$\${$_cfm_by}$\$"
	);

	if (isZKError($result))
		$M->goErrorPage($result, HTTP_DIR . "warehouse/warehouse/index.php");

	goPage(HTTP_DIR . 'warehouse/warehouse/detail_return_billing.php?_code='.urlencode($_code));
}

//UNCONFIRM WAREHOUSE
if (ckperm(ZKP_INSERT, HTTP_DIR . "warehouse/warehouse/index.php", 'uncfm_warehouse')) {

	$result =& query("UPDATE ".ZKP_SQL."_tb_return SET turn_cfm_wh_by_account='',turn_cfm_wh_timestamp=null,turn_cfm_wh_date=null where turn_code='$_code'");

	if (isZKError($result))
		$M->goErrorPage($result, HTTP_DIR . "warehouse/warehouse/index.php");

	goPage(HTTP_DIR . 'warehouse/warehouse/detail_return_billing.php?_code='.urlencode($_code));
}

//DEFAULT PROCESS
$sql = "SELECT *, isDepositUsed(turn_return_condition,turn_code,turn_cus_to,turn_return_date) AS deposit_used FROM ".ZKP_SQL."_tb_return WHERE turn_code = '$_code'";
if(isZKError($result =& query($sql)))
	$M->goErrorPage($result, HTTP_DIR . "warehouse/warehouse/index.php");

$column =& fetchRowAssoc($result);
if(numQueryRows($result) <= 0) {
	$result = new ZKError(
		"CODE_NOT_EXIST",
		"CODE_NOT_EXIST",
		"Return billing no <b>$_code</b> doesn't exist in system. Please check again.");

	$M->goErrorPage($result,  HTTP_DIR . "warehouse/warehouse/daily_summary_by_period.php");
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
<script language="javascript" type="text/javascript">
//Calculate (It different with revise_order.php & input_order.php)
function reCalculationTotal(){

	var f			= window.document.all;
	var count		= window.rowPosition.rows.length;
	var sumOfQty	= 0;

	for (var i = 0; i< count; i++) {
		var oRow = window.rowPosition.rows(i);
		sumOfQty = sumOfQty + parseFloat(removecomma(oRow.cells(3).innerText));
	}

	f.totalQty.value	  = addcomma(sumOfQty);
}
</script>
</head>
<body topmargin="0" leftmargin="0" onLoad="reCalculationTotal()">
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
<h4>RETURN BILLING DETAIL</h4>
	<table width="100%" class="table_box">
		<tr>
			<td colspan="3"><strong>RETURN INFORMATION</strong></td>
			<td colspan="3" align="right">
				<I>Last updated by : <?php echo $column['turn_lastupdated_by_account'].date(', j-M-Y g:i:s', strtotime($column['turn_lastupdated_timestamp']))?></I>
			</td>
		</tr>
		<tr>
			<th width="15%">RETURN CODE</th>
			<td colspan="2"><?php echo $_code ?></td>
			<th width="15%">RECEIVED BY</th>
			<td><?php echo $column['turn_received_by']?></td>
		</tr>
		<tr>
			<th>VAT</th>
			<td colspan="2">
				<input type="radio" name="_btnVat" value="y" disabled <?php echo ($column['turn_vat'] > 0) ? 'checked' : '' ?> disabled><input name="_vat" type="text" class="fmtn" size="2" maxlength="4" value="<?php echo $column['turn_vat'] ?>" readonly>%
				<input type="radio" name="_btnVat" value="n" disabled <?php echo ($column['turn_vat'] > 0) ? '' : 'checked' ?> disabled>NON VAT
			</td>
			<th width="15%">RETURN DATE</th>
			<td><?php echo date("j-M-Y", strtotime($column['turn_return_date']))?></td>
		</tr>
		<tr>
			<th>PO NO</th>
			<td colspan="2"><?php echo $column['turn_po_no']?></td>
			<th>PO DATE</th>
			<td><?php echo ($column['turn_po_date'] != '') ? date("j-M-Y", strtotime($column['turn_po_date'])) : ''?></td>
		</tr>
		<tr>
			<th>SJ CODE</th>
			<td colspan="2"><?php echo $column['turn_sj_code'] ?></td>
			<th width="15%">SJ DATE</th>
			<td colspan="2"><?php echo($column['turn_sj_date'] == '') ? '' : date("j-M-Y", strtotime($column['turn_sj_date']))?></td>
		</tr>
		<tr>
			<th rowspan="3">CUSTOMER</th>
			<th width="12%">CODE</th>
			<td width="28%"><?php echo $column['turn_cus_to'] ?></td>
			<th>NAME</th>
			<td><?php echo $column['turn_cus_to_name'] ?></td>
		</tr>
		<tr>
			<th>ATTN</th>
			<td><?php echo $column['turn_cus_to_attn'] ?></td>
			<th>NPWP</th>
			<td><?php echo $column['turn_npwp'] ?></td>
		</tr>
		<tr>
			<th width="12%">ADDRESS</th>
			<td colspan="3"><?php echo $column['turn_cus_to_address'] ?></td>
		</tr>
		<tr>
			<th width="15%" rowspan="3">INVOICE REF.</th>
			<th>CODE</th>
			<td><?php echo $column['turn_bill_code'] ?></td>
			<th>VAT INV NO</th>
			<td>
				<?php echo $column['turn_bill_vat_inv_no'] ?> &nbsp; &nbsp; 
				DATE : <?php echo ($column['turn_bill_inv_date'] == '') ? '' : date('j-M-Y', strtotime($column['turn_bill_inv_date'])) ?>
			</td>
		</tr>
		<tr>
			<th>PAID BILLING</th>
			<td>
				<input type="radio" name='_is_bill_paid' value='1' <?php echo ($column['turn_is_bill_paid'] == 1) ? 'checked' : '' ?> disabled>YES &nbsp; &nbsp;
				<input type="radio" name='_is_bill_paid' value='0' <?php echo ($column['turn_is_bill_paid'] == 0) ? 'checked' : '' ?> disabled>NO
			</td>
			<th>MONEY BACK</th>
			<td>
				<input type="radio" name='_is_money_back' value='1' <?php echo ($column['turn_is_money_back'] == 1) ? 'checked' : '' ?> disabled>YES &nbsp; &nbsp;
				<input type="radio" name='_is_money_back' value='0' <?php echo ($column['turn_is_money_back'] == 0) ? 'checked' : '' ?> disabled>NO
			</td>
		</tr>
		<tr>
			<th>TYPE RETURN</th>
			<td>
				<select name="_type_return" class="req" disabled>
					<option value="RO">Return Order</option>
					<option value="RR">Return Replace</option>
				</select>
				Condition : <?php echo $column['turn_return_condition'] ?>
			</td>
			<th>SAME ITEM</th>
			<td>
				<input type="radio" name='_is_same_item' value='1' <?php echo ($column['turn_is_same_item'] == 1) ? 'checked' : '' ?> disabled>YES &nbsp; &nbsp;
				<input type="radio" name='_is_same_item' value='0' <?php echo ($column['turn_is_same_item'] == 0) ? 'checked' : '' ?> disabled>NO
			</td>
		</tr>
	</table><br>
	<strong>ITEM LIST</strong>
	<table width="85%" class="table_box">
		<thead>
			<tr>
				<th width="5%">CODE</th>
				<th width="13%">ITEM NO</th>
				<th>DESCRIPTION</th>
				<th width="10%">QTY</th>
				<th width="20%">REMARK</th>
			</tr>
		</thead>
		<tbody id="rowPosition">
<?php
$sql = "
SELECT
 it_code,	
 it_model_no,
 it_desc,
 reit_unit_price,	
 reit_qty,		
 reit_unit_price * reit_qty AS amount,	
 reit_remark,
 reit_idx		
FROM ".ZKP_SQL."_tb_return_item
WHERE turn_code = '$_code'
ORDER BY it_code";
$result	=& query($sql);
while($items =& fetchRow($result)) {
?>
			<tr>
				<td><?php echo $items[0]?></td>
				<td><?php echo $items[1]?></td>
				<td><?php echo cut_string($items[2],50)?></td>
				<td align="right"><?php echo $items[4]?></td>
				<td><?php echo $items[6]?></td>
			</tr>
<?php
} //END WHILE
?>
		</tbody>
	</table>
	<table width="85%" class="table_box">
		<tr>
			<th align="right">TOTAL</th>
			<th width="10%"><input name="totalQty" type="text" class="reqn" style="width:100%" readonly></th>
			<th width="20%">&nbsp;</th>
		</tr>
	</table><br>
	<strong>OTHERS</strong>
	<table width="100%" class="table_box">
		<tr>
			<th>SHIP TO</th>
			<td colspan="4"><b>[<?php echo trim($column['turn_ship_to']) ?>]</b> <?php echo $column['turn_ship_to_name'] ?></td>
		</tr>
		<tr>
			<th width="15%">SIGN BY</th>
			<td width="25%"><?php echo $column['turn_signature_by'] ?></td>
			<th width="15%">TYPE RETURN</th>
			<td>
				<input type="radio" name="_paper" value="0" <?php echo ($column['turn_paper']==0)?'checked':'' ?> disabled> Return Item &nbsp;
				<input type="radio" name="_paper" value="1" <?php echo ($column['turn_paper']==1)?'checked':'' ?> disabled> Return No. Only
			</td>
		</tr>
	</table><br />
<table width="100%" class="table_layout">
	<tr>
		<td width="50%"><strong>WAREHOUSE CONFIRM</strong></td>
		<td align="right">
			<?php if($column['turn_cfm_wh_timestamp'] != '') {?>
			<i><span class="comment">Confirmed by : <?php echo  $column["turn_cfm_wh_by_account"] . date(", j-M-Y g:i:s", strtotime($column['turn_cfm_wh_timestamp']))?></span></i>
			<?php } ?>
		</td>
	</tr>
</table>
<?php if($column['turn_cfm_wh_timestamp'] == '') {?>
<form name="frmWarehouseConfirm" method="post">
<input type="hidden" name="p_mode" value="cfm_warehouse">
<input type="hidden" name="_code" value="<?php echo $column['turn_code']?>">
<table width="100%" class="table_box">
	<tr>
		<th width="15%">DATE</th>
		<td><input type="text" name="_date" class="reqd" value="<?php echo date("j-M-Y")?>"></td>
		<td align="right"><button name="btnCfmWarehouse" class="input_sky">CONFIRM</button></td> 
	</tr>
</table><br />
</form>
<script language="javascript" type="text/javascript">
window.document.frmWarehouseConfirm.btnCfmWarehouse.onclick = function() {
	if(confirm("Are you sure to confirm outgoing item from warehouse?")) {
		if(verify(window.document.frmWarehouseConfirm)){
			window.document.frmWarehouseConfirm.submit();
		}
	}
}
</script>
<?php } else if($column['turn_cfm_wh_timestamp'] != '') { ?>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">DATE</th>
		<td><?php echo date("j-M-Y", strtotime($column['turn_cfm_wh_date']))?></td>
	</tr>
</table><br />
<?php if($S->getValue("ma_idx") == 1) { ?>
<form name="frmWarehouseUnConfirm" method="post">
<input type="hidden" name="p_mode" value="uncfm_warehouse">
<input type="hidden" name="_code" value="<?php echo $column['turn_code']?>">
<table width="100%" class="table_box">
	<tr>
		<th width="15%">DATE</th>
		<td><?php echo date("j-M-Y", strtotime($column['turn_cfm_wh_date']))?></td>
		<td align="right"><button name="btnUnCfmWarehouse" class="input_sky">UNCONFIRM</button></td> 
	</tr>
</table><br />
</form>
<script language="javascript" type="text/javascript">
window.document.frmWarehouseUnConfirm.btnUnCfmWarehouse.onclick = function() {
/*	if(confirm("Are you sure to unconfirm outgoing item from warehouse?\nUnconfirmed invoice will changes the previous summary!")) {
		if(verify(window.document.frmWarehouseUnConfirm)){
			window.document.frmWarehouseUnConfirm.submit();
		}
	}*/
}
</script>
<?php }} ?>
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