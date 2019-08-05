<strong class="info">[<font color="#446FBE">WAREHOUSE</font>] ITEM LIST</strong> &nbsp; <small class="comment"><i><a href="javascript:fillItem()">( search <img src="../../_images/icon/search_mini.gif"> )</a></i></small>
<table width="100%" class="table_box">
	<thead>
		<tr height="30px">
			<th width="7%">CODE</th>
			<th width="7%">FOR</th>
			<th width="15%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="7%">QTY</th>
			<th width="7%">(x)</th>
			<th width="15%">REMARK</th>
			<th width="5%">DEL</th>
		</tr>
	</thead>
	<tbody id="itemWHPosition">
<?php while($items =& fetchRow($wh_res)) { ?>
		<tr id="<?php echo trim($items[0])?>">
			<td><?php echo $items[0]?><input type="hidden" name="_wh_it_code[]" value="<?php echo $items[0]?>"></td>
			<td><?php echo trim($items[5])?><input type="hidden" name="_wh_it_code_for[]" value="<?php echo $items[5]?>"></td>
			<td><?php echo $items[2]?><input type="hidden" name="_wh_it_model_no[]" value="<?php echo $items[2]?>"></td>
			<td><?php echo $items[4]?><input type="hidden" name="_wh_it_desc[]" value="<?php echo $items[4]?>"></td>
			<td align="right"><?php echo number_format((double)$items[6],2)?><input type="hidden" name="_wh_it_qty[]" value="<?php echo $items[6]?>"></td>
			<td align="right"><?php echo $items[7]?><input type="hidden" name="_wh_it_function[]" value="<?php echo $items[7]?>"></td>
			<td><input type="text" name="_wh_it_remark[]" class="fmt" style="width:100%" value="<?php echo $items[8]?>"></td>
			<td align="center">
				<a href="javascript:deleteWHItem('<?php echo trim($items[0])?>')"><img src="../../_images/icon/delete.gif" width="12px"></a>
			</td>
		</tr>
<?php } ?>
	</tbody>
</table>
<table width="100%" class="table_box">
	<tr>
		<th align="right">TOTAL QTY</th>
		<th width="7%"><input name="totalWhQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="27%">&nbsp;</th>
</table><br />
<strong class="info">[<font color="#446FBE">CUSTOMER</font>] ITEM LIST</strong>
<table width="100%" class="table_box">
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
			<th width="5%">DEL</th>
		</tr>
	</thead>
	<tbody id="itemCusPosition">
<?php while($items =& fetchRow($cus_res)) { ?>
	<tr id="<?php echo trim($items[0])?>">
		<td><input type="hidden" name="_cus_it_code[]" value="<?php echo $items[0]?>"><?php echo $items[0]?></td>
		<td><input type="hidden" name="_cus_it_model_no[]" value="<?php echo $items[1]?>"><?php echo $items[1]?></td>
		<td><input type="hidden" name="_cus_it_desc[]" value="<?php echo $items[2]?>"><?php echo $items[2]?></td>
		<td align="right"><input type="text" name="_cus_it_unit_price[]" value="<?php echo number_format((double)$items[3])?>" class="fmtn" style="width:100%" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')"></td>
		<td align="right"><input type="text" name="_cus_it_qty[]" value="<?php echo number_format((double)$items[4]) ?>" style="width:100%" class="reqn" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')" readonly></td>
		<td align="right"><input type="text" name="_cus_it_amount[]" value="<?php echo number_format((double)$items[5])?>" class="reqn" style="width:100%" readonly></td>
		<td><input type="text" name="_cus_it_delivery[]" class="fmtd" style="width:100%" value="<?php echo $items[6]?>"></td>
		<td><input type="text" name="_cus_it_remark[]" class="fmt" style="width:100%" value="<?php echo $items[7]?>"></td>
		<td align="center"><a href="javascript:deleteCusItem('<?php echo trim($items[0])?>')"><img src="../../_images/icon/delete.gif" width="12px"></a></td>
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
		<th width="25%"></th>
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
</table><br>
<script language="javascript" type="text/javascript">
//Open window for search item
var wSearchItem;
function fillItem(){
	var x = (screen.availWidth - 550) / 2;
	var y = (screen.availHeight - 350) / 2;

	wSearchItem = window.open("./p_list_item_1.php?_cus_code=<?php echo $column['ord_cus_to'] ?>",'wSearchItem',
		'scrollbars,width=550,height=350,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wSearchItem.focus();
}


//It Called by child window window's name is wSearchItem
//Please see the p_list_item.php
function createItem() {

	var f2	  = wSearchItem.document.frmCreateItem;
	var oTR_1 = window.document.createElement("TR");
	var oTR_2 = window.document.createElement("TR");
	var oTD_1 = new Array();
	var oTD_2 = new Array();
	var oTextbox_1 = new Array();
	var oTextbox_2 = new Array();

	var count = itemCusPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.itemCusPosition.rows(i);
		if (oRow.id == trim(f2.elements[12].value)) {
			alert("[" + trim(f2.elements[12].value) + "] " + f2.elements[13].value + " already exist in customer item list");
			return;
		}
	}

	//Print cell for WH
	for (var i=0; i<8; i++) {
		oTD_1[i] = window.document.createElement("TD");
		oTextbox_1[i] = window.document.createElement("INPUT");
		oTextbox_1[i].type = "text";

		switch (i) {
			case 0: // _wh_it_code
				oTD_1[i].innerText	= trim(f2.elements[0].value);
				oTextbox_1[i].type	= "hidden";
				oTextbox_1[i].name	= "_wh_it_code[]";
				oTextbox_1[i].value	= f2.elements[0].value;
				break;

			case 1: // _wh_it_code_for
				oTD_1[i].innerText	= trim(f2.elements[12].value);
				oTextbox_1[i].type	= "hidden";
				oTextbox_1[i].name	= "_wh_it_code_for[]";
				oTextbox_1[i].value	= f2.elements[12].value;
				break;

			case 2: // _wh_it_model_no
				oTD_1[i].innerText	= f2.elements[3].value;
				oTextbox_1[i].type	= "hidden";
				oTextbox_1[i].name	= "_wh_it_model_no[]";
				oTextbox_1[i].value	= f2.elements[3].value;
				break;

			case 3: // _wh_it_desc
				oTD_1[i].innerText	= f2.elements[4].value;
				oTextbox_1[i].type	= "hidden";
				oTextbox_1[i].name	= "_wh_it_desc[]";
				oTextbox_1[i].value	= f2.elements[4].value;
				break;

			case 4: // _wh_it_qty
				oTD_1[i].innerText	= numFormatval(f2.elements[5].value+'',2);
				oTD_1[i].align		= "right";
				oTextbox_1[i].type	= "hidden";
				oTextbox_1[i].name	= "_wh_it_qty[]";
				oTextbox_1[i].value	= parseFloat(f2.elements[5].value);
				break;

			case 5: // _wh_it_function
				oTD_1[i].innerText	= numFormatval(f2.elements[6].value+'',2);
				oTD_1[i].align		= "right";
				oTextbox_1[i].type	= "hidden";
				oTextbox_1[i].name	= "_wh_it_function[]";
				oTextbox_1[i].value	= numFormatval(f2.elements[6].value+'',2);
				break;

			case 6: // _wh_it_remark
				oTextbox_1[i].style.width	= "100%";
				oTextbox_1[i].className		= "fmt";
				oTextbox_1[i].name			= "_wh_it_remark[]";
				oTextbox_1[i].value			= f2.elements[7].value;
				break;

			case 7: // DELETE
				oTD_1[i].innerHTML	= "<a href=\"javascript:deleteWHItem('" + trim(f2.elements[0].value) + "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD_1[i].align		= "center";
				break;
		}

		if (i!=7) oTD_1[i].appendChild(oTextbox_1[i]);
		oTR_1.id = trim(f2.elements[0].value);
		oTR_1.appendChild(oTD_1[i]);
	}
	window.itemWHPosition.appendChild(oTR_1);

	if(f2.elements[9].checked==true) {var i = 8;}
	else {var i = 17;}

	//Print cell for Customer
	for (var i=8; i<17; i++) {
		oTD_2[i] = window.document.createElement("TD");
		oTextbox_2[i] = window.document.createElement("INPUT");
		oTextbox_2[i].type = "text";

		switch (i) {
			case 8: // _cus_it_code
				oTD_2[i].innerText	= trim(f2.elements[12].value);
				oTextbox_2[i].type	= "hidden";
				oTextbox_2[i].name	= "_cus_it_code[]";
				oTextbox_2[i].value	= f2.elements[12].value;
				break;

			case 9: // _cus_it_model_no
				oTD_2[i].innerText	= f2.elements[13].value;
				oTextbox_2[i].type	= "hidden";
				oTextbox_2[i].name	= "_cus_it_model_no[]";
				oTextbox_2[i].value	= f2.elements[13].value;
				break;

			case 10: // _cus_it_desc
				oTD_2[i].innerText	= f2.elements[14].value;
				oTextbox_2[i].type	= "hidden";
				oTextbox_2[i].name	= "_cus_it_desc[]";
				oTextbox_2[i].value	= f2.elements[14].value;
				break;

			case 11: // _cus_it_unit_price
				var group_disc_pct	= parseFloat(removecomma(window.frmInsert._basic_disc_ptc.value));
				var user_price		= parseFloat(removecomma(f2.elements[16].value));
				var apotik_price	= Math.round((user_price - (user_price*group_disc_pct/100))/1.1);

				oTD_2[i].align				= "right";
				oTextbox_2[i].style.width	= "100%";
				oTextbox_2[i].className		= "fmtn";
				oTextbox_2[i].name			= "_cus_it_unit_price[]";
				oTextbox_2[i].value			= numFormatval(apotik_price+'',0);
				oTextbox_2[i].onblur		= function() {updateAmount();}
				oTextbox_2[i].onkeyup		= function() {formatNumber(this, 'dot');}
				break;

			case 12: // _cus_it_qty
				oTextbox_2[i].style.width	= "100%";
				oTextbox_2[i].className		= "fmtn";
				oTextbox_2[i].name			= "_cus_it_qty[]";
				oTextbox_2[i].readOnly		= "readonly";
				oTextbox_2[i].value			= numFormatval(f2.elements[15].value+'',0);
				oTextbox_2[i].onblur		= function() {updateAmount();}
				oTextbox_2[i].onkeyup		= function() {formatNumber(this, 'dot');}
				break;

			case 13: // AMOUNT
				var amount = parseFloat(removecomma(f2.elements[15].value)) * parseInt(removecomma(oTextbox_2[11].value));

				oTD_2[i].align				= "right";
				oTextbox_2[i].readOnly		= "readonly";
				oTextbox_2[i].name			= "_cus_it_amount[]";
				oTextbox_2[i].style.width	= "100%";
				oTextbox_2[i].className		= "fmtn";
				oTextbox_2[i].value			= numFormatval(amount+'',0);
				break;

			case 14: // _cus_it_delivery
				oTextbox_2[i].style.width	= "100%";
				oTextbox_2[i].className		= "fmtd";
				oTextbox_2[i].name			= "_cus_it_delivery[]";
				oTextbox_2[i].value			= f2.elements[17].value;
				break;

			case 15: // _cus_it_remark
				oTextbox_2[i].style.width	= "100%";
				oTextbox_2[i].className		= "fmt";
				oTextbox_2[i].name			= "_cus_it_remark[]";
				oTextbox_2[i].value			= f2.elements[18].value;
				break;

			case 16: // DELETE
				oTD_2[i].innerHTML	= "<a href=\"javascript:deleteCusItem('" + trim(f2.elements[12].value) + "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD_2[i].align		= "center";
				break;
		}
		if (i!=16) oTD_2[i].appendChild(oTextbox_2[i]);
		oTR_2.id = trim(f2.elements[12].value);
		oTR_2.appendChild(oTD_2[i]);
	}
	if(f2.elements[9].checked==true) {window.itemCusPosition.appendChild(oTR_2);}
	updateAmount();
}

//Delete Item rows collection
function deleteWHItem(idx) {

	var count = window.itemWHPosition.rows.length;

	var cus_idx = '';
	for (var i=0; i<count; i++) {
		var oRow	= window.itemWHPosition.rows(i);
		if(trim(oRow.cells(0).innerText) == idx) {
			cus_idx = trim(oRow.cells(1).innerText);
		}
	}

	for (var i=0; i<count; i++) {
		var oRow = window.itemWHPosition.rows(i);
		if (oRow.id == idx) {
			var n = window.itemWHPosition.removeChild(oRow);
			count = count - 1;
			break;
		}
	}
	deleteCusItem(cus_idx);
	updateAmount();
}

function deleteCusItem(idx) {
	var count = window.itemCusPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.itemCusPosition.rows(i);
		if (oRow.id == idx) {
			var n = window.itemCusPosition.removeChild(oRow);
			count = count - 1;
			break;
		}
	}
	updateAmount();
}

function updateAmount(){
	var f			= window.document.frmInsert;
	var e 			= window.document.frmInsert.elements;
	var countWH		= window.itemWHPosition.rows.length;
	var countCus	= window.itemCusPosition.rows.length;
	var numInputWH	= 7;
	var numInputCus	= 8;

	var idx_qty1	= 26;			/////
	var idx_price	= idx_qty1+(numInputWH*countWH);
	var idx_qty2	= idx_qty1+(numInputWH*countWH)+1;
	var idx_amount	= idx_qty1+(numInputWH*countWH)+2;
//alert(e(26).name +' - '+ e(26).value);
	var sumOfQty1	= 0;
	var sumOfQty2	= 0;
	var sumOfTotal	= 0;

	for (var i=0; i<countWH; i++) {
		var qty = parseFloat(removecomma(e(idx_qty1+i*numInputWH).value));
		sumOfQty1	+= qty;
	}

	for (var i=0; i<countCus; i++) {
		var price = parseFloat(removecomma(e(idx_price+i*numInputCus).value));
		var qty	  = parseFloat(removecomma(e(idx_qty2+i*numInputCus).value));

		e(idx_amount+i*numInputCus).value = numFormatval((price*qty)+'',0);

		sumOfQty2	+= qty;
		sumOfTotal	+= price*qty;
	}

	var vat = parseFloat(window.document.frmInsert._vat.value) / 100 * sumOfTotal;

	window.document.frmInsert.totalWhQty.value	= numFormatval(sumOfQty1+'',2);
	window.document.frmInsert.totalQty.value	= addcomma(sumOfQty2);
	window.document.frmInsert.total.value		= numFormatval(sumOfTotal.toString(), 2);
	window.document.frmInsert.totalVat.value	= numFormatval(vat.toString(), 2);
	window.document.frmInsert.totalAmount.value = numFormatval(sumOfTotal + vat + '',2);
}
</script>