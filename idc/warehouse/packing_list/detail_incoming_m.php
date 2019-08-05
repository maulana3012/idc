<?php
function getPLConfirm($_type = FALSE, $_idx = FALSE, $_pl_no = FALSE, $source = FALSE) {

	if($_type == 'PL')
	{
		switch ($source) {
			case 'info':
				$sql = "SELECT * FROM ".ZKP_SQL."_tb_pl AS pl WHERE pl_idx = $_idx";
				$result =& query($sql);
				$col =& fetchRowAssoc($result);
				break;
			case 'item':
				$sql = "SELECT 
					  it.icat_midx, it.it_code, it.it_ed,
					  substr(plit.plit_item,1,15) AS plit_item,
					  substr(plit.plit_desc,1,28) || '...' AS plit_desc,
					  plit.plit_qty, plit.plit_remark, plit.plit_attribute,
					  plit.plit_qty - ".ZKP_SQL."_arrivedQty(1,pl.pl_idx::varchar, plit.it_code) AS remain_qty
					FROM
					  ".ZKP_SQL."_tb_pl AS pl
					  JOIN ".ZKP_SQL."_tb_pl_item AS plit USING(pl_idx)
					  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
					WHERE pl.pl_idx = $_idx
					ORDER BY plit.it_code";
				$col =& query($sql);
				break;
			case 'in_item':
				$sql = "SELECT
					  'v1'||a.inpl_idx AS inpl_idx, to_char(a.inpl_checked_date,'dd-Mon-YY') AS checked_date, a.inpl_inv_no, 'v1' AS version, inpl_checked_date,
					  c.it_code, c.it_model_no, c.it_desc, b.init_qty,
					  CASE
						WHEN (select DISTINCT(inpl_idx) FROM ".ZKP_SQL."_tb_expired_pl WHERE inpl_idx = a.inpl_idx) is not null THEN true
						else false
					  END AS inpl_has_ed,
					  'detail_confirm_pl.php?_ver=v1&_code='||a.pl_idx||'&_inpl_idx='||b.inpl_idx AS go_page
					FROM
					  ".ZKP_SQL."_tb_in_pl AS a
					  JOIN ".ZKP_SQL."_tb_in_pl_item AS b USING(inpl_idx)
					  JOIN ".ZKP_SQL."_tb_item AS c USING(it_code)
					WHERE a.pl_idx = $_idx AND init_qty > 0
						UNION
					SELECT
					  'v2'||a.inpl_idx AS inpl_idx, to_char(a.inpl_checked_date,'dd-Mon-YY') AS checked_date, a.inpl_inv_no, 'v2' AS version, inpl_checked_date,
					  c.it_code, c.it_model_no, c.it_desc, b.init_qty,
					  CASE
						WHEN (select DISTINCT(inpl_idx) FROM ".ZKP_SQL."_tb_in_pl_item_ed WHERE inpl_idx = a.inpl_idx) is not null THEN true
						else false
					  END AS inpl_has_ed,
					  'detail_confirm_pl.php?_ver=v2&_code='||a.pl_idx||'&_inpl_idx='||b.inpl_idx AS go_page
					FROM
					  ".ZKP_SQL."_tb_in_pl_v2 AS a
					  JOIN ".ZKP_SQL."_tb_in_pl_item_v2 AS b USING(inpl_idx)
					  JOIN ".ZKP_SQL."_tb_item AS c USING(it_code)
					WHERE a.pl_idx = $_idx AND init_qty > 0
					ORDER BY inpl_checked_date, inpl_idx, it_code";
				$col =& query($sql);
				break;
			case 'in_item_ed':
				$sql = "SELECT
					  a.it_code,
					  a.it_model_no,
					  to_char(b.epl_expired_date,'Mon-YYYY') AS expired_date,
					  epl_expired_date AS date_expired,
					  epl_qty AS qty
					FROM ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_expired_pl AS b USING(it_code) 
					WHERE b.inpl_idx = $_idx
						UNION
					SELECT
					  a.it_code,
					  a.it_model_no,
					  to_char(b.ined_expired_date,'Mon-YYYY') AS expired_date,
					  ined_expired_date AS date_expired,
					  ined_qty AS qty
					FROM ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_in_pl_item_ed AS b USING(it_code) 
					WHERE b.inpl_idx = $_idx
					ORDER BY it_code, date_expired";
				$col =& query($sql);
				break;
		}
	}
	else if($_type == 'Claim')
	{
		switch ($source) {
			case 'info':
				$sql = "SELECT * FROM ".ZKP_SQL."_tb_claim WHERE cl_idx = $_idx";
				$result =& query($sql);
				$col =& fetchRowAssoc($result);
				break;
			case 'item':
				$sql = "SELECT 
					  it.icat_midx,
					  it.it_code,
					  it.it_ed,
					  substr(it.it_model_no,1,15) AS plit_item,
					  substr(it.it_desc,1,28) || '...' AS plit_desc,
					  clit.clit_qty,
					  clit.clit_remark,
					  clit.clit_attribute,
					  clit.clit_qty - ".ZKP_SQL."_arrivedQty(2,cl.cl_idx::varchar, clit.it_code) AS remain_qty
					FROM ".ZKP_SQL."_tb_claim AS cl JOIN ".ZKP_SQL."_tb_claim_item AS clit USING(cl_idx) JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
					WHERE cl.cl_idx = $_idx
					ORDER BY clit.it_code";
				$col =& query($sql);
				break;
			case 'in_item':
				$sql = "SELECT
					  'v1'||a.incl_idx AS incl_idx, to_char(a.incl_checked_date,'dd-Mon-YY') AS checked_date, incl_checked_date AS in_date, a.incl_inv_no,
					  c.it_code, c.it_model_no, c.it_desc, b.init_qty, 
					  CASE
					    WHEN (select DISTINCT(incl_idx) FROM ".ZKP_SQL."_tb_expired_claim WHERE incl_idx = a.incl_idx) is not null THEN true
					    else false
					  END AS incl_has_ed,
					  'detail_confirm_claim.php?_ver=v1&_code='||a.cl_idx||'&_incl_idx='||b.incl_idx AS go_page
					FROM
					  ".ZKP_SQL."_tb_in_claim AS a
					  JOIN ".ZKP_SQL."_tb_in_claim_item AS b USING(incl_idx)
					  JOIN ".ZKP_SQL."_tb_item AS c USING(it_code)
					WHERE a.cl_idx = $_idx
						UNION
					SELECT
					  'v2'||a.incl_idx AS incl_idx, to_char(a.incl_checked_date,'dd-Mon-YY') AS checked_date, incl_checked_date AS in_date, a.incl_inv_no,
					  c.it_code, c.it_model_no, c.it_desc, b.init_qty, 
					  CASE
					    WHEN (select DISTINCT(incl_idx) FROM ".ZKP_SQL."_tb_in_claim_item_ed WHERE incl_idx = a.incl_idx) is not null THEN true
					    else false
					  END AS incl_has_ed,
					  'detail_confirm_claim.php?_ver=v2&_code='||a.cl_idx||'&_incl_idx='||b.incl_idx AS go_page
					FROM
					  ".ZKP_SQL."_tb_in_claim_v2 AS a
					  JOIN ".ZKP_SQL."_tb_in_claim_item_v2 AS b USING(incl_idx)
					  JOIN ".ZKP_SQL."_tb_item AS c USING(it_code)
					WHERE a.cl_idx = $_idx
					ORDER BY in_date, incl_idx, it_code";
				$col =& query($sql);
				break;
			case 'in_item_ed':
				$sql = "SELECT
					  a.it_code, a.it_model_no,
					  to_char(b.ecl_expired_date,'Mon-YYYY') AS expired_date, ecl_expired_date AS date_expired,
					  b.ecl_qty AS qty
					FROM ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_expired_claim AS b USING(it_code) 
					WHERE b.incl_idx = $_idx
						UNION
					SELECT
					  a.it_code, a.it_model_no,
					  to_char(b.ined_expired_date,'Mon-YYYY') AS expired_date, ined_expired_date AS date_expired, 
					  ined_qty AS qty
					FROM ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_in_claim_item_ed AS b USING(it_code) 
					WHERE b.incl_idx = $_idx
					ORDER BY it_code, date_expired";
				$col =& query($sql);
				break;
		}
	}
	else if($_type == 'Local')
	{
		switch ($source) {
			case 'info':
				$sql = "SELECT * FROM ".ZKP_SQL."_tb_po_local JOIN ".ZKP_SQL."_tb_pl_local AS pl USING(po_code) WHERE po_code = '$_idx' and pl_no = '$_pl_no'";
				$result =& query($sql);
				$col =& fetchRowAssoc($result);
				break;
			case 'item':
				$sql = "SELECT 
					  it.icat_midx,
					  it.it_code,
					  it.it_ed,
					  substr(it_model_no,1,15) AS plit_item,
					  substr(it_desc,1,28) AS plit_desc,
					  plit.plit_qty,
					  (SELECT poit_unit FROM ".ZKP_SQL."_tb_po_local_item AS poit WHERE po_code='$_idx' and poit.it_code=plit.it_code limit 1) AS plit_unit,
					  plit.plit_qty - ".ZKP_SQL."_arrivedQty(3, po_code|| '-' ||pl_no, it.it_code) AS remain_qty
					FROM
					  ".ZKP_SQL."_tb_pl_local AS pl
					  JOIN ".ZKP_SQL."_tb_pl_local_item AS plit USING(po_code, pl_no)
					  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
					WHERE po_code = '$_idx' and pl_no = $_pl_no
					ORDER BY it_code";
				$col =& query($sql);
				break;
			case 'in_item':
				$sql = "SELECT
					  'v1'||a.inlc_idx AS inlc_idx, to_char(a.inlc_checked_date,'dd-Mon-YY') AS checked_date, inlc_checked_date AS in_date, po_code || ' #' || pl_no AS invoice_no,
					  c.it_code, c.it_model_no, c.it_desc, b.init_qty,
					  CASE
					    WHEN (select DISTINCT(inlc_idx) FROM ".ZKP_SQL."_tb_expired_local WHERE inlc_idx = a.inlc_idx) is not null THEN true
					    else false
					  END AS inpl_has_ed,
					  'detail_confirm_pl_local.php?_ver=v1&_inlc_idx='||b.inlc_idx AS go_page
					FROM
					  ".ZKP_SQL."_tb_in_local AS a
					  JOIN ".ZKP_SQL."_tb_in_local_item AS b USING(inlc_idx)
					  JOIN ".ZKP_SQL."_tb_item AS c USING(it_code)
					WHERE po_code = '$_idx' and pl_no = $_pl_no
						UNION
					SELECT
					  'v2'||a.inlc_idx AS inlc_idx, to_char(a.inlc_checked_date,'dd-Mon-YY') AS checked_date, inlc_checked_date AS in_date, po_code || ' #' || pl_no AS invoice_no,
					  c.it_code, c.it_model_no, c.it_desc, b.init_qty,
					  CASE
					    WHEN (select DISTINCT(inlc_idx) FROM ".ZKP_SQL."_tb_in_local_item_ed WHERE inlc_idx = a.inlc_idx) is not null THEN true
					    else false
					  END AS inpl_has_ed,
					  'detail_confirm_pl_local.php?_ver=v2&_inlc_idx='||b.inlc_idx AS go_page
					FROM
					  ".ZKP_SQL."_tb_in_local_v2 AS a
					  JOIN ".ZKP_SQL."_tb_in_local_item_v2 AS b USING(inlc_idx)
					  JOIN ".ZKP_SQL."_tb_item AS c USING(it_code)
					WHERE po_code = '$_idx' and pl_no = $_pl_no
					ORDER BY in_date, inlc_idx, it_code";
				$col =& query($sql);
				break;
			case 'in_item_ed':
				$sql = "SELECT
					  a.it_code, a.it_model_no, 
					  to_char(b.elc_expired_date,'Mon-YYYY') AS expired_date, elc_expired_date AS date_expired,
					  b.elc_qty AS qty
					FROM ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_expired_local AS b USING(it_code) 
					WHERE b.inlc_idx = $_idx
					ORDER BY a.it_code, b.elc_expired_date
						UNION
					SELECT
					  a.it_code, a.it_model_no,
					  to_char(b.ined_expired_date,'Mon-YYYY') AS expired_date, ined_expired_date AS date_expired, 
					  ined_qty AS qty
					FROM ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_in_local_item_ed AS b USING(it_code) 
					WHERE b.incl_idx = $_idx
					ORDER BY it_code, date_expired";
				$col =& query($sql);
				break;
		}
	}

	return $col;
}

function getPLDetail($version = FALSE, $_type = FALSE, $_idx = FALSE, $_in_idx = FALSE, $source = FALSE, $arr = FALSE) {

	if($_type == 'PL')
	{
		if ($version == 'v1')
		{
			switch ($source) {
				case 'info':
					$sql = "SELECT *
						FROM
						  ".ZKP_SQL."_tb_pl AS pl
						  JOIN ".ZKP_SQL."_tb_in_pl AS inpl USING(pl_idx)
						WHERE
						  pl.pl_idx = $_idx AND inpl.inpl_idx = $_in_idx";
					$result =& query($sql);
					$col =& fetchRowAssoc($result);
					break;
				case 'item':
					$sql = "SELECT
						  it.it_code,
						  it.it_model_no,
						  it.it_desc,
						  init.init_qty
						FROM ".ZKP_SQL."_tb_item AS it JOIN ".ZKP_SQL."_tb_in_pl_item AS init USING(it_code) 
						WHERE init.inpl_idx = $_in_idx AND init_qty > 0
						ORDER BY it.it_code";
					$col =& query($sql);
					break;
				case 'item_ed':
					$sql = "SELECT
						 a.it_code,
						 a.it_model_no,
						 to_char(b.epl_expired_date,'Mon-YYYY') AS expired_date,
						 b.epl_qty AS qty
						FROM ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_expired_pl AS b USING(it_code) 
						WHERE b.inpl_idx = $_in_idx
						ORDER BY a.it_code, b.epl_expired_date";
					$col =& query($sql);
					break;
			}
		}
		else if ($version == 'v2')
		{
			switch ($source) {
				case 'info':
					$sql = "SELECT *
						FROM
						  ".ZKP_SQL."_tb_pl AS pl
						  JOIN ".ZKP_SQL."_tb_in_pl_v2 AS inpl USING(pl_idx)
						WHERE
						  pl.pl_idx = $_idx AND inpl.inpl_idx = $_in_idx";
					$result =& query($sql);
					$col =& fetchRowAssoc($result);
					break;
				case 'item':
					$sql = "SELECT
						  it.it_code,
						  it.it_model_no,
						  it.it_desc,
						  init.init_qty
						FROM ".ZKP_SQL."_tb_item AS it JOIN ".ZKP_SQL."_tb_in_pl_item_v2 AS init USING(it_code) 
						WHERE init.inpl_idx = $_in_idx AND init_qty > 0
						ORDER BY it.it_code";
					$col =& query($sql);
					break;
				case 'item_ed':
					$sql = "SELECT
						 a.it_code,
						 a.it_model_no,
						 to_char(b.ined_expired_date,'Mon-YYYY') AS expired_date,
						 b.ined_qty AS qty
						FROM ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_in_pl_item_ed AS b USING(it_code) 
						WHERE b.inpl_idx = $_in_idx
						ORDER BY a.it_code, b.ined_expired_date";
					$col =& query($sql);
					break;
			}
		}
	}
	else if($_type == 'Claim')
	{
		if ($version == 'v1')
		{
			switch ($source) {
				case 'info':
					$sql = "SELECT *
						FROM
						  ".ZKP_SQL."_tb_claim AS cl
						  JOIN ".ZKP_SQL."_tb_in_claim AS incl USING(cl_idx)
						WHERE
						  cl.cl_idx = $_idx AND incl.incl_idx = $_in_idx";
					$result =& query($sql);
					$col =& fetchRowAssoc($result);
					break;
				case 'item':
					$sql = "SELECT
						  it.it_code,
						  it.it_model_no,
						  it.it_desc,
						  init.init_qty
						FROM
						  ".ZKP_SQL."_tb_item AS it
						  JOIN ".ZKP_SQL."_tb_in_claim_item AS init USING(it_code) 
						WHERE init.incl_idx = $_in_idx AND init_qty > 0
						ORDER BY it.it_code";
					$col =& query($sql);
					break;
				case 'item_ed':
					$sql = "SELECT
						  a.it_code,
						  a.it_model_no,
						  to_char(b.ecl_expired_date,'Mon-YYYY') AS expired_date,
						  b.ecl_qty AS qty
						FROM
						  ".ZKP_SQL."_tb_item AS a
						  JOIN ".ZKP_SQL."_tb_expired_claim AS b USING(it_code) 
						WHERE b.incl_idx = $_in_idx
						ORDER BY a.it_code, b.ecl_expired_date";
					$col =& query($sql);
					break;
			}
		}
		else if ($version == 'v2')
		{
			switch ($source) {
				case 'info':
					$sql = "SELECT *
						FROM
						  ".ZKP_SQL."_tb_claim AS cl
						  JOIN ".ZKP_SQL."_tb_in_claim_v2 AS incl USING(cl_idx)
						WHERE
						  cl.cl_idx = $_idx AND incl.incl_idx = $_in_idx";
					$result =& query($sql);
					$col =& fetchRowAssoc($result);
					break;
				case 'item':
					$sql = "SELECT
						  it.it_code,
						  it.it_model_no,
						  it.it_desc,
						  init.init_qty
						FROM
						  ".ZKP_SQL."_tb_item AS it
						  JOIN ".ZKP_SQL."_tb_in_claim_item_v2 AS init USING(it_code) 
						WHERE init.incl_idx = $_in_idx AND init_qty > 0
						ORDER BY it.it_code";
					$col =& query($sql);
					break;
				case 'item_ed':
					$sql = "
						SELECT
						  a.it_code,
						  a.it_model_no,
						  to_char(b.ined_expired_date,'Mon-YYYY') AS expired_date,
						  b.ined_qty AS qty
						FROM
						  ".ZKP_SQL."_tb_item AS a
						  JOIN ".ZKP_SQL."_tb_in_claim_item_ed AS b USING(it_code) 
						WHERE
						  b.incl_idx = $_in_idx
						ORDER BY a.it_code, b.ined_expired_date";
					$col =& query($sql);
					break;
			}
		}
	}
	else if($_type == 'Local')
	{
		if ($version == 'v1')
		{
			switch ($source) {
				case 'info':
					$sql = "SELECT * 
						FROM
						  ".ZKP_SQL."_tb_po_local AS po
						  JOIN ".ZKP_SQL."_tb_pl_local AS pl USING(po_code)
						  JOIN ".ZKP_SQL."_tb_in_local AS inlc USING(po_code, pl_no)
						WHERE inlc_idx = $_in_idx";
					$result =& query($sql);
					$col =& fetchRowAssoc($result);
					break;
				case 'item':
					$sql = "SELECT
						  it.it_code,
						  it.it_model_no,
						  it.it_desc,
						  init.init_qty
						FROM ".ZKP_SQL."_tb_item AS it JOIN ".ZKP_SQL."_tb_in_local_item AS init USING(it_code) 
						WHERE inlc_idx = $_in_idx AND init_qty > 0
						ORDER BY it.it_code";
					$col =& query($sql);
					break;
				case 'item_ed':
					$sql = "SELECT
						  a.it_code,
						  a.it_model_no,
						  to_char(b.elc_expired_date,'Mon-YYYY') AS expired_date,
						  b.elc_qty AS qty
						FROM ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_expired_local AS b USING(it_code) 
						WHERE b.inlc_idx = $_in_idx
						ORDER BY a.it_code, b.elc_expired_date";
					$col =& query($sql);
					break;
			}
		}
		else if ($version == 'v2')
		{
			switch ($source) {
				case 'info':
					$sql = "SELECT * 
						FROM
						  ".ZKP_SQL."_tb_po_local AS po
						  JOIN ".ZKP_SQL."_tb_pl_local AS pl USING(po_code)
						  JOIN ".ZKP_SQL."_tb_in_local_v2 AS inlc USING(po_code, pl_no)
						WHERE inlc_idx = $_in_idx";
					$result =& query($sql);
					$col =& fetchRowAssoc($result);
					break;
				case 'item':
					$sql = "SELECT
						  it.it_code,
						  it.it_model_no,
						  it.it_desc,
						  init.init_qty
						FROM ".ZKP_SQL."_tb_item AS it JOIN ".ZKP_SQL."_tb_in_local_item_v2 AS init USING(it_code) 
						WHERE inlc_idx = $_in_idx AND init_qty > 0
						ORDER BY it.it_code";
					$col =& query($sql);
					break;
				case 'item_ed':
					$sql = "SELECT
						  a.it_code,
						  a.it_model_no,
						  to_char(b.ined_expired_date,'Mon-YYYY') AS expired_date,
						  b.ined_qty AS qty
						FROM ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_in_local_item_ed AS b USING(it_code) 
						WHERE b.inlc_idx = $_in_idx
						ORDER BY a.it_code, b.ined_expired_date";
					$col =& query($sql);
					break;
			}
		}
	}

	return $col;
}