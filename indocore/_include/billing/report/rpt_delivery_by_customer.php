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
//Variable Color
$display_css['black']	= "color:#333333";
$display_css['red'] 	= "color:#EE5811";
$display_css['grey']	= "color:#9D9DA1";

//SET WHERE PARAMETER
$tmp_bill	= array();
$tmp_turn	= array();
$tmp_dt		= array();
$tmp_df		= array();
$tmp_dr		= array();
$tmp_rt		= array();

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp_bill[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_turn[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_dt[]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_df[]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_dr[]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_rt[]	= "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if($_sort_date == "deli") {
	if ($some_date != "") {
		$tmp_bill[] = "bill_delivery_date = DATE '$some_date'";
		$tmp_turn[] = "turn_return_date = DATE '$some_date'";
		$tmp_dt[]	= "dt_delivery_date = DATE '$some_date'";
		$tmp_df[]	= "df_delivery_date = DATE '$some_date'";
		$tmp_dr[]	= "dr_delivery_date = DATE '$some_date'";
		$tmp_rt[]	= "rdt_date = DATE '$some_date'";
	} else {
		$tmp_bill[] = "bill_delivery_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_turn[] = "turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_dt[]	= "dt_delivery_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_df[]	= "df_delivery_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_dr[]	= "dr_delivery_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_rt[]	= "rdt_date BETWEEN DATE '$period_from' AND '$period_to'";
	}
} else if($_sort_date == "inv") {
	if ($some_date != "") {
		$tmp_bill[] = "bill_inv_date = DATE '$some_date'";
		$tmp_turn[] = "turn_return_date = DATE '$some_date'";
		$tmp_dt[]	= "dt_date = DATE '$some_date'";
		$tmp_df[]	= "df_date = DATE '$some_date'";
		$tmp_dr[]	= "dr_date = DATE '$some_date'";
		$tmp_rt[]	= "rdt_date = DATE '$some_date'";
	} else {
		$tmp_bill[] = "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_turn[] = "turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_dt[]	= "dt_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_df[]	= "df_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_dr[]	= "dr_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_rt[]	= "rdt_date BETWEEN DATE '$period_from' AND '$period_to'";
	}
}

if(ZKP_FUNCTION == 'ALL') {
	if($_order_by != 'all'){
		$tmp_bill[]	= "bill_ordered_by = $_order_by";
		$tmp_turn[] = "turn_ordered_by = $_order_by";
		$tmp_dr[]	= "dr_ordered_by = $_order_by";
	}
} else {
	$tmp_bill[]	= "bill_ordered_by = ".$cboFilter[1][ZKP_FUNCTION][0][0];
	$tmp_turn[] = "turn_ordered_by = ".$cboFilter[1][ZKP_FUNCTION][0][0];
	$tmp_dr[]	= "dr_ordered_by = ".$cboFilter[1][ZKP_FUNCTION][0][0];
	$tmp_bill[] = ZKP_SQL."_isValidShowInvoice('".ZKP_FUNCTION."', bill_code,'billing')";
	$tmp_turn[] = ZKP_SQL."_isValidShowInvoice('".ZKP_FUNCTION."', turn_code,'billing_return')";
	$tmp_dr[]	= ZKP_SQL."_isValidShowInvoice('".ZKP_FUNCTION."', dr_code,'dr')";
}

if($_marketing != "all") {
	$tmp_bill[]	= "cus_responsibility_to = $_marketing";
	$tmp_turn[] = "cus_responsibility_to = $_marketing";
	$tmp_dt[]	= "cus_responsibility_to = $_marketing";
	$tmp_df[]	= "cus_responsibility_to = $_marketing";
	$tmp_dr[]	= "cus_responsibility_to = $_marketing";
	$tmp_rdt[]	= "cus_responsibility_to = $_marketing";
}

if($_document == 'I'){
	$tmp_turn[] = "turn_code is null";
	$tmp_dt[]	= "dt_code is null";
	$tmp_df[]	= "df_code is null";
	$tmp_dr[]	= "dr_code is null";
	$tmp_rt[]	= "rdt_code is null";
} else if($_document == 'R') {
	$tmp_bill[] = "bill_code is null";
	$tmp_dt[]	= "dt_code is null";
	$tmp_df[]	= "df_code is null";
	$tmp_dr[]	= "dr_code is null";
	$tmp_rt[]	= "rdt_code is null";
} else if($_document == 'DT') {
	$tmp_bill[] = "bill_code is null";
	$tmp_turn[] = "turn_code is null";
	$tmp_df[]	= "df_code is null";
	$tmp_dr[]	= "dr_code is null";
	$tmp_rt[]	= "rdt_code is null";
} else if($_document == 'DF') {
	$tmp_bill[] = "bill_code is null";
	$tmp_turn[] = "turn_code is null";
	$tmp_dt[]	= "dt_code is null";
	$tmp_dr[]	= "dr_code is null";
	$tmp_rt[]	= "rdt_code is null";
} else if($_document == 'DR') {
	$tmp_bill[] = "bill_code is null";
	$tmp_turn[] = "turn_code is null";
	$tmp_df[]	= "df_code is null";
	$tmp_dt[]	= "dt_code is null";
	$tmp_rt[]	= "rdt_code is null";
} else if($_document == 'RT') {
	$tmp_bill[] = "bill_code is null";
	$tmp_turn[] = "turn_code is null";
	$tmp_df[]	= "df_code is null";
	$tmp_dt[]	= "dt_code is null";
	$tmp_dr[]	= "dr_code is null";
}

if ($_paper == '0') {
	$tmp_bill[]	= "bill_type_invoice = '0'";
	$tmp_turn[]	= "turn_paper = 0";
} else if ($_paper == '1') {
	$tmp_bill[]	= "bill_type_invoice = '1'";
	$tmp_turn[]	= "turn_paper = 1";
	$tmp_dr[]	= "dr_code is null";
} else if ($_paper == 'A') {
	$tmp_bill[]	= "bill_paper_format = 'A'";
	$tmp_turn[]	= "turn_paper = 0";
	$tmp_dr[]	= "dr_code is null";
} else if ($_paper == 'B') {
	$tmp_bill[]	= "bill_paper_format = 'B'";
	$tmp_turn[]	= "turn_paper = 1";
	$tmp_dr[]	= "dr_code is null";
}

if($_status == 'pending'){
	$tmp_bill[] = "bill_delivery_date is null";
	$tmp_turn[] = "turn_code is null";
	$tmp_dt[]	= "dt_delivery_date is null";
	$tmp_df[]	= "df_delivery_date is null";
	$tmp_dr[]	= "dr_delivery_date is null";
	$tmp_rt[]	= "rdt_code is null";
} else if($_status == 'delivered') {
	$tmp_bill[] = "bill_delivery_date is not null";
	$tmp_dt[]	= "dt_delivery_date is not null";
	$tmp_df[]	= "df_delivery_date is not null";
	$tmp_dr[]	= "dr_delivery_date is not null";
	$tmp_rt[]	= "rdt_code is not null";
}

$tmp_bill[] = "bill_ship_to = '$_cus_code'";
$tmp_turn[]	= "turn_ship_to = '$_cus_code'";
$tmp_dt[]	= "dt_cus_to = '$_cus_code'";
$tmp_df[]	= "df_cus_to = '$_cus_code'";
$tmp_dr[]	= "dr_cus_to = '$_cus_code'";
$tmp_rt[]	= "rdt_cus_to = '$_cus_code'";

$tmp_bill[] = "bill_dept = '$department'";
$tmp_turn[]	= "turn_dept = '$department'";
$tmp_dt[]	= "dt_dept = '$department'";
$tmp_df[]	= "df_dept = '$department'";
$tmp_dr[]	= "dr_dept = '$department'";
$tmp_rt[]	= "rdt_dept = '$department'";

$strWhereBilling	= implode(" AND ", $tmp_bill);
$strWhereReturn		= implode(" AND ", $tmp_turn);
$strWhereDT			= implode(" AND ", $tmp_dt);
$strWhereDF			= implode(" AND ", $tmp_df);
$strWhereDR			= implode(" AND ", $tmp_dr);
$strWhereRT			= implode(" AND ", $tmp_rt);

//include sql
include "generate_sql/generate_delivery_sql_by_customer.php";
$sql = "$sql_billing UNION $sql_return UNION $sql_dt UNION $sql_df UNION $sql_dr UNION $sql_rt
		ORDER BY cus_code, icat_pidx, icat_midx, it_code, inv_date, invoice_code";

// raw data
$rd = array();
$rdIdx = 0;
$cache = array("","","","");
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'],		//0
		$col['invoice_code'],	//1
		$col['invoice_date'],	//2
		$col['delivery_date'],	//3
		$col['delivery_by'],	//4
		$col['cus_code'],		//5
		$col['cus_full_name'],	//6
		$col['it_idx'],			//7
		$col['it_code'],		//8
		$col['it_model_no'],	//9
		$col['it_unit_price'],	//10
		$col['it_qty'],			//11
		$col['it_amount'],		//12
		$col['it_vat'],			//13
		$col['it_amount']+$col['it_vat'],	//14
		$col['it_grand_total'],	//15
		$col['go_page'],		//16
		$col['coloring_general'],	//17
		$col['coloring_qty'],		//18
		$col['coloring_amount']		//19
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

	if($cache[2] != $col['invoice_code']) {
		$cache[2] = $col['invoice_code'];
		$group0[$col['icat_midx']][$col['it_code']][$col['invoice_code']] = array();
	}

	if($cache[3] != $col['it_idx']) {
		$cache[3] = $col['it_idx'];
	}

	$group0[$col['icat_midx']][$col['it_code']][$col['invoice_code']][$col['it_idx']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$grand_total = array(0,0,0,0);

//GROUP
foreach ($group0 as $total1 => $group1) {

	//get category path from current icat_midx.
	$path = executeSP(ZKP_SQL."_getCategoryPath", $rd[$rdIdx][0]);
	eval(html_entity_decode($path[0]));	
	$path = array_reverse($path);
	$total1 = $path[1][4]." > ".$path[2][4]." > ".$path[3][4] ;

	echo "<span class=\"comment\"><b> CATEGORY : ". $total1. "</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th>MODEL NO</th>
			<th width="20%">INV. NO</th>
			<th width="8%">INV. DATE</th>
			<th width="8%">DELI DATE</th>
			<th width="10%">@PRICE<br/>(Rp)</th>
			<th width="5%">QTY<br>(EA)</th>
			<th width="10%">AMOUNT<br>(Rp)</th>
			<th width="8%">VAT<br>(Rp)</th>
			<th width="12%">AMOUNT<br>+VAT(Rp)</th>
		</tr>\n
END;
	$cat_total = array(0,0,0,0);
	$print_tr_1 = 0;

	print "<tr>\n";
	//MODEL
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".trim($rd[$rdIdx][8]). "] ".$rd[$rdIdx][9], ' valign="top" rowspan="'.$rowSpan.'"');

		$model_total	= array(0,0,0,0);
		$print_tr_2		= 0;
		//ORDER
		foreach($group2 as $total3 => $group3) {
			$rowSpan = 0;
			array_walk_recursive($group3, 'getRowSpan');

			if($print_tr_2++ > 0) print "<tr>\n";
			cell_link($rd[$rdIdx][1], ' align="center" valign="top" rowspan="'.$rowSpan.'"',	//invoice no
				' href="'.$rd[$rdIdx][16].'" style="'.$display_css[$rd[$rdIdx][17]].'"');
			cell($rd[$rdIdx][2], ' style="'.$display_css[$rd[$rdIdx][17]].'" align="center" valign="top" rowspan="'.$rowSpan.'"');		//invoice date
			cell($rd[$rdIdx][3], ' style="'.$display_css[$rd[$rdIdx][17]].'" align="center" valign="top" rowspan="'.$rowSpan.'"');		//delivery date

			$print_tr_3 = 0;
			//ITEM
			foreach($group3 as $total4 => $group4) {
				if($print_tr_3++ > 0) print "<tr>\n";
				cell(number_format((double)$rd[$rdIdx][10]), ' style="'.$display_css[$rd[$rdIdx][19]].'" align="right"');	//unit price
				cell(number_format((double)$rd[$rdIdx][11]), ' style="'.$display_css[$rd[$rdIdx][18]].'" align="right"');	//qty
				cell(number_format((double)$rd[$rdIdx][12]), ' style="'.$display_css[$rd[$rdIdx][19]].'" align="right"');	//amount
				cell(number_format((double)$rd[$rdIdx][13]), ' style="'.$display_css[$rd[$rdIdx][19]].'" align="right"');	//vat
				cell(number_format((double)$rd[$rdIdx][14]), ' style="'.$display_css[$rd[$rdIdx][19]].'" align="right"');	//amount + vat
				print "</tr>\n";

				//nilai positif atau negatif
				//qty
				if($rd[$rdIdx][18] == 'red') {	$model_total[0] += $rd[$rdIdx][11]*-1; }
				else {							$model_total[0] += $rd[$rdIdx][11]; }

				//amount
				if($rd[$rdIdx][19] == 'red') {				//return
					$model_total[1] += $rd[$rdIdx][12]*-1;
					$model_total[2] += $rd[$rdIdx][13]*-1;
					$model_total[3] += $rd[$rdIdx][14]*-1;
				} else if($rd[$rdIdx][19] != 'grey') {	//billing
					$model_total[1] += $rd[$rdIdx][12];
					$model_total[2] += $rd[$rdIdx][13];
					$model_total[3] += $rd[$rdIdx][14];
				}

				$model_no	= $rd[$rdIdx][9];
				$rdIdx++;
			}
		}

		print "<tr>\n";
		cell($model_no, ' colspan="4" align="right" style="color:darkblue"');
		cell(number_format((double)$model_total[0]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$model_total[1]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$model_total[2]), ' align="right" style="color:darkblue"');
		cell(number_format((double)$model_total[3]), ' align="right" style="color:darkblue"');

		$cat_total[0] += $model_total[0];
		$cat_total[1] += $model_total[1];
		$cat_total[2] += $model_total[2];
		$cat_total[3] += $model_total[3];
		print "</tr>\n";
	}

	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cat_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cat_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cat_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cat_total[3]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";

	print "</table><br />\n";
	
	$grand_total[0] += $cat_total[0];
	$grand_total[1] += $cat_total[1];
	$grand_total[2] += $cat_total[2];
	$grand_total[3] += $cat_total[3];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_f">
	<tr>
		<th>MODEL NO</th>
		<th width="20%">INV. NO</th>
		<th width="8%">INV. DATE</th>
		<th width="8%">DELI DATE</th>
		<th width="10%">@PRICE<br/>(Rp)</th>
		<th width="5%">QTY<br>(EA)</th>
		<th width="10%">AMOUNT<br>(Rp)</th>
		<th width="8%">VAT<br>(Rp)</th>
		<th width="12%">AMOUNT<br>+VAT(Rp)</th>
	</tr>\n
END;
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[3]), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>