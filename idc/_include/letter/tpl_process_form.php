<?php
//INSERT PROCESS ============================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "letter/{$moduleDept}/input_letter.php", "insert")) {

	$_cus_code			= $_POST['_cus_code'];
	$_cus_attn			= $_POST['_cus_attn'];
	$_stamp_pcs			= ($_POST['_stamp_pcs'] == "") ? 0 : $_POST['_stamp_pcs'];
	$_dept				= $_POST['_dept'];
	$_reg_amount		= $_POST['totalFee'];
	$_reg_date			= $_POST['_reg_date'];
	$_reg_issued_by		= $_POST['_reg_issued_by'];
	$_reg_type			= $_POST['cboRegType'];
	$_reg_send_to		= $_POST['_reg_send_to'];
	$_reg_pic			= $_POST['_reg_pic'];
	$_reg_item			= $_POST['_reg_item'];
	$_reg_address		= $_POST['_reg_address'];
	$_reg_remark		= $_POST['_reg_remark'];	
	$_reg_brief_summary	= $_POST['_reg_brief_summary'];
	$_reg_status		= $_POST['cboRegStatus'];	
	$_reg_confirmed_date = isset($_POST['_reg_confirmed_date']) ? $_POST['_reg_confirmed_date'] : "";
	$_lastupdated_by_account = ucfirst($S->getValue("ma_account"));
	$_rev_no			= 0;

	$sql = "SELECT ".ZKP_SQL."_getCurrentLetterNo($\$".ZKP_SQL."$\$,'".$_dept."', '$_reg_date','$_reg_type')";
	$res =& query($sql);
	$col = fetchRow($res);
	$_code = str_replace("/", "-", $col[0]);

	$storage = PDF_STORAGE_LETTER . "/$moduleDept/". date("Ym/", strtotime($_reg_date));
	(!is_dir($storage)) ? mkdir($storage, 0777, true) : 0;
	$type_file = array("application/pdf"=>"pdf", 
						"application/msword"=>"word","application/vnd.openxmlformats-officedocument.wordprocessingml.document"=>"word",
						"application/vnd.ms-excel"=>"excel", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"=>"excel",
				);

	// Copy of Letter
	for($i=0; $i<count($_FILES["_reg_letter"]["name"]); $i++) {
		$file_name = $_code . '_A_' . time() . "_" . $_FILES["_reg_letter"]["name"][$i];
		if(move_uploaded_file($_FILES["_reg_letter"]["tmp_name"][$i], $storage . $file_name)){
			$_reg_file_part[] = "A";
			$_reg_file_name[] = $_FILES["_reg_letter"]["name"][$i];
			$_reg_file_path[] = strstr($storage . $file_name, "/$moduleDept");
			$_reg_file_type[] = $type_file[$_FILES["_reg_letter"]["type"][$i]];
		}
		$_reg_file_desc[] = "";
	}

	// Attachment
	for($i=0; $i<count($_FILES["_reg_attachment"]["name"]); $i++) {
		$file_name = $_code . '_B_' . time() . "_" . $_FILES["_reg_attachment"]["name"][$i];
		if(move_uploaded_file($_FILES["_reg_attachment"]["tmp_name"][$i], $storage . $file_name)){
			$_reg_file_part[] = "B";
			$_reg_file_name[] = $_FILES["_reg_attachment"]["name"][$i];
			$_reg_file_path[] = strstr($storage . $file_name, "/$moduleDept");
			$_reg_file_type[] = $type_file[$_FILES["_reg_attachment"]["type"][$i]];
		}
		$_reg_file_desc[] = $_POST["_reg_desc_attachment"][$i];
	}

	if (empty($_reg_file_part)) {
		$_reg_file_part		= '$$$$';
		$_reg_file_name		= '$$$$';
		$_reg_file_path 	= '$$$$';
		$_reg_file_type 	= '$$$$';
		$_reg_file_desc 	= '$$$$';
	} else {
		$_reg_file_part		= '$$' . implode('$$,$$', $_reg_file_part) . '$$';
		$_reg_file_name		= '$$' . implode('$$,$$', $_reg_file_name) . '$$';
		$_reg_file_path 	= '$$' . implode('$$,$$', $_reg_file_path) . '$$';
		$_reg_file_type 	= '$$' . implode('$$,$$', $_reg_file_type) . '$$';
		$_reg_file_desc 	= '$$' . implode('$$,$$', $_reg_file_desc) . '$$';	
	}

	if(isset($_POST['_fee_desc'])) {
		foreach($_POST['_fee_desc'] as $val)	$_fee_desc[]	= $val;
		foreach($_POST['_fee_amount'] as $val)	$_fee_amount[]	= $val;
		$_fee_desc	 = '$$' . implode('$$,$$', $_fee_desc) . '$$';
		$_fee_amount = implode(',', $_fee_amount);
	} else {
		$_fee_desc	 = '$$$$';
		$_fee_amount = 0;
	}


	$result = executeSP(
		ZKP_SQL."_insertRegLetter",
		"$\$".ZKP_SQL."$\$",
		"$\${$_dept}$\$",
		"$\${$_cus_code}$\$",
		"$\${$_cus_attn}$\$",
		$_reg_amount,
		$_stamp_pcs,
		"$\${$_reg_date}$\$",
		"$\${$_reg_issued_by}$\$",
		"$\${$_reg_type}$\$",
		"$\${$_reg_send_to}$\$",
		"$\${$_reg_pic}$\$",
		"$\${$_reg_item}$\$",
		"$\${$_reg_address}$\$",
		"$\${$_reg_remark}$\$",
		"$\${$_reg_brief_summary}$\$",
		"$\${$_reg_status}$\$",
		"$\${$_reg_confirmed_date}$\$",
		"$\${$_lastupdated_by_account}$\$",
		"ARRAY[$_reg_file_part]",
		"ARRAY[$_reg_file_name]",
		"ARRAY[$_reg_file_path]",
		"ARRAY[$_reg_file_type]",
		"ARRAY[$_reg_file_desc]",
		"ARRAY[$_fee_desc]",
		"ARRAY[$_fee_amount]"
	);

	if (isZKError($result)) {
		$errMessage = $result->getMessage();
		if(strpos($errMessage, "duplicate key violates")) {
			$result = new ZKError(
				"DUPLICATE_CODE_EXIST",
				"DUPLICATE_CODE_EXIST",
				"The code : <strong>$_code</strong> already exist. please, use different code");
		}
		$M->goErrorPage($result, "input_letter.php");
	}

	$_code = $result[0];
	include APP_DIR . "_include/letter/pdf/generate_receipt.php";
	$M->goPage("list_letter.php");

}

//UPDATE PROCESS ============================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR."letter/{$moduleDept}/revise_letter.php?_code=$_code", "update")) {

	$_code = str_replace("/", "-", $_POST['_code']);
	$_cus_code			= $_POST['_cus_code'];
	$_cus_name			= $_POST['_cus_name'];
	$_cus_attn			= $_POST['_cus_attn'];	
	$_stamp_pcs			= $_POST['_stamp_pcs'];
	$_dept				= $_POST['_dept'];
	$_reg_amount		= $_POST['totalFee'];
	$_reg_date			= $_POST['_reg_date'];
	$_reg_issued_by		= $_POST['_reg_issued_by'];
	$_reg_send_to		= $_POST['_reg_send_to'];
	$_reg_pic			= $_POST['_reg_pic'];
	$_reg_item			= $_POST['_reg_item'];
	$_reg_address		= $_POST['_reg_address'];
	$_reg_remark		= $_POST['_reg_remark'];
	$_reg_brief_summary	= $_POST['_reg_brief_summary'];
	$_reg_status		= $_POST['cboRegStatus'];
	$_reg_confirmed_date	 = isset($_POST['_reg_confirmed_date']) ? $_POST['_reg_confirmed_date'] : "";
	$_reg_cancelled_reason	 = isset($_POST['_reg_cancelled_reason']) ? $_POST['_reg_cancelled_reason'] : "";
	$_lastupdated_by_account = ucfirst($S->getValue("ma_account"));
	$_rev_no			= $_POST['_rev_no']+1;

	$storage = PDF_STORAGE_LETTER . "/$moduleDept/". date("Ym/", strtotime($_reg_date));
	(!is_dir($storage)) ? mkdir($storage, 0777, true) : 0;
	$type_file = array("application/pdf"=>"pdf", 
						"application/msword"=>"word","application/vnd.openxmlformats-officedocument.wordprocessingml.document"=>"word",
						"application/vnd.ms-excel"=>"excel", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"=>"excel",
				);

	// Copy of Letter
	for($i=0; $i<count($_FILES["_reg_letter"]["name"]); $i++) {
		$file_name = $_code . '_A_' . time() . "_" . $_FILES["_reg_letter"]["name"][$i];
		if(move_uploaded_file($_FILES["_reg_letter"]["tmp_name"][$i], $storage . $file_name)){
			$_reg_file_part[] = "A";
			$_reg_file_name[] = $_FILES["_reg_letter"]["name"][$i];
			$_reg_file_path[] = strstr($storage . $file_name, "/$moduleDept");
			$_reg_file_type[] = $type_file[$_FILES["_reg_letter"]["type"][$i]];
		}
		$_reg_file_desc[] = "";
	}

	// Attachment
	for($i=0; $i<count($_FILES["_reg_attachment"]["name"]); $i++) {
		$file_name = $_code . '_B_' . time() . "_" . $_FILES["_reg_attachment"]["name"][$i];
		if(move_uploaded_file($_FILES["_reg_attachment"]["tmp_name"][$i], $storage . $file_name)){
			$_reg_file_part[] = "B";
			$_reg_file_name[] = $_FILES["_reg_attachment"]["name"][$i];
			$_reg_file_path[] = strstr($storage . $file_name, "/$moduleDept");
			$_reg_file_type[] = $type_file[$_FILES["_reg_attachment"]["type"][$i]];
		}
		$_reg_file_desc[] = $_POST["_reg_desc_attachment"][$i];
	}

	if (empty($_reg_file_part)) {
		$_reg_file_part		= '$$$$';
		$_reg_file_name		= '$$$$';
		$_reg_file_path 	= '$$$$';
		$_reg_file_type 	= '$$$$';
		$_reg_file_desc 	= '$$$$';
	} else {
		$_reg_file_part		= '$$' . implode('$$,$$', $_reg_file_part) . '$$';
		$_reg_file_name		= '$$' . implode('$$,$$', $_reg_file_name) . '$$';
		$_reg_file_path 	= '$$' . implode('$$,$$', $_reg_file_path) . '$$';
		$_reg_file_type 	= '$$' . implode('$$,$$', $_reg_file_type) . '$$';
		$_reg_file_desc 	= '$$' . implode('$$,$$', $_reg_file_desc) . '$$';	
	}
	
	$_code = str_replace("-", "/", $_POST['_code']);

	if(isset($_POST['_fee_desc'])) {
		foreach($_POST['_fee_desc'] as $val)	$_fee_desc[]	= $val;
		foreach($_POST['_fee_amount'] as $val)	$_fee_amount[]	= $val;
		$_fee_desc	 = '$$' . implode('$$,$$', $_fee_desc) . '$$';
		$_fee_amount = implode(',', $_fee_amount);
	} else {
		$_fee_desc	 = '$$$$';
		$_fee_amount = 0;
	}


	$result = executeSP(
		ZKP_SQL."_updateRegLetter",
		"$\${$_code}$\$",
		"$\${$_dept}$\$",
		"$\${$_cus_code}$\$",
		"$\${$_cus_attn}$\$",
		$_reg_amount,
		$_stamp_pcs,
		"$\${$_reg_date}$\$",
		"$\${$_reg_issued_by}$\$",
		"$\${$_reg_send_to}$\$",
		"$\${$_reg_pic}$\$",
		"$\${$_reg_item}$\$",
		"$\${$_reg_address}$\$",
		"$\${$_reg_remark}$\$",
		"$\${$_reg_brief_summary}$\$",
		"$\${$_reg_status}$\$",
		"$\${$_reg_confirmed_date}$\$",
		"$\${$_reg_cancelled_reason}$\$",
		"$\${$_lastupdated_by_account}$\$",
		$_rev_no,
		"ARRAY[$_reg_file_part]",
		"ARRAY[$_reg_file_name]",
		"ARRAY[$_reg_file_path]",
		"ARRAY[$_reg_file_type]",
		"ARRAY[$_reg_file_desc]",
		"ARRAY[$_fee_desc]",
		"ARRAY[$_fee_amount]"
	);

	if (isZKError($result)) {
		$M->goErrorPage($result, "input_letter.php");
	}

	include APP_DIR . "_include/letter/pdf/generate_receipt.php";
	$M->goPage("revise_letter.php?_code=$_code");

}

//DELETE PROCESS ============================================================================================
if(ckperm(ZKP_DELETE, HTTP_DIR."letter/{$moduleDept}/revise_letter.php?_code=$_code", "delete")) {

	$_code = $_POST['_code'];

	$file_sql = "SELECT * FROM ".ZKP_SQL."_tb_letter_file WHERE lt_reg_no = '$_code' ORDER BY ltf_file_name";
	$file_res =& query($file_sql);
	while($rows =& fetchRowAssoc($file_res)) {
		$file_name = PDF_STORAGE_LETTER.$rows["ltf_file_path"];
		@unlink($file_name);
	}

	$sql = "DELETE FROM ".ZKP_SQL."_tb_letter WHERE lt_reg_no='$_code'";

	if(isZKError($result = query($sql))) {
		$M->goErrorPage($result, "revise_letter.php?_code=$_code");
	}
	$M->goPage("list_letter.php");

}

//CANCEL PROCESS ============================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR."letter/{$moduleDept}/revise_letter.php?_code=$_code", "cancel")) {

	$_code = $_POST['_code'];
	$_reason = $_POST['_reason'];
	$_by_account = $_POST['_by_account'];

	$sql = "UPDATE ".ZKP_SQL."_tb_letter SET
			  lt_status_of_letter = '3',
			  lt_cancelled_reason = '$_reason',
			  lt_cancelled_by_account = '$_by_account',
			  lt_cancelled_timestamp = current_timestamp
			WHERE lt_reg_no='$_code'";
	if(isZKError($result = query($sql))) $M->goErrorPage($result, "revise_letter.php?_code=$_code");
	$M->goPage("revise_letter.php?_code=$_code");

}

//DELETE FILE ===============================================================================================
if(ckperm(ZKP_DELETE, HTTP_DIR."letter/{$moduleDept}/revise_letter.php?_code=$_code", "deleteFile")) {

	$_idx	= $_POST['_del_file_idx'];
	$file_name = PDF_STORAGE_LETTER.$_POST['_del_file_path'];
	@unlink($file_name);
	$sql = "DELETE FROM ".ZKP_SQL."_tb_letter_file WHERE ltf_idx=$_idx";
	if(isZKError($result = query($sql))) $M->goErrorPage($result, HTTP_DIR."/letter/{$moduleDept}/revise_letter.php?_code=$_code");
	$M->goPage("revise_letter.php?_code=$_code");
} 

//CONFIRM STAMP ============================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR."letter/{$moduleDept}/revise_letter.php?_code=$_code", "confirm_stamp")) {

	$_code = $_POST['_code'];

	$sql = "UPDATE ".ZKP_SQL."_tb_letter SET
			  lt_stamp_confirm = current_timestamp,
			  lt_stamp_confirmby = '".ucfirst($S->getValue("ma_account"))."'
			WHERE lt_reg_no='$_code'";
	if(isZKError($result = query($sql))) $M->goErrorPage($result, "revise_letter.php?_code=$_code");
	$M->goPage("revise_letter.php?_code=$_code");
}

//CONFIRM PAYMENT ============================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR."letter/{$moduleDept}/revise_letter.php?_code=$_code", "confirm_payment")) {

	$_code = $_POST['_code'];

	$sql = "UPDATE ".ZKP_SQL."_tb_letter SET
			  lt_amount_confirm = current_timestamp,
			  lt_amount_confirmby = '".ucfirst($S->getValue("ma_account"))."'
			WHERE lt_reg_no='$_code'";
	if(isZKError($result = query($sql))) $M->goErrorPage($result, "revise_letter.php?_code=$_code");
	$M->goPage("revise_letter.php?_code=$_code");
}

?>