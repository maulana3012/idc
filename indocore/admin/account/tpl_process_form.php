<?php
//INSERT ACCOUNT ======================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "admin/account/input_account.php", 'insert_account')) {
	require_once(LIB_DIR . "zk_dbconn.php");

	$txtAccount	= $_POST['txtAccount'];
	$txtUsrName	= $_POST['txtUsrName'];
	$txtRemark	= $_POST['txtRemark'];

	$result = executeSP(
			"addNewAccount",
			"$$$txtAccount$$",
			"$$$txtUsrName$$",
			"$$$txtRemark$$"
			);

	if (isZKError($result)) 
		$M->goErrorPage($result, HTTP_DIR . "admin/account/input_account.php");

	$o = new ZKError(
		"UNBLOCK_ACCOUNT",
		"New password issued",
		"Temporary password is as below. Please let user know this password. User will receved the announcement once he/she has logged on the system with this password.<br/><br/> Account : " . $txtAccount ."<br/> Temporary PW : " . $result[0]);
	$M->goErrorPage($o, HTTP_DIR . "admin/account/list_account.php");
}

//UPDATE REMARK =======================================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR."admin/account/list_account.php", "update")) {

	$txtRemark	= $_POST['txtRemark'];
	$chkDisplayAs	= isset($_POST["chkDisplayAs"]) ? array_sum($_POST["chkDisplayAs"]) : 0;

	$sql = "UPDATE tb_mbracc SET ma_remark = $\${$txtRemark}$\$, ma_display_as = $chkDisplayAs  WHERE ma_idx = $ma_idx";

	if(isZKError($result =& query($sql))) {
		$M->goErrorPage($result, HTTP_DIR . "admin/account/list_account.php");
	}

	$M->goPage(HTTP_DIR."admin/account/detail_account.php?ma_idx=$ma_idx");
}

//DELETE ACCOUNT ======================================================================================================
if(ckperm(ZKP_DELETE, HTTP_DIR."admin/account/list_account.php", "deleteAccount")) {
	$ma_idx = $_POST['ma_idx'];
	$result = query("DELETE FROM tb_mbracc WHERE ma_idx=$ma_idx");
	if(isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR."admin/account/list_account.php");
	} else {
		$o = new ZKError("ACCOUNT_WAS_DELETED", "ACCOUNT_WAS_DELETED", "Account was delete from system");
		$M->goErrorPage($o, HTTP_DIR."admin/account/list_account.php");
	}
}

//ISSUE PASSWORD ======================================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR."admin/account/list_account.php", "activate")) {
	if(isZKError($result = executeSP("issueTempPwd", $ma_idx))) {
		$M->goErrorPage($result, HTTP_DIR . "admin/account/list_account.php");
	}

	$o = new ZKError(
		"UNBLOCK_ACCOUNT",
		"New password issued",
		"Use this temporary password to activate user account.<br/><br/> Account ID : " . $ma_idx ."<br/> Temporary PW : " . $result[0]);

	$M->goErrorPage($o, HTTP_DIR . "admin/account/detail_account.php?ma_idx=".$ma_idx);
}

//BLOCK ACCOUNT =======================================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR."admin/account/list_account.php", "block")) {

	$result = query("UPDATE tb_mbracc SET ma_isvalidacc = FALSE, ma_passwordblockdate = CURRENT_TIMESTAMP WHERE ma_idx = $ma_idx");
	if(isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "admin/account/list_account.php");
	}

	$o = new ZKError(
		"BLOCK_ACCOUNT",
		"ACCOUNT WAS BLOCKED",
		"This account is block by administrator");
	$M->goErrorPage($o, HTTP_DIR . "admin/account/detail_account.php?ma_idx=".$ma_idx);
}

//UPDATE PERM =========================================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR."admin/account/list_account.php", "updatePerm")) {

	$ma_idx	  = $_POST["_idx"];
	$_gr_grade = array();
	$arrPerm = array();

	for($i=0; $i<5; $i++) {
		$_gr_grade[] = (isset($_POST["_gr_grade_". strtolower($part[$i])]))	? $_POST["_gr_grade_". strtolower($part[$i])] : 'false';
		foreach ($_POST["_gr_idx_".$part[$i]] as $val) {
			if(isset($_POST["chkPerm_".$part[$i]."_".$val])) {
				$arrPerm[0][] = $part[$i];
				$arrPerm[1][] = "[".$val.",".array_sum($_POST["chkPerm_".$part[$i]."_".$val])."]";
			} 
		}
	}
	$_gr_grade = '$$' . implode('$$,$$', $_gr_grade) . '$$';

	if(isZKError(
		$result = executeSP(
			"updateGroupPermissionForMember",
			$ma_idx,
			"ARRAY[$_gr_grade]",
			"ARRAY[$$".implode("$$,$$",$arrPerm[0])."$$]",
			"ARRAY[".implode(",",$arrPerm[1])."]"
			)
		)
		) {
		$M->goErrorPage($result, HTTP_DIR . "admin/account/detail_account.php?ma_idx=".$ma_idx);
	}

	$M->goPage(HTTP_DIR . "admin/account/detail_account.php?ma_idx=".$ma_idx);
}

//UPDATE AUTH =========================================================================================================
if(ckperm(ZKP_UPDATE, HTTP_DIR."admin/account/list_account.php", "updateAuth")) {
	$ma_idx	= $_POST["_idx"];
	$_auth	= 0;
	foreach($_POST['_auth'] as $val) 	$_auth = $_auth + $val;

	$result = query("UPDATE tb_mbracc SET ma_authority = $_auth WHERE ma_idx = $ma_idx");
	if(isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR."admin/account/list_account.php");
	} else {
		$M->goPage(HTTP_DIR . "admin/account/detail_account.php?ma_idx=".$ma_idx);
	}
}

//INSERT MEMBER =======================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "admin/account/p_group_member_list.php?_access=$_access&_idx=$_idx&_name=$_name", 'insertMember')) {

	$_access = $_POST['_access'];
	$lstNewMember = $_POST['lstNewMember'];
	$chkPerm	= array_sum($_POST["chkNewPerm"]) + 1; //1 means inquiry

	if(isZKError($result = executeSP("asignMemberToGroup", "$\${$_access}$\$", $_idx, $lstNewMember, $chkPerm))) {

		$M->goErrorPage($result, HTTP_DIR . "admin/account/p_group_member_list.php?_access=$_access&_idx=$_idx&_name=".urlencode($_name));
	}

	$M->goPage(HTTP_DIR . "admin/account/p_group_member_list.php?_access=$_access&_idx=$_idx&_name=".urlencode($_name));
}

//DELETE MEMBER =======================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "admin/account/p_group_member_list.php?_access=$_access&_idx=$_idx&_name=$_name", 'deleteMember')) {

	if(isZKError($result = & executeSP("removeMemberPermission", "'".ZKP_FUNCTION."'", "$\$group$\$", $_idx,"ARRAY[".implode(",",$_POST['chk'])."]"))) {
		$M->goErrorPage($result, HTTP_DIR . "admin/account/p_group_member_list.php?_access=$_access&_idx=$_idx&_name=".urlencode($_name));
	}
}

//UPDATE PERM =========================================================================================================
if(ckperm(ZKP_INSERT, HTTP_DIR . "admin/account/p_group_member_list.php?_access=$_access&_idx=$_idx&_name=$_name", 'updatePermPopUp')) {

	$_access = $_POST['_access'];
	$_gr_grade = $_POST['_gr_grade'];

	$arrPerm = array();
	foreach ($_POST['chk'] as $val) {
		$_POST['chkPerm_'.$val][] = 1;
		$arrPerm[] = "[".$val.",".array_sum($_POST['chkPerm_'.$val])."]";
	}

	if(isZKError(
		$result = executeSP(
			"updateGroupPermissionForMember",
			"$\${$_access}$\$", 
			$_idx,
			$_gr_grade,
			"ARRAY[".implode(",",$arrPerm)."]"))) {
			$M->goErrorPage($result, HTTP_DIR . "admin/account/p_group_member_list.php?_access=$_access&_idx=$_idx&_name=".urlencode($_name));
	}
	
	$M->goPage(HTTP_DIR . "admin/account/p_group_member_list.php?_access=$_access&_idx=$_idx&_name=".urlencode($_name));
}
?>