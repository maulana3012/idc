<?php
$db = array("idc", "med");
$l = 0;
$sql_perMonth = array();
for($k=0; $k<2; $k++) {
	if($start_month>12) { $start_month = $start_month-12; $start_year+=1;}
	$whereBillMonth = ($strWhereMonth[$l]=='') ? "" : $strWhereMonth[$l] ." AND "; $l++;
	$whereTurnMonth = ($strWhereMonth[$l]=='') ? "" : $strWhereMonth[$l] ." AND "; $l++;
	$catList = executeSP($db[$k]."_getSubCategory", $key);

	$sql_perMonth[$k] = 
		"SELECT trunc(sum(biit_qty * (biit_unit_price*(100-bill_discount)/100))) AS amount
		FROM
			".$db[$k]."_tb_billing
			JOIN ".$db[$k]."_tb_billing_item USING(bill_code)
			JOIN ".$db[$k]."_tb_item_cat AS icat USING(icat_midx)
			JOIN ".$db[$k]."_tb_item USING(it_code)
		WHERE $whereBillMonth bill_inv_date ".$period_month[$i]." AND icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")
	  UNION
		SELECT trunc(sum(reit_qty * (reit_unit_price*(100-turn_discount)/100)))*-1 AS amount
		FROM
			".$db[$k]."_tb_return
			JOIN ".$db[$k]."_tb_return_item USING(turn_code)
			JOIN ".$db[$k]."_tb_item_cat AS icat USING(icat_midx)
			JOIN ".$db[$k]."_tb_item USING(it_code)
		WHERE $whereTurnMonth turn_return_date ".$period_month[$i]." AND icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")
		";
/*
echo "<pre>";
var_dump($sql_perMonth[$k]);
echo "<br /><br />*********<br /><br />";
echo "</pre>";
exit;
*/
}

switch (ZKP_URL) {
  case "ALL": $sql_perMonth = $sql_perMonth[0] . " UNION " . $sql_perMonth[1]; break;
  case "IDC": $sql_perMonth = $sql_perMonth[0]; break;
  case "MED": $sql_perMonth = $sql_perMonth[1]; break;
  case "MEP": $sql_perMonth = $sql_perMonth[0]; break;
}

echo "<pre>";
//var_dump($sql_perMonth);
//echo $sql_perMonth . "<br /><br />*********<br /><br />";
echo "</pre>";

?>