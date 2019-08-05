
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
$tmp_pl = array();
$tmp_cl = array();
$tmp_lk	= array();

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp_pl[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_cl[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_lk[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}


if ($_source == 1) {
	$tmp_cl[] = "cl.cl_idx is null";
	$tmp_lk[] = "po_code is null";
} else if ($_source == 2) {
	$tmp_pl[] = "pl.pl_idx is null";
	$tmp_cl[] = "cl.cl_idx is null";
} else if ($_source == 3) {
	$tmp_pl[] = "pl.pl_idx is null";
	$tmp_lk[] = "po_code is null";
}

if ($some_date != "") {
	$tmp_pl[] = "pl.pl_inv_date = DATE '$some_date'";
	$tmp_cl[] = "cl.cl_inv_date = DATE '$some_date'";
	$tmp_lk[] = "pl.pl_date = DATE '$some_date'";
} else {
	$tmp_pl[] = "pl.pl_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_cl[] = "cl.cl_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_lk[] = "pl.pl_date BETWEEN DATE '$period_from' AND '$period_to'";
}

$strWherePL		= implode(" AND ", $tmp_pl);
$strWhereClaim	= implode(" AND ", $tmp_cl);
$strWhereLocal	= implode(" AND ", $tmp_lk);

$sql_pl = "
SELECT
 sp.sp_code AS sp_code,
 sp.sp_full_name AS sp_name,
 'PL-'||pl.pl_idx AS idx,
 pl.po_code AS po_code,
 pl.pl_inv_no AS invoice_no,
 pl.pl_inv_date AS invoice_date,
 to_char(pl.pl_inv_date, 'dd-Mon-YY') AS issued_invoice_date,
 it.it_code AS it_code,
 it.it_model_no AS it_model_no,
 pepl.pepl_qty AS it_qty,
 'confirm_pl.php?_code='||pl.pl_idx AS go_page
FROM
 ".ZKP_SQL."_tb_supplier AS sp
 JOIN ".ZKP_SQL."_tb_pl AS pl ON pl.pl_sp_code = sp.sp_code
 JOIN ".ZKP_SQL."_tb_pending_pl AS pepl USING(pl_idx)
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE pepl.pepl_qty > 0 AND $strWherePL ";

$sql_claim = "
SELECT
 sp.sp_code AS sp_code,
 sp.sp_full_name AS sp_name,
 'CL-'||cl.cl_idx AS idx,
 null AS po_code,
 cl.cl_inv_no AS invoice_no,
 cl.cl_inv_date AS invoice_date,
 to_char(cl.cl_inv_date, 'dd-Mon-YY') AS issued_invoice_date,
 it.it_code AS it_code,
 it.it_model_no AS it_model_no,
 ".ZKP_SQL."_outstandingPLCLaim(cl_idx, it_code) AS it_qty,
 'confirm_claim.php?_code='||cl.cl_idx AS go_page
FROM
 ".ZKP_SQL."_tb_supplier AS sp
 JOIN ".ZKP_SQL."_tb_claim AS cl ON cl.cl_sp_code = sp.sp_code
 JOIN ".ZKP_SQL."_tb_claim_item AS clit USING(cl_idx)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)

WHERE ".ZKP_SQL."_arrivedQty(2, cl.cl_idx::text, it.it_code) < clit.clit_qty AND $strWhereClaim";

$sql_local = "
SELECT
 sp.sp_code AS sp_code,
 sp.sp_full_name AS sp_name,
 po_code|| '-' ||pl_no AS idx,
 pl.po_code AS po_code,
 po_code|| ' #' ||pl_no AS invoice_no,
 pl.pl_date AS invoice_date,
 to_char(pl.pl_date, 'dd-Mon-YY') AS issued_invoice_date,
 it.it_code AS it_code,
 it.it_model_no AS it_model_no,
 ".ZKP_SQL."_outstandingPLLocal(po_code, pl_no, it_code) AS it_qty,
 'confirm_pl_local.php?_code=' || po_code || '&_pl_no=' || pl_no AS go_page
FROM
 ".ZKP_SQL."_tb_supplier_local AS sp
 JOIN ".ZKP_SQL."_tb_po_local AS po USING(sp_code)
 JOIN ".ZKP_SQL."_tb_pl_local AS pl USING(po_code)
 JOIN ".ZKP_SQL."_tb_pl_local_item AS plit USING(po_code, pl_no)
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE ".ZKP_SQL."_arrivedQty(3, po_code|| '-' ||pl_no, it.it_code) < plit_qty AND $strWhereLocal ";

$sql = "$sql_pl UNION $sql_claim UNION $sql_local ORDER BY sp_code, idx, it_code";
/*
echo "<pre>";
echo $sql;
echo "</pre>";
exit;
*/
// raw data
$rd = array();
$rdIdx = 0;

$cache = array("","","");
$group0 = array();

$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['sp_code'],			//0
		$col['sp_name'],			//1
		$col['po_code'],			//2
		$col['idx'],				//3
		$col['invoice_no'],			//4
		$col['issued_invoice_date'],//5
		$col['it_code'],			//6
		$col['it_model_no'],		//7
		$col['it_qty'],				//8
		$col['go_page']				//9
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
$ggTot_db_ea = 0;

//GROUP
foreach ($group0 as $total1 => $group1) {

	echo "<span class=\"comment\"><b> SUPPLIER : ". $rd[$rdIdx][1] . "</b></span>\n";
	print <<<END
	<table width="80%" class="table_f">
		<tr>
			<th width="22%">INVOICE NO#</th>
			<th width="15%">INVOICE DATE</th>
			<th>MODEL NO</th>
			<th width="10%">QTY<br>(EA)</th>
		</tr>\n
END;

	$gTot_db_ea = 0;
	$print_tr_1 = 0;

	print "<tr>\n";
	//PL
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell_link("<span class=\"bar\">".$rd[$rdIdx][4]."</span>", ' align="center" valign="top" rowspan="'.$rowSpan.'"',
			' href="'.$rd[$rdIdx][9].'"');												//invoice no
		cell($rd[$rdIdx][5], ' valign="top" align="center" rowspan="'.$rowSpan.'"');	//invoice date

		$print_tr_2	= 0;
		$tot_db_ea	= 0;
		//ITEM
		foreach($group2 as $group3) {

			if($print_tr_2++ > 0) print "<tr>\n";
			cell("[". trim($rd[$rdIdx][6]) ."] ".$rd[$rdIdx][7]);		//model no
			cell(number_format($rd[$rdIdx][8]), ' align="right"');		//qty
			print "</tr>\n";

			$tot_db_ea	+= $rd[$rdIdx][8];
			$invoice	= $rd[$rdIdx][4];
			$supplier	= $rd[$rdIdx][1];
			$rdIdx++;
		}
		print "<tr>\n";
		cell("<b>TOTAL $invoice</b>", ' align="right" style="color:darkblue"');
		cell(number_format($tot_db_ea), ' align="right" style="color:darkblue"');
		print "</tr>\n";
		$gTot_db_ea += $tot_db_ea;
	}

	print "<tr>\n";
	cell("<b>$supplier</b>", ' colspan="3" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($gTot_db_ea), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$ggTot_db_ea  += $gTot_db_ea;
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="80%" class="table_f">
	<tr>
		<th width="22%">INVOICE NO#</th>
		<th width="15%">INVOICE DATE</th>
		<th>MODEL NO</th>
		<th width="10%">QTY<br>(EA)</th>
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="3" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTot_db_ea), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>