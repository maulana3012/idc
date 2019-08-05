<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim
*
*
*/
//SET WHERE PARAMETER
$dept['A']	= 'Apotik Team Sales Data by Customer';
$dept['D']	= 'Dealer Team Sales Data by Customer';
$dept['H']	= 'Hospital Team Sales Data by Customer';
$dept['M']	= 'Marketing Team Sales Data by Customer';
$dept['P']	= 'Pharmaceutical Team Sales Data by Customer';
$dept['T']	= 'Tender Team Sales Data by Customer';

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

include "rpt_summary_by_customer_print_1.php";
include "rpt_summary_by_customer_print_2.php";
include "rpt_summary_by_customer_print_3.php";
?>
