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

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if ($_type != "all") {
	$tmp[] = "po_type = $_type";
}

if ($_curr != "all") {
	$tmp[] = "po_currency_type = $_curr";
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
 sp.sp_code,
 sp.sp_full_name,
 po.po_code,
 po.po_date,
 to_char(po.po_date, 'dd-Mon-YY') AS issued_po_date,
 po.po_shipment_mode,
 po.po_currency_type,
 poit.it_code,
 poit.poit_item,
 poit.poit_desc,
 poit.poit_unit_price,
 poit.poit_qty,
 CASE 
  WHEN po.po_layout_type = 3 THEN (poit.poit_qty * poit.poit_unit_price)/100
  ELSE poit.poit_qty * poit.poit_unit_price
 END AS amount
FROM
 ".ZKP_SQL."_tb_supplier AS sp
 JOIN ".ZKP_SQL."_tb_po AS po ON po.po_sp_code = sp.sp_code
 JOIN ".ZKP_SQL."_tb_po_item AS poit USING(po_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code) 
WHERE " . $strWhere . "
ORDER BY
 sp_code, po_code, po_date, it_code";

// raw data
$rd = array();
$rdIdx = 0;
$cache = array("","","");
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['sp_code'],			//0
		$col['sp_full_name'],		//1
		$col['po_code'],			//2
		$col['issued_po_date'],		//3
		$col['it_code'],			//4
		$col['poit_item'],			//5
		$col['poit_desc'],			//6
		$col['poit_unit_price'],	//7
		$col['poit_qty'],			//8
		$col['amount'],				//9
		$col['po_shipment_mode'],	//10
		$col['po_currency_type']	//11
	);

	//1st grouping
	if($cache[0] != $col['sp_full_name']) {
		$cache[0] = $col['sp_full_name'];
		$group0[$col['sp_full_name']] = array();
	}

	if($cache[1] != $col['po_code']) {
		$cache[1] = $col['po_code'];
		$group0[$col['sp_full_name']][$col['po_code']] = array();
	}

	if($cache[2] != $col['it_code']) {
		$cache[2] = $col['it_code'];
		$group0[$col['sp_full_name']][$col['po_code']][$col['it_code']] = 1;
	} else {
		$group0[$col['sp_full_name']][$col['po_code']][$col['it_code']] = 1;
	}
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$ggTot = array(0,0,0);

//GROUP
foreach ($group0 as $total1 => $group1) {

	echo "<span class=\"comment\"><b> SUPPLIER : ". $total1. "</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th rowspan="2" width="10%">PO NO#</th>
			<th rowspan="2" width="9%">PO DATE</th>
			<th rowspan="2" width="8%">SHIP<br />MODE</th>
			<th rowspan="2" width="15%">MODEL NO</th>
			<th rowspan="2">DESC</th>
			<th colspan="2" width="12%">UNIT PRICE</th>
			<th rowspan="2" width="7%">QTY<br>(EA)</th>
			<th colspan="2" width="15%">AMOUNT</th>
		</tr>
		<tr>
			<th width="5%">US $</th>
			<th width="7%">RUPIAH</th>
			<th width="7%">US $</th>
			<th width="8%">RUPIAH</th>
		</tr>\n
END;

	$gTot = array(0,0,0);
	$print_tr_1 = 0;
	print "<tr>\n";

	//PO
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell_link("<span class=\"bar\">".$rd[$rdIdx][2]."</span>", ' align="center" valign="top" rowspan="'.$rowSpan.'"',
			' href="revise_po.php?_code='.$rd[$rdIdx][2].'"');							//po code
		cell($rd[$rdIdx][3], ' valign=""top align="center" rowspan="'.$rowSpan.'"');	//po date
		cell(strtoupper($rd[$rdIdx][10]), ' valign=""top align="center" rowspan="'.$rowSpan.'"');	//ship mode

		$print_tr_2 = 0;
		$tot = array(0,0,0);
		//ITEM
		foreach($group2 as $group3) {
			if($print_tr_2++ > 0) print "<tr>\n";
			
			cell("[".$rd[$rdIdx][4]."] ".$rd[$rdIdx][5]);				//MODEL NO
			cell($rd[$rdIdx][6]);										//DESC
			if($rd[$rdIdx][11]==1) {									//PRICE
				cell(number_format($rd[$rdIdx][7],2), ' align="right"'); cell(""); 
			} else {
				cell(""); cell(number_format($rd[$rdIdx][7],2), ' align="right"'); 
			}
			cell(number_format($rd[$rdIdx][8]), ' align="right"');	$tot[0] += $rd[$rdIdx][8];	//QTY
			if($rd[$rdIdx][11]==1) {									//AMOUNT
				cell(number_format($rd[$rdIdx][9],2), ' align="right"'); cell(""); $tot[1] += $rd[$rdIdx][9];
			} else {
				cell(""); cell(number_format($rd[$rdIdx][9],2), ' align="right"');  $tot[2] += $rd[$rdIdx][9];
			}
			print "</tr>\n";

			$rdIdx++;
		}

		print "<tr>\n";
		cell("$total2", ' colspan="4" align="right" style="color:darkblue"');
		cell(number_format($tot[0]), ' align="right" style="color:darkblue"');
		cell(number_format($tot[1],2), ' align="right" style="color:darkblue"');
		cell(number_format($tot[2],2), ' align="right" style="color:darkblue"');
		print "</tr>\n";

		for($i=0; $i<3; $i++) $gTot[$i] += $tot[$i];
	}

	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="7" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($gTot[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($gTot[1],2), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($gTot[2],2), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	for($i=0; $i<3; $i++) $ggTot[$i] += $gTot[$i];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th rowspan="2" width="10%">PO NO#</th>
			<th rowspan="2" width="9%">PO DATE</th>
			<th rowspan="2" width="8%">SHIP<br />MODE</th>
			<th rowspan="2" width="15%">MODEL NO</th>
			<th rowspan="2">DESC</th>
			<th colspan="2" width="12%">UNIT PRICE</th>
			<th rowspan="2" width="7%">QTY<br>(EA)</th>
			<th colspan="2" width="15%">AMOUNT</th>
		</tr>
		<tr>
			<th width="5%">US $</th>
			<th width="7%">RUPIAH</th>
			<th width="7%">US $</th>
			<th width="8%">RUPIAH</th>
		</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="7" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTot[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTot[1],2), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTot[2],2), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>