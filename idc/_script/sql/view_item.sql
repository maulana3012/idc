CREATE OR REPLACE VIEW vw_daily_order_summary_by_group AS
SELECT
	c.cug_code,
	o.ord_po_date,
	sum(i.odit_ok_qty) AS ok_qty,
	sum(i.odit_oo_qty) AS oo_qty,
	sum(i.odit_ok_qty * i.odit_unit_price) AS ok_amount,
	sum(i.odit_oo_qty * i.odit_unit_price) AS oo_amount
FROM ".ZKP_SQL."_tb_customer c JOIN ".ZKP_SQL."_tb_order o ON (c.cus_code = o.ord_cus_to)
	JOIN ".ZKP_SQL."_tb_order_item i ON (o.ord_code = i.ord_code)
GROUP BY 1,2;

SELECT cug_name, sum(dos.ok_qty), sum(oo_qty), sum(ok_amount), sum(oo_amount)
FROM ".ZKP_SQL."_tb_customer_group AS g LEFT JOIN vw_daily_order_summary_by_group AS dos USING(cug_code)
GROUP BY cug_name;

-- 아포틱 그룹명, 아포틱 명, 판매일자, 제품번호, 수량, 가격1,합계1, 가격2, 합계2,  차액, 비고,
SELECT
cug_name,
cus_full_name,
ilog_date,
it_model_no,
ilog_sales,
ilog_retailer_price,
(ilog_sales * ilog_retailer_price) AS amount1,
ilog_soled_price,
(ilog_sales * ilog_soled_price) AS amount2,
ilog_remark
FROM ".ZKP_SQL."_tb_customer_group JOIN ".ZKP_SQL."_tb_customer USING(cug_code)
JOIN ".ZKP_SQL."_tb_apotik_item_log USING(cus_code)
JOIN ".ZKP_SQL."_tb_item USING(it_code) ORDER BY 1,2,3 DESC;