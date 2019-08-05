<?php
//SET WHERE PARAMETER
$tmp_bill	= array();
$tmp_turn	= array();
$tmp_dr		= array();
$tmpbill_item	= array();
$tmpturn_item	= array();
$tmpdr_item		= array();
$strWhere		= array();
$strWhereItem	= array();

switch (ZKP_URL) {
	// ALL ==============================================================================================================
	case "ALL":

		if($_order_by == "1") {			// INDOCORE
			$tmp_bill[0][]		= "bill_ordered_by = 1";
			$tmp_turn[0][]		= "turn_ordered_by = 1";
			$tmp_dr[0][]		= "dr_ordered_by = 1";
			$tmpbill_item[0][]	= "bill_ordered_by = 1";
			$tmpturn_item[0][]	= "turn_ordered_by = 1";
			$tmpdr_item[0][]	= "dr_ordered_by = 1";
			$tmp_bill[1][]		= "bill_code is null";
			$tmp_turn[1][]		= "turn_code is null";
			$tmp_dr[1][]		= "dr_code is null";
			$tmpbill_item[1][]	= "bill_code is null";
			$tmpturn_item[1][]	= "turn_code is null";
			$tmpdr_item[1][]	= "dr_code is null";
		} else if($_order_by == "2") {		// MEDIKUS
			$tmp_bill[0][]		= "bill_ordered_by = 2";
			$tmp_turn[0][]		= "turn_ordered_by = 2";
			$tmp_dr[0][]		= "dr_ordered_by = 2";
			$tmpbill_item[0][]	= "bill_ordered_by = 2";
			$tmpturn_item[0][]	= "turn_ordered_by = 2";
			$tmpdr_item[0][]	= "dr_ordered_by = 2";
			$tmp_bill[1][]		= "bill_code is null";
			$tmp_turn[1][]		= "turn_code is null";
			$tmp_dr[1][]		= "dr_code is null";
			$tmpbill_item[1][]	= "bill_code is null";
			$tmpturn_item[1][]	= "turn_code is null";
			$tmpdr_item[1][]	= "dr_code is null";
		} else if($_order_by == "3") {		// MEDISINDO
			$tmp_bill[0][]		= "bill_code is null";
			$tmp_turn[0][]		= "turn_code is null";
			$tmp_dr[0][]		= "dr_code is null";
			$tmpbill_item[0][]	= "bill_code is null";
			$tmpturn_item[0][]	= "turn_code is null";
			$tmpdr_item[0][]	= "dr_code is null";
			$tmp_bill[1][]		= "bill_ordered_by = 1";
			$tmp_turn[1][]		= "turn_ordered_by = 1";
			$tmp_dr[1][]		= "dr_ordered_by = 1";
			$tmpbill_item[1][]	= "bill_ordered_by = 1";
			$tmpturn_item[1][]	= "turn_ordered_by = 1";
			$tmpdr_item[1][]	= "dr_ordered_by = 1";
		} else if($_order_by == "4") {		// SAMUDIA
			$tmp_bill[0][]		= "bill_code is null";
			$tmp_turn[0][]		= "turn_code is null";
			$tmp_dr[0][]		= "dr_code is null";
			$tmpbill_item[0][]	= "bill_code is null";
			$tmpturn_item[0][]	= "turn_code is null";
			$tmpdr_item[0][]	= "dr_code is null";
			$tmp_bill[1][]		= "bill_ordered_by = 2";
			$tmp_turn[1][]		= "turn_ordered_by = 2";
			$tmp_dr[1][]		= "dr_ordered_by = 2";
			$tmpbill_item[1][]	= "bill_ordered_by = 2";
			$tmpturn_item[1][]	= "turn_ordered_by = 2";
			$tmpdr_item[1][]	= "dr_ordered_by = 2";
		} else if($_order_by == "5") {		// INDOCORE & MEDIKUS
			$tmp_bill[1][]		= "bill_code is null";
			$tmp_turn[1][]		= "turn_code is null";
			$tmp_dr[1][]		= "dr_code is null";
			$tmpbill_item[1][]	= "bill_code is null";
			$tmpturn_item[1][]	= "turn_code is null";
			$tmpdr_item[1][]	= "dr_code is null";
		} else if($_order_by == "6") {		// MEDISINDO & SAMUDIA
			$tmp_bill[0][]		= "bill_code is null";
			$tmp_turn[0][]		= "turn_code is null";
			$tmp_dr[0][]		= "dr_code is null";
			$tmpbill_item[0][]	= "bill_code is null";
			$tmpturn_item[0][]	= "turn_code is null";
			$tmpdr_item[0][]	= "dr_code is null";
		}

		if($_chk_company == 'off') {
			$tmp_bill[0][]		= "bill_ship_to NOT IN ('6IDC', '0MSD', '0SMD')";
			$tmp_turn[0][]		= "turn_ship_to NOT IN ('6IDC', '0MSD', '0SMD')";
			$tmp_dr[0][]		= "dr_ship_to NOT IN ('6IDC', '0MSD', '0SMD')";
			$tmp_bill[1][]		= "bill_ship_to NOT IN ('6IDC', '0MSD', '0SMD')";
			$tmp_turn[1][]		= "turn_ship_to NOT IN ('6IDC', '0MSD', '0SMD')";
			$tmp_dr[1][]		= "dr_ship_to NOT IN ('6IDC', '0MSD', '0SMD')";
			$tmpbill_item[0][]	= "bill_ship_to NOT IN ('6IDC', '0MSD', '0SMD')";
			$tmpturn_item[0][]	= "turn_ship_to NOT IN ('6IDC', '0MSD', '0SMD')";
			$tmpdr_item[0][]	= "dr_ship_to NOT IN ('6IDC', '0MSD', '0SMD')";
			$tmpbill_item[1][]	= "bill_ship_to NOT IN ('6IDC', '0MSD', '0SMD')";
			$tmpturn_item[1][]	= "turn_ship_to NOT IN ('6IDC', '0MSD', '0SMD')";
			$tmpdr_item[1][]	= "dr_ship_to NOT IN ('6IDC', '0MSD', '0SMD')";
		}

		if ($_filter_doc == "I") {
			$tmp_turn[0][]		= "b.turn_code = NULL";
			$tmp_dr[0][]			= "b.dr_code = NULL";
			$tmpturn_item[0][]	= "b.turn_code= NULL";
			$tmpdr_item[0][]		= "b.dr_code= NULL";
			$tmp_turn[1][]		= "b.turn_code = NULL";
			$tmp_dr[1][]			= "b.dr_code = NULL";
			$tmpturn_item[1][]	= "b.turn_code= NULL";
			$tmpdr_item[1][]		= "b.dr_code= NULL";
		} else if ($_filter_doc == "R") {
			$tmp_bill[0][]		= "b.bill_code = NULL";
			$tmp_dr[0][]			= "b.dr_code = NULL";
			$tmpbill_item[0][]	= "b.bill_code = NULL";
			$tmpdr_item[0][]		= "b.dr_code= NULL";
			$tmp_bill[1][]		= "b.bill_code = NULL";
			$tmp_dr[1][]			= "b.dr_code = NULL";
			$tmpbill_item[1][]	= "b.bill_code = NULL";
			$tmpdr_item[1][]		= "b.dr_code= NULL";
		} else if ($_filter_doc == "DR") {
			$tmp_bill[0][]		= "b.bill_code = NULL";
			$tmp_turn[0][]		= "b.turn_code = NULL";
			$tmpbill_item[0][]	= "b.bill_code = NULL";
			$tmpturn_item[0][]	= "b.turn_code= NULL";
			$tmp_bill[1][]		= "b.bill_code = NULL";
			$tmp_turn[1][]		= "b.turn_code = NULL";
			$tmpbill_item[1][]	= "b.bill_code = NULL";
			$tmpturn_item[1][]	= "b.turn_code= NULL";
		}
		
		if ($_last_category != 0) {
			$catList = executeSP("idc_getSubCategory", $_last_category);
			$tmp_bill[0][]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_turn[0][]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_dr[0][]		= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$catList = executeSP("med_getSubCategory", $_last_category);
			$tmp_bill[1][]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_turn[1][]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_dr[1][]		= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
		}
		
		if($_marketing != "all") {
			$tmp_bill[0][]		= "cus_responsibility_to = $_marketing";
			$tmp_turn[0][]		= "cus_responsibility_to = $_marketing";
			$tmp_dr[0][]		= "cus_responsibility_to = $_marketing";
			$tmpbill_item[0][]	= "cus_responsibility_to = $_marketing";
			$tmpturn_item[0][]	= "cus_responsibility_to = $_marketing";
			$tmpdr_item[0][]		= "cus_responsibility_to = $_marketing";
			$tmp_bill[1][]		= "cus_responsibility_to = $_marketing";
			$tmp_turn[1][]		= "cus_responsibility_to = $_marketing";
			$tmp_dr[1][]			= "cus_responsibility_to = $_marketing";
			$tmpbill_item[1][]	= "cus_responsibility_to = $_marketing";
			$tmpturn_item[1][]	= "cus_responsibility_to = $_marketing";
			$tmpdr_item[1][]		= "cus_responsibility_to = $_marketing";
		}
		
		if($_dept != 'all') {
			$tmp_bill[0][]		= "b.bill_dept = '$_dept'";
			$tmp_turn[0][]		= "b.turn_dept = '$_dept'";
			$tmp_dr[0][]			= "b.dr_dept = '$_dept'";
			$tmpbill_item[0][]	= "b.bill_dept = '$_dept'";
			$tmpturn_item[0][]	= "b.turn_dept = '$_dept'";
			$tmpdr_item[0][]		= "b.dr_dept = '$_dept'";
			$tmp_bill[1][]		= "b.bill_dept = '$_dept'";
			$tmp_turn[1][]		= "b.turn_dept = '$_dept'";
			$tmp_dr[1][]		= "b.dr_dept = '$_dept'";
			$tmpbill_item[1][]	= "b.bill_dept = '$_dept'";
			$tmpturn_item[1][]	= "b.turn_dept = '$_dept'";
			$tmpdr_item[1][]		= "b.dr_dept = '$_dept'";
		}
		
		if ($some_date != "") {
			$tmp_bill[0][]		= "b.bill_inv_date = DATE '$some_date'";
			$tmp_turn[0][] 		= "b.turn_return_date = DATE '$some_date'";
			$tmp_dr[0][]			= "b.dr_issued_date = DATE '$some_date'";
			$tmpbill_item[0][]	= "b.bill_inv_date = DATE '$some_date'";
			$tmpturn_item[0][]	= "b.turn_return_date = DATE '$some_date'";
			$tmpdr_item[0][]		= "b.dr_issued_date = DATE '$some_date'";
			$tmp_bill[1][] 		= "b.bill_inv_date = DATE '$some_date'";
			$tmp_turn[1][] 		= "b.turn_return_date = DATE '$some_date'";
			$tmp_dr[1][]			= "b.dr_issued_date = DATE '$some_date'";
			$tmpbill_item[1][]	= "b.bill_inv_date = DATE '$some_date'";
			$tmpturn_item[1][]	= "b.turn_return_date = DATE '$some_date'";
			$tmpdr_item[1][]		= "b.dr_issued_date = DATE '$some_date'";
		} else {
			$tmp_bill[0][]		= "b.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_turn[0][]		= "b.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_dr[0][]			= "b.dr_issued_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpbill_item[0][]	= "b.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpturn_item[0][]	= "b.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpdr_item[0][]		= "b.dr_issued_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_bill[1][]		= "b.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_turn[1][]		= "b.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_dr[1][]			= "b.dr_issued_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpbill_item[1][]	= "b.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpturn_item[1][]	= "b.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpdr_item[1][]		= "b.dr_issued_date BETWEEN DATE '$period_from' AND '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_bill[0][]		= "b.bill_vat > 0";
			$tmp_turn[0][] 		= "b.turn_vat > 0";
			$tmp_dr[0][]		= "dr_type_item = 1";
			$tmpbill_item[0][]	= "b.bill_vat > 0";
			$tmpturn_item[0][]	= "b.turn_vat > 0"; 
			$tmpdr_item[0][]		= "dr_type_item = 1";
			$tmp_bill[1][]		= "b.bill_vat > 0";
			$tmp_turn[1][] 		= "b.turn_vat > 0";
			$tmp_dr[1][]			= "dr_type_item = 1";
			$tmpbill_item[1][]	= "b.bill_vat > 0";
			$tmpturn_item[1][]	= "b.turn_vat > 0"; 
			$tmpdr_item[1][]		= "dr_type_item = 1";
		} else if($_vat == 'vat-IO') {
			$tmp_bill[0][]		= "b.bill_vat > 0 AND b.bill_type_pajak = 'IO'";
			$tmp_turn[0][] 		= "b.turn_vat > 0";
			$tmp_dr[0][]			= "dr_type_item = 1";
			$tmpbill_item[0][]	= "b.bill_vat > 0 AND b.bill_type_pajak = 'IO'";
			$tmpturn_item[0][]	= "b.turn_vat > 0";
			$tmpdr_item[0][]		= "dr_type_item = 1";
			$tmp_bill[1][] 		= "b.bill_vat > 0 AND b.bill_type_pajak = 'IO'";
			$tmp_turn[1][] 		= "b.turn_vat > 0";
			$tmp_dr[1][]			= "dr_type_item = 1";
			$tmpbill_item[1][]	= "b.bill_vat > 0 AND b.bill_type_pajak = 'IO'";
			$tmpturn_item[1][]	= "b.turn_vat > 0";
			$tmpdr_item[1][]		= "dr_type_item = 1";
		}else if($_vat == 'vat-IP') {
			$tmp_bill[0][] 		= "b.bill_vat > 0 AND b.bill_type_pajak = 'IP'";
			$tmp_turn[0][] 		= "b.turn_code = NULL";
			$tmp_dr[0][]			= "dr_code is null";
			$tmpbill_item[0][]	= "b.bill_vat > 0 AND b.bill_type_pajak = 'IP'";
			$tmpturn_item[0][]	= "b.turn_code = NULL";
			$tmpdr_item[0][]		= "dr_code is null";  
			$tmp_bill[1][]		= "b.bill_vat > 0 AND b.bill_type_pajak = 'IP'";
			$tmp_turn[1][] 		= "b.turn_code = NULL";
			$tmp_dr[1][]			= "dr_code is null";
			$tmpbill_item[1][]	= "b.bill_vat > 0 AND b.bill_type_pajak = 'IP'";
			$tmpturn_item[1][]	= "b.turn_code = NULL";
			$tmpdr_item[1][]		= "dr_code is null";  
		} else if ($_vat == 'non') {
			$tmp_bill[0][] 		= "b.bill_vat = 0";
			$tmp_turn[0][] 		= "b.turn_vat = 0";
			$tmp_dr[0][]			= "dr_type_item = 2";
			$tmpbill_item[0][]	= "b.bill_vat = 0";
			$tmpturn_item[0][]	= "b.turn_vat = 0";
			$tmpdr_item[0][]		= "dr_type_item = 2";
			$tmp_bill[1][]		= "b.bill_vat = 0";
			$tmp_turn[1][] 		= "b.turn_vat = 0";
			$tmp_dr[1][]			= "dr_type_item = 2";
			$tmpbill_item[1][]	= "b.bill_vat = 0";
			$tmpturn_item[1][]	= "b.turn_vat = 0";
			$tmpdr_item[1][]		= "dr_type_item = 2";
		}
		
		if($cboSearchType != '' && $txtSearch != '') {
			$type = array("byCity"=>"cus_city");
			$tmp_bill[0][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmp_turn[0][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmp_dr[0][] 		= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmpbill_item[0][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmpturn_item[0][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmpdr_item[0][]		= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmp_bill[1][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmp_turn[1][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmp_dr[1][] 		= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmpbill_item[1][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmpturn_item[1][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmpdr_item[1][]		= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$get[0] = "$cboSearchType=$txtSearch";
		}


	break;

	// IDC ==============================================================================================================
	case "IDC":

		if ($_filter_doc == "I") {
			$tmp_turn[0][]		= "b.turn_code = NULL";
			$tmp_dr[0][]			= "b.dr_code = NULL";
			$tmpturn_item[0][]	= "b.turn_code= NULL";
			$tmpdr_item[0][]		= "b.dr_code= NULL";
		} else if ($_filter_doc == "R") {
			$tmp_bill[0][]		= "b.bill_code = NULL";
			$tmp_dr[0][]			= "b.dr_code = NULL";
			$tmpbill_item[0][]	= "b.bill_code = NULL";
			$tmpdr_item[0][]		= "b.dr_code= NULL";
		} else if ($_filter_doc == "DR") {
			$tmp_bill[0][]		= "b.bill_code = NULL";
			$tmp_turn[0][]		= "b.turn_code = NULL";
			$tmpbill_item[0][]	= "b.bill_code = NULL";
			$tmpturn_item[0][]	= "b.turn_code= NULL";
		}
		
		if ($_last_category != 0) {
			$catList = executeSP("idc_getSubCategory", $_last_category);
			$tmp_bill[0][]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_turn[0][]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_dr[0][]		= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
		}
		
		if($_marketing != "all") {
			$tmp_bill[0][]		= "cus_responsibility_to = $_marketing";
			$tmp_turn[0][]		= "cus_responsibility_to = $_marketing";
			$tmp_dr[0][]			= "cus_responsibility_to = $_marketing";
			$tmpbill_item[0][]	= "cus_responsibility_to = $_marketing";
			$tmpturn_item[0][]	= "cus_responsibility_to = $_marketing";
			$tmpdr_item[0][]		= "cus_responsibility_to = $_marketing";
		}
		
		if($_dept != 'all') {
			$tmp_bill[0][]		= "b.bill_dept = '$_dept'";
			$tmp_turn[0][]		= "b.turn_dept = '$_dept'";
			$tmp_dr[0][]			= "b.dr_dept = '$_dept'";
			$tmpbill_item[0][]	= "b.bill_dept = '$_dept'";
			$tmpturn_item[0][]	= "b.turn_dept = '$_dept'";
			$tmpdr_item[0][]		= "b.dr_dept = '$_dept'";
		}
		
		if ($some_date != "") {
			$tmp_bill[0][] 		= "b.bill_inv_date = DATE '$some_date'";
			$tmp_turn[0][] 		= "b.turn_return_date = DATE '$some_date'";
			$tmp_dr[0][]			= "b.dr_issued_date = DATE '$some_date'";
			$tmpbill_item[0][]	= "b.bill_inv_date = DATE '$some_date'";
			$tmpturn_item[0][]	= "b.turn_return_date = DATE '$some_date'";
			$tmpdr_item[0][]		= "b.dr_issued_date = DATE '$some_date'";
		} else {
			$tmp_bill[0][]		= "b.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_turn[0][]		= "b.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_dr[0][]			= "b.dr_issued_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpbill_item[0][]	= "b.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpturn_item[0][]	= "b.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpdr_item[0][]		= "b.dr_issued_date BETWEEN DATE '$period_from' AND '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_bill[0][]		= "b.bill_vat > 0";
			$tmp_turn[0][] 		= "b.turn_vat > 0";
			$tmp_dr[0][]			= "dr_type_item = 1";
			$tmpbill_item[0][]	= "b.bill_vat > 0";
			$tmpturn_item[0][]	= "b.turn_vat > 0"; 
			$tmpdr_item[0][]		= "dr_type_item = 1";
		} else if($_vat == 'vat-IO') {
			$tmp_bill[0][] 		= "b.bill_vat > 0 AND b.bill_type_pajak = 'IO'";
			$tmp_turn[0][] 		= "b.turn_vat > 0";
			$tmp_dr[0][]			= "dr_type_item = 1";
			$tmpbill_item[0][]	= "b.bill_vat > 0 AND b.bill_type_pajak = 'IO'";
			$tmpturn_item[0][]	= "b.turn_vat > 0";
			$tmpdr_item[0][]		= "dr_type_item = 1";
		}else if($_vat == 'vat-IP') {
			$tmp_bill[0][] 		= "b.bill_vat > 0 AND b.bill_type_pajak = 'IP'";
			$tmp_turn[0][] 		= "b.turn_code = NULL";
			$tmp_dr[0][]			= "dr_code is null";
			$tmpbill_item[0][]	= "b.bill_vat > 0 AND b.bill_type_pajak = 'IP'";
			$tmpturn_item[0][]	= "b.turn_code = NULL";
			$tmpdr_item[0][]		= "dr_code is null";  
		} else if ($_vat == 'non') {
			$tmp_bill[0][] 		= "b.bill_vat = 0";
			$tmp_turn[0][] 		= "b.turn_vat = 0";
			$tmp_dr[0][]			= "dr_type_item = 2";
			$tmpbill_item[0][]	= "b.bill_vat = 0";
			$tmpturn_item[0][]	= "b.turn_vat = 0";
			$tmpdr_item[0][]		= "dr_type_item = 2";
		}
		
		if($cboSearchType != '' && $txtSearch != '') {
			$type = array("byCity"=>"cus_city");
			$tmp_bill[0][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmp_turn[0][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmp_dr[0][] 		= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmpbill_item[0][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmpturn_item[0][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmpdr_item[0][]		= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$get[0] = "$cboSearchType=$txtSearch";
		}

	break;

	// MED ==============================================================================================================
	case "MED":

		if ($_filter_doc == "I") {
			$tmp_turn[1]		= "b.turn_code = NULL";
			$tmp_dr[1]			= "b.dr_code = NULL";
			$tmpturn_item[1]	= "b.turn_code= NULL";
			$tmpdr_item[1]		= "b.dr_code= NULL";
		} else if ($_filter_doc == "R") {
			$tmp_bill[1]		= "b.bill_code = NULL";
			$tmp_dr[1]			= "b.dr_code = NULL";
			$tmpbill_item[1]	= "b.bill_code = NULL";
			$tmpdr_item[1]		= "b.dr_code= NULL";
		} else if ($_filter_doc == "DR") {
			$tmp_bill[1]		= "b.bill_code = NULL";
			$tmp_turn[1]		= "b.turn_code = NULL";
			$tmpbill_item[1]	= "b.bill_code = NULL";
			$tmpturn_item[1]	= "b.turn_code= NULL";
		}
		
		if ($_last_category != 0) {
			$catList = executeSP("idc_getSubCategory", $_last_category);
			$tmp_bill[1]	= "icat.icat_midx IN (" . (empty($catList[1]) ? "0" : $catList[1]) . ")";
			$tmp_turn[1]	= "icat.icat_midx IN (" . (empty($catList[1]) ? "0" : $catList[1]) . ")";
			$tmp_dr[1]		= "icat.icat_midx IN (" . (empty($catList[1]) ? "0" : $catList[1]) . ")";
		}
		
		if($_marketing != "all") {
			$tmp_bill[1]		= "cus_responsibility_to = $_marketing";
			$tmp_turn[1]		= "cus_responsibility_to = $_marketing";
			$tmp_dr[1]			= "cus_responsibility_to = $_marketing";
			$tmpbill_item[1]	= "cus_responsibility_to = $_marketing";
			$tmpturn_item[1]	= "cus_responsibility_to = $_marketing";
			$tmpdr_item[1]		= "cus_responsibility_to = $_marketing";
		}
		
		if($_dept != 'all') {
			$tmp_bill[1]		= "b.bill_dept = '$_dept'";
			$tmp_turn[1]		= "b.turn_dept = '$_dept'";
			$tmp_dr[1]			= "b.dr_dept = '$_dept'";
			$tmpbill_item[1]	= "b.bill_dept = '$_dept'";
			$tmpturn_item[1]	= "b.turn_dept = '$_dept'";
			$tmpdr_item[1]		= "b.dr_dept = '$_dept'";
		}
		
		if ($some_date != "") {
			$tmp_bill[1] 		= "b.bill_inv_date = DATE '$some_date'";
			$tmp_turn[1] 		= "b.turn_return_date = DATE '$some_date'";
			$tmp_dr[1]			= "b.dr_issued_date = DATE '$some_date'";
			$tmpbill_item[1]	= "b.bill_inv_date = DATE '$some_date'";
			$tmpturn_item[1]	= "b.turn_return_date = DATE '$some_date'";
			$tmpdr_item[1]		= "b.dr_issued_date = DATE '$some_date'";
		} else {
			$tmp_bill[1]		= "b.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_turn[1]		= "b.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_dr[1]			= "b.dr_issued_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpbill_item[1]	= "b.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpturn_item[1]	= "b.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpdr_item[1]		= "b.dr_issued_date BETWEEN DATE '$period_from' AND '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_bill[1]		= "b.bill_vat > 0";
			$tmp_turn[1] 		= "b.turn_vat > 0";
			$tmp_dr[1]			= "dr_type_item = 1";
			$tmpbill_item[1]	= "b.bill_vat > 0";
			$tmpturn_item[1]	= "b.turn_vat > 0"; 
			$tmpdr_item[1]		= "dr_type_item = 1";
		} else if($_vat == 'vat-IO') {
			$tmp_bill[1] 		= "b.bill_vat > 0 AND b.bill_type_pajak = 'IO'";
			$tmp_turn[1] 		= "b.turn_vat > 0";
			$tmp_dr[1]			= "dr_type_item = 1";
			$tmpbill_item[1]	= "b.bill_vat > 0 AND b.bill_type_pajak = 'IO'";
			$tmpturn_item[1]	= "b.turn_vat > 0";
			$tmpdr_item[1]		= "dr_type_item = 1";
		}else if($_vat == 'vat-IP') {
			$tmp_bill[1] 		= "b.bill_vat > 0 AND b.bill_type_pajak = 'IP'";
			$tmp_turn[1] 		= "b.turn_code = NULL";
			$tmp_dr[1]			= "dr_code is null";
			$tmpbill_item[1]	= "b.bill_vat > 0 AND b.bill_type_pajak = 'IP'";
			$tmpturn_item[1]	= "b.turn_code = NULL";
			$tmpdr_item[1]		= "dr_code is null";  
		} else if ($_vat == 'non') {
			$tmp_bill[1] 		= "b.bill_vat = 0";
			$tmp_turn[1] 		= "b.turn_vat = 0";
			$tmp_dr[1]			= "dr_type_item = 2";
			$tmpbill_item[1]	= "b.bill_vat = 0";
			$tmpturn_item[1]	= "b.turn_vat = 0";
			$tmpdr_item[1]		= "dr_type_item = 2";
		}
		
		if($cboSearchType != '' && $txtSearch != '') {
			$type = array("byCity"=>"cus_city");
			$tmp_bill[1]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmp_turn[1]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmp_dr[1] 		= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmpbill_item[1]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmpturn_item[1]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmpdr_item[1]		= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$get[1] = "$cboSearchType=$txtSearch";
		}


	break;

	// MEP ==============================================================================================================
	case "MEP":

		if ($_filter_doc == "I") {
			$tmp_turn[0][]		= "b.turn_code = NULL";
			$tmp_dr[0][]			= "b.dr_code = NULL";
			$tmpturn_item[0][]	= "b.turn_code= NULL";
			$tmpdr_item[0][]		= "b.dr_code= NULL";
		} else if ($_filter_doc == "R") {
			$tmp_bill[0][]		= "b.bill_code = NULL";
			$tmp_dr[0][]			= "b.dr_code = NULL";
			$tmpbill_item[0][]	= "b.bill_code = NULL";
			$tmpdr_item[0][]		= "b.dr_code= NULL";
		} else if ($_filter_doc == "DR") {
			$tmp_bill[0][]		= "b.bill_code = NULL";
			$tmp_turn[0][]		= "b.turn_code = NULL";
			$tmpbill_item[0][]	= "b.bill_code = NULL";
			$tmpturn_item[0][]	= "b.turn_code= NULL";
		}
		
		if ($_last_category != 0) {
			$catList = executeSP("idc_getSubCategory", $_last_category);
			$tmp_bill[0][]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_turn[0][]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_dr[0][]		= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
		}
		
		if($_marketing != "all") {
			$tmp_bill[0][]		= "cus_responsibility_to = $_marketing";
			$tmp_turn[0][]		= "cus_responsibility_to = $_marketing";
			$tmp_dr[0][]			= "cus_responsibility_to = $_marketing";
			$tmpbill_item[0][]	= "cus_responsibility_to = $_marketing";
			$tmpturn_item[0][]	= "cus_responsibility_to = $_marketing";
			$tmpdr_item[0][]		= "cus_responsibility_to = $_marketing";
		}
		
		if($_dept != 'all') {
			$tmp_bill[0][]		= "b.bill_dept = '$_dept'";
			$tmp_turn[0][]		= "b.turn_dept = '$_dept'";
			$tmp_dr[0][]			= "b.dr_dept = '$_dept'";
			$tmpbill_item[0][]	= "b.bill_dept = '$_dept'";
			$tmpturn_item[0][]	= "b.turn_dept = '$_dept'";
			$tmpdr_item[0][]		= "b.dr_dept = '$_dept'";
		}
		
		if ($some_date != "") {
			$tmp_bill[0][] 		= "b.bill_inv_date = DATE '$some_date'";
			$tmp_turn[0][] 		= "b.turn_return_date = DATE '$some_date'";
			$tmp_dr[0][]			= "b.dr_issued_date = DATE '$some_date'";
			$tmpbill_item[0][]	= "b.bill_inv_date = DATE '$some_date'";
			$tmpturn_item[0][]	= "b.turn_return_date = DATE '$some_date'";
			$tmpdr_item[0][]		= "b.dr_issued_date = DATE '$some_date'";
		} else {
			$tmp_bill[0][]		= "b.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_turn[0][]		= "b.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmp_dr[0][]			= "b.dr_issued_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpbill_item[0][]	= "b.bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpturn_item[0][]	= "b.turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
			$tmpdr_item[0][]		= "b.dr_issued_date BETWEEN DATE '$period_from' AND '$period_to'";
		}
		
		if($_vat == 'vat') {
			$tmp_bill[0][]		= "b.bill_vat > 0";
			$tmp_turn[0][] 		= "b.turn_vat > 0";
			$tmp_dr[0][]			= "dr_type_item = 1";
			$tmpbill_item[0][]	= "b.bill_vat > 0";
			$tmpturn_item[0][]	= "b.turn_vat > 0"; 
			$tmpdr_item[0][]		= "dr_type_item = 1";
		} else if($_vat == 'vat-IO') {
			$tmp_bill[0][] 		= "b.bill_vat > 0 AND b.bill_type_pajak = 'IO'";
			$tmp_turn[0][] 		= "b.turn_vat > 0";
			$tmp_dr[0][]			= "dr_type_item = 1";
			$tmpbill_item[0][]	= "b.bill_vat > 0 AND b.bill_type_pajak = 'IO'";
			$tmpturn_item[0][]	= "b.turn_vat > 0";
			$tmpdr_item[0][]		= "dr_type_item = 1";
		}else if($_vat == 'vat-IP') {
			$tmp_bill[0][] 		= "b.bill_vat > 0 AND b.bill_type_pajak = 'IP'";
			$tmp_turn[0][] 		= "b.turn_code = NULL";
			$tmp_dr[0][]			= "dr_code is null";
			$tmpbill_item[0][]	= "b.bill_vat > 0 AND b.bill_type_pajak = 'IP'";
			$tmpturn_item[0][]	= "b.turn_code = NULL";
			$tmpdr_item[0][]		= "dr_code is null";  
		} else if ($_vat == 'non') {
			$tmp_bill[0][] 		= "b.bill_vat = 0";
			$tmp_turn[0][] 		= "b.turn_vat = 0";
			$tmp_dr[0][]			= "dr_type_item = 2";
			$tmpbill_item[0][]	= "b.bill_vat = 0";
			$tmpturn_item[0][]	= "b.turn_vat = 0";
			$tmpdr_item[0][]		= "dr_type_item = 2";
		}
		
		if($cboSearchType != '' && $txtSearch != '') {
			$type = array("byCity"=>"cus_city");
			$tmp_bill[0][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmp_turn[0][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmp_dr[0][] 		= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmpbill_item[0][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmpturn_item[0][]	= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$tmpdr_item[0][]		= $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
			$get[0] = "$cboSearchType=$txtSearch";
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