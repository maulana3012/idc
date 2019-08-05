<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*
* $Id: _left_menu.php,v 1.1 2008/05/03 06:29:37 neki Exp $
*/


//------------------------------------------------------------------
// Left Menu (for Admin Page)
//   Menu Category. (Define $item as number of element
//------------------------------------------------------------------
//$cat = array("ORDER", "ITEM", "SALES REPORT");
$cat = array("DEMO", "Outgoing Demo", "Return Demo");
//------------------------------------------------------------------

//------------------------------------------------------------------
// Menu Item ("Url::Pagename")
//  variable name is ${"item".Index of $cat array}
//------------------------------------------------------------------
$item0[] = "list_detail_demo.php::Detail Stock";
$item0[] = "list_demo.php::Per Item";
$item0[] = "list_ed_demo.php::Per E/D";

$item1[] = "daily_outgoing_demo_by_item.php::By Item";
$item1[] = "daily_outgoing_demo_by_reference.php::By Reference";

$item2[] = "daily_return_demo_by_item.php::By Item";
$item2[] = "daily_return_demo_by_reference.php::By Reference";
//------------------------------------------------------------------

if(isset($cat)) {
	echo "          <td width=\"14%\" height=\"480\" valign=\"top\" style=\"padding:5\" bgcolor=\"#F0F5F6\"  style=\"border-right-width:1px; border-right-style:solid; border-right-color:#CCC\">\n";
	require_once APP_DIR . "_include/tpl_leftMenu.php";
	echo "          </td>\n";
}
?>