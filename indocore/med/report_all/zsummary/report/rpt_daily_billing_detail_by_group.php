<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim
*
* $$
*/
//Variable Color
$display_css['bill_before_due'] 	= "color:#333333";
$display_css['bill_over_due'] 		= "background-color:lightyellow; color:red";
$display_css['bill_paid'] 			= "background-color:lightgrey; color:#333333";
$display_css['bill_before_due_tf']	= "color:purple";
$display_css['bill_over_due_tf']	= "background-color:lightyellow;color:purple";
$display_css['bill_paid_tf']		= "background-color:lightgrey;color:purple";
$display_css['turn_counted'] 		= "color:EE5811";
$display_css['turn_uncounted'] 		= "color:#9D9DA1";


//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

include "rpt_daily_billing_detail_by_group_print_1.php";
include "rpt_daily_billing_detail_by_group_print_2.php";
include "rpt_daily_billing_detail_by_group_print_3.php";
?>