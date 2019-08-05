<?php
$db = array("idc", "med");
$j = 0; $k = 0;
$total_amount = 0;
for($i=0; $i<2; $i++) {

	$sql_bill[$i] = "
	SELECT
	  DISTINCT(it.it_code) AS it_code,
	  it.it_model_no AS model_no,
	  icat.icat_pidx AS icat_pidx,
	  icat.icat_midx AS icat_midx
	FROM
	  ".$db[$i]."_tb_customer AS cus
	  JOIN ".$db[$i]."_tb_billing AS bill ON bill_ship_to = cus_code
	  JOIN ".$db[$i]."_tb_billing_item AS biit USING(bill_code)
	  JOIN ".$db[$i]."_tb_item_cat AS icat USING(icat_midx)
	  JOIN ".$db[$i]."_tb_item AS it USING(it_code)
	WHERE " . $strWhere[$j++];
	
	$sql_turn[$i] = "
	SELECT
	  DISTINCT(it.it_code) AS it_code,
	  it.it_model_no AS model_no,
	  icat.icat_pidx AS icat_pidx,
	  icat.icat_midx AS icat_midx
	FROM
	  ".$db[$i]."_tb_customer AS cus
	  JOIN ".$db[$i]."_tb_return AS turn ON turn_ship_to = cus_code
	  JOIN ".$db[$i]."_tb_return_item AS reit USING(turn_code)
	  JOIN ".$db[$i]."_tb_item_cat AS icat USING(icat_midx)
	  JOIN ".$db[$i]."_tb_item AS it USING(it_code)
	WHERE " . $strWhere[$j++];
	
	$sql_dr[$i] = "
	SELECT
	  DISTINCT(it.it_code) AS it_code,
	  it.it_model_no AS model_no,
	  icat.icat_pidx AS icat_pidx,
	  icat.icat_midx AS icat_midx
	FROM
	  ".$db[$i]."_tb_customer AS cus
	  JOIN ".$db[$i]."_tb_dr AS dr ON dr_ship_to = cus_code
	  JOIN ".$db[$i]."_tb_dr_item AS biit USING(dr_code)
	  JOIN ".$db[$i]."_tb_item AS it USING(it_code)
	  JOIN ".$db[$i]."_tb_item_cat AS icat USING(icat_midx)
	WHERE " . $strWhere[$j++];

// --------------------------------------------------------------------------------------------------------------

	$amountbill_sql[$i] = 
	"SELECT
		icat.icat_pidx AS icat_pidx,
		SUM(ROUND(biit.biit_qty * (biit.biit_unit_price*(100-bill.bill_discount)/100))) AS amount,
		'billing' AS condition
	FROM
		".$db[$i]."_tb_customer AS cus
		JOIN ".$db[$i]."_tb_billing AS bill ON bill_ship_to = cus_code
		JOIN ".$db[$i]."_tb_billing_item AS biit USING(bill_code)
		JOIN ".$db[$i]."_tb_item_cat AS icat USING(icat_midx)
	WHERE " . $strWhere[$k++] . "
	GROUP BY icat_pidx ";
	
	$amountturn_sql[$i] = 
	"SELECT
		icat.icat_pidx AS icat_pidx,
		SUM(-(ROUND(reit.reit_qty * (reit.reit_unit_price*(100-turn.turn_discount)/100)))) AS amount,
		CASE
			WHEN turn.turn_return_condition=1 THEN 'turn_1'
			WHEN turn.turn_return_condition=2 THEN 'turn_2'
			WHEN turn.turn_return_condition=3 THEN 'turn_3'
			WHEN turn.turn_return_condition=4 THEN 'turn_4'
		END AS condition
	FROM
		".$db[$i]."_tb_customer AS cus
		JOIN ".$db[$i]."_tb_return AS turn ON turn_ship_to = cus_code
		JOIN ".$db[$i]."_tb_return_item AS reit USING(turn_code)
		JOIN ".$db[$i]."_tb_item_cat AS icat USING(icat_midx)
	WHERE " . $strWhere[$k++] . " AND turn.turn_return_condition IN(2,3,4)
	GROUP BY icat_pidx, turn.turn_return_condition ";
	
	$k++;

}

switch (ZKP_URL) {
  case "ALL":
	$sql =	$sql_bill[0] . " UNION " . $sql_turn[0] . " UNION " .  $sql_dr[0] . " UNION " . 
			$sql_bill[1] . " UNION " . $sql_turn[1] . " UNION " .  $sql_dr[1];
	$amount_sql =	$amountbill_sql[0] . " UNION " . $amountturn_sql[0] . " UNION " . 
					$amountbill_sql[1] . " UNION " . $amountturn_sql[1] ;
	break;
  case "IDC":
	$sql =	$sql_bill[0] . " UNION " . $sql_turn[0] . " UNION " .  $sql_dr[0];
	$amount_sql = $amountbill_sql[0] . " UNION " . $amountturn_sql[0];
	break;
  case "MED":
	$sql =	$sql_bill[1] . " UNION " . $sql_turn[1] . " UNION " .  $sql_dr[1];
	$amount_sql = $amountbill_sql[1] . " UNION " . $amountturn_sql[1];
	break;	
  case "MEP":
	$sql =	$sql_bill[0] . " UNION " . $sql_turn[0] . " UNION " .  $sql_dr[0];
	$amount_sql = $amountbill_sql[0] . " UNION " . $amountturn_sql[0];
	break;
}

$sql		.= " ORDER by icat_pidx, icat_midx, it_code";
$amount_sql .= " ORDER BY icat_pidx";
/*
echo "<pre>";
var_dump($sql);
echo "</pre>";
*/
// raw data
$rd		= array();
$rdIdx	= 0;
$cache	= array("","");
$group0	= array();
$a		= array("","");
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_pidx'],	//0
		$col['it_code'],	//1
		$col['model_no'],	//2
		$col['icat_midx']	//3
		);

	//1st grouping
	if($cache[0] != $col['icat_pidx']) {
		$cache[0] = $col['icat_pidx'];
		$group0[$col['icat_pidx']] = array();
	}

	if($cache[1] != $col['icat_midx'].$col['it_code']) {
		$cache[1] = $col['icat_midx'].$col['it_code'];
	}
	$group0[$col['icat_pidx']][$col['icat_midx'].$col['it_code']] = 1;
}


$amount_res =& query($amount_sql);
while($amount_col =& fetchRowAssoc($amount_res)) {
	if(!isset($sub_amount[$amount_col['icat_pidx']])) {
		$sub_amount[$amount_col['icat_pidx']] = $amount_col['amount'];
	} else {
		$sub_amount[$amount_col['icat_pidx']] += $amount_col['amount'];
	}
	$total_amount += $amount_col['amount'];
}
?>