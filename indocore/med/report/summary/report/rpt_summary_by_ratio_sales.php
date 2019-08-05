<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim
*
*
*/
//SET WHERE PARAMETER
$channel	= array('A'=>'Apotik', 'D'=>'Dealer', 'H'=>'Hospital', 'M'=>'Marketing', 'P'=>'Pharmaceutical', 'T'=>'Tender');
$item		= array('Healthcare Tools', 'Diagnostic Tools', 'Others', 'Delivery charge');

$tmp_bill	= array();
$tmp_turn	= array();
$tmp_paid	= array();

if(ZKP_FUNCTION == 'ALL') {
	if($_order_by != 'all'){
		$tmp_bill[]		= "bill_ordered_by = $_order_by";
		$tmp_turn[]		= "turn_ordered_by = $_order_by";
		if($_order_by == '1') {
			$tmp_paid[] 	= "substr(bill_code,1,1) = 'I'";
		} else if($_order_by == '2') {
			$tmp_paid[] 	= "substr(bill_code,1,1) = 'M'";
		}
	}
} else {
	$tmp_bill[]		= "bill_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill_code,'billing')";
	$tmp_turn[]		= "turn_ordered_by = ".$cboFilter[1][ZKP_URL][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', turn_code,'billing_return')";
	if($cboFilter[1][ZKP_URL][0][0] == '1') {
		$tmp_paid[] 	= "substr(bill_code,1,1) = 'I' AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill_code,'billing')";
	} else if($cboFilter[1][ZKP_URL][0][0] == '2') {
		$tmp_paid[] 	= "substr(bill_code,1,1) = 'M' AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_URL."', bill_code,'billing')";
	}
}

if ($_filter_doc == "I") {
	$tmp_turn[]	= "turn_code is NULL";
	$tmp_paid[]	= "pay_note IN ('USUAL','DEPOSIT-A', 'RETURN','CROSS_TRANSFER+','CROSS_TRANSFER-')";
} else if ($_filter_doc == "R") {
	$tmp_bill[]	= "bill_code is NULL";
	$tmp_paid[]	= "pay_paid <= 0 AND pay_note IN ('USUAL','DEPOSIT-A', 'RETURN')";
} else {
	$tmp_paid[]	  = "pay_note IN ('USUAL','DEPOSIT-A', 'RETURN','CROSS_TRANSFER+','CROSS_TRANSFER-')";
}

if($_dept != 'all') {
	$tmp_bill[] = "bill_dept = '$_dept'";
	$tmp_turn[] = "turn_dept = '$_dept'";
	$tmp_paid[] = "bill_dept = '$_dept'";
}

if ($some_date != "") {
	$tmp_bill[] = "bill_inv_date = DATE '$some_date'";
	$tmp_turn[] = "turn_return_date = DATE '$some_date'";
	$tmp_paid[] = "pay_date = DATE '$some_date'";
} else {
	$tmp_bill[]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_turn[]	= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_paid[]	= "pay_date BETWEEN DATE '$period_from' AND '$period_to'";
}

if($_vat == 'vat') {
	$tmp_bill[]	= "bill_vat > 0";
	$tmp_turn[]	= "turn_vat > 0";
	$tmp_paid[]	= "bill_vat > 0";
} else if($_vat == 'vat-IO') {
	$tmp_bill[]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
	$tmp_turn[]	= "turn_vat > 0";
	$tmp_paid[]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'O'";
}else if($_vat == 'vat-IP') {
	$tmp_bill[]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
	$tmp_turn[]	= "turn_code = NULL";
	$tmp_paid[]	= "bill_vat > 0 AND substr(bill_code,2,1) = 'P'";
} else if ($_vat == 'non') {
	$tmp_bill[]	= "bill_vat = 0";
	$tmp_turn[]	= "turn_vat = 0";
	$tmp_paid[]	= "bill_vat = 0";
}
/*
$tmp_bill[] = "bill_code in (
'IX-00061D-K09'
)";
*/
$tmp_turn[] = "turn_total_return > 0";

$strWhereBill    = implode(" AND ", $tmp_bill);
$strWhereTurn    = implode(" AND ", $tmp_turn);
$strWherePayment = implode(" AND ", $tmp_paid);

/*
SQL
*/
$items = array(0=>array(1,70,116,146,176,240,125,59), 1=>array(46,232,282,252,109));
$catListAK = array();
$catListAD = array();
for($k = 0; $k < count($items[0]); $k++) {
	$catListAK0	= executeSP(ZKP_SQL."_getSubCategory", $items[0][$k]);
	$catListAK[]	= (empty($catListAK0[0]) ? "0" : $catListAK0[0]);
}
for($k = 0; $k < count($items[1]); $k++) {
	$catListAD0	= executeSP(ZKP_SQL."_getSubCategory", $items[1][$k]);
	$catListAD[]	= (empty($catListAK0[0]) ? "0" : $catListAD0[0]);
}
$catAK	= implode(', ', $catListAK);
$catAD	= implode(', ', $catListAD);
$t		= array();

foreach ($channel as $i => $key) {

	include "rpt_generate_summary_by_ratio_sales.php";

	// GENERAL ========================================================================================================
	$res =& query($sql);
	while($col =& fetchRowAssoc($res)) {
		if(isset($t['general'][$i][$col['item_cat']][0])) {
			$t['general'][$i][$col['item_cat']][0]	+= round($col['total_invoice_before_vat'],0);
			$t['general'][$i][$col['item_cat']][1]	+= round($col['total_invoice'],0);
		} else {
			$t['general'][$i][$col['item_cat']][0]	= round($col['total_invoice_before_vat'],0);
			$t['general'][$i][$col['item_cat']][1]	= round($col['total_invoice'],0);
		}
	}
	$tot = 0;
	for($j = 0; $j < count($item); $j++) {
		$tot += (isset($t['general'][$i][$j][0])) ? $t['general'][$i][$j][0] : 0;
	}
	for($j = 0; $j < count($item); $j++) {
		if(isset($t['general'][$i][$j][0])) {
			$t['general'][$i][$j][2] =  $t['general'][$i][$j][0] * 100 / $tot;
		}
	}

	// REALTIME =======================================================================================================
	$res_bill =& query($sql_bill);
	while($col_bill =& fetchRowAssoc($res_bill)) {
		if(isset($t['realtime'][$i][$col_bill['item_cat']][0])) {
			$t['realtime'][$i][$col_bill['item_cat']][0]	+= $col_bill['total_before_vat'];
			$t['realtime'][$i][$col_bill['item_cat']][1]	+= $col_bill['total_billing'];
		} else {
			$t['realtime'][$i][$col_bill['item_cat']][0]	= $col_bill['total_before_vat'];
			$t['realtime'][$i][$col_bill['item_cat']][1]	= $col_bill['total_billing'];
		}
		$bill_code[$i][$col_bill['inv']][$col_bill['item_cat']][0] = $col_bill['total_billing'];
	}

	$res_bill =& query($sql_bill_realtime);
	while($col_bill =& fetchRowAssoc($res_bill)) {
		if(isset($t['realtime'][$i][$col_bill['item_cat']][2])) {
			$t['realtime'][$i][$col_bill['item_cat']][2] += $col_bill['total_return'];
		} else {
			$t['realtime'][$i][$col_bill['item_cat']][2] = $col_bill['total_return'];
		}
		$bill_code[$i][$col_bill['inv']][$col_bill['item_cat']][1]	= $col_bill['total_return'];
	}
	for($j = 0; $j < count($item); $j++) {
		if(isset($t['realtime'][$i][$j][2])) {
			$t['realtime'][$i][$j][2] = $t['realtime'][$i][$j][1] + $t['realtime'][$i][$j][2];
		} else {
			$t['realtime'][$i][$j][2] = (isset($t['realtime'][$i][$j][1])) ? $t['realtime'][$i][$j][1] : 0;
		}
	}

	if(isset($bill_code[$i])) {
		foreach($bill_code[$i] as $key1 => $val1) {
			$bill = $key1;
			$bills = "";
			foreach($val1 as $key2 => $val2) {
				if($bills != $bill) {
				$bills = $bill;
				$bill_total = 0;
				$bill_code[$i][$bill][$j][3] = 0;
				for($j = 0; $j < count($item); $j++) {
					if(isset($bill_code[$i][$bill][$j][0])) {
						$bill_code[$i][$bill][$j][0] = (isset($bill_code[$i][$bill][$j][0])) ? $bill_code[$i][$bill][$j][0] : 0;
						$bill_code[$i][$bill][$j][1] = (isset($bill_code[$i][$bill][$j][1])) ? $bill_code[$i][$bill][$j][1] : 0;
						$bill_code[$i][$bill][$j][2] = $bill_code[$i][$bill][$j][0] + $bill_code[$i][$bill][$j][1];
					} else if(isset($bill_code[$i][$bill][$j][0])) {
						$bill_code[$i][$bill][$j][0] = (isset($bill_code[$i][$bill][$j][0])) ? $bill_code[$i][$bill][$j][0] : 0;
						$bill_code[$i][$bill][$j][2] = $bill_code[$i][$bill][$j][0];
					}
					$bill_total += (isset($bill_code[$i][$bill][$j][2])) ? $bill_code[$i][$bill][$j][2] : 0;
				}
				$bill_total = $bill_total;

				if(isset($bill_code[$i][$bill][3][2])) {
					$bill_total_fre = $bill_total - $bill_code[$i][$bill][3][2];
				} else {
					$bill_total_fre = $bill_total;
				}

				/*
				0. realtime billing inc freight charge
				1. realtime billing exc freight charge
				2. pay_paid inc deposit-B
				3. pay_paid deposit-B
				4. cat 0,1,2,3
				*/
				$pay = array(round($bill_total), round($bill_total_fre), 0, 0,0,0,0,0);	
				$payment = array();
				$sql_pay = "SELECT * FROM ".ZKP_SQL."_tb_payment WHERE bill_code = '$bill'";
				$res_pay =& query($sql_pay);
				while($col =& fetchRowAssoc($res_pay)) {
					if($col['pay_note'] != 'DEPOSIT-B') {
						$pay[2] += $col['pay_paid'];
						$pay[3] += $col['pay_paid'];
					} else if($col['pay_note'] == 'DEPOSIT-B') {
						$pay[2] += $col['pay_paid'];
					}
				}

				/*if($pay[2] > $pay[3]) {
					if($pay[2] >= $pay[0]) {
					for($j = 0; $j < count($item); $j++) {
						$pay[4+$j] = 0;
					}
					}
					
				} else {*/
					if($pay[2] >= $pay[0]) {
						for($j = 0; $j < count($item); $j++) {
							$pay[4+$j] = (isset($bill_code[$i][$bill][$j][2])) ? $bill_code[$i][$bill][$j][2] : 0;
						}
					} else if ($pay[2] < $pay[0] && $pay[2] >= $pay[1]) {
						for($j = 0; $j < count($item)-1; $j++) {
							$pay[4+$j] = (isset($bill_code[$i][$bill][$j][2])) ? $bill_code[$i][$bill][$j][2] : 0;
						}
					} else if ($pay[2] < $pay[1] && $pay[2] > 0) {
						$per = array(0,0,0,0);
						for($j = 0; $j < count($item)-1; $j++) {
							if(isset($bill_code[$i][$bill][$j][2]))
									$per[$j] = round((($bill_code[$i][$bill][$j][2] * 100) / $bill_total_fre),2);
							else 	$per[$j] = 0;
							$pay[4+$j] = round(($per[$j] / 100) * $pay[2],2);
						}
					} else if ($pay[2] == 0) {
						for($j = 0; $j < count($item)-1; $j++) {
							$pay[4+$j] = 0;
						}
					}
				}
				//}

			}

			/*
			3. payment inc DEPOSIT-B
			4. payment exc DEPOSIT-B
			5. remain billing
			*/
			for($j = 0; $j < count($item); $j++) {
				if(isset($t['realtime'][$i][$j][3]))
						$t['realtime'][$i][$j][3] += (isset($pay[4+$j])) ? $pay[4+$j] : 0;
				else	$t['realtime'][$i][$j][3] = (isset($pay[4+$j])) ? $pay[4+$j] : 0;
			}
			for($j = 0; $j < count($item); $j++) {
				$t['realtime'][$i][$j][4] = round($t['realtime'][$i][$j][2]) - round($t['realtime'][$i][$j][3]);
			}
/*
echo "<pre>";
var_dump($pay);
echo "</pre>";
*/
		}
	}

	$tot = 0;
	for($j = 0; $j < count($item); $j++) {
		$tot += (isset($t['realtime'][$i][$j][0])) ? $t['realtime'][$i][$j][0] : 0;
	}
	for($j = 0; $j < count($item); $j++) {
		if($t['realtime'][$i][$j][2] != 0) {
			$t['realtime'][$i][$j][5] =  $t['realtime'][$i][$j][3] * 100 / $t['realtime'][$i][$j][2];
			$t['realtime'][$i][$j][6] =  $t['realtime'][$i][$j][4] * 100 / $t['realtime'][$i][$j][2];
		}
	}

}
/*
echo "<pre>";
var_dump($t['realtime']['H']);
echo "</pre>";
*/
$amounttotal = array(0,0,0,0,0,0,0);
print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th rowspan="2">TEAM</th>
			<th rowspan="2">CATEGORY ITEM</th>
			<th width="10%">BILLING<br />(Rp)</th>
			<th width="5%">% BILL</th>
			<th width="10%">FULL BILLING<br />(Rp)</th>
			<th width="10%">PAYMENT<br />REALTIME<br />(Rp)</th>
			<th width="10%">REMAIN<br />(Rp)</th>
			<th width="5%">% PAID</th>
			<th width="5%">% REMAIN</th>
		</tr>
		<tr>
			<th>(1)</th>
			<th>(2)</th>
			<th>(3)</th>
			<th>(4)</th>
			<th>(5)</th>
			<th>(6)</th>
			<th>(7)</th>
		</tr>\n
END;
foreach ($channel as $i => $key) {
	$total = array(0,0,0,0,0,0,0);
	print "<tr>\n";	
	cell($channel[$i], ' rowspan="5" valign="top"');

	for($j = 0; $j < count($item); $j++) {
		if($j > 0) print "<tr>\n";	

		cell($item[$j]);
		cell((empty($t['general'][$i][$j][0]) ? 0 : number_format((double)$t['general'][$i][$j][0])), ' align="right"');
		cell((empty($t['general'][$i][$j][2]) ? 0 : number_format((double)$t['general'][$i][$j][2],2)."%"), ' align="right"');
		cell((empty($t['general'][$i][$j][1]) ? 0 : number_format((double)$t['general'][$i][$j][1])), ' align="right"');
		cell((empty($t['realtime'][$i][$j][3]) ? 0 : number_format((double)$t['realtime'][$i][$j][3])), ' align="right"');
		cell((empty($t['realtime'][$i][$j][4]) ? 0 : number_format((double)$t['realtime'][$i][$j][4])), ' align="right"');
		cell((empty($t['realtime'][$i][$j][5]) ? 0 : number_format((double)$t['realtime'][$i][$j][5],2)."%"), ' align="right"');
		cell((empty($t['realtime'][$i][$j][6]) ? 0 : number_format((double)$t['realtime'][$i][$j][6],2)."%"), ' align="right"');
		print "</tr>\n";

		$total[0] += (empty($t['general'][$i][$j][0])) ? 0 : $t['general'][$i][$j][0];
		$total[1] += (empty($t['general'][$i][$j][2])) ? 0 : $t['general'][$i][$j][2];
		$total[2] += (empty($t['general'][$i][$j][1])) ? 0 : $t['general'][$i][$j][1];
		$total[3] += (empty($t['realtime'][$i][$j][3])) ? 0 : $t['realtime'][$i][$j][3];
		$total[4] += (empty($t['realtime'][$i][$j][4])) ? 0 : $t['realtime'][$i][$j][4];
		$total[5] += (empty($t['realtime'][$i][$j][5])) ? 0 : $t['realtime'][$i][$j][5];
		$total[6] += (empty($t['realtime'][$i][$j][6])) ? 0 : $t['realtime'][$i][$j][6];

	}

	print "<tr>\n";	
	cell("<b>Total ".$channel[$i]." </b>", ' align="right" style="color:brown;"');
	cell(number_format((double)$total[0]), ' align="right" style="color:brown;"');
	cell(number_format((double)$total[1],2)."%", ' align="right" style="color:brown;"');
	cell(number_format((double)$total[2]), ' align="right" style="color:brown;"');
	cell(number_format((double)$total[3]), ' align="right" style="color:brown;"');
	cell(number_format((double)$total[4]), ' align="right" style="color:brown;"');
	cell("");
	cell("");

	for($k=0; $k<7; $k++) {
		$amounttotal[$k] += $total[$k];
	}
	print "</tr>\n";

}
print "\t<tr>\n";
cell("<b>TOTAL</b>", ' colspan="2" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$amounttotal[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell("", ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$amounttotal[2]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$amounttotal[3]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$amounttotal[4]), ' align="right" style="color:brown; background-color:lightyellow"');
cell("", ' style="color:brown; background-color:lightyellow"');
cell("", ' style="color:brown; background-color:lightyellow"');
print "\t</tr>\n";
print "</table><br />\n";
?>