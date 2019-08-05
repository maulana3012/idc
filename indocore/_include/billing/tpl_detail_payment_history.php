<table width="100%" class="table_n">
<?php 
if($currentDept != 'accounting') {
?>
	<tr>
		<th width="5%">#</th>
		<th>PAYMENT<br />DATE</th>
		<th width="10%">METHOD</th>
		<th width="10%">BANK</th>
		<th width="25%">REMARK</th>
		<th width="18%">INPUT</th>
		<th>AMOUNT<br />(Rp)</th>
		<th width="5%">DEL</th>
	</tr>
<?php
$i = 1;
while($payment =& fetchRow($pay_res)) { 
?>
	<tr>
		<td align="center"><?php echo $i++ ?></td>
		<td align="center"><?php echo $payment[1] ?></td>
		<td align="center"><?php echo strtoupper($payment[7]) ?></td>
		<td align="center"><?php echo strtoupper($payment[8]) ?></td>
		<td><?php echo $payment[3] ?></td>
		<td><?php echo $payment[4] . ", " . date('j-M-Y', strtotime($payment[5])) ?></td>
		<td align="right"><?php echo number_format((double)$payment[2], 2) ?></td>
		<td align="center">
		<?php if(substr($payment[6],0,7) == 'DEPOSIT' || $column['billing_used'] == 't') { ?>
			<img src="../../_images/icon/deletedisabled.gif" width="12" onclick="deletePayment(false,<?php echo  $payment[0] ?>,0,'deposit')">
		<?php } else { ?>
			<img src="../../_images/icon/delete.gif" width="12" onclick="deletePayment(true,<?php echo  $payment[0].",".$payment[10] ?>,'')">
		<?php } ?>
		</td>
	</tr>
<?php
	if(substr($payment[6],0,7) == 'DEPOSIT' && $payment[11]=='f')		{ $used_deposit = true; }
	if(strlen($payment[3]) != 11 && substr($payment[3],0,2) != 'RO')	{ $total_paid += $payment[2]; }
	$pay_amount['delivery'][1] += $payment[12];
}
?>
<?php } else { ?>
	<tr>
		<th width="5%">#</th>
		<th>PAYMENT<br />DATE</th>
		<th width="10%">METHOD</th>
		<th width="10%">BANK</th>
		<th width="25%">REMARK</th>
		<th width="18%">INPUT</th>
		<th>AMOUNT<br />(Rp)</th>
	</tr>
<?php
$i = 1;
while($payment =& fetchRow($pay_res)) { 
?>
	<tr>
		<td align="center"><?php echo $i++ ?></td>
		<td align="center"><?php echo $payment[1] ?></td>
		<td align="center"><?php echo strtoupper($payment[7]) ?></td>
		<td align="center"><?php echo strtoupper($payment[8]) ?></td>
		<td><?php echo $payment[3] ?></td>
		<td><?php echo $payment[4] . ", " . date('j-M-Y', strtotime($payment[5])) ?></td>
		<td align="right"><?php echo number_format((double)$payment[2], 2) ?></td>
	</tr>
<?php
	if (strlen($payment[3]) != 11 && substr($payment[3],0,2) != 'RO') $total_paid += $payment[2];
} 
?>
<?php } ?>
	<tr>
		<td colspan="6" align="right">TOTAL PAID</td>
		<td align="right"><input type="text" class="fmtn" style="width:80%" value="<?php echo number_format((double)$total_paid, 2) ?>" readonly></td>
		<td></td>
	</tr>
	<tr>
		<td colspan="6" align="right"><b style="color:red">LACK</b></td>
		<td align="right"><input type="text" class="fmtn" style="color:red;width:80%" value="<?php echo number_format((double)$column['bill_remain_amount'], 2) ?>" readonly></td>
		<td></td>
	</tr>
	<tr>
		<th colspan="6" align="right">TOTAL AMOUNT</th>
		<th align="right"><input type="text" class="fmtn" style="width:80%" value="<?php echo number_format((double)$column['bill_total_billing'], 2) ?>" readonly></th>
		<th></th>
	</tr>
</table><br />
<?php 
if($num_depa > 0) {
?>
<form name="frmDeductionPayment" method="post">
<input type="hidden" name="p_mode">
<input type="hidden" name="_depa_idx">
<strong>Deduction Info</strong>
<table width="50%" class="tbl_l">
	<thead>
	<tr>
		<th width="8%">#</th>
		<th>Description</th>
		<th width="25%">Amount<br />(Rp)</th>
		<th width="8%"></th>
	</tr>
	</thead>
	<?php $i = array(1,0); while($row =& fetchRow($depa_res)) { ?>
	<tr>
		<td align="center"><?php echo $i[0]++ ?></td>
		<td><?php echo $row[3] ?></td>
		<td align="right"><?php echo number_format((double)$row[4], 2) ?></td>
		<?php if($currentDept != 'accounting') { ?><td align="center"><a href="javascript:deleteDeductionPayment(<?php echo $row[0] ?>)"><img src="../../_images/icon/delete.gif" width="12"></a></td><?php } ?>
	</tr>
	<?php $i[1] += $row[4]; } ?>
	<tr>
		<td align="right" colspan="2">Total</td>
		<td><input type="text" name="totalDeductionH" class="fmtn" style="width:100%;font-weight:bold" value="<?php echo number_format((double)$i[1], 2) ?>" readonly></td>
		<td></td>
	</tr>
</table>
</form>
<?php } ?>