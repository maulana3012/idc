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

$tmp_out1	= array();
$tmp_out	= array();
$tmp_inc	= array();
$tmp_rjt1	= array();	//Reject for alat
$tmp_rjt2	= array();	//Reject for E/D
$tmp_out1_month	= array();
$tmp_out_month	= array();
$tmp_inc_month	= array();
$tmp_rjt1_month	= array();
$tmp_rjt2_month	= array();

if(ZKP_URL == 'MEP') {
	$tmp_out1[]	= "out_idx IS NULL";
	$tmp_out[]	= "out_idx IS NULL";
	$tmp_inc[]	= "inc_idx IS NULL";
	$tmp_rjt1[]	= "rjit_idx is null";
	$tmp_rjt2[]	= "rjed_idx is null";
	$tmp_out1_month[]	= "out_idx IS NULL";
	$tmp_out_month[]	= "out_idx IS NULL";
	$tmp_inc_month[]	= "inc_idx IS NULL";
	$tmp_rjt1_month[]	= "rjit_idx is null";
	$tmp_rjt2_month[]	= "rjed_idx is null";
}

if($_inc_med == '') {
	$tmp_out[]	= "cus_code NOT IN ('0MSD')";
	$tmp_out1[]	= "cus_code NOT IN ('0MSD')";
	$tmp_inc[]	= "cus_code NOT IN ('0MSD')";
	$tmp_out_month[]	= "cus_code NOT IN ('0MSD')";
	$tmp_out1_month[]	= "cus_code NOT IN ('0MSD')";
	$tmp_inc_month[]	= "cus_code NOT IN ('0MSD')";
}

if($_inc_idc == '') {
	$tmp_out[]	= "cus_code NOT IN ('6IDC')";
	$tmp_out1[]	= "cus_code NOT IN ('6IDC')";
	$tmp_inc[]	= "cus_code NOT IN ('6IDC')";
	$tmp_out_month[]	= "cus_code NOT IN ('6IDC')";
	$tmp_out1_month[]	= "cus_code NOT IN ('6IDC')";
	$tmp_inc_month[]	= "cus_code NOT IN ('6IDC')";
}

if($_cus_code != '') {
	$tmp_out[] 	= "cus_code = '$_cus_code'";
	$tmp_out1[] 	= "cus_code = '$_cus_code'";
	$tmp_inc[]	= "cus_code = '$_cus_code'";
	$tmp_rjt1[]	= "rjit_idx is null";
	$tmp_rjt2[]	= "rjed_idx is null";
	$tmp_out_month[]	= "cus_code = '$_cus_code'";
	$tmp_out1_month[]	= "cus_code = '$_cus_code'";
	$tmp_inc_month[]	= "cus_code = '$_cus_code'";
	$tmp_rjt1_month[]	= "rjit_idx is null";
	$tmp_rjt2_month[]	= "rjed_idx is null";
}

if($_cug_code != 'all') {
	$tmp_out[]	= "cus_code IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp_out1[]	= "cus_code IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp_inc[]	= "cus_code IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp_rjt1[]	= "rjit_idx is null";
	$tmp_rjt2[]	= "rjed_idx is null";
	$tmp_out_month[]	= "cus_code IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp_out1_month[]	= "cus_code IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp_inc_month[]	= "cus_code IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp_rjt1_month[]	= "rjit_idx is null";
	$tmp_rjt2_month[]	= "rjed_idx is null";
}

if(substr($_filter_doc,0,3) == 'out') {
	if($_filter_doc == 'out') {
		$tmp_inc[]	= "inc_idx is null";
		$tmp_inc_month[]	= "inc_idx is null";
	} else if($_filter_doc == 'out-7') {
		$tmp_out[]	= "out_idx is null";
		$tmp_out1[]	= "out_idx is null";
		$tmp_inc[]	= "inc_idx is null";
		$tmp_out_month[]	= "out_idx is null";
		$tmp_out1_month[]	= "out_idx is null";
		$tmp_inc_month[]	= "inc_idx is null";
	} else {
		if(substr($_filter_doc,4,1) == 1) {
			$tmp_out[] = "out_doc_type = 'DO Billing'";
			$tmp_out_month[] = "out_doc_type = 'DO Billing'";
		} else if(substr($_filter_doc,4,1) == 2) {
			$tmp_out[] = "out_doc_type = 'DO Order'";
			$tmp_out_month[] = "out_doc_type = 'DO Order'";
		} else if(substr($_filter_doc,4,1) == 3) {
			$tmp_out[] = "out_doc_type = 'DT'";
			$tmp_out_month[] = "out_doc_type = 'DT'";
		} else if(substr($_filter_doc,4,1) == 4) {
			$tmp_out[] = "out_doc_type = 'DF'";
			$tmp_out_month[] = "out_doc_type = 'DF'";
		} else if(substr($_filter_doc,4,1) == 5) {
			$tmp_out[] = "out_doc_type = 'DR'";
			$tmp_out_month[] = "out_doc_type = 'DR'";
		} else if(substr($_filter_doc,4,1) == 6) {
			$tmp_out[] = "out_doc_type = 'DM'";
			$tmp_out_month[] = "out_doc_type = 'DM'";
		} else if(substr($_filter_doc,4,1) == 7) {
			$tmp_out[] = "out_doc_type = 'Reject'";
			$tmp_out_month[] = "out_doc_type = 'Reject'";
		}
		$tmp_out1[] = "out_doc_type = ".substr($_filter_doc,4,1);
		$tmp_inc[]	= "inc_idx is null";
		$tmp_rjt1[]	= "rjit_idx is null";
		$tmp_rjt2[]	= "rjed_idx is null";
		$tmp_out1_month[]	= "out_doc_type = ".substr($_filter_doc,4,1);
		$tmp_inc_month[]	= "inc_idx is null";
		$tmp_rjt1_month[]	= "rjit_idx is null";
		$tmp_rjt2_month[]	= "rjed_idx is null";
	}
} else if(substr($_filter_doc,0,2) == 'in') {
	if($_filter_doc == 'in') {
		$tmp_out[]	= "out_idx is null";
		$tmp_out1[]	= "out_idx is null";
		$tmp_rjt1[]	= "rjit_idx is null";
		$tmp_rjt2[]	= "rjed_idx is null";
		$tmp_out_month[]	= "out_idx is null";
		$tmp_out1_month[]	= "out_idx is null";
		$tmp_rjt1_month[]	= "rjit_idx is null";
		$tmp_rjt2_month[]	= "rjed_idx is null";		
	} else {
		if(substr($_filter_doc,3,1) == 1) {
			$tmp_inc[] = "inc_doc_type = 'Return Billing'";
			$tmp_inc_month[] = "inc_doc_type = 'Return Billing'";
		} else if(substr($_filter_doc,3,1) == 2) {
			$tmp_inc[] = "inc_doc_type = 'Return Order'";
			$tmp_inc_month[] = "inc_doc_type = 'Return Order'";
		} else if(substr($_filter_doc,3,1) == 3) {
			$tmp_inc[] = "inc_doc_type = 'Return DT'";
			$tmp_inc_month[] = "inc_doc_type = 'Return DT'";
		} else if(substr($_filter_doc,3,1) == 4) {
			$tmp_inc[] = "inc_doc_type = 'Return Tradein'";
			$tmp_inc_month[] = "inc_doc_type = 'Return Tradein'";
		} 
		$tmp_out[]	= "out_idx is null";
		$tmp_out1[]	= "out_idx is null";
		$tmp_rjt1[]	= "rjit_idx is null";
		$tmp_rjt2[]	= "rjed_idx is null";		
		$tmp_out_month[]	= "out_idx is null";
		$tmp_out1_month[]	= "out_idx is null";
		$tmp_rjt1_month[]	= "rjit_idx is null";
		$tmp_rjt2_month[]	= "rjed_idx is null";
	}
}

if($_filter_order != 'all') {
	if($_filter_order == '1') {
		$tmp_out[] = "substr(out_code,1,1)='D' and substr(out_code,4,1)!='S'";
		$tmp_out1[] = "substr(out_code,1,1)='D' and substr(out_code,4,1)!='S'";
		$tmp_inc[] = "substr(inc_doc_ref,1,1)='R' and substr(inc_doc_ref,4,1)!='M'";
		$tmp_out_month[] = "substr(out_code,1,1)='D' and substr(out_code,4,1)!='S'";
		$tmp_out1_month[] = "substr(out_code,1,1)='D' and substr(out_code,4,1)!='S'";
		$tmp_inc_month[] = "substr(inc_doc_ref,1,1)='R' and substr(inc_doc_ref,4,1)!='S'";
	} else if($_filter_order == '2') {
		$tmp_out[] = "substr(out_code,4,1)='S'";
		$tmp_out1[] = "substr(out_code,4,1)='S'";
		$tmp_inc[] = "substr(inc_doc_ref,1,1)='R' and substr(inc_doc_ref,4,1)='M'";
		$tmp_out_month[] = "substr(out_code,4,1)='S'";
		$tmp_out1_month[] = "substr(out_code,4,1)='S'";
		$tmp_inc_month[] = "substr(inc_doc_ref,1,1)='R' and substr(inc_doc_ref,4,1)='S'";	
	}
	$tmp_rjt1[]	= "rjit_idx is null";
	$tmp_rjt2[]	= "rjed_idx is null";
	$tmp_rjt1_month[]	= "rjit_idx is null";
	$tmp_rjt2_month[]	= "rjed_idx is null";
}

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp_out[]	= "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_out1[]	= "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_inc[]	= "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_rjt1[] = "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_rjt2[] = "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_out_month[]	= "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_out1_month[]	= "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_inc_month[]	= "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_rjt1_month[]	= "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_rjt2_month[]	= "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if($_filter_vat != 'all') {
	$tmp_out[]	= "out_type = $_filter_vat";
	$tmp_out1[]	= "out_type = $_filter_vat";
	$tmp_inc[]	= "inc_type = $_filter_vat";
	$tmp_rjt1[] = "rjit_type = $_filter_vat";
	$tmp_rjt2[] = "rjed_type = $_filter_vat";
	$tmp_out_month[]	= "out_type = $_filter_vat";
	$tmp_out1_month[]	= "out_type = $_filter_vat";
	$tmp_inc_month[]	= "inc_type = $_filter_vat";
	$tmp_rjt1_month[]	= "rjit_type = $_filter_vat";
	$tmp_rjt2_month[]	= "rjed_type = $_filter_vat";
}

if($_filter_dept != 'all') {
	$tmp_out[]	= "out_dept = '$_filter_dept'";
	$tmp_out1[]	= "out_dept = '$_filter_dept'";
	$tmp_inc[]	= "inc_dept = '$_filter_dept'";
	$tmp_rjt1[] = "rjit_idx is null";
	$tmp_rjt2[] = "rjed_idx is null";
	$tmp_out_month[]	= "out_dept = '$_filter_dept'";
	$tmp_out1_month[]	= "out_dept = '$_filter_dept'";
	$tmp_inc_month[]	= "inc_dept = '$_filter_dept'";
	$tmp_rjt1_month[]	= "rjit_idx is null";
	$tmp_rjt2_month[]	= "rjed_idx is null";
}

$tmp_out[]	= "out_cfm_date BETWEEN DATE '$period_from 00:00:00' AND '$period_to 23:59:59'";
$tmp_out1[]	= "out_cfm_date BETWEEN DATE '$period_from 00:00:00' AND '$period_to 23:59:59'";
$tmp_inc[]	= "inc_confirmed_timestamp BETWEEN DATE '$period_from 00:00:00' AND '$period_to 23:59:59'";
$tmp_rjt1[] = "rjt_date BETWEEN DATE '$period_from 00:00:00' AND '$period_to 23:59:59' AND rjt_doc_idx is null";
$tmp_rjt2[] = "rjed_timestamp BETWEEN DATE '$period_from 00:00:00' AND '$period_to 23:59:59'";
$tmp_out_month[]	= "out_cfm_date BETWEEN DATE '$period_from 00:00:00' AND '$period_to 23:59:59'";
$tmp_out1_month[]	= "out_cfm_date BETWEEN DATE '$period_from 00:00:00' AND '$period_to 23:59:59'";
$tmp_inc_month[]	= "inc_confirmed_timestamp BETWEEN DATE '$period_from 23:59:59' AND '$period_to 23:59:59'";
$tmp_rjt1_month[]	= "rjt_date BETWEEN DATE '$period_from 00:00:00' AND '$period_to 23:59:59' AND rjt_doc_idx is null";
$tmp_rjt2_month[]	= "rjed_timestamp BETWEEN DATE '$period_from 00:00:00' AND '$period_to 23:59:59'";

$strWhereOut  	= implode(" AND ", $tmp_out);
$strWhereOut1  	= implode(" AND ", $tmp_out1);
$strWhereInc 	= implode(" AND ", $tmp_inc);
$strWhereRjt1	= implode(" AND ", $tmp_rjt1);
$strWhereRjt2	= implode(" AND ", $tmp_rjt2);
$strWhereMonthOut  	= implode(" AND ", $tmp_out_month);
$strWhereMonthOut1  = implode(" AND ", $tmp_out1_month);
$strWhereMonthInc 	= implode(" AND ", $tmp_inc_month);
$strWhereMonthRjt1	= implode(" AND ", $tmp_rjt1_month);
$strWhereMonthRjt2	= implode(" AND ", $tmp_rjt2_month);
/*
echo "<pre>";
var_dump($strWhereMonthOut, $strWhereMonthOut1);
echo "</pre>";
exit;
*/
$sql_out = "
SELECT icat_pidx, icat_midx, it_code, it_model_no
FROM
  ".ZKP_SQL."_tb_customer
  JOIN ".ZKP_SQL."_tb_outgoing USING(cus_code)
  JOIN ".ZKP_SQL."_tb_outgoing_stock USING (out_idx)
  JOIN ".ZKP_SQL."_tb_item USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat USING (icat_midx)
WHERE $strWhereOut1
	UNION
SELECT icat_pidx, icat_midx, it_code, it_model_no
FROM
  ".ZKP_SQL."_tb_customer
  JOIN ".ZKP_SQL."_tb_outgoing_v2 USING(cus_code)
  JOIN ".ZKP_SQL."_tb_outgoing_stock_v2 USING (out_idx)
  JOIN ".ZKP_SQL."_tb_item USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat USING (icat_midx)
WHERE $strWhereOut
";

$sql_inc = "
SELECT icat_pidx, icat_midx, it_code, it_model_no
FROM
  ".ZKP_SQL."_tb_customer
  JOIN ".ZKP_SQL."_tb_incoming USING(cus_code)
  JOIN ".ZKP_SQL."_tb_incoming_stock USING (inc_idx)
  JOIN ".ZKP_SQL."_tb_item USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat USING (icat_midx)
WHERE $strWhereInc
	UNION
SELECT icat_pidx, icat_midx, it_code, it_model_no
FROM
  ".ZKP_SQL."_tb_customer
  JOIN ".ZKP_SQL."_tb_incoming USING(cus_code)
  JOIN ".ZKP_SQL."_tb_incoming_stock_v2 USING (inc_idx)
  JOIN ".ZKP_SQL."_tb_item USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat USING (icat_midx)
WHERE $strWhereInc
";

$sql_rjt1 = "
SELECT icat_pidx, icat_midx, it_code, it_model_no
FROM
  ".ZKP_SQL."_tb_reject
  JOIN ".ZKP_SQL."_tb_reject_item USING (rjt_idx)
  JOIN ".ZKP_SQL."_tb_item USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat USING (icat_midx)
WHERE $strWhereRjt1";

$sql_rjt2 = "
SELECT icat_pidx, icat_midx, it_code, it_model_no
FROM
  ".ZKP_SQL."_tb_reject_ed
  JOIN ".ZKP_SQL."_tb_item USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat USING (icat_midx)
WHERE $strWhereRjt2";

$sql = "$sql_out UNION $sql_inc UNION $sql_rjt1 UNION $sql_rjt2 GROUP BY icat_pidx, icat_midx, it_code, it_model_no 
		ORDER BY icat_pidx, icat_midx, it_code";

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
		$col['it_model_no'],		//3
	);

	if($cache[0] != $col['icat_pidx'].$col['icat_midx']) {
		$cache[0] = $col['icat_pidx'].$col['icat_midx'];
		$group0[$col['icat_pidx'].$col['icat_midx']] = array();
	}

	if($cache[1] != $col['it_code']) {
		$cache[1] = $col['it_code'];
	}

	$group0[$col['icat_pidx'].$col['icat_midx']][$col['it_code']] = 1;
}

$sqlQtyperMonth = "
SELECT a.it_code, to_char(out_cfm_date, 'YYYYMM') AS month, sum(otst_qty) AS qty,'1' AS column
  FROM
	".ZKP_SQL."_tb_customer
	JOIN ".ZKP_SQL."_tb_outgoing USING(cus_code)
	JOIN ".ZKP_SQL."_tb_outgoing_stock USING (out_idx)
	JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
  WHERE ". $strWhereMonthOut1 ."
  GROUP BY a.it_code, month UNION
SELECT a.it_code, to_char(out_cfm_date, 'YYYYMM') AS month, sum(otst_qty) AS qty,'1' AS column
  FROM
	".ZKP_SQL."_tb_customer
	JOIN ".ZKP_SQL."_tb_outgoing_v2 USING(cus_code)
	JOIN ".ZKP_SQL."_tb_outgoing_stock_v2 USING (out_idx)
	JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
  WHERE ". $strWhereMonthOut ."
  GROUP BY a.it_code, month UNION

SELECT a.it_code, to_char(inc_confirmed_timestamp, 'YYYYMM') AS month, sum(inst_qty)*-1 AS qty,'2' AS column
  FROM
	".ZKP_SQL."_tb_customer
	JOIN ".ZKP_SQL."_tb_incoming USING(cus_code)
	JOIN ".ZKP_SQL."_tb_incoming_stock USING (inc_idx)
	JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
  WHERE ". $strWhereMonthInc ." 
  GROUP BY a.it_code, month UNION
SELECT a.it_code, to_char(inc_confirmed_timestamp, 'YYYYMM') AS month, sum(inst_qty)*-1 AS qty,'2' AS column
  FROM
	".ZKP_SQL."_tb_customer
	JOIN ".ZKP_SQL."_tb_incoming USING(cus_code)
	JOIN ".ZKP_SQL."_tb_incoming_stock_v2 USING (inc_idx)
	JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
  WHERE ". $strWhereMonthInc ." 
  GROUP BY a.it_code, month UNION

SELECT a.it_code, to_char(rjt_date, 'YYYYMM') AS month, sum(rjit_qty) AS qty,'3' AS column
  FROM
	".ZKP_SQL."_tb_reject
	JOIN ".ZKP_SQL."_tb_reject_item USING (rjt_idx)
	JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
  WHERE ". $strWhereMonthRjt1 ." 
  GROUP BY a.it_code, month UNION
SELECT a.it_code, to_char(rjed_timestamp, 'YYYYMM') AS month, sum(rjed_qty) AS qty,'4' AS column
  FROM
	".ZKP_SQL."_tb_reject_ed
	JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
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
/*
echo "<pre>";
var_dump($sqlQtyperMonth);
echo "</pre>";
*/
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
	$path = executeSP(ZKP_SQL."_getCategoryPath", $rd[$rdIdx][1]);
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
		cell("[".trim($rd[$rdIdx][2])."] ". $rd[$rdIdx][3]); //Model No

		$item_total	= 0;
		for($j=0; $j<$mon_length; $j++) {
			$mon = date('Ym', mktime(0,0,0, date('m', strtotime($period_from))+$j, 1, date('Y', strtotime($period_from))));
			cell(number_format($qty[$rd[$rdIdx][2]][$mon], 2), ' align="right"');
			$item_total	+= $qty[$rd[$rdIdx][2]][$mon];
			$item_cat[$j] += $qty[$rd[$rdIdx][2]][$mon];
		}

		cell(number_format((double)$item_total,2), ' align="right"');
		cell(number_format((double)$item_total/$mon_length,2), ' align="right"');
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
	cell("", ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br /><br />";

	for($i=0; $i<$mon_length; $i++) {
		$grand_total[$i] += $item_cat[$i];
	}
	$grand_total[$mon_length]	+= $item_cat[$mon_length];
//	$grand_total[$mon_length+1]	+= $item_cat[$mon_length+1];
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