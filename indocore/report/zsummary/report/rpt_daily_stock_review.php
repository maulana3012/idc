<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim

*
* $_po_date : Inquire Date
*
*/
//Variable Color
$display_css['safe'] 		= "color:#333333";
$display_css['critical'] 	= "background-color:lightyellow; color:red";

//SET WHERE PARAMETER
$item_var = array();
$tmp = array();

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if($txtKeyword != "") {
	$tmp[] = "$searchBy LIKE '%%$txtKeyword%%'";
}

$strWhere = count($tmp>0) ? implode(" AND ", $tmp) : "";

// Current stock
$strWhere1 = ($strWhere == '') ? '' : 'WHERE '.$strWhere;
$sql_var1 = "
SELECT a.it_code, it_model_no, it_calc_stock_max , it_calc_stock_delivery, sum(stk_qty) AS qty
FROM
  ".ZKP_SQL."_tb_stock AS a
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
$strWhere1
GROUP BY it_code, it_model_no, it_calc_stock_max , it_calc_stock_delivery
ORDER BY it_code;
";
$res =& query($sql_var1);
while($col =& fetchRow($res)) {
	if(isset($item_var[$col[0]][1]))
		 $item_var[$col[0]][1] += $col[4];
	else $item_var[$col[0]][1] = (int) $col[4];

	$item_var[$col[0]][0] = $col[1];
	$item_var[$col[0]][8] = $col[2];
	$item_var[$col[0]][6] = $col[3];
}


// Current outstanding PO, Claim, PO Local
$sql_var2 = "
SELECT po_code AS code, it.it_code, med_getOutstandingINPL('PO', po_code, it.it_code) AS qty
FROM
  ".ZKP_SQL."_tb_po AS a
  JOIN ".ZKP_SQL."_tb_po_item AS b USING(po_code)
  INNER JOIN ".ZKP_SQL."_tb_item AS it ON b.it_code = it.it_code
  INNER JOIN ".ZKP_SQL."_tb_item_cat AS icat ON it.icat_midx = icat.icat_midx
$strWhere1
GROUP BY po_code, it.it_code
	UNION
SELECT cl_idx::text AS code, it.it_code, med_getOutstandingINPL('Claim', cl_idx::text, it.it_code) AS qty
FROM
  ".ZKP_SQL."_tb_claim AS a
  JOIN ".ZKP_SQL."_tb_claim_item AS b USING(cl_idx)
  INNER JOIN ".ZKP_SQL."_tb_item AS it ON b.it_code = it.it_code
  INNER JOIN ".ZKP_SQL."_tb_item_cat AS icat ON it.icat_midx = icat.icat_midx
$strWhere1
GROUP BY cl_idx, it.it_code
	UNION
SELECT po_code AS code, it_code, med_getOutstandingINPL('Local', po_code, it_code) AS qty
FROM
  ".ZKP_SQL."_tb_po_local AS a
  JOIN ".ZKP_SQL."_tb_po_local_item AS b USING(po_code)
  JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
$strWhere1
GROUP BY po_code, it_code
ORDER BY it_code
";
$res =& query($sql_var2);
while($col =& fetchRow($res)) {
	if(isset($item_var[$col[1]][2])) 
		 $item_var[$col[1]][2] += $col[2];
	else $item_var[$col[1]][2] = (int) $col[2];
}


// Current average balance stock for 6 last months
$strWhere2 = ($strWhere=='') ? $strWhere : ' AND '.$strWhere;
$sql_var4 = "
SELECT 
 a.it_code, sum(otst_qty) AS qty, 'outgoing' AS column
  FROM
	MED_tb_customer
	JOIN MED_tb_outgoing USING(cus_code)
	JOIN MED_tb_outgoing_stock USING (out_idx)
	JOIN MED_tb_item AS a USING(it_code)
	JOIN MED_tb_item_cat AS icat USING(icat_midx)
  WHERE out_cfm_date BETWEEN CURRENT_DATE - interval '180 days' AND CURRENT_DATE AND cus_code != '6IDC' ". $strWhere2 . "
  GROUP BY a.it_code
	UNION
SELECT a.it_code, sum(inst_qty)*-1 AS qty, 'incoming' AS column
  FROM
	MED_tb_customer
	JOIN MED_tb_incoming USING(cus_code)
	JOIN MED_tb_incoming_stock USING (inc_idx)
	JOIN MED_tb_item AS a USING(it_code)
	JOIN MED_tb_item_cat AS icat USING(icat_midx)
  WHERE inc_confirmed_timestamp BETWEEN CURRENT_DATE - interval '180 days' AND CURRENT_DATE AND cus_code != '6IDC' ". $strWhere2 . "
  GROUP BY a.it_code
	UNION
SELECT a.it_code, sum(rjit_qty) AS qty, 'reject_item' AS column
  FROM
	MED_tb_reject
	JOIN MED_tb_reject_item USING (rjt_idx)
	JOIN MED_tb_item AS a USING(it_code)
	JOIN MED_tb_item_cat AS icat USING(icat_midx)
  WHERE rjt_date BETWEEN CURRENT_DATE - interval '180 days' AND CURRENT_DATE AND rjt_doc_idx is null ". $strWhere2 . "
  GROUP BY a.it_code
	UNION
SELECT a.it_code, sum(rjed_qty) AS qty, 'reject_ed' AS column
  FROM
	MED_tb_reject_ed
	JOIN MED_tb_item AS a USING(it_code)
	JOIN MED_tb_item_cat AS icat USING(icat_midx)
  WHERE rjed_timestamp BETWEEN CURRENT_DATE - interval '180 days' AND CURRENT_DATE ". $strWhere2 . "
  GROUP BY a.it_code
ORDER BY it_code ";
$res =& query($sql_var4);
while($col =& fetchRow($res)) {
	if(isset($item_var[$col[0]][4]))
		 $item_var[$col[0]][4] += $col[1];
	else $item_var[$col[0]][4] = (int) $col[1];
}

//sort($item_var);
$it = array();
$i = 0;
foreach($item_var as $key => $val) {
	$it[$i][$key] = $val;
	$i++;
}

echo "<pre>";
//echo $sql_var2 ;
//var_dump($strWhere);
echo "</pre>";

print <<<END
<table width="80%" class="table_c">
	<tr>
		<th width="8%">CODE</th>
		<th>MODEL NO</th>
		<th width="10%">STOCK</th>
		<th width="10%">PO</th>
		<th width="10%">TOTAL</th>
		<th width="15%" colspan="2">MONTHLY<br />MAX</th>
		<th width="7%">COVERAGE</th>
		<th width="7%">DELIVERY</th>
		<th width="7%">ACTUAL</th>
	</tr>\n
END;

$pagination = new pagination;
if (count($it)) {
	$itPages = $pagination->generate($it, $txtRow);

	if (count($itPages) != 0) {
		foreach ($itPages  as $key1 => $val1) {
			foreach ($val1 as $key => $val) {
				$val[3] = $val[1] + $val[2];
				$val[4] = (isset($val[4]) && $val[4]!=0) ? round(($val[4]/6)*$val[8]) : 0;
				$val[5] = (isset($val[4]) && $val[4]!=0) ? round($val[3]/$val[4],2) : 0;
				$val[7] = $val[5] - $val[6];
				
				if($val[3] > 0) {
					$status = 'safe';
				} else {
					$status = 'critical';
				}

				print "<tr>\n";
				cell($key, ' style="'.$display_css[$status].'"');	// Code
				cell_link($val[0], ' style="'.$display_css[$status].'"', " href=\"javascript:openWindow('./p_detail_item.php?_code={$key}', 450, 250);\"");	// Model
				cell(number_format($val[1]), ' align="right" style="'.$display_css[$status].'"');	// Stock
				cell(number_format($val[2]), ' align="right" style="'.$display_css[$status].'"');	// Outstanding PO
				cell(number_format($val[3]), ' align="right" style="'.$display_css[$status].'"');	// Total stock estimate
				cell(number_format($val[4]), ' align="right" style="'.$display_css[$status].'"');	// Average 6 months usage
				cell(number_format($val[8],2), ' align="right" width="5%" style="'.$display_css[$status].'"');	// Pengalinya
				cell(number_format($val[5],2), ' align="right" style="'.$display_css[$status].'"');	// Coverage
				cell(number_format($val[6],2), ' align="right" style="'.$display_css[$status].'"');	// Delivery
				cell(number_format($val[7],2), ' align="right" style="'.$display_css[$status].'"');	// Actual
				print "</tr>\n";
			}
		}
	echo "</table>";
	echo "<table width=\"100%\" cellpadding=\"0\" cellspacing=\"2\" border=\"0\">";
	echo "<tr>";
	echo "<td align=\"center\"><div class=\"numbers\">".$pagination->links()."</div></td>";
	echo "</tr>";
	echo "</table>";
	}
}
?>