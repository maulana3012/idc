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

$tmpDF = array();
$tmpDR = array();
$tmpDT = array();
$tmpRDT = array();

//SET WHERE PARAMETER
if(ZKP_FUNCTION == 'ALL') {
	if($_order_by != 'all'){
		$tmpDF[]	= "df_ordered_by = $_order_by";
		$tmpDR[]	= "dr_ordered_by = $_order_by";
		$tmpDT[]	= "dt_ordered_by = $_order_by";
		$tmpRDT[]	= "rdt_ordered_by = $_order_by";
	}
} else {
	$tmpDF[]	= "df_ordered_by = ".$cboFilter[1][ZKP_FUNCTION][0][0];
	$tmpDR[]	= "dr_ordered_by = ".$cboFilter[1][ZKP_FUNCTION][0][0];
	$tmpDT[]	= "dt_ordered_by = ".$cboFilter[1][ZKP_FUNCTION][0][0];
	$tmpRDT[]	= "rdt_ordered_by = ".$cboFilter[1][ZKP_FUNCTION][0][0];
}

if ($_last_category != 0) {
	$catList = executeSP(ZKP_SQL."_getSubCategory", $_last_category);
	$tmpDF[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmpDR[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmpDT[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
	$tmpRDT[] = "icat.icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";
}

if ($_cug_code != 'all') {
	$tmpDF[]		= "df_cus_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmpDR[]		= "dr_cus_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmpDT[]		= "dt_cus_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
	$tmpRDT[]		= "rdt_cus_to IN (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '$_cug_code')";
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
$tmpDT[]   = "dt_dept = '$department'";
$tmpDR[]   = "dr_dept = '$department'";
$tmpRDT[]  = "rdt_dept = '$department'";

$strWhereDF = implode(" AND ", $tmpDF);
$strWhereDR = implode(" AND ", $tmpDR);
$strWhereDT = implode(" AND ", $tmpDT);
$strWhereRDT = implode(" AND ", $tmpRDT);

$sqlDF = "
SELECT
  icat_midx AS icat_midx,
  icat_pidx AS icat_pidx,
  it_code AS it_code,
  it_model_no AS it_model_no,
  it_desc AS it_desc,
  df_code AS do_code,
  df_issued_date AS date,
  (SELECT to_char(out_cfm_date,'dd-Mon-YY') FROM ".ZKP_SQL."_tb_outgoing WHERE out_doc_ref=df_code AND out_doc_type=4) AS wh_cfm_date,
  to_char(df_issued_date, 'dd-Mon-yy') as do_date,
  cus_code AS cus_code,
  cus_full_name AS cus_full_name,
  'df' || dfit_idx AS idx,
  dfit_qty AS it_qty,
  'revise_df.php?_code='||df_code AS go_page
FROM
  	".ZKP_SQL."_tb_customer AS c
    JOIN ".ZKP_SQL."_tb_df AS df ON c.cus_code = df.df_cus_to
	JOIN ".ZKP_SQL."_tb_df_item AS dfit USING(df_code)
    JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
    JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx) 
WHERE ". $strWhereDF;

$sqlDR = "
SELECT
  icat_midx AS icat_midx,
  icat_pidx AS icat_pidx,
  it_code AS it_code,
  it_model_no AS it_model_no,
  it_desc AS it_desc,
  dr_code AS do_code,
  dr_issued_date AS date,
  (SELECT to_char(out_cfm_date,'dd-Mon-YY') FROM ".ZKP_SQL."_tb_outgoing WHERE out_doc_ref=dr_code AND out_doc_type=5) AS wh_cfm_date,
  to_char(dr_issued_date, 'dd-Mon-yy') as do_date,
  cus_code AS cus_code,
  cus_full_name AS cus_full_name,
  'dr' || drit_idx AS idx,
  drit_qty AS it_qty,
  'revise_dr.php?_code='||dr_code AS go_page
FROM
  	".ZKP_SQL."_tb_customer AS c
    JOIN ".ZKP_SQL."_tb_dr AS dr ON c.cus_code = dr.dr_cus_to
	JOIN ".ZKP_SQL."_tb_dr_item AS drit USING(dr_code)
    JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
    JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx) 
WHERE ". $strWhereDR;

$sqlDT = "
SELECT
  icat_midx AS icat_midx,
  icat_pidx AS icat_pidx,
  it_code AS it_code,
  it_model_no AS it_model_no,
  it_desc AS it_desc,
  dt_code AS do_code,
  dt_issued_date AS date,
  (SELECT to_char(out_cfm_date,'dd-Mon-YY') FROM ".ZKP_SQL."_tb_outgoing WHERE out_doc_ref=dt_code AND out_doc_type=3) AS wh_cfm_date,
  to_char(dt_issued_date, 'dd-Mon-yy') as do_date,
  cus_code AS cus_code,
  cus_full_name AS cus_full_name,
  'dt' || dtit_idx AS idx,
  dtit_qty AS it_qty,
  'revise_dt.php?_code='||dt_code AS go_page
FROM
  	".ZKP_SQL."_tb_customer AS c
    JOIN ".ZKP_SQL."_tb_dt AS dt ON c.cus_code = dt.dt_cus_to
	JOIN ".ZKP_SQL."_tb_dt_item AS dtit USING(dt_code)
    JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
    JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx) 
WHERE ". $strWhereDT;

$sqlRDT = "
SELECT
  icat_midx AS icat_midx,
  icat_pidx AS icat_pidx,
  it_code AS it_code,
  it_model_no AS it_model_no,
  it_desc AS it_desc,
  rdt_code AS do_code,
  rdt_date AS date,
  to_char(rdt_cfm_wh_delivery_timestamp, 'dd-Mon-yy') AS wh_cfm_date,
  to_char(rdt_date, 'dd-Mon-yy') as do_date,
  cus_code AS cus_code,
  cus_full_name AS cus_full_name,
  'rdt' || rdtit_idx AS idx,
  -rdtit_qty AS it_qty,
  'revise_return_dt.php?_code='||rdt_code AS go_page
FROM
  	".ZKP_SQL."_tb_customer AS c
    JOIN ".ZKP_SQL."_tb_return_dt AS rdt ON c.cus_code = rdt.rdt_cus_to
	JOIN ".ZKP_SQL."_tb_return_dt_item AS dtit USING(rdt_code)
    JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
    JOIN ".ZKP_SQL."_tb_item_cat AS icat USING(icat_midx) 
WHERE ". $strWhereRDT;

$sql = "$sqlDF UNION $sqlDR UNION $sqlDT UNION $sqlRDT ORDER BY icat_midx, it_code, idx";
/*
echo "<pre>";
echo $sql;
echo "</pre>";
*/
// raw data
$rd		= array();
$rdIdx	= 0;
$cache	= array("","","");
$group0	= array();
$res	=& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['icat_midx'],		//0
		$col['it_code'],		//1
		$col['it_model_no'],	//2
		$col['it_desc'],		//3
		$col['do_code'],		//4
		$col['do_date'],		//5
		$col['wh_cfm_date'],	//6
		$col['cus_code'],		//7
		$col['cus_full_name'],	//8
		$col['idx'],			//9
		$col['it_qty'],			//10
		$col['go_page']			//11
	);

	//1st grouping
	if($cache[0] != $col['icat_midx']) {
		$cache[0] = $col['icat_midx'];
		$group0[$col['icat_midx']] = array();
	}

	if($cache[1] != $col['it_code']) {
		$cache[1] = $col['it_code'];
		$group0[$col['icat_midx']][$col['it_code']] = array();
	}

	if($cache[2] != $col['idx']) {
		$cache[2] = $col['idx'];
	}

	$group0[$col['icat_midx']][$col['it_code']][$col['idx']] = 1;
}

//check how many leaves in group0
function getRowSpan($value, $key) {
	global $rowSpan;
	$rowSpan += 1;
}

//GROUP TOTAL
$ggTotal = 0;

//GROUP
foreach ($group0 as $total1 => $group1) {

	//get category path from current icat_midx.
	$path = executeSP(ZKP_SQL."_getCategoryPath", $rd[$rdIdx][0]);
	eval(html_entity_decode($path[0]));	
	$path = array_reverse($path);
	$total1 = $path[1][4]." > ".$path[2][4]." > ".$path[3][4] ;

	echo "<span class=\"comment\"><b> CATEGORY: ". $total1. "</b></span>\n";
	print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th width="15%">MODEL NO</th>
			<th>DESC</th>
			<th width="13%">DO NO#</th>
			<th width="8%">ISSUE DATE</th>
			<th width="8%">WH CFM DATE</th>
			<th width="25%">CUSTOMER</th>
			<th width="8%">QTY<br>(EA)</th>
		</tr>\n
END;

	$gTotal		= 0;
	$print_tr_1 = 0;
	print "<tr>\n";
	//MODEL
	foreach($group1 as $total2 => $group2) {
		$rowSpan = 0;
		array_walk_recursive($group2, 'getRowSpan');
		$rowSpan += 1;

		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[".$rd[$rdIdx][1]."]<br/>".$rd[$rdIdx][2], ' valign="top" rowspan="'.$rowSpan.'"');	//model no
		cell($rd[$rdIdx][3], ' valign="top" rowspan="'.$rowSpan.'"');								//desc

		$total		= 0;
		$print_tr_2 = 0;
		//DO
		foreach($group2 as $total3 => $group3) {
			if($print_tr_2++ > 0) print "<tr>\n";
			cell_link("<span class=\"bar\">".$rd[$rdIdx][4]."</span>", ' align="center" ',
				' href="'.$rd[$rdIdx][11].'"');							//DO no
			cell($rd[$rdIdx][5], ' align="center"');					//DO date
			cell($rd[$rdIdx][6], ' align="center"');					//warehouse confirm date
			cell("[".trim($rd[$rdIdx][7])."] ".cut_string($rd[$rdIdx][8],25));		//customer	
			cell(number_format((double)$rd[$rdIdx][10]), ' align="right"');		//qty
			print "</tr>\n";

			$total += $rd[$rdIdx][10];
			$rdIdx++;
		}

		print "<tr>\n";
		cell("$total2", ' colspan="4" align="right" style="color:darkblue"');
		cell(number_format((double)$total), ' align="right" style="color:darkblue"');
		print "</tr>\n";

		$gTotal += $total;
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
		<th width="15%">MODEL NO</th>
		<th>DESC</th>
		<th width="13%">DO NO#</th>
		<th width="8%">ISSUE DATE</th>
		<th width="8%">WH CFM DATE</th>
		<th width="25%">CUSTOMER</th>
		<th width="8%">QTY<br>(EA)</th>
	</tr>\n
END;

print "<tr>\n";
cell("<b>GRAND TOTAL</b>", ' colspan="6" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$ggTotal), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>\n";
?>