<?php include "tpl_detail_payment_sql.php"; ?>
<table width="100%" class="table_sub">
    <tr>
        <th height="35"><img src="../../_images/icon/setting_mini.gif"> &nbsp; <strong>PAYMENT CONFIRM</strong></th>
    </tr>
</table>
<table width="100%" cellpadding="0">
	<?php 
	if($currentDept != 'accounting') { include "tpl_detail_payment_input.php"; } ?>
 	<tr height="20">
		<td width="2%"><img src="../../_images/properties/p_leftmenu_icon02.gif"></td>
		<td><strong>Payment History</strong></td>
    </tr>
    <tr>
    	<td></td>
		<td>
		<?php
		if (numQueryRows($pay_res) <= 0) { echo "\t\t\t<span class=\"comment\"><i>(No recorded payment)</i></span>"; }
		else 							 { include "tpl_detail_payment_history.php"; }
		?>
		</td>
    </tr>
	<?php 
	if($used_deposit && $currentDept != 'accounting') {  include "tpl_detail_payment_deposit.php"; } 
	?>
</table><br /><br />
<script language="javascript" type="text/javascript">
function defaultPaymentConfirm() {
	var f = window.document.frmPayment;

	if(f._method[2].checked) {
		for(i=0; i<7; i++) {
			f._bank[i].disabled = false;
		}
	} else if(f._method[1].checked || f._method[3].checked) {
		for(i=0; i<7; i++) {
			if(i<4) { f._bank[i].disabled = true; }
			else    { f._bank[i].disabled = false; }
		}
	}

<?php if ($column['bill_remain_amount'] <= 0) { ?>
	//when amount is 0
	f.btnSave.disabled = true;
	f._payment_date.className	= 'fmt';
	f._payment_paid.className	= 'fmt';
	f._payment_date.disabled	= true;
	f._payment_paid.disabled	= true;
	f._payment_remark.disabled	= true;
	f._payment_date.value		= '';
	f._payment_paid.value		= '';
	for(i=0; i<7; i++) {
		if(i<4) {
			f._method[i].disabled	= true;
			f._method[i].checked	= false;
		}
		f._bank[i].disabled = true;
		f._bank[i].checked = false	;
	}
<?php }  ?>
}

function enabledBankPayment(o, method){
	var f = window.document.frmPayment;
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

function seeReturn() {

	var x = (screen.availWidth - 470) / 2;
	var y = (screen.availHeight - 600) / 2;
	var bill_code = window.document.frmInsert._code.value;
	var cus_to    = window.document.frmInsert._cus_to.value;
	var remain_billing = removecomma(window.document.frmPayment._payment_paid.value);

	if(cus_to.length <= 0) {
		alert("You have to fill customer first");
		window.document.frmInsert._cus_to.focus();
	}

	var win = window.open(
		'./p_detail_return.php?_bill_code='+bill_code+'&_cus_to='+cus_to+'&_remain_billing='+remain_billing,
		'',
		'scrollbars,width=450,height=450,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

function deletePayment(value,idx,ref,reason) {
	if(value) {
		if(confirm("Are you sure to delete?")) {
			window.document.frmPayment.p_mode.value = 'delete_payment';
			window.document.frmPayment._pay_idx.value    = idx;
			window.document.frmPayment._pay_ref.value    = ref;
			window.document.frmPayment.submit();
		}
	} else if(reason == 'deposit') {
		alert("You just can delete this payment from Deposit history");
	} 
}

<?php if($currentDept != 'accounting') { ?>
window.document.frmPayment.btnSave.onclick = function() {
	var o = window.document.frmPayment;

	var d = validDate(o._payment_date);
	var today = Date.parse("<?php echo date('M d, Y') ?>");

	if(d.getTime() < today) {
		alert("Payment date cannot less than today");
		o._payment_date.value = "<?php echo date('d-M-Y') ?>";
		return;
	}

	if(parseFloat(removecomma(o._payment_paid.value)) > parseFloat((<?php echo $column['bill_remain_amount']?>))) {
		alert("Payment paid cannot more than remain billing");
		o._payment_paid.value = addcomma(<?php echo $column['bill_remain_amount']?>);
		o._payment_paid.focus();
		return;
	}

	if(o._method[2].checked==true){
		var check = false;
		for (var i=0;i<6;i++) {
			if(o._bank[i].checked==true){
				var check = true;
			}
		}
		if(check == false){
			alert("Please choose the bank");
			return;
		}
	}

	if(confirm("Are you sure to save payment")) {
		if(verify(o)) {
			o.p_mode.value = 'add_payment';
			o.submit();
		}
	}
}
<?php } ?>

function addDeduction() {

	// check element
	var f = window.document.frmPayment;
	if(trim(f._de_desc.value).length <= 0) 	 { alert("Please complete the deduction description"); f._de_desc.focus(); return; }
	else if (f._de_amount.value.length <= 0) { alert("Please complete the deduction amount"); f._de_amount.focus(); return; }

	// print row
	var oTr = window.document.createElement("TR");
	var oTd	= new Array(); var oText = new Array();
	oTd[0]		= window.document.createElement("TD");
	oTd[1]		= window.document.createElement("TD");
	oTd[2]		= window.document.createElement("TD");
	oText[0]	= window.document.createElement("INPUT");
	oText[1]	= window.document.createElement("INPUT");
	oText[2]	= window.document.createElement("INPUT");

	oText[0].type = "text";
	oText[0].style.width = "100%";
	oText[0].name = "_deduction_desc[]";
	oText[0].maxlength = "3";
	oText[0].className = "req";
	oText[0].value = f._de_desc.value;
	f._de_desc.value = '';

	oText[1].type = "text";
	oText[1].style.width = "100%";
	oText[1].name = "_deduction_amount[]";
	oText[1].size = "40";
	oText[1].className = "reqn";
	oText[1].value = f._de_amount.value;
	oText[1].onblur = function() {updateDeductionAmount();}
	oText[1].onkeyup = function() {formatNumber(this, 'dot');}
	f._de_amount.value = '';

	oText[2].type = "button";
	oText[2].style.width = "100%";
	oText[2].name = "btnDelDeduction";
	oText[2].value = " - ";
	oText[2].className = "fmt";
	oText[2].onclick = function () {
		var oRow = this.parentElement.parentElement;
		window.deductionPosition.removeChild(oRow);
		updateDeductionAmount();
	}

	oTd[0].appendChild(oText[0]);
	oTd[1].appendChild(oText[1]);
	oTd[2].appendChild(oText[2]);
	oTr.appendChild(oTd[0]);
	oTr.appendChild(oTd[1]);
	oTr.appendChild(oTd[2]);

	window.deductionPosition.appendChild(oTr);
	f._de_desc.focus();
	updateDeductionAmount();

}

function updateDeductionAmount() {
	var f			= window.document.frmPayment;
	var e 			= window.document.frmPayment.elements;
	var count		= window.deductionPosition.rows.length;
	var numInput	= 3;		/////
	var idx_amount	= 26;		/////

	var totalAmount = 0;
	for (var i=0; i<count; i++) {
		var amount = parseFloat(removecomma(e(idx_amount+i*numInput).value));
		totalAmount	+= amount;
	}
	f.totalDeduction.value   = numFormatval(totalAmount + '', 0);

}

function deleteDeductionPayment(idx) {
	if(confirm("Are you sure to delete payment deduction?")) {
		window.document.frmDeductionPayment.p_mode.value = 'delete_payment_deduction';
		window.document.frmDeductionPayment._depa_idx.value = idx;
		window.document.frmDeductionPayment.submit();
	}
}
</script>