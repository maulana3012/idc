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

if(ZKP_SQL == 'IDC') {
	if($_order_by == '1') {
		$tmp_out[] = "substr(out_code,1,1)='D' and substr(out_code,4,1)!='M'";
		$tmp_inc[] = "substr(inc_doc_ref,4,1)!='M'";
	} else if($_order_by == '2') {
		$tmp_out[] = "substr(out_code,1,1)='D' and substr(out_code,4,1)='M'";
		$tmp_inc[] = "substr(inc_doc_ref,4,1)='M'";
	}
} else if(ZKP_SQL == 'MED') {
	if($_order_by == '1') {
		$tmp_out[] = "substr(out_code,1,1)='D' and substr(out_code,4,1)!='S'";
		$tmp_inc[] = "substr(inc_doc_ref,4,1)!='S'";
	} else if($_order_by == '2') {
		$tmp_out[] = "substr(out_code,1,1)='D' and substr(out_code,4,1)='S'";
		$tmp_inc[] = "substr(inc_doc_ref,4,1)='S'";
	}
}

if(substr($_source,0,3) == 'out') {
	if($_source == 'out') {
		$tmp_inc[]	= "inc_idx is null";
	} else {
		$tmp_out[]	= "out_doc_type = ". substr($_source,4,1);
		$tmp_inc[]	= "inc_idx is null";
	}
} else if(substr($_source,0,2) == 'in') {
	if($_source == 'in') {
		$tmp_out[]	= "out_idx is null";
	} else {
		$tmp_inc[]	= "inc_doc_type = ". substr($_source,3,1);
		$tmp_out[]	= "out_idx is null";
	}
}

if ($_cug_code != 'all') {
	//If group specified, 
	$tmp_out[]	= "b.cus_code IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp_inc[]	= "b.cus_code IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$sql_out	= "SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
	$sql_inc	= "SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
} else {
	$sql_out	= "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = b.cus_code),
		'Others') AS cug_name,"; // if null, return Others Group
	$sql_inc	= "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = b.cus_code),
		'Others') AS cug_name,"; // if null, return Others Group
}

if($_dept != 'all') {
	if($_dept == 'DEMO') {
		$tmp_out[] = "b.out_doc_type = 6";
		$tmp_inc[] = "b.inc_doc_type = 6";
	} else {
		$tmp_out[] = "b.out_dept = '$_dept' AND b.out_doc_type != 6";
		$tmp_inc[] = "b.inc_dept = '$_dept' AND b.inc_doc_type != 6";
	}
}

if ($_filter_date == 'document_date') {
	if ($some_date != "") {
		$tmp_out[]   = "b.out_issued_date = DATE '$some_date'";
		$tmp_inc[]   = "b.inc_date = DATE '$some_date'";
	} else {
		$tmp_out[]   = "b.out_issued_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_inc[]   = "b.inc_date BETWEEN DATE '$period_from' AND '$period_to'";
	}
} else if ($_filter_date == 'confirm_date') {
	if ($some_date != "") {
		$tmp_out[]   = "b.out_cfm_date = DATE '$some_date'";
		$tmp_inc[]   = "b.inc_confirmed_timestamp = DATE '$some_date'";
	} else {
		$tmp_out[]   = "b.out_cfm_date BETWEEN DATE '$period_from' AND '$period_to'";
		$tmp_inc[]   = "b.inc_confirmed_timestamp BETWEEN DATE '$period_from' AND '$period_to'";
	}
}

if($_vat == 1) {
	$tmp_out[]	= "b.out_type = 1"; 
	$tmp_inc[]	= "b.inc_type = 1"; 
} else if ($_vat == 2) {
	$tmp_out[]	= "b.out_type = 2";
	$tmp_inc[]	= "b.inc_type = 2";
}

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp_out[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp_inc[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

$strWhereOut   = implode(" AND ", $tmp_out);
$strWhereInc   = implode(" AND ", $tmp_inc);

$sql_out .= "
 a.cus_code AS cus_code,
 a.cus_full_name AS cus_full_name,
 b.out_idx AS doc_idx,
 b.out_doc_ref AS document_no,
 to_char(b.out_issued_date,'dd-Mon-YY') AS document_date,
 b.out_code AS do_no,
 to_char(b.out_cfm_date,'dd-Mon-YY') AS confirm_date,
 CASE
   WHEN b.out_type = 1 THEN 'VAT'
   WHEN b.out_type = 2 THEN 'NON'
   ELSE 'NON SPECIFIED'
 END AS document_type,
 d.it_code AS it_code,
 d.it_model_no AS it_model_no,
 'out-'||oted_idx AS docit_idx,
 to_char(oted_date,'YYYY.MM') AS it_expired_date,
 oted_date AS ed,
 oted_qty AS it_qty,
 '../delivery/detail_do.php?_code='||out_idx AS go_page
FROM
 ".ZKP_SQL."_tb_customer AS a
 JOIN ".ZKP_SQL."_tb_outgoing AS b USING(cus_code)
 JOIN ".ZKP_SQL."_tb_outgoing_ed AS c USING(out_idx)
 JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE " . $strWhereOut ."
";

$sql_inc .= "
 a.cus_code AS cus_code,
 a.cus_full_name AS cus_full_name,
 b.inc_idx AS doc_idx,
 b.inc_doc_ref AS document_no,
 to_char(b.inc_date,'dd-Mon-YY') AS document_date,
 b.inc_doc_ref AS do_no,
 to_char(b.inc_confirmed_timestamp,'dd-Mon-YY') AS confirm_date,
 CASE
   WHEN b.inc_type = 1 THEN 'VAT'
   WHEN b.inc_type = 2 THEN 'NON'
   ELSE 'NON SPECIFIED'
 END AS document_type,
 d.it_code AS it_code,
 d.it_model_no AS it_model_no,
 'inc-'||ised_idx AS docit_idx,
 to_char(ised_expired_date,'YYYY.MM') AS it_expired_date,
 ised_expired_date AS ed,
 ised_qty*-1 AS it_qty,
 CASE
  WHEN b.inc_doc_type IN(1,2) THEN '../delivery/confirm_return.php?_inc_idx='|| b.inc_idx || '&_std_idx=' || b.inc_std_idx
  WHEN b.inc_doc_type = 3 THEN '../delivery/confirm_return_dt.php?_inc_idx='|| b.inc_idx || '&_std_idx=' || b.inc_std_idx
 END AS go_page
FROM
 ".ZKP_SQL."_tb_customer AS a
 JOIN ".ZKP_SQL."_tb_incoming AS b USING(cus_code)
 JOIN ".ZKP_SQL."_tb_incoming_stock_ed AS c USING(inc_idx)
 JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE " . $strWhereInc ."
";

$sql = "$sql_out UNION $sql_inc ORDER BY cug_name, cus_code, doc_idx, it_code, ed, docit_idx";
echo "<pre>";
//echo $sql_inc;
echo "</pre>";
// raw data
$rd = array();
$rdIdx = 0;
$i = 0;
$cache = array("","","","","");
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['cug_name'],		//0
		$col['cus_code'],		//1
		$col['cus_full_name'],	//2
		$col['doc_idx'], 		//3
		$col['document_no'], 	//4
		$col['document_date'],	//5
		$col['do_no'], 			//6
		$col['confirm_date'],	//7
		$col['document_type'],	//8
		$col['docit_idx'],		//9
		$col['it_code'], 		//10
		$col['it_model_no'],	//11
		$col['it_expired_date'],//12
		$col['it_qty'],			//13
		$col['go_page']			//14
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

	if($cache[2] != $col['doc_idx']) {
		$cache[2] = $col['doc_idx'];
		$group0[$col['cug_name']][$col['cus_code']][$col['doc_idx']] = array();
	}

	if($cache[3] != $col['it_code']) {
		$cache[3] = $col['it_code'];
		$group0[$col['cug_name']][$col['cus_code']][$col['doc_idx']][$col['it_code']] = array();
	}

	if($cache[4] != $col['it_expired_date']) {
		$cache[4] = $col['it_expired_date'];
	}

	$group0[$col['cug_name']][$col['cus_code']][$col['doc_idx']][$col['it_code']][$col['it_expired_date']] = 1;
}

echo "<pre>";
//var_dump($group0);
echo "</pre>";

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$g_total = 0;
$numInvoice = 0;

//GROUP
foreach ($group0 as $total1 => $group1) {

	echo "<span class=\"comment\"><b> PUSAT: ". $total1. "</b></span>\n";	//Group Name
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th>CUSTOMER</th>
			<th width="13%">DOCUMENT NO</th>
			<th width="8%">DOCUMENT DATE</th>
			<th width="12%">DO NO</th>
			<th width="8%">CONFIRM DATE</th>
			<th width="15%">MODEL NO</th>
			<th width="8%">EXPIRED DATE</th>			
			<th width="5%">QTY<br>(EA)</th>
		</tr>\n
END;
	print "<tr>\n";

	$cus_total = 0;
	$print_tr_1 = 0;
	//CUSTOMER 
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += count($group2);

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".$rd[$rdIdx][1]."]<br/>".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');	//Customer

		$print_tr_2 = 0;
		//DOCUMENT 
		foreach($group2 as $total3 => $group3) {
			$rowSpan = 0;
			array_walk_recursive($group3, 'getRowSpan');

			if($print_tr_2++ > 0) print "<tr>\n";
			cell_link('<span class="bar">'.$rd[$rdIdx][4].'</span>', ' valign="top" align="center" rowspan="'.$rowSpan.'"', 	// Document no
				' href="'.$rd[$rdIdx][14].'"');
			cell($rd[$rdIdx][5], ' valign="top" align="center" rowspan="'.$rowSpan.'"');		// Document date
			cell($rd[$rdIdx][6], ' valign="top" align="center" rowspan="'.$rowSpan.'"');		// DO no
			cell($rd[$rdIdx][7], ' valign="top" align="center" rowspan="'.$rowSpan.'"');		// Confirm date

			$inv_total = 0;
			$print_tr_3 = 0;
			//ITEM LIST
			foreach($group3 as $total4 => $group4) {
				$rowSpan = 0;
				array_walk_recursive($group4, 'getRowSpan');

				if($print_tr_3++ > 0) print "<tr>\n";
				cell("[".trim($rd[$rdIdx][10])."] ".$rd[$rdIdx][11], ' valign="top" rowspan="'.$rowSpan.'"');		//Model no

				$item_total = 0;
				$print_tr_4 = 0;
				//ITEM LIST
				foreach($group4 as $total5) {
					if($print_tr_4++ > 0) print "<tr>\n";

					cell($rd[$rdIdx][12], ' align="center"');					// Expired date
					cell(number_format($rd[$rdIdx][13],2), ' align="right"');	// Qty
if(isset($a[trim($rd[$rdIdx][10])][$rd[$rdIdx][12]])){
	$a[trim($rd[$rdIdx][10])][$rd[$rdIdx][12]] += $rd[$rdIdx][13];
} else {
	$a[trim($rd[$rdIdx][10])][$rd[$rdIdx][12]] = $rd[$rdIdx][13];
}
					print "</tr>\n";
	
					$item_total += $rd[$rdIdx][13];
					$rdIdx++;
				}
				$inv_total += $item_total;
			}
			print "<tr>\n";
			cell("INVOICE TOTAL ", ' colspan="6" align="right" style="color:darkblue;"');
			cell(number_format($inv_total,2), ' align="right" style="color:darkblue;"');
			print "</tr>\n";
	
			$cus_total += $inv_total;
			$numInvoice++;
		}
	}
	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="7" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($cus_total,2), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$g_total += $cus_total;
}

print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th>CUSTOMER</th>
			<th width="13%">DOCUMENT NO</th>
			<th width="8%">DOCUMENT DATE</th>
			<th width="12%">DO NO</th>
			<th width="8%">CONFIRM DATE</th>
			<th width="15%">MODEL NO</th>
			<th width="8%">EXPIRED DATE</th>			
			<th width="5%">QTY<br>(EA)</th>
		</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="7" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($g_total,2), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";

echo "<pre>";
//var_dump($a);
echo "</pre>";
?>