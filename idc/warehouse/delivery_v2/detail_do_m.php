<?php
function getDOItem($version = FALSE, $out_idx = FALSE, $source = FALSE, $arr = FALSE) {

	if ($version == 'v1')
	{
		switch ($source) {
		    case 'info':
			$sql = "SELECT *,
					".ZKP_SQL."_isLockedCondition(book_doc_type,book_doc_ref) AS is_locked
				FROM
					".ZKP_SQL."_tb_customer
					JOIN ".ZKP_SQL."_tb_booking using(cus_code)
					JOIN ".ZKP_SQL."_tb_outgoing on book_idx=out_book_idx
				WHERE out_idx = $out_idx";
			$result =& query($sql);
			$col =& fetchRowAssoc($result);
			break;
		    case 'cus_item':
			if($arr['out_doc_type'] == 1) {
				$sql = "SELECT it_code, i.it_model_no, i.it_desc, biit_qty as qty, biit_remark FROM ".ZKP_SQL."_tb_billing JOIN ".ZKP_SQL."_tb_billing_item USING (bill_code) JOIN ".ZKP_SQL."_tb_item AS i USING (it_code) WHERE bill_code = '{$arr['out_doc_ref']}' ORDER BY it_code";
			} else if($arr['out_doc_type'] == 2) {
				$sql = "SELECT it_code, it_model_no, it_desc, odit_qty as qty, odit_remark FROM ".ZKP_SQL."_tb_order JOIN ".ZKP_SQL."_tb_order_item USING (ord_code) JOIN ".ZKP_SQL."_tb_item USING (it_code) WHERE ord_code = '{$arr['out_doc_ref']}' ORDER BY it_code";
			} else if($arr['out_doc_type'] == 3) {
				$sql = "SELECT it_code, it_model_no, it_desc, dtit_qty as qty, dtit_remark FROM ".ZKP_SQL."_tb_dt JOIN ".ZKP_SQL."_tb_dt_item USING (dt_code) JOIN ".ZKP_SQL."_tb_item USING (it_code) WHERE dt_code = '{$arr['out_doc_ref']}' ORDER BY it_code";
			} else if($arr['out_doc_type'] == 4) {
				$sql = "SELECT it_code, it_model_no, it_desc, dfit_qty as qty, dfit_remark FROM ".ZKP_SQL."_tb_df JOIN ".ZKP_SQL."_tb_df_item USING (df_code) JOIN ".ZKP_SQL."_tb_item USING (it_code) WHERE df_code = '{$arr['out_doc_ref']}' ORDER BY it_code";
			} else if($arr['out_doc_type'] == 5) {
				$sql = "SELECT it_code, it_model_no, it_desc, drit_qty as qty, drit_remark FROM ".ZKP_SQL."_tb_dr JOIN ".ZKP_SQL."_tb_dr_item USING (dr_code) JOIN ".ZKP_SQL."_tb_item USING (it_code) WHERE dr_code = '{$arr['out_doc_ref']}' ORDER BY it_code";
			} else if($arr['out_doc_type'] == 6) {
				$sql = "SELECT it_code, it_model_no, it_desc, rqit_qty as qty, rqit_remark FROM ".ZKP_SQL."_tb_request JOIN ".ZKP_SQL."_tb_request_item USING (req_code) JOIN ".ZKP_SQL."_tb_item USING (it_code) WHERE req_code = '{$arr['out_doc_ref']}' ORDER BY it_code";
			} 
			$col = query($sql);
			break;
		    case 'book_item':
			$sql = "
			SELECT
			  a.it_code,
			  a.icat_midx,
			  a.it_model_no,
			  a.it_type,
			  a.it_desc,
			  (select it_model_no from ".ZKP_SQL."_tb_item where it_code=b.boit_it_code_for) AS it_used_for,
			  b.boit_qty,
			  b.boit_function,
			  b.boit_remark,
			  b.boit_type,
			  a.it_ed
			FROM
			  ".ZKP_SQL."_tb_booking AS c
			  JOIN ".ZKP_SQL."_tb_booking_item AS b USING(book_idx)
			  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
			WHERE c.book_doc_ref = '".trim($arr["book_doc_ref"])."'
			ORDER BY a.it_code";
			$col = query($sql);
			break;
		    case 'out_item':
			$sql = "
			SELECT 
			  a.it_code,
			  a.it_model_no,
			  a.it_desc,
			  b.otit_qty
			FROM
			  ".ZKP_SQL."_tb_outgoing AS c
			  JOIN ".ZKP_SQL."_tb_outgoing_item AS b USING(out_idx)
			  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
			WHERE c.out_idx = $out_idx
			ORDER BY a.it_code
			";
			$col = query($sql);
			break;
		    case 'ed_item':
			$sql = "
			SELECT 
			  a.it_code,
			  a.it_model_no,
			  b.oted_wh_location,
			  to_char(b.oted_date,'Mon-YYYY') AS exp_date,
			  b.oted_qty
			FROM
			  ".ZKP_SQL."_tb_outgoing AS c
			  JOIN ".ZKP_SQL."_tb_outgoing_ed AS b USING(out_idx)
			  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
			WHERE c.out_idx = $out_idx
			ORDER BY a.it_code, b.oted_date
			";
			$col = query($sql);
			break;
		}
	}
	else if ($version == 'v2')
	{
		switch ($source) {
		    case 'info':
			$sql = "SELECT *,".ZKP_SQL."_isLockedCondition(book_doc_type,book_doc_ref) AS is_locked FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_booking using(cus_code) JOIN ".ZKP_SQL."_tb_outgoing_v2 on book_idx=out_book_idx WHERE out_idx = $out_idx";
			$result =& query($sql);
			$col =& fetchRowAssoc($result);
			break;
		    case 'cus_item':
			if($arr['out_doc_type'] == 'DO Billing') {
				$sql = "SELECT it_code, i.it_model_no, i.it_desc, biit_qty AS qty, biit_remark AS remark FROM ".ZKP_SQL."_tb_billing JOIN ".ZKP_SQL."_tb_billing_item USING (bill_code) JOIN ".ZKP_SQL."_tb_item AS i USING (it_code) WHERE bill_code = '{$arr['out_doc_ref']}' ORDER BY it_code";
			} else if($arr['out_doc_type'] == 'DO Order') {
				$sql = "SELECT it_code, it_model_no, it_desc, odit_qty AS qty, odit_remark AS remark FROM ".ZKP_SQL."_tb_order JOIN ".ZKP_SQL."_tb_order_item USING (ord_code) JOIN ".ZKP_SQL."_tb_item USING (it_code) WHERE ord_code = '{$arr['out_doc_ref']}' ORDER BY it_code";
			} else if($arr['out_doc_type'] == 'DT') {
				$sql = "SELECT it_code, it_model_no, it_desc, dtit_qty AS qty, dtit_remark AS remark FROM ".ZKP_SQL."_tb_dt JOIN ".ZKP_SQL."_tb_dt_item USING (dt_code) JOIN ".ZKP_SQL."_tb_item USING (it_code) WHERE dt_code = '{$arr['out_doc_ref']}' ORDER BY it_code";
			} else if($arr['out_doc_type'] == 'DF') {
				$sql = "SELECT it_code, it_model_no, it_desc, dfit_qty AS qty, dfit_remark AS remark FROM ".ZKP_SQL."_tb_df JOIN ".ZKP_SQL."_tb_df_item USING (df_code) JOIN ".ZKP_SQL."_tb_item USING (it_code) WHERE df_code = '{$arr['out_doc_ref']}' ORDER BY it_code";
			} else if($arr['out_doc_type'] == 'DR') {
				$sql = "SELECT it_code, it_model_no, it_desc, drit_qty AS qty, drit_remark AS remark FROM ".ZKP_SQL."_tb_dr JOIN ".ZKP_SQL."_tb_dr_item USING (dr_code) JOIN ".ZKP_SQL."_tb_item USING (it_code) WHERE dr_code = '{$arr['out_doc_ref']}' ORDER BY it_code";
			} else if($arr['out_doc_type'] == 'DM') {
				$sql = "SELECT it_code, it_model_no, it_desc, rqit_qty AS qty, rqit_remark AS remark FROM ".ZKP_SQL."_tb_request JOIN ".ZKP_SQL."_tb_request_item USING (req_code) JOIN ".ZKP_SQL."_tb_item USING (it_code) WHERE req_code = '{$arr['out_doc_ref']}' ORDER BY it_code";
			} 
			$col = query($sql);
			break;
		    case 'book_item':
			$sql = "
			SELECT
			  a.it_code,
			  a.icat_midx,
			  a.it_model_no,
			  a.it_type,
			  a.it_desc,
			  (select it_model_no from ".ZKP_SQL."_tb_item where it_code=b.boit_it_code_for) AS it_used_for,
			  b.boit_qty,
			  b.boit_function,
			  b.boit_remark,
			  b.boit_type,
			  a.it_ed
			FROM
			  ".ZKP_SQL."_tb_booking AS c
			  JOIN ".ZKP_SQL."_tb_booking_item AS b USING(book_idx)
			  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
			WHERE c.book_doc_ref = '".trim($arr["book_doc_ref"])."'
			ORDER BY a.it_code";
			$col = query($sql);
			break;
		    case 'out_item':
			$sql = "
			SELECT 
			  a.it_code,
			  a.it_model_no,
			  a.it_desc,
			  b.otit_qty
			FROM
			  ".ZKP_SQL."_tb_outgoing_v2 AS c
			  JOIN ".ZKP_SQL."_tb_outgoing_item_v2 AS b USING(out_idx)
			  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
			WHERE c.out_idx = $out_idx
			ORDER BY a.it_code
			";
			$col = query($sql);
			break;
		    case 'ed_item':
			$sql = "
			SELECT 
			  a.it_code,
			  a.it_model_no,
			  b.oted_wh_location,
			  to_char(b.oted_expired_date,'Mon-YYYY') AS exp_date,
			  b.oted_qty
			FROM
			  ".ZKP_SQL."_tb_outgoing_v2 AS c
			  JOIN ".ZKP_SQL."_tb_outgoing_stock_ed AS b USING(out_idx)
			  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
			WHERE c.out_idx = $out_idx
			ORDER BY oted_timestamp, a.it_code, b.oted_expired_date
			";
			$col = query($sql);
			break;
		    case 'rev_item':
			$sql = "
			SELECT trim(it_code) AS it_code, it_model_no, boit_qty, it_ed
			FROM
			  ".ZKP_SQL."_tb_booking
			  JOIN ".ZKP_SQL."_tb_booking_revised USING(book_idx)
			  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
			WHERE book_doc_ref = '".trim($arr["book_doc_ref"])."' AND boit_cfm_timestamp is null
			ORDER BY it_code
			";
			$col = query($sql);
			break;
		}
	}
	return $col;
}

function getReturnItem($_inc_idx = FALSE, $_std_idx = FALSE, $source = FALSE, $arr = FALSE) {
	switch ($source) {
		case 'info_confirm':
			$sql =	"SELECT *,
				".ZKP_SQL."_getReturnReference('doc', std_doc_type, std_doc_ref) AS inv_no,
				".ZKP_SQL."_getReturnReference('date', std_doc_type, std_doc_ref) AS inv_date
			      FROM ".ZKP_SQL."_tb_outstanding JOIN ".ZKP_SQL."_tb_customer using(cus_code) 
			      WHERE std_idx = '$_std_idx'";
			$col = query($sql);
			$col =& fetchRowAssoc($col);
			break;
		case 'info_detail':
			$sql =	"SELECT *,
					".ZKP_SQL."_isLockedConditionReturn(inc_idx) AS is_locked, 
					(SELECT std_revision_time FROM ".ZKP_SQL."_tb_outstanding WHERE std_idx = $_std_idx) AS revision_time,
					".ZKP_SQL."_getReturnReference('doc', inc_doc_type, inc_doc_ref) AS inv_no,
					".ZKP_SQL."_getReturnReference('date', inc_doc_type, inc_doc_ref) AS inv_date
				FROM ".ZKP_SQL."_tb_incoming join ".ZKP_SQL."_tb_customer using(cus_code) WHERE inc_idx = $_inc_idx";
			$col = query($sql);
			$col =& fetchRowAssoc($col);
			break;		
		case 'cus_item':
			if($arr['inc_doc_type'] == 'Return Billing') {
				$sql = "SELECT a.it_code, a.it_model_no, a.it_desc, reit_qty AS qty, reit_remark AS remark FROM ".ZKP_SQL."_tb_return JOIN ".ZKP_SQL."_tb_return_item USING (turn_code) JOIN ".ZKP_SQL."_tb_item AS a USING(it_code) WHERE turn_code = '".trim($arr['inc_doc_ref'])."'";
			} else if($arr['inc_doc_type'] == 'Return Order') {
				$sql = "SELECT a.it_code, a.it_model_no, a.it_desc, roit_qty AS qty, roit_remark AS remark FROM ".ZKP_SQL."_tb_return_order JOIN ".ZKP_SQL."_tb_return_order_item USING (reor_code) JOIN ".ZKP_SQL."_tb_item AS a USING(it_code) WHERE reor_code = '".trim($arr['inc_doc_ref'])."'";
			} else if($arr['inc_doc_type'] == 'Return DT') {
				$sql = "SELECT a.it_code, a.it_model_no, a.it_desc, rdtit_qty AS qty, rdtit_remark AS remark FROM ".ZKP_SQL."_tb_return_dt JOIN ".ZKP_SQL."_tb_return_dt_item USING (rdt_code) JOIN ".ZKP_SQL."_tb_item AS a USING(it_code) WHERE rdt_code = '".trim($arr['inc_doc_ref'])."'";
			}
			$col =& query($sql);
			break;
		case 'std_item':
			$sql = "
			SELECT
			  a.it_code,
			  b.istd_it_code_for,
			  a.it_model_no,
			  a.it_desc,
			  b.istd_qty,
			  b.istd_function,
			  b.istd_remark
			FROM ".ZKP_SQL."_tb_outstanding_item as b JOIN ".ZKP_SQL."_tb_item as a USING(it_code)
			WHERE std_idx = $_std_idx
			ORDER BY it_code,istd_idx";
			$col =& query($sql);
			break;
		case 'inc_item':
			$sql = "
			SELECT
			  a.it_code,
			  a.it_model_no,
			  a.it_desc,
			  a.it_ed,
			  b.init_qty AS qty,
			  b.init_stock_qty,
			  b.init_demo_qty,
			  b.init_reject_qty
			FROM ".ZKP_SQL."_tb_incoming_item as b JOIN ".ZKP_SQL."_tb_item as a USING(it_code)
			WHERE inc_idx = $_inc_idx
			ORDER BY it_code";
			$col =& query($sql);
			break;
		case 'stock_item':
			$sql = "
			SELECT
			  a.it_code,
			  a.it_model_no,
			  ined_wh_location,
			  to_char(ined_expired_date, 'Mon-YYYY') AS expired_date,
			  ined_qty
			FROM ".ZKP_SQL."_tb_incoming_stock_ed_v2 as b JOIN ".ZKP_SQL."_tb_item as a USING(it_code)
			WHERE inc_idx = $_inc_idx
			ORDER BY it_code,ined_expired_date";
			$col =& query($sql);
			break;
		case 'demo_item':
			$sql = "
			SELECT
			  a.it_code,
			  a.it_model_no,
			  to_char(ided_expired_date, 'Mon-YYYY') AS expired_date,
			  ided_qty
			FROM ".ZKP_SQL."_tb_incoming_ed_demo as b JOIN ".ZKP_SQL."_tb_item as a USING(it_code)
			WHERE inc_idx = $_inc_idx
			ORDER BY it_code,ided_expired_date";
			$col =& query($sql);
			break;
		case 'reject_item':
			$sql = "
			SELECT
			  it_code, rjit_serial_number, to_char(rjit_warranty,'Mon-YYYY') AS warranty, rjit_desc
			FROM ".ZKP_SQL."_tb_reject JOIN ".ZKP_SQL."_tb_reject_item USING(rjt_idx) WHERE rjt_doc_idx = $_inc_idx AND rjt_doc_type = 1 ORDER BY it_code";
			$col =& query($sql);
			break;
	}
	return $col;
}