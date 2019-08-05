<?php
//VARIABLE
$amount			 = array();
$amounttotal	 = array(0,0,0,0,0,0,0);
$grandamount	 = 0;
$sql = array();
/*
SQL
*/
$db = array("idc", "med");
$j = 0;
for($i=0; $i<2; $i++) {
	$sql[$i] = "
		SELECT SUM(b.bill_amount_qty_unit_price) as amount
		FROM ".$db[$i]."_tb_customer AS c JOIN ".$db[$i]."_tb_billing AS b ON bill_ship_to = cus_code WHERE ". $strWhere[$j++] ."
	  UNION
		SELECT -(SUM(t.turn_amount_qty_unit_price)) as amount
		FROM ".$db[$i]."_tb_customer AS c JOIN ".$db[$i]."_tb_return AS t ON turn_ship_to = cus_code WHERE ". $strWhere[$j++] ." AND turn_return_condition IN (2,3,4)
	  UNION
		SELECT SUM(sv_total_amount) as amount
		FROM ".$db[$i]."_tb_customer AS c JOIN ".$db[$i]."_tb_service AS b ON sv_cus_to = cus_code WHERE ". $strWhere[$j++] ."
		";
}

switch (ZKP_URL) {
  case "ALL": $sql = $sql[0] . " UNION " . $sql[1]; break;
  case "IDC": $sql = $sql[0]; break;
  case "MED": $sql = $sql[1]; break;
  case "MEP": $sql = $sql[0]; break;
}
/*
echo "<pre>";
var_dump($sql_bill);
echo "</pre>";
*/
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {
	$grandamount += $col['amount'];
}

foreach ($channel as $value => $key) {
	/*
	0. billing before vat
	1. rate
	2. billing after vat + freight charge
	3. billing realtime
	4. paid billing realtime
	5. paid billing
	6. remain billing
	7. amount billing before vat
	*/

	$sql_bill = array();
	$db = array("idc", "med");
	$j = 0;
	for($i=0; $i<2; $i++) {
		$sql_bill[$i]	= "
			SELECT
				'billing' AS invoice,
				sum(b.bill_amount_qty_unit_price) AS total_before_vat,
				sum(b.bill_total_billing) AS total_billing,
				sum(b.bill_total_billing_net) AS total_billing_realtime,
				sum(b.bill_remain_amount) AS remain_billing,
				(select sum(pay_paid) from ".$db[$i]."_tb_customer as c join ".$db[$i]."_tb_billing as b ON bill_cus_to = cus_code join ".$db[$i]."_tb_payment using(bill_code) where cus_channel = '$value' AND ".$strWhere[$j]." AND pay_note!='DEPOSIT-B') AS pay_amount
			FROM
				".$db[$i]."_tb_customer AS c
				JOIN ".$db[$i]."_tb_billing AS b ON b.bill_cus_to = c.cus_code
			WHERE c.cus_channel = '$value' AND ".$strWhere[$j++]."
			GROUP BY invoice
		UNION
			SELECT
				'return' AS invoice,
				sum(-(t.turn_amount_qty_unit_price)) AS total_before_vat,
				sum(-(t.turn_total_return)) AS total_billing,
				NULL AS total_billing_realtime,
				NULL AS remain_billing,
				NULL AS pay_amount
			FROM
				".$db[$i]."_tb_customer AS c
				JOIN ".$db[$i]."_tb_return AS t ON t.turn_cus_to = c.cus_code
			WHERE c.cus_channel = '$value' AND ".$strWhere[$j++]." AND t.turn_return_condition IN (2,3,4)
			GROUP BY invoice
		UNION
			SELECT
				'billing' AS invoice,
				sum(sv_total_amount) AS total_before_vat,
				sum(sv_total_amount) AS total_billing,
				sum(sv_total_amount) AS total_billing_realtime,
				sum(sv_total_remain) AS remain_billing,
				(select sum(svpay_paid) from ".$db[$i]."_tb_customer as c JOIN ".$db[$i]."_tb_service AS b ON sv_cus_to = c.cus_code JOIN ".$db[$i]."_tb_service_payment AS p USING(sv_code) WHERE c.cus_channel = '$value' AND ".$strWhere[$j].") AS pay_amount
			FROM
				".$db[$i]."_tb_customer AS c
				JOIN ".$db[$i]."_tb_service AS b ON sv_cus_to = c.cus_code
			WHERE c.cus_channel = '$value' AND ".$strWhere[$j++]."
			GROUP BY invoice
		";
	}

	switch (ZKP_URL) {
	  case "ALL": $sql_bill = $sql_bill[0] . " UNION " . $sql_bill[1]; break;
	  case "IDC": $sql_bill = $sql_bill[0]; break;
	  case "MED": $sql_bill = $sql_bill[1]; break;
	  case "MEP": $sql_bill = $sql_bill[0]; break;
	}
/*
echo "<pre>";
var_dump($sql_bill);
echo "</pre>";
*/
	$res_bill =& query($sql_bill);
	while($col_bill =& fetchRowAssoc($res_bill)) {
		if(empty($amount[$value][0])) {
			$amount[$value][0]	= $col_bill['total_before_vat'];
			$amount[$value][2]	= $col_bill['total_billing'];
			$amount[$value][3]	= $col_bill['total_billing_realtime'];
			$amount[$value][4]	= $col_bill['pay_amount'];
			$amount[$value][6]	= $col_bill['remain_billing'];
		} else {
			$amount[$value][0]	+= $col_bill['total_before_vat'];
			$amount[$value][2]	+= $col_bill['total_billing'];
			$amount[$value][3]	+= $col_bill['total_billing_realtime'];
			$amount[$value][4]	+= $col_bill['pay_amount'];
			$amount[$value][6]	+= $col_bill['remain_billing'];
		}
	}

	$sql_paid = array();
	$db = array("idc", "med");
	$j = 0;
	for($i=0; $i<2; $i++) {
		$sql_paid[$i] = "
			SELECT SUM(p.pay_paid)  AS paid_billing
			FROM
				".$db[$i]."_tb_customer AS c
				JOIN ".$db[$i]."_tb_billing AS b ON b.bill_cus_to = c.cus_code
				JOIN ".$db[$i]."_tb_payment AS p USING(bill_code)
			WHERE c.cus_channel = '$value' AND ".$strWherePay[$j++]."
		UNION
			SELECT SUM(svpay_paid)  AS paid_billing
			FROM
				".$db[$i]."_tb_customer AS c
				JOIN ".$db[$i]."_tb_service AS b ON sv_cus_to = c.cus_code
				JOIN ".$db[$i]."_tb_service_payment AS p USING(sv_code)
			WHERE c.cus_channel = '$value' AND ".$strWherePay[$j++]."
		";		
	}

	switch (ZKP_URL) {
	  case "ALL": $sql_paid = $sql_paid[0] . " UNION " . $sql_paid[1]; break;
	  case "IDC": $sql_paid = $sql_paid[0]; break;
	  case "MED": $sql_paid = $sql_paid[1]; break;
	  case "MEP": $sql_paid = $sql_paid[0]; break;
	}
/*
echo "<pre>";
var_dump($sql_paid);
echo "</pre>";
*/
	$res_paid =& query($sql_paid);
	while($col_paid =& fetchRowAssoc($res_paid)) {
		if(empty($amount[$value][5]))	$amount[$value][5]	= $col_paid['paid_billing'];
		else							$amount[$value][5]	+= $col_paid['paid_billing'];
	}

	if(empty($amount[$value][0])) {
		$amount[$value][1] = 0;
	} else if($amount[$value][0] > 0) {
		$amount[$value][1] = $amount[$value][0]*100/$grandamount;
		if($amount[$value][1] < 0) $amount[$value][1] = $amount[$value][1]*-1;
	} else if($amount[$value][0] < 0) {
		$amount[$value][1] = $amount[$value][0]*100/$grandamount;
		if($amount[$value][1] > 0) $amount[$value][1] = -($amount[$value][1]);
	}

}
?>