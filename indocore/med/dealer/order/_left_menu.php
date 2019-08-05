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
$cat = array("NEW", "Order Summary", "Outstanding Delivery", "Delivery Summary", "Monthly Summary", "Customer Deli Log");
//------------------------------------------------------------------

//------------------------------------------------------------------
$item0[] = "input_order_step_1.php::New Order";
$item0[] = "input_return_order_step_1.php::New Return";

$item1[] = "summary_order_by_item.php::By Item";
$item1[] = "summary_order_by_group.php::By Group";
$item1[] = "summary_order_by_customer.php::By Customer";

$item2[] = "summary_outstanding_by_item.php::By Item";
$item2[] = "summary_outstanding_by_group.php::By Group";

$item3[] = "summary_delivery_by_item.php::By Item";
$item3[] = "summary_delivery_by_group.php::By Group";
$item3[] = "summary_delivery_by_customer.php::By Customer";

$item4[] = "summary_monthly_consignment_by_item.php::By Item";

$item5[] = "summary_delivery_log_by_order.php::By Order";
//------------------------------------------------------------------

if(isset($cat)) {
	echo "          <td width=\"14%\" height=\"480\" valign=\"top\" style=\"padding:5\" bgcolor=\"#F0F5F6\"  style=\"border-right-width:1px; border-right-style:solid; border-right-color:#CCC\">\n";
	require_once APP_DIR . "_include/tpl_leftMenu.php";
	echo "          </td>\n";
}
?>