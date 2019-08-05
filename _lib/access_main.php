<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @author : daesung kim
*
* $Id: access_main.php,v 1.2 2008/02/27 13:05:19 dskim Exp $
*/
require_once "zk_dbconn.php";

$conf_perm = $GROUP_PERMISSION[$S->getValue("ma_group_code")];
/*
//Define Superuser account who has full access :|
if($S->getValue('ma_idx') == 1) {
	define("ZKP_ACCESSIBLE", 'true');
} elseif($S->getValue('ma_idx') == 74) {
	define("ZKP_ACCESSIBLE", 'true');
} else {
	define("ZKP_ACCESSIBLE", 'false');
}
*/
$oNEP = new ZKError(
	"ZK003",
	"NOT_ENOUGH_PERMISSION",
	"Sorry, You don't have %s permission. Please contact to administrator.".
	"<ul style='display:'>".
		"Your account is member of ".$S->getValue("ma_group_name").", this menu configured as below".
		"<li>[".$S->getValue("ma_group_code")."] ".$S->getValue("ma_group_name")." : ".
			"INQUIRY:<input type='checkbox'".(($conf_perm & ZKP_SELECT)?" checked>":">")."&nbsp;&nbsp;".
			"INPUT:<input type='checkbox'".(($conf_perm & ZKP_INSERT)?" checked>":">")."&nbsp;&nbsp;".
			"UPDATE:<input type='checkbox'".(($conf_perm & ZKP_UPDATE)?" checked>":">")."&nbsp;&nbsp;".
			"DELETE:<input type='checkbox'".(($conf_perm & ZKP_DELETE)?" checked>":">")."</li>".
		"<li>Your permission : ".
			"INQUIRY:<input type='checkbox'".(($S->getValue("ma_permission") & ZKP_SELECT)?" checked>":">")."&nbsp;&nbsp;".
			"INPUT:<input type='checkbox'".(($S->getValue("ma_permission") & ZKP_INSERT)?" checked>":">")."&nbsp;&nbsp;".
			"UPDATE:<input type='checkbox'".(($S->getValue("ma_permission") & ZKP_UPDATE)?" checked>":">")."&nbsp;&nbsp;".
			"DELETE:<input type='checkbox'".(($S->getValue("ma_permission") & ZKP_DELETE)?" checked>":">")."</li>".
	"</ul>");

//CHECK USER LOGIN
if (isZKError($o = $S->isLogin())) {
	$M->goErrorPage($o, LOGIN_PAGE . "?returnUrl=".urlencode($_SERVER['PHP_SELF']));
}

//Read DIRECTORY Permission
if (!isset($GROUP_PERMISSION[$S->getValue("ma_group_code")]) ||
	!($GROUP_PERMISSION[$S->getValue("ma_group_code")] & $S->getValue("ma_permission"))) {
	$M->goErrorPage($oNEP, MAIN_PAGE);
}

//Check Permission
//
// my group's directory permission	: $GROUP_PERMISSION[$S->getValue("ma_group_code");
// Requested Permission				: $octal;
// my group's persional permission	: $S->getValue("ma_permission");
//
//	arg 1	: Requested Permission 
//	arg 2	: afer error Goto 
//	arg 2	: Process name
function ckperm($octal, $afterErrorGoto, $p_mode = "") {
	global $S, $M, $oNEP, $conf_perm, $dbconn;
	$row = fetchRow(query("SELECT gm_perm FROM tb_gmember WHERE ma_idx = {$S->getValue('ma_idx')}")); 
	
	$rtn = false;
	$pn[1] = "INQUIRY";
	$pn[2] = "INPUT";
	$pn[4] = "UPDATE";
	$pn[8] = "DELETE";

	//case 1: ckperm(ZKP_SELECT, "../main/index.php");
	if ($p_mode == "") {
		if (($conf_perm & $octal) && ($row[0] & $octal)) {
			$rtn = true;
		} else {
			$oNEP->message = sprintf($oNEP->message, $pn[$octal]);
			$M->goErrorPage($oNEP, $afterErrorGoto);
		}
	//case 2: if(ckperm(ZKP_SELECT, "../main/index.php", "update")) { ... }
	} elseif (($p_mode != "") && isset($_POST['p_mode']) && ($_POST['p_mode'] == $p_mode)) {
		if (($conf_perm & $octal) && ($row[0] & $octal)) {
			$rtn = true;
		} else {
			$oNEP->message = sprintf($oNEP->message, $pn[$octal]);		
			$M->goErrorPage($oNEP, $afterErrorGoto);
		}
	}

	return $rtn;
}
?>