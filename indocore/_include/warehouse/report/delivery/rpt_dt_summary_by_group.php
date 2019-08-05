
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
if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp[] = "e.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if ($_cug_code != 'all') {
	$tmp[]		= "dt_cus_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
}

if ($some_date != "") {
	$tmp[] = "dt_date = DATE '$some_date'";
} else {
	$tmp[] = "dt_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
}

$strWhere = implode(" AND ", $tmp);

$sql = "
SELECT
 cus_code,
 cus_full_name,
 dt_code,
 dt_date,
 to_char(dt_date, 'dd-Mon-YY') AS dt_date,
 dt_issued_by AS request_by,
 dtit_idx,
 it_code,
 it_model_no,
 it_desc,
 dtit_qty,
 ".ZKP_SQL."_getRDTQty(dt_code, it_code, NULL) AS return_qty,
 ".ZKP_SQL."_getLastRTCode(dt_code, it_code) AS last_rt_code,
 to_char(".ZKP_SQL."_getLastRTDate(dt_code, it_code), 'dd-Mon-yy') AS last_rt_date,
 CASE
	WHEN dt_cfm_wh_delivery_timestamp is null then 'confirm_do.php?_code='||(select book_idx::text from ".ZKP_SQL."_tb_booking where book_code=dt_code) 
	WHEN dt_cfm_wh_delivery_timestamp is not null then 'detail_do.php?_code='||(select out_idx::text from ".ZKP_SQL."_tb_outgoing where out_code=dt_code) 
 END AS go_page,
 'confirm_return_dt.php?_inc_idx='|| (select inc_idx::text from ".ZKP_SQL."_tb_incoming where inc_doc_ref=".ZKP_SQL."_getLastRTCode(dt_code, it_code)) || '&_std_idx=' || (select std_idx::text from ".ZKP_SQL."_tb_outstanding where std_doc_ref=".ZKP_SQL."_getLastRTCode(dt_code, it_code)) AS go_return_page
FROM
 ".ZKP_SQL."_tb_customer AS cus
 JOIN ".ZKP_SQL."_tb_dt AS dt ON dt_cus_to = cus_code
 JOIN ".ZKP_SQL."_tb_dt_item AS dtit USING(dt_code) 
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS e USING(icat_midx)
WHERE " . $strWhere . "
ORDER BY dt_code DESC, it_code";

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
		$col['dt_date'],		//3
		$col['request_by'],		//4
		$col['dtit_idx'],		//5
		$col['it_code'],		//6
		$col['it_model_no'],	//7
		$col['it_desc'],		//8
		$col['dtit_qty'],		//9
		$col['return_qty'],		//10
		$col['last_rt_code'],	//11
		$col['last_rt_date'],	//12
		$col['go_page'],		//13
		$col['go_return_page']	//14
	);

	//1st grouping
	if($cache[0] != $col['dt_code']) {
		$cache[0] = $col['dt_code'];
		$group0[$col['dt_code']] = array();
	}

	if($cache[1] != $col['dtit_idx']) {
		$cache[1] = $col['dtit_idx'];
	}

	$group0[$col['dt_code']][$col['dtit_idx']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$ggTotal	= array(0,0,0);

//
print <<<END
<table width="100%" class="table_f">
	<tr>
		<th width="10%">DT NO.</th>
		<th width="8%">DT DATE</th>
		<th>CUSTOMER</th>
		<th width="10%">REQUEST BY</th>
		<th>MODEL NO</th>
		<th width="5%">DT QTY<br />(Pcs)</th>
		<th width="5%">RT QTY<br />(Pcs)</th>
		<th width="5%">BAL<br />(Pcs)</th>
		<th width="10%">Last RT No.</th>
		<th width="8%">Last RT Date</th>
	</tr>\n
END;

foreach ($group0 as $total1 => $group1) {
	$rowSpan = 0;
	array_walk_recursive($group1, 'getRowSpan');

	print "<tr>\n";
	cell_link("<span class=\"bar\">".$rd[$rdIdx][2]."</span>", ' align="center" valign="top" rowspan="'.$rowSpan.'"',
		' href="'.$rd[$rdIdx][13].'"');													//DT No
	cell($rd[$rdIdx][3], ' valign="top" align="center" rowspan="'.$rowSpan.'"');		//DT Date
	cell("[".trim($rd[$rdIdx][0])."] ".$rd[$rdIdx][1], ' valign="top" rowspan="'.$rowSpan.'"');						//Customer to
	cell($rd[$rdIdx][4], ' valign="top" rowspan="'.$rowSpan.'"');						//Request by

	$total		= array(0,0,0);
	$print_tr_1 = 0;
	//ITEM
	foreach($group1 as $group2) {
		if($print_tr_1++ > 0) print "<tr>\n";
		$bal_qty		= $rd[$rdIdx][9]-$rd[$rdIdx][10];

		cell("[".trim($rd[$rdIdx][6])."] ".$rd[$rdIdx][7]);						//Model No
		cell(number_format($rd[$rdIdx][9]), ' align="right"');					//DT qty
		cell(number_format($rd[$rdIdx][10]), ' align="right"');					//RT qty
		if($bal_qty < 0)
			cell(number_format($bal_qty), ' align="right" style="color:red"');
		else cell(number_format($bal_qty), ' align="right"');					//BALANCE
		cell_link("<span class=\"bar\">".$rd[$rdIdx][11]."</span>", ' align="center"', 
					' href="'.$rd[$rdIdx][14].'"');								//last RT code
		cell($rd[$rdIdx][12], ' align="center"');								//last RT Date
		print "</tr>\n";

		$total[0] += $rd[$rdIdx][9];
		$total[1] += $rd[$rdIdx][10];
		$total[2] += $bal_qty;
		$rdIdx++;
	}

	print "<tr>\n";
	cell("TOTAL $total1", ' colspan="5" align="right" style="color:darkblue"');
	cell(number_format($total[0]), ' align="right" style="color:darkblue"');
	cell(number_format($total[1]), ' align="right" style="color:darkblue"');
	cell(number_format($total[2]), ' align="right" style="color:darkblue"');
	cell('');
	cell('');
	print "</tr>\n";

	$ggTotal[0] += $total[0];
	$ggTotal[1] += $total[1];
	$ggTotal[2] += $total[2];
}
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTotal[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTotal[1]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($ggTotal[2]), ' align="right" style="color:brown; background-color:lightyellow"');
cell('', ' style="color:brown; background-color:lightyellow"');
cell('', ' style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br />\n";
?>