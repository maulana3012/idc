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
$cat = array("Estimate Summary");
//------------------------------------------------------------------

$item0[] = "summary_estimate_income_by_dept.php::By Dept";
$item0[] = "summary_monthly_by_payment.php::Detail Payment";

//------------------------------------------------------------------

if(isset($cat)) {
	echo "          <td width=\"14%\" height=\"480\" valign=\"top\" style=\"padding:5\" bgcolor=\"#F0F5F6\"  style=\"border-right-width:1px; border-right-style:solid; border-right-color:#CCC\">\n";
	require_once APP_DIR . "_include/tpl_leftMenu.php";
	echo "          </td>\n";
}
?>