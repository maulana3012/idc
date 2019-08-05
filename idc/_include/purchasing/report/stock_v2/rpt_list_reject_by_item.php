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
$tmp_1	= array();
$tmp_2	= array();

if ($some_date != "") {
	$tmp_1[]   = "inc_date = DATE '$some_date'";
	$tmp_2[]   = "rjt_date = DATE '$some_date'";
} else {
	$tmp_1[]   = "inc_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp_2[]   = "rjt_date BETWEEN DATE '$period_from' AND '$period_to'";
}

if($_dept != "all") {
	$tmp_1[] = "inc_dept = '$_dept'";
	$tmp_2[] = "rjt_idx is null";
}

if($_status != "all") {
	$tmp_1[]   = "rjit_status = '$_status'";
	$tmp_2[]   = "rjt_idx is null";
}

$strWhere1	= implode(" AND ", $tmp_1);
$strWhere2	= implode(" AND ", $tmp_2);

$sql = "
SELECT
  icat_pidx AS icat_pidx,
  icat_midx AS icat_midx,
  rjit_idx AS reject_idx,
  rjt_date AS reject_date,
  to_char(rjt_date, 'dd-Mon-yy') AS incoming_date,
  inc_idx AS inc_idx,
  inc_doc_ref AS document_no,
  to_char(inc_date, 'dd-Mon-yy') AS document_date,
  it_code AS it_code,
  it_model_no AS it_model_no,
  rjit_serial_number AS it_serial_no,
  to_char(rjit_warranty, 'Mon-yy') AS it_warranty_date,
  rjit_desc AS it_desc,
  CASE
	WHEN rjit_status = 'on_wh' THEN 'On Warehouse'
	WHEN rjit_status = 'on_repair' THEN 'Repaired'
	WHEN rjit_status = 'on_stock' THEN 'Back to Stock'
	WHEN rjit_status = 'on_deleted' THEN 'Deleted'
  END AS it_status,
  '../delivery_v2/confirm_return.php?_inc_idx=' || inc_idx || '&_std_idx=' ||inc_std_idx AS go_page,
  'true' AS it_att
FROM
 ".ZKP_SQL."_tb_incoming AS a
 JOIN ".ZKP_SQL."_tb_reject AS b ON rjt_doc_idx = inc_idx
 JOIN ".ZKP_SQL."_tb_reject_item AS c USING(rjt_idx)
 JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS e USING(icat_midx)
WHERE " . $strWhere1 ." AND rjt_doc_type = 1
	UNION
SELECT
  icat_pidx AS icat_pidx,
  icat_midx AS icat_midx,
  rjit_idx AS reject_idx,
  rjt_date AS reject_date,
  to_char(rjt_date, 'dd-Mon-yy') AS incoming_date,
  null AS inc_idx,
  null AS document_no,
  null AS document_date,
  it_code AS it_code,
  it_model_no AS it_model_no,
  rjit_serial_number AS it_serial_no,
  to_char(rjit_warranty, 'Mon-yy') AS it_warranty_date,
  rjit_desc AS it_desc,
  CASE
	WHEN rjit_status = 'on_wh' THEN 'On Warehouse'
	WHEN rjit_status = 'on_repair' THEN 'Repaired'
	WHEN rjit_status = 'on_stock' THEN 'Back to Stock'
	WHEN rjit_status = 'on_deleted' THEN 'Deleted'
  END AS it_status,
  null AS go_page,
  CASE
	WHEN rjit_status = 'on_stock' THEN 'true'
	ELSE 'false'
  END AS it_att
FROM
 ".ZKP_SQL."_tb_reject AS b
 JOIN ".ZKP_SQL."_tb_reject_item AS c USING(rjt_idx)
 JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS e USING(icat_midx)
WHERE " . $strWhere2 ." AND rjt_doc_type = 2
ORDER BY icat_pidx, icat_midx, it_code, reject_idx";

// raw data
$rd = array();
$rdIdx = 0;
$cache = array("","",""); // 3th level
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'],			//0
		$col['reject_idx'],			//1
		$col['reject_date'],		//2
		$col['incoming_date'],		//3
		$col['document_no'], 		//4
		$col['document_date'],		//5
		$col['it_code'],			//6
		$col['it_model_no'], 		//7
		$col['it_serial_no'],		//8
		$col['it_warranty_date'],	//9
		$col['it_desc'], 			//10
		$col['it_status'], 			//11
		$col['go_page'], 			//12
		$col['it_att']	 			//13
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

	if($cache[2] != $col['reject_idx']) {
		$cache[2] = $col['reject_idx'];
	}

	$group0[$col['icat_midx']][$col['it_code']][$col['reject_idx']] = 1;
}

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
	<table width="100%" class="table_f">
		<tr>
			<th width="18%">MODEL NO</th>
			<th width="8%">IN DATE</th>
			<th width="13%">REFERENCE NO</th>
			<th width="8%">RETURN DATE</th>
			<th width="15%">SERIAL NO</th>
			<th width="8%">EXPIRED<br />WARRANTY</th>
			<th>FULL DESCRIPTION</th>
			<th width="12%">STATUS</th>
			<th width="5%">QTY</th>
		</tr>\n
END;
	$cat_total = 0;
	$print_tr_1 = 0;

	print "<tr>\n";
	//MODEL
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".trim($rd[$rdIdx][6])."] ".$rd[$rdIdx][7], ' valign="top" rowspan="'.$rowSpan.'"');	//model no

		$model_total	= 0;
		$print_tr_2		= 0;
		//ORDER
		foreach($group2 as $total3 => $group3) {

			if($print_tr_2++ > 0) print "<tr>\n";
			cell($rd[$rdIdx][3], ' align="center" valign="top"');							//warehouse confirm
			cell_link("<span class=\"bar\">".$rd[$rdIdx][4]."</bar>", ' align="center" valign="top"',
				' href="'.$rd[$rdIdx][12].'"');												//Reference document
			cell($rd[$rdIdx][5], ' align="center" valign="top"');							//Reference date
			cell("<span class=\"bar\">".strtoupper($rd[$rdIdx][8])."</span>", '');			//SN
			cell($rd[$rdIdx][9], ' align="center"');	//Expired
			cell(cut_string($rd[$rdIdx][10],25));		//Desc
			cell($rd[$rdIdx][11]);						//Status
			cell('1', ' align="right"');				//qty
			print "</tr>\n";

			$model_total ++;
			$model_no	 = $rd[$rdIdx][7];
			$rdIdx++;
		}

		print "<tr>\n";
		cell($model_no, ' colspan="7" align="right" style="color:darkblue"');
		cell(number_format($model_total), ' align="right" style="color:darkblue"');
		print "</tr>\n";

		$cat_total += $model_total;
	}
	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="8" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($cat_total), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$grand_total += $cat_total;
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_f">
	<tr>
		<th width="18%">MODEL NO</th>
		<th width="8%">IN DATE</th>
		<th width="13%">REFERENCE NO</th>
		<th width="8%">RETURN DATE</th>
		<th width="15%">SERIAL NO</th>
		<th width="8%">EXPIRED<br />WARRANTY</th>
		<th>FULL DESCRIPTION</th>
		<th width="12%">STATUS</th>
		<th width="5%">QTY</th>
	</tr>\n
END;
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="8" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br />\n";
?>