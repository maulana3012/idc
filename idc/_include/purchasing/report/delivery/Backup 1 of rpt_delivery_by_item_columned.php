<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim
*
*
*/
//SET WHERE PARAMETER
$tmp_out	= array();
$tmp_inc	= array();

if ($_last_category != 0) {
	$catList = executeSP("getSubCategory", $_last_category);
	$strWhereItem = "WHERE a.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if(substr($_source,0,3) == 'out') {
	if($_source == 'out') {
		$tmp_inc[]	= "inc_idx is null";
	} else {
		$tmp_out[]	= "out_doc_type = ". substr($_source,4,1);
		$tmp_inc[]	= "inc_idx is null";
	}
} else if(substr($_source,0,2) == 'in') {
	if($_source == 'in') {
		$tmp_out[]	= "out_idx is null";
	} else {
		$tmp_inc[]	= "inc_doc_type = ". substr($_source,3,1);
		$tmp_out[]	= "out_idx is null";
	}
}

if($_vat == 'vat') {
	$tmp_inc[]	= "inc_type = 1";
	$tmp_out[]	= "out_idx = 1";
} else if($_vat == 'non') {
	$tmp_inc[]	= "inc_type = 2";
	$tmp_out[]	= "out_idx = 2";
}

if ($some_date != "") {
	$tmp_out[]	= "out_cfm_date = DATE '$some_date'";
	$tmp_inc[]	= "inc_confirmed_timestamp between DATE '$some_date 00:00:00' AND '$some_date 23:59:59'";
} else {
	$tmp_out[]	= "out_cfm_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_inc[]	= "inc_confirmed_timestamp BETWEEN DATE '$period_from 00:00:00' AND '$period_to 23:59:59'";
}

$strWhereOutgoing	= implode(" AND ", $tmp_out);
$strWhereIncoming	= implode(" AND ", $tmp_inc);

$sql = 
"SELECT
  a.icat_pidx AS icat_pidx,
  a.icat_midx AS icat_midx,
  b.it_code AS it_code,
  b.it_model_no AS it_model_no,
  (SELECT SUM(otit_qty) FROM ".ZKP_SQL."_tb_outgoing JOIN ".ZKP_SQL."_tb_outgoing_item USING(out_idx) WHERE it_code = b.it_code AND out_dept='A' AND $strWhereOutgoing) AS qty_0,
  (SELECT SUM(otit_qty) FROM ".ZKP_SQL."_tb_outgoing JOIN ".ZKP_SQL."_tb_outgoing_item USING(out_idx) WHERE it_code = b.it_code AND out_dept='D' AND $strWhereOutgoing) AS qty_1,
  (SELECT SUM(otit_qty) FROM ".ZKP_SQL."_tb_outgoing JOIN ".ZKP_SQL."_tb_outgoing_item USING(out_idx) WHERE it_code = b.it_code AND out_dept='H' AND $strWhereOutgoing) AS qty_2,
  (SELECT SUM(otit_qty) FROM ".ZKP_SQL."_tb_outgoing JOIN ".ZKP_SQL."_tb_outgoing_item USING(out_idx) WHERE it_code = b.it_code AND out_dept='P' AND $strWhereOutgoing) AS qty_3,
  (SELECT SUM(otit_qty) FROM ".ZKP_SQL."_tb_outgoing JOIN ".ZKP_SQL."_tb_outgoing_item USING(out_idx) WHERE it_code = b.it_code AND out_dept='S' AND $strWhereOutgoing) AS qty_4,
  (SELECT SUM(otit_qty) FROM ".ZKP_SQL."_tb_outgoing JOIN ".ZKP_SQL."_tb_outgoing_item USING(out_idx) WHERE it_code = b.it_code AND out_dept='M' AND $strWhereOutgoing) AS qty_5,
  (SELECT SUM(otit_qty) FROM ".ZKP_SQL."_tb_outgoing JOIN ".ZKP_SQL."_tb_outgoing_item USING(out_idx) WHERE it_code = b.it_code AND $strWhereOutgoing) AS qty_6,
  (SELECT -SUM(init_stock_qty) FROM ".ZKP_SQL."_tb_incoming JOIN ".ZKP_SQL."_tb_incoming_item USING(inc_idx) WHERE it_code = b.it_code AND inc_dept='A' AND $strWhereIncoming) AS qty_7,
  (SELECT -SUM(init_stock_qty) FROM ".ZKP_SQL."_tb_incoming JOIN ".ZKP_SQL."_tb_incoming_item USING(inc_idx) WHERE it_code = b.it_code AND inc_dept='D' AND $strWhereIncoming) AS qty_8,
  (SELECT -SUM(init_stock_qty) FROM ".ZKP_SQL."_tb_incoming JOIN ".ZKP_SQL."_tb_incoming_item USING(inc_idx) WHERE it_code = b.it_code AND inc_dept='H' AND $strWhereIncoming) AS qty_9,
  (SELECT -SUM(init_stock_qty) FROM ".ZKP_SQL."_tb_incoming JOIN ".ZKP_SQL."_tb_incoming_item USING(inc_idx) WHERE it_code = b.it_code AND inc_dept='P' AND $strWhereIncoming) AS qty_10,
  (SELECT -SUM(init_stock_qty) FROM ".ZKP_SQL."_tb_incoming JOIN ".ZKP_SQL."_tb_incoming_item USING(inc_idx) WHERE it_code = b.it_code AND inc_dept='S' AND $strWhereIncoming) AS qty_11,
  (SELECT -SUM(init_stock_qty) FROM ".ZKP_SQL."_tb_incoming JOIN ".ZKP_SQL."_tb_incoming_item USING(inc_idx) WHERE it_code = b.it_code AND $strWhereIncoming) AS qty_12
FROM ".ZKP_SQL."_tb_item_cat AS a JOIN ".ZKP_SQL."_tb_item AS b USING(icat_midx) 
$strWhereItem
ORDER BY icat_pidx, icat_midx, it_code
";

// raw data
$rd = array();
$rdIdx = 0;

$cache = array("","");
$group0 = array();
$a = array("","");
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'],				//0
		$col['it_code'],				//1
		$col['it_model_no'],			//2
		$col['qty_0']+$col['qty_7'],	//3	A
		$col['qty_1']+$col['qty_8'],	//4	D
		$col['qty_2']+$col['qty_9'],	//5	H
		$col['qty_3']+$col['qty_10'],	//6	P
		$col['qty_4']+$col['qty_11'],	//7	CS
		$col['qty_5'],					//8	M
		$col['qty_6']+$col['qty_12'] 	//9	TOTAL
	);

	//1st grouping
	if($cache[0] != $col['icat_midx']) {
		$cache[0] = $col['icat_midx'];
		$group0[$col['icat_midx']] = array();
	}

	if($cache[1] != $col['it_code']) {
		$cache[1] = $col['it_code'];
	}

	$group0[$col['icat_midx']][$col['it_code']] = 1;
}

function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$grand_total = array(3=>0,0,0,0,0,0,0);

//GROUP
foreach ($group0 as $total1 => $group1) {
	//get category path from current icat_midx.
	$path = executeSP(ZKP_SQL."_getCategoryPath", $rd[$rdIdx][0]);
	eval(html_entity_decode($path[0]));	
	$path = array_reverse($path);
	$total1 = $path[1][4]." > ".$path[2][4]." > ". $path[3][4];

	echo "<span class=\"comment\"><b> CATEGORY: ". $total1. "</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr height="25px">
			<th>MODEL NO</th>
			<th width="8%">A</th>
			<th width="8%">D</th>
			<th width="8%">H</th>
			<th width="8%">P</th>
			<th width="8%">CS</th>
			<th width="8%">M</th>
			<th width="10%">TOTAL</th>
		</tr>\n
END;

	$cat_total = array(3=>0,0,0,0,0,0,0);
	$print_tr_1 = 0;
	//ITEM CODE
	foreach($group1 as $total2 => $group2) {

		if($rd[$rdIdx][9] != 0) {
			//PRINT CONTENT
			if($print_tr_1++ > 0) print "<tr>\n";
			cell("[".trim($rd[$rdIdx][1])."] ".$rd[$rdIdx][2]);			//model name
			cell(number_format($rd[$rdIdx][3]), ' align="right"');		//billing D
			cell(number_format($rd[$rdIdx][4]), ' align="right"');		//billing D
			cell(number_format($rd[$rdIdx][5]), ' align="right"');		//billing H
			cell(number_format($rd[$rdIdx][6]), ' align="right"');		//billing P
			cell(number_format($rd[$rdIdx][7]), ' align="right"');		//CS
			cell(number_format($rd[$rdIdx][8]), ' align="right"');		//billing M
			cell(number_format($rd[$rdIdx][9]), ' align="right"');		//TOTAL
			print "</tr>\n";
		}

		for ($i=3; $i<10; $i++)
			$cat_total[$i] += $rd[$rdIdx][$i];
		$rdIdx++;
	}

	print "<tr>\n";
	cell("<b>$total1</b>", ' align="right" style="color:brown; background-color:lightyellow"');
	for ($i=3; $i<10; $i++)
		cell(($i == 9) ? "<b>".number_format($cat_total[$i])."</b>" : number_format($cat_total[$i]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	for ($i=3; $i<10; $i++)
		$grand_total[$i] += $cat_total[$i];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr height="25px">
			<th>MODEL NO</th>
			<th width="8%">A</th>
			<th width="8%">D</th>
			<th width="8%">H</th>
			<th width="8%">P</th>
			<th width="8%">CS</th>
			<th width="8%">M</th>
			<th width="10%">TOTAL</th>
		</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' align="right" style="color:brown; background-color:lightyellow"');
for ($i=3; $i<10; $i++)
	cell('<b>'.number_format($grand_total[$i]).'</b>', ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>