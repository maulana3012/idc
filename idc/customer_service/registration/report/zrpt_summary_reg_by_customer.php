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

//SET WHERE PARAMETER
if ($some_date != "") {
	$tmp[] = "sg_receive_date = DATE '$some_date'";
} else {
	$tmp[] = "sg_receive_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
}

if($_cus_code != '') {
	$tmp[] = "cus_code = '$_cus_code'";
}

if($_status != 'all') {
	$tmp[] = "sgit_status = $_status";
}

$strWhere = implode(" AND ", $tmp);

$sql = "
SELECT 
 cus_code,
 cus_full_name,
 sg_code,
 to_char(sg_receive_date, 'dd-Mon-YY') AS reg_date,
 sgit_idx AS idx,
 it_code AS it_code,
 sgit_model_no,
 sgit_serial_number,
 sgit_cus_complain,
 sgit_tech_analyze,
 CASE
	WHEN sgit_is_guarantee=1 THEN 'Y'
	WHEN sgit_is_guarantee=0 THEN 'N'
 END AS sgit_is_guarantee,
 CASE
	WHEN sgit_status=0 THEN 'Incoming'
	WHEN sgit_status=1 THEN 'Finished'
	WHEN sgit_status=2 THEN 'Delivered'
 END AS sgit_status,
 ".ZKP_SQL."_getCurrentDateStatus(sgit_idx, sgit_status) AS sgit_status_date,
 1 AS sgit_qty,
 'revise_registration.php?_code='||sg_code AS go_page
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
		$col['reg_date'],			//3
		$col['idx'],				//4
		$col['it_code'],			//5
		$col['sgit_model_no'],		//6
		$col['sgit_serial_number'],	//7
		$col['sgit_cus_complain'],	//8
		$col['sgit_tech_analyze'],	//9
		$col['sgit_is_guarantee'],	//10
		$col['sgit_status'],		//11
		$col['sgit_status_date'],	//12
		$col['sgit_qty'],			//13
		$col['go_page']				//14
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
<table width="100%" class="table_f">
	<tr>
		<th width="9%">REG. NO</th>
		<th width="70px">REG DATE</th>
		<th width="12%">CUSTOMER</th>
		<th>MODEL NO.</th>
		<th>SERIAL NO.</th>
		<th width="15%">CUSTOMER COMPLAIN</th>
		<th width="15%">TECHNICAL ANALYSIS</th>
		<th width="3%">WARR</th>
		<th width="10%">LAST STATUS</th>
		<th width="70px">STATUS DATE</th>
		<th width="3%">QTY</th>
	</tr>\n
END;

foreach ($group0 as $total1 => $group1) {
	$rowSpan = 0;
	array_walk_recursive($group1, 'getRowSpan');
	$rowSpan = $rowSpan+1;

	print "<tr>\n";
	cell_link("<b>{$rd[$rdIdx][2]}</b>", ' align="center" valign="top" rowspan="'.$rowSpan.'"', ' href="'.$rd[$rdIdx][14].'"');
	cell($rd[$rdIdx][3], ' align="center" valign="top" rowspan="'.$rowSpan.'"');
	cell($rd[$rdIdx][1], ' valign="top" rowspan="'.$rowSpan.'"');

	$gTotal = 0;
	$print_tr_1 = 0;
	foreach ($group1 as $total2) {
		if($print_tr_1++ > 0) print "<tr>\n";
		cell($rd[$rdIdx][6]);
		cell($rd[$rdIdx][7]);
		cell($rd[$rdIdx][8]);
		cell($rd[$rdIdx][9]);
		cell($rd[$rdIdx][10], ' align="center"');
		cell($rd[$rdIdx][11]);
		cell($rd[$rdIdx][12], ' align="center"');
		cell($rd[$rdIdx][13], ' align="right"');
		print "</tr>\n";

		$sg_no = $rd[$rdIdx][2];
		$gTotal++;
		$rdIdx++;
	}

	print "<tr>\n";
	cell("Reg no. $sg_no", ' colspan="7" align="right" style="color:darkblue;"');
	cell(number_format($gTotal), ' align="right" style="color:darkblue;"');
	print "</tr>\n";

	$ggTotal += $gTotal;
}

print "<tr height=\"20px\">\n";
cell("<b>TOTAL</b>", ' colspan="10" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTotal), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br />\n";
?>