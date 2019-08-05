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
$cat = array("Setup Initial Stock", "Change Stock", "Move Summary", "Reject Item");
//------------------------------------------------------------------

//------------------------------------------------------------------
// Menu Item ("Url::Pagename")
//  variable name is ${"item".Index of $cat array}
//------------------------------------------------------------------
$item0[] = "input_initial_stock.php::Setup Stock";
$item0[] = "list_initial_stock.php::List Initial Stock";

$item1[] = "input_move_location.php::Move Location";
$item1[] = "list_borrow_by_item.php::Move Type";
$item1[] = "input_move_type_po.php::Move Type 2";

$item2[] = "list_remove_by_item.php::By Item";
$item2[] = "list_remove_by_date.php::By Date";

$item3[] = "input_initial_reject.php::Add Reject Item";
$item3[] = "list_reject_by_item.php::By Item";
$item3[] = "list_reject_by_group.php::By Group";
$item3[] = "list_reject_ed_by_item.php::Deleted E/D";
//------------------------------------------------------------------

if(isset($cat)) {
	echo "          <td width=\"14%\" height=\"480\" valign=\"top\" style=\"padding:5\" bgcolor=\"#F0F5F6\"  style=\"border-right-width:1px; border-right-style:solid; border-right-color:#CCC\">\n";
	require_once APP_DIR . "_include/tpl_leftMenu.php";
	echo "          </td>\n";
}
?>