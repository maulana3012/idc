<?php
//INSERT REQUEST ======================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'insert_request')) {

	$_issued_by			= $_POST['_issued_by'];
	$_issued_date		= $_POST['_issued_date'];
	$_lastupdated_by_account	= $S->getValue("ma_account");
	$_revision_time 	= -1;
	$_remark			= $_POST['_remark'];

	//Item Value
	foreach($_POST['_wh_it_code'] as $val)		$_wh_it_code[]		= $val;
	foreach($_POST['_wh_it_qty'] as $val)		$_wh_it_qty[]		= $val;
	foreach($_POST['_wh_it_remark'] as $val)	$_wh_it_remark[]	= $val;
	$_wh_it_code		= '$$' . implode('$$,$$', $_wh_it_code) . '$$';
	$_wh_it_qty			= implode(',', $_wh_it_qty);
	$_wh_it_remark		= '$$' . implode('$$,$$', $_wh_it_remark) . '$$';

	$result = executeSP(
		ZKP_SQL."_insertRequestStocktoDemo",
		"$\$".ZKP_SQL."$\$",
		"$\${$_issued_by}$\$",
		"$\${$_issued_date}$\$",
		"$\${$_lastupdated_by_account}$\$",
		"$\${$_remark}$\$",
		"ARRAY[$_wh_it_code]",
		"ARRAY[$_wh_it_qty]",
		"ARRAY[$_wh_it_remark]"
	);

	if (isZKError($result)) {
		$errMessage = $result->getMessage();
		if(strpos($errMessage, "duplicate key violates")) {
			$result = new ZKError(
				"DUPLICATE_CODE_EXIST",
				"DUPLICATE_CODE_EXIST",
				"Same Code exists, please check your order code again");
		}
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/input_request.php");
	}

		$_code = $result[0];
		include APP_DIR . "_include/demo/pdf/generate_request_pdf.php";
		$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_request.php?_code=".$_code);
}

//DELETE REQUEST ======================================================================================================
if (ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'delete_request')) {

	$_rev		= (int) $_POST['_revision_time'];
	$_book_idx	= $_POST['_book_idx'];
	$_issued_date	= date("Ym", strtotime($_POST['_issued_date']));

	$result =& query(
				"DELETE FROM ".ZKP_SQL."_tb_request WHERE req_code = '$_code';
				DELETE FROM ".ZKP_SQL."_tb_booking WHERE book_idx = $_book_idx");

	if(isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_request.php?_code=".urlencode($_code));
	} else {
		for ($i = $_rev ; $i >=0 ; $i--) {
			@unlink(PDF_STOREGE . "marketing/request/{$_issued_date}/{$_code}_rev_{$i}.pdf");
		}
	}
	goPage(HTTP_DIR . "$currentDept/$moduleDept/daily_request_demo_by_reference.php");
}

//UPDATE REQUEST ======================================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'update_request')) {

	$_code				= $_POST['_code'];
	$_book_idx			= $_POST['_book_idx'];
	$_issued_by			= $_POST['_issued_by'];
	$_issued_date		= $_POST['_issued_date'];
	$_remark			= $_POST['_remark'];
	$_revision_time		= (int) $_POST['_revision_time'];
	$_lastupdated_by_account	= $S->getValue("ma_account");

	//Item Value
	foreach($_POST['_wh_it_code'] as $val)		$_wh_it_code[]		= $val;
	foreach($_POST['_wh_it_qty'] as $val)		$_wh_it_qty[]		= $val;
	foreach($_POST['_wh_it_remark'] as $val)	$_wh_it_remark[]	= $val;
	$_wh_it_code		= '$$' . implode('$$,$$', $_wh_it_code) . '$$';
	$_wh_it_qty			= implode(',', $_wh_it_qty);
	$_wh_it_remark		= '$$' . implode('$$,$$', $_wh_it_remark) . '$$';

	$result = executeSP(
		ZKP_SQL."_reviseRequestStocktoDemo",
		"$\${$_code}$\$",
		$_book_idx,
		"$\${$_issued_by}$\$",
		"$\${$_issued_date}$\$",
		"$\${$_lastupdated_by_account}$\$",
		"$\${$_remark}$\$",
		$_revision_time,
		"ARRAY[$_wh_it_code]",
		"ARRAY[$_wh_it_qty]",
		"ARRAY[$_wh_it_remark]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/revise_request.php?_code=".$_code);
	}

	include APP_DIR . "_include/demo/pdf/generate_request_pdf.php";
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_request.php?_code=".$_code);
}

//CONFIRM RECEIVED STOCK ==============================================================================================
if (ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'cfm_received')) {

	$_wh_idx			= $_POST['_wh_idx'];
	$_type				= $_POST['_type'];
	$_doc				= $_POST['_code'];
	$_idx				= ($_POST['_type'] == 2) ? $_POST['_code'] : null;
	$_received_by		= $_POST['_received_by'];
	$_received_date 	= $_POST['_received_date'];
	$_cfm_received_by	= $S->getValue("ma_account");
	$go_page			= ($_POST['_type'] == 1) ? "detail_request.php" : "detail_return.php";

	$result = executeSP(
		ZKP_SQL."_cfmRequestByMarketing",
		$_wh_idx,
		$_type,
		"$\${$_doc}$\$",
		$_idx,		
		"$\${$_received_by}$\$",
		"$\${$_received_date}$\$",
		"$\${$_cfm_received_by}$\$"
	);

	if (isZKError($result)) {
		$errMessage = $result->getMessage();
		if(strpos($errMessage, "duplicate key value violates")) {
			$result = new ZKError(
				"DUPLICATE_CODE_EXIST",
				"DUPLICATE_CODE_EXIST",
				"Same Code exists, the possibility is document no. $_code already confirmed.");
		}
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/{$go_page}?_code=".urlencode($_code));
	} else if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/{$go_page}?_code=".urlencode($_code));
	} else {
		goPage(HTTP_DIR . "$currentDept/$moduleDept/{$go_page}?_code=".urlencode($_code));
	}
}

//UNCONFIRM RECEIVED STOCK ============================================================================================
if (ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'uncfm_received')) {

	$_type	= $_POST['_type'];
	$_doc	= $_POST['_code'];
	$_idx	= "null";

	$result = executeSP(
		ZKP_SQL."_unCfmRequestByMarketing",
		$_type,
		"$\${$_doc}$\$",
		$_idx		
	);

	if (isZKError($result)) $M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/{$go_page}?_code=".urlencode($_code));
	goPage(HTTP_DIR . "$currentDept/$moduleDept/{$go_page}?_code=".urlencode($_code));
}
?>