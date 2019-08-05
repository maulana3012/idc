<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim

*
* $_po_date : Inquire Date
*
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
		$tmp_inc[]   = "b.inc_date = DATE '$some_date'";
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

$sql = "
SELECT
  a.cus_code AS cus_code,
  a.cus_full_name AS cus_full_name,
  b.out_idx AS doc_idx,
  b.out_doc_ref AS document_no,
  to_char(b.out_issued_date,'dd-Mon-YY') AS s_document_date,
  b.out_issued_date AS document_date,
  b.out_code AS do_no,
  to_char(b.out_cfm_date,'dd-Mon-YY') AS s_confirm_date,
  b.out_cfm_date AS confirm_date,
  CASE
	WHEN b.out_type = 1 THEN 'VAT'
	WHEN b.out_type = 2 THEN 'NON'
	ELSE 'NON SPECIFIED'
  END AS document_type,
  'out-'||c.oted_idx AS docit_idx,
  c.it_code AS it_code,
  d.it_model_no AS it_model_no,
  to_char(oted_date,'YYYY.MM') AS it_expired_date,
  oted_date AS ed,
  oted_qty AS it_qty,
  icat.icat_pidx AS icat_pidx,
  icat.icat_midx AS icat_midx,
 '../delivery/detail_do.php?_code='||out_idx AS go_page
FROM
  ".ZKP_SQL."_tb_customer AS a
  JOIN ".ZKP_SQL."_tb_outgoing AS b USING(cus_code)
  JOIN ".ZKP_SQL."_tb_outgoing_ed AS c USING(out_idx)
  JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE " . $strWhereOut ."
	UNION
SELECT
  a.cus_code AS cus_code,
  a.cus_full_name AS cus_full_name,
  b.inc_idx AS doc_idx,
  b.inc_doc_ref AS document_no,
  to_char(b.inc_date,'dd-Mon-YY') AS s_document_date,
  b.inc_date AS document_date,
  b.inc_doc_ref AS do_no,
  to_char(b.inc_confirmed_timestamp,'dd-Mon-YY') AS s_confirm_date,
  b.inc_confirmed_timestamp AS confirm_date,
  CASE
	WHEN b.inc_type = 1 THEN 'VAT'
	WHEN b.inc_type = 2 THEN 'NON'
	ELSE 'NON SPECIFIED'
  END AS document_type,
  'inc-'||c.ised_idx AS docit_idx,
  c.it_code AS it_code,
  d.it_model_no AS it_model_no,
  to_char(ised_expired_date,'YYYY.MM') AS it_expired_date,
  ised_expired_date AS ed,
  ised_qty*-1 AS it_qty,
  icat.icat_pidx AS icat_pidx,
  icat.icat_midx AS icat_midx,
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
ORDER BY icat_pidx, icat_midx, it_code, it_expired_date, $_filter_date, docit_idx";

// raw data
$rd = array();
$rdIdx = 0;
$cache = array("","","","");
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'], 			//0
		$col['it_code'],			//1
		$col['it_model_no'],		//2
		$col['doc_idx'],			//3
		$col['cus_code'],			//4
		$col['cus_full_name'],		//5
		$col['do_no'], 				//6
		$col['confirm_date'],	 	//7
		$col['document_no'],		//8
		$col['document_date'],		//9
		$col['document_type'],		//10
		$col['docit_idx'],			//11
		$col['it_expired_date'],	//12
		$col['it_qty'],				//13
		$col['go_page']				//14
	);

	//1st grouping
	if($cache[0] != $col['icat_midx']) {
		$cache[0] = $col['icat_midx'];
		$group0[$col['icat_midx']] = array();
	}

	if($cache[1] != $col['it_code']) {
		$cache[1] = $col['it_code'];
		$group0[$col['icat_midx']][$col['it_code']] = array();
	}

	if($cache[2] != $col['it_expired_date']) {
		$cache[2] = $col['it_expired_date'];
		$group0[$col['icat_midx']][$col['it_code']][$col['it_expired_date']] = array();
	}
	
	if($cache[3] != $col['doc_idx']) {
		$cache[3] = $col['doc_idx'];
	}
	
	$group0[$col['icat_midx']][$col['it_code']][$col['it_expired_date']][$col['doc_idx']] = 1;
}

echo "<pre>";
//echo $sql;
echo "</pre>";

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$grand_total = 0;

//GROUP
foreach ($group0 as $total1 => $group1) {

	//get category path from current icat_midx.
	$path = executeSP(ZKP_SQL."_getCategoryPath", $rd[$rdIdx][0]);
	eval(html_entity_decode($path[0]));	
	$path = array_reverse($path);
	$total1 = $path[1][4]." > ".$path[2][4]." > ".$path[3][4] ;

	echo "<span class=\"comment\"><b> CATEGORY: ". $total1. "</b></span>\n";
	print <<<END
	<table width="80%" class="table_f">
		<tr>
			<th width="18%">MODEL NO</th>
			<th width="10%">EXPIRED DATE</th>
			<th>CUSTOMER</th>
			<th width="15%">INV. NO</th>
			<th width="10%">DO DATE</th>
			<th width="10%">CONFIRM<br />DATE</th>
			<th width="7%">QTY</th>
		</tr>\n
END;
	$cat_total = 0;
	$print_tr_1 = 0;

	print "<tr>\n";
	//MODEL
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += count($group2)+1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".trim($rd[$rdIdx][1])."] ".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');	//Item code, model no

		$model_total = 0;
		$print_tr_2 = 0;
		// EXPIRED DATE
		foreach($group2 as $total3 => $group3) {
			$rowSpan = 0;
			array_walk_recursive($group3, 'getRowSpan');
			$rowSpan += 1;

			if($print_tr_2++ > 0) print "<tr>\n";
			cell($rd[$rdIdx][12], ' align="center" valign="top" rowspan="'.$rowSpan.'"');	// Expired date

			$ed_total = 0;
			$print_tr_3 = 0;
			// DOCUMENT
			foreach($group3 as $total4 => $group4) {
				if($print_tr_3++ > 0) print "<tr>\n";
			
				cell("[".trim($rd[$rdIdx][4])."] ".$rd[$rdIdx][5]);	// Customer
				cell_link('<span class="bar">'.$rd[$rdIdx][8].'</span>', ' valign="top" align="center"',
					' href="'.$rd[$rdIdx][14].'"'); 				// Document no
				cell(date('d-M-y',strtotime($rd[$rdIdx][9])), ' align="center"');	// Document date
				cell(date('d-M-y',strtotime($rd[$rdIdx][7])), ' align="center"');	// Confirm date
				cell(number_format($rd[$rdIdx][13],2), ' align="right"'); // Qty
				print "</tr>\n";

if(isset($a[trim($rd[$rdIdx][1])][$rd[$rdIdx][12]])){
	$a[trim($rd[$rdIdx][1])][$rd[$rdIdx][12]] += $rd[$rdIdx][13];
} else {
	$a[trim($rd[$rdIdx][1])][$rd[$rdIdx][12]] = $rd[$rdIdx][13];
}
				$item = "[".trim($rd[$rdIdx][1])."] ".$rd[$rdIdx][2];
				$ed_total += $rd[$rdIdx][13];
				$rdIdx++;
			}
			print "<tr>\n";
			cell("TOTAL ED $total3", ' colspan="4" align="right" style="color:darkblue"');
			cell(number_format($ed_total,2), ' align="right" style="color:darkblue"');
			print "</tr>\n";
			$model_total += $ed_total;
		}
		print "<tr>\n";
		cell($item, ' colspan="5" align="right" style="color:darkblue"');
		cell(number_format($model_total,2), ' align="right" style="color:darkblue"');
		print "</tr>\n";
		$cat_total += $model_total;
	}
	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="6" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($cat_total,2), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";	
	$grand_total += $cat_total;
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="80%" class="table_f">
	<tr>
		<th width="18%">MODEL NO</th>
		<th width="10%">EXPIRED DATE</th>
		<th>CUSTOMER</th>
		<th width="15%">INV. NO</th>
		<th width="10%">DO DATE</th>
		<th width="10%">CONFIRM<br />DATE</th>
		<th width="7%">QTY</th>
	</tr>\n
END;
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="6" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total,2), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";

echo "<pre>";
//var_dump($a);
echo "</pre>";
?>