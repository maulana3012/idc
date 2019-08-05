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
//Variable Color
$display_css['one'] = "color:#333333;";
$display_css['two'] = "background-color:#EEEEEE;";

//SET WHERE PARAMETER
if ($_search_by == 'registration') {
	if ($some_date != "") {
		$tmp[] = "sg_receive_date = DATE '$some_date'";
	} else {
		$tmp[] = "sg_receive_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
	}
} else if ($_search_by == 'incoming') {
	if ($some_date != "") {
		$tmp[] = "sgit_incoming_date = DATE '$some_date'";
	} else {
		$tmp[] = "sgit_incoming_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
	}
} else if ($_search_by == 'incoming2') {
	if ($some_date != "") {
		$tmp[] = "sgit_incoming_date = DATE '$some_date' AND sgit_finishing_date IS NULL";
	} else {
		$tmp[] = "sgit_incoming_date BETWEEN DATE '$period_from' AND DATE '$period_to' AND sgit_finishing_date IS NULL";
	}
} else if ($_search_by == 'finished') {
	if ($some_date != "") {
		$tmp[] = "sgit_finishing_date = DATE '$some_date'";
	} else {
		$tmp[] = "sgit_finishing_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
	}
} else if ($_search_by == 'finished2') {
	if ($some_date != "") {
		$tmp[] = "sgit_finishing_date = DATE '$some_date' AND sgit_delivery_date IS NULL";
	} else {
		$tmp[] = "sgit_finishing_date BETWEEN DATE '$period_from' AND DATE '$period_to' AND sgit_delivery_date IS NULL";
	}
} else if ($_search_by == 'delivery') {
	if ($some_date != "") {
		$tmp[] = "sgit_delivery_date = DATE '$some_date'";
	} else {
		$tmp[] = "sgit_delivery_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
	}
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
  icat_midx AS icat_midx,
  icat_pidx AS icat_pidx,
  it_code AS it_code,
  it_model_no AS it_model_no,
  sg_code,
  to_char(sg_receive_date, 'dd-Mon-YY') AS reg_date,
  cus_code,
  cus_full_name,
  sgit_idx AS idx,
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
  'revise_registration.php?_code='||sg_code AS go_page,
 ".ZKP_SQL."_cekSN(sgit_serial_number) AS cek_sn
FROM
 ".ZKP_SQL."_tb_customer AS cus
 JOIN ".ZKP_SQL."_tb_service_reg AS sg ON sg_cus_to = cus_code
 JOIN ".ZKP_SQL."_tb_service_reg_item AS sgit USING(sg_code) 
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx) 
WHERE " . $strWhere . "
ORDER BY icat_pidx, icat_midx, it_code, sg_code";
//echo $sql;
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
		$col['reg_date'],			//5
		$col['cus_code'],			//6
		$col['cus_full_name'],		//7
		$col['sgit_serial_number'],	//8
		$col['sgit_cus_complain'],	//9
		$col['sgit_tech_analyze'],	//10
		$col['sgit_is_guarantee'],	//11
		$col['sgit_status'],		//12
		$col['sgit_status_date'],	//13
		$col['sgit_qty'],			//14
		$col['go_page'],			//15
		$col['cek_sn']				//16
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
	<table width="100%" class="table_f">
		<tr>
			<th>MODEL NO.</th>
			<th width="7%">REG. NO</th>
			<th width="80px">REG DATE</th>
			<th width="15%">CUSTOMER</th>
			<th>SERIAL NO.</th>
			<th width="10%">CUSTOMER COMPLAIN</th>
			<th width="15%">TECHNICAL ANALYSIS</th>
			<th width="3%">WARR</th>
			<th width="10%">LAST STATUS</th>
			<th width="80px">STATUS DATE</th>
			<th width="3%">QTY</th>
		</tr>\n
END;

	$gTotal		= 0;
	$print_tr_1 = 0;

	print "<tr>\n";
	//MODEL
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".trim($rd[$rdIdx][1])."]<br/>".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');

		$total		= 0;
		$print_tr_2 = 0;
		//DO
		foreach($group2 as $total3 => $group3) {

			$val_css = ($rd[$rdIdx][16] == 0) ?  "one" : "two";

			if($print_tr_2++ > 0) print "<tr>\n";
			cell_link("<b>".$rd[$rdIdx][4]."</b>", ' align="center" style="'.$display_css[$val_css].'" ', ' href="'.$rd[$rdIdx][15].'"');
			cell($rd[$rdIdx][5], ' align="center" style="'.$display_css[$val_css].'"');
			cell($rd[$rdIdx][7], ' style="'.$display_css[$val_css].'"');
			if($rd[$rdIdx][16] == 0) cell($rd[$rdIdx][8], ' style="'.$display_css[$val_css].'"');
			else {
				cell_link($rd[$rdIdx][8].'('.$rd[$rdIdx][16].'x)',
					' style="'.$display_css[$val_css].'"',
					" href=\"javascript:openWindow('./p_detail_registration.php?idx={$rd[$rdIdx][8]}', 600, 300);\""
				);
			}
			cell($rd[$rdIdx][9], ' style="'.$display_css[$val_css].'"');
			cell($rd[$rdIdx][10], ' style="'.$display_css[$val_css].'"');
			cell($rd[$rdIdx][11], ' align="center" style="'.$display_css[$val_css].'"');
			cell($rd[$rdIdx][12], ' style="'.$display_css[$val_css].'"');
			cell($rd[$rdIdx][13], ' align="center" style="'.$display_css[$val_css].'"');
			cell(number_format($rd[$rdIdx][14]), ' align="right" style="'.$display_css[$val_css].'"');
			print "</tr>\n";

			$item = $rd[$rdIdx][2];
			$total++;
			$rdIdx++;
		}

		print "<tr>\n";
		cell("[".trim($total2)."] $item", ' colspan="9" align="right" style="color:darkblue"');
		cell(number_format($total), ' align="right" style="color:darkblue"');
		print "</tr>\n";

		$gTotal += $total;
	}
	
	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="10" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($gTotal), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";

	print "</table><br />\n";
	
	$ggTotal += $gTotal;
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th>MODEL NO.</th>
			<th width="7%">REG. NO</th>
			<th width="8%">REG DATE</th>
			<th width="15%">CUSTOMER</th>
			<th>SERIAL NO.</th>
			<th width="15%">CUSTOMER COMPLAIN</th>
			<th width="15%">TECHNICAL ANALYSIS</th>
			<th width="3%">WARR</th>
			<th width="10%">LAST STATUS</th>
			<th width="8%">STATUS DATE</th>
			<th width="3%">QTY</th>
		</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="10" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTotal), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>