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
  rjit_idx AS reject_idx,
  rjt_date AS reject_date,
  to_char(rjt_date, 'dd-Mon-yy') AS incoming_date,
  inc_doc_ref AS document_no,
  to_char(inc_date, 'dd-Mon-yy') AS document_date,
  inc_idx AS inc_idx,
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
  '../delivery/confirm_return.php?_inc_idx=' || inc_idx || '&_std_idx=' || inc_std_idx AS go_page,
  'true' AS it_att
FROM
 ".ZKP_SQL."_tb_incoming AS a
 JOIN ".ZKP_SQL."_tb_reject AS b ON rjt_doc_idx = inc_idx
 JOIN ".ZKP_SQL."_tb_reject_item AS c USING(rjt_idx)
 JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
WHERE " . $strWhere1 ." AND rjt_doc_type = 1
	UNION
SELECT
  rjit_idx AS reject_idx,
  rjt_date AS reject_date,
  to_char(rjt_date, 'dd-Mon-yy') AS incoming_date,
  null AS document_no,
  null AS document_date,
  null AS inc_idx,
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
  'false' AS it_att
FROM
 ".ZKP_SQL."_tb_reject
 JOIN ".ZKP_SQL."_tb_reject_item AS c USING(rjt_idx)
 JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
WHERE " . $strWhere2 ." AND rjt_doc_type = 2
ORDER BY reject_date, reject_idx";

// raw data
$rd = array();
$rdIdx = 0;
$i = 0;
$cache = array("");
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['reject_idx'],			//0
		$col['reject_date'],		//1
		$col['incoming_date'],		//2
		$col['document_no'],		//3
		$col['document_date'], 		//4
		$col['inc_idx'],			//5
		$col['it_code'], 			//6
		$col['it_model_no'],		//7
		$col['it_serial_no'],		//8
		$col['it_warranty_date'], 	//9
		$col['it_desc'], 			//10
		$col['it_status'], 			//11
		$col['go_page'], 			//12
		$col['it_att']	 			//13
	);

	//1st grouping
	if($cache[0] != $col['reject_idx']) {
		$cache[0] = $col['reject_idx'];
	}

	$group0[$col['reject_idx']] = 1;
}

//GROUP TOTAL
$ggTotal	= 0;
print <<<END
<table width="100%" class="table_f">
	<tr>
		<th width="8%">IN DATE</th>
		<th width="13%">REFERENCE NO</th>
		<th width="8%">RETURN DATE</th>
		<th width="18%">MODEL NO</th>
		<th width="15%">SERIAL NO</th>
		<th width="8%">EXPIRED<br />WARRANTY</th>
		<th>FULL DESCRIPTION</th>
		<th width="12%">STATUS</th>
		<th width="5%">QTY</th>
	</tr>\n
END;

foreach ($group0 as $total1 => $group1) {
	print "<tr>\n";
	cell($rd[$rdIdx][2], ' align="center"');								//incoming date
	cell_link("<span class=\"bar\">".$rd[$rdIdx][3]."</span>", ' align="center"', 
		' href="'.$rd[$rdIdx][12].'"');										//reference no
	cell($rd[$rdIdx][4], ' align="center"');								//reference date
	cell("[". trim($rd[$rdIdx][6]). "] ".$rd[$rdIdx][7], ' align="left"');	//model no
	cell("<span class=\"bar\">".strtoupper($rd[$rdIdx][8])."</span>", '');	//SN
	cell($rd[$rdIdx][9], ' align="center"');								//expired date
	cell(cut_string($rd[$rdIdx][10],25));									//desc
	cell($rd[$rdIdx][11]);													//status
	cell('1', ' align="right"');											//qty
	print "</tr>\n";

	$ggTotal ++;
	$rdIdx++;
}

print "<tr height=\"25px\">\n";
cell("<b>TOTAL</b>", ' colspan="8" align="right" style="color:brown; background-color:lightyellow"');
cell($ggTotal, ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br />\n";
?>