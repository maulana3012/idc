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
$tmp_req	= array();
$tmp_turn	= array();

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp_req[] = "d.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_turn[] = "d.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if ($some_date != "") {
	$tmp_req[]	= "req_issued_date = DATE '$some_date'";
	$tmp_turn[]	= "inm_doc_date = DATE '$some_date'";
} else {
	$tmp_req[]	= "req_issued_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_turn[]	= "inm_doc_date BETWEEN DATE '$period_from' AND '$period_to'";
}

if($_source == "1") {
	$tmp_turn[]	= "inm_idx is null";
} else if($_source == "2") {
	$tmp_req[]	= "req_code is null";
}

if($_status == "0") {
	$tmp_req[]	= "req_cfm_wh_delivery_timestamp IS NULL AND req_cfm_marketing_timestamp IS NULL";
	$tmp_turn[]	= "inm_cfm_wh_delivery_timestamp IS NULL AND inm_cfm_marketing_timestamp IS NULL";
} else if($_status == "1") {
	$tmp_req[]	= "req_cfm_wh_delivery_timestamp IS NOT NULL AND req_cfm_marketing_timestamp IS NULL";
	$tmp_turn[]	= "inm_cfm_wh_delivery_timestamp IS NOT NULL AND inm_cfm_marketing_timestamp IS NULL";
} else if($_status == "2") {
	$tmp_req[]	= "req_cfm_wh_delivery_timestamp IS NOT NULL AND req_cfm_marketing_timestamp IS NOT NULL";
	$tmp_turn[]	= "inm_cfm_wh_delivery_timestamp IS NOT NULL AND inm_cfm_marketing_timestamp IS NOT NULL";
}

$tmp_turn[] = "init_demo_qty > 0";

$strWhereRequest	= implode(" AND ", $tmp_req);
$strWhereReturn		= implode(" AND ", $tmp_turn);

$sql = "
	SELECT 
	  req_code AS doc_no,
	  req_issued_by AS doc_by,
	  req_issued_date AS doc_date,
	  to_char(req_issued_date,'dd-Mon-yy') AS issued_date,
	  to_char(req_cfm_wh_delivery_timestamp,'dd-Mon-yy') AS wh_cfm_date,
	  to_char(req_cfm_marketing_timestamp,'dd-Mon-yy') AS received_date,
	  it_code,
	  it_model_no,
	  rqit_qty AS qty,
	  rqit_remark AS remark,
	  'revise_request.php?_code=' || req_code AS go_page
	FROM
	 ".ZKP_SQL."_tb_request AS a
	 JOIN ".ZKP_SQL."_tb_request_item AS b USING(req_code)
	 JOIN ".ZKP_SQL."_tb_item AS c USING(it_code)
	 JOIN ".ZKP_SQL."_tb_item_cat AS d USING(icat_midx) 
	WHERE " . $strWhereRequest ."
UNION
	SELECT 
	  inm_doc_no AS doc_no,
	  inm_issued_by AS doc_by,
	  inm_doc_date AS doc_date,
	  to_char(inm_doc_date,'dd-Mon-yy') AS issued_date,
	  to_char(inm_cfm_wh_delivery_timestamp,'dd-Mon-yy') AS wh_cfm_date,
	  to_char(inm_cfm_marketing_timestamp,'dd-Mon-yy') AS received_date,
	  it_code,
	  it_model_no,
	  init_demo_qty AS qty,
	  null AS remark,
	  'detail_return.php?_code=' || inc_idx AS go_page
	FROM
	 ".ZKP_SQL."_tb_incoming_marketing AS a
	 JOIN ".ZKP_SQL."_tb_incoming_item AS b USING(inc_idx)
	 JOIN ".ZKP_SQL."_tb_item AS c USING(it_code)
	 JOIN ".ZKP_SQL."_tb_item_cat AS d USING(icat_midx) 
	WHERE " . $strWhereReturn ."
ORDER BY doc_date, doc_no, it_code";

echo "<pre>";
//echo $sql;
echo "</pre>";

// raw data
$rd		= array();
$rdIdx	= 0;
$i		= 0;
$cache	= array("","");
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['doc_no'],				//0
		$col['doc_by'],				//1
		$col['issued_date'],		//2
		$col['wh_cfm_date'],		//3
		$col['received_date'],		//4
		$col['it_code'],			//5
		$col['it_model_no'], 		//6
		$col['qty'], 				//7
		$col['remark'],				//8
		$col['go_page']				//9	
	);

	//1st grouping
	if($cache[0] != $col['doc_no']) {
		$cache[0] = $col['doc_no'];
		$group0[$col['doc_no']] = array();
	}

	if($cache[1] != $col['it_code']) {
		$cache[1] = $col['it_code'];
	}

	$group0[$col['doc_no']][$col['it_code']] = 1;
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
		<th width="15%">DOCUMENT NO.</th>
		<th width="10%">DOCUMENT<br />DATE</th>
		<th width="10%">WH CFM<br />DATE</th>
		<th width="10%">RECEIVED<br />DATE</th>
		<th>MODEL NO</th>
		<th width="8%">QTY<br />(Pcs)</th>
		<th width="15%">REMARK</th>
	</tr>\n
END;

foreach ($group0 as $total1 => $group1) {
	$rowSpan = 0;
	array_walk_recursive($group1, 'getRowSpan');

	print "<tr>\n";
	cell_link('<span class="bar">'.$rd[$rdIdx][0].'</span>', ' align="center" valign="top" rowspan="'.$rowSpan.'"',
		' href="'.$rd[$rdIdx][9].'"');													//Document no
	cell($rd[$rdIdx][2], ' valign="top" align="center" rowspan="'.$rowSpan.'"');		//Document date
	cell($rd[$rdIdx][3], ' valign="top" align="center" rowspan="'.$rowSpan.'"');		//Document wh confirm
	cell($rd[$rdIdx][4], ' valign="top" align="center" rowspan="'.$rowSpan.'"');		//Document received date

	$total		= 0;
	$print_tr_1 = 0;
	//ITEM
	foreach($group1 as $group2) {
		if($print_tr_1++ > 0) print "<tr>\n";
		//$bal_qty		= $rd[$rdIdx][9]-$rd[$rdIdx][10];

		cell("[".trim($rd[$rdIdx][5])."] ".$rd[$rdIdx][6]);						//Model No
		cell(number_format($rd[$rdIdx][7]), ' align="right"');					//qty
		cell($rd[$rdIdx][8]);													//remark
		print "</tr>\n";

		$total += $rd[$rdIdx][7];
		$rdIdx++;
	}

	print "<tr>\n";
	cell("TOTAL $total1", ' colspan="5" align="right" style="color:darkblue"');
	cell(number_format($total), ' align="right" style="color:darkblue"');
	cell('');
	print "</tr>\n";

	$ggTotal += $total;
}
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTotal), ' align="right" style="color:brown; background-color:lightyellow"');
cell('', ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br />\n";
?>