CREATE TABLE ".ZKP_SQL."_tb_return_log(
	rl_idx serial,
	rl_date date NOT NULL,
	cus_code char(7) NOT NULL,
	it_code char(6) NOT NULL,
	rl_qty smallint NOT NULL DEFAULT 0,
	rl_remark varchar(255),
	
	CONSTRAINT ".ZKP_SQL."_tb_return_log_rl_idx_pk PRIMARY KEY(rl_idx)
);

CREATE OR REPLACE FUNCTION addReturnData(
	v_code varchar, -- cus code
	v_it_code varchar[],
	v_it_qty integer[],
	v_return_date date[],
	v_return_remark varchar[]
) RETURNS void AS $body$
DECLARE
	v_i integer := 1;
BEGIN
	WHILE v_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO ".ZKP_SQL."_tb_return_log(rl_date, cus_code, it_code, rl_qty, rl_remark)
		VALUES(v_return_date[v_i], v_code, v_it_code[v_i], v_it_qty[v_i], v_return_remark[v_i]);
		v_i := v_i + 1;
	END LOOP;
END;
$body$ LANGUAGE plpgsql;


-- ------------------------------------------------------
-- Maintain sales amount in ".ZKP_SQL."_tb_apotik_inv
-- ------------------------------------------------------
CREATE OR REPLACE FUNCTION summary_return_qty() RETURNS TRIGGER AS $body$
DECLARE
	v_cus_code varchar;
	v_it_code varchar;
	v_delta_return integer := 0;
BEGIN
	IF(TG_OP = 'DELETE') THEN
		v_cus_code = OLD.cus_code;
		v_it_code = OLD.it_code;
		v_delta_return = -1 * OLD.rl_qty;

	ELSIF (TG_OP = 'UPDATE') THEN
		--키를 바꾸면 집계를 할 수 없다..
		IF(OLD.it_code != NEW.it_code OR OLD.cus_code != NEW.cus_code) THEN
			RAISE EXCEPTION 'Update of customer code or it_code is not allowed';
		END IF;
		
		v_cus_code = OLD.cus_code;
		v_it_code = OLD.it_code;
		v_delta_return = NEW.rl_qty - OLD.rl_qty;
		
	ELSIF (TG_OP = 'INSERT') THEN
		v_cus_code = NEW.cus_code;
		v_it_code = NEW.it_code;
		v_delta_return = NEW.rl_qty;
	END IF;

	-- Insert or update the summary row wirh the new values
	<<dahlia>>
	LOOP
		UPDATE ".ZKP_SQL."_tb_apotik_inv SET
			inv_return = inv_return + v_delta_return,
			inv_updated = CURRENT_DATE
		WHERE cus_code = v_cus_code AND it_code = v_it_code;
		
		EXIT dahlia WHEN FOUND;
		
		BEGIN
			INSERT INTO ".ZKP_SQL."_tb_apotik_inv (cus_code, it_code, inv_updated, inv_return)
			VALUES(v_cus_code, v_it_code, CURRENT_DATE, v_delta_return);
			
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
CREATE TRIGGER summary_return_qty AFTER INSERT OR UPDATE OR DELETE ON ".ZKP_SQL."_tb_return_log
FOR EACH ROW EXECUTE PROCEDURE summary_return_qty();
