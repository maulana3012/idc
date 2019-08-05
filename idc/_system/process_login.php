<?php
/**
* Copyright(c) 2005 PT. ZONEKOM All right reserved
* Homepage http://www.zonekom.com
* Contact us dskim@indosat.net.id
*
* author : dskim
*
* $Id: process_login_gmis.php,v 1.1 2008/04/28 06:52:13 neki Exp $
*/
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//login Process
$account	= $_POST['userid'];
$password	= md5($_POST['userpw']);
$returnUrl	= $_POST['returnUrl'];

//login is fail
$result = executeSP("loginmember", "$\${$account}$\$", "$\${$password}$\$");
if(isZKError($result)) {

	$errMessage = $result->getMessage();

	if (strpos($errMessage, "ACCOUNT_IS_BLOCKED") !== FALSE) {
		$o = new ZKError(
			"ACCOUNT_IS_BLOCKED",
			"ACCOUNT_IS_BLOCKED",
			"Account is blocked. Please contact administrator to re-issue a temporary password");
			$afterClick = MAIN_PAGE;
			
	} elseif(strpos($errMessage, "LOGIN_FAIL_TIME") !== FALSE) {
		$failTime = substr($errMessage, -2, 2);
		$o = new ZKError(
			"LOGIN_FAIL",
			"LOGIN_FAIL",
			"Wrong password. System may block your account after 3 times wrong password.<br/><br/> Login Fail time : $failTime");

		$afterClick = LOGIN_PAGE . "?returnUrl=" . urlencode($returnUrl);

	} elseif (strpos($errMessage, "ERROR") !== FALSE) {
		$errNo = substr($errMessage, -2, 1);
//echo $errNo;
		switch ($errNo) {

			case "0": //Error No @ stored procedure
				$o = new ZKError(
					"LICENSE_EXPIRED",
					"LICENSE EXPIRED", 
					"License time already expired. please contact to ZONEKOM.</br>
					TEL : 021 5579 0980</br>
					HP : 0815 913 1624<br/>
					E-MAIL : dskim@zonekom.com");
				$afterClick = MAIN_PAGE;
				break;

			case "1":
				$o = new ZKError (
					"ACCOUNT_NOT_FOUND",
					"ACCOUNT_NOT_FOUND",
					"Account not found.Try again.");
				$afterClick = LOGIN_PAGE . "?returnUrl=" . urlencode($returnUrl);
				break;

			case "2":
				$o = new ZKError (
					"ACCOUNT_NOT_AVAILABLE",
					"ACCOUNT_NOT_AVAILABLE",
					"Account is blocked. Please contact administrator to re-issue a temporary password.");
				$afterClick = MAIN_PAGE;
				break;

			case "3":
				$o = new ZKError (
					"NEED_TO_ENROLL",
					"Password registration Information",
					"Please register a new password to active your account");
				$result =& query("SELECT ma_idx FROM tb_mbracc WHERE ma_account = '$account'");
				$data = fetchRow($result);
				$afterClick = HTTP_DIR . "admin/user/change_pw.php?ma_idx=" . $data[0];

			break;

			case "4":
				$o = new ZKError (
					"PASSWORD_ALREADY_EXPIRED",
					"PASSWORD_ALREADY_EXPIRED",
					"Password has expired. Please contact to administrator.");
				$result =& query("SELECT ma_idx FROM tb_mbracc WHERE ma_account = '$account'");
				$data = fetchRow($result);
				$afterClick = MAIN_PAGE;
			break;

			default :
				$o =& $result;
				$afterClick = MAIN_PAGE;
		}
	}

	$M->goErrorPage($o, $afterClick);

} else {

	$ma_idx = $result[0];
	$ma_group = array(
					0=>array('ALL', 'IDC', 'MED', 'MEP'),
					1=>array(100,101,102,103,104,105,106,108,107,111,112,113,114,115,116,117,130,131,132,133,134,135,136)
			   );

	$sql = "
	SELECT 
		ma_is_manager_all,
		ma_is_manager_idc,
		ma_is_manager_med,
		ma_is_manager_mep,
		ma_display_as,
		ma_authority,
		isEnableTab(ma_idx) AS ma_see_tab,
		";

	for($i=0; $i<count($ma_group[0]); $i++) {
		for($j=0; $j<count($ma_group[1]); $j++) {
			$sql .= "(select gm_perm from tb_gmember where ma_idx=".$ma_idx." AND gr_access='".$ma_group[0][$i]."' AND gr_idx=".$ma_group[1][$j].") AS ".$ma_group[0][$i]."_".$ma_group[1][$j].",";
		}
	}

	$sql = substr($sql,1,-1);
	$sql .= " FROM tb_mbracc WHERE ma_idx = $ma_idx";
/*
echo "<pre>";
echo $sql;
echo "</pre>";
*/
	$row = fetchRowAssoc(query($sql));

	$S->setValue("ma_isWeb", ZKP_URL);
	$S->setValue("ma_isLogin", true);
	$S->setValue("ma_account", $account);
	$S->setValue("ma_idx", (int) $ma_idx);
	$S->setValue("ma_is_manager_all", $row['ma_is_manager_all']);	
	$S->setValue("ma_is_manager_idc", $row['ma_is_manager_idc']);	
	$S->setValue("ma_is_manager_med", $row['ma_is_manager_med']);	
	$S->setValue("ma_is_manager_mep", $row['ma_is_manager_mep']);	
	$S->setValue("ma_authority", $row['ma_authority']);	
	$S->setValue("ma_see_tab", $row['ma_see_tab']);

	if($row["ma_display_as"] & 1) $S->setValue("ma_is_marketing_idc", true);
	else 			$S->setValue("ma_is_marketing_idc", false);
	if($row["ma_display_as"] & 2) $S->setValue("ma_is_marketing_med", true);
	else 			$S->setValue("ma_is_marketing_med", false);
	if($row["ma_display_as"] & 4) $S->setValue("ma_see_all", true);
	else 			$S->setValue("ma_see_all", false);

	// SET ACCOUNT PERM FOR EACH MODUL
	for($i=0; $i<count($ma_group[0]); $i++) {
		for($j=0; $j<count($ma_group[1]); $j++) {
			$S->setValue("ma_".strtoupper($ma_group[0][$i])."_".$ma_group[1][$j], ($row[strtolower($ma_group[0][$i])."_".$ma_group[1][$j]] == '') ? 0 : $row[strtolower($ma_group[0][$i])."_".$ma_group[1][$j]]);
		}
	}

	goPage(CHOOSE_PAGE . "?showMsg=true");
}
?>