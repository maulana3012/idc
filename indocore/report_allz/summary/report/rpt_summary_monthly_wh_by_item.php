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
$dept['A']	= 'Apotik Team Sales Data by Customer';
$dept['D']	= 'Dealer Team Sales Data by Customer';
$dept['H']	= 'Hospital Team Sales Data by Customer';
$dept['M']	= 'Marketing Team Sales Data by Customer';
$dept['P']	= 'Pharmaceutical Team Sales Data by Customer';
$dept['T']	= 'Tender Team Sales Data by Customer';

$tmp_out	= array();
$tmp_inc	= array();
$tmp_rjt1	= array();	//Reject for alat
$tmp_rjt2	= array();	//Reject for E/D
$tmp_out_month	= array();
$tmp_inc_month	= array();
$tmp_rjt1_month	= array();
$tmp_rjt2_month	= array();

$db = $_order_by;

if($_cus_code != '') {
	$tmp_out[] 	= "cus_code = '$_cus_code'";
	$tmp_inc[]	= "cus_code = '$_cus_code'";
	$tmp_rjt1[]	= "rjit_idx is null";
	$tmp_rjt2[]	= "rjed_idx is null";
	$tmp_out_month[]	= "cus_code = '$_cus_code'";
	$tmp_inc_month[]	= "cus_code = '$_cus_code'";
	$tmp_rjt1_month[]	= "rjit_idx is null";
	$tmp_rjt2_month[]	= "rjed_idx is null";
}

if($_cug_code != 'all') {
	$tmp_out[]	= "cus_code IN (SELECT cus_code FROM ".$db."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp_inc[]	= "cus_code IN (SELECT cus_code FROM ".$db."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp_rjt1[]	= "rjit_idx is null";
	$tmp_rjt2[]	= "rjed_idx is null";
	$tmp_out_month[]	= "cus_code IN (SELECT cus_code FROM ".$db."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp_inc_month[]	= "cus_code IN (SELECT cus_code FROM ".$db."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp_rjt1_month[]	= "rjit_idx is null";
	$tmp_rjt2_month[]	= "rjed_idx is null";
}

if(substr($_filter_doc,0,3) == 'out') {
	if($_filter_doc == 'out') {
		$tmp_inc[]	= "inc_idx is null";
		$tmp_inc_month[]	= "inc_idx is null";
	} else if($_filter_doc == 'out-7') {
		$tmp_out[]	= "out_idx is null";
		$tmp_inc[]	= "inc_idx is null";
		$tmp_out_month[]	= "out_idx is null";
		$tmp_inc_month[]	= "inc_idx is null";
	} else {
		$tmp_out[]	= "out_doc_type = ". substr($_filter_doc,4,1);
		$tmp_inc[]	= "inc_idx is null";
		$tmp_rjt1[]	= "rjit_idx is null";
		$tmp_rjt2[]	= "rjed_idx is null";
		$tmp_out_month[]	= "out_doc_type = ". substr($_filter_doc,4,1);
		$tmp_inc_month[]	= "inc_idx is null";
		$tmp_rjt1_month[]	= "rjit_idx is null";
		$tmp_rjt2_month[]	= "rjed_idx is null";
	}
} else if(substr($_filter_doc,0,2) == 'in') {
	if($_filter_doc == 'in') {
		$tmp_out[]	= "out_idx is null";
		$tmp_rjt1[]	= "rjit_idx is null";
		$tmp_rjt2[]	= "rjed_idx is null";
		$tmp_out_month[]	= "out_idx is null";
		$tmp_rjt1_month[]	= "rjit_idx is null";
		$tmp_rjt2_month[]	= "rjed_idx is null";		
	} else {
		$tmp_inc[]	= "inc_doc_type = ". substr($_filter_doc,3,1);
		$tmp_out[]	= "out_idx is null";
		$tmp_rjt1[]	= "rjit_idx is null";
		$tmp_rjt2[]	= "rjed_idx is null";
		$tmp_out_month[]	= "inc_doc_type = ". substr($_filter_doc,3,1);
		$tmp_inc_month[]	= "out_idx is null";
		$tmp_rjt1_month[]	= "rjit_idx is null";
		$tmp_rjt2_month[]	= "rjed_idx is null";
	}
}

if ($_last_category != 0) {
	$catList = executeSP($db."_getSubCategory", $_last_category);
	$tmp_out[]	= "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_inc[]	= "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_rjt1[] = "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_rjt2[] = "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_out_month[]	= "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_inc_month[]	= "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_rjt1_month[]	= "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_rjt2_month[]	= "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if($_filter_vat != 'all') {
	$tmp_out[]	= "out_type = $_filter_vat";
	$tmp_inc[]	= "inc_type = $_filter_vat";
	$tmp_rjt1[] = "rjit_type = $_filter_vat";
	$tmp_rjt2[] = "rjed_type = $_filter_vat";
	$tmp_out_month[]	= "out_type = $_filter_vat";
	$tmp_inc_month[]	= "inc_type = $_filter_vat";
	$tmp_rjt1_month[]	= "rjit_type = $_filter_vat";
	$tmp_rjt2_month[]	= "rjed_type = $_filter_vat";
}

if($_filter_dept != 'all') {
	$tmp_out[]	= "out_dept = '$_filter_dept'";
	$tmp_inc[]	= "inc_dept = '$_filter_dept'";
	$tmp_rjt1[] = "rjit_idx is null";
	$tmp_rjt2[] = "rjed_idx is null";
	$tmp_out_month[]	= "out_dept = '$_filter_dept'";
	$tmp_inc_month[]	= "inc_dept = '$_filter_dept'";
	$tmp_rjt1_month[]	= "rjit_idx is null";
	$tmp_rjt2_month[]	= "rjed_idx is null";
}


$tmp_out[]	= "out_cfm_date BETWEEN DATE '$period_from 00:00:00' AND '$period_to 23:59:59'";
$tmp_inc[]	= "inc_confirmed_timestamp BETWEEN DATE '$period_from 00:00:00' AND '$period_to 23:59:59'";
$tmp_rjt1[] = "rjt_date BETWEEN DATE '$period_from 00:00:00' AND '$period_to 23:59:59' AND rjt_doc_idx is null";
$tmp_rjt2[] = "rjed_timestamp BETWEEN DATE '$period_from 00:00:00' AND '$period_to 23:59:59'";
$tmp_out_month[]	= "out_cfm_date BETWEEN DATE '$period_from 00:00:00' AND '$period_to 23:59:59'";
$tmp_inc_month[]	= "inc_confirmed_timestamp BETWEEN DATE '$period_from 23:59:59' AND '$period_to 23:59:59'";
$tmp_rjt1_month[]	= "rjt_date BETWEEN DATE '$period_from 00:00:00' AND '$period_to 23:59:59' AND rjt_doc_idx is null";
$tmp_rjt2_month[]	= "rjed_timestamp BETWEEN DATE '$period_from 00:00:00' AND '$period_to 23:59:59'";

$strWhereOut  	= implode(" AND ", $tmp_out);
$strWhereInc 	= implode(" AND ", $tmp_inc);
$strWhereRjt1	= implode(" AND ", $tmp_rjt1);
$strWhereRjt2	= implode(" AND ", $tmp_rjt2);
$strWhereMonthOut  	= implode(" AND ", $tmp_out_month);
$strWhereMonthInc 	= implode(" AND ", $tmp_inc_month);
$strWhereMonthRjt1	= implode(" AND ", $tmp_rjt1_month);
$strWhereMonthRjt2	= implode(" AND ", $tmp_rjt2_month);

$sql_out = "
SELECT icat_pidx, icat_midx, it_code, it_model_no
FROM
  ".$db."_tb_customer
  JOIN ".$db."_tb_outgoing USING(cus_code)
  JOIN ".$db."_tb_outgoing_stock USING (out_idx)
  JOIN ".$db."_tb_item USING(it_code)
  JOIN ".$db."_tb_item_cat USING (icat_midx)
WHERE $strWhereOut";

$sql_inc = "
SELECT icat_pidx, icat_midx, it_code, it_model_no
FROM
  ".$db."_tb_customer
  JOIN ".$db."_tb_incoming USING(cus_code)
  JOIN ".$db."_tb_incoming_stock USING (inc_idx)
  JOIN ".$db."_tb_item USING(it_code)
  JOIN ".$db."_tb_item_cat USING (icat_midx)
WHERE $strWhereInc";

$sql_rjt1 = "
SELECT icat_pidx, icat_midx, it_code, it_model_no
FROM
  ".$db."_tb_reject
  JOIN ".$db."_tb_reject_item USING (rjt_idx)
  JOIN ".$db."_tb_item USING(it_code)
  JOIN ".$db."_tb_item_cat USING (icat_midx)
WHERE $strWhereRjt1";

$sql_rjt2 = "
SELECT icat_pidx, icat_midx, it_code, it_model_no
FROM
  ".$db."_tb_reject_ed
  JOIN ".$db."_tb_item USING(it_code)
  JOIN ".$db."_tb_item_cat USING (icat_midx)
WHERE $strWhereRjt2";

$sql = "$sql_out UNION $sql_inc UNION $sql_rjt1 UNION $sql_rjt2 GROUP BY icat_pidx, icat_midx, it_code, it_model_no ORDER BY icat_pidx, icat_midx, it_code";

// raw data
$rd 	= array();
$rdIdx	= 0;
$cache	= array("","");
$group0 = array();
$amount = array('A'=>0,'D'=>0,'H'=>0,'P'=>0);
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {
	$rd[] = array(
		$col['icat_pidx'],		//0
		$col['icat_midx'],		//1
		$col['it_code'],		//2
		$col['it_model_no']		//3
	);

	if($cache[0] != $col['icat_midx'].$col['icat_midx']) {
		$cache[0] = $col['icat_midx'].$col['icat_midx'];
		$group0[$col['icat_midx'].$col['icat_midx']] = array();
	}

	if($cache[1] != $col['it_code']) {
		$cache[1] = $col['it_code'];
	}

	$group0[$col['icat_midx'].$col['icat_midx']][$col['it_code']] = 1;
}

$sqlQtyperMonth = "
SELECT a.it_code, to_char(out_cfm_date, 'YYYYMM') AS month, sum(otst_qty) AS qty,'1' AS column
  FROM
	".$db."_tb_customer
	JOIN ".$db."_tb_outgoing USING(cus_code)
	JOIN ".$db."_tb_outgoing_stock USING (out_idx)
	JOIN ".$db."_tb_item AS a USING(it_code)
  WHERE ". $strWhereMonthOut ."
  GROUP BY a.it_code, month UNION
SELECT a.it_code, to_char(inc_confirmed_timestamp, 'YYYYMM') AS month, sum(inst_qty)*-1 AS qty,'2' AS column
  FROM
	".$db."_tb_customer
	JOIN ".$db."_tb_incoming USING(cus_code)
	JOIN ".$db."_tb_incoming_stock USING (inc_idx)
	JOIN ".$db."_tb_item AS a USING(it_code)
  WHERE ". $strWhereMonthInc ." 
  GROUP BY a.it_code, month UNION
SELECT a.it_code, to_char(rjt_date, 'YYYYMM') AS month, sum(rjit_qty) AS qty,'3' AS column
  FROM
	".$db."_tb_reject
	JOIN ".$db."_tb_reject_item USING (rjt_idx)
	JOIN ".$db."_tb_item AS a USING(it_code)
  WHERE ". $strWhereMonthRjt1 ." 
  GROUP BY a.it_code, month UNION
SELECT a.it_code, to_char(rjed_timestamp, 'YYYYMM') AS month, sum(rjed_qty) AS qty,'4' AS column
  FROM
	".$db."_tb_reject_ed
	JOIN ".$db."_tb_item AS a USING(it_code)
  WHERE ". $strWhereMonthRjt2 ."
  GROUP BY a.it_code, month
ORDER BY it_code, month";
$qty = array();
$res_month =& query($sqlQtyperMonth);
while($col =& fetchRowAssoc($res_month)) { 
	if(!isset($qty[$col["it_code"]][$col["month"]])) 
		 $qty[$col["it_code"]][$col["month"]] = $col["qty"];
	else $qty[$col["it_code"]][$col["month"]] += $col["qty"];
}

echo "<pre>";
//echo  $strWhereMonthOut . "<br />" . $strWhereMonthInc . "<br />" . $strWhereMonthRjt1 . "<br />" . $strWhereMonthRjt2;
echo "</pre>";

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$grand_total = array();
for($i=0; $i<=$mon_length+1; $i++) $grand_total[$i] = 0;

//DEPARTEMEN
foreach ($group0 as $total1 => $group1) {

	//get category path from current icat_midx.
	$path = executeSP($db."_getCategoryPath", $rd[$rdIdx][1]);
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

	$item_cat = array();
	for($i=0; $i<=$mon_length+1; $i++) $item_cat[$i] = 0;
	$print_tr_1 = 0;
	print "<tr>\n";
	//GROUP
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		$rowSpan += count($group2);

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".trim($rd[$rdIdx][2])."] ". $rd[$rdIdx][3]);		//Model No
		$item_total	= 0;
		for($j=0; $j<$mon_length; $j++) {
			$i_qty = isset($qty[$rd[$rdIdx][2]][$mon]) ? $qty[$rd[$rdIdx][2]][$mon] : 0;
			$mon = date('Ym', mktime(0,0,0, date('m', strtotime($period_from))+$j, 1, date('Y', strtotime($period_from))));
			cell(number_format($i_qty , 2), ' align="right"');
			$item_total	+= $i_qty;
			$item_cat[$j]	+= $i_qty;
		}

		cell(number_format((double)$item_total,2), ' align="right"');
		cell(number_format(round((double)$item_total/$mon_length),0), ' align="right"');
		print "</tr>\n";

		$item_cat[$j] += $item_total;
		$rdIdx++;
	}

	print "<tr>\n";
	cell("<b>$cat</b>", ' align="right" style="color:brown; background-color:lightyellow"');
	for($i=0; $i<$mon_length; $i++) {
		cell(number_format((double)$item_cat[$i],2), ' align="right" style="color:brown; background-color:lightyellow"');
	}
	cell(number_format((double)$item_cat[$i],2), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$item_cat[$i+1],2), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br /><br />";

	for($i=0; $i<$mon_length; $i++) {
		$grand_total[$i] += $item_cat[$i];
	}
	$grand_total[$mon_length]	+= $item_cat[$mon_length];
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
	cell(number_format((double)$grand_total[$i],2), ' align="right" style="color:brown; background-color:lightyellow"');
}
cell(number_format((double)$grand_total[$i],2), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[$i+1],2), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>