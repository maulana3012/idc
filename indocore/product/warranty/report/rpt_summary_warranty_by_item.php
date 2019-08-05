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
if($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp[] = "icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if($cboSearchType != '' && $txtSearch != '') {
	$type = array("byCity"=>"wr_cus_city", "byAddress"=>"wr_cus_address", "byModelNo"=>"it_model_no", "byWarrantyNo"=>"wr_warranty_no", "byStore"=>"wr_purchase_store");
	$tmp[] = $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
}

if ($some_date != "") {
	$tmp[] = "wr_purchase_date = DATE '$some_date'";
} else {
	$tmp[] = "wr_purchase_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
}

$strWhere = implode(" AND ", $tmp);

$sql = "
SELECT 
	icat_pidx,
	icat_midx,
	a.it_code,
	it_model_no,
	wr_idx,
	wr_warranty_no,
	wr_serial_no,
	wr_purchase_date,
	wr_cus_name,
	wr_cus_phone,
	wr_cus_hphone,
	wr_cus_address,
	'detail_warranty.php?_code='||wr_idx AS go_page
FROM 
	".ZKP_SQL."_tb_warranty AS a 
	JOIN ".ZKP_SQL."_tb_item AS b ON(a.it_code = b.it_code) JOIN ".ZKP_SQL."_tb_item_cat USING (icat_midx)
WHERE ". $strWhere ."
ORDER BY icat_midx, a.it_code, wr_purchase_date
";

// raw data
$rd		= array();
$rdIdx	= 0;
$cache	= array("","","");
$group0 = array();

$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'],				//0
		$col['it_code'],				//1
		$col['it_model_no'],			//2
		$col['wr_serial_no'],			//3		
		$col['wr_purchase_date'],		//4
		$col['wr_cus_name'],			//5
		$col['wr_cus_phone'],			//6
		$col['wr_cus_hphone'],			//7
		$col['wr_cus_address'],			//8
		$col['go_page'],				//9
		$col['wr_warranty_no'],			//10
		$col['wr_idx']					//11
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

	if($cache[2] != $col['wr_idx']) {
		$cache[2] = $col['wr_idx'];
	}
	
	$group0[$col['icat_midx']][$col['it_code']][$col['wr_idx']] = 1;
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
			<th width="10%">MODEL</th>
			<th width="10%">WARRANTY NO</th>
			<th width="8%">SERIAL NO</th>
			<th width="8%">DATE</th>
			<th width="15%">CUSTOMER NAME</th>
			<th width="10%">HP</th>
			<th width="10%">TELP</th>
			<th>ALAMAT</th>
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
		cell('['.trim($rd[$rdIdx][1]).'] '.$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');

		$model_total = 0;
		$print_tr_2 = 0;
		//ORDER
		foreach($group2 as $total3 => $group3) {
			if($print_tr_2++ > 0) print "<tr>\n";
			cell_link('<span style="color:blue">'.$rd[$rdIdx][10].'</span>', ' ', ' href="'.$rd[$rdIdx][9].'" ');
			cell($rd[$rdIdx][3]);
			cell(date('d-M-y', strtotime($rd[$rdIdx][4])), ' align="center"');
			cell($rd[$rdIdx][5]);
			cell($rd[$rdIdx][7]);
			cell($rd[$rdIdx][6]);
			cell($rd[$rdIdx][8]);
			cell("1", ' align="right"');

			$model_no = $rd[$rdIdx][2];
			$model_total++;
			$rdIdx++;
		}

		print "<tr>\n";
		cell($model_no, ' colspan="7" align="right" style="color:darkblue"');
		cell($model_total, ' align="right" style="color:darkblue"');
		print "</tr>\n";

		$cat_total += $model_total;
	}

	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="8" align="right" style="color:brown; background-color:lightyellow"');
	cell($cat_total, ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$grand_total += $cat_total;
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_f">
	<tr>
		<th width="10%">MODEL</th>
		<th width="10%">WARRANTY NO</th>
		<th width="8%">SERIAL NO</th>
		<th width="8%">DATE</th>
		<th width="15%">CUSTOMER NAME</th>
		<th width="10%">HP</th>
		<th width="10%">TELP</th>
		<th>ALAMAT</th>
		<th width="5%">QTY</th>
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="8" align="right" style="color:brown; background-color:lightyellow"');
cell($grand_total, ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>