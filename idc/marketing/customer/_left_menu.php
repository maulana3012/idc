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
$cat = array("NEW", "GROUP", "CUSTOMER");
//------------------------------------------------------------------

//------------------------------------------------------------------
// Menu Item ("Url::Pagename")
//  variable name is ${"item".Index of $cat array}
//------------------------------------------------------------------
$item0[] = "input_customer.php::New Customer";
$item0[] = "input_cus_group.php::New Group";

$item1[] = "list_cus_group.php::Group List";

$item2[] = "list_customer.php?_channel=000::Medical Dealer";
$item2[] = "list_customer.php?_channel=001::Medicine Dist";
$item2[] = "list_customer.php?_channel=002::Pharmacy Chain";
$item2[] = "list_customer.php?_channel=003::Gen/ Specialty";
$item2[] = "list_customer.php?_channel=004::Pharmaceutical";
$item2[] = "list_customer.php?_channel=005::Hospital";
$item2[] = "list_customer.php?_channel=6.1::M/L Marketing";
$item2[] = "list_customer.php?_channel=6.2::Mail Order";
$item2[] = "list_customer.php?_channel=6.3::Internet Business";
$item2[] = "list_customer.php?_channel=007::Promotion&Other";
$item2[] = "list_customer.php?_channel=008::Individual";
$item2[] = "list_customer.php?_channel=009::Private use";
$item2[] = "list_customer.php?_channel=00S::Service";
//------------------------------------------------------------------

if(isset($cat)) {
	echo "          <td width=\"14%\" height=\"480\" valign=\"top\" style=\"padding:5\" bgcolor=\"#F0F5F6\"  style=\"border-right-width:1px; border-right-style:solid; border-right-color:#CCC\">\n";
	require_once APP_DIR . "_include/tpl_leftMenu.php";
	echo "          </td>\n";
}
?>