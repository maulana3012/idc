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

if ($some_date != "") {
	$tmp[]	= "red_return_date = DATE '$some_date'";
} else {
	$tmp[]	= "red_return_date BETWEEN DATE '$period_from' AND '$period_to'";
}

if($_dept != 'all') {
	$tmp[]	= "red_dept = '$_dept'";
}

if($_status == 'uncfm') {
	$tmp[]	= "red_cfm_marketing_timestamp IS NULL";
} else if($_status == 'cfm') {
	$tmp[]	= "red_cfm_marketing_timestamp IS NOT NULL";
}

$strWhere = implode(" AND ", $tmp);

$sql = "
SELECT 
  red_code,
  use_code,
  red_return_by,
  to_char(red_return_date, 'dd-Mon-YYYY') AS return_date,
  to_char(red_cfm_marketing_timestamp, 'dd-Mon-YYYY') AS confirm_date,
  cus_code,
  cus_full_name,
  it_code,
  it_model_no,
  rdit_qty,
  'confirm_return.php?_code=' || red_code AS go_page_return,
  'confirm_request.php?_code=' || use_code AS go_page_request
FROM
 ".ZKP_SQL."_tb_customer AS c
 JOIN ".ZKP_SQL."_tb_return_demo AS a ON red_cus_to = cus_code
 JOIN ".ZKP_SQL."_tb_return_demo_item AS b USING(red_code)
 JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
WHERE " . $strWhere ."
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
		$col['confirm_date'],		//4
		$col['cus_code'],			//5
		$col['cus_full_name'],		//6
		$col['it_code'],			//7
		$col['it_model_no'], 		//8
		$col['rdit_qty'], 			//9
		$col['go_page_return'],		//10
		$col['go_page_request']		//11
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
<table width="100%" class="table_f">
	<tr>
		<th width="15%" colspan="2">RETURN NO.</th>
		<th width="10%">RETURN DATE</th>
		<th width="10%">CONFIRM DATE</th>
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
		' href="'.$rd[$rdIdx][10].'"');													//Document no
	cell($rd[$rdIdx][3], ' valign="top" align="center" rowspan="'.$rowSpan.'"');		//Document date
	cell($rd[$rdIdx][4], ' valign="top" align="center" rowspan="'.$rowSpan.'"');		//Confirm date
	cell("[".trim($rd[$rdIdx][5])."] ".$rd[$rdIdx][6], ' valign="top" rowspan="'.$rowSpan.'"');		//Document wh confirm
	cell_link("<span class=\"bar\">".$rd[$rdIdx][1]."</span>", ' align="center" valign="" rowspan="'.$rowSpan.'"',
		' href="'.$rd[$rdIdx][11].'"');													//Request ref	

	$total		= 0;
	$print_tr_1 = 0;
	//ITEM
	foreach($group1 as $group2) {
		if($print_tr_1++ > 0) print "<tr>\n";

		cell("[".trim($rd[$rdIdx][7])."] ".$rd[$rdIdx][8], ' valign="top"');	//Model No
		cell(number_format($rd[$rdIdx][9],2), ' align="right" valign="top"');	//qty
		print "</tr>\n";

		$total += $rd[$rdIdx][9];
		$rdIdx++;
	}

	print "<tr>\n";
	cell("TOTAL $total1", ' colspan="7" align="right" style="color:darkblue"');
	cell(number_format($total,2), ' align="right" style="color:darkblue"');
	print "</tr>\n";

	$ggTotal += $total;
}
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="7" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTotal,2), ' align="right" style="color:brown; background-color:lightyellow"');
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