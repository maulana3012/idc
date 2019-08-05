<?php
if($_idx_pl == 'pl') {
	$col[3] = getPLConfirm('PL', $inpl_idx, '', 'in_item_ed'); 
} else if($_idx_pl == 'claim') {
	$col[3] = getPLConfirm('Claim', $inpl_idx, '', 'in_item_ed'); 
	$sql_ed = "
";
} else if($_idx_pl == 'local') {
	$sql_ed = "
";
}
echo "<pre>";
//var_dump($inpl_idx, $col[3]);
echo "</pre>";
// raw data
$rd_ed 		= array();
$rdIdx_ed	= 0;
$cache_ed	= array("","");
$group0_ed	= array();
while($col_ed =& fetchRowAssoc($col[3])) {

	$rd_ed[] = array(
		$col_ed['it_code'],		//0
		$col_ed['it_model_no'],		//1
		$col_ed['expired_date'],	//2
		$col_ed['qty']			//3
	);

	//1st grouping
	if($cache_ed[0] != $col_ed['it_code']) {
		$cache_ed[0] = $col_ed['it_code'];
		$group0_ed[$col_ed['it_code']] = array();
	}

	if($cache_ed[1] != $col_ed['expired_date']) {
		$cache_ed[1] = $col_ed['expired_date'];
	}
	$group0_ed[$col_ed['it_code']][$col_ed['expired_date']] = 1;
}

print <<<END
<br /><span class="comment"><img src="../../_images/properties/p_leftmenu_icon02.gif"> <b>E/D List</b> </span>
<table width="100%" class="table_box">
	<tr height="25px">
		<th width="20%">ITEM CODE</th>
		<th>ITEM NAME</th>
		<th width="30%">EXPIRED DATE</th>
		<th width="15%">QTY</th>
	</tr>
END;
foreach ($group0_ed as $total1_ed => $group1_ed) {
	$rowSpan_ed = 0;
	//array_walk_recursive($group1_ed, 'getRowSpanED');

	print "<tr>\n";
	$total = 0;
	$print_tr_1 = 0;
	foreach($group1_ed as $total2_ed) {
		if($print_tr_1++ > 0) print "<tr>\n";
		cell($rd_ed[$rdIdx_ed][0], ' valign="top"');					//IT CODE
		cell($rd_ed[$rdIdx_ed][1], ' valign="top"');					//IT MODEL NO
		cell($rd_ed[$rdIdx_ed][2]);							//INVOICE DATE
		cell(number_format($rd_ed[$rdIdx_ed][3]), ' align="right"');	//INVOICE QTY
		print "</tr>\n";
		$total += $rd_ed[$rdIdx_ed][3];
		$model = $rd_ed[$rdIdx_ed][1];
		$rdIdx_ed++;
	}
	print "<tr>\n";
	cell("<b>[$total1_ed] $model</b>", ' colspan="3" align="right" valign="middle" style="color:brown; background-color:lightyellow"');
	cell(number_format($total), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
}
print "</table><br />";
?>