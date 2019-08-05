<?php
//RECEIVE DATA =========================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/input_invoice_step_1.php", 'info_pl')) {

	//VARIABLE
	$_ordered_by		= $cboFilter[1][ZKP_URL][0][0];
	$_inv_no		= $_POST['_inv_no'];
	$_inv_date		= date("j-M-Y", strtotime($_POST['_inv_date']));
	$_etd_date		= date("j-M-Y", strtotime($_POST['_etd_date']));
	$_eta_date		= date("j-M-Y", strtotime($_POST['_eta_date']));
	$_po_code		= $_POST['_po_code'];
	$_po_date		= date("j-M-Y", strtotime($_POST['_po_date']));
	$_pl_type		= $_POST['_pl_type'];
	$_shipment_mode		= $_POST['_shipment_mode'];
	$_mode_desc		= $_POST['_mode_desc'];
	$_layout_type		= $_POST['_layout_type'];
	$_received_by		= $_POST['_received_by'];

	$_supplier_code		= $_POST['_sp_code'];
	$_supplier_name		= $_POST['_sp_name'];
	$_supplier_attn		= $_POST['_sp_attn'];
	$_supplier_cc		= $_POST['_sp_cc'];
	$_supplier_phone	= $_POST['_sp_phone'];
	$_supplier_fax		= $_POST['_sp_fax'];

	//Check valid supplier code
	$sql = "SELECT sp_code FROM ".ZKP_SQL."_tb_supplier WHERE sp_code = '$_supplier_code'";
	isZKError($result =& query($sql)) ? $M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/input_invoice_step_1.php") : false;

	if (numQueryRows($result) <= 0) {
		$o = new ZKError ("INVALID_SUPPLIER_CODE", "INVALID_SUPPLIER_CODE", "The Supplier code, <strong>'$_supplier_code'</strong> does not exist, Please try again");
		$M->goErrorPage($o, HTTP_DIR . "$currentDept/$moduleDept/input_invoice_step_1.php");
	}
}

//RECEIVE DATA =========================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . $currentDept . '/packing_list/input_invoice_step_3.php', 'info_claim')) {

	//VARIABLE
	$_ordered_by		= $cboFilter[1][ZKP_URL][0][0];
	$_inv_no		= $_POST['_inv_no'];
	$_inv_date		= date("j-M-Y", strtotime($_POST['_inv_date']));
	$_etd_date		= date("j-M-Y", strtotime($_POST['_etd_date']));
	$_eta_date		= date("j-M-Y", strtotime($_POST['_eta_date']));
	$_shipment_mode		= $_POST['_shipment_mode'];
	$_mode_desc		= $_POST['_mode_desc'];
	$_received_by		= $_POST['_received_by'];
	$_layout_type		= $_POST['_layout_type'];

	$_supplier_code		= $_POST['_sp_code'];
	$_supplier_name		= $_POST['_sp_name'];
	$_supplier_attn		= $_POST['_sp_attn'];
	$_supplier_cc		= $_POST['_sp_cc'];
	$_supplier_phone	= $_POST['_sp_phone'];
	$_supplier_fax		= $_POST['_sp_fax'];
}


//INSERT PL ============================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "/index.php", 'insert_pl')) {

	$_ordered_by 		= $_POST['_ordered_by'];
	$_sp_code		= strtoupper($_POST['_sp_code']);
	$_sp_name	 	= $_POST['_sp_name'];
	$_inv_no	 	= $_POST['_inv_no'];
	$_inv_date	 	= $_POST['_inv_date'];
	$_po_code	 	= $_POST['_po_code'];
	$_po_date	 	= $_POST['_po_date'];
	$_etd_date	 	= $_POST['_etd_date'];
	$_eta_date	 	= $_POST['_eta_date'];
	$_layout_type		= $_POST['_layout_type'];
	$_shipment_mode		= $_POST['_shipment_mode'];
	$_pl_type		= $_POST['_pl_type'];
	$_mode_desc		= $_POST['_mode_desc'];
	$_received_by		= $_POST['_received_by'];
	$_total_qty		= $_POST['totalQty'];
	$_remark		= $_POST['_remark'];
	$_lastupdated_by_account = $S->getValue("ma_account");

	//Item Value
	foreach($_POST['_icat_midx'] as $val)		$_icat_midx[]		= $val;
	foreach($_POST['_it_code'] as $val)		$_it_code[]		= $val;
	foreach($_POST['_plit_item'] as $val)		$_plit_item[]		= $val;
	foreach($_POST['_plit_desc'] as $val)		$_plit_desc[]		= $val;
	foreach($_POST['_plit_qty'] as $val)		$_plit_qty[] 		= $val;
	foreach($_POST['_plit_unit_price'] as $val)	$_plit_unit_price[]	= $val;
	foreach($_POST['_plit_remark'] as $val)		$_plit_remark[]		= $val;
	foreach($_POST['_plit_att'] as $val)		$_plit_att[] 		= $val;

	//make pgsql ARRAY String for many item
	$_icat_midx	= implode(',', $_icat_midx);
	$_it_code	= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_plit_item	= '$$' . implode('$$,$$', $_plit_item) . '$$';
	$_plit_desc	= '$$' . implode('$$,$$', $_plit_desc) . '$$';
	$_plit_qty	= implode(',', $_plit_qty);
	$_plit_unit_price = implode(',', $_plit_unit_price);
	$_plit_remark	= '$$' . implode('$$,$$', $_plit_remark) . '$$';
	$_plit_att	= '$$' . implode('$$,$$', $_plit_att) . '$$';

	//ADD NEW PL
	$result = executeSP(
		ZKP_SQL."_addNewPL",
		"$\${$_po_code}$\$",
		$_ordered_by,
		"$\${$_sp_code}$\$",
		"$\${$_sp_name}$\$",
		"$\${$_inv_no}$\$",
		"$\${$_inv_date}$\$",
		"$\${$_etd_date}$\$",
		"$\${$_eta_date}$\$",
		$_layout_type,
		"$\${$_received_by}$\$",
		"$\${$_lastupdated_by_account}$\$",
		"$\${$_shipment_mode}$\$",
		"$\${$_mode_desc}$\$",
		$_pl_type,
		$_total_qty,
		"$\${$_remark}$\$",
		"ARRAY[$_icat_midx]",
		"ARRAY[$_it_code]",
		"ARRAY[$_plit_item]",
		"ARRAY[$_plit_desc]",
		"ARRAY[$_plit_unit_price]",
		"ARRAY[$_plit_qty]",
		"ARRAY[$_plit_remark]",
		"ARRAY[$_plit_att]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/input_pl_step_1.php");
	}
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/summary_pl_by_supplier.php");
}

//INSERT CLAIM =========================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . $currentDept . '/packing_list/index.php', 'insert_claim')) {

	$_ordered_by 		= $_POST['_ordered_by'];
	$_sp_code		= strtoupper($_POST['_sp_code']);
	$_sp_name	 	= $_POST['_sp_name'];
	$_inv_no	 	= $_POST['_inv_no'];
	$_inv_date	 	= $_POST['_inv_date'];
	$_etd_date	 	= $_POST['_etd_date'];
	$_eta_date	 	= $_POST['_eta_date'];
	$_received_by		= $_POST['_received_by'];
	$_shipment_mode		= $_POST['_shipment_mode'];
	$_mode_desc		= $_POST['_mode_desc'];
	$_remark		= $_POST['_remark'];
	$_lastupdated_by_account = $S->getValue("ma_account");

	//Item Value
	foreach($_POST['_icat_midx'] as $val)		$_icat_midx[]		= $val;
	foreach($_POST['_it_code'] as $val)		$_it_code[]		= $val;
	foreach($_POST['_it_item'] as $val)		$_it_item[]		= $val;
	foreach($_POST['_it_desc'] as $val)		$_it_desc[]		= $val;
	foreach($_POST['_it_qty'] as $val)		$_it_qty[] 		= $val;
	foreach($_POST['_it_unit_price'] as $val)	$_it_unit_price[] 	= $val;
	foreach($_POST['_it_remark'] as $val)		$_it_remark[]		= $val;
	foreach($_POST['_it_att'] as $val)		$_it_att[] 		= $val;

	//make pgsql ARRAY String for many item
	$_icat_midx	= implode(',', $_icat_midx);
	$_it_code	= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_it_qty	= implode(',', $_it_qty);
	$_it_unit_price = implode(',', $_it_unit_price);
	$_it_remark	= '$$' . implode('$$,$$', $_it_remark) . '$$';
	$_it_att	= '$$' . implode('$$,$$', $_it_att) . '$$';

	//ADD NEW PL from CLAIM
	$result = executeSP(
		ZKP_SQL."_addNewPLClaim",
		$_ordered_by,
		"$\${$_sp_code}$\$",
		"$\${$_sp_name}$\$",
		"$\${$_inv_no}$\$",
		"$\${$_inv_date}$\$",
		"$\${$_etd_date}$\$",
		"$\${$_eta_date}$\$",
		"$\${$_received_by}$\$",
		"$\${$_lastupdated_by_account}$\$",
		"$\${$_shipment_mode}$\$",
		"$\${$_mode_desc}$\$",
		"$\${$_remark}$\$",
		"ARRAY[$_icat_midx]",
		"ARRAY[$_it_code]",
		"ARRAY[$_it_unit_price]",
		"ARRAY[$_it_qty]",
		"ARRAY[$_it_remark]",
		"ARRAY[$_it_att]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result,  HTTP_DIR . $currentDept . "/packing_list/input_pl_step_3.php");
	}
	$M->goPage(HTTP_DIR . $currentDept . "/packing_list/summary_pl_by_supplier.php");
}
?>