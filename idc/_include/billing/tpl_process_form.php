<?php
//RECEIVE VARIABLE ====================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/input_billing_step_1.php", 'order_info')) {

	//VARIABLE
	$title		= array(1=>"Issue Invoice &amp; booking Item","Issue invoice only","Issue invoice &amp; linked item from sales report");
	$ordby		= array(1=>'INDOCORE PERKASA', 2=>'MEDIKUS EKA');
	$_ordered_by	= ($_POST['_order_by'] != '' ) ? $_POST['_order_by'] : $_POST['cboOrdBy'];
	$_type_bill	= $_POST['cboTypeBill'];
	$_dept		= $_POST['_dept'];
	$_received_by	= $_POST['_received_by'];
	$_inv_date	= date("j-M-Y", strtotime($_POST['_inv_date']));
	$_do_no		= empty($_POST['_do_no']) ? '' : $_POST['_do_no'];
	$_do_date	= empty($_POST['_do_date']) ? '' : date("j-M-Y", strtotime($_POST['_do_date']));
	$_chk_sj_code	= empty($_POST['chkSjCode']) ? '' : $_POST['chkSjCode'];
	$_sj_code	= empty($_POST['_sj_code']) ? '' : $_POST['_sj_code'];
	$_sj_date	= empty($_POST['_sj_date']) ? '' : date("j-M-Y", strtotime($_POST['_sj_date']));
	$_po_no		= $_POST['_po_no'];
	$_po_date	= ($_POST['_po_date']=="") ? '' : date("j-M-Y", strtotime($_POST['_po_date']));
	$_btnVat	= $_POST['_btnVat'];
	$_vat		= empty($_POST['_vat_val']) ? '' : $_POST['_vat_val'];
	$_type_of_pajak	= empty($_POST['_type_of_pajak']) ? '' : $_POST['_type_of_pajak'];
	$_ship_to_responsible_by = $_POST['_ship_to_responsible_by'];

	$_cug_code	= $_POST['_cug_code'];
	$_cus_to	= strtoupper($_POST['_cus_to']);
	$_cus_name	= $_POST['_cus_name'];
	$_cus_attn	= $_POST['_cus_attn'];
	$_cus_npwp	= $_POST['_cus_npwp'];
	$_cus_address	= $_POST['_cus_address'];
	$_ship_to	= strtoupper($_POST['_ship_to']);
	$_ship_name	= $_POST['_ship_name'];
	$_pajak_to	= empty($_POST['_pajak_to']) ? '' : $_POST['_pajak_to'];
	$_pajak_name	= empty($_POST['_pajak_name']) ? '' : $_POST['_pajak_name'];
	$_pajak_address	= empty($_POST['_pajak_address']) ? '' : $_POST['_pajak_address'];

	//take discount percentage from customer group
	$sql = "SELECT cug_basic_disc_pct from ".ZKP_SQL."_tb_customer_group WHERE cug_code = (SELECT cug_code FROM ".ZKP_SQL."_tb_customer WHERE cus_code = '$_cus_to')";
	isZKError($res =& query($sql)) ? $M->goErrorPage($res, HTTP_DIR . "$currentDept/$moduleDept/input_billing_step_1.php") : false;
	$disc = fetchRow($res);
}


//INSERT BILLING ======================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'insert_billing')) {
	// BILLING INFORMATION
	$_ordered_by		= $_POST['_ordered_by'];
	$_type_bill		= $_POST['_type_bill'];
	$_type_invoice		= ($_type_bill == '1') ? 0 : 1; // 0.for booking item , 1.isssue number only
	$_dept			= $_POST['_dept'];
	$_received_by		= $_POST['_received_by'];
	$_inv_date		= date("j-M-Y", strtotime($_POST['_inv_date']));
	$_do_no			= empty($_POST['_do_no']) ? '' : $_POST['_do_no'];
	$_do_date		= empty($_POST['_do_date']) ? '' : date("j-M-Y", strtotime($_POST['_do_date']));
	$_chk_sj_code		= $_POST['_chk_sj_code'];	
	$_sj_code		= empty($_POST['_sj_code']) ? '' : $_POST['_sj_code'];
	$_sj_date		= empty($_POST['_sj_date']) ? '' : date("j-M-Y", strtotime($_POST['_sj_date']));
	$_po_no			= $_POST['_po_no'];
	$_po_date		= $_POST['_po_date'];
	$_is_vat		= $_POST['_is_vat'];
	$_vat_val		= $_POST['_vat_val'];
	$_is_tax		= $_POST['_is_tax'];
	$_ship_to_responsible_by	= $_POST['_ship_to_responsible_by'];
	$_cug_code		= $_POST['_cug_code'];
	$_cus_to		= strtoupper($_POST['_cus_to']);
	$_cus_name		= $_POST['_cus_name'];
	$_cus_attn		= $_POST['_cus_attn'];
	$_cus_npwp		= $_POST['_cus_npwp'];
	$_cus_address		= $_POST['_cus_address'];
	$_ship_to		= strtoupper($_POST['_ship_to']);
	$_ship_name		= $_POST['_ship_name'];
	$_pajak_to		= $_POST['_pajak_to'];
	$_pajak_name		= $_POST['_pajak_name'];
	$_pajak_address		= $_POST['_pajak_address'];
	$_disc			= $_POST['_disc'];
	$_total_amount		= $_POST['totalAmount'];
	$_amount_before_vat	= $_POST['total2'];
	$_revision_time 	= -1; // will be 0 at the print time
	$_lastupdated_by_account = ucfirst($S->getValue("ma_account"));

	// CONDITION
	$_delivery_by		 	= $_POST['_delivery_by'];
	$_delivery_warehouse 		= $_POST['_delivery_warehouse'];
	$_delivery_franco	 	= $_POST['_delivery_franco'];
	$_delivery_freight_charge	= empty($_POST['_delivery_freight_charge'])? 0 : $_POST['_delivery_freight_charge'];
	$_payment_widthin_days		= $_POST['_payment_widthin_days'];
	$_payment_closing_on		= $_POST['_payment_closing_on'];
	$_payment_for_the_month_week	= $_POST['_payment_for_the_month_week'];
	$_payment_cash_by		= empty($_POST['_payment_cash_by']) ? '' : $_POST['_payment_cash_by'];
	$_payment_check_by		= empty($_POST['_payment_check_by']) ? '' : $_POST['_payment_check_by'];
	$_payment_transfer_by		= empty($_POST['_payment_transfer_by']) ? '' : $_POST['_payment_transfer_by'];
	$_payment_sj_inv_fp_tender	= $_POST['_payment_sj_inv_fp_tender'];
	$_payment_giro_issue		= $_POST['_payment_giro_issue'];
	$_payment_giro_due		= $_POST['_payment_giro_due'];
	$_tukar_faktur_date		= empty($_POST['_tukar_faktur_date']) ? '' : $_POST['_tukar_faktur_date'];
	$_bank				= empty($_POST['_bank']) ? '' : $_POST['_bank'];
	$_bank_address 			= $_POST['_bank_address'];
	$_is_cons			= isset($_POST['_is_cons']) ? (($_POST['_is_cons'] == 't') ? 'true' : 'false') : 'false';
	$_sales_from			= isset($_POST['_sales_from']) ? $_POST['_sales_from'] : '';
	$_sales_to			= isset($_POST['_sales_to']) ? $_POST['_sales_to'] : '';
	$_signature_by			= $_POST['_signature_by'];
	$_signature_pajak_by		= empty($_POST['_signature_pajak_by']) ? '' : $_POST['_signature_pajak_by'];
	$_paper_format			= $_POST['_paper_format'];
	$_remark			= $_POST['_remark'];

	//Check box
	$_delivery_chk	= 0;
	$_payment_chk	= 0;
	if(isset($_POST['_delivery_chk']) && is_array($_POST['_delivery_chk']))
		foreach($_POST['_delivery_chk'] as $val)	$_delivery_chk = $_delivery_chk + $val;
	if(isset($_POST['_payment_chk']) && is_array($_POST['_payment_chk']))
		foreach($_POST['_payment_chk'] as $val)		$_payment_chk = $_payment_chk + $val;

	// ITEM LIST
	foreach($_POST['_cus_it_code'] as $val)		 $_cus_it_code[]		= $val;
	foreach($_POST['_cus_it_model_no'] as $val)	 $_cus_it_model_no[]	= $val;
	foreach($_POST['_cus_it_desc'] as $val)		 $_cus_it_desc[]		= $val;
	foreach($_POST['_cus_it_qty'] as $val)		 $_cus_it_qty[]			= $val;
	foreach($_POST['_cus_it_unit_price'] as $val)$_cus_it_unit_price[]	= $val;
	foreach($_POST['_cus_it_remark'] as $val)	 $_cus_it_remark[]		= $val;

	$_cus_it_code		= '$$' . implode('$$,$$', $_cus_it_code) . '$$';
	$_cus_it_model_no	= '$$' . implode('$$,$$', $_cus_it_model_no) . '$$';
	$_cus_it_desc		= '$$' . implode('$$,$$', $_cus_it_desc) . '$$';
	$_cus_it_qty		= implode(',', $_cus_it_qty);
	$_cus_it_unit_price	= implode(',', $_cus_it_unit_price);
	$_cus_it_remark		= '$$' . implode('$$,$$', $_cus_it_remark) . '$$';
	$_cus_it_sl_idx		= '$$$$';
	$_wh_it_code		= '$$$$';
	$_wh_it_code_for	= '$$$$';
	$_wh_it_qty			= 0;
	$_wh_it_function	= 0;
	$_wh_it_remark		= '$$$$';
	$_sl_date			= '$$$$';
	$_sl_cus_code		= '$$$$';
	$_sl_cus_name		= '$$$$';
	$_sl_faktur_no		= '$$$$';
	$_sl_lop_no			= '$$$$';
	$_sl_amount			= 0;

	if($_type_bill == '1') {
		foreach($_POST['_wh_it_code'] as $val)		 $_zwh_it_code[]		= $val;
		foreach($_POST['_wh_it_code_for'] as $val)	 $_zwh_it_code_for[]	= $val;
		foreach($_POST['_wh_it_qty'] as $val)		 $_zwh_it_qty[]			= $val;
		foreach($_POST['_wh_it_function'] as $val)	 $_zwh_it_function[]	= $val;
		foreach($_POST['_wh_it_remark'] as $val)	 $_zwh_it_remark[]		= $val;
		$_wh_it_code		= '$$' . implode('$$,$$', $_zwh_it_code) . '$$';
		$_wh_it_code_for	= '$$' . implode('$$,$$', $_zwh_it_code_for) . '$$';
		$_wh_it_qty			= implode(',', $_zwh_it_qty);
		$_wh_it_function	= implode(',', $_zwh_it_function);
		$_wh_it_remark		= '$$' . implode('$$,$$', $_zwh_it_remark) . '$$';
	} else if($_type_bill == '3') {
		foreach($_POST['_sl_date'] as $val)			$_zsl_date[]		= $val;
		foreach($_POST['_sl_cus_code'] as $val)		$_zsl_cus_code[]	= $val;
		foreach($_POST['_sl_cus_name'] as $val)		$_zsl_cus_name[]	= $val;
		foreach($_POST['_sl_faktur_no'] as $val)	$_zsl_faktur_no[]	= $val;
		foreach($_POST['_sl_lop_no'] as $val)		$_zsl_lop_no[]		= $val;
		foreach($_POST['_sl_amount'] as $val)	 	$_zsl_amount[]		= $val;
		foreach($_POST['_cus_it_sl_idx'] as $val)	$_zcus_it_sl_idx[]	= $val;
		$_sl_date		= '$$' . implode('$$,$$', $_zsl_date) . '$$';
		$_sl_cus_code	= '$$' . implode('$$,$$', $_zsl_cus_code) . '$$';
		$_sl_cus_name	= '$$' . implode('$$,$$', $_zsl_cus_name) . '$$';
		$_sl_faktur_no	= '$$' . implode('$$,$$', $_zsl_faktur_no) . '$$';
		$_sl_lop_no		= '$$' . implode('$$,$$', $_zsl_lop_no) . '$$';
		$_sl_amount		= implode(',', $_zsl_amount);
		$_cus_it_sl_idx	= '$$' . implode('$$,$$', $_zcus_it_sl_idx) . '$$';
	}

	$result = executeSP(
		ZKP_SQL."_insertBilling",
		"$\$".ZKP_SQL."$\$", $_ordered_by, $_type_bill, $_type_invoice, "$\${$_dept}$\$", $_revision_time, "$\${$_lastupdated_by_account}$\$",
		"$\${$_received_by}$\$", "$\${$_inv_date}$\$", "$\${$_do_no}$\$", "$\${$_do_date}$\$",
		"$\${$_chk_sj_code}$\$", "$\${$_sj_code}$\$", "$\${$_sj_date}$\$",
		"$\${$_po_no}$\$", "$\${$_po_date}$\$",
		"$\${$_is_vat}$\$", $_vat_val, "$\${$_is_tax}$\$",$_ship_to_responsible_by,
		"$\${$_cug_code}$\$", "$\${$_cus_to}$\$", "$\${$_cus_name}$\$", "$\${$_cus_attn}$\$", "$\${$_cus_npwp}$\$", "$\${$_cus_address}$\$",
		"$\${$_ship_to}$\$", "$\${$_ship_name}$\$",
		"$\${$_pajak_to}$\$", "$\${$_pajak_name}$\$", "$\${$_pajak_address}$\$",
		$_disc, $_total_amount, $_amount_before_vat,
		$_delivery_chk, "$\${$_delivery_by}$\$", "$\${$_delivery_warehouse}$\$", "$\${$_delivery_franco}$\$", $_delivery_freight_charge,
		$_payment_chk, $_payment_widthin_days, "$\${$_payment_sj_inv_fp_tender}$\$",
		"$\${$_payment_closing_on}$\$", "$\${$_payment_for_the_month_week}$\$",
		"$\${$_payment_cash_by}$\$", "$\${$_payment_check_by}$\$", "$\${$_payment_transfer_by}$\$",
		"$\${$_payment_giro_due}$\$", "$\${$_payment_giro_issue}$\$",
		"$\${$_bank}$\$", "$\${$_bank_address}$\$", "$\${$_tukar_faktur_date}$\$",
		"$\${$_signature_by}$\$", "$\${$_signature_pajak_by}$\$",
		"$\${$_paper_format}$\$", "$\${$_is_cons}$\$", "$\${$_sales_from}$\$", "$\${$_sales_to}$\$", "$\${$_remark}$\$",
		"ARRAY[$_wh_it_code]", "ARRAY[$_wh_it_code_for]", "ARRAY[$_wh_it_qty]", "ARRAY[$_wh_it_function]", "ARRAY[$_wh_it_remark]",
		"ARRAY[$_sl_date]", "ARRAY[$_sl_cus_code]", "ARRAY[$_sl_cus_name]", "ARRAY[$_sl_faktur_no]", "ARRAY[$_sl_lop_no]", "ARRAY[$_sl_amount]",
		"ARRAY[$_cus_it_code]", "ARRAY[$_cus_it_model_no]", "ARRAY[$_cus_it_desc]", "ARRAY[$_cus_it_qty]", 
		"ARRAY[$_cus_it_unit_price]", "ARRAY[$_cus_it_remark]", "ARRAY[$_cus_it_sl_idx]"
	);

	if (isZKError($result)) {
		$errMessage = $result->getMessage();
		if(strpos($errMessage, "duplicate key violates")) {
			$result = new ZKError(
				"DUPLICATE_CODE_EXIST",
				"DUPLICATE_CODE_EXIST",
				"Same Code exists, please check your order code again");
		}
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/index.php");
	} else if(isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/input_billing_step_1.php");
	}

	$_code = substr($result[0],0,13);
	$_book_idx = substr($result[0],14);
	include APP_DIR . "_include/billing/pdf/generate_billing.php";
	$M->goPage(HTTP_DIR . "$currentDept/billing/revise_billing.php?_code=".$_code);
}

//DELETE BILLING ======================================================================================================
if (ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'delete_billing')) {

	$_code		= $_POST['_code'];
	$_book_idx	= $_POST['_book_idx'];
	$_inv_date	= date("Ym", strtotime($_POST['_inv_date']));
	$_rev		= (int) $_POST['_revision_time'];

	$result = executeSP(ZKP_SQL."_deleteBilling","$\${$_code}$\$", $_book_idx, "false", "$\$$\$", "$\$$\$");

	if(isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_billing.php?_code=".urlencode($_code));
	} else {
		for ($i = $_rev ; $i >=0 ; $i--) {
			@unlink(PDF_STORAGE . "billing/{$currentDept}/{$_inv_date}/{$_code}_rev_{$i}.pdf");
		}
	}
	goPage(HTTP_DIR . $currentDept . '/summary/daily_billing_by_group.php');
}

//DELETE BILLING PAJAK ================================================================================================
if (ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'delete_billing_pajak')) {

	$_rev		= (int) $_POST['_revision_time'];
	$_book_idx	= ($_POST['_book_idx']<1) ? 0 : $_POST['_book_idx'];
	$_inv_date	= date("Ym", strtotime($_POST['_inv_date']));
	$_admin_account	 = $_POST["_account"];
	$_admin_password = md5($_POST["_password"]);

	//deleteBilling
	$result = executeSP(
		ZKP_SQL."_deleteBilling",
		"$\${$_code}$\$",
		$_book_idx,
		"true",
		$_admin_account,
		"$\${$_admin_password}$\$"
	);

	if(isZKError($result)) {
		$errMessage = $result->getMessage();
		if(strpos($errMessage, "FAIL_TO_AUTH")) {
			$result = new ZKError(
				"FAIL_TO_AUTHORITY",
				"FAIL_TO_AUTHORITY",
				"Your input wrong password, please try again. Also check [Caps Lock] Key");
		}
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/delete_billing.php?_code=".urlencode($_code));
	} else {
		for ($i = $_rev ; $i >=0 ; $i--) {
			@unlink(PDF_STORAGE . "billing/{$currentDept}/{$_inv_date}/{$_code}_rev_{$i}.pdf");
		}
	}
	goPage(HTTP_DIR . "$currentDept/summary/daily_billing_by_group.php");
}

//UPDATE BILLING ======================================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'update_billing')) {

	$_code				= $_POST['_code'];
	$_ordered_by		= $_POST['_ordered_by'];
	$_type_bill			= $_POST['_type_bill'];
	$_type_invoice		= $_POST['_type_invoice'];
	$_type_template		= $_POST['_type_template'];
	$_book_idx			= $_POST['_book_idx'];
	$_dept				= $_POST['_dept'];
	$_is_vat			= $_POST['_is_vat'];
	$_vat_val			= $_POST['_vat_val'];
	$_is_tax			= $_POST['_is_tax'];
	$_inv_date			= $_POST['_inv_date'];
	$_received_by		= $_POST['_received_by'];
	$_ship_to_responsible_by	= $_POST['_ship_to_responsible_by'];
	$_do_no				= empty($_POST['_do_no']) ? "" : $_POST['_do_no'];
	$_do_date			= empty($_POST['_do_date']) ? "" : $_POST['_do_date'];
	$_po_no				= $_POST['_po_no'];
	$_po_date			= $_POST['_po_date'];
	$_sj_code			= empty($_POST['_sj_code']) ? "" : $_POST['_sj_code'];
	$_sj_date			= empty($_POST['_sj_date']) ? "" : $_POST['_sj_date'];
	$_lastupdated_by_account = $S->getValue("ma_account");
	$_revision_time		= (int) $_POST['_revision_time'];

	$_cus_to			= strtoupper($_POST['_cus_to']);
	$_cus_name			= $_POST['_cus_name'];
	$_cus_attn			= $_POST['_cus_attn'];
	$_cus_address		= $_POST['_cus_address'];
	$_cus_npwp			= $_POST['_cus_npwp'];
	$_ship_to			= strtoupper($_POST['_ship_to']);
	$_ship_name			= $_POST['_ship_name'];
	$_pajak_to			= strtoupper($_POST['_pajak_to']);
	$_pajak_name		= $_POST['_pajak_name'];
	$_pajak_address		= $_POST['_pajak_address'];
	$_is_cons			= (isset($_POST['_is_cons']) && $_POST['_is_cons'] == 't') ? 'true' : 'false';
	$_sales_from		= isset($_POST['_sales_from']) ? $_POST['_sales_from'] : '';
	$_sales_to			= isset($_POST['_sales_to']) ? $_POST['_sales_to'] : '';
	$_disc				= $_POST['_disc'];
	$_total_amount		= $_POST['totalAmount'];
	$_amount_before_vat	= $_POST['total2'];

	//delivery option
	$_delivery_by				= $_POST['_delivery_by'];
	$_delivery_warehouse 		= $_POST['_delivery_warehouse'];
	$_delivery_franco			= $_POST['_delivery_franco'];
	$_delivery_freight_charge	= empty($_POST['_delivery_freight_charge'])? 0 : $_POST['_delivery_freight_charge'];

	//payment option
	$_payment_widthin_days		= $_POST['_payment_widthin_days'];
	$_payment_closing_on		= $_POST['_payment_closing_on'];
	$_payment_for_the_month_week= $_POST['_payment_for_the_month_week'];
	$_payment_cash_by			= empty($_POST['_payment_cash_by']) ? '' : $_POST['_payment_cash_by'];
	$_payment_check_by			= empty($_POST['_payment_check_by']) ? '' : $_POST['_payment_check_by'] ;
	$_payment_transfer_by		= empty($_POST['_payment_transfer_by']) ? '' : $_POST['_payment_transfer_by'];
	$_payment_sj_inv_fp_tender	= empty($_POST['_payment_sj_inv_fp_tender']) ? 'Tukar Faktur' : $_POST['_payment_sj_inv_fp_tender'];
	$_payment_giro_issue		= $_POST['_payment_giro_issue'];
	$_payment_giro_due			= $_POST['_payment_giro_due'];
	$_bank						= empty($_POST['_bank']) ? '' : $_POST['_bank'];
	$_bank_address 				= $_POST['_bank_address'];
	$_signature_by				= $_POST['_signature_by'];
	$_signature_pajak_by		= empty($_POST['_signature_pajak_by']) ? '' : $_POST['_signature_pajak_by'];
	$_paper_format				= $_POST['_paper_format'];
	$_tukar_faktur_date			= empty($_POST['_tukar_faktur_date']) ? '' : $_POST['_tukar_faktur_date'];
	$_remark					= $_POST['_remark'];
	$_note						= $_POST['_note'];

	//Check box
	$_delivery_chk	= 0;
	$_payment_chk	= 0;

	if(isset($_POST['_delivery_chk']) && is_array($_POST['_delivery_chk']))
		foreach($_POST['_delivery_chk'] as $val)	$_delivery_chk = $_delivery_chk + $val;
	if(isset($_POST['_payment_chk']) && is_array($_POST['_payment_chk']))
		foreach($_POST['_payment_chk'] as $val)		$_payment_chk = $_payment_chk + $val;

	// ITEM LIST
	foreach($_POST['_cus_it_code'] as $val)		 $_cus_it_code[]		= $val;
	foreach($_POST['_cus_it_model_no'] as $val)	 $_cus_it_model_no[]	= $val;
	foreach($_POST['_cus_it_desc'] as $val)		 $_cus_it_desc[]		= $val;
	foreach($_POST['_cus_it_qty'] as $val)		 $_cus_it_qty[]			= $val;
	foreach($_POST['_cus_it_unit_price'] as $val)$_cus_it_unit_price[]	= $val;
	foreach($_POST['_cus_it_remark'] as $val)	 $_cus_it_remark[]		= $val;

	$_cus_it_code		= '$$' . implode('$$,$$', $_cus_it_code) . '$$';
	$_cus_it_model_no	= '$$' . implode('$$,$$', $_cus_it_model_no) . '$$';
	$_cus_it_desc		= '$$' . implode('$$,$$', $_cus_it_desc) . '$$';
	$_cus_it_qty		= implode(',', $_cus_it_qty);
	$_cus_it_unit_price	= implode(',', $_cus_it_unit_price);
	$_cus_it_remark		= '$$' . implode('$$,$$', $_cus_it_remark) . '$$';
	$_cus_it_sl_idx		= '$$$$';
	$_wh_it_code		= '$$$$';
	$_wh_it_code_for	= '$$$$';
	$_wh_it_qty			= 0;
	$_wh_it_function	= 0;
	$_wh_it_remark		= '$$$$';
	$_sl_date			= '$$$$';
	$_sl_cus_code		= '$$$$';
	$_sl_cus_name		= '$$$$';
	$_sl_faktur_no		= '$$$$';
	$_sl_lop_no			= '$$$$';
	$_sl_amount			= 0;

	if($_type_template == '1') {
		foreach($_POST['_wh_it_code'] as $val)		 $_zwh_it_code[]		= $val;
		foreach($_POST['_wh_it_code_for'] as $val)	 $_zwh_it_code_for[]	= $val;
		foreach($_POST['_wh_it_qty'] as $val)		 $_zwh_it_qty[]			= $val;
		foreach($_POST['_wh_it_function'] as $val)	 $_zwh_it_function[]	= $val;
		foreach($_POST['_wh_it_remark'] as $val)	 $_zwh_it_remark[]		= $val;
		$_wh_it_code		= '$$' . implode('$$,$$', $_zwh_it_code) . '$$';
		$_wh_it_code_for	= '$$' . implode('$$,$$', $_zwh_it_code_for) . '$$';
		$_wh_it_qty			= implode(',', $_zwh_it_qty);
		$_wh_it_function	= implode(',', $_zwh_it_function);
		$_wh_it_remark		= '$$' . implode('$$,$$', $_zwh_it_remark) . '$$';
	} else if($_type_template == '3') {
		foreach($_POST['_sl_date'] as $val)			$_zsl_date[]		= $val;
		foreach($_POST['_sl_cus_code'] as $val)		$_zsl_cus_code[]	= $val;
		foreach($_POST['_sl_cus_name'] as $val)		$_zsl_cus_name[]	= $val;
		foreach($_POST['_sl_faktur_no'] as $val)	$_zsl_faktur_no[]	= $val;
		foreach($_POST['_sl_lop_no'] as $val)		$_zsl_lop_no[]		= $val;
		foreach($_POST['_sl_amount'] as $val)	 	$_zsl_amount[]		= $val;
		foreach($_POST['_cus_it_sl_idx'] as $val)	$_zcus_it_sl_idx[]	= $val;
		$_sl_date		= '$$' . implode('$$,$$', $_zsl_date) . '$$';
		$_sl_cus_code	= '$$' . implode('$$,$$', $_zsl_cus_code) . '$$';
		$_sl_cus_name	= '$$' . implode('$$,$$', $_zsl_cus_name) . '$$';
		$_sl_faktur_no	= '$$' . implode('$$,$$', $_zsl_faktur_no) . '$$';
		$_sl_lop_no		= '$$' . implode('$$,$$', $_zsl_lop_no) . '$$';
		$_sl_amount		= implode(',', $_zsl_amount);
		$_cus_it_sl_idx	= '$$' . implode('$$,$$', $_zcus_it_sl_idx) . '$$';
	}
if($_code != 'IO-01457A-B15') {
	$result = executeSP(
		ZKP_SQL."_updateBilling",
		"$\${$_code}$\$",$_type_bill, $_type_invoice, $_type_template, $_book_idx, "$\${$_dept}$\$", $_revision_time, "$\${$_lastupdated_by_account}$\$",
		"$\${$_received_by}$\$", $_ship_to_responsible_by, "$\${$_inv_date}$\$", "$\${$_do_no}$\$", "$\${$_do_date}$\$",
		"$\${$_sj_code}$\$", "$\${$_sj_date}$\$", "$\${$_po_no}$\$", "$\${$_po_date}$\$",
		"$\${$_cus_to}$\$", "$\${$_cus_name}$\$", "$\${$_cus_attn}$\$", "$\${$_cus_npwp}$\$", "$\${$_cus_address}$\$",
		"$\${$_ship_to}$\$", "$\${$_ship_name}$\$", "$\${$_pajak_to}$\$", "$\${$_pajak_name}$\$", "$\${$_pajak_address}$\$",
		$_disc, $_total_amount, $_amount_before_vat,
		$_delivery_chk, "$\${$_delivery_by}$\$", "$\${$_delivery_warehouse}$\$", "$\${$_delivery_franco}$\$", $_delivery_freight_charge,
		$_payment_chk, $_payment_widthin_days, "$\${$_payment_sj_inv_fp_tender}$\$",
		"$\${$_payment_closing_on}$\$", "$\${$_payment_for_the_month_week}$\$",
		"$\${$_payment_cash_by}$\$", "$\${$_payment_check_by}$\$", "$\${$_payment_transfer_by}$\$",
		"$\${$_payment_giro_due}$\$", "$\${$_payment_giro_issue}$\$",
		"$\${$_bank}$\$", "$\${$_bank_address}$\$", "$\${$_tukar_faktur_date}$\$",
		"$\${$_signature_by}$\$", "$\${$_signature_pajak_by}$\$",
		"$\${$_paper_format}$\$", "$\${$_is_cons}$\$", "$\${$_sales_from}$\$", "$\${$_sales_to}$\$", "$\${$_remark}$\$", "$\${$_note}$\$",
		"ARRAY[$_wh_it_code]", "ARRAY[$_wh_it_code_for]", "ARRAY[$_wh_it_qty]", "ARRAY[$_wh_it_function]", "ARRAY[$_wh_it_remark]",
		"ARRAY[$_sl_date]", "ARRAY[$_sl_cus_code]", "ARRAY[$_sl_cus_name]", "ARRAY[$_sl_faktur_no]", "ARRAY[$_sl_lop_no]", "ARRAY[$_sl_amount]",
		"ARRAY[$_cus_it_code]", "ARRAY[$_cus_it_model_no]", "ARRAY[$_cus_it_desc]", "ARRAY[$_cus_it_qty]", 
		"ARRAY[$_cus_it_unit_price]", "ARRAY[$_cus_it_remark]", "ARRAY[$_cus_it_sl_idx]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_billing.php?_code=".urlencode($_code));
	}
} else {
	$_revision_time = $_revision_time - 1;
}
	//SAVE PDF FILE
	include APP_DIR . "_include/billing/pdf/generate_billing.php";
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_billing.php?_code=".urlencode($_code));
}

//UPDATE BILLING AFTER OUTGOING ITEM ===================================================================================
if(ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'update_billing_revised')) {

	$_code			= $_POST['_code'];
	$_ordered_by		= $_POST['_ordered_by'];
	$_type_bill		= $_POST['_type_bill'];
	$_type_invoice		= $_POST['_type_invoice'];
	$_type_template		= $_POST['_type_template'];
	$_book_idx		= $_POST['_book_idx'];
	$_dept			= $_POST['_dept'];
	$_is_vat		= $_POST['_is_vat'];
	$_vat_val		= $_POST['_vat_val'];
	$_is_tax		= $_POST['_is_tax'];
	$_inv_date		= $_POST['_inv_date'];
	$_received_by		= $_POST['_received_by'];
	$_ship_to_responsible_by= $_POST['_ship_to_responsible_by'];
	$_do_no			= empty($_POST['_do_no']) ? "" : $_POST['_do_no'];
	$_do_date		= empty($_POST['_do_date']) ? "" : $_POST['_do_date'];
	$_po_no			= $_POST['_po_no'];
	$_po_date		= $_POST['_po_date'];
	$_sj_code		= empty($_POST['_sj_code']) ? "" : $_POST['_sj_code'];
	$_sj_date		= empty($_POST['_sj_date']) ? "" : $_POST['_sj_date'];
	$_lastupdated_by_account= $S->getValue("ma_account");
	$_revision_time		= (int) $_POST['_revision_time'];

	$_cus_to		= strtoupper($_POST['_cus_to']);
	$_cus_name		= $_POST['_cus_name'];
	$_cus_attn		= $_POST['_cus_attn'];
	$_cus_address		= $_POST['_cus_address'];
	$_cus_npwp		= $_POST['_cus_npwp'];
	$_ship_to		= strtoupper($_POST['_ship_to']);
	$_ship_name		= $_POST['_ship_name'];
	$_pajak_to		= strtoupper($_POST['_pajak_to']);
	$_pajak_name		= $_POST['_pajak_name'];
	$_pajak_address		= $_POST['_pajak_address'];
	$_is_cons		= (isset($_POST['_is_cons']) && $_POST['_is_cons'] == 't') ? 'true' : 'false';
	$_sales_from		= isset($_POST['_sales_from']) ? $_POST['_sales_from'] : '';
	$_sales_to		= isset($_POST['_sales_to']) ? $_POST['_sales_to'] : '';
	$_disc			= $_POST['_disc'];
	$_total_amount		= $_POST['totalAmount'];
	$_amount_before_vat	= $_POST['total2'];

	//delivery option
	$_delivery_by			= $_POST['_delivery_by'];
	$_delivery_warehouse 		= $_POST['_delivery_warehouse'];
	$_delivery_franco		= $_POST['_delivery_franco'];
	$_delivery_freight_charge	= empty($_POST['_delivery_freight_charge'])? 0 : $_POST['_delivery_freight_charge'];

	//payment option
	$_payment_widthin_days		= $_POST['_payment_widthin_days'];
	$_payment_closing_on		= $_POST['_payment_closing_on'];
	$_payment_for_the_month_week	= $_POST['_payment_for_the_month_week'];
	$_payment_cash_by		= empty($_POST['_payment_cash_by']) ? '' : $_POST['_payment_cash_by'];
	$_payment_check_by		= empty($_POST['_payment_check_by']) ? '' : $_POST['_payment_check_by'] ;
	$_payment_transfer_by		= empty($_POST['_payment_transfer_by']) ? '' : $_POST['_payment_transfer_by'];
	$_payment_sj_inv_fp_tender	= empty($_POST['_payment_sj_inv_fp_tender']) ? 'Tukar Faktur' : $_POST['_payment_sj_inv_fp_tender'];
	$_payment_giro_issue		= $_POST['_payment_giro_issue'];
	$_payment_giro_due		= $_POST['_payment_giro_due'];
	$_bank				= empty($_POST['_bank']) ? '' : $_POST['_bank'];
	$_bank_address 			= $_POST['_bank_address'];
	$_signature_by			= $_POST['_signature_by'];
	$_signature_pajak_by		= empty($_POST['_signature_pajak_by']) ? '' : $_POST['_signature_pajak_by'];
	$_paper_format			= $_POST['_paper_format'];
	$_tukar_faktur_date		= empty($_POST['_tukar_faktur_date']) ? '' : $_POST['_tukar_faktur_date'];
	$_remark			= $_POST['_remark'];

	//Check box
	$_delivery_chk	= 0;
	$_payment_chk	= 0;

	if(isset($_POST['_delivery_chk']) && is_array($_POST['_delivery_chk']))
		foreach($_POST['_delivery_chk'] as $val) $_delivery_chk = $_delivery_chk + $val;
	if(isset($_POST['_payment_chk']) && is_array($_POST['_payment_chk']))
		foreach($_POST['_payment_chk'] as $val) $_payment_chk = $_payment_chk + $val;

	// ITEM LIST
	foreach($_POST['_cus_it_code'] as $val)		 $_cus_it_code[]	= trim($val);
	foreach($_POST['_cus_it_model_no'] as $val)	 $_cus_it_model_no[]	= $val;
	foreach($_POST['_cus_it_desc'] as $val)		 $_cus_it_desc[]	= $val;
	foreach($_POST['_cus_it_qty'] as $val)		 $_cus_it_qty[]		= $val;
	foreach($_POST['_cus_it_unit_price'] as $val)	 $_cus_it_unit_price[]	= $val;
	foreach($_POST['_cus_it_remark'] as $val)	 $_cus_it_remark[]	= $val;

	$_cus_it_code		= '$$' . implode('$$,$$', $_cus_it_code) . '$$';
	$_cus_it_model_no	= '$$' . implode('$$,$$', $_cus_it_model_no) . '$$';
	$_cus_it_desc		= '$$' . implode('$$,$$', $_cus_it_desc) . '$$';
	$_cus_it_qty		= implode(',', $_cus_it_qty);
	$_cus_it_unit_price	= implode(',', $_cus_it_unit_price);
	$_cus_it_remark		= '$$' . implode('$$,$$', $_cus_it_remark) . '$$';
	$_cus_it_sl_idx		= '$$$$';
	$_wh_it_code		= '$$$$';
	$_wh_it_code_for	= '$$$$';
	$_wh_it_qty		= 0;
	$_wh_it_function	= 0;
	$_wh_it_remark		= '$$$$';
	$_sl_date		= '$$$$';
	$_sl_cus_code		= '$$$$';
	$_sl_cus_name		= '$$$$';
	$_sl_faktur_no		= '$$$$';
	$_sl_lop_no		= '$$$$';
	$_sl_amount		= 0;

	foreach($_POST['_wh_it_code'] as $val)		 $_zwh_it_code[]	= trim($val);
	foreach($_POST['_wh_it_code_for'] as $val)	 $_zwh_it_code_for[]	= $val;
	foreach($_POST['_wh_it_qty'] as $val)		 $_zwh_it_qty[]		= $val;
	foreach($_POST['_wh_it_function'] as $val)	 $_zwh_it_function[]	= $val;
	foreach($_POST['_wh_it_remark'] as $val)	 $_zwh_it_remark[]	= $val;
	$_wh_it_code		= '$$' . implode('$$,$$', $_zwh_it_code) . '$$';
	$_wh_it_code_for	= '$$' . implode('$$,$$', $_zwh_it_code_for) . '$$';
	$_wh_it_qty		= implode(',', $_zwh_it_qty);
	$_wh_it_function	= implode(',', $_zwh_it_function);
	$_wh_it_remark		= '$$' . implode('$$,$$', $_zwh_it_remark) . '$$';

	$_rcp = get_diff_data(array($_POST['_wh_it_code'], $_POST['_wh_it_qty'], $_POST['_out_it_code'], $_POST['_out_it_qty']));

	if(empty($_rcp['item'])) {
		$message = new ZKError(
			"NO_DIFFERENT_ITEM",
			"NO_DIFFERENT_ITEM",
			"There is no different item and qty. Please check again");
		$M->goErrorPage($message,  HTTP_DIR . "$currentDept/$moduleDept/revise_billing_2.php?_code=".urlencode($_code));
	}

	$result = executeSP(
		ZKP_SQL."_updateBillingRevised",
		"$\${$_code}$\$",$_type_bill, $_type_invoice, $_type_template, $_book_idx, "$\${$_dept}$\$", $_revision_time, "$\${$_lastupdated_by_account}$\$",
		"$\${$_received_by}$\$", $_ship_to_responsible_by, "$\${$_inv_date}$\$", "$\${$_do_no}$\$", "$\${$_do_date}$\$",
		"$\${$_sj_code}$\$", "$\${$_sj_date}$\$", "$\${$_po_no}$\$", "$\${$_po_date}$\$",
		"$\${$_cus_to}$\$", "$\${$_cus_name}$\$", "$\${$_cus_attn}$\$", "$\${$_cus_npwp}$\$", "$\${$_cus_address}$\$",
		"$\${$_ship_to}$\$", "$\${$_ship_name}$\$", "$\${$_pajak_to}$\$", "$\${$_pajak_name}$\$", "$\${$_pajak_address}$\$",
		$_disc, $_total_amount, $_amount_before_vat,
		$_delivery_chk, "$\${$_delivery_by}$\$", "$\${$_delivery_warehouse}$\$", "$\${$_delivery_franco}$\$", $_delivery_freight_charge,
		$_payment_chk, $_payment_widthin_days, "$\${$_payment_sj_inv_fp_tender}$\$",
		"$\${$_payment_closing_on}$\$", "$\${$_payment_for_the_month_week}$\$",
		"$\${$_payment_cash_by}$\$", "$\${$_payment_check_by}$\$", "$\${$_payment_transfer_by}$\$",
		"$\${$_payment_giro_due}$\$", "$\${$_payment_giro_issue}$\$",
		"$\${$_bank}$\$", "$\${$_bank_address}$\$", "$\${$_tukar_faktur_date}$\$",
		"$\${$_signature_by}$\$", "$\${$_signature_pajak_by}$\$",
		"$\${$_paper_format}$\$", "$\${$_is_cons}$\$", "$\${$_sales_from}$\$", "$\${$_sales_to}$\$", "$\${$_remark}$\$",
		"ARRAY[$_wh_it_code]", "ARRAY[$_wh_it_code_for]", "ARRAY[$_wh_it_qty]", "ARRAY[$_wh_it_function]", "ARRAY[$_wh_it_remark]",
		"ARRAY[$_sl_date]", "ARRAY[$_sl_cus_code]", "ARRAY[$_sl_cus_name]", "ARRAY[$_sl_faktur_no]", "ARRAY[$_sl_lop_no]", "ARRAY[$_sl_amount]",
		"ARRAY[$_cus_it_code]", "ARRAY[$_cus_it_model_no]", "ARRAY[$_cus_it_desc]", "ARRAY[$_cus_it_qty]", 
		"ARRAY[$_cus_it_unit_price]", "ARRAY[$_cus_it_remark]", "ARRAY[$_cus_it_sl_idx]",
		"ARRAY[".$_rcp['item']."]", "ARRAY[".$_rcp['qty']."]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_billing.php?_code=".urlencode($_code));
	}

	//SAVE PDF FILE
	include APP_DIR . "_include/billing/pdf/generate_billing.php";
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_billing.php?_code=".urlencode($_code));
}

//DELETE PAYMENT DEDUCTION ============================================================================================
if (ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'delete_payment_deduction')) {
	$_depa_idx	= $_POST['_depa_idx'];
	$result = query("DELETE FROM ".ZKP_SQL."_tb_payment_deduction WHERE pade_idx=$_depa_idx; ");
	if (isZKError($result)) $M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");
	goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_billing.php?_code=".urlencode($_code));
}

//DELETE PAYMENT ======================================================================================================
if (ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'delete_payment')) {
	$_pay_idx	= $_POST['_pay_idx'];
	$_pay_ref	= $_POST['_pay_ref'];	
	$result = query("
					DELETE FROM ".ZKP_SQL."_tb_payment WHERE pay_idx=$_pay_idx;
					DELETE FROM ".ZKP_SQL."_tb_payment WHERE pay_is_deposit_cross_ref=$_pay_ref;
					UPDATE ".ZKP_SQL."_tb_payment SET pay_is_deposit_cross=false WHERE pay_idx=$_pay_ref;
			  ");
	if (isZKError($result)) $M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");
	goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_billing.php?_code=".urlencode($_code));
}

//INSERT PAYMENT ======================================================================================================
if (ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'add_payment')) {

	$_code				= $_POST['_code'];
	$_dept				= $_POST['_dept'];
	$_cus_code	  		= strtoupper($_POST['_cus_code']);
	$_payment_date		= $_POST['_payment_date'];
	$_payment_paid		= $_POST['_payment_paid'];
	$_payment_paid_delivery	= isset($_POST['_payment_paid_delivery']) ? $_POST['_payment_paid_delivery'] : 0;
	$_remain_amount		= $_POST['_remain_amount'];
	$_payment_remark	= $_POST['_payment_remark'];
	$_method			= $_POST['_method'];
	$_bank				= $_POST['_bank'];
	$_inputed_by		= $S->getValue('ma_account');

	if(isset($_POST['_deduction_desc'])) {
		foreach($_POST['_deduction_type'] as $val)		$_deduction_type[]		= $val;
		foreach($_POST['_deduction_desc'] as $val)		$_deduction_desc[]		= $val;
		foreach($_POST['_deduction_amount'] as $val)	$_deduction_amount[]	= $val;
		$_deduction_type	= implode(',', $_deduction_type);
		$_deduction_desc	= '$$' . implode('$$,$$', $_deduction_desc) . '$$';
		$_deduction_amount	= implode(',', $_deduction_amount);
	} else {
		$_deduction_type	= 0;
		$_deduction_desc	= '$$$$';
		$_deduction_amount	= 0;
	}

	$result = executeSP(
		ZKP_SQL."_addNewPayment",
		"$\${$_code}$\$",
		"$\${$_cus_code}$\$",
		"$\${$_payment_date}$\$",
		$_payment_paid,
		$_payment_paid_delivery,
		$_remain_amount,
		"$\${$_payment_remark}$\$",
		"$\${$_inputed_by}$\$",
		"$\${$_method}$\$",
		"$\${$_bank}$\$",
		"$\${$_dept}$\$",
		"ARRAY[$_deduction_type]",
		"ARRAY[$_deduction_desc]",
		"ARRAY[$_deduction_amount]"		
	);

	if (isZKError($result)) $M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_billing.php?_code=".urlencode($_code));
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_billing.php?_code=".urlencode($_code));
}

//EDIT PAYMENT / CROSS TRANSFER =======================================================================================
if (ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'edit_payment')) {

	$_code				= $_POST['_code'];
	$_payment_date		= $_POST['_payment_date'];
	$_method			= $_POST['_method'];
	$_bank				= isset($_POST['_bank']) ? $_POST['_bank'] : "";
	$_remark			= $_POST['_remark'];
	$_inputed_by		= $S->getValue('ma_account');

	foreach($_POST['chkPayIdx'] as $val)	 $chkPayIdx[]		= $val;
	$chkPayIdx = implode(',', $chkPayIdx);

	$result = executeSP(
		ZKP_SQL."_addCrossTransfer",
		"$\${$_code}$\$",
		"$\${$_payment_date}$\$",
		"$\${$_method}$\$",
		"$\${$_bank}$\$",
		"$\${$_remark}$\$",
		"$\${$_inputed_by}$\$",
		"ARRAY[$chkPayIdx]"
	);

	if (isZKError($result)) $M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_billing.php?_code=".urlencode($_code));
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_billing.php?_code=".urlencode($_code));
}

//CONFIRM TUKAR FAKTUR ================================================================================================
if (ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'cfm_tukar_faktur')) {

	$_code		  = $_POST['_code'];
	$_tukar_faktur_date = $_POST['_tukar_faktur_date'];
	$_cfm_tukar_faktur_by	= $S->getValue("ma_account");
	$_due_date    = $_POST['_due_date'];

	$result = executeSP(
		ZKP_SQL."_cfmTukarFaktur",
		"$\${$_code}$\$",
		"$\${$_tukar_faktur_date}$\$",
		"$\${$_cfm_tukar_faktur_by}$\$",
		"$\${$_due_date}$\$"
	);

	if (isZKError($result)) $M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");
	goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_billing.php?_code=".urlencode($_code));
}

//CONFIRM DELIVERY CHARGRE ============================================================================================
if (ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'cfm_delivery_charge')) {

	$_code		  		= $_POST['_code'];
	$_delivery_charge	= $_POST['_delivery_charge'];
	$_cfm_delivery_by	= $S->getValue("ma_account");

	$result = executeSP(
		ZKP_SQL."_cfmDeliveryCharge",
		"$\${$_code}$\$",
		$_delivery_charge,
		"$\${$_cfm_delivery_by}$\$"
	);

	if (isZKError($result)) $M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");
	goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_billing.php?_code=".urlencode($_code));
}

//UNCONFIRM DELIVERY CHARGE ===========================================================================================
if (ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'uncfm_delivery_charge')) {

	$_code		  		= $_POST['_code'];
	$_is_confirm		= $_POST['_is_confirm'];
	$_deli_charge		= ($_POST['_deli_charge'] == '') ? 0.00 : $_POST['_deli_charge'];
	$_cfm_delivery_by	= $S->getValue("ma_account");

	$result = executeSP(
		ZKP_SQL."_unCfmDeliveryCharge",
		"$\${$_code}$\$",
		$_is_confirm,
		$_deli_charge,
		"$\${$_cfm_delivery_by}$\$"
	);

	if (isZKError($result)) $M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");
	goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_billing.php?_code=".urlencode($_code));
}

//CONFIRM DELIVERY ====================================================================================================
if (ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'cfm_delivery')) {

	$_code		  = $_POST['_code'];
	$_delivery_date	= $_POST['_delivery_date'];
	$_delivery_by	= $_POST['_delivery_by'];
	$_confirm_by	= $S->getValue("ma_account");

	$result = executeSP(
		ZKP_SQL."_cfmDelivery",
		"$\${$_code}$\$",
		"$\${$_delivery_date}$\$",
		"$\${$_delivery_by}$\$",
		"$\${$_confirm_by}$\$"
	);

	if (isZKError($result)) $M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");
	goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_billing.php?_code=".urlencode($_code));
}

//CONFIRM FIX BILLING ============================================================================================
if (ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'cfm_billing_only')) {

	$_code		= $_POST['_code'];
	$_bill_date	= $_POST['_bill_date'];
	$_wh_date	= $_POST['_wh_date'];
	$_cfm_wh_by	= $S->getValue("ma_account");

	$result = executeSP(
		ZKP_SQL."_cfmBillingOnly",
		"$\${$_code}$\$",
		"$\${$_bill_date}$\$",
		"$\${$_wh_date}$\$",
		"$\${$_cfm_wh_by}$\$"
	);

	if (isZKError($result)) {
		$errMessage = $result->getMessage();
		if(strpos($errMessage, "ERR_TYPE_NONVAT_INVOICE")) {
			$result = new ZKError(
				"ERR_TYPE_NONVAT_INVOICE",
				"ERR_TYPE_NONVAT_INVOICE",
				"This is not a vat invoice!");
		} else if(strpos($errMessage, "ERR_TYPE_INVOICE")) {
			$result = new ZKError(
				"ERR_TYPE_INVOICE",
				"ERR_TYPE_INVOICE",
				"This is not a no only invoice!");
		}
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/index.php");
	}
	goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_billing.php?_code=".urlencode($_code));
}

//UNCONFIRM FIX BILLING ============================================================================================
if (ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'uncfm_billing_only')) {

	$_code		= $_POST['_code'];

	$result = executeSP(
		ZKP_SQL."_uncfmBillingOnly",
		"$\${$_code}$\$"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/index.php");
	}
	goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_billing.php?_code=".urlencode($_code));
}

//MOVE BILLING ========================================================================================================
if(ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'move_billing')) {

	$_old_code			= $_POST['_old_code'];
	$_rev				= (int) $_POST['_revision_time'];
	$_inv_date			= date("Y-m-d", strtotime($_POST['_inv_date']));
	$_old_dept			= $_POST['_old_dept'];
	$_new_dept			= $_POST['_new_dept'];
	$_old_type_invoice	= $_POST['_old_type_invoice'];
	$_new_type_invoice	= $_POST['_new_type_invoice'];
	$_updated_by		= $S->getValue("ma_account");

	if($_new_dept == 'A') 		$_new_code = substr($_old_code,0,8).'A'.substr($_old_code,9);
	else if($_new_dept == 'D')	$_new_code = substr($_old_code,0,8).'D'.substr($_old_code,9);
	else if($_new_dept == 'H')	$_new_code = substr($_old_code,0,8).'H'.substr($_old_code,9);
	else if($_new_dept == 'M')	$_new_code = substr($_old_code,0,8).'M'.substr($_old_code,9);
	else if($_new_dept == 'P')	$_new_code = substr($_old_code,0,8).'P'.substr($_old_code,9);
	else if($_new_dept == 'T')	$_new_code = substr($_old_code,0,8).'T'.substr($_old_code,9);
	else if($_new_dept == 'S')  $_new_code = substr($_old_code,0,8).'S'.substr($_old_code,9);

	if($_old_dept == $_new_dept)
	{
		if($_old_type_invoice == $_new_type_invoice)	
		{
			$result = new ZKError(
				"NO_DIFFERENT_MOVEMENT",
				"NO_DIFFERENT_MOVEMENT",
				"There is no different movement. Please choose different department or type!");
			$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/change_dept.php?_code=$_old_code");
		}
	}

	//move bill_code
	$result = executeSP(
		ZKP_SQL."_moveBillingCode",
		"$\${$_old_code}$\$",
		"$\${$_new_code}$\$",
		"$\${$_old_dept}$\$",
		"$\${$_new_dept}$\$",
		$_old_type_invoice,
		$_new_type_invoice,
		"$\${$_inv_date}$\$",
		"$\${$_updated_by}$\$"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");
	} else {
		for ($i = $_rev ; $i >=0 ; $i--) {
			//@unlink(PDF_STORAGE . "billing/$currentDept/{$_inv_date}/{$_old_code}_rev_{$i}.pdf");
		}
		if($_new_dept == 'A') 		$M->goPage(HTTP_DIR . "apotik/billing/revise_billing.php?_code=".$_new_code);
		else if($_new_dept == 'D')	$M->goPage(HTTP_DIR . "dealer/billing/revise_billing.php?_code=".$_new_code);
		else if($_new_dept == 'H')	$M->goPage(HTTP_DIR . "hospital/billing/revise_billing.php?_code=".$_new_code);
		else if($_new_dept == 'M')	$M->goPage(HTTP_DIR . "marketing/billing/revise_billing.php?_code=".$_new_code);
		else if($_new_dept == 'P')	$M->goPage(HTTP_DIR . "pharmaceutical/billing/revise_billing.php?_code=".$_new_code);
		else if($_new_dept == 'T')	$M->goPage(HTTP_DIR . "tender/billing/revise_billing.php?_code=".$_new_code);
		else if($_new_dept == 'S')  $M->goPage(HTTP_DIR . "sales/billing/revise_billing.php?_code=".$_new_code);
	}
}

//INSERT ATTACHMENT ========================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'upload_file')) {

    $_code = $_POST['_code'];
    $_date = $_POST['_date'];
    $_fp_no = isset($_POST['_fp_no']) ? $_POST['_fp_no'] : "";    
    $_inputted_by_account = ucfirst($S->getValue("ma_account"));
    
    if($_POST['Faktur_Pajak'] == 'Faktur Pajak'){ 
        $valid_type = array ('application/pdf');
        $storage = USER_DATA . "archieve/pajak/$currentDept/". date("Ym/", strtotime($_date));
        $path = "/pajak/$currentDept/". date("Ym/", strtotime($_date));
    } else { 
        $valid_type = array ('image/png','image/jpeg','image/gif', 'image/jpg', 'image/pjpeg');
        $storage = USER_DATA . "archieve/$moduleDept/$currentDept/". date("Ym/", strtotime($_date));
        $path = "/$moduleDept/$currentDept/". date("Ym/", strtotime($_date));
    }    
    (!is_dir($storage)) ? mkdir($storage, 0777, true) : 0;

    // Attachment
    for($i=0; $i<count($_FILES["_file"]["name"]); $i++) {
        if($_POST['Faktur_Pajak'] == 'Faktur Pajak') { 
            if($_POST['Perbaikan'] == 'fpb') {
                $_change = substr($_fp_no,4);
                $file_name = $_code . "___011." . $_change . ".pdf";
            } else {
                $file_name = $_code . "___" . $_fp_no . ".pdf";
            }
            
        } else {
            $file_name = $_code . "_" . time() . "_" . $_FILES["_file"]["name"][$i];
        }

        // Check validity type and size
        if ((in_array($_FILES["_file"]["type"][$i], $valid_type, true))
            && $_FILES["_file"]["size"][$i] < 200000)
        {
            if(move_uploaded_file($_FILES["_file"]["tmp_name"][$i], $storage . $file_name))
            {
                if($_POST['Faktur_Pajak'] == 'Faktur Pajak' || $_POST['Faktur_Pajak'] == 'Faktur Pajak Rev') { 
                    $_file['name'][$i] = $file_name;
                } else {
                    $_file['name'][$i] = $_FILES["_file"]["name"][$i];
                }
                
                $_file['path'][$i] = $path . $file_name;
                $_file['type'][$i] = $_FILES["_file"]["type"][$i];
            }
            $_file['type'][$i] = $_POST["cboType"][$i];
            $_file['desc'][$i] = $_POST["_file_remark"][$i];
        }
    }

    $_file['name']    = '$$' . implode('$$,$$', $_file['name']) . '$$';
    $_file['path']     = '$$' . implode('$$,$$', $_file['path']) . '$$';
    $_file['type']    = '$$' . implode('$$,$$', $_file['type']) . '$$';
    $_file['desc']    = '$$' . implode('$$,$$', $_file['desc']) . '$$';
/*echo "<pre>";
var_dump($_POST);
exit;*/
    $result = executeSP(
        ZKP_SQL."_uploadFileArchieve",
        "$\$Billing$\$",
        "$\${$_code}$\$",
        "$\${$_inputted_by_account}$\$",
        "ARRAY[".$_file['name']."]",
        "ARRAY[".$_file['path']."]",
        "ARRAY[".$_file['type']."]",
        "ARRAY[".$_file['desc']."]"
    );

    if (isZKError($result))
    {
        $M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_billing.php?_code=".$_code);
    }

    $M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_billing.php?_code=".$_code);

}

//DELETE ATTACHMENT ========================================================================================================
if(ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'upload_file_delete')) {

    $_code = $_POST['_code'];
    $_idx = $_POST['_idx'];
    $_idx_path = $_POST['_idx_path'];

    $sql = "DELETE FROM ".ZKP_SQL."_tb_billing_file WHERE bill_code='$_code' AND billf_idx=$_idx ";

    if(isZKError($result =& query($sql)))
    {
        $M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_billing.php?_code=".$_code);
    }

    $file_name = USER_DATA . "archieve/" . $_idx_path;
    @unlink($file_name);

    $M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_billing.php?_code=".$_code);
}
?>