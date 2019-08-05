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

if($currentDept != "letter") {
	if($S->getValue("ma_is_manager_".strtolower(ZKP_URL)) != "t") {
		$gPerm	= $grMember[$currentDept][1];
		$aPerm	= $S->getValue("ma_".ZKP_URL."_".$gPerm);
	} else {
		$gPerm	= 100;
		$aPerm = $S->getValue("ma_".ZKP_URL."_"."100");
	} 
} else {
	$gPerm	= $grMember['letter_'.$moduleDept][1];
	if($S->getValue("ma_".ZKP_URL."_130") > 0) {
		$aPerm = $S->getValue("ma_".ZKP_URL."_"."130");
	} else {
		$aPerm	= $S->getValue("ma_".ZKP_URL."_".$gPerm);
	}
}
/*if($S->getValue("ma_is_manager_".strtolower(ZKP_URL)) != "t") {
	$gPerm	= $grMember[$currentDept][1];
	$aPerm	= $S->getValue("ma_".ZKP_URL."_".$gPerm);
} else {
	$gPerm	= 100;
	$aPerm = $S->getValue("ma_".ZKP_URL."_"."100");
} */
/*
$conf_perm	= $GROUP_PERMISSION[$gPerm];
echo "<pre>";
var_dump($gPerm, $aPerm, $conf_perm);
echo "</pre>";
*/
$oNEP = new ZKError(
	"ZK003",
	"NOT_ENOUGH_PERMISSION",
	"Sorry, You don't have permission. Please contact to administrator.".
	"<ul style='display:'>".
		"Your account configured as below <br />".
		"<li>Require : ".
			"INQUIRY:<input type='checkbox'".(($conf_perm & ZKP_SELECT)?" checked>":">")."&nbsp;&nbsp;".
			"INPUT:<input type='checkbox'".(($conf_perm & ZKP_INSERT)?" checked>":">")."&nbsp;&nbsp;".
			"UPDATE:<input type='checkbox'".(($conf_perm & ZKP_UPDATE)?" checked>":">")."&nbsp;&nbsp;".
			"DELETE:<input type='checkbox'".(($conf_perm & ZKP_DELETE)?" checked>":">")."</li>".
		"<li>Your permission : ".
			"INQUIRY:<input type='checkbox'".(($aPerm & ZKP_SELECT)?" checked>":">")."&nbsp;&nbsp;".
			"INPUT:<input type='checkbox'".(($aPerm & ZKP_INSERT)?" checked>":">")."&nbsp;&nbsp;".
			"UPDATE:<input type='checkbox'".(($aPerm & ZKP_UPDATE)?" checked>":">")."&nbsp;&nbsp;".
			"DELETE:<input type='checkbox'".(($aPerm & ZKP_DELETE)?" checked>":">")."</li>".
	"</ul>");

//CHECK USER LOGIN
if (isZKError($o = $S->isLogin())) {
	$M->goErrorPage($o, LOGIN_PAGE . "?returnUrl=".urlencode($_SERVER['PHP_SELF']));
}

//Read DIRECTORY Permission
if (!isset($GROUP_PERMISSION[$gPerm]) ||
	!($GROUP_PERMISSION[$gPerm] & $aPerm)) {

	if($S->getValue("ma_grade") != "MANAGER") {
		$M->goErrorPage($oNEP, MAIN_PAGE);
	}
} 

//Check Permission
//
// my group's directory permission	: $GROUP_PERMISSION[$S->getValue($gPerm);
// Requested Permission				: $octal;
// my group's persional permission	: $S->getValue("ma_permission");
//
//	arg 1	: Requested Permission 
//	arg 2	: afer error Goto 
//	arg 2	: Process name
function ckperm($octal, $afterErrorGoto, $p_mode = "") {
	global $S, $M, $oNEP, $conf_perm, $dbconn, $gPerm, $aPerm;

	$rtn = false;
	$pn[1] = "INQUIRY";
	$pn[2] = "INPUT";
	$pn[4] = "UPDATE";
	$pn[8] = "DELETE";

	//case 1: ckperm(ZKP_SELECT, "../main/index.php");
	if ($p_mode == "") {
		if (($conf_perm & $octal) && ($aPerm & $octal)) {
			$rtn = true;
		} else {
			$oNEP->message = sprintf($oNEP->message, $pn[$octal]);
			$M->goErrorPage($oNEP, $afterErrorGoto);
		}
	//case 2: if(ckperm(ZKP_SELECT, "../main/index.php", "update")) { ... }
	} elseif (($p_mode != "") && isset($_POST['p_mode']) && ($_POST['p_mode'] == $p_mode)) {
		if (($conf_perm & $octal) && ($aPerm & $octal)) {
			$rtn = true;
		} else {
			$oNEP->message = sprintf($oNEP->message, $pn[$octal]);		
			$M->goErrorPage($oNEP, $afterErrorGoto);
		}
	}

	return $rtn;
}
?>