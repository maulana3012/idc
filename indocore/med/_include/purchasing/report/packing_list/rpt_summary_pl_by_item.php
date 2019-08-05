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
	$tmp_pl[] = "substr(pl.po_code,4,4) = 'M-IP'";
	$tmp_cl[] = "cl_idx IS NULL";
} else if ($_type == 2) {
	$tmp_pl[] = "substr(pl.po_code,4,5) = 'M-ICP'";
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
  'PL-'||pl.pl_idx AS idx,
  pl.po_code AS po_code,
  pl.pl_inv_no AS invoice_no,
  pl.pl_inv_date AS invoice_date,
  to_char(pl.pl_inv_date, 'dd-Mon-yy') as invoice_issued_date,
  pl.pl_shipment_mode AS shipment_mode,
  sp.sp_code AS sp_code,
  sp.sp_full_name AS sp_name,
  icat.icat_pidx AS icat_pidx,
  it.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  plit.plit_qty AS it_qty,
  'revise_pl.php?_code='||pl_idx AS go_page_pl,
  '../purchasing/revise_po.php?_code='||po_code AS go_page_po
FROM
  ".ZKP_SQL."_tb_supplier AS sp
  JOIN ".ZKP_SQL."_tb_pl AS pl ON pl.pl_sp_code = sp.sp_code
  JOIN ".ZKP_SQL."_tb_pl_item AS plit USING(pl_idx)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE ". $strWherePL ;

$sql_claim = "
SELECT
  'CL-'||cl.cl_idx AS idx,
  null AS po_code,
  cl.cl_inv_no AS invoice_no,
  cl.cl_inv_date AS invoice_date,
  to_char(cl.cl_inv_date, 'dd-Mon-yy') as invoice_issued_date,
  cl.cl_shipment_mode AS shipment_mode,
  sp.sp_code AS sp_code,
  sp.sp_full_name AS sp_name,
  icat.icat_pidx AS icat_pidx,
  it.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  clit.clit_qty AS it_qty,
  'revise_claim.php?_code='||cl_idx AS go_page_pl,
  null AS go_page_po
FROM
  ".ZKP_SQL."_tb_supplier AS sp
  JOIN ".ZKP_SQL."_tb_claim AS cl ON cl.cl_sp_code = sp.sp_code
  JOIN ".ZKP_SQL."_tb_claim_item AS clit USING(cl_idx)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE ". $strWhereClaim ;

$sql = "$sql_pl UNION $sql_claim ORDER BY icat_pidx, icat_midx, it_code, idx";

// raw data
$rd = array();
$rdIdx = 0;

$cache = array("","",""); // 3th level
$group0 = array();

$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'],			//0
		$col['it_code'],			//1
		$col['it_model_no'],		//2
		$col['po_code'],			//3
		$col['idx'],				//4
		$col['invoice_no'],			//5
		$col['invoice_issued_date'],	//6
		$col['shipment_mode'],		//7
		$col['sp_code'],			//8
		$col['sp_name'],			//9
		$col['it_qty'],				//10
		$col['go_page_pl'],			//11
		$col['go_page_po'],			//12
		$col['icat_pidx']			//13
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

	if($cache[2] != $col['idx']) {
		$cache[2] = $col['idx'];
	}

	$group0[$col['icat_pidx'].$col['icat_midx']][$col['it_code']][$col['idx']] = 1;
}



//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$ggTot_db_ea = 0;
$ggTot_db_amt = 0;

//GROUP
foreach ($group0 as $total1 => $group1) {

	//get category path from current icat_midx.
	$path = executeSP(ZKP_SQL."_getCategoryPath", $rd[$rdIdx][0]);
	eval(html_entity_decode($path[0]));	
	$path = array_reverse($path);
	$total1 = $path[1][4]." > ".$path[2][4]." > ".$path[3][4] ;

	echo "<span class=\"comment\"><b> CATEGORY: ". $total1. "</b></span>\n";
	print <<<END
	<table width="100%" class="table_c">
		<tr>
			<th width="15%">MODEL NO</th>
			<th width="12%">INVOICE#</th>
			<th width="10%">INVOICE DATE</th>
			<th width="12%">PO#</th>
			<th width="12%">SHIPMENT<br />MODE</th>
			<th>SUPPLIER</th>
			<th width="10%">QTY<br>(EA)</th>
		</tr>\n
END;

	$gTot_db_ea = 0;
	$print_tr_1 = 0;

	print "<tr>\n";
	//MODEL
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".trim($rd[$rdIdx][1])."]<br/>".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');		//model no

		$tot_db_ea = 0;

		$print_tr_2 = 0;
		//ORDER
		foreach($group2 as $total3 => $group3) {
			if($print_tr_2++ > 0) print "<tr>\n";
			cell_link("<span class=\"bar\">".$rd[$rdIdx][5]."</span>", ' align="center"',
				' href="'.$rd[$rdIdx][11].'"');											//invoice no
			cell($rd[$rdIdx][6], ' align="center" ');									//invoice date
			cell_link("<span class=\"bar\">".$rd[$rdIdx][3]."</span>", ' align="center"',
				' href="'.$rd[$rdIdx][12].'"');											//PO code
			cell(strtoupper($rd[$rdIdx][7]), ' align="center" ');						//shipment mode
			cell("[".trim($rd[$rdIdx][8])."] ".cut_string($rd[$rdIdx][9],32));								//supplier
			cell(number_format($rd[$rdIdx][10]), ' align="right"');						//qty
			print "</tr>\n";

			$tot_db_ea	+= $rd[$rdIdx][10];
			$rdIdx++;
		}
		print "<tr>\n";
		cell("$total2", ' colspan="5" align="right" style="color:darkblue"');
		cell(number_format($tot_db_ea), ' align="right" style="color:darkblue"');
		print "</tr>\n";

		$gTot_db_ea += $tot_db_ea;
	}
	
	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="6" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($gTot_db_ea), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";

	print "</table><br />\n";
	
	$ggTot_db_ea += $gTot_db_ea;
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_c">
		<tr>
			<th width="15%">MODEL NO</th>
			<th width="12%">INVOICE#</th>
			<th width="10%">INVOICE DATE</th>
			<th width="12%">PO#</th>
			<th width="12%">SHIPMENT<br />MODE</th>
			<th>SUPPLIER</th>
			<th width="10%">QTY<br>(EA)</th>
		</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="6" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTot_db_ea), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>