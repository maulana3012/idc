<?php if($_do_type == 'df') { //----------------------------------------------------------------- ?>
<table width="100%" class="table_sub">
    <tr>
        <th height="35"><img src="../../_images/icon/setting_mini.gif"> &nbsp; <strong>DELIVERY CONFIRM</strong></th>
        <td>
        	<i><?php echo ($column['df_delivery_timestamp']=='')?'':'Confirm delivery by : '.$column['df_delivery_confirmed_by'].', '.date('d-M-Y g:i:s',strtotime($column['df_delivery_timestamp'])) ."<br />" ?></i>
        </td>
    </tr>
</table>
<table width="100%" cellpadding="0">
	<tr>
		<td width="2%"><img src="../../_images/properties/p_leftmenu_icon02.gif"></td>
		<td><strong>Delivery to Customer</strong></td>
    </tr>
    <tr>
		<td></td>
		<td>
			<form name="frmCfmDelivery" method="post">
			<input type="hidden" name="p_mode" value="cfm_delivery_df">
			<input type="hidden" name="_code" value="<?php echo $column['df_code']?>">
    		<table width="100%" class="table_box">
    			<?php if($column['df_delivery_timestamp'] == ''){ ?>
    			<tr>
					<td width="15%">DATE</td>
					<td width="2%">:</td>
					<td width="20%"><input type="text" name="_delivery_date" class="reqd" value="<?php echo date('j-M-Y')?>" size="15"></td>
					<td width="15%">DELIVERY BY</td>
					<td width="2%">:</td>
					<td width="20%"><input type="text" name="_delivery_by" class="fmt" maxlength="32" value=""></td>
					<td align="right"><button name='btnCfmDelivery' class='input_btn' style='width:100px;'> <img src="../../_images/icon/btnSave-black.gif" align="middle"> &nbsp; Confirm</button></td>
    			</tr>
    			<?php } else { ?>
    			<tr>
					<td width="15%">DATE</td>
					<td width="2%">:</td>
					<td width="20%"><?php echo date('d-M-Y',strtotime($column['df_delivery_date'])) ?></td>
					<td width="15%">DELIVERY BY</td>
					<td width="2%">:</td>
					<td width="20%"><?php echo $column['df_delivery_to_customer_by'] ?></td>
					<td align="right"><button name='btnCfmDelivery' class='input_btn' style='width:100px;' disabled> <img src="../../_images/icon/btnSave-black.gif" align="middle"> &nbsp; Confirm</button></td>
    			</tr>
    			<?php } ?>
    		</table>
    		</form>
    	</td>
    </tr>
</table><br /><br />
<script language="javascript" type="text/javascript">
var f = window.document.frmCfmDelivery;

f.btnCfmDelivery.onclick = function() {
	if(confirm("Are you sure to confirm delivery?")) {
		if(verify(f)){
			f.submit();
		}
	}
}
</script>
<?php } else if($_do_type == 'dr') { //---------------------------------------------------------- ?>
<table width="100%" class="table_sub">
    <tr>
        <th height="35"><img src="../../_images/icon/setting_mini.gif"> &nbsp; <strong>DELIVERY CONFIRM</strong></th>
        <td>
        	<i><?php echo ($column['dr_delivery_timestamp']=='')?'':'Confirm delivery by : '.$column['dr_delivery_confirmed_by'].', '.date('d-M-Y g:i:s',strtotime($column['dr_delivery_timestamp'])) ."<br />" ?></i>
        </td>
    </tr>
</table>
<table width="100%" cellpadding="0">
	<tr>
		<td width="2%"><img src="../../_images/properties/p_leftmenu_icon02.gif"></td>
		<td><strong>Delivery to Customer</strong></td>
    </tr>
    <tr>
		<td></td>
		<td>
			<form name="frmCfmDelivery" method="post">
			<input type="hidden" name="p_mode" value="cfm_delivery_dr">
			<input type="hidden" name="_code" value="<?php echo $column['dr_code']?>">
    		<table width="100%" class="table_box">
    			<?php if($column['dr_delivery_timestamp'] == ''){ ?>
    			<tr>
					<td width="15%">DATE</td>
					<td width="2%">:</td>
					<td width="20%"><input type="text" name="_delivery_date" class="reqd" value="<?php echo date('j-M-Y')?>" size="15"></td>
					<td width="15%">DELIVERY BY</td>
					<td width="2%">:</td>
					<td width="20%"><input type="text" name="_delivery_by" class="fmt" maxlength="32" value=""></td>
					<td align="right"><button name='btnCfmDelivery' class='input_btn' style='width:100px;'> <img src="../../_images/icon/btnSave-black.gif" align="middle"> &nbsp; Confirm</button></td>
    			</tr>
    			<?php } else { ?>
    			<tr>
					<td width="15%">DATE</td>
					<td width="2%">:</td>
					<td width="20%"><?php echo date('d-M-Y',strtotime($column['dr_delivery_date'])) ?></td>
					<td width="15%">DELIVERY BY</td>
					<td width="2%">:</td>
					<td width="20%"><?php echo $column['dr_delivery_to_customer_by'] ?></td>
					<td align="right"><button name='btnCfmDelivery' class='input_btn' style='width:100px;' disabled> <img src="../../_images/icon/btnSave-black.gif" align="middle"> &nbsp; Confirm</button></td>
    			</tr>
    			<?php } ?>
    		</table>
    		</form>
    	</td>
    </tr>
</table><br /><br />
<script language="javascript" type="text/javascript">
var f = window.document.frmCfmDelivery;

f.btnCfmDelivery.onclick = function() {
	if(confirm("Are you sure to confirm delivery?")) {
		if(verify(f)){
			f.submit();
		}
	}
}
</script>
<?php } else if($_do_type == 'dt') { //---------------------------------------------------------- ?>
<table width="100%" class="table_sub">
    <tr>
        <th height="35"><img src="../../_images/icon/setting_mini.gif"> &nbsp; <strong>DELIVERY CONFIRM</strong></th>
        <td>
        	<i><?php echo ($column['dt_delivery_timestamp']=='')?'':'Confirm delivery by : '.$column['dt_delivery_confirmed_by'].', '.date('d-M-Y g:i:s',strtotime($column['dt_delivery_timestamp'])) ."<br />" ?></i>
        </td>
    </tr>
</table>
<table width="100%" cellpadding="0">
	<tr>
		<td width="2%"><img src="../../_images/properties/p_leftmenu_icon02.gif"></td>
		<td><strong>Delivery to Customer</strong></td>
    </tr>
    <tr>
		<td></td>
		<td>
			<form name="frmCfmDelivery" method="post">
			<input type="hidden" name="p_mode" value="cfm_delivery_dt">
			<input type="hidden" name="_code" value="<?php echo $column['dt_code']?>">
    		<table width="100%" class="table_box">
    			<?php if($column['dt_delivery_timestamp'] == ''){ ?>
    			<tr>
					<td width="15%">DATE</td>
					<td width="2%">:</td>
					<td width="20%"><input type="text" name="_delivery_date" class="reqd" value="<?php echo date('j-M-Y')?>" size="15"></td>
					<td width="15%">DELIVERY BY</td>
					<td width="2%">:</td>
					<td width="20%"><input type="text" name="_delivery_by" class="fmt" maxlength="32" value=""></td>
					<td align="right"><button name='btnCfmDelivery' class='input_btn' style='width:100px;'> <img src="../../_images/icon/btnSave-black.gif" align="middle"> &nbsp; Confirm</button></td>
    			</tr>
    			<?php } else { ?>
    			<tr>
					<td width="15%">DATE</td>
					<td width="2%">:</td>
					<td width="20%"><?php echo date('d-M-Y',strtotime($column['dt_delivery_date'])) ?></td>
					<td width="15%">DELIVERY BY</td>
					<td width="2%">:</td>
					<td width="20%"><?php echo $column['dt_delivery_to_customer_by'] ?></td>
					<td align="right"><button name='btnCfmDelivery' class='input_btn' style='width:100px;' disabled> <img src="../../_images/icon/btnSave-black.gif" align="middle"> &nbsp; Confirm</button></td>
    			</tr>
    			<?php } ?>
    		</table>
    		</form>
    	</td>
    </tr>
</table><br /><br />
<script language="javascript" type="text/javascript">
var f = window.document.frmCfmDelivery;

f.btnCfmDelivery.onclick = function() {
	if(confirm("Are you sure to confirm delivery?")) {
		if(verify(f)){
			f.submit();
		}
	}
}
</script>
<?php } ?>