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
 sp.sp_code AS sp_code,
 sp.sp_full_name AS sp_name,
 'PL-'||pl.pl_idx AS idx,
 pl.pl_inv_no AS invoice_no,
 pl.pl_inv_date AS invoice_date,
 to_char(pl.pl_inv_date, 'dd-Mon-YY') AS issued_invoice_date,
 it.it_code AS it_code,
 it.it_model_no AS it_model_no,
 it.it_desc AS it_desc,
 inpl.inpl_idx AS inpl_idx,
 inpl.inpl_checked_date AS inpl_checked_date,
 to_char(inpl.inpl_checked_date, 'dd-Mon-YY') AS checked_date,
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
 sp.sp_code AS sp_code,
 sp.sp_full_name AS sp_name,
 'CL-'||cl.cl_idx AS idx,
 cl.cl_inv_no AS invoice_no,
 cl.cl_inv_date AS invoice_date,
 to_char(cl.cl_inv_date, 'dd-Mon-YY') AS issued_invoice_date,
 it.it_code AS it_code,
 it.it_model_no AS it_model_no,
 it.it_desc AS it_desc,
 incl.incl_idx AS inpl_idx,
 incl.incl_checked_date AS inpl_checked_date,
 to_char(incl.incl_checked_date, 'dd-Mon-YY') AS checked_date,
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

$sql = "$sql_pl UNION $sql_claim ORDER BY sp_code, invoice_date, idx, it_code, inpl_checked_date";

// raw data
$cache	= array("","","","");
$rd		= array();
$group0 = array();
$rdIdx	= 0;
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['sp_code'],				//0
		$col['sp_name'],				//1
		$col['idx'],					//2
		$col['invoice_no'],				//3
		$col['issued_invoice_date'],	//4
		$col['it_code'],				//5
		$col['it_model_no'],			//6
		$col['it_desc'],				//7
		$col['inpl_idx'],				//8
		$col['checked_date'],			//9
		$col['it_qty'],					//10
		$col['pl_type'],				//11
		$col['go_page_pl'],				//12
		$col['go_page_inc']				//13
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
		$group0[$col['sp_code']][$col['idx']][$col['it_code']] = array();
	}

	if($cache[3] != $col['inpl_idx']) {
		$cache[3] = $col['inpl_idx'];
	}

	$group0[$col['sp_code']][$col['idx']][$col['it_code']][$col['inpl_idx']] = 1;
}
/*
echo "<pre>";
var_dump($group0);
echo "<pre>";
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

	echo "<span class=\"comment\"><b> SUPPLIER : ". $rd[$rdIdx][1] . "</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th width="12%">INVOICE NO#</th>
			<th width="12%">INVOICE DATE</th>
			<th width="7%">PL TYPE</th>
			<th width="18%">MODEL NO</th>
			<th>DESC</th>
			<th width="12%">ARRIVAL DATE</th>
			<th width="10%">QTY<br>(EA)</th>
		</tr>\n
END;

	$g_total	= 0;
	$print_tr_1 = 0;
	print "<tr>\n";
	//INVOICE
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell_link($rd[$rdIdx][3], ' align="center" valign="top" rowspan="'.$rowSpan.'"',
			' href="'.$rd[$rdIdx][12].'"');												//invoice no
		cell($rd[$rdIdx][4], ' valign="top" align="center" rowspan="'.$rowSpan.'"');	//invoice date
		cell($rd[$rdIdx][11], ' valign="top" align="center" rowspan="'.$rowSpan.'"');	//PL type
		
		$print_tr_2	= 0;
		$total		= 0;
		//ITEM
		foreach($group2 as $total3 => $group3) {
			$rowSpan = 0;
			array_walk_recursive($group3, 'getRowSpan');

			if($print_tr_2++ > 0) print "<tr>\n";
			cell("[".trim($rd[$rdIdx][5])."] ".$rd[$rdIdx][6], ' valign="top" rowspan="'.$rowSpan.'"');	//model no
			cell(cut_string($rd[$rdIdx][7],35) , ' valign="top" rowspan="'.$rowSpan.'"');				//desc

			$print_tr_3	= 0;
			//INCOMING ITEM
			foreach($group3 as $group4) {

				if($print_tr_3++ > 0) print "<tr>\n";
				cell_link("<span class=\"bar\">".$rd[$rdIdx][9]."</span>", ' align="center"', 
					' href="'.$rd[$rdIdx][13].'"');							//incoming date
				cell(number_format($rd[$rdIdx][10]), ' align="right"');		//incoming qty
				print "</tr>\n";
	
				$total		+= $rd[$rdIdx][10];
				$invoice	= $rd[$rdIdx][3];
				$sp_name	= $rd[$rdIdx][1];
				$rdIdx++;
			}
		}
		print "<tr>\n";
		cell($invoice, ' colspan="3" align="right" style="color:darkblue"');
		cell(number_format($total), ' align="right" style="color:darkblue"');
		print "</tr>\n";

		$g_total += $total;
	}

	print "<tr>\n";
	cell("<b>$sp_name</b>", ' colspan="6" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($g_total), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$gg_total  += $g_total;
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th width="12%">INVOICE NO#</th>
			<th width="12%">INVOICE DATE</th>
			<th width="18%">MODEL NO</th>
			<th>DESC</th>
			<th width="15%">ARRIVAL DATE</th>
			<th width="10%">QTY<br>(EA)</th>
		</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($gg_total), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>