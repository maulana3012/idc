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
  po.po_code,
  to_char(po.po_date, 'dd-Mon-yy') as issued_po_date,
  po.po_shipment_mode,
  po.po_currency_type,
  sp.sp_code,
  sp.sp_full_name,
  icat.icat_pidx,
  it.icat_midx,
  it.it_code,
  poit.poit_item,
  poit.poit_qty,
  poit.poit_unit_price,
  CASE 
  	WHEN po.po_layout_type = 3 THEN (poit.poit_qty * poit.poit_unit_price)/100
  	ELSE (poit.poit_qty * poit.poit_unit_price)
  END AS amount
FROM
  ".ZKP_SQL."_tb_supplier AS sp
  JOIN ".ZKP_SQL."_tb_po AS po ON po.po_sp_code = sp.sp_code
  JOIN ".ZKP_SQL."_tb_po_item AS poit USING(po_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE ". $strWhere . "
ORDER BY
  icat.icat_pidx, it.icat_midx, it.it_code, po.po_date, po.po_code";

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
		$col['poit_item'],		//2
		$col['po_code'],		//3
		$col['issued_po_date'],	//4
		$col['sp_code'],		//5
		$col['sp_full_name'],	//6
		$col['poit_qty'],		//7
		$col['poit_unit_price'],//8
		$col['amount'],			//9
		$col['po_shipment_mode'],	//10
		$col['icat_pidx'],		//11
		$col['po_currency_type']	//12
		);

	//1st grouping
	if($cache[0] != $col['icat_pidx'].$col['icat_midx']) {
		$cache[0] = $col['icat_pidx'].$col['icat_midx'];
		$group0[$col['icat_pidx'].$col['icat_midx']] = array();
	}

	if($cache[1] != $col['it_code']) {
		$cache[1] = $col['it_code'];
		$group0[$col['icat_pidx'].$col['icat_midx']][$col['it_code']] = array();
	}

	if($cache[2] != $col['po_code']) {
		$cache[2] = $col['po_code'];
	}

	$group0[$col['icat_pidx'].$col['icat_midx']][$col['it_code']][$col['po_code']] = 1;
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

	//get category path from current icat_midx.
	$path = executeSP(ZKP_SQL."_getCategoryPath", $rd[$rdIdx][0]);
	eval(html_entity_decode($path[0]));	
	$path = array_reverse($path);
	$total1 = $path[1][4]." > ".$path[2][4]." > ".$path[3][4] ;

	echo "<span class=\"comment\"><b> CATEGORY: ". $total1. "</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th rowspan="2" width="13%">MODEL NO</th>
			<th rowspan="2" width="10%">PO#</th>
			<th rowspan="2" width="8%">PO DATE</th>
			<th rowspan="2" width="8%">SHIP<br />MODE</th>
			<th rowspan="2">SUPPLIER</th>
			<th colspan="2" width="12%">@PRICE</th>
			<th rowspan="2" width="5%">QTY<br>(EA)</th>
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
	//MODEL
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".trim($rd[$rdIdx][1])."]<br/>".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');	//MODEL NO

		$tot = array(0,0,0);
		$print_tr_2 = 0;
		$item = "[".trim($rd[$rdIdx][1])."] ".$rd[$rdIdx][2];
		//ORDER
		foreach($group2 as $total3 => $group3) {
			if($print_tr_2++ > 0) print "<tr>\n";
			cell_link("<span class=\"bar\">".$rd[$rdIdx][3]."</span>", ' align="center" href="revise_po.php?_code='.$rd[$rdIdx][3].'"',
				' href="revise_po.php?_code='.$rd[$rdIdx][3].'"'); //PO NO	
			cell($rd[$rdIdx][4], ' align="center"');					//PO DATE
			cell(strtoupper($rd[$rdIdx][10]), ' align="center"');		//SHIPMENT MODE
			cell("[".$rd[$rdIdx][5]."] ".$rd[$rdIdx][6], ' ');			//SUPPLIER
			if($rd[$rdIdx][12]==1) {									//PRICE
				cell(number_format($rd[$rdIdx][8],2), ' align="right"'); cell("");
			} else {
				cell(""); cell(number_format($rd[$rdIdx][8],2), ' align="right"');
			}
			cell(number_format($rd[$rdIdx][7]), ' align="right"'); $tot[0] += $rd[$rdIdx][7];		//QTY
			
			if($rd[$rdIdx][12]==1) {									//AMOUNT
				cell(number_format($rd[$rdIdx][9],2), ' align="right"'); cell(""); $tot[1] += $rd[$rdIdx][9];
			} else {
				cell(""); cell(number_format($rd[$rdIdx][9],2), ' align="right"'); $tot[2] += $rd[$rdIdx][9];
			}
			print "</tr>\n";

			$rdIdx++;
		}

		print "<tr>\n";
		cell("$item", ' colspan="6" align="right" style="color:darkblue"');
		cell(number_format($tot[0]), ' align="right" style="color:darkblue"');
		cell(number_format($tot[1],2), ' align="right" style="color:darkblue"');
		cell(number_format($tot[2],2), ' align="right" style="color:darkblue"');
		print "</tr>\n";

		for($i=0;$i<3;$i++) $gTot[$i] += $tot[$i];
	}
	
	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="7" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($gTot[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($gTot[1],2), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($gTot[2],2), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";
	
	for($i=0;$i<3;$i++) $ggTot[$i] += $gTot[$i];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_f">
		<tr>
			<th rowspan="2" width="13%">MODEL NO</th>
			<th rowspan="2" width="10%">PO#</th>
			<th rowspan="2" width="8%">PO DATE</th>
			<th rowspan="2" width="8%">SHIP<br />MODE</th>
			<th rowspan="2">SUPPLIER</th>
			<th colspan="2" width="12%">@PRICE</th>
			<th rowspan="2" width="5%">QTY<br>(EA)</th>
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