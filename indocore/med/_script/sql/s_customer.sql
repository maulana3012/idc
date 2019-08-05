-- --------------------------------------------
-- CUSTOMER GROUP
-- --------------------------------------------
CREATE TABLE ".ZKP_SQL."_tb_customer_group (
	cug_code char(2) NOT NULL,
	cug_name varchar(128) NOT NULL,
	cug_basic_disc_pct numeric(3,1) NOT NULL DEFAULT 0,
	cug_regtime date default CURRENT_DATE,
	cug_remark text,
	
	CONSTRAINT ".ZKP_SQL."_tb_customer_group_cug_code_pk PRIMARY KEY(cug_code)
);

-- --------------------------------------------
-- CUSTOMER TABLE
--  각 고객 유형별 데이터는 이 테이블을 상속받아서 자료를 저장할 것임.
-- --------------------------------------------
CREATE TABLE ".ZKP_SQL."_tb_customer(
	--primary  key
	cus_code char(7) NOT NULL,

	--foreign key
	cug_code char(2),
	
	cus_name varchar(128) NOT NULL,
	cus_full_name varchar(128) NOT NULL,
	cus_channel char(3) NOT NULL,
	cus_representative varchar(128),
	cus_company_title varchar(16),
	cus_type_of_biz varchar(64),
	cus_since date,
	cus_introduced_by varchar(128),

	--representative
	cus_contact varchar(128), -- contact person (marketing staff)
	cus_contact_position varchar(64),
	cus_contact_phone varchar(32),
	cus_contact_hphone varchar(32),
	cus_contact_email varchar(64),

	-- address information 
	cus_fax varchar(32),
	cus_address varchar(255),
	cus_phone varchar(32),
	
	CONSTRAINT ".ZKP_SQL."_tb_customer_cus_code_pk PRIMARY KEY(cus_code),
	CONSTRAINT ".ZKP_SQL."_tb_customer_cug_code_fk FOREIGN KEY(cug_code) REFERENCES ".ZKP_SQL."_tb_customer_group
		ON UPDATE CASCADE
		ON DELETE RESTRICT
);

CREATE INDEX ".ZKP_SQL."_tb_customer_cus_channel_idx ON ".ZKP_SQL."_tb_customer(cus_channel);

CREATE OR REPLACE FUNCTION addNewCusGroup(
	v_code varchar,
	v_name varchar,
	v_regtime date,
	v_remark varchar,
	v_basic_disc_pct numeric
) RETURNS void AS $body$
BEGIN
	INSERT INTO ".ZKP_SQL."_tb_customer_group(
		cug_code,
		cug_name,
		cug_regtime,
		cug_remark,
		cug_basic_disc_pct
	) VALUES (
		v_code,
		v_name,
		v_regtime,
		v_remark,
		v_basic_disc_pct);
END;
$body$ LANGUAGE plpgsql;

-- -----------------------------------------------------------
--
-- -----------------------------------------------------------
CREATE OR REPLACE FUNCTION updateCusGroup(
	v_code varchar,
	v_name varchar,
	v_regtime date,
	v_remark varchar,
	v_basic_disc_pct numeric
) RETURNS void AS $body$
DECLARE
BEGIN
UPDATE ".ZKP_SQL."_tb_customer_group SET 
	cug_name	= v_name,
	cug_regtime	= v_regtime,
	cug_remark	= v_remark,
	cug_basic_disc_pct = v_basic_disc_pct
WHERE
	cug_code	= v_code;
END;
$body$ LANGUAGE plpgsql;


---------------------------------
--  update customer
--------------------------------
CREATE OR REPLACE FUNCTION updateCustomer(
	v_code varchar,
	v_cug_code varchar,
	v_name varchar,
	v_full_name varchar,
	v_channel varchar,
	v_representative varchar,
	v_company_title varchar,
	v_type_of_biz varchar,
	v_since date,
	v_introduced_by varchar,
	v_contact varchar,
	v_contact_position varchar,
	v_contact_phone varchar,
	v_contact_hphone varchar,
	v_contact_email varchar,
	v_fax varchar,
	v_address varchar,
	v_phone varchar
) RETURNS void AS $body$
BEGIN
UPDATE ".ZKP_SQL."_tb_customer SET 
	cus_code	= v_code,
	cug_code	= v_cug_code,
	cus_name	= v_name,
	cus_full_name	= v_full_name,
	cus_channel	= v_channel,
	cus_representative	= v_representative,
	cus_company_title	= v_company_title,
	cus_type_of_biz	= v_type_of_biz,
	cus_since	= v_since,
	cus_introduced_by	= v_introduced_by,
	cus_contact	= v_contact,
	cus_contact_position	= v_contact_position,
	cus_contact_phone	= v_contact_phone,
	cus_contact_hphone	= v_contact_hphone,
	cus_contact_email	= v_contact_email,
	cus_fax	= v_fax,
	cus_address	= v_address,
	cus_phone	= v_phone
WHERE
	cus_code	= v_code;
END;
$body$ LANGUAGE plpgsql;

---------------------------------
--  new  customer
--------------------------------
CREATE OR REPLACE FUNCTION addNewCustomer(
	v_channel varchar,
	v_code varchar,
	v_since date,
	v_company_title varchar,
	v_full_name varchar,
	v_customer_group varchar,
	v_name varchar,
	v_representative varchar,
	v_introduced_by varchar,
	v_type_of_biz varchar,
	v_contact varchar,
	v_contact_position varchar,
	v_contact_phone varchar,
	v_contact_hphone varchar,
	v_contact_email varchar,
	v_address varchar,
	v_phone varchar,
	v_fax varchar
) RETURNS void AS $body$
BEGIN
INSERT INTO ".ZKP_SQL."_tb_customer(
	cus_code,
	cug_code,
	cus_name,
	cus_full_name,
	cus_channel,
	cus_representative,
	cus_company_title,
	cus_type_of_biz,
	cus_since,
	cus_introduced_by,
	cus_contact,
	cus_contact_position,
	cus_contact_phone,
	cus_contact_hphone,
	cus_contact_email,
	cus_fax,
	cus_address,
	cus_phone
) VALUES (
	v_code,
	v_customer_group,
	v_name,
	v_full_name,
	v_channel,
	v_representative,
	v_company_title,
	v_type_of_biz,
	v_since,
	v_introduced_by,
	v_contact,
	v_contact_position,
	v_contact_phone,
	v_contact_hphone,
	v_contact_email,
	v_fax,
	v_address,
	v_phone);
END;
$body$ LANGUAGE plpgsql;

