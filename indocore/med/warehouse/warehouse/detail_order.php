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
$_code = addslashes(urldecode($_GET['_code']));

//CONFIRM WAREHOUSE
if (ckperm(ZKP_INSERT, HTTP_DIR . "warehouse/warehouse/index.php", 'cfm_warehouse')) {

	$_code		= $_POST['_code'];
	$_date 		= $_POST['_date'];
	$_cfm_by	= $S->getValue("ma_account");

	$result = executeSP(
		ZKP_SQL."_cfmWarehouse",
		"$\$order$\$",
		"$\${$_code}$\$",
		"$\${$_date}$\$",
		"$\${$_cfm_by}$\$"
	);

	if (isZKError($result))
		$M->goErrorPage($result, HTTP_DIR . "apotik/order/index.php");

	goPage(HTTP_DIR . 'warehouse/warehouse/detail_order.php?_code='.urlencode($_code));
}

//DEFAULT PROCESS
$sql = "SELECT * FROM ".ZKP_SQL."_tb_order WHERE ord_code = '$_code'";
if(isZKError($result =& query($sql)))
	$M->goErrorPage($result, HTTP_DIR . "warehouse/warehouse/index.php");

$column =& fetchRowAssoc($result);
if(numQueryRows($result) <= 0) {
	$result = new ZKError(
		"CODE_NOT_EXIST",
		"CODE_NOT_EXIST",
		"Order no <b>$_code</b> doesn't exist in system. Please check again.");

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
function reCalculationTotal() {
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
<h4>DETAIL ORDER</h4>
<table width="100%" class="table_box">
	<tr>
		<td colspan="2"><strong>ORDER INFORMATION</strong></td>
		<td colspan="4" align="right"><I>Last updated by : <?php echo $column['ord_lastupdated_by_account'].date(', j-M-Y g:i:s', strtotime($column['ord_lastupdated_timestamp']))." Rev:".$column['ord_revision_time']?></I></td>
	</tr>
	<tr>
		<th width="12%">ORDER CODE</th>
		<td width="30%"><strong><?php echo $column['ord_code']?></strong></td>
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
		<td><span id="_vat"><?php echo $column['ord_vat']?></span>%</td>
	</tr>
</table>
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<th rowspan="2" width="12%">CUSTOMER</th>
		<th width="12%">CODE</th>
		<td width="18%"><?php echo $column['ord_cus_to']?></td>
		<th width="12%">ATTN</th>
		<td><?php echo $column['ord_cus_to_attn']?></td>
	</tr>
	<tr>
		<th width="12%">ADDRESS</th>
		<td colspan="3"><?php echo $column['ord_cus_to_address']?></td>
	</tr>
	<tr>
		<th rowspan="2">SHIP TO</th>
		<th>CODE</th>
		<td><?php echo $column['ord_ship_to']?></td>
		<th>ATTN</th>
		<td><?php echo $column['ord_ship_to_attn']?></td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="3"><?php echo $column['ord_ship_to_address']?></td>
	</tr>
	<tr>
		<th rowspan="2">BILL TO</th>
		<th>CODE</th>
		<td><?php echo $column['ord_bill_to']?></td>
		<th>ATTN</th>
		<td><?php echo $column['ord_bill_to_attn']?></td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="3"><?php echo $column['ord_bill_to_address']?></td>
	</tr>
</table><br>
<strong>ITEM LIST</strong>
<table width="85%" class="table_box">
	<tr>
		<th width="5%">CODE</th>
		<th width="12%">ITEM NO</th>
		<th>DESCRIPTION</th>
		<th width="10%">QTY</th>
		<th width="15%">DELIVERY</th>
	</tr>
		<tbody id="rowPosition">
<?php
$sql = "SELECT
a.it_code,
a.it_model_no,
a.it_desc,
b.odit_unit_price,
b.odit_qty,
b.odit_unit_price * b.odit_qty AS amount,
to_char(b.odit_delivery, 'DD-Mon-YY') AS delivery
FROM ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_order_item AS b ON (a.it_code = b.it_code)
WHERE b.ord_code = '$_code'";
$result	=& query($sql);
while($items =& fetchRow($result)) {
?>
		<tr>
			<td><?php echo trim($items[0])?></td>
			<td><?php echo cut_string($items[1], 17)?></td>
			<td><?php echo cut_string($items[2], 45)?></td>
			<td align="right"><?php echo number_format($items[4])?></td>
			<td align="center"><?php echo $items[6]?></td>
		</tr>
<?php } ?>		
	</tbody>
</table>
<table width="85%" class="table_box">
	<thead>
		<tr>
			<th colspan="4" align="right">TOTAL</th>
			<th width="10%"><input name="totalQty" type="text" class="reqn" style="width:100%" readonly></th>
			<th width="15%" colspan="2">&nbsp;</th>
		</tr>
	</thead>
</table><br>
<table width="100%" class="table_layout">
	<tr>
		<td width="50%"><strong>WAREHOUSE CONFIRM</strong></td>
		<td align="right">
			<?php if($column['ord_cfm_wh_timestamp'] != '') {?>
			<i><span class="comment">Confirmed by : <?php echo  $column["ord_cfm_wh_by_account"] . date(", j-M-Y g:i:s", strtotime($column['ord_cfm_wh_timestamp']))?></span></i>
			<?php } ?>
		</td>
	</tr>
</table>
<form name="frmWarehouseConfirm" method="post">
<input type="hidden" name="p_mode" value="cfm_warehouse">
<input type="hidden" name="_code" value="<?php echo $column['ord_code']?>">
<table width="100%" class="table_box">
	<tr>
		<?php if($column['ord_cfm_wh_timestamp'] == '') {?>
		<th width="12%">DATE</th>
		<td><input type="text" name="_date" class="reqd" value="<?php echo date("j-M-Y")?>"></td>
		<td align="right"><button name="btnCfmWarehouse" class="input_sky">CONFIRM</button></td> 
		<?php } else {?>
		<th width="12%">DATE</th>
		<td><?php echo date("j-M-Y", strtotime($column['ord_cfm_wh_date']))?></td>
		<td align="right"><button name="btnCfmWarehouse" class="input_sky" disabled>CONFIRM</button></td>
		<?php } ?>
	</tr>
</table><br />
</form>
<script language="javascript" type="text/javascript">
window.document.frmWarehouseConfirm.btnCfmWarehouse.onclick = function() {
/*	if(confirm("Are you sure to confirm outgoing item from warehouse?")) {
		if(verify(window.document.frmWarehouseConfirm)){
			window.document.frmWarehouseConfirm.submit();
		}
	}*/
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