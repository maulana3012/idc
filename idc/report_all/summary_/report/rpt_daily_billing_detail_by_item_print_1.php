<?php
//SET WHERE PARAMETER
$tmp_bill	= array();
$tmp_turn	= array();
$tmp_dr		= array();
$strWhere	= array();

switch (ZKP_URL) {
	// ALL ==============================================================================================================
	case "ALL":

		if($_order_by == "1") {				// INDOCORE
			$tmp_bill[0][]	= "bill_ordered_by = 1";
			$tmp_turn[0][]	= "turn_ordered_by = 1";
			$tmp_dr[0][]	= "dr_ordered_by = 1";
			$tmp_bill[1][]	= "bill_code is null";
			$tmp_turn[1][]	= "turn_code is null";
			$tmp_dr[1][]	= "dr_code is null";
		} else if($_order_by == "2") {		// MEDISINDO
			$tmp_bill[0][]	= "bill_code is null";
			$tmp_turn[0][]	= "turn_code is null";
			$tmp_dr[0][]	= "dr_code is null";
		} else if($_order_by == "3") {		// MEDIKUS
			$tmp_bill[0][]	= "bill_ordered_by = 2";
			$tmp_turn[0][]	= "turn_ordered_by = 2";
			$tmp_dr[0][]	= "dr_ordered_by = 2";
			$tmp_bill[1][]	= "bill_code is null";
			$tmp_turn[1][]	= "turn_code is null";
			$tmp_dr[1][]	= "dr_code is null";
		}

		if ($_last_category != 0) {
			$catList = executeSP("idc_getSubCategory", $_last_category);
			$tmp_bill[0][] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_turn[0][] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_dr[0][]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$catList = executeSP("med_getSubCategory", $_last_category);
			$tmp_bill[1][] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_turn[1][] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_dr[1][]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
		}

		if ($_cug_code != 'all') {
			$tmp_bill[0][]	= "bill_ship_to IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_turn[0][]	= "turn_ship_to IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_dr[0][]	= "dr_ship_to IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_bill[1][]	= "bill_ship_to IN (SELECT cus_code FROM med_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_turn[1][]	= "turn_ship_to IN (SELECT cus_code FROM med_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_dr[1][]	= "dr_ship_to IN (SELECT cus_code FROM med_tb_customer WHERE cug_code = '$_cug_code')";
		} else {
			$sql_bill[0] = "
			SELECT
				COALESCE((SELECT cug_name FROM idc_tb_customer JOIN idc_tb_customer_group USING (cug_code) WHERE cus_code = bill_ship_to),
				'Others') AS cug_name,";
			$sql_return[0] = "
			SELECT
				COALESCE((SELECT cug_name FROM idc_tb_customer JOIN idc_tb_customer_group USING (cug_code) WHERE cus_code = turn_ship_to),
				'Others') AS cug_name,";
			$sql_dr[0] = "
			SELECT
				COALESCE((SELECT cug_name FROM idc_tb_customer JOIN idc_tb_customer_group USING (cug_code) WHERE cus_code = dr_ship_to),
				'Others') AS cug_name,";

			$sql_bill[1] = "
			SELECT
				COALESCE((SELECT cug_name FROM med_tb_customer JOIN med_tb_customer_group USING (cug_code) WHERE cus_code = bill_ship_to),
				'Others') AS cug_name,";
			$sql_return[1] = "
			SELECT
				COALESCE((SELECT cug_name FROM med_tb_customer JOIN med_tb_customer_group USING (cug_code) WHERE cus_code = turn_ship_to),
				'Others') AS cug_name,";
			$sql_dr[1] = "
			SELECT
				COALESCE((SELECT cug_name FROM med_tb_customer JOIN med_tb_customer_group USING (cug_code) WHERE cus_code = dr_ship_to),
				'Others') AS cug_name,";
		}

		if($_filter_doc == 'I'){
			$tmp_turn[0][]	= "turn_code = ''";
			$tmp_dr[0][]	= "dr_code = ''";
			$tmp_turn[1][]	= "turn_code = ''";
			$tmp_dr[1][]	= "dr_code = ''";
		} else if($_filter_doc == 'R') {
			$tmp_bill[0][]	= "bill_code = ''";
			$tmp_dr[0][]	= "dr_code = ''";
			$tmp_bill[1][]	= "bill_code = ''";
			$tmp_dr[1][]	= "dr_code = ''";
		} else if($_filter_doc == 'DR') {
			$tmp_bill[0][]	= "bill_code = ''";
			$tmp_turn[0][]	= "turn_code = ''";
			$tmp_bill[1][]	= "bill_code = ''";
			$tmp_turn[1][]	= "turn_code = ''";
		}

		if($_dept != 'all') {
			$tmp_bill[0][]	= "bill_dept = '$_dept'";
			$tmp_turn[0][]	= "turn_dept = '$_dept'";
			$tmp_dr[0][]	= "dr_dept = '$_dept'";
			$tmp_bill[1][]	= "bill_dept = '$_dept'";
			$tmp_turn[1][]	= "turn_dept = '$_dept'";
			$tmp_dr[1][]	= "dr_dept = '$_dept'";
		}

		if($_vat == 'vat') {
			$tmp_bill[0][]	= "bill_vat > 0";
			$tmp_turn[0][]	= "turn_vat > 0";  
			$tmp_dr[0][]	= "dr_type_item = 1";
			$tmp_bill[1][]	= "bill_vat > 0";
			$tmp_turn[1][]	= "turn_vat > 0";  
			$tmp_dr[1][]	= "dr_type_item = 1";
		} else if($_vat == 'vat-IO') {
			$tmp_bill[0][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_turn[0][]	= "turn_vat > 0";  
			$tmp_dr[0][]	= "dr_type_item = 1";
			$tmp_bill[1][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_turn[1][]	= "turn_vat > 0";  
			$tmp_dr[1][]	= "dr_type_item = 1";
		}else if($_vat == 'vat-IP') {
			$tmp_bill[0][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
			$tmp_turn[0][]	= "turn_code = ''";  
			$tmp_dr[0][]	= "dr_code is null";
			$tmp_bill[1][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
			$tmp_turn[1][]	= "turn_code = ''";  
			$tmp_dr[1][]	= "dr_code is null";
		} else if ($_vat == 'non') {
			$tmp_bill[0][]	= "bill_vat = 0";
			$tmp_turn[0][]	= "turn_vat = 0";
			$tmp_dr[0][]	= "dr_type_item = 2";
			$tmp_bill[1][]	= "bill_vat = 0";
			$tmp_turn[1][]	= "turn_vat = 0";
			$tmp_dr[1][]	= "dr_type_item = 2";
		}

		if ($_paper == '0') {
			$tmp_bill[0][]	= "bill_type_invoice = '0'";
			$tmp_turn[0][]	= "turn_paper = 0";
			$tmp_bill[1][]	= "bill_type_invoice = '0'";
			$tmp_turn[1][]	= "turn_paper = 0";
		} else if ($_paper == '1') {
			$tmp_bill[0][]	= "bill_type_invoice = '1'";
			$tmp_turn[0][]	= "turn_paper = 1";
			$tmp_dr[0][]	= "dr_code is null";
			$tmp_bill[1][]	= "bill_type_invoice = '1'";
			$tmp_turn[1][]	= "turn_paper = 1";
			$tmp_dr[1][]	= "dr_code is null";
		} else if ($_paper == 'A') {
			$tmp_bill[0][]	= "bill_paper_format = 'A'";
			$tmp_turn[0][]	= "turn_paper = 0";
			$tmp_dr[0][]	= "dr_code is null";
			$tmp_bill[1][]	= "bill_paper_format = 'A'";
			$tmp_turn[1][]	= "turn_paper = 0";
			$tmp_dr[1][]	= "dr_code is null";
		} else if ($_paper == 'B') {
			$tmp_bill[0][]	= "bill_paper_format = 'B'";
			$tmp_turn[0][]	= "turn_paper = 1";
			$tmp_dr[0][]	= "dr_code is null";
			$tmp_bill[1][]	= "bill_paper_format = 'B'";
			$tmp_turn[1][]	= "turn_paper = 1";
			$tmp_dr[1][]	= "dr_code is null";
		}

		if($_marketing != "all") {
			$tmp_bill[0][]	= "bill_responsible_by = $_marketing";
			$tmp_turn[0][]	= "turn_responsible_by = $_marketing";
			$tmp_dr[0][]	= "cus_responsibility_to = $_marketing";
			$tmp_bill[1][]	= "bill_responsible_by = $_marketing";
			$tmp_turn[1][]	= "turn_responsible_by = $_marketing";
			$tmp_dr[1][]	= "cus_responsibility_to = $_marketing";
		}


		if($cboSearchType != '' && $txtSearch != '') {
			$type = array("byCity"=>"cus_city");
			$tmp_bill[0][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmp_turn[0][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmp_dr[0][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmp_bill[1][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmp_turn[1][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmp_dr[1][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$get[0][] = "$cboSearchType=$txtSearch";
		}

		if ($some_date != "") {
			$tmp_bill[0][]	= "bill_inv_date = DATE '$some_date'";
			$tmp_turn[0][]	= "turn_return_date = DATE '$some_date'";
			$tmp_dr[0][]	= "dr_issued_date = DATE '$some_date'";
			$tmp_bill[1][]	= "bill_inv_date = DATE '$some_date'";
			$tmp_turn[1][]	= "turn_return_date = DATE '$some_date'";
			$tmp_dr[1][]	= "dr_issued_date = DATE '$some_date'";
		} else {
			$tmp_bill[0][]	= "bill_inv_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_turn[0][]	= "turn_return_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_dr[0][]	= "dr_issued_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_bill[1][]	= "bill_inv_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_turn[1][]	= "turn_return_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_dr[1][]	= "dr_issued_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
		}

	break;

	// IDC ==============================================================================================================
	case "IDC":

		$tmp_bill[0][]	= "bill_ordered_by = 1 AND idc_isValidShowInvoice('idc', bill_code,'billing')";
		$tmp_turn[0][]	= "turn_ordered_by = 1 AND idc_isValidShowInvoice('idc', turn_code,'billing_return')";
		$tmp_dr[0][]	= "dr_ordered_by = 1 AND idc_isValidShowInvoice('idc', dr_code,'dr')";

		if ($_cug_code != 'all') {
			$tmp_bill[0][]	= "bill_ship_to IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_turn[0][]	= "turn_ship_to IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_dr[0][]	= "dr_ship_to IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
		}

		if($_filter_doc == 'I'){
			$tmp_turn[0][]	= "turn_code = ''";
			$tmp_dr[0][]	= "dr_code = ''";
		} else if($_filter_doc == 'R') {
			$tmp_bill[0][]	= "bill_code = ''";
			$tmp_dr[0][]	= "dr_code = ''";
		} else if($_filter_doc == 'DR') {
			$tmp_bill[0][]	= "bill_code = ''";
			$tmp_turn[0][]	= "turn_code = ''";
		}

		if($_dept != 'all') {
			$tmp_bill[0][]	= "bill_dept = '$_dept'";
			$tmp_turn[0][]	= "turn_dept = '$_dept'";
			$tmp_dr[0][]	= "dr_dept = '$_dept'";
		}

		if($_vat == 'vat') {
			$tmp_bill[0][]	= "bill_vat > 0";
			$tmp_turn[0][]	= "turn_vat > 0";  
			$tmp_dr[0][]	= "dr_type_item = 1";
		} else if($_vat == 'vat-IO') {
			$tmp_bill[0][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_turn[0][]	= "turn_vat > 0";  
			$tmp_dr[0][]	= "dr_type_item = 1";
		}else if($_vat == 'vat-IP') {
			$tmp_bill[0][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
			$tmp_turn[0][]	= "turn_code = ''";  
			$tmp_dr[0][]	= "dr_code is null";
		} else if ($_vat == 'non') {
			$tmp_bill[0][]	= "bill_vat = 0";
			$tmp_turn[0][]	= "turn_vat = 0";
			$tmp_dr[0][]	= "dr_type_item = 2";
		}

		if ($_paper == '0') {
			$tmp_bill[0][]	= "bill_type_invoice = '0'";
			$tmp_turn[0][]	= "turn_paper = 0";
		} else if ($_paper == '1') {
			$tmp_bill[0][]	= "bill_type_invoice = '1'";
			$tmp_turn[0][]	= "turn_paper = 1";
			$tmp_dr[0][]	= "dr_code is null";
		} else if ($_paper == 'A') {
			$tmp_bill[0][]	= "bill_paper_format = 'A'";
			$tmp_turn[0][]	= "turn_paper = 0";
			$tmp_dr[0][]	= "dr_code is null";
		} else if ($_paper == 'B') {
			$tmp_bill[0][]	= "bill_paper_format = 'B'";
			$tmp_turn[0][]	= "turn_paper = 1";
			$tmp_dr[0][]	= "dr_code is null";
		}

		if($_marketing != "all") {
			$tmp_bill[0][]	= "bill_responsible_by = $_marketing";
			$tmp_turn[0][]	= "turn_responsible_by = $_marketing";
			$tmp_dr[0][]	= "dr_code is null = $_marketing";
		}


		if($cboSearchType != '' && $txtSearch != '') {
			$type = array("byCity"=>"cus_city");
			$tmp_bill[0][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmp_turn[0][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmp_dr[0][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$get[0][] = "$cboSearchType=$txtSearch";
		}

		if ($some_date != "") {
			$tmp_bill[0][]	= "bill_inv_date = DATE '$some_date'";
			$tmp_turn[0][]	= "turn_return_date = DATE '$some_date'";
			$tmp_dr[0][]	= "dr_issued_date = DATE '$some_date'";
		} else {
			$tmp_bill[0][]	= "bill_inv_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_turn[0][]	= "turn_return_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_dr[0][]	= "dr_issued_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
		}

	break;
	
	// MED ==============================================================================================================
	case "MED":

		if ($_cug_code != 'all') {
			$tmp_bill[1][]	= "bill_ship_to IN (SELECT cus_code FROM med_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_turn[1][]	= "turn_ship_to IN (SELECT cus_code FROM med_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_dr[1][]	= "dr_ship_to IN (SELECT cus_code FROM med_tb_customer WHERE cug_code = '$_cug_code')";
		}

		if($_filter_doc == 'I'){
			$tmp_turn[1][]	= "turn_code = ''";
			$tmp_dr[1][]	= "dr_code = ''";
		} else if($_filter_doc == 'R') {
			$tmp_bill[1][]	= "bill_code = ''";
			$tmp_dr[1][]	= "dr_code = ''";
		} else if($_filter_doc == 'DR') {
			$tmp_bill[1][]	= "bill_code = ''";
			$tmp_turn[1][]	= "turn_code = ''";
		}

		if($_dept != 'all') {
			$tmp_bill[1][]	= "bill_dept = '$_dept'";
			$tmp_turn[1][]	= "turn_dept = '$_dept'";
			$tmp_dr[1][]	= "dr_dept = '$_dept'";
		}

		if($_vat == 'vat') {
			$tmp_bill[1][]	= "bill_vat > 0";
			$tmp_turn[1][]	= "turn_vat > 0";  
			$tmp_dr[1][]	= "dr_type_item = 1";
		} else if($_vat == 'vat-IO') {
			$tmp_bill[1][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_turn[1][]	= "turn_vat > 0";  
			$tmp_dr[1][]	= "dr_type_item = 1";
		}else if($_vat == 'vat-IP') {
			$tmp_bill[1][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
			$tmp_turn[1][]	= "turn_code = ''";  
			$tmp_dr[1][]	= "dr_code is null";
		} else if ($_vat == 'non') {
			$tmp_bill[1][]	= "bill_vat = 0";
			$tmp_turn[1][]	= "turn_vat = 0";
			$tmp_dr[1][]	= "dr_type_item = 2";
		}

		if ($_paper == '0') {
			$tmp_bill[1][]	= "bill_type_invoice = '0'";
			$tmp_turn[1][]	= "turn_paper = 0";
		} else if ($_paper == '1') {
			$tmp_bill[1][]	= "bill_type_invoice = '1'";
			$tmp_turn[1][]	= "turn_paper = 1";
			$tmp_dr[1][]	= "dr_code is null";
		} else if ($_paper == 'A') {
			$tmp_bill[1][]	= "bill_paper_format = 'A'";
			$tmp_turn[1][]	= "turn_paper = 0";
			$tmp_dr[1][]	= "dr_code is null";
		} else if ($_paper == 'B') {
			$tmp_bill[1][]	= "bill_paper_format = 'B'";
			$tmp_turn[1][]	= "turn_paper = 1";
			$tmp_dr[1][]	= "dr_code is null";
		}

		if($_marketing != "all") {
			$tmp_bill[1][]	= "bill_responsible_by = $_marketing";
			$tmp_turn[1][]	= "turn_responsible_by = $_marketing";
			$tmp_dr[1][]	= "dr_code is null = $_marketing";
		}


		if($cboSearchType != '' && $txtSearch != '') {
			$type = array("byCity"=>"cus_city");
			$tmp_bill[1][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmp_turn[1][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmp_dr[1][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$get[1][] = "$cboSearchType=$txtSearch";
		}

		if ($some_date != "") {
			$tmp_bill[1][]	= "bill_inv_date = DATE '$some_date'";
			$tmp_turn[1][]	= "turn_return_date = DATE '$some_date'";
			$tmp_dr[1][]	= "dr_issued_date = DATE '$some_date'";
		} else {
			$tmp_bill[1][]	= "bill_inv_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_turn[1][]	= "turn_return_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_dr[1][]	= "dr_issued_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
		}

	break;

	// MEP ==============================================================================================================
	case "MEP":

		$tmp_bill[0][]	= "bill_ordered_by = 2 AND idc_isValidShowInvoice('idc', bill_code,'billing')";
		$tmp_turn[0][]	= "turn_ordered_by = 2 AND idc_isValidShowInvoice('idc', turn_code,'billing_return')";
		$tmp_dr[0][]	= "dr_ordered_by = 2 AND idc_isValidShowInvoice('idc', dr_code,'dr')";

		if ($_cug_code != 'all') {
			$tmp_bill[0][]	= "bill_ship_to IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_turn[0][]	= "turn_ship_to IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_dr[0][]	= "dr_ship_to IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
			$sql_bill[0] 	= " SELECT (SELECT cug_name FROM idc_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
			$sql_return[0]	= " SELECT (SELECT cug_name FROM idc_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
			$sql_dr[0]		= " SELECT (SELECT cug_name FROM idc_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
		} else {
			$sql_bill[0] = "
			SELECT
				COALESCE((SELECT cug_name FROM idc_tb_customer JOIN idc_tb_customer_group USING (cug_code) WHERE cus_code = bill_ship_to),
				'Others') AS cug_name,";
			$sql_return[0] = "
			SELECT
				COALESCE((SELECT cug_name FROM idc_tb_customer JOIN idc_tb_customer_group USING (cug_code) WHERE cus_code = turn_ship_to),
				'Others') AS cug_name,";
			$sql_dr[0] = "
			SELECT
				COALESCE((SELECT cug_name FROM idc_tb_customer JOIN idc_tb_customer_group USING (cug_code) WHERE cus_code = dr_ship_to),
				'Others') AS cug_name,";
		}

		if($_filter_doc == 'I'){
			$tmp_turn[0][]	= "turn_code = ''";
			$tmp_dr[0][]	= "dr_code = ''";
		} else if($_filter_doc == 'R') {
			$tmp_bill[0][]	= "bill_code = ''";
			$tmp_dr[0][]	= "dr_code = ''";
		} else if($_filter_doc == 'DR') {
			$tmp_bill[0][]	= "bill_code = ''";
			$tmp_turn[0][]	= "turn_code = ''";
		}

		if($_dept != 'all') {
			$tmp_bill[0][]	= "bill_dept = '$_dept'";
			$tmp_turn[0][]	= "turn_dept = '$_dept'";
			$tmp_dr[0][]	= "dr_dept = '$_dept'";
		}

		if($_vat == 'vat') {
			$tmp_bill[0][]	= "bill_vat > 0";
			$tmp_turn[0][]	= "turn_vat > 0";  
			$tmp_dr[0][]	= "dr_type_item = 1";
		} else if($_vat == 'vat-IO') {
			$tmp_bill[0][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
			$tmp_turn[0][]	= "turn_vat > 0";  
			$tmp_dr[0][]	= "dr_type_item = 1";
		}else if($_vat == 'vat-IP') {
			$tmp_bill[0][]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
			$tmp_turn[0][]	= "turn_code = ''";  
			$tmp_dr[0][]	= "dr_code is null";
		} else if ($_vat == 'non') {
			$tmp_bill[0][]	= "bill_vat = 0";
			$tmp_turn[0][]	= "turn_vat = 0";
			$tmp_dr[0][]	= "dr_type_item = 2";
		}

		if ($_paper == '0') {
			$tmp_bill[0][]	= "bill_type_invoice = '0'";
			$tmp_turn[0][]	= "turn_paper = 0";
		} else if ($_paper == '1') {
			$tmp_bill[0][]	= "bill_type_invoice = '1'";
			$tmp_turn[0][]	= "turn_paper = 1";
			$tmp_dr[0][]	= "dr_code is null";
		} else if ($_paper == 'A') {
			$tmp_bill[0][]	= "bill_paper_format = 'A'";
			$tmp_turn[0][]	= "turn_paper = 0";
			$tmp_dr[0][]	= "dr_code is null";
		} else if ($_paper == 'B') {
			$tmp_bill[0][]	= "bill_paper_format = 'B'";
			$tmp_turn[0][]	= "turn_paper = 1";
			$tmp_dr[0][]	= "dr_code is null";
		}

		if($_marketing != "all") {
			$tmp_bill[0][]	= "bill_responsible_by = $_marketing";
			$tmp_turn[0][]	= "turn_responsible_by = $_marketing";
			$tmp_dr[0][]	= "dr_code is null = $_marketing";
		}


		if($cboSearchType != '' && $txtSearch != '') {
			$type = array("byCity"=>"cus_city");
			$tmp_bill[0][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmp_turn[0][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmp_dr[0][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$get[0][] = "$cboSearchType=$txtSearch";
		}

		if ($some_date != "") {
			$tmp_bill[0][]	= "bill_inv_date = DATE '$some_date'";
			$tmp_turn[0][]	= "turn_return_date = DATE '$some_date'";
			$tmp_dr[0][]	= "dr_issued_date = DATE '$some_date'";
		} else {
			$tmp_bill[0][]	= "bill_inv_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_turn[0][]	= "turn_return_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
			$tmp_dr[0][]	= "dr_issued_date  BETWEEN DATE '$period_from' AND DATE '$period_to'";
		}

	break;

}

$strWhere[0]	= implode(" AND ", $tmp_bill[0]);
$strWhere[1]	= implode(" AND ", $tmp_turn[0]);
$strWhere[2]	= implode(" AND ", $tmp_dr[0]);
$strWhere[3]	= implode(" AND ", $tmp_bill[1]);
$strWhere[4]	= implode(" AND ", $tmp_turn[1]);
$strWhere[5]	= implode(" AND ", $tmp_dr[1]);
?>