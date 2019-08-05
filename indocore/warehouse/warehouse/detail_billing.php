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
		"$\$billing$\$",
		"$\${$_code}$\$",
		"$\${$_date}$\$",
		"$\${$_cfm_by}$\$"
	);

	if (isZKError($result))
		$M->goErrorPage($result, HTTP_DIR . "warehouse/warehouse/index.php");

	goPage(HTTP_DIR . 'warehouse/warehouse/detail_billing.php?_code='.urlencode($_code));
}

//UNCONFIRM WAREHOUSE
if (ckperm(ZKP_INSERT, HTTP_DIR . "warehouse/warehouse/index.php", 'uncfm_warehouse')) {

	$result =& query("UPDATE ".ZKP_SQL."_tb_billing SET bill_cfm_wh_by_account='',bill_cfm_wh_timestamp=null,bill_cfm_wh_date=null where bill_code='$_code'");

	if (isZKError($result))
		$M->goErrorPage($result, HTTP_DIR . "warehouse/warehouse/index.php");

	goPage(HTTP_DIR . 'warehouse/warehouse/detail_billing.php?_code='.urlencode($_code));
}

//DEFAULT PROCESS
$sql = "SELECT *, isBillingUsed(bill_code) AS billing_used FROM ".ZKP_SQL."_tb_billing WHERE bill_code = '$_code'";
if(isZKError($result =& query($sql)))
	$M->goErrorPage($result, HTTP_DIR . "warehouse/warehouse/index.php");

$column =& fetchRowAssoc($result);
if(numQueryRows($result) <= 0) {
	$result = new ZKError(
		"CODE_NOT_EXIST",
		"CODE_NOT_EXIST",
		"Billing no <b>$_code</b> doesn't exist in system. Please check again.");

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
//Reculate Amount base on the form element
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
<h4>BILLING DETAIL</h4>
	<table width="100%" class="table_box">
		<tr>
			<td colspan="3"><strong>BILLING INFORMATION</strong></td>
			<td colspan="3" align="right"><I>
				Last updated by : <?php echo $column['bill_lastupdated_by_account'].date(', j-M-Y g:i:s', strtotime($column['bill_lastupdated_timestamp']))?></I>
			</td>
		</tr>
		<tr>
			<th width="15%">INVOICE NO</th>
			<td width="35%" colspan="2"><?php echo $_code ?></td>
			<th width="15%">INVOICE DATE</th>
			<td><?php echo date("j-M-Y", strtotime($column['bill_inv_date']))?></td>
		</tr>
		<tr>
			<th>FAKTUR PAJAK NO.</th>
			<td colspan="2"><?php echo $column['bill_vat_inv_no'] ?></td>
			<th>RECEIVED BY</th>
			<td colspan="2"><?php echo $column['bill_received_by']?></td>
		</tr>
		<tr>
			<th>VAT</th>
			<td colspan="2">
				<input type="radio" name="_btnVat" value="y" disabled <?php echo ($column['bill_vat'] > 0) ? 'checked' : '' ?> disabled><input name="_vat" type="text" class="fmtn" size="2" maxlength="4" value="<?php echo $column['bill_vat'] ?>" readonly>%
				<input type="radio" name="_btnVat" value="n" disabled <?php echo ($column['bill_vat'] > 0) ? '' : 'checked' ?> disabled>NON VAT
			</td>
			<th>TYPE OF PAJAK</th>
			<td>
				<input type="radio" name="_type_of_pajak" value="IO" <?php echo ($column['bill_type_pajak'] == 'IO') ? "checked" : '' ?> disabled>IO &nbsp;
				<input type="radio" name="_type_of_pajak" value="IP" <?php echo ($column['bill_type_pajak'] == 'IP') ? "checked" : '' ?> disabled>IP
			</td>
		</tr>
		<tr>
			<th>PO NO</th>
			<td colspan="2"><?php echo $column['bill_po_no']?></td>
			<th>PO DATE</th>
			<td><?php echo ($column['bill_po_date'] != '') ? date("j-M-Y", strtotime($column['bill_po_date'])) : ''?></td>
		</tr>
		<?php if(substr($column['bill_code'],2) == substr($column['bill_sj_code'],2)) {?>
		<tr>
			<th>SJ CODE</th>
			<td colspan="2">
				<input type="checkbox" disabled>
				<input type="hidden" name="_sj_code" class="req" value="<?php echo $column['bill_sj_code'] ?>">
				<?php echo $column['bill_sj_code']?>
			</td>
			<th width="15%">SJ DATE</th>
			<td colspan="2"><input type="hidden" name="_sj_date" value="<?php echo $column['bill_sj_date']?>"><?php echo date("j-M-Y", strtotime($column['bill_sj_date']))?></td>
		</tr>
		<?php } else {?>
		<tr>
			<th>SJ CODE</th>
			<td colspan="2">
				<input type="checkbox" checked disabled>
				<input type="text" name="_sj_code" class="req" value="<?php echo $column['bill_sj_code'] ?>">
			</td>
			<th width="15%">SJ DATE</th>
			<td colspan="2"><input type="text" name="_sj_date" class="reqd" value="<?php echo date("j-M-Y", strtotime($column['bill_sj_date']))?>"></td>
		</tr>
		<?php } ?>
		<tr>
			<th rowspan="3">CUSTOMER</th>
			<th width="12%">CODE</th>
			<td><?php echo $column['bill_cus_to'] ?></td>
			<th>NAME</th>
			<td colspan="3"><?php echo $column['bill_cus_to_name'] ?></td>
		</tr>
		<tr>
			<th>ATTN</th>
			<td><?php echo $column['bill_cus_to_attn'] ?></td>
			<th>NPWP</th>
			<td colspan="3"><?php echo $column['bill_npwp'] ?></td>
		</tr>
		<tr>
			<th width="12%">ADDRESS</th>
			<td colspan="5"><?php echo $column['bill_cus_to_address'] ?></td>
		</tr>
		<?php if($column['bill_vat'] > 0) { ?>
		<tr>
			<th rowspan="2">FAKTUR<br />PAJAK TO</th>
			<th width="12%">CODE</th>
			<td><?php echo $column['bill_pajak_to'] ?></td>
			<th>NAME</th>
			<td colspan="3"><?php echo $column['bill_pajak_to_name'] ?></td>
		</tr>
		<tr>
			<th width="12%">ADDRESS</th>
			<td colspan="5"><?php echo $column['bill_pajak_to_address'] ?></td>
		</tr>
		<?php } ?>
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
$sql = "SELECT 
icat_midx, 		--0
it_code,		--1
it_type,		--2
it_model_no,	--3
it_desc,		--4
biit_unit_price,	--5
biit_qty,		--6
biit_unit_price * biit_qty AS amount,	--7
biit_remark		--8
FROM ".ZKP_SQL."_tb_billing_item
WHERE bill_code = '$_code'
ORDER BY it_code";
$result	=& query($sql);
while($items =& fetchRow($result)) {
?>
			<tr id="<?php echo trim($items[1])?>">
				<td><?php echo $items[1]?></td>
				<td><?php echo $items[3]?></td>
				<td><?php echo $items[4]?></td>
				<td align="right"><?php echo number_format($items[6])?></td>
				<td><?php echo $items[8]?></td>
			</tr>
<?php } ?>
		</tbody>
	</table>
	<table width="85%" class="table_box">
		<tr>
			<th align="right">TOTAL</th>
			<th width="10%"><input name="totalQty" type="text" class="reqn" style="width:100%" readonly></th>
			<th width="19%"></th>
		</tr>
	</table><br>
	<strong>CONDITION</strong>
	<strong>OTHERS</strong>
	<table width="100%" class="table_box">
		<tr>
			<th>SHIP TO</th>
			<td><b>[<?php echo trim($column['bill_ship_to']) ?>]</b> <?php echo $column['bill_ship_to_name'] ?></td>
		</tr>
		<tr>
			<th width="15%">SIGN BY</th>
			<td colspan="3"><?php echo $column['bill_signature_by'] ?></td>
		</tr>
		<tr>
			<th>TYPE INVOICE</th>
			<td width="35%">
				<input type="radio" name="_type_invoice" value="0" <?php echo ($column['bill_type_invoice']==0? "checked":"") ?> disabled>Issue Item &nbsp;
				<input type="radio" name="_type_invoice" value="1" <?php echo ($column['bill_type_invoice']==1? "checked":"") ?> disabled>Issue No. Only
			</td>
			<th width="15%">PAPER FORMAT</th>
			<td>
				<input type="radio" name="_paper_format" value="A" <?php echo ($column['bill_paper_format']=='A'?" checked":"") ?> disabled>A,
				<input type="radio" name="_paper_format" value="B" <?php echo ($column['bill_paper_format']=='B'?" checked":"") ?> disabled>B
			</td>
		</tr>
		<tr>
			<th>SALES PERIOD</th>
			<td colspan="3">
				FROM : <?php echo ($column['bill_sales_from'] == '') ? '' : date('d-M-Y', strtotime($column['bill_sales_from'])) ?>
				TO : <?php echo ($column['bill_sales_to'] == '') ? '' : date('d-M-Y', strtotime($column['bill_sales_to'])) ?>
			</td>
		</tr>
		<?php if($column['bill_vat'] > 0) {?>
		<tr>
			<th>SIGN PAJAK BY</th>
			<td colspan="3">
				<input type="radio" name="_signature_pajak_by" value="A"<?php echo ($column['bill_signature_pajak_by']=='A'?" checked":"") ?> disabled>In Ki Kim Lee &nbsp;
				<input type="radio" name="_signature_pajak_by" value="B"<?php echo ($column['bill_signature_pajak_by']=='B'?" checked":"") ?> disabled>Min Sang Hyun
			</td>
		</tr>
		<?php } ?>
	</table><br />
<table width="100%" class="table_layout">
	<tr>
		<td width="50%"><strong>WAREHOUSE CONFIRM</strong></td>
		<td align="right">
			<?php if($column['bill_cfm_wh_timestamp'] != '') {?>
			<i><span class="comment">Confirmed by : <?php echo  $column["bill_cfm_wh_by_account"] . date(", j-M-Y g:i:s", strtotime($column['bill_cfm_wh_timestamp']))?></span></i>
			<?php } ?>
		</td>
	</tr>
</table>
<?php if($column['bill_cfm_wh_timestamp'] == '') {?>
<form name="frmWarehouseConfirm" method="post">
<input type="hidden" name="p_mode" value="cfm_warehouse">
<input type="hidden" name="_code" value="<?php echo $column['bill_code']?>">
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
<?php } else if($column['bill_cfm_wh_timestamp'] != '') { ?>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">DATE</th>
		<td><?php echo date("j-M-Y", strtotime($column['bill_cfm_wh_date']))?></td>
	</tr>
</table><br />
<?php if($S->getValue("ma_idx") == 1) { ?>
<form name="frmWarehouseUnConfirm" method="post">
<input type="hidden" name="p_mode" value="uncfm_warehouse">
<input type="hidden" name="_code" value="<?php echo $column['bill_code']?>">
<table width="100%" class="table_box">
	<tr>
		<th width="15%">DATE</th>
		<td><?php echo date("j-M-Y", strtotime($column['bill_cfm_wh_date']))?></td>
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