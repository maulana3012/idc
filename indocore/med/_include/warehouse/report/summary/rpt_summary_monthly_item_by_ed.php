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
$tmp_inc = array();
$tmp_out = array();
$tmp_out_month	= array();
$tmp_inc_month	= array();

if(ZKP_SQL == 'IDC') {
	if($_order_by == '1') {
		$tmp_out[] = "substr(out_code,1,1)='D' and substr(out_code,4,1)!='M'";
		$tmp_inc[] = "substr(inc_doc_ref,4,1)!='M'";
		$tmp_out_month[] = "substr(out_code,1,1)='D' and substr(out_code,4,1)!='M'";
		$tmp_inc_month[] = "substr(inc_doc_ref,4,1)!='M'";
	} else if($_order_by == '2') {
		$tmp_out[] = "substr(out_code,1,1)='D' and substr(out_code,4,1)='M'";
		$tmp_inc[] = "substr(inc_doc_ref,4,1)='M'";
		$tmp_out_month[] = "substr(out_code,1,1)='D' and substr(out_code,4,1)='M'";
		$tmp_inc_month[] = "substr(inc_doc_ref,4,1)='M'";
	}
} else if(ZKP_SQL == 'MED') {
	if($_order_by == '1') {
		$tmp_out[] = "substr(out_code,1,1)='D' and substr(out_code,4,1)!='S'";
		$tmp_inc[] = "substr(inc_doc_ref,4,1)!='S'";
		$tmp_out_month[] = "substr(out_code,1,1)='D' and substr(out_code,4,1)!='S'";
		$tmp_inc_month[] = "substr(inc_doc_ref,4,1)!='S'";
	} else if($_order_by == '2') {
		$tmp_out[] = "substr(out_code,1,1)='D' and substr(out_code,4,1)='S'";
		$tmp_inc[] = "substr(inc_doc_ref,4,1)='S'";
		$tmp_out_month[] = "substr(out_code,1,1)='D' and substr(out_code,4,1)='S'";
		$tmp_inc_month[] = "substr(inc_doc_ref,4,1)='S'";
	}
}

if(substr($_source,0,3) == 'out') {
	if($_source == 'out') {
		$tmp_inc[]	= "inc_idx is null";
		$tmp_inc_month[]	= "inc_idx is null";
	} else {
		$tmp_out[]	= "out_doc_type = ". substr($_source,4,1);
		$tmp_inc[]	= "inc_idx is null";
		$tmp_out_month[]	= "out_doc_type = ". substr($_source,4,1);
		$tmp_inc_month[]	= "inc_idx is null";
	}
} else if(substr($_source,0,2) == 'in') {
	if($_source == 'in') {
		$tmp_out[]	= "out_idx is null";
		$tmp_out_month[]	= "out_idx is null";
	} else {
		$tmp_inc[]	= "inc_doc_type = ". substr($_source,3,1);
		$tmp_out[]	= "out_idx is null";
		$tmp_inc_month[]	= "inc_doc_type = ". substr($_source,3,1);
		$tmp_out_month[]	= "out_idx is null";
	}
}

if($_dept != 'all') {
	if($_dept == 'DEMO') {
		$tmp_out[] = "b.out_doc_type = 6";
		$tmp_inc[] = "b.inc_doc_type = 6";
		$tmp_out_month[] = "b.out_doc_type = 6";
		$tmp_inc_month[] = "b.inc_doc_type = 6";
	} else {
		$tmp_out[] = "b.out_dept = '$_dept' AND b.out_doc_type != 6";
		$tmp_inc[] = "b.inc_dept = '$_dept' AND b.inc_doc_type != 6";
		$tmp_out_month[] = "b.out_dept = '$_dept' AND b.out_doc_type != 6";
		$tmp_inc_month[] = "b.inc_dept = '$_dept' AND b.inc_doc_type != 6";
	}
}

if ($_filter_date == 'document_date') {
	$tmp_out[]	= "out_issued_date BETWEEN DATE '$period_from 00:00:00' AND '$period_to 23:59:59'";
	$tmp_inc[]	= "inc_date BETWEEN DATE '$period_from 00:00:00' AND '$period_to 23:59:59'";
	$tmp_out_month[]	= "out_issued_date BETWEEN DATE '$period_from 00:00:00' AND '$period_to 23:59:59'";
	$tmp_inc_month[]	= "inc_date BETWEEN DATE '$period_from 23:59:59' AND '$period_to 23:59:59'";
} else if ($_filter_date == 'confirm_date') {
	$tmp_out[]	= "out_cfm_date BETWEEN DATE '$period_from 00:00:00' AND '$period_to 23:59:59'";
	$tmp_inc[]	= "inc_confirmed_timestamp BETWEEN DATE '$period_from 00:00:00' AND '$period_to 23:59:59'";
	$tmp_out_month[]	= "out_cfm_date BETWEEN DATE '$period_from 00:00:00' AND '$period_to 23:59:59'";
	$tmp_inc_month[]	= "inc_confirmed_timestamp BETWEEN DATE '$period_from 23:59:59' AND '$period_to 23:59:59'";
}

if($_vat == 1) {
	$tmp_out[]	= "b.out_type = 1"; 
	$tmp_inc[]	= "b.inc_type = 1"; 
	$tmp_out_month[]	= "b.out_type = 1"; 
	$tmp_inc_month[]	= "b.inc_type = 1"; 
} else if ($_vat == 2) {
	$tmp_out[]	= "b.out_type = 2";
	$tmp_inc[]	= "b.inc_type = 2";
	$tmp_out_month[]	= "b.out_type = 2";
	$tmp_inc_month[]	= "b.inc_type = 2";
}

$strWhereOut   = implode(" AND ", $tmp_out);
$strWhereInc   = implode(" AND ", $tmp_inc);
$strWhereOutMonth   = implode(" AND ", $tmp_out_month);
$strWhereIncMonth   = implode(" AND ", $tmp_inc_month);

$sql_out = "
SELECT 
  icat_midx, 
  icat_pidx, 
  TRIM(d.it_code) AS it_code,
  it_model_no AS model, 
  to_char(c.oted_date,'YYYY-MM') AS ed
FROM
 ".ZKP_SQL."_tb_outgoing AS b
 JOIN ".ZKP_SQL."_tb_outgoing_ed AS c USING(out_idx)
 JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS e USING(icat_midx)
WHERE " . $strWhereOut ."
";

$sql_inc = "
SELECT 
  icat_midx, 
  icat_pidx, 
  TRIM(d.it_code) AS it_code,
  it_model_no AS model, 
  to_char(c.ised_expired_date,'YYYY-MM') AS ed
FROM
 ".ZKP_SQL."_tb_incoming AS b
 JOIN ".ZKP_SQL."_tb_incoming_stock_ed AS c USING(inc_idx)
 JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS e USING(icat_midx)
WHERE " . $strWhereInc ."
";

$sql = "$sql_out UNION $sql_inc GROUP BY icat_pidx, icat_midx, d.it_code, model, ed ORDER BY icat_pidx, icat_midx, it_code, model, ed";

// raw data
$rd = array();
$rdIdx = 0;
$i = 0;
$cache = array("","","");
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'],		//0
		$col['icat_pidx'],		//1
		$col['it_code'],		//2
		$col['model'],			//3
		$col['ed']				//4
	);

	//1st grouping
	if($cache[0] !=$col['icat_pidx'].'-'.$col['icat_midx']) {
		$cache[0] = $col['icat_pidx'].'-'.$col['icat_midx'];
		$group0[$col['icat_pidx'].'-'.$col['icat_midx']] = array();
	}

	if($cache[1] != $col['it_code']) {
		$cache[1] = $col['it_code'];
		$group0[$col['icat_pidx'].'-'.$col['icat_midx']][$col['it_code']] = array();
	}

	if($cache[2] != $col['ed']) {
		$cache[2] = $col['ed'];
	}

	$group0[$col['icat_pidx'].'-'.$col['icat_midx']][$col['it_code']][$col['ed']] = 1;
}

$sqlQtyperMonth = "
SELECT 
  TRIM(d.it_code) AS it_code,  
  to_char(c.oted_date,'YYYY-MM') AS ed,
  CASE
  	WHEN '$_filter_date' = 'document_date' THEN to_char(b.out_issued_date ,'YYYYMM')
	WHEN '$_filter_date' = 'confirm_date' THEN to_char(b.out_cfm_date ,'YYYYMM')
  END AS v_period,
  '1' AS v_column,
  sum(oted_qty) AS qty
FROM
  ".ZKP_SQL."_tb_outgoing AS b
  JOIN ".ZKP_SQL."_tb_outgoing_ed AS c USING(out_idx)
  JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
  WHERE ". $strWhereOutMonth ."
  GROUP BY d.it_code, ed, v_period
UNION
SELECT
  TRIM(d.it_code) AS it_code,  
  to_char(c.ised_expired_date,'YYYY-MM') AS ed,
  CASE
  	WHEN '$_filter_date' = 'document_date' THEN to_char(b.inc_date ,'YYYYMM')
	WHEN '$_filter_date' = 'confirm_date' THEN to_char(b.inc_confirmed_timestamp ,'YYYYMM')
  END AS v_period,
  '2' AS v_column,
  sum(ised_qty)*-1 AS qty
FROM
  ".ZKP_SQL."_tb_incoming AS b
  JOIN ".ZKP_SQL."_tb_incoming_stock_ed AS c USING(inc_idx)
  JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
  WHERE ". $strWhereIncMonth ."
GROUP BY d.it_code, ed, v_period
ORDER BY it_code, ed, v_period, v_column";
$qty = array();
$res_month =& query($sqlQtyperMonth);
while($col =& fetchRowAssoc($res_month)) { 
	if(!isset($qty[$col["it_code"]][$col["ed"]][$col["v_period"]])) 
		 $qty[$col["it_code"]][$col["ed"]][$col["v_period"]] = $col["qty"];
	else $qty[$col["it_code"]][$col["ed"]][$col["v_period"]] += $col["qty"];
}
echo "<pre>";
//echo $sqlQtyperMonth."<BR />";
//var_dump($qty['2101']['2012-03']['201108']);
echo "</pre>";

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$grand_amount= array();
for($i=0; $i<=$mon_length+1; $i++) $grand_total[$i] = 0;

//CATEGORY
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
		<th width="150px" rowspan="2">EXPIRED DATE</th>
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

	$cat_amount = array();
	$print_tr_1 = 0;
	print "<tr>\n";
	//ITEM
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		$rowSpan += count($group2);
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell('['.$rd[$rdIdx][2].'] '. $rd[$rdIdx][3], ' valign="top" align="center" rowspan="'.$rowSpan.'"');	// Model No

		$item = '['.$rd[$rdIdx][2].'] '. $rd[$rdIdx][3];
		$it_amount = array();
		$print_tr_2 = 0;
		//EXPIRED DATE
		foreach($group2 as $total3 => $group3) {

			if($print_tr_2++ > 0) print "<tr>\n";
			cell($rd[$rdIdx][4], ' valign="top" align="center"');	// Expired Date

			// PERIOD
			$total = 0;
			for($j=0; $j<$mon_length; $j++) {
				$mon = date('Ym', mktime(0,0,0, date('m', strtotime($period_from))+$j, 1, date('Y', strtotime($period_from))));
				$i_qty = $qty[$rd[$rdIdx][2]][$rd[$rdIdx][4]][$mon];
				cell(number_format($i_qty, 2), ' align="right"');	// $rd[$rdIdx][2].'<br />'.$rd[$rdIdx][4].'<br />'.$rd[$rdIdx][5].'<br />'.$mon.'<br />'.
				$total += $i_qty;
				$it_amount[$j] += $i_qty;
			}

			cell(number_format((double)$total,2), ' align="right"');
			cell(number_format((double)$total/$mon_length,2), ' align="right"');
			print "</tr>\n";

			$it_amount[$j] += $total;
			$rdIdx++;
		}		
		print "<tr>\n";
		cell("<b>$item</b>", ' align="right" style="color:brown; background-color:lightyellow"');
		for($i=0; $i<$mon_length; $i++) cell(number_format((double)$it_amount[$i],2), ' align="right" style="color:brown; background-color:lightyellow"');
		cell(number_format((double)$it_amount[$i],2), ' align="right" style="color:brown; background-color:lightyellow"');
		cell("", ' align="right" style="color:brown; background-color:lightyellow"');
		print "</tr>\n";

		for($i=0; $i<$mon_length; $i++) $cat_amount[$i] += $it_amount[$i];
		$cat_amount[$mon_length]	+= $it_amount[$mon_length];
	}
	print "<tr>\n";
	cell("<b>CATEGORY: $cat</b>", ' colspan="2" align="right" style="color:brown; background-color:lightyellow"');
	for($i=0; $i<$mon_length; $i++) cell(number_format((double)$cat_amount[$i],2), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cat_amount[$i],2), ' align="right" style="color:brown; background-color:lightyellow"');
	cell("", ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />";

	for($i=0; $i<$mon_length; $i++) $grand_amount[$i] += $cat_amount[$i];
	$grand_amount[$mon_length]	+= $cat_amount[$mon_length];
}

print <<<END
<table class="table_f" width="$table_len px">
	<tr height="15px">
		<th width="200px" rowspan="2">MODEL NO</th>
		<th width="80px" rowspan="2">EXPIRED DATE</th>
		<th width="350px" rowspan="2">CUSTOMER</th>
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
cell("<b>GRAND TOTAL</b>", ' colspan="3" align="right" style="color:brown; background-color:lightyellow"');
for($i=0; $i<$mon_length; $i++) cell(number_format((double)$grand_amount[$i],2), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_amount[$i],2), ' align="right" style="color:brown; background-color:lightyellow"');
cell("", ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>