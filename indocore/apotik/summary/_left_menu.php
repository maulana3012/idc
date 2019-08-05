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
//$cat = array("ORDER", "ITEM", "SALES REPORT");
$cat = array("Billing Summary", "Debit Summary", "Payment Summary", "Booking Summary", "Delivery Summary", "Monthly Summary", "Deposit Summary");
//------------------------------------------------------------------

//------------------------------------------------------------------
// Menu Item ("Url::Pagename")
//  variable name is ${"item".Index of $cat array}
//------------------------------------------------------------------
$item0[] = "daily_billing_by_item.php::By Item";
$item0[] = "daily_billing_by_group.php::By Group";
$item0[] = "daily_billing_by_customer.php::By Customer";

$item1[] = "debit_by_group.php::By Group";
$item1[] = "debit_by_customer.php::By Customer";
$item1[] = "debit_by_due_date.php::By Due date";

$item2[] = "payment_by_group.php::By Group";
$item2[] = "payment_by_customer.php::By Customer";
$item2[] = "payment_by_date.php::By Period";

$item3[] = "daily_booking_by_item.php::By Item";
$item3[] = "daily_booking_by_group.php::By Group";

$item4[] = "delivery_by_item.php::By Item";
$item4[] = "delivery_by_group.php::By Group";
$item4[] = "delivery_by_customer.php::By Customer";

$item5[] = "summary_monthly_sales_by_item.php::By Item";
$item5[] = "summary_monthly_by_customer.php::By Amount";

$item6[] = "list_deposit.php::By Customer";
//------------------------------------------------------------------

if(isset($cat)) {
	echo "          <td width=\"14%\" height=\"480\" valign=\"top\" style=\"padding:5\" bgcolor=\"#F0F5F6\"  style=\"border-right-width:1px; border-right-style:solid; border-right-color:#CCC\">\n";
	require_once APP_DIR . "_include/tpl_leftMenu.php";
	echo "          </td>\n";
}
?>