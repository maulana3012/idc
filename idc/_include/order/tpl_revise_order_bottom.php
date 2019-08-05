<strong class="info">CONDITION</strong>
<table class="table_box" width="100%">
	<tr>
		<th rowspan="2" width="12%">PRICE</th>
		<td>DISCOUNT: <input name="_price_discount" type="text" class="fmtn" value="<?php echo $column['ord_price_discount'];?>" size="2" maxlength="4">
		%</td>
		<td>FROM: <input type="checkbox" name="_price_chk[]" value="1" <?php echo ($column['ord_price_chk'] & 1)? "checked":""?>>Dealer 1's</td>
		<td><input type="checkbox" name="_price_chk[]" value="2" <?php echo ($column['ord_price_chk'] & 2)? "checked":""?>>Dealer 2's</td>
		<td><input type="checkbox" name="_price_chk[]" value="4" <?php echo ($column['ord_price_chk'] & 4)? "checked":""?>>Retailer's</td>
	</tr>
	<tr>
		<td colspan="3">&nbsp;</td>
		<td><input type="checkbox" name="_price_chk[]" value="8" <?php echo ($column['ord_price_chk'] & 8)? "checked":""?>>Consumer's</td>
	</tr>
	<tr>
		<th width="12%">DELIVERY</th>
		<td>1.<input type="checkbox" name="_delivery_chk[]" value="1" <?php echo ($column['ord_delivery_chk'] & 1)? "checked":""?>>ex W/house(P/C/D)</td>
		<td>2.<input type="checkbox" name="_delivery_chk[]" value="2" <?php echo ($column['ord_delivery_chk'] & 2)? "checked":""?>>Franco(P/D)</td>
		<td>by <input type="text" name="_delivery_by" value="<?php echo $column['ord_delivery_by']?>" size="6" class="fmt"></td>
		<td><input type="checkbox" name="_delivery_chk[]" value="4" <?php echo ($column['ord_delivery_chk'] & 4)? "checked":""?>>Freight charge:<input type="text" name="_delivery_freight_charge" value="<?php echo number_format((double)$column['ord_delivery_freight_charge'])?>" class="fmtn" onKeyUp="formatNumber(this,'dot')" size="8"></td>
	</tr>
	<tr>
		<th rowspan="4" width="12%">PAYMENT</th>
		<td>1.<input type="checkbox" name="_payment_chk[]" value="1" <?php echo ($column['ord_payment_chk'] & 1)? "checked":""?>>COD</td>
		<td>2.<input type="checkbox" name="_payment_chk[]" value="2" <?php echo ($column['ord_payment_chk'] & 2)? "checked":""?>>PREPAID</td>
		<td>3.<input type="checkbox" name="_payment_chk[]" value="4" <?php echo ($column['ord_payment_chk'] & 4)? "checked":""?>>Consignment</td>
		<td>4.<input type="checkbox" name="_payment_chk[]" value="8" <?php echo ($column['ord_payment_chk'] & 8)? "checked":""?>>Free/TO/LF/RP/PT</td>
	</tr>
	<tr>
		<td>5. Within 
		  <input name="_payment_widthin_days" type="text" class="fmtn" size="2" value="<?php echo $column['ord_payment_widthin_days']?>">
		days after</td>
		<td>5a. <input type="checkbox" name="_payment_chk[]" value="16" <?php echo ($column['ord_payment_chk'] & 16)? "checked":""?>>SJ/Inv/FP/Tender</td>
		<td>5b. Closing on 
		  <input name="_payment_closing_on" type="text" class="fmtd" size="10" value="<?php echo empty($column['ord_payment_closing_on']) ? "" : date("j-M-Y",strtotime($column['ord_payment_closing_on']))?>"></td>
		<td><input type="checkbox" name="_payment_chk[]" value="32" <?php echo ($column['ord_payment_chk'] & 32) ? "checked":""?>> 
		For the Month/Week(M/W)</td>
	</tr>
	<tr>
		<td>by 1)<input type="checkbox" name="_payment_chk[]" value="64" <?php echo ($column['ord_payment_chk'] & 64)? "checked":""?>>Cash</td>
		<td>2)<input type="checkbox" name="_payment_chk[]" value="128" <?php echo ($column['ord_payment_chk'] & 128)? "checked":""?>>Check</td>
		<td>3)<input type="checkbox" name="_payment_chk[]" value="256" <?php echo ($column['ord_payment_chk'] & 256)? "checked":""?>>Transfer</td>
		<td>4)<input type="checkbox" name="_payment_chk[]" value="512" <?php echo ($column['ord_payment_chk'] & 512)? "checked":""?>> 
		Giro
	</tr>
	<tr>
		<td>by<input name="_payment_cash_by" type="text" class="fmt" id="_payment_cash_by" value="<?php echo $column['ord_payment_cash_by']?>"></td>
		<td>by<input name="_payment_check_by" type="text" class="fmt" id="_payment_check_by" value="<?php echo $column['ord_payment_check_by']?>"></td>
		<td>by<input name="_payment_transfer_by" type="text" class="fmt" id="_payment_transfer_by" value="<?php echo $column['ord_payment_transfer_by']?>"></td>
		<td>by<input name="_payment_giro_by" type="text" class="fmt" id="_payment_giro_by" value="<?php echo $column['ord_payment_giro_by']?>"></td>
	</tr>
	<tr>
		<th>SIGN BY</th>
		<td colspan="4"><input name="_sign_by" type="text" class="req" maxlength="32" value="<?php echo $column['ord_sign_by']?>"></td>
	</tr>
	<tr>
		<th width="12%">REMARK</th>
		<td colspan="4"><textarea name="_remark" rows="3" style="width:98%"><?php echo $column['ord_remark']?></textarea></td>
	</tr>
</table>