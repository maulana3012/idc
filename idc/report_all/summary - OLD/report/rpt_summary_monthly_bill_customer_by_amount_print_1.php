<?php
$tmp_bill	= array();
$tmp_sl		= array();
$tmp_turn	= array();
$tmp_bill_month	= array();
$tmp_sl_month	= array();
$tmp_turn_month	= array();
$strWhere		= array();
$strWhereMonth	= array();


switch (ZKP_URL) {
	// ALL ==============================================================================================================
	case "ALL":

/*
if(ZKP_URL == 'ALL') {
	if($_order_by != 'all'){
		$tmp_bill[]			= "bill_ordered_by = $_order_by";
		$tmp_bill_month[]	= "bill_ordered_by = $_order_by";
		$tmp_turn[]			= "turn_ordered_by = $_order_by";
		$tmp_turn_month[]	= "turn_ordered_by = $_order_by";
	}
} else {
	$tmp_bill[]			= "bill_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill_code,'billing')";
	$tmp_bill_month[]	= "bill_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill_code,'billing')";
	$tmp_turn[]			= "turn_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', turn_code,'billing_return')";
	$tmp_turn_month[]	= "turn_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', turn_code,'billing_return')";
	$tmp_sl[] 			= "bill_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill_code,'billing')";
	$tmp_sl_month[] 	= "bill_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill_code,'billing')";
}
*/

		if($_order_by == "1") {				// INDOCORE
			$tmp_bill[0][]	= "bill_ordered_by = 1";
			$tmp_sl[0][]	= "bill_ordered_by = 1";
			$tmp_turn[0][]	= "turn_ordered_by = 1";
			$tmp_bill_month[0][]	= "bill_ordered_by = 1";
			$tmp_sl_month[0][]		= "bill_ordered_by = 1";
			$tmp_turn_month[0][]	= "turn_ordered_by = 1";
			$tmp_bill[1][]	= "bill_code is null";
			$tmp_sl[1][]	= "bill_code is null";
			$tmp_turn[1][]	= "turn_code is null";
			$tmp_bill_month[1][]	= "bill_code is null";
			$tmp_sl_month[1][]		= "bill_code is null";
			$tmp_turn_month[1][]	= "turn_code is null";
		} else if($_order_by == "2") {		// MEDISINDO
			$tmp_bill[0][]	= "bill_code is null";
			$tmp_sl[0][]	= "bill_code is null";
			$tmp_turn[0][]	= "turn_code is null";
			$tmp_bill_month[0][]	= "bill_code is null";
			$tmp_sl_month[0][]		= "bill_code is null";
			$tmp_turn_month[0][]	= "turn_code is null";
		} else if($_order_by == "3") {		// MEDIKUS
			$tmp_bill[0][]	= "bill_ordered_by = 2";
			$tmp_sl[0][]	= "bill_ordered_by = 2";
			$tmp_turn[0][]	= "turn_ordered_by = 2";
			$tmp_bill_month[0][]	= "bill_ordered_by = 2";
			$tmp_sl_month[0][]		= "bill_ordered_by = 2";
			$tmp_turn_month[0][]	= "turn_ordered_by = 2";
			$tmp_bill[1][]	= "bill_code is null";
			$tmp_sl[1][]	= "bill_code is null";
			$tmp_turn[1][]	= "turn_code is null";
			$tmp_bill_month[1][]	= "bill_code is null";
			$tmp_sl_month[1][]		= "bill_code is null";
			$tmp_turn_month[1][]	= "turn_code is null";
		} else if($_order_by == "4") {		// INDOCORE & MEDIKUS
			$tmp_bill[1][]	= "bill_code is null";
			$tmp_sl[1][]	= "bill_code is null";
			$tmp_turn[1][]	= "turn_code is null";
			$tmp_bill_month[1][]	= "bill_code is null";
			$tmp_sl_month[1][]		= "bill_code is null";
			$tmp_turn_month[1][]	= "turn_code is null";
		}

		if ($_last_category != 0) {
			$catList = executeSP("idc_getSubCategory", $_last_category);
			$tmp_bill[0][] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_sl[0][] 	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_turn[0][] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_bill_month[0][] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_sl_month[0][]	  = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_turn_month[0][] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$catList = executeSP("med_getSubCategory", $_last_category);
			$tmp_bill[1][] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_sl[1][] 	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_turn[1][] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_bill_month[1][] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_sl_month[1][]	  = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_turn_month[1][] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
		}
		
		if ($_filter_doc == "I") {
			$tmp_turn[0][] = "turn_code is null";
			$tmp_turn_month[0][] = "turn_code is null";
			$tmp_turn[1][] = "turn_code is null";
			$tmp_turn_month[1][] = "turn_code is null";
		} else if ($_filter_doc == "R") {
			$tmp_bill[0][] = "bill_code is null";
			$tmp_sl[0][]	= "bill_code is null";
			$tmp_bill_month[0][] = "bill_code is null";
			$tmp_sl_month[0][]	  = "bill_code is null";
			$tmp_bill[1][] = "bill_code is null";
			$tmp_sl[1][]	= "bill_code is null";
			$tmp_bill_month[1][] = "bill_code is null";
			$tmp_sl_month[1][]	  = "bill_code is null";
		}
		
		if($_filter_vat == 'vat') {
			$tmp_bill[0][] = "bill_vat > 0";
			$tmp_sl[0][] = "bill_vat > 0";
			$tmp_turn[0][] = "turn_vat > 0";
			$tmp_bill_month[0][] = "bill_vat > 0";
			$tmp_sl_month[0][] = "bill_vat > 0";
			$tmp_turn_month[0][] = "turn_vat > 0";
			$tmp_bill[1][] = "bill_vat > 0";
			$tmp_sl[1][] = "bill_vat > 0";
			$tmp_turn[1][] = "turn_vat > 0";
			$tmp_bill_month[1][] = "bill_vat > 0";
			$tmp_sl_month[1][] = "bill_vat > 0";
			$tmp_turn_month[1][] = "turn_vat > 0";
		} else if ($_filter_vat == 'non') {
			$tmp_bill[0][] = "bill_vat = 0";
			$tmp_sl[0][] = "bill_vat = 0";
			$tmp_turn[0][] = "turn_vat = 0";
			$tmp_bill_month[0][] = "bill_vat = 0";
			$tmp_sl_month[0][] = "bill_vat = 0";
			$tmp_turn_month[0][] = "turn_vat = 0";
			$tmp_bill[1][] = "bill_vat = 0";
			$tmp_sl[1][] = "bill_vat = 0";
			$tmp_turn[1][] = "turn_vat = 0";
			$tmp_bill_month[1][] = "bill_vat = 0";
			$tmp_sl_month[1][] = "bill_vat = 0";
			$tmp_turn_month[1][] = "turn_vat = 0";
		}
		
		if($_filter_dept != 'all') {
			$tmp_bill[0][] = "bill_dept = '$_filter_dept'";
			$tmp_sl[0][] = "bill_dept = '$_filter_dept'";
			$tmp_turn[0][] = "turn_dept = '$_filter_dept'";
			$tmp_bill_month[0][] = "bill_dept = '$_filter_dept'";
			$tmp_sl_month[0][] = "bill_dept = '$_filter_dept'";
			$tmp_turn_month[0][] = "turn_dept = '$_filter_dept'";
			$tmp_bill[1][] = "bill_dept = '$_filter_dept'";
			$tmp_sl[1][] = "bill_dept = '$_filter_dept'";
			$tmp_turn[1][] = "turn_dept = '$_filter_dept'";
			$tmp_bill_month[1][] = "bill_dept = '$_filter_dept'";
			$tmp_sl_month[1][] = "bill_dept = '$_filter_dept'";
			$tmp_turn_month[1][] = "turn_dept = '$_filter_dept'";
		}
		
		$tmp_bill[0][]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to' AND bill_type_billing in (1,2)";
		$tmp_sl[0][]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to' AND bill_type_billing = 3";
		$tmp_turn[0][]	= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_bill_month[0][]	= "bill_type_billing in (1,2)";
		$tmp_sl_month[0][]		= "bill_type_billing = 3";
		$tmp_turn_month[0][]	= "turn_return_condition != 1";
		$tmp_bill[1][]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to' AND bill_type_billing in (1,2)";
		$tmp_sl[1][]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to' AND bill_type_billing = 3";
		$tmp_turn[1][]	= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_bill_month[1][]	= "bill_type_billing in (1,2)";
		$tmp_sl_month[1][]		= "bill_type_billing = 3";
		$tmp_turn_month[1][]	= "turn_return_condition != 1";

	break;

	// IDC ==============================================================================================================
	case "IDC":

		if ($_last_category != 0) {
			$catList = executeSP("idc_getSubCategory", $_last_category);
			$tmp_bill[0][] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_sl[0][] 	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_turn[0][] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_bill_month[0][] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_sl_month[0][]	  = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_turn_month[0][] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
		}
		
		if ($_filter_doc == "I") {
			$tmp_turn[0][] = "turn_code is null";
			$tmp_turn_month[0][] = "turn_code is null";
		} else if ($_filter_doc == "R") {
			$tmp_bill[0][] = "bill_code is null";
			$tmp_sl[0][]	= "bill_code is null";
			$tmp_bill_month[0][] = "bill_code is null";
			$tmp_sl_month[0][]	  = "bill_code is null";
		}
		
		if($_filter_vat == 'vat') {
			$tmp_bill[0][] = "bill_vat > 0";
			$tmp_sl[0][] = "bill_vat > 0";
			$tmp_turn[0][] = "turn_vat > 0";
			$tmp_bill_month[0][] = "bill_vat > 0";
			$tmp_sl_month[0][] = "bill_vat > 0";
			$tmp_turn_month[0][] = "turn_vat > 0";
		} else if ($_filter_vat == 'non') {
			$tmp_bill[0][] = "bill_vat = 0";
			$tmp_sl[0][] = "bill_vat = 0";
			$tmp_turn[0][] = "turn_vat = 0";
			$tmp_bill_month[0][] = "bill_vat = 0";
			$tmp_sl_month[0][] = "bill_vat = 0";
			$tmp_turn_month[0][] = "turn_vat = 0";
		}
		
		if($_filter_dept != 'all') {
			$tmp_bill[0][] = "bill_dept = '$_filter_dept'";
			$tmp_sl[0][] = "bill_dept = '$_filter_dept'";
			$tmp_turn[0][] = "turn_dept = '$_filter_dept'";
			$tmp_bill_month[0][] = "bill_dept = '$_filter_dept'";
			$tmp_sl_month[0][] = "bill_dept = '$_filter_dept'";
			$tmp_turn_month[0][] = "turn_dept = '$_filter_dept'";
		}

		$tmp_bill[0][]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to' AND bill_type_billing in (1,2)";
		$tmp_sl[0][]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to' AND bill_type_billing = 3";
		$tmp_turn[0][]	= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_bill_month[0][]	= "bill_type_billing in (1,2)";
		$tmp_sl_month[0][]		= "bill_type_billing = 3";
		$tmp_turn_month[0][]	= "turn_return_condition != 1";

	break;

	// MED ==============================================================================================================
	case "MED":

		if ($_last_category != 0) {
			$catList = executeSP("idc_getSubCategory", $_last_category);
			$tmp_bill[1][] = "icat.icat_midx IN (" . (empty($catList[1]) ? "0" : $catList[1]) . ")";
			$tmp_sl[1][] 	= "icat.icat_midx IN (" . (empty($catList[1]) ? "0" : $catList[1]) . ")";
			$tmp_turn[1][] = "icat.icat_midx IN (" . (empty($catList[1]) ? "0" : $catList[1]) . ")";
			$tmp_bill_month[1][] = "icat.icat_midx IN (" . (empty($catList[1]) ? "0" : $catList[1]) . ")";
			$tmp_sl_month[1][]	  = "icat.icat_midx IN (" . (empty($catList[1]) ? "0" : $catList[1]) . ")";
			$tmp_turn_month[1][] = "icat.icat_midx IN (" . (empty($catList[1]) ? "0" : $catList[1]) . ")";
		}
		
		if ($_filter_doc == "I") {
			$tmp_turn[1][] = "turn_code is null";
			$tmp_turn_month[1][] = "turn_code is null";
		} else if ($_filter_doc == "R") {
			$tmp_bill[1][] = "bill_code is null";
			$tmp_sl[1][]	= "bill_code is null";
			$tmp_bill_month[1][] = "bill_code is null";
			$tmp_sl_month[1][]	  = "bill_code is null";
		}
		
		if($_filter_vat == 'vat') {
			$tmp_bill[1][] = "bill_vat > 0";
			$tmp_sl[1][] = "bill_vat > 0";
			$tmp_turn[1][] = "turn_vat > 0";
			$tmp_bill_month[1][] = "bill_vat > 0";
			$tmp_sl_month[1][] = "bill_vat > 0";
			$tmp_turn_month[1][] = "turn_vat > 0";
		} else if ($_filter_vat == 'non') {
			$tmp_bill[1][] = "bill_vat = 0";
			$tmp_sl[1][] = "bill_vat = 0";
			$tmp_turn[1][] = "turn_vat = 0";
			$tmp_bill_month[1][] = "bill_vat = 0";
			$tmp_sl_month[1][] = "bill_vat = 0";
			$tmp_turn_month[1][] = "turn_vat = 0";
		}
		
		if($_filter_dept != 'all') {
			$tmp_bill[1][] = "bill_dept = '$_filter_dept'";
			$tmp_sl[1][] = "bill_dept = '$_filter_dept'";
			$tmp_turn[1][] = "turn_dept = '$_filter_dept'";
			$tmp_bill_month[1][] = "bill_dept = '$_filter_dept'";
			$tmp_sl_month[1][] = "bill_dept = '$_filter_dept'";
			$tmp_turn_month[1][] = "turn_dept = '$_filter_dept'";
		}

		$tmp_bill[1][]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to' AND bill_type_billing in (1,2)";
		$tmp_sl[1][]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to' AND bill_type_billing = 3";
		$tmp_turn[1][]	= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_bill_month[1][]	= "bill_type_billing in (1,2)";
		$tmp_sl_month[1][]		= "bill_type_billing = 3";
		$tmp_turn_month[1][]	= "turn_return_condition != 1";

	break;

	// MEP ==============================================================================================================
	case "MEP":

		if ($_last_category != 0) {
			$catList = executeSP("idc_getSubCategory", $_last_category);
			$tmp_bill[0][] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_sl[0][] 	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_turn[0][] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_bill_month[0][] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_sl_month[0][]	  = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_turn_month[0][] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
		}
		
		if ($_filter_doc == "I") {
			$tmp_turn[0][] = "turn_code is null";
			$tmp_turn_month[0][] = "turn_code is null";
		} else if ($_filter_doc == "R") {
			$tmp_bill[0][] = "bill_code is null";
			$tmp_sl[0][]	= "bill_code is null";
			$tmp_bill_month[0][] = "bill_code is null";
			$tmp_sl_month[0][]	  = "bill_code is null";
		}
		
		if($_filter_vat == 'vat') {
			$tmp_bill[0][] = "bill_vat > 0";
			$tmp_sl[0][] = "bill_vat > 0";
			$tmp_turn[0][] = "turn_vat > 0";
			$tmp_bill_month[0][] = "bill_vat > 0";
			$tmp_sl_month[0][] = "bill_vat > 0";
			$tmp_turn_month[0][] = "turn_vat > 0";
		} else if ($_filter_vat == 'non') {
			$tmp_bill[0][] = "bill_vat = 0";
			$tmp_sl[0][] = "bill_vat = 0";
			$tmp_turn[0][] = "turn_vat = 0";
			$tmp_bill_month[0][] = "bill_vat = 0";
			$tmp_sl_month[0][] = "bill_vat = 0";
			$tmp_turn_month[0][] = "turn_vat = 0";
		}
		
		if($_filter_dept != 'all') {
			$tmp_bill[0][] = "bill_dept = '$_filter_dept'";
			$tmp_sl[0][] = "bill_dept = '$_filter_dept'";
			$tmp_turn[0][] = "turn_dept = '$_filter_dept'";
			$tmp_bill_month[0][] = "bill_dept = '$_filter_dept'";
			$tmp_sl_month[0][] = "bill_dept = '$_filter_dept'";
			$tmp_turn_month[0][] = "turn_dept = '$_filter_dept'";
		}
		
		$tmp_bill[0][]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to' AND bill_type_billing in (1,2)";
		$tmp_sl[0][]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to' AND bill_type_billing = 3";
		$tmp_turn[0][]	= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_bill_month[0][]	= "bill_type_billing in (1,2)";
		$tmp_sl_month[0][]		= "bill_type_billing = 3";
		$tmp_turn_month[0][]	= "turn_return_condition != 1";

	break;


}

$strWhere[0]	= implode(" AND ", $tmp_bill[0]);
$strWhere[1]	= implode(" AND ", $tmp_sl[0]);
$strWhere[2]	= implode(" AND ", $tmp_turn[0]);
$strWhere[3]	= implode(" AND ", $tmp_bill[1]);
$strWhere[4]	= implode(" AND ", $tmp_sl[1]);
$strWhere[5]	= implode(" AND ", $tmp_turn[1]);

$strWhereMonth[0]	= implode(" AND ", $tmp_bill_month[0]);
$strWhereMonth[1]	= implode(" AND ", $tmp_sl_month[0]);
$strWhereMonth[2]	= implode(" AND ", $tmp_turn_month[0]);
$strWhereMonth[3]	= implode(" AND ", $tmp_bill_month[1]);
$strWhereMonth[4]	= implode(" AND ", $tmp_sl_month[1]);
$strWhereMonth[5]	= implode(" AND ", $tmp_turn_month[1]);
?>