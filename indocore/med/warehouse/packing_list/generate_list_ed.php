<?php
if($_idx_pl == 'pl') {
	$sql_ed = "
	SELECT
	 a.it_code,
	 a.it_model_no,
	 to_char(b.epl_expired_date,'Mon-YYYY') AS expired_date,
	 b.epl_qty AS qty
	FROM ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_expired_pl AS b USING(it_code) 
	WHERE b.inpl_idx = $inpl_idx
	ORDER BY a.it_code, b.epl_expired_date";
} else if($_idx_pl == 'claim') {
	$sql_ed = "
	SELECT
	 a.it_code,
	 a.it_model_no,
	 to_char(b.ecl_expired_date,'Mon-YYYY') AS expired_date,
	 b.ecl_qty AS qty
	FROM ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_expired_claim AS b USING(it_code) 
	WHERE b.incl_idx = $inpl_idx
	ORDER BY a.it_code, b.ecl_expired_date";
} else if($_idx_pl == 'local') {
	$sql_ed = "
	SELECT
	 a.it_code,
	 a.it_model_no,
	 to_char(b.elc_expired_date,'Mon-YYYY') AS expired_date,
	 b.elc_qty AS qty
	FROM ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_expired_local AS b USING(it_code) 
	WHERE b.inlc_idx = $inpl_idx
	ORDER BY a.it_code, b.elc_expired_date";
}
$res_ed =& query($sql_ed);

// raw data
$rd_ed 		= array();
$rdIdx_ed	= 0;
$cache_ed	= array("","");
$group0_ed	= array();

while($col_ed =& fetchRowAssoc($res_ed)) {

	$rd_ed[] = array(
		$col_ed['it_code'],			//0
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
		cell($rd_ed[$rdIdx_ed][2]);										//INVOICE DATE
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