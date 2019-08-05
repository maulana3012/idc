<?php
$sql = "
SELECT
  icat_midx, icat_pidx,
  trim(it_code) AS it_code, it_model_no,
  log_code, log_type, log_wh_location, log_qty_value,
  to_char(log_cfm_timestamp, 'dd-Mon-yy hh:ii:ss') AS cfm_date,
  TRIM(log_document_no) AS doc_no,
  to_char(log_document_date, 'dd-Mon-yy') AS doc_date,
  log_document_type,
  log_qty AS qty,
  (select to_char(min(log_cfm_timestamp::date), 'dd-Mon-yy') from ".ZKP_SQL."_tb_log_detail where log_recap_timestamp is null) AS min_date,
  (select to_char(max(log_cfm_timestamp::date), 'dd-Mon-yy') from ".ZKP_SQL."_tb_log_detail where log_recap_timestamp is null) AS max_date
FROM
  ".ZKP_SQL."_tb_log_detail
  JOIN ".ZKP_SQL."_tb_item USING (it_code)
  JOIN ".ZKP_SQL."_tb_item_cat USING (icat_midx)
WHERE log_recap_timestamp is null
ORDER BY icat_pidx, it_code, log_cfm_timestamp, log_code
";

echo "<pre>";
#var_dump($sql);
echo "</pre>";

// raw data
$rd	= array();
$rdIdx	= 0;
$cache	= array("","","");
$group0 = array();
$log	= array();
$res	=& query($sql);
$numRow	= numQueryRows($res);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'],	//0
		$col['it_code'],	//1
		$col['it_model_no'],	//2
		$col['log_code'],	//3		
		$col['cfm_date'],	//4
		$col['doc_no'],		//5
		$col['doc_date'],	//6
		$col['log_document_type'], //7		
		$col['qty'],		//8
		$col['min_date'],	//9
		$col['max_date'],	//10
		$col['log_type'],	//11
		$col['log_wh_location'],//12
		$col['log_qty_value']	//13
		
		
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

	if($cache[2] != $col['log_code']) {
		$cache[2] = $col['log_code'];
	}

	$group0[$col['icat_midx']][$col['it_code']][$col['log_code']] = 1;
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
			<th width="15%">MODEL NO</th>
			<th width="10%">TIME</th>
			<th width="13%">DOCUMENT TYPE</th>
			<th width="13%">DOCUMENT NO</th>
			<th width="7%">DOCUMENT DATE</th>
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
		cell('['.trim($rd[$rdIdx][1]).'] '.$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"'); //Model No

		$model_total = 0;
		$print_tr_2 = 0;
		//LOG DETAIL
		foreach($group2 as $total3 => $group3) {
			if($print_tr_2++ > 0) print "<tr>\n";
			cell($rd[$rdIdx][4], ' align="center"');	// Confirm Date
			cell($rd[$rdIdx][7]);				// Confirm type
			cell($rd[$rdIdx][5]);				// Document no
			cell($rd[$rdIdx][6], ' align="center"');	// Document date
			cell(number_format($rd[$rdIdx][8],2), ' align="right"');	// Qty
			print "</tr>\n";

			$model_total += $rd[$rdIdx][8];
			$model_no = '['.trim($rd[$rdIdx][1]).'] '.$rd[$rdIdx][2]; 	// Model No
			$date['min'] = $rd[$rdIdx][9];
			$date['max'] = $rd[$rdIdx][10];

			// $log[item][type][location]	=> ['in'], ['out'], ['balance']
			if ($rd[$rdIdx][13] == 't') {
				if (isset($log[$rd[$rdIdx][1]][$rd[$rdIdx][11]][$rd[$rdIdx][12]]['in']))
					$log[$rd[$rdIdx][1]][$rd[$rdIdx][11]][$rd[$rdIdx][12]]['in'] += $rd[$rdIdx][8];
				else	$log[$rd[$rdIdx][1]][$rd[$rdIdx][11]][$rd[$rdIdx][12]]['in'] =  $rd[$rdIdx][8];
			} else {
				if (isset($log[$rd[$rdIdx][1]][$rd[$rdIdx][11]][$rd[$rdIdx][12]]['out']))
					$log[$rd[$rdIdx][1]][$rd[$rdIdx][11]][$rd[$rdIdx][12]]['out'] += $rd[$rdIdx][8];
				else	$log[$rd[$rdIdx][1]][$rd[$rdIdx][11]][$rd[$rdIdx][12]]['out'] =  $rd[$rdIdx][8];
			}
			if (isset($log[$rd[$rdIdx][1]][$rd[$rdIdx][11]][$rd[$rdIdx][12]]['bal']))
				$log[$rd[$rdIdx][1]][$rd[$rdIdx][11]][$rd[$rdIdx][12]]['bal'] += $rd[$rdIdx][8];
			else	$log[$rd[$rdIdx][1]][$rd[$rdIdx][11]][$rd[$rdIdx][12]]['bal'] =  $rd[$rdIdx][8];

			$rdIdx++;
		}

		print "<tr>\n";
		cell($model_no, ' colspan="4" align="right" style="color:darkblue"');
		cell(number_format($model_total,2), ' align="right" style="color:darkblue"');
		print "</tr>\n";

		$cat_total += $model_total;
	}

	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format($cat_total,2), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$grand_total += $cat_total;
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
print <<<END
<table width="100%" class="table_f">
END;

print "<tr>\n";
cell(number_format($grand_total,2), ' align="right" style="color:brown; background-color:lightyellow; font-size:14px; font-weight: bold"');
print "</tr>\n";
print "</table>\n";
?>