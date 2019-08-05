<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @author : daesung kim
*
* $Id: _access_local.php,v 1.1 2008/04/28 06:52:12 neki Exp $

/*
100 | General Manager
101 | Apotik Admin
102 | Dealer Admin
103 | Hospital Admin
104 | Marketing  Admin
105 | Pharmaceutical Admin
106 | Tender Admin
107 | Customer Service Admin
111 | Accounting Admin
112 | Purchasing Admin
113 | Warehouse Admin
114 | Demo Stock Admin
115 | Event Admin
116 | Warranty Admin
130 | General Manager
131 | Apotik Admin
132 | Dealer Admin
133 | Hospital Admin
134 | Marketing  Admin
135 | General Admin
136 | Management Admin
*/

//CHECK ACCOUNT PERMISSION
$grade = array(100,101,102,103,104,105,106,107,111,112,113,114,115,116,130,131,132,133,134,135,136);
$grMember = array(
	'admin'				=>array(1,100,15),
	'report'			=>array(1,100,15),
	'report_all'		=>array(1,100,15),
	'apotik'			=>array(2,101,0),
	'dealer'			=>array(3,102,0),
	'hospital'			=>array(4,103,0),
	'marketing'			=>array(5,104,0),
	'pharmaceutical'	=>array(6,105,0),
	'tender'			=>array(7,106,0),
	'customer_service'	=>array(8,107,0),
	'accounting'		=>array(9,111,0),
	'purchasing'		=>array(10,112,0),
	'warehouse'			=>array(11,113,0),
	'demo'				=>array(12,114,0),
	'event'				=>array(13,115,0),
	'product'			=>array(14,116,0),
	'letter_report'		=>array(15,130,0),
	'letter_apotik'		=>array(15,131,0),
	'letter_dealer'		=>array(15,132,0),
	'letter_hospital'	=>array(15,133,0),
	'letter_marketing'	=>array(15,134,0),
	'letter_general'	=>array(15,135,0),
	'letter_management'	=>array(15,136,0)
);

foreach($grMember as $key => $val) {
	if($currentDept == $key) $grMember[$key][2] = 15;
}

foreach($grMember as $key => $val) {
	$GROUP_PERMISSION[$val[1]] = $val[2];
}

require_once LIB_DIR ."devel_access_main.php";
?>