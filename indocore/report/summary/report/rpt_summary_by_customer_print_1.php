<?php
//SET WHERE PARAMETER
$tmp_bill	= array();
$tmp_sl		= array();
$tmp_turn	= array();
$tmp_cus_bill	= array();
$tmp_cus_sl		= array();
$tmp_cus_turn	= array();
$strWhere		= array();
$strWhereCus	= array();

switch (ZKP_URL) {
	// ALL ==============================================================================================================
	case "ALL":

		if($_order_by == "1") {			// INDOCORE
			$tmp_bill[0][]		= "bill_ordered_by = 1";
			$tmp_sl[0][]		= "bill_ordered_by = 1";
			$tmp_turn[0][]		= "turn_ordered_by = 1";
			$tmp_cus_bill[0][]	= "a.bill_ordered_by = 1";
			$tmp_cus_sl[0][]	= "a.bill_ordered_by = 1";
			$tmp_cus_turn[0][]	= "a.turn_ordered_by = 1";
			$tmp_cus_bill[1][]	= "a.bill_code is null";
			$tmp_bill[1][]		= "bill_code is null";
			$tmp_sl[1][]		= "bill_code is null";
			$tmp_turn[1][]		= "turn_code is null";
			$tmp_cus_bill[1][]	= "a.bill_code is null";
			$tmp_cus_sl[1][]	= "a.bill_code is null";
			$tmp_cus_turn[1][]	= "a.turn_code is null";
		} else if($_order_by == "2") {		// MEDIKUS
			$tmp_bill[0][]		= "bill_ordered_by = 2";
			$tmp_sl[0][]		= "bill_ordered_by = 2";
			$tmp_turn[0][]		= "turn_ordered_by = 2";
			$tmp_cus_bill[0][]	= "a.bill_ordered_by = 2";
			$tmp_cus_sl[0][]	= "a.bill_ordered_by = 2";
			$tmp_cus_turn[0][]	= "a.turn_ordered_by = 2";
			$tmp_cus_bill[1][]	= "a.bill_code is null";
			$tmp_bill[1][]		= "bill_code is null";
			$tmp_sl[1][]		= "bill_code is null";
			$tmp_turn[1][]		= "turn_code is null";
			$tmp_cus_bill[1][]	= "a.bill_code is null";
			$tmp_cus_sl[1][]	= "a.bill_code is null";
			$tmp_cus_turn[1][]	= "a.turn_code is null";
		} else if($_order_by == "3") {		// MEDISINDO
			$tmp_bill[0][]		= "bill_code is null";
			$tmp_turn[0][]		= "turn_code is null";
			$tmp_sl[0][]		= "bill_code is null";
			$tmp_cus_bill[0][]	= "a.bill_code is null";
			$tmp_cus_turn[0][]	= "a.turn_code is null";
			$tmp_cus_sl[0][]	= "a.bill_code is null";
			$tmp_bill[1][]		= "bill_ordered_by = 1";
			$tmp_sl[1][]		= "bill_ordered_by = 1";
			$tmp_turn[1][]		= "turn_ordered_by = 1";
			$tmp_cus_bill[1][]	= "a.bill_ordered_by = 1";
			$tmp_cus_sl[1][]	= "a.bill_ordered_by = 1";
			$tmp_cus_turn[1][]	= "a.turn_ordered_by = 1";
			$tmp_cus_bill[1][]	= "a.bill_code is null";
		} else if($_order_by == "4") {		// SAMUDIA
			$tmp_bill[0][]		= "bill_code is null";
			$tmp_turn[0][]		= "turn_code is null";
			$tmp_sl[0][]		= "bill_code is null";
			$tmp_cus_bill[0][]	= "a.bill_code is null";
			$tmp_cus_turn[0][]	= "a.turn_code is null";
			$tmp_cus_sl[0][]	= "a.bill_code is null";
			$tmp_bill[1][]		= "bill_ordered_by = 2";
			$tmp_sl[1][]		= "bill_ordered_by = 2";
			$tmp_turn[1][]		= "turn_ordered_by = 2";
			$tmp_cus_bill[1][]	= "a.bill_ordered_by = 2";
			$tmp_cus_sl[1][]	= "a.bill_ordered_by = 2";
			$tmp_cus_turn[1][]	= "a.turn_ordered_by = 2";
		} else if($_order_by == "5") {		// INDOCORE & MEDIKUS
			$tmp_bill[1][]		= "bill_code is null";
			$tmp_turn[1][]		= "turn_code is null";
			$tmp_sl[1][]		= "bill_code is null";
			$tmp_cus_bill[1][]	= "a.bill_code is null";
			$tmp_cus_turn[1][]	= "a.turn_code is null";
			$tmp_cus_sl[1][]	= "a.bill_code is null";
		} else if($_order_by == "6") {		// MEDISINDO & SAMUDIA			
			$tmp_bill[0][]		= "bill_code is null";
			$tmp_turn[0][]		= "turn_code is null";
			$tmp_sl[0][]		= "bill_code is null";
			$tmp_cus_bill[0][]	= "a.bill_code is null";
			$tmp_cus_turn[0][]	= "a.turn_code is null";
			$tmp_cus_sl[0][]	= "a.bill_code is null";
		}

		if ($_last_category != 0) {
			$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
			$tmp_bill[0][]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_turn[0][]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_bill[1][]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_turn[1][]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			#$tmp_cus_bill[0][]	= "a.bill_code is null";
			#$tmp_cus_turn[0][]	= "a.turn_code is null";
		}

		if($_chk_company == 'off') {
			$tmp_bill[0][]		= "bill_ship_to NOT IN ('0MDS', '0SMD')";
			$tmp_turn[0][]		= "turn_ship_to NOT IN ('0MDS', '0SMD')";
			$tmp_sl[0][]		= "bill_code NOT IN ('0MDS', '0SMD')";
			$tmp_bill[1][]		= "bill_ship_to NOT IN ('6IDC', '0MDS', '0SMD')";
			$tmp_turn[1][]		= "turn_ship_to NOT IN ('6IDC', '0MDS', '0SMD')";
			$tmp_sl[1][]		= "bill_code NOT IN ('6IDC', '0MDS', '0SMD')";
			$tmpbill_item[0][]	= "a.bill_ship_to NOT IN ('0MDS', '0SMD')";
			$tmpturn_item[0][]	= "a.turn_ship_to NOT IN ('0MDS', '0SMD')";
			$tmpdr_item[0][]	= "a.bill_ship_to NOT IN ('0MDS', '0SMD')";
			$tmpbill_item[1][]	= "a.bill_ship_to NOT IN ('6IDC', '0MDS', '0SMD')";
			$tmpturn_item[1][]	= "a.turn_ship_to NOT IN ('6IDC', '0MDS', '0SMD')";
			$tmpdr_item[1][]	= "a.bill_ship_to NOT IN ('6IDC', '0MDS', '0SMD')";
		}

		if($_marketing != "all") {
			$tmp_bill[0][]		= "bill_responsible_by = $_marketing";
			$tmp_sl[0][]		= "bill_responsible_by= $_marketing";
			$tmp_turn[0][]		= "turn_responsible_by = $_marketing";
			$tmp_cus_bill[0][]	= "c.bill_responsible_by = $_marketing";
			$tmp_cus_sl[0][]	= "c.bill_responsible_by = $_marketing";
			$tmp_cus_turn[0][]	= "c.turn_responsible_by = $_marketing";

			$tmp_bill[1][]		= "bill_responsible_by = $_marketing";
			$tmp_sl[1][]		= "bill_responsible_by = $_marketing";
			$tmp_turn[1][]		= "turn_responsible_by = $_marketing";
			$tmp_cus_bill[1][]	= "c.bill_responsible_by = $_marketing";
			$tmp_cus_sl[1][]	= "c.bill_responsible_by = $_marketing";
			$tmp_cus_turn[1][]	= "c.turn_responsible_by = $_marketing";
		}
		
		if ($_filter_doc == "I") {
			$tmp_turn[0][]		= "turn_code = NULL";
			$tmp_cus_turn[0][]	= "a.turn_code = NULL";
			$tmp_turn[1][]		= "turn_code = NULL";
			$tmp_cus_turn[1][]	= "a.turn_code = NULL";			
		} else if ($_filter_doc == "R") {
			$tmp_bill[0][]		= "bill_code = NULL";
			$tmp_sl[0][]		= "bill_code = NULL";
			$tmp_cus_bill[0][]	= "a.bill_code = NULL";
			$tmp_cus_sl[0][]	= "a.bill_code = NULL";
			$tmp_bill[1][]		= "bill_code = NULL";
			$tmp_sl[1][]		= "bill_code = NULL";
			$tmp_cus_bill[1][]	= "a.bill_code = NULL";
			$tmp_cus_sl[1][]	= "a.bill_code = NULL";
		}
		
		if($_dept != 'all') {
			$tmp_bill[0][]		= "bill_dept = '$_dept'";
			$tmp_sl[0][]		= "bill_dept = '$_dept'";
			$tmp_turn[0][]		= "turn_dept = '$_dept'";
			$tmp_cus_bill[0][]	= "a.bill_dept = '$_dept'";
			$tmp_cus_sl[0][]	= "a.bill_dept = '$_dept'";
			$tmp_cus_turn[0][]	= "a.turn_dept = '$_dept'";
			$tmp_bill[1][]		= "bill_dept = '$_dept'";
			$tmp_sl[1][]		= "bill_dept = '$_dept'";
			$tmp_turn[1][]		= "turn_dept = '$_dept'";
			$tmp_cus_bill[1][]	= "a.bill_dept = '$_dept'";
			$tmp_cus_sl[1][]	= "a.bill_dept = '$_dept'";
			$tmp_cus_turn[1][]	= "a.turn_dept = '$_dept'";
		}
		
		if ($some_date != "") {
			$tmp_bill[0][]		= "bill_inv_date =DATE '$some_date'";
			$tmp_sl[0][]		= "bill_inv_date =DATE '$some_date'";
			$tmp_turn[0][]		= "turn_return_date =DATE '$some_date'";
			$tmp_cus_bill[0][]	= "a.bill_inv_date = DATE '$some_date'";
			$tmp_cus_sl[0][]	= "a.bill_inv_date = DATE '$some_date'";
			$tmp_cus_turn[0][]	= "a.turn_return_date = DATE '$some_date'";

			$tmp_bill[1][]		= "bill_inv_date =DATE '$some_date'";
			$tmp_sl[1][]		= "bill_inv_date =DATE '$some_date'";
			$tmp_turn[1][]		= "turn_return_date =DATE '$some_date'";
			$tmp_cus_bill[1][]	= "a.bill_inv_date = DATE '$some_date'";
			$tmp_cus_sl[1][]	= "a.bill_inv_date = DATE '$some_date'";
			$tmp_cus_turn[1][]	= "a.turn_return_date = DATE '$some_date'";
		} else {
			$tmp_bill[0][]		= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_sl[0][]		= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_turn[0][]		= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_cus_bill[0][]	= "a.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_cus_sl[0][]	= "a.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_cus_turn[0][]	= "a.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";

			$tmp_bill[1][]		= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_sl[1][]		= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_turn[1][]		= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_cus_bill[1][]	= "a.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_cus_sl[1][]	= "a.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_cus_turn[1][]	= "a.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_bill[0][]		= "bill_vat > 0";
			$tmp_sl[0][]		= "bill_vat > 0";
			$tmp_turn[0][]		= "turn_vat > 0";
			$tmp_cus_bill[0][]	= "a.bill_vat > 0";
			$tmp_cus_sl[0][]	= "a.bill_vat > 0";
			$tmp_cus_turn[0][]	= "a.turn_vat > 0";
			$tmp_bill[1][]		= "bill_vat > 0";
			$tmp_sl[1][]		= "bill_vat > 0";
			$tmp_turn[1][]		= "turn_vat > 0";
			$tmp_cus_bill[1][]	= "a.bill_vat > 0";
			$tmp_cus_sl[1][]	= "a.bill_vat > 0";
			$tmp_cus_turn[1][]	= "a.turn_vat > 0";
		} else if($_vat == 'vat-IO') {
			$tmp_bill[0][]		= "bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmp_sl[0][]		= "bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmp_turn[0][]		= "turn_vat > 0";
			$tmp_cus_bill[0][]	= "a.bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmp_cus_sl[0][]	= "a.bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmp_cus_turn[0][]	= "a.turn_vat > 0";
			$tmp_bill[1][]		= "bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmp_sl[1][]		= "bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmp_turn[1][]		= "turn_vat > 0";
			$tmp_cus_bill[1][]	= "a.bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmp_cus_sl[1][]	= "a.bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmp_cus_turn[1][]	= "a.turn_vat > 0";
		} else if($_vat == 'vat-IP') {
			$tmp_bill[0][]		= "bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmp_sl[0][]		= "bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmp_turn[0][]		= "turn_code = NULL";
			$tmp_cus_bill[0][]	= "a.bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmp_cus_sl[0][]	= "a.bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmp_cus_turn[0][]	= "a.turn_code = NULL";
			$tmp_bill[1][]		= "bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmp_sl[1][]		= "bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmp_turn[1][]		= "turn_code = NULL";
			$tmp_cus_bill[1][]	= "a.bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmp_cus_sl[1][]	= "a.bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmp_cus_turn[1][]	= "a.turn_code = NULL";
		} else if ($_vat == 'non') {
			$tmp_bill[0][]		= "bill_vat = 0";
			$tmp_sl[0][]		= "bill_vat = 0";
			$tmp_turn[0][]		= "turn_vat = 0";
			$tmp_cus_bill[0][]	= "a.bill_vat = 0";
			$tmp_cus_sl[0][]	= "a.bill_vat = 0";
			$tmp_cus_turn[0][]	= "a.turn_vat = 0";
			$tmp_bill[1][]		= "bill_vat = 0";
			$tmp_sl[1][]		= "bill_vat = 0";
			$tmp_turn[1][]		= "turn_vat = 0";
			$tmp_cus_bill[1][]	= "a.bill_vat = 0";
			$tmp_cus_sl[1][]	= "a.bill_vat = 0";
			$tmp_cus_turn[1][]	= "a.turn_vat = 0";
		}

	break;

	// IDC ==============================================================================================================
	case "IDC":

		$tmp_bill[0][]	= "bill_ordered_by = 1 AND idc_isValidShowInvoice('idc', bill_code,'billing')";
		$tmp_turn[0][]	= "turn_ordered_by = 1 AND idc_isValidShowInvoice('idc', turn_code,'billing_return')";
		$tmp_cus_bill[0][] = "a.bill_ordered_by = 1 AND idc_isValidShowInvoice('idc', a.bill_code,'billing')";
		$tmp_cus_turn[0][] = "a.turn_ordered_by = 1 AND idc_isValidShowInvoice('idc', a.turn_code,'billing_return')";

		if($_marketing != "all") {
			$tmp_bill[0][]		= "cus_responsibility_to = $_marketing";
			$tmp_sl[0][]		= "cus_responsibility_to = $_marketing";
			$tmp_turn[0][]		= "cus_responsibility_to = $_marketing";
			$tmp_cus_bill[0][]	= "c.cus_responsibility_to = $_marketing";
			$tmp_cus_sl[0][]	= "c.cus_responsibility_to = $_marketing";
			$tmp_cus_turn[0][]	= "c.cus_responsibility_to = $_marketing";
		}
		
		if ($_filter_doc == "I") {
			$tmp_turn[0][]		= "turn_code = NULL";
			$tmp_cus_turn[0][]	= "a.turn_code = NULL";
		} else if ($_filter_doc == "R") {
			$tmp_bill[0][]		= "bill_code = NULL";
			$tmp_sl[0][]		= "bill_code = NULL";
			$tmp_cus_bill[0][]	= "a.bill_code = NULL";
			$tmp_cus_sl[0][]	= "a.bill_code = NULL";
		}
		
		if($_dept != 'all') {
			$tmp_bill[0][]		= "bill_dept = '$_dept'";
			$tmp_sl[0][]		= "bill_dept = '$_dept'";
			$tmp_turn[0][]		= "turn_dept = '$_dept'";
			$tmp_cus_bill[0][]	= "a.bill_dept = '$_dept'";
			$tmp_cus_sl[0][]	= "a.bill_dept = '$_dept'";
			$tmp_cus_turn[0][]	= "a.turn_dept = '$_dept'";
		}
		
		if ($some_date != "") {
			$tmp_bill[0][]		= "bill_inv_date =DATE '$some_date'";
			$tmp_sl[0][]		= "bill_inv_date =DATE '$some_date'";
			$tmp_turn[0][]		= "turn_return_date =DATE '$some_date'";
			$tmp_cus_bill[0][]	= "a.bill_inv_date = DATE '$some_date'";
			$tmp_cus_sl[0][]	= "a.bill_inv_date = DATE '$some_date'";
			$tmp_cus_turn[0][]	= "a.turn_return_date = DATE '$some_date'";
		} else {
			$tmp_bill[0][]		= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_sl[0][]		= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_turn[0][]		= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_cus_bill[0][]	= "a.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_cus_sl[0][]	= "a.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_cus_turn[0][]	= "a.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_bill[0][]		= "bill_vat > 0";
			$tmp_sl[0][]		= "bill_vat > 0";
			$tmp_turn[0][]		= "turn_vat > 0";
			$tmp_cus_bill[0][]	= "a.bill_vat > 0";
			$tmp_cus_sl[0][]	= "a.bill_vat > 0";
			$tmp_cus_turn[0][]	= "a.turn_vat > 0";
		} else if($_vat == 'vat-IO') {
			$tmp_bill[0][]		= "bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmp_sl[0][]		= "bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmp_turn[0][]		= "turn_vat > 0";
			$tmp_cus_bill[0][]	= "a.bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmp_cus_sl[0][]	= "a.bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmp_cus_turn[0][]	= "a.turn_vat > 0";
		} else if($_vat == 'vat-IP') {
			$tmp_bill[0][]		= "bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmp_sl[0][]		= "bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmp_turn[0][]		= "turn_code = NULL";
			$tmp_cus_bill[0][]	= "a.bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmp_cus_sl[0][]	= "a.bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmp_cus_turn[0][]	= "a.turn_code = NULL";
		} else if ($_vat == 'non') {
			$tmp_bill[0][]		= "bill_vat = 0";
			$tmp_sl[0][]		= "bill_vat = 0";
			$tmp_turn[0][]		= "turn_vat = 0";
			$tmp_cus_bill[0][]	= "a.bill_vat = 0";
			$tmp_cus_sl[0][]	= "a.bill_vat = 0";
			$tmp_cus_turn[0][]	= "a.turn_vat = 0";
		}
		
		$tmp_bill[0][]	= "bill_type_billing in (1,2)";
		$tmp_sl[0][]	= "bill_type_billing = 3";

	break;

	// MED ==============================================================================================================
	case "MED":

		if($_marketing != "all") {
			$tmp_bill[1][]		= "cus_responsibility_to = $_marketing";
			$tmp_sl[1][]		= "cus_responsibility_to = $_marketing";
			$tmp_turn[1][]		= "cus_responsibility_to = $_marketing";
			$tmp_cus_bill[1][]	= "c.cus_responsibility_to = $_marketing";
			$tmp_cus_sl[1][]	= "c.cus_responsibility_to = $_marketing";
			$tmp_cus_turn[1][]	= "c.cus_responsibility_to = $_marketing";
		}
		
		if ($_filter_doc == "I") {
			$tmp_turn[1][]		= "turn_code = NULL";
			$tmp_cus_turn[1][]	= "a.turn_code = NULL";
		} else if ($_filter_doc == "R") {
			$tmp_bill[1][]		= "bill_code = NULL";
			$tmp_sl[1][]		= "bill_code = NULL";
			$tmp_cus_bill[1][]	= "a.bill_code = NULL";
			$tmp_cus_sl[1][]	= "a.bill_code = NULL";
		}
		
		if($_dept != 'all') {
			$tmp_bill[1][]		= "bill_dept = '$_dept'";
			$tmp_sl[1][]		= "bill_dept = '$_dept'";
			$tmp_turn[1][]		= "turn_dept = '$_dept'";
			$tmp_cus_bill[1][]	= "a.bill_dept = '$_dept'";
			$tmp_cus_sl[1][]	= "a.bill_dept = '$_dept'";
			$tmp_cus_turn[1][]	= "a.turn_dept = '$_dept'";
		}
		
		if ($some_date != "") {
			$tmp_bill[1][]		= "bill_inv_date =DATE '$some_date'";
			$tmp_sl[1][]		= "bill_inv_date =DATE '$some_date'";
			$tmp_turn[1][]		= "turn_return_date =DATE '$some_date'";
			$tmp_cus_bill[1][]	= "a.bill_inv_date = DATE '$some_date'";
			$tmp_cus_sl[1][]	= "a.bill_inv_date = DATE '$some_date'";
			$tmp_cus_turn[1][]	= "a.turn_return_date = DATE '$some_date'";
		} else {
			$tmp_bill[1][]		= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_sl[1][]		= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_turn[1][]		= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_cus_bill[1][]	= "a.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_cus_sl[1][]	= "a.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_cus_turn[1][]	= "a.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_bill[1][]		= "bill_vat > 0";
			$tmp_sl[1][]		= "bill_vat > 0";
			$tmp_turn[1][]		= "turn_vat > 0";
			$tmp_cus_bill[1][]	= "a.bill_vat > 0";
			$tmp_cus_sl[1][]	= "a.bill_vat > 0";
			$tmp_cus_turn[1][]	= "a.turn_vat > 0";
		} else if($_vat == 'vat-IO') {
			$tmp_bill[1][]		= "bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmp_sl[1][]		= "bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmp_turn[1][]		= "turn_vat > 0";
			$tmp_cus_bill[1][]	= "a.bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmp_cus_sl[1][]	= "a.bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmp_cus_turn[1][]	= "a.turn_vat > 0";
		} else if($_vat == 'vat-IP') {
			$tmp_bill[1][]		= "bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmp_sl[1][]		= "bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmp_turn[1][]		= "turn_code = NULL";
			$tmp_cus_bill[1][]	= "a.bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmp_cus_sl[1][]	= "a.bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmp_cus_turn[1][]	= "a.turn_code = NULL";
		} else if ($_vat == 'non') {
			$tmp_bill[1][]		= "bill_vat = 0";
			$tmp_sl[1][]		= "bill_vat = 0";
			$tmp_turn[1][]		= "turn_vat = 0";
			$tmp_cus_bill[1][]	= "a.bill_vat = 0";
			$tmp_cus_sl[1][]	= "a.bill_vat = 0";
			$tmp_cus_turn[1][]	= "a.turn_vat = 0";
		}
		
		$tmp_bill[1][]	= "bill_type_billing in (1,2)";
		$tmp_sl[1][]	= "bill_type_billing = 3";

	break;

	// MEP ==============================================================================================================
	case "MEP":

		$tmp_bill[0][]	= "bill_ordered_by = 2 AND idc_isValidShowInvoice('idc', bill_code,'billing')";
		$tmp_turn[0][]	= "turn_ordered_by = 2 AND idc_isValidShowInvoice('idc', turn_code,'billing_return')";
		$tmp_cus_bill[0][] = "a.bill_ordered_by = 2 AND idc_isValidShowInvoice('idc', a.bill_code,'billing')";
		$tmp_cus_turn[0][] = "a.turn_ordered_by = 2 AND idc_isValidShowInvoice('idc', a.turn_code,'billing_return')";

		if($_marketing != "all") {
			$tmp_bill[0][]		= "cus_responsibility_to = $_marketing";
			$tmp_sl[0][]		= "cus_responsibility_to = $_marketing";
			$tmp_turn[0][]		= "cus_responsibility_to = $_marketing";
			$tmp_cus_bill[0][]	= "c.cus_responsibility_to = $_marketing";
			$tmp_cus_sl[0][]	= "c.cus_responsibility_to = $_marketing";
			$tmp_cus_turn[0][]	= "c.cus_responsibility_to = $_marketing";
		}
		
		if ($_filter_doc == "I") {
			$tmp_turn[0][]		= "turn_code = NULL";
			$tmp_cus_turn[0][]	= "a.turn_code = NULL";
		} else if ($_filter_doc == "R") {
			$tmp_bill[0][]		= "bill_code = NULL";
			$tmp_sl[0][]		= "bill_code = NULL";
			$tmp_cus_bill[0][]	= "a.bill_code = NULL";
			$tmp_cus_sl[0][]	= "a.bill_code = NULL";
		}
		
		if($_dept != 'all') {
			$tmp_bill[0][]		= "bill_dept = '$_dept'";
			$tmp_sl[0][]		= "bill_dept = '$_dept'";
			$tmp_turn[0][]		= "turn_dept = '$_dept'";
			$tmp_cus_bill[0][]	= "a.bill_dept = '$_dept'";
			$tmp_cus_sl[0][]	= "a.bill_dept = '$_dept'";
			$tmp_cus_turn[0][]	= "a.turn_dept = '$_dept'";
		}
		
		if ($some_date != "") {
			$tmp_bill[0][]		= "bill_inv_date =DATE '$some_date'";
			$tmp_sl[0][]		= "bill_inv_date =DATE '$some_date'";
			$tmp_turn[0][]		= "turn_return_date =DATE '$some_date'";
			$tmp_cus_bill[0][]	= "a.bill_inv_date = DATE '$some_date'";
			$tmp_cus_sl[0][]	= "a.bill_inv_date = DATE '$some_date'";
			$tmp_cus_turn[0][]	= "a.turn_return_date = DATE '$some_date'";
		} else {
			$tmp_bill[0][]		= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_sl[0][]		= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_turn[0][]		= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_cus_bill[0][]	= "a.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_cus_sl[0][]	= "a.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_cus_turn[0][]	= "a.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_bill[0][]		= "bill_vat > 0";
			$tmp_sl[0][]		= "bill_vat > 0";
			$tmp_turn[0][]		= "turn_vat > 0";
			$tmp_cus_bill[0][]	= "a.bill_vat > 0";
			$tmp_cus_sl[0][]	= "a.bill_vat > 0";
			$tmp_cus_turn[0][]	= "a.turn_vat > 0";
		} else if($_vat == 'vat-IO') {
			$tmp_bill[0][]		= "bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmp_sl[0][]		= "bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmp_turn[0][]		= "turn_vat > 0";
			$tmp_cus_bill[0][]	= "a.bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmp_cus_sl[0][]	= "a.bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmp_cus_turn[0][]	= "a.turn_vat > 0";
		} else if($_vat == 'vat-IP') {
			$tmp_bill[0][]		= "bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmp_sl[0][]		= "bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmp_turn[0][]		= "turn_code = NULL";
			$tmp_cus_bill[0][]	= "a.bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmp_cus_sl[0][]	= "a.bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmp_cus_turn[0][]	= "a.turn_code = NULL";
		} else if ($_vat == 'non') {
			$tmp_bill[0][]		= "bill_vat = 0";
			$tmp_sl[0][]		= "bill_vat = 0";
			$tmp_turn[0][]		= "turn_vat = 0";
			$tmp_cus_bill[0][]	= "a.bill_vat = 0";
			$tmp_cus_sl[0][]	= "a.bill_vat = 0";
			$tmp_cus_turn[0][]	= "a.turn_vat = 0";
		}
		
		$tmp_bill[0][]	= "bill_type_billing in (1,2)";
		$tmp_sl[0][]	= "bill_type_billing = 3";

	break;

}

$strWhere[0]	= implode(" AND ", $tmp_bill[0]);
$strWhere[1]	= implode(" AND ", $tmp_sl[0]);
$strWhere[2]	= implode(" AND ", $tmp_turn[0]);
$strWhere[3]	= implode(" AND ", $tmp_bill[1]);
$strWhere[4]	= implode(" AND ", $tmp_sl[1]);
$strWhere[5]	= implode(" AND ", $tmp_turn[1]);

$strWhereCus[0]	= implode(" AND ", $tmp_cus_bill[0]);
$strWhereCus[1]	= implode(" AND ", $tmp_cus_sl[0]);
$strWhereCus[2]	= implode(" AND ", $tmp_cus_turn[0]);
$strWhereCus[3]	= implode(" AND ", $tmp_cus_bill[1]);
$strWhereCus[4]	= implode(" AND ", $tmp_cus_sl[1]);
$strWhereCus[5]	= implode(" AND ", $tmp_cus_turn[1]);
/*
echo "<pre>";
var_dump($strWhere);
echo "</pre>";
exit;
*/
?>