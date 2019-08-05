<?php
//RECEIVE DATA ========================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/input_dt_step_1.php", 'do_info')) {

	//VARIABLE
	$_do_type		= $_POST['_do_type'];
	$_do_date		= date('d-M-Y', strtotime($_POST['_do_date']));
	$_issued_by		= $_POST['_issued_by'];
	$_issued_date	= ($_POST['_issued_date']=='')?'':date('d-M-Y', strtotime($_POST['_issued_date']));
	$_received_by	= $_POST['_received_by'];
	$_type_item		= $_POST['_type_item'];

	$_cus_to		= strtoupper($_POST['_cus_to']);
	$_cus_name		= $_POST['_cus_name'];
	$_cus_address	= $_POST['_cus_address'];
	$_ship_to		= strtoupper($_POST['_ship_to']);
	$_ship_name		= $_POST['_ship_name'];

	$_turn_code		= $_POST['_turn_code'];
	$_turn_date		= ($_POST['_turn_date']=='')?'':date('d-M-Y', strtotime($_POST['_turn_date']));

}

//PROCESS INSERT ======================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'insert')) {

	$_dept					= $_POST['_dept'];
	$_ordered_by			= $_POST['_ordered_by'];
	$_do_type				= $_POST['_do_type'];
	$_do_date				= $_POST['_do_date'];
	$_issued_by				= $_POST['_issued_by'];
	$_issued_date			= $_POST['_issued_date'];
	$_received_by			= $_POST['_received_by'];
	$_type_item				= $_POST['_type_item'];
	$_cus_to				= strtoupper($_POST['_cus_to']);
	$_cus_name				= $_POST['_cus_name'];
	$_cus_address			= $_POST['_cus_address'];
	$_ship_to				= strtoupper($_POST['_ship_to']);
	$_ship_name				= $_POST['_ship_name'];
	$_turn_code				= $_POST['_turn_code'];
	$_turn_date				= $_POST['_turn_date'];
	$_lastupdated_by_account 	= $S->getValue("ma_account");
	$_revision_time 			= -1;

	$_cus_to			= strtoupper($_POST['_cus_to']);
	$_cus_name			= $_POST['_cus_name'];
	$_cus_address		= $_POST['_cus_address'];
	$_ship_to			= strtoupper($_POST['_ship_to']);
	$_ship_name			= $_POST['_ship_name'];

	$_delivery_by		 		= $_POST['_delivery_by'];
	$_delivery_warehouse 		= $_POST['_delivery_warehouse'];
	$_delivery_franco	 		= $_POST['_delivery_franco'];
	$_delivery_freight_charge	= empty($_POST['_delivery_freight_charge'])? 0 : $_POST['_delivery_freight_charge'];
	$_remark					= $_POST['_remark'];

	//Item Value
	foreach($_POST['_wh_it_code'] as $val)		 $_wh_it_code[]		= $val;
	foreach($_POST['_wh_it_code_for'] as $val)	 $_wh_it_code_for[]	= $val;
	foreach($_POST['_wh_it_qty'] as $val)		 $_wh_it_qty[]		= $val;
	foreach($_POST['_wh_it_function'] as $val)	 $_wh_it_function[]	= $val;
	foreach($_POST['_wh_it_remark'] as $val)	 $_wh_it_remark[]	= $val;
	foreach($_POST['_cus_it_code'] as $val)		 $_cus_it_code[]		= $val;
	foreach($_POST['_cus_it_qty'] as $val)		 $_cus_it_qty[]			= $val;
	foreach($_POST['_cus_it_remark'] as $val)	 $_cus_it_remark[]		= $val;

	//make pgsql ARRAY String for many item
	$_wh_it_code		= '$$' . implode('$$,$$', $_wh_it_code) . '$$';
	$_wh_it_code_for	= '$$' . implode('$$,$$', $_wh_it_code_for) . '$$';
	$_wh_it_qty			= implode(',', $_wh_it_qty);
	$_wh_it_function	= implode(',', $_wh_it_function);
	$_wh_it_remark		= '$$' . implode('$$,$$', $_wh_it_remark) . '$$';
	$_cus_it_code		= '$$' . implode('$$,$$', $_cus_it_code) . '$$';
	$_cus_it_qty		= implode(',', $_cus_it_qty);
	$_cus_it_remark		= '$$' . implode('$$,$$', $_cus_it_remark) . '$$';

	if($_do_type == 'dt') {
		$function = "insertDT";
	} else if($_do_type == 'dr') {
		$function = "insertDR";
	} else if($_do_type == 'df') {
		$function = "insertDF";
	} 

	$result = executeSP(
		ZKP_SQL."_".$function,
		"$\$".ZKP_SQL."$\$",
		"$\${$_dept}$\$",
		"$\${$_do_date}$\$",
		"$\${$_issued_by}$\$",
		"$\${$_issued_date}$\$",
		"$\${$_received_by}$\$",
		$_type_item,
		$_ordered_by,
		"$\${$_cus_to}$\$",
		"$\${$_cus_name}$\$",
		"$\${$_cus_address}$\$",
		"$\${$_ship_to}$\$",
		"$\${$_ship_name}$\$",
		"$\${$_turn_code}$\$",
		"$\${$_turn_date}$\$",
		"$\${$_lastupdated_by_account}$\$",
		"$\${$_cus_to}$\$",
		"$\${$_cus_name}$\$",
		"$\${$_cus_address}$\$",
		"$\${$_ship_to}$\$",
		"$\${$_ship_name}$\$",
		"$\${$_delivery_by}$\$",
		"$\${$_delivery_warehouse}$\$",
		"$\${$_delivery_franco}$\$",
		$_delivery_freight_charge,
		"$\${$_remark}$\$",
		"ARRAY[$_wh_it_code]",
		"ARRAY[$_wh_it_code_for]",
		"ARRAY[$_wh_it_qty]",
		"ARRAY[$_wh_it_function]",
		"ARRAY[$_wh_it_remark]",
		"ARRAY[$_cus_it_code]",
		"ARRAY[$_cus_it_qty]",
		"ARRAY[$_cus_it_remark]"
	);

	if (isZKError($result)) {
		$errMessage = $result->getMessage();
		if(strpos($errMessage, "duplicate key violates")) {
			$result = new ZKError(
				"DUPLICATE_CODE_EXIST",
				"DUPLICATE_CODE_EXIST",
				"Same Code exists, please check your code again");
		}
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/index.php");
	}

	$_code = substr($result[0],0,11);
	$_book_idx = substr($result[0],12);
	include APP_DIR . "_include/other/pdf/generate_pdf.php";
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_$_do_type.php?_code=".$_code);

}

//DELETE DF ===========================================================================================================
if (ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'delete_df')) {

	$_rev		= (int) $_POST['_revision_time'];
	$_book_idx	= $_POST['_book_idx'];
	$_inv_date	= date("Ym", strtotime($_POST['_date']));

	$result =& query(
				"DELETE FROM ".ZKP_SQL."_tb_df WHERE df_code = '$_code';
				DELETE FROM ".ZKP_SQL."_tb_booking WHERE book_idx = $_book_idx");

	if(isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_df.php?_code=".urlencode($_code));
	} else {
		for ($i = $_rev ; $i >=0 ; $i--) {
			@unlink(PDF_STORAGE . "other_document/{$currentDept}/{$_inv_date}/{$_code}_rev_{$i}.pdf");
		}
	}
	goPage(HTTP_DIR . "$currentDept/$moduleDept/daily_summary_by_group.php?cboSource=df");
}

//UPDATE DF ===========================================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'update_df')) {

	$_code				= $_POST['_code'];
	$_do_type			= 'df';
	$_dept				= $_POST['_dept'];
	$_book_idx			= $_POST['_book_idx'];
	$_do_date			= $_POST['_do_date'];
	$_issued_by			= $_POST['_issued_by'];
	$_issued_date		= $_POST['_issued_date'];
	$_received_by 		= $_POST['_received_by'];
	$_lastupdated_by_account = $S->getValue("ma_account");
	$_revision_time 	= $_POST['_revision_time'];
	$_cus_to			= strtoupper($_POST['_cus_to']);
	$_cus_name			= $_POST['_cus_name'];
	$_cus_address		= $_POST['_cus_address'];
	$_ship_to			= strtoupper($_POST['_ship_to']);
	$_ship_name			= $_POST['_ship_name'];

	$_delivery_by		 	= $_POST['_delivery_by'];
	$_delivery_warehouse 	= $_POST['_delivery_warehouse'];
	$_delivery_franco	 	= $_POST['_delivery_franco'];
	$_delivery_freight_charge	= empty($_POST['_delivery_freight_charge'])? 0 : $_POST['_delivery_freight_charge'];
	$_remark					= $_POST['_remark'];

	//Item Value
	foreach($_POST['_wh_it_code'] as $val)		$_wh_it_code[]		= $val;
	foreach($_POST['_wh_it_qty'] as $val)		$_wh_it_qty[]		= $val;
	foreach($_POST['_wh_it_remark'] as $val)	$_wh_it_remark[]	= $val;
	foreach($_POST['_cus_it_code'] as $val)		$_cus_it_code[]	= $val;
	foreach($_POST['_cus_it_qty'] as $val)		$_cus_it_qty[]		= $val;
	foreach($_POST['_cus_it_remark'] as $val)	$_cus_it_remark[]	= $val;
	$_wh_it_code		= '$$' . implode('$$,$$', $_wh_it_code) . '$$';
	$_wh_it_qty			= implode(',', $_wh_it_qty);
	$_wh_it_remark		= '$$' . implode('$$,$$', $_wh_it_remark) . '$$';
	$_cus_it_code		= '$$' . implode('$$,$$', $_cus_it_code) . '$$';
	$_cus_it_qty		= implode(',', $_cus_it_qty);
	$_cus_it_remark		= '$$' . implode('$$,$$', $_cus_it_remark) . '$$';

	$result = executeSP(
		ZKP_SQL."_updateDF",
		"$\${$_code}$\$",
		"$\${$_dept}$\$",	
		$_book_idx,
		"$\${$_do_date}$\$",
		"$\${$_issued_by}$\$",
		"$\${$_issued_date}$\$",
		"$\${$_received_by}$\$",
		"$\${$_lastupdated_by_account}$\$",
		$_revision_time,
		"$\${$_cus_to}$\$",
		"$\${$_cus_name}$\$",
		"$\${$_cus_address}$\$",
		"$\${$_ship_to}$\$",
		"$\${$_ship_name}$\$",
		"$\${$_delivery_by}$\$",
		"$\${$_delivery_warehouse}$\$",
		"$\${$_delivery_franco}$\$",
		$_delivery_freight_charge,
		"$\${$_remark}$\$",
		"ARRAY[$_wh_it_code]",
		"ARRAY[$_wh_it_qty]",
		"ARRAY[$_wh_it_remark]",
		"ARRAY[$_cus_it_code]",
		"ARRAY[$_cus_it_qty]",
		"ARRAY[$_cus_it_remark]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_df.php?_code=".urlencode($_code));
	}

	include APP_DIR . "_include/other/pdf/generate_pdf.php";
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_df.php?_code=".urlencode($_code));
}

//DELETE DR ===========================================================================================================
if (ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'delete_dr')) {

	$_rev		= (int) $_POST['_revision_time'];
	$_book_idx	= $_POST['_book_idx'];
	$_inv_date	= date("Ym", strtotime($_POST['_date']));

	$result =& query(
				"DELETE FROM ".ZKP_SQL."_tb_dr WHERE dr_code = '$_code';
				DELETE FROM ".ZKP_SQL."_tb_booking WHERE book_idx = $_book_idx");

	if(isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_dr.php?_code=".urlencode($_code));
	} else {
		for ($i = $_rev ; $i >=0 ; $i--) {
			@unlink(PDF_STORAGE . "other_document/{$currentDept}/{$_inv_date}/{$_code}_rev_{$i}.pdf");
		}
	}
	goPage(HTTP_DIR . "$currentDept/$moduleDept/daily_summary_by_group.php?cboSource=dr");
}

//UPDATE DR ===========================================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'update_dr')) {

	$_code				= $_POST['_code'];
	$_do_type			= 'dr';
	$_dept				= $_POST['_dept'];
	$_ordered_by		= $_POST['_ordered_by'];
	$_book_idx			= $_POST['_book_idx'];
	$_do_date			= $_POST['_do_date'];
	$_issued_by			= $_POST['_issued_by'];
	$_issued_date		= $_POST['_issued_date'];
	$_received_by 		= $_POST['_received_by'];
	$_turn_code			= $_POST['_turn_code'];
	$_turn_date			= $_POST['_turn_date'];
	$_lastupdated_by_account = $S->getValue("ma_account");
	$_revision_time 	= $_POST['_revision_time'];
	$_cus_to			= strtoupper($_POST['_cus_to']);
	$_cus_name			= $_POST['_cus_name'];
	$_cus_address		= $_POST['_cus_address'];
	$_ship_to			= strtoupper($_POST['_ship_to']);
	$_ship_name			= $_POST['_ship_name'];

	$_delivery_by		 	= $_POST['_delivery_by'];
	$_delivery_warehouse 	= $_POST['_delivery_warehouse'];
	$_delivery_franco	 	= $_POST['_delivery_franco'];
	$_delivery_freight_charge	= empty($_POST['_delivery_freight_charge'])? 0 : $_POST['_delivery_freight_charge'];
	$_remark					= $_POST['_remark'];

	//Item Value
	foreach($_POST['_wh_it_code'] as $val)		$_wh_it_code[]		= $val;
	foreach($_POST['_wh_it_qty'] as $val)		$_wh_it_qty[]		= $val;
	foreach($_POST['_wh_it_remark'] as $val)	$_wh_it_remark[]	= $val;
	foreach($_POST['_cus_it_code'] as $val)		$_cus_it_code[]	= $val;
	foreach($_POST['_cus_it_qty'] as $val)		$_cus_it_qty[]		= $val;
	foreach($_POST['_cus_it_remark'] as $val)	$_cus_it_remark[]	= $val;
	$_wh_it_code		= '$$' . implode('$$,$$', $_wh_it_code) . '$$';
	$_wh_it_qty			= implode(',', $_wh_it_qty);
	$_wh_it_remark		= '$$' . implode('$$,$$', $_wh_it_remark) . '$$';
	$_cus_it_code		= '$$' . implode('$$,$$', $_cus_it_code) . '$$';
	$_cus_it_qty		= implode(',', $_cus_it_qty);
	$_cus_it_remark		= '$$' . implode('$$,$$', $_cus_it_remark) . '$$';

	$result = executeSP(
		ZKP_SQL."_updateDR",
		"$\${$_code}$\$",
		"$\${$_dept}$\$",	
		$_book_idx,
		"$\${$_do_date}$\$",
		"$\${$_issued_by}$\$",
		"$\${$_issued_date}$\$",
		"$\${$_received_by}$\$",
		"$\${$_lastupdated_by_account}$\$",
		$_revision_time,
		"$\${$_cus_to}$\$",
		"$\${$_cus_name}$\$",
		"$\${$_cus_address}$\$",
		"$\${$_ship_to}$\$",
		"$\${$_ship_name}$\$",
		"$\${$_delivery_by}$\$",
		"$\${$_delivery_warehouse}$\$",
		"$\${$_delivery_franco}$\$",
		$_delivery_freight_charge,
		"$\${$_remark}$\$",
		"ARRAY[$_wh_it_code]",
		"ARRAY[$_wh_it_qty]",
		"ARRAY[$_wh_it_remark]",
		"ARRAY[$_cus_it_code]",
		"ARRAY[$_cus_it_qty]",
		"ARRAY[$_cus_it_remark]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_dr.php?_code=".urlencode($_code));
	}

	include APP_DIR . "_include/other/pdf/generate_pdf.php";
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_dr.php?_code=".urlencode($_code));
}

//DELETE DT ===========================================================================================================
if (ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'delete_dt')) {

	$_rev		= (int) $_POST['_revision_time'];
	$_book_idx	= $_POST['_book_idx'];
	$_inv_date	= date("Ym", strtotime($_POST['_date']));

	$result =& query(
				"DELETE FROM ".ZKP_SQL."_tb_dt WHERE dt_code = '$_code';
				DELETE FROM ".ZKP_SQL."_tb_booking WHERE book_idx = $_book_idx");

	if(isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_dt.php?_code=".urlencode($_code));
	} else {
		for ($i = $_rev ; $i >=0 ; $i--) {
			@unlink(PDF_STORAGE . "other_document/{$currentDept}/{$_inv_date}/{$_code}_rev_{$i}.pdf");
		}
	}
	goPage(HTTP_DIR . "$currentDept/$moduleDept/daily_summary_by_group.php?cboSource=dt");
}

//UPDATE DT ===========================================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'update_dt')) {

	$_code				= $_POST['_code'];
	$_do_type			= 'dt';
	$_dept				= $_POST['_dept'];
	$_book_idx			= $_POST['_book_idx'];
	$_do_date			= $_POST['_do_date'];
	$_issued_by			= $_POST['_issued_by'];
	$_issued_date		= $_POST['_issued_date'];
	$_received_by 		= $_POST['_received_by'];
	$_lastupdated_by_account = $S->getValue("ma_account");
	$_revision_time 	= $_POST['_revision_time'];
	$_cus_to			= strtoupper($_POST['_cus_to']);
	$_cus_name			= $_POST['_cus_name'];
	$_cus_address		= $_POST['_cus_address'];
	$_ship_to			= strtoupper($_POST['_ship_to']);
	$_ship_name			= $_POST['_ship_name'];

	$_delivery_by		 	= $_POST['_delivery_by'];
	$_delivery_warehouse 	= $_POST['_delivery_warehouse'];
	$_delivery_franco	 	= $_POST['_delivery_franco'];
	$_delivery_freight_charge	= empty($_POST['_delivery_freight_charge'])? 0 : $_POST['_delivery_freight_charge'];
	$_remark					= $_POST['_remark'];

	//Item Value
	foreach($_POST['_wh_it_code'] as $val)		$_wh_it_code[]		= $val;
	foreach($_POST['_wh_it_qty'] as $val)		$_wh_it_qty[]		= $val;
	foreach($_POST['_wh_it_remark'] as $val)	$_wh_it_remark[]	= $val;
	foreach($_POST['_cus_it_code'] as $val)		$_cus_it_code[]	= $val;
	foreach($_POST['_cus_it_qty'] as $val)		$_cus_it_qty[]		= $val;
	foreach($_POST['_cus_it_remark'] as $val)	$_cus_it_remark[]	= $val;
	$_wh_it_code		= '$$' . implode('$$,$$', $_wh_it_code) . '$$';
	$_wh_it_qty			= implode(',', $_wh_it_qty);
	$_wh_it_remark		= '$$' . implode('$$,$$', $_wh_it_remark) . '$$';
	$_cus_it_code		= '$$' . implode('$$,$$', $_cus_it_code) . '$$';
	$_cus_it_qty		= implode(',', $_cus_it_qty);
	$_cus_it_remark		= '$$' . implode('$$,$$', $_cus_it_remark) . '$$';

	$result = executeSP(
		ZKP_SQL."_updateDT",
		"$\${$_code}$\$",
		"$\${$_dept}$\$",	
		$_book_idx,
		"$\${$_do_date}$\$",
		"$\${$_issued_by}$\$",
		"$\${$_issued_date}$\$",
		"$\${$_received_by}$\$",
		"$\${$_lastupdated_by_account}$\$",
		$_revision_time,
		"$\${$_cus_to}$\$",
		"$\${$_cus_name}$\$",
		"$\${$_cus_address}$\$",
		"$\${$_ship_to}$\$",
		"$\${$_ship_name}$\$",
		"$\${$_delivery_by}$\$",
		"$\${$_delivery_warehouse}$\$",
		"$\${$_delivery_franco}$\$",
		$_delivery_freight_charge,
		"$\${$_remark}$\$",
		"ARRAY[$_wh_it_code]",
		"ARRAY[$_wh_it_qty]",
		"ARRAY[$_wh_it_remark]",
		"ARRAY[$_cus_it_code]",
		"ARRAY[$_cus_it_qty]",
		"ARRAY[$_cus_it_remark]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_dt.php?_code=".urlencode($_code));
	}

	include APP_DIR . "_include/other/pdf/generate_pdf.php";
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_dt.php?_code=".urlencode($_code));
}

//DELETE RDT ==========================================================================================================
if (ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'delete_rdt')) {

	$_rev		= (int) $_POST['_revision_time'];
	$_std_idx	= $_POST['_std_idx'];
	$_inc_idx	= $_POST['_inc_idx'];
	$_date	= date("Ym", strtotime($_POST['_date']));

	$result =& query(
				"DELETE FROM ".ZKP_SQL."_tb_return_dt WHERE rdt_code = '$_code';
				DELETE FROM ".ZKP_SQL."_tb_outstanding WHERE std_idx = $_std_idx;
				DELETE FROM ".ZKP_SQL."_tb_incoming WHERE inc_idx = $_inc_idx;"
			);

	if(isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_return_dt.php?_code=".urlencode($_code));
	} else {
		for ($i = $_rev ; $i >=0 ; $i--) {
			@unlink(PDF_STORAGE . "other_document/{$currentDept}/{$_date}/{$_code}_rev_{$i}.pdf");
		}
	}
	goPage(HTTP_DIR . "$currentDept/$moduleDept/daily_summary_by_group.php?cboSource=rdt");
}

//UPDATE RDT ==========================================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'update_rdt')) {

	$_code				= $_POST['_code'];
	$_do_type			= 'rdt';
	$_std_idx			= $_POST['_std_idx'];
	$_date				= $_POST['_date'];
	$_dt_code			= $_POST['_dt_code'];
	$_dt_date			= $_POST['_dt_date'];
	$_issued_by			= $_POST['_issued_by'];
	$_lastupdated_by_account = $S->getValue("ma_account");
	$_revision_time 	= $_POST['_revision_time'];
	$_cus_to			= strtoupper($_POST['_cus_to']);
	$_cus_name			= $_POST['_cus_name'];
	$_cus_address		= $_POST['_cus_address'];
	$_ship_to			= strtoupper($_POST['_ship_to']);
	$_ship_name			= $_POST['_ship_name'];

	$_delivery_by		 	= $_POST['_delivery_by'];
	$_delivery_warehouse 	= $_POST['_delivery_warehouse'];
	$_delivery_franco	 	= $_POST['_delivery_franco'];
	$_delivery_freight_charge	= empty($_POST['_delivery_freight_charge'])? 0 : $_POST['_delivery_freight_charge'];
	$_remark					= $_POST['_remark'];

	$result = executeSP(
		ZKP_SQL."_updateReturnDT",
		"$\${$_code}$\$",
		"$\${$_date}$\$",
		"$\${$_issued_by}$\$",
		"$\${$_lastupdated_by_account}$\$",
		$_revision_time,
		"$\${$_cus_to}$\$",
		"$\${$_cus_name}$\$",
		"$\${$_cus_address}$\$",
		"$\${$_ship_to}$\$",
		"$\${$_ship_name}$\$",
		"$\${$_delivery_by}$\$",
		"$\${$_delivery_warehouse}$\$",
		"$\${$_delivery_franco}$\$",
		$_delivery_freight_charge,
		"$\${$_remark}$\$"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_return_dt.php?_code=".urlencode($_code));
	}

	include APP_DIR . "_include/other/pdf/generate_rt_pdf.php";
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_return_dt.php?_code=".urlencode($_code));
}

//INSERT RDT ==========================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'insert_rdt')) {

	$_dept				= $_POST['_dept'];
	$_ordered_by		= $_POST['_ordered_by'];
	$_do_type 			= 'rdt'; 
	$_date				= $_POST['_date'];
	$_dt_code			= $_POST['_dt_code'];
	$_dt_date			= $_POST['_dt_date'];
	$_issued_by 		= $_POST['_issued_by'];
	$_type_item			= $_POST['_type_item'];
	$_lastupdated_by_account = $S->getValue("ma_account");
	$_revision_time 	= -1;
	$_cus_to			= strtoupper($_POST['_cus_to']);
	$_cus_name			= $_POST['_cus_name'];
	$_cus_address		= $_POST['_cus_address'];
	$_ship_to			= strtoupper($_POST['_ship_to']);
	$_ship_name			= $_POST['_ship_name'];

	$_delivery_by		 	= $_POST['_delivery_by'];
	$_delivery_warehouse 	= $_POST['_delivery_warehouse'];
	$_delivery_franco	 	= $_POST['_delivery_franco'];
	$_delivery_freight_charge	= empty($_POST['_delivery_freight_charge'])? 0 : $_POST['_delivery_freight_charge'];
	$_remark					= $_POST['_remark'];

	//Item Value
	foreach($_POST['_wh_it_code'] as $val)		 $_wh_it_code[]			= $val;
	foreach($_POST['_wh_it_type'] as $val)		 $_wh_it_type[]			= $val;
	foreach($_POST['_wh_it_qty'] as $val)		 $_wh_it_qty[]			= $val;
	foreach($_POST['_wh_it_coming_qty'] as $val) $_wh_it_coming_qty[]	= $val;
	foreach($_POST['_wh_it_return_qty'] as $val) $_wh_it_return_qty[]	= $val;
	foreach($_POST['_wh_it_remark'] as $val)	 $_wh_it_remark[]		= $val;
	foreach($_POST['_cus_it_code'] as $val)		 $_cus_it_code[]		= $val;
	foreach($_POST['_cus_it_qty'] as $val)		 $_cus_it_qty[]			= $val;
	foreach($_POST['_cus_it_remark'] as $val)	 $_cus_it_remark[]		= $val;
	$_wh_it_code		= '$$' . implode('$$,$$', $_wh_it_code) . '$$';
	$_wh_it_type		= implode(',', $_wh_it_type);
	$_wh_it_coming_qty	= implode(',', $_wh_it_coming_qty);
	$_wh_it_remark		= '$$' . implode('$$,$$', $_wh_it_remark) . '$$';
	$_cus_it_code		= '$$' . implode('$$,$$', $_cus_it_code) . '$$';
	$_cus_it_qty		= implode(',', $_cus_it_qty);
	$_cus_it_remark		= '$$' . implode('$$,$$', $_cus_it_remark) . '$$';

	$result = executeSP(
		ZKP_SQL."_insertReturnDT",
		"$\$".ZKP_SQL."$\$",
		"$\${$_date}$\$",
		"$\${$_dept}$\$",
		"$\${$_dt_code}$\$",
		"$\${$_dt_date}$\$",
		"$\${$_issued_by}$\$",
		"$\${$_lastupdated_by_account}$\$",
		$_type_item,
		$_ordered_by,
		"$\${$_cus_to}$\$",
		"$\${$_cus_name}$\$",
		"$\${$_cus_address}$\$",
		"$\${$_ship_to}$\$",
		"$\${$_ship_name}$\$",
		"$\${$_delivery_by}$\$",
		"$\${$_delivery_warehouse}$\$",
		"$\${$_delivery_franco}$\$",
		$_delivery_freight_charge,
		"$\${$_lastupdated_by_account}$\$",
		"$\${$_remark}$\$",
		"ARRAY[$_wh_it_code]",
		"ARRAY[$_wh_it_type]",
		"ARRAY[$_wh_it_coming_qty]",
		"ARRAY[$_wh_it_remark]",
		"ARRAY[$_cus_it_code]",
		"ARRAY[$_cus_it_qty]",
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
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/input_return_dt.php?_code=$_code");
	}

	$_code = substr($result[0],0,11);
	$_std_idx = substr($result[0],12);
	include APP_DIR . "_include/other/pdf/generate_rt_pdf.php";
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_return_dt.php?_code=".$_code);
}

//CONDIRM DELIVERY ====================================================================================================
if (ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'cfm_delivery_df')) {

	$_code		  	= $_POST['_code'];
	$_delivery_date	= $_POST['_delivery_date'];
	$_delivery_by	= $_POST['_delivery_by'];
	$_confirm_by	= $S->getValue("ma_account");

	$result = executeSP(
		ZKP_SQL."_cfmDFDelivery",
		"$\${$_code}$\$",
		"$\${$_delivery_date}$\$",
		"$\${$_delivery_by}$\$",
		"$\${$_confirm_by}$\$"
	);

	if (isZKError($result)) $M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");
	goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_df.php?_code=".urlencode($_code));
}

//CONDIRM DELIVERY ====================================================================================================
if (ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'cfm_delivery_dr')) {

	$_code		  	= $_POST['_code'];
	$_delivery_date	= $_POST['_delivery_date'];
	$_delivery_by	= $_POST['_delivery_by'];
	$_confirm_by	= $S->getValue("ma_account");

	$result = executeSP(
		ZKP_SQL."_cfmDRDelivery",
		"$\${$_code}$\$",
		"$\${$_delivery_date}$\$",
		"$\${$_delivery_by}$\$",
		"$\${$_confirm_by}$\$"
	);

	if (isZKError($result)) $M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");
	goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_dr.php?_code=".urlencode($_code));
}

//CONDIRM DELIVERY ====================================================================================================
if (ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'cfm_delivery_dt')) {

	$_code		  	= $_POST['_code'];
	$_delivery_date	= $_POST['_delivery_date'];
	$_delivery_by	= $_POST['_delivery_by'];
	$_confirm_by	= $S->getValue("ma_account");

	$result = executeSP(
		ZKP_SQL."_cfmDTDelivery",
		"$\${$_code}$\$",
		"$\${$_delivery_date}$\$",
		"$\${$_delivery_by}$\$",
		"$\${$_confirm_by}$\$"
	);

	if (isZKError($result)) $M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");
	goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_dt.php?_code=".urlencode($_code));
}
?>