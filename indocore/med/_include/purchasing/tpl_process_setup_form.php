<?php
//========================================================================================== RETURN BORROW
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'return_borrow')) {

	foreach ($_POST['chkBorIdx'] as $val) {
		$_box_idx[]	= $val;
	}

	$_box_idx	= implode(',', $_box_idx);
	$_confirm_by_account = $S->getValue("ma_account");

	if(isset($_POST['_ed_it_code'])) {
		foreach($_POST['_ed_it_code'] as $val) {
			$_ed_it_code[] = $val;
		}
		$_ed_it_code = '$$' . implode('$$,$$', $_ed_it_code) . '$$';
	} else {$_ed_it_code = '$$$$';}

	if(isset($_POST['_ed_it_location'])) {
		foreach($_POST['_ed_it_location'] as $val) {
			$_ed_it_location[] = $val;
		}
		$_ed_it_location = implode(',', $_ed_it_location);
	} else {$_ed_it_location = '0';}

	if(isset($_POST['_ed_it_type'])) {
		foreach($_POST['_ed_it_type'] as $val) {
			$_ed_it_type[] = $val;
		}
		$_ed_it_type = implode(',', $_ed_it_type);
	} else {$_ed_it_type = '0';}

	if(isset($_POST['_ed_it_date'])) {
		foreach($_POST['_ed_it_date'] as $val) {
			$_ed_it_date[] = $val;
		}
		$_ed_it_date = '$$' . implode('$$,$$', $_ed_it_date) . '$$';
	} else {$_ed_it_date = '$$$$';}

	if(isset($_POST['_ed_it_qty'])) {
		foreach($_POST['_ed_it_qty'] as $val) {
			$_ed_it_qty[] 		 = number_format($val,2);
		}
		$_ed_it_qty		= implode(',', $_ed_it_qty);
	} else {$_ed_it_qty	= '0.00';}

	$result = executeSP(
		ZKP_SQL."_insertReturnBorrow",
		"$\$$\$",
		"ARRAY[$_box_idx]",
		"$\${$_confirm_by_account}$\$",
		"ARRAY[$_ed_it_code]",
		"ARRAY[$_ed_it_location]",
		"ARRAY[$_ed_it_type]",
		"ARRAY[$_ed_it_date]",
		"ARRAY[$_ed_it_qty]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/list_borrow_by_item.php");
	}
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/list_borrow_by_item.php");
}

//========================================================================================== CHANGE TYPE
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'change_type')) {

	$_doc_number = $_POST['_doc_number'];
	$_doc_date = $_POST['_doc_date'];
	$_type_item = $_POST['_type_item'];
	$_location = $_POST['_location'];
	$_it_code = $_POST['_it_code'];
	$_confirm_by_account = $S->getValue("ma_account");

	foreach ($_POST['chkBorIdx'] as $val)	$_box_idx[]	= $val;
		$_box_idx = implode(',', $_box_idx);

	if(isset($_POST['_ed_it_code'])) {
		foreach($_POST['_ed_it_code'] as $val) {
			$_ed_it_code[] = $val;
		}
		$_ed_it_code = '$$' . implode('$$,$$', $_ed_it_code) . '$$';
	} else {$_ed_it_code = '$$$$';}

	if(isset($_POST['_ed_it_location'])) {
		foreach($_POST['_ed_it_location'] as $val) {
			$_ed_it_location[] = $val;
		}
		$_ed_it_location = implode(',', $_ed_it_location);
	} else {$_ed_it_location = '0';}

	if(isset($_POST['_ed_it_type'])) {
		foreach($_POST['_ed_it_type'] as $val) {
			$_ed_it_type[] = $val;
		}
		$_ed_it_type = implode(',', $_ed_it_type);
	} else {$_ed_it_type = '0';}

	if(isset($_POST['_ed_it_date'])) {
		foreach($_POST['_ed_it_date'] as $val) {
			$_ed_it_date[] = $val;
		}
		$_ed_it_date = '$$' . implode('$$,$$', $_ed_it_date) . '$$';
	} else {$_ed_it_date = '$$$$';}

	if(isset($_POST['_ed_it_qty'])) {
		foreach($_POST['_ed_it_qty'] as $val) {
			$_ed_it_qty[] 		 = number_format($val,2);
		}
		$_ed_it_qty		= implode(',', $_ed_it_qty);
	} else {$_ed_it_qty	= '0.00';}

	$result = executeSP(
		ZKP_SQL."_insertReturnBorrow",
		"$\$CHANGE TYPE$\$",
		"$\${$_doc_number}$\$",
		"$\${$_doc_date}$\$",
		$_type_item,
		$_location,
		"ARRAY[$_box_idx]",
		"ARRAY[$_it_code]",
		"$\${$_confirm_by_account}$\$",
		"ARRAY[$_ed_it_code]",
		"ARRAY[$_ed_it_location]",
		"ARRAY[$_ed_it_type]",
		"ARRAY[$_ed_it_date]",
		"ARRAY[$_ed_it_qty]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, "input_move_type.php?".getQueryString());
	}
	$M->goPage("input_move_type.php?".getQueryString());
}


//========================================================================================== CHANGE TYPE FROM PO FORMALITAS
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'change_type_po')) {

	$_doc_number = $_POST['_doc_number'];
	$_doc_date = $_POST['_doc_date'];
	$_type_item = $_POST['_type_item'];
	$_location = $_POST['_location'];
	$_it_code = $_POST['_it_code'];
	$_confirm_by_account = $S->getValue("ma_account");

	foreach ($_POST['chkBorIdx'] as $val)	$_box_idx[]	= $val;
		$_box_idx = implode(',', $_box_idx);

	if(isset($_POST['_ed_it_code'])) {
		foreach($_POST['_ed_it_code'] as $val) {
			$_ed_it_code[] = $val;
		}
		$_ed_it_code = '$$' . implode('$$,$$', $_ed_it_code) . '$$';
	} else {$_ed_it_code = '$$$$';}

	if(isset($_POST['_ed_it_location'])) {
		foreach($_POST['_ed_it_location'] as $val) {
			$_ed_it_location[] = $val;
		}
		$_ed_it_location = implode(',', $_ed_it_location);
	} else {$_ed_it_location = '0';}

	if(isset($_POST['_ed_it_type'])) {
		foreach($_POST['_ed_it_type'] as $val) {
			$_ed_it_type[] = $val;
		}
		$_ed_it_type = implode(',', $_ed_it_type);
	} else {$_ed_it_type = '0';}

	if(isset($_POST['_ed_it_date'])) {
		foreach($_POST['_ed_it_date'] as $val) {
			$_ed_it_date[] = $val;
		}
		$_ed_it_date = '$$' . implode('$$,$$', $_ed_it_date) . '$$';
	} else {$_ed_it_date = '$$$$';}

	if(isset($_POST['_ed_it_qty'])) {
		foreach($_POST['_ed_it_qty'] as $val) {
			$_ed_it_qty[] 		 = number_format($val,2);
		}
		$_ed_it_qty		= implode(',', $_ed_it_qty);
	} else {$_ed_it_qty	= '0.00';}

	$result = executeSP(
		ZKP_SQL."_insertReturnBorrow",
		"$\$FROM PO$\$",
		"$\${$_doc_number}$\$",
		"$\${$_doc_date}$\$",
		$_type_item,
		$_location,
		"ARRAY[$_box_idx]",
		"ARRAY[$_it_code]",
		"$\${$_confirm_by_account}$\$",
		"ARRAY[$_ed_it_code]",
		"ARRAY[$_ed_it_location]",
		"ARRAY[$_ed_it_type]",
		"ARRAY[$_ed_it_date]",
		"ARRAY[$_ed_it_qty]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, "input_move_type_po.php?".getQueryString());
	}
	$M->goPage("input_move_type_po.php?".getQueryString());
}
?>