 	<tr height="30px" valign="bottom">
		<td width="2%"><img src="../../_images/properties/p_leftmenu_icon02.gif"></td>
		<td><strong>Payment Information</strong></td>
    </tr>
    <tr>
		<td></td>
    	<td>
			<form name="frmPayment" method="post" id="frmPayment">
			<input type="hidden" name="p_mode">
			<input type="hidden" name="_dept" value="<?php echo substr(strtoupper($currentDept),0,1) ?>">
			<input type="hidden" name="_code" value="<?php echo $column['bill_code']?>">
			<input type="hidden" name="_cus_code" value="<?php echo $column['bill_cus_to']?>">
			<input type="hidden" name="_over_paid">
			<input type="hidden" name="_pay_idx">
			<input type="hidden" name="_pay_ref">
			<input type="hidden" name="_remain_amount" value="<?php echo $column['bill_remain_amount'] ?>">
            <input type="hidden" name="_remain_amount_deli" value="<?php echo $pay_amount['delivery'][0]-$pay_amount['delivery'][1] ?>">
			<table width="100%" class="table_box">
				<tr>
					<td width="15%">PAYMENT DATE</td>
					<td width="2%">:</td>
					<td width="25%" colspan="3"><input type="text" class="reqd" name="_payment_date" size="15" value="<?php echo date("j-M-Y")?>"></td>
					<td width="14%">AMOUNT</td>
					<td width="2%">:</td>
					<td>
                    	Rp. <input type="text" class="reqn" name="_payment_paid" size="15" onKeyUp="formatNumber(this,'dot')" value="<?php echo number_format((double)$column['bill_remain_amount']) ?>"> &nbsp;
                        <input type="checkbox" name="hasFreightAmount" onclick="hasFreight(this)" /> DELIVERY &nbsp;: 
                        Rp. <input type="text" class="fmtn" name="_payment_paid_delivery" size="12" onKeyUp="formatNumber(this,'dot')" value="">
                    </td>
				</tr>
				<tr>
					<td>METHOD</td>
					<td>:</td>
					<td colspan="3">
						<input type="radio" name="_method" value="cash" <?php echo ($column['bill_payment_chk'] & 16) ? "checked":""?> onClick="enabledBankPayment(this, 'cash')" checked>Cash &nbsp;
						<input type="radio" name="_method" value="check" <?php echo ($column['bill_payment_chk'] & 32) ? "checked":""?> onClick="enabledBankPayment(this, 'check')">Check &nbsp;
						<input type="radio" name="_method" value="transfer" <?php echo ($column['bill_payment_chk'] & 64) ? "checked":""?> onClick="enabledBankPayment(this, 'transfer')">Transfer &nbsp;
						<input type="radio" name="_method" value="giro" <?php echo ($column['bill_payment_chk'] & 128) ? "checked":""?> onClick="enabledBankPayment(this, 'giro')">Giro &nbsp;
					</td>
					<td width="15%">REMARK</td>
					<td width="2%">:</td>
					<td><input type="text" name="_payment_remark" class="fmt" style="width:100%"></td>
				</tr>
				<tr>
					<td valign="top">BANK</td>
					<td valign="top">:</td>
                    <?php if(ZKP_SQL == 'IDC') { ?>
					<td>
						<input type="radio" name="_bank" value="BCA1" <?php echo ($column['bill_payment_bank'] == 'BCA1') ? 'checked' : '' ?> disabled>BCA 1<br />
						<input type="radio" name="_bank" value="BCA2" <?php echo ($column['bill_payment_bank'] == 'BCA2') ? 'checked' : '' ?> disabled>BCA 2
					</td>
					<td>
						<input type="radio" name="_bank" value="MANDIRI" <?php echo ($column['bill_payment_bank'] == 'MANDIRI') ? 'checked' : '' ?> disabled>Mandiri<br />
						<input type="radio" name="_bank" value="BII1" <?php echo ($column['bill_payment_bank'] == 'BII1') ? 'checked' : '' ?> disabled>BII 1
					</td>
					<td>
						<input type="radio" name="_bank" value="BII2" <?php echo ($column['bill_payment_bank'] == 'BII2') ? 'checked' : '' ?> disabled>BII 2<br />
						<input type="radio" name="_bank" value="DANAMON" <?php echo ($column['bill_payment_bank'] == 'DANAMON') ? 'checked' : '' ?> disabled>Danamon
					</td>
					<td valign="top">
						<input type="radio" name="_bank" value="BNIS" <?php echo ($column['bill_payment_bank'] == 'BNIS') ? 'checked' : '' ?> disabled>BNI Syariah<br />
					</td>
                    <?php
					} else if(ZKP_SQL == 'MED') {
						if ($column["bill_ordered_by"] == 1) {
					?>
					<td>
						<input type="radio" name="_bank" value="DANAMON2" <?php echo ($column['bill_payment_bank'] == 'DANAMON2') ? 'checked' : '' ?> disabled>DANAMON &nbsp; &nbsp;
						<input type="radio" name="_bank" value="BII3" <?php echo ($column['bill_payment_bank'] == 'BII3') ? 'checked' : '' ?> disabled>BII
					</td>
                    <?php } else { ?>
                    <td>
						<input type="radio" name="_bank" value="DANAMON3" <?php echo ($column['bill_payment_bank'] == 'DANAMON3') ? 'checked' : '' ?> disabled>DANAMON &nbsp; &nbsp;
					</td>
                    <?php } } ?>
					<td colspan="5" align="right" valign="bottom">
						<button name='btnSave' class='input_btn' style='width:130px;'> <img src="../../_images/icon/btnSave-blue.gif" align="middle"> &nbsp; Save payment</button>
					</td>
				</tr>
			</table><br />
            <table width="50%" class="tbl" id="deduction">
                <thead>
                <tr>
                    <th width="20%">Type</th>
                    <th>Deduction Description</th>
                    <th width="20%">Amount<br />(Rp.)</th>
					<th width="5%"></th>
                </tr>
                </thead>
                <tbody class="new_deduction"></tbody>
                <tfoot>
                    <td align="right" colspan="2">Total</td>
                    <td><input type="text" name="totalDeduction" id="totalDeduction" class="fmtn" style="width:100%;font-weight:bold" readonly></td>
                    <td align="center"><a href="#" id="add_new"><img src="../../_images/icon/add.png"></a></td>
                </tfoot>
            </table>
			<br />
            <input type="hidden" name="web_url" value="<?php echo ZKP_SQL ?>">
			</form>
    	</td>
    </tr>
<!-- start print deposit -->
<?php if ($column['bill_remain_amount'] > 0 && $dep_col[0] > 0) { ?>
    <tr height="30" valign="top">
    	<td></td>
    	<td>
    		<img src="../../_images/icon/dollar.gif"> 
    		Deposit <?php echo "[<b>".trim($column['bill_cus_to'])."</b>] ". $column['bill_cus_to_name'] ?> is Rp. <?php echo number_format((double)$dep_col[0],2) ?> &nbsp; <a href="javascript:seeReturn()"><i>(see detail)</i></a>
    	</td>
    </tr>
<?php } ?>
<!-- end print deposit -->