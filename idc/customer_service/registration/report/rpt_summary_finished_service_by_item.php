<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim

*
* $_po_date : Inquire Date
*
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
  icat_midx AS icat_midx,
  icat_pidx AS icat_pidx,
  sgit_idx AS idx,
  it_code AS it_code,
  it_model_no AS it_model_no,
  sg_code,
  cus_code,
  cus_full_name,
  sgit_serial_number,
  sgit_tech_analyze,
  to_char(sgit_incoming_date, 'dd-Mon-YY') AS inc_date,
  to_char(sgit_finishing_date, 'dd-Mon-YY') AS finish_date,
  to_char(sgit_delivery_date, 'dd-Mon-YY') AS deli_date,
  sgit_finishing_date-sgit_incoming_date AS total_time,
  sgit_service_action_chk AS action,
  sgit_replacement_product AS replace_product,
  sgit_replacement_part AS replace_part,
  ".ZKP_SQL."_getServiceBill(sg_code,sgit_idx,3) AS status,
  'revise_registration.php?_code='||sg_code AS go_page
FROM
 ".ZKP_SQL."_tb_customer AS cus
 JOIN ".ZKP_SQL."_tb_service_reg AS sg ON sg_cus_to = cus_code
 JOIN ".ZKP_SQL."_tb_service_reg_item AS sgit USING(sg_code) 
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx) 
WHERE " . $strWhere . "
ORDER BY icat_pidx, icat_midx, it_code, sg_code";

// raw data
$rd = array();
$rdIdx = 0;
$cache = array("","","");
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {
	$rd[] = array(
		$col['icat_midx'],			//0
		$col['it_code'],			//1
		$col['it_model_no'],		//2
		$col['idx'],				//3
		$col['sg_code'],			//4
		$col['cus_code'],			//5
		$col['cus_full_name'],		//6
		$col['sgit_serial_number'],	//7
		$col['sgit_tech_analyze'],	//8
		$col['inc_date'],			//9
		$col['finish_date'],		//10
		$col['deli_date'],			//11
		$col['total_time'],			//12
		$col['action'],				//13
		$col['replace_product'],	//14
		$col['replace_part'],		//15
		$col['status'],				//16
		$col['go_page']				//17
	);

	//1st grouping
	if($cache[0] != $col['icat_midx']) {
		$cache[0] = $col['icat_midx'];
		$group0[$col['icat_midx']] = array();
	}

	if($cache[1] != $col['it_code']) {
		$cache[1] = $col['it_code'];
		$group0[$col['icat_midx']][$col['it_code']] = array();
	}

	if($cache[2] != $col['idx']) {
		$cache[2] = $col['idx'];
	}

	$group0[$col['icat_midx']][$col['it_code']][$col['idx']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$ggTotal = 0;

//GROUP
foreach ($group0 as $total1 => $group1) {

	//get category path from current icat_midx.
	$path = executeSP(ZKP_SQL."_getCategoryPath", $rd[$rdIdx][0]);
	eval(html_entity_decode($path[0]));	
	$path = array_reverse($path);
	$total1 = $path[1][4]." > ".$path[2][4]." > ".$path[3][4] ;

	echo "<span class=\"comment\"><b> CATEGORY: ". $total1. "</b></span>\n";
	print <<<END
	<table width="110%" class="table_f">
		<tr>
			<th width="120px" rowspan="2">MODEL NO.</th>
			<th width="7%" rowspan="2">REG. NO</th>
			<th width="15%" rowspan="2">CUSTOMER</th>
			<th rowspan="2">SERIAL NO.</th>
			<th width="15%" rowspan="2">TECHNICAL ANALYSIS</th>
			<th width="85px" rowspan="2">RCV<br />DATE</th>
			<th width="85px" rowspan="2">FINISH<br />DATE</th>
			<th width="3%" rowspan="2">TOTAL<br />DAY(S)</th>
			<th width="85px" rowspan="2">DELI<br />DATE</th>
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
	$gTotal = 0;
	$print_tr_1 = 0;
	print "<tr>\n";
	//MODEL
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan = $rowSpan+1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".trim($rd[$rdIdx][1])."]<br/>".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');

		$total = 0;
		$print_tr_2 = 0;
		//DO
		foreach($group2 as $total3 => $group3) {
			if($print_tr_2++ > 0) print "<tr>\n";
			cell_link("<b>".$rd[$rdIdx][4]."</b>", ' style="'.$display_css[$rd[$rdIdx][16]].'" align="center" ', ' href="'.$rd[$rdIdx][17].'" target="_parent"');
			cell($rd[$rdIdx][6],' style="'.$display_css[$rd[$rdIdx][16]].'"');
			cell($rd[$rdIdx][7],' style="'.$display_css[$rd[$rdIdx][16]].'"');
			cell($rd[$rdIdx][8],' style="'.$display_css[$rd[$rdIdx][16]].'"');
			cell($rd[$rdIdx][9], ' style="'.$display_css[$rd[$rdIdx][16]].'" align="center"');
			cell($rd[$rdIdx][10], ' style="'.$display_css[$rd[$rdIdx][16]].'" align="center"');
			cell($rd[$rdIdx][12], ' style="'.$display_css[$rd[$rdIdx][16]].'" align="center"');
			cell($rd[$rdIdx][11], ' style="'.$display_css[$rd[$rdIdx][16]].'" align="center"');
			cell(($rd[$rdIdx][13] & 1) ? '<img src="../../_images/icon/bullet.gif">':'', ' style="'.$display_css[$rd[$rdIdx][16]].'" align="center"');
			cell(($rd[$rdIdx][13] & 4) ? '<img src="../../_images/icon/bullet.gif">':'', ' style="'.$display_css[$rd[$rdIdx][16]].'" align="center"');
			cell(($rd[$rdIdx][13] & 2) ? '<img src="../../_images/icon/bullet.gif">':'', ' style="'.$display_css[$rd[$rdIdx][16]].'" align="center"');
			cell(($rd[$rdIdx][13] & 8) ? '<img src="../../_images/icon/bullet.gif">':'', ' style="'.$display_css[$rd[$rdIdx][16]].'" align="center"');
			cell(($rd[$rdIdx][13] & 16) ? '<img src="../../_images/icon/bullet.gif">':'', ' style="'.$display_css[$rd[$rdIdx][16]].'" align="center"');
			cell($rd[$rdIdx][14],' style="'.$display_css[$rd[$rdIdx][16]].'"');
			cell($rd[$rdIdx][15],' style="'.$display_css[$rd[$rdIdx][16]].'"');
			print "</tr>\n";

			$item = $rd[$rdIdx][2];
			$total++;
			$rdIdx++;
		}

		print "<tr>\n";
		cell("[".trim($total2)."] $item : ".number_format($total), ' colspan="15" align="right" style="color:darkblue"');
		print "</tr>\n";
		$gTotal += $total;
	}

	print "<tr>\n";
	cell("<b>$total1 : </b>".number_format($gTotal), ' colspan="16" align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$ggTotal += $gTotal;
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
	<table width="110%" class="table_f">
		<tr>
			<th rowspan="2">MODEL NO.</th>
			<th width="7%" rowspan="2">REG. NO</th>
			<th width="15%" rowspan="2">CUSTOMER</th>
			<th rowspan="2">SERIAL NO.</th>
			<th width="15%" rowspan="2">TECHNICAL ANALYSIS</th>
			<th width="8%" rowspan="2">RCV<br />DATE</th>
			<th width="8%" rowspan="2">FINISH<br />DATE</th>
			<th width="3%" rowspan="2">TOTAL<br />DAY(S)</th>
			<th width="8%" rowspan="2">DELI<br />DATE</th>
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

print "<tr>\n";
cell("<b>GRAND TOTAL : </b>".number_format($ggTotal), ' colspan="16" align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>