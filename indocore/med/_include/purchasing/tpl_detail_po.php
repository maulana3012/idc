<!-------------------- DETAIL PL -------------------->
<?php if($part == "detail_pl_po") { ?>
<?php
$sql_pl =
"SELECT
  a.inpl_idx,
  to_char(a.inpl_checked_date,'dd-Mon-YY') AS checked_date,
  a.inpl_inv_no,
  c.it_code,
  c.it_model_no,
  c.it_desc,
  b.init_qty, 
  '../packing_list/revise_pl.php?_code='||a.pl_idx AS go_pl_page,
  '../packing_list/detail_confirm_pl.php?_code='||a.pl_idx||'&_inpl_idx='||a.inpl_idx AS  go_inpl_page
FROM
  ".ZKP_SQL."_tb_in_pl AS a
  JOIN ".ZKP_SQL."_tb_in_pl_item AS b USING(inpl_idx)
  JOIN ".ZKP_SQL."_tb_item AS c USING(it_code)
WHERE a.po_code = '$_code'
ORDER BY a.inpl_idx, a.inpl_checked_date, c.it_code 
";

$rd		= array();
$rdIdx	= 0;
$cache	= array("","");
$group0 = array();
$res_pl = query($sql_pl);
while($col =& fetchRowAssoc($res_pl)) {

	$rd[] = array(
		$col['inpl_idx'],		//0
		$col['checked_date'],	//1
		$col['inpl_inv_no'],	//2
		$col['it_code'], 		//3
		$col['it_model_no'],	//4
		$col['it_desc'],		//5
		$col['init_qty'], 		//6
		$col['go_pl_page'],		//7
		$col['go_inpl_page']	//8		
	);

	//1st grouping
	if($cache[0] != $col['inpl_idx']) {
		$cache[0] = $col['inpl_idx'];
		$group0[$col['inpl_idx']] = array();
	}

	if($cache[1] != $col['it_code']) {
		$cache[1] = $col['it_code'];
	}

	$group0[$col['inpl_idx']][$col['it_code']] = 1;
}
$g_total = 0;
?>
<table width="100%" class="table_sub">
    <tr>
        <th height="50" valign="top" align="left"><img src="../../_images/icon/package.gif"> <strong>PACKING LIST HISTORY</strong></th>
    </tr>
</table>
<table width="100%" class="table_nn">
	<tr height="30px">
		<th width="12%">ARRIVAL DATE</th>
		<th width="15%">INVOICE NO</th>
		<th width="20%">MODEL NO</th>
		<th>DESC</th>
		<th width="10%">QTY</th>
	</tr>
<?php
//INCOMING ITEM
foreach($group0 as $total1 => $group1) {
	$rowSpan = 0;
	$rowSpan += count($group1);

	print "<tr>\n";
	cell_link($rd[$rdIdx][1], ' valign="top" align="center" rowspan="'.$rowSpan.'"', ' href="'.$rd[$rdIdx][7].'"');	//arrival date
	cell_link("<span class=\"bar\">".$rd[$rdIdx][2]."</span>", ' valign="top" align="center" rowspan="'.$rowSpan.'"', ' href="'.$rd[$rdIdx][8].'"');	//invoice no

	$total 		= 0;
	$print_tr_1 = 0;
	//ORDER
	foreach($group1 as $total2) {
		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[". trim($rd[$rdIdx][3]) ."] ".$rd[$rdIdx][4]);	//model name
		cell($rd[$rdIdx][5]);									//desc
		cell(number_format($rd[$rdIdx][6]),' align="right"');	//qty
		print "</tr>\n";

		$total += $rd[$rdIdx][6]; 
		$rdIdx++;
	}
	print "<tr>\n";
	cell("ARRIVAL TOTAL", ' colspan="4" align="right" style="color:darkblue;"');
	cell(number_format($total), ' align="right" style="color:darkblue;"');
	print "</tr>\n";
	$g_total += $total;
}
print "<tr>\n";
cell("<b>TOTAL INCOMING</b>", ' colspan="4" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($g_total), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
?>
</table><br />
<?php } ?> 
<!-- ======================================================================================================== -->
<?php if($part == "detail_pl_pl") { ?>
<?php
$sql_pl =
"SELECT
  a.inpl_idx,
  to_char(a.inpl_checked_date,'dd-Mon-YY') AS checked_date,
  a.inpl_inv_no,
  c.it_code,
  c.it_model_no,
  c.it_desc,
  b.init_qty,
  CASE
	WHEN (select DISTINCT(inpl_idx) FROM ".ZKP_SQL."_tb_expired_pl WHERE inpl_idx = a.inpl_idx) is not null THEN true
	else false
  END AS inpl_has_ed,
  '../packing_list/detail_confirm_pl.php?_code='||a.pl_idx||'&_inpl_idx='||a.inpl_idx AS  go_page
FROM
  ".ZKP_SQL."_tb_in_pl AS a
  JOIN ".ZKP_SQL."_tb_in_pl_item AS b USING(inpl_idx)
  JOIN ".ZKP_SQL."_tb_item AS c USING(it_code)
WHERE a.pl_idx = $_code
ORDER BY a.inpl_idx, a.inpl_checked_date, c.it_code";
$rd		= array();
$rdIdx	= 0;
$cache	= array("","");
$group0 = array();
$res_pl = query($sql_pl);
$numRow = numQueryRows($res_pl);

while($col =& fetchRowAssoc($res_pl)) {

	$rd[] = array(
		$col['inpl_idx'],		//0
		$col['checked_date'],	//1
		$col['inpl_inv_no'],	//2
		$col['it_code'], 		//3
		$col['it_model_no'],	//4
		$col['it_desc'],		//5
		$col['init_qty'], 		//6
		$col['inpl_has_ed'], 	//7
		$col['go_page']		 	//8
	);

	//1st grouping
	if($cache[0] != $col['inpl_idx']) {
		$cache[0] = $col['inpl_idx'];
		$group0[$col['inpl_idx']] = array();
	}

	if($cache[1] != $col['it_code']) {
		$cache[1] = $col['it_code'];
	}

	$group0[$col['inpl_idx']][$col['it_code']] = 1;
}
$g_total = 0;
?>
<table width="100%" class="table_sub">
    <tr>
        <th height="50" valign="top" align="left"><img src="../../_images/icon/package.gif"> <strong>PACKING LIST HISTORY</strong></th>
    </tr>
</table>
<table width="100%" class="table_nn">
	<tr height="30px">
		<th width="15%">ARRIVAL DATE</th>
		<th width="25%">MODEL NO</th>
		<th>DESC</th>
		<th width="10%">QTY</th>
	</tr>
<?php
//INCOMING ITEM
foreach($group0 as $total1 => $group1) {
	$rowSpan = 0;
	$rowSpan += count($group1)+1;

	print "<tr>\n";
	cell_link('<b>'.$rd[$rdIdx][1].'</b>', ' valign="top" align="center" rowspan="'.$rowSpan.'"', ' href="'.$rd[$rdIdx][8].'"');	//arrival date

	$total 		= 0;
	$print_tr_1 = 0;
	//ORDER
	foreach($group1 as $total2) {
		if($print_tr_1++ > 0) print "<tr>\n";
		cell("[". trim($rd[$rdIdx][3]) ."] ".$rd[$rdIdx][4]);	//model name
		cell($rd[$rdIdx][5]);									//desc
		cell(number_format($rd[$rdIdx][6]),' align="right"');	//qty
		print "</tr>\n";

		$total += $rd[$rdIdx][6]; 
		$inpl_idx	= $rd[$rdIdx][0]; 
		$pl_has_ed	= $rd[$rdIdx][7]; 
		$rdIdx++;
	}
	print "<tr>\n";
	cell("ARRIVAL TOTAL", ' colspan="2" align="right" style="color:darkblue;"');
	cell(number_format($total), ' align="right" style="color:darkblue;"');
	print "</tr>\n";
	$g_total += $total;
}
print "<tr>\n";
cell("<b>TOTAL INCOMING</b>", ' colspan="3" align="right" style="color:brown; background-color:lightyellow"');
cell('<b>'.number_format($g_total).'</b>', ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
?>
</table><br />

<?php } ?>


<!-------------------- DETAIL UNCFM -------------------->
<?php if($part == "detail_uncfm") { ?>

<table width="100%" class="table_sub">
    <tr>
        <th height="35"><img src="../../_images/icon/setting_mini.gif"> &nbsp; <strong>UNCONFIRM PO</strong></th>
    </tr>
</table><br />
<form name="frmUnconfirmed" method="post">
<input type="hidden" name="p_mode" value="unconfirmed">
<input type="hidden" name="_code" value="<?php echo $_code ?>">
<table width="100%" cellpadding="0">
 	<tr>
		<td width="2%"><img src="../../_images/properties/p_leftmenu_icon02.gif"></td>
		<td><strong>Unconfirmed Information</strong></td>
		<td align="right">
			<button name='btnUnConfirm' class='input_btn' style='width:130px;'><img src="../../_images/icon/clean.gif" align="middle"> &nbsp; Unconfirm PO</button>
		</td>
    </tr>
</table><br /><br />
</form>
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmUnconfirmed;

	window.document.frmUnconfirmed.btnUnConfirm.onclick = function() {
		var f = window.document.frmUnconfirmed;

		if(confirm("Are you sure to unconfirmed PO?")) {
			window.document.frmUnconfirmed.submit();
		}
	}
</script>
<?php } ?>

<!-------------------- DETAIL UNCFM DISABLED -------------------->
<?php if($part == "detail_uncfm_disabled") { ?>
<table width="100%" class="table_box">
	<tr>
		<td width="10%" valign="top"><img src="../../_images/icon/warning.gif"></td>
		<td>
			<span>
			This is a locked document. To modify document, see the hierarchy process.<br /><br />
			Here are the possibility(es) :
			</span>
			<ul>
				<li> This PO already has one or more Packing List</li>
			</ul>
		</td>
	</tr>
</table>
<?php } ?>