<?php
$title				= array(1=>"Issue Invoice &amp; booking Item","Issue invoice only");
$_code	= (empty($_code)) ? "" : $_code;

//RECEIVE DATA ========================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'order_info')) {

	//VARIABLE
	$_ord_code			= $_POST['_ord_code'];
	$_type_ord			= $_POST['cboTypeOrd'];
	$_ord_date			= $_POST['_ord_date'];
	$_po_date			= date("j-M-Y", strtotime($_POST['_po_date']));
	$_po_no				= $_POST['_po_no'];
	if(isset($_POST['_type']) && $_POST['_type'] != '') {
		$_type			= $_POST['_type'];	
	} else if(isset($_POST['_return_type']) && $_POST['_return_type'] != '') {
		$_type			= $_POST['_return_type'];	
	}
	$_vat				= empty($_POST['_vat']) ? 0 : $_POST['_vat'];
	$_received_by		= $_POST['_received_by'];
	$_confirm_by		= $_POST['_confirm_by'];
	$_cus_to			= strtoupper($_POST['_cus_to']);
	$_ship_to			= strtoupper($_POST['_ship_to']);
	$_bill_to			= strtoupper($_POST['_bill_to']);
	$_cus_to_attn		= $_POST['_cus_to_attn'];
	$_ship_to_attn		= $_POST['_ship_to_attn'];
	$_bill_to_attn		= $_POST['_bill_to_attn'];
	$_cus_to_address	= $_POST['_cus_to_address'];
	$_ship_to_address	= $_POST['_ship_to_address'];
	$_bill_to_address	= $_POST['_bill_to_address'];

	//Check valid customer code
	if($department == 'A') {
		$sql = "SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cus_code = '$_cus_to' AND cus_channel = '002'";
		isZKError($result =& query($sql)) ? $M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/input_order_step_1.php") : false;
		if (numQueryRows($result) <= 0) {
			$o = new ZKError ("INVALID_CUSTOMER_CODE", "INVALID_CUSTOMER_CODE", "The Customer code, <strong>'$_cus_to'</strong> does not exist, Please try again");
			$M->goErrorPage($o, HTTP_DIR . "$currentDept/$moduleDept/input_order_step_1.php");
		}
		//take discount percentage from customer group
		$sql = "SELECT cug_basic_disc_pct from ".ZKP_SQL."_tb_customer_group WHERE cug_code = (SELECT cug_code FROM ".ZKP_SQL."_tb_customer WHERE cus_code = '$_cus_to')";
		isZKError($res =& query($sql)) ? $M->goErrorPage($res, HTTP_DIR . "$currentDept/$moduleDept/input_order_step_1.php") : false;
		$disc = fetchRow($res);
	} else {
		$sql = "SELECT cug_basic_disc_pct from ".ZKP_SQL."_tb_customer_group WHERE cug_code = (SELECT cug_code FROM ".ZKP_SQL."_tb_customer WHERE cus_code = '$_cus_to')";
		isZKError($res =& query($sql)) ? $M->goErrorPage($res, HTTP_DIR . "$currentDept/$moduleDept/input_order_step_1.php") : false;
		$disc = fetchRow($res);
		$disc[0] = (empty($disc[0])) ? 0 : $disc[0];
	}
}

//INSERT ORDER ========================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'insert')) {

	$_ord_code				= $_POST['_ord_code'];
	$_dept					= $_POST['_dept'];
	$_paper					= $_POST['_type_ord'];
	$_ord_date				= $_POST['_ord_date'];
	$_po_date				= $_POST['_po_date'];
	$_po_no					= $_POST['_po_no'];
	$_type					= $_POST['_type'];
	$_vat					= empty($_POST['_vat']) ? 0 : $_POST['_vat'];
	$_received_by			= $_POST['_received_by'];
	$_confirm_by			= $_POST['_confirm_by'];
	$_sign_by				= $_POST['_sign_by'];
	$_remark				= $_POST['_remark'];
	$_lastupdated_by_account= $S->getValue("ma_account");
	$_revision_time			= -1;

	$_cus_to				= strtoupper($_POST['_cus_to']);
	$_ship_to				= strtoupper($_POST['_ship_to']);
	$_bill_to				= strtoupper($_POST['_bill_to']);
	$_cus_to_attn			= $_POST['_cus_to_attn'];
	$_ship_to_attn			= $_POST['_ship_to_attn'];
	$_bill_to_attn			= $_POST['_bill_to_attn'];
	$_cus_to_address		= $_POST['_cus_to_address'];
	$_ship_to_address		= $_POST['_ship_to_address'];
	$_bill_to_address		= $_POST['_bill_to_address'];

	$_price_discount		= $_POST['_price_discount'];
	$_delivery_by			= $_POST['_delivery_by'];
	$_delivery_freight_charge= empty($_POST['_delivery_freight_charge'])? 0 : $_POST['_delivery_freight_charge'];
	$_payment_widthin_days	= ($_POST['_payment_widthin_days'] == '') ? 0 : $_POST['_payment_widthin_days'];
	$_payment_closing_on	= $_POST['_payment_closing_on'];
	$_payment_cash_by		= $_POST['_payment_cash_by'];
	$_payment_check_by		= $_POST['_payment_check_by'];
	$_payment_transfer_by	= $_POST['_payment_transfer_by'];
	$_payment_giro_by		= $_POST['_payment_giro_by'];

	$_price_chk		= 0;
	$_delivery_chk	= 0;
	$_payment_chk	= 0;
	if(isset($_POST['_price_chk']) && is_array($_POST['_price_chk']))
		foreach($_POST['_price_chk'] as $val)		$_price_chk = $_price_chk + $val;
	if(isset($_POST['_delivery_chk']) && is_array($_POST['_delivery_chk']))
		foreach($_POST['_delivery_chk'] as $val)	$_delivery_chk = $_delivery_chk + $val;
	if(isset($_POST['_payment_chk']) && is_array($_POST['_payment_chk']))
		foreach($_POST['_payment_chk'] as $val)		$_payment_chk = $_payment_chk + $val;

	// ITEM LIST
	foreach($_POST['_cus_it_code'] as $val)			$_cus_it_code[]			= $val;
	foreach($_POST['_cus_it_unit_price'] as $val)	$_cus_it_unit_price[]	= $val;
	foreach($_POST['_cus_it_qty'] as $val)			$_cus_it_qty[] 			= $val;
	foreach($_POST['_cus_it_remark'] as $val)		$_cus_it_remark[] 		= $val;
	$_cus_it_code		= '$$' . implode('$$,$$', $_cus_it_code) . '$$';
	$_cus_it_unit_price	= implode(',', $_cus_it_unit_price);
	$_cus_it_qty		= implode(',', $_cus_it_qty);
	$_cus_it_remark		= '$$' . implode('$$,$$', $_cus_it_remark) . '$$';

	if($_paper == 0) {
		foreach($_POST['_wh_it_code'] as $val)			$_wh_it_code[]			= $val;
		foreach($_POST['_wh_it_code_for'] as $val)		$_wh_it_code_for[]		= $val;
		foreach($_POST['_wh_it_qty'] as $val)			$_wh_it_qty[]			= $val;
		foreach($_POST['_wh_it_function'] as $val)		$_wh_it_function[]		= $val;
		foreach($_POST['_wh_it_remark'] as $val)		$_wh_it_remark[]		= $val;
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
		ZKP_SQL."_insertReturnOrder",
		"$\$".ZKP_SQL."$\$", 
		"$\${$_ord_code}$\$",
		$_paper,
		"$\${$_dept}$\$",
		"$\${$_ord_date}$\$",
		"$\${$_received_by}$\$",
		"$\${$_confirm_by}$\$",
		"$\${$_po_date}$\$",
		"$\${$_po_no}$\$",
		"$\${$_type}$\$",
		$_vat,
		"$\${$_cus_to}$\$",
		"$\${$_cus_to_attn}$\$",
		"$\${$_cus_to_address}$\$",
		"$\${$_ship_to}$\$",
		"$\${$_ship_to_attn}$\$",
		"$\${$_ship_to_address}$\$",
		"$\${$_bill_to}$\$",
		"$\${$_bill_to_attn}$\$",
		"$\${$_bill_to_address}$\$",
		$_price_discount,
		$_price_chk,
		$_delivery_chk,
		"$\${$_delivery_by}$\$",
		$_delivery_freight_charge,
		$_payment_chk,
		$_payment_widthin_days,
		"$\${$_payment_closing_on}$\$",
		"$\${$_payment_cash_by}$\$",
		"$\${$_payment_check_by}$\$",
		"$\${$_payment_transfer_by}$\$",
		"$\${$_payment_giro_by}$\$",
		"$\${$_sign_by}$\$",
		"$\${$_remark}$\$",
		"$\${$_lastupdated_by_account}$\$",
		$_revision_time,
		"ARRAY[$_wh_it_code]",
		"ARRAY[$_wh_it_code_for]",
		"ARRAY[$_wh_it_qty]",
		"ARRAY[$_wh_it_function]",
		"ARRAY[$_wh_it_remark]",
		"ARRAY[$_cus_it_code]",
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
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/input_return_order_step_1.php");
	}

	//SAVE PDF FILE---------------------------
	$_code = substr($result[0],0,12);
	$_std_idx = substr($result[0],13);
	include APP_DIR . "_include/order/pdf/generate_return.php";
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_return_order.php?_code=".$_code);

}

//UPDATE RETURN ORDER =================================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'update')) {

	$_code					= $_POST['_code'];
	$_dept					= $_POST['_dept'];
	$_ord_code				= $_POST['_ord_code'];
	$_std_idx				= $_POST['_std_idx'];
	$_inc_idx				= $_POST['_inc_idx'];
	$_ord_date				= $_POST['_ord_date'];
	$_po_date				= $_POST['_po_date'];
	$_po_no					= $_POST['_po_no'];
	$_type					= $_POST['_type'];
	$_vat					= empty($_POST['_vat']) ? 0 : $_POST['_vat'];
	$_paper					= $_POST['_paper'];
	$_received_by			= $_POST['_received_by'];
	$_confirm_by			= $_POST['_confirm_by'];
	$_sign_by				= $_POST['_sign_by'];
	$_remark				= $_POST['_remark'];
	$_lastupdated_by_account= $S->getValue("ma_account");
	$_revision_time			= (int) $_POST['_revesion_time'];

	$_cus_to				= strtoupper($_POST['_cus_to']);
	$_ship_to				= strtoupper($_POST['_ship_to']);
	$_bill_to				= strtoupper($_POST['_bill_to']);
	$_cus_to_attn			= $_POST['_cus_to_attn'];
	$_ship_to_attn			= $_POST['_ship_to_attn'];
	$_bill_to_attn			= $_POST['_bill_to_attn'];
	$_cus_to_address		= $_POST['_cus_to_address'];
	$_ship_to_address		= $_POST['_ship_to_address'];
	$_bill_to_address		= $_POST['_bill_to_address'];

	$_price_discount	= $_POST['_price_discount'];
	$_delivery_by	= $_POST['_delivery_by'];
	$_delivery_freight_charge	= empty($_POST['_delivery_freight_charge'])? 0 : $_POST['_delivery_freight_charge'];
	$_payment_widthin_days	= $_POST['_payment_widthin_days'];
	$_payment_closing_on	= $_POST['_payment_closing_on'];
	$_payment_cash_by		= $_POST['_payment_cash_by'];
	$_payment_check_by		= $_POST['_payment_check_by'];
	$_payment_transfer_by	= $_POST['_payment_transfer_by'];
	$_payment_giro_by		= $_POST['_payment_giro_by'];

	$_price_chk		= 0;
	$_delivery_chk	= 0;
	$_payment_chk	= 0;
	if(isset($_POST['_price_chk']) && is_array($_POST['_price_chk']))
		foreach($_POST['_price_chk'] as $val) $_price_chk = $_price_chk + $val;
	if(isset($_POST['_delivery_chk']) && is_array($_POST['_delivery_chk']))
		foreach($_POST['_delivery_chk'] as $val) $_delivery_chk = $_delivery_chk + $val;
	if(isset($_POST['_payment_chk']) && is_array($_POST['_payment_chk']))
		foreach($_POST['_payment_chk'] as $val) $_payment_chk = $_payment_chk + $val;

	// ITEM LIST
	foreach($_POST['_cus_it_code'] as $val)			$_cus_it_code[]			= $val;
	foreach($_POST['_cus_it_unit_price'] as $val)	$_cus_it_unit_price[]	= $val;
	foreach($_POST['_cus_it_qty'] as $val)			$_cus_it_qty[] 			= $val;
	foreach($_POST['_cus_it_remark'] as $val)		$_cus_it_remark[] 		= $val;
	$_cus_it_code		= '$$' . implode('$$,$$', $_cus_it_code) . '$$';
	$_cus_it_unit_price	= implode(',', $_cus_it_unit_price);
	$_cus_it_qty		= implode(',', $_cus_it_qty);
	$_cus_it_remark		= '$$' . implode('$$,$$', $_cus_it_remark) . '$$';

	if($_paper == 0) {
		foreach($_POST['_wh_it_code'] as $val)			$_wh_it_code[]			= $val;
		foreach($_POST['_wh_it_code_for'] as $val)		$_wh_it_code_for[]		= $val;
		foreach($_POST['_wh_it_qty'] as $val)			$_wh_it_qty[]			= $val;
		foreach($_POST['_wh_it_function'] as $val)		$_wh_it_function[]		= $val;
		foreach($_POST['_wh_it_remark'] as $val)		$_wh_it_remark[]		= $val;
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
		ZKP_SQL."_updateReturnOrder",
		"$\${$_code}$\$",
		"$\${$_dept}$\$",
		"$\${$_ord_code}$\$",
		$_std_idx,
		$_inc_idx,
		"$\${$_ord_date}$\$",
		"$\${$_received_by}$\$",
		"$\${$_confirm_by}$\$",
		"$\${$_po_date}$\$",
		"$\${$_po_no}$\$",
		"$\${$_type}$\$",
		$_vat,
		$_paper,
		"$\${$_cus_to}$\$",
		"$\${$_cus_to_attn}$\$",
		"$\${$_cus_to_address}$\$",
		"$\${$_ship_to}$\$",
		"$\${$_ship_to_attn}$\$",
		"$\${$_ship_to_address}$\$",
		"$\${$_bill_to}$\$",
		"$\${$_bill_to_attn}$\$",
		"$\${$_bill_to_address}$\$",
		$_price_discount,
		$_price_chk,
		$_delivery_chk,
		"$\${$_delivery_by}$\$",
		$_delivery_freight_charge,
		$_payment_chk,
		$_payment_widthin_days,
		"$\${$_payment_closing_on}$\$",
		"$\${$_payment_cash_by}$\$",
		"$\${$_payment_check_by}$\$",
		"$\${$_payment_transfer_by}$\$",
		"$\${$_payment_giro_by}$\$",
		"$\${$_sign_by}$\$",
		"$\${$_remark}$\$",
		"$\${$_lastupdated_by_account}$\$",
		"ARRAY[$_wh_it_code]",
		"ARRAY[$_wh_it_code_for]",
		"ARRAY[$_wh_it_qty]",
		"ARRAY[$_wh_it_function]",
		"ARRAY[$_wh_it_remark]",
		"ARRAY[$_cus_it_code]",
		"ARRAY[$_cus_it_qty]",
		"ARRAY[$_cus_it_unit_price]",
		"ARRAY[$_cus_it_remark]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_return_order.php?_code=".urlencode($_code));
	}

	//SAVE PDF FILE---------------------------
	include APP_DIR . "_include/order/pdf/generate_return.php";
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_return_order.php?_code=".urlencode($_code));
}

//DELETE RETURN ORDER =================================================================================================
if (ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'delete')) {

	$_std_idx		= ($_POST['_std_idx'] == "") ? 0 : $_POST['_std_idx'];
	$_inc_idx		= ($_POST['_inc_idx'] == "") ? 0 : $_POST['_inc_idx'];
	$_rev = (int) $_POST['_revesion_time'];
	$_po_date = date("Ym", strtotime($_POST['_po_date']));

	$result =& query(
		"DELETE FROM ".ZKP_SQL."_tb_return_order WHERE reor_code = '$_code';
		DELETE FROM ".ZKP_SQL."_tb_outstanding WHERE std_idx = $_std_idx;
		DELETE FROM ".ZKP_SQL."_tb_incoming WHERE inc_idx = $_inc_idx;"
	);

	if(isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_return_order.php?_code=".urlencode($_code));
	} else {
		for ($i = $_rev ; $i >=0 ; $i--) {
			@unlink(PDF_STORAGE . "order/$currentDept/{$_po_date}/{$_code}_rev_{$i}.pdf");
		}
	}

	goPage(HTTP_DIR . "$currentDept/$moduleDept/summary_order_by_group.php");
}

//INSERT ATTACHMENT ========================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'upload_file')) {

	$_code = $_POST['_code'];
	$_date = $_POST['_date'];
	$_inputted_by_account = ucfirst($S->getValue("ma_account"));

	$storage = USER_DATA . "archieve/$moduleDept/$currentDept/". date("Ym/", strtotime($_date));
	$path = "/$moduleDept/$currentDept/". date("Ym/", strtotime($_date));
	(!is_dir($storage)) ? mkdir($storage, 0777, true) : 0;
	$valid_type = array ('image/png','image/jpeg','image/gif', 'image/jpg', 'image/pjpeg');

	// Attachment
	for($i=0; $i<count($_FILES["_file"]["name"]); $i++) {
		$file_name = $_code . "_" . time() . "_" . $_FILES["_file"]["name"][$i];

		// Check validity type and size
		if ((in_array($_FILES["_file"]["type"][$i], $valid_type, true))
		    && $_FILES["_file"]["size"][$i] < 200000)
		{
			if(move_uploaded_file($_FILES["_file"]["tmp_name"][$i], $storage . $file_name))
			{
				$_file['name'][$i] = $_FILES["_file"]["name"][$i];
				$_file['path'][$i] = $path . $file_name;
				$_file['type'][$i] = $_FILES["_file"]["type"][$i];
			}
			$_file['type'][$i] = $_POST["cboType"][$i];
			$_file['desc'][$i] = $_POST["_file_remark"][$i];
		}
	}

	$_file['name']	= '$$' . implode('$$,$$', $_file['name']) . '$$';
	$_file['path'] 	= '$$' . implode('$$,$$', $_file['path']) . '$$';
	$_file['type']	= '$$' . implode('$$,$$', $_file['type']) . '$$';
	$_file['desc']	= '$$' . implode('$$,$$', $_file['desc']) . '$$';

	$result = executeSP(
		ZKP_SQL."_uploadFileArchieve",
		"$\$Return Order$\$",
		"$\${$_code}$\$",
		"$\${$_inputted_by_account}$\$",
		"ARRAY[".$_file['name']."]",
		"ARRAY[".$_file['path']."]",
		"ARRAY[".$_file['type']."]",
		"ARRAY[".$_file['desc']."]"
	);

	if (isZKError($result))
	{
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_return_order.php?_code=".$_code);
	}

	$M->goPage("revise_return_order.php?_code=".$_code);

}

//DELETE ATTACHMENT ========================================================================================================
if(ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'upload_file_delete')) {

	$_code = $_POST['_code'];
	$_idx = $_POST['_idx'];
	$_idx_path = $_POST['_idx_path'];

	$sql = "DELETE FROM ".ZKP_SQL."_tb_return_order_file WHERE reor_code='$_code' AND reorf_idx=$_idx ";

	if(isZKError($result =& query($sql)))
	{
		$M->goErrorPage($result, "revise_return_order.php?_code=".$_code);
	}

	$file_name = USER_DATA . "archieve/" . $_idx_path;
	@unlink($file_name);

	$M->goPage("revise_return_order.php?_code=".$_code);
}
?>