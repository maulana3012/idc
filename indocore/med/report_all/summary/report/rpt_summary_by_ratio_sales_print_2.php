<?php
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

	include "rpt_summary_by_ratio_sales_print_4.php";

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
?>