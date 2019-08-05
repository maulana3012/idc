<?php
$db = array("idc", "med");
$j = 0;
if ($_last_category == 0):
	for($i=0; $i<2; $i++) {
		/*
		 * Jika tidak pilih kategori, maka summary :
		 * - Invoice type from sales report akan menjabarkan customer chain dari invoice tersebut
		*/
		$sql_bill[$i] = "
		SELECT
		  ".$db[$i]."_getGroupName(bill_dept, bill_ship_to) AS cug_name,
		  bill_dept AS dept,
		  c.cus_code AS ship_to,
		  cus_full_name AS ship_to_name,
		  trunc(sum(biit_qty * (biit_unit_price*(100-bill_discount)/100))) AS amount
		FROM
		  ".$db[$i]."_tb_customer AS c
		  JOIN ".$db[$i]."_tb_billing AS b ON bill_ship_to = cus_code
		  JOIN ".$db[$i]."_tb_billing_item USING(bill_code)
		  JOIN ".$db[$i]."_tb_item_cat AS icat USING(icat_midx)
		  JOIN ".$db[$i]."_tb_item USING(it_code)
		WHERE ".$strWhere[$j++]." AND bill_type_billing in (1,2)
		GROUP BY dept, cug_name, ship_to, ship_to_name";
		
		$sql_sales[$i] = "
		SELECT DISTINCT
		  ".$db[$i]."_getGroupName(bill_dept, cus_code) AS cug_name,
		  bill_dept AS dept,
		  s.cus_code AS ship_to,
		  (select cus_full_name from ".$db[$i]."_tb_customer where cus_code=s.cus_code) AS ship_to_name,
		  TRUNC(SUM(bisl_amount)/1.1) AS amount
		FROM
		  ".$db[$i]."_tb_billing AS b
		  JOIN ".$db[$i]."_tb_billing_sales AS s USING(bill_code)
		WHERE ".$strWhere[$j++]." AND bill_type_billing = 3
		GROUP BY cug_name, dept, ship_to, ship_to_name";
		
		$sql_turn[$i] = "
		SELECT
		  ".$db[$i]."_getGroupName(turn_dept, turn_ship_to) AS cug_name,
		  turn_dept AS dept,
		  c.cus_code AS ship_to,
		  cus_full_name AS ship_to_name,
		  trunc(sum(reit_qty * (reit_unit_price*(100-turn_discount)/100)))*-1 AS amount
		FROM
		  ".$db[$i]."_tb_customer AS c
		  JOIN ".$db[$i]."_tb_return AS t ON turn_ship_to = cus_code
		  JOIN ".$db[$i]."_tb_return_item USING(turn_code)
		  JOIN ".$db[$i]."_tb_item_cat AS icat USING(icat_midx)
		WHERE ".$strWhere[$j++]." AND turn_return_condition IN (2,3,4)
		GROUP BY dept, cug_name, ship_to, ship_to_name";
	}
else :

	for($i=0; $i<2; $i++) {
		/*
		 * Jika pilih kategori, maka summary :
		 * - Invoice type from sales report tidak akan menjabarkan customer chain dari invoice tersebut
		*/
		$sql_bill[$i] = "
			SELECT
			  ".$db[$i]."_getGroupName(bill_dept, bill_ship_to) AS cug_name,
			  bill_dept AS dept,
			  c.cus_code AS ship_to,
			  cus_full_name AS ship_to_name,
			  trunc(sum(biit_qty * (biit_unit_price*(100-bill_discount)/100))) AS amount
			FROM
			  ".$db[$i]."_tb_customer AS c
			  JOIN ".$db[$i]."_tb_billing AS b ON bill_ship_to = cus_code
			  JOIN ".$db[$i]."_tb_billing_item USING(bill_code)
			  JOIN ".$db[$i]."_tb_item_cat AS icat USING(icat_midx)
			  JOIN ".$db[$i]."_tb_item USING(it_code)
			WHERE ".$strWhere[$j++]."
			GROUP BY dept, cug_name, ship_to, ship_to_name
		";

		$j++;

		$sql_turn[$i] = "
			SELECT
			  ".$db[$i]."_getGroupName(turn_dept, turn_ship_to) AS cug_name,
			  turn_dept AS dept,
			  c.cus_code AS ship_to,
			  cus_full_name AS ship_to_name,
			  trunc(sum(reit_qty * (reit_unit_price*(100-turn_discount)/100)))*-1 AS amount
			FROM
			  ".$db[$i]."_tb_customer AS c
			  JOIN ".$db[$i]."_tb_return AS t ON turn_ship_to = cus_code
			  JOIN ".$db[$i]."_tb_return_item USING(turn_code)
			  JOIN ".$db[$i]."_tb_item_cat AS icat USING(icat_midx)
			WHERE ".$strWhere[$j++]." AND turn_return_condition IN (2,3,4)
			GROUP BY dept, cug_name, ship_to, ship_to_name
		";
	}
endif;


switch (ZKP_URL) {
  case "ALL":
  	if (@$sql_sales) {
		$sql =	$sql_bill[0] . " UNION " . $sql_sales[0] . " UNION " .  $sql_turn[0] . " UNION " . 
				$sql_bill[1] . " UNION " . $sql_sales[1] . " UNION " .  $sql_turn[1];
  	} else {
		$sql =	$sql_bill[0] . " UNION " . $sql_turn[0] . " UNION " . 
				$sql_bill[1] . " UNION " . $sql_turn[1];
  	}
	break;
  case "IDC":
  	if (@$sql_sales) {
  		$sql =	$sql_bill[0] . " UNION " . $sql_sales[0] . " UNION " .  $sql_turn[0];
	} else {
		$sql =	$sql_bill[0] . " UNION " . $sql_turn[0];
	}	
	break;
  case "MED":
  	if (@$sql_sales) {
  		$sql =	$sql_bill[1] . " UNION " . $sql_sales[1] . " UNION " .  $sql_turn[1];
	} else {
		$sql =	$sql_bill[1] . " UNION " .  $sql_turn[1];
	}
	break;	
  case "MEP":
  	if (@$sql_sales) {
  		$sql =	$sql_bill[0] . " UNION " . $sql_sales[0] . " UNION " .  $sql_turn[0];
	} else {
		$sql =	$sql_bill[0] . " UNION " .  $sql_turn[0];
	}	
	break;
}

$sql .= " ORDER BY dept, cug_name, ship_to";
/*
echo "<pre>";
var_dump($sql);
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