<?php
//[WAREHOUSE] return item
$std_idx	= ($column["std_idx"]=='')?0:$column["std_idx"];
$whitem_sql = "
SELECT
  a.it_code,
  b.istd_it_code_for,
  a.it_model_no,
  a.it_desc,
  b.istd_qty,
  b.istd_function,
  b.istd_remark
FROM ".ZKP_SQL."_tb_outstanding_item as b JOIN ".ZKP_SQL."_tb_item as a USING(it_code)
WHERE std_idx = $std_idx
ORDER BY it_code,std_idx";
$whitem_res	=& query($whitem_sql);
?>
<table width="100%" class="table_nn">
	<tr>
		<td height="35"><img src="../../_images/icon/star.gif">&nbsp;&nbsp;<strong>WAREHOUSE INFORMATION</strong></td>
		<td align="right">
			<i><?php echo "Confirm by : ". ucfirst($column["turn_cfm_wh_delivery_by_account"]) . date(', j-M-Y g:i:s', strtotime($column["turn_cfm_wh_delivery_timestamp"]))?></i>
		</td>
	</tr>
</table><br />
<strong class="info">[<font color="#315c87">WAREHOUSE</font>] ITEM LIST</strong>
<table width="100%" class="table_l">
	<thead>
		<tr height="25px">
			<th width="7%">CODE</th>
			<th width="7%">FOR</th>
			<th width="15%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="7%">QTY</th>
			<th width="7%">(x)</th>
			<th width="15%">REMARK</th>
		</tr>
	</thead>
	<tbody id="itemWHPosition">
<?php
$amount = 0;
while($items =& fetchRow($whitem_res)) {
?>
		<tr>
			<td><?php echo $items[0]?><input type="hidden" name="_wh_it_code[]" value="<?php echo $items[0] ?>"></td>
			<td><?php echo $items[1]?><input type="hidden" name="_wh_it_code_for[]" value="<?php echo $items[1] ?>"></td>
			<td><?php echo $items[2]?><input type="hidden" name="_wh_it_model_no[]" value="<?php echo $items[2] ?>"></td>
			<td><?php echo $items[3]?><input type="hidden" name="_wh_it_desc[]" value="<?php echo $items[3] ?>"></td>
			<td align="right"><?php echo number_format((double)$items[4],2)?><input type="hidden" name="_wh_it_qty[]" value="<?php echo $items[4] ?>"></td>
			<td align="right"><?php echo number_format((double)$items[5],2)?><input type="hidden" name="_wh_it_function[]" value="<?php echo $items[5] ?>"></td>
			<td><?php echo $items[6]?><input type="hidden" name="_wh_it_remark[]" value="<?php echo $items[6] ?>"></td>
		</tr>
<?php 
	$amount +=  $items[4];
}
?>
	</tbody>
</table>
<table width="100%" class="table_box">
	<tr>
		<th align="right">TOTAL QTY</th>
		<th width="7%"><input name="totalWhQty" type="text" class="reqn" style="width:100%" value="<?php echo number_format((double)$amount,2) ?>" readonly></th>
		<th width="21%">&nbsp;</th>
	</tr>
</table>
<?php
if($std_idx==0) echo "\t<span class=\"comment\"><i>*Old record. Don't have confirmation history</i></span><br /><br />";
?>
<br />
<strong class="info">[<font color="#315c87">CUSTOMER</font>] ITEM LIST</strong>
<table width="100%" class="table_l">
	<thead>
		<tr height="25px">
			<th width="5%">CODE</th>
			<th width="13%">ITEM NO</th>
			<th width="28%">DESCRIPTION</th>
			<th width="10%">UNIT PRICE<br />(Rp)</th>
			<th width="6%">QTY</th>
			<th width="12%">AMOUNT<br />(Rp)</th>
			<th width="12%">REMARK</th>
		</tr>
	</thead>
	<tbody id="itemCusPosition">
<?php while($items =& fetchRow($cusitem_res)) { ?>
		<tr>
			<td><?php echo $items[0]?><input type="hidden" name="_cus_it_code[]" value="<?php echo $items[0] ?>"></td>
			<td><?php echo $items[1]?><input type="hidden" name="_cus_it_model_no[]" value="<?php echo $items[1] ?>"></td>
			<td><?php echo $items[2]?><input type="hidden" name="_cus_it_desc[]" value="<?php echo $items[2] ?>"></td>
			<td align="right"><?php echo number_format((double)$items[3])?><input type="hidden" name="_cus_it_unit_price[]" value="<?php echo $items[3] ?>"></td>
			<td align="right"><?php echo $items[4]?><input type="hidden" name="_cus_it_qty[]" value="<?php echo $items[4] ?>"></td>
			<td align="right"><?php echo number_format((double)$items[5])?><input type="hidden" name="_cus_it_amount[]" value="<?php echo $items[5] ?>"></td>
			<td>
				<?php echo $items[6]?>
				<input type="hidden" name="_cus_it_remark[]" value="<?php echo $items[6] ?>">
				<input type="hidden" name="_cus_it_icat_midx[]" value="<?php echo $items[8] ?>">
			</td>
		</tr>
<?php } ?>
	</tbody>
</table>
<table width="100%" class="table_box">
	<tr>
		<th align="left">
		<?php if($column['turn_dept'] == 'A') {?>
		BASIC GROUP DISC PRICE: <input name="_basic_disc_ptc" type="text" class="fmtn" size="2" maxlength="4" value="<?php echo $disc[0]?>" readonly="readonly">%
		<?php } ?>
		</th>
		<th align="right">SUB TOTAL</th>
		<th width="7%"><input name="totalQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="13%"><input name="total" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="18%">&nbsp;</th>
	</tr>
	<tr>
		<th colspan="2" align="right">DISC %</th>
		<th><input name="_disc" type="text" class="reqn" style="width:100%" value="<?php echo $column["turn_discount"] ?>" onBlur="updateAmount()"></th>
		<th><input name="totalDisc" type="text" class="reqn" style="width:100%" readonly></th>
		<th></th>
	</tr>
	<tr>
		<th colspan="3" align="right">Before Vat</th>
		<th><input name="total2" type="text" class="reqn" style="width:100%" readonly></th>
		<th></th>
	</tr>
	<tr>
		<th colspan="3" align="right">VAT</th>
		<th><input name="totalVat" type="text" class="reqn" style="width:100%" readonly></th>
		<th></th>
	</tr>
	<tr>
		<th colspan="3" align="right">Delivery Cost</th>
		<th><input name="totalDelivery" type="text" class="reqn" style="width:100%" readonly></th>
		<th></th>
	</tr>
	<tr>
		<th colspan="3" align="right">GRAND TOTAL</th>
		<th><input name="totalAmount" type="text" class="reqn" style="width:100%" readonly></th>
		<th></th>
	</tr>
</table><br />
<script language="javascript" type="text/javascript">
function updateAmount(){

	var f			= window.document.all;
	var count 		= window.itemCusPosition.rows.length;
	var sumOfQty	= 0;
	var sumOfTotal	= 0;

	for (var i=0; i<count; i++) {
		var oRow 	= window.itemCusPosition.rows(i);
		sumOfQty 	= sumOfQty + parseInt(oRow.cells(4).innerText);
		sumOfTotal	= sumOfTotal + parseFloat(removecomma(oRow.cells(5).innerText));
	}

	var totalAfterDisc = sumOfTotal;
	var total_disc	  = 0;
	var vat			  = 0;
	var delivery_cost = 0;

	if(f._disc.value > 0) {
		total_disc = Math.round(sumOfTotal * f._disc.value/100);
		totalAfterDisc = sumOfTotal - total_disc;
	}

	if (f._vat_value.value != '') {
		vat = f._vat_value.value;
	}

	if (f._delivery_freight_charge.value != '') {
		delivery_cost = parseFloat(removecomma(f._delivery_freight_charge.value));
	}

	vat = Math.round(parseFloat(vat) / 100 * totalAfterDisc);
	var totalAmount	= totalAfterDisc + vat + delivery_cost;

	f.totalQty.value	  = addcomma(sumOfQty);
	f.total.value		  = numFormatval(sumOfTotal.toString(), 0);
	f.total2.value		  = numFormatval(totalAfterDisc.toString(), 0);
	f.totalVat.value	  = numFormatval(vat.toString(), 0);
	f.totalDelivery.value = numFormatval(delivery_cost + '', 0);
	f.totalDisc.value	  = numFormatval(total_disc + '', 0);
	f.totalAmount.value   = numFormatval(totalAmount + '', 0);
}
</script>