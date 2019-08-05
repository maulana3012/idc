-- -----------------------------------------------------------
-- sales data
-- 판매기록으로 재고를 정산하지 말고... 특정일로 일괄 정산하는 기능,, 개별  정산하는 기능을 추가해서 해서 정산한 재고를 INVENTORY에 기록하자..
-- -----------------------------------------------------------
CREATE TABLE ".ZKP_SQL."_tb_sales_log (
	sl_idx serial NOT NULL,
	sl_date date NOT NULL,
	it_code char(6),
	cus_code char(7),
	sl_basic_disc numeric(4,2) default 0,  -- 판매시점 기본 할인율
	sl_add_disc numeric(4,2) default 0, -- 판매시점 추가 할인율
	sl_user_price numeric(12,2) default 0, -- 해당일 기준가격
	sl_debit_price numeric(12,2) default 0, -- 청구할 가격
	sl_payment_price numeric(12,2) default 0, --아포틱에서  payment할 가격
	sl_qty smallint,
	sl_remark varchar(255),
	
	CONSTRAINT ".ZKP_SQL."_tb_sales_lo_sl_idx_pk PRIMARY KEY(sl_idx)
);

-- ---------------------------------------------------
-- add Sales Data
-- ---------------------------------------------------
CREATE OR REPLACE FUNCTION addSalesData(
	v_code varchar, -- cus code
	v_it_code varchar[],
	v_sales_date date[],
	v_it_qty integer[],
	v_payment_price numeric[]
) RETURNS void AS $body$
DECLARE
	v_i integer := 1;
	v_row_count integer := 0;
	v_is_apply_all boolean := FALSE;
	v_debit_price numeric;
	v_unit_price numeric;
	v_basic_disc_pct numeric := 0;
	v_add_disc_pct numeric := 0;
	v_cug_code varchar;
	v_sales_remark varchar := '';
	v_policy_idx integer; -- for remark
	rec RECORD;
BEGIN
	--Get Customer group
	SELECT INTO v_cug_code cug_code FROM ".ZKP_SQL."_tb_customer WHERE cus_code = v_code;
	IF NOT FOUND OR v_cug_code IS NULL THEN
		RAISE EXCEPTION 'CANNOT FIND CUSTOMER GROUP';
	END IF;

	-- get basic_disc_price
	SELECT INTO v_basic_disc_pct cug_basic_disc_pct
	FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = (SELECT cug_code FROM ".ZKP_SQL."_tb_customer WHERE cus_code = v_code);

	-- For all the sales item (PRICE)
	WHILE v_it_code[v_i] IS NOT NULL LOOP

		--check apotik has price policy. Basically User cannot input duplicate period 
		
		SELECT INTO v_policy_idx, v_add_disc_pct
			ap_idx, ap_disc_pct FROM ".ZKP_SQL."_tb_apotik_policy
		WHERE cus_code = v_code
		   AND ap_is_valid = TRUE
		   AND ap_is_apply_all = TRUE
		   AND ap_date_from <= v_sales_date[v_i]
		   AND ap_date_to + 1 > v_sales_date[v_i];

		IF FOUND THEN
			v_sales_remark := 'AP#' || v_policy_idx;
		ELSE
			SELECT INTO v_policy_idx, v_add_disc_pct
				ap.ap_idx, ap.ap_disc_pct
			FROM ".ZKP_SQL."_tb_apotik_policy AS ap
				JOIN ".ZKP_SQL."_tb_apotik_price AS ait ON (ap.ap_idx = ait.ap_idx)
			WHERE ap.cus_code = v_code
			   AND ap.ap_is_valid = TRUE
			   AND ait.it_code = v_it_code[v_i]
			   AND ap.ap_date_from <= v_sales_date[v_i]
			   AND ap.ap_date_to + 1 > v_sales_date[v_i];

			IF FOUND THEN
				v_sales_remark := 'AP#' || v_policy_idx;

			--check group price policy or item price
			ELSE
				SELECT INTO v_policy_idx, v_add_disc_pct
					ag_idx, ag_disc_pct
				FROM ".ZKP_SQL."_tb_group_policy
				WHERE cug_code = v_cug_code
				   AND ag_is_valid = TRUE
				   AND ag_is_apply_all = TRUE
				   AND ag_date_from <= v_sales_date[v_i]
				   AND ag_date_to + 1 > v_sales_date[v_i];

				IF FOUND THEN
					v_sales_remark := 'GP#' || v_policy_idx;
				ELSE
				   
					SELECT INTO v_policy_idx, v_add_disc_pct
						ag.ag_idx, ag.ag_disc_pct
					FROM ".ZKP_SQL."_tb_group_policy AS ag
					   JOIN ".ZKP_SQL."_tb_group_price AS git ON (ag.ag_idx = git.ag_idx)
					WHERE ag.cug_code = v_cug_code
					   AND ag.ag_is_valid = TRUE
					   AND git.it_code = v_it_code[v_i]
					   AND ag.ag_date_from <= v_sales_date[v_i]
					   AND ag.ag_date_to + 1 > v_sales_date[v_i];

					IF FOUND THEN
						v_sales_remark := 'GP#' || v_policy_idx;

					--Check Item unit price
					ELSE
						v_add_disc_pct := 0;
						v_sales_remark := NULL;
					END IF;
				END IF;
			END IF;
		END IF;

		-- get user price on sales date
		SELECT INTO v_unit_price ip_user_price
		FROM ".ZKP_SQL."_tb_item_price
		WHERE it_code = v_it_code[v_i]
		  AND ip_date_from <= v_sales_date[v_i]
		  AND ip_date_to + 1 > v_sales_date[v_i];

		IF NOT FOUND THEN
			SELECT INTO v_unit_price ip_user_price
			FROM ".ZKP_SQL."_tb_item_price WHERE ip_idx = (SELECT max(ip_idx) FROM ".ZKP_SQL."_tb_item_price WHERE it_code = v_it_code[v_i]);
		END IF;

		v_sales_remark := v_sales_remark;
		
		-- now calculate the user price
		v_debit_price := round((v_unit_price - (v_unit_price * (v_basic_disc_pct + v_add_disc_pct)/100))/1.1);

		-- Input sales log
		INSERT INTO ".ZKP_SQL."_tb_sales_log(sl_date, it_code, cus_code, sl_basic_disc, sl_add_disc, sl_user_price, sl_debit_price, sl_payment_price, sl_qty, sl_remark)
		VALUES(v_sales_date[v_i], v_it_code[v_i], v_code, v_basic_disc_pct, v_add_disc_pct, v_unit_price, v_debit_price, v_payment_price[v_i], v_it_qty[v_i], v_sales_remark);

		v_i := v_i + 1;
	END LOOP;
END;
$body$ LANGUAGE plpgsql;


-- ------------------------------------------------------
-- Maintain sales amount in ".ZKP_SQL."_tb_apotik_inv
-- ------------------------------------------------------
CREATE OR REPLACE FUNCTION summary_sales_qty() RETURNS TRIGGER AS $body$
DECLARE
	v_cus_code varchar;
	v_it_code varchar;
	v_delta_sales integer := 0;
	v_sales_date date;
BEGIN
	IF(TG_OP = 'DELETE') THEN
		v_cus_code = OLD.cus_code;
		v_it_code = OLD.it_code;
		v_delta_sales = -1 * OLD.sl_qty;

	ELSIF (TG_OP = 'UPDATE') THEN
		--키를 바꾸면 집계를 할 수 없다..
		IF(OLD.it_code != NEW.it_code OR OLD.cus_code != NEW.cus_code) THEN
			RAISE EXCEPTION 'Update of customer code or it_code is not allowed';
		END IF;
		
		v_cus_code = OLD.cus_code;
		v_it_code = OLD.it_code;
		v_delta_sales = NEW.sl_qty - OLD.sl_qty;
		v_sales_date = NEW.sl_date;
		
	ELSIF (TG_OP = 'INSERT') THEN
		v_cus_code = NEW.cus_code;
		v_it_code = NEW.it_code;
		v_delta_sales = NEW.sl_qty;
		v_sales_date = NEW.sl_date;
	END IF;

	-- Insert or update the summary row wirh the new values
	<<dahlia>>
	LOOP
		UPDATE ".ZKP_SQL."_tb_apotik_inv SET
			inv_sales = inv_sales + v_delta_sales,
			inv_updated = CURRENT_DATE,
			inv_sales_updated = v_sales_date
		WHERE cus_code = v_cus_code AND it_code = v_it_code;
		
		EXIT dahlia WHEN FOUND;
		
		BEGIN
			INSERT INTO ".ZKP_SQL."_tb_apotik_inv (cus_code, it_code, inv_updated, inv_sales, inv_sales_updated)
			VALUES(v_cus_code, v_it_code, CURRENT_DATE, v_delta_sales, v_sales_date);
			
			EXIT dahlia;
			
		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				-- do nothing
		END;
	END LOOP dahlia;
	
	RETURN NULL;
END;
$body$ LANGUAGE plpgsql;

-- ---------------------------------------------------------
--  CREATE TREAGGER
-- ---------------------------------------------------------
CREATE TRIGGER summary_sales_qty AFTER INSERT OR UPDATE OR DELETE ON ".ZKP_SQL."_tb_sales_log
FOR EACH ROW EXECUTE PROCEDURE summary_sales_qty();
