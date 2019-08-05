-- ---------------------------------------------------
-- Menu category
-- ---------------------------------------------------
CREATE TABLE ".ZKP_SQL."_tb_mat_cat (
	mcat_midx serial NOT NULL,
	mcat_pidx integer NOT NULL DEFAULT 0,
	mcat_depth smallint NOT NULL DEFAULT 1,
	mcat_islock_att boolean NOT NULL default false,
	mcat_code char(2),
	mcat_name varchar(64),
	
	CONSTRAINT ".ZKP_SQL."_tb_mat_cat_mcat_midx_pk PRIMARY KEY(mcat_midx),
	CONSTRAINT ".ZKP_SQL."_tb_mat_cat_mcat_pidx_fk FOREIGN KEY(mcat_pidx) REFERENCES ".ZKP_SQL."_tb_mat_cat
		ON UPDATE CASCADE
		ON DELETE CASCADE
);

-- Add Root Category It's Necessory
INSERT INTO ".ZKP_SQL."_tb_mat_cat (mcat_midx, mcat_pidx, mcat_depth, mcat_code, mcat_name)
VALUES (0, 0, 0, '', 'Category');

-- -------------------------------------------------------------
-- add New Menu categroy
-- -------------------------------------------------------------
CREATE OR REPLACE FUNCTION addNewMaterialCat(
	v_pidx integer,
	v_level integer,
	v_code varchar,
	v_name varchar
) RETURNS void AS $body$
BEGIN
	INSERT INTO ".ZKP_SQL."_tb_mat_cat (mcat_pidx, mcat_depth, mcat_code ,mcat_name)
	VALUES(v_pidx, v_level, v_code ,v_name);
END;
$body$ LANGUAGE 'plpgsql';

-- -------------------------------------------------------------
-- GET category path
--  make sure
-- INSERT INTO ".ZKP_SQL."_tb_mat_cat (mcat_midx, mcat_pidx, mcat_depth, mcat_code, mcat_name)
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
		SELECT INTO r_cat * FROM ".ZKP_SQL."_tb_mat_cat WHERE mcat_midx = v_idx;
		IF NOT FOUND THEN
			EXIT;
		ELSE 
			v_return := v_return || 'array(' ||
				r_cat.mcat_midx || ', ' ||
				r_cat.mcat_pidx || ', ' ||
				r_cat.mcat_depth ||', "' ||
				r_cat.mcat_code || '", "' ||
				r_cat.mcat_name || '")';
			EXIT WHEN  r_cat.mcat_midx = 0;
			v_return := v_return || ', ';
			v_idx := r_cat.mcat_pidx;
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
	v_t".ZKP_SQL."_tb_mat_cat integer;
	v_element integer[];
	v_lastdepth_idx integer[];
	v_return varchar := '';
BEGIN
	--$1 :  must be larger than 0
	SELECT INTO v_t".ZKP_SQL."_tb_mat_cat mcat_midx FROM ".ZKP_SQL."_tb_mat_cat WHERE mcat_pidx = $1;
	
	IF FOUND THEN
		v_element[v_i] := $1;
		WHILE v_element[v_j] IS NOT NULL LOOP
			FOR v_midx, v_depth IN
			SELECT mcat_midx, mcat_depth FROM ".ZKP_SQL."_tb_mat_cat WHERE mcat_pidx = v_element[v_j] LOOP
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

-- ---------------------------------------------------
-- Menu
-- ---------------------------------------------------
CREATE TABLE ".ZKP_SQL."_tb_menu (
  mn_code CHAR(3) NOT NULL,
  mcat_midx INTEGER NOT NULL,
  mn_name VARCHAR(255) NOT NULL,
  mn_price NUMERIC(12,2) NOT NULL DEFAULT 0.00,
  
  CONSTRAINT ".ZKP_SQL."_tb_menu_pk PRIMARY KEY (mn_code),
  CONSTRAINT ".ZKP_SQL."_tb_menu_mcat_midx_fk FOREIGN KEY(mcat_midx) REFERENCES ".ZKP_SQL."_tb_mat_cat
    ON UPDATE CASCADE
    ON DELETE RESTRICT
);
