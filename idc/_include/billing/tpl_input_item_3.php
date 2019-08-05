<strong>[<font color="#446FBE">CUSTOMER</font>] SALES REPORT</strong> &nbsp; <small class="comment"><i><a href="javascript:fillItem()">( search <img src="../../_images/icon/search_mini.gif"> )</a></i></small>
<table width="100%" class="table_l" border="1">
	<thead>
		<tr>
			<th width="13%">SALES DATE</th>
			<th width="8%">CUS. CODE</th>
			<th>COSTUMER</th>
			<th width="13%">FAKTUR/<br />BILL NO</th>
			<th width="13%">LOP NO</th>
			<th width="12%">AMOUNT (+VAT)<br />(Rp)</th>
			<th width="5%">DEL</th>
		</tr>
	</thead>
	<tbody id="salesPosition">
	</tbody>
</table>
<table width="100%" class="table_box" border="1">
	<tr>
		<th align="right" colspan="5">TOTAL</th>
		<th width="12%"><input name="totalSales" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="5%"> &nbsp; </th>
	</tr>
</table><br />
<strong>[<font color="#446FBE">CUSTOMER</font>] ITEM LIST</strong>
<table width="100%" class="table_box">
	<thead>
		<tr>
			<th width="5%">CODE</th>
			<th width="13%">ITEM NO</th>
			<th width="32%">DESCRIPTION</th>
			<th width="12%">UNIT PRICE</th>
			<th width="7%">QTY</th>
			<th width="12%">AMOUNT(Rp)</th>
			<th width="14%">REMARK</th>
			<th width="1%"></th>
		</tr>
	</thead>
	<tbody id="billPosition">
	</tbody>
</table>
<table width="100%" class="table_box">
	<tr>
		<th align="right">SUB TOTAL</th>
		<th width="7%"><input name="totalQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="12%"><input name="total" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="15%" colspan="2">&nbsp;</th>
	</tr>
	<tr>
		<th align="right">DISC %</th>
		<th><input name="_disc" type="text" class="reqn" style="width:100%" value="0" onBlur="updateAmount()"></th>
		<th><input name="totalDisc" type="text" class="reqn" style="width:100%" readonly></th>
		<th colspan="2">&nbsp;</th>
	</tr>
	<tr>
		<th colspan="2" align="right">Before Vat</th>
		<th><input name="total2" type="text" class="reqn" style="width:100%" readonly></th>
		<th colspan="2">&nbsp;</th>
	</tr>
	<tr>
		<th colspan="2" align="right">VAT</th>
		<th><input name="totalVat" type="text" class="reqn" style="width:100%" readonly></th>
		<th colspan="2">&nbsp;</th>
	</tr>
	<tr>
		<th colspan="2" align="right">Delivery Cost</th>
		<th><input name="totalDelivery" type="text" class="reqn" style="width:100%" readonly></th>
		<th colspan="2">&nbsp;</th>
	</tr>
	<tr>
		<th colspan="2" align="right">GRAND TOTAL</th>
		<th><input name="totalAmount" type="text" class="reqn" style="width:100%" readonly></th>
		<th colspan="2">&nbsp;</th>
	</tr>
</table><br />
<script language="javascript" type="text/javascript">
//Open window for search item
var wSearchItem;
function fillItem(){
	var f = window.document.frmInsert;
	var x = (screen.availWidth - 750) / 2;
	var y = (screen.availHeight - 620) / 2;
	wSearchItem = window.open('./p_list_sales.php?_cug_code='+f._cug_code.value,'wSearchItem',
		'scrollbars,width=750,height=620,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wSearchItem.focus();
}

function createItem() {

	var f2 = wSearchItem.document.frmCreateItem;
	var start = window.salesPosition.rows.length;

	for(var i=0; i<f2.length; i+=7) {
		var oTR = window.document.createElement("TR");
		var oTD = new Array();
		var oTextbox = new Array();

		for(var j=0; j<7; j++) {
			oTD[j] = window.document.createElement("TD");
			oTextbox[j] = window.document.createElement("INPUT");
			oTextbox[j].type = "text";

			var id = ''; for(var k=0; k<6; k++) { id = id +'|||'+ f2.elements[i+k].value; }

			//Check duplicate sales
			var count = salesPosition.rows.length;
			for (var k=0; k<count; k++) {
				var oRow = window.salesPosition.rows(k);
				if(oRow.id == id) {
					alert("Same sales log already exist");
					return false;
				}
			}

			switch (j) {
				case 0:	oTextbox[j].type 	= "hidden";	
						oTD[j].innerText	= f2.elements[i].value;
						oTD[j].align		= "center";
						oTextbox[j].name	= "_sl_date[]";
						oTextbox[j].value	= f2.elements[i].value;
						break;
				case 1:	oTextbox[j].type 	= "hidden";	
						oTD[j].innerText	= f2.elements[i+1].value;
						oTD[j].align		= "center";
						oTextbox[j].name	= "_sl_cus_code[]";
						oTextbox[j].value	= f2.elements[i+1].value;
						break;
				case 2:	oTextbox[j].type 	= "hidden";	
						oTD[j].innerText	= f2.elements[i+2].value;
						oTextbox[j].name	= "_sl_cus_name[]";
						oTextbox[j].value	= f2.elements[i+2].value;
						break;
				case 3:	oTextbox[j].type 	= "hidden";	
						oTD[j].innerText	= f2.elements[i+3].value;
						oTextbox[j].name	= "_sl_faktur_no[]";
						oTextbox[j].value	= f2.elements[i+3].value;
						break;
				case 4:	oTextbox[j].type 	= "hidden";	
						oTD[j].innerText	= f2.elements[i+4].value;
						oTextbox[j].name	= "_sl_lop_no[]";
						oTextbox[j].value	= f2.elements[i+4].value;
						break;
				case 5:	oTextbox[j].name		= "_sl_amount[]";
						oTextbox[j].className	= "fmtn";
						oTextbox[j].readOnly	= "readOnly";
						oTextbox[j].value		= numFormatval(f2.elements[i+5].value+'',0);
						oTextbox[j].style.width	= "100%";
						break;
				case 6:	oTextbox[j].type 	= "hidden";	
						oTD[j].innerHTML	= "<a href=\"javascript:deleteSales('" + id + "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
						oTD[j].align		= "center";
						break;
			}
			if (j!=6) oTD[j].appendChild(oTextbox[j]);
			oTR.id = id; oTR.appendChild(oTD[j]);
		}
		window.salesPosition.appendChild(oTR);
	}
	checkSalesIdx(true, '', start);

}

function createItemII(idx) {

	var oTR = window.document.createElement("TR");
	var oTD = new Array();
	var oTextbox = new Array();
	var isListed = false;

	for (var i=0; i<8; i++) {
		oTD[i] = window.document.createElement("TD");
		oTextbox[i] = window.document.createElement("INPUT");
		oTextbox[i].type = "text";

		switch (i) {
			case 0:	oTextbox[i].name		= "_cus_it_code[]";
					oTD[i].innerText		= sl[idx][5];
					oTextbox[i].type 		= "hidden";	
					oTextbox[i].value		= sl[idx][5];
					break;
			case 1:	oTextbox[i].name		= "_cus_it_model_no[]";
					oTextbox[i].className	= "fmt";
					oTextbox[i].readOnly	= "readOnly";
					oTextbox[i].value		= sl[idx][6];
					oTextbox[i].style.width	= "100%";
					break;
			case 2:	oTextbox[i].name		= "_cus_it_desc[]";
					oTextbox[i].className	= "fmt";
					oTextbox[i].readOnly	= "readOnly";
					oTextbox[i].value		= sl[idx][7];
					oTextbox[i].style.width	= "100%";
					break;
			case 3:	oTextbox[i].name		= "_cus_it_unit_price[]";
					oTextbox[i].className	= "fmtn";
					oTextbox[i].readOnly	= "readOnly";
					oTextbox[i].value		= numFormatval(sl[idx][9]+'',0);
					oTextbox[i].style.width	= "100%";
					break;
			case 4:	oTextbox[i].name		= "_cus_it_qty[]";
					oTextbox[i].className	= "fmtn";
					oTextbox[i].readOnly	= "readOnly";
					oTextbox[i].value		= numFormatval(sl[idx][10]+'',0);
					oTextbox[i].style.width	= "100%";
					break;
			case 5:	oTextbox[i].name		= "_cus_it_amount[]";
					var amount = sl[idx][9]*sl[idx][10];
					oTextbox[i].className	= "fmtn";
					oTextbox[i].readOnly	= "readOnly";
					oTextbox[i].value		= numFormatval(amount+'',0);
					oTextbox[i].style.width	= "100%";
					break;
			case 6:	oTextbox[i].name		= "_cus_it_remark[]";
					oTextbox[i].className	= 'fmt';
					oTextbox[i].style.width	= "100%";
					break;
			case 7:	oTextbox[i].name		= "_cus_it_sl_idx[]";
					oTextbox[i].type 		= "hidden";	
					oTextbox[i].value		= sl[idx][8];
					break;
		}
		oTD[i].appendChild(oTextbox[i]);
		oTR.id = sl[idx][5]+numFormatval(sl[idx][9]+'',0);
		oTR.appendChild(oTD[i]);
	}
	window.billPosition.appendChild(oTR);
	updateAmount();

}

function updateQtyBill(indicator, code, price, sl_idx) {

	var f			= window.document.frmInsert;
	var e 			= window.document.frmInsert.elements;
	var countI		= window.salesPosition.rows.length;
	var countII		= window.billPosition.rows.length;

	var numInputI	= 6;	/////
	var numInputII	= 8;	/////
	var idx_price	= 38+(numInputI*countI);	/////
	var idx_qty		= idx_price+1;
	var idx_sl_idx	= idx_price+4;

	var count	 = billPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.billPosition.rows(i);
		if(oRow.id == code+price) {
			var int1 = parseInt(e(idx_qty+i*numInputII).value);
			var int2 = parseInt(sl[sl_idx][10]);
			var sl_log = e(idx_sl_idx+i*numInputII).value;

			if(indicator) {
				e(idx_qty+i*numInputII).value	 = int1 + int2;
				e(idx_sl_idx+i*numInputII).value = sl_log +', '+ sl[sl_idx][8];
			} else {
				e(idx_qty+i*numInputII).value	 = int1 - int2;
				sl_log_array  = sl_log.split(', ');
				sl_log = '';
				for(var j=0; j<sl_log_array.length; j++) {
					if(sl_log_array[j] != sl[sl_idx][8]) {
						sl_log  = sl_log + ', ' + sl_log_array[j];
					}
				}
				e(idx_sl_idx+i*numInputII).value = sl_log;
			}
		}
	}
	updateAmount();

}

function checkSalesIdx(indicator, idx, start) {
	var countI	= salesPosition.rows.length;
	var countII	= billPosition.rows.length;
	var countSl	= sl.length;
	var idxSl	= new Array();

	if(indicator) {		//------------------------------------------------------------------- when item inserted
		var k = 0;
		for (var i=start; i<countI; i++) {
			var oRowI = window.salesPosition.rows(i);
				var col = new Array;
				col[0] = trim(oRowI.cells(0).innerText);	// date
				col[1] = trim(oRowI.cells(1).innerText);	// cus code
				col[2] = trim(oRowI.cells(3).innerText);	// faktur no
				col[3] = trim(oRowI.cells(4).innerText);	// LOP no

			for (var j=0; j<countSl; j++) {
				if(col[0]==sl[j][4] && col[1]==sl[j][2] && col[2]==sl[j][12] && col[3]==sl[j][13]) {
					idxSl[k] = sl[j][15]; k++;
				}
			}
		}

		for (var i=0; i<idxSl.length; i++) {
			//Check is item with this price already exist
			var count	 = billPosition.rows.length;
			var isListed = false;
			var code	 = sl[idxSl[i]][5];
			var price	 = numFormatval(sl[idxSl[i]][9]+'',0);
			for (var j=0; j<count; j++) {
				var oRow = window.billPosition.rows(j);
				if(oRow.id == code+price) {
					isListed = true;
				}
			}

			if(isListed) { updateQtyBill(indicator, code, price, idxSl[i]); }
			else { createItemII(idxSl[i]); }
		}
	} else {					//------------------------------------------------------------------- when item deleted
		var j = 0;
		var col = new Array;
		col[0] = trim(idx[1]);	// date
		col[1] = trim(idx[2]);	// cus code
		col[2] = trim(idx[4]);	// faktur no
		col[3] = trim(idx[5]);	// LOP no

		for (var i=0; i<countSl; i++) {
			if(col[0]==sl[i][4] && col[1]==sl[i][2] && col[2]==sl[i][12] && col[3]==sl[i][13]) {
				idxSl[j] = sl[i][15]; j++;
			}
		}

		for (var i=0; i<idxSl.length; i++) {
			var code	 = sl[idxSl[i]][5];
			var price	 = numFormatval(sl[idxSl[i]][9]+'',0);
			updateQtyBill(indicator, code, price, idxSl[i]);
		}
		deleteBill();

	}
}

function deleteSales(idx) {
	var count = window.salesPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.salesPosition.rows(i);
		if (idx == oRow.id) {
			var n = window.salesPosition.removeChild(oRow);
			count = count - 1;

			var string = idx;
			var array  = string.split('|||');
			checkSalesIdx(false, array, 0);
			break;
		}
	}
	updateAmount();
}

function deleteBill() {
	var f			= window.document.frmInsert;
	var e 			= window.document.frmInsert.elements;
	var countI		= window.salesPosition.rows.length;
	var countII		= window.billPosition.rows.length;
	var sumofTotalI	= 0;
	var sumofTotalII= 0;
	var sumofQty	= 0;

	var numInputI	= 6;	/////
	var numInputII	= 8;	/////
	var idx_price	= 38+(numInputI*countI);	/////
	var idx_qty		= idx_price+1;
	var idx_amount	= idx_price+2;

	for (var i=0; i<countII; i++) {
		var oRow = window.billPosition.rows(i);
		if (e(idx_qty+i*numInputII).value <= 0) {
			var n = window.billPosition.removeChild(oRow);
			countII = countII - 1;
			i = i - 1;
		}
	}
	updateAmount();
}

function updateAmount() {
	var f			= window.document.frmInsert;
	var e 			= window.document.frmInsert.elements;
	var countI		= window.salesPosition.rows.length;
	var countII		= window.billPosition.rows.length;
	var sumofTotalI	= 0;
	var sumofTotalII= 0;
	var sumofQty	= 0;

	var numInputI	= 6;	/////
	var numInputII	= 8;	/////
	var idx_sl_amount = 39; /////
	var idx_price	= idx_sl_amount-1+(numInputI*countI);
	var idx_qty		= idx_price+1;
	var idx_amount	= idx_price+2;

	for (var i=0; i<countI; i++) {
		var sl_amount = parseFloat(removecomma(e(idx_sl_amount+i*numInputI).value));
		sumofTotalI	= sumofTotalI + sl_amount;
	}

	for (var i=0; i<countII; i++) {
		var price = parseFloat(removecomma(e(idx_price+i*numInputII).value));
		var qty	  = parseFloat(removecomma(e(idx_qty+i*numInputII).value));

		e(idx_amount+i*numInputII).value = numFormatval((price*qty)+'',0);

		sumofQty	+= qty;
		sumofTotalII+= price*qty;
	}

	var totalAfterDisc = sumofTotalII;
	var total_disc	  = 0;
	var vat			  = 0;
	var delivery_cost = 0;

	if(f._disc.value > 0) {
		total_disc = Math.round(sumofTotalII * f._disc.value/100);
		totalAfterDisc = sumofTotalII - total_disc;
	}

	if (f._vat_val.value != '') {
		vat = f._vat_val.value;
	}

	if (f._delivery_freight_charge.value != '') {
		delivery_cost = parseFloat(removecomma(f._delivery_freight_charge.value));
	}

	vat = Math.round(parseFloat(vat) / 100 * totalAfterDisc);
	var totalAmount	= totalAfterDisc + vat + delivery_cost;

	f.totalSales.value	  = numFormatval(sumofTotalI+'',0);
	f.totalQty.value	  = numFormatval(sumofQty+'',0);
	f.total.value		  = numFormatval(sumofTotalII+'',0);
	f.total2.value		  = numFormatval(totalAfterDisc.toString(), 0);
	f.totalVat.value	  = numFormatval(vat.toString(), 0);
	f.totalDelivery.value = numFormatval(delivery_cost + '', 0);
	f.totalDisc.value	  = numFormatval(total_disc + '', 0);
	f.totalAmount.value   = numFormatval(totalAmount + '', 0);

}
</script>