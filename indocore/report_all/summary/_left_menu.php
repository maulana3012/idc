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
$cat = array("BILLING SUMMARY", "DEBIT", "PAYMENT", "SUMMARY", "MONTHLY"/*, "WH Summary"*/);
//------------------------------------------------------------------

$item0[] = "daily_billing_by_item.php::By Item";
$item0[] = "daily_billing_by_group.php::By Group";
//$item0[] = "daily_billing_by_customer.php::By Customer";
$item0[] = "daily_billing_by_invoice.php::By Invoice";

$item1[] = "debit_by_group.php::By Group";
$item1[] = "debit_by_due_date.php::By Due date";
//$item1[] = "debit_by_customer.php::By Customer";

$item2[] = "payment_by_group.php::By Group";
$item2[] = "payment_by_date.php::By Period";
//$item2[] = "payment_by_customer.php::By Customer";

$item3[] = "summary_by_item.php::By Item";
//$item3[] = "summary_by_customer.php::By Customer";
$item3[] = "summary_by_area.php::By Area";
$item3[] = "summary_by_channel.php::By Dept";
$item3[] = "summary_by_ratio_sales.php::By Ratio Sales";
$item3[] = "summary_by_account_receivable.php::By Acc Receivable";

$item4[] = "summary_monthly_sales_by_item.php::Sales - Qty";
$item4[] = "summary_monthly_wh_by_item.php::Wh - Qty<br /><br />";
$item4[] = "summary_monthly_bill_customer_by_amount.php::Bill - Cus, amount";
$item4[] = "summary_monthly_bill_supplier_by_amount.php::Bill - Spl, amount";

//$item5[] = "daily_delivery_by_item_columned.php::By Item";

//$item6[] = "list_deposit.php::By Customer";

//------------------------------------------------------------------

if(isset($cat)) {
	echo "          <td width=\"14%\" height=\"480\" valign=\"top\" style=\"padding:5\" bgcolor=\"#F0F5F6\"  style=\"border-right-width:1px; border-right-style:solid; border-right-color:#CCC\">\n";
	require_once APP_DIR . "_include/tpl_leftMenu.php";
	echo "          </td>\n";
}
?>