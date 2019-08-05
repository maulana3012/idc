<?php
//SET WHERE PARAMETER
$tmp_sl		= array();
$tmp_bill	= array();
$tmp_dr		= array();
$tmp_turn	= array();
$strWhere	= array();

switch (ZKP_URL) {
	// ALL ==============================================================================================================
	case "ALL":

		if($_order_by == "1") {				// INDOCORE
			$tmp_bill[0][]	= "bill_ordered_by = 1";
			$tmp_turn[0][]	= "turn_ordered_by = 1";
			$tmp_dr[0][]	= "dr_ordered_by = 1";
			$tmp_bill[1][]	= "bill_code is null";
			$tmp_sl[1][]	= "sl_idx is null";
			$tmp_turn[1][]	= "turn_code is null";
			$tmp_dr[1][]	= "dr_code is null";
		} else if($_order_by == "2") {		// MEDISINDO
			$tmp_bill[0][]	= "bill_code is null";
			$tmp_sl[0][]	= "sl_idx is null";
			$tmp_turn[0][]	= "turn_code is null";
			$tmp_dr[0][]	= "dr_code is null";
		} else if($_order_by == "3") {		// MEDIKUS
			$tmp_bill[0][]	= "bill_ordered_by = 2";
			$tmp_turn[0][]	= "turn_ordered_by = 2";
			$tmp_dr[0][]	= "dr_ordered_by = 2";
			$tmp_sl[0][]	= "sl_idx is null";
			$tmp_bill[1][]	= "bill_code is null";
			$tmp_sl[1][]	= "sl_idx is null";
			$tmp_turn[1][]	= "turn_code is null";
			$tmp_dr[1][]	= "dr_code is null";
		} else if($_order_by == "4") {		// INDOCORE & MEDIKUS
			$tmp_bill[1][]	= "bill_code is null";
			$tmp_sl[1][]	= "sl_idx is null";
			$tmp_turn[1][]	= "turn_code is null";
			$tmp_dr[1][]	= "dr_code is null";
		} 

		if($_cug_code != 'all') {
			$tmp_sl[0][]	= "a.cus_code IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_bill[0][]	= "a.cus_code IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_dr[0][]	= "a.cus_code IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_turn[0][]	= "a.cus_code IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_sl[1][]	= "a.cus_code IN (SELECT cus_code FROM med_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_bill[1][]	= "a.cus_code IN (SELECT cus_code FROM med_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_dr[1][]	= "a.cus_code IN (SELECT cus_code FROM med_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_turn[1][]	= "a.cus_code IN (SELECT cus_code FROM med_tb_customer WHERE cug_code = '$_cug_code')";
		}

		if($_cus_code != '') {
			$tmp_sl[0][]	= "a.cus_code = '$_cus_code'";
			$tmp_bill[0][]	= "a.cus_code = '$_cus_code'";
			$tmp_dr[0][]	= "a.cus_code = '$_cus_code'";
			$tmp_turn[0][]	= "a.cus_code = '$_cus_code'";
			$tmp_sl[1][]	= "a.cus_code = '$_cus_code'";
			$tmp_bill[1][]	= "a.cus_code = '$_cus_code'";
			$tmp_dr[1][]	= "a.cus_code = '$_cus_code'";
			$tmp_turn[1][]	= "a.cus_code = '$_cus_code'";
		}

		if($_filter_doc == 'sales') {
			$tmp_sl[0][]	= "sl_qty > 0";
			$tmp_turn[0][]	= "turn_code IS NULL";
			$tmp_sl[1][]	= "sl_qty > 0";
			$tmp_turn[1][]	= "turn_code IS NULL";
		} else if($_filter_doc == 'return') {
			$tmp_sl[0][]	= "sl_qty < 0";
			$tmp_bill[0][]	= "bill_code IS NULL";
			$tmp_dr[0][]	= "dr_code IS NULL";
			$tmp_sl[1][]	= "sl_qty < 0";
			$tmp_bill[1][]	= "bill_code IS NULL";
			$tmp_dr[1][]	= "dr_code IS NULL";
		}

		if($_filter_dept != 'all') {
			$tmp_sl[0][]	= "sl_dept= '$_filter_dept'";
			$tmp_bill[0][]	= "bill_dept = '$_filter_dept'";
			$tmp_dr[0][]	= "dr_dept = '$_filter_dept'";
			$tmp_turn[0][]	= "turn_dept = '$_filter_dept'";
			$tmp_sl[1][]	= "sl_dept = '$_filter_dept'";
			$tmp_bill[1][]	= "bill_dept = '$_filter_dept'";
			$tmp_dr[1][]	= "dr_dept = '$_filter_dept'";
			$tmp_turn[1][]	= "turn_dept = '$_filter_dept'";
		}

		if ($_last_category != 0) {
			$catList = executeSP("idc_getSubCategory", $_last_category);
			$tmp_sl[0][]	= "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_bill[0][]	= "d.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_dr[0][]	= "d.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_turn[0][]	= "d.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$catList = executeSP("med_getSubCategory", $_last_category);
			$tmp_sl[1][]	= "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_bill[1][]	= "d.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_dr[1][]	= "d.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_turn[1][]	= "d.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
		}

		if($currentDept != 'report' && $currentDept != 'report_all') {
			$tmp_bill[0][]	= "bill_dept = '$department'";
			$tmp_dr[0][]	= "dr_dept = '$department'";
			$tmp_turn[0][]	= "turn_dept = '$department'";
			$tmp_bill[1][]	= "bill_dept = '$department'";
			$tmp_dr[1][]	= "dr_dept = '$department'";
			$tmp_turn[1][]	= "turn_dept = '$department'";
		}

		$tmp_sl[0][]	= "sl_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_bill[0][]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to' AND bill_type_invoice = '0'";
		$tmp_dr[0][]	= "dr_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_turn[0][]	= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to' AND turn_paper = 0";
		$tmp_sl[1][]	= "sl_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_bill[1][]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to' AND bill_type_invoice = '0'";
		$tmp_dr[1][]	= "dr_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_turn[1][]	= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to' AND turn_paper = 0";
	break;

	// IDC ==============================================================================================================
	case "IDC":

		if($_cug_code != 'all') {
			$tmp_sl[0][]	= "a.cus_code IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_bill[0][]	= "a.cus_code IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_dr[0][]	= "a.cus_code IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_turn[0][]	= "a.cus_code IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
		}
		
		if($_cus_code != '') {
			$tmp_sl[0][]	= "a.cus_code = '$_cus_code'";
			$tmp_bill[0][]	= "a.cus_code = '$_cus_code'";
			$tmp_dr[0][]	= "a.cus_code = '$_cus_code'";
			$tmp_turn[0][]	= "a.cus_code = '$_cus_code'";
		}
		
		if($_filter_doc == 'sales') {
			$tmp_sl[0][]	= "sl_qty > 0";
			$tmp_turn[0][]	= "turn_code IS NULL";
		} else if($_filter_doc == 'return') {
			$tmp_sl[0][]	= "sl_qty < 0";
			$tmp_bill[0][]	= "bill_code IS NULL";
			$tmp_dr[0][]	= "dr_code IS NULL";
		}
		
		if($_filter_dept != 'all') {
			$tmp_sl[0][]	= "sl_dept = '$_filter_dept'";
			$tmp_bill[0][]	= "bill_dept = '$_filter_dept'";
			$tmp_dr[0][]	= "dr_dept = '$_filter_dept'";
			$tmp_turn[0][]	= "turn_dept = '$_filter_dept'";
		}
		
		if ($_last_category != 0) {
			$catList = executeSP("idc_getSubCategory", $_last_category);
			$tmp_sl[0][]	= "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_bill[0][]	= "d.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_dr[0][]	= "d.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_turn[0][]	= "d.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
		}
		
		if($currentDept != 'report' && $currentDept != 'report_all') {
			$tmp_bill[0][]	= "bill_dept = '$department'";
			$tmp_dr[0][]	= "dr_dept = '$department'";
			$tmp_turn[0][]	= "turn_dept = '$department'";
		}
		
		$tmp_sl[0][]	= "sl_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_bill[0][]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to' AND bill_type_invoice = '0'";
		$tmp_dr[0][]	= "dr_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_turn[0][]	= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to' AND turn_paper = 0";

	break;

	// MED ==============================================================================================================
	case "MED":

		if($_cug_code != 'all') {
			$tmp_sl[1][]	= "a.cus_code IN (SELECT cus_code FROM med_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_bill[1][]	= "a.cus_code IN (SELECT cus_code FROM med_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_dr[1][]	= "a.cus_code IN (SELECT cus_code FROM med_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_turn[1][]	= "a.cus_code IN (SELECT cus_code FROM med_tb_customer WHERE cug_code = '$_cug_code')";
		}
		
		if($_cus_code != '') {
			$tmp_sl[1][]	= "a.cus_code = '$_cus_code'";
			$tmp_bill[1][]	= "a.cus_code = '$_cus_code'";
			$tmp_dr[1][]	= "a.cus_code = '$_cus_code'";
			$tmp_turn[1][]	= "a.cus_code = '$_cus_code'";
		}
		
		if($_filter_doc == 'sales') {
			$tmp_sl[1][]	= "sl_qty > 0";
			$tmp_turn[1][]	= "turn_code IS NULL";
		} else if($_filter_doc == 'return') {
			$tmp_sl[1][]	= "sl_qty < 0";
			$tmp_bill[1][]	= "bill_code IS NULL";
			$tmp_dr[1][]	= "dr_code IS NULL";
		}
		
		if($_filter_dept != 'all') {
			$tmp_sl[1][]	= "sl_dept = '$_filter_dept'";
			$tmp_bill[1][]	= "bill_dept = '$_filter_dept'";
			$tmp_dr[1][]	= "dr_dept = '$_filter_dept'";
			$tmp_turn[1][]	= "turn_dept = '$_filter_dept'";
		}
		
		if ($_last_category != 0) {
			$catList = executeSP("med_getSubCategory", $_last_category);
			$tmp_sl[1][]	= "icat_midx IN (" . (empty($catList[1]) ? "0" : $catList[1]) . ")";
			$tmp_bill[1][]	= "d.icat_midx IN (" . (empty($catList[1]) ? "0" : $catList[1]) . ")";
			$tmp_dr[1][]	= "d.icat_midx IN (" . (empty($catList[1]) ? "0" : $catList[1]) . ")";
			$tmp_turn[1][]	= "d.icat_midx IN (" . (empty($catList[1]) ? "0" : $catList[1]) . ")";
		}
		
		if($currentDept != 'report' && $currentDept != 'report_all') {
			$tmp_bill[1][]	= "bill_dept = '$department'";
			$tmp_dr[1][]	= "dr_dept = '$department'";
			$tmp_turn[1][]	= "turn_dept = '$department'";
		}
		
		$tmp_sl[1][]	= "sl_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_bill[1][]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to' AND bill_type_invoice = '0'";
		$tmp_dr[1][]	= "dr_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_turn[1][]	= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to' AND turn_paper = 0";

	break;

	// MEP ==============================================================================================================
	case "MEP":

		if($_cug_code != 'all') {
			$tmp_sl[0][]	= "a.cus_code IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_bill[0][]	= "a.cus_code IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_dr[0][]	= "a.cus_code IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
			$tmp_turn[0][]	= "a.cus_code IN (SELECT cus_code FROM idc_tb_customer WHERE cug_code = '$_cug_code')";
		}
		
		if($_cus_code != '') {
			$tmp_sl[0][]	= "a.cus_code = '$_cus_code'";
			$tmp_bill[0][]	= "a.cus_code = '$_cus_code'";
			$tmp_dr[0][]	= "a.cus_code = '$_cus_code'";
			$tmp_turn[0][]	= "a.cus_code = '$_cus_code'";
		}
		
		if($_filter_doc == 'sales') {
			$tmp_sl[0][]	= "sl_qty > 0";
			$tmp_turn[0][]	= "turn_code IS NULL";
		} else if($_filter_doc == 'return') {
			$tmp_sl[0][]	= "sl_qty < 0";
			$tmp_bill[0][]	= "bill_code IS NULL";
			$tmp_dr[0][]	= "dr_code IS NULL";
		}
		
		if($_filter_dept != 'all') {
			$tmp_sl[0][]	= "sl_dept = '$_filter_dept'";
			$tmp_bill[0][]	= "bill_dept = '$_filter_dept'";
			$tmp_dr[0][]	= "dr_dept = '$_filter_dept'";
			$tmp_turn[0][]	= "turn_dept = '$_filter_dept'";
		}
		
		if ($_last_category != 0) {
			$catList = executeSP("idc_getSubCategory", $_last_category);
			$tmp_sl[0][]	= "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_bill[0][]	= "d.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_dr[0][]	= "d.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
			$tmp_turn[0][]	= "d.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
		}
		
		if($currentDept != 'report' && $currentDept != 'report_all') {
			$tmp_bill[0][]	= "bill_dept = '$department'";
			$tmp_dr[0][]	= "dr_dept = '$department'";
			$tmp_turn[0][]	= "turn_dept = '$department'";
		}
		
		$tmp_sl[0][]	= "sl_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_bill[0][]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to' AND bill_type_invoice = '0'";
		$tmp_dr[0][]	= "dr_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_turn[0][]	= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to' AND turn_paper = 0";

	break;

}

$strWhere[0]	= implode(" AND ", $tmp_sl[0]);
$strWhere[1]	= implode(" AND ", $tmp_bill[0]);
$strWhere[2]	= implode(" AND ", $tmp_dr[0]);
$strWhere[3]	= implode(" AND ", $tmp_turn[0]);
$strWhere[4]	= implode(" AND ", $tmp_sl[1]);
$strWhere[5]	= implode(" AND ", $tmp_bill[1]);
$strWhere[6]	= implode(" AND ", $tmp_dr[1]);
$strWhere[7]	= implode(" AND ", $tmp_turn[1]);
?>