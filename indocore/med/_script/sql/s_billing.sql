CREATE TABLE ".ZKP_SQL."_tb_billing(
	--for admin
	bill_code char(13) NOT NULL,
	bill_received_by varchar(32),
	bill_dept char(1),

	--for system
	bill_lastupdated_by_account varchar(32) NOT NULL,
	bill_lastupdated_timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	bill_revesion_time smallint NOT NULL DEFAULT 0,

	bill_npwp varchar(32),
	bill_inv_date date DEFAULT CURRENT_DATE,
	bill_sj_code varchar(32),
	bill_sj_date date,
	bill_po_no varchar(64),
	bill_po_date date,
	bill_vat numeric(3,1),
	bill_total_billing numeric(12,2) NOT NULL, 

	bill_cus_to char(7),
	bill_cus_to_name varchar(128),
	bill_cus_to_attn varchar(128),
	bill_cus_to_address varchar(255),

	bill_ship_to char(7),
	bill_ship_to_name varchar(128),

	bill_delivery_chk smallint NOT NULL DEFAULT 0,
	bill_payment_chk smallint NOT NULL DEFAULT 0,

	bill_delivery_by varchar(128),
	bill_delivery_freight_charge numeric(12,2) NOT NULL DEFAULT 0,

	bill_payment_widthin_days smallint DEFAULT 0,
	bill_payment_closing_on date,
	bill_payment_sj_inv_fp_tender varchar(8),
	bill_payment_cash_by varchar(128),
	bill_payment_check_by varchar(128),
	bill_payment_transfer_by varchar(128),
	bill_payment_giro_due date NOT NULL,
	bill_payment_giro_issue date,	
	bill_payment_bank varchar(12),
	bill_payment_bank_address varchar(255),

	bill_signature_by varchar(32),
	bill_paper_format char(1) default 'A',

	bill_remain_amount numeric(12,2) NOT NULL DEFAULT 0,
    bill_last_payment_date date,

	CONSTRAINT ".ZKP_SQL."_tb_billing_bill_code_pk PRIMARY KEY(bill_code),
	CONSTRAINT ".ZKP_SQL."_tb_billing_cus_code_fk FOREIGN KEY(bill_cus_to) REFERENCES ".ZKP_SQL."_tb_customer(cus_code)
		ON DELETE RESTRICT
		ON UPDATE CASCADE
);

CREATE TABLE ".ZKP_SQL."_tb_billing_item (
	bill_code char(13) NOT NULL,
	cus_code char(7) NOT NULL,
	icat_midx integer,	
	it_code char(6) NOT NULL,
	it_model_no varchar(64),
	it_type varchar(32),
	it_desc varchar(255),
	biit_inv_date date NOT NULL DEFAULT CURRENT_DATE,
	biit_qty smallint NOT NULL,
	biit_unit_price numeric(12,2),
	biit_remark varchar(255),

	CONSTRAINT ".ZKP_SQL."_tb_billing_item_pk PRIMARY KEY(bill_code, it_code),
	
	CONSTRAINT ".ZKP_SQL."_tb_billing_item_bill_code_fk FOREIGN KEY(bill_code) REFERENCES ".ZKP_SQL."_tb_billing
	ON DELETE CASCADE
	ON UPDATE CASCADE,

	CONSTRAINT ".ZKP_SQL."_tb_billing_item_it_code FOREIGN KEY(it_code) REFERENCES ".ZKP_SQL."_tb_item
	ON DELETE RESTRICT
	ON UPDATE CASCADE
);

--
--
--
CREATE OR REPLACE FUNCTION addNewApotikBilling(
		v_dept varchar, v_inv_date date, v_sj_code varchar, v_sj_date date, v_po_no varchar, v_po_date date,
		v_received_by varchar, v_cus_to varchar, v_cus_name varchar, v_cus_attn varchar, v_cus_address varchar,
		v_npwp varchar, v_ship_to varchar, v_ship_name varchar, v_vat integer,
		v_icat_midx integer[], v_it_code varchar[], v_it_model_no varchar[], v_it_type varchar[],
		v_it_desc varchar[], v_biit_unit_price numeric[], v_biit_qty integer[], v_biit_remark varchar[],
		v_delivery_chk integer, v_delivery_by varchar, v_delivery_freight_charge numeric,
		v_payment_chk integer, v_payment_widthin_days integer, v_payment_sj_inv_fp_tender varchar,
		v_payment_closing_on date, v_payment_cash_by varchar,
		v_payment_check_by varchar, v_payment_transfer_by varchar, v_payment_giro_due date,
		v_payment_giro_issue date, v_bank varchar, v_bank_address varchar, v_lastupdated_by_account varchar, v_total_amount numeric,
		v_signature_by varchar, v_paper_format varchar
) RETURNS varchar AS $body$
DECLARE
	v_i integer := 1;
	v_code varchar;
	v_sj_code2 varchar;
	v_sj_date2 date;
BEGIN
	SELECT INTO v_code getCurrentBillCode(v_vat, v_dept, v_inv_date);

	IF v_sj_code != '' THEN
		v_sj_code2 = v_sj_code;
		v_sj_date2 = v_sj_date;
	ELSE 
		v_sj_code2 = 'J' || substr(v_code,2,12);
		v_sj_date2 = v_inv_date;
	END IF;

	--Insert ".ZKP_SQL."_tb_billing
	INSERT INTO ".ZKP_SQL."_tb_billing(
		bill_code, bill_dept, bill_inv_date, bill_sj_code, bill_sj_date, bill_po_no, bill_po_date,
		bill_received_by, bill_cus_to, bill_cus_to_name, bill_cus_to_attn, bill_cus_to_address, bill_npwp,
		bill_ship_to, bill_ship_to_name, bill_vat,
		bill_delivery_chk, bill_delivery_by, bill_delivery_freight_charge,
		bill_payment_chk, bill_payment_widthin_days, bill_payment_sj_inv_fp_tender,
		bill_payment_closing_on, bill_payment_cash_by,
		bill_payment_check_by, bill_payment_transfer_by, bill_payment_giro_issue,
		bill_payment_giro_due, bill_payment_bank, bill_payment_bank_address, bill_lastupdated_by_account, 
		bill_total_billing, bill_remain_amount, bill_signature_by, bill_paper_format
	) VALUES (
		v_code, v_dept, v_inv_date, v_sj_code2, v_sj_date2, v_po_no, v_po_date,
		v_received_by, v_cus_to, v_cus_name, v_cus_attn, v_cus_address, v_npwp,
		v_ship_to, v_ship_name, v_vat, 
		v_delivery_chk, v_delivery_by, v_delivery_freight_charge,
		v_payment_chk, v_payment_widthin_days, v_payment_sj_inv_fp_tender,
		v_payment_closing_on, v_payment_cash_by,
		v_payment_check_by, v_payment_transfer_by, v_payment_giro_issue,
		v_payment_giro_due, v_bank, v_bank_address, v_lastupdated_by_account,
		v_total_amount, v_total_amount, v_signature_by, v_paper_format
	);

	WHILE v_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO ".ZKP_SQL."_tb_billing_item (
			bill_code, cus_code, icat_midx, it_code, it_model_no, it_type, it_desc,
			biit_inv_date, biit_qty, biit_unit_price, biit_remark
		) VALUES (
			v_code, v_cus_to, v_icat_midx[v_i], v_it_code[v_i],v_it_model_no[v_i], v_it_type[v_i], v_it_desc[v_i],
			v_inv_date, v_biit_qty[v_i], v_biit_unit_price[v_i], v_biit_remark[v_i]);
			v_i := v_i + 1;
	END LOOP;

	RETURN v_code;
END;
$body$ LANGUAGE plpgsql;


-- -----------------------------------------------------
-- UPDATE BILLING ORDER
-- -----------------------------------------------------
CREATE OR REPLACE FUNCTION reviseApotikBilling(
	v_code varchar,
	v_sj_code varchar,
	v_sj_date date,
	v_po_no varchar,
	v_po_date date,
	v_received_by varchar,
	v_revesion_time integer,
	v_cus_to varchar,
	v_cus_name varchar,
	v_cus_attn varchar,
	v_cus_address varchar,
	v_npwp varchar,
	v_ship_to varchar,
	v_ship_name varchar,
	v_icat_midx integer[],
	v_it_code varchar[],
	v_it_model_no varchar[],
	v_it_type varchar[],
	v_it_desc varchar[],
	v_biit_unit_price numeric[],
	v_biit_qty integer[],
	v_biit_remark varchar[],
	v_delivery_chk integer,
	v_delivery_by varchar,
	v_delivery_freight_charge numeric,
	v_payment_chk integer,
	v_payment_widthin_days integer, 
	v_payment_sj_inv_fp_tender varchar,
	v_payment_closing_on date, 
	v_payment_cash_by varchar,
	v_payment_check_by varchar, 
	v_payment_transfer_by varchar, 
	v_payment_giro_due date,
	v_payment_giro_issue date,
	v_bank varchar,
 	v_bank_address varchar,
	v_lastupdated_by_account varchar,
	v_total_amount numeric,
	v_signature_by varchar,
	v_paper_format varchar
) RETURNS void AS $body$
DECLARE
	v_i integer := 1;
BEGIN
	UPDATE ".ZKP_SQL."_tb_billing SET 
		bill_lastupdated_by_account = v_lastupdated_by_account,
		bill_lastupdated_timestamp	= CURRENT_TIMESTAMP,
		bill_sj_code		= v_sj_code,
		bill_sj_date		= v_sj_date,
		bill_po_no 			= v_po_no,
		bill_po_date		= v_po_date,
		bill_received_by 	= v_received_by,
		bill_revesion_time 	= V_revesion_time + 1,
		bill_cus_to 		= v_cus_to,
		bill_cus_to_name	= v_cus_name,
		bill_cus_to_attn	= v_cus_attn,
		bill_cus_to_address = v_cus_address,
		bill_ship_to		= v_ship_to,
		bill_ship_to_name 		= v_ship_name,
		bill_npwp				= v_npwp,
		bill_delivery_chk 		= v_delivery_chk,
		bill_delivery_by 		= v_delivery_by,
		bill_delivery_freight_charge  = v_delivery_freight_charge,
		bill_payment_chk 			  = v_payment_chk,
		bill_payment_widthin_days 	  = v_payment_widthin_days,
		bill_payment_sj_inv_fp_tender = v_payment_sj_inv_fp_tender,
		bill_payment_closing_on 	= v_payment_closing_on,
		bill_payment_cash_by 		= v_payment_cash_by,
		bill_payment_check_by 		= v_payment_check_by,
		bill_payment_transfer_by 	= v_payment_transfer_by,
		bill_payment_giro_due 		= v_payment_giro_due,
		bill_payment_giro_issue 	= v_payment_giro_issue,
		bill_payment_bank			= v_bank,
		bill_payment_bank_address	= v_bank_address,
		bill_total_billing			= v_total_amount,
		bill_remain_amount			= v_total_amount,
		bill_signature_by		= v_signature_by,
		bill_paper_format	= v_paper_format
	WHERE
		bill_code = v_code;

	DELETE FROM ".ZKP_SQL."_tb_billing_item WHERE bill_code = v_code;

	WHILE v_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO ".ZKP_SQL."_tb_billing_item (
			bill_code, cus_code, icat_midx, it_code, it_model_no, it_type,
			it_desc, biit_qty, biit_unit_price, biit_remark
		) VALUES (
			v_code, v_cus_to, v_icat_midx[v_i], v_it_code[v_i],v_it_model_no[v_i], v_it_type[v_i],
			v_it_desc[v_i], v_biit_qty[v_i], v_biit_unit_price[v_i], v_biit_remark[v_i]);
			v_i := v_i + 1;
	END LOOP;

END;
$body$ LANGUAGE plpgsql;


--
-- Get current bill code 13char
-- IO-00001A-I07
-- IX-00002H-I07
CREATE OR REPLACE FUNCTION getCurrentBillCode(
	v_vat integer,
	v_dept varchar,
	v_inv_date date
) RETURNS varchar AS $body$
DECLARE
	v_current_month varchar[] := ARRAY['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
	v_month integer := extract(MONTH FROM v_inv_date) + 0;
	v_monyy varchar := v_current_month[v_month] || to_char(v_inv_date, 'YY');
	v_new_code varchar;
	v_serial integer;
	v_tax varchar;
BEGIN

	IF v_vat = 0 THEN
		v_tax := 'X';
		SELECT INTO v_serial max(substr(bill_code, 4, 5))
		FROM ".ZKP_SQL."_tb_billing WHERE substr(bill_code, 9,1) = v_dept AND substr(bill_code, 11, 1) = substr(v_monyy,1,1);

	ELSE
		v_tax := 'O';
		SELECT INTO v_serial max(substr(bill_code, 4, 5))
		FROM ".ZKP_SQL."_tb_billing WHERE substr(bill_code, 9,1) = v_dept AND substr(bill_code, 11, 3) = v_monyy;
	END IF;


	IF v_serial IS NULL THEN
		v_serial := 1;
	ELSE
		v_serial := v_serial + 1;
	END IF;

	v_new_code := 'I' || v_tax || '-' || lpad(v_serial, 5, '0') || v_dept || '-' || v_monyy;

	RETURN v_new_code;
END;
$body$ LANGUAGE plpgsql;
