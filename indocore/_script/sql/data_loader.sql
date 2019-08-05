-- copy customer data
COPY ".ZKP_SQL."_tb_customer (
	cus_channel,
	cus_code,
	cus_full_name,
	cus_name,
	cus_company_title,
	cus_type_of_biz,
	cus_contact,
	cus_contact_position,
	cus_since,
	cus_introduced_by)
FROM $$C:\www\root\indocore\_user_data\sql\indocore_customer_code.txt$$
WITH NULL AS '';

UPDATE ".ZKP_SQL."_tb_customer set cus_channel = lpad(cus_channel, 3, '0');

--SETTING  GROUP PERMISSION
INSERT INTO ".ZKP_SQL."_tb_grade(gr_idx, gr_name, gr_isdefault, gr_desc)
VALUES ('200', 'GENERAL MANAGER', 'F', 'SYSTEM GROUP');

INSERT INTO ".ZKP_SQL."_tb_grade(gr_idx, gr_name, gr_isdefault, gr_desc)
VALUES ('210', 'DEALER ADMIN', 'F', 'SYSTEM GROUP');

INSERT INTO ".ZKP_SQL."_tb_grade(gr_idx, gr_name, gr_isdefault, gr_desc)
VALUES ('220', 'APOTIK ADMIN', 'F', 'SYSTEM GROUP');

INSERT INTO ".ZKP_SQL."_tb_grade(gr_idx, gr_name, gr_isdefault, gr_desc)
VALUES ('230', 'PHARMACEUTICAL ADMIN', 'F', 'SYSTEM GROUP');

INSERT INTO ".ZKP_SQL."_tb_grade(gr_idx, gr_name, gr_isdefault, gr_desc)
VALUES ('240', 'HOSPITAL ADMIN', 'F', 'SYSTEM GROUP');

INSERT INTO ".ZKP_SQL."_tb_grade(gr_idx, gr_name, gr_isdefault, gr_desc)
VALUES ('300', 'MARKETING', 'F', 'SYSTEM GROUP');
