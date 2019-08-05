<table width="100%" class="table_sub">
    <tr>
        <th height="35"><img src="../../_images/icon/setting_mini.gif"> &nbsp; <strong>RETURN INFORMATION</strong></th>
    </tr>
</table><br />
<?php
$turn_sql	= "SELECT turn_code, turn_return_date, turn_paper, turn_received_by  FROM ".ZKP_SQL."_tb_return WHERE turn_bill_code = '$_code' ORDER BY turn_return_date";
$turn_res	=& query($turn_sql);
while($turn =& fetchRow($turn_res)) {
?>
<img src="../../_images/icon/blacksmallicon.gif"> <a href="revise_return.php?_code=<?php echo $turn[0] ?>" target="_blank" style="color:#07519a;font-family:courier;font-weight:bold"><u>RETURN : <?php echo trim($turn[0])?></u></a> <img src="../../_images/icon/blacksmallicon.gif">
<table width="100%" cellpadding="0">
<tr>
		<td width="2%"><img src="../../_images/properties/p_leftmenu_icon02.gif"></td>
		<td><strong>Return Condition</strong></td>
    </tr>
    <tr>
    	<td></td>
		<td>
    		<table width="100%" class="table_box">
    			<tr>
					<td width="5%" style="color:#016FA1">DATE</td>
					<td width="2%">:</td>
					<td width="15%"><?php echo date('j-M-Y',strtotime($turn[1])) ?></td>
					<td width="5%" style="color:#016FA1">BY</td>
					<td width="2%">:</td>
					<td width="15%"><?php echo $turn[3] ?></td>
					<td width="5%" style="color:#016FA1">TYPE</td>
					<td width="2%">:</td>
					<td>
						<input type="radio" value="0" disabled <?php echo ($turn[2]==0)?'checked':'' ?>> Issue invoice &amp; receive items &nbsp;
						<input type="radio" value="1" disabled <?php echo ($turn[2]==1)?'checked':'' ?>> Issue invoice only
					</td>
				</tr>
    		</table>
    	</td>
    </tr>
    <tr height="10px">
    	<td colspan="2"></td>
    </tr>
	<tr>
		<td width="2%"><img src="../../_images/properties/p_leftmenu_icon02.gif"></td>
		<td><strong>Return Item</strong></td>
    </tr>
    <tr>
		<td></td>
		<td>
    		<table width="100%" class="table_nn">
    			<tr height="25px">
					<th width="5%">CODE</th>
					<th width="13%">ITEM NO</th>
					<th>DESCRIPTION</th>
					<th width="12%">UNIT PRICE<br />(Rp)</th>
					<th width="5%">QTY</th>
					<th width="12%">AMOUNT<br />(Rp)</th>
					<th width="20%">REMARK</th>
    			</tr>
				<?php
				$reor_sql	= "SELECT * FROM ".ZKP_SQL."_tb_return_item JOIN ".ZKP_SQL."_tb_item USING (it_code) WHERE turn_code = '{$turn[0]}'";
				$reor_res	= query($reor_sql);
				while($items =& fetchRow($reor_res)) {
				?>
				<tr>
					<td><?php echo $items[0]?></td>
					<td><?php echo $items[5]?></td>
					<td><?php echo cut_string($items[7],50)?></td>
					<td align="right"><?php echo number_format((double)$items[10])?></td>
					<td align="right"><?php echo number_format((double)$items[9])?></td>
					<td align="right"><?php echo number_format((double)$items[9]*$items[10])?></td>
					<td><?php echo $items[11]?></td>
				</tr>
				<?php } ?>
    		</table>
    	</td>
    </tr>
</table><br /><br />
<?php } ?>