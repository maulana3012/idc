-- --------------------------------------------------------------------
--	Apotik invntory
--
--	when change order, delivery, sales, return, it will updated automatically BY TRIGGER
-- --------------------------------------------------------------------
CREATE TABLE ".ZKP_SQL."_tb_apotik_inv (
	cus_code char(7) NOT NULL,
	it_code char(6) NOT NULL,
	inv_updated date NOT NULL,
	inv_ok integer NOT NULL DEFAULT 0,
	inv_oo integer NOT NULL DEFAULT 0,
	inv_jk integer NOT NULL DEFAULT 0,
	inv_jo integer NOT NULL DEFAULT 0,
	inv_return integer NOT NULL DEFAULT 0,
	inv_sales integer NOT NULL DEFAULT 0,

	CONSTRAINT ".ZKP_SQL."_tb_apotik_inv_pk PRIMARY KEY (cus_code, it_code)
);

CREATE INDEX ".ZKP_SQL."_tb_apotik_inv_inv_updated_idx ON ".ZKP_SQL."_tb_apotik_inv(inv_updated);
