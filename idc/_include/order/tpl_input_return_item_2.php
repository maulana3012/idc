<strong class="info">[<font color="#446FBE">CUSTOMER</font>] ITEM LIST</strong> &nbsp; <small class="comment"><i><a href="javascript:fillItem()">( search <img src="../../_images/icon/search_mini.gif"> )</a></i></small>
<table width="100%" class="table_box">
	<thead>
		<tr>
			<th width="6%">CODE</th>
			<th width="12%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="12%">APOTIK PRICE<br />(Rp)</th>
			<th width="5%">QTY</th>
			<th width="12%">AMOUNT<br />(Rp)</th>
			<th width="10%">REMARK</th>
			<th width="5%">DEL</th>
		</tr>
	</thead>
	<tbody id="itemCusPosition">
<?php
if($numRow > 0) {
	while($items =& fetchRow($cus_res)) {
?>
	<tr id="<?php echo trim($items[0])?>">
		<td><input type="hidden" name="_cus_it_code[]" value="<?php echo $items[0]?>"><?php echo $items[0]?></td>
		<td><input type="text" class="fmt" style="width:100%" name="_cus_it_model_no[]" value="<?php echo $items[1]?>" readonly></td>
		<td><input type="text" class="fmt" style="width:100%" name="_cus_it_desc[]" value="<?php echo $items[2]?>" readonly></td>
		<td><input type="text" class="reqn" style="width:100%" name="_cus_it_unit_price[]" value="<?php echo number_format((double)$items[3])?>" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')"></td>
		<td><input type="text" class="reqn" style="width:100%" name="_cus_it_qty[]" value="<?php echo number_format((double)$items[4])?>" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')"></td>
		<td><input type="text" class="reqn" style="width:100%" name="_cus_it_amount[]" value="<?php echo number_format((double)$items[5])?>" readonly></td>
		<td><input type="text" class="fmt" style="width:100%" name="_cus_it_remark[]" value="<?php echo $items[7]?>"></td>
		<td align="center"><a href="javascript:deleteItem('<?php echo trim($items[0])?>')"><img src='../../_images/icon/delete.gif' width='12px'></a></td>
	</tr>
<?php
	}
}
?>
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
		<th width="15%">&nbsp;</th>
	</tr>
	<tr>
		<th colspan="3" align="right">VAT</th>
		<th><input name="totalVat" type="text" class="reqn" style="width:100%" readonly></th>
		<th></th>
	</tr>
	<tr>
		<th colspan="3" align="right">GRAND TOTAL</th>
		<th><input name="totalAmount" type="text" class="reqn" style="width:100%" readonly></th>
		<th></th>
	</tr>
</table><br />
<script language="javascript" type="text/javascript">
var wSearchItem;
function fillItem(){
	var x = (screen.availWidth - 550) / 2;
	var y = (screen.availHeight - 640) / 2;
	wSearchItem = window.open('./p_list_item_return.php?_cus_code=<?php echo $_cus_to;?>','wSearchItem',
		'scrollbars,width=550,height=640,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wSearchItem.focus();
}

function createItem(o) {

	var f2		= wSearchItem.document.frmCreateItem;
	var oTR		= window.document.createElement("TR");
	var oTD		= new Array();
	var oTextbox= new Array();

	//Check has same CODE
	var count = itemCusPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.itemCusPosition.rows(i);
		if (trim(oRow.cells(0).innerText) == trim(f2.elements(0).value)) {
			alert("Same Item code already exist!");
			return false;
		}
	}

	for (var i=0; i<8; i++) {
		oTD[i] = window.document.createElement("TD");
		oTextbox[i] = window.document.createElement("INPUT");
		oTextbox[i].type = "text";

		switch (i) {
			case 0: // CODE
				oTD[i].innerText		= f2.elements[0].value;
				oTextbox[i].type		= "hidden";
				oTextbox[i].name		= "_cus_it_code[]";
				oTextbox[i].value		= f2.elements[0].value;
				break;

			case 1: // ITEM NO
				oTextbox[i].style.width	= "100%";
				oTextbox[i].readOnly	= "readonly";
				oTextbox[i].className	= "fmt";
				oTextbox[i].name		= "_cus_it_model_no[]";
				oTextbox[i].value		= f2.elements[1].value;
				break;

			case 2: // DESCRIPTION
				oTextbox[i].style.width = "100%";
				oTextbox[i].className	= "fmt";
				oTextbox[i].name		= "_cus_it_desc[]";
				oTextbox[i].value		= f2.elements[2].value;
				break;

			case 3: // APOTIK PRICE
				var group_disc_pct		= parseFloat(window.frmInsert._basic_disc_ptc.value);
				var user_price			= parseFloat(f2.elements[3].value);
				var apotik_price		= Math.round((user_price - (user_price*group_disc_pct/100))/1.1);

				oTD[i].align = "right";
				oTextbox[i].style.width = "100%";
				oTextbox[i].className	= "reqn";
				oTextbox[i].name		= "_cus_it_unit_price[]";
				oTextbox[i].value		= numFormatval(apotik_price+'',0);
				oTextbox[i].onblur		= function() {updateAmount();}
				oTextbox[i].onkeyup		= function() {formatNumber(this, 'dot');}
				break;

			case 4: // QTY
				oTD[i].align			= "right";
				oTextbox[i].style.width = "100%";
				oTextbox[i].className	= "reqn";
				oTextbox[i].name		= "_cus_it_qty[]";
				oTextbox[i].value		= removecomma(f2.elements[4].value);
				oTextbox[i].onblur		= function() {updateAmount();}
				oTextbox[i].onkeyup		= function() {formatNumber(this, 'dot');}
				break;

			case 5: //AMOUNT
				var amount = parseFloat(removecomma(oTextbox[3].value)) * parseInt(removecomma(oTextbox[4].value));

				oTD[i].align			= "right";
				oTextbox[i].readOnly	= "readonly";
				oTextbox[i].name		= "_cus_it_amount[]";
				oTextbox[i].style.width = "100%";
				oTextbox[i].className	= "reqn";
				oTextbox[i].value		= numFormatval(amount+'',0);
				break;

			case 6: // REMARK
				oTextbox[i].style.width	= "100%";
				oTextbox[i].className	= "fmt";
				oTextbox[i].name		= "_cus_it_remark[]";
				oTextbox[i].value		= f2.elements[5].value;
				break;

			case 7: // DELETE
				oTD[i].innerHTML		= "<a href=\"javascript:deleteItem('" + trim(f2.elements[0].value) + "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD[i].align			= "center";
				break;
		}

		if (i!= 7) oTD[i].appendChild(oTextbox[i]);
		oTR.id = trim(f2.elements[0].value);
		oTR.appendChild(oTD[i]);

	}
	window.itemCusPosition.appendChild(oTR);

	for (var i=0; i<5; i++) {f2.elements[i].value = '';}
	updateAmount();
}

function deleteItem(idx) {

	if (window.itemCusPosition.rows.length == 1) {
		alert("You need to choose at least 1 item");
		return;
	}

	var count = window.itemCusPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.itemCusPosition.rows(i);
		if (oRow.id == idx) {
			var n = window.itemCusPosition.removeChild(oRow);
			count = count - 1;
		}
	}

	updateAmount();
}

function updateAmount(){

	var f			= window.document.frmInsert;
	var e 			= window.document.frmInsert.elements;
	var numItem 	= window.itemCusPosition.rows.length;
	var numInput = 7;

	var idx_price	= 25;		/////
	var idx_qty		= idx_price+1;
	var idx_amount	= idx_price+2;
	var sumOfQty	= 0;
	var sumOfTotal	= 0;

	for (var i = 0; i< numItem; i++) {
		var price = parseFloat(removecomma(e(idx_price+i*numInput).value));
		var qty = parseFloat(removecomma(e(idx_qty+i*numInput).value));

		e(idx_amount+i*numInput).value = numFormatval((price*qty)+'',0);

		sumOfQty += qty;
		sumOfTotal += price*qty;
	}

	var vat = parseFloat(f._vat.value) / 100 * sumOfTotal;

	f.totalQty.value	= addcomma(sumOfQty);
	f.total.value		= numFormatval(sumOfTotal.toString(), 2);
	f.totalVat.value	= numFormatval(vat.toString(), 2);
	f.totalAmount.value	= numFormatval(sumOfTotal + vat + '',2);
}
</script>