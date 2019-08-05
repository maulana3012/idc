<?php
//VARIABLE
$tmp_bill	= array();
$tmp_turn	= array();
$tmp_sv		= array();
$tmp_paid	= array();
$tmp_svpay	= array();
$strWhere	= array();
$strWherePay= array();

switch (ZKP_URL) {
	// ALL ==============================================================================================================
	case "ALL":

		if($_order_by == "1") {				// INDOCORE
			$tmp_bill[0][]	= "b.bill_ordered_by = 1";
			$tmp_turn[0][]	= "t.turn_ordered_by = 1";
			$tmp_paid[0][]	= "substr(bill_code,1,1) = 'I'";
			$tmp_bill[1][]	= "b.bill_code is null";
			$tmp_turn[1][]	= "t.turn_code is null";
			$tmp_sv[1][]	= "sv_code is null";
			$tmp_paid[1][]	= "bill_code is null";
			$tmp_svpay[1][]	= "sv_code is null";
		} else if($_order_by == "2") {		// MEDIKUS
			$tmp_bill[0][]	= "b.bill_ordered_by = 2";
			$tmp_turn[0][]	= "t.turn_ordered_by = 2";
			$tmp_sv[0][]	= "sv_code is null";
			$tmp_paid[0][]	= "substr(bill_code,1,1) = 'M'";
			$tmp_svpay[0][]	= "sv_code is null";
			$tmp_bill[1][]	= "b.bill_code is null";
			$tmp_turn[1][]	= "t.turn_code is null";
			$tmp_sv[1][]	= "sv_code is null";
			$tmp_paid[1][]	= "bill_code is null";
			$tmp_svpay[1][]	= "sv_code is null";
		} else if($_order_by == "3") {		// MEDISINDO
			$tmp_bill[0][]	= "b.bill_code is null";
			$tmp_turn[0][]	= "t.turn_code is null";
			$tmp_sv[0][]	= "sv_code is null";
			$tmp_paid[0][]	= "b.bill_code is null";
			$tmp_svpay[0][]	= "sv_code is null";
			$tmp_bill[1][]	= "b.bill_ordered_by = 1";
			$tmp_turn[1][]	= "t.turn_ordered_by = 1";
			$tmp_paid[1][]	= "substr(bill_code,1,1) = 'B'";
		} else if($_order_by == "4") {		// SAMUDIA
			$tmp_bill[0][]	= "b.bill_code is null";
			$tmp_turn[0][]	= "t.turn_code is null";
			$tmp_sv[0][]	= "sv_code is null";
			$tmp_paid[0][]	= "b.bill_code is null";
			$tmp_svpay[0][]	= "sv_code is null";
			$tmp_bill[1][]	= "b.bill_ordered_by = 2";
			$tmp_turn[1][]	= "t.turn_ordered_by = 2";
			$tmp_paid[1][]	= "substr(bill_code,1,1) = 'S'";
		} else if($_order_by == "5") {		// INDOCORE & MEDIKUS
			$tmp_bill[1][]	= "b.bill_code is null";
			$tmp_turn[1][]	= "t.turn_code is null";
			$tmp_sv[1][]	= "sv_code is null";
			$tmp_paid[1][]	= "bill_code is null";
			$tmp_svpay[1][]	= "sv_code is null";
		} else if($_order_by == "6") {		// MEDISINDO & SAMUDIA
			$tmp_bill[0][]	= "b.bill_code is null";
			$tmp_turn[0][]	= "t.turn_code is null";
			$tmp_sv[0][]	= "sv_code is null";
			$tmp_paid[0][]	= "bill_code is null";
			$tmp_svpay[0][]	= "sv_code is null";
		}


		if($_chk_company == 'off') {
			$tmp_bill[0][]	= "b.bill_ship_to NOT IN ('0MDS', '0SMD')";
			$tmp_turn[0][]	= "t.turn_ship_to NOT IN ('0MDS', '0SMD')";
			$tmp_sv[0][]	= "sv_code NOT IN ('0MDS', '0SMD')";
			$tmp_paid[0][]	= "bill_ship_to NOT IN ('0MDS', '0SMD')";
			$tmp_svpay[0][]	= "sv_code NOT IN ('0MDS', '0SMD')";
			$tmp_bill[1][]	= "b.bill_ship_to NOT IN ('6IDC', '0MDS', '0SMD')";
			$tmp_turn[1][]	= "t.turn_ship_to NOT IN ('6IDC', '0MDS', '0SMD')";
			$tmp_sv[1][]	= "sv_code NOT IN ('6IDC', '0MDS', '0SMD')";
			$tmp_paid[1][]	= "bill_ship_to NOT IN ('6IDC', '0MDS', '0SMD')";
			$tmp_svpay[1][]	= "sv_code NOT IN ('6IDC', '0MDS', '0SMD')";
		}

		if ($_filter_doc == "I") {
			$tmp_turn[0][]	= "t.turn_code = NULL";
			$tmp_paid[0][]	= "pay_note IN ('USUAL','DEPOSIT-A', 'RETURN','CROSS_TRANSFER+','CROSS_TRANSFER-')";
			$tmp_turn[1][]	= "t.turn_code = NULL";
			$tmp_paid[1][]	= "pay_note IN ('USUAL','DEPOSIT-A', 'RETURN','CROSS_TRANSFER+','CROSS_TRANSFER-')";
		} else if ($_filter_doc == "R") {
			$tmp_bill[0][]	= "b.bill_code = NULL";
			$tmp_paid[0][]	= "pay_paid <= 0 AND pay_note IN ('USUAL','DEPOSIT-A', 'RETURN')";
			$tmp_sv[0][]	= "sv_code is null";
			$tmp_svpay[0][]= "sv_code is null";
			$tmp_bill[1][]	= "b.bill_code = NULL";
			$tmp_paid[1][]	= "pay_paid <= 0 AND pay_note IN ('USUAL','DEPOSIT-A', 'RETURN')";
			$tmp_sv[1][]	= "sv_code is null";
			$tmp_svpay[1][]= "sv_code is null";
		} else if ($_filter_doc == "CT") {
			$tmp_bill[0][]	= "b.bill_code = NULL";
			$tmp_turn[0][]	= "t.turn_code = NULL";
			$tmp_sv[0][]	= "sv_code is null";
			$tmp_paid[0][]	= "pay_note IN ('CROSS_TRANSFER+')";
			$tmp_svpay[0][]= "sv_code is null";
			$tmp_bill[1][]	= "b.bill_code = NULL";
			$tmp_turn[1][]	= "t.turn_code = NULL";
			$tmp_sv[1][]	= "sv_code is null";
			$tmp_paid[1][]	= "pay_note IN ('CROSS_TRANSFER+')";
			$tmp_svpay[1][]= "sv_code is null";
		} else {
			$tmp_paid[0][]	  = "pay_note IN ('USUAL','DEPOSIT-A', 'RETURN','CROSS_TRANSFER+','CROSS_TRANSFER-')";
			$tmp_paid[1][]	  = "pay_note IN ('USUAL','DEPOSIT-A', 'RETURN','CROSS_TRANSFER+','CROSS_TRANSFER-')";
		}
		
		if($_marketing != "all") {
			$tmp_bill[0][]	= "cus_responsibility_to = $_marketing";
			$tmp_turn[0][] = "cus_responsibility_to = $_marketing";
			$tmp_paid[0][] = "cus_responsibility_to = $_marketing";
			$tmp_sv[0][]	= "sv_code is null";
			$tmp_svpay[0][]= "sv_code is null";
		}
		
		if($_dept != 'all' && $_dept != 'S') {
			$tmp_bill[0][] = "b.bill_dept = '$_dept'";
			$tmp_turn[0][] = "t.turn_dept = '$_dept'";
			$tmp_paid[0][] = "b.bill_dept = '$_dept'";
			$tmp_sv[0][]	= "sv_code is null";
			$tmp_svpay[0][]= "sv_code is null";
			$tmp_bill[1][] = "b.bill_dept = '$_dept'";
			$tmp_turn[1][] = "t.turn_dept = '$_dept'";
			$tmp_paid[1][] = "b.bill_dept = '$_dept'";
			$tmp_sv[1][]	= "sv_code is null";
			$tmp_svpay[1][]= "sv_code is null";
		} else if($_dept == 'S') {
			$tmp_bill[0][] = "b.bill_code is null";
			$tmp_turn[0][] = "t.turn_code is null";
			$tmp_paid[0][] = "b.bill_code is null";
			$tmp_bill[1][] = "b.bill_code is null";
			$tmp_turn[1][] = "t.turn_code is null";
			$tmp_paid[1][] = "b.bill_code is null";
		}
		
		if ($some_date != "") {
			$tmp_bill[0][] = "b.bill_inv_date = DATE '$some_date'";
			$tmp_turn[0][] = "t.turn_return_date = DATE '$some_date'";
			$tmp_paid[0][] = "p.pay_date = DATE '$some_date'";
			$tmp_sv[0][]	= "sv_date = DATE '$some_date'";
			$tmp_svpay[0][]= "svpay_date = DATE '$some_date'";
			$tmp_bill[1][] = "b.bill_inv_date = DATE '$some_date'";
			$tmp_turn[1][] = "t.turn_return_date = DATE '$some_date'";
			$tmp_paid[1][] = "p.pay_date = DATE '$some_date'";
			$tmp_sv[1][]	= "sv_date = DATE '$some_date'";
			$tmp_svpay[1][]= "svpay_date = DATE '$some_date'";
		} else {
			$tmp_bill[0][]	= "b.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_turn[0][]	= "t.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_paid[0][]	= "p.pay_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_sv[0][]	= "sv_date BETWEEN DATE '$period_from' AND '$period_to'";	
			$tmp_svpay[0][]= "svpay_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_bill[1][]	= "b.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_turn[1][]	= "t.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_paid[1][]	= "p.pay_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_sv[1][]	= "sv_date BETWEEN DATE '$period_from' AND '$period_to'";	
			$tmp_svpay[1][]= "svpay_date BETWEEN DATE '$period_from' AND '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_bill[0][]	= "b.bill_vat > 0";
			$tmp_turn[0][]	= "t.turn_vat > 0";
			$tmp_paid[0][]	= "b.bill_vat > 0";
			$tmp_sv[0][]	= "sv_code is null";
			$tmp_svpay[0][]= "sv_code is null";
			$tmp_bill[1][]	= "b.bill_vat > 0";
			$tmp_turn[1][]	= "t.turn_vat > 0";
			$tmp_paid[1][]	= "b.bill_vat > 0";
			$tmp_sv[1][]	= "sv_code is null";
			$tmp_svpay[1][]= "sv_code is null";
		} else if($_vat == 'vat-IO') {
			$tmp_bill[0][]	= "b.bill_vat > 0 AND substr(b.bill_code,2,1) = 'O'";
			$tmp_turn[0][]	= "t.turn_vat > 0";
			$tmp_paid[0][]	= "b.bill_vat > 0 AND substr(b.bill_code,2,1) = 'O'";
			$tmp_sv[0][]	= "sv_code is null";
			$tmp_svpay[0][]= "sv_code is null";
			$tmp_bill[1][]	= "b.bill_vat > 0 AND substr(b.bill_code,2,1) = 'O'";
			$tmp_turn[1][]	= "t.turn_vat > 0";
			$tmp_paid[1][]	= "b.bill_vat > 0 AND substr(b.bill_code,2,1) = 'O'";
			$tmp_sv[1][]	= "sv_code is null";
			$tmp_svpay[1][]= "sv_code is null";
		}else if($_vat == 'vat-IP') {
			$tmp_bill[0][]	= "b.bill_vat > 0 AND substr(b.bill_code,2,1) = 'P'";
			$tmp_turn[0][]	= "t.turn_code = NULL";
			$tmp_paid[0][]	= "b.bill_vat > 0 AND substr(b.bill_code,2,1) = 'P'";
			$tmp_sv[0][]	= "sv_code is null";
			$tmp_svpay[0][]= "sv_code is null";
			$tmp_bill[1][]	= "b.bill_vat > 0 AND substr(b.bill_code,2,1) = 'P'";
			$tmp_turn[1][]	= "t.turn_code = NULL";
			$tmp_paid[1][]	= "b.bill_vat > 0 AND substr(b.bill_code,2,1) = 'P'";
			$tmp_sv[1][]	= "sv_code is null";
			$tmp_svpay[1][]= "sv_code is null";
		} else if ($_vat == 'non') {
			$tmp_bill[0][]	= "b.bill_vat = 0";
			$tmp_turn[0][]	= "t.turn_vat = 0";
			$tmp_paid[0][]	= "b.bill_vat = 0";
			$tmp_bill[1][]	= "b.bill_vat = 0";
			$tmp_turn[1][]	= "t.turn_vat = 0";
			$tmp_paid[1][]	= "b.bill_vat = 0";
		}

	break;

	// IDC ==============================================================================================================
	case "IDC":

		if ($_filter_doc == "I") {
			$tmp_turn[0][]	= "t.turn_code = NULL";
			$tmp_paid[0][]	= "pay_note IN ('USUAL','DEPOSIT-A', 'RETURN','CROSS_TRANSFER+','CROSS_TRANSFER-')";
		} else if ($_filter_doc == "R") {
			$tmp_bill[0][]	= "b.bill_code = NULL";
			$tmp_paid[0][]	= "pay_paid <= 0 AND pay_note IN ('USUAL','DEPOSIT-A', 'RETURN')";
			$tmp_sv[0][]	= "sv_code is null";
			$tmp_svpay[0][]= "sv_code is null";
		} else if ($_filter_doc == "CT") {
			$tmp_bill[0][]	= "b.bill_code = NULL";
			$tmp_turn[0][]	= "t.turn_code = NULL";
			$tmp_sv[0][]	= "sv_code is null";
			$tmp_paid[0][]	= "pay_note IN ('CROSS_TRANSFER+')";
			$tmp_svpay[0][]= "sv_code is null";
		} else {
			$tmp_paid[0][]	  = "pay_note IN ('USUAL','DEPOSIT-A', 'RETURN','CROSS_TRANSFER+','CROSS_TRANSFER-')";
		}
		
		if($_marketing != "all") {
			$tmp_bill[0][]	= "cus_responsibility_to = $_marketing";
			$tmp_turn[0][] = "cus_responsibility_to = $_marketing";
			$tmp_paid[0][] = "cus_responsibility_to = $_marketing";
			$tmp_sv[0][]	= "sv_code is null";
			$tmp_svpay[0][]= "sv_code is null";
		}
		
		if($_dept != 'all' && $_dept != 'S') {
			$tmp_bill[0][] = "b.bill_dept = '$_dept'";
			$tmp_turn[0][] = "t.turn_dept = '$_dept'";
			$tmp_paid[0][] = "b.bill_dept = '$_dept'";
			$tmp_sv[0][]	= "sv_code is null";
			$tmp_svpay[0][]= "sv_code is null";
		} else if($_dept == 'S') {
			$tmp_bill[0][] = "b.bill_code is null";
			$tmp_turn[0][] = "t.turn_code is null";
			$tmp_paid[0][] = "b.bill_code is null";
		}
		
		if ($some_date != "") {
			$tmp_bill[0][] = "b.bill_inv_date = DATE '$some_date'";
			$tmp_turn[0][] = "t.turn_return_date = DATE '$some_date'";
			$tmp_paid[0][] = "p.pay_date = DATE '$some_date'";
			$tmp_sv[0][]	= "sv_date = DATE '$some_date'";
			$tmp_svpay[0][]= "svpay_date = DATE '$some_date'";
		} else {
			$tmp_bill[0][]	= "b.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_turn[0][]	= "t.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_paid[0][]	= "p.pay_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_sv[0][]	= "sv_date BETWEEN DATE '$period_from' AND '$period_to'";	
			$tmp_svpay[0][]= "svpay_date BETWEEN DATE '$period_from' AND '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_bill[0][]	= "b.bill_vat > 0";
			$tmp_turn[0][]	= "t.turn_vat > 0";
			$tmp_paid[0][]	= "b.bill_vat > 0";
			$tmp_sv[0][]	= "sv_code is null";
			$tmp_svpay[0][]= "sv_code is null";
		} else if($_vat == 'vat-IO') {
			$tmp_bill[0][]	= "b.bill_vat > 0 AND substr(b.bill_code,2,1) = 'O'";
			$tmp_turn[0][]	= "t.turn_vat > 0";
			$tmp_paid[0][]	= "b.bill_vat > 0 AND substr(b.bill_code,2,1) = 'O'";
			$tmp_sv[0][]	= "sv_code is null";
			$tmp_svpay[0][]= "sv_code is null";
		}else if($_vat == 'vat-IP') {
			$tmp_bill[0][]	= "b.bill_vat > 0 AND substr(b.bill_code,2,1) = 'P'";
			$tmp_turn[0][]	= "t.turn_code = NULL";
			$tmp_paid[0][]	= "b.bill_vat > 0 AND substr(b.bill_code,2,1) = 'P'";
			$tmp_sv[0][]	= "sv_code is null";
			$tmp_svpay[0][]= "sv_code is null";
		} else if ($_vat == 'non') {
			$tmp_bill[0][]	= "b.bill_vat = 0";
			$tmp_turn[0][]	= "t.turn_vat = 0";
			$tmp_paid[0][]	= "b.bill_vat = 0";
		}

	break;

	// MED ==============================================================================================================
	case "MED":

		if ($_filter_doc == "I") {
			$tmp_turn[1][]	= "t.turn_code = NULL";
			$tmp_paid[1][]	= "pay_note IN ('USUAL','DEPOSIT-A', 'RETURN','CROSS_TRANSFER+','CROSS_TRANSFER-')";
		} else if ($_filter_doc == "R") {
			$tmp_bill[1][]	= "b.bill_code = NULL";
			$tmp_paid[1][]	= "pay_paid <= 0 AND pay_note IN ('USUAL','DEPOSIT-A', 'RETURN')";
			$tmp_sv[1][]	= "sv_code is null";
			$tmp_svpay[1][]= "sv_code is null";
		} else if ($_filter_doc == "CT") {
			$tmp_bill[1][]	= "b.bill_code = NULL";
			$tmp_turn[1][]	= "t.turn_code = NULL";
			$tmp_sv[1][]	= "sv_code is null";
			$tmp_paid[1][]	= "pay_note IN ('CROSS_TRANSFER+')";
			$tmp_svpay[1][]= "sv_code is null";
		} else {
			$tmp_paid[1][]	  = "pay_note IN ('USUAL','DEPOSIT-A', 'RETURN','CROSS_TRANSFER+','CROSS_TRANSFER-')";
		}
		
		if($_marketing != "all") {
			$tmp_bill[1][]	= "cus_responsibility_to = $_marketing";
			$tmp_turn[1][] = "cus_responsibility_to = $_marketing";
			$tmp_paid[1][] = "cus_responsibility_to = $_marketing";
			$tmp_sv[1][]	= "sv_code is null";
			$tmp_svpay[1][]= "sv_code is null";
		}
		
		if($_dept != 'all' && $_dept != 'S') {
			$tmp_bill[1][] = "b.bill_dept = '$_dept'";
			$tmp_turn[1][] = "t.turn_dept = '$_dept'";
			$tmp_paid[1][] = "b.bill_dept = '$_dept'";
			$tmp_sv[1][]	= "sv_code is null";
			$tmp_svpay[1][]= "sv_code is null";
		} else if($_dept == 'S') {
			$tmp_bill[1][] = "b.bill_code is null";
			$tmp_turn[1][] = "t.turn_code is null";
			$tmp_paid[1][] = "b.bill_code is null";
		}
		
		if ($some_date != "") {
			$tmp_bill[1][] = "b.bill_inv_date = DATE '$some_date'";
			$tmp_turn[1][] = "t.turn_return_date = DATE '$some_date'";
			$tmp_paid[1][] = "p.pay_date = DATE '$some_date'";
			$tmp_sv[1][]	= "sv_date = DATE '$some_date'";
			$tmp_svpay[1][]= "svpay_date = DATE '$some_date'";
		} else {
			$tmp_bill[1][]	= "b.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_turn[1][]	= "t.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_paid[1][]	= "p.pay_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_sv[1][]	= "sv_date BETWEEN DATE '$period_from' AND '$period_to'";	
			$tmp_svpay[1][]= "svpay_date BETWEEN DATE '$period_from' AND '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_bill[1][]	= "b.bill_vat > 0";
			$tmp_turn[1][]	= "t.turn_vat > 0";
			$tmp_paid[1][]	= "b.bill_vat > 0";
			$tmp_sv[1][]	= "sv_code is null";
			$tmp_svpay[1][]= "sv_code is null";
		} else if($_vat == 'vat-IO') {
			$tmp_bill[1][]	= "b.bill_vat > 0 AND substr(b.bill_code,2,1) = 'O'";
			$tmp_turn[1][]	= "t.turn_vat > 0";
			$tmp_paid[1][]	= "b.bill_vat > 0 AND substr(b.bill_code,2,1) = 'O'";
			$tmp_sv[1][]	= "sv_code is null";
			$tmp_svpay[1][]= "sv_code is null";
		}else if($_vat == 'vat-IP') {
			$tmp_bill[1][]	= "b.bill_vat > 0 AND substr(b.bill_code,2,1) = 'P'";
			$tmp_turn[1][]	= "t.turn_code = NULL";
			$tmp_paid[1][]	= "b.bill_vat > 0 AND substr(b.bill_code,2,1) = 'P'";
			$tmp_sv[1][]	= "sv_code is null";
			$tmp_svpay[1][]= "sv_code is null";
		} else if ($_vat == 'non') {
			$tmp_bill[1][]	= "b.bill_vat = 0";
			$tmp_turn[1][]	= "t.turn_vat = 0";
			$tmp_paid[1][]	= "b.bill_vat = 0";
		}

	break;

	// MEP ==============================================================================================================
	case "MEP":

		if ($_filter_doc == "I") {
			$tmp_turn[0][]	= "t.turn_code = NULL";
			$tmp_paid[0][]	= "pay_note IN ('USUAL','DEPOSIT-A', 'RETURN','CROSS_TRANSFER+','CROSS_TRANSFER-')";
		} else if ($_filter_doc == "R") {
			$tmp_bill[0][]	= "b.bill_code = NULL";
			$tmp_paid[0][]	= "pay_paid <= 0 AND pay_note IN ('USUAL','DEPOSIT-A', 'RETURN')";
			$tmp_sv[0][]	= "sv_code is null";
			$tmp_svpay[0][]= "sv_code is null";
		} else if ($_filter_doc == "CT") {
			$tmp_bill[0][]	= "b.bill_code = NULL";
			$tmp_turn[0][]	= "t.turn_code = NULL";
			$tmp_sv[0][]	= "sv_code is null";
			$tmp_paid[0][]	= "pay_note IN ('CROSS_TRANSFER+')";
			$tmp_svpay[0][]= "sv_code is null";
		} else {
			$tmp_paid[0][]	  = "pay_note IN ('USUAL','DEPOSIT-A', 'RETURN','CROSS_TRANSFER+','CROSS_TRANSFER-')";
		}
		
		if($_marketing != "all") {
			$tmp_bill[0][]	= "cus_responsibility_to = $_marketing";
			$tmp_turn[0][] = "cus_responsibility_to = $_marketing";
			$tmp_paid[0][] = "cus_responsibility_to = $_marketing";
			$tmp_sv[0][]	= "sv_code is null";
			$tmp_svpay[0][]= "sv_code is null";
		}
		
		if($_dept != 'all' && $_dept != 'S') {
			$tmp_bill[0][] = "b.bill_dept = '$_dept'";
			$tmp_turn[0][] = "t.turn_dept = '$_dept'";
			$tmp_paid[0][] = "b.bill_dept = '$_dept'";
			$tmp_sv[0][]	= "sv_code is null";
			$tmp_svpay[0][]= "sv_code is null";
		} else if($_dept == 'S') {
			$tmp_bill[0][] = "b.bill_code is null";
			$tmp_turn[0][] = "t.turn_code is null";
			$tmp_paid[0][] = "b.bill_code is null";
		}
		
		if ($some_date != "") {
			$tmp_bill[0][] = "b.bill_inv_date = DATE '$some_date'";
			$tmp_turn[0][] = "t.turn_return_date = DATE '$some_date'";
			$tmp_paid[0][] = "p.pay_date = DATE '$some_date'";
			$tmp_sv[0][]	= "sv_date = DATE '$some_date'";
			$tmp_svpay[0][]= "svpay_date = DATE '$some_date'";
		} else {
			$tmp_bill[0][]	= "b.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_turn[0][]	= "t.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_paid[0][]	= "p.pay_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_sv[0][]	= "sv_date BETWEEN DATE '$period_from' AND '$period_to'";	
			$tmp_svpay[0][]= "svpay_date BETWEEN DATE '$period_from' AND '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_bill[0][]	= "b.bill_vat > 0";
			$tmp_turn[0][]	= "t.turn_vat > 0";
			$tmp_paid[0][]	= "b.bill_vat > 0";
			$tmp_sv[0][]	= "sv_code is null";
			$tmp_svpay[0][]= "sv_code is null";
		} else if($_vat == 'vat-IO') {
			$tmp_bill[0][]	= "b.bill_vat > 0 AND substr(b.bill_code,2,1) = 'O'";
			$tmp_turn[0][]	= "t.turn_vat > 0";
			$tmp_paid[0][]	= "b.bill_vat > 0 AND substr(b.bill_code,2,1) = 'O'";
			$tmp_sv[0][]	= "sv_code is null";
			$tmp_svpay[0][]= "sv_code is null";
		}else if($_vat == 'vat-IP') {
			$tmp_bill[0][]	= "b.bill_vat > 0 AND substr(b.bill_code,2,1) = 'P'";
			$tmp_turn[0][]	= "t.turn_code = NULL";
			$tmp_paid[0][]	= "b.bill_vat > 0 AND substr(b.bill_code,2,1) = 'P'";
			$tmp_sv[0][]	= "sv_code is null";
			$tmp_svpay[0][]= "sv_code is null";
		} else if ($_vat == 'non') {
			$tmp_bill[0][]	= "b.bill_vat = 0";
			$tmp_turn[0][]	= "t.turn_vat = 0";
			$tmp_paid[0][]	= "b.bill_vat = 0";
		}

	break;

}

$strWhere[0]	= implode(" AND ", $tmp_bill[0]);
$strWhere[1]	= implode(" AND ", $tmp_turn[0]);
$strWhere[2]	= implode(" AND ", $tmp_sv[0]);
$strWhere[3]	= implode(" AND ", $tmp_bill[1]);
$strWhere[4]	= implode(" AND ", $tmp_turn[1]);
$strWhere[5]	= implode(" AND ", $tmp_sv[1]);

$strWherePay[0]	= implode(" AND ", $tmp_paid[0]);
$strWherePay[1]	= implode(" AND ", $tmp_svpay[0]);
$strWherePay[2]	= implode(" AND ", $tmp_paid[1]);
$strWherePay[3]	= implode(" AND ", $tmp_svpay[1]);
?>