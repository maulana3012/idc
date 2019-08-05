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
	$tmp_cl[] = "cl.cl_idx IS NULL";
} else if ($_source == 2) {
	$tmp_pl[] = "pl.pl_idx IS NULL";
}

if ($_type == 1) {
	$tmp_pl[] = "substr(pl.po_code,4,2) = 'IP'";
	$tmp_cl[] = "cl.cl_idx is null";
} else if ($_type == 2) {
	$tmp_pl[] = "substr(pl.po_code,4,3) = 'ICP'";
	$tmp_cl[] = "cl.cl_idx is not null";
}

if ($some_date != "") {
	$tmp_pl[] = "inpl.inpl_checked_date = DATE '$some_date'";
	$tmp_cl[] = "incl.incl_checked_date = DATE '$some_date'";
} else {
	$tmp_pl[] = "inpl.inpl_checked_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_cl[] = "incl.incl_checked_date BETWEEN DATE '$period_from' AND '$period_to'";
}

$tmp_pl[] = "pl.pl_ordered_by = ".$cboFilter[1][ZKP_URL][0][0];
$tmp_cl[] = "cl.cl_ordered_by = ".$cboFilter[1][ZKP_URL][0][0];

$strWherePL		= implode(" AND ", $tmp_pl);
$strWhereClaim	= implode(" AND ", $tmp_cl);

$sql_pl = "
SELECT
  'PL-'||pl.pl_idx AS idx,
  pl.pl_inv_no AS invoice_no,
  to_char(pl.pl_inv_date, 'dd-Mon-YY') AS issued_invoice_date,
  pl.pl_total_qty AS total_pl,
  sp.sp_code AS sp_code,
  sp.sp_full_name AS sp_name,
  'PL-'||inpl.inpl_idx AS inpl_idx,
  inpl.inpl_checked_date AS inpl_checked_date,
  to_char(inpl.inpl_checked_date, 'dd-Mon-YY') AS checked_date,
  icat.icat_pidx AS icat_pidx,
  icat.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  it.it_desc AS it_desc,
  init.init_qty AS it_qty,
  CASE
 	WHEN pl.pl_type = 1 THEN 'VAT'
 	WHEN pl.pl_type = 2 THEN 'NON'
  END AS pl_type,
 'revise_pl.php?_code='||pl.pl_idx AS go_page_pl,
 'detail_confirm_pl.php?_code='||pl.pl_idx||'&_inpl_idx='||inpl.inpl_idx AS go_page_inc
FROM
  ".ZKP_SQL."_tb_supplier AS sp
  JOIN ".ZKP_SQL."_tb_pl AS pl ON pl.pl_sp_code = sp.sp_code
  JOIN ".ZKP_SQL."_tb_in_pl AS inpl USING(pl_idx)
  JOIN ".ZKP_SQL."_tb_in_pl_item AS init USING(inpl_idx)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE $strWherePL ";

$sql_claim = "
SELECT
  'CL-'||cl.cl_idx AS idx,
  cl.cl_inv_no AS invoice_no,
  to_char(cl.cl_inv_date, 'dd-Mon-YY') AS issued_invoice_date,
  (select sum(clit_qty) from ".ZKP_SQL."_tb_claim_item where cl_idx=cl.cl_idx) AS total_pl,
  sp.sp_code AS sp_code,
  sp.sp_full_name AS sp_name,
  'CL-'||incl.incl_idx AS inpl_idx,
  incl.incl_checked_date AS inpl_checked_date,
  to_char(incl.incl_checked_date, 'dd-Mon-YY') AS checked_date,
  icat.icat_pidx AS icat_pidx,
  icat.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  it.it_desc AS it_desc,
  init.init_qty AS it_qty,
  'NON' AS pl_type,
  'revise_claim.php?_code='||incl.incl_idx AS go_page_pl,
  'detail_confirm_claim.php?_code='||cl.cl_idx||'&_incl_idx='||incl.incl_idx AS go_page_inc
FROM
  ".ZKP_SQL."_tb_supplier AS sp
  JOIN ".ZKP_SQL."_tb_claim AS cl ON cl.cl_sp_code = sp.sp_code
  JOIN ".ZKP_SQL."_tb_in_claim AS incl USING(cl_idx)
  JOIN ".ZKP_SQL."_tb_in_claim_item AS init USING(incl_idx)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE $strWhereClaim ";

$sql = "$sql_pl UNION $sql_claim ORDER BY icat_pidx, icat_midx, it_code, idx, inpl_checked_date, inpl_idx";

// raw data
$cache	= array("","","",""); // 3th level
$group0 = array();
$rd		= array();
$rdIdx	= 0;
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'],				//0
		$col['it_code'],				//1
		$col['it_model_no'],			//2
		$col['idx'],					//3
		$col['invoice_no'],				//4
		$col['issued_invoice_date'],	//5
		$col['total_pl'],				//6
		$col['sp_code'],				//7
		$col['sp_name'],				//8
		$col['inpl_idx'],				//9
		$col['checked_date'],			//10
		$col['it_qty'],					//11
		$col['pl_type'],				//12
		$col['go_page_pl'],				//13
		$col['go_page_inc'],			//14
		$col['icat_pidx']				//15
	);

	//1st grouping
	if($cache[0] != $col['icat_pidx']."-".$col['icat_midx']) {
		$cache[0] = $col['icat_pidx']."-".$col['icat_midx'];
		$group0[$col['icat_pidx']."-".$col['icat_midx']] = array();
	}

	if($cache[1] != $col['it_code']) {
		$cache[1] = $col['it_code'];
		$group0[$col['icat_pidx']."-".$col['icat_midx']][$col['it_code']] = array();
	}

	if($cache[2] != $col['idx']) {
		$cache[2] = $col['idx'];
		$group0[$col['icat_pidx']."-".$col['icat_midx']][$col['it_code']][$col['idx']] = array();
	}

	if($cache[3] != $col['inpl_idx']) {
		$cache[3] = $col['inpl_idx'];
	}

	$group0[$col['icat_pidx']."-".$col['icat_midx']][$col['it_code']][$col['idx']][$col['inpl_idx']] = 1;
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
$gg_total = 0;

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
			<th width="15%">MODEL NO</th>
			<th width="10%">INVOICE#</th>
			<th width="12%">INVOICE<br />DATE</th>
			<th width="7%">PL TYPE</th>
			<th>SUPPLIER</th>
			<th width="12%">ARRIVAL DATE</th>
			<th width="8%">QTY</th>
		</tr>\n
END;

	$g_total 	= 0;
	$print_tr_1 = 0;
	print "<tr>\n";
	//MODEL
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".trim($rd[$rdIdx][1])."]<br/>".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');	//model no

		$total		= 0;
		$print_tr_2 = 0;
		//INVOICE
		foreach($group2 as $total3 => $group3) {
			$rowSpan = 0;
			array_walk_recursive($group3, 'getRowSpan');

			if($print_tr_2++ > 0) print "<tr>\n";
			cell_link($rd[$rdIdx][4], ' align="center" valign="top" rowspan="'.$rowSpan.'"',
				' href="'.$rd[$rdIdx][13].'"'); 												//invoice no	
			cell($rd[$rdIdx][5], ' align="center" valign="top" rowspan="'.$rowSpan.'"');		//invoice date
			cell($rd[$rdIdx][12], ' align="center" valign="top" rowspan="'.$rowSpan.'"');		//PL type
			cell("[". trim($rd[$rdIdx][7]) ."] ".$rd[$rdIdx][8],' valign="top" rowspan="'.$rowSpan.'"');//supplier

			$print_tr_3 = 0;
			//INCOMING QTY
			foreach($group3 as $total4) {
				if($print_tr_3++ > 0) print "<tr>\n";
				cell_link("<span class=\"bar\">".$rd[$rdIdx][10]."</span>", ' align="center"',
				' href="'.$rd[$rdIdx][14].'"');								//incoming date
				cell(number_format($rd[$rdIdx][11]), ' align="right"');		//incoming qty
				print "</tr>\n";

				$total		+= $rd[$rdIdx][11];
				$item_name	= $rd[$rdIdx][2];
				$rdIdx++;
			}
		}
		print "<tr>\n";
		cell("[".trim($total2)."] $item_name", ' colspan="5" align="right" style="color:darkblue"');
		cell(number_format($total), ' align="right" style="color:darkblue"');
		print "</tr>\n";

		$g_total += $total;
	}

	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="6" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($g_total), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";

	print "</table><br />\n";
	
	$gg_total += $g_total;
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_f">
		<tr>
			<th width="15%">MODEL NO</th>
			<th width="10%">INVOICE#</th>
			<th width="12%">INVOICE<br />DATE</th>
			<th width="7%">PL TYPE</th>
			<th>SUPPLIER</th>
			<th width="13%">ARRIVAL DATE</th>
			<th width="8%">QTY</th>
		</tr>\n
END;
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="6" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($gg_total), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>