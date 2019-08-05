<strong class="info">[<font color="#446FBE">CUSTOMER</font>] ITEM LIST</strong>
<table width="100%" class="table_nn">
	<thead>
		<tr>
			<th width="6%">CODE</th>
			<th width="12%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="12%">APOTIK PRICE<br />(Rp)</th>
			<th width="5%">QTY</th>
			<th width="12%">AMOUNT<br />(Rp)</th>
			<th width="10%">DELIVERY</th>
			<th width="10%">REMARK</th>
		</tr>
	</thead>
	<tbody id="itemCusPosition">
<?php while($items =& fetchRow($cus_res)) { ?>
	<tr id="<?php echo trim($items[0])?>">
		<td><input type="hidden" name="_cus_it_code[]" value="<?php echo $items[0]?>"><?php echo $items[0]?></td>
		<td><input type="hidden" name="_cus_it_model_no[]" value="<?php echo $items[1]?>"><?php echo $items[1]?></td>
		<td><input type="hidden" name="_cus_it_desc[]" value="<?php echo $items[2]?>"><?php echo $items[2]?></td>
		<td align="right"><input type="hidden" name="_cus_it_unit_price[]" value="<?php echo $items[3] ?>"><?php echo number_format((double)$items[3])?></td>
		<td align="right"><input type="hidden" name="_cus_it_qty[]" value="<?php echo $items[4] ?>"><?php echo number_format((double)$items[4])?></td>
		<td align="right"><input type="hidden" name="_cus_it_amount[]" value="<?php echo $items[5] ?>"><?php echo number_format((double)$items[5])?></td>
		<td> &nbsp; <input type="hidden" name="_cus_it_delivery[]" value="<?php echo $items[6]?>"><?php echo $items[6]?></td>
		<td> &nbsp; <input type="hidden" name="_cus_it_remark[]" value="<?php echo $items[7]?>"><?php echo $items[7]?></td>
	</tr>
<?php } ?>	
	</tbody>
</table>
<table width="100%" class="table_box">
	<tr>
		<th align="left">
			<?php if($currentDept == 'apotik') { ?> 
			BASIC GROUP DISC PRICE: <input name="_basic_disc_ptc" type="text" class="fmtn" size="2" maxlength="4" value="<?php echo $disc[0]?>" readonly="readonly">%
			<?php } else if($disc[0] > 0) { ?> 
			BASIC GROUP DISC PRICE: <input name="_basic_disc_ptc" type="text" class="fmtn" size="2" maxlength="4" value="<?php echo $disc[0]?>" readonly="readonly">%
			<?php } else { ?>
			<input name="_basic_disc_ptc" type="hidden" value="<?php echo $disc[0]?>">
			<?php } ?>
		</th>
		<th align="right">Before VAT</th>
		<th width="5%"><input name="totalQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="12%"><input name="total" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="20%"></th>
	</tr>
	<tr>
		<th colspan="3" align="right">VAT</th>
		<th width="12%"><input name="totalVat" type="text" class="reqn" style="width:100%" readonly></th>
		<th></th>
	</tr>
	<tr>
		<th colspan="3" align="right">GRAND TOTAL</th>
		<th width="12%"><input name="totalAmount" type="text" class="reqn" style="width:100%" readonly></th>
		<th></th>
	</tr>
</table><br />
<script language="javascript" type="text/javascript">
function updateAmount(){
	var f			= window.document.frmInsert;
	var e 			= window.document.frmInsert.elements;
	var countCus	= window.itemCusPosition.rows.length;
	var numInputCus	= 3;

	var idx_price	= 25;
	var idx_qty2	= idx_price+1;
	var idx_amount	= idx_price+2;

	var sumOfQty2	= 0;
	var sumOfTotal	= 0;

	for (var i=0; i<countCus; i++) {
		var oRow	= window.itemCusPosition.rows(i);
		sumOfQty2	= sumOfQty2 + parseInt(oRow.cells(4).innerText);
		sumOfTotal	= sumOfTotal + parseFloat(removecomma(oRow.cells(5).innerText));
	}

	var vat = parseFloat(window.document.frmInsert._vat.value) / 100 * sumOfTotal;

	window.document.frmInsert.totalQty.value	= addcomma(sumOfQty2);
	window.document.frmInsert.total.value		= numFormatval(sumOfTotal.toString(), 2);
	window.document.frmInsert.totalVat.value	= numFormatval(vat.toString(), 2);
	window.document.frmInsert.totalAmount.value = numFormatval(sumOfTotal + vat + '',2);
}
</script>