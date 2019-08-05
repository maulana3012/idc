<?php
//INSERT ITEM =========================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/list_item.php", 'insert_item')) {

	$_code			= strtoupper($_POST['_code']);
	$_midx			= $_POST['_midx'];
	$_model_no		= $_POST['_model_no'];
	$_type			= $_POST['_type'];
	$_item_type		= $_POST['_item_type'];
	$_has_ed		= $_POST['_has_ed'];
	$_user_price	= $_POST['_user_price'];
	$_date_from		= $_POST['_date_from'];
	$_desc			= $_POST['_desc'];
	$_remark		= $_POST['_remark'];
	$_created_by_account = ucfirst($S->getValue("ma_account"));
	$_is_vat_item_kurs		= empty($_POST['_user_price_net_kurs']) ? 0 : $_POST['_user_price_net_kurs'];
	$_is_vat_item_rupiah	= empty($_POST['_user_price_net_dollar']) ? 0 : $_POST['_user_price_net_dollar'];
	$_is_vat_item_date		= empty($_POST['_user_price_net_date']) ? '' : $_POST['_user_price_net_date'];

	//Item Value
	if(isset($_POST['_it_code'])) {
		foreach($_POST['_it_code'] as $val)			$_it_code[]		= $val;
		$_it_code		= '$$' . implode('$$,$$', $_it_code) . '$$';
	} else {
		$_it_code		= '$$$$';
	}

	$result = executeSP(
		ZKP_SQL."_insertItem",
		"$\${$_code}$\$",
		$_midx,
		"$\${$_model_no}$\$",
		"$\${$_type}$\$",
		"$\${$_desc}$\$",
		$_user_price,
		"$\${$_date_from}$\$",
		"$\${$_remark}$\$",
		$_item_type,
		"$\${$_has_ed}$\$",
		"$\${$_created_by_account}$\$",
		$_is_vat_item_kurs,
		$_is_vat_item_rupiah,
		"$\${$_is_vat_item_date}$\$",
		"ARRAY[$_it_code]"
	);

	if (isZKError($result)) {
		$errMessage = $result->getMessage();
		if(strpos($errMessage, "duplicate key violates")) {
			$result = new ZKError(
				"DUPLICATE_CODE_EXIST",
				"DUPLICATE_CODE_EXIST",
				"The code : <strong>$_code</strong> already exist. please, use different code");
		}
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/input_item.php");
	}
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_item.php?_code=$_code");
}

//UPDATE ITEM =========================================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/list_item.php", 'update_item')) {

	$_code		= $_POST['_code'];
	$_midx		= $_POST['_midx'];
	$_model_no	= $_POST['_model_no'];
	$_type		= $_POST['_type'];
	$_desc		= $_POST['_desc'];
	$_remark	= $_POST['_remark'];

	$result = executeSP(
		ZKP_SQL."_updateItem",
		"$\${$_code}$\$",
		$_midx,
		"$\${$_model_no}$\$",
		"$\${$_type}$\$",
		"$\${$_desc}$\$",
		"$\${$_remark}$\$");

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/detail_item.php?_code=$_code");
	}

	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_item.php?_code=$_code");
}

//DELETE ITEM =========================================================================================================
if(ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/detail_item.php?_code=$_code", 'delete_item')) {
	$_code = $_POST['_code'];
	$_midx = $_POST['_midx'];

	if(isZKError($result =& query("DELETE FROM ".ZKP_SQL."_tb_item WHERE it_code = '$_code';DELETE FROM ".ZKP_SQL."_tb_set_item WHERE seit_code = '$_code'"))) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/detail_item.php?_code=$_code");
	}

	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/list_item.php?lastCategoryNo=$_midx");
}

//INSERT ITEM CAT =====================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'insert_item_cat')) {
	$_code	= strtoupper($_POST['_code']);
	$_depth = $_POST['_depth'];
	$_name	= $_POST['_name'];

	$result = executeSP(
		ZKP_SQL."_addNewItemCat",
		"$_pidx",
		"$_depth",
		"$\${$_code}$\$",
		"$\${$_name}$\$"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/input_item_cat.php");
	}
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/input_item_cat.php?_pidx=$_pidx&_depth=$_depth");
}

//DELETE ITEM CAT =====================================================================================================
if(ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/input_item_cat.php", 'delete_item_cat')) {
	$_midx = $_POST['_midx'];

	$sql = "DELETE FROM ".ZKP_SQL."_tb_item_cat WHERE icat_midx = $_midx";

	if (isZKError($result =& query($sql))) {

		$result = new ZKError(
			"ITEM_STILL_EXIST",
			"ITEM_STILL_EXIST",
			"In order to delete this category, Please remove all item under this category first.");

		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/input_item_cat.php");
	}
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/input_item_cat.php?_pidx=$_pidx&_depth=$_depth");
}

//INSERT ITEM PRICE ===================================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/list_item_price.php", 'insert_item_price')) {

	$_code		 = $_POST['_code'];
	$_date_from	 = $_POST['_date_from'];
	$_user_price = $_POST['_user_price'];
	$_remark	 = $_POST['_remark'];

	$result = executeSP(
		ZKP_SQL."_makeNewPrice",
		"$\${$_code}$\$",
		"$\${$_date_from}$\$",
		$_user_price,
		"$\${$_remark}$\$",
		"$\$".$S->getValue("ma_account")."$\$"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/detail_item.php?_code=$_code");
	}
	goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_item.php?_code=$_code");
}

//UPDATE ITEM PRICE ===================================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/list_item_price.php", 'update_item_price')) {

	$_code		 = $_POST['_code'];
	$_idx		  = $_POST['_idx'];
	$_date_from  = $_POST['_date_from'];
	$_user_price = $_POST['_user_price'];
	$_remark     = $_POST['_remark'];

	$result = executeSP(
		ZKP_SQL."_updateItemPrice",
		"$\${$_code}$\$",
		$_idx,
		"$\${$_date_from}$\$",
		$_user_price,
		"$\${$_remark}$\$",
		"$\$".$S->getValue("ma_account")."$\$"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/detail_item.php?_code=$_code");
	}
	goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_item.php?_code=$_code");
}

//INSERT ITEM PRICE NET ===================================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/list_item_price.php", 'insert_item_price_net')) {

	$_code		= $_POST['_code'];
	$_date_from	= $_POST['_date_from'];
	$_price_dollar	= $_POST['_price_dollar'];
	$_price_rupiah	= $_POST['_price_rupiah'];
	$_remark	= $_POST['_remark'];

	$result = executeSP(
		ZKP_SQL."_makeNewPriceNet",
		"$\${$_code}$\$",
		"$\${$_date_from}$\$",
		$_price_dollar,
		$_price_rupiah,
		"$\${$_remark}$\$",
		"$\$".$S->getValue("ma_account")."$\$"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/detail_item.php?_code=$_code");
	}
	goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_item.php?_code=$_code");
}

//UPDATE ITEM PRICE NET ===================================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/list_item_price.php", 'update_item_price_net')) {

	$_code		= $_POST['_code'];
	$_idx		= $_POST['_idx'];
	$_date_from	= $_POST['_date_from'];
	$_price_dollar	= $_POST['_price_dollar'];
	$_price_rupiah	= $_POST['_price_rupiah'];
	$_remark	= $_POST['_remark'];

	$result = executeSP(
		ZKP_SQL."_updateItemPriceNet",
		"$\${$_code}$\$",
		$_idx,
		"$\${$_date_from}$\$",
		$_price_dollar,
		$_price_rupiah,
		"$\${$_remark}$\$",
		"$\$".$S->getValue("ma_account")."$\$"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/detail_item.php?_code=$_code");
	}
	goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_item.php?_code=$_code");
}
?>