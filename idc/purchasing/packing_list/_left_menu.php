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
$cat = array("NEW PL", "PL Summary", "PL Balance", "Outstanding PL", "Arrival PL");
//------------------------------------------------------------------

//------------------------------------------------------------------
// Menu Item ("Url::Pagename")
//  variable name is ${"item".Index of $cat array}
//------------------------------------------------------------------
$item0[] = "input_pl_step_1.php::from PO";
$item0[] = "input_pl_step_3.php::from Claim";

$item1[] = "summary_pl_by_item.php::By Item";
$item1[] = "summary_pl_by_supplier.php::By Supplier";

$item2[] = "summary_pl_balance_by_item.php::By Item";
$item2[] = "summary_pl_balance_by_supplier.php::By Supplier";

$item3[] = "summary_outstanding_by_item.php::By Item";
$item3[] = "summary_outstanding_by_supplier.php::By Supplier";

$item4[] = "summary_arrival_by_item.php::By Item";
$item4[] = "summary_arrival_by_supplier.php::By Supplier";
//------------------------------------------------------------------

if(isset($cat)) {
	echo "          <td width=\"14%\" height=\"480\" valign=\"top\" style=\"padding:5\" bgcolor=\"#F0F5F6\"  style=\"border-right-width:1px; border-right-style:solid; border-right-color:#CCC\">\n";
	require_once APP_DIR . "_include/tpl_leftMenu.php";
	echo "          </td>\n";
}
?>