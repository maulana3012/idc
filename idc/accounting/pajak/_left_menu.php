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
$cat = array("SUMMARY", "SET UP");
//------------------------------------------------------------------

$item0[] = "daily_billing_by_invoice.php::By Invoice";
$item0[] = "list_fp_sent.php::Send Mail";

$item1[] = "list_available_number.php::List Faktur Number";
$item1[] = "list_returnable_invoice.php::Returnable Invoice";

//------------------------------------------------------------------

if(isset($cat)) {
	echo "          <td width=\"14%\" height=\"480\" valign=\"top\" style=\"padding:5\" bgcolor=\"#F0F5F6\"  style=\"border-right-width:1px; border-right-style:solid; border-right-color:#CCC\">\n";
	require_once APP_DIR . "_include/tpl_leftMenu.php";
	echo "          </td>\n";
}
?>