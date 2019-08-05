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
$tmp	= array();

if($_order_by == '1') {
	$tmp[0][] = $tmp[1][] = "substr(out_code,1,1)='D' and substr(out_code,4,1)!='M'";
} else if($_order_by == '2') {
	$tmp[0][] = $tmp[1][] = "substr(out_code,1,1)='D' and substr(out_code,4,1)='M'";
}

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp[0][] = $tmp[1][] = "e.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if($_source != 'all') {
	$tmp[0][] = "b.out_doc_type = $_source";
	$tmp[1][] = "b.out_doc_type = '".$v_source_doc[$_source] ."'";
}

if ($_cug_code != 'all') {
	//If group specified, 
	$tmp[0][] = $tmp[1][] = "b.cus_code IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$sql1 = " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
	$sql2 = " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
} else {
	$sql1	= "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = b.cus_code),
		'Others') AS cug_name,"; // if null, return Others Group
	$sql2	= "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = b.cus_code),
		'Others') AS cug_name,"; // if null, return Others Group
}

if($_dept != 'all') {
	if($_dept == 'DEMO') {
		$tmp[0][] = "b.out_doc_type = 6";
		$tmp[1][] = "b.out_doc_type = 'DM'";
	} else {
		$tmp[0][] = "b.out_dept = '$_dept' AND b.out_doc_type != 6";
		$tmp[1][] = "b.out_dept = '$_dept' AND b.out_doc_type != 'DM'";
	}
}

if ($some_date != "") {
	$tmp[0][] = $tmp[1][] = "b.out_cfm_date = DATE '$some_date'";
} else {
	$tmp[0][] = $tmp[1][] = "b.out_cfm_date BETWEEN DATE '$period_from' AND '$period_to'";
}

$tmp[1][] = "b.out_is_revised = false";

$strWhere[0] = implode(" AND ", $tmp[0]);
$strWhere[1] = implode(" AND ", $tmp[1]);

$sql1 .= "
 a.cus_code AS cus_code,
 a.cus_full_name AS cus_full_name,
 'v1-'||b.out_idx AS out_idx,
 b.out_code AS deli_code,
 b.out_doc_ref AS doc_ref_code,
 to_char(b.out_cfm_date,'dd-Mon-YY') AS deli_date,
 to_char(b.out_issued_date,'dd-Mon-YY') AS issued_date,
 CASE
   WHEN b.out_type = 1 THEN 'VAT'
   WHEN b.out_type = 2 THEN 'NON'
   WHEN b.out_type = 3 THEN 'NON SPECIFIED'
 END AS deli_type,
 d.it_code AS it_code,
 d.it_model_no AS it_model_no,
 'v2-'||c.otit_idx AS otit_idx,
 c.otit_vat_qty AS it_vat_qty,
 c.otit_non_qty AS it_non_qty,
 c.otit_qty AS it_qty
FROM
 ".ZKP_SQL."_tb_customer AS a
 JOIN ".ZKP_SQL."_tb_outgoing AS b USING(cus_code)
 JOIN ".ZKP_SQL."_tb_outgoing_item AS c USING(out_idx)
 JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS e USING(icat_midx)
WHERE ". $strWhere[0];

$sql2 .= "
 a.cus_code AS cus_code,
 a.cus_full_name AS cus_full_name,
 'v2-'||b.out_idx AS out_idx,
 b.out_code AS deli_code,
 b.out_doc_ref AS doc_ref_code,
 to_char(b.out_cfm_date,'dd-Mon-YY') AS deli_date,
 to_char(b.out_issued_date,'dd-Mon-YY') AS issued_date,
 CASE
   WHEN b.out_type = 1 THEN 'VAT'
   WHEN b.out_type = 2 THEN 'NON'
   WHEN b.out_type = 3 THEN 'NON SPECIFIED'
 END AS deli_type,
 d.it_code AS it_code,
 d.it_model_no AS it_model_no,
 'v2-'||c.otit_idx AS otit_idx,
 c.otit_vat_qty AS it_vat_qty,
 c.otit_non_qty AS it_non_qty,
 c.otit_qty AS it_qty
FROM
 ".ZKP_SQL."_tb_customer AS a
 JOIN ".ZKP_SQL."_tb_outgoing_v2 AS b USING(cus_code)
 JOIN ".ZKP_SQL."_tb_outgoing_item_v2 AS c USING(out_idx)
 JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS e USING(icat_midx)
WHERE ". $strWhere[1];

$sql = "$sql1 UNION $sql2 ORDER BY cug_name, cus_code, out_idx, it_code, otit_idx";

echo "<pre>";
//var_dump($sql2);
echo "</pre>";

// raw data
$rd = array();
$rdIdx = 0;
$i = 0;
$cache = array("","","","");
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['cug_name'],	//0
		$col['cus_code'],	//1
		$col['cus_full_name'],	//2
		$col['out_idx'], 	//3
		$col['deli_code'], 	//4
		$col['doc_ref_code'], 	//5
		$col['deli_date'],	//6
		$col['issued_date'],	//7
		$col['deli_type'],	//8
		$col['otit_idx'],	//9
		$col['it_code'], 	//10
		$col['it_model_no'],	//11
		$col['it_vat_qty'], 	//12
		$col['it_non_qty'], 	//13
		$col['it_qty']		//14
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

	if($cache[2] != $col['out_idx']) {
		$cache[2] = $col['out_idx'];
		$group0[$col['cug_name']][$col['cus_code']][$col['out_idx']] = array();
	}

	if($cache[3] != $col['otit_idx']) {
		$cache[3] = $col['otit_idx'];
	}

	$group0[$col['cug_name']][$col['cus_code']][$col['out_idx']][$col['otit_idx']] = 1;
}

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
			<th width="15%" colspan="2">DELI NO</th>
			<th width="13%">INV. NO</th>
			<th width="8%">DO DATE</th>
			<th width="8%">CONFIRM DATE</th>
			<th width="20%">MODEL NO</th>
			<th width="7%">QTY<br>(EA)</th>
		</tr>\n
END;
	print "<tr>\n";

	$cus_total = 0;
	$print_tr_1 = 0;
	//CUSTOMER INFO
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += count($group2);

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".$rd[$rdIdx][1]."]<br/>".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');	//Customer

		$print_tr_2 = 0;
		//BOOKING INFO
		foreach($group2 as $total3 => $group3) {
			$rowSpan = 0;
			array_walk_recursive($group3, 'getRowSpan');

			if($print_tr_2++ > 0) print "<tr>\n";
			//cell('<input type="checkbox" name="chkDO[]" value="'.$rd[$rdIdx][3].'">', ' valign="top" align="center" rowspan="'.$rowSpan.'"');		//Checkbox
			cell_link("<span class=\"bar\">".$rd[$rdIdx][4]."</span>", ' align="center" valign="top" colspan="2" rowspan="'.$rowSpan.'"',
					' href="./detail_do.php?_code='.substr($rd[$rdIdx][3],3).'&_source='. substr($rd[$rdIdx][3],0,2) .'"');						//Delivery code
			cell($rd[$rdIdx][5], ' valign="top" align="center" rowspan="'.$rowSpan.'"');		//Document ref code
			cell($rd[$rdIdx][7], ' valign="top" align="center" rowspan="'.$rowSpan.'"');		//Issue date
			cell($rd[$rdIdx][6], ' valign="top" align="center" rowspan="'.$rowSpan.'"');		//Delivery date

			$inv_total = 0;
			$print_tr_3 = 0;
			//ITEM LIST
			foreach($group3 as $group4) {
				if($print_tr_3++ > 0) print "<tr>\n";

				cell("[".trim($rd[$rdIdx][10])."] ".$rd[$rdIdx][11]);		//Model no
				cell(number_format($rd[$rdIdx][14],2), ' align="right"');	//Qty
				print "</tr>\n";

				$inv_total += $rd[$rdIdx][14]; //qty
				$rdIdx++;
			}
			print "<tr>\n";
			cell("INVOICE TOTAL", ' colspan="6" align="right" style="color:darkblue;"');
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

/*
print <<<END
<table width="100%" class="table_layout">
	<tr>
		<td><input type="checkbox" name="chkAll" onclick="checkAll(this.checked)"><span class="comment">check all</span></td>
		<td align="right"><button name='btnSummarize' class='input_btn' style='width:130px;' onclick="summarizeDO()"><img src="../../_images/icon/list.gif" width="20px" align="middle"> &nbsp; Summarize</button></td>
	</tr>
</table><br />
END;
*/
print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th>CUSTOMER</th>
			<th width="15%">DELI NO</th>
			<th width="13%">INV. NO</th>
			<th width="8%">DO DATE</th>
			<th width="8%">CONFIRM DATE</th>
			<th width="20%">MODEL NO</th>
			<th width="7%">QTY<br>(EA)</th>
		</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="6" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($g_total,2), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>