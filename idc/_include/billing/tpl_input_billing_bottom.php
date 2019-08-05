<strong>CONDITION</strong>
<table class="table_box" width="100%">
	<tr>
		<th width="12%">DELIVERY</th>
		<td>1.<input type="text" name="_delivery_warehouse" class="fmt" size="2" maxlength="2">ex W/house(P/C/D)</td>
		<td>2.<input type="text" name="_delivery_franco" class="fmt" size="2" maxlength="2" value="D">Franco(P/D)</td>
		<td>by <input type="text" name="_delivery_by" value="Courier" size="6" class="fmt"></td>
		<td><input type="checkbox" name="_delivery_chk[]" value="1" onClick="enabledText(this, 'freight_charge')">Freight charge:<input type="text" name="_delivery_freight_charge" class="fmtn" onKeyUp="formatNumber(this,'dot')" size="8" onBlur="updateAmount()" disabled></td>
	</tr>
	<tr>
		<th rowspan="4" width="12%">PAYMENT</th>
		<td>1.<input type="checkbox" name="_payment_chk[]" value="1" onclick=cod(this)>COD</td>
		<td>2.<input type="checkbox" name="_payment_chk[]" value="2">PREPAID</td>
		<td>3.<input type="checkbox" name="_payment_chk[]" value="4">Consignment</td>
		<td>4.<input type="checkbox" name="_payment_chk[]" value="8">Free/TO/LF/RP/PT</td>
	</tr>
	<tr>
		<td>5. Within 
		  <input name="_payment_widthin_days" type="text" class="fmtn" id="_payment_widthin_days" size="2" onBlur="dueDateValue()" />
		days after</td>
		<td>5a. 
			<select name="_payment_sj_inv_fp_tender" class="fmt" onChange="dueDateValue()">
				<option value="Invoice">INVOICE</option>
				<option value="Surat Jalan">SURAT JALAN</option>
				<option value="Tukar Faktur">TUKAR FAKTUR</option>
			</select>
		</td>
		<td>5b. Closing on <input name="_payment_closing_on" type="text" class="fmtd" id="_payment_closing_on" size="10" onBlur="dueDateValue()" /></td>
		<td><input type="text" name="_payment_for_the_month_week" class="fmt" size="2" maxlength="2">For the Month/Week(M/W)</td>
	</tr>
	<tr>
		<td>by 1)<input type="checkbox" name="_payment_chk[]" value="16" onClick="enabledText(this, 'cash')" />Cash</td>
		<td>2)<input type="checkbox" name="_payment_chk[]" value="32" onClick="enabledText(this, 'check')" />Check</td>
		<td>3)<input type="checkbox" name="_payment_chk[]" value="64" onClick="enabledText(this, 'transfer')" checked />Transfer</td>
		<td>4)<input type="checkbox" name="_payment_chk[]" value="128" onClick="enabledBankOption(this)" /> Giro</td>
	</tr>
	<tr>
		<td>by<input name="_payment_cash_by" type="text" class="fmt" id="_payment_cash_by" maxlength="18" disabled></td>
		<td>by<input name="_payment_check_by" type="text" class="fmt" id="_payment_check_by" maxlength="18" disabled></td>
		<td>by<input name="_payment_transfer_by" type="text" class="fmt" id="_payment_transfer_by" maxlength="18" disabled></td>
		<td>
			Issue : <input type="text" name="_payment_giro_issue" size="8" class="fmtd">
			Due : <input type="text" name="_payment_giro_due" size="8" class="reqd">
		</td>
	</tr>
	<tr>
		<th>BANK</th>
		<?php if(ZKP_SQL == 'IDC' && ZKP_URL == 'IDC') { ?>
		<td>
			<input type="radio" name="_bank" value="BCA1" onCLick="bankDesc(this.value)" id="bca1" disabled><label for="bca1">BCA 1</label><br />
			<input type="radio" name="_bank" value="DANAMON" onCLick="bankDesc(this.value)" id="danamon" disabled><label for="danamon">Danamon</label>
		</td>
		<td>
			<input type="radio" name="_bank" value="MANDIRI" onCLick="bankDesc(this.value)" id="mandiri" disabled><label for="mandiri">Mandiri</label>
		</td>
		<?php } else if(ZKP_SQL == 'IDC' && ZKP_URL == 'MEP') { ?>
		<td colspan="2">
			<input type="radio" name="_bank" value="BCA1" onCLick="bankDesc(this.value)" id="bca1" disabled><label for="bca1">BCA 1</label><br />
			<input type="radio" name="_bank" value="MANDIRI" onCLick="bankDesc(this.value)" id="mandiri" disabled><label for="mandiri">Mandiri</label>
		</td>
		
		<?php } else if(ZKP_SQL == 'MED') { ?>
		<td colspan="2">
			<input type="radio" name="_bank" value="DANAMON2" onCLick="bankDesc(this.value)" id="danamon2" disabled><label for="danamon2">DANAMON</label><br />
			<input type="radio" name="_bank" value="BII3" onCLick="bankDesc(this.value)" id="bii3" disabled><label for="bii3">BII</label><br />
		</td>
		<?php } ?>
		<td colspan="2">
			<textarea name="_bank_address" rows="3" style="width:100%" readonly></textarea>
		</td>
	</tr>
	<tr>
		<th>DATE</th>
		<td colspan="2">Tukar Faktur : <input type="text" name="_tukar_faktur_date" class="fmt" size="15" onBlur="dueDateValue()" disabled></td>
	</tr>
</table><br/>
<strong>OTHERS</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="12%">SIGN BY</th>
		<td width="35%"><input type="text" name="_signature_by" class="req"></td>
		<th width="15%">PAPER FORMAT</th>
		<td>
			<input type="radio" name="_paper_format" value="A" id="A" checked><label for="A">A &nbsp; </label>
			<input type="radio" name="_paper_format" value="B" id="B"><label for="B">B </label>
		</td>
	</tr>
	<?php if(ZKP_SQL == 'IDC' && $_vat > 0) {?>
	<tr>
		<th>SIGN PAJAK BY</th>
		<td colspan="3">
			<input type="radio" name="_signature_pajak_by" value="A" id="signA" checked><label for="signA">In Ki Kim Lee &nbsp; </label>
			<input type="radio" name="_signature_pajak_by" value="B" id="signB"><label for="signB">Hee Bae Kim</label>
		</td>
	</tr>
	<?php } else if(ZKP_SQL == 'MED' && $_vat > 0) { ?>
	<tr>
		<th>SIGN PAJAK BY</th>
		<td colspan="3">
			<input type="radio" name="_signature_pajak_by" value="A" id="signA" checked><label for="signa">Jae Hyun Yoon&nbsp; </label>
			<input type="radio" name="_signature_pajak_by" value="B" id="signB"><label for="signB">Tia Setiawati</label>
		</td>
	</tr>
    <?php } ?>
	<?php if($_dept == 'A' && $_type_bill != '1') {?>
	<tr>
		<th>CONSIGNMENT</th>
		<td colspan="3">
			<input type="checkbox" name="_is_cons" value="t" onclick="enabledSalesPeriod(this.checked)">Yes, &nbsp; &nbsp;
			Sales Period from : <input type="text" name="_sales_from" class="fmtd" size="10" disabled> 
			to : <input type="text" name="_sales_to" class="fmtd" size="10" disabled>
		</td>
	</tr>
	<?php } ?>
	<tr>
		<th>REMARK</th>
		<td colspan="3"><textarea name="_remark" style="width:100%" rows="4"></textarea></td>
	</tr>
</table>