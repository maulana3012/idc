<?php
# Stock detail
if($_type != 'all')
{
  $tmp[1][0][] = "log_type = $_type";
  $tmp[1]['in'][]  = "log_type = $_type";
  $tmp[1]['out'][] = "log_type = $_type";
}
if($_type == 'all')
{
  $tmp[1][0][] = "substr(log_document_type,1,11) != 'Move Type ('";
}
if($currentDept == 'purchasing')
{
  $tmp[1][0][] = "log_wh_location = $_location";
  $tmp[1]['in'][]  = "log_wh_location = $_location";
  $tmp[1]['out'][] = "log_wh_location = $_location";
} else {
  if($_location != '0')
  {
    $tmp[1][0][] = "log_wh_location = $_location";
    $tmp[1]['in'][]  = "log_wh_location = $_location";
    $tmp[1]['out'][] = "log_wh_location = $_location";
  }
}
if($_activity != '0')
{
  if ($_activity == '1') {
    $tmp[1][0][] = $tmp[1]['in'][] = "log_document_type IN ($v_log_in)";
    $tmp[1]['out'][] = "log_document_type is null";
  }
  else if ($_activity == '2') {
    $tmp[1][0][] = $tmp[1]['out'][] = "log_document_type IN ($v_log_out)";
    $tmp[1]['in'][] = "log_document_type is null";
    
  }
  else {
    $tmp[1][0][] = "log_document_type = '$_activity'";
    $tmp[1]['in'][]  = "log_document_type = '$_activity'";
    $tmp[1]['out'][] = "log_document_type = '$_activity'";
  }
}
$tmp[1][0][] = $tmp[1]['in'][] = $tmp[1]['out'][] = "it_code = '$_code'";
$tmp[1][0][] = "log_cfm_timestamp::date BETWEEN DATE '$period_from' AND '$period_to'";
$tmp[1]['in'][] = $tmp[1]['out'][] = "log_cfm_timestamp <= DATE '$period_from 00:00:00'";

$strWhere[1][0] = implode(" AND ", $tmp[1][0]);
$strWhere['in'][0]  = implode(" AND ", $tmp[1]['in']);
$strWhere['out'][0] = implode(" AND ", $tmp[1]['out']);
/*
echo "<pre>";
var_dump($strWhere);
echo "</pre>";
*/
# Query
$sql_history	= "
SELECT *,
  CASE WHEN log_is_revised is TRUE THEN log_document_type || ' (rev)'
  ELSE log_document_type
 END AS log_document_type_adj
FROM ".ZKP_SQL."_tb_log_detail WHERE {$strWhere[1][0]} ORDER BY log_cfm_timestamp, log_code";
$res_history	= query($sql_history);

# Query last stock qty
$sql_last_stock_qty = "
SELECT sum(log_qty) AS qty FROM ".ZKP_SQL."_tb_log_detail WHERE {$strWhere['in'][0]} UNION
SELECT sum(log_qty) AS qty FROM ".ZKP_SQL."_tb_log_detail WHERE {$strWhere['out'][0]}
";
$res_bal = query($sql_last_stock_qty);
$v_last_bal = 0;
while($bal =& fetchRowAssoc($res_bal)) {
  if($bal['qty'] != '')
    $v_last_bal += $bal['qty'];
}
$v_cur_bal = $v_last_bal;

echo "<pre>";
#var_dump($_type,$strWhere['out'][0]);
echo "</pre>";

?>
<table class="tb_highlight" style="width:100%">
  <caption>Detail incoming & outgoing item</caption>
</table>
<table class="table_box" width="100%">
  <tr>
    <td width="50%"></td>
    <td>
	Filter by :
	<select name="cboTypeActivity" onchange="changeItem('activity', this.value)">
		<option value="0" style="background-color:#FFFFFF;color:darkblue"> == ALL CONDITION== </option>
		<optgroup label="-In Stock-" style="background-color:#FFFF99;color:darkblue;">
		  <option value="1">ALL IN STOCK</option>
		  <option value="Initial Stock">Initial Stock</option>
		  <option value="PL Import">P/L Import</option>
		  <option value="PL Claim">P/L Claim</option>
		  <option value="PL Local">P/L Local</option>
		  <option value="Return Billing">Return Billing</option>
		  <option value="Return Order">Return Order</option>
		  <option value="Return DT">Return DT</option>
      <option value="Move Type (PO)">Move Type (PO No Only)</option>
      <option value="Move Type (Return No Only)">Move Type (Return No Only)</option>
		</optgroup>
		<optgroup label="-Out Stock-" style="background-color:#FFCC99;color:darkblue">
		  <option value="2">ALL OUT STOCK</option>
		  <option value="DO Order">DO Order</option>
		  <option value="DO Billing">DO Billing</option>
		  <option value="DT">DT</option>
		  <option value="DF">DF</option>
		  <option value="DR">DR</option>
		  <option value="DM">DM</option>
		  <option value="Reject Stock">Move to Reject</option>
		  <option value="Reject ED">Delete expired stock</option>
      <option value="Move Type (Billing No Only)">Move Type (Billing No Only)</option>      
		</optgroup>
	</select>
    </td>
    <td>
	Periode : 
	<a href="javascript:setFilterDate(-1)"> <img src="../../_images/icon/arrow_left.gif" alt="Previous month"> </a>
	<input type="text" name="period_from" size="12" class="fmtd" value="<?php echo $period_from; ?>">&nbsp;
	<input type="text" name="period_to" size="12" class="fmtd"  value="<?php echo $period_to ?>">
	<a href="javascript:setFilterDate(1)"> <img src="../../_images/icon/arrow_right.gif" alt="Next month"> </a>
    </td>
  </tr>
</table>
<table width="100%" class="table_box">
  <tr>
    <th width="20%">CONFIRM DATE</th>
    <th width="18%">TYPE</th>
    <th width="20%">DOCUMENT NO.</th>
    <th width="15%">DOCUMENT DATE</th>
    <th width="8%">QTY<br />(Pcs)</th>
    <th width="20%">REMARK</th>
    <th width="4%"></th>
  </tr>
</table>
<table width="100%" class="table_n">
  <tr>
    <td width="20%"></td>
    <td width="18%"></td>
    <td width="20%"></td>
    <td width="15%"></td>
    <td width="8%"></td>
    <td width="20%"></td>
    <td width="4%"></td>
  </tr>
  <tr height="35px">
    <td align="right" colspan="4"><i>Last period balance</i></td>
    <td width="8%"><input type="text" name="totalLastPeriodQty" id="totalLastPeriodQty" class="fmtn" style="width:100%" value="<?php echo number_format($v_last_bal,2) ?>" readonly></td>
    <td colspan="2">&nbsp;</td>
  </tr>
<?php
$v_cur_period = 0;
if(numQueryRows($res_history) == 0) {  
?>
  <tr>
    <td colspan="7"><i>There is no log. Make sure your period search</i></td>
  </tr>
<?php } else {?>
<?php while($log =& fetchRowAssoc($res_history)) { ?>
  <tr>
    <td><?php echo date('d / m / Y H:i:s', strtotime($log["log_cfm_timestamp"])) ?></td>
    <td><?php echo $log["log_document_type_adj"] ?></td>
    <td align="center"><?php echo $log["log_document_no"] ?></td>
    <td align="center"><?php echo ($log["log_document_date"]!='') ? date('d-M-y', strtotime($log["log_document_date"])):'' ?></td>
    <td align="right"><?php echo number_format($log["log_qty"],2) ?></td>
    <td></td>
    <td></td>
  </tr>
<?php  $v_cur_period += $log["log_qty"]; ?>
<?php }} ?>
  <tr height="35px">
    <td colspan="3" align="left"><input type="checkbox" name="chkIncBalance" onclick="calcuteQty(this.checked)" checked><i style="font-weight:normal">include last period balance</i></td>
    <td align="right">Total Stock</td>
    <td>
      <input type="hidden" name="totalPeriod" id="totalPeriod" value="<?php echo number_format($v_cur_period, 2) ?>" readonly>
      <input type="text" name="totalQty" id="totalQty" class="fmtn" style="width:100%" value="<?php echo number_format($v_cur_bal+$v_cur_period, 2) ?>" readonly>
    </td>
    <td colspan="2"></td>
  </tr>
</table><br /><br />

<script language="javascript1.2" type="text/javascript">
    var f = window.document.frmLog;
    var ts = <?php echo time() * 1000;?>;
    f.period_from.onkeypress = function() {
	  if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
		  f.submit();
	  }
    }

    f.period_to.onkeypress = function() {
	  if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
		  f.submit();
	  }
    }
</script>

<?php if($item['it_ed']=='t') { ?>
<table class="tb_highlight" style="width:100%">
  <caption>E/D Log</caption>
</table>
<table class="table_box" width="100%">
  <tr>
    <td align="right">
	<span class="comment">Search E/D log for this period :</span> 
	<select name="cboMonth">
	  <option value="1">January</option>
	  <option value="2">February</option>
	  <option value="3">March</option>
	  <option value="4">April</option>
	  <option value="5">May</option>
	  <option value="6">June</option>
	  <option value="7">July</option>
	  <option value="8">August</option>
	  <option value="9">September</option>
	  <option value="10">October</option>
	  <option value="11">November</option>
	  <option value="12">December</option>
	</select>&nbsp; 
	<input type="text" name="_year" class="fmtn" style="width:40px" maxlength="4" value="<?php echo date("Y") ?>" onKeyPress="if(window.event.keyCode == 13) findEDLog()">&nbsp;
    </td>
    <th width="5%"><a href="javascript:findEDLog()"><img src="../../_images/icon/search_mini.gif" alt="Search"></a></th>
  </tr>
</table><br />
<script language="javascript1.2" type="text/javascript">
function findEDLog() {
	var f = window.document.frmLog;
	if(f._year.value.length < 4) {
		alert("Please input year in complete digit");
		f._year.focus();
		return;
	}

	var code	= f._code.value;
	var model	= f._model_no.value;
	var type	= f._type.value;
	var loc		= f._wh_location.value;
	var month	= f.cboMonth.value;
	var year	= f._year.value;
	var filter_by	= window.document.frmLog.cboTypeActivity.value;

	var x = (screen.availWidth - 650) / 2;
	var y = (screen.availHeight - 650) / 2;

	wSearchItem = 
		window.open("p_history_stock_ed.php?_code="+code+"&_model="+model+"&_type="+type+"&_loc="+loc+"&_month="+month+"&_year="+year+"&cboTypeActivity="+filter_by,
		'',
		'scrollbars,width=650,height=650,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wSearchItem.focus();
}
</script>
<?php }?>