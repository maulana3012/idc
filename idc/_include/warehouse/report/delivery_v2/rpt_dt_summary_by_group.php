<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim
*
* $_search_date : Inquire Date
*/
$tmp = array();

//SET WHERE PARAMETER
if ($some_date != "") {
	$tmp[] = "out_issued_date = DATE '$some_date'";
} else {
	$tmp[] = "out_issued_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
}

if($_cus_code != "") {
	$tmp[] = "cus_code = '$_cus_code'";
}

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if ($_dept != "all") {
	$tmp[] = "out_dept = '$_dept'";
}

$tmp[] = "out_doc_type = 'DT'";
$strWhere = implode(" AND ", $tmp);

$sql = "
SELECT
 cus_code,
 cus_full_name,
 cus_full_name,
 TRIM(out_doc_ref) AS dt_code,
 to_char(out_issued_date, 'dd-Mon-YY') AS dt_doc_date,
 otst_idx AS idx,
 it_code,
 it_model_no,
 it_desc,
 otst_qty AS dt_qty,
 ".ZKP_SQL."_getrdtwhqty(TRIM(out_doc_ref), it_code, 'stock') AS return_stock_qty,
 ".ZKP_SQL."_getrdtwhqty(TRIM(out_doc_ref), it_code, 'demo') AS return_demo_qty,
 ".ZKP_SQL."_getrdtwhqty(TRIM(out_doc_ref), it_code, 'reject') AS return_reject_qty,
 ".ZKP_SQL."_getrdtwh(TRIM(out_doc_ref), it_code, 'code') AS last_rt_code,
 ".ZKP_SQL."_getrdtwh(TRIM(out_doc_ref), it_code, 'date') AS last_rt_date,
 'detail_do.php?_code='||(select out_idx::text from ".ZKP_SQL."_tb_outgoing_v2 where out_doc_ref=out.out_doc_ref) || '&_source=v2' AS go_page
FROM
 ".ZKP_SQL."_tb_customer AS cus
 JOIN ".ZKP_SQL."_tb_outgoing_v2 AS out USING (cus_code)
 JOIN ".ZKP_SQL."_tb_outgoing_stock_v2 USING (out_idx)
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE " . $strWhere . "
ORDER BY out_issued_date, out_doc_ref, it_code";
/*
echo "<pre>";
echo $sql;
exit;
*/
// raw data
$rd = array();
$rdIdx = 0;
$cache = array("","","","");
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['cus_code'],		//0
		$col['cus_full_name'],	//1
		$col['dt_code'],		//2
		$col['dt_doc_date'],	//3
		$col['request_by'],		//4
		$col['idx'],			//5
		$col['it_code'],		//6
		$col['it_model_no'],	//7
		$col['it_desc'],		//8
		$col['dt_qty'],			//9
		$col['return_stock_qty'],		//10
		$col['return_demo_qty'],		//11
		$col['return_reject_qty'],		//12
		$col['last_rt_code'],	//13
		$col['last_rt_date'],	//14
		$col['go_page']			//15
	);

	//1st grouping
	if($cache[0] != $col['dt_code']) {
		$cache[0] = $col['dt_code'];
		$group0[$col['dt_code']] = array();
	}

	if($cache[1] != $col['idx']) {
		$cache[1] = $col['idx'];
	}

	$group0[$col['dt_code']][$col['idx']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$ggTotal	= array(0,0,0,0,0);	// DT QTY, RETURN STOCK, DEMO, REJECT, BAL

//
print <<<END
<table width="100%" class="table_f">
	<tr>
		<th width="10%" rowspan="2">DT NO.</th>
		<th width="8%" rowspan="2">DT DATE</th>
		<th WIDTH="20%" rowspan="2">CUSTOMER</th>
		<th rowspan="2">MODEL NO</th>
		<th width="5%" rowspan="2">DT QTY<br />(Pcs)</th>
		<th width="12%" colspan="3">RT QTY</th>
		<th width="5%" rowspan="2">BAL<br />(Pcs)</th>
		<th width="10%" rowspan="2">Last RT No.</th>
		<th width="8%" rowspan="2">Last RT Date</th>
	</tr>\n
	<tr>
		<th width="4%">STOCK</th>
		<th width="4%">DEMO</th>
		<th width="4%">REJECT</th>
	</tr>
END;

foreach ($group0 as $total1 => $group1) {
	$rowSpan = 0;
	array_walk_recursive($group1, 'getRowSpan');

	print "<tr>\n";
	cell_link("<span class=\"bar\">".$rd[$rdIdx][2]."</span>", ' align="center" valign="top" rowspan="'.$rowSpan.'"',
		' href="'.$rd[$rdIdx][15].'"');													//DT No
	cell($rd[$rdIdx][3], ' valign="top" align="center" rowspan="'.$rowSpan.'"');		//DT Date
	cell("[".trim($rd[$rdIdx][0])."] ".$rd[$rdIdx][1], ' valign="top" rowspan="'.$rowSpan.'"');						//Customer to

	$total		= array(0,0,0,0,0); // DT QTY, RETURN STOCK, DEMO, REJECT, BAL
	$print_tr_1 = 0;
	//ITEM
	foreach($group1 as $group2) {
		if($print_tr_1++ > 0) print "<tr>\n";
		$bal_qty		= $rd[$rdIdx][9]- ($rd[$rdIdx][10]+$rd[$rdIdx][11]+$rd[$rdIdx][12]);

		cell("[".trim($rd[$rdIdx][6])."] ".$rd[$rdIdx][7]);						//Model No
		cell(number_format($rd[$rdIdx][9],1), ' align="right"');				//DT qty
		cell(($rd[$rdIdx][10] == 0) ? "" : number_format($rd[$rdIdx][10],1), ' align="right"');	//RT qty STOCK
		cell(($rd[$rdIdx][11] == 0) ? "" : number_format($rd[$rdIdx][11],1), ' align="right"');	//RT qty DEMO
		cell(($rd[$rdIdx][12] == 0) ? "" : number_format($rd[$rdIdx][12],1), ' align="right"');	//RT qty REJECT
		if($bal_qty < 0)
			cell(number_format($bal_qty,1), ' align="right" style="color:red"');
		else cell(number_format($bal_qty,1), ' align="right"');		//BALANCE
		cell($rd[$rdIdx][13], ' align="center"');					//last RT code
		cell($rd[$rdIdx][14], ' align="center"');					//last RT Date
		print "</tr>\n";

		$total[0] += $rd[$rdIdx][9];
		$total[1] += $rd[$rdIdx][10];
		$total[2] += $rd[$rdIdx][11];
		$total[3] += $rd[$rdIdx][12];
		$total[4] += $bal_qty;
		$rdIdx++;
	}

	print "<tr>\n";
	cell("TOTAL $total1", ' colspan="4" align="right" style="color:darkblue"');
	cell(number_format($total[0],1), ' align="right" style="color:darkblue"');
	cell(number_format($total[1],1), ' align="right" style="color:darkblue"');
	cell(number_format($total[2],1), ' align="right" style="color:darkblue"');
	cell(number_format($total[3],1), ' align="right" style="color:darkblue"');
	cell(number_format($total[4],1), ' align="right" style="color:darkblue"');
	cell('');
	cell('');
	print "</tr>\n";

	$ggTotal[0] += $total[0];
	$ggTotal[1] += $total[1];
	$ggTotal[2] += $total[2];
	$ggTotal[3] += $total[3];
	$ggTotal[4] += $total[4];
}
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="4" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTotal[0],1), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTotal[1],1), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTotal[2],1), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTotal[3],1), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTotal[4],1), ' align="right" style="color:brown; background-color:lightyellow"');
cell('', ' style="color:brown; background-color:lightyellow"');
cell('', ' style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br />\n";
?>