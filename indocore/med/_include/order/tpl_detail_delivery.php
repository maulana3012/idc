<table width="100%" class="table_sub">
    <tr>
        <th height="35"><img src="../../_images/icon/setting_mini.gif"> &nbsp; <strong>DELIVERY CONFIRM</strong></th>
        <td>
        	<i><?php echo ($column['ord_cfm_deli_timestamp'] == '')?'':'Delivery confirm by : '.$column['ord_cfm_deli_by_account'].date(', j-M-Y g:i:s', strtotime($column['ord_cfm_deli_timestamp']))  ?></i>
        </td>
    </tr>
</table>
<table width="100%" cellpadding="0">
 	<tr>
		<td width="2%"><img src="../../_images/properties/p_leftmenu_icon02.gif"></td>
		<td><strong>Delivery Information</strong></td>
    </tr>
    <tr>
		<td></td>
    	<td>
			<form name="frmCfmDelivery" method="post">
			<input type="hidden" name="p_mode" value="confirm_deli">
			<input type="hidden" name="_code" value="<?php echo $column['ord_code']?>">
			<input type="hidden" name="_dept" value="<?php echo $column['ord_dept']?>">
			<input type="hidden" name="_cus_code" value="<?php echo $column['ord_cus_to']?>">
			<table width="100%" class="table_box">
				<?php if($column['ord_cfm_deli_timestamp']=='') { ?>
				<tr>
					<td width="15%">DATE</td>
					<td width="2%">:</td>
					<td width="15%"><input type="text" class="reqd" size="10" name="_delivery_date" value="<?php echo date("j-M-Y")?>"></td>
					<td width="15%">DELIVERY BY</td>
					<td width="2%">:</td>
					<td><input type="text" class="fmtd" size="30" name="_delivery_by_whom"></td>
					<td align="right">
						<button name='btnCfmDelivery' class='input_btn' style='width:100px;'> <img src="../../_images/icon/btnSave-black.gif" align="middle"> &nbsp; Confirm</button>
					</td>
				</tr>
				<?php } else { 
				$sql = "SELECT deli_date, deli_by  FROM ".ZKP_SQL."_tb_delivery WHERE ord_code = '$_code'";
				$result =& query($sql);
				$deli =& fetchRowAssoc($result);
				?>
				<tr>
					<td width="15%">DATE</td>
					<td width="2%">:</td>
					<td width="15%"><?php echo date('d-M-Y', strtotime($deli['deli_date']))?></td>
					<td width="15%">DELIVERY BY</td>
					<td width="2%">:</td>
					<td><?php echo $deli['deli_by'] ?></td>
					<td align="right">
						<button name='btnCfmDelivery' class='input_btn' style='width:100px;' disabled> <img src="../../_images/icon/btnSave-black.gif" align="middle"> &nbsp; Confirm</button>
					</td>
				</tr>
				<?php } ?>
			</table>
			</form>
    	</td>
    </tr>
</table><br /><br />
<script language="javascript" type="text/javascript">
window.document.frmCfmDelivery.btnCfmDelivery.onclick = function() {

	var po_date = parseDate('<?php echo $column['ord_po_date']?>', 'prefer_euro_format');
	var deli_date = validDate(window.document.frmCfmDelivery._delivery_date);

	if(!deli_date) {
		return;
	} else if (deli_date.getTime() < po_date.getTime()) {
		alert("You cannot deliver the goods before po date.");
		window.document.frmCfmDelivery._delivery_date.value = '';
		return;
	}

	if(confirm("Are you sure to confirm delivery?")) {
		window.document.frmCfmDelivery.submit();
	}
}
</script>