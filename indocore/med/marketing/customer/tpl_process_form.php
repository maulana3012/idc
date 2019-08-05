<?php
//INSERT CUSTOMER =====================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/input_customer.php", "insert_cus")) {

	$_channel			= $_POST['_channel'];
	$_code				= strtoupper($_POST['_code']);
	$_check_code		= $_POST['_check_code'];
	$_since				= $_POST['_since'];
	$_address			= $_POST['_address'];
	$_phone				= $_POST['_phone'];
	$_fax				= $_POST['_fax'];
	$_city				= $_POST['_city'];
	$_name				= $_POST['_name'];
	$_company_title		= $_POST['_company_title'];
	$_full_name			= $_POST['_full_name'];
	$_customer_group	= $_POST['_customer_group'];
	$_representative	= $_POST['_representative'];
	$_introduced_by		= $_POST['_introduced_by'];
	$_type_of_biz		= $_POST['_type_of_biz'];
	$_tax_code_status	= $_POST['_tax_code_status'];
	$_contact			= $_POST['_contact'];
	$_contact_email		= $_POST['_contact_email'];
	$_contact_position	= $_POST['_contact_position'];
	$_contact_phone		= $_POST['_contact_phone'];
	$_contact_hphone	= $_POST['_contact_hphone'];
	$_marketing_staff	= $_POST['_marketing_staff'];
	$_remark			= $_POST['_remark'];

	$result = executeSP(
		ZKP_SQL."_addNewCustomer",
		"$\${$_channel}$\$",
		"$\${$_code}$\$",
		"DATE $\${$_since}$\$",
		"$\${$_company_title}$\$",
		"$\${$_full_name}$\$",
		"$\${$_customer_group}$\$",
		"$\${$_name}$\$",
		"$\${$_representative}$\$",
		"$\${$_introduced_by}$\$",
		"$\${$_type_of_biz}$\$",
		"$\${$_tax_code_status}$\$",		
		"$\${$_contact}$\$",
		"$\${$_contact_position}$\$",
		"$\${$_contact_phone}$\$",
		"$\${$_contact_hphone}$\$",
		"$\${$_contact_email}$\$",
		"$\${$_address}$\$",
		"$\${$_phone}$\$",
		"$\${$_fax}$\$",
		"$\${$_city}$\$",
		$_marketing_staff,
		"$\${$_remark}$\$"
	);

	if (isZKError($result)) {
		$errMessage = $result->getMessage();
		if(strpos($errMessage, "duplicate key value violates unique constraint")) {
			$result = new ZKError(
				"DUPLICATE_CODE_EXIST",
				"DUPLICATE_CODE_EXIST",
				"The code : <strong>$_code</strong> already exist. please, use different code");
		}
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/input_customer.php");
	}
	$M->goPage(HTTP_DIR ."$currentDept/$moduleDept/detail_customer.php?_code=$_code&_channel=$_channel");
}

//UPDATE CUSTOMER =====================================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/list_customer.php", "update_cus")) {

	set_magic_quotes_runtime(1);
	$_code				= $_POST['_code'];
	$_channel			= $_POST['_channel'];
	$_since				= $_POST['_since'];
	$_company_title		= $_POST['_company_title'];
	$_full_name			= $_POST['_full_name'];
	$_customer_group	= $_POST['_customer_group'];
	$_name				= $_POST['_name'];
	$_representative	= $_POST['_representative'];
	$_introduced_by		= $_POST['_introduced_by'];
	$_type_of_biz		= $_POST['_type_of_biz'];
	$_tax_code_status	= $_POST['_tax_code_status'];
	$_contact			= $_POST['_contact'];
	$_contact_position	= $_POST['_contact_position'];
	$_contact_phone		= $_POST['_contact_phone'];
	$_contact_hphone	= $_POST['_contact_hphone'];
	$_contact_email		= $_POST['_contact_email'];
	$_address			= $_POST['_address'];
	$_phone				= $_POST['_phone'];
	$_fax				= $_POST['_fax'];
	$_city				= $_POST['_city'];
	$_marketing_staff	= $_POST['_marketing_staff'];
	$_remark			= $_POST['_remark'];

	$result = executeSP(
		ZKP_SQL."_updateCustomer",
		"$\${$_code}$\$",
		"$\${$_customer_group}$\$",
		"$\${$_name}$\$",
		"$\${$_full_name}$\$",
		"$\${$_channel}$\$",
		"$\${$_representative}$\$",
		"$\${$_company_title}$\$",
		"$\${$_type_of_biz}$\$",
		"$\${$_tax_code_status}$\$",		
		"$\${$_since}$\$",
		"$\${$_introduced_by}$\$",
		"$\${$_contact}$\$",
		"$\${$_contact_position}$\$",
		"$\${$_contact_phone}$\$",
		"$\${$_contact_hphone}$\$",
		"$\${$_contact_email}$\$",
		"$\${$_fax}$\$",
		"$\${$_city}$\$",
		"$\${$_address}$\$",
		"$\${$_phone}$\$",
		$_marketing_staff,
		"$\${$_remark}$\$"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/detail_customer.php?_channel=$_channel&_code=$_code");
	}
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_customer.php?_channel=$_channel&_code=$_code");
}

//DELETE CUSTOMER =====================================================================================================
if(ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/list_customer.php", "delete_cus")) {
	$_code = $_POST['_code'];
	$sql = "DELETE FROM ".ZKP_SQL."_tb_customer WHERE cus_code = '$_code'";
	if (isZKError($result =& query($sql))) {
		$M->goErrorPage($result, HTTP_DIR ."$currentDept/$moduleDept/detail_customer.php?_channel=$_channel&_code=$_code");
	}
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/list_customer.php?_channel=$_channel");
}

//BLOCK CUSTOMER =====================================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/list_customer.php", "block_cus")) {
	$_code = $_POST['_code'];
	$sql = "UPDATE ".ZKP_SQL."_tb_customer SET cus_is_blocked = TRUE, cus_is_blocked_timestamp = CURRENT_TIMESTAMP, cus_is_blocked_by = '".ucfirst($S->getValue("ma_account"))."' WHERE cus_code = '$_code'";
	if (isZKError($result =& query($sql))) {
		$M->goErrorPage($result, HTTP_DIR ."$currentDept/$moduleDept/detail_customer.php?_channel=$_channel&_code=$_code");
	}
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_customer.php?_channel=$_channel&_code=$_code");
}

//UNBLOCK CUSTOMER =====================================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/list_customer.php", "unblock_cus")) {
	$_code = $_POST['_code'];
	$sql = "UPDATE ".ZKP_SQL."_tb_customer SET cus_is_blocked = FALSE, cus_is_blocked_timestamp = NULL, cus_is_blocked_by = '' WHERE cus_code = '$_code'";
	if (isZKError($result =& query($sql))) {
		$M->goErrorPage($result, HTTP_DIR ."$currentDept/$moduleDept/detail_customer.php?_channel=$_channel&_code=$_code");
	}
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_customer.php?_channel=$_channel&_code=$_code");
}

//INSERT CUSTOMER GROUP ===============================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/list_cus_group.php", 'insert_cus_group')) {
	$_code		= strtoupper($_POST['_code']);
	$_regtime	= $_POST['_regtime'];
	$_name		= $_POST['_name'];
	$_remark	= $_POST['_remark'];
	$_basic_disc_pct = $_POST['_basic_disc_pct'];

	$result = executeSP(
		ZKP_SQL."_addNewCusGroup",
		"$\${$_code}$\$",
		"$\${$_name}$\$",
		"$\${$_regtime}$\$",
		"$\${$_remark}$\$",
		$_basic_disc_pct
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/input_cus_group.php");
	}
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/list_cus_group.php");
}

//UPDATE CUSTOMER GROUP ===============================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/detail_cus_group.php?_code=$_code", 'update_cus_group')) {
	$_code		= $_POST['_code'];
	$_regtime	= $_POST['_regtime'];
	$_name		= $_POST['_name'];
	$_remark	= $_POST['_remark'];
	$_basic_disc_pct = $_POST['_basic_disc_pct'];

	$result = executeSP(
		ZKP_SQL."_updateCusGroup",
		"$\${$_code}$\$",
		"$\${$_name}$\$",
		"$\${$_regtime}$\$",
		"$\${$_remark}$\$",
		$_basic_disc_pct);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/detail_cus_group.php?_code=$_code");
	}
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_cus_group.php?_code=$_code");
}

//DELETE CUSTOMER GROUP ===============================================================================================
if(ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/detail_cus_group.php?_code=$_code", "delete_cus_group")) {
	$sql = "DELETE FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_code'";
	if(isZKError($result =& query($sql))) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/detail_cus_group.php?_code=$_code");
	}
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/list_cus_group.php");
}
?>