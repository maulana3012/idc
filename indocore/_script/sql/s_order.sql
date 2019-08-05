CREATE TABLE ".ZKP_SQL."_tb_order(
	--for admin
	ord_code char(12) NOT NULL,
	ord_received_by varchar(32),
	ord_confirm_by varchar(32),
	
	--for system
	ord_lastupdated_by_account varchar(32) NOT NULL,
	ord_cfm_deli_by_account varchar(32),
	ord_lastupdated_timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	ord_cfm_deli_timestamp TIMESTAMP,
	ord_revesion_time smallint NOT NULL DEFAULT 0,

	ord_po_date date DEFAULT CURRENT_DATE,
	ord_po_no varchar(64),
	ord_vat numeric(3,1),

	ord_cus_to char(7),
	ord_cus_to_attn varchar(128),
	ord_cus_to_address varchar(255),

	ord_ship_to char(7),
	ord_ship_to_attn varchar(128),
	ord_ship_to_address varchar(255),

	ord_bill_to char(7),
	ord_bill_to_attn varchar(128),
	ord_bill_to_address varchar(255),

	ord_price_chk smallint NOT NULL DEFAULT 0,
	ord_delivery_chk smallint NOT NULL DEFAULT 0,
	ord_payment_chk smallint NOT NULL DEFAULT 0,

	ord_price_discount numeric(3,1),

	ord_delivery_by varchar(128),
	ord_delivery_freight_charge numeric(12,2) NOT NULL DEFAULT 0,

	ord_payment_widthin_days smallint DEFAULT 0,
	ord_payment_closing_on date,
	ord_payment_cash_by varchar(128),
	ord_payment_check_by varchar(128),
	ord_payment_transfer_by varchar(128),
	ord_payment_giro_by varchar(128),

	ord_remark varchar(255),

	CONSTRAINT ".ZKP_SQL."_tb_order_ord_code_pk PRIMARY KEY(ord_code),
	CONSTRAINT ".ZKP_SQL."_tb_order_cus_code_fk FOREIGN KEY(ord_cus_to) REFERENCES ".ZKP_SQL."_tb_customer(cus_code)
		ON DELETE RESTRICT
		ON UPDATE CASCADE
);

CREATE TABLE ".ZKP_SQL."_tb_order_item (
	ord_code char(12) NOT NULL,
	it_code char(6) NOT NULL,
	cus_code char(7) NOT NULL, -- For References
	odit_oo_qty smallint NOT NULL DEFAULT 0, -- For calculation : when order item input, will specify again.
	odit_ok_qty smallint NOT NULL DEFAULT 0, -- For Calculation : when order item input, will specify again.
	odit_date date NOT NULL DEFAULT CURRENT_DATE, -- Item order_date
	odit_qty smallint NOT NULL,
	odit_unit_price numeric(12,2),
	odit_delivery date,

	CONSTRAINT ".ZKP_SQL."_tb_order_item_pk PRIMARY KEY(ord_code, it_code),
	
	CONSTRAINT ".ZKP_SQL."_tb_odit_ord_code_fk FOREIGN KEY(ord_code) REFERENCES ".ZKP_SQL."_tb_order
	ON DELETE CASCADE
	ON UPDATE CASCADE,

	CONSTRAINT ".ZKP_SQL."_tb_odit_it_code FOREIGN KEY(it_code) REFERENCES ".ZKP_SQL."_tb_item
	ON DELETE RESTRICT
	ON UPDATE CASCADE
);

--
--
--
CREATE OR REPLACE FUNCTION addNewApotikOrder(
	v_type varchar, v_received_by varchar, v_confirm_by varchar,
	v_po_date date, v_po_no varchar, v_vat numeric,
	v_cus_to varchar, v_cus_to_attn varchar, v_cus_to_address varchar,
	v_ship_to varchar, v_ship_to_attn varchar, v_ship_to_address varchar,
	v_bill_to varchar, v_bill_to_attn varchar, v_bill_to_address varchar,
	v_it_code varchar[], v_it_unit_price numeric[], v_it_qty integer[],
	v_it_delivery date[], v_price_discount numeric, v_price_chk integer,
	v_delivery_chk integer, v_delivery_by varchar, v_delivery_freight_charge numeric,
	v_payment_chk integer, v_payment_widthin_days integer, v_payment_closing_on date,
	v_payment_cash_by varchar, v_payment_check_by varchar, v_payment_transfer_by varchar,
	v_payment_giro_by varchar, v_remark varchar, v_lastupdated_by_account varchar
) RETURNS varchar AS $body$
DECLARE
	v_i integer := 1;
	v_code varchar;
BEGIN
	SELECT INTO v_code getCurrentOrdCode(v_type, v_po_date);

	--Insert ".ZKP_SQL."_tb_order
	INSERT INTO ".ZKP_SQL."_tb_order(
		ord_code, ord_type, ord_lastupdated_by_account, ord_po_date,
		ord_po_no, ord_received_by, ord_confirm_by, ord_vat, ord_cus_to,
		ord_cus_to_attn, ord_cus_to_address, ord_ship_to, ord_ship_to_attn, ord_ship_to_address,
		ord_bill_to, ord_bill_to_attn, ord_bill_to_address, ord_price_discount, ord_price_chk,
		ord_delivery_chk, ord_delivery_by, ord_delivery_freight_charge, ord_payment_chk, ord_payment_widthin_days,
		ord_payment_closing_on, ord_payment_cash_by, ord_payment_check_by, ord_payment_transfer_by, ord_payment_giro_by,
		ord_remark
	) VALUES (
		v_code, v_type, v_lastupdated_by_account, v_po_date,
		v_po_no, v_received_by, v_confirm_by, v_vat, v_cus_to,
		v_cus_to_attn, v_cus_to_address, v_ship_to, v_ship_to_attn, v_ship_to_address,
		v_bill_to, v_bill_to_attn, v_bill_to_address, v_price_discount, v_price_chk,
		v_delivery_chk, v_delivery_by, v_delivery_freight_charge, v_payment_chk, v_payment_widthin_days,
		v_payment_closing_on, v_payment_cash_by, v_payment_check_by, v_payment_transfer_by, v_payment_giro_by,
		v_remark);
	
		IF v_type = 'OO' THEN
			WHILE v_it_code[v_i] IS NOT NULL LOOP
				INSERT INTO ".ZKP_SQL."_tb_order_item (
					ord_code, cus_code, it_code, odit_oo_qty, odit_qty, odit_unit_price, odit_delivery, odit_date)
				VALUES (
					v_code, v_cus_to, v_it_code[v_i], v_it_qty[v_i], v_it_qty[v_i],
					v_it_unit_price[v_i], v_it_delivery[v_i], v_po_date);

				v_i := v_i + 1;
			END LOOP;
		ELSE
			WHILE v_it_code[v_i] IS NOT NULL LOOP
				INSERT INTO ".ZKP_SQL."_tb_order_item (
					ord_code, cus_code, it_code, odit_ok_qty, odit_qty, odit_unit_price, odit_delivery, odit_date)
				VALUES (v_code, v_cus_to, v_it_code[v_i], v_it_qty[v_i], v_it_qty[v_i],
					v_it_unit_price[v_i], v_it_delivery[v_i], v_po_date);
				
				v_i := v_i + 1;
			END LOOP;
		END IF;

		RETURN v_code;
END;
$body$ LANGUAGE plpgsql;

-- -----------------------------------------------------
-- UPDATE APOTIK ORDER
-- -----------------------------------------------------
CREATE OR REPLACE FUNCTION reviseApotikOrder(
	v_is_dirty_item boolean, -- when need to delete item
	v_code varchar, v_type varchar, v_received_by varchar, v_confirm_by varchar,
	v_po_date date, v_po_no varchar, v_vat numeric,
	v_cus_to varchar, v_cus_to_attn varchar, v_cus_to_address varchar,
	v_ship_to varchar, v_ship_to_attn varchar, v_ship_to_address varchar,
	v_bill_to varchar, v_bill_to_attn varchar, v_bill_to_address varchar,
	v_it_code varchar[], v_it_unit_price numeric[], v_it_qty integer[],
	v_it_delivery date[], v_price_discount numeric, v_price_chk integer,
	v_delivery_chk integer, v_delivery_by varchar, v_delivery_freight_charge numeric,
	v_payment_chk integer, v_payment_widthin_days integer, v_payment_closing_on date,
	v_payment_cash_by varchar, v_payment_check_by varchar, v_payment_transfer_by varchar,
	v_payment_giro_by varchar, v_remark varchar,
	v_lastupdated_by_account varchar
) RETURNS void AS $body$
DECLARE
	v_i integer := 1;
	rec1 record;
	rec2 record;
BEGIN
	UPDATE ".ZKP_SQL."_tb_order SET 
		ord_lastupdated_by_account = v_lastupdated_by_account,
		ord_lastupdated_timestamp = CURRENT_TIMESTAMP,
		ord_po_date = v_po_date,
		ord_po_no = v_po_no,
		ord_received_by = v_received_by,
		ord_confirm_by = v_confirm_by,
		ord_revision_time = ord_revision_time + 1,
		ord_vat	= v_vat,
		ord_cus_to = v_cus_to,
		ord_cus_to_attn = v_cus_to_attn,
		ord_cus_to_address = v_cus_to_address,
		ord_ship_to = v_ship_to,
		ord_ship_to_attn = v_ship_to_attn,
		ord_ship_to_address = v_ship_to_address,
		ord_bill_to = v_bill_to,
		ord_bill_to_attn = v_bill_to_attn,
		ord_bill_to_address = v_bill_to_address,
		ord_price_discount = v_price_discount,
		ord_price_chk = v_price_chk,
		ord_delivery_chk = v_delivery_chk,
		ord_delivery_by = v_delivery_by,
		ord_delivery_freight_charge = v_delivery_freight_charge,
		ord_payment_chk = v_payment_chk,
		ord_payment_widthin_days = v_payment_widthin_days,
		ord_payment_closing_on = v_payment_closing_on,
		ord_payment_cash_by = v_payment_cash_by,
		ord_payment_check_by = v_payment_check_by,
		ord_payment_transfer_by = v_payment_transfer_by,
		ord_payment_giro_by = v_payment_giro_by,
		ord_remark = v_remark
	WHERE
		ord_code = v_code;

	IF v_is_dirty_item THEN
		-- Delete order item
		DELETE FROM ".ZKP_SQL."_tb_order_item WHERE ord_code = v_code;

		-- Make order item again
		IF substr(v_code, 1, 2) = 'OO' THEN
			WHILE v_it_code[v_i] IS NOT NULL LOOP
				INSERT INTO ".ZKP_SQL."_tb_order_item (
					ord_code, cus_code, it_code, odit_oo_qty, odit_qty, odit_unit_price, odit_delivery, odit_date)
				VALUES (
					v_code, v_cus_to, v_it_code[v_i], v_it_qty[v_i], v_it_qty[v_i],
					v_it_unit_price[v_i], v_it_delivery[v_i], v_po_date);
				v_i := v_i + 1;
			END LOOP;
		ELSE
			WHILE v_it_code[v_i] IS NOT NULL LOOP
				INSERT INTO ".ZKP_SQL."_tb_order_item (
					ord_code, cus_code, it_code, odit_ok_qty, odit_qty, odit_unit_price, odit_delivery, odit_date)
				VALUES (v_code, v_cus_to, v_it_code[v_i], v_it_qty[v_i], v_it_qty[v_i],
					v_it_unit_price[v_i], v_it_delivery[v_i], v_po_date);
				v_i := v_i + 1;
			END LOOP;
		END IF;
	END IF;
END;
$body$ LANGUAGE plpgsql;

------------------------------------------------------------
-- DELETE Order
------------------------------------------------------------
CREATE OR REPLACE FUNCTION deleteOrder(
	v_ord_code varchar
) RETURNS void AS $body$
DECLARE
	v_type varchar;
	rec1 record;
	rec2 record;
BEGIN
	-- Delete order
	DELETE FROM ".ZKP_SQL."_tb_order WHERE ord_code = v_ord_code;
END;
$body$ LANGUAGE plpgsql;

--
-- Get current order code
-- OO-A_ _ _ _-_ _ _
-- update ".ZKP_SQL."_tb_order set ord_code =  'OO-A0' || substr(ord_code, 5,3) || '-' || CASE WHEN extract(month FROM ord_po_date)=6 THEN 'F' ELSE 'G' END || '07';
--
CREATE OR REPLACE FUNCTION getCurrentOrdCode(v_type varchar, v_po_date date) RETURNS varchar AS $body$
DECLARE
	v_current_month varchar[] := ARRAY['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
	v_month integer := extract(MONTH FROM v_po_date) + 0;
	v_monyy varchar := v_current_month[v_month] || to_char(v_po_date, 'YY');
	v_new_code varchar;
	v_serial integer;
BEGIN
	SELECT INTO v_serial max(substr(ord_code, 5, 4))
	FROM ".ZKP_SQL."_tb_order WHERE substr(ord_code, 10,3) = v_monyy;
	
	IF v_serial IS NULL THEN
		v_serial := 1;
	ELSE
		v_serial := v_serial + 1;
	END IF;

	IF v_type = 'OK' THEN
		v_new_code := 'OK-K' || lpad(v_serial, 4, '0') || '-' || v_monyy;
	ELSE
		v_new_code := 'OO-A' || lpad(v_serial, 4, '0') || '-' || v_monyy;
	END IF;
	
	RETURN v_new_code;
END;
$body$ LANGUAGE plpgsql;

-- ------------------------------------------------------
-- Maintain Order amount in ".ZKP_SQL."_tb_apotik_inv
-- ------------------------------------------------------
CREATE OR REPLACE FUNCTION summary_order_qty() RETURNS TRIGGER AS $body$
DECLARE
	v_cus_code varchar;
	v_it_code varchar;
	v_delta_ok_qty integer := 0;
	v_delta_oo_qty integer := 0;
BEGIN
	IF(TG_OP = 'DELETE') THEN
		v_cus_code = OLD.cus_code;
		v_it_code = OLD.it_code;
		v_delta_ok_qty = -1 * OLD.odit_ok_qty;
		v_delta_oo_qty = -1 * OLD.odit_oo_qty;

	ELSIF (TG_OP = 'UPDATE') THEN
		--키를 바꾸면 집계를 할 수 없다..
		IF(OLD.it_code != NEW.it_code OR OLD.cus_code != NEW.cus_code) THEN
			RAISE EXCEPTION 'Update of customer code or it_code is not allowed';
		END IF;
		
		v_cus_code = OLD.cus_code;
		v_it_code = OLD.it_code;
		v_delta_ok_qty = NEW.odit_ok_qty - OLD.odit_ok_qty;
		v_delta_oo_qty = NEW.odit_oo_qty - OLD.odit_oo_qty;
		
	ELSIF (TG_OP = 'INSERT') THEN
		v_cus_code = NEW.cus_code;
		v_it_code = NEW.it_code;
		v_delta_ok_qty = NEW.odit_ok_qty;
		v_delta_oo_qty = NEW.odit_oo_qty;
	END IF;

	-- Insert or update the summary row wirh the new values
	<<dahlia>>
	LOOP
		UPDATE ".ZKP_SQL."_tb_apotik_inv SET
			inv_ok = inv_ok + v_delta_ok_qty,
			inv_oo = inv_oo + v_delta_oo_qty,
			inv_updated = CURRENT_DATE
		WHERE cus_code = v_cus_code AND it_code = v_it_code;
		
		EXIT dahlia WHEN FOUND;
		
		BEGIN
			INSERT INTO ".ZKP_SQL."_tb_apotik_inv (cus_code, it_code, inv_updated, inv_ok, inv_oo)
			VALUES(v_cus_code, v_it_code, CURRENT_DATE, v_delta_ok_qty, v_delta_oo_qty);
			
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
CREATE TRIGGER summary_order_qty AFTER INSERT OR UPDATE OR DELETE ON ".ZKP_SQL."_tb_order_item
FOR EACH ROW EXECUTE PROCEDURE summary_order_qty();