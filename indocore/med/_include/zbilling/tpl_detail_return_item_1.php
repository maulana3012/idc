<?php
//[WAREHOUSE] billing item
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
WHERE std_idx = {$column['std_idx']}
ORDER BY it_code,istd_idx";
$whitem_res	=& query($whitem_sql);
?>
<strong class="info">[<font color="#315c87">WAREHOUSE</font>] ITEM LIST</strong> &nbsp; <small class="comment"><i><a href="javascript:fillItem()">( search <img src="../../_images/icon/search_mini.gif"> )</a></i></small>
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
			<th width="5%">DEL</th>
		</tr>
	</thead>
	<tbody id="itemWHPosition">
<?php while($items =& fetchRow($whitem_res)) { ?>
		<tr id="<?php echo trim($items[0]).'-'.trim($items[1])?>">
			<td><input type="hidden" name="_wh_it_code[]" value="<?php echo trim($items[0])?>"><?php echo $items[0]?></td>
			<td><input type="hidden" name="_wh_it_code_for[]" value="<?php echo trim($items[1])?>"><?php echo $items[1]?></td>
			<td><input type="text" class="fmt" style="width:100%" name="_wh_it_model_no[]" value="<?php echo $items[2]?>" readonly></td>
			<td><input type="text" class="fmt" style="width:100%" name="_wh_it_desc[]" value="<?php echo $items[3]?>" readonly></td>
			<td><input type="text" class="reqn" style="width:100%" name="_wh_it_qty[]" value="<?php echo number_format((double)$items[4],2)?>" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')" readonly></td>
			<td><input type="text" class="fmtn" style="width:100%" name="_wh_it_function[]" value="<?php echo number_format((double)$items[5],2)?>" onKeyUp="formatNumber(this,'dot')"></td>
			<td><input type="text" class="fmt" style="width:100%" name="_wh_it_remark[]" value="<?php echo $items[6]?>" style="width:100%"></td>
			<td align="center"><a href="javascript:deleteWHItem('<?php echo trim($items[0]).'-'.trim($items[1])?>')"><img src='../../_images/icon/delete.gif' width='12px'></a></td>
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
<strong class="info">[<font color="#315c87">CUSTOMER</font>] ITEM LIST</strong>
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
			<th width="5%" colspan="2">DEL</th>
		</tr>
	</thead>
	<tbody id="itemCusPosition">
<?php while($items =& fetchRow($cusitem_res)) { ?>
		<tr id="<?php echo trim($items[0])?>">
			<td><input type="hidden" name="_cus_it_code[]" value="<?php echo $items[0]?>"><?php echo $items[0]?></td>
			<td><input type="text" class="fmt" name="_cus_it_model_no[]" value="<?php echo $items[1]?>" style="width:100%" readonly></td>
			<td><input type="text" class="fmt" name="_cus_it_desc[]" value="<?php echo $items[2]?>" style="width:100%" readonly></td>
			<td><input type="text" class="reqn" name="_cus_it_unit_price[]" value="<?php echo number_format((double)$items[3])?>" style="width:100%" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')"></td>
			<td><input type="text" class="reqn" name="_cus_it_qty[]" value="<?php echo $items[4]?>" style="width:100%" onBlur="updateAmount()" onKeyUp="formatNumber(this,'dot')" readonly></td>
			<td><input type="text" class="reqn" name="_cus_it_amount[]" value="<?php echo number_format((double)$items[5])?>" style="width:100%" readonly></td>
			<td><input type="text" class="fmt" name="_cus_it_remark[]" value="<?php echo $items[6]?>" style="width:100%"></td>
			<td align="center">
				<a href="javascript:deleteCusItem('<?php echo trim($items[0])?>')"><img src='../../_images/icon/delete.gif' width='12px'></a>
				<input type="hidden" name="_cus_it_icat_midx[]" value="<?php echo $items[8]?>">
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
//Open window for search item
var wSearchItem;
function fillItem(){
	var x = (screen.availWidth - 580) / 2;
	var y = (screen.availHeight - 620) / 2;
	wSearchItem = window.open(
		'./p_list_item_return_1.php','wSearchItem',
		'scrollbars,width=550,height=640,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wSearchItem.focus();
}

//Please see the p_list_item.php
function createItem() {

	var f	  = window.document.frmInsert;
	var f2	  = wSearchItem.document.frmCreateItem;
	var oTR_1 = window.document.createElement("TR");
	var oTR_2 = window.document.createElement("TR");
	var oTD_1 = new Array();
	var oTD_2 = new Array();
	var oTextbox_1 = new Array();
	var oTextbox_2 = new Array();

	if(f2.elements[8].checked==true) {var i = 0;}
	else {var i = 8;}

	//Print cell for WH
	for (var i=i; i<8; i++) {
		oTD_1[i] = window.document.createElement("TD");
		oTextbox_1[i] = window.document.createElement("INPUT");
		oTextbox_1[i].type = "text";

		switch (i) {
			case 0: // _wh_it_code
				oTD_1[i].innerText			= trim(f2.elements[0].value);
				oTextbox_1[i].type			= "hidden";
				oTextbox_1[i].name			= "_wh_it_code[]";
				oTextbox_1[i].value			= f2.elements[0].value;
				break;

			case 1: // _wh_it_code_for
				oTD_1[i].innerText			= trim(f2.elements[13].value);
				oTextbox_1[i].type			= "hidden";
				oTextbox_1[i].name			= "_wh_it_code_for[]";
				oTextbox_1[i].value			= f2.elements[13].value;
				break;

			case 2: // _wh_it_model_no
				oTextbox_1[i].style.width	= "100%";
				oTextbox_1[i].className		= "fmt";
				oTextbox_1[i].name			= "_wh_it_model_no[]";
				oTextbox_1[i].value			= f2.elements[3].value;
				oTextbox_1[i].readOnly		= 'readonly';
				break;

			case 3: // _wh_it_desc
				oTextbox_1[i].style.width	= "100%";
				oTextbox_1[i].className		= "fmt";
				oTextbox_1[i].name			= "_wh_it_desc[]";
				oTextbox_1[i].value			= f2.elements[4].value;
				oTextbox_1[i].readOnly		= 'readonly';
				break;

			case 4: // _wh_it_qty
				oTextbox_1[i].style.width	= "100%";
				oTextbox_1[i].className		= "reqn";
				oTextbox_1[i].name			= "_wh_it_qty[]";
				oTextbox_1[i].value			= numFormatval(f2.elements[5].value+'',2);
				oTextbox_1[i].onblur		= function() {updateAmount();}
				oTextbox_1[i].onkeyup		= function() {formatNumber(this, 'dot');}
				oTextbox_1[i].readOnly		= 'readonly';
				break;

			case 5: // _wh_it_function
				oTextbox_1[i].style.width	= "100%";
				oTextbox_1[i].className		= "fmtn";
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
				oTD_1[i].innerHTML			= "<a href=\"javascript:deleteWHItem('" + trim(f2.elements[0].value)+'-'+trim(f2.elements[13].value) + "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD_1[i].align				= "center";
				break;
		}

		if (i!=7) oTD_1[i].appendChild(oTextbox_1[i]);
		oTR_1.id = trim(f2.elements[0].value)+'-'+trim(f2.elements[13].value);
		oTR_1.appendChild(oTD_1[i]);
	}
	if(f2.elements[8].checked==true) {window.itemWHPosition.appendChild(oTR_1);}

	if(f2.elements[10].checked==true) {var i = 8;}
	else {var i = 17;}

	//Print cell for Customer
	for (var i=i; i<17; i++) {
		oTD_2[i] = window.document.createElement("TD");
		oTextbox_2[i] = window.document.createElement("INPUT");
		oTextbox_2[i].type = "text";

		switch (i) {
			case 8: // _cus_it_code
				oTD_2[i].innerText			= trim(f2.elements[13].value);
				oTextbox_2[i].type			= "hidden";
				oTextbox_2[i].name			= "_cus_it_code[]";
				oTextbox_2[i].value			= f2.elements[13].value;
				break;

			case 9: // _cus_it_model_no
				oTextbox_2[i].style.width	= "100%";
				oTextbox_2[i].className		= "fmt";
				oTextbox_2[i].name			= "_cus_it_model_no[]";
				oTextbox_2[i].value			= f2.elements[14].value;
				break;

			case 10: // _cus_it_desc
				oTextbox_2[i].style.width	= "100%";
				oTextbox_2[i].className		= "fmt";
				oTextbox_2[i].name			= "_cus_it_desc[]";
				oTextbox_2[i].value			= f2.elements[15].value;
				break;

			case 11: // _cus_it_unit_price
				if(f._dept.value == 'A') {
					var group_disc_pct	= parseFloat(removecomma(window.frmInsert._basic_disc_ptc.value));
					var user_price		= parseFloat(removecomma(f2.elements[17].value));
					var price			= Math.round((user_price - (user_price*group_disc_pct/100))/1.1);
				} else {
					var price			= parseFloat(removecomma(f2.elements[17].value));
				}

				oTD_2[i].align				= "right";
				oTextbox_2[i].style.width	= "100%";
				oTextbox_2[i].className		= "reqn";
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
				oTextbox_2[i].value			= numFormatval(removecomma(f2.elements[16].value)+'',0);
				oTextbox_2[i].onblur		= function() {updateAmount();}
				oTextbox_2[i].onkeyup		= function() {formatNumber(this, 'dot');}
				oTextbox_2[i].readOnly		= 'readonly';
				break;

			case 13: // AMOUNT
				var amount = parseFloat(removecomma(f2.elements[16].value)) * parseInt(removecomma(f2.elements[17].value));

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
				oTextbox_2[i].value			= f2.elements[18].value;
				break;

			case 15: // DELETE
				oTD_2[i].innerHTML			= "<a href=\"javascript:deleteCusItem('" + trim(f2.elements[13].value) + "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD_2[i].align				= "center";
				break;

			case 16: // _cus_it_icat_midx
				oTextbox_2[i].type 			= "hidden";
				oTextbox_2[i].name			= "_cus_it_icat_midx[]";
				oTextbox_2[i].value			= f2.elements[11].value;
				break;
		}
		if (i!=15) oTD_2[i].appendChild(oTextbox_2[i]);
		oTR_2.id = trim(f2.elements[13].value);
		oTR_2.appendChild(oTD_2[i]);
	}
	if(f2.elements[10].checked==true) {window.itemCusPosition.appendChild(oTR_2);}
	updateAmount();
}

//Delete Item rows collection
function deleteWHItem(idx) {

	var count = window.itemWHPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow	 = window.itemWHPosition.rows(i);
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
	var numInputWH	= 7;
	var numInputCus	= 8;

	var idx_qty1	= 40;		/////
	var idx_qty2	= idx_qty1+(numInputWH*countWH)+1;
	var idx_price	= idx_qty1+(numInputWH*countWH);
	var idx_amount	= idx_qty1+(numInputWH*countWH)+2;
	var sumOfQty1	= 0;
	var sumOfQty2	= 0;
	var sumOfTotal	= 0;

	for (var i=0; i<countWH; i++) {
		var qty 	= parseFloat(removecomma(e(idx_qty1+i*numInputWH).value));
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

	if (f._vat_value.value != '') {
		vat = f._vat_value.value;
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