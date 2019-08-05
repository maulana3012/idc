<?php
//INSERT COMPLAIN =====================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/input_complain.php", "insert_complain")) {

	$_tanggal			= $_POST['_tanggal'];
	$_customer			= $_POST['_customer'];
	$_category			= $_POST['cboCategory'];
	$_desc_complain		= $_POST['_desc_complain'];
	$_action			= $_POST['_action'];
	$_remark			= $_POST['_remark'];
	$_created_by		= ucfirst($S->getValue("ma_account"));

	$result = executeSP(
		ZKP_SQL."_insertCustomerComplain",
		"DATE $\${$_tanggal}$\$",
		"$\${$_customer}$\$",
		"$\${$_category}$\$",
		"$\${$_desc_complain}$\$",
		"$\${$_action}$\$",
		"$\${$_remark}$\$",
		"$\${$_created_by}$\$"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/input_complain.php");
	}
	$M->goPage(HTTP_DIR ."$currentDept/$moduleDept/detail_complain.php?_code=".$result[0]);
}

//UPDATE COMPLAIN =====================================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/list_complain.php", "update_complain")) {

	$_code				= $_POST['_code'];
	$_tanggal			= $_POST['_tanggal'];
	$_customer			= $_POST['_customer'];
	$_category			= $_POST['cboCategory'];
	$_desc_complain		= $_POST['_desc_complain'];
	$_action			= $_POST['_action'];
	$_remark			= $_POST['_remark'];
	$_updated_by		= ucfirst($S->getValue("ma_account"));

	$result = executeSP(
		ZKP_SQL."_updateCustomerComplain",
		"$\${$_code}$\$",
		"DATE $\${$_tanggal}$\$",
		"$\${$_customer}$\$",
		"$\${$_category}$\$",
		"$\${$_desc_complain}$\$",
		"$\${$_action}$\$",
		"$\${$_remark}$\$",
		"$\${$_updated_by}$\$"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/detail_complain.php?_code=$_code");
	}
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_complain.php?_code=$_code");
}

//DELETE COMPLAIN =====================================================================================================
if(ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/list_complain.php", "delete_complain")) {
	$_code = $_POST['_code'];
	$sql = "DELETE FROM ".ZKP_SQL."_tb_customer_complain WHERE cp_idx = $_code";
	if (isZKError($result =& query($sql))) {
		$M->goErrorPage($result, HTTP_DIR ."$currentDept/$moduleDept/detail_complain.php?_code=$_code");
	}
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/list_complain.php");
}
?>