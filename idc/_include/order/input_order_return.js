function fillOrder() {

	var x = (screen.availWidth - 470) / 2;
	var y = (screen.availHeight - 600) / 2;
	var cus_to		= window.document.frmInsert._ship_to.value;
	var cus_name	= window.document.frmInsert._ship_to_attn.value;

	if(cus_to.length <= 0) {
		alert("You have to fill customer first");
		window.document.frmInsert._ship_to.focus();
		return;
	}

	var win = window.open(
		'./p_list_order.php?_cus_code='+ trim(cus_to) + '&_cus_name=' + cus_name ,
		'',
		'scrollbars,width=550,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

function fillCustomer(target) {
	if (target == 'customer') {
		keyword = window.document.frmInsert._cus_to.value;
	} else if (target == 'ship') {
		keyword = window.document.frmInsert._ship_to.value;
	} else if (target == 'bill') {
		keyword = window.document.frmInsert._bill_to.value;
	}

	var x = (screen.availWidth - 400) / 2;
	var y = (screen.availHeight - 600) / 2;
	var dept = window.document.frmInsert._dept.value;
	var win = window.open(
		'../../_include/order/p_list_cus_code.php?_dept='+dept+'&_check_code='+ keyword,
		target,
		'scrollbars,width=400,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

function copyCustomer(o, target) {
	var f = window.document.frmInsert;

	if (target == 'ship') {
		if(o.checked) {
			f._ship_to.value = f._cus_to.value;
			f._ship_to_attn.value = f._cus_to_attn.value;
			f._ship_to_address.value = f._cus_to_address.value;
		} else {
			f._ship_to.value = "";
			f._ship_to_attn.value = "";
			f._ship_to_address.value = "";
		}
	} else if (target == 'bill') {
		if(o.checked) {
			f._bill_to.value = f._cus_to.value;
			f._bill_to_attn.value = f._cus_to_attn.value;
			f._bill_to_address.value = f._cus_to_address.value;
		} else {
			f._bill_to.value = "";
			f._bill_to_attn.value = "";
			f._bill_to_address.value = "";
		}
	}

	if (o.check) {
		f._ship_to
	}
}

function seeDetailOrdRef() {

	var x = (screen.availWidth - 470) / 2;
	var y = (screen.availHeight - 600) / 2;
	var ord_code = window.document.frmInsert._ord_code.value;

	var win = window.open(
		'./p_detail_order.php?_code='+ ord_code,
		'',
		'scrollbars,width=550,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

function initPageInput(v_access) {

	updateAmount();

	if(v_access == 'IDC') {
		if(window.document.frmInsert._dept.value == 'A') {
			window.document.frmInsert._sign_by.value = 'Riani Kurniati';
		}
	} else if(v_access == 'MED') {
		if(window.document.frmInsert._dept.value == 'A') {
			window.document.frmInsert._sign_by.value = 'Neneng Sri P.';
		}
	}
}