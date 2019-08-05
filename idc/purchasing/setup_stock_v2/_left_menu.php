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
$cat = array("Setup Initial Stock", "Critical Stock", "Reject Item");
//------------------------------------------------------------------

//------------------------------------------------------------------
// Menu Item ("Url::Pagename")
//  variable name is ${"item".Index of $cat array}
//------------------------------------------------------------------
$item0[] = "input_initial_stock.php::Setup Stock";
$item0[] = "list_initial_stock.php::List Initial Stock";

$item1[] = "list_quantity_level.php::Setup";

$item2[] = "input_initial_reject.php::Add Reject Item";
$item2[] = "list_reject_by_item.php::By Item";
$item2[] = "list_reject_by_group.php::By Group";
$item2[] = "list_reject_ed_by_item.php::Deleted E/D";
//------------------------------------------------------------------

if(isset($cat)) {
	echo "          <td width=\"14%\" height=\"480\" valign=\"top\" style=\"padding:5\" bgcolor=\"#F0F5F6\"  style=\"border-right-width:1px; border-right-style:solid; border-right-color:#CCC\">\n";
	require_once APP_DIR . "_include/tpl_leftMenu.php";
	echo "          </td>\n";
}
?>