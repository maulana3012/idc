<?php
//INSERT FAKTUR PAJAK NUMBER ================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "list_available_number.php", 'insert_number')) {
    $_ordered_by = $cboFilter[1][ZKP_FUNCTION][0][0];
    $_year = $_POST['_year'];
    $_digit = $_POST['_digit'];
    $_from = $_POST['_from'];
    $_to = $_POST['_to'];

    $result = executeSP(ZKP_SQL."_insertFakturNo", $_ordered_by, "$\${$_year}$\$", "$\${$_digit}$\$", $_from, $_to);
    if (isZKError($result)) $M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");
    goPage(HTTP_DIR . "$currentDept/$moduleDept/list_available_number.php");
}

//UPDATE FAKTUR PAJAK NUMBER ================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "p_list_faktur.php?_idx=$_idx&_type=edit", 'update_number')) {
    $_idx = $_POST['_idx'];
    $_year = $_POST['_year'];
    $_digit = $_POST['_digit'];
    $_from = $_POST['_from'];
    $_to = $_POST['_to'];

    $result = executeSP(ZKP_SQL."_updateFakturNo", $_idx, "$\${$_year}$\$", "$\${$_digit}$\$", $_from, $_to);
    if (isZKError($result)) $M->goErrorPage($result, "p_list_faktur.php?_idx=$_idx&_type=edit");
    goPage("p_list_faktur.php?_idx=$_idx&_type=edit");
}

//DELETE FAKTUR PAJAK NUMBER ================================================================================
if(ckperm(ZKP_DELETE, HTTP_DIR . "list_available_number.php", 'delete_number')) {
    $_idx = $_POST['_idx'];
    $result = query("DELETE FROM ".ZKP_SQL."_tb_faktur_pajak WHERE fk_idx = $_idx");
    if (isZKError($result)) $M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");
    goPage(HTTP_DIR . "$currentDept/$moduleDept/list_available_number.php");
}

//CONFIRM FAKTUR PAJAK ======================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "list_available_number.php", 'confirm_date')) {
    $_date = $_POST['_date'];
    $result = query("UPDATE ".ZKP_SQL."_tb_setup SET st_date = '$_date' WHERE st_desc='confirm_pajak'");
    if (isZKError($result)) $M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");
    goPage(HTTP_DIR . "$currentDept/$moduleDept/list_available_number.php");
}

//UPDATE RETURN =============================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "list_returnable_invoice.php", 'update_return')) {
    $bill_code = $_POST['_bill_code'];
        
    $sql = "UPDATE ".ZKP_SQL."_tb_billing SET bill_is_returnable = TRUE WHERE bill_code = '$bill_code'";

    if(isZKError($result =& query($sql))) {
        $M->goErrorPage($result, "list_returnable_invoice.php");
    }

    $M->goPage("list_returnable_invoice.php");
}

//UPDATE RETURN OPPOSITE ====================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR . "list_returnable_invoice.php", 'update_return_opposite')) {
    $bill_code = $_POST['_bill_code'];

    $sql = "UPDATE ".ZKP_SQL."_tb_billing SET bill_is_returnable = FALSE WHERE bill_code = '$bill_code'";

    if(isZKError($result =& query($sql))) {
        $M->goErrorPage($result, "list_returnable_invoice.php");
    }
    $M->goPage("list_returnable_invoice.php");
}

//SEND MAIL =================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "list_fp_sent.php", 'send_mail')) {

    $_email_customer = $_POST['_customer_email'];
    $_location = $_POST['_location'];
    $_file_type = $_POST['_file_type'];
    $_source = isset($_POST['_source']) ? $_POST['_source'] : "page";

    if ($_email_customer == "") {
        $result = new ZKError(
            "CUSTOMER_EMAIL_NOT_EXIST",
            "CUSTOMER_EMAIL_NOT_EXIST",
            "Customer FP email doesn't not exist. Please make sure input the email first.");
        $M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/".$_location);
    }

    $_bill_code = explode(',', $_POST['_bill_code']);
    if (sizeof($_bill_code) > 1) {
        $_bill_code = "'" . implode("', '", $_bill_code) . "'";
    } else {
        $_bill_code = "'" . implode("', '", $_bill_code) . "'";
    }
/*
echo "<pre>";
var_dump($_bill_code);
exit;    
*/
    if ($_file_type == "FP") {
        $sql = "UPDATE ".ZKP_SQL."_tb_billing SET bill_is_fp_delivery = TRUE WHERE bill_code IN ($_bill_code)";
    } else {
        $sql = "UPDATE ".ZKP_SQL."_tb_billing SET bill_is_fpp_delivery = TRUE WHERE bill_code IN ($_bill_code)";
    }

    if(isZKError($result =& query($sql))) {
        $M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/list_fp_sent.php?".$_location);
    }

    // PROCESS SEND MAIL
    include APP_DIR . "_system/email/report_faktur.php";

    if($_source == "page") {
        $M->goPage($_location);
    } else {
        die("<script language=\"javascript1.2\">window.opener.location.reload();window.close();</script>");
    }
}


