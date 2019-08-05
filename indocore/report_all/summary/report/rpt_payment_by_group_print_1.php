<?php
//SET WHERE PARAMETER
$tmp_billing1	= array();
$tmp_billing2	= array();
$tmp_service	= array();
$strWhere		= array();

switch (ZKP_URL) {
	// ALL ==============================================================================================================
	case "ALL":

		if($_order_by == "1") {			// INDOCORE
			$tmp_billing1[0][]	= "bill_ordered_by = 1";
			$tmp_billing2[0][]	= "bill_ordered_by = 1";
			$tmp_billing1[1][]	= "bill_code is null";
			$tmp_billing2[1][]	= "bill_code is null";
			$tmp_service[1][]	= "sv_code is null";
		} else if($_order_by == "2") {		// MEDIKUS
			$tmp_billing1[0][]	= "bill_ordered_by = 2";
			$tmp_billing2[0][]	= "bill_ordered_by = 2";
			$tmp_service[0][]	= "sv_code is null";
			$tmp_billing1[1][]	= "bill_code is null";
			$tmp_billing2[1][]	= "bill_code is null";
			$tmp_service[1][]	= "sv_code is null";
		} else if($_order_by == "3") {		// MEDISINDO
			$tmp_billing1[0][]	= "bill_code is null";
			$tmp_billing2[0][]	= "bill_code is null";
			$tmp_service[0][]	= "sv_code is null";
			$tmp_billing1[1][]	= "bill_ordered_by = 1";
			$tmp_billing2[1][]	= "bill_ordered_by = 1";
		} else if($_order_by == "4") {		// SAMUDIA
			$tmp_billing1[0][]	= "bill_code is null";
			$tmp_billing2[0][]	= "bill_code is null";
			$tmp_service[0][]	= "sv_code is null";
			$tmp_billing1[1][]	= "bill_ordered_by = 2";
			$tmp_billing2[1][]	= "bill_ordered_by = 2";
			$tmp_service[1][]	= "sv_code is null";
		} else if($_order_by == "5") {		// INDOCORE & MEDIKUS
			$tmp_billing1[1][]	= "bill_code is null";
			$tmp_billing2[1][]	= "bill_code is null";
			$tmp_service[1][]	= "sv_code is null";
		} else if($_order_by == "6") {		// MEDISINDO & SAMUDIA
			$tmp_billing1[0][]	= "bill_code is null";
			$tmp_billing2[0][]	= "bill_code is null";
			$tmp_service[0][]	= "sv_code is null";
		}

		if($_chk_company == 'off') {
			$tmp_billing1[0][]	= "bill_ship_to NOT IN ('6IDC', '0MSD', '0SMD')";
			$tmp_billing2[0][]	= "bill_ship_to NOT IN ('6IDC', '0MSD', '0SMD')";
			$tmp_service[0][]	= "sv_cus_to NOT IN ('6IDC', '0MSD', '0SMD')";
			$tmp_billing1[1][]	= "bill_ship_to NOT IN ('6IDC', '0MSD', '0SMD')";
			$tmp_billing2[1][]	= "bill_ship_to NOT IN ('6IDC', '0MSD', '0SMD')";
			$tmp_service[1][]	= "sv_cus_to NOT IN ('6IDC', '0MSD', '0SMD')";
		}

		if ($_cug_code != 'all') {
			$tmp_billing1[0][]	= "bill_ship_to IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_billing2[0][]	= "bill_ship_to IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_service[0][]	= "sv_cus_to IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_billing1[1][]	= "bill_ship_to IN (SELECT cus_code FROM med_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_billing2[1][]	= "bill_ship_to IN (SELECT cus_code FROM med_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_service[1][]	= "sv_cus_to IN (SELECT cus_code FROM med_tb_customer WHERE cug_code = '$_cug_code')";
		
			$sql_billing1[0]	= " SELECT (SELECT cug_name FROM idc_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
			$sql_billing2[0]	= " SELECT (SELECT cug_name FROM idc_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
			$sql_service[0]		= " SELECT (SELECT cug_name FROM idc_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
			$sql_billing1[1]	= " SELECT (SELECT cug_name FROM med_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
			$sql_billing2[1]	= " SELECT (SELECT cug_name FROM med_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
			$sql_service[1]		= " SELECT (SELECT cug_name FROM med_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
		} else {
			$sql_billing1[0] = "
			SELECT
				COALESCE((SELECT cug_name FROM idc_tb_customer JOIN idc_tb_customer_group USING (cug_code) WHERE cus_code = bill_ship_to),
				'Others') AS cug_name,";
			$sql_billing2[0] = "
			SELECT
				COALESCE((SELECT cug_name FROM idc_tb_customer JOIN idc_tb_customer_group USING (cug_code) WHERE cus_code = bill_ship_to),
				'Others') AS cug_name,";
			$sql_service[0] = "
			SELECT
				COALESCE((SELECT cug_name FROM idc_tb_customer JOIN idc_tb_customer_group USING (cug_code) WHERE cus_code = sv_cus_to),
				'Others') AS cug_name,";

			$sql_billing1[1] = "
			SELECT
				COALESCE((SELECT cug_name FROM med_tb_customer JOIN med_tb_customer_group USING (cug_code) WHERE cus_code = bill_ship_to),
				'Others') AS cug_name,";
			$sql_billing2[1] = "
			SELECT
				COALESCE((SELECT cug_name FROM med_tb_customer JOIN med_tb_customer_group USING (cug_code) WHERE cus_code = bill_ship_to),
				'Others') AS cug_name,";
			$sql_service[1] = "
			SELECT
				COALESCE((SELECT cug_name FROM med_tb_customer JOIN med_tb_customer_group USING (cug_code) WHERE cus_code = sv_cus_to),
				'Others') AS cug_name,";
		}
		
		if($_from_mon!='' && $_from_year!='') {
			$_from_date	= date('Y-n-d', mktime(0,0,0, $_from_mon, 1, $_from_year));
			$_to_date	= date('Y-n-d', mktime(0,0,0, $_from_mon+1, 1-1, $_from_year));
			$tmp_billing1[0][] = "bill_inv_date between date '$_from_date' AND '$_to_date'";
			$tmp_billing2[0][] = "bill_inv_date between date '$_from_date' AND '$_to_date'";
			$tmp_service[0][] = "sv_date between date '$_from_date' AND '$_to_date'";
			$tmp_billing1[1][] = "bill_inv_date between date '$_from_date' AND '$_to_date'";
			$tmp_billing2[1][] = "bill_inv_date between date '$_from_date' AND '$_to_date'";
			$tmp_service[1][] = "sv_date between date '$_from_date' AND '$_to_date'";
		}
		
		if($_marketing != "all") {
			$tmp_billing1[0][] = "cus_responsibility_to = $_marketing";
			$tmp_billing2[0][] = "cus_responsibility_to = $_marketing";
			$tmp_service[0][] = "sv_code is null";
			$tmp_billing1[1][] = "cus_responsibility_to = $_marketing";
			$tmp_billing2[1][] = "cus_responsibility_to = $_marketing";
			$tmp_service[1][] = "sv_code is null";
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
		
		if($_method != "all") {
			$tmp_billing1[0][] = "p.pay_method = '$_method'";
			$tmp_billing2[0][] = "p.pay_method = '$_method'";
			$tmp_service[0][] = "svpay_method = '$_method'";
			$tmp_billing1[1][] = "p.pay_method = '$_method'";
			$tmp_billing2[1][] = "p.pay_method = '$_method'";
			$tmp_service[1][] = "svpay_method = '$_method'";
		}
		
		if($_bank != "all") {
			$tmp_billing1[0][] = "p.pay_bank = '$_bank'";
			$tmp_billing2[0][] = "p.pay_bank = '$_bank'";
			$tmp_service[0][] = "svpay_bank = '$_bank'";
			$tmp_billing1[1][] = "p.pay_bank = '$_bank'";
			$tmp_billing2[1][] = "p.pay_bank = '$_bank'";
			$tmp_service[1][] = "svpay_bank = '$_bank'";
		}
		
		if ($some_date != "") {
			$tmp_billing1[0][] = "pay_date = DATE '$some_date'";
			$tmp_billing2[0][] = "pay_date = DATE '$some_date'";
			$tmp_service[0][] = "svpay_date = DATE '$some_date'";
			$tmp_billing1[1][] = "pay_date = DATE '$some_date'";
			$tmp_billing2[1][] = "pay_date = DATE '$some_date'";
			$tmp_service[1][] = "svpay_date = DATE '$some_date'";
		} else {
			$tmp_billing1[0][] = "pay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_billing2[0][] = "pay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_service[0][] = "svpay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_billing1[1][] = "pay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_billing2[1][] = "pay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
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
			$tmp_billing1[0][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_billing2[0][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_service[0][] = "sv_code is null";
			$tmp_billing1[1][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_billing2[1][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_service[1][] = "sv_code is null";
		}else if($_vat == 'vat-IP') {
			$tmp_billing1[0][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
			$tmp_billing2[0][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
			$tmp_service[0][] = "sv_code is null";
			$tmp_billing1[1][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
			$tmp_billing2[1][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
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
			} else if($searchType[$cboSearchType] == 2) {
				$tmp_service[0][] = "svpay_idx IS NULL";
				$tmp_service[1][] = "svpay_idx IS NULL";
			}
		}

	break;

	// IDC ==============================================================================================================
	case "IDC":

		$tmp_billing1[0][]	= "bill_ordered_by = 1 AND idc_isValidShowInvoice('idc', bill_code,'billing')";
		$tmp_billing2[0][]	= "turn_ordered_by = 1 AND idc_isValidShowInvoice('idc', bill_code,'billing')";

		if ($_cug_code != 'all') {
			$tmp_billing1[0][]	= "bill_ship_to IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_billing2[0][]	= "bill_ship_to IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_service[0][]	= "sv_cus_to IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
		
			$sql_billing1	= " SELECT (SELECT cug_name FROM idc_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
			$sql_billing2	= " SELECT (SELECT cug_name FROM idc_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
			$sql_service	= " SELECT (SELECT cug_name FROM idc_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
		} else {
			$sql_billing1[0] = "
			SELECT
				COALESCE((SELECT cug_name FROM idc_tb_customer JOIN idc_tb_customer_group USING (cug_code) WHERE cus_code = bill_ship_to),
				'Others') AS cug_name,";
			$sql_billing2[0] = "
			SELECT
				COALESCE((SELECT cug_name FROM idc_tb_customer JOIN idc_tb_customer_group USING (cug_code) WHERE cus_code = bill_ship_to),
				'Others') AS cug_name,";
			$sql_service[0] = "
			SELECT
				COALESCE((SELECT cug_name FROM idc_tb_customer JOIN idc_tb_customer_group USING (cug_code) WHERE cus_code = sv_cus_to),
				'Others') AS cug_name,";
		}
		
		if($_from_mon!='' && $_from_year!='') {
			$_from_date	= date('Y-n-d', mktime(0,0,0, $_from_mon, 1, $_from_year));
			$_to_date	= date('Y-n-d', mktime(0,0,0, $_from_mon+1, 1-1, $_from_year));
			$tmp_billing1[0][] = "bill_inv_date between date '$_from_date' AND '$_to_date'";
			$tmp_billing2[0][] = "bill_inv_date between date '$_from_date' AND '$_to_date'";
			$tmp_service[0][] = "sv_date between date '$_from_date' AND '$_to_date'";
		}
		
		if($_marketing != "all") {
			$tmp_billing1[0][] = "cus_responsibility_to = $_marketing";
			$tmp_billing2[0][] = "cus_responsibility_to = $_marketing";
			$tmp_service[0][] = "sv_code is null";
		}
		
		if($_filter_doc == 'I') {
			$tmp_billing1[0][] = "pay_paid > 0 AND pay_note NOT IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
			$tmp_billing2[0][] = "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
		} else if($_filter_doc == 'R') {
			$tmp_billing1[0][] = "pay_paid < 0 AND pay_note NOT IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
			$tmp_billing2[0][] = "pay_idx IS NULL";
			$tmp_service[0][] = "sv_code is null";
		} else if($_filter_doc == 'CT') {
			$tmp_billing1[0][] = "pay_idx IS NULL";
			$tmp_billing2[0][] = "pay_note IN ('CROSS_TRANSFER+')";
			$tmp_service[0][] = "sv_code is null";
		} else {
			$tmp_billing1[0][] = "pay_note NOT IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
			$tmp_billing2[0][] = "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
		}
		
		if($_dept != 'all' && $_dept != 'S') {
			$tmp_billing1[0][] = "p.pay_dept = '$_dept'";
			$tmp_billing2[0][] = "p.pay_dept = '$_dept'";
			$tmp_service[0][] = "sv_code is null";
		} else if($_dept == 'S') {
			$tmp_billing1[0][] = "bill_code is null";
			$tmp_billing2[0][] = "bill_code is null";
		}
		
		if($_method != "all") {
			$tmp_billing1[0][] = "p.pay_method = '$_method'";
			$tmp_billing2[0][] = "p.pay_method = '$_method'";
			$tmp_service[0][] = "svpay_method = '$_method'";
		}
		
		if($_bank != "all") {
			$tmp_billing1[0][] = "p.pay_bank = '$_bank'";
			$tmp_billing2[0][] = "p.pay_bank = '$_bank'";
			$tmp_service[0][] = "svpay_bank = '$_bank'";
		}
		
		if ($some_date != "") {
			$tmp_billing1[0][] = "pay_date = DATE '$some_date'";
			$tmp_billing2[0][] = "pay_date = DATE '$some_date'";
			$tmp_service[0][] = "svpay_date = DATE '$some_date'";
		} else {
			$tmp_billing1[0][] = "pay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_billing2[0][] = "pay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_service[0][] = "svpay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_billing1[0][] = "bill_vat > 0";
			$tmp_billing2[0][] = "bill_vat > 0";
			$tmp_service[0][] = "sv_code is null";
		} else if($_vat == 'vat-IO') {
			$tmp_billing1[0][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_billing2[0][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_service[0][] = "sv_code is null";
		}else if($_vat == 'vat-IP') {
			$tmp_billing1[0][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
			$tmp_billing2[0][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
			$tmp_service[0][] = "sv_code is null";
		} else if ($_vat == 'non') {
			$tmp_billing1[0][] = "bill_vat = 0";
			$tmp_billing2[0][] = "bill_vat = 0";
		}
		
		if($cboSearchType != '' && $txtSearch != '') {
			$searchType = array("byPayment"=>1, "byDeduction"=>2);
			$tmp_billing1[0][] = "idc_isPayDescTrue({$searchType[$cboSearchType]}, pay_idx, '%$txtSearch%') = true";
			$tmp_billing2[0][] = "idc_isPayDescTrue({$searchType[$cboSearchType]}, pay_idx, '%$txtSearch%') = true";
			if($searchType[$cboSearchType] == 1) {
				$tmp_service[0][] = "idc_isPayDescTrue(3, svpay_idx, '%$txtSearch%') = true";
			} else if($searchType[$cboSearchType] == 1) {
				$tmp_service[0][] = "svpay_idx IS NULL";
			} else if($searchType[$cboSearchType] == 2) {
				$tmp_service[0][] = "svpay_idx IS NULL";
			}
		}

	break;

	// MED==============================================================================================================
	case "MED":

		if ($_cug_code != 'all') {
			$tmp_billing1[1][]	= "bill_ship_to IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_billing2[1][]	= "bill_ship_to IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_service[1][]	= "sv_cus_to IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
		
			$sql_billing1	= " SELECT (SELECT cug_name FROM idc_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
			$sql_billing2	= " SELECT (SELECT cug_name FROM idc_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
			$sql_service	= " SELECT (SELECT cug_name FROM idc_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
		} else {
			$sql_billing1[1] = "
			SELECT
				COALESCE((SELECT cug_name FROM idc_tb_customer JOIN idc_tb_customer_group USING (cug_code) WHERE cus_code = bill_ship_to),
				'Others') AS cug_name,";
			$sql_billing2[1] = "
			SELECT
				COALESCE((SELECT cug_name FROM idc_tb_customer JOIN idc_tb_customer_group USING (cug_code) WHERE cus_code = bill_ship_to),
				'Others') AS cug_name,";
			$sql_service[1] = "
			SELECT
				COALESCE((SELECT cug_name FROM idc_tb_customer JOIN idc_tb_customer_group USING (cug_code) WHERE cus_code = sv_cus_to),
				'Others') AS cug_name,";
		}
		
		if($_from_mon!='' && $_from_year!='') {
			$_from_date	= date('Y-n-d', mktime(0,0,0, $_from_mon, 1, $_from_year));
			$_to_date	= date('Y-n-d', mktime(0,0,0, $_from_mon+1, 1-1, $_from_year));
			$tmp_billing1[1][] = "bill_inv_date between date '$_from_date' AND '$_to_date'";
			$tmp_billing2[1][] = "bill_inv_date between date '$_from_date' AND '$_to_date'";
			$tmp_service[1][] = "sv_date between date '$_from_date' AND '$_to_date'";
		}
		
		if($_marketing != "all") {
			$tmp_billing1[1][] = "cus_responsibility_to = $_marketing";
			$tmp_billing2[1][] = "cus_responsibility_to = $_marketing";
			$tmp_service[1][] = "sv_code is null";
		}
		
		if($_filter_doc == 'I') {
			$tmp_billing1[1][] = "pay_paid > 0 AND pay_note NOT IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
			$tmp_billing2[1][] = "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
		} else if($_filter_doc == 'R') {
			$tmp_billing1[1][] = "pay_paid < 0 AND pay_note NOT IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
			$tmp_billing2[1][] = "pay_idx IS NULL";
			$tmp_service[1][] = "sv_code is null";
		} else if($_filter_doc == 'CT') {
			$tmp_billing1[1][] = "pay_idx IS NULL";
			$tmp_billing2[1][] = "pay_note IN ('CROSS_TRANSFER+')";
			$tmp_service[1][] = "sv_code is null";
		} else {
			$tmp_billing1[1][] = "pay_note NOT IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
			$tmp_billing2[1][] = "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
		}
		
		if($_dept != 'all' && $_dept != 'S') {
			$tmp_billing1[1][] = "p.pay_dept = '$_dept'";
			$tmp_billing2[1][] = "p.pay_dept = '$_dept'";
			$tmp_service[1][] = "sv_code is null";
		} else if($_dept == 'S') {
			$tmp_billing1[1][] = "bill_code is null";
			$tmp_billing2[1][] = "bill_code is null";
		}
		
		if($_method != "all") {
			$tmp_billing1[1][] = "p.pay_method = '$_method'";
			$tmp_billing2[1][] = "p.pay_method = '$_method'";
			$tmp_service[1][] = "svpay_method = '$_method'";
		}
		
		if($_bank != "all") {
			$tmp_billing1[1][] = "p.pay_bank = '$_bank'";
			$tmp_billing2[1][] = "p.pay_bank = '$_bank'";
			$tmp_service[1][] = "svpay_bank = '$_bank'";
		}
		
		if ($some_date != "") {
			$tmp_billing1[1][] = "pay_date = DATE '$some_date'";
			$tmp_billing2[1][] = "pay_date = DATE '$some_date'";
			$tmp_service[1][] = "svpay_date = DATE '$some_date'";
		} else {
			$tmp_billing1[1][] = "pay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_billing2[1][] = "pay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_service[1][] = "svpay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_billing1[1][] = "bill_vat > 0";
			$tmp_billing2[1][] = "bill_vat > 0";
			$tmp_service[1][] = "sv_code is null";
		} else if($_vat == 'vat-IO') {
			$tmp_billing1[1][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_billing2[1][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_service[1][] = "sv_code is null";
		}else if($_vat == 'vat-IP') {
			$tmp_billing1[1][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
			$tmp_billing2[1][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
			$tmp_service[1][] = "sv_code is null";
		} else if ($_vat == 'non') {
			$tmp_billing1[1][] = "bill_vat = 0";
			$tmp_billing2[1][] = "bill_vat = 0";
		}
		
		if($cboSearchType != '' && $txtSearch != '') {
			$searchType = array("byPayment"=>1, "byDeduction"=>2);
			$tmp_billing1[1][] = "idc_isPayDescTrue({$searchType[$cboSearchType]}, pay_idx, '%$txtSearch%') = true";
			$tmp_billing2[1][] = "idc_isPayDescTrue({$searchType[$cboSearchType]}, pay_idx, '%$txtSearch%') = true";
			if($searchType[$cboSearchType] == 1) {
				$tmp_service[1][] = "idc_isPayDescTrue(3, svpay_idx, '%$txtSearch%') = true";
			} else if($searchType[$cboSearchType] == 1) {
				$tmp_service[1][] = "svpay_idx IS NULL";
			} else if($searchType[$cboSearchType] == 2) {
				$tmp_service[1][] = "svpay_idx IS NULL";
			}
		}

	break;

	// MEP ==============================================================================================================
	case "MEP":

		$tmp_billing1[0][]	= "bill_ordered_by = 2 AND idc_isValidShowInvoice('idc', bill_code,'billing')";
		$tmp_billing2[0][]	= "turn_ordered_by = 2 AND idc_isValidShowInvoice('idc', bill_code,'billing')";

		if ($_cug_code != 'all') {
			$tmp_billing1[0][]	= "bill_ship_to IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_billing2[0][]	= "bill_ship_to IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_service[0][]	= "sv_cus_to IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
		
			$sql_billing1	= " SELECT (SELECT cug_name FROM idc_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
			$sql_billing2	= " SELECT (SELECT cug_name FROM idc_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
			$sql_service	= " SELECT (SELECT cug_name FROM idc_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
		} else {
			$sql_billing1[0] = "
			SELECT
				COALESCE((SELECT cug_name FROM idc_tb_customer JOIN idc_tb_customer_group USING (cug_code) WHERE cus_code = bill_ship_to),
				'Others') AS cug_name,";
			$sql_billing2[0] = "
			SELECT
				COALESCE((SELECT cug_name FROM idc_tb_customer JOIN idc_tb_customer_group USING (cug_code) WHERE cus_code = bill_ship_to),
				'Others') AS cug_name,";
			$sql_service[0] = "
			SELECT
				COALESCE((SELECT cug_name FROM idc_tb_customer JOIN idc_tb_customer_group USING (cug_code) WHERE cus_code = sv_cus_to),
				'Others') AS cug_name,";
		}
		
		if($_from_mon!='' && $_from_year!='') {
			$_from_date	= date('Y-n-d', mktime(0,0,0, $_from_mon, 1, $_from_year));
			$_to_date	= date('Y-n-d', mktime(0,0,0, $_from_mon+1, 1-1, $_from_year));
			$tmp_billing1[0][] = "bill_inv_date between date '$_from_date' AND '$_to_date'";
			$tmp_billing2[0][] = "bill_inv_date between date '$_from_date' AND '$_to_date'";
			$tmp_service[0][] = "sv_date between date '$_from_date' AND '$_to_date'";
		}
		
		if($_marketing != "all") {
			$tmp_billing1[0][] = "cus_responsibility_to = $_marketing";
			$tmp_billing2[0][] = "cus_responsibility_to = $_marketing";
			$tmp_service[0][] = "sv_code is null";
		}
		
		if($_filter_doc == 'I') {
			$tmp_billing1[0][] = "pay_paid > 0 AND pay_note NOT IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
			$tmp_billing2[0][] = "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
		} else if($_filter_doc == 'R') {
			$tmp_billing1[0][] = "pay_paid < 0 AND pay_note NOT IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
			$tmp_billing2[0][] = "pay_idx IS NULL";
			$tmp_service[0][] = "sv_code is null";
		} else if($_filter_doc == 'CT') {
			$tmp_billing1[0][] = "pay_idx IS NULL";
			$tmp_billing2[0][] = "pay_note IN ('CROSS_TRANSFER+')";
			$tmp_service[0][] = "sv_code is null";
		} else {
			$tmp_billing1[0][] = "pay_note NOT IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
			$tmp_billing2[0][] = "pay_note IN ('CROSS_TRANSFER+', 'CROSS_TRANSFER-')";
		}
		
		if($_dept != 'all' && $_dept != 'S') {
			$tmp_billing1[0][] = "p.pay_dept = '$_dept'";
			$tmp_billing2[0][] = "p.pay_dept = '$_dept'";
			$tmp_service[0][] = "sv_code is null";
		} else if($_dept == 'S') {
			$tmp_billing1[0][] = "bill_code is null";
			$tmp_billing2[0][] = "bill_code is null";
		}
		
		if($_method != "all") {
			$tmp_billing1[0][] = "p.pay_method = '$_method'";
			$tmp_billing2[0][] = "p.pay_method = '$_method'";
			$tmp_service[0][] = "svpay_method = '$_method'";
		}
		
		if($_bank != "all") {
			$tmp_billing1[0][] = "p.pay_bank = '$_bank'";
			$tmp_billing2[0][] = "p.pay_bank = '$_bank'";
			$tmp_service[0][] = "svpay_bank = '$_bank'";
		}
		
		if ($some_date != "") {
			$tmp_billing1[0][] = "pay_date = DATE '$some_date'";
			$tmp_billing2[0][] = "pay_date = DATE '$some_date'";
			$tmp_service[0][] = "svpay_date = DATE '$some_date'";
		} else {
			$tmp_billing1[0][] = "pay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_billing2[0][] = "pay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_service[0][] = "svpay_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_billing1[0][] = "bill_vat > 0";
			$tmp_billing2[0][] = "bill_vat > 0";
			$tmp_service[0][] = "sv_code is null";
		} else if($_vat == 'vat-IO') {
			$tmp_billing1[0][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_billing2[0][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_service[0][] = "sv_code is null";
		}else if($_vat == 'vat-IP') {
			$tmp_billing1[0][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
			$tmp_billing2[0][] = "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
			$tmp_service[0][] = "sv_code is null";
		} else if ($_vat == 'non') {
			$tmp_billing1[0][] = "bill_vat = 0";
			$tmp_billing2[0][] = "bill_vat = 0";
		}
		
		if($cboSearchType != '' && $txtSearch != '') {
			$searchType = array("byPayment"=>1, "byDeduction"=>2);
			$tmp_billing1[0][] = "idc_isPayDescTrue({$searchType[$cboSearchType]}, pay_idx, '%$txtSearch%') = true";
			$tmp_billing2[0][] = "idc_isPayDescTrue({$searchType[$cboSearchType]}, pay_idx, '%$txtSearch%') = true";
			if($searchType[$cboSearchType] == 1) {
				$tmp_service[0][] = "idc_isPayDescTrue(3, svpay_idx, '%$txtSearch%') = true";
			} else if($searchType[$cboSearchType] == 1) {
				$tmp_service[0][] = "svpay_idx IS NULL";
			} else if($searchType[$cboSearchType] == 2) {
				$tmp_service[0][] = "svpay_idx IS NULL";
			}
		}

	break;

}

$strWhere[0]	= implode(" AND ", $tmp_billing1[0]);
$strWhere[1]	= implode(" AND ", $tmp_billing2[0]);
$strWhere[2]	= implode(" AND ", $tmp_service[0]);
$strWhere[3]	= implode(" AND ", $tmp_billing1[1]);
$strWhere[4]	= implode(" AND ", $tmp_billing2[1]);
$strWhere[5]	= implode(" AND ", $tmp_service[1]);
?>