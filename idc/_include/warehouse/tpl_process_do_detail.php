<?php
//CONFIRM DO  REVISED ==========================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'confirm_do_revised')) {

	$_out_idx	= $_POST["_out_idx"];
	$_book_idx	= $_POST["_book_idx"];
	$_out_type	= $_POST["_out_type"];
	$_out_doc_type	= $_POST["_doc_type"];
	$_out_doc_ref	= 'D' . substr(trim($_POST["_doc_ref"]),1);
	$_issued_date	= $_POST["_book_date"];
	$_account_name	= $S->getValue("ma_account");
	$_password	= md5($_POST["_password"]);

	// Item Value
	foreach($_POST['_it_code'] as $val)	$_it_code[]	= $val;
	foreach($_POST['_it_ed'] as $val)	$_it_ed[] 	= $val;
	foreach($_POST['_it_qty'] as $val)	$_it_qty[] 	= $val;

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
			$_ed_it_date[]	 = date('Y-m-d',strtotime('1-'.$val));
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
	$_it_code	= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_it_ed		= '$$' . implode('$$,$$', $_it_ed) . '$$';
	$_it_qty	= implode(',', $_it_qty);

	//confirm DO Revised
	$result = executeSP(
		ZKP_SQL."_addNewDeliveryStockRevised",
		$_out_idx,
		$_book_idx,
		$_out_type,
		$_out_doc_type,
		"$\${$_out_doc_ref}$\$",
		"$\${$_issued_date}$\$",
		"$\${$_account_name}$\$",
		"$\${$_password}$\$",
		"ARRAY[$_it_code]",
		"ARRAY[$_it_ed]",
		"ARRAY[$_it_qty]",
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
		} else if(strpos($errMessage, "FAIL_TO_AUTH")) {
			$result = new ZKError(
				"FAIL_TO_AUTHORITY",
				"FAIL_TO_AUTHORITY",
				"You input wrong password, please Try again. Also check [Caps Lock] Key");
		}
		$M->goErrorPage($result, HTTP_DIR ."$currentDept/$moduleDept/detail_do.php?_code=$_out_idx&_source=v2");
	} else if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/detail_do.php?_code=$_out_idx&_source=v2");
	}
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_do.php?_code=$_out_idx&_source=v2");
}

//UNCONFIRM RETURN =================================================================================
if(ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'unconfirmed_return')) {

	$_inc_idx	= $_POST["_inc_idx"];
	$_std_idx	= $_POST["_std_idx"];
	$_doc_type	= $_POST["_doc_type"];
	$_doc_ref	= $_POST["_doc_ref"];
	$_admin_account	= $S->getValue('ma_idx');
	$_admin_password= md5($_POST["_password"]);
	$_log_by	= $S->getValue('ma_account');

	//unConfirmedDO
	$result = executeSP(
		ZKP_SQL."_unConfirmedReturn",
		$_inc_idx,
		$_std_idx,
		$_doc_type,
		"$\${$_doc_ref}$\$",
		$_admin_account,
		"$\${$_admin_password}$\$",
		"$\${$_log_by}$\$"
	);

	if (isZKError($result)) {
		$errMessage = $result->getMessage();
		if(strpos($errMessage, "FAIL_TO_AUTH")) {
			$result = new ZKError(
				"FAIL_TO_AUTHORITY",
				"FAIL_TO_AUTHORITY",
				"Your password wrong, please Try again. Also check [Caps Lock] Key");
		}
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/detail_return.php?_inc_idx=$_inc_idx&_std_idx=$_std_idx");
	}

	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/confirm_return.php?_inc_idx=$_inc_idx&_std_idx=$_std_idx");
}
