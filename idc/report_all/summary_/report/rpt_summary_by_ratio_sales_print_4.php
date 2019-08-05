<?php
$db = array("idc", "med");
$j = 0; $k = 0; $l = 0;
$sql = array();
$sql_bill = array();
$sql_bill_realtime = array();
for($a=0; $a<2; $a++) {
	$sql[$a] = "
		SELECT
			1 AS type, bill_code AS inv,
			trunc(sum((biit_qty * biit_unit_price) * (1-bill_discount/100)),2) AS total_invoice_before_vat,
			trunc(sum(((biit_qty * biit_unit_price)* (1-bill_discount/100)) * (1+bill_vat/100)),2) AS total_invoice,
			CASE WHEN c.icat_midx IN($catAK) THEN 0 WHEN c.icat_midx IN($catAD) THEN 1 ELSE 2 END AS item_cat
		FROM
			".$db[$a]."_tb_billing AS a
			JOIN ".$db[$a]."_tb_billing_item AS b USING (bill_code)
			JOIN ".$db[$a]."_tb_item_cat AS d USING (icat_midx)
			JOIN ".$db[$a]."_tb_item AS c USING (it_code)
		WHERE ". $strWhere[$j]." AND bill_dept = '$i'
		GROUP BY type, inv, item_cat
	UNION
		SELECT 
			1 AS type, bill_code AS inv,
			null AS total_invoice_before_vat, 
			sum(bill_delivery_freight_charge) AS total_invoice, 
			3 AS item_cat 
		FROM ".$db[$a]."_tb_billing AS a 
		WHERE ". $strWhere[$j++]." AND bill_delivery_freight_charge > 0 AND bill_dept = '$i'
		GROUP BY type, inv, item_cat
	UNION
		SELECT
			2 AS type, turn_code AS inv,
			trunc(sum((reit_qty * reit_unit_price) * (1-turn_discount/100)),2)*-1 AS total_invoice_before_vat,
			trunc(sum(((reit_qty * reit_unit_price)* (1+turn_vat/100)) * (1-turn_discount/100)),2)*-1 AS total_invoice,
			CASE WHEN c.icat_midx IN($catAK) THEN 0 WHEN c.icat_midx IN($catAD) THEN 1 ELSE 2 END AS item_cat
		FROM
			".$db[$a]."_tb_return AS a
			JOIN ".$db[$a]."_tb_return_item AS b USING (turn_code)
			JOIN ".$db[$a]."_tb_item_cat AS d USING (icat_midx)
			JOIN ".$db[$a]."_tb_item AS c USING (it_code)
		WHERE ". $strWhere[$j]." AND turn_dept = '$i' AND turn_return_condition != 1
		GROUP BY type, inv, item_cat
	UNION
		SELECT 
			2 AS type, turn_code AS inv,
			null AS total_invoice_before_vat, 
			sum(turn_delivery_freight_charge)*-1 AS total_invoice, 
			3 AS item_cat 
		FROM 
			".$db[$a]."_tb_return AS a
		WHERE ". $strWhere[$j++]." AND turn_delivery_freight_charge > 0 AND turn_dept = '$i'
		GROUP BY type, inv, item_cat 
	";

	$sql_bill[$a] = "
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
			".$db[$a]."_tb_billing AS a
			JOIN ".$db[$a]."_tb_billing_item AS b USING (bill_code)
			JOIN ".$db[$a]."_tb_item_cat AS d USING (icat_midx)
			JOIN ".$db[$a]."_tb_item AS c USING (it_code)
		WHERE ". $strWhere[$k]." AND bill_dept = '$i'
		GROUP BY inv, item_cat
	UNION
		SELECT 
			bill_code AS inv,
			null AS total_before_vat, 
			sum(bill_delivery_freight_charge) AS total_billing, 
			3 AS item_cat 
		FROM ".$db[$a]."_tb_billing AS a 
		WHERE ". $strWhere[$k++]." AND bill_delivery_freight_charge > 0 AND bill_dept = '$i'
		GROUP BY inv, item_cat	
		";
	$k++;

	$sql_bill_realtime[$a] = "
		SELECT
			bill_code AS inv,
			trunc(sum(((reit_qty * reit_unit_price)* (1+turn_vat/100)) * (1-turn_discount/100)),2)*-1 AS total_return,
			CASE
				WHEN c.icat_midx IN($catAK) THEN 0
				WHEN c.icat_midx IN($catAD) THEN 1
				ELSE 2
			END AS item_cat
		FROM
			".$db[$a]."_tb_billing
			JOIN ".$db[$a]."_tb_return ON bill_code = turn_bill_code
			JOIN ".$db[$a]."_tb_return_item AS b USING (turn_code)
			JOIN ".$db[$a]."_tb_item_cat AS d USING (icat_midx)
			JOIN ".$db[$a]."_tb_item AS c USING (it_code)
		WHERE ". $strWhere[$l]." AND bill_dept = '$i' AND turn_return_condition NOT IN (1)
		GROUP BY inv, item_cat
	UNION
		SELECT 
			bill_code AS inv,
			sum(turn_delivery_freight_charge)*-1 AS total_return, 
			3 AS item_cat 
		FROM 
			".$db[$a]."_tb_billing AS a 
			JOIN ".$db[$a]."_tb_return ON bill_code = turn_bill_code
		WHERE ". $strWhere[$l++]." AND turn_delivery_freight_charge > 0 AND bill_dept = '$i'
		GROUP BY inv, item_cat 	
		";
	$l++;
}

switch (ZKP_URL) {
  case "ALL":
	$sql 				= $sql[0] . " UNION " . $sql[1];
	$sql_bill			= $sql_bill[0] . " UNION " . $sql_bill[1];
	$sql_bill_realtime	= $sql_bill_realtime[0] . " UNION " . $sql_bill_realtime[1];
	break;
  case "IDC":
	$sql 				= $sql[0];
	$sql_bill			= $sql_bill[0];
	$sql_bill_realtime	= $sql_bill_realtime[0];
	break;
  case "MED":
	$sql 				= $sql[1];
	$sql_bill			= $sql_bill[1];
	$sql_bill_realtime	= $sql_bill_realtime[1];
	break;	
  case "MEP":
	$sql 				= $sql[0];
	$sql_bill			= $sql_bill[0];
	$sql_bill_realtime	= $sql_bill_realtime[0];
	break;
}

$sql				.= " ORDER BY inv, item_cat";
$sql_bill			.= " ORDER BY inv, item_cat";
$sql_bill_realtime	.= " ORDER BY inv, item_cat";
/*
echo "<pre>";
echo $sql ."<br />". $sql_bill ."<br />". $sql_bill_realtime ."<br /><br /><br />";
echo "</pre>";
exit;
*/
?>