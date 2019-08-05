<?php
	$sql = "
		SELECT
			1 AS type, bill_code AS inv,
			trunc(sum((biit_qty * biit_unit_price) * (1-bill_discount/100)),2) AS total_invoice_before_vat,
			trunc(sum(((biit_qty * biit_unit_price)* (1-bill_discount/100)) * (1+bill_vat/100)),2) AS total_invoice,
			CASE WHEN c.icat_midx IN($catAK) THEN 0 WHEN c.icat_midx IN($catAD) THEN 1 ELSE 2 END AS item_cat
		FROM
			".ZKP_SQL."_tb_billing AS a
			JOIN ".ZKP_SQL."_tb_billing_item AS b USING (bill_code)
			JOIN ".ZKP_SQL."_tb_item_cat AS d USING (icat_midx)
			JOIN ".ZKP_SQL."_tb_item AS c USING (it_code)
		WHERE $strWhereBill AND bill_dept = '$i'
		GROUP BY type, inv, item_cat
	UNION
		SELECT 
			1 AS type, bill_code AS inv,
			null AS total_invoice_before_vat, 
			sum(bill_delivery_freight_charge) AS total_invoice, 
			3 AS item_cat 
		FROM ".ZKP_SQL."_tb_billing AS a 
		WHERE $strWhereBill AND bill_delivery_freight_charge > 0 AND bill_dept = '$i'
		GROUP BY type, inv, item_cat
	UNION
		SELECT
			2 AS type, turn_code AS inv,
			trunc(sum((reit_qty * reit_unit_price) * (1-turn_discount/100)),2)*-1 AS total_invoice_before_vat,
			trunc(sum(((reit_qty * reit_unit_price)* (1+turn_vat/100)) * (1-turn_discount/100)),2)*-1 AS total_invoice,
			CASE WHEN c.icat_midx IN($catAK) THEN 0 WHEN c.icat_midx IN($catAD) THEN 1 ELSE 2 END AS item_cat
		FROM
			".ZKP_SQL."_tb_return AS a
			JOIN ".ZKP_SQL."_tb_return_item AS b USING (turn_code)
			JOIN ".ZKP_SQL."_tb_item_cat AS d USING (icat_midx)
			JOIN ".ZKP_SQL."_tb_item AS c USING (it_code)
		WHERE $strWhereTurn AND turn_dept = '$i' AND turn_return_condition != 1
		GROUP BY type, inv, item_cat
	UNION
		SELECT 
			2 AS type, turn_code AS inv,
			null AS total_invoice_before_vat, 
			sum(turn_delivery_freight_charge)*-1 AS total_invoice, 
			3 AS item_cat 
		FROM 
			".ZKP_SQL."_tb_return AS a
		WHERE $strWhereTurn AND turn_delivery_freight_charge > 0 AND turn_dept = '$i'
		GROUP BY type, inv, item_cat 
	ORDER BY inv, item_cat";

	$sql_bill = "
		SELECT
			bill_code AS inv,
			trunc(sum((biit_qty * biit_unit_price) * (1-bill_discount/100)),2) AS total_before_vat,
			trunc(sum(((biit_qty * biit_unit_price)* (1-bill_discount/100)) * (1+bill_vat/100)),2) AS total_billing,
			CASE
				WHEN c.icat_midx IN($catAK) THEN 0
				WHEN c.icat_midx IN($catAD) THEN 1
				ELSE 2
			END AS item_cat
		FROM
			".ZKP_SQL."_tb_billing AS a
			JOIN ".ZKP_SQL."_tb_billing_item AS b USING (bill_code)
			JOIN ".ZKP_SQL."_tb_item_cat AS d USING (icat_midx)
			JOIN ".ZKP_SQL."_tb_item AS c USING (it_code)
		WHERE $strWhereBill AND bill_dept = '$i'
		GROUP BY inv, item_cat
	UNION
		SELECT 
			bill_code AS inv,
			null AS total_before_vat, 
			sum(bill_delivery_freight_charge) AS total_billing, 
			3 AS item_cat 
		FROM ".ZKP_SQL."_tb_billing AS a 
		WHERE $strWhereBill AND bill_delivery_freight_charge > 0 AND bill_dept = '$i'
		GROUP BY inv, item_cat
	ORDER BY inv, item_cat
		";

	$sql_bill_realtime = "
		SELECT
			bill_code AS inv,
			trunc(sum(((reit_qty * reit_unit_price)* (1+turn_vat/100)) * (1-turn_discount/100)),2)*-1 AS total_return,
			CASE
				WHEN c.icat_midx IN($catAK) THEN 0
				WHEN c.icat_midx IN($catAD) THEN 1
				ELSE 2
			END AS item_cat
		FROM
			".ZKP_SQL."_tb_billing
			JOIN ".ZKP_SQL."_tb_return ON bill_code = turn_bill_code
			JOIN ".ZKP_SQL."_tb_return_item AS b USING (turn_code)
			JOIN ".ZKP_SQL."_tb_item_cat AS d USING (icat_midx)
			JOIN ".ZKP_SQL."_tb_item AS c USING (it_code)
		WHERE $strWhereBill AND bill_dept = '$i' AND turn_return_condition NOT IN (1)
		GROUP BY inv, item_cat
	UNION
		SELECT 
			bill_code AS inv,
			sum(turn_delivery_freight_charge)*-1 AS total_return, 
			3 AS item_cat 
		FROM 
			".ZKP_SQL."_tb_billing AS a 
			JOIN ".ZKP_SQL."_tb_return ON bill_code = turn_bill_code
		WHERE $strWhereBill AND turn_delivery_freight_charge > 0 AND bill_dept = '$i'
		GROUP BY inv, item_cat 
	ORDER BY inv, item_cat
		";
?>