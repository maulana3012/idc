<strong>RETURN INFORMATION</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="12%">TYPE</th>
		<td width="37%">
			<select name="_type" class="req" disabled>
				<option value="RO"<?php echo ($_type_return=='RO') ? ' selected':'' ?>>Return</option>
				<option value="RR"<?php echo ($_type_return=='RR') ? ' selected':'' ?>>Return Replace</option>
			</select> <b>[ <?php echo $_return_condition ?> ]</b>
		</td>
		<th width="15%">RETURN DATE</th>
		<td><?php echo $_return_date ?></td>
	</tr>
	<tr>
		<th>RECEIVED BY</th>
		<td><?php echo $_received_by ?></td>
		<th>RESPONSIBLE BY</th>
		<td>
<?php
$sql = "SELECT ma_idx, ma_account,ma_display_as FROM ".ZKP_SQL."_tb_mbracc WHERE ma_display_as > 0 ORDER BY ma_account";
isZKError($res = & query($sql)) ? $M->printMessage($result):0;
print "\t\t\t<select name=\"_ship_to_responsible_by\" class=\"req\" disabled>\n";
print "\t\t\t\t<option value=\"0\">==SELECT==</option>\n";
while ($col = fetchRow($res)) {
	if($col[0]==$_ship_to_responsible_by) {
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
</table>
<table width="100%" class="table_box" cellspacing="1">
	<tr height="3px">
		<td colspan="4"></td>
	</tr>
	<tr>
		<th rowspan="3" width="12%">CUSTOMER</th>
		<th width="12%"><font color="#696969">CODE</font></th>
		<td width="25%"><?php echo $_cus_to ?></td>
		<th width="15%">NAME</th>
		<td width="43%"><?php echo $_cus_name ?></td>
	</tr>
	<tr>
		<th>ATTN</th>
		<td><?php echo $_cus_attn ?></td>
		<th>NPWP</th>
		<td><?php echo $_cus_npwp ?></td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="3"><?php echo $_cus_address ?></td>
	</tr>
	<tr>
		<th>SHIP TO</th>
		<th><font color="#696969">CODE</font></th>
		<td><?php echo $_ship_to ?></td>
		<th>NAME</th>
		<td><?php echo $_ship_name ?></td>
	</tr>
</table><br />
<strong>REFERENCE INFORMATION</strong>
<table width="100%" class="table_box" cellspacing="1">
	<?php if ($return_code != '') { ?>
	<tr>
		<th><img src="../../_images/icon/hint.gif"> &nbsp; <span style="font-family:Courier;color:blue;font-weight:bold">HINT</span></th>
		<th colspan="4" align="left">
			<span style="font-family:Courier;font-size:12px">
			This billing already has return. Please check again in <a href="javascript:seedetailreturn()" style="color:#446FBE"><u>billing detail</u>.</a>
			Current return for this billing : <b style="color:#000000"><?php echo $return_code ?></b>
			</span>
		</th>
	</tr>
	<?php } ?>
	<tr>
		<th rowspan="7" width="12%" valign="top">INVOICE REF.</th>
		<th width="12%"><font color="#696969">CODE</font></th>
		<td width="25%"><a href="revise_billing.php?_code=<?php echo $_bill_code ?>" target="_blank"><b><?php echo $_bill_code ?></b></a></td>
		<th width="15%">DATE</th>
		<td><?php echo $_bill_date ?></td>
	</tr>
	<tr>
		<th>VAT</th>
		<td>
			<input type="radio" name="_btnVat" value="1"<?php echo ($_is_vat=='1')? ' checked':'' ?> disabled><input name="_vat_value" type="text" class="fmtn" size="2" maxlength="4" value="<?php echo $_vat ?>" disabled>%
			<input type="radio" name="_btnVat" value="0"<?php echo ($_is_vat=='0')? ' checked':'' ?> disabled>NON VAT
		</td>
		<th>VAT INV NO</th>
		<td><?php echo $_faktur_no ?></td>
	</tr>
	<tr>
		<th>PAID BILLING</th>
		<td>
			<input type="radio" name='_is_bill_paid' value='1'<?php echo ($_is_bill_paid=='1')? ' checked':'' ?> disabled>YES &nbsp; &nbsp;
			<input type="radio" name='_is_bill_paid' value='0'<?php echo ($_is_bill_paid=='0')? ' checked':'' ?> disabled>NO
		</td>
		<th>MONEY BACK</th>
		<td>
			<input type="radio" name='_is_money_back' value='1'<?php echo ($_is_money_back=='1')? ' checked':'' ?> disabled>YES &nbsp; &nbsp;
			<input type="radio" name='_is_money_back' value='0'<?php echo ($_is_money_back=='0')? ' checked':'' ?> disabled>NO
		</td>
	</tr>
	<tr>
		<th>DO NO</th>
		<td><?php echo $_do_no ?></td>
		<th>DO DATE</th>
		<td><?php echo $_do_date ?></td>
	</tr>
	<tr>
		<th>SJ CODE</th>
		<td><?php echo $_sj_no ?></td>
		<th>SJ DATE</th>
		<td><?php echo $_sj_date ?></td>
	</tr>
	<tr>
		<th>PO NO</th>
		<td><?php echo $_po_no ?></td>
		<th>PO DATE</th>
		<td><?php echo $_po_date ?></td>
	</tr>
</table><br />