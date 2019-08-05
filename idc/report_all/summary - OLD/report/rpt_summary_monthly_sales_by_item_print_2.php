<?php
$db = array("idc", "med");
$j = 0; $k = 0;
$total_amount = 0;
for($i=0; $i<2; $i++) {

	$sql[$i] = "
		SELECT icat_pidx, icat_midx, it_code, it_model_no
		FROM
		  ".$db[$i]."_tb_customer AS a
		  JOIN ".$db[$i]."_tb_sales_log AS b USING(cus_code)
		  JOIN ".$db[$i]."_tb_item AS d USING(it_code)
		  JOIN ".$db[$i]."_tb_item_cat USING(icat_midx)
		WHERE " . $strWhere[$j++] . "
	UNION
		SELECT icat_pidx, d.icat_midx, d.it_code, d.it_model_no
		FROM
		  ".$db[$i]."_tb_customer AS a
		  JOIN ".$db[$i]."_tb_billing AS b ON bill_ship_to = cus_code
		  JOIN ".$db[$i]."_tb_billing_item AS c USING (bill_code)
		  JOIN ".$db[$i]."_tb_item AS d USING(it_code)
		  JOIN ".$db[$i]."_tb_item_cat AS e ON d.icat_midx=e.icat_midx
		WHERE " . $strWhere[$j++] . "
	UNION
		SELECT icat_pidx, d.icat_midx, d.it_code, d.it_model_no
		FROM
		  ".$db[$i]."_tb_customer AS a
		  JOIN ".$db[$i]."_tb_dr AS b ON dr_ship_to = cus_code
		  JOIN ".$db[$i]."_tb_dr_item AS c USING (dr_code)
		  JOIN ".$db[$i]."_tb_item AS d USING(it_code)
		  JOIN ".$db[$i]."_tb_item_cat AS e ON d.icat_midx=e.icat_midx
		WHERE " . $strWhere[$j++] . "
	UNION
		SELECT icat_pidx, d.icat_midx, d.it_code, d.it_model_no
		FROM
		  ".$db[$i]."_tb_customer AS a
		  JOIN ".$db[$i]."_tb_return AS b ON turn_ship_to = cus_code
		  JOIN ".$db[$i]."_tb_return_item AS c USING (turn_code)
		  JOIN ".$db[$i]."_tb_item AS d USING(it_code)
		  JOIN ".$db[$i]."_tb_item_cat AS e ON d.icat_midx=e.icat_midx
		WHERE " . $strWhere[$j++] . "
	GROUP BY icat_pidx, d.icat_midx, d.it_code, d.it_model_no
	";
	
	
	$sqlMonth[$i] = "
		SELECT 'A' AS source, it_code, to_char(sl_date, 'YYYYMM') AS period, sum(sl_qty) AS qty
		FROM
		  ".$db[$i]."_tb_customer AS a
		  JOIN ".$db[$i]."_tb_sales_log AS b USING(cus_code)
		  JOIN ".$db[$i]."_tb_item AS d USING(it_code)
		  JOIN ".$db[$i]."_tb_item_cat AS e USING(icat_midx)	  
		WHERE " . $strWhere[$k++] . "
		GROUP BY it_code, period 
	UNION
		SELECT 'B' AS source, c.it_code, to_char(bill_inv_date, 'YYYYMM') AS period, sum(biit_qty) AS qty
		FROM
		  ".$db[$i]."_tb_customer AS a
		  JOIN ".$db[$i]."_tb_billing AS b ON bill_ship_to = cus_code
		  JOIN ".$db[$i]."_tb_billing_item AS c USING (bill_code)
		  JOIN ".$db[$i]."_tb_item AS d USING(it_code)
		  JOIN ".$db[$i]."_tb_item_cat AS e ON d.icat_midx=e.icat_midx
		WHERE " . $strWhere[$k++] . "
		GROUP BY c.it_code, period
	UNION
		SELECT 'C' AS source, c.it_code, to_char(dr_date, 'YYYYMM') AS period, sum(drit_qty) AS qty
		FROM
		  ".$db[$i]."_tb_customer AS a
		  JOIN ".$db[$i]."_tb_dr AS b ON dr_ship_to = cus_code
		  JOIN ".$db[$i]."_tb_dr_item AS c USING (dr_code)
		  JOIN ".$db[$i]."_tb_item AS d USING(it_code)
		  JOIN ".$db[$i]."_tb_item_cat AS e ON d.icat_midx=e.icat_midx
		WHERE " . $strWhere[$k++] . "
		GROUP BY c.it_code, period
	UNION
		SELECT 'D' AS source, c.it_code, to_char(turn_return_date, 'YYYYMM') AS period, sum(reit_qty)*-1 AS qty
		FROM
		  ".$db[$i]."_tb_customer AS a
		  JOIN ".$db[$i]."_tb_return AS b ON turn_ship_to = cus_code
		  JOIN ".$db[$i]."_tb_return_item AS c USING (turn_code)
		  JOIN ".$db[$i]."_tb_item AS d USING(it_code)
		  JOIN ".$db[$i]."_tb_item_cat AS e ON d.icat_midx=e.icat_midx
		WHERE " . $strWhere[$k++] . "
		GROUP BY c.it_code, period
	";
}

switch (ZKP_URL) {
  case "ALL":
	$sql = $sql[0] . " UNION " . $sql[1];
	$sqlMonth = $sqlMonth[0] . " UNION " . $sqlMonth[1];
	break;
  case "IDC":
	$sql = $sql[0];
	$sqlMonth = $sqlMonth[0];
	break;
  case "MED":
	$sql = $sql[1];
	$sqlMonth = $sqlMonth[1];
	break;	
  case "MEP":
	$sql = $sql[0];
	$sqlMonth = $sqlMonth[0];
	break;
}

$sql		.= " ORDER BY icat_pidx, icat_midx, it_code";
$sqlMonth	.= " ORDER BY it_code, period ";
/*
echo "<pre>";
var_dump($_order_by, $strWhere);
echo "</pre>";
*/
$tot = array();
$cit = array();
$res =& query($sqlMonth);
while($col =& fetchRowAssoc($res)) {
	$rd[] = array(
		$col['it_code'],$col['period'],$col['qty']
	);

	if(isset($tot[$col['it_code']][$col['period']])) {
		$tot[$col['it_code']][$col['period']] += $col['qty'];
	} else {
		$tot[$col['it_code']][$col['period']] = $col['qty'];
	}
}

// raw data
$rd 	= array();
$rdIdx	= 0;
$cache	= array("","","");
$group0 = array();
//$amount = array('A'=>0,'D'=>0,'H'=>0,'P'=>0);

$res =& query($sql);
while($col =& fetchRowAssoc($res)) {
	$rd[] = array(
		$col['icat_midx'],		//0
		$col['it_code'],		//1
		$col['it_model_no'],	//2
	);

	if($cache[0] != $col['icat_midx']) {
		$cache[0] = $col['icat_midx'];
		$group0[$col['icat_midx']] = array();
	}

	if($cache[1] != $col['it_code']) {
		$cache[1] = $col['it_code'];
	}

	$group0[$col['icat_midx']][$col['it_code']] = 1;
}
?>