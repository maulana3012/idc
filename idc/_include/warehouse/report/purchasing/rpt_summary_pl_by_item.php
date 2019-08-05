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
$tmp = array();

if ($_type != 'all') {
	$tmp[] = "po_type = $_type";
}

if ($some_date != "") {
	$tmp[] = "pl_date = DATE '$some_date'";
} else {
	$tmp[] = "pl_date BETWEEN DATE '$period_from' AND '$period_to'";
}

$strWhere = implode(" AND ", $tmp);

$sql = "
SELECT
  po_code,
  pl_no,
  to_char(pl_date, 'dd-Mon-yy') as issued_pl_date,
  sp_code,
  sp_full_name,
  icat_midx,
  it_code,
  it_model_no,
  plit_qty,
  'revise_pl.php?_code='||po_code||'&_pl_no='||pl_no AS go_page
FROM
  ".ZKP_SQL."_tb_supplier_local AS sp
  JOIN ".ZKP_SQL."_tb_po_local AS po USING(sp_code)
  JOIN ".ZKP_SQL."_tb_pl_local AS pl USING(po_code)
  JOIN ".ZKP_SQL."_tb_pl_local_item AS poit USING(po_code, pl_no)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE ". $strWhere . "
ORDER BY
  icat.icat_pidx, icat_midx, it_code, po_code, pl_no";

// raw data
$rd = array();
$rdIdx = 0;
$cache = array("","",""); // 3th level
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'],		//0
		$col['it_code'],		//1
		$col['it_model_no'],	//2
		$col['po_code'],		//3
		$col['pl_no'],			//4
		$col['issued_pl_date'],	//5
		$col['sp_code'],		//6
		$col['sp_full_name'],	//7
		$col['plit_qty'],		//8
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

	if($cache[2] != $col['po_code'].$col['pl_no']) {
		$cache[2] = $col['po_code'].$col['pl_no'];
	}

	$group0[$col['icat_midx']][$col['it_code']][$col['po_code'].$col['pl_no']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$ggTotal	= array(0);

//GROUP
foreach ($group0 as $total1 => $group1) {

	//get category path from current icat_midx.
	$path = executeSP(ZKP_SQL."_getCategoryPath", $rd[$rdIdx][0]);
	eval(html_entity_decode($path[0]));	
	$path = array_reverse($path);
	$total1 = $path[1][4]." > ".$path[2][4]." > ".$path[3][4] ;

	echo "<span class=\"comment\"><b> CATEGORY: ". $total1. "</b></span>\n";
	print <<<END
	<table width="85%" class="table_f">
		<tr>
			<th width="17%">MODEL NO</th>
			<th width="18%">PO NO#</th>
			<th width="5%">PL NO#</th>
			<th width="10%">PL DATE</th>
			<th>SUPPLIER</th>
			<th width="8%">QTY</th>
		</tr>\n
END;

	$gTotal		= array(0);
	$print_tr_1	= 0;
	print "<tr>\n";
	//MODEL
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".trim($rd[$rdIdx][1])."]<br/>".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');	//MODEL NO

		$total = array(0);
		$print_tr_2 = 0;
		//ORDER
		foreach($group2 as $total3 => $group3) {
			if($print_tr_2++ > 0) print "<tr>\n";
			cell_link("<span class=\"bar\">".$rd[$rdIdx][3]."</span>", ' align="center" href="revise_po.php?_code='.$rd[$rdIdx][3].'"',
				' href="'.$rd[$rdIdx][9].'"');							//PO NO
			cell($rd[$rdIdx][4], ' align="center"');					//PL NO
			cell($rd[$rdIdx][5], ' align="center"');					//PL DATE
			cell($rd[$rdIdx][7], ' ');									//SUPPLIER
			cell(number_format($rd[$rdIdx][8]), ' align="right"');		//QTY
			print "</tr>\n";

			$total[0]	+= $rd[$rdIdx][8];
			$rdIdx++;
		}
		print "<tr>\n";
		cell("$total2", ' colspan="4" align="right" style="color:darkblue"');
		cell(number_format($total[0]), ' align="right" style="color:darkblue"');
		print "</tr>\n";

		$gTotal[0] += $total[0];
	}

	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($gTotal[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";
	
	$ggTotal[0] += $gTotal[0];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="85%" class="table_f">
	<tr>
		<th width="17%">MODEL NO</th>
		<th width="18%">PO NO#</th>
		<th width="5%">PL NO#</th>
		<th width="10%">PL DATE</th>
		<th>SUPPLIER</th>
		<th width="8%">QTY</th>
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTotal[0]), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>