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
  'PL-'||pl.pl_idx AS idx,
  pl.po_code AS po_code,
  pl.pl_inv_no AS invoice_no,
  pl.pl_inv_date AS invoice_date,
  to_char(pl.pl_inv_date, 'dd-Mon-yy') as issued_invoice_date,
  sp.sp_code AS sp_code,
  sp.sp_full_name AS sp_name,
  icat.icat_pidx AS icat_pidx,
  it.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  plit.plit_qty AS it_qty,
  ".ZKP_SQL."_arrivedQty(1, pl.pl_idx::text, it.it_code) AS it_arrived_qty,
  'confirm_pl.php?_code='||pl.pl_idx AS go_page
FROM
  ".ZKP_SQL."_tb_supplier AS sp
  JOIN ".ZKP_SQL."_tb_pl AS pl ON pl.pl_sp_code = sp.sp_code
  JOIN ".ZKP_SQL."_tb_pl_item AS plit USING(pl_idx)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE ".ZKP_SQL."_arrivedQty(1, pl.pl_idx::text, it.it_code) < plit.plit_qty and $strWherePL" ;

$sql_claim = "
SELECT
  'CL-'||cl.cl_idx AS idx,
  null AS po_code,
  cl.cl_inv_no AS invoice_no,
  cl.cl_inv_date AS invoice_date,
  to_char(cl.cl_inv_date, 'dd-Mon-yy') as issued_invoice_date,
  sp.sp_code AS sp_code,
  sp.sp_full_name AS sp_name,
  icat.icat_pidx AS icat_pidx,
  it.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  ".ZKP_SQL."_outstandingPLCLaim(cl_idx, it_code) AS it_qty,
  ".ZKP_SQL."_arrivedQty(2, cl.cl_idx::text, it.it_code) AS it_arrived_qty,
  'confirm_claim.php?_code='||cl.cl_idx AS go_page
FROM
  ".ZKP_SQL."_tb_supplier AS sp
  JOIN ".ZKP_SQL."_tb_claim AS cl ON cl.cl_sp_code = sp.sp_code
  JOIN ".ZKP_SQL."_tb_claim_item AS clit USING(cl_idx)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE ".ZKP_SQL."_arrivedQty(2, cl.cl_idx::text, it.it_code) < clit.clit_qty and  $strWhereClaim ";

$sql_local = "
SELECT
  po_code|| '-' ||pl_no AS idx,
  pl.po_code AS po_code,
  po_code|| ' #' ||pl_no AS invoice_no,
  pl.pl_date AS invoice_date,
  to_char(pl.pl_date, 'dd-Mon-yy') as issued_invoice_date,
  sp.sp_code AS sp_code,
  sp.sp_full_name AS sp_name,
  icat.icat_pidx AS icat_pidx,
  it.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  plit.plit_qty AS it_qty,
  ".ZKP_SQL."_arrivedQty(3, po_code|| '-' ||pl_no, it.it_code) AS it_arrived_qty,
  'confirm_pl_local.php?_code=' || po_code || '&_pl_no=' || pl_no AS go_page
FROM
  ".ZKP_SQL."_tb_supplier_local AS sp
  JOIN ".ZKP_SQL."_tb_po_local AS po USING(sp_code)
  JOIN ".ZKP_SQL."_tb_pl_local AS pl USING(po_code)
  JOIN ".ZKP_SQL."_tb_pl_local_item AS plit USING(po_code, pl_no)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE ".ZKP_SQL."_arrivedQty(3, po_code|| '-' ||pl_no, it.it_code) < plit_qty AND $strWhereLocal ";

$sql = "$sql_pl UNION $sql_claim UNION $sql_local ORDER BY icat_pidx, icat_midx, it_code, invoice_date";

echo "<pre>";
//echo $sql_pl;
echo "</pre>";

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
		$col['it_model_no'],	//2
		$col['po_code'],		//3
		$col['idx'],			//4
		$col['invoice_no'],		//5
		$col['issued_invoice_date'],	//6
		$col['sp_code'],		//7
		$col['sp_name'],		//8
		$col['it_qty']-$col['it_arrived_qty'],			//9
		$col['go_page']			//10
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

	if($cache[2] != $col['idx']) {
		$cache[2] = $col['idx'];
	}

	$group0[$col['icat_midx']][$col['it_code']][$col['idx']] = 1;
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
	<table width="85%" class="table_f">
		<tr>
			<th width="18%">MODEL NO</th>
			<th width="22%">INVOICE#</th>
			<th width="10%">INVOICE DATE</th>
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
		cell("[".trim($rd[$rdIdx][1])."]<br/>".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');	//model no

		$tot_db_ea  = 0;
		$print_tr_2 = 0;
		//ORDER
		foreach($group2 as $total3 => $group3) {

			if($print_tr_2++ > 0) print "<tr>\n";
			cell_link("<span class=\"bar\">".$rd[$rdIdx][5]."</span>", ' align="center"',
				' href="'.$rd[$rdIdx][10].'"'); 					//invoice no	
			cell($rd[$rdIdx][6], ' align="center"');				//invoice date
			cell("[".$rd[$rdIdx][7]."] ".$rd[$rdIdx][8]);			//supplier
			cell(number_format($rd[$rdIdx][9]), ' align="right"');	//qty
			print "</tr>\n";

			$tot_db_ea	+= $rd[$rdIdx][9];
			$model_no	= $rd[$rdIdx][2];
			$rdIdx++;
		}
		print "<tr>\n";
		cell("$model_no", ' colspan="3" align="right" style="color:darkblue"');
		cell(number_format($tot_db_ea), ' align="right" style="color:darkblue"');
		print "</tr>\n";

		$gTot_db_ea += $tot_db_ea;
	}
	
	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="4" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($gTot_db_ea), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";

	print "</table><br />\n";
	
	$ggTot_db_ea += $gTot_db_ea;
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="85%" class="table_f">
	<tr>
		<th width="18%">MODEL NO</th>
		<th width="22%">INVOICE#</th>
		<th width="10%">INVOICE DATE</th>
		<th>SUPPLIER</th>
		<th width="10%">QTY<br>(EA)</th>
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="4" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTot_db_ea), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>