<strong class="info">[<font color="#315c87">CUSTOMER</font>] ITEM LIST</strong> &nbsp; <small class="comment"><i><a href="javascript:fillItem()">( search <img src="../../_images/icon/search_mini.gif"> )</a></i></small>
<table width="100%" class="table_box">
	<thead>
		<tr>
			<th width="5%">CODE</th>
			<th width="13%">ITEM NO</th>
			<th width="28%">DESCRIPTION</th>
			<th width="10%">UNIT PRICE<br />(Rp)</th>
			<th width="5%">QTY</th>
			<th width="12%">AMOUNT<br />(Rp)</th>
			<th width="12%">REMARK</th>
		</tr>
	</thead>
	<tbody id="itemCusPosition">
<?php while($items =& fetchRow($cusitem_res)) { ?>
		<tr id="<?php echo trim($items[0])?>">
			<td><input type="hidden" name="_cus_it_code[]" value="<?php echo $items[0]?>"><?php echo $items[0]?></td>
			<td><input type="text" class="fmt" name="_cus_it_model_no[]" value="<?php echo $items[1]?>" style="width:100%" readonly></td>
			<td><input type="text" class="fmt" name="_cus_it_desc[]" value="<?php echo $items[2]?>" style="width:100%" readonly></td>
			<td><input type="text" class="reqn" name="_cus_it_unit_price[]" value="<?php echo number_format((double)$items[3])?>" style="width:100%" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')" readonly></td>
			<td><input type="text" class="reqn" name="_cus_it_qty[]" value="<?php echo $items[4]?>" style="width:100%" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')" readonly></td>
			<td><input type="text" class="reqn" name="_cus_it_amount[]" value="<?php echo number_format((double)$items[5])?>" style="width:100%" readonly></td>
			<td><input type="text" class="fmt" name="_cus_it_remark[]" value="<?php echo $items[6]?>" style="width:100%"></td>
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
		<th width="12%">&nbsp;</th>
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
//Open window for search item
var wSearchItem;
function fillItem(){
	var x = (screen.availWidth - 580) / 2;
	var y = (screen.availHeight - 620) / 2;
	wSearchItem = window.open(
		'./p_list_item.php?_cus_code=<?php echo $column['turn_cus_to'] ?>','wSearchItem',
		'scrollbars,width=550,height=640,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wSearchItem.focus();
}

//Please see the p_list_item.php
function createItem() {

	var f = window.document.frmInsert;
	var f2 = wSearchItem.document.frmCreateItem;
	var oTR = window.document.createElement("TR");
	var oTD = new Array();
	var oTextbox = new Array();

	for (var i=0; i<9; i++) {
		oTD[i] = window.document.createElement("TD");
		oTextbox[i] = window.document.createElement("INPUT");
		oTextbox[i].type = "text";

		switch (i) {
			case 0: // ITEM CODE
				oTD[i].innerText		= f2.elements[0].value;
				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_cus_it_code[]";
				oTextbox[i].value		= f2.elements[0].value;
				break;

			case 1: // MODEL NO
				oTextbox[i].style.width	= "100%";
				oTextbox[i].readOnly	= "readonly";
				oTextbox[i].className 	= "fmt";
				oTextbox[i].name		= "_cus_it_model_no[]";
				oTextbox[i].value		= f2.elements[3].value;
				break;

			case 2: // DESCRIPTION
				oTextbox[i].style.width	= "100%";
				oTextbox[i].readOnly	= "readonly";
				oTextbox[i].className	= "fmt";
				oTextbox[i].name		= "_cus_it_desc[]";
				oTextbox[i].value		= f2.elements[4].value;
				break;

			case 3: //UNIT PRICE
				if(f._dept.value == 'A') {
					var group_disc_pct	= parseFloat(removecomma(window.frmInsert._basic_disc_ptc.value));
					var user_price		= parseFloat(removecomma(f2.elements[5].value));
					var price			= Math.round((user_price - (user_price*group_disc_pct/100))/1.1);
				} else {
					var price			= parseFloat(removecomma(f2.elements[5].value));
				}

				oTD[i].align			= "right";
				oTextbox[i].style.width	= "100%";
				oTextbox[i].className	= "reqn";
				oTextbox[i].name		= "_cus_it_unit_price[]";
				oTextbox[i].value		= numFormatval(price+'',0);
				oTextbox[i].onblur		= function() {updateAmount();}
				oTextbox[i].onkeyup		= function() {formatNumber(this, 'dot');}
				break;

			case 4: // QTY
				oTD[i].align			= "right";
				oTextbox[i].style.width	= "100%";
				oTextbox[i].className	= "reqn";
				oTextbox[i].name		= "_cus_it_qty[]";
				oTextbox[i].value		= removecomma(f2.elements[6].value);
				oTextbox[i].onblur		= function() {updateAmount();}
				oTextbox[i].onkeyup		= function() {formatNumber(this, 'dot');}
				break;

			case 5: //AMOUNT
				var amount = parseFloat(removecomma(oTextbox[3].value)) * parseInt(removecomma(oTextbox[4].value));

				oTD[i].align			= "right";
				oTextbox[i].readOnly	= "readonly";
				oTextbox[i].name		= "_cus_it_amount[]";
				oTextbox[i].style.width	= "100%";
				oTextbox[i].className	= "fmtn";
				oTextbox[i].value		= numFormatval(amount+'',0);
				break;

			case 6: // REMARK
				oTextbox[i].style.width	= "100%";
				oTextbox[i].className	= "fmt";
				oTextbox[i].name		= "_cus_it_remark[]";
				oTextbox[i].value		= f2.elements[7].value;
				break;

			case 7: // DELETE
				oTD[i].innerHTML		= "<a href=\"javascript:deleteItem('" + trim(f2.elements[0].value) + "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD[i].align			= "center";
				break;

			case 8: // _cus_it_icat_midx
				oTextbox[i].type 		= "hidden";
				oTextbox[i].name		= "_cus_it_icat_midx[]";
				oTextbox[i].value		= f2.elements[1].value;
				break;
		}

		if (i!= 7) oTD[i].appendChild(oTextbox[i]);
		oTR.id = trim(f2.elements[0].value);
		oTR.appendChild(oTD[i]);

	}
	window.itemCusPosition.appendChild(oTR);

	//Reset pop form
	for (var i=0; i<8; i++) {f2.elements[i].value = '';}
	updateAmount();
}

function deleteItem(idx) {

	//Delete Row
	var count = window.itemCusPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.itemCusPosition.rows(i);
		if (oRow.id == idx) {
			var n = window.itemCusPosition.removeChild(oRow);
			count = count - 1; //decrease loop - 1
		}
	}
	updateAmount();
}

function updateAmount(){

	var f			= window.document.frmInsert;
	var e 			= window.document.frmInsert.elements;
	var numItem		= window.itemCusPosition.rows.length; // number of Item
	var numInput	= 7;
	var idx_price	= 39;	/////
	var idx_qty		= idx_price+1;
	var idx_amount	= idx_price+2;
	var sumOfQty	= 0;
	var sumOfTotal	= 0;

	for (var i = 0; i< numItem; i++) {
		var price = parseFloat(removecomma(e(idx_price+i*numInput).value));
		var qty = parseFloat(removecomma(e(idx_qty+i*numInput).value));

		e(idx_amount+i*numInput).value = numFormatval((price*qty)+'',0);

		sumOfQty	+= qty;
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