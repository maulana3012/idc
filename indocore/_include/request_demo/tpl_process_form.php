<?php
//INSERT REQUEST ======================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'insert_request')) {

	$_dept				= $_POST['_dept'];
	$_request_by		= $_POST['_request_by'];
	$_request_date		= $_POST['_request_date'];
	$_cus_to			= $_POST['_cus_to'];
	$_cus_name			= $_POST['_cus_name'];
	$_cus_address		= $_POST['_cus_address'];
	$_lastupdated_by_account	= $S->getValue("ma_account");
	$_revision_time 	= -1;
	$_sign_by			= $_POST['_sign_by'];
	$_remark			= $_POST['_remark'];

	//Item Value
	foreach($_POST['_it_code'] as $val)			$_it_code[]			= $val;
	foreach($_POST['_it_returnable'] as $val)	$_it_returnable[]	= $val;
	foreach($_POST['_it_qty'] as $val)			$_it_qty[]			= $val;
	foreach($_POST['_it_remark'] as $val)		$_it_remark[]		= $val;
	$_it_code			= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_it_returnable		= '$$' . implode('$$,$$', $_it_returnable) . '$$';
	$_it_qty			= implode(',', $_it_qty);
	$_it_remark			= '$$' . implode('$$,$$', $_it_remark) . '$$';

	$result = executeSP(
		ZKP_SQL."_insertRequestDemo",
		"$\$".ZKP_SQL."$\$",
		"$\${$_dept}$\$",
		"$\${$_request_by}$\$",
		"$\${$_request_date}$\$",
		"$\${$_cus_to}$\$",
		"$\${$_cus_name}$\$",
		"$\${$_cus_address}$\$",
		"$\${$_sign_by}$\$",
		"$\${$_remark}$\$",
		"$\${$_lastupdated_by_account}$\$",
		"ARRAY[$_it_code]",
		"ARRAY[$_it_returnable]",
		"ARRAY[$_it_qty]",
		"ARRAY[$_it_remark]"
	);

	if (isZKError($result)) {
		$errMessage = $result->getMessage();
		if(strpos($errMessage, "duplicate key violates")) {
			$result = new ZKError(
				"DUPLICATE_CODE_EXIST",
				"DUPLICATE_CODE_EXIST",
				"Same Code exists, please check your code again");
		}
		$M->goErrorPage($result,  "$currentDept/$moduleDept/index.php");
	}

	$_code = $result[0];
	include APP_DIR . "_include/request_demo/pdf/generate_request_pdf.php";
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_request.php?_code=".$_code);
}

//DELETE REQUEST ======================================================================================================
if (ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'delete_request')) {

	$_rev = (int) $_POST['_revision_time'];
	$_request_date = date("Ym", strtotime($_POST['_request_date']));

	$result =& query("DELETE FROM ".ZKP_SQL."_tb_using_demo WHERE use_code = '$_code'");

	if(isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_request.php?_code=".urlencode($_code));
	} else {
		for ($i = $_rev ; $i >=0 ; $i--) {
			@unlink(PDF_STORAGE . "marketing/using/{$_request_date}/{$_code}_rev_{$i}.pdf");
		}
	}
	goPage(HTTP_DIR . "$currentDept/$moduleDept/daily_request_demo_by_reference.php");
}

//UODATE REQUEST ======================================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'update_request')) {

	$_code				= $_POST['_code'];
	$_request_by		= $_POST['_request_by'];
	$_request_date		= $_POST['_request_date'];
	$_cus_to			= $_POST['_cus_to'];
	$_cus_name			= $_POST['_cus_name'];
	$_cus_address		= $_POST['_cus_address'];
	$_lastupdated_by_account	= $S->getValue("ma_account");
	$_sign_by			= $_POST['_sign_by'];
	$_remark			= $_POST['_remark'];
	$_revision_time 	= $_POST['_revision_time'];

	//Item Value
	foreach($_POST['_it_code'] as $val)			$_it_code[]			= $val;
	foreach($_POST['_it_returnable'] as $val)	$_it_returnable[]	= $val;
	foreach($_POST['_it_qty'] as $val)			$_it_qty[]			= $val;
	foreach($_POST['_it_remark'] as $val)		$_it_remark[]		= $val;
	$_it_code			= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_it_returnable		= '$$' . implode('$$,$$', $_it_returnable) . '$$';
	$_it_qty			= implode(',', $_it_qty);
	$_it_remark			= '$$' . implode('$$,$$', $_it_remark) . '$$';

	$result = executeSP(
		ZKP_SQL."_updateRequestDemo",
		"$\${$_code}$\$",
		"$\${$_request_by}$\$",
		"$\${$_request_date}$\$",
		"$\${$_cus_to}$\$",
		"$\${$_cus_name}$\$",
		"$\${$_cus_address}$\$",
		"$\${$_sign_by}$\$",
		"$\${$_remark}$\$",
		"$\${$_lastupdated_by_account}$\$",
		"ARRAY[$_it_code]",
		"ARRAY[$_it_returnable]",
		"ARRAY[$_it_qty]",
		"ARRAY[$_it_remark]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/revise_request.php?_code=".$_code);
	}

	include APP_DIR . "_include/request_demo/pdf/generate_request_pdf.php";
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_request.php?_code=".$_code);
}

//INSERT RETURN ======================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'insert_return')) {

	$_use_code			= $_POST['_use_code'];
	$_dept				= $_POST['_dept'];
	$_return_by			= $_POST['_return_by'];
	$_return_date		= $_POST['_return_date'];
	$_cus_code			= $_POST['_cus_code'];
	$_lastupdated_by_account	= $S->getValue("ma_account");
	$_revision_time 	= -1;
	$_sign_by			= $_POST['_sign_by'];
	$_remark			= $_POST['_remark'];

	//Item Value
	foreach($_POST['_it_code'] as $val)			$_it_code[]			= $val;
	foreach($_POST['_it_qty'] as $val)			$_it_qty[]			= $val;
	$_it_code			= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_it_qty			= implode(',', $_it_qty);

	$result = executeSP(
		ZKP_SQL."_insertReturnDemo",
		"$\$".ZKP_SQL."$\$",
		"$\${$_use_code}$\$",
		"$\${$_dept}$\$",
		"$\${$_return_by}$\$",
		"$\${$_return_date}$\$",
		"$\${$_cus_code}$\$",
		"$\${$_sign_by}$\$",
		"$\${$_remark}$\$",
		"$\${$_lastupdated_by_account}$\$",
		"ARRAY[$_it_code]",
		"ARRAY[$_it_qty]"
	);

	if (isZKError($result)) {
		$errMessage = $result->getMessage();
		if(strpos($errMessage, "duplicate key violates")) {
			$result = new ZKError(
				"DUPLICATE_CODE_EXIST",
				"DUPLICATE_CODE_EXIST",
				"Same Code exists, please check your code again");
		}
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/input_return.php?_code=$_code");
	}

	//SAVE PDF FILE
	$_code = $result[0];
	include APP_DIR . "_include/request_demo/pdf/generate_return_pdf.php";
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_return.php?_code=$_code");
}

//DELETE RETURN =======================================================================================================
if (ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'delete_return')) {

	$_use_code		= $_POST['_use_code'];
	$_rev			= (int) $_POST['_revision_time'];
	$_return_date	= date("Ym", strtotime($_POST['_return_date']));

	$result =& query("DELETE FROM ".ZKP_SQL."_tb_return_demo WHERE red_code = '$_code'");

	if(isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_return.php?_code=".urlencode($_code));
	} else {
		for ($i = $_rev ; $i >=0 ; $i--) {
			@unlink(PDF_STORAGE . "marketing/using/{$_return_date}/{$_code}_rev_{$i}.pdf");
		}
	}
	goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_request.php?_code=$_use_code");
}

//UODATE RETURN =======================================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'update_return')) {

	$_code				= $_POST['_code'];
	$_return_by			= $_POST['_return_by'];
	$_return_date		= $_POST['_return_date'];
	$_cus_to			= $_POST['_cus_to'];
	$_sign_by			= $_POST['_sign_by'];
	$_remark			= $_POST['_remark'];
	$_lastupdated_by_account	= $S->getValue("ma_account");
	$_revision_time 	= $_POST['_revision_time'];
	$_use_code			= $_POST['_use_code'];

	//Item Value
	foreach($_POST['_it_code'] as $val)			$_it_code[]			= $val;
	foreach($_POST['_it_qty'] as $val)			$_it_qty[]			= $val;
	$_it_code			= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_it_qty			= implode(',', $_it_qty);

	$result = executeSP(
		ZKP_SQL."_updateReturnDemo",
		"$\${$_code}$\$",
		"$\${$_return_by}$\$",
		"$\${$_return_date}$\$",
		"$\${$_cus_to}$\$",
		"$\${$_sign_by}$\$",
		"$\${$_remark}$\$",
		"$\${$_lastupdated_by_account}$\$",
		"ARRAY[$_it_code]",
		"ARRAY[$_it_qty]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/revise_return.php?_code=".$_code);
	}

	include APP_DIR . "_include/request_demo/pdf/generate_return_pdf.php";
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_return.php?_code=".$_code);
}
?>