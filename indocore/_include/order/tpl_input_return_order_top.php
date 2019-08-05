<strong class="info">RETURN INFORMATION</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="12%">TYPE</th>
		<td width="22%">
			<select name="_type" class="req" disabled>
				<option value="RO" <?php echo ($_type == 'RO') ? 'selected' : '' ?>>SALES</option>
				<option value="RK" <?php echo ($_type == 'RK') ? 'selected' : '' ?>>KONSINYASI</option>
			</select>
		</td>
		<th width="12%">RECEIVED BY</th>
		<td width="22%"><?php echo $_received_by?></td>
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
	<tr>
		<th rowspan="2">ORDER</th>
		<td><b><?php echo $_ord_code?></b></td>
		<th>DATE</th>
		<td><?php echo ($_ord_date == '') ? '' : date('j-M-Y', strtotime($_ord_date))?></td>
		<td colspan="2" align="right"><button name="btnDetailOrder" onClick="seeDetailOrdRef()" class="fmt" <?php echo ($_ord_code == '') ? 'disabled' : '' ?>>DETAIL</button></td>
	</tr>
	<?php if($column["return_code"] != '') { ?>
	<tr>
		
		<th><img src="../../_images/icon/hint.gif"> &nbsp; <span style="font-family:Courier;color:blue;font-weight:bold">HINT</span></th>
		<th colspan="4" align="left">
			<span style="font-family:Courier;font-size:12px">
			This order already has return. Please check again in <a href="<?php echo HTTP_DIR . "$currentDept/order/revise_order.php?_code=$_ord_code" ?>" target="_blank" style="color:#446FBE"><u>order detail</u>.</a>
			Current return for this order : <b style="color:#000000"><?php echo $column["return_code"] ?></b>
			</span>
		</th>
	</tr>
	<tr height="10px"><td></td></tr>
	<?php } ?>
</table>
<table width="100%" class="table_nn" cellspacing="0">
	<tr height="30px">
		<th width="12%">&nbsp;</th>
		<th width="8%">CODE</th>
		<th width="18%">ATTN</th>
		<th width="65%">ADDRESS</th>
	</tr>
	<tr>
		<th>CUSTOMER</th>
		<td align="center"><?php echo $_cus_to?></td>
		<td><?php echo cut_string($_cus_to_attn, 20)?></td>
		<td><?php echo cut_string($_cus_to_address, 90)?></td>
	</tr>
	<tr>
		<th>SHIP TO</th>
		<td align="center"><?php echo $_ship_to?></td>
		<td><?php echo cut_string($_ship_to_attn, 20)?></td>
		<td><?php echo cut_string($_ship_to_address, 90)?></td>
	</tr>
	<tr>
		<th>BILL TO</th>
		<td align="center"><?php echo $_bill_to?></td>
		<td><?php echo cut_string($_bill_to_attn, 20)?></td>
		<td><?php echo cut_string($_bill_to_address, 90)?></td>
	</tr>
</table><br />