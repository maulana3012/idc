<strong>BILLING INFORMATION</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">RECEIVED BY</th>
		<td width="34%" colspan="2"><?php echo $_received_by?></td>
		<th>INVOICE DATE</th>
		<td><?php echo $_inv_date?></td>
	</tr>
	<tr>
		<th>DO NO</th>
		<td colspan='2'></td>
		<th>DO DATE</th>
		<td><?php echo $_do_date ?></td>
	</tr>
	<tr>
		<th width="15%">SJ CODE</th>
		<td width="34%" colspan="2"><input type="checkbox" <?php echo ($_sj_code == '') ? '' : 'checked' ?> disabled><?php echo $_sj_code?></td>
		<th width="15%">SJ DATE</th>
		<td><?php echo $_sj_date?></td>
	</tr>
	<tr>
		<th>PO NO</th>
		<td colspan='2'><?php echo $_po_no ?></td>
		<th>PO DATE</th>
		<td><?php echo $_po_date ?></td>
	</tr>
	<tr>
		<th>VAT</th>
		<td colspan='2'>
			<input type="radio"<?php echo ($_btnVat == "y") ? " checked" : '' ?> disabled><input type="text" class="fmtn" size="2" value="<?php echo $_vat ?>" disabled>%
			<input type="radio"<?php echo ($_btnVat == "n") ? "checked" : '' ?> disabled>NON VAT
		</td>
		<th>TYPE OF PAJAK</th>
		<td>
			<input type="radio"<?php echo ($_type_of_pajak == "IO") ? " checked" : '' ?> disabled>IO &nbsp;
			<input type="radio"<?php echo ($_type_of_pajak == "IP") ? " checked" : '' ?> disabled>IP
		</td>
	</tr>
	<tr>
		<th rowspan="4">CUSTOMER</th>
		<th width="12%">CODE</th>
		<td><?php echo $_cus_to?></td>
		<th>NAME</th>
		<td><?php echo $_cus_name?></td>
	</tr>
	<tr>
		<th>ATTN</th>
		<td><?php echo $_cus_attn?></td>
		<th>RESPONSIBLE BY</th>
		<td>
<?php
$sql = "SELECT ma_idx, ma_account,ma_display_as FROM ".ZKP_SQL."_tb_mbracc WHERE ma_display_as > 0 ORDER BY ma_account";
isZKError($res = & query($sql)) ? $M->printMessage($result):0;
print "\t\t\t<select name=\"_ship_to_responsible_by\" class=\"req\" disabled>\n";
print "\t\t\t\t<option value=\"0\">==SELECT==</option>\n";
while ($col = fetchRow($res)) {
	if($col[2] & 1) {
		if($col[0]==$_ship_to_responsible_by) {
			print "\t\t\t\t<option value=\"".$col[0]."\" selected>".strtoupper($col[1])."</option>\n";
		} else {
			print "\t\t\t\t<option value=\"".$col[0]."\">".strtoupper($col[1])."</option>\n";
		}
	}
}
if(1000==$_ship_to_responsible_by) {
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
		<td colspan="2"><?php echo $_cus_npwp?></td>
	</tr>
	<tr>
		<th width="12%">ADDRESS</th>
		<td colspan="3"><?php echo $_cus_address?></td>
	</tr>
	<tr>
		<th>SHIP TO</th>
		<th width="12%">CODE</th>
		<td><?php echo $_ship_to?></td>
		<th>NAME</th>
		<td><?php echo $_ship_name?></td>
	</tr>
	<?php if($_vat > 0){ ?>
	<tr>
		<th rowspan="2">FAKTUR<br />PAJAK TO</th>
		<th width="12%">CODE</th>
		<td><?php echo $_pajak_to?></td>
		<th>NAME</th>
		<td><?php echo $_pajak_name?></td>
	</tr>
	<tr>
		<th width="12%">ADDRESS</th>
		<td colspan="3"><?php echo $_pajak_address?></td>
	</tr>
	<?php } ?>
</table><br />