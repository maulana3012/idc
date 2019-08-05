<?php
//============================================================== RECEIVE DATA FROM input_return_step_1.php
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/input_billing_step_1.php", 'order_info')) {

	//VARIABLE
	$title			= array("Issue return invoice &amp; receive item","Issue return invoice only");
	$ordby			= array(1=>'INDOCORE PERKASA', 2=>'MEDIKUS EKA');
	$_ordered_by	= isset($_POST["cboOrdBy"]) ? $_POST["cboOrdBy"] : $_POST["_ordered_by"];
	$_type_return	= empty($_POST['_type']) ? $_POST['_type_return'] : $_POST['_type'];
	$_paper			= $_POST['cboTypeBill'];
	$_return_date	= date("j-M-Y", strtotime($_POST['_return_date']));
	$_received_by	= $_POST['_received_by'];
	$_ship_to_responsible_by = $_POST['_ship_to_responsible_by'];

	$_cus_to		= strtoupper($_POST['_cus_to']);
	$_cus_name		= $_POST['_cus_name'];
	$_cus_attn		= $_POST['_cus_attn'];
	$_cus_npwp		= $_POST['_cus_npwp'];
	$_cus_address 	= $_POST['_cus_address'];
	$_ship_to		= strtoupper($_POST['_ship_to']);
	$_ship_name		= $_POST['_ship_name'];

	$_bill_code		= $_POST['_bill_code'];
	$_book_idx		= $_POST['_book_idx'];
	$_bill_date		= ($_POST['_bill_inv_date'] == '') ? '' : date("j-M-Y", strtotime($_POST['_bill_inv_date']));
	$_is_vat		= empty($_POST['_btnVat']) ? $_POST['_is_vat'] : $_POST['_btnVat'];
	$_vat			= $_POST['_vat'];
	$_faktur_no		= $_POST['_bill_vat_inv_no'];

	$_return_condition	= $_POST['_return_condition'];
	$_is_bill_paid		= empty($_POST['_is_bill_paid']) ? $_POST['_bill_paid'] : $_POST['_is_bill_paid'];
	$_is_money_back		= empty($_POST['_is_money_back']) ? $_POST['_money_back'] : $_POST['_is_money_back'];
	$_is_money_back		= ($_is_money_back == '') ? 0 : $_is_money_back;

	$_do_no			= $_POST['_do_no'];
	$_do_date		= ($_POST['_do_date'] == '') ? '' : date("j-M-Y", strtotime($_POST['_do_date']));
	$_sj_no			= $_POST['_sj_code'];
	$_sj_date		= ($_POST['_sj_date'] == '') ? '' : date("j-M-Y", strtotime($_POST['_sj_date']));
	$_po_no			= $_POST['_po_no'];
	$_po_date		= ($_POST['_po_date'] == '') ? '' : date("j-M-Y", strtotime($_POST['_po_date']));

	//Check valid invoice billing code
	//condition 3, have to has invoice reference
	if($_return_condition == '4') {
		if($_bill_code == "") {
			$o = new ZKError ("BLANK_INVOICE", "BLANK_INVOICE", "<br />You have to fill the invoice reference for return condition 3.");
			$M->goErrorPage($o, HTTP_DIR . "$currentDept/$moduleDept/input_return_step_1.php");
		} else if($_bill_code != "") {
			$sql = "SELECT bill_code, ".ZKP_SQL."_getTurnCode(bill_code,1) AS return_code FROM ".ZKP_SQL."_tb_billing WHERE bill_code = '$_bill_code'";
			$result =& query($sql);

			if (numQueryRows($result) <= 0) {
				$o = new ZKError ("INVALID_INVOICE_CODE", "INVALID_INVOICE_CODE", "<br />The Invoice code <strong>'$_bill_code'</strong> does not exist.<br />You have to input invoice reference code from system.");
				$M->goErrorPage($o, HTTP_DIR . "$currentDept/$moduleDept/input_return_step_1.php");
			}
		}

		$col			 =& fetchRow($result);
		$isIssetBillCode = true;
		$return_code	 = $col[1];

		$sql	= "SELECT book_idx FROM ".ZKP_SQL."_tb_booking WHERE book_idx = $_book_idx";
		$result =& query($sql);
		$col	=& fetchRow($result);
		if (numQueryRows($result) <= 0) $col[0] = 0;
	} else if($_bill_code != "") {
		$sql = "SELECT bill_code, ".ZKP_SQL."_getTurnCode(bill_code,1) AS return_code FROM ".ZKP_SQL."_tb_billing WHERE bill_code = '$_bill_code'";
		$result =& query($sql);

		if (numQueryRows($result) > 0) {
			$col			 =& fetchRow($result);
			$isIssetBillCode = true;
			$return_code	 = $col[1];
		}

		$sql	= "SELECT book_idx FROM ".ZKP_SQL."_tb_booking WHERE book_idx = $_book_idx";
		$result =& query($sql);
		$col	=& fetchRow($result);
	}

	//take discount percentage from customer group
	$sql = "SELECT cug_basic_disc_pct from ".ZKP_SQL."_tb_customer_group WHERE cug_code = (SELECT cug_code FROM ".ZKP_SQL."_tb_customer WHERE cus_code = '$_cus_to')";
	isZKError($res =& query($sql)) ? $M->goErrorPage($res, HTTP_DIR . "$currentDept/$moduleDept/input_return_step_1.php") : false;
	$disc = fetchRow($res);
}

//================================================================================================= INSERT PROCESS
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'insert')) {

	$_dept				= $_POST['_dept'];
	$_ordered_by		= $_POST['_ordered_by'];
	$_paper				= $_POST['_paper'];
	$_type_return		= $_POST['_type_return'];
	$_type_return		= $_POST['_type_return'];
	$_return_date		= $_POST['_return_date'];
	$_received_by		= $_POST['_received_by'];
	$_ship_to_responsible_by	= $_POST['_ship_to_responsible_by'];
	$_return_condition	= $_POST['_return_condition'];

	$_cus_to			= strtoupper($_POST['_cus_to']);
	$_cus_name			= $_POST['_cus_name'];
	$_cus_attn			= $_POST['_cus_attn'];
	$_cus_address		= $_POST['_cus_address'];
	$_cus_npwp			= $_POST['_cus_npwp'];
	$_ship_to			= strtoupper($_POST['_ship_to']);
	$_ship_name			= $_POST['_ship_name'];

	$_bill_code			= $_POST['_bill_code'];
	$_bill_date			= $_POST['_bill_date'];
	$_is_vat			= $_POST['_is_vat'];
	$_vat				= ($_POST['_vat'] == '') ? 0 : $_POST['_vat'];
	$_faktur_no			= $_POST['_faktur_no'];
	$_is_bill_paid		= $_POST['_is_bill_paid'];
	$_is_money_back		= ($_POST['_is_money_back']=='') ? null : $_POST['_is_money_back'];
	$_do_no				= $_POST['_do_no'];
	$_do_date			= $_POST['_do_date'];
	$_sj_no				= $_POST['_sj_no'];
	$_sj_date			= $_POST['_sj_date'];
	$_po_no				= $_POST['_po_no'];
	$_po_date			= $_POST['_po_date'];

	$_lastupdated_by_account = $S->getValue("ma_account");
	$_revision_time		= -1;
	$_disc				= $_POST['_disc'];
	$_total				= $_POST['total'];
	$_total_amount		= $_POST['totalAmount'];

	//delivery option
	$_delivery_by		 = $_POST['_delivery_by'];
	$_delivery_warehouse = $_POST['_delivery_warehouse'];
	$_delivery_franco	 = $_POST['_delivery_franco'];
	$_delivery_freight_charge	= empty($_POST['_delivery_freight_charge'])? 0 : $_POST['_delivery_freight_charge'];

	//payment option
	$_payment_widthin_days	= ($_POST['_payment_widthin_days'] == '') ? 0 : $_POST['_payment_widthin_days'];
	$_payment_closing_on	= $_POST['_payment_closing_on'];
	$_payment_for_the_month_week	= $_POST['_payment_for_the_month_week'];
	$_payment_cash_by		= empty($_POST['_payment_cash_by']) ? '' : $_POST['_payment_cash_by'];
	$_payment_check_by		= empty($_POST['_payment_check_by']) ? '' : $_POST['_payment_check_by'];
	$_payment_transfer_by	= empty($_POST['_payment_transfer_by']) ? '' : $_POST['_payment_transfer_by'];
	$_payment_sj_inv_fp_tender	= $_POST['_payment_sj_inv_fp_tender'];
	$_payment_giro_issue	= $_POST['_payment_giro_issue'];
	$_payment_giro_due		= $_POST['_payment_giro_due'];
	$_bank					= empty($_POST['_bank']) ? '' : $_POST['_bank'];
	$_bank_address 			= $_POST['_bank_address'];
	$_signature_by			= $_POST['_signature_by'];
	$_tukar_faktur_date		= empty($_POST['_tukar_faktur_date']) ? '' : $_POST['_tukar_faktur_date'];
	$_remark				= $_POST['_remark'];

	//Check box
	$_delivery_chk	= 0;
	$_payment_chk	= 0;

	if(isset($_POST['_delivery_chk']) && is_array($_POST['_delivery_chk']))
		foreach($_POST['_delivery_chk'] as $val)	$_delivery_chk = $_delivery_chk + $val;
	if(isset($_POST['_payment_chk']) && is_array($_POST['_payment_chk']))
		foreach($_POST['_payment_chk'] as $val)		$_payment_chk = $_payment_chk + $val;

	//Customer Item
	foreach($_POST['_cus_it_code'] as $val)		 $_cus_it_code[]		= $val;
	foreach($_POST['_cus_it_icat_midx'] as $val) $_cus_it_icat_midx[]	= $val;
	foreach($_POST['_cus_it_model_no'] as $val)	 $_cus_it_model_no[]	= $val;
	foreach($_POST['_cus_it_desc'] as $val)		 $_cus_it_desc[]		= $val;
	foreach($_POST['_cus_it_qty'] as $val)		 $_cus_it_qty[]			= $val;
	foreach($_POST['_cus_it_unit_price'] as $val)$_cus_it_unit_price[]	= $val;
	foreach($_POST['_cus_it_remark'] as $val)	 $_cus_it_remark[]		= $val;
	$_cus_it_code		= '$$' . implode('$$,$$', $_cus_it_code) . '$$';
	$_cus_it_icat_midx	= implode(',', $_cus_it_icat_midx);
	$_cus_it_model_no	= '$$' . implode('$$,$$', $_cus_it_model_no) . '$$';
	$_cus_it_desc		= '$$' . implode('$$,$$', $_cus_it_desc) . '$$';
	$_cus_it_qty		= implode(',', $_cus_it_qty);
	$_cus_it_unit_price	= implode(',', $_cus_it_unit_price);
	$_cus_it_remark		= '$$' . implode('$$,$$', $_cus_it_remark) . '$$';

	//make pgsql ARRAY String for many item
	if($_paper == 0) {
		foreach($_POST['_wh_it_code'] as $val)		 $_wh_it_code[]		= $val;
		foreach($_POST['_wh_it_code_for'] as $val)	 $_wh_it_code_for[]	= $val;
		foreach($_POST['_wh_it_qty'] as $val)		 $_wh_it_qty[]		= $val;
		foreach($_POST['_wh_it_function'] as $val)	 $_wh_it_function[]	= $val;
		foreach($_POST['_wh_it_remark'] as $val)	 $_wh_it_remark[]	= $val;
		$_wh_it_code		= '$$' . implode('$$,$$', $_wh_it_code) . '$$';
		$_wh_it_code_for	= '$$' . implode('$$,$$', $_wh_it_code_for) . '$$';
		$_wh_it_qty			= implode(',', $_wh_it_qty);
		$_wh_it_function	= implode(',', $_wh_it_function);
		$_wh_it_remark		= '$$' . implode('$$,$$', $_wh_it_remark) . '$$';
	} else if($_paper == 1) {
		$_wh_it_code		= '$$$$';
		$_wh_it_code_for	= '$$$$';
		$_wh_it_qty			= 0;
		$_wh_it_function	= 0;
		$_wh_it_remark		= '$$$$';
	}

	$result = executeSP(
		ZKP_SQL."_insertReturnBilling",
		"$\$".ZKP_SQL."$\$",
		"$\${$_dept}$\$",
		$_ordered_by,
		$_paper,
		$_return_condition,
		"$\${$_type_return}$\$",
		"$\${$_return_date}$\$",
		"$\${$_received_by}$\$",
		$_ship_to_responsible_by,
		"$\${$_cus_to}$\$",
		"$\${$_cus_name}$\$",
		"$\${$_cus_attn}$\$",
		"$\${$_cus_address}$\$",
		"$\${$_cus_npwp}$\$",
		"$\${$_ship_to}$\$",
		"$\${$_ship_name}$\$",
		"$\${$_bill_code}$\$",
		"$\${$_bill_date}$\$",
		//$_is_vat,
		null,
		$_vat,
		"$\${$_faktur_no}$\$",
		$_is_bill_paid,
		$_is_money_back,
		"$\${$_do_no}$\$",
		"$\${$_do_date}$\$",
		"$\${$_sj_no}$\$",
		"$\${$_sj_date}$\$",
		"$\${$_po_no}$\$",
		"$\${$_po_date}$\$",
		$_delivery_chk,
		$_payment_chk,
		"$\${$_delivery_by}$\$",
		"$\${$_delivery_warehouse}$\$",
		"$\${$_delivery_franco}$\$",
		$_delivery_freight_charge,
		$_payment_widthin_days,
		"$\${$_payment_sj_inv_fp_tender}$\$",
		"$\${$_payment_closing_on}$\$",
		"$\${$_payment_for_the_month_week}$\$",
		"$\${$_payment_cash_by}$\$",
		"$\${$_payment_check_by}$\$",
		"$\${$_payment_transfer_by}$\$",
		"$\${$_payment_giro_due}$\$",
		"$\${$_payment_giro_issue}$\$",
		"$\${$_bank}$\$",
		"$\${$_bank_address}$\$",
		"$\${$_lastupdated_by_account}$\$",
		$_disc,
		$_total,
		$_total_amount,
		"$\${$_signature_by}$\$",
		"$\${$_tukar_faktur_date}$\$",
		"$\${$_remark}$\$",
		"ARRAY[$_wh_it_code]",
		"ARRAY[$_wh_it_code_for]",
		"ARRAY[$_wh_it_qty]",
		"ARRAY[$_wh_it_function]",
		"ARRAY[$_wh_it_remark]",
		"ARRAY[$_cus_it_code]",
		"ARRAY[$_cus_it_icat_midx]",
		"ARRAY[$_cus_it_model_no]",
		"ARRAY[$_cus_it_desc]",
		"ARRAY[$_cus_it_qty]",
		"ARRAY[$_cus_it_unit_price]",
		"ARRAY[$_cus_it_remark]"
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
	} else {
		$_code = substr($result[0],0,11);
		$_std_idx = substr($result[0],12);
		include APP_DIR . "_include/billing/pdf/generate_return.php";
		$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_return.php?_code=".$_code);
	}
}

//================================================================================================= UPDATE PROCESS
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'update')) {

	$_code						 = $_POST['_code'];
	$_ordered_by				 = $_POST['_ordered_by'];
	$_bill_code					 = $_POST['_bill_code'];
	$_bill_date					 = $_POST['_bill_date'];
	$_faktur_no					 = $_POST['_bill_vat_inv_no'];
	$_std_idx					 = $_POST['_std_idx'];
	$_inc_idx					 = $_POST['_inc_idx'];
	$_paper						 = $_POST['_paper'];
	$_return_condition			 = $_POST['_return_condition'];
	$_dept						 = $_POST['_dept'];
	$_return_date				 = $_POST['_return_date'];
	$_received_by				 = $_POST['_received_by'];
	$_lastupdated_by_account	 = $S->getValue("ma_account");
	$_revision_time				 = $_POST['_revision_time'];
	$_sj_code					 = $_POST['_sj_code'];
	$_sj_date					 = $_POST['_sj_date'];
	$_po_no						 = $_POST['_po_no'];
	$_po_date					 = $_POST['_po_date'];
	$_disc						 = $_POST['_disc'];
	$_vat						 = $_POST['_vat_value'];
	$_old_total_amount			 = $_POST['_old_total_amount'];
	$_total_before_vat			 = $_POST['total'];
	$_total_return				 = $_POST['totalAmount'];
	$_cus_to					 = $_POST['_cus_to'];
	$_cus_name					 = $_POST['_cus_name'];
	$_cus_attn					 = $_POST['_cus_attn'];
	$_cus_address				 = $_POST['_cus_address'];
	$_cus_npwp					 = $_POST['_cus_npwp'];
	$_ship_to					 = $_POST['_ship_to'];
	$_ship_name					 = $_POST['_ship_name'];
	$_delivery_by				 = $_POST['_delivery_by'];
	$_delivery_warehouse		 = $_POST['_delivery_warehouse'];
	$_delivery_franco			 = $_POST['_delivery_franco'];
	$_delivery_freight_charge	 = (isset($_POST['_delivery_freight_charge'])) ? $_POST['_delivery_freight_charge'] : 0;
	$_payment_widthin_days		 = $_POST['_payment_widthin_days'];
	$_payment_sj_inv_fp_tender	 = $_POST['_payment_sj_inv_fp_tender'];
	$_payment_closing_on		 = $_POST['_payment_closing_on'];
	$_payment_for_the_month_week = $_POST['_payment_for_the_month_week'];
	$_payment_cash_by			 = $_POST['_payment_cash_by'];
	$_payment_check_by			 = $_POST['_payment_check_by'];
	$_payment_transfer_by		 = empty($_POST['_payment_transfer_by']) ? "" : $_POST['_payment_transfer_by'];
	$_payment_giro_due			 = $_POST['_payment_giro_due'];
	$_payment_giro_issue		 = $_POST['_payment_giro_issue'];
	$_bank						 = empty($_POST['_bank']) ? "" : $_POST['_bank'];
	$_bank_address				 = $_POST['_bank_address'];
	$_signature_by				 = $_POST['_signature_by'];
	$_tukar_faktur_date			 = empty($_POST['_tukar_faktur_date']) ? "" : $_POST['_tukar_faktur_date'];
	$_remark					 = $_POST['_remark'];

	//Check box
	$_delivery_chk	= 0;
	$_payment_chk	= 0;

	if(isset($_POST['_delivery_chk']) && is_array($_POST['_delivery_chk']))
		foreach($_POST['_delivery_chk'] as $val)	$_delivery_chk = $_delivery_chk + $val;
	if(isset($_POST['_payment_chk']) && is_array($_POST['_payment_chk']))
		foreach($_POST['_payment_chk'] as $val)		$_payment_chk = $_payment_chk + $val;

	//Customer Item
	foreach($_POST['_cus_it_code'] as $val)		 $_cus_it_code[]		= $val;
	foreach($_POST['_cus_it_icat_midx'] as $val) $_cus_it_icat_midx[]	= $val;
	foreach($_POST['_cus_it_model_no'] as $val)	 $_cus_it_model_no[]	= $val;
	foreach($_POST['_cus_it_desc'] as $val)		 $_cus_it_desc[]		= $val;
	foreach($_POST['_cus_it_qty'] as $val)		 $_cus_it_qty[]			= $val;
	foreach($_POST['_cus_it_unit_price'] as $val)$_cus_it_unit_price[]	= $val;
	foreach($_POST['_cus_it_remark'] as $val)	 $_cus_it_remark[]		= $val;
	$_cus_it_code		= '$$' . implode('$$,$$', $_cus_it_code) . '$$';
	$_cus_it_icat_midx	= implode(',', $_cus_it_icat_midx);
	$_cus_it_model_no	= '$$' . implode('$$,$$', $_cus_it_model_no) . '$$';
	$_cus_it_desc		= '$$' . implode('$$,$$', $_cus_it_desc) . '$$';
	$_cus_it_qty		= implode(',', $_cus_it_qty);
	$_cus_it_unit_price	= implode(',', $_cus_it_unit_price);
	$_cus_it_remark		= '$$' . implode('$$,$$', $_cus_it_remark) . '$$';

	//Warehouse Item
	if($_paper == 0) {
		foreach($_POST['_wh_it_code'] as $val)		 $_wh_it_code[]		= $val;
		foreach($_POST['_wh_it_code_for'] as $val)	 $_wh_it_code_for[]	= $val;
		foreach($_POST['_wh_it_qty'] as $val)		 $_wh_it_qty[]		= $val;
		foreach($_POST['_wh_it_function'] as $val)	 $_wh_it_function[]	= $val;
		foreach($_POST['_wh_it_remark'] as $val)	 $_wh_it_remark[]	= $val;
		$_wh_it_code		= '$$' . implode('$$,$$', $_wh_it_code) . '$$';
		$_wh_it_code_for	= '$$' . implode('$$,$$', $_wh_it_code_for) . '$$';
		$_wh_it_qty			= implode(',', $_wh_it_qty);
		$_wh_it_function	= implode(',', $_wh_it_function);
		$_wh_it_remark		= '$$' . implode('$$,$$', $_wh_it_remark) . '$$';
	} else if($_paper == 1) {
		$_wh_it_code		= '$$$$';
		$_wh_it_code_for	= '$$$$';
		$_wh_it_qty			= 0;
		$_wh_it_function	= 0;
		$_wh_it_remark		= '$$$$';
	}

	//reviseReturnBilling
	$result = executeSP(
		ZKP_SQL."_updateReturnBilling",
			"$\${$_code}$\$",
			$_paper,
			"$\${$_bill_code}$\$",
			$_std_idx,
			$_inc_idx,
			$_return_condition,
			"$\${$_dept}$\$",
			"$\${$_return_date}$\$",
			"$\${$_received_by}$\$",
			"$\${$_lastupdated_by_account}$\$",
			$_revision_time,
			"$\${$_sj_code}$\$",
			"$\${$_sj_date}$\$",
			"$\${$_po_no}$\$",
			"$\${$_po_date}$\$",
			$_disc,
			$_vat,
			$_old_total_amount,
			$_total_before_vat,
			$_total_return,
			"$\${$_cus_to}$\$",
			"$\${$_cus_name}$\$",
			"$\${$_cus_attn}$\$",
			"$\${$_cus_address}$\$",
			"$\${$_cus_npwp}$\$",
			"$\${$_ship_to}$\$",
			"$\${$_ship_name}$\$",
			$_delivery_chk,
			"$\${$_delivery_by}$\$",
			"$\${$_delivery_warehouse}$\$",
			"$\${$_delivery_franco}$\$",
			$_delivery_freight_charge,
			$_payment_chk,
			$_payment_widthin_days,
			"$\${$_payment_sj_inv_fp_tender}$\$",
			"$\${$_payment_closing_on}$\$",
			"$\${$_payment_for_the_month_week}$\$",
			"$\${$_payment_cash_by}$\$",
			"$\${$_payment_check_by}$\$",
			"$\${$_payment_transfer_by}$\$",
			"$\${$_payment_giro_due}$\$",
			"$\${$_payment_giro_issue}$\$",
			"$\${$_bank}$\$",
			"$\${$_bank_address}$\$",
			"$\${$_signature_by}$\$",
			"$\${$_tukar_faktur_date}$\$",
			"$\${$_remark}$\$",
			"ARRAY[$_wh_it_code]",
			"ARRAY[$_wh_it_code_for]",
			"ARRAY[$_wh_it_qty]",
			"ARRAY[$_wh_it_function]",
			"ARRAY[$_wh_it_remark]",
			"ARRAY[$_cus_it_code]",
			"ARRAY[$_cus_it_icat_midx]",
			"ARRAY[$_cus_it_model_no]",
			"ARRAY[$_cus_it_desc]",
			"ARRAY[$_cus_it_qty]",
			"ARRAY[$_cus_it_unit_price]",
			"ARRAY[$_cus_it_remark]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_return.php?_code=".urlencode($_code));
	}

	//SAVE PDF FILE
	include APP_DIR . "_include/billing/pdf/generate_return.php";
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_return.php?_code=".urlencode($_code));

}

//================================================================================================= DELETE PROCESS
if (ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'delete')) {

	$_code				= $_POST['_code'];
	$_paper				= $_POST['_paper'];
	$_bill_code			= $_POST['_bill_code'];
	$_std_idx			= $_POST['_std_idx'];
	$_inc_idx			= $_POST['_inc_idx'];
	$_return_condition	= $_POST['_return_condition'];
	$_cus_to			= $_POST['_cus_to'];
	$_ship_to			= $_POST['_ship_to'];
	$_total_return		= $_POST['_total_return'];
	$_rev 		  		= (int) $_POST['_revision_time'];
	$_return_date 		= date("Ym", strtotime($_POST['_return_date']));
	$_total_return		= $_POST['totalAmount'];

	//deleteReturnBilling
	$result = executeSP(
		ZKP_SQL."_deleteReturnBilling",
		"$\${$_code}$\$",
		$_paper,
		"$\${$_bill_code}$\$",
		$_std_idx,
		$_inc_idx,
		$_return_condition,
		"$\${$_cus_to}$\$",
		"$\${$_ship_to}$\$",
		$_total_return
	);
	
	if(isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_return.php?_code=".urlencode($_code));
	} else {
		for ($i = $_rev ; $i >=0 ; $i--) {
			@unlink(PDF_STORAGE . "billing/$currentDept/{$_return_date}/{$_code}_rev_{$i}.pdf");
		}
	}
	goPage(HTTP_DIR . $currentDept . '/summary/daily_billing_by_group.php?cboInv=R');
}
?>