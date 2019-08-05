<table width="100%" class="table_box">
	<tr>
		<td colspan="2"><strong class="info">BILLING INFORMATION</strong></td>
		<td colspan="3" align="right">
			<I>Last updated by : <?php echo ucfirst($column['bill_lastupdated_by_account']) . date(', j-M-Y g:i:s', strtotime($column['bill_lastupdated_timestamp']))?></I>
		</td>
	</tr>
	<tr>
		<th width="15%">INVOICE NO</th>
		<td><b><?php echo $_code ?></b></td>
		<td><button name='btnMoveDept' class='input_sky' style='width:60px;height:20px' onclick="window.location.href='change_dept.php?_code=<?php echo $_code ?>'"><img src="../../_images/icon/move.gif" width="15px" align="middle" alt="Move billing"></button></td>
		<th width="15%">INVOICE DATE</th>
		<td colspan="2"><input type='text' name="_inv_date" size="15" class="reqd" value="<?php echo date("j-M-Y", strtotime($column['bill_inv_date']))?>"></td>
	</tr>
	<tr>
		<th>FAKTUR PAJAK NO.</th>
		<td colspan="2"><?php echo $column['bill_vat_inv_no'] ?></td>
		<th>RECEIVED BY</th>
		<td><input type="text" name="_received_by" size="15" class="req" value="<?php echo $column['bill_received_by']?>"></td>
	</tr>
<?php if ($template=='1' || $template=='4') {?>
	<tr>
		<th>DO NO</th>
		<td colspan="2"><input type="text" name="_do_no" value="<?php echo $column['bill_do_no']?>"></td>
		<th>DO DATE</th>
		<td><input type="text" name="_do_date" size="15" value="<?php echo ($column['bill_do_date'] != '') ? date("j-M-Y", strtotime($column['bill_do_date'])) : "" ?>"></td>
	</tr>
<?php } else { ?>
	<tr>
		<td>
			<input type="hidden" name="_do_no" value="<?php echo $column['bill_do_no']?>">
			<input type="hidden" name="_do_date" value="<?php echo $column['bill_do_date']?>">
		</td>
	</tr>
<?php } ?>
	<tr>
		<th>PO NO</th>
		<td colspan="2"><input type="text" name="_po_no" class="fmt" value="<?php echo $column['bill_po_no']?>"></td>
		<th>PO DATE</th>
		<td><input type="text" name="_po_date" size="15" class="fmtd" value="<?php echo ($column['bill_po_date'] != '') ? date("j-M-Y", strtotime($column['bill_po_date'])) : ''?>"></td>
	</tr>
	<tr>
		<th>SJ CODE</th>
		<td colspan="2">
			<input type="checkbox" name="chkSjCode">
			<input type="text" name="_sj_code" value="<?php echo $column['bill_sj_code'] ?>">
		</td>
		<th width="15%">SJ DATE</th>
		<td colspan="2"><input type="text" name="_sj_date" size="15" value="<?php echo date("j-M-Y", strtotime($column['bill_sj_date']))?>"></td>
	</tr>
	<tr>
		<th>VAT</th>
		<td colspan="2">
			<input type="radio" name="_btnVat" disabled <?php echo ($column['bill_vat'] > 0) ? 'checked' : '' ?>><input type="text" class="fmtn" size="2" maxlength="4" value="<?php echo $column['bill_vat'] ?>" disabled>%
			<input type="radio" name="_btnVat" disabled <?php echo ($column['bill_vat'] > 0) ? '' : 'checked' ?>>NON VAT
		</td>
		<th>TYPE OF PAJAK</th>
		<td>
			<input type="radio" <?php echo ($column['bill_type_pajak'] == 'IO') ? "checked" : '' ?> disabled>IO &nbsp;
			<input type="radio" <?php echo ($column['bill_type_pajak'] == 'IP') ? "checked" : '' ?> disabled>IP
		</td>
	</tr>
	<tr>
		<th rowspan="4" width="15%">CUSTOMER</th>
		<th><a href="javascript:fillCustomer('customer')"><u>C</u>ODE</a></th>
		<td><input type="text" name="_cus_to" class="req" size="5" value="<?php echo $column['bill_cus_to'] ?>"></td>
		<th>NAME</th>
		<td colspan="3"><input type="text" name="_cus_name" class="req" style="width:100%" maxlength="128" value="<?php echo $column['bill_cus_to_name'] ?>"></td>
	</tr>
	<tr>
		<th>ATTN</th>
		<td><input type="text" name="_cus_attn" class="fmt" style="width:100%" maxlength="128" value="<?php echo $column['bill_cus_to_attn'] ?>"></td>
		<th>RESPONSIBLE BY</th>
		<td colspan="3">
<?php
$sql = "SELECT ma_idx, ma_account, ma_display_as FROM ".ZKP_SQL."_tb_mbracc WHERE ma_display_as > 0 ORDER BY ma_account";
isZKError($res = & query($sql)) ? $M->printMessage($result):0;
print "\t\t\t<input type=\"hidden\" name=\"_cus_to_responsible_by\" value=\"0\" class=\"fmt\">\n";
print "\t\t\t<select name=\"_ship_to_responsible_by\" class=\"req\">\n";
print "\t\t\t\t<option value=\"0\">==SELECT==</option>\n";
while ($col = fetchRow($res)) {
	if($col[2] & 1) {
		if($col[0]==$column['bill_responsible_by']) {
			print "\t\t\t\t<option value=\"".$col[0]."\" selected>".strtoupper($col[1])."</option>\n";
		} else {
			print "\t\t\t\t<option value=\"".$col[0]."\">".strtoupper($col[1])."</option>\n";
		}
	}
}
if(1000==$column['bill_responsible_by']) {
	print "\t\t\t\t<option value=\"1000\" selected>PUSAT</option>\n";
} else {
	print "\t\t\t\t<option value=\"1000\">PUSAT</option>\n";
}
print "\t\t\t</select>\n";
?>
		</td>
	</tr>
	<tr>
		<th>NPWP</th>
		<td colspan="3"><input type="text" name="_cus_npwp" class="fmt" style="width:100%" maxlength="128" value="<?php echo $column['bill_npwp'] ?>"></td>
	</tr>
	<tr>
		<th width="12%">ADDRESS</th>
		<td colspan="5"><input type="text" name="_cus_address" class="fmt" style="width:100%" maxlength="255" value="<?php echo $column['bill_cus_to_address'] ?>"></td>
	</tr>
	<tr>
		<th>SHIP TO</th>
		<th width="12%"><a href="javascript:fillCustomer('ship')"><u>C</u>ODE</a></th>
		<td><input type="text" name="_ship_to" class="req" size="5" value="<?php echo $column['bill_ship_to'] ?>"></td>
		<th>NAME</th>
		<td colspan="3"><input type="text" name="_ship_name" class="req" style="width:100%" maxlength="128" value="<?php echo $column['bill_ship_to_name'] ?>"></td>
	</tr>
	<tr>
		<th rowspan="2">FAKTUR<br />PAJAK TO</th>
		<th width="12%"><a href="javascript:fillCustomer('pajak')"><u>C</u>ODE</a></th>
		<td><input type="text" name="_pajak_to" size="5" value="<?php echo $column['bill_pajak_to'] ?>"></td>
		<th>NAME</th>
		<td colspan="3"><input type="text" name="_pajak_name" style="width:100%" maxlength="128" value="<?php echo $column['bill_pajak_to_name'] ?>"></td>
	</tr>
	<tr>
		<th width="12%">ADDRESS</th>
		<td colspan="5"><input type="text" name="_pajak_address" style="width:100%" maxlength="128" value="<?php echo $column['bill_pajak_to_address'] ?>"></td>
	</tr>
</table><br />