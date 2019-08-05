<?php
$sql_billing .= "
  cus.cus_code AS cus_code,
  cus.cus_full_name AS cus_full_name,
  bill_code AS invoice_code,
  bill_inv_date AS inv_date,
  to_char(bill_inv_date, 'dd-Mon-YY') AS invoice_date,
  bill_delivery_date AS deli_date,
  to_char(bill_delivery_date, 'dd-Mon-YY') AS delivery_date,
  bill_delivery_to_customer_by AS delivery_by,
  bill_discount AS discount,
  bill_delivery_freight_charge AS freight_charge,
  biit_idx AS it_idx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  TRUNC(biit.biit_unit_price * (1 - bill.bill_discount/100),2) AS it_unit_price,
  biit.biit_qty AS it_qty,
  bill_total_billing AS it_grand_total,
  TRUNC((bill.bill_vat/100)*(biit.biit_qty * biit.biit_unit_price * (1 - bill.bill_discount/100)),2) AS it_vat,
  TRUNC((biit.biit_qty * biit.biit_unit_price * (1 - bill.bill_discount/100)),2) AS it_amount,
  '../billing/revise_billing.php?_code='||bill_code AS go_page,
  'black' AS coloring_general,
  'black' AS coloring_qty,
  'black' AS coloring_amount
FROM
  ".ZKP_SQL."_tb_customer AS cus
  JOIN ".ZKP_SQL."_tb_billing AS bill ON bill.bill_ship_to = cus.cus_code
  JOIN ".ZKP_SQL."_tb_billing_item AS biit USING(bill_code)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE $strWhereBilling";

$sql_return .= "
  cus.cus_code AS cus_code,
  cus.cus_full_name AS cus_full_name,
  turn_code AS invoice_code,
  turn_return_date AS inv_date,
  to_char(turn_return_date, 'dd-Mon-YY') AS invoice_date,
  turn_return_date AS deli_date,
  to_char(turn_return_date, 'dd-Mon-YY') AS delivery_date,
  null AS delivery_by,
  null AS discount,
  null AS freight_charge,
  reit_idx AS it_idx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  TRUNC(reit.reit_unit_price * (1 - turn.turn_discount/100),2) AS it_unit_price,
  reit.reit_qty AS it_qty,
  turn_total_return AS it_grand_total,
  TRUNC((turn.turn_vat/100)*(reit.reit_qty * reit.reit_unit_price * (1 - turn.turn_discount/100)),2) AS it_vat,
  TRUNC((reit.reit_qty * reit.reit_unit_price * (1 - turn.turn_discount/100)),2) AS it_amount,
  '../billing/revise_return.php?_code='||turn_code AS go_page,
  'red' AS coloring_general,
  'red' AS coloring_qty,
  CASE
	WHEN turn_return_condition = 1 THEN 'grey'
	ELSE 'red'
  END AS coloring_amount
FROM
  ".ZKP_SQL."_tb_customer AS cus
  JOIN ".ZKP_SQL."_tb_return AS turn ON turn.turn_ship_to = cus.cus_code
  JOIN ".ZKP_SQL."_tb_return_item AS reit USING(turn_code)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE $strWhereReturn";

$sql_dt .= "
  cus.cus_code AS cus_code,
  cus.cus_full_name AS cus_full_name,
  dt_code AS invoice_code,
  dt_date AS inv_date,
  to_char(dt_date, 'dd-Mon-YY') AS invoice_date,
  dt_delivery_date AS deli_date,
  to_char(dt_delivery_date, 'dd-Mon-YY') AS delivery_date,
  dt_delivery_to_customer_by AS delivery_by,
  null AS discount,
  null AS freight_charge,
  dtit_idx AS it_idx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  null AS it_unit_price,
  dtit.dtit_qty AS it_qty,
  null AS it_grand_total,
  null AS it_vat,
  null AS it_amount,
  '../other/revise_dt.php?_code='||dt_code AS go_page,
  'black' AS coloring_general,
  'black' AS coloring_qty,
  'black' AS coloring_amount
FROM
  ".ZKP_SQL."_tb_customer AS cus
  JOIN ".ZKP_SQL."_tb_dt AS dt ON dt.dt_cus_to = cus.cus_code
  JOIN ".ZKP_SQL."_tb_dt_item AS dtit USING(dt_code)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE $strWhereDT";

$sql_df .= "
  cus.cus_code AS cus_code,
  cus.cus_full_name AS cus_full_name,
  df_code AS invoice_code,
  df_date AS inv_date,
  to_char(df_date, 'dd-Mon-YY') AS invoice_date,
  df_delivery_date AS deli_date,
  to_char(df_delivery_date, 'dd-Mon-YY') AS delivery_date,
  df_delivery_to_customer_by AS delivery_by,
  null AS discount,
  null AS freight_charge,
  dfit_idx AS it_idx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  null AS it_unit_price,
  dfit.dfit_qty AS it_qty,
  null AS it_grand_total,
  null AS it_vat,
  null AS it_amount,
  '../other/revise_df.php?_code='||df_code AS go_page,
  'black' AS coloring_general,
  'black' AS coloring_qty,
  'black' AS coloring_amount
FROM
  ".ZKP_SQL."_tb_customer AS cus
  JOIN ".ZKP_SQL."_tb_df AS df ON df.df_cus_to = cus.cus_code
  JOIN ".ZKP_SQL."_tb_df_item AS dfit USING(df_code)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE $strWhereDF";

$sql_dr .= "
  cus.cus_code AS cus_code,
  cus.cus_full_name AS cus_full_name,
  dr_code AS invoice_code,
  dr_date AS inv_date,
  to_char(dr_date, 'dd-Mon-YY') AS invoice_date,
  dr_delivery_date AS deli_date,
  to_char(dr_delivery_date, 'dd-Mon-YY') AS delivery_date,
  dr_delivery_to_customer_by AS delivery_by,
  null AS discount,
  null AS freight_charge,
  drit_idx AS it_idx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  null AS it_unit_price,
  drit.drit_qty AS it_qty,
  null AS it_grand_total,
  null AS it_vat,
  null AS it_amount,
  '../other/revise_dr.php?_code='||dr_code AS go_page,
  'black' AS coloring_general,
  'black' AS coloring_qty,
  'black' AS coloring_amount
FROM
  ".ZKP_SQL."_tb_customer AS cus
  JOIN ".ZKP_SQL."_tb_dr AS dr ON dr.dr_cus_to = cus.cus_code
  JOIN ".ZKP_SQL."_tb_dr_item AS drit USING(dr_code)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE $strWhereDR";

$sql_rt .= "
  cus.cus_code AS cus_code,
  cus.cus_full_name AS cus_full_name,
  rdt_code AS invoice_code,
  rdt_date AS inv_date,
  to_char(rdt_date, 'dd-Mon-YY') AS invoice_date,
  rdt_date AS deli_date,
  to_char(rdt_date, 'dd-Mon-YY') AS delivery_date,
  null AS delivery_by,
  null AS discount,
  null AS freight_charge,
  rdtit_idx AS it_idx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  null AS it_unit_price,
  rdtit.rdtit_qty AS it_qty,
  null AS it_grand_total,
  null AS it_vat,
  null AS it_amount,
  '../other/revise_return_dt.php?_code='||rdt_code AS go_page,
  'red' AS coloring_general,
  'red' AS coloring_qty,
  'red' AS coloring_amount
FROM
  ".ZKP_SQL."_tb_customer AS cus
  JOIN ".ZKP_SQL."_tb_return_dt AS rdt ON rdt.rdt_cus_to = cus.cus_code
  JOIN ".ZKP_SQL."_tb_return_dt_item AS rdtit USING(rdt_code)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE $strWhereRT";
?>