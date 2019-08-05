<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*
* $Id: _left_menu.php,v 1.2 2008/06/30 06:11:05 neki Exp $
*/
//------------------------------------------------------------------
// Left Menu (for Admin Page)
//   Menu Category. (Define $item as number of element
//------------------------------------------------------------------
$cat = array("BILLING SUMMARY", "REPORT");
//------------------------------------------------------------------
$item0[] = "summary_by_item.php::By Item";
$item0[] = "summary_by_customer.php::By Customer";
$item0[] = "summary_by_channel.php::By Dept";
$item1[] = "summary_historical_item.php::In-Out Item";
//------------------------------------------------------------------

if(isset($cat)) {
	echo "          <td width=\"14%\" height=\"480\" valign=\"top\" style=\"padding:5\" bgcolor=\"#F0F5F6\"  style=\"border-right-width:1px; border-right-style:solid; border-right-color:#CCC\">\n";
	require_once APP_DIR . "_include/tpl_leftMenu.php";
	echo "          </td>\n";
}
?>