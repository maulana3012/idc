<?php
$tmp = array();
if($showNull=="no") { $tmp[] = "(SELECT sum(stk_qty) FROM ".ZKP_SQL."_tb_stock WHERE it_code=it.it_code) > 0"; }
$tmp[] = "it_status = 0 AND ".ZKP_SQL."_statusQtyLevel(it_code) is false";
$strWhere = implode(" AND ", $tmp);
$stk_sql = "
SELECT 
	it_code,
	it_model_no,
	it_desc,
	it_critical_stock,
	(SELECT sum(stk_qty) FROM ".ZKP_SQL."_tb_stock WHERE it_code=it.it_code) AS it_available_stock,
	(SELECT sum(stk_qty) FROM ".ZKP_SQL."_tb_stock WHERE it_code=it.it_code) - it_critical_stock AS it_diff,
	CASE
		WHEN (SELECT sum(stk_qty) FROM ".ZKP_SQL."_tb_stock WHERE it_code=it.it_code) - it_critical_stock > 0 THEN 'green'
		ELSE 'red'
	END AS it_color
FROM ".ZKP_SQL."_tb_item AS it
WHERE $strWhere
ORDER BY it_code";
$stk_res = query($stk_sql);
$numRow  = numQueryRows($stk_res);
?>
<p align="center"><strong>CURRENT CRITICAL LEVEL OF STOCK</strong></p>
<table width="75%" class="table_box" align="center">
	<tr>
		<td colspan="4"></td>
		<td colspan="3"><input type="checkbox" onclick="chkIncludeNull(this.checked)"<?php echo ($showNull=="yes")?" checked":"" ?>>Include null qty</td>
	</tr>
	<tr>
		<th width="3%">No</th>
		<th width="6%">CODE</th>
		<th width="18%">ITEM NO</th>
		<th>DESCRIPTION</th>
		<th width="9%">CRITICAL<br />STOCK</th>
		<th width="8%">RECENTLY<br />STOCK</th>
		<th width="6%">CALC<br />( <font color="green">+</font> / <font color="red">-</font> )</th>
	</tr>
</table>
<div style="height:400; overflow-y:scroll">
<table width="75%" class="table_c" align="center">
	<?php while ($column =& fetchRowAssoc($stk_res)) { ?>
	<tr>
		<td width="3%"><?php echo ++$oPage->serial ;?></td>
		<td width="6%"><?php echo $column['it_code']?></td>
		<td width="18%"><?php echo substr($column['it_model_no'], 0, 13)?></td>
		<td><?php echo cut_string($column['it_desc'],60);?></td>
		<td width="8%" align="right"><?php echo number_format((double)$column['it_critical_stock'])?></td>
		<td width="8%" align="right"><?php echo number_format((double)$column['it_available_stock'],2)?></td>
		<td width="8%" align="right"><font color="<?php echo $column['it_color'] ?>"><?php echo ($column['it_diff']<=0) ? number_format((double)$column['it_diff']*-1,2) : number_format((double)$column['it_diff'],2)?></font></td>
	</tr>
	<?php } ?>
</table>
</div>
<br /><br />
<script type="text/javascript">
function chkIncludeNull(o) {
	if(o) {
		window.location.href = "index.php?showMsg=true&showNull=yes";
	} else {
		window.location.href = "index.php?showMsg=true&showNull=no";
	}
}
</script>