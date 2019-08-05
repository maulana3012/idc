<?php
//SET WHERE PARAMETER
$tmp = array();

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmp[0][] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmp[1][] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if($_type != 'all') {
	if($_type == 1) $tmp[1][] = "it_code IS NULL";
	else if($_type == 2) $tmp[0][] = "it_code IS NULL";
	
}

$strWhere[0] = (isset($tmp[0]) && count($tmp[0])>0) ? "WHERE ".implode(" AND ", $tmp[0]) : '';
$strWhere[1] = (isset($tmp[1]) && count($tmp[1])>0) ? "WHERE ".implode(" AND ", $tmp[1]) : '';

#Outstanding DO
if ($_type == 1)
{
	$sql_std = "
        select  
            bill_code AS doc_no,
            to_char(bill_inv_date,'dd-Mon-yy') as date,
            bill_inv_date AS doc_date,
            CASE
                WHEN bill_dept='A' THEN 'Apotik'
                WHEN bill_dept='D' THEN 'Dealer'
                WHEN bill_dept='H' THEN 'Hospital'
                WHEN bill_dept='M' THEN 'Marketing'
                WHEN bill_dept='P' THEN 'Pharmaceutical'
                WHEN bill_dept='T' THEN 'Tender'
            END AS dept,
            CASE
                WHEN bill_ordered_by = 1 AND bill_dept = 'A' THEN 'http://192.168.1.88/idc/apotik/billing/revise_billing.php?_code='||bill_code
                WHEN bill_ordered_by = 1 AND bill_dept = 'D' THEN 'http://192.168.1.88/idc/dealer/billing/revise_billing.php?_code='||bill_code
                WHEN bill_ordered_by = 1 AND bill_dept = 'H' THEN 'http://192.168.1.88/idc/hospital/billing/revise_billing.php?_code='||bill_code
                WHEN bill_ordered_by = 1 AND bill_dept = 'M' THEN 'http://192.168.1.88/idc/marketing/billing/revise_billing.php?_code='||bill_code
                WHEN bill_ordered_by = 1 AND bill_dept = 'P' THEN 'http://192.168.1.88/idc/pharmaceutical/billing/revise_billing.php?_code='||bill_code
                WHEN bill_ordered_by = 1 AND bill_dept = 'T' THEN 'http://192.168.1.88/idc/tender/billing/revise_billing.php?_code='||bill_code
            END AS go_page
        from ".ZKP_SQL."_tb_billing where bill_cfm_wh_date is null and bill_inv_date between date '2012-09-01' and date '$period_to' and bill_vat > 0
            UNION
        select  
            turn_code AS doc_no,
            to_char(turn_return_date,'dd-Mon-yy') as date,
            turn_return_date AS doc_date,
            CASE
                WHEN turn_dept='A' THEN 'Apotik'
                WHEN turn_dept='D' THEN 'Dealer'
                WHEN turn_dept='H' THEN 'Hospital'
                WHEN turn_dept='M' THEN 'Marketing'
                WHEN turn_dept='P' THEN 'Pharmaceutical'
                WHEN turn_dept='T' THEN 'Tender'
            END AS dept,
            CASE
                WHEN turn_ordered_by = 1 AND turn_dept = 'A' THEN 'http://192.168.1.88/idc/apotik/billing/revise_return.php?_code='||turn_code
                WHEN turn_ordered_by = 1 AND turn_dept = 'D' THEN 'http://192.168.1.88/idc/dealer/billing/revise_return.php?_code='||turn_code
                WHEN turn_ordered_by = 1 AND turn_dept = 'H' THEN 'http://192.168.1.88/idc/hospital/billing/revise_return.php?_code='||turn_code
                WHEN turn_ordered_by = 1 AND turn_dept = 'M' THEN 'http://192.168.1.88/idc/marketing/billing/revise_return.php?_code='||turn_code
                WHEN turn_ordered_by = 1 AND turn_dept = 'P' THEN 'http://192.168.1.88/idc/pharmaceutical/billing/revise_return.php?_code='||turn_code
                WHEN turn_ordered_by = 1 AND turn_dept = 'T' THEN 'http://192.168.1.88/idc/tender/billing/revise_return.php?_code='||turn_code
            END AS go_page
        from ".ZKP_SQL."_tb_return where turn_cfm_wh_delivery_timestamp is null and turn_return_date between date '2012-09-01' and date '$period_to' and turn_vat > 0
            UNION
        select  
            dr_code AS doc_no,
            to_char(dr_issued_date,'dd-Mon-yy') as date,
            dr_issued_date AS doc_date,
            CASE
                WHEN dr_dept='A' THEN 'Apotik'
                WHEN dr_dept='D' THEN 'Dealer'
                WHEN dr_dept='H' THEN 'Hospital'
                WHEN dr_dept='M' THEN 'Marketing'
                WHEN dr_dept='P' THEN 'Pharmaceutical'
                WHEN dr_dept='T' THEN 'Tender'
            END AS dept,
            CASE
                WHEN dr_ordered_by = 1 AND dr_dept = 'A' THEN 'http://192.168.1.88/idc/apotik/billing/revise_dr.php?_code='||dr_code
                WHEN dr_ordered_by = 1 AND dr_dept = 'D' THEN 'http://192.168.1.88/idc/dealer/billing/revise_dr.php?_code='||dr_code
                WHEN dr_ordered_by = 1 AND dr_dept = 'H' THEN 'http://192.168.1.88/idc/hospital/billing/revise_dr.php?_code='||dr_code
                WHEN dr_ordered_by = 1 AND dr_dept = 'M' THEN 'http://192.168.1.88/idc/marketing/billing/revise_dr.php?_code='||dr_code
                WHEN dr_ordered_by = 1 AND dr_dept = 'P' THEN 'http://192.168.1.88/idc/pharmaceutical/billing/revise_dr.php?_code='||dr_code
                WHEN dr_ordered_by = 1 AND dr_dept = 'T' THEN 'http://192.168.1.88/idc/tender/billing/revise_dr.php?_code='||dr_code
            END AS go_page
        from ".ZKP_SQL."_tb_dr where dr_cfm_wh_delivery_timestamp is null and dr_issued_date between date '2012-09-01' and date '$period_to' and dr_type_item = 1
        ORDER BY doc_date";
	$res_std =& query($sql_std);

	if (numQueryRows($res_std) > 0)
	{
print <<<END
<br />The normal stock not fix yet. There is/are still outstanding document(s) confirm. Please let admin each department know.<br />
<table width="30%" class="table_c">
    <tr>
        <th width="4%">No</th>
        <th>INVOICE NO</th>
        <th width="25%">DATE</th>
        <th width="25%">DEPT</th>
    </tr>
END;
		$i = 1;
		while($column =& fetchRowAssoc($res_std)) {
echo "<tr>\n";
echo "\t<td>". $i++ ."</td>\n";
echo "\t<td><a href=". $column['go_page'] ."><b>". $column['doc_no']."</b></a></td>\n";
echo "\t<td>". $column['date']."</td>\n";
echo "\t<td>". $column['dept']."</td>\n";
echo "</tr>\n";
		}
echo "</table>\n";
	}
}


# Query
$sql = "
SELECT
  'VAT' AS type_stock,
  trim(it_code) AS it_code,
  trim(it_model_no) AS it_model_no,
  (SELECT sum(log_in_qty)-sum(log_out_qty) FROM ".ZKP_SQL."_tb_log WHERE it_code = it.it_code AND log_type = 1 AND log_wh_location = 1 AND log_date < DATE '$period_from') AS log_start_qty,
  (SELECT sum(log_in_qty) FROM ".ZKP_SQL."_tb_log WHERE it_code = it.it_code AND log_type = 1 AND log_wh_location = 1 AND log_date BETWEEN DATE '$period_from' AND '$period_to') AS log_in_qty,
  (SELECT sum(log_out_qty) FROM ".ZKP_SQL."_tb_log WHERE it_code = it.it_code AND log_type = 1 AND log_wh_location = 1 AND log_date BETWEEN DATE '$period_from' AND '$period_to') AS log_out_qty,
  (SELECT ipn_price_dollar FROM ".ZKP_SQL."_tb_item_price_net WHERE it_code = it.it_code AND ipn_date_from <= '$period_to' AND ipn_idx = (SELECT max(ipn_idx) FROM ".ZKP_SQL."_tb_item_price_net WHERE it_code = it.it_code AND ipn_date_from <= '$period_to')) AS ipn_price_dollar,
  ".ZKP_SQL."_getuserprice(it_code, DATE '$period_to', 'net', ".str_replace(',','',$_kurs).") AS ipn_price
FROM
  ".ZKP_SQL."_tb_item AS it
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING (icat_midx)
{$strWhere[0]}
	UNION
SELECT
  'DTD' AS type_stock,
  trim(it_code) AS it_code,
  trim(it_model_no) AS it_model_no,
  (SELECT sum(log_in_qty)-sum(log_out_qty) FROM ".ZKP_SQL."_tb_log WHERE it_code = it.it_code AND log_type = 2 AND log_wh_location = 1 AND log_date < DATE '$period_from') AS log_start_qty,
  (SELECT sum(log_in_qty) FROM ".ZKP_SQL."_tb_log WHERE it_code = it.it_code AND log_type = 2 AND log_wh_location = 1 AND log_date BETWEEN DATE '$period_from' AND '$period_to') AS log_in_qty,
  (SELECT sum(log_out_qty) FROM ".ZKP_SQL."_tb_log WHERE it_code = it.it_code AND log_type = 2 AND log_wh_location = 1 AND log_date BETWEEN DATE '$period_from' AND '$period_to') AS log_out_qty,
  (SELECT ipn_price_dollar FROM ".ZKP_SQL."_tb_item_price_net WHERE it_code = it.it_code AND ipn_date_from <= '$period_to' AND ipn_idx = (SELECT max(ipn_idx) FROM ".ZKP_SQL."_tb_item_price_net WHERE it_code = it.it_code AND ipn_date_from <= '$period_to')) AS ipn_price_dollar,
  ".ZKP_SQL."_getuserprice(it_code, DATE '$period_to', 'net', ".str_replace(',','',$_kurs).") AS ipn_price
FROM
  ".ZKP_SQL."_tb_item AS it
  JOIN ".ZKP_SQL."_tb_item_cat AS icat USING (icat_midx)
{$strWhere[1]}
ORDER BY it_code
";

$res =& query($sql);
while($col =& fetchRowAssoc($res)) {
	if (!isset($item[$col['it_code']])) {
		$item[$col['it_code']]['start'] = (float) $col['log_start_qty'];
		$item[$col['it_code']]['in'] = (float) $col['log_in_qty'];
		$item[$col['it_code']]['out'] = (float) $col['log_out_qty'];
		$item[$col['it_code']]['end'] = ((float) $col['log_start_qty'] + (float) $col['log_in_qty'] - (float) $col['log_out_qty']);
	} else {
		$item[$col['it_code']]['start'] += (float) $col['log_start_qty'];
		$item[$col['it_code']]['in'] += (float) $col['log_in_qty'];
		$item[$col['it_code']]['out'] += (float) $col['log_out_qty'];
		$item[$col['it_code']]['end'] += ((float) $col['log_start_qty'] + (float) $col['log_in_qty'] - (float) $col['log_out_qty']);
	}
	$item[$col['it_code']]['it_code'] = $col['it_code'];
	$item[$col['it_code']]['it_model_no'] = $col['it_model_no'];
	$item[$col['it_code']]['ipn_price_dollar'] = $col['ipn_price_dollar'];
	$item[$col['it_code']]['ipn_price'] = $col['ipn_price'];
}

echo "<pre>";
//var_dump($sql);
echo "</pre>";
//exit;
if($currentDept == 'purchasing')
{

print <<<END
<table width="100%" class="table_c">
	<tr>
		<th rowspan="2" width="3%">NO</th>
		<th rowspan="2" width="7%">CODE</th>
		<th rowspan="2">MODEL NO</th>
		<th colspan="4">STOCK</th>
		<th rowspan="2" width="5%">PRICE ($)</th>
		<th rowspan="2" width="10%">@PRICE</th>
		<th rowspan="2" width="12%">AMOUNT</th>
	</tr>
	<tr>
		<th width="8%">START</th>
		<th width="8%">INCOMING</th>
		<th width="8%">OUTGOING</th>
		<th width="8%">END</th>
	</tr>\n
END;

$i = 1;
$grand_tot = array(0,0,0,0,0);
foreach ($item as $key => $it) {
	if ($it['start'] != 0 || $it['in'] != 0 || $it['out'] != 0 || $it['end'] != 0) {
		$it['price'] = $it['end'] * $it['ipn_price'];
		
		$grand_tot[0] += $it['start'];
		$grand_tot[1] += $it['in'];
		$grand_tot[2] += $it['out'];
		$grand_tot[3] += $it['end'];
		$grand_tot[4] += $it['price'];

		$it['start'] = (number_format($it['start'])=='0') ? '' : number_format($it['start'],1);
		$it['in'] = (number_format($it['in'])=='0') ? '' : number_format($it['in'],1);
		$it['out'] = (number_format($it['out'])=='0') ? '' : number_format($it['out'],1);
		$it['end'] = (number_format($it['end'])=='0') ? '' : number_format($it['end'],1);
		$it['ipn_price_dollar'] = (number_format((double)$it['ipn_price_dollar'])=='0') ? '' : number_format($it['ipn_price_dollar'],2);

		echo "<tr>\n";
		echo "\t<td align=\"center\">".$i++."</td>\n";
		echo "\t<td>{$it['it_code']}</td>\n";
		echo "\t<td><a href=\"". HTTP_DIR . "$currentDept/$moduleDept/history_stock.php?_code=".$it['it_code'] ."&_type=$_type\"><div class=\"bar\">{$it['it_model_no']}</div></a></td>\n";
		echo "\t<td align=\"right\">". $it['start'] ."</td>\n";
		echo "\t<td align=\"right\">". $it['in'] ."</td>\n";
		echo "\t<td align=\"right\">". $it['out'] ."</td>\n";
		echo "\t<td align=\"right\">". $it['end'] ."</td>\n";
		echo "\t<td align=\"right\">". $it['ipn_price_dollar'] ."</td>\n";
		echo "\t<td align=\"right\">". number_format((float)$it['ipn_price'],2) ."</td>\n";
		echo "\t<td align=\"right\">". number_format($it['price'],2) ."</td>\n";
		echo "</tr>\n";
	}
}

echo "<tr>\n";
echo "\t<td align=\"right\" colspan=\"3\" style=\"color:brown; background-color:lightyellow\"><b>GRAND TOTAL</b></td>\n";
echo "\t<td align=\"right\" style=\"color:brown; background-color:lightyellow\"><b>". number_format($grand_tot[0],1) ."</b></td>\n";
echo "\t<td align=\"right\" style=\"color:brown; background-color:lightyellow\"><b>". number_format($grand_tot[1],1) ."</b></td>\n";
echo "\t<td align=\"right\" style=\"color:brown; background-color:lightyellow\"><b>". number_format($grand_tot[2],1) ."</b></td>\n";
echo "\t<td align=\"right\" style=\"color:brown; background-color:lightyellow\"><b>". number_format($grand_tot[3],1) ."</b></td>\n";
echo "\t<td colspan=\"2\" style=\"color:brown; background-color:lightyellow\"></td>\n";
echo "\t<td align=\"right\" style=\"color:brown; background-color:lightyellow\"><b>". number_format($grand_tot[4],2) ."</b></td>\n";
echo "</tr>\n";
echo "</table>\n";

}
else if($currentDept == 'warehouse')
{

print <<<END
<table width="100%" class="table_c">
	<tr>
		<th rowspan="2" width="3%">NO</th>
		<th rowspan="2" width="7%">CODE</th>
		<th rowspan="2">MODEL NO</th>
		<th colspan="4">STOCK</th>
	</tr>
	<tr>
		<th width="8%">START</th>
		<th width="8%">INCOMING</th>
		<th width="8%">OUTGOING</th>
		<th width="8%">END</th>
	</tr>\n
END;

$i = 1;
$grand_tot = array(0,0,0,0,0);
foreach ($item as $key => $it) {
	if ($it['end'] != 0) {
		$it['price'] = $it['end'] * $it['ipn_price'];
		
		$grand_tot[0] += $it['start'];
		$grand_tot[1] += $it['in'];
		$grand_tot[2] += $it['out'];
		$grand_tot[3] += $it['end'];
		$grand_tot[4] += $it['price'];

		$it['start'] = (number_format($it['start'])=='0') ? '' : number_format($it['start'],1);
		$it['in'] = (number_format($it['in'])=='0') ? '' : number_format($it['in'],1);
		$it['out'] = (number_format($it['out'])=='0') ? '' : number_format($it['out'],1);
		$it['end'] = (number_format($it['end'])=='0') ? '' : number_format($it['end'],1);
		$it['ipn_price_dollar'] = (number_format((double)$it['ipn_price_dollar'])=='0') ? '' : number_format($it['ipn_price_dollar'],2);

		echo "<tr>\n";
		echo "\t<td align=\"center\">".$i++."</td>\n";
		echo "\t<td>{$it['it_code']}</td>\n";
		echo "\t<td><a href=\"". HTTP_DIR . "$currentDept/$moduleDept/history_stock.php?_code=".$it['it_code'] ."&_type=$_type\"><div class=\"bar\">{$it['it_model_no']}</div></a></td>\n";
		echo "\t<td align=\"right\">". $it['start'] ."</td>\n";
		echo "\t<td align=\"right\">". $it['in'] ."</td>\n";
		echo "\t<td align=\"right\">". $it['out'] ."</td>\n";
		echo "\t<td align=\"right\">". $it['end'] ."</td>\n";
		echo "</tr>\n";
	}
}

echo "<tr>\n";
echo "\t<td align=\"right\" colspan=\"3\" style=\"color:brown; background-color:lightyellow\"><b>GRAND TOTAL</b></td>\n";
echo "\t<td align=\"right\" style=\"color:brown; background-color:lightyellow\"><b>". number_format($grand_tot[0],1) ."</b></td>\n";
echo "\t<td align=\"right\" style=\"color:brown; background-color:lightyellow\"><b>". number_format($grand_tot[1],1) ."</b></td>\n";
echo "\t<td align=\"right\" style=\"color:brown; background-color:lightyellow\"><b>". number_format($grand_tot[2],1) ."</b></td>\n";
echo "\t<td align=\"right\" style=\"color:brown; background-color:lightyellow\"><b>". number_format($grand_tot[3],1) ."</b></td>\n";
echo "</tr>\n";
echo "</table>\n";

}


