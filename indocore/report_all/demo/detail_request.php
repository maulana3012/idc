<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*/
//REQUIRE
require_once "../../zk_config.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "daily_outgoing_demo_by_reference.php";
if (!isset($_GET['_code']) || $_GET['_code'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else {
	$_code = urldecode($_GET['_code']);
}

//---------------------------------------------------------------------------------------------- DEFAULT PROCESS
$sql	 = "SELECT *, ".ZKP_SQL."_getTurnDemo(use_code) AS return_demo FROM ".ZKP_SQL."_tb_using_demo WHERE use_code = '$_code'";
$result = query($sql);
$column = fetchRowAssoc($result);

if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
}

$sql_item = "
SELECT
  it_code,
  it_model_no,
  it_desc,
  CASE 
	WHEN usit_returnable is true THEN 'Yes'
	WHEN usit_returnable is false THEN 'No'
  END AS it_returnable,
  CASE 
	WHEN usit_returnable is true THEN 0
	WHEN usit_returnable is false THEN 1
  END AS it_return,
  usit_qty,
  usit_remark,
  it_ed
FROM ".ZKP_SQL."_tb_using_demo_item JOIN ".ZKP_SQL."_tb_item USING(it_code) WHERE use_code = '$_code'
ORDER BY it_code";
$res_item = query($sql_item);

$sql_ed ="
SELECT
  it_code,
  it_model_no,
  to_char(used_expired_date, 'Mon-YYYY') AS expired_date,
  used_qty
FROM ".ZKP_SQL."_tb_using_demo_ed JOIN ".ZKP_SQL."_tb_item USING(it_code) WHERE use_code = '$_code'";
$res_ed = query($sql_ed);
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
function updateAmount() {

	var count	 = window.rowPosition.rows.length;
	var sumOfQty = 0;

	for (var i=0; i<count; i++) {
		var oRow = window.rowPosition.rows(i);
		sumOfQty = sumOfQty + parseFloat(removecomma(oRow.cells(3).innerText));
	}
	
	window.document.all.totalWhQty.value = numFormatval(sumOfQty.toString(), 2);
}
</script>
</head>
<body topmargin="0" leftmargin="0" onload="updateAmount()">
<table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#9CBECC">
  <tr>
    <td>
			<?php require_once APP_DIR . "_include/tpl_header.php"?>
    </td>
  </tr>
  <tr>
    <td style="padding:5 10 0 10" valign="bottom">
		<?php require_once APP_DIR . "_include/tpl_topMenu.php";?>
    </td>
  </tr>
  <tr>
    <td style="padding:0 3 3 3">
    	<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
        <tr>
			<?php require_once "_left_menu.php";?>
			<td style="padding:10" height="480" valign="top">
          	<!--START: BODY-->
<h3>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] DETAIL REQUEST</h3>
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<td colspan="4"><strong class="info">REQUEST INFORMATION</strong></td>
		<td colspan="3" align="right"><i>Last updated by : <?php echo $column['use_lastupdated_by_account'].date(', j-M-Y g:i:s', strtotime($column['use_lastupdated_timestamp']))?></i></td>
	</tr>
	<tr>
		<th width="15%">REQUEST NO</th>
		<td colspan="2"><strong><?php echo $column['use_code'] ?></strong></td>
		<th width="15%">REQUEST BY</th>
		<td width="20%"><?php echo $column['use_request_by'] ?></td>
		<th width="15%">REQUEST DATE</th>
		<td width="15%"><?php echo date('d-M-Y', strtotime($column['use_request_date'])) ?></td>
	</tr>
	<tr>
		<th rowspan="2">CUSTOMER/<br />EVENT</th>
		<th width="12%">CODE</th>
		<td><?php echo $column['use_cus_to'] ?></td>
		<th>NAME</th>
		<td colspan="3"><?php echo $column['use_cus_name'] ?></td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="5"><?php echo $column['use_cus_address'] ?></td>
	</tr>
</table><br />
<strong class="info">ITEM LIST</strong>
<table width="100%" class="table_l">
	<thead>
		<tr height="40px">
			<th width="7%">CODE</th>
			<th width="15%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="10%">QTY</th>
			<th width="7%">RETURNABLE<br />Yes | No</th>
			<th width="15%">REMARK</th>
		</tr>
	</thead>
	<tbody id="rowPosition">
<?php
$i = 0;
while($items =& fetchRow($res_item)) {
?>
		<tr id="<?php echo trim($items[0])?>">
			<td><?php echo $items[0]?></td>
			<td><?php echo $items[1]?></td>
			<td><?php echo $items[2]?></td>
			<td align="right"><?php echo number_format($items[5],2)?></td>
			<td align="center"><?php echo $items[3]?></td>
			<td><?php echo $items[6]?></td>
		</tr>
<?php $i++; } ?>
	</tbody>
	<tr>
		<th colspan="3" align="right">TOTAL QTY</th>
		<th width="10%"><input name="totalWhQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th colspan="3" width="20%">&nbsp;</th>
	</tr>
</table><br />
<table width="100%" class="table_layout">
	<tr>
		<td width="55%" valign="top">
			<strong class="info">OTHERS</strong>
			<table width="100%" class="table_box" cellspacing="1">
				<tr>
					<th width="20%">SIGN BY</th>
					<td width="20%"><?php echo $column["use_signature_by"] ?></td>
					<th width="20%">CONFIRM BY</th>
					<td><?php echo $column['use_confirm_by_account'] ?></td>
				</tr>
				<tr>
					<th>REMARK</th>
					<td colspan="3"><textarea name="_remark" rows="5" style="width:95%" readonly><?php echo $column["use_remark"] ?></textarea></td>
				</tr>
			</table>
		</td>
		<td width="45%" valign="top">
			<strong class="info">DETAIL ITEM PER E/D</strong>
			<table width="100%" class="table_l">
				<thead>
					<tr height="25px">
						<th width="15%">CODE</th>
						<th>ITEM NO</th>
						<th width="25%">E/D</th>
						<th width="15%">QTY</th>
					</tr>
				</thead>
				<tbody id="EDPosition">
<?php while($items =& fetchRow($res_ed)) { ?>
					<tr>
						<td><?php echo $items[0] ?></td>
						<td><?php echo $items[1] ?></td>
						<td><?php echo $items[2] ?></td>
						<td align="right"><?php echo $items[3] ?></td>
					</tr>
<?php } ?>
				</tbody>
			</table><br />
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<table width="100%" class="table_box">
				<tr>
					<td><span class="comment"><i>Confirm request by : <?php echo $column['use_cfm_marketing_by_account'].date(', j-M-Y g:i:s',strtotime($column['use_cfm_marketing_timestamp'])) ?></i></span></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td align="center"><button name='btnList' class='input_btn' style='width:130px' onclick="window.location.href='daily_outgoing_demo_by_reference.php'"><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button></td>
	</tr>
</table>
            <!--END: BODY-->
          </td>
        </tr>
      </table>
      </td>
  </tr>
  <tr>
    <td style="padding:5 10 5 10" bgcolor="#FFFFFF">
			<?php require_once APP_DIR . "_include/tpl_footer.php"?>
    </td>
  </tr>
</table>
</body>
</html>