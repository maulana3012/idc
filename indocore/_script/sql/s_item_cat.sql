CREATE TABLE ".ZKP_SQL."_tb_item_cat (
	icat_midx serial NOT NULL,
	icat_pidx integer NOT NULL DEFAULT 0,
	icat_depth smallint NOT NULL DEFAULT 1,
	icat_code varchar(8),
	icat_name varchar(32),
	
	CONSTRAINT ".ZKP_SQL."_tb_item_cat_icat_midx_pk PRIMARY KEY(icat_midx),
	CONSTRAINT ".ZKP_SQL."_tb_item_cat_icat_pidx_fk FOREIGN KEY(icat_pidx) REFERENCES ".ZKP_SQL."_tb_item_cat
		ON UPDATE CASCADE
		ON DELETE CASCADE
);

-- --------------------------------------------
-- ITEM TABLE
-- --------------------------------------------
CREATE TABLE ".ZKP_SQL."_tb_item (
	it_code char(6) NOT NULL,
	icat_midx integer NOT NULL,
	it_model_no varchar(64),
	it_type varchar(32),
	it_desc varchar(255),
	it_remark varchar(255),

	CONSTRAINT ".ZKP_SQL."_tb_item_it_code_pk PRIMARY KEY(it_code),
	CONSTRAINT ".ZKP_SQL."_tb_item_icat_midx_fk FOREIGN KEY(icat_midx) REFERENCES ".ZKP_SQL."_tb_item_cat
		ON UPDATE CASCADE
		ON DELETE RESTRICT
);

-- Add Root Category It's Necessory
INSERT INTO ".ZKP_SQL."_tb_item_cat (icat_midx, icat_pidx, icat_depth, icat_code, icat_name)
VALUES (0, 0, 0, '', 'Category');

-- --------------------------------------------
-- item price.
--	The price can be change by period. but before price must be saved
-- --------------------------------------------
CREATE TABLE ".ZKP_SQL."_tb_item_price (
	ip_idx serial NOT NULL,
	it_code char(6) NOT NULL,
	ip_date_from date NOT NULL,
	ip_date_to date, -- IF null, current_price.
	ip_user_price numeric(12,2) NOT NULL DEFAULT 0,
	ip_remark varchar(255),
	ip_updated timestamp DEFAULT CURRENT_TIMESTAMP,
	ip_created timestamp DEFAULT CURRENT_TIMESTAMP,
	ip_updated_by varchar(32),
	ip_created_by varchar(32),

	CONSTRAINT ".ZKP_SQL."_tb_item_price_ip_idx_pk PRIMARY KEY(ip_idx),
	CONSTRAINT ".ZKP_SQL."_tb_item_price_it_code_fk FOREIGN KEY(it_code) REFERENCES ".ZKP_SQL."_tb_item
		ON UPDATE CASCADE
		ON DELETE CASCADE
);

--INSERT INTO ".ZKP_SQL."_tb_item_price (it_code, ip_date_from, ip_date_to, ip_user_price)
--SELECT it_code, '2007-1-1', NULL, it_unit_price FROM ".ZKP_SQL."_tb_item;


-- -------------------------------------------------------------
-- add New Item categroy
-- -------------------------------------------------------------
CREATE OR REPLACE FUNCTION addNewItemCat(
	v_pidx integer,
	v_level integer,
	v_code varchar,
	v_name varchar
) RETURNS void AS $body$
BEGIN
	INSERT INTO ".ZKP_SQL."_tb_item_cat (icat_pidx, icat_depth, icat_code ,icat_name)
	VALUES(v_pidx, v_level, v_code ,v_name);
END;
$body$ LANGUAGE 'plpgsql';

-- -------------------------------------------------------------
-- add New Item categroy
--  make sure
-- INSERT INTO ".ZKP_SQL."_tb_item_cat (icat_midx, icat_pidx, icat_depth, icat_code, icat_name)
-- VALUES (0, 0, 0, 'ROOT', 'ROOT');
-- -------------------------------------------------------------
CREATE OR REPLACE FUNCTION getCategoryPath(
	v_midx integer
) RETURNS varchar AS $body$
DECLARE
	r_cat RECORD;
	v_idx integer := 0;
	v_return varchar := '';
BEGIN
	v_idx = v_midx;
	LOOP
		SELECT INTO r_cat * FROM ".ZKP_SQL."_tb_item_cat WHERE icat_midx = v_idx;
		IF NOT FOUND THEN
			EXIT;
		ELSE 
			v_return := v_return || 'array(' ||
				r_cat.icat_midx || ', ' ||
				r_cat.icat_pidx || ', ' ||
				r_cat.icat_depth ||', "' ||
				r_cat.icat_code || '", "' ||
				r_cat.icat_name || '")';
			EXIT WHEN  r_cat.icat_midx = 0;
			v_return := v_return || ', ';
			v_idx := r_cat.icat_pidx;
		END IF;
	END LOOP;
	RETURN '$path = array(' || v_return || ');';
END;
$body$ LANGUAGE 'plpgsql';

--------------------------------------------------------
-- get Sub-Category
--------------------------------------------------------
CREATE OR REPLACE FUNCTION getSubCategory (integer) RETURNS varchar AS $body$
DECLARE
	v_midx integer := 0;
	v_depth integer := 0;
	v_i integer := 1;
	v_j integer := 1;
	v_temp integer;
	v_element integer[];
	v_lastdepth_idx integer[];
	v_return varchar := '';
BEGIN
	--$1 :  must be larger than 0
	SELECT INTO v_temp icat_midx FROM ".ZKP_SQL."_tb_item_cat WHERE icat_pidx = $1;
	
	IF FOUND THEN
		v_element[v_i] := $1;
		WHILE v_element[v_j] IS NOT NULL LOOP
			FOR v_midx, v_depth IN
			SELECT icat_midx, icat_depth FROM ".ZKP_SQL."_tb_item_cat WHERE icat_pidx = v_element[v_j] LOOP
				v_i = v_i + 1; -- asign element index that will search.
				v_element[v_i] = v_midx;
				IF v_depth = 3 THEN
					v_lastdepth_idx := array_append(v_lastdepth_idx, v_midx);
				END IF;
			END LOOP;
			v_j := v_j + 1; --next element index
		END LOOP;
		v_return := array_to_string(v_lastdepth_idx, ', ');
	ELSE
		v_return := $1;
	END IF;

	RETURN v_return;
END;
$body$ LANGUAGE plpgsql;

-- -------------------------------------------------------------
-- add New Item 
-- -------------------------------------------------------------
CREATE OR REPLACE FUNCTION addNewItem(
	v_code varchar,
	v_midx integer,
	v_model_no varchar,
	v_type varchar,
	v_desc varchar,
	v_user_price numeric,
	v_date_from date,
	v_remark varchar
) RETURNS void AS $body$
BEGIN
	INSERT INTO ".ZKP_SQL."_tb_item(it_code, icat_midx, it_model_no, it_type, it_desc, it_remark)
	VALUES (v_code, v_midx, v_model_no, v_type, v_desc, v_remark);
	
	INSERT INTO ".ZKP_SQL."_tb_item_price (it_code, ip_date_from, ip_user_price)
	VALUES (v_code, v_date_from, v_user_price);
END;
$body$ LANGUAGE 'plpgsql';

---------------------------------
-- Update Item
--------------------------------
CREATE OR REPLACE FUNCTION updateItem(
	v_code varchar,
	v_midx integer,
	v_model_no varchar,
	v_type varchar,
	v_desc varchar,
	v_remark varchar
) RETURNS void AS $body$
BEGIN
UPDATE ".ZKP_SQL."_tb_item SET 
	icat_midx	= v_midx,
	it_model_no	= v_model_no,
	it_type	= v_type,
	it_desc	= v_desc,
	it_remark	= v_remark
WHERE
	it_code	= v_code;
END;
$body$ LANGUAGE plpgsql;

-- ---------------------------------------------------
-- update Item Price
-- ---------------------------------------------------
CREATE OR REPLACE FUNCTION updateItemPrice(
	v_code varchar,
	v_idx integer,
	v_date_from date,
	v_user_price numeric,
	v_remark varchar,
	v_updated_by varchar
) RETURNS void AS $body$
DECLARE
	v_last_ip_idx integer;
BEGIN
	-- search the last date_to & change it's date to date
	SELECT INTO v_last_ip_idx max(ip_idx) FROM ".ZKP_SQL."_tb_item_price
	WHERE it_code = v_code AND ip_idx != v_idx;

	IF v_last_ip_idx != v_idx THEN
		-- set last close date
		UPDATE ".ZKP_SQL."_tb_item_price SET ip_date_to = v_date_from - 1
		WHERE ip_idx = v_last_ip_idx;
	END IF;

	UPDATE ".ZKP_SQL."_tb_item_price SET
		ip_date_from = v_date_from,
		ip_user_price = v_user_price,
		ip_remark = v_remark,
		ip_updated = CURRENT_TIMESTAMP,
		ip_updated_by = v_updated_by			
	WHERE ip_idx = v_idx;
END;
$body$ LANGUAGE plpgsql;


-- ---------------------------------------------------
-- make New Price
-- ---------------------------------------------------
CREATE OR REPLACE FUNCTION makeNewPrice(
	v_code varchar,
	v_date_from date,
	v_user_price numeric,
	v_remark varchar,
	v_created_by varchar
) RETURNS void AS $body$
DECLARE
	v_last_ip_idx integer;
BEGIN

	SELECT INTO v_last_ip_idx max(ip_idx) FROM ".ZKP_SQL."_tb_item_price
	WHERE it_code = v_code;

	UPDATE ".ZKP_SQL."_tb_item_price SET ip_date_to = v_date_from - 1
	WHERE ip_idx = v_last_ip_idx;

	INSERT INTO ".ZKP_SQL."_tb_item_price (it_code, ip_date_from, ip_user_price, ip_remark, ip_updated, ip_created, ip_created_by, ip_updated_by)
	VALUES(v_code, v_date_from, v_user_price, v_remark, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, v_created_by, v_created_by);
END;
$body$ LANGUAGE plpgsql;

-- ------------------------------------------------------------------
-- get Current price
-- -------------------------------------------------------------------
CREATE OR REPLACE FUNCTION ".ZKP_SQL."_getUserPrice(
	v_code varchar, -- it_code
	v_date date
) RETURNS numeric AS $body$
DECLARE
	v_unit_price numeric;
BEGIN
	SELECT INTO v_unit_price ip_user_price
	FROM ".ZKP_SQL."_tb_item_price
	WHERE it_code = v_code AND ip_date_from <= v_date AND ip_date_to + 1 > v_date;

	IF NOT FOUND THEN
		SELECT INTO v_unit_price ip_user_price
		FROM ".ZKP_SQL."_tb_item_price WHERE ip_idx = (SELECT max(ip_idx) FROM ".ZKP_SQL."_tb_item_price WHERE it_code = v_code);
	END IF;

	RETURN v_unit_price;
END;
$body$ LANGUAGE plpgsql;
