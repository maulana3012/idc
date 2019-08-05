<br /><br /><div class="i_line">Payment Status</div>
<table width="70%" class="table_l">
<?php if ($column["lt_stamp"] > 0) { ?>
	<tr>
		<th width="25%">STAMP</th>
		<td><?php echo $column["lt_stamp"] ?> pcs</td>
		<td width="25%"><button name='btnConfirmStamp' class='input_btn' style='width:100%;'>CONFIRM</button></td>
		<?php if($column["lt_stamp_confirm"]!='') { ?>
		<td width="30%" align="right">Confirm by <?php echo $column["lt_stamp_confirmby"] . " at " . date('d-M-Y g:i:s',strtotime($column["lt_stamp_confirm"])) ?></td>
		<?php } else { ?>
		<?php } ?>
	</tr>
<script language="javascript" type="text/javascript">
	window.document.all.btnConfirmStamp.onclick = function() {
		if(confirm("Are you sure to confirm stamp replacement?")) {
			window.document.frmUpdate.p_mode.value = 'confirm_stamp';
			f.submit();
		}
	}
	<?php echo ($column["lt_stamp_confirm"]!='') ? "window.document.all.btnConfirmStamp.disabled=true;":"" ?>
</script>
<?php } ?>	
<?php if ($column["lt_amount"] > 0) { ?>
	<tr>
		<th>PAYMENT</th>
		<td>Rp <?php echo number_format($column["lt_amount"],0) ?></td>
		<td><button name='btnConfirmPayment' class='input_btn' style='width:100%;'>CONFIRM</button></td>
		<?php if($column["lt_amount_confirm"]!='') { ?>
		<td width="30%" align="right">Confirm by <?php echo $column["lt_amount_confirmby"] . " at " . date('d-M-Y g:i:s',strtotime($column["lt_amount_confirm"])) ?></td>
		<?php } ?>
	</tr>
<script language="javascript" type="text/javascript">
	window.document.all.btnConfirmPayment.onclick = function() {
		if(confirm("Are you sure to confirm payment?")) {
			window.document.frmUpdate.p_mode.value = 'confirm_payment';
			f.submit();
		}
	}
	<?php echo ($column["lt_amount_confirm"]!='') ? "window.document.all.btnConfirmPayment.disabled=true;":"" ?>
</script>
<?php } ?>
</table>