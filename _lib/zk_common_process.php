<?php
/**
* Copyright(c) 2005 PT. ZONEKOM All right reserved
* Contact us dskim@zonekom.com
*
* author : dskim
*
* $Id: zk_common_process.php,v 1.2 2008/02/28 04:39:45 dskim Exp $
*/

$login_include = BEFORELOGIN_INCLUDE_URL;
if ($S->getInitCommon()) {
	if (INIT_PROCESS != "") {
        require_once INIT_PROCESS;
	}
}
if (COMMON_PROCESS != "") {
	require_once COMMON_PROCESS;
}

if ($S->isLogin() === true) {

    $login_include = AFTERLOGIN_INCLUDE_URL;
	if ($S->getValue('isInitMember')) {
		if (INIT_MEMBER_PROCESS != "") {
			$S->setValue('isInitMember', false);
            require_once INIT_MEMBER_PROCESS;
		}
	}

	if (COMMON_MEMBER_PROCESS != "") {
        require_once COMMON_MEMBER_PROCESS;
	}

    if (isset($_GET['logout'])) {
        if (!isset($_REQUEST['returnUrl'])) {
            die("������ Error : �α׾ƿ��ÿ��� �ݵ�� �α׾ƿ� �� �̵��� returnUrl ���� �����ؾ� �Ѵ�.");
        }

		$S->logout();

        if (AFTER_LOGOUT_PROCESS != "") {
            require_once AFTER_LOGOUT_PROCESS;
        }

        $_GET = delArrayByKey($_GET, 'logout');

        if (isset($_REQUEST['returnUrl'])) {
            $returnUrl = urldecode($_REQUEST['returnUrl']);
        } else {
            $returnUrl = getQueryString();
        }

        if (AFTER_LOGOUT_PAGE == '') {
            $M->goPage($returnUrl);
        } else {
            $M->goPage(AFTER_LOGOUT_PAGE . "?" .getQueryString() . "&returnUrl=". $returnUrl);
        }

        exit;
    }

} elseif (isset($_POST['userpw']) && trim($_POST['userpw']) != '' && isset($_POST['userid']) && trim($_POST['userid']) != '') {

    if (!isset($_REQUEST['returnUrl'])) {
        die("������ Error : �α��νÿ��� �ݵ�� returnUrl ���� �����ؾ� �Ѵ�.");
    }

    if (LOGIN_PROCESS == "") {
		die("������ Error : LOGIN_PROCESS ������ config ���Ͽ��� ������ ���� �ʾ���");
    } else {

        require_once LOGIN_PROCESS;
		$S->setValue("loginTimestamp", time());
		$login_include = AFTERLOGIN_INCLUDE_URL;

        $returnUrl = $_REQUEST['returnUrl'];
        $_GET = delArrayByKey($_GET, "returnUrl");

        if (AFTER_LOGIN_PAGE == '') {
            $M->goPage(urldecode($returnUrl));
        } else {
            $M->goPage(AFTER_LOGIN_PAGE . "?" . getQueryString() . "&returnUrl=" . $returnUrl);
        }
	}
	exit;
} else {

}
?>