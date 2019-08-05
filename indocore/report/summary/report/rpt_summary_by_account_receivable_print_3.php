<?php
$db = array("idc", "med");
$j = 0;
$sql = array();
for($i=0; $i<2; $i++) {
	$sql[$i] = "
		SELECT 
			c.cus_channel AS channel,
			p.pay_method AS method, 
			p.pay_bank AS bank,
			sum(p.pay_paid) AS amount
		FROM ".$db[$i]."_tb_customer AS c JOIN ".$db[$i]."_tb_billing ON bill_cus_to=cus_code JOIN ".$db[$i]."_tb_payment AS p USING(bill_code)
		WHERE ". $strWhere[$j++] ."
		GROUP BY channel, method, bank
			UNION
		SELECT 
			c.cus_channel AS channel,
			p.pay_method AS method, 
			p.pay_bank AS bank,
			sum(p.pay_paid) AS amount
		FROM ".$db[$i]."_tb_customer AS c JOIN ".$db[$i]."_tb_billing ON bill_cus_to=cus_code JOIN ".$db[$i]."_tb_payment AS p USING(bill_code)
		WHERE ". $strWhere[$j++] ."
		GROUP BY channel, method, bank
			UNION
		SELECT 
			c.cus_channel AS channel,
			p.svpay_method AS method, 
			p.svpay_bank AS bank, 
			sum(p.svpay_paid) AS amount
		FROM ".$db[$i]."_tb_customer AS c JOIN ".$db[$i]."_tb_service_payment AS p USING(cus_code)
		WHERE ". $strWhere[$j++] ."
		GROUP BY channel, method, bank
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
echo $sql;
echo "</pre>";
//exit;
*/
?>