<table width="100%" class="table_nn">
	<tr>
		<td height="35"><img src="../../_images/icon/star.gif">&nbsp;&nbsp;<strong>WAREHOUSE INFORMATION</strong></td>
		<td align="right">
			<i><?php echo "Confirm by : ". ucfirst($column["ord_cfm_wh_delivery_by_account"]) . date(', j-M-Y g:i:s', strtotime($column["ord_cfm_wh_delivery_timestamp"]))?></i>
		</td>
	</tr>
</table><br />
<strong class="info">[<font color="#446FBE">WAREHOUSE</font>] ITEM LIST</strong>
<table width="100%" class="table_nn">
	<thead>
		<tr height="30px">
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
<?php while($items =& fetchRow($wh_res)) { ?>
		<tr id="<?php echo trim($items[0])?>">
			<td><?php echo $items[0]?><input type="hidden" name="_wh_it_code[]" value="<?php echo $items[0]?>"></td>
			<td><?php echo trim($items[5])?><input type="hidden" name="_wh_it_code_for[]" value="<?php echo $items[5]?>"></td>
			<td><?php echo $items[2]?><input type="hidden" name="_wh_it_model_no[]" value="<?php echo $items[2]?>"></td>
			<td><?php echo $items[4]?><input type="hidden" name="_wh_it_desc[]" value="<?php echo $items[4]?>"></td>
			<td align="right"><?php echo $items[6] ?><input type="hidden" name="_wh_it_qty[]" value="<?php echo $items[6]?>"></td>
			<td align="right"><?php echo $items[7] ?><input type="hidden" name="_wh_it_function[]" value="<?php echo $items[7]?>"></td>
			<td> &nbsp; <input type="hidden" name="_wh_it_remark[]" class="fmt" style="width:100%" value="<?php echo $items[8]?>"><?php echo $items[8]?></td>
		</tr>
<?php } ?>
	</tbody>
</table>
<table width="100%" class="table_box">
	<tr>
		<th align="right">TOTAL QTY</th>
		<th width="7%"><input name="totalWhQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="22%">&nbsp;</th>
</table>
<?php
if($book_idx==0) echo "\t<span class=\"comment\"><i>*Old record. Don't have booking history</i></span><br />";
?>
<br />
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
		<td> &nbsp; <input type="hidden" name="_cus_it_delivery[]" class="fmtd" style="width:100%" value="<?php echo $items[6]?>"><?php echo $items[6]?></td>
		<td> &nbsp; <input type="hidden" name="_cus_it_remark[]" class="fmt" style="width:100%" value="<?php echo $items[7]?>"><?php echo $items[7]?></td>
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
	var countWH		= window.itemWHPosition.rows.length;
	var countCus	= window.itemCusPosition.rows.length;
	var numInputWH	= 1;
	var numInputCus	= 3;

	var idx_qty1	= 23;
	var idx_price	= idx_qty1+(numInputWH*countWH)+1;
	var idx_qty2	= idx_qty1+(numInputWH*countWH)+2;
	var idx_amount	= idx_qty1+(numInputWH*countWH)+3;

	var sumOfQty1	= 0;
	var sumOfQty2	= 0;
	var sumOfTotal	= 0;

	for (var i=0; i<countWH; i++) {
		var oRow	= window.itemWHPosition.rows(i);
		sumOfQty1	= sumOfQty1 + parseFloat(removecomma(oRow.cells(4).innerText));
	}

	for (var i=0; i<countCus; i++) {
		var oRow	= window.itemCusPosition.rows(i);
		sumOfQty2	= sumOfQty2 + parseInt(oRow.cells(4).innerText);
		sumOfTotal	= sumOfTotal + parseFloat(removecomma(oRow.cells(5).innerText));
	}

	var vat = parseFloat(window.document.frmInsert._vat.value) / 100 * sumOfTotal;

	window.document.frmInsert.totalWhQty.value	= numFormatval(sumOfQty1+'',2);
	window.document.frmInsert.totalQty.value	= addcomma(sumOfQty2);
	window.document.frmInsert.total.value		= numFormatval(sumOfTotal.toString(), 2);
	window.document.frmInsert.totalVat.value	= numFormatval(vat.toString(), 2);
	window.document.frmInsert.totalAmount.value = numFormatval(sumOfTotal + vat + '',2);
}
</script>