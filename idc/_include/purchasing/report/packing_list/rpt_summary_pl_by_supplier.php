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
	$tmp_cl[] = "cl_idx IS NULL";
} else if ($_source == 2) {
	$tmp_pl[] = "pl_idx IS NULL";
}

if ($_type == 1) {
	$tmp_pl[] = "substr(pl.po_code,4,2) = 'IP'";
	$tmp_cl[] = "cl_idx IS NULL";
} else if ($_type == 2) {
	$tmp_pl[] = "substr(pl.po_code,4,3) = 'ICP'";
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

$strWherePL	= implode(" AND ", $tmp_pl);
$strWhereClaim	= implode(" AND ", $tmp_cl);

$sql_pl = "
SELECT
 sp.sp_code AS sp_code,
 sp.sp_full_name AS sp_name,
 'PL-'||pl.pl_idx AS idx,
 pl.po_code AS po_code,
 pl.pl_inv_no AS invoice_no,
 pl.pl_inv_date AS invoice_date,
 to_char(pl.pl_inv_date, 'dd-Mon-YY') AS invoice_issued_date,
 to_char(pl.pl_etd_date, 'dd-Mon-YY') AS etd_date,
 to_char(pl.pl_eta_date, 'dd-Mon-YY') AS eta_date,
 pl.pl_shipment_mode AS shipment_mode,
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
WHERE " . $strWherePL;

$sql_claim = "
SELECT
 sp.sp_code AS sp_code,
 sp.sp_full_name AS sp_name,
 'CL-'||cl.cl_idx AS idx,
 null AS po_code,
 cl.cl_inv_no AS invoice_no,
 cl.cl_inv_date AS invoice_date,
 to_char(cl.cl_inv_date, 'dd-Mon-YY') AS invoice_issued_date,
 to_char(cl.cl_etd_date, 'dd-Mon-YY') AS etd_date,
 to_char(cl.cl_eta_date, 'dd-Mon-YY') AS eta_date,
 cl.cl_shipment_mode AS shipment_mode,
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
WHERE " . $strWhereClaim;

$sql = "$sql_pl UNION $sql_claim ORDER BY sp_code, idx, invoice_date, it_code"; 

// raw data
$rd = array();
$rdIdx = 0;
$cache = array("","","");
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['sp_code'],		//0
		$col['sp_name'],		//1
		$col['idx'],			//2
		$col['po_code'],		//3
		$col['invoice_no'],		//4
		$col['invoice_issued_date'],	//5
		$col['etd_date'],		//6
		$col['eta_date'],		//7
		$col['shipment_mode'],	//8
		$col['it_code'],		//9
		$col['it_model_no'],	//10
		$col['it_qty'],			//11
		$col['go_page_pl'],		//12
		$col['go_page_po']		//13
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

	echo "<span class=\"comment\"><b> SUPPLIER : [". $total1. "] ".$rd[$rdIdx][1]." </b></span>\n";
	print <<<END
	<table width="100%" class="table_c">
		<tr>
			<th width="12%">INVOICE NO#</th>
			<th width="10%">INVOICE DATE</th>
			<th width="10%">PO NO#</th>
			<th width="9%">ETD DATE</th>
			<th width="9%">ETA DATE</th>
			<th width="10%">SHIPMENT<br />MODE</th>
			<th>MODEL NO</th>
			<th width="10%">QTY<br>(EA)</th>
		</tr>\n
END;

	$gTot_db_ea = 0;
	$print_tr_1 = 0;
	
	print "<tr>\n";

	//PO
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell_link("<span class=\"bar\">".$rd[$rdIdx][4]."</span>", ' align="center" valign="top" rowspan="'.$rowSpan.'"',
			' href="'.$rd[$rdIdx][12].'"');												//invoice no
		cell($rd[$rdIdx][5], ' valign=""top align="center" rowspan="'.$rowSpan.'"');	//invoice date
		cell_link("<span class=\"bar\">".$rd[$rdIdx][3]."</span>", ' valign=""top align="center" rowspan="'.$rowSpan.'"',
			' href="'.$rd[$rdIdx][13].'"');												//PO no
		cell($rd[$rdIdx][6], ' valign=""top align="center" rowspan="'.$rowSpan.'"');	//ETD date
		cell($rd[$rdIdx][7], ' valign=""top align="center" rowspan="'.$rowSpan.'"');	//ETA date
		cell(strtoupper($rd[$rdIdx][8]), ' valign=""top align="center" rowspan="'.$rowSpan.'"');	//shipment mode

		$print_tr_2	= 0;
		$tot_db_ea	= 0;
		//ITEM
		foreach($group2 as $group3) {
			if($print_tr_2++ > 0) print "<tr>\n";
			
			cell("[".trim($rd[$rdIdx][9])."] ".$rd[$rdIdx][10]);				//item name
			cell(number_format($rd[$rdIdx][11]), ' align="right"');		//qty
			print "</tr>\n";

			
			$tot_db_ea	+= $rd[$rdIdx][11];
			$sp_name	= $rd[$rdIdx][1];
			$invoice	= $rd[$rdIdx][4];
			$rdIdx++;
		}
		
		print "<tr>\n";
		cell("<b>TOTAL $invoice</b>", ' align="right" style="color:darkblue"');
		cell(number_format($tot_db_ea), ' align="right" style="color:darkblue"');
		print "</tr>\n";

		$gTot_db_ea += $tot_db_ea;
	}

	print "<tr>\n";
	cell("<b>$sp_name</b>", ' colspan="7" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($gTot_db_ea), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$ggTot_db_ea  += $gTot_db_ea;
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
	print <<<END
	<table width="100%" class="table_c">
		<tr>
			<th width="12%">INVOICE NO#</th>
			<th width="10%">INVOICE DATE</th>
			<th width="10%">PO NO#</th>
			<th width="9%">ETD DATE</th>
			<th width="9%">ETA DATE</th>
			<th width="10%">SHIPMENT<br />MODE</th>
			<th>MODEL NO</th>
			<th width="10%">QTY<br>(EA)</th>
		</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="7" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTot_db_ea), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>