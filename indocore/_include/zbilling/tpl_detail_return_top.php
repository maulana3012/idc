<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<td colspan="4"><strong class="bar_bl">RETURN INFORMATION</strong></td>
		<td colspan="2" align="right">
			<I>Last updated by : <?php echo ucfirst($column['turn_lastupdated_by_account']).date(', j-M-Y g:i:s', strtotime($column['turn_lastupdated_timestamp']))?></I>
		</td>
	</tr>
	<tr>
		<th>RETURN CODE</th>
		<td colspan="2"><b><?php echo $column["turn_code"] ?></b></td>
	</tr>
	<tr>
		<th>TYPE</th>
		<td colspan="2">
			<select name="_type" class="req" disabled>
				<option value="RO">Return</option>
				<option value="RR">Return Replace</option>
			</select> <b>[ <?php echo $column['turn_return_condition'] ?> ]</b>
		</td>
		<th>RETURN DATE</th>
		<td><input type="text" name="_return_date" class="reqd" value="<?php echo date('d-M-Y',strtotime($column['turn_return_date'])) ?>"></td>
	</tr>
	<tr>
		<th>RECEIVED BY</th>
		<td colspan="2"><input type="text" name="_received_by" class="req" value="<?php echo $column['turn_received_by']?>"></td>
		<th>RESPONSIBLE BY</th>
		<td>
<?php
$sql = "SELECT ma_idx, ma_account,ma_display_as FROM ".ZKP_SQL."_tb_mbracc WHERE ma_display_as > 0 ORDER BY ma_account";
isZKError($res = & query($sql)) ? $M->printMessage($result):0;
print "\t\t\t<select name=\"_ship_to_responsible_by\" class=\"req\" disabled>\n";
print "\t\t\t\t<option value=\"0\">==SELECT==</option>\n";
while ($col = fetchRow($res)) {
	if($col[0]==$column['turn_responsible_by']) {
		print "\t\t\t\t<option value=\"".$col[0]."\" selected>".strtoupper($col[1])."</option>\n";
	} else {
		print "\t\t\t\t<option value=\"".$col[0]."\">".strtoupper($col[1])."</option>\n";
	}
}
print "\t\t\t\t<option value=\"1000\">PUSAT</option>\n";
print "\t\t\t</select>\n";
?>
		</td>
	</tr>
	<tr>
		<th rowspan="3" width="12%">CUSTOMER</th>
		<th width="12%"><font color="#696969">CODE</font></th>
		<td width="25%"><b><?php echo $column['turn_cus_to'] ?></b></td>
		<th width="15%">NAME</th>
		<td width="43%"><input type="text" name="_cus_name" class="req" style="width:100%" value="<?php echo $column['turn_cus_to_name'] ?>"></td>
	</tr>
	<tr>
		<th>ATTN</th>
		<td><input type="text" name="_cus_attn" class="fmt" style="width:100%" value="<?php echo $column['turn_cus_to_attn'] ?>"></td>
		<th>NPWP</th>
		<td><input type="text" name="_cus_npwp" class="fmt" style="width:100%" value="<?php echo $column['turn_npwp'] ?>"></td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="3"><input type="text" name="_cus_address" class="fmt" style="width:100%" value="<?php echo $column['turn_cus_to_address'] ?>"></td>
	</tr>
	<tr>
		<th>SHIP TO</th>
		<th><font color="#696969">CODE</font></th>
		<td><b><?php echo $column['turn_ship_to'] ?></b></td>
		<th>NAME</th>
		<td><input type="text" name="_ship_name" class="fmt" size="50" value="<?php echo $column['turn_ship_to_name'] ?>"></td>
	</tr>
</table><br />
<strong>REFERENCE INFORMATION</strong>
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<th rowspan="7" width="12%" valign="top">INVOICE REF.</th>
		<th width="12%"><font color="#696969">CODE</font></th>
		<td width="25%"><a href="revise_billing.php?_code=<?php echo $column['turn_bill_code'] ?>" target="_blank"><b><?php echo $column['turn_bill_code'] ?></b></a></td>
		<th width="15%">DATE</th>
		<td><?php echo $column['bill_date'] ?></td>
	</tr>
	<tr>
		<th>VAT</th>
		<td>
			<input type="radio" name="_btnVat" value="1"<?php echo ($column['turn_vat']>0)? ' checked':'' ?> disabled><input name="_vat_value" type="text" class="fmtn" size="2" maxlength="4" value="<?php echo $column['turn_vat'] ?>" readonly>%
			<input type="radio" name="_btnVat" value="0"<?php echo ($column['turn_vat']<=0)? ' checked':'' ?> disabled>NON VAT
		</td>
		<th>VAT INV NO</th>
		<td><?php echo $column['turn_bill_vat_inv_no'] ?></td>
	</tr>
	<tr>
		<th>PAID BILLING</th>
		<td>
			<input type="radio" name='_is_bill_paid' value='1'<?php echo ($column['turn_is_bill_paid']=='1')? ' checked':'' ?> disabled>YES &nbsp; &nbsp;
			<input type="radio" name='_is_bill_paid' value='0'<?php echo ($column['turn_is_bill_paid']=='0')? ' checked':'' ?> disabled>NO
		</td>
		<th>MONEY BACK</th>
		<td>
			<input type="radio" name='_is_money_back' value='1'<?php echo ($column['turn_is_money_back']=='1')? ' checked':'' ?> disabled>YES &nbsp; &nbsp;
			<input type="radio" name='_is_money_back' value='0'<?php echo ($column['turn_is_money_back']=='0')? ' checked':'' ?> disabled>NO
		</td>
	</tr>
	<tr>
		<th>DO NO</th>
		<td><input type="text" name="_do_code" class="fmt" value="<?php echo $column['bill_do_no'] ?>"></td>
		<th>DO DATE</th>
		<td><input type="text" name="_do_date" class="fmtd" value="<?php echo $column['bill_do_date'] ?>"></td>
	</tr>
	<tr>
		<th>SJ CODE</th>
		<td><input type="text" name="_sj_code" class="fmt" value="<?php echo $column['bill_sj_code'] ?>"></td>
		<th>SJ DATE</th>
		<td><input type="text" name="_sj_date" class="fmtd" value="<?php echo $column['bill_sj_date'] ?>"></td>
	</tr>
	<tr>
		<th>PO NO</th>
		<td><input type="text" name="_po_no" class="fmt" value="<?php echo $column['bill_po_no']?>"></td>
		<th>PO DATE</th>
		<td><input type="text" name="_po_date" class="fmtd" value="<?php echo $column['bill_po_date'] ?>"></td>
	</tr>
</table><br />