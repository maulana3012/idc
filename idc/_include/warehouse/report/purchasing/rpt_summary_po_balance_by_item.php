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

if($_status=='0') {
	$tmp[] = ZKP_SQL."_statusPOLocal(po.po_code)::boolean = false";
} else if($_status=='1') {
	$tmp[] = ZKP_SQL."_statusPOLocal(po.po_code)::boolean = true";
}

if ($some_date != "") {
	$tmp[] = "po_date = DATE '$some_date'";
} else {
	$tmp[] = "po_date BETWEEN DATE '$period_from' AND '$period_to'";
}

$strWhere = implode(" AND ", $tmp);

$sql = "
SELECT
 icat.icat_pidx,
 icat.icat_midx,
 it.it_code,
 it.it_model_no,
 sp.sp_code,
 sp.sp_full_name,
 po_code,
 to_char(po_date, 'dd-Mon-YY') AS issued_po_date,
 (select sum(poit_qty) from ".ZKP_SQL."_tb_po_local_item where po_code=po.po_code and it_code=it.it_code) AS poit_qty,
 '#'||pl_no AS pl_no,
 inlc_idx,
 to_char(inlc_checked_date, 'dd-Mon-YY') AS arrival_date,
 inlc_checked_date,
 init_qty,
 'revise_po.php?_code='||po_code AS go_page
FROM
 ".ZKP_SQL."_tb_supplier_local AS sp
 JOIN ".ZKP_SQL."_tb_po_local AS po USING(sp_code)
 JOIN ".ZKP_SQL."_tb_in_local AS inpl USING(po_code)
 JOIN ".ZKP_SQL."_tb_in_local_item AS init USING(inlc_idx)
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE " . $strWhere . "
	UNION
SELECT
 icat.icat_pidx,
 icat.icat_midx,
 it.it_code,
 it.it_model_no,
 sp.sp_code,
 sp.sp_full_name,
 po_code,
 to_char(po_date, 'dd-Mon-YY') AS issued_po_date,
 (select sum(poit_qty) from ".ZKP_SQL."_tb_po_local_item where po_code=po.po_code and it_code=it.it_code) AS poit_qty,
 '#'||pl_no AS pl_no,
 inlc_idx,
 to_char(inlc_checked_date, 'dd-Mon-YY') AS arrival_date,
 inlc_checked_date,
 init_qty,
 'revise_po.php?_code='||po_code AS go_page
FROM
 ".ZKP_SQL."_tb_supplier_local AS sp
 JOIN ".ZKP_SQL."_tb_po_local AS po USING(sp_code)
 JOIN ".ZKP_SQL."_tb_in_local_v2 AS inpl USING(po_code)
 JOIN ".ZKP_SQL."_tb_in_local_item_v2 AS init USING(inlc_idx)
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE " . $strWhere . "
ORDER BY icat_pidx, icat_midx, it_code, po_code, inlc_checked_date";
echo "<pre>";
//echo $sql;
echo "</pre>";
// raw data
$rdIdx	= 0;
$cache	= array("","","","");
$rd	= array();
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'],	//0
		$col['it_code'],	//1
		$col['it_model_no'],	//2
		$col['sp_code'],	//3
		$col['sp_full_name'],	//4
		$col['po_code'],	//5
		$col['issued_po_date'],	//6
		$col['poit_qty'],	//7
		$col['pl_no'],		//8
		$col['inlc_idx'],	//9
		$col['arrival_date'],	//10
		$col['init_qty'],	//11
		$col['go_page']		//12
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

	if($cache[3] != $col['inlc_idx']) {
		$cache[3] = $col['inlc_idx'];
	}

	$group0[$col['icat_midx']][$col['it_code']][$col['po_code']][$col['inlc_idx']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$grand_total = array(0,0,0);

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
			<th width="12%">MODEL NO</th>
			<th width="17%">PO NO#</th>
			<th width="10%">PO DATE</th>
			<th>SUPPLIER</th>
			<th width="8%">P/O QTY</th>
			<th width="10%">ARRIVAL<br />DATE</th>
			<th width="5%">INV NO#</th>
			<th width="8%">RECEIVED<br />QTY</th>
			<th width="8%">BALANCE<br />QTY</th>
		</tr>\n
END;
	$cat_total	= array(0,0,0);
	$print_tr_1 = 0;
	//MODEL
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += count($group2);
		$rowSpan + 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".trim($rd[$rdIdx][1])."]<br/>".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');	//MODEL NO

		$item_total	= array(0,0,0);
		$print_tr_2 = 0;
		//PO NO
		foreach($group2 as $total3 => $group3) {
			$rowSpan = 0;
			array_walk_recursive($group3, 'getRowSpan');

			if($print_tr_2++ > 0) print "<tr>\n";
			cell_link("<span class=\"bar\">".$rd[$rdIdx][5]."</span>", ' align="center" valign="top" rowspan="'.$rowSpan.'"',
				' href="'.$rd[$rdIdx][12].'"');												//PO NO
			cell($rd[$rdIdx][6], ' align="center" valign="top" rowspan="'.$rowSpan.'"');	//PO DATE
			cell($rd[$rdIdx][4], ' valign="top" rowspan="'.$rowSpan.'"');					//SUPPLIER NAME
			cell(number_format($rd[$rdIdx][7]), ' align="right" valign="top" rowspan="'.$rowSpan.'"');	//TOTAL PO ITEM QTY

			$po_total		= array(0,0);
			$print_tr_3 = 0;
			//INCOMING PL
			foreach($group3 as $total4) {
				if($print_tr_3++ > 0) print "<tr>\n";
				cell($rd[$rdIdx][10], ' align="center"');					//ARRIVAL DATE
				cell($rd[$rdIdx][8], ' align="center"');					//INVOICE NO#
				cell(number_format($rd[$rdIdx][11]), ' align="right"');		//ARRIVED QTY
				cell('');
				print "</tr>\n";

				$po_total[0]	= $rd[$rdIdx][7];
				$po_total[1]	+= $rd[$rdIdx][11];
				$model_no	= $rd[$rdIdx][2];
				$rdIdx++;
			}
			print "<tr>\n";
			cell("INVOICE TOTAL", ' colspan="3" align="right" style="color:blue"');
			cell(number_format($po_total[0]), ' align="right" style="color:blue"');
			cell('');
			cell('');
			cell(number_format($po_total[1]), ' align="right" style="color:blue"');
			cell(number_format($po_total[0]-$po_total[1]), ' align="right" style="color:blue"');
			print "</tr>\n";

			$item_total[0]	+= $po_total[0];
			$item_total[1]	+= $po_total[1];
			$item_total[2]	+= $po_total[0]-$po_total[1];
		}
		print "<tr>\n";
		cell("<b>[$total2] $model_no</b>", ' colspan="4" align="right" style="color:brown"');
		cell(number_format($item_total[0]), ' align="right" style="color:brown"');
		cell('', ' align="right" style="color:brown"');
		cell('', ' align="right" style="color:brown"');
		cell(number_format($item_total[1]), ' align="right" style="color:brown"');
		cell(number_format($item_total[2]), ' align="right" style="color:brown"');
		print "</tr>\n";

		$cat_total[0]	+= $item_total[0];
		$cat_total[1]	+= $item_total[1];
		$cat_total[2]	+= $item_total[2]; 
	}
	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="4" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($cat_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell('', ' align="right" style="color:brown; background-color:lightyellow"');
	cell('', ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($cat_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($cat_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$grand_total[0]	+= $cat_total[0];
	$grand_total[1]	+= $cat_total[1];
	$grand_total[2]	+= $cat_total[2];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_f">
	<tr>
		<th width="12%">MODEL NO</th>
		<th width="17%">PO NO#</th>
		<th width="10%">PO DATE</th>
		<th>SUPPLIER</th>
		<th width="8%">P/O QTY</th>
		<th width="10%">ARRIVAL<br />DATE</th>
		<th width="5%">INV NO#</th>
		<th width="8%">RECEIVED<br />QTY</th>
		<th width="8%">BALANCE<br />QTY</th>
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="4" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell('', ' align="right" style="color:brown; background-color:lightyellow"');
cell('', ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>