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
  sp_code,
  sp_full_name,
  po_code,
  po_date,
  to_char(po_date, 'dd-Mon-YY') AS issued_po_date,
  poit_idx,
  it_code,
  it_model_no,
  it_desc,
  poit_unit_price,
  poit_qty,
  poit_unit_price*poit_qty AS poit_amount,
  po_vat,
  po_total_charge1+po_total_charge2 as add_charge,
  (select sum(poit_unit_price*poit_qty) from ".ZKP_SQL."_tb_po_local_item where po_code=po.po_code) as po_total_item,
  'revise_po.php?_code='||po_code AS go_page
FROM
  ".ZKP_SQL."_tb_supplier_local AS sp
  JOIN ".ZKP_SQL."_tb_po_local AS po USING(sp_code)
  JOIN ".ZKP_SQL."_tb_po_local_item AS poit USING(po_code)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE " . $strWhere . "
ORDER BY
  sp_code, po_code, po_date, it_code, poit_idx";

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
		$col['issued_po_date'],		//3
		$col['it_code'],			//4
		$col['it_model_no'],		//5
		$col['it_desc'],			//6
		$col['poit_unit_price'],	//7
		$col['poit_qty'],			//8
		$col['poit_amount'],		//9
		$col['po_total_item'],		//10
		$col['po_vat'],				//11
		$col['add_charge'],			//12
		$col['go_page'],			//13
		$col['poit_idx']			//14
	);

	//1st grouping
	if($cache[0] != $col['sp_code']) {
		$cache[0] = $col['sp_code'];
		$group0[$col['sp_code']] = array();
	}

	if($cache[1] != $col['po_code']) {
		$cache[1] = $col['po_code'];
		$group0[$col['sp_code']][$col['po_code']] = array();
	}

	if($cache[2] != $col['it_code']) {
		$cache[2] = $col['it_code'];
		$group0[$col['sp_code']][$col['po_code']][$col['it_code']] = array();
	}

	if($cache[3] != $col['poit_idx']) {
		$cache[3] = $col['poit_idx'];
	}

	$group0[$col['sp_code']][$col['po_code']][$col['it_code']][$col['poit_idx']] = 1;
}
/*
echo "<pre>";
var_dump($group0);
echo "</pre>";
*/
//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$ggTotal = array(0,0,0,0,0,0);

//GROUP
foreach ($group0 as $total1 => $group1) {

	echo "<span class=\"comment\"><b> SUPPLIER : ". "[" . $rd[$rdIdx][0] . "] " . $rd[$rdIdx][1]. "</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th width="18%">PO NO#</th>
			<th width="9%">PO DATE</th>
			<th width="17%">MODEL NO</th>
			<th width="12%">UNIT PRICE<br/>(Rp)</th>
			<th width="4%">QTY</th>
			<th width="8%">AMOUNT<br>(Rp)</th>
			<th width="8%">+CHARGE<br>(Rp)</th>
			<th width="8%">-VAT<br>(Rp)</th>
			<th width="8%">VAT<br>(Rp)</th>
			<th width="8%">TOTAL<br>(Rp)</th>
		</tr>\n
END;

	$po_code = '';
	$gTotal	= array(0,0,0,0,0,0);
	$print_tr_1 = 0;
	print "<tr>\n";
	//PO
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell_link("<span class=\"bar\">".$rd[$rdIdx][2]."</span>", ' align="center" valign="top" rowspan="'.$rowSpan.'"',
			' href="'.$rd[$rdIdx][13].'"');												//po code
		cell($rd[$rdIdx][3], ' valign=""top align="center" rowspan="'.$rowSpan.'"');	//po date

		$print_tr_2 = 0;
		$total		= array(0,0,0,0,0,0);
		//ITEM
		foreach($group2 as $total3 => $group3) {
			$rowSpan = 0;
			array_walk_recursive($group3, 'getRowSpan');

			if($print_tr_2++ > 0) print "<tr>\n";
			cell("[". trim($rd[$rdIdx][4]) ."] ".$rd[$rdIdx][5], ' valign="top" rowspan="'.$rowSpan.'"');	//MODEL NO

			$print_tr_3 = 0;
			$tot = array(0,0,0,0);
			//ITEM
			foreach($group3 as $group4) {
				if($print_tr_3++ > 0) print "<tr>\n";
				cell(number_format($rd[$rdIdx][7]), ' align="right"');	//PRICE
				cell(number_format($rd[$rdIdx][8]), ' align="right"');	//QTY
				cell(number_format($rd[$rdIdx][9]), ' align="right"');	//AMOUNT

				$rowSpan = 0;
				array_walk_recursive($group2, 'getRowSpan');
				if($print_tr_2==1 && $rd[$rdIdx][2]!=$po_code) {
					$tot[0] = $rd[$rdIdx][12];
					$tot[1] = $rd[$rdIdx][10]+$rd[$rdIdx][12];
					$tot[2] = ($tot[1]*$rd[$rdIdx][11]) / 100;
					$tot[3] = $tot[1]+$tot[2];

					cell(($tot[0]<=0) ? '0' : number_format($tot[0]), ' align="right" valign="bottom" rowspan="'.$rowSpan.'"');	//+CHARGE
					cell(($tot[1]<=0) ? '0' : number_format($tot[1]), ' align="right" valign="bottom" rowspan="'.$rowSpan.'"');	//AMOUNT -VAT
					cell(($tot[2]<=0) ? '0' : number_format($tot[2]), ' align="right" valign="bottom" rowspan="'.$rowSpan.'"');	//VAT
					cell(($tot[3]<=0) ? '0' : number_format($tot[3]), ' align="right" valign="bottom" rowspan="'.$rowSpan.'"');	//AMOUNT +VAT

					$total[2]	+= $tot[0];
					$total[3]	+= $tot[1];
					$total[4]	+= $tot[2];
					$total[5]	+= $tot[3];
				}
				print "</tr>\n";

				$total[0]	+= $rd[$rdIdx][8];
				$total[1]	+= $rd[$rdIdx][9];
				$supplier	= $rd[$rdIdx][1];
				$po_code	= $rd[$rdIdx][2];
				$rdIdx++;
			}
		}

		print "<tr>\n";
		cell("$total2", ' colspan="2" align="right" style="color:darkblue"');
		for($i=0; $i<6; $i++) {
			cell(number_format($total[$i]), ' align="right" style="color:darkblue"');
			$gTotal[$i]	+= $total[$i];
		}
		print "</tr>\n";
	}

	print "<tr>\n";
	cell("<b>$supplier</b>", ' colspan="4" align="right" style="color:brown; background-color:lightyellow"');
	for($i=0; $i<6; $i++) {
		cell(number_format($gTotal[$i]), ' align="right" style="color:brown; background-color:lightyellow"');
		$ggTotal[$i]	+= $gTotal[$i];
	}
	print "</tr>\n";
	print "</table><br />\n";
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_f">
	<tr>
		<th width="18%">PO NO#</th>
		<th width="9%">PO DATE</th>
		<th width="17%">MODEL NO</th>
		<th width="12%">UNIT PRICE<br/>(Rp)</th>
		<th width="4%">QTY</th>
		<th width="8%">AMOUNT<br>(Rp)</th>
		<th width="8%">+CHARGE<br>(Rp)</th>
		<th width="8%">-VAT<br>(Rp)</th>
		<th width="8%">VAT<br>(Rp)</th>
		<th width="8%">TOTAL<br>(Rp)</th>
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="4" align="right" style="color:brown; background-color:lightyellow"');
for($i=0; $i<6; $i++) {
	cell(number_format($ggTotal[$i]), ' align="right" style="color:brown; background-color:lightyellow"');
}
print "</tr>\n";
print "</table>\n";
?>