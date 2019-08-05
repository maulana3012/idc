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
$cat = array("NEW", "ITEMS");
$item0 = array("input_item.php::New Items", "input_item_cat.php::Manage Category");

if($currentDept == 'purchasing')
	$item1 = array("list_item.php::Item List", "list_item_price.php::Price History", "list_item_price_net.php::Price Net History");
else if($currentDept == 'warehouse')
	$item1 = array("list_item.php::Item List");
else 	$item1 = array("list_item.php::Item List", "list_item_price.php::Price History");

//------------------------------------------------------------------

if(isset($cat)) {
	echo "          <td width=\"14%\" height=\"480\" valign=\"top\" style=\"padding:5\" bgcolor=\"#F0F5F6\"  style=\"border-right-width:1px; border-right-style:solid; border-right-color:#CCC\">\n";
	require_once APP_DIR . "_include/tpl_leftMenu.php";
	echo "          </td>\n";
}
?>