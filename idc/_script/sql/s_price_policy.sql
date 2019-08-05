CREATE TABLE ".ZKP_SQL."_tb_group_policy (
	ag_idx serial NOT NULL,
	cug_code char(2) NOT NULL,
	ag_desc varchar(255) NOT NULL,
	ag_is_valid BOOLEAN NOT NULL DEFAULT TRUE,
	ag_is_apply_all BOOLEAN NOT NULL DEFAULT FALSE,
	ag_date_from DATE NOT NULL,
	ag_date_to DATE NOT NULL,
	ag_disc_pct numeric(4,2) DEFAULT 0,
	ag_basic_disc_pct numeric(4,2) DEFAULT 0,
	ag_remark varchar(255),
	ag_updated timestamp DEFAULT CURRENT_TIMESTAMP,
	ag_created timestamp DEFAULT CURRENT_TIMESTAMP,
	ag_updated_by varchar(32) NOT NULL,
	ag_created_by varchar(32) NOT NULL,

	CONSTRAINT ag_idx_pk PRIMARY KEY(ag_idx),
	CONSTRAINT ag_cug_code_fk FOREIGN KEY(cug_code) REFERENCES ".ZKP_SQL."_tb_customer_group(cug_code)
		ON DELETE RESTRICT
		ON UPDATE CASCADE
);

CREATE TABLE ".ZKP_SQL."_tb_group_price(
	ag_idx integer NOT NULL,
	it_code char(6),

	CONSTRAINT git_pk PRIMARY KEY(ag_idx, it_code),

	CONSTRAINT git_ag_idx_fk FOREIGN KEY(ag_idx) REFERENCES ".ZKP_SQL."_tb_group_policy(ag_idx)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
		
	CONSTRAINT git_it_code_fk FOREIGN KEY(it_code) REFERENCES ".ZKP_SQL."_tb_item(it_code)
		ON DELETE CASCADE
		ON UPDATE CASCADE
);

-----------------------------------------------------------------------------------------------------------------------------------------------------------

CREATE TABLE ".ZKP_SQL."_tb_apotik_policy (
	ap_idx serial NOT NULL,
	cus_code char(7) NOT NULL,
	ap_desc varchar(255) NOT NULL,
	ap_is_valid BOOLEAN NOT NULL DEFAULT TRUE,
	ap_is_apply_all BOOLEAN NOT NULL DEFAULT FALSE,
	ap_date_from DATE NOT NULL,
	ap_date_to DATE NOT NULL,
	ap_disc_pct numeric(4,2) DEFAULT 0,
	ap_basic_disc_pct numeric(4,2) DEFAULT 0,
	ap_remark varchar(255),
	ap_updated timestamp DEFAULT CURRENT_TIMESTAMP,
	ap_created timestamp DEFAULT CURRENT_TIMESTAMP,
	ap_updated_by varchar(32) NOT NULL,
	ap_created_by varchar(32) NOT NULL,

	CONSTRAINT ap_idx_pk PRIMARY KEY(ap_idx),
	CONSTRAINT ap_cus_code_fk FOREIGN KEY(cus_code) REFERENCES ".ZKP_SQL."_tb_customer(cus_code)
		ON DELETE RESTRICT
		ON UPDATE CASCADE
);

CREATE TABLE ".ZKP_SQL."_tb_apotik_price(
	ap_idx integer NOT NULL,
	it_code char(6),

	CONSTRAINT ait_pk PRIMARY KEY(ap_idx, it_code),

	CONSTRAINT ait_ap_idx_fk FOREIGN KEY(ap_idx) REFERENCES ".ZKP_SQL."_tb_apotik_policy(ap_idx)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
		
	CONSTRAINT ait_it_code_fk FOREIGN KEY(it_code) REFERENCES ".ZKP_SQL."_tb_item(it_code)
		ON DELETE CASCADE
		ON UPDATE CASCADE
);

-- -------------------------------------------------------------------------------------------
--
-- -------------------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION addApotikPrice(
	v_cus_code varchar,
	v_desc varchar,
	v_basic_disc_pct numeric,
	v_disc_pct numeric,
	v_is_valid boolean,
	v_is_apply_all boolean,
	v_date_from date,
	v_date_to date,
	v_remark varchar,
	v_it_code varchar[],
	v_created_by varchar
) RETURNS void AS $body$
DECLARE
	v_i integer := 1;
	v_policy_idx integer;
	v_row_count integer := 0;
	v_cur_ap_idx integer := 0;
BEGIN
	-- Check the duplicated period
	SELECT INTO v_policy_idx ap_idx FROM ".ZKP_SQL."_tb_apotik_policy
	WHERE cus_code = v_cus_code AND (ap_date_from, ap_date_to + 1) OVERLAPS (v_date_from, v_date_to + 1);
	
	GET DIAGNOSTICS v_row_count := ROW_COUNT;
	
	IF v_row_count >= 1 THEN
		RAISE EXCEPTION 'PERIOD_DUPLICATED_BY_POLICY_%_ITEM_%', v_policy_idx, v_it_code[v_i];
	ELSE
		INSERT INTO ".ZKP_SQL."_tb_apotik_policy (cus_code, ap_desc, ap_is_valid, ap_is_apply_all, ap_date_from, ap_date_to, ap_disc_pct, ap_basic_disc_pct, ap_remark, ap_created_by, ap_updated_by)
		VALUES (v_cus_code, v_desc, v_is_valid, v_is_apply_all, v_date_from, v_date_to, v_disc_pct, v_basic_disc_pct, v_remark, v_created_by, v_created_by);
	END IF;
	
	IF v_is_apply_all IS NOT TRUE THEN
		v_cur_ap_idx := currval('".ZKP_SQL."_tb_apotik_policy_ap_idx_seq');
		WHILE v_it_code[v_i] IS NOT NULL LOOP
			INSERT INTO ".ZKP_SQL."_tb_apotik_price (ap_idx, it_code)
			VALUES (v_cur_ap_idx, v_it_code[v_i]);
			v_i := v_i + 1;
		END LOOP;
	END IF;
END;
$body$ LANGUAGE plpgsql;

-- -------------------------------------------------------------------------------------------
--
-- -------------------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION addGroupPrice(
	v_cug_code varchar,
	v_desc varchar,
	v_basic_disc_pct numeric,
	v_disc_pct numeric,
	v_is_valid boolean,
	v_is_apply_all boolean,
	v_date_from date,
	v_date_to date,
	v_remark varchar,
	v_it_code varchar[],
	v_created_by varchar
) RETURNS void AS $body$
DECLARE
	v_i integer := 1;
	v_policy_idx integer;
	v_row_count integer := 0;
	v_cur_ag_idx integer := 0;
BEGIN
	-- Check the duplicated period
	SELECT INTO v_policy_idx ag_idx FROM ".ZKP_SQL."_tb_group_policy
	WHERE cug_code = v_cug_code AND (ag_date_from, ag_date_to + 1) OVERLAPS (v_date_from, v_date_to + 1);
	
	GET DIAGNOSTICS v_row_count := ROW_COUNT;
	IF v_row_count >= 1 THEN
		RAISE EXCEPTION 'PERIOD_DUPLICATED_BY_POLICY_%_ITEM_%', v_policy_idx, v_it_code[v_i];
	ELSE
		INSERT INTO ".ZKP_SQL."_tb_group_policy (cug_code, ag_desc, ag_is_valid, ag_is_apply_all, ag_date_from, ag_date_to, ag_disc_pct, ag_basic_disc_pct, ag_remark, ag_created_by, ag_updated_by)
		VALUES (v_cug_code, v_desc, v_is_valid, v_is_apply_all, v_date_from, v_date_to, v_disc_pct, v_basic_disc_pct, v_remark, v_created_by, v_created_by);
	END IF;
	
	IF v_is_apply_all IS NOT TRUE THEN
		v_cur_ag_idx := currval('".ZKP_SQL."_tb_group_policy_ag_idx_seq');
		WHILE v_it_code[v_i] IS NOT NULL LOOP
			INSERT INTO ".ZKP_SQL."_tb_group_price (ag_idx, it_code)
			VALUES (v_cur_ag_idx, v_it_code[v_i]);
			v_i := v_i + 1;
		END LOOP;
	END IF;
END;
$body$ LANGUAGE plpgsql;

-- -------------------------------------------------------------------------------------------
--
-- -------------------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION updateGroupPrice (
	v_idx integer,
	v_is_dirty_item boolean,
	v_cug_code varchar,
	v_desc varchar,
	v_basic_disc_pct numeric,
	v_disc_pct numeric,
	v_is_valid boolean,
	v_is_apply_all boolean,
	v_date_from date,
	v_date_to date,
	v_remark varchar,
	v_it_code varchar[],
	v_updated_by varchar
) RETURNS void AS $body$
DECLARE
	v_i integer := 1;
	v_row_count integer := 0;
	v_policy_idx integer;
BEGIN
	-- Check the duplicated period
	SELECT INTO v_policy_idx ag_idx FROM ".ZKP_SQL."_tb_group_policy
	WHERE cug_code = v_cug_code AND (ag_date_from, ag_date_to + 1) OVERLAPS (v_date_from, v_date_to + 1);
	
	GET DIAGNOSTICS v_row_count := ROW_COUNT;
	IF v_row_count > 1 THEN
		RAISE EXCEPTION 'PERIOD_DUPLICATED_BY_POLICY_%_ITEM_%', v_policy_idx, v_it_code[v_i];
	ELSE
		UPDATE ".ZKP_SQL."_tb_group_policy SET
			ag_desc = v_desc,
			ag_is_valid = v_is_valid,
			ag_is_apply_all = v_is_apply_all,
			ag_date_from = v_date_from,
			ag_date_to = v_date_to,
			ag_disc_pct = v_disc_pct,
			ag_remark = v_remark,
			ag_updated = CURRENT_TIMESTAMP,
			ag_updated_by = v_updated_by
		WHERE
			ag_idx = v_idx;
	END IF;

	IF v_is_apply_all IS TRUE THEN
		DELETE FROM ".ZKP_SQL."_tb_group_price WHERE ag_idx = v_idx;
	ELSIF v_is_dirty_item IS TRUE THEN
		DELETE FROM ".ZKP_SQL."_tb_group_price WHERE ag_idx = v_idx;
		WHILE v_it_code[v_i] IS NOT NULL LOOP
			INSERT INTO ".ZKP_SQL."_tb_group_price(ag_idx, it_code) VALUES(v_idx, v_it_code[v_i]);
			v_i := v_i + 1;
		END LOOP;
	END IF;
END;
$body$ LANGUAGE plpgsql;

-- -------------------------------------------------------------------------------------------
--
-- -------------------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION updateApotikPrice (
	v_idx integer,
	v_is_dirty_item boolean,
	v_cus_code varchar,
	v_desc varchar,
	v_basic_disc_pct numeric,
	v_disc_pct numeric,
	v_is_valid boolean,
	v_is_apply_all boolean,
	v_date_from date,
	v_date_to date,
	v_remark varchar,
	v_it_code varchar[],
	v_updated_by varchar
) RETURNS void AS $body$
DECLARE
	v_i integer := 1;
	v_row_count integer := 0;
	v_policy_idx integer;
BEGIN
	-- Check the duplicated period
	SELECT INTO v_policy_idx ap_idx FROM ".ZKP_SQL."_tb_apotik_policy
	WHERE cus_code = v_cus_code AND (ap_date_from, ap_date_to + 1) OVERLAPS (v_date_from, v_date_to + 1);
	
	GET DIAGNOSTICS v_row_count := ROW_COUNT;
	IF v_row_count > 1 THEN
		RAISE EXCEPTION 'PERIOD_DUPLICATED_BY_POLICY_%_ITEM_%', v_policy_idx, v_it_code[v_i];
	ELSE
		UPDATE ".ZKP_SQL."_tb_apotik_policy SET
			ap_desc = v_desc,
			ap_is_valid = v_is_valid,
			ap_is_apply_all = v_is_apply_all,
			ap_date_from = v_date_from,
			ap_date_to = v_date_to,
			ap_disc_pct = v_disc_pct,
			ap_remark = v_remark,
			ap_updated = CURRENT_TIMESTAMP,
			ap_updated_by = v_updated_by
		WHERE
			ap_idx = v_idx;
	END IF;

	IF v_is_apply_all IS TRUE THEN
		DELETE FROM ".ZKP_SQL."_tb_apotik_price WHERE ap_idx = v_idx;
	ELSIF v_is_dirty_item IS TRUE THEN
		DELETE FROM ".ZKP_SQL."_tb_apotik_price WHERE ap_idx = v_idx;
		WHILE v_it_code[v_i] IS NOT NULL LOOP
			INSERT INTO ".ZKP_SQL."_tb_apotik_price(ap_idx, it_code) VALUES(v_idx, v_it_code[v_i]);
			v_i := v_i + 1;
		END LOOP;
	END IF;
END;
$body$ LANGUAGE plpgsql;
