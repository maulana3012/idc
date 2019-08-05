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
if($currentDept == 'purchasing')
{
	$cat = array("STOCK", "QUANTITY LEVEL", "Reject Item");
	$item0 = array("list_stock.php::Per Item", 'list_stock_ed.php::Per E/D', 'list_stock_detail.php::Detail Stock');
	$item1 = array("list_quantity_level.php::Stock Level");
	$item2 = array("list_reject_by_item.php::By Item", "list_reject_by_group.php::By Group", "list_reject_ed_by_item.php::Deleted E/D");
} else 
{
	$cat = array("STOCK", "Reject Item");
	$item0 = array("list_stock.php::Per Item", 'list_stock_ed.php::Per E/D', 'list_stock_detail.php::Detail Stock');
	$item1 = array("list_reject_by_item.php::By Item", "list_reject_by_group.php::By Group", "list_reject_ed_by_item.php::Deleted E/D");
}
//------------------------------------------------------------------

//------------------------------------------------------------------
// Menu Item ("Url::Pagename")
//  variable name is ${"item".Index of $cat array}
//------------------------------------------------------------------



//------------------------------------------------------------------

if(isset($cat)) {
	echo "          <td width=\"14%\" height=\"480\" valign=\"top\" style=\"padding:5\" bgcolor=\"#F0F5F6\"  style=\"border-right-width:1px; border-right-style:solid; border-right-color:#CCC\">\n";
	require_once APP_DIR . "_include/tpl_leftMenu.php";
	echo "          </td>\n";
}
?>