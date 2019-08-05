<strong class="info">CONDITION</strong>
<table class="table_box" width="100%">
	<tr>
		<th width="12%">DELIVERY</th>
		<td>1.<input type="text" name="_delivery_warehouse" class="fmt" size="2" maxlength="2" value="<?php echo $column['turn_delivery_warehouse']?>">ex W/house(P/C/D)</td>
		<td>2.<input type="text" name="_delivery_franco" class="fmt" size="2" maxlength="2" value="<?php echo $column['turn_delivery_franco']?>">Franco(P/D)</td>
		<td>by <input type="text" name="_delivery_by" value="<?php echo $column['turn_delivery_by']?>" size="6" class="fmt"></td>
		<td><input type="checkbox" name="_delivery_chk[]" value="1" <?php echo ($column['turn_delivery_chk'] & 1)? "checked":""?> disabled>Freight charge:<input type="text" name="_delivery_freight_charge" size="8" value="<?php echo ($column['turn_delivery_freight_charge'] <= 0) ? '' : number_format((double)$column['turn_delivery_freight_charge'])?>" class="fmtn" onKeyUp="formatNumber(this,'dot')" disabled></td>
	</tr>
	<tr>
		<th rowspan="4" width="12%">PAYMENT</th>
		<td>1.<input type="checkbox" name="_payment_chk[]" value="1" <?php echo ($column['turn_payment_chk'] & 1)? "checked":""?>>COD</td>
		<td>2.<input type="checkbox" name="_payment_chk[]" value="2" <?php echo ($column['turn_payment_chk'] & 2)? "checked":""?>>PREPAID</td>
		<td>3.<input type="checkbox" name="_payment_chk[]" value="4" <?php echo ($column['turn_payment_chk'] & 4)? "checked":""?>>Consignment</td>
		<td>4.<input type="checkbox" name="_payment_chk[]" value="8" <?php echo ($column['turn_payment_chk'] & 8)? "checked":""?>>Free/TO/LF/RP/PT</td>
	</tr>
	<tr>
		<td>5. Within 
		  <input name="_payment_widthin_days" type="text" class="fmtn" size="2" value="<?php echo $column['turn_payment_widthin_days']?>">
		days after</td>
		<td>5a.
			<select name="_payment_sj_inv_fp_tender" class="fmt">
				<option value=""></option>
				<option value="Invoice">INVOICE</option>
				<option value="Surat Jalan">SURAT JALAN</option>
				<option value="Tukar Faktur">TUKAR FAKTUR</option>
			</select>
		</td>
		<td>5b. Closing on <input name="_payment_closing_on" type="text" class="fmtd" size="10" value="<?php echo empty($column['turn_payment_closing_on']) ? "" : date("j-M-Y",strtotime($column['turn_payment_closing_on']))?>"></td>
		<td><input type="text" name="_payment_for_the_month_week" class="fmt" size="2" maxlength="2" value="<?php echo $column['turn_payment_for_the_month_week'] ?>">For the Month/Week(M/W)</td>
	</tr>
	<tr>
		<td>by 1)<input type="checkbox" name="_payment_chk[]" value="16" <?php echo ($column['turn_payment_chk'] & 16)? "checked":""?> onClick="enabledText(this, 'cash')">Cash</td>
		<td>2)<input type="checkbox" name="_payment_chk[]" value="32" <?php echo ($column['turn_payment_chk'] & 32)? "checked":""?> onClick="enabledText(this, 'check')">Check</td>
		<td>3)<input type="checkbox" name="_payment_chk[]" value="64" <?php echo ($column['turn_payment_chk'] & 64)? "checked":""?> onClick="enabledText(this, 'transfer')">Transfer</td>
		<td>4)<input type="checkbox" name="_payment_chk[]" value="128" <?php echo ($column['turn_payment_chk'] & 128)? "checked":""?>  onClick="enabledBankOption(this)">Giro</td>
	</tr>
	<tr>
		<td>by<input name="_payment_cash_by" type="text" class="fmt" id="_payment_cash_by" value="<?php echo $column['turn_payment_cash_by']?>"></td>
		<td>by<input name="_payment_check_by" type="text" class="fmt" id="_payment_check_by" value="<?php echo $column['turn_payment_check_by']?>"></td>
		<td>by<input name="_payment_transfer_by" type="text" class="fmt" id="_payment_transfer_by" value="<?php echo $column['turn_payment_transfer_by']?>"></td>
		<td>
			Issue : <input type="text" name="_payment_giro_issue" size="10" class="fmtd" value="<?php echo ($column['turn_payment_giro_issue'] != '') ? date("j-M-Y", strtotime($column['turn_payment_giro_issue'])) : ''?>">
			Due : <input type="text" name="_payment_giro_due" size="10" class="fmtd" value="<?php echo ($column['turn_payment_giro_due'] != '') ? date("j-M-Y", strtotime($column['turn_payment_giro_due'])) : ''?>">
		</td>
	</tr>
	<tr>
		<th>BANK</th>
		<td>
			<input type="radio" name="_bank" value="BCA1" id="bca1" <?php echo ($column['turn_payment_bank'] == 'BCA1') ? 'checked' : '' ?> onCLick=bankDesc(this) disabled><label for="bca1">BCA 1</label><br />
			<input type="radio" name="_bank" value="BCA2" id="bca2" <?php echo ($column['turn_payment_bank'] == 'BCA2') ? 'checked' : '' ?> onCLick=bankDesc(this) disabled><label for="bca2">BCA 2</label><br />
			<input type="radio" name="_bank" value="MANDIRI" id="mandiri" <?php echo ($column['turn_payment_bank'] == 'MANDIRI') ? 'checked' : '' ?> onCLick=bankDesc(this) disabled><label for="mandiri">Mandiri</label><br />
		</td>
		<td>
			<input type="radio" name="_bank" value="BII1" id="bii1" <?php echo ($column['turn_payment_bank'] == 'BII1') ? 'checked' : '' ?> onCLick=bankDesc(this) disabled><label for="bii1">BII 1</label><br />
			<input type="radio" name="_bank" value="BII2" id="bii2" <?php echo ($column['turn_payment_bank'] == 'BII2') ? 'checked' : '' ?> onCLick=bankDesc(this) disabled><label for="bii2">BII 2</label><br />
			<input type="radio" name="_bank" value="DANAMON" id="danamon" <?php echo ($column['turn_payment_bank'] == 'DANAMON') ? 'checked' : '' ?> onCLick=bankDesc(this) disabled><label for="danamon">Danamon</label>
		</td>
		<td colspan="2">
			<textarea name="_bank_address" rows="3" style="width:100%" readonly><?php echo $column['turn_payment_bank_address'] ?></textarea>
		</td>
	</tr>
	<tr>
		<th>DATE</th>
		<td colspan="2">Tukar Faktur : <input type="text" name="_tukar_faktur_date" class="fmt" value="<?php echo ($column['turn_tukar_faktur_date'] != '') ? date("j-M-Y", strtotime($column['turn_tukar_faktur_date'])) : ''?>" disabled></td>
	</tr>
</table><br />
<strong class="info">OTHERS</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="12%">SIGN BY</th>
		<td><input type="text" name="_signature_by" class="req" value="<?php echo $column['turn_signature_by'] ?>"></td>
	</tr>
	<tr>
		<th>REMARK</th>
		<td><textarea name="_remark" rows="4" style="width:100%"><?php echo $column['turn_remark'] ?></textarea></td>
	</tr>
</table>