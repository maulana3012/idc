<?php
$tmp_bill	= array();
$tmp_turn	= array();
$tmp_paid	= array();
$strWhere	= array();
$strWherePay= array();

switch (ZKP_URL) {
	// ALL ==============================================================================================================
	case "ALL":

		if($_order_by == "1") {				// INDOCORE
			$tmp_bill[0][]	= "bill_ordered_by = 1";
			$tmp_turn[0][]	= "turn_ordered_by = 1";
			$tmp_bill[1][]	= "substr(bill_code,1,1) = 'I'";
			$tmp_turn[1][]	= "turn_code is null";
			$tmp_paid[1][]	= "sv_code is null";
		} else if($_order_by == "2") {		// MEDISINDO
			$tmp_bill[0][]	= "bill_code is null";
			$tmp_turn[0][]	= "turn_code is null";
			$tmp_paid[0][]	= "sv_code is null";
		} else if($_order_by == "3") {		// MEDIKUS
			$tmp_bill[0][]	= "bill_ordered_by = 2";
			$tmp_turn[0][]	= "turn_ordered_by = 2";
			$tmp_paid[0][]	= "substr(bill_code,1,1) = 'M'";
			$tmp_bill[1][]	= "bill_code is null";
			$tmp_turn[1][]	= "turn_code is null";
			$tmp_paid[1][]	= "sv_code is null";
		}

		if ($_filter_doc == "I") {
			$tmp_turn[0][]	= "turn_code is NULL";
			$tmp_paid[0][]	= "pay_note IN ('USUAL','DEPOSIT-A', 'RETURN','CROSS_TRANSFER+','CROSS_TRANSFER-')";
			$tmp_turn[1][]	= "turn_code is NULL";
			$tmp_paid[1][]	= "pay_note IN ('USUAL','DEPOSIT-A', 'RETURN','CROSS_TRANSFER+','CROSS_TRANSFER-')";
		} else if ($_filter_doc == "R") {
			$tmp_bill[0][]	= "bill_code is NULL";
			$tmp_paid[0][]	= "pay_paid <= 0 AND pay_note IN ('USUAL','DEPOSIT-A', 'RETURN')";
			$tmp_bill[1][]	= "bill_code is NULL";
			$tmp_paid[1][]	= "pay_paid <= 0 AND pay_note IN ('USUAL','DEPOSIT-A', 'RETURN')";
		} else {
			$tmp_paid[0][]	  = "pay_note IN ('USUAL','DEPOSIT-A', 'RETURN','CROSS_TRANSFER+','CROSS_TRANSFER-')";
			$tmp_paid[1][]	  = "pay_note IN ('USUAL','DEPOSIT-A', 'RETURN','CROSS_TRANSFER+','CROSS_TRANSFER-')";
		}
		
		if($_dept != 'all') {
			$tmp_bill[0][] = "bill_dept = '$_dept'";
			$tmp_turn[0][] = "turn_dept = '$_dept'";
			$tmp_paid[0][] = "bill_dept = '$_dept'";
			$tmp_bill[1][] = "bill_dept = '$_dept'";
			$tmp_turn[1][] = "turn_dept = '$_dept'";
			$tmp_paid[1][] = "bill_dept = '$_dept'";
		}
		
		if ($some_date != "") {
			$tmp_bill[0][] = "bill_inv_date = DATE '$some_date'";
			$tmp_turn[0][] = "turn_return_date = DATE '$some_date'";
			$tmp_paid[0][] = "pay_date = DATE '$some_date'";
			$tmp_bill[1][] = "bill_inv_date = DATE '$some_date'";
			$tmp_turn[1][] = "turn_return_date = DATE '$some_date'";
			$tmp_paid[1][] = "pay_date = DATE '$some_date'";
		} else {
			$tmp_bill[0][]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_turn[0][]	= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_paid[0][]	= "pay_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_bill[1][]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_turn[1][]	= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_paid[1][]	= "pay_date BETWEEN DATE '$period_from' AND '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_bill[0][]	= "bill_vat > 0";
			$tmp_turn[0][]	= "turn_vat > 0";
			$tmp_paid[0][]	= "bill_vat > 0";
			$tmp_bill[1][]	= "bill_vat > 0";
			$tmp_turn[1][]	= "turn_vat > 0";
			$tmp_paid[1][]	= "bill_vat > 0";
		} else if($_vat == 'vat-IO') {
			$tmp_bill[0][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_turn[0][]	= "turn_vat > 0";
			$tmp_paid[0][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_bill[1][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_turn[1][]	= "turn_vat > 0";
			$tmp_paid[1][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
		}else if($_vat == 'vat-IP') {
			$tmp_bill[0][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
			$tmp_turn[0][]	= "turn_code = NULL";
			$tmp_paid[0][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
			$tmp_bill[1][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
			$tmp_turn[1][]	= "turn_code = NULL";
			$tmp_paid[1][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
		} else if ($_vat == 'non') {
			$tmp_bill[0][]	= "bill_vat = 0";
			$tmp_turn[0][]	= "turn_vat = 0";
			$tmp_paid[0][]	= "bill_vat = 0";
			$tmp_bill[1][]	= "bill_vat = 0";
			$tmp_turn[1][]	= "turn_vat = 0";
			$tmp_paid[1][]	= "bill_vat = 0";
		}

		$tmp_turn[0][] = "turn_total_return > 0";
		$tmp_turn[1][] = "turn_total_return > 0";

	break;

	// IDC==============================================================================================================
	case "IDC":

		if ($_filter_doc == "I") {
			$tmp_turn[0][]	= "turn_code is NULL";
			$tmp_paid[0][]	= "pay_note IN ('USUAL','DEPOSIT-A', 'RETURN','CROSS_TRANSFER+','CROSS_TRANSFER-')";
		} else if ($_filter_doc == "R") {
			$tmp_bill[0][]	= "bill_code is NULL";
			$tmp_paid[0][]	= "pay_paid <= 0 AND pay_note IN ('USUAL','DEPOSIT-A', 'RETURN')";
		} else {
			$tmp_paid[0][]	  = "pay_note IN ('USUAL','DEPOSIT-A', 'RETURN','CROSS_TRANSFER+','CROSS_TRANSFER-')";
		}
		
		if($_dept != 'all') {
			$tmp_bill[0][] = "bill_dept = '$_dept'";
			$tmp_turn[0][] = "turn_dept = '$_dept'";
			$tmp_paid[0][] = "bill_dept = '$_dept'";
		}
		
		if ($some_date != "") {
			$tmp_bill[0][] = "bill_inv_date = DATE '$some_date'";
			$tmp_turn[0][] = "turn_return_date = DATE '$some_date'";
			$tmp_paid[0][] = "pay_date = DATE '$some_date'";
		} else {
			$tmp_bill[0][]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_turn[0][]	= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_paid[0][]	= "pay_date BETWEEN DATE '$period_from' AND '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_bill[0][]	= "bill_vat > 0";
			$tmp_turn[0][]	= "turn_vat > 0";
			$tmp_paid[0][]	= "bill_vat > 0";
		} else if($_vat == 'vat-IO') {
			$tmp_bill[0][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_turn[0][]	= "turn_vat > 0";
			$tmp_paid[0][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
		}else if($_vat == 'vat-IP') {
			$tmp_bill[0][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
			$tmp_turn[0][]	= "turn_code = NULL";
			$tmp_paid[0][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
		} else if ($_vat == 'non') {
			$tmp_bill[0][]	= "bill_vat = 0";
			$tmp_turn[0][]	= "turn_vat = 0";
			$tmp_paid[0][]	= "bill_vat = 0";
		}

		$tmp_turn[0][] = "turn_total_return > 0";

	break;

	// MED ==============================================================================================================
	case "MED":

		if ($_filter_doc == "I") {
			$tmp_turn[1][]	= "turn_code is NULL";
			$tmp_paid[1][]	= "pay_note IN ('USUAL','DEPOSIT-A', 'RETURN','CROSS_TRANSFER+','CROSS_TRANSFER-')";
		} else if ($_filter_doc == "R") {
			$tmp_bill[1][]	= "bill_code is NULL";
			$tmp_paid[1][]	= "pay_paid <= 0 AND pay_note IN ('USUAL','DEPOSIT-A', 'RETURN')";
		} else {
			$tmp_paid[1][]	  = "pay_note IN ('USUAL','DEPOSIT-A', 'RETURN','CROSS_TRANSFER+','CROSS_TRANSFER-')";
		}
		
		if($_dept != 'all') {
			$tmp_bill[1][] = "bill_dept = '$_dept'";
			$tmp_turn[1][] = "turn_dept = '$_dept'";
			$tmp_paid[1][] = "bill_dept = '$_dept'";
		}
		
		if ($some_date != "") {
			$tmp_bill[1][] = "bill_inv_date = DATE '$some_date'";
			$tmp_turn[1][] = "turn_return_date = DATE '$some_date'";
			$tmp_paid[1][] = "pay_date = DATE '$some_date'";
		} else {
			$tmp_bill[1][]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_turn[1][]	= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_paid[1][]	= "pay_date BETWEEN DATE '$period_from' AND '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_bill[1][]	= "bill_vat > 0";
			$tmp_turn[1][]	= "turn_vat > 0";
			$tmp_paid[1][]	= "bill_vat > 0";
		} else if($_vat == 'vat-IO') {
			$tmp_bill[1][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_turn[1][]	= "turn_vat > 0";
			$tmp_paid[1][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
		}else if($_vat == 'vat-IP') {
			$tmp_bill[1][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
			$tmp_turn[1][]	= "turn_code = NULL";
			$tmp_paid[1][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
		} else if ($_vat == 'non') {
			$tmp_bill[1][]	= "bill_vat = 0";
			$tmp_turn[1][]	= "turn_vat = 0";
			$tmp_paid[1][]	= "bill_vat = 0";
		}

		$tmp_turn[1][] = "turn_total_return > 0";

	break;

	// MEP ==============================================================================================================
	case "MEP":

		if ($_filter_doc == "I") {
			$tmp_turn[0][]	= "turn_code is NULL";
			$tmp_paid[0][]	= "pay_note IN ('USUAL','DEPOSIT-A', 'RETURN','CROSS_TRANSFER+','CROSS_TRANSFER-')";
		} else if ($_filter_doc == "R") {
			$tmp_bill[0][]	= "bill_code is NULL";
			$tmp_paid[0][]	= "pay_paid <= 0 AND pay_note IN ('USUAL','DEPOSIT-A', 'RETURN')";
		} else {
			$tmp_paid[0][]	  = "pay_note IN ('USUAL','DEPOSIT-A', 'RETURN','CROSS_TRANSFER+','CROSS_TRANSFER-')";
		}
		
		if($_dept != 'all') {
			$tmp_bill[0][] = "bill_dept = '$_dept'";
			$tmp_turn[0][] = "turn_dept = '$_dept'";
			$tmp_paid[0][] = "bill_dept = '$_dept'";
		}
		
		if ($some_date != "") {
			$tmp_bill[0][] = "bill_inv_date = DATE '$some_date'";
			$tmp_turn[0][] = "turn_return_date = DATE '$some_date'";
			$tmp_paid[0][] = "pay_date = DATE '$some_date'";
		} else {
			$tmp_bill[0][]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_turn[0][]	= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_paid[0][]	= "pay_date BETWEEN DATE '$period_from' AND '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_bill[0][]	= "bill_vat > 0";
			$tmp_turn[0][]	= "turn_vat > 0";
			$tmp_paid[0][]	= "bill_vat > 0";
		} else if($_vat == 'vat-IO') {
			$tmp_bill[0][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_turn[0][]	= "turn_vat > 0";
			$tmp_paid[0][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
		}else if($_vat == 'vat-IP') {
			$tmp_bill[0][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
			$tmp_turn[0][]	= "turn_code = NULL";
			$tmp_paid[0][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
		} else if ($_vat == 'non') {
			$tmp_bill[0][]	= "bill_vat = 0";
			$tmp_turn[0][]	= "turn_vat = 0";
			$tmp_paid[0][]	= "bill_vat = 0";
		}

		$tmp_turn[0][] = "turn_total_return > 0";

	break;

}

$strWhere[0]	= implode(" AND ", $tmp_bill[0]);
$strWhere[1]	= implode(" AND ", $tmp_turn[0]);
$strWhere[2]	= implode(" AND ", $tmp_bill[1]);
$strWhere[3]	= implode(" AND ", $tmp_turn[1]);

$strWherePay[0]	= implode(" AND ", $tmp_paid[0]);
$strWherePay[1]	= implode(" AND ", $tmp_paid[1]);
/*
echo "<pre>";
var_dump($tmp_bill);
echo "</pre>";
*/
?>