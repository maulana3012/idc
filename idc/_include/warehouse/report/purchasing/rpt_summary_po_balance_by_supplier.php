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
$display_css["f"]		= "color:black";
$display_css["t"]		= "background-color:#F0F5F6;color:black";

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
	$tmp[] = "po.po_date = DATE '$some_date'";
} else {
	$tmp[] = "po.po_date BETWEEN DATE '$period_from' AND '$period_to'";
}

$strWhere = implode(" AND ", $tmp);

$sql = "
SELECT
 sp.sp_code,
 sp.sp_full_name,
 po.po_code,
 to_char(po.po_date, 'dd-Mon-YY') AS issued_po_date,
 ".ZKP_SQL."_statusPOLocal(po.po_code) AS status,
 it.it_code,
 it.it_model_no,
 (select sum(poit_qty) from ".ZKP_SQL."_tb_po_local_item where po_code=po.po_code and it_code=it.it_code) AS po_qty,
 (select sum(init_qty) from ".ZKP_SQL."_tb_in_local join ".ZKP_SQL."_tb_in_local_item using(inlc_idx) where po_code=po.po_code and it_code=it.it_code) AS in_qty1,
 (select sum(init_qty) from ".ZKP_SQL."_tb_in_local_v2 join ".ZKP_SQL."_tb_in_local_item_v2 using(inlc_idx) where po_code=po.po_code and it_code=it.it_code) AS in_qty2,
 '# '||".ZKP_SQL."_lastArrived('PL', 'Local', 'Invoice', po_code, it.it_code) AS last_arrived_inv,
 ".ZKP_SQL."_lastArrived('PL', 'Local', 'Date', po_code, it.it_code) AS last_arrived_date,
 'revise_po.php?_code='||po_code AS go_page
FROM
 ".ZKP_SQL."_tb_supplier_local AS sp
 JOIN ".ZKP_SQL."_tb_po_local AS po USING(sp_code)
 JOIN ".ZKP_SQL."_tb_po_local_item AS poit USING(po_code)
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE " . $strWhere . "
GROUP BY sp.sp_code, sp.sp_full_name, po.po_code, issued_po_date, it.it_code, it.it_model_no, poit.it_code
ORDER BY sp_code, po_code, it_code
";
echo "<pre>";
//echo $sql;
echo "</pre>";
// raw data
$cache	= array("","","");
$rd	= array();
$group0 = array();
$rdIdx	= 0;
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['sp_code'],		//0
		$col['sp_full_name'],		//1
		$col['po_code'],		//2
		$col['issued_po_date'],		//3
		$col['status'],			//4
		$col['it_code'],		//5
		$col['it_model_no'],		//6
		$col['po_qty'],			//7
		$col['in_qty1']+$col['in_qty2'],			//8
		$col['po_qty']-($col['in_qty1']+$col['in_qty2']),	//9
		$col['last_arrived_inv'],	//10
		$col['last_arrived_date'],	//11
		$col['go_page']			//12
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
	}

	$group0[$col['sp_code']][$col['po_code']][$col['it_code']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$gg_total = array(0,0,0);

//GROUP
foreach ($group0 as $total1 => $group1) {

	echo "<span class=\"comment\"><b> SUPPLIER : [". $total1. "] {$rd[$rdIdx][1]}</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th width="17%">PO NO#</th>
			<th width="10%">PO DATE</th>
			<th width="10%">STATUS</th>
			<th>MODEL NO</th>
			<th width="8%">P/O Qty</th>
			<th width="8%">Received<br />Qty</th>
			<th width="8%">Balance<br />Qty</th>
			<th width="5%">Last Inv No#</th>
			<th width="10%">Last Arrival Date</th>
		</tr>\n
END;

	$g_total = array(0,0,0);
	$print_tr_1 = 0;

	print "<tr>\n";

	//PO
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell_link("<span class=\"bar\">".$rd[$rdIdx][2]."</span>", ' style="'.$display_css[$rd[$rdIdx][4]].'" align="center" valign="top" rowspan="'.$rowSpan.'"',
			' href="'.$rd[$rdIdx][12].'"');				//PO NO
		cell($rd[$rdIdx][3], ' style="'.$display_css[$rd[$rdIdx][4]].'" valign="top" align="center" rowspan="'.$rowSpan.'"');	//PO DATE
		cell(($rd[$rdIdx][4] == "t") ? "Complete" : "Incomplete", ' style="'.$display_css[$rd[$rdIdx][4]].'" valign="top" align="center" rowspan="'.$rowSpan.'"');	//PO STATUS

		$print_tr_2	= 0;
		$total	= array(0,0,0);
		//ITEM
		foreach($group2 as $group3) {
			if($print_tr_2++ > 0) print "<tr>\n";
			cell("[".$rd[$rdIdx][5]."] ".$rd[$rdIdx][6], ' style="'.$display_css[$rd[$rdIdx][4]].'"');			//MODEL NO
			cell(number_format($rd[$rdIdx][7]), ' style="'.$display_css[$rd[$rdIdx][4]].'" align="right"');		//PO BALANCE QTY
			cell(($rd[$rdIdx][8]=='')?'':number_format($rd[$rdIdx][8]), ' style="'.$display_css[$rd[$rdIdx][4]].'" align="right"');		//RECEIVED QTY
			cell(number_format($rd[$rdIdx][9]), ' style="'.$display_css[$rd[$rdIdx][4]].'" align="right"');		//OUTSTANDING QTY
			cell($rd[$rdIdx][10],' style="'.$display_css[$rd[$rdIdx][4]].'" align="center"');					//LAST INVOICE NO
			cell(($rd[$rdIdx][11] == '') ? '' : date('d-M-Y',strtotime($rd[$rdIdx][11])),' style="'.$display_css[$rd[$rdIdx][4]].'" align="center"');	//LAST INVOICE DATE
			print "</tr>\n";

			$total[0]	+= $rd[$rdIdx][7];
			$total[1]	+= $rd[$rdIdx][8];
			$total[2]	+= $rd[$rdIdx][7]-$rd[$rdIdx][8];
			$customer	= $rd[$rdIdx][1];
			$css		= $rd[$rdIdx][4];
			$rdIdx++;
		}

		print "<tr>\n";
		cell("INVOICE TOTAL", ' style="'.$display_css[$css].';color:darkblue" align="right"');
		cell(number_format($total[0]), ' style="'.$display_css[$css].';color:darkblue" align="right"');
		cell(number_format($total[1]), ' style="'.$display_css[$css].';color:darkblue" align="right"');
		cell(number_format($total[2]), ' style="'.$display_css[$css].';color:darkblue" align="right"');
		cell('', ' style="'.$display_css[$css].';color:darkblue" align="right"');
		cell('', ' style="'.$display_css[$css].';color:darkblue" align="right"');
		print "</tr>\n";

		$g_total[0] += $total[0];
		$g_total[1] += $total[1];
		$g_total[2] += $total[2];
	}

	print "<tr>\n";
	cell("<b>$customer</b>", ' colspan="4" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($g_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($g_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($g_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell("", ' align="right" style="color:brown; background-color:lightyellow"');
	cell("", ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$gg_total[0]  += $g_total[0];
	$gg_total[1]  += $g_total[1];
	$gg_total[2]  += $g_total[2];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_f">
	<tr>
		<th width="17%">PO NO#</th>
		<th width="10%">PO DATE</th>
		<th width="10%">STATUS</th>
		<th>MODEL NO</th>
		<th width="8%">P/O Qty</th>
		<th width="8%">Received<br />Qty</th>
		<th width="8%">Balance<br />Qty</th>
		<th width="5%">Last Inv No#</th>
		<th width="10%">Last Arrival Date</th>
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="4" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($gg_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($gg_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($gg_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
cell("", ' align="right" style="color:brown; background-color:lightyellow"');
cell("", ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>