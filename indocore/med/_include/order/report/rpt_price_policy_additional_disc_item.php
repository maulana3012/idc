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

$sql = "
SELECT it_code, icat_midx,
it_model_no,
cus_code,
cus_full_name,
to_char(ap_date_from, 'dd-Mon-yyyy') AS ap_date_from,
to_char(ap_date_to, 'dd-Mon-yyyy') AS ap_date_to,
ap_disc_pct
FROM ".ZKP_SQL."_tb_customer AS cus JOIN ".ZKP_SQL."_tb_apotik_policy AS ap USING (cus_code) JOIN ".ZKP_SQL."_tb_apotik_price AS ait USING(ap_idx) JOIN ".ZKP_SQL."_tb_item AS it USING(it_code) WHERE " . implode(" AND ", $strWhere) . " ORDER BY it_code, cus_code, ap_idx DESC";

$dup = "";
$rowSpan = array();
$rowSpanIdx = -1; //it will start 0
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	//get category path from current icat_midx.
	$path = executeSP(ZKP_SQL."_getCategoryPath", $col['icat_midx']);
	eval(html_entity_decode($path[0]));	
	$path = array_reverse($path);
	$cat_path = $path[1][4]." > ".$path[2][4]." > ".$path[3][4];

	$td[] = array(
		$cat_path,
		cut_string($col['it_model_no'], 20),
		$col['cus_code'],		
		cut_string($col['cus_full_name'], 32),
		$col['ap_date_from'],
		$col['ap_date_to'],
		$col['ap_disc_pct']
	);

	if($dup == $col['it_code']) {
		$rowSpan[$rowSpanIdx] += 1;
	} else {
		$dup = $col['it_code'];
		$rowSpanIdx += 1; // check how many item now duplicate
		$rowSpan[$rowSpanIdx] = 1; //rowspan = 1
	}
	
}

//Print Header
print <<<END
<table width="100%" class="table_c">
	<tr>
		<th width="25%">CATEGORY</th>
		<th width="12%">MODEL NO</th>
		<th width="6%">C/ CODE</th>
		<th>CUSTOMER NAME</th>
		<th width="10%">FROM</th>
		<th width="10%">TO</th>
		<th width="8%">ADD/<br /> DISC %</th>
	</tr>\n
END;

$rowIdx = 0;
$numItem = count($rowSpan);
for ($i = 0; $i < $numItem; $i++) {
	print "<tr>\n";	
	cell($td[$rowIdx][0], ' valign="top" rowspan="'.$rowSpan[$i].'"');
	cell($td[$rowIdx][1], ' valign="top" rowspan="'.$rowSpan[$i].'"');

	for ($o = 0; $o < $rowSpan[$i]; $o++) {
		if($o > 0) print "<tr>\n";
		cell($td[$rowIdx][2]);
		cell($td[$rowIdx][3]);
		cell($td[$rowIdx][4]);
		cell($td[$rowIdx][5]);
		cell(number_format((double)$td[$rowIdx][6],2), ' align="right"');
		print "</tr>\n";
		$rowIdx++;
	}

	print "</tr>\n";

}

print "</table><br>\n";
?>
