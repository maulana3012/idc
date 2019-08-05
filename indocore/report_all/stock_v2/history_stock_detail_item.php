<?php
# Item Detail
if($_type != 'all')
{
	$tmp[0][0][] = "stk_type = $_type";
	$tmp[0][1][] = "log_type = $_type";
}
if($_location != '0') {
	$tmp[0][0][] = "stk_wh_location = $_location";
	$tmp[0][1][] = "log_wh_location = $_location";
}

$tmp[0][0][] = $tmp[0][1][] = "it_code = it.it_code";
$strWhere[0][0] = implode(" AND ", $tmp[0][0]);
$strWhere[0][1] = implode(" AND ", $tmp[0][1]);

# Query
$sql = "
SELECT
  trim(it_code) AS it_code,
  trim(it_model_no) AS it_model_no,
  CASE
	WHEN it_desc != '' THEN '['||trim(it_code)||'] '||it_desc
	ELSE '['||trim(it_code)||']'
  END AS it_desc,
  it_ed,
  it_critical_stock AS critical_qty,
  (SELECT sum(stk_qty) FROM ".ZKP_SQL."_tb_stock_v2 WHERE {$strWhere[0][0]}) AS qty,
  (SELECT sum(log_qty) FROM ".ZKP_SQL."_tb_log_detail WHERE {$strWhere[0][1]} AND log_document_type in ($v_log_in)) AS in_qty,
  (SELECT sum(log_qty)*-1 FROM ".ZKP_SQL."_tb_log_detail WHERE {$strWhere[0][1]} AND log_document_type in ($v_log_out)) AS out_qty
FROM
  ".ZKP_SQL."_tb_item AS it
WHERE it_code = '$_code'
";
$result	= query($sql);
$item	= fetchRowAssoc($result);
/*
echo "<pre>";
echo $sql;
echo "</pre>";
*/
$item_qty[0] = ($item['in_qty']=='')?0:$item['in_qty'];
$item_qty[1] = ($item['out_qty']=='')?0:$item['out_qty'];
$item_qty[2] = $item_qty[0]-$item_qty[1];
$item_qty[3] = $item_qty[2]-$item['critical_qty'];
if($item_qty[3] < 0)
	$item_qty['color'] = 'color:red';
else	$item_qty['color'] = 'color:#000';
?>
<input type="hidden" name="_code" value="<?php echo $_code ?>">
<input type="hidden" name="_model_no" value="<?php echo $item['it_model_no'] ?>">
<input type="hidden" name="_type" value="<?php echo $_type ?>">
<input type="hidden" name="_wh_location" value="<?php echo $_location ?>">
<table class="tb_highlight" style="width:100%">
  <caption>Item Description</caption>
  <thead>
    <tr>
	<th width="70%">Model</th>
	<th width="15%">Stock Type</th>
	<th width="15%" class="right">Location</th>
    </tr>
  </thead>
  <tbody>
    <tr>
	<td><?php echo $item['it_model_no'] ?> &nbsp;<span><?php echo $item['it_desc']?></span></td>
	<td><?php echo $v_type[$_type] ?> <a href="javascript:changeItem('type', '<?php echo $_type ?>')"> <img src="../../_images/icon/arrow_right.gif" alt="Change type stock"></a></td>
	<td width="25%" class="right"><?php echo $v_loc[$_location] ?> <a href="javascript:changeItem('loc', '<?php echo $_location ?>')"> <img src="../../_images/icon/arrow_right.gif" alt="Change type stock"></a></td>
    </tr>
  </tbody>
</table>
<table class="tb_highlight" style="width:100%">
  <caption>Stock Status</caption>
  <thead>
    <tr>
	<th width="25%">Incoming</th>
	<th width="25%">Outgoing</th>
	<th width="25%">Stock</th>
	<th>Remain</th>
    </tr>
  </thead>
  <tbody>
    <tr>
	<td><?php echo number_format($item_qty[0],2)?> <span>qty</span></td>
	<td><?php echo number_format($item_qty[1],2)?> <span>qty</span></td>
	<td><?php echo number_format($item_qty[2],2)?> <span>qty</span></td>
	<td class="right"  style="<?php echo $item_qty['color'] ?>"><?php echo number_format($item_qty[3],2)?>
	<span>qty <br/>to critical status (<?php echo number_format($item['critical_qty'],0)?> pcs)</span></td>
    </tr>
  </tbody>
</table>