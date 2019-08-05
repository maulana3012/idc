<strong>CONDITION</strong>
<table class="table_box" width="100%">
	<tr>
		<th width="12%">DELIVERY</th>
		<td>1.<input type="text" name="_delivery_warehouse" class="fmt" size="2" maxlength="2" value="<?php echo $column['bill_delivery_warehouse']?>" >ex W/house(P/C/D)</td>
		<td>2.<input type="text" name="_delivery_franco" class="fmt" size="2" maxlength="2" value="<?php echo $column['bill_delivery_franco']?>">Franco(P/D)</td>
		<td>by <input type="text" name="_delivery_by" value="<?php echo $column['bill_delivery_by']?>" size="6" class="fmt"></td>
		<td>
			<input type="checkbox" name="_delivery_chk[]" onClick="enabledText(this, 'freight_charge')" value="1" <?php echo ($column['bill_delivery_chk'] & 1)? "checked":""?>>
			Freight charge:<input type="text" name="_delivery_freight_charge" size="8" value="<?php echo ($column['bill_delivery_freight_charge'] <= 0) ? '' : number_format((double)$column['bill_delivery_freight_charge'])?>" class="fmtn" onKeyUp="formatNumber(this,'dot')" onBlur="updateAmount()">
		</td>
	</tr>
	<tr>
		<th rowspan="4" width="12%">PAYMENT</th>
		<td>1.<input type="checkbox" name="_payment_chk" value="1" <?php echo ($column['bill_payment_chk'] & 1)? "checked":""?> onclick="cod(this)">COD</td>
		<td>2.<input type="checkbox" name="_payment_chk" value="2" <?php echo ($column['bill_payment_chk'] & 2)? "checked":""?>>PREPAID</td>
		<td>3.<input type="checkbox" name="_payment_chk" value="4" <?php echo ($column['bill_payment_chk'] & 4)? "checked":""?>>Consignment</td>
		<td>4.<input type="checkbox" name="_payment_chk" value="8" <?php echo ($column['bill_payment_chk'] & 8)? "checked":""?>>Free/TO/LF/RP/PT</td>
	</tr>
	<tr>
		<td>5. Within  
		  <input name="_payment_widthin_days" type="text" class="fmtn" size="2" value="<?php echo $column['bill_payment_widthin_days']?>" onBlur="dueDateValue()">
		days after</td>
		<td>5a.
			<select name="_payment_sj_inv_fp_tender" class="fmt" onChange="dueDateValue()" <?php echo ($column['bill_cfm_tukar_faktur'] != '')?'disabled':'' ?>>
				<option value="Invoice">INVOICE</option>
				<option value="Surat Jalan">SURAT JALAN</option>
				<option value="Tukar Faktur">TUKAR FAKTUR</option>
			</select>
		</td>
		<td>5b. Closing on <input name="_payment_closing_on" type="text" class="fmtd" size="10" value="<?php echo empty($column['bill_payment_closing_on']) ? "" : date("j-M-Y",strtotime($column['bill_payment_closing_on']))?>" onBlur="dueDateValue()"></td>
		<td><input type="text" name="_payment_for_the_month_week" class="fmt" size="2" maxlength="2" value="<?php echo $column['bill_payment_for_the_month_week'] ?>">For the Month/Week(M/W)</td>
	</tr>
	<tr>
		<td>by 1)<input type="checkbox" name="_payment_chk" value="16" onClick="enabledText(this, 'cash')" <?php echo ($column['bill_payment_chk'] & 16)? "checked":""?>>Cash</td>
		<td>2)<input type="checkbox" name="_payment_chk" value="32" onClick="enabledText(this, 'check')" <?php echo ($column['bill_payment_chk'] & 32)? "checked":""?> onclick="enabledBankOption(this)">Check</td>
		<td>3)<input type="checkbox" name="_payment_chk" value="64" onClick="enabledText(this, 'transfer')" <?php echo ($column['bill_payment_chk'] & 64)? "checked":""?> onclick="enabledBankOption(this)">Transfer</td>
		<td>4)<input type="checkbox" name="_payment_chk" value="128" onClick="enabledBankOption(this)" <?php echo ($column['bill_payment_chk'] & 128)? "checked":""?>>Giro</td>
	</tr>
	<tr>
		<td>by<input name="_payment_cash_by" type="text" class="fmt" id="_payment_cash_by" value="<?php echo $column['bill_payment_cash_by']?>"></td>
		<td>by<input name="_payment_check_by" type="text" class="fmt" id="_payment_check_by" value="<?php echo $column['bill_payment_check_by']?>"></td>
		<td>by<input name="_payment_transfer_by" type="text" class="fmt" id="_payment_transfer_by" value="<?php echo $column['bill_payment_transfer_by']?>"></td>
		<td>
			Issue : <input type="text" name="_payment_giro_issue" size="10" class="fmtd" value="<?php echo ($column['bill_payment_giro_issue'] != '') ? date("j-M-Y", strtotime($column['bill_payment_giro_issue'])) : ''?>">
			Due : <input type="text" name="_payment_giro_due" size="10" class="reqd" value="<?php echo date("j-M-Y", strtotime($column['bill_payment_giro_due']))?>">
		</td>
	</tr>
	<tr>
		<th>BANK</th>
		<?php if(ZKP_SQL == 'IDC') { ?>
		<td>
			<input type="radio" name="_bank" value="BCA1" onCLick="bankDesc(this.value)" id="bca1" disabled <?php echo ($column['bill_payment_bank'] == 'BCA1') ? 'checked' : '' ?>><label for="bca1">BCA 1</label><br />
			<input type="radio" name="_bank" value="BCA2" onCLick="bankDesc(this.value)" id="bca2" disabled <?php echo ($column['bill_payment_bank'] == 'BCA2') ? 'checked' : '' ?>><label for="bca2">BCA 2</label><br />
			<input type="radio" name="_bank" value="MANDIRI" onCLick="bankDesc(this.value)" id="mandiri" disabled <?php echo ($column['bill_payment_bank'] == 'MANDIRI') ? 'checked' : '' ?>><label for="mandiri">Mandiri</label><br />
		</td>
		<td>
			<input type="radio" name="_bank" value="BII1" onCLick="bankDesc(this.value)" id="bii1" disabled <?php echo ($column['bill_payment_bank'] == 'BII1') ? 'checked' : '' ?>><label for="bii1">BII 1</label><br />
			<input type="radio" name="_bank" value="BII2" onCLick="bankDesc(this.value)" id="bii2" disabled <?php echo ($column['bill_payment_bank'] == 'BII2') ? 'checked' : '' ?>><label for="bii2">BII 2</label><br />
			<input type="radio" name="_bank" value="DANAMON" onCLick="bankDesc(this.value)" id="danamon" disabled <?php echo ($column['bill_payment_bank'] == 'DANAMON') ? 'checked' : '' ?>><label for="danamon">Danamon</label>
		</td>
		<?php } else if(ZKP_SQL == 'MED' && ZKP_URL == 'MED') { ?>
		<td colspan="2">
			<input type="radio" name="_bank" value="DANAMON2" onCLick="bankDesc(this.value)" id="danamon2" disabled <?php echo ($column['bill_payment_bank'] == 'DANAMON2') ? 'checked' : '' ?>><label for="danamon2">DANAMON</label><br />
			<input type="radio" name="_bank" value="BII3" onCLick="bankDesc(this.value)" id="bii3" disabled <?php echo ($column['bill_payment_bank'] == 'BII3') ? 'checked' : '' ?>><label for="bii3">BII</label><br />
		</td>
		<?php } else if(ZKP_SQL == 'MED' && ZKP_URL == 'SMD') { ?>
		<td colspan="2">
			<input type="radio" name="_bank" value="DANAMON3" onCLick="bankDesc(this.value)" id="danamon3" disabled <?php echo ($column['bill_payment_bank'] == 'DANAMON3') ? 'checked' : '' ?>><label for="danamon3">DANAMON</label><br />
		</td>
		<?php } ?>
		<td colspan="2">
			<textarea name="_bank_address" rows="3" style="width:100%" readonly><?php echo $column['bill_payment_bank_address'] ?></textarea>
		</td>
	</tr>
	<tr>
		<th>DATE</th>
		<td colspan="2">Tukar Faktur : <input type="text" name="_tukar_faktur_date" class="fmt" size="15" onBlur="dueDateValue()" value="<?php echo ($column['bill_tukar_faktur_date'] != '') ? date("j-M-Y", strtotime($column['bill_tukar_faktur_date'])) : ''?>"></td>
	</tr>
</table><br/>
<strong>OTHERS</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="12%">SIGN BY</th>
		<td width="35%"><input type="text" name="_signature_by" class="req" value="<?php echo $column['bill_signature_by'] ?>"></td>
		<th width="15%">PAPER FORMAT</th>
		<td>
			<input type="radio" name="_paper_format" value="A" id="A" <?php echo ($column['bill_paper_format']=='A')?'checked':'' ?>><label for="A">A &nbsp; </label>
			<input type="radio" name="_paper_format" value="B" id="B" <?php echo ($column['bill_paper_format']=='B')?'checked':'' ?>><label for="B">B </label>
		</td>
	</tr>
	<?php if(ZKP_SQL == 'IDC' && $column["bill_vat"] > 0) { ?>
	<tr>
		<th>SIGN PAJAK BY</th>
		<td colspan="3">
			<input type="radio" name="_signature_pajak_by" value="A" id="signA" <?php echo ($column['bill_signature_pajak_by']=='A'?" checked":"") ?>><label for="signA">In Ki Kim Lee &nbsp;</label>
			<input type="radio" name="_signature_pajak_by" value="B" id="signB" <?php echo ($column['bill_signature_pajak_by']=='B'?" checked":"") ?>><label for="signB">Min Sang Hyun</label>
		</td>
	</tr>
	<?php 
	} else if(ZKP_SQL == 'MED' && $column["bill_vat"] > 0) {
		if($column["bill_ordered_by"] == '1') {
	?>
	<tr>
		<th>SIGN PAJAK BY</th>
		<td colspan="3">
			<input type="radio" name="_signature_pajak_by" value="A" id="signA" <?php echo ($column['bill_signature_pajak_by']=='A'?" checked":"") ?>><label for="signa">Jae Hyun Yoon&nbsp; </label>
			<input type="radio" name="_signature_pajak_by" value="B" id="signB" <?php echo ($column['bill_signature_pajak_by']=='B'?" checked":"") ?>><label for="signB">Ratna Afrianti</label>
		</td>
	</tr>
    <?php } else if($column["bill_ordered_by"] == '2') { ?>
    <tr>
		<th>SIGN PAJAK BY</th>
		<td colspan="3">
			<input type="radio" name="_signature_pajak_by" value="A" id="signA" <?php echo ($column['bill_signature_pajak_by']=='A'?" checked":"") ?>><label for="signa">Min Sang Hyun</label>
			<input type="radio" name="_signature_pajak_by" value="B" id="signB" <?php echo ($column['bill_signature_pajak_by']=='B'?" checked":"") ?>><label for="signB">Dahlia Sana Buwana</label>
		</td>
	</tr>
    <?php } } ?>
    <?php if($column["bill_dept"] == 'A' & $column["bill_type_billing"] != '1') { ?>
	<tr>
		<th>CONSIGNMENT</th>
		<td colspan="3">
			<input type="checkbox" name="_is_cons" value="t" onclick="enabledSalesPeriod(this.checked)" <?php echo ($column['bill_is_consinyasi'] == 't') ? 'checked':'' ?>>Yes, &nbsp; &nbsp;
			Sales Period from : <input type="text" name="_sales_from" class="fmtd" size="10" value="<?php echo ($column['bill_sales_from'] == '') ? '' : date('d-M-Y', strtotime($column['bill_sales_from'])) ?>"> 
			to : <input type="text" name="_sales_to" class="fmtd" size="10" value="<?php echo ($column['bill_sales_to'] == '') ? '' : date('d-M-Y', strtotime($column['bill_sales_to'])) ?>">
		</td>
	</tr>
	<?php } ?>
	<tr>
		<th>REMARK</th>
		<td colspan="3"><textarea name="_remark" style="width:100%" rows="4"><?php echo $column['bill_remark'] ?></textarea></td>
	</tr>
</table>