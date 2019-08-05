.<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim
*
* $$
*/

//SET WHERE PARAMETER
$tmp = array();

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if ($_cus_code != "") {
	$tmp[]	= "red_cus_to = '$_cus_code'";
}

if ($some_date != "") {
	$tmp[]	= "red_return_date = DATE '$some_date'";
} else {
	$tmp[]	= "red_return_date BETWEEN DATE '$period_from' AND '$period_to'";
}

if($_status == 'uncfm') {
	$tmp[]	= "red_cfm_marketing_timestamp IS NULL";
} else if($_status == 'cfm') {
	$tmp[]	= "red_cfm_marketing_timestamp IS NOT NULL";
}

$tmp[]	= "red_dept = '$department'";

$strWhere = implode(" AND ", $tmp);

$sql = "
SELECT 
  red_code,
  use_code,
  red_return_by,
  to_char(red_return_date, 'dd-Mon-YYYY') AS return_date,
  cus_code,
  cus_full_name,
  it_code,
  it_model_no,
  rdit_qty,
  'revise_return.php?_code=' || red_code AS go_page_return,
  'revise_request.php?_code=' || use_code AS go_page_request
FROM
 ".ZKP_SQL."_tb_customer AS c
 JOIN ".ZKP_SQL."_tb_return_demo AS a ON red_cus_to = cus_code
 JOIN ".ZKP_SQL."_tb_return_demo_item AS b USING(red_code)
 JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx) 
WHERE " . $strWhere ." AND rdit_qty>0
ORDER BY red_code,red_return_date, it_code";

// raw data
$rd		= array();
$rdIdx	= 0;
$i		= 0;
$cache	= array("","");
$group0 = array();

$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['red_code'],			//0
		$col['use_code'],			//1
		$col['red_return_by'],		//2
		$col['return_date'],		//3
		$col['cus_code'],			//4
		$col['cus_full_name'],		//5
		$col['it_code'],			//6
		$col['it_model_no'], 		//7
		$col['rdit_qty'], 			//8
		$col['go_page_return'],		//9
		$col['go_page_request']		//10
	);

	//1st grouping
	if($cache[0] != $col['red_code']) {
		$cache[0] = $col['red_code'];
		$group0[$col['red_code']] = array();
	}

	if($cache[1] != $col['it_code']) {
		$cache[1] = $col['it_code'];
	}

	$group0[$col['red_code']][$col['it_code']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$ggTotal	= 0;

//
print <<<END
<table width="100%" class="table_c">
	<tr>
		<th width="15%" colspan="2">RETURN NO.</th>
		<th width="12%">RETURN DATE</th>
		<th>CUSTOMER / EVENT</th>
		<th width="12%">REQUEST REF.</th>
		<th width="20%">MODEL NO</th>
		<th width="8%">QTY<br />(Pcs)</th>
	</tr>\n
END;

foreach ($group0 as $total1 => $group1) {
	$rowSpan = 0;
	array_walk_recursive($group1, 'getRowSpan');

	print "<tr>\n";
	cell('<input type="checkbox" name="chkRequest[]" value="'.$rd[$rdIdx][0].'">', ' valign="top" align="center" rowspan="'.$rowSpan.'"');		//Checkbox
	cell_link("<span class=\"bar\">".$rd[$rdIdx][0]."</span>", ' align="center" valign="" rowspan="'.$rowSpan.'"',
		' href="'.$rd[$rdIdx][9].'"');													//Document no
	cell($rd[$rdIdx][3], ' valign="top" align="center" rowspan="'.$rowSpan.'"');		//Document date
	cell("[".trim($rd[$rdIdx][4])."] ".$rd[$rdIdx][5], ' valign="top" rowspan="'.$rowSpan.'"');		//Document wh confirm
	cell_link("<span class=\"bar\">".$rd[$rdIdx][1]."</span>", ' align="center" valign="" rowspan="'.$rowSpan.'"',
		' href="'.$rd[$rdIdx][10].'"');													//Request ref	

	$total		= 0;
	$print_tr_1 = 0;
	//ITEM
	foreach($group1 as $group2) {
		if($print_tr_1++ > 0) print "<tr>\n";

		cell("[".trim($rd[$rdIdx][6])."] ".$rd[$rdIdx][7]);						//Model No
		cell(number_format((double)$rd[$rdIdx][8],2), ' align="right"');				//qty
		print "</tr>\n";

		$total += $rd[$rdIdx][8];
		$rdIdx++;
	}

	print "<tr>\n";
	cell("TOTAL $total1", ' colspan="6" align="right" style="color:darkblue"');
	cell(number_format((double)$total,2), ' align="right" style="color:darkblue"');
	print "</tr>\n";

	$ggTotal += $total;
}
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="6" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$ggTotal,2), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br />\n";

print <<<END
<table width="100%" class="table_layout">
	<tr>
		<td><input type="checkbox" name="chkAll" onclick="checkAll(this.checked)"><span class="comment">check all</span></td>
		<td align="right"><button name="btnSummarize" class="input_sky" onclick="summarizeRequest()">Summarize</button></td>
	</tr>
</table><br />
END;
?>