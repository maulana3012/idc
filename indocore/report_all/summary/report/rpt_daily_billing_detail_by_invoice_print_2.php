<?php
$db = array("idc", "med");
$j = 0;
$s_page[0] = array('idc'=>array(1=>'idc',2=>'mep'), 'med'=>array(1=>'med',2=>'smd'));
$s_page[1] = array('A'=>'apotik', 'D'=>'dealer', 'H'=>'hospital', 'M'=>'marketing', 'P'=>'pharmaceutical', 'T'=>'tender');

for($i=0; $i<2; $i++) {

    $sql_bill[$i] .= "
      substr(b.bill_code,1,2) as initial,
      b.bill_ship_to AS ship_to,
      b.bill_ship_to_name AS ship_to_name,
      b.bill_code AS invoice_code,
      to_char(b.bill_inv_date, 'dd-Mon-YY') AS invoice_issue_date,
      to_char(b.bill_payment_giro_due, 'dd-Mon-YY') AS invoice_due_date,
      to_char(b.bill_sales_from, 'dd-Mon-YY') AS invoice_sales_from,
      to_char(b.bill_sales_to, 'dd-Mon-YY') AS invoice_sales_to,
      b.bill_delivery_freight_charge AS freight_charge,
      b.bill_discount AS discount,
      bi.biit_idx AS it_idx,
      it.it_code AS it_code,
      it.it_model_no AS it_model_no,
      TRUNC(bi.biit_unit_price * (1 - b.bill_discount/100),2) AS unit_price,
      bi.biit_qty AS qty,
      TRUNC((bi.biit_qty * bi.biit_unit_price * (1 - b.bill_discount/100)),2) AS amount,
      TRUNC((b.bill_vat/100)*(bi.biit_qty * bi.biit_unit_price * (1 - b.bill_discount/100)),2) AS vat,
      bill_total_billing AS grand_total,
      b.bill_inv_date AS invoice_date,
      'billing' AS invoice_condition,
      CASE
        WHEN b.bill_cfm_tukar_faktur IS NOT NULL AND b.bill_remain_amount > 0 AND b.bill_payment_giro_due > CURRENT_TIMESTAMP THEN 'bill_before_due_tf'
        WHEN b.bill_cfm_tukar_faktur IS NOT NULL AND b.bill_remain_amount > 0 AND b.bill_payment_giro_due < CURRENT_TIMESTAMP THEN 'bill_over_due_tf'
        WHEN b.bill_cfm_tukar_faktur IS NOT NULL AND b.bill_remain_amount <= 0 THEN 'bill_paid_tf'
        WHEN b.bill_total_billing = 0 THEN 'bill_before_due'
        WHEN b.bill_remain_amount <= 0 THEN 'bill_paid'
        WHEN b.bill_remain_amount > 0 AND b.bill_payment_giro_due > CURRENT_TIMESTAMP THEN 'bill_before_due'
        WHEN b.bill_remain_amount > 0 AND b.bill_payment_giro_due < CURRENT_TIMESTAMP THEN 'bill_over_due'
      END AS invoice_layout_general,
      CASE
        WHEN b.bill_cfm_tukar_faktur IS NOT NULL AND b.bill_remain_amount > 0 AND b.bill_payment_giro_due > CURRENT_TIMESTAMP THEN 'bill_before_due_tf'
        WHEN b.bill_cfm_tukar_faktur IS NOT NULL AND b.bill_remain_amount > 0 AND b.bill_payment_giro_due < CURRENT_TIMESTAMP THEN 'bill_over_due_tf'
        WHEN b.bill_cfm_tukar_faktur IS NOT NULL AND b.bill_remain_amount <= 0 THEN 'bill_paid_tf'
        WHEN b.bill_total_billing = 0 THEN 'bill_before_due'
        WHEN b.bill_remain_amount <= 0 THEN 'bill_paid'
        WHEN b.bill_remain_amount > 0 AND b.bill_payment_giro_due > CURRENT_TIMESTAMP THEN 'bill_before_due'
        WHEN b.bill_remain_amount > 0 AND b.bill_payment_giro_due < CURRENT_TIMESTAMP THEN 'bill_over_due'
      END AS invoice_layout_qty,
      CASE
        WHEN b.bill_cfm_tukar_faktur IS NOT NULL AND b.bill_remain_amount > 0 AND b.bill_payment_giro_due > CURRENT_TIMESTAMP THEN 'bill_before_due_tf'
        WHEN b.bill_cfm_tukar_faktur IS NOT NULL AND b.bill_remain_amount > 0 AND b.bill_payment_giro_due < CURRENT_TIMESTAMP THEN 'bill_over_due_tf'
        WHEN b.bill_cfm_tukar_faktur IS NOT NULL AND b.bill_remain_amount <= 0 THEN 'bill_paid_tf'
        WHEN b.bill_total_billing = 0 THEN 'bill_before_due'
        WHEN b.bill_remain_amount <= 0 THEN 'bill_paid'
        WHEN b.bill_remain_amount > 0 AND b.bill_payment_giro_due > CURRENT_TIMESTAMP THEN 'bill_before_due'
        WHEN b.bill_remain_amount > 0 AND b.bill_payment_giro_due < CURRENT_TIMESTAMP THEN 'bill_over_due'
      END AS invoice_layout_amount,
      CASE 
        WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'idc' THEN 'idc_'||b.bill_code
        WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'idc' THEN 'mep_'||b.bill_code
        WHEN b.bill_ordered_by = 1 AND '".$db[$i]."' = 'med' THEN 'med_'||b.bill_code
        WHEN b.bill_ordered_by = 2 AND '".$db[$i]."' = 'med' THEN 'smd_'||b.bill_code
      END AS invoice_source,
      '".$db[$i]."' || '--' || b.bill_ordered_by || '--' || b.bill_dept || '--' || 'billing/revise_billing.php?_code='||bill_code AS go_page
    FROM
     ".$db[$i]."_tb_customer AS c
     JOIN ".$db[$i]."_tb_billing AS b ON bill_ship_to = c.cus_code
     JOIN ".$db[$i]."_tb_billing_item AS bi USING(bill_code)
     JOIN ".$db[$i]."_tb_item_cat AS icat USING(icat_midx)
     JOIN ".$db[$i]."_tb_item AS it USING(it_code)
    WHERE " . $strWhere[$j++] ;
    
    $sql_return[$i] .= "
      substr(t.turn_code,1,2) as initial,
      t.turn_ship_to AS ship_to,
      t.turn_ship_to_name AS ship_to_name,
      t.turn_code AS invoice_code,
      to_char(t.turn_return_date, 'dd-Mon-YY') AS invoice_issued_date,
      to_char(t.turn_payment_giro_due, 'dd-Mon-YY') AS invoice_due_date,
      NULL AS sales_from,
      NULL AS sales_to,
      t.turn_delivery_freight_charge AS freight_charge,
      t.turn_discount AS discount,
      reit_idx AS it_idx,
      it.it_code AS it_code,
      it.it_model_no AS it_model_no,
      TRUNC(ti.reit_unit_price * (1 - t.turn_discount/100),2) AS unit_price,
      ti.reit_qty AS qty,
      TRUNC((ti.reit_qty * ti.reit_unit_price * (1 - t.turn_discount/100)),2) AS amount,
      TRUNC((t.turn_vat/100)*(ti.reit_qty * ti.reit_unit_price * (1 - t.turn_discount/100)),2) AS vat,
      turn_total_return AS grand_total,
      t.turn_return_date AS invoice_date,
      CASE
        WHEN t.turn_return_condition = 1 THEN 'turn_counted' 
        WHEN t.turn_return_condition = 2 THEN 'turn_counted'
        WHEN t.turn_return_condition = 3 THEN 'turn_counted'
        WHEN t.turn_return_condition = 4 THEN 'turn_counted'
      END AS invoice_condition,
      'turn_counted' AS invoice_layout_general,
      'turn_counted' AS invoice_layout_qty,
      CASE
        WHEN t.turn_return_condition = 1 THEN 'turn_uncounted'
        ELSE 'turn_counted'
      END AS invoice_layout_amount,
      CASE 
        WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'idc' THEN 'idc_'||t.turn_code
        WHEN t.turn_ordered_by = 2 AND '".$db[$i]."' = 'idc' THEN 'mep_'||t.turn_code
        WHEN t.turn_ordered_by = 1 AND '".$db[$i]."' = 'med' THEN 'med_'||t.turn_code
        WHEN t.turn_ordered_by = 2 AND '".$db[$i]."' = 'med' THEN 'smd_'||t.turn_code
      END AS invoice_source,
      '".$db[$i]."' || '--' || t.turn_ordered_by || '--' || t.turn_dept || '--' || 'billing/revise_billing.php?_code='||turn_code AS go_page
    FROM
     ".$db[$i]."_tb_customer AS c
     JOIN ".$db[$i]."_tb_return AS t ON t.turn_ship_to = c.cus_code
     JOIN ".$db[$i]."_tb_return_item AS ti USING(turn_code)
     JOIN ".$db[$i]."_tb_item_cat AS icat USING(icat_midx)
     JOIN ".$db[$i]."_tb_item AS it USING(it_code)
    WHERE " . $strWhere[$j++] ;
    
    $sql_dr[$i] .= "
      substr(b.dr_code,1,2) as initial,
      b.dr_ship_to AS ship_to,
      b.dr_ship_name AS ship_to_name,
      b.dr_code AS invoice_code,
      to_char(b.dr_issued_date, 'dd-Mon-YY') AS invoice_issue_date,
      null AS invoice_due_date,
      null AS invoice_sales_from,
      null AS invoice_sales_to,
      null AS freight_charge,
      null AS discount,
      bi.drit_idx AS it_idx,
      it.it_code AS it_code,
      it.it_model_no AS it_model_no,
      null AS unit_price,
      bi.drit_qty AS qty,
      null AS amount,
      null AS vat,
      null AS grand_total,
      b.dr_issued_date AS invoice_date,
      'billing' AS invoice_condition,
      'bill_before_due' AS invoice_layout_general,
      'bill_before_due' AS invoice_layout_qty,
      'bill_before_due' AS invoice_layout_amount,
      CASE 
        WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'idc' THEN 'idc_'||b.dr_code
        WHEN b.dr_ordered_by = 2 AND '".$db[$i]."' = 'idc' THEN 'mep_'||b.dr_code
        WHEN b.dr_ordered_by = 1 AND '".$db[$i]."' = 'med' THEN 'med_'||b.dr_code
        WHEN b.dr_ordered_by = 2 AND '".$db[$i]."' = 'med' THEN 'smd_'||b.dr_code
      END AS invoice_source,
      '".$db[$i]."' || '--' || b.dr_ordered_by || '--' || b.dr_dept || '--' || 'billing/revise_billing.php?_code='||dr_code AS go_page
    FROM
     ".$db[$i]."_tb_customer AS c
     JOIN ".$db[$i]."_tb_dr AS b ON dr_ship_to = c.cus_code
     JOIN ".$db[$i]."_tb_dr_item AS bi USING(dr_code)
     JOIN ".$db[$i]."_tb_item AS it USING(it_code)
     JOIN ".$db[$i]."_tb_item_cat AS icat USING(icat_midx)
    WHERE " . $strWhere[$j++];

}

switch (ZKP_URL) {
  case "ALL":
    $sql =  $sql_bill[0] . " UNION " . $sql_return[0] . " UNION " .  $sql_dr[0] . " UNION " . 
            $sql_bill[1] . " UNION " . $sql_return[1] . " UNION " .  $sql_dr[1];
    break;
  case "IDC":
    $sql =  $sql_bill[0] . " UNION " . $sql_return[0] . " UNION " .  $sql_dr[0];
    break;
  case "MED":
    $sql =  $sql_bill[1] . " UNION " . $sql_return[1] . " UNION " .  $sql_dr[1];
    break;  
  case "MEP":
    $sql =  $sql_bill[0] . " UNION " . $sql_return[0] . " UNION " .  $sql_dr[0];
    break;
}

$sql .= " ORDER BY initial, invoice_date, invoice_code, it_code";
/*
echo "<pre>";
var_dump($sql);
echo "</pre>";
*/
// raw data
$rd = array();
$rdIdx  = 0;
$cache  = array("","","");
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

    $rd[] = array(
        $col['cug_name'],               //0
        $col['ship_to'],                //1
        $col['ship_to_name'],           //2
        $col['invoice_code'],           //3
        $col['invoice_issue_date'],     //4
        $col['invoice_due_date'],       //5
        $col['invoice_sales_from'],     //6
        $col['invoice_sales_to'],       //7
        $col['freight_charge'],         //8
        $col['discount'],               //9
        $col['it_idx'],                 //10
        $col['it_code'],                //11
        $col['it_model_no'],            //12
        $col['unit_price'],             //13
        $col['qty'],                    //14
        $col['amount'],                 //15
        $col['vat'],                    //16
        $col['amount']+$col['vat'],     //17
        $col['grand_total'],            //18
        $col['invoice_condition'],      //19
        $col['invoice_layout_general'], //20
        $col['invoice_layout_qty'],     //21
        $col['invoice_layout_amount'],  //22
        $col['go_page'],                //23
        $col['go_page'],                //24
        $col['invoice_source'],         //25
        $col['initial']                 //26
        
    );

    //1st grouping
    if($cache[0] != $col['initial']) {
        $cache[0] = $col['initial'];
        $group0[$col['initial']] = array();
    }

    if($cache[1] != $col['invoice_code']) {
        $cache[1] = $col['invoice_code'];
        $group0[$col['initial']][$col['invoice_code']] = array();
    }

    if($cache[2] != $col['it_idx']) {
        $cache[2] = $col['it_idx'];
    }

    $group0 [$col['initial']] [$col['invoice_code']] [$col['it_idx']] = 1;
}
/*
echo "<pre>";
var_dump($group0);
echo "</pre>";
exit;
*/
?>