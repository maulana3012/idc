<?php
//deposit
$sqlDeposit = "
SELECT 
  pay_idx,
  to_char(pay_date,'DD-Mon-YY') AS date, 
  pay_paid AS amount,
  CASE
	WHEN pay_bank = '' THEN pay_method
	ELSE 					pay_method || ' > ' || pay_bank
  END AS method,
  pay_is_deposit_cross
FROM ".ZKP_SQL."_tb_payment 
WHERE bill_code ='$_code' AND SUBSTR(pay_note, 1, 7) = 'DEPOSIT'
ORDER BY pay_date";
$dep_res	=& query($sqlDeposit);
$used_cross_transfer	= false;
?>
	<tr height="30" valign="bottom">
		<td width="2%"><img src="../../_images/properties/p_leftmenu_icon02.gif"></td>
		<td><strong>Cross Transfer</strong></td>
    </tr>
	<tr>
		<td></td>
		<td>
<form name="frmDepositCrossTransfer" method="post">
<input type="hidden" name="p_mode">
<input type="hidden" name="_code" value="<?php echo $column['bill_code']?>">
<table width="100%">
	<tr>
		<td width="30%" valign="top">
			<table width="100%" class="table_n">
				<tr>
					<th width="10%"></th>
					<th width="35%">Date</th>
					<th>Method</th>
				</tr>
				<tbody id="depPosition">
<?php
while ($rows =& fetchRowAssoc($dep_res,0)) {
	if($rows["pay_is_deposit_cross"]=='f') {
?>
				<tr>
					<td>
						<input type="checkbox" name="chkPayIdx[]" value="<?php echo $rows["pay_idx"] ?>" onclick="updateDepAmount()">
						<input type="hidden" name="_amount[]" value="<?php echo $rows["amount"] ?>">
					</td>
					<td><?php echo $rows["date"] ?></td>
					<td><?php echo $rows["method"] ?></td>
				</tr>
<?php
	} else {
		$used_cross_transfer = true;
	}
}
?>
				</tbody>
			</table>
			<div><i class="comment">Checked amount : Rp. <span id="amountChk" style="font-weight:bold;color:#000">0</span></i></div>
		</td>
		<td valign="top">
			<table width="100%" class="table_box" border="1">
				<tr>
					<td width="18%">DATE</td>
					<td width="2%">:</td>
					<td><input type="text" class="reqd" name="_payment_date" size="15" value="<?php echo date("j-M-Y")?>"></td>
					<td width="18%">METHOD</td>
					<td width="2%">:</td>
					<td colspan="2">
						<input type="radio" name="_method" value="cash" onClick="enabledDepBankPayment(this, 'cash')" checked>Cash &nbsp;
						<input type="radio" name="_method" value="check" onClick="enabledDepBankPayment(this, 'check')">Check &nbsp;
						<input type="radio" name="_method" value="transfer" onClick="enabledDepBankPayment(this, 'transfer')">Transfer &nbsp;
						<input type="radio" name="_method" value="giro" onClick="enabledDepBankPayment(this, 'giro')">Giro &nbsp;
					</td>
				</tr>
				<tr>
					<td valign="top">BANK</td>
					<td valign="top">:</td>
					<td>
						<input type="radio" name="_bank" value="BCA1" disabled>BCA 1<br />
						<input type="radio" name="_bank" value="BCA2" disabled>BCA 2
					</td>
					<td colspan="2">
						<input type="radio" name="_bank" value="MANDIRI" disabled>Mandiri<br />
						<input type="radio" name="_bank" value="BII1" disabled>BII 1
					</td>
					<td width="20%">
						<input type="radio" name="_bank" value="BII2" disabled>BII 2<br />
						<input type="radio" name="_bank" value="DANAMON" disabled>Danamon
					</td>
					<td valign="top">
						<input type="radio" name="_bank" value="BNIS" disabled>BNI Syariah<br />
					</td>
				</tr>
				<tr>
					<td valign="top">REMARK</td>
					<td valign="top">:</td>
					<td colspan="4"><input type="text" class="fmt" name="_remark" style="width:100%"></td>
					<td align="right">
						<button name='btnSave' class='input_btn' style='width:80px;'> <img src="../../_images/icon/btnSave-blue.gif" align="middle"> &nbsp; Save</button>
					</td>
				</tr>
			</table><br />
		</td>
	</tr>
</table>
</form>
<script language="javascript" type="text/javascript">
function updateDepAmount() {
	var f		= window.document.frmDepositCrossTransfer;
	var e 		= window.document.frmDepositCrossTransfer.elements;
	var count	= window.depPosition.rows.length;
	var idxChx	= 2;		/////
	var idxAmount = 3;		/////
	var amount	= 0;

	for (var i=0; i<count; i++) {
		if(e(idxChx+i*2).type == "checkbox" && e(idxChx+i*2).checked) {
			amount	= amount + parseFloat(removecomma(e(idxAmount+i*2).value));
		}
	}
	var para = document.getElementById('amountChk');
	para.lastChild.nodeValue = numFormatval(amount+'',0);
}

function enabledDepBankPayment(o, method){
	var f = window.document.frmDepositCrossTransfer;
	if (o.checked == true) {
		if(method == 'transfer') {
			for(var i=0; i<7; i++) { f._bank[i].disabled = false; }
		} else if(method == 'check' || method == 'giro') {
			for(var i=0; i<4; i++) { f._bank[i].disabled = true; }
			for(var i=4; i<7; i++) { f._bank[i].disabled = false; }
			f._bank[6].checked	= true;
		} else {
			for(var i=0; i<7; i++) { f._bank[i].disabled = true; f._bank[i].checked	= false; }
		}
	} 
}

window.document.frmDepositCrossTransfer.btnSave.onclick = function() {
	var o = window.document.frmDepositCrossTransfer;

	var d = validDate(o._payment_date);
	var today = Date.parse("<?php echo date('M d, Y') ?>");
	var para = document.getElementById('amountChk');

	if(d.getTime() < today) {
		alert("Date cannot less than today");
		o._payment_date.value = "<?php echo date('d-M-Y') ?>";
		return;
	}

	var para_amount = parseFloat(removecomma(document.getElementById('amountChk').lastChild.nodeValue));
	if(para_amount <= 0) {
		alert("Please check at least one payment"); return;
	} else if(o._method[2].checked==true){
		var check = false;
		for (var i=0;i<7;i++) {
			if(o._bank[i].checked==true){ var check = true; }
		}
		if(check == false){
			alert("Please choose the bank"); return;
		}
	}

	if(confirm("Are you sure to save Cross Transfer")) {
		if(verify(o)) {
			o.p_mode.value = 'edit_payment';
			o.submit();
		}
	}
}
</script>
		</td>
	</tr>