<table width="100%" class="table_box">
	<tr>
		<td colspan="3"><strong class="info">ORDER INFORMATION</strong></td>
		<td colspan="3" align="right">
			<I>Last updated by : <?php echo $column['ord_lastupdated_by_account'].date(', j-M-Y g:i:s', strtotime($column['ord_lastupdated_timestamp']))." Rev:".$column['ord_revision_time']?></I>
		</td>
	</tr>
	<tr>
		<th width="10%">CODE</th>
		<td>
			<strong><?php echo $column['ord_code']?></strong> &nbsp;
			<button name='btnChangeOrder' class='input_sky' style='width:60px;height:20px' onclick="window.location.href='change_type.php?_code=<?php echo $_code ?>'"><img src="../../_images/icon/move.gif" width="15px" align="middle" alt="Change type"></button>
		</td>
		<th width="12%">RECEIVED BY</th>
		<td><input name="_received_by" type="text" class="req" id="_received_by" value="<?php echo $column['ord_received_by']?>"></td>
		<th width="12%">CONFIRM BY</th>
		<td><input name="_confirm_by" type="text" value="<?php echo $column['ord_confirm_by']?>" class="fmt"></td>
	</tr>
	<tr>
		<th>PO DATE</th>
		<td><input type="text" name="_po_date" class="reqd" size="10" value="<?php echo date("j-M-Y", strtotime($column['ord_po_date']))?>"></td>
		<th>PO NO</th>
		<td><input name="_po_no" type="text" class="fmt" maxlength="64" value="<?php echo $column['ord_po_no']?>"></td>
		<th>VAT</th>
		<td><input name="_vat" type="text" class="fmtn" size="2" maxlength="4" value="<?php echo $column['ord_vat']?>">%</td>
	</tr>
</table>
<table width="100%" class="table_box" cellspacing="0">
	<tr height="30px">
		<th width="12%">&nbsp;</th>
		<th width="8%">CODE</th>
		<th width="12%">ATTN</th>
		<th>ADDRESS</th>
		<th width="2%"></th>
	</tr>
	<tr>
		<th>CUSTOMER</th>
		<td><input type="text" class="fmt" style="width:100%" name="_cus_to" value="<?php echo $column['ord_cus_to']?>"></td>
		<td><input type="text" class="fmt" style="width:100%" name="_cus_to_attn" value="<?php echo $column['ord_cus_to_attn']?>"></td>
		<td><input type="text" class="fmt" style="width:100%" name="_cus_to_address" value="<?php echo $column['ord_cus_to_address']?>"></td>
		<?php if($column['ord_cfm_deli_timestamp'] == '') { ?>
		<td><a href="javascript:fillCustomer('customer')"><img src="../../_images/icon/search_mini_2.gif"></a></td>
		<?php } ?>
	</tr>
	<tr>
		<th>SHIP TO</th>
		<td><input type="text" class="fmt" style="width:100%" name="_ship_to" value="<?php echo $column['ord_ship_to']?>"></td>
		<td><input type="text" class="fmt" style="width:100%" name="_ship_to_attn" value="<?php echo $column['ord_ship_to_attn']?>"></td>
		<td><input type="text" class="fmt" style="width:100%" name="_ship_to_address" value="<?php echo $column['ord_ship_to_address']?>"></td>
		<td><a href="javascript:fillCustomer('ship')"><img src="../../_images/icon/search_mini_2.gif"></a></td>
	</tr>
	<tr>
		<th>BILL TO</th>
		<td><input type="text" class="fmt" style="width:100%" name="_bill_to" value="<?php echo $column['ord_bill_to']?>"></td>
		<td><input type="text" class="fmt" style="width:100%" name="_bill_to_attn" value="<?php echo $column['ord_bill_to_attn']?>"></td>
		<td><input type="text" class="fmt" style="width:100%" name="_bill_to_address" value="<?php echo $column['ord_bill_to_address']?>"></td>
		<td><a href="javascript:fillCustomer('bill')"><img src="../../_images/icon/search_mini_2.gif"></a></td>
	</tr>
</table><br />