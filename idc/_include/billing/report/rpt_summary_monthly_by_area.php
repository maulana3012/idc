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
$tmp_bill	= array();
$tmp_dr		= array();
$tmp_turn	= array();

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

if($_cug_code != '') {
	$tmp_bill[]	= "a.cus_code IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp_dr[]	= "a.cus_code IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp_turn[]	= "a.cus_code IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
}

if($_cus_code != '') {
	$tmp_bill[]	= "a.cus_code = '$_cus_code'";
	$tmp_dr[]	= "a.cus_code = '$_cus_code'";
	$tmp_turn[]	= "a.cus_code = '$_cus_code'";
}

if($_filter_doc == 'sales') {
	$tmp_turn[]	= "turn_code IS NULL";
} else if($_filter_doc == 'return') {
	$tmp_bill[]	= "bill_code IS NULL";
	$tmp_dr[]	= "dr_code IS NULL";
}

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp_bill[]	= "d.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_dr[]	= "d.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_turn[]	= "d.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if($currentDept != 'report') {
	$tmp_bill[]	= "bill_dept='$department'";
	$tmp_dr[]	= "dr_dept='$department'";
	$tmp_turn[]	= "turn_dept='$department'";
}

$tmp_bill[]	= "bill_inv_date BETWEEN DATE '$period_from' AND '$period_to'";
$tmp_dr[]	= "dr_date BETWEEN DATE '$period_from' AND '$period_to'";
$tmp_turn[]	= "turn_return_date BETWEEN DATE '$period_from' AND '$period_to'";

$strWhereBilling	= implode(" AND ", $tmp_bill);
$strWhereDR		= implode(" AND ", $tmp_dr);
$strWhereReturn		= implode(" AND ", $tmp_turn);

$sql = "
	SELECT icat_pidx, d.icat_midx, TRIM(d.it_code) AS it_code, d.it_model_no, TRIM(cus_city) AS city
	FROM
	  ".ZKP_SQL."_tb_customer AS a
	  JOIN ".ZKP_SQL."_tb_billing AS b ON bill_ship_to = cus_code
	  JOIN ".ZKP_SQL."_tb_billing_item AS c USING (bill_code)
	  JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
	  JOIN ".ZKP_SQL."_tb_item_cat AS e ON d.icat_midx=e.icat_midx
	WHERE $strWhereBilling
UNION
	SELECT icat_pidx, d.icat_midx, TRIM(d.it_code) AS it_code, d.it_model_no, TRIM(cus_city) AS city
	FROM
	  ".ZKP_SQL."_tb_customer AS a
	  JOIN ".ZKP_SQL."_tb_dr AS b ON dr_ship_to = cus_code
	  JOIN ".ZKP_SQL."_tb_dr_item AS c USING (dr_code)
	  JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
	  JOIN ".ZKP_SQL."_tb_item_cat AS e ON d.icat_midx=e.icat_midx
	WHERE $strWhereDR
UNION
	SELECT icat_pidx, d.icat_midx, TRIM(d.it_code) AS it_code, d.it_model_no, TRIM(cus_city) AS city
	FROM
	  ".ZKP_SQL."_tb_customer AS a
	  JOIN ".ZKP_SQL."_tb_return AS b ON turn_ship_to = cus_code
	  JOIN ".ZKP_SQL."_tb_return_item AS c USING (turn_code)
	  JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
	  JOIN ".ZKP_SQL."_tb_item_cat AS e ON d.icat_midx=e.icat_midx
	WHERE $strWhereReturn
GROUP BY icat_pidx, d.icat_midx, d.it_code, d.it_model_no, city
ORDER BY icat_pidx, icat_midx, it_code, city";

$sqlMonth = "
	SELECT
		'A' AS source,
		bill_code AS doc_ref,
		TRIM(c.it_code) AS it_code,
		to_char(bill_inv_date, 'YYYYMM') AS period,
		TRIM(cus_city) AS city,
		biit_qty AS qty,
		biit_qty * TRUNC(c.biit_unit_price * (1 - b.bill_discount/100),2) AS amount
	FROM
	  ".ZKP_SQL."_tb_customer AS a
	  JOIN ".ZKP_SQL."_tb_billing AS b ON bill_ship_to = cus_code
	  JOIN ".ZKP_SQL."_tb_billing_item AS c USING (bill_code)
	  JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
	  JOIN ".ZKP_SQL."_tb_item_cat AS e ON d.icat_midx=e.icat_midx
	WHERE $strWhereBilling
UNION
	SELECT
		'B' AS source,
		dr_code AS doc_ref,
		TRIM(c.it_code) AS it_code,
		to_char(dr_date, 'YYYYMM') AS period,
		TRIM(cus_city) AS city,
		drit_qty AS qty,
		null AS amount
	FROM
	  ".ZKP_SQL."_tb_customer AS a
	  JOIN ".ZKP_SQL."_tb_dr AS b ON dr_ship_to = cus_code
	  JOIN ".ZKP_SQL."_tb_dr_item AS c USING (dr_code)
	  JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
	  JOIN ".ZKP_SQL."_tb_item_cat AS e ON d.icat_midx=e.icat_midx
	WHERE $strWhereDR
UNION
	SELECT
		'C' AS source,
		turn_code AS doc_ref,
		TRIM(c.it_code) AS it_code,
		to_char(turn_return_date, 'YYYYMM') AS period,
		TRIM(cus_city) AS city,
		reit_qty*-1 AS qty,
		CASE
		  WHEN b.turn_return_condition = 1 THEN null
		  ELSE (reit_qty * TRUNC(c.reit_unit_price * (1 - b.turn_discount/100),2)) * -1
		END AS amount
	FROM
	  ".ZKP_SQL."_tb_customer AS a
	  JOIN ".ZKP_SQL."_tb_return AS b ON turn_ship_to = cus_code
	  JOIN ".ZKP_SQL."_tb_return_item AS c USING (turn_code)
	  JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
	  JOIN ".ZKP_SQL."_tb_item_cat AS e ON d.icat_midx=e.icat_midx
	WHERE $strWhereReturn
ORDER BY it_code, period ";
$tot = array();
$cit = array();
$res =& query($sqlMonth);
while($col =& fetchRowAssoc($res)) {
	$rd[] = array(
		$col['it_code'],$col['city'],$col['period'],$col['qty'],$col['amount']
	);

	if(empty($tot[$col['it_code']][$col['city']][$col['period']]['qty']))
		$tot[$col['it_code']][$col['city']][$col['period']]['qty'] = $col['qty'];
	else	$tot[$col['it_code']][$col['city']][$col['period']]['qty'] += $col['qty'];
	
	if(empty($tot[$col['it_code']][$col['city']][$col['period']]['amount']))
		$tot[$col['it_code']][$col['city']][$col['period']]['amount'] = $col['amount'];
	else	$tot[$col['it_code']][$col['city']][$col['period']]['amount'] += $col['amount'];

}

// raw data
$rd 	= array();
$rdIdx	= 0;
$cache	= array("","","");
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {
	$rd[] = array(
		$col['icat_midx'],	//0
		$col['it_code'],	//1
		$col['it_model_no'],	//2
		$col['city']		//3
	);

	if($cache[0] != $col['icat_midx']) {
		$cache[0] = $col['icat_midx'];
		$group0[$col['icat_midx']] = array();
	}

	if($cache[1] != $col['it_code']) {
		$cache[1] = $col['it_code'];
		$group0[$col['icat_midx']][$col['it_code']] = array();
	}
	
	if($cache[2] != $col['city']) {
		$cache[2] = $col['city'];
	}

	$group0[$col['icat_midx']][$col['it_code']][$col['city']] = 1;
}
/*
echo "<pre>";
//echo $sql;
//var_dump($group0);
echo "</pre>";
exit;
*/
//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$grand_total = array();

//DEPARTEMEN
foreach ($group0 as $total1 => $group1) {

	//get category path from current icat_midx.
	$path = executeSP(ZKP_SQL."_getCategoryPath", $rd[$rdIdx][0]);
	eval(html_entity_decode($path[0]));	
	$path = array_reverse($path);
	$cat = $path[1][4]." > ".$path[2][4]." > ".$path[3][4] ;

echo "<span class=\"comment\"><b> CATEGORY: $cat</b></span>\n";
print <<<END
<table class="table_f" width="$table_len px">
	<tr height="15px">
		<th width="200px" rowspan="2">MODEL NO</th>
		<th width="100px" rowspan="2">CITY</th>
		<th colspan="$mon_length">MONTH</th>
		<th width="{$len[$_show]}px" rowspan="2">TOTAL</th>
		<th width="{$len[$_show]}px" rowspan="2">AVG</th>
	</tr>
	<tr height="25px">\n
END;

	$start_month = $_month_from;
	$start_year	 = $_year_from;
	for($i=0; $i<$mon_length; $i++) {
		if($start_month>12) { $start_month = $start_month-12; $start_year+=1;}
		echo "\t\t<th width=\"{$len[$_show]}\">".$month[$start_month]."<br />$start_year</th>\n";
		$start_month++;
	}
print <<<END
	</tr>\n
END;

	$item_cat = array();
	$print_tr_1 = 0;
	print "<tr>\n";
	//GROUP
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
	

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".trim($rd[$rdIdx][1])."] ". $rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');   // Model No

		$item = array();
		$print_tr_2 = 0;
		//ORDER
		foreach($group2 as $total3 => $group3) {
			if($print_tr_2++ > 0) print "<tr>\n";
			cell(($rd[$rdIdx][3] == '') ? '<i>Undefined</i>' : $rd[$rdIdx][3]);	// City
			$item_total	= 0;
			for($j=0; $j<$mon_length; $j++) {
				$amount = isset($tot[$rd[$rdIdx][1]][$rd[$rdIdx][3]][$period[$j]][$_show]) ? $tot[$rd[$rdIdx][1]][$rd[$rdIdx][3]][$period[$j]][$_show] : 0;
				cell(number_format((double)$amount,0), ' align="right"');
				if(isset($item[$j]))
					$item[$j] += $amount;
				else	$item[$j] = $amount;
				$item_total 	+= $amount;
				$start_month++;
			}
			cell(number_format((double)$item_total,0), ' align="right"');
			cell(number_format((double)$item_total/$mon_length,2), ' align="right"');
			print "</tr>\n";
	
			if(isset($item[$mon_length])) {
				$item[$mon_length] += $item_total;
				$item[$mon_length+1] += $item_total/$mon_length;
			} else {
				$item[$mon_length] = $item_total;
				$item[$mon_length+1] = $item_total/$mon_length;
			}
			$model_no = "[".trim($rd[$rdIdx][1])."] ". $rd[$rdIdx][2];
			$rdIdx++;
		}

		print "<tr>\n";
		cell($model_no, ' colspan="2" align="right" style="color:darkblue"');
		for($k=0; $k<$mon_length; $k++) {
			cell(empty($item[$k]) ? 0 : number_format((double)$item[$k],0), ' align="right" style="color:darkblue"');
			if(isset($item_cat[$k]))
				$item_cat[$k] += $item[$k];
			else	$item_cat[$k] = $item[$k];
		}
		cell(number_format((double)$item[$k],0), ' align="right" style="color:darkblue"');
		cell(number_format((double)$item[$k+1],2), ' align="right" style="color:darkblue"');
		print "</tr>\n";
		
		if(isset($item_cat[$mon_length])) {
			$item_cat[$mon_length]	 += $item[$mon_length];
			$item_cat[$mon_length+1] += $item[$mon_length+1]/$mon_length;
		} else {
			$item_cat[$mon_length]	 = $item[$mon_length];
			$item_cat[$mon_length+1] = $item[$mon_length+1]/$mon_length;
		}
	}

	print "<tr>\n";
	cell("<b>$cat</b>", ' colspan="2" align="right" style="color:brown; background-color:lightyellow"');
	for($k=0; $k<$mon_length; $k++) {
		cell(empty($item_cat[$k]) ? 0 : number_format((double)$item_cat[$k],0), ' align="right" style="color:brown; background-color:lightyellow"');
		if(isset($grand_total[$k]))
			$grand_total[$k] += $item_cat[$k];
		else	$grand_total[$k] = $item_cat[$k];
	}
	cell(number_format((double)$item_cat[$k],0), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$item_cat[$k+1],2), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br /><br />";

	if(isset($grand_total[$mon_length])) {
		$grand_total[$mon_length]	+= $item_cat[$mon_length];
		$grand_total[$mon_length+1]	+= $item_cat[$mon_length+1];
	} else {
		$grand_total[$mon_length]	= $item_cat[$mon_length];
		$grand_total[$mon_length+1]	= $item_cat[$mon_length+1];
	}
}

print <<<END
<table class="table_f" width="$table_len px">
	<tr height="15px">
		<th width="200px" rowspan="2">MODEL NO</th>
		<th width="100px" rowspan="2">CITY</th>
		<th colspan="$mon_length">MONTH</th>
		<th width="{$len[$_show]}px" rowspan="2">TOTAL</th>
		<th width="{$len[$_show]}px" rowspan="2">AVG</th>
	</tr>
	<tr height="25px">\n
END;

	$start_month = $_month_from;
	$start_year	 = $_year_from;
	for($i=0; $i<$mon_length; $i++) {
		if($start_month>12) { $start_month = $start_month-12; $start_year+=1;}
		echo "\t\t<th width=\"{$len[$_show]}px\">".$month[$start_month]."<br />$start_year</th>\n";
		$start_month++;
	}
print <<<END
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="2" align="right" style="color:brown; background-color:lightyellow"');
for($i=0; $i<$mon_length; $i++) {
	cell((isset($grand_total[$i])) ? number_format((double)$grand_total[$i],0) : 0, ' align="right" style="color:brown; background-color:lightyellow"');
}
cell((isset($grand_total[$i])) ? number_format((double)$grand_total[$i],0) : 0, ' align="right" style="color:brown; background-color:lightyellow"');
cell((isset($grand_total[$i+1])) ? number_format((double)$grand_total[$i+1],2) : 0, ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>