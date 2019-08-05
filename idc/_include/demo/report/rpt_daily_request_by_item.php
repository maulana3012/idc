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
//SET WHERE PARAMETER
$tmp_req	= array();
$tmp_turn	= array();

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp_req[] = "e.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_turn[] = "e.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
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
	  icat_midx,
	  icat_pidx,
	  it_code,
	  it_model_no,
	  req_code AS doc_no,
	  req_issued_by AS doc_by,
	  req_issued_date AS issued_date_ord,
	  to_char(req_issued_date,'dd-Mon-yy') AS issued_date,
	  to_char(req_cfm_wh_delivery_timestamp,'dd-Mon-yy') AS wh_cfm_date,
	  to_char(req_cfm_marketing_timestamp,'dd-Mon-yy') AS received_date,
	  rqit_qty AS qty,
	  rqit_remark AS remark,
	  'revise_request.php?_code=' || req_code AS go_page
	FROM
	 ".ZKP_SQL."_tb_request AS a
	 JOIN ".ZKP_SQL."_tb_request_item AS b USING(req_code)
	 JOIN ".ZKP_SQL."_tb_item AS c USING(it_code)
	 JOIN ".ZKP_SQL."_tb_item_cat AS e USING(icat_midx) 
	WHERE ". $strWhereRequest ."
UNION
	SELECT
	  icat_midx,
	  icat_pidx,
	  it_code,
	  it_model_no,
	  inm_doc_no AS doc_no,
	  inm_issued_by AS doc_by,
	  inm_doc_date AS issued_date_ord,
	  to_char(inm_doc_date,'dd-Mon-yy') AS issued_date,
	  to_char(inm_cfm_wh_delivery_timestamp,'dd-Mon-yy') AS wh_cfm_date,
	  to_char(inm_cfm_marketing_timestamp,'dd-Mon-yy') AS received_date,
	  init_demo_qty AS qty,
	  null AS remark,
	  'detail_return.php?_code=' || inc_idx AS go_page
	FROM
	 ".ZKP_SQL."_tb_incoming_marketing AS a
	 JOIN ".ZKP_SQL."_tb_incoming_item AS b USING(inc_idx)
	 JOIN ".ZKP_SQL."_tb_item AS c USING(it_code)
	 JOIN ".ZKP_SQL."_tb_item_cat AS e USING(icat_midx) 
	WHERE ". $strWhereReturn ."
ORDER BY icat_pidx, icat_midx, it_code, issued_date_ord, doc_no, it_code";

$rd = array();
$rdIdx = 0;

$cache = array("","","");
$group0 = array();

$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'],		//0
		$col['it_code'],		//1
		$col['it_model_no'],	//2
		$col['doc_no'],			//3
		$col['issued_date'],	//4
		$col['wh_cfm_date'],	//5
		$col['received_date'],	//6
		$col['qty'],			//7
		$col['remark'],			//8
		$col['go_page']			//9
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

	if($cache[2] != $col['doc_no']) {
		$cache[2] = $col['doc_no'];
	}

	$group0[$col['icat_midx']][$col['it_code']][$col['doc_no']] = 1;
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
			<th>MODEL NO</th>
			<th width="15%">REFERENCE NO.</th>
			<th width="10%">REFERENCE DATE</th>
			<th width="10%">WH CFM<br />DATE</th>
			<th width="10%">RECEIVE<br />DATE</th>
			<th width="8%">DT QTY<br />(Pcs)</th>
			<th width="15%">REMARK</th>
		</tr>\n
END;

	$print_tr_1 = 0;
	$gTotal		= 0;
	print "<tr>\n";
	//ITEM
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".trim($rd[$rdIdx][1])."] ".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');	//Model No

		$total		= 0;
		$print_tr_2 = 0;
		//IDX
		foreach($group2 as $total3 => $group3) {

			if($print_tr_2++ > 0) print "<tr>\n";
			cell_link('<span class="bar">'.$rd[$rdIdx][3].'</span>', ' valign="top" align="center"',
				' href="'.$rd[$rdIdx][9].'"');										//Document no
			cell($rd[$rdIdx][4], ' valign="top" align="center"');					//Document date
			cell($rd[$rdIdx][5], ' valign="top" align="center"');							//Warehouse confirm
			cell($rd[$rdIdx][6], ' valign="top" align="center"');							//Receive date
			cell(number_format($rd[$rdIdx][7],2), ' align="right"');						//qty
			cell_link($rd[$rdIdx][8], ' align="center"', ' href="'.$rd[$rdIdx][9].'"');		//remark
			print "</tr>\n";

			$item = "[".trim($rd[$rdIdx][1])."] ".$rd[$rdIdx][2];
			$total += $rd[$rdIdx][7];
			$rdIdx++;
		}
		print "<tr>\n";
		cell("$item", ' colspan="4" align="right" style="color:darkblue"');
		cell(number_format($total,2), ' align="right" style="color:darkblue"');
		cell('');
		print "</tr>\n";

		$gTotal += $total;
	}

	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($gTotal,2), ' align="right" style="color:brown; background-color:lightyellow"');
	cell('',' style="color:brown; background-color:lightyellow"');
	print "</tr>\n";

	print "</table><br />\n";
	
	$ggTotal += $gTotal;
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_f">
	<tr>
		<th>MODEL NO</th>
		<th width="15%">REFERENCE NO.</th>
		<th width="10%">REFERENCE DATE</th>
		<th width="10%">WH CFM<br />DATE</th>
		<th width="10%">RECEIVE<br />DATE</th>
		<th width="8%">DT QTY<br />(Pcs)</th>
		<th width="15%">REMARK</th>
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTotal,2), ' align="right" style="color:brown; background-color:lightyellow"');
cell('',' style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>