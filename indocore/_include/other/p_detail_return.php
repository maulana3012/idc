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

//Check PARAMETER
if(!isset($_GET['_code']) || $_GET['_code'] == "")
	die("<script language=\"javascript1.2\">window.close();</script>");

$_code = urldecode($_GET['_code']);

//DEFAULT PROCESS
$sql = "SELECT * FROM ".ZKP_SQL."_tb_return WHERE turn_code = '$_code'";
if(isZKError($result =& query($sql)))
	die("<script language=\"javascript1.2\">window.close();</script>");
$column =& fetchRowAssoc($result);
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
//Calculate (It different with revise_order.php & input_order.php)
function reCalculationTotal() {
	//set Total EA & Amount
	var f = window.document.all;
	var count = window.rowPosition.rows.length;
	var sumOfQty = 0;
	var sumOfTotal = 0;
	
	for (var i=0; i<count; i++) {
		var oRow = window.rowPosition.rows(i);
		sumOfQty = sumOfQty + parseInt(oRow.cells(4).innerText);
		sumOfTotal = sumOfTotal + parseFloat(removecomma(oRow.cells(5).innerText));
	}

	var totalAfterDisc = sumOfTotal;
	var total_disc	  = 0;
	var vat			  = 0;
	var delivery_cost = 0;

	if(f._disc.value > 0) {
		total_disc = Math.round(sumOfTotal * f._disc.value/100);
		totalAfterDisc = sumOfTotal - total_disc;
	}

	if (f._vat.value != '') {
		vat = f._vat.value;
	}

	if (f._delivery_freight_charge.value != '') {
		delivery_cost = parseFloat(removecomma(f._delivery_freight_charge.value));
	}

	vat = Math.round(parseFloat(vat) / 100 * totalAfterDisc);
	var totalAmount	= totalAfterDisc + vat + delivery_cost;

	var vat = 10 / 100 * sumOfTotal;

	f.totalQty.value	  = addcomma(sumOfQty);
	f.total.value		  = numFormatval(sumOfTotal.toString(), 0);
	f.total2.value		  = numFormatval(totalAfterDisc.toString(), 0);
	f.totalVat.value	  = numFormatval(vat.toString(), 0);
	f.totalDelivery.value = numFormatval(delivery_cost + '', 0);
	f.totalDisc.value	  = numFormatval(total_disc + '', 0);
	f.totalAmount.value   = numFormatval(totalAmount + '', 0);
}
</script>
</head>
<body style="margin:8" topmargin="0" leftmargin="0" onLoad="reCalculationTotal()">
<span class="font-size=18px;"><b>[<font color="blue"><?php echo strtoupper($currentDept) ?></font>] DETAIL RETURN : <?php echo $_code ?></b></span><br />
<?php
if($column["turn_paper"]==0) echo "<span class=\"comment\"><i>* issue invoice return &amp; outgoing item</i></span>";
else if($column["turn_paper"]==1) echo "<span class=\"comment\"><i>* issue invoice return only</i></span>";
?>
<br /><br />
<?php
$numRow = numQueryRows($result);
if ($numRow == 0) {
	echo "<span class=\"comment\">Invoice Return no <b>$_code</b> doesn't exist in system. So, you can't see the detail return.</span><br /><br />\n";
	echo "<button name=\"btnCLoseWindow\" class=\"input_sky\" onCLick=\"window.close()\">CLOSE</close>";
	exit;
}
?>
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<td colspan="2"><strong class="info">RETURN INFORMATION</strong></td>
		<td colspan="4" align="right">
			<I>Last updated by : <?php echo $column['turn_lastupdated_by_account'].date(', j-M-Y g:i:s', strtotime($column['turn_lastupdated_timestamp']))?></I>
		</td>
	</tr>
	<tr>
		<th width="15%">CODE</th>
		<td colspan="2"><?php echo $_code ?></td>
		<th width="18%">DATE</th>
		<td><?php echo date("j-M-Y", strtotime($column['turn_return_date']))?></td>
	</tr>
	<tr>
		<th>VAT</th>
		<td colspan="2">
			<input type="radio" name="_btnVat" value="y" disabled <?php echo ($column['turn_vat'] > 0) ? 'checked' : '' ?>><input name="_vat" type="text" class="fmtn" size="2" maxlength="4" value="<?php echo $column['turn_vat'] ?>" readonly>%
			<input type="radio" name="_btnVat" value="n" disabled <?php echo ($column['turn_vat'] > 0) ? '' : 'checked' ?>>NON VAT
		</td>
		<th>RECEIVED BY</th>
		<td><?php echo $column['turn_received_by']?></td>
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
		<th rowspan="2">CUSTOMER</th>
		<th width="12%">CODE</th>
		<td><?php echo $column['turn_cus_to'] ?></td>
		<th>NAME</th>
		<td><?php echo $column['turn_cus_to_name'] ?></td>
	</tr>
	<tr>
		<th width="12%">ADDRESS</th>
		<td colspan="3"><?php echo cut_string($column['turn_cus_to_address'],65) ?></td>
	</tr>
	<tr>
		<th width="15%" rowspan="5">INVOICE REF.</th>
		<th>CODE</th>
		<td><b><?php echo $column['turn_bill_code'] ?></b></td>
	</tr>
	<tr>
		<th>INVOICE DATE</th>
		<td><?php echo ($column['turn_bill_inv_date'] == '') ? '' : date('j-M-Y', strtotime($column['turn_bill_inv_date'])) ?></td>			
		<th>VAT INV NO</th>
		<td><?php echo $column['turn_bill_vat_inv_no'] ?></td>
	</tr>
	<tr>
		<th>DO NO</th>
		<td><?php echo $column['turn_bill_do_no']?></td>
		<th>DO DATE</th>
		<td><?php echo ($column['turn_bill_do_date']=='')?'':date('d-M-Y',strtotime($column['turn_bill_do_date'])) ?></td>
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
		<th>TYPE</th>
		<td>
			<select name="_type_return" class="req" disabled>
				<option value="RO">Return Order</option>
				<option value="RR">Return Replace</option>
			</select>
			<b class="info"> &nbsp;[ <?php echo $column['turn_return_condition'] ?> ]</b>
		</td>
		<th>SAME ITEM</th>
		<td>
			<input type="radio" name='_is_same_item' value='1' <?php echo ($column['turn_is_same_item'] == 1) ? 'checked' : '' ?> disabled>YES &nbsp; &nbsp;
			<input type="radio" name='_is_same_item' value='0' <?php echo ($column['turn_is_same_item'] == 0) ? 'checked' : '' ?> disabled>NO
		</td>
	</tr>
</table><br />
	<strong>ITEM LIST</strong>
	<table width="100%" class="table_box">
		<thead>
			<tr>
				<th width="5%">CODE</th>
				<th width="15%">ITEM NO</th>
				<th>DESCRIPTION</th>
				<th width="12%">UNIT PRICE</th>
				<th width="5%">QTY</th>
				<th width="15%">AMOUNT(Rp)</th>
			</tr>
		</thead>
		<tbody id="rowPosition">
<?php
$sql = "
SELECT
 a.it_code,			--0
 a.it_model_no,		--1
 a.it_desc,			--2
 b.reit_unit_price,	--3
 b.reit_qty,		--4
 b.reit_unit_price * b.reit_qty AS amount,	--5	
 b.reit_remark,		--6
 b.reit_idx			--7		
FROM ".ZKP_SQL."_tb_return_item AS b JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE turn_code = '$_code'
ORDER BY it_code, reit_idx";
$result	=& query($sql);
while($items =& fetchRow($result)) {
?>
			<tr>
				<td><?php echo $items[0]?></td>
				<td><?php echo $items[1]?></td>
				<td><?php echo $items[2]?></td>
				<td align="right"><?php echo number_format((double)$items[3])?></td>
				<td align="right"><?php echo $items[4]?></td>
				<td align="right"><?php echo number_format((double)$items[5])?></td>
			</tr>
<?php } ?>
		</tbody>
	</table>
	<table width="100%" class="table_box">
		<tr>
			<th align="right">SUB TOTAL</th>
			<th width="5%"><input name="totalQty" type="text" class="reqn" style="width:100%" readonly></th>
			<th width="15%"><input name="total" type="text" class="reqn" style="width:100%" readonly></th>
		</tr>
		<tr>
			<th align="right">DISC %</th>
			<th width="5%"><input name="_disc" type="text" class="reqn" style="width:100%" value="<?php echo $column['bill_discount'] ?>" onBlur="updateAmount()"></th>
			<th width="15%"><input name="totalDisc" type="text" class="reqn" style="width:100%" readonly></th>
		</tr>
		<tr>
			<th colspan="2" align="right">Before Vat</th>
			<th width="12%"><input name="total2" type="text" class="reqn" style="width:100%" readonly></th>
		</tr>
		<tr>
			<th colspan="2" align="right">VAT</th>
			<th width="15%"><input name="totalVat" type="text" class="reqn" style="width:100%" readonly></th>
		</tr>
		<tr>
			<th colspan="2" align="right">Delivery Cost</th>
			<th width="15%"><input name="totalDelivery" type="text" class="reqn" style="width:100%" readonly></th>
		</tr>
		<tr>
			<th colspan="2" align="right">GRAND TOTAL</th>
			<th width="15%"><input name="totalAmount" type="text" class="reqn" style="width:100%" readonly></th>
		</tr>
	</table><br>
	<strong>CONDITION</strong>
	<table class="table_box" width="100%">
		<tr>
			<th width="18%" valign="top" rowspan="2">DELIVERY</th>
			<td>1. &nbsp; <?php echo $column['turn_delivery_warehouse']?> &nbsp; ex W/house(P/C/D)</td>
			<td>2. &nbsp; <?php echo $column['turn_delivery_franco']?> &nbsp; Franco(P/D)</td>
		</tr>
		<tr>
			<td>by <?php echo $column['turn_delivery_by']?></td>
			<td>Freight charge: <input type="text" name="_delivery_freight_charge" size="8" value="<?php echo ($column['turn_delivery_freight_charge'] <= 0) ? '' : number_format((double)$column['turn_delivery_freight_charge'])?>" class="fmtn" disabled></td>
		</tr>
		<tr>
			<th rowspan="7" valign="top" width="12%">PAYMENT</th>
			<td colspan="2">
			 <input type="checkbox" name="_payment_chk[]" value="1" <?php echo ($column['turn_payment_chk'] & 1)? "checked":""?> disabled>COD &nbsp;
			 <input type="checkbox" name="_payment_chk[]" value="2" <?php echo ($column['turn_payment_chk'] & 2)? "checked":""?> disabled>PREPAID &nbsp;
			 <input type="checkbox" name="_payment_chk[]" value="4" <?php echo ($column['turn_payment_chk'] & 4)? "checked":""?> disabled>Consignment &nbsp;
			 <input type="checkbox" name="_payment_chk[]" value="8" <?php echo ($column['turn_payment_chk'] & 8)? "checked":""?> disabled>Free/TO/LF/RP/PT
			</td>
		</tr>
		<tr>
			<td>5. Within  &nbsp; <?php echo $column['turn_payment_widthin_days']?>  &nbsp; days after</td>
			<td>5a. <?php echo $column['turn_payment_sj_inv_fp_tender']?></td>
		</tr>
		<tr>
			<td>5b. Closing on <?php echo empty($column['turn_payment_closing_on']) ? "" : date("j-M-Y",strtotime($column['turn_payment_closing_on']))?></td>
			<td><?php echo $column['turn_payment_for_the_month_week'] ?>For the Month/Week(M/W)</td>
		</tr>
		<tr>
			<td colspan="2">1)<input type="checkbox" name="_payment_chk[]" value="16" <?php echo ($column['turn_payment_chk'] & 16)? "checked":""?> disabled>Cash by <input name="_payment_cash_by" type="text" class="fmt" id="_payment_cash_by" value="<?php echo $column['turn_payment_cash_by']?>" disabled></td>
		</tr>
		<tr>
			<td colspan="2">2)<input type="checkbox" name="_payment_chk[]" value="32" <?php echo ($column['turn_payment_chk'] & 32)? "checked":""?> disabled>Check by <input name="_payment_check_by" type="text" class="fmt" id="_payment_check_by" value="<?php echo $column['turn_payment_check_by']?>" disabled></td>
		</tr>
		<tr>	
			<td colspan="2">3)<input type="checkbox" name="_payment_chk[]" value="64" <?php echo ($column['turn_payment_chk'] & 64)? "checked":""?> disabled>Transfer by <input name="_payment_transfer_by" type="text" class="fmt" id="_payment_transfer_by" value="<?php echo $column['turn_payment_transfer_by']?>" disabled></td>
		</tr>
		<tr>
			<td colspan="2">
				4)<input type="checkbox" name="_payment_chk[]" value="128" <?php echo ($column['turn_payment_chk'] & 128)? "checked":""?> disabled>Giro &nbsp;
				Issue : <input type="text" name="_payment_giro_issue" size="10" class="fmtd" value="<?php echo ($column['turn_payment_giro_issue'] != '') ? date("j-M-Y", strtotime($column['turn_payment_giro_issue'])) : ''?>" disabled>
				Due : <input type="text" name="_payment_giro_due" size="10" class="reqd" value="<?php echo date("j-M-Y", strtotime($column['turn_payment_giro_due']))?>" disabled>
			</td>
		</tr>
		<tr>
			<th rowspan="2" valign="top">BANK</th>
			<td>
				<input type="radio" name="_bank" value="BCA1" onCLick=bankDesc(this) id="bca1" disabled <?php echo ($column['turn_payment_bank'] == 'BCA1') ? 'checked' : '' ?>><label for="bca1">BCA 1</label><br />
				<input type="radio" name="_bank" value="BCA2" onCLick=bankDesc(this) id="bca2" disabled <?php echo ($column['turn_payment_bank'] == 'BCA2') ? 'checked' : '' ?>><label for="bca2">BCA 2</label><br />
				<input type="radio" name="_bank" value="MANDIRI" onCLick=bankDesc(this) id="mandiri" disabled <?php echo ($column['turn_payment_bank'] == 'MANDIRI') ? 'checked' : '' ?>><label for="mandiri">Mandiri</label><br />
			</td>
			<td>
				<input type="radio" name="_bank" value="BII1" onCLick=bankDesc(this) id="bii1" disabled <?php echo ($column['turn_payment_bank'] == 'BII1') ? 'checked' : '' ?>><label for="bii1">BII 1</label><br />
				<input type="radio" name="_bank" value="BII2" onCLick=bankDesc(this) id="bii2" disabled <?php echo ($column['turn_payment_bank'] == 'BII2') ? 'checked' : '' ?>><label for="bii2">BII 2</label><br />
				<input type="radio" name="_bank" value="DANAMON" onCLick=bankDesc(this) id="danamon" disabled <?php echo ($column['turn_payment_bank'] == 'DANAMON') ? 'checked' : '' ?>><label for="danamon">Danamon</label>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<textarea name="_bank_address" rows="3" style="width:100%" readonly><?php echo $column['turn_payment_bank_address'] ?></textarea>
			</td>
		</tr>
		<tr>
			<th>TUKAR FAKTUR<br />DATE</th>
			<td><?php echo ($column['turn_tukar_faktur_date'] != '') ? date("j-M-Y", strtotime($column['turn_tukar_faktur_date'])) : ''?></td>
		</tr>
		<tr>
			<th>SHIP TO</th>
			<td colspan="4">
				<input type="text" name="_ship_to" class="fmt" size="5" value="<?php echo $column['turn_ship_to'] ?>" disabled>
				<input type="text" name="_ship_name" class="fmt" size="50" value="<?php echo $column['turn_ship_to_name'] ?>" disabled>
			</td>
		</tr>
	</table><br />
	<strong>OTHERS</strong>
	<table width="100%" class="table_box">
		<tr>
			<th width="15%">SIGN BY</th>
			<td><?php echo $column['turn_signature_by'] ?></td>
		</tr>
		<tr>
			<th>REMARK</th>
			<td><textarea name="_remark" rows="4" style="width:100%" disabled><?php echo $column['turn_remark'] ?></textarea></td>
		</tr>
	</table><br />
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td align="right">
			<button name="btnClose" class="input_sky" onClick="window.close();">Close <img src="../../_images/icon/close_mini.gif"></button>
			<?php if(isset($_GET['_cus_code']) && $_GET['_cus_code'] != '') { ?>
			<button name="btnList" class="input_sky" onClick="window.history.go(-1)">LIST</button>
			<?php } ?>
		</td>
	</tr>
</table>
<!--END Button-->
</body>
</html>