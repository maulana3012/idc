<?php
$db = array("idc", "med");
$l = 0;
$sql_perMonth = array();
for($m=0; $m<2; $m++) {

	$whereBillMonth		= ($strWhereMonth[$l]=='') ? "" : $strWhereMonth[$l] . " AND "; $l++;
	$whereSalesMonth	= ($strWhereMonth[$l]=='') ? "" : $strWhereMonth[$l] . " AND "; $l++;
	$whereTurnMonth		= ($strWhereMonth[$l]=='') ? "" : $strWhereMonth[$l] . " AND "; $l++;

	$sql_perMonth[$m] = 
		"
		SELECT trunc(sum(biit_qty * (biit_unit_price*(100-bill_discount)/100))) AS amount, 'bill-$m' as source
		FROM
			".$db[$m]."_tb_billing
			JOIN ".$db[$m]."_tb_billing_item USING(bill_code)
			JOIN ".$db[$m]."_tb_item_cat AS icat USING(icat_midx)
			JOIN ".$db[$m]."_tb_item USING(it_code)
		WHERE $whereBillMonth bill_dept='".$rd[$rdIdx][0]."' AND bill_ship_to='".trim($rd[$rdIdx][2])."' AND bill_inv_date ".$period_month[$k]." 
	  UNION
		SELECT sum(sl_qty * sl_payment_price) AS amount, 'sales-$m' as source
		FROM
			".$db[$m]."_tb_billing AS b
			JOIN ".$db[$m]."_tb_billing_sales AS s USING(bill_code)
			JOIN ".$db[$m]."_tb_sales_log AS c USING(cus_code)
			JOIN ".$db[$m]."_tb_item AS it USING(it_code)
			JOIN ".$db[$m]."_tb_item_cat AS icat USING(icat_midx)
		WHERE $whereSalesMonth bill_dept='".$rd[$rdIdx][0]."' AND s.cus_code='".trim($rd[$rdIdx][2])."' AND bill_inv_date ".$period_month[$k]." 
			  AND sl_date=s.bisl_date /*AND sl_faktur_no=s.bisl_sl_faktur_no AND sl_lop_no=s.bisl_lop_no*/
	  UNION
		SELECT trunc(sum(reit_qty * (reit_unit_price*(100-turn_discount)/100)))*-1 AS amount, 'turn-$m' as source
		FROM
			".$db[$m]."_tb_return
			JOIN ".$db[$m]."_tb_return_item USING(turn_code)
			JOIN ".$db[$m]."_tb_item_cat AS icat USING(icat_midx)
			JOIN ".$db[$m]."_tb_item USING(it_code)
		WHERE $whereTurnMonth turn_dept='".$rd[$rdIdx][0]."' AND turn_ship_to='".trim($rd[$rdIdx][2])."' AND turn_return_date ".$period_month[$k]."
		";

}

switch (ZKP_URL) {
  case "ALL": $sql_perMonth = $sql_perMonth[0] . " UNION " . $sql_perMonth[1]; break;
  case "IDC": $sql_perMonth = $sql_perMonth[0]; break;
  case "MED": $sql_perMonth = $sql_perMonth[1]; break;
  case "MEP": $sql_perMonth = $sql_perMonth[0]; break;
}
/*
echo "<pre>";
var_dump($sql_perMonth);
echo "</pre>";
exit;
*/
?>