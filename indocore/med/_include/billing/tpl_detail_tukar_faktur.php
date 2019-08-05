<table width="100%" class="table_sub">
    <tr>
        <th height="35"><img src="../../_images/icon/setting_mini.gif"> &nbsp; <strong>TUKAR FAKTUR CONFIRM</strong></th>
        <td>
        	<i><?php echo ($column['bill_cfm_tukar_faktur']=='')?'':'Confirm tukar faktur by : '.ucfirst($column['bill_cfm_tukar_faktur_by']).', '.date('d-M-Y g:i:s',strtotime($column['bill_cfm_tukar_faktur']))  ?></i>
        </td>
    </tr>
</table>
<table width="100%" cellpadding="0">
 	<tr>
		<td width="2%"><img src="../../_images/properties/p_leftmenu_icon02.gif"></td>
		<td><strong>Tukar Faktur Information</strong></td>
    </tr>
    <tr>
		<td></td>
    	<td>
			<form name="frmTukarFaktur" method="post">
			<input type="hidden" name="p_mode" value="cfm_tukar_faktur">
			<input type="hidden" name="_code" value="<?php echo $column['bill_code']?>">
			<input type="hidden" name="_due_date" value="<?php echo $column['bill_payment_giro_due']?>">
			<table width="100%" class="table_box">
				<?php if($column['bill_cfm_tukar_faktur']=='') { ?>
				<tr>
					<td width="15%">DATE</td>
					<td width="2%">:</td>
					<td width="15%"><input type="text" name="_tukar_faktur_date" class="reqd" size="15" value="<?php echo date("j-M-Y", strtotime($column['bill_tukar_faktur_date']))?>" onBlur="setDueDateValue()"></td>
					<td align="right">
						<button name='btnCfmTukarFaktur' class='input_btn' style='width:100px;'> <img src="../../_images/icon/btnSave-black.gif" align="middle"> &nbsp; Confirm</button>
					</td>
				</tr>
				<?php } else { ?>
				<tr>
					<td width="15%">DATE</td>
					<td width="2%">:</td>
					<td width="15%"><?php echo date("j-M-Y", strtotime($column['bill_tukar_faktur_date']))?></td>
					<td align="right">
						<button name='btnCfmTukarFaktur' class='input_btn' style='width:100px;' disabled> <img src="../../_images/icon/btnSave-black.gif" align="middle"> &nbsp; Confirm</button>
					</td>
				</tr>
				<?php } ?>
			</table>
			</form>
    	</td>
    </tr>
</table><br /><br />
<script language="javascript" type="text/javascript">
function setDueDateValue() {

	var f = window.document.frmTukarFaktur; 

	if(window.document.frmInsert._payment_widthin_days.value.length == 0) {
		var add_days = 0;
	} else {
		var add_days	= parseInt(<?php echo  $column['bill_payment_widthin_days']?>);
	}

	var date_from = f._tukar_faktur_date.value;
	var due_date = parseDate(date_from, 'prefer_euro_format');
	due_date.setDate(due_date.getDate()+add_days);

	date_from = parseDate(date_from, 'prefer_euro_format');
	f._tukar_faktur_date.value = formatDate(date_from, 'd-NNN-yyyy')
	f._due_date.value = formatDate(due_date, 'd-NNN-yyyy');
	window.document.frmInsert._payment_giro_due.value = formatDate(due_date, 'd-NNN-yyyy');
}

window.document.frmTukarFaktur.btnCfmTukarFaktur.onclick = function() {
	if(confirm("Are you sure to confirm tukar faktur?")) {
		if(verify(window.document.frmTukarFaktur)){
			window.document.frmTukarFaktur.submit();
		}
	}
}
</script>