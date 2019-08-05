<?php

$db = array("idc", "med");
$j = 0; 
for($i=0; $i<2; $i++) {

	$sql_tmp[$i] = "
		SELECT 
			SUM(biit.biit_qty) AS qty, 
			TRUNC(SUM(biit.biit_qty * (biit.biit_unit_price*(100-bill.bill_discount)/100)),2) AS amount,
			'billing' AS condition
		FROM
			".$db[$i]."_tb_customer AS cus
			JOIN ".$db[$i]."_tb_billing AS bill ON bill_ship_to = cus_code
			JOIN ".$db[$i]."_tb_billing_item AS biit USING(bill_code)
		WHERE biit.it_code = '{$rd[$rdIdx][1]}' AND biit.icat_midx = {$rd[$rdIdx][3]} AND ".$strWhereItem[$j++]."
	UNION 
		SELECT 
			SUM(-(reit.reit_qty)) AS qty, 
			TRUNC(SUM(-(reit.reit_qty * (reit.reit_unit_price*(100-turn.turn_discount)/100))),2) AS amount,
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
		WHERE reit.it_code = '{$rd[$rdIdx][1]}' AND reit.icat_midx = {$rd[$rdIdx][3]} AND ".$strWhereItem[$j++]."
	GROUP BY condition
	UNION
		SELECT 
			SUM(drit.drit_qty) AS qty, 
			0 AS amount,
			'billing' AS condition
		FROM
			".$db[$i]."_tb_customer AS cus
			JOIN ".$db[$i]."_tb_dr AS dr ON dr_ship_to = cus_code
			JOIN ".$db[$i]."_tb_dr_item AS drit USING(dr_code)
			JOIN ".$db[$i]."_tb_item AS it USING(it_code)
		WHERE drit.it_code = '{$rd[$rdIdx][1]}' AND it.icat_midx = {$rd[$rdIdx][3]} AND ".$strWhereItem[$j++]."
	";
}

switch (ZKP_URL) {
  case "ALL": $sql_item = $sql_tmp[0] . " UNION " . $sql_tmp[1]; break;
  case "IDC": $sql_item = $sql_tmp[0]; break;
  case "MED": $sql_item = $sql_tmp[1]; break;	
  case "MEP": $sql_item = $sql_tmp[0]; break;
}
/*
echo "<pre>";
echo "$sql_item";
echo "</pre>";
exit;
*/
$result =& query($sql_item);
$total = array(0,0);
while($column =& fetchRowAssoc($result)) {
	if($column['condition'] != 'turn_1') {
		$total[1] += $column['amount'];
	}
	$total[0] += $column['qty'];
}

if($total[1] == 0) $rate = 0;
else if($total[1] > 0) {
	$rate = $total[1]*100/$sub_amount[$rd[$rdIdx][0]];
	if($rate < 0) $rate = $rate*-1;
}
else if($total[1] < 0) {
	$rate = $total[1]*100/$sub_amount[$rd[$rdIdx][0]];
	if($rate > 0) $rate = -($rate);
}

if($sub_amount[$rd[$rdIdx][0]] == 0) $sub_rate = 0;
else if($sub_amount[$rd[$rdIdx][0]] > 0) {
	$sub_rate = $sub_amount[$rd[$rdIdx][0]]*100/$total_amount;
	if($sub_rate < 0) $sub_rate = $sub_rate*-1;
} else if($sub_amount[$rd[$rdIdx][0]] < 0) {
	$sub_rate = $sub_amount[$rd[$rdIdx][0]]*100/$total_amount;
	if($sub_rate > 0) $sub_rate = -($sub_rate);
}
?>