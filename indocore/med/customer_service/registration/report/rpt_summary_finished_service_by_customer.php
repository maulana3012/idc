<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim
*
* $_search_date : Inquire Date
*/
//Variable Color (make same with the javascript)
$display_css['before_due'] 	= "color:black";
$display_css['over_due'] 	= "background-color:lightyellow; color:red";
$display_css['paid'] 		= "background-color:lightgrey; color:black";
$display_css['before_due_chr']	= "color:purple";
$display_css['over_due_chr']	= "background-color:lightyellow;color:purple";
$display_css['paid_chr']		= "background-color:lightgrey;color:purple";

//SET WHERE PARAMETER
if ($some_date != "") {
	$tmp[] = "sg_receive_date = DATE '$some_date'";
} else {
	$tmp[] = "sg_receive_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
}

if($_cus_code != '') {
	$tmp[] = "cus_code = '$_cus_code'";
}

$tmp[] = "sgit_finishing_date is not null";

$strWhere = implode(" AND ", $tmp);

$sql = "
SELECT 
 cus_code,
 cus_full_name,
 sg_code,
 ".ZKP_SQL."_getServiceBill(sg_code,null,1) AS bill_no,
 ".ZKP_SQL."_getServiceBill(sg_code,null,2) AS service_cost,
 sgit_idx AS idx,
 it_code AS it_code,
 sgit_model_no,
 sgit_serial_number,
 sgit_tech_analyze,
 to_char(sgit_incoming_date, 'dd-Mon-YY') AS inc_date,
 to_char(sgit_finishing_date, 'dd-Mon-YY') AS finish_date,
 to_char(sgit_delivery_date, 'dd-Mon-YY') AS deli_date,
 sgit_finishing_date-sgit_incoming_date AS total_time,
 sgit_service_action_chk AS action,
 sgit_replacement_product AS replace_product,
 sgit_replacement_part AS replace_part,
 ".ZKP_SQL."_getServiceBill(sg_code,null,3) AS status1,
 ".ZKP_SQL."_getServiceBill(sg_code,sgit_idx,3) AS status2,
 'revise_registration.php?_code='||sg_code AS go_page1,
 '../service/revise_service.php?_code='||".ZKP_SQL."_getServiceBill(sg_code,null,1) AS go_page2
FROM
 ".ZKP_SQL."_tb_customer AS cus
 JOIN ".ZKP_SQL."_tb_service_reg AS sg ON sg_cus_to = cus_code
 JOIN ".ZKP_SQL."_tb_service_reg_item AS sgit USING(sg_code) 
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE " . $strWhere . "
ORDER BY sg_code, it_code";

// raw data
$rd = array();
$rdIdx = 0;
$cache = array("","",);
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {
	$rd[] = array(
		$col['cus_code'],			//0
		$col['cus_full_name'],		//1
		$col['sg_code'],			//2
		$col['bill_no'],			//3
		$col['service_cost'],		//4
		$col['idx'],				//5
		$col['it_code'],			//6
		$col['sgit_model_no'],		//7
		$col['sgit_serial_number'],	//8
		$col['sgit_tech_analyze'],	//9
		$col['inc_date'],			//10
		$col['finish_date'],		//11
		$col['deli_date'],			//12
		$col['total_time'],			//13
		$col['action'],				//14
		$col['replace_product'],	//15
		$col['replace_part'],		//16
		$col['status1'],			//17
		$col['status2'],			//18
		$col['go_page1'],			//19
		$col['go_page2']			//20
	);

	//1st grouping
	if($cache[0] != $col['sg_code']) {
		$cache[0] = $col['sg_code'];
		$group0[$col['sg_code']] = array();
	}

	if($cache[1] != $col['idx']) {
		$cache[1] = $col['idx'];
	}

	$group0[$col['sg_code']][$col['idx']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$ggTotal	= 0;

print <<<END
<table width="120%" class="table_f">
	<tr>
		<th width="7%" rowspan="2">REG. NO</th>
		<th width="12%" rowspan="2">CUSTOMER</th>
		<th width="7%" rowspan="2">BILL NO.</th>
		<th width="7%" rowspan="2">SERVICE<br />COST</th>
		<th width="15%" rowspan="2">MODEL NO.</th>
		<th rowspan="2">SERIAL NO.</th>
		<th width="15%" rowspan="2">TECHNICAL ANALYSIS</th>
		<th width="12%" rowspan="2">RCV<br />DATE</th>
		<th width="12%" rowspan="2">FINISH<br />DATE</th>
		<th width="3%" rowspan="2">TOTAL<br />DAY(S)</th>
		<th width="12%" rowspan="2">DELI<br />DATE</th>
		<th width="10%" colspan="5">ACTION</th>
		<th width="5%" rowspan="2">REPLACE<br />PRODUCT</th>
		<th width="5%" rowspan="2">REPLACE<br />S/P</th>
	</tr>
	<tr>
		<th width="2%">S</th>
		<th width="2%">C</th>
		<th width="2%">RT</th>
		<th width="2%">RP</th>
		<th width="2%">RS</th>
	</tr>\n
END;

foreach ($group0 as $total1 => $group1) {
	$rowSpan = 0;
	array_walk_recursive($group1, 'getRowSpan');
	$rowSpan = $rowSpan+1;

	print "<tr>\n";
	cell_link("<b>{$rd[$rdIdx][2]}</b>", ' style="'.$display_css[$rd[$rdIdx][17]].'" align="center" valign="top" rowspan="'.$rowSpan.'"', ' href="'.$rd[$rdIdx][19].'" target="_parent"');
	cell($rd[$rdIdx][1], ' style="'.$display_css[$rd[$rdIdx][17]].'" valign="top" rowspan="'.$rowSpan.'"');
	cell_link("<b>{$rd[$rdIdx][3]}</b>", ' style="'.$display_css[$rd[$rdIdx][17]].'" align="center" valign="top" rowspan="'.$rowSpan.'"', ' href="'.$rd[$rdIdx][20].'" target="_parent"');
	cell(number_format($rd[$rdIdx][4],0,'','.'), ' style="'.$display_css[$rd[$rdIdx][17]].'" valign="top" align="right" rowspan="'.$rowSpan.'"');

	$gTotal = 0;
	$print_tr_1 = 0;
	foreach ($group1 as $total2) {
		if($print_tr_1++ > 0) print "<tr>\n";
		cell($rd[$rdIdx][7], ' style="'.$display_css[$rd[$rdIdx][18]].'"');
		cell($rd[$rdIdx][8], ' style="'.$display_css[$rd[$rdIdx][18]].'"');
		cell($rd[$rdIdx][9], ' style="'.$display_css[$rd[$rdIdx][18]].'"');
		cell($rd[$rdIdx][10], ' style="'.$display_css[$rd[$rdIdx][18]].'" align="center"');
		cell($rd[$rdIdx][11], ' style="'.$display_css[$rd[$rdIdx][18]].'" align="center"');
		cell($rd[$rdIdx][13], ' style="'.$display_css[$rd[$rdIdx][18]].'" align="center"');
		cell($rd[$rdIdx][12], ' style="'.$display_css[$rd[$rdIdx][18]].'" align="center"');
		cell(($rd[$rdIdx][14] & 1) ? '<img src="../../_images/icon/bullet.gif">':'', ' style="'.$display_css[$rd[$rdIdx][18]].'" align="center"');
		cell(($rd[$rdIdx][14] & 4) ? '<img src="../../_images/icon/bullet.gif">':'', ' style="'.$display_css[$rd[$rdIdx][18]].'" align="center"');
		cell(($rd[$rdIdx][14] & 2) ? '<img src="../../_images/icon/bullet.gif">':'', ' style="'.$display_css[$rd[$rdIdx][18]].'" align="center"');
		cell(($rd[$rdIdx][14] & 8) ? '<img src="../../_images/icon/bullet.gif">':'', ' style="'.$display_css[$rd[$rdIdx][18]].'" align="center"');
		cell(($rd[$rdIdx][14] & 16) ? '<img src="../../_images/icon/bullet.gif">':'', ' style="'.$display_css[$rd[$rdIdx][18]].'" align="center"');
		cell($rd[$rdIdx][15], ' style="'.$display_css[$rd[$rdIdx][18]].'"');
		cell($rd[$rdIdx][16], ' style="'.$display_css[$rd[$rdIdx][18]].'"');
		print "</tr>\n";

		$sg_no   = $rd[$rdIdx][2];
		$status1 = $rd[$rdIdx][17];
		$gTotal++;
		$rdIdx++;
	}

	print "<tr>\n";
	cell("Reg no. $sg_no : ".number_format($gTotal), ' style="'.$display_css[$status1].'" colspan="14" align="right" style="color:darkblue;"');
	print "</tr>\n";

	$ggTotal += $gTotal;
}

print "<tr height=\"20px\">\n";
cell("<b>TOTAL : </b>".number_format($ggTotal), ' colspan="18" align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br />\n";
?>