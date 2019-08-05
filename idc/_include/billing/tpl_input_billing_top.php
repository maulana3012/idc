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
	        <select name="_ship_to_responsible_by" id="_ship_to_responsible" class="fmt" disabled>
                <option value="0">==SELECT==</option>
            </select>
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
<script language="javascript" type="text/javascript">
<?php
$sql = "SELECT ma_idx, ma_account, ma_display_as FROM tb_mbracc WHERE ma_display_as > 0 ORDER BY ma_account";
$result = & query($sql);
echo "var mkt = new Array();\n";
$i = 0;
while ($row =& fetchRow($result,0)) {
	if($row[2] & 1) $j='IDC';
	if($row[2] & 2) $j='MED';
	if($row[2] & 1 && $row[2] & 2) $j='ALL';
	if($row[2] == 4) $j=false;
	if($j != false) {
		if(ZKP_SQL == $j || $j == 'ALL') echo "mkt['".$i++."'] = ['".$row[0]."','".strtoupper($row[1])."',".$row[2]."];\n";
	}
}
?>

function initOption() {
	for (i=0; i<mkt.length; i++) { 
		addOption(document.frmInsert._ship_to_responsible,mkt[i][1], mkt[i][0]);
	}

	addOption(document.frmInsert._ship_to_responsible,'PUSAT', '1000');
	setSelect(window.document.frmInsert._ship_to_responsible, "<?php echo $_ship_to_responsible_by ?>");
}
</script>