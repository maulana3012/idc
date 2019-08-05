<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim
*
* $id$
*/
//Variable Color
$display_css['billing'] 	= "color:#333333";
$display_css['return'] 		= "color:#EE5811";
$display_css['uncounted'] 	= "color:#9D9DA1";

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

include "rpt_payment_by_customer_print_1.php";
include "rpt_payment_by_customer_print_2.php";
include "rpt_payment_by_customer_print_3.php";
?>