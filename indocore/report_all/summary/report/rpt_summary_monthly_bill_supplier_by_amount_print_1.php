<?php
//SET WHERE PARAMETER
$tmp_bill	= array();
$tmp_turn	= array();
$tmp_bill_month	= array();
$tmp_turn_month	= array();

switch (ZKP_URL) {
	// ALL ==============================================================================================================
	case "ALL":

		if($_order_by == "1") {			// INDOCORE
			$tmp_bill[0][]		= "bill_ordered_by = 1";
			$tmp_turn[0][]		= "turn_ordered_by = 1";
			$tmp_bill_month[0][]	= "bill_ordered_by = 1";
			$tmp_turn_month[0][]	= "turn_ordered_by = 1";
			$tmp_bill[1][]		= "bill_code is null";
			$tmp_turn[1][]		= "turn_code is null";
			$tmp_bill_month[1][]	= "bill_code is null";
			$tmp_turn_month[1][]	= "turn_code is null";
		} else if($_order_by == "2") {		// MEDIKUS
			$tmp_bill[0][]		= "bill_ordered_by = 2";
			$tmp_turn[0][]		= "turn_ordered_by = 2";
			$tmp_bill_month[0][]	= "bill_ordered_by = 2";
			$tmp_turn_month[0][]	= "turn_ordered_by = 2";
			$tmp_bill[1][]		= "bill_code is null";
			$tmp_turn[1][]		= "turn_code is null";
			$tmp_bill_month[1][]	= "bill_code is null";
			$tmp_turn_month[1][]	= "turn_code is null";
		} else if($_order_by == "3") {		// MEDISINDO
			$tmp_bill[0][]		= "bill_code is null";
			$tmp_turn[0][]		= "turn_code is null";
			$tmp_bill_month[0][]	= "bill_code is null";
			$tmp_turn_month[0][]	= "turn_code is null";
			$tmp_bill[1][]		= "bill_ordered_by = 1";
			$tmp_turn[1][]		= "turn_ordered_by = 1";
			$tmp_bill_month[1][]	= "bill_ordered_by = 1";
			$tmp_turn_month[1][]	= "turn_ordered_by = 1";
		} else if($_order_by == "4") {		// SAMUDIA
			$tmp_bill[0][]		= "bill_code is null";
			$tmp_turn[0][]		= "turn_code is null";
			$tmp_bill_month[0][]	= "bill_code is null";
			$tmp_turn_month[0][]	= "turn_code is null";
			$tmp_bill[1][]		= "bill_ordered_by = 2";
			$tmp_turn[1][]		= "turn_ordered_by = 2";
			$tmp_bill_month[1][]	= "bill_ordered_by = 2";
			$tmp_turn_month[1][]	= "turn_ordered_by = 2";
		} else if($_order_by == "5") {		// INDOCORE & MEDIKUS
			$tmp_bill[1][]		= "bill_code is null";
			$tmp_turn[1][]		= "turn_code is null";
			$tmp_bill_month[1][]	= "bill_code is null";
			$tmp_turn_month[1][]	= "turn_code is null";
		} else if($_order_by == "6") {		// MEDISINDO & SAMUDIA
			$tmp_bill[0][]		= "bill_code is null";
			$tmp_turn[0][]		= "turn_code is null";
			$tmp_bill_month[0][]	= "bill_code is null";
			$tmp_turn_month[0][]	= "turn_code is null";
		}

		if($_chk_company == 'off') {
			$tmp_bill[0][]	= "bill_ship_to NOT IN ('6IDC', '0MSD', '0SMD')";
			$tmp_turn[0][]	= "turn_ship_to NOT IN ('6IDC', '0MSD', '0SMD')";
			$tmp_bill_month[0][]	= "bill_ship_to NOT IN ('6IDC', '0MSD', '0SMD')";
			$tmp_turn_month[0][]	= "turn_ship_to NOT IN ('6IDC', '0MSD', '0SMD')";
			$tmp_bill[1][]	= "bill_ship_to NOT IN ('6IDC', '0MSD', '0SMD')";
			$tmp_turn[1][]	= "turn_ship_to NOT IN ('6IDC', '0MSD', '0SMD')";
			$tmp_bill_month[1][]	= "bill_ship_to NOT IN ('6IDC', '0MSD', '0SMD')";
			$tmp_turn_month[1][]	= "turn_ship_to NOT IN ('6IDC', '0MSD', '0SMD')";
		}

		if ($_filter_doc == "I") {
			$tmp_turn[0][] = "turn_code is null";
			$tmp_turn_month[0][] = "turn_code is null";
			$tmp_turn[1][] = "turn_code is null";
			$tmp_turn_month[1][] = "turn_code is null";
		} else if ($_filter_doc == "R") {
			$tmp_bill[0][] = "bill_code is null";
			$tmp_bill_month[0][] = "bill_code is null";
			$tmp_bill[1][] = "bill_code is null";
			$tmp_bill_month[1][] = "bill_code is null";
		}

		if($_filter_vat == 'vat') {
			$tmp_bill[0][] = "bill_vat > 0";
			$tmp_turn[0][] = "turn_vat > 0";
			$tmp_bill_month[0][] = "bill_vat > 0";
			$tmp_turn_month[0][] = "turn_vat > 0";
			$tmp_bill[1][] = "bill_vat > 0";
			$tmp_turn[1][] = "turn_vat > 0";
			$tmp_bill_month[1][] = "bill_vat > 0";
			$tmp_turn_month[1][] = "turn_vat > 0";
		} else if ($_filter_vat == 'non') {
			$tmp_bill[0][] = "bill_vat = 0";
			$tmp_turn[0][] = "turn_vat = 0";
			$tmp_bill_month[0][] = "bill_vat = 0";
			$tmp_turn_month[0][] = "turn_vat = 0";
			$tmp_bill[1][] = "bill_vat = 0";
			$tmp_turn[1][] = "turn_vat = 0";
			$tmp_bill_month[1][] = "bill_vat = 0";
			$tmp_turn_month[1][] = "turn_vat = 0";
		}
		
		if($_filter_dept != 'all') {
			$tmp_bill[0][] = "bill_dept = '$_filter_dept'";
			$tmp_turn[0][] = "turn_dept = '$_filter_dept'";
			$tmp_bill_month[0][] = "bill_dept = '$_filter_dept'";
			$tmp_turn_month[0][] = "turn_dept = '$_filter_dept'";
			$tmp_bill[1][] = "bill_dept = '$_filter_dept'";
			$tmp_turn[1][] = "turn_dept = '$_filter_dept'";
			$tmp_bill_month[1][] = "bill_dept = '$_filter_dept'";
			$tmp_turn_month[1][] = "turn_dept = '$_filter_dept'";
		}
		
		$tmp_bill[0][]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to' AND bill_type_billing in (1,2)";
		$tmp_turn[0][]	= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_turn_month[0][]	= "turn_return_condition != 1";
		$tmp_bill[1][]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to' AND bill_type_billing in (1,2)";
		$tmp_turn[1][]	= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_turn_month[1][]	= "turn_return_condition != 1";

	break;

	// IDC ==============================================================================================================
	case "IDC":

		if ($_filter_doc == "I") {
			$tmp_turn[0][] = "turn_code is null";
			$tmp_turn_month[0][] = "turn_code is null";
		} else if ($_filter_doc == "R") {
			$tmp_bill[0][] = "bill_code is null";
			$tmp_bill_month[0][] = "bill_code is null";
		}
		
		if($_filter_vat == 'vat') {
			$tmp_bill[0][] = "bill_vat > 0";
			$tmp_turn[0][] = "turn_vat > 0";
			$tmp_bill_month[0][] = "bill_vat > 0";
			$tmp_turn_month[0][] = "turn_vat > 0";
		} else if ($_filter_vat == 'non') {
			$tmp_bill[0][] = "bill_vat = 0";
			$tmp_turn[0][] = "turn_vat = 0";
			$tmp_bill_month[0][] = "bill_vat = 0";
			$tmp_turn_month[0][] = "turn_vat = 0";
		}
		
		if($_filter_dept != 'all') {
			$tmp_bill[0][] = "bill_dept = '$_filter_dept'";
			$tmp_turn[0][] = "turn_dept = '$_filter_dept'";
			$tmp_bill_month[0][] = "bill_dept = '$_filter_dept'";
			$tmp_turn_month[0][] = "turn_dept = '$_filter_dept'";
		}
		
		$tmp_bill[0][]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to' AND bill_type_billing in (1,2)";
		$tmp_turn[0][]	= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_turn_month[0][]	= "turn_return_condition != 1";

	break;

	// MED==============================================================================================================
	case "MED":

		if ($_filter_doc == "I") {
			$tmp_turn[1][] = "turn_code is null";
			$tmp_turn_month[1][] = "turn_code is null";
		} else if ($_filter_doc == "R") {
			$tmp_bill[1][] = "bill_code is null";
			$tmp_bill_month[1][] = "bill_code is null";
		}
		
		if($_filter_vat == 'vat') {
			$tmp_bill[1][] = "bill_vat > 0";
			$tmp_turn[1][] = "turn_vat > 0";
			$tmp_bill_month[1][] = "bill_vat > 0";
			$tmp_turn_month[1][] = "turn_vat > 0";
		} else if ($_filter_vat == 'non') {
			$tmp_bill[1][] = "bill_vat = 0";
			$tmp_turn[1][] = "turn_vat = 0";
			$tmp_bill_month[1][] = "bill_vat = 0";
			$tmp_turn_month[1][] = "turn_vat = 0";
		}
		
		if($_filter_dept != 'all') {
			$tmp_bill[1][] = "bill_dept = '$_filter_dept'";
			$tmp_turn[1][] = "turn_dept = '$_filter_dept'";
			$tmp_bill_month[1][] = "bill_dept = '$_filter_dept'";
			$tmp_turn_month[1][] = "turn_dept = '$_filter_dept'";
		}
		
		$tmp_bill[1][]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to' AND bill_type_billing in (1,2)";
		$tmp_turn[1][]	= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_turn_month[1][]	= "turn_return_condition != 1";

	break;

	// MEP ==============================================================================================================
	case "MEP":

		if ($_filter_doc == "I") {
			$tmp_turn[0][] = "turn_code is null";
			$tmp_turn_month[0][] = "turn_code is null";
		} else if ($_filter_doc == "R") {
			$tmp_bill[0][] = "bill_code is null";
			$tmp_bill_month[0][] = "bill_code is null";
		}
		
		if($_filter_vat == 'vat') {
			$tmp_bill[0][] = "bill_vat > 0";
			$tmp_turn[0][] = "turn_vat > 0";
			$tmp_bill_month[0][] = "bill_vat > 0";
			$tmp_turn_month[0][] = "turn_vat > 0";
		} else if ($_filter_vat == 'non') {
			$tmp_bill[0][] = "bill_vat = 0";
			$tmp_turn[0][] = "turn_vat = 0";
			$tmp_bill_month[0][] = "bill_vat = 0";
			$tmp_turn_month[0][] = "turn_vat = 0";
		}
		
		if($_filter_dept != 'all') {
			$tmp_bill[0][] = "bill_dept = '$_filter_dept'";
			$tmp_turn[0][] = "turn_dept = '$_filter_dept'";
			$tmp_bill_month[0][] = "bill_dept = '$_filter_dept'";
			$tmp_turn_month[0][] = "turn_dept = '$_filter_dept'";
		}
		
		$tmp_bill[0][]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to' AND bill_type_billing in (1,2)";
		$tmp_turn[0][]	= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_turn_month[0][]	= "turn_return_condition != 1";

	break;



}

$strWhere[0]	= implode(" AND ", $tmp_bill[0]);
$strWhere[1]	= implode(" AND ", $tmp_turn[0]);
$strWhere[2]	= implode(" AND ", $tmp_bill[1]);
$strWhere[3]	= implode(" AND ", $tmp_turn[1]);

$strWhereMonth[0]	= implode(" AND ", $tmp_bill_month[0]);
$strWhereMonth[1]	= implode(" AND ", $tmp_turn_month[0]);
$strWhereMonth[2]	= implode(" AND ", $tmp_bill_month[1]);
$strWhereMonth[3]	= implode(" AND ", $tmp_turn_month[1]);

echo "<pre>";
//var_dump($tmp_bill);
echo "</pre>";


?>