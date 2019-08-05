<?php
//SET WHERE PARAMETER
$tmp_bill	= array();
$tmp_turn	= array();
$tmp_cs		= array();
$strWhere	= array();

switch (ZKP_URL) {
	// ALL ==============================================================================================================
	case "ALL":

		if($_order_by == "1") {				// INDOCORE
			$tmp_bill[0][]	= "bill_ordered_by = 1";
			$tmp_turn[0][]	= "turn_ordered_by = 1";
			$tmp_bill[1][]	= "bill_code is null";
			$tmp_turn[1][]	= "turn_code is null";
			$tmp_cs[1][]	= "sv_code is null";
		} else if($_order_by == "2") {		// MEDISINDO
			$tmp_bill[0][]	= "bill_code is null";
			$tmp_turn[0][]	= "turn_code is null";
			$tmp_cs[0][]	= "sv_code is null";
		} else if($_order_by == "3") {		// MEDIKUS
			$tmp_bill[0][]	= "bill_ordered_by = 2";
			$tmp_turn[0][]	= "turn_ordered_by = 2";
			$tmp_cs[0][]	= "sv_code is null";
			$tmp_bill[1][]	= "bill_code is null";
			$tmp_turn[1][]	= "turn_code is null";
			$tmp_cs[1][]	= "sv_code is null";
		} else if($_order_by == "4") {		// INDOCORE $ MEDIKUS
			$tmp_bill[1][]	= "bill_code is null";
			$tmp_turn[1][]	= "turn_code is null";
			$tmp_dr[1][]	= "dr_code is null";
		} 

		if($_filter_doc == 'I'){
			$tmp_turn[0][] = "turn_code is null";
			$tmp_turn[1][] = "turn_code is null";
		} else if($_filter_doc == 'R') {
			$tmp_bill[0][] = "bill_code is null";
			$tmp_bill[1][] = "bill_code is null";
			$tmp_cs[0][]   = "sv_code is null";
		}
		
		if ($some_date != "") {
			$tmp_bill[0][] = "bill_payment_giro_due = DATE '$some_date'";
			$tmp_turn[0][] = "turn_return_date = DATE '$some_date'";
			$tmp_cs[0][]   = "sv_due_date = DATE '$some_date'";
			$tmp_bill[1][] = "bill_payment_giro_due = DATE '$some_date'";
			$tmp_turn[1][] = "turn_return_date = DATE '$some_date'";
			$tmp_cs[1][]   = "sv_due_date = DATE '$some_date'";
		} else {
			$tmp_bill[0][] = "bill_payment_giro_due  BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_turn[0][] = "turn_return_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_cs[0][]   = "sv_due_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_bill[1][] = "bill_payment_giro_due  BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_turn[1][] = "turn_return_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_cs[1][]   = "sv_due_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_bill[0][]	= "bill_vat > 0";
			$tmp_turn[0][] = "turn_vat > 0";  
			$tmp_cs[0][]   = "sv_code is null";
			$tmp_bill[1][]	= "bill_vat > 0";
			$tmp_turn[1][] = "turn_vat > 0";  
			$tmp_cs[1][]   = "sv_code is null";
		} else if($_vat == 'vat-IO') {
			$tmp_bill[0][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_turn[0][] = "turn_vat > 0 AND turn_code is null";  
			$tmp_cs[0][]   = "sv_code is null";
			$tmp_bill[1][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_turn[1][] = "turn_vat > 0 AND turn_code is null";  
			$tmp_cs[1][]   = "sv_code is null";
		}else if($_vat == 'vat-IP') {
			$tmp_bill[0][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
			$tmp_turn[0][] = "turn_vat > 0 AND turn_code is null";  
			$tmp_cs[0][]   = "sv_code is null";
			$tmp_bill[1][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
			$tmp_turn[1][] = "turn_vat > 0 AND turn_code is null";  
			$tmp_cs[1][]   = "sv_code is null";
		} else if ($_vat == 'non') {
			$tmp_bill[0][] = "bill_vat = 0";
			$tmp_turn[0][] = "turn_vat = 0";
			$tmp_cs[0][]   = "sv_code is null";
			$tmp_bill[1][] = "bill_vat = 0";
			$tmp_turn[1][] = "turn_vat = 0";
			$tmp_cs[1][]   = "sv_code is null";
		}
		
		if($_status == 'paid') {
			$tmp_bill[0][]	= "bill_remain_amount <= 0";
			$tmp_turn[0][] = "turn_code is null";  
			$tmp_cs[0][]   = "sv_total_remain <= 0";
			$tmp_bill[1][]	= "bill_remain_amount <= 0";
			$tmp_turn[1][] = "turn_code is null";  
			$tmp_cs[1][]   = "sv_total_remain <= 0";
		} else if($_status == 'unpaid') {
			$tmp_bill[0][]	= "bill_total_billing_rev = bill_remain_amount";
			$tmp_turn[0][] = "turn_code is null";
			$tmp_cs[0][]   = "sv_total_remain = sv_total_amount";
			$tmp_bill[1][]	= "bill_total_billing_rev = bill_remain_amount";
			$tmp_turn[1][] = "turn_code is null";
			$tmp_cs[1][]   = "sv_total_remain = sv_total_amount";
		} else if($_status == 'half_paid') {
			$tmp_bill[0][]	= "bill_remain_amount < bill_total_billing_rev AND bill_remain_amount > 0";
			$tmp_turn[0][] = "turn_code is null";  
			$tmp_cs[0][]   = "sv_total_remain < sv_total_amount";
			$tmp_bill[1][]	= "bill_remain_amount < bill_total_billing_rev AND bill_remain_amount > 0";
			$tmp_turn[1][] = "turn_code is null";  
			$tmp_cs[1][]   = "sv_total_remain < sv_total_amount";
		} else if($_status == 'has_bal') {
			$tmp_bill[0][]	= "bill_remain_amount > 0";
			$tmp_turn[0][] = "turn_code is null";
			$tmp_cs[0][]   = "sv_total_remain > 0";
			$tmp_bill[1][]	= "bill_remain_amount > 0";
			$tmp_turn[1][] = "turn_code is null";
			$tmp_cs[1][]   = "sv_total_remain > 0";
		}
		
		if($_dept != 'all') {
			if($_dept != 'S') {
				$tmp_cs[0][]	= "sv_code is null";
				$tmp_bill[0][]	= "bill_dept = '$_dept'";
				$tmp_turn[0][]	= "turn_dept = '$_dept'";
				$tmp_cs[1][]	= "sv_code is null";
				$tmp_bill[1][]	= "bill_dept = '$_dept'";
				$tmp_turn[1][]	= "turn_dept = '$_dept'";
			} else if($_dept == 'S') {
				$tmp_bill[0][]	= "bill_code is null";
				$tmp_turn[0][]	= "turn_dept is null";
				$tmp_bill[1][]	= "bill_code is null";
				$tmp_turn[1][]	= "turn_dept is null";
			}
		}
		
		$tmp_bill[0][]		= "bill_ship_to = '$_cus_code'";
		$tmp_turn[0][]		= "turn_ship_to = '$_cus_code' AND turn_return_condition IN (2,3,4)";
		$tmp_cs[0][]		= "cus_code = '$_cus_code'";
		$tmp_bill[1][]		= "bill_ship_to = '$_cus_code'";
		$tmp_turn[1][]		= "turn_ship_to = '$_cus_code' AND turn_return_condition IN (2,3,4)";
		$tmp_cs[1][]		= "cus_code = '$_cus_code'";

	break;

	// IDC ==============================================================================================================
	case "IDC":

		if($_filter_doc == 'I'){
			$tmp_turn[0][] = "turn_code is null";
		} else if($_filter_doc == 'R') {
			$tmp_bill[0][] = "bill_code is null";
		}
		
		if ($some_date != "") {
			$tmp_bill[0][] = "bill_payment_giro_due = DATE '$some_date'";
			$tmp_turn[0][] = "turn_return_date = DATE '$some_date'";
		} else {
			$tmp_bill[0][] = "bill_payment_giro_due  BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_turn[0][] = "turn_return_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_bill[0][]	= "bill_vat > 0";
			$tmp_turn[0][] = "turn_vat > 0";  
		} else if($_vat == 'vat-IO') {
			$tmp_bill[0][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_turn[0][] = "turn_vat > 0 AND turn_code is null";  
		}else if($_vat == 'vat-IP') {
			$tmp_bill[0][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
			$tmp_turn[0][] = "turn_vat > 0 AND turn_code is null";  
		} else if ($_vat == 'non') {
			$tmp_bill[0][] = "bill_vat = 0";
			$tmp_turn[0][] = "turn_vat = 0";
		}
		
		if($_status == 'paid') {
			$tmp_bill[0][]	= "bill_remain_amount <= 0";
			$tmp_turn[0][] = "turn_code is null";  
		} else if($_status == 'unpaid') {
			$tmp_bill[0][]	= "bill_total_billing_rev = bill_remain_amount";
			$tmp_turn[0][] = "turn_code is null";
		} else if($_status == 'half_paid') {
			$tmp_bill[0][]	= "bill_remain_amount < bill_total_billing_rev AND bill_remain_amount > 0";
			$tmp_turn[0][] = "turn_code is null";  
		} else if($_status == 'has_bal') {
			$tmp_bill[0][]	= "bill_remain_amount > 0";
			$tmp_turn[0][] = "turn_code is null";
		}
		
		$tmp_bill[0][]		= "bill_ship_to = '$_cus_code'";
		$tmp_turn[0][]		= "turn_ship_to = '$_cus_code' AND turn_return_condition IN (2,3,4)";

	break;

	// MED ==============================================================================================================
	case "MED":

		if($_filter_doc == 'I'){
			$tmp_turn[1][] = "turn_code is null";
		} else if($_filter_doc == 'R') {
			$tmp_bill[1][] = "bill_code is null";
		}
		
		if ($some_date != "") {
			$tmp_bill[1][] = "bill_payment_giro_due = DATE '$some_date'";
			$tmp_turn[1][] = "turn_return_date = DATE '$some_date'";
		} else {
			$tmp_bill[1][] = "bill_payment_giro_due  BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_turn[1][] = "turn_return_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_bill[1][]	= "bill_vat > 0";
			$tmp_turn[1][] = "turn_vat > 0";  
		} else if($_vat == 'vat-IO') {
			$tmp_bill[1][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_turn[1][] = "turn_vat > 0 AND turn_code is null";  
		}else if($_vat == 'vat-IP') {
			$tmp_bill[1][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
			$tmp_turn[1][] = "turn_vat > 0 AND turn_code is null";  
		} else if ($_vat == 'non') {
			$tmp_bill[1][] = "bill_vat = 0";
			$tmp_turn[1][] = "turn_vat = 0";
		}
		
		if($_status == 'paid') {
			$tmp_bill[1][]	= "bill_remain_amount <= 0";
			$tmp_turn[1][] = "turn_code is null";  
		} else if($_status == 'unpaid') {
			$tmp_bill[1][]	= "bill_total_billing_rev = bill_remain_amount";
			$tmp_turn[1][] = "turn_code is null";
		} else if($_status == 'half_paid') {
			$tmp_bill[1][]	= "bill_remain_amount < bill_total_billing_rev AND bill_remain_amount > 0";
			$tmp_turn[1][] = "turn_code is null";  
		} else if($_status == 'has_bal') {
			$tmp_bill[1][]	= "bill_remain_amount > 0";
			$tmp_turn[1][] = "turn_code is null";
		}
		
		$tmp_bill[1][]		= "bill_ship_to = '$_cus_code'";
		$tmp_turn[1][]		= "turn_ship_to = '$_cus_code' AND turn_return_condition IN (2,3,4)";

	break;

	// MEP ==============================================================================================================
	case "MEP":

		if($_filter_doc == 'I'){
			$tmp_turn[0][] = "turn_code is null";
		} else if($_filter_doc == 'R') {
			$tmp_bill[0][] = "bill_code is null";
		}
		
		if ($some_date != "") {
			$tmp_bill[0][] = "bill_payment_giro_due = DATE '$some_date'";
			$tmp_turn[0][] = "turn_return_date = DATE '$some_date'";
		} else {
			$tmp_bill[0][] = "bill_payment_giro_due  BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_turn[0][] = "turn_return_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_bill[0][]	= "bill_vat > 0";
			$tmp_turn[0][] = "turn_vat > 0";  
		} else if($_vat == 'vat-IO') {
			$tmp_bill[0][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_turn[0][] = "turn_vat > 0 AND turn_code is null";  
		}else if($_vat == 'vat-IP') {
			$tmp_bill[0][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
			$tmp_turn[0][] = "turn_vat > 0 AND turn_code is null";  
		} else if ($_vat == 'non') {
			$tmp_bill[0][] = "bill_vat = 0";
			$tmp_turn[0][] = "turn_vat = 0";
		}
		
		if($_status == 'paid') {
			$tmp_bill[0][]	= "bill_remain_amount <= 0";
			$tmp_turn[0][] = "turn_code is null";  
		} else if($_status == 'unpaid') {
			$tmp_bill[0][]	= "bill_total_billing_rev = bill_remain_amount";
			$tmp_turn[0][] = "turn_code is null";
		} else if($_status == 'half_paid') {
			$tmp_bill[0][]	= "bill_remain_amount < bill_total_billing_rev AND bill_remain_amount > 0";
			$tmp_turn[0][] = "turn_code is null";  
		} else if($_status == 'has_bal') {
			$tmp_bill[0][]	= "bill_remain_amount > 0";
			$tmp_turn[0][] = "turn_code is null";
		}
		
		$tmp_bill[0][]		= "bill_ship_to = '$_cus_code'";
		$tmp_turn[0][]		= "turn_ship_to = '$_cus_code' AND turn_return_condition IN (2,3,4)";

	break;

}

$strWhere[0]	= implode(" AND ", $tmp_bill[0]);
$strWhere[1]	= implode(" AND ", $tmp_turn[0]);
$strWhere[2]	= implode(" AND ", $tmp_cs[0]);
$strWhere[3]	= implode(" AND ", $tmp_bill[1]);
$strWhere[4]	= implode(" AND ", $tmp_turn[1]);
$strWhere[5]	= implode(" AND ", $tmp_cs[1]);
?>