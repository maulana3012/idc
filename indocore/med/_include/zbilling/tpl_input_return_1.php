<strong>RETURN INFORMATION</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="12%">TYPE</th>
		<td width="37%">
			<select name="_type" class="req" onChange="checkedValue()">
				<option value="RO">Return</option>
				<option value="RR">Return Replace</option>
			</select>
		</td>
		<th width="15%">RETURN DATE</th>
		<td><input name="_return_date" type="text" class="reqd" size="15" value="<?php echo date("j-M-Y", time())?>" maxlength="64"></td>
	</tr>
	<tr>
		<th>RECEIVED BY</th>
		<td><input name="_received_by" type="text" class="req" size="15" id="_received_by" value="<?php echo $S->getValue("ma_account")?>"></td>
		<th>RESPONSIBLE BY</th>
		<td>
<?php
$sql = "SELECT ma_idx, ma_account,ma_display_as FROM ".ZKP_SQL."_tb_mbracc WHERE ma_display_as > 0 ORDER BY ma_account";
isZKError($res = & query($sql)) ? $M->printMessage($result):0;
	if(numQueryRows($res) <= 0) {
		$o = new ZKError("INFORMATION", "INFORMATION", "Please register the member hospital first");
		$M->printMessage($res);
	} else {
		print "\t\t\t<input type=\"hidden\" name=\"_cus_to_responsible_by\" value=\"0\" class=\"fmt\">\n";
		print "\t\t\t<select name=\"_ship_to_responsible_by\" class=\"req\">\n";
		print "\t\t\t\t<option value=\"0\">==SELECT==</option>\n";
		while ($col = fetchRow($res)) {
			if($col[2] & 1) print "\t\t\t\t<option value=\"".$col[0]."\">".strtoupper($col[1])."</option>\n";
		}
		print "\t\t\t\t<option value=\"1000\">PUSAT</option>\n";
		print "\t\t\t</select>\n";
	}
?>
		</td>
	</tr>
</table>
<table width="100%" class="table_box" cellspacing="1">
	<tr height="3px">
		<td colspan="4"></td>
	</tr>
	<tr>
		<th rowspan="3" width="12%">CUSTOMER</th>
		<th width="12%"><a href="javascript:fillCustomer('customer')" accesskey="c"><u>C</u>ODE</a></th>
		<td width="25%">
			<input type="hidden" name="_cug_code">
			<input name="_cus_to" type="text" class="req" size="10" maxlength="7">
		</td>
		<th width="15%">NAME</th>
		<td width="43%"><input type="text" name="_cus_name" class="req" style="width:100%" readOnly></td>
	</tr>
	<tr>
		<th>ATTN</th>
		<td><input type="text" name="_cus_attn" class="fmt" style="width:100%"></td>
		<th>NPWP</th>
		<td colspan="2"><input type="text" name="_cus_npwp" class="fmt" style="width:100%"></td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="3"><input type="text" name="_cus_address" class="fmt"  style="width:100%"></td>
	</tr>
	<tr>
		<th>SHIP TO</th>
		<th><a href="javascript:fillCustomer('ship')">CODE</a></th>
		<td>
			<input name="_ship_to" type="text" class="req" size="10" maxlength="7">
			<input type="checkbox" name="chkAbove" onClick="copyCustomer(this, 'ship')" id="ship"><label for="ship">Same as Above</label>
		</td>
		<th>NAME</th>
		<td><input type="text" name="_ship_name" class="req" style="width:100%" readOnly></td>
	</tr>
</table><br />
<strong>REFERENCE INFORMATION</strong>
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<th rowspan="7" width="12%" valign="top">INVOICE REF.</th>
		<th width="12%"><a href="javascript:fillInvoice()"><u>C</u>ODE</a></th>
		<td width="25%">
			<input type="text" name="_bill_code" class="fmt" size="15" readonly>
			<input type="hidden" name="_book_idx">
		</td>
		<th width="15%">DATE</th>
		<td><input type="text" name="_bill_inv_date" size="15" class="fmtd"></td>
	</tr>
	<tr>
		<th>VAT</th>
		<td>
			<input type="hidden" name="_is_vat">
			<input type="radio" name="_btnVat" value="1" onCLick="vatValue(this,1)" checked><input name="_vat" type="text" class="fmtn" size="2" maxlength="4" value="10">%
			<input type="radio" name="_btnVat" value="0" onCLick="vatValue(this,0)">NON VAT
		</td>
		<th>VAT INV NO</th>
		<td><input type="text" name="_bill_vat_inv_no" class="fmt" style="width:100%"></td>
	</tr>
	<tr>
		<th>PAID BILLING</th>
		<td>
			<input type="hidden" name="_bill_paid">
			<input type="radio" name='_is_bill_paid' value='1' onClick="enabledMoneyBack()">YES &nbsp; &nbsp;
			<input type="radio" name='_is_bill_paid' value='0' onClick="enabledMoneyBack()" checked>NO
		</td>
		<th>MONEY BACK</th>
		<td>
			<input type="hidden" name="_money_back">
			<input type="radio" name='_is_money_back' value='1'>YES &nbsp; &nbsp;
			<input type="radio" name='_is_money_back' value='0' checked>NO
		</td>
	</tr>
	<tr>
		<th>DO NO</th>
		<td><input type="text" name="_do_no" class="fmt" size="15" readonly></td>
		<th>DO DATE</th>
		<td><input type="text" name="_do_date" class="fmtd" size="15" readonly></td>
	</tr>
	<tr>
		<th>SJ CODE</th>
		<td><input name="_sj_code" type="text" class="fmt" size="15"></td>
		<th>SJ DATE</th>
		<td><input name="_sj_date" type="text" class="fmtd" size="15"></td>
	</tr>
	<tr>
		<th>PO NO</th>
		<td><input name="_po_no" type="text" class="fmt" size="15" maxlength="64"></td>
		<th>PO DATE</th>
		<td><input name="_po_date" type="text" class="fmtd" size="15" maxlength="64"></td>
	</tr>
</table>