<?php
//[WAREHOUSE] billing item
$book_idx = ($column["book_idx"]=='')?0:$column["book_idx"];
$whitem_sql = "
SELECT
  a.it_code,			--0
  a.icat_midx,			--1
  a.it_model_no,		--2
  a.it_type,			--3
  a.it_desc,			--4
  b.boit_it_code_for,			--5
  b.boit_qty,			--6
  b.boit_function,		--7
  b.boit_remark, 		--8
  b.boit_type			--9
FROM
  ".ZKP_SQL."_tb_booking_item AS b
  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE b.book_idx = '$book_idx'
ORDER BY a.it_code,b.boit_idx";
$whitem_res	=& query($whitem_sql);
?>
<table width="100%" class="table_nn">
	<tr>
		<td height="35"><img src="../../_images/icon/star.gif">&nbsp;&nbsp;<strong>WAREHOUSE INFORMATION</strong></td>
		<td align="right">
			<i><?php echo "Confirm by : ". ucfirst($column["bill_cfm_wh_delivery_by_account"]) . date(', j-M-Y g:i:s', strtotime($column["bill_cfm_wh_delivery_timestamp"]))?></i>
		</td>
	</tr>
</table><br />
<strong>[<font color="#446FBE">WAREHOUSE</font>] ITEM LIST</strong>
<table width="100%" class="table_l">
	<thead>
		<tr>
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
<?php while($items =& fetchRow($whitem_res)) { ?>
		<tr id="<?php echo trim($items[0])?>">
			<td><?php echo $items[0]?></td>
			<td><?php echo $items[5]?></td>
			<td><?php echo $items[2]?></td>
			<td><?php echo $items[4]?></td>
			<td align="right"><?php echo number_format((double)$items[6],2)?></td>
			<td align="right"><?php echo $items[7]?></td>
			<td><?php echo $items[8]?></td>
		</tr>
<?php } ?>
	</tbody>
</table>
<table width="100%" class="table_box">
	<tr>
		<th align="right">TOTAL QTY</th>
		<th width="7%"><input name="totalWhQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="27%">&nbsp;</th>
	</tr>
</table><br />
<strong>[<font color="#446FBE">CUSTOMER</font>] ITEM LIST</strong>
<table width="100%" class="table_l">
	<thead>
		<tr>
			<th width="5%">CODE</th>
			<th width="13%">ITEM NO</th>
			<th width="28%">DESCRIPTION</th>
			<th width="10%">UNIT PRICE<br />(Rp)</th>
			<th width="7%">QTY</th>
			<th width="12%">AMOUNT<br />(Rp)</th>
			<th width="15%">REMARK</th>
		</tr>
	</thead>
	<tbody id="itemCusPosition">
<?php while($items =& fetchRow($cusitem_res)) { ?>
		<tr id="<?php echo trim($items[0])?>">
			<td><?php echo $items[0]?><input type="hidden" name="_cus_it_code[]" value="<?php echo $items[0]?>"></td>
			<td><input type="text" name="_cus_it_model_no[]" class="fmt" style="width:100%" value="<?php echo $items[2]?>" readOnly></td>
			<td><input type="text" name="_cus_it_desc[]" class="fmt" style="width:100%" value="<?php echo $items[4]?>" readOnly></td>
			<td align="right"><input type="text" name="_cus_it_unit_price[]" class="reqn" style="width:100%" value="<?php echo number_format((double)$items[5])?>" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')"<?php echo ($column['bill_cus_to']=='6IDC') ? " readOnly":"" ?>></td>
			<td align="right"><input type="text" name="_cus_it_qty[]" class="reqn" style="width:100%" value="<?php echo number_format((double)$items[6])?>" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')" readOnly></td>
			<td align="right"><input type="text" name="_cus_it_amount[]" class="reqn" style="width:100%" value="<?php echo number_format((double)$items[7])?>" readonly></td>
			<td><input type="text" name="_cus_it_remark[]" class="fmt" style="width:100%" value="<?php echo $items[8]?>"></td>
		</tr>
<?php } ?>
	</tbody>
</table>
<table width="100%" class="table_box">
	<tr>
		<th align="left">
		<?php if ($column["bill_dept"]=='A') { ?>
		BASIC GROUP DISC PRICE: <input name="_basic_disc_ptc" type="text" class="fmtn" size="2" maxlength="4" value="<?php echo $disc[0] ?>" readonly="readonly">%
		<?php } ?>
		</th>
		<th align="right">SUB TOTAL</th>
		<th width="7%"><input name="totalQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="12%"><input name="total" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="15%">&nbsp;</th>
	</tr>
	<tr>
		<th colspan="2" align="right">DISC %</th>
		<th><input name="_disc" type="text" class="reqn" style="width:100%" value="<?php echo $column["bill_discount"] ?>" onBlur="updateAmount()"></th>
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
		<th width="12%"><input name="totalVat" type="text" class="reqn" style="width:100%" readonly></th>
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
//Reculate Amount base on the form element
function updateAmount(){

	var f			= window.document.frmInsert;
	var e			= window.document.frmInsert.elements;
	var countWH		= window.itemWHPosition.rows.length;
	var countCus	= window.itemCusPosition.rows.length;
	var numInputCus	= 7;

	var idx_price	= 45;	/////
	var idx_qty		= idx_price+1;
	var idx_amount	= idx_price+2;

	var sumOfQty1	= 0;
	var sumOfQty2	= 0;
	var sumOfTotal	= 0;

	for (var i=0; i<countWH; i++) {
		var oRow	= window.itemWHPosition.rows(i);
		sumOfQty1	+= parseFloat(oRow.cells(4).innerText);		
	}

	for (var i=0; i<countCus; i++) {

		var price = parseFloat(removecomma(e(idx_price+i*numInputCus).value));
		var qty	  = parseFloat(removecomma(e(idx_qty+i*numInputCus).value));

		e(idx_amount+i*numInputCus).value = numFormatval((price*qty)+'',0);

		sumOfQty2	+= qty;
		sumOfTotal	+= price*qty;
	}

	var totalAfterDisc = sumOfTotal;
	var total_disc	  = 0;
	var vat			  = 0;
	var delivery_cost = 0;

	if(f._disc.value > 0) {
		total_disc = Math.round(sumOfTotal * f._disc.value/100);
		totalAfterDisc = sumOfTotal - total_disc;
	}

	if (f._vat_val.value != '') {
		vat = f._vat_val.value;
	}

	if (f._delivery_freight_charge.value != '') {
		delivery_cost = parseFloat(removecomma(f._delivery_freight_charge.value));
	}

	vat = Math.round(parseFloat(vat) / 100 * totalAfterDisc);
	var totalAmount	= totalAfterDisc + vat + delivery_cost;

	f.totalWhQty.value	  = numFormatval(sumOfQty1 + '', 2);
	f.totalQty.value	  = addcomma(sumOfQty2);
	f.total.value		  = numFormatval(sumOfTotal.toString(), 0);
	f.total2.value		  = numFormatval(totalAfterDisc.toString(), 0);
	f.totalVat.value	  = numFormatval(vat.toString(), 0);
	f.totalDelivery.value = numFormatval(delivery_cost + '', 0);
	f.totalDisc.value	  = numFormatval(total_disc + '', 0);
	f.totalAmount.value   = numFormatval(totalAmount + '', 0);

}
</script>