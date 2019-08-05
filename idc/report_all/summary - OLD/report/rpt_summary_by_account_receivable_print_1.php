<?php
//SET WHERE PARAMETER
$channel["000"] = "Medical Dealer";
$channel["001"] = "Medicine Dist";
$channel["002"] = "Pharmacy Chain";
$channel["003"] = "Gen/ Specialty";
$channel["004"] = "Pharmaceutical";
$channel["005"] = "Hospital";
$channel["6.1"] = "M/L Marketing";
$channel["6.2"] = "Mail Order";
$channel["6.3"] = "Internet Business";
$channel["007"] = "Promotion & Other";
$channel["008"] = "Individual";
$channel["009"] = "Private use";
$channel["00S"] = "Service";

$tmp_billing1 = array();
$tmp_billing2 = array();
$tmp_service = array();
$strWhere	= array();

switch (ZKP_URL) {
	// ALL ==============================================================================================================
	case "ALL":

		if($_order_by == "1") {				// INDOCORE
			$tmp_billing1[0][]	= "bill_ordered_by = 1";
			$tmp_billing2[0][]	= "bill_ordered_by = 1";
			$tmp_billing1[1][]	= "bill_code is null";
			$tmp_billing2[1][]	= "bill_code is null";
			$tmp_service[1][]	= "sv_code is null";
		} else if($_order_by == "2") {		// MEDISINDO
			$tmp_billing1[0][]	= "bill_code is null";
			$tmp_billing2[0][]	= "bill_code is null";
			$tmp_service[0][]	= "sv_code is null";
		} else if($_order_by == "3") {		// MEDIKUS
			$tmp_billing1[0][]	= "bill_ordered_by = 2";
			$tmp_billing2[0][]	= "bill_ordered_by = 2";
			$tmp_service[0][]	= "sv_code is null";
			$tmp_billing1[1][]	= "bill_code is null";
			$tmp_billing2[1][]	= "bill_code is null";
			$tmp_service[1][]	= "sv_code is null";
		} else if($_order_by == "4") {		// INDOCORE & MEDIKUS
			$tmp_billing1[1][]	= "bill_code is null";
			$tmp_billing2[1][]	= "bill_code is null";
			$tmp_service[1][]	= "sv_code is null";
		}

		if ($_filter_doc == "I") {
			$tmp_billing1[0][] = "p.pay_paid > 0 AND pay_note IN ('USUAL', 'DEPOSIT-A', 'RETURN')";
			$tmp_billing2[0][] = "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
			$tmp_billing1[1][] = "p.pay_paid > 0 AND pay_note IN ('USUAL', 'DEPOSIT-A', 'RETURN')";
			$tmp_billing2[1][] = "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
		} else if ($_filter_doc == "R") {
			$tmp_billing1[0][] = "p.pay_paid <= 0 AND pay_note IN ('USUAL', 'DEPOSIT-A', 'RETURN')";
			$tmp_billing2[0][] = "p.pay_idx IS NULL";
			$tmp_service[0][] = "svpay_idx is null";
			$tmp_billing1[1][] = "p.pay_paid <= 0 AND pay_note IN ('USUAL', 'DEPOSIT-A', 'RETURN')";
			$tmp_billing2[1][] = "p.pay_idx IS NULL";
			$tmp_service[0][] = "svpay_idx is null";
		} else if ($_filter_doc == "CT") {
			$tmp_billing1[0][] = "p.pay_idx IS NULL";
			$tmp_billing2[0][] = "pay_note IN ('CROSS_TRANSFER+')";
			$tmp_service[0][] = "svpay_idx is null";
			$tmp_billing1[1][] = "p.pay_idx IS NULL";
			$tmp_billing2[1][] = "pay_note IN ('CROSS_TRANSFER+')";
			$tmp_service[1][] = "svpay_idx is null";
		} else {
			$tmp_billing1[0][]	= "pay_note IN ('USUAL', 'DEPOSIT-A', 'RETURN')";
			$tmp_billing2[0][]	= "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
			$tmp_billing1[1][]	= "pay_note IN ('USUAL', 'DEPOSIT-A', 'RETURN')";
			$tmp_billing2[1][]	= "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
		}
		
		if($_marketing != "all") {
			$tmp_billing1[0][] = "cus_responsibility_to = $_marketing";
			$tmp_billing2[0][] = "cus_responsibility_to = $_marketing";
			$tmp_service[0][] = "sv_code is null";
			$tmp_billing1[1][] = "cus_responsibility_to = $_marketing";
			$tmp_billing2[1][] = "cus_responsibility_to = $_marketing";
			$tmp_service[1][] = "sv_code is null";
		}
		
		if ($some_date != "") {
			$tmp_billing1[0][] = "p.pay_date = DATE '$some_date'";
			$tmp_billing2[0][] = "p.pay_date = DATE '$some_date'";
			$tmp_service[0][] = "svpay_date = DATE '$some_date'";
			$tmp_billing1[1][] = "p.pay_date = DATE '$some_date'";
			$tmp_billing2[1][] = "p.pay_date = DATE '$some_date'";
			$tmp_service[1][] = "svpay_date = DATE '$some_date'";
		} else {
			$tmp_billing1[0][] = "p.pay_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_billing2[0][] = "p.pay_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_service[0][] = "svpay_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_billing1[1][] = "p.pay_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_billing2[1][] = "p.pay_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_service[1][] = "svpay_date BETWEEN DATE '$period_from' AND '$period_to'";
		}
		
		if($_dept != 'all' && $_dept != 'S') {
			$tmp_billing1[0][] = "p.pay_dept = '$_dept'";
			$tmp_billing2[0][] = "p.pay_dept = '$_dept'";
			$tmp_service[0][] = "sv_code is null";
			$tmp_billing1[1][] = "p.pay_dept = '$_dept'";
			$tmp_billing2[1][] = "p.pay_dept = '$_dept'";
			$tmp_service[1][] = "sv_code is null";
		} else if($_dept == 'S') {
			$tmp_billing1[0][] = "bill_code is null";
			$tmp_billing2[0][] = "bill_code is null";
			$tmp_billing1[1][] = "bill_code is null";
			$tmp_billing2[1][] = "bill_code is null";
		}
		
		if($_vat == 'vat') { 
			$tmp_billing1[0][] = "SUBSTR(p.bill_code,2,1) IN ('O','P')";
			$tmp_billing2[0][] = "SUBSTR(p.bill_code,2,1) IN ('O','P')";
			$tmp_service[0][] = "sv_code is null";
			$tmp_billing1[1][] = "SUBSTR(p.bill_code,2,1) IN ('O','P')";
			$tmp_billing2[1][] = "SUBSTR(p.bill_code,2,1) IN ('O','P')";
			$tmp_service[1][] = "sv_code is null";
		} else if($_vat == 'vat-IO') {
			$tmp_billing1[0][] = "SUBSTR(p.bill_code,2,1) = 'O'";
			$tmp_billing2[0][] = "SUBSTR(p.bill_code,2,1) = 'O'";
			$tmp_service[0][] = "sv_code is null";
			$tmp_billing1[1][] = "SUBSTR(p.bill_code,2,1) = 'O'";
			$tmp_billing2[1][] = "SUBSTR(p.bill_code,2,1) = 'O'";
			$tmp_service[1][] = "sv_code is null";
		} else if($_vat == 'vat-IP') {
			$tmp_billing1[0][] = "SUBSTR(p.bill_code,2,1) = 'P'";
			$tmp_billing2[0][] = "SUBSTR(p.bill_code,2,1) = 'P'";
			$tmp_service[0][] = "sv_code is null";
			$tmp_billing1[1][] = "SUBSTR(p.bill_code,2,1) = 'P'";
			$tmp_billing2[1][] = "SUBSTR(p.bill_code,2,1) = 'P'";
			$tmp_service[1][] = "sv_code is null";
		} else if ($_vat == 'non') {
			$tmp_billing1[0][] = "SUBSTR(p.bill_code,2,1) IN ('N','X')";
			$tmp_billing2[0][] = "SUBSTR(p.bill_code,2,1) IN ('N','X')";
			$tmp_billing1[1][] = "SUBSTR(p.bill_code,2,1) IN ('N','X')";
			$tmp_billing2[1][] = "SUBSTR(p.bill_code,2,1) IN ('N','X')";
		}

	break;

	// IDC ==============================================================================================================
	case "IDC":

		if ($_filter_doc == "I") {
			$tmp_billing1[0][] = "p.pay_paid > 0 AND pay_note IN ('USUAL', 'DEPOSIT-A', 'RETURN')";
			$tmp_billing2[0][] = "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
		} else if ($_filter_doc == "R") {
			$tmp_billing1[0][] = "p.pay_paid <= 0 AND pay_note IN ('USUAL', 'DEPOSIT-A', 'RETURN')";
			$tmp_billing2[0][] = "p.pay_idx IS NULL";
			$tmp_service[0][] = "svpay_idx is null";
		} else if ($_filter_doc == "CT") {
			$tmp_billing1[0][] = "p.pay_idx IS NULL";
			$tmp_billing2[0][] = "pay_note IN ('CROSS_TRANSFER+')";
			$tmp_service[0][] = "svpay_idx is null";
		} else {
			$tmp_billing1[0][]	= "pay_note IN ('USUAL', 'DEPOSIT-A', 'RETURN')";
			$tmp_billing2[0][]	= "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
		}
		
		if($_marketing != "all") {
			$tmp_billing1[0][] = "cus_responsibility_to = $_marketing";
			$tmp_billing2[0][] = "cus_responsibility_to = $_marketing";
			$tmp_service[0][] = "sv_code is null";
		}
		
		if ($some_date != "") {
			$tmp_billing1[0][] = "p.pay_date = DATE '$some_date'";
			$tmp_billing2[0][] = "p.pay_date = DATE '$some_date'";
			$tmp_service[0][] = "svpay_date = DATE '$some_date'";
		} else {
			$tmp_billing1[0][] = "p.pay_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_billing2[0][] = "p.pay_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_service[0][] = "svpay_date BETWEEN DATE '$period_from' AND '$period_to'";
		}
		
		if($_dept != 'all' && $_dept != 'S') {
			$tmp_billing1[0][] = "p.pay_dept = '$_dept'";
			$tmp_billing2[0][] = "p.pay_dept = '$_dept'";
			$tmp_service[0][] = "sv_code is null";
		} else if($_dept == 'S') {
			$tmp_billing1[0][] = "bill_code is null";
			$tmp_billing2[0][] = "bill_code is null";
		}
		
		if($_vat == 'vat') { 
			$tmp_billing1[0][] = "SUBSTR(p.bill_code,2,1) IN ('O','P')";
			$tmp_billing2[0][] = "SUBSTR(p.bill_code,2,1) IN ('O','P')";
			$tmp_service[0][] = "sv_code is null";
		} else if($_vat == 'vat-IO') {
			$tmp_billing1[0][] = "SUBSTR(p.bill_code,2,1) = 'O'";
			$tmp_billing2[0][] = "SUBSTR(p.bill_code,2,1) = 'O'";
			$tmp_service[0][] = "sv_code is null";
		} else if($_vat == 'vat-IP') {
			$tmp_billing1[0][] = "SUBSTR(p.bill_code,2,1) = 'P'";
			$tmp_billing2[0][] = "SUBSTR(p.bill_code,2,1) = 'P'";
			$tmp_service[0][] = "sv_code is null";
		} else if ($_vat == 'non') {
			$tmp_billing1[0][] = "SUBSTR(p.bill_code,2,1) IN ('N','X')";
			$tmp_billing2[0][] = "SUBSTR(p.bill_code,2,1) IN ('N','X')";
		}

	break;

	// MED ==============================================================================================================
	case "MED":

		if ($_filter_doc == "I") {
			$tmp_billing1[1][] = "p.pay_paid > 0 AND pay_note IN ('USUAL', 'DEPOSIT-A', 'RETURN')";
			$tmp_billing2[1][] = "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
		} else if ($_filter_doc == "R") {
			$tmp_billing1[1][] = "p.pay_paid <= 0 AND pay_note IN ('USUAL', 'DEPOSIT-A', 'RETURN')";
			$tmp_billing2[1][] = "p.pay_idx IS NULL";
			$tmp_service[1][] = "svpay_idx is null";
		} else if ($_filter_doc == "CT") {
			$tmp_billing1[1][] = "p.pay_idx IS NULL";
			$tmp_billing2[1][] = "pay_note IN ('CROSS_TRANSFER+')";
			$tmp_service[1][] = "svpay_idx is null";
		} else {
			$tmp_billing1[1][]	= "pay_note IN ('USUAL', 'DEPOSIT-A', 'RETURN')";
			$tmp_billing2[1][]	= "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
		}
		
		if($_marketing != "all") {
			$tmp_billing1[1][] = "cus_responsibility_to = $_marketing";
			$tmp_billing2[1][] = "cus_responsibility_to = $_marketing";
			$tmp_service[1][] = "sv_code is null";
		}
		
		if ($some_date != "") {
			$tmp_billing1[1][] = "p.pay_date = DATE '$some_date'";
			$tmp_billing2[1][] = "p.pay_date = DATE '$some_date'";
			$tmp_service[1][] = "svpay_date = DATE '$some_date'";
		} else {
			$tmp_billing1[1][] = "p.pay_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_billing2[1][] = "p.pay_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_service[1][] = "svpay_date BETWEEN DATE '$period_from' AND '$period_to'";
		}
		
		if($_dept != 'all' && $_dept != 'S') {
			$tmp_billing1[1][] = "p.pay_dept = '$_dept'";
			$tmp_billing2[1][] = "p.pay_dept = '$_dept'";
			$tmp_service[1][] = "sv_code is null";
		} else if($_dept == 'S') {
			$tmp_billing1[1][] = "bill_code is null";
			$tmp_billing2[1][] = "bill_code is null";
		}
		
		if($_vat == 'vat') { 
			$tmp_billing1[1][] = "SUBSTR(p.bill_code,2,1) IN ('O','P')";
			$tmp_billing2[1][] = "SUBSTR(p.bill_code,2,1) IN ('O','P')";
			$tmp_service[1][] = "sv_code is null";
		} else if($_vat == 'vat-IO') {
			$tmp_billing1[1][] = "SUBSTR(p.bill_code,2,1) = 'O'";
			$tmp_billing2[1][] = "SUBSTR(p.bill_code,2,1) = 'O'";
			$tmp_service[1][] = "sv_code is null";
		} else if($_vat == 'vat-IP') {
			$tmp_billing1[1][] = "SUBSTR(p.bill_code,2,1) = 'P'";
			$tmp_billing2[1][] = "SUBSTR(p.bill_code,2,1) = 'P'";
			$tmp_service[1][] = "sv_code is null";
		} else if ($_vat == 'non') {
			$tmp_billing1[1][] = "SUBSTR(p.bill_code,2,1) IN ('N','X')";
			$tmp_billing2[1][] = "SUBSTR(p.bill_code,2,1) IN ('N','X')";
		}

	break;

	// MEP ==============================================================================================================
	case "MEP":

		if ($_filter_doc == "I") {
			$tmp_billing1[0][] = "p.pay_paid > 0 AND pay_note IN ('USUAL', 'DEPOSIT-A', 'RETURN')";
			$tmp_billing2[0][] = "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
		} else if ($_filter_doc == "R") {
			$tmp_billing1[0][] = "p.pay_paid <= 0 AND pay_note IN ('USUAL', 'DEPOSIT-A', 'RETURN')";
			$tmp_billing2[0][] = "p.pay_idx IS NULL";
			$tmp_service[0][] = "svpay_idx is null";
		} else if ($_filter_doc == "CT") {
			$tmp_billing1[0][] = "p.pay_idx IS NULL";
			$tmp_billing2[0][] = "pay_note IN ('CROSS_TRANSFER+')";
			$tmp_service[0][] = "svpay_idx is null";
		} else {
			$tmp_billing1[0][]	= "pay_note IN ('USUAL', 'DEPOSIT-A', 'RETURN')";
			$tmp_billing2[0][]	= "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
		}
		
		if($_marketing != "all") {
			$tmp_billing1[0][] = "cus_responsibility_to = $_marketing";
			$tmp_billing2[0][] = "cus_responsibility_to = $_marketing";
			$tmp_service[0][] = "sv_code is null";
		}
		
		if ($some_date != "") {
			$tmp_billing1[0][] = "p.pay_date = DATE '$some_date'";
			$tmp_billing2[0][] = "p.pay_date = DATE '$some_date'";
			$tmp_service[0][] = "svpay_date = DATE '$some_date'";
		} else {
			$tmp_billing1[0][] = "p.pay_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_billing2[0][] = "p.pay_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_service[0][] = "svpay_date BETWEEN DATE '$period_from' AND '$period_to'";
		}
		
		if($_dept != 'all' && $_dept != 'S') {
			$tmp_billing1[0][] = "p.pay_dept = '$_dept'";
			$tmp_billing2[0][] = "p.pay_dept = '$_dept'";
			$tmp_service[0][] = "sv_code is null";
		} else if($_dept == 'S') {
			$tmp_billing1[0][] = "bill_code is null";
			$tmp_billing2[0][] = "bill_code is null";
		}
		
		if($_vat == 'vat') { 
			$tmp_billing1[0][] = "SUBSTR(p.bill_code,2,1) IN ('O','P')";
			$tmp_billing2[0][] = "SUBSTR(p.bill_code,2,1) IN ('O','P')";
			$tmp_service[0][] = "sv_code is null";
		} else if($_vat == 'vat-IO') {
			$tmp_billing1[0][] = "SUBSTR(p.bill_code,2,1) = 'O'";
			$tmp_billing2[0][] = "SUBSTR(p.bill_code,2,1) = 'O'";
			$tmp_service[0][] = "sv_code is null";
		} else if($_vat == 'vat-IP') {
			$tmp_billing1[0][] = "SUBSTR(p.bill_code,2,1) = 'P'";
			$tmp_billing2[0][] = "SUBSTR(p.bill_code,2,1) = 'P'";
			$tmp_service[0][] = "sv_code is null";
		} else if ($_vat == 'non') {
			$tmp_billing1[0][] = "SUBSTR(p.bill_code,2,1) IN ('N','X')";
			$tmp_billing2[0][] = "SUBSTR(p.bill_code,2,1) IN ('N','X')";
		}

	break;

}

$strWhere[0]	= implode(" AND ", $tmp_billing1[0]);
$strWhere[1]	= implode(" AND ", $tmp_billing2[0]);
$strWhere[2]	= implode(" AND ", $tmp_service[0]);
$strWhere[3]	= implode(" AND ", $tmp_billing1[1]);
$strWhere[4]	= implode(" AND ", $tmp_billing2[1]);
$strWhere[5]	= implode(" AND ", $tmp_service[1]);
/*
echo "<pre>";
var_dump($strWhere);
echo "</pre>";
*/
?>