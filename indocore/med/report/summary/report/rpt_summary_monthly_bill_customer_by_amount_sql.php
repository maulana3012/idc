<?php
if ($_last_category == 0):
	/*
	 * Jika tidak pilih kategori, maka summary :
	 * - Invoice type from sales report akan menjabarkan customer chain dari invoice tersebut
	*/
	$sql = "
	SELECT
	  ".ZKP_SQL."_getGroupName(bill_dept, bill_ship_to) AS cug_name,
	  bill_dept AS dept,
	  bill_ship_to AS ship_to,
	  cus_full_name AS ship_to_name,
	  to_char(bill_inv_date, 'yyyymm') AS doc_period,
	  trunc(sum(biit_qty * (biit_unit_price*(100-bill_discount)/100))) AS amount
	FROM
	  ".ZKP_SQL."_tb_customer AS c
	  JOIN ".ZKP_SQL."_tb_billing AS b ON bill_ship_to = cus_code
	  JOIN ".ZKP_SQL."_tb_billing_item USING(bill_code)
	  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
	WHERE $strWhereBill
	GROUP BY cug_name, dept, ship_to, ship_to_name, doc_period
			UNION
	SELECT
	  ".ZKP_SQL."_getGroupName(bill_dept, s.cus_code) AS cug_name,
	  bill_dept AS dept,
	  s.cus_code AS ship_to,
	  (select cus_full_name from ".ZKP_SQL."_tb_customer where cus_code=s.cus_code) AS ship_to_name,
	  to_char(bill_inv_date, 'yyyymm') AS doc_period,
	  TRUNC(SUM(bisl_amount)/1.1) AS amount
	FROM
	  ".ZKP_SQL."_tb_billing AS b
	  JOIN ".ZKP_SQL."_tb_billing_sales AS s USING(bill_code)
	WHERE $strWhereSl
	GROUP BY cug_name, dept, ship_to, ship_to_name, doc_period
			UNION
	SELECT
	  ".ZKP_SQL."_getGroupName(turn_dept, turn_ship_to) AS cug_name,
	  turn_dept AS dept,
	  turn_ship_to AS ship_to,
	  cus_full_name AS ship_to_name,
	  to_char(turn_return_date, 'yyyymm') AS doc_period,
	  trunc(sum(reit_qty * (reit_unit_price*(100-turn_discount)/100)))*-1 AS amount
	FROM
	  ".ZKP_SQL."_tb_customer AS c
	  JOIN ".ZKP_SQL."_tb_return AS t ON turn_ship_to = cus_code
	  JOIN ".ZKP_SQL."_tb_return_item USING(turn_code)
	  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
	WHERE $strWhereTurn
	GROUP BY cug_name, dept, ship_to, ship_to_name, doc_period
	ORDER BY dept, cug_name, ship_to, ship_to_name, doc_period
	";

else :

	/*
	 * Jika pilih kategori, maka summary :
	 * - Invoice type from sales report tidak akan menjabarkan customer chain dari invoice tersebut
	*/
	$sql = "
	SELECT
	  ".ZKP_SQL."_getGroupName(bill_dept, bill_ship_to) AS cug_name,
	  bill_dept AS dept,
	  bill_ship_to AS ship_to,
	  cus_full_name AS ship_to_name,
	  to_char(bill_inv_date, 'yyyymm') AS doc_period,
	  trunc(sum(biit_qty * (biit_unit_price*(100-bill_discount)/100))) AS amount
	FROM
	  ".ZKP_SQL."_tb_customer AS c
	  JOIN ".ZKP_SQL."_tb_billing AS b ON bill_ship_to = cus_code
	  JOIN ".ZKP_SQL."_tb_billing_item USING(bill_code)
	  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
	WHERE $strWhereBill
	GROUP BY cug_name, dept, ship_to, ship_to_name, doc_period
			UNION
	SELECT
	  ".ZKP_SQL."_getGroupName(turn_dept, turn_ship_to) AS cug_name,
	  turn_dept AS dept,
	  turn_ship_to AS ship_to,
	  cus_full_name AS ship_to_name,
	  to_char(turn_return_date, 'yyyymm') AS doc_period,
	  trunc(sum(reit_qty * (reit_unit_price*(100-turn_discount)/100)))*-1 AS amount
	FROM
	  ".ZKP_SQL."_tb_customer AS c
	  JOIN ".ZKP_SQL."_tb_return AS t ON turn_ship_to = cus_code
	  JOIN ".ZKP_SQL."_tb_return_item USING(turn_code)
	  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
	WHERE $strWhereTurn
	GROUP BY cug_name, dept, ship_to, ship_to_name, doc_period
	ORDER BY dept, cug_name, ship_to, ship_to_name, doc_period
	";

endif;