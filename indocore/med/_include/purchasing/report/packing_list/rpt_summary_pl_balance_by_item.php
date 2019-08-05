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
	$tmp_pl[] = "pl.pl_type = 1";
	$tmp_cl[] = "cl.cl_idx is null";
} else if ($_type == 2) {
	$tmp_pl[] = "pl.pl_type = 2";
	$tmp_cl[] = "cl.cl_idx is not null";
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
  icat.icat_pidx AS icat_pidx,
  icat.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  sp.sp_code AS sp_code,
  sp.sp_full_name AS sp_name,
  'PL-'||pl.pl_idx AS idx,
  pl.po_code AS po_code,
  pl.pl_inv_no AS invoice_no,
  pl.pl_inv_date AS invoice_date,
  to_char(pl.pl_inv_date, 'dd-Mon-yy') AS issued_invoice_date,
  (select sum(plit_qty) from ".ZKP_SQL."_tb_pl_item where it_code = it.it_code and pl_idx= pl.pl_idx) AS total_qty,
  inpl_idx AS inpl_idx,
  to_char(inpl.inpl_checked_date, 'dd-Mon-yy') AS arrival_date,
  init.init_qty AS init_qty,
  'revise_pl.php?_code='||pl.pl_idx AS go_page
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
  icat.icat_pidx AS icat_pidx,
  icat.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  sp.sp_code AS sp_code,
  sp.sp_full_name AS sp_name,
  'CL-'||cl.cl_idx AS idx,
  null AS po_code,
  cl.cl_inv_no AS invoice_no,
  cl.cl_inv_date AS invoice_date,
  to_char(cl.cl_inv_date, 'dd-Mon-yy') AS issued_invoice_date,
  (select sum(clit_qty) from ".ZKP_SQL."_tb_claim_item where it_code = it.it_code and cl_idx= cl.cl_idx) AS total_qty,
  incl_idx AS inpl_idx,
  to_char(incl.incl_checked_date, 'dd-Mon-yy') AS arrival_date,
  init.init_qty AS init_qty,
  'revise_claim.php?_code='||cl.cl_idx AS go_page
FROM
  ".ZKP_SQL."_tb_supplier AS sp
  JOIN ".ZKP_SQL."_tb_claim AS cl ON cl.cl_sp_code = sp.sp_code
  JOIN ".ZKP_SQL."_tb_in_claim AS incl USING(cl_idx)
  JOIN ".ZKP_SQL."_tb_in_claim_item AS init USING(incl_idx)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE $strWhereClaim ";

$sql = "$sql_pl UNION $sql_claim ORDER BY icat_pidx, icat_midx, it_code, idx";

// raw data
$rdIdx	= 0;
$cache	= array("","","","");
$rd		= array();
$group0 = array();

$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'],			//0
		$col['it_code'],			//1
		$col['it_model_no'],		//2
		$col['sp_code'],			//3
		$col['sp_name'],			//4
		$col['idx'],				//5
		$col['po_code'],			//6
		$col['invoice_no'],			//7
		$col['issued_invoice_date'],//8
		$col['total_qty'],			//9
		$col['inpl_idx'],			//10
		$col['arrival_date'],		//11
		$col['init_qty'],			//12
		$col['go_page'],			//13
		$col['icat_pidx']			//14
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
		$group0[$col['icat_pidx'].$col['icat_midx']][$col['it_code']][$col['idx']] = array();
	}

	if($cache[3] != $col['inpl_idx']) {
		$cache[3] = $col['inpl_idx'];
	}

	$group0[$col['icat_pidx'].$col['icat_midx']][$col['it_code']][$col['idx']][$col['inpl_idx']] = 1;
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
	<table width="100%" class="table_c">
		<tr>
			<th width="12%">MODEL NO</th>
			<th width="10%">INVOICE#</th>
			<th width="10%">INVOICE DATE</th>
			<th>SUPPLIER</th>
			<th width="8%">P/L QTY</th>
			<th width="12%">ARRIVAL<br />DATE</th>
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
		cell("[".trim($rd[$rdIdx][1])."]<br/>".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');	//model no

		$item_total	= array(0,0,0);
		$print_tr_2 = 0;
		//INVOICE NO
		foreach($group2 as $total3 => $group3) {
			$rowSpan = 0;
			array_walk_recursive($group3, 'getRowSpan');

			if($print_tr_2++ > 0) print "<tr>\n";
			cell_link("<span class=\"bar\">".$rd[$rdIdx][7]."</span>", ' align="center" valign="top" rowspan="'.$rowSpan.'"',
			' href="'.$rd[$rdIdx][13].'"');													//invoice no
			cell($rd[$rdIdx][8], ' align="center" valign="top" rowspan="'.$rowSpan.'"');	//invoice date
			cell($rd[$rdIdx][4], ' valign="top" rowspan="'.$rowSpan.'"');					//supplier
			cell(number_format($rd[$rdIdx][9]), ' align="right" valign="top" rowspan="'.$rowSpan.'"');	//total invoice

			$inv_total		= array(0,0);
			$print_tr_3 = 0;
			//INCOMING PL
			foreach($group3 as $total4) {
				if($print_tr_3++ > 0) print "<tr>\n";
				cell($rd[$rdIdx][11], ' align="center"');					//arrival date
				cell(number_format($rd[$rdIdx][12]), ' align="right"');		//arrival qty
				cell('');
				print "</tr>\n";

				$inv_total[0]	= $rd[$rdIdx][9];
				$inv_total[1]	+= $rd[$rdIdx][12];
				$model_no	= $rd[$rdIdx][2];
				$rdIdx++;
			}
			print "<tr>\n";
			cell("INVOICE TOTAL", ' colspan="3" align="right" style="color:blue"');
			cell(number_format($inv_total[0]), ' align="right" style="color:blue"');
			cell('');
			cell(number_format($inv_total[1]), ' align="right" style="color:blue"');
			cell(number_format($inv_total[0]-$inv_total[1]), ' align="right" style="color:blue"');
			print "</tr>\n";

			$item_total[0]	+= $inv_total[0];
			$item_total[1]	+= $inv_total[1];
			$item_total[2]	+= $inv_total[0]-$inv_total[1];
		}
		print "<tr>\n";
		cell("<b>[$total2] $model_no</b>", ' colspan="4" align="right" style="color:brown"');
		cell(number_format($item_total[0]), ' align="right" style="color:brown"');
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
<table width="100%" class="table_c">
		<tr>
			<th width="12%">MODEL NO</th>
			<th width="10%">INVOICE#</th>
			<th width="10%">INVOICE DATE</th>
			<th>SUPPLIER</th>
			<th width="8%">P/L QTY</th>
			<th width="12%">ARRIVAL<br />DATE</th>
			<th width="8%">RECEIVED<br />QTY</th>
			<th width="8%">BALANCE<br />QTY</th>
		</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="4" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell('', ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>