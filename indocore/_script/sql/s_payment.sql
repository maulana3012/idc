CREATE TABLE ".ZKP_SQL."_tb_payment(
	pay_idx SERIAL NOT NULL,
	bill_code char(12) NOT NULL,
	pay_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	pay_paid numeric(12,2) NOT NULL,
	pay_inputed_by varchar(32) NOT NULL,
	pay_inputed_timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	pay_remark varchar(255),

	CONSTRAINT ".ZKP_SQL."_tb_payment_bill_code_pk PRIMARY KEY(pay_idx),
	CONSTRAINT ".ZKP_SQL."_tb_payment_bill_code_fk FOREIGN KEY(bill_code) REFERENCES ".ZKP_SQL."_tb_billing(bill_code)
		ON DELETE CASCADE
		ON UPDATE CASCADE
);


--INSERT INTO ".ZKP_SQL."_tb_payment
--
CREATE OR REPLACE FUNCTION addNewPayment(
	v_code varchar,
	v_payment_date date,
	v_payment_paid numeric,
	v_remain_amount numeric,
	v_payment_remark varchar,
	v_inputed_by varchar
) RETURNS void AS $body$
DECLARE
	v_idx integer;
	v_i integer := 1;
BEGIN
		INSERT INTO ".ZKP_SQL."_tb_payment(bill_code, pay_date, pay_paid, pay_inputed_by, pay_remark)
		VALUES (v_code, v_payment_date, v_payment_paid, v_inputed_by, v_payment_remark);
END;
$body$ LANGUAGE plpgsql;


-- --------------------------------------------
-- CALCULATE REMAIN BILLING
-- --------------------------------------------
CREATE OR REPLACE FUNCTION update_remain_billing() RETURNS TRIGGER AS $body$
DECLARE
	v_bill_code varchar;
	v_delta numeric := 0;
	v_payment_date date;
BEGIN
	IF(TG_OP = 'DELETE') THEN
		v_bill_code = OLD.bill_code;
		v_delta = -1 * OLD.pay_paid;
		v_payment_date = null;

	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.bill_code != NEW.bill_code) THEN
			RAISE EXCEPTION 'Update of bill code is not allowed';
		END IF;
		
		v_bill_code = OLD.bill_code;
		v_delta = NEW.pay_paid - OLD.pay_paid;
		v_payment_date = CURRENT_DATE;

	ELSIF (TG_OP = 'INSERT') THEN
		v_bill_code = NEW.bill_code;
		v_delta = NEW.pay_paid;
		v_payment_date = CURRENT_DATE;
	END IF;

	-- Insert or update the summary row wirh the new values
	UPDATE ".ZKP_SQL."_tb_billing SET
		bill_remain_amount = bill_remain_amount - v_delta,
		bill_last_payment_date = v_payment_date
	WHERE bill_code = v_bill_code;

	RETURN NULL;
END;
$body$ LANGUAGE plpgsql;

-- ---------------------------------------------------------
--  CREATE TREAGGER
-- ---------------------------------------------------------
CREATE TRIGGER update_remain_billing AFTER INSERT OR UPDATE OR DELETE ON ".ZKP_SQL."_tb_payment
FOR EACH ROW EXECUTE PROCEDURE update_remain_billing();
