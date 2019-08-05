function fillCustomer(target) {
	if (target == 'customer') {
		keyword = window.document.frmInsert._cus_to.value;
	} else if (target == 'ship') {
		keyword = window.document.frmInsert._ship_to.value;
	} else if (target == 'pajak') {
		if(window.document.frmInsert._vat.disabled ==  true) {
			alert("You choosed NON VAT, so you need not to fill this column");
			return;
		}
		keyword = window.document.frmInsert._pajak_to.value;
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

	if(target == 'ship') {
		if(o.checked) {
			f._ship_to.value = f._cus_to.value;
			f._ship_name.value = f._cus_name.value;
			f._ship_to_responsible_by.value = f._cus_to_responsible_by.value;
		} else {
			f._ship_to.value = "";
			f._ship_name.value = "";
		}
	}
}

function checkTukarFaktur(){
	var f = window.document.frmInsert;

	if(f._payment_sj_inv_fp_tender.value == 'Tukar Faktur') {
		f._tukar_faktur_date.disabled = false;
		f._tukar_faktur_date.className = 'reqd';
	} else {
		f._tukar_faktur_date.disabled = true;
		f._tukar_faktur_date.className = 'fmt';
		f._tukar_faktur_date.value = '';
	}
}

function enabledText(o, value) {
	var f = window.document.frmInsert;

	if (value == 'freight_charge') {
		if(o.checked == true) {
			f._delivery_freight_charge.disabled = false;
			f._delivery_freight_charge.className = 'reqn';
			f._delivery_freight_charge.focus();
		} else {
			f._delivery_freight_charge.disabled = true;
			f._delivery_freight_charge.className = 'fmt';
			f._delivery_freight_charge.value = '';
		}
	} else if (value == 'cash') {
		if(o.checked == true) {
			f._payment_cash_by.disabled = false;
			f._payment_cash_by.focus();
		} else {
			f._payment_cash_by.disabled = true;
			f._payment_cash_by.value = '';
		}
	}  else if (value == 'check') {
		if(o.checked == true) {
			f._payment_check_by.disabled = false;
			f._payment_check_by.focus();
		} else {
			f._payment_check_by.disabled = true;
			f._payment_check_by.value = '';
		}
		enabledBankOption(o);
	}  else if (value == 'transfer') {
		if(o.checked == true) {
			f._payment_transfer_by.disabled = false;
			f._payment_transfer_by.focus();
		} else {
			f._payment_transfer_by.disabled = true;
			f._payment_transfer_by.value = '';
		}
		enabledBankOption(o);
	}
}

function enabledBankOption(o) {
	var f = window.document.frmInsert;

	if(f.web_url.value == 'IDC') {
		if (o.checked == true) {
			for(var i=0; i<2; i++) f._bank[i].disabled = false; 
		} else if (o.checked == false) {
			for(var i=0; i<6; i++) {
				f._bank[i].disabled   = true;
				f._bank[i].checked	  = false;
			}
			f._bank_address.value = '';
		}
	} else if(f.web_url.value == 'MED') {
		if (f._ordered_by.value == 1) {
			if (o.checked == true) {
				for(var i=0; i<2; i++) f._bank[i].disabled = false; 
			} else if (o.checked == false) {
				for(var i=0; i<2; i++) {
					f._bank[i].disabled   = true;
					f._bank[i].checked	  = false;
				}
				f._bank_address.value = '';
			}
		} else if (f._ordered_by.value == 2) {
			if (o.checked == true)
				f._bank.disabled = false;
				
			else if (o.checked == false) {
				f._bank.disabled = true;
				f._bank.checked = false;
				f._bank_address.value = '';
			}
		}
	}
}

function bankDesc(o) {
	var f = window.document.frmInsert;

	if (o == "BCA1") {
		f._bank_address.value = "BCA KCU WISMA GKBI\nA/N\t: PT. INDOCORE PERKASA\nA/C\t: 0063094100";
	} else if (o == "BCA2") {
		f._bank_address.value = "BCA KCU KELAPA GADING\nA/N\t: In Ki Kim W/O Lee\nA/C\t: 0650690176";
	} else if (o == "BII1") {
		f._bank_address.value = "BII KELAPA GADING JAKARTA\nA/N\t: In Ki Kim W/O Lee\nA/C\t: 1.016.691.961";
	} else if (o == "BII2") {
		f._bank_address.value = "BII KELAPA GADING JAKARTA\nA/N\t: PT. INDOCORE PERKASA\nA/C\t: 2.016.296.083";
	} else if (o == "BII3") {
		f._bank_address.value = "BII CABANG GRAHA CEMPAKA MAS\nA/C\t: 219751283\nA/N\t: PT. Medisindo Bahana";
	} else if (o == "MANDIRI") {
		f._bank_address.value = "MANDIRI KCP JAKARTA DESIGN CENTER\nA/N\t: In Ki Kim\nA/C\t: 117-00-0219394-4";
	} else if (o == "DANAMON") {
		f._bank_address.value = "DANAMON CAB. KELAPA GADING, JAKARTA\nA/N\t: PT. Indocore Perkasa\nA/C\t: 21772660";
	} else if (o == "DANAMON2") {
		f._bank_address.value = "DANAMON CABANG KELAPA GADING II\nA/C\t: 351438364\nA/N\t: PT. Medisindo Bahana";
	} else if (o == "DANAMON3") {
		f._bank_address.value = "DANAMON CABANG KELAPA GADING II\nA/C\t: 3524032574\nA/N\t: PT. Samudia Bahtera";
	}
}