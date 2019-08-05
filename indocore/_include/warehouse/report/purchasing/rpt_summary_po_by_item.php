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

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if ($_type != 'all') {
	$tmp[] = "po_type = $_type";
}

if ($some_date != "") {
	$tmp[] = "po.po_date = DATE '$some_date'";
} else {
	$tmp[] = "po.po_date BETWEEN DATE '$period_from' AND '$period_to'";
}

$tmp[] = "po_ordered_by = ".$cboFilter[1][ZKP_URL][0][0];

$strWhere = implode(" AND ", $tmp);

$sql = "
SELECT
  po_code,
  to_char(po_date, 'dd-Mon-yy') as issued_po_date,
  sp_code,
  sp_full_name,
  poit_idx,
  icat_midx,
  it_code,
  it_model_no,
  poit_qty,
  poit_unit_price,
  poit_qty*poit_unit_price AS poit_amount,
  'revise_po.php?_code='||po_code AS go_page
FROM
  ".ZKP_SQL."_tb_supplier_local AS sp
  JOIN ".ZKP_SQL."_tb_po_local AS po USING(sp_code)
  JOIN ".ZKP_SQL."_tb_po_local_item AS poit USING(po_code)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE ". $strWhere . "
ORDER BY
  icat.icat_pidx, icat_midx, it_code, po_date, po_code, poit_idx";

// raw data
$rd = array();
$rdIdx = 0;

$cache = array("","","",""); // 4th level
$group0 = array();

$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'],		//0
		$col['it_code'],		//1
		$col['it_model_no'],	//2
		$col['po_code'],		//3
		$col['issued_po_date'],	//4
		$col['sp_code'],		//5
		$col['sp_full_name'],	//6
		$col['poit_qty'],		//7
		$col['poit_unit_price'],//8
		$col['poit_amount'],	//9
		$col['go_page'],		//10
		$col['poit_idx']		//11
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

	if($cache[2] != $col['po_code']) {
		$cache[2] = $col['po_code'];
		$group0[$col['icat_midx']][$col['it_code']][$col['po_code']] = array();
	}

	if($cache[3] != $col['poit_idx']) {
		$cache[3] = $col['poit_idx'];
	}

	$group0[$col['icat_midx']][$col['it_code']][$col['po_code']][$col['poit_idx']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$ggTotal	= array(0,0);

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
			<th width="17%">MODEL NO</th>
			<th width="18%">PO#</th>
			<th width="8%">PO DATE</th>
			<th>SUPPLIER</th>
			<th width="12%">UNIT PRICE<br/>(Rp)</th>
			<th width="8%">QTY</th>
			<th width="12%">AMOUNT<br>(Rp)</th>
		</tr>\n
END;

	
	$gTotal		= array(0,0);
	$print_tr_1	= 0;
	print "<tr>\n";
	//MODEL
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".trim($rd[$rdIdx][1])."]<br/>".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');	//MODEL NO

		$total = array(0,0);
		$print_tr_2 = 0;
		//ORDER
		foreach($group2 as $total3 => $group3) {
			$rowSpan = 0;
			array_walk_recursive($group3, 'getRowSpan');

			if($print_tr_2++ > 0) print "<tr>\n";
			cell_link("<span class=\"bar\">".$rd[$rdIdx][3]."</span>", ' align="center" valign="top" rowspan="'.$rowSpan.'"',
				' href="'.$rd[$rdIdx][10].'"');												//PO NO	
			cell($rd[$rdIdx][4], ' align="center" valign="top" rowspan="'.$rowSpan.'"');	//PO DATE
			cell($rd[$rdIdx][6], ' valign="top" rowspan="'.$rowSpan.'"');					//SUPPLIER

			$print_tr_3 = 0;
			//ORDER
			foreach($group3 as $total4 => $group4) {
				if($print_tr_3++ > 0) print "<tr>\n";
				cell(number_format($rd[$rdIdx][8],2), ' align="right"');	//PRICE
				cell(number_format($rd[$rdIdx][7]), ' align="right"');		//QTY
				cell(number_format($rd[$rdIdx][9],2), ' align="right"');	//AMOUNT
				print "</tr>\n";

				$total[0]	+= $rd[$rdIdx][7];
				$total[1]	+= $rd[$rdIdx][9];
				$item = $rd[$rdIdx][2];
				$rdIdx++;
			}
		}
		print "<tr>\n";
		cell("[$total2] $item", ' colspan="4" align="right" style="color:darkblue"');
		cell(number_format($total[0]), ' align="right" style="color:darkblue"');
		cell(number_format($total[1],2), ' align="right" style="color:darkblue"');
		print "</tr>\n";

		$gTotal[0] += $total[0];
		$gTotal[1] += $total[1];
	}

	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($gTotal[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($gTotal[1],2), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";

	print "</table><br />\n";
	
	$ggTotal[0] += $gTotal[0];
	$ggTotal[1] += $gTotal[1];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_f">
	<tr>
		<th width="17%">MODEL NO</th>
		<th width="18%">PO#</th>
		<th width="8%">PO DATE</th>
		<th>SUPPLIER</th>
		<th width="12%">UNIT PRICE<br/>(Rp)</th>
		<th width="8%">QTY</th>
		<th width="12%">AMOUNT<br>(Rp)</th>
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTotal[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTotal[1],2), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>