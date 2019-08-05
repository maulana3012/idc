<?php
//CONFIRM DO ==========================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'confirm_do')) {

	$_cus_code	= trim($_POST["_cus_code"]);
	$_out_type	= $_POST["_out_type"]; 
	$_book_idx	= $_POST["_book_idx"];
	$_dept		= $_POST["_book_dept"];
	$_out_code	= $_POST["_book_code"];
	$_out_doc_ref	= trim($_POST["_doc_ref"]);
	$_out_doc_type	= $_POST["_doc_type"];
	$_issued_date	= $_POST["_book_date"];
	$_received_by	= $_POST["_received_by"];
	$_cfm_date	= $_POST["_confirmed_date"];
	$_revision_time	= $_POST["_revision_time"];
	$_cfm_by_account= $S->getValue("ma_account");
	$_remark	= $_POST["_remark"];

	//	Item Value
	foreach($_POST['_it_code'] as $val)		$_it_code[]		= $val;
	foreach($_POST['_it_ed'] as $val)		$_it_ed[] 		= $val;
	foreach($_POST['_it_booked_qty'] as $val)	$_it_booked_qty[] 	= $val;

	if(isset($_POST['_ed_it_code'])) {
		foreach($_POST['_ed_it_code'] as $val) {
			$_ed_it_code[]	 = $val;
		}
		$_ed_it_code	= '$$'.implode('$$,$$', $_ed_it_code).'$$';
	} else {
		$_ed_it_code	= '$$$$';
	}

	if(isset($_POST['_ed_it_location'])) {
		foreach($_POST['_ed_it_location'] as $val) {
			$_ed_it_location[] 		 = $val;
		}
		$_ed_it_location	= implode(',', $_ed_it_location);
	} else {
		$_ed_it_location	= '0';
	}

	if(isset($_POST['_ed_it_date'])) {
		foreach($_POST['_ed_it_date'] as $val) {
			$_ed_it_date[]	 = $val;
		}
		$_ed_it_date	= '$$'.implode('$$,$$', $_ed_it_date).'$$';
	} else {
		$_ed_it_date	= '$$$$';
	}

	if(isset($_POST['_ed_it_qty'])) {
		foreach($_POST['_ed_it_qty'] as $val) {
			$_ed_it_qty[]	= $val;
		}
		$_ed_it_qty	= implode(',', $_ed_it_qty);
	} else {
		$_ed_it_qty	= '0';
	}

	//make pgsql ARRAY String for many item
	$_it_code		= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_it_ed			= '$$' . implode('$$,$$', $_it_ed) . '$$';
	$_it_booked_qty	= implode(',', $_it_booked_qty);

	//confirmDO
	$result = executeSP(
		ZKP_SQL."_addNewDeliveryStock",
		"$\${$_cus_code}$\$",
		$_out_type,
		$_book_idx,
		"$\${$_dept}$\$",
		"$\${$_out_code}$\$",
		"$\${$_out_doc_ref}$\$",
		$_out_doc_type,
		"$\${$_issued_date}$\$",
		"$\${$_received_by}$\$",
		"$\${$_cfm_date}$\$",
		"$\${$_cfm_by_account}$\$",
		"$\${$_remark}$\$",
		"ARRAY[$_it_code]",
		"ARRAY[$_it_ed]",
		"ARRAY[$_it_booked_qty]",
		"ARRAY[$_ed_it_code]",
		"ARRAY[$_ed_it_location]",
		"ARRAY[$_ed_it_date]",
		"ARRAY[$_ed_it_qty]"
	);

	if (isZKError($result)) {
		$errMessage = $result->getMessage();
		if(strpos($errMessage, "duplicate key value violates")) {
			$result = new ZKError(
				"DUPLICATE_CODE_EXIST",
				"DUPLICATE_CODE_EXIST",
				"Same Code exists, the possibility is document no. $_out_doc_ref already confirmed.");
		}
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/index.php");
	} else if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/confirm_do.php?_code=$_code");
	}

	$_out_idx = $result[0];
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_do.php?_code=$_out_idx&_source=v2");
}

//CONFIRM DO RETURN ===================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'confirm_do_return')) {

	$_std_idx		= $_POST["_std_idx"];
	$_inc_idx		= $_POST["_inc_idx"];
	$_cus_code		= $_POST["_cus_code"];
	$_type			= $_POST["_std_type"];
	$_remark		= $_POST["_remark"];
	$_revision_time		= $_POST["_revision_time"];
	$_cfm_by_account	= $S->getValue("ma_account");
	$_doc_type		= $_POST["_doc_type"];
	$_doc_ref		= $_POST["_doc_ref"];
	$_doc_date		= $_POST["_doc_date"];

	// Item Value
	foreach($_POST['_it_code'] as $val)		$_it_code[]		= $val;
	foreach($_POST['_it_ed'] as $val)		$_it_ed[]		= $val;
	foreach($_POST['_it_stock_qty'] as $val)	$_it_stock_qty[] 	= $val;
	foreach($_POST['_it_demo_qty'] as $val)		$_it_demo_qty[] 	= $val;
	foreach($_POST['_it_reject_qty'] as $val)	$_it_reject_qty[] 	= $val;

	// make pgsql ARRAY String for many item
	$_it_code	= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_it_ed		= '$$' . implode('$$,$$', $_it_ed) . '$$';
	$_it_type	= '0';
	$_it_stock_qty	= implode(',', $_it_stock_qty);
	$_it_demo_qty	= implode(',', $_it_demo_qty);
	$_it_reject_qty	= implode(',', $_it_reject_qty);

	//stock item list
	if(isset($_POST['_ed_stk_it_code'])) {
		foreach($_POST['_ed_stk_it_code'] as $val) {$_ed_stk_it_code[] = $val;}
		$_ed_stk_it_code = '$$'.implode('$$,$$', $_ed_stk_it_code).'$$';
	} else {
		$_ed_stk_it_code = '$$$$';
	}

	if(isset($_POST['_ed_stk_it_date'])) {
		foreach($_POST['_ed_stk_it_date'] as $val) {$_ed_stk_it_date[] = $val;}
		$_ed_stk_it_date = '$$'.implode('$$,$$', $_ed_stk_it_date).'$$';
	} else {
		$_ed_stk_it_date = '$$$$';
	}

	if(isset($_POST['_ed_stk_it_location'])) {
		foreach($_POST['_ed_stk_it_location'] as $val) {$_ed_stk_it_location[] = $val;}
		$_ed_stk_it_location = implode(',', $_ed_stk_it_location);
	} else {
		$_ed_stk_it_location = '0';
	}

	if(isset($_POST['_ed_stk_it_qty'])) {
		foreach($_POST['_ed_stk_it_qty'] as $val) {$_ed_stk_it_qty[] = $val;}
		$_ed_stk_it_qty	= implode(',', $_ed_stk_it_qty);
	} else {
		$_ed_stk_it_qty	= '0';
	}

	//demo item list
	if(isset($_POST['_ed_demo_it_code'])) {
		foreach($_POST['_ed_demo_it_code'] as $val) {$_ed_demo_it_code[] = $val;}
		$_ed_demo_it_code = '$$'.implode('$$,$$', $_ed_demo_it_code).'$$';
	} else {
		$_ed_demo_it_code = '$$$$';
	}

	if(isset($_POST['_ed_demo_it_date'])) {
		foreach($_POST['_ed_demo_it_date'] as $val) {$_ed_demo_it_date[] = $val;}
		$_ed_demo_it_date = '$$'.implode('$$,$$', $_ed_demo_it_date).'$$';
	} else {
		$_ed_demo_it_date = '$$$$';
	}

	if(isset($_POST['_ed_demo_it_location'])) {
		foreach($_POST['_ed_demo_it_location'] as $val) {$_ed_demo_it_location[] = $val;}
		$_ed_demo_it_location = implode(',', $_ed_demo_it_location);
	} else {
		$_ed_demo_it_location = '0';
	}

	if(isset($_POST['_ed_demo_it_qty'])) {
		foreach($_POST['_ed_demo_it_qty'] as $val) {$_ed_demo_it_qty[] = $val;}
		$_ed_demo_it_qty = implode(',', $_ed_demo_it_qty);
	} else {
		$_ed_demo_it_qty = '0';
	}

	//reject item list
	if(isset($_POST['_reject_it_code'])) {
		foreach($_POST['_reject_it_code'] as $val) {$_reject_it_code[] = $val;}
		$_reject_it_code = '$$'.implode('$$,$$', $_reject_it_code).'$$';
	} else {
		$_reject_it_code = '$$$$';
	}

	if(isset($_POST['_reject_it_sn'])) {
		foreach($_POST['_reject_it_sn'] as $val) {$_reject_it_sn[] = $val;}
		$_reject_it_sn	= '$$'.implode('$$,$$', $_reject_it_sn).'$$';
	} else {
		$_reject_it_sn	= '$$$$';
	}

	if(isset($_POST['_reject_it_warranty'])) {
		foreach($_POST['_reject_it_warranty'] as $val) {$_reject_it_warranty[] = $val;}
		$_reject_it_warranty = '$$'.implode('$$,$$', $_reject_it_warranty).'$$';
	} else {
		$_reject_it_warranty = '$$$$';
	}

	if(isset($_POST['_reject_it_desc'])) {
		foreach($_POST['_reject_it_desc'] as $val) {$_reject_it_desc[] = $val;}
		$_reject_it_desc = '$$'.implode('$$,$$', $_reject_it_desc).'$$';
	} else {
		$_reject_it_desc = '$$$$';
	}

	//confirmReturn
	$result = executeSP(
		ZKP_SQL."_confirmReturn",
		$_std_idx,
		$_inc_idx,
		$_type,
		"$\${$_remark}$\$",
		"$\${$_cfm_by_account}$\$",
		"$\${$_doc_type}$\$",
		"$\${$_doc_ref}$\$",
		"$\${$_doc_date}$\$",
		"ARRAY[$_it_code]",
		"ARRAY[$_it_ed]",
		"ARRAY[$_it_type]",
		"ARRAY[$_it_stock_qty]",
		"ARRAY[$_it_demo_qty]",
		"ARRAY[$_it_reject_qty]",
		"ARRAY[$_ed_stk_it_code]",
		"ARRAY[$_ed_stk_it_date]",
		"ARRAY[$_ed_stk_it_location]",
		"ARRAY[$_ed_stk_it_qty]",
		"ARRAY[$_ed_demo_it_code]",
		"ARRAY[$_ed_demo_it_date]",
		"ARRAY[$_ed_demo_it_location]",
		"ARRAY[$_ed_demo_it_qty]",
		"ARRAY[$_reject_it_code]",
		"ARRAY[$_reject_it_sn]",
		"ARRAY[$_reject_it_warranty]",
		"ARRAY[$_reject_it_desc]"
	);

	if (isZKError($result)) {
		$errMessage = $result->getMessage();
		if(strpos($errMessage, "DUPLICATE_CODE_EXIST")) {
			$result = new ZKError(
				"DUPLICATE_CODE_EXIST",
				"DUPLICATE_CODE_EXIST",
				"Same Code exists, the possibility is document no. $_doc_ref already confirmed.");
		}
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/daily_return_by_group.php");
	} else if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/confirm_return.php?_inc_idx=$_inc_idx&_std_idx=$_std_idx");
	}

	$_out_idx = $result[0];
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_return.php?_inc_idx=$_inc_idx&_std_idx=$_std_idx");
}


?>