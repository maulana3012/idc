<?php
$db = array("idc", "med");
$j = 0;
$sql = array();
for($i=0; $i<2; $i++) {
	$sql[$i] = "
		SELECT 
			p.pay_method AS method, 
			p.pay_bank AS bank,
			sum(p.pay_paid) AS amount
		FROM ".$db[$i]."_tb_customer AS c JOIN ".$db[$i]."_tb_payment AS p USING(cus_code)
		WHERE c.cus_channel = '$value' AND ". $strWhere[$j++] ."
		GROUP BY p.pay_method, p.pay_bank
			UNION
		SELECT 
			p.pay_method AS method, 
			p.pay_bank AS bank,
			sum(p.pay_paid) AS amount
		FROM ".$db[$i]."_tb_customer AS c JOIN ".$db[$i]."_tb_payment AS p USING(cus_code)
		WHERE c.cus_channel = '$value' AND ". $strWhere[$j++] ."
		GROUP BY p.pay_method, p.pay_bank
			UNION
		SELECT 
			p.svpay_method AS method, 
			p.svpay_bank AS bank, 
			sum(p.svpay_paid) AS amount
		FROM ".$db[$i]."_tb_customer AS c JOIN ".$db[$i]."_tb_service_payment AS p USING(cus_code)
		WHERE c.cus_channel = '$value' AND ". $strWhere[$j++] ."
		GROUP BY p.svpay_method, p.svpay_bank
		";
}

switch (ZKP_URL) {
  case "ALL": $sql = $sql[0] . " UNION " . $sql[1]; break;
  case "IDC": $sql = $sql[0]; break;
  case "MED": $sql = $sql[1]; break;
  case "MEP": $sql = $sql[0]; break;
}
$sql .= " ORDER BY method";
/*
echo "<pre>";
var_dump($sql);
echo "</pre>";
*/
?>