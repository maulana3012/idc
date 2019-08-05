CREATE TABLE ".ZKP_SQL."_tb_initial_stock (
	cus_code char(7) NOT NULL,
	it_code char(6) NOT NULL,
	ijk_date date NOT NULL DEFAULT CURRENT_DATE,
	ijk_qty smallint NOT NULL DEFAULT 0,
	
	CONSTRAINT ".ZKP_SQL."_tb_initial_stock_pk PRIMARY KEY(cus_code, it_code)
);

CREATE OR REPLACE FUNCTION setupInitialApotikStock(
	v_code varchar, -- cus code
	v_it_code varchar[],
	v_it_qty integer[]
) RETURNS void AS $body$
DECLARE
	v_i integer := 1;
BEGIN
	WHILE v_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO ".ZKP_SQL."_tb_initial_stock(cus_code, it_code, ijk_qty)
		VALUES(v_code, v_it_code[v_i], v_it_qty[v_i]);
		v_i := v_i + 1;
	END LOOP;
END;
$body$ LANGUAGE plpgsql;


-- ------------------------------------------------------
-- Maintain sales amount in ".ZKP_SQL."_tb_apotik_inv
-- ------------------------------------------------------
CREATE OR REPLACE FUNCTION summary_initial_stock_qty() RETURNS TRIGGER AS $body$
DECLARE
	v_cus_code varchar;
	v_it_code varchar;
	v_delta_qty integer := 0;
BEGIN
	IF(TG_OP = 'DELETE') THEN
		v_cus_code = OLD.cus_code;
		v_it_code = OLD.it_code;
		v_delta_qty = -1 * OLD.ijk_qty;

	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.cus_code != NEW.cus_code) THEN
			RAISE EXCEPTION 'Update of customer code or it_code is not allowed';
		END IF;
		
		v_cus_code = OLD.cus_code;
		v_it_code = OLD.it_code;
		v_delta_qty = NEW.ijk_qty - OLD.ijk_qty;
		
	ELSIF (TG_OP = 'INSERT') THEN
		v_cus_code = NEW.cus_code;
		v_it_code = NEW.it_code;
		v_delta_qty = NEW.ijk_qty;
	END IF;

	-- Insert or update the summary row wirh the new values
	<<dahlia>>
	LOOP
		UPDATE ".ZKP_SQL."_tb_apotik_inv SET
			inv_jk = inv_jk + v_delta_qty,
			inv_updated = CURRENT_DATE
		WHERE cus_code = v_cus_code AND it_code = v_it_code;
		
		EXIT dahlia WHEN FOUND;
		
		BEGIN
			INSERT INTO ".ZKP_SQL."_tb_apotik_inv (cus_code, it_code, inv_updated, inv_jk)
			VALUES(v_cus_code, v_it_code, CURRENT_DATE, v_delta_qty);
			
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
CREATE TRIGGER summary_initial_stock_qty AFTER INSERT OR UPDATE OR DELETE ON ".ZKP_SQL."_tb_initial_stock
FOR EACH ROW EXECUTE PROCEDURE summary_initial_stock_qty();
