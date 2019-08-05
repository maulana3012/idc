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
//$cat = array("NEW BILLING", "BILLING SUMMARY", "DEBIT", "PAYMENT", "DEPOSIT");
$cat = array("EVENT");
//------------------------------------------------------------------

//------------------------------------------------------------------
// Menu Item ("Url::Pagename")
//  variable name is ${"item".Index of $cat array}
//------------------------------------------------------------------
$item0[] = "input_event_step_1.php::Masukkan event";
$item0[] = "summary_by_personal_info.php::By Personal Info";
$item0[] = "summary_by_result_test.php::By Test Result";
//$item0[] = "summary_by_percentage.php::By Percentage";
//------------------------------------------------------------------

if(isset($cat)) {
	echo "          <td width=\"14%\" height=\"480\" valign=\"top\" style=\"padding:5\" bgcolor=\"#F0F5F6\"  style=\"border-right-width:1px; border-right-style:solid; border-right-color:#CCC\">\n";
	require_once APP_DIR . "_include/tpl_leftMenu.php";
	echo "          </td>\n";
}
?>