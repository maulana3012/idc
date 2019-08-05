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
$tmp_pl = array();
$tmp_cl = array();

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp_pl[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_cl[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if ($_source == 1) {
	$tmp_cl[] = "cl_idx IS NULL";
} else if ($_source == 2) {
	$tmp_pl[] = "pl_idx IS NULL";
}

if ($_type == 1) {
	$tmp_pl[] = "pl.pl_type = 1";
	$tmp_cl[] = "cl_idx IS NULL";
} else if ($_type == 2) {
	$tmp_pl[] = "pl.pl_type = 2";
	$tmp_cl[] = "cl_idx IS NOT NULL";
}

if ($some_date != "") {
	$tmp_pl[] = "pl.pl_inv_date = DATE '$some_date'";
	$tmp_cl[] = "cl.cl_inv_date = DATE '$some_date'";
} else {
	$tmp_pl[] = "pl.pl_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_cl[] = "cl.cl_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
}

$tmp_pl[] = "pl.pl_ordered_by = ".$cboFilter[1][ZKP_URL][0][0];
$tmp_cl[] = "cl.cl_ordered_by = ".$cboFilter[1][ZKP_URL][0][0];

$strWherePL		= implode(" AND ", $tmp_pl);
$strWhereClaim	= implode(" AND ", $tmp_cl);

$sql_pl = "
SELECT
 sp.sp_code AS sp_code,
 sp.sp_full_name AS sp_name,
 'PL-'||pl.pl_idx AS idx,
 ".ZKP_SQL."_statusPL(1,pl.pl_idx) AS status,
 pl.po_code AS po_code,
 pl.pl_inv_no AS invoice_no,
 pl.pl_inv_date AS invoice_date,
 to_char(pl.pl_inv_date, 'dd-Mon-YY') AS issued_invoice_date,
 it.it_code AS it_code,
 it.it_model_no AS it_model_no,
 it.it_desc AS it_desc,
 plit.plit_qty AS it_qty,
 ".ZKP_SQL."_arrivedQty(1, pl.pl_idx::varchar, plit.it_code) AS arrived_qty,
 plit.plit_qty - ".ZKP_SQL."_arrivedQty(1, pl.pl_idx::varchar, plit.it_code) AS remain_qty,
 ".ZKP_SQL."_lastArrived('PL', 'PL', 'Date', pl.pl_idx::varchar, it.it_code) AS last_arrived_date,
 'revise_pl.php?_code='||pl.pl_idx AS go_page
FROM
 ".ZKP_SQL."_tb_supplier AS sp
 JOIN ".ZKP_SQL."_tb_pl AS pl ON pl.pl_sp_code = sp.sp_code
 JOIN ".ZKP_SQL."_tb_pl_item AS plit USING(pl_idx)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE " . $strWherePL ;

$sql_claim = "
SELECT
 sp.sp_code AS sp_code,
 sp.sp_full_name AS sp_name,
 'CL-'||cl.cl_idx AS idx,
 ".ZKP_SQL."_statusPL(2,cl.cl_idx) AS status,
 null AS po_code,
 cl.cl_inv_no AS invoice_no,
 cl.cl_inv_date AS invoice_date,
 to_char(cl.cl_inv_date, 'dd-Mon-YY') AS issued_invoice_date,
 it.it_code AS it_code,
 it.it_model_no AS it_model_no,
 it.it_desc AS it_desc,
 clit.clit_qty AS it_qty,
 ".ZKP_SQL."_arrivedQty(2, cl.cl_idx::varchar, clit.it_code) AS arrived_qty,
 clit.clit_qty - ".ZKP_SQL."_arrivedQty(2, cl.cl_idx::varchar, clit.it_code) AS remain_qty,
 ".ZKP_SQL."_lastArrived('PL', 'Claim', 'Date', cl.cl_idx::varchar, it.it_code) AS last_arrived_date,
 'revise_claim.php?_code='||cl.cl_idx AS go_page
FROM
 ".ZKP_SQL."_tb_supplier AS sp
 JOIN ".ZKP_SQL."_tb_claim AS cl ON cl.cl_sp_code = sp.sp_code
 JOIN ".ZKP_SQL."_tb_claim_item AS clit USING(cl_idx)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE " . $strWhereClaim ;

$sql = "$sql_pl UNION $sql_claim ORDER BY sp_code, idx, invoice_date, it_code";
echo "<pre>";
//var_dump($sql);
echo "</pre>";
// raw data
$cache	= array("","","");
$rd		= array();
$group0 = array();
$rdIdx	= 0;
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['sp_code'],		//0
		$col['sp_name'],		//1
		$col['po_code'],		//2
		$col['idx'],			//3
		$col['invoice_no'],		//4
		$col['issued_invoice_date'],	//5
		$col['it_code'],		//6
		$col['it_model_no'],		//7
		$col['it_desc'],		//8
		$col['it_qty'],			//9
		$col['arrived_qty'],		//10
		$col['remain_qty'],		//11
		$col['last_arrived_date'],	//12
		$col['status'],			//13
		$col['go_page']			//14
	);

	//1st grouping
	if($cache[0] != $col['sp_code']) {
		$cache[0] = $col['sp_code'];
		$group0[$col['sp_code']] = array();
	}

	if($cache[1] != $col['idx']) {
		$cache[1] = $col['idx'];
		$group0[$col['sp_code']][$col['idx']] = array();
	}

	if($cache[2] != $col['it_code']) {
		$cache[2] = $col['it_code'];
	}
	$group0[$col['sp_code']][$col['idx']][$col['it_code']] = 1;
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
	<table width="100%" class="table_c">
		<tr>
			<th width="10%">INVOICE NO#</th>
			<th width="10%">INV DATE</th>
			<th width="10%">PO NO#</th>
			<th>MODEL NO</th>
			<th width="8%">P/L Qty</th>
			<th width="8%">Received Qty</th>
			<th width="8%">BALANCE</th>
			<th width="10%">Last Arrival<br />Date</th>
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
		cell_link("<span class=\"bar\">".$rd[$rdIdx][4]."</span>", ' style="'.$display_css[$rd[$rdIdx][13]].'" align="center" valign="top" rowspan="'.$rowSpan.'"',
			' href="'.$rd[$rdIdx][14].'"');	//invoice no
		cell($rd[$rdIdx][5], '  style="'.$display_css[$rd[$rdIdx][13]].'" valign=""top align="center" rowspan="'.$rowSpan.'"');	//invoice date
		cell($rd[$rdIdx][2], '  style="'.$display_css[$rd[$rdIdx][13]].'" valign=""top align="center" rowspan="'.$rowSpan.'"');	//PO no

		$inv_no = $rd[$rdIdx][4];
		$print_tr_2	= 0;
		$total	= array(0,0,0);
		//ITEM
		foreach($group2 as $group3) {

			if($print_tr_2++ > 0) print "<tr>\n";
			cell("[". trim($rd[$rdIdx][6]) ."] ".$rd[$rdIdx][7], '  style="'.$display_css[$rd[$rdIdx][13]].'"');		//model no
			cell(number_format($rd[$rdIdx][9]), '  style="'.$display_css[$rd[$rdIdx][13]].'" align="right"');		//PL balance qty
			cell(number_format($rd[$rdIdx][10]), '  style="'.$display_css[$rd[$rdIdx][13]].'" align="right"');		//received qty
			cell(number_format($rd[$rdIdx][11]), '  style="'.$display_css[$rd[$rdIdx][13]].'" align="right"');		//outstanding qty
			cell(($rd[$rdIdx][12] == '') ? '' : date('d-M-y',strtotime($rd[$rdIdx][12])), '  style="'.$display_css[$rd[$rdIdx][13]].'" align="center"');	//last arrival date
			print "</tr>\n";

			$total[0]	+= $rd[$rdIdx][9];
			$total[1]	+= $rd[$rdIdx][10];
			$total[2]	+= $rd[$rdIdx][11];
			$css		= $rd[$rdIdx][13];
			$rdIdx++;
		}
		
		print "<tr>\n";
		cell("<b>TOTAL $inv_no</b>", ' style="'.$display_css[$css].';color:darkblue" align="right" style="color:darkblue"');
		cell(number_format($total[0]), ' style="'.$display_css[$css].';color:darkblue" align="right" style="color:darkblue"');
		cell(number_format($total[1]), ' style="'.$display_css[$css].';color:darkblue" align="right" style="color:darkblue"');
		cell(number_format($total[2]), ' style="'.$display_css[$css].';color:darkblue" align="right" style="color:darkblue"');
		cell('', ' style="'.$display_css[$css].';color:darkblue" align="right" style="color:darkblue"');
		print "</tr>\n";

		$g_total[0] += $total[0];
		$g_total[1] += $total[1];
		$g_total[2] += $total[2];
	}

	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="4" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($g_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($g_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($g_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell("", ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$gg_total[0]  += $g_total[0];
	$gg_total[1]  += $g_total[1];
	$gg_total[2]  += $g_total[2];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
	print <<<END
	<table width="100%" class="table_c">
		<tr>
			<th width="10%">INVOICE NO#</th>
			<th width="10%">INV DATE</th>
			<th width="10%">PO NO#</th>
			<th>MODEL NO</th>
			<th width="8%">P/L Qty</th>
			<th width="8%">Received Qty</th>
			<th width="8%">BALANCE</th>
			<th width="10%">Last Arrival<br />Date</th>
		</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="4" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($gg_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($gg_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($gg_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
cell("", ' colspan="3" align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>