<?php
//SET WHERE PARAMETER
$tmp_bill		= array();
$tmp_turn		= array();
$tmp_dr			= array();
$tmpbill_item	= array();
$tmpturn_item	= array();
$tmpdr_item		= array();
$strWhere		= array();
$strWhereItem	= array();

switch (ZKP_URL) {
	// ALL ==============================================================================================================
	case "ALL":

		if($_order_by == "1") {				// INDOCORE
			$tmp_bill[0][]	= "bill_ordered_by = 1";
			$tmp_turn[0][]	= "turn_ordered_by = 1";
			$tmp_dr[0][]	= "dr_ordered_by = 1";
			$tmpbill_item[0][]	= "bill_ordered_by = 1";
			$tmpturn_item[0][]	= "turn_ordered_by = 1";
			$tmpdr_item[0][]	= "dr_ordered_by = 1";
			$tmp_bill[1][]	= "bill_code is null";
			$tmp_turn[1][]	= "turn_code is null";
			$tmp_dr[1][]	= "dr_code is null";
			$tmpbill_item[1][]	= "bill_code is null";
			$tmpturn_item[1][]	= "turn_code is null";
			$tmpdr_item[1][]	= "dr_code is null";
		} else if($_order_by == "2") {		// MEDISINDO
			$tmp_bill[0][]	= "bill_code is null";
			$tmp_turn[0][]	= "turn_code is null";
			$tmp_dr[0][]	= "dr_code is null";
			$tmpbill_item[0][]	= "bill_code is null";
			$tmpturn_item[0][]	= "turn_code is null";
			$tmpdr_item[0][]	= "dr_code is null";
		} else if($_order_by == "3") {		// MEDIKUS
			$tmp_bill[0][]	= "bill_ordered_by = 2";
			$tmp_turn[0][]	= "turn_ordered_by = 2";
			$tmp_dr[0][]	= "dr_ordered_by = 2";
			$tmpbill_item[0][]	= "bill_ordered_by = 2";
			$tmpturn_item[0][]	= "turn_ordered_by = 2";
			$tmpdr_item[0][]	= "dr_ordered_by = 2";
			$tmp_bill[1][]	= "bill_code is null";
			$tmp_turn[1][]	= "turn_code is null";
			$tmp_dr[1][]	= "dr_code is null";
			$tmpbill_item[1][]	= "bill_code is null";
			$tmpturn_item[1][]	= "turn_code is null";
			$tmpdr_item[1][]	= "dr_code is null";
		} else if($_order_by == "4") {		// INDOCORE & MEDIKUS
			$tmp_bill[1][]	= "bill_code is null";
			$tmp_turn[1][]	= "turn_code is null";
			$tmp_dr[1][]	= "dr_code is null";
			$tmpbill_item[1][]	= "bill_code is null";
			$tmpturn_item[1][]	= "turn_code is null";
			$tmpdr_item[1][]	= "dr_code is null";
		}

		if ($_filter_doc == "I") {
			$tmp_turn[0][]		= "turn.turn_code = NULL";
			$tmp_dr[0][]		= "dr.dr_code = NULL";
			$tmpturn_item[0][]	= "turn.turn_code= NULL";
			$tmpdr_item[0][]	= "dr.dr_code= NULL";
			$tmp_turn[1][]		= "turn.turn_code = NULL";
			$tmp_dr[1][]		= "dr.dr_code = NULL";
			$tmpturn_item[1][]	= "turn.turn_code= NULL";
			$tmpdr_item[1][]	= "dr.dr_code= NULL";
		} else if ($_filter_doc == "R") {
			$tmp_bill[0][]		= "bill.bill_code = NULL";
			$tmp_dr[0][]		= "dr.dr_code = NULL";
			$tmpbill_item[0][] = "bill.bill_code = NULL";
			$tmpdr_item[0][]	= "dr.dr_code= NULL";
			$tmp_bill[1][]		= "bill.bill_code = NULL";
			$tmp_dr[1][]		= "dr.dr_code = NULL";
			$tmpbill_item[1][] = "bill.bill_code = NULL";
			$tmpdr_item[1][]	= "dr.dr_code= NULL";
		} else if ($_filter_doc == "DR") {
			$tmp_bill[0][]		= "bill.bill_code = NULL";
			$tmp_turn[0][]		= "turn.turn_code = NULL";
			$tmpbill_item[0][] = "bill.bill_code = NULL";
			$tmpturn_item[0][] = "turn.turn_code= NULL";
			$tmp_bill[1][]		= "bill.bill_code = NULL";
			$tmp_turn[1][]		= "turn.turn_code = NULL";
			$tmpbill_item[1][] = "bill.bill_code = NULL";
			$tmpturn_item[1][] = "turn.turn_code= NULL";
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
		
		if($_marketing != "all") {
			$tmp_bill[0][]		= "bill_responsible_by = $_marketing";
			$tmp_turn[0][]		= "turn_responsible_by = $_marketing";
			$tmp_dr[0][]		= "dr_code is null";
			$tmpbill_item[0][]	= "bill_responsible_by = $_marketing";
			$tmpturn_item[0][]	= "turn_responsible_by  = $_marketing";
			$tmpdr_item[0][]	= "dr_code is null";
			$tmp_bill[1][]		= "bill_responsible_by = $_marketing";
			$tmp_turn[1][]		= "turn_responsible_by  = $_marketing";
			$tmp_dr[1][]		= "dr_code is null";
			$tmpbill_item[1][]	= "bill_responsible_by = $_marketing";
			$tmpturn_item[1][]	= "turn_responsible_by  = $_marketing";
			$tmpdr_item[1][]	= "dr_code is null";
		}
		
		if($_dept != 'all') {
			$tmp_bill[0][] = "bill.bill_dept = '$_dept'";
			$tmp_turn[0][] = "turn.turn_dept = '$_dept'";
			$tmp_dr[0][]	= "dr.dr_dept = '$_dept'";
			$tmpbill_item[0][] = "bill.bill_dept = '$_dept'";
			$tmpturn_item[0][] = "turn.turn_dept = '$_dept'";
			$tmpdr_item[0][]	= "dr.dr_dept = '$_dept'";
			$tmp_bill[1][] = "bill.bill_dept = '$_dept'";
			$tmp_turn[1][] = "turn.turn_dept = '$_dept'";
			$tmp_dr[1][]	= "dr.dr_dept = '$_dept'";
			$tmpbill_item[1][] = "bill.bill_dept = '$_dept'";
			$tmpturn_item[1][] = "turn.turn_dept = '$_dept'";
			$tmpdr_item[1][]	= "dr.dr_dept = '$_dept'";
		}
		
		if ($some_date != "") {
			$tmp_bill[0][] 	= "bill.bill_inv_date = DATE '$some_date'";
			$tmp_turn[0][] 	= "turn.turn_return_date = DATE '$some_date'";
			$tmp_dr[0][]		= "dr.dr_issued_date = DATE '$some_date'";
			$tmpbill_item[0][]	= "bill.bill_inv_date = DATE '$some_date'";
			$tmpturn_item[0][]	= "turn.turn_return_date = DATE '$some_date'";
			$tmpdr_item[0][]	= "dr.dr_issued_date = DATE '$some_date'";

			$tmp_bill[1][] 	= "bill.bill_inv_date = DATE '$some_date'";
			$tmp_turn[1][] 	= "turn.turn_return_date = DATE '$some_date'";
			$tmp_dr[1][]		= "dr.dr_issued_date = DATE '$some_date'";
			$tmpbill_item[1][]	= "bill.bill_inv_date = DATE '$some_date'";
			$tmpturn_item[1][]	= "turn.turn_return_date = DATE '$some_date'";
			$tmpdr_item[1][]	= "dr.dr_issued_date = DATE '$some_date'";
		} else {
			$tmp_bill[0][]		= "bill.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_turn[0][]		= "turn.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_dr[0][]		= "dr.dr_issued_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpbill_item[0][] = "bill.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpturn_item[0][] = "turn.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpdr_item[0][]	= "dr.dr_issued_date BETWEEN DATE '$period_from' AND '$period_to'";

			$tmp_bill[1][]		= "bill.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_turn[1][]		= "turn.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_dr[1][]		= "dr.dr_issued_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpbill_item[1][] = "bill.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpturn_item[1][] = "turn.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpdr_item[1][]	= "dr.dr_issued_date BETWEEN DATE '$period_from' AND '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_bill[0][] 	= "bill_vat > 0";
			$tmp_turn[0][] 	= "turn.turn_vat > 0";
			$tmp_dr[0][]		= "dr_type_item = 1";
			$tmpbill_item[0][] = "bill_vat > 0";
			$tmpturn_item[0][] = "turn.turn_vat > 0"; 
			$tmpdr_item[0][]	= "dr_type_item = 1";

			$tmp_bill[1][] 	= "bill_vat > 0";
			$tmp_turn[1][] 	= "turn.turn_vat > 0";
			$tmp_dr[1][]		= "dr_type_item = 1";
			$tmpbill_item[1][] = "bill_vat > 0";
			$tmpturn_item[1][] = "turn.turn_vat > 0"; 
			$tmpdr_item[1][]	= "dr_type_item = 1";
		} else if($_vat == 'vat-IO') {
			$tmp_bill[0][] 	= "bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmp_turn[0][] 	= "turn.turn_vat > 0";
			$tmp_dr[0][]		= "dr_type_item = 1";
			$tmpbill_item[0][] = "bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmpturn_item[0][] = "turn.turn_vat > 0";
			$tmpdr_item[0][]	= "dr_type_item = 1";

			$tmp_bill[1][] 	= "bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmp_turn[1][] 	= "turn.turn_vat > 0";
			$tmp_dr[1][]		= "dr_type_item = 1";
			$tmpbill_item[1][] = "bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmpturn_item[1][] = "turn.turn_vat > 0";
			$tmpdr_item[1][]	= "dr_type_item = 1";
		} else if($_vat == 'vat-IP') {
			$tmp_bill[0][] 	= "bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmp_turn[0][] 	= "turn.turn_code = NULL";
			$tmp_dr[0][]		= "dr_code is null";
			$tmpbill_item[0][] = "bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmpturn_item[0][] = "turn.turn_code = NULL";
			$tmpdr_item[0][]	= "dr_code is null";  

			$tmp_bill[1][] 	= "bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmp_turn[1][] 	= "turn.turn_code = NULL";
			$tmp_dr[1][]		= "dr_code is null";
			$tmpbill_item[1][] = "bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmpturn_item[1][] = "turn.turn_code = NULL";
			$tmpdr_item[1][]	= "dr_code is null";  
		} else if ($_vat == 'non') {
			$tmp_bill[0][] 	= "bill_vat = 0";
			$tmp_turn[0][] 	= "turn.turn_vat = 0";
			$tmp_dr[0][]		= "dr_type_item = 1";
			$tmpbill_item[0][] = "bill_vat = 0";
			$tmpturn_item[0][] = "turn.turn_vat = 0";
			$tmpdr_item[0][]	= "dr_type_item = 2";

			$tmp_bill[1][] 	= "bill_vat = 0";
			$tmp_turn[1][] 	= "turn.turn_vat = 0";
			$tmp_dr[1][]		= "dr_type_item = 1";
			$tmpbill_item[1][] = "bill_vat = 0";
			$tmpturn_item[1][] = "turn.turn_vat = 0";
			$tmpdr_item[1][]	= "dr_type_item = 2";
		}

	break;

	// IDC ==============================================================================================================
	case "IDC":

		$tmp_bill[0][]		= "bill_ordered_by = 1 AND idc_isValidShowInvoice('idc', bill_code,'billing')";
		$tmp_turn[0][]		= "turn_ordered_by = 1 AND idc_isValidShowInvoice('idc', turn_code,'billing_return')";
		$tmp_dr[0][]		= "dr_ordered_by = 1 AND idc_isValidShowInvoice('idc', dr_code,'dr')";
		$tmpbill_item[0][]	= "bill_ordered_by = 1 AND idc_isValidShowInvoice('idc', bill_code,'billing')";
		$tmpturn_item[0][]	= "turn_ordered_by = 1 AND idc_isValidShowInvoice('idc', turn_code,'billing_return')";
		$tmpdr_item[0][]	= "dr_ordered_by = 1 AND idc_isValidShowInvoice('idc', dr_code,'dr')";

		if ($_filter_doc == "I") {
			$tmp_turn[0][]		= "turn.turn_code = NULL";
			$tmp_dr[0][]		= "dr.dr_code = NULL";
			$tmpturn_item[0][] = "turn.turn_code= NULL";
			$tmpdr_item[0][]	= "dr.dr_code= NULL";
		} else if ($_filter_doc == "R") {
			$tmp_bill[0][]		= "bill.bill_code = NULL";
			$tmp_dr[0][]		= "dr.dr_code = NULL";
			$tmpbill_item[0][] = "bill.bill_code = NULL";
			$tmpdr_item[0][]	= "dr.dr_code= NULL";
		} else if ($_filter_doc == "DR") {
			$tmp_bill[0][]		= "bill.bill_code = NULL";
			$tmp_turn[0][]		= "turn.turn_code = NULL";
			$tmpbill_item[0][] = "bill.bill_code = NULL";
			$tmpturn_item[0][] = "turn.turn_code= NULL";
		}
		
		if ($_last_category != 0) {
			$catList = executeSP("idc_getSubCategory", $_last_category);
			$tmp_bill[0][] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_turn[0][] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_dr[0][]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
		}
		
		if($_marketing != "all") {
			$tmp_bill[0][]	= "cus_responsibility_to = $_marketing";
			$tmp_turn[0][] = "cus_responsibility_to = $_marketing";
			$tmp_dr[0][] = "cus_responsibility_to = $_marketing";
			$tmpbill_item[0][] = "cus_responsibility_to = $_marketing";
			$tmpturn_item[0][] = "cus_responsibility_to = $_marketing";
			$tmpdr_item[0][]	= "cus_responsibility_to = $_marketing";
		}
		
		if($_dept != 'all') {
			$tmp_bill[0][] = "bill.bill_dept = '$_dept'";
			$tmp_turn[0][] = "turn.turn_dept = '$_dept'";
			$tmp_dr[0][]	= "dr.dr_dept = '$_dept'";
			$tmpbill_item[0][] = "bill.bill_dept = '$_dept'";
			$tmpturn_item[0][] = "turn.turn_dept = '$_dept'";
			$tmpdr_item[0][]	= "dr.dr_dept = '$_dept'";
		}
		
		if ($some_date != "") {
			$tmp_bill[0][] 	= "bill.bill_inv_date = DATE '$some_date'";
			$tmp_turn[0][] 	= "turn.turn_return_date = DATE '$some_date'";
			$tmp_dr[0][]		= "dr.dr_issued_date = DATE '$some_date'";
			$tmpbill_item[0][]	= "bill.bill_inv_date = DATE '$some_date'";
			$tmpturn_item[0][]	= "turn.turn_return_date = DATE '$some_date'";
			$tmpdr_item[0][]	= "dr.dr_issued_date = DATE '$some_date'";
		} else {
			$tmp_bill[0][]		= "bill.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_turn[0][]		= "turn.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_dr[0][]		= "dr.dr_issued_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpbill_item[0][] = "bill.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpturn_item[0][] = "turn.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpdr_item[0][]	= "dr.dr_issued_date BETWEEN DATE '$period_from' AND '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_bill[0][] 	= "bill_vat > 0";
			$tmp_turn[0][] 	= "turn.turn_vat > 0";
			$tmp_dr[0][]		= "dr_type_item = 1";
			$tmpbill_item[0][] = "bill_vat > 0";
			$tmpturn_item[0][] = "turn.turn_vat > 0"; 
			$tmpdr_item[0][]	= "dr_type_item = 1";
		} else if($_vat == 'vat-IO') {
			$tmp_bill[0][] 	= "bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmp_turn[0][] 	= "turn.turn_vat > 0";
			$tmp_dr[0][]		= "dr_type_item = 1";
			$tmpbill_item[0][] = "bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmpturn_item[0][] = "turn.turn_vat > 0";
			$tmpdr_item[0][]	= "dr_type_item = 1";
		} else if($_vat == 'vat-IP') {
			$tmp_bill[0][] 	= "bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmp_turn[0][] 	= "turn.turn_code = NULL";
			$tmp_dr[0][]		= "dr_code is null";
			$tmpbill_item[0][] = "bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmpturn_item[0][] = "turn.turn_code = NULL";
			$tmpdr_item[0][]	= "dr_code is null";  
		} else if ($_vat == 'non') {
			$tmp_bill[0][] 	= "bill_vat = 0";
			$tmp_turn[0][] 	= "turn.turn_vat = 0";
			$tmp_dr[0][]		= "dr_type_item = 1";
			$tmpbill_item[0][] = "bill_vat = 0";
			$tmpturn_item[0][] = "turn.turn_vat = 0";
			$tmpdr_item[0][]	= "dr_type_item = 2";
		}

	break;

	// MED ==============================================================================================================
	case "MED":

		if ($_filter_doc == "I") {
			$tmp_turn[1][]		= "turn.turn_code = NULL";
			$tmp_dr[1][]		= "dr.dr_code = NULL";
			$tmpturn_item[1][] = "turn.turn_code= NULL";
			$tmpdr_item[1][]	= "dr.dr_code= NULL";
		} else if ($_filter_doc == "R") {
			$tmp_bill[1][]		= "bill.bill_code = NULL";
			$tmp_dr[1][]		= "dr.dr_code = NULL";
			$tmpbill_item[1][] = "bill.bill_code = NULL";
			$tmpdr_item[1][]	= "dr.dr_code= NULL";
		} else if ($_filter_doc == "DR") {
			$tmp_bill[1][]		= "bill.bill_code = NULL";
			$tmp_turn[1][]		= "turn.turn_code = NULL";
			$tmpbill_item[1][] = "bill.bill_code = NULL";
			$tmpturn_item[1][] = "turn.turn_code= NULL";
		}
		
		if ($_last_category != 0) {
			$catList = executeSP("idc_getSubCategory", $_last_category);
			$tmp_bill[1][] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_turn[1][] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_dr[1][]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
		}
		
		if($_marketing != "all") {
			$tmp_bill[1][]	= "cus_responsibility_to = $_marketing";
			$tmp_turn[1][] = "cus_responsibility_to = $_marketing";
			$tmp_dr[1][] = "cus_responsibility_to = $_marketing";
			$tmpbill_item[1][] = "cus_responsibility_to = $_marketing";
			$tmpturn_item[1][] = "cus_responsibility_to = $_marketing";
			$tmpdr_item[1][]	= "cus_responsibility_to = $_marketing";
		}
		
		if($_dept != 'all') {
			$tmp_bill[1][] = "bill.bill_dept = '$_dept'";
			$tmp_turn[1][] = "turn.turn_dept = '$_dept'";
			$tmp_dr[1][]	= "dr.dr_dept = '$_dept'";
			$tmpbill_item[1][] = "bill.bill_dept = '$_dept'";
			$tmpturn_item[1][] = "turn.turn_dept = '$_dept'";
			$tmpdr_item[1][]	= "dr.dr_dept = '$_dept'";
		}
		
		if ($some_date != "") {
			$tmp_bill[1][] 	= "bill.bill_inv_date = DATE '$some_date'";
			$tmp_turn[1][] 	= "turn.turn_return_date = DATE '$some_date'";
			$tmp_dr[1][]		= "dr.dr_issued_date = DATE '$some_date'";
			$tmpbill_item[1][]	= "bill.bill_inv_date = DATE '$some_date'";
			$tmpturn_item[1][]	= "turn.turn_return_date = DATE '$some_date'";
			$tmpdr_item[1][]	= "dr.dr_issued_date = DATE '$some_date'";
		} else {
			$tmp_bill[1][]		= "bill.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_turn[1][]		= "turn.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_dr[1][]		= "dr.dr_issued_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpbill_item[1][] = "bill.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpturn_item[1][] = "turn.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpdr_item[1][]	= "dr.dr_issued_date BETWEEN DATE '$period_from' AND '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_bill[1][] 	= "bill_vat > 0";
			$tmp_turn[1][] 	= "turn.turn_vat > 0";
			$tmp_dr[1][]		= "dr_type_item = 1";
			$tmpbill_item[1][] = "bill_vat > 0";
			$tmpturn_item[1][] = "turn.turn_vat > 0"; 
			$tmpdr_item[1][]	= "dr_type_item = 1";
		} else if($_vat == 'vat-IO') {
			$tmp_bill[1][] 	= "bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmp_turn[1][] 	= "turn.turn_vat > 0";
			$tmp_dr[1][]		= "dr_type_item = 1";
			$tmpbill_item[1][] = "bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmpturn_item[1][] = "turn.turn_vat > 0";
			$tmpdr_item[1][]	= "dr_type_item = 1";
		} else if($_vat == 'vat-IP') {
			$tmp_bill[1][] 	= "bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmp_turn[1][] 	= "turn.turn_code = NULL";
			$tmp_dr[1][]		= "dr_code is null";
			$tmpbill_item[1][] = "bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmpturn_item[1][] = "turn.turn_code = NULL";
			$tmpdr_item[1][]	= "dr_code is null";  
		} else if ($_vat == 'non') {
			$tmp_bill[1][] 	= "bill_vat = 0";
			$tmp_turn[1][] 	= "turn.turn_vat = 0";
			$tmp_dr[1][]		= "dr_type_item = 1";
			$tmpbill_item[1][] = "bill_vat = 0";
			$tmpturn_item[1][] = "turn.turn_vat = 0";
			$tmpdr_item[1][]	= "dr_type_item = 2";
		}

	break;

	// MEP ==============================================================================================================
	case "MEP":

		$tmp_bill[0][]		= "bill_ordered_by = 2 AND idc_isValidShowInvoice('idc', bill_code,'billing')";
		$tmp_turn[0][]		= "turn_ordered_by = 2 AND idc_isValidShowInvoice('idc', turn_code,'billing_return')";
		$tmp_dr[0][]		= "dr_ordered_by = 2 AND idc_isValidShowInvoice('idc', dr_code,'dr')";
		$tmpbill_item[0][]	= "bill_ordered_by = 2 AND idc_isValidShowInvoice('idc', bill_code,'billing')";
		$tmpturn_item[0][]	= "turn_ordered_by = 2 AND idc_isValidShowInvoice('idc', turn_code,'billing_return')";
		$tmpdr_item[0][]	= "dr_ordered_by = 2 AND idc_isValidShowInvoice('idc', dr_code,'dr')";

		if ($_filter_doc == "I") {
			$tmp_turn[0][]		= "turn.turn_code = NULL";
			$tmp_dr[0][]		= "dr.dr_code = NULL";
			$tmpturn_item[0][] = "turn.turn_code= NULL";
			$tmpdr_item[0][]	= "dr.dr_code= NULL";
		} else if ($_filter_doc == "R") {
			$tmp_bill[0][]		= "bill.bill_code = NULL";
			$tmp_dr[0][]		= "dr.dr_code = NULL";
			$tmpbill_item[0][] = "bill.bill_code = NULL";
			$tmpdr_item[0][]	= "dr.dr_code= NULL";
		} else if ($_filter_doc == "DR") {
			$tmp_bill[0][]		= "bill.bill_code = NULL";
			$tmp_turn[0][]		= "turn.turn_code = NULL";
			$tmpbill_item[0][] = "bill.bill_code = NULL";
			$tmpturn_item[0][] = "turn.turn_code= NULL";
		}
		
		if ($_last_category != 0) {
			$catList = executeSP("idc_getSubCategory", $_last_category);
			$tmp_bill[0][] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_turn[0][] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_dr[0][]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
		}
		
		if($_marketing != "all") {
			$tmp_bill[0][]	= "cus_responsibility_to = $_marketing";
			$tmp_turn[0][] = "cus_responsibility_to = $_marketing";
			$tmp_dr[0][] = "cus_responsibility_to = $_marketing";
			$tmpbill_item[0][] = "cus_responsibility_to = $_marketing";
			$tmpturn_item[0][] = "cus_responsibility_to = $_marketing";
			$tmpdr_item[0][]	= "cus_responsibility_to = $_marketing";
		}
		
		if($_dept != 'all') {
			$tmp_bill[0][] = "bill.bill_dept = '$_dept'";
			$tmp_turn[0][] = "turn.turn_dept = '$_dept'";
			$tmp_dr[0][]	= "dr.dr_dept = '$_dept'";
			$tmpbill_item[0][] = "bill.bill_dept = '$_dept'";
			$tmpturn_item[0][] = "turn.turn_dept = '$_dept'";
			$tmpdr_item[0][]	= "dr.dr_dept = '$_dept'";
		}
		
		if ($some_date != "") {
			$tmp_bill[0][] 	= "bill.bill_inv_date = DATE '$some_date'";
			$tmp_turn[0][] 	= "turn.turn_return_date = DATE '$some_date'";
			$tmp_dr[0][]		= "dr.dr_issued_date = DATE '$some_date'";
			$tmpbill_item[0][]	= "bill.bill_inv_date = DATE '$some_date'";
			$tmpturn_item[0][]	= "turn.turn_return_date = DATE '$some_date'";
			$tmpdr_item[0][]	= "dr.dr_issued_date = DATE '$some_date'";
		} else {
			$tmp_bill[0][]		= "bill.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_turn[0][]		= "turn.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_dr[0][]		= "dr.dr_issued_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpbill_item[0][] = "bill.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpturn_item[0][] = "turn.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpdr_item[0][]	= "dr.dr_issued_date BETWEEN DATE '$period_from' AND '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_bill[0][] 	= "bill_vat > 0";
			$tmp_turn[0][] 	= "turn.turn_vat > 0";
			$tmp_dr[0][]		= "dr_type_item = 1";
			$tmpbill_item[0][] = "bill_vat > 0";
			$tmpturn_item[0][] = "turn.turn_vat > 0"; 
			$tmpdr_item[0][]	= "dr_type_item = 1";
		} else if($_vat == 'vat-IO') {
			$tmp_bill[0][] 	= "bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmp_turn[0][] 	= "turn.turn_vat > 0";
			$tmp_dr[0][]		= "dr_type_item = 1";
			$tmpbill_item[0][] = "bill_vat > 0 AND bill_type_pajak = 'IO'";
			$tmpturn_item[0][] = "turn.turn_vat > 0";
			$tmpdr_item[0][]	= "dr_type_item = 1";
		} else if($_vat == 'vat-IP') {
			$tmp_bill[0][] 	= "bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmp_turn[0][] 	= "turn.turn_code = NULL";
			$tmp_dr[0][]		= "dr_code is null";
			$tmpbill_item[0][] = "bill_vat > 0 AND bill_type_pajak = 'IP'";
			$tmpturn_item[0][] = "turn.turn_code = NULL";
			$tmpdr_item[0][]	= "dr_code is null";  
		} else if ($_vat == 'non') {
			$tmp_bill[0][] 	= "bill_vat = 0";
			$tmp_turn[0][] 	= "turn.turn_vat = 0";
			$tmp_dr[0][]		= "dr_type_item = 1";
			$tmpbill_item[0][] = "bill_vat = 0";
			$tmpturn_item[0][] = "turn.turn_vat = 0";
			$tmpdr_item[0][]	= "dr_type_item = 2";
		}

	break;

}


$strWhere[0]	= implode(" AND ", $tmp_bill[0]);
$strWhere[1]	= implode(" AND ", $tmp_turn[0]);
$strWhere[2]	= implode(" AND ", $tmp_dr[0]);
$strWhere[3]	= implode(" AND ", $tmp_bill[1]);
$strWhere[4]	= implode(" AND ", $tmp_turn[1]);
$strWhere[5]	= implode(" AND ", $tmp_dr[1]);

$strWhereItem[0]	= implode(" AND ", $tmpbill_item[0]);
$strWhereItem[1]	= implode(" AND ", $tmpturn_item[0]);
$strWhereItem[2]	= implode(" AND ", $tmpdr_item[0]);
$strWhereItem[3]	= implode(" AND ", $tmpbill_item[1]);
$strWhereItem[4]	= implode(" AND ", $tmpturn_item[1]);
$strWhereItem[5]	= implode(" AND ", $tmpdr_item[1]);
?>