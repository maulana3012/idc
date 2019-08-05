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
$tmp	= array();

if($_code != "") {
	$_code = explode(",", $_code);
	$_code = '$$' . implode('$$,$$', $_code) . '$$';
	$tmp[]   = "it_code IN ({$_code})";
}

if ($some_date != "") {
	$tmp[]   = "out_issued_date = DATE '$some_date'";
} else {
	$tmp[]   = "out_issued_date BETWEEN DATE '$period_from' AND '$period_to'";
}

$tmp[] = "bor_from_wh = $_loc";
$tmp[] = "bor_from_type = $_type";
$tmp[] = "bor_is_returned is false";
$strWhere	= implode(" AND ", $tmp);

$sql = "
SELECT
  icat_pidx AS icat_pidx,
  icat_midx AS icat_midx,
  it_code,
  it_model_no,
  c.cus_code,
  cus_full_name,
  out_idx,
  out_cfm_timestamp AS cfm_timestamp,
  'D'||substr(out_doc_ref,2) AS document_no,
  to_char(out_issued_date,'dd-Mon-yy') AS document_date,
  to_char(out_cfm_timestamp,'dd-Mon-yy') AS document_cfm_date,
  to_char(bor_return_timestamp,'dd-Mon-yy') AS return_date,
  bor_idx,
  bor_to_wh,
  bor_to_type,
  CASE
  	WHEN bor_from_type=1 THEN 'VAT'
  	WHEN bor_from_type=2 THEN 'NON'
  END AS type_item,
  bor_qty AS qty,
  bor_is_returned,
  it_ed,
  '../delivery/detail_do.php?_code='||out_idx AS go_page
FROM
  ".ZKP_SQL."_tb_customer AS c
  JOIN ".ZKP_SQL."_tb_outgoing USING(cus_code)
  JOIN ".ZKP_SQL."_tb_borrow USING(out_idx)
 JOIN ".ZKP_SQL."_tb_item AS d USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS e USING(icat_midx)
WHERE $strWhere ORDER BY  icat_pidx, icat_midx, it_code, cfm_timestamp";

echo "<pre>";
//var_dump($sql);
echo "</pre>";

// raw data
$rd = array();
$rdIdx = 0;
$cache = array("","","");
$group0 = array();
$items = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'],			//0
		$col['it_code'],			//1
		$col['it_model_no'],		//2
		$col['cus_code'],			//3
		$col['cus_full_name'], 		//4
		$col['out_idx'],			//5
		$col['document_no'],		//6
		$col['document_date'], 		//7
		$col['document_cfm_date'],	//8
		$col['return_date'],		//9
		$col['bor_idx'], 			//10
		$col['type_item'], 			//11
		$col['qty'], 				//12
		$col['bor_is_returned'],	//13
		$col['it_ed'], 				//14
		$col['bor_to_wh'],	 		//15
		$col['bor_to_type'], 		//16
		$col['go_page']				//17
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

	if($cache[2] != $col['bor_idx']) {
		$cache[2] = $col['bor_idx'];
	}

	$group0[$col['icat_midx']][$col['it_code']][$col['bor_idx']] = 1;
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
		<tr height="30px">
			<th width="18%">MODEL NO</th>
			<th width="18%" colspan="2">DELIVERY NO</th>
			<th width="8%">CONFIRM DATE</th>
			<th width="8%">RETURN DATE</th>
			<th>CUSTOMER</th>
			<th width="9%">BORROW FROM</th>
			<th width="8%">QTY<br />(Pcs)</th>
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
		print "\t<td valign=\"top\" rowspan=\"".$rowSpan."\">\n";
		print "\t\t[".trim($rd[$rdIdx][1])."] ".$rd[$rdIdx][2]."&nbsp;\n";
		if($rd[$rdIdx][14]=='t') {
			print "\t\t<a href=\"javascript:insertED('".$rd[$rdIdx][1]."','".$rd[$rdIdx][2]."',".$rd[$rdIdx][15].")\"><img src=\"../../_images/icon/add.gif\" alt=\"Add E/D information\" width=\"12px\"></a>\n";
		}
		print "\t</td>\n";

		$model_total	= 0;
		$print_tr_2		= 0;
		//BORROW
		foreach($group2 as $total3 => $group3) {

			if($print_tr_2++ > 0) print "<tr>\n";
			if($rd[$rdIdx][13]=='t') {
				cell('<input type="checkbox" name="chkBorIdx[]" value="'.$rd[$rdIdx][10].'" disabled>', ' width="3%" valign="top" align="center"');
			} else {
				cell('<input type="checkbox" name="chkBorIdx[]" value="'.$rd[$rdIdx][10].'">', ' width="3%" valign="top" align="center"');
			}
			print "\t<td align=\"center\">\n";
			print "\t\t<input type=\"hidden\" name=\"z_it_code[]\" value=\"".$rd[$rdIdx][1]."\">\n";
			print "\t\t<input type=\"hidden\" name=\"_it_model_no[]\" value=\"".$rd[$rdIdx][2]."\">\n";
			print "\t\t<input type=\"hidden\" name=\"_it_ed[]\" value=\"".$rd[$rdIdx][14]."\">\n";
			print "\t\t<input type=\"hidden\" name=\"_it_loc[]\" value=\"".$rd[$rdIdx][15]."\">\n";
			print "\t\t<input type=\"hidden\" name=\"_it_type[]\" value=\"".$rd[$rdIdx][16]."\">\n";
			print "\t\t<input type=\"hidden\" name=\"_it_qty[]\" value=\"".$rd[$rdIdx][12]."\">\n";
			print "\t\t<input type=\"hidden\" name=\"_document_no[]\" value=\"".$rd[$rdIdx][6]."\">\n";
			print "\t\t<a href=\"".$rd[$rdIdx][17]."\"><span class=\"bar\">".trim($rd[$rdIdx][6])."</a>&nbsp;\n";
			print "\t</td>\n";											//document no
			cell($rd[$rdIdx][8], ' align="center"');					//confirm date
			cell($rd[$rdIdx][9], ' align="center"');					//return date
			cell("[".trim($rd[$rdIdx][3])."] ".$rd[$rdIdx][4]);			//customer
			cell($rd[$rdIdx][11], ' align="center"');					//type item
			if($rd[$rdIdx][13]=='t') {
				cell(number_format($rd[$rdIdx][12],2), ' style="color:#006DA5" align="right"');	//qty
			} else {
				cell(number_format($rd[$rdIdx][12],2), ' align="right"');	//qty
			}
			print "</tr>\n";

			$model_total += $rd[$rdIdx][12];
			$model_no	 = '['. trim($rd[$rdIdx][1]) .'] '.$rd[$rdIdx][2];
			$it = $rd[$rdIdx][1];
			$rdIdx++;
		}

		print "<tr>\n";
		cell($model_no, ' colspan="6" align="right" style="color:darkblue"');
		cell(number_format($model_total,2), ' align="right" style="color:darkblue"');
		print "</tr>\n";
		$items[] = $it;
		$cat_total += $model_total;
	}
	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="7" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($cat_total,2), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$grand_total += $cat_total;
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_f">
	<tr height="30px">
		<th width="18%">MODEL NO</th>
		<th width="18%" colspan="2">DELIVERY NO</th>
		<th width="8%">CONFIRM DATE</th>
		<th width="8%">RETURN DATE</th>
		<th>CUSTOMER</th>
		<th width="8%">BORROW<br />FROM</th>
		<th width="8%">QTY<br />(Pcs)</th>
	</tr>\n
END;
print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="7" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total,2), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br />\n";
?>