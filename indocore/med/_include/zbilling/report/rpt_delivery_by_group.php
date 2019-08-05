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
//similar with billing summary + column delivery date, delivery by
//SET WHERE PARAMETER
$display_css['black']	= "color:#333333";
$display_css['red'] 	= "color:#EE5811";
$display_css['grey']	= "color:#9D9DA1";

$tmp_bill	= array();
$tmp_turn	= array();
$tmp_dt		= array();
$tmp_df		= array();
$tmp_dr		= array();
$tmp_rt		= array();

if ($_cug_code != 'all') {
	//If group specified, 
	$tmp_bill[]	= "bill_cus_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp_turn[]	= "turn_cus_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp_dt[]	= "dt_cus_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp_df[]	= "df_cus_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp_dr[]	= "dr_cus_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp_rt[]	= "dr_cus_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";

	$sql_billing = " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
	$sql_return	 = " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
	$sql_dt	 = " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
	$sql_df	 = " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
	$sql_dr	 = " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
	$sql_rt	 = " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
} else {
	$sql_billing = "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = bill.bill_cus_to), 'Others') AS cug_name,";
	$sql_return	 = "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = turn.turn_cus_to), 'Others') AS cug_name,";
	$sql_dt	 = "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = dt.dt_cus_to), 'Others') AS cug_name,";
	$sql_df	 = "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = df.df_cus_to), 'Others') AS cug_name,";
	$sql_dr	 = "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = dr.dr_cus_to), 'Others') AS cug_name,";
	$sql_rt	 = "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = rdt.rdt_cus_to), 'Others') AS cug_name,";
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
	$tmp_rt[]	= "cus_responsibility_to = $_marketing";
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
}

$tmp_bill[] = "bill_dept = '$department'";
$tmp_turn[] = "turn_dept = '$department'";
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
include "generate_sql/generate_delivery_sql_by_group.php";
$sql	= "$sql_billing UNION $sql_return UNION $sql_dt UNION $sql_df UNION $sql_dr UNION $sql_rt
			ORDER BY cug_name, cus_code, invoice_code, it_code";
/*
echo "<pre>";
echo $sql_dr;//, $sql_return, $sql_dt, $sql_df, $sql_rt);
echo "</pre>";
*/
// raw data
$rd		= array();
$rdIdx	= 0;
$cache	= array("","","","");
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['cug_name'],		//0
		$col['cus_code'],		//1
		$col['cus_full_name'],	//2
		$col['invoice_code'],	//3
		$col['invoice_date'],	//4
		$col['delivery_date'],	//5
		$col['delivery_by'],	//6
		$col['discount'],		//7
		$col['freight_charge'],	//8
		$col['it_idx'],			//9
		$col['it_code'],		//10
		$col['it_model_no'],	//11
		$col['it_unit_price'],	//12
		$col['it_qty'],			//13
		$col['it_amount'],		//14
		$col['it_vat'],			//15
		$col['it_amount']+$col['it_vat'],	//16
		$col['it_grand_total'],		//17
		$col['go_page'],			//18
		$col['coloring_general'],	//19
		$col['coloring_qty'],		//20
		$col['coloring_amount']		//21
	);

	//1st grouping
	if($cache[0] != $col['cug_name']) {
		$cache[0] = $col['cug_name'];
		$group0[$col['cug_name']] = array();
	}

	if($cache[1] != $col['cus_code']) {
		$cache[1] = $col['cus_code'];
		$group0[$col['cug_name']][$col['cus_code']] = array();
	}

	if($cache[2] != $col['invoice_code']) {
		$cache[2] = $col['invoice_code'];
		$group0[$col['cug_name']][$col['cus_code']][$col['invoice_code']] = array();
	}

	if($cache[3] != $col['it_idx']) {
		$cache[3] = $col['it_idx'];
	}

	$group0[$col['cug_name']][$col['cus_code']][$col['invoice_code']][$col['it_idx']] = 1;
}

echo "</pre>";
//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$g_total = array(0,0,0,0,0,0);  // freight, qty, vat, amount, amount+vat, amount+vat+frt

//GROUP
foreach ($group0 as $total1 => $group1) {

	echo "<span class=\"comment\"><b> PUSAT: ". $total1. "</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th>CUSTOMER</th>
			<th width="12%">INVOICE NO</th>
			<th width="9%">INVOICE DATE</th>
			<th width="9%">DELIVERY DATE</th>
			<th width="10%">DELIVERY BY</th>
			<th width="7%">FREIGHT</th>
			<th width="3%">DISC<br />(%)</th>
			<th width="13%">MODEL NO</th>
			<th width="7%">UNIT PRICE<br/>(Rp)</th>
			<th width="3%">QTY<br>(EA)</th>
			<th width="7%">AMOUNT<br>-VAT (Rp)</th>
			<th width="6%">VAT<br>(Rp)</th>
			<th width="7%">AMOUNT<br>+VAT</th>
			<th width="7%">AMOUNT<br>+FRT/VAT</th>
		</tr>\n
END;
	print "<tr>\n";

	$cus_total = array(0,0,0,0,0,0);
	$print_tr_1 = 0;
	//CUSTOMER
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += count($group2);

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".trim($rd[$rdIdx][1])."] ".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');	//CUSTOMER

		$print_tr_2 = 0;
		//ORDER
		foreach($group2 as $total3 => $group3) {
			$rowSpan = 0;
			array_walk_recursive($group3, 'getRowSpan');
			$rowSpan += 1;

			if($print_tr_2++ > 0) print "<tr>\n";
			cell_link($rd[$rdIdx][3], ' align="center" valign="top" rowspan="'.$rowSpan.'"',
				' href="'.$rd[$rdIdx][18].'" style="'.$display_css[$rd[$rdIdx][19]].'"');											//invoice no
			cell($rd[$rdIdx][4], ' style="'.$display_css[$rd[$rdIdx][19]].'" valign="top" align="center" rowspan="'.$rowSpan.'"');	//invoice date
			cell($rd[$rdIdx][5], ' style="'.$display_css[$rd[$rdIdx][19]].'" valign="top" align="center" rowspan="'.$rowSpan.'"');	//delivery date
			cell($rd[$rdIdx][6], ' style="'.$display_css[$rd[$rdIdx][19]].'" valign="top" rowspan="'.$rowSpan.'"');					//delivery by
			cell(number_format((double)$rd[$rdIdx][8]), ' style="'.$display_css[$rd[$rdIdx][19]].'" valign="top" align="right" rowspan="'.$rowSpan.'"');		//freight
			cell(($rd[$rdIdx][7] <= 0)?'':$rd[$rdIdx][7], ' style="'.$display_css[$rd[$rdIdx][19]].'" valign="top" align="right" rowspan="'.$rowSpan.'"');		//disc

			//freight, qty, amount, vat, amount_vat, grand_total 
			$inv_total		= array($rd[$rdIdx][8],0,0,0,0,$rd[$rdIdx][17]);
			$print_tr_3		= 0;

			//ITEM
			foreach($group3 as $group4) {
				if($print_tr_3++ > 0) print "<tr>\n";
				cell($rd[$rdIdx][11], ' style="'.$display_css[$rd[$rdIdx][19]].'"');	//Model No
				cell(number_format((double)$rd[$rdIdx][12]), ' style="'.$display_css[$rd[$rdIdx][21]].'" align="right"');	//Unit price
				cell(number_format((double)$rd[$rdIdx][13]), ' style="'.$display_css[$rd[$rdIdx][20]].'" align="right"');	//Qty
				cell(number_format((double)$rd[$rdIdx][14]), ' style="'.$display_css[$rd[$rdIdx][21]].'" align="right"'); 	//Amount
				cell(number_format((double)$rd[$rdIdx][15]), ' style="'.$display_css[$rd[$rdIdx][21]].'" align="right"');	//Vat
				cell(number_format((double)$rd[$rdIdx][16]), ' style="'.$display_css[$rd[$rdIdx][21]].'" align="right"');	//Amount + vat
				cell("&nbsp;", ' style="'.$display_css[$rd[$rdIdx][19]].'"');
				print "</tr>\n";

				//Count invoice amount
				$inv_total[0] = $inv_total[0];
				$inv_total[1] += $rd[$rdIdx][13];
				$inv_total[2] += $rd[$rdIdx][14];
				$inv_total[3] += $rd[$rdIdx][15];
				$inv_total[4] += $rd[$rdIdx][16];

				$css_general	= $rd[$rdIdx][19];
				$css_qty		= $rd[$rdIdx][20];
				$css_amount		= $rd[$rdIdx][21];
				$rdIdx++;
			}

			print "<tr>\n";
			cell("INVOICE TOTAL", ' style="'.$display_css[$css_general].'" colspan="2" align="right" style="color:darkblue;"');
			cell(number_format((double)$inv_total[1]), ' style="'.$display_css[$css_qty].'" align="right" style="color:darkblue;"');
			cell(number_format((double)$inv_total[2]), ' style="'.$display_css[$css_amount].'" align="right" style="color:darkblue;"');
			cell(number_format((double)$inv_total[3]), ' style="'.$display_css[$css_amount].'" align="right" style="color:darkblue;"');
			cell(number_format((double)$inv_total[4]), ' style="'.$display_css[$css_amount].'" align="right" style="color:darkblue;"');
			cell(number_format((double)$inv_total[5]), ' style="'.$display_css[$css_amount].'" align="right" style="color:darkblue;"');
			print "</tr>\n";

			//nilai dihitung atau tidak dihitung
			//qty
			if($css_qty == 'red') {	$cus_total[1] += $inv_total[1]*-1; }
			else {					$cus_total[1] += $inv_total[1]; }

			//amount
			if($css_amount == 'red') {				//return
				$cus_total[0] += $inv_total[0]*-1;
				$cus_total[2] += $inv_total[2]*-1;
				$cus_total[3] += $inv_total[3]*-1;
				$cus_total[4] += $inv_total[4]*-1;
				$cus_total[5] += $inv_total[5]*-1;
			} else if($css_amount != 'grey') {	//billing
				$cus_total[0] += $inv_total[0];
				$cus_total[2] += $inv_total[2];
				$cus_total[3] += $inv_total[3];
				$cus_total[4] += $inv_total[4];
				$cus_total[5] += $inv_total[5];
			}
		}
	}
	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cus_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="background-color:lightyellow" colspan="3"');
	cell(number_format((double)$cus_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cus_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cus_total[3]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cus_total[4]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cus_total[5]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$g_total[0] += $cus_total[0];
	$g_total[1] += $cus_total[1];
	$g_total[2] += $cus_total[2];
	$g_total[3] += $cus_total[3];
	$g_total[4] += $cus_total[4];
	$g_total[5] += $cus_total[5];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th>CUSTOMER</th>
			<th width="12%">INVOICE NO</th>
			<th width="9%">INVOICE DATE</th>
			<th width="9%">DELIVERY DATE</th>
			<th width="10%">DELIVERY BY</th>
			<th width="7%">FREIGHT</th>
			<th width="3%">DISC<br />(%)</th>
			<th width="13%">MODEL NO</th>
			<th width="7%">UNIT PRICE<br/>(Rp)</th>
			<th width="3%">QTY<br>(EA)</th>
			<th width="7%">AMOUNT<br>-VAT (Rp)</th>
			<th width="6%">VAT<br>(Rp)</th>
			<th width="7%">AMOUNT<br>+VAT</th>
			<th width="7%">AMOUNT<br>+FRT/VAT</th>
		</tr>\n
END;

	print "<tr>\n";
	cell("<b>GRAND TOTAL</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$g_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="background-color:lightyellow"');
	cell("&nbsp", ' style="background-color:lightyellow"');
	cell("&nbsp", ' style="background-color:lightyellow"');
	cell(number_format((double)$g_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$g_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$g_total[3]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$g_total[4]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$g_total[5]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table>\n";
?>