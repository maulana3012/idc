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
$tmp1	= array();
$tmp2	= array();

if(ZKP_FUNCTION == 'ALL') {
	if($_order_by != 'all'){
		$tmp1[]	= "book_ordered_by = $_order_by";
	}
} else {
	$tmp1[]	= "book_ordered_by = ".$cboFilter[1][ZKP_FUNCTION][0][0];
}

if($_source != 'all') {
	$tmp1[]	= "b.book_doc_type = $_source"; 
	$tmp2[]	= "b.book_doc_type = $_source"; 
}

if (isset($_cug_code) && $_cug_code != 'all') {
	//If group specified, 
	$tmp1[]	= "b.cus_code IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmp2[]	= "b.cus_code IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$sql1 	= " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
	$sql2 	= " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
} else {
	// if null, return Others Group
	$sql1	= "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = b.cus_code),
		'Others') AS cug_name,";
	$sql2	= "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = b.cus_code),
		'Others') AS cug_name,";
}

if ($some_date != "") {
	$tmp1[]   = "b.book_date = DATE '$some_date'";
	$tmp2[]   = "b.book_date = DATE '$some_date'";
} else {
	$tmp1[]   = "b.book_date BETWEEN DATE '$period_from' AND '$period_to'";
	$tmp2[]   = "b.book_date BETWEEN DATE '$period_from' AND '$period_to'";
}

$tmp1[] = "b.book_is_delivered = 'f' AND b.book_dept = '$department' AND book_doc_type IN(1,5)";
$tmp2[] = "b.book_is_delivered = 'f' AND b.book_dept = '$department' AND book_doc_type IN(2,3,4)";

$strWhere1   = implode(" AND ", $tmp1);
$strWhere2   = implode(" AND ", $tmp2);

$sql1 .= "
 a.cus_code AS cus_code,
 a.cus_full_name AS cus_full_name,
 b.book_doc_ref AS doc_code,
 b.book_idx AS booked_idx,
 b.book_code AS booked_code,
 book_date,
 to_char(b.book_date,'dd-Mon-YY') AS issued_date,
 CASE
   WHEN b.book_type = 1 THEN 'VAT'
   WHEN b.book_type = 2 THEN 'NON'
   WHEN b.book_type = 3 THEN 'NON SPECIFIED'
 END AS booked_type,
 d.it_code AS it_code,
 d.it_model_no AS it_model_no,
 c.boit_idx AS it_idx,
 c.boit_qty AS it_qty,
 c.boit_remark AS it_remark,
  CASE 
	WHEN book_doc_type = 1 THEN '../billing/revise_billing.php?_code=' || book_doc_ref
	WHEN book_doc_type = 2 THEN '../order/revise_order.php?_code=' || book_doc_ref
	WHEN book_doc_type = 3 THEN '../other/revise_dt.php?_code=' || book_doc_ref
	WHEN book_doc_type = 4 THEN '../other/revise_df.php?_code=' || book_doc_ref
	WHEN book_doc_type = 5 THEN '../other/revise_dr.php?_code=' || book_doc_ref
  END AS go_page
FROM
 ".ZKP_SQL."_tb_customer AS a
 JOIN ".ZKP_SQL."_tb_booking AS b USING(cus_code)
 JOIN ".ZKP_SQL."_tb_booking_item AS c USING(book_idx)
 JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
WHERE " . $strWhere1;

$sql2 .= "
 a.cus_code AS cus_code,
 a.cus_full_name AS cus_full_name,
 b.book_doc_ref AS doc_code,
 b.book_idx AS booked_idx,
 b.book_code AS booked_code,
 book_date,
 to_char(b.book_date,'dd-Mon-YY') AS issued_date,
 CASE
   WHEN b.book_type = 1 THEN 'VAT'
   WHEN b.book_type = 2 THEN 'NON'
   WHEN b.book_type = 3 THEN 'NON SPECIFIED'
 END AS booked_type,
 d.it_code AS it_code,
 d.it_model_no AS it_model_no,
 c.boit_idx AS it_idx,
 c.boit_qty AS it_qty,
 c.boit_remark AS it_remark,
  CASE 
	WHEN book_doc_type = 1 THEN '../billing/revise_billing.php?_code=' || book_doc_ref
	WHEN book_doc_type = 2 THEN '../order/revise_order.php?_code=' || book_doc_ref
	WHEN book_doc_type = 3 THEN '../other/revise_dt.php?_code=' || book_doc_ref
	WHEN book_doc_type = 4 THEN '../other/revise_df.php?_code=' || book_doc_ref
	WHEN book_doc_type = 5 THEN '../other/revise_dr.php?_code=' || book_doc_ref
  END AS go_page
FROM
 ".ZKP_SQL."_tb_customer AS a
 JOIN ".ZKP_SQL."_tb_booking AS b USING(cus_code)
 JOIN ".ZKP_SQL."_tb_booking_item AS c USING(book_idx)
 JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
WHERE " . $strWhere2;

$sql = "$sql1 UNION $sql2 ORDER BY cug_name, cus_code, book_date, booked_code, it_code, it_idx";
/*
echo "<pre>";
//echo $strWhere1 ."<Br />".$strWhere2 ."<Br />";
echo $sql1;
echo "</pre>";
*/
// raw data
$rd = array();
$rdIdx = 0;
$i = 0;
$cache = array("","","","");
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['cug_name'],		//0
		$col['cus_code'],		//1
		$col['cus_full_name'],	//2
		$col['doc_code'], 		//3
		$col['issued_date'],	//4
		$col['booked_idx'], 	//5
		$col['booked_code'], 	//6
		$col['booked_type'],	//7
		$col['it_idx'], 		//8
		$col['it_code'], 		//9
		$col['it_model_no'],	//10
		$col['it_qty'], 		//11
		$col['it_remark'],		//12
		$col['go_page']			//13
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

	if($cache[2] != $col['booked_idx']) {
		$cache[2] = $col['booked_idx'];
		$group0[$col['cug_name']][$col['cus_code']][$col['booked_idx']] = array();
	}

	if($cache[3] != $col['it_idx']) {
		$cache[3] = $col['it_idx'];
	}

	$group0[$col['cug_name']][$col['cus_code']][$col['booked_idx']][$col['it_idx']] = 1;
}
/*
echo "<pre>";
var_dump($sql);
echo "</pre>";
exit;
*/
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
			<th width="18%" colspan="2">DELI NO</th>
			<th width="13%">INV. NO</th>
			<th width="12%">DO DATE</th>
			<th width="20%">MODEL NO</th>
			<th width="7%">QTY<br>(EA)</th>
			<th width="13%">REMARK</th>
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
			cell('<input type="checkbox" name="chkDO[]" value="'.$rd[$rdIdx][5].'">', ' valign="top" align="center" rowspan="'.$rowSpan.'"');		//Checkbox
			cell_link("<span class=\"bar\">".$rd[$rdIdx][6]."</span>", ' align="center" valign="top" rowspan="'.$rowSpan.'"',
					' href="'.$rd[$rdIdx][13].'"');				//Booking code
			cell($rd[$rdIdx][3], ' valign="top" align="center" rowspan="'.$rowSpan.'"');		//Document ref code
			cell($rd[$rdIdx][4], ' valign="top" align="center" rowspan="'.$rowSpan.'"');		//Issued date

			$inv_total = 0;
			$print_tr_3 = 0;
			//ITEM LIST
			foreach($group3 as $group4) {
				if($print_tr_3++ > 0) print "<tr>\n";

				cell($rd[$rdIdx][10],' valign="top"');									//Model no
				cell(number_format((double)$rd[$rdIdx][11],2), ' valign="top" align="right"');	//Qty
				cell($rd[$rdIdx][12],' valign="top"');				 					//Remark
				print "</tr>\n";

				$inv_total += $rd[$rdIdx][11]; //qty
				$rdIdx++;
			}
			print "<tr>\n";
			cell("INVOICE TOTAL", ' colspan="5" align="right" style="color:darkblue;"');
			cell(number_format((double)$inv_total,2), ' align="right" style="color:darkblue;"');
			print "</tr>\n";
	
			$cus_total += $inv_total;
			$numInvoice++;
		}
	}
	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="6" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$cus_total,2), ' align="right" style="color:brown; background-color:lightyellow"');
	cell("&nbsp", ' style="background-color:lightyellow" colspan="3"');
	print "</tr>\n";
	print "</table><br />\n";

	$g_total += $cus_total;
}

print <<<END
<table width="100%" class="table_layout">
	<tr>
		<td><input type="checkbox" name="chkAll" onclick="checkAll(this.checked)"><span class="comment">check all</span></td>
		<td align="right"><button name='btnSummarize' class='input_btn' style='width:130px;' onclick="summarizeDO()"><img src="../../_images/icon/list.gif" width="20px" align="middle"> &nbsp; Summarize</button></td>
	</tr>
</table><br />
END;

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th>CUSTOMER</th>
			<th width="18%" colspan="2">DELI NO</th>
			<th width="13%">INV. NO</th>
			<th width="12%">DO DATE</th>
			<th width="20%">MODEL NO</th>
			<th width="7%">QTY<br>(EA)</th>
			<th width="13%">REMARK</th>
		</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="6" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$g_total,2), ' align="right" style="color:brown; background-color:lightyellow"');
cell("&nbsp", ' style="background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>