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
if ($_cug_code != 'all') {
	$tmp[] = "cug.cug_code = '$_cug_code'";
}

if ($_lastCategoryNo > 0) {
	$tmp[] = "icat_midx = $_lastCategoryNo";
}

$tmp[]	  = "inv_dept = '$department'";
$strWhere = implode(" AND ", $tmp);

$sql ="
SELECT
  it.it_code,
  it.it_model_no,
  it.it_desc,
  cug.cug_name,
  cus.cus_code,
  cus.cus_full_name,
  inv_ok,
  inv_oo,
  (inv_ok + inv_oo) AS sum_ok_oo,
  inv_jk,
  inv_jo,
  (inv_jk + inv_jo) AS sum_jk_jo,
  (inv_ok + inv_oo) - (inv_jk + inv_jo) AS pending,
  inv_return,
  inv_sales,
  (inv_jk + inv_jo - inv_return - inv_sales) AS stock
FROM
  ".ZKP_SQL."_tb_customer_group AS cug
    JOIN ".ZKP_SQL."_tb_customer AS cus USING(cug_code)
    JOIN ".ZKP_SQL."_tb_apotik_inv USING(cus_code)
    JOIN ".ZKP_SQL."_tb_item as it USING(it_code)
WHERE ".$strWhere."
ORDER BY it.it_code, cug.cug_code, cus.cus_code";

// raw data
$rd 	= array();
$rdIdx	= 0;
$cache	= array("","","","");
$group0 = array();
$res	=& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['it_code'],		//0
		$col['it_model_no'],	//1
		$col['it_desc'],		//2
		$col['cug_name'],		//3
		$col['cus_code'],		//4
		$col['cus_full_name'],	//5
		$col['inv_ok'],		//6
		$col['inv_oo'],		//7
		$col['sum_ok_oo'],	//8
		$col['inv_jk'],		//9
		$col['inv_jo'],		//10
		$col['sum_jk_jo'],	//11
		$col['pending'],	//12
		$col['inv_return'],	//13
		$col['inv_sales'],	//14
		$col['stock']		//15
		);	

	//1st grouping
	if($cache[0] != $col['it_code']) {
		$cache[0] = $col['it_code'];
		$group0[$col['it_code']] = array();
	}

	if($cache[1] != $col['cug_name']) {
		$cache[1] = $col['cug_name'];
		$group0[$col['it_code']][$col['cug_name']] = array();
	}

	if($cache[2] != $col['cus_code']) {
		$cache[2] = $col['cus_code'];
	}

	$group0[$col['it_code']][$col['cug_name']][$col['cus_code']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//Grand Total
$grand_total = array(6=>0,0,0,0,0,0,0,0,0,0);

//ITEM
foreach ($group0 as $model_no => $group1) {

	echo "<span class=\"comment\"><b> [".$rd[$rdIdx][0]."] ".$rd[$rdIdx][1]."&nbsp;&nbsp;".$rd[$rdIdx][2]."</b></span>\n";
	print <<<END
	<table width="100%" class="table_cc">
		<tr>
			<th rowspan="2" width="15%">PUSAT</th>
			<th rowspan="2" width="3%">CODE</th>
			<th rowspan="2">CUSTOMER</th>
			<th width="10%" colspan="2">ORDER</th>
			<th rowspan="2" width="5%">TOTAL<br />ORDER</th>
			<th width="10%" colspan="2">CFM ORDER</th>
			<th rowspan="2" width="5%">TOTAL<br />CFM</th>
			<th rowspan="2" width="5%">PEN-<br />DING</th>
			<th rowspan="2" width="5%">RTRN</th>
			<th rowspan="2" width="5%">SALES</th>
			<th rowspan="2" width="5%">STOCK</th>
		</tr>
		<tr>
			<th width="5%">OK</th>
			<th width="5%">OO</th>
			<th width="5%">JK</th>
			<th width="5%">JO</th>
		</tr>\n
END;

	$item_total = array(6=>0,0,0,0,0,0,0,0,0,0);
	$print_tr_1 = 0;

	//PUSAT
	foreach($group1 as $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan = count($group2) + 1; //each pusat has 1 total
	
		if($print_tr_1++ > 0) print "<tr>\n";
		cell($rd[$rdIdx][3], ' valign="top" rowspan="'.$rowSpan.'"');

		$pusat_total = array(6=>0,0,0,0,0,0,0,0,0,0);
		$print_tr_2 = 0;
		//CUSTOMER
		foreach($group2 as $group3) {
			if($print_tr_2++ > 0) print "<tr>\n";

			cell($rd[$rdIdx][4]);		//CUSTUMER CODE
			cell($rd[$rdIdx][5]);		//COSTUMER FULL NAME
			cell(number_format((double)$rd[$rdIdx][6]), ' align="right"');		//OK
			cell(number_format((double)$rd[$rdIdx][7]), ' align="right"');		//OO
			cell(number_format((double)$rd[$rdIdx][8]), ' align="right" ');		//OK+OO
			cell(number_format((double)$rd[$rdIdx][9]), ' align="right" ');		//JO
			cell(number_format((double)$rd[$rdIdx][10]), ' align="right" ');	//JK
			cell(number_format((double)$rd[$rdIdx][11]), ' align="right" ');	//JO+JK
			cell(number_format((double)$rd[$rdIdx][12]), ' align="right"');		//PENDING
			cell(number_format((double)$rd[$rdIdx][13]), ' align="right"');		//RETURN
			cell(number_format((double)$rd[$rdIdx][14]), ' align="right"');		//SALES
			cell(number_format((double)$rd[$rdIdx][15]), ' align="right"');		//STOCK
			print "</tr>\n";

			for ($i=6; $i<16; $i++)
				$pusat_total[$i] += $rd[$rdIdx][$i];
	
			$rdIdx++;
		}
		
		//PUSAT TOTAL
		print "<tr>\n";
		cell("SUB TOTAL", ' colspan="2" align="right" style="color:darkblue"');
		for ($i=6; $i<16; $i++)
			cell(number_format((double)$pusat_total[$i]), ' align="right" style="color:darkblue"');
		print "</tr>\n";

		for ($i=6; $i<16; $i++)
			$item_total[$i] += $pusat_total[$i];
	}

	//ITEM TOTAL
	print "<tr>\n";
	cell("<b>$model_no</b>", ' colspan="3" align="right" style="color:brown; background-color:lightyellow"');	
	for ($i=6; $i<16; $i++)
		cell(number_format((double)$item_total[$i]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	for ($i=6; $i<16; $i++)
		$grand_total[$i] += $item_total[$i];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_cc">
	<tr>
		<th rowspan="2" width="15%">PUSAT</th>
		<th rowspan="2" width="3%">CODE</th>
		<th rowspan="2">CUSTOMER</th>
		<th width="10%" colspan="2">ORDER</th>
		<th rowspan="2" width="5%">TOTAL<br />ORDER</th>
		<th width="10%" colspan="2">CFM ORDER</th>
		<th rowspan="2" width="5%">TOTAL<br />CFM</th>
		<th rowspan="2" width="5%">PEN-<br />DING</th>
		<th rowspan="2" width="5%">RTRN</th>
		<th rowspan="2" width="5%">SALES</th>
		<th rowspan="2" width="5%">STOCK</th>
	</tr>
	<tr>
		<th width="5%">OK</th>
		<th width="5%">OO</th>
		<th width="5%">JK</th>
		<th width="5%">JO</th>
	</tr>\n
END;
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="3" align="right" style="color:brown; background-color:lightyellow"');	
for ($i=6; $i<16; $i++)
	cell(number_format((double)$grand_total[$i]), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>