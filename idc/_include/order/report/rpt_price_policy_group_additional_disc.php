<?php
$dup = "";
$rowSpan = array();
$rowSpanIdx = -1; //it will start 0

$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$td[] = array(
		$col['ag_idx'],
		$col['cug_code'],
		$col['cug_name'],
		cut_string($col['ag_desc'], 25),
		$col['date_from'],
		$col['date_to'],
		$col['ag_basic_disc_pct'],
		$col['ag_disc_pct'],
		$col['ag_is_valid'],
		$col['ag_is_apply_all']);

	if($dup == $col['cug_code']) {
		$rowSpan[$rowSpanIdx] += 1;
	} else {
		$dup = $col['cug_code'];
		$rowSpanIdx += 1; // check how many item now duplicate
		$rowSpan[$rowSpanIdx] = 1; //rowspan = 1
	}
}

//Print Header
print <<<END
<table width="100%" class="table_c">
	<tr>
	   <th width="7%">GROUP<br>CODE</th>
	   <th>GROUP</th>
	   <th width="20%">DESC</th>
	   <th width="10%">FROM</th>
	   <th width="10%">TO</th>
	   <th width="6%">DISC%</th>
	   <th width="6%">ADD/<br>DISC%</th>
	   <th width="6%">VALID</th>
	   <th width="6%">ITEM</th>
	</tr>\n
END;

$rowIdx = 0;
$numItem = count($rowSpan);
for ($i = 0; $i < $numItem; $i++) {

	print "<tr>\n";
	cell($td[$rowIdx][1], ' valign="top" align="center" rowspan="'.$rowSpan[$i].'"');  //GROUP CODE
	cell($td[$rowIdx][2], ' valign="top" rowspan="'.$rowSpan[$i].'"');  //GROUP
	
	for ($o = 0; $o < $rowSpan[$i]; $o++) {
		if($o > 0) print "<tr>";

		cell_link("[".$td[$rowIdx][0]."] ".$td[$rowIdx][3], '', " href=detail_group_policy.php?_code=".$td[$rowIdx][0]);
 		cell($td[$rowIdx][4]); //FROM 
 		cell($td[$rowIdx][5]); //TO 
 		cell($td[$rowIdx][6], ' align="right"'); //DISC% 
 		cell($td[$rowIdx][7], ' align="right"'); //ADD/DISC% 
 		cell(($td[$rowIdx][8]=='t')?"VALID":"-", ' align="center"'); //VALID 
		
		if($td[$rowIdx][9] == 'f') {
	 		cell_link("VIEW", ' align="center"', ' href="javascript:openWindow(\'./p_group_disc_item_list.php?_idx='.$td[$rowIdx][0].'\', 700, 350)"');
		} else {
			cell("ALL", ' align="center"');
		}
		
	 	print "</tr>\n";
		$rowIdx++;
	}

}  //End Parents row 
print '</table>';
?>
