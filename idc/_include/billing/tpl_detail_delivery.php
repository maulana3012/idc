<table width="100%" class="table_sub">
    <tr>
        <th height="35"><img src="../../_images/icon/setting_mini.gif"> &nbsp; <strong>DELIVERY CONFIRM</strong></th>
        <td>
        	<i>
        	<?php echo ($column['bill_delivery_timestamp']=='')?'':'Confirm delivery by : '.ucfirst($column['bill_delivery_by_account']).', '.date('d-M-Y g:i:s',strtotime($column['bill_delivery_timestamp'])) ."<br />" ?>
        	<?php echo ($column['bill_cfm_delivery']=='')?'':'Confirm delivery charge by : '.ucfirst($column['bill_cfm_delivery_by']).', '.date('d-M-Y g:i:s',strtotime($column['bill_cfm_delivery']))  ?>
        	</i>
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
			<input type="hidden" name="p_mode" value="cfm_delivery">
			<input type="hidden" name="_code" value="<?php echo $column['bill_code']?>">
    		<table width="100%" class="table_box">
    			<?php if($column['bill_delivery_timestamp'] == ''){ ?>
    			<tr>
					<td width="15%">DATE</td>
					<td width="2%">:</td>
					<td width="20%"><input type="text" name="_delivery_date" class="reqd" value="<?php echo date('j-M-Y')?>" size="15"></td>
					<td width="15%">DELIVERY BY</td>
					<td width="2%">:</td>
					<td width="20%"><input type="text" name="_delivery_by" class="fmt" maxlength="32" value=""></td>
					<td align="right">
						<button name='btnCfmDelivery' class='input_btn' style='width:100px;'> <img src="../../_images/icon/btnSave-black.gif" align="middle"> &nbsp; Confirm</button>
					</td>
    			</tr>
    			<?php } else { ?>
    			<tr>
					<td width="15%">DATE</td>
					<td width="2%">:</td>
					<td width="20%"><?php echo date('d-M-Y',strtotime($column['bill_delivery_date'])) ?></td>
					<td width="15%">DELIVERY BY</td>
					<td width="2%">:</td>
					<td width="20%"><?php echo $column['bill_delivery_to_customer_by'] ?></td>
					<td align="right">
						<button name='btnCfmDelivery' class='input_btn' style='width:100px;' disabled> <img src="../../_images/icon/btnSave-black.gif" align="middle"> &nbsp; Confirm</button>
					</td>
    			</tr>
    			<?php } ?>
    		</table>
    		</form>
    	</td>
    </tr>
    <tr>
    	<td height="10"></td>
    </tr>
    <tr>
    	<td width="2%"><img src="../../_images/properties/p_leftmenu_icon02.gif"></td>
        <td><strong>Delivery Charge</strong></td>
    </tr>
    <tr>
    	<td></td>
    	<td>
			<form name="frmDeliveryCharge" method="post">
			<input type="hidden" name="p_mode" value="cfm_delivery_charge">
			<input type="hidden" name="_code" value="<?php echo $column['bill_code']?>">
			<table width="100%" class="table_box">
				<tr>
					<td width="16%">CHARGE</td>
					<td width="2%">:</td>
					<?php if($column['bill_cfm_delivery_by'] == ''){ ?>
					<td width="15%">Rp. <input type="text" name="_delivery_charge" class="reqn" size="15" value="<?php echo number_format((double)$column['bill_delivery_freight_charge'])?>" onKeyUp="formatNumber(this,'dot')"></td>
					<?php } else { ?>
					<td width="15%">Rp. <?php echo number_format((double)$column['bill_delivery_freight_charge'])?></td>
					<?php } ?>
					<td align="right">
						<button name='btnCfmDelivery' class='input_btn' style='width:100px;'<?php echo ($column['bill_cfm_delivery_by']=='')?'':' disabled' ?>> <img src="../../_images/icon/btnSave-black.gif" align="middle"> &nbsp; Confirm</button>
					</td>
				</tr>
			</table><br />
			</form>
			<?php 
			if($column['bill_cfm_delivery_by']!='') { 
				if($S->getValue("ma_authority") & 8) {
			?>
			<form name="frmUnDeliveryCharge" method="post">
			<input type="hidden" name="p_mode" value="uncfm_delivery_charge">
			<input type="hidden" name="_code" value="<?php echo $column['bill_code']?>">
			<table width="100%" class="table_box">
				<tr>
					<td width="5%" align="right"><img src="../../_images/icon/alert.gif"></td>
					<td width="20%"><b style="color:#C60000">Unconfirm delivery charge |</b></td>
					<td width="18%">Confirm new charge</td>
					<td width="2%">:</td>
					<td width="20%">
						<input type="radio" name="_is_confirm" value="1" onclick="chkIsConfirmDeli(1,this.checked)" checked>
						Rp. <input type="text" name="_deli_charge" class="reqn" size="15" value="<?php echo number_format((double)$column['bill_delivery_freight_charge'])?>" onKeyUp="formatNumber(this,'dot')">
					</td>
					<td><input type="radio" name="_is_confirm" value="0" onclick="chkIsConfirmDeli(0,this.checked)">&nbsp; Unconfirm</td>
					<td align="right">
						<button name='btnUnCfmDelivery' class='input_btn' style='width:100px;'> <img src="../../_images/icon/clean.gif" align="middle"> &nbsp; Modify</button>
					</td>
				</tr>
			</table><br /><br />
			</form>
			<script language="javascript" type="text/javascript">
			function chkIsConfirmDeli(value, o) {
				var f = window.document.frmUnDeliveryCharge;
				if(value == 1 && o == true) {
					f._deli_charge.className	= 'reqn';
					f._deli_charge.value		= '<?php echo number_format((double)$column['bill_delivery_freight_charge'])?>';
					f._deli_charge.readOnly		= false;
				} else if(value == 0 && o == true) {
					f._deli_charge.className	= 'fmtn';
					f._deli_charge.value		= '';
					f._deli_charge.readOnly		= 'readOnly';
				}
			}

			window.document.frmUnDeliveryCharge.btnUnCfmDelivery.onclick = function() {
				if(confirm("Are you sure to unconfirm delivery charge?")) {
					if(verify(window.document.frmUnDeliveryCharge)){
						window.document.frmUnDeliveryCharge.submit();
					}
				}
			}
			</script>
			<?php } } ?>
    	</td>
    </tr>
</table><br /><br />
<script language="javascript" type="text/javascript">
var f1 = window.document.frmCfmDelivery;
var f2 = window.document.frmDeliveryCharge;

f1.btnCfmDelivery.onclick = function() {
	if(confirm("Are you sure to confirm delivery?")) {
		if(verify(f1)){
			f1.submit();
		}
	}
}

f2.btnCfmDelivery.onclick = function() {
	if(confirm("Are you sure to confirm delivery charge?\nInputed charge : Rp. "+f2._delivery_charge.value)) {
		if(verify(f2)){
			f2.submit();
		}
	}
}
</script>