SELECT
	cug.cug_name,
	cus.cus_full_name,
	it.it_model_no,
	sl.sl_idx,
	to_char(sl.sl_date, 'dd-Mon-YYYY') AS sl_date,
	sl.sl_basic_disc,
	sl.sl_add_disc,
	sl.sl_debit_price,
	sl.sl_payment_price,
	sl.sl_qty,
	sl.sl_debit_price * sl_qty AS sl_amount,
	round((sl_payment_price - sl_debit_price)/sl_payment_price * 100) AS sl_diff,
	CASE WHEN sl_payment_price - sl_debit_price < 0 THEN sl_debit_price - sl_payment_price ELSE 0 END AS sl_lesspay,
	CASE WHEN sl_payment_price - sl_debit_price > 0 THEN sl_payment_price - sl_debit_price ELSE 0 END AS sl_overpay
FROM
	".ZKP_SQL."_tb_customer_group AS cug JOIN ".ZKP_SQL."_tb_customer AS cus USING(cug_code)
		JOIN ".ZKP_SQL."_tb_sales_log AS sl USING(cus_code)
			JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
ORDER BY cug_code;
