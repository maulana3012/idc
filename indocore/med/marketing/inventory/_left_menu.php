<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*
* $Id: _left_menu.php,v 1.1 2008/04/28 06:52:13 neki Exp $
*/


//------------------------------------------------------------------
// Left Menu (for Admin Page)
//   Menu Category. (Define $item as number of element
//------------------------------------------------------------------
$cat = array("Inventory Summary", "Setup Initial Stock");
//------------------------------------------------------------------

//------------------------------------------------------------------
// Menu Item ("Url::Pagename")
//  variable name is ${"item".Index of $cat array}
//------------------------------------------------------------------
$item0[] = "inventory_summary_by_group.php::By Group";
$item0[] = "inventory_summary_by_item.php::By Item Category";
$item0[] = "inventory_summary_by_customer.php::By Customer";

$item1[] = "setup_initial_stock.php::Setup JK Qty";
//------------------------------------------------------------------

if(isset($cat)) {
	echo "          <td width=\"14%\" height=\"480\" valign=\"top\" style=\"padding:5\" bgcolor=\"#F0F5F6\"  style=\"border-right-width:1px; border-right-style:solid; border-right-color:#CCC\">\n";
	require_once APP_DIR . "_include/tpl_leftMenu.php";
	echo "          </td>\n";
}
?>