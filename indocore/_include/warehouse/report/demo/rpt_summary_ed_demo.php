<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim
*
*
*/
$display_css['expired']		= "background-color:#F3F3F3;color:#333333";
$display_css['available']	= "";

//SET WHERE PARAMETER
$tmp = array();

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

$tmp[] = "it.it_ed	= 't'";
$tmp[] = "d.d_qty	> 0";

$strWhere = implode(" AND ", $tmp);

$sql = "
SELECT
  icat.icat_pidx AS icat_pidx,
  icat.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no,
  d.d_expired_date AS expired_date,
  ".ZKP_SQL."_getRemainMonth(d.d_expired_date) AS remain_month,
  SUM(d.d_qty) AS total_qty
FROM
  ".ZKP_SQL."_tb_expired_demo AS d
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE ". $strWhere ."
GROUP BY icat.icat_pidx, icat.icat_midx, it.it_code, it.it_model_no, d.d_expired_date
ORDER BY icat_pidx, icat_midx, it_code, expired_date
";

// raw data
$rd		= array();
$rdIdx	= 0;
$cache	= array("","","");
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'],		//0
		$col['it_code'],		//1
		$col['it_model_no'],	//2
		$col['expired_date'],	//3
		$col['remain_month'],	//4
		$col['total_qty']		//5
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

	if($cache[2] != $col['expired_date']) {
		$cache[2] = $col['expired_date'];
	}

	$group0[$col['icat_midx']][$col['it_code']][$col['expired_date']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$gg_total = 0;

//GROUP
foreach ($group0 as $total1 => $group1) {

	//get category path from current icat_midx.
	$path = executeSP(ZKP_SQL."_getCategoryPath", $rd[$rdIdx][0]);
	eval(html_entity_decode($path[0]));	
	$path = array_reverse($path);
	$total1 = $path[1][4]." > ".$path[2][4]." > ".$path[3][4] ;

	echo "<span class=\"comment\"><b> CATEGORY: ". $total1. "</b></span>\n";
	print <<<END
	<table width="70%" class="table_l">
		<tr>
			<th width="12%">MODEL NO</th>
			<th width="10%">EXPIRED DATE</th>
			<th width="5%">M/S<br />(month/s)</th>
			<th width="6%">QTY<br>(Pcs)</th>
			<td width="5%"></td>
		</tr>\n
END;
	$g_total = 0;
	$print_tr_1 = 0;
	print "<tr>\n";
	//ITEM CODE
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".trim($rd[$rdIdx][1])."] {$rd[$rdIdx][2]}", ' valign="top" rowspan="'.$rowSpan.'"');	//MODEL NO

		$total		= 0;
		$print_tr_2 = 0;
		//EXPIRED DATE
		foreach($group2 as $total3 => $group3) {

			$it_code = trim($rd[$rdIdx][1]);
			if($it_code=='2101' || $it_code=='2101NE' || $it_code=='2200') {
				//unchategorized items
				$status = ($rd[$rdIdx][4]<4) ? 'expired':'available';
			} else if($rd[$rdIdx][0]==99 || $rd[$rdIdx][0]==100 || $rd[$rdIdx][0]==101 || $rd[$rdIdx][0]==103 || $rd[$rdIdx][0]==104 || $rd[$rdIdx][0]==105 || $rd[$rdIdx][0]==106 || $rd[$rdIdx][0]==107) {
				//sekisui
				$status = ($rd[$rdIdx][4]<2) ? 'expired':'available';
			} else if($rd[$rdIdx][0]==16) {
				//optima
				$status = ($rd[$rdIdx][4]<12) ? 'expired':'available';
			}

			if($print_tr_2++ > 0) print "<tr>\n";
			cell(date('F Y', strtotime($rd[$rdIdx][3])), ' style="'.$display_css[$status].'" valign="top"');		//expired date
			cell(date($rd[$rdIdx][4]), ' style="'.$display_css[$status].'" valign="top" align="center"');			//remain month
			cell(number_format($rd[$rdIdx][5],2), ' style="'.$display_css[$status].'" valign="top" align="right"');	//qty
			/*if($status=='expired') {
				print "\t<td align=\"center\"><a href=\"javascript:deleteItem('".trim($rd[$rdIdx][1])."','{$rd[$rdIdx][3]}','{$rd[$rdIdx][5]}')\" style=\"font-family:MS Mincho;background-color:lightyellow\"> &nbsp;delete </a></td>\n"; 
			}*/
			print "</tr>\n";

			$total		+= $rd[$rdIdx][5];
			$model_name = $rd[$rdIdx][2];
			$rdIdx++;
		}
		print "<tr>\n";
		cell("[".trim($total2)."] $model_name", ' colspan="2" align="right" style="color:darkblue;"');
		cell(number_format($total,2), ' align="right" style="color:darkblue;"');
		print "</tr>\n";

		$g_total += $total;
	}
	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="3" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($g_total), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$gg_total += $g_total;
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="61%" class="table_l">
	<tr>
		<th width="12%">MODEL NO</th>
		<th width="10%">EXPIRED DATE</th>
		<th width="5%">M/S<br />(month/s)</th>
		<th width="6%">QTY<br>(Pcs)</th>
	</tr>\n
END;
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="3" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($gg_total,2), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>