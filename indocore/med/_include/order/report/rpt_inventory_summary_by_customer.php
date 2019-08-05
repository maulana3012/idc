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

$sql ="
SELECT
  it.icat_midx,
  it.it_code,
  it.it_model_no,
  inv.inv_ok,
  inv.inv_oo,
  (inv.inv_ok + inv.inv_oo) AS sum_ok_oo,
  inv.inv_jk,
  inv.inv_jo,
  (inv.inv_jk + inv.inv_jo) AS sum_jk_jo,
  (inv.inv_ok + inv.inv_oo) - (inv.inv_jk + inv.inv_jo) AS pending,
  inv.inv_return,
  inv.inv_sales,
  to_char(inv.inv_sales_updated,'dd-Mon-YY') AS sales_updated,
  (SELECT sum(deit_jo_qty)
	FROM ".ZKP_SQL."_tb_delivery_item AS deit
	WHERE
	  deit.it_code = it.it_code AND
	  deit.cus_code = inv.cus_code AND
	  deit.deit_date > inv.inv_sales_updated) AS additional_sales_qty
FROM
	".ZKP_SQL."_tb_item_cat AS icat
	JOIN ".ZKP_SQL."_tb_item AS it USING(icat_midx)
	JOIN ".ZKP_SQL."_tb_apotik_inv AS inv USING(it_code)
WHERE inv.cus_code = '{$_cus_to}' AND inv_dept = '$department'
ORDER BY icat_pidx, icat_midx, it_code";

// raw data
$rd		= array();
$rdIdx	= 0;
$cache	= array("","");
$group0 = array();
$res	=& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'],	//0
		$col['it_code'],	//1
		$col['it_model_no'],//2
		$col['inv_ok'],		//3
		$col['inv_oo'], 	//4
		$col['sum_ok_oo'],	//5
		$col['inv_jk'],		//6
		$col['inv_jo'],		//7 
		$col['sum_jk_jo'],	//8
		$col['pending'],	//9
		$col['inv_return'], //10
		$col['inv_sales'],	//11
		$col['inv_jk'] + $col['inv_jo'] - $col['inv_return'] - $col['inv_sales'], //12
		$col['additional_sales_qty'], //13
		$col['inv_jk'] + $col['inv_jo'] - $col['inv_return'] - $col['inv_sales'] - $col['additional_sales_qty'], //14
		$col['sales_updated'] //15
	);

	//1st grouping
	if($cache[0] != $col['icat_midx']) {
		$cache[0] = $col['icat_midx'];
		$group0[$col['icat_midx']] = array();
	}

	if($cache[1] != $col['it_code']) {
		$cache[1] = $col['it_code'];
	}

	$group0[$col['icat_midx']][$col['it_code']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//PUSAT TOTAL
$grand_total = array(3=>0,0,0,0,0,0,0,0,0,0,0,0);

//PUSAT
foreach ($group0 as $group1) {

	//get category path from current icat_midx.
	$path = executeSP(ZKP_SQL."_getCategoryPath", $rd[$rdIdx][0]);
	eval(html_entity_decode($path[0]));	
	$path = array_reverse($path);
	$category = $path[1][4]." > ".$path[2][4]." > ".$path[3][4];

	echo "<span class=\"comment\"><b>". $category . "</b></span>\n";
	print <<<END
	<table width="100%" class="table_cc">
		<tr>
			<th rowspan="2">CODE</th>
			<th rowspan="2">MODEL NO</th>
			<th colspan="2" width="10%">ORDER</th>
			<th rowspan="2" width="5%">TOTAL<br />ORDER</th>
			<th colspan="2" width="10%">CFM ORDER</th>
			<th rowspan="2" width="5%">TOTAL<br />CFM ORDER</th>
			<th rowspan="2" width="5%">PENDING</th>
			<th rowspan="2" width="5%">RETURN</th>
			<th colspan="2">SALES</th>
			<th rowspan="2" width="5%">STOCK</th>
			<th rowspan="2" width="5%">EST<br />SALES</th>
			<th rowspan="2" width="5%">EST<br />STOCK</th>
		</tr>
		<tr>
			<th width="5%">OK</th>
			<th width="5%">OO</th>
			<th width="5%">JK</th>
			<th width="5%">JO</th>
			<th width="5%">QTY</th>
			<th width="10%">LAST DATE</th>
		</tr>\n
END;

	$category_total = array(3=>0,0,0,0,0,0,0,0,0,0,0,0);

	//ITEM CATEGORY
	foreach($group1 as $group2) {
		print "<tr>\n";
		cell($rd[$rdIdx][1]);	//ITEM CODE
		cell($rd[$rdIdx][2]);	//ITEM MODEL NO
		cell(number_format((double)$rd[$rdIdx][3]), ' align="right"');	//OK
		cell(number_format((double)$rd[$rdIdx][4]), ' align="right"');	//OO
		cell(number_format((double)$rd[$rdIdx][5]), ' align="right" ');	//OK+OO
		cell(number_format((double)$rd[$rdIdx][6]), ' align="right" ');	//JK
		cell(number_format((double)$rd[$rdIdx][7]), ' align="right" ');	//JO
		cell(number_format((double)$rd[$rdIdx][8]), ' align="right" ');	//JK+JO
		cell(number_format((double)$rd[$rdIdx][9]), ' align="right"');	//PENDING
		cell(number_format((double)$rd[$rdIdx][10]), ' align="right"');	//RETURN
		cell(number_format((double)$rd[$rdIdx][11]), ' align="right"');	//QTY
		cell($rd[$rdIdx][15], ' align="center"');						//LAST DATE
		cell(number_format((double)$rd[$rdIdx][12]), ' align="right"');	//STOCK
		cell(number_format((double)$rd[$rdIdx][13]), ' align="right"');	//ESTIMATE SALES
		cell(number_format((double)$rd[$rdIdx][14]), ' align="right"');	//ESTIMATE STOCK
		print "</tr>\n";

		for ($i=3; $i<15; $i++)
			$category_total[$i] += $rd[$rdIdx][$i];

		$rdIdx++;
	}
	
	print "<tr>\n";
	cell("<b>{$category}</b>", ' colspan="2" align="right" style="color:brown; background-color:lightyellow"');	
	for ($i=3; $i<12; $i++)
		cell(number_format((double)$category_total[$i]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell("&nbsp;", ' style="background-color:lightyellow"');
	cell(number_format((double)$category_total[12]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$category_total[13]), ' align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$category_total[14]), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	for ($i=3; $i<15; $i++)
		$grand_total[$i] += $category_total[$i];
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
	<table width="100%" class="table_cc">
		<tr>
			<th rowspan="2">CODE</th>
			<th rowspan="2">MODEL NO</th>
			<th colspan="2" width="10%">ORDER</th>
			<th rowspan="2" width="5%">TOTAL<br />ORDER</th>
			<th colspan="2" width="10%">CFM ORDER</th>
			<th rowspan="2" width="5%">TOTAL<br />CFM ORDER</th>
			<th rowspan="2" width="5%">PENDING</th>
			<th rowspan="2" width="5%">RETURN</th>
			<th colspan="2">SALES</th>
			<th rowspan="2" width="5%">STOCK</th>
			<th rowspan="2" width="5%">EST<br />SALES</th>
			<th rowspan="2" width="5%">EST<br />STOCK</th>
		</tr>
		<tr>
			<th width="5%">OK</th>
			<th width="5%">OO</th>
			<th width="5%">JK</th>
			<th width="5%">JO</th>
			<th width="5%">QTY</th>
			<th width="10%">LAST DATE</th>
		</tr>\n
END;
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="2" align="right" style="color:brown; background-color:lightyellow"');	
for ($i=3; $i<12; $i++)
	cell(number_format((double)$grand_total[$i]), ' align="right" style="color:brown; background-color:lightyellow"');
cell("&nbsp;", ' style="background-color:lightyellow"');
cell(number_format((double)$grand_total[12]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[13]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$grand_total[14]), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br />\n";
?>