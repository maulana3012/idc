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
	var win = window.open(
		'./p_list_cus_code.php?_check_code='+ keyword,
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
	//	f._ship_to
	}
}

function initPageInput(v_access) {
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