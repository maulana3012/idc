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
$tmp_ord	= array();
$tmp_turn	= array();

if($_cug_code != '') {
	$tmp_ord[]	= "a.cus_code IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp_turn[]	= "a.cus_code IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
} else if($_cus_code != '') {
	$tmp_ord[] 	= "a.cus_code = '$_cus_code'";
	$tmp_turn[]	= "a.cus_code = '$_cus_code'";
}

if($_filter_doc == 'order') {
	$tmp_turn[]	= "reor_code IS NULL";
} else if($_filter_doc == 'OO') {
	$tmp_ord[]	= "ord_type = 'OO'";
	$tmp_turn[]	= "reor_code IS NULL";
} else if($_filter_doc == 'OK') {
	$tmp_ord[]	= "ord_type = 'OK'";
	$tmp_turn[]	= "reor_code IS NULL";
} else if($_filter_doc == 'return') {
	$tmp_ord[]	= "ord_code IS NULL";
} else if($_filter_doc == 'RO') {
	$tmp_ord[]	= "ord_code IS NULL";
	$tmp_turn[]	= "reor_type = 'RO'";
} else if($_filter_doc == 'RK') {
	$tmp_ord[]	= "ord_code IS NULL";
	$tmp_turn[]	= "reor_type = 'RK'";
}

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp_ord[]	= "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_turn[]	= "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

$tmp_ord[]	= "ord_po_date BETWEEN DATE '$period_from' AND '$period_to'";
$tmp_turn[]	= "reor_po_date BETWEEN DATE '$period_from' AND '$period_to'";

$strWhereOrder  	= implode(" AND ", $tmp_ord);
$strWhereReturn 	= implode(" AND ", $tmp_turn);

$sql = "
	SELECT icat_midx, it_code, it_model_no
	FROM
	  ".ZKP_SQL."_tb_customer AS a
	  JOIN ".ZKP_SQL."_tb_order AS b ON ord_ship_to = cus_code
	  JOIN ".ZKP_SQL."_tb_order_item AS c USING (ord_code)
	  JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
	WHERE $strWhereOrder
UNION
	SELECT icat_midx, it_code, it_model_no
	FROM
	  ".ZKP_SQL."_tb_customer AS a
	  JOIN ".ZKP_SQL."_tb_return_order AS b ON reor_ship_to = cus_code
	  JOIN ".ZKP_SQL."_tb_return_order_item AS c USING (reor_code)
	  JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
	WHERE $strWhereReturn
GROUP BY icat_midx, it_code, it_model_no
ORDER BY icat_midx, it_code ";


$sqlMonth = "
	SELECT it_code,c.it_code, to_char(ord_po_date, 'YYYYMM') AS period, sum(odit_qty) AS qty
	FROM
	  ".ZKP_SQL."_tb_customer AS a
	  JOIN ".ZKP_SQL."_tb_order AS b ON ord_ship_to = cus_code
	  JOIN ".ZKP_SQL."_tb_order_item AS c USING (ord_code)
	  JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
	WHERE $strWhereOrder
	GROUP BY it_code,c.it_code, period
UNION
	SELECT it_code,c.it_code, to_char(reor_po_date, 'YYYYMM') AS period, sum(roit_qty)*-1 AS qty
	FROM
	  ".ZKP_SQL."_tb_customer AS a
	  JOIN ".ZKP_SQL."_tb_return_order AS b ON reor_ship_to = cus_code
	  JOIN ".ZKP_SQL."_tb_return_order_item AS c USING (reor_code)
	  JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
	WHERE $strWhereReturn
	GROUP BY it_code,c.it_code, period ";
$tot = array();
$cit = array();
$res =& query($sqlMonth);
while($col =& fetchRowAssoc($res)) {
	$rd[] = array(
		$col['it_code'],$col['period'],$col['qty']
	);

	if(isset($tot[$col['it_code']][$col['period']])) {
		$tot[$col['it_code']][$col['period']] += $col['qty'];
	} else {
		$tot[$col['it_code']][$col['period']] = $col['qty'];
	}

}

echo "<pre>";
//echo $sqlMonth;
echo "</pre>";

// raw data
$rd 	= array();
$rdIdx	= 0;
$cache	= array("","","");
$group0 = array();
$amount = array('A'=>0,'D'=>0,'H'=>0,'P'=>0);

$res =& query($sql);
while($col =& fetchRowAssoc($res)) {
	$rd[] = array(
		$col['icat_midx'],		//0
		$col['it_code'],		//1
		$col['it_model_no'],	//2
	);

	if($cache[0] != $col['icat_midx']) {
		$cache[0] = $col['icat_midx'];
		$group0[$col['icat_midx']] = array();
	}

	if($cache[1] != $col['it_code']) {
		$cache[1] = $col['it_code'];
	}

	$group0[$col['icat_midx']][$col['it_code']] = 1;
}

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
		<th width="250px" rowspan="2">MODEL NO</th>
		<th colspan="$mon_length">MONTH</th>
		<th width="80px" rowspan="2">TOTAL</th>
		<th width="80px" rowspan="2">AVG</th>
	</tr>
	<tr height="25px">\n
END;

	$start_month = $_month_from;
	$start_year	 = $_year_from;
	for($i=0; $i<$mon_length; $i++) {
		if($start_month>12) { $start_month = $start_month-12; $start_year+=1;}
		echo "\t\t<th width=\"80px\">".$month[$start_month]."<br />$start_year</th>\n";
		$start_month++;
	}
print <<<END
	</tr>\n
END;

	$item_cat	= array();
	$print_tr_1 = 0;
	print "<tr>\n";
	//GROUP
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		$rowSpan += count($group2);

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".trim($rd[$rdIdx][1])."] ". $rd[$rdIdx][2]);		//Model No

		$item_total	= 0;
		for($j=0; $j<$mon_length; $j++) {
			$amount = isset($tot[$rd[$rdIdx][1]][$period[$j]]) ? $tot[$rd[$rdIdx][1]][$period[$j]] : 0;
			cell(number_format((double)$amount,0), ' align="right"');
			if(isset($item_cat[$j])) $item_cat[$j]	+= $amount;
			else					 $item_cat[$j]	= $amount;
			$item_total 	+= $amount;
			$start_month++;
		}
		cell(number_format((double)$item_total,0), ' align="right"');
		cell(number_format((double)$item_total/$mon_length,2), ' align="right"');
		print "</tr>\n";

		if(isset($item_cat[$mon_length])) {
			$item_cat[$mon_length]	 += $item_total;
			$item_cat[$mon_length+1] += $item_total/$mon_length;
		} else {
			$item_cat[$mon_length]	 = $item_total;
			$item_cat[$mon_length+1] = $item_total/$mon_length;
		}
		$rdIdx++;
	}

	print "<tr>\n";
	cell("<b>$cat</b>", ' align="right" style="color:brown; background-color:lightyellow"');
	for($i=0; $i<$mon_length; $i++) {
		cell(number_format((double)$item_cat[$i],0), ' align="right" style="color:brown; background-color:lightyellow"');
	}
	cell(number_format((double)$item_cat[$i],0), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$item_cat[$i+1],2), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br /><br />";

	for($i=0; $i<$mon_length; $i++) {
		if(isset($grand_total[$i])) $grand_total[$i] += $item_cat[$i];
		else						$grand_total[$i] = $item_cat[$i];
	}
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
		<th width="250px" rowspan="2">MODEL NO</th>
		<th colspan="$mon_length">MONTH</th>
		<th width="80px" rowspan="2">TOTAL</th>
		<th width="80px" rowspan="2">AVG</th>
	</tr>
	<tr height="25px">\n
END;

	$start_month = $_month_from;
	$start_year	 = $_year_from;
	for($i=0; $i<$mon_length; $i++) {
		if($start_month>12) { $start_month = $start_month-12; $start_year+=1;}
		echo "\t\t<th width=\"80px\">".$month[$start_month]."<br />$start_year</th>\n";
		$start_month++;
	}
print <<<END
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' align="right" style="color:brown; background-color:lightyellow"');
for($i=0; $i<$mon_length; $i++) {
	cell((isset($grand_total[$i])) ? number_format((double)$grand_total[$i],0) : 0, ' align="right" style="color:brown; background-color:lightyellow"');
}
cell((isset($grand_total[$i])) ? number_format((double)$grand_total[$i],0) : 0, ' align="right" style="color:brown; background-color:lightyellow"');
cell((isset($grand_total[$i+1])) ? number_format((double)$grand_total[$i+1],2) : 0, ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>