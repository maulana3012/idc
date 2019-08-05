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
if (!isset($_GET['_code']) || $_GET['_code'] == '') goPage(HTTP_DIR . 'warehouse/warehouse/index.php');
$_code = $_GET['_code'];

//CONFIRM WAREHOUSE
if (ckperm(ZKP_INSERT, HTTP_DIR . "warehouse/warehouse/index.php", 'cfm_warehouse')) {

	$_code		= $_POST['_code'];
	$_date 		= $_POST['_date'];
	$_cfm_by	= $S->getValue("ma_account");

	$result = executeSP(
		ZKP_SQL."_cfmWarehouse",
		"$\$return_order$\$",
		"$\${$_code}$\$",
		"$\${$_date}$\$",
		"$\${$_cfm_by}$\$"
	);

	if (isZKError($result))
		$M->goErrorPage($result, HTTP_DIR . "warehouse/warehouse/index.php");

	goPage(HTTP_DIR . 'warehouse/warehouse/detail_return_order.php?_code='.urlencode($_code));
}

//UNCONFIRM WAREHOUSE
if (ckperm(ZKP_INSERT, HTTP_DIR . "warehouse/warehouse/index.php", 'uncfm_warehouse')) {

	$result =& query("UPDATE ".ZKP_SQL."_tb_return_order SET reor_cfm_wh_by_account='',reor_cfm_wh_timestamp=null,reor_cfm_wh_date=null where reor_code='$_code'");

	if (isZKError($result))
		$M->goErrorPage($result, HTTP_DIR . "warehouse/warehouse/index.php");

	goPage(HTTP_DIR . 'warehouse/warehouse/detail_return_order.php?_code='.urlencode($_code));
}

//DEFAULT PROCESS
$sql = "SELECT * FROM ".ZKP_SQL."_tb_return_order WHERE reor_code = '$_code'";
if(isZKError($result =& query($sql)))
	$M->goErrorPage($result, HTTP_DIR . "warehouse/warehouse/index.php");

$column =& fetchRowAssoc($result);
if(numQueryRows($result) <= 0) {
	$result = new ZKError(
		"CODE_NOT_EXIST",
		"CODE_NOT_EXIST",
		"Return order no <b>$_code</b> doesn't exist in system. Please check again.");

	$M->goErrorPage($result,  HTTP_DIR . "warehouse/warehouse/daily_summary_by_period.php");
}

//take discount percentage from customer group
$sql = "SELECT cug_basic_disc_pct from ".ZKP_SQL."_tb_customer_group WHERE cug_code = (SELECT cug_code FROM ".ZKP_SQL."_tb_customer WHERE cus_code = '".$column["reor_cus_to"]."')";

isZKError($res =& query($sql)) ? $M->goErrorPage($res, HTTP_DIR . "warehouse/warehouse/revise_order.php?_code=$_code") : false;
$disc = fetchRow($res);
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
function reCalculateAmount(){
	//set Total EA & Amount
	var count = window.rowPosition.rows.length;
	var sumOfQty = 0;
	
	for (var i=0; i<count; i++) {
		var oRow = window.rowPosition.rows(i);
		sumOfQty = sumOfQty + parseFloat(removecomma(oRow.cells(3).innerText));
	}

	window.document.all.totalQty.value = addcomma(sumOfQty);
}
</script>
</head>
<body topmargin="0" leftmargin="0" onLoad="reCalculateAmount()">
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
<h4>DETAIL RETURN ORDER</h4>
<table width="100%" class="table_box">
	<tr>
		<td colspan="3"><strong>ORDER INFORMATION</strong></td>
		<td colspan="3" align="right">
			<I>Last updated by : <?php echo $column['reor_lastupdated_by_account'].date(', j-M-Y g:i:s', strtotime($column['reor_lastupdated_timestamp']))." Rev:".$column['reor_revesion_time']?></I>
		</td>
	</tr>
	<tr>
		<th width="12%">RETURN CODE</th>
		<td width="30%"><strong><?php echo $column['reor_code']?></strong></td>
		<th width="12%">RECEIVED BY</th>
		<td><?php echo $column['reor_received_by']?></td>
		<th width="12%">CONFIRM BY</th>
		<td><?php echo $column['reor_confirm_by']?></td>
	</tr>
	<tr>
		<th>PO DATE</th>
		<td><?php echo date("j-M-Y", strtotime($column['reor_po_date']))?></td>
		<th>PO NO</th>
		<td><?php echo $column['reor_po_no']?></td>
		<th>VAT</th>
		<td><?php echo $column['reor_vat']?> %</td>
	</tr>
	<tr>
		<th>TYPE</th>
		<td><?php echo  ($column['reor_type'] == 'RO') ? 'Sales' : 'Konsinyasi'?></td>
		<th>ORDER</th>
		<td><?php echo $column['ord_code']?></td>
		<th>DATE</th>
		<td><?php echo ($column['reor_ord_reference_date'] == '') ? '' : date('j-M-Y', strtotime($column['reor_ord_reference_date']))?></td>
	</tr>
</table>
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<th rowspan="2" width="12%">CUSTOMER</th>
		<th width="12%">CODE</th>
		<td width="15%"><?php echo $column['reor_cus_to']?></td>
		<th width="12%">ATTN</th>
		<td width="43%"><?php echo $column['reor_cus_to_attn']?></td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="3"><?php echo $column['reor_cus_to_address']?></td>
	</tr>
	<tr>
		<th rowspan="2">SHIP TO</th>
		<th>CODE</th>
		<td><?php echo $column['reor_ship_to']?></td>
		<th>ATTN</th>
		<td><?php echo $column['reor_ship_to_attn']?></td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="3"><?php echo $column['reor_ship_to_address']?></td>
	</tr>
	<tr>
		<th rowspan="2">BILL TO</th>
		<th>CODE</th>
		<td><?php echo $column['reor_bill_to']?></td>
		<th>ATTN</th>
		<td><?php echo $column['reor_bill_to_attn']?></td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="3"><?php echo $column['reor_bill_to_address']?></td>
	</tr>
</table><br>
<strong>ITEM LIST</strong>
<table width="85%" class="table_box">
	<tr>
		<th width="6%">CODE</th>
		<th width="20%">ITEM NO</th>
		<th>DESCRIPTION</th>
		<th width="10%">QTY</th>
		<th width="20%">REMARK</th>
	</tr>
	<tbody id="rowPosition">
<?php
$sql = "
SELECT
 a.it_code,			--0
 a.it_model_no,		--1
 a.it_desc,			--2
 b.roit_remark,		--3
 b.roit_unit_price,	--4
 b.roit_qty,		--5
 b.roit_unit_price * b.roit_qty AS amount	--6
FROM ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_return_order_item AS b ON (a.it_code = b.it_code)
WHERE b.reor_code = '$_code'";
$result	=& query($sql);
while($items =& fetchRow($result)) {
?>
	<tr id="<?php echo trim($items[0])?>">
		<td><?php echo $items[0]?></td>
		<td><?php echo $items[1]?></td>
		<td><?php echo $items[2]?></td>
		<td align="right"><?php echo $items[5]?></td>
		<td><?php echo $items[3]?></td>
	</tr>
<?php } ?>
	</tbody>
</table>
<table width="85%" class="table_box">
	<tr>
		<th align="right">TOTAL</th>
		<th width="10%"><input name="totalQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="20%">&nbsp;</th>
	</tr>
</table><br />
<table width="100%" class="table_layout">
	<tr>
		<td width="50%"><strong>WAREHOUSE CONFIRM</strong></td>
		<td align="right">
			<?php if($column['reor_cfm_wh_timestamp'] != '') {?>
			<i><span class="comment">Confirmed by : <?php echo  $column["reor_cfm_wh_by_account"] . date(", j-M-Y g:i:s", strtotime($column['reor_cfm_wh_timestamp']))?></span></i>
			<?php } ?>
		</td>
	</tr>
</table>
<?php if($column['reor_cfm_wh_timestamp'] == '') {?>
<form name="frmWarehouseConfirm" method="post">
<input type="hidden" name="p_mode" value="cfm_warehouse">
<input type="hidden" name="_code" value="<?php echo $column['reor_code']?>">
<table width="100%" class="table_box">
	<tr>
		<th width="12%">DATE</th>
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
<?php } else if($column['reor_cfm_wh_timestamp'] != '') { ?>
<table width="100%" class="table_box">
	<tr>
		<th width="12%">DATE</th>
		<td><?php echo date("j-M-Y", strtotime($column['reor_cfm_wh_date']))?></td>
	</tr>
</table><br />
<?php if($S->getValue("ma_idx") == 1) { ?>
<form name="frmWarehouseUnConfirm" method="post">
<input type="hidden" name="p_mode" value="uncfm_warehouse">
<input type="hidden" name="_code" value="<?php echo $column['reor_code']?>">
<table width="100%" class="table_box">
	<tr>
		<th width="12%">DATE</th>
		<td><?php echo date("j-M-Y", strtotime($column['reor_cfm_wh_date']))?></td>
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