<strong class="info">ORDER INFORMATION</strong>
<table width="100%" class="table_box">
<?php if($cek['blocked']) { ?>
	<tr>
		<th colspan="6" align="center">
	        <img src="../../_images/icon/hint.gif"> &nbsp; <span style="font-family:Courier;color:blue;font-weight:bold">HINT</span>
			<span style="font-family:Courier;font-size:12px">
			Customer <?php echo $cek['customer_blocked'] ?> is a blocked customer. 
			</span>
		</th>
	</tr>
<?php } ?>
	<tr>
		<th width="9%"> CODE</th>
		<td width="25%"></td>
		<th width="12%">RECEIVED BY</th>
		<td><?php echo $_received_by?></td>
		<th width="12%">CONFIRM BY</th>
		<td><?php echo $_confirm_by?></td>
	</tr>
	<tr>
		<th>PO DATE</th>
		<td><?php echo $_po_date?></td>
		<th>PO NO</th>
		<td><?php echo $_po_no?></td>
		<th>VAT</th>
		<td><input name="_vat" type="text" class="fmtn" size="2" maxlength="4" value="<?php echo $_vat ?>" readonly>%</td>
	</tr>
</table>
<table width="100%" class="table_box" cellspacing="0">
	<tr>
		<th width="8%">&nbsp;</th>
		<th width="8%">CODE</th>
		<th width="19%">ATTN</th>
		<th width="65%">ADDRESS</th>
	</tr>
	<tr>
		<th>CUSTOMER</th>
		<td align="center"><?php echo $_cus_to?></td>
		<td><?php echo cut_string($_cus_to_attn, 20)?></td>
		<td><?php echo cut_string($_cus_to_address, 97)?></td>
	</tr>
	<tr>
		<th>SHIP TO</th>
		<td align="center"><?php echo $_ship_to?></td>
		<td><?php echo cut_string($_ship_to_attn, 20)?></td>
		<td><?php echo cut_string($_ship_to_address, 97)?></td>
	</tr>
	<tr>
		<th>BILL TO</th>
		<td align="center"><?php echo $_bill_to?></td>
		<td><?php echo cut_string($_bill_to_attn, 20)?></td>
		<td><?php echo cut_string($_bill_to_address, 97)?></td>
	</tr>
</table><br />