function fillCustomer(target) {
	if (target == 'customer') {
		keyword = window.document.frmInsert._cus_to.value;
	} else if (target == 'ship') {
		keyword = window.document.frmInsert._ship_to.value;
	} else if (target == 'pajak') {
		if(window.document.frmInsert._btnVat[1].checked) {
			alert("You choosed NON VAT, so you need not to fill this column");
			return;
		}
		keyword = window.document.frmInsert._pajak_to.value;
	}

	if(window.document.frmInsert._access) {
		if(window.document.frmInsert._access.value == 'ALL') {
			if(window.document.frmInsert.cboOrdBy[0].checked)	var ord_by = 1;
			if(window.document.frmInsert.cboOrdBy[1].checked)	var ord_by = 2;
		} else {
			var ord_by = window.document.frmInsert._order_by.value;
		}
	} else {
		var ord_by = window.document.frmInsert._ordered_by.value;
	}

	var x = (screen.availWidth - 400) / 2;
	var y = (screen.availHeight - 600) / 2;
	var win = window.open(
		'./p_list_cus_code.php?_check_code='+ keyword + '&_order_by=' + ord_by,
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
	} else if(target == 'pajak') {
		if(o.checked) {
			f._pajak_to.value = f._cus_to.value;
			f._pajak_name.value = f._cus_name.value;
			f._pajak_address.value = f._cus_address.value;
		} else {
			f._pajak_to.value = "";
			f._pajak_name.value = "";
			f._pajak_address.value = "";
		}
	}
}

function initBillDept(access) {
	var f = window.document.frmInsert;
	var dept = f._dept.value;
	var date = new Date();

	if(access == 'ALL' || access == 'IDC') {
		if(dept == 'A' ) {
			date.setDate(date.getDate()+1); var val_inv_date = formatDate(date, "d-NNN-yyyy");
		} else {
			date.setDate(date.getDate()+0); var val_inv_date = formatDate(date, "d-NNN-yyyy");
		}
	} else {
		date.setDate(date.getDate()+0); var val_inv_date = formatDate(date, "d-NNN-yyyy");
	}

	if(access == 'ALL' || access == 'IDC') {
		if(dept == 'A') {
			var chk_cboTypeBill = 2;
			var dsb_cboTypeBill3 = false;
			var val_do_no = '';
			var val_do_date = '';
			var dsb_do_date = true;
			var cls_do_date = 'fmtd';
		} else if(dept == 'D' || dept == 'H' || dept == 'M' || dept == 'P' || dept == 'T') {
			var chk_cboTypeBill = 0;
			var dsb_cboTypeBill3 = false;
			var val_do_no = '';
			var val_do_date = val_inv_date;
			var dsb_do_date = false;
			var cls_do_date = 'reqd';
		}
		var val_vat = '10';
		var dsb_vat = false;
		var chk_btnVat = 0;
		var dsb_btnVat = true;
		var chk_type_of_pajak0 = true;
		var chk_type_of_pajak1 = false;
		var dsb_type_of_pajak0 = false;
		var dsb_type_of_pajak1 = false;
		var dsb_checkAbove2 = false;
		var dsb_pajak_to = false;
		var dsb_pajak_name = false;
		var dsb_pajak_address = false;
		var cls_pajak_to = 'req';
		var cls_pajak_name = 'req';
		var cls_pajak_address = 'req';
	} else if(access == 'MED') {
		var chk_cboTypeBill = 0;
		var dsb_cboTypeBill3 = false;
		var val_do_no = '';
		var val_do_date = val_inv_date;
		var dsb_do_date = false;
		var cls_do_date = 'reqd';
		var val_vat = '';
		var dsb_vat = false;
		var chk_btnVat = 1;
		var dsb_btnVat = false;
		var chk_type_of_pajak0 = false;
		var chk_type_of_pajak1 = false;
		var dsb_type_of_pajak0 = true;
		var dsb_type_of_pajak1 = true;
		var dsb_checkAbove2 = true;
		var dsb_pajak_to = true;
		var dsb_pajak_name = true;
		var dsb_pajak_address = true;
		var cls_pajak_to = 'fmt';
		var cls_pajak_name = 'fmt';
		var cls_pajak_address = 'fmt';
	} else if (access == 'MEP') {
		var chk_cboTypeBill = 0;
		var val_do_no = '';
		var val_do_date = val_inv_date;
		var dsb_cboTypeBill3 = true;
		var dsb_do_date = false;
		var cls_do_date = 'reqd';
		var val_vat = '';
		var dsb_vat = false;
		var chk_btnVat = 1;
		var dsb_btnVat = false;
		var chk_type_of_pajak0 = false;
		var chk_type_of_pajak1 = false;
		var dsb_type_of_pajak0 = true;
		var dsb_type_of_pajak1 = true;
		var dsb_checkAbove2 = true;
		var dsb_pajak_to = true;
		var dsb_pajak_name = true;
		var dsb_pajak_address = true;
		var cls_pajak_to = 'fmt';
		var cls_pajak_name = 'fmt';
		var cls_pajak_address = 'fmt';
	}

	f.cboTypeBill[chk_cboTypeBill].checked = true;
	f.cboTypeBill[2].disabled = dsb_cboTypeBill3;
	f._received_by.value = f._admin.value;
	f._inv_date.value = val_inv_date;
	f._do_no.value = val_do_no;
	f._do_date.value = val_do_date;			///////////////
	f._do_no.readOnly = 'readOnly';
	f._do_date.disabled = dsb_do_date;
	f._do_date.className = cls_do_date;
	f._btnVat[chk_btnVat].checked = true;
	f._btnVat[1].disabled = dsb_btnVat;
	f._vat_val.value = val_vat;
	f._vat_val.disabled = dsb_vat;
	f._type_of_pajak[0].checked = chk_type_of_pajak0;
	f._type_of_pajak[1].checked = chk_type_of_pajak1;
	f._type_of_pajak[0].disabled = dsb_type_of_pajak0;
	f._type_of_pajak[1].disabled = dsb_type_of_pajak1;
	f.chkAbove2.disabled = dsb_checkAbove2;
	f._pajak_to.disabled = dsb_pajak_to;
	f._pajak_name.disabled = dsb_pajak_name;
	f._pajak_address.disabled = dsb_pajak_address;
	f._pajak_to.className = cls_pajak_to;
	f._pajak_name.className = cls_pajak_name;
	f._pajak_address.className = cls_pajak_address;

}

function initPageInput() {
	var f = window.document.frmInsert;
	var access = f.web_url.value;
	var dept = f._dept.value;

	if(f._type_bill.value == 2) { f._paper_format[1].checked = true; } 

	if(access == 'IDC') {
		if(f._ordered_by.value == 1) {
			for(var i=0; i<6; i++) { f._bank[i].disabled = false; }
			f._bank[5].checked = true;
			bankDesc('DANAMON');
		} else if(f._ordered_by.value == 2) {
			for(var i=0; i<6; i++) { f._bank[i].disabled = false; }
			if(f._dept.value!='D') {
				f._bank[1].checked = true;
				bankDesc('BCA2');
			}
		}
	} else if(access == 'MED') {
		for(var i=0; i<6; i++) { f._bank[i].disabled = false; }
		if(dept == 'A') 	 {f._bank[5].checked = true; bankDesc('DANAMON');}
		else if(dept == 'D') {f._bank[0].checked = true; bankDesc('BCA1');}
		else if(dept == 'H') {f._bank[1].checked = true; bankDesc('BCA2');}
		else if(dept == 'M') {f._bank[2].checked = true; bankDesc('MANDIRI');}
		else if(dept == 'P') {f._bank[1].checked = true; bankDesc('BCA2');}
		else if(dept == 'T') {f._bank[0].checked = true; bankDesc('BCA1');}
	}

	updateAmount();
}

function cod(o) {
	var f = window.document.frmInsert;

	if(o.checked == true) {
		if(f._sj_date.value.length <= 0) {
			f._sj_date.value = f._inv_date.value;
		}
		f._payment_giro_due.value = f._sj_date.value;
	} else {
		f._payment_giro_due.value = '';
	}
}

function enabledText(o, value) {
	var f = window.document.frmInsert;

	if (value == 'freight_charge') {
		if(o.checked == true) {
			f._delivery_freight_charge.disabled = false;
			f._delivery_freight_charge.className = 'reqn';
			f._delivery_freight_charge.value = '0';
		} else if(o.checked == false) {
			f._delivery_freight_charge.disabled = true;
			f._delivery_freight_charge.className = 'fmt';
			f._delivery_freight_charge.value = '';
		}
		updateAmount();
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
		if(f._is_vat.value == 'y' && o.checked == true) {
			f._bank[5].checked	= true;
			bankDesc("DANAMON");
		} else if(f._ordered_by.value == '2' && o.checked == true) {
			f._bank[0].checked	= true;
			bankDesc("BCA1");
		}
	}

}

function enabledBankOption(o) {
	var f = window.document.frmInsert;

	if (o.checked == true) {
		for(var i=1; i<6; i++) {
			f._bank[i].disabled = false;
		}
	} else if (o.checked == false) {
		for(var i=1; i<6; i++) {
			f._bank[i].disabled = true;
			f._bank[i].checked = false;
		}
		f._bank_address.value = '';
	}
	f._bank[0].disabled = true;
}

function bankDesc(o) {
	var f = window.document.frmInsert;

	if (o.value == "BCA1") {
		f._bank_address.value = "BCA KCU WISMA GKBI\nA/N\t: PT. INDOCORE PERKASA\nA/C\t: 0063094100";
	} else if (o.value == "BCA2") {
		f._bank_address.value = "BCA KCU KELAPA GADING\nA/N\t: In Ki Kim W/O Lee\nA/C\t: 0650690176";
	} else if (o.value == "BII1") {
		f._bank_address.value = "BII KELAPA GADING JAKARTA\nA/N\t: In Ki Kim W/O Lee\nA/C\t: 1.016.691.961";
	} else if (o.value == "BII2") {
		f._bank_address.value = "BII KELAPA GADING JAKARTA\nA/N\t: PT. INDOCORE PERKASA\nA/C\t: 2.016.296.083";
	} else if (o.value == "MANDIRI") {
		f._bank_address.value = "MANDIRI KCP JAKARTA DESIGN CENTER\nA/N\t: In Ki Kim\nA/C\t: 117-00-0219394-4";
	} else if (o.value == "DANAMON") {
		f._bank_address.value = "DANAMON CAB. KELAPA GADING, JAKARTA\nA/N\t: PT. Indocore Perkasa\nA/C\t: 21772660";
	} else if (o == "DANAMON") {
		f._bank_address.value = "DANAMON CAB. KELAPA GADING, JAKARTA\nA/N\t: PT. Indocore Perkasa\nA/C\t: 21772660";
	} else if (o == "BCA1") {
		f._bank_address.value = "BCA KCU WISMA GKBI\nA/N\t: PT. INDOCORE PERKASA\nA/C\t: 0063094100";
	} else if (o == "BCA2") {
		f._bank_address.value = "BCA KCU KELAPA GADING\nA/N\t: In Ki Kim W/O Lee\nA/C\t: 0650690176";
	} else if (o == "MANDIRI") {
		f._bank_address.value = "MANDIRI KCP JAKARTA DESIGN CENTER\nA/N\t: In Ki Kim\nA/C\t: 117-00-0219394-4";
	}
}

function enabledSalesPeriod(value) {
	var f = window.document.frmInsert;

	if(value) {
		f._sales_from.disabled	= false;
		f._sales_to.disabled	= false;
	} else {
		f._sales_from.disabled	= true;
		f._sales_to.disabled	= true;
		f._sales_from.value		= '';
		f._sales_to.value		= '';
	}
}

function dueDateValue() {
	var f = window.document.frmInsert;

	if(f._payment_closing_on.value.length > 0) {
		var d = parseDate(f._payment_closing_on.value, 'prefer_euro_format');

		if (d == null) {
			alert("You must be input date with proper format")
			f._payment_closing_on.value = "";
			f._payment_closing_on.focus();
			return false;
		}
	}

	if(isNaN(removecomma(f._payment_widthin_days.value))) {
		alert("You can enter only number");
		f._payment_widthin_days.value = '';
		f._payment_widthin_days.focus();
		return false;
	}

	if(f._payment_sj_inv_fp_tender.value == 'Tukar Faktur') {
		f._tukar_faktur_date.disabled = false;
		f._tukar_faktur_date.className = 'reqd';
	} else {
		f._tukar_faktur_date.disabled = true;
		f._tukar_faktur_date.className = 'fmt';
		f._tukar_faktur_date.value = '';
	}

	if(f._tukar_faktur_date.value.length > 0) {
		var d = parseDate(f._tukar_faktur_date.value, 'prefer_euro_format');
		if (d == null) {
			alert("You must be input date with proper format")
			f._tukar_faktur_date.value = "";
			f._tukar_faktur_date.focus();
			return false;
		}
	}

	var date_from 	= f._inv_date.value;

	if(f._payment_widthin_days.value.length == 0) {
		var add_days = 0;
	} else {
		var add_days	= parseInt(f._payment_widthin_days.value);
	}

	if(f._payment_closing_on.value.length > 0) {
		date_from = f._payment_closing_on.value;
	} else if(f._payment_sj_inv_fp_tender.value == 'Invoice') {
		date_from = f._inv_date.value;
	} else if(f._payment_sj_inv_fp_tender.value == 'Surat Jalan') {
		date_from = f._sj_date.value;
	} else if(f._payment_sj_inv_fp_tender.value == 'Tukar Faktur') {
		if (f._tukar_faktur_date.value.length > 0) {
			date_from = f._tukar_faktur_date.value;
		}
	} 

	var due_date = parseDate(date_from, 'prefer_euro_format');
	if (date_from != f._payment_closing_on.value){
		due_date.setDate(due_date.getDate()+add_days);
	}
	f._payment_giro_due.value = formatDate(due_date, 'd-NNN-yyyy');

}