<?php
/**
* Copyright(c) 2005 PT. ZONEKOM All right reserved
* Homepage http://www.zonekom.com
* Contact us dskim@indosat.net.id
*
* author : dskim
*
* $Id: zk_config.php,v 1.2 2007/12/03 06:15:45 dskim Exp $
*/
//session_cache_limiter('private');
session_start();
//ob_start();

//------------------------------------------------------------------------
//  DATABASE SECTION
//------------------------------------------------------------------------
$dbms      = "pgsql";
$dbuser    = "dskim";
$dbpass    = "2v2soft~!";
//$dbhost    = "127.0.0.1";
$dbhost    = "192.168.10.80";
$dbname    = "db_medisindo";

//------------------------------------------------------------------------
//  DEFINE CONSTANT SECTION
//------------------------------------------------------------------------
define("HTTP_DIR", '/med/');
define("USER_DATA", "../../_user_data/medisindo/");
//define("PDF_STORAGE", "C:/Program Files/PostgreSQL/EnterpriseDB-ApachePhp/apache/www/devel/indocore/");
define("PDF_STORAGE", "/home/pdf/medisindo/");
define("PDF_STORAGE_LETTER", "/home/pdf/medisindo/letter");
define("ZKP_FUNCTION", "MED");		// ALL, IDC, MED
define("ZKP_URL", "MED");			// ALL, IDC, MEP, MED
define("ZKP_SQL", "MED");			// IDC, MED
define("IP", '192.168.2.88');
//------------------------------------------------------------------------
define("APP_DIR", str_replace("\\", "/", dirname( __FILE__ )) . "/");		//web server app directory
define("LIB_DIR", APP_DIR . "../_lib/"); 									//web server lib directory
define("BEFORELOGIN_INCLUDE_URL", APP_DIR . "_include/tpl_beforeLogin.php");
define("AFTERLOGIN_INCLUDE_URL", APP_DIR . "_include/tpl_afterLogin.php");
define("ERROR_MESSAGE", APP_DIR . "_system/errorBook_kr.txt");
define("MAIN_PAGE", HTTP_DIR . "admin/main/index.php");
define("CHOOSE_PAGE", HTTP_DIR . "admin/main/choose_group.php");
define("ERROR_PAGE", HTTP_DIR . "_system/tools/page_error.php");
define("LOGIN_PAGE", HTTP_DIR . "_system/tools/page_login.php");
define("LOGIN_PROCESS", APP_DIR . "_system/process_login.php");
define("AFTER_LOGOUT_PAGE", MAIN_PAGE);
define("AFTER_LOGIN_PAGE", MAIN_PAGE);
define('RUNTIME_MODE', '');

// ZONEKOM Permission
define('ZKP_SELECT',  1);
define('ZKP_INSERT',  2);
define('ZKP_UPDATE',  4);
define('ZKP_DELETE',  8);

define ("INIT_PROCESS", APP_DIR . "_system/process_init.php"); 					//When guest session is made
define ("COMMON_PROCESS", APP_DIR . "_system/process_common.php"); 				//When every guest & member connection
define ("INIT_MEMBER_PROCESS", APP_DIR . "_system/process_init_member.php");	//When member session is made
define("COMMON_MEMBER_PROCESS", APP_DIR . "_system/process_common_member.php"); //When every member connection
define("AFTER_LOGOUT_PROCESS", APP_DIR . "_system/process_after_logout.php");	//When After user logout.

if (!isset($_SERVER['REQUEST_URI'])) {
	$_SERVER['REQUEST_URI'] = "";
}

if ($_SERVER['PHP_SELF'] == LOGIN_PAGE) {
	define("LOGIN_URL", LOGIN_PAGE . "?returnUrl=" . MAIN_PAGE);
} else {
	define("LOGIN_URL", LOGIN_PAGE . "?returnUrl=" . urlencode($_SERVER['REQUEST_URI']));
}

if (empty($_SERVER['QUERY_STRING'])) {
	define("LOGOUT_URL", $_SERVER['REQUEST_URI'] . urlencode("?logout=1&returnUrl=" . $_SERVER['REQUEST_URI']));
} else {
	define("LOGOUT_URL", $_SERVER['REQUEST_URI'] . urlencode("&logout=1&returnUrl=" . $_SERVER['REQUEST_URI']));
}

//------------------------------------------------------------------------
// COMMON INCLUDE SECTION
//------------------------------------------------------------------------
require_once LIB_DIR . "zk_common_function.php";
require_once LIB_DIR . "zk_session.php";
require_once LIB_DIR . "zk_messenger.php";
require_once APP_DIR . "_system/util_variable.php";

$S = new ZKSession;
$M = new Messenger;

//------------------------------------------------------------------------
// EXECUTE COMMON PROCESS
//------------------------------------------------------------------------
require_once LIB_DIR . "zk_common_process.php";
?>
