<?php
//SET WHERE PARAMETER
$tmp_billing1 = array();
$tmp_billing2 = array();
$tmp_service	= array();
$strWhere		= array();

switch (ZKP_URL) {
	// ALL ==============================================================================================================
	case "ALL":

		if($_order_by == "1") {				// INDOCORE
			$tmp_billing1[0][]	= "bill_ordered_by = 1";
			$tmp_billing2[0][]	= "bill_ordered_by = 1";
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
		} else if($_order_by == "4") {		// INDOCORE $ MEDIKUS
			$tmp_billing1[1][]	= "bill_code is null";
			$tmp_billing2[1][]	= "bill_code is null";
			$tmp_service[1][]	= "sv_code is null";
		} 

		if($_filter_doc == 'I') {
			$tmp_billing1[0][] = "pay_paid > 0 AND pay_note NOT IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
			$tmp_billing2[0][] = "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
			$tmp_billing1[1][] = "pay_paid > 0 AND pay_note NOT IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
			$tmp_billing2[1][] = "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
		} else if($_filter_doc == 'R') {
			$tmp_billing1[0][] = "pay_paid < 0 AND pay_note NOT IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
			$tmp_billing2[0][] = "pay_idx IS NULL";
			$tmp_service[0][] = "sv_code is null";
			$tmp_billing1[1][] = "pay_paid < 0 AND pay_note NOT IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
			$tmp_billing2[1][] = "pay_idx IS NULL";
			$tmp_service[1][] = "sv_code is null";
		} else if($_filter_doc == 'CT') {
			$tmp_billing1[0][] = "pay_idx IS NULL";
			$tmp_billing2[0][] = "pay_note IN ('CROSS_TRANSFER+')";
			$tmp_service[0][] = "sv_code is null";
			$tmp_billing1[1][] = "pay_idx IS NULL";
			$tmp_billing2[1][] = "pay_note IN ('CROSS_TRANSFER+')";
			$tmp_service[1][] = "sv_code is null";
		} else {
			$tmp_billing1[0][] = "pay_note NOT IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
			$tmp_billing2[0][] = "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
			$tmp_billing1[1][] = "pay_note NOT IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
			$tmp_billing2[1][] = "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
		}

		if($_dept != 'all' && $_dept != 'S') {
			$tmp_billing1[0][] = "bill_dept = '$_dept'";
			$tmp_billing2[0][] = "bill_dept = '$_dept'";
			$tmp_service[0][] = "sv_code is null";
			$tmp_billing1[1][] = "bill_dept = '$_dept'";
			$tmp_billing2[1][] = "bill_dept = '$_dept'";
			$tmp_service[1][] = "sv_code is null";
		} else if($_dept == 'S') {
			$tmp_billing1[0][] = "bill_code is null";
			$tmp_billing2[0][] = "bill_code is null";
			$tmp_billing1[1][] = "bill_code is null";
			$tmp_billing2[1][] = "bill_code is null";
		}

		if ($some_date != "") {
			$tmp_billing1[0][] = "p.pay_date = DATE '$some_date'";
			$tmp_billing2[0][] = "p.pay_date = DATE '$some_date'";
			$tmp_service[0][] = "svpay_date = DATE '$some_date'";
			$tmp_billing1[1][] = "p.pay_date = DATE '$some_date'";
			$tmp_billing2[1][] = "p.pay_date = DATE '$some_date'";
			$tmp_service[1][] = "svpay_date = DATE '$some_date'";
		} else {
			$tmp_billing1[0][] = "p.pay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_billing21[0][] = "p.pay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_service[0][] = "svpay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_billing1[1][] = "p.pay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_billing21[1][] = "p.pay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_service[1][] = "svpay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_billing1[0][] = "bill_vat > 0";
			$tmp_billing2[0][] = "bill_vat > 0";
			$tmp_service[0][] = "sv_code is null";
			$tmp_billing1[1][] = "bill_vat > 0";
			$tmp_billing2[1][] = "bill_vat > 0";
			$tmp_service[1][] = "sv_code is null";
		} else if($_vat == 'vat-IO') {
			$tmp_billing1[0][] = "bill_type_pajak = 'IO'";
			$tmp_billing2[0][] = "bill_type_pajak = 'IO'";
			$tmp_service[0][] = "sv_code is null";
			$tmp_billing1[1][] = "bill_type_pajak = 'IO'";
			$tmp_billing2[1][] = "bill_type_pajak = 'IO'";
			$tmp_service[1][] = "sv_code is null";
		} else if($_vat == 'vat-IP') {
			$tmp_billing1[0][] = "bill_type_pajak = 'IP'";
			$tmp_billing2[0][] = "bill_type_pajak = 'IP'";
			$tmp_service[0][] = "sv_code is null";
			$tmp_billing1[1][] = "bill_type_pajak = 'IP'";
			$tmp_billing2[1][] = "bill_type_pajak = 'IP'";
			$tmp_service[1][] = "sv_code is null";
		} else if ($_vat == 'non') {
			$tmp_billing1[0][] = "bill_vat = 0";
			$tmp_billing2[0][] = "bill_vat = 0";
			$tmp_billing1[1][] = "bill_vat = 0";
			$tmp_billing2[1][] = "bill_vat = 0";
		}
		
		if($cboSearchType != '' && $txtSearch != '') {
			$searchType = array("byPayment"=>1, "byDeduction"=>2);
			$tmp_billing1[0][] = "idc_isPayDescTrue({$searchType[$cboSearchType]}, pay_idx, '%$txtSearch%') = true";
			$tmp_billing2[0][] = "idc_isPayDescTrue({$searchType[$cboSearchType]}, pay_idx, '%$txtSearch%') = true";
			$tmp_billing1[1][] = "med_isPayDescTrue({$searchType[$cboSearchType]}, pay_idx, '%$txtSearch%') = true";
			$tmp_billing2[1][] = "med_isPayDescTrue({$searchType[$cboSearchType]}, pay_idx, '%$txtSearch%') = true";

			if($searchType[$cboSearchType] == 1) {
				$tmp_service[0][] = "idc_isPayDescTrue(3, svpay_idx, '%$txtSearch%') = true";
				$tmp_service[1][] = "med_isPayDescTrue(3, svpay_idx, '%$txtSearch%') = true";
			} else if($searchType[$cboSearchType] == 1) {
				$tmp_service[0][] = "svpay_idx IS NULL";
				$tmp_service[1][] = "svpay_idx IS NULL";
			} else if($searchType[$cboSearchType] == 2)	 {
				$tmp_service[0][] = "svpay_idx IS NULL";
				$tmp_service[1][] = "svpay_idx IS NULL";
			}
		}
		
		$tmp_billing1[0][]	= "bill_ship_to = '$_cus_code'";
		$tmp_billing2[0][]	= "bill_ship_to = '$_cus_code'";
		$tmp_service[0][]	= "c.cus_code = '$_cus_code'";
		$tmp_billing1[1][]	= "bill_ship_to = '$_cus_code'";
		$tmp_billing2[1][]	= "bill_ship_to = '$_cus_code'";
		$tmp_service[1][]	= "c.cus_code = '$_cus_code'";

	break;

	// IDC ==============================================================================================================
	case "IDC":

		if($_filter_doc == 'I') {
			$tmp_billing1[0][] = "pay_paid > 0 AND pay_note NOT IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
			$tmp_billing2[0][] = "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
		} else if($_filter_doc == 'R') {
			$tmp_billing1[0][] = "pay_paid < 0 AND pay_note NOT IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
			$tmp_billing2[0][] = "pay_idx IS NULL";
		} else if($_filter_doc == 'CT') {
			$tmp_billing1[0][] = "pay_idx IS NULL";
			$tmp_billing2[0][] = "pay_note IN ('CROSS_TRANSFER+')";
		} else {
			$tmp_billing1[0][] = "pay_note NOT IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
			$tmp_billing2[0][] = "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
		}
		
		if ($some_date != "") {
			$tmp_billing1[0][] = "p.pay_date = DATE '$some_date'";
			$tmp_billing2[0][] = "p.pay_date = DATE '$some_date'";
		} else {
			$tmp_billing1[0][] = "p.pay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_billing21[0][] = "p.pay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_billing1[0][] = "bill_vat > 0";
			$tmp_billing2[0][] = "bill_vat > 0";
		} else if($_vat == 'vat-IO') {
			$tmp_billing1[0][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_billing2[0][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
		} else if($_vat == 'vat-IP') {
			$tmp_billing1[0][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
			$tmp_billing2[0][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";	
		} else if ($_vat == 'non') {
			$tmp_billing1[0][] = "bill_vat = 0";
			$tmp_billing2[0][] = "bill_vat = 0";
		}
		
		if($cboSearchType != '' && $txtSearch != '') {
			$searchType = array("byPayment"=>1, "byDeduction"=>2);
			$tmp_billing1[0][] = ZKP_SQL."_isPayDescTrue({$searchType[$cboSearchType]}, pay_idx, '%$txtSearch%') = true";
			$tmp_billing2[0][] = ZKP_SQL."_isPayDescTrue({$searchType[$cboSearchType]}, pay_idx, '%$txtSearch%') = true";
		}
		
		$tmp_billing1[0][] = "bill_ship_to = '$_cus_code'";
		$tmp_billing2[0][] = "bill_ship_to = '$_cus_code'";

	break;

	// MED ==============================================================================================================
	case "MED":

		if($_filter_doc == 'I') {
			$tmp_billing1[1][] = "pay_paid > 0 AND pay_note NOT IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
			$tmp_billing2[1][] = "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
		} else if($_filter_doc == 'R') {
			$tmp_billing1[1][] = "pay_paid < 0 AND pay_note NOT IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
			$tmp_billing2[1][] = "pay_idx IS NULL";
		} else if($_filter_doc == 'CT') {
			$tmp_billing1[1][] = "pay_idx IS NULL";
			$tmp_billing2[1][] = "pay_note IN ('CROSS_TRANSFER+')";
		} else {
			$tmp_billing1[1][] = "pay_note NOT IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
			$tmp_billing2[1][] = "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
		}
		
		if ($some_date != "") {
			$tmp_billing1[1][] = "p.pay_date = DATE '$some_date'";
			$tmp_billing2[1][] = "p.pay_date = DATE '$some_date'";
		} else {
			$tmp_billing1[1][] = "p.pay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_billing21[1][] = "p.pay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_billing1[1][] = "bill_vat > 0";
			$tmp_billing2[1][] = "bill_vat > 0";
		} else if($_vat == 'vat-IO') {
			$tmp_billing1[1][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_billing2[1][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
		} else if($_vat == 'vat-IP') {
			$tmp_billing1[1][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
			$tmp_billing2[1][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";	
		} else if ($_vat == 'non') {
			$tmp_billing1[1][] = "bill_vat = 0";
			$tmp_billing2[1][] = "bill_vat = 0";
		}
		
		if($cboSearchType != '' && $txtSearch != '') {
			$searchType = array("byPayment"=>1, "byDeduction"=>2);
			$tmp_billing1[1][] = ZKP_SQL."_isPayDescTrue({$searchType[$cboSearchType]}, pay_idx, '%$txtSearch%') = true";
			$tmp_billing2[1][] = ZKP_SQL."_isPayDescTrue({$searchType[$cboSearchType]}, pay_idx, '%$txtSearch%') = true";
		}
		
		$tmp_billing1[1][] = "bill_ship_to = '$_cus_code'";
		$tmp_billing2[1][] = "bill_ship_to = '$_cus_code'";

	break;

	// MEP ==============================================================================================================
	case "MEP":

		if($_filter_doc == 'I') {
			$tmp_billing1[0][] = "pay_paid > 0 AND pay_note NOT IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
			$tmp_billing2[0][] = "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
		} else if($_filter_doc == 'R') {
			$tmp_billing1[0][] = "pay_paid < 0 AND pay_note NOT IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
			$tmp_billing2[0][] = "pay_idx IS NULL";
		} else if($_filter_doc == 'CT') {
			$tmp_billing1[0][] = "pay_idx IS NULL";
			$tmp_billing2[0][] = "pay_note IN ('CROSS_TRANSFER+')";
		} else {
			$tmp_billing1[0][] = "pay_note NOT IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
			$tmp_billing2[0][] = "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
		}
		
		if ($some_date != "") {
			$tmp_billing1[0][] = "p.pay_date = DATE '$some_date'";
			$tmp_billing2[0][] = "p.pay_date = DATE '$some_date'";
		} else {
			$tmp_billing1[0][] = "p.pay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_billing21[0][] = "p.pay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_billing1[0][] = "bill_vat > 0";
			$tmp_billing2[0][] = "bill_vat > 0";
		} else if($_vat == 'vat-IO') {
			$tmp_billing1[0][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_billing2[0][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
		} else if($_vat == 'vat-IP') {
			$tmp_billing1[0][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
			$tmp_billing2[0][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";	
		} else if ($_vat == 'non') {
			$tmp_billing1[0][] = "bill_vat = 0";
			$tmp_billing2[0][] = "bill_vat = 0";
		}
		
		if($cboSearchType != '' && $txtSearch != '') {
			$searchType = array("byPayment"=>1, "byDeduction"=>2);
			$tmp_billing1[0][] = ZKP_SQL."_isPayDescTrue({$searchType[$cboSearchType]}, pay_idx, '%$txtSearch%') = true";
			$tmp_billing2[0][] = ZKP_SQL."_isPayDescTrue({$searchType[$cboSearchType]}, pay_idx, '%$txtSearch%') = true";
		}
		
		$tmp_billing1[0][] = "bill_ship_to = '$_cus_code'";
		$tmp_billing2[0][] = "bill_ship_to = '$_cus_code'";

	break;


}

$strWhere[0]	= implode(" AND ", $tmp_billing1[0]);
$strWhere[1]	= implode(" AND ", $tmp_billing2[0]);
$strWhere[2]	= implode(" AND ", $tmp_service[0]);
$strWhere[3]	= implode(" AND ", $tmp_billing1[1]);
$strWhere[4]	= implode(" AND ", $tmp_billing2[1]);
$strWhere[5]	= implode(" AND ", $tmp_service[1]);
?>