<?php include "tpl_detail_payment_sql.php"; ?>
<table width="100%" class="table_sub">
    <tr>
        <th height="35"><img src="../../_images/icon/setting_mini.gif"> &nbsp; <strong>PAYMENT CONFIRM</strong></th>
    </tr>
</table>
<table width="100%" cellpadding="0">
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
	<?php if($currentDept != 'accounting') { include "tpl_detail_payment_input.php"; } ?>
	<?php 
	if($used_deposit && $currentDept != 'accounting') {  include "tpl_detail_payment_deposit.php"; } 
	?>
</table><br /><br />
<script language="javascript" type="text/javascript">
function defaultPaymentConfirm() {
	var f = window.document.frmPayment;

	if(f.web_url.value == 'IDC') {
		if(f._method[2].checked) {
			for(i=0; i<7; i++) {
				f._bank[i].disabled = false;
			}
		} else if(f._method[1].checked || f._method[3].checked) {
			for(i=0; i<7; i++) {
				if(i<7) { f._bank[i].disabled = true; }
				else    { f._bank[i].disabled = false; }
			}
		}
	} else if(f.web_url.value == 'MED') {
		if(f._method[0].checked || f._method[1].checked || f._method[2].checked) {
			for(i=0; i<2; i++) {
				f._bank[i].disabled = false;
			}
		}
	}

	if(f._remain_amount.value <= 0) {
		//when amount is 0
		f.btnSave.disabled = true;
		f._payment_date.className	= 'fmt';
		f._payment_paid.className	= 'fmt';
		f._payment_date.disabled	= true;
		f._payment_paid.disabled	= true;
		f._payment_remark.disabled	= true;
		f._payment_date.value		= '';
		f._payment_paid.value		= '';
	
		for(i=0; i<4; i++) {
			f._method[i].disabled	= true;
			f._method[i].checked	= false;
		}
		if(f.web_url.value == 'IDC') {
			for(i=0; i<7; i++) {
				f._bank[i].disabled = true;
				f._bank[i].checked = false	;
			}
		} else if(f.web_url.value == 'MED') {
			for(i=0; i<2; i++) {
				f._bank[i].disabled = true;
				f._bank[i].checked = false	;
			}
		}
	}

	if (f._remain_amount_deli.value > 0) {
		f.hasFreightAmount.disabled = false;
		f._payment_paid_delivery.disabled = false;
		f.hasFreightAmount.checked = true;
		f._payment_paid_delivery.className = 'reqn';
		f._payment_paid_delivery.value = numFormatval(f._remain_amount_deli.value+'',0);
	} else {
		f.hasFreightAmount.disabled = true;
		f._payment_paid_delivery.disabled = true;
		f.hasFreightAmount.checked = false;
	}
}

function enabledBankPayment(o, method){
	var f = window.document.frmPayment;
	if (o.checked == true) {
		if(f.web_url.value == 'IDC') {
			if(method == 'transfer') {
				for(var i=0; i<7; i++) { f._bank[i].disabled = false; }
			} else if(method == 'check' || method == 'giro') {
				for(var i=0; i<4; i++) { f._bank[i].disabled = true; }
				for(var i=0; i<7; i++) { f._bank[i].disabled = false; }
				f._bank[6].checked	= true;
			} else {
				for(var i=0; i<7; i++) { f._bank[i].disabled = true; f._bank[i].checked	= false; }
			}
		} else if(f.web_url.value == 'MED') {
			if(method == 'cash') {
				for(var i=0; i<2; i++) { f._bank[i].disabled = true; f._bank[i].checked	= false; }
			} else {
				for(var i=0; i<2; i++) { f._bank[i].disabled = false; }
			}
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

	if(o.web_url.value == 'IDC') {
		if(o._method[2].checked==true){
			var check = false;
			for (var i=0;i<7;i++) { if(o._bank[i].checked==true) var check = true; }
			if(check == false){
				alert("Please choose the bank");
				return;
			}
		}
	} else if(o.web_url.value == 'MED') {
		if(o._method[0].checked==false){
			var check = false;
			for (var i=0;i<2;i++) { if(o._bank[i].checked==true) var check = true; }
			if(check == false){
				alert("Please choose the bank");
				return;
			}
		}
	}

	var countRow = $("#deduction >tbody > tr").length;
	if(countRow > 0) {
		var $inputs = $('#deduction :input');
		var val = true;
		$inputs.each(function() {
			switch(this.name) {
				case '_deduction_desc[]': 
					if($(this).val() == '') val = false;
					break;
				case '_deduction_amount[]': 
					if($(this).val() == '0') val = false;
					break;
			}
		});
		if(!val) {
			alert("You have to fill both deduction info & amount");
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

$(document).ready(function(){
	id = 0;
	$("#add_new").click( function(){
		if($("input:hidden[name=_remain_amount]").val() <= 0) {
			return false;
		}
		var add_new = '<tr id="deduction_'+id+'">'+
				'<td>'+
					'<select name="_deduction_type[]" id="new_deduction">'+
					'<option value="1">Return</option>'+
					'<option value="2" selected="selected">Pot. Tagihan</option>'+
					'</select>'+
				'</td>'+
				'<td><input type="text" name="_deduction_desc[]" class="fmt" style="width:100%"></td>'+
				'<td><input type="text" name="_deduction_amount[]" class="fmtn" style="width:100%" onKeyUp="formatNumber(this, \'dot\');" onBlur="calc_deduction()" value="0"></td>'+
				'<td align="center"><a href="#" onclick="return delete_deduction('+id+')">-</a></td>'+
			'</tr>';
		$(".new_deduction").append(add_new);  
		id++;
		calc_deduction()
		return false;
	});

	$("input:text[name=_payment_paid]").blur( function(){
		if(parseInt(removecomma($("input:text[name=_payment_paid]").val())) > parseInt($("input:hidden[name=_remain_amount]").val())) {
			alert("Payment paid cannot more than remain billing");
			$("input:text[name=_payment_paid]").val(addcomma(removecomma($("input:hidden[name=_remain_amount]").val())));
			$("input:text[name=_payment_paid]").focus();
			return;
		}
	});	

	$("input:text[name=_payment_paid_delivery]").blur( function(){
		if(parseInt(removecomma($("input:text[name=_payment_paid_delivery]").val())) > parseInt($("input:hidden[name=_remain_amount_deli]").val())) {
			alert("Delivery paid cannot more than remain delivery");
			$("input:text[name=_payment_paid_delivery]").val(addcomma($("input:hidden[name=_remain_amount_deli]").val()));
			$("input:text[name=_payment_paid_delivery]").focus();
			return;
		}
	});

	calc_deduction()
});

function delete_deduction(id) {
	$('#deduction_'+id).remove();
	calc_deduction()
	return false;
}


function calc_deduction() {
	var countRow = $("#deduction >tbody > tr").length;
	var _payment_paid = parseInt(removecomma($('input:text[name=_payment_paid]').val()));
	var total = 0;
	if(countRow > 0) {
		var $inputs = $('#deduction :input');
		$inputs.each(function() {
			switch(this.name) {
				case '_deduction_amount[]': 
					if($(this).val() == '') $(this).val(0)
					total += parseInt(removecomma($(this).val()));
					break;
			}

			if(total > _payment_paid) {
				alert('Total payment deduction cannot bigger than payment paid');
				total -= parseInt(removecomma($(this).val()));
				$(this).val(0);
				return;
			}
		});
		$("#totalDeduction").val(numFormatval(total+''))
	} else {
		$("#totalDeduction").val(0)
	}
	//http://www.codeboss.in/web-funda/2009/05/27/jquery-validation-for-array-of-input-elements/
}

function deleteDeductionPayment(idx) {
	if(confirm("Are you sure to delete payment deduction?")) {
		window.document.frmDeductionPayment.p_mode.value = 'delete_payment_deduction';
		window.document.frmDeductionPayment._depa_idx.value = idx;
		window.document.frmDeductionPayment.submit();
	}
}

function hasFreight(o) {
	var f = window.document.frmPayment;
	if(o.checked) {
		f._payment_paid_delivery.disabled = false;
		f._payment_paid_delivery.value = numFormatval(f._remain_amount_deli.value+'',0);
		f._payment_paid_delivery.className = 'reqn';
	} else {
		f._payment_paid_delivery.disabled = true;
		f._payment_paid_delivery.value = '';
		f._payment_paid_delivery.className = 'fmtn';
	}
}
</script>