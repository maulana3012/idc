<?php
//CONFIRM INCOMING PL =================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'confirm_PL')) {

	$_pl_idx		= $_POST['_code'];
	$_sp_code		= strtoupper($_POST['_sp_code']);
	$_po_code	 	= $_POST['_po_code'];

	$_pl_type		= $_POST['_pl_type'];
	$_pl_inv_no		= $_POST['_pl_invoice_no'];
	$_wh_located 	= $_POST['_warehouse_name'];
	$_arrived_date	= $_POST['_arrived_date'];
	$_checked_by 	= $_POST['_checked_by'];
	$_confirmed_by	= $S->getValue("ma_account");
	$_remark		= $_POST['_remark'];

	//Item Value
	foreach($_POST['_it_code'] as $val)			$_it_code[]		 = $val;
	foreach($_POST['_plit_arrived'] as $val)	$_plit_arrived[] = $val;
	foreach($_POST['_plit_on_deli'] as $val)	$_plit_on_deli[] = $val;

	if(isset($_POST['_ed_it_code'])) {
		foreach($_POST['_ed_it_code'] as $val) {
			$_ed_it_code[]	 = $val;
		}
		$_ed_it_code	= '$$' . implode('$$,$$', $_ed_it_code) . '$$';
	} else {
		$_ed_it_code	= '$$$$';
	}

	if(isset($_POST['_ed_it_qty'])) {
		foreach($_POST['_ed_it_qty'] as $val) {
			$_ed_it_qty[] 		 = $val;
		}
		$_ed_it_qty		= implode(',', $_ed_it_qty);
	} else {
		$_ed_it_qty	= '0';
	}

	if(isset($_POST['_ed_it_date'])) {
		foreach($_POST['_ed_it_date'] as $val) {
			$_ed_it_date[]		 = $val;
		}
		$_ed_it_date		= '$$' . implode('$$,$$', $_ed_it_date) . '$$';
	} else {
		$_ed_it_date	= '$$$$';
	}

	//make pgsql ARRAY String for many item
	$_it_code		= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_plit_arrived	= implode(',', $_plit_arrived);
	$_plit_on_deli	= implode(',', $_plit_on_deli);

	//new Incoming PL to Indocore
	$result = executeSP(
		ZKP_SQL."_newIncomingPL",
		$_pl_idx,
		"$\${$_sp_code}$\$",
		"$\${$_po_code}$\$",
		$_pl_type,
		"$\${$_pl_inv_no}$\$",
		"$\${$_arrived_date}$\$",
		"$\${$_checked_by}$\$",
		"$\${$_confirmed_by}$\$",
		$_wh_located,
		"$\${$_remark}$\$",
		"ARRAY[$_it_code]",
		"ARRAY[$_plit_arrived]",
		"ARRAY[$_plit_on_deli]",
		"ARRAY[$_ed_it_code]",
		"ARRAY[$_ed_it_qty]",
		"ARRAY[$_ed_it_date]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/confirm_pl.php?_code=$_code");
	}
	$_inpl_idx = $result[0];
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_confirm_pl.php?_code=$_code&_inpl_idx=$_inpl_idx");
}

//UPDATE INCOMING PL ==================================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'update_PL')) {

	$_pl_idx			= (int) $_POST['_pl_idx'];
	$_inpl_idx			= (int) $_POST['_inpl_idx'];
	$_inpl_type			= (int) $_POST['_inpl_type'];
	$_wh_location		= (int) $_POST['_wh_location'];
	$_reconfirmed_by	= $S->getValue("ma_account");
	$_remark			= $_POST['_remark'];
	$_invoice_no		= $_POST['_invoice_no'];
	$_invoice_date		= $_POST['_invoice_date'];

	//Item Value
	foreach($_POST['_rcp_idx'] as $val)		$_rcp_idx[]	= $val;
	foreach($_POST['_it_code'] as $val)		$_it_code[]	= $val;
	foreach($_POST['_it_qty'] as $val)		$_it_qty[]	= $val;
	if(isset($_POST['_ed_it_code'])) {
		foreach($_POST['_ed_it_code'] as $val) {
			$_ed_it_code[]	 = $val;
		}
		$_ed_it_code	= '$$' . implode('$$,$$', $_ed_it_code) . '$$';
	} else {
		$_ed_it_code	= '$$$$';
	}

	if(isset($_POST['_ed_it_qty'])) {
		foreach($_POST['_ed_it_qty'] as $val) {
			$_ed_it_qty[] 		 = $val;
		}
		$_ed_it_qty		= implode(',', $_ed_it_qty);
	} else {
		$_ed_it_qty	= '0';
	}

	if(isset($_POST['_ed_it_date'])) {
		foreach($_POST['_ed_it_date'] as $val) {
			$_ed_it_date[]		 = $val;
		}
		$_ed_it_date		= '$$' . implode('$$,$$', $_ed_it_date) . '$$';
	} else {
		$_ed_it_date	= '$$$$';
	}

	//make pgsql ARRAY String for many item
	$_rcp_idx	= implode(',', $_rcp_idx);
	$_it_code	= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_it_qty	= implode(',', $_it_qty);

	//modify incoming PL to indocore
	$result = executeSP(
		ZKP_SQL."_reviseIncomingPL",
		$_pl_idx,
		$_inpl_idx,
		$_wh_location,
		$_inpl_type,
		"$\${$_invoice_no}$\$",
		"$\${$_invoice_date}$\$",
		"$\${$_reconfirmed_by}$\$",
		"$\${$_remark}$\$",
		"ARRAY[$_rcp_idx]",
		"ARRAY[$_it_code]",
		"ARRAY[$_it_qty]",
		"ARRAY[$_ed_it_code]",
		"ARRAY[$_ed_it_qty]",
		"ARRAY[$_ed_it_date]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/revise_confirm_pl.php?_code=$_code&_inpl_idx=$_inpl_idx");
	}
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_confirm_pl.php?_code=$_code&_inpl_idx=$_inpl_idx");
}

//DELETE INCOMING PL ==================================================================================================
if (ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'delete_PL')) {

	$_inpl_idx		= $_POST['_inpl_idx'];
	$_deleted_by	= $S->getValue("ma_account");

	$result = executeSP(
		ZKP_SQL."_deleteIncomingPL",
		$_inpl_idx,
		"$\${$_deleted_by}$\$"
	);

	if (isZKError($result))
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/revise_confirm_pl.php?_code=$_code&_inpl_idx=$_inpl_idx");
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/summary_arrival_by_supplier.php");
}

//MODIFY PL REQUEST ===================================================================================================
if (ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'modify_pl')) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_confirm_pl.php?_code=$_code&_inpl_idx=$_inpl_idx");
}

//CONFIRM INCOMING PL LOCAL ===========================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'confirm_PL_local')) {

	$_sp_code		= strtoupper($_POST['_sp_code']);
	$_po_code	 	= $_POST['_po_code'];
	$_pl_no			= $_POST['_pl_no'];
	$_pl_type		= $_POST['_pl_type'];
	$_wh_located 	= $_POST['_warehouse_name'];
	$_arrived_date	= $_POST['_arrived_date'];
	$_checked_by 	= $_POST['_checked_by'];
	$_confirmed_by	= $S->getValue("ma_account");
	$_remark		= $_POST['_remark'];

	//Item Value
	foreach($_POST['_it_code'] as $val)			$_it_code[]		 = $val;
	foreach($_POST['_plit_arrived'] as $val)	$_plit_arrived[] = $val;

	//make pgsql ARRAY String for many item
	$_it_code		= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_plit_arrived	= implode(',', $_plit_arrived);

	if(isset($_POST['_ed_it_code'])) {
		foreach($_POST['_ed_it_code'] as $val) {
			$_ed_it_code[]	 = $val;
		}
		$_ed_it_code	= '$$' . implode('$$,$$', $_ed_it_code) . '$$';
	} else {
		$_ed_it_code	= '$$$$';
	}

	if(isset($_POST['_ed_it_qty'])) {
		foreach($_POST['_ed_it_qty'] as $val) {
			$_ed_it_qty[] 		 = $val;
		}
		$_ed_it_qty		= implode(',', $_ed_it_qty);
	} else {
		$_ed_it_qty	= '0';
	}

	if(isset($_POST['_ed_it_date'])) {
		foreach($_POST['_ed_it_date'] as $val) {
			$_ed_it_date[]		 = $val;
		}
		$_ed_it_date		= '$$' . implode('$$,$$', $_ed_it_date) . '$$';
	} else {
		$_ed_it_date	= '$$$$';
	}

	//new Incoming PL to Indocore
	$result = executeSP(
		ZKP_SQL."_newIncomingPLLocal",
		"$\${$_sp_code}$\$",
		"$\${$_po_code}$\$",
		$_pl_no,
		$_pl_type,
		$_wh_located,
		"$\${$_arrived_date}$\$",
		"$\${$_checked_by}$\$",
		"$\${$_confirmed_by}$\$",
		"$\${$_remark}$\$",
		"ARRAY[$_it_code]",
		"ARRAY[$_plit_arrived]",
		"ARRAY[$_ed_it_code]",
		"ARRAY[$_ed_it_qty]",
		"ARRAY[$_ed_it_date]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/confirm_pl_local.php?_code=$_code&_pl_no=$_pl_no");
	}
	$_inlc_idx = $result[0];
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_confirm_pl_local.php?_code=$_inlc_idx");
}

//UPDATE INCOMING PL LOCAL ============================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'update_PL_local')) {

	$_inlc_idx		= $_POST['_inlc_idx'];
	$_po_code	 	= $_POST['_po_code'];
	$_pl_no			= $_POST['_pl_no'];
	$_invoice_no	= $_POST['_po_code'].' #'. $_POST['_pl_no'];
	$_pl_type		= $_POST['_inlc_type'];
	$_wh_location 	= $_POST['_wh_location'];
	$_arrived_date	= $_POST['_arrival_date'];
	$_confirmed_by	= $S->getValue("ma_account");
	$_remark		= $_POST['_remark'];

	//Item Value
	foreach($_POST['_it_code'] as $val)			$_it_code[]	= $val;
	foreach($_POST['_it_qty'] as $val)			$_it_qty[] 	= $val;

	if(isset($_POST['_ed_it_code'])) {
		foreach($_POST['_ed_it_code'] as $val) {
			$_ed_it_code[]	 = $val;
		}
		$_ed_it_code	= '$$' . implode('$$,$$', $_ed_it_code) . '$$';
	} else {
		$_ed_it_code	= '$$$$';
	}

	if(isset($_POST['_ed_it_qty'])) {
		foreach($_POST['_ed_it_qty'] as $val) {
			$_ed_it_qty[] 		 = $val;
		}
		$_ed_it_qty		= implode(',', $_ed_it_qty);
	} else {
		$_ed_it_qty	= '0';
	}

	if(isset($_POST['_ed_it_date'])) {
		foreach($_POST['_ed_it_date'] as $val) {
			$_ed_it_date[]		 = $val;
		}
		$_ed_it_date		= '$$' . implode('$$,$$', $_ed_it_date) . '$$';
	} else {
		$_ed_it_date	= '$$$$';
	}

	//make pgsql ARRAY String for many item
	$_it_code	= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_it_qty	= implode(',', $_it_qty);

	//modify incoming PL to indocore
	$result = executeSP(
		ZKP_SQL."_reviseIncomingPLLocal",
		$_inlc_idx,
		"$\${$_po_code}$\$",
		$_pl_no,
		"$\${$_invoice_no}$\$",
		$_pl_type,
		$_wh_location,
		"$\${$_arrived_date}$\$",
		"$\${$_confirmed_by}$\$",
		"$\${$_remark}$\$",
		"ARRAY[$_it_code]",
		"ARRAY[$_it_qty]",
		"ARRAY[$_ed_it_code]",
		"ARRAY[$_ed_it_qty]",
		"ARRAY[$_ed_it_date]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/revise_confirm_pl_local.php?_inlc_idx=$_inlc_idx");
	}
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_confirm_pl_local.php?_inlc_idx=$_inlc_idx");
}

//DELETE INCOMING PL LOCAL ============================================================================================
if (ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'delete_PL_local')) {

	$_inlc_idx		= $_POST['_inlc_idx'];
	$_deleted_by	= $S->getValue("ma_account");

	$result = executeSP(
		ZKP_SQL."_deleteIncomingPLLocal",
		$_inlc_idx,
		"$\${$_deleted_by}$\$"
	);

	if (isZKError($result))
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/revise_confirm_pl_local.php?_inlc_idx=$_inlc_idx");
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/summary_arrival_by_supplier.php");
}

//MODIFY PL LOCAL REQUEST =============================================================================================
if (ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'modify_pl_local')) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_confirm_pl_local.php?_inlc_idx=$_inlc_idx");
}

//CONFIRM INCOMING CLAIM ==============================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'confirm_claim')) {

	$_cl_idx		= $_POST['_code'];
	$_sp_code		= strtoupper($_POST['_sp_code']);
	$_pl_type		= $_POST['_pl_type'];
	$_invoice_no	= $_POST['_invoice_no'];
	$_wh_located 	= $_POST['_warehouse_name'];
	$_arrived_date	= $_POST['_arrived_date'];
	$_checked_by 	= $_POST['_checked_by'];
	$_confirmed_by	= $S->getValue("ma_account");
	$_remark		= $_POST['_remark'];

	//Item Value
	foreach($_POST['_it_code'] as $val)			$_it_code[]		 = $val;
	foreach($_POST['_plit_arrived'] as $val)	$_plit_arrived[] = $val;
	foreach($_POST['_plit_on_deli'] as $val)	$_plit_on_deli[] = $val;

	//make pgsql ARRAY String for many item
	$_it_code		= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_plit_arrived	= implode(',', $_plit_arrived);
	$_plit_on_deli	= implode(',', $_plit_on_deli);

	if(isset($_POST['_ed_it_code'])) {
		foreach($_POST['_ed_it_code'] as $val) {
			$_ed_it_code[]	 = $val;
		}
		$_ed_it_code	= '$$' . implode('$$,$$', $_ed_it_code) . '$$';
	} else {
		$_ed_it_code	= '$$$$';
	}

	if(isset($_POST['_ed_it_qty'])) {
		foreach($_POST['_ed_it_qty'] as $val) {
			$_ed_it_qty[] 		 = $val;
		}
		$_ed_it_qty		= implode(',', $_ed_it_qty);
	} else {
		$_ed_it_qty	= '0';
	}

	if(isset($_POST['_ed_it_date'])) {
		foreach($_POST['_ed_it_date'] as $val) {
			$_ed_it_date[]		 = $val;
		}
		$_ed_it_date		= '$$' . implode('$$,$$', $_ed_it_date) . '$$';
	} else {
		$_ed_it_date	= '$$$$';
	}

	//new Incoming PL to Indocore
	$result = executeSP(
		ZKP_SQL."_newIncomingPLClaim",
		$_cl_idx,
		"$\${$_sp_code}$\$",
		$_pl_type,
		"$\${$_invoice_no}$\$",
		"$\${$_arrived_date}$\$",
		"$\${$_checked_by}$\$",
		"$\${$_confirmed_by}$\$",
		$_wh_located,
		"$\${$_remark}$\$",
		"ARRAY[$_it_code]",
		"ARRAY[$_plit_arrived]",
		"ARRAY[$_plit_on_deli]",
		"ARRAY[$_ed_it_code]",
		"ARRAY[$_ed_it_qty]",
		"ARRAY[$_ed_it_date]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/confirm_claim.php?_code=$_code");
	}
	$_incl_idx = $result[0];
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_confirm_claim.php?_code=$_code&_incl_idx=$_incl_idx");
}

//UPDATE INCOMING CLAIM ===============================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'update_claim')) {

	$_cl_idx			= (int) $_POST['_cl_idx'];
	$_incl_idx			= (int) $_POST['_incl_idx'];
	$_inpl_type			= (int) $_POST['_inpl_type'];
	$_wh_location		= (int) $_POST['_wh_location'];
	$_reconfirmed_by	= $S->getValue("ma_account");
	$_remark			= $_POST['_remark'];
	$_invoice_no		= $_POST['_invoice_no'];
	$_invoice_date		= $_POST['_invoice_date'];

	//Item Value
	foreach($_POST['_it_code'] as $val)		$_it_code[]	= $val;
	foreach($_POST['_it_qty'] as $val)		$_it_qty[]	= $val;
	if(isset($_POST['_ed_it_code'])) {
		foreach($_POST['_ed_it_code'] as $val) {
			$_ed_it_code[]	 = $val;
		}
		$_ed_it_code	= '$$' . implode('$$,$$', $_ed_it_code) . '$$';
	} else {
		$_ed_it_code	= '$$$$';
	}

	if(isset($_POST['_ed_it_qty'])) {
		foreach($_POST['_ed_it_qty'] as $val) {
			$_ed_it_qty[] 		 = $val;
		}
		$_ed_it_qty		= implode(',', $_ed_it_qty);
	} else {
		$_ed_it_qty	= '0';
	}

	if(isset($_POST['_ed_it_date'])) {
		foreach($_POST['_ed_it_date'] as $val) {
			$_ed_it_date[]		 = $val;
		}
		$_ed_it_date		= '$$' . implode('$$,$$', $_ed_it_date) . '$$';
	} else {
		$_ed_it_date	= '$$$$';
	}

	//make pgsql ARRAY String for many item
	$_it_code	= '$$' . implode('$$,$$', $_it_code) . '$$';
	$_it_qty	= implode(',', $_it_qty);

	//modify incoming PL Claim to indocore
	$result = executeSP(
		ZKP_SQL."_reviseIncomingPLClaim",
		$_cl_idx,
		$_incl_idx,
		$_wh_location,
		$_inpl_type,
		"$\${$_invoice_no}$\$",
		"$\${$_invoice_date}$\$",
		"$\${$_reconfirmed_by}$\$",
		"$\${$_remark}$\$",
		"ARRAY[$_it_code]",
		"ARRAY[$_it_qty]",
		"ARRAY[$_ed_it_code]",
		"ARRAY[$_ed_it_qty]",
		"ARRAY[$_ed_it_date]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/revise_confirm_claim.php?_code=$_code&_incl_idx=$_incl_idx");
	}
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_confirm_claim.php?_code=$_code&_incl_idx=$_incl_idx");
}

//DELETE INCOMING CLAIM ===============================================================================================
if (ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'delete_claim')) {

	$_incl_idx		= $_POST['_incl_idx'];
	$_deleted_by	= $S->getValue("ma_account");

	$result = executeSP(
		ZKP_SQL."_deleteIncomingPLClaim",
		$_incl_idx,
		"$\${$_deleted_by}$\$"
	);

	if (isZKError($result))
		$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/revise_confirm_claim.php?_code=$_code&_incl_idx=$_incl_idx");
	$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/summary_arrival_by_supplier.php");
}

//MODIFY CLAIM REQUEST ================================================================================================
if (ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php", 'modify_claim')) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_confirm_claim.php?_code=$_code&_incl_idx=$_incl_idx");
}
?>