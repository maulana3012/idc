<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*
* $Id: _left_menu.php,v 1.1 2008/04/28 06:52:13 neki Exp $
*/
$cat = array("New Request", "Request Demo", "Return Demo");

$item0[] = "input_request.php::Request Demo";

$item1[] = "daily_request_demo_by_item.php::By Item";
$item1[] = "daily_request_demo_by_reference.php::By Reference";

$item2[] = "daily_return_demo_by_item.php::By Item";
$item2[] = "daily_return_demo_by_reference.php::By Reference";
//------------------------------------------------------------------
if(isset($cat)) {
	echo "          <td width=\"14%\" height=\"480\" valign=\"top\" style=\"padding:5\" bgcolor=\"#F0F5F6\"  style=\"border-right-width:1px; border-right-style:solid; border-right-color:#CCC\">\n";
	require_once APP_DIR . "_include/tpl_leftMenu.php";
	echo "          </td>\n";
}
?>