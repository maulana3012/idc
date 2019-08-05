<strong>[<font color="#446FBE">WAREHOUSE</font>] ITEM LIST</strong> &nbsp; <small class="comment"><i><a href="javascript:fillItem()">( search <img src="../../_images/icon/search_mini.gif"> )</a></i></small>
<table width="100%" class="table_box">
	<thead>
		<tr>
			<th width="7%">CODE</th>
			<th width="7%">FOR</th>
			<th width="15%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="7%">QTY</th>
			<th width="7%">(x)</th>
			<th width="15%">REMARK</th>
			<th width="5%" colspan="4">DEL</th>
		</tr>
	</thead>
	<tbody id="itemWHPosition">
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
<table width="100%" class="table_box">
	<thead>
		<tr>
			<th width="5%">CODE</th>
			<th width="13%">ITEM NO</th>
			<th width="28%">DESCRIPTION</th>
			<th width="10%">UNIT PRICE<br />(Rp)</th>
			<th width="7%">QTY</th>
			<th width="12%">AMOUNT<br />(Rp)</th>
			<th width="15%">REMARK</th>
			<th width="5%" colspan="3">DEL</th>
		</tr>
	</thead>
	<tbody id="itemCusPosition">
	</tbody>
</table>
<table width="100%" class="table_box">
	<tr>
		<th align="left">
		<?php if ($_dept=='A') { ?>
		BASIC GROUP DISC PRICE: <input name="_basic_disc_ptc" type="text" class="fmtn" size="2" maxlength="4" value="<?php echo empty($disc[0]) ? 0 : $disc[0] ?>" readonly="readonly">%
		<?php } ?>
		</th>
		<th align="right">SUB TOTAL</th>
		<th width="7%"><input name="totalQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="12%"><input name="total" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="20%">&nbsp;</th>
	</tr>
	<tr>
		<th colspan="2" align="right">DISC %</th>
		<th><input name="_disc" type="text" class="reqn" style="width:100%" value="0" onBlur="updateAmount()"></th>
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
//Open window for search item
var wSearchItem;
function fillItem(){
	var x = (screen.availWidth - 580) / 2;
	var y = (screen.availHeight - 350) / 2;

	wSearchItem = window.open("./p_list_item_1.php",'wSearchItem',
		'scrollbars,width=550,height=350,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wSearchItem.focus();
}

function createItem() {

	var f = window.document.frmInsert;
	var f2	  = wSearchItem.document.frmCreateItem;
	var oTR_1 = window.document.createElement("TR");
	var oTR_2 = window.document.createElement("TR");
	var oTD_1 = new Array();
	var oTD_2 = new Array();
	var oTextbox_1 = new Array();
	var oTextbox_2 = new Array();

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
				oTextbox_1[i].style.width	= "100%";
				oTextbox_1[i].className		= "fmt";
				oTextbox_1[i].readOnly		= "readOnly";
				oTextbox_1[i].name			= "_wh_it_model_no[]";
				oTextbox_1[i].value			= f2.elements[3].value;
				break;

			case 3: // _wh_it_desc
				oTextbox_1[i].style.width	= "100%";
				oTextbox_1[i].className		= "fmt";
				oTextbox_1[i].readOnly		= "readOnly";
				oTextbox_1[i].name			= "_wh_it_desc[]";
				oTextbox_1[i].value			= f2.elements[4].value;
				break;

			case 4: // _wh_it_qty
				oTextbox_1[i].style.width	= "100%";
				oTextbox_1[i].className		= "reqn";
				oTextbox_1[i].readOnly		= "readOnly";
				oTextbox_1[i].name			= "_wh_it_qty[]";
				oTextbox_1[i].value			= numFormatval(f2.elements[5].value+'',2);
				break;

			case 5: // _wh_it_function
				oTextbox_1[i].style.width	= "100%";
				oTextbox_1[i].className		= "fmtn";
				oTextbox_1[i].readOnly		= "readOnly";
				oTextbox_1[i].name			= "_wh_it_function[]";
				oTextbox_1[i].value			= numFormatval(f2.elements[6].value+'',2);
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
	else {var i = 18;}

	//Print cell for Customer
	for (var i=i; i<18; i++) {
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
				oTextbox_2[i].style.width	= "100%";
				oTextbox_2[i].className		= "fmt";
				oTextbox_2[i].readOnly		= "readOnly";
				oTextbox_2[i].name			= "_cus_it_model_no[]";
				oTextbox_2[i].value			= f2.elements[13].value;
				break;

			case 10: // _cus_it_desc
				oTextbox_2[i].style.width	= "100%";
				oTextbox_2[i].className		= "fmt";
				oTextbox_2[i].readOnly		= "readOnly";
				oTextbox_2[i].name			= "_cus_it_desc[]";
				oTextbox_2[i].value			= f2.elements[14].value;
				break;

			case 11: // _cus_it_unit_price
				if(trim(f._cus_to.value) == '6IDC') {
					var price = Math.round(removecomma(f2.elements[16].value)/1.1);
				} else if(f._dept.value == 'A') {
					var group_disc_pct = parseFloat(removecomma(window.frmInsert._basic_disc_ptc.value));
					var user_price = parseFloat(removecomma(f2.elements[16].value));
					var price = Math.round((user_price - (user_price*group_disc_pct/100))/1.1);
				} else {
					var price = parseInt(removecomma(f2.elements[16].value));
				}

				if(trim(window.document.frmInsert._cus_to.value) == '6IDC')
					oTextbox_2[i].readOnly = true;

				oTD_2[i].align				= "right";
				oTextbox_2[i].style.width	= "100%";
				oTextbox_2[i].className		= "fmtn";
				oTextbox_2[i].name			= "_cus_it_unit_price[]";
				oTextbox_2[i].value			= numFormatval(price+'',0);
				oTextbox_2[i].onblur		= function() {updateAmount();}
				oTextbox_2[i].onkeyup		= function() {formatNumber(this, 'dot');}
				break;

			case 12: // _cus_it_qty
				oTD_2[i].align				= "right";
				oTextbox_2[i].style.width	= "100%";
				oTextbox_2[i].className		= "reqn";
				oTextbox_2[i].name			= "_cus_it_qty[]";
				oTextbox_2[i].readOnly		= "readOnly";
				oTextbox_2[i].value			= numFormatval(removecomma(f2.elements[15].value)+'',0);
				oTextbox_2[i].onblur		= function() {updateAmount();}
				oTextbox_2[i].onkeyup		= function() {formatNumber(this, 'dot');}
				break;

			case 13: // AMOUNT
				var amount = parseFloat(removecomma(f2.elements[15].value)) * parseInt(removecomma(f2.elements[16].value));

				oTD_2[i].align				= "right";
				oTextbox_2[i].readOnly		= "readonly";
				oTextbox_2[i].name			= "_cus_it_amount[]";
				oTextbox_2[i].style.width	= "100%";
				oTextbox_2[i].className		= "reqn";
				oTextbox_2[i].value			= numFormatval(amount+'',0);
				break;

			case 14: // _cus_it_remark
				oTextbox_2[i].style.width	= "100%";
				oTextbox_2[i].className		= "fmt";
				oTextbox_2[i].name			= "_cus_it_remark[]";
				oTextbox_2[i].value			= f2.elements[17].value;
				break;

			case 15: // DELETE
				oTD_2[i].innerHTML	= "<a href=\"javascript:deleteCusItem('" + trim(f2.elements[12].value) + "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD_2[i].align		= "center";
				break;

			case 16: // _cus_it_icat_midx
				oTextbox_2[i].type = "hidden";
				oTextbox_2[i].name = "_cus_it_icat_midx[]";
				oTextbox_2[i].value = f2.elements[10].value;
				break;

			case 17: // _cus_it_type
				oTextbox_2[i].type = "hidden";
				oTextbox_2[i].name = "_cus_it_type[]";
				oTextbox_2[i].value = f2.elements[11].value;
				break;
		}
		if (i!=15) oTD_2[i].appendChild(oTextbox_2[i]);
		oTR_2.id = trim(f2.elements[12].value);
		oTR_2.appendChild(oTD_2[i]);
	}
	if(f2.elements[9].checked==true) {window.itemCusPosition.appendChild(oTR_2);}
	updateAmount();
}

//Delete Item rows collection
function deleteWHItem(idx) {

	var count = window.itemWHPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.itemWHPosition.rows(i);
		if (oRow.id == idx) {
			var code_ref = trim(oRow.cells(1).innerText);
			var n = window.itemWHPosition.removeChild(oRow);
			count = count - 1;
			break;
		}
	}
	deleteCusItem(code_ref);
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


//Reculate Amount base on the form element
function updateAmount(){

	var f			= window.document.frmInsert;
	var e 			= window.document.frmInsert.elements;
	var countWH		= window.itemWHPosition.rows.length;
	var countCus	= window.itemCusPosition.rows.length;
	var numInputWH	= 7;		/////
	var numInputCus	= 9;		/////

	var idx_qty1	= 38;		/////
	var idx_price	= idx_qty1+(numInputWH*countWH);
	var idx_qty2	= idx_qty1+(numInputWH*countWH)+1;
	var idx_amount	= idx_qty1+(numInputWH*countWH)+2;

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