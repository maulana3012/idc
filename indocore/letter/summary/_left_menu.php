<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*
* $Id: _left_menu.php,v 1.1 2008/04/28 06:52:12 neki Exp $
*/


//------------------------------------------------------------------
// Left Menu (for Admin Page)
//   Menu Category. (Define $item as number of element
//------------------------------------------------------------------
$cat = array("Letter");
//------------------------------------------------------------------

//------------------------------------------------------------------
// Menu Item ("Url::Pagename")
//  variable name is ${"item".Index of $cat array}
//------------------------------------------------------------------
$item0[] = "list_letter_summary.php::List Letter";
//------------------------------------------------------------------

if(isset($cat)) {
	echo "          <td width=\"14%\" height=\"480\" valign=\"top\" style=\"padding:5\" bgcolor=\"#F0F5F6\"  style=\"border-right-width:1px; border-right-style:solid; border-right-color:#CCC\">\n";
	require_once APP_DIR . "_include/tpl_leftMenu.php";
	echo "          </td>\n";
}
?>