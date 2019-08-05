<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim
*
* $_search_date : Inquire Date
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
  sp_code,
  sp_full_name,
  po_code,
  pl_no,
  pl_date,
  to_char(pl_date, 'dd-Mon-YY') AS issued_pl_date,
  it_code,
  it_model_no,
  it_desc,
  plit_qty,
  'revise_pl.php?_code='||po_code||'&_pl_no='||pl_no AS go_page
FROM
  ".ZKP_SQL."_tb_supplier_local AS sp
  JOIN ".ZKP_SQL."_tb_po_local AS po USING(sp_code)
  JOIN ".ZKP_SQL."_tb_pl_local AS pl USING(po_code)
  JOIN ".ZKP_SQL."_tb_pl_local_item AS poit USING(po_code, pl_no)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE " . $strWhere . "
ORDER BY
  sp_code, po_code, pl_no, it_code";

// raw data
$rd = array();
$rdIdx = 0;
$cache = array("","","","");
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['sp_code'],			//0
		$col['sp_full_name'],		//1
		$col['po_code'],			//2
		$col['pl_no'],				//3
		$col['issued_pl_date'],		//4
		$col['it_code'],			//5
		$col['it_model_no'],		//6
		$col['it_desc'],			//7
		$col['plit_qty'],			//8
		$col['go_page']				//9
	);

	//1st grouping
	if($cache[0] != $col['sp_code']) {
		$cache[0] = $col['sp_code'];
		$group0[$col['sp_code']] = array();
	}

	if($cache[1] != $col['po_code'].$col['pl_no']) {
		$cache[1] = $col['po_code'].$col['pl_no'];
		$group0[$col['sp_code']][$col['po_code'].$col['pl_no']] = array();
	}

	if($cache[2] != $col['it_code']) {
		$cache[2] = $col['it_code'];
	}

	$group0[$col['sp_code']][$col['po_code'].$col['pl_no']][$col['it_code']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$ggTotal = array(0);

//GROUP
foreach ($group0 as $total1 => $group1) {

	echo "<span class=\"comment\"><b> SUPPLIER : ". "[" . $rd[$rdIdx][0] . "] " . $rd[$rdIdx][1]. "</b></span>\n";
	print <<<END
	<table width="85%" class="table_f">
		<tr>
			<th width="20%">PO NO#</th>
			<th width="5%">PL NO#</th>
			<th width="12%">PL DATE</th>
			<th width="18%">MODEL NO</th>
			<th>DESC</th>
			<th width="8%">QTY</th>
		</tr>\n
END;

	$gTotal	= array(0);
	$print_tr_1	= 0;
	print "<tr>\n";
	//PO
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell_link("<span class=\"bar\">".$rd[$rdIdx][2]."</span>", ' align="center" valign="top" rowspan="'.$rowSpan.'"',
			' href="'.$rd[$rdIdx][9].'"');												//po code
		cell($rd[$rdIdx][3], ' valign=""top align="center" rowspan="'.$rowSpan.'"');	//pl no
		cell($rd[$rdIdx][4], ' valign=""top align="center" rowspan="'.$rowSpan.'"');	//pl date

		$print_tr_2 = 0;
		$total		= array(0,0);
		//ITEM
		foreach($group2 as $group3) {
			if($print_tr_2++ > 0) print "<tr>\n";
			
			cell("[". trim($rd[$rdIdx][5]) ."] ".$rd[$rdIdx][6]);		//MODEL NO
			cell($rd[$rdIdx][7]);										//DESC
			cell(number_format($rd[$rdIdx][8]), ' align="right"');		//QTY
			print "</tr>\n";

			$total[0]	+= $rd[$rdIdx][8];
			$supplier	= $rd[$rdIdx][1];
			$rdIdx++;
		}
		
		print "<tr>\n";
		cell("$total2", ' colspan="2" align="right" style="color:darkblue"');
		cell(number_format($total[0]), ' align="right" style="color:darkblue"');
		print "</tr>\n";

		$gTotal[0] += $total[0];
	}

	print "<tr>\n";
	cell("<b>$supplier</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($gTotal[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$ggTotal[0]	+= $gTotal[0];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="85%" class="table_f">
	<tr>
		<th width="20%">PO NO#</th>
		<th width="5%">PL NO#</th>
		<th width="12%">PL DATE</th>
		<th width="18%">MODEL NO</th>
		<th>DESC</th>
		<th width="8%">QTY</th>
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTotal[0]), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>