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
	$tmp_pl[] = "inpl.inpl_checked_date = DATE '$some_date'";
	$tmp_cl[] = "incl.incl_checked_date = DATE '$some_date'";
	$tmp_lk[] = "inlc.inlc_checked_date = DATE '$some_date'";
} else {
	$tmp_pl[] = "inpl.inpl_checked_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_cl[] = "incl.incl_checked_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_lk[] = "inlc.inlc_checked_date BETWEEN DATE '$period_from' AND '$period_to'";
}

$strWherePL		= implode(" AND ", $tmp_pl);
$strWhereClaim	= implode(" AND ", $tmp_cl);
$strWhereLocal	= implode(" AND ", $tmp_lk);

$sql_pl = "
SELECT
  'PL-'||'v1-'||pl.pl_idx AS idx,
  pl.pl_inv_no AS invoice_no,
  to_char(pl.pl_inv_date, 'dd-Mon-YY') AS issued_invoice_date,
  pl.pl_total_qty AS total_pl,
  sp.sp_code AS sp_code,
  sp.sp_full_name AS sp_name,
  'v1-'||inpl.inpl_idx AS inpl_idx,
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
  'confirm_pl.php?_code='||pl.pl_idx AS go_cfm_page,
  'detail_confirm_pl.php?_code='||pl.pl_idx||'&_inpl_idx='||inpl.inpl_idx AS go_page
FROM
  ".ZKP_SQL."_tb_supplier AS sp
  JOIN ".ZKP_SQL."_tb_pl AS pl ON pl.pl_sp_code = sp.sp_code
  JOIN ".ZKP_SQL."_tb_in_pl AS inpl USING(pl_idx)
  JOIN ".ZKP_SQL."_tb_in_pl_item AS init USING(inpl_idx)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE $strWherePL
	UNION
SELECT
  'PL-'||'v2-'||pl.pl_idx AS idx,
  pl.pl_inv_no AS invoice_no,
  to_char(pl.pl_inv_date, 'dd-Mon-YY') AS issued_invoice_date,
  pl.pl_total_qty AS total_pl,
  sp.sp_code AS sp_code,
  sp.sp_full_name AS sp_name,
  'v2-'||inpl.inpl_idx AS inpl_idx,
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
  'confirm_pl.php?_code='||pl.pl_idx AS go_cfm_page,
  'detail_confirm_pl.php?_code='||pl.pl_idx||'&_inpl_idx='||inpl.inpl_idx AS go_page
FROM
  ".ZKP_SQL."_tb_supplier AS sp
  JOIN ".ZKP_SQL."_tb_pl AS pl ON pl.pl_sp_code = sp.sp_code
  JOIN ".ZKP_SQL."_tb_in_pl_v2 AS inpl USING(pl_idx)
  JOIN ".ZKP_SQL."_tb_in_pl_item_v2 AS init USING(inpl_idx)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE $strWherePL
";

$sql_claim = "
SELECT
  'CL-'||'v1-'||cl.cl_idx AS idx,
  cl.cl_inv_no AS invoice_no,
  to_char(cl.cl_inv_date, 'dd-Mon-YY') AS issued_invoice_date,
  (select sum(clit_qty) from ".ZKP_SQL."_tb_claim_item where cl_idx=cl.cl_idx) AS total_pl,
  sp.sp_code AS sp_code,
  sp.sp_full_name AS sp_name,
  'v1-'||incl.incl_idx AS inpl_idx,
  incl.incl_checked_date AS inpl_checked_date,
  to_char(incl.incl_checked_date, 'dd-Mon-YY') AS checked_date,
  icat.icat_pidx AS icat_pidx,
  icat.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  it.it_desc AS it_desc,
  init.init_qty AS it_qty,
  'NON' AS pl_type,
  'confirm_claim.php?_code='||cl.cl_idx AS go_cfm_page,
  'detail_confirm_claim.php?_code='||cl.cl_idx||'&_incl_idx='||incl.incl_idx AS go_page
FROM
  ".ZKP_SQL."_tb_supplier AS sp
  JOIN ".ZKP_SQL."_tb_claim AS cl ON cl.cl_sp_code = sp.sp_code
  JOIN ".ZKP_SQL."_tb_in_claim AS incl USING(cl_idx)
  JOIN ".ZKP_SQL."_tb_in_claim_item AS init USING(incl_idx)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE $strWhereClaim
	UNION
SELECT
  'CL-'||'v2-'||cl.cl_idx AS idx,
  cl.cl_inv_no AS invoice_no,
  to_char(cl.cl_inv_date, 'dd-Mon-YY') AS issued_invoice_date,
  (select sum(clit_qty) from ".ZKP_SQL."_tb_claim_item where cl_idx=cl.cl_idx) AS total_pl,
  sp.sp_code AS sp_code,
  sp.sp_full_name AS sp_name,
  'v2-'||incl.incl_idx AS inpl_idx,
  incl.incl_checked_date AS inpl_checked_date,
  to_char(incl.incl_checked_date, 'dd-Mon-YY') AS checked_date,
  icat.icat_pidx AS icat_pidx,
  icat.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  it.it_desc AS it_desc,
  init.init_qty AS it_qty,
  'NON' AS pl_type,
  'confirm_claim.php?_code='||cl.cl_idx AS go_cfm_page,
  'detail_confirm_claim.php?_code='||cl.cl_idx||'&_incl_idx='||incl.incl_idx AS go_page
FROM
  ".ZKP_SQL."_tb_supplier AS sp
  JOIN ".ZKP_SQL."_tb_claim AS cl ON cl.cl_sp_code = sp.sp_code
  JOIN ".ZKP_SQL."_tb_in_claim_v2 AS incl USING(cl_idx)
  JOIN ".ZKP_SQL."_tb_in_claim_item_v2 AS init USING(incl_idx)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE $strWhereClaim
";

$sql_local = "
SELECT
  'v1-'||po_code|| '-' ||pl_no AS idx,
  po_code|| ' #' ||pl_no AS invoice_no,
  to_char(pl.pl_date, 'dd-Mon-YY') AS issued_invoice_date,
  (select sum(plit_qty) from ".ZKP_SQL."_tb_pl_local join ".ZKP_SQL."_tb_pl_local_item using(po_code, pl_no) where po_code=pl.po_code and pl_no=pl.pl_no) AS total_pl,
  sp.sp_code AS sp_code,
  sp.sp_full_name AS sp_name,
  'v1-'||inlc.inlc_idx AS inpl_idx,
  inlc.inlc_checked_date AS inpl_checked_date,
  to_char(inlc.inlc_checked_date, 'dd-Mon-YY') AS checked_date,
  icat.icat_pidx AS icat_pidx,
  icat.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  it.it_desc AS it_desc,
  init.init_qty AS it_qty,
  CASE
 	WHEN po.po_type = 1 THEN 'VAT'
 	WHEN po.po_type = 2 THEN 'NON'
  END AS pl_type,
  'confirm_pl_local.php?_code=' || po_code || '&_pl_no=' || pl_no AS go_cfm_page,
  'detail_confirm_pl_local.php?_inlc_idx=' || inlc_idx AS go_page
FROM
  ".ZKP_SQL."_tb_supplier_local AS sp
  JOIN ".ZKP_SQL."_tb_po_local AS po USING(sp_code)
  JOIN ".ZKP_SQL."_tb_pl_local AS pl USING(po_code)
  JOIN ".ZKP_SQL."_tb_in_local AS inlc USING(po_code, pl_no)
  JOIN ".ZKP_SQL."_tb_in_local_item AS init USING(inlc_idx)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE $strWhereLocal
	UNION
SELECT
  'v2-'||po_code|| '-' ||pl_no AS idx,
  po_code|| ' #' ||pl_no AS invoice_no,
  to_char(pl.pl_date, 'dd-Mon-YY') AS issued_invoice_date,
  (select sum(plit_qty) from ".ZKP_SQL."_tb_pl_local join ".ZKP_SQL."_tb_pl_local_item using(po_code, pl_no) where po_code=pl.po_code and pl_no=pl.pl_no) AS total_pl,
  sp.sp_code AS sp_code,
  sp.sp_full_name AS sp_name,
  'v2-'||inlc.inlc_idx AS inpl_idx,
  inlc.inlc_checked_date AS inpl_checked_date,
  to_char(inlc.inlc_checked_date, 'dd-Mon-YY') AS checked_date,
  icat.icat_pidx AS icat_pidx,
  icat.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  it.it_desc AS it_desc,
  init.init_qty AS it_qty,
  CASE
 	WHEN po.po_type = 1 THEN 'VAT'
 	WHEN po.po_type = 2 THEN 'NON'
  END AS pl_type,
  'confirm_pl_local.php?_code=' || po_code || '&_pl_no=' || pl_no AS go_cfm_page,
  'detail_confirm_pl_local.php?_inlc_idx=' || inlc_idx AS go_page
FROM
  ".ZKP_SQL."_tb_supplier_local AS sp
  JOIN ".ZKP_SQL."_tb_po_local AS po USING(sp_code)
  JOIN ".ZKP_SQL."_tb_pl_local AS pl USING(po_code)
  JOIN ".ZKP_SQL."_tb_in_local_v2 AS inlc USING(po_code, pl_no)
  JOIN ".ZKP_SQL."_tb_in_local_item_v2 AS init USING(inlc_idx)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE $strWhereLocal
";

$sql = "$sql_pl UNION $sql_claim UNION $sql_local ORDER BY icat_pidx, icat_midx, it_code, inpl_idx, inpl_checked_date, idx";

echo "<pre>";
//echo $sql;
echo "</pre>";

// raw data
$cache	= array("","","","");
$group0 = array();
$rd	= array();
$rdIdx	= 0;
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'],		//0
		$col['it_code'],		//1
		$col['it_model_no'],		//2
		$col['idx'],			//3
		$col['invoice_no'],		//4
		$col['issued_invoice_date'],	//5
		$col['total_pl'],		//6
		$col['sp_code'],		//7
		$col['sp_name'],		//8
		$col['inpl_idx'],		//9
		$col['checked_date'],		//10
		$col['it_qty'],			//11
		$col['pl_type'],		//12
		$col['go_cfm_page'],		//13
		$col['go_page']			//14
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
		$group0[$col['icat_midx']][$col['it_code']][$col['idx']] = array();
	}

	if($cache[3] != $col['inpl_idx']) {
		$cache[3] = $col['inpl_idx'];
	}

	$group0[$col['icat_midx']][$col['it_code']][$col['idx']][$col['inpl_idx']] = 1;
}

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
			<th width="18%">INVOICE#</th>
			<th width="10%">INVOICE<br />DATE</th>
			<th>SUPPLIER</th>
			<th width="10%">ARRIVAL DATE</th>
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
			cell_link("<span class=\"bar\">".$rd[$rdIdx][4]."</span>", ' align="center" valign="top" rowspan="'.$rowSpan.'"',
				' href="'.$rd[$rdIdx][13].'"'); 												//invoice no	
			cell($rd[$rdIdx][5], ' align="center" valign="top" rowspan="'.$rowSpan.'"');		//invoice date
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
		cell("[".trim($total2)."] $item_name", ' colspan="4" align="right" style="color:darkblue"');
		cell(number_format($total), ' align="right" style="color:darkblue"');
		print "</tr>\n";

		$g_total += $total;
	}

	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
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
		<th width="18%">INVOICE#</th>
		<th width="10%">INVOICE<br />DATE</th>
		<th>SUPPLIER</th>
		<th width="10%">ARRIVAL DATE</th>
		<th width="8%">QTY</th>
	</tr>\n
END;
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($gg_total), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>