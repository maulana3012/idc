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
$tmp_item	= array();

if(ZKP_URL == 'MEP') {
	$tmp_inc[]	= "inc_idx IS NULL";
	$tmp_out[]	= "out_idx IS NULL";
}

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp_item[] = "a.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
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

$sql_qty = "
SELECT it_code, out_dept AS dept, substr(out_code,1,2) AS init, sum(otit_qty) 
FROM ".ZKP_SQL."_tb_outgoing JOIN ".ZKP_SQL."_tb_outgoing_item USING(out_idx) 
WHERE $strWhereOutgoing
GROUP BY it_code, out_dept, substr(out_code,1,2)
	UNION 
SELECT it_code, inc_dept AS dept, 'OU' AS init, sum(init_stock_qty) * -1
FROM ".ZKP_SQL."_tb_incoming JOIN ".ZKP_SQL."_tb_incoming_item USING(inc_idx)
WHERE $strWhereIncoming
GROUP BY it_code, dept
ORDER BY it_code, dept,init
";
$col_dept = array('A'=>0,'D'=>1,'H'=>2,'M'=>3,'P'=>4,'T'=>5,'DEMO'=>6,'TOTAL'=>7);
$item = array();
$qty = array();
$res_sql =& query($sql_qty);
while($col =& fetchRow($res_sql)) {
	if($col[2] != 'DM') {
		if( isset( $qty[$col[0]][$col_dept[$col[1]]] ) )	$qty[$col[0]][$col_dept[$col[1]]] += $col[3];
		else	$qty[$col[0]][$col_dept[$col[1]]] = $col[3];
	} else if($col[2] == 'DM') {
		if( isset( $qty[$col[0]][$col_dept['DEMO']] ) )	$qty[$col[0]][$col_dept['DEMO']] += $col[3];
		else	$qty[$col[0]][$col_dept['DEMO']] = $col[3];
	}

	if(isset($qty[$col[0]][$col_dept['TOTAL']]))	$qty[$col[0]][$col_dept['TOTAL']] += $col[3];
	else											$qty[$col[0]][$col_dept['TOTAL']] = $col[3];

	$item[] = trim($col[0]);
}

$item = array_unique($item);
$items = "'" . implode("','", $item) . "'";
$tmp_item[] = "it_code IN ($items)";
$strWhereItem	= implode(" AND ", $tmp_item);
$sql = 
"SELECT
  a.icat_pidx AS icat_pidx,
  a.icat_midx AS icat_midx,
  b.it_code AS it_code,
  b.it_model_no AS it_model_no
FROM ".ZKP_SQL."_tb_item_cat AS a JOIN ".ZKP_SQL."_tb_item AS b USING(icat_midx) 
WHERE $strWhereItem
ORDER BY icat_pidx, icat_midx, it_code
";

// raw data
$rd = array();
$rdIdx = 0;
$cache = array("","");
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'],		//0
		$col['it_code'],		//1
		$col['it_model_no']		//2
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
$grand_total = array(0,0,0,0,0,0,0,0);

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
			<th width="8%">M</th>
			<th width="8%">P</th>
			<th width="8%">T</th>
			<th width="8%">demo</th>
			<th width="10%">TOTAL</th>
		</tr>\n
END;

	$cat_total = array(0,0,0,0,0,0,0,0);
	$print_tr_1 = 0;
	//ITEM CODE
	foreach($group1 as $total2 => $group2) {

		//PRINT CONTENT
		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".trim($rd[$rdIdx][1])."] ".$rd[$rdIdx][2]);			//model name
		for($i=0; $i<8; $i++) {
			cell( (isset($qty[$rd[$rdIdx][1]][$i])) ? number_format((double)$qty[$rd[$rdIdx][1]][$i]) : 0 , ' align="right"');
		}
		print "</tr>\n";
		for($i=0; $i<8; $i++) {
			$cat_total[$i] += (isset($qty[$rd[$rdIdx][1]][$i])) ? $qty[$rd[$rdIdx][1]][$i] : 0;
		}
		$rdIdx++;
	}
/*
echo "<pre>";
var_dump($qty[$rd[$rdIdx][1]]);
echo "</pre>";
exit;
*/
	print "<tr>\n";
	cell("<b>$total1</b>", ' align="right" style="color:brown; background-color:lightyellow"');
	for($i=0; $i<8; $i++) {
		cell(number_format((double)$cat_total[$i]), ' align="right" style="color:brown; background-color:lightyellow"');
	}
	print "</tr>\n";
	print "</table><br />\n";

	for ($i=0; $i<8; $i++)
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
		<th width="8%">M</th>
		<th width="8%">P</th>
		<th width="8%">T</th>
		<th width="8%">demo</th>
		<th width="10%">TOTAL</th>
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' align="right" style="color:brown; background-color:lightyellow"');
for ($i=0; $i<8; $i++)
	cell('<b>'.number_format((double)$grand_total[$i]).'</b>', ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>