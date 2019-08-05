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
ckperm(ZKP_SELECT, HTTP_DIR . "apotik/order/index.php");

//Check PARAMETER
if(!isset($_GET['_code']) || $_GET['_code'] == "")
	die("<script language=\"javascript1.2\">window.close();</script>");
$_code = addslashes(urldecode($_GET['_code']));

//DEFAULT PROCESS
$sql = "SELECT * FROM ".ZKP_SQL."_tb_order WHERE ord_code = '$_code'";
if(isZKError($result =& query($sql)))
	die("<script language=\"javascript1.2\">window.close();</script>");

$column =& fetchRowAssoc($result);
$numRow = numQueryRows($result);
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
	var sumOfTotal = 0;
	
	for (var i=0; i<count; i++) {
		var oRow = window.rowPosition.rows(i);
		sumOfQty = sumOfQty + parseInt(oRow.cells(4).innerText);
		sumOfTotal = sumOfTotal + parseFloat(removecomma(oRow.cells(5).innerText));
	}

	var vat = parseFloat(window.document.all._vat.innerText) / 100 * sumOfTotal;
	
	window.document.all.totalQty.value = addcomma(sumOfQty);
	window.document.all.total.value = numFormatval(sumOfTotal.toString(), 2);
	window.document.all.totalVat.value = numFormatval(vat.toString(), 2);
	window.document.all.totalAmount.value = numFormatval(sumOfTotal + vat + '', 2);
}
</script>
</head>
<body topmargin="0" leftmargin="0" style="margin:8px" onLoad="reCalculationTotal()">
<span class="font-size=18px;"><b>[<font color="blue"><?php echo strtoupper($currentDept) ?></font>] DETAIL ORDER : <?php echo $_code ?></b></span><br />
<?php
if($column["ord_type_invoice"]==0) echo "<span class=\"comment\"><i>* issue invoice &amp; outgoing item</i></span>";
else if($column["ord_type_invoice"]==1) echo "<span class=\"comment\"><i>* issue invoice only</i></span>";
?>
<br /><br />
	<strong>ORDER INFORMATION</strong>
	<table width="100%" class="table_box">
		<tr>
			<th width="12%">ORDER CODE</th>
			<td><strong><?php echo $column['ord_code']?></strong></td>
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
			<td width="25%"><?php echo $column['ord_cus_to']?></td>
			<th width="8%">ATTN</th>
			<td width="43%"><?php echo $column['ord_cus_to_attn']?></td>
		</tr>
		<tr>
			<th width="12%">ADDRESS</th>
			<td colspan="3"><?php echo cut_string($column['ord_cus_to_address'],60)?></td>
		</tr>
		<tr>
			<th rowspan="2" width="12%">SHIP TO</th>
			<th width="12%">CODE</th>
			<td width="25%"><?php echo $column['ord_ship_to']?></td>
			<th width="8%">ATTN</th>
			<td width="43%"><?php echo $column['ord_ship_to_attn']?></td>
		</tr>
		<tr>
			<th width="12%">ADDRESS</th>
			<td colspan="3"><?php echo cut_string($column['ord_ship_to_address'],60)?></td>
		</tr>
		<tr>
			<th rowspan="2" width="12%">BILL TO</th>
			<th width="12%">CODE</th>
			<td width="25%"><?php echo $column['ord_bill_to']?></td>
			<th width="8%">ATTN</th>
			<td width="43%"><?php echo $column['ord_bill_to_attn']?></td>
		</tr>
		<tr>
			<th width="12%">ADDRESS</th>
			<td colspan="3"><?php echo cut_string($column['ord_bill_to_address'],60)?></td>
		</tr>
	</table><br>
	<strong>ITEM LIST</strong>
	<table width="100%" class="table_box">
		<tr>
			<th width="5%">CODE</th>
			<th width="12%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="10%">@ PRICE</th>
			<th width="5%">QTY</th>
			<th width="15%">AMOUNT(Rp)</th>
			<th width="12%">DELIVERY</th>
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
			<td><?php echo cut_string($items[1], 10)?></td>
			<td><?php echo cut_string($items[2], 30)?></td>
			<td align="right"><?php echo number_format((double)$items[3])?></td>
			<td align="right"><?php echo number_format((double)$items[4])?></td>
			<td align="right"><?php echo number_format((double)$items[5])?></td>
			<td align="center"><?php echo $items[6]?></td>
		</tr>
<?php
} //END WHILE
?>
		
		</tbody>
	</table>
	<table width="100%" class="table_box">
		<thead>
			<tr>
				<th colspan="4" align="right">Before VAT</th>
				<th width="5%"><input name="totalQty" type="text" class="reqn" style="width:100%" readonly></th>
				<th width="12%"><input name="total" type="text" class="reqn" style="width:100%" readonly></th>
				<th width="8%" colspan="2">&nbsp;</th>
			</tr>
			<tr>
				<th colspan="5" align="right">VAT</th>
				<th width="12%"><input name="totalVat" type="text" class="reqn" style="width:100%" readonly></th>
				<th width="8%" colspan="2">&nbsp;</th>
			</tr>
			<tr>
				<th colspan="5" align="right">GRAND TOTAL</th>
				<th width="15%"><input name="totalAmount" type="text" class="reqn" style="width:100%" readonly></th>
				<th width="12%" colspan="2">&nbsp;</th>
			</tr>
		</thead>
	</table><br>
	<strong>CONDITION</strong>
	<table class="table_box" width="100%">
		<tr>
			<th rowspan="2" width="12%">PRICE</th>
			<td>DISCOUNT:<?php echo $column['ord_price_discount'];?>%</td>
			<td>FROM: <input type="checkbox" name="_price_chk[]" value="1" <?php echo ($column['ord_price_chk'] & 1)? "checked":""?> disabled>Dealer 1's</td>
			<td><input type="checkbox" name="_price_chk[]" value="2" <?php echo ($column['ord_price_chk'] & 2)? "checked":""?> disabled>Dealer 2's</td>
			<td><input type="checkbox" name="_price_chk[]" value="4" <?php echo ($column['ord_price_chk'] & 4)? "checked":""?> disabled>Retailer's</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
			<td><input type="checkbox" name="_price_chk[]" value="8" <?php echo ($column['ord_price_chk'] & 8)?"checked":""?> disabled>Consumer's</td>
		</tr>
		<tr>
			<th width="12%">DELIVERY</th>
			<td>1.<input type="checkbox" name="_delivery_chk[]" value="1" <?php echo ($column['ord_delivery_chk'] & 1)? "checked":""?> disabled>ex W/house(P/C/D)</td>
			<td>2.<input type="checkbox" name="_delivery_chk[]" value="2" <?php echo ($column['ord_delivery_chk'] & 2)? "checked":""?> disabled>Franco(P/D)</td>
			<td>by <?php echo $column['ord_delivery_by']?></td>
			<td><input type="checkbox" name="_delivery_chk[]" value="4" <?php echo ($column['ord_delivery_chk'] & 4)? "checked":""?> disabled>Freight charge:<?php echo number_format((double)$column['ord_delivery_freight_charge'])?></td>
		</tr>
		<tr>
			<th rowspan="4" width="12%">PAYMENT</th>
			<td>1.<input type="checkbox" name="_payment_chk[]" value="1" <?php echo ($column['ord_payment_chk'] & 1)? "checked":""?> disabled>COD</td>
			<td>2.<input type="checkbox" name="_payment_chk[]" value="2" <?php echo ($column['ord_payment_chk'] & 2)? "checked":""?> disabled>PREPAID</td>
			<td>3.<input type="checkbox" name="_payment_chk[]" value="4" <?php echo ($column['ord_payment_chk'] & 4)? "checked":""?> disabled>Consignment</td>
			<td>4.<input type="checkbox" name="_payment_chk[]" value="8" <?php echo ($column['ord_payment_chk'] & 8)? "checked":""?> disabled>Free/TO/LF/RP/PT</td>
		</tr>
		<tr>
			<td>5. Within <?php echo $column['ord_payment_widthin_days']?> days after</td>
			<td>5a. <input type="checkbox" name="_payment_chk[]" value="16" <?php echo ($column['ord_payment_chk'] & 16)? "checked":""?> disabled>SJ/Inv/FP/Tender</td>
			<td>5b. Closing on <?php echo empty($column['ord_payment_closing_on']) ? "" : date("j-M-Y",strtotime($column['ord_payment_closing_on']))?></td>
			<td><input type="checkbox" name="_payment_chk[]" value="32" <?php echo ($column['ord_payment_chk'] & 32) ? "checked":""?> disabled> 
			For the Month/Week(M/W)</td>
		</tr>
		<tr>
			<td>by 1)<input type="checkbox" name="_payment_chk[]" value="64" <?php echo ($column['ord_payment_chk'] & 64)? "checked":""?> disabled>Cash</td>
			<td>2)<input type="checkbox" name="_payment_chk[]" value="128" <?php echo ($column['ord_payment_chk'] & 128)? "checked":""?> disabled>Check</td>
			<td>3)<input type="checkbox" name="_payment_chk[]" value="256" <?php echo ($column['ord_payment_chk'] & 256)? "checked":""?> disabled>Transfer</td>
			<td>4)<input type="checkbox" name="_payment_chk[]" value="512" <?php echo ($column['ord_payment_chk'] & 512)? "checked":""?> disabled> 
			Giro
		</tr>
		<tr>
			<td>by <textarea name="_payment_cash_by" rows="4" class="fmt" readonly><?php echo $column['ord_payment_cash_by']?></textarea></td>
			<td>by <textarea name="_payment_check_by" class="fmt" rows="4" readonly><?php echo $column['ord_payment_check_by']?></textarea></td>
			<td>by <textarea name="_payment_transfer_by" class="fmt" rows="4" readonly><?php echo $column['ord_payment_transfer_by']?></textarea></td>
			<td>by <textarea name="_payment_giro_by" rows="4" readonly><?php echo $column['ord_payment_giro_by']?></textarea></td>
		</tr>
		<tr>
			<th width="12%">REMARK</th>
			<td colspan="4"><textarea name="_remark" rows="3" style="width:98%" readonly><?php echo $column['ord_remark']?></textarea></td>
		</tr>
	</table>
<table width="100%" class="table_box">
	<tr>
		<td><I>Last updated by : <?php echo $column['ord_lastupdated_by_account'].date(', j-M-Y g:i:s', strtotime($column['ord_lastupdated_timestamp']))." Rev:".$column['ord_revision_time']?></I></td>
		<td align="right"><I>Delivery Confirm by : <?php echo $column['ord_cfm_deli_by_account'].date(', j-M-Y g:i:s', strtotime($column['ord_cfm_deli_timestamp']))?></I></td>
	</tr>
</table>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td align="right">
			<button name="btnClose" class="input_sky" onClick="window.close()">CLOSE</button>&nbsp;&nbsp;&nbsp;
			<?php if(isset($_GET['_list']) && $_GET['_list'] == 'y') {?>
			<button name="btnList" class="input_sky" onClick="window.history.go(-1)">LIST</button>
			<?php } ?>
		</td>
</table>
</body>
</html>