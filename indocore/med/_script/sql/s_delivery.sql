-- ------------------------------------------------------------------------
-- 출고
-- 하나의 PO는 여러번 걸처서 출고될 수 있다...
-- ------------------------------------------------------------------------
CREATE TABLE ".ZKP_SQL."_tb_delivery (
	deli_idx serial NOT NULL,
	ord_code char(12) NOT NULL,
	deli_type CHAR(2), -- JK, JO
	deli_date date NOT NULL,
	deli_by varchar(32),
	deli_remark varchar(255),
	
	CONSTRAINT ".ZKP_SQL."_tb_delivery_deli_idx_pk PRIMARY KEY(deli_idx),
	CONSTRAINT ".ZKP_SQL."_tb_delivery_ord_code_fk FOREIGN KEY(ord_code) REFERENCES ".ZKP_SQL."_tb_order(ord_code)
		ON UPDATE CASCADE
		ON DELETE RESTRICT
);

CREATE TABLE ".ZKP_SQL."_tb_delivery_item (
	deli_idx integer NOT NULL,
	it_code char(6) NOT NULL,
	deit_date date NOT NULL DEFAULT CURRENT_DATE, --  FOR REFERENCES Item delivery_date
	cus_code char(7) NOT NULL, -- FOR REFERENCES
	deit_jo_qty smallint NOT NULL DEFAULT 0, -- For calculation : when order item input, will specify again.
	deit_jk_qty smallint NOT NULL DEFAULT 0, -- For Calculation : when order item input, will specify again.
	deit_qty smallint NOT NULL,

	CONSTRAINT ".ZKP_SQL."_tb_delivery_item_pk PRIMARY KEY(deli_idx, it_code),
	
	CONSTRAINT ".ZKP_SQL."_tb_deit_ord_code_fk FOREIGN KEY(deli_idx) REFERENCES ".ZKP_SQL."_tb_delivery
	ON DELETE CASCADE
	ON UPDATE CASCADE,

	CONSTRAINT ".ZKP_SQL."_tb_deit_it_code FOREIGN KEY(it_code) REFERENCES ".ZKP_SQL."_tb_item
	ON DELETE RESTRICT
	ON UPDATE CASCADE
);


-- ----------------------------------------------
-- confirm delivery order
--
--  when po sheet is ckecked by delivery note, user clike [CONFIRM DELIVER]
-- ----------------------------------------------
CREATE OR REPLACE FUNCTION confirmDeliveryOrder(
	v_code varchar, -- ord_code
	v_cfm_deli_by_account varchar,
	v_deliverd_date date,
	v_deliverd_by varchar,
	v_deliverd_remark varchar,
	v_cus_code varchar
) RETURNS void AS $body$
DECLARE
	v_type varchar;
	v_curr_deli_idx integer;
BEGIN
	v_type := substr(v_code, 1, 2);

	-- set user cannot modify this order sheet.
	UPDATE ".ZKP_SQL."_tb_order SET
		ord_cfm_deli_by_account = v_cfm_deli_by_account,
		ord_cfm_deli_timestamp = CURRENT_TIMESTAMP -- Confirmed timestamp
	WHERE ord_code = v_code;
	
	-- insert ".ZKP_SQL."_tb_delivery
	INSERT INTO ".ZKP_SQL."_tb_delivery(ord_code, deli_type, deli_date, deli_by, deli_remark)
	VALUES (v_code, v_type, v_deliverd_date, v_deliverd_by, v_deliverd_remark);
	
	v_curr_deli_idx := currval('".ZKP_SQL."_tb_delivery_deli_idx_seq');
		
	-- make JO qty with OK qty
	IF v_type = 'OO' THEN
		INSERT INTO ".ZKP_SQL."_tb_delivery_item (deli_idx, it_code, deit_date, cus_code, deit_jo_qty, deit_qty)
		SELECT v_curr_deli_idx, it_code, v_deliverd_date, v_cus_code, odit_qty, odit_qty
		FROM ".ZKP_SQL."_tb_order_item WHERE ord_code = v_code;

	ELSIF v_type = 'OK' THEN
		INSERT INTO ".ZKP_SQL."_tb_delivery_item (deli_idx, it_code, deit_date, cus_code, deit_jk_qty, deit_qty)
		SELECT v_curr_deli_idx, it_code, v_deliverd_date, v_cus_code, odit_qty, odit_qty
		FROM ".ZKP_SQL."_tb_order_item WHERE ord_code = v_code;
	END IF;
END;
$body$ LANGUAGE plpgsql;


-- ------------------------------------------------------
-- Maintain Order amount in ".ZKP_SQL."_tb_apotik_inv
-- ------------------------------------------------------
CREATE OR REPLACE FUNCTION summary_delivery_qty() RETURNS TRIGGER AS $body$
DECLARE
	v_cus_code varchar;
	v_it_code varchar;
	v_delta_jk_qty integer := 0;
	v_delta_jo_qty integer := 0;
BEGIN
	IF(TG_OP = 'DELETE') THEN
		v_cus_code = OLD.cus_code;
		v_it_code = OLD.it_code;
		v_delta_jk_qty = -1 * OLD.deit_jk_qty;
		v_delta_jo_qty = -1 * OLD.deit_jo_qty;

	ELSIF (TG_OP = 'UPDATE') THEN
		--키를 바꾸면 집계를 할 수 없다..
		IF(OLD.it_code != NEW.it_code OR OLD.cus_code != NEW.cus_code) THEN
			RAISE EXCEPTION 'Update of customer code or it_code is not allowed';
		END IF;
		
		v_cus_code = OLD.cus_code;
		v_it_code = OLD.it_code;
		v_delta_jk_qty = NEW.deit_jk_qty - OLD.deit_jk_qty;
		v_delta_jo_qty = NEW.deit_jo_qty - OLD.deit_jo_qty;
		
	ELSIF (TG_OP = 'INSERT') THEN
		v_cus_code = NEW.cus_code;
		v_it_code = NEW.it_code;
		v_delta_jk_qty = NEW.deit_jk_qty;
		v_delta_jo_qty = NEW.deit_jo_qty;
	END IF;

	-- Insert or update the summary row wirh the new values
	<<dahlia>>
	LOOP
		UPDATE ".ZKP_SQL."_tb_apotik_inv SET
			inv_jk = inv_jk + v_delta_jk_qty,
			inv_jo = inv_jo + v_delta_jo_qty,
			inv_updated = CURRENT_DATE
		WHERE cus_code = v_cus_code AND it_code = v_it_code;
		
		EXIT dahlia WHEN FOUND;
		
		BEGIN
			INSERT INTO ".ZKP_SQL."_tb_apotik_inv (cus_code, it_code, inv_updated, inv_ok, inv_oo)
			VALUES(v_cus_code, v_it_code, CURRENT_DATE, v_delta_jk_qty, v_delta_jo_qty);
			
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
--  CREATE TREAGGER for ".ZKP_SQL."_tb_order_item
-- ---------------------------------------------------------
CREATE TRIGGER summary_delivery_qty AFTER INSERT OR UPDATE OR DELETE ON ".ZKP_SQL."_tb_delivery_item
FOR EACH ROW EXECUTE PROCEDURE summary_delivery_qty();
