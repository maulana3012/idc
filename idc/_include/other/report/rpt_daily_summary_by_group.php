<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim
*
* $_search_date : Inquire Date
*/

$tmpDF = array();
$tmpDR = array();
$tmpDT = array();
$tmpRDT = array();

//SET WHERE PARAMETER
if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmpDF[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmpDR[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmpDT[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmpRDT[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if(ZKP_FUNCTION == 'ALL') {
	if($_order_by != 'all'){
		$tmpDR[]	= "dr_ordered_by = $_order_by";
	}
} else {
	$tmpDR[]	= "dr_ordered_by = ".$cboFilter[1][ZKP_FUNCTION][0][0] . " AND ".ZKP_SQL."_isValidShowInvoice('".ZKP_FUNCTION."', dr_code,'dr')";
}

if ($_cug_code != 'all') {
	$tmpDF[]		= "df_cus_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmpDR[]		= "dr_cus_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmpDT[]		= "dt_cus_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$sqlDF 	= " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
	$sqlDR	= " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
	$sqlDT	= " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
	$sqlRDT	= " SELECT (SELECT cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_cug_code') AS cug_name,";
} else {
	$sqlDF = "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = df_cus_to),
		'Others') AS cug_name,";
	$sqlDR = "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = dr_cus_to),
		'Others') AS cug_name,";
	$sqlDT = "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = dt_cus_to),
		'Others') AS cug_name,";
	$sqlRDT = "
	SELECT
		COALESCE((SELECT cug_name FROM ".ZKP_SQL."_tb_customer JOIN ".ZKP_SQL."_tb_customer_group USING (cug_code) WHERE cus_code = rdt_cus_to),
		'Others') AS cug_name,";
}

if($_cus_code != "") {
	$tmpDF[] = "df_cus_to = '$_cus_code'";
	$tmpDR[] = "dr_cus_to = '$_cus_code'";
	$tmpDT[] = "dt_cus_to = '$_cus_code'";
	$tmpRDT[] = "rdt_cus_to = '$_cus_code'";
}

if ($some_date != "") {
	$tmpDF[] = "df_date = DATE '$some_date'";
	$tmpDR[] = "dr_date = DATE '$some_date'";
	$tmpDT[] = "dt_date = DATE '$some_date'";
	$tmpRDT[] = "rdt_date = DATE '$some_date'";
} else {
	$tmpDF[] = "df_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
	$tmpDR[] = "dr_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
	$tmpDT[] = "dt_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
	$tmpRDT[] = "rdt_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
}

if($_source == 'df') {
	$tmpDR[]	= "dr_code = NULL";
	$tmpDT[]	= "dt_code = NULL";
	$tmpRDT[]	= "rdt_code = NULL";
} else if($_source == 'dr') {
	$tmpDF[]	= "df_code = NULL";
	$tmpDT[]	= "dt_code = NULL";
	$tmpRDT[]	= "rdt_code = NULL";
} else if($_source == 'dt') {
	$tmpDF[]	= "df_code = NULL";
	$tmpDR[]	= "dr_code = NULL";
	$tmpRDT[]	= "rdt_code = NULL";
} else if($_source == 'rdt') {
	$tmpDF[]	= "df_code = NULL";
	$tmpDR[]	= "dr_code = NULL";
	$tmpDT[]	= "dt_code = NULL";
}

$tmpDF[]   = "df_dept = '$department'";
$tmpDR[]   = "dr_dept = '$department'";
$tmpDT[]   = "dt_dept = '$department'";
$tmpRDT[]   = "rdt_dept = '$department'";

$strWhereDF = implode(" AND ", $tmpDF);
$strWhereDR = implode(" AND ", $tmpDR);
$strWhereDT = implode(" AND ", $tmpDT);
$strWhereRDT = implode(" AND ", $tmpRDT);

$sqlDF .= "
 cus_code AS cus_code,
 cus_full_name AS cus_full_name,
 df_code AS do_code,
 df_issued_date AS date,
 to_char(df_cfm_wh_delivery_timestamp,'dd-Mon-YY') AS wh_cfm_date,
 to_char(df_issued_date, 'dd-Mon-YY') AS do_date,
 'df' || dfit_idx AS idx,
 it_code AS it_code,
 it_model_no AS it_model_no,
 it_desc AS it_desc,
 dfit_qty AS it_qty,
 'revise_df.php?_code='||df_code AS go_page
FROM
 ".ZKP_SQL."_tb_customer AS cus
 JOIN ".ZKP_SQL."_tb_df AS df ON df_cus_to = cus_code
 JOIN ".ZKP_SQL."_tb_df_item AS dfit USING(df_code) 
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE " . $strWhereDF;

$sqlDT .= "
 cus_code AS cus_code,
 cus_full_name AS cus_full_name,
 dt_code AS do_code,
 dt_issued_date AS date,
 to_char(dt_cfm_wh_delivery_timestamp,'dd-Mon-YY') AS wh_cfm_date,
 to_char(dt_issued_date, 'dd-Mon-YY') AS do_date,
 'dt' || dtit_idx AS idx,
 it_code AS it_code,
 it_model_no AS it_model_no,
 it_desc AS it_desc,
 dtit_qty AS it_qty,
 'revise_dt.php?_code='||dt_code AS go_page
FROM
 ".ZKP_SQL."_tb_customer AS cus
 JOIN ".ZKP_SQL."_tb_dt AS dt ON dt_cus_to = cus_code
 JOIN ".ZKP_SQL."_tb_dt_item AS dtit USING(dt_code) 
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE " . $strWhereDT;

$sqlDR .= "
 cus_code AS cus_code,
 cus_full_name AS cus_full_name,
 dr_code AS do_code,
 dr_issued_date AS date,
 to_char(dr_cfm_wh_delivery_timestamp,'dd-Mon-YY') AS wh_cfm_date,
 to_char(dr_issued_date, 'dd-Mon-YY') AS do_date,
 'dr' || drit_idx AS idx,
 it_code AS it_code,
 it_model_no AS it_model_no,
 it_desc AS it_desc,
 drit_qty AS it_qty,
 'revise_dr.php?_code='||dr_code AS go_page
FROM
 ".ZKP_SQL."_tb_customer AS cus
 JOIN ".ZKP_SQL."_tb_dr AS dr ON dr_cus_to = cus_code
 JOIN ".ZKP_SQL."_tb_dr_item AS drit USING(dr_code) 
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE " . $strWhereDR;

$sqlRDT .= "
 cus_code AS cus_code,
 cus_full_name AS cus_full_name,
 rdt_code AS do_code,
 rdt_date AS date,
 to_char(rdt_cfm_wh_delivery_timestamp, 'dd-Mon-yy') AS wh_cfm_date,
 to_char(rdt_date, 'dd-Mon-YY') AS do_date,
 'rdt' || rdtit_idx AS idx,
 it_code AS it_code,
 it_model_no AS it_model_no,
 it_desc AS it_desc,
 -rdtit_qty AS it_qty,
 'revise_return_dt.php?_code='||rdt_code AS go_page
FROM
 ".ZKP_SQL."_tb_customer AS cus
 JOIN ".ZKP_SQL."_tb_return_dt AS dt ON rdt_cus_to = cus_code
 JOIN ".ZKP_SQL."_tb_return_dt_item AS dtit USING(rdt_code) 
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
 JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx)
WHERE " . $strWhereRDT;

$sql = "$sqlDF UNION $sqlDT UNION $sqlDR UNION $sqlRDT ORDER BY cug_name, cus_code, date, do_code, it_code, idx";
/*
echo "<pre>";
echo $sql;
echo "</pre>";
*/
// raw data
$rd		= array();
$rdIdx	= 0;
$cache	= array("","","","");
$group0	= array();
$res	=& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['cug_name'],			//0
		$col['cus_code'],			//1
		$col['cus_full_name'],		//2
		$col['do_code'],			//3
		$col['do_date'],			//4
		$col['wh_cfm_date'],		//5
		$col['idx'],				//6
		$col['it_code'],			//7
		$col['it_model_no'],		//8
		$col['it_desc'],			//9
		$col['it_qty'],				//10
		$col['go_page']				//11
	);

	//1st grouping
	if($cache[0] != $col['cug_name']) {
		$cache[0] = $col['cug_name'];
		$group0[$col['cug_name']] = array();
	}

	if($cache[1] != $col['cus_code']) {
		$cache[1] = $col['cus_code'];
		$group0[$col['cug_name']][$col['cus_code']] = array();
	}

	if($cache[2] != $col['do_code']) {
		$cache[2] = $col['do_code'];
		$group0[$col['cug_name']][$col['cus_code']][$col['do_code']] = array();
	}

	if($cache[3] != $col['idx']) {
		$cache[3] = $col['idx'];
	}

	$group0[$col['cug_name']][$col['cus_code']][$col['do_code']][$col['idx']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$ggTotal	= 0;

//GROUP
foreach ($group0 as $total1 => $group1) {

	echo "<span class=\"comment\"><b> PUSAT: ". $total1. "</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th width="18%">CUSTOMER</th>
			<th width="12%">DO NO#</th>
			<th width="8%">ISSUE DATE</th>
			<th width="8%">WH CFM DATE</th>
			<th width="18%">MODEL NO</th>
			<th>DESC</th>
			<th width="8%">QTY<br>(EA)</th>
		</tr>\n
END;

	$gTotal		= 0;
	$print_tr_1 = 0;
	
	print "<tr>\n";

	//CUSTOMER
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += count($group2);

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".trim($rd[$rdIdx][1])."]<br/>".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');		//Customer

		$print_tr_2 = 0;
		//ORDER
		foreach($group2 as $total3 => $group3) {
			$rowSpan = 0;
			array_walk_recursive($group3, 'getRowSpan');
			$rowSpan += 1;

			if($print_tr_2++ > 0) print "<tr>\n";
			cell_link("<span class=\"bar\">".$rd[$rdIdx][3]."</span>", ' align="center" valign="top" rowspan="'.$rowSpan.'"',
				' href="'.$rd[$rdIdx][11].'"');													//DO no
			cell($rd[$rdIdx][4], ' valign=""top align="center" rowspan="'.$rowSpan.'"');		//DO date
			cell($rd[$rdIdx][5], ' valign=""top align="center" rowspan="'.$rowSpan.'"');		//wh cfm date

			$total		= 0;
			$print_tr_3 = 0;
			//ITEM
			foreach($group3 as $group4) {
				if($print_tr_3++ > 0) print "<tr>\n";

				cell("[".trim($rd[$rdIdx][7])."] ".$rd[$rdIdx][8]);				//model no
				cell(cut_string($rd[$rdIdx][9],35));							//desc
				cell(number_format((double)$rd[$rdIdx][10]), ' align="right"');			//qty
				print "</tr>\n";

				$total += $rd[$rdIdx][10];
				$rdIdx++;
			}
			
			print "<tr>\n";
			cell("$total3", ' colspan="2" align="right" style="color:darkblue"');
			cell(number_format((double)$total), ' align="right" style="color:darkblue"');
			print "</tr>\n";

			$gTotal += $total;
		}
	}

	print "<tr>\n";
	cell("<b>$total1</b>", ' colspan="6" align="right" style="color:brown; background-color:lightyellow"');
	cell(number_format((double)$gTotal), ' align="right" style="color:brown; background-color:lightyellow"');
	print "</tr>\n";
	print "</table><br />\n";

	$ggTotal += $gTotal;
}

echo "<span class=\"comment\"><b>GRAND TOTAL</b></span>\n";
	print <<<END
<table width="100%" class="table_f">
	<tr>
		<th width="18%">CUSTOMER</th>
		<th width="13%">DO NO#</th>
		<th width="8%">ISSUE DATE</th>
		<th width="8%">WH CFM DATE</th>
		<th width="18%">MODEL NO</th>
		<th>DESC</th>
		<th width="8%">QTY<br>(EA)</th>
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="6" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$ggTotal), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>