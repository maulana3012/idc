<strong class="info">CONDITION</strong>
<table class="table_box" width="100%">
	<tr>
		<th rowspan="2" width="12%">PRICE</th>
		<td>DISCOUNT: <input name="_price_discount" type="text" class="fmtn" size="2" maxlength="4" value="0">
		%</td>
		<td>FROM: <input type="checkbox" name="_price_chk[]" value="1">Dealer 1's</td>
		<td><input type="checkbox" name="_price_chk[]" value="2">Dealer 2's</td>
		<td><input type="checkbox" name="_price_chk[]" value="4" checked>Retailer's</td>
	</tr>
	<tr>
		<td colspan="3">&nbsp;</td>
		<td><input type="checkbox" name="_price_chk[]" value="8">Consumer's</td>
	</tr>
	<tr>
		<th width="12%">DELIVERY</th>
		<td>1.<input type="checkbox" name="_delivery_chk[]" value="1">ex W/house(P/C/D)</td>
		<td>2.<input type="checkbox" name="_delivery_chk[]" value="2">Franco(P/D)</td>
		<td>by <input type="text" name="_delivery_by" value="Courier" size="6" class="fmt"></td>
		<td><input type="checkbox" name="_delivery_chk[]" value="4">Freight charge:<input type="text" name="_delivery_freight_charge" value="0" class="fmtn" onKeyUp="formatNumber(this,'dot')" size="8"></td>
	</tr>
	<tr>
		<th rowspan="4" width="12%">PAYMENT</th>
		<td>1.<input type="checkbox" name="_payment_chk[]" value="1">COD</td>
		<td>2.<input type="checkbox" name="_payment_chk[]" value="2">PREPAID</td>
		<td>3.<input type="checkbox" name="_payment_chk[]" value="4" checked="checked">Consignment</td>
		<td>4.<input type="checkbox" name="_payment_chk[]" value="8">Free/TO/LF/RP/PT</td>
	</tr>
	<tr>
		<td>5. Within 
		  <input name="_payment_widthin_days" type="text" class="fmtn" id="_payment_widthin_days" size="2" />
		days after</td>
		<td>5a. <input type="checkbox" name="_payment_chk[]" value="16" />SJ/Inv/FP/Tender</td>
		<td>5b. Closing on 
		  <input name="_payment_closing_on" type="text" class="fmtd" id="_payment_closing_on" size="10" /></td>
		<td><input type="checkbox" name="_payment_chk[]" value="32" /> 
		For the Month/Week(M/W)</td>
	</tr>
	<tr>
		<td>by 1)<input type="checkbox" name="_payment_chk[]" value="64" />Cash</td>
		<td>2)<input type="checkbox" name="_payment_chk[]" value="128" />Check</td>
		<td>3)<input type="checkbox" name="_payment_chk[]" value="256" />Transfer</td>
		<td>4)<input type="checkbox" name="_payment_chk[]" value="512" /> 
		Giro
	</tr>
	<tr>
		<td>by<input name="_payment_cash_by" type="text" class="fmt" id="_payment_cash_by" maxlength="18"></td>
		<td>by<input name="_payment_check_by" type="text" class="fmt" id="_payment_check_by" maxlength="18"></td>
		<td>by<input name="_payment_transfer_by" type="text" class="fmt" id="_payment_transfer_by" maxlength="18"></td>
		<td>by<input name="_payment_giro_by" type="text" class="fmt" id="_payment_giro_by" maxlength="18"></td>
	</tr>
	<tr>
		<th>SIGN BY</th>
		<td colspan="4"><input name="_sign_by" type="text" class="req" maxlength="32"></td>
	</tr>
	<tr>
		<th>REMARK</th>
		<td colspan="4"><textarea name="_remark" rows="3" style="width:98%" class="textarea_a"></textarea></td>
	</tr>
</table>