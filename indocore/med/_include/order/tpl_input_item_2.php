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
			<th width="10%">DELIVERY</th>
			<th width="10%">REMARK</th>
			<th width="5%">DEL</th>
		</tr>
	</thead>
	<tbody id="itemCusPosition">
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
		<th width="25%">&nbsp;</th>
	</tr>
	<tr>
		<th colspan="3" align="right">VAT</th>
		<th width="12%"><input name="totalVat" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="25%">&nbsp;</th>
	</tr>
	<tr>
		<th colspan="3" align="right">GRAND TOTAL</th>
		<th width="12%"><input name="totalAmount" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="25%">&nbsp;</th>
	</tr>
</table><br />
<script language="javascript" type="text/javascript">
var wSearchItem;
function fillItem(){
	var x = (screen.availWidth - 550) / 2;
	var y = (screen.availHeight - 640) / 2;

	wSearchItem = window.open("./p_list_item_3.php?_cus_code=<?php echo $_cus_to ?>&_cus_name=<?php echo $_cus_to_attn?>",'wSearchItem',
		'scrollbars,width=550,height=640,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wSearchItem.focus();
}

function createItem(o) {

	var f2		= wSearchItem.document.frmCreateItem;
	var oTR 	= window.document.createElement("TR");
	var oTD		= new Array();
	var oTextbox= new Array();

	var count = itemCusPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.itemCusPosition.rows(i);
		if (trim(oRow.cells(0).innerText) == trim(f2.elements(0).value)) {
			alert("Same Item code already exist!");
			return false;
		}
	}

	var po_date = validDate(window.document.frmInsert._po_date);
	var deli_date = validDate(f2.elements(5));

	if (po_date.getTime() > deli_date.getTime()) {
		alert("Delivery must be future date than PO DATE");
		return false;
	}

	for (var i=0; i<9; i++) {
		oTD[i] = window.document.createElement("TD");
		oTextbox[i] = window.document.createElement("INPUT");
		oTextbox[i].type = "text";

		switch (i) {
			case 0: // _cus_it_code
				oTD[i].innerText			= f2.elements[0].value;
				oTextbox[i].type			= "hidden";
				oTextbox[i].name			= "_cus_it_code[]";
				oTextbox[i].value			= f2.elements[0].value;
				break;

			case 1: // _cus_it_model_no
				oTextbox[i].style.width		= "100%";
				oTextbox[i].readOnly		= "readonly";
				oTextbox[i].className		= "fmt";
				oTextbox[i].name			= "_cus_it_model_no[]";
				oTextbox[i].value			= f2.elements[1].value;
				break;

			case 2: // _cus_it_desc
				oTextbox[i].style.width		= "100%";
				oTextbox[i].readOnly		= "readonly";
				oTextbox[i].className		= "fmt";
				oTextbox[i].name			= "_cus_it_desc[]";
				oTextbox[i].value			= f2.elements[2].value;
				break;

			case 3: // _cus_it_unit_price
				var group_disc_pct			= parseFloat(window.frmInsert._basic_disc_ptc.value);
				var user_price				= parseFloat(f2.elements[3].value);
				var apotik_price			= Math.round((user_price - (user_price*group_disc_pct/100))/1.1);

				oTD[i].align				= "right";
				oTextbox[i].style.width		= "100%";
				oTextbox[i].className		= "reqn";
				oTextbox[i].name			= "_cus_it_unit_price[]";
				oTextbox[i].value			= numFormatval(apotik_price+'',0);
				oTextbox[i].onblur			= function() {updateAmount();}
				oTextbox[i].onkeyup			= function() {formatNumber(this, 'dot');}
				break;

			case 4: // _cus_it_qty
				oTD[i].align				= "right";
				oTextbox[i].style.width		= "100%";
				oTextbox[i].className 		= "reqn";
				oTextbox[i].name			= "_cus_it_qty[]";
				oTextbox[i].value			= numFormatval(f2.elements[4].value+'',0);
				oTextbox[i].onblur			= function() {updateAmount();}
				oTextbox[i].onkeyup			= function() {formatNumber(this, 'dot');}
				break;

			case 5: // _cus_it_amount
				var amount = parseFloat(removecomma(oTextbox[3].value)) * parseInt(removecomma(oTextbox[4].value));

				oTD[i].align				= "right";
				oTextbox[i].readOnly 		= "readonly";
				oTextbox[i].name			= "_cus_it_amount[]";
				oTextbox[i].style.width		= "100%";
				oTextbox[i].className		= "reqn";
				oTextbox[i].value			= numFormatval(amount+'',0);
				break;

			case 6: // _cus_it_delivery
				oTextbox[i].style.width 	= "100%";
				oTextbox[i].name			= "_cus_it_delivery[]";
				oTextbox[i].className 		= "fmtd";
				oTextbox[i].value			= f2.elements[5].value;
				oTD[i].align				= "center";
				break;

			case 7: // _cus_it_remark
				oTextbox[i].style.width 	= "100%";
				oTextbox[i].name			= "_cus_it_remark[]";
				oTextbox[i].className 		= "fmt";
				oTextbox[i].value			= f2.elements[6].value;
				oTD[i].align				= "center";
				break;

			case 8: // DELETE
				oTD[i].innerHTML			= "<a href=\"javascript:deleteItem('" + trim(f2.elements[0].value) + "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD[i].align				= "center";
				break;
		}

		if (i!= 8) oTD[i].appendChild(oTextbox[i]);
		oTR.id = trim(f2.elements[0].value);
		oTR.appendChild(oTD[i]);

	}
	window.itemCusPosition.appendChild(oTR);
	for (var i=0; i<6; i++) {
		if(i != 5) {f2.elements[i].value = '';}
	}
	updateAmount();
}

function deleteItem(idx) {

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

	var e			= window.document.frmInsert.elements;
	var numItem		= window.itemCusPosition.rows.length;
	var numInput	= 8;
	var idx_price	= 21;		/////
	var idx_qty		= idx_price+1;
	var idx_amount	= idx_price+2;

	var sumOfQty	= 0;
	var sumOfTotal	= 0;

	for (var i = 0; i< numItem; i++) {
		var price	= parseFloat(removecomma(e(idx_price+i*numInput).value));
		var qty		= parseFloat(removecomma(e(idx_qty+i*numInput).value));

		e(idx_amount+i*numInput).value = numFormatval((price*qty)+'',0);

		sumOfQty	+= qty;
		sumOfTotal	+= price*qty;
	}

	var vat = parseFloat(window.document.frmInsert._vat.value) / 100 * sumOfTotal;

	window.document.frmInsert.totalQty.value	= addcomma(sumOfQty);
	window.document.frmInsert.total.value		= numFormatval(sumOfTotal.toString(), 2);
	window.document.frmInsert.totalVat.value	= numFormatval(vat.toString(), 2);
	window.document.frmInsert.totalAmount.value = numFormatval(sumOfTotal + vat + '',2);
}
</script>