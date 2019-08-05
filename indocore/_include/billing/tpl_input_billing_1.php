<strong class="info">BILLING INFORMATION</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">RECEIVED BY</th>
		<td width="34%"><input name="_received_by" type="text" class="req" size="15"></td>
		<th>INVOICE DATE</th>
		<td><input name="_inv_date" type="text" class="reqd" size="15"></td>
	</tr>
	<tr>
		<th width="15%">DO NO</th>
		<td><input name="_do_no" type="text" class="fmt" size="15"></td>
		<th width="15%">DO DATE</th>
		<td><input name="_do_date" type="text" class="fmtd" size="15"></td>
	</tr>
	<tr>
		<th>SJ CODE</th>
		<td><input type="checkbox" name="chkSjCode" onClick="enabledText(this)"><input name="_sj_code" type="text" class="fmt" size="15" disabled></td>
		<th>SJ DATE</th>
		<td><input name="_sj_date" type="text" class="fmtd" size="15" disabled></td>
	</tr>
	<tr>
		<th>PO NO</th>
		<td><input name="_po_no" type="text" class="fmt" size="15" maxlength="64"></td>
		<th>PO DATE</th>
		<td><input name="_po_date" type="text" class="fmtd" size="15" maxlength="64"></td>
	</tr>
	<tr>
		<th>VAT</th>
		<td>
			<input type="radio" name="_btnVat" value="y" onClick="vatValue(this.value)"><input name="_vat_val" type="text" class="fmtn" size="2" maxlength="4">%
			<input type="radio" name="_btnVat" value="n" onClick="vatValue(this.value)">NON VAT</label>
		</td>
		<th>TYPE OF PAJAK</th>
		<td>
			<input type="radio" name="_type_of_pajak" value="IO">IO &nbsp;
			<input type="radio" name="_type_of_pajak" value="IP">IP
		</td>
	</tr>
</table>
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<th rowspan="4" width="12%">CUSTOMER</th>
		<th width="12%"><a href="javascript:fillCustomer('customer')" accesskey="c"><u>C</u>ODE</a></th>
		<td width="25%">
			<input type="hidden" name="_cug_code">
			<input type="text" name="_cus_to" class="req" size="10" maxlength="7">
		</td>
		<th width="15%">NAME</th>
		<td width="43%"><input type="text" name="_cus_name" class="fmt" style="width:100%" maxlength="128" readOnly></td>
	</tr>
	<tr>
		<th>ATTN</th>
		<td><input type="text" name="_cus_attn" class="fmt"  style="width:70%" maxlength="128"></td>
		<th>RESPONSIBLE BY</th>
		<td>
        	<input type="hidden" name="_cus_to_responsible_by" value="0" class="fmt">
	        <select name="_ship_to_responsible_by" id="_ship_to_responsible_by" class="fmt">
                <option value="0">==SELECT==</option>
            </select>
		</td>
	</tr>
	<tr>
		<th>NPWP</th>
		<td colspan="2"><input type="text" name="_cus_npwp" class="fmt" style="width:100%" maxlength="128"></td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="3"><input type="text" name="_cus_address" class="fmt"  style="width:100%" maxlength="255"></td>
	</tr>
	<tr>
		<th>SHIP TO</th>
		<th><a href="javascript:fillCustomer('ship')">CODE</a></th>
		<td>
			<input name="_ship_to" type="text" class="req" size="10" maxlength="7">
			<input type="checkbox" name="chkAbove" onClick="copyCustomer(this, 'ship')" id="ship"><label for="ship">Same as Above</label>
		</td>
		<th>NAME</th>
		<td><input type="text" name="_ship_name" class="req" style="width:100%" maxlength="128" readOnly></td>
	</tr>
	<tr>
		<th rowspan="2">FAKTUR<br />PAJAK TO</th>
		<th><a href="javascript:fillCustomer('pajak')">CODE</a></th>
		<td>
			<input name="_pajak_to" type="text" class="req" size="10" maxlength="7">
			<input type="checkbox" name="chkAbove2" onClick="copyCustomer(this, 'pajak')" id="pajak"><label for="pajak">Same as Above</label>
		</td>
		<th>NAME</th>
		<td><input type="text" name="_pajak_name" class="req" style="width:100%" maxlength="128" readOnly></td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="3"><input type="text" name="_pajak_address" class="req"  style="width:100%" maxlength="255"></td>
	</tr>
</table>
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
	for (i=0; i<mkt.length; i++) 
		addOption(document.frmInsert._ship_to_responsible_by,mkt[i][1], mkt[i][0]);
	addOption(document.frmInsert._ship_to_responsible_by,'PUSAT', '1000');
}
</script>