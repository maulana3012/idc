<table width="100%" class="table_sub">
    <tr>
        <th height="35"><img src="../../_images/icon/setting_mini.gif"> &nbsp; <strong>CONFIRM FIX BILLING NO ONLY</strong></th>
        <td>
            <i><?php echo ($column['bill_cfm_wh_delivery_timestamp']=='')?'':'Confirm by : '.ucfirst($column['bill_cfm_wh_delivery_by_account']).', '.date('d-M-Y g:i:s',strtotime($column['bill_cfm_wh_delivery_timestamp'])) ?></i>
        </td>
    </tr>
</table>
<table width="100%" cellpadding="0">
    <tr>
		<td></td>
		<td>
			<form name="frmCfmBillingOnly" method="post">
			<input type="hidden" name="_code" value="<?php echo $column['bill_code']?>">
			<input type="hidden" name="_bill_date" value="<?php echo $column['bill_inv_date']?>">
    		<table width="100%" class="table_box">
    			<?php if($column['bill_cfm_wh_delivery_timestamp'] == ''){ ?>
				<input type="hidden" name="p_mode" value="cfm_billing_only">
    			<tr>
			    <td width="15%">DATE</td>
			    <td width="2%">:</td>
			    <td width="20%"><input type="text" name="_wh_date" class="reqd" value="<?php echo date('j-M-Y')?>" size="15"></td>
			    <td align="right">
				<button name='btnCfmBillingOnly' class='input_btn' style='width:100px;'> <img src="../../_images/icon/btnSave-black.gif" align="middle"> &nbsp; Confirm</button>
			    </td>
    			</tr>
<script language="javascript" type="text/javascript">
var f = window.document.frmCfmBillingOnly;
f.btnCfmBillingOnly.onclick = function() {
	if(confirm("Are you sure to confirm billing?")) {
		if(verify(f)){
			f.submit();
		}
	}
}
</script>
    			<?php } else { ?>
    			<input type="hidden" name="p_mode" value="uncfm_billing_only">
    			<tr>
			    <td width="15%">DATE</td>
			    <td width="2%">:</td>
			    <td width="20%"><?php echo date('d-M-Y',strtotime($column['bill_cfm_wh_date'])) ?></td>
			    <td align="right">
				<button name='btnUncfmBillingOnly' class='input_btn' style='width:150px;'> <img src="../../_images/icon/btnSave-black.gif" align="middle"> &nbsp; Cancel Confirm</button>
			    </td>
    			</tr>
<script language="javascript" type="text/javascript">
var f = window.document.frmCfmBillingOnly;
f.btnUncfmBillingOnly.onclick = function() {
	if(confirm("Are you sure to unconfirm billing?")) {
		if(verify(f)){
			f.submit();
		}
	}
}
</script>
    			<?php } ?>
    		</table>
    		</form>
    	</td>
    </tr>
</table><br /><br />
