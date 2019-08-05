<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim
*
* $$
*/

//SET WHERE PARAMETER
$tmp	= array();
$display_css['confirmed']	= "background-color:lightgrey; color:black";
$display_css['unconfirmed']	= "color:black";

if($_source == 1) {
	$tmp[]	= "b.inc_doc_type = 1"; 
} else if ($_source == 2) {
	$tmp[]	= "b.inc_doc_type = 2";
} else if ($_source == 3) {
	$tmp[]	= "b.inc_doc_type = 3";
}

if ($_cug_code != 'all') {
	//If group specified, 
	$tmp[]	= "b.cus_code IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$sql 	= " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
} else {
	$sql	= "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = b.cus_code),
		'Others') AS cug_name,"; // if null, return Others Group
}

if($_dept != 'all') {
	$tmp[] = "b.inc_dept = '$_dept'";
}

if ($_filter_date == 'turn_date' && $some_date != "") {
	$tmp[]   = "b.inc_date = DATE '$some_date'";
} else if ($_filter_date == 'turn_date' && $some_date == "") {
	$tmp[]   = "b.inc_date BETWEEN DATE '$period_from' AND '$period_to'";
} else if ($_filter_date == 'cfm_date' && $some_date != "") {
	$tmp[]   = "b.inc_confirmed_timestamp BETWEEN TIMESTAMP '$some_date 00:00:00' AND '$some_date 23:59:59'";
} else if ($_filter_date == 'cfm_date' && $some_date == "") {
	$tmp[]   = "b.inc_confirmed_timestamp BETWEEN TIMESTAMP '$period_from 00:00:00' AND '$period_to 23:59:59'";
}

if($_vat == 1) {
	$tmp[]	= "b.inc_type = 1"; 
} else if ($_vat == 2) {
	$tmp[]	= "b.inc_type = 2";
} else if ($_vat == 3) {
	$tmp[]	= "b.inc_type = 3";
}

if($_status == 0 && $_status != 'all') {
	$tmp[]	= "b.inc_is_confirmed = false"; 
} else if ($_status == 1 && $_status != 'all') {
	$tmp[]	= "b.inc_is_confirmed = true";
}

$strWhere   = implode(" AND ", $tmp);

$sql .= "
  a.cus_code AS cus_code,
  a.cus_full_name AS cus_full_name,
  b.inc_idx AS inc_idx,
  b.inc_std_idx AS std_idx,
  b.inc_doc_ref AS return_no,
  to_char(b.inc_date,'dd-Mon-YY') AS return_date,
  to_char(b.inc_confirmed_timestamp,'dd-Mon-YY') AS confirm_date,
  CASE
	WHEN b.inc_type = 1 THEN 'VAT'
	WHEN b.inc_type = 2 THEN 'NON'
	WHEN b.inc_type = 3 THEN 'NON SPECIFIED'
  END AS return_type,
  CASE
	WHEN b.inc_doc_type IN(1,2) THEN 'confirm_return.php'
	WHEN b.inc_doc_type = 3 THEN 'confirm_return_dt.php'
  END AS go_page,
  CASE
	WHEN b.inc_is_confirmed = true THEN 'confirmed'
	WHEN b.inc_is_confirmed = false THEN 'unconfirmed'
  END AS display_css,
  d.it_code AS it_code,
  d.it_model_no AS it_model_no,
  c.init_idx AS it_idx,
  c.init_qty AS qty,
  c.init_stock_qty AS stock_qty,
  c.init_demo_qty AS demo_qty,
  c.init_reject_qty AS reject_qty
FROM
 ".ZKP_SQL."_tb_customer AS a
 JOIN ".ZKP_SQL."_tb_incoming AS b USING(cus_code)
 JOIN ".ZKP_SQL."_tb_incoming_item AS c USING(inc_idx)
 JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
WHERE " . $strWhere ."
ORDER BY cug_name, cus_code, b.inc_date, b.inc_doc_ref, it_code, it_idx";

// raw data
$rd = array();
$rdIdx = 0;
$i = 0;
$cache = array("","","","");
$group0 = array();

$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['cug_name'],		//0
		$col['cus_code'],		//1
		$col['cus_full_name'],	//2
		$col['inc_idx'], 		//3
		$col['std_idx'], 		//4
		$col['return_no'], 		//5
		$col['return_date'], 	//6
		$col['confirm_date'],	//7
		$col['return_type'],	//8
		$col['it_code'],		//9
		$col['it_model_no'],	//10
		$col['it_idx'], 		//11
		$col['qty'],			//12
		$col['stock_qty'], 		//13
		$col['demo_qty'], 		//14
		$col['reject_qty'],		//15
		$col['go_page'],		//16
		$col['display_css']		//17
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

	if($cache[2] != $col['inc_idx']) {
		$cache[2] = $col['inc_idx'];
		$group0[$col['cug_name']][$col['cus_code']][$col['inc_idx']] = array();
	}

	if($cache[3] != $col['it_idx']) {
		$cache[3] = $col['it_idx'];
	}

	$group0[$col['cug_name']][$col['cus_code']][$col['inc_idx']][$col['it_idx']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL

$g_total = array(0,0,0,0);
$numInvoice = 0;

//GROUP
foreach ($group0 as $total1 => $group1) {

	echo "<span class=\"comment\"><b> PUSAT: ". $total1. "</b></span>\n";	//Group Name
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th rowspan="2">CUSTOMER</th>
			<th rowspan="2" width="15%" colspan="2">RETURN NO</th>
			<th rowspan="2" width="8%">RETURN DATE</th>
			<th rowspan="2" width="8%">CONFIRM DATE</th>
			<th rowspan="2" width="7%">RETURN TYPE</th>
			<th rowspan="2" width="20%">MODEL NO</th>
			<th rowspan="2" width="6%">QTY<br>(EA)</th>
			<th colspan="3" width="15%">SAVE TO (pcs)</th>
		</tr>
		<tr>
			<th width="5%">STOCK</th>
			<th width="5%">DEMO</th>
			<th width="5%">REJECT</th>
		</tr>\n
END;
	print "<tr>\n";

	$cus_total = array(0,0,0,0);
	$print_tr_1 = 0;
	//CUSTOMER INFO
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += count($group2);

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".$rd[$rdIdx][1]."]<br/>".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');	//Customer

		$print_tr_2 = 0;
		//BOOKING INFO
		foreach($group2 as $total3 => $group3) {
			$rowSpan = 0;
			array_walk_recursive($group3, 'getRowSpan');

			if($print_tr_2++ > 0) print "<tr>\n";
			cell('<input type="checkbox" name="chkDO[]" value="'.$rd[$rdIdx][3].'">', ' style="'.$display_css[$rd[$rdIdx][17]].'" valign="top" align="center" rowspan="'.$rowSpan.'"');		//Checkbox
			cell_link("<span class=\"bar\">".$rd[$rdIdx][5]."</span>", ' style="'.$display_css[$rd[$rdIdx][17]].'" align="center" valign="top" rowspan="'.$rowSpan.'"',
					' href="'.$rd[$rdIdx][16].'?_inc_idx='.$rd[$rdIdx][3].'&_std_idx='.$rd[$rdIdx][4].'"');	//Return code
			cell($rd[$rdIdx][6], ' style="'.$display_css[$rd[$rdIdx][17]].'" valign="top" align="center" rowspan="'.$rowSpan.'"');		//Return date
			cell($rd[$rdIdx][7], ' style="'.$display_css[$rd[$rdIdx][17]].'" valign="top" align="center" rowspan="'.$rowSpan.'"');		//Confirm date
			cell($rd[$rdIdx][8], ' style="'.$display_css[$rd[$rdIdx][17]].'" valign="top" align="center" rowspan="'.$rowSpan.'"');		//Return type

			$inv_total	= array(0,0,0,0);
			$print_tr_3 = 0;
			//ITEM LIST
			foreach($group3 as $group4) {
				if($print_tr_3++ > 0) print "<tr>\n";

				cell("[".trim($rd[$rdIdx][9])."] ".$rd[$rdIdx][10], ' style="'.$display_css[$rd[$rdIdx][17]].'"');			//Model no
				cell(number_format($rd[$rdIdx][12],2), ' style="'.$display_css[$rd[$rdIdx][17]].'" align="right"');		//Qty
				cell(($rd[$rdIdx][13]==0)?'':number_format($rd[$rdIdx][13],2), ' style="'.$display_css[$rd[$rdIdx][17]].'" align="right"');	//stock qty
				cell(($rd[$rdIdx][14]==0)?'':number_format($rd[$rdIdx][14],2), ' style="'.$display_css[$rd[$rdIdx][17]].'" align="right"');	//demo qty
				cell(($rd[$rdIdx][15]==0)?'':number_format($rd[$rdIdx][15],2), ' style="'.$display_css[$rd[$rdIdx][17]].'" align="right"');	//reject qty
				print "</tr>\n";

				$inv_total[0] += $rd[$rdIdx][12]; //qty
				$inv_total[1] += $rd[$rdIdx][13]; //qty
				$inv_total[2] += $rd[$rdIdx][14]; //qty
				$inv_total[3] += $rd[$rdIdx][15]; //qty
				$display		= $rd[$rdIdx][17];
				$rdIdx++;
			}
			print "<tr>\n";
			cell("INVOICE TOTAL", ' style="'.$display_css[$display].';color:darkblue;" colspan="6" align="right"');
			cell(number_format($inv_total[0],2), ' style="'.$display_css[$display].';color:darkblue;" align="right"');
			cell(number_format($inv_total[1],2), ' style="'.$display_css[$display].';color:darkblue;" align="right"');
			cell(number_format($inv_total[2],2), ' style="'.$display_css[$display].';color:darkblue;" align="right"');
			cell(number_format($inv_total[3],2), ' style="'.$display_css[$display].';color:darkblue;" align="right"');
			print "</tr>\n";
	
			$cus_total[0] += $inv_total[0];
			$cus_total[1] += $inv_total[1];
			$cus_total[2] += $inv_total[2];
			$cus_total[3] += $inv_total[3];
			$numInvoice++;
		}
	}
	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="7" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($cus_total[0],2), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($cus_total[1],2), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($cus_total[2],2), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($cus_total[3],2), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$g_total[0] += $cus_total[0];
	$g_total[1] += $cus_total[1];
	$g_total[2] += $cus_total[2];
	$g_total[3] += $cus_total[3];
}

print <<<END
<table width="100%" class="table_layout">
	<tr>
		<td><input type="checkbox" name="chkAll" onclick="checkAll(this.checked)"><span class="comment">check all</span></td>
		<td align="right"><button name='btnSummarize' class='input_btn' style='width:130px;' onclick="summarizeDO()"><img src="../../_images/icon/list.gif" width="20px" align="middle"> &nbsp; Summarize</button></td>
	</tr>
</table><br />
END;

print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th rowspan="2">CUSTOMER</th>
			<th rowspan="2" width="15%" colspan="2">RETURN NO</th>
			<th rowspan="2" width="8%">RETURN DATE</th>
			<th rowspan="2" width="8%">CONFIRM DATE</th>
			<th rowspan="2" width="7%">RETURN TYPE</th>
			<th rowspan="2" width="20%">MODEL NO</th>
			<th rowspan="2" width="6%">QTY<br>(EA)</th>
			<th colspan="3" width="15%">SAVE TO (pcs)</th>
		</tr>
		<tr>
			<th width="5%">STOCK</th>
			<th width="5%">DEMO</th>
			<th width="5%">REJECT</th>
		</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="7" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($g_total[0],2), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($g_total[1],2), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($g_total[2],2), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($g_total[3],2), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>