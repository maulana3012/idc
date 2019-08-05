<?php
$db = array("idc", "med");
$j = 0; $k = 0; 
for($i=0; $i<2; $i++) {

	$sql_bill[$i] = "
	SELECT
	  icat.icat_pidx AS icat_pidx,
	  it.icat_midx AS icat_midx,
	  it.it_code AS it_code,
	  it.it_model_no AS it_model_no,
	  c.cus_city AS cus_city
	FROM
	  ".$db[$i]."_tb_customer AS c
	  JOIN ".$db[$i]."_tb_billing AS b ON c.cus_code = b.bill_ship_to
	  JOIN ".$db[$i]."_tb_billing_item AS bi USING(bill_code) 
	  JOIN ".$db[$i]."_tb_item_cat AS icat USING(icat_midx)
	  JOIN ".$db[$i]."_tb_item AS it USING(it_code)
	WHERE ". $strWhere[$j++]."
	GROUP BY icat.icat_pidx, it.icat_midx, it.it_code, it.it_model_no, c.cus_city
	";
	
	$sql_return[$i] = "
	SELECT
	  icat.icat_pidx AS icat_pidx,
	  it.icat_midx AS icat_midx,
	  it.it_code AS it_code,
	  it.it_model_no AS it_model_no,
	  c.cus_city AS cus_city
	FROM
	  ".$db[$i]."_tb_customer AS c
	  JOIN ".$db[$i]."_tb_return AS b ON c.cus_code = b.turn_ship_to
	  JOIN ".$db[$i]."_tb_return_item AS ti USING(turn_code) 
	  JOIN ".$db[$i]."_tb_item_cat AS icat USING(icat_midx)
	  JOIN ".$db[$i]."_tb_item AS it USING(it_code)
	WHERE ". $strWhere[$j++]."
	GROUP BY icat.icat_pidx, it.icat_midx, it.it_code, it.it_model_no, c.cus_city
	";
	
	$sql_dr[$i] = "
	SELECT
	  icat.icat_pidx AS icat_pidx,
	  it.icat_midx AS icat_midx,
	  it.it_code AS it_code,
	  it.it_model_no AS it_model_no,
	  c.cus_city AS cus_city
	FROM
	  ".$db[$i]."_tb_customer AS c
	  JOIN ".$db[$i]."_tb_dr AS b ON c.cus_code = b.dr_ship_to
	  JOIN ".$db[$i]."_tb_dr_item AS bi USING(dr_code) 
	  JOIN ".$db[$i]."_tb_item AS it USING(it_code)
	  JOIN ".$db[$i]."_tb_item_cat AS icat USING(icat_midx)
	WHERE ". $strWhere[$j++]."
	GROUP BY icat.icat_pidx, it.icat_midx, it.it_code, it.it_model_no, c.cus_city
	";
	
// ------------------------------------------------------------------------------------------

	$sql_area_tmp[$i] = "
	SELECT
	  bill_code AS invoice,
	  icat.icat_pidx AS icat_pidx,
	  it.icat_midx AS icat_midx,
	  it.it_code AS it_code,
	  it.it_model_no AS it_model_no,
	  CASE 
		WHEN c.cus_city IS NOT NULL THEN c.cus_city
		ELSE 'Undefined'
	  END AS cus_city ,
	  TRUNC(bi.biit_qty) AS qty,
	  TRUNC((bi.biit_qty * bi.biit_unit_price * (1 - b.bill_discount/100)),2) AS amount
	FROM
	  ".$db[$i]."_tb_customer AS c
	  JOIN ".$db[$i]."_tb_billing AS b ON c.cus_code = b.bill_ship_to
	  JOIN ".$db[$i]."_tb_billing_item AS bi USING(bill_code) 
	  JOIN ".$db[$i]."_tb_item_cat AS icat USING(icat_midx)
	  JOIN ".$db[$i]."_tb_item AS it USING(it_code)
	WHERE ". $strWhere[$k++]."
			UNION
	SELECT
	  turn_code AS invoice,
	  icat.icat_pidx AS icat_pidx,
	  it.icat_midx AS icat_midx,
	  it.it_code AS it_code,
	  it.it_model_no AS it_model_no,
	  CASE 
		WHEN c.cus_city IS NOT NULL THEN c.cus_city
		ELSE 'Undefined'
	  END AS cus_city ,
	  TRUNC(ti.reit_qty)*-1 AS qty,
	  CASE 
		WHEN turn_return_condition=1 THEN null
		ELSE TRUNC((ti.reit_qty * ti.reit_unit_price * (1 - b.turn_discount/100)),2)*-1 
	  END AS amount
	FROM
	  ".$db[$i]."_tb_customer AS c
	  JOIN ".$db[$i]."_tb_return AS b ON c.cus_code = b.turn_ship_to
	  JOIN ".$db[$i]."_tb_return_item AS ti USING(turn_code) 
	  JOIN ".$db[$i]."_tb_item_cat AS icat USING(icat_midx)
	  JOIN ".$db[$i]."_tb_item AS it USING(it_code)
	WHERE ". $strWhere[$k++]."
			UNION
	SELECT
	  dr_code AS invoice,
	  icat.icat_pidx AS icat_pidx,
	  it.icat_midx AS icat_midx,
	  it.it_code AS it_code,
	  it.it_model_no AS it_model_no,
	  CASE 
		WHEN c.cus_city IS NOT NULL THEN c.cus_city
		ELSE 'Undefined'
	  END AS cus_city ,
	  bi.drit_qty AS qty,
	  null AS amount
	FROM
	  ".$db[$i]."_tb_customer AS c
	  JOIN ".$db[$i]."_tb_dr AS b ON c.cus_code = b.dr_ship_to
	  JOIN ".$db[$i]."_tb_dr_item AS bi USING(dr_code) 
	  JOIN ".$db[$i]."_tb_item AS it USING(it_code)
	  JOIN ".$db[$i]."_tb_item_cat AS icat USING(icat_midx)
	WHERE ". $strWhere[$k++] ;

}

switch (ZKP_URL) {
  case "ALL":
	$sql =	$sql_bill[0] . " UNION " . $sql_return[0] . " UNION " .  $sql_dr[0] . " UNION " . 
			$sql_bill[1] . " UNION " . $sql_return[1] . " UNION " .  $sql_dr[1];
	$sql_area = $sql_area_tmp[0] . " UNION " . $sql_area_tmp[1];
	break;
  case "IDC":
	$sql =	$sql_bill[0] . " UNION " . $sql_return[0] . " UNION " .  $sql_dr[0];
	$sql_area = $sql_area_tmp[0];
	break;
  case "MED":
	$sql =	$sql_bill[1] . " UNION " . $sql_return[1] . " UNION " .  $sql_dr[1];
	$sql_area = $sql_area_tmp[1];
	break;	
  case "MEP":
	$sql =	$sql_bill[0] . " UNION " . $sql_return[0] . " UNION " .  $sql_dr[0];
	$sql_area = $sql_area_tmp[0];
	break;
}

$sql		.= " ORDER BY icat_pidx, icat_midx,it_code,it_model_no,cus_city ";
$sql_area	.= " ORDER BY icat_pidx, icat_midx,it_code,it_model_no,cus_city ";
/*
echo "<pre>";
var_dump($sql_area);
echo "</pre>";
*/
// raw data
$rd		= array();
$rdIdx	= 0;
$cache	= array("","","");
$group0 = array();
$res 	=& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_pidx'],				//0
		$col['icat_midx'],				//1
		$col['it_code'],				//2
		$col['it_model_no'],			//3
		$col['cus_city']				//4
	);

	//1st grouping
	if($cache[0] != $col['icat_pidx']) {
		$cache[0] = $col['icat_pidx'];
		$group0[$col['icat_pidx']] = array();
	}
	
	if($cache[1] != $col['icat_midx'].'-'.$col['it_code']) {
		$cache[1] = $col['icat_midx'].'-'.$col['it_code'];
		$group0[$col['icat_pidx']][$col['icat_midx'].'-'.$col['it_code']] = array();
	}

	if($cache[2] != $col['cus_city']) {
		$cache[2] = $col['cus_city'];
	}

	$group0[$col['icat_pidx']][$col['icat_midx'].'-'.$col['it_code']][$col['cus_city']] = 1;
}

$tot = array();
$cit = array();
$res =& query($sql_area);
while($col =& fetchRowAssoc($res)) {
	$rd[] = array(
		$col['it_code'],$col['cus_city'],$col['qty'],$col['amount']
	);

	if(isset($tot[$col['it_code']][$col['cus_city']][0])) {
		$tot[$col['it_code']][$col['cus_city']][0] += $col['qty'];
		$tot[$col['it_code']][$col['cus_city']][1] += $col['amount'];
	} else {
		$tot[$col['it_code']][$col['cus_city']][0] = $col['qty'];
		$tot[$col['it_code']][$col['cus_city']][1] = $col['amount'];
	}

	if(!isset($cit[$col['cus_city']])) {
		$cit[$col['cus_city']] = $col['cus_city'];
	}

}
sort($cit);
?>