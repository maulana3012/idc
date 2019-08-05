create function idc_addapotikprice(v_cus_code character varying, v_desc character varying, v_basic_disc_pct numeric, v_disc_pct numeric, v_is_valid boolean, v_is_apply_all boolean, v_date_from date, v_date_to date, v_remark character varying, v_it_code character varying[], v_created_by character varying) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_policy_idx integer;
	v_row_count integer := 0;
	v_row_count2 integer := 0;
	v_cur_ap_idx integer := 0;
BEGIN

	-- Check the duplicated period
	SELECT INTO v_policy_idx ap_idx FROM idc_tb_apotik_policy
	WHERE cus_code = v_cus_code AND (ap_date_from, ap_date_to + 1) OVERLAPS (v_date_from, v_date_to + 1);

	GET DIAGNOSTICS v_row_count := ROW_COUNT;
	IF v_row_count >= 1 THEN
		-- Check the duplicated disc
		SELECT INTO v_policy_idx ap_idx FROM idc_tb_apotik_policy
		WHERE cus_code = v_cus_code AND (ap_date_from, ap_date_to + 1) OVERLAPS (v_date_from, v_date_to + 1) AND ap_disc_pct = v_disc_pct;

		GET DIAGNOSTICS v_row_count2 := ROW_COUNT;
		IF v_row_count2 >= 1 THEN
			RAISE EXCEPTION 'PERIOD_DUPLICATED_BY_POLICY_%_ITEM_%', v_policy_idx, v_it_code[v_i];
		ELSE
			INSERT INTO idc_tb_apotik_policy (cus_code, ap_desc, ap_is_valid, ap_is_apply_all, ap_date_from, ap_date_to, ap_disc_pct, ap_basic_disc_pct, ap_remark, ap_created_by, ap_updated_by)
			VALUES (v_cus_code, v_desc, v_is_valid, v_is_apply_all, v_date_from, v_date_to, v_disc_pct, v_basic_disc_pct, v_remark, v_created_by, v_created_by);
		END IF;
	ELSE
		INSERT INTO idc_tb_apotik_policy (cus_code, ap_desc, ap_is_valid, ap_is_apply_all, ap_date_from, ap_date_to, ap_disc_pct, ap_basic_disc_pct, ap_remark, ap_created_by, ap_updated_by)
		VALUES (v_cus_code, v_desc, v_is_valid, v_is_apply_all, v_date_from, v_date_to, v_disc_pct, v_basic_disc_pct, v_remark, v_created_by, v_created_by);
	END IF;

	IF v_is_apply_all IS NOT TRUE THEN
		v_cur_ap_idx := currval('idc_tb_apotik_policy_ap_idx_seq');
		WHILE v_it_code[v_i] IS NOT NULL LOOP
			INSERT INTO idc_tb_apotik_price (ap_idx, it_code) VALUES (v_cur_ap_idx, v_it_code[v_i]);
			v_i := v_i + 1;
		END LOOP;
	END IF;

END;
$$
;

alter function idc_addapotikprice(varchar, varchar, numeric, numeric, boolean, boolean, date, date, varchar, character varying[], varchar) owner to dskim
;

create function idc_addcrosstransfer(v_code character varying, v_payment_date date, v_method character varying, v_bank character varying, v_remark character varying, v_inputed_by character varying, v_pay_idx integer[]) returns void
	language plpgsql
as $$
DECLARE
	rec record;
	v_i integer := 1;
BEGIN

	v_i = 1;
	WHILE v_pay_idx[v_i] IS NOT NULL LOOP
		FOR rec IN SELECT cus_code, pay_dept, pay_paid, pay_method, pay_date, pay_bank FROM idc_tb_payment WHERE pay_idx = v_pay_idx[v_i] LOOP
			INSERT INTO idc_tb_payment(bill_code, cus_code, pay_dept, pay_date, pay_paid, pay_inputed_by, pay_method, pay_bank, pay_note, pay_is_deposit_cross_ref, pay_remark)
			VALUES (v_code, rec.cus_code, rec.pay_dept, rec.pay_date, rec.pay_paid*-1, v_inputed_by, rec.pay_method, rec.pay_bank, 'CROSS_TRANSFER-', v_pay_idx[v_i], v_remark);

			INSERT INTO idc_tb_payment(bill_code, cus_code, pay_dept, pay_date, pay_paid, pay_inputed_by, pay_method, pay_bank, pay_note, pay_is_deposit_cross_ref, pay_remark)
			VALUES (v_code, rec.cus_code, rec.pay_dept, v_payment_date, rec.pay_paid, v_inputed_by, v_method, v_bank, 'CROSS_TRANSFER+', v_pay_idx[v_i], v_remark);

			UPDATE idc_tb_payment SET pay_is_deposit_cross=true WHERE pay_idx = v_pay_idx[v_i];
			UPDATE idc_tb_deposit SET dep_is_deposit_cross=true WHERE pay_idx = v_pay_idx[v_i];
		END LOOP;
		v_i := v_i + 1;
	END LOOP;

END;
$$
;

alter function idc_addcrosstransfer(varchar, date, varchar, varchar, varchar, varchar, integer[]) owner to dskim
;

create function idc_addgorupprice(v_cug_code character varying, v_desc character varying, v_basic_disc_pct numeric, v_disc_pct numeric, v_is_valid boolean, v_is_apply_all boolean, v_date_from date, v_date_to date, v_remark character varying, v_it_code character varying[], v_created_by character varying) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_policy_idx integer;
	v_row_count integer := 0;
	v_cur_ag_idx integer := 0;
BEGIN
	-- Check the duplicated period
	SELECT INTO v_policy_idx ag_idx FROM idc_group_policy WHERE cug_code = v_cug_code AND (ag_date_from, ag_date_to + 1) OVERLAPS (v_date_from, v_date_to + 1);

	GET DIAGNOSTICS v_row_count := ROW_COUNT;
	IF v_row_count >= 1 THEN
		RAISE EXCEPTION 'PERIOD_DUPLICATED_BY_POLICY_%_ITEM_%', v_policy_idx, v_it_code[v_i];
	ELSE
		INSERT INTO idc_group_policy (cug_code, ag_desc, ag_is_valid, ag_is_apply_all, ag_date_from, ag_date_to, ag_disc_pct, ag_basic_disc_pct, ag_remark, ag_created_by, ag_updated_by)
		VALUES (v_cug_code, v_desc, v_is_valid, v_is_apply_all, v_date_from, v_date_to, v_disc_pct, v_basic_disc_pct, v_remark, v_created_by, v_created_by);
	END IF;

	IF v_is_apply_all IS NOT TRUE THEN
		v_cur_ag_idx := currval('idc_group_policy_ag_idx_seq');
		WHILE v_it_code[v_i] IS NOT NULL LOOP
			INSERT INTO idc_group_price (ag_idx, it_code)
			VALUES (v_cur_ag_idx, v_it_code[v_i]);
			v_i := v_i + 1;
		END LOOP;
	END IF;
END;
$$
;

alter function idc_addgorupprice(varchar, varchar, numeric, numeric, boolean, boolean, date, date, varchar, character varying[], varchar) owner to dskim
;

create function idc_addgrade(v_gridx integer, v_groupname character varying, v_isdefault boolean, v_desc character varying) returns void
	language plpgsql
as $$
BEGIN
	IF v_isdefault THEN
		UPDATE idc_tb_grade SET gr_isdefault = false WHERE gr_isdefault = true;
	END IF;

	INSERT INTO idc_tb_grade(gr_idx, gr_name, gr_isdefault, gr_desc)
	VALUES(v_gridx, v_groupname, v_isdefault, v_desc);
END;
$$
;

alter function idc_addgrade(integer, varchar, boolean, varchar) owner to dskim
;

create function idc_addgroupprice(v_cug_code character varying, v_desc character varying, v_basic_disc_pct numeric, v_disc_pct numeric, v_is_valid boolean, v_is_apply_all boolean, v_date_from date, v_date_to date, v_remark character varying, v_it_code character varying[], v_created_by character varying) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_policy_idx integer;
	v_row_count integer := 0;
	v_row_count2 integer := 0;
	v_cur_ag_idx integer := 0;
BEGIN

	-- Check the duplicated period
	SELECT INTO v_policy_idx ag_idx FROM idc_tb_group_policy
	WHERE cug_code = v_cug_code AND (ag_date_from, ag_date_to + 1) OVERLAPS (v_date_from, v_date_to + 1);

	GET DIAGNOSTICS v_row_count := ROW_COUNT;
	IF v_row_count >= 1 THEN
		-- Check the duplicated disc
		SELECT INTO v_policy_idx ag_idx FROM idc_tb_group_policy
		WHERE cug_code = v_cug_code AND (ag_date_from, ag_date_to + 1) OVERLAPS (v_date_from, v_date_to + 1) AND ag_disc_pct = v_disc_pct;

		GET DIAGNOSTICS v_row_count2 := ROW_COUNT;
		IF v_row_count2 >= 1 THEN
			RAISE EXCEPTION 'PERIOD_DUPLICATED_BY_POLICY_%_ITEM_%', v_policy_idx, v_it_code[v_i];
		ELSE
			INSERT INTO idc_tb_group_policy (cug_code, ag_desc, ag_is_valid, ag_is_apply_all, ag_date_from, ag_date_to, ag_disc_pct, ag_basic_disc_pct, ag_remark, ag_created_by, ag_updated_by)
			VALUES (v_cug_code, v_desc, v_is_valid, v_is_apply_all, v_date_from, v_date_to, v_disc_pct, v_basic_disc_pct, v_remark, v_created_by, v_created_by);
		END IF;
	ELSE
		INSERT INTO idc_tb_group_policy (cug_code, ag_desc, ag_is_valid, ag_is_apply_all, ag_date_from, ag_date_to, ag_disc_pct, ag_basic_disc_pct, ag_remark, ag_created_by, ag_updated_by)
		VALUES (v_cug_code, v_desc, v_is_valid, v_is_apply_all, v_date_from, v_date_to, v_disc_pct, v_basic_disc_pct, v_remark, v_created_by, v_created_by);
	END IF;

	IF v_is_apply_all IS NOT TRUE THEN
		v_cur_ag_idx := currval('idc_tb_group_policy_ag_idx_seq');
		WHILE v_it_code[v_i] IS NOT NULL LOOP
			INSERT INTO idc_tb_group_price (ag_idx, it_code) VALUES (v_cur_ag_idx, v_it_code[v_i]);
			v_i := v_i + 1;
		END LOOP;
	END IF;
END;
$$
;

alter function idc_addgroupprice(varchar, varchar, numeric, numeric, boolean, boolean, date, date, varchar, character varying[], varchar) owner to dskim
;

create function idc_addnewapotikdeptbilling(v_dept character varying, v_ordered_by integer, v_type_invoice integer, v_inv_date date, v_sj_code character varying, v_sj_date date, v_po_no character varying, v_po_date date, v_received_by character varying, v_cus_to character varying, v_cus_name character varying, v_cus_attn character varying, v_cus_address character varying, v_npwp character varying, v_ship_to character varying, v_ship_name character varying, v_pajak_to character varying, v_pajak_name character varying, v_pajak_address character varying, v_vat integer, v_icat_midx integer[], v_it_code character varying[], v_it_model_no character varying[], v_it_type character varying[], v_it_desc character varying[], v_biit_unit_price numeric[], v_biit_qty integer[], v_biit_remark character varying[], v_delivery_chk integer, v_delivery_by character varying, v_delivery_warehouse character varying, v_delivery_franco character varying, v_delivery_freight_charge numeric, v_payment_chk integer, v_payment_widthin_days integer, v_payment_sj_inv_fp_tender character varying, v_payment_closing_on date, v_payment_for_the_month_week character varying, v_payment_cash_by character varying, v_payment_check_by character varying, v_payment_transfer_by character varying, v_payment_giro_due date, v_payment_giro_issue date, v_bank character varying, v_bank_address character varying, v_lastupdated_by_account character varying, v_disc numeric, v_total_amount numeric, v_signature_by character varying, v_signature_pajak_by character varying, v_paper_format character varying, v_tukar_faktur_date date, v_amount_before_vat numeric, v_type_pajak character varying, v_sales_from date, v_sales_to date) returns character varying
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_code varchar;
	v_vat_inv_no varchar;
	v_sj_code2 varchar;
	v_sj_date2 date;
	v_issued_date timestamp;
BEGIN
	SELECT INTO v_code getCurrentBillCode(v_ordered_by, v_vat, v_dept, v_inv_date, v_type_pajak);

	IF v_vat > 0 AND v_type_pajak = 'IO' THEN
		v_vat_inv_no = '010.000-' || substr(v_code, 12, 2) || '.' || lpad(substr(v_code,4,5), 8, '0');
		v_issued_date = CURRENT_TIMESTAMP;
	ELSIF v_vat > 0 AND v_type_pajak = 'IP' THEN
		v_vat_inv_no = v_code;
		v_issued_date = CURRENT_TIMESTAMP;
	ELSIF v_vat = 0 THEN
		v_issued_date = v_inv_date;
	END IF;

	IF v_sj_code != '' THEN
		v_sj_code2 = v_sj_code;
		v_sj_date2 = v_sj_date;
	ELSE
		v_sj_code2 = 'J' || substr(v_code,2,12);
		v_sj_date2 = v_inv_date;
	END IF;

	--Insert idc_billing
	INSERT INTO idc_billing(
		bill_code, bill_ordered_by, bill_type_invoice, bill_dept, bill_inv_date, bill_vat_inv_no, bill_sj_code, bill_sj_date, bill_po_no, bill_po_date,
		bill_received_by, bill_cus_to, bill_cus_to_name, bill_cus_to_attn, bill_cus_to_address, bill_npwp,
		bill_ship_to, bill_ship_to_name, bill_pajak_to, bill_pajak_to_name, bill_pajak_to_address, bill_vat,
		bill_delivery_chk, bill_delivery_by, bill_delivery_warehouse, bill_delivery_franco, bill_delivery_freight_charge,
		bill_payment_chk, bill_payment_widthin_days, bill_payment_sj_inv_fp_tender,
		bill_payment_closing_on, bill_payment_for_the_month_week, bill_payment_cash_by,
		bill_payment_check_by, bill_payment_transfer_by, bill_payment_giro_issue,
		bill_payment_giro_due, bill_payment_bank, bill_payment_bank_address, bill_lastupdated_by_account,
		bill_discount, bill_total_billing, bill_total_billing_rev, bill_remain_amount, bill_signature_by, bill_signature_pajak_by,
		bill_paper_format, bill_tukar_faktur_date, bill_amount_qty_unit_price, bill_type_pajak,
		bill_sales_from, bill_sales_to, bill_issued_timestamp, bill_issued_by_account
	) VALUES (
		v_code, v_ordered_by, v_type_invoice, v_dept, v_inv_date, v_vat_inv_no, v_sj_code2, v_sj_date2, v_po_no, v_po_date,
		v_received_by, v_cus_to, v_cus_name, v_cus_attn, v_cus_address, v_npwp,
		v_ship_to, v_ship_name, v_pajak_to, v_pajak_name, v_pajak_address, v_vat,
		v_delivery_chk, v_delivery_by, v_delivery_warehouse, v_delivery_franco, v_delivery_freight_charge,
		v_payment_chk, v_payment_widthin_days, v_payment_sj_inv_fp_tender,
		v_payment_closing_on, v_payment_for_the_month_week, v_payment_cash_by,
		v_payment_check_by, v_payment_transfer_by, v_payment_giro_issue,
		v_payment_giro_due, v_bank, v_bank_address, v_lastupdated_by_account,
		v_disc, v_total_amount, v_total_amount, v_total_amount, v_signature_by, v_signature_pajak_by,
		v_paper_format, v_tukar_faktur_date, v_amount_before_vat, v_type_pajak,
		v_sales_from, v_sales_to, v_issued_date, v_lastupdated_by_account
	);

	WHILE v_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_billing_item (
			bill_code, cus_code, icat_midx, it_code, it_model_no, it_type, it_desc,
			biit_inv_date, biit_qty, biit_unit_price, biit_remark
		) VALUES (
			v_code, v_cus_to, v_icat_midx[v_i], v_it_code[v_i],v_it_model_no[v_i], v_it_type[v_i], v_it_desc[v_i],
			v_inv_date, v_biit_qty[v_i], v_biit_unit_price[v_i], v_biit_remark[v_i]);
			v_i := v_i + 1;
	END LOOP;

	RETURN v_code;
END;
$$
;

alter function idc_addnewapotikdeptbilling(varchar, integer, integer, date, varchar, date, varchar, date, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, integer, integer[], character varying[], character varying[], character varying[], character varying[], numeric[], integer[], character varying[], integer, varchar, varchar, varchar, numeric, integer, integer, varchar, date, varchar, varchar, varchar, varchar, date, date, varchar, varchar, varchar, numeric, numeric, varchar, varchar, varchar, date, numeric, varchar, date, date) owner to dskim
;

create function idc_addnewapotikorder(v_type character varying, v_received_by character varying, v_confirm_by character varying, v_po_date date, v_po_no character varying, v_vat numeric, v_cus_to character varying, v_cus_to_attn character varying, v_cus_to_address character varying, v_ship_to character varying, v_ship_to_attn character varying, v_ship_to_address character varying, v_bill_to character varying, v_bill_to_attn character varying, v_bill_to_address character varying, v_it_code character varying[], v_it_unit_price numeric[], v_it_qty integer[], v_it_delivery date[], v_price_discount numeric, v_price_chk integer, v_delivery_chk integer, v_delivery_by character varying, v_delivery_freight_charge numeric, v_payment_chk integer, v_payment_widthin_days integer, v_payment_closing_on date, v_payment_cash_by character varying, v_payment_check_by character varying, v_payment_transfer_by character varying, v_payment_giro_by character varying, v_remark character varying, v_lastupdated_by_account character varying) returns character varying
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_code varchar;
BEGIN

	SELECT INTO v_code idc_getCurrentOrdCode(v_type, v_po_date);

	INSERT INTO idc_tb_order(
	ord_code, ord_type, ord_lastupdated_by_account, ord_po_date, ord_po_no, ord_received_by,
	ord_confirm_by, ord_vat, ord_cus_to,ord_cus_to_attn, ord_cus_to_address, ord_ship_to, ord_ship_to_attn, ord_ship_to_address,
	ord_bill_to, ord_bill_to_attn, ord_bill_to_address, ord_price_discount, ord_price_chk,
	ord_delivery_chk, ord_delivery_by, ord_delivery_freight_charge, ord_payment_chk, ord_payment_widthin_days,
	ord_payment_closing_on, ord_payment_cash_by, ord_payment_check_by, ord_payment_transfer_by, ord_payment_giro_by, ord_remark
	) VALUES (
	v_code, v_type, v_lastupdated_by_account, v_po_date, v_po_no, v_received_by,
	v_confirm_by, v_vat, v_cus_to,v_cus_to_attn, v_cus_to_address, v_ship_to, v_ship_to_attn, v_ship_to_address,
	v_bill_to, v_bill_to_attn, v_bill_to_address, v_price_discount, v_price_chk,
	v_delivery_chk, v_delivery_by, v_delivery_freight_charge, v_payment_chk, v_payment_widthin_days,
	v_payment_closing_on, v_payment_cash_by, v_payment_check_by, v_payment_transfer_by, v_payment_giro_by, v_remark
	);

	IF v_type = 'OO' THEN
		WHILE v_it_code[v_i] IS NOT NULL LOOP
			INSERT INTO idc_tb_order_item (ord_code, cus_code, it_code, odit_oo_qty, odit_qty, odit_unit_price, odit_delivery, odit_date)
			VALUES (v_code, v_cus_to, v_it_code[v_i], v_it_qty[v_i], v_it_qty[v_i],v_it_unit_price[v_i], v_it_delivery[v_i], v_po_date);
			v_i := v_i + 1;
	END LOOP;
	ELSE
		WHILE v_it_code[v_i] IS NOT NULL LOOP
			INSERT INTO idc_tb_order_item (ord_code, cus_code, it_code, odit_ok_qty, odit_qty, odit_unit_price, odit_delivery, odit_date)
			VALUES (v_code, v_cus_to, v_it_code[v_i], v_it_qty[v_i], v_it_qty[v_i],v_it_unit_price[v_i], v_it_delivery[v_i], v_po_date);
			v_i := v_i + 1;
		END LOOP;
	END IF;

	RETURN v_code;
END;
$$
;

alter function idc_addnewapotikorder(varchar, varchar, varchar, date, varchar, numeric, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, character varying[], numeric[], integer[], date[], numeric, integer, integer, varchar, numeric, integer, integer, date, varchar, varchar, varchar, varchar, varchar, varchar) owner to dskim
;

create function idc_addnewapotikreturnorder(v_ord_code character varying, v_ord_date date, v_received_by character varying, v_confirm_by character varying, v_po_date date, v_po_no character varying, v_type character varying, v_vat numeric, v_cus_to character varying, v_cus_to_attn character varying, v_cus_to_address character varying, v_ship_to character varying, v_ship_to_attn character varying, v_ship_to_address character varying, v_bill_to character varying, v_bill_to_attn character varying, v_bill_to_address character varying, v_it_code character varying[], v_it_remark character varying[], v_it_unit_price numeric[], v_it_qty integer[], v_price_discount numeric, v_price_chk integer, v_delivery_chk integer, v_delivery_by character varying, v_delivery_freight_charge numeric, v_payment_chk integer, v_payment_widthin_days integer, v_payment_closing_on date, v_payment_cash_by character varying, v_payment_check_by character varying, v_payment_transfer_by character varying, v_payment_giro_by character varying, v_remark character varying, v_lastupdated_by_account character varying, v_revision_time integer) returns character varying
	language plpgsql
as $$
DECLARE
v_i integer := 1;
v_code varchar;
BEGIN

	SELECT INTO v_code idc_getCurrentOrdReturnCode(v_po_date, v_type);

	INSERT INTO idc_tb_return_order(
		reor_code, ord_code, reor_type, reor_ord_reference_date, reor_lastupdated_by_account, reor_po_date,
		reor_po_no, reor_received_by, reor_confirm_by, reor_vat, reor_cus_to,
		reor_cus_to_attn, reor_cus_to_address, reor_ship_to, reor_ship_to_attn, reor_ship_to_address,
		reor_bill_to, reor_bill_to_attn, reor_bill_to_address, reor_price_discount, reor_price_chk,
		reor_delivery_chk, reor_delivery_by, reor_delivery_freight_charge, reor_payment_chk, reor_payment_widthin_days,
		reor_payment_closing_on, reor_payment_cash_by, reor_payment_check_by, reor_payment_transfer_by, reor_payment_giro_by,
		reor_remark, reor_revesion_time
	) VALUES (
		v_code, v_ord_code, v_type, v_ord_date, v_lastupdated_by_account, v_po_date,
		v_po_no, v_received_by, v_confirm_by, v_vat, v_cus_to,
		v_cus_to_attn, v_cus_to_address, v_ship_to, v_ship_to_attn, v_ship_to_address,
		v_bill_to, v_bill_to_attn, v_bill_to_address, v_price_discount, v_price_chk,
		v_delivery_chk, v_delivery_by, v_delivery_freight_charge, v_payment_chk, v_payment_widthin_days,
		v_payment_closing_on, v_payment_cash_by, v_payment_check_by, v_payment_transfer_by, v_payment_giro_by,
		v_remark, v_revision_time + 1
	);


	WHILE v_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_return_order_item (reor_code, cus_code, it_code, roit_qty, roit_unit_price, roit_remark, roit_date)
		VALUES (v_code, v_cus_to, v_it_code[v_i], v_it_qty[v_i], v_it_unit_price[v_i], v_it_remark[v_i], v_po_date);
		v_i := v_i + 1;
	END LOOP;

	RETURN v_code;
END;
$$
;

alter function idc_addnewapotikreturnorder(varchar, date, varchar, varchar, date, varchar, varchar, numeric, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, character varying[], character varying[], numeric[], integer[], numeric, integer, integer, varchar, numeric, integer, integer, date, varchar, varchar, varchar, varchar, varchar, varchar, integer) owner to dskim
;

create function idc_addnewcusgroup(v_code character varying, v_name character varying, v_regtime date, v_remark character varying, v_basic_disc_pct numeric) returns void
	language plpgsql
as $$
BEGIN
	INSERT INTO idc_tb_customer_group(cug_code, cug_name, cug_regtime, cug_remark, cug_basic_disc_pct)
	VALUES (v_code, v_name, v_regtime, v_remark, v_basic_disc_pct);
END;
$$
;

alter function idc_addnewcusgroup(varchar, varchar, date, varchar, numeric) owner to dskim
;

create function idc_addnewcustomer(v_channel character varying, v_code character varying, v_since date, v_company_title character varying, v_full_name character varying, v_customer_group character varying, v_name character varying, v_representative character varying, v_introduced_by character varying, v_type_of_biz character varying, v_tax_code_status integer, v_contact character varying, v_contact_position character varying, v_contact_phone character varying, v_contact_hphone character varying, v_contact_email character varying, v_address character varying, v_phone character varying, v_fax character varying, v_city character varying, v_marketing_staff integer, v_remark character varying, v_fp_email character varying) returns void
	language plpgsql
as $$
BEGIN
	INSERT INTO idc_tb_customer(
		cus_code, cug_code, cus_name, cus_full_name, cus_channel, cus_representative, cus_company_title,
		cus_type_of_biz, cus_tax_code_status, cus_since, cus_introduced_by, cus_contact, cus_contact_position, cus_contact_phone,
		cus_contact_hphone, cus_contact_email, cus_fax, cus_city, cus_address, cus_phone, cus_responsibility_to, cus_remark, cus_fp_email
	) VALUES (
		v_code, v_customer_group, v_name, v_full_name, v_channel, v_representative, v_company_title,
		v_type_of_biz, v_tax_code_status, v_since, v_introduced_by, v_contact, v_contact_position, v_contact_phone,
		v_contact_hphone, v_contact_email, v_fax, v_city, v_address, v_phone, v_marketing_staff, v_remark, v_fp_email
	);
END;
$$
;

alter function idc_addnewcustomer(varchar, varchar, date, varchar, varchar, varchar, varchar, varchar, varchar, varchar, integer, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, integer, varchar, varchar) owner to dskim
;

create function idc_addnewdeliverystock(v_cus_code character varying, v_out_type integer, v_book_idx integer, v_dept character varying, v_out_code character varying, v_out_doc_ref character varying, v_out_doc_type integer, v_issued_date date, v_received_by character varying, v_cfm_date date, v_cfm_by_account character varying, v_remark character varying, v_it_code character varying[], v_it_ed character varying[], v_it_booked_qty numeric[], v_ed_it_code character varying[], v_ed_it_location integer[], v_ed_it_date character varying[], v_ed_it_qty numeric[]) returns integer
	language plpgsql
as $$
DECLARE
    recI RECORD;
    recII RECORD;
    v_cur_out_idx integer := 0;
    v_cur_bor_idx integer := 0;
    v_cur_bed_idx integer := 0;
    v_bed_qty numeric;
    v_i integer := 1;
    v_j integer := 1;
    v_date date;
    v_ed boolean;
    v_temp_qty numeric := 0;
    v_qty numeric := 0;
    v_log_code varchar;
    v_type_activity varchar;
    v_do_no varchar;
    v_out_type_inverse integer;
    v_stock_use_qty numeric;
    v_vat_qty numeric;
    v_non_qty numeric;
    v_different_qty numeric;
    v_out_doc_type_adj varchar;
    v_check boolean := false;
BEGIN

    SELECT INTO v_check book_is_delivered FROM idc_tb_booking WHERE book_idx = v_book_idx;
    IF v_check THEN
        RAISE EXCEPTION 'DO ALREADY CONFIRMED, PLEASE RE-CHECK %', v_out_code;
    END IF;

    -- Update related table
    IF v_out_doc_type = 1 THEN v_out_doc_type_adj := 'DO Billing';
    ELSIF v_out_doc_type = 2 THEN v_out_doc_type_adj := 'DO Order';
    ELSIF v_out_doc_type = 3 THEN v_out_doc_type_adj := 'DT';
    ELSIF v_out_doc_type = 4 THEN v_out_doc_type_adj := 'DF';
    ELSIF v_out_doc_type = 5 THEN v_out_doc_type_adj := 'DR';
    ELSIF v_out_doc_type = 6 THEN v_out_doc_type_adj := 'DM';
    END IF;

    SELECT INTO v_type_activity idc_updateRelatedTables(true, v_book_idx, v_out_doc_ref, v_out_doc_type_adj, v_cfm_by_account, v_cfm_date);

    IF v_out_doc_type IN (1,2) THEN v_do_no := 'D' || substr(v_out_doc_ref,2);
    ELSE v_do_no := v_out_doc_ref; END IF;

    IF v_out_type = 1 THEN v_out_type_inverse := 2;
    ELSIF v_out_type = 2 THEN v_out_type_inverse := 1; END IF;

    -- Insert into idc_tb_outgoing_v2
    INSERT INTO idc_tb_outgoing_v2 (
        cus_code, out_type, out_dept, out_book_idx, out_code, out_doc_ref, out_doc_type, out_issued_date,
        out_received_by, out_cfm_date, out_cfm_by_account, out_cfm_timestamp, out_remark
    ) VALUES (
        v_cus_code, v_out_type, v_dept, v_book_idx, v_out_code, v_out_doc_ref, v_out_doc_type_adj, v_issued_date,
        v_received_by, v_cfm_date, v_cfm_by_account, CURRENT_TIMESTAMP, v_remark
    );
    v_cur_out_idx := currval('idc_tb_outgoing_v2_out_idx_seq');

    -- Insert idc_tb_outgoing_item_v2
    WHILE v_it_code[v_i] IS NOT NULL LOOP
        v_ed := v_it_ed[v_i];
        INSERT INTO idc_tb_outgoing_item_v2 (out_idx, it_code, otit_ed, otit_type, otit_qty, otit_vat_qty, otit_non_qty)
        VALUES (v_cur_out_idx, v_it_code[v_i], v_ed, v_out_type, v_it_booked_qty[v_i], 0, 0);
        v_i := v_i + 1;
    END LOOP;

    -- Insert idc_tb_outgoing_stock_v2
    FOR recI IN SELECT out_code, cus_code, it_code, otit_type, otit_qty
    FROM idc_tb_outgoing_v2 AS a JOIN idc_tb_outgoing_item_v2 using(out_idx)
    WHERE out_idx = v_cur_out_idx
    ORDER BY otit_idx
    LOOP
        INSERT INTO idc_tb_outgoing_stock_v2 (out_idx, it_code, otst_wh_location, otst_type, otst_qty, otst_confirm_date, otst_document_date)
        VALUES (v_cur_out_idx, recI.it_code, 1, v_out_type, recI.otit_qty, CURRENT_DATE, v_issued_date);
        SELECT INTO v_log_code idc_insertStockLog(
            recI.it_code, 1, v_out_type, v_out_doc_type_adj, null, v_do_no, v_issued_date,
            v_cfm_by_account, false, recI.otit_qty, false
        );
    END LOOP;

    -- Insert idc_tb_outgoing_stock_ed
    WHILE v_ed_it_code[v_j] IS NOT NULL AND v_ed_it_code[v_j] != '' LOOP
        v_date := v_ed_it_date[v_j];
        INSERT INTO idc_tb_outgoing_stock_ed (out_idx, it_code, oted_wh_location, oted_expired_date, oted_qty)
        VALUES (v_cur_out_idx, v_ed_it_code[v_j], v_ed_it_location[v_j], v_date, v_ed_it_qty[v_j]);
        v_j := v_j + 1;
    END LOOP;

    RETURN v_cur_out_idx;
END;
$$
;

alter function idc_addnewdeliverystock(varchar, integer, integer, varchar, varchar, varchar, integer, date, varchar, date, varchar, varchar, character varying[], character varying[], numeric[], character varying[], integer[], character varying[], numeric[]) owner to dskim
;

create function idc_addnewdeliverystockrevised(v_out_idx integer, v_book_idx integer, v_out_type integer, v_out_doc_type integer, v_out_doc_ref character varying, v_issued_date date, v_account_name character varying, v_account_password character varying, v_it_code character varying[], v_it_ed character[], v_it_qty numeric[], v_ed_it_code character varying[], v_ed_it_location integer[], v_ed_it_date character varying[], v_ed_it_qty numeric[]) returns integer
	language plpgsql
as $$
DECLARE
    v_account_idx integer;
    recI RECORD;
    recII RECORD;
    v_i integer := 1;
    v_j integer := 1;
    v_k integer := 1;
    v_l integer := 1;
    v_out_doc_type_adj varchar;
    v_log_code varchar;
    v_it_rev boolean[];
    v_ed_it_rev boolean[];
    v_ed boolean;
    v_check boolean := false;
BEGIN


    SELECT INTO v_check book_is_delivered FROM idc_tb_booking WHERE book_idx = v_book_idx;
    IF v_check THEN
        RAISE EXCEPTION 'DO ALREADY CONFIRMED, PLEASE RE-CHECK %', v_out_code;
    END IF;


    -- Check account validity
    SELECT INTO v_account_idx ma_idx FROM tb_mbracc WHERE ma_account = v_account_name AND ma_password = v_account_password;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'FAIL_TO_AUTH';
    END IF;

    -- Update related table
    IF v_out_doc_type = 1 THEN v_out_doc_type_adj := 'DO Billing';
    ELSIF v_out_doc_type = 2 THEN v_out_doc_type_adj := 'DO Order';
    ELSIF v_out_doc_type = 3 THEN v_out_doc_type_adj := 'DT';
    ELSIF v_out_doc_type = 4 THEN v_out_doc_type_adj := 'DF';
    ELSIF v_out_doc_type = 5 THEN v_out_doc_type_adj := 'DR';
    ELSIF v_out_doc_type = 6 THEN v_out_doc_type_adj := 'DM';
    END IF;

    -- Lock tb_booking as delivered
    UPDATE idc_tb_booking SET
        book_is_revised     = false,
        book_is_delivered   = true
    WHERE
        book_idx = v_book_idx;

    -- Update idc_tb_booking_revised
    UPDATE idc_tb_booking_revised SET
        boit_cfm_by_account = v_account_name,
        boit_cfm_timestamp = CURRENT_TIMESTAMP
    WHERE book_idx = v_book_idx;

    -- Lock idc_tb_outgoing_v2 as delivered
    UPDATE idc_tb_outgoing_v2 SET out_is_revised = false WHERE out_idx = v_out_idx;

    --
    -- Update idc_tb_outgoing_item_v2
    --
    -- Jika item sudah ada sebelumnya
    FOR recI IN SELECT * FROM idc_tb_outgoing_item_v2 WHERE out_idx = v_out_idx ORDER BY it_code
    LOOP
        v_i := 1;
        WHILE v_it_code[v_i] IS NOT NULL AND v_it_code[v_i] != '' LOOP
            IF TRIM(recI.it_code) = TRIM(v_it_code[v_i]) THEN

                -- Jika ada perubahan qty nya ...
                IF recI.otit_qty + v_it_qty[v_i] != 0 THEN
                    UPDATE idc_tb_outgoing_item_v2 SET otit_qty = recI.otit_qty + v_it_qty[v_i] WHERE otit_idx = recI.otit_idx;
                ELSE
                    DELETE FROM idc_tb_outgoing_item_v2 WHERE otit_idx = recI.otit_idx;
                END IF;

                -- Record change log
                SELECT INTO v_log_code idc_insertStockLog(recI.it_code, 1, v_out_type, v_out_doc_type_adj, null, v_out_doc_ref, v_issued_date,v_account_name, false, v_it_qty[v_i], true);

                v_it_rev[v_i] :=  true;
            END IF;
            v_i := v_i + 1;
        END LOOP;
    END LOOP;

    -- Jika item belum ada
    WHILE v_it_code[v_j] IS NOT NULL AND v_it_code[v_j] != '' LOOP
        IF v_it_rev[v_j] IS null THEN

            IF v_it_ed[v_j] = 't' THEN v_ed = true;
            ELSIF v_it_ed[v_j] = 'f' THEN v_ed = false;
            END IF;

            INSERT INTO idc_tb_outgoing_item_v2 (out_idx, it_code, otit_ed, otit_type, otit_qty, otit_vat_qty, otit_non_qty)
            VALUES (v_out_idx, v_it_code[v_j], v_ed, v_out_type, v_it_qty[v_j], 0, 0);

            -- Record change log
            SELECT INTO v_log_code idc_insertStockLog(v_it_code[v_j], 1, v_out_type, v_out_doc_type_adj, null, v_out_doc_ref, v_issued_date, v_account_name, false, v_it_qty[v_j], true);
        END IF;
        v_j := v_j + 1;
    END LOOP;

    -- Update idc_tb_outgoing_stock_v2
    DELETE FROM idc_tb_outgoing_stock_v2 WHERE out_idx = v_out_idx;
    FOR recI IN SELECT * FROM idc_tb_outgoing_item_v2 WHERE out_idx = v_out_idx ORDER BY it_code
    LOOP
        INSERT INTO idc_tb_outgoing_stock_v2 (out_idx, it_code, otst_wh_location, otst_type, otst_qty, otst_confirm_date, otst_document_date)
        VALUES (v_out_idx, recI.it_code, 1, v_out_type, recI.otit_qty, CURRENT_DATE, v_issued_date);
    END LOOP;

    --
    -- Update idc_tb_outgoing_stock_ed
    --
    -- Jika item sudah ada sebelumnya
    FOR recII IN SELECT * FROM idc_tb_outgoing_stock_ed WHERE out_idx = v_out_idx ORDER BY it_code, oted_expired_date
    LOOP
        v_k := 1;
        WHILE v_ed_it_code[v_k] IS NOT NULL AND v_ed_it_code[v_k] != '' LOOP
            IF recII.it_code = v_ed_it_code[v_k] AND recII.oted_expired_date = v_ed_it_date[v_k]::date THEN
                -- Jika perubahannya ...
                DELETE FROM idc_tb_outgoing_stock_ed WHERE oted_idx = recII.oted_idx;
                IF recII.oted_qty + v_ed_it_qty[v_k] != 0 THEN
                    INSERT INTO idc_tb_outgoing_stock_ed (out_idx, it_code, oted_wh_location, oted_expired_date, oted_qty, oted_timestamp)
                    VALUES (v_out_idx, v_ed_it_code[v_k], v_ed_it_location[v_k], v_ed_it_date[v_k]::date, recII.oted_qty + v_ed_it_qty[v_k], CURRENT_TIMESTAMP);
                END IF;
                v_ed_it_rev[v_k] :=  true;
            END IF;
            v_k := v_k + 1;
        END LOOP;
    END LOOP;

    -- Jika item belum ada
    WHILE v_ed_it_code[v_l] IS NOT NULL AND v_ed_it_code[v_l] != '' LOOP
        IF v_ed_it_rev[v_l] IS null THEN
            INSERT INTO idc_tb_outgoing_stock_ed (out_idx, it_code, oted_wh_location, oted_expired_date, oted_qty, oted_timestamp)
            VALUES (v_out_idx, v_ed_it_code[v_l], v_ed_it_location[v_l], v_ed_it_date[v_l]::date, v_ed_it_qty[v_l], CURRENT_TIMESTAMP);
        END IF;
        v_l := v_l + 1;
    END LOOP;


    RETURN v_out_idx;
END;
$$
;

alter function idc_addnewdeliverystockrevised(integer, integer, integer, integer, varchar, date, varchar, varchar, character varying[], character[], numeric[], character varying[], integer[], character varying[], numeric[]) owner to dskim
;

create function idc_addnewdeposit(v_dept character varying, v_cus_code character varying, v_cus_name character varying, v_payment_date date, v_payment_paid numeric, v_payment_method character varying, v_payment_bank character varying, v_payment_remark character varying, v_inputed_by_account character varying) returns void
	language plpgsql
as $$
BEGIN
	INSERT INTO idc_tb_deposit(
		cus_code, dep_cus_name, dep_dept, dep_amount, dep_issued_date, dep_method,
		dep_bank, dep_remark, dep_type, dep_updated_by_account, dep_updated_timestamp
	) VALUES (
		v_cus_code, v_cus_name, v_dept, v_payment_paid, v_payment_date, v_payment_method,
		v_payment_bank, v_payment_remark, 'deposit', v_inputed_by_account, CURRENT_TIMESTAMP
	);
END;
$$
;

alter function idc_addnewdeposit(varchar, varchar, varchar, date, numeric, varchar, varchar, varchar, varchar) owner to dskim
;

create function idc_addnewforwarder(v_code character varying, v_full_name character varying, v_representative character varying, v_contact_name character varying, v_contact_phone character varying, v_contact_email character varying, v_address character varying, v_phone character varying, v_fax character varying, v_mobile_phone character varying, v_remark character varying) returns void
	language plpgsql
as $$
BEGIN
	INSERT INTO idc_tb_forwarder(
		fw_code, fw_full_name, fw_representative, fw_contact_name, fw_contact_phone, fw_contact_email,
		fw_address, fw_phone, fw_fax, fw_mobile_phone, fw_remark
	) VALUES (
		v_code, v_full_name, v_representative, v_contact_name, v_contact_phone, v_contact_email,
		v_address, v_phone, v_fax, v_mobile_phone, v_remark
	);
END;
$$
;

alter function idc_addnewforwarder(varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar) owner to dskim
;

create function idc_addnewitem(v_code character varying, v_midx integer, v_model_no character varying, v_type character varying, v_desc character varying, v_user_price numeric, v_date_from date, v_remark character varying) returns void
	language plpgsql
as $$
BEGIN

	INSERT INTO idc_tb_item(it_code, icat_midx, it_model_no, it_type, it_desc, it_remark)
	VALUES (v_code, v_midx, v_model_no, v_type, v_desc, v_remark);

	INSERT INTO idc_tb_item_price (it_code, ip_date_from, ip_user_price)
	VALUES (v_code, v_date_from, v_user_price);
END;
$$
;

alter function idc_addnewitem(varchar, integer, varchar, varchar, varchar, numeric, date, varchar) owner to dskim
;

create function idc_addnewitemcat(v_pidx integer, v_level integer, v_code character varying, v_name character varying) returns void
	language plpgsql
as $$
BEGIN
	INSERT INTO idc_tb_item_cat (icat_pidx, icat_depth, icat_code ,icat_name)
	VALUES(v_pidx, v_level, v_code ,v_name);
END;
$$
;

alter function idc_addnewitemcat(integer, integer, varchar, varchar) owner to dskim
;

create function idc_addnewpayment(v_code character varying, v_cus_to character varying, v_payment_date date, v_payment_paid numeric, v_payment_paid_delivery numeric, v_remain_amount numeric, v_payment_remark character varying, v_inputed_by character varying, v_method character varying, v_bank character varying, v_dept character varying, v_deduction_type integer[], v_deduction_desc character varying[], v_deduction_amount numeric[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_cur_pay_idx integer;
BEGIN

	INSERT INTO idc_tb_payment(bill_code, cus_code, pay_dept, pay_date, pay_paid, pay_paid_charge, pay_inputed_by, pay_method, pay_bank, pay_remark)
	VALUES (v_code, v_cus_to, v_dept, v_payment_date, v_payment_paid, v_payment_paid_delivery, v_inputed_by, v_method, v_bank, v_payment_remark);

	v_cur_pay_idx := currval('idc_tb_payment_pay_idx_seq');
	WHILE v_deduction_desc[v_i] != '' AND v_deduction_amount[v_i] != 0 LOOP
		INSERT INTO idc_tb_payment_deduction ( pay_idx, bill_code, pade_type, pade_description, pade_amount )
		VALUES ( v_cur_pay_idx, v_code, v_deduction_type[v_i], v_deduction_desc[v_i], v_deduction_amount[v_i]);
		v_i := v_i + 1;
	END LOOP;

END;
$$
;

alter function idc_addnewpayment(varchar, varchar, date, numeric, numeric, numeric, varchar, varchar, varchar, varchar, varchar, integer[], character varying[], numeric[]) owner to dskim
;

create function idc_addnewpaymentbydeposit(v_dept character varying, v_cus_to character varying, v_bill_code character varying, v_date date, v_amount numeric, v_inputed_by character varying, v_remark character varying, v_deposit_type character varying, v_method character varying, v_bank character varying) returns void
	language plpgsql
as $$
DECLARE
	v_idx integer;
	v_cus_name varchar;
	v_type varchar;
BEGIN

	INSERT INTO idc_tb_payment(bill_code, cus_code, pay_dept, pay_date, pay_paid, pay_inputed_by, pay_remark, pay_method, pay_bank, pay_note)
	VALUES (v_bill_code, v_cus_to, v_dept, v_date, v_amount, v_inputed_by, v_remark, v_method, v_bank, v_deposit_type);

	IF v_deposit_type = 'DEPOSIT-A' THEN
		v_type = 'paymentA';
	ELSE
		v_type = 'paymentB';
	END IF;

	SELECT INTO v_idx MAX(pay_idx) FROM idc_tb_payment;
	SELECT INTO v_cus_name cus_name FROM idc_tb_customer WHERE cus_code = v_cus_to;

	INSERT INTO idc_tb_deposit (cus_code, dep_cus_name, pay_idx, dep_dept, dep_amount, dep_issued_date, dep_type, dep_method, dep_bank)
	VALUES(v_cus_to, v_cus_name, v_idx, v_dept, -v_amount, v_date, v_type, v_method, v_bank);
END;
$$
;

alter function idc_addnewpaymentbydeposit(varchar, varchar, varchar, date, numeric, varchar, varchar, varchar, varchar, varchar) owner to dskim
;

create function idc_addnewpaymentbyreturn(v_dept character varying, v_cus_to character varying, v_bill_code character varying, v_date date, v_amount numeric, v_inputed_by character varying, v_remark character varying) returns void
	language plpgsql
as $$
DECLARE
	v_idx integer;
	v_cus_name varchar;
BEGIN
	INSERT INTO idc_tb_payment(bill_code, cus_code, pay_date, pay_paid, pay_inputed_by, pay_remark, pay_method, pay_note)
	VALUES (v_bill_code, v_cus_to, v_date, v_amount, v_inputed_by, v_remark, 'deposit', 'DEPOSIT');

	SELECT INTO v_idx MAX(pay_idx) FROM idc_tb_payment;
	SELECT INTO v_cus_name cus_name FROM idc_tb_customer WHERE cus_code = v_cus_to;

	INSERT INTO idc_tb_deposit (cus_code, dep_cus_name, pay_idx, dep_dept, dep_amount, dep_issued_date)
	VALUES(v_cus_to, v_cus_name, v_idx, v_dept, -v_amount, v_date);
END;
$$
;

alter function idc_addnewpaymentbyreturn(varchar, varchar, varchar, date, numeric, varchar, varchar) owner to dskim
;

create function idc_addnewpl(v_po_code character varying, v_ordered_by integer, v_sp_code character varying, v_sp_name character varying, v_inv_no character varying, v_inv_date date, v_etd_date date, v_eta_date date, v_layout_type integer, v_received_by character varying, v_lastupdated_by_account character varying, v_shipment_mode character varying, v_mode_desc character varying, v_pl_type integer, v_total_qty integer, v_remark character varying, v_icat_midx integer[], v_it_code character varying[], v_plit_item character varying[], v_plit_desc character varying[], v_plit_unit_price numeric[], v_plit_qty integer[], v_plit_remark character varying[], v_plit_att character varying[]) returns integer
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_cur_pl_idx integer := 0;
BEGIN

	/* Insert into idc_tb_pl */
	INSERT INTO idc_tb_pl(
		po_code, pl_sp_code, pl_sp_name, pl_inv_no, pl_inv_date, pl_received_by, pl_layout_type,
		pl_lastupdated_by_account, pl_lastupdated_timestamp,  pl_total_qty,
		pl_etd_date, pl_eta_date, pl_shipment_mode, pl_type, pl_shipment_desc, pl_remark, pl_ordered_by
	) VALUES (
		v_po_code, v_sp_code, v_sp_name, v_inv_no, v_inv_date, v_received_by, v_layout_type,
		v_lastupdated_by_account, CURRENT_TIMESTAMP, v_total_qty,
		v_etd_date, v_eta_date, v_shipment_mode, v_pl_type, v_mode_desc, v_remark, v_ordered_by
	);
	v_cur_pl_idx := currval('idc_tb_pl_pl_idx_seq');

	WHILE v_it_code[v_i] IS NOT NULL LOOP
		/* Insert into idc_tb_pl_item */
		INSERT INTO idc_tb_pl_item (
			pl_idx, icat_midx, it_code, plit_item, plit_desc,
			plit_attribute, plit_unit_price, plit_qty, plit_remark
		) VALUES (
			v_cur_pl_idx, v_icat_midx[v_i], v_it_code[v_i], v_plit_item[v_i], v_plit_desc[v_i],
			v_plit_att[v_i], v_plit_unit_price[v_i], v_plit_qty[v_i], v_plit_remark[v_i]
		);
		v_i := v_i + 1;
	END LOOP;
	RETURN v_cur_pl_idx;
END;
$$
;

alter function idc_addnewpl(varchar, integer, varchar, varchar, varchar, date, date, date, integer, varchar, varchar, varchar, varchar, integer, integer, varchar, integer[], character varying[], character varying[], character varying[], numeric[], integer[], character varying[], character varying[]) owner to dskim
;

create function idc_addnewplclaim(v_ordered_by integer, v_sp_code character varying, v_sp_name character varying, v_inv_no character varying, v_inv_date date, v_etd_date date, v_eta_date date, v_received_by character varying, v_lastupdated_by_account character varying, v_shipment_mode character varying, v_mode_desc character varying, v_remark character varying, v_icat_midx integer[], v_it_code character varying[], v_it_unit_price numeric[], v_it_qty numeric[], v_it_remark character varying[], v_it_att character varying[]) returns integer
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_cur_cl_idx integer := 0;
BEGIN

	INSERT INTO idc_tb_claim(
		cl_sp_code, cl_sp_name, cl_inv_no, cl_inv_date, cl_received_by,
		cl_lastupdated_by_account, cl_lastupdated_timestamp,
		cl_etd_date, cl_eta_date, cl_shipment_mode, cl_type, cl_shipment_desc, cl_remark
	) VALUES (
		v_sp_code, v_sp_name, v_inv_no, v_inv_date, v_received_by,
		v_lastupdated_by_account, CURRENT_TIMESTAMP,
		v_etd_date, v_eta_date, v_shipment_mode, 2, v_mode_desc, v_remark
	);
	v_cur_cl_idx := currval('idc_tb_claim_cl_idx_seq');

	WHILE v_it_code[v_i] IS NOT NULL LOOP
	INSERT INTO idc_tb_claim_item (cl_idx, icat_midx, it_code, clit_attribute, clit_unit_price, clit_qty, clit_remark)
	VALUES (v_cur_cl_idx, v_icat_midx[v_i], v_it_code[v_i], v_it_att[v_i], v_it_unit_price[v_i], v_it_qty[v_i], v_it_remark[v_i]);
	v_i := v_i + 1;
	END LOOP;

	RETURN v_cur_cl_idx;
END;
$$
;

alter function idc_addnewplclaim(integer, varchar, varchar, varchar, date, date, date, varchar, varchar, varchar, varchar, varchar, integer[], character varying[], numeric[], numeric[], character varying[], character varying[]) owner to dskim
;

create function idc_addnewpo(v_source character varying, v_ordered_by integer, v_po_date date, v_sp_code character varying, v_sp_name character varying, v_layout_type integer, v_currency_type integer, v_received_by character varying, v_lastupdated_by_account character varying, v_shipment_mode character varying, v_mode_desc character varying, v_po_type integer, v_po_type_invoice integer, v_total_qty integer, v_total_amount numeric, v_print_remark character varying, v_remark character varying, v_prepared_by character varying, v_confirmed_by character varying, v_icat_midx integer[], v_it_code character varying[], v_poit_item character varying[], v_poit_desc character varying[], v_poit_unit_price numeric[], v_poit_qty integer[], v_poit_remark character varying[], v_poit_att character varying[]) returns character varying
	language plpgsql
as $$
DECLARE
    v_i integer := 1;
    v_code varchar;
BEGIN

    SELECT INTO v_code idc_getCurrentPOCode(v_source, v_ordered_by, v_po_type, v_po_date, v_currency_type);

    INSERT INTO idc_tb_po(
        po_code, po_date, po_layout_type, po_currency_type, po_received_by, po_lastupdated_by_account,
        po_shipment_mode, po_shipment_desc, po_type, po_type_invoice, po_total_amount, po_total_qty,
        po_doc_remark, po_remark, po_prepared_by, po_confirmed_by,
        po_sp_code, po_sp_name, po_ordered_by
    ) VALUES (
        v_code, v_po_date, v_layout_type, v_currency_type, v_received_by, v_lastupdated_by_account,
        v_shipment_mode, v_mode_desc, v_po_type, v_po_type_invoice, v_total_amount, v_total_qty,
        v_print_remark, v_remark, v_prepared_by, v_confirmed_by,
        v_sp_code, v_sp_name, v_ordered_by
    );

    WHILE v_it_code[v_i] IS NOT NULL LOOP
        INSERT INTO idc_tb_po_item (
            po_code, icat_midx, it_code, poit_item, poit_desc,
            poit_qty, poit_unit_price, poit_remark, poit_attribute
        ) VALUES (
            v_code, v_icat_midx[v_i], v_it_code[v_i], v_poit_item[v_i], v_poit_desc[v_i],
            v_poit_qty[v_i], v_poit_unit_price[v_i], v_poit_remark[v_i], v_poit_att[v_i]
        );
        v_i := v_i + 1;
    END LOOP;

    RETURN v_code;
END;
$$
;

alter function idc_addnewpo(varchar, integer, date, varchar, varchar, integer, integer, varchar, varchar, varchar, varchar, integer, integer, integer, numeric, varchar, varchar, varchar, varchar, integer[], character varying[], character varying[], character varying[], numeric[], integer[], character varying[], character varying[]) owner to dskim
;

create function idc_addnewservicepayment(v_code character varying, v_cus_to character varying, v_payment_date date, v_payment_paid numeric, v_payment_remark character varying, v_inputed_by character varying, v_method character varying, v_bank character varying) returns void
	language plpgsql
as $$
BEGIN
	INSERT INTO idc_tb_service_payment(
		sv_code, cus_code, svpay_date, svpay_paid, svpay_inputed_by, svpay_method, svpay_bank, svpay_remark
	) VALUES (
		v_code, v_cus_to, v_payment_date, v_payment_paid, v_inputed_by, v_method, v_bank, v_payment_remark
	);
END;
$$
;

alter function idc_addnewservicepayment(varchar, varchar, date, numeric, varchar, varchar, varchar, varchar) owner to dskim
;

create function idc_addnewsupplier(v_code character varying, v_name character varying, v_full_name character varying, v_representative character varying, v_contact_name character varying, v_contact_phone character varying, v_contact_email character varying, v_attn character varying, v_cc character varying, v_address character varying, v_phone character varying, v_fax character varying, v_remark character varying, v_bank_name character varying, v_bank_swift character varying, v_bank_address character varying, v_bank_acc_no character varying, v_bank_currency character varying, v_bank_acc_name character varying) returns void
	language plpgsql
as $$
BEGIN
	INSERT INTO idc_tb_supplier(
		sp_code, sp_name, sp_full_name, sp_representative, sp_contact_name, sp_contact_phone, sp_contact_email,
		sp_address, sp_phone, sp_fax, sp_remark, sp_contact_attn, sp_contact_cc,
		sp_bank_name, sp_bank_swift_code, sp_bank_address, sp_bank_account_no, sp_bank_currency, sp_bank_account_name
	) VALUES (
		v_code, v_name, v_full_name, v_representative, v_contact_name, v_contact_phone, v_contact_email,
		v_address, v_phone, v_fax, v_remark, v_attn, v_cc,
		v_bank_name, v_bank_swift, v_bank_address, v_bank_acc_no, v_bank_currency, v_bank_acc_name
	);
END;
$$
;

alter function idc_addnewsupplier(varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar) owner to dskim
;

create function idc_addreturndata(v_code character varying, v_it_code character varying[], v_it_qty integer[], v_return_date date[], v_return_remark character varying[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
BEGIN
	WHILE v_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_return_log(rl_date, cus_code, it_code, rl_qty, rl_remark)
		VALUES(v_return_date[v_i], v_code, v_it_code[v_i], v_it_qty[v_i], v_return_remark[v_i]);
		v_i := v_i + 1;
	END LOOP;
END;
$$
;

alter function idc_addreturndata(varchar, character varying[], integer[], date[], character varying[]) owner to dskim
;

create function idc_addsalesdata(v_code character varying, v_dept character varying, v_cus_to_responsible_by integer, v_it_code character varying[], v_it_sales_date date[], v_it_qty integer[], v_payment_price numeric[], v_it_faktur_no character varying[], v_it_lop_no character varying[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_row_count integer := 0;
	v_is_apply_all boolean := FALSE;
	v_debit_price numeric;
	v_unit_price numeric;
	v_basic_disc_pct numeric := 0;
	v_add_disc_pct numeric := 0;
	v_cug_code varchar;
	v_sales_remark varchar := '';
	v_policy_idx integer; -- for remark
	v_it_faktur_no_adj varchar;
	v_it_lop_no_adj varchar;
	rec RECORD;
BEGIN


	IF v_dept = 'A' THEN
		--Get Customer group
		SELECT INTO v_cug_code cug_code FROM idc_tb_customer WHERE cus_code = v_code;
		IF NOT FOUND OR v_cug_code IS NULL THEN
			RAISE EXCEPTION 'CANNOT FIND CUSTOMER GROUP';
		END IF;

		-- get basic_disc_price
		SELECT INTO v_basic_disc_pct cug_basic_disc_pct FROM idc_tb_customer_group WHERE cug_code = (SELECT cug_code FROM idc_tb_customer WHERE cus_code = v_code);
	ELSE
		v_basic_disc_pct := 30;
	END IF;

	-- For all the sales item (PRICE)
	WHILE v_it_code[v_i] IS NOT NULL LOOP
		IF v_dept = 'A' THEN
			--check apotik has price policy. Basically User cannot input duplicate period
			SELECT INTO v_policy_idx, v_add_disc_pct ap_idx, ap_disc_pct FROM idc_tb_apotik_policy WHERE cus_code = v_code
			   AND ap_is_valid = TRUE
			   AND ap_is_apply_all = TRUE
			   AND ap_date_from <= v_it_sales_date[v_i]
			   AND ap_date_to + 1 > v_it_sales_date[v_i];

			IF FOUND THEN
				v_sales_remark := 'AP#' || v_policy_idx;
			ELSE
			SELECT INTO v_policy_idx, v_add_disc_pct
			ap.ap_idx, ap.ap_disc_pct
			FROM idc_tb_apotik_policy AS ap
			JOIN idc_tb_apotik_price AS ait ON (ap.ap_idx = ait.ap_idx)
			WHERE ap.cus_code = v_code
			   AND ap.ap_is_valid = TRUE
			   AND ait.it_code = v_it_code[v_i]
			   AND ap.ap_date_from <= v_it_sales_date[v_i]
			   AND ap.ap_date_to + 1 > v_it_sales_date[v_i];

				IF FOUND THEN
				v_sales_remark := 'AP#' || v_policy_idx;

				--check group price policy or item price
				ELSE
				SELECT INTO v_policy_idx, v_add_disc_pct
				ag_idx, ag_disc_pct
				FROM idc_tb_group_policy
				WHERE cug_code = v_cug_code
				   AND ag_is_valid = TRUE
				   AND ag_is_apply_all = TRUE
				   AND ag_date_from <= v_it_sales_date[v_i]
				   AND ag_date_to + 1 > v_it_sales_date[v_i];

					IF FOUND THEN
					v_sales_remark := 'GP#' || v_policy_idx;
					ELSE

					SELECT INTO v_policy_idx, v_add_disc_pct
					ag.ag_idx, ag.ag_disc_pct
					FROM idc_tb_group_policy AS ag
					   JOIN idc_tb_group_price AS git ON (ag.ag_idx = git.ag_idx)
					WHERE ag.cug_code = v_cug_code
					   AND ag.ag_is_valid = TRUE
					   AND git.it_code = v_it_code[v_i]
					   AND ag.ag_date_from <= v_it_sales_date[v_i]
					   AND ag.ag_date_to + 1 > v_it_sales_date[v_i];

						IF FOUND THEN v_sales_remark := 'GP#' || v_policy_idx;

						--Check Item unit price
						ELSE
							v_add_disc_pct := 0;
							v_sales_remark := null;
						END IF;
					END IF;
				END IF;
			END IF;
		ELSE
			v_sales_remark := null;
		END IF;

		-- get user price on sales date
		SELECT INTO v_unit_price ip_user_price FROM idc_tb_item_price
		WHERE it_code = v_it_code[v_i] AND ip_date_from <= v_it_sales_date[v_i] AND ip_date_to + 1 > v_it_sales_date[v_i];

		IF NOT FOUND THEN
			SELECT INTO v_unit_price ip_user_price FROM idc_tb_item_price WHERE ip_idx = (SELECT max(ip_idx) FROM idc_tb_item_price WHERE it_code = v_it_code[v_i]);
		END IF;

		v_sales_remark := v_sales_remark;

		-- now calculate the user price
		v_debit_price := round((v_unit_price - (v_unit_price * (v_basic_disc_pct + v_add_disc_pct)/100))/1.1);

		-- Input sales log
		IF char_length(v_it_faktur_no[v_i]) = 0	THEN v_it_faktur_no_adj := null;	ELSE v_it_faktur_no_adj:=v_it_faktur_no[v_i]; END IF;
		IF char_length(v_it_lop_no[v_i]) = 0	THEN v_it_lop_no_adj := null; 		ELSE v_it_lop_no_adj:=v_it_lop_no[v_i]; END IF;
		INSERT INTO idc_tb_sales_log(sl_date, it_code, cus_code, sl_dept, sl_cus_to_responsible_by, sl_basic_disc, sl_add_disc, sl_user_price, sl_debit_price, sl_payment_price, sl_qty, sl_remark, sl_faktur_no, sl_lop_no)
		VALUES(v_it_sales_date[v_i], v_it_code[v_i], v_code, v_dept, v_cus_to_responsible_by, v_basic_disc_pct, v_add_disc_pct, v_unit_price, v_debit_price, v_payment_price[v_i], v_it_qty[v_i], v_sales_remark, v_it_faktur_no_adj, v_it_lop_no_adj);

		v_i := v_i + 1;
	END LOOP;
END;
$$
;

alter function idc_addsalesdata(varchar, varchar, integer, character varying[], date[], integer[], numeric[], character varying[], character varying[]) owner to dskim
;

create function idc_arrivedqty(v_type_pl integer, v_code character varying, v_it_code character varying) returns numeric
	language plpgsql
as $$
DECLARE
	v_qty numeric := 0;
	r_qty record; r_qty2 record;
BEGIN

	IF v_type_pl = 1 THEN
		FOR r_qty IN SELECT init_qty FROM idc_tb_in_pl_item WHERE pl_idx = v_code::integer AND it_code = v_it_code LOOP
			v_qty := v_qty + r_qty.init_qty;
		END LOOP;
		FOR r_qty2 IN SELECT init_qty FROM idc_tb_in_pl_item_v2 WHERE pl_idx = v_code::integer AND it_code = v_it_code LOOP
			v_qty := v_qty + r_qty2.init_qty;
		END LOOP;
	ELSIF v_type_pl = 2 THEN
		FOR r_qty IN SELECT init_qty FROM idc_tb_in_claim_item WHERE cl_idx = v_code::integer AND it_code = v_it_code LOOP
			v_qty := v_qty + r_qty.init_qty;
		END LOOP;
		FOR r_qty2 IN SELECT init_qty FROM idc_tb_in_claim_item_v2 WHERE cl_idx = v_code::integer AND it_code = v_it_code LOOP
			v_qty := v_qty + r_qty2.init_qty;
		END LOOP;
	ELSIF v_type_pl = 3 THEN
		FOR r_qty IN SELECT init_qty FROM idc_tb_in_local JOIN idc_tb_in_local_item USING(inlc_idx) WHERE po_code = substr(v_code,1,18)  AND pl_no = substr(v_code,20)::integer AND it_code = v_it_code LOOP
			v_qty := v_qty + r_qty.init_qty;
		END LOOP;
		FOR r_qty2 IN SELECT init_qty FROM idc_tb_in_local_v2 JOIN idc_tb_in_local_item_v2 USING(inlc_idx) WHERE po_code = substr(v_code,1,18)  AND pl_no = substr(v_code,20)::integer AND it_code = v_it_code LOOP
			v_qty := v_qty + r_qty2.init_qty;
		END LOOP;
	END IF;

	IF v_qty IS NULL THEN
		v_qty = 0;
	END IF;

	RETURN v_qty;
END;
$$
;

alter function idc_arrivedqty(integer, varchar, varchar) owner to dskim
;

create function idc_availablepllocalqty(v_po_no character varying, v_it_code character varying, v_pl_no integer) returns numeric
	language plpgsql
as $$
DECLARE
	v_po_qty integer := 0;
	v_pl_qty integer := 0;
	v_pl_ref_qty integer;
	v_qty integer;
	r_po_qty record;
	r_pl_qty record;
BEGIN

	FOR r_pl_qty IN SELECT plit_qty FROM idc_tb_pl_local_item WHERE po_code = v_po_no AND it_code = v_it_code LOOP
		v_pl_qty := v_pl_qty + r_pl_qty.plit_qty;
	END LOOP;
	IF v_pl_qty IS NULL THEN v_pl_qty = 0; END IF;

	FOR r_po_qty IN SELECT poit_qty FROM idc_tb_po_local_item WHERE po_code = v_po_no AND it_code = v_it_code LOOP
		v_po_qty := v_po_qty + r_po_qty.poit_qty;
	END LOOP;
	IF v_po_qty IS NULL THEN v_po_qty = 0; END IF;

	if v_pl_no is not null then
		select into v_pl_ref_qty plit_qty FROM idc_tb_pl_local_item where po_code = v_po_no and pl_no = v_pl_no and it_code = v_it_code;
		if v_pl_ref_qty is null then v_pl_ref_qty := 0; end if;
		v_qty = v_po_qty - v_pl_qty + v_pl_ref_qty;
	else
		v_qty = v_po_qty - v_pl_qty;
	end if;

	RETURN v_qty;
END;
$$
;

alter function idc_availablepllocalqty(varchar, varchar, integer) owner to dskim
;

create function idc_canusedqty(v_type_pl integer, v_pl_idx character varying, v_inpl_idx integer, v_it_code character varying) returns numeric
	language plpgsql
as $$
DECLARE
	v_pl_qty numeric;
	v_coming_qty numeric;
	v_inpl_qty numeric;
	v_max_qty numeric;
BEGIN
	IF v_type_pl = 1 THEN
		SELECT INTO v_pl_qty sum(plit_qty) FROM idc_tb_pl_item WHERE pl_idx = v_pl_idx::integer AND it_code = v_it_code;
		SELECT INTO v_coming_qty sum(init_qty) FROM idc_tb_in_pl_item_v2 WHERE pl_idx = v_pl_idx::integer AND it_code = v_it_code;
		SELECT INTO v_inpl_qty sum(init_qty) FROM idc_tb_in_pl_item_v2 WHERE inpl_idx = v_inpl_idx AND it_code = v_it_code;
	ELSIF v_type_pl = 2 THEN
		SELECT INTO v_pl_qty sum(clit_qty) FROM idc_tb_claim_item WHERE cl_idx = v_pl_idx::integer AND it_code = v_it_code;
		SELECT INTO v_coming_qty sum(init_qty) FROM idc_tb_in_claim_item WHERE cl_idx = v_pl_idx::integer AND it_code = v_it_code;
		SELECT INTO v_inpl_qty sum(init_qty) FROM idc_tb_in_claim_item WHERE incl_idx = v_inpl_idx AND it_code = v_it_code;
	ELSIF v_type_pl = 3 THEN
		SELECT INTO v_pl_qty sum(plit_qty) FROM idc_tb_pl_local_item WHERE po_code = substr(v_pl_idx,1,18) AND pl_no = substr(v_pl_idx,20)::integer AND it_code = v_it_code;
		SELECT INTO v_coming_qty sum(init_qty) FROM idc_tb_in_local join idc_tb_in_local_item using(inlc_idx) WHERE po_code = substr(v_pl_idx,1,18) AND pl_no = substr(v_pl_idx,20)::integer AND it_code = v_it_code;
		SELECT INTO v_inpl_qty sum(init_qty) FROM idc_tb_in_local join idc_tb_in_local_item using(inlc_idx) WHERE inlc_idx = v_inpl_idx AND it_code = v_it_code;
	END IF;

	if v_pl_qty is null then v_pl_qty = 0; end if;
	if v_coming_qty is null then v_coming_qty = 0; end if;
	if v_inpl_qty is null then v_inpl_qty = 0; end if;
	v_max_qty = (v_pl_qty - v_coming_qty) + v_inpl_qty;

	RETURN v_max_qty;
END;
$$
;

alter function idc_canusedqty(integer, varchar, integer, varchar) owner to dskim
;

create function idc_ceksn(v_sn character varying) returns integer
	language plpgsql
as $$
DECLARE
    v_val_count integer;
    v_val integer := 0;
BEGIN

    IF TRIM(v_sn) != TRIM('-') THEN

        SELECT INTO v_val_count count(v_sn) FROM idc_tb_service_reg_item WHERE trim(sgit_serial_number) = trim(v_sn);

        IF v_val_count NOT IN (0,1)  THEN
            v_val := v_val_count::text;
        ELSE
            v_val := 0;
        END IF;

    END IF;

    RETURN v_val;
END;
$$
;

alter function idc_ceksn(varchar) owner to dskim
;

create function idc_cfmbillingonly(v_code character varying, v_bill_date date, v_wh_date date, v_confirm_by character varying) returns void
	language plpgsql
as $$
DECLARE
	rec record;
	rec2 record;
	v_logs_code varchar;
	v_vat_val numeric;
	v_type_bill integer;
	v_cfm_wh_date_val date;
BEGIN

	SELECT INTO v_vat_val bill_vat FROM idc_tb_billing WHERE bill_code= v_code;
	SELECT INTO v_type_bill bill_type_billing FROM idc_tb_billing WHERE bill_code= v_code;
	SELECT INTO v_cfm_wh_date_val bill_cfm_wh_date FROM idc_tb_billing WHERE bill_code= v_code;
	IF v_vat_val <= 0 THEN RAISE EXCEPTION 'ERR_TYPE_NONVAT_INVOICE'; END IF;
	IF v_type_bill = 1 THEN RAISE EXCEPTION 'ERR_TYPE_INVOICE'; END IF;
	IF v_bill_date < '2012-06-01' THEN RAISE EXCEPTION 'ERR_INVOICE_DATE'; END IF;
	IF v_cfm_wh_date_val is not null THEN RAISE EXCEPTION 'ERR_INVOICE_ALREADY_CONFIRMED'; END IF;

	UPDATE idc_tb_billing SET
		bill_cfm_wh_delivery_by_account = v_confirm_by,
		bill_cfm_wh_delivery_timestamp = CURRENT_TIMESTAMP,
		bill_cfm_wh_date = v_wh_date
	WHERE bill_code = v_code;

	FOR rec IN SELECT it_code, SUM(biit_qty) AS qty FROM idc_tb_billing_item WHERE bill_code = v_code GROUP BY it_code ORDER BY it_code LOOP
		FOR rec2 IN SELECT * FROM idc_tb_item_function WHERE it_code = rec.it_code ORDER BY it_code LOOP
			-- Minus stock NORMAL
			INSERT INTO idc_tb_billing_item_only (bill_code, it_code, biit_wh_location, biit_type, biit_qty, biit_confirm_date, biit_document_date)
			VALUES (v_code, rec2.ipf_it_code, 1, 1, rec2.ipf_qty * rec.qty, v_wh_date, v_bill_date);
			SELECT INTO v_logs_code idc_getStockLogIdx(rec2.ipf_it_code, 1, 1, CURRENT_DATE);
			INSERT INTO idc_tb_log_detail(log_code, it_code, log_wh_location, log_type, log_document_type, log_document_idx, log_document_no, log_document_date,log_cfm_timestamp, log_cfm_by_account, log_qty)
			VALUES (v_logs_code, rec2.ipf_it_code, 1, 1, 'Move Type (Billing No Only)', null, v_code, v_bill_date, CURRENT_TIMESTAMP, v_confirm_by, (rec2.ipf_qty * rec.qty) * -1);

			-- Plus stock DTD
			INSERT INTO idc_tb_billing_item_only (bill_code, it_code, biit_wh_location, biit_type, biit_qty, biit_confirm_date, biit_document_date)
			VALUES (v_code, rec2.ipf_it_code, 1, 2, (rec2.ipf_qty * rec.qty) * -1, v_wh_date, v_bill_date);
			SELECT INTO v_logs_code idc_getStockLogIdx(rec2.ipf_it_code, 1, 2, CURRENT_DATE);
			INSERT INTO idc_tb_log_detail(log_code, it_code, log_wh_location, log_type, log_document_type, log_document_idx, log_document_no, log_document_date,log_cfm_timestamp, log_cfm_by_account, log_qty)
			VALUES (v_logs_code, rec2.ipf_it_code, 1, 2, 'Move Type (Billing No Only)', null, v_code, v_bill_date, CURRENT_TIMESTAMP, v_confirm_by, rec2.ipf_qty * rec.qty);

		END LOOP;
	END LOOP;

END;
$$
;

alter function idc_cfmbillingonly(varchar, date, date, varchar) owner to dskim
;

create function idc_cfmdelivery(v_code character varying, v_delivery_charge numeric, v_cfm_delivery_by character varying) returns void
	language plpgsql
as $$
DECLARE
	v_delivery_freight_charge numeric;
	v_remainder numeric := 0;
BEGIN
	SELECT INTO v_delivery_freight_charge bill_delivery_freight_charge FROM idc_tb_billing WHERE bill_code = v_code;

	IF v_delivery_freight_charge > v_delivery_charge THEN
		v_remainder = -(v_delivery_freight_charge - v_delivery_charge);
	ELSIF v_delivery_freight_charge < v_delivery_charge THEN
		v_remainder = v_delivery_charge - v_delivery_freight_charge;
	END IF;

	UPDATE idc_tb_billing SET
		bill_delivery_freight_charge = v_delivery_charge,
		bill_cfm_delivery = CURRENT_TIMESTAMP,
		bill_cfm_delivery_by = v_cfm_delivery_by,
		bill_total_billing = bill_total_billing + v_remainder,
		bill_total_billing_rev = bill_total_billing_rev + v_remainder,
		bill_remain_amount = bill_remain_amount + v_remainder
	WHERE bill_code = v_code;
END;
$$
;

alter function idc_cfmdelivery(varchar, numeric, varchar) owner to dskim
;

create function idc_cfmdelivery(v_code character varying, v_delivery_date date, v_delivery_by character varying, v_confirm_by character varying) returns void
	language plpgsql
as $$
BEGIN
	UPDATE idc_tb_billing SET
		bill_delivery_timestamp		= CURRENT_TIMESTAMP,
		bill_delivery_by_account	= v_confirm_by,
		bill_delivery_date			= v_delivery_date,
		bill_delivery_to_customer_by	= v_delivery_by
	WHERE
		bill_code = v_code;
END;
$$
;

alter function idc_cfmdelivery(varchar, date, varchar, varchar) owner to dskim
;

create function idc_cfmdeliverycharge(v_code character varying, v_delivery_charge numeric, v_cfm_delivery_by character varying) returns void
	language plpgsql
as $$
DECLARE
	v_delivery_freight_charge numeric;
	v_remainder numeric := 0;
BEGIN
	SELECT INTO v_delivery_freight_charge bill_delivery_freight_charge FROM idc_tb_billing WHERE bill_code = v_code;

	IF v_delivery_freight_charge > v_delivery_charge THEN
		v_remainder = -(v_delivery_freight_charge - v_delivery_charge);
	ELSIF v_delivery_freight_charge < v_delivery_charge THEN
		v_remainder = v_delivery_charge - v_delivery_freight_charge;
	END IF;

	UPDATE idc_tb_billing SET
		bill_delivery_freight_charge = v_delivery_charge,
		bill_cfm_delivery			 = CURRENT_TIMESTAMP,
		bill_cfm_delivery_by		 = v_cfm_delivery_by,
		bill_total_billing			 = bill_total_billing + v_remainder,
		bill_total_billing_rev		 = bill_total_billing_rev + v_remainder,
		bill_remain_amount			 = bill_remain_amount + v_remainder
	WHERE
		bill_code = v_code;
END;
$$
;

alter function idc_cfmdeliverycharge(varchar, numeric, varchar) owner to dskim
;

create function idc_cfmdfdelivery(v_code character varying, v_delivery_date date, v_delivery_by character varying, v_confirm_by character varying) returns void
	language plpgsql
as $$
BEGIN
	UPDATE idc_tb_df SET
		df_delivery_timestamp		= CURRENT_TIMESTAMP,
		df_delivery_confirmed_by	= v_confirm_by,
		df_delivery_date			= v_delivery_date,
		df_delivery_to_customer_by	= v_delivery_by
	WHERE
		df_code = v_code;
END;
$$
;

alter function idc_cfmdfdelivery(varchar, date, varchar, varchar) owner to dskim
;

create function idc_cfmdrdelivery(v_code character varying, v_delivery_date date, v_delivery_by character varying, v_confirm_by character varying) returns void
	language plpgsql
as $$
BEGIN
	UPDATE idc_tb_dr SET
		dr_delivery_timestamp		= CURRENT_TIMESTAMP,
		dr_delivery_confirmed_by	= v_confirm_by,
		dr_delivery_date			= v_delivery_date,
		dr_delivery_to_customer_by	= v_delivery_by
	WHERE
		dr_code = v_code;
END;
$$
;

alter function idc_cfmdrdelivery(varchar, date, varchar, varchar) owner to dskim
;

create function idc_cfmdtdelivery(v_code character varying, v_delivery_date date, v_delivery_by character varying, v_confirm_by character varying) returns void
	language plpgsql
as $$
BEGIN
	UPDATE idc_tb_dt SET
		dt_delivery_timestamp		= CURRENT_TIMESTAMP,
		dt_delivery_confirmed_by	= v_confirm_by,
		dt_delivery_date			= v_delivery_date,
		dt_delivery_to_customer_by	= v_delivery_by
	WHERE
		dt_code = v_code;
END;
$$
;

alter function idc_cfmdtdelivery(varchar, date, varchar, varchar) owner to dskim
;

create function idc_cfmrequestbymarketing(v_wh_idx integer, v_type integer, v_doc character varying, v_idx integer, v_received_by character varying, v_received_date date, v_cfm_received_by character varying) returns void
	language plpgsql
as $$
DECLARE
	recI record;
	recII record;
	v_cur_inde_idx integer := 0;
BEGIN

	IF v_type = 1 THEN
		UPDATE idc_tb_request SET
			req_received_by_account			= v_received_by,
			req_received_date				= v_received_date,
			req_cfm_marketing_by_account	= v_cfm_received_by,
			req_cfm_marketing_timestamp		= CURRENT_TIMESTAMP
		WHERE req_code = v_doc;

		/* Insert into idc_tb_incoming_demo, idc_tb_incoming_demo_stock, idc_tb_incoming_demo_ed */
		INSERT INTO idc_tb_incoming_demo(inde_doc_ref, inde_doc_type, inde_date, inde_received_by, inde_confirmed_by_account)
		VALUES (v_doc, 1, v_received_date, v_received_by, v_cfm_received_by);

		v_cur_inde_idx := currval('idc_tb_incoming_demo_inde_idx_seq');
		/* + demo qty */
		FOR recI IN SELECT it_code, otst_qty FROM idc_tb_outgoing_stock_v2 WHERE out_idx = v_wh_idx LOOP
			INSERT INTO idc_tb_incoming_demo_stock(inde_idx, it_code, indst_qty)
			VALUES(v_cur_inde_idx, recI.it_code, recI.otst_qty);
		END LOOP;

		/* + E/D demo qty */
		FOR recII IN SELECT it_code, oted_expired_date, oted_qty  FROM idc_tb_outgoing_stock_ed WHERE out_idx = v_wh_idx LOOP
			INSERT INTO idc_tb_incoming_demo_ed(inde_idx, it_code, idded_expired_date, idded_qty)
			VALUES(v_cur_inde_idx, recII.it_code, recII. oted_expired_date, recII.oted_qty);
		END LOOP;

	ELSIF v_type = 2 THEN
		UPDATE idc_tb_incoming_marketing SET
			inm_received_by_account			= v_received_by,
			inm_received_date				= v_received_date,
			inm_cfm_marketing_by_account	= v_cfm_received_by,
			inm_cfm_marketing_timestamp		= CURRENT_TIMESTAMP
		WHERE inm_idx = v_idx;

		/* Insert into idc_tb_incoming_demo, idc_tb_incoming_demo_stock, idc_tb_incoming_demo_ed */
		INSERT INTO idc_tb_incoming_demo(inde_doc_ref, inde_doc_type, inde_date, inde_received_by, inde_confirmed_by_account)
		VALUES (v_doc, 2, v_received_date, v_received_by, v_cfm_received_by);

		v_cur_inde_idx := currval('idc_tb_incoming_demo_inde_idx_seq');
		/* + demo qty */
		FOR recI IN SELECT it_code, init_demo_qty FROM idc_tb_incoming_item WHERE inc_idx = v_wh_idx LOOP
			INSERT INTO idc_tb_incoming_demo_stock(inde_idx, it_code, indst_qty)
			VALUES(v_cur_inde_idx, recI.it_code, recI.init_demo_qty);
		END LOOP;

		/* + E/D demo qty */
		FOR recII IN SELECT it_code, ided_expired_date, ided_qty  FROM idc_tb_incoming_ed_demo WHERE inc_idx = v_wh_idx LOOP
			INSERT INTO idc_tb_incoming_demo_ed(inde_idx, it_code, idded_expired_date, idded_qty)
			VALUES(v_cur_inde_idx, recII.it_code, recII.ided_expired_date, recII.ided_qty);
		END LOOP;

	END IF;

END;
$$
;

alter function idc_cfmrequestbymarketing(integer, integer, varchar, integer, varchar, date, varchar) owner to dskim
;

create function idc_cfmrequestdemobymarketing(v_code character varying, v_confirm_by character varying, v_log_by_account character varying, v_ed_it_code character varying[], v_ed_it_date character varying[], v_ed_it_qty numeric[]) returns void
	language plpgsql
as $$
DECLARE
	v_date date;
	recI record;
	v_i integer := 1;
BEGIN

	UPDATE idc_tb_using_demo SET
		use_confirm_by_account			= v_confirm_by,
		use_cfm_marketing_by_account	= v_log_by_account,
		use_cfm_marketing_timestamp		= CURRENT_TIMESTAMP
	WHERE use_code = v_code;

	/* demo qty */
	FOR recI IN SELECT it_code, usit_qty FROM idc_tb_using_demo_item WHERE use_code = v_code LOOP
		INSERT INTO idc_tb_using_demo_stock(use_code, it_code, usst_qty)
		VALUES(v_code, recI.it_code, recI.usit_qty);
	END LOOP;

	/* - E/D demo qty */
	WHILE v_ed_it_code[v_i] IS NOT NULL AND v_ed_it_code[v_i] != '' LOOP
		v_date = v_ed_it_date[v_i];
		INSERT INTO idc_tb_using_demo_ed (use_code, it_code, used_expired_date, used_qty)
		VALUES (v_code, v_ed_it_code[v_i], v_date, v_ed_it_qty[v_i]);
		v_i := v_i + 1;
	END LOOP;

END;
$$
;

alter function idc_cfmrequestdemobymarketing(varchar, varchar, varchar, character varying[], character varying[], numeric[]) owner to dskim
;

create function idc_cfmreturnbillingonly(v_code character varying, v_turn_date date, v_wh_date date, v_confirm_by character varying) returns void
	language plpgsql
as $$
DECLARE
	rec record;
	rec2 record;
	v_logs_code varchar;
	v_vat_val numeric;
	v_type_turn integer;
	v_cfm_wh_date_val date;
BEGIN

	SELECT INTO v_vat_val turn_vat FROM idc_tb_return WHERE turn_code= v_code;
	SELECT INTO v_type_turn turn_paper FROM idc_tb_return WHERE turn_code= v_code;
	SELECT INTO v_cfm_wh_date_val turn_cfm_wh_date FROM idc_tb_return WHERE turn_code= v_code;
	IF v_vat_val <= 0 THEN RAISE EXCEPTION 'ERR_TYPE_NONVAT_RETURN'; END IF;
	IF v_type_turn = 0 THEN RAISE EXCEPTION 'ERR_TYPE_RETURN'; END IF;
	IF v_turn_date < '2012-06-01' THEN RAISE EXCEPTION 'ERR_RETURN_DATE'; END IF;
	IF v_cfm_wh_date_val is not null THEN RAISE EXCEPTION 'ERR_RETURN_ALREADY_CONFIRMED'; END IF;

	UPDATE idc_tb_return SET
		turn_cfm_wh_by_account	= v_confirm_by,
		turn_cfm_wh_timestamp	= CURRENT_TIMESTAMP,
		turn_cfm_wh_date		= v_wh_date
	WHERE turn_code = v_code;

	FOR rec IN SELECT it_code, SUM(reit_qty) AS qty FROM idc_tb_return_item WHERE turn_code = v_code GROUP BY it_code ORDER BY it_code LOOP
		FOR rec2 IN SELECT * FROM idc_tb_item_function WHERE it_code = rec.it_code ORDER BY it_code LOOP
			-- Minus stock DTD
			INSERT INTO idc_tb_return_item_only (turn_code, it_code, reit_wh_location, reit_type, reit_qty, reit_confirm_date, reit_document_date)
			VALUES (v_code, rec2.ipf_it_code, 1, 1, rec2.ipf_qty * rec.qty, v_wh_date, v_turn_date);
			SELECT INTO v_logs_code idc_getStockLogIdx(rec2.ipf_it_code, 1, 1, CURRENT_DATE);
			INSERT INTO idc_tb_log_detail(log_code, it_code, log_wh_location, log_type, log_document_type, log_document_idx, log_document_no, log_document_date,log_cfm_timestamp, log_cfm_by_account, log_qty)
			VALUES (v_logs_code, rec2.ipf_it_code, 1, 1, 'Move Type (Return No Only)', null, v_code, v_turn_date, CURRENT_TIMESTAMP, v_confirm_by, rec2.ipf_qty * rec.qty);

			-- Plus stock NORMAL
			INSERT INTO idc_tb_return_item_only (turn_code, it_code, reit_wh_location, reit_type, reit_qty, reit_confirm_date, reit_document_date)
			VALUES (v_code, rec2.ipf_it_code, 1, 2, (rec2.ipf_qty * rec.qty)*-1, v_wh_date, v_turn_date);
			SELECT INTO v_logs_code idc_getStockLogIdx(rec2.ipf_it_code, 1, 2, CURRENT_DATE);
			INSERT INTO idc_tb_log_detail(log_code, it_code, log_wh_location, log_type, log_document_type, log_document_idx, log_document_no, log_document_date,log_cfm_timestamp, log_cfm_by_account, log_qty)
			VALUES (v_logs_code, rec2.ipf_it_code, 1, 2, 'Move Type (Return No Only)', null, v_code, v_turn_date, CURRENT_TIMESTAMP, v_confirm_by, (rec2.ipf_qty * rec.qty)*-1);
		END LOOP;
	END LOOP;

END;
$$
;

alter function idc_cfmreturnbillingonly(varchar, date, date, varchar) owner to dskim
;

create function idc_cfmreturndemobymarketing(v_code character varying, v_confirm_by character varying, v_log_by_account character varying, v_ed_it_code character varying[], v_ed_it_date character varying[], v_ed_it_qty numeric[]) returns void
	language plpgsql
as $$
DECLARE
	v_date date;
	recI record;
	v_i integer := 1;
BEGIN

	UPDATE idc_tb_return_demo SET
		red_confirm_by_account			= v_confirm_by,
		red_cfm_marketing_by_account	= v_log_by_account,
		red_cfm_marketing_timestamp		= CURRENT_TIMESTAMP
	WHERE red_code = v_code;

	/* + demo qty */
	FOR recI IN SELECT it_code, rdit_qty FROM idc_tb_return_demo_item WHERE red_code = v_code LOOP
		INSERT INTO idc_tb_return_demo_stock(red_code, it_code, rdst_qty)
		VALUES(v_code, recI.it_code, recI.rdit_qty);
	END LOOP;

	/* + E/D demo qty */
	WHILE v_ed_it_code[v_i] IS NOT NULL AND v_ed_it_code[v_i] != '' LOOP
		v_date = v_ed_it_date[v_i];
		INSERT INTO idc_tb_return_demo_ed (red_code, it_code, rded_expired_date, rded_qty)
		VALUES (v_code, v_ed_it_code[v_i], v_date, v_ed_it_qty[v_i]);
		v_i := v_i + 1;
	END LOOP;

END;
$$
;

alter function idc_cfmreturndemobymarketing(varchar, varchar, varchar, character varying[], character varying[], numeric[]) owner to dskim
;

create function idc_cfmtukarfaktur(v_code character varying, v_tukar_faktur_date date, v_cfm_tukar_faktur_by character varying, v_due_date date) returns void
	language plpgsql
as $$
BEGIN
	UPDATE idc_tb_billing SET
		bill_cfm_tukar_faktur	 = CURRENT_TIMESTAMP,
		bill_tukar_faktur_date	 = v_tukar_faktur_date,
		bill_cfm_tukar_faktur_by = v_cfm_tukar_faktur_by,
		bill_payment_giro_due	 = v_due_date
	WHERE
		bill_code = v_code;
END;
$$
;

alter function idc_cfmtukarfaktur(varchar, date, varchar, date) owner to dskim
;

create function idc_cfmwarehouse(v_source character varying, v_code character varying, v_date date, v_cfm_by character varying) returns void
	language plpgsql
as $$
BEGIN
	IF v_source = 'billing' THEN
		UPDATE idc_tb_billing SET
			bill_cfm_wh_by_account= v_cfm_by,
			bill_cfm_wh_timestamp= CURRENT_TIMESTAMP,
			bill_cfm_wh_date= v_date
		WHERE bill_code = v_code;
	ELSIF v_source = 'order' THEN
		UPDATE idc_tb_order SET
			ord_cfm_wh_by_account= v_cfm_by,
			ord_cfm_wh_timestamp= CURRENT_TIMESTAMP,
			ord_cfm_wh_date= v_date
		WHERE ord_code = v_code;
	ELSIF v_source = 'return_billing' THEN
		UPDATE idc_tb_return SET
			turn_cfm_wh_by_account= v_cfm_by,
			turn_cfm_wh_timestamp= CURRENT_TIMESTAMP,
			turn_cfm_wh_date= v_date
		WHERE turn_code = v_code;
	ELSIF v_source = 'return_order' THEN
		UPDATE idc_tb_return_order SET
			reor_cfm_wh_by_account= v_cfm_by,
			reor_cfm_wh_timestamp= CURRENT_TIMESTAMP,
			reor_cfm_wh_date= v_date
		WHERE reor_code = v_code;
	END IF;
END;
$$
;

alter function idc_cfmwarehouse(varchar, varchar, date, varchar) owner to dskim
;

create function idc_checkduplicateorderno() returns character varying
	language plpgsql
as $$
DECLARE
v_value varchar := '';
rec record;v_prev_value varchar := '';
v_it_code varchar := '';
BEGIN
FOR rec IN SELECT a.ord_code FROM idc_tb_order as a JOIN idc_tb_delivery using (ord_code) ORDER BY ord_code LOOP
IF v_prev_value = rec.ord_code THEN
v_value := v_value || ', ' || rec.ord_code;
END IF;
v_prev_value := rec.ord_code;
END LOOP;

RETURN v_value;
END;
$$
;

alter function idc_checkduplicateorderno() owner to dskim
;

create function idc_checkduplicatewarrantyno() returns character varying
	language plpgsql
as $$
DECLARE
	v_value varchar := '';
	rec record;	v_prev_value varchar := '';
	v_it_code varchar := '';
BEGIN
	FOR rec IN SELECT wr_warranty_no, wr_inputted_by_account, wr_idx, it_code FROM idc_tb_warranty 	WHERE wr_inputted_timestamp::date = '2009-09-14' ORDER BY wr_warranty_no LOOP
		IF v_prev_value = rec.wr_warranty_no AND v_it_code = rec.it_code THEN
			v_value := v_value || ', ' || rec.wr_warranty_no;
		END IF;
			v_prev_value := rec.wr_warranty_no;
			v_it_code := rec.it_code;
	END LOOP;

	RETURN v_value;
END;
$$
;

alter function idc_checkduplicatewarrantyno() owner to dskim
;

create function idc_checked(v_inpl_idx integer, v_it_code character varying) returns character varying
	language plpgsql
as $$
DECLARE
	v_code varchar;
	v_row_count integer := 0;
	v_is_used boolean := false;
BEGIN
	SELECT INTO v_code it_code FROM idc_tb_in_pl_item_ed WHERE inpl_idx = v_inpl_idx AND it_code = v_it_code;
	GET DIAGNOSTICS v_row_count := ROW_COUNT;
	IF v_row_count >= 1 THEN
		v_is_used = true;
	END IF;
	RETURN v_is_used;
END;
$$
;

alter function idc_checked(integer, varchar) owner to dskim
;

create function idc_checkrecapidx(v_inpl_idx integer, v_it_code character varying) returns character varying
	language plpgsql
as $$
DECLARE
v_idx integer := 0;
BEGIN
	SELECT INTO v_idx rcp_idx FROM idc_tb_po_recap WHERE rcp_inpl_code = v_inpl_idx AND it_code = v_it_code;
	RETURN v_idx;
END;
$$
;

alter function idc_checkrecapidx(integer, varchar) owner to dskim
;

create function idc_comparedate(v_date_1 character varying, v_date_2 character varying, v_result character varying) returns character varying
	language plpgsql
as $$
DECLARE
	v_date1 date;
	v_date2 date;
	v_val varchar;
BEGIN

	IF v_date_1 is null THEN v_date1 := DATE '1970-01-01'; ELSE v_date1 := v_date_1::text; END IF;
	IF v_date_2 is null THEN v_date2 := DATE '1970-01-01'; ELSE v_date2 := v_date_2::text; END IF;

	IF v_result = 'position' THEN
		IF v_date1 > v_date2 THEN
			v_val = '1';
		ELSIF v_date2 > v_date1 THEN
			v_val = '2';
		END IF;
	ELSIF v_result = 'date' THEN
		IF v_date1 > v_date2 THEN
			v_val = v_date1::text;
		ELSIF v_date2 > v_date1 THEN
			v_val = v_date2::text;
		END IF;
	END IF;

	RETURN v_val;
END;
$$
;

alter function idc_comparedate(varchar, varchar, varchar) owner to dskim
;

create function idc_confirmdeliveryorder(v_code character varying, v_dept character varying, v_cfm_deli_by_account character varying, v_deliverd_date date, v_deliverd_by character varying, v_received_by_whom character varying, v_cus_code character varying) returns void
	language plpgsql
as $$
DECLARE
    v_type varchar;
    v_curr_deli_idx integer;
BEGIN

    SELECT INTO v_type ord_type FROM idc_tb_order WHERE ord_code = v_code;

    -- set user cannot modify this order sheet.
    UPDATE idc_tb_order SET
        ord_cfm_deli_by_account = v_cfm_deli_by_account,
        ord_cfm_deli_timestamp = CURRENT_TIMESTAMP -- Confirmed timestamp
    WHERE ord_code = v_code;

    -- insert idc_tb_delivery
    INSERT INTO idc_tb_delivery(ord_code, deli_type, deli_date, deli_by, deli_received_by)
    VALUES (v_code, v_type, v_deliverd_date, v_deliverd_by, v_received_by_whom);

    v_curr_deli_idx := currval('idc_tb_delivery_deli_idx_seq');

    -- make JO qty with OK qty
    IF v_type = 'OO' THEN
        INSERT INTO idc_tb_delivery_item (deli_idx, deit_dept, it_code, deit_date, cus_code, deit_jo_qty, deit_qty)
        SELECT v_curr_deli_idx, v_dept, it_code, v_deliverd_date, v_cus_code, odit_qty, odit_qty
    FROM idc_tb_order_item WHERE ord_code = v_code;

    ELSIF v_type = 'OK' THEN
        INSERT INTO idc_tb_delivery_item (deli_idx, deit_dept, it_code, deit_date, cus_code, deit_jk_qty, deit_qty)
        SELECT v_curr_deli_idx, v_dept, it_code, v_deliverd_date, v_cus_code, odit_qty, odit_qty FROM idc_tb_order_item WHERE ord_code = v_code;
    END IF;

END;
$$
;

alter function idc_confirmdeliveryorder(varchar, varchar, varchar, date, varchar, varchar, varchar) owner to dskim
;

create function idc_confirmpo(v_code character varying, v_sp_code character varying, v_po_date date, v_po_type integer, v_po_type_invoice integer, v_cfm_by_account character varying, v_it_code character varying[], v_poit_qty integer[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_logs_code varchar := '';
	v_wh_located integer := 1;
	v_confirmed_timestamp timestamp;
	v_po_type_inverse integer;
BEGIN

	SELECT INTO v_confirmed_timestamp po_confirmed_timestamp FROM idc_tb_po WHERE po_code = v_code;
	IF v_confirmed_timestamp is not null THEN RAISE EXCEPTION 'ERR_PO_ALREADY_CONFIRMED'; END IF;

	-- Set user cannot modify this PO
	UPDATE idc_tb_po SET
		po_confirmed_by_account = v_cfm_by_account,
		po_confirmed_timestamp  = CURRENT_TIMESTAMP
	WHERE po_code = v_code;

	IF v_po_type_invoice = 1 THEN
		-- Insert into tb_po_recap
		WHILE v_it_code[v_i] IS NOT NULL LOOP
			INSERT INTO idc_tb_po_recap (rcp_sp_code, rcp_po_code, rcp_confirmed_date, it_code, rcp_po_qty)
			VALUES (v_sp_code, v_code, CURRENT_TIMESTAMP, v_it_code[v_i], v_poit_qty[v_i]);
			v_i := v_i + 1;
		END LOOP;
	ELSIF v_po_type_invoice = 2 THEN
		IF v_po_type = 1 THEN v_po_type_inverse = 2;
		ELSIF v_po_type = 2 THEN v_po_type_inverse = 1;
		END IF;

		WHILE v_it_code[v_i] IS NOT NULL LOOP
			-- Minus stock inverse
			INSERT INTO idc_tb_po_item_only (po_code, it_code, poit_wh_location, poit_type, poit_qty, poit_confirm_date, poit_document_date)
			VALUES (v_code, v_it_code[v_i], 1, v_po_type_inverse, v_poit_qty[v_i]*-1, v_po_date, v_po_date);
			SELECT INTO v_logs_code idc_getStockLogIdx(v_it_code[v_i], 1, v_po_type_inverse, CURRENT_DATE);
			INSERT INTO idc_tb_log_detail(log_code, it_code, log_wh_location, log_type, log_document_type, log_document_idx, log_document_no, log_document_date,log_cfm_timestamp, log_cfm_by_account, log_qty)
			VALUES (v_logs_code, v_it_code[v_i], 1, v_po_type_inverse, 'Move Type (PO)', null, v_code, v_po_date, CURRENT_TIMESTAMP, v_cfm_by_account, v_poit_qty[v_i]*-1);

			-- Plus stock PO
			INSERT INTO idc_tb_po_item_only (po_code, it_code, poit_wh_location, poit_type, poit_qty, poit_confirm_date, poit_document_date)
			VALUES (v_code, v_it_code[v_i], 1, v_po_type, v_poit_qty[v_i], v_po_date, v_po_date);
			SELECT INTO v_logs_code idc_getStockLogIdx(v_it_code[v_i], 1, v_po_type, CURRENT_DATE);
			INSERT INTO idc_tb_log_detail(log_code, it_code, log_wh_location, log_type, log_document_type, log_document_idx, log_document_no, log_document_date,log_cfm_timestamp, log_cfm_by_account, log_qty)
			VALUES (v_logs_code, v_it_code[v_i], 1, v_po_type, 'Move Type (PO)', null, v_code, v_po_date, CURRENT_TIMESTAMP, v_cfm_by_account, v_poit_qty[v_i]);

			v_i := v_i + 1;
		END LOOP;
	END IF;

END;
$$
;

alter function idc_confirmpo(varchar, varchar, date, integer, integer, varchar, character varying[], integer[]) owner to dskim
;

create function idc_confirmpolocal(v_code character varying, v_cfm_by_account character varying) returns void
	language plpgsql
as $$
BEGIN

	UPDATE idc_tb_po_local SET
		po_confirmed_by_account = v_cfm_by_account,
		po_confirmed_timestamp  = CURRENT_TIMESTAMP
	WHERE po_code = v_code;

END;
$$
;

alter function idc_confirmpolocal(varchar, varchar) owner to dskim
;

create function idc_confirmreturn(v_std_idx integer, v_inc_idx integer, v_type integer, v_remark character varying, v_cfm_by_account character varying, v_doc_type character varying, v_doc_ref character varying, v_doc_date date, v_it_code character varying[], v_it_ed character varying[], v_it_type integer[], v_it_stock_qty numeric[], v_it_demo_qty numeric[], v_it_reject_qty numeric[], v_ed_stk_it_code character varying[], v_ed_stk_it_date character varying[], v_ed_stk_it_location integer[], v_ed_stk_it_qty numeric[], v_ed_demo_it_code character varying[], v_ed_demo_it_date character varying[], v_ed_demo_it_location integer[], v_ed_demo_it_qty numeric[], v_reject_it_code character varying[], v_reject_it_sn character varying[], v_reject_it_warranty character varying[], v_reject_it_desc character varying[]) returns void
	language plpgsql
as $$
DECLARE
    v_i integer := 1;
    v_j integer;
    v_k integer;
    v_ed date;
    v_variable varchar;
    v_is_confirmed boolean;
BEGIN

    -- Check validity, does this return already confirmed or not
    SELECT INTO v_is_confirmed inc_is_confirmed FROM idc_tb_incoming WHERE inc_idx = v_inc_idx;
    IF v_is_confirmed is TRUE THEN
        RAISE EXCEPTION 'DUPLICATE_CODE_EXIST';
    END IF;

    IF v_doc_type = 'Return Billing' THEN
        /* Update data in idc_tb_return */
        UPDATE idc_tb_return SET
            turn_cfm_wh_delivery_by_account = v_cfm_by_account,
            turn_cfm_wh_delivery_timestamp  = CURRENT_TIMESTAMP
        WHERE turn_code = substr(v_doc_ref,1,13);
    ELSIF v_doc_type = 'Return Order' THEN
        /* Update data in idc_tb_return_order */
        UPDATE idc_tb_return_order SET
            reor_cfm_wh_delivery_by_account = v_cfm_by_account,
            reor_cfm_wh_delivery_timestamp  = CURRENT_TIMESTAMP
        WHERE reor_code = substr(v_doc_ref,1,12);
    ELSIF v_doc_type = 'Return DT' THEN
        /* Update data in idc_tb_return_dt */
        UPDATE idc_tb_return_dt SET
            rdt_cfm_wh_delivery_by_account  = v_cfm_by_account,
            rdt_cfm_wh_delivery_timestamp   = CURRENT_TIMESTAMP
        WHERE rdt_code = substr(v_doc_ref,1,12);
    END IF;

    /* Update data in idc_tb_outstanding */
    UPDATE idc_tb_outstanding SET std_is_confirmed = TRUE WHERE std_idx = v_std_idx;

    /* Update data in idc_tb_incoming */
    UPDATE idc_tb_incoming SET
        inc_is_confirmed         = TRUE,
        inc_confirmed_by_account = v_cfm_by_account,
        inc_confirmed_timestamp  = CURRENT_TIMESTAMP,
        inc_remark               = v_remark
    WHERE inc_idx = v_inc_idx;

    WHILE v_it_code[v_i] IS NOT NULL LOOP
        v_j = 1;
        v_k = 1;

        IF v_it_ed[v_i] = 't' THEN
            /* incoming for E/D Stock */
            WHILE v_ed_stk_it_code[v_j] IS NOT NULL LOOP
                IF v_it_code[v_i] = v_ed_stk_it_code[v_j] THEN
                    v_ed = v_ed_stk_it_date[v_j];
                    INSERT INTO idc_tb_incoming_stock_ed_v2 (inc_idx, it_code, ined_wh_location, ined_expired_date, ined_qty)
                    VALUES (v_inc_idx, v_ed_stk_it_code[v_j], v_ed_stk_it_location[v_j], v_ed, v_ed_stk_it_qty[v_j]);
                END IF;
                v_j := v_j + 1;
            END LOOP;

            WHILE v_ed_demo_it_code[v_k] IS NOT NULL LOOP
                IF v_it_code[v_i] = v_ed_demo_it_code[v_k] THEN
                    v_ed = v_ed_demo_it_date[v_k];
                    INSERT INTO idc_tb_incoming_ed_demo(inc_idx, it_code, ided_expired_date, ided_qty)
                    VALUES(v_inc_idx, v_ed_demo_it_code[v_k], v_ed, v_ed_demo_it_qty[v_k]);
                END IF;
                v_k := v_k + 1;
            END LOOP;
        END IF;

        /* update idc_tb_incoming_item */
        UPDATE idc_tb_incoming_item SET
            init_stock_qty   = v_it_stock_qty[v_i],
            init_demo_qty    = v_it_demo_qty[v_i],
            init_reject_qty  = v_it_reject_qty[v_i],
            init_wh_location = 1
        WHERE it_code = v_it_code[v_i] AND inc_idx = v_inc_idx;

        v_i := v_i + 1;
    END LOOP;

    /* set stock, demo, reject :) */
    SELECT INTO v_variable idc_setAllStock(
        v_inc_idx, v_type, v_cfm_by_account, v_doc_type, v_doc_ref, v_doc_date, v_doc_type,
        v_it_code, v_it_ed, v_it_type,
        v_it_stock_qty, v_it_demo_qty, v_it_reject_qty,
        v_ed_stk_it_code, v_ed_stk_it_date, v_ed_stk_it_location, v_ed_stk_it_qty,
        v_ed_demo_it_code, v_ed_demo_it_date, v_ed_demo_it_location, v_ed_demo_it_qty,
        v_reject_it_code, v_reject_it_sn, v_reject_it_warranty, v_reject_it_desc
    );

END;
$$
;

alter function idc_confirmreturn(integer, integer, integer, varchar, varchar, varchar, varchar, date, character varying[], character varying[], integer[], numeric[], numeric[], numeric[], character varying[], character varying[], integer[], numeric[], character varying[], character varying[], integer[], numeric[], character varying[], character varying[], character varying[], character varying[]) owner to dskim
;

create function idc_confirmservicereg(v_code character varying, v_deli_date date, v_sign_confirm_by character varying, v_confirm_by character varying) returns void
	language plpgsql
as $$
DECLARE
	rec record;
BEGIN

	UPDATE idc_tb_service_reg SET
		sg_signature_completion_by	= v_sign_confirm_by,
		sg_complete_date		= v_deli_date,
		sg_complete_service		= true,
		sg_complete_by_account	= v_confirm_by,
		sg_complete_timestamp	= CURRENT_TIMESTAMP
	WHERE sg_code = v_code;

	FOR rec IN SELECT sgit_idx, sgit_delivery_date, sgit_finishing_date FROM idc_tb_service_reg_item WHERE sg_code = v_code LOOP
		IF rec.sgit_delivery_date is null THEN
			UPDATE idc_tb_service_reg_item SET sgit_delivery_date = v_deli_date WHERE sgit_idx=rec.sgit_idx;
		END IF;
		IF rec.sgit_finishing_date is null THEN
			UPDATE idc_tb_service_reg_item SET sgit_finishing_date = v_deli_date WHERE sgit_idx=rec.sgit_idx;
		END IF;
		UPDATE idc_tb_service_reg_item SET sgit_status = 2 WHERE sgit_idx=rec.sgit_idx;
	END LOOP;

END;
$$
;

alter function idc_confirmservicereg(varchar, date, varchar, varchar) owner to dskim
;

create function idc_deletebilling(v_code character varying, v_book_idx integer, v_use_password boolean, v_admin_account integer, v_admin_password character varying) returns void
	language plpgsql
as $$
DECLARE
    v_ma_idx integer;
BEGIN

    IF v_use_password IS TRUE THEN
        SELECT INTO v_ma_idx ma_idx FROM tb_mbracc WHERE ma_idx = v_admin_account AND ma_password = v_admin_password;

        IF NOT FOUND THEN
            RAISE EXCEPTION 'FAIL_TO_AUTH';
        END IF;
    END IF;

    UPDATE idc_tb_faktur_pajak_item SET
    	bill_code = null,
    	fkit_number = '010.' || substr(fkit_number, 5, 15)
    WHERE bill_code = v_code;
    DELETE FROM idc_tb_billing WHERE bill_code  = v_code;
    DELETE FROM idc_tb_booking WHERE book_idx   = v_book_idx;

END;
$$
;

alter function idc_deletebilling(varchar, integer, boolean, integer, varchar) owner to dskim
;

create function idc_deletedemostock(v_it_code character varying, v_ed date, v_it_desc character varying, v_it_qty numeric, v_log_by_account character varying) returns void
	language plpgsql
as $$
BEGIN

	INSERT INTO idc_tb_reject_demo (it_code, rjde_warranty, rjde_desc, rjde_deleted_by_account, rjde_qty)
	VALUES(v_it_code, v_ed, v_it_desc, v_log_by_account, v_it_qty);

END;
$$
;

alter function idc_deletedemostock(varchar, date, varchar, numeric, varchar) owner to dskim
;

create function idc_deleteedstock(v_it_code character varying, v_it_date date, v_location integer, v_deleted_by_account character varying) returns void
	language plpgsql
as $$
DECLARE
	--rec record;
	v_vat_stock numeric;
	v_non_stock numeric;
	v_ed_stock numeric;
	v_diff_stock numeric;

	v_cur_rjed_idx integer;
	v_log_code varchar;
BEGIN

	SELECT INTO v_non_stock stk_qty FROM idc_tb_stock_v2 WHERE it_code = v_it_code AND stk_wh_location = v_location;
	SELECT INTO v_vat_stock stk_qty FROM idc_tb_stock_v2 WHERE it_code = v_it_code AND stk_wh_location = v_location;
	SELECT INTO v_ed_stock sted_qty FROM idc_tb_stock_ed WHERE it_code = v_it_code AND sted_wh_location = v_location AND sted_expired_date = v_it_date;

	IF v_non_stock is null THEN v_non_stock := 0; END IF;
	IF v_vat_stock is null THEN v_vat_stock := 0; END IF;

	v_diff_stock = v_ed_stock - v_non_stock;

	IF v_diff_stock <= 0 THEN
		INSERT INTO idc_tb_reject_ed (it_code, rjed_wh_location, rjed_type, rjed_expired_date, rjed_qty, rjed_deleted_by_account)
		VALUES (v_it_code, v_location, 2, v_it_date, v_ed_stock, v_deleted_by_account);
		v_cur_rjed_idx := currval('idc_tb_reject_ed_rjed_idx_seq');
		--SELECT INTO v_log_code idc_insertStockLog(v_it_code,v_location, 2, 'Reject ED', v_cur_rjed_idx, null, null, v_deleted_by_account, false, v_ed_stock);
	ELSE
		INSERT INTO idc_tb_reject_ed (it_code, rjed_wh_location, rjed_type, rjed_expired_date, rjed_qty, rjed_deleted_by_account)
		VALUES (v_it_code, v_location, 2, v_it_date, v_non_stock, v_deleted_by_account);
		v_cur_rjed_idx := currval('idc_tb_reject_ed_rjed_idx_seq');
		SELECT INTO v_log_code idc_insertStockLog(v_it_code,v_location, 2, 'Reject ED', v_cur_rjed_idx, null, null, v_deleted_by_account, false, v_non_stock);

		INSERT INTO idc_tb_reject_ed (it_code, rjed_wh_location, rjed_type, rjed_expired_date, rjed_qty, rjed_deleted_by_account)
		VALUES (v_it_code, v_location, 1, v_it_date, v_diff_stock, v_deleted_by_account);
		v_cur_rjed_idx := currval('idc_tb_reject_ed_rjed_idx_seq');
		SELECT INTO v_log_code idc_insertStockLog(v_it_code,v_location, 1, 'Reject ED', v_cur_rjed_idx, null, null, v_deleted_by_account, false, v_diff_stock);
	END IF;

END;
$$
;

alter function idc_deleteedstock(varchar, date, integer, varchar) owner to dskim
;

create function idc_deleteincomingpllocal(v_inlc_idx integer, v_deleted_by character varying) returns void
	language plpgsql
as $$
DECLARE
	v_logs_disabled varchar;
	v_log_document_no varchar;
	rec record;
BEGIN

	SELECT INTO v_log_document_no log_document_no FROM idc_tb_log_detail WHERE log_document_idx = v_inlc_idx AND log_document_type = 'PL Local';

	DELETE FROM idc_tb_in_local_v2 WHERE inlc_idx = v_inlc_idx;

	DELETE FROM idc_tb_log_detail WHERE log_document_no = v_log_document_no;
/*
	FOR rec IN SELECT it_code, log_code FROM idc_tb_stock_logs WHERE log_document_idx = v_inlc_idx LOOP
		SELECT INTO v_logs_disabled idc_getLastStockLogs(12,v_inlc_idx,null,rec.it_code);
		IF v_logs_disabled IS NOT NULL THEN
			UPDATE idc_tb_stock_logs SET
				log_qty_status			= false,
				log_uncfm_by_account	= v_deleted_by,
				log_uncfm_timestamp		= current_timestamp
			WHERE it_code = rec.it_code AND log_code = v_logs_disabled;
		END IF;
	END LOOP;
*/
END;
$$
;

alter function idc_deleteincomingpllocal(integer, varchar) owner to dskim
;

create function idc_deletepayment(v_code character varying, v_pay_idx integer, v_deleted_amount numeric) returns void
	language plpgsql
as $$
DECLARE
	v_remain numeric;
BEGIN

	SELECT INTO v_remain bill_remain_amount FROM idc_tb_billing WHERE bill_code = v_code;

	-- Delete payment
	DELETE FROM idc_tb_payment WHERE bill_code = v_code AND pay_idx = v_pay_idx;

	-- Update remain amount in tb_billing
	UPDATE idc_tb_billing SET bill_remain_amount = v_remain + v_deleted_amount WHERE bill_code = v_code;

END;
$$
;

alter function idc_deletepayment(varchar, integer, numeric) owner to dskim
;

create function idc_deleterejectstock(v_reject_idx integer, v_it_code character varying, v_it_location integer, v_it_type integer, v_log_by_account character varying) returns void
	language plpgsql
as $$
DECLARE
	v_logs_disabled varchar;
BEGIN

	UPDATE idc_tb_stock SET
		stk_qty		= stk_qty + 1,
		stk_updated	= current_timestamp
	WHERE it_code = v_it_code and stk_wh_location = v_it_location and stk_type = v_it_type;

	SELECT INTO v_logs_disabled idc_getLastStockLogs(27,v_reject_idx,null,v_it_code);
	IF v_logs_disabled IS NOT NULL THEN
		UPDATE idc_tb_stock_logs SET
			log_qty_status			= false,
			log_uncfm_by_account	= v_log_by_account,
			log_uncfm_timestamp		= current_timestamp
		WHERE it_code = v_it_code AND log_code = v_logs_disabled;
	END IF;

	DELETE FROM tb_reject_item WHERE rjit_idx = v_reject_idx;
END;
$$
;

alter function idc_deleterejectstock(integer, varchar, integer, integer, varchar) owner to dskim
;

create function idc_deletereturnbilling(v_code character varying, v_paper integer, v_bill_code character varying, v_std_idx integer, v_inc_idx integer, v_return_condition integer, v_cus_to character varying, v_ship_to character varying, v_total_return numeric) returns character varying
	language plpgsql
as $$
DECLARE
	v_pay_idx integer;
BEGIN
	DELETE FROM idc_tb_return WHERE turn_code = v_code;

	IF v_return_condition = 2 THEN
		UPDATE idc_tb_billing SET
		bill_remain_amount		= bill_remain_amount + v_total_return,
		bill_total_billing_rev	= bill_total_billing_rev + v_total_return
		WHERE bill_code = v_bill_code;
	ELSIF v_return_condition = 3 THEN
		/* insert deposit to idc_tb_return_amount */
		DELETE FROM idc_tb_deposit WHERE turn_code = v_code;
	ELSIF v_return_condition = 4 THEN
		/*Mengurangi Payment yang sudah ada sebelumnya */
		SELECT INTO v_pay_idx pay_idx FROM idc_tb_payment WHERE pay_remark = v_code;
		DELETE FROM idc_tb_payment WHERE pay_idx = v_pay_idx;

		/* Update total_amount yang ada di idc_tb_billing */
		UPDATE idc_tb_billing SET bill_remain_amount = 0 WHERE bill_code = v_bill_code;	END IF;	IF v_paper = 0 THEN
		DELETE FROM idc_tb_outstanding WHERE std_idx = v_std_idx;
		DELETE FROM idc_tb_incoming WHERE inc_idx = v_inc_idx;
	END IF;
	RETURN NULL;
END;
$$
;

alter function idc_deletereturnbilling(varchar, integer, varchar, integer, integer, integer, varchar, varchar, numeric) owner to dskim
;

create function idc_detailapotikorder(v_code character varying, v_book_idx integer, v_type character varying, v_received_by character varying, v_confirm_by character varying, v_po_date date, v_po_no character varying, v_vat numeric, v_cus_to character varying, v_cus_to_attn character varying, v_cus_to_address character varying, v_ship_to character varying, v_ship_to_attn character varying, v_ship_to_address character varying, v_bill_to character varying, v_bill_to_attn character varying, v_bill_to_address character varying, v_price_discount numeric, v_price_chk integer, v_delivery_chk integer, v_delivery_by character varying, v_delivery_freight_charge numeric, v_payment_chk integer, v_payment_widthin_days integer, v_payment_closing_on date, v_payment_cash_by character varying, v_payment_check_by character varying, v_payment_transfer_by character varying, v_payment_giro_by character varying, v_remark character varying, v_lastupdated_by_account character varying) returns void
	language plpgsql
as $$
BEGIN
	UPDATE idc_tb_order SET
		ord_lastupdated_by_account = v_lastupdated_by_account,
		ord_lastupdated_timestamp = CURRENT_TIMESTAMP,
		ord_po_date = v_po_date,
		ord_po_no = v_po_no,
		ord_received_by = v_received_by,
		ord_confirm_by = v_confirm_by,
		ord_revision_time = ord_revision_time + 1,
		ord_vat	= v_vat,
		ord_cus_to = v_cus_to,
		ord_cus_to_attn = v_cus_to_attn,
		ord_cus_to_address = v_cus_to_address,
		ord_ship_to = v_ship_to,
		ord_ship_to_attn = v_ship_to_attn,
		ord_ship_to_address = v_ship_to_address,
		ord_bill_to = v_bill_to,
		ord_bill_to_attn = v_bill_to_attn,
		ord_bill_to_address = v_bill_to_address,
		ord_price_discount = v_price_discount,
		ord_price_chk = v_price_chk,
		ord_delivery_chk = v_delivery_chk,
		ord_delivery_by = v_delivery_by,
		ord_delivery_freight_charge = v_delivery_freight_charge,
		ord_payment_chk = v_payment_chk,
		ord_payment_widthin_days = v_payment_widthin_days,
		ord_payment_closing_on = v_payment_closing_on,
		ord_payment_cash_by = v_payment_cash_by,
		ord_payment_check_by = v_payment_check_by,
		ord_payment_transfer_by = v_payment_transfer_by,
		ord_payment_giro_by = v_payment_giro_by,
		ord_remark = v_remark
	WHERE ord_code = v_code;
END;
$$
;

alter function idc_detailapotikorder(varchar, integer, varchar, varchar, varchar, date, varchar, numeric, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, numeric, integer, integer, varchar, numeric, integer, integer, date, varchar, varchar, varchar, varchar, varchar, varchar) owner to dskim
;

create function idc_getaccountgrade(v_access character varying, v_ma_idx integer) returns character varying
	language plpgsql
as $$
DECLARE
	v_is_manager boolean;
	v_value varchar;
BEGIN

	IF v_access = 'ALL' THEN
		SELECT INTO v_is_manager ma_is_manager FROM all_tb_mbracc WHERE ma_idx = v_ma_idx;
	ELSIF v_access = 'IDC' THEN
		SELECT INTO v_is_manager ma_is_manager FROM idc_tb_mbracc WHERE ma_idx = v_ma_idx;
	ELSIF v_access = 'MEP' THEN
		SELECT INTO v_is_manager ma_is_manager FROM idc_tb_mbracc WHERE ma_idx = v_ma_idx;
	ELSIF v_access = 'MED' THEN
		SELECT INTO v_is_manager ma_is_manager FROM med_tb_mbracc WHERE ma_idx = v_ma_idx;
	END IF;

	IF v_is_manager is TRUE THEN
		v_value := 'MANAGER';
	ELSIF v_is_manager is FALSE THEN
		v_value := 'ADMIN';
	END IF;

	RETURN v_value;
END;
$$
;

alter function idc_getaccountgrade(varchar, integer) owner to dskim
;

create function idc_getbookdemo(v_code character varying) returns numeric
	language plpgsql
as $$
DECLARE
	v_qty numeric;
BEGIN

	SELECT INTO v_qty sum(usit_qty)
	FROM idc_tb_using_demo JOIN idc_tb_using_demo_item USING(use_code)
	WHERE it_code = v_code AND use_cfm_marketing_timestamp is null;

	if(v_qty is null) then v_qty := 0;	end if;

	RETURN v_qty;
END;
$$
;

alter function idc_getbookdemo(varchar) owner to dskim
;

create function idc_getbookedstock(v_book_idx integer, v_it_code character varying) returns numeric
	language plpgsql
as $$
DECLARE
	v_qty numeric := 0;
BEGIN
	IF v_book_idx IS NOT NULL THEN
		SELECT INTO v_qty SUM(boit_qty) FROM idc_tb_booking AS a JOIN idc_tb_booking_item USING(book_idx) WHERE it_code = v_it_code AND book_is_delivered = 'f' AND book_idx = v_book_idx;
	ELSE
		SELECT INTO v_qty SUM(boit_qty) FROM idc_tb_booking AS a JOIN idc_tb_booking_item USING(book_idx) WHERE it_code = v_it_code AND book_is_delivered = 'f';
	END IF;

	IF v_qty <= 0 OR v_qty is null THEN
		v_qty := 0;
	END IF;

	RETURN v_qty;
END;
$$
;

alter function idc_getbookedstock(integer, varchar) owner to dskim
;

create function idc_getcategorypath(v_midx integer) returns character varying
	language plpgsql
as $$
DECLARE
	r_cat RECORD;
	v_idx integer := 0;
	v_return varchar := '';
BEGIN
	v_idx = v_midx;
	LOOP
		SELECT INTO r_cat * FROM idc_tb_item_cat WHERE icat_midx = v_idx;
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
$$
;

alter function idc_getcategorypath(integer) owner to dskim
;

create function idc_getcategorypathitem(v_it_code character varying, v_depth integer) returns character varying
	language plpgsql
as $$
DECLARE
    v_midx integer;
    r_cat RECORD;
    v_idx integer := 0;
    v_return varchar := '';
BEGIN

    SELECT INTO v_midx icat_midx FROM idc_tb_item WHERE it_code = v_it_code;

    v_idx = v_midx;
    LOOP
        SELECT INTO r_cat * FROM idc_tb_item_cat WHERE icat_midx = v_idx;
        IF NOT FOUND THEN
            EXIT;
        ELSE

            v_return :=  r_cat.icat_name || ', ' || v_return;
            EXIT WHEN r_cat.icat_depth = v_depth;
            v_return := v_return;
            v_idx := r_cat.icat_pidx;


        END IF;
    END LOOP;
    RETURN v_return;
END;
$$
;

alter function idc_getcategorypathitem(varchar, integer) owner to dskim
;

create function idc_getcategorypathitem2(v_it_code character varying, v_depth integer) returns character varying
	language plpgsql
as $$
DECLARE
    v_midx integer;
    r_cat RECORD;
    v_idx integer := 0;
    v_return varchar := '';
BEGIN

    SELECT INTO v_midx icat_midx FROM med_tb_item WHERE it_code = v_it_code;

    v_idx = v_midx;
    LOOP
        SELECT INTO r_cat * FROM med_tb_item_cat WHERE icat_midx = v_idx;
        IF NOT FOUND THEN
            EXIT;
        ELSE

            IF r_cat.icat_depth = 1 AND v_depth = 0 THEN
                v_return := r_cat.icat_name;
                exit;
            END IF;

            v_return :=  r_cat.icat_name || ', ' || v_return;
            EXIT WHEN r_cat.icat_depth = v_depth;
            v_return := v_return;
            v_idx := r_cat.icat_pidx;


        END IF;
    END LOOP;
    RETURN v_return;
END;
$$
;

alter function idc_getcategorypathitem2(varchar, integer) owner to dskim
;

create function idc_getcurrentbillcode(v_source character varying, v_ordered_by integer, v_vat integer, v_dept character varying, v_inv_date date, v_type_pajak character varying) returns character varying
	language plpgsql
as $$
DECLARE
    v_current_month varchar[] := ARRAY['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
    v_month integer := extract(MONTH FROM v_inv_date) + 0;
    v_monyy varchar := v_current_month[v_month] || to_char(v_inv_date, 'YY');
    v_new_code varchar;
    v_serial integer;
    v_tax varchar;
    v_init varchar;
BEGIN

    IF v_source = 'IDC' THEN
        IF v_ordered_by = 1 THEN
            v_init := 'I';
            IF v_vat = 0.00 THEN
                v_tax := 'M';
                SELECT INTO v_serial max(substr(bill_code, 4, 5))
                FROM idc_tb_billing WHERE bill_ordered_by=v_ordered_by AND substr(bill_code, 2, 1) = 'M' AND substr(bill_code, 9, 1) = v_dept AND substr(bill_code, 11, 3) = v_monyy;
            ELSIF v_vat != 0.00 THEN
                IF v_type_pajak = 'IO' THEN
                    v_tax := 'O';
                    SELECT INTO v_serial max(substr(bill_code, 4, 5))
                    FROM idc_tb_billing WHERE bill_ordered_by=v_ordered_by AND  substr(bill_code, 2, 1) = 'O' AND substr(bill_code, 12, 2) = substr(v_monyy, 2, 2);
                ELSIF v_type_pajak = 'IP' THEN
                    v_tax := 'P';
                    SELECT INTO v_serial max(substr(bill_code, 4, 5))
                    FROM idc_tb_billing WHERE bill_ordered_by=v_ordered_by AND  substr(bill_code, 2, 1) = 'P' AND substr(bill_code, 11, 3) = v_monyy;
                END IF;
            END IF;
        ELSIF v_ordered_by = 2 THEN
            v_init := 'M';
            IF v_vat = 0.00 THEN
                v_tax := 'N';
                SELECT INTO v_serial max(substr(bill_code, 4, 5))
                FROM idc_tb_billing WHERE bill_ordered_by=v_ordered_by AND substr(bill_code, 2, 1) = 'N' AND substr(bill_code, 9, 1) = v_dept AND substr(bill_code, 11, 3) = v_monyy;
            ELSIF v_vat != 0.00 THEN
                IF v_type_pajak = 'IO' THEN
                    v_tax := 'F';
                    SELECT INTO v_serial max(substr(bill_code, 4, 5))
                    FROM idc_tb_billing WHERE bill_ordered_by=v_ordered_by AND  substr(bill_code, 2, 1) = 'F' AND substr(bill_code, 12, 2) = substr(v_monyy, 2, 2);
                ELSIF v_type_pajak = 'IP' THEN
                    v_tax := 'S';
                    SELECT INTO v_serial max(substr(bill_code, 4, 5))
                    FROM idc_tb_billing WHERE bill_ordered_by=v_ordered_by AND  substr(bill_code, 2, 1) = 'S' AND substr(bill_code, 11, 3) = v_monyy;
                END IF;
            END IF;
        END IF;
    ELSIF v_source = 'MED' THEN
        IF v_ordered_by = 1 THEN
            v_init := 'B';
            IF v_vat = 0.00 THEN
                v_tax := 'N';
                SELECT INTO v_serial max(substr(bill_code, 4, 5))
                FROM idc_tb_billing WHERE bill_ordered_by=v_ordered_by AND substr(bill_code, 2, 1) = 'N' AND substr(bill_code, 9, 1) = v_dept AND substr(bill_code, 11, 3) = v_monyy;
            ELSIF v_vat != 0.00 THEN
                IF v_type_pajak = 'IO' THEN
                    v_tax := 'O';
                    SELECT INTO v_serial max(substr(bill_code, 4, 5))
                    FROM idc_tb_billing WHERE bill_ordered_by=v_ordered_by AND  substr(bill_code, 2, 1) = 'O' AND substr(bill_code, 12, 2) = substr(v_monyy, 2, 2);
                ELSIF v_type_pajak = 'IP' THEN
                    v_tax := 'P';
                    SELECT INTO v_serial max(substr(bill_code, 4, 5))
                    FROM idc_tb_billing WHERE bill_ordered_by=v_ordered_by AND  substr(bill_code, 2, 1) = 'P' AND substr(bill_code, 11, 3) = v_monyy;
                END IF;
            END IF;
        END IF;
    END IF;

    IF v_serial IS NULL THEN
        v_serial := 1;
    ELSE
        v_serial := v_serial + 1;
    END IF;

    v_new_code := v_init || v_tax || '-' || lpad(v_serial::text, 5, '0') || v_dept || '-' || v_monyy;

    RETURN v_new_code;
END;
$$
;

alter function idc_getcurrentbillcode(varchar, integer, integer, varchar, date, varchar) owner to dskim
;

create function idc_getcurrentcscode() returns character varying
	language plpgsql
as $$
DECLARE
    v_year integer := substr(extract(YEAR FROM current_date)::text,3,2);
    v_serial integer;
    v_cus_code varchar;
BEGIN

    SELECT INTO v_serial max(substr(cus_code, 4, 4)::integer)
    FROM idc_tb_customer
    WHERE
      substr(cus_code, 2, 2) = lpad(v_year::text, 2, '0')
      AND substr(cus_code,1,1) = 'S' AND cus_channel = '00S';

    IF v_serial IS NULL THEN
        v_serial := 1;
    ELSE
        v_serial := v_serial + 1;
    END IF;

    v_cus_code := 'S' || lpad(v_year::text, 2, '0') || lpad(v_serial::text, 4, '0');

    RETURN v_cus_code;
END;
$$
;

alter function idc_getcurrentcscode() owner to dskim
;

create function idc_getcurrentdatestatus(v_idx integer, v_status integer) returns character varying
	language plpgsql
as $$
DECLARE
	v_date varchar;
BEGIN

	IF v_status = 0 THEN
		SELECT INTO v_date to_char(sgit_incoming_date, 'dd-Mon-YY') FROM idc_tb_service_reg_item WHERE sgit_idx=v_idx;
	ELSIF v_status = 1 THEN
		SELECT INTO v_date to_char(sgit_finishing_date, 'dd-Mon-YY') FROM idc_tb_service_reg_item WHERE sgit_idx=v_idx;
	ELSIF v_status = 2 THEN
		SELECT INTO v_date to_char(sgit_delivery_date, 'dd-Mon-YY') FROM idc_tb_service_reg_item WHERE sgit_idx=v_idx;
	END IF;

	RETURN v_date;
END;
$$
;

alter function idc_getcurrentdatestatus(integer, integer) owner to dskim
;

create function idc_getcurrentdfcode(v_dept character varying, v_date date) returns character varying
	language plpgsql
as $$
DECLARE
	v_current_month varchar[] := ARRAY['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
	v_month integer := extract(MONTH FROM v_date) + 0;
	v_monyy varchar := v_current_month[v_month] || to_char(v_date, 'YY');
	v_new_code varchar;
	v_serial integer;
BEGIN
	SELECT INTO v_serial max(substr(df_code, 4, 3)) FROM idc_tb_df
	WHERE substr(df_code, 7, 1) = v_dept AND substr(df_code, 10, 2) = substr(v_monyy,2,2);

	IF v_serial IS NULL THEN
		v_serial := 1;
	ELSE
		v_serial := v_serial + 1;
	END IF;

	v_new_code := 'DF-' || lpad(v_serial::text, 3, '0') || v_dept || '-' || v_monyy;

	RETURN v_new_code;
END;
$$
;

alter function idc_getcurrentdfcode(varchar, date) owner to dskim
;

create function idc_getcurrentdrcode(v_dept character varying, v_ordered_by integer, v_date date) returns character varying
	language plpgsql
as $$
DECLARE
	v_current_month varchar[] := ARRAY['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
	v_month integer := extract(MONTH FROM v_date) + 0;
	v_monyy varchar := v_current_month[v_month] || to_char(v_date, 'YY');
	v_new_code varchar;
	v_serial integer;
BEGIN

	IF v_ordered_by = 1 THEN
		SELECT INTO v_serial max(substr(dr_code, 4, 3)) FROM idc_tb_dr
		WHERE dr_ordered_by= 1 AND substr(dr_code, 7, 1) = v_dept AND substr(dr_code, 10, 2) = substr(v_monyy,2,2);
	ELSIF v_ordered_by = 2 THEN
		SELECT INTO v_serial max(substr(dr_code, 5, 2)) FROM idc_tb_dr
		WHERE dr_ordered_by= 2 AND substr(dr_code, 7, 1) = v_dept AND substr(dr_code, 10, 2) = substr(v_monyy,2,2);
	END IF;

	IF v_serial IS NULL THEN
		v_serial := 1;
	ELSE
		v_serial := v_serial + 1;
	END IF;

	IF v_ordered_by = 1 THEN
		v_new_code := 'DR-' || lpad(v_serial::text, 3, '0') || v_dept || '-' || v_monyy;
	ELSIF v_ordered_by = 2 THEN
		v_new_code := 'DR-M' || lpad(v_serial::text, 2, '0') || v_dept || '-' || v_monyy;
	END IF;

	RETURN v_new_code;
END;
$$
;

alter function idc_getcurrentdrcode(varchar, integer, date) owner to dskim
;

create function idc_getcurrentdtcode(v_dept character varying, v_date date) returns character varying
	language plpgsql
as $$
DECLARE
	v_current_month varchar[] := ARRAY['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
	v_month integer := extract(MONTH FROM v_date) + 0;
	v_monyy varchar := v_current_month[v_month] || to_char(v_date, 'YY');
	v_new_code varchar;
	v_serial integer;
BEGIN
	SELECT INTO v_serial max(substr(dt_code, 4, 3)) FROM idc_tb_dt
	WHERE substr(dt_code, 7, 1) = v_dept AND substr(dt_code, 10, 2) = substr(v_monyy,2,2);

	IF v_serial IS NULL THEN
		v_serial := 1;
	ELSE
		v_serial := v_serial + 1;
	END IF;

	v_new_code := 'DT-' || lpad(v_serial::text, 3, '0') || v_dept || '-' || v_monyy;

	RETURN v_new_code;
END;
$$
;

alter function idc_getcurrentdtcode(varchar, date) owner to dskim
;

create function idc_getcurrentletterno(v_source character varying, v_dept character, v_reg_date date, v_type_letter character varying) returns character varying
	language plpgsql
as $$
DECLARE
	v_current_month varchar[] := ARRAY['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
	v_month integer := extract(MONTH FROM v_reg_date) + 0;
	v_monyy varchar := v_current_month[v_month] || to_char(v_reg_date, 'YY');
	v_new_code varchar;
	v_serial integer;
BEGIN

	IF v_source = 'IDC' THEN
		SELECT INTO v_serial max(substr(lt_reg_no, 1, 4)) FROM idc_tb_letter WHERE substr(lt_reg_no, 10, 1) = v_type_letter AND substr(lt_reg_no, 13, 2) = to_char(v_reg_date, 'YY');
		IF v_serial IS NULL THEN v_serial := 1001;
		ELSE v_serial := v_serial + 1;
		END IF;
		v_new_code := lpad(v_serial::text, 4, '0') || v_dept || '/IP/' || v_type_letter || '/' || v_monyy;
	ELSIF v_source = 'MED' THEN
		SELECT INTO v_serial max(substr(lt_reg_no, 1, 4)) FROM med_tb_letter WHERE substr(lt_reg_no, 10, 1) = v_type_letter AND substr(lt_reg_no, 13, 2) = to_char(v_reg_date, 'YY');
		IF v_serial IS NULL THEN v_serial := 1001;
		ELSE v_serial := v_serial + 1;
		END IF;
		v_new_code := lpad(v_serial::text, 4, '0') || v_dept || '/MB/' || v_type_letter || '/' || v_monyy;
	END IF;

	RETURN v_new_code;
END;
$$
;

alter function idc_getcurrentletterno(varchar, char, date, varchar) owner to dskim
;

create function idc_getcurrentordcode(v_source character varying, v_dept character varying, v_type character varying, v_po_date date) returns character varying
	language plpgsql
as $$
DECLARE
	v_current_month varchar[] := ARRAY['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
	v_month integer := extract(MONTH FROM v_po_date) + 0;
	v_monyy varchar := v_current_month[v_month] || to_char(v_po_date, 'YY');
	v_new_code varchar;	v_serial integer;
BEGIN

	SELECT INTO v_serial max(substr(ord_code, 5, 4)) FROM idc_tb_order WHERE ord_dept = v_dept AND substr(ord_code, 10,3) = v_monyy;

	IF v_serial IS NULL THEN
		v_serial := 1;	ELSE
		v_serial := v_serial + 1;
	END IF;

	IF v_source = 'IDC' THEN
		IF v_dept = 'A' THEN
			IF v_type = 'OK' THEN
				v_new_code := 'OK-O' || lpad(v_serial::text, 4, '0') || '-' || v_monyy;
			ELSE
				v_new_code := 'OO-A' || lpad(v_serial::text, 4, '0') || '-' || v_monyy;
			END IF;
		ELSE
			IF v_type = 'OK' THEN
				v_new_code := 'OK-' || v_dept || lpad(v_serial::text, 4, '0') || '-' || v_monyy;
			ELSE
				v_new_code := 'OO-' || v_dept || lpad(v_serial::text, 4, '0') || '-' || v_monyy;
			END IF;
		END IF;
	ELSIF v_source = 'MED' THEN
		IF v_type = 'OK' THEN
			v_new_code := 'MK-' || v_dept || lpad(v_serial::text, 4, '0') || '-' || v_monyy;
		ELSE
			v_new_code := 'MO-' || v_dept || lpad(v_serial::text, 4, '0') || '-' || v_monyy;
		END IF;
	END IF;

	RETURN v_new_code;
END;
$$
;

alter function idc_getcurrentordcode(varchar, varchar, varchar, date) owner to dskim
;

create function idc_getcurrentordreturncode(v_source character varying, v_dept character varying, v_po_date date, v_type character varying) returns character varying
	language plpgsql
as $$
DECLARE
	v_current_month varchar[] := ARRAY['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
	v_month integer := extract(MONTH FROM v_po_date) + 0;
	v_monyy varchar := v_current_month[v_month] || to_char(v_po_date, 'YY');
	v_new_code varchar;
	v_serial integer;
BEGIN

	SELECT INTO v_serial max(substr(reor_code, 5, 3)) FROM idc_tb_return_order WHERE substr(reor_code, 10,3) = v_monyy AND reor_dept = v_dept;

	IF v_serial IS NULL THEN
		v_serial := 1;
	ELSE
		v_serial := v_serial + 1;
	END IF;

	IF v_dept = 'A' THEN
		IF v_type = 'RO' THEN
			v_new_code := 'RO-A' || lpad(v_serial::text, 3, '0') || 'A-' || v_monyy;
		ELSIF v_type = 'RK' THEN
			v_new_code := 'RK-O' || lpad(v_serial::text, 3, '0') || 'A-' || v_monyy;
		END IF;
	ELSE
		IF v_type = 'RO' THEN
			v_new_code := 'RO-' || v_dept || lpad(v_serial::text, 3, '0') || 'A-' || v_monyy;
		ELSIF v_type = 'RK' THEN
			v_new_code := 'RK-' || v_dept || lpad(v_serial::text, 3, '0') || 'A-' || v_monyy;
		END IF;
	END IF;

	RETURN v_new_code;
END;
$$
;

alter function idc_getcurrentordreturncode(varchar, varchar, date, varchar) owner to dskim
;

create function idc_getcurrentpesertacode(v_date date) returns character varying
	language plpgsql
as $$
DECLARE
	v_year varchar := to_char(v_date, 'YY');
	v_new_code varchar;
	v_serial integer;
BEGIN

	SELECT INTO v_serial max(substr(evp_code, 1, 5)) FROM idc_tb_event_peserta WHERE substr(evp_code, 6, 2) = v_year;

	IF v_serial IS NULL THEN v_serial := 1;
	ELSE 					 v_serial := v_serial + 1; END IF;

	v_new_code := lpad(v_serial::text, 5, '0') || v_year;

	RETURN v_new_code;

END;
$$
;

alter function idc_getcurrentpesertacode(date) owner to dskim
;

create function idc_getcurrentpocode(v_source character varying, v_ordered_by integer, v_po_type integer, v_po_date date, v_currency_type integer) returns character varying
	language plpgsql
as $$
DECLARE
    v_year varchar := substr(extract(YEAR FROM v_po_date)::text,3,2);
    v_initial_code varchar;
    v_new_code varchar;
    v_serial integer;
BEGIN

    IF v_source = 'IDC' THEN
        IF v_currency_type = 1 THEN
            IF v_po_type = 1 THEN
                SELECT INTO v_serial max(substr(po_code, 1, 2)) FROM idc_tb_po WHERE substr(po_code, 4, 2) = 'IP' AND substr(po_code, 7, 2) = v_year;
                v_initial_code = 'IP';
            ELSIF v_po_type = 2 THEN
                SELECT INTO v_serial max(substr(po_code, 1, 2)) FROM idc_tb_po WHERE substr(po_code, 4, 3) = 'ICP' AND substr(po_code, 8, 2) = v_year;
                v_initial_code = 'ICP';
            END IF;
        ELSIF v_currency_type = 2 THEN
            SELECT INTO v_serial max(substr(po_code, 1, 2)) FROM idc_tb_po WHERE substr(po_code, 4, 3) = 'IPL' AND substr(po_code, 8, 2) = v_year;
            v_initial_code = 'IPL';
        END IF;
    ELSIF v_source = 'MED' THEN
        IF v_ordered_by = 1 THEN
            IF v_po_type = 1 THEN
                SELECT INTO v_serial max(substr(po_code, 1, 2)) FROM med_tb_po WHERE substr(po_code, 4, 4) = 'M-IP' AND substr(po_code, 9, 2) = v_year;
                v_initial_code = 'M-IP';
            ELSIF v_po_type = 2 THEN
                SELECT INTO v_serial max(substr(po_code, 1, 2)) FROM med_tb_po WHERE substr(po_code, 4, 5) = 'M-ICP' AND substr(po_code, 10, 2) = v_year;
                IF v_serial >= 99 THEN
                    SELECT INTO v_serial max(substr(po_code, 1, 3)) FROM med_tb_po WHERE substr(po_code, 5, 5) = 'M-ICP' AND substr(po_code, 11, 2) = v_year;
                END IF;
                v_initial_code = 'M-ICP';
            END IF;
        ELSIF v_ordered_by = 2 THEN
            IF v_po_type = 1 THEN
                SELECT INTO v_serial max(substr(po_code, 1, 2)) FROM med_tb_po WHERE substr(po_code, 4, 4) = 'S-IP' AND substr(po_code, 9, 2) = v_year;
                v_initial_code = 'S-IP';
            ELSIF v_po_type = 2 THEN
                SELECT INTO v_serial max(substr(po_code, 1, 2)) FROM med_tb_po WHERE substr(po_code, 4, 5) = 'S-ICP' AND substr(po_code, 10, 2) = v_year;
                v_initial_code = 'S-ICP';
            END IF;
        END IF;
    END IF;

    IF v_serial IS NULL THEN v_serial := 1;
    ELSE v_serial := v_serial + 1;
    END IF;

    IF v_serial > 99 THEN
        v_new_code := lpad(v_serial::text, 3, '0') || '/' || v_initial_code || '/' || v_year::text;
    ELSE
        v_new_code := lpad(v_serial::text, 2, '0') || '/' || v_initial_code || '/' || v_year::text;
    END IF;

    RETURN v_new_code;
END;
$$
;

alter function idc_getcurrentpocode(varchar, integer, integer, date, integer) owner to dskim
;

create function idc_getcurrentpolocalcode(v_sp_code character varying, v_po_date date) returns character varying
	language plpgsql
as $$
DECLARE
	v_year varchar			:= extract(YEAR FROM v_po_date)::text;
	v_serial_date varchar	:= lpad(extract(MONTH FROM v_po_date)::text,2 , '0') || '-' || v_year;
	v_serial integer;
	v_new_code varchar;
BEGIN

	SELECT INTO v_serial max(substr(po_code, 5, 3)) FROM idc_tb_po_local WHERE substr(po_code, 12, 4) = v_year;

	IF v_serial IS NULL THEN
		v_serial := 1;
	ELSE
		v_serial := v_serial + 1;
	END IF;

	v_new_code := 'IND-' || lpad(v_serial::text, 3, '0') || '-' || v_serial_date || '-' || v_sp_code;

	RETURN v_new_code;
END;
$$
;

alter function idc_getcurrentpolocalcode(varchar, date) owner to dskim
;

create function idc_getcurrentrequestcode(v_source character varying, v_issued_date date) returns character varying
	language plpgsql
as $$
DECLARE
	v_current_month varchar[] := ARRAY['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
	v_month integer := extract(MONTH FROM v_issued_date) + 0;
	v_monyy varchar := v_current_month[v_month] || to_char(v_issued_date, 'YY');
	v_new_code varchar;
	v_serial integer;
BEGIN

	SELECT INTO v_serial max(substr(req_code, 4, 3)) FROM idc_tb_request
	WHERE substr(req_code, 10, 2) = substr(v_monyy,2,2);

	IF v_serial IS NULL THEN
		v_serial := 1;
	ELSE
		v_serial := v_serial + 1;
	END IF;

	IF v_source = 'IDC' THEN
		v_new_code := 'DM-' || lpad(v_serial::text, 3, '0') || 'M-' || v_monyy;
	ELSIF v_source = 'MED' THEN
		v_new_code := 'MM-' || lpad(v_serial::text, 3, '0') || 'M-' || v_monyy;
	END IF;

	RETURN v_new_code;
END;
$$
;

alter function idc_getcurrentrequestcode(varchar, date) owner to dskim
;

create function idc_getcurrentrequestdemocode(v_source character varying, v_dept character varying, v_issued_date date) returns character varying
	language plpgsql
as $$
DECLARE
	v_current_month varchar[] := ARRAY['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
	v_month integer := extract(MONTH FROM v_issued_date) + 0;
	v_monyy varchar := v_current_month[v_month] || to_char(v_issued_date, 'YY');
	v_new_code varchar;
	v_serial integer;
BEGIN

	SELECT INTO v_serial max(substr(use_code, 4, 3)) FROM idc_tb_using_demo
	WHERE substr(use_code, 10, 2) = substr(v_monyy,2,2);

	IF v_serial IS NULL THEN
		v_serial := 1;
	ELSE
		v_serial := v_serial + 1;
	END IF;

	IF v_source = 'IDC' THEN
		v_new_code := 'QO-' || lpad(v_serial::text, 3, '0') || v_dept || '-' || v_monyy;
	ELSIF v_source = 'MED' THEN
		v_new_code := 'MD-' || lpad(v_serial::text, 3, '0') || v_dept || '-' || v_monyy;
	END IF;

	RETURN v_new_code;
END;
$$
;

alter function idc_getcurrentrequestdemocode(varchar, varchar, date) owner to dskim
;

create function idc_getcurrentreturncode(v_vat integer, v_dept character varying, v_return_date date, v_type_return character varying) returns character varying
	language plpgsql
as $$
DECLARE
	v_current_month varchar[] := ARRAY['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
	v_month integer := extract(MONTH FROM v_return_date) + 0;
	v_monyy varchar := v_current_month[v_month] || to_char(v_return_date, 'YY');
	v_new_code varchar;
	v_serial integer;
	v_tax varchar;
BEGIN

	SELECT INTO v_serial max(substr(turn_code, 4, 3))
	FROM idc_tb_return WHERE substr(turn_code, 7, 1) = v_dept AND substr(turn_code, 9, 1) = substr(v_monyy,1,1);

	--GET SERIAL NUMBER
	IF v_serial IS NULL THEN v_serial := 1;
	ELSE v_serial := v_serial + 1; END IF;

	IF v_vat > 0 THEN v_tax := 'O';
	ELSE v_tax := 'X'; END IF;

	--GET RETURN CODE
	IF v_type_return = 'RO' THEN
		v_new_code := 'R' || v_tax || '-' || lpad(v_serial, 3, '0') || v_dept || '-' || v_monyy;
	ELSIF v_type_return = 'RR' THEN
		v_new_code := 'RR-' || lpad(v_serial, 3, '0') || v_dept || '-' || v_monyy;
	END IF;

RETURN v_new_code;
END;
$$
;

alter function idc_getcurrentreturncode(integer, varchar, date, varchar) owner to dskim
;

create function idc_getcurrentreturncode(v_order_by integer, v_vat integer, v_dept character varying, v_return_date date, v_type_return character varying) returns character varying
	language plpgsql
as $$
DECLARE
	v_current_month varchar[] := ARRAY['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
	v_month integer := extract(MONTH FROM v_return_date) + 0;
	v_monyy varchar := v_current_month[v_month] || to_char(v_return_date, 'YY');
	v_new_code varchar;
	v_serial integer;
	v_tax varchar;
BEGIN

	IF v_order_by = 1 THEN
		SELECT INTO v_serial max(substr(turn_code, 4, 3))
		FROM idc_tb_return WHERE turn_ordered_by=1 AND substr(turn_code, 7, 1) = v_dept AND substr(turn_code, 9, 1) = substr(v_monyy,1,1);

		--GET SERIAL NUMBER
		IF v_serial IS NULL THEN v_serial := 1;
		ELSE v_serial := v_serial + 1;
		END IF;

		IF v_vat > 0 THEN v_tax := 'O';
		ELSE v_tax := 'X';
		END IF;

		--GET RETURN CODE
		IF v_type_return = 'RO' THEN
			v_new_code := 'R' || v_tax || '-' || lpad(v_serial::text, 3, '0') || v_dept || '-' || v_monyy;
		ELSIF v_type_return = 'RR' THEN
			v_new_code := 'RR-' || lpad(v_serial::text, 3, '0') || v_dept || '-' || v_monyy;
		END IF;

	ELSIF v_order_by = 2 THEN
		SELECT INTO v_serial max(substr(turn_code, 5, 2))
		FROM idc_tb_return WHERE turn_ordered_by=2 AND substr(turn_code, 7, 1) = v_dept AND substr(turn_code, 9, 1) = substr(v_monyy,1,1);

		--GET SERIAL NUMBER
		IF v_serial IS NULL THEN v_serial := 1;
		ELSE v_serial := v_serial + 1;
		END IF;

		IF v_vat > 0 THEN v_tax := 'O';
		ELSE v_tax := 'X';
		END IF;

		--GET RETURN CODE
		IF v_type_return = 'RO' THEN
			v_new_code := 'R' || v_tax || '-M' || lpad(v_serial::text, 2, '0') || v_dept || '-' || v_monyy;
		ELSIF v_type_return = 'RR' THEN
			v_new_code := 'RR-M' || lpad(v_serial::text, 2, '0') || v_dept || '-' || v_monyy;
		END IF;
	END IF;

	RETURN v_new_code;
END;
$$
;

alter function idc_getcurrentreturncode(integer, integer, varchar, date, varchar) owner to dskim
;

create function idc_getcurrentreturndemocode(v_source character varying, v_dept character varying, v_issued_date date) returns character varying
	language plpgsql
as $$
DECLARE
	v_current_month varchar[] := ARRAY['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
	v_month integer := extract(MONTH FROM v_issued_date) + 0;
	v_monyy varchar := v_current_month[v_month] || to_char(v_issued_date, 'YY');
	v_new_code varchar;
	v_serial integer;
BEGIN

	SELECT INTO v_serial max(substr(red_code, 4, 3)) FROM idc_tb_return_demo
	WHERE substr(red_code, 10, 2) = substr(v_monyy,2,2);

	IF v_serial IS NULL THEN
		v_serial := 1;
	ELSE
		v_serial := v_serial + 1;
	END IF;

	IF v_source = 'IDC' THEN
		v_new_code := 'RQ-' || lpad(v_serial::text, 3, '0') || v_dept || '-' || v_monyy;
	ELSIF v_source = 'MED' THEN
		v_new_code := 'RD-' || lpad(v_serial::text, 3, '0') || v_dept || '-' || v_monyy;
	END IF;

	RETURN v_new_code;
END;
$$
;

alter function idc_getcurrentreturndemocode(varchar, varchar, date) owner to dskim
;

create function idc_getcurrentreturndtcode(v_dept character varying, v_date date) returns character varying
	language plpgsql
as $$
DECLARE
	v_current_month varchar[] := ARRAY['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
	v_month integer := extract(MONTH FROM v_date) + 0;
	v_monyy varchar := v_current_month[v_month] || to_char(v_date, 'YY');
	v_new_code varchar;
	v_serial integer;
BEGIN
	SELECT INTO v_serial max(substr(rdt_code, 4, 3)) FROM idc_tb_return_dt
	WHERE substr(rdt_code, 7, 1) = v_dept AND substr(rdt_code, 9, 3) = v_monyy;

	IF v_serial IS NULL THEN
		v_serial := 1;
	ELSE
		v_serial := v_serial + 1;
	END IF;

	v_new_code := 'RT-' || lpad(v_serial::text, 3, '0') || v_dept || '-' || v_monyy;

	RETURN v_new_code;
END;
$$
;

alter function idc_getcurrentreturndtcode(varchar, date) owner to dskim
;

create function idc_getcurrentservicecode(v_date date) returns character varying
	language plpgsql
as $$
DECLARE
	v_year integer := substr(extract(YEAR FROM v_date)::text,3,2);
	v_month integer := extract(MONTH FROM v_date);
	v_serial integer;
	v_new_code varchar;
BEGIN

	SELECT INTO v_serial max(substr(sv_code, 7, 3)) FROM idc_tb_service WHERE substr(sv_code, 3, 2) = lpad(v_year::text, 2, '0');

	IF v_serial IS NULL THEN
		v_serial := 1;
	ELSE
		v_serial := v_serial + 1;
	END IF;

	v_new_code := 'NT' || lpad(v_year::text, 2, '0') || lpad(v_month::text, 2, '0') || lpad(v_serial::text, 3, '0');

	RETURN v_new_code;
END;
$$
;

alter function idc_getcurrentservicecode(date) owner to dskim
;

create function idc_getcurrentserviceregcode(v_date date) returns character varying
	language plpgsql
as $$
DECLARE
	v_year integer := substr(extract(YEAR FROM v_date)::text,3,2);
	v_month integer := extract(MONTH FROM v_date);
	v_serial integer;
	v_new_code varchar;
BEGIN

	SELECT INTO v_serial max(substr(sg_code, 6, 3)) FROM idc_tb_service_reg
	WHERE substr(sg_code, 2, 2) = lpad(v_year::text, 2, '0') AND substr(sg_code, 4, 2) = lpad(v_month::text, 2, '0');

	IF v_serial IS NULL THEN
		v_serial := 1;
	ELSE
		v_serial := v_serial + 1;
	END IF;

	v_new_code := 'S' || lpad(v_year::text, 2, '0') || lpad(v_month::text, 2, '0') || lpad(v_serial::text, 3, '0');

	RETURN v_new_code;
END;
$$
;

alter function idc_getcurrentserviceregcode(date) owner to dskim
;

create function idc_getdemoqty(v_activity integer, v_it_code character varying) returns numeric
	language plpgsql
as $$
DECLARE
	v_qty numeric;
	v_temp_qty numeric;
BEGIN

	IF v_activity = 1 THEN
		SELECT INTO v_qty SUM(indst_qty) FROM idc_tb_incoming_demo_stock WHERE it_code = v_it_code;
	ELSIF v_activity = 2 THEN
		SELECT INTO v_qty SUM(usst_qty) FROM idc_tb_using_demo_stock WHERE it_code = v_it_code;
	ELSIF v_activity = 3 THEN
		SELECT INTO v_qty SUM(rdst_qty) FROM idc_tb_return_demo_stock WHERE it_code = v_it_code;
	ELSIF v_activity = 4 THEN
		SELECT INTO v_qty SUM(rjde_qty) FROM idc_tb_reject_demo WHERE it_code = v_it_code;
	END IF;

	IF v_qty IS NULL THEN
		v_qty = 0.00;
	END IF;

	RETURN v_qty;
END;
$$
;

alter function idc_getdemoqty(integer, varchar) owner to dskim
;

create function idc_getdetailstock(v_it_code character varying, v_condition integer, v_location integer, v_type integer) returns numeric
	language plpgsql
as $$
DECLARE
	v_temp_1_qty numeric;
	v_temp_2_qty numeric;
	v_qty numeric;
	v_rjit_idx integer;
BEGIN

	IF v_condition <= 10 THEN
		SELECT INTO v_qty idc_getStockBalance(v_it_code, v_condition, v_location, v_type);
	ELSIF v_condition > 10 AND v_condition <= 20 THEN
		SELECT INTO v_qty idc_getIncomingBalance(v_it_code, v_condition, v_location, v_type);
	ELSIF v_condition > 20 AND v_condition <= 30 THEN
		SELECT INTO v_qty idc_getOutgoingBalance(v_it_code, v_condition, v_location, v_type);
	ELSIF v_condition > 30 AND v_condition <=40 THEN
		SELECT INTO v_qty idc_getRequestBalance(v_it_code, v_condition, v_location, v_type);
	END IF;

	/* if v_qty is null then default v_qty is 0.00 */
	IF v_qty IS NULL THEN
		v_qty = 0.00;
	END IF;

	RETURN v_qty;
END;
$$
;

alter function idc_getdetailstock(varchar, integer, integer, integer) owner to dskim
;

create function idc_getdrcode(v_code character varying) returns character varying
	language plpgsql
as $$
DECLARE
	rec record;
	v_dr_code varchar := '';
BEGIN
	FOR rec IN SELECT dr_code FROM idc_tb_dr WHERE dr_turn_code = v_code ORDER BY dr_code LOOP
		v_dr_code = v_dr_code || rec.dr_code  || ', ';
	END LOOP;

	IF v_dr_code != '' THEN
		RETURN substr(v_dr_code, 1, length(v_dr_code) - 2);
	ELSE
		RETURN '';
	END IF;
END;
$$
;

alter function idc_getdrcode(varchar) owner to dskim
;

create function idc_getdueremain(v_code character varying) returns character varying
	language plpgsql
as $$
DECLARE
	v_giro_due date;
	v_remain_amount numeric;
	v_due_remain integer := 0;
BEGIN

	SELECT INTO v_giro_due bill_payment_giro_due FROM idc_tb_billing WHERE bill_code = v_code;
	SELECT INTO v_remain_amount bill_remain_amount FROM idc_tb_billing WHERE bill_code = v_code;

	IF v_remain_amount > 0 THEN
		v_due_remain = v_giro_due - CURRENT_DATE;
	END IF;

	RETURN v_due_remain;
END;
$$
;

alter function idc_getdueremain(varchar) owner to dskim
;

create function idc_getdueremaincs(v_code character varying) returns character varying
	language plpgsql
as $$
DECLARE
v_giro_due date;
v_remain_amount numeric;
v_due_remain integer := 0;
BEGIN

SELECT INTO v_giro_due sv_due_date FROM idc_tb_service WHERE sv_code = v_code;
SELECT INTO v_remain_amount sv_total_remain FROM idc_tb_service WHERE sv_code = v_code;

IF v_remain_amount > 0 THEN
v_due_remain = v_giro_due - CURRENT_DATE;
END IF;

RETURN v_due_remain;
END;
$$
;

alter function idc_getdueremaincs(varchar) owner to dskim
;

create function idc_getedqty(v_it_code character varying, v_exp_date date, v_type integer, v_loc integer) returns character varying
	language plpgsql
as $$
DECLARE
v_qty numeric;
BEGIN
	SELECT INTO v_qty SUM(e_qty) FROM idc_tb_expired_stock
	WHERE it_code = v_it_code AND e_type = v_type AND e_expired_date = v_exp_date AND e_wh_location = v_loc;
	IF v_qty IS NULL THEN
		v_qty = 0.00;
	END IF;
	RETURN v_qty;
END;
$$
;

alter function idc_getedqty(varchar, date, integer, integer) owner to dskim
;

create function idc_getemail(v_pajak_to character varying, v_ship_to character varying) returns character varying
	language plpgsql
as $$
DECLARE
	v_return varchar;
	v_email_pajak varchar;
	v_email_ship varchar;
BEGIN

    SELECT INTO v_email_pajak cus_fp_email FROM idc_tb_customer WHERE cus_code = v_pajak_to;

	SELECT INTO v_email_ship cus_fp_email FROM idc_tb_customer WHERE cus_code = v_ship_to;

	IF (substr(v_pajak_to,1,2) = '2F') THEN
		v_return := v_email_ship;
	ELSE
		v_return := v_email_pajak;
	END IF;

    RETURN v_return;
END;
$$
;

alter function idc_getemail(varchar, varchar) owner to dskim
;

create function idc_getexpiredcode(v_inpl_idx integer, v_it_code character varying) returns character varying
	language plpgsql
as $fun$
DECLARE
v_code varchar := '$$';
r_code record;
BEGIN
	FOR r_code IN SELECT it_code FROM idc_tb_expired_pl
	WHERE inpl_idx = v_inpl_idx AND it_code = v_it_code ORDER BY epl_expired_date LOOP
		v_code := v_code || r_code.it_code || '$$, $$';
	END LOOP;
	RETURN substr(v_code, 1, length(v_code) - 4);
END;
$fun$
;

alter function idc_getexpiredcode(integer, varchar) owner to dskim
;

create function idc_getexpireddate(v_inpl_idx integer, v_it_code character varying) returns character varying
	language plpgsql
as $fun$
DECLARE
v_date varchar := '$$';
r_date record;
BEGIN
	FOR r_date IN SELECT epl_expired_date FROM idc_tb_expired_pl
	WHERE inpl_idx = v_inpl_idx AND it_code = v_it_code ORDER BY epl_expired_date LOOP
		v_date := v_date || to_char(r_date.epl_expired_date,'dd-Mon-YYYY') || '$$, $$';
	END LOOP;
	RETURN substr(v_date, 1, length(v_date) - 4);
END;
$fun$
;

alter function idc_getexpireddate(integer, varchar) owner to dskim
;

create function idc_getexpireddatelay(v_inpl_idx integer, v_it_code character varying) returns character varying
	language plpgsql
as $$
DECLARE
v_date varchar := '';
r_date record;
BEGIN
	FOR r_date IN SELECT epl_expired_date FROM idc_tb_expired_pl
	WHERE inpl_idx = v_inpl_idx AND it_code = v_it_code ORDER BY epl_expired_date LOOP
		v_date := v_date || to_char(r_date.epl_expired_date,'Mon-YYYY') || ', ';
	END LOOP;
	RETURN substr(v_date, 1, length(v_date) - 2);
END;
$$
;

alter function idc_getexpireddatelay(integer, varchar) owner to dskim
;

create function idc_getexpiredqty(v_inpl_idx integer, v_it_code character varying) returns character varying
	language plpgsql
as $$
DECLARE
	v_qty varchar := '';
	r_qty record;
BEGIN
	FOR r_qty IN SELECT epl_qty FROM idc_tb_expired_pl
	WHERE inpl_idx = v_inpl_idx AND it_code = v_it_code ORDER BY epl_expired_date LOOP
		v_qty := v_qty || r_qty.epl_qty || ', ';
	END LOOP;
	RETURN substr(v_qty, 1, length(v_qty) - 2);
END;
$$
;

alter function idc_getexpiredqty(integer, varchar) owner to dskim
;

create function idc_getgopage(v_perm character varying, v_idx integer) returns character varying
	language plpgsql
as $$
DECLARE
    v_page varchar;
    v_ordered_by integer;
    rec record;
    rec2 record;
BEGIN

    IF v_perm = 'idc' THEN
        FOR rec IN SELECT * FROM idc_tb_deposit WHERE dep_idx = v_idx LOOP
            IF rec.turn_code is not null THEN
                FOR rec2 IN SELECT *  FROM idc_tb_return WHERE turn_code = rec.turn_code LOOP
                    IF rec2.turn_ordered_by = 1 AND rec2.turn_dept = 'A' THEN v_page = 'http://192.168.10.88/idc/apotik/billing/revise_return.php?_code='||rec.turn_code;
                    ELSIF rec2.turn_ordered_by = 1 AND rec2.turn_dept = 'D' THEN v_page = 'http://192.168.10.88/idc/dealer/billing/revise_return.php?_code='||rec.turn_code;
                    ELSIF rec2.turn_ordered_by = 1 AND rec2.turn_dept = 'H' THEN v_page = 'http://192.168.10.88/idc/hospital/billing/revise_return.php?_code='||rec.turn_code;
                    ELSIF rec2.turn_ordered_by = 1 AND rec2.turn_dept = 'M' THEN v_page = 'http://192.168.10.88/idc/marketing/billing/revise_return.php?_code='||rec.turn_code;
                    ELSIF rec2.turn_ordered_by = 1 AND rec2.turn_dept = 'P' THEN v_page = 'http://192.168.10.88/idc/pharmaceutical/billing/revise_return.php?_code='||rec.turn_code;
                    ELSIF rec2.turn_ordered_by = 1 AND rec2.turn_dept = 'T' THEN v_page = 'http://192.168.10.88/idc/tender/billing/revise_return.php?_code='||rec.turn_code;
                    ELSIF rec2.turn_ordered_by = 2 AND rec2.turn_dept = 'A' THEN v_page = 'http://192.168.10.88/mep/apotik/billing/revise_return.php?_code='||rec.turn_code;
                    ELSIF rec2.turn_ordered_by = 2 AND rec2.turn_dept = 'D' THEN v_page = 'http://192.168.10.88/mep/dealer/billing/revise_return.php?_code='||rec.turn_code;
                    ELSIF rec2.turn_ordered_by = 2 AND rec2.turn_dept = 'H' THEN v_page = 'http://192.168.10.88/mep/hospital/billing/revise_return.php?_code='||rec.turn_code;
                    ELSIF rec2.turn_ordered_by = 2 AND rec2.turn_dept = 'M' THEN v_page = 'http://192.168.10.88/mep/marketing/billing/revise_return.php?_code='||rec.turn_code;
                    ELSIF rec2.turn_ordered_by = 2 AND rec2.turn_dept = 'P' THEN v_page = 'http://192.168.10.88/mep/pharmaceutical/billing/revise_return.php?_code='||rec.turn_code;
                    ELSIF rec2.turn_ordered_by = 2 AND rec2.turn_dept = 'T' THEN v_page = 'http://192.168.10.88/mep/tender/billing/revise_return.php?_code='||rec.turn_code;
                    END IF;
                END LOOP;
            ELSIF rec.pay_idx is not null THEN
                v_page = 'javascript:seePaymentDetail('||rec.pay_idx||')';
            ELSE
                IF rec.dep_dept = 'A' THEN v_page = 'http://192.168.10.88/idc/apotik/billing/detail_deposit.php?_code='||rec.dep_idx;
                ELSIF rec.dep_dept = 'D' THEN v_page = 'http://192.168.10.88/idc/dealer/billing/detail_deposit.php?_code='||rec.dep_idx;
                ELSIF rec.dep_dept = 'H' THEN v_page = 'http://192.168.10.88/idc/hospital/billing/detail_deposit.php?_code='||rec.dep_idx;
                ELSIF rec.dep_dept = 'M' THEN v_page = 'http://192.168.10.88/idc/marketing/billing/detail_deposit.php?_code='||rec.dep_idx;
                ELSIF rec.dep_dept = 'P' THEN v_page = 'http://192.168.10.88/idc/pharmaceutical/billing/detail_deposit.php?_code='||rec.dep_idx;
                ELSIF rec.dep_dept = 'T' THEN v_page = 'http://192.168.10.88/idc/tender/billing/detail_deposit.php?_code='||rec.dep_idx;
                END IF;
            END IF;
        END LOOP;

    ELSIF v_perm = 'med' THEN
        FOR rec IN SELECT * FROM med_tb_deposit WHERE dep_idx = v_idx LOOP
            IF rec.turn_code is not null THEN
                FOR rec2 IN SELECT *  FROM med_tb_return WHERE turn_code = rec.turn_code LOOP
                    IF rec2.turn_dept = 'A' THEN v_page = 'http://192.168.10.88/med/apotik/billing/revise_return.php?_code='||rec.turn_code;
                    ELSIF rec2.turn_dept = 'D' THEN v_page = 'http://192.168.10.88/med/dealer/billing/revise_return.php?_code='||rec.turn_code;
                    ELSIF rec2.turn_dept = 'H' THEN v_page = 'http://192.168.10.88/med/hospital/billing/revise_return.php?_code='||rec.turn_code;
                    ELSIF rec2.turn_dept = 'M' THEN v_page = 'http://192.168.10.88/med/marketing/billing/revise_return.php?_code='||rec.turn_code;
                    ELSIF rec2.turn_dept = 'P' THEN v_page = 'http://192.168.10.88/med/pharmaceutical/billing/revise_return.php?_code='||rec.turn_code;
                    ELSIF rec2.turn_dept = 'T' THEN v_page = 'http://192.168.10.88/med/tender/billing/revise_return.php?_code='||rec.turn_code;
                    END IF;
                END LOOP;
            ELSIF rec.pay_idx is not null THEN
                v_page = 'javascript:seePaymentDetail('||rec.pay_idx||')';
            ELSE
                IF rec.dep_dept = 'A' THEN v_page = 'http://192.168.10.88/med/apotik/billing/detail_deposit.php?_code='||rec.dep_idx;
                ELSIF rec.dep_dept = 'D' THEN v_page = 'http://192.168.10.88/med/dealer/billing/detail_deposit.php?_code='||rec.dep_idx;
                ELSIF rec.dep_dept = 'H' THEN v_page = 'http://192.168.10.88/med/hospital/billing/detail_deposit.php?_code='||rec.dep_idx;
                ELSIF rec.dep_dept = 'M' THEN v_page = 'http://192.168.10.88/med/marketing/billing/detail_deposit.php?_code='||rec.dep_idx;
                ELSIF rec.dep_dept = 'P' THEN v_page = 'http://192.168.10.88/med/pharmaceutical/billing/detail_deposit.php?_code='||rec.dep_idx;
                ELSIF rec.dep_dept = 'T' THEN v_page = 'http://192.168.10.88/med/tender/billing/detail_deposit.php?_code='||rec.dep_idx;
                END IF;
            END IF;
        END LOOP;
    END IF;

RETURN v_page;
END;
$$
;

alter function idc_getgopage(varchar, integer) owner to dskim
;

create function idc_getgroupname(v_dept character varying, v_cus_code character varying) returns character varying
	language plpgsql
as $$
DECLARE
	v_group_name varchar;
	v_channel varchar;
	v_row_count integer := 0;
BEGIN

	SELECT INTO v_group_name cug_name FROM idc_tb_customer JOIN idc_tb_customer_group USING (cug_code) WHERE cus_code = v_cus_code;

	GET DIAGNOSTICS v_row_count := ROW_COUNT;
	IF v_row_count = 0 THEN
		SELECT INTO v_channel cus_channel FROM idc_tb_customer WHERE cus_code = v_cus_code;
		IF v_channel = '000' THEN     v_group_name = 'Medical Dealer';
		ELSIF v_channel = '001' THEN  v_group_name = 'Medicine Dist';
		ELSIF v_channel = '002' THEN  v_group_name = 'Pharmacy Chain';
		ELSIF v_channel = '003' THEN  v_group_name = 'Gen/ Specialty';
		ELSIF v_channel = '004' THEN  v_group_name = 'Pharmaceutical';
		ELSIF v_channel = '005' THEN  v_group_name = 'Hospital';
		ELSIF v_channel = '6.1' THEN  v_group_name = 'M/L Marketing';
		ELSIF v_channel = '6.2' THEN  v_group_name = 'Mail Order';
		ELSIF v_channel = '6.3' THEN  v_group_name = 'Internet Business';
		ELSIF v_channel = '007' THEN  v_group_name = 'Promotion & Other';
		ELSIF v_channel = '008' THEN  v_group_name = 'Individual';
		ELSIF v_channel = '009' THEN  v_group_name = 'Private use';
		END IF;
	END IF;

	RETURN v_group_name;
END;
$$
;

alter function idc_getgroupname(varchar, varchar) owner to dskim
;

create function idc_getincomingbalance(v_it_code character varying, v_condition integer, v_location integer, v_type integer) returns numeric
	language plpgsql
as $$
DECLARE
	v_temp_1_qty numeric;
	v_temp_2_qty numeric;
	v_qty numeric;
	v_rjit_idx integer;
BEGIN
/* incoming */
	/* INTIAL STOCK */
	IF v_condition = 11 THEN
		IF v_location = 0 AND v_type = 0 THEN
			select into v_qty SUM(ist_qty) FROM idc_tb_initial_indocore_stock WHERE it_code = v_it_code;
		ELSIF v_location = 0 THEN
			select into v_qty SUM(ist_qty) FROM idc_tb_initial_indocore_stock WHERE it_code = v_it_code AND ist_type = v_type;
		ELSIF v_type = 0 THEN
			select into v_qty SUM(ist_qty) FROM idc_tb_initial_indocore_stock WHERE it_code = v_it_code AND ist_wh_location = v_location;
		ELSE
			select into v_qty SUM(ist_qty) FROM idc_tb_initial_indocore_stock WHERE it_code = v_it_code AND ist_type = v_type AND ist_wh_location = v_location;
		END IF;
	/* PACKING LIST */
	ELSIF v_condition = 12 THEN
		IF v_location = 0 AND v_type = 0 THEN
			select into v_temp_1_qty SUM(init_qty) FROM idc_tb_in_pl_item WHERE it_code = v_it_code;
		ELSIF v_location = 0 THEN
			select into v_temp_1_qty SUM(init_qty) FROM idc_tb_in_pl_item WHERE it_code = v_it_code AND init_type = v_type;
		ELSIF v_type = 0 THEN
			select into v_temp_1_qty SUM(init_qty) FROM idc_tb_in_pl_item WHERE it_code = v_it_code AND init_wh_location = v_location;
		ELSE
			select into v_temp_1_qty SUM(init_qty) FROM idc_tb_in_pl_item WHERE it_code = v_it_code AND init_type = v_type AND init_wh_location = v_location;
		END IF;

		IF v_location = 0 AND v_type = 0 THEN
			select into v_temp_2_qty SUM(init_qty) FROM idc_tb_in_local_item WHERE it_code = v_it_code;
		ELSIF v_location = 0 THEN
			select into v_temp_2_qty SUM(init_qty) FROM idc_tb_in_local_item WHERE it_code = v_it_code AND init_type = v_type;
		ELSIF v_type = 0 THEN
			select into v_temp_2_qty SUM(init_qty) FROM idc_tb_in_local_item WHERE it_code = v_it_code AND init_wh_location = v_location;
		ELSE
			select into v_temp_2_qty SUM(init_qty) FROM idc_tb_in_local_item WHERE it_code = v_it_code AND init_type = v_type AND init_wh_location = v_location;
		END IF;

	IF v_temp_1_qty is null THEN v_temp_1_qty = 0; END IF;
	IF v_temp_2_qty is null THEN v_temp_2_qty = 0; END IF;
	v_qty = v_temp_1_qty + v_temp_2_qty;

	/* RETURN GOOD */
	ELSIF v_condition = 13 THEN
		IF v_location = 0 AND v_type = 0 THEN
			select into v_qty SUM(inst_qty) FROM idc_tb_incoming JOIN idc_tb_incoming_stock USING(inc_idx) WHERE inc_doc_type IN(1,2) AND it_code = v_it_code;
		ELSIF v_location = 0 THEN
			select into v_qty SUM(inst_qty) FROM idc_tb_incoming JOIN idc_tb_incoming_stock USING(inc_idx) WHERE inc_doc_type IN(1,2) AND it_code = v_it_code AND inst_type = v_type;
		ELSIF v_type = 0 THEN
			select into v_qty SUM(inst_qty) FROM idc_tb_incoming JOIN idc_tb_incoming_stock USING(inc_idx) WHERE inc_doc_type IN(1,2) AND it_code = v_it_code AND inst_wh_location = v_location;
		ELSE
			select into v_qty SUM(inst_qty) FROM idc_tb_incoming JOIN idc_tb_incoming_stock USING(inc_idx) WHERE inc_doc_type IN(1,2) AND it_code = v_it_code AND inst_type = v_type AND inst_wh_location = v_location;
		END IF;

	/* REPLACE CLAIM */
	ELSIF v_condition = 14 THEN
		IF v_location = 0 AND v_type = 0 THEN
			select into v_qty SUM(init_qty) FROM idc_tb_in_claim_item WHERE it_code = v_it_code;
		ELSIF v_location = 0 THEN
			select into v_qty SUM(init_qty) FROM idc_tb_in_claim_item WHERE it_code = v_it_code AND init_type = v_type;
		ELSIF v_type = 0 THEN
			select into v_qty SUM(init_qty) FROM idc_tb_in_claim_item WHERE it_code = v_it_code AND init_wh_location = v_location;
		ELSE
			select into v_qty SUM(init_qty) FROM idc_tb_in_claim_item WHERE it_code = v_it_code AND init_type = v_type AND init_wh_location = v_location;
		END IF;

	/*REPLACE CLAIM */
	ELSIF v_condition = 17 THEN
		IF v_location = 2 OR v_type = 1 THEN
			select into v_rjit_idx rjit_idx FROM idc_tb_reject_item WHERE it_code = null;
		ELSE
			select into v_rjit_idx rjit_idx FROM idc_tb_reject_item WHERE it_code = v_it_code AND rjit_status = 'on_stock';
		END IF;

	GET DIAGNOSTICS v_qty := ROW_COUNT;
	/* RETURN TEMPORARRY */
	ELSIF v_condition = 15 THEN
		IF v_location = 0 AND v_type = 0 THEN
			select into v_qty SUM(inst_qty) FROM idc_tb_incoming JOIN idc_tb_incoming_stock USING(inc_idx) WHERE inc_doc_type = 3 AND it_code = v_it_code;
		ELSIF v_location = 0 THEN
			select into v_qty SUM(inst_qty) FROM idc_tb_incoming JOIN idc_tb_incoming_stock USING(inc_idx) WHERE inc_doc_type = 3 AND it_code = v_it_code AND inst_type = v_type;
		ELSIF v_type = 0 THEN
			select into v_qty SUM(inst_qty) FROM idc_tb_incoming JOIN idc_tb_incoming_stock USING(inc_idx) WHERE inc_doc_type = 3 AND it_code = v_it_code AND inst_wh_location = v_location;
		ELSE
			select into v_qty SUM(inst_qty) FROM idc_tb_incoming JOIN idc_tb_incoming_stock USING(inc_idx) WHERE inc_doc_type = 3 AND it_code = v_it_code AND inst_type = v_type AND inst_wh_location = v_location;
		END IF;

	/* ENTER */
	ELSIF v_condition = 16 THEN
		IF v_location = 0 AND v_type = 0 THEN
			select into v_qty SUM(ent_qty) FROM idc_tb_borrow JOIN idc_tb_enter AS e USING(bor_idx) WHERE e.it_code = v_it_code AND bor_is_returned is false;
		ELSIF v_location = 0 THEN
			select into v_qty SUM(ent_qty) FROM idc_tb_borrow JOIN idc_tb_enter AS e USING(bor_idx) WHERE e.it_code = v_it_code AND bor_is_returned is false AND ent_type = v_type;
		ELSIF v_type = 0 THEN
			select into v_qty SUM(ent_qty) FROM idc_tb_borrow JOIN idc_tb_enter AS e USING(bor_idx) WHERE e.it_code = v_it_code AND bor_is_returned is false AND ent_wh_location = v_location;
		ELSE
			select into v_qty SUM(ent_qty) FROM idc_tb_borrow JOIN idc_tb_enter AS e USING(bor_idx) WHERE e.it_code = v_it_code AND bor_is_returned is false AND ent_type = v_type AND ent_wh_location = v_location;
		END IF;

	/* MOVE LOCATION */
	ELSIF v_condition = 19 THEN
		IF v_location = 0 AND v_type = 0 THEN
			select into v_qty SUM(mv_qty) FROM idc_tb_move_stock WHERE it_code = v_it_code;
		ELSIF v_location = 0 THEN
			select into v_qty SUM(mv_qty) FROM idc_tb_move_stock WHERE it_code = v_it_code AND mv_to_type = v_type;
		ELSIF v_type = 0 THEN
			select into v_qty SUM(mv_qty) FROM idc_tb_move_stock WHERE it_code = v_it_code AND mv_to_wh = v_location;
		ELSE
			select into v_qty SUM(mv_qty) FROM idc_tb_move_stock WHERE it_code = v_it_code AND mv_to_type = v_type AND mv_to_wh = v_location;
		END IF;
	END IF;

	RETURN v_qty;
END;
$$
;

alter function idc_getincomingbalance(varchar, integer, integer, integer) owner to dskim
;

create function idc_getinitstock(v_it_code character varying, v_type integer, v_location integer) returns numeric
	language plpgsql
as $$
DECLARE
	v_qty numeric;
BEGIN

	SELECT INTO v_qty SUM(init_qty) FROM idc_tb_initial_stock_v2
	WHERE it_code = v_it_code AND init_type = v_type AND init_wh_location = v_location;
	IF v_qty IS NULL THEN
		v_qty := 0.00;
	END IF;
	RETURN v_qty;
END;
$$
;

alter function idc_getinitstock(varchar, integer, integer) owner to dskim
;

create function idc_getinputwarranty(v_account character varying, v_date date) returns integer
	language plpgsql
as $$
DECLARE
rec record;
v_val integer := 0;
BEGIN
	FOR rec IN SELECT it_code FROM idc_tb_warranty
	WHERE wr_inputted_by_account = v_account and wr_inputted_timestamp::date=v_date LOOP
		IF rec.it_code is NOT NULL THEN
			v_val = v_val + 1;
		END IF;
	END LOOP;
	RETURN v_val;
END;
$$
;

alter function idc_getinputwarranty(varchar, date) owner to dskim
;

create function idc_getlastbalanceqty(v_it_code character varying, v_type integer, v_location integer, v_type_activity integer, v_period_from timestamp without time zone) returns numeric
	language plpgsql
as $$
DECLARE
v_qty numeric := 0.00;
v_temp_qty numeric := 0.00;
BEGIN

	/* For warehouse */
	IF v_type IS NULL THEN
		IF v_type_activity = 0 THEN
			select into v_temp_qty SUM(log_qty) FROM idc_tb_stock_logs
			where
				it_code = v_it_code AND
				log_wh_location = v_location AND
				log_cfm_timestamp <= v_period_from AND
				log_qty_status=true AND
				log_document_type in (11,12,13,14,15,16,17,18,19);

			if v_temp_qty is null then v_temp_qty := 0; end if;

			v_qty	= v_qty + v_temp_qty;
			select into v_temp_qty SUM(log_qty) FROM idc_tb_stock_logs
			where
				it_code = v_it_code AND
				log_wh_location = v_location AND
				log_cfm_timestamp <= v_period_from AND
				log_qty_status=true AND
				log_document_type in (21,22,23,24,25,26,27,28,29,30);

			if v_temp_qty is null then v_temp_qty := 0;
			else v_temp_qty := v_temp_qty*-1; end if;
			v_qty	= v_qty + v_temp_qty;

		ELSIF v_type_activity = 1 THEN
			select into v_temp_qty SUM(log_qty) FROM idc_tb_stock_logs
			where
				it_code = v_it_code AND
				log_wh_location = v_location AND
				log_cfm_timestamp <= v_period_from AND
				log_qty_status=true AND
				log_document_type in (11,12,13,14,15,16,17,18,19);

			if v_temp_qty is null then v_temp_qty := 0; end if;
			v_qty	= v_qty + v_temp_qty;

		ELSIF v_type_activity = 2 THEN
			select into v_temp_qty SUM(log_qty) FROM idc_tb_stock_logs
			where
				it_code = v_it_code AND
				log_wh_location = v_location AND
				log_cfm_timestamp <= v_period_from AND
				log_qty_status=true AND
				log_document_type in (21,22,23,24,25,26,27,28,29,30);

			if v_temp_qty is null then v_temp_qty := 0;
			else v_temp_qty := v_temp_qty*-1; end if;
			v_qty	= v_qty + v_temp_qty;

		ELSE
			select into v_temp_qty SUM(log_qty) FROM idc_tb_stock_logs
			where
				it_code = v_it_code AND
				log_wh_location = v_location AND
				log_cfm_timestamp <= v_period_from AND
				log_qty_status=true AND
				log_document_type = v_type_activity;

			if v_temp_qty is null then v_temp_qty := 0;
			elsif v_type_activity in (11,12,13,14,15,16) then v_temp_qty := v_temp_qty;
			elsif v_type_activity in (21,22,23,24,25,26,27) then v_temp_qty := v_temp_qty*-1;
			end if;
			v_qty	= v_qty + v_temp_qty;
		END IF;

	/* For purchasing */
	ELSE
		IF v_type_activity = 0 THEN
			select into v_temp_qty SUM(log_qty) FROM idc_tb_stock_logs
			where
				it_code = v_it_code AND
				log_type = v_type AND
				log_wh_location = v_location AND
				log_cfm_timestamp <= v_period_from AND
				log_qty_status=true AND
				log_document_type in (11,12,13,14,15,16,17,18,19);
				if v_temp_qty is null then v_temp_qty := 0; end if;

			v_qty	= v_qty + v_temp_qty;

			select into v_temp_qty SUM(log_qty) FROM idc_tb_stock_logs
			where
				it_code = v_it_code AND
				log_type = v_type AND
				log_wh_location = v_location AND
				log_cfm_timestamp <= v_period_from AND
				log_qty_status=true AND
				log_document_type in (21,22,23,24,25,26,27,28,29,30);

			if v_temp_qty is null then v_temp_qty := 0;
			else v_temp_qty := v_temp_qty*-1; end if;
			v_qty	= v_qty + v_temp_qty;

		ELSIF v_type_activity = 1 THEN

			select into v_temp_qty SUM(log_qty) FROM idc_tb_stock_logs
			where
				it_code = v_it_code AND
				log_type = v_type AND
				log_wh_location = v_location AND
				log_cfm_timestamp <= v_period_from AND
				log_qty_status=true AND
				log_document_type in (11,12,13,14,15,16,17,18,19);

			if v_temp_qty is null then v_temp_qty := 0; end if;
			v_qty	= v_qty + v_temp_qty;

		ELSIF v_type_activity = 2 THEN
			select into v_temp_qty SUM(log_qty) FROM idc_tb_stock_logs
			where
				it_code = v_it_code AND
				log_type = v_type AND
				log_wh_location = v_location AND
				log_cfm_timestamp <= v_period_from AND
				log_qty_status=true AND
				log_document_type in (21,22,23,24,25,26,27,28,29,30);

			if v_temp_qty is null then v_temp_qty := 0;
			else v_temp_qty := v_temp_qty*-1; end if;
			v_qty	= v_qty + v_temp_qty;

		ELSE
			select into v_temp_qty SUM(log_qty) FROM idc_tb_stock_logs
			where
			it_code = v_it_code AND
			log_type = v_type AND
			log_wh_location = v_location AND
			log_cfm_timestamp <= v_period_from AND
			log_qty_status=true AND
			log_document_type = v_type_activity;

			if v_temp_qty is null then v_temp_qty := 0;
			elsif v_type_activity in (11,12,13,14,15,16) then v_temp_qty := v_temp_qty;
			elsif v_type_activity in (21,22,23,24,25,26,27) then v_temp_qty := v_temp_qty*-1;
			end if;
			v_qty	= v_qty + v_temp_qty;

		END IF;
	END IF;

	IF v_qty IS NULL THEN
		v_qty = 0.00;
	END IF;

	RETURN v_qty;
END;
$$
;

alter function idc_getlastbalanceqty(varchar, integer, integer, integer, timestamp) owner to dskim
;

create function idc_getlastreturndate(v_code character varying, v_it_code character varying) returns date
	language plpgsql
as $$
DECLARE
    v_return_date date;
BEGIN

    SELECT INTO v_return_date red_return_date FROM idc_tb_return_demo JOIN idc_tb_return_demo_item USING(red_code)
    WHERE use_code = v_code AND it_code = v_it_code;

    RETURN v_return_date;
END;
$$
;

alter function idc_getlastreturndate(varchar, varchar) owner to dskim
;

create function idc_getlastrtcode(v_code character varying, v_it_code character varying) returns character varying
	language plpgsql
as $$
DECLARE
	v_rdt_code varchar := '';
BEGIN
	SELECT INTO v_rdt_code max(rdt_code)
	FROM idc_tb_return_dt AS a JOIN idc_tb_return_dt_item AS b USING (rdt_code)
	WHERE dt_code = v_code AND it_code = v_it_code;

	RETURN v_rdt_code;
END;
$$
;

alter function idc_getlastrtcode(varchar, varchar) owner to dskim
;

create function idc_getlastrtdate(v_code character varying, v_it_code character varying) returns date
	language plpgsql
as $$
DECLARE
	v_rdt_code varchar;
	v_rdt_date date;
BEGIN
	SELECT INTO v_rdt_code max(rdt_code)
	FROM idc_tb_return_dt AS a JOIN idc_tb_return_dt_item AS b USING (rdt_code)
	WHERE dt_code = v_code AND it_code = v_it_code;

	SELECT INTO v_rdt_date rdt_date FROM idc_tb_return_dt WHERE rdt_code = v_rdt_code;

	RETURN v_rdt_date;
END;
$$
;

alter function idc_getlastrtdate(varchar, varchar) owner to dskim
;

create function idc_getlaststocklogs(v_doc_type integer, v_doc_idx integer, v_doc_no character varying, v_it_code character varying) returns character varying
	language plpgsql
as $$
DECLARE
	v_logs_code varchar;
BEGIN

	IF v_doc_type = 12 THEN
		IF v_doc_no is not null  or v_doc_no != '' then
			SELECT INTO v_logs_code log_code FROM idc_tb_stock_logs
			WHERE log_document_type = v_doc_type AND log_document_idx = v_doc_idx AND log_document_no = v_doc_no AND it_code = v_it_code AND log_uncfm_timestamp IS NULL;
		ELSE
			SELECT INTO v_logs_code log_code FROM idc_tb_stock_logs
			WHERE log_document_type = v_doc_type AND log_document_idx = v_doc_idx AND it_code = v_it_code AND log_uncfm_timestamp IS NULL;
		END IF;
	ELSIF v_doc_type in(14,17,27) THEN
		SELECT INTO v_logs_code log_code FROM idc_tb_stock_logs
		WHERE log_document_type = v_doc_type AND log_document_idx = v_doc_idx AND it_code = v_it_code AND log_uncfm_timestamp IS NULL;
	ELSE
		SELECT INTO v_logs_code log_code FROM idc_tb_stock_logs
		WHERE log_document_type = v_doc_type AND log_document_no = v_doc_no AND it_code = v_it_code AND log_uncfm_timestamp IS NULL;
	END IF;

	RETURN v_logs_code;
END;
$$
;

alter function idc_getlaststocklogs(integer, integer, varchar, varchar) owner to dskim
;

create function idc_getoutgoingbalance(v_it_code character varying, v_condition integer, v_location integer, v_type integer) returns numeric
	language plpgsql
as $$
DECLARE
	v_temp_1_qty numeric;
	v_temp_2_qty numeric;
	v_qty numeric;
	v_rjit_idx integer;
BEGIN

	/* outgoing */
	/* OUTGOING DO */
	IF v_condition = 21 THEN
		IF v_location = 0 AND v_type = 0 THEN
			select into v_qty SUM(otst_qty) FROM idc_tb_outgoing JOIN idc_tb_outgoing_stock USING(out_idx) WHERE out_doc_type IN(1,2) AND it_code = v_it_code;
		ELSIF v_location = 0 THEN
			select into v_qty SUM(otst_qty) FROM idc_tb_outgoing JOIN idc_tb_outgoing_stock USING(out_idx) WHERE out_doc_type IN(1,2) AND it_code = v_it_code AND otst_type = v_type;
		ELSIF v_type = 0 THEN
			select into v_qty SUM(otst_qty) FROM idc_tb_outgoing JOIN idc_tb_outgoing_stock USING(out_idx) WHERE out_doc_type IN(1,2) AND it_code = v_it_code AND otst_wh_location = v_location;
		ELSE
			select into v_qty SUM(otst_qty) FROM idc_tb_outgoing JOIN idc_tb_outgoing_stock USING(out_idx) WHERE out_doc_type IN(1,2) AND it_code = v_it_code AND otst_type = v_type AND otst_wh_location = v_location;
		END IF;

	/* OUTGOING DT */
	ELSIF v_condition = 22 THEN
		IF v_location = 0 AND v_type = 0 THEN
			select into v_qty SUM(otst_qty) FROM idc_tb_outgoing JOIN idc_tb_outgoing_stock USING(out_idx) WHERE out_doc_type = 3 AND it_code = v_it_code;
		ELSIF v_location = 0 THEN
			select into v_qty SUM(otst_qty) FROM idc_tb_outgoing JOIN idc_tb_outgoing_stock USING(out_idx) WHERE out_doc_type = 3 AND it_code = v_it_code AND otst_type = v_type;
		ELSIF v_type = 0 THEN
			select into v_qty SUM(otst_qty) FROM idc_tb_outgoing JOIN idc_tb_outgoing_stock USING(out_idx) WHERE out_doc_type = 3 AND it_code = v_it_code AND otst_wh_location = v_location;
		ELSE
			select into v_qty SUM(otst_qty) FROM idc_tb_outgoing JOIN idc_tb_outgoing_stock USING(out_idx) WHERE out_doc_type = 3 AND it_code = v_it_code AND otst_type = v_type AND otst_wh_location = v_location;
		END IF;

	/* OUTGOING DF */
	ELSIF v_condition = 23 THEN
		IF v_location = 0 AND v_type = 0 THEN
			select into v_qty SUM(otst_qty) FROM idc_tb_outgoing JOIN idc_tb_outgoing_stock USING(out_idx) WHERE out_doc_type = 4 AND it_code = v_it_code;
		ELSIF v_location = 0 THEN
			select into v_qty SUM(otst_qty) FROM idc_tb_outgoing JOIN idc_tb_outgoing_stock USING(out_idx) WHERE out_doc_type = 4 AND it_code = v_it_code AND otst_type = v_type;
		ELSIF v_type = 0 THEN
			select into v_qty SUM(otst_qty) FROM idc_tb_outgoing JOIN idc_tb_outgoing_stock USING(out_idx) WHERE out_doc_type = 4 AND it_code = v_it_code AND otst_wh_location = v_location;
		ELSE
			select into v_qty SUM(otst_qty) FROM idc_tb_outgoing JOIN idc_tb_outgoing_stock USING(out_idx) WHERE out_doc_type = 4 AND it_code = v_it_code AND otst_type = v_type AND otst_wh_location = v_location;
		END IF;

	/* OUTGOING DR */
	ELSIF v_condition = 24 THEN
		IF v_location = 0 AND v_type = 0 THEN
			select into v_qty SUM(otst_qty) FROM idc_tb_outgoing JOIN idc_tb_outgoing_stock USING(out_idx) WHERE out_doc_type = 5 AND it_code = v_it_code;
		ELSIF v_location = 0 THEN
			select into v_qty SUM(otst_qty) FROM idc_tb_outgoing JOIN idc_tb_outgoing_stock USING(out_idx) WHERE out_doc_type = 5 AND it_code = v_it_code AND otst_type = v_type;
		ELSIF v_type = 0 THEN
			select into v_qty SUM(otst_qty) FROM idc_tb_outgoing JOIN idc_tb_outgoing_stock USING(out_idx) WHERE out_doc_type = 5 AND it_code = v_it_code AND otst_wh_location = v_location;
		ELSE
			select into v_qty SUM(otst_qty) FROM idc_tb_outgoing JOIN idc_tb_outgoing_stock USING(out_idx) WHERE out_doc_type = 5 AND it_code = v_it_code AND otst_type = v_type AND otst_wh_location = v_location;
		END IF;

	/* BORROW */
	ELSIF v_condition = 25 THEN
		IF v_location = 0 AND v_type = 0 THEN
			select into v_qty SUM(bor_qty) FROM idc_tb_borrow WHERE it_code = v_it_code AND bor_is_returned is false;
		ELSIF v_location = 0 THEN
			select into v_qty SUM(bor_qty) FROM idc_tb_borrow WHERE it_code = v_it_code AND bor_is_returned is false AND bor_from_type = v_type;
		ELSIF v_type = 0 THEN
			select into v_qty SUM(bor_qty) FROM idc_tb_borrow WHERE it_code = v_it_code AND bor_is_returned is false AND bor_from_wh = v_location;
		ELSE
			select into v_qty SUM(bor_qty) FROM idc_tb_borrow WHERE it_code = v_it_code AND bor_is_returned is false AND bor_from_type = v_type AND bor_from_wh = v_location;
		END IF;

	/* STOCK MOVE TO DEMO */
	ELSIF v_condition = 26 THEN
		IF v_location = 0 AND v_type = 0 THEN
			select into v_qty SUM(otst_qty) FROM idc_tb_outgoing JOIN idc_tb_outgoing_stock USING(out_idx) WHERE out_doc_type = 6 AND it_code = v_it_code;
		ELSIF v_location = 0 THEN
			select into v_qty SUM(otst_qty) FROM idc_tb_outgoing JOIN idc_tb_outgoing_stock USING(out_idx) WHERE out_doc_type = 6 AND it_code = v_it_code AND otst_type = v_type;
		ELSIF v_type = 0 THEN
			select into v_qty SUM(otst_qty) FROM idc_tb_outgoing JOIN idc_tb_outgoing_stock USING(out_idx) WHERE out_doc_type = 6 AND it_code = v_it_code AND otst_wh_location = v_location;
		ELSE
			select into v_qty SUM(otst_qty) FROM idc_tb_outgoing JOIN idc_tb_outgoing_stock USING(out_idx) WHERE out_doc_type = 6 AND it_code = v_it_code AND otst_type = v_type AND otst_wh_location = v_location;
		END IF;

	/* STOCK MOVE TO REJECT */
	ELSIF v_condition = 27 THEN
		IF v_location = 0 AND v_type = 0 THEN
			select into v_qty SUM(rjit_qty) FROM idc_tb_reject join idc_tb_reject_item using(rjt_idx) WHERE rjt_doc_type = 2 AND it_code = v_it_code;
		ELSIF v_location = 0 THEN
			select into v_qty SUM(rjit_qty) FROM idc_tb_reject join idc_tb_reject_item using(rjt_idx) WHERE rjt_doc_type = 2 AND it_code = v_it_code AND rjit_type = v_type;
		ELSIF v_type = 0 THEN
			select into v_qty SUM(rjit_qty) FROM idc_tb_reject join idc_tb_reject_item using(rjt_idx) WHERE rjt_doc_type = 2 AND it_code = v_it_code AND rjit_wh_location = v_location;
		ELSE
			select into v_qty SUM(rjit_qty) FROM idc_tb_reject join idc_tb_reject_item using(rjt_idx) WHERE rjt_doc_type = 2 AND it_code = v_it_code AND rjit_type = v_type AND rjit_wh_location = v_location;
		END IF;

	/* DELETE EXPIRED DATE */
	ELSIF v_condition = 29 THEN
		IF v_location = 0 AND v_type = 0 THEN
			select into v_qty SUM(rjed_qty) FROM idc_tb_reject_ed WHERE it_code = v_it_code;
		ELSIF v_location = 0 THEN
			select into v_qty SUM(rjed_qty) FROM idc_tb_reject_ed WHERE it_code = v_it_code AND rjed_type = v_type;
		ELSIF v_type = 0 THEN
			select into v_qty SUM(rjed_qty) FROM idc_tb_reject_ed WHERE it_code = v_it_code AND rjed_wh_location = v_location;
		ELSE
			select into v_qty SUM(rjed_qty) FROM idc_tb_reject_ed WHERE it_code = v_it_code AND rjed_type = v_type AND rjed_wh_location = v_location;
		END IF;

	/* MOVE LOCATION */
	ELSIF v_condition = 30 THEN
		IF v_location = 0 AND v_type = 0 THEN
			select into v_qty SUM(mv_qty) FROM idc_tb_move_stock WHERE it_code = v_it_code;
		ELSIF v_location = 0 THEN
			select into v_qty SUM(mv_qty) FROM idc_tb_move_stock WHERE it_code = v_it_code AND mv_from_type = v_type;
		ELSIF v_type = 0 THEN
			select into v_qty SUM(mv_qty) FROM idc_tb_move_stock WHERE it_code = v_it_code AND mv_from_wh = v_location;
		ELSE
			select into v_qty SUM(mv_qty) FROM idc_tb_move_stock WHERE it_code = v_it_code AND mv_from_type = v_type AND mv_from_wh = v_location;
		END IF;
	END IF;

	IF v_qty IS NULL OR v_qty <= 0 THEN
		v_qty = 0;
	ELSE
		v_qty = v_qty*-1;
	END IF;

	RETURN v_qty;
END;
$$
;

alter function idc_getoutgoingbalance(varchar, integer, integer, integer) owner to dskim
;

create function idc_getpaymentdate(v_bill_code character varying) returns character varying
	language plpgsql
as $$
DECLARE
	rec RECORD;
	v_date varchar := 'aaaa';
BEGIN
	SELECT INTO v_date max(pay_date) FROM idc_tb_payment WHERE bill_code = v_bill_code;
	RETURN v_date;
END;
$$
;

alter function idc_getpaymentdate(varchar) owner to dskim
;

create function idc_getpdf(v_bill_code character varying) returns character varying
	language plpgsql
as $$
DECLARE
	v_return varchar;
BEGIN

	SELECT INTO v_return billf_file_name FROM idc_tb_billing_file WHERE billf_file_type = 'Faktur Pajak' AND bill_code = v_bill_code;

    RETURN v_return;

END;
$$
;

alter function idc_getpdf(varchar) owner to dskim
;

create function idc_getplcode(v_po_code character varying) returns character varying
	language plpgsql
as $$
DECLARE
	rec record;
	v_pl_code varchar := '';
BEGIN

	FOR rec IN SELECT pl_idx FROM idc_tb_pl WHERE po_code = v_po_code ORDER BY pl_idx LOOP
		v_pl_code = v_pl_code || rec.pl_idx  || ', ';
	END LOOP;

	IF v_pl_code != '' THEN
		RETURN substr(v_pl_code, 1, length(v_pl_code) - 2);
	ELSE
		RETURN '';
	END IF;

END;
$$
;

alter function idc_getplcode(varchar) owner to dskim
;

create function idc_getpllocalcode(v_po_code character varying, v_pl_no integer) returns character varying
	language plpgsql
as $$
DECLARE
	rec record; rec2 record;
	v_pl_code varchar := '';
BEGIN

	IF v_pl_no is not null THEN
		FOR rec IN SELECT inlc_idx FROM idc_tb_in_local WHERE po_code = v_po_code AND pl_no = v_pl_no ORDER BY inlc_idx LOOP
			v_pl_code = v_pl_code || rec.inlc_idx  || ', ';
		END LOOP;
		FOR rec2 IN SELECT inlc_idx FROM idc_tb_in_local_v2 WHERE po_code = v_po_code AND pl_no = v_pl_no ORDER BY inlc_idx LOOP
			v_pl_code = v_pl_code || rec2.inlc_idx  || ', ';
		END LOOP;
	ELSE
		FOR rec IN SELECT pl_no FROM idc_tb_pl_local WHERE po_code = v_po_code ORDER BY pl_no LOOP
			v_pl_code = v_pl_code || rec.pl_no  || ', ';
		END LOOP;
	END IF;

	IF v_pl_code != '' THEN
		RETURN substr(v_pl_code, 1, length(v_pl_code) - 2);
	ELSE
		RETURN '';
	END IF;

END;
$$
;

alter function idc_getpllocalcode(varchar, integer) owner to dskim
;

create function idc_getpllocalno(v_po_code character varying) returns integer
	language plpgsql
as $$
DECLARE
	v_pl_no integer;
BEGIN
	SELECT INTO v_pl_no max(pl_no) FROM idc_tb_pl_local WHERE po_code = v_po_code;

	IF v_pl_no IS NULL THEN
		v_pl_no := 1;
	ELSE
		v_pl_no := v_pl_no + 1;
	END IF;
	RETURN v_pl_no;
END;
$$
;

alter function idc_getpllocalno(varchar) owner to dskim
;

create function idc_getpoitemqty(v_code character varying, v_it_code character varying) returns character varying
	language plpgsql
as $$
DECLARE
	v_qty varchar;
BEGIN
	SELECT INTO v_qty poit_qty FROM idc_tb_po_item WHERE po_code = v_code AND it_code = v_it_code;
	RETURN v_qty;
END;
$$
;

alter function idc_getpoitemqty(varchar, varchar) owner to dskim
;

create function idc_getrdtcode(v_code character varying) returns character varying
	language plpgsql
as $$
DECLARE
	rec record;
	v_rdt_code varchar := '';
BEGIN

	FOR rec IN SELECT rdt_code FROM idc_tb_return_dt WHERE dt_code = v_code ORDER BY rdt_code LOOP
		v_rdt_code = v_rdt_code || rec.rdt_code  || ', ';
	END LOOP;

	IF v_rdt_code != '' THEN
		RETURN substr(v_rdt_code, 1, length(v_rdt_code) - 2);
	ELSE
		RETURN '';
	END IF;
END;
$$
;

alter function idc_getrdtcode(varchar) owner to dskim
;

create function idc_getrdtqty(v_code character varying, v_it_code character varying, v_type integer) returns numeric
	language plpgsql
as $$
DECLARE
	recI record;
	recII record;
	v_qty numeric := 0.00;
BEGIN

	IF v_it_code = '' AND v_type IS NULL THEN
		FOR recI IN SELECT rdt_code FROM idc_tb_return_dt WHERE dt_code = v_code ORDER BY rdt_code LOOP
			FOR recII IN SELECT istd_qty FROM idc_tb_outstanding JOIN idc_tb_outstanding_item USING(std_idx) WHERE std_doc_ref = recI.rdt_code AND std_doc_type='Return DT' LOOP
				v_qty = v_qty + recII.istd_qty;
			END LOOP;
		END LOOP;

	ELSIF v_type IS NULL THEN
		FOR recI IN SELECT rdt_code FROM idc_tb_return_dt WHERE dt_code = v_code ORDER BY rdt_code LOOP
			FOR recII IN SELECT rdtit_qty FROM idc_tb_return_dt_item  WHERE rdt_code=recI.rdt_code AND it_code=v_it_code LOOP
				v_qty = v_qty + recII.rdtit_qty;
			END LOOP;
		END LOOP;

	ELSE
		FOR recI IN SELECT rdt_code FROM idc_tb_return_dt WHERE dt_code = v_code ORDER BY rdt_code LOOP
			FOR recII IN SELECT istd_qty FROM idc_tb_outstanding JOIN idc_tb_outstanding_item USING(std_idx) WHERE std_doc_ref = recI.rdt_code AND std_doc_type='Return DT' AND it_code=v_it_code AND istd_type=v_type LOOP
				v_qty = v_qty + recII.istd_qty;
			END LOOP;

		END LOOP;
	END IF;

	IF v_qty IS NULL THEN
		v_qty = 0.00;
	END IF;

	RETURN v_qty;
END;
$$
;

alter function idc_getrdtqty(varchar, varchar, integer) owner to dskim
;

create function idc_getrdtwh(v_code character varying, v_it_code character varying, v_type character varying) returns character varying
	language plpgsql
as $$
DECLARE
    r1 record;
    r2 record;
    v_val varchar ;
BEGIN

    -- v_type > last rdt code, last rdt date
    FOR r1 IN SELECT rdt_code, rdt_date FROM idc_tb_return_dt WHERE dt_code = v_code ORDER BY rdt_date, rdt_code LOOP
        FOR r2 IN SELECT * FROM idc_tb_incoming JOIN idc_tb_incoming_item USING(inc_idx)
        WHERE inc_doc_ref = r1.rdt_code AND inc_confirmed_timestamp is not null and it_code = v_it_code
        LOOP
            IF v_type = 'code' THEN
                v_val := r1.rdt_code;
            ELSIF v_type = 'date' THEN
                v_val := to_char(r1.rdt_date,'dd-Mon-yyyy');
            END IF;
        END LOOP;
    END LOOP;

    RETURN v_val;
END;
$$
;

alter function idc_getrdtwh(varchar, varchar, varchar) owner to dskim
;

create function idc_getrdtwhqty(v_code character varying, v_it_code character varying, v_type character varying) returns numeric
	language plpgsql
as $$
DECLARE
    r1 record;
    r2 record;
    v_qty numeric := 0.00;
BEGIN

    -- v_type > stock, demo, reject

    FOR r1 IN SELECT rdt_code FROM idc_tb_return_dt WHERE dt_code = v_code ORDER BY rdt_code LOOP
        FOR r2 IN SELECT * FROM idc_tb_incoming JOIN idc_tb_incoming_item USING(inc_idx)
        WHERE inc_doc_ref = r1.rdt_code AND it_code = v_it_code
        LOOP
            IF v_type = 'stock' THEN
                v_qty = v_qty + r2.init_stock_qty;
            ELSIF v_type = 'demo' THEN
                v_qty = v_qty + r2.init_demo_qty;
            ELSIF v_type = 'reject' THEN
                v_qty = v_qty + r2.init_reject_qty;
            END IF;
        END LOOP;
    END LOOP;

    IF v_qty IS NULL THEN
        v_qty = 0.00;
    END IF;

    RETURN v_qty;
END;
$$
;

alter function idc_getrdtwhqty(varchar, varchar, varchar) owner to dskim
;

create function idc_getreadystock(v_it_code character varying, v_dept character varying) returns numeric
	language plpgsql
as $$
DECLARE
	v_ed boolean;
	v_qty numeric := 0.00;
	v_icat_midx integer;
BEGIN

	SELECT INTO v_ed it_ed FROM idc_tb_item WHERE it_code = v_it_code;
	SELECT INTO v_icat_midx icat_midx FROM idc_tb_item WHERE it_code = v_it_code;

	IF v_ed = true THEN
		SELECT INTO v_qty idc_getReadyStockED(v_it_code, v_dept);
	ELSE
		SELECT INTO v_qty SUM(stk_qty) FROM idc_tb_stock_v2 WHERE it_code = v_it_code;
	END IF;


	IF v_qty <= 0 OR v_qty IS NULL THEN
		v_qty := 0;
	END IF;

	RETURN v_qty;
END;
$$
;

alter function idc_getreadystock(varchar, varchar) owner to dskim
;

create function idc_getreadystocked(v_it_code character varying, v_dept character varying) returns numeric
	language plpgsql
as $$
DECLARE
	v_qty numeric := 0.00;
	v_icat_midx integer;
BEGIN

/*
A
	2101, 2101NE		8	Strip
	2200				6	AGL-28

D
	2101, 2101NE		8
	2200				6
	icat_midx (99, 100, 101, 103, 104, 105, 106, 107)		6

H
	2101, 2101NE, 2200	4
	icat_midx (99, 100, 101, 103, 104, 105, 106, 107)		2

M
	2101, 2101NE, 2200	4
	icat_midx (99, 100, 101, 103, 104, 105, 106, 107)		2

P
	2101, 2101NE, 2200	4
	icat_midx (99, 100, 101, 103, 104, 105, 106, 107)		2

T
	2101, 2101NE, 2200	0
	icat_midx (99, 100, 101, 103, 104, 105, 106, 107)		2

*/

	SELECT INTO v_icat_midx icat_midx FROM idc_tb_item WHERE it_code = v_it_code;

	-- Apotik
	IF v_dept = 'A' THEN
		IF v_it_code IN ('2101','2101NE') THEN
			select into v_qty sum(sted_qty) from idc_tb_stock_ed  where it_code = v_it_code and idc_getRemainMonth(sted_expired_date) >= 8;
		ELSIF v_it_code IN ('2200') THEN
			select into v_qty sum(sted_qty) from idc_tb_stock_ed  where it_code = v_it_code and idc_getRemainMonth(sted_expired_date) >= 6;
		ELSE
			select into v_qty sum(sted_qty) from idc_tb_stock_ed  where it_code = v_it_code;
		END IF;

	-- Dealer
	ELSIF v_dept = 'D' THEN
		IF v_it_code IN ('2101','2101NE') THEN
			select into v_qty sum(sted_qty) from idc_tb_stock_ed  where it_code = v_it_code and idc_getRemainMonth(sted_expired_date) >= 8;
		ELSIF v_it_code IN ('2200') THEN
			select into v_qty sum(sted_qty) from idc_tb_stock_ed  where it_code = v_it_code and idc_getRemainMonth(sted_expired_date) >= 5;
		ELSIF v_icat_midx IN (99, 100, 101, 103, 104, 105, 106, 107) THEN
			select into v_qty sum(sted_qty) from idc_tb_stock_ed  where it_code = v_it_code and idc_getRemainMonth(sted_expired_date) >= 6;
		ELSE
			select into v_qty sum(sted_qty) from idc_tb_stock_ed  where it_code = v_it_code;
		END IF;

	-- Hospital
	ELSIF v_dept = 'H' THEN
		IF v_it_code IN('2101','2101NE','2200') THEN
			select into v_qty sum(sted_qty) from idc_tb_stock_ed  where it_code = v_it_code and idc_getRemainMonth(sted_expired_date) >= 4;
		ELSIF  v_icat_midx IN (99, 100, 101, 103, 104, 105, 106, 107) THEN
			select into v_qty sum(sted_qty) from idc_tb_stock_ed  where it_code = v_it_code and idc_getRemainMonth(sted_expired_date) >= 2;
		ELSE
			select into v_qty sum(sted_qty) from idc_tb_stock_ed  where it_code = v_it_code;
		END IF;

	-- Marketing
	ELSIF v_dept = 'M' THEN
		IF v_it_code IN('2101','2101NE','2200') THEN
			select into v_qty sum(sted_qty) from idc_tb_stock_ed  where it_code = v_it_code and idc_getRemainMonth(sted_expired_date) >= 4;
		ELSIF  v_icat_midx IN (99, 100, 101, 103, 104, 105, 106, 107) THEN
			select into v_qty sum(sted_qty) from idc_tb_stock_ed  where it_code = v_it_code and idc_getRemainMonth(sted_expired_date) >= 2;
		ELSE
			select into v_qty sum(sted_qty) from idc_tb_stock_ed  where it_code = v_it_code;
		END IF;

	-- Pharmaceutical
	ELSIF v_dept = 'P' THEN
		IF v_it_code IN('2101','2101NE','2200') THEN
			select into v_qty sum(sted_qty) from idc_tb_stock_ed  where it_code = v_it_code and idc_getRemainMonth(sted_expired_date) >= 4;
		ELSIF  v_icat_midx IN (99, 100, 101, 103, 104, 105, 106, 107) THEN
			select into v_qty sum(sted_qty) from idc_tb_stock_ed  where it_code = v_it_code and idc_getRemainMonth(sted_expired_date) >= 2;
		ELSE
			select into v_qty sum(sted_qty) from idc_tb_stock_ed  where it_code = v_it_code;
		END IF;

	-- Tender
	ELSIF v_dept = 'T' THEN
		select into v_qty sum(sted_qty) from idc_tb_stock_ed  where it_code = v_it_code;

	-- Others item
	ELSE
		select into v_qty sum(sted_qty) from idc_tb_stock_ed  where it_code = v_it_code;
	END IF;

	IF v_qty <= 0 OR v_qty IS NULL THEN
		v_qty := 0;
	END IF;

	RETURN v_qty;
END;
$$
;

alter function idc_getreadystocked(varchar, varchar) owner to dskim
;

create function idc_getremainmonth(v_expired_date date) returns integer
	language plpgsql
as $$
DECLARE
	v_remain_month integer := 0;
	v_current_month date := to_char(current_date,'YYYY-MM-01');
BEGIN
	v_remain_month := v_expired_date - v_current_month;	v_remain_month :=  v_remain_month/ 29;
	RETURN v_remain_month;
END;
$$
;

alter function idc_getremainmonth(date) owner to dskim
;

create function idc_getrequestbalance(v_it_code character varying, v_condition integer, v_location integer, v_type integer) returns numeric
	language plpgsql
as $$
DECLARE
	v_temp_1_qty numeric;
	v_temp_2_qty numeric;
	v_qty numeric;
	v_rjit_idx integer;
BEGIN

	/* request item */
	/* REQUEST DO */
	IF v_condition = 31 THEN
		IF v_type != 0 THEN
			select into v_qty SUM(boit_qty) FROM idc_tb_booking JOIN idc_tb_booking_item USING(book_idx) WHERE book_doc_type IN(1,2) AND it_code = v_it_code AND boit_type = v_type;
		ELSE
			select into v_qty SUM(boit_qty) FROM idc_tb_booking JOIN idc_tb_booking_item USING(book_idx) WHERE book_doc_type IN(1,2) AND it_code = v_it_code;
	END IF;
	/* REQUEST DT */
	ELSIF v_condition = 32 THEN
		IF v_type != 0 THEN
			select into v_qty SUM(boit_qty) FROM idc_tb_booking JOIN idc_tb_booking_item USING(book_idx) WHERE book_doc_type = 3 AND it_code = v_it_code AND boit_type = v_type;
		ELSE
			select into v_qty SUM(boit_qty) FROM idc_tb_booking JOIN idc_tb_booking_item USING(book_idx) WHERE book_doc_type = 3  AND it_code = v_it_code;
		END IF;
	/* REQUEST DF */
	ELSIF v_condition = 33 THEN
		IF v_type != 0 THEN
			select into v_qty SUM(boit_qty) FROM idc_tb_booking JOIN idc_tb_booking_item USING(book_idx) WHERE book_doc_type = 4  AND it_code = v_it_code AND boit_type = v_type;
		ELSE
			select into v_qty SUM(boit_qty) FROM idc_tb_booking JOIN idc_tb_booking_item USING(book_idx) WHERE book_doc_type = 4  AND it_code = v_it_code;
		END IF;
	/* REQUEST DR */
	ELSIF v_condition = 34 THEN
		IF v_type != 0 THEN
			select into v_qty SUM(boit_qty) FROM idc_tb_booking JOIN idc_tb_booking_item USING(book_idx) WHERE book_doc_type = 5  AND it_code = v_it_code AND boit_type = v_type;
		ELSE
			select into v_qty SUM(boit_qty) FROM idc_tb_booking JOIN idc_tb_booking_item USING(book_idx) WHERE book_doc_type = 5  AND it_code = v_it_code;
		END IF;
	/* REQUEST DM */
	ELSIF v_condition = 35 THEN
		IF v_type != 0 THEN
			select into v_qty SUM(boit_qty) FROM idc_tb_booking JOIN idc_tb_booking_item USING(book_idx) WHERE book_doc_type = 6  AND it_code = v_it_code AND boit_type = v_type;
		ELSE
			select into v_qty SUM(boit_qty) FROM idc_tb_booking JOIN idc_tb_booking_item USING(book_idx) WHERE book_doc_type = 6  AND it_code = v_it_code;
		END IF;
	END IF;

	RETURN v_qty;
END;
$$
;

alter function idc_getrequestbalance(varchar, integer, integer, integer) owner to dskim
;

create function idc_getreturnbillingcode(v_source character varying, v_ordered_by integer, v_vat integer, v_dept character varying, v_return_date date, v_type_return character varying) returns character varying
	language plpgsql
as $$
DECLARE
	v_current_month varchar[] := ARRAY['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
	v_month integer := extract(MONTH FROM v_return_date) + 0;
	v_monyy varchar := v_current_month[v_month] || to_char(v_return_date, 'YY');
	v_new_code varchar;
	v_serial integer;
	v_tax varchar;
BEGIN

	IF v_source = 'IDC' THEN
		IF v_ordered_by = 1 THEN
			SELECT INTO v_serial max(substr(turn_code, 4, 3)) FROM idc_tb_return WHERE turn_ordered_by = 1 AND substr(turn_code, 7, 1) = v_dept AND substr(turn_code, 9, 3) = v_monyy;
			IF v_serial IS NULL THEN v_serial := 1;
			ELSE 					 v_serial := v_serial + 1; END IF;
			IF v_vat > 0 THEN 		 v_tax := 'O';
			ELSE					 v_tax := 'X'; END IF;

			IF v_type_return = 'RO' THEN
				v_new_code := 'R' || v_tax || '-' || lpad(v_serial::text, 3, '0') || v_dept || '-' || v_monyy;
			ELSIF v_type_return = 'RR' THEN
				v_new_code := 'RR-' || lpad(v_serial::text, 3, '0') || v_dept || '-' || v_monyy;
			END IF;
		ELSIF v_ordered_by = 2 THEN
			SELECT INTO v_serial max(substr(turn_code, 5, 2)) FROM idc_tb_return WHERE turn_ordered_by = 2 AND substr(turn_code, 7, 1) = v_dept AND substr(turn_code, 9, 3) = v_monyy;
			IF v_serial IS NULL THEN v_serial := 1;
			ELSE 					 v_serial := v_serial + 1; END IF;
			IF v_vat > 0 THEN 		 v_tax := 'F';
			ELSE					 v_tax := 'N'; END IF;

			IF v_type_return = 'RO' THEN
				v_new_code := 'R' || v_tax || '-M' || lpad(v_serial::text, 2, '0') || v_dept || '-' || v_monyy;
			ELSIF v_type_return = 'RR' THEN
				v_new_code := 'RR-M' || lpad(v_serial::text, 2, '0') || v_dept || '-' || v_monyy;
			END IF;
		END IF;
	ELSIF v_source = 'MED' THEN
		IF v_ordered_by = 1 THEN
			SELECT INTO v_serial max(substr(turn_code, 5, 2)) FROM med_tb_return WHERE turn_ordered_by = 1 AND substr(turn_code, 7, 1) = v_dept AND substr(turn_code, 9, 3) = v_monyy;
			IF v_serial IS NULL THEN v_serial := 1;
			ELSE 					 v_serial := v_serial + 1; END IF;
			IF v_vat > 0 THEN 		 v_tax := 'F';
			ELSE					 v_tax := 'N'; END IF;

			IF v_type_return = 'RO' THEN
				v_new_code := 'R' || v_tax || '-M' || lpad(v_serial::text, 2, '0') || v_dept || '-' || v_monyy;
			ELSIF v_type_return = 'RR' THEN
				v_new_code := 'RR-M' || lpad(v_serial::text, 2, '0') || v_dept || '-' || v_monyy;
			END IF;
		END IF;
	END IF;

	RETURN v_new_code;

END;
$$
;

alter function idc_getreturnbillingcode(varchar, integer, integer, varchar, date, varchar) owner to dskim
;

create function idc_getreturnreference(v_value character varying, v_type character varying, v_return_code character varying) returns character varying
	language plpgsql
as $$
DECLARE
	v_val varchar;
	v_val_doc varchar;
	v_val_date varchar;
BEGIN

	IF v_value = 'doc' THEN
		IF v_type = 'Return Billing' THEN
			SELECT INTO v_val_doc turn_bill_code FROM idc_tb_return WHERE turn_code = v_return_code;
		ELSIF v_type = 'Return Order' THEN
			SELECT INTO v_val_doc ord_code FROM idc_tb_return_order WHERE reor_code = v_return_code;
		ELSIF v_type = 'Return DT' THEN
			SELECT INTO v_val_doc dt_code FROM idc_tb_return_dt WHERE rdt_code = v_return_code;
		END IF;
		v_val = v_val_doc;
	ELSIF v_value = 'Date' THEN
		SELECT INTO v_val_doc idc_getReturnReference ('date', v_type, v_return_code);

		IF v_type = 'Return Billing' THEN
			SELECT INTO v_val_date bill_inv_date FROM idc_tb_billing WHERE bill_code = v_code;
		ELSIF v_type = 'Return Order' THEN
			SELECT INTO v_val_date ord_po_date FROM idc_tb_order WHERE ord_code = v_code;
		ELSIF v_type = 'Return DT' THEN
			SELECT INTO v_val_date dt_date FROM idc_tb_dt WHERE dt_code = v_code;
		END IF;
		v_val = v_val_date::text;
	END IF;

	RETURN v_val;
END;
$$
;

alter function idc_getreturnreference(varchar, varchar, varchar) owner to dskim
;

create function idc_getservicebill(v_code character varying, v_idx integer, v_type integer) returns character varying
	language plpgsql
as $$
DECLARE
	v_return varchar;
	v_cost integer :=0;
	v_amount numeric;
	v_date date;
BEGIN

	IF v_type = 1 THEN
		SELECT INTO v_return sv_code FROM idc_tb_service WHERE sv_reg_no=v_code;
	ELSIF v_type = 2 THEN
		SELECT INTO v_return sv_total_amount::varchar FROM idc_tb_service WHERE sv_reg_no=v_code;
	ELSIF v_type = 3 THEN
		SELECT INTO v_amount sv_total_remain FROM idc_tb_service WHERE sv_reg_no=v_code;
		SELECT INTO v_date sv_due_date FROM idc_tb_service WHERE sv_reg_no=v_code;
		IF v_idx is not null THEN
			SELECT INTO v_cost sgit_cost FROM idc_tb_service_reg_item WHERE sgit_idx=v_idx;
		END IF;

		IF v_amount is not null AND v_date is not null THEN
			IF v_cost=1 THEN
				IF v_amount <= 0 THEN v_return = 'paid_chr';
				ELSIF v_amount > 0 AND v_date > CURRENT_TIMESTAMP THEN v_return = 'before_due_chr';
				ELSIF v_amount > 0 AND v_date < CURRENT_TIMESTAMP THEN v_return = 'over_due_chr';
				ELSE v_return = 'before_due_chr';
				END IF;
			ELSIF v_amount <= 0 THEN v_return = 'paid';
			ELSIF v_amount > 0 AND v_date > CURRENT_TIMESTAMP THEN v_return = 'before_due';
			ELSIF v_amount > 0 AND v_date < CURRENT_TIMESTAMP THEN v_return = 'over_due';
			ELSE v_return = 'before_due';
			END IF;
		ELSE
			IF v_cost=1 THEN
				v_return = 'before_due_chr';
			ELSE
				v_return = 'before_due';
			END IF;
		END IF;
	END IF;

	RETURN v_return;
END;
$$
;

alter function idc_getservicebill(varchar, integer, integer) owner to dskim
;

create function idc_getstock(v_it_code character varying, v_type integer, v_location integer) returns numeric
	language plpgsql
as $$
DECLARE
	v_qty numeric;
BEGIN

	IF v_location = 0 THEN
		SELECT INTO v_qty SUM(stk_qty) FROM idc_tb_stock_v2 WHERE it_code = v_it_code AND stk_type = v_type;
	ELSE
		SELECT INTO v_qty SUM(stk_qty) FROM idc_tb_stock_v2 WHERE it_code = v_it_code AND stk_type = v_type AND stk_wh_location = v_location;
	END IF;

	IF v_qty IS NULL THEN
		v_qty = 0.00;
	END IF;

	RETURN v_qty;
END;
$$
;

alter function idc_getstock(varchar, integer, integer) owner to dskim
;

create function idc_getstockbalance(v_it_code character varying, v_condition integer, v_location integer, v_type integer) returns numeric
	language plpgsql
as $$
DECLARE
	v_temp_1_qty numeric;
	v_temp_2_qty numeric;
	v_qty numeric;
	v_rjit_idx integer;
BEGIN
	/* inventory value */
	/* STOCK */
	IF v_condition = 0 THEN
		IF v_location = 0 AND v_type = 0 THEN
			select into v_qty SUM(stk_qty) FROM idc_tb_stock WHERE it_code = v_it_code;
		ELSIF v_location = 0 THEN
			select into v_qty SUM(stk_qty) FROM idc_tb_stock WHERE it_code = v_it_code AND stk_type = v_type;
		ELSIF v_type = 0 THEN
			select into v_qty SUM(stk_qty) FROM idc_tb_stock WHERE it_code = v_it_code AND stk_wh_location = v_location;
		ELSE
			select into v_qty SUM(stk_qty) FROM idc_tb_stock WHERE it_code = v_it_code AND stk_type = v_type AND stk_wh_location = v_location;
		END IF;

	/*DEMO */
	ELSIF v_condition = 1 THEN
		select into v_temp_1_qty SUM(rqit_qty) from idc_tb_request join idc_tb_request_item using(req_code) where req_cfm_wh_delivery_timestamp is not null and it_code = v_it_code;
		select into v_temp_2_qty SUM(init_demo_qty) from idc_tb_incoming_marketing join idc_tb_incoming_item using(inc_idx) where inm_cfm_wh_delivery_timestamp is not null and it_code = v_it_code;

		if v_temp_1_qty is null then v_temp_1_qty = 0; end if;
		if v_temp_2_qty is null then v_temp_2_qty = 0; end if;
		v_qty = v_temp_1_qty + v_temp_2_qty;

	/* REJECT */
	ELSIF v_condition = 2 THEN
		select into v_qty sum(rjit_qty) FROM idc_tb_reject_item WHERE it_code = v_it_code;
	END IF;

	RETURN v_qty;
END;
$$
;

alter function idc_getstockbalance(varchar, integer, integer, integer) owner to dskim
;

create function idc_getstocklogidx(v_it_code character varying, v_location integer, v_type integer, v_cfm_date date) returns character varying
	language plpgsql
as $$
DECLARE
	v_monyy varchar := to_char(v_cfm_date, 'YY');
	v_new_log_code varchar;
	v_serial integer;
BEGIN

	SELECT INTO
		v_serial max(substr(log_code, 6, 5))
	FROM
		idc_tb_log_detail
	WHERE
		it_code = v_it_code
		AND log_wh_location = v_location
		AND log_type = v_type
		AND SUBSTR(log_code,3,2) = v_monyy;

	IF v_serial IS NULL THEN
		v_serial := 1;
	ELSE
		v_serial := v_serial + 1;
	END IF;

	v_new_log_code := v_location::text || v_type::text || v_monyy || '-' || lpad(v_serial::text, 5, '0');

	RETURN v_new_log_code;
END;
$$
;

alter function idc_getstocklogidx(varchar, integer, integer, date) owner to dskim
;

create function idc_getsubcategory(integer) returns character varying
	language plpgsql
as $$
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
	SELECT INTO v_temp icat_midx FROM idc_tb_item_cat WHERE icat_pidx = $1;

	IF FOUND THEN
		v_element[v_i] := $1;
		WHILE v_element[v_j] IS NOT NULL LOOP
			FOR v_midx, v_depth IN
			SELECT icat_midx, icat_depth FROM idc_tb_item_cat WHERE icat_pidx = v_element[v_j] LOOP
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
$$
;

alter function idc_getsubcategory(integer) owner to dskim
;

create function idc_gettotalpaid(v_code character varying) returns character varying
	language plpgsql
as $$
DECLARE
	v_pay_idx integer;
	v_paid_amount numeric := 0;
	v_row_count integer := 0;
	r_amount record;
BEGIN

	-- Check the payment
	SELECT INTO v_pay_idx pay_idx FROM idc_tb_payment WHERE bill_code = v_code;

	GET DIAGNOSTICS v_row_count := ROW_COUNT;
	IF v_row_count >= 1 THEN
		FOR r_amount IN SELECT pay_paid FROM idc_tb_payment  WHERE bill_code = v_code ORDER BY pay_idx LOOP
			v_paid_amount := v_paid_amount + r_amount.pay_paid;
		END LOOP;
	END IF;

  RETURN v_paid_amount;
END;
$$
;

alter function idc_gettotalpaid(varchar) owner to dskim
;

create function idc_getturncode(v_code character varying, v_type integer) returns character varying
	language plpgsql
as $$
DECLARE
	rec record;
	v_turn_code varchar := '';
BEGIN

	IF v_type = 1 THEN
		FOR rec IN SELECT turn_code FROM idc_tb_return WHERE turn_bill_code = v_code ORDER BY turn_code LOOP
			v_turn_code = v_turn_code || rec.turn_code  || ', ';
		END LOOP;
	ELSEIF v_type = 2 THEN
		FOR rec IN SELECT reor_code FROM idc_tb_return_order WHERE ord_code = v_code ORDER BY reor_code LOOP
			v_turn_code = v_turn_code || rec.reor_code  || ', ';
		END LOOP;
	END IF;

	IF v_turn_code != '' THEN
		RETURN substr(v_turn_code, 1, length(v_turn_code) - 2);
	ELSE
		RETURN '';
	END IF;
END;
$$
;

alter function idc_getturncode(varchar, integer) owner to dskim
;

create function idc_getturndemo(v_code character varying) returns character varying
	language plpgsql
as $$
DECLARE
	rec record;
	v_red_code varchar := '';
BEGIN

	FOR rec IN SELECT red_code FROM idc_tb_return_demo WHERE use_code = v_code ORDER BY red_code LOOP
		v_red_code = v_red_code || rec.red_code  || ', ';
	END LOOP;

	IF v_red_code != '' THEN
		RETURN substr(v_red_code, 1, length(v_red_code) - 2);
	ELSE
		RETURN '';
	END IF;
END;
$$
;

alter function idc_getturndemo(varchar) owner to dskim
;

create function idc_getuserprice(v_code character varying, v_date date, v_type character varying DEFAULT 'basic'::character varying, v_kurs numeric DEFAULT 0.00) returns numeric
	language plpgsql
as $$
DECLARE
	v_unit_price1 numeric; v_unit_price2 numeric;
	v_unit_price numeric;
BEGIN

	IF v_type = 'basic' THEN
		SELECT INTO v_unit_price ip_user_price FROM idc_tb_item_price WHERE it_code = v_code AND ip_date_from <= v_date AND ip_date_to + 1 > v_date;
		IF NOT FOUND THEN
			SELECT INTO v_unit_price ip_user_price FROM idc_tb_item_price WHERE ip_idx = (SELECT max(ip_idx) FROM idc_tb_item_price WHERE it_code = v_code);
		END IF;

	ELSIF v_type = 'net' THEN
		SELECT INTO v_unit_price1 ipn_price_dollar FROM idc_tb_item_price_net WHERE
			it_code = v_code AND ipn_date_from <= v_date AND ipn_idx = (SELECT max(ipn_idx) FROM idc_tb_item_price_net WHERE it_code = v_code AND ipn_date_from <= v_date);
		SELECT INTO v_unit_price2 ipn_price_rupiah FROM idc_tb_item_price_net WHERE
			it_code = v_code AND ipn_date_from <= v_date AND ipn_idx = (SELECT max(ipn_idx) FROM idc_tb_item_price_net WHERE it_code = v_code AND ipn_date_from <= v_date);

		IF v_unit_price1 = 0 THEN
			v_unit_price := v_unit_price2;
		ELSIF v_unit_price2 = 0 THEN
			v_unit_price := v_unit_price1 * v_kurs;
		END IF;

	END IF;

	RETURN v_unit_price;

END;
$$
;

alter function idc_getuserprice(varchar, date, varchar, numeric) owner to dskim
;

create function idc_initialitemfunction() returns void
	language plpgsql
as $$
DECLARE
  rec record;
BEGIN

  FOR rec IN SELECT * FROM idc_tb_item WHERE it_status = 0 ORDER BY it_code LOOP
    INSERT INTO idc_tb_item_function(it_code, ipf_it_code, ipf_qty) VALUES (rec.it_code, rec.it_code, 1);
  END LOOP;

END;
$$
;

alter function idc_initialitemfunction() owner to dskim
;

create function idc_insertbilling(v_source character varying, v_ordered_by integer, v_type_bill integer, v_type_invoice integer, v_dept character varying, v_revision_time integer, v_lastupdated_by_account character varying, v_received_by character varying, v_inv_date date, v_do_no character varying, v_do_date character varying, v_chk_sj_code character varying, v_sj_code character varying, v_sj_date character varying, v_po_no character varying, v_po_date character varying, v_is_vat character varying, v_vat_val integer, v_is_tax character varying, v_ship_to_responsible_by integer, v_cug_code character varying, v_cus_to character varying, v_cus_name character varying, v_cus_attn character varying, v_cus_npwp character varying, v_cus_address character varying, v_ship_to character varying, v_ship_name character varying, v_pajak_to character varying, v_pajak_name character varying, v_pajak_address character varying, v_disc numeric, v_total_amount integer, v_amount_before_vat integer, v_delivery_chk integer, v_delivery_by character varying, v_delivery_warehouse character varying, v_delivery_franco character varying, v_delivery_freight_charge integer, v_payment_chk integer, v_payment_widthin_days integer, v_payment_sj_inv_fp_tender character varying, v_payment_closing_on character varying, v_payment_for_the_month_week character varying, v_payment_cash_by character varying, v_payment_check_by character varying, v_payment_transfer_by character varying, v_payment_giro_due character varying, v_payment_giro_issue character varying, v_bank character varying, v_bank_address character varying, v_tukar_faktur_date character varying, v_signature_by character varying, v_signature_pajak_by character varying, v_paper_format character varying, v_is_cons boolean, v_sales_from character varying, v_sales_to character varying, v_remark character varying, v_wh_it_code character varying[], v_wh_it_code_for character varying[], v_wh_it_qty numeric[], v_wh_it_function numeric[], v_wh_it_remark character varying[], v_sl_date character varying[], v_sl_cus_code character varying[], v_sl_cus_name character varying[], v_sl_faktur_no character varying[], v_sl_lop_no character varying[], v_sl_amount numeric[], v_cus_it_code character varying[], v_cus_it_model_no character varying[], v_cus_it_desc character varying[], v_cus_it_qty integer[], v_cus_it_unit_price numeric[], v_cus_it_remark character varying[], v_cus_it_sl_idx character varying[]) returns character varying
	language plpgsql
as $$
DECLARE
    v_i integer := 1;
    v_j integer := 1;
    v_k integer := 1;
    v_code varchar := '';
    v_do_no varchar;
    v_vat_inv_no varchar;
    v_type integer;
    v_sj_code_adj varchar;
    v_sj_date_adj date;
    v_cur_book_idx integer := 0;
    v_do_date_adj date;
    v_po_date_adj date;
    v_payment_closing_on_adj date;
    v_payment_giro_due_adj date;
    v_payment_giro_issue_adj date;
    v_tukar_faktur_date_adj date;
    v_sales_from_adj date;
    v_sales_to_adj date;
    v_icat_midx integer;
    v_it_type varchar;
    v_fkit_idx integer;
    v_available_faktur_number varchar;
    v_cus_tax_code_status integer;
BEGIN

    -- Billing Code
    SELECT INTO v_code idc_getCurrentBillCode(v_source, v_ordered_by, v_vat_val, v_dept, v_inv_date, v_is_tax);
    IF v_ordered_by = 1 THEN v_do_no := 'D' || substr(v_code, 2, 12);
    ELSIF v_ordered_by = 2 THEN v_do_no := 'M' || substr(v_code, 2, 12); END IF;

    IF v_vat_val > 0 AND v_is_tax = 'IO' THEN
        SELECT INTO v_cus_tax_code_status cus_tax_code_status FROM idc_tb_customer WHERE cus_code = v_pajak_to;

        IF v_inv_date >= date '2013-04-01' THEN
            SELECT INTO v_fkit_idx MIN(fkit_idx) FROM idc_tb_faktur_pajak_item WHERE bill_code IS NULL and SUBSTR(fkit_number,9,2) = to_char(v_inv_date,'YY') AND fkit_ordered_by = v_ordered_by;
            SELECT INTO v_available_faktur_number fkit_number FROM idc_tb_faktur_pajak_item WHERE fkit_idx = v_fkit_idx;

            IF v_cus_tax_code_status = 1 THEN

                -- Jika customer Kimia Farma dan invoice diatas Rp 10jt, maka FK No 030
                IF SUBSTR(v_pajak_to,1,2) = '2F' AND v_total_amount > 10000000 THEN
                    UPDATE idc_tb_faktur_pajak_item SET fkit_number = '030'|| SUBSTR(v_available_faktur_number,4) WHERE fkit_number = v_available_faktur_number;
                    v_vat_inv_no = '030'|| SUBSTR(v_available_faktur_number,4);
                ELSE
                    v_vat_inv_no = v_available_faktur_number;
                END IF;

            ELSIF v_cus_tax_code_status = 2 THEN
                UPDATE idc_tb_faktur_pajak_item SET fkit_number = '020'|| SUBSTR(v_available_faktur_number,4) WHERE fkit_number = v_available_faktur_number;
                v_vat_inv_no = '020'|| SUBSTR(v_available_faktur_number,4);

                -- Jika customer pajak code 2 nilai total invoice dibawah Rp1jt, maka FK no 010
                IF v_total_amount < 1000000 THEN
                    UPDATE idc_tb_faktur_pajak_item SET fkit_number = '010'|| SUBSTR(v_available_faktur_number,4) WHERE fkit_number = v_available_faktur_number;
                    v_vat_inv_no = '010'|| SUBSTR(v_available_faktur_number,4);
                END IF;

            ELSIF v_cus_tax_code_status = 3 THEN
                UPDATE idc_tb_faktur_pajak_item SET fkit_number = '030'|| SUBSTR(v_available_faktur_number,4) WHERE fkit_number = v_available_faktur_number;
                v_vat_inv_no = '030'|| SUBSTR(v_available_faktur_number,4);

						ELSIF v_cus_tax_code_status = 4 THEN
                UPDATE idc_tb_faktur_pajak_item SET fkit_number = '040'|| SUBSTR(v_available_faktur_number,4) WHERE fkit_number = v_available_faktur_number;
                v_vat_inv_no = '040'|| SUBSTR(v_available_faktur_number,4);

            ELSIF v_cus_tax_code_status = 7 THEN
                UPDATE idc_tb_faktur_pajak_item SET fkit_number = '070'|| SUBSTR(v_available_faktur_number,4) WHERE fkit_number = v_available_faktur_number;
                v_vat_inv_no = '070'|| SUBSTR(v_available_faktur_number,4);
            END IF;
        ELSE
            v_vat_inv_no = '010.000-' || substr(v_code, 12, 2) || '.' || lpad(substr(v_code,4,5), 8, '0');
        END IF;
    ELSIF v_vat_val > 0 AND v_is_tax = 'IP' THEN
        v_vat_inv_no = v_code;
    END IF;

    if v_vat_val > 0 then v_type := 1;
                 else v_type := 2;
    end if;

    if v_sj_code is null or v_sj_code = '' then
        v_sj_code_adj = 'J' || substr(v_code,2,12);
        v_sj_date_adj = v_inv_date;
    else
        v_sj_code_adj = v_sj_code;
        v_sj_date_adj = v_sj_date;
    end if;

    -- Adjust variable
    if v_do_date is null or v_do_date = ''
        then v_do_date_adj=null;
        else v_do_date_adj=v_do_date;end if;
    if v_po_date is null or v_po_date = ''
        then v_po_date_adj=null;
        else v_po_date_adj=v_po_date;end if;
    if v_payment_closing_on is null or v_payment_closing_on = ''
        then v_payment_closing_on_adj=null;
        else v_payment_closing_on_adj=v_payment_closing_on;end if;
    if v_payment_giro_due is null or v_payment_giro_due = ''
        then v_payment_giro_due_adj=null;
        else v_payment_giro_due_adj=v_payment_giro_due;end if;
    if v_payment_giro_issue is null or v_payment_giro_issue = ''
        then v_payment_giro_issue_adj=null;
        else v_payment_giro_issue_adj=v_payment_giro_issue;end if;
    if v_tukar_faktur_date is null or v_tukar_faktur_date = ''
        then v_tukar_faktur_date_adj=null;
        else v_tukar_faktur_date_adj=v_tukar_faktur_date;end if;
    if v_sales_from is null or v_sales_from = ''
        then v_sales_from_adj=null;
        else v_sales_from_adj=v_sales_from;end if;
    if v_sales_to is null or v_sales_to = ''
        then v_sales_to_adj=null;
        else v_sales_to_adj=v_sales_to;end if;

    -- Insert into idc_tb_billing
    INSERT INTO idc_tb_billing(
        bill_code, bill_dept, bill_inv_date, bill_vat_inv_no, bill_sj_code, bill_sj_date, bill_po_no, bill_po_date,
        bill_received_by, bill_cus_to, bill_cus_to_name, bill_cus_to_attn, bill_cus_to_address, bill_npwp,
        bill_ship_to, bill_ship_to_name, bill_pajak_to, bill_pajak_to_name, bill_pajak_to_address, bill_vat, bill_responsible_by,
        bill_delivery_chk, bill_delivery_by, bill_delivery_warehouse, bill_delivery_franco, bill_delivery_freight_charge,
        bill_payment_chk, bill_payment_widthin_days, bill_payment_sj_inv_fp_tender,
        bill_payment_closing_on, bill_payment_for_the_month_week, bill_payment_cash_by,
        bill_payment_check_by, bill_payment_transfer_by, bill_payment_giro_issue,
        bill_payment_giro_due, bill_payment_bank, bill_payment_bank_address, bill_lastupdated_by_account,
        bill_discount, bill_total_billing, bill_total_billing_rev, bill_remain_amount, bill_signature_by, bill_signature_pajak_by,
        bill_paper_format, bill_tukar_faktur_date, bill_amount_qty_unit_price, bill_type_pajak,
        bill_is_consinyasi, bill_sales_from, bill_sales_to, bill_do_no, bill_do_date, bill_remark, bill_type_invoice,
        bill_type_billing, bill_ordered_by
    ) VALUES (
        v_code, v_dept, v_inv_date, v_vat_inv_no, v_sj_code_adj, v_sj_date_adj, v_po_no, v_po_date_adj,
        v_received_by, v_cus_to, v_cus_name, v_cus_attn, v_cus_address, v_cus_npwp,
        v_ship_to, v_ship_name, v_pajak_to, v_pajak_name, v_pajak_address, v_vat_val, v_ship_to_responsible_by,
        v_delivery_chk, v_delivery_by, v_delivery_warehouse, v_delivery_franco, v_delivery_freight_charge,
        v_payment_chk, v_payment_widthin_days, v_payment_sj_inv_fp_tender,
        v_payment_closing_on_adj, v_payment_for_the_month_week, v_payment_cash_by,
        v_payment_check_by, v_payment_transfer_by, v_payment_giro_issue_adj,
        v_payment_giro_due_adj, v_bank, v_bank_address, v_lastupdated_by_account,
        v_disc, v_total_amount, v_total_amount, v_total_amount, v_signature_by, v_signature_pajak_by,
        v_paper_format, v_tukar_faktur_date_adj, v_amount_before_vat, v_is_tax,
        v_is_cons, v_sales_from_adj, v_sales_to_adj, v_do_no, v_do_date_adj, v_remark, v_type_invoice,
        v_type_bill, v_ordered_by
    );

    -- Insert idc_tb_billing_item
    WHILE v_cus_it_code[v_i] IS NOT NULL LOOP
        SELECT INTO v_icat_midx icat_midx FROM idc_tb_item WHERE it_code=v_cus_it_code[v_i];
        SELECT INTO v_it_type icat_midx FROM idc_tb_item WHERE it_code=v_cus_it_code[v_i];

        INSERT INTO idc_tb_billing_item (
            bill_code, cus_code, biit_inv_date, biit_qty, biit_unit_price, biit_remark,
            it_code, icat_midx, it_model_no, it_type, it_desc, biit_sl_idx
        ) VALUES (
            v_code, v_cus_to, v_inv_date, v_cus_it_qty[v_i], v_cus_it_unit_price[v_i], v_cus_it_remark[v_i],
            v_cus_it_code[v_i], v_icat_midx, v_cus_it_model_no[v_i], v_it_type, v_cus_it_desc[v_i], v_cus_it_sl_idx[v_i]
        );
        v_i := v_i + 1;
    END LOOP;

    IF v_type_invoice = 0 THEN
        -- Insert idc_tb_booking
        INSERT INTO idc_tb_booking(book_code, book_dept, cus_code, book_doc_ref, book_date, book_doc_type, book_type, book_ordered_by, book_received_by)
        VALUES (v_do_no, v_dept, v_ship_to, v_code, v_inv_date, 1, v_type, v_ordered_by, v_received_by);

        v_cur_book_idx := currval('idc_tb_booking_book_idx_seq');
        -- Insert idc_tb_booking_item
        WHILE v_wh_it_code[v_j] IS NOT NULL LOOP
            INSERT INTO idc_tb_booking_item (
                book_idx, it_code, boit_it_code_for, boit_type,
                boit_qty, boit_function, boit_remark
            ) VALUES (
                v_cur_book_idx, v_wh_it_code[v_j], v_wh_it_code_for[v_j], 0,
                v_wh_it_qty[v_j], v_wh_it_function[v_j], v_wh_it_remark[v_j]
            );
            v_j := v_j + 1;
        END LOOP;
    END IF;

    IF v_type_bill = 3 THEN
        WHILE v_sl_date[v_k] IS NOT NULL LOOP
            INSERT INTO idc_tb_billing_sales (
                bill_code, bisl_date, cus_code, bisl_sl_faktur_no, bisl_lop_no, bisl_amount
            ) VALUES (
                v_code, v_sl_date[v_k]::date, v_sl_cus_code[v_k], v_sl_faktur_no[v_k], v_sl_lop_no[v_k], v_sl_amount[v_k]
            );
            v_k := v_k + 1;
        END LOOP;
    END IF;

    IF v_vat_val > 0 AND v_is_tax = 'IO' THEN
        IF v_inv_date >= date '2013-04-01' THEN
            UPDATE idc_tb_faktur_pajak_item SET bill_code = v_code WHERE fkit_idx = v_fkit_idx;
        END IF;
    END IF;

    RETURN v_code||'-'||v_cur_book_idx;
END;
$$
;

alter function idc_insertbilling(varchar, integer, integer, integer, varchar, integer, varchar, varchar, date, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, integer, varchar, integer, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, numeric, integer, integer, integer, varchar, varchar, varchar, integer, integer, integer, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, boolean, varchar, varchar, varchar, character varying[], character varying[], numeric[], numeric[], character varying[], character varying[], character varying[], character varying[], character varying[], character varying[], numeric[], character varying[], character varying[], character varying[], integer[], numeric[], character varying[], character varying[]) owner to dskim
;

create function idc_insertborrow(v_cur_out_idx integer, v_it_code character varying, v_cus_code character varying, v_out_code character varying, v_do_no character varying, v_issued_date date, v_cfm_by_account character varying, v_out_type integer, v_out_type_inverse integer, v_different_qty numeric) returns integer
	language plpgsql
as $$
DECLARE
	v_cur_bor_idx integer := 0;
	v_log_code varchar;
BEGIN

	insert into idc_tb_borrow (out_idx, it_code, cus_code, bor_code, bor_from_wh, bor_to_wh, bor_from_type, bor_to_type, bor_qty)
	values (v_cur_out_idx, v_it_code, v_cus_code, v_out_code, 1,1, v_out_type_inverse, v_out_type, v_different_qty);
	v_cur_bor_idx := currval('idc_tb_borrow_bor_idx_seq');
	select into v_log_code idc_insertStockLog(
		v_it_code, 1, v_out_type_inverse, 25, v_cur_bor_idx, v_do_no, v_issued_date,
		v_cfm_by_account, false, v_different_qty
	);

	insert into idc_tb_enter (bor_idx,it_code,ent_wh_location,ent_qty,ent_type) values (v_cur_bor_idx, v_it_code, 1, v_different_qty,v_out_type);
	select into v_log_code idc_insertStockLog(
		v_it_code, 1, v_out_type, 16, v_cur_bor_idx, v_do_no, v_issued_date,
		v_cfm_by_account, true, v_different_qty
	);

	RETURN v_cur_bor_idx;
END;
$$
;

alter function idc_insertborrow(integer, varchar, varchar, varchar, varchar, date, varchar, integer, integer, numeric) owner to dskim
;

create function idc_insertcustomercomplain(v_date date, v_customer character varying, v_category character varying, v_complain_desc character varying, v_action character varying, v_remark character varying, v_created_by_account character varying) returns integer
	language plpgsql
as $$
DECLARE
	v_idx integer;
BEGIN

	INSERT INTO idc_tb_customer_complain(
		cp_date, cp_customer, cp_category, cp_complain_desc, cp_complain_completion, cp_remark, cp_created_by_account
	) VALUES (
		v_date, v_customer, v_category, v_complain_desc, v_action, v_remark, v_created_by_account
	);
	v_idx := currval('idc_tb_customer_complain_cp_idx_seq');

	RETURN v_idx;

END;
$$
;

alter function idc_insertcustomercomplain(date, varchar, varchar, varchar, varchar, varchar, varchar) owner to dskim
;

create function idc_insertdf(v_dept character varying, v_do_date date, v_issued_by character varying, v_issued_date date, v_received_by character varying, v_type_item integer, v_ordered_by integer, v_cus_to character varying, v_cus_name character varying, v_cus_address character varying, v_ship_to character varying, v_ship_name character varying, v_bill_code character varying, v_bill_date text, v_lastupdated_by_account character varying, v_delivery_by character varying, v_delivery_warehouse character varying, v_delivery_franco character varying, v_delivery_freight_charge numeric, v_remark character varying, v_wh_it_code character varying[], v_wh_it_code_for character varying[], v_wh_it_qty numeric[], v_wh_it_function numeric[], v_wh_it_remark character varying[], v_cus_it_code character varying[], v_cus_it_qty numeric[], v_cus_it_remark character varying[]) returns character varying
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_j integer := 1;
	v_code varchar;
	v_sj_no varchar;
	v_cur_book_idx integer := 0;
	v_bill_date_adj date;
BEGIN

	/* Set variable */
	SELECT INTO v_code idc_getCurrentDFCode(v_dept, v_do_date);
	v_sj_no := 'S' || substr(v_code, 2, 11);

	if v_bill_date is null or v_bill_date = ''
		then v_bill_date_adj=null;
		else v_bill_date_adj=v_bill_date; end if;

	/* Insert idc_tb_df */
	INSERT INTO idc_tb_df(
		df_code, df_dept, df_date, df_issued_by, df_issued_date, df_received_by, df_type_item,
		df_lastupdated_by_account, df_lastupdated_timestamp, df_do_no, df_do_date, df_sj_no, df_sj_date,
		df_cus_to, df_cus_name, df_cus_address, df_ship_to, df_ship_name,
		df_delivery_warehouse, df_delivery_franco, df_delivery_by, df_delivery_freight_charge, df_remark
	) VALUES (
		v_code, v_dept, v_do_date, v_issued_by, v_issued_date, v_received_by, v_type_item,
		v_lastupdated_by_account, CURRENT_TIMESTAMP, v_code, v_do_date, v_sj_no, v_do_date,
		v_cus_to, v_cus_name, v_cus_address, v_ship_to, v_ship_name,
		v_delivery_warehouse, v_delivery_franco, v_delivery_by, v_delivery_freight_charge, v_remark
	);

	/* Insert idc_tb_df_item */
	WHILE v_cus_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_df_item (
			df_code, it_code, dfit_qty, dfit_remark
		) VALUES (
			v_code, v_cus_it_code[v_i], v_cus_it_qty[v_i], v_cus_it_remark[v_i]
		);
		v_i := v_i + 1;
	END LOOP;

	/* Insert idc_tb_booking */
	INSERT INTO idc_tb_booking(book_code, book_dept, cus_code, book_doc_ref, book_date, book_doc_type, book_type, book_received_by, book_ordered_by)
	VALUES (v_code, v_dept, v_ship_to, v_code, v_do_date, 4, v_type_item, v_received_by, v_ordered_by);

	v_cur_book_idx := currval('idc_tb_booking_book_idx_seq');
	/* Insert idc_tb_booking_item */
	WHILE v_wh_it_code[v_j] IS NOT NULL LOOP
		INSERT INTO idc_tb_booking_item (
			book_idx, it_code, boit_it_code_for, boit_type,
			boit_qty, boit_function, boit_remark
		) VALUES (
			v_cur_book_idx, v_wh_it_code[v_j], v_wh_it_code_for[v_j], 0,
			v_wh_it_qty[v_j], v_wh_it_function[v_j], v_wh_it_remark[v_j]
		);
		v_j := v_j + 1;
	END LOOP;

	RETURN v_code||'-'||v_cur_book_idx;
END;
$$
;

alter function idc_insertdf(varchar, date, varchar, date, varchar, integer, integer, varchar, varchar, varchar, varchar, varchar, varchar, text, varchar, varchar, varchar, varchar, numeric, varchar, character varying[], character varying[], numeric[], numeric[], character varying[], character varying[], numeric[], character varying[]) owner to dskim
;

create function idc_insertdr(v_dept character varying, v_do_date date, v_issued_by character varying, v_issued_date date, v_received_by character varying, v_type_item integer, v_ordered_by integer, v_cus_to character varying, v_cus_name character varying, v_cus_address character varying, v_ship_to character varying, v_ship_name character varying, v_turn_code character varying, v_turn_date text, v_lastupdated_by_account character varying, v_delivery_by character varying, v_delivery_warehouse character varying, v_delivery_franco character varying, v_delivery_freight_charge numeric, v_remark character varying, v_wh_it_code character varying[], v_wh_it_code_for character varying[], v_wh_it_qty numeric[], v_wh_it_function numeric[], v_wh_it_remark character varying[], v_cus_it_code character varying[], v_cus_it_qty numeric[], v_cus_it_remark character varying[]) returns character varying
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_j integer := 1;
	v_code varchar;
	v_sj_no varchar;
	v_cur_book_idx integer := 0;
	v_turn_date_adj date;
BEGIN

	/* Set variable */
	SELECT INTO v_code idc_getCurrentDRCode(v_dept, v_ordered_by, v_do_date);
	v_sj_no := 'S' || substr(v_code, 2, 11);

	if v_turn_date is null or v_turn_date = ''
		then v_turn_date_adj=null;
		else v_turn_date_adj=v_turn_date; end if;

	/* Insert idc_tb_dr */
	INSERT INTO idc_tb_dr(
		dr_code, dr_dept, dr_date, dr_issued_by, dr_issued_date, dr_received_by, dr_type_item,
		dr_lastupdated_by_account, dr_lastupdated_timestamp, dr_do_no, dr_do_date, dr_sj_no, dr_sj_date,
		dr_cus_to, dr_cus_name, dr_cus_address, dr_ship_to, dr_ship_name,
		dr_delivery_warehouse, dr_delivery_franco, dr_delivery_by, dr_delivery_freight_charge, dr_remark,
		dr_turn_code, dr_turn_date, dr_ordered_by
	) VALUES (
		v_code, v_dept, v_do_date, v_issued_by, v_issued_date, v_received_by, v_type_item,
		v_lastupdated_by_account, CURRENT_TIMESTAMP, v_code, v_do_date, v_sj_no, v_do_date,
		v_cus_to, v_cus_name, v_cus_address, v_ship_to, v_ship_name,
		v_delivery_warehouse, v_delivery_franco, v_delivery_by, v_delivery_freight_charge, v_remark,
		v_turn_code, v_turn_date_adj, v_ordered_by
	);

	/* Insert idc_tb_dr_item */
	WHILE v_cus_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_dr_item (
			dr_code, it_code, drit_qty, drit_remark
		) VALUES (
			v_code, v_cus_it_code[v_i], v_cus_it_qty[v_i], v_cus_it_remark[v_i]
		);
		v_i := v_i + 1;
	END LOOP;

	/* Insert idc_tb_booking */
	INSERT INTO idc_tb_booking(book_code, book_dept, cus_code, book_doc_ref, book_date, book_doc_type, book_type, book_received_by, book_ordered_by)
	VALUES (v_code, v_dept, v_ship_to, v_code, v_do_date, 5, v_type_item, v_received_by, v_ordered_by);

	v_cur_book_idx := currval('idc_tb_booking_book_idx_seq');
	/* Insert idc_tb_booking_item */
	WHILE v_wh_it_code[v_j] IS NOT NULL LOOP
		INSERT INTO idc_tb_booking_item (
			book_idx, it_code, boit_it_code_for, boit_type,
			boit_qty, boit_function, boit_remark
		) VALUES (
			v_cur_book_idx, v_wh_it_code[v_j], v_wh_it_code_for[v_j], 0,
			v_wh_it_qty[v_j], v_wh_it_function[v_j], v_wh_it_remark[v_j]
		);
		v_j := v_j + 1;
	END LOOP;

	RETURN v_code||'-'||v_cur_book_idx;
END;
$$
;

alter function idc_insertdr(varchar, date, varchar, date, varchar, integer, integer, varchar, varchar, varchar, varchar, varchar, varchar, text, varchar, varchar, varchar, varchar, numeric, varchar, character varying[], character varying[], numeric[], numeric[], character varying[], character varying[], numeric[], character varying[]) owner to dskim
;

create function idc_insertdt(v_dept character varying, v_do_date date, v_issued_by character varying, v_issued_date date, v_received_by character varying, v_type_item integer, v_ordered_by integer, v_cus_to character varying, v_cus_name character varying, v_cus_address character varying, v_ship_to character varying, v_ship_name character varying, v_bill_code character varying, v_bill_date text, v_lastupdated_by_account character varying, v_delivery_by character varying, v_delivery_warehouse character varying, v_delivery_franco character varying, v_delivery_freight_charge numeric, v_remark character varying, v_wh_it_code character varying[], v_wh_it_code_for character varying[], v_wh_it_qty numeric[], v_wh_it_function numeric[], v_wh_it_remark character varying[], v_cus_it_code character varying[], v_cus_it_qty numeric[], v_cus_it_remark character varying[]) returns character varying
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_j integer := 1;
	v_code varchar;
	v_sj_no varchar;
	v_cur_book_idx integer := 0;
	v_bill_date_adj date;
BEGIN

	/* Set variable */
	SELECT INTO v_code idc_getCurrentDTCode(v_dept, v_do_date);
	v_sj_no := 'S' || substr(v_code, 2, 11);

	if v_bill_date is null or v_bill_date = ''
		then v_bill_date_adj=null;
		else v_bill_date_adj=v_bill_date; end if;

	/* Insert idc_tb_dt */
	INSERT INTO idc_tb_dt(
		dt_code, dt_dept, dt_date, dt_issued_by, dt_issued_date, dt_received_by, dt_type_item,
		dt_lastupdated_by_account, dt_lastupdated_timestamp, dt_do_no, dt_do_date, dt_sj_no, dt_sj_date,
		dt_cus_to, dt_cus_name, dt_cus_address, dt_ship_to, dt_ship_name,
		dt_delivery_warehouse, dt_delivery_franco, dt_delivery_by, dt_delivery_freight_charge, dt_remark
	) VALUES (
		v_code, v_dept, v_do_date, v_issued_by, v_issued_date, v_received_by, v_type_item,
		v_lastupdated_by_account, CURRENT_TIMESTAMP, v_code, v_do_date, v_sj_no, v_do_date,
		v_cus_to, v_cus_name, v_cus_address, v_ship_to, v_ship_name,
		v_delivery_warehouse, v_delivery_franco, v_delivery_by, v_delivery_freight_charge, v_remark
	);

	/* Insert idc_tb_dt_item */
	WHILE v_cus_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_dt_item (
			dt_code, it_code, dtit_qty, dtit_remark
		) VALUES (
			v_code, v_cus_it_code[v_i], v_cus_it_qty[v_i], v_cus_it_remark[v_i]
		);
		v_i := v_i + 1;
	END LOOP;

	/* Insert idc_tb_booking */
	INSERT INTO idc_tb_booking(book_code, book_dept, cus_code, book_doc_ref, book_date, book_doc_type, book_type, book_received_by, book_ordered_by)
	VALUES (v_code, v_dept, v_ship_to, v_code, v_do_date, 3, v_type_item, v_received_by, v_ordered_by);

	v_cur_book_idx := currval('idc_tb_booking_book_idx_seq');
	/* Insert idc_tb_booking_item */
	WHILE v_wh_it_code[v_j] IS NOT NULL LOOP
		INSERT INTO idc_tb_booking_item (
			book_idx, it_code, boit_it_code_for, boit_type,
			boit_qty, boit_function, boit_remark
		) VALUES (
			v_cur_book_idx, v_wh_it_code[v_j], v_wh_it_code_for[v_j], 0,
			v_wh_it_qty[v_j], v_wh_it_function[v_j], v_wh_it_remark[v_j]
		);
		v_j := v_j + 1;
	END LOOP;

	RETURN v_code||'-'||v_cur_book_idx;
END;
$$
;

alter function idc_insertdt(varchar, date, varchar, date, varchar, integer, integer, varchar, varchar, varchar, varchar, varchar, varchar, text, varchar, varchar, varchar, varchar, numeric, varchar, character varying[], character varying[], numeric[], numeric[], character varying[], character varying[], numeric[], character varying[]) owner to dskim
;

create function idc_insertevent(v_nama_acara character varying, v_tanggal_acara date, v_tempat_acara character varying, v_nama_peyelenggara character varying) returns character varying
	language plpgsql
as $$
DECLARE
	v_cur_ev_idx integer;
BEGIN
	INSERT INTO idc_tb_event(ev_nama_acara, ev_tanggal_acara, ev_tempat_acara, ev_penyelenggara)
	VALUES(v_nama_acara, v_tanggal_acara, v_tempat_acara, v_nama_peyelenggara);
	v_cur_ev_idx := currval('idc_tb_event_ev_idx_seq');
	RETURN v_cur_ev_idx;
END;
$$
;

alter function idc_insertevent(varchar, date, varchar, varchar) owner to dskim
;

create function idc_inserteventpeserta(v_code integer, v_nama character varying, v_alamat character varying, v_kota character varying, v_kode_pos character varying, v_jns_kelamin character varying, v_usia integer, v_telepon character varying, v_handphone character varying, v_email character varying, v_jns_alkes character varying, v_lastupdated_by_account character varying, v_td_sistolik integer, v_td_diastolik integer, v_gd_sewaktu integer, v_gd_puasa integer, v_kt_berat_badan numeric, v_kt_tinggi_badan numeric, v_kt_lemak_tubuh numeric, v_kt_bmi numeric, v_kt_lemak_perut numeric, v_kt_bmr integer, v_kt_lemak_subkutan numeric, v_kt_otot_rangka numeric, v_kt_klasifikasi_umur_tubuh integer) returns character varying
	language plpgsql
as $$
DECLARE
	v_peserta_code varchar;
BEGIN

	SELECT INTO v_peserta_code idc_getCurrentPesertaCode(current_date);

	INSERT INTO idc_tb_event_peserta(
		ev_idx, evp_code, evp_nama, evp_jenis_kelamin, evp_usia,
		evp_contact_telepon, evp_contact_handphone, evp_contact_email, evp_contact_alamat,
		evp_kota, evp_pos_kode, evp_alat, evp_updated_by_account,
		evp_sistolik, evp_diastolik, evp_glukosa_darah_sewaktu, evp_glukosa_darah_puasa,
		evp_berat_badan, evp_tinggi_badan, evp_lemak_tubuh, evp_bmi,
		evp_lemak_perut, evp_bmr, evp_lemak_subkutan, evp_otot_rangka, evp_klasifikasi_umur_tubuh
	) VALUES(
		v_code, v_peserta_code, v_nama, v_jns_kelamin, v_usia,
		v_telepon, v_handphone, v_email, v_alamat,
		v_kota, v_kode_pos, v_jns_alkes, v_lastupdated_by_account,
		v_td_sistolik, v_td_diastolik, v_gd_sewaktu, v_gd_puasa,
		v_kt_berat_badan, v_kt_tinggi_badan, v_kt_lemak_tubuh, v_kt_bmi,
		v_kt_lemak_perut, v_kt_bmr, v_kt_lemak_subkutan, v_kt_otot_rangka, v_kt_klasifikasi_umur_tubuh
	);

	RETURN v_peserta_code;
END;
$$
;

alter function idc_inserteventpeserta(integer, varchar, varchar, varchar, varchar, varchar, integer, varchar, varchar, varchar, varchar, varchar, integer, integer, integer, integer, numeric, numeric, numeric, numeric, numeric, integer, numeric, numeric, integer) owner to dskim
;

create function idc_insertfakturno(v_ordered_by integer, v_year character varying, v_digit character varying, v_from integer, v_to integer) returns integer
	language plpgsql
as $$
DECLARE
    v_val integer := 0;
    v_i integer := v_from;
    v_fk_idx integer;
BEGIN

    INSERT INTO idc_tb_faktur_pajak (fk_year, fk_digit, fk_from, fk_to, fk_ordered_by) VALUES (v_year, v_digit, v_from, v_to, v_ordered_by);
    v_fk_idx := currval('idc_tb_faktur_pajak_fk_idx_seq');

    FOR v_i IN v_from..v_to LOOP
        INSERT INTO idc_tb_faktur_pajak_item (fk_idx, fkit_number, fkit_ordered_by)
        VALUES (v_fk_idx, '010.'||v_digit||'-'||v_year||'.'||lpad(v_i::text, 8, '0'), v_ordered_by);
        v_i := v_i + 1;
    END LOOP;

    RETURN v_val;
END;
$$
;

alter function idc_insertfakturno(integer, varchar, varchar, integer, integer) owner to dskim
;

create function idc_insertitem(v_code character varying, v_midx integer, v_model_no character varying, v_type character varying, v_desc character varying, v_user_price numeric, v_date_from date, v_remark character varying, v_item_type integer, v_has_ed boolean, v_account character varying, v_is_vat_item_kurs numeric, v_is_vat_item_rupiah numeric, v_is_vat_item_date date, v_item_code character varying[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
BEGIN
	INSERT INTO idc_tb_item(it_code, icat_midx, it_model_no, it_type, it_desc, it_remark, it_ed, it_status)
	VALUES (v_code, v_midx, v_model_no, v_type, v_desc, v_remark, v_has_ed, v_item_type);

	INSERT INTO idc_tb_item_price (it_code, ip_date_from, ip_user_price, ip_created, ip_created_by)
	VALUES (v_code, v_date_from, v_user_price, CURRENT_TIMESTAMP, v_account);

	IF v_is_vat_item_kurs != 0 AND v_is_vat_item_rupiah != 0 THEN
		INSERT INTO idc_tb_item_price_net (it_code, ipn_date_from, ipn_price_kurs, ipn_price_dollar, ipn_created, ipn_created_by)
		VALUES (v_code, v_is_vat_item_date, v_is_vat_item_kurs, v_is_vat_item_rupiah, CURRENT_TIMESTAMP, v_account);
	END IF;

	INSERT INTO idc_tb_set_item(it_code, seit_code) VALUES (v_code, v_code);

	IF v_item_type != 0 THEN
		WHILE v_item_code[v_i] IS NOT NULL LOOP
			INSERT INTO idc_tb_set_item(it_code, seit_code) VALUES (v_item_code[v_i], v_code);
			v_i := v_i + 1;
		END LOOP;
	END IF;
END;
$$
;

alter function idc_insertitem(varchar, integer, varchar, varchar, varchar, numeric, date, varchar, integer, boolean, varchar, numeric, numeric, date, character varying[]) owner to dskim
;

create function idc_insertlocalsupplier(v_code character varying, v_internal_name character varying, v_full_name character varying, v_contact_name character varying, v_contact_position character varying, v_contact_phone character varying, v_contact_hphone character varying, v_contact_email character varying, v_phone character varying, v_fax character varying, v_address character varying) returns void
	language plpgsql
as $$
BEGIN

	INSERT INTO idc_tb_supplier_local(
		sp_code, sp_internal_name, sp_full_name, sp_contact_name, sp_contact_position,
		sp_contact_phone, sp_contact_hphone, sp_contact_email, sp_phone, sp_fax, sp_address
	) VALUES (
		v_code, v_internal_name, v_full_name, v_contact_name, v_contact_position,
		v_contact_phone, v_contact_hphone, v_contact_email, v_phone, v_fax, v_address
	);

END;
$$
;

alter function idc_insertlocalsupplier(varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar) owner to dskim
;

create function idc_insertorder(v_source character varying, v_type character varying, v_type_order integer, v_dept character varying, v_received_by character varying, v_confirm_by character varying, v_po_date date, v_po_no character varying, v_vat numeric, v_cus_to character varying, v_cus_to_attn character varying, v_cus_to_address character varying, v_ship_to character varying, v_ship_to_attn character varying, v_ship_to_address character varying, v_bill_to character varying, v_bill_to_attn character varying, v_bill_to_address character varying, v_price_discount numeric, v_price_chk integer, v_delivery_chk integer, v_delivery_by character varying, v_delivery_freight_charge numeric, v_payment_chk integer, v_payment_widthin_days integer, v_payment_closing_on text, v_payment_cash_by character varying, v_payment_check_by character varying, v_payment_transfer_by character varying, v_payment_giro_by character varying, v_sign_by character varying, v_remark character varying, v_lastupdated_by_account character varying, v_wh_it_code character varying[], v_wh_it_code_for character varying[], v_wh_it_qty numeric[], v_wh_it_function numeric[], v_wh_it_remark character varying[], v_cus_it_code character varying[], v_cus_it_unit_price numeric[], v_cus_it_qty integer[], v_cus_it_delivery date[], v_cus_it_remark character varying[]) returns character varying
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_j integer := 1;
	v_code varchar;
	v_do_no varchar;
	v_cur_book_idx integer := 0;
	v_wh_account varchar;
	v_wh_timestamp timestamp;
	v_payment_closing_on_adj date;
BEGIN

	SELECT INTO v_code idc_getCurrentOrdCode(v_source, v_dept, v_type, v_po_date);
	v_do_no := 'D' || substr(v_code,2,12);

	IF v_type_order = 0 THEN
		v_wh_account	= '';
		v_wh_timestamp	= NULL;
	ELSIF v_type_order = 1 THEN
		v_wh_account	= v_lastupdated_by_account;
		v_wh_timestamp	= CURRENT_TIMESTAMP;
	END IF;

	IF v_payment_closing_on IS NULL THEN
		v_payment_closing_on_adj = null;
	END IF;

	/* Insert idc_tb_order */
	INSERT INTO idc_tb_order(
		ord_code, ord_type, ord_lastupdated_by_account, ord_po_date, ord_dept,
		ord_po_no, ord_received_by, ord_confirm_by, ord_vat, ord_cus_to,
		ord_cus_to_attn, ord_cus_to_address, ord_ship_to, ord_ship_to_attn, ord_ship_to_address,
		ord_bill_to, ord_bill_to_attn, ord_bill_to_address, ord_price_discount, ord_price_chk,
		ord_delivery_chk, ord_delivery_by, ord_delivery_freight_charge, ord_payment_chk, ord_payment_widthin_days,
		ord_payment_closing_on, ord_payment_cash_by, ord_payment_check_by, ord_payment_transfer_by, ord_payment_giro_by,
		ord_sign_by, ord_remark, ord_do_no, ord_type_invoice, ord_cfm_wh_delivery_by_account, ord_cfm_wh_delivery_timestamp
	) VALUES (
		v_code, v_type, v_lastupdated_by_account, v_po_date, v_dept,
		v_po_no, v_received_by, v_confirm_by, v_vat, v_cus_to,
		v_cus_to_attn, v_cus_to_address, v_ship_to, v_ship_to_attn, v_ship_to_address,
		v_bill_to, v_bill_to_attn, v_bill_to_address, v_price_discount, v_price_chk,
		v_delivery_chk, v_delivery_by, v_delivery_freight_charge, v_payment_chk, v_payment_widthin_days,
		v_payment_closing_on_adj, v_payment_cash_by, v_payment_check_by, v_payment_transfer_by, v_payment_giro_by,
		v_sign_by, v_remark, v_do_no, v_type_order, v_wh_account, v_wh_timestamp
	);

	/* Insert idc_tb_order_item */
	IF v_type = 'OO' THEN
		WHILE v_cus_it_code[v_i] IS NOT NULL LOOP
			INSERT INTO idc_tb_order_item (
				ord_code, cus_code, odit_dept, it_code, odit_oo_qty, odit_qty, odit_unit_price, odit_delivery, odit_date, odit_remark
			) VALUES (
				v_code, v_cus_to, v_dept, v_cus_it_code[v_i], v_cus_it_qty[v_i], v_cus_it_qty[v_i],
				v_cus_it_unit_price[v_i], v_cus_it_delivery[v_i], v_po_date, v_cus_it_remark[v_i]
			);
			v_i := v_i + 1;
		END LOOP;
	ELSE
		WHILE v_cus_it_code[v_i] IS NOT NULL LOOP
			INSERT INTO idc_tb_order_item (
				ord_code, cus_code, odit_dept, it_code, odit_ok_qty, odit_qty, odit_unit_price, odit_delivery, odit_date, odit_remark
			) VALUES (
				v_code, v_cus_to, v_dept, v_cus_it_code[v_i], v_cus_it_qty[v_i], v_cus_it_qty[v_i],
				v_cus_it_unit_price[v_i], v_cus_it_delivery[v_i], v_po_date, v_cus_it_remark[v_i]
			);
			v_i := v_i + 1;
		END LOOP;
	END IF;

	IF v_type_order = 0 THEN
		/* Insert idc_tb_booking */
		INSERT INTO idc_tb_booking(book_code, book_dept, cus_code, book_doc_ref, book_date, book_doc_type, book_type, book_received_by)
		VALUES (v_do_no, v_dept, v_ship_to, v_code, v_po_date, 2, 2, v_received_by);
		v_cur_book_idx := currval('idc_tb_booking_book_idx_seq');

		/* Insert idc_tb_booking_item */
		WHILE v_wh_it_code[v_j] IS NOT NULL LOOP
			INSERT INTO idc_tb_booking_item (
				book_idx, it_code, boit_it_code_for, boit_type,
				boit_qty, boit_function, boit_remark
			) VALUES (
				v_cur_book_idx, v_wh_it_code[v_j], v_wh_it_code_for[v_j], 0,
				v_wh_it_qty[v_j], v_wh_it_function[v_j], v_wh_it_remark[v_j]
			);
			v_j := v_j + 1;
		END LOOP;
	END IF;

	RETURN v_code || '-' || v_cur_book_idx;

END;
$$
;

alter function idc_insertorder(varchar, varchar, integer, varchar, varchar, varchar, date, varchar, numeric, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, numeric, integer, integer, varchar, numeric, integer, integer, text, varchar, varchar, varchar, varchar, varchar, varchar, varchar, character varying[], character varying[], numeric[], numeric[], character varying[], character varying[], numeric[], integer[], date[], character varying[]) owner to dskim
;

create function idc_insertpllocal(v_po_code character varying, v_pl_no integer, v_pl_date date, v_issued_by character varying, v_delivery_date date, v_lastupdated_by_account character varying, v_remark character varying, v_it_code character varying[], v_plit_qty integer[]) returns void
	language plpgsql
as $$
DECLARE
v_i integer := 1;
BEGIN
	INSERT INTO idc_tb_pl_local(
		po_code, pl_no, pl_date, pl_issued_by, pl_delivery_date,
		pl_lastupdated_by_account, pl_lastupdated_timestamp, pl_remark
	) VALUES (
		v_po_code, v_pl_no, v_pl_date, v_issued_by, v_delivery_date,
		v_lastupdated_by_account, CURRENT_TIMESTAMP, v_remark
	);

	WHILE v_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_pl_local_item (po_code, pl_no, it_code, plit_qty)
		VALUES (v_po_code, v_pl_no, v_it_code[v_i], v_plit_qty[v_i]);
		v_i := v_i + 1;
	END LOOP;
END;
$$
;

alter function idc_insertpllocal(varchar, integer, date, varchar, date, varchar, varchar, character varying[], integer[]) owner to dskim
;

create function idc_insertpolocal(v_po_date date, v_po_type integer, v_deli_date date, v_sp_code character varying, v_sp_name character varying, v_sp_attn character varying, v_sp_phone character varying, v_sp_fax character varying, v_sp_address character varying, v_total_qty integer, v_total_amount numeric, v_vat numeric, v_text_add1 character varying, v_text_add2 character varying, v_total_add1 numeric, v_total_add2 numeric, v_says_in_word character varying, v_prepared_by character varying, v_confirmed_by character varying, v_approved_by character varying, v_remark character varying, v_lastupdated_by_account character varying, v_it_code character varying[], v_poit_unit character varying[], v_poit_unit_price numeric[], v_poit_qty integer[], v_poit_remark character varying[]) returns character varying
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_code varchar;
BEGIN

	SELECT INTO v_code idc_getCurrentPOLocalCode(v_sp_code, v_po_date);

	INSERT INTO idc_tb_po_local(
		po_code, po_date, sp_code, po_sp_name, po_sp_attn, po_sp_phone, po_sp_fax, po_sp_address,
		po_lastupdated_by_account, po_lastupdated_timestamp, po_type, po_delivery_date,
		po_prepared_by, po_confirmed_by, po_approved_by, po_total_qty, po_total_amount, po_says_in_words, po_remark,
		po_vat, po_text_charge1, po_text_charge2, po_total_charge1, po_total_charge2
	) VALUES (
		v_code, v_po_date, v_sp_code, v_sp_name, v_sp_attn, v_sp_phone, v_sp_fax, v_sp_address,
		v_lastupdated_by_account, CURRENT_TIMESTAMP, v_po_type, v_deli_date,
		v_prepared_by, v_confirmed_by, v_approved_by, v_total_qty, v_total_amount, v_says_in_word, v_remark,
		v_vat, v_text_add1, v_text_add2, v_total_add1, v_total_add2
	);

	WHILE v_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_po_local_item (po_code, it_code, poit_unit, poit_qty, poit_unit_price, poit_remark)
		VALUES (v_code, v_it_code[v_i], v_poit_unit[v_i], v_poit_qty[v_i], v_poit_unit_price[v_i], v_poit_remark[v_i]);
		v_i := v_i + 1;
	END LOOP;

	RETURN v_code;
END;
$$
;

alter function idc_insertpolocal(date, integer, date, varchar, varchar, varchar, varchar, varchar, varchar, integer, numeric, numeric, varchar, varchar, numeric, numeric, varchar, varchar, varchar, varchar, varchar, varchar, character varying[], character varying[], numeric[], integer[], character varying[]) owner to dskim
;

create function idc_insertregletter(v_source character varying, v_dept character, v_cus_code character varying, v_cus_attn character varying, v_amount numeric, v_stamp_pcs integer, v_reg_date date, v_reg_issued_by character varying, v_reg_type character varying, v_reg_send_to character varying, v_reg_pic character varying, v_reg_item character varying, v_reg_address character varying, v_remark character varying, v_reg_brief_summary character varying, v_reg_status character varying, v_reg_confirmed_date date, v_lastupdated_by_account character varying, v_type character varying[], v_file_name character varying[], v_file_path character varying[], v_file_type character varying[], v_file_desc character varying[], v_fee_desc character varying[], v_fee_amount numeric[]) returns character varying
	language plpgsql
as $$
DECLARE
  v_code varchar;
  v_is_charge boolean := false;
  v_i integer := 1;
  v_j integer := 1;
BEGIN

  SELECT INTO v_code idc_getCurrentLetterNo(v_source, v_dept, v_reg_date, v_reg_type);

  IF v_amount > 0 OR v_stamp_pcs > 0 THEN
    v_is_charge = true;
  END IF;

  INSERT INTO idc_tb_letter(
    lt_reg_no, lt_reg_date, lt_dept, lt_issued_by, lt_type_of_letter, lt_status_of_letter,
    lt_send_to, lt_pic, lt_item, lt_address, lt_confirm_date, lt_remark, lt_brief_summary, lt_lastupdated_by_account,
    cus_code, lt_cus_attn, lt_amount, lt_stamp, lt_is_charge
  ) VALUES (
    v_code, v_reg_date, v_dept, v_reg_issued_by, v_reg_type, v_reg_status,
    v_reg_send_to, v_reg_pic, v_reg_item, v_reg_address, v_reg_confirmed_date, v_remark, v_reg_brief_summary, v_lastupdated_by_account,
    v_cus_code, v_cus_attn, v_amount, v_stamp_pcs, v_is_charge
  );

  WHILE v_file_name[v_i] IS NOT NULL AND v_file_name[v_i] != '' LOOP
    INSERT INTO idc_tb_letter_file (lt_reg_no, ltf_type, ltf_file_name, ltf_file_path, ltf_file_type, ltf_file_desc)
    VALUES (v_code, v_type[v_i], v_file_name[v_i], v_file_path[v_i], v_file_type[v_i], v_file_desc[v_i]);
    v_i := v_i + 1;
  END LOOP;

  WHILE v_fee_desc[v_j] IS NOT NULL AND v_fee_desc[v_j] != '' LOOP
    INSERT INTO idc_tb_letter_item (lt_reg_no, lti_desc, lti_amount)
    VALUES (v_code, v_fee_desc[v_j], v_fee_amount[v_j]);
    v_j := v_j + 1;
  END LOOP;

  RETURN v_code;
END;
$$
;

alter function idc_insertregletter(varchar, char, varchar, varchar, numeric, integer, date, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, date, varchar, character varying[], character varying[], character varying[], character varying[], character varying[], character varying[], numeric[]) owner to dskim
;

create function idc_insertrequestdemo(v_source character varying, v_dept character varying, v_request_by character varying, v_request_date date, v_cus_code character varying, v_cus_name character varying, v_cus_address character varying, v_sign_by character varying, v_remark character varying, v_log_by_account character varying, v_it_code character varying[], v_it_returnable character varying[], v_it_qty numeric[], v_it_remark character varying[]) returns character varying
	language plpgsql
as $$
DECLARE
	v_code varchar;
	v_i integer := 1;
	v_returnable boolean;
BEGIN

	SELECT INTO v_code idc_getCurrentRequestDemoCode(v_source, v_dept, v_request_date);

	/* Insert tb_using_demo */
	INSERT INTO idc_tb_using_demo(
		use_code, use_request_by, use_request_date, use_dept, use_cus_to, use_cus_name, use_cus_address,
		use_lastupdated_by_account, use_lastupdated_timestamp, use_signature_by, use_remark
	) VALUES (
		v_code, v_request_by, v_request_date, v_dept, v_cus_code, v_cus_name, v_cus_address,
		v_log_by_account, CURRENT_TIMESTAMP, v_sign_by, v_remark
	);

	/* Insert tb_using_demo_item */
	WHILE v_it_code[v_i] IS NOT NULL AND v_it_code[v_i] != '' LOOP
		IF v_it_returnable[v_i] = '0' THEN
			v_returnable = true;
		ELSIF v_it_returnable[v_i] = '1' THEN
			v_returnable = false;
		END IF;

		INSERT INTO idc_tb_using_demo_item (use_code, it_code, usit_returnable, usit_qty, usit_remark)
		VALUES (v_code, v_it_code[v_i], v_returnable, v_it_qty[v_i], v_it_remark[v_i]);
		v_i := v_i + 1;
	END LOOP;

	RETURN v_code;
END;
$$
;

alter function idc_insertrequestdemo(varchar, varchar, varchar, date, varchar, varchar, varchar, varchar, varchar, varchar, character varying[], character varying[], numeric[], character varying[]) owner to dskim
;

create function idc_insertrequeststocktodemo(v_source character varying, v_issued_by character varying, v_issued_date date, v_log_by_account character varying, v_remark character varying, v_wh_it_code character varying[], v_wh_it_qty numeric[], v_wh_it_remark character varying[]) returns character varying
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_j integer := 1;
	v_code varchar;
	v_cur_book_idx integer := 0;
BEGIN

	SELECT INTO v_code idc_getCurrentRequestCode(v_source,v_issued_date);

	/* Insert tb_request */
	INSERT INTO idc_tb_request(
		req_code, req_issued_by, req_issued_date, req_remark,
		req_lastupdated_by_account, req_lastupdated_timestamp
	) VALUES (
		v_code, v_issued_by, v_issued_date, v_remark,
		v_log_by_account, CURRENT_TIMESTAMP
	);

	/* Insert tb_request_item */
	WHILE v_wh_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_request_item (req_code, it_code, rqit_type, rqit_qty, rqit_remark)
		VALUES (v_code, v_wh_it_code[v_i], 0, v_wh_it_qty[v_i], v_wh_it_remark[v_i]);
		v_i := v_i + 1;
	END LOOP;

	/* Insert tb_booking */
	INSERT INTO idc_tb_booking(book_code, book_dept, cus_code, book_doc_ref, book_date, book_doc_type, book_type, book_received_by)
	VALUES (v_code, 'M', '7CUS', v_code, v_issued_date, 6, 2, v_log_by_account);

	v_cur_book_idx := currval('idc_tb_booking_book_idx_seq');
	/* Insert tb_booking_item */
	WHILE v_wh_it_code[v_j] IS NOT NULL LOOP
		INSERT INTO idc_tb_booking_item (
			book_idx, it_code, boit_it_code_for, boit_type,
			boit_qty, boit_function, boit_remark
		) VALUES (
			v_cur_book_idx, v_wh_it_code[v_j], v_wh_it_code[v_j], 0,
			v_wh_it_qty[v_j], 1, v_wh_it_remark[v_j]
		);
		v_j := v_j + 1;
	END LOOP;

	RETURN v_code;
END;
$$
;

alter function idc_insertrequeststocktodemo(varchar, varchar, date, varchar, varchar, character varying[], numeric[], character varying[]) owner to dskim
;

create function idc_insertreturnbilling(v_source character varying, v_dept character varying, v_ordered_by integer, v_paper integer, v_return_condition integer, v_type_return character varying, v_return_date date, v_received_by character varying, v_ship_to_responsible_by integer, v_cus_to character varying, v_cus_name character varying, v_cus_attn character varying, v_cus_address character varying, v_cus_npwp character varying, v_ship_to character varying, v_ship_name character varying, v_bill_code character varying, v_bill_date text, v_is_vat integer, v_vat integer, v_faktur_no character varying, v_is_bill_paid integer, v_is_money_back integer, v_do_no character varying, v_do_date text, v_sj_no character varying, v_sj_date text, v_po_no character varying, v_po_date text, v_delivery_chk integer, v_payment_chk integer, v_delivery_by character varying, v_delivery_warehouse character varying, v_delivery_franco character varying, v_delivery_freight_charge integer, v_payment_widthin_days integer, v_payment_sj_inv_fp_tender character varying, v_payment_closing_on text, v_payment_for_the_month_week character varying, v_payment_cash_by character varying, v_payment_check_by character varying, v_payment_transfer_by character varying, v_payment_giro_due text, v_payment_giro_issue text, v_bank character varying, v_bank_address character varying, v_lastupdated_by_account character varying, v_disc numeric, v_total numeric, v_total_amount numeric, v_signature_by character varying, v_signature_pajak_by character varying, v_tukar_faktur_date text, v_remark character varying, v_wh_it_code character varying[], v_wh_it_code_for character varying[], v_wh_it_qty numeric[], v_wh_it_function numeric[], v_wh_it_remark character varying[], v_cus_it_code character varying[], v_cus_it_icat_midx integer[], v_cus_it_model_no character varying[], v_cus_it_desc character varying[], v_cus_it_qty numeric[], v_cus_it_unit_price numeric[], v_cus_it_remark character varying[]) returns character varying
	language plpgsql
as $$
DECLARE
    rec record;
    v_i integer := 1;
    v_j integer := 1;
    v_cur_std_idx integer := 0;
    v_cur_inc_idx integer := 0;
    v_code varchar;
    v_grand_amount numeric;
    v_method varchar;
    v_bank2 varchar;
    v_type_vat integer;
    v_bill_date_adj date;
    v_do_date_adj date;
    v_sj_date_adj date;
    v_po_date_adj date;
    v_payment_closing_on_adj date;
    v_payment_giro_due_adj date;
    v_payment_giro_issue_adj date;
    v_tukar_faktur_date_adj date;
BEGIN

    SELECT INTO v_code idc_getReturnBillingCode(v_source, v_ordered_by, v_vat, v_dept, v_return_date, v_type_return);

    /* Adjustment variable  */
    if v_bill_date is null or v_bill_date = ''
        then v_bill_date_adj=null;
        else v_bill_date_adj=v_bill_date;end if;
    if v_do_date is null or v_do_date = ''
        then v_do_date_adj=null;
        else v_do_date_adj=v_do_date;end if;
    if v_sj_date is null or v_sj_date = ''
        then v_sj_date_adj=null;
        else v_sj_date_adj=v_sj_date;end if;
    if v_po_date is null or v_po_date = ''
        then v_po_date_adj=null;
        else v_po_date_adj=v_po_date;end if;
    if v_payment_closing_on is null or v_payment_closing_on = ''
        then v_payment_closing_on_adj=null;
        else v_payment_closing_on_adj=v_payment_closing_on;end if;
    if v_payment_giro_due is null or v_payment_giro_due = ''
        then v_payment_giro_due_adj=null;
        else v_payment_giro_due_adj=v_payment_giro_due;end if;
    if v_payment_giro_issue is null or v_payment_giro_issue = ''
        then v_payment_giro_issue_adj=null;
        else v_payment_giro_issue_adj=v_payment_giro_issue;end if;
    if v_tukar_faktur_date is null or v_tukar_faktur_date = ''
        then v_tukar_faktur_date_adj=null;
        else v_tukar_faktur_date_adj=v_tukar_faktur_date;end if;

    /* Insert idc_tb_return */
    INSERT INTO idc_tb_return(
        turn_code, turn_dept, turn_return_date, turn_bill_code, turn_bill_inv_date, turn_bill_vat_inv_no,
        turn_sj_code, turn_sj_date, turn_po_no, turn_po_date, turn_received_by, turn_responsible_by,
        turn_cus_to, turn_cus_to_name, turn_cus_to_attn, turn_cus_to_address, turn_npwp,
        turn_ship_to, turn_ship_to_name,  turn_vat,
        turn_delivery_chk, turn_delivery_by, turn_delivery_warehouse, turn_delivery_franco, turn_delivery_freight_charge,
        turn_payment_chk, turn_payment_widthin_days, turn_payment_sj_inv_fp_tender,
        turn_payment_closing_on, turn_payment_for_the_month_week, turn_payment_cash_by,
        turn_payment_check_by, turn_payment_transfer_by, turn_payment_giro_issue,
        turn_payment_giro_due, turn_payment_bank, turn_payment_bank_address, turn_lastupdated_by_account,
        turn_discount, turn_amount_qty_unit_price, turn_total_return, turn_signature_by, turn_signature_pajak_by, turn_tukar_faktur_date,
        turn_return_condition, turn_type_return, turn_is_bill_paid, turn_is_money_back, /*turn_is_same_item,*/
        turn_remark, turn_bill_do_no, turn_bill_do_date, turn_paper, turn_ordered_by
    ) VALUES (
        v_code, v_dept, v_return_date, v_bill_code, v_bill_date_adj, v_faktur_no,
        v_sj_no, v_sj_date_adj, v_po_no, v_po_date_adj, v_received_by, v_ship_to_responsible_by,
        v_cus_to, v_cus_name, v_cus_attn, v_cus_address, v_cus_npwp,
        v_ship_to, v_ship_name, v_vat,
        v_delivery_chk, v_delivery_by, v_delivery_warehouse, v_delivery_franco, v_delivery_freight_charge,
        v_payment_chk, v_payment_widthin_days, v_payment_sj_inv_fp_tender,
        v_payment_closing_on_adj, v_payment_for_the_month_week, v_payment_cash_by,
        v_payment_check_by, v_payment_transfer_by, v_payment_giro_issue_adj,
        v_payment_giro_due_adj, v_bank, v_bank_address, v_lastupdated_by_account,
        v_disc, v_total, v_total_amount, v_signature_by, v_signature_pajak_by, v_tukar_faktur_date_adj,
        v_return_condition, v_type_return, v_is_bill_paid, v_is_money_back,
        v_remark, v_do_no, v_do_date_adj, v_paper, v_ordered_by
    );

    /* Insert idc_tb_return_item */
    WHILE v_cus_it_code[v_i] IS NOT NULL LOOP
        INSERT INTO idc_tb_return_item (
            turn_code, cus_code, it_code, it_model_no, it_desc, icat_midx,
            reit_return_date, reit_qty, reit_unit_price, reit_remark
        ) VALUES (
            v_code, v_cus_to, v_cus_it_code[v_i],v_cus_it_model_no[v_i], v_cus_it_desc[v_i], v_cus_it_icat_midx[v_i],
            v_return_date, v_cus_it_qty[v_i], v_cus_it_unit_price[v_i], v_cus_it_remark[v_i]);
        v_i := v_i + 1;
    END LOOP;

    /* influence to idc_tb_billing */
    IF v_return_condition = 2 THEN
        UPDATE idc_tb_billing SET
            bill_remain_amount      = bill_remain_amount - v_total_amount,
            bill_total_billing_rev  = bill_total_billing_rev - v_total_amount
        WHERE bill_code = v_bill_code;

    ELSIF v_return_condition = 3 THEN
        INSERT INTO idc_tb_deposit(cus_code, dep_cus_name, turn_code, dep_dept, dep_amount, dep_issued_date, dep_type)
        VALUES (v_cus_to, v_cus_name, v_code, v_dept, v_total_amount, v_return_date, 'return');

    ELSIF v_return_condition = 4 THEN
        IF v_payment_chk < 31 THEN
            v_method = 'cash';
        ELSIF v_payment_chk < 63 THEN
            v_method = 'check';
        ELSIF v_payment_chk < 127 THEN
            v_method = 'transfer';
            v_bank2  = v_bank;
        ELSIF v_payment_chk >= 128 THEN
            v_method = 'giro';
        END IF;

        /* Mengurangi Payment yang sudah ada sebelumnya */
        INSERT INTO idc_tb_payment (bill_code, cus_code, pay_dept, pay_date, pay_paid, pay_inputed_by, pay_remark, pay_note, pay_method, pay_bank)
        VALUES (v_bill_code, v_cus_to, v_dept, v_return_date, -v_total_amount, v_received_by, v_code, 'RETURN', v_method, v_bank2);

        /* Update total_amount yang ada di idc_tb_billing */
        UPDATE idc_tb_billing SET bill_remain_amount = 0 WHERE bill_code = v_bill_code;
    END IF;

    if v_vat > 0 then v_type_vat := 1;
    elsif v_vat <= 0 then v_type_vat := 2;
    end if;

    IF v_paper = 0 THEN
        /* Insert idc_tb_outstanding */
        INSERT INTO idc_tb_outstanding(std_dept, cus_code, std_doc_ref, std_date, std_doc_type, std_type, std_received_by, std_ordered_by)
        VALUES (v_dept, v_ship_to, v_code, v_return_date, 'Return Billing', v_type_vat, v_received_by, v_ordered_by);

        v_cur_std_idx := currval('idc_tb_outstanding_std_idx_seq');
        /* Insert idc_tb_outstanding_item */
        WHILE v_wh_it_code[v_j] IS NOT NULL LOOP
            INSERT INTO idc_tb_outstanding_item (
                std_idx, istd_type, it_code, istd_it_code_for, istd_qty, istd_function, istd_remark, istd_wh_location
            ) VALUES (
                v_cur_std_idx, v_type_vat, v_wh_it_code[v_j], v_wh_it_code_for[v_j],
                v_wh_it_qty[v_j], v_wh_it_function[v_j], v_wh_it_remark[v_j], 1
            );
            v_j := v_j + 1;
        END LOOP;

        /* Insert idc_tb_incoming */
        INSERT INTO idc_tb_incoming(inc_dept, cus_code, inc_std_idx, inc_doc_ref, inc_date, inc_doc_type, inc_type, inc_received_by, inc_ordered_by)
        VALUES (v_dept, v_ship_to, v_cur_std_idx, v_code, v_return_date, 'Return Billing', v_type_vat, v_received_by, v_ordered_by);

        v_cur_inc_idx := currval('idc_tb_incoming_inc_idx_seq');
        /* Insert idc_tb_incoming_item */
        FOR rec IN SELECT it_code, sum(istd_qty) AS qty FROM idc_tb_outstanding_item
        WHERE std_idx = v_cur_std_idx
        GROUP BY it_code ORDER BY it_code LOOP
            INSERT INTO idc_tb_incoming_item (
                inc_idx, init_type, it_code, init_qty, init_wh_location
            ) VALUES (
                v_cur_inc_idx, v_type_vat, rec.it_code, rec.qty, 1
            );
        END LOOP;
/*
    ELSIF v_paper = 1 THEN
        UPDATE idc_tb_return SET
            turn_cfm_wh_delivery_by_account = v_lastupdated_by_account,
            turn_cfm_wh_delivery_timestamp  = CURRENT_TIMESTAMP,
            turn_cfm_wh_by_account  = v_lastupdated_by_account,
            turn_cfm_wh_timestamp   = CURRENT_TIMESTAMP,
            turn_cfm_wh_date        = v_return_date
        WHERE turn_code = v_code;*/
    END IF;

    RETURN v_code||'-'||v_cur_std_idx;
END;
$$
;

alter function idc_insertreturnbilling(varchar, varchar, integer, integer, integer, varchar, date, varchar, integer, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, text, integer, integer, varchar, integer, integer, varchar, text, varchar, text, varchar, text, integer, integer, varchar, varchar, varchar, integer, integer, varchar, text, varchar, varchar, varchar, varchar, text, text, varchar, varchar, varchar, numeric, numeric, numeric, varchar, varchar, text, varchar, character varying[], character varying[], numeric[], numeric[], character varying[], character varying[], integer[], character varying[], character varying[], numeric[], numeric[], character varying[]) owner to dskim
;

create function idc_insertreturnborrow(v_bor_idx integer[], v_confirm_by_account character varying, v_ed_it_code character varying[], v_ed_it_location integer[], v_ed_it_type integer[], v_ed_it_date text[], v_ed_it_qty numeric[]) returns void
	language plpgsql
as $$
DECLARE
	rec record;
	v_i integer := 1;
	v_j integer := 1;
	v_cur_rebor_idx integer;
	v_log_code varchar;
	v_temp_to_type smallint;
	v_do_no varchar;
BEGIN

	WHILE v_bor_idx[v_i] IS NOT NULL LOOP
		FOR rec IN SELECT
			out_idx, out_doc_ref, out_doc_type, out_issued_date,
			bor_idx, it_code, bor_from_wh, bor_to_wh, bor_from_type, bor_to_type, bor_qty
		FROM idc_tb_outgoing JOIN idc_tb_borrow USING(out_idx) WHERE bor_idx = v_bor_idx[v_i] LOOP
			UPDATE idc_tb_borrow SET
				bor_is_returned = true, bor_return_timestamp = current_timestamp
			WHERE bor_idx = rec.bor_idx;

			if(rec.out_doc_type in(1,2)) THEN v_do_no = 'D'|| substr(rec.out_doc_ref,2);
			else v_do_no = rec.out_doc_ref; END IF;

			INSERT INTO idc_tb_return_borrow (
				out_idx, bor_idx, it_code, rebor_from_wh, rebor_to_wh,
				rebor_from_type, rebor_to_type, rebor_qty, rebor_by_account
			) VALUES(
				rec.out_idx, rec.bor_idx, rec.it_code, rec.bor_to_wh, rec.bor_from_wh,
				rec.bor_to_type, rec.bor_from_type, rec.bor_qty, v_confirm_by_account
			);
			v_cur_rebor_idx := currval('idc_tb_return_borrow_rebor_idx_seq');

			SELECT INTO v_log_code idc_insertStockLog(
				rec.it_code, rec.bor_to_wh, rec.bor_to_type, 28, v_cur_rebor_idx,
				v_do_no, rec.out_issued_date, v_confirm_by_account, false, rec.bor_qty
			);

			SELECT INTO v_log_code idc_insertStockLog(
				rec.it_code, rec.bor_from_wh, rec.bor_from_type, 18, v_cur_rebor_idx,
				v_do_no, rec.out_issued_date, v_confirm_by_account, true, rec.bor_qty
			);
		END LOOP;
		v_i := v_i + 1;
	END LOOP;

	WHILE v_ed_it_code[v_j] != null OR v_ed_it_code[v_j] != ''  LOOP

		if(v_ed_it_type[v_j] = 1)			then v_temp_to_type=2;
		elsif(v_ed_it_type[v_j] = 2)		then v_temp_to_type=1;	END IF;

		INSERT INTO idc_tb_return_borrow_ed (
			it_code, rebed_from_wh, rebed_to_wh, rebed_from_type, rebed_to_type,
			rebed_expired_date, rebed_qty
		) VALUES(
			v_ed_it_code[v_j], v_ed_it_location[v_j], v_ed_it_location[v_j], v_ed_it_type[v_j], v_temp_to_type,
			v_ed_it_date[v_j]::date, v_ed_it_qty[v_j]
		);
		v_j := v_j + 1;
	END LOOP;

END;
$$
;

alter function idc_insertreturnborrow(integer[], varchar, character varying[], integer[], integer[], text[], numeric[]) owner to dskim
;

create function idc_insertreturndemo(v_source character varying, v_use_code character varying, v_dept character varying, v_return_by character varying, v_return_date date, v_cus_code character varying, v_sign_by character varying, v_remark character varying, v_log_by_account character varying, v_it_code character varying[], v_it_qty numeric[]) returns character varying
	language plpgsql
as $$
DECLARE
	v_code varchar;
	v_i integer := 1;
BEGIN

	SELECT INTO v_code idc_getCurrentReturnDemoCode(v_source, v_dept, v_return_date);

	--Insert idc_tb_return_demo
	INSERT INTO idc_tb_return_demo (
		red_code, use_code, red_return_by, red_return_date, red_dept, red_cus_to,
		red_lastupdated_by_account, red_lastupdated_timestamp, red_signature_by, red_remark
	) VALUES (
		v_code, v_use_code, v_return_by, v_return_date, v_dept, v_cus_code,
		v_log_by_account, CURRENT_TIMESTAMP, v_sign_by, v_remark
	);

	--Insert idc_tb_return_demo_item
	WHILE v_it_code[v_i] IS NOT NULL LOOP
		IF v_it_qty[v_i] > 0 THEN
			INSERT INTO idc_tb_return_demo_item (red_code, it_code, rdit_qty)
			VALUES (v_code, v_it_code[v_i],v_it_qty[v_i]);
		END IF;
		v_i := v_i + 1;
	END LOOP;

	RETURN v_code;
END;
$$
;

alter function idc_insertreturndemo(varchar, varchar, varchar, varchar, date, varchar, varchar, varchar, varchar, character varying[], numeric[]) owner to dskim
;

create function idc_insertreturndt(v_date date, v_dept character varying, v_dt_code character varying, v_dt_date date, v_issued_by character varying, v_type_item integer, v_cus_to character varying, v_cus_name character varying, v_cus_address character varying, v_ship_to character varying, v_ship_name character varying, v_delivery_by character varying, v_delivery_warehouse character varying, v_delivery_franco character varying, v_delivery_freight_charge numeric, v_lastupdated_by_account character varying, v_remark character varying, v_wh_it_code character varying[], v_wh_it_type integer[], v_wh_it_coming_qty numeric[], v_wh_it_remark character varying[], v_cus_it_code character varying[], v_cus_it_qty numeric[], v_cus_it_remark character varying[]) returns character varying
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_j integer := 1;
	v_code varchar;
	v_cur_std_idx integer := 0;
	v_cur_inc_idx integer := 0;
	rec record;
BEGIN

	/* Set variable */
	SELECT INTO v_code idc_getCurrentReturnDTCode(v_dept, v_date);

	/* Insert idc_tb_dt */
	INSERT INTO idc_tb_return_dt(
		rdt_code, rdt_dept, rdt_date, rdt_issued_by, rdt_type_item,
		dt_code, rdt_dt_date, rdt_lastupdated_by_account, rdt_lastupdated_timestamp,
		rdt_cus_to, rdt_cus_name, rdt_cus_address, rdt_ship_to, rdt_ship_name,
		rdt_delivery_warehouse, rdt_delivery_franco, rdt_delivery_by,
		rdt_delivery_freight_charge, rdt_remark
	) VALUES (
		v_code, v_dept, v_date, v_issued_by, v_type_item,
		v_dt_code, v_dt_date, v_lastupdated_by_account, CURRENT_TIMESTAMP,
		v_cus_to, v_cus_name, v_cus_address, v_ship_to, v_ship_name,
		v_delivery_warehouse, v_delivery_franco, v_delivery_by,
		v_delivery_freight_charge, v_remark
	);

	/* Insert idc_tb_return_dt_item */
	WHILE v_cus_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_return_dt_item (rdt_code, it_code, rdtit_qty, rdtit_remark)
		VALUES (v_code, v_cus_it_code[v_i], v_cus_it_qty[v_i], v_cus_it_remark[v_i]);
		v_i := v_i + 1;
	END LOOP;

	/* Insert idc_tb_outstanding */
	INSERT INTO idc_tb_outstanding(std_dept, cus_code, std_doc_ref, std_date, std_doc_type, std_type, std_received_by)
	VALUES (v_dept, v_ship_to, v_code, v_date, 'Return DT', v_type_item, v_issued_by);

	v_cur_std_idx := currval('idc_tb_outstanding_std_idx_seq');
	/* Insert idc_tb_outstanding_item */
	WHILE v_wh_it_code[v_j] IS NOT NULL LOOP
		IF v_wh_it_coming_qty[v_j] > 0 THEN
			INSERT INTO idc_tb_outstanding_item (
				std_idx, istd_type, it_code, istd_it_code_for, istd_qty, istd_function, istd_remark, istd_wh_location
			) VALUES (
				v_cur_std_idx, v_wh_it_type[v_j], v_wh_it_code[v_j], v_wh_it_code[v_j],
				v_wh_it_coming_qty[v_j], 1, v_wh_it_remark[v_j], 1
			);
		END IF;
		v_j := v_j + 1;
	END LOOP;

	/* Insert idc_tb_incoming */
	INSERT INTO idc_tb_incoming(inc_dept, cus_code, inc_std_idx, inc_doc_ref, inc_date, inc_doc_type, inc_type, inc_received_by)
	VALUES (v_dept, v_ship_to, v_cur_std_idx, v_code, v_date, 'Return DT', v_type_item, v_issued_by);

	v_cur_inc_idx := currval('idc_tb_incoming_inc_idx_seq');
	/* Insert idc_tb_incoming_item */
	FOR rec IN SELECT it_code, istd_type, sum(istd_qty) AS qty FROM idc_tb_outstanding_item
	WHERE std_idx = v_cur_std_idx
	GROUP BY it_code, istd_type ORDER BY it_code LOOP
		INSERT INTO idc_tb_incoming_item (
			inc_idx, init_type, it_code, init_qty, init_wh_location
		) VALUES (
			v_cur_inc_idx, rec.istd_type, rec.it_code, rec.qty, 1
		);
	END LOOP;

	RETURN v_code||'-'||v_cur_std_idx;
END;
$$
;

alter function idc_insertreturndt(date, varchar, varchar, date, varchar, integer, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, numeric, varchar, varchar, character varying[], integer[], numeric[], character varying[], character varying[], numeric[], character varying[]) owner to dskim
;

create function idc_insertreturnorder(v_source character varying, v_ord_code character varying, v_paper integer, v_dept character varying, v_ord_date date, v_received_by character varying, v_confirm_by character varying, v_po_date date, v_po_no character varying, v_type character varying, v_vat numeric, v_cus_to character varying, v_cus_to_attn character varying, v_cus_to_address character varying, v_ship_to character varying, v_ship_to_attn character varying, v_ship_to_address character varying, v_bill_to character varying, v_bill_to_attn character varying, v_bill_to_address character varying, v_price_discount numeric, v_price_chk integer, v_delivery_chk integer, v_delivery_by character varying, v_delivery_freight_charge numeric, v_payment_chk integer, v_payment_widthin_days integer, v_payment_closing_on text, v_payment_cash_by character varying, v_payment_check_by character varying, v_payment_transfer_by character varying, v_payment_giro_by character varying, v_sign_by character varying, v_remark character varying, v_lastupdated_by_account character varying, v_revision_time integer, v_wh_it_code character varying[], v_wh_it_code_for character varying[], v_wh_it_qty numeric[], v_wh_it_function numeric[], v_wh_it_remark character varying[], v_cus_it_code character varying[], v_cus_it_qty numeric[], v_cus_it_unit_price numeric[], v_cus_it_remark character varying[]) returns character varying
	language plpgsql
as $$
DECLARE
	rec record;
	v_i integer := 1;
	v_code varchar;
	v_j integer := 1;
	v_cur_std_idx integer := 0;
	v_cur_inc_idx integer := 0;
	v_payment_closing_on_adj date;
BEGIN

	SELECT INTO v_code idc_getCurrentOrdReturnCode(v_source, v_dept, v_po_date, v_type);

	IF v_payment_closing_on IS NULL THEN
		v_payment_closing_on_adj = null;
	END IF;

	/* Insert idc_tb_return_order */
	INSERT INTO idc_tb_return_order(
		reor_code, ord_code, reor_type, reor_dept, reor_ord_reference_date, reor_lastupdated_by_account, reor_po_date,
		reor_po_no, reor_received_by, reor_confirm_by, reor_vat, reor_cus_to,
		reor_cus_to_attn, reor_cus_to_address, reor_ship_to, reor_ship_to_attn, reor_ship_to_address,
		reor_bill_to, reor_bill_to_attn, reor_bill_to_address, reor_price_discount, reor_price_chk,
		reor_delivery_chk, reor_delivery_by, reor_delivery_freight_charge, reor_payment_chk, reor_payment_widthin_days,
		reor_payment_closing_on, reor_payment_cash_by, reor_payment_check_by, reor_payment_transfer_by, reor_payment_giro_by,
		reor_sign_by, reor_remark, reor_revesion_time, reor_paper
	) VALUES (
		v_code, v_ord_code, v_type, v_dept, v_ord_date, v_lastupdated_by_account, v_po_date,
		v_po_no, v_received_by, v_confirm_by, v_vat, v_cus_to,
		v_cus_to_attn, v_cus_to_address, v_ship_to, v_ship_to_attn, v_ship_to_address,
		v_bill_to, v_bill_to_attn, v_bill_to_address, v_price_discount, v_price_chk,
		v_delivery_chk, v_delivery_by, v_delivery_freight_charge, v_payment_chk, v_payment_widthin_days,
		v_payment_closing_on_adj, v_payment_cash_by, v_payment_check_by, v_payment_transfer_by, v_payment_giro_by,
		v_sign_by, v_remark, v_revision_time + 1, v_paper
	);

	/* Insert idc_tb_return_order_item */
	WHILE v_cus_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_return_order_item (reor_code, cus_code, roit_dept, it_code, roit_qty, roit_unit_price, roit_remark, roit_date)
		VALUES (v_code, v_cus_to, v_dept, v_cus_it_code[v_i], v_cus_it_qty[v_i], v_cus_it_unit_price[v_i], v_cus_it_remark[v_i], v_po_date);
		v_i := v_i + 1;
	END LOOP;

	IF v_paper = 0 THEN

		/* Insert idc_tb_outstanding */
		INSERT INTO idc_tb_outstanding(std_dept, cus_code, std_doc_ref, std_date, std_doc_type, std_type, std_received_by)
		VALUES (v_dept, v_ship_to, v_code, v_po_date, 'Return Order', 2, v_received_by);
		v_cur_std_idx := currval('idc_tb_outstanding_std_idx_seq');

		/* Insert idc_tb_outstanding_item */
		WHILE v_wh_it_code[v_j] IS NOT NULL LOOP
			INSERT INTO idc_tb_outstanding_item (
				std_idx, istd_type, it_code, istd_it_code_for, istd_qty, istd_function, istd_remark, istd_wh_location
			) VALUES (
				v_cur_std_idx, 2, v_wh_it_code[v_j], v_wh_it_code_for[v_j],
				v_wh_it_qty[v_j], v_wh_it_function[v_j], v_wh_it_remark[v_j], 1
			);
			v_j := v_j + 1;
		END LOOP;

		/* Insert idc_tb_incoming */
		INSERT INTO idc_tb_incoming(inc_dept, cus_code, inc_std_idx, inc_doc_ref, inc_date, inc_doc_type, inc_type, inc_received_by)
		VALUES (v_dept, v_ship_to, v_cur_std_idx, v_code, v_po_date, 'Return Order', 2, v_received_by);
		v_cur_inc_idx := currval('idc_tb_incoming_inc_idx_seq');

		/* Insert idc_tb_incoming_item */
		FOR rec IN SELECT it_code, sum(istd_qty) AS qty FROM idc_tb_outstanding_item WHERE std_idx = v_cur_std_idx
		GROUP BY it_code ORDER BY it_code LOOP
			INSERT INTO idc_tb_incoming_item (inc_idx, init_type, it_code, init_qty, init_wh_location)
			VALUES (v_cur_inc_idx, 2, rec.it_code, rec.qty, 1);
		END LOOP;
	END IF;

	RETURN v_code||'-'||v_cur_std_idx;
END;
$$
;

alter function idc_insertreturnorder(varchar, varchar, integer, varchar, date, varchar, varchar, date, varchar, varchar, numeric, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, numeric, integer, integer, varchar, numeric, integer, integer, text, varchar, varchar, varchar, varchar, varchar, varchar, varchar, integer, character varying[], character varying[], numeric[], numeric[], character varying[], character varying[], numeric[], numeric[], character varying[]) owner to dskim
;

create function idc_insertservice(v_service_date date, v_received_by character varying, v_source_customer integer, v_cus_to character varying, v_cus_name character varying, v_cus_address character varying, v_make_cus_name character varying, v_make_cus_phone character varying, v_make_cus_hphone character varying, v_make_cus_address character varying, v_is_guarantee boolean, v_guarantee_period date, v_signature_by character varying, v_due_date_chk integer, v_days_to_due integer, v_due_date date, v_remark character varying, v_total_disc numeric, v_total_amount numeric, v_lastupdated_by_account character varying, v_it_model_no character varying[], v_it_sn character varying[], v_it_repair_desc character varying[], v_it_repair_qty integer[], v_it_repair_price numeric[], v_it_repair_remark character varying[], v_it_replace_part_name character varying[], v_it_replace_qty integer[], v_it_replace_price numeric[], v_it_replace_remark character varying[]) returns character varying
	language plpgsql
as $$
DECLARE
	v_code varchar;
	v_i integer := 1;
	v_j integer := 1;
	v_k integer := 1;
	v_cus_code varchar;
	v_cus_code_name varchar;
	v_cus_code_address varchar;
BEGIN

	IF v_source_customer = 0 THEN
		SELECT INTO v_cus_code idc_getCurrentCSCode();
		INSERT INTO idc_tb_customer (cus_code, cus_name, cus_full_name, cus_channel, cus_contact_phone, cus_contact_hphone, cus_address, cus_since)
		VALUES (v_cus_code, v_make_cus_name, v_make_cus_name, '00S', v_make_cus_phone, v_make_cus_hphone, v_make_cus_address, CURRENT_DATE);
		v_cus_code_name	:= v_make_cus_name;
		v_cus_code_address := v_make_cus_address;
	ELSE
		v_cus_code := v_cus_to;
		v_cus_code_name	:= v_cus_name;
		v_cus_code_address := v_cus_address;
	END IF;

	SELECT INTO v_code getCurrentServiceCode(v_service_date);

	INSERT INTO idc_tb_service(
		sv_code, sv_date, sv_lastupdated_by_account, sv_cus_to, sv_cus_to_name, sv_cus_to_address,
		sv_received_by, sv_is_guarantee, sv_guarantee_period, sv_signature_by, sv_remark,
		sv_due_date_chk, sv_days_to_due, sv_due_date, sv_total_discount, sv_total_amount, sv_total_remain
	) VALUES(
		v_code, v_service_date, v_lastupdated_by_account, v_cus_code, v_cus_code_name, v_cus_code_address,
		v_received_by, v_is_guarantee, v_guarantee_period, v_signature_by, v_remark,
		v_due_date_chk, v_days_to_due, v_due_date, v_total_disc, v_total_amount, v_total_amount
	);

	WHILE v_it_model_no[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_service_model (sv_code, sv_mdl_model_no, sv_mdl_serial_number)
		VALUES (v_code, v_it_model_no[v_i], v_it_sn[v_i]);
		v_i := v_i + 1;
	END LOOP;

	WHILE v_it_repair_desc[v_j] IS NOT NULL LOOP
		INSERT INTO idc_tb_service_repair (sv_code, sv_repair_desc, sv_repair_qty, sv_repair_unit_price, sv_repair_remark)
		VALUES (v_code, v_it_repair_desc[v_j], v_it_repair_qty[v_j], v_it_repair_price[v_j], v_it_repair_remark[v_j]);
		v_j := v_j + 1;
	END LOOP;

	WHILE v_it_replace_part_name[v_k] IS NOT NULL AND v_it_replace_part_name[v_k] != '' LOOP
		INSERT INTO idc_tb_service_replace (sv_code, sv_replace_part_name, sv_replace_qty, sv_replace_unit_price, sv_replace_remark)
		VALUES (v_code, v_it_replace_part_name[v_k], v_it_replace_qty[v_k], v_it_replace_price[v_k], v_it_replace_remark[v_k]);
		v_k := v_k + 1;
	END LOOP;

	RETURN v_code;
END;
$$
;

alter function idc_insertservice(date, varchar, integer, varchar, varchar, varchar, varchar, varchar, varchar, varchar, boolean, date, varchar, integer, integer, date, varchar, numeric, numeric, varchar, character varying[], character varying[], character varying[], integer[], numeric[], character varying[], character varying[], integer[], numeric[], character varying[]) owner to dskim
;

create function idc_insertservice(v_reg_no character varying, v_service_date date, v_received_by character varying, v_source_customer integer, v_cus_to character varying, v_cus_name character varying, v_cus_address character varying, v_make_cus_name character varying, v_make_cus_phone character varying, v_make_cus_hphone character varying, v_make_cus_address character varying, v_is_guarantee boolean, v_guarantee_period date, v_signature_by character varying, v_due_date_chk integer, v_days_to_due integer, v_due_date date, v_remark character varying, v_total_disc numeric, v_total_amount numeric, v_lastupdated_by_account character varying, v_it_code character varying[], v_it_model_no character varying[], v_it_sn character varying[], v_it_repair_desc character varying[], v_it_repair_qty integer[], v_it_repair_price numeric[], v_it_repair_remark character varying[], v_it_replace_part_name character varying[], v_it_replace_qty integer[], v_it_replace_price numeric[], v_it_replace_remark character varying[]) returns character varying
	language plpgsql
as $$
DECLARE
	v_code varchar;
	v_i integer := 1;
	v_j integer := 1;
	v_k integer := 1;
	v_cus_code varchar;
	v_cus_code_name varchar;
	v_cus_code_address varchar;
BEGIN

	IF v_source_customer = 0 THEN
		SELECT INTO v_cus_code idc_getCurrentCSCode();
		INSERT INTO idc_tb_customer (cus_code, cus_name, cus_full_name, cus_channel, cus_contact_phone, cus_contact_hphone, cus_address, cus_since)
		VALUES (v_cus_code, v_make_cus_name, v_make_cus_name, '00S', v_make_cus_phone, v_make_cus_hphone, v_make_cus_address, CURRENT_DATE);
		v_cus_code_name	:= v_make_cus_name;
		v_cus_code_address := v_make_cus_address;
	ELSE
		v_cus_code := v_cus_to;
		v_cus_code_name	:= v_cus_name;
		v_cus_code_address := v_cus_address;
	END IF;

	SELECT INTO v_code idc_getCurrentServiceCode(v_service_date);

	INSERT INTO idc_tb_service(
		sv_code, sv_reg_no, sv_date, sv_lastupdated_by_account, sv_cus_to, sv_cus_to_name, sv_cus_to_address,
		sv_received_by, sv_is_guarantee, sv_guarantee_period, sv_signature_by, sv_remark,
		sv_due_date_chk, sv_days_to_due, sv_due_date, sv_total_discount, sv_total_amount, sv_total_remain
	) VALUES(
		v_code, v_reg_no, v_service_date, v_lastupdated_by_account, v_cus_code, v_cus_code_name, v_cus_code_address,
		v_received_by, v_is_guarantee, v_guarantee_period, v_signature_by, v_remark,
		v_due_date_chk, v_days_to_due, v_due_date, v_total_disc, v_total_amount, v_total_amount
	);

	WHILE v_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_service_item (sv_code, it_code, svit_model_no, svit_serial_number)
		VALUES (v_code, v_it_code[v_i], v_it_model_no[v_i], v_it_sn[v_i]);
		v_i := v_i + 1;
	END LOOP;

	WHILE v_it_repair_desc[v_j] IS NOT NULL LOOP
		INSERT INTO idc_tb_service_repair (sv_code, sv_repair_desc, sv_repair_qty, sv_repair_unit_price, sv_repair_remark)
		VALUES (v_code, v_it_repair_desc[v_j], v_it_repair_qty[v_j], v_it_repair_price[v_j], v_it_repair_remark[v_j]);
		v_j := v_j + 1;
	END LOOP;

	WHILE v_it_replace_part_name[v_k] IS NOT NULL AND v_it_replace_part_name[v_k] != '' LOOP
		INSERT INTO idc_tb_service_replace (sv_code, sv_replace_part_name, sv_replace_qty, sv_replace_unit_price, sv_replace_remark)
		VALUES (v_code, v_it_replace_part_name[v_k], v_it_replace_qty[v_k], v_it_replace_price[v_k], v_it_replace_remark[v_k]);
		v_k := v_k + 1;
	END LOOP;

	RETURN v_code;
END;
$$
;

alter function idc_insertservice(varchar, date, varchar, integer, varchar, varchar, varchar, varchar, varchar, varchar, varchar, boolean, date, varchar, integer, integer, date, varchar, numeric, numeric, varchar, character varying[], character varying[], character varying[], character varying[], integer[], numeric[], character varying[], character varying[], integer[], numeric[], character varying[]) owner to dskim
;

create function idc_insertservicereg(v_reg_date date, v_source_customer integer, v_cus_to character varying, v_cus_name character varying, v_cus_address character varying, v_make_cus_name character varying, v_make_cus_phone character varying, v_make_cus_hphone character varying, v_make_cus_address character varying, v_lastupdated_by_account character varying, v_signature_by character varying, v_remark character varying, v_it_code character varying[], v_it_model_no character varying[], v_it_sn character varying[], v_it_is_guarantee integer[], v_it_guarantee_period date[], v_it_cus_complain character varying[], v_it_tech_analyze character varying[]) returns character varying
	language plpgsql
as $$
DECLARE
	v_code varchar;
	v_cus_code varchar;
	v_cus_code_name varchar;
	v_cus_code_address varchar;
	v_i integer := 1;
BEGIN

	IF v_source_customer = 0 THEN
		SELECT INTO v_cus_code idc_getCurrentCSCode();
		INSERT INTO idc_tb_customer (cus_code, cus_name, cus_full_name, cus_channel, cus_contact_phone, cus_contact_hphone, cus_address, cus_since)
		VALUES (v_cus_code, v_make_cus_name, v_make_cus_name, '00S', v_make_cus_phone, v_make_cus_hphone, v_make_cus_address, CURRENT_DATE);
		v_cus_code_name	:= v_make_cus_name;
		v_cus_code_address := v_make_cus_address;
	ELSE
		v_cus_code := v_cus_to;
		v_cus_code_name	:= v_cus_name;
		v_cus_code_address := v_cus_address;
	END IF;

	SELECT INTO v_code idc_getCurrentServiceRegCode(v_reg_date);

	INSERT INTO idc_tb_service_reg(
		sg_code, sg_receive_date, sg_lastupdated_by_account, sg_lastupdated_timestamp,
		sg_cus_to, sg_cus_to_name, sg_cus_to_address, sg_signature_by, sg_remark
	) VALUES (
		v_code, v_reg_date, v_lastupdated_by_account, current_timestamp,
		v_cus_code, v_cus_code_name, v_cus_code_address, v_signature_by, v_remark
	);

	WHILE v_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_service_reg_item (
			sg_code, it_code, sgit_model_no, sgit_is_guarantee, sgit_guarantee, sgit_serial_number,
			sgit_incoming_date, sgit_cus_complain, sgit_tech_analyze
		) VALUES (
			v_code, v_it_code[v_i], v_it_model_no[v_i], v_it_is_guarantee[v_i], v_it_guarantee_period[v_i], v_it_sn[v_i],
			v_reg_date, v_it_cus_complain[v_i], v_it_tech_analyze[v_i]
		);
		v_i := v_i + 1;
	END LOOP;

	RETURN v_code;
END;
$$
;

alter function idc_insertservicereg(date, integer, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, character varying[], character varying[], character varying[], integer[], date[], character varying[], character varying[]) owner to dskim
;

create function idc_insertstocklog(v_it_code character varying, v_it_location integer, v_it_type integer, v_document_type character varying, v_document_idx integer, v_document_no character varying, v_document_date date, v_log_by character varying, v_qty_value boolean, v_qty numeric, v_is_revised boolean) returns character varying
	language plpgsql
as $$
DECLARE
	v_revision_time smallint;
	v_logs_code varchar;
	v_qty_adj numeric;
BEGIN

	SELECT INTO v_logs_code idc_getStockLogIdx(v_it_code, v_it_location, v_it_type, CURRENT_DATE);

	IF v_document_type IN ('DO Order', 'DO Billing') THEN
		SELECT INTO v_revision_time book_revision_time FROM idc_tb_booking WHERE book_doc_type IN(1,2) AND substr(book_doc_ref,2) = substr(v_document_no,2);
		v_qty_adj = v_qty*-1;
	ELSIF v_document_type IN ('DT', 'DF', 'DR', 'DM') THEN
		SELECT INTO v_revision_time book_revision_time FROM idc_tb_booking WHERE book_doc_type IN (3,4,5,6) AND book_doc_ref = v_document_no;
		v_qty_adj = v_qty*-1;
	ELSIF v_document_type IN ('Reject Stock', 'Reject ED') THEN
		v_qty_adj = v_qty*-1;
	ELSIF v_document_type IN ('Return Order', 'Return Billing', 'Return DT') THEN
		SELECT INTO v_revision_time std_revision_time FROM idc_tb_outstanding WHERE std_doc_ref = v_document_no;
		v_qty_adj = v_qty;
	END IF;
	if v_revision_time is null then v_revision_time := 0; end if;

	INSERT INTO idc_tb_log_detail(
		log_code, it_code, log_wh_location, log_type, log_revision_time, log_is_revised,
		log_document_type, log_document_idx, log_document_no, log_document_date,
		log_cfm_timestamp, log_cfm_by_account, log_qty_value, log_qty
	) VALUES (
		v_logs_code, v_it_code, v_it_location, v_it_type, v_revision_time, v_is_revised,
		v_document_type, v_document_idx, v_document_no, v_document_date,
		CURRENT_TIMESTAMP, v_log_by, v_qty_value, v_qty_adj
	);

	RETURN v_logs_code;
END;
$$
;

alter function idc_insertstocklog(varchar, integer, integer, varchar, integer, varchar, date, varchar, boolean, numeric, boolean) owner to dskim
;

create function idc_insertwarranty(v_cus_name character varying, v_cus_sex character varying, v_cus_address character varying, v_cus_city character varying, v_cus_zip_code character varying, v_cus_phone character varying, v_cus_hphone character varying, v_cus_email character varying, v_it_product integer, v_it_code character varying, v_it_model_no character varying, v_warranty_no character varying, v_serial_no character varying, v_purchase_date date, v_purchase_store character varying, v_suggest character varying, v_lastupdated_by_account character varying) returns integer
	language plpgsql
as $$
DECLARE
	v_idx integer;
BEGIN
	INSERT INTO idc_tb_warranty(
		wr_cus_name, wr_cus_sex, wr_cus_address, wr_cus_city, wr_cus_zip_code,
		wr_cus_phone, wr_cus_hphone, wr_cus_email,
		it_code, wr_warranty_no, wr_serial_no, wr_product, wr_it_model_no,
		wr_purchase_date, wr_purchase_store, wr_suggest, wr_lastupdated_by_account, wr_inputted_by_account
	) VALUES (
		v_cus_name, v_cus_sex, v_cus_address, v_cus_city, v_cus_zip_code,
		v_cus_phone, v_cus_hphone, v_cus_email,
		v_it_code, v_warranty_no, v_serial_no, v_it_product, v_it_model_no,
		v_purchase_date, v_purchase_store, v_suggest, v_lastupdated_by_account, v_lastupdated_by_account
	);
	v_idx := currval('idc_tb_warranty_wr_idx_seq');
	RETURN v_idx;
END;
$$
;

alter function idc_insertwarranty(varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, integer, varchar, varchar, varchar, varchar, date, varchar, varchar, varchar) owner to dskim
;

create function idc_isarrivedpl(v_type integer, v_pl_idx integer) returns boolean
	language plpgsql
as $$
DECLARE
	v_value boolean := false;
	v_inpl_idx integer;
	v_row integer; v_row1 integer; v_row2 integer;
BEGIN

	/* 1. for PL from PO*/
	IF v_type = 1 THEN
		SELECT INTO v_inpl_idx inpl_idx FROM idc_tb_in_pl WHERE pl_idx = v_pl_idx; GET DIAGNOSTICS v_row1 := ROW_COUNT;
		SELECT INTO v_inpl_idx inpl_idx FROM idc_tb_in_pl_v2 WHERE pl_idx = v_pl_idx; GET DIAGNOSTICS v_row2 := ROW_COUNT;
		v_row := v_row1 + v_row2;
	/* 2. for PL from replace claim*/
	ELSIF v_type = 2 THEN
		SELECT INTO v_inpl_idx incl_idx FROM idc_tb_in_claim WHERE cl_idx = v_pl_idx; GET DIAGNOSTICS v_row1 := ROW_COUNT;
		SELECT INTO v_inpl_idx incl_idx FROM idc_tb_in_claim_v2 WHERE cl_idx = v_pl_idx; GET DIAGNOSTICS v_row2 := ROW_COUNT;
		v_row := v_row1 + v_row2;
	END IF;

	IF v_row > 0 THEN
		v_value = true;
	END IF;

	RETURN v_value;
END;
$$
;

alter function idc_isarrivedpl(integer, integer) owner to dskim
;

create function idc_isbillingused(v_code character varying) returns character varying
	language plpgsql
as $$
DECLARE
	v_turn_code varchar;
	v_row_count integer := 0;
	v_is_used boolean := false;
BEGIN

	SELECT INTO v_turn_code turn_code FROM idc_tb_return WHERE turn_bill_code = v_code; -- AND turn_return_condition IN (0,1,2,3);

	GET DIAGNOSTICS v_row_count := ROW_COUNT;
	IF v_row_count >= 1 THEN
		v_is_used = true;
	END IF;

	RETURN v_is_used;
END;
$$
;

alter function idc_isbillingused(varchar) owner to dskim
;

create function idc_isdepositused(v_return_condition integer, v_code character varying, v_cus_code character varying, v_date date) returns character varying
	language plpgsql
as $$
DECLARE
	v_idx integer;
	v_row_count integer := 0;
	v_is_used boolean := false;
BEGIN

	--Cari ada payment yang menggunakan deposit di atas tanggal return atau tidak
	IF v_return_condition = 3 THEN
		SELECT INTO v_idx pay_idx FROM idc_tb_deposit
		WHERE pay_idx IS NOT NULL AND dep_issued_date >= v_date AND cus_code = v_cus_code AND dep_type = 'paymentB';

		GET DIAGNOSTICS v_row_count := ROW_COUNT;
		IF v_row_count >= 1 THEN
			v_is_used = true;
		END IF;
	END IF;

	RETURN v_is_used;

END;
$$
;

alter function idc_isdepositused(integer, varchar, varchar, date) owner to dskim
;

create function idc_isdepositused(v_cus_code character varying, v_date date, v_method character varying, v_bank character varying) returns character varying
	language plpgsql
as $$
DECLARE
	v_idx integer;
	v_row_count integer := 0;
	v_is_used boolean := false;
BEGIN

	--Cari ada payment yang menggunakan deposit di atas tanggal deposit atau tidak
	IF v_method  = 'transfer' THEN
		SELECT INTO v_idx pay_idx FROM idc_tb_deposit
		WHERE pay_idx IS NOT NULL AND dep_issued_date >= v_date AND cus_code = v_cus_code AND dep_type = 'paymentA' AND dep_method = v_method AND dep_bank = v_bank;
	ELSE
		SELECT INTO v_idx pay_idx FROM idc_tb_deposit
		WHERE pay_idx IS NOT NULL AND dep_issued_date >= v_date AND cus_code = v_cus_code AND dep_type = 'paymentA' AND dep_method = v_method;
	END IF;

	GET DIAGNOSTICS v_row_count := ROW_COUNT;
	IF v_row_count >= 1 THEN
		v_is_used = true;
	END IF;

	RETURN v_is_used;
END;
$$
;

alter function idc_isdepositused(varchar, date, varchar, varchar) owner to dskim
;

create function idc_isfileexist(v_code character) returns boolean
	language plpgsql
as $$
DECLARE
	v_idx integer;
	v_val boolean := false;
BEGIN

	SELECT INTO v_idx ltf_idx FROM idc_tb_letter_file WHERE lt_reg_no = v_code AND ltf_type='A';

	IF v_idx IS NOT NULL THEN
		v_val := true;
	END IF;

	RETURN v_val;
END;
$$
;

alter function idc_isfileexist(char) owner to dskim
;

create function idc_isissetpayment(v_code character varying) returns character varying
	language plpgsql
as $$
DECLARE
	v_billing boolean;
	v_amount_billing numeric;
	v_amount_payment numeric;
	v_amount_return numeric;
	v_value varchar;
BEGIN

	SELECT INTO v_billing idc_isBillingUsed(v_code);

	IF v_billing = 't' THEN
		SELECT INTO v_amount_billing bill_total_billing from idc_tb_billing where bill_code = v_code;
		SELECT INTO v_amount_payment sum(pay_paid) from idc_tb_payment where bill_code = v_code;
		SELECT INTO v_amount_return sum(turn_total_return) from idc_tb_return where turn_bill_code = v_code;

		IF v_amount_billing = v_amount_return THEN
			v_value = 'paid';
		ELSIF v_amount_payment is not null THEN
			v_value = 'paid';
		ELSE
			v_value = 'unpaid';
		END IF;
	END IF;

	RETURN v_value;
END;
$$
;

alter function idc_isissetpayment(varchar) owner to dskim
;

create function idc_islockedcondition(v_type integer, v_doc character varying) returns boolean
	language plpgsql
as $$
DECLARE
	v_return_code varchar;
	v_date date;
	v_is_true boolean := false;
BEGIN

	IF v_type = 1 THEN
		SELECT INTO v_return_code turn_bill_code FROM idc_tb_return WHERE turn_bill_code = v_doc;
		IF v_return_code != '' THEN
			v_is_true := true;
		END IF;
	ELSIF v_type = 2 THEN
		SELECT INTO v_return_code ord_code FROM idc_tb_return_order WHERE ord_code = v_doc;
		IF v_return_code != '' THEN
			v_is_true := true;
		END IF;
	ELSIF v_type = 3 THEN
		SELECT INTO v_return_code rdt_code FROM idc_tb_return_dt WHERE dt_code = v_doc;
		IF v_return_code != '' THEN
			v_is_true := true;
		END IF;
	ELSIF v_type = 6 THEN
		SELECT INTO v_date req_received_date FROM idc_tb_request WHERE req_code = v_doc;
		IF v_date IS NOT NULL THEN
			v_is_true := true;
		END IF;
	END IF;

	RETURN v_is_true;
END;
$$
;

alter function idc_islockedcondition(integer, varchar) owner to dskim
;

create function idc_islockedconditionreturn(v_inc_idx integer) returns boolean
	language plpgsql
as $$
DECLARE
	v_is_demo_true boolean := false;
	v_is_reject_true boolean := false;
	v_is_true boolean := false;
	v_doc_ref varchar;
	v_demo_qty numeric;
	v_timestamp timestamp;
	rec record;
BEGIN

	SELECT INTO v_demo_qty sum(init_demo_qty) FROM idc_tb_incoming_item WHERE inc_idx = v_inc_idx;
	IF v_demo_qty IS NOT NULL AND v_demo_qty > 0 THEN
		SELECT INTO v_timestamp inm_cfm_marketing_timestamp FROM idc_tb_incoming_marketing WHERE inc_idx = v_inc_idx;
		IF v_timestamp IS NOT NULL THEN
			v_is_demo_true = true;
		END IF;
	END IF;

	FOR rec IN SELECT rjit_status FROM idc_tb_reject JOIN idc_tb_reject_item USING(rjt_idx) WHERE rjt_doc_idx = v_inc_idx AND rjt_doc_type = 1 ORDER BY it_code LOOP
		if rec.rjit_status != 'on_wh' then
			v_is_reject_true = true;
		end if;
	END LOOP;

	SELECT INTO v_doc_ref inc_doc_ref FROM idc_tb_incoming where inc_idx = v_inc_idx;

	if v_is_demo_true then v_is_true = true; end if;
	if v_is_reject_true then v_is_true = true; end if;
	if v_doc_ref is not null then v_is_true = true; end if;

	RETURN v_is_true;
END;
$$
;

alter function idc_islockedconditionreturn(integer) owner to dskim
;

create function idc_isorderused(v_code character varying) returns character varying
	language plpgsql
as $$
DECLARE
	v_turn_code varchar;
	v_row_count integer := 0;
	v_is_used boolean := false;
BEGIN
	SELECT INTO v_turn_code reor_code FROM idc_tb_return_order WHERE ord_code = v_code;
	GET DIAGNOSTICS v_row_count := ROW_COUNT;
	IF v_row_count >= 1 THEN
		v_is_used = true;
	END IF;
	RETURN v_is_used;
END;
$$
;

alter function idc_isorderused(varchar) owner to dskim
;

create function idc_ispaydesctrue(v_source integer, v_idx integer, v_remark character varying) returns boolean
	language plpgsql
as $$
DECLARE
	v_value boolean := false;
	rec record;
BEGIN

	-- v_source Description
	-- 1. Payment billing , pay_remark
	-- 2. Payment billing, pade_description
	-- 3. Payment service, svpay_remark

	IF v_source = 1 THEN
		FOR rec IN SELECT pay_idx FROM idc_tb_payment WHERE pay_idx = v_idx AND pay_remark ILIKE v_remark ORDER BY pay_idx LOOP
			v_value := true;
		END LOOP;
	ELSIF v_source = 2 THEN
		FOR rec IN SELECT pade_idx FROM idc_tb_payment_deduction WHERE pay_idx = v_idx AND pade_description ILIKE v_remark ORDER BY pade_idx LOOP
			v_value := true;
		END LOOP;
	ELSIF v_source = 3 THEN
		FOR rec IN SELECT svpay_idx FROM idc_tb_service_payment WHERE svpay_idx = v_idx AND svpay_remark ILIKE v_remark ORDER BY svpay_idx LOOP
			v_value := true;
		END LOOP;
	END IF;

	RETURN v_value;
END;
$$
;

alter function idc_ispaydesctrue(integer, integer, varchar) owner to dskim
;

create function idc_isseecriticalstock(v_perm character varying, v_idx integer) returns boolean
	language plpgsql
as $$
DECLARE
	v_val boolean := false;
	rec record;
BEGIN

	-- Function to see who can see critical stock in main index when first time they are login
	-- Authority just for, 1) General manager, 2) Purchasing, 3) Warehouse
	FOR rec IN SELECT gr_idx, gm_perm FROM idc_tb_gmember WHERE ma_idx = v_idx AND gm_type=v_perm LOOP
		IF rec.gr_idx = 1000 AND rec.gm_perm>0 THEN			-- General manager
			v_val := true;
		ELSIF rec.gr_idx = 2002 AND rec.gm_perm>0 THEN		-- Purchasing
			v_val := true;
		ELSIF rec.gr_idx = 2003 AND rec.gm_perm>0 THEN		-- Warehouse
			v_val := true;
		END IF;
	END LOOP;

	RETURN v_val;
END;
$$
;

alter function idc_isseecriticalstock(varchar, integer) owner to dskim
;

create function idc_issetchargeitem(v_code character varying) returns character varying
	language plpgsql
as $$
DECLARE
	v_value boolean := false;
	rec record;
BEGIN

	FOR rec IN SELECT sgit_cost FROM idc_tb_service_reg_item WHERE sg_code = v_code
	LOOP
		IF rec.sgit_cost = 1 THEN
			v_value = true;
		END IF;
	END LOOP;

	RETURN v_value::varchar;
END;
$$
;

alter function idc_issetchargeitem(varchar) owner to dskim
;

create function idc_isvalidshowinvoice(v_perm character varying, v_inv_no character varying, v_inv_type character varying) returns boolean
	language plpgsql
as $$
DECLARE
	v_value boolean := false;
	v_ordered_by integer;
	rec record;
	v_turn_code varchar;
	v_turn_date date;
	v_turn_vat numeric;

BEGIN

	IF v_perm = 'IDC' THEN

		IF v_inv_type = 'billing' THEN
			FOR rec IN SELECT bill_code, bill_vat, bill_inv_date, bill_remain_amount  FROM idc_tb_billing WHERE bill_code=v_inv_no LOOP
				IF rec.bill_vat > 0 THEN
					v_value := true;
				ELSIF rec.bill_vat <= 0 THEN
					IF rec.bill_inv_date > '2015-01-01' THEN
						v_value := true;
					ELSIF rec.bill_remain_amount > 0 THEN
						v_value := true;
					END IF;
				END IF;
			END LOOP;

		ELSIF v_inv_type = 'billing_return' THEN
			FOR rec IN SELECT turn_code, turn_vat, turn_return_date FROM idc_tb_return WHERE turn_code=v_inv_no LOOP
				IF rec.turn_vat > 0 THEN
					v_value := true;
				ELSIF rec.turn_vat <= 0 THEN
					IF rec.turn_return_date > '2015-01-01' THEN
						v_value := true;
					END IF;
				END IF;
			END LOOP;

		ELSIF v_inv_type = 'dr' THEN
			SELECT INTO v_turn_code dr_turn_code FROM idc_tb_dr WHERE dr_code=v_inv_no;
			SELECT INTO v_turn_vat turn_vat FROM idc_tb_return WHERE turn_code=v_turn_code;
			SELECT INTO v_turn_date turn_return_date FROM idc_tb_return WHERE turn_code=v_turn_code;
			IF v_turn_vat > 0 THEN
				v_value := true;
			ELSIF v_turn_vat <= 0 THEN
				IF v_turn_date > '2015-01-01' THEN
					v_value := true;
				END IF;
			END IF;

		ELSIF v_inv_type = 'service' THEN
			FOR rec IN SELECT sv_code, sv_date, sv_total_remain FROM idc_tb_service WHERE sv_code=v_inv_no LOOP
				IF rec.sv_date > '2015-01-01' THEN
					v_value := true;
				ELSIF rec.sv_total_remain > 0 THEN
					v_value := true;
				END IF;
			END LOOP;

		END IF;

	ELSIF v_perm = 'MED' THEN


		IF v_inv_type = 'billing' THEN
			FOR rec IN SELECT bill_code, bill_vat, bill_inv_date, bill_remain_amount  FROM med_tb_billing WHERE bill_code=v_inv_no LOOP
				IF rec.bill_vat > 0 THEN
					v_value := true;
				ELSIF rec.bill_vat <= 0 THEN
					IF rec.bill_inv_date > '2015-01-01' THEN
						v_value := true;
					ELSIF rec.bill_remain_amount > 0 THEN
						v_value := true;
					END IF;
				END IF;
			END LOOP;

		ELSIF v_inv_type = 'billing_return' THEN
			FOR rec IN SELECT turn_code, turn_vat, turn_return_date FROM med_tb_return WHERE turn_code=v_inv_no LOOP
				IF rec.turn_vat > 0 THEN
					v_value := true;
				ELSIF rec.turn_vat <= 0 THEN
					IF rec.turn_return_date > '2015-01-01' THEN
						v_value := true;
					END IF;
				END IF;
			END LOOP;

		ELSIF v_inv_type = 'dr' THEN
			SELECT INTO v_turn_code dr_turn_code FROM med_tb_dr WHERE dr_code=v_inv_no;
			SELECT INTO v_turn_vat turn_vat FROM med_tb_return WHERE turn_code=v_turn_code;
			SELECT INTO v_turn_date turn_return_date FROM med_tb_return WHERE turn_code=v_turn_code;
			IF v_turn_vat > 0 THEN
				v_value := true;
			ELSIF v_turn_vat <= 0 THEN
				IF v_turn_date > '2015-01-01' THEN
					v_value := true;
				END IF;
			END IF;

		END IF;

	ELSE
		v_value := true;
	END IF;


RETURN v_value;
END;
$$
;

alter function idc_isvalidshowinvoice(varchar, varchar, varchar) owner to dskim
;

create function idc_lastarrived(v_source character varying, v_source_type character varying, v_result character varying, v_code character varying, v_it_code character varying) returns character varying
	language plpgsql
as $$
DECLARE
    v_date1 date;
    v_date2 date;
    v_date date;
    v_idx integer;
    v_inv varchar;
    v_val varchar;
BEGIN

    IF v_source = 'PO' THEN
        IF v_source_type = 'PL' THEN
            SELECT INTO v_date1 MAX(inpl_checked_date) FROM idc_tb_in_pl AS inpl JOIN idc_tb_in_pl_item AS init USING (inpl_idx) WHERE po_code = v_code AND it_code = v_it_code;
            SELECT INTO v_date2 MAX(inpl_checked_date) FROM idc_tb_in_pl_v2 AS inpl JOIN idc_tb_in_pl_item_v2 AS init USING (inpl_idx) WHERE po_code = v_code AND it_code = v_it_code;

            IF v_result = 'Date' THEN
                SELECT INTO v_val idc_CompareDate(v_date1::text, v_date2::text, 'date');
            ELSIF v_result = 'Invoice' THEN
                SELECT INTO v_val idc_CompareDate(v_date1::text, v_date2::text, 'position');
                IF v_val = '1' THEN
                    SELECT INTO v_idx MAX(inpl_idx) FROM idc_tb_in_pl WHERE inpl_checked_date = v_date1 AND po_code = v_code;
                    SELECT INTO v_inv inpl_inv_no FROM idc_tb_in_pl WHERE inpl_idx = v_idx;
                ELSIF v_val = '2' THEN
                    SELECT INTO v_idx MAX(inpl_idx) FROM idc_tb_in_pl_v2 WHERE inpl_checked_date = v_date2 AND po_code = v_code;
                    SELECT INTO v_inv inpl_inv_no FROM idc_tb_in_pl_v2 WHERE inpl_idx = v_idx;
                END IF;
                v_val := v_inv;
            END IF;
        ELSIF v_source_type = 'Claim' THEN
        ELSIF v_source_type = 'Local' THEN
        END IF;

    ELSIF v_source = 'PL' THEN
        IF v_source_type = 'PL' THEN
            SELECT INTO v_date1 max(a.inpl_checked_date) FROM idc_tb_in_pl AS a JOIN idc_tb_in_pl_item AS b USING (inpl_idx) WHERE a.pl_idx = v_code::integer AND b.it_code = v_it_code;
            SELECT INTO v_date2 max(a.inpl_checked_date) FROM idc_tb_in_pl_v2 AS a JOIN idc_tb_in_pl_item_v2 AS b USING (inpl_idx) WHERE a.pl_idx = v_code::integer AND b.it_code = v_it_code;

            IF v_result = 'Date' THEN
                SELECT INTO v_val idc_CompareDate(v_date1::text, v_date2::text, 'date');
            ELSIF v_result = 'Invoice' THEN
                SELECT INTO v_val idc_CompareDate(v_date1::text, v_date2::text, 'position');
                IF v_val = '1' THEN
                    SELECT INTO v_idx MAX(inpl_idx) FROM idc_tb_in_pl WHERE inpl_checked_date = v_date1 AND pl_idx = v_code::integer;
                    SELECT INTO v_inv inpl_inv_no FROM idc_tb_in_pl WHERE inpl_idx = v_idx;
                ELSIF v_val = '2' THEN
                    SELECT INTO v_idx MAX(inpl_idx) FROM idc_tb_in_pl_v2 WHERE inpl_checked_date = v_date2 AND pl_idx = v_code::integer;
                    SELECT INTO v_inv inpl_inv_no FROM idc_tb_in_pl_v2 WHERE inpl_idx = v_idx;
                END IF;
                v_val := v_inv;
            END IF;
        ELSIF v_source_type = 'Claim' THEN
            SELECT INTO v_date1 max(a.incl_checked_date) FROM idc_tb_in_claim AS a JOIN idc_tb_in_claim_item AS b USING (incl_idx) WHERE a.cl_idx = v_code::integer AND b.it_code = v_it_code;
            SELECT INTO v_date2 max(a.incl_checked_date) FROM idc_tb_in_claim_v2 AS a JOIN idc_tb_in_claim_item_v2 AS b USING (incl_idx) WHERE a.cl_idx = v_code::integer AND b.it_code = v_it_code;

            IF v_result = 'Date' THEN
                SELECT INTO v_val idc_CompareDate(v_date1::text, v_date2::text, 'date');
            ELSIF v_result = 'Invoice' THEN
                SELECT INTO v_val idc_CompareDate(v_date1::text, v_date2::text, 'position');
                IF v_val = '1' THEN
                    SELECT INTO v_idx MAX(incl_idx) FROM idc_tb_in_claim WHERE incl_checked_date = v_date1 AND cl_idx = v_code::integer;
                    SELECT INTO v_inv incl_inv_no FROM idc_tb_in_claim WHERE incl_idx = v_idx;
                ELSIF v_val = '2' THEN
                    SELECT INTO v_idx MAX(incl_idx) FROM idc_tb_in_claim_v2 WHERE incl_checked_date = v_date2 AND cl_idx = v_code::integer;
                    SELECT INTO v_inv incl_inv_no FROM idc_tb_in_claim_v2 WHERE incl_idx = v_idx;
                END IF;
                v_val := v_inv;
            END IF;
        ELSIF v_source_type = 'Local' THEN
            SELECT INTO v_date1 max(a.inlc_checked_date) FROM idc_tb_in_local AS a JOIN idc_tb_in_local_item AS b USING (inlc_idx) WHERE TRIM(po_code)||'-'||pl_no = v_code AND b.it_code = v_it_code;
            SELECT INTO v_date2 max(a.inlc_checked_date) FROM idc_tb_in_local_v2 AS a JOIN idc_tb_in_local_item_v2 AS b USING (inlc_idx) WHERE TRIM(po_code)||'-'||pl_no = v_code AND b.it_code = v_it_code;

            IF v_result = 'Date' THEN
                SELECT INTO v_val idc_CompareDate(v_date1::text, v_date2::text, 'date');
            ELSIF v_result = 'Invoice' THEN
                SELECT INTO v_val idc_CompareDate(v_date1::text, v_date2::text, 'position');
                IF v_val = '1' THEN
                    SELECT INTO v_inv MAX(pl_no)::text FROM idc_tb_in_local JOIN idc_tb_in_local_item USING (inlc_idx) WHERE inlc_checked_date = v_date1 AND TRIM(po_code)||'-'||pl_no = v_code AND it_code = v_it_code;
                ELSIF v_val = '2' THEN
                    SELECT INTO v_inv MAX(pl_no)::text FROM idc_tb_in_local_v2 JOIN idc_tb_in_local_item_v2 USING (inlc_idx) WHERE inlc_checked_date = v_date2 AND TRIM(po_code)||'-'||pl_no = v_code AND it_code = v_it_code;
                END IF;
                v_val := v_inv;
            END IF;
        END IF;
    END IF;

    RETURN v_val;
END;
$$
;

alter function idc_lastarrived(varchar, varchar, varchar, varchar, varchar) owner to dskim
;

create function idc_loginmember(v_account character varying, v_password character varying) returns integer
	language plpgsql
as $$
DECLARE
	v_member RECORD;
	v_numFail integer := 0;
	v_licensed_time timestamp;
	v_use_block integer := 0;
	v_pw_change_date timestamp;
	v_pw_valid_period integer;
	v_licensed_epoch integer :=0;
	v_allow_gracetime integer;
	v_grace_time integer;
BEGIN
	-- check license
	SELECT INTO v_licensed_epoch, v_allow_gracetime, v_grace_time
		pl_opt1, pl_opt2, pl_opt3 FROM idc_tb_policy WHERE pl_no = 447;

	-- decide when is the licensed time
	IF v_allow_gracetime = 1 THEN
		v_licensed_time := timestamp 'epoch' + (v_licensed_epoch + v_grace_time) * interval '1 second';
	ELSE
		v_licensed_time := timestamp 'epoch' + v_licensed_epoch * interval '1 second';
	END IF;

	-- ******************************************************************************************
	-- **********************  NEED TO DEBUG  ***************************************************
	IF (v_licensed_time + interval '1 7:59:59') < CURRENT_TIMESTAMP THEN
		RAISE EXCEPTION '0';
	END IF;
	--********************************************************************************************

	-- find a row with the give username and password ( the account must be still valid )
	SELECT INTO v_member * FROM idc_tb_mbracc WHERE ma_account = v_account;

	--is exist account?
	IF NOT FOUND THEN
		RAISE EXCEPTION '1';
	ELSE
		--is valid account?
		IF v_member.ma_isvalidacc = FALSE THEN
			RAISE EXCEPTION '2';

		-- login success
		ELSIF v_member.ma_password = v_password THEN

			-- check password validation
			SELECT INTO v_pw_change_date ma_lastpasswdchangedate FROM idc_tb_mbracc WHERE ma_idx = v_member.ma_idx;
			SELECT INTO v_pw_valid_period pl_opt4 FROM idc_tb_policy WHERE pl_no = 154;

			IF v_pw_change_date IS NULL THEN
				RAISE EXCEPTION '3';
			ELSIF v_pw_valid_period > 0 AND -- use password valid period
				v_pw_valid_period * interval '1 second' + v_pw_change_date <= CURRENT_TIMESTAMP THEN
				RAISE EXCEPTION '4';
			END IF;

			UPDATE idc_tb_mbracc SET
				ma_lastsignindate = CURRENT_TIMESTAMP,
				ma_numsignin = ma_numsignin + 1,
				ma_numsigninfail = 0
			WHERE
				ma_idx = v_member.ma_idx;
			RETURN v_member.ma_idx;

		--login fail
		ELSE
			-- apply grace time
			IF v_member.ma_signinfaildate + interval '5 min' < CURRENT_TIMESTAMP THEN
				UPDATE idc_tb_mbracc SET
					ma_signinfaildate = CURRENT_TIMESTAMP,
					ma_numsigninfail = 1
				WHERE
					ma_idx = v_member.ma_idx;
				v_numFail = 1;
			ELSE
				UPDATE idc_tb_mbracc SET
					ma_signinfaildate = CURRENT_TIMESTAMP,
					ma_numsigninfail = ma_numsigninfail + 1
				WHERE
					ma_idx = v_member.ma_idx;
				v_numFail = v_member.ma_numsigninfail + 1;
			END IF;

			--if system use password block,
			SELECT INTO v_use_block pl_opt2 FROM idc_tb_policy WHERE pl_no = 154;
			IF v_use_block = 1 THEN
				IF v_numFail >= 3 THEN
					UPDATE idc_tb_mbracc SET
						ma_isvalidacc = FALSE,
						ma_numsigninfail = 0,
						ma_signinfaildate = '1983-09-25',
						ma_passwordblockdate = CURRENT_TIMESTAMP
					WHERE
						ma_idx = v_member.ma_idx;

					RAISE NOTICE 'ACCOUNT_IS_BLOCKED';
				END IF;
			END IF;

			RAISE NOTICE 'LOGIN_FAIL_TIME_ %', v_numFail;

			RETURN 0; -- dummy
		END IF;
	END IF;
END;
$$
;

alter function idc_loginmember(varchar, varchar) owner to dskim
;

create function idc_makenewprice(v_code character varying, v_date_from date, v_user_price numeric, v_remark character varying, v_created_by character varying) returns void
	language plpgsql
as $$
DECLARE
	v_last_ip_idx integer;
BEGIN

	SELECT INTO v_last_ip_idx max(ip_idx) FROM idc_tb_item_price WHERE it_code = v_code;

	UPDATE idc_tb_item_price SET ip_date_to = v_date_from - 1 WHERE ip_idx = v_last_ip_idx;

	INSERT INTO idc_tb_item_price (it_code, ip_date_from, ip_user_price, ip_remark, ip_updated, ip_created, ip_created_by, ip_updated_by)
	VALUES(v_code, v_date_from, v_user_price, v_remark, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, v_created_by, v_created_by);
END;
$$
;

alter function idc_makenewprice(varchar, date, numeric, varchar, varchar) owner to dskim
;

create function idc_makenewpricenet(v_code character varying, v_date_from date, v_price_kurs numeric, v_price_dollar numeric, v_remark character varying, v_created_by character varying) returns void
	language plpgsql
as $$
DECLARE
	v_last_ipn_idx integer;
BEGIN

	SELECT INTO v_last_ipn_idx max(ipn_idx) FROM idc_tb_item_price_net WHERE it_code = v_code;

	UPDATE idc_tb_item_price_net SET ipn_date_to = v_date_from - 1 WHERE ipn_idx = v_last_ipn_idx;

	INSERT INTO idc_tb_item_price_net (it_code, ipn_date_from, ipn_price_kurs, ipn_price_dollar, ipn_created, ipn_created_by, ipn_remark)
	VALUES (v_code, v_date_from, v_price_kurs, v_price_dollar, CURRENT_TIMESTAMP, v_created_by, v_remark);

END;
$$
;

alter function idc_makenewpricenet(varchar, date, numeric, numeric, varchar, varchar) owner to dskim
;

create function idc_movebillingcode(v_old_code character varying, v_new_code character varying, v_old_dept character varying, v_new_dept character varying, v_old_type_invoice integer, v_new_type_invoice integer, v_inv_date date, v_updated_by character varying) returns void
	language plpgsql
as $$
DECLARE
    v_book_idx integer := 0;
    v_out_idx integer := 0;
    v_rev integer;
    v_confirm_by varchar := NULL;
    v_confirm_timestamp timestamp := NULL;
    v_wh_date date := NULL;
BEGIN

    IF v_old_type_invoice = 0 THEN
        -- Processing update idc_tb_booking, idc_tb_outgoing and status
        SELECT INTO v_book_idx book_idx FROM idc_tb_booking WHERE book_doc_ref = v_old_code AND book_doc_type = 1;
        SELECT INTO v_out_idx out_idx FROM idc_tb_outgoing_v2 WHERE out_doc_ref = v_old_code;

        IF v_new_type_invoice = 0 THEN
            IF v_old_dept != v_new_dept THEN
                UPDATE idc_tb_booking SET
                  book_code         = 'D' || substr(v_new_code,2,12),
                  book_doc_ref      = v_new_code,
                  book_dept         = v_new_dept
                WHERE book_idx = v_book_idx;


                UPDATE idc_tb_outgoing_v2 SET
                  out_code         = 'D' || substr(v_new_code,2,12),
                  out_doc_ref      = v_new_code,
                  out_dept         = v_new_dept
                WHERE out_idx = v_out_idx;

                UPDATE idc_tb_log_detail SET log_document_no = 'D'||substr(v_new_code,2,12) WHERE log_document_no = 'D'||substr(v_old_code,2,12);
            END IF;

            SELECT INTO v_confirm_by bill_cfm_wh_delivery_by_account FROM idc_tb_billing WHERE bill_code = v_old_code;
            SELECT INTO v_confirm_timestamp bill_cfm_wh_delivery_timestamp FROM idc_tb_billing WHERE bill_code = v_old_code;
            SELECT INTO v_wh_date bill_cfm_wh_date FROM idc_tb_billing WHERE bill_code = v_old_code;
        ELSIF v_new_type_invoice = 1 THEN
            DELETE FROM idc_tb_booking WHERE book_idx = v_book_idx;
            DELETE FROM idc_tb_outgoing_v2 WHERE out_doc_ref in (v_old_code);
            DELETE FROM idc_tb_log_detail WHERE log_document_no in ('D' || substr(v_old_code,2,12));
        END IF;
    END IF;

    SELECT INTO v_rev bill_revesion_time FROM idc_tb_billing WHERE bill_code = v_old_code;
/*
    IF v_old_type_invoice = v_new_type_invoice THEN
        SELECT INTO v_rev bill_revesion_time FROM idc_tb_billing WHERE bill_code = v_old_code;
    ELSIF v_old_type_invoice != v_new_type_invoice THEN
        v_rev   = -1;
    END IF;
*/
    IF v_old_dept != v_new_dept THEN
        DELETE FROM idc_tb_payment WHERE bill_code = v_old_code;
    END IF;

    UPDATE idc_tb_billing SET
        bill_code                       = v_new_code,
        bill_dept                       = v_new_dept,
        bill_type_invoice               = v_new_type_invoice,
        bill_type_billing               = v_new_type_invoice+1,
        bill_do_no                      = 'D' || substr(v_new_code,2,12),
        bill_do_date                    = bill_inv_date,
        bill_sj_code                    = 'J' || substr(v_new_code,2,12),
        bill_revesion_time              = v_rev,
        bill_lastupdated_by_account     = v_updated_by,
        bill_lastupdated_timestamp      = CURRENT_TIMESTAMP,
        bill_cfm_wh_delivery_by_account = v_confirm_by,
        bill_cfm_wh_delivery_timestamp  = v_confirm_timestamp,
        bill_cfm_wh_date                = v_wh_date
    WHERE
        bill_code = v_old_code;

    UPDATE idc_tb_billing_item SET bill_code = v_new_code WHERE bill_code = v_old_code;

END;
$$
;

alter function idc_movebillingcode(varchar, varchar, varchar, varchar, integer, integer, date, varchar) owner to dskim
;

create function idc_moveordertype(v_code character varying, v_old_type_invoice integer, v_new_type_invoice integer, v_po_date date, v_updated_by character varying) returns void
	language plpgsql
as $$
DECLARE
	v_book_idx integer := 0;
	v_wh_account varchar;
	v_wh_timestamp timestamp;
	v_rev integer;
	v_ship_to varchar;
	v_received_by varchar;
BEGIN

	IF v_old_type_invoice = 0 THEN
		SELECT INTO v_book_idx book_idx FROM idc_tb_booking WHERE book_doc_ref = v_code AND book_doc_type = 2;
		IF v_old_type_invoice != v_new_type_invoice THEN
			DELETE FROM idc_tb_booking WHERE book_idx = v_book_idx;
		END IF;
	END IF;

	IF v_new_type_invoice = 0 THEN
		v_wh_account	= '';
		v_wh_timestamp	= NULL;

		SELECT INTO v_ship_to ord_ship_to FROM idc_tb_order WHERE ord_code = v_code;
		SELECT INTO v_received_by ord_received_by FROM idc_tb_order WHERE ord_code = v_code;
		INSERT INTO idc_tb_booking(
			book_code, book_dept, cus_code, book_doc_ref, book_date, book_doc_type, book_type, book_received_by
		) VALUES (
			'D'||substr(v_code,2,12), 'A', v_ship_to, v_code, v_po_date, 2, 1, v_received_by
		);
	ELSIF v_new_type_invoice = 1 THEN
		v_wh_account	= v_updated_by;
		v_wh_timestamp	= CURRENT_TIMESTAMP;
	END IF;

	IF v_old_type_invoice = v_new_type_invoice THEN
		SELECT INTO v_rev ord_revision_time FROM idc_tb_order WHERE ord_code = v_code;
	ELSIF v_old_type_invoice != v_new_type_invoice THEN
		v_rev	= -1;
	END IF;

	UPDATE idc_tb_order SET
		ord_type_invoice 				= v_new_type_invoice,
		ord_revision_time				= v_rev,
		ord_cfm_wh_delivery_by_account	= v_wh_account,
		ord_cfm_wh_delivery_timestamp	= v_wh_timestamp
	WHERE ord_code = v_code;

END;
$$
;

alter function idc_moveordertype(varchar, integer, integer, date, varchar) owner to dskim
;

create function idc_movestocklocation(v_log_by_account character varying, v_it_code character varying[], v_it_type integer[], v_it_location_from integer[], v_it_location_to integer[], v_it_qty numeric[], v_it_remark character varying[], v_ed_it_code character varying[], v_ed_it_location integer[], v_ed_it_type integer[], v_ed_it_date character varying[], v_ed_it_qty numeric[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_j integer := 1;
	v_k integer := 1;
	v_cur_mv_idx integer;
	v_temp_date date;
	v_log_code varchar;
BEGIN

	WHILE v_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_move_stock (
			it_code, mv_from_wh, mv_to_wh,
			mv_from_type, mv_to_type, mv_qty, mv_by_account, mv_remark
		) VALUES(
			v_it_code[v_i], v_it_location_from[v_i], v_it_location_to[v_i],
			v_it_type[v_i], v_it_type[v_i], v_it_qty[v_i], v_log_by_account, v_it_remark[v_i]
		);
		v_cur_mv_idx := currval('idc_tb_move_stock_mv_idx_seq');

		WHILE v_ed_it_code[v_j] IS NOT NULL AND v_ed_it_location[v_j]=v_it_location_from[v_i] AND v_ed_it_type[v_j]=v_it_type[v_i] LOOP

			v_temp_date = v_ed_it_date[v_j];

			INSERT INTO idc_tb_move_stock_ed (
				it_code, mv_idx, mved_from_wh, mved_to_wh,
				mved_from_type, mved_to_type, mved_expired_date, mved_qty
			) VALUES (
				v_ed_it_code[v_j], v_cur_mv_idx, v_ed_it_location[v_j], v_it_location_to[v_i],
				v_ed_it_type[v_j], v_ed_it_type[v_j], v_temp_date, v_ed_it_qty[v_j]
			);

			v_j := v_j + 1;
		END LOOP;

		/* History stock */
		SELECT INTO v_log_code idc_insertStockLog(
			v_it_code[v_i], v_it_location_from[v_i], v_it_type[v_i], 30, v_cur_mv_idx,
			null, null, v_log_by_account, false, v_it_qty[v_i]);
		SELECT INTO v_log_code idc_insertStockLog(
			v_it_code[v_i], v_it_location_to[v_i], v_it_type[v_i], 19, v_cur_mv_idx,
			null, null, v_log_by_account, true, v_it_qty[v_i]);

		v_i := v_i + 1;
	END LOOP;

END;
$$
;

alter function idc_movestocklocation(varchar, character varying[], integer[], integer[], integer[], numeric[], character varying[], character varying[], integer[], integer[], character varying[], numeric[]) owner to dskim
;

create function idc_outstandingplclaim(v_idx integer, v_it_code character varying) returns numeric
	language plpgsql
as $$
DECLARE
	v_pl_qty numeric := 0;
	v_in_qty numeric := 0;
	v_qty numeric := 0;
	r_qty record; r_qty2 record;
BEGIN

	SELECT INTO v_pl_qty sum(clit_qty) from idc_tb_claim_item where cl_idx = v_idx and it_code = v_it_code;
	FOR r_qty IN SELECT init_qty FROM idc_tb_in_claim_item WHERE cl_idx = v_idx AND it_code = v_it_code LOOP
		v_in_qty := v_in_qty + r_qty.init_qty;
	END LOOP;
	if v_in_qty is null then v_in_qty = 0; end if;
	FOR r_qty2 IN SELECT init_qty FROM idc_tb_in_claim_item_v2 WHERE cl_idx = v_idx AND it_code = v_it_code LOOP
		v_in_qty := v_in_qty + r_qty2.init_qty;
	END LOOP;

	if v_in_qty is null then v_in_qty = 0; end if;
	v_qty = v_pl_qty - v_in_qty;

	RETURN v_qty;
END;
$$
;

alter function idc_outstandingplclaim(integer, varchar) owner to dskim
;

create function idc_outstandingpllocal(v_po_code character varying, v_pl_no integer, v_it_code character varying) returns numeric
	language plpgsql
as $$
DECLARE
	v_pl_qty numeric;
	v_in_qty numeric := 0;
	v_qty  numeric;
	r_qty record;
BEGIN

	SELECT INTO v_pl_qty sum(plit_qty) from idc_tb_pl_local_item where po_code = v_po_code and pl_no = v_pl_no and it_code = v_it_code;
	FOR r_qty IN SELECT init_qty FROM idc_tb_in_local JOIN idc_tb_in_local_item USING(inlc_idx)
	WHERE po_code = v_po_code and pl_no = v_pl_no and it_code = v_it_code LOOP
		v_in_qty := v_in_qty + r_qty.init_qty;
	END LOOP;

	if v_in_qty is null then v_in_qty = 0; end if;
	v_qty = v_pl_qty - v_in_qty;

	RETURN v_qty;
END;
$$
;

alter function idc_outstandingpllocal(varchar, integer, varchar) owner to dskim
;

create function idc_plclaimdelete(v_cl_idx integer, v_incl_idx integer, v_wh_location integer, v_pl_type integer, v_pl_inv_no character varying, v_pl_date date, v_deleted_by character varying, v_it_code character varying[], v_it_qty integer[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_logs_code varchar;
BEGIN
	-- Delete from related table
	DELETE FROM idc_tb_in_claim_v2 WHERE incl_idx = v_incl_idx;

	WHILE v_it_code[v_i] IS NOT NULL LOOP
		-- Insert into idc_tb_log_detail
		SELECT INTO v_logs_code idc_getStockLogIdx(v_it_code[v_i], v_wh_location, v_pl_type, CURRENT_DATE);
		INSERT INTO idc_tb_log_detail(
			log_code, it_code, log_wh_location, log_type, log_is_revised,
			log_document_type, log_document_idx, log_document_no, log_document_date,
			log_cfm_timestamp, log_cfm_by_account, log_qty
		) VALUES (
			v_logs_code, v_it_code[v_i], v_wh_location, v_pl_type, true,
			'PL Import', v_incl_idx, v_pl_inv_no, v_pl_date,
			CURRENT_TIMESTAMP, v_deleted_by, v_it_qty[v_i] * -1
		);
		v_i := v_i + 1;
	END LOOP;

END;
$$
;

alter function idc_plclaimdelete(integer, integer, integer, integer, varchar, date, varchar, character varying[], integer[]) owner to dskim
;

create function idc_plclaiminsert(v_cl_idx integer, v_sp_code character varying, v_pl_type integer, v_invoice_no character varying, v_arrived_date date, v_checked_by character varying, v_confirmed_by character varying, v_wh_located integer, v_remark character varying, v_it_code character varying[], v_plit_arrived numeric[], v_plit_on_deli numeric[], v_ed_it_code character varying[], v_ed_qty numeric[], v_ed_date text[]) returns integer
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_j integer := 1;
	v_k integer := 1;
	v_cur_incl_idx integer := 0;
	v_exp_date date;
	v_logs_code varchar;
	v_cl_date date;
BEGIN

	--1. Insert into idc_tb_in_claim
	INSERT INTO idc_tb_in_claim_v2 (
		sp_code, cl_idx, incl_inv_no, incl_type,
		incl_checked_date, incl_checked_by, incl_warehouse,
		incl_created_by_account, incl_created_timestamp, incl_remark
	) VALUES (
		v_sp_code,v_cl_idx, v_invoice_no, v_pl_type,
		v_arrived_date, v_checked_by, v_wh_located,
		v_confirmed_by, CURRENT_TIMESTAMP, v_remark
	);
	v_cur_incl_idx := currval('idc_tb_in_claim_v2_incl_idx_seq');

	WHILE v_it_code[v_i] IS NOT NULL LOOP
		IF v_plit_arrived[v_i] > 0 THEN
			SELECT INTO v_cl_date cl_inv_date FROM idc_tb_claim WHERE cl_idx = v_cl_idx;
			SELECT INTO v_logs_code idc_getStockLogIdx(v_it_code[v_i], v_wh_located, v_pl_type, CURRENT_DATE);

			--2. Insert into idc_tb_in_claim_item
			INSERT INTO idc_tb_in_claim_item_v2 (cl_idx, incl_idx, it_code, init_qty, init_type, init_wh_location, init_confirm_date)
			VALUES (v_cl_idx, v_cur_incl_idx , v_it_code[v_i], v_plit_arrived[v_i], v_pl_type, v_wh_located, v_arrived_date);

			--3. Insert into idc_tb_log_detail
			INSERT INTO idc_tb_log_detail(
				log_code, it_code, log_wh_location, log_type,
				log_document_type, log_document_idx, log_document_no, log_document_date,
				log_cfm_timestamp, log_cfm_by_account, log_qty
			) VALUES (
				v_logs_code, v_it_code[v_i], v_wh_located, v_pl_type,
				'PL Claim', v_cur_incl_idx, v_invoice_no, v_cl_date,
				CURRENT_TIMESTAMP, v_checked_by, v_plit_arrived[v_i]
			);
		END IF;
		v_i := v_i + 1;
	END LOOP;

	--4. Insert into idc_tb_in_claim_item_ed
	WHILE v_ed_it_code[v_j] != '' LOOP
		v_exp_date := v_ed_date[v_j];
		INSERT INTO idc_tb_in_claim_item_ed (it_code, ined_wh_location, incl_idx, ined_expired_date, ined_qty)
		VALUES (v_ed_it_code[v_j], v_wh_located, v_cur_incl_idx, v_exp_date, v_ed_qty[v_j]);
		v_j := v_j + 1;
	END LOOP;

	RETURN v_cur_incl_idx;
END;
$$
;

alter function idc_plclaiminsert(integer, varchar, integer, varchar, date, varchar, varchar, integer, varchar, character varying[], numeric[], numeric[], character varying[], numeric[], text[]) owner to dskim
;

create function idc_plclaimupdate(v_cl_idx integer, v_incl_idx integer, v_wh_located integer, v_pl_type integer, v_pl_inv_no character varying, v_pl_date date, v_reconfirmed_by character varying, v_remark character varying, v_it_code character varying[], v_it_qty integer[], v_ed_it_code character varying[], v_ed_qty numeric[], v_ed_date text[], v_in_it_code character varying[], v_in_it_qty integer[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1; v_j integer; v_k integer := 1; v_l integer := 1;
	v_logs_code varchar;
	v_exp_date date;
BEGIN

	-- 1. Update idc_tb_in_claim_v2
	UPDATE idc_tb_in_claim SET
		incl_created_by_account = v_reconfirmed_by,
		incl_created_timestamp	= CURRENT_TIMESTAMP,
		incl_remark				= v_remark
	WHERE incl_idx = v_incl_idx;

	-- 2. Update idc_tb_in_claim_item_v2
	WHILE v_it_code[v_i] IS NOT NULL LOOP
		v_j := 1;
		WHILE v_in_it_code[v_j] IS NOT NULL LOOP
			IF v_it_code[v_i] = v_in_it_code[v_j] AND v_in_it_qty[v_j] != 0 THEN
				-- Update data
				UPDATE idc_tb_in_claim_item_v2 SET init_qty = init_qty + v_in_it_qty[v_j] WHERE incl_idx = v_incl_idx AND it_code = v_it_code[v_i];

				-- Insert into idc_tb_log_detail
				SELECT INTO v_logs_code idc_getStockLogIdx(v_it_code[v_i], v_wh_located, v_pl_type, CURRENT_DATE);
				INSERT INTO idc_tb_log_detail(
					log_code, it_code, log_wh_location, log_type, log_is_revised,
					log_document_type, log_document_idx, log_document_no, log_document_date,
					log_cfm_timestamp, log_cfm_by_account, log_qty
				) VALUES (
					v_logs_code, v_it_code[v_i], v_wh_located, v_pl_type, true,
					'PL Claim', v_incl_idx, v_pl_inv_no, v_pl_date,
					CURRENT_TIMESTAMP, v_reconfirmed_by, v_in_it_qty[v_j]
				);
			END IF;
			v_j := v_j + 1;
		END LOOP;
		v_i := v_i + 1;
	END LOOP;

	-- 3. Update idc_tb_in_claim_item_ed
	DELETE FROM idc_tb_in_claim_item_ed WHERE incl_idx = v_incl_idx;
	WHILE v_ed_it_code[v_k] != '' LOOP
		v_exp_date := v_ed_date[v_k];
		INSERT INTO idc_tb_in_claim_item_ed(it_code, ined_wh_location, incl_idx, ined_expired_date, ined_qty)
		VALUES (v_ed_it_code[v_k], v_wh_located, v_incl_idx, v_exp_date, v_ed_qty[v_k]);
		v_k := v_k + 1;
	END LOOP;

END;
$$
;

alter function idc_plclaimupdate(integer, integer, integer, integer, varchar, date, varchar, varchar, character varying[], integer[], character varying[], numeric[], text[], character varying[], integer[]) owner to dskim
;

create function idc_plimportdelete(v_pl_idx integer, v_inpl_idx integer, v_wh_location integer, v_pl_type integer, v_pl_inv_no character varying, v_pl_date date, v_deleted_by character varying, v_it_code character varying[], v_it_qty integer[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_logs_code varchar;
BEGIN
	-- Delete from related table
	DELETE FROM idc_tb_po_recap WHERE rcp_inpl_code = v_inpl_idx;
	DELETE FROM idc_tb_in_pl_v2 WHERE inpl_idx = v_inpl_idx;

	WHILE v_it_code[v_i] IS NOT NULL LOOP
		-- Insert into idc_tb_log_detail
		SELECT INTO v_logs_code idc_getStockLogIdx(v_it_code[v_i], v_wh_location, v_pl_type, CURRENT_DATE);
		INSERT INTO idc_tb_log_detail(
			log_code, it_code, log_wh_location, log_type, log_is_revised,
			log_document_type, log_document_idx, log_document_no, log_document_date,
			log_cfm_timestamp, log_cfm_by_account, log_qty
		) VALUES (
			v_logs_code, v_it_code[v_i], v_wh_location, v_pl_type, true,
			'PL Import', v_inpl_idx, v_pl_inv_no, v_pl_date,
			CURRENT_TIMESTAMP, v_deleted_by, v_it_qty[v_i] * -1
		);
		v_i := v_i + 1;
	END LOOP;

END;
$$
;

alter function idc_plimportdelete(integer, integer, integer, integer, varchar, date, varchar, character varying[], integer[]) owner to dskim
;

create function idc_plimportinsert(v_pl_idx integer, v_sp_code character varying, v_po_code character varying, v_pl_type integer, v_pl_inv_no character varying, v_arrived_date date, v_checked_by character varying, v_confirmed_by character varying, v_wh_located integer, v_remark character varying, v_it_code character varying[], v_plit_arrived integer[], v_plit_on_deli integer[], v_ed_it_code character varying[], v_ed_qty integer[], v_ed_date text[]) returns integer
	language plpgsql
as $$
DECLARE
    v_i integer := 1;
    v_j integer := 1;
    v_k integer := 1;
    v_cur_inpl_idx integer := 0;
    v_exp_date date;
    v_inpl_idx integer;
    v_logs_code varchar;
    v_pl_date date;
BEGIN

    --1. Insert into idc_tb_in_pl_v2
    INSERT INTO idc_tb_in_pl_v2(
        sp_code, po_code, pl_idx, inpl_inv_no, inpl_type,
        inpl_checked_date, inpl_checked_by, inpl_warehouse,
        inpl_created_by_account, inpl_created_timestamp, inpl_remark
    ) VALUES (
        v_sp_code, v_po_code, v_pl_idx, v_pl_inv_no, v_pl_type,
        v_arrived_date, v_checked_by, v_wh_located,
        v_confirmed_by, CURRENT_TIMESTAMP, v_remark
    );
    v_cur_inpl_idx := currval('idc_tb_in_pl_v2_inpl_idx_seq');

    WHILE v_it_code[v_i] IS NOT NULL LOOP
        IF v_plit_arrived[v_i] > 0 THEN
            SELECT INTO v_pl_date pl_inv_date FROM idc_tb_pl WHERE pl_idx = v_pl_idx;
            SELECT INTO v_logs_code idc_getStockLogIdx(v_it_code[v_i], v_wh_located, v_pl_type, CURRENT_DATE);

            --2. Insert into idc_tb_in_pl_item_v2
            INSERT INTO idc_tb_in_pl_item_v2 (pl_idx, inpl_idx, it_code, init_qty, init_type, init_wh_location, init_confirm_date)
            VALUES (v_pl_idx, v_cur_inpl_idx , v_it_code[v_i], v_plit_arrived[v_i], v_pl_type, v_wh_located, v_arrived_date);

            --3. Insert into idc_tb_log_detail
            INSERT INTO idc_tb_log_detail(
                log_code, it_code, log_wh_location, log_type,
                log_document_type, log_document_idx, log_document_no, log_document_date,
                log_cfm_timestamp, log_cfm_by_account, log_qty
            ) VALUES (
                v_logs_code, v_it_code[v_i], v_wh_located, v_pl_type,
                'PL Import', v_cur_inpl_idx, v_pl_inv_no, v_pl_date,
                CURRENT_TIMESTAMP, v_checked_by, v_plit_arrived[v_i]
            );
        END IF;
        v_i := v_i + 1;
    END LOOP;

    --4. Insert into idc_tb_in_pl_item_ed
    WHILE v_ed_it_code[v_j] != '' LOOP
        v_exp_date := v_ed_date[v_j];
        INSERT INTO idc_tb_in_pl_item_ed(it_code, ined_wh_location, inpl_idx, ined_expired_date, ined_qty)
        VALUES (v_ed_it_code[v_j], v_wh_located, v_cur_inpl_idx, v_exp_date, v_ed_qty[v_j]);
        v_j := v_j + 1;
    END LOOP;

    --5. Insert into idc_tb_po_recap
    WHILE v_it_code[v_k] IS NOT NULL LOOP
        INSERT INTO idc_tb_po_recap (
            rcp_sp_code, rcp_po_code, rcp_pl_code, rcp_inpl_code, rcp_confirmed_date, it_code, rcp_pl_qty
        ) VALUES (
            v_sp_code, v_po_code, v_pl_idx, v_cur_inpl_idx, v_arrived_date, v_it_code[v_k], v_plit_arrived[v_k]
        );
        v_k := v_k + 1;
    END LOOP;

    RETURN v_cur_inpl_idx;

END;
$$
;

alter function idc_plimportinsert(integer, varchar, varchar, integer, varchar, date, varchar, varchar, integer, varchar, character varying[], integer[], integer[], character varying[], integer[], text[]) owner to dskim
;

create function idc_plimportupdate(v_pl_idx integer, v_inpl_idx integer, v_wh_located integer, v_pl_type integer, v_pl_inv_no character varying, v_pl_date date, v_reconfirmed_by character varying, v_remark character varying, v_rcp_idx integer[], v_it_code character varying[], v_it_qty integer[], v_ed_it_code character varying[], v_ed_qty numeric[], v_ed_date text[], v_in_it_code character varying[], v_in_it_qty integer[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1; v_j integer; v_k integer := 1; v_l integer := 1;
	v_logs_code varchar;
	v_exp_date date;
BEGIN

	-- 1. Update idc_tb_in_pl_v2
	UPDATE idc_tb_in_pl_v2 SET
		inpl_created_by_account = v_reconfirmed_by,
		inpl_created_timestamp	= CURRENT_TIMESTAMP,
		inpl_remark				= v_remark
	WHERE inpl_idx = v_inpl_idx;

	-- 2. Update idc_tb_in_pl_item_v2
	WHILE v_it_code[v_i] IS NOT NULL LOOP
		v_j := 1;
		WHILE v_in_it_code[v_j] IS NOT NULL LOOP
			IF v_it_code[v_i] = v_in_it_code[v_j] AND v_in_it_qty[v_j] != 0 THEN
				-- Update data
				UPDATE idc_tb_in_pl_item_v2 SET init_qty = init_qty + v_in_it_qty[v_j] WHERE inpl_idx = v_inpl_idx AND it_code = v_it_code[v_i];

				-- Insert into idc_tb_log_detail
				SELECT INTO v_logs_code idc_getStockLogIdx(v_it_code[v_i], v_wh_located, v_pl_type, CURRENT_DATE);
				INSERT INTO idc_tb_log_detail(
					log_code, it_code, log_wh_location, log_type, log_is_revised,
					log_document_type, log_document_idx, log_document_no, log_document_date,
					log_cfm_timestamp, log_cfm_by_account, log_qty
				) VALUES (
					v_logs_code, v_it_code[v_i], v_wh_located, v_pl_type, true,
					'PL Import', v_inpl_idx, v_pl_inv_no, v_pl_date,
					CURRENT_TIMESTAMP, v_reconfirmed_by, v_in_it_qty[v_j]
				);
			END IF;
			v_j := v_j + 1;
		END LOOP;
		v_i := v_i + 1;
	END LOOP;

	-- 3. Update idc_tb_in_pl_item_ed
	DELETE FROM idc_tb_in_pl_item_ed WHERE inpl_idx = v_inpl_idx;
	WHILE v_ed_it_code[v_k] != '' LOOP
		v_exp_date := v_ed_date[v_k];
		INSERT INTO idc_tb_in_pl_item_ed(it_code, ined_wh_location, inpl_idx, ined_expired_date, ined_qty)
		VALUES (v_ed_it_code[v_k], v_wh_located, v_inpl_idx, v_exp_date, v_ed_qty[v_k]);
		v_k := v_k + 1;
	END LOOP;

	-- 4. Update idc_tb_po_recap
	WHILE v_it_code[v_l] IS NOT NULL LOOP
		UPDATE idc_tb_po_recap SET rcp_pl_qty = v_it_qty[v_l] WHERE rcp_idx = v_rcp_idx[v_l] AND rcp_pl_code = v_pl_idx AND it_code = v_it_code[v_l];
		v_l := v_l + 1;
	END LOOP;

END;
$$
;

alter function idc_plimportupdate(integer, integer, integer, integer, varchar, date, varchar, varchar, integer[], character varying[], integer[], character varying[], numeric[], text[], character varying[], integer[]) owner to dskim
;

create function idc_pllocalinsert(v_sp_code character varying, v_po_code character varying, v_pl_no integer, v_pl_type integer, v_wh_located integer, v_arrived_date date, v_checked_by character varying, v_confirmed_by character varying, v_remark character varying, v_it_code character varying[], v_plit_arrived integer[], v_ed_it_code character varying[], v_ed_it_qty integer[], v_ed_it_date text[]) returns integer
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_j integer := 1;
	v_k integer := 1;
	v_cur_inlc_idx integer := 0;
	v_exp_date date;
	v_logs_code varchar;
	v_pl_date date;
BEGIN

	INSERT INTO idc_tb_in_local_v2 (
		sp_code, po_code, pl_no, inlc_type, inlc_checked_date, inlc_checked_by,
		inlc_created_by_account, inlc_created_timestamp, inlc_remark, inlc_warehouse
	) VALUES (
		v_sp_code, v_po_code, v_pl_no, v_pl_type, v_arrived_date, v_checked_by,
		v_confirmed_by, CURRENT_TIMESTAMP, v_remark, v_wh_located
	);
	v_cur_inlc_idx := currval('idc_tb_in_local_v2_inlc_idx_seq');

	WHILE v_it_code[v_i] IS NOT NULL LOOP
		IF v_plit_arrived[v_i] > 0 THEN
			SELECT INTO v_pl_date pl_date FROM idc_tb_pl_local WHERE po_code = v_po_code and pl_no = v_pl_no;
			SELECT INTO v_logs_code idc_getStockLogIdx(v_it_code[v_i], v_wh_located, v_pl_type, CURRENT_DATE);

			--2. Insert into idc_tb_in_pl_item
			INSERT INTO idc_tb_in_local_item_v2 (inlc_idx, it_code, init_wh_location, init_type, init_qty, init_confirm_date)
			VALUES (v_cur_inlc_idx , v_it_code[v_i], v_wh_located, v_pl_type, v_plit_arrived[v_i], v_arrived_date);

			--3. Insert into idc_tb_log_detail
			INSERT INTO idc_tb_log_detail(
				log_code, it_code, log_wh_location, log_type,
				log_document_type, log_document_idx, log_document_no, log_document_date,
				log_cfm_timestamp, log_cfm_by_account, log_qty
			) VALUES (
				v_logs_code, v_it_code[v_i], v_wh_located, v_pl_type,
				'PL Local', v_cur_inlc_idx, v_po_code||' #'||v_pl_no, v_pl_date,
				CURRENT_TIMESTAMP, v_checked_by, v_plit_arrived[v_i]
			);
		END IF;
		v_i := v_i + 1;
	END LOOP;

	--4. Insert into idc_tb_in_local_item_ed
	WHILE v_ed_it_code[v_j] != '' LOOP
		v_exp_date := v_ed_it_date[v_j];
		INSERT INTO idc_tb_in_local_item_ed (it_code, ined_wh_location, inlc_idx, ined_expired_date, ined_qty)
		VALUES (v_ed_it_code[v_j], v_wh_located, v_cur_inlc_idx, v_exp_date, v_ed_it_qty[v_j]);
		v_j := v_j + 1;
	END LOOP;

	RETURN v_cur_inlc_idx;
END;
$$
;

alter function idc_pllocalinsert(varchar, varchar, integer, integer, integer, date, varchar, varchar, varchar, character varying[], integer[], character varying[], integer[], text[]) owner to dskim
;

create function idc_pllocalupdate(v_inlc_idx integer, v_po_code character varying, v_pl_no integer, v_pl_type integer, v_wh_located integer, v_arrived_date date, v_reconfirmed_by character varying, v_remark character varying, v_it_code character varying[], v_plit_arrived integer[], v_ed_it_code character varying[], v_ed_it_qty integer[], v_ed_it_date text[], v_in_it_code character varying[], v_in_it_qty integer[]) returns void
	language plpgsql
as $$
DECLARE
	v_pl_date date;
	v_i integer := 1; v_j integer; v_k integer := 1; v_l integer := 1;
	v_logs_code varchar;
	v_exp_date date;
BEGIN

	-- 1. Update idc_tb_in_local_v2
	UPDATE idc_tb_in_local_v2 SET
		inlc_created_by_account = v_reconfirmed_by,
		inlc_created_timestamp	= CURRENT_TIMESTAMP,
		inlc_remark				= v_remark
	WHERE inlc_idx = v_inlc_idx;

	-- 2. Update idc_tb_in_local_item_v2
	SELECT INTO v_pl_date pl_date FROM idc_tb_pl_local WHERE po_code = v_po_code and pl_no = v_pl_no;
	WHILE v_it_code[v_i] IS NOT NULL LOOP
		v_j := 1;
		WHILE v_in_it_code[v_j] IS NOT NULL LOOP
			IF v_it_code[v_i] = v_in_it_code[v_j] AND v_in_it_qty[v_j] != 0 THEN
				-- Update data
				UPDATE idc_tb_in_local_item_v2 SET init_qty = init_qty + v_in_it_qty[v_j] WHERE inlc_idx = v_inlc_idx AND it_code = v_it_code[v_i];

				-- Insert into idc_tb_log_detail
				SELECT INTO v_logs_code idc_getStockLogIdx(v_it_code[v_i], v_wh_located, v_pl_type, CURRENT_DATE);
				INSERT INTO idc_tb_log_detail(
					log_code, it_code, log_wh_location, log_type, log_is_revised,
					log_document_type, log_document_idx, log_document_no, log_document_date,
					log_cfm_timestamp, log_cfm_by_account, log_qty
				) VALUES (
					v_logs_code, v_it_code[v_i], v_wh_located, v_pl_type, true,
					'PL Local', v_inlc_idx, v_po_code||' #'||v_pl_no, v_pl_date,
					CURRENT_TIMESTAMP, v_reconfirmed_by, v_in_it_qty[v_j]
				);
			END IF;
			v_j := v_j + 1;
		END LOOP;
		v_i := v_i + 1;
	END LOOP;

	-- 3. Update idc_tb_in_local_item_ed
	DELETE FROM idc_tb_in_local_item_ed WHERE inlc_idx = v_inlc_idx;
	WHILE v_ed_it_code[v_k] != '' LOOP
		v_exp_date := v_ed_date[v_k];
		INSERT INTO idc_tb_in_local_item_ed(it_code, ined_wh_location, inpl_idx, ined_expired_date, ined_qty)
		VALUES (v_ed_it_code[v_k], v_wh_located, v_inlc_idx, v_exp_date, v_ed_it_qty[v_k]);
		v_k := v_k + 1;
	END LOOP;

END;
$$
;

alter function idc_pllocalupdate(integer, varchar, integer, integer, integer, date, varchar, varchar, character varying[], integer[], character varying[], integer[], text[], character varying[], integer[]) owner to dskim
;

create function idc_recordinitial() returns void
	language plpgsql
as $$
DECLARE
	rec record;
	v_logs_code varchar;
BEGIN
	FOR rec IN SELECT * FROM idc_tb_initial_stock_v2 where
	it_code in ('1S003','1S004','1S005') ORDER BY it_code LOOP

		SELECT INTO v_logs_code idc_getStockLogIdx(rec.it_code, rec.init_wh_location, rec.init_type, CURRENT_DATE);
		INSERT INTO idc_tb_log_detail(
			log_code, it_code, log_wh_location, log_type,
			log_document_type, log_document_idx, log_document_no, log_document_date,
			log_cfm_timestamp, log_cfm_by_account, log_qty
		) VALUES (
			v_logs_code, rec.it_code, rec.init_wh_location, rec.init_type,
			'Initial Stock', null, 'Initial Stock', CURRENT_DATE,
			CURRENT_TIMESTAMP, 'neki', rec.init_qty
		);
	END LOOP;
END;
$$
;

alter function idc_recordinitial() owner to dskim
;

create function idc_removememberpermission(v_from character varying, v_idx1 integer, v_idx2 integer[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
BEGIN

	IF v_from = 'member' THEN
		WHILE v_idx2[v_i] IS NOT NULL LOOP
			DELETE FROM idc_tb_gmember WHERE ma_idx = v_idx1 AND gr_idx = v_idx2[v_i];
			UPDATE idc_tb_grade SET gr_total_member = gr_total_member - 1 WHERE gr_idx = v_idx2[v_i];
			v_i := v_i + 1;
		END LOOP;
	ELSE
		WHILE v_idx2[v_i] IS NOT NULL LOOP
			DELETE FROM idc_tb_gmember WHERE gr_idx = v_idx1 AND ma_idx = v_idx2[v_i];
			v_i := v_i + 1;
		END LOOP;

		UPDATE idc_tb_grade SET gr_total_member = gr_total_member - (v_i - 1) WHERE gr_idx = v_idx1;
	END IF;

END;
$$
;

alter function idc_removememberpermission(varchar, integer, integer[]) owner to dskim
;

create function idc_reviseapotikorder(v_is_dirty_item boolean, v_code character varying, v_type character varying, v_received_by character varying, v_confirm_by character varying, v_po_date date, v_po_no character varying, v_vat numeric, v_cus_to character varying, v_cus_to_attn character varying, v_cus_to_address character varying, v_ship_to character varying, v_ship_to_attn character varying, v_ship_to_address character varying, v_bill_to character varying, v_bill_to_attn character varying, v_bill_to_address character varying, v_it_code character varying[], v_it_unit_price numeric[], v_it_qty integer[], v_it_delivery date[], v_price_discount numeric, v_price_chk integer, v_delivery_chk integer, v_delivery_by character varying, v_delivery_freight_charge numeric, v_payment_chk integer, v_payment_widthin_days integer, v_payment_closing_on date, v_payment_cash_by character varying, v_payment_check_by character varying, v_payment_transfer_by character varying, v_payment_giro_by character varying, v_remark character varying, v_lastupdated_by_account character varying) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	rec1 record;
	rec2 record;
BEGIN
	UPDATE idc_tb_order SET
		ord_lastupdated_by_account = v_lastupdated_by_account,
		ord_lastupdated_timestamp = CURRENT_TIMESTAMP,
		ord_po_date = v_po_date,
		ord_po_no = v_po_no,
		ord_received_by = v_received_by,
		ord_confirm_by = v_confirm_by,
		ord_revision_time = ord_revision_time + 1,
		ord_vat= v_vat,
		ord_cus_to = v_cus_to,
		ord_cus_to_attn = v_cus_to_attn,
		ord_cus_to_address = v_cus_to_address,
		ord_ship_to = v_ship_to,
		ord_ship_to_attn = v_ship_to_attn,
		ord_ship_to_address = v_ship_to_address,
		ord_bill_to = v_bill_to,
		ord_bill_to_attn = v_bill_to_attn,
		ord_bill_to_address = v_bill_to_address,
		ord_price_discount = v_price_discount,
		ord_price_chk = v_price_chk,
		ord_delivery_chk = v_delivery_chk,
		ord_delivery_by = v_delivery_by,
		ord_delivery_freight_charge = v_delivery_freight_charge,
		ord_payment_chk = v_payment_chk,
		ord_payment_widthin_days = v_payment_widthin_days,
		ord_payment_closing_on = v_payment_closing_on,
		ord_payment_cash_by = v_payment_cash_by,
		ord_payment_check_by = v_payment_check_by,
		ord_payment_transfer_by = v_payment_transfer_by,
		ord_payment_giro_by = v_payment_giro_by,
		ord_remark = v_remark
	WHERE
	ord_code = v_code;

	IF v_is_dirty_item THEN
		-- Delete order item
		DELETE FROM idc_tb_order_item WHERE ord_code = v_code;

		-- Make order item again
		IF substr(v_code, 1, 2) = 'OO' THEN
			WHILE v_it_code[v_i] IS NOT NULL LOOP
				INSERT INTO idc_tb_order_item (
					ord_code, cus_code, it_code, odit_oo_qty, odit_qty, odit_unit_price, odit_delivery, odit_date
				) VALUES (
					v_code, v_cus_to, v_it_code[v_i], v_it_qty[v_i], v_it_qty[v_i],
					v_it_unit_price[v_i], v_it_delivery[v_i], v_po_date
				);
				v_i := v_i + 1;
			END LOOP;
		ELSE
			WHILE v_it_code[v_i] IS NOT NULL LOOP
				INSERT INTO idc_tb_order_item (
					ord_code, cus_code, it_code, odit_ok_qty, odit_qty, odit_unit_price, odit_delivery, odit_date
				) VALUES (
					v_code, v_cus_to, v_it_code[v_i], v_it_qty[v_i], v_it_qty[v_i],
					v_it_unit_price[v_i], v_it_delivery[v_i], v_po_date
				);
				v_i := v_i + 1;
			END LOOP;
		END IF;
	END IF;

END;
$$
;

alter function idc_reviseapotikorder(boolean, varchar, varchar, varchar, varchar, date, varchar, numeric, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, character varying[], numeric[], integer[], date[], numeric, integer, integer, varchar, numeric, integer, integer, date, varchar, varchar, varchar, varchar, varchar, varchar) owner to dskim
;

create function idc_reviseapotikreturnorder(v_code character varying, v_ord_code character varying, v_ord_date date, v_received_by character varying, v_confirm_by character varying, v_po_date date, v_po_no character varying, v_type character varying, v_vat numeric, v_cus_to character varying, v_cus_to_attn character varying, v_cus_to_address character varying, v_ship_to character varying, v_ship_to_attn character varying, v_ship_to_address character varying, v_bill_to character varying, v_bill_to_attn character varying, v_bill_to_address character varying, v_it_code character varying[], v_it_remark character varying[], v_it_unit_price numeric[], v_it_qty integer[], v_price_discount numeric, v_price_chk integer, v_delivery_chk integer, v_delivery_by character varying, v_delivery_freight_charge numeric, v_payment_chk integer, v_payment_widthin_days integer, v_payment_closing_on date, v_payment_cash_by character varying, v_payment_check_by character varying, v_payment_transfer_by character varying, v_payment_giro_by character varying, v_remark character varying, v_lastupdated_by_account character varying) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	rec1 record;
	rec2 record;
BEGIN
	UPDATE idc_tb_return_order SET
		reor_lastupdated_by_account = v_lastupdated_by_account,
		reor_lastupdated_timestamp = CURRENT_TIMESTAMP,
		reor_po_date = v_po_date,
		reor_po_no = v_po_no,
		reor_received_by = v_received_by,
		reor_confirm_by = v_confirm_by,
		reor_revesion_time = reor_revesion_time + 1,
		reor_vat= v_vat,
		reor_cus_to = v_cus_to,
		reor_cus_to_attn = v_cus_to_attn,
		reor_cus_to_address = v_cus_to_address,
		reor_ship_to = v_ship_to,
		reor_ship_to_attn = v_ship_to_attn,
		reor_ship_to_address = v_ship_to_address,
		reor_bill_to = v_bill_to,
		reor_bill_to_attn = v_bill_to_attn,
		reor_bill_to_address = v_bill_to_address,
		reor_price_discount = v_price_discount,
		reor_price_chk = v_price_chk,
		reor_delivery_chk = v_delivery_chk,
		reor_delivery_by = v_delivery_by,
		reor_delivery_freight_charge = v_delivery_freight_charge,
		reor_payment_chk = v_payment_chk,
		reor_payment_widthin_days = v_payment_widthin_days,
		reor_payment_closing_on = v_payment_closing_on,
		reor_payment_cash_by = v_payment_cash_by,
		reor_payment_check_by = v_payment_check_by,
		reor_payment_transfer_by = v_payment_transfer_by,
		reor_payment_giro_by = v_payment_giro_by,
		reor_remark = v_remark
	WHERE reor_code = v_code;
	DELETE FROM idc_tb_return_order_item WHERE reor_code = v_code;

	WHILE v_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_return_order_item (reor_code, cus_code, it_code, roit_qty, roit_unit_price, roit_remark, roit_date)
		VALUES (v_code, v_cus_to, v_it_code[v_i], v_it_qty[v_i], v_it_unit_price[v_i], v_it_remark[v_i], v_po_date);
		v_i := v_i + 1;
	END LOOP;

END;
$$
;

alter function idc_reviseapotikreturnorder(varchar, varchar, date, varchar, varchar, date, varchar, varchar, numeric, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, character varying[], character varying[], numeric[], integer[], numeric, integer, integer, varchar, numeric, integer, integer, date, varchar, varchar, varchar, varchar, varchar, varchar) owner to dskim
;

create function idc_revisepo(v_code character varying, v_sp_code character varying, v_sp_name character varying, v_po_date date, v_layout_type integer, v_received_by character varying, v_inputed_by character varying, v_shipment_mode character varying, v_mode_desc character varying, v_po_type integer, v_total_qty integer, v_total_amount numeric, v_print_remark character varying, v_remark character varying, v_prepared_by character varying, v_confirmed_by character varying, v_lastupdated_by_account character varying, v_revision_time integer, v_icat_midx integer[], v_it_code character varying[], v_poit_item character varying[], v_poit_desc character varying[], v_poit_unit_price numeric[], v_poit_qty integer[], v_poit_remark character varying[], v_poit_att character varying[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
BEGIN

	/* update idc_tb_po */
	UPDATE idc_tb_po SET
		po_sp_code			= v_sp_code,
		po_sp_name			= v_sp_name,
		po_date				= v_po_date,
		po_lastupdated_by_account = v_lastupdated_by_account,
		po_lastupdated_timestamp	= CURRENT_TIMESTAMP,
		po_revesion_time	= v_revision_time + 1,
		po_layout_type		= v_layout_type,
		po_received_by		= v_received_by,
		po_shipment_mode	= v_shipment_mode,
		po_shipment_desc	= v_mode_desc,
		po_type				= v_po_type,
		po_total_qty		= v_total_qty,
		po_total_amount		= v_total_amount,
		po_doc_remark		= v_print_remark,
		po_remark			= v_remark,
		po_prepared_by		= v_prepared_by,
		po_confirmed_by		= v_confirmed_by
	WHERE po_code = v_code;

	/* update idc_tb_po_item */
	DELETE FROM idc_tb_po_item WHERE po_code = v_code;
	WHILE v_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_po_item (
			po_code, icat_midx, it_code, poit_item, poit_desc,
			poit_qty, poit_unit_price, poit_remark, poit_attribute
		) VALUES (
			v_code, v_icat_midx[v_i], v_it_code[v_i], v_poit_item[v_i], v_poit_desc[v_i],
			v_poit_qty[v_i], v_poit_unit_price[v_i], v_poit_remark[v_i], v_poit_att[v_i]
		);
		v_i := v_i + 1;
	END LOOP;

	UPDATE idc_tb_pl SET pl_layout_type = v_layout_type WHERE po_code = v_code;
END;
$$
;

alter function idc_revisepo(varchar, varchar, varchar, date, integer, varchar, varchar, varchar, varchar, integer, integer, numeric, varchar, varchar, varchar, varchar, varchar, integer, integer[], character varying[], character varying[], character varying[], numeric[], integer[], character varying[], character varying[]) owner to dskim
;

create function idc_reviseregitemstatus(v_code character varying, v_it_idx integer[], v_it_guarantee integer[], v_it_guarantee_period date[], v_it_cus_complain character varying[], v_it_tech_analyze character varying[], v_it_grd_status integer[], v_it_incoming character varying[], v_it_finish character varying[], v_it_delivery character varying[], v_it_chk integer[], v_it_replace_product character varying[], v_it_replace_part character varying[], v_it_cost integer[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_incoming date;
	v_finish date;
	v_deli date;
BEGIN

	WHILE v_it_idx[v_i] IS NOT NULL LOOP

		IF v_it_grd_status[v_i] = 0 THEN
			v_incoming	:= v_it_incoming[v_i]::date;
			v_finish	:= null;
			v_deli		:= null;
		ELSIF v_it_grd_status[v_i] = 1 THEN
			v_incoming	:= v_it_incoming[v_i]::date;
			v_finish	:= v_it_finish[v_i]::date;
			v_deli		:= null;
		ELSIF v_it_grd_status[v_i] = 2 THEN
			v_incoming	:= v_it_incoming[v_i]::date;
			v_finish	:= v_it_finish[v_i]::date;
			v_deli		:= v_it_delivery[v_i]::date;
		END IF;

		UPDATE idc_tb_service_reg_item SET
			sgit_is_guarantee		 = v_it_guarantee[v_i],
			sgit_guarantee			 = v_it_guarantee_period[v_i],
			sgit_status				 = v_it_grd_status[v_i],
			sgit_incoming_date		 = v_incoming,
			sgit_finishing_date		 = v_finish,
			sgit_delivery_date		 = v_deli,
			sgit_service_action_chk	 = v_it_chk[v_i],
			sgit_replacement_product = v_it_replace_product[v_i],
			sgit_replacement_part	 = v_it_replace_part[v_i],
			sgit_cost				 = v_it_cost[v_i],
			sgit_cus_complain		 = v_it_cus_complain[v_i],
			sgit_tech_analyze		 = v_it_tech_analyze[v_i]
		WHERE sg_code = v_code AND sgit_idx = v_it_idx[v_i];

		v_i := v_i + 1;
	END LOOP;

END;
$$
;

alter function idc_reviseregitemstatus(varchar, integer[], integer[], date[], character varying[], character varying[], integer[], character varying[], character varying[], character varying[], integer[], character varying[], character varying[], integer[]) owner to dskim
;

create function idc_reviseregitemstatusdeli(v_code character varying, v_it_idx integer[], v_it_grd_status integer[], v_it_delivery character varying[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_status integer;
	v_deli date;
BEGIN

	WHILE v_it_idx[v_i] IS NOT NULL LOOP

		IF v_it_delivery[v_i] != '' THEN
			v_deli		:= v_it_delivery[v_i]::date;
			v_status 	:= 2;
		ELSE
			v_deli		:= null;
			v_status 	:= v_it_grd_status[v_i];
		END IF;

		UPDATE idc_tb_service_reg_item SET
			sgit_status				 = v_status,
			sgit_delivery_date		 = v_deli
		WHERE sg_code = v_code AND sgit_idx = v_it_idx[v_i];

		v_i := v_i + 1;
	END LOOP;

END;
$$
;

alter function idc_reviseregitemstatusdeli(varchar, integer[], integer[], character varying[]) owner to dskim
;

create function idc_reviserequeststocktodemo(v_code character varying, v_book_idx integer, v_issued_by character varying, v_issued_date date, v_log_by_account character varying, v_remark character varying, v_revesion_time integer, v_wh_it_code character varying[], v_wh_it_qty numeric[], v_wh_it_remark character varying[]) returns character varying
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_j integer := 1;
BEGIN

	/* Update idc_tb_request */
	UPDATE idc_tb_request SET
		req_issued_by		= v_issued_by,
		req_issued_date		= v_issued_date,
		req_lastupdated_by_account	= v_log_by_account,
		req_lastupdated_timestamp	= CURRENT_TIMESTAMP,
		req_revesion_time			= v_revesion_time+1,
		req_remark					= v_remark
	WHERE req_code = v_code;

	/* Update idc_tb_request_item */
	DELETE FROM idc_tb_request_item WHERE req_code = v_code;
	WHILE v_wh_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_request_item (req_code, it_code, rqit_type, rqit_qty, rqit_remark)
		VALUES (v_code, v_wh_it_code[v_i], 0, v_wh_it_qty[v_i], v_wh_it_remark[v_i]);
		v_i := v_i + 1;
	END LOOP;

	/* Update idc_tb_booking */
	UPDATE idc_tb_booking SET
		book_date			= v_issued_date,
		book_received_by	= v_issued_by
	WHERE book_idx = v_book_Idx;

	/* Update idc_tb_booking_item */
	DELETE FROM idc_tb_booking_item WHERE book_idx = v_book_idx;
	WHILE v_wh_it_code[v_j] IS NOT NULL LOOP
		INSERT INTO idc_tb_booking_item (
			book_idx, it_code, boit_it_code_for, boit_type,
			boit_qty, boit_function, boit_remark
		) VALUES (
			v_book_idx, v_wh_it_code[v_j], v_wh_it_code[v_j], 0,
			v_wh_it_qty[v_j], 1, v_wh_it_remark[v_j]
		);
		v_j := v_j + 1;
	END LOOP;

	RETURN v_code;
END;
$$
;

alter function idc_reviserequeststocktodemo(varchar, integer, varchar, date, varchar, varchar, integer, character varying[], numeric[], character varying[]) owner to dskim
;

create function idc_reviserequeststocktodemorevised(v_code character varying, v_book_idx integer, v_issued_by character varying, v_issued_date date, v_log_by_account character varying, v_remark character varying, v_revesion_time integer, v_wh_it_code character varying[], v_wh_it_qty numeric[], v_wh_it_remark character varying[], v_rcp_it_code character varying[], v_rcp_it_qty numeric[]) returns character varying
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_j integer := 1;
	v_k integer := 1;
BEGIN

	/* Update idc_tb_request */
	UPDATE idc_tb_request SET
		req_issued_by		= v_issued_by,
		req_issued_date		= v_issued_date,
		req_lastupdated_by_account	= v_log_by_account,
		req_lastupdated_timestamp	= CURRENT_TIMESTAMP,
		req_revesion_time			= v_revesion_time+1,
		req_remark					= v_remark
	WHERE req_code = v_code;

	/* Update idc_tb_request_item */
	DELETE FROM idc_tb_request_item WHERE req_code = v_code;
	WHILE v_wh_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_request_item (req_code, it_code, rqit_type, rqit_qty, rqit_remark)
		VALUES (v_code, v_wh_it_code[v_i], 0, v_wh_it_qty[v_i], v_wh_it_remark[v_i]);
		v_i := v_i + 1;
	END LOOP;

	-- Processing update idc_tb_booking, idc_tb_outgoing and status
	UPDATE idc_tb_booking SET
		book_date			= v_issued_date,
		book_received_by	= v_issued_by,
		book_is_revised		= true,
		book_is_delivered	= false
	WHERE
		book_idx = v_book_idx;
	UPDATE idc_tb_outgoing_v2 SET out_issued_date = v_issued_date, out_is_revised = true WHERE out_doc_ref = trim(v_code);


	-- Processing checking incoming/ outgoing additional item
	WHILE v_rcp_it_code[v_j] IS NOT NULL LOOP
				INSERT INTO idc_tb_booking_revised (book_idx, it_code, boit_qty)
				VALUES (v_book_idx, v_rcp_it_code[v_j], v_rcp_it_qty[v_j]);
		v_j := v_j + 1;
	END LOOP;

	-- Update idc_tb_booking_item
	DELETE FROM idc_tb_booking_item WHERE book_idx = v_book_idx;
	WHILE v_wh_it_code[v_k] IS NOT NULL LOOP
		INSERT INTO idc_tb_booking_item (book_idx, it_code, boit_it_code_for, boit_type, boit_qty, boit_remark)
		VALUES (v_book_idx, v_wh_it_code[v_k], v_wh_it_code[v_k], 0, v_wh_it_qty[v_k], v_wh_it_remark[v_k]);
		v_k := v_k + 1;
	END LOOP;

	RETURN v_code;
END;
$$
;

alter function idc_reviserequeststocktodemorevised(varchar, integer, varchar, date, varchar, varchar, integer, character varying[], numeric[], character varying[], character varying[], numeric[]) owner to dskim
;

create function idc_reviseservicereg(v_code character varying, v_reg_date date, v_cus_to character varying, v_cus_name character varying, v_cus_address character varying, v_lastupdated_by_account character varying, v_signature_by character varying, v_remark character varying, v_is_update_item integer, v_it_code character varying[], v_it_model_no character varying[], v_it_sn character varying[], v_it_is_guarantee integer[], v_it_guarantee_period date[], v_it_cus_complain character varying[], v_it_tech_analyze character varying[]) returns character varying
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
BEGIN

	UPDATE idc_tb_service_reg SET
		sg_receive_date				= v_reg_date,
		sg_cus_to					= v_cus_to,
		sg_cus_to_name				= v_cus_name,
		sg_cus_to_address			= v_cus_address,
		sg_lastupdated_by_account	= v_lastupdated_by_account,
		sg_lastupdated_timestamp	= CURRENT_TIMESTAMP,
		sg_revesion_time			= sg_revesion_time+1,
		sg_signature_by				= v_signature_by,
		sg_remark					= v_remark
	WHERE sg_code = v_code;

	IF v_is_update_item = 1 THEN
		DELETE FROM idc_tb_service_reg_item WHERE sg_code = v_code;
		WHILE v_it_code[v_i] IS NOT NULL LOOP
			INSERT INTO idc_tb_service_reg_item (
				sg_code, it_code, sgit_model_no, sgit_is_guarantee, sgit_guarantee, sgit_serial_number,
				sgit_incoming_date, sgit_cus_complain, sgit_tech_analyze
			) VALUES (
				v_code, v_it_code[v_i], v_it_model_no[v_i], v_it_is_guarantee[v_i], v_it_guarantee_period[v_i], v_it_sn[v_i],
				v_reg_date, v_it_cus_complain[v_i], v_it_tech_analyze[v_i]
			);
			v_i := v_i + 1;
		END LOOP;
	END IF;

	RETURN v_code;
END;
$$
;

alter function idc_reviseservicereg(varchar, date, varchar, varchar, varchar, varchar, varchar, varchar, integer, character varying[], character varying[], character varying[], integer[], date[], character varying[], character varying[]) owner to dskim
;

create function idc_setallstock(v_inc_idx integer, v_type integer, v_cfm_by_account character varying, v_doc_type character varying, v_doc_ref character varying, v_doc_date date, v_type_activity character varying, v_it_code character varying[], v_it_ed character varying[], v_it_type integer[], v_it_stock_qty numeric[], v_it_demo_qty numeric[], v_it_reject_qty numeric[], v_ed_stk_it_code character varying[], v_ed_stk_it_date character varying[], v_ed_stk_it_location integer[], v_ed_stk_it_qty numeric[], v_ed_demo_it_code character varying[], v_ed_demo_it_date character varying[], v_ed_demo_it_location integer[], v_ed_demo_it_qty numeric[], v_reject_it_code character varying[], v_reject_it_sn character varying[], v_reject_it_warranty character varying[], v_reject_it_desc character varying[]) returns character varying
	language plpgsql
as $$
DECLARE
	v_variable varchar;
	v_i integer := 1;
	v_cur_rjt_idx integer;
	v_warranty date;
	v_incoming_demo_qty numeric;
	v_idc_qty numeric;
	v_dnr_qty numeric;
	recI record;
	recII record;
	recIII record;
	v_log_code varchar;
BEGIN

	/*-- stock --------------------------------------------------------------------------------------------------- */
	FOR recI IN SELECT it_code, init_type, init_stock_qty FROM idc_tb_incoming_item JOIN idc_tb_item USING(it_code)
	WHERE inc_idx = v_inc_idx ORDER BY it_code
	LOOP
		IF recI.init_stock_qty > 0 THEN
			INSERT INTO idc_tb_incoming_stock_v2 (inc_idx, it_code, inst_wh_location, inst_type, inst_qty, inst_document_date, inst_confirm_date)
			VALUES (v_inc_idx, recI.it_code, 1, recI.init_type, recI.init_stock_qty, v_doc_date, CURRENT_DATE);

			SELECT INTO v_log_code idc_insertStockLog(
				recI.it_code, 1, recI.init_type, v_type_activity,
				null, v_doc_ref, v_doc_date, v_cfm_by_account, true, recI.init_stock_qty, false
			);
		END IF;
	END LOOP;

	/* -- demo ---------------------------------------------------------------------------------------------------- */
	FOR recIII IN SELECT SUM(init_demo_qty) AS init_demo_qty FROM idc_tb_incoming_item WHERE inc_idx = v_inc_idx LOOP
		IF recIII.init_demo_qty IS NOT NULL THEN
			IF recIII.init_demo_qty > 0 THEN
				INSERT INTO idc_tb_incoming_marketing (inc_idx, inm_doc_no, inm_doc_date, inm_issued_by, inm_cfm_wh_delivery_by_account)
				VALUES(v_inc_idx, v_doc_ref, v_doc_date, v_cfm_by_account, v_cfm_by_account);
			END IF;
		END IF;
	END LOOP;

	/*-- reject -------------------------------------------------------------------------------------------------- */
	WHILE v_reject_it_code[v_i] IS NOT NULL AND v_reject_it_code[v_i] != '' LOOP
		IF v_i = 1 THEN
			INSERT INTO idc_tb_reject(rjt_doc_idx, rjt_doc_type)
			VALUES(v_inc_idx, 1);
		END IF;

		v_cur_rjt_idx := currval('idc_tb_reject_rjt_idx_seq');

		v_warranty = v_reject_it_warranty[v_i];
		INSERT INTO idc_tb_reject_item (rjt_idx, it_code, rjit_serial_number, rjit_warranty, rjit_desc, rjit_wh_location, rjit_type)
		VALUES(v_cur_rjt_idx, v_reject_it_code[v_i], v_reject_it_sn[v_i], v_warranty, v_reject_it_desc[v_i],1,2);

		UPDATE idc_tb_stock_v2 SET stk_qty = stk_qty+1 WHERE it_code=v_reject_it_code[v_i] AND stk_wh_location=1 AND stk_type=2;

		v_i := v_i + 1;
	END LOOP;

	RETURN v_variable;
END;
$$
;

alter function idc_setallstock(integer, integer, varchar, varchar, varchar, date, varchar, character varying[], character varying[], integer[], numeric[], numeric[], numeric[], character varying[], character varying[], integer[], numeric[], character varying[], character varying[], integer[], numeric[], character varying[], character varying[], character varying[], character varying[]) owner to dskim
;

create function idc_setupinitialapotikstock(v_code character varying, v_dept character varying, v_it_code character varying[], v_it_qty integer[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
BEGIN

	WHILE v_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_initial_stock(cus_code, it_code, ijk_dept, ijk_qty)
		VALUES(v_code, v_it_code[v_i], v_dept, v_it_qty[v_i]);
		v_i := v_i + 1;
	END LOOP;

END;
$$
;

alter function idc_setupinitialapotikstock(varchar, varchar, character varying[], integer[]) owner to dskim
;

create function idc_setupinitialindocorestock(v_type integer, v_it_code character varying[], v_it_qty integer[], v_ed_it_code character varying[], v_ed_qty integer[], v_ed_date character varying[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_j integer := 1;
	v_exp_date date;
BEGIN

	WHILE v_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_initial_indocore_stock(it_code, ist_type, ist_qty)
		VALUES(v_it_code[v_i], v_type, v_it_qty[v_i]);
		v_i := v_i + 1;
	END LOOP;

	--Insert into tb_expired_initial
	WHILE v_ed_it_code[v_j] != '' LOOP
		v_exp_date := v_ed_date[v_j];
		INSERT INTO idc_tb_expired_initial(it_code, eni_type, eni_expired_date, eni_qty)
		VALUES (v_ed_it_code[v_j], v_type, v_exp_date, v_ed_qty[v_j]);
		v_j := v_j + 1;
	END LOOP;

END;
$$
;

alter function idc_setupinitialindocorestock(integer, character varying[], integer[], character varying[], integer[], character varying[]) owner to dskim
;

create function idc_setupinitialstock(v_type integer, v_location integer, v_insert_by_account character varying, v_it_code character varying[], v_it_qty numeric[], v_ed_it_code character varying[], v_ed_qty numeric[], v_ed_date character varying[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_j integer := 1;
	v_exp_date date;
	v_logs_code varchar;
BEGIN

	WHILE v_it_code[v_i] IS NOT NULL LOOP
		/* 1. Insert into idc_tb_initial_stock_v2 */
		INSERT INTO idc_tb_initial_stock_v2(it_code, init_wh_location, init_type, init_qty)
		VALUES(v_it_code[v_i], v_location, v_type, v_it_qty[v_i]);

		/* 2. Insert into idc_tb_log_detail */
		SELECT INTO v_logs_code idc_getStockLogIdx(v_it_code[v_i], v_location, v_type, CURRENT_DATE);
		INSERT INTO idc_tb_log_detail(
			log_code, it_code, log_wh_location, log_type,
			log_document_type, log_document_idx, log_document_no, log_document_date,
			log_cfm_timestamp, log_cfm_by_account, log_qty
		) VALUES (
			v_logs_code, v_it_code[v_i], v_location, v_type,
			'Initial Stock', null, 'Initial Stock', CURRENT_DATE,
			CURRENT_TIMESTAMP, v_insert_by_account, v_it_qty[v_i]
		);
		v_i := v_i + 1;
	END LOOP;

	/* 3. Insert into idc_tb_initial_stock_ed */
	WHILE v_ed_it_code[v_j] != '' LOOP
		v_exp_date := v_ed_date[v_j];
		INSERT INTO idc_tb_initial_stock_ed(it_code, ined_wh_location, ined_expired_date, ined_qty)
		VALUES (v_ed_it_code[v_j], v_location, v_exp_date, v_ed_qty[v_j]);
		v_j := v_j + 1;
	END LOOP;

END;
$$
;

alter function idc_setupinitialstock(integer, integer, varchar, character varying[], numeric[], character varying[], numeric[], character varying[]) owner to dskim
;

create function idc_setuprejectstock(v_log_by_account character varying, v_it_code character varying[], v_it_serial_no character varying[], v_it_expired_warranty character varying[], v_it_desc character varying[], v_it_type integer[], v_it_location integer[]) returns integer
	language plpgsql
as $$
DECLARE
	v_cur_rjt_idx integer;
	v_cur_rjit_idx integer;
	v_i integer := 1;
	v_warranty date;
	v_log_code varchar;
BEGIN

	WHILE v_it_code[v_i] IS NOT NULL LOOP
		IF v_i = 1 THEN
			INSERT INTO idc_tb_reject(rjt_doc_type) VALUES(2);
		END IF;

		v_cur_rjt_idx := currval('idc_tb_reject_rjt_idx_seq');
		v_warranty = v_it_expired_warranty[v_i]::date;
		INSERT INTO idc_tb_reject_item (rjt_idx, it_code, rjit_serial_number, rjit_warranty, rjit_desc, rjit_type, rjit_wh_location)
		VALUES(v_cur_rjt_idx, v_it_code[v_i], v_it_serial_no[v_i], v_warranty, v_it_desc[v_i],v_it_type[v_i],v_it_location[v_i]);
		v_cur_rjit_idx := currval('idc_tb_reject_item_rjit_idx_seq');

		SELECT INTO v_log_code idc_insertStockLog(
			v_it_code[v_i], v_it_location[v_i], v_it_type[v_i], 'Reject Stock',
			v_cur_rjit_idx, null, null,v_log_by_account, false, 1
		);

		v_i := v_i + 1;
	END LOOP;

	RETURN v_cur_rjt_idx;
END;
$$
;

alter function idc_setuprejectstock(varchar, character varying[], character varying[], character varying[], character varying[], integer[], integer[]) owner to dskim
;

create function idc_statuspl(v_type integer, v_code integer) returns character varying
	language plpgsql
as $$
DECLARE
	v_status boolean := false;
	v_total_pl numeric := 0;
	v_total_in_pl numeric;	v_total_in_pl1 numeric;	v_total_in_pl2 numeric;
	v_remain numeric := 0;
BEGIN

	IF v_type = 1 THEN
		SELECT INTO v_total_pl SUM(plit_qty) FROM idc_tb_pl_item WHERE pl_idx = v_code;
		SELECT INTO v_total_in_pl1 SUM(init_qty) FROM idc_tb_in_pl_item WHERE pl_idx = v_code;
		SELECT INTO v_total_in_pl2 SUM(init_qty) FROM idc_tb_in_pl_item_v2 WHERE pl_idx = v_code;
	ELSIF v_type = 2 THEN
		SELECT INTO v_total_pl SUM(clit_qty) FROM idc_tb_claim_item WHERE cl_idx = v_code;
		SELECT INTO v_total_in_pl1 SUM(init_qty) FROM idc_tb_in_claim_item WHERE cl_idx = v_code;
		SELECT INTO v_total_in_pl2 SUM(init_qty) FROM idc_tb_in_claim_item_v2 WHERE cl_idx = v_code;
	END IF;

	IF v_total_in_pl1 is null THEN v_total_in_pl1 := 0; END IF;
	IF v_total_in_pl2 is null THEN v_total_in_pl2 := 0; END IF;

	v_total_in_pl := v_total_in_pl1 + v_total_in_pl2;
	v_remain := v_total_pl - v_total_in_pl;

	IF v_remain = 0 THEN
		v_status := true;
	END IF;

	RETURN v_status;
END;
$$
;

alter function idc_statuspl(integer, integer) owner to dskim
;

create function idc_statuspo(v_code character varying) returns character varying
	language plpgsql
as $$
DECLARE
	v_status boolean := false;
	v_total_po integer := 0;
	v_total_pl integer := 0;
	v_remain integer := 0;
BEGIN

	SELECT INTO v_total_po SUM(rcp_po_qty) FROM idc_tb_po_recap WHERE rcp_po_code = v_code;
	SELECT INTO v_total_pl SUM(rcp_pl_qty) FROM idc_tb_po_recap WHERE rcp_po_code = v_code;

	v_remain := v_total_po - v_total_pl;

	IF v_remain = 0 THEN
		v_status := true;
	END IF;

	RETURN v_status;
END;
$$
;

alter function idc_statuspo(varchar) owner to dskim
;

create function idc_statuspolocal(v_code character varying) returns character varying
	language plpgsql
as $$
DECLARE
	v_status boolean := false;
	v_total_po numeric := 0;
	v_total_pl numeric; v_total_pl1 numeric; v_total_pl2 numeric;
	v_remain numeric := 0;
BEGIN

	SELECT INTO v_total_po SUM(poit_qty) FROM idc_tb_po_local join idc_tb_po_local_item using(po_code) WHERE po_code = v_code;
	SELECT INTO v_total_pl1 SUM(init_qty) FROM idc_tb_in_local join idc_tb_in_local_item using(inlc_idx) WHERE po_code = v_code;
	SELECT INTO v_total_pl2 SUM(init_qty) FROM idc_tb_in_local_v2 join idc_tb_in_local_item_v2 using(inlc_idx) WHERE po_code = v_code;

	if v_total_pl1 is null then v_total_pl1 := 0;end if;
	if v_total_pl2 is null then v_total_pl2 := 0;end if;

	v_total_pl := v_total_pl1 + v_total_pl2;
	v_remain := v_total_po - v_total_pl;

	IF v_remain = 0 THEN
		v_status := true;
	END IF;

	RETURN v_status;
END;
$$
;

alter function idc_statuspolocal(varchar) owner to dskim
;

create function idc_statusqtylevel(v_it_code character varying) returns boolean
	language plpgsql
as $$
DECLARE
	v_value boolean;
	v_stock numeric;
	v_critical_stock numeric;
	v_diff numeric;
BEGIN

	select into v_stock sum(stk_qty) from idc_tb_stock_v2 where it_code = v_it_code;
	select into v_critical_stock it_critical_stock from idc_tb_item where it_code = v_it_code;
	v_diff = v_stock - v_critical_stock;

	if v_diff > 0 then v_value = true;
	else v_value = false; end if;

	RETURN v_value;
END;
$$
;

alter function idc_statusqtylevel(varchar) owner to dskim
;

create function idc_summary_borrow_ed_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint;
	v_type smallint;
	v_date date;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.bed_from_wh;
		v_type		  = OLD.bed_from_type;
		v_date		  = OLD.bed_expired_date;
		v_qty		  = -1 * OLD.bed_qty;

	ELSIF (TG_OP = 'UPDATE') THEN

		IF(OLD.it_code != NEW.it_code OR OLD.bed_from_type != NEW.bed_from_type OR OLD.bed_from_wh != NEW.bed_from_wh) THEN
			RAISE EXCEPTION 'Update of ITEM CODE or ITEM TYPE is not allowed';
		END IF;

		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.bed_from_wh;
		v_type		  = OLD.bed_from_type;
		v_date		  = OLD.bed_expired_date;
		v_qty		  = NEW.bed_qty - OLD.bed_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	  = NEW.it_code;
		v_wh_location = NEW.bed_from_wh;
		v_type		  = NEW.bed_from_type;
		v_date		  = NEW.bed_expired_date;
		v_qty		  = NEW.bed_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_expired_stock SET
			e_qty			= e_qty - v_qty
		WHERE it_code = v_it_code AND e_type = v_type AND e_wh_location = v_wh_location AND e_expired_date = v_date;

		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_expired_stock (it_code, e_type, e_wh_location, e_expired_date, e_qty)
			VALUES(v_it_code, v_type, v_wh_location, v_date, -v_qty);

			EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
		END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_summary_borrow_ed_qty() owner to dskim
;

create function idc_summary_borrow_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint;
	v_type smallint;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.bor_from_wh;
		v_type		  = OLD.bor_from_type;
		v_qty		  = -1 * OLD.bor_qty;

	ELSIF (TG_OP = 'UPDATE') THEN

		IF(OLD.it_code != NEW.it_code OR OLD.bor_from_type != NEW.bor_from_type OR OLD.bor_from_wh != NEW.bor_from_wh) THEN
			RAISE EXCEPTION 'Update of ITEM CODE or ITEM TYPE is not allowed';
		END IF;

		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.bor_from_wh;
		v_type		  = OLD.bor_from_type;
		v_qty		  = NEW.bor_qty - OLD.bor_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	  = NEW.it_code;
		v_wh_location = NEW.bor_from_wh;
		v_type		  = NEW.bor_from_type;
		v_qty		  = NEW.bor_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_stock SET
			stk_qty			= stk_qty - v_qty,
			stk_updated		= CURRENT_TIMESTAMP
		WHERE it_code = v_it_code AND stk_type = v_type AND stk_wh_location = v_wh_location;

		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_stock (it_code, stk_type, stk_wh_location, stk_updated, stk_qty)
			VALUES(v_it_code, v_type, v_wh_location, CURRENT_TIMESTAMP, -v_qty);

			EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
		END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_summary_borrow_qty() owner to dskim
;

create function idc_summary_claim_expired_item() returns trigger
	language plpgsql
as $$
DECLARE
	v_idx integer;
	v_it_code varchar;
	v_wh_location smallint;
	v_type integer;
	v_date date;
	v_qty numeric := 0;
BEGIN
	IF(TG_OP = 'DELETE') THEN
		v_idx		= OLD.ecl_idx;
		v_it_code	= OLD.it_code;
		v_wh_location = OLD.ecl_wh_location;
		v_type		= OLD.ecl_type;
		v_date		= OLD.ecl_expired_date;
		v_qty		= -1 * OLD.ecl_qty;

	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.ecl_qty != NEW.ecl_qty) THEN
			RAISE EXCEPTION 'Update of item code or type is not allowed';
		END IF;
		v_idx		= OLD.ecl_idx;
		v_it_code	= OLD.it_code;
		v_wh_location = OLD.ecl_wh_location;
		v_type		= OLD.ecl_type;
		v_date		= OLD.ecl_expired_date;
		v_qty		= NEW.ecl_qty - OLD.ecl_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_idx		= NEW.ecl_idx;
		v_it_code	= NEW.it_code;
		v_wh_location = NEW.ecl_wh_location;
		v_type		= NEW.ecl_type;
		v_date		= NEW.ecl_expired_date;
		v_qty		= NEW.ecl_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<item>>
	LOOP
	UPDATE idc_tb_expired_stock SET
		e_qty = e_qty + v_qty
	WHERE it_code = v_it_code AND e_type= v_type AND e_expired_date = v_date AND e_wh_location = v_wh_location;
	EXIT item WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_expired_stock (it_code, e_wh_location, e_type, e_expired_date, e_qty)
		VALUES(v_it_code, v_wh_location, v_type, v_date, v_qty);
	EXIT item;

	EXCEPTION WHEN UNIQUE_VIOLATION THEN
		/* do nothing */
	END;
	END LOOP item;

	RETURN NULL;
END;
$$
;

alter function idc_summary_claim_expired_item() owner to dskim
;

create function idc_summary_coming_ed_stock_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint;
	v_type integer;
	v_date date;
	v_qty numeric := 0;
BEGIN
	IF(TG_OP = 'DELETE') THEN
		v_it_code	= OLD.it_code;
		v_wh_location = OLD.ised_wh_location;
		v_type		= OLD.ised_type;
		v_date		= OLD.ised_expired_date;
		v_qty		= -1 * OLD.ised_qty;

	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.ised_qty != NEW.ised_qty) THEN
			RAISE EXCEPTION 'Update of item code or type is not allowed';
		END IF;

		v_it_code	= OLD.it_code;
		v_wh_location = OLD.ised_wh_location;
		v_type		= OLD.ised_type;
		v_date		= OLD.ised_expired_date;
		v_qty		= NEW.ised_qty - OLD.ised_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	= NEW.it_code;
		v_wh_location = NEW.ised_wh_location;
		v_type		= NEW.ised_type;
		v_date		= NEW.ised_expired_date;
		v_qty		= NEW.ised_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<item>>
	LOOP
		UPDATE idc_tb_expired_stock SET
			e_qty = e_qty + v_qty
		WHERE it_code = v_it_code AND e_type= v_type AND e_expired_date = v_date AND e_wh_location = v_wh_location;
		EXIT item WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_expired_stock (it_code, e_wh_location, e_type, e_expired_date, e_qty)
			VALUES(v_it_code, v_wh_location, v_type, v_date, v_qty);
			EXIT item;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
		END;
	END LOOP item;

	RETURN NULL;
END;
$$
;

alter function idc_summary_coming_ed_stock_qty() owner to dskim
;

create function idc_summary_coming_pl_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_pl_idx integer;
	v_qty numeric := 0;
	v_convert_qty integer;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	= OLD.it_code;
		v_pl_idx	= OLD.pl_idx;
		v_qty		= -1 * OLD.init_qty;

	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.pl_idx != NEW.pl_idx) THEN
			RAISE EXCEPTION 'Update of ITEM CODE or PL IDX is not allowed';
		END IF;
		v_it_code	= OLD.it_code;
		v_pl_idx	= OLD.pl_idx;
		v_qty		= NEW.init_qty - OLD.init_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	= NEW.it_code;
		v_pl_idx	= NEW.pl_idx;
		v_qty		= NEW.init_qty;

	END IF;

	/* Insert or update the summary row wirh the new values */
	<<coming>>	LOOP
	UPDATE idc_tb_pending_pl SET
		pepl_qty		= pepl_qty - v_qty::integer
	WHERE it_code = v_it_code AND pl_idx = v_pl_idx;
	EXIT coming WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_pending_pl (it_code, pl_idx, pepl_qty)
		VALUES(v_it_code, v_pl_idx, -v_qty::integer);
	EXIT coming;

	EXCEPTION
		WHEN UNIQUE_VIOLATION THEN
			/* do nothing */
		END;
	END LOOP coming;
	RETURN NULL;

END;
$$
;

alter function idc_summary_coming_pl_qty() owner to dskim
;

create function idc_summary_coming_stock_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint := 1;
	v_type smallint;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.inst_wh_location;
		v_type		  = OLD.inst_type;
		v_qty		  = -1 * OLD.inst_qty;

	ELSIF (TG_OP = 'UPDATE') THEN

		IF(OLD.it_code != NEW.it_code OR OLD.inst_type != NEW.inst_type OR OLD.inst_wh_location != NEW.inst_wh_location) THEN
			RAISE EXCEPTION 'Update of ITEM CODE or ITEM TYPE is not allowed';
		END IF;

		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.inst_wh_location;
		v_type		  = OLD.inst_type;
		v_qty		  = NEW.inst_qty - OLD.inst_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	  = NEW.it_code;
		v_wh_location = NEW.inst_wh_location;
		v_type		  = NEW.inst_type;
		v_qty		  = NEW.inst_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_stock SET
			stk_qty			= stk_qty + v_qty,
			stk_updated		= CURRENT_TIMESTAMP
		WHERE it_code = v_it_code AND stk_type = v_type AND stk_wh_location = v_wh_location;

		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_stock (it_code, stk_type, stk_wh_location, stk_updated, stk_qty)
			VALUES(v_it_code, v_type, v_wh_location, CURRENT_TIMESTAMP, v_qty);

			EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
		END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_summary_coming_stock_qty() owner to dskim
;

create function idc_summary_delivery_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_cus_code varchar;
	v_it_code varchar;
	v_dept varchar;
	v_delta_jk_qty integer := 0;
	v_delta_jo_qty integer := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_cus_code		= OLD.cus_code;
		v_it_code		= OLD.it_code;
		v_dept			= OLD.deit_dept;
		v_delta_jk_qty	= -1 * OLD.deit_jk_qty;
		v_delta_jo_qty	= -1 * OLD.deit_jo_qty;

	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.cus_code != NEW.cus_code OR OLD.deit_dept != NEW.deit_dept) THEN
			RAISE EXCEPTION 'Update of customer code or it_code is not allowed';
		END IF;
		v_cus_code		= OLD.cus_code;
		v_it_code		= OLD.it_code;
		v_dept			= OLD.deit_dept;
		v_delta_jk_qty	= NEW.deit_jk_qty - OLD.deit_jk_qty;
		v_delta_jo_qty	= NEW.deit_jo_qty - OLD.deit_jo_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_cus_code		= NEW.cus_code;
		v_it_code		= NEW.it_code;
		v_dept			= NEW.deit_dept;
		v_delta_jk_qty	= NEW.deit_jk_qty;
		v_delta_jo_qty	= NEW.deit_jo_qty;
	END IF;

	-- Insert or update the summary row wirh the new values
	<<dahlia>>
	LOOP
	UPDATE idc_tb_apotik_inv SET
		inv_jk		= inv_jk + v_delta_jk_qty,
		inv_jo		= inv_jo + v_delta_jo_qty,
		inv_updated	= CURRENT_DATE
	WHERE cus_code = v_cus_code AND it_code = v_it_code AND inv_dept = v_dept;
	EXIT dahlia WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_apotik_inv (cus_code, it_code, inv_dept, inv_updated, inv_ok, inv_oo)
		VALUES(v_cus_code, v_it_code, v_dept, CURRENT_DATE, v_delta_jk_qty, v_delta_jo_qty);
	EXIT dahlia;

	EXCEPTION
		WHEN UNIQUE_VIOLATION THEN
			-- do nothing
		END;
	END LOOP dahlia;

	RETURN NULL;
END;
$$
;

alter function idc_summary_delivery_qty() owner to dskim
;

create function idc_summary_entering_ed_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint;
	v_type smallint;
	v_date date;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.eed_wh_location;
		v_type		  = OLD.eed_type;
		v_date		  = OLD.eed_expired_date;
		v_qty		  = -1 * OLD.eed_qty;

	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.eed_wh_location != NEW.eed_wh_location OR OLD.eed_type != NEW.eed_type) THEN
			RAISE EXCEPTION 'Update of ITEM CODE or ITEM TYPE is not allowed';
		END IF;

		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.eed_wh_location;
		v_type		  = OLD.eed_type;
		v_date		  = OLD.eed_expired_date;
		v_qty		  = NEW.eed_qty - OLD.eed_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	  = NEW.it_code;
		v_wh_location = NEW.eed_wh_location;
		v_type		  = NEW.eed_type;
		v_date		  = NEW.eed_expired_date;
		v_qty		  = NEW.eed_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_expired_stock SET
			e_qty			= e_qty + v_qty
		WHERE it_code = v_it_code AND e_type = v_type AND e_wh_location = v_wh_location AND e_expired_date = v_date;

		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_expired_stock (it_code, e_type, e_wh_location, e_expired_date, e_qty)
			VALUES(v_it_code, v_type, v_wh_location, v_date, v_qty);

			EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
		END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_summary_entering_ed_qty() owner to dskim
;

create function idc_summary_entering_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint;
	v_type smallint;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.ent_wh_location;
		v_type		  = OLD.ent_type;
		v_qty		  = -1 * OLD.ent_qty;

	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.ent_wh_location != NEW.ent_wh_location OR OLD.ent_type != NEW.ent_type) THEN
			RAISE EXCEPTION 'Update of ITEM CODE or ITEM TYPE is not allowed';
		END IF;

		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.ent_wh_location;
		v_type		  = OLD.ent_type;
		v_qty		  = NEW.ent_qty - OLD.ent_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	  = NEW.it_code;
		v_wh_location = NEW.ent_wh_location;
		v_type		  = NEW.ent_type;
		v_qty		  = NEW.ent_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_stock SET
			stk_qty			= stk_qty + v_qty,
			stk_updated		= CURRENT_TIMESTAMP
		WHERE it_code = v_it_code AND stk_type = v_type AND stk_wh_location = v_wh_location;

		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_stock (it_code, stk_type, stk_wh_location, stk_updated, stk_qty)
			VALUES(v_it_code, v_type, v_wh_location, CURRENT_TIMESTAMP, v_qty);

			EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
		END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_summary_entering_qty() owner to dskim
;

create function idc_summary_incoming_demo_ed() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_date date;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	= OLD.it_code;
		v_date		= OLD.idded_expired_date;
		v_qty		= -1 * OLD.idded_qty;

	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code and OLD.d_expired_date != NEW.d_expired_date) THEN
			RAISE EXCEPTION 'Update of ITEM CODE or EXPIRED DATE is not allowed';
		END IF;

		v_it_code	= OLD.it_code;
		v_date		= OLD.idded_expired_date;
		v_qty		= NEW.idded_qty - OLD.idded_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	= NEW.it_code;
		v_date		= NEW.idded_expired_date;
		v_qty		= NEW.idded_qty;
	END IF;

	/* Insert or update the summary row with the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_expired_demo SET
			d_qty = d_qty + v_qty
		WHERE it_code = v_it_code AND d_expired_date = v_date;
		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_expired_demo (it_code, d_expired_date, d_qty)
			VALUES(v_it_code, v_date, v_qty);
			EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
		END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_summary_incoming_demo_ed() owner to dskim
;

create function idc_summary_incoming_demo_stock() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	= OLD.it_code;
		v_qty		= -1 * OLD.indst_qty;

	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code) THEN
			RAISE EXCEPTION 'Update of ITEM CODE is not allowed';
		END IF;

		v_it_code	= OLD.it_code;
		v_qty		= NEW.indst_qty - OLD.indst_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	= NEW.it_code;
		v_qty		= NEW.indst_qty;
	END IF;

	/*  Insert or update the summary row with the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_demo SET
			demo_qty			= demo_qty + v_qty,
			demo_updated		= CURRENT_TIMESTAMP
		WHERE it_code = v_it_code;
		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_demo (it_code, demo_updated, demo_qty)
			VALUES(v_it_code, CURRENT_TIMESTAMP, v_qty);
			EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
		END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_summary_incoming_demo_stock() owner to dskim
;

create function idc_summary_incoming_pl_claim() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint;
	v_type smallint;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.init_wh_location;
		v_type		  = OLD.init_type;
		v_qty		  = -1 * OLD.init_qty;

	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.init_type != NEW.init_type) THEN
			RAISE EXCEPTION 'Update of ITEM CODE or ITEM TYPE is not allowed';
		END IF;		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.init_wh_location;
		v_type		  = OLD.init_type;
		v_qty		  = NEW.init_qty - OLD.init_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	  = NEW.it_code;
		v_wh_location = NEW.init_wh_location;
		v_type		  = NEW.init_type;
		v_qty		  = NEW.init_qty;
	END IF;

	/* Insert or update the summary row with the new values */
	<<stock>>
	LOOP
	UPDATE idc_tb_stock SET
		stk_qty			= stk_qty + v_qty,
		stk_updated		= CURRENT_TIMESTAMP
	WHERE it_code = v_it_code AND stk_type = v_type AND stk_wh_location = v_wh_location;
	EXIT stock WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_stock (it_code, stk_type, stk_wh_location, stk_updated, stk_qty)
		VALUES(v_it_code, v_type, v_wh_location, CURRENT_TIMESTAMP, v_qty);
	EXIT stock;

	EXCEPTION
	WHEN UNIQUE_VIOLATION THEN
		/* do nothing */
	END;
	END LOOP stock;

	RETURN NULL;

END;
$$
;

alter function idc_summary_incoming_pl_claim() owner to dskim
;

create function idc_summary_incoming_pl_local() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint;
	v_type smallint;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.init_wh_location;
		v_type		  = OLD.init_type;
		v_qty		  = -1 * OLD.init_qty;

	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.init_type != NEW.init_type OR OLD.init_wh_location != NEW.init_wh_location) THEN
			RAISE EXCEPTION 'Update of ITEM CODE or ITEM TYPE is not allowed';
		END IF;
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.init_wh_location;
		v_type		  = OLD.init_type;
		v_qty		  = NEW.init_qty - OLD.init_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	  = NEW.it_code;
		v_wh_location = NEW.init_wh_location;
		v_type		  = NEW.init_type;
		v_qty		  = NEW.init_qty;
	END IF;

	/* Insert or update the summary row with the new values */
	<<stock>>
	LOOP
	UPDATE idc_tb_stock SET
		stk_qty			= stk_qty + v_qty,
		stk_updated		= CURRENT_TIMESTAMP
	WHERE it_code = v_it_code AND stk_type = v_type AND stk_wh_location = v_wh_location;
	EXIT stock WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_stock (it_code, stk_type, stk_wh_location, stk_updated, stk_qty)
		VALUES(v_it_code, v_type, v_wh_location, CURRENT_TIMESTAMP, v_qty);
	EXIT stock;

	EXCEPTION
	WHEN UNIQUE_VIOLATION THEN
		/* do nothing */
	END;
	END LOOP stock;
	RETURN NULL;
END;
$$
;

alter function idc_summary_incoming_pl_local() owner to dskim
;

create function idc_summary_init_expired_item() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint;
	v_type integer;
	v_date date;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	= OLD.it_code;
		v_wh_location = OLD.eni_wh_location;
		v_type		= OLD.eni_type;
		v_date		= OLD.eni_expired_date;
		v_qty		= -1 * OLD.eni_qty;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.eni_qty != NEW.eni_qty) THEN
			RAISE EXCEPTION 'Update of item code or type is not allowed';
		END IF;
		v_it_code	= OLD.it_code;
		v_wh_location = OLD.eni_wh_location;
		v_type		= OLD.eni_type;
		v_date		= OLD.eni_expired_date;
		v_qty		= NEW.eni_qty - OLD.eni_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	= NEW.it_code;
		v_wh_location = NEW.eni_wh_location;
		v_type		= NEW.eni_type;
		v_date		= NEW.eni_expired_date;
		v_qty		= NEW.eni_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<item>>
	LOOP
		UPDATE idc_tb_expired_stock SET
			e_qty = e_qty + v_qty
		WHERE it_code = v_it_code AND e_type= v_type AND e_expired_date = v_date AND e_wh_location = v_wh_location;
	EXIT item WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_expired_stock (it_code, e_wh_location, e_type, e_expired_date, e_qty)
		VALUES(v_it_code, v_wh_location, v_type, v_date, v_qty);
	EXIT item;

	EXCEPTION WHEN UNIQUE_VIOLATION THEN
		/* do nothing */
	END;
	END LOOP item;

	RETURN NULL;
END;
$$
;

alter function idc_summary_init_expired_item() owner to dskim
;

create function idc_summary_init_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint;
	v_type smallint;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.init_wh_location;
		v_type		  = OLD.init_type;
		v_qty		  = -1 * OLD.init_qty;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.init_type != NEW.init_type) THEN
			RAISE EXCEPTION 'Update of ITEM CODE or ITEM TYPE is not allowed';
		END IF;
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.init_wh_location;
		v_type		  = OLD.init_type;
		v_qty		  = NEW.init_qty - OLD.init_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	  = NEW.it_code;
		v_wh_location = NEW.init_wh_location;
		v_type		  = NEW.init_type;
		v_qty		  = NEW.init_qty;
	END IF;

	/* Insert or update the summary row with the new values */
	<<stock>>	LOOP
	UPDATE idc_tb_stock SET
		stk_qty			= stk_qty + v_qty,
		stk_updated		= CURRENT_TIMESTAMP
	WHERE it_code = v_it_code AND stk_type = v_type AND stk_wh_location = v_wh_location;
	EXIT stock WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_stock (it_code, stk_type, stk_wh_location, stk_updated, stk_qty)
		VALUES(v_it_code, v_type, v_wh_location, CURRENT_TIMESTAMP, v_qty);
	EXIT stock;

	EXCEPTION
		WHEN UNIQUE_VIOLATION THEN
			/* do nothing */
		END;
	END LOOP stock;
	RETURN NULL;

END;
$$
;

alter function idc_summary_init_qty() owner to dskim
;

create function idc_summary_initial_indocore_stock_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_wh_location integer;
	v_type integer;
	v_it_code varchar;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_wh_location = OLD.ist_wh_location;
		v_type		 = OLD.ist_type;
		v_it_code	= OLD.it_code;
		v_qty		= -1 * OLD.ist_qty;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.ist_type != NEW.ist_type) THEN
			RAISE EXCEPTION 'Update of item code or type is not allowed';
		END IF;
		v_wh_location = OLD.ist_wh_location;
		v_type		= OLD.ist_type;
		v_it_code	= OLD.it_code;
		v_qty		= NEW.ist_qty - OLD.ist_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_wh_location = NEW.ist_wh_location;
		v_type		= NEW.ist_type;
		v_it_code	= NEW.it_code;
		v_qty		= NEW.ist_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
	UPDATE idc_tb_stock SET
		stk_qty		= stk_qty + v_qty,
		stk_updated = CURRENT_TIMESTAMP
	WHERE stk_type = v_type AND it_code = v_it_code AND stk_wh_location = v_wh_location;
	EXIT stock WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_stock (stk_type, stk_wh_location, it_code, stk_updated, stk_qty)
		VALUES(v_type, v_wh_location, v_it_code, CURRENT_TIMESTAMP, v_qty);
	EXIT stock;
	EXCEPTION			WHEN UNIQUE_VIOLATION THEN
		/* do nothing */
	END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_summary_initial_indocore_stock_qty() owner to dskim
;

create function idc_summary_initial_stock_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_cus_code varchar;
	v_it_code varchar;
	v_dept varchar;
	v_delta_qty integer := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_cus_code	= OLD.cus_code;
		v_it_code	= OLD.it_code;
		v_dept		= OLD.ijk_dept;
		v_delta_qty = -1 * OLD.ijk_qty;

	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.cus_code != NEW.cus_code OR OLD.ijk_dept != NEW.ijk_dept) THEN
			RAISE EXCEPTION 'Update of customer code or it_code is not allowed';
		END IF;
		v_cus_code 	= OLD.cus_code;
		v_it_code 	= OLD.it_code;
		v_dept		= OLD.ijk_dept;
		v_delta_qty = NEW.ijk_qty - OLD.ijk_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_cus_code 	= NEW.cus_code;
		v_it_code 	= NEW.it_code;
		v_dept		= NEW.ijk_dept;
		v_delta_qty = NEW.ijk_qty;
	END IF;

	-- Insert or update the summary row wirh the new values
	<<dahlia>>
	LOOP
	UPDATE idc_tb_apotik_inv SET
		inv_ok 		= inv_ok + v_delta_qty,
		inv_jk 		= inv_jk + v_delta_qty,
		inv_updated = CURRENT_DATE
	WHERE cus_code = v_cus_code AND it_code = v_it_code AND inv_dept = v_dept;
	EXIT dahlia WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_apotik_inv (cus_code, it_code, inv_dept, inv_updated, inv_ok, inv_jk)
		VALUES(v_cus_code, v_it_code, v_dept, CURRENT_DATE, v_delta_qty, v_delta_qty);
	EXIT dahlia;

	EXCEPTION
		WHEN UNIQUE_VIOLATION THEN
			-- do nothing
		END;
	END LOOP dahlia;

	RETURN NULL;
END;
$$
;

alter function idc_summary_initial_stock_qty() owner to dskim
;

create function idc_summary_local_expired_item() returns trigger
	language plpgsql
as $$
DECLARE
	v_idx integer;
	v_it_code varchar;
	v_wh_location smallint;
	v_type integer;
	v_date date;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_idx		= OLD.elc_idx;
		v_it_code	= OLD.it_code;
		v_wh_location = OLD.elc_wh_location;
		v_type		= OLD.elc_type;
		v_date		= OLD.elc_expired_date;
		v_qty		= -1 * OLD.elc_qty;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.elc_qty != NEW.elc_qty) THEN
			RAISE EXCEPTION 'Update of item code or type is not allowed';
		END IF;
		v_idx		= OLD.elc_idx;
		v_it_code	= OLD.it_code;
		v_wh_location = OLD.elc_wh_location;
		v_type		= OLD.elc_type;
		v_date		= OLD.elc_expired_date;
		v_qty		= NEW.elc_qty - OLD.elc_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_idx		= NEW.elc_idx;
		v_it_code	= NEW.it_code;
		v_wh_location = NEW.elc_wh_location;
		v_type		= NEW.elc_type;
		v_date		= NEW.elc_expired_date;
		v_qty		= NEW.elc_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<item>>
	LOOP
	UPDATE idc_tb_expired_stock SET
		e_qty = e_qty + v_qty
	WHERE it_code = v_it_code AND e_type= v_type AND e_expired_date = v_date AND e_wh_location = v_wh_location;
	EXIT item WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_expired_stock (it_code, e_wh_location, e_type, e_expired_date, e_qty)
		VALUES(v_it_code, v_wh_location, v_type, v_date, v_qty);
	EXIT item;

	EXCEPTION WHEN UNIQUE_VIOLATION THEN
		/* do nothing */
	END;
	END LOOP item;

	RETURN NULL;
END;
$$
;

alter function idc_summary_local_expired_item() owner to dskim
;

create function idc_summary_move_stock_ed_minus_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint;
	v_type smallint;
	v_date date;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.mved_from_wh;
		v_type		  = OLD.mved_from_type;
		v_date		  = OLD.mved_expired_date;
		v_qty		  = -1 * OLD.mved_qty;

	ELSIF (TG_OP = 'UPDATE') THEN

		IF(OLD.it_code != NEW.it_code OR OLD.mved_from_type != NEW.mved_from_type OR OLD.mved_from_wh != NEW.mved_from_wh) THEN
			RAISE EXCEPTION 'Update of ITEM CODE or ITEM TYPE is not allowed';
		END IF;

		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.mved_from_wh;
		v_type		  = OLD.mved_from_type;
		v_date		  = OLD.mved_expired_date;
		v_qty		  = NEW.mved_qty - OLD.mved_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	  = NEW.it_code;
		v_wh_location = NEW.mved_from_wh;
		v_type		  = NEW.mved_from_type;
		v_date		  = NEW.mved_expired_date;
		v_qty		  = NEW.mved_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_expired_stock SET
			e_qty = e_qty - v_qty
		WHERE it_code = v_it_code AND e_type = v_type AND e_wh_location = v_wh_location AND e_expired_date = v_date;

		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_expired_stock (it_code, e_type, e_wh_location, e_expired_date, e_qty)
			VALUES(v_it_code, v_type, v_wh_location, v_date, -v_qty);

			EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
		END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_summary_move_stock_ed_minus_qty() owner to dskim
;

create function idc_summary_move_stock_ed_plus_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint;
	v_type smallint;
	v_date date;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.mved_to_wh;
		v_type		  = OLD.mved_to_type;
		v_date		  = OLD.mved_expired_date;
		v_qty		  = -1 * OLD.mved_qty;

	ELSIF (TG_OP = 'UPDATE') THEN

		IF(OLD.it_code != NEW.it_code OR OLD.mved_to_type != NEW.mved_to_type OR OLD.mved_to_wh != NEW.mved_to_wh) THEN
			RAISE EXCEPTION 'Update of ITEM CODE or ITEM TYPE is not allowed';
		END IF;

		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.mved_to_wh;
		v_type		  = OLD.mved_to_type;
		v_date		  = OLD.mved_expired_date;
		v_qty		  = NEW.mved_qty - OLD.mved_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	  = NEW.it_code;
		v_wh_location = NEW.mved_to_wh;
		v_type		  = NEW.mved_to_type;
		v_date		  = NEW.mved_expired_date;
		v_qty		  = NEW.mved_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_expired_stock SET
			e_qty = e_qty + v_qty
		WHERE it_code = v_it_code AND e_type = v_type AND e_wh_location = v_wh_location AND e_expired_date = v_date;

		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_expired_stock (it_code, e_type, e_wh_location, e_expired_date, e_qty)
			VALUES(v_it_code, v_type, v_wh_location, v_date, v_qty);

			EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
		END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_summary_move_stock_ed_plus_qty() owner to dskim
;

create function idc_summary_move_stock_minus_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint;
	v_type smallint;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.mv_from_wh;
		v_type		  = OLD.mv_from_type;
		v_qty		  = -1 * OLD.mv_qty;

	ELSIF (TG_OP = 'UPDATE') THEN

		IF(OLD.it_code != NEW.it_code OR OLD.mv_from_type != NEW.mv_from_type OR OLD.mv_from_wh != NEW.mv_from_wh) THEN
			RAISE EXCEPTION 'Update of ITEM CODE or ITEM TYPE is not allowed';
		END IF;

		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.mv_from_wh;
		v_type		  = OLD.mv_from_type;
		v_qty		  = NEW.mv_qty - OLD.mv_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	  = NEW.it_code;
		v_wh_location = NEW.mv_from_wh;
		v_type		  = NEW.mv_from_type;
		v_qty		  = NEW.mv_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_stock SET
			stk_qty			= stk_qty - v_qty,
			stk_updated		= CURRENT_TIMESTAMP
		WHERE it_code = v_it_code AND stk_type = v_type AND stk_wh_location = v_wh_location;

		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_stock (it_code, stk_type, stk_wh_location, stk_updated, stk_qty)
			VALUES(v_it_code, v_type, v_wh_location, CURRENT_TIMESTAMP, -v_qty);

			EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
		END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_summary_move_stock_minus_qty() owner to dskim
;

create function idc_summary_move_stock_plus_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint;
	v_type smallint;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.mv_to_wh;
		v_type		  = OLD.mv_to_type;
		v_qty		  = -1 * OLD.mv_qty;

	ELSIF (TG_OP = 'UPDATE') THEN

		IF(OLD.it_code != NEW.it_code OR OLD.mv_to_type != NEW.mv_to_type OR OLD.mv_to_wh != NEW.mv_to_wh) THEN
			RAISE EXCEPTION 'Update of ITEM CODE or ITEM TYPE is not allowed';
		END IF;

		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.mv_to_wh;
		v_type		  = OLD.mv_to_type;
		v_qty		  = NEW.mv_qty - OLD.mv_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	  = NEW.it_code;
		v_wh_location = NEW.mv_to_wh;
		v_type		  = NEW.mv_to_type;
		v_qty		  = NEW.mv_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_stock SET
			stk_qty			= stk_qty + v_qty,
			stk_updated		= CURRENT_TIMESTAMP
		WHERE it_code = v_it_code AND stk_type = v_type AND stk_wh_location = v_wh_location;

		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_stock (it_code, stk_type, stk_wh_location, stk_updated, stk_qty)
			VALUES(v_it_code, v_type, v_wh_location, CURRENT_TIMESTAMP, v_qty);

			EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
		END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_summary_move_stock_plus_qty() owner to dskim
;

create function idc_summary_order_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_cus_code varchar;
	v_it_code varchar;
	v_dept varchar;
	v_delta_ok_qty integer := 0;
	v_delta_oo_qty integer := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_cus_code		= OLD.cus_code;
		v_it_code		= OLD.it_code;
		v_dept			= OLD.odit_dept;
		v_delta_ok_qty	= -1 * OLD.odit_ok_qty;
		v_delta_oo_qty	= -1 * OLD.odit_oo_qty;

	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.cus_code != NEW.cus_code OR OLD.odit_dept != NEW.odit_dept) THEN
			RAISE EXCEPTION 'Update of customer code or it_code is not allowed';
		END IF;
		v_cus_code		= OLD.cus_code;
		v_it_code		= OLD.it_code;
		v_dept			= OLD.odit_dept;
		v_delta_ok_qty	= NEW.odit_ok_qty - OLD.odit_ok_qty;
		v_delta_oo_qty	= NEW.odit_oo_qty - OLD.odit_oo_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_cus_code		= NEW.cus_code;
		v_it_code		= NEW.it_code;
		v_dept			= NEW.odit_dept;
		v_delta_ok_qty	= NEW.odit_ok_qty;
		v_delta_oo_qty	= NEW.odit_oo_qty;
	END IF;

	-- Insert or update the summary row wirh the new values
	<<dahlia>>
	LOOP
	UPDATE idc_tb_apotik_inv SET
		inv_ok		= inv_ok + v_delta_ok_qty,
		inv_oo		= inv_oo + v_delta_oo_qty,
		inv_updated = CURRENT_DATE
	WHERE cus_code = v_cus_code AND it_code = v_it_code AND inv_dept = v_dept;
	EXIT dahlia WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_apotik_inv (cus_code, it_code, inv_dept, inv_updated, inv_ok, inv_oo)
		VALUES(v_cus_code, v_it_code, v_dept, CURRENT_DATE, v_delta_ok_qty, v_delta_oo_qty);
	EXIT dahlia;

	EXCEPTION
		WHEN UNIQUE_VIOLATION THEN
			-- do nothing
		END;
	END LOOP dahlia;

	RETURN NULL;
END;
$$
;

alter function idc_summary_order_qty() owner to dskim
;

create function idc_summary_order_return_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_cus_code varchar;
	v_it_code varchar;
	v_dept varchar;
	v_delta_return integer := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_cus_code		= OLD.cus_code;
		v_it_code		= OLD.it_code;
		v_dept			= OLD.roit_dept;
		v_delta_return = -1 * OLD.roit_qty;

	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.cus_code != NEW.cus_code OR OLD.roit_dept != NEW.roit_dept) THEN
			RAISE EXCEPTION 'Update of customer code or it_code is not allowed';
		END IF;
		v_cus_code 		= OLD.cus_code;
		v_it_code 		= OLD.it_code;
		v_dept			= OLD.roit_dept;
		v_delta_return 	= NEW.roit_qty - OLD.roit_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_cus_code 		= NEW.cus_code;
		v_it_code 		= NEW.it_code;
		v_dept			= NEW.roit_dept;
		v_delta_return	= NEW.roit_qty;
	END IF;

	-- Insert or update the summary row wirh the new values
	<<dahlia>>
	LOOP
		UPDATE idc_tb_apotik_inv SET
			inv_return	= inv_return + v_delta_return,
			inv_updated	= CURRENT_DATE
		WHERE cus_code = v_cus_code AND it_code = v_it_code AND inv_dept = v_dept;
		EXIT dahlia WHEN FOUND;

		BEGIN
		INSERT INTO idc_tb_apotik_inv (cus_code, it_code, inv_dept, inv_updated, inv_return)
		VALUES(v_cus_code, v_it_code, v_dept, CURRENT_DATE, v_delta_return);
		EXIT dahlia;

		EXCEPTION
		WHEN UNIQUE_VIOLATION THEN
			-- do nothing
		END;
	END LOOP dahlia;

	RETURN NULL;
END;
$$
;

alter function idc_summary_order_return_qty() owner to dskim
;

create function idc_summary_outgoing_ed_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint := 1;
	v_type smallint;
	v_date date;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.oted_wh_location;
		v_type		  = OLD.oted_type;
		v_date		  = OLD.oted_date;
		v_qty		  = -1 * OLD.oted_qty;

	ELSIF (TG_OP = 'UPDATE') THEN
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.oted_wh_location;
		v_type		  = OLD.oted_type;
		v_date		  = OLD.oted_date;
		v_qty		  = NEW.oted_qty - OLD.oted_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	  = NEW.it_code;
		v_wh_location = NEW.oted_wh_location;
		v_type		  = NEW.oted_type;
		v_date		  = NEW.oted_date;
		v_qty		  = NEW.oted_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_expired_stock SET
			e_qty			= e_qty - v_qty
		WHERE it_code = v_it_code AND e_type = v_type AND e_wh_location = v_wh_location AND e_expired_date = v_date;
		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_expired_stock (it_code, e_type, e_wh_location, e_expired_date, e_qty)
			VALUES(v_it_code, v_type, v_wh_location, v_date, -v_qty);
			EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
		END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_summary_outgoing_ed_qty() owner to dskim
;

create function idc_summary_outgoing_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint := 1;
	v_type smallint;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.otst_wh_location;
		v_type		  = OLD.otst_type;
		v_qty		  = -1 * OLD.otst_qty;

	ELSIF (TG_OP = 'UPDATE') THEN

		IF(OLD.it_code != NEW.it_code OR OLD.otst_type != NEW.otst_type OR OLD.otst_wh_location != NEW.otst_wh_location) THEN
			RAISE EXCEPTION 'Update of ITEM CODE or ITEM TYPE is not allowed';
		END IF;

		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.otst_wh_location;
		v_type		  = OLD.otst_type;
		v_qty		  = NEW.otst_qty - OLD.otst_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	  = NEW.it_code;
		v_wh_location = NEW.otst_wh_location;
		v_type		  = NEW.otst_type;
		v_qty		  = NEW.otst_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_stock SET
			stk_qty			= stk_qty - v_qty,
			stk_updated		= CURRENT_TIMESTAMP
		WHERE it_code = v_it_code AND stk_type = v_type AND stk_wh_location = v_wh_location;

		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_stock (it_code, stk_type, stk_wh_location, stk_updated, stk_qty)
			VALUES(v_it_code, v_type, v_wh_location, CURRENT_TIMESTAMP, -v_qty);

			EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
		END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_summary_outgoing_qty() owner to dskim
;

create function idc_summary_pending_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_pl_idx integer;
	v_qty integer := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	= OLD.it_code;
		v_pl_idx	= OLD.pl_idx;
		v_qty		= -1 * OLD.plit_qty;

	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.pl_idx != NEW.pl_idx) THEN
			RAISE EXCEPTION 'Update of ITEM CODE or PL IDX is not allowed';
		END IF;
		v_it_code	= OLD.it_code;
		v_pl_idx	= OLD.pl_idx;
		v_qty		= NEW.plit_qty - OLD.plit_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	= NEW.it_code;
		v_pl_idx	= NEW.pl_idx;
		v_qty		= NEW.plit_qty;

	END IF;

	/* Insert or update the summary row wirh the new values */
	<<pending>>
	LOOP
		UPDATE idc_tb_pending_pl SET
			pepl_qty		= pepl_qty + v_qty
		WHERE it_code = v_it_code AND pl_idx = v_pl_idx;
	EXIT pending WHEN FOUND;
	BEGIN
		INSERT INTO idc_tb_pending_pl (it_code, pl_idx, pepl_qty)
		VALUES(v_it_code, v_pl_idx, v_qty);
	EXIT pending;
	EXCEPTION
	WHEN UNIQUE_VIOLATION THEN
		/* do nothing */
	END;
	END LOOP pending;
	RETURN NULL;
END;
$$
;

alter function idc_summary_pending_qty() owner to dskim
;

create function idc_summary_pl_expired_item() returns trigger
	language plpgsql
as $$
DECLARE
	v_idx integer;
	v_it_code varchar;
	v_wh_location smallint;
	v_type integer;
	v_date date;
	v_qty numeric := 0;
BEGIN
	IF(TG_OP = 'DELETE') THEN
		v_idx		= OLD.epl_idx;
		v_it_code	= OLD.it_code;
		v_wh_location = OLD.epl_wh_location;
		v_type		= OLD.epl_type;
		v_date		= OLD.epl_expired_date;
		v_qty		= -1 * OLD.epl_qty;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.epl_qty != NEW.epl_qty) THEN
			RAISE EXCEPTION 'Update of item code or type is not allowed';
		END IF;
		v_idx		= OLD.epl_idx;
		v_it_code	= OLD.it_code;
		v_wh_location = OLD.epl_wh_location;
		v_type		= OLD.epl_type;
		v_date		= OLD.epl_expired_date;
		v_qty		= NEW.epl_qty - OLD.epl_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_idx		= NEW.epl_idx;
		v_it_code	= NEW.it_code;
		v_wh_location = NEW.epl_wh_location;
		v_type		= NEW.epl_type;
		v_date		= NEW.epl_expired_date;
		v_qty		= NEW.epl_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<item>>
	LOOP
	UPDATE idc_tb_expired_stock SET
		e_qty = e_qty + v_qty
	WHERE it_code = v_it_code AND e_type= v_type AND e_expired_date = v_date AND e_wh_location = v_wh_location;
	EXIT item WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_expired_stock (it_code, e_wh_location, e_type, e_expired_date, e_qty)
		VALUES(v_it_code, v_wh_location, v_type, v_date, v_qty);
	EXIT item;

	EXCEPTION
	WHEN UNIQUE_VIOLATION THEN
		/* do nothing */
	END;
	END LOOP item;

	RETURN NULL;
END;
$$
;

alter function idc_summary_pl_expired_item() owner to dskim
;

create function idc_summary_reject_demo_ed_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_date date;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	= OLD.it_code;
		v_date		= OLD.rjde_warranty;
		v_qty		= -1 * OLD.rjde_qty;

	ELSIF (TG_OP = 'UPDATE') THEN

		IF(OLD.it_code != NEW.it_code || OLD.rjde_warranty != NEW.rjde_warranty) THEN
			RAISE EXCEPTION 'Update of ITEM CODE or ITEM TYPE is not allowed';
		END IF;

		v_it_code	= OLD.it_code;
		v_date		= OLD.rjde_warranty;
		v_qty		= NEW.rjde_qty - OLD.rjde_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	= NEW.it_code;
		v_date		= NEW.rjde_warranty;
		v_qty		= NEW.rjde_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_expired_demo SET
			d_qty		= d_qty - v_qty
		WHERE it_code = v_it_code AND d_expired_date = v_date;
		EXIT stock WHEN FOUND;

		BEGIN
			/*  do nothing */
			EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
		END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_summary_reject_demo_ed_qty() owner to dskim
;

create function idc_summary_reject_demo_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	= OLD.it_code;
		v_qty		= -1 * OLD.rjde_qty;

	ELSIF (TG_OP = 'UPDATE') THEN

		IF(OLD.it_code != NEW.it_code) THEN
			RAISE EXCEPTION 'Update of ITEM CODE or ITEM TYPE is not allowed';
		END IF;

		v_it_code	= OLD.it_code;
		v_qty		= NEW.rjde_qty - OLD.rjde_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	= NEW.it_code;
		v_qty		= NEW.rjde_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_demo SET
			demo_qty		= demo_qty - v_qty,
			demo_updated	= CURRENT_TIMESTAMP
		WHERE it_code = v_it_code;
		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_demo (it_code, demo_updated, demo_qty)
			VALUES(v_it_code, CURRENT_TIMESTAMP, -v_qty);
			EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
		END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_summary_reject_demo_qty() owner to dskim
;

create function idc_summary_return_borrow_minus_ed_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint;
	v_type smallint;
	v_date date;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.rebed_from_wh;
		v_type		  = OLD.rebed_from_type;
		v_date		  = OLD.rebed_expired_date;
		v_qty		  = -1 * OLD.rebed_qty;

	ELSIF (TG_OP = 'UPDATE') THEN

		IF(OLD.it_code != NEW.it_code OR OLD.rebed_from_type != NEW.rebed_from_type OR OLD.rebed_from_wh != NEW.rebed_from_wh) THEN
			RAISE EXCEPTION 'Update of ITEM CODE or ITEM TYPE is not allowed';
		END IF;

		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.rebed_from_wh;
		v_type		  = OLD.rebed_from_type;
		v_date		  = OLD.rebed_expired_date;
		v_qty		  = NEW.rebed_qty - OLD.rebed_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	  = NEW.it_code;
		v_wh_location = NEW.rebed_from_wh;
		v_type		  = NEW.rebed_from_type;
		v_date		  = NEW.rebed_expired_date;
		v_qty		  = NEW.rebed_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_expired_stock SET
			e_qty = e_qty - v_qty
		WHERE it_code = v_it_code AND e_type = v_type AND e_wh_location = v_wh_location AND e_expired_date = v_date;

		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_expired_stock (it_code, e_type, e_wh_location, e_expired_date, e_qty)
			VALUES(v_it_code, v_type, v_wh_location, v_date, -v_qty);

			EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
		END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_summary_return_borrow_minus_ed_qty() owner to dskim
;

create function idc_summary_return_borrow_minus_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint;
	v_type smallint;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.rebor_from_wh;
		v_type		  = OLD.rebor_from_type;
		v_qty		  = -1 * OLD.rebor_qty;

	ELSIF (TG_OP = 'UPDATE') THEN

		IF(OLD.it_code != NEW.it_code OR OLD.rebor_from_type != NEW.rebor_from_type OR OLD.rebor_from_wh != NEW.rebor_from_wh) THEN
			RAISE EXCEPTION 'Update of ITEM CODE or ITEM TYPE is not allowed';
		END IF;

		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.rebor_from_wh;
		v_type		  = OLD.rebor_from_type;
		v_qty		  = NEW.rebor_qty - OLD.rebor_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	  = NEW.it_code;
		v_wh_location = NEW.rebor_from_wh;
		v_type		  = NEW.rebor_from_type;
		v_qty		  = NEW.rebor_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_stock SET
			stk_qty			= stk_qty - v_qty,
			stk_updated		= CURRENT_TIMESTAMP
		WHERE it_code = v_it_code AND stk_type = v_type AND stk_wh_location = v_wh_location;

		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_stock (it_code, stk_type, stk_wh_location, stk_updated, stk_qty)
			VALUES(v_it_code, v_type, v_wh_location, CURRENT_TIMESTAMP, -v_qty);

			EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
		END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_summary_return_borrow_minus_qty() owner to dskim
;

create function idc_summary_return_borrow_plus_ed_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint;
	v_type smallint;
	v_date date;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.rebed_to_wh;
		v_type		  = OLD.rebed_to_type;
		v_date		  = OLD.rebed_expired_date;
		v_qty		  = -1 * OLD.rebed_qty;

	ELSIF (TG_OP = 'UPDATE') THEN

		IF(OLD.it_code != NEW.it_code OR OLD.rebed_to_type != NEW.rebed_to_type OR OLD.rebed_to_wh != NEW.rebed_to_wh) THEN
			RAISE EXCEPTION 'Update of ITEM CODE or ITEM TYPE is not allowed';
		END IF;

		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.rebed_to_wh;
		v_type		  = OLD.rebed_to_type;
		v_date		  = OLD.rebed_expired_date;
		v_qty		  = NEW.rebed_qty - OLD.rebed_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	  = NEW.it_code;
		v_wh_location = NEW.rebed_to_wh;
		v_type		  = NEW.rebed_to_type;
		v_date		  = NEW.rebed_expired_date;
		v_qty		  = NEW.rebed_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_expired_stock SET
			e_qty = e_qty + v_qty
		WHERE it_code = v_it_code AND e_type = v_type AND e_wh_location = v_wh_location AND e_expired_date = v_date;

		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_expired_stock (it_code, e_type, e_wh_location, e_expired_date, e_qty)
			VALUES(v_it_code, v_type, v_wh_location, v_date, v_qty);

			EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
		END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_summary_return_borrow_plus_ed_qty() owner to dskim
;

create function idc_summary_return_borrow_plus_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint;
	v_type smallint;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.rebor_to_wh;
		v_type		  = OLD.rebor_to_type;
		v_qty		  = -1 * OLD.rebor_qty;

	ELSIF (TG_OP = 'UPDATE') THEN

		IF(OLD.it_code != NEW.it_code OR OLD.rebor_to_type != NEW.rebor_to_type OR OLD.rebor_to_wh != NEW.rebor_to_wh) THEN
			RAISE EXCEPTION 'Update of ITEM CODE or ITEM TYPE is not allowed';
		END IF;

		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.rebor_to_wh;
		v_type		  = OLD.rebor_to_type;
		v_qty		  = NEW.rebor_qty - OLD.rebor_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	  = NEW.it_code;
		v_wh_location = NEW.rebor_to_wh;
		v_type		  = NEW.rebor_to_type;
		v_qty		  = NEW.rebor_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_stock SET
			stk_qty			= stk_qty + v_qty,
			stk_updated		= CURRENT_TIMESTAMP
		WHERE it_code = v_it_code AND stk_type = v_type AND stk_wh_location = v_wh_location;

		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_stock (it_code, stk_type, stk_wh_location, stk_updated, stk_qty)
			VALUES(v_it_code, v_type, v_wh_location, CURRENT_TIMESTAMP, v_qty);

			EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
		END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_summary_return_borrow_plus_qty() owner to dskim
;

create function idc_summary_return_demo_ed() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_qty numeric := 0;
	v_date date;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	= OLD.it_code;
		v_qty		= -1 * OLD.rded_qty;
		v_date		= OLD.rded_expired_date;

	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code) THEN
			RAISE EXCEPTION 'Update of ITEM CODE is not allowed';
		END IF;

		v_it_code	= OLD.it_code;
		v_qty		= NEW.rded_qty - OLD.rded_qty;
		v_date		= OLD.rded_expired_date;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	= NEW.it_code;
		v_qty		= NEW.rded_qty;
		v_date		= NEW.rded_expired_date;
	END IF;

	/* Insert or update the summary row with the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_expired_demo SET
			d_qty			= d_qty + v_qty
		WHERE it_code = v_it_code AND d_expired_date = v_date;
		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_expired_demo (it_code, d_qty, d_expired_date)
			VALUES(v_it_code, v_qty, v_date);
			EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
		END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_summary_return_demo_ed() owner to dskim
;

create function idc_summary_return_demo_stock() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	= OLD.it_code;
		v_qty		= -1 * OLD.rdst_qty;

	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code) THEN
			RAISE EXCEPTION 'Update of ITEM CODE is not allowed';
		END IF;

		v_it_code	= OLD.it_code;
		v_qty		= NEW.rdst_qty - OLD.rdst_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	= NEW.it_code;
		v_qty		= NEW.rdst_qty;
	END IF;

	/* Insert or update the summary row with the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_demo SET
			demo_qty			= demo_qty + v_qty,
			demo_updated		= CURRENT_TIMESTAMP
		WHERE it_code = v_it_code;
		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_demo (it_code, demo_updated, demo_qty)
			VALUES(v_it_code, CURRENT_TIMESTAMP, v_qty);
			EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
		END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_summary_return_demo_stock() owner to dskim
;

create function idc_summary_return_order_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_cus_code varchar;
	v_it_code varchar;
	v_dept varchar;
	v_delta_return integer := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_cus_code		= OLD.cus_code;
		v_it_code		= OLD.it_code;
		v_dept 			= OLD.roit_dept;
		v_delta_return	= -1 * OLD.roit_qty;

	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.cus_code != NEW.cus_code OR OLD.roit_dept != NEW.roit_dept) THEN
			RAISE EXCEPTION 'Update of customer code or it_code is not allowed';
		END IF;
		v_cus_code		= OLD.cus_code;
		v_it_code		= OLD.it_code;
		v_dept 			= OLD.roit_dept;
		v_delta_return	= NEW.roit_qty - OLD.roit_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_cus_code		= NEW.cus_code;
		v_it_code		= NEW.it_code;
		v_dept 			= NEW.roit_dept;
		v_delta_return	= NEW.roit_qty;
	END IF;

	-- Insert or update the summary row wirh the new values
	<<dahlia>>
	LOOP
	UPDATE idc_tb_apotik_inv SET
		inv_return = inv_return + v_delta_return,
		inv_updated = CURRENT_DATE
	WHERE cus_code = v_cus_code AND it_code = v_it_code AND inv_dept = v_dept;
	EXIT dahlia WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_apotik_inv (cus_code, it_code, inv_dept, inv_updated, inv_return)
		VALUES(v_cus_code, v_it_code, v_dept, CURRENT_DATE, v_delta_return);
	EXIT dahlia;

	EXCEPTION
		WHEN UNIQUE_VIOLATION THEN
			-- do nothing
		END;
	END LOOP dahlia;

	RETURN NULL;
END;
$$
;

alter function idc_summary_return_order_qty() owner to dskim
;

create function idc_summary_return_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_cus_code varchar;
	v_it_code varchar;
	v_dept varchar;
	v_delta_return integer := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_cus_code		= OLD.cus_code;
		v_it_code		= OLD.it_code;
		v_dept			= OLD.roit_dept;
		v_delta_return	= -1 * OLD.rl_qty;

	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.cus_code != NEW.cus_code OR OLD.roit_dept != NEW.roit_dept) THEN
			RAISE EXCEPTION 'Update of customer code or it_code is not allowed';
		END IF;
		v_cus_code		= OLD.cus_code;
		v_it_code		= OLD.it_code;
		v_dept			= OLD.roit_dept;
		v_delta_return	= NEW.rl_qty - OLD.rl_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_cus_code		= NEW.cus_code;
		v_it_code		= NEW.it_code;
		v_dept			= OLD.roit_dept;
		v_delta_return	= NEW.rl_qty;
	END IF;

	-- Insert or update the summary row wirh the new values
	<<dahlia>>
	LOOP
	UPDATE idc_tb_apotik_inv SET
		inv_return = inv_return + v_delta_return,
		inv_updated = CURRENT_DATE
	WHERE cus_code = v_cus_code AND it_code = v_it_code AND inv_dept = v_dept;
	EXIT dahlia WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_apotik_inv (cus_code, it_code, inv_dept, inv_updated, inv_return)
		VALUES(v_cus_code, v_it_code, v_dept, CURRENT_DATE, v_delta_return);
	EXIT dahlia;

	EXCEPTION
		WHEN UNIQUE_VIOLATION THEN
		-- do nothing
		END;
	END LOOP dahlia;

	RETURN NULL;
END;
$$
;

alter function idc_summary_return_qty() owner to dskim
;

create function idc_summary_sales_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_cus_code varchar;
	v_it_code varchar;
	v_dept varchar;
	v_delta_sales integer := 0;
	v_sales_date date;
	v_old_date date;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_cus_code		= OLD.cus_code;
		v_it_code		= OLD.it_code;
		v_dept			= OLD.sl_dept;
		v_delta_sales	= -1 * OLD.sl_qty;
		v_sales_date	= CURRENT_DATE;

	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.cus_code != NEW.cus_code AND OLD.sl_dept != NEW.sl_dept) THEN
			RAISE EXCEPTION 'Update of customer code or it_code is not allowed';
		END IF;
		v_cus_code		= OLD.cus_code;
		v_it_code		= OLD.it_code;
		v_dept			= OLD.sl_dept;
		v_delta_sales	= NEW.sl_qty - OLD.sl_qty;
		v_sales_date	= NEW.sl_date;

	ELSIF (TG_OP = 'INSERT') THEN
		v_cus_code		= NEW.cus_code;
		v_it_code		= NEW.it_code;
		v_dept			= NEW.sl_dept;
		v_delta_sales	= NEW.sl_qty;
		v_sales_date	= NEW.sl_date;

		SELECT INTO v_old_date max(sl_date) FROM idc_tb_sales_log WHERE cus_code = v_cus_code AND it_code = v_it_code AND sl_dept = v_dept;
		IF FOUND AND v_old_date IS NOT NULL THEN
			v_sales_date = v_old_date;
		END IF;

	END IF;

	-- Insert or update the summary row wirh the new values
	<<dahlia>>
	LOOP
	UPDATE idc_tb_apotik_inv SET
		inv_sales			= inv_sales + v_delta_sales,
		inv_updated			= CURRENT_DATE,
		inv_sales_updated	= v_sales_date
	WHERE cus_code = v_cus_code AND it_code = v_it_code AND inv_dept = v_dept;
	EXIT dahlia WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_apotik_inv (cus_code, it_code, inv_dept, inv_updated, inv_sales, inv_sales_updated)
		VALUES(v_cus_code, v_it_code, v_dept, CURRENT_DATE, v_delta_sales, v_sales_date);
	EXIT dahlia;

	EXCEPTION
		WHEN UNIQUE_VIOLATION THEN
			-- do nothing
		END;
	END LOOP dahlia;

	RETURN NULL;
END;
$$
;

alter function idc_summary_sales_qty() owner to dskim
;

create function idc_summary_using_demo_ed() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_qty numeric := 0;
	v_date date;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	= OLD.it_code;
		v_qty		= -1 * OLD.used_qty;
		v_date		= OLD.used_expired_date;

	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code) THEN
			RAISE EXCEPTION 'Update of ITEM CODE is not allowed';
		END IF;

		v_it_code	= OLD.it_code;
		v_qty		= NEW.used_qty - OLD.used_qty;
		v_date		= OLD.used_expired_date;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	= NEW.it_code;
		v_qty		= NEW.used_qty;
		v_date		= NEW.used_expired_date;
	END IF;

	/* Insert or update the summary row with the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_expired_demo SET
			d_qty			= d_qty - v_qty
		WHERE it_code = v_it_code AND d_expired_date = v_date;
		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_expired_demo (it_code, d_qty, d_expired_date)
			VALUES(v_it_code, -v_qty, v_date);
			EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
		END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_summary_using_demo_ed() owner to dskim
;

create function idc_summary_using_demo_stock() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	= OLD.it_code;
		v_qty		= -1 * OLD.usst_qty;

	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code) THEN
			RAISE EXCEPTION 'Update of ITEM CODE is not allowed';
		END IF;

		v_it_code	= OLD.it_code;
		v_qty		= NEW.usst_qty - OLD.usst_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	= NEW.it_code;
		v_qty		= NEW.usst_qty;
	END IF;

	/* Insert or update the summary row with the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_demo SET
			demo_qty			= demo_qty - v_qty,
			demo_updated		= CURRENT_TIMESTAMP
		WHERE it_code = v_it_code;
		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_demo (it_code, demo_updated, demo_qty)
			VALUES(v_it_code, CURRENT_TIMESTAMP, -v_qty);
			EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
		END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_summary_using_demo_stock() owner to dskim
;

create function idc_tb_billing_item_only_log_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_date date;
	v_type integer;
	v_wh_location integer;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code		= OLD.it_code;
		v_date			= OLD.biit_document_date;
		v_type			= OLD.biit_type;
		v_wh_location	= OLD.biit_wh_location;
		v_qty			= -1 * OLD.biit_qty;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.biit_document_date != NEW.biit_document_date OR OLD.biit_type != NEW.biit_type OR OLD.biit_wh_location != NEW.biit_wh_location) THEN
			RAISE EXCEPTION 'Update of item/date/type/warehouse location is not allowed';
		END IF;
		v_it_code		= OLD.it_code;
		v_date			= OLD.biit_document_date;
		v_type			= OLD.biit_type;
		v_wh_location	= OLD.biit_wh_location;
		v_qty			= NEW.biit_qty - OLD.biit_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code		= NEW.it_code;
		v_date			= NEW.biit_document_date;
		v_type			= NEW.biit_type;
		v_wh_location	= NEW.biit_wh_location;
		v_qty			= NEW.biit_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_log SET log_out_qty = log_out_qty + v_qty WHERE it_code = v_it_code AND log_date = v_date AND log_type = v_type AND log_wh_location = v_wh_location;
	EXIT stock WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_log (it_code, log_date, log_type, log_wh_location, log_out_qty) VALUES(v_it_code, v_date, v_type, v_wh_location, v_qty);
	EXIT stock;

	EXCEPTION WHEN UNIQUE_VIOLATION THEN
		/* do nothing */
	END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_tb_billing_item_only_log_qty() owner to dskim
;

create function idc_tb_billing_item_only_log_wh() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_date date;
	v_type integer;
	v_wh_location integer;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code		= OLD.it_code;
		v_date			= OLD.biit_confirm_date;
		v_type			= OLD.biit_type;
		v_wh_location	= OLD.biit_wh_location;
		v_qty			= -1 * OLD.biit_qty;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.biit_confirm_date != NEW.biit_confirm_date OR OLD.biit_type != NEW.biit_type OR OLD.biit_wh_location != NEW.biit_wh_location) THEN
			RAISE EXCEPTION 'Update of item/date/type/warehouse location is not allowed';
		END IF;
		v_it_code		= OLD.it_code;
		v_date			= OLD.biit_confirm_date;
		v_type			= OLD.biit_type;
		v_wh_location	= OLD.biit_wh_location;
		v_qty			= NEW.biit_qty - OLD.biit_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code		= NEW.it_code;
		v_date			= NEW.biit_confirm_date;
		v_type			= NEW.biit_type;
		v_wh_location	= NEW.biit_wh_location;
		v_qty			= NEW.biit_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_log_wh SET log_out_qty = log_out_qty + v_qty WHERE it_code = v_it_code AND log_date = v_date AND log_type = v_type AND log_wh_location = v_wh_location;
	EXIT stock WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_log_wh (it_code, log_date, log_type, log_wh_location, log_out_qty) VALUES(v_it_code, v_date, v_type, v_wh_location, v_qty);
	EXIT stock;

	EXCEPTION WHEN UNIQUE_VIOLATION THEN
		/* do nothing */
	END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_tb_billing_item_only_log_wh() owner to dskim
;

create function idc_tb_billing_item_only_stock() returns trigger
	language plpgsql
as $$
DECLARE
	v_wh_location integer;
	v_type integer;
	v_it_code varchar;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_wh_location	= OLD.biit_wh_location;
		v_type			= OLD.biit_type;
		v_it_code		= OLD.it_code;
		v_qty			= -1 * OLD.biit_qty;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.biit_type != NEW.biit_type OR OLD.biit_wh_location != NEW.biit_wh_location) THEN
			RAISE EXCEPTION 'Update of item code or type or location is not allowed';
		END IF;
		v_wh_location	= OLD.biit_wh_location;
		v_type			= OLD.biit_type;
		v_it_code		= OLD.it_code;
		v_qty			= NEW.biit_qty - OLD.biit_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_wh_location	= NEW.biit_wh_location;
		v_type			= NEW.biit_type;
		v_it_code		= NEW.it_code;
		v_qty			= NEW.biit_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_stock_v2 SET stk_qty = stk_qty - v_qty WHERE stk_type = v_type AND it_code = v_it_code AND stk_wh_location = v_wh_location;
	EXIT stock WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_stock_v2 (stk_type, stk_wh_location, it_code, stk_qty) VALUES(v_type, v_wh_location, v_it_code, -v_qty);
	EXIT stock;

	EXCEPTION WHEN UNIQUE_VIOLATION THEN
		/* do nothing */
	END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_tb_billing_item_only_stock() owner to dskim
;

create function idc_tb_in_claim_item_ed_stock() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint;
	v_date date;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code		= OLD.it_code;
		v_wh_location	= OLD.ined_wh_location;
		v_date			= OLD.ined_expired_date;
		v_qty		  	= OLD.ined_qty * -1;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.ined_wh_location != NEW.ined_wh_location OR OLD.ined_expired_date != NEW.ined_expired_date) THEN
			RAISE EXCEPTION 'Update of item code, type, warehouse location, expired date is not allowed';
		END IF;
		v_it_code		= OLD.it_code;
		v_wh_location	= OLD.ined_wh_location;
		v_date			= OLD.ined_expired_date;
		v_qty			= NEW.ined_qty - OLD.ined_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code		= NEW.it_code;
		v_wh_location	= NEW.ined_wh_location;
		v_date			= NEW.ined_expired_date;
		v_qty			= NEW.ined_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<item>>
	LOOP
		UPDATE idc_tb_stock_ed SET
			sted_qty = sted_qty + v_qty
		WHERE it_code = v_it_code AND sted_expired_date = v_date AND sted_wh_location = v_wh_location;
	EXIT item WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_stock_ed (it_code, sted_wh_location, sted_expired_date, sted_qty)
		VALUES(v_it_code, v_wh_location, v_date, v_qty);
	EXIT item;

	EXCEPTION
	WHEN UNIQUE_VIOLATION THEN
		/* do nothing */
	END;
	END LOOP item;

	RETURN NULL;
END;
$$
;

alter function idc_tb_in_claim_item_ed_stock() owner to dskim
;

create function idc_tb_in_claim_item_v2_log_qty() returns trigger
	language plpgsql
as $$
DECLARE
    v_it_code varchar;
    v_date date;
    v_type integer;
    v_wh_location integer;
    v_qty numeric := 0;
BEGIN

    IF(TG_OP = 'DELETE') THEN
        v_it_code       = OLD.it_code;
        v_date          = OLD.init_confirm_date;
        v_type          = OLD.init_type;
        v_wh_location   = OLD.init_wh_location;
        v_qty           = OLD.init_qty * -1;
    ELSIF (TG_OP = 'UPDATE') THEN
        IF(OLD.it_code != NEW.it_code OR OLD.init_confirm_date != NEW.init_confirm_date OR OLD.init_type != NEW.init_type OR OLD.init_wh_location != NEW.init_wh_location) THEN
            RAISE EXCEPTION 'Update of item code, confirm date, type, warehouse location is not allowed';
        END IF;
        v_it_code       = OLD.it_code;
        v_date          = OLD.init_confirm_date;
        v_type          = OLD.init_type;
        v_wh_location   = OLD.init_wh_location;
        v_qty           = NEW.init_qty - OLD.init_qty;
    ELSIF (TG_OP = 'INSERT') THEN
        v_it_code       = NEW.it_code;
        v_date          = NEW.init_confirm_date;
        v_type          = NEW.init_type;
        v_wh_location   = NEW.init_wh_location;
        v_qty           = NEW.init_qty;
    END IF;

    /* Insert or update the summary row wirh the new values */
    <<stock>>
    LOOP
        UPDATE idc_tb_log SET log_in_qty = log_in_qty + v_qty WHERE it_code = v_it_code AND log_date = v_date AND log_type = v_type AND log_wh_location = v_wh_location;
    EXIT stock WHEN FOUND;

    BEGIN
        INSERT INTO idc_tb_log (it_code, log_date, log_type, log_wh_location, log_in_qty) VALUES(v_it_code, v_date, v_type, v_wh_location, v_qty);
    EXIT stock;
    EXCEPTION WHEN UNIQUE_VIOLATION THEN
        /* do nothing */
    END;
    END LOOP stock;

    RETURN NULL;
END;
$$
;

alter function idc_tb_in_claim_item_v2_log_qty() owner to dskim
;

create function idc_tb_in_claim_item_v2_log_wh_qty() returns trigger
	language plpgsql
as $$
DECLARE
    v_it_code varchar;
    v_date date;
    v_type integer;
    v_wh_location integer;
    v_qty numeric := 0;
BEGIN

    IF(TG_OP = 'DELETE') THEN
        v_it_code       = OLD.it_code;
        v_date          = OLD.init_confirm_date;
        v_type          = OLD.init_type;
        v_wh_location   = OLD.init_wh_location;
        v_qty           = OLD.init_qty * -1;
    ELSIF (TG_OP = 'UPDATE') THEN
        IF(OLD.it_code != NEW.it_code OR OLD.init_confirm_date != NEW.init_confirm_date OR OLD.init_type != NEW.init_type OR OLD.init_wh_location != NEW.init_wh_location) THEN
            RAISE EXCEPTION 'Update of item code, confirm date, type, warehouse location is not allowed';
        END IF;
        v_it_code       = OLD.it_code;
        v_date          = OLD.init_confirm_date;
        v_type          = OLD.init_type;
        v_wh_location   = OLD.init_wh_location;
        v_qty           = NEW.init_qty - OLD.init_qty;
    ELSIF (TG_OP = 'INSERT') THEN
        v_it_code       = NEW.it_code;
        v_date          = NEW.init_confirm_date;
        v_type          = NEW.init_type;
        v_wh_location   = NEW.init_wh_location;
        v_qty           = NEW.init_qty;
    END IF;

    /* Insert or update the summary row wirh the new values */
    <<stock>>
    LOOP
        UPDATE idc_tb_log_wh SET log_in_qty = log_in_qty + v_qty WHERE it_code = v_it_code AND log_date = v_date AND log_type = v_type AND log_wh_location = v_wh_location;
    EXIT stock WHEN FOUND;

    BEGIN
        INSERT INTO idc_tb_log_wh (it_code, log_date, log_type, log_wh_location, log_in_qty) VALUES(v_it_code, v_date, v_type, v_wh_location, v_qty);
    EXIT stock;
    EXCEPTION WHEN UNIQUE_VIOLATION THEN
        /* do nothing */
    END;
    END LOOP stock;

    RETURN NULL;
END;
$$
;

alter function idc_tb_in_claim_item_v2_log_wh_qty() owner to dskim
;

create function idc_tb_in_claim_item_v2_stock() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint;
	v_type smallint;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.init_wh_location;
		v_type		  = OLD.init_type;
		v_qty		  = OLD.init_qty * -1;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.init_type != NEW.init_type OR OLD.init_wh_location != NEW.init_wh_location) THEN
			RAISE EXCEPTION 'Update of item code, type, warehouse location is not allowed';
		END IF;
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.init_wh_location;
		v_type		  = OLD.init_type;
		v_qty		  = NEW.init_qty - OLD.init_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	  = NEW.it_code;
		v_wh_location = NEW.init_wh_location;
		v_type		  = NEW.init_type;
		v_qty		  = NEW.init_qty;
	END IF;

	/* Insert or update the summary row with the new values */
	<<stock>>	LOOP
		UPDATE idc_tb_stock_v2 SET
			stk_qty = stk_qty + v_qty
		WHERE it_code = v_it_code AND stk_type = v_type AND stk_wh_location = v_wh_location;
	EXIT stock WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_stock_v2 (it_code, stk_type, stk_wh_location, stk_qty)
		VALUES(v_it_code, v_type, v_wh_location, v_qty);
	EXIT stock;

	EXCEPTION
		WHEN UNIQUE_VIOLATION THEN
			/* do nothing */
		END;
	END LOOP stock;
	RETURN NULL;

END;
$$
;

alter function idc_tb_in_claim_item_v2_stock() owner to dskim
;

create function idc_tb_in_local_item_ed_stock() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint;
	v_date date;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code		= OLD.it_code;
		v_wh_location	= OLD.ined_wh_location;
		v_date			= OLD.ined_expired_date;
		v_qty			= OLD.ined_qty * -1;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.ined_wh_location != NEW.ined_wh_location OR OLD.ined_expired_date != NEW.ined_expired_date) THEN
			RAISE EXCEPTION 'Update of item code, type, warehouse location, expired date is not allowed';
		END IF;
		v_it_code		= OLD.it_code;
		v_wh_location	= OLD.ined_wh_location;
		v_date			= OLD.ined_expired_date;
		v_qty			= NEW.ined_qty - OLD.ined_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code		= NEW.it_code;
		v_wh_location	= NEW.ined_wh_location;
		v_date			= NEW.ined_expired_date;
		v_qty			= NEW.ined_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<item>>
	LOOP
		UPDATE idc_tb_stock_ed SET sted_qty = sted_qty + v_qty WHERE it_code = v_it_code AND sted_expired_date = v_date AND sted_wh_location = v_wh_location;
	EXIT item WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_stock_ed (it_code, sted_wh_location, sted_expired_date, sted_qty) VALUES(v_it_code, v_wh_location, v_date, v_qty);
	EXIT item;

	EXCEPTION WHEN UNIQUE_VIOLATION THEN
		/* do nothing */
	END;
	END LOOP item;

	RETURN NULL;
END;
$$
;

alter function idc_tb_in_local_item_ed_stock() owner to dskim
;

create function idc_tb_in_local_item_v2_log_qty() returns trigger
	language plpgsql
as $$
DECLARE
    v_it_code varchar;
    v_date date;
    v_type integer;
    v_wh_location integer;
    v_qty numeric := 0;
BEGIN

    IF(TG_OP = 'DELETE') THEN
        v_it_code       = OLD.it_code;
        v_date          = OLD.init_confirm_date;
        v_type          = OLD.init_type;
        v_wh_location   = OLD.init_wh_location;
        v_qty           = OLD.init_qty * -1;
    ELSIF (TG_OP = 'UPDATE') THEN
        IF(OLD.it_code != NEW.it_code OR OLD.init_confirm_date != NEW.init_confirm_date OR OLD.init_type != NEW.init_type OR OLD.init_wh_location != NEW.init_wh_location) THEN
            RAISE EXCEPTION 'Update of item code, confirm date, type, warehouse location is not allowed';
        END IF;
        v_it_code       = OLD.it_code;
        v_date          = OLD.init_confirm_date;
        v_type          = OLD.init_type;
        v_wh_location   = OLD.init_wh_location;
        v_qty           = NEW.init_qty - OLD.init_qty;
    ELSIF (TG_OP = 'INSERT') THEN
        v_it_code       = NEW.it_code;
        v_date          = NEW.init_confirm_date;
        v_type          = NEW.init_type;
        v_wh_location   = NEW.init_wh_location;
        v_qty           = NEW.init_qty;
    END IF;

    /* Insert or update the summary row wirh the new values */
    <<stock>>
    LOOP
        UPDATE idc_tb_log SET log_in_qty = log_in_qty + v_qty WHERE it_code = v_it_code AND log_date = v_date AND log_type = v_type AND log_wh_location = v_wh_location;
    EXIT stock WHEN FOUND;

    BEGIN
        INSERT INTO idc_tb_log (it_code, log_date, log_type, log_wh_location, log_in_qty) VALUES(v_it_code, v_date, v_type, v_wh_location, v_qty);
    EXIT stock;
    EXCEPTION WHEN UNIQUE_VIOLATION THEN
        /* do nothing */
    END;
    END LOOP stock;

    RETURN NULL;
END;
$$
;

alter function idc_tb_in_local_item_v2_log_qty() owner to dskim
;

create function idc_tb_in_local_item_v2_log_wh_qty() returns trigger
	language plpgsql
as $$
DECLARE
    v_it_code varchar;
    v_date date;
    v_type integer;
    v_wh_location integer;
    v_qty numeric := 0;
BEGIN

    IF(TG_OP = 'DELETE') THEN
        v_it_code       = OLD.it_code;
        v_date          = OLD.init_confirm_date;
        v_type          = OLD.init_type;
        v_wh_location   = OLD.init_wh_location;
        v_qty           = OLD.init_qty * -1;
    ELSIF (TG_OP = 'UPDATE') THEN
        IF(OLD.it_code != NEW.it_code OR OLD.init_confirm_date != NEW.init_confirm_date OR OLD.init_type != NEW.init_type OR OLD.init_wh_location != NEW.init_wh_location) THEN
            RAISE EXCEPTION 'Update of item code, confirm date, type, warehouse location is not allowed';
        END IF;
        v_it_code       = OLD.it_code;
        v_date          = OLD.init_confirm_date;
        v_type          = OLD.init_type;
        v_wh_location   = OLD.init_wh_location;
        v_qty           = NEW.init_qty - OLD.init_qty;
    ELSIF (TG_OP = 'INSERT') THEN
        v_it_code       = NEW.it_code;
        v_date          = NEW.init_confirm_date;
        v_type          = NEW.init_type;
        v_wh_location   = NEW.init_wh_location;
        v_qty           = NEW.init_qty;
    END IF;

    /* Insert or update the summary row wirh the new values */
    <<stock>>
    LOOP
        UPDATE idc_tb_log_wh SET log_in_qty = log_in_qty + v_qty WHERE it_code = v_it_code AND log_date = v_date AND log_type = v_type AND log_wh_location = v_wh_location;
    EXIT stock WHEN FOUND;

    BEGIN
        INSERT INTO idc_tb_log_wh (it_code, log_date, log_type, log_wh_location, log_in_qty) VALUES(v_it_code, v_date, v_type, v_wh_location, v_qty);
    EXIT stock;
    EXCEPTION WHEN UNIQUE_VIOLATION THEN
        /* do nothing */
    END;
    END LOOP stock;

    RETURN NULL;
END;
$$
;

alter function idc_tb_in_local_item_v2_log_wh_qty() owner to dskim
;

create function idc_tb_in_local_item_v2_stock() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint;
	v_type smallint;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.init_wh_location;
		v_type		  = OLD.init_type;
		v_qty		  = OLD.init_qty * -1;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.init_type != NEW.init_type OR OLD.init_wh_location != NEW.init_wh_location) THEN
			RAISE EXCEPTION 'Update of item code, type, warehouse location is not allowed';
		END IF;
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.init_wh_location;
		v_type		  = OLD.init_type;
		v_qty		  = NEW.init_qty - OLD.init_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	  = NEW.it_code;
		v_wh_location = NEW.init_wh_location;
		v_type		  = NEW.init_type;
		v_qty		  = NEW.init_qty;
	END IF;

	/* Insert or update the summary row with the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_stock_v2 SET stk_qty = stk_qty + v_qty WHERE it_code = v_it_code AND stk_type = v_type AND stk_wh_location = v_wh_location;
	EXIT stock WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_stock_v2 (it_code, stk_type, stk_wh_location, stk_qty) VALUES(v_it_code, v_type, v_wh_location, v_qty);
	EXIT stock;

	EXCEPTION
	WHEN UNIQUE_VIOLATION THEN
		/* do nothing */
	END;
	END LOOP stock;
	RETURN NULL;
END;
$$
;

alter function idc_tb_in_local_item_v2_stock() owner to dskim
;

create function idc_tb_in_pl_item_ed_stock() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint;
	v_date date;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code		= OLD.it_code;
		v_wh_location	= OLD.ined_wh_location;
		v_date			= OLD.ined_expired_date;
		v_qty			= -1 * OLD.ined_qty;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.ined_wh_location != NEW.ined_wh_location OR OLD.ined_expired_date != NEW.ined_expired_date) THEN
			RAISE EXCEPTION 'Update of item code, type, warehouse location, expired date is not allowed';
		END IF;
		v_it_code		= OLD.it_code;
		v_wh_location	= OLD.ined_wh_location;
		v_date			= OLD.ined_expired_date;
		v_qty			= NEW.ined_qty - OLD.ined_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code		= NEW.it_code;
		v_wh_location	= NEW.ined_wh_location;
		v_date			= NEW.ined_expired_date;
		v_qty			= NEW.ined_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<item>>
	LOOP
		UPDATE idc_tb_stock_ed SET
			sted_qty = sted_qty + v_qty
		WHERE it_code = v_it_code AND sted_expired_date = v_date AND sted_wh_location = v_wh_location;
	EXIT item WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_stock_ed (it_code, sted_wh_location, sted_expired_date, sted_qty)
		VALUES(v_it_code, v_wh_location, v_date, v_qty);
	EXIT item;

	EXCEPTION
	WHEN UNIQUE_VIOLATION THEN
		/* do nothing */
	END;
	END LOOP item;

	RETURN NULL;
END;
$$
;

alter function idc_tb_in_pl_item_ed_stock() owner to dskim
;

create function idc_tb_in_pl_item_v2_log_qty() returns trigger
	language plpgsql
as $$
DECLARE
    v_it_code varchar;
    v_date date;
    v_type integer;
    v_wh_location integer;
    v_qty numeric := 0;
BEGIN

    IF(TG_OP = 'DELETE') THEN
        v_it_code       = OLD.it_code;
        v_date          = OLD.init_confirm_date;
        v_type          = OLD.init_type;
        v_wh_location   = OLD.init_wh_location;
        v_qty           = -1 * OLD.init_qty;
    ELSIF (TG_OP = 'UPDATE') THEN
        IF(OLD.it_code != NEW.it_code OR OLD.init_confirm_date != NEW.init_confirm_date OR OLD.init_type != NEW.init_type OR OLD.init_wh_location != NEW.init_wh_location) THEN
            RAISE EXCEPTION 'Update of item code, confirm date, type, warehouse location is not allowed';
        END IF;
        v_it_code       = OLD.it_code;
        v_date          = OLD.init_confirm_date;
        v_type          = OLD.init_type;
        v_wh_location   = OLD.init_wh_location;
        v_qty           = NEW.init_qty - OLD.init_qty;
    ELSIF (TG_OP = 'INSERT') THEN
        v_it_code       = NEW.it_code;
        v_date          = NEW.init_confirm_date;
        v_type          = NEW.init_type;
        v_wh_location   = NEW.init_wh_location;
        v_qty           = NEW.init_qty;
    END IF;

    /* Insert or update the summary row wirh the new values */
    <<stock>>
    LOOP
        UPDATE idc_tb_log SET log_in_qty = log_in_qty + v_qty WHERE it_code = v_it_code AND log_date = v_date AND log_type = v_type AND log_wh_location = v_wh_location;
    EXIT stock WHEN FOUND;

    BEGIN
        INSERT INTO idc_tb_log (it_code, log_date, log_type, log_wh_location, log_in_qty) VALUES(v_it_code, v_date, v_type, v_wh_location, v_qty);
    EXIT stock;
    EXCEPTION WHEN UNIQUE_VIOLATION THEN
        /* do nothing */
    END;
    END LOOP stock;

    RETURN NULL;
END;
$$
;

alter function idc_tb_in_pl_item_v2_log_qty() owner to dskim
;

create function idc_tb_in_pl_item_v2_log_wh_qty() returns trigger
	language plpgsql
as $$
DECLARE
    v_it_code varchar;
    v_date date;
    v_type integer;
    v_wh_location integer;
    v_qty numeric := 0;
BEGIN

    IF(TG_OP = 'DELETE') THEN
        v_it_code       = OLD.it_code;
        v_date          = OLD.init_confirm_date;
        v_type          = OLD.init_type;
        v_wh_location   = OLD.init_wh_location;
        v_qty           = -1 * OLD.init_qty;
    ELSIF (TG_OP = 'UPDATE') THEN
        IF(OLD.it_code != NEW.it_code OR OLD.init_confirm_date != NEW.init_confirm_date OR OLD.init_type != NEW.init_type OR OLD.init_wh_location != NEW.init_wh_location) THEN
            RAISE EXCEPTION 'Update of item code, confirm date, type, warehouse location is not allowed';
        END IF;
        v_it_code       = OLD.it_code;
        v_date          = OLD.init_confirm_date;
        v_type          = OLD.init_type;
        v_wh_location   = OLD.init_wh_location;
        v_qty           = NEW.init_qty - OLD.init_qty;
    ELSIF (TG_OP = 'INSERT') THEN
        v_it_code       = NEW.it_code;
        v_date          = NEW.init_confirm_date;
        v_type          = NEW.init_type;
        v_wh_location   = NEW.init_wh_location;
        v_qty           = NEW.init_qty;
    END IF;

    /* Insert or update the summary row wirh the new values */
    <<stock>>
    LOOP
        UPDATE idc_tb_log_wh SET log_in_qty = log_in_qty + v_qty WHERE it_code = v_it_code AND log_date = v_date AND log_type = v_type AND log_wh_location = v_wh_location;
    EXIT stock WHEN FOUND;

    BEGIN
        INSERT INTO idc_tb_log_wh (it_code, log_date, log_type, log_wh_location, log_in_qty) VALUES(v_it_code, v_date, v_type, v_wh_location, v_qty);
    EXIT stock;
    EXCEPTION WHEN UNIQUE_VIOLATION THEN
        /* do nothing */
    END;
    END LOOP stock;

    RETURN NULL;
END;
$$
;

alter function idc_tb_in_pl_item_v2_log_wh_qty() owner to dskim
;

create function idc_tb_in_pl_item_v2_remain_pl() returns trigger
	language plpgsql
as $$
DECLARE
    v_it_code varchar;
    v_pl_idx integer;
    v_qty numeric := 0;
    v_convert_qty integer;
BEGIN

    IF(TG_OP = 'DELETE') THEN
        v_it_code   = OLD.it_code;
        v_pl_idx    = OLD.pl_idx;
        v_qty       = -1 * OLD.init_qty;

    ELSIF (TG_OP = 'UPDATE') THEN
        IF(OLD.it_code != NEW.it_code OR OLD.pl_idx != NEW.pl_idx) THEN
            RAISE EXCEPTION 'Update of ITEM CODE or PL IDX is not allowed';
        END IF;
        v_it_code   = OLD.it_code;
        v_pl_idx    = OLD.pl_idx;
        v_qty       = NEW.init_qty - OLD.init_qty;

    ELSIF (TG_OP = 'INSERT') THEN
        v_it_code   = NEW.it_code;
        v_pl_idx    = NEW.pl_idx;
        v_qty       = NEW.init_qty;

    END IF;

    /* Insert or update the summary row wirh the new values */
    <<coming>>  LOOP
        UPDATE idc_tb_pending_pl SET pepl_qty = pepl_qty - v_qty::integer WHERE it_code = v_it_code AND pl_idx = v_pl_idx;
    EXIT coming WHEN FOUND;

    BEGIN
        INSERT INTO idc_tb_pending_pl (it_code, pl_idx, pepl_qty)
        VALUES(v_it_code, v_pl_idx, -v_qty::integer);
    EXIT coming;

    EXCEPTION
        WHEN UNIQUE_VIOLATION THEN
            /* do nothing */
        END;
    END LOOP coming;
    RETURN NULL;

END;
$$
;

alter function idc_tb_in_pl_item_v2_remain_pl() owner to dskim
;

create function idc_tb_in_pl_item_v2_stock() returns trigger
	language plpgsql
as $$
DECLARE
    v_it_code varchar;
    v_wh_location smallint;
    v_type smallint;
    v_qty numeric := 0;
BEGIN

    IF(TG_OP = 'DELETE') THEN
        v_it_code     = OLD.it_code;
        v_wh_location = OLD.init_wh_location;
        v_type        = OLD.init_type;
        v_qty         = OLD.init_qty * -1;
    ELSIF (TG_OP = 'UPDATE') THEN
        IF(OLD.it_code != NEW.it_code OR OLD.init_type != NEW.init_type OR OLD.init_wh_location != NEW.init_wh_location) THEN
            RAISE EXCEPTION 'Update of item code, type, warehouse location is not allowed';
        END IF;
        v_it_code     = OLD.it_code;
        v_wh_location = OLD.init_wh_location;
        v_type        = OLD.init_type;
        v_qty         = NEW.init_qty - OLD.init_qty;
    ELSIF (TG_OP = 'INSERT') THEN
        v_it_code     = NEW.it_code;
        v_wh_location = NEW.init_wh_location;
        v_type        = NEW.init_type;
        v_qty         = NEW.init_qty;
    END IF;

    /* Insert or update the summary row with the new values */
    <<stock>>   LOOP
        UPDATE idc_tb_stock_v2 SET
            stk_qty = stk_qty + v_qty
        WHERE it_code = v_it_code AND stk_type = v_type AND stk_wh_location = v_wh_location;
    EXIT stock WHEN FOUND;

    BEGIN
        INSERT INTO idc_tb_stock_v2 (it_code, stk_type, stk_wh_location, stk_qty)
        VALUES(v_it_code, v_type, v_wh_location, v_qty);
    EXIT stock;

    EXCEPTION
        WHEN UNIQUE_VIOLATION THEN
            /* do nothing */
        END;
    END LOOP stock;
    RETURN NULL;

END;
$$
;

alter function idc_tb_in_pl_item_v2_stock() owner to dskim
;

create function idc_tb_incoming_stock_ed_v2_stock() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint;
	v_date date;
	v_qty numeric := 0;
BEGIN
	IF(TG_OP = 'DELETE') THEN
		v_it_code	= OLD.it_code;
		v_wh_location = OLD.ined_wh_location;
		v_date		= OLD.ined_expired_date;
		v_qty		= -1 * OLD.ined_qty;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.ined_wh_location != NEW.ined_wh_location OR OLD.ined_expired_date != NEW.ined_expired_date) THEN
			RAISE EXCEPTION 'Update of item code, location, type, expired date is not allowed';
		END IF;
		v_it_code	= OLD.it_code;
		v_wh_location = OLD.ined_wh_location;
		v_date		= OLD.ined_expired_date;
		v_qty		= NEW.ined_qty - OLD.ined_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	= NEW.it_code;
		v_wh_location = NEW.ined_wh_location;
		v_date		= NEW.ined_expired_date;
		v_qty		= NEW.ined_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_stock_ed SET
			sted_qty = sted_qty + v_qty
		WHERE it_code = v_it_code AND sted_expired_date = v_date AND sted_wh_location = v_wh_location;
		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_stock_ed (it_code, sted_wh_location, sted_expired_date, sted_qty)
			VALUES(v_it_code, v_wh_location, v_date, v_qty);
		EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
			END;
		END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_tb_incoming_stock_ed_v2_stock() owner to dskim
;

create function idc_tb_incoming_stock_v2_log_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_date_confirm date;
	v_date_document date;
	v_type integer;
	v_wh_location integer;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code		= OLD.it_code;
		v_date_confirm	= OLD.inst_confirm_date;
		v_date_document	= OLD.inst_document_date;
		v_type			= OLD.inst_type;
		v_wh_location	= OLD.inst_wh_location;
		v_qty			= OLD.inst_qty * -1;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.inst_confirm_date != NEW.inst_confirm_date OR OLD.inst_document_date != NEW.inst_document_date OR OLD.inst_type != NEW.inst_type OR OLD.inst_wh_location != NEW.inst_wh_location) THEN
			RAISE EXCEPTION 'Update of item code, confirm date, document date, type, warehouse location is not allowed';
		END IF;
		v_it_code		= OLD.it_code;
		v_date_confirm	= OLD.inst_confirm_date;
		v_date_document	= OLD.inst_document_date;
		v_type			= OLD.inst_type;
		v_wh_location	= OLD.inst_wh_location;
		v_qty			= NEW.inst_qty - OLD.inst_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code		= NEW.it_code;
		v_date_confirm	= NEW.inst_confirm_date;
		v_date_document	= NEW.inst_document_date;
		v_type			= NEW.inst_type;
		v_wh_location	= NEW.inst_wh_location;
		v_qty			= NEW.inst_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_log SET log_in_qty = log_in_qty + v_qty WHERE it_code = v_it_code AND log_date = v_date_document AND log_type = v_type AND log_wh_location = v_wh_location;
	EXIT stock WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_log (it_code, log_date, log_type, log_wh_location, log_in_qty) VALUES(v_it_code, v_date_document, v_type, v_wh_location, v_qty);
	EXIT stock;
	EXCEPTION WHEN UNIQUE_VIOLATION THEN
		/* do nothing */
	END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_tb_incoming_stock_v2_log_qty() owner to dskim
;

create function idc_tb_incoming_stock_v2_log_wh() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_date_confirm date;
	v_date_document date;
	v_type integer;
	v_wh_location integer;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code		= OLD.it_code;
		v_date_confirm	= OLD.inst_confirm_date;
		v_date_document	= OLD.inst_document_date;
		v_type			= OLD.inst_type;
		v_wh_location	= OLD.inst_wh_location;
		v_qty			= OLD.inst_qty * -1;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.inst_confirm_date != NEW.inst_confirm_date OR OLD.inst_document_date != NEW.inst_document_date OR OLD.inst_type != NEW.inst_type OR OLD.inst_wh_location != NEW.inst_wh_location) THEN
			RAISE EXCEPTION 'Update of item code, confirm date, document date, type, warehouse location is not allowed';
		END IF;
		v_it_code		= OLD.it_code;
		v_date_confirm	= OLD.inst_confirm_date;
		v_date_document	= OLD.inst_document_date;
		v_type			= OLD.inst_type;
		v_wh_location	= OLD.inst_wh_location;
		v_qty			= NEW.inst_qty - OLD.inst_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code		= NEW.it_code;
		v_date_confirm	= NEW.inst_confirm_date;
		v_date_document	= NEW.inst_document_date;
		v_type			= NEW.inst_type;
		v_wh_location	= NEW.inst_wh_location;
		v_qty			= NEW.inst_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_log_wh SET log_in_qty = log_in_qty + v_qty WHERE it_code = v_it_code AND log_date = v_date_confirm AND log_type = v_type AND log_wh_location = v_wh_location;
	EXIT stock WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_log_wh (it_code, log_date, log_type, log_wh_location, log_in_qty) VALUES(v_it_code, v_date_confirm, v_type, v_wh_location, v_qty);
	EXIT stock;
	EXCEPTION WHEN UNIQUE_VIOLATION THEN
		/* do nothing */
	END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_tb_incoming_stock_v2_log_wh() owner to dskim
;

create function idc_tb_incoming_stock_v2_stock() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint := 1;
	v_type smallint;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.inst_wh_location;
		v_type		  = OLD.inst_type;
		v_qty		  = -1 * OLD.inst_qty;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.inst_type != NEW.inst_type OR OLD.inst_wh_location != NEW.inst_wh_location) THEN
			RAISE EXCEPTION 'Update of ITEM CODE or ITEM TYPE is not allowed';
		END IF;
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.inst_wh_location;
		v_type		  = OLD.inst_type;
		v_qty		  = NEW.inst_qty - OLD.inst_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	  = NEW.it_code;
		v_wh_location = NEW.inst_wh_location;
		v_type		  = NEW.inst_type;
		v_qty		  = NEW.inst_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_stock_v2 SET
			stk_qty = stk_qty + v_qty
		WHERE it_code = v_it_code AND stk_type = v_type AND stk_wh_location = v_wh_location;
		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_stock_v2 (it_code, stk_type, stk_wh_location, stk_qty)
			VALUES(v_it_code, v_type, v_wh_location, v_qty);
		EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
			END;
		END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_tb_incoming_stock_v2_stock() owner to dskim
;

create function idc_tb_initial_stock_ed_stock() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint;
	v_date date;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code		= OLD.it_code;
		v_wh_location	= OLD.ined_wh_location;
		v_date			= OLD.ined_expired_date;
		v_qty			= -1 * OLD.ined_qty;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.ined_wh_location != NEW.ined_wh_location OR OLD.ined_expired_date != NEW.ined_expired_date) THEN
			RAISE EXCEPTION 'Update of item code or type is not allowed';
		END IF;
		v_it_code		= OLD.it_code;
		v_wh_location	= OLD.ined_wh_location;
		v_date			= OLD.ined_expired_date;
		v_qty			= NEW.ined_qty - OLD.ined_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code		= NEW.it_code;
		v_wh_location	= NEW.ined_wh_location;
		v_date			= NEW.ined_expired_date;
		v_qty			= NEW.ined_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<item>>
	LOOP
		UPDATE idc_tb_stock_ed SET
			sted_qty = sted_qty + v_qty
		WHERE it_code = v_it_code AND sted_expired_date = v_date AND sted_wh_location = v_wh_location;
	EXIT item WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_stock_ed (it_code, sted_wh_location, sted_expired_date, sted_qty)
		VALUES(v_it_code, v_wh_location, v_date, v_qty);
	EXIT item;

	EXCEPTION WHEN UNIQUE_VIOLATION THEN
		/* do nothing */
	END;
	END LOOP item;

	RETURN NULL;
END;
$$
;

alter function idc_tb_initial_stock_ed_stock() owner to dskim
;

create function idc_tb_initial_stock_v2_log_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_date date;
	v_type integer;
	v_wh_location integer;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code		= OLD.it_code;
		v_date			= OLD.init_confirm_date::date;
		v_type			= OLD.init_type;
		v_wh_location	= OLD.init_wh_location;
		v_qty			= -1 * OLD.init_qty;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.init_confirm_date != NEW.init_confirm_date OR OLD.init_type != NEW.init_type OR OLD.init_wh_location != NEW.init_wh_location) THEN
			RAISE EXCEPTION 'Update of item/date/type/warehouse location is not allowed';
		END IF;
		v_it_code		= OLD.it_code;
		v_date			= OLD.init_confirm_date::date;
		v_type			= OLD.init_type;
		v_wh_location	= OLD.init_wh_location;
		v_qty			= NEW.init_qty - OLD.init_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code		= NEW.it_code;
		v_date			= NEW.init_confirm_date::date;
		v_type			= NEW.init_type;
		v_wh_location	= NEW.init_wh_location;
		v_qty			= NEW.init_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
	UPDATE idc_tb_log SET
		log_in_qty = log_in_qty + v_qty
	WHERE it_code = v_it_code AND log_date = v_date AND log_type = v_type AND log_wh_location = v_wh_location;
	UPDATE idc_tb_log_wh SET
		log_in_qty = log_in_qty + v_qty
	WHERE it_code = v_it_code AND log_date = v_date AND log_type = v_type AND log_wh_location = v_wh_location;
	EXIT stock WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_log (it_code, log_date, log_type, log_wh_location, log_in_qty) VALUES(v_it_code, v_date, v_type, v_wh_location, v_qty);
		INSERT INTO idc_tb_log_wh (it_code, log_date, log_type, log_wh_location, log_in_qty) VALUES(v_it_code, v_date, v_type, v_wh_location, v_qty);
	EXIT stock;
	EXCEPTION WHEN UNIQUE_VIOLATION THEN
		/* do nothing */
	END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_tb_initial_stock_v2_log_qty() owner to dskim
;

create function idc_tb_initial_stock_v2_stock() returns trigger
	language plpgsql
as $$
DECLARE
	v_wh_location integer;
	v_type integer;
	v_it_code varchar;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_wh_location	= OLD.init_wh_location;
		v_type			= OLD.init_type;
		v_it_code		= OLD.it_code;
		v_qty			= -1 * OLD.init_qty;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.init_type != NEW.init_type) THEN
			RAISE EXCEPTION 'Update of item code or type or location is not allowed';
		END IF;
		v_wh_location	= OLD.init_wh_location;
		v_type			= OLD.init_type;
		v_it_code		= OLD.it_code;
		v_qty			= NEW.init_qty - OLD.init_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_wh_location	= NEW.init_wh_location;
		v_type			= NEW.init_type;
		v_it_code		= NEW.it_code;
		v_qty			= NEW.init_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
	UPDATE idc_tb_stock_v2 SET
		stk_qty = stk_qty + v_qty
	WHERE stk_type = v_type AND it_code = v_it_code AND stk_wh_location = v_wh_location;
	EXIT stock WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_stock_v2 (stk_type, stk_wh_location, it_code, stk_qty)
		VALUES(v_type, v_wh_location, v_it_code, v_qty);
	EXIT stock;
	EXCEPTION WHEN UNIQUE_VIOLATION THEN
		/* do nothing */
	END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_tb_initial_stock_v2_stock() owner to dskim
;

create function idc_tb_outgoing_stock_ed_stock() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint := 1;
	v_date date;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.oted_wh_location;
		v_date		  = OLD.oted_expired_date;
		v_qty		  = -1 * OLD.oted_qty;

	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.oted_wh_location != NEW.oted_wh_location OR OLD.oted_expired_date != NEW.oted_expired_date) THEN
			RAISE EXCEPTION 'Update of item code, location, type, expired date is not allowed';
		END IF;
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.oted_wh_location;
		v_date		  = OLD.oted_expired_date;
		v_qty		  = OLD.oted_qty - NEW.oted_qty;

	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	  = NEW.it_code;
		v_wh_location = NEW.oted_wh_location;
		v_date		  = NEW.oted_expired_date;
		v_qty		  = NEW.oted_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_stock_ed SET
			sted_qty = sted_qty - v_qty
		WHERE it_code = v_it_code AND sted_expired_date = v_date AND sted_wh_location = v_wh_location;
		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_stock_ed (it_code, sted_wh_location, sted_expired_date, sted_qty)
			VALUES(v_it_code, v_wh_location, v_date, -v_qty);
		EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
			END;
		END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_tb_outgoing_stock_ed_stock() owner to dskim
;

create function idc_tb_outgoing_stock_v2_log_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_date_confirm date;
	v_date_document date;
	v_type integer;
	v_wh_location integer;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code		= OLD.it_code;
		v_date_confirm	= OLD.otst_confirm_date;
		v_date_document	= OLD.otst_document_date;
		v_type			= OLD.otst_type;
		v_wh_location	= OLD.otst_wh_location;
		v_qty			= OLD.otst_qty * -1;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.otst_confirm_date != NEW.otst_confirm_date OR OLD.otst_document_date != NEW.otst_document_date OR OLD.otst_type != NEW.otst_type OR OLD.otst_wh_location != NEW.otst_wh_location) THEN
			RAISE EXCEPTION 'Update of item code, confirm date, document date, type, warehouse location is not allowed';
		END IF;
		v_it_code		= OLD.it_code;
		v_date_confirm	= OLD.otst_confirm_date;
		v_date_document	= OLD.otst_document_date;
		v_type			= OLD.otst_type;
		v_wh_location	= OLD.otst_wh_location;
		v_qty			= NEW.otst_qty - OLD.otst_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code		= NEW.it_code;
		v_date_confirm	= NEW.otst_confirm_date;
		v_date_document	= NEW.otst_document_date;
		v_type			= NEW.otst_type;
		v_wh_location	= NEW.otst_wh_location;
		v_qty			= NEW.otst_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_log SET log_out_qty = log_out_qty + v_qty WHERE it_code = v_it_code AND log_date = v_date_document AND log_type = v_type AND log_wh_location = v_wh_location;
	EXIT stock WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_log (it_code, log_date, log_type, log_wh_location, log_out_qty) VALUES(v_it_code, v_date_document, v_type, v_wh_location, v_qty);
	EXIT stock;
	EXCEPTION WHEN UNIQUE_VIOLATION THEN
		/* do nothing */
	END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_tb_outgoing_stock_v2_log_qty() owner to dskim
;

create function idc_tb_outgoing_stock_v2_log_wh() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_date_confirm date;
	v_date_document date;
	v_type integer;
	v_wh_location integer;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code		= OLD.it_code;
		v_date_confirm	= OLD.otst_confirm_date;
		v_date_document	= OLD.otst_document_date;
		v_type			= OLD.otst_type;
		v_wh_location	= OLD.otst_wh_location;
		v_qty			= OLD.otst_qty * -1;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.otst_confirm_date != NEW.otst_confirm_date OR OLD.otst_document_date != NEW.otst_document_date OR OLD.otst_type != NEW.otst_type OR OLD.otst_wh_location != NEW.otst_wh_location) THEN
			RAISE EXCEPTION 'Update of item code, confirm date, document date, type, warehouse location is not allowed';
		END IF;
		v_it_code		= OLD.it_code;
		v_date_confirm	= OLD.otst_confirm_date;
		v_date_document	= OLD.otst_document_date;
		v_type			= OLD.otst_type;
		v_wh_location	= OLD.otst_wh_location;
		v_qty			= NEW.otst_qty - OLD.otst_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code		= NEW.it_code;
		v_date_confirm	= NEW.otst_confirm_date;
		v_date_document	= NEW.otst_document_date;
		v_type			= NEW.otst_type;
		v_wh_location	= NEW.otst_wh_location;
		v_qty			= NEW.otst_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_log_wh SET log_out_qty = log_out_qty + v_qty WHERE it_code = v_it_code AND log_date = v_date_confirm AND log_type = v_type AND log_wh_location = v_wh_location;
	EXIT stock WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_log_wh (it_code, log_date, log_type, log_wh_location, log_out_qty) VALUES(v_it_code, v_date_confirm, v_type, v_wh_location, v_qty);
	EXIT stock;
	EXCEPTION WHEN UNIQUE_VIOLATION THEN
		/* do nothing */
	END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_tb_outgoing_stock_v2_log_wh() owner to dskim
;

create function idc_tb_outgoing_stock_v2_stock() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint := 1;
	v_type smallint;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.otst_wh_location;
		v_type		  = OLD.otst_type;
		v_qty		  = -1 * OLD.otst_qty;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.otst_type != NEW.otst_type OR OLD.otst_wh_location != NEW.otst_wh_location) THEN
			RAISE EXCEPTION 'Update of ITEM CODE or TYPE or LOCATION is not allowed';
		END IF;
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.otst_wh_location;
		v_type		  = OLD.otst_type;
		v_qty		  = NEW.otst_qty - OLD.otst_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	  = NEW.it_code;
		v_wh_location = NEW.otst_wh_location;
		v_type		  = NEW.otst_type;
		v_qty		  = NEW.otst_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_stock_v2 SET
			stk_qty = stk_qty - v_qty
		WHERE it_code = v_it_code AND stk_type = v_type AND stk_wh_location = v_wh_location;
		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_stock_v2 (it_code, stk_type, stk_wh_location, stk_qty)
			VALUES(v_it_code, v_type, v_wh_location, -v_qty);
		EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
			END;
		END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_tb_outgoing_stock_v2_stock() owner to dskim
;

create function idc_tb_po_item_only_log_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_date date;
	v_type integer;
	v_wh_location integer;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code		= OLD.it_code;
		v_date			= OLD.poit_document_date;
		v_type			= OLD.poit_type;
		v_wh_location	= OLD.poit_wh_location;
		v_qty			= -1 * OLD.poit_qty;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.poit_document_date != NEW.poit_document_date OR OLD.poit_type != NEW.poit_type OR OLD.poit_wh_location != NEW.poit_wh_location) THEN
			RAISE EXCEPTION 'Update of item/date/type/warehouse location is not allowed';
		END IF;
		v_it_code		= OLD.it_code;
		v_date			= OLD.poit_document_date;
		v_type			= OLD.poit_type;
		v_wh_location	= OLD.poit_wh_location;
		v_qty			= NEW.poit_qty - OLD.poit_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code		= NEW.it_code;
		v_date			= NEW.poit_document_date;
		v_type			= NEW.poit_type;
		v_wh_location	= NEW.poit_wh_location;
		v_qty			= NEW.poit_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_log SET log_in_qty = log_in_qty + v_qty WHERE it_code = v_it_code AND log_date = v_date AND log_type = v_type AND log_wh_location = v_wh_location;
	EXIT stock WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_log (it_code, log_date, log_type, log_wh_location, log_in_qty) VALUES(v_it_code, v_date, v_type, v_wh_location, v_qty);
	EXIT stock;

	EXCEPTION WHEN UNIQUE_VIOLATION THEN
		/* do nothing */
	END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_tb_po_item_only_log_qty() owner to dskim
;

create function idc_tb_po_item_only_log_wh() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_date date;
	v_type integer;
	v_wh_location integer;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code		= OLD.it_code;
		v_date			= OLD.poit_confirm_date;
		v_type			= OLD.poit_type;
		v_wh_location	= OLD.poit_wh_location;
		v_qty			= -1 * OLD.poit_qty;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.poit_confirm_date != NEW.poit_confirm_date OR OLD.poit_type != NEW.poit_type OR OLD.poit_wh_location != NEW.poit_wh_location) THEN
			RAISE EXCEPTION 'Update of item/date/type/warehouse location is not allowed';
		END IF;
		v_it_code		= OLD.it_code;
		v_date			= OLD.poit_confirm_date;
		v_type			= OLD.poit_type;
		v_wh_location	= OLD.poit_wh_location;
		v_qty			= NEW.poit_qty - OLD.poit_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code		= NEW.it_code;
		v_date			= NEW.poit_confirm_date;
		v_type			= NEW.poit_type;
		v_wh_location	= NEW.poit_wh_location;
		v_qty			= NEW.poit_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_log_wh SET log_in_qty = log_in_qty + v_qty WHERE it_code = v_it_code AND log_date = v_date AND log_type = v_type AND log_wh_location = v_wh_location;
	EXIT stock WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_log_wh (it_code, log_date, log_type, log_wh_location, log_in_qty) VALUES(v_it_code, v_date, v_type, v_wh_location, v_qty);
	EXIT stock;

	EXCEPTION WHEN UNIQUE_VIOLATION THEN
		/* do nothing */
	END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_tb_po_item_only_log_wh() owner to dskim
;

create function idc_tb_po_item_only_stock() returns trigger
	language plpgsql
as $$
DECLARE
	v_wh_location integer;
	v_type integer;
	v_it_code varchar;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_wh_location	= OLD.poit_wh_location;
		v_type			= OLD.poit_type;
		v_it_code		= OLD.it_code;
		v_qty			= -1 * OLD.poit_qty;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.poit_type != NEW.poit_type OR OLD.poit_wh_location != NEW.poit_wh_location) THEN
			RAISE EXCEPTION 'Update of item code or type or location is not allowed';
		END IF;
		v_wh_location	= OLD.poit_wh_location;
		v_type			= OLD.poit_type;
		v_it_code		= OLD.it_code;
		v_qty			= NEW.poit_qty - OLD.poit_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_wh_location	= NEW.poit_wh_location;
		v_type			= NEW.poit_type;
		v_it_code		= NEW.it_code;
		v_qty			= NEW.poit_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_stock_v2 SET stk_qty = stk_qty + v_qty WHERE stk_type = v_type AND it_code = v_it_code AND stk_wh_location = v_wh_location;
	EXIT stock WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_stock_v2 (stk_type, stk_wh_location, it_code, stk_qty) VALUES(v_type, v_wh_location, v_it_code, v_qty);
	EXIT stock;

	EXCEPTION WHEN UNIQUE_VIOLATION THEN
		/* do nothing */
	END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_tb_po_item_only_stock() owner to dskim
;

create function idc_tb_reject_ed_stock() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint;
	v_type smallint;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.rjed_wh_location;
		v_type		  = OLD.rjed_type;
		v_qty		  = -1 * OLD.rjed_qty;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.rjed_type != NEW.rjed_type OR OLD.rjed_wh_location != NEW.rjed_wh_location) THEN
			RAISE EXCEPTION 'Update of ITEM CODE or ITEM TYPE is not allowed';
		END IF;
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.rjed_wh_location;
		v_type		  = OLD.rjed_type;
		v_qty		  = NEW.rjed_qty - OLD.rjed_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	  = NEW.it_code;
		v_wh_location = NEW.rjed_wh_location;
		v_type		  = NEW.rjed_type;
		v_qty		  = NEW.rjed_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_stock_v2 SET
			stk_qty = stk_qty - v_qty
		WHERE it_code = v_it_code AND stk_type = v_type AND stk_wh_location = v_wh_location;
		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_stock_v2 (it_code, stk_type, stk_wh_location, stk_qty)
			VALUES(v_it_code, v_type, v_wh_location, -v_qty);
		EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
			END;
		END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_tb_reject_ed_stock() owner to dskim
;

create function idc_tb_reject_ed_stock_ed() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint;
	v_date date;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.rjed_wh_location;
		v_date		  = OLD.rjed_expired_date;
		v_qty		  = -1 * OLD.rjed_qty;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.rjed_wh_location != NEW.rjed_wh_location OR OLD.rjed_expired_date != NEW.rjed_expired_date) THEN
			RAISE EXCEPTION 'Update of ITEM CODE or ITEM TYPE is not allowed';
		END IF;
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.rjed_wh_location;
		v_date		  = OLD.rjed_expired_date;
		v_qty		  = NEW.rjed_qty - OLD.rjed_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	  = NEW.it_code;
		v_wh_location = NEW.rjed_wh_location;
		v_date		  = NEW.rjed_expired_date;
		v_qty		  = NEW.rjed_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_stock_ed SET
			sted_qty = sted_qty - v_qty
		WHERE it_code = v_it_code AND sted_expired_date = v_date AND sted_wh_location = v_wh_location;
		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_stock_ed (it_code, sted_wh_location, sted_expired_date, sted_qty)
			VALUES(v_it_code, v_wh_location, v_date, -v_qty);
		EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
			END;
		END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_tb_reject_ed_stock_ed() owner to dskim
;

create function idc_tb_reject_item_stock() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_wh_location smallint;
	v_type smallint;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.rjit_wh_location;
		v_type		  = OLD.rjit_type;
		v_qty		  = -1;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.rjit_type != NEW.rjit_type OR OLD.rjit_wh_location != NEW.rjit_wh_location) THEN
			RAISE EXCEPTION 'Update of ITEM CODE or ITEM TYPE is not allowed';
		END IF;
		v_it_code	  = OLD.it_code;
		v_wh_location = OLD.rjit_wh_location;
		v_type		  = OLD.rjit_type;
		v_qty		  = 0;
	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code	  = NEW.it_code;
		v_wh_location = NEW.rjit_wh_location;
		v_type		  = NEW.rjit_type;
		v_qty		  = 1;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_stock_v2 SET
			stk_qty = stk_qty - v_qty
		WHERE it_code = v_it_code AND stk_type = v_type AND stk_wh_location = v_wh_location;
		EXIT stock WHEN FOUND;

		BEGIN
			INSERT INTO idc_tb_stock_v2 (it_code, stk_type, stk_wh_location, stk_qty)
			VALUES(v_it_code, v_type, v_wh_location, -v_qty);
		EXIT stock;

		EXCEPTION
			WHEN UNIQUE_VIOLATION THEN
				/* do nothing */
			END;
		END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_tb_reject_item_stock() owner to dskim
;

create function idc_tb_return_item_only_log_qty() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_date date;
	v_type integer;
	v_wh_location integer;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code		= OLD.it_code;
		v_date			= OLD.reit_document_date;
		v_type			= OLD.reit_type;
		v_wh_location	= OLD.reit_wh_location;
		v_qty			= -1 * OLD.reit_qty;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.reit_document_date != NEW.reit_document_date OR OLD.reit_type != NEW.reit_type OR OLD.reit_wh_location != NEW.reit_wh_location) THEN
			RAISE EXCEPTION 'Update of item/date/type/warehouse location is not allowed';
		END IF;
		v_it_code		= OLD.it_code;
		v_date			= OLD.reit_document_date;
		v_type			= OLD.reit_type;
		v_wh_location	= OLD.reit_wh_location;
		v_qty			= NEW.reit_qty - OLD.reit_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code		= NEW.it_code;
		v_date			= NEW.reit_document_date;
		v_type			= NEW.reit_type;
		v_wh_location	= NEW.reit_wh_location;
		v_qty			= NEW.reit_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_log SET log_in_qty = log_in_qty + v_qty WHERE it_code = v_it_code AND log_date = v_date AND log_type = v_type AND log_wh_location = v_wh_location;
	EXIT stock WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_log (it_code, log_date, log_type, log_wh_location, log_in_qty) VALUES(v_it_code, v_date, v_type, v_wh_location, v_qty);
	EXIT stock;

	EXCEPTION WHEN UNIQUE_VIOLATION THEN
		/* do nothing */
	END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_tb_return_item_only_log_qty() owner to dskim
;

create function idc_tb_return_item_only_log_wh() returns trigger
	language plpgsql
as $$
DECLARE
	v_it_code varchar;
	v_date date;
	v_type integer;
	v_wh_location integer;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_it_code		= OLD.it_code;
		v_date			= OLD.reit_confirm_date;
		v_type			= OLD.reit_type;
		v_wh_location	= OLD.reit_wh_location;
		v_qty			= -1 * OLD.reit_qty;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.reit_confirm_date != NEW.reit_confirm_date OR OLD.reit_type != NEW.reit_type OR OLD.reit_wh_location != NEW.reit_wh_location) THEN
			RAISE EXCEPTION 'Update of item/date/type/warehouse location is not allowed';
		END IF;
		v_it_code		= OLD.it_code;
		v_date			= OLD.reit_confirm_date;
		v_type			= OLD.reit_type;
		v_wh_location	= OLD.reit_wh_location;
		v_qty			= NEW.reit_qty - OLD.reit_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_it_code		= NEW.it_code;
		v_date			= NEW.reit_confirm_date;
		v_type			= NEW.reit_type;
		v_wh_location	= NEW.reit_wh_location;
		v_qty			= NEW.reit_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_log_wh SET log_in_qty = log_in_qty + v_qty WHERE it_code = v_it_code AND log_date = v_date AND log_type = v_type AND log_wh_location = v_wh_location;
	EXIT stock WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_log_wh (it_code, log_date, log_type, log_wh_location, log_in_qty) VALUES(v_it_code, v_date, v_type, v_wh_location, v_qty);
	EXIT stock;

	EXCEPTION WHEN UNIQUE_VIOLATION THEN
		/* do nothing */
	END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_tb_return_item_only_log_wh() owner to dskim
;

create function idc_tb_return_item_only_stock() returns trigger
	language plpgsql
as $$
DECLARE
	v_wh_location integer;
	v_type integer;
	v_it_code varchar;
	v_qty numeric := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_wh_location	= OLD.reit_wh_location;
		v_type			= OLD.reit_type;
		v_it_code		= OLD.it_code;
		v_qty			= -1 * OLD.reit_qty;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.it_code != NEW.it_code OR OLD.reit_type != NEW.reit_type OR OLD.reit_wh_location != NEW.reit_wh_location) THEN
			RAISE EXCEPTION 'Update of item code or type or location is not allowed';
		END IF;
		v_wh_location	= OLD.reit_wh_location;
		v_type			= OLD.reit_type;
		v_it_code		= OLD.it_code;
		v_qty			= NEW.reit_qty - OLD.reit_qty;
	ELSIF (TG_OP = 'INSERT') THEN
		v_wh_location	= NEW.reit_wh_location;
		v_type			= NEW.reit_type;
		v_it_code		= NEW.it_code;
		v_qty			= NEW.reit_qty;
	END IF;

	/* Insert or update the summary row wirh the new values */
	<<stock>>
	LOOP
		UPDATE idc_tb_stock_v2 SET stk_qty = stk_qty + v_qty WHERE stk_type = v_type AND it_code = v_it_code AND stk_wh_location = v_wh_location;
	EXIT stock WHEN FOUND;

	BEGIN
		INSERT INTO idc_tb_stock_v2 (stk_type, stk_wh_location, it_code, stk_qty) VALUES(v_type, v_wh_location, v_it_code, v_qty);
	EXIT stock;

	EXCEPTION WHEN UNIQUE_VIOLATION THEN
		/* do nothing */
	END;
	END LOOP stock;

	RETURN NULL;
END;
$$
;

alter function idc_tb_return_item_only_stock() owner to dskim
;

create function idc_uncfmbillingonly(v_code character varying) returns void
	language plpgsql
as $$
BEGIN

	UPDATE idc_tb_billing SET
		bill_cfm_wh_delivery_by_account = '',
		bill_cfm_wh_delivery_timestamp = null,
		bill_cfm_wh_date = null
	WHERE bill_code = v_code;

	DELETE FROM idc_tb_billing_item_only WHERE bill_code = v_code;
	DELETE FROM idc_tb_log_detail WHERE log_document_no = v_code AND log_document_type = 'Move Type (Billing No Only)';

END;
$$
;

alter function idc_uncfmbillingonly(varchar) owner to dskim
;

create function idc_uncfmdeliverycharge(v_code character varying, v_is_confirm integer, v_delivery_charge numeric, v_cfm_delivery_by character varying) returns void
	language plpgsql
as $$
DECLARE
	v_delivery_freight_charge numeric;
	v_remainder numeric := 0;
BEGIN

	SELECT INTO v_delivery_freight_charge bill_delivery_freight_charge FROM idc_tb_billing WHERE bill_code = v_code;

	IF v_delivery_freight_charge > v_delivery_charge THEN
		v_remainder = -(v_delivery_freight_charge - v_delivery_charge);
	ELSIF v_delivery_freight_charge < v_delivery_charge THEN
		v_remainder = v_delivery_charge - v_delivery_freight_charge;
	END IF;

	IF v_is_confirm = 1 THEN
		UPDATE idc_tb_billing SET
			bill_delivery_freight_charge = v_delivery_charge,
			bill_cfm_delivery			 = CURRENT_TIMESTAMP,
			bill_cfm_delivery_by		 = v_cfm_delivery_by,
			bill_total_billing			 = bill_total_billing + v_remainder,
			bill_total_billing_rev		 = bill_total_billing_rev + v_remainder,
			bill_remain_amount			 = bill_remain_amount + v_remainder
		WHERE bill_code = v_code;
	ELSIF v_is_confirm = 0 THEN
		UPDATE idc_tb_billing SET
			bill_delivery_freight_charge = v_delivery_charge,
			bill_cfm_delivery			 = null,
			bill_cfm_delivery_by		 = '',
			bill_total_billing			 = bill_total_billing + v_remainder,
			bill_total_billing_rev		 = bill_total_billing_rev + v_remainder,
			bill_remain_amount			 = bill_remain_amount + v_remainder
		WHERE bill_code = v_code;
	END IF;

END;
$$
;

alter function idc_uncfmdeliverycharge(varchar, integer, numeric, varchar) owner to dskim
;

create function idc_uncfmrequestbymarketing(v_type integer, v_doc character varying, v_idx integer) returns void
	language plpgsql
as $$
DECLARE
	v_inde_idx integer;
BEGIN

	IF v_type = 1 THEN
		UPDATE idc_tb_request SET
			req_received_by_account			= null,
			req_received_date				= null,
			req_cfm_marketing_by_account	= null,
			req_cfm_marketing_timestamp		= null
		WHERE req_code = v_doc;
	ELSIF v_type = 2 THEN
		UPDATE idc_tb_incoming_marketing SET
			inm_received_by_account			= null,
			inm_received_date				= null,
			inm_cfm_marketing_by_account	= null,
			inm_cfm_marketing_timestamp		= null
		WHERE inm_idx = v_idx;
	END IF;

	SELECT INTO v_inde_idx inde_idx FROM idc_tb_incoming_demo WHERE inde_doc_type=v_type AND inde_doc_ref=v_doc;
	DELETE FROM idc_tb_incoming_demo WHERE inde_idx = v_inde_idx;
	DELETE FROM idc_tb_incoming_demo_stock WHERE inde_idx = v_inde_idx;
	DELETE FROM idc_tb_incoming_demo_ed WHERE inde_idx = v_inde_idx;

END;
$$
;

alter function idc_uncfmrequestbymarketing(integer, varchar, integer) owner to dskim
;

create function idc_uncfmrequestdemobymarketing(v_code character varying) returns void
	language plpgsql
as $$
BEGIN

	UPDATE idc_tb_using_demo SET
		use_confirm_by_account			= null,
		use_cfm_marketing_by_account	= null,
		use_cfm_marketing_timestamp		= null
	WHERE use_code = v_code;

	DELETE FROM idc_tb_using_demo_stock WHERE use_code = v_code;
	DELETE FROM idc_tb_using_demo_ed WHERE use_code = v_code;

END;
$$
;

alter function idc_uncfmrequestdemobymarketing(varchar) owner to dskim
;

create function idc_uncfmreturnbillingonly(v_code character varying) returns void
	language plpgsql
as $$
BEGIN

	UPDATE idc_tb_return SET
		turn_cfm_wh_by_account	= '',
		turn_cfm_wh_timestamp	= null,
		turn_cfm_wh_date		= null
	WHERE turn_code = v_code;

	DELETE FROM idc_tb_return_item_only WHERE turn_code = v_code;
	DELETE FROM idc_tb_log_detail WHERE log_document_no = v_code AND log_document_type = 'Move Type (Return No Only)';

END;
$$
;

alter function idc_uncfmreturnbillingonly(varchar) owner to dskim
;

create function idc_uncfmreturndemobymarketing(v_code character varying) returns void
	language plpgsql
as $$
BEGIN

	UPDATE idc_tb_return_demo SET
		red_confirm_by_account			= null,
		red_cfm_marketing_by_account	= null,
		red_cfm_marketing_timestamp		= null
	WHERE red_code = v_code;

	DELETE FROM idc_tb_return_demo_stock WHERE red_code = v_code;
	DELETE FROM idc_tb_return_demo_ed WHERE red_code = v_code;

END;
$$
;

alter function idc_uncfmreturndemobymarketing(varchar) owner to dskim
;

create function idc_unconfirmeddeliverystock(v_out_idx integer, v_book_idx integer, v_ref_type integer, v_ref_doc character varying, v_admin_account integer, v_admin_password character varying, v_log_by character varying) returns void
	language plpgsql
as $$
DECLARE
	v_ma_idx integer;
	v_logs_disabled varchar;
	rec record;
	v_do_no varchar;
	v_variable integer;
BEGIN

	SELECT INTO v_ma_idx ma_idx FROM tb_mbracc WHERE ma_idx = v_admin_account AND ma_password = v_admin_password;

	IF NOT FOUND THEN
		RAISE EXCEPTION 'FAIL_TO_AUTH';
	END IF;

	/* update status idc_tb_stock_logs */
	FOR rec IN SELECT it_code, log_code, log_document_type, log_document_no FROM idc_tb_stock_logs WHERE log_document_type in(16,21,22,23,24,25,26) LOOP
		IF rec.log_document_type in (16,25,21) THEN
			v_do_no := 'D' || substr(v_ref_doc,2);
		ELSE
			v_do_no	:= v_ref_doc;
		END IF;

		SELECT INTO v_logs_disabled idc_getLastStockLogs(rec.log_document_type,null,v_do_no,rec.it_code);


		IF v_logs_disabled IS NOT NULL THEN
			UPDATE idc_tb_stock_logs SET
				log_qty_status			= false,
				log_uncfm_by_account	= v_log_by,
				log_uncfm_timestamp		= current_timestamp
			WHERE it_code = rec.it_code AND log_code = v_logs_disabled;
		END IF;
	END LOOP;

	/* delete related table */
	DELETE FROM idc_tb_borrow WHERE out_idx = v_out_idx;
	DELETE FROM idc_tb_borrow_ed WHERE out_idx = v_out_idx;
	DELETE FROM idc_tb_outgoing WHERE out_idx = v_out_idx;

	/* Update related table */
	SELECT INTO v_variable idc_updateRelatedTables(false, v_book_idx, v_ref_doc, v_ref_type, v_log_by, null);

END;
$$
;

alter function idc_unconfirmeddeliverystock(integer, integer, integer, varchar, integer, varchar, varchar) owner to dskim
;

create function idc_unconfirmedinitialstock(v_it_code character varying, v_location integer, v_type integer, v_remark character varying, v_log_by character varying) returns void
	language plpgsql
as $$
BEGIN

	/* update status tb_stock_logs */
	UPDATE idc_tb_stock_logs SET
		log_qty_status			= false,
		log_uncfm_by_account	= v_log_by,
		log_uncfm_timestamp		= current_timestamp,
		log_remark				= v_remark
	WHERE it_code = v_it_code AND log_wh_location = v_location AND log_type = v_type AND log_uncfm_timestamp IS NULL;

	/* delete related table */
	DELETE FROM idc_tb_initial_indocore_stock WHERE it_code = v_it_code AND ist_wh_location = v_location AND ist_type = v_type;
	DELETE FROM idc_tb_expired_initial WHERE it_code = v_it_code AND eni_wh_location = v_location AND eni_type = v_type;

END;
$$
;

alter function idc_unconfirmedinitialstock(varchar, integer, integer, varchar, varchar) owner to dskim
;

create function idc_unconfirmedpo(v_code character varying, v_type character varying) returns void
	language plpgsql
as $$
DECLARE
BEGIN

	IF v_type = 'PO Import' THEN

		UPDATE idc_tb_po SET
			po_confirmed_by_account = '',
			po_confirmed_timestamp  = null
		WHERE po_code = v_code;

		DELETE FROM idc_tb_po_recap WHERE rcp_po_code = v_code;
		DELETE FROM idc_tb_po_item_only WHERE po_code = v_code;
		DELETE FROM idc_tb_log_detail WHERE trim(log_document_no) = trim(v_code) AND log_document_type = 'Move Type (PO)';

	ELSIF v_type = 'PO Local' THEN

		UPDATE idc_tb_po_local SET
			po_confirmed_by_account = '',
			po_confirmed_timestamp  = null
		WHERE po_code = v_code;

 	END IF;

END;
$$
;

alter function idc_unconfirmedpo(varchar, varchar) owner to dskim
;

create function idc_unconfirmedreturn(v_inc_idx integer, v_std_idx integer, v_doc_type integer, v_doc_ref character varying, v_admin_account integer, v_admin_password character varying, v_log_by character varying) returns void
	language plpgsql
as $$
DECLARE
	v_ma_idx integer;
	v_logs_disabled varchar;
	rec record;
BEGIN

	SELECT INTO v_ma_idx ma_idx FROM idc_tb_mbracc WHERE ma_idx = v_admin_account AND ma_password = v_admin_password;

	IF NOT FOUND THEN
		RAISE EXCEPTION 'FAIL_TO_AUTH';
	END IF;

	/* update status idc_tb_stock_logs */
	FOR rec IN SELECT it_code, log_code, log_document_type, log_document_no FROM idc_tb_stock_logs WHERE log_document_type in(13,15) LOOP
		SELECT INTO v_logs_disabled idc_getLastStockLogs(rec.log_document_type,null,v_doc_ref,rec.it_code);

		IF v_logs_disabled IS NOT NULL THEN
			UPDATE idc_tb_stock_logs SET
				log_qty_status			= false,
				log_uncfm_by_account	= v_log_by,
				log_uncfm_timestamp		= current_timestamp
			WHERE it_code = rec.it_code AND log_code = v_logs_disabled;
		END IF;
	END LOOP;

	IF v_doc_type = 1 THEN
		/* Update data in idc_tb_return */
		UPDATE idc_tb_return SET
			turn_cfm_wh_delivery_by_account	= '',
			turn_cfm_wh_delivery_timestamp	= NULL
		WHERE turn_code = v_doc_ref;
	ELSIF v_doc_type = 2 THEN
		/* Update data in idc_tb_return_order */
		UPDATE idc_tb_return_order SET
			reor_cfm_wh_delivery_by_account	= '',
			reor_cfm_wh_delivery_timestamp	= NULL
		WHERE reor_code = v_doc_ref;
	ELSIF v_doc_type = 3 THEN
		/* Update data in idc_tb_return_dt */
		UPDATE idc_tb_return_dt SET
			rdt_cfm_wh_delivery_by_account	= '',
			rdt_cfm_wh_delivery_timestamp	= NULL
		WHERE rdt_code = v_doc_ref;
	END IF;

	/* Update data in idc_tb_outstanding */
	UPDATE idc_tb_outstanding SET
		std_is_confirmed		= false,
		std_revision_time		= std_revision_time+1,
		std_last_cancelled_by	= v_log_by,
		std_last_cancelled_timestamp	= CURRENT_TIMESTAMP
	WHERE std_idx = v_std_idx;

	/* Update data in idc_tb_incoming */
	UPDATE idc_tb_incoming SET
		inc_is_confirmed		 = false,
		inc_confirmed_by_account = '',
		inc_confirmed_timestamp	 = null,
		inc_remark				 = ''
	WHERE inc_idx = v_inc_idx;

	/* Update data in idc_tb_incoming_item */
	UPDATE idc_tb_incoming_item SET
		init_stock_qty	 = 0,
		init_demo_qty 	 = 0,
		init_reject_qty	 = 0,
		init_wh_location = 1
	WHERE inc_idx = v_inc_idx;

	DELETE FROM idc_tb_incoming_stock WHERE inc_idx = v_inc_idx;
	DELETE FROM idc_tb_incoming_stock_ed WHERE inc_idx = v_inc_idx;
	DELETE FROM idc_tb_incoming_ed_demo WHERE inc_idx = v_inc_idx;
	DELETE FROM idc_tb_reject WHERE rjt_doc_idx = v_inc_idx AND rjt_doc_type = 1;
	DELETE FROM idc_tb_incoming_marketing WHERE inc_idx = v_inc_idx;

END;
$$
;

alter function idc_unconfirmedreturn(integer, integer, integer, varchar, integer, varchar, varchar) owner to dskim
;

create function idc_update_deposit() returns trigger
	language plpgsql
as $$
DECLARE
	v_cus_code varchar;
	v_delta numeric := 0;
	v_total_amount numeric;
	v_row_count integer := 0;
BEGIN

	IF(TG_OP = 'DELETE') THEN
		v_cus_code= OLD.cus_code;
		v_delta= -1 * OLD.dep_amount;

	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.cus_code != NEW.cus_code) THEN
			RAISE EXCEPTION 'Update of cus code is not allowed';
		END IF;
		v_cus_code= OLD.cus_code;
		v_delta= NEW.dep_amount - OLD.dep_amount;

	ELSIF (TG_OP = 'INSERT') THEN
		v_cus_code= NEW.cus_code;
		v_delta= NEW.dep_amount;
	END IF;

	SELECT INTO v_total_amount deg_amount FROM idc_tb_deposit_group WHERE cus_code = v_cus_code;

	-- Insert or update the summary row with the new values
	GET DIAGNOSTICS v_row_count := ROW_COUNT;
	IF v_row_count >= 1 THEN
		UPDATE idc_tb_deposit_group SET
			deg_amount = deg_amount + v_delta
		WHERE cus_code = v_cus_code;
	ELSE
		INSERT INTO idc_tb_deposit_group(cus_code, deg_amount)
		VALUES (v_cus_code, v_delta);
	END IF;

	RETURN NULL;
END;
$$
;

alter function idc_update_deposit() owner to dskim
;

create function idc_update_remain_billing() returns trigger
	language plpgsql
as $$
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
		v_payment_date = NEW.pay_date;
	END IF;

	-- Insert or update the summary row wirh the new values
	UPDATE idc_tb_billing SET
		bill_remain_amount = bill_remain_amount - v_delta,
		bill_last_payment_date = v_payment_date
	WHERE bill_code = v_bill_code;

	RETURN NULL;
END;
$$
;

alter function idc_update_remain_billing() owner to dskim
;

create function idc_update_remain_service_billing() returns trigger
	language plpgsql
as $$
DECLARE
	v_sv_code varchar;
	v_delta numeric := 0;
	v_date date := null;
BEGIN
	IF(TG_OP = 'DELETE') THEN
		v_sv_code		= OLD.sv_code;
		v_delta			= -1 * OLD.svpay_paid;
		v_date			= null;

	ELSIF (TG_OP = 'UPDATE') THEN
		IF(OLD.sv_code != NEW.sv_code) THEN
			RAISE EXCEPTION 'Update of service code is not allowed';
		END IF;

		v_sv_code		= OLD.sv_code;
		v_delta			= NEW.svpay_paid - OLD.svpay_paid;
		v_date			= CURRENT_DATE;

	ELSIF (TG_OP = 'INSERT') THEN
		v_sv_code		= NEW.sv_code;
		v_delta			= NEW.svpay_paid;
		v_date			= NEW.svpay_date;
	END IF;

	/* Insert or update the summary row with the new values */
	UPDATE idc_tb_service SET
		sv_total_remain			= sv_total_remain - v_delta,
		sv_last_payment_date	= v_date
	WHERE sv_code = v_sv_code;

	RETURN NULL;
END;
$$
;

alter function idc_update_remain_service_billing() owner to dskim
;

create function idc_updateaccountpolicy(v_num_acc integer, v_account_block integer, v_pwd_valid_period integer) returns void
	language plpgsql
as $$
BEGIN
	UPDATE idc_tb_policy SET
		pl_opt1 = v_num_acc,
		pl_opt2 = v_account_block,
		pl_opt4 = v_pwd_valid_period
	WHERE
		pl_no = 154;
END;
$$
;

alter function idc_updateaccountpolicy(integer, integer, integer) owner to dskim
;

create function idc_updateapotikprice(v_idx integer, v_is_dirty_item boolean, v_cus_code character varying, v_desc character varying, v_basic_disc_pct numeric, v_disc_pct numeric, v_is_valid boolean, v_is_apply_all boolean, v_date_from date, v_date_to date, v_remark character varying, v_it_code character varying[], v_updated_by character varying) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_row_count integer := 0;
	v_row_count2 integer := 0;
	v_policy_idx integer;
BEGIN

	-- Check the duplicated period
	SELECT INTO v_policy_idx ap_idx FROM idc_tb_apotik_policy
	WHERE cus_code = v_cus_code AND ap_idx != v_idx AND (ap_date_from, ap_date_to + 1) OVERLAPS (v_date_from, v_date_to + 1);

	GET DIAGNOSTICS v_row_count := ROW_COUNT;
	IF v_row_count >= 1 THEN
		-- Check the duplicated disc
		SELECT INTO v_policy_idx ap_idx FROM idc_tb_apotik_policy
		WHERE cus_code = v_cus_code AND ap_idx != v_idx AND (ap_date_from, ap_date_to + 1) OVERLAPS (v_date_from, v_date_to + 1) AND ap_disc_pct = v_disc_pct;

		GET DIAGNOSTICS v_row_count2 := ROW_COUNT;
		IF v_row_count2 >= 1 THEN
			RAISE EXCEPTION 'PERIOD_DUPLICATED_BY_POLICY_%_ITEM_%', v_policy_idx, v_it_code[v_i];
		ELSE
			UPDATE idc_tb_apotik_policy SET
				ap_desc = v_desc,
				ap_is_valid = v_is_valid,
				ap_is_apply_all = v_is_apply_all,
				ap_date_from = v_date_from,
				ap_date_to = v_date_to,
				ap_disc_pct = v_disc_pct,
				ap_remark = v_remark,
				ap_updated = CURRENT_TIMESTAMP,
				ap_updated_by = v_updated_by
			WHERE ap_idx = v_idx;
		END IF;
	ELSE
		UPDATE idc_tb_apotik_policy SET
			ap_desc = v_desc,
			ap_is_valid = v_is_valid,
			ap_is_apply_all = v_is_apply_all,
			ap_date_from = v_date_from,
			ap_date_to = v_date_to,
			ap_disc_pct = v_disc_pct,
			ap_remark = v_remark,
			ap_updated = CURRENT_TIMESTAMP,
			ap_updated_by = v_updated_by
		WHERE ap_idx = v_idx;
	END IF;

	IF v_is_apply_all IS TRUE THEN
		DELETE FROM idc_tb_apotik_price WHERE ap_idx = v_idx;
	ELSIF v_is_dirty_item IS TRUE THEN
		DELETE FROM idc_tb_apotik_price WHERE ap_idx = v_idx;
		WHILE v_it_code[v_i] IS NOT NULL LOOP
			INSERT INTO idc_tb_apotik_price(ap_idx, it_code) VALUES(v_idx, v_it_code[v_i]);
			v_i := v_i + 1;
		END LOOP;
	END IF;
END;
$$
;

alter function idc_updateapotikprice(integer, boolean, varchar, varchar, numeric, numeric, boolean, boolean, date, date, varchar, character varying[], varchar) owner to dskim
;

create function idc_updatebilling(v_code character varying, v_type_bill integer, v_type_invoice integer, v_type_template integer, v_book_idx integer, v_dept character varying, v_revision_time integer, v_lastupdated_by_account character varying, v_received_by character varying, v_ship_to_responsible_by integer, v_inv_date date, v_do_no character varying, v_do_date character varying, v_sj_code character varying, v_sj_date date, v_po_no character varying, v_po_date character varying, v_cus_to character varying, v_cus_name character varying, v_cus_attn character varying, v_cus_npwp character varying, v_cus_address character varying, v_ship_to character varying, v_ship_name character varying, v_pajak_to character varying, v_pajak_name character varying, v_pajak_address character varying, v_disc numeric, v_total_amount numeric, v_amount_before_vat numeric, v_delivery_chk integer, v_delivery_by character varying, v_delivery_warehouse character varying, v_delivery_franco character varying, v_delivery_freight_charge numeric, v_payment_chk integer, v_payment_widthin_days integer, v_payment_sj_inv_fp_tender character varying, v_payment_closing_on character varying, v_payment_for_the_month_week character varying, v_payment_cash_by character varying, v_payment_check_by character varying, v_payment_transfer_by character varying, v_payment_giro_due character varying, v_payment_giro_issue character varying, v_bank character varying, v_bank_address character varying, v_tukar_faktur_date character varying, v_signature_by character varying, v_signature_pajak_by character varying, v_paper_format character varying, v_is_cons boolean, v_sales_from character varying, v_sales_to character varying, v_remark character varying, v_note character varying, v_wh_it_code character varying[], v_wh_it_code_for character varying[], v_wh_it_qty numeric[], v_wh_it_function numeric[], v_wh_it_remark character varying[], v_sl_date character varying[], v_sl_cus_code character varying[], v_sl_cus_name character varying[], v_sl_faktur_no character varying[], v_sl_lop_no character varying[], v_sl_amount numeric[], v_cus_it_code character varying[], v_cus_it_model_no character varying[], v_cus_it_desc character varying[], v_cus_it_qty integer[], v_cus_it_unit_price numeric[], v_cus_it_remark character varying[], v_cus_it_sl_idx character varying[]) returns integer
	language plpgsql
as $$
DECLARE
    v_i integer := 1;
    v_j integer := 1;
    v_k integer := 1;
    v_type integer;
    v_remain_amount numeric := 0;
    v_paid_amount numeric := 0;
    v_cur_book_idx integer := 0;
    v_do_date_adj date;
    v_po_date_adj date;
    v_payment_closing_on_adj date;
    v_payment_giro_due_adj date;
    v_payment_giro_issue_adj date;
    v_tukar_faktur_date_adj date;
    v_sales_from_adj date;
    v_sales_to_adj date;
    v_icat_midx integer;
    v_it_type varchar;
BEGIN

    -- Set variable
    SELECT INTO v_paid_amount idc_getTotalPaid(v_code);

    if v_paid_amount is null then   v_remain_amount = v_total_amount;
    else                            v_remain_amount = v_total_amount - v_paid_amount; end if;

    if substr(v_code,1,2) != 'IO' then  v_type := 2;
    else                                v_type := 1; end if;

    -- Adjustment variable
    if v_do_date is null or v_do_date = ''
        then v_do_date_adj=null;
        else v_do_date_adj=v_do_date;end if;
    if v_po_date is null or v_po_date = ''
        then v_po_date_adj=null;
        else v_po_date_adj=v_po_date;end if;
    if v_payment_closing_on is null or v_payment_closing_on = ''
        then v_payment_closing_on_adj=null;
        else v_payment_closing_on_adj=v_payment_closing_on;end if;
    if v_payment_giro_due is null or v_payment_giro_due = ''
        then v_payment_giro_due_adj=null;
        else v_payment_giro_due_adj=v_payment_giro_due;end if;
    if v_payment_giro_issue is null or v_payment_giro_issue = ''
        then v_payment_giro_issue_adj=null;
        else v_payment_giro_issue_adj=v_payment_giro_issue;end if;
    if v_tukar_faktur_date is null or v_tukar_faktur_date = ''
        then v_tukar_faktur_date_adj=null;
        else v_tukar_faktur_date_adj=v_tukar_faktur_date;end if;
    if v_sales_from is null or v_sales_from = ''
        then v_sales_from_adj=null;
        else v_sales_from_adj=v_sales_from;end if;
    if v_sales_to is null or v_sales_to = ''
        then v_sales_to_adj=null;
        else v_sales_to_adj=v_sales_to;end if;

    -- Update idc_tb_billing
    UPDATE idc_tb_billing SET
        bill_inv_date               = v_inv_date,
        bill_sj_code                = v_sj_code,
        bill_sj_date                = v_sj_date,
        bill_do_date                = v_do_date_adj,
        bill_po_no                  = v_po_no,
        bill_po_date                = v_po_date_adj,
        bill_received_by            = v_received_by,
        bill_responsible_by         = v_ship_to_responsible_by,
        bill_revesion_time          = v_revision_time + 1,
        bill_cus_to                 = v_cus_to,
        bill_cus_to_name            = v_cus_name,
        bill_cus_to_attn            = v_cus_attn,
        bill_cus_to_address         = v_cus_address,
        bill_npwp                   = v_cus_npwp,
        bill_ship_to                = v_ship_to,
        bill_ship_to_name           = v_ship_name,
        bill_pajak_to               = v_pajak_to,
        bill_pajak_to_name          = v_pajak_name,
        bill_pajak_to_address       = v_pajak_address,
        bill_delivery_chk           = v_delivery_chk,
        bill_delivery_by            = v_delivery_by,
        bill_delivery_warehouse     = v_delivery_warehouse,
        bill_delivery_franco        = v_delivery_franco,
        bill_delivery_freight_charge    = v_delivery_freight_charge,
        bill_payment_chk                = v_payment_chk,
        bill_payment_widthin_days       = v_payment_widthin_days,
        bill_payment_sj_inv_fp_tender   = v_payment_sj_inv_fp_tender,
        bill_payment_closing_on         = v_payment_closing_on_adj,
        bill_payment_for_the_month_week = v_payment_for_the_month_week,
        bill_payment_cash_by        = v_payment_cash_by,
        bill_payment_check_by       = v_payment_check_by,
        bill_payment_transfer_by    = v_payment_transfer_by,
        bill_payment_giro_due       = v_payment_giro_due_adj,
        bill_payment_giro_issue     = v_payment_giro_issue_adj,
        bill_payment_bank           = v_bank,
        bill_payment_bank_address   = v_bank_address,
        bill_discount               = v_disc,
        bill_total_billing          = v_total_amount,
        bill_total_billing_rev      = v_total_amount,
        bill_remain_amount          = v_remain_amount,
        bill_signature_by           = v_signature_by,
        bill_signature_pajak_by     = v_signature_pajak_by,
        bill_paper_format           = v_paper_format,
        bill_tukar_faktur_date      = v_tukar_faktur_date_adj,
        bill_amount_qty_unit_price  = v_amount_before_vat,
        bill_is_consinyasi          = v_is_cons,
        bill_sales_from             = v_sales_from_adj,
        bill_sales_to               = v_sales_to_adj,
        bill_remark                 = v_remark,
        bill_remark_notes           = v_note,

        bill_lastupdated_by_account = v_lastupdated_by_account,
        bill_lastupdated_timestamp  = CURRENT_TIMESTAMP
    WHERE
        bill_code = v_code;

    -- Update idc_tb_billing_item
    DELETE FROM idc_tb_billing_item WHERE bill_code = v_code;
    WHILE v_cus_it_code[v_i] IS NOT NULL LOOP
        SELECT INTO v_icat_midx icat_midx FROM idc_tb_item WHERE it_code=v_cus_it_code[v_i];
        SELECT INTO v_it_type icat_midx FROM idc_tb_item WHERE it_code=v_cus_it_code[v_i];

        INSERT INTO idc_tb_billing_item (
            bill_code, cus_code, biit_inv_date, biit_qty, biit_unit_price, biit_remark,
            it_code, icat_midx, it_model_no, it_type, it_desc, biit_sl_idx
        ) VALUES (
            v_code, v_cus_to, v_inv_date, v_cus_it_qty[v_i], v_cus_it_unit_price[v_i], v_cus_it_remark[v_i],
            v_cus_it_code[v_i], v_icat_midx, v_cus_it_model_no[v_i], v_it_type, v_cus_it_desc[v_i], v_cus_it_sl_idx[v_i]
        );
        v_i := v_i + 1;
    END LOOP;

    IF v_type_invoice = 0 AND v_book_idx IS NOT NULL AND v_type_template = 1 THEN
        UPDATE idc_tb_booking SET
            cus_code            = v_ship_to,
            book_date           = v_do_date_adj,
            book_received_by    = v_received_by
        WHERE
            book_idx = v_book_Idx;

        -- Update idc_tb_booking_item
        DELETE FROM idc_tb_booking_item WHERE book_idx = v_book_idx;
        WHILE v_wh_it_code[v_j] IS NOT NULL LOOP
            INSERT INTO idc_tb_booking_item (
                book_idx, it_code, boit_it_code_for, boit_type,
                boit_qty, boit_function, boit_remark
            ) VALUES (
                v_book_idx, v_wh_it_code[v_j], v_wh_it_code_for[v_j], 0,
                v_wh_it_qty[v_j], v_wh_it_function[v_j], v_wh_it_remark[v_j]
            );
            v_j := v_j + 1;
        END LOOP;

        v_cur_book_idx := v_book_idx;

    ELSIF v_type_invoice = 0 AND v_book_idx IS NULL THEN
    -- function will execute this one, when move billing from type_invoice 1 to type_invoice 0
        -- Insert idc_tb_booking
        INSERT INTO idc_tb_booking(book_code, book_dept, cus_code, book_doc_ref, book_date, book_doc_type, book_type, book_received_by)
        VALUES (v_do_no, v_dept, v_ship_to, v_code, v_do_date_adj, 1, v_type, v_received_by);

        v_cur_book_idx := currval('idc_tb_booking_book_idx_seq');
        -- Insert idc_tb_booking_item
        WHILE v_wh_it_code[v_j] IS NOT NULL LOOP
            INSERT INTO idc_tb_booking_item (
                book_idx, it_code, boit_it_code_for, boit_type,
                boit_qty, boit_function, boit_remark
            ) VALUES (
                v_cur_book_idx, v_wh_it_code[v_j], v_wh_it_code_for[v_j], 0,
                v_wh_it_qty[v_j], v_wh_it_function[v_j], v_wh_it_remark[v_j]
            );
            v_j := v_j + 1;
        END LOOP;
    END IF;

    IF v_type_bill = 3 THEN
        DELETE FROM idc_tb_billing_sales WHERE bill_code = v_code;
        WHILE v_sl_date[v_k] IS NOT NULL LOOP
            INSERT INTO idc_tb_billing_sales (
                bill_code, bisl_date, cus_code, bisl_sl_faktur_no, bisl_lop_no, bisl_amount
            ) VALUES (
                v_code, v_sl_date[v_k]::date, v_sl_cus_code[v_k], v_sl_faktur_no[v_k], v_sl_lop_no[v_k], v_sl_amount[v_k]
            );
            v_k := v_k + 1;
        END LOOP;
    END IF;

    RETURN v_cur_book_idx;
END;
$$
;

alter function idc_updatebilling(varchar, integer, integer, integer, integer, varchar, integer, varchar, varchar, integer, date, varchar, varchar, varchar, date, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, numeric, numeric, numeric, integer, varchar, varchar, varchar, numeric, integer, integer, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, boolean, varchar, varchar, varchar, varchar, character varying[], character varying[], numeric[], numeric[], character varying[], character varying[], character varying[], character varying[], character varying[], character varying[], numeric[], character varying[], character varying[], character varying[], integer[], numeric[], character varying[], character varying[]) owner to dskim
;

create function idc_updatebillingrevised(v_code character varying, v_type_bill integer, v_type_invoice integer, v_type_template integer, v_book_idx integer, v_dept character varying, v_revision_time integer, v_lastupdated_by_account character varying, v_received_by character varying, v_ship_to_responsible_by integer, v_inv_date date, v_do_no character varying, v_do_date character varying, v_sj_code character varying, v_sj_date date, v_po_no character varying, v_po_date character varying, v_cus_to character varying, v_cus_name character varying, v_cus_attn character varying, v_cus_npwp character varying, v_cus_address character varying, v_ship_to character varying, v_ship_name character varying, v_pajak_to character varying, v_pajak_name character varying, v_pajak_address character varying, v_disc numeric, v_total_amount numeric, v_amount_before_vat numeric, v_delivery_chk integer, v_delivery_by character varying, v_delivery_warehouse character varying, v_delivery_franco character varying, v_delivery_freight_charge numeric, v_payment_chk integer, v_payment_widthin_days integer, v_payment_sj_inv_fp_tender character varying, v_payment_closing_on character varying, v_payment_for_the_month_week character varying, v_payment_cash_by character varying, v_payment_check_by character varying, v_payment_transfer_by character varying, v_payment_giro_due character varying, v_payment_giro_issue character varying, v_bank character varying, v_bank_address character varying, v_tukar_faktur_date character varying, v_signature_by character varying, v_signature_pajak_by character varying, v_paper_format character varying, v_is_cons boolean, v_sales_from character varying, v_sales_to character varying, v_remark character varying, v_wh_it_code character varying[], v_wh_it_code_for character varying[], v_wh_it_qty numeric[], v_wh_it_function numeric[], v_wh_it_remark character varying[], v_sl_date character varying[], v_sl_cus_code character varying[], v_sl_cus_name character varying[], v_sl_faktur_no character varying[], v_sl_lop_no character varying[], v_sl_amount numeric[], v_cus_it_code character varying[], v_cus_it_model_no character varying[], v_cus_it_desc character varying[], v_cus_it_qty integer[], v_cus_it_unit_price numeric[], v_cus_it_remark character varying[], v_cus_it_sl_idx character varying[], v_rcp_it_code character varying[], v_rcp_it_qty numeric[]) returns integer
	language plpgsql
as $$
DECLARE
    v_i integer := 1;
    v_j integer := 1;
    v_k integer := 1;
    v_type integer;
    v_remain_amount numeric := 0;
    v_paid_amount numeric := 0;
    v_cur_book_idx integer := 0;
    v_do_date_adj date;
    v_po_date_adj date;
    v_payment_closing_on_adj date;
    v_payment_giro_due_adj date;
    v_payment_giro_issue_adj date;
    v_tukar_faktur_date_adj date;
    v_sales_from_adj date;
    v_sales_to_adj date;
    v_icat_midx integer;
    v_it_type varchar;
    v_check boolean := false;
BEGIN

    SELECT INTO v_check book_is_revised FROM idc_tb_booking WHERE book_idx = v_book_idx;
    IF v_check THEN
        RAISE EXCEPTION 'REVISED PROCESS ALREADY DONE, PLEASE RE-CHECK %', v_code;
    END IF;

    -- Set variable
    SELECT INTO v_paid_amount idc_getTotalPaid(v_code);

    if v_paid_amount is null then   v_remain_amount = v_total_amount;
    else                            v_remain_amount = v_total_amount - v_paid_amount; end if;

    if substr(v_code,1,2) != 'IO' then  v_type := 2;
    else                                v_type := 1; end if;

    -- Adjustment variable
    if v_do_date is null or v_do_date = ''
        then v_do_date_adj=null;
        else v_do_date_adj=v_do_date;end if;
    if v_po_date is null or v_po_date = ''
        then v_po_date_adj=null;
        else v_po_date_adj=v_po_date;end if;
    if v_payment_closing_on is null or v_payment_closing_on = ''
        then v_payment_closing_on_adj=null;
        else v_payment_closing_on_adj=v_payment_closing_on;end if;
    if v_payment_giro_due is null or v_payment_giro_due = ''
        then v_payment_giro_due_adj=null;
        else v_payment_giro_due_adj=v_payment_giro_due;end if;
    if v_payment_giro_issue is null or v_payment_giro_issue = ''
        then v_payment_giro_issue_adj=null;
        else v_payment_giro_issue_adj=v_payment_giro_issue;end if;
    if v_tukar_faktur_date is null or v_tukar_faktur_date = ''
        then v_tukar_faktur_date_adj=null;
        else v_tukar_faktur_date_adj=v_tukar_faktur_date;end if;
    if v_sales_from is null or v_sales_from = ''
        then v_sales_from_adj=null;
        else v_sales_from_adj=v_sales_from;end if;
    if v_sales_to is null or v_sales_to = ''
        then v_sales_to_adj=null;
        else v_sales_to_adj=v_sales_to;end if;

    -- Update idc_tb_billing
    UPDATE idc_tb_billing SET
        bill_inv_date               = v_inv_date,
        bill_sj_code                = v_sj_code,
        bill_sj_date                = v_sj_date,
        bill_do_date                = v_do_date_adj,
        bill_po_no                  = v_po_no,
        bill_po_date                = v_po_date_adj,
        bill_received_by            = v_received_by,
        bill_responsible_by         = v_ship_to_responsible_by,
        bill_revesion_time          = v_revision_time + 1,
        bill_cus_to                 = v_cus_to,
        bill_cus_to_name            = v_cus_name,
        bill_cus_to_attn            = v_cus_attn,
        bill_cus_to_address         = v_cus_address,
        bill_npwp                   = v_cus_npwp,
        bill_ship_to                = v_ship_to,
        bill_ship_to_name           = v_ship_name,
        bill_pajak_to               = v_pajak_to,
        bill_pajak_to_name          = v_pajak_name,
        bill_pajak_to_address       = v_pajak_address,
        bill_delivery_chk           = v_delivery_chk,
        bill_delivery_by            = v_delivery_by,
        bill_delivery_warehouse     = v_delivery_warehouse,
        bill_delivery_franco        = v_delivery_franco,
        bill_delivery_freight_charge    = v_delivery_freight_charge,
        bill_payment_chk                = v_payment_chk,
        bill_payment_widthin_days       = v_payment_widthin_days,
        bill_payment_sj_inv_fp_tender   = v_payment_sj_inv_fp_tender,
        bill_payment_closing_on         = v_payment_closing_on_adj,
        bill_payment_for_the_month_week = v_payment_for_the_month_week,
        bill_payment_cash_by        = v_payment_cash_by,
        bill_payment_check_by       = v_payment_check_by,
        bill_payment_transfer_by    = v_payment_transfer_by,
        bill_payment_giro_due       = v_payment_giro_due_adj,
        bill_payment_giro_issue     = v_payment_giro_issue_adj,
        bill_payment_bank           = v_bank,
        bill_payment_bank_address   = v_bank_address,
        bill_discount               = v_disc,
        bill_total_billing          = v_total_amount,
        bill_total_billing_rev      = v_total_amount,
        bill_remain_amount          = v_remain_amount,
        bill_signature_by           = v_signature_by,
        bill_signature_pajak_by     = v_signature_pajak_by,
        bill_paper_format           = v_paper_format,
        bill_tukar_faktur_date      = v_tukar_faktur_date_adj,
        bill_amount_qty_unit_price  = v_amount_before_vat,
        bill_is_consinyasi          = v_is_cons,
        bill_sales_from             = v_sales_from_adj,
        bill_sales_to               = v_sales_to_adj,
        bill_remark                 = v_remark,
        bill_lastupdated_by_account = v_lastupdated_by_account,
        bill_lastupdated_timestamp  = CURRENT_TIMESTAMP
    WHERE
        bill_code = v_code;

    -- Update idc_tb_billing_item
    DELETE FROM idc_tb_billing_item WHERE bill_code = v_code;
    WHILE v_cus_it_code[v_i] IS NOT NULL LOOP
        SELECT INTO v_icat_midx icat_midx FROM idc_tb_item WHERE it_code=v_cus_it_code[v_i];
        SELECT INTO v_it_type icat_midx FROM idc_tb_item WHERE it_code=v_cus_it_code[v_i];

        INSERT INTO idc_tb_billing_item (
            bill_code, cus_code, biit_inv_date, biit_qty, biit_unit_price, biit_remark,
            it_code, icat_midx, it_model_no, it_type, it_desc, biit_sl_idx
        ) VALUES (
            v_code, v_cus_to, v_inv_date, v_cus_it_qty[v_i], v_cus_it_unit_price[v_i], v_cus_it_remark[v_i],
            v_cus_it_code[v_i], v_icat_midx, v_cus_it_model_no[v_i], v_it_type, v_cus_it_desc[v_i], v_cus_it_sl_idx[v_i]
        );
        v_i := v_i + 1;
    END LOOP;

    -- Processing update idc_tb_booking, idc_tb_outgoing and status
    UPDATE idc_tb_booking SET
        cus_code            = v_ship_to,
        book_date           = v_inv_date,
        book_received_by    = v_received_by,
        book_is_revised     = true,
        book_is_delivered   = false
    WHERE
        book_idx = v_book_idx;
    UPDATE idc_tb_outgoing_v2 SET
        out_issued_date = v_inv_date,
        out_is_revised = true,
        cus_code = v_ship_to
    WHERE out_doc_ref = trim(v_code);

    -- Processing checking incoming/ outgoing additional item
    WHILE v_rcp_it_code[v_j] IS NOT NULL LOOP
                INSERT INTO idc_tb_booking_revised (book_idx, it_code, boit_qty)
                VALUES (v_book_idx, v_rcp_it_code[v_j], v_rcp_it_qty[v_j]);
        v_j := v_j + 1;
    END LOOP;

    -- Update idc_tb_booking_item
    DELETE FROM idc_tb_booking_item WHERE book_idx = v_book_idx;
    WHILE v_wh_it_code[v_k] IS NOT NULL LOOP
        INSERT INTO idc_tb_booking_item (
            book_idx, it_code, boit_it_code_for, boit_type,
            boit_qty, boit_function, boit_remark
        ) VALUES (
            v_book_idx, v_wh_it_code[v_k], v_wh_it_code_for[v_k], 0,
            v_wh_it_qty[v_k], v_wh_it_function[v_k], v_wh_it_remark[v_k]
        );
        v_k := v_k + 1;

        v_cur_book_idx := v_book_idx;
    END LOOP;

    RETURN v_cur_book_idx;
END;
$$
;

alter function idc_updatebillingrevised(varchar, integer, integer, integer, integer, varchar, integer, varchar, varchar, integer, date, varchar, varchar, varchar, date, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, numeric, numeric, numeric, integer, varchar, varchar, varchar, numeric, integer, integer, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, boolean, varchar, varchar, varchar, character varying[], character varying[], numeric[], numeric[], character varying[], character varying[], character varying[], character varying[], character varying[], character varying[], numeric[], character varying[], character varying[], character varying[], integer[], numeric[], character varying[], character varying[], character varying[], numeric[]) owner to dskim
;

create function idc_updateconfirmedbilling(v_code character varying, v_book_idx integer, v_dept character varying, v_inv_date date, v_sj_code character varying, v_sj_date date, v_do_no character varying, v_do_date date, v_po_no character varying, v_po_date date, v_received_by character varying, v_revesion_time integer, v_cus_to character varying, v_cus_name character varying, v_cus_attn character varying, v_cus_address character varying, v_npwp character varying, v_ship_to character varying, v_ship_name character varying, v_pajak_to character varying, v_pajak_name character varying, v_pajak_address character varying, v_delivery_chk integer, v_delivery_by character varying, v_delivery_warehouse character varying, v_delivery_franco character varying, v_delivery_freight_charge numeric, v_payment_chk integer, v_payment_widthin_days integer, v_payment_sj_inv_fp_tender character varying, v_payment_closing_on date, v_payment_for_the_month_week character varying, v_payment_cash_by character varying, v_payment_check_by character varying, v_payment_transfer_by character varying, v_payment_giro_due date, v_payment_giro_issue date, v_bank character varying, v_bank_address character varying, v_lastupdated_by_account character varying, v_disc numeric, v_total_amount numeric, v_signature_by character varying, v_signature_pajak_by character varying, v_paper_format character varying, v_tukar_faktur_date date, v_amount_before_vat numeric, v_sales_from date, v_sales_to date, v_remark character varying, v_biit_idx integer[], v_it_unit_price integer[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_type integer;
	v_remain_amount numeric := 0;
	v_paid_amount numeric := 0;
BEGIN

	/* Set variable */
	SELECT INTO v_paid_amount idc_getTotalPaid(v_code);

	IF v_paid_amount IS NULL THEN
		v_remain_amount = v_total_amount;
	ELSE
		v_remain_amount = v_total_amount - v_paid_amount;
	END IF;

	IF substr(v_code,1,2) != 'IO' THEN
		v_type := 2;
	ELSE
		v_type := 1;
	END IF;

	/* Update idc_tb_billing */
	UPDATE idc_tb_billing SET
		bill_inv_date				= v_inv_date,
		bill_lastupdated_by_account = v_lastupdated_by_account,
		bill_lastupdated_timestamp	= CURRENT_TIMESTAMP,
		bill_sj_code				= v_sj_code,
		bill_sj_date				= v_sj_date,
		bill_do_date				= v_do_date,
		bill_po_no 					= v_po_no,
		bill_po_date				= v_po_date,
		bill_received_by 			= v_received_by,
		bill_revesion_time 			= v_revesion_time + 1,
		bill_cus_to 				= v_cus_to,
		bill_cus_to_name			= v_cus_name,
		bill_cus_to_attn			= v_cus_attn,
		bill_cus_to_address 		= v_cus_address,
		bill_ship_to				= v_ship_to,
		bill_ship_to_name 			= v_ship_name,
		bill_pajak_to 				= v_pajak_to,
		bill_pajak_to_name			= v_pajak_name,
		bill_pajak_to_address 		= v_pajak_address,
		bill_npwp					= v_npwp,
		bill_delivery_chk 			= v_delivery_chk,
		bill_delivery_by 			= v_delivery_by,
		bill_delivery_warehouse		= v_delivery_warehouse,
		bill_delivery_franco		= v_delivery_franco,
		bill_delivery_freight_charge	= v_delivery_freight_charge,
		bill_payment_chk 				= v_payment_chk,
		bill_payment_widthin_days 		= v_payment_widthin_days,
		bill_payment_sj_inv_fp_tender	= v_payment_sj_inv_fp_tender,
		bill_payment_closing_on			= v_payment_closing_on,
		bill_payment_for_the_month_week	= v_payment_for_the_month_week,
		bill_payment_cash_by 		= v_payment_cash_by,
		bill_payment_check_by 		= v_payment_check_by,
		bill_payment_transfer_by 	= v_payment_transfer_by,
		bill_payment_giro_due 		= v_payment_giro_due,
		bill_payment_giro_issue 	= v_payment_giro_issue,
		bill_payment_bank			= v_bank,
		bill_payment_bank_address	= v_bank_address,
		bill_discount				= v_disc,
		bill_total_billing			= v_total_amount,
		bill_total_billing_rev		= v_total_amount,
		bill_remain_amount			= v_remain_amount,
		bill_signature_by			= v_signature_by,
		bill_signature_pajak_by		= v_signature_pajak_by,
		bill_paper_format			= v_paper_format,
		bill_tukar_faktur_date		= v_tukar_faktur_date,
		bill_amount_qty_unit_price	= v_amount_before_vat,
		bill_sales_from				= v_sales_from,
		bill_sales_to				= v_sales_to,
		bill_remark					= v_remark
	WHERE
		bill_code = v_code;

	WHILE v_biit_idx[v_i] IS NOT NULL LOOP
		UPDATE idc_tb_billing_item SET
			biit_unit_price = v_it_unit_price[v_i]
		WHERE biit_idx = v_biit_idx[v_i];
		v_i := v_i + 1;
	END LOOP;

END;
$$
;

alter function idc_updateconfirmedbilling(varchar, integer, varchar, date, varchar, date, varchar, date, varchar, date, varchar, integer, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, integer, varchar, varchar, varchar, numeric, integer, integer, varchar, date, varchar, varchar, varchar, varchar, date, date, varchar, varchar, varchar, numeric, numeric, varchar, varchar, varchar, date, numeric, date, date, varchar, integer[], integer[]) owner to dskim
;

create function idc_updatecriticalitem(v_it_code character varying[], v_critical_qty numeric[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
BEGIN
	WHILE v_it_code[v_i] IS NOT NULL LOOP
		UPDATE idc_tb_item SET it_critical_stock = v_critical_qty[v_i] WHERE it_code = v_it_code[v_i];
		v_i := v_i + 1;
	END LOOP;
END;
$$
;

alter function idc_updatecriticalitem(character varying[], numeric[]) owner to dskim
;

create function idc_updatecusgroup(v_code character varying, v_name character varying, v_regtime date, v_remark character varying, v_basic_disc_pct numeric) returns void
	language plpgsql
as $$
DECLARE
BEGIN
	UPDATE idc_tb_customer_group SET
		cug_name= v_name,
		cug_regtime= v_regtime,
		cug_remark= v_remark,
		cug_basic_disc_pct = v_basic_disc_pct
	WHERE cug_code= v_code;
END;
$$
;

alter function idc_updatecusgroup(varchar, varchar, date, varchar, numeric) owner to dskim
;

create function idc_updatecustomer(v_code character varying, v_cug_code character varying, v_name character varying, v_full_name character varying, v_channel character varying, v_representative character varying, v_company_title character varying, v_type_of_biz character varying, v_tax_code_status integer, v_since date, v_introduced_by character varying, v_contact character varying, v_contact_position character varying, v_contact_phone character varying, v_contact_hphone character varying, v_contact_email character varying, v_fax character varying, v_city character varying, v_address character varying, v_phone character varying, v_marketing_staff integer, v_remark character varying, v_fp_email character varying) returns void
	language plpgsql
as $$
BEGIN
	UPDATE idc_tb_customer SET
		cus_code			= v_code,
		cug_code			= v_cug_code,
		cus_name			= v_name,
		cus_full_name		= v_full_name,
		cus_channel			= v_channel,
		cus_representative	= v_representative,
		cus_company_title	= v_company_title,
		cus_type_of_biz		= v_type_of_biz,
		cus_tax_code_status	= v_tax_code_status,
		cus_since			= v_since,
		cus_introduced_by	= v_introduced_by,
		cus_contact			= v_contact,
		cus_contact_position= v_contact_position,
		cus_contact_phone	= v_contact_phone,
		cus_contact_hphone	= v_contact_hphone,
		cus_contact_email	= v_contact_email,
		cus_fax				= v_fax,
		cus_city 			= v_city,
		cus_address			= v_address,
		cus_phone			= v_phone,
		cus_responsibility_to = v_marketing_staff,
		cus_remark			= v_remark,
		cus_fp_email		= v_fp_email
	WHERE cus_code = v_code;
END;
$$
;

alter function idc_updatecustomer(varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, integer, date, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, integer, varchar, varchar) owner to dskim
;

create function idc_updatecustomercomplain(v_code integer, v_date date, v_customer character varying, v_category character varying, v_complain_desc character varying, v_action character varying, v_remark character varying, v_updated_by_account character varying) returns void
	language plpgsql
as $$
BEGIN

	UPDATE idc_tb_customer_complain SET
		cp_date					= v_date,
		cp_customer				= v_customer,
		cp_category				= v_category,
		cp_complain_desc		= v_complain_desc,
		cp_complain_completion	= v_action,
		cp_remark				= v_remark,
		cp_updated_by_account	= v_updated_by_account,
		cp_updated_timestamp	= CURRENT_TIMESTAMP
	WHERE cp_idx = v_code;

END;
$$
;

alter function idc_updatecustomercomplain(integer, date, varchar, varchar, varchar, varchar, varchar, varchar) owner to dskim
;

create function idc_updatedeposit(v_code integer, v_cus_name character varying, v_payment_date date, v_payment_paid numeric, v_payment_method character varying, v_payment_bank character varying, v_payment_remark character varying, v_inputed_by_account character varying) returns void
	language plpgsql
as $$
BEGIN
	UPDATE idc_tb_deposit SET
		dep_cus_name= v_cus_name,
		dep_amount= v_payment_paid,
		dep_issued_date = v_payment_date,
		dep_method= v_payment_method,
		dep_bank = v_payment_bank,
		dep_remark = v_payment_remark,
		dep_updated_by_account = v_inputed_by_account,
		dep_updated_timestamp = CURRENT_TIMESTAMP
	WHERE dep_idx = v_code;

END;
$$
;

alter function idc_updatedeposit(integer, varchar, date, numeric, varchar, varchar, varchar, varchar) owner to dskim
;

create function idc_updatedf(v_code character varying, v_dept character varying, v_book_idx numeric, v_do_date date, v_issued_by character varying, v_issued_date date, v_received_by character varying, v_lastupdated_by_account character varying, v_revision_time integer, v_cus_to character varying, v_cus_name character varying, v_cus_address character varying, v_ship_to character varying, v_ship_name character varying, v_delivery_by character varying, v_delivery_warehouse character varying, v_delivery_franco character varying, v_delivery_freight_charge numeric, v_remark character varying, v_wh_it_code character varying[], v_wh_it_qty numeric[], v_wh_it_remark character varying[], v_cus_it_code character varying[], v_cus_it_qty numeric[], v_cus_it_remark character varying[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_j integer := 1;
BEGIN

	/* Update idc_tb_df */
	UPDATE idc_tb_df SET
		df_date						= v_do_date,
		df_issued_by				= v_issued_by,
		df_issued_date				= v_issued_date,
		df_received_by				= v_received_by,
		df_cus_to					= v_cus_to,
		df_cus_name					= v_cus_name,
		df_cus_address				= v_cus_address,
		df_ship_to					= v_ship_to,
		df_ship_name				= v_ship_name,
		df_do_date					= v_do_date,
		df_sj_date					= v_do_date,
		df_lastupdated_by_account	= v_lastupdated_by_account,
		df_lastupdated_timestamp	= CURRENT_TIMESTAMP,
		df_revesion_time			= v_revision_time + 1,
		df_delivery_warehouse		= v_delivery_warehouse,
		df_delivery_franco			= v_delivery_franco,
		df_delivery_by				= v_delivery_by,
		df_delivery_freight_charge	= v_delivery_freight_charge,
		df_remark					= v_remark
	WHERE df_code = v_code;

	/* Delete, insert idc_tb_df_item */
	DELETE FROM idc_tb_df_item WHERE df_code = v_code;
	WHILE v_cus_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_df_item (
			df_code, it_code, dfit_qty, dfit_remark
		) VALUES (
			v_code, v_cus_it_code[v_i], v_cus_it_qty[v_i], v_cus_it_remark[v_i]
		);
		v_i := v_i + 1;
	END LOOP;

	UPDATE idc_tb_booking SET
		cus_code			= v_ship_to,
		book_date			= v_do_date,
		book_received_by	= v_received_by
	WHERE
		book_idx = v_book_idx;

	/* Delete, insert from idc_tb_booking_item */
	DELETE FROM idc_tb_booking_item WHERE book_idx = v_book_idx;
	WHILE v_wh_it_code[v_j] IS NOT NULL LOOP
		INSERT INTO idc_tb_booking_item (
			book_idx, it_code, boit_it_code_for, boit_type,
			boit_qty, boit_function, boit_remark
		) VALUES (
			v_book_idx, v_wh_it_code[v_j], v_wh_it_code[v_j], 0,
			v_wh_it_qty[v_j], 1, v_wh_it_remark[v_j]
		);
		v_j := v_j + 1;
	END LOOP;

END;
$$
;

alter function idc_updatedf(varchar, varchar, numeric, date, varchar, date, varchar, varchar, integer, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, numeric, varchar, character varying[], numeric[], character varying[], character varying[], numeric[], character varying[]) owner to dskim
;

create function idc_updatedfrevised(v_code character varying, v_dept character varying, v_book_idx numeric, v_do_date date, v_issued_by character varying, v_issued_date date, v_received_by character varying, v_lastupdated_by_account character varying, v_revision_time integer, v_cus_to character varying, v_cus_name character varying, v_cus_address character varying, v_ship_to character varying, v_ship_name character varying, v_delivery_by character varying, v_delivery_warehouse character varying, v_delivery_franco character varying, v_delivery_freight_charge numeric, v_remark character varying, v_wh_it_code character varying[], v_wh_it_code_for character varying[], v_wh_it_qty numeric[], v_wh_it_function numeric[], v_wh_it_remark character varying[], v_cus_it_code character varying[], v_cus_it_qty numeric[], v_cus_it_remark character varying[], v_rcp_it_code character varying[], v_rcp_it_qty numeric[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_j integer := 1;
	v_k integer := 1;
BEGIN

	/* Update idc_tb_df */
	UPDATE idc_tb_df SET
		df_date						= v_do_date,
		df_issued_by				= v_issued_by,
		df_issued_date				= v_issued_date,
		df_received_by				= v_received_by,
		df_cus_to					= v_cus_to,
		df_cus_name					= v_cus_name,
		df_cus_address				= v_cus_address,
		df_ship_to					= v_ship_to,
		df_ship_name				= v_ship_name,
		df_do_date					= v_do_date,
		df_sj_date					= v_do_date,
		df_lastupdated_by_account	= v_lastupdated_by_account,
		df_lastupdated_timestamp	= CURRENT_TIMESTAMP,
		df_revesion_time			= v_revision_time + 1,
		df_delivery_warehouse		= v_delivery_warehouse,
		df_delivery_franco			= v_delivery_franco,
		df_delivery_by				= v_delivery_by,
		df_delivery_freight_charge	= v_delivery_freight_charge,
		df_remark					= v_remark
	WHERE df_code = v_code;

	/* Delete, insert idc_tb_df_item */
	DELETE FROM idc_tb_df_item WHERE df_code = v_code;
	WHILE v_cus_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_df_item (
			df_code, it_code, dfit_qty, dfit_remark
		) VALUES (
			v_code, v_cus_it_code[v_i], v_cus_it_qty[v_i], v_cus_it_remark[v_i]
		);
		v_i := v_i + 1;
	END LOOP;

	-- Processing update idc_tb_booking, idc_tb_outgoing and status
	UPDATE idc_tb_booking SET
		cus_code			= v_ship_to,
		book_date			= v_issued_date,
		book_received_by	= v_received_by,
		book_is_revised		= true,
		book_is_delivered	= false
	WHERE
		book_idx = v_book_idx;
	UPDATE idc_tb_outgoing_v2 SET out_issued_date = v_issued_date, out_is_revised = true WHERE out_doc_ref = trim(v_code);


	-- Processing checking incoming/ outgoing additional item
	WHILE v_rcp_it_code[v_j] IS NOT NULL LOOP
				INSERT INTO idc_tb_booking_revised (book_idx, it_code, boit_qty)
				VALUES (v_book_idx, v_rcp_it_code[v_j], v_rcp_it_qty[v_j]);
		v_j := v_j + 1;
	END LOOP;

	-- Update idc_tb_booking_item
	DELETE FROM idc_tb_booking_item WHERE book_idx = v_book_idx;
	WHILE v_wh_it_code[v_k] IS NOT NULL LOOP
		INSERT INTO idc_tb_booking_item (
			book_idx, it_code, boit_it_code_for, boit_type, boit_qty, boit_function, boit_remark
		) VALUES (
			v_book_idx, v_wh_it_code[v_k], v_wh_it_code_for[v_k], 0,
			v_wh_it_qty[v_k], 1, v_wh_it_remark[v_k]
		);
		v_k := v_k + 1;
	END LOOP;

END;
$$
;

alter function idc_updatedfrevised(varchar, varchar, numeric, date, varchar, date, varchar, varchar, integer, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, numeric, varchar, character varying[], character varying[], numeric[], numeric[], character varying[], character varying[], numeric[], character varying[], character varying[], numeric[]) owner to dskim
;

create function idc_updatedr(v_code character varying, v_dept character varying, v_book_idx numeric, v_do_date date, v_issued_by character varying, v_issued_date date, v_received_by character varying, v_lastupdated_by_account character varying, v_revision_time integer, v_cus_to character varying, v_cus_name character varying, v_cus_address character varying, v_ship_to character varying, v_ship_name character varying, v_delivery_by character varying, v_delivery_warehouse character varying, v_delivery_franco character varying, v_delivery_freight_charge numeric, v_remark character varying, v_wh_it_code character varying[], v_wh_it_qty numeric[], v_wh_it_remark character varying[], v_cus_it_code character varying[], v_cus_it_qty numeric[], v_cus_it_remark character varying[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_j integer := 1;
BEGIN

	/* Update idc_tb_dr */
	UPDATE idc_tb_dr SET
		dr_date						= v_do_date,
		dr_issued_by				= v_issued_by,
		dr_issued_date				= v_issued_date,
		dr_received_by				= v_received_by,
		dr_cus_to					= v_cus_to,
		dr_cus_name					= v_cus_name,
		dr_cus_address				= v_cus_address,
		dr_ship_to					= v_ship_to,
		dr_ship_name				= v_ship_name,
		dr_do_date					= v_do_date,
		dr_sj_date					= v_do_date,
		dr_lastupdated_by_account	= v_lastupdated_by_account,
		dr_lastupdated_timestamp	= CURRENT_TIMESTAMP,
		dr_revesion_time			= v_revision_time + 1,
		dr_delivery_warehouse		= v_delivery_warehouse,
		dr_delivery_franco			= v_delivery_franco,
		dr_delivery_by				= v_delivery_by,
		dr_delivery_freight_charge	= v_delivery_freight_charge,
		dr_remark					= v_remark
	WHERE dr_code = v_code;

	/* Delete, insert idc_tb_dr_item */
	DELETE FROM idc_tb_dr_item WHERE dr_code = v_code;
	WHILE v_cus_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_dr_item (
			dr_code, it_code, drit_qty, drit_remark
		) VALUES (
			v_code, v_cus_it_code[v_i], v_cus_it_qty[v_i], v_cus_it_remark[v_i]
		);
		v_i := v_i + 1;
	END LOOP;

	UPDATE idc_tb_booking SET
		cus_code			= v_ship_to,
		book_date			= v_do_date,
		book_received_by	= v_received_by
	WHERE
		book_idx = v_book_idx;

	/* Delete, insert from idc_tb_booking_item */
	DELETE FROM idc_tb_booking_item WHERE book_idx = v_book_idx;
	WHILE v_wh_it_code[v_j] IS NOT NULL LOOP
		INSERT INTO idc_tb_booking_item (
			book_idx, it_code, boit_it_code_for, boit_type,
			boit_qty, boit_function, boit_remark
		) VALUES (
			v_book_idx, v_wh_it_code[v_j], v_wh_it_code[v_j], 0,
			v_wh_it_qty[v_j], 1, v_wh_it_remark[v_j]
		);
		v_j := v_j + 1;
	END LOOP;

END;
$$
;

alter function idc_updatedr(varchar, varchar, numeric, date, varchar, date, varchar, varchar, integer, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, numeric, varchar, character varying[], numeric[], character varying[], character varying[], numeric[], character varying[]) owner to dskim
;

create function idc_updatedrrevised(v_code character varying, v_dept character varying, v_book_idx numeric, v_do_date date, v_issued_by character varying, v_issued_date date, v_received_by character varying, v_lastupdated_by_account character varying, v_revision_time integer, v_cus_to character varying, v_cus_name character varying, v_cus_address character varying, v_ship_to character varying, v_ship_name character varying, v_delivery_by character varying, v_delivery_warehouse character varying, v_delivery_franco character varying, v_delivery_freight_charge numeric, v_remark character varying, v_wh_it_code character varying[], v_wh_it_code_for character varying[], v_wh_it_qty numeric[], v_wh_it_function numeric[], v_wh_it_remark character varying[], v_cus_it_code character varying[], v_cus_it_qty numeric[], v_cus_it_remark character varying[], v_rcp_it_code character varying[], v_rcp_it_qty numeric[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_j integer := 1;
	v_k integer := 1;
BEGIN

	/* Update idc_tb_dr */
	UPDATE idc_tb_dr SET
		dr_date						= v_do_date,
		dr_issued_by				= v_issued_by,
		dr_issued_date				= v_issued_date,
		dr_received_by				= v_received_by,
		dr_cus_to					= v_cus_to,
		dr_cus_name					= v_cus_name,
		dr_cus_address				= v_cus_address,
		dr_ship_to					= v_ship_to,
		dr_ship_name				= v_ship_name,
		dr_do_date					= v_do_date,
		dr_sj_date					= v_do_date,
		dr_lastupdated_by_account	= v_lastupdated_by_account,
		dr_lastupdated_timestamp	= CURRENT_TIMESTAMP,
		dr_revesion_time			= v_revision_time + 1,
		dr_delivery_warehouse		= v_delivery_warehouse,
		dr_delivery_franco			= v_delivery_franco,
		dr_delivery_by				= v_delivery_by,
		dr_delivery_freight_charge	= v_delivery_freight_charge,
		dr_remark					= v_remark
	WHERE dr_code = v_code;

	/* Delete, insert idc_tb_dr_item */
	DELETE FROM idc_tb_dr_item WHERE dr_code = v_code;
	WHILE v_cus_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_dr_item (
			dr_code, it_code, drit_qty, drit_remark
		) VALUES (
			v_code, v_cus_it_code[v_i], v_cus_it_qty[v_i], v_cus_it_remark[v_i]
		);
		v_i := v_i + 1;
	END LOOP;

	-- Processing update idc_tb_booking, idc_tb_outgoing and status
	UPDATE idc_tb_booking SET
		cus_code			= v_ship_to,
		book_date			= v_issued_date,
		book_received_by	= v_received_by,
		book_is_revised		= true,
		book_is_delivered	= false
	WHERE
		book_idx = v_book_idx;
	UPDATE idc_tb_outgoing_v2 SET out_issued_date = v_issued_date, out_is_revised = true WHERE out_doc_ref = trim(v_code);


	-- Processing checking incoming/ outgoing additional item
	WHILE v_rcp_it_code[v_j] IS NOT NULL LOOP
				INSERT INTO idc_tb_booking_revised (book_idx, it_code, boit_qty)
				VALUES (v_book_idx, v_rcp_it_code[v_j], v_rcp_it_qty[v_j]);
		v_j := v_j + 1;
	END LOOP;

	-- Update idc_tb_booking_item
	DELETE FROM idc_tb_booking_item WHERE book_idx = v_book_idx;
	WHILE v_wh_it_code[v_k] IS NOT NULL LOOP
		INSERT INTO idc_tb_booking_item (
			book_idx, it_code, boit_it_code_for, boit_type, boit_qty, boit_function, boit_remark
		) VALUES (
			v_book_idx, v_wh_it_code[v_k], v_wh_it_code_for[v_k], 0,
			v_wh_it_qty[v_k], 1, v_wh_it_remark[v_k]
		);
		v_k := v_k + 1;
	END LOOP;

END;
$$
;

alter function idc_updatedrrevised(varchar, varchar, numeric, date, varchar, date, varchar, varchar, integer, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, numeric, varchar, character varying[], character varying[], numeric[], numeric[], character varying[], character varying[], numeric[], character varying[], character varying[], numeric[]) owner to dskim
;

create function idc_updatedt(v_code character varying, v_dept character varying, v_book_idx numeric, v_do_date date, v_issued_by character varying, v_issued_date date, v_received_by character varying, v_lastupdated_by_account character varying, v_revision_time integer, v_cus_to character varying, v_cus_name character varying, v_cus_address character varying, v_ship_to character varying, v_ship_name character varying, v_delivery_by character varying, v_delivery_warehouse character varying, v_delivery_franco character varying, v_delivery_freight_charge numeric, v_remark character varying, v_wh_it_code character varying[], v_wh_it_qty numeric[], v_wh_it_remark character varying[], v_cus_it_code character varying[], v_cus_it_qty numeric[], v_cus_it_remark character varying[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_j integer := 1;
BEGIN

	/*Update idc_tb_dt */
	UPDATE idc_tb_dt SET
		dt_date						= v_do_date,
		dt_issued_by				= v_issued_by,
		dt_issued_date				= v_issued_date,
		dt_received_by				= v_received_by,
		dt_cus_to					= v_cus_to,
		dt_cus_name					= v_cus_name,
		dt_cus_address				= v_cus_address,
		dt_ship_to					= v_ship_to,
		dt_ship_name				= v_ship_name,
		dt_do_date					= v_do_date,
		dt_sj_date					= v_do_date,
		dt_lastupdated_by_account	= v_lastupdated_by_account,
		dt_lastupdated_timestamp	= CURRENT_TIMESTAMP,
		dt_revesion_time			= v_revision_time + 1,
		dt_delivery_warehouse		= v_delivery_warehouse,
		dt_delivery_franco			= v_delivery_franco,
		dt_delivery_by				= v_delivery_by,
		dt_delivery_freight_charge	= v_delivery_freight_charge,
		dt_remark					= v_remark
	WHERE dt_code = v_code;

	/* Delete, insert idc_tb_dt_item */
	DELETE FROM idc_tb_dt_item WHERE dt_code = v_code;
	WHILE v_cus_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_dt_item (
			dt_code, it_code, dtit_qty, dtit_remark
		) VALUES (
			v_code, v_cus_it_code[v_i], v_cus_it_qty[v_i], v_cus_it_remark[v_i]
		);
		v_i := v_i + 1;
	END LOOP;

	UPDATE idc_tb_booking SET
		cus_code			= v_ship_to,
		book_date			= v_do_date,
		book_received_by	= v_received_by
	WHERE
		book_idx = v_book_idx;

	/* Delete, insert from idc_tb_booking_item */
	DELETE FROM idc_tb_booking_item WHERE book_idx = v_book_idx;
	WHILE v_wh_it_code[v_j] IS NOT NULL LOOP
		INSERT INTO idc_tb_booking_item (
			book_idx, it_code, boit_it_code_for, boit_type,
			boit_qty, boit_function, boit_remark
		) VALUES (
			v_book_idx, v_wh_it_code[v_j], v_wh_it_code[v_j], 0,
			v_wh_it_qty[v_j], 1, v_wh_it_remark[v_j]
		);
		v_j := v_j + 1;
	END LOOP;

END;
$$
;

alter function idc_updatedt(varchar, varchar, numeric, date, varchar, date, varchar, varchar, integer, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, numeric, varchar, character varying[], numeric[], character varying[], character varying[], numeric[], character varying[]) owner to dskim
;

create function idc_updatedtrevised(v_code character varying, v_dept character varying, v_book_idx numeric, v_do_date date, v_issued_by character varying, v_issued_date date, v_received_by character varying, v_lastupdated_by_account character varying, v_revision_time integer, v_cus_to character varying, v_cus_name character varying, v_cus_address character varying, v_ship_to character varying, v_ship_name character varying, v_delivery_by character varying, v_delivery_warehouse character varying, v_delivery_franco character varying, v_delivery_freight_charge numeric, v_remark character varying, v_wh_it_code character varying[], v_wh_it_code_for character varying[], v_wh_it_qty numeric[], v_wh_it_function numeric[], v_wh_it_remark character varying[], v_cus_it_code character varying[], v_cus_it_qty numeric[], v_cus_it_remark character varying[], v_rcp_it_code character varying[], v_rcp_it_qty numeric[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_j integer := 1;
	v_k integer := 1;
BEGIN

	/*Update idc_tb_dt */
	UPDATE idc_tb_dt SET
		dt_date						= v_do_date,
		dt_issued_by				= v_issued_by,
		dt_issued_date				= v_issued_date,
		dt_received_by				= v_received_by,
		dt_cus_to					= v_cus_to,
		dt_cus_name					= v_cus_name,
		dt_cus_address				= v_cus_address,
		dt_ship_to					= v_ship_to,
		dt_ship_name				= v_ship_name,
		dt_do_date					= v_do_date,
		dt_sj_date					= v_do_date,
		dt_lastupdated_by_account	= v_lastupdated_by_account,
		dt_lastupdated_timestamp	= CURRENT_TIMESTAMP,
		dt_revesion_time			= v_revision_time + 1,
		dt_delivery_warehouse		= v_delivery_warehouse,
		dt_delivery_franco			= v_delivery_franco,
		dt_delivery_by				= v_delivery_by,
		dt_delivery_freight_charge	= v_delivery_freight_charge,
		dt_remark					= v_remark
	WHERE dt_code = v_code;

	/* Delete, insert idc_tb_dt_item */
	DELETE FROM idc_tb_dt_item WHERE dt_code = v_code;
	WHILE v_cus_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_dt_item (
			dt_code, it_code, dtit_qty, dtit_remark
		) VALUES (
			v_code, v_cus_it_code[v_i], v_cus_it_qty[v_i], v_cus_it_remark[v_i]
		);
		v_i := v_i + 1;
	END LOOP;

	-- Processing update idc_tb_booking, idc_tb_outgoing and status
	UPDATE idc_tb_booking SET
		cus_code			= v_ship_to,
		book_date			= v_issued_date,
		book_received_by	= v_received_by,
		book_is_revised		= true,
		book_is_delivered	= false
	WHERE
		book_idx = v_book_idx;
	UPDATE idc_tb_outgoing_v2 SET out_issued_date = v_issued_date, out_is_revised = true WHERE out_doc_ref = trim(v_code);


	-- Processing checking incoming/ outgoing additional item
	WHILE v_rcp_it_code[v_j] IS NOT NULL LOOP
				INSERT INTO idc_tb_booking_revised (book_idx, it_code, boit_qty)
				VALUES (v_book_idx, v_rcp_it_code[v_j], v_rcp_it_qty[v_j]);
		v_j := v_j + 1;
	END LOOP;

	-- Update idc_tb_booking_item
	DELETE FROM idc_tb_booking_item WHERE book_idx = v_book_idx;
	WHILE v_wh_it_code[v_k] IS NOT NULL LOOP
		INSERT INTO idc_tb_booking_item (
			book_idx, it_code, boit_it_code_for, boit_type, boit_qty, boit_function, boit_remark
		) VALUES (
			v_book_idx, v_wh_it_code[v_k], v_wh_it_code_for[v_k], 0,
			v_wh_it_qty[v_k], 1, v_wh_it_remark[v_k]
		);
		v_k := v_k + 1;
	END LOOP;

END;
$$
;

alter function idc_updatedtrevised(varchar, varchar, numeric, date, varchar, date, varchar, varchar, integer, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, numeric, varchar, character varying[], character varying[], numeric[], numeric[], character varying[], character varying[], numeric[], character varying[], character varying[], numeric[]) owner to dskim
;

create function idc_updateevent(v_code integer, v_nama_acara character varying, v_tanggal_acara date, v_tempat_acara character varying, v_nama_peyelenggara character varying) returns void
	language plpgsql
as $$
BEGIN
	UPDATE idc_tb_event SET
		ev_nama_acara		= v_nama_acara,
		ev_tanggal_acara	= v_tanggal_acara,
		ev_tempat_acara		= v_tempat_acara,
		ev_penyelenggara	= v_nama_peyelenggara
	WHERE ev_idx = v_code;
END;
$$
;

alter function idc_updateevent(integer, varchar, date, varchar, varchar) owner to dskim
;

create function idc_updateeventpeserta(v_code integer, v_id character varying, v_nama character varying, v_alamat character varying, v_kota character varying, v_kode_pos character varying, v_jns_kelamin character varying, v_usia integer, v_telepon character varying, v_handphone character varying, v_email character varying, v_jns_alkes character varying, v_lastupdated_by_account character varying, v_td_sistolik integer, v_td_diastolik integer, v_gd_sewaktu integer, v_gd_puasa integer, v_kt_berat_badan numeric, v_kt_tinggi_badan numeric, v_kt_lemak_tubuh numeric, v_kt_bmi numeric, v_kt_lemak_perut numeric, v_kt_bmr integer, v_kt_lemak_subkutan numeric, v_kt_otot_rangka numeric, v_kt_klasifikasi_umur_tubuh integer) returns void
	language plpgsql
as $$
BEGIN

	UPDATE idc_tb_event_peserta SET
		evp_nama				= v_nama,
		evp_jenis_kelamin		= v_jns_kelamin,
		evp_usia				= v_usia,
		evp_contact_telepon		= v_telepon,
		evp_contact_handphone	= v_handphone,
		evp_contact_email		= v_email,
		evp_contact_alamat		= v_alamat,
		evp_kota				= v_kota,
		evp_pos_kode			= v_kode_pos,
		evp_alat				= v_jns_alkes,
		evp_updated_by_account	= v_lastupdated_by_account,
		evp_updated_timestamp	= current_timestamp,
		evp_sistolik			= v_td_sistolik,
		evp_diastolik			= v_td_diastolik,
		evp_glukosa_darah_sewaktu = v_gd_sewaktu,
		evp_glukosa_darah_puasa	= v_gd_puasa,
		evp_berat_badan			= v_kt_berat_badan,
		evp_tinggi_badan		= v_kt_tinggi_badan,
		evp_lemak_tubuh			= v_kt_lemak_tubuh,
		evp_bmi					= v_kt_bmi,
		evp_lemak_perut			= v_kt_lemak_perut,
		evp_bmr					= v_kt_bmr,
		evp_lemak_subkutan		= v_kt_lemak_subkutan,
		evp_otot_rangka			= v_kt_otot_rangka,
		evp_klasifikasi_umur_tubuh	= v_kt_klasifikasi_umur_tubuh
	WHERE evp_code = v_id;

END;
$$
;

alter function idc_updateeventpeserta(integer, varchar, varchar, varchar, varchar, varchar, varchar, integer, varchar, varchar, varchar, varchar, varchar, integer, integer, integer, integer, numeric, numeric, numeric, numeric, numeric, integer, numeric, numeric, integer) owner to dskim
;

create function idc_updatefakturno(v_ordered_by integer, v_idx integer, v_year character varying, v_digit character varying, v_from integer, v_to integer) returns integer
	language plpgsql
as $$
DECLARE
    v_val integer := 0;
    v_i integer := v_from;
BEGIN

    UPDATE idc_tb_faktur_pajak SET
        fk_year = v_year,
        fk_digit = v_digit,
        fk_from = v_from,
        fk_to = v_to
    WHERE fk_idx = v_idx;

    DELETE FROM idc_tb_faktur_pajak_item WHERE fk_idx = v_idx;
    FOR v_i IN v_from..v_to LOOP
        INSERT INTO idc_tb_faktur_pajak_item (fk_idx, fkit_number, fkit_ordered_by)
        VALUES (v_idx, '010.'||v_digit||'-'||v_year||'.'||lpad(v_i::text, 8, '0'), v_ordered_by);
        v_i := v_i + 1;
    END LOOP;

    RETURN v_val;
END;
$$
;

alter function idc_updatefakturno(integer, integer, varchar, varchar, integer, integer) owner to dskim
;

create function idc_updateforwarder(v_code character varying, v_full_name character varying, v_representative character varying, v_contact_name character varying, v_contact_phone character varying, v_contact_email character varying, v_address character varying, v_phone character varying, v_fax character varying, v_mobile_phone character varying, v_remark character varying) returns void
	language plpgsql
as $$
BEGIN
	UPDATE idc_tb_forwarder SET
		fw_full_name= v_full_name,
		fw_representative= v_representative,
		fw_contact_name= v_contact_name,
		fw_contact_phone= v_contact_phone,
		fw_contact_email= v_contact_email,
		fw_address= v_address,
		fw_phone= v_phone,
		fw_fax= v_fax,
		fw_mobile_phone= v_mobile_phone,
		fw_remark= v_remark
	WHERE fw_code= v_code;
END;
$$
;

alter function idc_updateforwarder(varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar) owner to dskim
;

create function idc_updategroupprice(v_idx integer, v_is_dirty_item boolean, v_cug_code character varying, v_desc character varying, v_basic_disc_pct numeric, v_disc_pct numeric, v_is_valid boolean, v_is_apply_all boolean, v_date_from date, v_date_to date, v_remark character varying, v_it_code character varying[], v_updated_by character varying) returns void
	language plpgsql
as $$
DECLARE
v_i integer := 1;
v_row_count integer := 0;
v_row_count2 integer := 0;
v_policy_idx integer;
BEGIN

	-- Check the duplicated period
	SELECT INTO v_policy_idx ag_idx FROM idc_tb_group_policy
	WHERE cug_code = v_cug_code AND ag_idx != v_idx AND (ag_date_from, ag_date_to + 1) OVERLAPS (v_date_from, v_date_to + 1);

	GET DIAGNOSTICS v_row_count := ROW_COUNT;
	IF v_row_count >= 1 THEN
		-- Check the duplicated disc
		SELECT INTO v_policy_idx ag_idx FROM idc_tb_group_policy
		WHERE cug_code = v_cug_code AND ag_idx != v_idx AND (ag_date_from, ag_date_to + 1) OVERLAPS (v_date_from, v_date_to + 1) AND ag_disc_pct = v_disc_pct;

		GET DIAGNOSTICS v_row_count2 := ROW_COUNT;
		IF v_row_count2 >= 1 THEN
			RAISE EXCEPTION 'PERIOD_DUPLICATED_BY_POLICY_%_ITEM_%', v_policy_idx, v_it_code[v_i];
		ELSE
			UPDATE idc_tb_group_policy SET
				ag_desc = v_desc,
				ag_is_valid = v_is_valid,
				ag_is_apply_all = v_is_apply_all,
				ag_date_from = v_date_from,
				ag_date_to = v_date_to,
				ag_disc_pct = v_disc_pct,
				ag_remark = v_remark,
				ag_updated = CURRENT_TIMESTAMP,
				ag_updated_by = v_updated_by
			WHERE ag_idx = v_idx;
		END IF;
	ELSE
		UPDATE idc_tb_group_policy SET
			ag_desc = v_desc,
			ag_is_valid = v_is_valid,
			ag_is_apply_all = v_is_apply_all,
			ag_date_from = v_date_from,
			ag_date_to = v_date_to,
			ag_disc_pct = v_disc_pct,
			ag_remark = v_remark,
			ag_updated = CURRENT_TIMESTAMP,
			ag_updated_by = v_updated_by
		WHERE ag_idx = v_idx;
	END IF;

	IF v_is_apply_all IS TRUE THEN
		DELETE FROM idc_tb_group_price WHERE ag_idx = v_idx;
	ELSIF v_is_dirty_item IS TRUE THEN
		DELETE FROM idc_tb_group_price WHERE ag_idx = v_idx;
		WHILE v_it_code[v_i] IS NOT NULL LOOP
			INSERT INTO idc_tb_group_price(ag_idx, it_code) VALUES(v_idx, v_it_code[v_i]);
			v_i := v_i + 1;
		END LOOP;
	END IF;
END;
$$
;

alter function idc_updategroupprice(integer, boolean, varchar, varchar, numeric, numeric, boolean, boolean, date, date, varchar, character varying[], varchar) owner to dskim
;

create function idc_updateitem(v_code character varying, v_midx integer, v_model_no character varying, v_type character varying, v_desc character varying, v_remark character varying) returns void
	language plpgsql
as $$
BEGIN
	UPDATE idc_tb_item SET
		icat_midx	= v_midx,
		it_model_no	= v_model_no,
		it_type	= v_type,
		it_desc	= v_desc,
		it_remark	= v_remark
	WHERE it_code	= v_code;
END;
$$
;

alter function idc_updateitem(varchar, integer, varchar, varchar, varchar, varchar) owner to dskim
;

create function idc_updateitemprice(v_code character varying, v_idx integer, v_date_from date, v_user_price numeric, v_remark character varying, v_updated_by character varying) returns void
	language plpgsql
as $$
DECLARE
	v_last_ip_idx integer;
BEGIN
	-- search the last date_to & change it's date to date
	SELECT INTO v_last_ip_idx max(ip_idx) FROM idc_tb_item_price
	WHERE it_code = v_code AND ip_idx != v_idx;

	IF v_last_ip_idx != v_idx THEN
		-- set last close date
		UPDATE idc_tb_item_price SET ip_date_to = v_date_from - 1
		WHERE ip_idx = v_last_ip_idx;
	END IF;

	UPDATE idc_tb_item_price SET
		ip_date_from = v_date_from,
		ip_user_price = v_user_price,
		ip_remark = v_remark,
		ip_updated = CURRENT_TIMESTAMP,
		ip_updated_by = v_updated_by
	WHERE ip_idx = v_idx;
END;
$$
;

alter function idc_updateitemprice(varchar, integer, date, numeric, varchar, varchar) owner to dskim
;

create function idc_updateitempricenet(v_code character varying, v_idx integer, v_date_from date, v_price_kurs numeric, v_price_dollar numeric, v_remark character varying, v_updated_by character varying) returns void
	language plpgsql
as $$
DECLARE
	v_last_ipn_idx integer;
BEGIN

	-- search the last date_to & change it's date to date
	SELECT INTO v_last_ipn_idx max(ipn_idx) FROM idc_tb_item_price_net
	WHERE it_code = v_code AND ipn_idx != v_idx;

	IF v_last_ipn_idx != v_idx THEN
		-- set last close date
		UPDATE idc_tb_item_price_net SET ipn_date_to = v_date_from - 1 WHERE ipn_idx = v_last_ipn_idx;
	END IF;

	UPDATE idc_tb_item_price_net SET
		ipn_date_from = v_date_from,
		ipn_price_kurs = v_price_kurs,
		ipn_price_dollar = v_price_dollar,
		ipn_remark = v_remark,
		ipn_updated = CURRENT_TIMESTAMP,
		ipn_updated_by = v_updated_by
	WHERE ipn_idx = v_idx;

END;
$$
;

alter function idc_updateitempricenet(varchar, integer, date, numeric, numeric, varchar, varchar) owner to dskim
;

create function idc_updatelicensepolicy(v_valid_until integer, v_is_grace_time integer, v_grace_time integer, v_warning_acc integer[]) returns void
	language plpgsql
as $$
BEGIN
	UPDATE idc_tb_policy SET
		pl_opt1 = v_valid_until,
		pl_opt2 = v_is_grace_time,
		pl_opt3 = v_grace_time,
		pl_opt4 = v_warning_acc[1],
		pl_opt5 = v_warning_acc[2],
		pl_opt6 = v_warning_acc[3]
	WHERE
		pl_no = 447;
END;
$$
;

alter function idc_updatelicensepolicy(integer, integer, integer, integer[]) owner to dskim
;

create function idc_updatelocalsupplier(v_code character varying, v_internal_name character varying, v_full_name character varying, v_contact_name character varying, v_contact_position character varying, v_contact_phone character varying, v_contact_hphone character varying, v_contact_email character varying, v_phone character varying, v_fax character varying, v_address character varying) returns void
	language plpgsql
as $$
BEGIN

	UPDATE idc_tb_supplier_local SET
		sp_internal_name	= v_internal_name,
		sp_full_name		= v_full_name,
		sp_contact_name		= v_contact_name,
		sp_contact_position	= v_contact_position,
		sp_contact_phone	= v_contact_phone,
		sp_contact_hphone	= v_contact_hphone,
		sp_contact_email	= v_contact_email,
		sp_phone			= v_phone,
		sp_fax				= v_fax,
		sp_address			= v_address
	WHERE sp_code	= v_code;

END;
$$
;

alter function idc_updatelocalsupplier(varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar) owner to dskim
;

create function idc_updateorder(v_code character varying, v_dept character varying, v_type_order integer, v_book_idx integer, v_type character varying, v_received_by character varying, v_confirm_by character varying, v_po_date date, v_po_no character varying, v_vat numeric, v_cus_to character varying, v_cus_to_attn character varying, v_cus_to_address character varying, v_ship_to character varying, v_ship_to_attn character varying, v_ship_to_address character varying, v_bill_to character varying, v_bill_to_attn character varying, v_bill_to_address character varying, v_price_discount numeric, v_price_chk integer, v_delivery_chk integer, v_delivery_by character varying, v_delivery_freight_charge numeric, v_payment_chk integer, v_payment_widthin_days integer, v_payment_closing_on text, v_payment_cash_by character varying, v_payment_check_by character varying, v_payment_transfer_by character varying, v_payment_giro_by character varying, v_sign_by character varying, v_remark character varying, v_lastupdated_by_account character varying, v_wh_it_code character varying[], v_wh_it_code_for character varying[], v_wh_it_qty numeric[], v_wh_it_function numeric[], v_wh_it_remark character varying[], v_cus_it_code character varying[], v_cus_it_unit_price numeric[], v_cus_it_qty integer[], v_cus_it_delivery date[], v_cus_it_remark character varying[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_j integer := 1;
	v_cur_book_idx integer;
	rec1 record;
	rec2 record;
	v_payment_closing_on_adj date;
BEGIN

	IF v_payment_closing_on IS NULL THEN
		v_payment_closing_on_adj = null;
	END IF;

	UPDATE idc_tb_order SET
		ord_lastupdated_by_account	= v_lastupdated_by_account,
		ord_lastupdated_timestamp	= CURRENT_TIMESTAMP,
		ord_po_date					= v_po_date,
		ord_po_no					= v_po_no,
		ord_received_by				= v_received_by,
		ord_confirm_by				= v_confirm_by,
		ord_revision_time			= ord_revision_time + 1,
		ord_vat						= v_vat,
		ord_cus_to					= v_cus_to,
		ord_cus_to_attn				= v_cus_to_attn,
		ord_cus_to_address			= v_cus_to_address,
		ord_ship_to					= v_ship_to,
		ord_ship_to_attn			= v_ship_to_attn,
		ord_ship_to_address			= v_ship_to_address,
		ord_bill_to					= v_bill_to,
		ord_bill_to_attn			= v_bill_to_attn,
		ord_bill_to_address			= v_bill_to_address,
		ord_price_discount			= v_price_discount,
		ord_price_chk				= v_price_chk,
		ord_delivery_chk			= v_delivery_chk,
		ord_delivery_by				= v_delivery_by,
		ord_delivery_freight_charge = v_delivery_freight_charge,
		ord_payment_chk				= v_payment_chk,
		ord_payment_widthin_days	= v_payment_widthin_days,
		ord_payment_closing_on		= v_payment_closing_on_adj,
		ord_payment_cash_by			= v_payment_cash_by,
		ord_payment_check_by		= v_payment_check_by,
		ord_payment_transfer_by		= v_payment_transfer_by,
		ord_payment_giro_by			= v_payment_giro_by,
		ord_sign_by					= v_sign_by,
		ord_remark					= v_remark
	WHERE ord_code = v_code;

	/*Update idc_tb_order_item */
	DELETE FROM idc_tb_order_item WHERE ord_code = v_code;
	IF v_type = 'OO' THEN
		WHILE v_cus_it_code[v_i] IS NOT NULL LOOP
			INSERT INTO idc_tb_order_item (
				ord_code, cus_code, odit_dept, it_code, odit_oo_qty, odit_qty, odit_unit_price, odit_delivery, odit_date, odit_remark
			) VALUES (
				v_code, v_cus_to, v_dept, v_cus_it_code[v_i], v_cus_it_qty[v_i], v_cus_it_qty[v_i],
				v_cus_it_unit_price[v_i], v_cus_it_delivery[v_i], v_po_date, v_cus_it_remark[v_i]
			);
			v_i := v_i + 1;
		END LOOP;
	ELSE
		WHILE v_cus_it_code[v_i] IS NOT NULL LOOP
			INSERT INTO idc_tb_order_item (
				ord_code, cus_code, odit_dept, it_code, odit_ok_qty, odit_qty, odit_unit_price, odit_delivery, odit_date, odit_remark
			) VALUES (
				v_code, v_cus_to, v_dept, v_cus_it_code[v_i], v_cus_it_qty[v_i], v_cus_it_qty[v_i],
				v_cus_it_unit_price[v_i], v_cus_it_delivery[v_i], v_po_date, v_cus_it_remark[v_i]
			);
			v_i := v_i + 1;
		END LOOP;
	END IF;

	IF v_type_order = 0 THEN
		--Update booking information
		IF v_book_idx IS NOT NULL THEN
			UPDATE idc_tb_booking SET
				cus_code			= v_ship_to,
				book_date			= v_po_date,
				book_received_by	= v_received_by
			WHERE
				book_idx = v_book_idx;
			DELETE FROM idc_tb_booking_item WHERE book_idx = v_book_idx;

			WHILE v_wh_it_code[v_j] IS NOT NULL LOOP
				INSERT INTO idc_tb_booking_item (
					book_idx, it_code, boit_it_code_for, boit_type,
					boit_qty, boit_function, boit_remark
				) VALUES (
					v_book_idx, v_wh_it_code[v_j], v_wh_it_code_for[v_j], 0,
					v_wh_it_qty[v_j], v_wh_it_function[v_j], v_wh_it_remark[v_j]
				);
				v_j := v_j + 1;
			END LOOP;

		--function will execute this one, when move billing from type_invoice 1 to type_invoice 0
		ELSIF v_book_idx IS NULL THEN
			INSERT INTO idc_tb_booking(book_code, book_dept, cus_code, book_doc_ref, book_date, book_doc_type, book_type, book_received_by)
			VALUES ('D'||substr(v_code,2,12), 'A', v_ship_to, v_code, v_po_date, 2, 1, v_received_by);
			v_cur_book_idx := currval('idc_tb_booking_book_idx_seq');

			WHILE v_wh_it_code[v_j] IS NOT NULL LOOP
				INSERT INTO idc_tb_booking_item (
				book_idx, it_code, boit_it_code_for, boit_type, boit_qty, boit_function, boit_remark
				) VALUES (
					v_cur_book_idx, v_wh_it_code[v_j], v_wh_it_code_for[v_j], 0,
					v_wh_it_qty[v_j], v_wh_it_function[v_j], v_wh_it_remark[v_j]
				);
				v_j := v_j + 1;
			END LOOP;
		END IF;
	END IF;

END;
$$
;

alter function idc_updateorder(varchar, varchar, integer, integer, varchar, varchar, varchar, date, varchar, numeric, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, numeric, integer, integer, varchar, numeric, integer, integer, text, varchar, varchar, varchar, varchar, varchar, varchar, varchar, character varying[], character varying[], numeric[], numeric[], character varying[], character varying[], numeric[], integer[], date[], character varying[]) owner to dskim
;

create function idc_updateorderrevised(v_code character varying, v_dept character varying, v_type_order integer, v_book_idx integer, v_type character varying, v_received_by character varying, v_confirm_by character varying, v_po_date date, v_po_no character varying, v_vat numeric, v_cus_to character varying, v_cus_to_attn character varying, v_cus_to_address character varying, v_ship_to character varying, v_ship_to_attn character varying, v_ship_to_address character varying, v_bill_to character varying, v_bill_to_attn character varying, v_bill_to_address character varying, v_price_discount numeric, v_price_chk integer, v_delivery_chk integer, v_delivery_by character varying, v_delivery_freight_charge numeric, v_payment_chk integer, v_payment_widthin_days integer, v_payment_closing_on text, v_payment_cash_by character varying, v_payment_check_by character varying, v_payment_transfer_by character varying, v_payment_giro_by character varying, v_sign_by character varying, v_remark character varying, v_lastupdated_by_account character varying, v_wh_it_code character varying[], v_wh_it_code_for character varying[], v_wh_it_qty numeric[], v_wh_it_function numeric[], v_wh_it_remark character varying[], v_cus_it_code character varying[], v_cus_it_unit_price numeric[], v_cus_it_qty integer[], v_cus_it_delivery date[], v_cus_it_remark character varying[], v_rcp_it_code character varying[], v_rcp_it_qty numeric[]) returns void
	language plpgsql
as $$
DECLARE
    v_i integer := 1;
    v_j integer := 1;
    v_k integer := 1;
    v_payment_closing_on_adj date;
    v_check boolean := false;
BEGIN

    SELECT INTO v_check book_is_revised FROM idc_tb_booking WHERE book_idx = v_book_idx;
    IF v_check THEN
        RAISE EXCEPTION 'REVISED PROCESS ALREADY DONE, PLEASE RE-CHECK %', v_code;
    END IF;

    IF v_payment_closing_on IS NULL THEN
        v_payment_closing_on_adj = null;
    END IF;

    UPDATE idc_tb_order SET
        ord_lastupdated_by_account  = v_lastupdated_by_account,
        ord_lastupdated_timestamp   = CURRENT_TIMESTAMP,
        ord_po_date                 = v_po_date,
        ord_po_no                   = v_po_no,
        ord_received_by             = v_received_by,
        ord_confirm_by              = v_confirm_by,
        ord_revision_time           = ord_revision_time + 1,
        ord_vat                     = v_vat,
        ord_cus_to                  = v_cus_to,
        ord_cus_to_attn             = v_cus_to_attn,
        ord_cus_to_address          = v_cus_to_address,
        ord_ship_to                 = v_ship_to,
        ord_ship_to_attn            = v_ship_to_attn,
        ord_ship_to_address         = v_ship_to_address,
        ord_bill_to                 = v_bill_to,
        ord_bill_to_attn            = v_bill_to_attn,
        ord_bill_to_address         = v_bill_to_address,
        ord_price_discount          = v_price_discount,
        ord_price_chk               = v_price_chk,
        ord_delivery_chk            = v_delivery_chk,
        ord_delivery_by             = v_delivery_by,
        ord_delivery_freight_charge = v_delivery_freight_charge,
        ord_payment_chk             = v_payment_chk,
        ord_payment_widthin_days    = v_payment_widthin_days,
        ord_payment_closing_on      = v_payment_closing_on_adj,
        ord_payment_cash_by         = v_payment_cash_by,
        ord_payment_check_by        = v_payment_check_by,
        ord_payment_transfer_by     = v_payment_transfer_by,
        ord_payment_giro_by         = v_payment_giro_by,
        ord_sign_by                 = v_sign_by,
        ord_remark                  = v_remark
    WHERE ord_code = v_code;

    /*Update idc_tb_order_item */
    DELETE FROM idc_tb_order_item WHERE ord_code = v_code;
    IF v_type = 'OO' THEN
        WHILE v_cus_it_code[v_i] IS NOT NULL LOOP
            INSERT INTO idc_tb_order_item (
                ord_code, cus_code, odit_dept, it_code, odit_oo_qty, odit_qty, odit_unit_price, odit_delivery, odit_date, odit_remark
            ) VALUES (
                v_code, v_cus_to, v_dept, v_cus_it_code[v_i], v_cus_it_qty[v_i], v_cus_it_qty[v_i],
                v_cus_it_unit_price[v_i], v_cus_it_delivery[v_i], v_po_date, v_cus_it_remark[v_i]
            );
            v_i := v_i + 1;
        END LOOP;
    ELSE
        WHILE v_cus_it_code[v_i] IS NOT NULL LOOP
            INSERT INTO idc_tb_order_item (
                ord_code, cus_code, odit_dept, it_code, odit_ok_qty, odit_qty, odit_unit_price, odit_delivery, odit_date, odit_remark
            ) VALUES (
                v_code, v_cus_to, v_dept, v_cus_it_code[v_i], v_cus_it_qty[v_i], v_cus_it_qty[v_i],
                v_cus_it_unit_price[v_i], v_cus_it_delivery[v_i], v_po_date, v_cus_it_remark[v_i]
            );
            v_i := v_i + 1;
        END LOOP;
    END IF;

    -- Processing update idc_tb_booking, idc_tb_outgoing and status
    UPDATE idc_tb_booking SET
        cus_code            = v_ship_to,
        book_date           = v_po_date,
        book_received_by    = v_received_by,
        book_is_revised     = true,
        book_is_delivered   = false
    WHERE
        book_idx = v_book_idx;
    UPDATE idc_tb_outgoing_v2 SET
        out_issued_date = v_po_date,
        out_is_revised = true,
        cus_code = v_ship_to
    WHERE out_doc_ref = trim(v_code);


    -- Processing checking incoming/ outgoing additional item
    WHILE v_rcp_it_code[v_j] IS NOT NULL LOOP
                INSERT INTO idc_tb_booking_revised (book_idx, it_code, boit_qty)
                VALUES (v_book_idx, v_rcp_it_code[v_j], v_rcp_it_qty[v_j]);
        v_j := v_j + 1;
    END LOOP;

    -- Update idc_tb_booking_item
    DELETE FROM idc_tb_booking_item WHERE book_idx = v_book_idx;
    WHILE v_wh_it_code[v_k] IS NOT NULL LOOP
        INSERT INTO idc_tb_booking_item (
            book_idx, it_code, boit_it_code_for, boit_type, boit_qty, boit_function, boit_remark
        ) VALUES (
            v_book_idx, v_wh_it_code[v_k], v_wh_it_code_for[v_k], 0,
            v_wh_it_qty[v_k], v_wh_it_function[v_k], v_wh_it_remark[v_k]
        );
        v_k := v_k + 1;
    END LOOP;

END;
$$
;

alter function idc_updateorderrevised(varchar, varchar, integer, integer, varchar, varchar, varchar, date, varchar, numeric, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, numeric, integer, integer, varchar, numeric, integer, integer, text, varchar, varchar, varchar, varchar, varchar, varchar, varchar, character varying[], character varying[], numeric[], numeric[], character varying[], character varying[], numeric[], integer[], date[], character varying[], character varying[], numeric[]) owner to dskim
;

create function idc_updateplclaim(v_code integer, v_sp_code character varying, v_sp_name character varying, v_inv_no character varying, v_inv_date date, v_etd_date date, v_eta_date date, v_received_by character varying, v_lastupdated_by_account character varying, v_shipment_mode character varying, v_mode_desc character varying, v_remark character varying, v_icat_midx integer[], v_it_code character varying[], v_it_unit_price numeric[], v_it_qty integer[], v_it_remark character varying[], v_it_att character varying[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
BEGIN

	/* update idc_tb_claim */
	UPDATE idc_tb_claim SET
		cl_sp_code					= v_sp_code,
		cl_sp_name					= v_sp_name,
		cl_inv_no					= v_inv_no,
		cl_inv_date					= v_inv_date,
		cl_received_by				= v_received_by,
		cl_lastupdated_by_account	= v_lastupdated_by_account,
		cl_lastupdated_timestamp	= CURRENT_TIMESTAMP,
		cl_etd_date					= v_etd_date,
		cl_eta_date					= v_eta_date,
		cl_shipment_mode			= v_shipment_mode,
		cl_shipment_desc			= v_mode_desc,
		cl_remark					= v_remark
	WHERE cl_idx = v_code;

	/* update idc_tb_claim_item */
	DELETE FROM idc_tb_claim_item WHERE cl_idx = v_code;
		WHILE v_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_claim_item (cl_idx, icat_midx, it_code, clit_attribute, clit_unit_price, clit_qty, clit_remark)
		VALUES (v_code, v_icat_midx[v_i], v_it_code[v_i], v_it_att[v_i], v_it_unit_price[v_i], v_it_qty[v_i], v_it_remark[v_i]);
		v_i := v_i + 1;
	END LOOP;

END;
$$
;

alter function idc_updateplclaim(integer, varchar, varchar, varchar, date, date, date, varchar, varchar, varchar, varchar, varchar, integer[], character varying[], numeric[], integer[], character varying[], character varying[]) owner to dskim
;

create function idc_updateplclaim(v_code integer, v_sp_code character varying, v_sp_name character varying, v_inv_no character varying, v_inv_date date, v_etd_date date, v_eta_date date, v_received_by character varying, v_lastupdated_by_account character varying, v_shipment_mode character varying, v_mode_desc character varying, v_remark character varying, v_icat_midx integer[], v_it_code character varying[], v_it_unit_price numeric[], v_it_qty numeric[], v_it_remark character varying[], v_it_att character varying[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
BEGIN

	/* update idc_tb_claim */
	UPDATE idc_tb_claim SET
		cl_sp_code					= v_sp_code,
		cl_sp_name					= v_sp_name,
		cl_inv_no					= v_inv_no,
		cl_inv_date					= v_inv_date,
		cl_received_by				= v_received_by,
		cl_lastupdated_by_account	= v_lastupdated_by_account,
		cl_lastupdated_timestamp	= CURRENT_TIMESTAMP,
		cl_etd_date					= v_etd_date,
		cl_eta_date					= v_eta_date,
		cl_shipment_mode			= v_shipment_mode,
		cl_shipment_desc			= v_mode_desc,
		cl_remark					= v_remark
	WHERE cl_idx = v_code;

	/* update idc_tb_claim_item */
	DELETE FROM idc_tb_claim_item WHERE cl_idx = v_code;
		WHILE v_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_claim_item (cl_idx, icat_midx, it_code, clit_attribute, clit_unit_price, clit_qty, clit_remark)
		VALUES (v_code, v_icat_midx[v_i], v_it_code[v_i], v_it_att[v_i], v_it_unit_price[v_i], v_it_qty[v_i], v_it_remark[v_i]);
		v_i := v_i + 1;
	END LOOP;

END;
$$
;

alter function idc_updateplclaim(integer, varchar, varchar, varchar, date, date, date, varchar, varchar, varchar, varchar, varchar, integer[], character varying[], numeric[], numeric[], character varying[], character varying[]) owner to dskim
;

create function idc_updatepllocal(v_po_code character varying, v_pl_no integer, v_pl_date date, v_issued_by character varying, v_delivery_date date, v_lastupdated_by_account character varying, v_remark character varying, v_it_code character varying[], v_plit_qty integer[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
BEGIN
	UPDATE idc_tb_pl_local SET
		pl_date						= v_pl_date,
		pl_issued_by				= v_issued_by,
		pl_delivery_date			= v_delivery_date,
		pl_lastupdated_by_account	= v_lastupdated_by_account,
		pl_lastupdated_timestamp	= CURRENT_TIMESTAMP,
		pl_remark					= v_remark
	WHERE po_code = v_po_code AND pl_no = v_pl_no;

	DELETE FROM idc_tb_pl_local_item WHERE po_code = v_po_code and pl_no = v_pl_no;
		WHILE v_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_pl_local_item (po_code, pl_no, it_code, plit_qty)
		VALUES (v_po_code, v_pl_no, v_it_code[v_i], v_plit_qty[v_i]);
		v_i := v_i + 1;
	END LOOP;
END;
$$
;

alter function idc_updatepllocal(varchar, integer, date, varchar, date, varchar, varchar, character varying[], integer[]) owner to dskim
;

create function idc_updatepolocal(v_code character varying, v_po_date date, v_po_type integer, v_deli_date date, v_sp_code character varying, v_sp_name character varying, v_sp_attn character varying, v_sp_phone character varying, v_sp_fax character varying, v_sp_address character varying, v_total_qty integer, v_total_amount numeric, v_vat numeric, v_text_add1 character varying, v_text_add2 character varying, v_total_add1 numeric, v_total_add2 numeric, v_says_in_word character varying, v_prepared_by character varying, v_confirmed_by character varying, v_approved_by character varying, v_remark character varying, v_lastupdated_by_account character varying, v_revision_time integer, v_it_code character varying[], v_poit_unit character varying[], v_poit_unit_price numeric[], v_poit_qty integer[], v_poit_remark character varying[]) returns void
	language plpgsql
as $$
DECLARE
    v_i integer := 1;
BEGIN

    UPDATE idc_tb_po_local SET
        po_date             = v_po_date,
        po_type             = v_po_type,
        po_delivery_date    = v_deli_date,
        sp_code             = v_sp_code,
        po_sp_name          = v_sp_name,
        po_sp_attn          = v_sp_attn,
        po_sp_phone         = v_sp_phone,
        po_sp_fax           = v_sp_fax,
        po_sp_address       = v_sp_address,
        po_total_qty        = v_total_qty,
        po_total_amount     = v_total_amount,
        po_vat              = v_vat,
        po_text_charge1     = v_text_add1,
        po_text_charge2     = v_text_add2,
        po_total_charge1    = v_total_add1,
        po_total_charge2    = v_total_add2,
        po_says_in_words    = v_says_in_word,
        po_prepared_by      = v_prepared_by,
        po_confirmed_by     = v_confirmed_by,
        po_approved_by      = v_approved_by,
        po_remark           = v_remark,
        po_revesion_time    = v_revision_time,
        po_lastupdated_by_account   = v_lastupdated_by_account,
        po_lastupdated_timestamp    = CURRENT_TIMESTAMP
    WHERE po_code = v_code;

    DELETE FROM idc_tb_po_local_item WHERE po_code = v_code;
    WHILE v_it_code[v_i] IS NOT NULL LOOP
        INSERT INTO idc_tb_po_local_item (po_code, it_code, poit_unit, poit_qty, poit_unit_price, poit_remark)
        VALUES (v_code, v_it_code[v_i], v_poit_unit[v_i], v_poit_qty[v_i], v_poit_unit_price[v_i], v_poit_remark[v_i]);
        v_i := v_i + 1;
    END LOOP;

END;
$$
;

alter function idc_updatepolocal(varchar, date, integer, date, varchar, varchar, varchar, varchar, varchar, varchar, integer, numeric, numeric, varchar, varchar, numeric, numeric, varchar, varchar, varchar, varchar, varchar, varchar, integer, character varying[], character varying[], numeric[], integer[], character varying[]) owner to dskim
;

create function idc_updateregletter(v_code character varying, v_dept character, v_cus_code character varying, v_cus_attn character varying, v_amount numeric, v_stamp_pcs integer, v_reg_date date, v_reg_issued_by character varying, v_reg_send_to character varying, v_reg_pic character varying, v_reg_item character varying, v_reg_address character varying, v_remark character varying, v_reg_brief_summary character varying, v_reg_status character varying, v_reg_confirmed_date date, v_reg_cancelled_reason character varying, v_lastupdated_by_account character varying, v_rev_no integer, v_type character varying[], v_file_name character varying[], v_file_path character varying[], v_file_type character varying[], v_file_desc character varying[], v_fee_desc character varying[], v_fee_amount numeric[]) returns void
	language plpgsql
as $$
DECLARE
  v_is_charge boolean := false;
  v_i integer := 1;
  v_j integer := 1;
BEGIN

  IF v_amount > 0 OR v_stamp_pcs > 0 THEN
    v_is_charge = true;
  END IF;

  UPDATE idc_tb_letter SET
    lt_reg_date           = v_reg_date,
    lt_issued_by          = v_reg_issued_by,
    lt_status_of_letter   = v_reg_status,
    lt_send_to            = v_reg_send_to,
    lt_pic                = v_reg_pic,
    lt_item               = v_reg_item,
    lt_address            = v_reg_address,
    lt_confirm_date       = v_reg_confirmed_date,
    lt_cancelled_reason   = v_reg_cancelled_reason,
    lt_remark             = v_remark,
    lt_brief_summary      = v_reg_brief_summary,
    lt_lastupdated_by_account = v_lastupdated_by_account,
    lt_lastupdated_timestamp  = current_timestamp,
    lt_cancelled_by_account   = v_lastupdated_by_account,
    lt_cancelled_timestamp    = current_timestamp,
    cus_code                  = v_cus_code,
    lt_cus_attn               = v_cus_attn,
    lt_amount                 = v_amount,
    lt_stamp                  = v_stamp_pcs,
    lt_is_charge              = v_is_charge,
    lt_rev_no                 = v_rev_no
  WHERE lt_reg_no =  v_code;

  WHILE v_file_name[v_i] IS NOT NULL AND v_file_name[v_i] != '' LOOP
    INSERT INTO idc_tb_letter_file (lt_reg_no, ltf_type, ltf_file_name, ltf_file_path, ltf_file_type, ltf_file_desc)
    VALUES (v_code, v_type[v_i], v_file_name[v_i], v_file_path[v_i], v_file_type[v_i], v_file_desc[v_i]);
    v_i := v_i + 1;
  END LOOP;

  DELETE FROM idc_tb_letter_item WHERE lt_reg_no = v_code;
  WHILE v_fee_desc[v_j] IS NOT NULL AND v_fee_desc[v_j] != '' LOOP
    INSERT INTO idc_tb_letter_item (lt_reg_no, lti_desc, lti_amount)
    VALUES (v_code, v_fee_desc[v_j], v_fee_amount[v_j]);
    v_j := v_j + 1;
  END LOOP;


END;
$$
;

alter function idc_updateregletter(varchar, char, varchar, varchar, numeric, integer, date, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, date, varchar, varchar, integer, character varying[], character varying[], character varying[], character varying[], character varying[], character varying[], numeric[]) owner to dskim
;

create function idc_updaterejectstock(v_reject_idx integer, v_it_code character varying, v_serial_no character varying, v_warranty date, v_desc character varying, v_is_replace boolean, v_it_replacement integer, v_it_status character varying, v_log_by_account character varying) returns void
	language plpgsql
as $$
DECLARE
	v_logs_code varchar;
BEGIN

	UPDATE idc_tb_reject_item SET
		rjit_serial_number	= v_serial_no,
		rjit_warranty		= v_warranty,
		rjit_desc			= v_desc,
		rjit_status			= v_it_status,
		rjit_is_replace		= v_is_replace,
		rjit_replace_item	= v_it_replacement
	WHERE rjit_idx = v_reject_idx;

	IF v_it_status = 'on_stock' THEN
		UPDATE idc_tb_stock SET
			stk_qty = stk_qty + 1,
			stk_updated = current_timestamp
		WHERE it_code = v_it_code AND stk_wh_location = 1 AND stk_type = 2;

		SELECT INTO v_logs_code idc_getStockLogIdx(v_it_code, 1, 2, CURRENT_DATE);
		INSERT INTO idc_tb_stock_logs(
			log_code, it_code, log_wh_location, log_type,
			log_document_type, log_document_idx, log_document_no, log_document_date,
			log_cfm_timestamp, log_cfm_by_account, log_qty
		) VALUES (
			v_logs_code, v_it_code, 1, 2,
			17, null, null, null,
			CURRENT_TIMESTAMP, v_log_by_account, 1
		);
	END IF;

END;
$$
;

alter function idc_updaterejectstock(integer, varchar, varchar, date, varchar, boolean, integer, varchar, varchar) owner to dskim
;

create function idc_updaterelatedtables(v_activity_type boolean, v_book_idx integer, v_out_doc_ref character varying, v_out_doc_type character varying, v_cfm_by_account character varying, v_cfm_date date) returns void
	language plpgsql
as $$
BEGIN

	/* true is confirm, false is unconfirm */
	IF v_activity_type IS TRUE THEN
		/* LOCK REFERENCE TABLE */
		UPDATE idc_tb_booking SET book_is_delivered = true WHERE book_idx = v_book_idx;

		IF v_out_doc_type = 'DO Billing' THEN
			UPDATE idc_tb_billing SET
				bill_cfm_wh_delivery_by_account	= v_cfm_by_account,
				bill_cfm_wh_delivery_timestamp	= CURRENT_TIMESTAMP,
				bill_cfm_wh_date				= v_cfm_date
			WHERE bill_code = substr(v_out_doc_ref,1,13);
		ELSIF v_out_doc_type = 'DO Order' THEN
			UPDATE idc_tb_order SET
				ord_cfm_wh_delivery_by_account	= v_cfm_by_account,
				ord_cfm_wh_delivery_timestamp	= CURRENT_TIMESTAMP
			WHERE ord_code = substr(v_out_doc_ref,1,12);
		ELSIF v_out_doc_type = 'DT' THEN
			UPDATE idc_tb_dt SET
				dt_cfm_wh_delivery_by_account	= v_cfm_by_account,
				dt_cfm_wh_delivery_timestamp	= CURRENT_TIMESTAMP
			WHERE dt_code = substr(v_out_doc_ref,1,12);
		ELSIF v_out_doc_type = 'DF' THEN
			UPDATE idc_tb_df SET
				df_cfm_wh_delivery_by_account	= v_cfm_by_account,
				df_cfm_wh_delivery_timestamp	= CURRENT_TIMESTAMP
			WHERE df_code = substr(v_out_doc_ref,1,12);
		ELSIF v_out_doc_type = 'DR' THEN
			UPDATE idc_tb_dr SET
				dr_cfm_wh_delivery_by_account	= v_cfm_by_account,
				dr_cfm_wh_delivery_timestamp	= CURRENT_TIMESTAMP
			WHERE dr_code = substr(v_out_doc_ref,1,12);
		ELSIF v_out_doc_type = 'DM' THEN
			UPDATE idc_tb_request SET
				req_cfm_wh_delivery_by_account	= v_cfm_by_account,
				req_cfm_wh_delivery_timestamp	= CURRENT_TIMESTAMP
			WHERE req_code = substr(v_out_doc_ref,1,11);
		END IF;

	ELSIF v_activity_type IS FALSE THEN
		/* update status idc_tb_booking */
		UPDATE idc_tb_booking SET
			book_is_delivered	= 'f',
			book_revision_time	= book_revision_time+1,
			book_last_cancelled_by	= v_cfm_by_account,
			book_last_cancelled_timestamp = CURRENT_TIMESTAMP
		WHERE book_idx = v_book_idx;

		IF v_out_doc_type = 'DO Order' THEN
			UPDATE idc_tb_billing SET
				bill_cfm_wh_delivery_by_account = '',
				bill_cfm_wh_delivery_timestamp  = NULL,
				bill_cfm_wh_date				= NULL,
				bill_delivery_timestamp			= NULL,
				bill_delivery_by_account		= '',
				bill_delivery_date				= NULL,
				bill_cfm_delivery				= NULL,
				bill_delivery_by				= '',
				bill_cfm_delivery_by			= '',
				bill_cfm_tukar_faktur			= NULL,
				bill_cfm_tukar_faktur_by		= ''
			WHERE bill_code = substr(v_out_doc_ref,1,13);
		ELSIF v_out_doc_type = 'DO Billing' THEN
			UPDATE idc_tb_order SET
				ord_cfm_wh_delivery_by_account	= '',
				ord_cfm_wh_delivery_timestamp	= NULL,
				ord_cfm_deli_timestamp			= NULL,
				ord_cfm_deli_by_account			= ''
			WHERE ord_code = substr(v_out_doc_ref,1,12);
			DELETE FROM idc_tb_delivery WHERE ord_code = substr(v_out_doc_ref,1,12);
		ELSIF v_out_doc_type = 'DT' THEN
			UPDATE idc_tb_dt SET
				dt_cfm_wh_delivery_by_account	= '',
				dt_cfm_wh_delivery_timestamp	= NULL,
				dt_delivery_timestamp			= null,
				dt_delivery_date				= null,
				dt_delivery_to_customer_by		= '',
				dt_delivery_confirmed_by		= ''
			WHERE dt_code = substr(v_out_doc_ref,1,11);
		ELSIF v_out_doc_type = 'DF' THEN
			UPDATE idc_tb_df SET
				df_cfm_wh_delivery_by_account	= '',
				df_cfm_wh_delivery_timestamp	= NULL,
				df_delivery_timestamp			= null,
				df_delivery_date				= null,
				df_delivery_to_customer_by		= '',
				df_delivery_confirmed_by		= ''
			WHERE df_code = substr(v_out_doc_ref,1,11);
		ELSIF v_out_doc_type = 'DR' THEN
			UPDATE idc_tb_dr SET
				dr_cfm_wh_delivery_by_account	= '',
				dr_cfm_wh_delivery_timestamp	= NULL,
				dr_delivery_timestamp			= null,
				dr_delivery_date				= null,
				dr_delivery_to_customer_by		= '',
				dr_delivery_confirmed_by		= ''
			WHERE dr_code = substr(v_out_doc_ref,1,11);
		ELSIF v_out_doc_type = 'DM' THEN
			UPDATE idc_tb_request SET
				req_cfm_wh_delivery_by_account	= '',
				req_cfm_wh_delivery_timestamp	= NULL
			WHERE req_code = substr(v_out_doc_ref,1,11);
		END IF;
	END IF;

END;
$$
;

alter function idc_updaterelatedtables(boolean, integer, varchar, varchar, varchar, date) owner to dskim
;

create function idc_updaterequestdemo(v_code character varying, v_request_by character varying, v_request_date date, v_cus_code character varying, v_cus_name character varying, v_cus_address character varying, v_sign_by character varying, v_remark character varying, v_log_by_account character varying, v_it_code character varying[], v_it_returnable character varying[], v_it_qty numeric[], v_it_remark character varying[]) returns character varying
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_returnable boolean;
BEGIN

	/* Update idc_tb_using_demo */
	UPDATE idc_tb_using_demo SET
		use_request_by				= v_request_by,
		use_request_date			= v_request_date,
		use_cus_to					= v_cus_code,
		use_cus_name				= v_cus_name,
		use_cus_address				= v_cus_address,
		use_lastupdated_by_account	= v_log_by_account,
		use_lastupdated_timestamp	= CURRENT_TIMESTAMP,
		use_signature_by			= v_sign_by,
		use_remark					= v_remark,
		use_revesion_time			= use_revesion_time + 1
	WHERE use_code = v_code;

	/* Update idc_tb_using_demo_item */
	DELETE FROM idc_tb_using_demo_item WHERE use_code = v_code;
	WHILE v_it_code[v_i] IS NOT NULL LOOP
		IF v_it_returnable[v_i] = '0' THEN
			v_returnable = true;
		ELSIF v_it_returnable[v_i] = '1' THEN
			v_returnable = false;
		END IF;

		INSERT INTO idc_tb_using_demo_item (use_code, it_code, usit_returnable, usit_qty, usit_remark)
		VALUES (v_code, v_it_code[v_i], v_returnable, v_it_qty[v_i], v_it_remark[v_i]);
		v_i := v_i + 1;
	END LOOP;

	RETURN v_code;
END;
$$
;

alter function idc_updaterequestdemo(varchar, varchar, date, varchar, varchar, varchar, varchar, varchar, varchar, character varying[], character varying[], numeric[], character varying[]) owner to dskim
;

create function idc_updatereturnbilling(v_code character varying, v_paper integer, v_bill_code character varying, v_std_idx integer, v_inc_idx integer, v_return_condition integer, v_dept character varying, v_return_date date, v_received_by character varying, v_lastupdated_by_account character varying, v_revision_time integer, v_sj_code character varying, v_sj_date date, v_po_no character varying, v_po_date date, v_disc numeric, v_vat numeric, v_old_total_amount numeric, v_total_before_vat integer, v_total_return integer, v_cus_to character varying, v_cus_name character varying, v_cus_attn character varying, v_cus_address character varying, v_npwp character varying, v_ship_to character varying, v_ship_name character varying, v_delivery_chk integer, v_delivery_by character varying, v_delivery_warehouse character varying, v_delivery_franco character varying, v_delivery_freight_charge integer, v_payment_chk integer, v_payment_widthin_days integer, v_payment_sj_inv_fp_tender character varying, v_payment_closing_on date, v_payment_for_the_month_week character varying, v_payment_cash_by character varying, v_payment_check_by character varying, v_payment_transfer_by character varying, v_payment_giro_due date, v_payment_giro_issue date, v_bank character varying, v_bank_address character varying, v_signature_by character varying, v_signature_pajak_by character varying, v_tukar_faktur_date date, v_remark character varying, v_wh_it_code character varying[], v_wh_it_code_for character varying[], v_wh_it_qty numeric[], v_wh_it_function numeric[], v_wh_it_remark character varying[], v_cus_it_code character varying[], v_icat_midx integer[], v_cus_it_model_no character varying[], v_cus_it_desc character varying[], v_cus_it_qty integer[], v_cus_it_unit_price numeric[], v_cus_it_remark character varying[]) returns void
	language plpgsql
as $$
DECLARE
    rec record;
    v_i integer := 1;
    v_j integer := 1;
    v_pay_idx integer;
    v_method varchar;
    v_bank2 varchar;
    v_type integer;
BEGIN

    /* influence to idc_tb_billing */
    IF v_return_condition = 2 THEN
        UPDATE idc_tb_billing SET
            bill_remain_amount      = bill_remain_amount + v_old_total_amount,
            bill_total_billing_rev  = bill_total_billing_rev + v_old_total_amount
        WHERE bill_code = v_bill_code;
    ELSIF v_return_condition = 3 THEN
        DELETE FROM idc_tb_deposit WHERE dep_type = 'return' AND turn_code = v_code;
    ELSIF v_return_condition = 4 THEN
        DELETE FROM idc_tb_payment WHERE pay_remark = substr(v_code,1,11);
        UPDATE idc_tb_billing SET bill_remain_amount = 0 WHERE bill_code = v_bill_code;
    END IF;

    /* Update idc_tb_return */
    UPDATE idc_tb_return SET
        turn_return_date            = v_return_date,
        turn_received_by            = v_received_by,
        turn_dept                   = v_dept,
        turn_lastupdated_by_account = v_lastupdated_by_account,
        turn_lastupdated_timestamp  = CURRENT_TIMESTAMP,
        turn_revesion_time          = v_revision_time + 1,
        turn_bill_code              = v_bill_code,
        turn_sj_code                = v_sj_code,
        turn_sj_date                = v_sj_date,
        turn_po_no                  = v_po_no,
        turn_po_date                = v_po_date,
        turn_discount               = v_disc,
        turn_vat                    = v_vat,
        turn_amount_qty_unit_price  = v_total_before_vat,
        turn_total_return           = v_total_return,
        turn_cus_to                 = v_cus_to,
        turn_cus_to_name            = v_cus_name,
        turn_cus_to_attn            = v_cus_attn,
        turn_cus_to_address         = v_cus_address,
        turn_npwp                   = v_npwp,
        turn_ship_to                = v_ship_to,
        turn_ship_to_name           = v_ship_name,
        turn_delivery_chk           = v_delivery_chk,
        turn_delivery_by            = v_delivery_by,
        turn_delivery_warehouse     = v_delivery_warehouse,
        turn_delivery_franco        = v_delivery_franco,
        turn_delivery_freight_charge    = v_delivery_freight_charge,
        turn_payment_chk                = v_payment_chk,
        turn_payment_widthin_days       = v_payment_widthin_days,
        turn_payment_sj_inv_fp_tender   = v_payment_sj_inv_fp_tender,
        turn_payment_closing_on         = v_payment_closing_on,
        turn_payment_for_the_month_week = v_payment_for_the_month_week,
        turn_payment_cash_by            = v_payment_cash_by,
        turn_payment_check_by           = v_payment_check_by,
        turn_payment_transfer_by        = v_payment_transfer_by,
        turn_payment_giro_due           = v_payment_giro_due,
        turn_payment_giro_issue         = v_payment_giro_issue,
        turn_payment_bank               = v_bank,
        turn_payment_bank_address       = v_bank_address,
        turn_signature_by               = v_signature_by,
        turn_signature_pajak_by         = v_signature_pajak_by,
        turn_tukar_faktur_date          = v_tukar_faktur_date,
        turn_remark                     = v_remark
    WHERE turn_code = v_code;

    /*Update idc_tb_return_item */
    DELETE FROM idc_tb_return_item WHERE turn_code = v_code;
    WHILE v_cus_it_code[v_i] IS NOT NULL LOOP
        INSERT INTO idc_tb_return_item (
            turn_code, cus_code, it_code, it_model_no, it_desc, icat_midx,
            reit_return_date, reit_qty, reit_unit_price, reit_remark
        ) VALUES (
            v_code, v_cus_to, v_cus_it_code[v_i],v_cus_it_model_no[v_i], v_cus_it_desc[v_i], v_icat_midx[v_i],
            v_return_date, v_cus_it_qty[v_i], v_cus_it_unit_price[v_i], v_cus_it_remark[v_i]
        );
        v_i := v_i + 1;
    END LOOP;

    /* influence to idc_tb_billing */
    IF v_return_condition = 2 THEN
        UPDATE idc_tb_billing SET
        bill_remain_amount      = bill_remain_amount - v_total_return,
        bill_total_billing_rev  = bill_total_billing_rev - v_total_return
        WHERE bill_code = v_bill_code;
    ELSIF v_return_condition = 3 THEN
        INSERT INTO idc_tb_deposit(cus_code, dep_cus_name, turn_code, dep_dept, dep_amount, dep_issued_date, dep_type)
        VALUES (v_cus_to, v_cus_name, v_code, v_dept, v_total_return, v_return_date, 'return');
    ELSIF v_return_condition = 4 THEN
        IF v_payment_chk < 31 THEN       v_method = 'cash';
        ELSIF v_payment_chk < 63 THEN    v_method = 'check';
        ELSIF v_payment_chk < 127 THEN   v_method = 'transfer'; v_bank2  = v_bank;
        ELSIF v_payment_chk >= 128 THEN  v_method = 'giro';
        END IF;

    /* Mengurangi Payment yang sudah ada sebelumnya */
    INSERT INTO idc_tb_payment (bill_code, cus_code, pay_dept, pay_date, pay_paid, pay_inputed_by, pay_remark, pay_note, pay_method, pay_bank)
    VALUES (v_bill_code, v_cus_to, v_dept, v_return_date, -v_total_return, v_received_by, v_code, 'RETURN', v_method, v_bank2);     /* Update total_amount yang ada di idc_tb_billing */        UPDATE idc_tb_billing SET bill_remain_amount = 0 WHERE bill_code = v_bill_code;
    END IF;

    IF v_paper = 0 THEN

        SELECT INTO v_type std_type FROM idc_tb_outstanding WHERE std_idx = v_std_idx;

        /* Update idc_tb_outstanding */
        UPDATE idc_tb_outstanding SET
            cus_code        = v_ship_to,
            std_date        = v_return_date,
            std_received_by = v_received_by
        WHERE std_idx = v_std_idx;

        /*Update idc_tb_outstanding_item */
        DELETE FROM idc_tb_outstanding_item WHERE std_idx = v_std_idx;
        WHILE v_wh_it_code[v_j] IS NOT NULL LOOP
            INSERT INTO idc_tb_outstanding_item (
                std_idx, it_code, istd_it_code_for,istd_qty, istd_function, istd_remark, istd_wh_location, istd_type
            ) VALUES (
                v_std_idx, v_wh_it_code[v_j], v_wh_it_code_for[v_j],
                v_wh_it_qty[v_j], v_wh_it_function[v_j], v_wh_it_remark[v_j],1, v_type
            );
            v_j := v_j + 1;
        END LOOP;

        /* Update idc_tb_incoming */
        UPDATE idc_tb_incoming SET
            cus_code        = v_ship_to,
            inc_date        = v_return_date,
            inc_received_by = v_received_by
        WHERE inc_idx = v_inc_idx;

        /* Update idc_tb_incoming_item */
        DELETE FROM idc_tb_incoming_item WHERE inc_idx = v_inc_idx;
        FOR rec IN SELECT it_code, sum(istd_qty) AS qty FROM idc_tb_outstanding_item
        WHERE std_idx = v_std_idx GROUP BY it_code ORDER BY it_code LOOP
            INSERT INTO idc_tb_incoming_item (inc_idx, init_type, it_code, init_qty, init_wh_location)
            VALUES (v_inc_idx, v_type, rec.it_code, rec.qty, 1);
        END LOOP;
    END IF;

END;
$$
;

alter function idc_updatereturnbilling(varchar, integer, varchar, integer, integer, integer, varchar, date, varchar, varchar, integer, varchar, date, varchar, date, numeric, numeric, numeric, integer, integer, varchar, varchar, varchar, varchar, varchar, varchar, varchar, integer, varchar, varchar, varchar, integer, integer, integer, varchar, date, varchar, varchar, varchar, varchar, date, date, varchar, varchar, varchar, varchar, date, varchar, character varying[], character varying[], numeric[], numeric[], character varying[], character varying[], integer[], character varying[], character varying[], integer[], numeric[], character varying[]) owner to dskim
;

create function idc_updatereturndemo(v_code character varying, v_return_by character varying, v_return_date date, v_cus_code character varying, v_sign_by character varying, v_remark character varying, v_log_by_account character varying, v_it_code character varying[], v_it_qty numeric[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
BEGIN

	/* Update idc_tb_return_demo */
	UPDATE idc_tb_return_demo SET
		red_return_by				= v_return_by,
		red_return_date				= v_return_date,
		red_lastupdated_by_account	= v_log_by_account,
		red_lastupdated_timestamp	= CURRENT_TIMESTAMP,
		red_signature_by			= v_sign_by,
		red_remark					= v_remark,
		red_revesion_time			= red_revesion_time + 1
	WHERE red_code = v_code;

	/* Update idc_tb_return_demo_item */
	DELETE FROM idc_tb_return_demo_item WHERE red_code = v_code;
	WHILE v_it_code[v_i] IS NOT NULL LOOP
		IF v_it_qty[v_i] > 0 THEN
			INSERT INTO idc_tb_return_demo_item (red_code, it_code, rdit_qty)
			VALUES (v_code, v_it_code[v_i], v_it_qty[v_i]);
		END IF;
		v_i := v_i + 1;
	END LOOP;

END;
$$
;

alter function idc_updatereturndemo(varchar, varchar, date, varchar, varchar, varchar, varchar, character varying[], numeric[]) owner to dskim
;

create function idc_updatereturndt(v_code character varying, v_date date, v_issued_by character varying, v_lastupdated_by_account character varying, v_revesion_time integer, v_cus_to character varying, v_cus_name character varying, v_cus_address character varying, v_ship_to character varying, v_ship_name character varying, v_delivery_by character varying, v_delivery_warehouse character varying, v_delivery_franco character varying, v_delivery_freight_charge numeric, v_remark character varying) returns void
	language plpgsql
as $$
BEGIN

	UPDATE idc_tb_return_dt SET
		rdt_date				= v_date,
		rdt_issued_by			= v_issued_by,
		rdt_lastupdated_by_account	= v_lastupdated_by_account,
		rdt_revesion_time		= v_revesion_time + 1,
		rdt_cus_to				= v_cus_to,
		rdt_cus_name			= v_cus_name,
		rdt_cus_address			= v_cus_address,
		rdt_ship_to				= v_ship_to,
		rdt_ship_name			= v_ship_name,
		rdt_delivery_by			= v_delivery_by,
		rdt_delivery_warehouse	= v_delivery_warehouse,
		rdt_delivery_franco		= v_delivery_franco,
		rdt_delivery_freight_charge	= v_delivery_freight_charge,
		rdt_remark				= v_remark
	WHERE rdt_code  = v_code;

END;
$$
;

alter function idc_updatereturndt(varchar, date, varchar, varchar, integer, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, numeric, varchar) owner to dskim
;

create function idc_updatereturnorder(v_code character varying, v_dept character varying, v_ord_code character varying, v_std_idx integer, v_inc_idx integer, v_ord_date date, v_received_by character varying, v_confirm_by character varying, v_po_date date, v_po_no character varying, v_type character varying, v_vat numeric, v_paper integer, v_cus_to character varying, v_cus_to_attn character varying, v_cus_to_address character varying, v_ship_to character varying, v_ship_to_attn character varying, v_ship_to_address character varying, v_bill_to character varying, v_bill_to_attn character varying, v_bill_to_address character varying, v_price_discount numeric, v_price_chk integer, v_delivery_chk integer, v_delivery_by character varying, v_delivery_freight_charge numeric, v_payment_chk integer, v_payment_widthin_days integer, v_payment_closing_on date, v_payment_cash_by character varying, v_payment_check_by character varying, v_payment_transfer_by character varying, v_payment_giro_by character varying, v_sign_by character varying, v_remark character varying, v_lastupdated_by_account character varying, v_wh_it_code character varying[], v_wh_it_code_for character varying[], v_wh_it_qty numeric[], v_wh_it_function numeric[], v_wh_it_remark character varying[], v_cus_it_code character varying[], v_cus_it_qty numeric[], v_cus_it_unit_price numeric[], v_cus_it_remark character varying[]) returns void
	language plpgsql
as $$
DECLARE
    rec record;
    v_i integer := 1;
    v_j integer := 1;
BEGIN

    UPDATE idc_tb_return_order SET
        reor_lastupdated_by_account     = v_lastupdated_by_account,
        reor_lastupdated_timestamp      = CURRENT_TIMESTAMP,
        reor_po_date                    = v_po_date,
        reor_po_no                      = v_po_no,
        reor_received_by                = v_received_by,
        reor_confirm_by                 = v_confirm_by,
        reor_revesion_time              = reor_revesion_time + 1,
        reor_vat                        = v_vat,
        reor_cus_to                     = v_cus_to,
        reor_cus_to_attn                = v_cus_to_attn,
        reor_cus_to_address             = v_cus_to_address,
        reor_ship_to                    = v_ship_to,
        reor_ship_to_attn               = v_ship_to_attn,
        reor_ship_to_address            = v_ship_to_address,
        reor_bill_to                    = v_bill_to,
        reor_bill_to_attn               = v_bill_to_attn,
        reor_bill_to_address            = v_bill_to_address,
        reor_price_discount             = v_price_discount,
        reor_price_chk                  = v_price_chk,
        reor_delivery_chk               = v_delivery_chk,
        reor_delivery_by                = v_delivery_by,
        reor_delivery_freight_charge    = v_delivery_freight_charge,
        reor_payment_chk                = v_payment_chk,
        reor_payment_widthin_days       = v_payment_widthin_days,
        reor_payment_closing_on         = v_payment_closing_on,
        reor_payment_cash_by            = v_payment_cash_by,
        reor_payment_check_by           = v_payment_check_by,
        reor_payment_transfer_by        = v_payment_transfer_by,
        reor_payment_giro_by            = v_payment_giro_by,
        reor_sign_by                    = v_sign_by,
        reor_remark                     = v_remark
    WHERE
        reor_code = v_code;

    DELETE FROM idc_tb_return_order_item WHERE reor_code = v_code;
    WHILE v_cus_it_code[v_i] IS NOT NULL LOOP
        INSERT INTO idc_tb_return_order_item (reor_code, cus_code, roit_dept, it_code, roit_qty, roit_unit_price, roit_remark, roit_date)
        VALUES (v_code, v_cus_to, v_dept, v_cus_it_code[v_i], v_cus_it_qty[v_i], v_cus_it_unit_price[v_i], v_cus_it_remark[v_i], v_po_date);
        v_i := v_i + 1;
    END LOOP;

    IF v_paper = 0 THEN
        /* Update idc_tb_outstanding */
        UPDATE idc_tb_outstanding SET
            cus_code        = v_ship_to,
            std_date        = v_po_date,
            std_received_by = v_received_by
        WHERE std_idx = v_std_idx;

        /* Update idc_tb_outstanding_item */
        DELETE FROM idc_tb_outstanding_item WHERE std_idx = v_std_idx;
        WHILE v_wh_it_code[v_j] IS NOT NULL LOOP
            INSERT INTO idc_tb_outstanding_item (
                std_idx, it_code, istd_it_code_for,istd_qty, istd_function, istd_remark, istd_wh_location, istd_type
            ) VALUES (
                v_std_idx, v_wh_it_code[v_j], v_wh_it_code_for[v_j],
                v_wh_it_qty[v_j], v_wh_it_function[v_j], v_wh_it_remark[v_j], 1, 2
            );
            v_j := v_j + 1;
        END LOOP;

        /* Update idc_tb_incoming */
        UPDATE idc_tb_incoming SET
            cus_code        = v_ship_to,
            inc_date        = v_po_date,
            inc_received_by = v_received_by
        WHERE inc_idx = v_inc_idx;

        /* Update idc_tb_incoming_item */
        DELETE FROM idc_tb_incoming_item WHERE inc_idx = v_inc_idx;
        FOR rec IN SELECT it_code, sum(istd_qty) AS qty FROM idc_tb_outstanding_item WHERE std_idx = v_std_idx GROUP BY it_code ORDER BY it_code LOOP
            INSERT INTO idc_tb_incoming_item (inc_idx, init_type, it_code, init_qty, init_wh_location)
            VALUES (v_inc_idx, 2, rec.it_code, rec.qty, 1);
        END LOOP;
    END IF;

END;
$$
;

alter function idc_updatereturnorder(varchar, varchar, varchar, integer, integer, date, varchar, varchar, date, varchar, varchar, numeric, integer, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, numeric, integer, integer, varchar, numeric, integer, integer, date, varchar, varchar, varchar, varchar, varchar, varchar, varchar, character varying[], character varying[], numeric[], numeric[], character varying[], character varying[], numeric[], numeric[], character varying[]) owner to dskim
;

create function idc_updateservice(v_code character varying, v_reg_no character varying, v_service_date date, v_received_by character varying, v_cus_to character varying, v_cus_name character varying, v_cus_address character varying, v_is_guarantee boolean, v_guarantee_period date, v_signature_by character varying, v_due_date_chk integer, v_days_to_due integer, v_due_date date, v_remark character varying, v_total_disc numeric, v_total_amount numeric, v_lastupdated_by_account character varying, v_it_code character varying[], v_it_model_no character varying[], v_it_sn character varying[], v_it_repair_desc character varying[], v_it_repair_qty integer[], v_it_repair_price numeric[], v_it_repair_remark character varying[], v_it_replace_part_name character varying[], v_it_replace_qty integer[], v_it_replace_price numeric[], v_it_replace_remark character varying[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
	v_j integer := 1;
	v_k integer := 1;
	v_service_paid numeric;
BEGIN

	SELECT INTO v_service_paid SUM(svpay_paid) FROM idc_tb_service_payment WHERE sv_code = v_code;
	IF v_service_paid IS NULL THEN
		v_service_paid = 0;
	END IF;

	UPDATE idc_tb_service SET
		sv_date						= v_service_date,
		sv_lastupdated_by_account	= v_lastupdated_by_account,
		sv_lastupdated_timestamp	= current_timestamp,
		sv_revesion_time			= sv_revesion_time + 1,
		sv_cus_to					= v_cus_to,
		sv_cus_to_name				= v_cus_name,
		sv_cus_to_address			= v_cus_address,
		sv_received_by				= v_received_by,
		sv_is_guarantee				= v_is_guarantee,
		sv_guarantee_period			= v_guarantee_period,
		sv_signature_by				= v_signature_by,
		sv_due_date_chk				= v_due_date_chk,
		sv_days_to_due				= v_days_to_due,
		sv_due_date					= v_due_date,
		sv_remark					= v_remark,
		sv_total_discount			= v_total_disc,
		sv_total_amount				= v_total_amount,
		sv_total_remain				= v_total_amount - v_service_paid
	WHERE sv_code = v_code;

	DELETE FROM idc_tb_service_item WHERE sv_code = v_code;
	WHILE v_it_code[v_i] IS NOT NULL LOOP
		INSERT INTO idc_tb_service_item (sv_code, it_code, svit_model_no, svit_serial_number)
		VALUES (v_code, v_it_code[v_i], v_it_model_no[v_i], v_it_sn[v_i]);
		v_i := v_i + 1;
	END LOOP;

	DELETE FROM idc_tb_service_repair WHERE sv_code = v_code;
	WHILE v_it_repair_desc[v_j] IS NOT NULL LOOP
		INSERT INTO idc_tb_service_repair (sv_code, sv_repair_desc, sv_repair_qty, sv_repair_unit_price, sv_repair_remark)
		VALUES (v_code, v_it_repair_desc[v_j], v_it_repair_qty[v_j], v_it_repair_price[v_j], v_it_repair_remark[v_j]);
		v_j := v_j + 1;
	END LOOP;

	DELETE FROM idc_tb_service_replace WHERE sv_code = v_code;
	WHILE v_it_replace_part_name[v_k] IS NOT NULL AND v_it_replace_part_name[v_k] != '' LOOP
		INSERT INTO idc_tb_service_replace (sv_code, sv_replace_part_name, sv_replace_qty, sv_replace_unit_price, sv_replace_remark)
		VALUES (v_code, v_it_replace_part_name[v_k], v_it_replace_qty[v_k], v_it_replace_price[v_k], v_it_replace_remark[v_k]);
		v_k := v_k + 1;
	END LOOP;

END;
$$
;

alter function idc_updateservice(varchar, varchar, date, varchar, varchar, varchar, varchar, boolean, date, varchar, integer, integer, date, varchar, numeric, numeric, varchar, character varying[], character varying[], character varying[], character varying[], integer[], numeric[], character varying[], character varying[], integer[], numeric[], character varying[]) owner to dskim
;

create function idc_updatesupplier(v_code character varying, v_name character varying, v_full_name character varying, v_representative character varying, v_contact_name character varying, v_contact_phone character varying, v_contact_email character varying, v_attn character varying, v_cc character varying, v_address character varying, v_phone character varying, v_fax character varying, v_remark character varying, v_bank_name character varying, v_bank_swift character varying, v_bank_address character varying, v_bank_acc_no character varying, v_bank_currency character varying, v_bank_acc_name character varying) returns void
	language plpgsql
as $$
BEGIN
	UPDATE idc_tb_supplier SET
		sp_name= v_name,
		sp_full_name= v_full_name,
		sp_representative= v_representative,
		sp_contact_name= v_contact_name,
		sp_contact_phone= v_contact_phone,
		sp_contact_email= v_contact_email,
		sp_contact_attn= v_attn,
		sp_contact_cc= v_cc,
		sp_address= v_address,
		sp_phone= v_phone,
		sp_fax= v_fax,
		sp_remark= v_remark,
		sp_bank_name= v_bank_name,
		sp_bank_currency= v_bank_currency,
		sp_bank_account_name= v_bank_acc_name,
		sp_bank_account_no= v_bank_acc_no,
		sp_bank_address= v_bank_address,
		sp_bank_swift_code= v_bank_swift
	WHERE sp_code= v_code;
END;
$$
;

alter function idc_updatesupplier(varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar) owner to dskim
;

create function idc_updatetotalorderqty(v_cus_code character varying) returns void
	language plpgsql
as $$
DECLARE
	rec record;
	v_inv_cus_code varchar;
	v_inv_it_code varchar;
BEGIN
	FOR rec IN SELECT it_code, sum(odit_oo_qty) AS oo, sum(odit_ok_qty) AS ok
	FROM idc_tb_order_item WHERE cus_code = v_cus_code GROUP BY it_code
	LOOP
		SELECT INTO v_inv_cus_code, v_inv_it_code cus_code, it_code
		FROM idc_tb_apotik_inv WHERE cus_code = v_cus_code AND it_code = rec.it_code;

		IF NOT FOUND THEN
			-- If they don't have anything,  add new row for this item
			-- inv_last_account_date mean the last update stock qty
			INSERT INTO idc_tb_apotik_inv(cus_code, it_code, inv_ok, inv_oo, inv_last_account_date)
			VALUES (v_cus_code, rec.it_code, rec.ok, rec.oo, CURRENT_TIMESTAMP);
		ELSE
			-- If they have ever had, add qty for exist item
			UPDATE idc_tb_apotik_inv SET inv_ok = rec.ok, inv_oo = rec.oo
			WHERE cus_code = v_cus_code AND it_code = rec.it_code;
		END IF;
	END LOOP;
END;
$$
;

alter function idc_updatetotalorderqty(varchar) owner to dskim
;

create function idc_updatewarranty(v_idx integer, v_name character varying, v_sex character varying, v_address character varying, v_city character varying, v_zip_code character varying, v_contact_phone character varying, v_contact_hphone character varying, v_contact_email character varying, v_it_product integer, v_it_code character varying, v_it_model_no character varying, v_warranty_no character varying, v_serial_no character varying, v_purchase_date date, v_purchase_store character varying, v_suggest character varying, v_lastupdated_by_account character varying) returns void
	language plpgsql
as $$
BEGIN
	UPDATE idc_tb_warranty SET
		it_code				= v_it_code,
		wr_warranty_no		= v_warranty_no,
		wr_serial_no		= v_serial_no,
		wr_product			= v_it_product,
		wr_it_model_no		= v_it_model_no,
		wr_purchase_date	= v_purchase_date,
		wr_purchase_store	= v_purchase_store,
		wr_suggest			= v_suggest,
		wr_lastupdated_by_account	= v_lastupdated_by_account,
		wr_lastupdated_timestamp	= CURRENT_TIMESTAMP,
		wr_cus_name			= v_name,
		wr_cus_sex			= v_sex,
		wr_cus_phone		= v_contact_phone,
		wr_cus_hphone		= v_contact_hphone,
		wr_cus_email		= v_contact_email,
		wr_cus_address		= v_address,
		wr_cus_city			= v_city,
		wr_cus_zip_code		= v_zip_code
	WHERE wr_idx = v_idx;
END;
$$
;

alter function idc_updatewarranty(integer, varchar, varchar, varchar, varchar, varchar, varchar, varchar, varchar, integer, varchar, varchar, varchar, varchar, date, varchar, varchar, varchar) owner to dskim
;

create function idc_uploadfilearchieve(v_source character, v_code character, v_inputted_by_account character varying, v_file_name character varying[], v_file_path character varying[], v_file_type character varying[], v_file_desc character varying[]) returns void
	language plpgsql
as $$
DECLARE
	v_i integer := 1;
BEGIN

	IF (v_source = 'Order') THEN
		WHILE v_file_name[v_i] IS NOT NULL AND v_file_name[v_i] != '' LOOP
			INSERT INTO idc_tb_order_file (ord_code, ordf_inputted_by_account, ordf_file_name, ordf_file_path, ordf_file_type, ordf_file_desc)
			VALUES (v_code, v_inputted_by_account, v_file_name[v_i], v_file_path[v_i], v_file_type[v_i], v_file_desc[v_i]);
			v_i := v_i + 1;
		END LOOP;
	ELSIF (v_source = 'Return Order') THEN
		WHILE v_file_name[v_i] IS NOT NULL AND v_file_name[v_i] != '' LOOP
			INSERT INTO idc_tb_return_order_file (reor_code, reorf_inputted_by_account, reorf_file_name, reorf_file_path, reorf_file_type, reorf_file_desc)
			VALUES (v_code, v_inputted_by_account, v_file_name[v_i], v_file_path[v_i], v_file_type[v_i], v_file_desc[v_i]);
			v_i := v_i + 1;
		END LOOP;
	ELSIF (v_source = 'Billing') THEN
		WHILE v_file_name[v_i] IS NOT NULL AND v_file_name[v_i] != '' LOOP
			INSERT INTO idc_tb_billing_file (bill_code, billf_inputted_by_account, billf_file_name, billf_file_path, billf_file_type, billf_file_desc)
			VALUES (v_code, v_inputted_by_account, v_file_name[v_i], v_file_path[v_i], v_file_type[v_i], v_file_desc[v_i]);
			v_i := v_i + 1;
		END LOOP;
	ELSIF (v_source = 'Return Billing') THEN
		WHILE v_file_name[v_i] IS NOT NULL AND v_file_name[v_i] != '' LOOP
			INSERT INTO idc_tb_return_file (turn_code, turnf_inputted_by_account, turnf_file_name, turnf_file_path, turnf_file_type, turnf_file_desc)
			VALUES (v_code, v_inputted_by_account, v_file_name[v_i], v_file_path[v_i], v_file_type[v_i], v_file_desc[v_i]);
			v_i := v_i + 1;
		END LOOP;
	END IF;

END;
$$
;

alter function idc_uploadfilearchieve(char, char, varchar, character varying[], character varying[], character varying[], character varying[]) owner to dskim
;

