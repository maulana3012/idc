<?php
$db = array("idc", "med");
$j = 0;
for($i=0; $i<2; $i++) {

	$sql_bill[$i] = "
	SELECT
	  ".$db[$i]."_getGroupName(bill_dept, bill_ship_to) AS cug_name,
	  bill_dept AS dept,
	  bill_ship_to AS ship_to,
	  cus_full_name AS ship_to_name,
	  SUM(bill_amount_qty_unit_price) AS amount
	FROM
	  ".$db[$i]."_tb_customer AS c
	  JOIN ".$db[$i]."_tb_billing AS b ON bill_ship_to = cus_code
	WHERE ".$strWhere[$j++]."
	GROUP BY dept, cug_name, ship_to, ship_to_name";
	
	$sql_sales[$i] = "
	SELECT
	  ".$db[$i]."_getGroupName(bill_dept, cus_code) AS cug_name,
	  bill_dept AS dept,
	  s.cus_code AS ship_to,
	  (select cus_full_name from ".$db[$i]."_tb_customer where cus_code=s.cus_code) AS ship_to_name,
	  SUM(bisl_amount/1.1) AS amount
	FROM
	  ".$db[$i]."_tb_billing AS b
	  JOIN ".$db[$i]."_tb_billing_sales AS s USING(bill_code)
	WHERE ".$strWhere[$j++]."
	GROUP BY dept, cug_name, ship_to, ship_to_name";
	
	$sql_turn[$i] = "
	SELECT
	  ".$db[$i]."_getGroupName(turn_dept, turn_ship_to) AS cug_name,
	  turn_dept AS dept,
	  turn_ship_to AS ship_to,
	  cus_full_name AS ship_to_name,
	  SUM(-(turn_amount_qty_unit_price)) AS amount
	FROM
	  ".$db[$i]."_tb_customer AS c
	  JOIN ".$db[$i]."_tb_return AS t ON turn_ship_to = cus_code
	WHERE ".$strWhere[$j++]." AND turn_return_condition IN (2,3,4)
	GROUP BY dept, cug_name, ship_to, ship_to_name";

}

switch (ZKP_URL) {
  case "ALL":
	$sql =	$sql_bill[0] . " UNION " . $sql_sales[0] . " UNION " .  $sql_turn[0] . " UNION " . 
			$sql_bill[1] . " UNION " . $sql_sales[1] . " UNION " .  $sql_turn[1];
	break;
  case "IDC":
	$sql =	$sql_bill[0] . " UNION " . $sql_sales[0] . " UNION " .  $sql_turn[0];
	break;
  case "MED":
	$sql =	$sql_bill[1] . " UNION " . $sql_sales[1] . " UNION " .  $sql_turn[1];
	break;	
  case "MEP":
	$sql =	$sql_bill[0] . " UNION " . $sql_sales[0] . " UNION " .  $sql_turn[0];
	break;
}

$sql .= " ORDER BY dept, cug_name, ship_to";
/*
echo "<pre>";
var_dump($strWhere);
echo "</pre>";
*/
// raw data
$rd 	= array();
$rdIdx	= 0;
$cache	= array("","",""); // 3th level
$group0 = array();
$amount = array('A'=>0,'D'=>0,'H'=>0,'M'=>0,'P'=>0,'T'=>0);
$a 		= '';

$res =& query($sql);
while($col =& fetchRowAssoc($res)) {
	if($a != $col['ship_to']) {
		$rd[] = array(
			$col['dept'],			//0
			$col['cug_name'],		//1
			$col['ship_to'],		//2
			$col['ship_to_name']	//3
		);

		//1st grouping
		if($cache[0] != $col['dept']) {
			$cache[0] = $col['dept'];
			$group0[$col['dept']] = array();
		}

		if($cache[1] != $col['cug_name']) {
			$cache[1] = $col['cug_name'];
			$group0[$col['dept']][$col['cug_name']] = array();
		}
	
		if($cache[2] != $col['ship_to']) {
			$cache[2] = $col['ship_to'];
		}

		$group0[$col['dept']][$col['cug_name']][$col['ship_to']] = 1;
	}
	$a = $col['ship_to'];

	//to get total amount each dept, cug name, customer
	$amount[$col['dept']] += $col['amount'];

	if(!isset($sub_amount[$col['dept']][$col['cug_name']])) {
		$sub_amount[$col['dept']][$col['cug_name']] = $col['amount'];
	} else {
		$sub_amount[$col['dept']][$col['cug_name']] += $col['amount'];
	}

	if(!isset($cus_amount[$col['dept']][$col['cug_name']][$col['ship_to']])) {
		$cus_amount[$col['dept']][$col['cug_name']][$col['ship_to']] = $col['amount'];
	} else {
		$cus_amount[$col['dept']][$col['cug_name']][$col['ship_to']] += $col['amount'];
	}
}
?>