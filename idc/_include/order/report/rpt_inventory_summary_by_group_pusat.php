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
if ($_cug_name != 'all') {
	$having = "HAVING cug_name = '$_cug_name'";
} else {
	$having = "";
}

if($_cus_code != "") {
	$tmp[] = "cus_code = '$_cus_code'";
}

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

$tmp[]	= "inv_dept = '$department'";

$strWhere  = implode(" AND ", $tmp);

$sql ="
SELECT
  cug.cug_name,
  icat_midx,
  sum(inv_ok) AS ok,
  sum(inv_oo) AS oo,
  sum(inv_ok + inv_oo) AS sum_ok_oo,
  sum(inv_jk) AS jk,
  sum(inv_jo) AS jo,
  sum(inv_jk + inv_jo) AS sum_jk_jo,
  sum((inv_ok + inv_oo) - (inv_jk + inv_jo)) AS pending_deli,
  sum(inv_return) AS return,
  sum(inv_sales) AS sales
FROM
  ".ZKP_SQL."_tb_customer_group as cug
  JOIN ".ZKP_SQL."_tb_customer USING(cug_code)
  JOIN ".ZKP_SQL."_tb_apotik_inv USING(cus_code)
  JOIN ".ZKP_SQL."_tb_item USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE $strWhere
GROUP BY cug.cug_name, icat_midx ".
$having ."
ORDER BY cug_name, icat_midx";

// raw data
$rd 		= array();
$rdIdx		= 0;
$cache		= array("","","","");
$category	= array();
$group0		= array();
$res		=& query($sql);
while($col =& fetchRowAssoc($res)) {

	if(!isset($category[$col['icat_midx']])) {
		//get category path from current icat_midx.
		$path = executeSP(ZKP_SQL."_getCategoryPath", $col['icat_midx']);
		eval(html_entity_decode($path[0]));	
		$path = array_reverse($path);
		$category[$col['icat_midx']] = $path[1][4]." > ".$path[2][4]." > ".$path[3][4] ;
	}

	$rd[] = array(
		$col['cug_name'],				//0
		$category[$col['icat_midx']],	//1
		$col['ok'],						//2
		$col['oo'],						//3
		$col['sum_ok_oo'],				//4
		$col['jk'],						//5
		$col['jo'],						//6
		$col['sum_jk_jo'],				//7
		$col['pending_deli'],			//8
		$col['return'],					//9
		$col['sales'],					//10
		$col['jk'] + $col['jo'] - $col['return'] - $col['sales']	//11
	);

	//1st grouping
	if($cache[0] != $col['cug_name']) {
		$cache[0] = $col['cug_name'];
		$group0[$col['cug_name']] = array();
	}

	if($cache[1] != $col['icat_midx']) {
		$cache[1] = $col['icat_midx'];
	}

	$group0[$col['cug_name']][$col['icat_midx']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//PUSAT TOTAL
$pusat_total = array(2=>0,0,0,0,0,0,0,0,0,0);

//PUSAT
foreach ($group0 as $group1) {

	echo "<span class=\"comment\"><b> PUSAT: ". $rd[$rdIdx][0]. "</b></span>\n";
	print <<<END
	<table width="100%" class="table_cc">
		<tr>
			<th rowspan="2">CATEGORY</th>
			<th width="12%" colspan="2">ORDER</th>
			<th rowspan="2" width="6%">TOTAL<br />ORDER</th>
			<th width="12%" colspan="2">CFM ORDER</th>
			<th rowspan="2" width="6%">TOTAL<br />CFM</th>
			<th rowspan="2" width="6%">PENDING</th>
			<th rowspan="2" width="6%">RETURN</th>
			<th rowspan="2" width="6%">SALES</th>
			<th rowspan="2" width="6%">STOCK</th>
		</tr>
		<tr>
			<th width="6%">OK</th>
			<th width="6%">OO</th>
			<th width="6%">JK</th>
			<th width="6%">JO</th>
		</tr>\n
END;

	$category_total = array(2=>0,0,0,0,0,0,0,0,0,0);

	//ITEM CATEGORY
	foreach($group1 as $group2) {
		print "<tr>\n";
		cell($rd[$rdIdx][1]);
		cell(number_format((double)$rd[$rdIdx][2]), ' align="right"');	//OK
		cell(number_format((double)$rd[$rdIdx][3]), ' align="right"');	//OO
		cell(number_format((double)$rd[$rdIdx][4]), ' align="right" ');	//OK + OO
		cell(number_format((double)$rd[$rdIdx][5]), ' align="right" ');	//JK
		cell(number_format((double)$rd[$rdIdx][6]), ' align="right"');	//JO
		cell(number_format((double)$rd[$rdIdx][7]), ' align="right" ');	//JO + JK
		cell(number_format((double)$rd[$rdIdx][8]), ' align="right" ');	//PENDING
		cell(number_format((double)$rd[$rdIdx][9]), ' align="right"');	//RETURN
		cell(number_format((double)$rd[$rdIdx][10]), ' align="right"');	//SALES
		cell(number_format((double)$rd[$rdIdx][11]), ' align="right"');	//STOCK
		print "</tr>\n";

		for ($i=2; $i<12; $i++)
			$category_total[$i] += $rd[$rdIdx][$i];

		$rdIdx++;
	}
	
	print "<tr>\n";
	cell("<b>CATEGORY TOTAL</b>", ' align="right" style="color:brown; background-color:lightyellow"');	
	for ($i=2; $i<12; $i++)
		cell(number_format((double)$category_total[$i]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	for ($i=2; $i<12; $i++)
		$pusat_total[$i] += $category_total[$i];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_cc">
	<tr>
		<th rowspan="2">CATEGORY</th>
		<th width="12%" colspan="2">ORDER</th>
		<th rowspan="2" width="6%">TOTAL<br />ORDER</th>
		<th width="12%" colspan="2">CFM ORDER</th>
		<th rowspan="2" width="6%">TOTAL<br />CFM</th>
		<th rowspan="2" width="6%">PENDING</th>
		<th rowspan="2" width="6%">RETURN</th>
		<th rowspan="2" width="6%">SALES</th>
		<th rowspan="2" width="6%">STOCK</th>
	</tr>
	<tr>
		<th width="6%">OK</th>
		<th width="6%">OO</th>
		<th width="6%">JK</th>
		<th width="6%">JO</th>
	</tr>\n
END;
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' align="right" style="color:brown; background-color:lightyellow"');	
for ($i=2; $i<12; $i++)
	cell(number_format((double)$pusat_total[$i]), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br />\n";
?>