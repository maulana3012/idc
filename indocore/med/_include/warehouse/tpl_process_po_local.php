<?php
//INSERT PO ===========================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . $currentDept . "$currentDept/$moduleDept/index.php", 'insert_PO')) {

	$_ordered_by	= $cboFilter[1][ZKP_URL][0][0];
	$_po_date	 	= $_POST['_po_date'];
	$_po_type		= $_POST['_po_type'];
	$_deli_date		= $_POST['_deli_date'];
	$_sp_code		= strtoupper($_POST['_sp_code']);
	$_sp_name		= $_POST['_sp_name'];
	$_sp_attn		= $_POST['_sp_attn'];
	$_sp_phone		= $_POST['_sp_phone'];
	$_sp_fax		= $_POST['_sp_fax'];
	$_sp_address	= $_POST['_sp_address'];
	$_total_qty		= $_POST['totalQty'];
	$_total_amount	= $_POST['totalAmount'];
	$_vat			= $_POST['vat'];
	$_text_add1		= $_POST['_add_charge1'];
	$_text_add2		= $_POST['_add_charge2'];
	$_total_add1	= $_POST['totalAdd1'];
	$_total_add2	= $_POST['totalAdd2'];
	$_says_in_word	= $_POST['_says_in_word'];
	$_prepared_by	= $_POST['_prepared_by'];
	$_confirmed_by	= $_POST['_confirmed_by'];
	$_approved_by	= $_POST['_approved_by'];
	$_remark		= $_POST['_remark'];

	$_lastupdated_by_account = $S->getValue("ma_account");
	$_revision_time = -1;

	//Item Value
	foreach($_POST['_it_code'] as $val)			$_it_code[]			= $val;
	foreach($_POST['_it_model_no'] as $val)		$_it_model_no[]		= $val;
	foreach($_POST['_it_desc'] as $val)			$_it_desc[]			= $val;
	foreach($_POST['_poit_unit'] as $val)		$_poit_unit[]		= $val;
	foreach($_POST['_poit_unit_price'] as $val)	$_poit_unit_price[] = $val;
	foreach($_POST['_poit_qty'] as $val)		$_poit_qty[] 		= $val;
	foreach($_POST['_poit_remark'] as $val)		$_poit_remark[]		= $val;
	$_it_code		= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_it_model_no	= '$$' . implode('$$,$$', $_it_model_no) . '$$';
	$_it_desc		= '$$' . implode('$$,$$', $_it_desc) . '$$';
	$_poit_unit		= '$$' . implode('$$,$$', $_poit_unit) . '$$';
	$_poit_unit_price = implode(',', $_poit_unit_price);
	$_poit_qty		= implode(',', $_poit_qty);
	$_poit_remark	= '$$' . implode('$$,$$', $_poit_remark) . '$$';

	$result = executeSP(
		ZKP_SQL."_insertPOLocal",
		"'".ZKP_SQL."'",
		$_ordered_by,
		"DATE $\${$_po_date}$\$",
		$_po_type,
		"$\${$_deli_date}$\$",
		"$\${$_sp_code}$\$",
		"$\${$_sp_name}$\$",
		"$\${$_sp_attn}$\$",
		"$\${$_sp_phone}$\$",
		"$\${$_sp_fax}$\$",
		"$\${$_sp_address}$\$",
		$_total_qty,
		$_total_amount,
		$_vat,
		"$\${$_text_add1}$\$",
		"$\${$_text_add2}$\$",
		$_total_add1,
		$_total_add2,
		"$\${$_says_in_word}$\$",
		"$\${$_prepared_by}$\$",
		"$\${$_confirmed_by}$\$",
		"$\${$_approved_by}$\$",
		"$\${$_remark}$\$",
		"$\${$_lastupdated_by_account}$\$",
		"ARRAY[$_it_code]",
		"ARRAY[$_poit_unit]",
		"ARRAY[$_poit_unit_price]",
		"ARRAY[$_poit_qty]",
		"ARRAY[$_poit_remark]"
	);

	if (isZKError($result)) {
		$errMessage = $result->getMessage();
		if(strpos($errMessage, "duplicate key violates")) {
			$result = new ZKError(
				"DUPLICATE_CODE_EXIST",
				"DUPLICATE_CODE_EXIST",
				"Same Code exists, please check your order code again");
		}
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/input_po.php");
	}

	//SAVE PDF FILE
	$_code = $result[0];
	include APP_DIR . "_include/warehouse/pdf/generate_po.php";
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_po.php?_code=".$_code);
}

//UPDATE PO ===========================================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'update_PO')) {

	$_code	 		= $_POST['_code'];
	$_po_date	 	= $_POST['_po_date'];
	$_po_type		= $_POST['_po_type'];
	$_deli_date		= $_POST['_deli_date'];
	$_sp_code		= strtoupper($_POST['_sp_code']);
	$_sp_name		= $_POST['_sp_name'];
	$_sp_attn		= $_POST['_sp_attn'];
	$_sp_phone		= $_POST['_sp_phone'];
	$_sp_fax		= $_POST['_sp_fax'];
	$_sp_address	= $_POST['_sp_address'];
	$_total_qty		= $_POST['totalQty'];
	$_total_amount	= $_POST['totalAmount'];	
	$_vat			= $_POST['vat'];
	$_text_add1		= $_POST['_add_charge1'];
	$_text_add2		= $_POST['_add_charge2'];
	$_total_add1	= $_POST['totalAdd1'];
	$_total_add2	= $_POST['totalAdd2'];
	$_says_in_word	= $_POST['_says_in_word'];
	$_prepared_by	= $_POST['_prepared_by'];
	$_confirmed_by	= $_POST['_confirmed_by'];
	$_approved_by	= $_POST['_approved_by'];
	$_remark		= $_POST['_remark'];

	$_lastupdated_by_account = $S->getValue("ma_account");
	$_revision_time = (int) $_POST['_revesion_time'];

	//Item Value
	foreach($_POST['_it_code'] as $val)			$_it_code[]			= $val;
	foreach($_POST['_it_model_no'] as $val)		$_it_model_no[]		= $val;
	foreach($_POST['_it_desc'] as $val)			$_it_desc[]			= $val;
	foreach($_POST['_poit_unit'] as $val)		$_poit_unit[]		= $val;
	foreach($_POST['_poit_unit_price'] as $val)	$_poit_unit_price[] = $val;
	foreach($_POST['_poit_qty'] as $val)		$_poit_qty[] 		= $val;
	foreach($_POST['_poit_remark'] as $val)		$_poit_remark[]		= $val;

	//make pgsql ARRAY String for many item
	$_it_code		= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_it_model_no	= '$$' . implode('$$,$$', $_it_model_no) . '$$';
	$_it_desc		= '$$' . implode('$$,$$', $_it_desc) . '$$';
	$_poit_unit		= '$$' . implode('$$,$$', $_poit_unit) . '$$';
	$_poit_unit_price = implode(',', $_poit_unit_price);
	$_poit_qty		= implode(',', $_poit_qty);
	$_poit_remark	= '$$' . implode('$$,$$', $_poit_remark) . '$$';

	$result = executeSP(
		ZKP_SQL."_updatePOLocal",
		"$\${$_code}$\$",
		"DATE $\${$_po_date}$\$",
		$_po_type,
		"$\${$_deli_date}$\$",
		"$\${$_sp_code}$\$",
		"$\${$_sp_name}$\$",
		"$\${$_sp_attn}$\$",
		"$\${$_sp_phone}$\$",
		"$\${$_sp_fax}$\$",
		"$\${$_sp_address}$\$",
		$_total_qty,
		$_total_amount,
		$_vat,
		"$\${$_text_add1}$\$",
		"$\${$_text_add2}$\$",
		$_total_add1,
		$_total_add2,
		"$\${$_says_in_word}$\$",
		"$\${$_prepared_by}$\$",
		"$\${$_confirmed_by}$\$",
		"$\${$_approved_by}$\$",
		"$\${$_remark}$\$",
		"$\${$_lastupdated_by_account}$\$",
		$_revision_time,
		"ARRAY[$_it_code]",
		"ARRAY[$_poit_unit]",
		"ARRAY[$_poit_unit_price]",
		"ARRAY[$_poit_qty]",
		"ARRAY[$_poit_remark]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_po.php?_code=$_code");
	}

	//SAVE PDF FILE
	include APP_DIR . "_include/warehouse/pdf/generate_po.php";
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_po.php?_code=$_code");
}

//CONFIRM PO ==========================================================================================================
if (ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/revise_po.php?_code=$_code", 'confirm_PO')) {
	$_code		= $_POST['_code'];
	$_cfm_by_account = $S->getValue('ma_account');

	$result = executeSP(
		ZKP_SQL."_confirmPOLocal",
		"$\${$_code}$\$",
		"$\${$_cfm_by_account}$\$"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_po.php?_code=$_code");
	}

	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_po.php?_code=$_code");
}

//DELETE PO ===========================================================================================================
if (ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'delete_PO')) {

	$_rev		= (int) $_POST['_revesion_time'];
	$_po_date	= date("Ym", strtotime($_POST['_po_date']));
	$_type		= $_POST['_po_type'];

	$result =& query("DELETE FROM ".ZKP_SQL."_tb_po_local WHERE po_code = '$_code'");

	if(isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_po.php?_code=".urlencode($_code));
	} else {
		for ($i = $_rev ; $i >=0 ; $i--) {
			@unlink(PDF_STORAGE . "purchasing/po_local/{$_po_date}/{$_code}_rev_{$i}.pdf");
		}
	}
	goPage(HTTP_DIR . "$currentDept/$moduleDept/summary_po_by_supplier.php");
}
?>