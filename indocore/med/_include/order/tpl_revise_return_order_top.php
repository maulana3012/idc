<table width="100%" class="table_box">
	<tr>
		<td colspan="2"><strong class="info">RETURN INFORMATION</strong></td>
		<td colspan="6" align="right">
			<I>Last updated by : <?php echo $column['reor_lastupdated_by_account'].date(', j-M-Y g:i:s', strtotime($column['reor_lastupdated_timestamp']))." Rev:".$column['reor_revesion_time']?></I>
		</td>
	</tr>
	<tr>
		<th width="12%">CODE</th>
		<td width="20%"><strong><?php echo $column['reor_code']?></strong></td>
		<th width="12%">RECEIVED BY</th>
		<td><input name="_received_by" type="text" class="req" id="_received_by" value="<?php echo $column['reor_received_by']?>"></td>
		<th width="12%">CONFIRM BY</th>
		<td><input name="_confirm_by" type="text" value="<?php echo $column['reor_confirm_by']?>" class="fmt"></td>
	</tr>
	<tr>
		<th>PO DATE</th>
		<td><input type='text' name="_po_date" class="reqd" value="<?php echo date("j-M-Y", strtotime($column['reor_po_date']))?>"></td>
		<th>PO NO</th>
		<td><input name="_po_no" type="text" class="fmt" maxlength="64" value="<?php echo $column['reor_po_no']?>"></td>
		<th>VAT</th>
		<td><input name="_vat" type="text" class="fmtn" size="2" maxlength="4" value="<?php echo $column['reor_vat']?>">%</td>
	</tr>
	<tr>
		<th>TYPE</th>
		<td><?php echo  ($column['reor_type'] == 'RO') ? 'Sales' : 'Konsinyasi'?></td>
		<th>ORDER</th>
		<td><strong><a href="revise_order.php?_code=<?php echo $column['ord_code']?>" target="_blank"><?php echo $column['ord_code']?></a></strong></td>
		<th>DATE</th>
		<td><?php echo ($column['reor_ord_reference_date'] == '') ? '' : date('j-M-Y', strtotime($column['reor_ord_reference_date']))?></td>
		<td colspan="2" align="right"><button name="btnDetailOrder" onClick="seeDetailOrdRef()" class="fmt" <?php echo ($column['ord_code'] == '') ? 'disabled' : '' ?>>DETAIL</button></td>
	</tr>
</table>
<table width="100%" class="table_box" cellspacing="0">
	<tr height="30px">
		<th width="10%">&nbsp;</th>
		<th width="8%">CODE</th>
		<th width="18%">ATTN</th>
		<th width="65%" colspan="2">ADDRESS</th>
	</tr>
	<tr>
		<th>CUSTOMER</th>
		<td><input type="text" class="fmt" style="width:100%" name="_cus_to" value="<?php echo $column['reor_cus_to']?>" readonly></td>
		<td><input type="text" class="fmt" style="width:100%" name="_cus_to_attn" value="<?php echo $column['reor_cus_to_attn']?>"></td>
		<td><input type="text" class="fmt" style="width:100%" name="_cus_to_address" value="<?php echo $column['reor_cus_to_address']?>"></td>
		<td></td>
	</tr>
	<tr>
		<th>SHIP TO</th>
		<td><input type="text" class="fmt" style="width:100%" name="_ship_to" value="<?php echo $column['reor_ship_to']?>"></td>
		<td><input type="text" class="fmt" style="width:100%" name="_ship_to_attn" value="<?php echo $column['reor_ship_to_attn']?>"></td>
		<td><input type="text" class="fmt" style="width:100%" name="_ship_to_address" value="<?php echo $column['reor_ship_to_address']?>"></td>
		<td><a href="javascript:fillCustomer('ship')"><img src="../../_images/icon/search_mini_2.gif"></a></td>
	</tr>
	<tr>
		<th>BILL TO</th>
		<td><input type="text" class="fmt" style="width:100%" name="_bill_to" value="<?php echo $column['reor_bill_to']?>"></td>
		<td><input type="text" class="fmt" style="width:100%" name="_bill_to_attn" value="<?php echo $column['reor_bill_to_attn']?>"></td>
		<td><input type="text" class="fmt" style="width:100%" name="_bill_to_address" value="<?php echo $column['reor_bill_to_address']?>"></td>
		<td><a href="javascript:fillCustomer('bill')"><img src="../../_images/icon/search_mini_2.gif"></a></td>
	</tr>
</table><br />