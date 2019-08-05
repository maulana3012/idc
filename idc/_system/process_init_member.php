<?php
/**
* Copyright(c) 2005 PT. ZONEKOM All right reserved
* Homepage http://www.zonekom.com
* Contact us dskim@indosat.net.id
*
* author : dskim
*
*
* $Id: process_init_member.php,v 1.1 2008/04/28 06:52:13 neki Exp $
*/

require_once APP_DIR . "../_lib/zk_dbconn.php";

//WRITE ACCESS LOG ==================================================================
$accessLog = array(
	$S->getValue('ma_idx'),
	$S->getValue('ma_account')
	);

writeLog(APP_DIR . "../log/member_access_log.txt",$accessLog);

//CHECK POLICY =======================================================================
//
//	0 : no message
//	1 : License will expire within 14 days
//	2 : License alreay expired. now is applied the gracetime
//	4 : password will expired with in 3 days
//
if(isZKError($result = executeSP("checkpolicy",$S->getValue('ma_idx')))) {
	$M->goErrorPage($result, CHOOSE_PAGE);
} else {
	$rtnSP = $result[0];
}

//display message
$hasInformation = false;
$message = "<ul>\n";


//execute query for rtnSP 1 or 2
if ($rtnSP & 1 || $rtnSP & 2) {
	$sql = "SELECT pl_opt1, pl_opt2, pl_opt3, pl_opt4, pl_opt5, pl_opt6 FROM tb_policy WHERE pl_no = 447";

	if(isZKError($result =& query($sql))) {
		$M->goErrorPage($result, CHOOSE_PAGE);
	} else {
		$data = fetchRow($result);
	}
}

// Inform license information to director to decide will continue or not
if ($rtnSP & 1) {
	$receiver = array_slice($data, 2);
	if(in_array($S->getValue('ma_idx'), $receiver)) {
		$hasInformation	= true;
		$message .= "<li>Software license will be expired as below</li><br/>\n";
		$message .= "<strong>License Expire Date : ".date("j M, Y", $data[0])." 23:59:59</strong><br/>";
	}

//inform all the person who login at the system
} elseif($rtnSP & 2) {
	$hasInformation = true;
	$systemClosingDate = date("j M, Y", $data[0] + $data[2]);
	$message .= "<li>Software license is already expired. User would be accessed the system until as below.</li><br>\n";
	$message .= "<strong>System closing date : {$systemClosingDate} 23:59:59</strong><br/>";
}

$message .= "<br/>Please contact to PT. ZONEKOM.<br/><br/>\n";

//check pasword validation
if ($rtnSP & 4) {
	$hasInformation = true;

	$result1 =& query("SELECT ma_lastpasswdchangedate FROM tb_mbracc WHERE ma_idx = " . $S->getValue('ma_idx'));
	$result2 =& query("SELECT pl_opt4 FROM tb_policy WHERE pl_no = 154");

	$data1 = fetchRow($result1);
	$data2 = fetchRow($result2);
	$pwExpireDate = date("j M, Y", strtotime($data1[0]) + $data2[0] - 86400);

	$message .= "<li>Your password will be expired soon. Please change your password now.</li><br/>\n";
	$message .= "<strong>Password Expire Date : " . $pwExpireDate . " 23:59:59</strong>\n";
}

$message .= "</ul>\n";

if($hasInformation) {
	$o = new ZKError("INFORMATION", "INFORMATION", $message);
	$M->goErrorPage($o, $_GET['returnUrl']);
}
?>