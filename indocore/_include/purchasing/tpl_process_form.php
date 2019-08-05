<?php
//RECEIVE DATA ========================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/input_po_step_1.php", 'order_info')) {

	//VARIABLE
	$ordby			= array('IDC'=> array(1=>'INDOCORE PERKASA', 2=>'MEDIKUS EKA'), 'MED'=> array(1=>'MEDISINDO BAHANA', 2=>'SAMUDIA BAHTERA'));
	$title			= array(1=>"Issue PO &amp; Order Item","Invoice only");
	$_ordered_by	= ($_POST['_order_by'] != '' ) ? $_POST['_order_by'] : $_POST['cboOrdBy'];
	$_po_type_invoice = $_POST['cboTypePO'];
	$_po_date		= date("j-M-Y", strtotime($_POST['_po_date']));
	$_po_type		= $_POST['_po_type'];
	$_shipment_mode	= $_POST['_shipment_mode'];
	$_mode_desc		= $_POST['_mode_desc'];
	$_received_by	= $_POST['_received_by'];
	$_layout_type	= $_POST['_layout_type'];
	$_currency_type	= $_POST['_currency_type'];

	$_supplier_code		= $_POST['_sp_code'];
	$_supplier_name		= $_POST['_sp_name'];
	$_supplier_attn		= $_POST['_sp_attn'];
	$_supplier_cc		= $_POST['_sp_cc'];
	$_supplier_phone	= $_POST['_sp_phone'];
	$_supplier_fax		= $_POST['_sp_fax'];

	$_forwarder_code	= $_POST['_fw_code'];
	$_forwarder_name	= $_POST['_fw_name'];
	$_forwarder_phone	= $_POST['_fw_phone'];
	$_forwarder_fax		= $_POST['_fw_fax'];
	$_forwarder_mobile_phone	= $_POST['_fw_mobile_phone'];
	$_forwarder_contact	= $_POST['_fw_contact'];	

	//Check valid supplier code
	$sql = "SELECT sp_code FROM ".ZKP_SQL."_tb_supplier WHERE sp_code = '$_supplier_code'";
	isZKError($result =& query($sql)) ? $M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/input_po_step_1.php") : false;

	if (numQueryRows($result) <= 0) {
		$o = new ZKError ("INVALID_SUPPLIER_CODE", "INVALID_SUPPLIER_CODE", "The Supplier code, <strong>'$_supplier_code'</strong> does not exist, Please try again");
		$M->goErrorPage($o, HTTP_DIR . "$currentDept/$moduleDept/input_po_step_1.php");
	}

	//Check valid forwarder code
	$sql = "SELECT fw_code, fw_address FROM ".ZKP_SQL."_tb_forwarder WHERE fw_code = '$_forwarder_code'";
	$result =& query($sql);
	$column =& fetchRowAssoc($result);
	$_forwarder_address	= $column['fw_address'];

}

//INSERT PO ===========================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . $currentDept . "$currentDept/$moduleDept/index.php", 'insert')) {

	$_po_date	 	= $_POST['_po_date'];
	$_sp_code		= strtoupper($_POST['_sp_code']);
	$_sp_name		= $_POST['_sp_name'];
	$_layout_type	= $_POST['_layout_type'];
	$_currency_type	= $_POST['_currency_type'];
	$_received_by	= $_POST['_received_by'];
	$_shipment_mode	= $_POST['_shipment_mode'];
	$_po_type		= $_POST['_po_type'];
	$_po_type_invoice= $_POST['_po_type_invoice'];
	$_mode_desc		= $_POST['_mode_desc'];
	$_print_remark	= $_POST['_print_remark'];
	$_remark		= $_POST['_remark'];
	$_prepared_by	= $_POST['_prepared_by'];
	$_confirmed_by	= $_POST['_confirmed_by'];
	$_total_qty		= $_POST['totalQty'];
	$_total_amount	= $_POST['totalAmount'];

	$_lastupdated_by_account = $S->getValue("ma_account");
	$_revision_time = -1; // will be 0 at the print time
	$_ordered_by	= $_POST['_ordered_by'];

	//Item Value
	foreach($_POST['_icat_midx'] as $val)		$_icat_midx[]	= $val;
	foreach($_POST['_it_code'] as $val)			$_it_code[]		= $val;
	foreach($_POST['_poit_item'] as $val)		$_poit_item[]	= $val;
	foreach($_POST['_poit_desc'] as $val)		$_poit_desc[]		= $val;
	foreach($_POST['_poit_unit_price'] as $val)	$_poit_unit_price[] = $val;
	foreach($_POST['_poit_qty'] as $val)		$_poit_qty[] 		= $val;
	foreach($_POST['_poit_remark'] as $val)		$_poit_remark[]		= $val;
	foreach($_POST['_poit_att'] as $val)		$_poit_att[] 		= $val;

	//make pgsql ARRAY String for many item
	$_icat_midx		= implode(',', $_icat_midx);
	$_it_code		= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_poit_item		= '$$' . implode('$$,$$', $_poit_item) . '$$';
	$_poit_desc		= '$$' . implode('$$,$$', $_poit_desc) . '$$';
	$_poit_unit_price = implode(',', $_poit_unit_price);
	$_poit_qty		= implode(',', $_poit_qty);
	$_poit_remark	= '$$' . implode('$$,$$', $_poit_remark) . '$$';
	$_poit_att		= '$$' . implode('$$,$$', $_poit_att) . '$$';

	//newPO
	$result = executeSP(
		ZKP_SQL."_addNewPO",
		"$\$".ZKP_SQL."$\$",
		$_ordered_by,
		"DATE $\${$_po_date}$\$",
		"$\${$_sp_code}$\$",
		"$\${$_sp_name}$\$",
		$_layout_type,
		$_currency_type,
		"$\${$_received_by}$\$",
		"$\${$_lastupdated_by_account}$\$",
		"$\${$_shipment_mode}$\$",
		"$\${$_mode_desc}$\$",
		$_po_type,
		$_po_type_invoice,
		$_total_qty,
		$_total_amount,
		"$\${$_print_remark}$\$",
		"$\${$_remark}$\$",
		"$\${$_prepared_by}$\$",
		"$\${$_confirmed_by}$\$",
		"ARRAY[$_icat_midx]",
		"ARRAY[$_it_code]",
		"ARRAY[$_poit_item]",
		"ARRAY[$_poit_desc]",
		"ARRAY[$_poit_unit_price]",
		"ARRAY[$_poit_qty]",
		"ARRAY[$_poit_remark]",
		"ARRAY[$_poit_att]"
	);

	if (isZKError($result)) {
		$errMessage = $result->getMessage();
		if(strpos($errMessage, "duplicate key violates")) {
			$result = new ZKError(
				"DUPLICATE_CODE_EXIST",
				"DUPLICATE_CODE_EXIST",
				"Same Code exists, please check your order code again");
		}
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/input_po_step_1.php");
	}

	//SAVE PDF FILE
	$_code = $result[0];
	include "pdf/generate_pdf.php";
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_po.php?_code=".$_code);
}

//UPDATE PO ===========================================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'update')) {

	$_code	 		= $_POST['_code'];
	$_po_date	 	= $_POST['_po_date'];
	$_sp_code		= strtoupper($_POST['_sp_code']);
	$_sp_name		= $_POST['_sp_name'];
	$_layout_type	= $_POST['_layout_type'];
	$_currency_type	= $_POST['_currency_type'];
	$_received_by	= $_POST['_received_by'];
	$_inputed_by	= $S->getValue("ma_account");
	$_shipment_mode	= $_POST['_shipment_mode'];
	$_po_type		= $_POST['_po_type'];
	$_mode_desc		= $_POST['_mode_desc'];
	$_print_remark	= $_POST['_print_remark'];
	$_remark		= $_POST['_remark'];
	$_prepared_by	= $_POST['_prepared_by'];
	$_confirmed_by	= $_POST['_confirmed_by'];
	$_total_qty		= $_POST['totalQty'];
	$_total_amount	= $_POST['totalAmount'];

	$_lastupdated_by_account = $S->getValue("ma_account");
	$_revision_time = (int) $_POST['_revision_time'];

	//Item Value
	foreach($_POST['_icat_midx'] as $val)		$_icat_midx[]	= $val;
	foreach($_POST['_it_code'] as $val)			$_it_code[]		= $val;
	foreach($_POST['_poit_item'] as $val)		$_poit_item[]	= $val;
	foreach($_POST['_poit_desc'] as $val)		$_poit_desc[]		= $val;
	foreach($_POST['_poit_unit_price'] as $val)	$_poit_unit_price[] = $val;
	foreach($_POST['_poit_qty'] as $val)		$_poit_qty[] 		= $val;
	foreach($_POST['_poit_remark'] as $val)		$_poit_remark[]		= $val;
	foreach($_POST['_poit_att'] as $val)		$_poit_att[] 		= $val;

	//make pgsql ARRAY String for many item
	$_icat_midx		= implode(',', $_icat_midx);
	$_it_code		= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_poit_item		= '$$' . implode('$$,$$', $_poit_item) . '$$';
	$_poit_desc		= '$$' . implode('$$,$$', $_poit_desc) . '$$';
	$_poit_unit_price = implode(',', $_poit_unit_price);
	$_poit_qty		= implode(',', $_poit_qty);
	$_poit_remark	= '$$' . implode('$$,$$', $_poit_remark) . '$$';
	$_poit_att		= '$$' . implode('$$,$$', $_poit_att) . '$$';

	//newPO
	$result = executeSP(
		ZKP_SQL."_revisePO",
		"$\${$_code}$\$",
		"$\${$_sp_code}$\$",
		"$\${$_sp_name}$\$",
		"DATE $\${$_po_date}$\$",
		$_layout_type,
		$_currency_type,
		"$\${$_received_by}$\$",
		"$\${$_inputed_by}$\$",
		"$\${$_shipment_mode}$\$",
		"$\${$_mode_desc}$\$",
		$_po_type,
		$_total_qty,
		$_total_amount,
		"$\${$_print_remark}$\$",
		"$\${$_remark}$\$",
		"$\${$_prepared_by}$\$",
		"$\${$_confirmed_by}$\$",
		"$\${$_lastupdated_by_account}$\$",
		$_revision_time,
		"ARRAY[$_icat_midx]",
		"ARRAY[$_it_code]",
		"ARRAY[$_poit_item]",
		"ARRAY[$_poit_desc]",
		"ARRAY[$_poit_unit_price]",
		"ARRAY[$_poit_qty]",
		"ARRAY[$_poit_remark]",
		"ARRAY[$_poit_att]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_po.php?_code=$_code");
	}

	//SAVE PDF FILE
	include "pdf/generate_pdf.php";
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_po.php?_code=$_code");
}

//DELETE PO ===========================================================================================================
if (ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'delete')) {

	$_rev	  = (int) $_POST['_revision_time'];
	$_po_date = date("Ym", strtotime($_POST['_po_date']));
	$_type	  = $_POST['_po_type'];

	if($_type == 1) $_doc = substr($_code,0,2)."-". substr($_code,3,2)."-".substr($_code,6,2);
	else if($_type == 2) $_doc = substr($_code,0,2)."-". substr($_code,3,3)."-".substr($_code,7,2);

	$result =& query("DELETE FROM ".ZKP_SQL."_tb_po WHERE po_code = '$_code'");

	if(isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_po.php?_code=".urlencode($_code));
	} else {
		for ($i = $_rev ; $i >=0 ; $i--) {
			@unlink(PDF_STORAGE . "purchasing/po/{$_po_date}/{$_doc}_rev_{$i}.pdf");
		}
	}
	goPage(HTTP_DIR . "$currentDept/$moduleDept/summary_po_by_supplier.php");
}

//CONFIRM PO ==========================================================================================================
if (ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'confirm')) {
	$_code = $_POST['_code'];
	$_sp_code = strtoupper($_POST['_sp_code']);
	$_po_type = $_POST['_po_type'];
	$_po_type_invoice = $_POST['_po_type_invoice'];
	$_cfm_by_account = $S->getValue('ma_account');

	//Item Value
	foreach($_POST['_it_code'] as $val)			$_it_code[]		= $val;
	foreach($_POST['_poit_qty'] as $val)		$_poit_qty[]	= $val;

	//make pgsql ARRAY String for many item
	$_it_code		= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_poit_qty		= implode(',', $_poit_qty);

	$result = executeSP(
		ZKP_SQL."_confirmPO",
		"$\${$_code}$\$",
		"$\${$_sp_code}$\$",
		$_po_type,
		$_po_type_invoice,
		"$\${$_cfm_by_account}$\$",
		"ARRAY[$_it_code]",
		"ARRAY[$_poit_qty]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_po.php?_code=$_code");
	} else {
		$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_po.php?_code=$_code");
	}
}

//UNCONFIRM PO ========================================================================================================
if(ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'unconfirmed')) {

	$_code	= $_POST['_code'];
	$result = executeSP(ZKP_SQL."_unconfirmedPO", "$\${$_code}$\$");

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_po.php?_code=$_code");
	}
	goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_po.php?_code=$_code");
}
?>