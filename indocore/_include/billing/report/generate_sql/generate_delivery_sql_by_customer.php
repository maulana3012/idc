<?php
$sql_billing = "
SELECT
  icat.icat_pidx AS icat_pidx,
  it.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  bill.bill_code AS invoice_code,
  bill.bill_inv_date AS inv_date,
  to_char(bill.bill_inv_date, 'dd-Mon-yy') AS invoice_date,
  to_char(bill_delivery_date, 'dd-Mon-YY') AS delivery_date,
  bill_delivery_to_customer_by AS delivery_by,
  cus.cus_code AS cus_code,
  cus.cus_full_name AS cus_full_name,
  biit.biit_idx AS it_idx,
  biit.biit_qty AS it_qty,
  biit.biit_unit_price * (1 - bill.bill_discount/100) AS it_unit_price,
  (biit.biit_qty * biit.biit_unit_price * (1 - bill.bill_discount/100)) AS it_amount,
  (bill.bill_vat/100)*(biit.biit_qty * biit.biit_unit_price * (1 - bill.bill_discount/100)) AS it_vat,
  (biit.biit_qty * biit.biit_unit_price * (1 - bill.bill_discount/100)) + (bill.bill_vat/100)*(biit.biit_qty * biit.biit_unit_price * (1 - bill.bill_discount/100)) AS it_grand_total,
  '../billing/revise_billing.php?_code='||bill_code AS go_page,
  'black' AS coloring_general,
  'black' AS coloring_qty,
  'black' AS coloring_amount
FROM
  ".ZKP_SQL."_tb_customer AS cus
  JOIN ".ZKP_SQL."_tb_billing AS bill ON cus.cus_code = bill.bill_ship_to
  JOIN ".ZKP_SQL."_tb_billing_item AS biit USING(bill_code) 
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE ". $strWhereBilling;

$sql_return = "
SELECT
  icat.icat_pidx AS icat_pidx,
  it.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  turn.turn_code AS invoice_code,
  turn.turn_return_date AS inv_date,
  to_char(turn.turn_return_date, 'dd-Mon-yy') AS invoice_date,
  to_char(turn.turn_return_date, 'dd-Mon-yy') AS delivery_date,
  null AS delivery_by,
  cus.cus_code AS cus_code,
  cus.cus_full_name AS cus_full_name,
  reit.reit_idx AS it_idx,
  reit.reit_qty AS it_qty,
  (reit.reit_unit_price * (1 - turn.turn_discount/1000)) AS it_unit_price,
  (reit.reit_qty * reit.reit_unit_price * (1 - turn.turn_discount/100)) AS it_amount,
  (turn.turn_vat/100)*(reit.reit_qty * reit.reit_unit_price * (1 - turn.turn_discount/100)) AS it_vat,
  (reit.reit_qty * reit.reit_unit_price * (1 - turn.turn_discount/100)) + (turn.turn_vat/100)*(reit.reit_qty * reit.reit_unit_price * (1 - turn.turn_discount/100)) AS it_grand_total,
  '../billing/revise_return.php?_code='||turn_code AS go_page,
  'red' AS coloring_general,
  'red' AS coloring_qty,
  CASE
	WHEN turn_return_condition = 1 THEN 'grey'
	ELSE 'red'
  END AS coloring_amount
FROM
  ".ZKP_SQL."_tb_customer AS cus
  JOIN ".ZKP_SQL."_tb_return AS turn ON cus.cus_code = turn.turn_ship_to
  JOIN ".ZKP_SQL."_tb_return_item AS reit USING(turn_code) 
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE ". $strWhereReturn;

$sql_dt = "
SELECT
  icat.icat_pidx AS icat_pidx,
  it.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  dt.dt_code AS invoice_code,
  dt.dt_date AS inv_date,
  to_char(dt.dt_date, 'dd-Mon-yy') AS invoice_date,
  to_char(dt.dt_delivery_date, 'dd-Mon-YY') AS delivery_date,
  dt.dt_delivery_to_customer_by AS delivery_by,
  cus.cus_code AS cus_code,
  cus.cus_full_name AS cus_full_name,
  dtit_idx AS it_idx,
  dtit_qty AS it_qty,
  null AS it_unit_price,
  null AS it_amount,
  null AS it_vat,
  null AS it_grand_total,
  '../other/revise_dt.php?_code='||dt_code AS go_page,
  'black' AS coloring_general,
  'black' AS coloring_qty,
  'black' AS coloring_amount
FROM
  ".ZKP_SQL."_tb_customer AS cus
  JOIN ".ZKP_SQL."_tb_dt AS dt ON cus.cus_code = dt_cus_to
  JOIN ".ZKP_SQL."_tb_dt_item AS dtit USING(dt_code) 
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE ". $strWhereDT;

$sql_df = "
SELECT
  icat.icat_pidx AS icat_pidx,
  it.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  df.df_code AS invoice_code,
  df.df_date AS inv_date,
  to_char(df.df_date, 'dd-Mon-yy') AS invoice_date,
  to_char(df.df_delivery_date, 'dd-Mon-YY') AS delivery_date,
  df.df_delivery_to_customer_by AS delivery_by,
  cus.cus_code AS cus_code,
  cus.cus_full_name AS cus_full_name,
  dfit_idx AS it_idx,
  dfit_qty AS it_qty,
  null AS it_unit_price,
  null AS it_amount,
  null AS it_vat,
  null AS it_grand_total,
  '../other/revise_df.php?_code='||df_code AS go_page,
  'black' AS coloring_general,
  'black' AS coloring_qty,
  'black' AS coloring_amount
FROM
  ".ZKP_SQL."_tb_customer AS cus
  JOIN ".ZKP_SQL."_tb_df AS df ON cus.cus_code = df_cus_to
  JOIN ".ZKP_SQL."_tb_df_item AS dfit USING(df_code) 
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE ". $strWhereDF;

$sql_dr = "
SELECT
  icat.icat_pidx AS icat_pidx,
  it.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  dr.dr_code AS invoice_code,
  dr.dr_date AS inv_date,
  to_char(dr.dr_date, 'dd-Mon-yy') AS invoice_date,
  to_char(dr.dr_delivery_date, 'dd-Mon-YY') AS delivery_date,
  dr.dr_delivery_to_customer_by AS delivery_by,
  cus.cus_code AS cus_code,
  cus.cus_full_name AS cus_full_name,
  drit_idx AS it_idx,
  drit_qty AS it_qty,
  null AS it_unit_price,
  null AS it_amount,
  null AS it_vat,
  null AS it_grand_total,
  '../other/revise_dr.php?_code='||dr_code AS go_page,
  'black' AS coloring_general,
  'black' AS coloring_qty,
  'black' AS coloring_amount
FROM
  ".ZKP_SQL."_tb_customer AS cus
  JOIN ".ZKP_SQL."_tb_dr AS dr ON cus.cus_code = dr_cus_to
  JOIN ".ZKP_SQL."_tb_dr_item AS drit USING(dr_code) 
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE ". $strWhereDR;

$sql_rt = "
SELECT
  icat.icat_pidx AS icat_pidx,
  it.icat_midx AS icat_midx,
  it.it_code AS it_code,
  it.it_model_no AS it_model_no,
  rdt.rdt_code AS invoice_code,
  rdt.rdt_date AS inv_date,
  to_char(rdt.rdt_date, 'dd-Mon-yy') AS invoice_date,
  to_char(rdt.rdt_date, 'dd-Mon-YY') AS delivery_date,
  null AS delivery_by,
  cus.cus_code AS cus_code,
  cus.cus_full_name AS cus_full_name,
  rdtit_idx AS it_idx,
  rdtit_qty AS it_qty,
  null AS it_unit_price,
  null AS it_amount,
  null AS it_vat,
  null AS it_grand_total,
  '../other/revise_return_dt.php?_code='||rdt_code AS go_page,
  'red' AS coloring_general,
  'red' AS coloring_qty,
  'red' AS coloring_amount
FROM
  ".ZKP_SQL."_tb_customer AS cus
  JOIN ".ZKP_SQL."_tb_return_dt AS rdt ON cus.cus_code = rdt_cus_to
  JOIN ".ZKP_SQL."_tb_return_dt_item AS rdtit USING(rdt_code) 
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE ". $strWhereRT;
?>